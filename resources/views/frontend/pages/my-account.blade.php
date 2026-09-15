@extends('layouts.app')

@section('title', 'Your Account — ' . ($user->name ?? 'Idea'))

@push('head')
<style>
/* ══════════════════════════════════════════════════════════════════
   AUTHENTIC "YOUR ACCOUNT" PORTAL DESIGN (Navy & Sky-Blue Palette)
   ══════════════════════════════════════════════════════════════════ */
:root {
    --ya-sky-primary: #0284c7;
    --ya-sky-hover: #0369a1;
    --ya-sky-light: #e0f2fe;
    --ya-sky-soft: #f0f9ff;
    --ya-navy-dark: #0c4a6e;
    --ya-navy-deep: #082f49;
    --ya-border: #d5d9d9;
    --ya-border-subtle: #e2e8f0;
    --ya-text-main: #0f1111;
    --ya-text-muted: #565959;
}

.ya-page-wrapper {
    background-color: #ffffff;
    color: var(--ya-text-main);
    min-height: 80vh;
    padding-top: 1.5rem;
    padding-bottom: 4rem;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
}

/* Breadcrumb */
.ya-breadcrumb {
    font-size: 13px;
    margin-bottom: 1rem;
}
.ya-breadcrumb a {
    color: #007185;
    text-decoration: none;
}
.ya-breadcrumb a:hover {
    color: #c7511f;
    text-decoration: underline;
}

/* Page Main Heading */
.ya-page-title {
    font-size: 28px;
    font-weight: 500;
    color: #0f1111;
    line-height: 1.2;
    margin-bottom: 1.25rem;
}

/* Section Header */
.ya-section-title {
    font-size: 18px;
    font-weight: 700;
    color: #0f1111;
    margin-top: 2rem;
    margin-bottom: 0.85rem;
    padding-bottom: 0.35rem;
    border-bottom: 1px solid #e7e7e7;
    display: flex;
    align-items: center;
    gap: 8px;
}

/* Grid of Cards */
.ya-cards-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
    margin-bottom: 1.5rem;
}

/* Amazon-Style Single Tile Card */
.ya-card-tile {
    background: #ffffff;
    border: 1px solid var(--ya-border);
    border-radius: 8px;
    padding: 16px 18px;
    display: flex;
    align-items: flex-start;
    gap: 14px;
    text-decoration: none;
    color: var(--ya-text-main);
    transition: all 0.15s ease;
    cursor: pointer;
    box-shadow: 0 1px 2px rgba(15, 17, 17, 0.05);
    height: 100%;
}

.ya-card-tile:hover {
    background: #f7fafa;
    border-color: #0284c7;
    box-shadow: 0 4px 12px rgba(2, 132, 199, 0.12);
    transform: translateY(-2px);
    color: var(--ya-text-main);
}

.ya-card-icon-box {
    width: 48px;
    height: 48px;
    border-radius: 10px;
    background: var(--ya-sky-soft);
    border: 1px solid var(--ya-sky-light);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--ya-sky-primary);
    font-size: 22px;
    flex-shrink: 0;
    transition: all 0.15s ease;
}

.ya-card-tile:hover .ya-card-icon-box {
    background: var(--ya-sky-primary);
    color: #ffffff;
}

.ya-card-title-text {
    font-size: 15px;
    font-weight: 700;
    color: #0f1111;
    margin-bottom: 3px;
    line-height: 1.3;
}

.ya-card-desc-text {
    font-size: 12.5px;
    color: var(--ya-text-muted);
    line-height: 1.4;
    margin: 0;
}

/* User Greeting Hero */
.ya-greeting-hero {
    background: linear-gradient(135deg, #0c4a6e 0%, #0369a1 60%, #0284c7 100%);
    color: #ffffff;
    border-radius: 12px;
    padding: 1.5rem 1.75rem;
    margin-bottom: 1.75rem;
    box-shadow: 0 4px 15px rgba(12, 74, 110, 0.15);
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
}

.ya-user-avatar {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: #ffffff;
    color: #0284c7;
    font-weight: 800;
    font-size: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid rgba(255, 255, 255, 0.8);
    flex-shrink: 0;
    overflow: hidden;
}

/* Modal Panels / Detail Sections */
.ya-detail-panel {
    display: none;
    animation: fadeInPanel 0.2s ease;
}
.ya-detail-panel.active {
    display: block;
}

@keyframes fadeInPanel {
    from { opacity: 0; transform: translateY(6px); }
    to { opacity: 1; transform: translateY(0); }
}

.ya-back-nav {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    font-weight: 600;
    color: #007185;
    text-decoration: none;
    margin-bottom: 1rem;
    cursor: pointer;
}
.ya-back-nav:hover {
    color: #c7511f;
    text-decoration: underline;
}

@media (max-width: 992px) {
    .ya-cards-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 576px) {
    .ya-cards-grid {
        grid-template-columns: 1fr;
    }
    .ya-page-title {
        font-size: 22px;
    }
}
</style>
@endpush

@section('content')
<div class="ya-page-wrapper">
    <div class="container" style="max-width: 1140px;">

        {{-- Flash Alert Notifications --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-3 p-3 mb-3 border-0 shadow-xs" style="background: #ecfdf5; color: #065f46; border-left: 4px solid #10b981 !important;">
                <i class="fas fa-circle-check me-2 text-success"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show rounded-3 p-3 mb-3 border-0 shadow-xs" style="background: #fef2f2; color: #991b1b; border-left: 4px solid #ef4444 !important;">
                <i class="fas fa-triangle-exclamation me-2 text-danger"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- ═════════════════════════════════════════════════════════════════════ --}}
        {{-- VIEW 1: THE MAIN "YOUR ACCOUNT" HUB (Default View)                   --}}
        {{-- ═════════════════════════════════════════════════════════════════════ --}}
        <div id="mainAccountHubView">
            
            {{-- Breadcrumb --}}
            <nav class="ya-breadcrumb">
                <a href="{{ url('/') }}">Home</a> &rsaquo; <span>Your Account</span>
            </nav>

            {{-- User Greeting Hero Bar --}}
            <div class="ya-greeting-hero">
                <div class="d-flex align-items-center gap-3">
                    <div class="ya-user-avatar">
                        @if($user->avatar)
                            <img src="{{ str_starts_with($user->avatar, 'http') ? $user->avatar : asset('storage/' . ltrim($user->avatar, '/')) }}" alt="{{ $user->name }}" class="w-100 h-100 object-fit-cover rounded-circle">
                        @else
                            {{ mb_substr($user->name, 0, 1) }}
                        @endif
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <h4 class="fw-bold mb-0 text-white" style="font-size: 1.3rem;">Welcome, {{ $user->name }}</h4>
                            <span class="badge bg-light text-dark rounded-pill small px-2.5 py-1 fw-bold" style="font-size: 11px;">
                                @if($user->isAdmin()) Admin @elseif($user->role === 'author' || $user->reg_type === 'author') Author @elseif($user->role === 'publisher' || $user->reg_type === 'publisher') Publisher @elseif($user->role === 'seller' || $user->reg_type === 'seller') Seller @else Reader Account @endif
                            </span>
                        </div>
                        <div class="small text-white-50 mt-0.5">
                            <span><i class="fas fa-phone me-1"></i>{{ $user->phone }}</span>
                            @if($user->email && !str_contains($user->email, '@buyer.ideaabd.com'))
                                <span class="ms-3"><i class="fas fa-envelope me-1"></i>{{ $user->email }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Action Links --}}
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    @if($user->isAdmin() && Route::has('admin.dashboard'))
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-warning text-dark fw-bold rounded-pill px-3">Admin Panel</a>
                    @endif
                    @if(($user->role === 'author' || $user->reg_type === 'author') && Route::has('author.dashboard'))
                        <a href="{{ route('author.dashboard') }}" class="btn btn-sm btn-info text-dark fw-bold rounded-pill px-3">Author Panel</a>
                    @endif
                    @if(($user->role === 'publisher' || $user->reg_type === 'publisher') && Route::has('publisher.dashboard'))
                        <a href="{{ route('publisher.dashboard') }}" class="btn btn-sm btn-success text-white fw-bold rounded-pill px-3">Publisher Panel</a>
                    @endif
                    <a href="{{ route('book.index') }}" class="btn btn-sm btn-light text-dark fw-semibold rounded-pill px-3">Shop Books</a>
                    <a href="{{ route('logout') }}" class="btn btn-sm btn-outline-light rounded-pill px-3" onclick="event.preventDefault(); document.getElementById('yaLogoutForm').submit();">Sign Out</a>
                    <form id="yaLogoutForm" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
                </div>
            </div>

            <h1 class="ya-page-title">Your Account</h1>

            {{-- KYC Notification Banner for Author / Publisher / Seller / Incomplete Profiles --}}
            @php
                $isAuthor = $user->role === 'author' || $user->reg_type === 'author';
                $isPublisher = $user->role === 'publisher' || $user->reg_type === 'publisher';
                $isSeller = $user->role === 'seller' || $user->reg_type === 'seller';
                $regData = is_array($user->reg_data) ? $user->reg_data : [];
                $hasKyc = !empty($regData['nid']) || !empty($regData['bio']) || !empty($regData['kyc_submitted_at']);
            @endphp

            @if($isAuthor || $isPublisher || $isSeller)
                <div class="alert alert-light border rounded-3 p-3.5 mb-4 shadow-2xs d-flex align-items-center justify-content-between flex-wrap gap-3" style="background: linear-gradient(135deg, #f0fdf4 0%, #e0f2fe 100%); border-left: 5px solid #0284c7 !important;">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center bg-white shadow-xs" style="width: 44px; height: 44px; color: #0284c7; font-size: 20px;">
                            <i class="fa-solid fa-id-card"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-dark" style="font-size: 15px;">
                                @if($isAuthor) লেখক প্রোফাইল ও KYC ভেরিফিকেশন @elseif($isPublisher) প্রকাশক প্রোফাইল ও KYC ভেরিফিকেশন @else বিক্রেতা প্রোফাইল ও KYC ভেরিফিকেশন @endif
                                @if($user->reg_status === 'approved')
                                    <span class="badge bg-success text-white rounded-pill ms-2" style="font-size: 11px;"><i class="fa-solid fa-circle-check me-1"></i>ভেরিফাইড</span>
                                @elseif($hasKyc)
                                    <span class="badge bg-warning text-dark rounded-pill ms-2" style="font-size: 11px;"><i class="fa-solid fa-clock me-1"></i>অনুমোদনের অপেক্ষায়</span>
                                @else
                                    <span class="badge bg-info text-dark rounded-pill ms-2" style="font-size: 11px;"><i class="fa-solid fa-circle-info me-1"></i>তথ্য পূরণ করুন</span>
                                @endif
                            </div>
                            <small class="text-secondary">ছবি, লেখকের নাম (বাংলা ও ইংরেজি), সাহিত্য শাখা, জীবনী ও NID আপলোড করে আপনার প্রোফাইল সম্পূর্ণ করুন।</small>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-primary rounded-pill px-4 fw-bold shadow-xs" onclick="openSectionPanel('kyc')">
                        <i class="fa-solid fa-pen-to-square me-1.5"></i> KYC আপডেট করুন
                    </button>
                </div>
            @endif

            {{-- ───────────────────────────────────────────────────────────────── --}}
            {{-- 1. ORDERS & PURCHASES                                             --}}
            {{-- ───────────────────────────────────────────────────────────────── --}}
            <h2 class="ya-section-title">
                <i class="fa-solid fa-bag-shopping text-primary" style="font-size: 17px;"></i>
                <span>Orders & Purchases</span>
            </h2>

            <div class="ya-cards-grid">
                <!-- 1.1 Your Orders -->
                <div class="ya-card-tile" onclick="openSectionPanel('orders')">
                    <div class="ya-card-icon-box">
                        <i class="fa-solid fa-box-open"></i>
                    </div>
                    <div>
                        <div class="ya-card-title-text">Your Orders</div>
                        <p class="ya-card-desc-text">Track orders, initiate returns or cancellations, download invoices, or repurchase items.</p>
                    </div>
                </div>

                <!-- 1.2 Your Wishlist -->
                <div class="ya-card-tile" onclick="openSectionPanel('wishlist')">
                    <div class="ya-card-icon-box">
                        <i class="fa-solid fa-heart"></i>
                    </div>
                    <div>
                        <div class="ya-card-title-text">Your Wishlist</div>
                        <p class="ya-card-desc-text">View, edit, or share your saved books, or create new custom lists.</p>
                    </div>
                </div>

                <!-- 1.3 Buy Again -->
                <div class="ya-card-tile" onclick="openSectionPanel('buyAgain')">
                    <div class="ya-card-icon-box">
                        <i class="fa-solid fa-arrows-rotate"></i>
                    </div>
                    <div>
                        <div class="ya-card-title-text">Buy Again</div>
                        <p class="ya-card-desc-text">Quick access to easily reorder books you have previously purchased.</p>
                    </div>
                </div>
            </div>

            {{-- ───────────────────────────────────────────────────────────────── --}}
            {{-- 2. ACCOUNT & SECURITY                                             --}}
            {{-- ───────────────────────────────────────────────────────────────── --}}
            <h2 class="ya-section-title">
                <i class="fa-solid fa-shield-halved text-primary" style="font-size: 17px;"></i>
                <span>Account & Security</span>
            </h2>

            <div class="ya-cards-grid">
                <!-- 2.1 KYC & Profile Verification -->
                <div class="ya-card-tile" onclick="openSectionPanel('kyc')">
                    <div class="ya-card-icon-box">
                        <i class="fa-solid fa-id-card"></i>
                    </div>
                    <div>
                        <div class="ya-card-title-text">KYC & Verification</div>
                        <p class="ya-card-desc-text">Update profile photo, Bengali/English author name, literary genres, bio, and NID identity verification.</p>
                    </div>
                </div>

                <!-- 2.2 Login & Security -->
                <div class="ya-card-tile" onclick="openSectionPanel('loginSecurity')">
                    <div class="ya-card-icon-box">
                        <i class="fa-solid fa-lock"></i>
                    </div>
                    <div>
                        <div class="ya-card-title-text">Login & Security</div>
                        <p class="ya-card-desc-text">Edit your account name, mobile number, email address, or update your password.</p>
                    </div>
                </div>

                <!-- 2.3 Your Addresses -->
                <div class="ya-card-tile" onclick="openSectionPanel('addresses')">
                    <div class="ya-card-icon-box">
                        <i class="fa-solid fa-location-dot"></i>
                    </div>
                    <div>
                        <div class="ya-card-title-text">Your Addresses</div>
                        <p class="ya-card-desc-text">Add, edit, or manage delivery addresses and set your primary shipping address.</p>
                    </div>
                </div>

                <!-- 2.4 Your Payments -->
                <div class="ya-card-tile" onclick="openSectionPanel('payments')">
                    <div class="ya-card-icon-box">
                        <i class="fa-solid fa-credit-card"></i>
                    </div>
                    <div>
                        <div class="ya-card-title-text">Your Payments</div>
                        <p class="ya-card-desc-text">View transaction history and manage saved payment options (bKash, Nagad, Cards).</p>
                    </div>
                </div>
            </div>

            {{-- ───────────────────────────────────────────────────────────────── --}}
            {{-- 3. SUBSCRIPTIONS & GIFTING                                        --}}
            {{-- ───────────────────────────────────────────────────────────────── --}}
            <h2 class="ya-section-title">
                <i class="fa-solid fa-gift text-primary" style="font-size: 17px;"></i>
                <span>Subscriptions & Gifting</span>
            </h2>

            <div class="ya-cards-grid">
                <!-- 3.1 Idea Premium Membership -->
                <div class="ya-card-tile" onclick="openSectionPanel('premium')">
                    <div class="ya-card-icon-box">
                        <i class="fa-solid fa-crown"></i>
                    </div>
                    <div>
                        <div class="ya-card-title-text">Idea Premium Membership</div>
                        <p class="ya-card-desc-text">Manage your membership plan, view exclusive reader benefits, and update billing settings.</p>
                    </div>
                </div>

                <!-- 3.2 Gift Cards & Vouchers -->
                <div class="ya-card-tile" onclick="openSectionPanel('giftCards')">
                    <div class="ya-card-icon-box">
                        <i class="fa-solid fa-ticket"></i>
                    </div>
                    <div>
                        <div class="ya-card-title-text">Gift Cards & Vouchers</div>
                        <p class="ya-card-desc-text">Check your gift card balance, redeem digital vouchers, or purchase new gift cards.</p>
                    </div>
                </div>

                <!-- 3.3 Digital Services & E-Reader Support -->
                <div class="ya-card-tile" onclick="openSectionPanel('digitalServices')">
                    <div class="ya-card-icon-box">
                        <i class="fa-solid fa-tablet-screen-button"></i>
                    </div>
                    <div>
                        <div class="ya-card-title-text">Digital Services & E-Reader Support</div>
                        <p class="ya-card-desc-text">Manage e-book subscriptions, digital downloads, and troubleshoot reading device issues.</p>
                    </div>
                </div>
            </div>

            {{-- ───────────────────────────────────────────────────────────────── --}}
            {{-- 4. CORPORATE & FAMILY                                             --}}
            {{-- ───────────────────────────────────────────────────────────────── --}}
            <h2 class="ya-section-title">
                <i class="fa-solid fa-building-user text-primary" style="font-size: 17px;"></i>
                <span>Corporate & Family</span>
            </h2>

            <div class="ya-cards-grid">
                <!-- 4.1 Business Account -->
                <div class="ya-card-tile" onclick="openSectionPanel('businessAccount')">
                    <div class="ya-card-icon-box">
                        <i class="fa-solid fa-briefcase"></i>
                    </div>
                    <div>
                        <div class="ya-card-title-text">Business Account</div>
                        <p class="ya-card-desc-text">Access business-exclusive book pricing, request bulk orders, and set custom delivery schedules for institutions.</p>
                    </div>
                </div>

                <!-- 4.2 Family Profiles -->
                <div class="ya-card-tile" onclick="openSectionPanel('familyProfiles')">
                    <div class="ya-card-icon-box">
                        <i class="fa-solid fa-people-roof"></i>
                    </div>
                    <div>
                        <div class="ya-card-title-text">Family Profiles</div>
                        <p class="ya-card-desc-text">Create and manage profiles for family members, and control sharing permissions.</p>
                    </div>
                </div>
            </div>

            {{-- ───────────────────────────────────────────────────────────────── --}}
            {{-- 5. COMMUNICATION & HELP                                           --}}
            {{-- ───────────────────────────────────────────────────────────────── --}}
            <h2 class="ya-section-title">
                <i class="fa-solid fa-headset text-primary" style="font-size: 17px;"></i>
                <span>Communication & Help</span>
            </h2>

            <div class="ya-cards-grid">
                <!-- 5.1 Your Messages -->
                <div class="ya-card-tile" onclick="openSectionPanel('messages')">
                    <div class="ya-card-icon-box">
                        <i class="fa-solid fa-envelope-open-text"></i>
                    </div>
                    <div>
                        <div class="ya-card-title-text">Your Messages</div>
                        <p class="ya-card-desc-text">View and reply to notifications, seller updates, and system communications.</p>
                    </div>
                </div>

                <!-- 5.2 Customer Service -->
                <div class="ya-card-tile" onclick="openSectionPanel('customerService')">
                    <div class="ya-card-icon-box">
                        <i class="fa-solid fa-circle-question"></i>
                    </div>
                    <div>
                        <div class="ya-card-title-text">Customer Service</div>
                        <p class="ya-card-desc-text">Browse help topics, access self-service solutions, or get in touch with our support team.</p>
                    </div>
                </div>
            </div>

            {{-- ───────────────────────────────────────────────────────────────── --}}
            {{-- BOTTOM DIRECTORY & SERVICES LINKS (Amazon-Style Navigation Hub)  --}}
            {{-- ───────────────────────────────────────────────────────────────── --}}
            <div class="border-top pt-4 mt-5">
                <div class="row g-4 text-start">
                    <div class="col-md-6 col-lg-3">
                        <h6 class="fw-bold text-dark mb-2.5" style="font-size: 14px;">Digital Content & Devices</h6>
                        <ul class="list-unstyled d-flex flex-column gap-1.5 small text-muted">
                            <li><a href="javascript:void(0)" onclick="openSectionPanel('digitalServices')" class="text-decoration-none text-secondary">eBook Library & Downloads</a></li>
                            <li><a href="{{ route('webzine.index') }}" class="text-decoration-none text-secondary">Webzines & Literary Articles</a></li>
                            <li><a href="{{ route('ebook.index') }}" class="text-decoration-none text-secondary">Digital Audiobooks & Reading</a></li>
                            <li><a href="javascript:void(0)" onclick="openSectionPanel('digitalServices')" class="text-decoration-none text-secondary">Manage Your Content and Devices</a></li>
                        </ul>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <h6 class="fw-bold text-dark mb-2.5" style="font-size: 14px;">Email Alerts, Messages & Ads</h6>
                        <ul class="list-unstyled d-flex flex-column gap-1.5 small text-muted">
                            <li><a href="javascript:void(0)" onclick="openSectionPanel('messages')" class="text-decoration-none text-secondary">Message Center</a></li>
                            <li><a href="javascript:void(0)" onclick="openSectionPanel('messages')" class="text-decoration-none text-secondary">Order & Shipping Updates</a></li>
                            <li><a href="javascript:void(0)" onclick="openSectionPanel('messages')" class="text-decoration-none text-secondary">Author Newsletter Preferences</a></li>
                            <li><a href="javascript:void(0)" onclick="openSectionPanel('messages')" class="text-decoration-none text-secondary">Communication Preferences</a></li>
                        </ul>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <h6 class="fw-bold text-dark mb-2.5" style="font-size: 14px;">More Ways to Pay & Royalties</h6>
                        <ul class="list-unstyled d-flex flex-column gap-1.5 small text-muted">
                            <li><a href="javascript:void(0)" onclick="openSectionPanel('payments')" class="text-decoration-none text-secondary">bKash & Nagad Wallet</a></li>
                            <li><a href="javascript:void(0)" onclick="openSectionPanel('kyc')" class="text-decoration-none text-secondary">Author Royalty Payout Settings</a></li>
                            <li><a href="javascript:void(0)" onclick="openSectionPanel('giftCards')" class="text-decoration-none text-secondary">Redeem Idea Gift Vouchers</a></li>
                            <li><a href="javascript:void(0)" onclick="openSectionPanel('payments')" class="text-decoration-none text-secondary">Payment & Billing History</a></li>
                        </ul>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <h6 class="fw-bold text-dark mb-2.5" style="font-size: 14px;">Publishing & Author Central</h6>
                        <ul class="list-unstyled d-flex flex-column gap-1.5 small text-muted">
                            <li><a href="javascript:void(0)" onclick="openSectionPanel('kyc')" class="text-decoration-none text-secondary">Author / Publisher KYC Verification</a></li>
                            @if(Route::has('author.dashboard'))
                                <li><a href="{{ route('author.dashboard') }}" class="text-decoration-none text-secondary">Idea Author Central Dashboard</a></li>
                            @endif
                            <li><a href="{{ route('book.index') }}" class="text-decoration-none text-secondary">Publish New Manuscripts & Books</a></li>
                            <li><a href="javascript:void(0)" onclick="openSectionPanel('customerService')" class="text-decoration-none text-secondary">Publisher & Seller Support</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>


        {{-- ═════════════════════════════════════════════════════════════════════ --}}
        {{-- VIEW 2: INTERACTIVE DETAIL PANELS                                     --}}
        {{-- ═════════════════════════════════════════════════════════════════════ --}}

        <!-- PANEL: YOUR ORDERS -->
        <div class="ya-detail-panel" id="panel_orders">
            <span class="ya-back-nav" onclick="closeAllPanels()">&lsaquo; Your Account</span>
            <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                <h3 class="fw-bold mb-0">Your Orders</h3>
                <span class="badge bg-light text-dark border px-3 py-1.5 font-monospace">Total: {{ $myOrders->total() ?? 0 }} Orders</span>
            </div>

            @if($myOrders->count() > 0)
                <div class="d-flex flex-column gap-3">
                    @foreach($myOrders as $order)
                        <div class="card border rounded-3 overflow-hidden shadow-2xs">
                            <div class="card-header bg-light py-2.5 px-3 d-flex align-items-center justify-content-between flex-wrap gap-2 text-secondary small">
                                <div>
                                    <span class="text-uppercase fw-bold text-muted">Order Placed:</span> {{ $order->created_at->format('d M, Y') }}
                                </div>
                                <div>
                                    <span class="text-uppercase fw-bold text-muted">Total:</span> ৳{{ number_format($order->total_amount ?? 0) }}
                                </div>
                                <div>
                                    <span class="text-uppercase fw-bold text-muted">Order #</span> <strong class="text-dark">{{ $order->order_number ?? $order->id }}</strong>
                                </div>
                                <div>
                                    <span class="badge bg-{{ $order->status === 'delivered' ? 'success' : ($order->status === 'cancelled' ? 'danger' : 'warning text-dark') }} px-2.5 py-1">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </div>
                            </div>
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                    <div>
                                        <h6 class="fw-bold mb-1 text-dark">{{ $order->book->title ?? ($order->customer_name . ' Order') }}</h6>
                                        <small class="text-muted">Quantity: {{ $order->quantity ?? 1 }} &bull; Delivery: {{ $order->district ?? 'Standard' }}</small>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('my-account.orders.details', $order->id) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">View Details</a>
                                        <a href="{{ route('book.index') }}" class="btn btn-sm btn-primary rounded-pill px-3">Buy Again</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-5 bg-light rounded-4 border">
                    <i class="fa-solid fa-box-open text-muted fs-1 mb-2"></i>
                    <h5 class="fw-bold text-dark">No orders placed yet</h5>
                    <p class="text-muted small mb-3">Browse our extensive catalogue of books and ebooks.</p>
                    <a href="{{ route('book.index') }}" class="btn btn-primary rounded-pill px-4">Start Shopping</a>
                </div>
            @endif
        </div>

        <!-- PANEL: YOUR WISHLIST -->
        <div class="ya-detail-panel" id="panel_wishlist">
            <span class="ya-back-nav" onclick="closeAllPanels()">&lsaquo; Your Account</span>
            <h3 class="fw-bold mb-3 border-bottom pb-2">Your Wishlist</h3>
            @if(isset($wishlistItems) && $wishlistItems->count() > 0)
                <div class="row g-3">
                    @foreach($wishlistItems as $item)
                        <div class="col-md-6 col-lg-4">
                            <div class="card h-100 border rounded-3 p-3 shadow-2xs">
                                <h6 class="fw-bold text-dark mb-1">{{ $item->book->title ?? 'Saved Book' }}</h6>
                                <small class="text-muted mb-2 d-block">By {{ $item->book->author_name ?? 'Idea Author' }}</small>
                                <div class="mt-auto d-flex align-items-center justify-content-between pt-2 border-top">
                                    <span class="fw-bold text-primary">৳{{ number_format($item->book->sale_price ?? $item->book->regular_price ?? 0) }}</span>
                                    <form action="{{ route('my-account.wishlist.remove', $item->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-link text-danger text-decoration-none p-0">Remove</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-5 bg-light rounded-4 border">
                    <i class="fa-solid fa-heart text-muted fs-1 mb-2"></i>
                    <h5 class="fw-bold text-dark">Your Wishlist is empty</h5>
                    <p class="text-muted small mb-3">Save books you want to read later.</p>
                    <a href="{{ route('book.index') }}" class="btn btn-primary rounded-pill px-4">Explore Books</a>
                </div>
            @endif
        </div>

        <!-- PANEL: BUY AGAIN -->
        <div class="ya-detail-panel" id="panel_buyAgain">
            <span class="ya-back-nav" onclick="closeAllPanels()">&lsaquo; Your Account</span>
            <h3 class="fw-bold mb-3 border-bottom pb-2">Buy Again</h3>
            <p class="text-muted small mb-3">Recommended reorders based on your purchase history.</p>
            <div class="row g-3">
                @forelse($myOrders->take(4) as $o)
                    <div class="col-md-6">
                        <div class="card p-3 border rounded-3 d-flex flex-row align-items-center justify-content-between">
                            <div>
                                <h6 class="fw-bold mb-0 text-dark">{{ $o->book->title ?? 'Book Item' }}</h6>
                                <small class="text-muted">Purchased on {{ $o->created_at->format('d M, Y') }}</small>
                            </div>
                            <a href="{{ route('book.index') }}" class="btn btn-sm btn-primary rounded-pill px-3">Reorder</a>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center py-4 bg-light rounded-3">
                        <span class="text-muted small">No previous items to reorder yet.</span>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- PANEL: KYC & PROFILE VERIFICATION -->
        <div class="ya-detail-panel" id="panel_kyc">
            <span class="ya-back-nav" onclick="closeAllPanels()">&lsaquo; Your Account</span>
            <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2 flex-wrap gap-2">
                <div>
                    <h3 class="fw-bold mb-0 text-dark">
                        <i class="fa-solid fa-id-card text-primary me-2"></i>
                        @if($isAuthor) লেখক পরিচিতি ও KYC ভেরিফিকেশন @elseif($isPublisher) প্রকাশক প্রোফাইল ও KYC ভেরিফিকেশন @elseif($isSeller) বিক্রেতা প্রোফাইল ও KYC ভেরিফিকেশন @else গ্রাহক পরিচিতি ও ঠিকানা @endif
                    </h3>
                    <small class="text-secondary">আপনার তথ্য নির্ভুলভাবে প্রদান করুন। এডমিন অনুমোদনের পর প্রোফাইল ব্যাজ প্রদর্শিত হবে।</small>
                </div>
                <div>
                    @if($user->reg_status === 'approved')
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1.5 rounded-pill fw-bold">
                            <i class="fa-solid fa-circle-check me-1"></i> ভেরিফাইড প্রোফাইল
                        </span>
                    @elseif($hasKyc)
                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-1.5 rounded-pill fw-bold">
                            <i class="fa-solid fa-clock me-1"></i> অনুমোদনের অপেক্ষায়
                        </span>
                    @else
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-1.5 rounded-pill fw-bold">
                            <i class="fa-solid fa-triangle-exclamation me-1"></i> KYC অসম্পূর্ণ
                        </span>
                    @endif
                </div>
            </div>

            <form action="{{ route('my-account.kyc.update') }}" method="POST" enctype="multipart/form-data" id="yaKycForm">
                @csrf
                <div class="row g-4">
                    {{-- Left Column: Avatar & Personal Identity --}}
                    <div class="col-lg-5">
                        <div class="card p-4 border rounded-3 bg-light shadow-2xs h-100">
                            <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-camera text-primary me-1.5"></i> প্রোফাইল ছবি / পোর্ট্রেট</h6>
                            
                            <div class="text-center mb-3">
                                <div class="position-relative mx-auto rounded-circle border border-3 border-primary shadow-sm bg-white overflow-hidden" style="width: 130px; height: 130px; cursor: pointer;" onclick="document.getElementById('kycAvatarInput').click()">
                                    @if($user->avatar)
                                        <img id="kycAvatarPreview" src="{{ asset('storage/' . $user->avatar) }}" alt="Avatar" style="width: 100%; height: 100%; object-fit: cover;">
                                    @else
                                        <div id="kycAvatarPlaceholder" class="w-100 h-100 d-flex flex-column align-items-center justify-content-center text-muted">
                                            <i class="fa-solid fa-cloud-arrow-up fs-2 text-primary mb-1"></i>
                                            <span style="font-size: 11px;" class="fw-semibold">ছবি আপলোড</span>
                                        </div>
                                        <img id="kycAvatarPreview" src="" alt="Avatar" class="d-none" style="width: 100%; height: 100%; object-fit: cover;">
                                    @endif
                                </div>
                                <input type="file" name="avatar" id="kycAvatarInput" accept="image/*" class="d-none" onchange="previewKycPhoto(this)">
                                <small class="text-muted d-block mt-2" style="font-size: 11px;">ছবি পরিবর্তন করতে ক্লিক করুন (Max: 5MB)</small>
                            </div>

                            @if($isAuthor)
                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-dark">লেখকের নাম (বাংলা) <span class="text-danger">*</span></label>
                                    <input type="text" name="name_bn" value="{{ old('name_bn', $regData['name_bn'] ?? $user->name) }}" class="form-control form-control-sm" placeholder="যেমন: অমরেশ দত্ত" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-dark">Author Name (English) <span class="text-danger">*</span></label>
                                    <input type="text" name="name_en" value="{{ old('name_en', $regData['name_en'] ?? '') }}" class="form-control form-control-sm" placeholder="e.g. Amaresh Datta" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-dark">ছদ্মনাম / পরিচিত নাম (ঐচ্ছিক)</label>
                                    <input type="text" name="pen_name" value="{{ old('pen_name', $regData['pen_name'] ?? '') }}" class="form-control form-control-sm" placeholder="যদি থাকে">
                                </div>
                            @elseif($isPublisher)
                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-dark">প্রকাশনা সংস্থার নাম <span class="text-danger">*</span></label>
                                    <input type="text" name="publisher_name" value="{{ old('publisher_name', $regData['publisher_name'] ?? $user->name) }}" class="form-control form-control-sm" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-dark">প্রতিষ্ঠার বছর</label>
                                    <input type="number" name="established" value="{{ old('established', $regData['established'] ?? '') }}" class="form-control form-control-sm" placeholder="e.g. 2010">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-dark">ট্রেড লাইসেন্স নম্বর</label>
                                    <input type="text" name="trade_license" value="{{ old('trade_license', $regData['trade_license'] ?? '') }}" class="form-control form-control-sm">
                                </div>
                            @elseif($isSeller)
                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-dark">দোকান / বুকশপের নাম <span class="text-danger">*</span></label>
                                    <input type="text" name="shop_name" value="{{ old('shop_name', $regData['shop_name'] ?? $user->name) }}" class="form-control form-control-sm" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-dark">ট্রেড লাইসেন্স নম্বর</label>
                                    <input type="text" name="trade_license" value="{{ old('trade_license', $regData['trade_license'] ?? '') }}" class="form-control form-control-sm">
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Right Column: Role Details, Bio, NID Verification, and Payout --}}
                    <div class="col-lg-7">
                        <div class="card p-4 border rounded-3 bg-white shadow-2xs">
                            @if($isAuthor)
                                {{-- Literary Genre Multi-Choice Tags --}}
                                <div class="mb-3.5">
                                    <label class="form-label small fw-bold text-dark mb-1.5">
                                        <i class="fa-solid fa-feather text-primary me-1"></i> সাহিত্য শাখা / লেখার ধরন (একাধিক টিকচিহ্ন নির্বাচন করুন):
                                    </label>
                                    @php
                                        $presetGenres = [
                                            'কবিতা (Poetry)', 'উপন্যাস (Novel)', 'ছোটগল্প (Short Story)', 
                                            'প্রবন্ধ ও গবেষণা (Research)', 'শিশুসাহিত্য (Children)', 
                                            'অনুবাদ (Translation)', 'বিজ্ঞান কল্পকাহিনী (Sci-Fi)', 
                                            'ইতিহাস ও ঐতিহ্য (History)', 'ইসলামিক সাহিত্য (Islamic)', 
                                            'নাটক ও চিত্রনাট্য (Drama)', 'স্মৃতিকথা ও জীবনী (Biography)', 'রম্যরচনা (Humor)'
                                        ];
                                        $userGenres = (array) ($regData['genres'] ?? []);
                                    @endphp
                                    <div class="d-flex flex-wrap gap-2 pt-1">
                                        @foreach($presetGenres as $g)
                                            <div class="form-check-inline m-0">
                                                <input type="checkbox" name="genres[]" value="{{ $g }}" id="kyc_genre_{{ Str::slug($g) }}" 
                                                       class="btn-check" {{ in_array($g, $userGenres) ? 'checked' : '' }}>
                                                <label class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 text-nowrap" for="kyc_genre_{{ Str::slug($g) }}" style="font-size: 12px;">
                                                    <i class="fa-solid fa-check small me-1"></i>{{ $g }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                {{-- Bio Textarea --}}
                                <div class="mb-3.5">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <label class="form-label small fw-bold text-dark mb-0">
                                            <i class="fa-solid fa-align-left text-primary me-1"></i> সংক্ষিপ্ত লেখক পরিচিতি / জীবনী
                                        </label>
                                        <span class="text-muted small" style="font-size: 11px;">(সর্বোচ্চ ৫০০০ অক্ষর)</span>
                                    </div>
                                    <textarea name="bio" rows="4" class="form-control" placeholder="আপনার সাহিত্যকর্ম, প্রকাশিত বই, পুরস্কার বা পড়াশোনার সংক্ষিপ্ত বিবরণ...">{{ old('bio', $regData['bio'] ?? '') }}</textarea>
                                </div>
                            @endif

                            {{-- NID & Verification Document --}}
                            <div class="mb-3.5 p-3 bg-light rounded-3 border">
                                <h6 class="fw-bold text-dark mb-2" style="font-size: 13.5px;">
                                    <i class="fa-solid fa-shield-halved text-success me-1.5"></i> জাতীয় পরিচয়পত্র (NID) ভেরিফিকেশন
                                </h6>
                                <div class="row g-2">
                                    <div class="col-sm-6">
                                        <label class="form-label small fw-semibold text-secondary">NID / পাসপোর্ট নম্বর</label>
                                        <input type="text" name="nid" value="{{ old('nid', $regData['nid'] ?? '') }}" class="form-control form-control-sm" placeholder="জাতীয় পরিচয়পত্র নম্বর">
                                    </div>
                                    <div class="col-sm-6">
                                        <label class="form-label small fw-semibold text-secondary">NID ফাইল আপলোড (PDF/Image)</label>
                                        <input type="file" name="nid_file" class="form-control form-control-sm" accept="image/*,application/pdf">
                                    </div>
                                </div>
                                @if(!empty($regData['nid_file']))
                                    <div class="mt-2 small text-success">
                                        <i class="fa-solid fa-file-circle-check me-1"></i> ডকুমেন্ট ইতিমধ্যে আপলোড করা আছে
                                    </div>
                                @endif
                            </div>

                            {{-- Royalty Payout Details --}}
                            @if($isAuthor || $isPublisher || $isSeller)
                                <div class="mb-3.5 p-3 bg-light rounded-3 border">
                                    <h6 class="fw-bold text-dark mb-2" style="font-size: 13.5px;">
                                        <i class="fa-solid fa-wallet text-primary me-1.5"></i> রয়্যালটি / আয় উত্তোলনের মাধ্যম
                                    </h6>
                                    <div class="row g-2">
                                        <div class="col-sm-4">
                                            <label class="form-label small fw-semibold text-secondary">পদ্ধতি</label>
                                            <select name="payout_account_type" class="form-select form-select-sm">
                                                <option value="bkash" @selected(($regData['payout_type'] ?? '') === 'bkash')>বিকাশ (bKash)</option>
                                                <option value="nagad" @selected(($regData['payout_type'] ?? '') === 'nagad')>নগদ (Nagad)</option>
                                                <option value="rocket" @selected(($regData['payout_type'] ?? '') === 'rocket')>রকেট (Rocket)</option>
                                                <option value="bank" @selected(($regData['payout_type'] ?? '') === 'bank')>ব্যাংক একাউন্ট</option>
                                            </select>
                                        </div>
                                        <div class="col-sm-8">
                                            <label class="form-label small fw-semibold text-secondary">একাউন্ট / মোবাইল নম্বর বা ব্যাংক বিবরণ</label>
                                            <input type="text" name="payout_account_details" value="{{ old('payout_account_details', $regData['payout_details'] ?? '') }}" class="form-control form-control-sm" placeholder="017XXXXXXXX বা Bank, A/C, Branch">
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- Address Details --}}
                            <div class="mb-3">
                                <h6 class="fw-bold text-dark mb-2" style="font-size: 13.5px;">
                                    <i class="fa-solid fa-location-dot text-primary me-1.5"></i> ঠিকানা
                                </h6>
                                <div class="row g-2">
                                    <div class="col-sm-6">
                                        <input type="text" name="district" value="{{ old('district', $regData['district'] ?? ($defaultAddress['district'] ?? '')) }}" class="form-control form-control-sm" placeholder="জেলা (District)">
                                    </div>
                                    <div class="col-sm-6">
                                        <input type="text" name="thana" value="{{ old('thana', $regData['thana'] ?? ($defaultAddress['thana'] ?? '')) }}" class="form-control form-control-sm" placeholder="থানা (Thana)">
                                    </div>
                                    <div class="col-12 mt-2">
                                        <textarea name="address" rows="2" class="form-control form-control-sm" placeholder="সম্পূর্ণ ঠিকানা...">{{ old('address', $regData['address'] ?? ($defaultAddress['address'] ?? '')) }}</textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="pt-2 border-top d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-xs">
                                    <i class="fa-solid fa-floppy-disk me-1.5"></i> KYC তথ্য সংরক্ষণ করুন
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- PANEL: LOGIN & SECURITY -->
        <div class="ya-detail-panel" id="panel_loginSecurity">
            <span class="ya-back-nav" onclick="closeAllPanels()">&lsaquo; Your Account</span>
            <h3 class="fw-bold mb-3 border-bottom pb-2">Login & Security</h3>
            
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="card p-4 border rounded-3 shadow-2xs">
                        <h5 class="fw-bold mb-3 text-dark">Update Profile Info</h5>
                        <form action="{{ route('my-account.profile.update') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Name</label>
                                <input type="text" name="name" value="{{ old('name', $user->name) }}" class="form-control form-control-sm" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Email</label>
                                <input type="email" name="email" value="{{ old('email', str_contains($user->email, '@buyer.ideaabd.com') ? '' : $user->email) }}" class="form-control form-control-sm">
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Phone Number</label>
                                <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="form-control form-control-sm" required>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4">Save Changes</button>
                        </form>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card p-4 border rounded-3 shadow-2xs">
                        <h5 class="fw-bold mb-3 text-dark">Change Password</h5>
                        <form action="{{ route('my-account.password.update') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Current Password</label>
                                <input type="password" name="current_password" class="form-control form-control-sm" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">New Password</label>
                                <input type="password" name="password" class="form-control form-control-sm" minlength="8" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Confirm New Password</label>
                                <input type="password" name="password_confirmation" class="form-control form-control-sm" required>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4">Update Password</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- PANEL: YOUR ADDRESSES -->
        <div class="ya-detail-panel" id="panel_addresses">
            <span class="ya-back-nav" onclick="closeAllPanels()">&lsaquo; Your Account</span>
            <h3 class="fw-bold mb-3 border-bottom pb-2">Your Addresses</h3>
            <div class="card p-4 border rounded-3 max-w-lg shadow-2xs" style="max-width: 650px;">
                <h6 class="fw-bold text-dark mb-3">Primary Delivery Address</h6>
                <form action="{{ route('my-account.address.update') }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">District</label>
                            <input type="text" name="district" value="{{ old('district', $defaultAddress['district'] ?? '') }}" class="form-control form-control-sm" placeholder="e.g. Dhaka">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Thana / Area</label>
                            <input type="text" name="thana" value="{{ old('thana', $defaultAddress['thana'] ?? '') }}" class="form-control form-control-sm" placeholder="e.g. Dhanmondi">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">Full Street Address</label>
                            <textarea name="address" rows="3" class="form-control form-control-sm" placeholder="House, Road, Apartment details...">{{ old('address', $defaultAddress['address'] ?? '') }}</textarea>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4 mt-3">Update Address</button>
                </form>
            </div>
        </div>

        <!-- PANEL: YOUR PAYMENTS -->
        <div class="ya-detail-panel" id="panel_payments">
            <span class="ya-back-nav" onclick="closeAllPanels()">&lsaquo; Your Account</span>
            <h3 class="fw-bold mb-3 border-bottom pb-2">Your Payments</h3>
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="card p-3 border rounded-3 text-center bg-light">
                        <i class="fa-solid fa-mobile-screen fs-2 text-danger mb-2"></i>
                        <h6 class="fw-bold mb-1">bKash Payment</h6>
                        <small class="text-muted">Instant mobile gateway supported</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card p-3 border rounded-3 text-center bg-light">
                        <i class="fa-solid fa-wallet fs-2 text-warning mb-2"></i>
                        <h6 class="fw-bold mb-1">Nagad / Rocket</h6>
                        <small class="text-muted">Direct digital payout & checkout</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card p-3 border rounded-3 text-center bg-light">
                        <i class="fa-solid fa-credit-card fs-2 text-primary mb-2"></i>
                        <h6 class="fw-bold mb-1">Debit / Credit Cards</h6>
                        <small class="text-muted">Visa, Mastercard, AMEX encrypted</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- PANEL: IDEA PREMIUM MEMBERSHIP -->
        <div class="ya-detail-panel" id="panel_premium">
            <span class="ya-back-nav" onclick="closeAllPanels()">&lsaquo; Your Account</span>
            <h3 class="fw-bold mb-3 border-bottom pb-2">Idea Premium Membership</h3>
            <div class="card p-4 border rounded-3 bg-light">
                <div class="d-flex align-items-center gap-3">
                    <i class="fa-solid fa-crown text-warning fs-1"></i>
                    <div>
                        <h5 class="fw-bold text-dark mb-1">Reader Loyalty Program</h5>
                        <p class="text-muted small mb-0">Current Points: <strong>{{ number_format($user->loyalty_points ?? 0) }} Points</strong></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- PANEL: GIFT CARDS & VOUCHERS -->
        <div class="ya-detail-panel" id="panel_giftCards">
            <span class="ya-back-nav" onclick="closeAllPanels()">&lsaquo; Your Account</span>
            <h3 class="fw-bold mb-3 border-bottom pb-2">Gift Cards & Vouchers</h3>
            <div class="card p-4 border rounded-3 max-w-lg" style="max-width: 500px;">
                <h6 class="fw-bold mb-2">Redeem a Voucher</h6>
                <div class="input-group mb-2">
                    <input type="text" class="form-control font-monospace" placeholder="Enter voucher code...">
                    <button class="btn btn-primary px-3 fw-bold">Apply</button>
                </div>
                <small class="text-muted">Vouchers are automatically applied at checkout.</small>
            </div>
        </div>

        <!-- PANEL: DIGITAL SERVICES & E-READER SUPPORT -->
        <div class="ya-detail-panel" id="panel_digitalServices">
            <span class="ya-back-nav" onclick="closeAllPanels()">&lsaquo; Your Account</span>
            <h3 class="fw-bold mb-3 border-bottom pb-2">Digital Services & E-Reader Library</h3>
            @if(isset($myEbooks) && $myEbooks->count() > 0)
                <div class="row g-3">
                    @foreach($myEbooks as $eb)
                        <div class="col-md-6 col-lg-4">
                            <div class="card p-3 border rounded-3 h-100">
                                <h6 class="fw-bold mb-1">{{ $eb->ebook->title ?? 'Ebook' }}</h6>
                                <small class="text-muted mb-2">Author: {{ $eb->ebook->author->name ?? 'Idea Author' }}</small>
                                <a href="{{ route('ebooks.read', $eb->ebook->slug ?? $eb->ebook_id) }}" class="btn btn-sm btn-primary rounded-pill mt-auto">Read Online</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-4 bg-light rounded-3 border">
                    <span class="text-muted small">No digital e-books in your library yet.</span>
                </div>
            @endif
        </div>

        <!-- PANEL: BUSINESS ACCOUNT -->
        <div class="ya-detail-panel" id="panel_businessAccount">
            <span class="ya-back-nav" onclick="closeAllPanels()">&lsaquo; Your Account</span>
            <h3 class="fw-bold mb-3 border-bottom pb-2">Business Account & Institutional Orders</h3>
            <div class="card p-4 border rounded-3">
                <h5 class="fw-bold text-dark mb-2">Corporate & Bulk Book Purchasing</h5>
                <p class="text-muted small mb-3">Get specialized bulk discounts for schools, universities, libraries, and corporate gifts.</p>
                <a href="{{ route('contact') }}" class="btn btn-primary rounded-pill px-4">Contact Corporate Desk</a>
            </div>
        </div>

        <!-- PANEL: FAMILY PROFILES -->
        <div class="ya-detail-panel" id="panel_familyProfiles">
            <span class="ya-back-nav" onclick="closeAllPanels()">&lsaquo; Your Account</span>
            <h3 class="fw-bold mb-3 border-bottom pb-2">Family Profiles</h3>
            <div class="card p-4 border rounded-3">
                <h6 class="fw-bold mb-2">Manage Family Readers</h6>
                <p class="text-muted small mb-0">Share e-reader reading lists and family reading challenges.</p>
            </div>
        </div>

        <!-- PANEL: YOUR MESSAGES -->
        <div class="ya-detail-panel" id="panel_messages">
            <span class="ya-back-nav" onclick="closeAllPanels()">&lsaquo; Your Account</span>
            <h3 class="fw-bold mb-3 border-bottom pb-2">Your Messages & Notifications</h3>
            <div class="card p-4 border rounded-3 text-center bg-light">
                <i class="fa-solid fa-inbox fs-2 text-muted mb-2"></i>
                <h6 class="fw-bold text-dark mb-1">No unread notifications</h6>
                <small class="text-muted">All system updates and order dispatches will appear here.</small>
            </div>
        </div>

        <!-- PANEL: CUSTOMER SERVICE -->
        <div class="ya-detail-panel" id="panel_customerService">
            <span class="ya-back-nav" onclick="closeAllPanels()">&lsaquo; Your Account</span>
            <h3 class="fw-bold mb-3 border-bottom pb-2">Customer Service & Help Desk</h3>
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="card p-3 border rounded-3 text-center bg-light">
                        <i class="fa-solid fa-headset fs-2 text-primary mb-2"></i>
                        <h6 class="fw-bold mb-1">Direct Support</h6>
                        <small class="text-muted d-block mb-2">Call our helpline</small>
                        <a href="tel:+8801558712810" class="btn btn-sm btn-outline-primary rounded-pill px-3">+8801558712810</a>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card p-3 border rounded-3 text-center bg-light">
                        <i class="fa-brands fa-whatsapp fs-2 text-success mb-2"></i>
                        <h6 class="fw-bold mb-1">WhatsApp Help</h6>
                        <small class="text-muted d-block mb-2">Chat with our team</small>
                        <a href="https://api.whatsapp.com/send?phone=8801558712810" target="_blank" class="btn btn-sm btn-success rounded-pill px-3">Open WhatsApp</a>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card p-3 border rounded-3 text-center bg-light">
                        <i class="fa-solid fa-envelope fs-2 text-info mb-2"></i>
                        <h6 class="fw-bold mb-1">Email Desk</h6>
                        <small class="text-muted d-block mb-2">Send an inquiry</small>
                        <a href="{{ route('contact') }}" class="btn btn-sm btn-outline-info rounded-pill px-3">Contact Form</a>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
/**
 * Interactive Panel Navigation
 */
function openSectionPanel(panelKey) {
    document.getElementById('mainAccountHubView').style.display = 'none';
    document.querySelectorAll('.ya-detail-panel').forEach(p => p.classList.remove('active'));
    
    const target = document.getElementById('panel_' + panelKey);
    if (target) {
        target.classList.add('active');
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
}

function closeAllPanels() {
    document.querySelectorAll('.ya-detail-panel').forEach(p => p.classList.remove('active'));
    document.getElementById('mainAccountHubView').style.display = 'block';
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function previewKycPhoto(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        if (file.size > 5 * 1024 * 1024) {
            alert('File size exceeds 5MB limit.');
            input.value = '';
            return;
        }
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('kycAvatarPreview');
            const placeholder = document.getElementById('kycAvatarPlaceholder');
            if (preview) {
                preview.src = e.target.result;
                preview.classList.remove('d-none');
            }
            if (placeholder) placeholder.style.display = 'none';
        };
        reader.readAsDataURL(file);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const tab = urlParams.get('tab');
    if (tab) {
        openSectionPanel(tab);
    }
});
</script>
@endsection
