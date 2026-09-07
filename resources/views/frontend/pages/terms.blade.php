@extends('layouts.app')

@section('title', 'ব্যবহারের শর্তাবলী ও প্রাতিষ্ঠানিক নীতিমালা — আইডিয়া প্রকাশন')
@section('meta_description', 'আইডিয়া প্রকাশন প্ল্যাটফর্ম ব্যবহার, বই ও ই-বুক ক্রয়, ডেলিভারি, রিটার্ন-রিফান্ড, লেখক কপিরাইট ও প্রকাশনা সম্পর্কিত পূর্ণাঙ্গ শর্তাবলী ও নীতিমালা।')

@section('content')
@php
    $termsBadge = \App\Support\SiteSetting::termsBadge();
    $termsTitle = \App\Support\SiteSetting::termsTitle();
    $termsSubtitle = \App\Support\SiteSetting::termsSubtitle();
    $termsVersion = \App\Support\SiteSetting::termsVersion();
    $termsReturnDays = \App\Support\SiteSetting::termsReturnDays();
    $termsRefundTimeline = \App\Support\SiteSetting::termsRefundTimeline();
    $termsReturnConditions = \App\Support\SiteSetting::termsReturnConditions();
    $termsReturnExcluded = \App\Support\SiteSetting::termsReturnExcluded();
    $termsShippingNote = \App\Support\SiteSetting::termsShippingNote();
    $termsEbookDrmNote = \App\Support\SiteSetting::termsEbookDrmNote();
    $termsAuthorRoyaltyNote = \App\Support\SiteSetting::termsAuthorRoyaltyNote();
    $termsCustomNotice = \App\Support\SiteSetting::termsCustomNotice();
@endphp

<div class="terms-page-wrapper bg-light min-vh-100 pb-5">

    {{-- ══════════════════════════════════════════════════════════════════
         1. HERO HEADER SECTION
    ══════════════════════════════════════════════════════════════════ --}}
    <section class="terms-hero position-relative overflow-hidden text-white py-5" 
             style="background: linear-gradient(135deg, #07192f 0%, #004d40 60%, #006a4e 100%);">
        
        {{-- Background decorative icon --}}
        <div class="position-absolute top-0 end-0 opacity-10 pe-none d-none d-lg-block" style="transform: translate(15%, -15%);">
            <i class="fa-solid fa-scale-balanced" style="font-size: 380px;"></i>
        </div>

        <div class="container position-relative z-1">
            {{-- Breadcrumb --}}
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb mb-0 small" style="--bs-breadcrumb-divider: '›';">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-white-50 text-decoration-none"><i class="fa-solid fa-house me-1"></i>হোম</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('about') }}" class="text-white-50 text-decoration-none">আমাদের কথা</a></li>
                    <li class="breadcrumb-item text-white active" aria-current="page">শর্তাবলী ও নীতিমালা</li>
                </ol>
            </nav>

            <div class="row align-items-center g-4">
                <div class="col-lg-8">
                    <div class="d-inline-flex align-items-center gap-2 bg-white bg-opacity-15 backdrop-blur rounded-pill px-3.5 py-1 mb-3 fw-bold small text-white border border-white border-opacity-20 shadow-sm">
                        <i class="fa-solid fa-file-contract text-warning"></i>
                        <span>{{ $termsBadge }}</span>
                    </div>
                    <h1 class="fw-black mb-3 text-white display-6 lh-sm" style="letter-spacing: -0.5px;">
                        {{ $termsTitle }}
                    </h1>
                    <p class="text-white-50 lead mb-4" style="font-size: 16px; line-height: 1.7; max-width: 680px;">
                        {{ $termsSubtitle }}
                    </p>

                    <div class="d-flex flex-wrap align-items-center gap-2 pt-1">
                        <span class="badge bg-white bg-opacity-20 text-white border border-white border-opacity-20 px-3 py-2 rounded-pill font-monospace" style="font-size: 12px;">
                            <i class="fa-regular fa-clock me-1"></i> সর্বশেষ হালনাগাদ: {{ $termsVersion }}
                        </span>
                        <span class="badge bg-success bg-opacity-75 text-white px-3 py-2 rounded-pill" style="font-size: 12px;">
                            <i class="fa-solid fa-circle-check me-1"></i> সক্রিয় ও কার্যকর
                        </span>
                        <button onclick="window.print()" class="btn btn-sm btn-outline-light rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5 ms-lg-2">
                            <i class="fa-solid fa-print"></i> <span>প্রিন্ট / PDF সেভ</span>
                        </button>
                    </div>
                </div>

                {{-- Right quick highlights grid --}}
                <div class="col-lg-4">
                    <div class="card border-0 rounded-4 p-3.5 shadow-lg" style="background: rgba(255,255,255,0.08); backdrop-filter: blur(16px); border: 1px solid rgba(255,255,255,0.18) !important;">
                        <h6 class="text-warning fw-bold mb-2.5 small text-uppercase tracking-wider">
                            <i class="fa-solid fa-shield-halved me-1.5"></i> প্রধান অঙ্গীকার ও নিরাপত্তা
                        </h6>
                        <ul class="list-unstyled mb-0 d-flex flex-column gap-2 text-white small" style="font-size: 12.5px; line-height: 1.5;">
                            <li class="d-flex align-items-start gap-2">
                                <i class="fa-solid fa-check text-success mt-0.5"></i>
                                <span>১০০% অরিজিনাল ও কপিরাইট সংরক্ষিত বই।</span>
                            </li>
                            <li class="d-flex align-items-start gap-2">
                                <i class="fa-solid fa-check text-success mt-0.5"></i>
                                <span>ত্রুটিপূর্ণ বইয়ে সহজ {{ $termsReturnDays }} দিনের রিটার্ন ও রিপ্লেসমেন্ট।</span>
                            </li>
                            <li class="d-flex align-items-start gap-2">
                                <i class="fa-solid fa-check text-success mt-0.5"></i>
                                <span>লেখকদের স্বচ্ছ স্বয়ংক্রিয় রয়্যালটি হিসাব ও নিরাপত্তা।</span>
                            </li>
                            <li class="d-flex align-items-start gap-2">
                                <i class="fa-solid fa-check text-success mt-0.5"></i>
                                <span>নিরাপদ SSL এনক্রিপ্টেড অনলাইন পেমেন্ট সুবিধা।</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ══════════════════════════════════════════════════════════════════
         2. MAIN CONTENT AREA WITH STICKY SIDEBAR
    ══════════════════════════════════════════════════════════════════ --}}
    <div class="container mt-4 pt-3">
        <div class="row g-4">

            {{-- Sticky Navigation Menu (Left Column) --}}
            <aside class="col-lg-3 d-none d-lg-block">
                <div class="position-sticky" style="top: 100px; z-index: 10;">
                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                        <div class="fw-bold text-dark text-uppercase small pb-2 mb-2 border-bottom d-flex align-items-center justify-content-between" style="font-size: 11.5px; letter-spacing: 0.5px;">
                            <span><i class="fa-solid fa-list-ol text-primary me-1.5"></i> সূচিপত্র (Contents)</span>
                        </div>
                        <nav class="nav flex-column gap-1 terms-nav" id="termsStickyNav">
                            <a class="nav-link terms-nav-link rounded-3 px-2.5 py-2 text-dark active" href="#sec-intro">
                                <i class="fa-solid fa-scroll me-2 opacity-75"></i>১. ভূমিকা ও গ্রহণযোগ্যতা
                            </a>
                            <a class="nav-link terms-nav-link rounded-3 px-2.5 py-2 text-dark" href="#sec-orders">
                                <i class="fa-solid fa-cart-shopping me-2 opacity-75"></i>২. বই ক্রয় ও পেমেন্ট
                            </a>
                            <a class="nav-link terms-nav-link rounded-3 px-2.5 py-2 text-dark" href="#sec-shipping">
                                <i class="fa-solid fa-truck-fast me-2 opacity-75"></i>৩. ডেলিভারি ও শিপিং
                            </a>
                            <a class="nav-link terms-nav-link rounded-3 px-2.5 py-2 text-dark" href="#sec-returns">
                                <i class="fa-solid fa-arrow-rotate-left me-2 opacity-75"></i>৪. রিটার্ন ও রিফান্ড
                            </a>
                            <a class="nav-link terms-nav-link rounded-3 px-2.5 py-2 text-dark" href="#sec-ebooks">
                                <i class="fa-solid fa-tablet-screen-button me-2 opacity-75"></i>৫. ই-বুক ও ডিজিটাল কনটেন্ট
                            </a>
                            <a class="nav-link terms-nav-link rounded-3 px-2.5 py-2 text-dark" href="#sec-authors">
                                <i class="fa-solid fa-feather-pointed me-2 opacity-75"></i>৬. লেখক ও রয়্যালটি
                            </a>
                            <a class="nav-link terms-nav-link rounded-3 px-2.5 py-2 text-dark" href="#sec-publishers">
                                <i class="fa-solid fa-building me-2 opacity-75"></i>৭. প্রকাশক ও সেলার
                            </a>
                            <a class="nav-link terms-nav-link rounded-3 px-2.5 py-2 text-dark" href="#sec-blog">
                                <i class="fa-solid fa-newspaper me-2 opacity-75"></i>৮. ব্লগ ও পাঠক মন্তব্য
                            </a>
                            <a class="nav-link terms-nav-link rounded-3 px-2.5 py-2 text-dark" href="#sec-privacy">
                                <i class="fa-solid fa-lock me-2 opacity-75"></i>৯. গোপনীয়তা ও নিরাপত্তা
                            </a>
                            <a class="nav-link terms-nav-link rounded-3 px-2.5 py-2 text-dark" href="#sec-jurisdiction">
                                <i class="fa-solid fa-gavel me-2 opacity-75"></i>১০. আইনি এখতিয়ার ও বিচার
                            </a>
                            <a class="nav-link terms-nav-link rounded-3 px-2.5 py-2 text-dark" href="#sec-contact">
                                <i class="fa-solid fa-headset me-2 opacity-75"></i>১১. যোগাযোগ ও সহায়তা
                            </a>
                        </nav>

                        <div class="mt-3 pt-3 border-top text-center">
                            <a href="{{ route('contact') }}" class="btn btn-outline-primary btn-sm rounded-pill w-100 fw-bold py-1.5" style="font-size: 12px;">
                                <i class="fa-solid fa-envelope me-1"></i> কোনো জিজ্ঞাসা আছে?
                            </a>
                        </div>
                    </div>
                </div>
            </aside>

            {{-- Policy Articles (Right Column) --}}
            <main class="col-lg-9">
                <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white policy-article-card">

                    {{-- 1. Introduction --}}
                    <section id="sec-intro" class="policy-section mb-5 pb-4 border-bottom">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="section-num-badge">০১</span>
                            <h3 class="fw-bold text-dark mb-0 fs-4">ভূমিকা ও গ্রহণযোগ্যতা (Introduction & Acceptance)</h3>
                        </div>
                        <p class="text-secondary leading-relaxed">
                            আইডিয়া প্রকাশন (<strong>ideaabd.com</strong>) ওয়েবসাইট এবং সংশ্লিষ্ট ডিজিটাল সেবা ব্যবহারের ক্ষেত্রে আপনাকে স্বাগতম। এই ওয়েবসাইটের যেকোনো অংশ ব্রাউজ করা, অ্যাকাউন্ট তৈরি করা, বই ক্রয় করা কিংবা লেখা সাবমিট করার মাধ্যমে আপনি এই ব্যবহারের শর্তাবলীর সাথে সম্পূর্ণ একমত পোষণ করছেন। আপনি যদি এই শর্তাবলির কোনো অংশের সাথে একমত না হন, তবে অনুগ্রহ করে ওয়েবসাইট ব্যবহার থেকে বিরত থাকুন।
                        </p>
                        <div class="alert alert-info border-0 rounded-3 d-flex align-items-center gap-3 p-3 mt-3 bg-info bg-opacity-10 text-dark">
                            <i class="fa-solid fa-circle-info text-info fs-4 flex-shrink-0"></i>
                            <div class="small leading-normal">
                                আইডিয়া প্রকাশন যেকোনো সময় দেশের প্রচলিত আইন ও নীতিমালার আলোকে এই শর্তাবলী সংশোধন বা হালনাগাদ করার অধিকার সংরক্ষণ করে। সংশোধিত নীতিমালা ওয়েবসাইটে প্রকাশের সাথে সাথেই তা কার্যকর বলে গণ্য হবে।
                            </div>
                        </div>
                    </section>

                    {{-- 2. Orders & Payments --}}
                    <section id="sec-orders" class="policy-section mb-5 pb-4 border-bottom">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="section-num-badge">০২</span>
                            <h3 class="fw-bold text-dark mb-0 fs-4">বই ক্রয়, অর্ডার ও পেমেন্ট নীতিমালা (Orders & Payment Policy)</h3>
                        </div>
                        <ul class="text-secondary leading-relaxed list-unstyled d-flex flex-column gap-2.5">
                            <li class="d-flex gap-2.5">
                                <i class="fa-solid fa-circle-check text-primary mt-1 flex-shrink-0"></i>
                                <span><strong>অর্ডার নিশ্চিতকরণ:</strong> ওয়েবসাইটে সফলভাবে অর্ডার সম্পন্ন করার পর আপনার নিবন্ধিত ফোন নম্বর ও ইমেইলে অর্ডার নম্বর (#IDP-XXXX) সহ নিশ্চিতকরণ বার্তা প্রদান করা হবে।</span>
                            </li>
                            <li class="d-flex gap-2.5">
                                <i class="fa-solid fa-circle-check text-primary mt-1 flex-shrink-0"></i>
                                <span><strong>মূল্য ও অফার:</strong> বইয়ের গায়ে মুদ্রিত মূল্য (MRP), বিক্রয়মূল্য ও ডিসকাউন্ট ওয়েবসাইটে স্পষ্ট উল্লেখ থাকে। তবে মুদ্রণকারী প্রতিষ্ঠান বা কাগজ সংকটের কারণে আকস্মিক মূল্য পরিবর্তনের ক্ষেত্রে অর্ডার যাচাইকালে অবহিত করা হবে।</span>
                            </li>
                            <li class="d-flex gap-2.5">
                                <i class="fa-solid fa-circle-check text-primary mt-1 flex-shrink-0"></i>
                                <span><strong>পেমেন্ট মাধ্যম:</strong> গ্রাহকগণ বিকাশ (bKash), নগদ (Nagad), রকেট (Rocket), উপায় (Upay), ভিসা/মাস্টারকার্ড এবং সারাদেশে ক্যাশ অন ডেলিভারি (COD) মাধ্যমে মূল্য পরিশোধ করতে পারবেন।</span>
                            </li>
                            <li class="d-flex gap-2.5">
                                <i class="fa-solid fa-circle-check text-primary mt-1 flex-shrink-0"></i>
                                <span><strong>ক্যাশ অন ডেলিভারি ভেরিফিকেশন:</strong> ভুয়া বা অপ্রত্যাশিত অর্ডার রোধে ক্যাশ অন ডেলিভারির ক্ষেত্রে আংশিক ডেলিভারি চার্জ অগ্রিম গ্রহণের অধিকার কর্তৃপক্ষ সংরক্ষণ করে।</span>
                            </li>
                        </ul>
                    </section>

                    @if(!empty($termsCustomNotice))
                        {{-- Emergency / Special Policy Notice Callout --}}
                        <div class="alert alert-warning border-2 rounded-4 p-3.5 mb-4 shadow-xs d-flex align-items-start gap-3">
                            <i class="fa-solid fa-bullhorn text-danger fs-4 mt-1"></i>
                            <div>
                                <h6 class="fw-bold text-dark mb-1">বিশেষ প্রাতিষ্ঠানিক পলিসি নির্দেশনা ও আপডেট:</h6>
                                <div class="small text-secondary mb-0" style="line-height: 1.7; white-space: pre-line;">{!! nl2br(e($termsCustomNotice)) !!}</div>
                            </div>
                        </div>
                    @endif

                    {{-- 3. Shipping & Delivery --}}
                    <section id="sec-shipping" class="policy-section mb-5 pb-4 border-bottom">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="section-num-badge">০৩</span>
                            <h3 class="fw-bold text-dark mb-0 fs-4">ডেলিভারি, শিপিং ও কুরিয়ার ট্র্যাকিং (Shipping & Delivery Timelines)</h3>
                        </div>
                        <p class="text-secondary leading-relaxed mb-3">
                            আমরা নির্ভরযোগ্য কুরিয়ার পার্টনারদের (Steadfast, Pathao, RedX, eCourier, SA Paribahan, Sundarban) মাধ্যমে সমগ্র বাংলাদেশে ও বিদেশে দ্রুততম সময়ে বই পৌঁছে দেওয়ার ব্যবস্থা করি।
                        </p>
                        
                        @if(!empty($termsShippingNote))
                            <div class="p-3 bg-light rounded-3 border mb-3 small text-secondary">
                                <i class="fa-solid fa-circle-info text-primary me-1.5"></i> {!! nl2br(e($termsShippingNote)) !!}
                            </div>
                        @endif

                        <div class="table-responsive mb-3 shadow-xs rounded-3 border">
                            <table class="table table-bordered align-middle text-center small mb-0">
                                <thead class="table-dark">
                                    <tr>
                                        <th class="text-start ps-3">ডেলিভারি জোন</th>
                                        <th>আনুমানিক ডেলিভারি সময়</th>
                                        <th>শিপিং চার্জ</th>
                                        <th>ট্র্যাকিং সুবিধা</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="fw-semibold text-start ps-3">ঢাকা সিটি কর্পোরেশন এলাকা (Home Delivery)</td>
                                        <td>২৪ থেকে ৪৮ ঘণ্টা</td>
                                        <td>৳ ৬০ - ৭০</td>
                                        <td><span class="badge bg-success-subtle text-success">লাইভ ট্র্যাকিং</span></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-semibold text-start ps-3">ঢাকা উপশহর (সাভার, কেরানীগঞ্জ, গাজীপুর, নারায়ণগঞ্জ)</td>
                                        <td>২ থেকে ৩ কার্যদিবস</td>
                                        <td>৳ ৮০ - ১০০</td>
                                        <td><span class="badge bg-success-subtle text-success">লাইভ ট্র্যাকিং</span></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-semibold text-start ps-3">সমগ্র বাংলাদেশ (জেলা ও উপজেলা পর্যায়)</td>
                                        <td>৩ থেকে ৫ কার্যদিবস</td>
                                        <td>৳ ১২০ - ১৩০</td>
                                        <td><span class="badge bg-success-subtle text-success">লাইভ ট্র্যাকিং</span></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-semibold text-start ps-3">আন্তর্জাতিক কুরিয়ার (DHL/FedEx/Postal)</td>
                                        <td>৭ থেকে ১৪ কার্যদিবস</td>
                                        <td>ওজন ও দেশ অনুযায়ী</td>
                                        <td><span class="badge bg-primary-subtle text-primary">আন্তর্জাতিক কোড</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <small class="text-muted d-block" style="font-size: 11.5px;">
                            * প্রাকৃতিক দুর্যোগ, জাতীয় ছুটি বা রাজনৈতিক অচলাবস্থায় ডেলিভারি সময় কিছুটা পরিবর্তিত হতে পারে।
                        </small>
                    </section>

                    {{-- 4. Returns & Refunds --}}
                    <section id="sec-returns" class="policy-section mb-5 pb-4 border-bottom">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="section-num-badge">০৪</span>
                            <h3 class="fw-bold text-dark mb-0 fs-4">রিটার্ন, রিফান্ড ও বই প্রতিস্থাপন নীতি (Return & Refund Policy)</h3>
                        </div>
                        <p class="text-secondary leading-relaxed">
                            পাঠকের শতভাগ সন্তুষ্টি আমাদের মূল লক্ষ্য। ডেলিভারিকৃত কোনো বইয়ে মুদ্রণ ত্রুটি বা ক্ষতি থাকলে তা বিনা খরচে প্রতিস্থাপন করা হবে।
                        </p>
                        
                        <div class="row g-3 mt-2">
                            <div class="col-md-6">
                                <div class="p-3.5 bg-light rounded-3 border h-100">
                                    <h6 class="fw-bold text-success mb-2"><i class="fa-solid fa-check-double me-1"></i> যেক্ষেত্রে রিটার্ন ও প্রতিস্থাপন প্রযোজ্য:</h6>
                                    @if(!empty($termsReturnConditions))
                                        <div class="small text-muted ps-1" style="line-height: 1.7; white-space: pre-line;">{!! nl2br(e($termsReturnConditions)) !!}</div>
                                    @else
                                        <ul class="small text-muted mb-0 ps-3 d-flex flex-column gap-1.5">
                                            <li>বইয়ের ভেতরে পৃষ্ঠা মিসিং, উল্টো বাইন্ডিং বা অপাঠ্য ছাপার ত্রুটি থাকলে।</li>
                                            <li>অর্ডারের বাইরে ভুল বই ডেলিভারি হলে।</li>
                                            <li>কুরিয়ার পরিবহনে বই মারাত্মকভাবে ক্ষতিগ্রস্ত হলে।</li>
                                            <li>ডেলিভারি গ্রহণের <strong>{{ $termsReturnDays }} দিনের মধ্যে</strong> ছবি/ভিডিও সহ ক্লেইম করলে।</li>
                                        </ul>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3.5 bg-light rounded-3 border h-100">
                                    <h6 class="fw-bold text-danger mb-2"><i class="fa-solid fa-ban me-1"></i> যেক্ষেত্রে রিটার্ন প্রযোজ্য নয়:</h6>
                                    @if(!empty($termsReturnExcluded))
                                        <div class="small text-muted ps-1" style="line-height: 1.7; white-space: pre-line;">{!! nl2br(e($termsReturnExcluded)) !!}</div>
                                    @else
                                        <ul class="small text-muted mb-0 ps-3 d-flex flex-column gap-1.5">
                                            <li>গ্রাহকের অসাবধানতায় বইয়ের পাতা ছেঁড়া বা দাগ দেওয়া হলে।</li>
                                            <li>ডেলিভারির {{ $termsReturnDays }} দিন অতিক্রম করার পর অভিযোগ জানালে।</li>
                                            <li>ডিজিটাল ই-বুক ডাউনলোডের পর মত পরিবর্তন হলে।</li>
                                            <li>ক্লিয়ারেন্স বা মেগা ডিসকাউন্টের বই (পূর্ব ঘোষণা সাপেক্ষে)।</li>
                                        </ul>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="p-3 bg-warning bg-opacity-10 border border-warning rounded-3 mt-3">
                            <strong class="text-dark small"><i class="fa-solid fa-money-bill-transfer text-warning me-1"></i> রিফান্ড প্রসেসিং টাইমলাইন:</strong>
                            <p class="small text-muted mb-0 mt-1">
                                রিটার্ন যাচাইয়ের পর গ্রাহকের সম্মতিক্রমে <strong>{{ $termsRefundTimeline }}</strong>-এর মধ্যে যে মাধ্যমে পেমেন্ট করা হয়েছিল (বিকাশ/নগদ/কার্ড) সেই অ্যাকাউন্টেই দ্রুত রিফান্ড সম্পন্ন করা হয়।
                            </p>
                        </div>
                    </section>

                    {{-- 5. E-books & Digital Content --}}
                    <section id="sec-ebooks" class="policy-section mb-5 pb-4 border-bottom">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="section-num-badge">০৫</span>
                            <h3 class="fw-bold text-dark mb-0 fs-4">ডিজিটাল ই-বুক ও লাইব্রেরি অ্যাক্সেস নীতি (E-Books & DRM Policy)</h3>
                        </div>
                        @if(!empty($termsEbookDrmNote))
                            <div class="p-3 bg-light rounded-3 border mb-3 small text-secondary">
                                <i class="fa-solid fa-shield-halved text-primary me-1.5"></i> {!! nl2br(e($termsEbookDrmNote)) !!}
                            </div>
                        @endif
                        <ul class="text-secondary leading-relaxed list-unstyled d-flex flex-column gap-2.5">
                            <li class="d-flex gap-2.5">
                                <i class="fa-solid fa-tablet-screen-button text-info mt-1 flex-shrink-0"></i>
                                <span><strong>ব্যক্তিগত ব্যবহার:</strong> ক্রীত ই-বুক শুধুমাত্র গ্রাহকের ব্যক্তিগত পাঠের জন্য নির্ধারিত। এটি বাণিজ্যিক উদ্দেশ্যে বিক্রি, অননুমোদিত পুনর্মুদ্রণ বা পাইরেটেড শেয়ারিং আইনত দণ্ডনীয় অপরাধ।</span>
                            </li>
                            <li class="d-flex gap-2.5">
                                <i class="fa-solid fa-tablet-screen-button text-info mt-1 flex-shrink-0"></i>
                                <span><strong>ডিজিটাল রিডার লাইসেন্স:</strong> আইডিয়া প্রকাশনের অনলাইন ও অফলাইন রিডারে ই-বুক পড়ার জন্য গ্রাহককে নিরাপদ লাইসেন্স প্রদান করা হয়। কপিরাইট ও ডিজিটাল ওয়াটারমার্কের মাধ্যমে বইয়ের স্বত্ব সংরক্ষিত থাকে।</span>
                            </li>
                        </ul>
                    </section>

                    {{-- 6. Authors & Royalties --}}
                    <section id="sec-authors" class="policy-section mb-5 pb-4 border-bottom">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="section-num-badge">০৬</span>
                            <h3 class="fw-bold text-dark mb-0 fs-4">লেখক পাণ্ডুলিপি, স্বত্বাধিকার ও রয়্যালটি নীতিমালা (Author Copyright & Royalties)</h3>
                        </div>
                        <p class="text-secondary leading-relaxed">
                            আইডিয়া প্রকাশন লেখকদের মেধার সর্বোচ্চ মূল্যায়ন ও স্বচ্ছ প্রকাশনা পরিবেশ বজায় রাখতে প্রতিশ্রুতিবদ্ধ।
                        </p>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="p-3 border rounded-3 bg-light text-center h-100">
                                    <i class="fa-solid fa-copyright text-primary fs-3 mb-2"></i>
                                    <div class="fw-bold small text-dark">কপিরাইট সংরক্ষণ</div>
                                    <small class="text-muted" style="font-size: 11px;">রচনার পূর্ণাঙ্গ মূল স্বত্ব সর্বদা লেখকের অনুকূলে সংরক্ষিত থাকবে।</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 border rounded-3 bg-light text-center h-100">
                                    <i class="fa-solid fa-file-invoice-dollar text-success fs-3 mb-2"></i>
                                    <div class="fw-bold small text-dark">স্বচ্ছ ড্যাশবোর্ড</div>
                                    <small class="text-muted" style="font-size: 11px;">লেখক নিজস্ব ড্যাশবোর্ড থেকে বইয়ের লাইভ বিক্রয় ও রয়্যালটি রিপোর্ট দেখতে পারবেন।</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 border rounded-3 bg-light text-center h-100">
                                    <i class="fa-solid fa-building-columns text-info fs-3 mb-2"></i>
                                    <div class="fw-bold small text-dark">নির্ধারিত সময়ে প্রদান</div>
                                    <small class="text-muted" style="font-size: 11px;">চুক্তি অনুযায়ী রয়্যালটির অর্থ নির্ধারিত চক্রে ব্যাংক বা মোবাইল ব্যাংকিংয়ে জমা হবে।</small>
                                </div>
                            </div>
                        </div>
                    </section>

                    {{-- 7. Publishers & Sellers --}}
                    <section id="sec-publishers" class="policy-section mb-5 pb-4 border-bottom">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="section-num-badge">০৭</span>
                            <h3 class="fw-bold text-dark mb-0 fs-4">প্রকাশক ও সেলার পার্টনারশিপ শর্তাবলী (Publishers & Seller Channel)</h3>
                        </div>
                        <p class="text-secondary leading-relaxed">
                            অন্যান্য প্রকাশনা সংস্থা ও সেলার পার্টনারগণ আইডিয়া প্ল্যাটফর্মে তাদের বই তালিকাভুক্ত করে সারাদেশের পাঠকদের কাছে পৌঁছে দিতে পারেন। সকল বিক্রেতাকে অরিজিনাল বই সরবরাহ ও অনুমোদিত ডিসকাউন্ট বজায় রাখতে হবে। কোনো পাইরেটেড বা অননুমোদিত ফটোকপি বই তালিকাভুক্ত করা সম্পূর্ণ নিষিদ্ধ।
                        </p>
                    </section>

                    {{-- 8. Blog & Comments --}}
                    <section id="sec-blog" class="policy-section mb-5 pb-4 border-bottom">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="section-num-badge">০৮</span>
                            <h3 class="fw-bold text-dark mb-0 fs-4">আইডিয়াপত্র ব্লগ ও পাঠক মন্তব্য সম্পাদকীয় নীতি (Ideapatra & Community Guidelines)</h3>
                        </div>
                        <p class="text-secondary leading-relaxed">
                            ‘আইডিয়াপত্র’ ব্লগে প্রকাশিত সকল লেখার দায়িত্ব সংশ্লিষ্ট লেখকের। তবে মুক্তচিন্তা, সৃজনশীল সাহিত্য ও গবেষণামূলক লেখার পাশাপাশি নিম্নোক্ত বিষয়সমূহ কঠোরভাবে নিষিদ্ধ:
                        </p>
                        <div class="p-3 bg-danger bg-opacity-10 border border-danger border-opacity-25 rounded-3 text-dark small">
                            <ul class="mb-0 ps-3 d-flex flex-column gap-1">
                                <li>কারো ধর্মীয় অনুভূতিতে আঘাত বা সাম্প্রদায়িক বিদ্বেষমূলক বক্তব্য।</li>
                                <li>ব্যক্তিগত আক্রমণ, মানহানিকর বা অশালীন ভাষা ব্যবহার।</li>
                                <li>অনুমোদনহীন প্লেজিয়ারিজম (অন্যের লেখা নিজের নামে প্রকাশ)।</li>
                                <li>বাণিজ্যিক স্প্যামিং ও ক্ষতিকর ম্যালওয়্যার লিংক ছড়ানো।</li>
                            </ul>
                        </div>
                    </section>

                    {{-- 9. Privacy & Security --}}
                    <section id="sec-privacy" class="policy-section mb-5 pb-4 border-bottom">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="section-num-badge">০৯</span>
                            <h3 class="fw-bold text-dark mb-0 fs-4">গোপনীয়তা ও তথ্য সুরক্ষা (Privacy & Security)</h3>
                        </div>
                        <p class="text-secondary leading-relaxed">
                            গ্রাহকদের নাম, ফোন নম্বর, ডেলিভারি ঠিকানা ও ইমেইল সম্পূর্ণ সুরক্ষিত রাখা হয়। আমরা কোনো তৃতীয় পক্ষের কাছে গ্রাহকের ব্যক্তিগত তথ্য বিক্রয় বা অপব্যবহার করি না। বিস্তারিত জানতে আমাদের <a href="{{ route('privacy') }}" class="text-primary fw-bold text-decoration-none hover-underline">গোপনীয়তা নীতিমালা (Privacy Policy)</a> দেখুন।
                        </p>
                    </section>

                    {{-- 10. Governing Law --}}
                    <section id="sec-jurisdiction" class="policy-section mb-5 pb-4 border-bottom">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="section-num-badge">১০</span>
                            <h3 class="fw-bold text-dark mb-0 fs-4">আইনি এখতিয়ার ও বিচারিক বিধান (Governing Law & Jurisdiction)</h3>
                        </div>
                        <p class="text-secondary leading-relaxed">
                            এই শর্তাবলী গণপ্রজাতন্ত্রী বাংলাদেশের প্রচলিত আইন অনুসারে পরিচালিত ও ব্যাখ্যায়িত হবে। এই ওয়েবসাইট বা প্রকাশনা সম্পর্কিত যেকোনো বিরোধ সর্বপ্রথমে পারস্পরিক আলোচনা ও সালিশির মাধ্যমে নিষ্পত্তির চেষ্টা করা হবে। অন্যথায় তা ঢাকা, বাংলাদেশের আদালতের বিচারিক এখতিয়ারাধীন থাকবে।
                        </p>
                    </section>

                    {{-- 11. Contact & Legal Support --}}
                    <section id="sec-contact" class="policy-section">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="section-num-badge">১১</span>
                            <h3 class="fw-bold text-dark mb-0 fs-4">যোগাযোগ ও আইনি সহায়তা (Contact & Grievance Redressal)</h3>
                        </div>
                        <p class="text-secondary leading-relaxed mb-3">
                            নীতিমালা বা প্রকাশনা সেবা সম্পর্কিত যেকোনো অভিযোগ, জিজ্ঞাসা বা আইনি পর্যালোচনার জন্য সরাসরি আমাদের অফিস বা হেল্পলাইনে যোগাযোগ করতে পারেন:
                        </p>

                        <div class="p-4 rounded-4 bg-light border">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="d-flex align-items-center gap-2.5">
                                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px;">
                                            <i class="fa-solid fa-headset fs-5"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block" style="font-size: 11px;">কাস্টমার সাপোর্ট:</small>
                                            <a href="tel:+8801558712810" class="fw-bold text-dark text-decoration-none hover-primary">+8801558712810</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="d-flex align-items-center gap-2.5">
                                        <div class="rounded-circle bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px;">
                                            <i class="fa-brands fa-whatsapp fs-5"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block" style="font-size: 11px;">হোয়াটসঅ্যাপ হেল্পলাইন:</small>
                                            <a href="https://wa.me/8801558712810" target="_blank" class="fw-bold text-dark text-decoration-none hover-success">+8801558712810</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="d-flex align-items-center gap-2.5">
                                        <div class="rounded-circle bg-info bg-opacity-10 text-info d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px;">
                                            <i class="fa-solid fa-envelope fs-5"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block" style="font-size: 11px;">অফিশিয়াল ইমেইল:</small>
                                            <a href="mailto:support@ideaabd.com" class="fw-bold text-dark text-decoration-none hover-primary">support@ideaabd.com</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                </div>
            </main>
        </div>
    </div>
</div>

<style>
/* World-Class Terms & Conditions Styles */
.section-num-badge {
    background: linear-gradient(135deg, #006a4e 0%, #0284c7 100%);
    color: #ffffff;
    font-size: 13px;
    font-weight: 800;
    font-family: monospace;
    width: 34px;
    height: 34px;
    border-radius: 10px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 3px 8px rgba(0, 106, 78, 0.25);
}
.terms-nav-link {
    font-size: 13px;
    font-weight: 600;
    transition: all 0.2s ease;
    border-left: 3px solid transparent;
}
.terms-nav-link:hover {
    background: #f1f5f9;
    color: #006a4e !important;
    transform: translateX(3px);
}
.terms-nav-link.active {
    background: #e6f4ea !important;
    color: #006a4e !important;
    border-left-color: #006a4e !important;
}
.policy-section {
    scroll-margin-top: 100px;
}
@media print {
    .site-header, .site-footer, .terms-nav, #termsStickyNav, .terms-hero button, aside {
        display: none !important;
    }
    .terms-page-wrapper {
        background: #ffffff !important;
        padding: 0 !important;
    }
    .policy-article-card {
        box-shadow: none !important;
        border: none !important;
        padding: 0 !important;
    }
    .terms-hero {
        background: none !important;
        color: #000000 !important;
        padding: 20px 0 !important;
        border-bottom: 2px solid #000000;
    }
    .terms-hero * {
        color: #000000 !important;
    }
}
</style>

<script>
// Scrollspy active state for sticky nav
document.addEventListener('DOMContentLoaded', function() {
    const links = document.querySelectorAll('.terms-nav-link');
    const sections = document.querySelectorAll('.policy-section');

    function updateActiveNav() {
        let current = '';
        sections.forEach(sec => {
            const top = sec.offsetTop - 120;
            if (window.scrollY >= top) {
                current = sec.getAttribute('id');
            }
        });

        links.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === '#' + current) {
                link.classList.add('active');
            }
        });
    }

    window.addEventListener('scroll', updateActiveNav);
});
</script>
@endsection
