@extends('layouts.app')

@section('title', 'Account')

@push('head')
<link rel="stylesheet" href="{{ asset('css/my-account.css') }}?v={{ @filemtime(public_path('css/my-account.css')) ?: time() }}">
@endpush

@section('content')
<link rel="stylesheet" href="{{ asset('css/my-account.css') }}?v={{ @filemtime(public_path('css/my-account.css')) ?: time() }}">

<div class="amz-page-wrapper">
    <div class="amz-container">

        {{-- Flash Alert Notifications --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-3 p-3 mb-3 border-0 shadow-xs" style="background: #ecfdf5; color: #065f46; border-left: 4px solid #10b981 !important;">
                <i class="fa-solid fa-circle-check me-2 text-success"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show rounded-3 p-3 mb-3 border-0 shadow-xs" style="background: #fef2f2; color: #991b1b; border-left: 4px solid #ef4444 !important;">
                <i class="fa-solid fa-triangle-exclamation me-2 text-danger"></i> {{ session('error') }}
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

        {{-- Pending Approval Alert for Author / Publisher / Seller Accounts --}}
        @if(($isAuthor || $isPublisher || $isSeller) && !$isApproved)
            <div class="alert alert-warning border-0 rounded-4 p-3.5 mb-4 shadow-sm d-flex align-items-start gap-3" style="background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%); border-left: 5px solid #f59e0b !important;">
                <div class="rounded-circle bg-warning text-white p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px;">
                    <i class="fa-solid fa-hourglass-half fs-5 text-dark"></i>
                </div>
                <div class="flex-grow-1">
                    <h5 class="fw-bold mb-1 text-dark" style="font-size: 15.5px;">
                        আপনার {{ $isAuthor ? 'লেখক (Author)' : ($isPublisher ? 'প্রকাশক (Publisher)' : 'সেলার (Seller)') }} অ্যাকাউন্টটি অনুমোদনের অপেক্ষায় রয়েছে (Pending Approval)
                    </h5>
                    <p class="mb-0 text-muted small lh-base" style="font-size: 13px;">
                        আপনার আবেদনটি এডমিন টিম কর্তৃক পর্যালোচনায় রয়েছে। অনুমোদন সম্পন্ন হলে আপনার বিশেষায়িত ড্যাশবোর্ড ও ম্যানেজমেন্ট পোর্টাল স্বয়ংক্রিয়ভাবে সক্রিয় হবে। 
                        <strong class="text-dark">বর্তমানে আপনি সাধারণ গ্রাহক হিসেবে বই ব্রাউজিং, শপিং কার্ট, কেনাকাটা, অর্ডার ট্র্যাকিং এবং ব্যক্তিগত অ্যাকাউন্ট সুবিধা ব্যবহার করতে পারছেন।</strong>
                    </p>
                </div>
            </div>
        @endif

        {{-- ═════════════════════════════════════════════════════════════════════ --}}
        {{-- MY ACCOUNT QUICK-JUMP DROPDOWN NAVIGATION BAR                         --}}
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
                    <div class="fw-bold text-dark lh-1" style="font-size: 14px;">স্বাগতম, {{ $user->name }}</div>
                    <div class="text-muted small" style="font-size: 11px;">
                        @if($isAdmin)
                            <span class="badge bg-danger px-2 py-0.5 rounded-pill">👑 অ্যাডমিন</span>
                        @elseif($isAuthor)
                            <span class="badge {{ $isApproved ? 'bg-primary' : 'bg-warning text-dark' }} px-2 py-0.5 rounded-pill">
                                ✍️ লেখক {{ $isApproved ? 'Studio' : '(অপেক্ষমাণ)' }}
                            </span>
                        @elseif($isPublisher)
                            <span class="badge {{ $isApproved ? 'bg-success' : 'bg-warning text-dark' }} px-2 py-0.5 rounded-pill">
                                🏢 প্রকাশক {{ $isApproved ? 'Portal' : '(অপেক্ষমাণ)' }}
                            </span>
                        @elseif($isSeller)
                            <span class="badge {{ $isApproved ? 'bg-info text-dark' : 'bg-warning text-dark' }} px-2 py-0.5 rounded-pill">
                                💼 সেলার {{ $isApproved ? 'Panel' : '(অপেক্ষমাণ)' }}
                            </span>
                        @else
                            <span class="badge bg-secondary px-2 py-0.5 rounded-pill">👤 কাস্টমার একাউন্ট</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Responsive Dropdown Navigation Controller --}}
            <div class="dropdown">
                <button class="amz-nav-dropdown-btn dropdown-toggle" type="button" id="myAccountInternalNavDropdownBtn" data-bs-toggle="dropdown" aria-expanded="false" title="My Account Menu Options">
                    <i class="fa-solid fa-house text-primary" id="currentAccountNavIcon"></i>
                    <span id="currentAccountNavLabel">Your Account Hub</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end amz-nav-menu shadow-2xl" aria-labelledby="myAccountInternalNavDropdownBtn">
                    <li class="dropdown-header">Primary Hub</li>
                    <li>
                        <a class="dropdown-item active" href="javascript:void(0)" data-panel="hub" onclick="closeAllPanels()">
                            <i class="fa-solid fa-house text-primary" style="width: 18px;"></i>
                            <span>Your Account Hub</span>
                        </a>
                    </li>
                    <li class="dropdown-header mt-1">Orders & Shopping</li>
                    <li>
                        <a class="dropdown-item" href="javascript:void(0)" data-panel="orders" onclick="openSectionPanel('orders')">
                            <i class="fa-solid fa-box-archive text-info" style="width: 18px;"></i>
                            <span>Your Orders</span>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="javascript:void(0)" data-panel="digitalServices" onclick="openSectionPanel('digitalServices')">
                            <i class="fa-solid fa-book-open-reader text-success" style="width: 18px;"></i>
                            <span>Digital Library & E-Books</span>
                        </a>
                    </li>
                    <li class="dropdown-header mt-1">Account & Security</li>
                    <li>
                        <a class="dropdown-item" href="javascript:void(0)" data-panel="loginSecurity" onclick="openSectionPanel('loginSecurity')">
                            <i class="fa-solid fa-shield-halved text-success" style="width: 18px;"></i>
                            <span>Login & Security</span>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="javascript:void(0)" data-panel="addresses" onclick="openSectionPanel('addresses')">
                            <i class="fa-solid fa-location-dot text-danger" style="width: 18px;"></i>
                            <span>Your Addresses</span>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="javascript:void(0)" data-panel="payments" onclick="openSectionPanel('payments')">
                            <i class="fa-solid fa-credit-card text-purple" style="width: 18px; color: #8b5cf6;"></i>
                            <span>Your Payments</span>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="javascript:void(0)" data-panel="prime" onclick="openSectionPanel('prime')">
                            <i class="fa-solid fa-crown text-warning" style="width: 18px;"></i>
                            <span>Prime Membership</span>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="javascript:void(0)" data-panel="giftcards" onclick="openSectionPanel('giftcards')">
                            <i class="fa-solid fa-gift text-danger" style="width: 18px;"></i>
                            <span>Gift Cards & Balance</span>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="javascript:void(0)" data-panel="kyc" onclick="openSectionPanel('kyc')">
                            <i class="fa-solid fa-id-card text-secondary" style="width: 18px;"></i>
                            <span>KYC & Verification</span>
                        </a>
                    </li>
                    @if($isAuthor)
                        <li class="dropdown-header mt-1">Author Services</li>
                        <li>
                            <a class="dropdown-item" href="javascript:void(0)" data-panel="royalties" onclick="openSectionPanel('royalties')">
                                <i class="fa-solid fa-sack-dollar text-warning" style="width: 18px;"></i>
                                <span>Royalties & Payouts</span>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="javascript:void(0)" data-panel="blog" onclick="openSectionPanel('blog')">
                                <i class="fa-solid fa-pen-nib text-primary" style="width: 18px;"></i>
                                <span>Author Articles & Blog</span>
                            </a>
                        </li>
                    @endif
                    <li class="dropdown-header mt-1">Support & Settings</li>
                    <li>
                        <a class="dropdown-item" href="javascript:void(0)" data-panel="customerService" onclick="openSectionPanel('customerService')">
                            <i class="fa-solid fa-headset text-success" style="width: 18px;"></i>
                            <span>Customer Service & Help</span>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="javascript:void(0)" data-panel="preferences" onclick="openSectionPanel('preferences')">
                            <i class="fa-solid fa-globe text-muted" style="width: 18px;"></i>
                            <span>Language & Preferences</span>
                        </a>
                    </li>
                    @if(($isAuthor && $isApproved) || ($isPublisher && $isApproved) || ($isSeller && $isApproved) || $isAdmin)
                        <li><hr class="dropdown-divider my-1"></li>
                        <li class="dropdown-header">Special Portals</li>
                        @if($isAdmin)
                            <li><a class="dropdown-item fw-bold text-danger" href="{{ url('/admin') }}"><i class="fa-solid fa-shield-halved" style="width: 18px;"></i> Admin Panel</a></li>
                        @endif
                        @if($isAuthor && $isApproved)
                            <li><a class="dropdown-item fw-bold text-primary" href="{{ route('author.dashboard') }}"><i class="fa-solid fa-feather-pointed" style="width: 18px;"></i> Author Studio</a></li>
                        @endif
                        @if($isPublisher && $isApproved)
                            <li><a class="dropdown-item fw-bold text-success" href="{{ route('publisher.dashboard') }}"><i class="fa-solid fa-building" style="width: 18px;"></i> Publisher Portal</a></li>
                        @endif
                        @if($isSeller && $isApproved)
                            <li><a class="dropdown-item fw-bold text-warning" href="{{ route('subadmin.dashboard') }}"><i class="fa-solid fa-store" style="width: 18px;"></i> Seller Panel</a></li>
                        @endif
                    @endif
                    <li><hr class="dropdown-divider my-1"></li>
                    <li>
                        <a class="dropdown-item text-danger fw-bold" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('amzLogoutForm').submit();">
                            <i class="fa-solid fa-power-off text-danger" style="width: 18px;"></i>
                            <span>Sign Out</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        {{-- ═════════════════════════════════════════════════════════════════════ --}}
        {{-- VIEW 1: AUTHENTIC AMAZON "YOUR ACCOUNT" HOME HUB                      --}}
        {{-- ═════════════════════════════════════════════════════════════════════ --}}
        <div id="mainAccountHubView" style="{{ $activeTab ? 'display: none !important;' : 'display: block !important;' }}">
            
            {{-- Amazon Main Header --}}
            <div class="amz-header-row border-0 mb-4 pb-0">
                <div>
                    <h1 class="amz-main-title" style="font-size: 28px; font-weight: 500;">Your Account</h1>
                </div>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    @if($isAdmin)
                        <a href="{{ url('/admin') }}" class="amz-btn-silver" title="Admin Panel">
                            <i class="fa-solid fa-shield-halved me-1 text-danger"></i> Admin Panel
                        </a>
                    @endif
                    @if($isAuthor && $isApproved && Route::has('author.dashboard'))
                        <a href="{{ route('author.dashboard') }}" class="amz-btn-silver" title="Author Studio">
                            <i class="fa-solid fa-feather-pointed me-1 text-primary"></i> Author Studio
                        </a>
                    @endif
                    @if($isPublisher && $isApproved && Route::has('publisher.dashboard'))
                        <a href="{{ route('publisher.dashboard') }}" class="amz-btn-silver" title="Publisher Portal">
                            <i class="fa-solid fa-building me-1 text-success"></i> Publisher Portal
                        </a>
                    @endif
                    @if(($isSeller || $user->isSeller() || $user->isSubAdmin()) && $isApproved && Route::has('subadmin.dashboard'))
                        <a href="{{ route('subadmin.dashboard') }}" class="amz-btn-silver" title="Seller Dashboard">
                            <i class="fa-solid fa-store me-1 text-warning"></i> Seller Dashboard
                        </a>
                    @endif
                    <a href="{{ route('logout') }}" class="amz-btn-silver" onclick="event.preventDefault(); document.getElementById('amzLogoutForm').submit();">
                        <i class="fa-solid fa-power-off me-1 text-muted"></i> Sign Out
                    </a>
                    <form id="amzLogoutForm" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
                </div>
            </div>

            {{-- ───────────────────────────────────────────────────────────────── --}}
            {{-- THE SIGNATURE AMAZON 3-COLUMN SERVICE CARDS GRID (12 CARDS)        --}}
            {{-- ───────────────────────────────────────────────────────────────── --}}
            <div class="amz-cards-grid">
                
                <!-- CARD 1: Your Orders -->
                <div class="amz-service-card" role="button" tabindex="0" onclick="openSectionPanel('orders')" onkeydown="if(event.key==='Enter'||event.key===' ')openSectionPanel('orders')">
                    <div class="amz-icon-holder ic-orders">
                        <i class="fa-solid fa-box-archive" style="font-size: 26px;"></i>
                    </div>
                    <div class="amz-card-text">
                        <div class="amz-card-headline">Your Orders</div>
                        <p class="amz-card-summary">Track, return, cancel an order, download invoice or buy again</p>
                    </div>
                </div>

                <!-- CARD 2: Login & security -->
                <div class="amz-service-card" role="button" tabindex="0" onclick="openSectionPanel('loginSecurity')" onkeydown="if(event.key==='Enter'||event.key===' ')openSectionPanel('loginSecurity')">
                    <div class="amz-icon-holder ic-security">
                        <i class="fa-solid fa-shield-halved" style="font-size: 26px;"></i>
                    </div>
                    <div class="amz-card-text">
                        <div class="amz-card-headline">Login & security</div>
                        <p class="amz-card-summary">Edit login, name, and mobile number</p>
                    </div>
                </div>

                <!-- CARD 3: Prime -->
                <div class="amz-service-card" role="button" tabindex="0" onclick="openSectionPanel('prime')" onkeydown="if(event.key==='Enter'||event.key===' ')openSectionPanel('prime')">
                    <div class="amz-icon-holder ic-prime">
                        <span style="font-weight: 800; font-size: 21px; color: #00a8e1; font-family: sans-serif; letter-spacing: -0.5px;">prime</span>
                    </div>
                    <div class="amz-card-text">
                        <div class="amz-card-headline">Prime</div>
                        <p class="amz-card-summary">Manage your membership, view benefits, and payment settings</p>
                    </div>
                </div>

                <!-- CARD 4: Your Addresses -->
                <div class="amz-service-card" role="button" tabindex="0" onclick="openSectionPanel('addresses')" onkeydown="if(event.key==='Enter'||event.key===' ')openSectionPanel('addresses')">
                    <div class="amz-icon-holder ic-address">
                        <i class="fa-solid fa-house" style="font-size: 24px;"></i>
                    </div>
                    <div class="amz-card-text">
                        <div class="amz-card-headline">Your Addresses</div>
                        <p class="amz-card-summary">Edit, remove or set default address</p>
                    </div>
                </div>

                <!-- CARD 5: Your business account -->
                <div class="amz-service-card" role="button" tabindex="0" onclick="openSectionPanel('business')" onkeydown="if(event.key==='Enter'||event.key===' ')openSectionPanel('business')">
                    <div class="amz-icon-holder ic-business">
                        <i class="fa-solid fa-briefcase" style="font-size: 24px;"></i>
                    </div>
                    <div class="amz-card-text">
                        <div class="amz-card-headline">Your business account</div>
                        <p class="amz-card-summary">Sign up to save with business-exclusive pricing, schedule fast deliveries during business-hours, and more</p>
                    </div>
                </div>

                <!-- CARD 6: Gift cards -->
                <div class="amz-service-card" role="button" tabindex="0" onclick="openSectionPanel('giftcards')" onkeydown="if(event.key==='Enter'||event.key===' ')openSectionPanel('giftcards')">
                    <div class="amz-icon-holder ic-giftcard">
                        <i class="fa-solid fa-gift" style="font-size: 24px;"></i>
                    </div>
                    <div class="amz-card-text">
                        <div class="amz-card-headline">Gift cards</div>
                        <p class="amz-card-summary">View balance or redeem a card, and purchase a new Gift Card</p>
                    </div>
                </div>

                <!-- CARD 7: Your Payments -->
                <div class="amz-service-card" role="button" tabindex="0" onclick="openSectionPanel('payments')" onkeydown="if(event.key==='Enter'||event.key===' ')openSectionPanel('payments')">
                    <div class="amz-icon-holder ic-payment">
                        <i class="fa-solid fa-wallet" style="font-size: 24px;"></i>
                    </div>
                    <div class="amz-card-text">
                        <div class="amz-card-headline">Your Payments</div>
                        <p class="amz-card-summary">View all transactions, manage payment methods and settings</p>
                    </div>
                </div>

                <!-- CARD 8: Your Amazon Family -->
                <div class="amz-service-card" role="button" tabindex="0" onclick="openSectionPanel('family')" onkeydown="if(event.key==='Enter'||event.key===' ')openSectionPanel('family')">
                    <div class="amz-icon-holder ic-family">
                        <i class="fa-solid fa-users" style="font-size: 24px;"></i>
                    </div>
                    <div class="amz-card-text">
                        <div class="amz-card-headline">Your Amazon Family</div>
                        <p class="amz-card-summary">Manage profiles, sharing, and permissions in one place</p>
                    </div>
                </div>

                <!-- CARD 9: Digital Services and Device Support -->
                <div class="amz-service-card" role="button" tabindex="0" onclick="openSectionPanel('digitalServices')" onkeydown="if(event.key==='Enter'||event.key===' ')openSectionPanel('digitalServices')">
                    <div class="amz-icon-holder ic-digital">
                        <i class="fa-solid fa-display" style="font-size: 24px;"></i>
                    </div>
                    <div class="amz-card-text">
                        <div class="amz-card-headline">Digital Services and Device Support</div>
                        <p class="amz-card-summary">Troubleshoot device issues, manage or cancel digital subscriptions</p>
                    </div>
                </div>

                <!-- CARD 10: Your Lists -->
                <div class="amz-service-card" role="button" tabindex="0" onclick="openSectionPanel('wishlist')" onkeydown="if(event.key==='Enter'||event.key===' ')openSectionPanel('wishlist')">
                    <div class="amz-icon-holder ic-lists">
                        <i class="fa-solid fa-list-check" style="font-size: 24px;"></i>
                    </div>
                    <div class="amz-card-text">
                        <div class="amz-card-headline">Your Lists</div>
                        <p class="amz-card-summary">View, modify, and share your lists, or create new ones</p>
                    </div>
                </div>

                <!-- CARD 11: Customer Service -->
                <div class="amz-service-card" role="button" tabindex="0" onclick="openSectionPanel('customerService')" onkeydown="if(event.key==='Enter'||event.key===' ')openSectionPanel('customerService')">
                    <div class="amz-icon-holder ic-contact">
                        <i class="fa-solid fa-headset" style="font-size: 24px;"></i>
                    </div>
                    <div class="amz-card-text">
                        <div class="amz-card-headline">Customer Service</div>
                        <p class="amz-card-summary">Browse self service options, help articles or contact us</p>
                    </div>
                </div>

                <!-- CARD 12: Your Messages -->
                <div class="amz-service-card" role="button" tabindex="0" onclick="openSectionPanel('messages')" onkeydown="if(event.key==='Enter'||event.key===' ')openSectionPanel('messages')">
                    <div class="amz-icon-holder ic-messages">
                        <i class="fa-solid fa-envelope" style="font-size: 24px;"></i>
                    </div>
                    <div class="amz-card-text">
                        <div class="amz-card-headline">Your Messages</div>
                        <p class="amz-card-summary">View or respond to messages from Amazon, Sellers and Buyers</p>
                    </div>
                </div>

            </div>

            {{-- ───────────────────────────────────────────────────────────────── --}}
            {{-- AMAZON 3-COLUMN DIRECTORY SECTION (MATCHING SCREENSHOT)            --}}
            {{-- ───────────────────────────────────────────────────────────────── --}}
            <div class="amz-directory-section">
                <div class="amz-directory-grid">
                    
                    {{-- Column 1: Ordering and shopping preferences --}}
                    <div class="amz-dir-col">
                        <div class="amz-dir-title">Ordering and shopping preferences</div>
                        <ul class="amz-dir-list">
                            <li><a href="javascript:void(0)" onclick="openSectionPanel('loginSecurity')">About You</a></li>
                            <li><a href="javascript:void(0)" onclick="openSectionPanel('addresses')">Your Addresses</a></li>
                            <li><a href="javascript:void(0)" onclick="openSectionPanel('payments')">Amazon credit cards</a></li>
                            <li><a href="javascript:void(0)" onclick="openSectionPanel('payments')">Your Payments</a></li>
                            <li><a href="javascript:void(0)" onclick="openSectionPanel('orders')">Your Transactions</a></li>
                            <li><a href="javascript:void(0)" onclick="openSectionPanel('preferences')">Your Shopping preferences</a></li>
                            <li><a href="javascript:void(0)" onclick="openSectionPanel('digitalServices')">Your Content</a></li>
                            <li><a href="javascript:void(0)" onclick="openSectionPanel('preferences')">1-Click settings</a></li>
                            <li><a href="javascript:void(0)" onclick="openSectionPanel('preferences')">Amazon Key settings</a></li>
                            <li><a href="javascript:void(0)" onclick="openSectionPanel('preferences')">Whole Foods Market settings</a></li>
                            <li><a href="javascript:void(0)" onclick="openSectionPanel('preferences')">Language preferences</a></li>
                            <li><a href="javascript:void(0)" onclick="openSectionPanel('kyc')">Manage saved IDs</a></li>
                            <li><a href="javascript:void(0)" onclick="openSectionPanel('giftcards')">Coupons</a></li>
                            <li><a href="javascript:void(0)" onclick="openSectionPanel('giftcards')">Product Vouchers</a></li>
                        </ul>
                    </div>

                    {{-- Column 2: Digital content and devices --}}
                    <div class="amz-dir-col">
                        <div class="amz-dir-title">Digital content and devices</div>
                        <ul class="amz-dir-list">
                            <li><a href="javascript:void(0)" onclick="openSectionPanel('digitalServices')">All things Alexa</a></li>
                            <li><a href="javascript:void(0)" onclick="openSectionPanel('digitalServices')">Content Library</a></li>
                            <li><a href="javascript:void(0)" onclick="openSectionPanel('digitalServices')">Devices</a></li>
                            <li><a href="javascript:void(0)" onclick="openSectionPanel('digitalServices')">Manage Digital Delivery</a></li>
                            <li><a href="javascript:void(0)" onclick="openSectionPanel('digitalServices')">Your apps</a></li>
                            <li><a href="javascript:void(0)" onclick="openSectionPanel('digitalServices')">Prime Video settings</a></li>
                            <li><a href="javascript:void(0)" onclick="openSectionPanel('digitalServices')">Amazon Music settings</a></li>
                            <li><a href="javascript:void(0)" onclick="openSectionPanel('digitalServices')">Manage Amazon Drive and photos</a></li>
                            <li><a href="javascript:void(0)" onclick="openSectionPanel('digitalServices')">Twitch settings</a></li>
                            <li><a href="javascript:void(0)" onclick="openSectionPanel('digitalServices')">Audible settings</a></li>
                            <li><a href="javascript:void(0)" onclick="openSectionPanel('giftcards')">Amazon Coins</a></li>
                            <li><a href="javascript:void(0)" onclick="openSectionPanel('giftcards')">Digital gifts you've received</a></li>
                            <li><a href="javascript:void(0)" onclick="openSectionPanel('customerService')">Digital and device forum</a></li>
                            <li><a href="javascript:void(0)" onclick="openSectionPanel('digitalServices')">Comixology settings</a></li>
                            <li><a href="javascript:void(0)" onclick="openSectionPanel('kyc')">Verify AI Generated Content</a></li>
                        </ul>
                    </div>

                    {{-- Column 3: Memberships and subscriptions --}}
                    <div class="amz-dir-col">
                        <div class="amz-dir-title">Memberships and subscriptions</div>
                        <ul class="amz-dir-list">
                            <li><a href="javascript:void(0)" onclick="openSectionPanel('prime')">Kindle Unlimited</a></li>
                            <li><a href="javascript:void(0)" onclick="openSectionPanel('prime')">Prime Video Channels</a></li>
                            <li><a href="javascript:void(0)" onclick="openSectionPanel('prime')">Music Unlimited</a></li>
                            <li><a href="javascript:void(0)" onclick="openSectionPanel('memberships')">Subscribe & Save</a></li>
                            <li><a href="javascript:void(0)" onclick="openSectionPanel('memberships')">Amazon Kids+</a></li>
                            <li><a href="javascript:void(0)" onclick="openSectionPanel('digitalServices')">Audible membership</a></li>
                            <li><a href="javascript:void(0)" onclick="openSectionPanel('preferences')">Auto Buy</a></li>
                            <li><a href="{{ route('webzine.index') }}">Magazine subscriptions</a></li>
                            <li><a href="javascript:void(0)" onclick="openSectionPanel('prime')">One Medical membership for Prime members</a></li>
                            <li><a href="javascript:void(0)" onclick="openSectionPanel('memberships')">Other subscriptions</a></li>
                        </ul>
                    </div>

                </div>
            </div>

        </div>


        {{-- ═════════════════════════════════════════════════════════════════════ --}}
        {{-- VIEW 2: DEDICATED SUB-PAGE PANELS                                     --}}
        {{-- ═════════════════════════════════════════════════════════════════════ --}}

        <!-- PANEL 1: Your Orders -->
        <div class="amz-subpage-panel {{ $activeTab === 'orders' ? 'active' : '' }}" id="panel_orders" style="{{ $activeTab === 'orders' ? 'display: block !important;' : 'display: none !important;' }}">
            <span class="amz-back-link" onclick="closeAllPanels()"><i class="fa-solid fa-chevron-left me-1"></i> Your Account</span>
            <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2 flex-wrap gap-2">
                <div>
                    <h2 class="amz-main-title" style="font-size: 24px;">Your Orders</h2>
                    <small class="text-muted">Track, return, cancel an order, download invoice or buy again</small>
                </div>
                <span class="badge bg-light text-dark border px-3 py-1.5 font-monospace">Total Orders: {{ $myOrders->total() ?? 0 }}</span>
            </div>

            @if($myOrders->count() > 0)
                <div class="d-flex flex-column gap-3">
                    @foreach($myOrders as $order)
                        <div class="amz-order-card">
                            <div class="amz-order-header">
                                <div>
                                    <span>ORDER PLACED</span>
                                    <strong>{{ $order->created_at->format('d M, Y') }}</strong>
                                </div>
                                <div>
                                    <span>TOTAL</span>
                                    <strong>৳{{ number_format($order->total_amount ?? 0) }}</strong>
                                </div>
                                <div>
                                    <span>SHIP TO</span>
                                    <strong>{{ $order->customer_name ?: $user->name }}</strong>
                                </div>
                                <div class="text-end">
                                    <span>ORDER # {{ $order->order_number ?? $order->id }}</span>
                                    <span class="badge bg-{{ $order->status === 'delivered' ? 'success' : ($order->status === 'cancelled' ? 'danger' : 'warning text-dark') }} ms-2">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </div>
                            </div>
                            <div class="amz-order-body">
                                <div class="d-flex align-items-center gap-3">
                                    <div style="width: 58px; height: 78px; background: #e2e8f0; border-radius: 4px; overflow: hidden; flex-shrink: 0;">
                                        @if($order->book && $order->book->cover_image)
                                            <img src="{{ str_starts_with($order->book->cover_image, 'http') ? $order->book->cover_image : asset('storage/' . ltrim($order->book->cover_image, '/')) }}" alt="{{ $order->book->title }}" class="w-100 h-100 object-fit-cover">
                                        @else
                                            <div class="w-100 h-100 d-flex align-items-center justify-content-center text-muted"><i class="fa-solid fa-book"></i></div>
                                        @endif
                                    </div>
                                    <div>
                                        <h4 class="fw-bold text-dark mb-1" style="font-size: 15px;">{{ $order->book ? $order->book->title : 'Book Order #' . ($order->order_number ?? $order->id) }}</h4>
                                        <p class="text-secondary small mb-0">Quantity: {{ $order->quantity ?? 1 }} copy • Method: {{ ucfirst($order->payment_method ?? 'Cash On Delivery') }}</p>
                                        <p class="text-secondary small mb-0"><i class="fa-solid fa-location-dot me-1"></i> {{ $order->customer_address ?? $user->address }}</p>
                                    </div>
                                </div>
                                <div class="d-flex flex-column gap-2 text-end">
                                    <a href="{{ route('track-order', ['tracking_id' => $order->tracking_id ?? $order->order_number]) }}" class="amz-btn-gold">
                                        Track package
                                    </a>
                                    @if($order->book)
                                        <a href="{{ route('book.show', $order->book->slug) }}" class="amz-btn-silver">
                                            Buy it again
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
                    <i class="fa-solid fa-box-open fs-1 text-muted mb-3 opacity-50"></i>
                    <h5 class="fw-bold text-dark">No orders found</h5>
                    <p class="text-secondary small mb-3">You have not placed any book orders yet.</p>
                    <div>
                        <a href="{{ route('book.index') }}" class="amz-btn-gold">
                            <i class="fa-solid fa-bag-shopping me-1"></i> Browse Books
                        </a>
                    </div>
                </div>
            @endif
        </div>

        <!-- PANEL 2: Login & Security -->
        <div class="amz-subpage-panel {{ $activeTab === 'loginSecurity' ? 'active' : '' }}" id="panel_loginSecurity" style="{{ $activeTab === 'loginSecurity' ? 'display: block !important;' : 'display: none !important;' }}">
            <span class="amz-back-link" onclick="closeAllPanels()"><i class="fa-solid fa-chevron-left me-1"></i> Your Account</span>
            <h2 class="amz-main-title mb-3" style="font-size: 24px;">Login & Security</h2>
            
            <div class="card p-4 border rounded-3 bg-white" style="max-width: 780px;">
                
                {{-- Row 1: Name --}}
                <div class="amz-security-row">
                    <div>
                        <div class="amz-security-label">Name:</div>
                        <div class="amz-security-value">{{ $user->name }}</div>
                    </div>
                    <div>
                        <button type="button" class="amz-btn-silver" onclick="document.getElementById('editProfileBox').style.display='block'">Edit</button>
                    </div>
                </div>

                {{-- Row 2: Email --}}
                <div class="amz-security-row">
                    <div>
                        <div class="amz-security-label">Email:</div>
                        <div class="amz-security-value">{{ str_contains($user->email ?? '', '@buyer.ideaabd.com') ? 'No email linked' : $user->email }}</div>
                    </div>
                    <div>
                        <button type="button" class="amz-btn-silver" onclick="document.getElementById('editProfileBox').style.display='block'">Edit</button>
                    </div>
                </div>

                {{-- Row 3: Phone --}}
                <div class="amz-security-row">
                    <div>
                        <div class="amz-security-label">Mobile Phone Number:</div>
                        <div class="amz-security-value">{{ $user->phone }}</div>
                    </div>
                    <div>
                        <button type="button" class="amz-btn-silver" onclick="document.getElementById('editProfileBox').style.display='block'">Edit</button>
                    </div>
                </div>

                {{-- Row 4: Password --}}
                <div class="amz-security-row">
                    <div>
                        <div class="amz-security-label">Password:</div>
                        <div class="amz-security-value">••••••••••••</div>
                    </div>
                    <div>
                        <button type="button" class="amz-btn-silver" onclick="document.getElementById('editPasswordBox').style.display='block'">Edit</button>
                    </div>
                </div>

            </div>

            {{-- Inline Edit Forms --}}
            <div id="editProfileBox" class="card p-4 border rounded-3 bg-white mt-3" style="display: none; max-width: 780px;">
                <h5 class="fw-bold text-dark mb-3 border-bottom pb-2">Update Name, Email & Phone</h5>
                <form action="{{ route('my-account.profile.update') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-dark">Full Name *</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-dark">Mobile Phone Number *</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-dark">Email Address</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', str_contains($user->email ?? '', '@buyer.ideaabd.com') ? '' : $user->email) }}">
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="amz-btn-gold">Save Changes</button>
                        <button type="button" class="amz-btn-silver" onclick="document.getElementById('editProfileBox').style.display='none'">Cancel</button>
                    </div>
                </form>
            </div>

            <div id="editPasswordBox" class="card p-4 border rounded-3 bg-white mt-3" style="display: none; max-width: 780px;">
                <h5 class="fw-bold text-dark mb-3 border-bottom pb-2">Change Password</h5>
                <form action="{{ route('my-account.password.update') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-dark">Current Password *</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-dark">New Password *</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-dark">Confirm New Password *</label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="amz-btn-gold">Update Password</button>
                        <button type="button" class="amz-btn-silver" onclick="document.getElementById('editPasswordBox').style.display='none'">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- PANEL 3: Prime -->
        <div class="amz-subpage-panel {{ $activeTab === 'prime' ? 'active' : '' }}" id="panel_prime" style="{{ $activeTab === 'prime' ? 'display: block !important;' : 'display: none !important;' }}">
            <span class="amz-back-link" onclick="closeAllPanels()"><i class="fa-solid fa-chevron-left me-1"></i> Your Account</span>
            <h2 class="amz-main-title mb-3" style="font-size: 24px;">Prime Membership</h2>
            <div class="card p-4 border rounded-3 bg-white" style="max-width: 780px;">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="rounded-circle bg-primary-subtle text-primary p-3 fs-3">
                        <i class="fa-solid fa-crown"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold text-dark mb-0">Idea Prime Membership</h5>
                        <small class="text-muted">Manage your membership, view benefits, and payment settings</small>
                    </div>
                </div>
                <div class="p-3 bg-light rounded-3 border mb-3">
                    <span class="badge bg-success text-white mb-2">Active Benefits</span>
                    <ul class="mb-0 small text-secondary">
                        <li>Exclusive book discounts and seasonal promotions</li>
                        <li>Unlimited webzine access and author articles</li>
                        <li>Priority book delivery across Bangladesh</li>
                    </ul>
                </div>
                <a href="{{ route('book.index') }}" class="amz-btn-gold">Browse Prime Books</a>
            </div>
        </div>

        <!-- PANEL 4: Your Addresses -->
        <div class="amz-subpage-panel {{ $activeTab === 'addresses' ? 'active' : '' }}" id="panel_addresses" style="{{ $activeTab === 'addresses' ? 'display: block !important;' : 'display: none !important;' }}">
            <span class="amz-back-link" onclick="closeAllPanels()"><i class="fa-solid fa-chevron-left me-1"></i> Your Account</span>
            <h2 class="amz-main-title mb-3" style="font-size: 24px;">Your Addresses</h2>

            <div class="amz-address-grid mb-4">
                
                <!-- Add Address Tile -->
                <div class="amz-add-address-card" onclick="toggleAddressForm()">
                    <i class="fa-solid fa-plus fs-1 mb-2 text-secondary"></i>
                    <h5 class="fw-bold text-dark mb-0">Add Address</h5>
                </div>

                <!-- Default Address Tile -->
                <div class="amz-address-card">
                    <span class="badge bg-light text-secondary border position-absolute top-0 end-0 m-2 font-monospace">Default</span>
                    <strong class="text-dark fs-6 d-block mb-1">{{ $defaultAddress['name'] ?? $user->name }}</strong>
                    <div class="text-secondary small mb-2">
                        {{ $defaultAddress['address'] ?: 'No street address provided' }}<br>
                        {{ $defaultAddress['thana'] ? $defaultAddress['thana'].', ' : '' }}{{ $defaultAddress['district'] }}<br>
                        Phone: {{ $defaultAddress['phone'] ?? $user->phone }}
                    </div>
                    <div class="mt-auto pt-2 border-top d-flex gap-3">
                        <a href="javascript:void(0)" class="small text-decoration-none" style="color: #007185;" onclick="toggleAddressForm()">Edit</a>
                    </div>
                </div>

            </div>

            <!-- Add/Edit Address Form Box -->
            <div id="amzAddressFormBox" class="card p-4 border rounded-3 bg-white d-none" style="max-width: 700px;">
                <h5 class="fw-bold text-dark mb-3 border-bottom pb-2">Add New Delivery Address</h5>
                <form action="{{ route('my-account.address.update') }}" method="POST">
                    @csrf
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-dark">Full Name *</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $defaultAddress['name'] ?? $user->name) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-dark">Phone Number *</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone', $defaultAddress['phone'] ?? $user->phone) }}" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-dark">District / City *</label>
                            <input type="text" name="district" class="form-control" placeholder="e.g. Dhaka or Rangpur" value="{{ old('district', $defaultAddress['district'] ?? '') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-dark">Thana / Upazila</label>
                            <input type="text" name="thana" class="form-control" placeholder="e.g. Dhanmondi or Rangpur City" value="{{ old('thana', $defaultAddress['thana'] ?? '') }}">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-dark">Street Address *</label>
                        <textarea name="address" class="form-control" rows="3" placeholder="House/Flat No, Road Name, Area..." required>{{ old('address', $defaultAddress['address'] ?? '') }}</textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="amz-btn-gold">Save Address</button>
                        <button type="button" class="amz-btn-silver" onclick="toggleAddressForm()">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- PANEL 7: Your Payments -->
        <div class="amz-subpage-panel {{ $activeTab === 'payments' ? 'active' : '' }}" id="panel_payments" style="{{ $activeTab === 'payments' ? 'display: block !important;' : 'display: none !important;' }}">
            <span class="amz-back-link" onclick="closeAllPanels()"><i class="fa-solid fa-chevron-left me-1"></i> Your Account</span>
            <h2 class="amz-main-title mb-3" style="font-size: 24px;">Your Payments</h2>
            
            <div class="card p-4 border rounded-3 bg-white" style="max-width: 780px;">
                <div class="d-flex align-items-center justify-content-between mb-4 border-bottom pb-3 flex-wrap gap-2">
                    <div>
                        <h4 class="fw-bold mb-0 text-dark">Wallet & Royalty Balance</h4>
                        <small class="text-muted">Available balance & payout accounts</small>
                    </div>
                    <span class="badge bg-success text-white px-3 py-1.5 rounded-pill fs-6 font-monospace">৳{{ number_format($walletBalance, 2) }}</span>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded-3 border">
                            <small class="text-muted d-block">Payout Method</small>
                            <strong class="text-dark fs-6">{{ strtoupper($regData['payout_type'] ?? 'bKash') }}</strong>
                            <div class="text-secondary small">{{ $regData['payout_details'] ?? 'No payout account set' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded-3 border">
                            <small class="text-muted d-block">This Month's Royalties</small>
                            <strong class="text-success fs-6">৳{{ number_format($monthlyRoyalty, 2) }}</strong>
                            <div class="text-secondary small">{{ now()->format('F Y') }} Earnings</div>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="button" class="amz-btn-gold" onclick="openSectionPanel('kyc')">
                        Update Payment Settings
                    </button>
                    @if(Route::has('author.royalties'))
                        <a href="{{ route('author.royalties') }}" class="amz-btn-silver">
                            View Royalty Statement
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- PANEL 5: Your business account -->
        <div class="amz-subpage-panel {{ $activeTab === 'business' ? 'active' : '' }}" id="panel_business" style="{{ $activeTab === 'business' ? 'display: block !important;' : 'display: none !important;' }}">
            <span class="amz-back-link" onclick="closeAllPanels()"><i class="fa-solid fa-chevron-left me-1"></i> Your Account</span>
            <h2 class="amz-main-title mb-3" style="font-size: 24px;">Your Business Account</h2>
            <div class="card p-4 border rounded-3 bg-white" style="max-width: 780px;">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="rounded-circle bg-dark text-white p-3 fs-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                        <i class="fa-solid fa-briefcase"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold text-dark mb-0">Idea Business & Publishing Solutions</h5>
                        <small class="text-muted">Sign up to save with business-exclusive pricing and publisher perks</small>
                    </div>
                </div>
                <div class="p-3 bg-light rounded-3 border mb-3">
                    <span class="badge bg-primary text-white mb-2">Business Features</span>
                    <ul class="mb-0 small text-secondary">
                        <li>Bulk book purchasing with wholesale institutional discounts</li>
                        <li>Author & publisher royalty management and manuscript direct submission</li>
                        <li>Automated tax invoices and dedicated business billing support</li>
                    </ul>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="amz-btn-gold" onclick="openSectionPanel('kyc')">
                        Complete Business / Author KYC
                    </button>
                    <a href="{{ route('contact') }}" class="amz-btn-silver">
                        Contact Business Support
                    </a>
                </div>
            </div>
        </div>

        <!-- PANEL 6: Gift cards -->
        <div class="amz-subpage-panel {{ $activeTab === 'giftcards' ? 'active' : '' }}" id="panel_giftcards" style="{{ $activeTab === 'giftcards' ? 'display: block !important;' : 'display: none !important;' }}">
            <span class="amz-back-link" onclick="closeAllPanels()"><i class="fa-solid fa-chevron-left me-1"></i> Your Account</span>
            <h2 class="amz-main-title mb-3" style="font-size: 24px;">Gift Cards</h2>
            <div class="card p-4 border rounded-3 bg-white" style="max-width: 780px;">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="rounded-circle bg-warning-subtle text-warning p-3 fs-3">
                        <i class="fa-solid fa-gift"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold text-dark mb-0">Redeem Gift Card or Voucher</h5>
                        <small class="text-muted">View balance or redeem a card, and purchase a new Gift Card</small>
                    </div>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-md-8">
                        <input type="text" class="form-control" placeholder="Enter claim code (e.g. IDEA-GIFT-XXXX)">
                    </div>
                    <div class="col-md-4">
                        <button type="button" class="amz-btn-gold w-100">Apply to your balance</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- PANEL 8: Your Amazon Family -->
        <div class="amz-subpage-panel {{ $activeTab === 'family' ? 'active' : '' }}" id="panel_family" style="{{ $activeTab === 'family' ? 'display: block !important;' : 'display: none !important;' }}">
            <span class="amz-back-link" onclick="closeAllPanels()"><i class="fa-solid fa-chevron-left me-1"></i> Your Account</span>
            <h2 class="amz-main-title mb-3" style="font-size: 24px;">Your Amazon Family & Profiles</h2>
            <div class="card p-4 border rounded-3 bg-white" style="max-width: 780px;">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="rounded-circle bg-info-subtle text-info p-3 fs-3">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold text-dark mb-0">Family Profiles & Shared Reading Library</h5>
                        <small class="text-muted">Manage profiles, sharing, and reading permissions in one place</small>
                    </div>
                </div>
                <div class="p-3 bg-light rounded-3 border mb-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <strong class="text-dark">{{ $user->name }} (Primary Account)</strong>
                            <div class="small text-muted">Full account & billing administrator</div>
                        </div>
                        <span class="badge bg-success">Active</span>
                    </div>
                </div>
                <a href="{{ route('ebook.index') }}" class="amz-btn-silver">Browse Family eBook Library</a>
            </div>
        </div>

        <!-- PANEL 10: Your Lists -->
        <div class="amz-subpage-panel {{ $activeTab === 'wishlist' ? 'active' : '' }}" id="panel_wishlist" style="{{ $activeTab === 'wishlist' ? 'display: block !important;' : 'display: none !important;' }}">
            <span class="amz-back-link" onclick="closeAllPanels()"><i class="fa-solid fa-chevron-left me-1"></i> Your Account</span>
            <h2 class="amz-main-title mb-3 border-bottom pb-2" style="font-size: 24px;">Your Lists & Wishlist</h2>
            
            @if($wishlistItems->count() > 0)
                <div class="row g-3">
                    @foreach($wishlistItems as $item)
                        @if($item->book)
                            <div class="col-md-6 col-lg-4">
                                <div class="card h-100 border rounded-3 p-3 bg-white">
                                    <div class="d-flex gap-3">
                                        <div style="width: 68px; height: 92px; background: #e2e8f0; border-radius: 4px; overflow: hidden; flex-shrink: 0;">
                                            @if($item->book->cover_image)
                                                <img src="{{ str_starts_with($item->book->cover_image, 'http') ? $item->book->cover_image : asset('storage/' . ltrim($item->book->cover_image, '/')) }}" alt="{{ $item->book->title }}" class="w-100 h-100 object-fit-cover">
                                            @else
                                                <div class="w-100 h-100 d-flex align-items-center justify-content-center text-muted"><i class="fa-solid fa-book"></i></div>
                                            @endif
                                        </div>
                                        <div class="flex-grow-1 min-w-0">
                                            <h4 class="fw-bold text-dark mb-1 text-truncate" style="font-size: 14.5px;">{{ $item->book->title }}</h4>
                                            <small class="text-secondary d-block mb-1">{{ $item->book->author_name ?? 'Idea Prokashon' }}</small>
                                            <div class="fw-bold text-primary mb-2">৳{{ number_format($item->book->discount_price ?: $item->book->price) }}</div>
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('book.show', $item->book->slug) }}" class="amz-btn-gold">View</a>
                                                <form action="{{ route('my-account.wishlist.remove', $item->id) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="amz-btn-silver" title="Remove"><i class="fa-solid fa-trash"></i></button>
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
                    <i class="fa-solid fa-heart-crack fs-1 text-muted mb-3 opacity-50"></i>
                    <h5 class="fw-bold text-dark">Your list is empty</h5>
                    <p class="text-secondary small mb-3">Explore and save items you'd like to read later.</p>
                    <div><a href="{{ route('book.index') }}" class="amz-btn-gold">Explore Books</a></div>
                </div>
            @endif
        </div>

        <!-- PANEL 11: Customer Service -->
        <div class="amz-subpage-panel {{ $activeTab === 'customerService' ? 'active' : '' }}" id="panel_customerService" style="{{ $activeTab === 'customerService' ? 'display: block !important;' : 'display: none !important;' }}">
            <span class="amz-back-link" onclick="closeAllPanels()"><i class="fa-solid fa-chevron-left me-1"></i> Your Account</span>
            <h2 class="amz-main-title mb-3 border-bottom pb-2" style="font-size: 24px;">Customer Service & Support</h2>
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="card p-4 border rounded-3 text-center bg-white h-100">
                        <i class="fa-solid fa-headset fs-1 text-primary mb-2"></i>
                        <h6 class="fw-bold mb-1 text-dark">Direct Phone Support</h6>
                        <small class="text-muted d-block mb-3">Speak with customer care specialist</small>
                        <a href="tel:+8801558712810" class="amz-btn-silver mt-auto">+8801558712810</a>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card p-4 border rounded-3 text-center bg-white h-100">
                        <i class="fa-brands fa-whatsapp fs-1 text-success mb-2"></i>
                        <h6 class="fw-bold mb-1 text-dark">WhatsApp Support</h6>
                        <small class="text-muted d-block mb-3">Fast instant messaging help</small>
                        <a href="https://api.whatsapp.com/send?phone=8801558712810" target="_blank" class="amz-btn-gold mt-auto">Open WhatsApp</a>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card p-4 border rounded-3 text-center bg-white h-100">
                        <i class="fa-solid fa-envelope fs-1 text-info mb-2"></i>
                        <h6 class="fw-bold mb-1 text-dark">Email Helpdesk</h6>
                        <small class="text-muted d-block mb-3">Submit a support request</small>
                        <a href="{{ route('contact') }}" class="amz-btn-silver mt-auto">Contact Form</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- PANEL 12: Your Messages -->
        <div class="amz-subpage-panel {{ $activeTab === 'messages' ? 'active' : '' }}" id="panel_messages" style="{{ $activeTab === 'messages' ? 'display: block !important;' : 'display: none !important;' }}">
            <span class="amz-back-link" onclick="closeAllPanels()"><i class="fa-solid fa-chevron-left me-1"></i> Your Account</span>
            <h2 class="amz-main-title mb-3 border-bottom pb-2" style="font-size: 24px;">Your Messages & Notifications</h2>
            <div class="card p-4 border rounded-3 bg-white">
                <div class="d-flex align-items-center gap-3 p-3 bg-light rounded-3 mb-2 border">
                    <i class="fa-solid fa-circle-check text-success fs-4"></i>
                    <div>
                        <strong class="text-dark">Order Shipment Update</strong>
                        <p class="text-secondary small mb-0">Your book order package has been dispatched with courier tracking.</p>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-3 p-3 bg-light rounded-3 mb-2 border">
                    <i class="fa-solid fa-sack-dollar text-primary fs-4"></i>
                    <div>
                        <strong class="text-dark">Monthly Royalty Statement Updated</strong>
                        <p class="text-secondary small mb-0">Author royalties for the current cycle have been posted to your wallet.</p>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-3 p-3 bg-light rounded-3 border">
                    <i class="fa-solid fa-feather text-info fs-4"></i>
                    <div>
                        <strong class="text-dark">Manuscript Review Notification</strong>
                        <p class="text-secondary small mb-0">Our editorial team has received your submission for publication review.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- PANEL 9: Digital Services and Device Support -->
        <div class="amz-subpage-panel {{ $activeTab === 'digitalServices' ? 'active' : '' }}" id="panel_digitalServices" style="{{ $activeTab === 'digitalServices' ? 'display: block !important;' : 'display: none !important;' }}">
            <span class="amz-back-link" onclick="closeAllPanels()"><i class="fa-solid fa-chevron-left me-1"></i> Your Account</span>
            <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2 flex-wrap gap-2">
                <div>
                    <h2 class="amz-main-title" style="font-size: 24px;">Digital Services and Device Support</h2>
                    <small class="text-muted">Manage purchased eBooks, content library, and digital readers</small>
                </div>
                <a href="{{ route('ebook.index') }}" class="amz-btn-gold">
                    Browse eBook Store
                </a>
            </div>

            @if($myEbooks->count() > 0)
                <div class="row g-3">
                    @foreach($myEbooks as $item)
                        @if($item->ebook)
                            <div class="col-md-6 col-lg-4">
                                <div class="card p-3 border rounded-3 bg-white h-100">
                                    <div class="d-flex gap-3">
                                        <div style="width: 68px; height: 92px; background: #e2e8f0; border-radius: 4px; overflow: hidden; flex-shrink: 0;">
                                            @if($item->ebook->cover_image)
                                                <img src="{{ str_starts_with($item->ebook->cover_image, 'http') ? $item->ebook->cover_image : asset('storage/' . ltrim($item->ebook->cover_image, '/')) }}" alt="{{ $item->ebook->title }}" class="w-100 h-100 object-fit-cover">
                                            @else
                                                <div class="w-100 h-100 d-flex align-items-center justify-content-center text-muted"><i class="fa-solid fa-book"></i></div>
                                            @endif
                                        </div>
                                        <div class="flex-grow-1 min-w-0">
                                            <h4 class="fw-bold text-dark mb-1 text-truncate" style="font-size: 14.5px;">{{ $item->ebook->title }}</h4>
                                            <small class="text-muted d-block mb-1">{{ $item->ebook->author_name ?? $item->ebook->author?->name ?? 'Idea Prokashon' }}</small>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0.5 small mb-2 d-inline-block">
                                                <i class="fa-solid fa-circle-check me-1"></i> Unlocked
                                            </span>
                                            <div>
                                                <a href="{{ route('ebook.read', $item->ebook->slug ?? $item->ebook->id) }}" class="amz-btn-gold">
                                                    Read Now
                                                </a>
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
                    <i class="fa-solid fa-book-open-reader fs-1 text-muted mb-3 opacity-50"></i>
                    <h5 class="fw-bold text-dark">No eBooks in Library</h5>
                    <p class="text-secondary small mb-3">You have not purchased or unlocked any eBooks yet.</p>
                    <div>
                        <a href="{{ route('ebook.index') }}" class="amz-btn-gold">Browse eBook Store</a>
                    </div>
                </div>
            @endif
        </div>

        <!-- PANEL: Preferences -->
        <div class="amz-subpage-panel {{ $activeTab === 'preferences' ? 'active' : '' }}" id="panel_preferences" style="{{ $activeTab === 'preferences' ? 'display: block !important;' : 'display: none !important;' }}">
            <span class="amz-back-link" onclick="closeAllPanels()"><i class="fa-solid fa-chevron-left me-1"></i> Your Account</span>
            <h2 class="amz-main-title mb-3" style="font-size: 24px;">Shopping & Communication Preferences</h2>
            <div class="card p-4 border rounded-3 bg-white" style="max-width: 700px;">
                <h6 class="fw-bold text-dark mb-3">Notifications & Alerts</h6>
                <div class="d-flex flex-column gap-3">
                    <div class="d-flex align-items-center justify-content-between p-3 border rounded-3 bg-light">
                        <div>
                            <div class="fw-bold text-dark small">Order Status SMS & WhatsApp Alerts</div>
                            <small class="text-muted">Receive live courier and delivery notifications on mobile</small>
                        </div>
                        <div class="form-check form-switch m-0">
                            <input class="form-check-input" type="checkbox" role="switch" checked>
                        </div>
                    </div>
                    <div class="d-flex align-items-center justify-content-between p-3 border rounded-3 bg-light">
                        <div>
                            <div class="fw-bold text-dark small">New Releases & Literature Newsletter</div>
                            <small class="text-muted">Monthly digest of new book releases and discounts</small>
                        </div>
                        <div class="form-check form-switch m-0">
                            <input class="form-check-input" type="checkbox" role="switch" checked>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- PANEL: Memberships -->
        <div class="amz-subpage-panel {{ $activeTab === 'memberships' ? 'active' : '' }}" id="panel_memberships" style="{{ $activeTab === 'memberships' ? 'display: block !important;' : 'display: none !important;' }}">
            <span class="amz-back-link" onclick="closeAllPanels()"><i class="fa-solid fa-chevron-left me-1"></i> Your Account</span>
            <h2 class="amz-main-title mb-3" style="font-size: 24px;">Memberships & Subscriptions</h2>
            <div class="card p-4 border rounded-3 bg-white" style="max-width: 700px;">
                <div class="p-3 border rounded-3 bg-light d-flex align-items-center justify-content-between">
                    <div>
                        <strong class="text-dark">Idea Standard Reader Account</strong>
                        <div class="text-muted small">Standard membership • Lifetime active</div>
                    </div>
                    <span class="badge bg-success text-white px-3 py-1.5 rounded-pill">Active</span>
                </div>
            </div>
        </div>

        <!-- PANEL: KYC Verification -->
        <div class="amz-subpage-panel {{ $activeTab === 'kyc' ? 'active' : '' }}" id="panel_kyc" style="{{ $activeTab === 'kyc' ? 'display: block !important;' : 'display: none !important;' }}">
            <span class="amz-back-link" onclick="closeAllPanels()"><i class="fa-solid fa-chevron-left me-1"></i> Your Account</span>
            <div class="card p-4 border rounded-3 bg-white" style="max-width: 800px;">
                <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-3 flex-wrap gap-2">
                    <div>
                        <h4 class="fw-bold mb-0 text-dark">Identity & KYC Verification</h4>
                        <small class="text-muted">Provide your details for verified author/publisher blue badge</small>
                    </div>
                    @if($isApproved)
                        <span class="badge bg-success text-white px-3 py-1.5 rounded-pill"><i class="fa-solid fa-shield-check me-1"></i> Verified Account</span>
                    @elseif($isPending)
                        <span class="badge bg-warning text-dark px-3 py-1.5 rounded-pill"><i class="fa-solid fa-clock-rotate-left me-1"></i> Under Review</span>
                    @else
                        <span class="badge bg-danger text-white px-3 py-1.5 rounded-pill"><i class="fa-solid fa-circle-xmark me-1"></i> Incomplete</span>
                    @endif
                </div>

                <form action="{{ route('my-account.kyc.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    {{-- Profile Photo --}}
                    <div class="mb-4 d-flex align-items-center gap-4">
                        <div style="width: 76px; height: 76px; border-radius: 50%; overflow: hidden; background: #e0f2fe; border: 2px solid #007185; flex-shrink: 0;" class="d-flex align-items-center justify-content-center">
                            @if($user->avatar)
                                <img src="{{ str_starts_with($user->avatar, 'http') ? $user->avatar : asset('storage/' . ltrim($user->avatar, '/')) }}" alt="{{ $user->name }}" id="kycAvatarPreview" class="w-100 h-100 object-fit-cover">
                            @else
                                <img id="kycAvatarPreview" class="w-100 h-100 object-fit-cover d-none" alt="Preview">
                                <span id="kycAvatarPlaceholder" class="fs-2 text-primary fw-bold">{{ mb_substr($user->name, 0, 1) }}</span>
                            @endif
                        </div>
                        <div>
                            <label class="form-label fw-bold text-dark small mb-1">প্রোফাইল / লেখকের ছবি</label>
                            <input type="file" name="avatar" class="form-control form-control-sm" accept="image/*" onchange="previewKycPhoto(this)">
                            <small class="text-muted">JPG, PNG, WebP ফরম্যাটে সর্বোচ্চ ৫MB</small>
                        </div>
                    </div>

                    {{-- Names --}}
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small">লেখকের নাম (বাংলা)</label>
                            <input type="text" name="name_bn" class="form-control" placeholder="যেমন: শাকিল মাসুদ" value="{{ old('name_bn', $regData['name_bn'] ?? $user->name) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small">লেখকের নাম (ইংরেজি)</label>
                            <input type="text" name="name_en" class="form-control" placeholder="e.g. Shakil Masud" value="{{ old('name_en', $regData['name_en'] ?? '') }}">
                        </div>
                    </div>

                    {{-- Pen Name & Bio --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small">ছদ্মনাম (যদি থাকে)</label>
                        <input type="text" name="pen_name" class="form-control" placeholder="ঐচ্ছিক" value="{{ old('pen_name', $regData['pen_name'] ?? '') }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small">সংক্ষিপ্ত লেখক পরিচিতি / বায়ো</label>
                        <textarea name="bio" class="form-control" rows="4" placeholder="আপনার সাহিত্য চর্চা বা প্রকাশিত বই সম্পর্কে লিখুন...">{{ old('bio', $regData['bio'] ?? '') }}</textarea>
                    </div>

                    {{-- NID Verification --}}
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small">জাতীয় পরিচয়পত্র (NID) নম্বর</label>
                            <input type="text" name="nid" class="form-control" placeholder="১০ বা ১৭ ডিজিটের নম্বর" value="{{ old('nid', $regData['nid'] ?? '') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small">NID কার্ডের স্ক্যান কপি (PDF/Image)</label>
                            <input type="file" name="nid_file" class="form-control" accept="image/*,.pdf">
                        </div>
                    </div>

                    {{-- Payout Settings --}}
                    <div class="row g-3 mb-4">
                        <div class="col-md-5">
                            <label class="form-label fw-bold text-dark small">রয়্যালটি গ্রহণের মাধ্যম</label>
                            <select name="payout_account_type" class="form-select">
                                <option value="bkash" {{ ($regData['payout_type'] ?? '') === 'bkash' ? 'selected' : '' }}>বিকাশ</option>
                                <option value="nagad" {{ ($regData['payout_type'] ?? '') === 'nagad' ? 'selected' : '' }}>নগদ</option>
                                <option value="rocket" {{ ($regData['payout_type'] ?? '') === 'rocket' ? 'selected' : '' }}>রকেট</option>
                                <option value="bank" {{ ($regData['payout_type'] ?? '') === 'bank' ? 'selected' : '' }}>ব্যাংক অ্যাকাউন্ট</option>
                            </select>
                        </div>
                        <div class="col-md-7">
                            <label class="form-label fw-bold text-dark small">অ্যাকাউন্ট নম্বর / বিস্তারিত</label>
                            <input type="text" name="payout_account_details" class="form-control" placeholder="যেমন: 017XXXXXXXX" value="{{ old('payout_account_details', $regData['payout_details'] ?? '') }}">
                        </div>
                    </div>

                    <button type="submit" class="amz-btn-gold">
                        সংরক্ষণ ও জমা দিন
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>

@push('scripts')
<script src="{{ asset('js/my-account.js') }}?v={{ @filemtime(public_path('js/my-account.js')) ?: time() }}"></script>
@endpush
@endsection
