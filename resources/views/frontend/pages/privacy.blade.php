@extends('layouts.app')

@section('title', 'গোপনীয়তা নীতিমালা (Privacy Policy) — আইডিয়া প্রকাশন')
@section('meta_description', 'আইডিয়া প্রকাশন (ideaabd.com) গ্রাহক, লেখক ও পাঠকদের ব্যক্তিগত তথ্যের সর্বোচ্চ নিরাপত্তা এবং গোপনীয়তা বজায় রাখতে প্রতিশ্রুতিবদ্ধ। আমাদের পূর্ণাঙ্গ ডেটা পলিসি পড়ুন।')

@section('content')
<div class="privacy-page-wrapper bg-light min-vh-100 pb-5">

    {{-- ══════════════════════════════════════════════════════════════════
         1. HERO HEADER SECTION
    ══════════════════════════════════════════════════════════════════ --}}
    <section class="privacy-hero position-relative overflow-hidden text-white py-5" 
             style="background: linear-gradient(135deg, #091a28 0%, #064e3b 50%, #047857 100%);">
        
        {{-- Background decorative icon --}}
        <div class="position-absolute top-0 end-0 opacity-10 pe-none d-none d-lg-block" style="transform: translate(15%, -15%);">
            <i class="fa-solid fa-user-shield" style="font-size: 380px;"></i>
        </div>

        <div class="container position-relative z-1">
            {{-- Breadcrumb --}}
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb mb-0 small" style="--bs-breadcrumb-divider: '›';">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-white-50 text-decoration-none"><i class="fa-solid fa-house me-1"></i>হোম</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('terms') }}" class="text-white-50 text-decoration-none">ব্যবহারের শর্তাবলী</a></li>
                    <li class="breadcrumb-item text-white active" aria-current="page">গোপনীয়তা নীতিমালা</li>
                </ol>
            </nav>

            <div class="row align-items-center g-4">
                <div class="col-lg-8">
                    <div class="d-inline-flex align-items-center gap-2 bg-white bg-opacity-15 backdrop-blur rounded-pill px-3.5 py-1 mb-3 fw-bold small text-white border border-white border-opacity-20 shadow-sm">
                        <i class="fa-solid fa-shield-halved text-warning"></i>
                        <span>গ্রাহক তথ্য সুরক্ষা ও ডেটা প্রাইভেসি ফ্রেমওয়ার্ক</span>
                    </div>
                    <h1 class="fw-black mb-3 text-white display-6 lh-sm" style="letter-spacing: -0.5px;">
                        গোপনীয়তা নীতিমালা (Privacy Policy)
                    </h1>
                    <p class="text-white-50 lead mb-4" style="font-size: 16px; line-height: 1.7; max-width: 680px;">
                        আইডিয়া প্রকাশন (ideaabd.com) আপনার তথ্যের মর্যাদা ও নিরাপত্তার প্রতি পূর্ণ শ্রদ্ধাশীল। আমরা আপনার তথ্য কীভাবে সংগ্রহ, প্রক্রিয়াকরণ ও সুরক্ষিত রাখি তা স্বচ্ছভাবে নিচে তুলে ধরা হলো।
                    </p>

                    <div class="d-flex flex-wrap align-items-center gap-2 pt-1">
                        <span class="badge bg-white bg-opacity-20 text-white border border-white border-opacity-20 px-3 py-2 rounded-pill font-monospace" style="font-size: 12px;">
                            <i class="fa-regular fa-clock me-1"></i> কার্যকর সংস্করণ: সেপ্টেম্বর ২০২৬
                        </span>
                        <span class="badge bg-emerald-500 text-white px-3 py-2 rounded-pill" style="background: #10b981; font-size: 12px;">
                            <i class="fa-solid fa-lock me-1"></i> ২৫৬-বিট SSL সুরক্ষিত
                        </span>
                        <button onclick="window.print()" class="btn btn-sm btn-outline-light rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5 ms-lg-2">
                            <i class="fa-solid fa-print"></i> <span>প্রিন্ট / সেভ</span>
                        </button>
                    </div>
                </div>

                {{-- Right quick highlights grid --}}
                <div class="col-lg-4">
                    <div class="card border-0 rounded-4 p-3.5 shadow-lg" style="background: rgba(255,255,255,0.08); backdrop-filter: blur(16px); border: 1px solid rgba(255,255,255,0.18) !important;">
                        <h6 class="text-warning fw-bold mb-2.5 small text-uppercase tracking-wider">
                            <i class="fa-solid fa-key me-1.5"></i> আমাদের মূল প্রতিশ্রুতি
                        </h6>
                        <ul class="list-unstyled mb-0 d-flex flex-column gap-2 text-white small" style="font-size: 12.5px; line-height: 1.5;">
                            <li class="d-flex align-items-start gap-2">
                                <i class="fa-solid fa-check text-success mt-0.5"></i>
                                <span>কখনোই কোনো তৃতীয় পক্ষের কাছে গ্রাহকের ফোন/ইমেইল বিক্রি করা হয় না।</span>
                            </li>
                            <li class="d-flex align-items-start gap-2">
                                <i class="fa-solid fa-check text-success mt-0.5"></i>
                                <span>কার্ড বা ব্যাংক অ্যাকাউন্টের পিন/ওটিপি আমরা কখনোই সংরক্ষণ করি না।</span>
                            </li>
                            <li class="d-flex align-items-start gap-2">
                                <i class="fa-solid fa-check text-success mt-0.5"></i>
                                <span>যেকোনো সময় অ্যাকাউন্ট ও সংরক্ষিত তথ্য মুছে ফেলার পূর্ণ অধিকার।</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ══════════════════════════════════════════════════════════════════
         2. MAIN BODY CONTENT WITH STICKY SIDEBAR
    ══════════════════════════════════════════════════════════════════ --}}
    <div class="container py-5">
        <div class="row g-4">
            
            {{-- Sticky Navigation Sidebar --}}
            <div class="col-lg-3 d-none d-lg-block">
                <div class="sticky-top" style="top: 90px; z-index: 10;">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                        <div class="card-header bg-white py-3 px-3.5 border-bottom">
                            <h6 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                                <i class="fa-solid fa-list-check text-success"></i>
                                <span>সূচিপত্র (Contents)</span>
                            </h6>
                        </div>
                        <div class="list-group list-group-flush policy-nav-list p-2" style="font-size: 13px;">
                            <a href="#sec-collected" class="list-group-item list-group-item-action rounded-3 border-0 py-2 px-3 active">১. কী কী তথ্য আমরা সংগ্রহ করি</a>
                            <a href="#sec-usage" class="list-group-item list-group-item-action rounded-3 border-0 py-2 px-3">২. তথ্যের ব্যবহার ও উদ্দেশ্য</a>
                            <a href="#sec-security" class="list-group-item list-group-item-action rounded-3 border-0 py-2 px-3">৩. ডেটা এনক্রিপশন ও সাইবার নিরাপত্তা</a>
                            <a href="#sec-payment" class="list-group-item list-group-item-action rounded-3 border-0 py-2 px-3">৪. পেমেন্ট গেটওয়ে ও আর্থিক ডেটা</a>
                            <a href="#sec-sharing" class="list-group-item list-group-item-action rounded-3 border-0 py-2 px-3">৫. কুরিয়ার ও তৃতীয় পক্ষের সাথে বিনিময়</a>
                            <a href="#sec-cookies" class="list-group-item list-group-item-action rounded-3 border-0 py-2 px-3">৬. কুকিজ ও ব্রাউজিং অ্যানালিটিক্স</a>
                            <a href="#sec-rights" class="list-group-item list-group-item-action rounded-3 border-0 py-2 px-3">৭. গ্রাহকের ডেটা অধিকার ও মুছে ফেলা</a>
                            <a href="#sec-contact" class="list-group-item list-group-item-action rounded-3 border-0 py-2 px-3">৮. ডেটা প্রটেকশন যোগাযোগ</a>
                        </div>
                        <div class="card-footer bg-light p-3 text-center border-top">
                            <a href="{{ route('terms') }}" class="btn btn-sm btn-outline-secondary w-100 rounded-pill fw-semibold">
                                <i class="fa-solid fa-file-contract me-1"></i> শর্তাবলী ও নীতিমালা দেখুন
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Policy Articles Container --}}
            <div class="col-lg-9">
                <div class="d-flex flex-column gap-4">

                    {{-- SECTION 1: কী কী তথ্য আমরা সংগ্রহ করি --}}
                    <article id="sec-collected" class="card border-0 shadow-sm rounded-4 p-4 p-md-4.5 bg-white policy-section">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <span class="section-badge-icon bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center">
                                <i class="fa-solid fa-database"></i>
                            </span>
                            <div>
                                <span class="badge bg-success bg-opacity-15 text-success rounded-pill px-2.5 py-1 small fw-bold">ধারা ০১</span>
                                <h3 class="h4 fw-bold text-dark mb-0 mt-1">কী কী তথ্য আমরা সংগ্রহ করি (Information We Collect)</h3>
                            </div>
                        </div>
                        <div class="policy-body text-secondary" style="font-size: 14.5px; line-height: 1.8;">
                            <p>আইডিয়া প্রকাশন (ideaabd.com) এর ওয়েবসাইট ব্যবহারের সময় আমরা প্রধানত নিম্নোক্ত তথ্যসমূহ সংগ্রহ করতে পারি:</p>
                            <div class="row g-3 my-2">
                                <div class="col-md-6">
                                    <div class="p-3 bg-light rounded-3 border">
                                        <h6 class="fw-bold text-dark mb-1.5"><i class="fa-solid fa-user me-1.5 text-primary"></i> ব্যক্তিগত পরিচিতি তথ্য:</h6>
                                        <ul class="mb-0 small text-muted ps-3">
                                            <li>গ্রাহকের পূর্ণ নাম</li>
                                            <li>সচল মোবাইল নম্বর ও অল্টারনেট ফোন</li>
                                            <li>ইমেইল ঠিকানা</li>
                                            <li>বই ডেলিভারির পূর্ণ ঠিকানা ও পোস্টকোড</li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 bg-light rounded-3 border">
                                        <h6 class="fw-bold text-dark mb-1.5"><i class="fa-solid fa-laptop-code me-1.5 text-success"></i> প্রযুক্তিগত তথ্য ও লগ:</h6>
                                        <ul class="mb-0 small text-muted ps-3">
                                            <li>IP অ্যাড্রেস ও ব্রাউজারের ধরন</li>
                                            <li>ডিভাইস টাইপ ও অপারেটিং সিস্টেম</li>
                                            <li>পছন্দের বই ও উইশলিস্ট হিস্টোরি</li>
                                            <li>অর্ডার হিস্টোরি ও ট্রানজ্যাকশন আইডি</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="alert alert-info border-0 rounded-3 small py-2 px-3 mb-0 d-flex align-items-center gap-2">
                                <i class="fa-solid fa-circle-info fs-5"></i>
                                <span>আমরা কোনো অবস্থাতেই আপনার জাতীয় পরিচয়পত্র (NID) নম্বর বা ব্যাংক গোপন পিন সংগ্রহ করি না।</span>
                            </div>
                        </div>
                    </article>

                    {{-- SECTION 2: তথ্যের ব্যবহার ও উদ্দেশ্য --}}
                    <article id="sec-usage" class="card border-0 shadow-sm rounded-4 p-4 p-md-4.5 bg-white policy-section">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <span class="section-badge-icon bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center">
                                <i class="fa-solid fa-sliders"></i>
                            </span>
                            <div>
                                <span class="badge bg-primary bg-opacity-15 text-primary rounded-pill px-2.5 py-1 small fw-bold">ধারা ০২</span>
                                <h3 class="h4 fw-bold text-dark mb-0 mt-1">তথ্যের ব্যবহার ও উদ্দেশ্য (How We Use Your Information)</h3>
                            </div>
                        </div>
                        <div class="policy-body text-secondary" style="font-size: 14.5px; line-height: 1.8;">
                            <p>সংগৃহীত তথ্যসমূহ মূলত নিম্নলিখিত বৈধ ও প্রয়োজনীয় উদ্দেশ্যসমূহে ব্যবহৃত হয়:</p>
                            <ul class="list-unstyled d-flex flex-column gap-2 mb-0">
                                <li class="d-flex align-items-start gap-2">
                                    <i class="fa-solid fa-circle-check text-success mt-1"></i>
                                    <span><strong>অর্ডার প্রসেসিং ও পার্সেল হ্যান্ডওভার:</strong> আপনার অর্ডার করা বই সঠিক ঠিকানায় পৌঁছাতে কুরিয়ার পার্টনারকে ডেলিভারি তথ্য সরবরাহ করা।</span>
                                </li>
                                <li class="d-flex align-items-start gap-2">
                                    <i class="fa-solid fa-circle-check text-success mt-1"></i>
                                    <span><strong>অর্ডার ট্র্যাকিং ও আপডেট:</strong> আপনার অর্ডারের অগ্রগতি, শিপমেন্ট স্ট্যাটাস ও ডেলিভারি নিশ্চিত করতে SMS ও ইমেইল নোটিফিকেশন প্রদান।</span>
                                </li>
                                <li class="d-flex align-items-start gap-2">
                                    <i class="fa-solid fa-circle-check text-success mt-1"></i>
                                    <span><strong>ই-বুক লাইব্রেরি সিঙ্ক:</strong> ক্রয়কৃত ই-বুক আপনার প্রোফাইল ও রিডারে সংযুক্ত রাখা।</span>
                                </li>
                                <li class="d-flex align-items-start gap-2">
                                    <i class="fa-solid fa-circle-check text-success mt-1"></i>
                                    <span><strong>গ্রাহক সহায়তা:</strong> রিটার্ন, রিফান্ড বা যেকোনো অভিযোগের প্রেক্ষিতে সরাসরি যোগাযোগ ও সমাধান প্রদান।</span>
                                </li>
                            </ul>
                        </div>
                    </article>

                    {{-- SECTION 3: ডেটা এনক্রিপশন ও সাইবার নিরাপত্তা --}}
                    <article id="sec-security" class="card border-0 shadow-sm rounded-4 p-4 p-md-4.5 bg-white policy-section">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <span class="section-badge-icon bg-warning bg-opacity-10 text-warning rounded-circle d-flex align-items-center justify-content-center">
                                <i class="fa-solid fa-shield-halved"></i>
                            </span>
                            <div>
                                <span class="badge bg-warning bg-opacity-15 text-warning rounded-pill px-2.5 py-1 small fw-bold">ধারা ০৩</span>
                                <h3 class="h4 fw-bold text-dark mb-0 mt-1">ডেটা এনক্রিপশন ও সাইবার নিরাপত্তা (Data Security)</h3>
                            </div>
                        </div>
                        <div class="policy-body text-secondary" style="font-size: 14.5px; line-height: 1.8;">
                            <p>আমরা আধুনিক শিল্পমানের ডেটা এনক্রিপশন ও সুরক্ষা প্রোটোকল অনুসরণ করি:</p>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="p-3 bg-light rounded-3 text-center border h-100">
                                        <i class="fa-solid fa-lock text-success fs-3 mb-2"></i>
                                        <h6 class="fw-bold text-dark mb-1">SSL/TLS 1.3</h6>
                                        <p class="small text-muted mb-0">ওয়েবসাইটে আদান-প্রদান হওয়া সকল ডেটা শক্তিশালী এনক্রিপশনে সুরক্ষিত।</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="p-3 bg-light rounded-3 text-center border h-100">
                                        <i class="fa-solid fa-key text-primary fs-3 mb-2"></i>
                                        <h6 class="fw-bold text-dark mb-1">Bcrypt পাসওয়ার্ড হ্যাশিং</h6>
                                        <p class="small text-muted mb-0">ব্যবহারকারীর পাসওয়ার্ড একমুখী হ্যাশ অ্যালগরিদমে এনক্রিপ্ট থাকে।</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="p-3 bg-light rounded-3 text-center border h-100">
                                        <i class="fa-solid fa-fire-extinguisher text-danger fs-3 mb-2"></i>
                                        <h6 class="fw-bold text-dark mb-1">ফায়ারওয়াল ও ব্যাকআপ</h6>
                                        <p class="small text-muted mb-0">অটোমেটেড ডেইলি ব্যাকআপ ও রিয়েল-টাইম ম্যালওয়্যার গার্ড।</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </article>

                    {{-- SECTION 4: পেমেন্ট গেটওয়ে ও আর্থিক ডেটা --}}
                    <article id="sec-payment" class="card border-0 shadow-sm rounded-4 p-4 p-md-4.5 bg-white policy-section">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <span class="section-badge-icon bg-info bg-opacity-10 text-info rounded-circle d-flex align-items-center justify-content-center">
                                <i class="fa-solid fa-credit-card"></i>
                            </span>
                            <div>
                                <span class="badge bg-info bg-opacity-15 text-info rounded-pill px-2.5 py-1 small fw-bold">ধারা ০৪</span>
                                <h3 class="h4 fw-bold text-dark mb-0 mt-1">পেমেন্ট গেটওয়ে ও আর্থিক তথ্যের নিরাপত্তা</h3>
                            </div>
                        </div>
                        <div class="policy-body text-secondary" style="font-size: 14.5px; line-height: 1.8;">
                            <p>অনলাইন পেমেন্টের ক্ষেত্রে বিকাশ (bKash), নগদ (Nagad), রকেট (Rocket), উপায় (Upay) অথবা ভিসা ও মাস্টারকার্ডের নিজস্ব সুরক্ষিত পেমেন্ট গেটওয়ে ইন্টারফেস ব্যবহৃত হয়।</p>
                            <p class="mb-0"><strong>গুরুত্বপূর্ণ:</strong> আপনার কার্ড নম্বর, সিভিভি (CVV), ওটিপি (OTP) বা গোপন পিন নম্বর সরাসরি পেমেন্ট গেটওয়ে প্রসেস করে। আইডিয়া প্রকাশন সার্ভারে কোনো গোপন ব্যাংকিং ক্রেডেনশিয়াল সংরক্ষিত হয় না।</p>
                        </div>
                    </article>

                    {{-- SECTION 5: কুরিয়ার ও তৃতীয় পক্ষের সাথে বিনিময় --}}
                    <article id="sec-sharing" class="card border-0 shadow-sm rounded-4 p-4 p-md-4.5 bg-white policy-section">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <span class="section-badge-icon bg-secondary bg-opacity-10 text-secondary rounded-circle d-flex align-items-center justify-content-center">
                                <i class="fa-solid fa-truck-fast"></i>
                            </span>
                            <div>
                                <span class="badge bg-secondary bg-opacity-15 text-secondary rounded-pill px-2.5 py-1 small fw-bold">ধারা ০৫</span>
                                <h3 class="h4 fw-bold text-dark mb-0 mt-1">কুরিয়ার ও তৃতীয় পক্ষের সাথে ডেটা বিনিময়</h3>
                            </div>
                        </div>
                        <div class="policy-body text-secondary" style="font-size: 14.5px; line-height: 1.8;">
                            <p>শুধুমাত্র আপনার বই সফলভাবে হস্তান্তরের জন্য আমরা নির্ভরযোগ্য কুরিয়ার অংশীদারদের (যেমন: সুন্দরবন কুরিয়ার, এসএ পরিবহন, রেডএক্স, পেপারফ্লাই বা ই-কুরিয়ার) সাথে আপনার নাম, ফোন নম্বর ও ডেলিভারি ঠিকানা শেয়ার করি। এই পক্ষসমূহ চুক্তিবদ্ধভাবে কেবলমাত্র পার্সেল ডেলিভারির কাজেই এই তথ্য ব্যবহার করতে বাধ্য।</p>
                        </div>
                    </article>

                    {{-- SECTION 6: কুকিজ ও ব্রাউজিং অ্যানালিটিক্স --}}
                    <article id="sec-cookies" class="card border-0 shadow-sm rounded-4 p-4 p-md-4.5 bg-white policy-section">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <span class="section-badge-icon bg-warning bg-opacity-10 text-warning rounded-circle d-flex align-items-center justify-content-center">
                                <i class="fa-solid fa-cookie-bite"></i>
                            </span>
                            <div>
                                <span class="badge bg-warning bg-opacity-15 text-warning rounded-pill px-2.5 py-1 small fw-bold">ধারা ০৬</span>
                                <h3 class="h4 fw-bold text-dark mb-0 mt-1">কুকিজ ও ব্রাউজিং অ্যানালিটিক্স (Cookies Policy)</h3>
                            </div>
                        </div>
                        <div class="policy-body text-secondary" style="font-size: 14.5px; line-height: 1.8;">
                            <p>আমাদের ওয়েবসাইট স্মুথভাবে পরিচালনা করতে এবং আপনার কার্ট আইটেম ও লগইন সেশন মনে রাখতে ব্রাউজার কুকিজ ব্যবহার করা হয়। আপনি আপনার ব্রাউজার সেটিংস থেকে যেকোনো সময় কুকিজ ডিজেবল করতে পারেন, তবে সেক্ষেত্রে কিছু ফিচার (যেমন শপিং কার্ট মেমোরি) সঠিকভাবে কাজ নাও করতে পারে।</p>
                        </div>
                    </article>

                    {{-- SECTION 7: গ্রাহকের ডেটা অধিকার --}}
                    <article id="sec-rights" class="card border-0 shadow-sm rounded-4 p-4 p-md-4.5 bg-white policy-section">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <span class="section-badge-icon bg-danger bg-opacity-10 text-danger rounded-circle d-flex align-items-center justify-content-center">
                                <i class="fa-solid fa-user-gear"></i>
                            </span>
                            <div>
                                <span class="badge bg-danger bg-opacity-15 text-danger rounded-pill px-2.5 py-1 small fw-bold">ধারা ০৭</span>
                                <h3 class="h4 fw-bold text-dark mb-0 mt-1">গ্রাহকের ডেটা অধিকার ও মুছে ফেলা (Data Subject Rights)</h3>
                            </div>
                        </div>
                        <div class="policy-body text-secondary" style="font-size: 14.5px; line-height: 1.8;">
                            <p>আপনার ব্যক্তিগত তথ্যের উপর আপনার পূর্ণ নিয়ন্ত্রণ রয়েছে। আপনি নিম্নোক্ত অধিকারসমূহ প্রয়োগ করতে পারেন:</p>
                            <ul class="list-unstyled d-flex flex-column gap-2 mb-0">
                                <li class="d-flex align-items-start gap-2">
                                    <i class="fa-solid fa-check-double text-success mt-1"></i>
                                    <span><strong>তথ্য দেখার অধিকার:</strong> আপনার অ্যাকাউন্টে কী কী তথ্য সংরক্ষিত আছে তা ড্যাশবোর্ড থেকে দেখতে পারবেন।</span>
                                </li>
                                <li class="d-flex align-items-start gap-2">
                                    <i class="fa-solid fa-check-double text-success mt-1"></i>
                                    <span><strong>তথ্য সংশোধনের অধিকার:</strong> প্রোফাইল এডিট অপশন থেকে যেকোনো তথ্য হালনাগাদ করতে পারেন।</span>
                                </li>
                                <li class="d-flex align-items-start gap-2">
                                    <i class="fa-solid fa-check-double text-success mt-1"></i>
                                    <span><strong>অ্যাকাউন্ট ডিলিট করার অধিকার:</strong> হেল্পডেস্কে অনুরোধ পাঠিয়ে যেকোনো সময় আপনার সম্পূর্ণ ডেটা ও অ্যাকাউন্ট ডিলিট করতে পারবেন।</span>
                                </li>
                            </ul>
                        </div>
                    </article>

                    {{-- SECTION 8: ডেটা প্রটেকশন যোগাযোগ --}}
                    <article id="sec-contact" class="card border-0 shadow-sm rounded-4 p-4 p-md-4.5 bg-white policy-section">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <span class="section-badge-icon bg-teal-500 bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center">
                                <i class="fa-solid fa-headset"></i>
                            </span>
                            <div>
                                <span class="badge bg-success bg-opacity-15 text-success rounded-pill px-2.5 py-1 small fw-bold">ধারা ০৮</span>
                                <h3 class="h4 fw-bold text-dark mb-0 mt-1">ডেটা প্রটেকশন ও গোপনীয়তা হেল্পডেস্ক</h3>
                            </div>
                        </div>
                        <div class="policy-body text-secondary" style="font-size: 14.5px; line-height: 1.8;">
                            <p>গোপনীয়তা নীতিমালা সম্পর্কিত যেকোনো প্রশ্ন, মতামত বা ডেটা সংক্রান্ত সহায়তার জন্য আমাদের সাথে সরাসরি যোগাযোগ করুন:</p>
                            <div class="p-3 bg-light rounded-3 border d-flex flex-column gap-2">
                                <div><i class="fa-solid fa-building text-success me-2"></i> <strong>আইডিয়া প্রকাশন (Idea Prokashon)</strong></div>
                                <div><i class="fa-solid fa-envelope text-primary me-2"></i> <a href="mailto:privacy@ideaabd.com" class="text-decoration-none fw-semibold">privacy@ideaabd.com</a> / <a href="mailto:idea.sakil@gmail.com" class="text-decoration-none">idea.sakil@gmail.com</a></div>
                                <div><i class="fa-solid fa-phone text-success me-2"></i> +৮৮০ ১৫৫৮-৭১২৮১০ (সরাসরি হেল্পলাইন ও হোয়াটসঅ্যাপ)</div>
                                <div><i class="fa-solid fa-location-dot text-danger me-2"></i> ৩৮/২ক বাংলাবাজার, ঢাকা-১১০০, বাংলাদেশ</div>
                            </div>
                        </div>
                    </article>

                </div>
            </div>

        </div>
    </div>

</div>

<style>
.privacy-page-wrapper {
    font-family: 'Hind Siliguri', 'Segoe UI', system-ui, -apple-system, sans-serif;
}
.section-badge-icon {
    width: 48px;
    height: 48px;
    font-size: 22px;
    flex-shrink: 0;
}
.policy-nav-list a {
    color: #475569;
    font-weight: 500;
    transition: all 0.2s ease;
}
.policy-nav-list a:hover {
    background: rgba(16, 185, 129, 0.08);
    color: #047857;
    padding-left: 18px !important;
}
.policy-nav-list a.active {
    background: #047857 !important;
    color: #ffffff !important;
    font-weight: 700;
}
.policy-section {
    scroll-margin-top: 100px;
    transition: box-shadow 0.3s ease;
}
.policy-section:hover {
    box-shadow: 0 10px 30px rgba(0,0,0,0.06) !important;
}
@media print {
    .privacy-hero button, .policy-nav-list, .site-header, .site-footer, nav[aria-label="breadcrumb"] {
        display: none !important;
    }
    .col-lg-9 {
        width: 100% !important;
    }
    .policy-section {
        box-shadow: none !important;
        border: 1px solid #ddd !important;
        page-break-inside: avoid;
    }
}
</style>
@endsection
