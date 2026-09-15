@extends('layouts.app')

@section('title', 'প্রকাশক হিসেবে নিবন্ধন করুন — আইডিয়া প্রকাশন ও ডিস্ট্রিবিউশন হাব')

@section('meta_description', 'আইডিয়া প্রকাশনের গ্লোবাল ডিস্ট্রিবিউশন নেটওয়ার্কে যুক্ত হন। আপনার মুদ্রিত বই ও ডিজিটাল ই-বুক বিশ্বজুড়ে লাখো পাঠকের কাছে পৌঁছে দিন।')

@push('head')
<style>
/* -------------------------------------------------------------
   CLEAN LIGHT-THEME PUBLISHER REGISTRATION (B2B SaaS Spec)
------------------------------------------------------------- */
:root {
    --pub-primary: #0284c7;
    --pub-primary-hover: #0369a1;
    --pub-accent: #4f46e5;
    --pub-success: #059669;
    --pub-bg-page: #f8fafc;
    --pub-card-bg: #ffffff;
    --pub-border-subtle: #e2e8f0;
    --pub-text-main: #0f172a;
    --pub-text-muted: #64748b;
}

.publisher-reg-page {
    background: linear-gradient(180deg, #f0f9ff 0%, #f8fafc 100%);
    color: var(--pub-text-main);
    min-height: 100vh;
    padding-top: 2rem;
    padding-bottom: 5rem;
    font-family: 'Hind Siliguri', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

/* Breadcrumb */
.pub-breadcrumb {
    font-size: 0.88rem;
    background: transparent;
    padding: 0;
    margin-bottom: 1.5rem;
}
.pub-breadcrumb a {
    color: #64748b;
    text-decoration: none;
}
.pub-breadcrumb a:hover {
    color: var(--pub-primary);
}

/* Left Showcase Card */
.publisher-info-card {
    background: #ffffff;
    border: 1px solid var(--pub-border-subtle);
    border-radius: 20px;
    padding: 2.25rem;
    box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.04), 0 1px 3px rgba(0, 0, 0, 0.02);
    height: 100%;
}

.badge-b2b-prestige {
    background: #e0f2fe;
    color: #0369a1;
    font-size: 0.82rem;
    font-weight: 700;
    padding: 0.4rem 0.9rem;
    border-radius: 9999px;
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    border: 1px solid #bae6fd;
    margin-bottom: 1rem;
}

.b2b-card-clean {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    padding: 1.1rem;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    margin-bottom: 1rem;
    transition: all 0.2s ease;
}
.b2b-card-clean:hover {
    background: #ffffff;
    border-color: #cbd5e1;
    transform: translateY(-2px);
    box-shadow: 0 8px 20px -4px rgba(0, 0, 0, 0.05);
}
.b2b-icon-clean {
    width: 42px;
    height: 42px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    flex-shrink: 0;
}

/* Dynamic B2B Calculator */
.calc-b2b-clean {
    background: linear-gradient(135deg, #f8fafc 0%, #f0f9ff 100%);
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 1.5rem;
    margin-top: 1.5rem;
}
.calc-turnover-clean {
    font-size: 2rem;
    font-weight: 800;
    color: #0284c7;
    line-height: 1;
}

/* Right Main Registration Wizard Card */
.publisher-wizard-card {
    background: #ffffff;
    border: 1px solid var(--pub-border-subtle);
    border-radius: 20px;
    padding: 2.25rem;
    box-shadow: 0 15px 35px -5px rgba(0, 0, 0, 0.05), 0 1px 3px rgba(0, 0, 0, 0.03);
}

/* Stepper Progress Bar Header */
.stepper-header {
    margin-bottom: 2rem;
}
.stepper-steps-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 0.75rem;
}
.stepper-step-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.88rem;
    font-weight: 600;
    color: #94a3b8;
    cursor: pointer;
    transition: color 0.2s ease;
}
.stepper-step-item.active {
    color: #0284c7;
    font-weight: 700;
}
.stepper-step-item.completed {
    color: #059669;
}
.stepper-bubble {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    background: #e2e8f0;
    color: #64748b;
    font-weight: 700;
    transition: all 0.2s ease;
}
.stepper-step-item.active .stepper-bubble {
    background: #0284c7;
    color: #ffffff;
    box-shadow: 0 4px 10px rgba(2, 132, 199, 0.3);
}
.stepper-step-item.completed .stepper-bubble {
    background: #059669;
    color: #ffffff;
}

.stepper-track-bar {
    height: 5px;
    background: #e2e8f0;
    border-radius: 9999px;
    overflow: hidden;
}
.stepper-track-fill {
    height: 100%;
    width: 33.33%;
    background: linear-gradient(90deg, #0284c7, #4f46e5);
    border-radius: 9999px;
    transition: width 0.35s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Step Wizard Sections (Controlled by JS) */
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
    font-size: 0.88rem;
    font-weight: 600;
    color: #334155;
    margin-bottom: 0.4rem;
    display: block;
}
.input-clean {
    background: #ffffff;
    border: 1px solid #cbd5e1;
    color: #0f172a;
    border-radius: 10px;
    padding: 0.75rem 1rem;
    font-size: 0.95rem;
    width: 100%;
    transition: all 0.2s ease;
}
.input-clean:focus {
    border-color: #0284c7;
    outline: none;
    box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.12);
}

/* Clean Country Code Dropdown */
.country-select-btn {
    background: #f8fafc;
    border: 1px solid #cbd5e1;
    border-right: none;
    color: #1e293b;
    border-radius: 10px 0 0 10px;
    padding: 0.75rem 0.9rem;
    display: flex;
    align-items: center;
    gap: 0.4rem;
    font-weight: 600;
    font-size: 0.92rem;
    cursor: pointer;
    white-space: nowrap;
    transition: background 0.15s ease;
}
.country-select-btn:hover { background: #f1f5f9; }
.phone-clean-input { border-radius: 0 10px 10px 0 !important; }

.country-menu-clean {
    max-height: 300px;
    overflow-y: auto;
    background: #ffffff;
    border: 1px solid #cbd5e1;
    border-radius: 12px;
    padding: 0.5rem;
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.12);
    z-index: 1050;
    min-width: 310px;
}
.country-menu-clean::-webkit-scrollbar { width: 5px; }
.country-menu-clean::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
.country-search-clean {
    background: #f8fafc;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    color: #0f172a;
    padding: 0.45rem 0.75rem;
    font-size: 0.85rem;
    width: 100%;
    margin-bottom: 0.5rem;
}
.country-search-clean:focus { outline: none; border-color: #0284c7; background: #ffffff; }
.country-row-btn {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    padding: 0.55rem 0.75rem;
    background: transparent;
    border: none;
    border-radius: 8px;
    color: #334155;
    text-align: left;
    font-size: 0.88rem;
    cursor: pointer;
    transition: background 0.15s ease;
}
.country-row-btn:hover {
    background: #e0f2fe;
    color: #0284c7;
}

/* Category Chips */
.cat-tag-check { display: none; }
.cat-tag-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.45rem 0.85rem;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 9999px;
    color: #475569;
    font-size: 0.84rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.15s ease;
    user-select: none;
}
.cat-tag-btn:hover {
    background: #f1f5f9;
    border-color: #cbd5e1;
    color: #0f172a;
}
.cat-tag-check:checked + .cat-tag-btn {
    background: #e0f2fe;
    border-color: #0284c7;
    color: #0369a1;
    font-weight: 600;
    box-shadow: 0 2px 6px rgba(2, 132, 199, 0.15);
}

/* Logo Upload Clean Box */
.logo-clean-zone {
    display: flex;
    align-items: center;
    gap: 1.25rem;
    padding: 1rem;
    border: 2px dashed #cbd5e1;
    border-radius: 14px;
    background: #f8fafc;
    cursor: pointer;
    transition: all 0.2s ease;
}
.logo-clean-zone:hover {
    border-color: #0284c7;
    background: #f0f9ff;
}
.logo-box-preview {
    width: 64px;
    height: 64px;
    border-radius: 12px;
    object-fit: cover;
    border: 2px solid #0284c7;
    background: #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #64748b;
    font-size: 1.5rem;
    flex-shrink: 0;
    overflow: hidden;
}

/* Password Strength Bar */
.pwd-meter-track {
    height: 4px;
    background: #e2e8f0;
    border-radius: 9999px;
    overflow: hidden;
    margin-top: 0.4rem;
}
.pwd-meter-fill {
    height: 100%;
    width: 0%;
    transition: width 0.3s ease, background-color 0.3s ease;
}

/* Wizard Buttons */
.btn-pub-wizard-next {
    background: #0284c7;
    color: #ffffff;
    font-weight: 700;
    padding: 0.85rem 1.75rem;
    border-radius: 10px;
    border: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.2s ease;
    cursor: pointer;
}
.btn-pub-wizard-next:hover {
    background: #0369a1;
    color: #ffffff;
    transform: translateY(-1px);
    box-shadow: 0 6px 16px rgba(2, 132, 199, 0.3);
}

.btn-pub-wizard-prev {
    background: #f1f5f9;
    color: #475569;
    font-weight: 600;
    padding: 0.85rem 1.5rem;
    border-radius: 10px;
    border: 1px solid #cbd5e1;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.2s ease;
    cursor: pointer;
}
.btn-pub-wizard-prev:hover {
    background: #e2e8f0;
    color: #0f172a;
}
</style>
@endpush

@section('content')
<div class="publisher-reg-page">
    <div class="container">
        <!-- Breadcrumb Navigation -->
        <nav aria-label="breadcrumb" class="pub-breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}"><i class="fas fa-home me-1"></i>হোম</a></li>
                <li class="breadcrumb-item"><a href="{{ url('/publishers') }}">প্রকাশনী কর্নার</a></li>
                <li class="breadcrumb-item active text-dark fw-semibold" aria-current="page">প্রকাশক নিবন্ধন</li>
            </ol>
        </nav>

        <!-- Flash Alert Messages -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-3 border-0 shadow-sm mb-4" style="background: #ecfdf5; color: #065f46; border-left: 4px solid #10b981 !important;">
                <i class="fas fa-check-circle me-2 text-success"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show rounded-3 border-0 shadow-sm mb-4" style="background: #fef2f2; color: #991b1b; border-left: 4px solid #ef4444 !important;">
                <i class="fas fa-exclamation-triangle me-2 text-danger"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if(isset($errors) && $errors->any())
            <div class="alert alert-danger alert-dismissible fade show rounded-3 border-0 shadow-sm mb-4" style="background: #fef2f2; color: #991b1b; border-left: 4px solid #ef4444 !important;">
                <div class="fw-bold mb-1"><i class="fas fa-exclamation-circle me-1"></i> অনুগ্রহ করে নিচের তথ্যগুলো সংশোধন করুন:</div>
                <ul class="mb-0 ps-3 small">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row g-4 g-lg-5 align-items-stretch">
            <!-- Left Column: Publisher Benefits & Dynamic B2B Sales Calculator -->
            <div class="col-lg-5">
                <div class="publisher-info-card d-flex flex-column justify-content-between">
                    <div>
                        <!-- Header Badge -->
                        <span class="badge-b2b-prestige">
                            <i class="fas fa-building-columns"></i> প্রকাশনী ও পরিবেশক পার্টনারশিপ
                        </span>
                        <h1 class="h3 fw-bold text-dark mb-2">আপনার প্রকাশনার বই ছড়িয়ে দিন বিশ্বজুড়ে</h1>
                        <p class="text-secondary small mb-4" style="line-height: 1.7;">
                            আইডিয়া প্ল্যাটফর্মের মাধ্যমে আপনার মুদ্রিত বই ও ডিজিটাল ই-বুক ক্যাটালগ পরিবেশন করুন। লাইভ ইনভেন্টরি, আজকের ক্রয় চালান ও নিয়মিত বিল নিষ্পত্তি সুবিধা।
                        </p>

                        <!-- Key B2B Benefits List -->
                        <div class="b2b-card-clean">
                            <div class="b2b-icon-clean" style="background: #e0f2fe; color: #0284c7;">
                                <i class="fas fa-globe"></i>
                            </div>
                            <div>
                                <h6 class="text-dark fw-bold mb-1" style="font-size: 0.95rem;">গ্লোবাল ই-বুক ও প্রিন্ট ডিস্ট্রিবিউশন</h6>
                                <p class="text-secondary small mb-0">মুদ্রিত বইয়ের পাশাপাশি আন্তর্জাতিকমানের EPUB ও PDF ই-বুক রূপান্তর ও বিপণন।</p>
                            </div>
                        </div>

                        <div class="b2b-card-clean">
                            <div class="b2b-icon-clean" style="background: #ecfdf5; color: #059669;">
                                <i class="fas fa-boxes-stacked"></i>
                            </div>
                            <div>
                                <h6 class="text-dark fw-bold mb-1" style="font-size: 0.95rem;">সেন্ট্রালাইজড কোম্পানি ড্যাশবোর্ড</h6>
                                <p class="text-secondary small mb-0">বইয়ের স্টক ম্যানেজমেন্ট, আজকের ক্রয় চালান ও সেলস রিপোর্ট রিয়েলটাইমে পরিচালনা করুন।</p>
                            </div>
                        </div>

                        <div class="b2b-card-clean">
                            <div class="b2b-icon-clean" style="background: #eef2ff; color: #4f46e5;">
                                <i class="fas fa-money-bill-transfer"></i>
                            </div>
                            <div>
                                <h6 class="text-dark fw-bold mb-1" style="font-size: 0.95rem;">দ্রুত পেমেন্ট ও স্বচ্ছ লেনদেন</h6>
                                <p class="text-secondary small mb-0">সরাসরি প্রকাশনীর ব্যাংক একাউন্ট বা মোবাইল পেমেন্টে নিয়মিত বিল নিষ্পত্তি।</p>
                            </div>
                        </div>

                        <!-- Dynamic B2B Sales Calculator -->
                        <div class="calc-b2b-clean">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-bold text-dark small"><i class="fas fa-calculator text-primary me-1"></i> ক্যাটালগ বিক্রি প্রক্ষেপণ</span>
                                <span class="badge bg-info-subtle text-info rounded-pill px-2 py-1" style="font-size: 0.72rem;">বি২বি ডিস্ট্রিবিউশন</span>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between text-secondary small mb-1">
                                    <span>প্রকাশিত বইয়ের মোট টাইটেল:</span>
                                    <strong class="text-dark fw-bold" id="calcTitlesDisplay">৫০টি বই</strong>
                                </div>
                                <input type="range" class="form-range" id="calcTitlesSlider" min="5" max="500" step="5" value="50" oninput="updateB2BCalc(this.value)">
                            </div>
                            <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                                <span class="text-secondary small">সম্ভাব্য মাসিক টার্নওভার:</span>
                                <span class="calc-turnover-clean" id="calcTurnoverDisplay">৳১,২৫,০০০</span>
                            </div>
                        </div>
                    </div>

                    <!-- Bottom Login Link -->
                    <div class="mt-4 pt-3 border-top text-center">
                        <span class="text-secondary small">ইতিমধ্যে নিবন্ধিত প্রকাশক বা বিক্রেতা?</span>
                        <a href="{{ route('login') }}" class="text-primary fw-bold text-decoration-none ms-1 small">কোম্পানি প্যানেলে লগইন করুন &rarr;</a>
                    </div>
                </div>
            </div>

            <!-- Right Column: Smart Multi-Step Wizard Form -->
            <div class="col-lg-7">
                <div class="publisher-wizard-card">
                    <!-- Step Progress Header -->
                    <div class="stepper-header">
                        <div class="stepper-steps-row">
                            <div class="stepper-step-item active" id="stepIndicator1" onclick="goToStep(1)">
                                <span class="stepper-bubble">১</span>
                                <span>প্রতিষ্ঠান ও যোগাযোগ</span>
                            </div>
                            <i class="fas fa-chevron-right text-muted small opacity-50"></i>
                            <div class="stepper-step-item" id="stepIndicator2" onclick="goToStep(2)">
                                <span class="stepper-bubble">২</span>
                                <span>ক্যাটাগরি ও ব্র্যান্ডিং</span>
                            </div>
                            <i class="fas fa-chevron-right text-muted small opacity-50"></i>
                            <div class="stepper-step-item" id="stepIndicator3" onclick="goToStep(3)">
                                <span class="stepper-bubble">৩</span>
                                <span>সিকিউরিটি ও চুক্তি</span>
                            </div>
                        </div>
                        <div class="stepper-track-bar">
                            <div class="stepper-track-fill" id="stepperProgressBar"></div>
                        </div>
                    </div>

                    <!-- Main Form -->
                    <form action="{{ route('register.publisher.store') }}" method="POST" enctype="multipart/form-data" id="publisherRegistrationForm">
                        @csrf
                        <!-- Anti-bot honeypot -->
                        <input type="text" name="publisher_hp_field" style="display:none !important;" tabindex="-1" autocomplete="off">
                        <input type="hidden" name="country_code" id="hiddenCountryCode" value="{{ old('country_code', '+880') }}">

                        <!-- ================= STEP 1: COMPANY & CONTACT ================= -->
                        <div class="wizard-step-section active" id="stepSection1">
                            <div class="d-flex align-items-center justify-content-between mb-4 pb-2 border-bottom">
                                <h5 class="fw-bold text-dark mb-0">
                                    <i class="fas fa-building text-primary me-2"></i> প্রকাশনী বা প্রতিষ্ঠানের পরিচিতি
                                </h5>
                                <span class="badge bg-light text-secondary">ধাপ ১ / ৩</span>
                            </div>

                            <div class="row g-3 mb-3">
                                <!-- Publication Name -->
                                <div class="col-md-6">
                                    <label class="label-clean">প্রকাশনী / প্রতিষ্ঠানের নাম <span class="text-danger">*</span></label>
                                    <input type="text" name="name" id="pubNameInput" required value="{{ old('name') }}" class="input-clean" placeholder="যেমন: কথাপ্রকাশ বা অনুপম প্রকাশনী">
                                </div>

                                <!-- Contact Person Name -->
                                <div class="col-md-6">
                                    <label class="label-clean">দায়িত্বপ্রাপ্ত কর্মকর্তার নাম <span class="text-danger">*</span></label>
                                    <input type="text" name="contact_person" id="contactPersonInput" required value="{{ old('contact_person', auth()->user()?->name) }}" class="input-clean" placeholder="যেমন: মো. জাহাঙ্গীর আলম">
                                </div>
                            </div>

                            <div class="row g-3 mb-3">
                                <!-- Official Email -->
                                <div class="col-md-6">
                                    <label class="label-clean">অফিসিয়াল ইমেইল ঠিকানা <span class="text-danger">*</span></label>
                                    <input type="email" name="email" id="pubEmailInput" required value="{{ old('email', auth()->user()?->email) }}" class="input-clean" placeholder="info@publisher.com">
                                </div>

                                <!-- Clean All-Country Phone Selector -->
                                <div class="col-md-6">
                                    <label class="label-clean">অফিসিয়াল মোবাইল / ফোন <span class="text-danger">*</span></label>
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

                                        <!-- Phone Input -->
                                        <input type="tel" name="phone" id="phoneInput" required value="{{ old('phone', auth()->user()?->phone) }}" class="input-clean phone-clean-input" placeholder="017XXXXXXXX">
                                    </div>
                                    <small class="text-muted" style="font-size: 0.76rem;">আন্তর্জাতিক যেকোনো দেশের নম্বর সাপোর্ট করে।</small>
                                </div>
                            </div>

                            <div class="row g-3 mb-3">
                                <!-- Trade License -->
                                <div class="col-md-6">
                                    <label class="label-clean">ট্রেড লাইসেন্স / রেজিস্ট্রেশন নম্বর (ঐচ্ছিক)</label>
                                    <input type="text" name="trade_license_no" value="{{ old('trade_license_no') }}" class="input-clean" placeholder="TRAD/DNCC/XXXXX">
                                </div>

                                <!-- Established Year -->
                                <div class="col-md-6">
                                    <label class="label-clean">প্রতিষ্ঠার সাল</label>
                                    <input type="number" name="established_year" value="{{ old('established_year', date('Y')) }}" min="1800" max="2030" class="input-clean" placeholder="যেমন: 2012">
                                </div>
                            </div>

                            <!-- Office Address -->
                            <div class="mb-4">
                                <label class="label-clean">অফিস / শোরুমের পূর্ণ ঠিকানা</label>
                                <input type="text" name="address" value="{{ old('address') }}" class="input-clean" placeholder="যেমন: ৩৮/২ক, বাংলাবাজার, ঢাকা-১১০০">
                            </div>

                            <!-- Step 1 Footer Action -->
                            <div class="d-flex justify-content-end pt-3 border-top">
                                <button type="button" class="btn-pub-wizard-next" onclick="validateAndGoToStep(2)">
                                    <span>পরবর্তী ধাপ</span>
                                    <i class="fas fa-arrow-right"></i>
                                </button>
                            </div>
                        </div>

                        <!-- ================= STEP 2: CATEGORIES & BRANDING ================= -->
                        <div class="wizard-step-section" id="stepSection2">
                            <div class="d-flex align-items-center justify-content-between mb-4 pb-2 border-bottom">
                                <h5 class="fw-bold text-dark mb-0">
                                    <i class="fas fa-layer-group text-primary me-2"></i> প্রকাশনা ক্যাটাগরি ও ব্র্যান্ডিং
                                </h5>
                                <span class="badge bg-light text-secondary">ধাপ ২ / ৩</span>
                            </div>

                            <!-- Publication Categories Multi-Select Chips -->
                            <div class="mb-4">
                                <label class="label-clean">আপনার প্রকাশনীর প্রধান ক্ষেত্র / বইয়ের ধরন (একাধিক নির্বাচনযোগ্য):</label>
                                <div class="d-flex flex-wrap gap-2 pt-1">
                                    @foreach($publisherCategories as $cat)
                                        @php $slugCat = Str::slug($cat); @endphp
                                        <input type="checkbox" name="categories[]" value="{{ $cat }}" id="cat_{{ $slugCat }}" class="cat-tag-check" {{ is_array(old('categories')) && in_array($cat, old('categories')) ? 'checked' : '' }}>
                                        <label for="cat_{{ $slugCat }}" class="cat-tag-btn">
                                            <i class="fas fa-check-circle small opacity-50"></i> {{ $cat }}
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Logo Upload with Live Preview -->
                            <div class="mb-4">
                                <label class="label-clean">প্রকাশনীর অফিসিয়াল লোগো</label>
                                <div class="logo-clean-zone" onclick="document.getElementById('logoFileInput').click()">
                                    <div class="logo-box-preview" id="logoPreviewContainer">
                                        <i class="fas fa-image"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="fw-bold text-dark mb-1" style="font-size: 0.92rem;">লোগো আপলোড করতে এখানে ক্লিক করুন</div>
                                        <small class="text-muted d-block" style="font-size: 0.78rem;">PNG, JPEG বা WebP ফরম্যাট (সর্বোচ্চ ৫ মেগাবাইট)</small>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-secondary px-3 py-1 rounded-pill">বাছাই করুন</button>
                                </div>
                                <input type="file" name="logo" id="logoFileInput" accept="image/*" style="display: none;" onchange="previewLogoImage(this)">
                            </div>

                            <!-- Description / About Publisher -->
                            <div class="mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="label-clean mb-0">প্রকাশনীর পরিচিতি ও লক্ষ্য</label>
                                    <span class="text-muted small" id="descCharCount">০/১০০০</span>
                                </div>
                                <textarea name="description" rows="3" class="input-clean" placeholder="আপনার প্রকাশনীর ইতিহাস, বিশেষত্ব ও সাহিত্যের ভূমিকা সম্পর্কে সংক্ষেপে লিখুন..." maxlength="1000" oninput="updateDescCount(this)">{{ old('description') }}</textarea>
                            </div>

                            <!-- Website -->
                            <div class="mb-4">
                                <label class="label-clean">অফিসিয়াল ওয়েবসাইট বা ফেসবুক পেইজ (ঐচ্ছিক)</label>
                                <input type="url" name="website" value="{{ old('website') }}" class="input-clean" placeholder="https://publisher.com">
                            </div>

                            <!-- Step 2 Footer Action -->
                            <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                                <button type="button" class="btn-pub-wizard-prev" onclick="goToStep(1)">
                                    <i class="fas fa-arrow-left"></i>
                                    <span>পূর্ববর্তী ধাপ</span>
                                </button>
                                <button type="button" class="btn-pub-wizard-next" onclick="goToStep(3)">
                                    <span>পরবর্তী ধাপ</span>
                                    <i class="fas fa-arrow-right"></i>
                                </button>
                            </div>
                        </div>

                        <!-- ================= STEP 3: SECURITY & CONTRACT ================= -->
                        <div class="wizard-step-section" id="stepSection3">
                            <div class="d-flex align-items-center justify-content-between mb-4 pb-2 border-bottom">
                                <h5 class="fw-bold text-dark mb-0">
                                    <i class="fas fa-lock text-primary me-2"></i> একাউন্ট সিকিউরিটি ও পরিবেশনা চুক্তি
                                </h5>
                                <span class="badge bg-light text-secondary">ধাপ ৩ / ৩</span>
                            </div>

                            <!-- Password (for guest publishers) -->
                            @guest
                                <div class="mb-4">
                                    <label class="label-clean">কোম্পানি প্যানেল লগইন পাসওয়ার্ড <span class="text-danger">*</span></label>
                                    <div class="position-relative">
                                        <input type="password" name="password" id="passwordInput" required class="input-clean pe-5" placeholder="কমপক্ষে ৬ অক্ষরের পাসওয়ার্ড দিন" oninput="checkPasswordStrength(this.value)">
                                        <button type="button" class="btn btn-link text-muted position-absolute end-0 top-50 translate-middle-y text-decoration-none me-2" onclick="togglePasswordVisibility('passwordInput', this)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <!-- Strength Meter -->
                                    <div class="pwd-meter-track">
                                        <div class="pwd-meter-fill" id="pwdStrengthFill"></div>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mt-1">
                                        <small class="text-muted" style="font-size: 0.75rem;">পাসওয়ার্ড নিরাপত্তা স্তর:</small>
                                        <small class="fw-bold text-muted" style="font-size: 0.75rem;" id="pwdStrengthText">প্রদান করুন</small>
                                    </div>
                                </div>
                            @endguest

                            <!-- Terms & Distribution Agreement Consent -->
                            <div class="mb-4 p-3 rounded-3 bg-light border">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="terms_agreed" id="termsCheck" required value="1" {{ old('terms_agreed') ? 'checked' : '' }}>
                                    <label class="form-check-label text-dark small" for="termsCheck">
                                        আমি আইডিয়া প্রকাশন ও ডিস্ট্রিবিউশন হাবের <a href="javascript:void(0)" class="text-primary fw-bold text-decoration-underline" data-bs-toggle="modal" data-bs-target="#termsModal">প্রকাশক পরিবেশনা চুক্তি ও নীতিমালা</a> পড়েছি এবং এতে সম্মতি জানাচ্ছি। <span class="text-danger">*</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Step 3 Footer Action -->
                            <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                                <button type="button" class="btn-pub-wizard-prev" onclick="goToStep(2)">
                                    <i class="fas fa-arrow-left"></i>
                                    <span>পূর্ববর্তী ধাপ</span>
                                </button>
                                <button type="submit" class="btn-pub-wizard-next px-4" id="btnSubmitPublisherReg">
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
     MODAL: Publisher Distribution Agreement & Policy
============================================================= -->
<div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 rounded-4 shadow-lg bg-white">
            <div class="modal-header border-bottom px-4 py-3 bg-light">
                <h5 class="modal-title fw-bold text-dark" id="termsModalLabel">
                    <i class="fas fa-file-contract text-primary me-2"></i> আইডিয়া প্রকাশনী পরিবেশনা ও বিক্রয় চুক্তিমালা
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 text-secondary" style="line-height: 1.8; font-size: 0.95rem;">
                <h6 class="text-dark fw-bold mb-2">১. বই সরবরাহ ও গুণগত মান</h6>
                <p>প্রকাশক নিশ্চিত করবেন যে সরবরাহকৃত সকল মুদ্রিত বই এবং ডিজিটাল প্রকাশনা মৌলিক ও বৈধ স্বত্বাধিকারপ্রাপ্ত। প্রতিটি বইয়ের বাইন্ডিং ও প্রিন্ট কোয়ালিটি গ্রাহক সন্তুষ্টির উপযোগী হতে হবে।</p>

                <h6 class="text-dark fw-bold mb-2">২. ইনভেন্টরি ও বিক্রয় কমিশন</h6>
                <p>আইডিয়া প্ল্যাটফর্মের মাধ্যমে বিক্রিত বইয়ের ওপর নির্ধারিত পরিবেশক কমিশন প্রযোজ্য হবে। প্রকাশক তাঁর ড্যাশবোর্ড থেকে রিয়েলটাইমে স্টক, চালান ও বিক্রয় হিসাব নিরীক্ষণ করতে পারবেন।</p>

                <h6 class="text-dark fw-bold mb-2">৩. ডিজিটাল ই-বুক রূপান্তর ও ডিআরএম</h6>
                <p>ডিজিটাল সংস্করণ বিক্রির ক্ষেত্রে আইডিয়া নিজস্ব ডিআরএম এনক্রিপশন ও ওয়াটারমার্ক প্রযুক্তি প্রয়োগ করবে যাতে প্রকাশনীর ক্যাটালগ পাইরেসি থেকে শতভাগ সুরক্ষিত থাকে।</p>

                <h6 class="text-dark fw-bold mb-2">৪. বিল নিষ্পত্তি ও পেমেন্ট</h6>
                <p>মাসিক বা পাক্ষিক ভিত্তিতে প্রস্তুতকৃত ক্রয় চালানের বিপরীতে সরাসরি প্রকাশনীর ব্যাংক অ্যাকাউন্ট বা মনোনীত একাউন্টে বিল পরিশোধ করা হবে।</p>
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
        window.scrollTo({ top: document.querySelector('.publisher-wizard-card').offsetTop - 20, behavior: 'smooth' });
    }
}

function validateAndGoToStep(nextStep) {
    if (nextStep === 2) {
        const name = document.getElementById('pubNameInput');
        const contact = document.getElementById('contactPersonInput');
        const email = document.getElementById('pubEmailInput');
        const phone = document.getElementById('phoneInput');

        if (!name.value.trim()) {
            name.focus();
            alert('অনুগ্রহ করে প্রকাশনী বা প্রতিষ্ঠানের নাম লিখুন।');
            return;
        }
        if (!contact.value.trim()) {
            contact.focus();
            alert('দায়িত্বপ্রাপ্ত কর্মকর্তার নাম প্রদান করুন।');
            return;
        }
        if (!email.value.trim() || !email.checkValidity()) {
            email.focus();
            alert('সঠিক অফিশিয়াল ইমেইল ঠিকানা প্রদান করুন।');
            return;
        }
        if (!phone.value.trim()) {
            phone.focus();
            alert('সচল অফিশিয়াল মোবাইল নম্বর প্রদান করুন।');
            return;
        }
    }
    goToStep(nextStep);
}

// --- 2. Dynamic B2B Profit & Reach Calculator ---
function updateB2BCalc(titles) {
    const titleNum = parseInt(titles);
    document.getElementById('calcTitlesDisplay').innerText = titleNum.toLocaleString('bn-BD') + 'টি বই';
    
    const copiesPerTitle = 10;
    const avgPrice = 250;
    const totalTurnover = titleNum * copiesPerTitle * avgPrice;
    
    document.getElementById('calcTurnoverDisplay').innerText = '৳' + totalTurnover.toLocaleString('bn-BD');
}

// --- 3. Country Selector and Search Filter ---
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

// --- 4. Logo Image Live Preview ---
function previewLogoImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function (e) {
            const container = document.getElementById('logoPreviewContainer');
            container.innerHTML = `<img src="${e.target.result}" style="width:100%;height:100%;object-fit:cover;border-radius:10px;">`;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// --- 5. Description Character Counter ---
function updateDescCount(textarea) {
    const len = textarea.value.length;
    document.getElementById('descCharCount').innerText = len.toLocaleString('bn-BD') + '/১০০০';
}

// --- 6. Real-Time Password Strength Checker ---
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
