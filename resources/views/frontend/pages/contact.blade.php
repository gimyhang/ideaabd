@extends('layouts.app')
@section('title', 'যোগাযোগ ও সহায়তা — আইডিয়া প্রকাশন')
@section('meta_description', 'আইডিয়া প্রকাশন কাস্টমার কেয়ার, বই অর্ডার, লেখক প্রকাশনা, পাইকারি বুকশপ ও হেল্পলাইন। সরাসরি কথা বলুন বা বার্তা পাঠান।')

@section('content')
<div class="contact-page-wrapper bg-light min-vh-100 pb-5">

    {{-- 1. Hero Header --}}
    <section class="contact-hero-section position-relative overflow-hidden text-white py-5" 
             style="background: linear-gradient(135deg, #07192f 0%, #004d40 60%, #006a4e 100%);">
        <div class="position-absolute top-0 end-0 opacity-10 pe-none d-none d-md-block" style="transform: translate(15%, -20%);">
            <i class="fa-solid fa-headset" style="font-size: 380px;"></i>
        </div>

        <div class="container position-relative z-1">
            {{-- Breadcrumb --}}
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb mb-0 small" style="--bs-breadcrumb-divider: '›';">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-white-50 text-decoration-none"><i class="fa-solid fa-house me-1"></i>Home</a></li>
                    <li class="breadcrumb-item text-white active" aria-current="page">Contact</li>
                </ol>
            </nav>

            <div class="row align-items-center g-4">
                <div class="col-lg-8">
                    <div class="d-inline-flex align-items-center gap-2 bg-white bg-opacity-15 backdrop-blur rounded-pill px-3.5 py-1 mb-3 fw-bold small text-white border border-white border-opacity-20 shadow-sm">
                        <i class="fa-solid fa-headset text-warning"></i>
                        <span>২৪/৭ ডেডিকেটেড কাস্টমার সাপোর্ট ও হেল্পডেস্ক</span>
                    </div>
                    <h1 class="fw-black text-white display-6 mb-3 lh-sm" style="letter-spacing: -0.5px;">
                        যোগাযোগ ও সহায়তা কেন্দ্র
                    </h1>
                    <p class="text-white-50 lead mb-4" style="font-size: 16px; line-height: 1.7; max-width: 650px;">
                        বই অর্ডার, প্রকাশনা সেবা, লেখক পান্ডুলিপি জমা, পাইকারি বুকশপ ডিস্ট্রিবিউশন বা যেকোনো প্রয়োজনে আমাদের সাথে সরাসরি যোগাযোগ করুন।
                    </p>
                </div>

                <div class="col-lg-4 text-lg-end">
                    <div class="p-3.5 rounded-4 bg-white bg-opacity-10 backdrop-blur border border-white border-opacity-15 shadow-sm text-start text-lg-end d-inline-block">
                        <div class="d-flex align-items-center gap-2.5 justify-content-lg-end mb-1">
                            <span class="position-relative d-flex h-3 w-3" style="width: 10px; height: 10px;">
                                <span class="animate-ping position-absolute h-100 w-100 rounded-circle bg-success opacity-75"></span>
                                <span class="position-relative rounded-circle bg-success" style="width: 10px; height: 10px;"></span>
                            </span>
                            <span class="small fw-bold text-white">সাপোর্ট টিম এখন অনলাইন</span>
                        </div>
                        <small class="text-white-50 d-block">গড় রিপ্লাই সময়: <strong class="text-warning">১৫–৩০ মিনিট</strong></small>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- 2. Four Quick Reach Highlight Cards --}}
    <section class="container mt-n4 position-relative z-2">
        <div class="row g-3 g-md-4">

            {{-- 1. Phone Helpline --}}
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card border-0 rounded-4 p-3.5 bg-white shadow-sm h-100 d-flex flex-column justify-content-between transition-all hover-translate">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-2.5">
                            <div class="rounded-3 bg-warning bg-opacity-15 text-dark p-2.5 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                                <i class="fa-solid fa-phone fs-5 text-warning"></i>
                            </div>
                            <span class="badge bg-warning bg-opacity-15 text-dark fw-bold small">সরাসরি কল</span>
                        </div>
                        <h6 class="fw-bold text-dark mb-1">হটলাইন হেল্পলাইন</h6>
                        <p class="text-muted small mb-3">শনি–বৃহস্পতি (সকাল ৯টা – রাত ১১টা)</p>
                    </div>
                    <div class="d-flex align-items-center gap-2 pt-2 border-top">
                        <a href="tel:+8801726976982" class="btn btn-warning btn-sm rounded-pill fw-bold flex-grow-1 text-dark">
                            <i class="fa-solid fa-phone me-1"></i> কল করুন
                        </a>
                        <button type="button" class="btn btn-light btn-sm rounded-pill px-2.5" onclick="copyContactText('+8801726976982', 'ফোন নম্বর')" title="নম্বর কপি করুন">
                            <i class="fa-solid fa-copy"></i>
                        </button>
                    </div>
                </div>
            </div>

            {{-- 2. WhatsApp Chat --}}
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card border-0 rounded-4 p-3.5 bg-white shadow-sm h-100 d-flex flex-column justify-content-between transition-all hover-translate">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-2.5">
                            <div class="rounded-3 bg-success bg-opacity-10 text-success p-2.5 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                                <i class="fa-brands fa-whatsapp fs-4"></i>
                            </div>
                            <span class="badge bg-success bg-opacity-10 text-success fw-bold small">তাৎক্ষণিক চ্যাট</span>
                        </div>
                        <h6 class="fw-bold text-dark mb-1">অফিসিয়াল হোয়াটসঅ্যাপ</h6>
                        <p class="text-muted small mb-3">মেসেজ ড্রপ করুন যেকোনো সময়</p>
                    </div>
                    <div class="d-flex align-items-center gap-2 pt-2 border-top">
                        <a href="https://wa.me/8801726976982" target="_blank" class="btn btn-success btn-sm rounded-pill fw-bold flex-grow-1">
                            <i class="fa-brands fa-whatsapp me-1"></i> চ্যাট করুন
                        </a>
                        <button type="button" class="btn btn-light btn-sm rounded-pill px-2.5" onclick="copyContactText('+8801726976982', 'হোয়াটসঅ্যাপ')" title="কপি করুন">
                            <i class="fa-solid fa-copy"></i>
                        </button>
                    </div>
                </div>
            </div>

            {{-- 3. Email Support --}}
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card border-0 rounded-4 p-3.5 bg-white shadow-sm h-100 d-flex flex-column justify-content-between transition-all hover-translate">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-2.5">
                            <div class="rounded-3 bg-info bg-opacity-10 text-info p-2.5 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                                <i class="fa-solid fa-envelope fs-5"></i>
                            </div>
                            <span class="badge bg-info bg-opacity-10 text-info fw-bold small">ইমেইল ডেস্ক</span>
                        </div>
                        <h6 class="fw-bold text-dark mb-1">অফিশিয়াল মেইল</h6>
                        <p class="text-muted small mb-3 text-truncate">ideapbd@gmail.com</p>
                    </div>
                    <div class="d-flex align-items-center gap-2 pt-2 border-top">
                        <a href="mailto:ideapbd@gmail.com" class="btn btn-outline-primary btn-sm rounded-pill fw-bold flex-grow-1">
                            <i class="fa-solid fa-paper-plane me-1"></i> মেইল লিখুন
                        </a>
                        <button type="button" class="btn btn-light btn-sm rounded-pill px-2.5" onclick="copyContactText('ideapbd@gmail.com', 'ইমেইল')" title="কপি করুন">
                            <i class="fa-solid fa-copy"></i>
                        </button>
                    </div>
                </div>
            </div>

            {{-- 4. Corporate Office --}}
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card border-0 rounded-4 p-3.5 bg-white shadow-sm h-100 d-flex flex-column justify-content-between transition-all hover-translate">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-2.5">
                            <div class="rounded-3 bg-danger bg-opacity-10 text-danger p-2.5 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                                <i class="fa-solid fa-location-dot fs-5"></i>
                            </div>
                            <span class="badge bg-secondary bg-opacity-15 text-dark fw-bold small">হেড অফিস</span>
                        </div>
                        <h6 class="fw-bold text-dark mb-1">প্রধান কার্যালয়</h6>
                        <p class="text-muted small mb-3">ঢাকা, বাংলাদেশ</p>
                    </div>
                    <div class="d-flex align-items-center gap-2 pt-2 border-top">
                        <a href="#officeLocationMap" class="btn btn-outline-secondary btn-sm rounded-pill fw-bold flex-grow-1">
                            <i class="fa-solid fa-map-location-dot me-1"></i> ম্যাপ দেখুন
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </section>

    {{-- 3. Main Contact Content (Interactive Form + Department Direct Directory) --}}
    <section class="container mt-4 pt-3">
        <div class="row g-4">

            {{-- Left Column: Interactive Contact Form --}}
            <div class="col-12 col-lg-7">
                <div class="card border-0 rounded-4 shadow-sm p-4 p-md-4.5 bg-white position-relative overflow-hidden h-100">
                    <div class="d-flex align-items-center justify-content-between pb-3 mb-3.5 border-bottom">
                        <div>
                            <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-1 fw-bold small mb-1.5">
                                <i class="fa-solid fa-pen-to-square me-1"></i> সরাসরি যোগাযোগ ফর্ম
                            </span>
                            <h3 class="fw-black text-dark mb-0 fs-4">আমাদের বার্তা পাঠান</h3>
                        </div>
                        <span class="rounded-circle p-2.5 bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px;">
                            <i class="fa-solid fa-paper-plane fs-5"></i>
                        </span>
                    </div>

                    {{-- Form Alert Message Box --}}
                    <div id="contactFormAlert" class="alert d-none py-2.5 px-3 rounded-3 small mb-3 border-0"></div>

                    @if(session('success'))
                        <div class="alert alert-success py-2.5 px-3 rounded-3 small mb-3 border-0 bg-success bg-opacity-10 text-success">
                            <i class="fa-solid fa-circle-check me-1"></i> {{ session('success') }}
                        </div>
                    @endif

                    <form action="{{ route('contact.submit') }}" method="POST" id="mainContactForm">
                        @csrf

                        <div class="row g-3">
                            {{-- Full Name --}}
                            <div class="col-12 col-sm-6">
                                <label for="contactName" class="form-label fw-bold text-dark small mb-1.5">
                                    আপনার নাম <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-solid fa-user"></i></span>
                                    <input type="text" name="name" id="contactName" class="form-control bg-light bg-opacity-50 border-start-0" 
                                           placeholder="পূর্ণ নাম লিখুন..." required value="{{ old('name', auth()->user()->name ?? '') }}">
                                </div>
                            </div>

                            {{-- Mobile / WhatsApp --}}
                            <div class="col-12 col-sm-6">
                                <label for="contactPhone" class="form-label fw-bold text-dark small mb-1.5">
                                    মোবাইল / হোয়াটসঅ্যাপ নম্বর <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-solid fa-phone"></i></span>
                                    <input type="tel" name="phone" id="contactPhone" class="form-control bg-light bg-opacity-50 border-start-0" 
                                           placeholder="017xxxxxxxx" required value="{{ old('phone', auth()->user()->phone ?? '') }}">
                                </div>
                            </div>

                            {{-- Email --}}
                            <div class="col-12 col-sm-6">
                                <label for="contactEmail" class="form-label fw-bold text-dark small mb-1.5">
                                    ইমেইল ঠিকানা (ঐচ্ছিক)
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-solid fa-envelope"></i></span>
                                    <input type="email" name="email" id="contactEmail" class="form-control bg-light bg-opacity-50 border-start-0" 
                                           placeholder="example@mail.com" value="{{ old('email', auth()->user()->email ?? '') }}">
                                </div>
                            </div>

                            {{-- Subject Topic --}}
                            <div class="col-12 col-sm-6">
                                <label for="contactSubject" class="form-label fw-bold text-dark small mb-1.5">
                                    বার্তার বিষয়শ্রেণী <span class="text-danger">*</span>
                                </label>
                                <select name="subject" id="contactSubject" class="form-select bg-light bg-opacity-50" required>
                                    <option value="" disabled selected>বিষয় নির্বাচন করুন...</option>
                                    <option value="বই অর্ডার ও ডেলিভারি জিজ্ঞাসা">📚 বই অর্ডার ও ডেলিভারি জিজ্ঞাসা</option>
                                    <option value="লেখক প্রকাশনা ও পান্ডুলিপি জমা">✍️ লেখক প্রকাশনা ও পান্ডুলিপি জমা</option>
                                    <option value="সেলার ও পাইকারি বুকশপ পার্টনারশিপ">💼 সেলার ও পাইকারি বুকশপ</option>
                                    <option value="পেমেন্ট, ইনভয়েস ও রয়্যালটি">💳 পেমেন্ট, ইনভয়েস ও রয়্যালটি</option>
                                    <option value="ই-বুক ও ডিজিটাল লাইব্রেরি">📱 ই-বুক ও ডিজিটাল রিডার</option>
                                    <option value="অন্যান্য পরামর্শ ও অভিযোগ">💬 অন্যান্য সাধারণ অনুসন্ধান</option>
                                </select>
                            </div>

                            {{-- Message Content --}}
                            <div class="col-12">
                                <label for="contactMessage" class="form-label fw-bold text-dark small mb-1.5">
                                    আপনার বার্তা বা বিস্তারিত বিবরণ <span class="text-danger">*</span>
                                </label>
                                <textarea name="message" id="contactMessage" rows="5" class="form-control bg-light bg-opacity-50" 
                                          placeholder="আপনার প্রয়োজনীয় তথ্য, বইয়ের নাম বা প্রশ্ন বিস্তারিত লিখুন..." required></textarea>
                            </div>

                            {{-- Submit Buttons --}}
                            <div class="col-12 d-flex flex-column flex-sm-row align-items-center gap-2.5 pt-2">
                                <button type="submit" class="btn btn-primary rounded-pill px-4 py-2.5 fw-bold shadow-xs d-inline-flex align-items-center justify-content-center gap-2 w-100 w-sm-auto" id="contactSubmitBtn">
                                    <i class="fa-solid fa-paper-plane"></i>
                                    <span>বার্তা পাঠান</span>
                                </button>
                                <button type="button" class="btn btn-success rounded-pill px-4 py-2.5 fw-bold shadow-xs d-inline-flex align-items-center justify-content-center gap-2 w-100 w-sm-auto" onclick="sendViaWhatsApp()">
                                    <i class="fa-brands fa-whatsapp fs-5"></i>
                                    <span>হোয়াটসঅ্যাপে পাঠান</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Right Column: Department Direct Directory & FAQ Link --}}
            <div class="col-12 col-lg-5">
                <div class="d-flex flex-column gap-3">

                    {{-- 1. Department Contacts Directory --}}
                    <div class="card border-0 rounded-4 shadow-sm p-4 bg-white">
                        <div class="d-flex align-items-center gap-2.5 mb-3.5 pb-2 border-bottom">
                            <i class="fa-solid fa-sitemap text-primary fs-5"></i>
                            <h5 class="fw-bold text-dark mb-0">বিভাগভিত্তিক সরাসরি হেল্পডেস্ক</h5>
                        </div>

                        <div class="d-flex flex-column gap-3">
                            {{-- Editorial Desk --}}
                            <div class="p-3 rounded-3 bg-light border transition-all hover-translate">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <span class="fw-bold text-dark small"><i class="fa-solid fa-feather-pointed me-1.5 text-success"></i> প্রকাশনা ও সম্পাদকীয় বিভাগ</span>
                                    <span class="badge bg-success bg-opacity-10 text-success small">পান্ডুলিপি</span>
                                </div>
                                <div class="small text-muted mb-2">নতুন বই প্রকাশ ও পান্ডুলিপি মূল্যায়নের জন্য যোগাযোগ করুন।</div>
                                <div class="small fw-semibold text-primary font-monospace">ideapbd@gmail.com</div>
                            </div>

                            {{-- Wholesale Desk --}}
                            <div class="p-3 rounded-3 bg-light border transition-all hover-translate">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <span class="fw-bold text-dark small"><i class="fa-solid fa-store me-1.5 text-warning"></i> বিক্রয় ও পরিবেশনা বিভাগ</span>
                                    <span class="badge bg-warning bg-opacity-15 text-dark small">বুকশপ</span>
                                </div>
                                <div class="small text-muted mb-2">লাইব্রেরি ও বুকশপের পাইকারি বই সরবরাহ ও কমিশন সংক্রান্ত।</div>
                                <div class="small fw-semibold text-dark font-monospace">+88 01726976982</div>
                            </div>

                            {{-- Accounts & Royalty --}}
                            <div class="p-3 rounded-3 bg-light border transition-all hover-translate">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <span class="fw-bold text-dark small"><i class="fa-solid fa-wallet me-1.5 text-info"></i> হিসাব ও লেখক রয়্যালটি বিভাগ</span>
                                    <span class="badge bg-info bg-opacity-10 text-info small">রয়্যালটি</span>
                                </div>
                                <div class="small text-muted mb-2">লেখক সম্মানী, ব্যাংক সেটেলমেন্ট ও ইনভয়েস স্টেটমেন্ট।</div>
                                <div class="small fw-semibold text-primary font-monospace">ideapbd@gmail.com</div>
                            </div>
                        </div>
                    </div>

                    {{-- 2. FAQ Quick CTA Box --}}
                    <div class="card border-0 rounded-4 p-4 shadow-sm text-white position-relative overflow-hidden" 
                         style="background: linear-gradient(135deg, #07192f 0%, #004d40 100%);">
                        <div class="d-flex align-items-start gap-3">
                            <span class="rounded-circle p-2.5 bg-white bg-opacity-15 text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                                <i class="fa-solid fa-circle-question fs-5 text-warning"></i>
                            </span>
                            <div>
                                <h6 class="fw-bold text-white mb-1">সাধারণ জিজ্ঞাসা রয়েছে?</h6>
                                <p class="text-white-50 small mb-3">আমাদের FAQ সেকশনে বই অর্ডার, ডেলিভারি ও প্রকাশনার সর্বাধিক জিজ্ঞাসিত প্রশ্নের তাৎক্ষণিক উত্তর রয়েছে।</p>
                                <a href="{{ route('faq') }}" class="btn btn-light btn-sm rounded-pill fw-bold px-3 text-dark">
                                    FAQ দেখুন →
                                </a>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </section>

    {{-- 4. Location & Working Schedule Section --}}
    <section class="container mt-4 pt-2" id="officeLocationMap">
        <div class="card border-0 rounded-4 shadow-sm p-4 p-lg-5 bg-white">
            <div class="row g-4 align-items-center">
                <div class="col-lg-6">
                    <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3 py-1 fw-bold small mb-2">
                        <i class="fa-solid fa-location-dot me-1"></i> প্রধান কার্যালয়
                    </span>
                    <h3 class="fw-bold text-dark mb-3">আমাদের কার্যালয়ে স্বাগতম</h3>
                    <p class="text-muted small mb-4" style="line-height: 1.7;">
                        আইডিয়া প্রকাশন বাংলাদেশের অগ্রণী সাহিত্য ও গবেষণা প্রকাশনা প্রতিষ্ঠান। লেখক ও পাঠকদের সুবিধার্থে আমাদের হেড অফিসে রয়েছে বই প্রদর্শনী ও পান্ডুলিপি পরামর্শ ডেস্ক।
                    </p>

                    <div class="d-flex flex-column gap-3 mb-4">
                        <div class="d-flex align-items-start gap-3">
                            <div class="rounded-circle bg-light p-2 text-primary d-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px;">
                                <i class="fa-solid fa-building"></i>
                            </div>
                            <div>
                                <strong class="text-dark small d-block">ঠিকানা:</strong>
                                <span class="text-muted small">আইডিয়া প্রকাশন, ঢাকা, বাংলাদেশ</span>
                            </div>
                        </div>

                        <div class="d-flex align-items-start gap-3">
                            <div class="rounded-circle bg-light p-2 text-warning d-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px;">
                                <i class="fa-solid fa-clock"></i>
                            </div>
                            <div>
                                <strong class="text-dark small d-block">কার্যকাল ও সময়সূচী:</strong>
                                <span class="text-muted small">শনিবার – বৃহস্পতিবার: সকাল ৯:০০ টা থেকে রাত ১১:০০ টা (শুক্রবার অনলাইন সাপোর্ট চালু)</span>
                            </div>
                        </div>

                        <div class="d-flex align-items-start gap-3">
                            <div class="rounded-circle bg-light p-2 text-success d-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px;">
                                <i class="fa-solid fa-envelope"></i>
                            </div>
                            <div>
                                <strong class="text-dark small d-block">অফিশিয়াল ইমেইল:</strong>
                                <span class="text-muted small">ideapbd@gmail.com</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Visual Map / Direction Card --}}
                <div class="col-lg-6">
                    <div class="rounded-4 overflow-hidden border p-4 text-center bg-light position-relative" style="min-height: 280px;">
                        <div class="py-4">
                            <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-3 d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px;">
                                <i class="fa-solid fa-map-location-dot fs-2"></i>
                            </div>
                            <h5 class="fw-bold text-dark mb-1">আইডিয়া প্রকাশন হেড অফিস</h5>
                            <p class="text-muted small mb-4">ঢাকা, বাংলাদেশ</p>
                            <div class="d-flex align-items-center justify-content-center gap-2 flex-wrap">
                                <a href="https://maps.google.com/?q=Dhaka,Bangladesh" target="_blank" class="btn btn-primary rounded-pill px-4 fw-bold shadow-xs">
                                    <i class="fa-solid fa-diamond-turn-right me-1"></i> গুগল ম্যাপে দেখুন
                                </a>
                                <button type="button" class="btn btn-outline-secondary rounded-pill px-3.5 fw-bold" onclick="copyContactText('আইডিয়া প্রকাশন, ঢাকা, বাংলাদেশ', 'অফিসের ঠিকানা')">
                                    <i class="fa-solid fa-copy me-1"></i> ঠিকানা কপি করুন
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

</div>

{{-- Styles --}}
<style>
.hover-translate:hover {
    transform: translateY(-4px);
    border-color: #006a4e !important;
    box-shadow: 0 12px 24px -6px rgba(15, 23, 42, 0.1) !important;
}
.backdrop-blur {
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
}
.mt-n4 {
    margin-top: -1.75rem !important;
}
</style>

{{-- Script --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('mainContactForm');
    const submitBtn = document.getElementById('contactSubmitBtn');
    const alertBox = document.getElementById('contactFormAlert');

    if (form) {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();

            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> বার্তা পাঠানো হচ্ছে...';
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
                    alertBox.className = 'alert alert-success py-2.5 px-3 rounded-3 small mb-3 border-0 bg-success bg-opacity-10 text-success';
                    alertBox.innerHTML = '<i class="fa-solid fa-circle-check me-1"></i> ' + data.message;
                    alertBox.classList.remove('d-none');
                    form.reset();
                    alertBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                } else {
                    alertBox.className = 'alert alert-danger py-2.5 px-3 rounded-3 small mb-3 border-0 bg-danger bg-opacity-10 text-danger';
                    alertBox.textContent = data.message || 'বার্তা পাঠাতে সমস্যা হয়েছে। অনুগ্রহ করে সকল তথ্য সঠিকভাবে পূরণ করুন।';
                    alertBox.classList.remove('d-none');
                }
            } catch(err) {
                console.error('Contact form error:', err);
                form.submit();
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fa-solid fa-paper-plane"></i> <span>বার্তা পাঠান</span>';
                }
            }
        });
    }
});

function sendViaWhatsApp() {
    const name = document.getElementById('contactName')?.value.trim() || 'গ্রাহক';
    const phone = document.getElementById('contactPhone')?.value.trim() || '';
    const subject = document.getElementById('contactSubject')?.value || 'সাধারণ অনুসন্ধান';
    const message = document.getElementById('contactMessage')?.value.trim() || '';

    const text = `আইডিয়া প্রকাশন কাস্টমার সাপোর্ট:\n\n👤 নাম: ${name}\n📞 ফোন: ${phone}\n📌 বিষয়: ${subject}\n💬 বার্তা: ${message}`;
    const url = `https://wa.me/8801726976982?text=${encodeURIComponent(text)}`;
    window.open(url, '_blank');
}

function copyContactText(text, label) {
    navigator.clipboard.writeText(text).then(() => {
        const toast = document.createElement('div');
        toast.className = 'position-fixed bottom-0 end-0 m-3 p-3 bg-dark text-white rounded-4 shadow-2xl d-flex align-items-center gap-2 border border-secondary border-opacity-50';
        toast.style.zIndex = '999999';
        toast.innerHTML = `<i class="fa-solid fa-circle-check text-success fs-5"></i><span>${label} সফলভাবে কপি করা হয়েছে: <strong>${text}</strong></span>`;
        document.body.appendChild(toast);
        setTimeout(() => { 
            toast.style.opacity = '0';
            toast.style.transition = 'opacity 0.4s ease';
            setTimeout(() => toast.remove(), 400);
        }, 3000);
    });
}
</script>
@endsection
