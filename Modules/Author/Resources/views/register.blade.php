@extends('layouts.app')

@section('title', 'লেখক হিসেবে নিবন্ধন করুন — আইডিয়া প্রকাশন ও ডিজিটাল লাইব্রেরি')

@section('meta_description', 'আইডিয়া প্রকাশনের লেখক পরিবারে যুক্ত হন। সর্বোচ্চ ৫০%+ রয়্যালটি, ডিজিটাল কপিরাইট সুরক্ষা এবং বিশ্বব্যাপী পাঠকদের কাছে পৌঁছানোর সুযোগ গ্রহণ করুন।')

@push('head')
<style>
/* -------------------------------------------------------------
   CLEAN LIGHT-THEME AUTHOR REGISTRATION (Substack/Stripe Spec)
------------------------------------------------------------- */
:root {
    --auth-primary: #4f46e5;
    --auth-primary-hover: #4338ca;
    --auth-accent: #0284c7;
    --auth-success: #059669;
    --auth-bg-page: #f8fafc;
    --auth-card-bg: #ffffff;
    --auth-border-subtle: #e2e8f0;
    --auth-text-main: #0f172a;
    --auth-text-muted: #64748b;
}

.author-reg-page {
    background: linear-gradient(180deg, #f1f5f9 0%, #f8fafc 100%);
    color: var(--auth-text-main);
    min-height: 100vh;
    padding-top: 2.5rem;
    padding-bottom: 5.5rem;
    font-family: 'Hind Siliguri', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

/* Breadcrumb */
.auth-breadcrumb {
    font-size: 0.9rem;
    background: transparent;
    padding: 0;
    margin-bottom: 1.75rem;
}
.auth-breadcrumb a {
    color: #64748b;
    text-decoration: none;
}
.auth-breadcrumb a:hover {
    color: var(--auth-primary);
}

/* Left Showcase Card */
.author-info-card {
    background: #ffffff;
    border: 1px solid var(--auth-border-subtle);
    border-radius: 20px;
    padding: 2.5rem 2.25rem;
    box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.04), 0 1px 3px rgba(0, 0, 0, 0.02);
    height: 100%;
}

.badge-prestige {
    background: #eef2ff;
    color: #4f46e5;
    font-size: 0.85rem;
    font-weight: 700;
    padding: 0.45rem 1rem;
    border-radius: 9999px;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    border: 1px solid #e0e7ff;
    margin-bottom: 1.25rem;
}

.benefit-card-clean {
    display: flex;
    align-items: flex-start;
    gap: 1.15rem;
    padding: 1.25rem;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    margin-bottom: 1.15rem;
    transition: all 0.2s ease;
}
.benefit-card-clean:hover {
    background: #ffffff;
    border-color: #cbd5e1;
    transform: translateY(-2px);
    box-shadow: 0 8px 20px -4px rgba(0, 0, 0, 0.05);
}
.benefit-icon-clean {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    flex-shrink: 0;
}

/* Dynamic Royalty Calculator */
.calc-card-clean {
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 1.5rem;
    margin-top: 1.5rem;
}
.calc-earnings-clean {
    font-size: 2rem;
    font-weight: 800;
    color: #4f46e5;
    line-height: 1;
}

/* Right Main Registration Wizard Card */
.author-wizard-card {
    background: #ffffff;
    border: 1px solid var(--auth-border-subtle);
    border-radius: 20px;
    padding: 2.75rem 2.5rem;
    box-shadow: 0 15px 35px -5px rgba(0, 0, 0, 0.05), 0 1px 3px rgba(0, 0, 0, 0.03);
}

/* Stepper Progress Bar Header */
.stepper-header {
    margin-bottom: 2.25rem;
}
.stepper-steps-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 0.9rem;
}
.stepper-step-item {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    font-size: 0.92rem;
    font-weight: 600;
    color: #94a3b8;
    cursor: pointer;
    transition: color 0.2s ease;
}
.stepper-step-item.active {
    color: #4f46e5;
    font-weight: 700;
}
.stepper-step-item.completed {
    color: #059669;
}
.stepper-bubble {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.82rem;
    background: #e2e8f0;
    color: #64748b;
    font-weight: 700;
    transition: all 0.2s ease;
}
.stepper-step-item.active .stepper-bubble {
    background: #4f46e5;
    color: #ffffff;
    box-shadow: 0 4px 10px rgba(79, 70, 229, 0.3);
}
.stepper-step-item.completed .stepper-bubble {
    background: #059669;
    color: #ffffff;
}

.stepper-track-bar {
    height: 6px;
    background: #e2e8f0;
    border-radius: 9999px;
    overflow: hidden;
}
.stepper-track-fill {
    height: 100%;
    width: 33.33%;
    background: linear-gradient(90deg, #4f46e5, #06b6d4);
    border-radius: 9999px;
    transition: width 0.35s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Step Wizard Sections */
.wizard-step-section {
    display: none;
    animation: fadeInStep 0.3s ease-in-out;
}
.wizard-step-section.active {
    display: block;
}
@keyframes fadeInStep {
    from { opacity: 0; transform: translateY(6px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Form Controls & Inputs */
.label-clean {
    font-size: 0.92rem;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 0.5rem;
    display: block;
}
.label-clean .req {
    color: #ef4444;
    font-weight: 700;
    margin-left: 2px;
}

.input-clean {
    background: #ffffff;
    border: 1.5px solid #cbd5e1;
    color: #0f172a;
    border-radius: 12px;
    padding: 0.8rem 1.1rem;
    font-size: 0.98rem;
    font-weight: 500;
    width: 100%;
    min-height: 48px;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}
.input-clean:focus {
    border-color: #4f46e5;
    outline: none;
    box-shadow: 0 0 0 3.5px rgba(79, 70, 229, 0.14);
}

/* Bio Textarea: Expansive & Comfortable */
.bio-clean-textarea {
    min-height: 180px;
    padding: 1rem 1.25rem;
    line-height: 1.8;
    font-size: 0.98rem;
    resize: vertical;
    border-radius: 14px;
}

/* Clean Country Code Dropdown */
.country-select-btn {
    background: #f8fafc;
    border: 1.5px solid #cbd5e1;
    border-right: none;
    color: #1e293b;
    border-radius: 12px 0 0 12px;
    padding: 0.8rem 1rem;
    display: flex;
    align-items: center;
    gap: 0.45rem;
    font-weight: 600;
    font-size: 0.94rem;
    cursor: pointer;
    white-space: nowrap;
    min-height: 48px;
    transition: background 0.15s ease;
}
.country-select-btn:hover {
    background: #f1f5f9;
}
.phone-clean-input {
    border-radius: 0 12px 12px 0 !important;
}

.country-menu-clean {
    max-height: 320px;
    overflow-y: auto;
    background: #ffffff;
    border: 1px solid #cbd5e1;
    border-radius: 14px;
    padding: 0.6rem;
    box-shadow: 0 18px 40px rgba(0, 0, 0, 0.12);
    z-index: 1050;
    min-width: 320px;
}
.country-menu-clean::-webkit-scrollbar { width: 5px; }
.country-menu-clean::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
.country-search-clean {
    background: #f8fafc;
    border: 1.5px solid #cbd5e1;
    border-radius: 10px;
    color: #0f172a;
    padding: 0.55rem 0.85rem;
    font-size: 0.88rem;
    width: 100%;
    margin-bottom: 0.6rem;
}
.country-search-clean:focus { outline: none; border-color: #4f46e5; background: #ffffff; }
.country-row-btn {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    padding: 0.6rem 0.85rem;
    background: transparent;
    border: none;
    border-radius: 8px;
    color: #334155;
    text-align: left;
    font-size: 0.9rem;
    cursor: pointer;
    transition: background 0.15s ease;
}
.country-row-btn:hover {
    background: #eef2ff;
    color: #4f46e5;
}

/* Clean Genre Tags */
.genre-tag-check { display: none; }
.genre-tag-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.55rem 1rem;
    background: #f8fafc;
    border: 1.5px solid #e2e8f0;
    border-radius: 9999px;
    color: #475569;
    font-size: 0.88rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.15s ease;
    user-select: none;
}
.genre-tag-btn:hover {
    background: #f1f5f9;
    border-color: #cbd5e1;
    color: #0f172a;
}
.genre-tag-check:checked + .genre-tag-btn {
    background: #eef2ff;
    border-color: #6366f1;
    color: #4338ca;
    font-weight: 600;
    box-shadow: 0 2px 8px rgba(99, 102, 241, 0.18);
}

/* Avatar Upload Clean Box */
.avatar-clean-zone {
    display: flex;
    align-items: center;
    gap: 1.25rem;
    padding: 1.25rem;
    border: 2px dashed #cbd5e1;
    border-radius: 16px;
    background: #f8fafc;
    cursor: pointer;
    transition: all 0.2s ease;
}
.avatar-clean-zone:hover {
    border-color: #6366f1;
    background: #eef2ff;
}
.avatar-circle-preview {
    width: 68px;
    height: 68px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #6366f1;
    background: #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #64748b;
    font-size: 1.6rem;
    flex-shrink: 0;
    overflow: hidden;
}

/* Payout Clean Radio Cards with High-Contrast Active State */
.payout-card-option {
    border: 1.5px solid #cbd5e1;
    border-radius: 14px;
    padding: 1rem 0.75rem;
    text-align: center;
    cursor: pointer;
    background: #ffffff;
    transition: all 0.2s ease;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    position: relative;
    user-select: none;
    height: 100%;
}
.payout-card-option:hover {
    background: #f8fafc;
    border-color: #94a3b8;
    transform: translateY(-2px);
}
.payout-card-option.active {
    border-color: #4f46e5 !important;
    background: #eef2ff !important;
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.18) !important;
}
.payout-card-option .payout-check-icon {
    position: absolute;
    top: 8px;
    right: 8px;
    font-size: 0.85rem;
    color: #4f46e5;
    display: none;
}
.payout-card-option.active .payout-check-icon {
    display: block;
}

/* Password Strength Bar */
.pwd-meter-track {
    height: 5px;
    background: #e2e8f0;
    border-radius: 9999px;
    overflow: hidden;
    margin-top: 0.5rem;
}
.pwd-meter-fill {
    height: 100%;
    width: 0%;
    transition: width 0.3s ease, background-color 0.3s ease;
}

/* Wizard Buttons */
.btn-wizard-next {
    background: #4f46e5;
    color: #ffffff;
    font-weight: 700;
    padding: 0.85rem 1.85rem;
    border-radius: 12px;
    border: none;
    display: inline-flex;
    align-items: center;
    gap: 0.6rem;
    transition: all 0.2s ease;
    cursor: pointer;
    min-height: 48px;
}
.btn-wizard-next:hover {
    background: #4338ca;
    color: #ffffff;
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(79, 70, 229, 0.3);
}

.btn-wizard-prev {
    background: #f1f5f9;
    color: #475569;
    font-weight: 600;
    padding: 0.85rem 1.6rem;
    border-radius: 12px;
    border: 1.5px solid #cbd5e1;
    display: inline-flex;
    align-items: center;
    gap: 0.6rem;
    transition: all 0.2s ease;
    cursor: pointer;
    min-height: 48px;
}
.btn-wizard-prev:hover {
    background: #e2e8f0;
    color: #0f172a;
}

@media (max-width: 768px) {
    .author-wizard-card {
        padding: 1.75rem 1.25rem;
    }
    .author-info-card {
        padding: 1.75rem 1.25rem;
    }
}
</style>
@endpush

@section('content')
<div class="author-reg-page">
    <div class="container">
        <!-- Breadcrumb Navigation -->
        <nav aria-label="breadcrumb" class="auth-breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}"><i class="fas fa-home me-1"></i>হোম</a></li>
                <li class="breadcrumb-item"><a href="{{ url('/authors') }}">লেখক কর্নার</a></li>
                <li class="breadcrumb-item active text-dark fw-semibold" aria-current="page">লেখক নিবন্ধন</li>
            </ol>
        </nav>

        <!-- Flash Alert Messages -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4 p-3" style="background: #ecfdf5; color: #065f46; border-left: 5px solid #10b981 !important;">
                <i class="fas fa-check-circle me-2 text-success fs-5"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4 p-3" style="background: #fef2f2; color: #991b1b; border-left: 5px solid #ef4444 !important;">
                <i class="fas fa-exclamation-triangle me-2 text-danger fs-5"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if(isset($errors) && $errors->any())
            <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4 p-3.5" style="background: #fef2f2; color: #991b1b; border-left: 5px solid #ef4444 !important;">
                <div class="fw-bold mb-1.5"><i class="fas fa-exclamation-circle me-1 fs-5"></i> অনুগ্রহ করে নিচের তথ্যগুলো সংশোধন করুন:</div>
                <ul class="mb-0 ps-3 small">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row g-4 g-lg-5 align-items-stretch">
            <!-- Left Column: Author Benefits & Dynamic Royalty Calculator -->
            <div class="col-lg-5">
                <div class="author-info-card d-flex flex-column justify-content-between">
                    <div>
                        <!-- Header Badge -->
                        <span class="badge-prestige">
                            <i class="fas fa-feather-pointed"></i> আইডিয়া লেখক সম্প্রদায়
                        </span>
                        <h1 class="h3 fw-bold text-dark mb-2">আপনার রচনা পৌঁছে যাক বিশ্বমঞ্চে</h1>
                        <p class="text-secondary small mb-4" style="line-height: 1.75;">
                            আইডিয়া প্রকাশন ও ডিজিটাল লাইব্রেরির মাধ্যমে আপনার মৌলিক সাহিত্য, কবিতা, উপন্যাস ও গবেষণাধর্মী ই-বুক প্রকাশ করুন আকর্ষণীয় রয়্যালটি সুবিধায়।
                        </p>

                        <!-- Key Benefits List -->
                        <div class="benefit-card-clean">
                            <div class="benefit-icon-clean" style="background: #ecfdf5; color: #059669;">
                                <i class="fas fa-hand-holding-dollar"></i>
                            </div>
                            <div>
                                <h6 class="text-dark fw-bold mb-1" style="font-size: 0.96rem;">৫০%+ আকর্ষণীয় রয়্যালটি</h6>
                                <p class="text-secondary small mb-0">ডিজিটাল ই-বুক ও মুদ্রিত বই বিক্রির ওপর নিয়মিত ও স্বচ্ছ রয়্যালটি অর্জন করুন।</p>
                            </div>
                        </div>

                        <div class="benefit-card-clean">
                            <div class="benefit-icon-clean" style="background: #e0f2fe; color: #0284c7;">
                                <i class="fas fa-shield-halved"></i>
                            </div>
                            <div>
                                <h6 class="text-dark fw-bold mb-1" style="font-size: 0.96rem;">কপিরাইট ও ডিআরএম সুরক্ষা</h6>
                                <p class="text-secondary small mb-0">পাইরেসি প্রতিরোধক এনক্রিপ্টেড ওয়াটারমার্ক এবং আপনার স্বত্বাধিকারের শতভাগ নিশ্চয়তা।</p>
                            </div>
                        </div>

                        <div class="benefit-card-clean">
                            <div class="benefit-icon-clean" style="background: #fef3c7; color: #d97706;">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <div>
                                <h6 class="text-dark fw-bold mb-1" style="font-size: 0.96rem;">স্বতন্ত্র লেখক ড্যাশবোর্ড</h6>
                                <p class="text-secondary small mb-0">রিয়েলটাইম রিডার অ্যানালিটিক্স, রেটিং, বিক্রি ট্র্যাকিং এবং পেমেন্ট উইথড্র সুবিধা।</p>
                            </div>
                        </div>

                        <!-- Dynamic Royalty Calculator -->
                        <div class="calc-card-clean">
                            <div class="d-flex justify-content-between align-items-center mb-2.5">
                                <span class="fw-bold text-dark small"><i class="fas fa-calculator text-primary me-1"></i> রয়্যালটি আয়ের হিসাব</span>
                                <span class="badge bg-primary-subtle text-primary rounded-pill px-2.5 py-1" style="font-size: 0.75rem;">৫০% রয়্যালটি মডেল</span>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between text-secondary small mb-1.5">
                                    <span>মাসিক আনুমানিক কপি বিক্রি:</span>
                                    <strong class="text-dark fw-bold" id="calcCopyDisplay">১০০ কপি</strong>
                                </div>
                                <input type="range" class="form-range" id="calcSlider" min="10" max="1000" step="10" value="100" oninput="updateRoyaltyCalc(this.value)">
                            </div>
                            <div class="d-flex justify-content-between align-items-center pt-2.5 border-top">
                                <span class="text-secondary small">আপনার সম্ভাব্য মাসিক আয়:</span>
                                <span class="calc-earnings-clean" id="calcEarningsDisplay">৳১২,৫০০</span>
                            </div>
                        </div>
                    </div>

                    <!-- Bottom Login Link -->
                    <div class="mt-4 pt-3.5 border-top text-center">
                        <span class="text-secondary small">ইতিমধ্যে নিবন্ধিত লেখক?</span>
                        <a href="{{ route('login') }}" class="text-primary fw-bold text-decoration-none ms-1 small">সরাসরি লগইন করুন &rarr;</a>
                    </div>
                </div>
            </div>

            <!-- Right Column: Smart Multi-Step Wizard Form -->
            <div class="col-lg-7">
                <div class="author-wizard-card">
                    <!-- Step Progress Header -->
                    <div class="stepper-header">
                        <div class="stepper-steps-row">
                            <div class="stepper-step-item active" id="stepIndicator1" onclick="goToStep(1)">
                                <span class="stepper-bubble">১</span>
                                <span>মৌলিক তথ্য</span>
                            </div>
                            <i class="fas fa-chevron-right text-muted small opacity-50"></i>
                            <div class="stepper-step-item" id="stepIndicator2" onclick="goToStep(2)">
                                <span class="stepper-bubble">২</span>
                                <span>সাহিত্যধারা ও পরিচিতি</span>
                            </div>
                            <i class="fas fa-chevron-right text-muted small opacity-50"></i>
                            <div class="stepper-step-item" id="stepIndicator3" onclick="goToStep(3)">
                                <span class="stepper-bubble">৩</span>
                                <span>সিকিউরিটি ও রয়্যালটি</span>
                            </div>
                        </div>
                        <div class="stepper-track-bar">
                            <div class="stepper-track-fill" id="stepperProgressBar"></div>
                        </div>
                    </div>

                    <!-- Main Form -->
                    <form action="{{ route('author.store-registration') }}" method="POST" enctype="multipart/form-data" id="authorRegistrationForm">
                        @csrf
                        <!-- Anti-bot honeypot -->
                        <input type="text" name="author_hp_field" style="display:none !important;" tabindex="-1" autocomplete="off">
                        <input type="hidden" name="country_code" id="hiddenCountryCode" value="{{ old('country_code', '+880') }}">

                        <!-- ================= STEP 1: PERSONAL & CONTACT ================= -->
                        <div class="wizard-step-section active" id="stepSection1">
                            <div class="d-flex align-items-center justify-content-between mb-4 pb-2.5 border-bottom">
                                <h5 class="fw-bold text-dark mb-0">
                                    <i class="fas fa-user-pen text-primary me-2"></i> লেখকের প্রাথমিক ও যোগাযোগের তথ্য
                                </h5>
                                <span class="badge bg-light text-secondary border px-2.5 py-1">ধাপ ১ / ৩</span>
                            </div>

                            <!-- Row 1: Bangla Name & English Name -->
                            <div class="row g-3.5 mb-3.5">
                                <div class="col-md-6">
                                    <label class="label-clean">লেখকের নাম (বাংলা) <span class="req">*</span></label>
                                    <input type="text" name="name" id="authorNameInput" required value="{{ old('name', auth()->user()?->name) }}" class="input-clean" placeholder="যেমন: অমরেশ দত্ত" oninput="autoSuggestSlug(this.value)">
                                </div>

                                <div class="col-md-6">
                                    <label class="label-clean">লেখকের নাম (ইংরেজি) <span class="req">*</span></label>
                                    <input type="text" name="name_en" id="authorNameEnInput" required value="{{ old('name_en') }}" class="input-clean" placeholder="e.g. Amaresh Datta">
                                </div>
                            </div>

                            <!-- Row 2: Email & Phone with All-Country Selector -->
                            <div class="row g-3.5 mb-3.5">
                                <div class="col-md-6">
                                    <label class="label-clean">সচল ইমেইল <span class="req">*</span></label>
                                    <input type="email" name="email" id="authorEmailInput" required value="{{ old('email', auth()->user()?->email) }}" class="input-clean" placeholder="author@example.com">
                                </div>

                                <div class="col-md-6">
                                    <label class="label-clean">মোবাইল নম্বর <span class="req">*</span></label>
                                    <div class="input-group">
                                        <!-- Country Selector Button -->
                                        <div class="dropdown">
                                            <button class="country-select-btn dropdown-toggle" type="button" id="countryDropdownBtn" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                                                <span id="selectedFlag">🇧🇩</span>
                                                <span id="selectedDialCode">+880</span>
                                            </button>

                                            <!-- Searchable Dropdown Menu -->
                                            <div class="dropdown-menu country-menu-clean shadow-lg" aria-labelledby="countryDropdownBtn">
                                                <input type="text" class="country-search-clean" id="countrySearchBox" placeholder="🔍 দেশ বা ডায়াল কোড খুঁজুন..." oninput="filterCountries(this.value)" autocomplete="off">

                                                <div id="countriesListContainer">
                                                    @foreach($countries as $c)
                                                        <button type="button" class="country-row-btn" data-name="{{ $c['name'] }} {{ $c['name_en'] }} {{ $c['dial_code'] }}" onclick="selectCountry('{{ $c['code'] }}', '{{ $c['dial_code'] }}', '{{ $c['flag'] }}')">
                                                            <span>{{ $c['flag'] }} {{ $c['name'] }} <small class="text-muted">({{ $c['name_en'] }})</small></span>
                                                            <span class="badge bg-light text-dark fw-bold border">{{ $c['dial_code'] }}</span>
                                                        </button>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Phone Number Input -->
                                        <input type="tel" name="phone" id="phoneInput" required value="{{ old('phone', auth()->user()?->phone) }}" class="input-clean phone-clean-input" placeholder="017XXXXXXXX">
                                    </div>
                                    <small class="text-muted mt-1 d-block" style="font-size: 0.78rem;">বিশ্বের যেকোনো দেশের নম্বর সাপোর্ট করে।</small>
                                </div>
                            </div>

                            <!-- Row 3: Pen Name & Profile Permalink -->
                            <div class="row g-3.5 mb-4">
                                <div class="col-md-6">
                                    <label class="label-clean">ছদ্মনাম / পরিচিত নাম (ঐচ্ছিক)</label>
                                    <input type="text" name="pen_name" value="{{ old('pen_name') }}" class="input-clean" placeholder="যেমন: নীলকণ্ঠ">
                                </div>
                                <div class="col-md-6">
                                    <label class="label-clean">প্রোফাইল পারমালিঙ্ক (Auto-Generated)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0 text-muted" style="font-size: 0.85rem; border-radius: 12px 0 0 12px;">ideaabd.com/authors/</span>
                                        <input type="text" name="slug_preview" id="authorSlugInput" value="{{ old('name_en') ? Str::slug(old('name_en')) : '' }}" class="input-clean" style="border-radius: 0 12px 12px 0;" placeholder="amaresh-datta" readonly>
                                    </div>
                                </div>
                            </div>

                            <!-- Step 1 Footer Action -->
                            <div class="d-flex justify-content-end pt-3.5 border-top">
                                <button type="button" class="btn-wizard-next" onclick="validateAndGoToStep(2)">
                                    <span>পরবর্তী ধাপ</span>
                                    <i class="fas fa-arrow-right"></i>
                                </button>
                            </div>
                        </div>

                        <!-- ================= STEP 2: LITERARY GENRES & BIO ================= -->
                        <div class="wizard-step-section" id="stepSection2">
                            <div class="d-flex align-items-center justify-content-between mb-4 pb-2.5 border-bottom">
                                <h5 class="fw-bold text-dark mb-0">
                                    <i class="fas fa-feather text-primary me-2"></i> সাহিত্যধারা ও লেখক পরিচিতি
                                </h5>
                                <span class="badge bg-light text-secondary border px-2.5 py-1">ধাপ ২ / ৩</span>
                            </div>

                            <!-- Literary Genres Multi-Select Chips -->
                            <div class="mb-4">
                                <label class="label-clean">আপনার লেখার প্রিয় ক্ষেত্র / সাহিত্য শাখা (একাধিক নির্বাচনযোগ্য):</label>
                                <div class="d-flex flex-wrap gap-2 pt-1">
                                    @foreach($genresList as $g)
                                        @php $slugGenre = Str::slug($g); @endphp
                                        <input type="checkbox" name="genres[]" value="{{ $g }}" id="genre_{{ $slugGenre }}" class="genre-tag-check" {{ is_array(old('genres')) && in_array($g, old('genres')) ? 'checked' : '' }}>
                                        <label for="genre_{{ $slugGenre }}" class="genre-tag-btn">
                                            <i class="fas fa-check-circle small opacity-50"></i> {{ $g }}
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Avatar Upload with Live Preview -->
                            <div class="mb-4">
                                <label class="label-clean">লেখকের প্রোফাইল ছবি / পোর্ট্রেট</label>
                                <div class="avatar-clean-zone" onclick="document.getElementById('avatarFileInput').click()">
                                    <div class="avatar-circle-preview" id="avatarPreviewContainer">
                                        <i class="fas fa-camera"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="fw-bold text-dark mb-1" style="font-size: 0.95rem;">ছবি আপলোড করতে এখানে ক্লিক করুন</div>
                                        <small class="text-muted d-block" style="font-size: 0.8rem;">JPEG, PNG বা WebP ফরম্যাট (সর্বোচ্চ ৫ মেগাবাইট)</small>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-secondary px-3.5 py-1.5 rounded-pill">বাছাই করুন</button>
                                </div>
                                <input type="file" name="avatar" id="avatarFileInput" accept="image/*" style="display: none;" onchange="previewAvatarImage(this)">
                            </div>

                            <!-- Bio / About Author: Expansive Writing Box -->
                            <div class="mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-1.5">
                                    <label class="label-clean mb-0">সংক্ষিপ্ত লেখক পরিচিতি / জীবনী <span class="text-muted fw-normal font-monospace">(বিস্তৃত লেখার বক্স)</span></label>
                                    <span class="badge bg-light text-secondary border font-monospace" id="bioCharCount">০ / ৫০০০</span>
                                </div>
                                <textarea name="bio" rows="6" class="input-clean bio-clean-textarea" placeholder="আপনার সাহিত্য জীবনের পথচলা, প্রকাশিত বই, উল্লেখযোগ্য অর্জন, প্রিয় সাহিত্যধারা বা সাহিত্যদর্শন সম্পর্কে বিশদ লিখুন..." maxlength="5000" oninput="updateBioCount(this)">{{ old('bio') }}</textarea>
                                <small class="text-muted mt-1 d-block" style="font-size: 0.78rem;">অনুকূলিত বড় লেখার স্থান — এখানে ইচ্ছামতো দীর্ঘ অনুচ্ছেদ লিখতে পারেন।</small>
                            </div>

                            <!-- Social / Website Links -->
                            <div class="row g-3.5 mb-4">
                                <div class="col-md-6">
                                    <label class="label-clean">ফেসবুক প্রোফাইল / পেইজ লিংক (ঐচ্ছিক)</label>
                                    <input type="url" name="facebook" value="{{ old('facebook') }}" class="input-clean" placeholder="https://facebook.com/username">
                                </div>
                                <div class="col-md-6">
                                    <label class="label-clean">ব্যক্তিগত ওয়েবসাইট / ব্লগ (ঐচ্ছিক)</label>
                                    <input type="url" name="website" value="{{ old('website') }}" class="input-clean" placeholder="https://mywebsite.com">
                                </div>
                            </div>

                            <!-- Step 2 Footer Action -->
                            <div class="d-flex justify-content-between align-items-center pt-3.5 border-top">
                                <button type="button" class="btn-wizard-prev" onclick="goToStep(1)">
                                    <i class="fas fa-arrow-left"></i>
                                    <span>পূর্ববর্তী ধাপ</span>
                                </button>
                                <button type="button" class="btn-wizard-next" onclick="goToStep(3)">
                                    <span>পরবর্তী ধাপ</span>
                                    <i class="fas fa-arrow-right"></i>
                                </button>
                            </div>
                        </div>

                        <!-- ================= STEP 3: SECURITY & PAYOUT ================= -->
                        <div class="wizard-step-section" id="stepSection3">
                            <div class="d-flex align-items-center justify-content-between mb-4 pb-2.5 border-bottom">
                                <h5 class="fw-bold text-dark mb-0">
                                    <i class="fas fa-lock text-primary me-2"></i> সিকিউরিটি ও রয়্যালটি সেটিংস
                                </h5>
                                <span class="badge bg-light text-secondary border px-2.5 py-1">ধাপ ৩ / ৩</span>
                            </div>

                            <!-- Password (for guest authors) -->
                            @guest
                                <div class="mb-4">
                                    <label class="label-clean">ড্যাশবোর্ড লগইন পাসওয়ার্ড <span class="req">*</span></label>
                                    <div class="position-relative">
                                        <input type="password" name="password" id="passwordInput" required class="input-clean pe-5" placeholder="কমপক্ষে ৬ অক্ষরের পাসওয়ার্ড দিন" oninput="checkPasswordStrength(this.value)">
                                        <button type="button" class="btn btn-link text-muted position-absolute end-0 top-50 translate-middle-y text-decoration-none me-2.5" onclick="togglePasswordVisibility('passwordInput', this)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <!-- Strength Meter -->
                                    <div class="pwd-meter-track">
                                        <div class="pwd-meter-fill" id="pwdStrengthFill"></div>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mt-1.5">
                                        <small class="text-muted" style="font-size: 0.78rem;">পাসওয়ার্ড নিরাপত্তা স্তর:</small>
                                        <small class="fw-bold text-muted" style="font-size: 0.78rem;" id="pwdStrengthText">প্রদান করুন</small>
                                    </div>
                                </div>
                            @endguest

                            <!-- Preferred Payout Method Cards with Interactive JS Switcher -->
                            <div class="mb-4">
                                <label class="label-clean">রয়্যালটি উত্তোলনের পছন্দের মাধ্যম:</label>
                                <input type="hidden" name="payout_account_type" id="payoutAccountTypeInput" value="bkash">

                                <div class="row g-2.5 pt-1 mb-3">
                                    <div class="col-6 col-sm-3">
                                        <div class="payout-card-option active" id="payoutCard_bkash" onclick="selectPayoutMethod('bkash')">
                                            <i class="fas fa-check-circle payout-check-icon"></i>
                                            <div class="fw-bold text-danger mb-1"><i class="fas fa-mobile-screen fs-4"></i></div>
                                            <span class="small fw-bold text-dark">বিকাশ</span>
                                        </div>
                                    </div>
                                    <div class="col-6 col-sm-3">
                                        <div class="payout-card-option" id="payoutCard_nagad" onclick="selectPayoutMethod('nagad')">
                                            <i class="fas fa-check-circle payout-check-icon"></i>
                                            <div class="fw-bold text-warning mb-1"><i class="fas fa-wallet fs-4"></i></div>
                                            <span class="small fw-bold text-dark">নগদ</span>
                                        </div>
                                    </div>
                                    <div class="col-6 col-sm-3">
                                        <div class="payout-card-option" id="payoutCard_rocket" onclick="selectPayoutMethod('rocket')">
                                            <i class="fas fa-check-circle payout-check-icon"></i>
                                            <div class="fw-bold text-info mb-1"><i class="fas fa-money-bill-wave fs-4"></i></div>
                                            <span class="small fw-bold text-dark">রকেট</span>
                                        </div>
                                    </div>
                                    <div class="col-6 col-sm-3">
                                        <div class="payout-card-option" id="payoutCard_bank" onclick="selectPayoutMethod('bank')">
                                            <i class="fas fa-check-circle payout-check-icon"></i>
                                            <div class="fw-bold text-success mb-1"><i class="fas fa-building-columns fs-4"></i></div>
                                            <span class="small fw-bold text-dark">ব্যাংক একাউন্ট</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-2">
                                    <label class="label-clean" id="payoutDetailsLabel">বিকাশ নম্বর (ব্যক্তিগত / মার্চেন্ট):</label>
                                    <input type="text" name="payout_account_details" id="payoutAccountDetailsInput" value="{{ old('payout_account_details') }}" class="input-clean" placeholder="যেমন: 017XXXXXXXX">
                                </div>
                            </div>

                            <!-- Terms & Conditions Consent -->
                            <div class="mb-4 p-3.5 rounded-4 bg-light border">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="terms_agreed" id="termsCheck" required value="1" {{ old('terms_agreed') ? 'checked' : '' }}>
                                    <label class="form-check-label text-dark small" for="termsCheck">
                                        আমি আইডিয়া প্রকাশনের <a href="javascript:void(0)" class="text-primary fw-bold text-decoration-underline" data-bs-toggle="modal" data-bs-target="#termsModal">লেখক নীতিমালা, রয়্যালটি চুক্তি ও শর্তাবলী</a> পড়েছি এবং এতে সম্মতি জানাচ্ছি। <span class="req">*</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Step 3 Footer Action -->
                            <div class="d-flex justify-content-between align-items-center pt-3.5 border-top">
                                <button type="button" class="btn-wizard-prev" onclick="goToStep(2)">
                                    <i class="fas fa-arrow-left"></i>
                                    <span>পূর্ববর্তী ধাপ</span>
                                </button>
                                <button type="submit" class="btn-wizard-next px-4" id="btnSubmitAuthorReg">
                                    <i class="fas fa-check-circle"></i>
                                    <span>নিবন্ধন সম্পন্ন করুন</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- =============================================================
     MODAL: Terms of Publication & Royalty Agreement
============================================================= -->
<div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 rounded-4 shadow-lg bg-white">
            <div class="modal-header border-bottom px-4 py-3 bg-light">
                <h5 class="modal-title fw-bold text-dark" id="termsModalLabel">
                    <i class="fas fa-file-contract text-primary me-2"></i> আইডিয়া প্রকাশন লেখক নীতিমালা ও রয়্যালটি চুক্তি
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 text-secondary" style="line-height: 1.85; font-size: 0.96rem;">
                <h6 class="text-dark fw-bold mb-2">১. স্বত্বাধিকার ও মৌলিকত্ব</h6>
                <p>আইডিয়া প্রকাশনে প্রকাশিত সকল পাণ্ডুলিপি ও রচনার কপিরাইট এবং মেধাস্বত্ব সম্পূর্ণভাবে লেখকের অনুকূলে সংরক্ষিত থাকবে। লেখক নিশ্চিত করবেন যে তাঁর প্রদানকৃত রচনাটি সম্পূর্ণ মৌলিক এবং কোনো প্রকার কপিরাইট লঙ্ঘন করে না।</p>

                <h6 class="text-dark fw-bold mb-2">২. রয়্যালটি ও পেমেন্ট পলিসি</h6>
                <p>ডিজিটাল ই-বুক ও মুদ্রিত বইয়ের ক্ষেত্রে নির্ধারিত রয়্যালটি (৫০%+ পর্যন্ত) স্বয়ংক্রিয়ভাবে লেখকের ড্যাশবোর্ড ওয়ালেটে জমা হবে। লেখক যেকোনো সময় বিকাশ, নগদ, রকেট বা ব্যাংক অ্যাকাউন্টের মাধ্যমে উইথড্র রিকোয়েস্ট পাঠাতে পারবেন।</p>

                <h6 class="text-dark fw-bold mb-2">৩. ডিজিটাল অধিকার সুরক্ষা (DRM)</h6>
                <p>আইডিয়া প্ল্যাটফর্মে প্রকাশিত ই-বুকগুলো উন্নত এনক্রিপশন ও ডিজিটাল ওয়াটারমার্কের মাধ্যমে সুরক্ষিত থাকবে যাতে অনুমোদনহীন পাইরেসি রোধ করা যায়।</p>

                <h6 class="text-dark fw-bold mb-2">৪. একাউন্ট পরিচালনা ও নিরাপত্তা</h6>
                <p>লেখক তাঁর ড্যাশবোর্ডের মাধ্যমে যেকোনো সময় নতুন বই আপলোড, লেখার খসড়া সংরক্ষণ এবং রিডারদের সাথে যুক্ত থাকতে পারবেন।</p>
            </div>
            <div class="modal-footer border-top px-4 bg-light">
                <button type="button" class="btn btn-primary btn-sm px-4" data-bs-dismiss="modal" onclick="document.getElementById('termsCheck').checked = true;">আমি সম্মত</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// --- 1. Multi-Step Wizard Navigation Engine ---
let currentStep = 1;

function goToStep(stepNumber) {
    document.querySelectorAll('.wizard-step-section').forEach(sec => sec.classList.remove('active'));
    
    const targetSection = document.getElementById('stepSection' + stepNumber);
    if (targetSection) {
        targetSection.classList.add('active');
    }

    currentStep = stepNumber;
    const progressFill = document.getElementById('stepperProgressBar');
    if (progressFill) {
        progressFill.style.width = (stepNumber === 1 ? '33.33%' : (stepNumber === 2 ? '66.66%' : '100%'));
    }

    for (let i = 1; i <= 3; i++) {
        const ind = document.getElementById('stepIndicator' + i);
        if (ind) {
            ind.classList.remove('active', 'completed');
            if (i < stepNumber) {
                ind.classList.add('completed');
                ind.querySelector('.stepper-bubble').innerHTML = '<i class="fas fa-check"></i>';
            } else if (i === stepNumber) {
                ind.classList.add('active');
                ind.querySelector('.stepper-bubble').innerText = i;
            } else {
                ind.querySelector('.stepper-bubble').innerText = i;
            }
        }
    }

    if (window.innerWidth < 768) {
        window.scrollTo({ top: document.querySelector('.author-wizard-card').offsetTop - 20, behavior: 'smooth' });
    }
}

function validateAndGoToStep(nextStep) {
    if (nextStep === 2) {
        const name = document.getElementById('authorNameInput');
        const nameEn = document.getElementById('authorNameEnInput');
        const email = document.getElementById('authorEmailInput');
        const phone = document.getElementById('phoneInput');

        if (!name.value.trim()) {
            name.focus();
            alert('অনুগ্রহ করে লেখকের নাম (বাংলা) লিখুন।');
            return;
        }
        if (!nameEn.value.trim()) {
            nameEn.focus();
            alert('অনুগ্রহ করে লেখকের নাম (ইংরেজি) লিখুন।');
            return;
        }
        if (!email.value.trim() || !email.checkValidity()) {
            email.focus();
            alert('সঠিক ইমেইল ঠিকানা প্রদান করুন।');
            return;
        }
        if (!phone.value.trim()) {
            phone.focus();
            alert('সচল মোবাইল নম্বর প্রদান করুন।');
            return;
        }
    }
    goToStep(nextStep);
}

// --- 2. Payout Method Interactive Selection Engine ---
function selectPayoutMethod(method) {
    document.getElementById('payoutAccountTypeInput').value = method;

    document.querySelectorAll('.payout-card-option').forEach(card => card.classList.remove('active'));
    const activeCard = document.getElementById('payoutCard_' + method);
    if (activeCard) {
        activeCard.classList.add('active');
    }

    const label = document.getElementById('payoutDetailsLabel');
    const input = document.getElementById('payoutAccountDetailsInput');

    if (method === 'bkash') {
        label.innerText = 'বিকাশ নম্বর (ব্যক্তিগত / মার্চেন্ট):';
        input.placeholder = 'যেমন: 017XXXXXXXX';
    } else if (method === 'nagad') {
        label.innerText = 'নগদ একাউন্ট নম্বর:';
        input.placeholder = 'যেমন: 018XXXXXXXX';
    } else if (method === 'rocket') {
        label.innerText = 'রকেট একাউন্ট নম্বর:';
        input.placeholder = 'যেমন: 019XXXXXXXX-X';
    } else if (method === 'bank') {
        label.innerText = 'ব্যাংক হিসাবের পূর্ণ বিবরণ:';
        input.placeholder = 'ব্যাংকের নাম, শাখা, একাউন্ট হোল্ডার ও একাউন্ট নম্বর';
    }
}

// --- 3. Dynamic Royalty Calculator Slider ---
function updateRoyaltyCalc(copies) {
    const copyNum = parseInt(copies);
    document.getElementById('calcCopyDisplay').innerText = copyNum.toLocaleString('bn-BD') + ' কপি';
    
    const avgPrice = 250;
    const royaltyRate = 0.50;
    const totalEarnings = Math.round(copyNum * avgPrice * royaltyRate);
    
    document.getElementById('calcEarningsDisplay').innerText = '৳' + totalEarnings.toLocaleString('bn-BD');
}

// --- 4. Country Selector and Search Filter ---
function selectCountry(code, dialCode, flag) {
    document.getElementById('hiddenCountryCode').value = dialCode;
    document.getElementById('selectedFlag').innerText = flag;
    document.getElementById('selectedDialCode').innerText = dialCode;
    
    const btn = document.getElementById('countryDropdownBtn');
    if (typeof bootstrap !== 'undefined' && btn) {
        const dropdown = bootstrap.Dropdown.getInstance(btn);
        if (dropdown) dropdown.hide();
    }
}

function filterCountries(searchTerm) {
    const term = searchTerm.toLowerCase().trim();
    const items = document.querySelectorAll('#countriesListContainer .country-row-btn');
    items.forEach(item => {
        const data = item.getAttribute('data-name').toLowerCase();
        if (data.includes(term)) {
            item.style.display = 'flex';
        } else {
            item.style.display = 'none';
        }
    });
}

// --- 5. Auto English Slug Generator from Bengali Name & English Input Sync ---
function autoSuggestSlug(name) {
    const nameEnInput = document.getElementById('authorNameEnInput');
    const slugInput = document.getElementById('authorSlugInput');
    
    let text = name.trim().toLowerCase();
    const dictionary = {
        'অ': 'a', 'আ': 'a', 'ই': 'i', 'ঈ': 'ee', 'উ': 'u', 'ঊ': 'oo', 'ঋ': 'ri', 'এ': 'e', 'ঐ': 'oi', 'ও': 'o', 'ঔ': 'ou',
        'ক': 'k', 'খ': 'kh', 'গ': 'g', 'ঘ': 'gh', 'ঙ': 'ng', 'চ': 'ch', 'ছ': 'chh', 'জ': 'j', 'ঝ': 'jh', 'ঞ': 'n',
        'ট': 't', 'ঠ': 'th', 'ড': 'd', 'ঢ': 'dh', 'ণ': 'n', 'ত': 't', 'থ': 'th', 'দ': 'd', 'ধ': 'dh', 'ন': 'n',
        'প': 'p', 'ফ': 'f', 'ব': 'b', 'ভ': 'bh', 'ম': 'm', 'য': 'z', 'র': 'r', 'ল': 'l', 'শ': 'sh', 'ষ': 'sh',
        'স': 's', 'হ': 'h', 'ড়': 'r', 'ঢ়': 'rh', 'য়': 'y', 'ৎ': 't', 'ং': 'ng', 'ঃ': 'h', 'ঁ': '',
        'া': 'a', 'ি': 'i', 'ী': 'ee', 'ু': 'u', 'ূ': 'oo', 'ৃ': 'ri', 'ে': 'e', 'ৈ': 'oi', 'ো': 'o', 'ৌ': 'ou',
        '্': '', '্য': 'y', '্র': 'r', '্ব': 'b'
    };

    let result = '';
    for (let char of text) {
        result += dictionary[char] !== undefined ? dictionary[char] : char;
    }

    result = result.replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
    if (result) {
        if (slugInput) slugInput.value = result;
        if (nameEnInput && (!nameEnInput.value || nameEnInput.dataset.auto === 'true')) {
            nameEnInput.value = result.split('-').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');
            nameEnInput.dataset.auto = 'true';
        }
    }
}

// --- 6. Avatar Image Live Preview ---
function previewAvatarImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function (e) {
            const container = document.getElementById('avatarPreviewContainer');
            container.innerHTML = `<img src="${e.target.result}" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">`;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// --- 7. Bio Character Counter ---
function updateBioCount(textarea) {
    const len = textarea.value.length;
    document.getElementById('bioCharCount').innerText = len.toLocaleString('bn-BD') + ' / ৫০০০';
}

// --- 8. Real-Time Password Strength Checker ---
function checkPasswordStrength(password) {
    const fill = document.getElementById('pwdStrengthFill');
    const text = document.getElementById('pwdStrengthText');
    if (!fill || !text) return;

    if (!password) {
        fill.style.width = '0%';
        text.innerText = 'প্রদান করুন';
        text.className = 'fw-bold text-muted small';
        return;
    }

    let score = 0;
    if (password.length >= 6) score += 25;
    if (password.length >= 8) score += 25;
    if (/[A-Z]/.test(password) || /[0-9]/.test(password)) score += 25;
    if (/[^A-Za-z0-9]/.test(password)) score += 25;

    fill.style.width = score + '%';
    if (score <= 25) {
        fill.style.backgroundColor = '#ef4444';
        text.innerText = 'খুবই দুর্বল';
        text.className = 'fw-bold text-danger small';
    } else if (score <= 50) {
        fill.style.backgroundColor = '#f59e0b';
        text.innerText = 'মাঝারি';
        text.className = 'fw-bold text-warning small';
    } else if (score <= 75) {
        fill.style.backgroundColor = '#0284c7';
        text.innerText = 'ভালো';
        text.className = 'fw-bold text-info small';
    } else {
        fill.style.backgroundColor = '#059669';
        text.innerText = 'অত্যন্ত শক্তিশালী';
        text.className = 'fw-bold text-success small';
    }
}

function togglePasswordVisibility(inputId, btn) {
    const input = document.getElementById(inputId);
    if (!input) return;
    if (input.type === 'password') {
        input.type = 'text';
        btn.innerHTML = '<i class="fas fa-eye-slash"></i>';
    } else {
        input.type = 'password';
        btn.innerHTML = '<i class="fas fa-eye"></i>';
    }
}
</script>
@endpush
