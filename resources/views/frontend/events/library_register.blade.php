@extends('layouts.app')

@php
    $libTitle = 'বাৎসরিক বিনামূল্যে বই বিতরণ কর্মসূচি ও পাঠাগার নিবন্ধন ২০২৬';
    $libDesc  = 'আইডিয়া প্রকাশন ও বুকস অব আইডিয়া বাৎসরিক বিনামূল্যে বই বিতরণ কর্মসূচির আওতায় পাঠাগার ও শিক্ষা প্রতিষ্ঠানে বই অনুদান নিবন্ধন ফরম।';
    $libCover = asset('images/og-banner.jpg');
@endphp

@section('title', 'আইডিয়া পাঠাগার — বই অনুদান আবেদন ফরম ২০২৬')
@section('meta_description', Str::limit(strip_tags($libDesc), 180))
@section('meta_keywords', 'পাঠাগার নিবন্ধন, বিনামূল্যে বই বিতরণ, বই অনুদান, পাঠাগার উন্নয়ন, লাইব্রেরি অনুদান, আইডিয়া প্রকাশন')
@section('og_type', 'website')
@section('og_title', $libTitle . ' | আইডিয়া প্রকাশন')
@section('og_description', Str::limit(strip_tags($libDesc), 180))
@section('og_image', $libCover)
@section('og_url', url('/pathagar'))

@section('schema_json')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@type": "Event",
  "name": @json($libTitle),
  "description": @json(Str::limit(strip_tags($libDesc), 300)),
  "image": @json($libCover),
  "url": @json(url('/pathagar')),
  "startDate": "{{ date('c') }}",
  "endDate": "{{ date('c', strtotime('+60 days')) }}",
  "eventStatus": "https://schema.org/EventScheduled",
  "eventAttendanceMode": "https://schema.org/OnlineEventAttendanceMode",
  "organizer": {
    "@@type": "Organization",
    "name": "আইডিয়া প্রকাশন (Idea Publication)",
    "url": "https://www.ideaabd.com"
  },
  "offers": {
    "@@type": "Offer",
    "url": @json(url('/pathagar')),
    "price": "0",
    "priceCurrency": "BDT",
    "availability": "https://schema.org/InStock"
  }
}
</script>
@endsection

@push('styles')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/library-grant.css') }}">
    <style>
        body, .library-grant-wrapper, .lib-main-title, .lib-field-label, .lib-input, .lib-select, .genre-chip-item, .lib-auth-gate-card {
            font-family: 'Hind Siliguri', 'Inter', system-ui, -apple-system, sans-serif;
        }
    </style>
@endpush

@section('content')
<div class="container py-3 py-md-4">
    <div class="library-grant-wrapper">

        {{-- নোটিফিকেশন এলার্ট --}}
        @if(session('error'))
            <div class="alert alert-danger rounded-3 py-2.5 px-3 mb-3 small d-flex align-items-center gap-2">
                <i class="fa-solid fa-circle-exclamation text-danger flex-shrink-0"></i>
                <div><strong>ত্রুটি:</strong> {{ session('error') }}</div>
            </div>
        @endif

        @if(session('success'))
            <div class="alert alert-success rounded-3 py-2.5 px-3 mb-3 small d-flex align-items-center gap-2">
                <i class="fa-solid fa-circle-check text-success flex-shrink-0"></i>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        @if(session('info'))
            <div class="alert alert-info rounded-3 py-2.5 px-3 mb-3 small d-flex align-items-center gap-2">
                <i class="fa-solid fa-circle-info text-info flex-shrink-0"></i>
                <div>{{ session('info') }}</div>
            </div>
        @endif

        @if(isset($errors) && $errors->any())
            <div class="alert alert-danger rounded-3 py-2.5 px-3 mb-3 small">
                <div class="fw-bold mb-1"><i class="fa-solid fa-triangle-exclamation me-1"></i> অনুগ্রহ করে নিচের ত্রুটিগুলো সংশোধন করুন:</div>
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @php
            $fSettings = $campaign->form_settings ?? [];
            $siteLogo = $fSettings['logo_url'] ?? (\App\Support\SiteSetting::logoUrl() ?: (\App\Support\SiteSetting::loginLogoUrl() ?: asset('images/logo.png')));
            $brandName = $fSettings['brand_name'] ?? 'আইডিয়া পাঠাগার';
            $subTitle = $fSettings['sub_title'] ?? 'বই অনুদান আবেদন ফরম — Library Apply';
        @endphp

        {{-- হেডার ব্যানার --}}
        <div class="lib-header-banner mb-3 rounded-4 shadow-sm">
            <div class="lib-header-inner d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="lib-title-area">
                    <div class="lib-logo-wrapper" title="{{ $brandName }}">
                        <img src="{{ $siteLogo }}" alt="{{ $brandName }}" class="lib-header-logo" onerror="this.src='{{ asset('images/logo.png') }}';">
                    </div>
                    <div>
                        <h1 class="lib-main-title">{{ $brandName }}</h1>
                        <div class="lib-sub-title">{{ $subTitle }}</div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="lib-badge-pill" style="font-size: 13px; padding: 6px 14px; background: rgba(255,255,255,0.2); border: 1.5px solid rgba(255,255,255,0.4);">
                        <i class="fa-solid fa-book-open-reader text-warning me-1"></i> Library Apply
                    </span>
                </div>
            </div>
        </div>

        {{-- কাস্টমার অথেন্টিকেশন গেইট (যদি ইউজার লগইন না করা থাকে) --}}
        @guest
            <div class="lib-auth-gate-card shadow-sm" id="authGateCard">
                <div class="lib-auth-gate-header">
                    <div>
                        <h3 class="lib-auth-gate-title">
                            <i class="fa-solid fa-user-shield text-warning"></i>
                            <span>পাঠাগার প্রতিনিধি একাউন্ট লগইন / নিবন্ধন</span>
                        </h3>
                        <div class="small text-white-50 mt-1">বই অনুদান আবেদন ফরম পূরণ করতে একাউন্টে লগইন করুন বা মোবাইল ভেরিফাই করে নতুন একাউন্ট খুলুন</div>
                    </div>
                    <span class="badge bg-warning text-dark fw-bold px-3 py-1.5 rounded-pill"><i class="fa-solid fa-key me-1"></i> ধাপ ১: প্রতিনিধি ভেরিফিকেশন</span>
                </div>
                <div class="lib-auth-gate-body">

                    {{-- ট্যাব সুইচ বাটন (Idea Modern Segmented Design - Register Active By Default) --}}
                    <div class="lib-auth-nav-tabs">
                        <button type="button" class="lib-auth-tab-btn active" id="tabBtnRegister" onclick="switchAuthTab('register')">
                            <i class="fa-solid fa-user-plus"></i>
                            <span>নতুন অ্যাকাউন্ট খুলুন</span>
                        </button>
                        <button type="button" class="lib-auth-tab-btn" id="tabBtnLogin" onclick="switchAuthTab('login')">
                            <i class="fa-solid fa-arrow-right-to-bracket"></i>
                            <span>সাইন ইন</span>
                        </button>
                    </div>

                    {{-- AJAX রেসপন্স এলার্ট বক্স --}}
                    <div id="authAlertBox" class="d-none alert rounded-3 py-2.5 px-3 mb-3 small"></div>

                    {{-- ফর্ম ১: কাস্টমার লগইন ফর্ম (ডিফল্ট হিডেন) --}}
                    <form action="{{ route('login') }}" method="POST" id="gateLoginForm" class="d-none">
                        @csrf
                        <input type="hidden" name="redirect_to" value="{{ request()->fullUrl() }}">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="lib-field-group">
                                    <label class="lib-field-label">
                                        <span><i class="fa-solid fa-mobile-screen-button text-success me-1"></i> মোবাইল নম্বর / ইমেইল / ইউজারনেম <span class="text-danger">*</span></span>
                                    </label>
                                    <input type="text" name="email" id="gateLoginEmail" class="lib-input font-monospace" placeholder="যেমন: 017XXXXXXXX বা library@gmail.com" required autocomplete="username">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="lib-field-group">
                                    <label class="lib-field-label d-flex justify-content-between align-items-center">
                                        <span><i class="fa-solid fa-lock text-success me-1"></i> পাসওয়ার্ড <span class="text-danger">*</span></span>
                                        <a href="{{ route('password.request') }}" target="_blank" class="text-success small text-decoration-none fw-semibold">পাসওয়ার্ড ভুলে গেছেন?</a>
                                    </label>
                                    <div class="lib-pwd-wrap">
                                        <input type="password" name="password" id="gateLoginPassword" class="lib-input" placeholder="আপনার একাউন্টের পাসওয়ার্ড লিখুন" required autocomplete="current-password">
                                        <button type="button" class="lib-pwd-eye" onclick="togglePasswordVisibility('gateLoginPassword', this)" title="পাসওয়ার্ড দেখুন / লুকান">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="mt-1.5 text-end">
                                    <button type="button" class="btn btn-sm btn-link text-success p-0 text-decoration-none fw-semibold" style="font-size: 12px;" onclick="switchAuthTab('register', document.getElementById('gateLoginEmail')?.value)">
                                        <i class="fa-solid fa-shield-halved me-1"></i> পাসওয়ার্ড নেই / ওটিপি দিয়ে ভেরিফাই ও লগইন
                                    </button>
                                </div>
                            </div>
                            <div class="col-12 d-flex align-items-center justify-content-between flex-wrap gap-2 pt-2 border-top">
                                <label class="form-check-label small text-muted d-flex align-items-center gap-1.5 cursor-pointer">
                                    <input type="checkbox" name="remember" class="form-check-input mt-0" checked>
                                    <span>লগইন তথ্য সংরক্ষণ করুন</span>
                                </label>
                                <button type="submit" id="gateLoginBtn" class="lib-btn-auth-action">
                                    <i class="fa-solid fa-arrow-right-to-bracket"></i>
                                    <span>লগইন করুন ও আবেদন ফরম খুলুন</span>
                                </button>
                            </div>
                        </div>
                    </form>

                    {{-- ফর্ম ২: দ্রুত কাস্টমার রেজিস্টার ফর্ম (ওটিপি সহ - ডিফল্ট একটিভ) --}}
                    <form action="{{ route('register.quick-customer') }}" method="POST" id="gateRegisterForm">
                        @csrf
                        <input type="hidden" name="redirect_to" value="{{ request()->fullUrl() }}">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="lib-field-group">
                                    <label class="lib-field-label">
                                        <span><i class="fa-solid fa-user-pen text-success me-1"></i> প্রতিনিধি / আবেদনকারীর পূর্ণ নাম <span class="text-danger">*</span></span>
                                    </label>
                                    <input type="text" name="name" id="gateRegName" class="lib-input" placeholder="যেমন: মো: রফিকুল ইসলাম" required autocomplete="name">
                                </div>
                            </div>

                            {{-- মোবাইল নম্বর এবং সেন্ড ওটিপি বাটন (দেশভিত্তিক কোড সহ) --}}
                            <div class="col-md-6">
                                <div class="lib-field-group">
                                    <label class="lib-field-label d-flex justify-content-between align-items-center">
                                        <span><i class="fa-solid fa-phone text-success me-1"></i> মোবাইল নম্বর <span class="text-danger">*</span></span>
                                        <span id="otpStatusBadge" class="badge bg-secondary-subtle text-secondary border px-2 py-0.5" style="font-size: 11px;">
                                            <i class="fa-solid fa-shield-halved me-1"></i> SMS ভেরিফিকেশন
                                        </span>
                                    </label>
                                    <div class="lib-phone-group d-flex align-items-stretch" style="border: 1.5px solid #cbd5e1; border-radius: 8px; overflow: hidden; background: #ffffff;">
                                        <select name="country_code" id="gateCountryCodeSelect" class="form-select border-0 flex-shrink-0" style="width: auto; min-width: 105px; max-width: 130px; border-radius: 0; font-weight: 600; font-size: 0.88rem; background-color: #f1f5f9; border-right: 1px solid #cbd5e1 !important; box-shadow: none;" onchange="if(typeof handleGatePhoneCheck==='function') handleGatePhoneCheck();">
                                            @include('partials.country-code-options')
                                        </select>
                                        <input type="tel" name="phone" id="gateRegPhone" class="lib-input font-monospace flex-grow-1 border-0" placeholder="017XXXXXXXX" required maxlength="15" autocomplete="tel" style="border-radius: 0; box-shadow: none;">
                                        <button type="button" class="btn btn-success fw-bold px-3 flex-shrink-0 rounded-0" id="sendOtpBtn" onclick="handleSendOtp()" style="font-size: 0.85rem; height: 42px;">
                                            <span class="spinner-border spinner-border-sm d-none" id="otpSpinner" role="status"></span>
                                            <span id="sendOtpText"><i class="fa-solid fa-paper-plane me-1"></i> কোড পাঠান</span>
                                        </button>
                                    </div>
                                    {{-- লাইভ কনফ্লিক্ট নোটিশ বক্স --}}
                                    <div id="phoneConflictAlert" class="d-none mt-2"></div>
                                </div>
                            </div>

                            {{-- ওটিপি ইনপুট প্যানেল --}}
                            <div class="col-12" id="otpInputContainer">
                                <div class="lib-otp-box-card">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <label class="form-label small fw-bold mb-0 text-dark">
                                            <i class="fa-solid fa-shield-halved text-success me-1"></i> মোবাইলে প্রাপ্ত ৬-ডিজিট ওটিপি (OTP) লিখুন:
                                        </label>
                                        <span id="otpCountdownText" class="badge bg-white text-muted border font-monospace px-2 py-1" style="font-size: 11px;">১২০ সে</span>
                                    </div>
                                    <div class="input-group mb-1">
                                        <input type="text" id="buyerOtpCode" class="form-control font-monospace text-center fw-bold fs-5 lib-otp-code-input" maxlength="6" placeholder="______" autocomplete="one-time-code">
                                        <button type="button" class="btn btn-success fw-bold px-4" id="verifyOtpBtn" onclick="handleVerifyOtp()">
                                            <span class="spinner-border spinner-border-sm d-none" id="verifySpinner"></span>
                                            <span id="verifyOtpText"><i class="fa-solid fa-circle-check me-1"></i> ভেরিফাই</span>
                                        </button>
                                    </div>
                                    <div id="otpFeedback" class="small d-none mt-1"></div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="lib-field-group">
                                    <label class="lib-field-label">
                                        <span><i class="fa-solid fa-envelope text-success me-1"></i> ইমেইল ঠিকানা <small class="text-muted">(ঐচ্ছিক)</small></span>
                                    </label>
                                    <input type="email" name="email" id="gateRegEmail" class="lib-input" placeholder="library@gmail.com" autocomplete="email">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="lib-field-group">
                                    <label class="lib-field-label">
                                        <span><i class="fa-solid fa-lock text-success me-1"></i> পাসওয়ার্ড তৈরি করুন <span class="text-danger">*</span></span>
                                    </label>
                                    <div class="lib-pwd-wrap">
                                        <input type="password" name="password" id="gateRegPassword" class="lib-input" placeholder="কমপক্ষে ৬ অক্ষরের পাসওয়ার্ড দিন" required minlength="6" autocomplete="new-password">
                                        <button type="button" class="lib-pwd-eye" onclick="togglePasswordVisibility('gateRegPassword', this)" title="পাসওয়ার্ড দেখুন / লুকান">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 d-flex align-items-center justify-content-between flex-wrap gap-2 pt-2 border-top">
                                <div class="small text-muted">
                                    <i class="fa-solid fa-shield-halved text-success me-1"></i> মোবাইল ভেরিফাই করে একাউন্ট তৈরি সম্পন্ন করুন
                                </div>
                                <button type="submit" id="gateRegisterBtn" class="lib-btn-auth-action">
                                    <i class="fa-solid fa-user-check"></i>
                                    <span>একাউন্ট তৈরি করুন ও ফরম খুলুন</span>
                                </button>
                            </div>
                        </div>
                    </form>

                </div>
            </div>

            {{-- আবেদন ফরম লক নোটিশ --}}
            <div id="lockedNoticeBar" class="lib-form-locked-msg shadow-sm">
                <i class="fa-solid fa-lock text-warning fs-5 flex-shrink-0"></i>
                <div>
                    <strong>আবেদন ফরমটি আনলক করুন:</strong> বই অনুদানের মূল আবেদন ফরম পূরণ করতে অনুগ্রহ করে প্রথমে উপরে লগইন করুন অথবা মোবাইল ভেরিফাই করে অ্যাকাউন্ট তৈরি সম্পন্ন করুন।
                </div>
            </div>
        @endguest

        {{-- যদি লগইন করা থাকে তবে প্রতিনিধির তথ্য বার দেখানো হবে --}}
        @auth
            <div class="lib-user-badge-bar" id="authUserBanner">
                <div class="lib-user-info-text">
                    <div class="d-flex align-items-center justify-content-center bg-success text-white rounded-circle flex-shrink-0" style="width: 40px; height: 40px; font-size: 18px;">
                        <i class="fa-solid fa-user-check"></i>
                    </div>
                    <div>
                        <div class="fw-bold fs-6">
                            <span id="repDisplayName">{{ auth()->user()->name }}</span>
                            <span class="badge bg-success-subtle text-success border border-success-subtle ms-1.5"><i class="fa-solid fa-circle-check me-1"></i>লগইনকৃত প্রতিনিধি একাউন্ট</span>
                        </div>
                        <div class="small text-muted font-monospace mt-0.5">
                            <i class="fa-solid fa-phone me-1 text-success"></i> <span id="repDisplayPhone">{{ auth()->user()->phone ?? '—' }}</span>
                            @if(auth()->user()->email)
                                <span class="mx-1">|</span> <i class="fa-solid fa-envelope me-1 text-success"></i> <span id="repDisplayEmail">{{ auth()->user()->email }}</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div>
                    <a href="{{ route('logout') }}" class="lib-user-switch-link" onclick="event.preventDefault(); document.getElementById('libLogoutForm').submit();">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i>
                        <span>লগআউট / অন্য একাউন্ট</span>
                    </a>
                    <form id="libLogoutForm" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </div>
            </div>
        @endauth

        {{-- লাইব্রেরি রেজিস্ট্রেশন ফরম --}}
        <div id="libraryFormWrapper" class="{{ !auth()->check() ? 'opacity-50 pe-none' : '' }}" style="transition: all 0.4s ease;">
            <form action="{{ route('event.submit', $campaign->slug) }}" method="POST" enctype="multipart/form-data" id="dynamicLibraryForm" class="library-card-box">
                @csrf
                
                {{-- অপ্টিমাইজড ফটো বেস৬৪ ডাটা --}}
                <input type="hidden" name="optimized_photo_data" id="optimizedPhotoData">

                {{-- ফরম পূরণ অগ্রগতি বার --}}
                <div class="lib-progress-wrap">
                    <div class="lib-progress-info">
                        <i class="fa-solid fa-list-check text-success"></i>
                        <span>আবেদন ফরম পূরণ অগ্রগতি:</span>
                    </div>
                    <div class="lib-progress-track">
                        <div class="lib-progress-fill" id="formProgressFill"></div>
                    </div>
                    <div class="lib-progress-percent" id="formProgressPercent">০%</div>
                </div>

                {{-- সেকশন ১: পাঠাগার ও প্রতিষ্ঠানের তথ্য --}}
                <div class="lib-section-head">
                    <div class="sec-left">
                        <span class="sec-icon-pill"><i class="fa-solid fa-building-columns"></i></span>
                        <span>১. পাঠাগার ও প্রতিষ্ঠানের তথ্য</span>
                    </div>
                </div>
                <div class="lib-form-body">
                    <div class="lib-grid-row">
                        <div class="lib-col-8">
                            <div class="lib-grid-row">
                                <div class="lib-col-12">
                                    <div class="lib-field-group">
                                        <label class="lib-field-label">
                                            <span><i class="fa-solid fa-landmark text-success me-1"></i> পাঠাগারের পূর্ণ নাম <span class="text-danger">*</span></span>
                                        </label>
                                        <input type="text" name="institution_or_org" class="lib-input" placeholder="পাঠাগারের পূর্ণ নাম লিখুন" value="{{ old('institution_or_org') }}" required autocomplete="off">
                                    </div>
                                </div>
                                <div class="lib-col-12">
                                    <div class="lib-field-group">
                                        <label class="lib-field-label">
                                            <span><i class="fa-solid fa-shapes text-success me-1"></i> পাঠাগারের ধরন <span class="text-danger">*</span></span>
                                        </label>
                                        <select name="library_type" class="lib-select" required>
                                            <option value="গণপাঠাগার / পাবলিক লাইব্রেরি" {{ old('library_type') === 'গণপাঠাগার / পাবলিক লাইব্রেরি' || old('library_type') === 'Public / Community Library' ? 'selected' : '' }}>গণপাঠাগার / পাবলিক লাইব্রেরি</option>
                                            <option value="বিদ্যালয় লাইব্রেরি" {{ old('library_type') === 'বিদ্যালয় লাইব্রেরি' || old('library_type') === 'School Library' ? 'selected' : '' }}>বিদ্যালয় লাইব্রেরি</option>
                                            <option value="কলেজ লাইব্রেরি" {{ old('library_type') === 'কলেজ লাইব্রেরি' || old('library_type') === 'College Library' ? 'selected' : '' }}>কলেজ লাইব্রেরি</option>
                                            <option value="মাদরাসা পাঠাগার" {{ old('library_type') === 'মাদরাসা পাঠাগার' || old('library_type') === 'Madrasa Library' ? 'selected' : '' }}>মাদরাসা পাঠাগার</option>
                                            <option value="বিশ্ববিদ্যালয় লাইব্রেরি" {{ old('library_type') === 'বিশ্ববিদ্যালয় লাইব্রেরি' || old('library_type') === 'University / Departmental Library' ? 'selected' : '' }}>বিশ্ববিদ্যালয় লাইব্রেরি</option>
                                            <option value="ক্লাব / সামাজিক পাঠাগার" {{ old('library_type') === 'ক্লাব / সামাজিক পাঠাগার' || old('library_type') === 'Youth Club / Organization' ? 'selected' : '' }}>ক্লাব / সামাজিক পাঠাগার</option>
                                            <option value="অন্যান্য পাঠাগার" {{ old('library_type') === 'অন্যান্য পাঠাগার' || old('library_type') === 'Other Library' ? 'selected' : '' }}>অন্যান্য পাঠাগার</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="lib-col-6">
                                    <div class="lib-field-group">
                                        <label class="lib-field-label">
                                            <span><i class="fa-solid fa-id-card text-success me-1"></i> সরকারি / গ্রন্থকেন্দ্র নিবন্ধন নম্বর</span>
                                        </label>
                                        <input type="text" name="reg_no" class="lib-input" placeholder="নিবন্ধন নম্বর লিখুন (যদি থাকে)" value="{{ old('reg_no') }}">
                                    </div>
                                </div>
                                <div class="lib-col-6">
                                    <div class="lib-field-group">
                                        <label class="lib-field-label">
                                            <span><i class="fa-solid fa-calendar-days text-success me-1"></i> প্রতিষ্ঠা সাল</span>
                                        </label>
                                        <input type="text" name="established_year" class="lib-input font-monospace" placeholder="যেমন: ২০১৮" value="{{ old('established_year') }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- সাইনবোর্ড / ভবনের ছবি --}}
                        <div class="lib-col-4">
                            <div class="lib-photo-card">
                                <label class="lib-field-label mb-2 text-center">
                                    <span><i class="fa-solid fa-camera text-success me-1"></i> পাঠাগারের ছবি / সাইনবোর্ড</span>
                                </label>
                                <div class="lib-photo-box" id="libPhotoBox" title="ছবি নির্বাচন বা ড্রপ করুন">
                                    <button type="button" class="lib-photo-remove" id="libPhotoRemove" title="ছবি বাদ দিন">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                    <img id="libPreviewImg" class="lib-photo-preview" alt="পাঠাগারের ছবি">
                                    <div id="libUploadPrompt" class="lib-photo-placeholder">
                                        <div class="icon"><i class="fa-solid fa-camera"></i></div>
                                        <div class="prompt-title">ছবি আপলোড করুন</div>
                                    </div>
                                </div>
                                <div id="libOptBadge" class="lib-photo-badge">✓ ছবি প্রস্তুত হয়েছে</div>
                                <input type="file" id="libPhotoInput" name="student_photo" accept="image/*" class="d-none">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- সেকশন ২: দায়িত্বপ্রাপ্ত প্রতিনিধি ও যোগাযোগের তথ্য --}}
                <div class="lib-section-head">
                    <div class="sec-left">
                        <span class="sec-icon-pill"><i class="fa-solid fa-user-tie"></i></span>
                        <span>২. প্রতিনিধি ও যোগাযোগের তথ্য</span>
                    </div>
                </div>
                <div class="lib-form-body">
                    <div class="lib-grid-row">
                        <div class="lib-col-6">
                            <div class="lib-field-group">
                                <label class="lib-field-label">
                                    <span><i class="fa-solid fa-user-pen text-success me-1"></i> প্রতিনিধির নাম <span class="text-danger">*</span></span>
                                </label>
                                <input type="text" name="name" class="lib-input" placeholder="আবেদনকারী প্রতিনিধির পূর্ণ নাম" value="{{ old('name', auth()->user()?->name) }}" required>
                            </div>
                        </div>
                        <div class="lib-col-6">
                            <div class="lib-field-group">
                                <label class="lib-field-label">
                                    <span><i class="fa-solid fa-briefcase text-success me-1"></i> পদবি <span class="text-danger">*</span></span>
                                </label>
                                <select name="designation_or_class" class="lib-select" required>
                                    <option value="সাধারণ সম্পাদক" {{ old('designation_or_class') === 'সাধারণ সম্পাদক' || old('designation_or_class') === 'General Secretary' ? 'selected' : '' }}>সাধারণ সম্পাদক</option>
                                    <option value="সভাপতি" {{ old('designation_or_class') === 'সভাপতি' || old('designation_or_class') === 'President' ? 'selected' : '' }}>সভাপতি</option>
                                    <option value="প্রতিষ্ঠাতা / পরিচালক" {{ old('designation_or_class') === 'প্রতিষ্ঠাতা / পরিচালক' || old('designation_or_class') === 'Director / Founder' ? 'selected' : '' }}>প্রতিষ্ঠাতা / পরিচালক</option>
                                    <option value="প্রধান শিক্ষক / অধ্যক্ষ" {{ old('designation_or_class') === 'প্রধান শিক্ষক / অধ্যক্ষ' || old('designation_or_class') === 'Principal / Headmaster' ? 'selected' : '' }}>প্রধান শিক্ষক / অধ্যক্ষ</option>
                                    <option value="গ্রন্থাগারিক" {{ old('designation_or_class') === 'গ্রন্থাগারিক' || old('designation_or_class') === 'Librarian' ? 'selected' : '' }}>গ্রন্থাগারিক</option>
                                    <option value="আহ্বায়ক / সদস্য" {{ old('designation_or_class') === 'আহ্বায়ক / সদস্য' || old('designation_or_class') === 'Member / Convener' ? 'selected' : '' }}>আহ্বায়ক / সদস্য</option>
                                </select>
                            </div>
                        </div>
                        <div class="lib-col-6">
                            <div class="lib-field-group">
                                <label class="lib-field-label">
                                    <span><i class="fa-solid fa-phone text-success me-1"></i> মোবাইল নম্বর <span class="text-danger">*</span></span>
                                </label>
                                <div class="lib-phone-group d-flex align-items-stretch" style="border: 1.5px solid #cbd5e1; border-radius: 8px; overflow: hidden; background: #ffffff;">
                                    <select name="country_code" id="libCountryCodeSelect" class="form-select border-0 flex-shrink-0" style="width: auto; min-width: 105px; max-width: 130px; border-radius: 0; font-weight: 600; font-size: 0.88rem; background-color: #f1f5f9; border-right: 1px solid #cbd5e1 !important; box-shadow: none;">
                                        @include('partials.country-code-options')
                                    </select>
                                    <input type="tel" name="phone" id="libPhone" class="lib-input font-monospace flex-grow-1 border-0" placeholder="017XXXXXXXX" value="{{ old('phone', auth()->user()?->phone) }}" required maxlength="15" style="border-radius: 0; box-shadow: none;">
                                </div>
                            </div>
                        </div>
                        <div class="lib-col-6">
                            <div class="lib-field-group">
                                <label class="lib-field-label">
                                    <span><i class="fa-solid fa-mobile-screen-button text-success me-1"></i> বিকল্প মোবাইল নম্বর</span>
                                </label>
                                <div class="lib-phone-group d-flex align-items-stretch" style="border: 1.5px solid #cbd5e1; border-radius: 8px; overflow: hidden; background: #ffffff;">
                                    <select name="guardian_country_code" class="form-select border-0 flex-shrink-0" style="width: auto; min-width: 105px; max-width: 130px; border-radius: 0; font-weight: 600; font-size: 0.88rem; background-color: #f1f5f9; border-right: 1px solid #cbd5e1 !important; box-shadow: none;">
                                        @include('partials.country-code-options')
                                    </select>
                                    <input type="tel" name="guardian_phone" class="lib-input font-monospace flex-grow-1 border-0" placeholder="018XXXXXXXX" value="{{ old('guardian_phone') }}" maxlength="15" style="border-radius: 0; box-shadow: none;">
                                </div>
                            </div>
                        </div>
                        <div class="lib-col-6">
                            <div class="lib-field-group">
                                <label class="lib-field-label">
                                    <span><i class="fa-solid fa-envelope text-success me-1"></i> ইমেইল ঠিকানা</span>
                                </label>
                                <input type="email" name="email" class="lib-input" placeholder="library@example.com" value="{{ old('email', auth()->user()?->email) }}">
                            </div>
                        </div>
                        <div class="lib-col-6">
                            <div class="lib-field-group">
                                <label class="lib-field-label">
                                    <span><i class="fa-solid fa-address-card text-success me-1"></i> জাতীয় পরিচয়পত্র (NID) নম্বর</span>
                                </label>
                                <input type="text" name="nid" class="lib-input font-monospace" placeholder="জাতীয় পরিচয়পত্র নম্বর" value="{{ old('nid') }}">
                            </div>
                        </div>

                        {{-- পরিচালনা কমিটির তথ্য --}}
                        <div class="lib-col-12 mt-2 pt-2 border-top">
                            <div class="d-flex align-items-center gap-1.5 text-success fw-bold small mb-2">
                                <i class="fa-solid fa-users-gear"></i>
                                <span>পরিচালনা কমিটি <span class="text-danger">*</span>:</span>
                            </div>
                        </div>
                        <div class="lib-col-6">
                            <div class="lib-field-group">
                                <label class="lib-field-label">
                                    <span><i class="fa-solid fa-user-check text-success me-1"></i> সভাপতির নাম <span class="text-danger">*</span></span>
                                </label>
                                <input type="text" name="president_name" class="lib-input" placeholder="সভাপতির নাম লিখুন" value="{{ old('president_name') }}" required>
                            </div>
                        </div>
                        <div class="lib-col-6">
                            <div class="lib-field-group">
                                <label class="lib-field-label">
                                    <span><i class="fa-solid fa-phone text-success me-1"></i> সভাপতির মোবাইল নম্বর <span class="text-danger">*</span></span>
                                </label>
                                <div class="lib-phone-group d-flex align-items-stretch" style="border: 1.5px solid #cbd5e1; border-radius: 8px; overflow: hidden; background: #ffffff;">
                                    <select name="president_country_code" class="form-select border-0 flex-shrink-0" style="width: auto; min-width: 105px; max-width: 130px; border-radius: 0; font-weight: 600; font-size: 0.88rem; background-color: #f1f5f9; border-right: 1px solid #cbd5e1 !important; box-shadow: none;">
                                        @include('partials.country-code-options')
                                    </select>
                                    <input type="tel" name="president_phone" class="lib-input font-monospace flex-grow-1 border-0" placeholder="01XXXXXXXXX" value="{{ old('president_phone') }}" required maxlength="15" style="border-radius: 0; box-shadow: none;">
                                </div>
                            </div>
                        </div>
                        <div class="lib-col-6">
                            <div class="lib-field-group">
                                <label class="lib-field-label">
                                    <span><i class="fa-solid fa-user-pen text-success me-1"></i> সাধারণ সম্পাদকের নাম <span class="text-danger">*</span></span>
                                </label>
                                <input type="text" name="secretary_name" class="lib-input" placeholder="সাধারণ সম্পাদকের নাম লিখুন" value="{{ old('secretary_name') }}" required>
                            </div>
                        </div>
                        <div class="lib-col-6">
                            <div class="lib-field-group">
                                <label class="lib-field-label">
                                    <span><i class="fa-solid fa-phone text-success me-1"></i> সাধারণ সম্পাদকের মোবাইল নম্বর <span class="text-danger">*</span></span>
                                </label>
                                <div class="lib-phone-group d-flex align-items-stretch" style="border: 1.5px solid #cbd5e1; border-radius: 8px; overflow: hidden; background: #ffffff;">
                                    <select name="secretary_country_code" class="form-select border-0 flex-shrink-0" style="width: auto; min-width: 105px; max-width: 130px; border-radius: 0; font-weight: 600; font-size: 0.88rem; background-color: #f1f5f9; border-right: 1px solid #cbd5e1 !important; box-shadow: none;">
                                        @include('partials.country-code-options')
                                    </select>
                                    <input type="tel" name="secretary_phone" class="lib-input font-monospace flex-grow-1 border-0" placeholder="01XXXXXXXXX" value="{{ old('secretary_phone') }}" required maxlength="15" style="border-radius: 0; box-shadow: none;">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- সেকশন ৩: অবস্থান ও পূর্ণ ডাক ঠিকানা --}}
                <div class="lib-section-head">
                    <div class="sec-left">
                        <span class="sec-icon-pill"><i class="fa-solid fa-map-location-dot"></i></span>
                        <span>৩. পাঠাগারের অবস্থান ও পূর্ণ ডাক ঠিকানা</span>
                    </div>
                </div>
                <div class="lib-form-body">
                    <div class="lib-grid-row">
                        <div class="lib-col-6">
                            <div class="lib-field-group">
                                <label class="lib-field-label">
                                    <span><i class="fa-solid fa-earth-asia text-success me-1"></i> বিভাগ <span class="text-danger">*</span></span>
                                </label>
                                <select name="division" id="libDivision" class="lib-select" data-old="{{ old('division') }}" required>
                                    <option value="">-- বিভাগ নির্বাচন করুন --</option>
                                </select>
                            </div>
                        </div>
                        <div class="lib-col-6">
                            <div class="lib-field-group">
                                <label class="lib-field-label">
                                    <span><i class="fa-solid fa-city text-success me-1"></i> জেলা <span class="text-danger">*</span></span>
                                </label>
                                <select name="district" id="libDistrict" class="lib-select" data-old="{{ old('district') }}" required>
                                    <option value="">-- প্রথমে বিভাগ নির্বাচন করুন --</option>
                                </select>
                            </div>
                        </div>
                        <div class="lib-col-6">
                            <div class="lib-field-group">
                                <label class="lib-field-label">
                                    <span><i class="fa-solid fa-location-crosshairs text-success me-1"></i> উপজেলা / থানা <span class="text-danger">*</span></span>
                                </label>
                                <select name="thana" id="libThana" class="lib-select" data-old="{{ old('thana') }}" required>
                                    <option value="">-- প্রথমে জেলা নির্বাচন করুন --</option>
                                </select>
                            </div>
                        </div>
                        <div class="lib-col-6">
                            <div class="lib-field-group">
                                <label class="lib-field-label">
                                    <span><i class="fa-solid fa-house-chimney text-success me-1"></i> ইউনিয়ন / গ্রাম / এলাকা <span class="text-danger">*</span></span>
                                </label>
                                <input type="text" name="perm_village" class="lib-input" placeholder="গ্রাম / ওয়ার্ড / রোড নম্বর" value="{{ old('perm_village') }}" required>
                            </div>
                        </div>
                        <div class="lib-col-6">
                            <div class="lib-field-group">
                                <label class="lib-field-label">
                                    <span><i class="fa-solid fa-envelopes-bulk text-success me-1"></i> ডাকঘর ও পোস্ট কোড <span class="text-danger">*</span></span>
                                </label>
                                <input type="text" name="perm_post_office" class="lib-input" placeholder="যেমন: ডাকঘর - ৫৪০০" value="{{ old('perm_post_office') }}" required>
                            </div>
                        </div>
                        <div class="lib-col-6">
                            <div class="lib-field-group">
                                <label class="lib-field-label">
                                    <span><i class="fa-solid fa-map-pin text-success me-1"></i> পূর্ণ পার্সেল ডাক ঠিকানা <span class="text-danger">*</span></span>
                                </label>
                                <input type="text" name="library_address" class="lib-input" placeholder="বই পার্সেল গ্রহণের পূর্ণ ডাক ঠিকানা" value="{{ old('library_address') }}" required>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- সেকশন ৪: বই ও অনুদান সংক্রান্ত তথ্য --}}
                <div class="lib-section-head">
                    <div class="sec-left">
                        <span class="sec-icon-pill"><i class="fa-solid fa-book-open-reader"></i></span>
                        <span>৪. বই ও অনুদান সংক্রান্ত তথ্য</span>
                    </div>
                </div>
                <div class="lib-form-body">
                    <div class="lib-grid-row">
                        <div class="lib-col-6">
                            <div class="lib-field-group">
                                <label class="lib-field-label">
                                    <span><i class="fa-solid fa-book-bookmark text-success me-1"></i> পাঠাগারের বর্তমান বই সংখ্যা</span>
                                </label>
                                <input type="text" name="current_book_count" class="lib-input font-monospace" placeholder="যেমন: ৫০০" value="{{ old('current_book_count') }}">
                            </div>
                        </div>
                        <div class="lib-col-6">
                            <div class="lib-field-group">
                                <label class="lib-field-label">
                                    <span><i class="fa-solid fa-users text-success me-1"></i> নিয়মিত পাঠক / সদস্য সংখ্যা</span>
                                </label>
                                <input type="text" name="reader_count" class="lib-input font-monospace" placeholder="যেমন: ১২০" value="{{ old('reader_count') }}">
                            </div>
                        </div>
                        <div class="lib-col-12">
                            <div class="lib-field-group">
                                <label class="lib-field-label">
                                    <span><i class="fa-solid fa-truck-fast text-success me-1"></i> বই গ্রহণের মাধ্যম / কুরিয়ার</span>
                                </label>
                                <select name="delivery_method" class="lib-select">
                                    <option value="সুন্দরবন কুরিয়ার সার্ভিস" {{ old('delivery_method') === 'সুন্দরবন কুরিয়ার সার্ভিস' ? 'selected' : '' }}>সুন্দরবন কুরিয়ার সার্ভিস</option>
                                    <option value="এসএ পরিবহন" {{ old('delivery_method') === 'এসএ পরিবহন' ? 'selected' : '' }}>এসএ পরিবহন</option>
                                    <option value="করতোয়া কুরিয়ার" {{ old('delivery_method') === 'করতোয়া কুরিয়ার' ? 'selected' : '' }}>করতোয়া কুরিয়ার</option>
                                    <option value="রেডএক্স / স্টিডফাস্ট হোম ডেলিভারি" {{ old('delivery_method') === 'রেডএক্স / স্টিডফাস্ট হোম ডেলিভারি' ? 'selected' : '' }}>রেডএক্স / স্টিডফাস্ট হোম ডেলিভারি</option>
                                    <option value="সরাসরি অফিস থেকে সংগ্রহ" {{ old('delivery_method') === 'সরাসরি অফিস থেকে সংগ্রহ' ? 'selected' : '' }}>সরাসরি অফিস থেকে সংগ্রহ</option>
                                </select>
                            </div>
                        </div>
                        <div class="lib-col-12">
                            <div class="lib-field-group">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <label class="lib-field-label mb-0">
                                        <span><i class="fa-solid fa-tags text-success me-1"></i> প্রত্যাশিত বইয়ের বিষয়সমূহ</span>
                                    </label>
                                    <span id="selectedGenreCount" class="badge bg-success-subtle text-success border border-success-subtle"></span>
                                </div>
                                <div class="genre-chips-container">
                                    @php
                                        $genreOptions = [
                                            'Literature & Novels'      => 'সাহিত্য ও উপন্যাস',
                                            'Liberation War & History'  => 'মুক্তিযুদ্ধ ও ইতিহাস',
                                            'Poetry & Rhymes'           => 'কবিতা ও ছড়া',
                                            'Children & Teenagers'      => 'শিশু-কিশোর সাহিত্য',
                                            'Science & Technology'      => 'বিজ্ঞান ও প্রযুক্তি',
                                            'Career & Self-Development' => 'ক্যারিয়ার ও আত্মউন্নয়ন',
                                            'Islamic & Religious'       => 'ইসলামিক ও নৈতিক শিক্ষা',
                                            'General Knowledge'         => 'সাধারণ জ্ঞান ও রেফারেন্স',
                                            'Biography & Memoirs'       => 'জীবনী ও স্মৃতিকথা'
                                        ];
                                        $oldGenres = old('preferred_genres', []);
                                    @endphp
                                    @foreach($genreOptions as $key => $bnLabel)
                                        <label class="genre-chip-item {{ in_array($key, $oldGenres) || in_array($bnLabel, $oldGenres) ? 'active' : '' }}">
                                            <input type="checkbox" name="preferred_genres[]" value="{{ $bnLabel }}" {{ in_array($key, $oldGenres) || in_array($bnLabel, $oldGenres) ? 'checked' : '' }}>
                                            <span>{{ $bnLabel }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- সেকশন ৫: অনুদানের প্রয়োজনীয়তা ও উদ্দেশ্য --}}
                <div class="lib-section-head">
                    <div class="sec-left">
                        <span class="sec-icon-pill"><i class="fa-solid fa-feather-pointed"></i></span>
                        <span>৫. অনুদানের প্রয়োজনীয়তা ও উদ্দেশ্য</span>
                    </div>
                    <span id="wordCounter" class="lib-word-badge badge-safe">০ / ৮০ শব্দ</span>
                </div>
                <div class="lib-form-body">
                    <div class="lib-field-group">
                        <textarea name="scholarship_reason" id="reasonText" rows="3" class="lib-textarea" placeholder="আপনার পাঠাগারের বর্তমান কার্যক্রম এবং বিনামূল্যে বই অনুদানের প্রয়োজনীয়তা সংক্ষেপে লিখুন (সর্বোচ্চ ৮০ শব্দ)...">{{ old('scholarship_reason') }}</textarea>
                        <div id="wordLimitAlert" class="text-danger small mt-1 d-none font-monospace fw-bold">
                            <i class="fa-solid fa-triangle-exclamation me-1"></i> সতর্কবার্তা: ৮০ শব্দের সীমা অতিক্রম করেছে। অনুগ্রহ করে বিবরণটি সংক্ষিপ্ত করুন।
                        </div>
                    </div>
                </div>

                {{-- নীতিমালার স্পষ্ট বার্তা --}}
                <div class="alert alert-warning border-0 rounded-4 py-2.5 px-3.5 mb-3 d-flex align-items-center gap-2.5 small text-dark shadow-xs" style="background-color: #fef3c7;">
                    <i class="fa-solid fa-circle-exclamation text-warning-emphasis fs-5 flex-shrink-0"></i>
                    <div class="fw-semibold">
                        আইডিয়া পাঠাগার নিজ উদ্যোগে বই বিতরণ করে। বই প্রদানের ক্ষেত্রে যে কোনো সিদ্ধান্ত গ্রহণের ক্ষমতা সংরক্ষণ করে।
                    </div>
                </div>

                {{-- সেকশন ৬: অঙ্গীকারনামা ও সাবমিশন --}}
                <div class="lib-submission-footer">
                    <div>
                        <label for="agreeCheck" class="lib-declaration-check">
                            <input type="checkbox" id="agreeCheck" name="declaration_agreed" checked required>
                            <span>আমি ঘোষণা করছি যে, উপরে প্রদত্ত সমস্ত তথ্য সম্পূর্ণ সত্য ও সঠিক। <span class="text-danger">*</span></span>
                        </label>
                    </div>

                    <button type="submit" id="submitBtn" class="lib-btn-submit">
                        <i class="fa-solid fa-paper-plane"></i>
                        <span>পাঠাগার আবেদন জমা দিন (Submit Library Apply)</span>
                    </button>
                </div>

            </form>
        </div>

    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/bd-geo-data.js') }}"></script>
    <script src="{{ asset('js/library-grant.js') }}"></script>
    <script>
        // Bengali to English digit converter helper
        function normalizeBnToEn(str) {
            if (!str) return '';
            const bn = ['০','১','২','৩','৪','৫','৬','৭','৮','৯'];
            const en = ['0','1','2','3','4','5','6','7','8','9'];
            let result = str.toString();
            for (let i = 0; i < bn.length; i++) {
                result = result.replaceAll(bn[i], en[i]);
            }
            return result;
        }

        function switchAuthTab(tab, prefillPhone = '') {
            const loginBtn = document.getElementById('tabBtnLogin');
            const regBtn = document.getElementById('tabBtnRegister');
            const loginForm = document.getElementById('gateLoginForm');
            const regForm = document.getElementById('gateRegisterForm');
            const alertBox = document.getElementById('authAlertBox');
            const conflictBox = document.getElementById('phoneConflictAlert');

            if (alertBox) {
                alertBox.className = 'd-none';
                alertBox.innerHTML = '';
            }
            if (conflictBox) {
                conflictBox.className = 'd-none';
                conflictBox.innerHTML = '';
            }

            if (tab === 'login') {
                if (loginBtn) loginBtn.classList.add('active');
                if (regBtn) regBtn.classList.remove('active');
                if (loginForm) loginForm.classList.remove('d-none');
                if (regForm) regForm.classList.add('d-none');

                if (prefillPhone) {
                    const loginEmailInput = document.getElementById('gateLoginEmail');
                    if (loginEmailInput) {
                        loginEmailInput.value = prefillPhone;
                        const pwdInput = document.getElementById('gateLoginPassword');
                        if (pwdInput) setTimeout(() => pwdInput.focus(), 150);
                    }
                }
            } else {
                if (regBtn) regBtn.classList.add('active');
                if (loginBtn) loginBtn.classList.remove('active');
                if (regForm) regForm.classList.remove('d-none');
                if (loginForm) loginForm.classList.add('d-none');
            }
        }

        function switchToLoginWithPhone(phone) {
            switchAuthTab('login', phone);
        }

        // Live Phone Conflict Detection Debounce & Country Code
        let phoneCheckTimer = null;
        let isPhoneVerified = false;
        const regPhoneInput = document.getElementById('gateRegPhone');

        function handleGatePhoneCheck() {
            if (!regPhoneInput) return;
            const countryCodeSelect = document.getElementById('gateCountryCodeSelect');
            const countryCode = countryCodeSelect ? countryCodeSelect.value : '+880';
            const enVal = normalizeBnToEn(regPhoneInput.value).replace(/[^\d]/g, '');
            regPhoneInput.value = enVal;

            const conflictBox = document.getElementById('phoneConflictAlert');
            const statusBadge = document.getElementById('otpStatusBadge');

            if (phoneCheckTimer) clearTimeout(phoneCheckTimer);

            const minLen = (countryCode === '+880') ? 10 : 6;
            if (enVal.length >= minLen) {
                phoneCheckTimer = setTimeout(() => {
                    fetch('{{ route("register.check-phone") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            phone: enVal,
                            country_code: countryCode
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.exists) {
                            if (!data.has_password || !data.is_verified) {
                                if (statusBadge) {
                                    statusBadge.className = 'badge bg-info-subtle text-primary border border-primary px-2 py-0.5';
                                    statusBadge.innerHTML = '<i class="fa-solid fa-shield-halved me-1"></i> ভেরিফিকেশন ও পাসওয়ার্ড প্রয়োজন';
                                }
                                if (conflictBox) {
                                    conflictBox.className = 'alert alert-info py-2.5 px-3 rounded-3 small d-flex align-items-center justify-content-between flex-wrap gap-2 mt-2';
                                    conflictBox.innerHTML = `
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="fa-solid fa-circle-info text-primary fs-6"></i>
                                            <div><strong>${countryCode} ${enVal}</strong> নম্বরটি সংরক্ষিত আছে কিন্তু পাসওয়ার্ড সেট/ভেরিফিকেশন সম্পন্ন হয়নি। ডানের <strong>"কোড পাঠান"</strong> বাটনে চাপুন।</div>
                                        </div>
                                    `;
                                }
                            } else {
                                if (statusBadge) {
                                    statusBadge.className = 'badge bg-warning text-dark border border-warning px-2 py-0.5';
                                    statusBadge.innerHTML = '<i class="fa-solid fa-user-check me-1"></i> অ্যাকাউন্ট বিদ্যমান (OTP-তে লগইন সম্ভব)';
                                }
                                if (conflictBox) {
                                    conflictBox.className = 'alert alert-warning py-2.5 px-3 rounded-3 small d-flex align-items-center justify-content-between flex-wrap gap-2 mt-2';
                                    conflictBox.innerHTML = `
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="fa-solid fa-circle-info text-warning fs-6"></i>
                                            <div><strong>${countryCode} ${enVal}</strong> নম্বরটি নিবন্ধিত। কোড পাঠান চাপলে মোবাইলে ওটিপি যাবে অথবা পাসওয়ার্ড দিন।</div>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-dark fw-bold py-1 px-2.5 rounded-2" onclick="switchToLoginWithPhone('${enVal}')">
                                            <i class="fa-solid fa-key me-1"></i> পাসওয়ার্ড দিয়ে লগইন
                                        </button>
                                    `;
                                }
                            }
                        } else {
                            if (conflictBox) {
                                conflictBox.className = 'd-none';
                                conflictBox.innerHTML = '';
                            }
                            if (statusBadge) {
                                statusBadge.className = 'badge bg-secondary-subtle text-secondary border px-2 py-0.5';
                                statusBadge.innerHTML = '<i class="fa-solid fa-shield-halved me-1"></i> SMS ভেরিফিকেশন প্রস্তুত';
                            }
                        }
                    })
                    .catch(() => {});
                }, 350);
            } else {
                if (conflictBox) {
                    conflictBox.className = 'd-none';
                    conflictBox.innerHTML = '';
                }
            }
        }

        if (regPhoneInput) {
            regPhoneInput.addEventListener('input', handleGatePhoneCheck);
        }

        // Live OTP Code Bengali Number Normalization
        const buyerOtpInput = document.getElementById('buyerOtpCode');
        if (buyerOtpInput) {
            buyerOtpInput.addEventListener('input', function () {
                this.value = normalizeBnToEn(this.value).replace(/[^\d]/g, '');
                if (this.value.length === 6) {
                    handleVerifyOtp();
                }
            });
        }

        // OTP Cooldown & Send/Verify Functions
        let otpCooldownTimer = null;

        function handleSendOtp() {
            const phoneInput = document.getElementById('gateRegPhone');
            const countryCodeSelect = document.getElementById('gateCountryCodeSelect');
            const countryCode = countryCodeSelect ? countryCodeSelect.value : '+880';
            const sendBtn = document.getElementById('sendOtpBtn');
            const spinner = document.getElementById('otpSpinner');
            const sendText = document.getElementById('sendOtpText');
            const otpFeedback = document.getElementById('otpFeedback');
            const statusBadge = document.getElementById('otpStatusBadge');
            const conflictBox = document.getElementById('phoneConflictAlert');

            if (!phoneInput) return;
            let phone = normalizeBnToEn(phoneInput.value.trim()).replace(/[^\d]/g, '');
            if (countryCode === '+880' && phone.length === 10 && phone.startsWith('1')) {
                phone = '0' + phone;
                phoneInput.value = phone;
            }

            const minLen = (countryCode === '+880') ? 10 : 6;
            if (!phone || phone.length < minLen) {
                if (conflictBox) {
                    conflictBox.className = 'alert alert-danger py-2 px-3 rounded-3 small d-flex align-items-center gap-2 mt-2';
                    conflictBox.innerHTML = '<i class="fa-solid fa-circle-exclamation text-danger"></i> <div>অনুগ্রহ করে সঠিক মোবাইল নম্বর লিখুন।</div>';
                }
                phoneInput.focus();
                return;
            }

            sendBtn.disabled = true;
            spinner.classList.remove('d-none');
            sendText.innerHTML = 'পাঠানো হচ্ছে...';

            fetch('{{ route("register.send-otp") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    phone: phone,
                    country_code: countryCode,
                    allow_existing: true,
                    purpose: 'library'
                })
            })
            .then(res => res.json().then(data => ({ status: res.status, body: data })))
            .then(result => {
                spinner.classList.add('d-none');

                if (result.status === 200 && result.body.success) {
                    if (conflictBox) {
                        conflictBox.className = 'd-none';
                        conflictBox.innerHTML = '';
                    }

                    if (otpFeedback) {
                        otpFeedback.classList.remove('d-none', 'text-danger');
                        otpFeedback.classList.add('text-success');
                        let successMsg = '<i class="fa-solid fa-circle-check me-1"></i> ' + result.body.message;
                        if (result.body.dev_otp) {
                            successMsg += ` <span class="badge bg-dark text-warning ms-1 font-monospace">[টেস্ট কোড: ${result.body.dev_otp}]</span>`;
                        }
                        otpFeedback.innerHTML = successMsg;
                    }

                    if (statusBadge) {
                        statusBadge.className = 'badge bg-warning text-dark border border-warning px-2 py-0.5';
                        statusBadge.innerHTML = '<i class="fa-solid fa-shield-halved me-1"></i> কোড পাঠানো হয়েছে';
                    }

                    startOtpCooldown(result.body.cooldown || 120);
                    const otpCodeInput = document.getElementById('buyerOtpCode');
                    if (otpCodeInput) {
                        if (result.body.dev_otp) {
                            otpCodeInput.value = result.body.dev_otp;
                            setTimeout(() => handleVerifyOtp(), 300);
                        } else {
                            otpCodeInput.focus();
                        }
                    }
                } else {
                    sendBtn.disabled = false;
                    sendText.innerHTML = '<i class="fa-solid fa-paper-plane me-1"></i> কোড পাঠান';
                    const errMsg = result.body.message || 'ভেরিফিকেশন কোড পাঠাতে সমস্যা হয়েছে।';
                    
                    if (conflictBox) {
                        conflictBox.className = 'alert alert-danger py-2 px-3 rounded-3 small d-flex align-items-center gap-2 mt-2';
                        conflictBox.innerHTML = '<i class="fa-solid fa-circle-exclamation text-danger"></i> <div>' + errMsg + '</div>';
                    }
                }
            })
            .catch(() => {
                spinner.classList.add('d-none');
                sendBtn.disabled = false;
                sendText.innerHTML = '<i class="fa-solid fa-paper-plane me-1"></i> কোড পাঠান';
                if (conflictBox) {
                    conflictBox.className = 'alert alert-danger py-2 px-3 rounded-3 small d-flex align-items-center gap-2 mt-2';
                    conflictBox.innerHTML = '<i class="fa-solid fa-circle-exclamation text-danger"></i> <div>সার্ভারে সংযোগ স্থাপন করা সম্ভব হয়নি। পুনরায় চেষ্টা করুন।</div>';
                }
            });
        }

        function startOtpCooldown(seconds) {
            const sendBtn = document.getElementById('sendOtpBtn');
            const sendText = document.getElementById('sendOtpText');
            const countdownBadge = document.getElementById('otpCountdownText');

            let remaining = seconds;
            if (sendBtn) sendBtn.disabled = true;
            if (otpCooldownTimer) clearInterval(otpCooldownTimer);

            otpCooldownTimer = setInterval(() => {
                remaining--;
                if (countdownBadge) countdownBadge.textContent = remaining + ' সে';
                if (sendText) sendText.innerHTML = `পুনরায় (${remaining}সে)`;

                if (remaining <= 0) {
                    clearInterval(otpCooldownTimer);
                    if (sendBtn) sendBtn.disabled = false;
                    if (sendText) sendText.innerHTML = '<i class="fa-solid fa-rotate-right me-1"></i> পুনরায় পাঠান';
                    if (countdownBadge) countdownBadge.textContent = '১২০ সে';
                }
            }, 1000);
        }

        function handleVerifyOtp() {
            const phoneInput = document.getElementById('gateRegPhone');
            const countryCodeSelect = document.getElementById('gateCountryCodeSelect');
            const countryCode = countryCodeSelect ? countryCodeSelect.value : '+880';
            const otpInput = document.getElementById('buyerOtpCode');
            const verifyBtn = document.getElementById('verifyOtpBtn');
            const spinner = document.getElementById('verifySpinner');
            const verifyText = document.getElementById('verifyOtpText');
            const otpFeedback = document.getElementById('otpFeedback');
            const statusBadge = document.getElementById('otpStatusBadge');

            if (!phoneInput || !otpInput) return;

            let phone = normalizeBnToEn(phoneInput.value.trim()).replace(/[^\d]/g, '');
            if (countryCode === '+880' && phone.length === 10 && phone.startsWith('1')) phone = '0' + phone;
            const otp = normalizeBnToEn(otpInput.value.trim()).replace(/[^\d]/g, '');

            if (!otp || otp.length !== 6) {
                if (otpFeedback) {
                    otpFeedback.classList.remove('d-none', 'text-success');
                    otpFeedback.classList.add('text-danger');
                    otpFeedback.innerHTML = '<i class="fa-solid fa-triangle-exclamation me-1"></i> সম্পূর্ণ ৬-ডিজিট ওটিপি কোডটি লিখুন।';
                }
                otpInput.focus();
                return;
            }

            if (verifyBtn) verifyBtn.disabled = true;
            if (spinner) spinner.classList.remove('d-none');
            if (verifyText) verifyText.innerHTML = 'যাচাই হচ্ছে...';

            fetch('{{ route("register.verify-otp") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    phone: phone,
                    country_code: countryCode,
                    otp: otp,
                    auto_login: true
                })
            })
            .then(res => res.json().then(data => ({ status: res.status, body: data })))
            .then(result => {
                if (spinner) spinner.classList.add('d-none');
                if (verifyBtn) {
                    verifyBtn.disabled = false;
                    verifyText.innerHTML = '<i class="fa-solid fa-circle-check me-1"></i> ভেরিফাই';
                }

                if (result.status === 200 && result.body.success) {
                    isPhoneVerified = true;

                    if (otpFeedback) {
                        otpFeedback.classList.remove('d-none', 'text-danger');
                        otpFeedback.classList.add('text-success');
                        otpFeedback.innerHTML = '<i class="fa-solid fa-circle-check me-1"></i> ' + result.body.message;
                    }

                    if (statusBadge) {
                        statusBadge.className = 'badge bg-success text-white border border-success px-2 py-0.5';
                        statusBadge.innerHTML = '<i class="fa-solid fa-circle-check me-1"></i> ভেরিফাইড';
                    }

                    phoneInput.readOnly = true;
                    if (countryCodeSelect) countryCodeSelect.disabled = true;
                    const sendBtn = document.getElementById('sendOtpBtn');
                    if (sendBtn) sendBtn.disabled = true;
                    otpInput.readOnly = true;

                    if (verifyBtn) {
                        verifyBtn.className = 'btn btn-success fw-bold px-4';
                        verifyBtn.disabled = true;
                        verifyBtn.innerHTML = '<i class="fa-solid fa-check me-1"></i> ভেরিফাইড';
                    }

                    // If existing user was auto-logged in or user info returned
                    if (result.body.is_logged_in && result.body.user) {
                        showAuthAlert('স্বাগতম! ওটিপি যাচাই সফল এবং লগইন সম্পন্ন হয়েছে! আবেদন ফরম প্রস্তুত হচ্ছে...', true);
                        unlockFormOnClient(result.body.user);
                        setTimeout(() => {
                            window.location.reload();
                        }, 600);
                        return;
                    }

                    const regPwdInput = document.getElementById('gateRegPassword');
                    if (regPwdInput) regPwdInput.focus();
                } else {
                    if (otpFeedback) {
                        otpFeedback.classList.remove('d-none', 'text-success');
                        otpFeedback.classList.add('text-danger');
                        otpFeedback.innerHTML = '<i class="fa-solid fa-circle-xmark me-1"></i> ' + (result.body.message || 'ভুল ওটিপি কোড।');
                    }
                }
            })
            .catch(() => {
                if (spinner) spinner.classList.add('d-none');
                if (verifyBtn) {
                    verifyBtn.disabled = false;
                    verifyText.innerHTML = '<i class="fa-solid fa-circle-check me-1"></i> ভেরিফাই';
                }
                if (otpFeedback) {
                    otpFeedback.classList.remove('d-none', 'text-success');
                    otpFeedback.classList.add('text-danger');
                    otpFeedback.innerHTML = '<i class="fa-solid fa-circle-xmark me-1"></i> ওটিপি যাচাইয়ের সময় সমস্যা হয়েছে।';
                }
            });
        }

        // Toggle Password Visibility
        function togglePasswordVisibility(inputId, btn) {
            const input = document.getElementById(inputId);
            if (!input) return;
            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';
            if (btn) {
                const icon = btn.querySelector('i');
                if (icon) {
                    icon.className = isPassword ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye';
                }
            }
        }

        // AJAX Authentication Handler for Seamless In-Page Login & Quick Register
        function showAuthAlert(msg, isSuccess) {
            const alertBox = document.getElementById('authAlertBox');
            if (!alertBox) return;
            alertBox.className = 'alert ' + (isSuccess ? 'alert-success' : 'alert-danger') + ' rounded-3 py-2 px-3 mb-3 small d-flex align-items-center gap-2';
            alertBox.innerHTML = '<i class="fa-solid ' + (isSuccess ? 'fa-circle-check text-success fs-6' : 'fa-circle-exclamation text-danger fs-6') + '"></i><div>' + msg + '</div>';
            alertBox.classList.remove('d-none');
        }

        function unlockFormOnClient(user) {
            const authGate = document.getElementById('authGateCard');
            const lockMsg = document.getElementById('lockedNoticeBar');
            const formWrap = document.getElementById('libraryFormWrapper');

            if (authGate) authGate.classList.add('d-none');
            if (lockMsg) lockMsg.classList.add('d-none');
            if (formWrap) {
                formWrap.classList.remove('opacity-50', 'pe-none');
            }

            if (user) {
                const nameInput = document.querySelector('#dynamicLibraryForm input[name="name"]');
                const phoneInput = document.querySelector('#dynamicLibraryForm input[name="phone"]');
                const emailInput = document.querySelector('#dynamicLibraryForm input[name="email"]');
                if (nameInput && user.name && !nameInput.value) nameInput.value = user.name;
                if (phoneInput && user.phone && !phoneInput.value) phoneInput.value = user.phone;
                if (emailInput && user.email && !emailInput.value) emailInput.value = user.email;
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            const gateLoginForm = document.getElementById('gateLoginForm');
            const gateRegForm = document.getElementById('gateRegisterForm');

            if (gateLoginForm) {
                gateLoginForm.addEventListener('submit', function (e) {
                    const btn = document.getElementById('gateLoginBtn');
                    const origHtml = btn ? btn.innerHTML : '';
                    if (btn) {
                        btn.disabled = true;
                        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> যাচাই করা হচ্ছে...';
                    }

                    const formData = new FormData(gateLoginForm);
                    fetch("{{ route('login') }}", {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json().then(data => ({ status: res.status, body: data })))
                    .then(res => {
                        if (res.body.success) {
                            showAuthAlert('লগইন সফল হয়েছে! ফরম প্রস্তুত করা হচ্ছে...', true);
                            unlockFormOnClient(res.body.user);
                            setTimeout(() => {
                                window.location.reload();
                            }, 500);
                        } else {
                            if (btn) {
                                btn.disabled = false;
                                btn.innerHTML = origHtml;
                            }

                            // If account needs OTP verification or password setting
                            if (res.body.requires_otp) {
                                const targetPhone = res.body.phone || formData.get('email');
                                switchAuthTab('register', targetPhone);
                                showAuthAlert(res.body.message || 'আপনার অ্যাকাউন্টের ওটিপি ভেরিফিকেশন ও পাসওয়ার্ড সেট করতে ওটিপি কোড পাঠানো হচ্ছে...', true);
                                setTimeout(() => {
                                    handleSendOtp();
                                }, 300);
                                return;
                            }

                            const errMsg = res.body.message || (res.body.errors ? Object.values(res.body.errors)[0][0] : 'লগইন ব্যর্থ হয়েছে। তথ্য যাচাই করুন।');
                            showAuthAlert(errMsg, false);
                        }
                    })
                    .catch(() => {
                        gateLoginForm.submit();
                    });

                    e.preventDefault();
                });
            }

            if (gateRegForm) {
                gateRegForm.addEventListener('submit', function (e) {
                    const btn = document.getElementById('gateRegisterBtn');
                    const origHtml = btn ? btn.innerHTML : '';

                    if (!isPhoneVerified) {
                        e.preventDefault();
                        showAuthAlert('অনুগ্রহ করে প্রথমে মোবাইলে পাঠানো ৬-ডিজিটের ওটিপি কোড দিয়ে ভেরিফাই সম্পন্ন করুন।', false);
                        const otpInput = document.getElementById('buyerOtpCode');
                        if (otpInput) otpInput.focus();
                        return;
                    }

                    if (btn) {
                        btn.disabled = true;
                        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> একাউন্ট তৈরি হচ্ছে...';
                    }

                    const formData = new FormData(gateRegForm);
                    fetch("{{ route('register.quick-customer') }}", {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json().then(data => ({ status: res.status, body: data })))
                    .then(res => {
                        if (res.body.success) {
                            showAuthAlert(res.body.message || 'রেজিস্ট্রেশন সফল হয়েছে! ফরম প্রস্তুত করা হচ্ছে...', true);
                            unlockFormOnClient(res.body.user);
                            setTimeout(() => {
                                window.location.reload();
                            }, 500);
                        } else {
                            if (btn) {
                                btn.disabled = false;
                                btn.innerHTML = origHtml;
                            }
                            const errMsg = res.body.message || (res.body.errors ? Object.values(res.body.errors)[0][0] : 'রেজিস্ট্রেশন ব্যর্থ হয়েছে।');
                            showAuthAlert(errMsg, false);
                        }
                    })
                    .catch(() => {
                        gateRegForm.submit();
                    });

                    e.preventDefault();
                });
            }
        });
    </script>
@endpush