@extends('layouts.app')
@section('title', 'যোগাযোগ ও সহায়তা — আইডিয়া প্রকাশন')
@section('meta_description', 'আইডিয়া প্রকাশন কাস্টমার কেয়ার, বই অর্ডার, লেখক প্রকাশনা, পাইকারি বুকশপ ও হেল্পলাইন। সরাসরি কথা বলুন বা বার্তা পাঠান।')

@section('content')
@php
    $siteName = \App\Support\SiteSetting::name() ?: 'আইডিয়া প্রকাশন';
    $helplinePhone = \App\Support\SiteSetting::helplinePhone() ?: '01726976982';
    $whatsappNum = \App\Support\SiteSetting::get('whatsapp_number') ?: '01726976982';
    $helplineEmail = \App\Support\SiteSetting::get('helpline_email') ?: \App\Support\SiteSetting::get('contact_email') ?: 'ideapbd@gmail.com';
    $officeAddress = \App\Support\SiteSetting::get('contact_address') ?: 'আইডিয়া প্রকাশন, ঢাকা, বাংলাদেশ';
@endphp

<div class="contact-ultra-wrapper bg-slate-50 min-vh-100">

    {{-- ========================================================================= --}}
    {{-- 1. HERO SECTION: Modern Mesh Gradient with Ambient Glow & Badges        --}}
    {{-- ========================================================================= --}}
    <section class="contact-hero position-relative overflow-hidden text-white">
        <!-- Ambient Radial Glows -->
        <div class="hero-glow-1 position-absolute pe-none"></div>
        <div class="hero-glow-2 position-absolute pe-none"></div>
        <div class="hero-grid-pattern position-absolute pe-none"></div>

        <div class="container position-relative z-2 py-5 py-lg-6">
            {{-- Breadcrumb --}}
            <nav aria-label="breadcrumb" class="mb-3 mb-md-4">
                <ol class="breadcrumb mb-0 align-items-center">
                    <li class="breadcrumb-item">
                        <a href="{{ route('home') }}" class="hero-breadcrumb-link">
                            <i class="fa-solid fa-house me-1"></i>হোম
                        </a>
                    </li>
                    <li class="breadcrumb-item text-white-50 active" aria-current="page">যোগাযোগ ও সহায়তা</li>
                </ol>
            </nav>

            <div class="row align-items-center g-4 g-lg-5">
                <div class="col-lg-8">
                    <div class="hero-pill-badge mb-3 d-inline-flex align-items-center gap-2">
                        <span class="pulse-dot">
                            <span class="pulse-ring"></span>
                            <span class="pulse-core"></span>
                        </span>
                        <span>২৪/৭ ডেডিকেটেড সাপোর্ট ও কাস্টমার কেয়ার</span>
                    </div>

                    <h1 class="hero-title fw-black mb-3 lh-sm">
                        যোগাযোগ ও সহায়তা কেন্দ্র
                    </h1>

                    <p class="hero-subtitle mb-0">
                        বই অর্ডার, প্রকাশনা সেবা, লেখক পান্ডুলিপি জমা, পাইকারি বুকশপ ডিস্ট্রিবিউশন বা যেকোনো প্রয়োজনে আমাদের সাথে সরাসরি যোগাযোগ করুন।
                    </p>
                </div>

                <div class="col-lg-4 text-start text-lg-end">
                    <div class="hero-status-card d-inline-block text-start">
                        <div class="d-flex align-items-center gap-2 mb-1.5">
                            <span class="status-live-dot"></span>
                            <span class="fw-bold text-white small">সাপোর্ট টিম এখন সক্রিয়</span>
                        </div>
                        <div class="small text-white-50">
                            গড় রেসপন্স সময়: <strong class="text-emerald-300">১৫–৩০ মিনিট</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ========================================================================= --}}
    {{-- 2. FOUR QUICK CONTACT CHANNELS (Floating Cards)                          --}}
    {{-- ========================================================================= --}}
    <section class="container position-relative z-3 quick-channels-container">
        <div class="row g-3 g-md-4">

            {{-- Channel 1: Phone Hotline --}}
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="quick-channel-card h-100 d-flex flex-column justify-content-between p-4">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="channel-icon-capsule bg-amber-gradient text-white">
                                <i class="fa-solid fa-phone-volume"></i>
                            </div>
                            <span class="channel-badge badge-amber">সরাসরি কল</span>
                        </div>
                        <h3 class="channel-heading mb-1">হটলাইন হেল্পলাইন</h3>
                        <p class="channel-text mb-3">শনি–বৃহস্পতি (সকাল ৯টা – রাত ১১টা)</p>
                        <div class="channel-value font-monospace text-slate-800 fw-bold mb-3">{{ $helplinePhone }}</div>
                    </div>
                    <div class="d-flex align-items-center gap-2 pt-3 border-top border-slate-100">
                        <a href="tel:{{ $helplinePhone }}" class="btn btn-action-primary flex-grow-1">
                            <i class="fa-solid fa-phone me-1.5"></i>কল করুন
                        </a>
                        <button type="button" class="btn btn-action-icon" onclick="copyContactText('{{ $helplinePhone }}', 'ফোন নম্বর')" title="নম্বর কপি করুন">
                            <i class="fa-regular fa-copy"></i>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Channel 2: WhatsApp Chat --}}
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="quick-channel-card h-100 d-flex flex-column justify-content-between p-4">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="channel-icon-capsule bg-emerald-gradient text-white">
                                <i class="fa-brands fa-whatsapp fs-5"></i>
                            </div>
                            <span class="channel-badge badge-emerald">তাৎক্ষণিক চ্যাট</span>
                        </div>
                        <h3 class="channel-heading mb-1">অফিসিয়াল হোয়াটসঅ্যাপ</h3>
                        <p class="channel-text mb-3">মেসেজ ড্রপ করুন যেকোনো সময়</p>
                        <div class="channel-value font-monospace text-slate-800 fw-bold mb-3">{{ $whatsappNum }}</div>
                    </div>
                    <div class="d-flex align-items-center gap-2 pt-3 border-top border-slate-100">
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $whatsappNum) }}" target="_blank" class="btn btn-action-emerald flex-grow-1">
                            <i class="fa-brands fa-whatsapp me-1.5"></i>চ্যাট করুন
                        </a>
                        <button type="button" class="btn btn-action-icon" onclick="copyContactText('{{ $whatsappNum }}', 'হোয়াটসঅ্যাপ')" title="কপি করুন">
                            <i class="fa-regular fa-copy"></i>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Channel 3: Email Support --}}
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="quick-channel-card h-100 d-flex flex-column justify-content-between p-4">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="channel-icon-capsule bg-indigo-gradient text-white">
                                <i class="fa-solid fa-paper-plane"></i>
                            </div>
                            <span class="channel-badge badge-indigo">ইমেইল ডেস্ক</span>
                        </div>
                        <h3 class="channel-heading mb-1">অফিসিয়াল ইমেইল</h3>
                        <p class="channel-text mb-3">২৪ ঘণ্টার মধ্যে জবাব নিশ্চিত</p>
                        <div class="channel-value font-monospace text-slate-800 fw-bold mb-3 text-truncate" title="{{ $helplineEmail }}">{{ $helplineEmail }}</div>
                    </div>
                    <div class="d-flex align-items-center gap-2 pt-3 border-top border-slate-100">
                        <a href="mailto:{{ $helplineEmail }}" class="btn btn-action-indigo flex-grow-1">
                            <i class="fa-solid fa-envelope me-1.5"></i>মেইল লিখুন
                        </a>
                        <button type="button" class="btn btn-action-icon" onclick="copyContactText('{{ $helplineEmail }}', 'ইমেইল')" title="কপি করুন">
                            <i class="fa-regular fa-copy"></i>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Channel 4: Corporate Office --}}
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="quick-channel-card h-100 d-flex flex-column justify-content-between p-4">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="channel-icon-capsule bg-rose-gradient text-white">
                                <i class="fa-solid fa-location-dot"></i>
                            </div>
                            <span class="channel-badge badge-rose">হেড অফিস</span>
                        </div>
                        <h3 class="channel-heading mb-1">প্রধান কার্যালয়</h3>
                        <p class="channel-text mb-3">সরাসরি দেখা ও পরামর্শ ডেস্ক</p>
                        <div class="channel-value text-slate-800 fw-bold mb-3 text-truncate">{{ $officeAddress }}</div>
                    </div>
                    <div class="d-flex align-items-center gap-2 pt-3 border-top border-slate-100">
                        <a href="#officeLocationMap" class="btn btn-action-outline flex-grow-1">
                            <i class="fa-solid fa-map-pin me-1.5"></i>ম্যাপ ও ঠিকানা
                        </a>
                        <button type="button" class="btn btn-action-icon" onclick="copyContactText('{{ $officeAddress }}', 'অফিসের ঠিকানা')" title="ঠিকানা কপি করুন">
                            <i class="fa-regular fa-copy"></i>
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </section>

    {{-- ========================================================================= --}}
    {{-- 3. MAIN SECTION: Interactive Contact Form & Department Helpdesks        --}}
    {{-- ========================================================================= --}}
    <section class="container py-5">
        <div class="row g-4 g-lg-5 align-items-stretch">

            {{-- Left Column: Interactive Contact Form --}}
            <div class="col-12 col-lg-7">
                <div class="modern-card p-4 p-md-5 h-100 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center justify-content-between pb-3 mb-4 border-bottom border-slate-100">
                            <div>
                                <span class="badge bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-pill px-3 py-1 fw-bold small mb-2 d-inline-flex align-items-center gap-1.5">
                                    <i class="fa-solid fa-pen-nib text-emerald-600"></i> সরাসরি বার্তা
                                </span>
                                <h2 class="h4 fw-bold text-slate-900 mb-1">আমাদের সরাসরি বার্তা পাঠান</h2>
                                <p class="text-slate-500 small mb-0">আপনার জিজ্ঞাসা বা প্রয়োজনের বিবরণ লিখুন, আমরা দ্রুত সমাধান দেব।</p>
                            </div>
                            <div class="form-header-badge d-none d-sm-flex align-items-center justify-content-center">
                                <i class="fa-solid fa-paper-plane text-emerald-600"></i>
                            </div>
                        </div>

                        {{-- Alert Boxes --}}
                        <div id="contactFormAlert" class="alert d-none py-3 px-3.5 rounded-4 small mb-4 border-0"></div>

                        @if(session('success'))
                            <div class="alert alert-success py-3 px-3.5 rounded-4 small mb-4 border-0 bg-emerald-50 text-emerald-800 d-flex align-items-center gap-2">
                                <i class="fa-solid fa-circle-check text-emerald-600 fs-5"></i>
                                <div>{{ session('success') }}</div>
                            </div>
                        @endif

                        <form action="{{ route('contact.submit') }}" method="POST" id="mainContactForm" class="modern-contact-form">
                            @csrf

                            <div class="row g-3 g-md-3.5">
                                {{-- Full Name --}}
                                <div class="col-12 col-sm-6">
                                    <label for="contactName" class="form-label-modern">
                                        আপনার পূর্ণ নাম <span class="text-rose-500">*</span>
                                    </label>
                                    <div class="modern-input-group">
                                        <span class="input-icon"><i class="fa-regular fa-user"></i></span>
                                        <input type="text" name="name" id="contactName" class="form-control modern-input" 
                                               placeholder="নাম লিখুন..." required value="{{ old('name', auth()->user()->name ?? '') }}">
                                    </div>
                                </div>

                                {{-- Mobile / WhatsApp --}}
                                <div class="col-12 col-sm-6">
                                    <label for="contactPhone" class="form-label-modern">
                                        মোবাইল / হোয়াটসঅ্যাপ নম্বর <span class="text-rose-500">*</span>
                                    </label>
                                    <div class="modern-input-group">
                                        <span class="input-icon"><i class="fa-solid fa-phone"></i></span>
                                        <input type="tel" name="phone" id="contactPhone" class="form-control modern-input font-monospace" 
                                               placeholder="017XXXXXXXX" required value="{{ old('phone', auth()->user()->phone ?? '') }}">
                                    </div>
                                </div>

                                {{-- Email Address --}}
                                <div class="col-12 col-sm-6">
                                    <label for="contactEmail" class="form-label-modern">
                                        ইমেইল ঠিকানা <span class="text-slate-400 fw-normal">(ঐচ্ছিক)</span>
                                    </label>
                                    <div class="modern-input-group">
                                        <span class="input-icon"><i class="fa-regular fa-envelope"></i></span>
                                        <input type="email" name="email" id="contactEmail" class="form-control modern-input" 
                                               placeholder="name@example.com" value="{{ old('email', auth()->user()->email ?? '') }}">
                                    </div>
                                </div>

                                {{-- Subject Category --}}
                                <div class="col-12 col-sm-6">
                                    <label for="contactSubject" class="form-label-modern">
                                        বার্তার বিষয়শ্রেণী <span class="text-rose-500">*</span>
                                    </label>
                                    <div class="modern-input-group">
                                        <span class="input-icon"><i class="fa-solid fa-list-check"></i></span>
                                        <select name="subject" id="contactSubject" class="form-select modern-input" required>
                                            <option value="" disabled selected>বিষয় নির্বাচন করুন...</option>
                                            <option value="বই অর্ডার ও ডেলিভারি জিজ্ঞাসা">📚 বই অর্ডার ও ডেলিভারি জিজ্ঞাসা</option>
                                            <option value="লেখক প্রকাশনা ও পান্ডুলিপি জমা">✍️ লেখক প্রকাশনা ও পান্ডুলিপি জমা</option>
                                            <option value="সেলার ও পাইকারি বুকশপ পার্টনারশিপ">💼 সেলার ও পাইকারি বুকশপ</option>
                                            <option value="পেমেন্ট, ইনভয়েস ও রয়্যালটি">💳 পেমেন্ট, ইনভয়েস ও রয়্যালটি</option>
                                            <option value="ই-বুক ও ডিজিটাল লাইব্রেরি">📱 ই-বুক ও ডিজিটাল রিডার</option>
                                            <option value="অন্যান্য পরামর্শ ও সাধারণ অনুসন্ধান">💬 অন্যান্য সাধারণ অনুসন্ধান</option>
                                        </select>
                                    </div>
                                </div>

                                {{-- Message Textarea --}}
                                <div class="col-12">
                                    <div class="d-flex justify-content-between align-items-center mb-1.5">
                                        <label for="contactMessage" class="form-label-modern mb-0">
                                            আপনার বার্তা বা বিস্তারিত বিবরণ <span class="text-rose-500">*</span>
                                        </label>
                                        <span class="text-slate-400 small" id="charCount">০ / ৩০০০</span>
                                    </div>
                                    <div class="modern-input-group">
                                        <textarea name="message" id="contactMessage" rows="5" class="form-control modern-input textarea-input" 
                                                  placeholder="আপনার প্রয়োজনীয় তথ্য, বইয়ের নাম বা প্রশ্ন বিস্তারিতভাবে লিখুন..." required maxlength="3000" oninput="updateCharCount(this)"></textarea>
                                    </div>
                                </div>

                                {{-- Action Buttons --}}
                                <div class="col-12 pt-2">
                                    <div class="d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center gap-3">
                                        <button type="submit" class="btn btn-submit-modern" id="contactSubmitBtn">
                                            <i class="fa-solid fa-paper-plane"></i>
                                            <span>বার্তা পাঠান</span>
                                        </button>
                                        <button type="button" class="btn btn-whatsapp-direct" onclick="sendViaWhatsApp()">
                                            <i class="fa-brands fa-whatsapp fs-5"></i>
                                            <span>হোয়াটসঅ্যাপে পাঠান</span>
                                        </button>
                                    </div>
                                    <p class="text-slate-400 small mt-2.5 mb-0">
                                        <i class="fa-solid fa-shield-halved text-emerald-600 me-1"></i>আপনার তথ্য সম্পূর্ণ নিরাপদ এবং গোপনীয় রাখা হয়।
                                    </p>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Right Column: Department Directory & Support Info --}}
            <div class="col-12 col-lg-5">
                <div class="d-flex flex-column gap-4 h-100">

                    {{-- 1. Department Direct Directory Card --}}
                    <div class="modern-card p-4 p-md-4.5 flex-grow-1">
                        <div class="d-flex align-items-center justify-content-between pb-3 mb-3 border-bottom border-slate-100">
                            <div class="d-flex align-items-center gap-2.5">
                                <div class="dept-main-icon">
                                    <i class="fa-solid fa-sitemap text-emerald-600"></i>
                                </div>
                                <div>
                                    <h3 class="h6 fw-bold text-slate-900 mb-0">বিভাগভিত্তিক সরাসরি হেল্পডেস্ক</h3>
                                    <span class="text-slate-500 small">নির্দিষ্ট প্রয়োজনে সরাসরি যোগাযোগ</span>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex flex-column gap-3">
                            {{-- Editorial Desk --}}
                            <div class="dept-item-box p-3 rounded-4">
                                <div class="d-flex align-items-center justify-content-between mb-1.5">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="dept-badge-icon bg-emerald-50 text-emerald-600"><i class="fa-solid fa-feather-pointed"></i></span>
                                        <strong class="text-slate-800 small">প্রকাশনা ও সম্পাদকীয় বিভাগ</strong>
                                    </div>
                                    <span class="badge bg-emerald-100 text-emerald-800 small rounded-pill px-2.5">পান্ডুলিপি</span>
                                </div>
                                <p class="text-slate-500 small mb-2">নতুন বই প্রকাশনা ও পান্ডুলিপি মূল্যায়নের জন্য যোগাযোগ করুন।</p>
                                <div class="d-flex align-items-center justify-content-between">
                                    <a href="mailto:{{ $helplineEmail }}" class="dept-contact-link">{{ $helplineEmail }}</a>
                                    <span class="text-slate-400 small"><i class="fa-regular fa-clock me-1"></i>২৪ ঘণ্টার মধ্যে রেসপন্স</span>
                                </div>
                            </div>

                            {{-- Wholesale Desk --}}
                            <div class="dept-item-box p-3 rounded-4">
                                <div class="d-flex align-items-center justify-content-between mb-1.5">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="dept-badge-icon bg-amber-50 text-amber-600"><i class="fa-solid fa-store"></i></span>
                                        <strong class="text-slate-800 small">বিক্রয় ও পরিবেশনা বিভাগ</strong>
                                    </div>
                                    <span class="badge bg-amber-100 text-amber-800 small rounded-pill px-2.5">বুকশপ ও এজেন্ট</span>
                                </div>
                                <p class="text-slate-500 small mb-2">লাইব্রেরি ও বুকশপের পাইকারি বই সরবরাহ ও কমিশন সংক্রান্ত।</p>
                                <div class="d-flex align-items-center justify-content-between">
                                    <a href="tel:{{ $helplinePhone }}" class="dept-contact-link font-monospace">{{ $helplinePhone }}</a>
                                    <span class="text-slate-400 small"><i class="fa-solid fa-truck-fast me-1"></i>সারাদেশে ডেলিভারি</span>
                                </div>
                            </div>

                            {{-- Accounts & Royalty --}}
                            <div class="dept-item-box p-3 rounded-4">
                                <div class="d-flex align-items-center justify-content-between mb-1.5">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="dept-badge-icon bg-indigo-50 text-indigo-600"><i class="fa-solid fa-wallet"></i></span>
                                        <strong class="text-slate-800 small">হিসাব ও লেখক রয়্যালটি বিভাগ</strong>
                                    </div>
                                    <span class="badge bg-indigo-100 text-indigo-800 small rounded-pill px-2.5">রয়্যালটি ও পেমেন্ট</span>
                                </div>
                                <p class="text-slate-500 small mb-2">লেখক সম্মানী, ব্যাংক সেটেলমেন্ট ও ইনভয়েস স্টেটমেন্ট।</p>
                                <div class="d-flex align-items-center justify-content-between">
                                    <a href="mailto:{{ $helplineEmail }}" class="dept-contact-link">{{ $helplineEmail }}</a>
                                    <span class="text-slate-400 small"><i class="fa-solid fa-shield-check me-1"></i>নিরাপদ লেনদেন</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 2. FAQ Callout Box --}}
                    <div class="faq-callout-card p-4 rounded-4 position-relative overflow-hidden text-white">
                        <div class="d-flex align-items-start gap-3 position-relative z-2">
                            <div class="faq-callout-icon flex-shrink-0">
                                <i class="fa-solid fa-circle-question fs-4"></i>
                            </div>
                            <div>
                                <h4 class="h6 fw-bold text-white mb-1.5">কোনো সাধারণ জিজ্ঞাসা রয়েছে?</h4>
                                <p class="text-white-50 small mb-3 leading-relaxed">
                                    বই অর্ডার, পেমেন্ট, ডেলিভারি ট্র্যাকিং ও রিটার্ন পলিসির সর্বাধিক জিজ্ঞাসিত প্রশ্নের তাৎক্ষণিক উত্তর পেতে আমাদের FAQ পেজ দেখুন।
                                </p>
                                <a href="{{ route('faq') }}" class="btn btn-faq-link">
                                    <span>FAQ প্রশ্নোত্তর দেখুন</span>
                                    <i class="fa-solid fa-arrow-right small"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </section>

    {{-- ========================================================================= --}}
    {{-- 4. LOCATION, WORKING SCHEDULE & INTERACTIVE MAP SHOWCASE                 --}}
    {{-- ========================================================================= --}}
    <section class="container pb-5" id="officeLocationMap">
        <div class="modern-card p-4 p-md-5">
            <div class="row g-4 g-lg-5 align-items-center">

                {{-- Left: Office Details & Operating Schedule --}}
                <div class="col-lg-6">
                    <span class="badge bg-rose-50 text-rose-700 border border-rose-200 rounded-pill px-3 py-1 fw-bold small mb-2.5 d-inline-flex align-items-center gap-1.5">
                        <i class="fa-solid fa-building-flag text-rose-600"></i> হেড অফিস ও পরামর্শ কেন্দ্র
                    </span>
                    <h2 class="h3 fw-bold text-slate-900 mb-3">আমাদের কার্যালয়ে আপনাকে স্বাগতম</h2>
                    <p class="text-slate-600 mb-4 leading-relaxed" style="font-size: 14.5px;">
                        {{ $siteName }} বাংলাদেশের অগ্রণী সাহিত্য ও গবেষণা প্রকাশনা প্রতিষ্ঠান। লেখক ও পাঠকদের সুবিধার্থে আমাদের হেড অফিসে রয়েছে বই প্রদর্শনী, পান্ডুলিপি পরামর্শ ডেস্ক এবং সরাসরি বই সংগ্রহের সুবিধা।
                    </p>

                    <div class="d-flex flex-column gap-3.5 mb-4">
                        {{-- Address --}}
                        <div class="d-flex align-items-start gap-3">
                            <div class="location-item-icon bg-emerald-50 text-emerald-600 flex-shrink-0">
                                <i class="fa-solid fa-location-dot"></i>
                            </div>
                            <div>
                                <strong class="text-slate-800 small d-block mb-0.5">অফিসের ঠিকানা:</strong>
                                <span class="text-slate-600 small">{{ $officeAddress }}</span>
                            </div>
                        </div>

                        {{-- Working Hours & Live Status --}}
                        <div class="d-flex align-items-start gap-3">
                            <div class="location-item-icon bg-amber-50 text-amber-600 flex-shrink-0">
                                <i class="fa-solid fa-clock"></i>
                            </div>
                            <div>
                                <div class="d-flex align-items-center gap-2 mb-0.5">
                                    <strong class="text-slate-800 small">কার্যকাল ও সময়সূচী:</strong>
                                    <span id="liveOfficeStatusBadge" class="badge bg-emerald-100 text-emerald-800 rounded-pill px-2.5 py-0.5 small fw-bold">খোলা রয়েছে</span>
                                </div>
                                <span class="text-slate-600 small d-block">শনিবার – বৃহস্পতিবার: সকাল ৯:০০ টা – রাত ১১:০০ টা</span>
                                <span class="text-slate-400 small">(শুক্রবার অনলাইন ও হোয়াটসঅ্যাপ সাপোর্ট সার্বক্ষণিক সচল থাকে)</span>
                            </div>
                        </div>

                        {{-- Email & Hotline --}}
                        <div class="d-flex align-items-start gap-3">
                            <div class="location-item-icon bg-indigo-50 text-indigo-600 flex-shrink-0">
                                <i class="fa-solid fa-headset"></i>
                            </div>
                            <div>
                                <strong class="text-slate-800 small d-block mb-0.5">জরুরি হেল্পলাইন ও সাপোর্ট:</strong>
                                <span class="text-slate-600 small font-monospace">{{ $helplinePhone }}</span>
                                <span class="text-slate-400 mx-1">•</span>
                                <span class="text-slate-600 small">{{ $helplineEmail }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap align-items-center gap-2.5">
                        <a href="https://maps.google.com/?q={{ urlencode($officeAddress) }}" target="_blank" class="btn btn-action-primary">
                            <i class="fa-solid fa-diamond-turn-right me-1.5"></i>গুগল ম্যাপে দিকনির্দেশনা
                        </a>
                        <button type="button" class="btn btn-action-outline" onclick="copyContactText('{{ $officeAddress }}', 'অফিসের ঠিকানা')">
                            <i class="fa-regular fa-copy me-1.5"></i>ঠিকানা কপি করুন
                        </button>
                    </div>
                </div>

                {{-- Right: Visual Map Container --}}
                <div class="col-lg-6">
                    <div class="map-visual-box rounded-4 overflow-hidden position-relative p-4 p-md-5 text-center d-flex flex-column align-items-center justify-content-center">
                        <div class="map-icon-halo mb-3">
                            <i class="fa-solid fa-map-location-dot"></i>
                        </div>
                        <h4 class="h5 fw-bold text-slate-900 mb-1.5">{{ $siteName }} হেড অফিস</h4>
                        <p class="text-slate-500 small mb-4 max-w-sm">{{ $officeAddress }}</p>

                        <div class="map-action-card p-3 rounded-4 bg-white border border-slate-200 shadow-sm w-100 max-w-md">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="small fw-bold text-slate-700"><i class="fa-solid fa-route text-emerald-600 me-1"></i>নেভিগেশন ও অবস্থান</span>
                                <span class="badge bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-pill px-2.5 py-0.5 small">ঢাকা হাব</span>
                            </div>
                            <p class="text-slate-500 small mb-3 text-start">গুগল ম্যাপে সরাসরি লোকেশন দেখে সহজেই আমাদের কার্যালয়ে পৌঁছান।</p>
                            <a href="https://maps.google.com/?q={{ urlencode($officeAddress) }}" target="_blank" class="btn btn-emerald-solid w-100 fw-bold rounded-pill">
                                <i class="fa-solid fa-arrow-up-right-from-square me-1.5"></i>গুগল ম্যাপ চালু করুন
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    {{-- ========================================================================= --}}
    {{-- 5. QUICK RESOLUTION ACCORDION (Self-Service)                             --}}
    {{-- ========================================================================= --}}
    <section class="container pb-5">
        <div class="text-center max-w-2xl mx-auto mb-4">
            <span class="badge bg-slate-200 text-slate-700 rounded-pill px-3 py-1 fw-bold small mb-2">দ্রুত সমাধান</span>
            <h2 class="h4 fw-bold text-slate-900 mb-2">সর্বাধিক জিজ্ঞাসিত কিছু সাধারণ প্রশ্ন</h2>
            <p class="text-slate-500 small mb-0">মেসেজ পাঠানোর আগেই হয়তো আপনার উত্তরটি এখানে পেয়ে যেতে পারেন</p>
        </div>

        <div class="row justify-content-center">
            <div class="col-12 col-lg-10">
                <div class="accordion modern-accordion" id="contactFaqAccordion">
                    
                    {{-- FAQ 1 --}}
                    <div class="accordion-item modern-accordion-item">
                        <h2 class="accordion-header" id="faqHeading1">
                            <button class="accordion-button modern-accordion-btn collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse1" aria-expanded="false" aria-controls="faqCollapse1">
                                <i class="fa-solid fa-truck-ramp-box text-emerald-600 me-2.5"></i>
                                বই অর্ডার করার পর কত দিনের মধ্যে ডেলিভারি পাওয়া যায়?
                            </button>
                        </h2>
                        <div id="faqCollapse1" class="accordion-collapse collapse" aria-labelledby="faqHeading1" data-bs-parent="#contactFaqAccordion">
                            <div class="accordion-body modern-accordion-body">
                                ঢাকা শহরের ভেতর সাধারণ ডেলিভারি <strong>২৪ থেকে ৪৮ ঘণ্টার</strong> মধ্যে সম্পন্ন হয়। ঢাকা সাব-অ্যারিয়া ও ঢাকার বাইরে জেলা-উপজেলায় কুরিয়ারের মাধ্যমে <strong>২ থেকে ৪ কার্যদিবসের</strong> মধ্যে বই নিরাপদে পৌঁছে দেওয়া হয়।
                            </div>
                        </div>
                    </div>

                    {{-- FAQ 2 --}}
                    <div class="accordion-item modern-accordion-item">
                        <h2 class="accordion-header" id="faqHeading2">
                            <button class="accordion-button modern-accordion-btn collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse2" aria-expanded="false" aria-controls="faqCollapse2">
                                <i class="fa-solid fa-pen-fancy text-emerald-600 me-2.5"></i>
                                নতুন লেখক হিসেবে পান্ডুলিপি কীভাবে জমা দেব?
                            </button>
                        </h2>
                        <div id="faqCollapse2" class="accordion-collapse collapse" aria-labelledby="faqHeading2" data-bs-parent="#contactFaqAccordion">
                            <div class="accordion-body modern-accordion-body">
                                আপনার পান্ডুলিপির সারসংক্ষেপ, লেখক পরিচিতি ও নমুনা অধ্যায় ওয়ার্ড (DOCX) বা পিডিএফ ফরম্যাটে আমাদের ইমেইল (<span class="font-monospace text-emerald-700 fw-bold">{{ $helplineEmail }}</span>) ঠিকানায় পাঠাতে পারেন অথবা সম্পাদকীয় হেল্পডেস্কে যোগাযোগ করতে পারেন। আমাদের সম্পাদনা পর্ষদ ৭–১০ কার্যদিবসের মধ্যে যোগাযোগ করবে।
                            </div>
                        </div>
                    </div>

                    {{-- FAQ 3 --}}
                    <div class="accordion-item modern-accordion-item">
                        <h2 class="accordion-header" id="faqHeading3">
                            <button class="accordion-button modern-accordion-btn collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse3" aria-expanded="false" aria-controls="faqCollapse3">
                                <i class="fa-solid fa-money-bill-transfer text-emerald-600 me-2.5"></i>
                                ক্যাশ অন ডেলিভারি (COD) ও অনলাইন পেমেন্টের কী কী মাধ্যম রয়েছে?
                            </button>
                        </h2>
                        <div id="faqCollapse3" class="accordion-collapse collapse" aria-labelledby="faqHeading3" data-bs-parent="#contactFaqAccordion">
                            <div class="accordion-body modern-accordion-body">
                                আমরা বিকাশ, নগদ, রকেট, ডেবিট/ক্রেডিট কার্ড (ভিসা, মাস্টারকার্ড) এবং পণ্য হাতে পেয়ে মূল্য পরিশোধের সুবিধা (Cash on Delivery) প্রদান করি।
                            </div>
                        </div>
                    </div>

                    {{-- FAQ 4 --}}
                    <div class="accordion-item modern-accordion-item">
                        <h2 class="accordion-header" id="faqHeading4">
                            <button class="accordion-button modern-accordion-btn collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse4" aria-expanded="false" aria-controls="faqCollapse4">
                                <i class="fa-solid fa-store text-emerald-600 me-2.5"></i>
                                পাইকারি বা লাইব্রেরির জন্য কীভাবে বই সংগ্রহ করব?
                            </button>
                        </h2>
                        <div id="faqCollapse4" class="accordion-collapse collapse" aria-labelledby="faqHeading4" data-bs-parent="#contactFaqAccordion">
                            <div class="accordion-body modern-accordion-body">
                                যেকোনো লাইব্রেরি, শিক্ষা প্রতিষ্ঠান বা পাইকারি বুকশপ পার্টনারশিপের জন্য সরাসরি আমাদের বিক্রয় ও পরিবেশনা বিভাগের নম্বরে (<span class="font-monospace text-emerald-700 fw-bold">{{ $helplinePhone }}</span>) কল করতে পারেন অথবা সেলার পার্টনারশিপ ফর্মের মাধ্যমে আবেদন করতে পারেন।
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>

</div>

{{-- ========================================================================= --}}
{{-- STYLES: Scoped World-Class CSS for Ultra-Modern Contact Page               --}}
{{-- ========================================================================= --}}
<style>
/* CSS Variables & Tokens */
:root {
    --contact-primary: #006a4e;
    --contact-primary-dark: #004d40;
    --contact-accent-emerald: #10b981;
    --contact-accent-teal: #0d9488;
    --contact-border-radius: 20px;
    --contact-card-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.05), 0 2px 6px -1px rgba(15, 23, 42, 0.03);
    --contact-hover-shadow: 0 20px 30px -8px rgba(0, 106, 78, 0.12), 0 8px 16px -4px rgba(15, 23, 42, 0.06);
}

.contact-ultra-wrapper {
    font-family: 'Hind Siliguri', 'Segoe UI', system-ui, -apple-system, sans-serif;
    color: #1e293b;
    background-color: #f8fafc;
}

/* 1. Hero Section */
.contact-hero {
    background: linear-gradient(135deg, #05192d 0%, #033a32 50%, #005a43 100%);
    min-height: 280px;
}

.hero-glow-1 {
    top: -10%;
    right: 5%;
    width: 450px;
    height: 450px;
    background: radial-gradient(circle, rgba(16, 185, 129, 0.18) 0%, rgba(0, 0, 0, 0) 70%);
    border-radius: 50%;
    filter: blur(40px);
}

.hero-glow-2 {
    bottom: -20%;
    left: 10%;
    width: 380px;
    height: 380px;
    background: radial-gradient(circle, rgba(14, 165, 233, 0.15) 0%, rgba(0, 0, 0, 0) 70%);
    border-radius: 50%;
    filter: blur(50px);
}

.hero-grid-pattern {
    inset: 0;
    background-image: radial-gradient(rgba(255, 255, 255, 0.08) 1px, transparent 1px);
    background-size: 24px 24px;
    opacity: 0.6;
}

.hero-breadcrumb-link {
    color: rgba(255, 255, 255, 0.7);
    text-decoration: none;
    transition: color 0.2s ease;
}
.hero-breadcrumb-link:hover {
    color: #ffffff;
}

.hero-pill-badge {
    background: rgba(255, 255, 255, 0.12);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 9999px;
    padding: 6px 16px;
    font-size: 12.5px;
    font-weight: 700;
    color: #f1f5f9;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.pulse-dot {
    position: relative;
    display: inline-flex;
    width: 8px;
    height: 8px;
}
.pulse-ring {
    position: absolute;
    inset: 0;
    border-radius: 9999px;
    background-color: #34d399;
    animation: ping 1.5s cubic-bezier(0, 0, 0.2, 1) infinite;
    opacity: 0.75;
}
.pulse-core {
    position: relative;
    display: inline-block;
    width: 8px;
    height: 8px;
    border-radius: 9999px;
    background-color: #10b981;
}

@keyframes ping {
    75%, 100% {
        transform: scale(2);
        opacity: 0;
    }
}

.hero-title {
    font-size: clamp(1.85rem, 3.5vw, 2.75rem);
    letter-spacing: -0.5px;
    color: #ffffff;
}

.hero-subtitle {
    font-size: 15px;
    line-height: 1.7;
    color: rgba(255, 255, 255, 0.82);
    max-width: 680px;
}

.hero-status-card {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
    border: 1px solid rgba(255, 255, 255, 0.18);
    border-radius: 16px;
    padding: 14px 20px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.12);
}

.status-live-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background-color: #10b981;
    box-shadow: 0 0 10px #10b981;
}

/* 2. Quick Channels */
.quick-channels-container {
    margin-top: -36px;
}

.quick-channel-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: var(--contact-border-radius);
    box-shadow: var(--contact-card-shadow);
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

.quick-channel-card:hover {
    transform: translateY(-6px);
    border-color: #cbd5e1;
    box-shadow: var(--contact-hover-shadow);
}

.channel-icon-capsule {
    width: 46px;
    height: 46px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.bg-amber-gradient {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
}
.bg-emerald-gradient {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}
.bg-indigo-gradient {
    background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
}
.bg-rose-gradient {
    background: linear-gradient(135deg, #f43f5e 0%, #e11d48 100%);
}

.channel-badge {
    padding: 4px 10px;
    font-size: 11px;
    font-weight: 700;
    border-radius: 9999px;
}
.badge-amber { background: #fef3c7; color: #92400e; }
.badge-emerald { background: #d1fae5; color: #065f46; }
.badge-indigo { background: #e0e7ff; color: #3730a3; }
.badge-rose { background: #ffe4e6; color: #9f1239; }

.channel-heading {
    font-size: 16px;
    font-weight: 800;
    color: #0f172a;
}
.channel-text {
    font-size: 12px;
    color: #64748b;
}
.channel-value {
    font-size: 14px;
}

/* Action Buttons */
.btn-action-primary {
    background: var(--contact-primary);
    color: #ffffff;
    font-weight: 700;
    font-size: 13px;
    border-radius: 9999px;
    padding: 8px 16px;
    border: none;
    transition: all 0.2s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.btn-action-primary:hover {
    background: var(--contact-primary-dark);
    color: #ffffff;
    transform: translateY(-1px);
}

.btn-action-emerald {
    background: #10b981;
    color: #ffffff;
    font-weight: 700;
    font-size: 13px;
    border-radius: 9999px;
    padding: 8px 16px;
    border: none;
    transition: all 0.2s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.btn-action-emerald:hover {
    background: #059669;
    color: #ffffff;
    transform: translateY(-1px);
}

.btn-action-indigo {
    background: #4f46e5;
    color: #ffffff;
    font-weight: 700;
    font-size: 13px;
    border-radius: 9999px;
    padding: 8px 16px;
    border: none;
    transition: all 0.2s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.btn-action-indigo:hover {
    background: #4338ca;
    color: #ffffff;
    transform: translateY(-1px);
}

.btn-action-outline {
    background: #f8fafc;
    color: #334155;
    font-weight: 700;
    font-size: 13px;
    border-radius: 9999px;
    padding: 8px 16px;
    border: 1px solid #cbd5e1;
    transition: all 0.2s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.btn-action-outline:hover {
    background: #e2e8f0;
    color: #0f172a;
    transform: translateY(-1px);
}

.btn-action-icon {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: #f1f5f9;
    color: #64748b;
    border: 1px solid #e2e8f0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    transition: all 0.2s ease;
}
.btn-action-icon:hover {
    background: #e2e8f0;
    color: #0f172a;
}

/* 3. Modern Card & Forms */
.modern-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: var(--contact-border-radius);
    box-shadow: var(--contact-card-shadow);
}

.form-header-badge {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: #ecfdf5;
    font-size: 18px;
}

.form-label-modern {
    font-size: 13px;
    font-weight: 700;
    color: #334155;
    margin-bottom: 6px;
    display: block;
}

.modern-input-group {
    position: relative;
    display: flex;
    align-items: center;
}

.modern-input-group .input-icon {
    position: absolute;
    left: 14px;
    color: #94a3b8;
    font-size: 15px;
    pointer-events: none;
    z-index: 4;
}

.modern-input {
    background-color: #f8fafc;
    border: 1.5px solid #e2e8f0;
    border-radius: 12px !important;
    padding: 10px 14px 10px 42px;
    font-size: 14px;
    color: #1e293b;
    transition: all 0.2s ease;
    height: 46px;
}
.modern-input:focus {
    background-color: #ffffff;
    border-color: #006a4e;
    box-shadow: 0 0 0 4px rgba(0, 106, 78, 0.12);
    outline: none;
}

.textarea-input {
    height: auto !important;
    padding-left: 14px;
}

.btn-submit-modern {
    background: linear-gradient(135deg, #006a4e 0%, #004d40 100%);
    color: #ffffff;
    font-weight: 800;
    font-size: 14px;
    border-radius: 9999px;
    padding: 12px 28px;
    border: none;
    box-shadow: 0 4px 14px rgba(0, 106, 78, 0.25);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all 0.2s ease;
}
.btn-submit-modern:hover {
    background: linear-gradient(135deg, #005a43 0%, #00382e 100%);
    color: #ffffff;
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0, 106, 78, 0.35);
}

.btn-whatsapp-direct {
    background: #10b981;
    color: #ffffff;
    font-weight: 800;
    font-size: 14px;
    border-radius: 9999px;
    padding: 12px 24px;
    border: none;
    box-shadow: 0 4px 14px rgba(16, 185, 129, 0.25);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all 0.2s ease;
}
.btn-whatsapp-direct:hover {
    background: #059669;
    color: #ffffff;
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(16, 185, 129, 0.35);
}

/* Department Directory */
.dept-main-icon {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    background: #ecfdf5;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
}

.dept-item-box {
    background: #f8fafc;
    border: 1px solid #edf2f7;
    transition: all 0.2s ease;
}
.dept-item-box:hover {
    background: #ffffff;
    border-color: #cbd5e1;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
}

.dept-badge-icon {
    width: 26px;
    height: 26px;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
}

.dept-contact-link {
    font-size: 13px;
    font-weight: 700;
    color: #006a4e;
    text-decoration: none;
}
.dept-contact-link:hover {
    text-decoration: underline;
    color: #004d40;
}

/* FAQ Callout */
.faq-callout-card {
    background: linear-gradient(135deg, #05192d 0%, #004d40 100%);
    box-shadow: 0 8px 24px rgba(0, 77, 64, 0.2);
}

.faq-callout-icon {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.15);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fcd34d;
}

.btn-faq-link {
    background: #ffffff;
    color: #0f172a;
    font-weight: 700;
    font-size: 13px;
    border-radius: 9999px;
    padding: 8px 18px;
    border: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s ease;
    text-decoration: none;
}
.btn-faq-link:hover {
    background: #f1f5f9;
    color: #006a4e;
    transform: translateY(-1px);
}

/* 4. Location & Map Visual Box */
.location-item-icon {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 15px;
}

.map-visual-box {
    background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
    border: 1px solid #cbd5e1;
    min-height: 360px;
}

.map-icon-halo {
    width: 72px;
    height: 72px;
    border-radius: 50%;
    background: #ecfdf5;
    color: #006a4e;
    font-size: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 8px 24px rgba(0, 106, 78, 0.12);
}

.btn-emerald-solid {
    background: #006a4e;
    color: #ffffff;
    padding: 10px 20px;
    font-size: 13.5px;
    border: none;
    transition: all 0.2s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.btn-emerald-solid:hover {
    background: #004d40;
    color: #ffffff;
    transform: translateY(-1px);
}

/* 5. Modern Accordion */
.modern-accordion-item {
    background: #ffffff;
    border: 1px solid #e2e8f0 !important;
    border-radius: 14px !important;
    margin-bottom: 12px;
    overflow: hidden;
    box-shadow: 0 2px 6px rgba(15, 23, 42, 0.02);
}

.modern-accordion-btn {
    font-size: 14.5px;
    font-weight: 700;
    color: #1e293b;
    padding: 16px 20px;
    background: #ffffff;
    box-shadow: none !important;
}
.modern-accordion-btn:not(.collapsed) {
    background-color: #f8fafc;
    color: #006a4e;
}

.modern-accordion-body {
    font-size: 14px;
    line-height: 1.7;
    color: #475569;
    padding: 16px 20px 20px;
    background-color: #ffffff;
    border-top: 1px solid #f1f5f9;
}

/* Responsive Tweaks */
@media (max-width: 767.98px) {
    .quick-channels-container {
        margin-top: -24px;
    }
    .hero-title {
        font-size: 1.75rem;
    }
}
</style>

{{-- ========================================================================= --}}
{{-- JAVASCRIPT: Dynamic Time, AJAX Submission, WhatsApp, and Clipboard Toast   --}}
{{-- ========================================================================= --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    updateOfficeStatus();
    setInterval(updateOfficeStatus, 60000);

    // Form Handling with Smooth AJAX & Live Feedback
    const form = document.getElementById('mainContactForm');
    const submitBtn = document.getElementById('contactSubmitBtn');
    const alertBox = document.getElementById('contactFormAlert');

    if (form) {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();

            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1.5" role="status"></span><span>বার্তা পাঠানো হচ্ছে...</span>';
            }

            const formData = new FormData(form);

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: formData
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    alertBox.className = 'alert alert-success py-3 px-3.5 rounded-4 small mb-4 border-0 bg-emerald-50 text-emerald-800 d-flex align-items-center gap-2';
                    alertBox.innerHTML = '<i class="fa-solid fa-circle-check text-emerald-600 fs-5 flex-shrink-0"></i><div>' + data.message + '</div>';
                    alertBox.classList.remove('d-none');
                    form.reset();
                    updateCharCount(document.getElementById('contactMessage'));
                    alertBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                } else {
                    alertBox.className = 'alert alert-danger py-3 px-3.5 rounded-4 small mb-4 border-0 bg-rose-50 text-rose-800 d-flex align-items-center gap-2';
                    alertBox.innerHTML = '<i class="fa-solid fa-circle-exclamation text-rose-600 fs-5 flex-shrink-0"></i><div>' + (data.message || 'বার্তা পাঠাতে সমস্যা হয়েছে। অনুগ্রহ করে প্রয়োজনীয় তথ্য পূরণ করুন।') + '</div>';
                    alertBox.classList.remove('d-none');
                }
            } catch (err) {
                console.warn('Submitting via standard POST fallback due to network/cors:', err);
                form.submit();
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fa-solid fa-paper-plane"></i><span>বার্তা পাঠান</span>';
                }
            }
        });
    }
});

function updateCharCount(textarea) {
    const counter = document.getElementById('charCount');
    if (counter && textarea) {
        const len = textarea.value.length;
        counter.textContent = `${toBengaliDigits(len)} / ৩০০০`;
    }
}

function toBengaliDigits(num) {
    const banglaNums = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
    return String(num).replace(/[0-9]/g, function(d) {
        return banglaNums[d];
    });
}

function updateOfficeStatus() {
    const badge = document.getElementById('liveOfficeStatusBadge');
    if (!badge) return;

    const now = new Date();
    // Bangladesh is UTC+6
    const utc = now.getTime() + (now.getTimezoneOffset() * 60000);
    const bdTime = new Date(utc + (3600000 * 6));
    
    const day = bdTime.getDay(); // 0 is Sunday, 5 is Friday
    const hour = bdTime.getHours();

    // Sat(6) to Thu(4): 9 AM to 11 PM (23:00)
    const isFriday = (day === 5);
    const isOpenHours = (hour >= 9 && hour < 23);

    if (isFriday) {
        badge.className = 'badge bg-indigo-100 text-indigo-800 rounded-pill px-2.5 py-0.5 small fw-bold';
        badge.textContent = 'অনলাইন সাপোর্ট সচল (শুক্রবার)';
    } else if (isOpenHours) {
        badge.className = 'badge bg-emerald-100 text-emerald-800 rounded-pill px-2.5 py-0.5 small fw-bold';
        badge.textContent = 'এখন খোলা রয়েছে';
    } else {
        badge.className = 'badge bg-slate-200 text-slate-700 rounded-pill px-2.5 py-0.5 small fw-bold';
        badge.textContent = 'অনলাইন হেল্পডেস্ক সচল';
    }
}

function sendViaWhatsApp() {
    const name = document.getElementById('contactName')?.value.trim() || 'গ্রাহক';
    const phone = document.getElementById('contactPhone')?.value.trim() || '';
    const subject = document.getElementById('contactSubject')?.value || 'সাধারণ জিজ্ঞাসা';
    const message = document.getElementById('contactMessage')?.value.trim() || '';

    const text = `আইডিয়া প্রকাশন কাস্টমার সাপোর্ট:\n\n👤 নাম: ${name}\n📞 ফোন: ${phone}\n📌 বিষয়শ্রেণী: ${subject}\n💬 বার্তা: ${message}`;
    const rawNumber = '{{ preg_replace('/[^0-9]/', '', $whatsappNum) }}';
    const url = `https://wa.me/${rawNumber}?text=${encodeURIComponent(text)}`;
    window.open(url, '_blank');
}

function copyContactText(text, label) {
    if (!navigator.clipboard) {
        const temp = document.createElement('input');
        temp.value = text;
        document.body.appendChild(temp);
        temp.select();
        document.execCommand('copy');
        document.body.removeChild(temp);
        showCopyToast(text, label);
        return;
    }

    navigator.clipboard.writeText(text).then(() => {
        showCopyToast(text, label);
    });
}

function showCopyToast(text, label) {
    const existing = document.getElementById('copyContactToast');
    if (existing) existing.remove();

    const toast = document.createElement('div');
    toast.id = 'copyContactToast';
    toast.className = 'position-fixed bottom-0 end-0 m-3 m-md-4 p-3 rounded-4 shadow-2xl d-flex align-items-center gap-2.5 border border-slate-700';
    toast.style.background = '#0f172a';
    toast.style.color = '#ffffff';
    toast.style.zIndex = '999999';
    toast.style.fontSize = '13.5px';
    toast.style.animation = 'fadeInUp 0.3s ease';
    toast.innerHTML = `
        <div class="rounded-circle bg-emerald-500 bg-opacity-20 text-emerald-400 p-1.5 d-flex align-items-center justify-content-center">
            <i class="fa-solid fa-circle-check fs-5"></i>
        </div>
        <div>
            <strong class="d-block text-white" style="font-size: 13px;">${label} কপি করা হয়েছে!</strong>
            <span class="text-slate-400 font-monospace" style="font-size: 12px;">${text}</span>
        </div>
    `;
    document.body.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(10px)';
        toast.style.transition = 'all 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}
</script>
@endsection
