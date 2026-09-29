@extends('layouts.app')

@section('title', 'আইডিয়া পাঠাগার — বই অনুদান আবেদন ফরম')

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
            $brandName = $fSettings['brand_name'] ?? 'আইডিয়া পাঠাগার';
            $subTitle = $fSettings['sub_title'] ?? 'বই অনুদান আবেদন ফরম';
        @endphp

        {{-- হেডার ব্যানার --}}
        <div class="lib-header-banner mb-3 rounded-4 shadow-sm">
            <div class="lib-header-inner">
                <div class="lib-title-area">
                    <div class="lib-logo-wrapper" title="{{ $brandName }}">
                        <img src="{{ $siteLogo }}" alt="{{ $brandName }}" class="lib-header-logo" onerror="this.src='{{ asset('images/logo.png') }}';">
                    </div>
                    <div>
                        <h1 class="lib-main-title">{{ $brandName }}</h1>
                        <div class="lib-sub-title">{{ $subTitle }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- কাস্টমার অথেন্টিকেশন গেইট (যদি ইউজার লগইন না করা থাকে) --}}
        @guest
            <div class="lib-auth-gate-card">
                <div class="lib-auth-gate-header">
                    <h3 class="lib-auth-gate-title">
                        <i class="fa-solid fa-user-lock"></i>
                        <span>পাঠাগার আবেদন করার পূর্বে কাস্টমার লগইন / নিবন্ধন করুন</span>
                    </h3>
                    <span class="badge bg-warning text-dark fw-bold px-2.5 py-1.5"><i class="fa-solid fa-circle-info me-1"></i> ধাপ ১: একাউন্ট ভেরিফিকেশন</span>
                </div>
                <div class="lib-auth-gate-body">
                    <div class="alert alert-info py-2.5 px-3 rounded-3 small mb-3 d-flex align-items-start gap-2">
                        <i class="fa-solid fa-circle-info fs-5 text-info mt-0.5 flex-shrink-0"></i>
                        <div>
                            <strong>কেন লগইন প্রয়োজন?</strong> বই অনুদানের আবেদন জমা করার পর আপনার একাউন্ট ড্যাশবোর্ডে আবেদনের অগ্রগতি, অনুমোদন ও স্লিপ সংরক্ষিত থাকবে। আপনার ইতিমধ্যে একাউন্ট থাকলে সরাসরি লগইন করুন অথবা ১ মিনিটে নতুন একাউন্ট খুলুন।
                        </div>
                    </div>

                    {{-- ট্যাব সুইচ বাটন --}}
                    <div class="lib-auth-nav-tabs">
                        <button type="button" class="lib-auth-tab-btn active" id="tabBtnLogin" onclick="switchAuthTab('login')">
                            <i class="fa-solid fa-arrow-right-to-bracket"></i>
                            <span>বিদ্যমান একাউন্টে লগইন (Login)</span>
                        </button>
                        <button type="button" class="lib-auth-tab-btn" id="tabBtnRegister" onclick="switchAuthTab('register')">
                            <i class="fa-solid fa-user-plus"></i>
                            <span>নতুন একাউন্ট খুলুন (Register)</span>
                        </button>
                    </div>

                    {{-- AJAX রেসপন্স এলার্ট বক্স --}}
                    <div id="authAlertBox" class="d-none alert rounded-3 py-2.5 px-3 mb-3 small"></div>

                    {{-- ফর্ম ১: কাস্টমার লগইন ফর্ম --}}
                    <form action="{{ route('login') }}" method="POST" id="gateLoginForm">
                        @csrf
                        <input type="hidden" name="redirect_to" value="{{ request()->fullUrl() }}">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="lib-field-group">
                                    <label class="lib-field-label">
                                        <span>মোবাইল নম্বর / ইমেইল / ইউজারনেম <span class="text-danger">*</span></span>
                                    </label>
                                    <input type="text" name="email" id="gateLoginEmail" class="lib-input font-monospace" placeholder="যেমন: 017XXXXXXXX বা library@gmail.com" required autocomplete="username">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="lib-field-group">
                                    <label class="lib-field-label d-flex justify-content-between">
                                        <span>পাসওয়ার্ড <span class="text-danger">*</span></span>
                                        <a href="{{ route('password.request') }}" target="_blank" class="text-success small text-decoration-none">পাসওয়ার্ড ভুলে গেছেন?</a>
                                    </label>
                                    <input type="password" name="password" id="gateLoginPassword" class="lib-input" placeholder="আপনার একাউন্টের পাসওয়ার্ড লিখুন" required autocomplete="current-password">
                                </div>
                            </div>
                            <div class="col-12 d-flex align-items-center justify-content-between flex-wrap gap-2 pt-2">
                                <label class="form-check-label small text-muted d-flex align-items-center gap-1.5 cursor-pointer">
                                    <input type="checkbox" name="remember" class="form-check-input mt-0" checked>
                                    <span>লগইন তথ্য সংরক্ষণ করুন</span>
                                </label>
                                <button type="submit" id="gateLoginBtn" class="btn btn-success fw-bold px-4 py-2.5 rounded-3 d-flex align-items-center gap-2">
                                    <i class="fa-solid fa-arrow-right-to-bracket"></i>
                                    <span>লগইন করুন ও ফরম আনলক করুন</span>
                                </button>
                            </div>
                        </div>
                    </form>

                    {{-- ফর্ম ২: দ্রুত কাস্টমার রেজিস্টার ফর্ম --}}
                    <form action="{{ route('register.quick-customer') }}" method="POST" id="gateRegisterForm" class="d-none">
                        @csrf
                        <input type="hidden" name="redirect_to" value="{{ request()->fullUrl() }}">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="lib-field-group">
                                    <label class="lib-field-label">
                                        <span>আপনার পূর্ণ নাম <span class="text-danger">*</span></span>
                                    </label>
                                    <input type="text" name="name" id="gateRegName" class="lib-input" placeholder="প্রতিনিধি / আবেদনকারীর পূর্ণ নাম লিখুন" required autocomplete="name">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="lib-field-group">
                                    <label class="lib-field-label">
                                        <span>মোবাইল নম্বর (১১ ডিজিট) <span class="text-danger">*</span></span>
                                    </label>
                                    <div class="lib-phone-group">
                                        <span class="lib-phone-prefix"><i class="fa-solid fa-phone"></i> +৮৮</span>
                                        <input type="tel" name="phone" id="gateRegPhone" class="lib-input font-monospace" placeholder="017XXXXXXXX" required maxlength="11" autocomplete="tel">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="lib-field-group">
                                    <label class="lib-field-label">
                                        <span>ইমেইল ঠিকানা <small class="text-muted">(ঐচ্ছিক)</small></span>
                                    </label>
                                    <input type="email" name="email" id="gateRegEmail" class="lib-input" placeholder="user@gmail.com" autocomplete="email">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="lib-field-group">
                                    <label class="lib-field-label">
                                        <span>পাসওয়ার্ড তৈরি করুন <span class="text-danger">*</span></span>
                                    </label>
                                    <input type="password" name="password" id="gateRegPassword" class="lib-input" placeholder="কমপক্ষে ৬ অক্ষরের পাসওয়ার্ড" required minlength="6" autocomplete="new-password">
                                </div>
                            </div>
                            <div class="col-12 d-flex align-items-center justify-content-between flex-wrap gap-2 pt-2">
                                <div class="small text-muted">
                                    <i class="fa-solid fa-shield-halved text-success me-1"></i> একাউন্ট তৈরি করে স্বয়ংক্রিয়ভাবে লাইব্রেরি ফরম পূরণ করতে পারবেন
                                </div>
                                <button type="submit" id="gateRegisterBtn" class="btn btn-success fw-bold px-4 py-2.5 rounded-3 d-flex align-items-center gap-2">
                                    <i class="fa-solid fa-user-check"></i>
                                    <span>একাউন্ট তৈরি করুন ও ফরম খুলুন</span>
                                </button>
                            </div>
                        </div>
                    </form>

                </div>
            </div>
        @endguest

        {{-- যদি লগইন করা থাকে তবে প্রতিনিধির তথ্য বার দেখানো হবে --}}
        @auth
            <div class="lib-user-badge-bar">
                <div class="lib-user-info-text">
                    <div class="d-flex align-items-center justify-content-center bg-success text-white rounded-circle" style="width: 38px; height: 38px;">
                        <i class="fa-solid fa-user-check"></i>
                    </div>
                    <div>
                        <div class="fw-bold">
                            {{ auth()->user()->name }}
                            <span class="badge bg-success-subtle text-success border border-success-subtle ms-1"><i class="fa-solid fa-circle-check me-1"></i>লগইনকৃত প্রতিনিধি একাউন্ট</span>
                        </div>
                        <div class="small text-muted font-monospace">
                            <i class="fa-solid fa-phone me-1"></i> {{ auth()->user()->phone ?? '—' }} 
                            @if(auth()->user()->email)
                                | <i class="fa-solid fa-envelope me-1"></i> {{ auth()->user()->email }}
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
        <div id="libraryFormWrapper" class="{{ !auth()->check() ? 'opacity-50 pe-none' : '' }}">
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
                        <i class="fa-solid fa-landmark"></i>
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
                                            <span>পাঠাগারের পূর্ণ নাম <span class="text-danger">*</span></span>
                                        </label>
                                        <input type="text" name="institution_or_org" class="lib-input" placeholder="পাঠাগারের পূর্ণ নাম লিখুন" value="{{ old('institution_or_org') }}" required autocomplete="off">
                                    </div>
                                </div>
                                <div class="lib-col-12">
                                    <div class="lib-field-group">
                                        <label class="lib-field-label">
                                            <span>পাঠাগারের ধরন <span class="text-danger">*</span></span>
                                        </label>
                                        <select name="library_type" class="lib-select" required>
                                            <option value="গণপাঠাগার / পাবলিক লাইব্রেরি" {{ old('library_type') === 'গণপাঠাগার / পাবলিক লাইব্রেরি' || old('library_type') === 'Public / Community Library' ? 'selected' : '' }}>গণপাঠাগার / পাবলিক লাইব্রেরি</option>
                                            <option value="বিদ্যালয় লাইব্রেরি" {{ old('library_type') === 'বিদ্যালয় লাইব্রেরি' || old('library_type') === 'School Library' ? 'selected' : '' }}>বিদ্যালয় লাইব্রেরি</option>
                                            <option value="কলেজ লাইব্রেরি" {{ old('library_type') === 'কলেজ লাইব্রেরি' || old('library_type') === 'College Library' ? 'selected' : '' }}>কলেজ লাইব্রেরি</option>
                                            <option value="মাদরাসা পাঠাগার" {{ old('library_type') === 'মাদরাসা পাঠাগার' || old('library_type') === 'Madrasa Library' ? 'selected' : '' }}>মাদরাসা পাঠাগার</option>
                                            <option value="বিশ্ববিদ্যালয় লাইব্রেরি" {{ old('library_type') === 'বিশ্ববিদ্যালয় লাইব্রেরি' || old('library_type') === 'University / Departmental Library' ? 'selected' : '' }}>বিশ্ববিদ্যালয় লাইব্রেরি</option>
                                            <option value="ক্লাব / সামাজিক পাঠাগার" {{ old('library_type') === 'ক্লাব / সামাজিক পাঠাগার' || old('library_type') === 'Youth Club / Organization' ? 'selected' : '' }}>ক্লাব / সামাজিক পাঠাগার</option>
                                            <option value="অন্যান্য পাঠাগার" {{ old('library_type') === 'অন্যান্য পাঠাগার' || old('library_type') === 'Other Library' ? 'selected' : '' }}>অন্যান্য পাঠাগার</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="lib-col-6">
                                    <div class="lib-field-group">
                                        <label class="lib-field-label">
                                            <span>সরকারি / গ্রন্থকেন্দ্র নিবন্ধন নম্বর</span>
                                        </label>
                                        <input type="text" name="reg_no" class="lib-input" placeholder="নিবন্ধন নম্বর লিখুন (যদি থাকে)" value="{{ old('reg_no') }}">
                                    </div>
                                </div>
                                <div class="lib-col-6">
                                    <div class="lib-field-group">
                                        <label class="lib-field-label">
                                            <span>প্রতিষ্ঠা সাল</span>
                                        </label>
                                        <input type="text" name="established_year" class="lib-input" placeholder="যেমন: ২০১৮" value="{{ old('established_year') }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- সাইনবোর্ড / ভবনের ছবি --}}
                        <div class="lib-col-4">
                            <div class="lib-photo-card">
                                <label class="lib-field-label mb-2 text-center">
                                    <span>পাঠাগারের ছবি</span>
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
                                <div id="libOptBadge" class="lib-photo-badge">✓ ছবি প্রস্তুত হয়েছে</div>
                                <input type="file" id="libPhotoInput" name="student_photo" accept="image/*" class="d-none">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- সেকশন ২: দায়িত্বপ্রাপ্ত প্রতিনিধি ও যোগাযোগের তথ্য --}}
                <div class="lib-section-head">
                    <div class="sec-left">
                        <i class="fa-solid fa-id-badge"></i>
                        <span>২. প্রতিনিধি ও যোগাযোগের তথ্য</span>
                    </div>
                </div>
                <div class="lib-form-body">
                    <div class="lib-grid-row">
                        <div class="lib-col-6">
                            <div class="lib-field-group">
                                <label class="lib-field-label">
                                    <span>প্রতিনিধির নাম <span class="text-danger">*</span></span>
                                </label>
                                <input type="text" name="name" class="lib-input" placeholder="আবেদনকারী প্রতিনিধির পূর্ণ নাম" value="{{ old('name', auth()->user()?->name) }}" required>
                            </div>
                        </div>
                        <div class="lib-col-6">
                            <div class="lib-field-group">
                                <label class="lib-field-label">
                                    <span>পদবি <span class="text-danger">*</span></span>
                                </label>
                                <select name="designation_or_class" class="lib-select" required>
                                    <option value="সাধারণ সম্পাদক" {{ old('designation_or_class') === 'সাধারণ সম্পাদক' || old('designation_or_class') === 'General Secretary' ? 'selected' : '' }}>সাধারণ সম্পাদক</option>
                                    <option value="সভাপতি" {{ old('designation_or_class') === 'সভাপতি' || old('designation_or_class') === 'President' ? 'selected' : '' }}>সভাপতি</option>
                                    <option value="প্রতিষ্ঠাতা / পরিচালক" {{ old('designation_or_class') === 'প্রতিষ্ঠাতা / পরিচালক' || old('designation_or_class') === 'Director / Founder' ? 'selected' : '' }}>প্রতিষ্ঠাতা / পরিচালক</option>
                                    <option value="প্রধান শিক্ষক / অধ্যক্ষ" {{ old('designation_or_class') === 'প্রধান শিক্ষক / অধ্যক্ষ' || old('designation_or_class') === 'Principal / Headmaster' ? 'selected' : '' }}>প্রধান শিক্ষক / অধ্যক্ষ</option>
                                    <option value="গ্রন্থাগারিক" {{ old('designation_or_class') === 'গ্রন্থাগারিক' || old('designation_or_class') === 'Librarian' ? 'selected' : '' }}>গ্রন্থাগারিক</option>
                                    <option value="আহ্বায়ক / সদস্য" {{ old('designation_or_class') === 'আহ্বায়ক / সদস্য' || old('designation_or_class') === 'Member / Convener' ? 'selected' : '' }}>আহ্বায়ক / সদস্য</option>
                                </select>
                            </div>
                        </div>
                        <div class="lib-col-6">
                            <div class="lib-field-group">
                                <label class="lib-field-label">
                                    <span>মোবাইল নম্বর <span class="text-danger">*</span></span>
                                </label>
                                <div class="lib-phone-group">
                                    <span class="lib-phone-prefix"><i class="fa-solid fa-phone"></i> +৮৮</span>
                                    <input type="tel" name="phone" id="libPhone" class="lib-input font-monospace" placeholder="017XXXXXXXX" value="{{ old('phone', auth()->user()?->phone) }}" required maxlength="11">
                                </div>
                            </div>
                        </div>
                        <div class="lib-col-6">
                            <div class="lib-field-group">
                                <label class="lib-field-label">
                                    <span>বিকল্প মোবাইল নম্বর</span>
                                </label>
                                <div class="lib-phone-group">
                                    <span class="lib-phone-prefix"><i class="fa-solid fa-mobile-screen"></i> +৮৮</span>
                                    <input type="tel" name="guardian_phone" class="lib-input font-monospace" placeholder="018XXXXXXXX" value="{{ old('guardian_phone') }}" maxlength="11">
                                </div>
                            </div>
                        </div>
                        <div class="lib-col-6">
                            <div class="lib-field-group">
                                <label class="lib-field-label">
                                    <span>ইমেইল ঠিকানা</span>
                                </label>
                                <input type="email" name="email" class="lib-input" placeholder="library@example.com" value="{{ old('email', auth()->user()?->email) }}">
                            </div>
                        </div>
                        <div class="lib-col-6">
                            <div class="lib-field-group">
                                <label class="lib-field-label">
                                    <span>জাতীয় পরিচয়পত্র (NID) নম্বর</span>
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
                                    <span>সভাপতির নাম <span class="text-danger">*</span></span>
                                </label>
                                <input type="text" name="president_name" class="lib-input" placeholder="সভাপতির নাম লিখুন" value="{{ old('president_name') }}" required>
                            </div>
                        </div>
                        <div class="lib-col-6">
                            <div class="lib-field-group">
                                <label class="lib-field-label">
                                    <span>সভাপতির মোবাইল নম্বর <span class="text-danger">*</span></span>
                                </label>
                                <div class="lib-phone-group">
                                    <span class="lib-phone-prefix"><i class="fa-solid fa-phone"></i> +৮৮</span>
                                    <input type="tel" name="president_phone" class="lib-input font-monospace" placeholder="01XXXXXXXXX" value="{{ old('president_phone') }}" required maxlength="11">
                                </div>
                            </div>
                        </div>
                        <div class="lib-col-6">
                            <div class="lib-field-group">
                                <label class="lib-field-label">
                                    <span>সাধারণ সম্পাদকের নাম <span class="text-danger">*</span></span>
                                </label>
                                <input type="text" name="secretary_name" class="lib-input" placeholder="সাধারণ সম্পাদকের নাম লিখুন" value="{{ old('secretary_name') }}" required>
                            </div>
                        </div>
                        <div class="lib-col-6">
                            <div class="lib-field-group">
                                <label class="lib-field-label">
                                    <span>সাধারণ সম্পাদকের মোবাইল নম্বর <span class="text-danger">*</span></span>
                                </label>
                                <div class="lib-phone-group">
                                    <span class="lib-phone-prefix"><i class="fa-solid fa-phone"></i> +৮৮</span>
                                    <input type="tel" name="secretary_phone" class="lib-input font-monospace" placeholder="01XXXXXXXXX" value="{{ old('secretary_phone') }}" required maxlength="11">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- সেকশন ৩: অবস্থান ও পূর্ণ ডাক ঠিকানা --}}
                <div class="lib-section-head">
                    <div class="sec-left">
                        <i class="fa-solid fa-map-location-dot"></i>
                        <span>৩. পাঠাগারের অবস্থান ও পূর্ণ ডাক ঠিকানা</span>
                    </div>
                </div>
                <div class="lib-form-body">
                    <div class="lib-grid-row">
                        <div class="lib-col-6">
                            <div class="lib-field-group">
                                <label class="lib-field-label">
                                    <span>বিভাগ <span class="text-danger">*</span></span>
                                </label>
                                <select name="division" id="libDivision" class="lib-select" data-old="{{ old('division') }}" required>
                                    <option value="">-- বিভাগ নির্বাচন করুন --</option>
                                </select>
                            </div>
                        </div>
                        <div class="lib-col-6">
                            <div class="lib-field-group">
                                <label class="lib-field-label">
                                    <span>জেলা <span class="text-danger">*</span></span>
                                </label>
                                <select name="district" id="libDistrict" class="lib-select" data-old="{{ old('district') }}" required>
                                    <option value="">-- প্রথমে বিভাগ নির্বাচন করুন --</option>
                                </select>
                            </div>
                        </div>
                        <div class="lib-col-6">
                            <div class="lib-field-group">
                                <label class="lib-field-label">
                                    <span>উপজেলা / থানা <span class="text-danger">*</span></span>
                                </label>
                                <select name="thana" id="libThana" class="lib-select" data-old="{{ old('thana') }}" required>
                                    <option value="">-- প্রথমে জেলা নির্বাচন করুন --</option>
                                </select>
                            </div>
                        </div>
                        <div class="lib-col-6">
                            <div class="lib-field-group">
                                <label class="lib-field-label">
                                    <span>ইউনিয়ন / গ্রাম / এলাকা <span class="text-danger">*</span></span>
                                </label>
                                <input type="text" name="perm_village" class="lib-input" placeholder="গ্রাম / ওয়ার্ড / রোড নম্বর" value="{{ old('perm_village') }}" required>
                            </div>
                        </div>
                        <div class="lib-col-6">
                            <div class="lib-field-group">
                                <label class="lib-field-label">
                                    <span>ডাকঘর ও পোস্ট কোড <span class="text-danger">*</span></span>
                                </label>
                                <input type="text" name="perm_post_office" class="lib-input" placeholder="যেমন: ডাকঘর - ৫৪০০" value="{{ old('perm_post_office') }}" required>
                            </div>
                        </div>
                        <div class="lib-col-6">
                            <div class="lib-field-group">
                                <label class="lib-field-label">
                                    <span>পূর্ণ ডাক ঠিকানা <span class="text-danger">*</span></span>
                                </label>
                                <input type="text" name="library_address" class="lib-input" placeholder="বই পার্সেল গ্রহণের পূর্ণ ডাক ঠিকানা" value="{{ old('library_address') }}" required>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- সেকশন ৪: বই ও অনুদান সংক্রান্ত তথ্য --}}
                <div class="lib-section-head">
                    <div class="sec-left">
                        <i class="fa-solid fa-book-bookmark"></i>
                        <span>৪. বই ও অনুদান সংক্রান্ত তথ্য</span>
                    </div>
                </div>
                <div class="lib-form-body">
                    <div class="lib-grid-row">
                        <div class="lib-col-6">
                            <div class="lib-field-group">
                                <label class="lib-field-label">
                                    <span>পাঠাগারের বর্তমান বই সংখ্যা</span>
                                </label>
                                <input type="text" name="current_book_count" class="lib-input font-monospace" placeholder="যেমন: ৫০০" value="{{ old('current_book_count') }}">
                            </div>
                        </div>
                        <div class="lib-col-6">
                            <div class="lib-field-group">
                                <label class="lib-field-label">
                                    <span>নিয়মিত পাঠক / সদস্য সংখ্যা</span>
                                </label>
                                <input type="text" name="reader_count" class="lib-input font-monospace" placeholder="যেমন: ১২০" value="{{ old('reader_count') }}">
                            </div>
                        </div>
                        <div class="lib-col-12">
                            <div class="lib-field-group">
                                <label class="lib-field-label">
                                    <span>বই গ্রহণের পছন্দনীয় মাধ্যম</span>
                                </label>
                                <select name="delivery_method" class="lib-select">
                                    <option value="সুন্দরবন কুরিয়ার সার্ভিস" {{ old('delivery_method') === 'সুন্দরবন কুরিয়ার সার্ভিস' ? 'selected' : '' }}>সুন্দরবন কুরিয়ার সার্ভিস</option>
                                    <option value="এসএ পরিবহন" {{ old('delivery_method') === 'এসএ পরিবহন' ? 'selected' : '' }}>এসএ পরিবহন</option>
                                    <option value="করতোয়া কুরিয়ার" {{ old('delivery_method') === 'করতোয়া কুরিয়ার' ? 'selected' : '' }}>করতোয়া কুরিয়ার</option>
                                    <option value="রেডএক্স / স্টিডফাস্ট হোম ডেলিভারি" {{ old('delivery_method') === 'রেডএক্স / স্টিডফাস্ট হোম ডেলিভারি' ? 'selected' : '' }}>রেডএক্স / স্টিডফাস্ট হোম ডেলিভারি</option>
                                    <option value="সরাসরি অফিস থেকে সংগ্রহ" {{ old('delivery_method') === 'সরাসরি অফিস থেকে সংগ্রহ' ? 'selected' : '' }}>সরাসরি অফিস থেকে সংগ্রহ</option>
                                </select>
                            </div>
                        </div>
                        <div class="lib-col-12">
                            <div class="lib-field-group">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <label class="lib-field-label mb-0">
                                        <span>প্রত্যাশিত বইয়ের বিষয়সমূহ</span>
                                    </label>
                                    <span id="selectedGenreCount" class="badge bg-success-subtle text-success border border-success-subtle"></span>
                                </div>
                                <div class="genre-chips-container">
                                    @php
                                        $genreOptions = [
                                            'Literature & Novels'       => 'সাহিত্য ও উপন্যাস',
                                            'Liberation War & History'  => 'মুক্তিযুদ্ধ ও ইতিহাস',
                                            'Poetry & Rhymes'           => 'কবিতা ও ছড়া',
                                            'Children & Teenagers'      => 'শিশু-কিশোর সাহিত্য',
                                            'Science & Technology'      => 'বিজ্ঞান ও প্রযুক্তি',
                                            'Career & Self-Development' => 'ক্যারিয়ার ও আত্মউন্নয়ন',
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

                {{-- সেকশন ৫: অনুদানের প্রয়োজনীয়তা ও উদ্দেশ্য --}}
                <div class="lib-section-head">
                    <div class="sec-left">
                        <i class="fa-solid fa-feather-pointed"></i>
                        <span>৫. অনুদানের প্রয়োজনীয়তা ও উদ্দেশ্য</span>
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
                        আইডিয়া পাঠাগার নিজ উদ্যোগে বই বিতরণ করে। বই প্রদানের ক্ষেত্রে যে কোনো সিদ্ধান্ত গ্রহণের ক্ষমতা সংরক্ষণ করে।
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
                        <span>আবেদন জমা দিন</span>
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
        function switchAuthTab(tab) {
            const loginBtn = document.getElementById('tabBtnLogin');
            const regBtn = document.getElementById('tabBtnRegister');
            const loginForm = document.getElementById('gateLoginForm');
            const regForm = document.getElementById('gateRegisterForm');
            const alertBox = document.getElementById('authAlertBox');

            if (alertBox) {
                alertBox.className = 'd-none';
                alertBox.innerHTML = '';
            }

            if (tab === 'login') {
                loginBtn.classList.add('active');
                regBtn.classList.remove('active');
                loginForm.classList.remove('d-none');
                regForm.classList.add('d-none');
            } else {
                regBtn.classList.add('active');
                loginBtn.classList.remove('active');
                regForm.classList.remove('d-none');
                loginForm.classList.add('d-none');
            }
        }

        // AJAX Authentication Handler for Seamless In-Page Login & Quick Register
        document.addEventListener('DOMContentLoaded', function () {
            const gateLoginForm = document.getElementById('gateLoginForm');
            const gateRegForm = document.getElementById('gateRegisterForm');
            const alertBox = document.getElementById('authAlertBox');

            function showAuthAlert(msg, isSuccess) {
                if (!alertBox) return;
                alertBox.className = 'alert ' + (isSuccess ? 'alert-success' : 'alert-danger') + ' rounded-3 py-2 px-3 mb-3 small d-flex align-items-center gap-2';
                alertBox.innerHTML = '<i class="fa-solid ' + (isSuccess ? 'fa-circle-check text-success' : 'fa-circle-exclamation text-danger') + '"></i><div>' + msg + '</div>';
                alertBox.classList.remove('d-none');
            }

            if (gateLoginForm) {
                gateLoginForm.addEventListener('submit', function (e) {
                    const btn = document.getElementById('gateLoginBtn');
                    const origHtml = btn ? btn.innerHTML : '';
                    if (btn) {
                        btn.disabled = true;
                        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> যাচাই করা হচ্ছে...';
                    }

                    // Let normal submit or try fetch
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
                            showAuthAlert('লগইন সফল হয়েছে! ফরম লোড করা হচ্ছে...', true);
                            setTimeout(() => {
                                window.location.reload();
                            }, 500);
                        } else {
                            if (btn) {
                                btn.disabled = false;
                                btn.innerHTML = origHtml;
                            }
                            const errMsg = res.body.message || (res.body.errors ? Object.values(res.body.errors)[0][0] : 'লগইন ব্যর্থ হয়েছে। তথ্য যাচাই করুন।');
                            showAuthAlert(errMsg, false);
                        }
                    })
                    .catch(() => {
                        // Fallback to standard form submit
                        gateLoginForm.submit();
                    });

                    e.preventDefault();
                });
            }

            if (gateRegForm) {
                gateRegForm.addEventListener('submit', function (e) {
                    const btn = document.getElementById('gateRegisterBtn');
                    const origHtml = btn ? btn.innerHTML : '';
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
                            showAuthAlert(res.body.message || 'রেজিস্ট্রেশন সফল হয়েছে!', true);
                            setTimeout(() => {
                                window.location.reload();
                            }, 500);
                        } else {
                            if (btn) {
                                btn.disabled = false;
                                btn.innerHTML = origHtml;
                            }
                            const errMsg = res.body.message || (res.body.errors ? Object.values(res.body.errors)[0][0] : 'রেজিস্ট্রেশন ব্যর্থ হয়েছে।');
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
