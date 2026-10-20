@extends('layouts.app')

@section('title', 'My Account')

@push('head')
<link rel="stylesheet" href="{{ asset('css/my-account.css') }}?v={{ @filemtime(public_path('css/my-account.css')) ?: time() }}">
@endpush

@section('content')
<link rel="stylesheet" href="{{ asset('css/my-account.css') }}?v={{ @filemtime(public_path('css/my-account.css')) ?: time() }}">

<div class="amz-page-wrapper">
    <div class="amz-container">

        {{-- Flash Alert Notifications --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-3 p-3 mb-3 border-0" style="background: #ecfdf5; color: #065f46; border-left: 4px solid #10b981 !important;">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show rounded-3 p-3 mb-3 border-0" style="background: #fef2f2; color: #991b1b; border-left: 4px solid #ef4444 !important;">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @php
            $activeTab = request('tab');
            $isAuthor = $user->role === 'author' || $user->reg_type === 'author' || !empty($author);
            $isPublisher = $user->role === 'publisher' || $user->reg_type === 'publisher';
            $isSeller = $user->role === 'seller' || $user->reg_type === 'seller';
            $isAdmin = $user->isAdmin();
            $regData = is_array($user->reg_data) ? $user->reg_data : [];
            $isApproved = ($user->reg_status === 'approved');
            $isPending = !$isApproved && ($user->reg_status === 'pending' || !empty($regData['kyc_submitted_at']));
        @endphp

        {{-- Pending Approval Alert for Special Roles --}}
        @if(($isAuthor || $isPublisher || $isSeller) && !$isApproved)
            <div class="alert alert-warning border-0 rounded-3 p-3 mb-4" style="background: #fffbeb; border-left: 4px solid #f59e0b !important;">
                <h5 class="fw-bold mb-1 text-dark" style="font-size: 14.5px;">
                    আপনার {{ $isAuthor ? 'লেখক' : ($isPublisher ? 'প্রকাশক' : 'সেলার') }} অ্যাকাউন্টটি অনুমোদনের অপেক্ষায় রয়েছে
                </h5>
                <p class="mb-0 text-muted small lh-base" style="font-size: 13px;">
                    আপনার একাউন্টটি পর্যালোচনায় রয়েছে। অনুমোদন সম্পন্ন হলে বিশেষায়িত পোর্টাল সক্রিয় হবে। পাঠক হিসেবে বই ব্রাউজিং, অর্ডার ও ট্র্যাকিং করতে পারেন।
                </p>
            </div>
        @endif

        {{-- ═════════════════════════════════════════════════════════════════════ --}}
        {{-- USER NAVIGATION & QUICK-JUMP BAR                                      --}}
        {{-- ═════════════════════════════════════════════════════════════════════ --}}
        <div class="amz-account-nav-bar">
            <div class="amz-nav-user-badge">
                <div class="amz-nav-user-avatar">
                    @if(!empty($user->avatar))
                        <img src="{{ str_starts_with($user->avatar, 'http') ? $user->avatar : asset('storage/' . ltrim($user->avatar, '/')) }}" alt="{{ $user->name }}" class="w-100 h-100 object-fit-cover rounded-circle">
                    @else
                        <span>{{ mb_substr($user->name, 0, 1) }}</span>
                    @endif
                </div>
                <div>
                    <div class="fw-semibold text-dark lh-sm" style="font-size: 14.5px;">{{ $user->name }}</div>
                    <div class="text-muted" style="font-size: 12px;">
                        @if($isAdmin)
                            <span class="badge bg-danger px-2 py-0.5 rounded-pill">অ্যাডমিন</span>
                        @elseif($isAuthor)
                            <span class="badge {{ $isApproved ? 'bg-primary' : 'bg-warning text-dark' }} px-2 py-0.5 rounded-pill">
                                লেখক {{ $isApproved ? 'Studio' : '(অপেক্ষমাণ)' }}
                            </span>
                        @elseif($isPublisher)
                            <span class="badge {{ $isApproved ? 'bg-success' : 'bg-warning text-dark' }} px-2 py-0.5 rounded-pill">
                                প্রকাশক {{ $isApproved ? 'Portal' : '(অপেক্ষমাণ)' }}
                            </span>
                        @elseif($isSeller)
                            <span class="badge {{ $isApproved ? 'bg-info text-dark' : 'bg-warning text-dark' }} px-2 py-0.5 rounded-pill">
                                সেলার {{ $isApproved ? 'Panel' : '(অপেক্ষমাণ)' }}
                            </span>
                        @else
                            <span class="badge bg-light text-secondary border px-2 py-0.5 rounded-pill">গ্রাহক অ্যাকাউন্ট</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Clean Quick-Jump Dropdown --}}
            <div class="dropdown">
                <button class="amz-nav-dropdown-btn dropdown-toggle" type="button" id="myAccountInternalNavDropdownBtn" data-bs-toggle="dropdown" aria-expanded="false">
                    <span id="currentAccountNavLabel">অ্যাকাউন্ট ড্যাশবোর্ড</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end amz-nav-menu" aria-labelledby="myAccountInternalNavDropdownBtn">
                    <li class="dropdown-header">প্রধান ড্যাশবোর্ড</li>
                    <li>
                        <a class="dropdown-item active" href="javascript:void(0)" data-panel="hub" onclick="closeAllPanels()">
                            অ্যাকাউন্ট ড্যাশবোর্ড
                        </a>
                    </li>
                    <li class="dropdown-header mt-1">অর্ডার ও বই</li>
                    <li>
                        <a class="dropdown-item" href="javascript:void(0)" data-panel="orders" onclick="openSectionPanel('orders')">
                            অর্ডার ও ট্র্যাকিং
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="javascript:void(0)" data-panel="digitalServices" onclick="openSectionPanel('digitalServices')">
                            ই-বুক ও লাইব্রেরি
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="javascript:void(0)" data-panel="wishlist" onclick="openSectionPanel('wishlist')">
                            পছন্দের তালিকা
                        </a>
                    </li>
                    <li class="dropdown-header mt-1">প্রোফাইল ও সেটিংস</li>
                    <li>
                        <a class="dropdown-item" href="javascript:void(0)" data-panel="addresses" onclick="openSectionPanel('addresses')">
                            ডেলিভারি ঠিকানা
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="javascript:void(0)" data-panel="loginSecurity" onclick="openSectionPanel('loginSecurity')">
                            লগইন ও নিরাপত্তা
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="javascript:void(0)" data-panel="kyc" onclick="openSectionPanel('kyc')">
                            কেওয়াইসি ও ভেরিফিকেশন
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="javascript:void(0)" data-panel="payments" onclick="openSectionPanel('payments')">
                            ওয়ালেট ও রয়্যালটি
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="javascript:void(0)" data-panel="libraryGrant" onclick="openSectionPanel('libraryGrant')">
                            পাঠাগার ও বই অনুদান
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="javascript:void(0)" data-panel="customerService" onclick="openSectionPanel('customerService')">
                            গ্রাহক সেবা ও সহায়তা
                        </a>
                    </li>
                    @if(($isAuthor && $isApproved) || ($isPublisher && $isApproved) || ($isSeller && $isApproved) || $isAdmin)
                        <li><hr class="dropdown-divider my-1"></li>
                        <li class="dropdown-header">বিশেষ পোর্টাল</li>
                        @if($isAdmin)
                            <li><a class="dropdown-item fw-semibold text-danger" href="{{ url('/admin') }}">Admin Panel</a></li>
                        @endif
                        @if($isAuthor && $isApproved)
                            <li><a class="dropdown-item fw-semibold text-primary" href="{{ route('author.dashboard') }}">Author Studio</a></li>
                        @endif
                        @if($isPublisher && $isApproved)
                            <li><a class="dropdown-item fw-semibold text-success" href="{{ route('publisher.dashboard') }}">Publisher Portal</a></li>
                        @endif
                        @if($isSeller && $isApproved)
                            <li><a class="dropdown-item fw-semibold text-warning" href="{{ route('subadmin.dashboard') }}">Seller Panel</a></li>
                        @endif
                    @endif
                    <li><hr class="dropdown-divider my-1"></li>
                    <li>
                        <a class="dropdown-item text-danger fw-semibold" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('amzLogoutForm').submit();">
                            সাইন আউট
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        {{-- ═════════════════════════════════════════════════════════════════════ --}}
        {{-- VIEW 1: MAIN ACCOUNT HUB                                              --}}
        {{-- ═════════════════════════════════════════════════════════════════════ --}}
        <div id="mainAccountHubView" style="{{ $activeTab ? 'display: none !important;' : 'display: block !important;' }}">
            
            {{-- Clean Header --}}
            <div class="amz-header-row">
                <div>
                    <h1 class="amz-main-title">আমার অ্যাকাউন্ট</h1>
                </div>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    @if($isAdmin)
                        <a href="{{ url('/admin') }}" class="amz-btn-silver">Admin Panel</a>
                    @endif
                    @if($isAuthor && $isApproved && Route::has('author.dashboard'))
                        <a href="{{ route('author.dashboard') }}" class="amz-btn-silver">Author Studio</a>
                    @endif
                    @if($isPublisher && $isApproved && Route::has('publisher.dashboard'))
                        <a href="{{ route('publisher.dashboard') }}" class="amz-btn-silver">Publisher Portal</a>
                    @endif
                    @if(($isSeller || $user->isSeller() || $user->isSubAdmin()) && $isApproved && Route::has('subadmin.dashboard'))
                        <a href="{{ route('subadmin.dashboard') }}" class="amz-btn-silver">Seller Dashboard</a>
                    @endif
                    <a href="{{ route('logout') }}" class="amz-btn-silver" onclick="event.preventDefault(); document.getElementById('amzLogoutForm').submit();">
                        সাইন আউট
                    </a>
                    <form id="amzLogoutForm" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
                </div>
            </div>

            {{-- Minimalist Quick Stats Strip --}}
            <div class="amz-stats-strip">
                <div class="amz-stat-card" role="button" onclick="openSectionPanel('orders')">
                    <div class="amz-stat-value">{{ $totalOrdersCount ?? $myOrders->total() ?? 0 }}</div>
                    <div class="amz-stat-label">মোট অর্ডার</div>
                </div>
                <div class="amz-stat-card" role="button" onclick="openSectionPanel('digitalServices')">
                    <div class="amz-stat-value">{{ $myEbooks->count() }}</div>
                    <div class="amz-stat-label">ই-বুক সংগ্রহ</div>
                </div>
                <div class="amz-stat-card" role="button" onclick="openSectionPanel('wishlist')">
                    <div class="amz-stat-value">{{ $wishlistItems->count() }}</div>
                    <div class="amz-stat-label">পছন্দের বই</div>
                </div>
                <div class="amz-stat-card" role="button" onclick="openSectionPanel('payments')">
                    <div class="amz-stat-value">৳{{ number_format($walletBalance, 0) }}</div>
                    <div class="amz-stat-label">ওয়ালেট ও রয়্যালটি</div>
                </div>
            </div>

            {{-- Minimalist KYC Status Card --}}
            <div class="card p-3 p-md-4 mb-4" style="background: #ffffff;">
                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <h5 class="fw-semibold mb-0 text-dark" style="font-size: 15.5px;">কেওয়াইসি ও প্রোফাইল ভেরিফিকেশন</h5>
                            @if($kycPercent >= 100)
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-0.5" style="font-size: 11px;">সম্পন্ন</span>
                            @else
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2.5 py-0.5" style="font-size: 11px;">{{ $kycPercent }}% সম্পন্ন</span>
                            @endif
                        </div>
                        <p class="mb-2 text-muted small" style="font-size: 13px;">
                            @if($kycPercent >= 100)
                                আপনার অ্যাকাউন্ট প্রোফাইল তথ্য সম্পূর্ণ রয়েছে। প্রয়োজন অনুসারে যে কোনো সময় আপডেট করতে পারেন।
                            @else
                                তথ্য ও ভূমিকা আপডেট করলে ভেরিফায়েড সুবিধা, রয়্যালটি উত্তোলন এবং লেখক/প্রকাশক পোর্টাল কার্যকর হবে।
                            @endif
                        </p>
                        {{-- Minimalist Progress Bar --}}
                        <div class="progress" style="height: 6px; background-color: #f1f5f9; border-radius: 4px; max-width: 380px;">
                            <div class="progress-bar {{ $kycPercent >= 80 ? 'bg-success' : 'bg-warning' }}" role="progressbar" style="width: {{ $kycPercent }}%;" aria-valuenow="{{ $kycPercent }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                    <div>
                        <button type="button" class="amz-btn-silver" onclick="openSectionPanel('kyc')">
                            কেওয়াইসি আপডেট করুন
                        </button>
                    </div>
                </div>
            </div>

            {{-- ───────────────────────────────────────────────────────────────── --}}
            {{-- CLEAN & FUNCTIONAL FEATURE CARDS GRID (9 CLEAN CARDS)             --}}
            {{-- ───────────────────────────────────────────────────────────────── --}}
            <div class="amz-cards-grid">
                
                <!-- CARD 1: Your Orders -->
                <div class="amz-service-card" role="button" tabindex="0" onclick="openSectionPanel('orders')">
                    <div class="amz-icon-holder ic-orders">
                        <i class="fa-solid fa-box-archive"></i>
                    </div>
                    <div class="amz-card-text">
                        <div class="amz-card-headline">অর্ডার ও ট্র্যাকিং</div>
                        <p class="amz-card-summary">অর্ডার হিস্ট্রি, পার্সেল ট্র্যাকিং ও চালান ডাউনলোড</p>
                    </div>
                </div>

                <!-- CARD 2: Digital Library & E-Books -->
                <div class="amz-service-card" role="button" tabindex="0" onclick="openSectionPanel('digitalServices')">
                    <div class="amz-icon-holder ic-digital">
                        <i class="fa-solid fa-book-open"></i>
                    </div>
                    <div class="amz-card-text">
                        <div class="amz-card-headline">ই-বুক ও লাইব্রেরি</div>
                        <p class="amz-card-summary">সংগৃহীত ডিজিটাল বই পড়ুন ও লাইব্রেরি অ্যাক্সেস করুন</p>
                    </div>
                </div>

                <!-- CARD 3: Wishlist / Saved Books -->
                <div class="amz-service-card" role="button" tabindex="0" onclick="openSectionPanel('wishlist')">
                    <div class="amz-icon-holder ic-lists">
                        <i class="fa-solid fa-heart"></i>
                    </div>
                    <div class="amz-card-text">
                        <div class="amz-card-headline">পছন্দের তালিকা</div>
                        <p class="amz-card-summary">সংরক্ষণ করা বই ও পরবর্তীতে কেনার তালিকা</p>
                    </div>
                </div>

                <!-- CARD 4: Addresses -->
                <div class="amz-service-card" role="button" tabindex="0" onclick="openSectionPanel('addresses')">
                    <div class="amz-icon-holder ic-address">
                        <i class="fa-solid fa-location-dot"></i>
                    </div>
                    <div class="amz-card-text">
                        <div class="amz-card-headline">ডেলিভারি ঠিকানা</div>
                        <p class="amz-card-summary">বই ডেলিভারির ঠিকানা যোগ ও পরিবর্তন করুন</p>
                    </div>
                </div>

                <!-- CARD 5: Login & Security -->
                <div class="amz-service-card" role="button" tabindex="0" onclick="openSectionPanel('loginSecurity')">
                    <div class="amz-icon-holder ic-security">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                    <div class="amz-card-text">
                        <div class="amz-card-headline">লগইন ও নিরাপত্তা</div>
                        <p class="amz-card-summary">নাম, মোবাইল নম্বর, ইমেইল ও পাসওয়ার্ড পরিবর্তন</p>
                    </div>
                </div>

                <!-- CARD 6: KYC & Verification -->
                <div class="amz-service-card" role="button" tabindex="0" onclick="openSectionPanel('kyc')">
                    <div class="amz-icon-holder ic-kyc">
                        <i class="fa-solid fa-id-card"></i>
                    </div>
                    <div class="amz-card-text">
                        <div class="amz-card-headline">কেওয়াইসি ও ভেরিফিকেশন</div>
                        <p class="amz-card-summary">পরিচয় ও এনআইডি যাচাই</p>
                    </div>
                </div>

                <!-- CARD 7: Wallet & Royalties -->
                <div class="amz-service-card" role="button" tabindex="0" onclick="openSectionPanel('payments')">
                    <div class="amz-icon-holder ic-payment">
                        <i class="fa-solid fa-wallet"></i>
                    </div>
                    <div class="amz-card-text">
                        <div class="amz-card-headline">ওয়ালেট ও রয়্যালটি</div>
                        <p class="amz-card-summary">রয়্যালটি ও পেমেন্ট হিস্ট্রি</p>
                    </div>
                </div>

                <!-- CARD 8: Library Grant -->
                <div class="amz-service-card" role="button" tabindex="0" onclick="openSectionPanel('libraryGrant')">
                    <div class="amz-icon-holder ic-library">
                        <i class="fa-solid fa-book-open-reader"></i>
                    </div>
                    <div class="amz-card-text">
                        <div class="amz-card-headline">পাঠাগারে বই অনুদান</div>
                        <p class="amz-card-summary">বিনামূল্যে বই প্রাপ্তিস্বীকার এন্ট্রি</p>
                    </div>
                </div>

                <!-- CARD 9: Customer Service -->
                <div class="amz-service-card" role="button" tabindex="0" onclick="openSectionPanel('customerService')">
                    <div class="amz-icon-holder ic-contact">
                        <i class="fa-solid fa-headset"></i>
                    </div>
                    <div class="amz-card-text">
                        <div class="amz-card-headline">গ্রাহক সেবা ও সহায়তা</div>
                        <p class="amz-card-summary">সরাসরি হোয়াটসঅ্যাপ ও হেল্পলাইন সাপোর্ট</p>
                    </div>
                </div>

            </div>

        </div> {{-- END mainAccountHubView --}}

        {{-- ═════════════════════════════════════════════════════════════════════ --}}
        {{-- VIEW 2: SUB-PAGE PANELS                                               --}}
        {{-- ═════════════════════════════════════════════════════════════════════ --}}

        <!-- PANEL 1: Your Orders -->
        <div class="amz-subpage-panel {{ $activeTab === 'orders' ? 'active' : '' }}" id="panel_orders" style="{{ $activeTab === 'orders' ? 'display: block !important;' : 'display: none !important;' }}">
            <span class="amz-back-link" onclick="closeAllPanels()"><i class="fa-solid fa-arrow-left me-1"></i> অ্যাকাউন্ট ড্যাশবোর্ড</span>
            <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2 flex-wrap gap-2">
                <div>
                    <h2 class="amz-main-title">অর্ডার ও ট্র্যাকিং</h2>
                    <small class="text-muted">আপনার অর্ডারের বিবরণ, পার্সেল ট্র্যাকিং ও চালান</small>
                </div>
                <span class="badge bg-light text-dark border px-3 py-1.5 font-monospace">মোট অর্ডার: {{ $myOrders->total() ?? 0 }}</span>
            </div>

            @if($myOrders->count() > 0)
                <div class="d-flex flex-column gap-3">
                    @foreach($myOrders as $order)
                        <div class="amz-order-card">
                            <div class="amz-order-header">
                                <div>
                                    <span>অর্ডারের তারিখ</span>
                                    <strong>{{ $order->created_at->format('d M, Y') }}</strong>
                                </div>
                                <div>
                                    <span>মোট মূল্য</span>
                                    <strong>৳{{ number_format($order->total_amount ?? 0) }}</strong>
                                </div>
                                <div>
                                    <span>প্রাপক</span>
                                    <strong>{{ $order->customer_name ?: $user->name }}</strong>
                                </div>
                                <div class="text-end">
                                    <span>অর্ডার নম্বর: {{ $order->order_number ?? $order->id }}</span>
                                    <span class="badge bg-{{ $order->status === 'delivered' ? 'success' : ($order->status === 'cancelled' ? 'danger' : 'warning text-dark') }} ms-2">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </div>
                            </div>
                            <div class="amz-order-body">
                                <div class="d-flex align-items-center gap-3">
                                    <div style="width: 58px; height: 78px; background: #f1f5f9; border-radius: 6px; overflow: hidden; flex-shrink: 0;">
                                        @if($order->book && $order->book->cover_image)
                                            <img src="{{ str_starts_with($order->book->cover_image, 'http') ? $order->book->cover_image : asset('storage/' . ltrim($order->book->cover_image, '/')) }}" alt="{{ $order->book->title }}" class="w-100 h-100 object-fit-cover">
                                        @else
                                            <div class="w-100 h-100 d-flex align-items-center justify-content-center text-muted"><i class="fa-solid fa-book"></i></div>
                                        @endif
                                    </div>
                                    <div>
                                        <h4 class="fw-semibold text-dark mb-1" style="font-size: 14.5px;">{{ $order->book ? $order->book->title : 'বই অর্ডার #' . ($order->order_number ?? $order->id) }}</h4>
                                        <p class="text-secondary small mb-0">পরিমাণ: {{ $order->quantity ?? 1 }} কপি • মাধ্যম: {{ ucfirst($order->payment_method ?? 'Cash On Delivery') }}</p>
                                        <p class="text-secondary small mb-0">{{ $order->customer_address ?? $user->address }}</p>
                                    </div>
                                </div>
                                <div class="d-flex flex-column gap-2 text-end">
                                    <a href="{{ route('track-order', ['tracking_id' => $order->tracking_id ?? $order->order_number]) }}" class="amz-btn-gold">
                                        ট্র্যাক পার্সেল
                                    </a>
                                    @if($order->book)
                                        <a href="{{ route('book.show', $order->book->slug) }}" class="amz-btn-silver">
                                            আবার কিনুন
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <div class="mt-3">
                        {{ $myOrders->appends(request()->query())->links() }}
                    </div>
                </div>
            @else
                <div class="card p-5 text-center bg-white border rounded-3">
                    <h5 class="fw-semibold text-dark mb-1">কোনো অর্ডার পাওয়া যায়নি</h5>
                    <p class="text-muted small mb-3">আপনি এখনও কোনো বই অর্ডার করেননি।</p>
                    <div>
                        <a href="{{ route('book.index') }}" class="amz-btn-gold">
                            বই ব্রাউজ করুন
                        </a>
                    </div>
                </div>
            @endif
        </div>

        <!-- PANEL 2: Login & Security -->
        <div class="amz-subpage-panel {{ $activeTab === 'loginSecurity' ? 'active' : '' }}" id="panel_loginSecurity" style="{{ $activeTab === 'loginSecurity' ? 'display: block !important;' : 'display: none !important;' }}">
            <span class="amz-back-link" onclick="closeAllPanels()"><i class="fa-solid fa-arrow-left me-1"></i> অ্যাকাউন্ট ড্যাশবোর্ড</span>
            <h2 class="amz-main-title mb-3">লগইন ও নিরাপত্তা</h2>
            
            <div class="card p-4 border rounded-3 bg-white" style="max-width: 760px;">
                <div class="amz-security-row">
                    <div>
                        <div class="amz-security-label">নাম:</div>
                        <div class="amz-security-value">{{ $user->name }}</div>
                    </div>
                    <div>
                        <button type="button" class="amz-btn-silver" onclick="document.getElementById('editProfileBox').style.display='block'">সম্পাদনা</button>
                    </div>
                </div>

                <div class="amz-security-row">
                    <div>
                        <div class="amz-security-label">ইমেইল:</div>
                        <div class="amz-security-value">{{ str_contains($user->email ?? '', '@buyer.ideaabd.com') ? 'ইমেইল যুক্ত নেই' : $user->email }}</div>
                    </div>
                    <div>
                        <button type="button" class="amz-btn-silver" onclick="document.getElementById('editProfileBox').style.display='block'">সম্পাদনা</button>
                    </div>
                </div>

                <div class="amz-security-row">
                    <div>
                        <div class="amz-security-label">মোবাইল নম্বর:</div>
                        <div class="amz-security-value">{{ $user->phone }}</div>
                    </div>
                    <div>
                        <button type="button" class="amz-btn-silver" onclick="document.getElementById('editProfileBox').style.display='block'">সম্পাদনা</button>
                    </div>
                </div>

                <div class="amz-security-row">
                    <div>
                        <div class="amz-security-label">পাসওয়ার্ড:</div>
                        <div class="amz-security-value">••••••••••••</div>
                    </div>
                    <div>
                        <button type="button" class="amz-btn-silver" onclick="document.getElementById('editPasswordBox').style.display='block'">পরিবর্তন</button>
                    </div>
                </div>
            </div>

            {{-- Inline Edit Profile Box --}}
            <div id="editProfileBox" class="card p-4 border rounded-3 bg-white mt-3" style="display: none; max-width: 760px;">
                <h5 class="fw-semibold text-dark mb-3 border-bottom pb-2">নাম, ইমেইল ও ফোন নম্বর আপডেট</h5>
                <form action="{{ route('my-account.profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-dark">প্রোফাইল ছবি</label>
                        <input type="file" name="avatar" class="form-control" accept="image/*">
                        <small class="text-muted" style="font-size: 11px;">JPG, PNG, WebP ফরম্যাটে ছবি নির্বাচন করুন</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-dark">পূর্ণ নাম *</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-dark">মোবাইল ফোন নম্বর *</label>
                        <input type="text" name="phone" id="profilePhone" class="form-control" value="{{ old('phone', $user->phone) }}" data-original="{{ $user->phone }}" required>
                        @error('phone')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3" id="phoneOtpBox" style="display: none;">
                        <label class="form-label fw-semibold small text-dark">নতুন নম্বরের যাচাই কোড *</label>
                        <div class="input-group">
                            <input type="text" name="phone_otp" class="form-control" inputmode="numeric" maxlength="6" autocomplete="one-time-code">
                            <button type="button" class="btn btn-outline-secondary" id="phoneOtpSend" data-url="{{ route('my-account.phone.send-otp') }}" data-token="{{ csrf_token() }}">কোড পাঠান</button>
                        </div>
                        <small class="text-muted" id="phoneOtpMsg" style="font-size: 11px;">নম্বর বদলাতে নতুন নম্বরে পাঠানো ৬ সংখ্যার কোড লাগবে।</small>
                    </div>
                    <script>
                        (function () {
                            var phone = document.getElementById('profilePhone'), box = document.getElementById('phoneOtpBox');
                            var send = document.getElementById('phoneOtpSend'), msg = document.getElementById('phoneOtpMsg');
                            if (!phone || !box) return;
                            var digits = function (v) { return String(v || '').replace(/\D/g, '').slice(-10); };
                            var toggle = function () { box.style.display = digits(phone.value) !== digits(phone.dataset.original) ? '' : 'none'; };
                            phone.addEventListener('input', toggle); toggle();
                            send.addEventListener('click', function () {
                                send.disabled = true;
                                var fd = new FormData(); fd.append('_token', send.dataset.token); fd.append('phone', phone.value);
                                fetch(send.dataset.url, { method: 'POST', body: fd, headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
                                    .then(function (r) { return r.json().then(function (j) { msg.textContent = j.message || ''; }); })
                                    .catch(function () { msg.textContent = 'কোড পাঠানো যায়নি, আবার চেষ্টা করুন।'; })
                                    .then(function () { setTimeout(function () { send.disabled = false; }, 30000); });
                            });
                        })();
                    </script>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-dark">ইমেইল ঠিকানা</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', str_contains($user->email ?? '', '@buyer.ideaabd.com') ? '' : $user->email) }}">
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="amz-btn-gold">সংরক্ষণ করুন</button>
                        <button type="button" class="amz-btn-silver" onclick="document.getElementById('editProfileBox').style.display='none'">বাতিল</button>
                    </div>
                </form>
            </div>

            {{-- Inline Change Password Box --}}
            <div id="editPasswordBox" class="card p-4 border rounded-3 bg-white mt-3" style="display: none; max-width: 760px;">
                <h5 class="fw-semibold text-dark mb-3 border-bottom pb-2">পাসওয়ার্ড পরিবর্তন</h5>
                <form action="{{ route('my-account.password.update') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-dark">বর্তমান পাসওয়ার্ড *</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-dark">নতুন পাসওয়ার্ড *</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-dark">নতুন পাসওয়ার্ড নিশ্চিত করুন *</label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="amz-btn-gold">পাসওয়ার্ড পরিবর্তন করুন</button>
                        <button type="button" class="amz-btn-silver" onclick="document.getElementById('editPasswordBox').style.display='none'">বাতিল</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- PANEL 3: Delivery Addresses -->
        <div class="amz-subpage-panel {{ $activeTab === 'addresses' ? 'active' : '' }}" id="panel_addresses" style="{{ $activeTab === 'addresses' ? 'display: block !important;' : 'display: none !important;' }}">
            <span class="amz-back-link" onclick="closeAllPanels()"><i class="fa-solid fa-arrow-left me-1"></i> অ্যাকাউন্ট ড্যাশবোর্ড</span>
            <h2 class="amz-main-title mb-3">ডেলিভারি ঠিকানা</h2>

            <div class="amz-address-grid mb-4">
                <!-- Add Address Card -->
                <div class="amz-add-address-card" onclick="toggleAddressForm()">
                    <h5 class="fw-semibold text-dark mb-1" style="font-size: 15px;">নতুন ঠিকানা যোগ করুন</h5>
                    <span class="text-muted small">ক্লিক করে নতুন ডেলিভারি ঠিকানা যুক্ত করুন</span>
                </div>

                <!-- Default Address Card -->
                <div class="amz-address-card">
                    <span class="badge bg-light text-secondary border position-absolute top-0 end-0 m-3">ডিফল্ট</span>
                    <strong class="text-dark fs-6 d-block mb-1">{{ $defaultAddress['name'] ?? $user->name }}</strong>
                    <div class="text-secondary small mb-2">
                        {{ $defaultAddress['address'] ?: 'কোনো সড়ক ঠিকানা দেওয়া নেই' }}<br>
                        {{ $defaultAddress['thana'] ? $defaultAddress['thana'].', ' : '' }}{{ $defaultAddress['district'] }}<br>
                        ফোন: {{ $defaultAddress['phone'] ?? $user->phone }}
                    </div>
                    <div class="mt-auto pt-2 border-top">
                        <a href="javascript:void(0)" class="small text-decoration-none fw-semibold text-primary" onclick="toggleAddressForm()">সম্পাদনা</a>
                    </div>
                </div>
            </div>

            <!-- Address Form Box -->
            <div id="amzAddressFormBox" class="card p-4 border rounded-3 bg-white d-none" style="max-width: 700px;">
                <h5 class="fw-semibold text-dark mb-3 border-bottom pb-2">ডেলিভারি ঠিকানা আপডেট</h5>
                <form action="{{ route('my-account.address.update') }}" method="POST">
                    @csrf
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-dark">পূর্ণ নাম *</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $defaultAddress['name'] ?? $user->name) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-dark">ফোন নম্বর *</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone', $defaultAddress['phone'] ?? $user->phone) }}" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-dark">জেলা *</label>
                            <input type="text" name="district" class="form-control" placeholder="যেমন: ঢাকা বা রংপুর" value="{{ old('district', $defaultAddress['district'] ?? '') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-dark">থানা / উপজেলা</label>
                            <input type="text" name="thana" class="form-control" placeholder="যেমন: ধানমন্ডি" value="{{ old('thana', $defaultAddress['thana'] ?? '') }}">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-dark">বিস্তারিত ঠিকানা *</label>
                        <textarea name="address" class="form-control" rows="3" placeholder="বাড়ি/ফ্ল্যাট নং, রোড নাম, এলাকা..." required>{{ old('address', $defaultAddress['address'] ?? '') }}</textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="amz-btn-gold">ঠিকানা সংরক্ষণ করুন</button>
                        <button type="button" class="amz-btn-silver" onclick="toggleAddressForm()">বাতিল</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- PANEL 4: Digital Services & E-Books -->
        <div class="amz-subpage-panel {{ $activeTab === 'digitalServices' ? 'active' : '' }}" id="panel_digitalServices" style="{{ $activeTab === 'digitalServices' ? 'display: block !important;' : 'display: none !important;' }}">
            <span class="amz-back-link" onclick="closeAllPanels()"><i class="fa-solid fa-arrow-left me-1"></i> অ্যাকাউন্ট ড্যাশবোর্ড</span>
            <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2 flex-wrap gap-2">
                <div>
                    <h2 class="amz-main-title">ই-বুক ও ডিজিটাল লাইব্রেরি</h2>
                    <small class="text-muted">আপনার সংগৃহীত ও আনলক করা ই-বুকসমূহ</small>
                </div>
                <a href="{{ route('ebook.index') }}" class="amz-btn-gold">
                    ই-বুক ক্যাটালগ
                </a>
            </div>

            @if($myEbooks->count() > 0)
                <div class="row g-3">
                    @foreach($myEbooks as $item)
                        @if($item->ebook)
                            <div class="col-md-6 col-lg-4">
                                <div class="card p-3 border rounded-3 bg-white h-100">
                                    <div class="d-flex gap-3">
                                        <div style="width: 68px; height: 92px; background: #f1f5f9; border-radius: 6px; overflow: hidden; flex-shrink: 0;">
                                            @if($item->ebook->cover_image)
                                                <img src="{{ str_starts_with($item->ebook->cover_image, 'http') ? $item->ebook->cover_image : asset('storage/' . ltrim($item->ebook->cover_image, '/')) }}" alt="{{ $item->ebook->title }}" class="w-100 h-100 object-fit-cover">
                                            @else
                                                <div class="w-100 h-100 d-flex align-items-center justify-content-center text-muted"><i class="fa-solid fa-book"></i></div>
                                            @endif
                                        </div>
                                        <div class="flex-grow-1 min-w-0">
                                            <h4 class="fw-semibold text-dark mb-1 text-truncate" style="font-size: 14.5px;">{{ $item->ebook->title }}</h4>
                                            <small class="text-muted d-block mb-2">{{ $item->ebook->author_name ?? $item->ebook->author?->name ?? 'আইডিয়া প্রকাশন' }}</small>
                                            <a href="{{ route('ebook.read', $item->ebook->slug ?? $item->ebook->id) }}" class="amz-btn-gold">
                                                অনলাইনে পড়ুন
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            @else
                <div class="card p-5 text-center bg-white border rounded-3">
                    <h5 class="fw-semibold text-dark mb-1">কোনো ই-বুক পাওয়া যায়নি</h5>
                    <p class="text-muted small mb-3">আপনার অ্যাকাউন্টে এখনও কোনো ই-বুক যুক্ত করা হয়নি।</p>
                    <div>
                        <a href="{{ route('ebook.index') }}" class="amz-btn-gold">ই-বুক স্টোর দেখুন</a>
                    </div>
                </div>
            @endif
        </div>

        <!-- PANEL 5: Wishlist / Saved Books -->
        <div class="amz-subpage-panel {{ $activeTab === 'wishlist' ? 'active' : '' }}" id="panel_wishlist" style="{{ $activeTab === 'wishlist' ? 'display: block !important;' : 'display: none !important;' }}">
            <span class="amz-back-link" onclick="closeAllPanels()"><i class="fa-solid fa-arrow-left me-1"></i> অ্যাকাউন্ট ড্যাশবোর্ড</span>
            <h2 class="amz-main-title mb-3 border-bottom pb-2">পছন্দের তালিকা ও উইশলিস্ট</h2>
            
            @if($wishlistItems->count() > 0)
                <div class="row g-3">
                    @foreach($wishlistItems as $item)
                        @if($item->book)
                            <div class="col-md-6 col-lg-4">
                                <div class="card h-100 border rounded-3 p-3 bg-white">
                                    <div class="d-flex gap-3">
                                        <div style="width: 68px; height: 92px; background: #f1f5f9; border-radius: 6px; overflow: hidden; flex-shrink: 0;">
                                            @if($item->book->cover_image)
                                                <img src="{{ str_starts_with($item->book->cover_image, 'http') ? $item->book->cover_image : asset('storage/' . ltrim($item->book->cover_image, '/')) }}" alt="{{ $item->book->title }}" class="w-100 h-100 object-fit-cover">
                                            @else
                                                <div class="w-100 h-100 d-flex align-items-center justify-content-center text-muted"><i class="fa-solid fa-book"></i></div>
                                            @endif
                                        </div>
                                        <div class="flex-grow-1 min-w-0">
                                            <h4 class="fw-semibold text-dark mb-1 text-truncate" style="font-size: 14.5px;">{{ $item->book->title }}</h4>
                                            <small class="text-muted d-block mb-1">{{ $item->book->author_name ?? 'আইডিয়া প্রকাশন' }}</small>
                                            <div class="fw-bold text-dark mb-2">৳{{ number_format($item->book->discount_price ?: $item->book->price) }}</div>
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('book.show', $item->book->slug) }}" class="amz-btn-gold">বিস্তারিত</a>
                                                <form action="{{ route('my-account.wishlist.remove', $item->id) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="amz-btn-silver" title="তালিকা থেকে বাদ দিন">বাদ দিন</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            @else
                <div class="card p-5 text-center bg-white border rounded-3">
                    <h5 class="fw-semibold text-dark mb-1">আপনার পছন্দের তালিকা খালি</h5>
                    <p class="text-muted small mb-3">পছন্দের বইগুলো তালিকায় সংরক্ষণ করে রাখুন।</p>
                    <div><a href="{{ route('book.index') }}" class="amz-btn-gold">বই ব্রাউজ করুন</a></div>
                </div>
            @endif
        </div>

        <!-- PANEL 6: Payments & Royalties -->
        <div class="amz-subpage-panel {{ $activeTab === 'payments' ? 'active' : '' }}" id="panel_payments" style="{{ $activeTab === 'payments' ? 'display: block !important;' : 'display: none !important;' }}">
            <span class="amz-back-link" onclick="closeAllPanels()"><i class="fa-solid fa-arrow-left me-1"></i> অ্যাকাউন্ট ড্যাশবোর্ড</span>
            <h2 class="amz-main-title mb-3">ওয়ালেট ও রয়্যালটি</h2>
            
            <div class="card p-4 border rounded-3 bg-white" style="max-width: 760px;">
                <div class="d-flex align-items-center justify-content-between mb-4 border-bottom pb-3 flex-wrap gap-2">
                    <div>
                        <h4 class="fw-semibold mb-0 text-dark" style="font-size: 16px;">ওয়ালেট ব্যালেন্স</h4>
                        <small class="text-muted">উত্তোলনযোগ্য ব্যালেন্স ও রয়্যালটি হিসাব</small>
                    </div>
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1.5 rounded-pill fs-6 font-monospace">৳{{ number_format($walletBalance, 2) }}</span>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded-3 border">
                            <small class="text-muted d-block">পেমেন্ট মেথড</small>
                            <strong class="text-dark fs-6">{{ strtoupper($regData['payout_type'] ?? 'bKash') }}</strong>
                            <div class="text-secondary small">{{ $regData['payout_details'] ?? 'কোনো অ্যাকাউন্ট যুক্ত নেই' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded-3 border">
                            <small class="text-muted d-block">চলতি মাসের রয়্যালটি</small>
                            <strong class="text-success fs-6">৳{{ number_format($monthlyRoyalty, 2) }}</strong>
                            <div class="text-secondary small">{{ now()->format('F Y') }} এর হিসাব</div>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="button" class="amz-btn-gold" onclick="openSectionPanel('kyc')">
                        পেমেন্ট তথ্য আপডেট করুন
                    </button>
                    @if(Route::has('author.royalties'))
                        <a href="{{ route('author.royalties') }}" class="amz-btn-silver">
                            রয়্যালটি বিবরণী
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- PANEL 7: KYC Verification -->
        <div class="amz-subpage-panel {{ $activeTab === 'kyc' ? 'active' : '' }}" id="panel_kyc" style="{{ $activeTab === 'kyc' ? 'display: block !important;' : 'display: none !important;' }}">
            <span class="amz-back-link" onclick="closeAllPanels()"><i class="fa-solid fa-arrow-left me-1"></i> অ্যাকাউন্ট ড্যাশবোর্ড</span>
            <div class="card p-4 border rounded-3 bg-white" style="max-width: 820px;">
                <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-3 flex-wrap gap-2">
                    <div>
                        <h4 class="fw-semibold mb-0 text-dark" style="font-size: 17px;">কেওয়াইসি ও ভেরিফিকেশন</h4>
                        <small class="text-muted">আপনার পরিচয়, ভূমিকা ও যোগাযোগের তথ্য আপডেট করুন</small>
                    </div>
                    @if($isApproved)
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1.5 rounded-pill">ভেরিফায়েড অ্যাকাউন্ট</span>
                    @elseif($isPending)
                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-3 py-1.5 rounded-pill">পর্যালোচনায় রয়েছে</span>
                    @else
                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3 py-1.5 rounded-pill">অসম্পূর্ণ ({{ $kycPercent }}%)</span>
                    @endif
                </div>

                <form action="{{ route('my-account.kyc.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    {{-- Role Selection --}}
                    @php
                        $currentRole = $user->reg_type ?: ($user->role ?: 'buyer');
                        if ($currentRole === 'customer') $currentRole = 'buyer';
                    @endphp
                    <div class="p-3 bg-light rounded-3 border mb-4">
                        <label class="form-label fw-semibold text-dark small mb-1" for="kycRequestedRoleSelect">
                            আপনার ভূমিকা / কাঙ্ক্ষিত অ্যাকাউন্ট ক্যাটাগরি *
                        </label>
                        <select name="requested_role" id="kycRequestedRoleSelect" class="form-select fw-semibold" onchange="toggleKycRoleFields(this.value)">
                            <option value="buyer" @selected($currentRole === 'buyer')>১. সাধারণ পাঠক / ক্রেতা (Customer / Reader)</option>
                            <option value="author" @selected($currentRole === 'author')>২. লেখক ও গবেষক (Author & Researcher)</option>
                            <option value="publisher" @selected($currentRole === 'publisher')>৩. প্রকাশক ও প্রকাশনী (Publisher)</option>
                            <option value="seller" @selected($currentRole === 'seller')>৪. সেলার / বুকশপ বিক্রেতা (Seller / Bookshop)</option>
                        </select>
                        <small class="text-muted d-block mt-1.5" style="font-size: 12px;">
                            সাধারণ পাঠক হিসেবে কেনাকাটা ছাড়াও লেখক, প্রকাশক বা সেলার হিসেবে যুক্ত হতে চাইলে ভূমিকা নির্বাচন করে তথ্য জমা দিন।
                        </small>
                    </div>

                    {{-- Profile Photo --}}
                    <div class="mb-4 d-flex align-items-center gap-3 p-3 rounded-3 border bg-light">
                        <div style="width: 64px; height: 64px; border-radius: 50%; overflow: hidden; background: #e2e8f0; border: 1.5px solid #cbd5e1; flex-shrink: 0;" class="d-flex align-items-center justify-content-center">
                            @if($user->avatar)
                                <img src="{{ str_starts_with($user->avatar, 'http') ? $user->avatar : asset('storage/' . ltrim($user->avatar, '/')) }}" alt="{{ $user->name }}" id="kycAvatarPreview" class="w-100 h-100 object-fit-cover">
                            @else
                                <img id="kycAvatarPreview" class="w-100 h-100 object-fit-cover d-none" alt="Preview">
                                <span id="kycAvatarPlaceholder" class="fs-4 text-secondary fw-semibold">{{ mb_substr($user->name, 0, 1) }}</span>
                            @endif
                        </div>
                        <div class="flex-grow-1">
                            <label class="form-label fw-semibold text-dark small mb-1">প্রোফাইল ছবি</label>
                            <input type="file" name="avatar" class="form-control form-control-sm" accept="image/*" onchange="previewKycPhoto(this)">
                            <small class="text-muted" style="font-size: 11px;">JPG, PNG, WebP ফরম্যাটে সর্বোচ্চ ৫MB</small>
                        </div>
                    </div>

                    {{-- Names --}}
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-dark small">পুরো নাম (বাংলায়)</label>
                            <input type="text" name="name_bn" class="form-control" placeholder="যেমন: শাকিল মাসুদ" value="{{ old('name_bn', $regData['name_bn'] ?? $user->name) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-dark small">Full Name (English)</label>
                            <input type="text" name="name_en" class="form-control" placeholder="e.g. Shakil Masud" value="{{ old('name_en', $regData['name_en'] ?? '') }}">
                        </div>
                    </div>

                    {{-- Author Fields --}}
                    <div id="kycAuthorFields" class="{{ $currentRole === 'author' ? '' : 'd-none' }} p-3 rounded-3 mb-3 border bg-light">
                        <div class="mb-2 pb-1 border-bottom">
                            <strong class="text-dark small">লেখক প্রোফাইল ও সাহিত্য পরিচিতি</strong>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark small">কলমনাম / ছদ্মনাম (ঐচ্ছিক)</label>
                            <input type="text" name="pen_name" class="form-control" placeholder="যেমন: বনফুল" value="{{ old('pen_name', $regData['pen_name'] ?? '') }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark small">সাহিত্য শাখা (Genres)</label>
                            <input type="text" name="genres[]" class="form-control" placeholder="যেমন: কবিতা, কথাসাহিত্য, ইতিহাস..." value="{{ old('genres', is_array($regData['genres'] ?? null) ? implode(', ', $regData['genres']) : ($regData['genres'] ?? '')) }}">
                        </div>
                        <div class="mb-1">
                            <label class="form-label fw-semibold text-dark small">লেখক পরিচিতি / বায়ো</label>
                            <textarea name="bio" class="form-control" rows="3" placeholder="আপনার সাহিত্যচর্চা ও প্রকাশিত গ্রন্থের বিবরণ...">{{ old('bio', $regData['bio'] ?? '') }}</textarea>
                        </div>
                    </div>

                    {{-- Publisher Fields --}}
                    <div id="kycPublisherFields" class="{{ $currentRole === 'publisher' ? '' : 'd-none' }} p-3 rounded-3 mb-3 border bg-light">
                        <div class="mb-2 pb-1 border-bottom">
                            <strong class="text-dark small">প্রকাশক ও প্রকাশনা প্রতিষ্ঠানের বিবরণ</strong>
                        </div>
                        <div class="row g-3 mb-2">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark small">প্রকাশনীর নাম</label>
                                <input type="text" name="publisher_name" class="form-control" placeholder="যেমন: আইডিয়া প্রকাশন" value="{{ old('publisher_name', $regData['publisher_name'] ?? ($regData['publishing_house_name'] ?? '')) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark small">প্রতিষ্ঠার সাল</label>
                                <input type="text" name="established" class="form-control" placeholder="যেমন: 2015" value="{{ old('established', $regData['established'] ?? '') }}">
                            </div>
                        </div>
                        <div class="mb-1">
                            <label class="form-label fw-semibold text-dark small">ট্রেড লাইসেন্স বা নিবন্ধন নম্বর</label>
                            <input type="text" name="trade_license" class="form-control" placeholder="e.g. TRAD/12345" value="{{ old('trade_license', $regData['trade_license'] ?? '') }}">
                        </div>
                    </div>

                    {{-- Seller Fields --}}
                    <div id="kycSellerFields" class="{{ $currentRole === 'seller' ? '' : 'd-none' }} p-3 rounded-3 mb-3 border bg-light">
                        <div class="mb-2 pb-1 border-bottom">
                            <strong class="text-dark small">বুকশপ ও বিক্রেতা বিবরণ</strong>
                        </div>
                        <div class="row g-3 mb-2">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark small">দোকান / বুকশপের নাম</label>
                                <input type="text" name="shop_name" class="form-control" placeholder="যেমন: আল-মদিনা বুক সেন্টার" value="{{ old('shop_name', $regData['shop_name'] ?? '') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark small">ট্রেড লাইসেন্স নম্বর</label>
                                <input type="text" name="trade_license" class="form-control" placeholder="e.g. TRAD/XYZ/12345" value="{{ old('trade_license', $regData['trade_license'] ?? '') }}">
                            </div>
                        </div>
                    </div>

                    {{-- Delivery & Business Location Address --}}
                    <div class="mb-3">
                        <div class="fw-semibold text-dark small mb-2">যোগাযোগের ঠিকানা</div>
                        <div class="row g-2 mb-2">
                            <div class="col-md-6">
                                <label class="form-label small text-muted">জেলা</label>
                                <input type="text" name="district" class="form-control" placeholder="যেমন: ঢাকা" value="{{ old('district', $regData['district'] ?? ($defaultAddress['district'] ?? '')) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small text-muted">থানা / উপজেলা</label>
                                <input type="text" name="thana" class="form-control" placeholder="যেমন: ধানমন্ডি" value="{{ old('thana', $regData['thana'] ?? ($defaultAddress['thana'] ?? '')) }}">
                            </div>
                        </div>
                        <div>
                            <label class="form-label small text-muted">বিস্তারিত সড়ক ঠিকানা</label>
                            <textarea name="address" class="form-control" rows="2" placeholder="বাড়ি নং, রোড নং, এলাকা...">{{ old('address', $regData['address'] ?? ($defaultAddress['address'] ?? '')) }}</textarea>
                        </div>
                    </div>

                    {{-- National ID Verification --}}
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-dark small">জাতীয় পরিচয়পত্র (NID) নম্বর</label>
                            <input type="text" name="nid" class="form-control" placeholder="১০ বা ১৭ ডিজিটের নম্বর" value="{{ old('nid', $regData['nid'] ?? '') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-dark small">NID কার্ডের ফাইল (PDF/Image)</label>
                            <input type="file" name="nid_file" class="form-control" accept="image/*,.pdf">
                            @if(!empty($regData['nid_file']))
                                <small class="text-success d-block mt-1">ডকুমেন্ট পূর্বে আপলোড করা হয়েছে</small>
                            @endif
                        </div>
                    </div>

                    {{-- Payout Settings --}}
                    <div class="row g-3 mb-4">
                        <div class="col-md-5">
                            <label class="form-label fw-semibold text-dark small">রয়্যালটি গ্রহণের মাধ্যম</label>
                            <select name="payout_account_type" class="form-select">
                                <option value="bkash" {{ ($regData['payout_type'] ?? '') === 'bkash' ? 'selected' : '' }}>বিকাশ (bKash)</option>
                                <option value="nagad" {{ ($regData['payout_type'] ?? '') === 'nagad' ? 'selected' : '' }}>নগদ (Nagad)</option>
                                <option value="rocket" {{ ($regData['payout_type'] ?? '') === 'rocket' ? 'selected' : '' }}>রকেট (Rocket)</option>
                                <option value="bank" {{ ($regData['payout_type'] ?? '') === 'bank' ? 'selected' : '' }}>ব্যাংক অ্যাকাউন্ট (Bank)</option>
                            </select>
                        </div>
                        <div class="col-md-7">
                            <label class="form-label fw-semibold text-dark small">অ্যাকাউন্ট নম্বর ও বিবরণ</label>
                            <input type="text" name="payout_account_details" class="form-control" placeholder="যেমন: 017XXXXXXXX বা ব্যাংক ও অ্যাকাউন্ট নং" value="{{ old('payout_account_details', $regData['payout_details'] ?? '') }}">
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <button type="submit" class="amz-btn-gold">
                            কেওয়াইসি তথ্য সংরক্ষণ করুন
                        </button>
                        <button type="button" class="amz-btn-silver" onclick="closeAllPanels()">
                            বাতিল
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- PANEL 8: Library Grant -->
        <div id="panel-libraryGrant" class="amz-section-panel d-none">
            <span class="amz-back-link" onclick="closeAllPanels()"><i class="fa-solid fa-arrow-left me-1"></i> অ্যাকাউন্ট ড্যাশবোর্ড</span>
            <div class="border-bottom pb-2 mb-3">
                <h2 class="amz-main-title">পাঠাগার ও বাৎসরিক বই অনুদান</h2>
                <p class="text-muted small mb-0">আইডিয়া প্রকাশনের বাৎসরিক বিনামূল্যে বই বিতরণ কর্মসূচি ও প্রাপ্তিস্বীকার</p>
            </div>

            <div class="p-0">
                @if(isset($myLibraryRegistrations) && $myLibraryRegistrations->isNotEmpty())
                    <div class="d-flex flex-column gap-3">
                        @foreach($myLibraryRegistrations as $reg)
                            @php
                                $fd = $reg->form_data ?? [];
                                $allocated = intval($fd['books_allocated'] ?? 0);
                                $dispatchedDate = $fd['dispatched_date'] ?? null;
                                $receivedCount = intval($fd['received_books_count'] ?? 0);
                                $receivedDate = $fd['received_date'] ?? null;
                                $isAck = $reg->isAcknowledged();
                                $libName = $reg->institution_or_org ?: ($fd['library_name'] ?? 'পাঠাগার');
                            @endphp
                            <div class="card border rounded-3 p-4 bg-white">
                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                                    <div>
                                        <h5 class="fw-semibold mb-1 text-dark" style="font-size: 16px;">{{ $libName }}</h5>
                                        <div class="d-flex align-items-center gap-2 flex-wrap small">
                                            <span class="badge bg-light text-secondary border">#{{ $reg->registration_number }}</span>
                                            <span class="text-muted">{{ $reg->district ?? '' }}{{ $reg->thana ? ', ' . $reg->thana : '' }}</span>
                                        </div>
                                    </div>
                                    <div>
                                        @if(in_array($reg->status, ['confirmed', 'selected', 'approved']))
                                            <span class="badge bg-success-subtle text-success border px-3 py-1.5 rounded-pill fw-semibold">
                                                অনুমোদিত
                                            </span>
                                        @else
                                            <span class="badge bg-warning-subtle text-warning-emphasis border px-3 py-1.5 rounded-pill fw-semibold">
                                                অপেক্ষমাণ
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="row g-3 p-3 rounded-3 bg-light mb-3">
                                    <div class="col-6 col-md-3">
                                        <span class="text-muted small d-block">বরাদ্দকৃত বই</span>
                                        <strong class="text-success fs-6">{{ $allocated > 0 ? $allocated . ' টি' : 'যাচাইাধীন' }}</strong>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <span class="text-muted small d-block">বিতরণের তারিখ</span>
                                        <strong class="text-dark">{{ $dispatchedDate ? date('d M, Y', strtotime($dispatchedDate)) : '—' }}</strong>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <span class="text-muted small d-block">প্রাপ্তিস্বীকার</span>
                                        @if($isAck)
                                            <span class="badge bg-success text-white">নিশ্চিত ({{ $receivedCount ?: $allocated }} টি)</span>
                                        @elseif($allocated > 0)
                                            <span class="badge bg-warning text-dark">প্রেরিত (প্রাপ্তিস্বীকার দিন)</span>
                                        @else
                                            <span class="badge bg-secondary text-white">অপেক্ষমাণ</span>
                                        @endif
                                    </div>
                                    <div class="col-6 col-md-3 text-md-end">
                                        <a href="{{ route('event.registration.print', $reg->registration_number) }}" target="_blank" class="amz-btn-silver">
                                            স্লিপ ডাউনলোড
                                        </a>
                                    </div>
                                </div>

                                @if(!$isAck)
                                    <div class="border-top pt-3 mt-2">
                                        <h6 class="fw-semibold text-dark mb-2">বই প্রাপ্তিস্বীকার নিশ্চিতকরণ</h6>
                                        <form action="{{ url('/pathagar/acknowledgment/' . $reg->registration_number) }}" method="POST" class="row g-2 align-items-center">
                                            @csrf
                                            <div class="col-md-3">
                                                <input type="number" name="received_books_count" class="form-control form-control-sm" placeholder="প্রাপ্ত বই সংখ্যা" value="{{ $allocated > 0 ? $allocated : '' }}" required min="1">
                                            </div>
                                            <div class="col-md-3">
                                                <input type="date" name="received_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
                                            </div>
                                            <div class="col-md-4">
                                                <input type="text" name="acknowledgment_notes" class="form-control form-control-sm" placeholder="মন্তব্য (ঐচ্ছিক)">
                                            </div>
                                            <div class="col-md-2">
                                                <button type="submit" class="amz-btn-gold w-100">
                                                    জমা দিন
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                @else
                                    <div class="alert alert-success rounded-3 p-2.5 small mb-0">
                                        {{ $receivedDate ? date('d M, Y', strtotime($receivedDate)) : '' }} তারিখে <strong>{{ $receivedCount ?: $allocated }} টি বই</strong> প্রাপ্তিস্বীকার নিশ্চিত করা হয়েছে।
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="card p-5 text-center bg-white border rounded-3">
                        <h5 class="fw-semibold text-dark mb-1">কোনো পাঠাগার আবেদন পাওয়া যায়নি</h5>
                        <p class="text-muted small mb-3">আপনার পাঠাগারের জন্য বাৎসরিক বিনামূল্যে বই বিতরণ কর্মসূচিতে আবেদন করতে পারেন।</p>
                        <div>
                            <a href="{{ url('/pathagar') }}" target="_blank" class="amz-btn-gold">
                                পাঠাগার আবেদন করুন
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- PANEL 9: Customer Service -->
        <div class="amz-subpage-panel {{ $activeTab === 'customerService' ? 'active' : '' }}" id="panel_customerService" style="{{ $activeTab === 'customerService' ? 'display: block !important;' : 'display: none !important;' }}">
            <span class="amz-back-link" onclick="closeAllPanels()"><i class="fa-solid fa-arrow-left me-1"></i> অ্যাকাউন্ট ড্যাশবোর্ড</span>
            <h2 class="amz-main-title mb-3 border-bottom pb-2">গ্রাহক সেবা ও সহায়তা</h2>
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="card p-4 border rounded-3 text-center bg-white h-100">
                        <h6 class="fw-semibold mb-1 text-dark">সরাসরি ফোন কল</h6>
                        <small class="text-muted d-block mb-3">কাস্টমার কেয়ার প্রতিনিধির সাথে কথা বলুন</small>
                        <a href="tel:+8801558712810" class="amz-btn-silver mt-auto">+8801558712810</a>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card p-4 border rounded-3 text-center bg-white h-100">
                        <h6 class="fw-semibold mb-1 text-dark">হোয়াটসঅ্যাপ সাপোর্ট</h6>
                        <small class="text-muted d-block mb-3">দ্রুত মেসেজে সাহায্য পেতে মেসেজ দিন</small>
                        <a href="https://api.whatsapp.com/send?phone=8801558712810" target="_blank" class="amz-btn-gold mt-auto">WhatsApp মেসেজ</a>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card p-4 border rounded-3 text-center bg-white h-100">
                        <h6 class="fw-semibold mb-1 text-dark">ইমেইল ও মেসেজ হেল্পডেস্ক</h6>
                        <small class="text-muted d-block mb-3">অনলাইন ফর্মের মাধ্যমে বিস্তারিত অনুরোধ পাঠান</small>
                        <a href="{{ route('contact') }}" class="amz-btn-silver mt-auto">যোগাযোগ ফর্ম</a>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

@push('scripts')
<script src="{{ asset('js/my-account.js') }}?v={{ @filemtime(public_path('js/my-account.js')) ?: time() }}"></script>
@endpush
@endsection
