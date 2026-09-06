@extends('layouts.app')
@section('title', 'সাধারণ জিজ্ঞাসা ও প্রশ্নোত্তর (FAQ) — আইডিয়া প্রকাশন')
@section('meta_description', 'আইডিয়া প্রকাশনের বই অর্ডার, ডেলিভারি, লেখক প্রকাশনা, সম্মানী, পাইকারি বুকশপ ও পেমেন্ট সম্পর্কিত সকল প্রশ্নের উত্তর জানুন।')

@section('content')
<div class="faq-page-wrapper bg-light min-vh-100 pb-5">

    {{-- 1. Hero Search Header --}}
    <section class="faq-hero-section position-relative overflow-hidden text-white py-5" 
             style="background: linear-gradient(135deg, #07192f 0%, #004d40 60%, #006a4e 100%);">
        <div class="position-absolute top-0 end-0 opacity-10 pe-none d-none d-md-block" style="transform: translate(15%, -20%);">
            <i class="fa-solid fa-circle-question" style="font-size: 380px;"></i>
        </div>

        <div class="container position-relative z-1">
            {{-- Breadcrumb --}}
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb mb-0 small" style="--bs-breadcrumb-divider: '›';">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-white-50 text-decoration-none"><i class="fa-solid fa-house me-1"></i>Home</a></li>
                    <li class="breadcrumb-item text-white active" aria-current="page">FAQ</li>
                </ol>
            </nav>

            <div class="text-center mx-auto" style="max-width: 760px;">
                <div class="d-inline-flex align-items-center gap-2 bg-white bg-opacity-15 backdrop-blur rounded-pill px-3.5 py-1 mb-3 fw-bold small text-white border border-white border-opacity-20 shadow-sm">
                    <i class="fa-solid fa-circle-question text-warning"></i>
                    <span>সহায়তা কেন্দ্র ও সাধারণ জিজ্ঞাসা</span>
                </div>
                <h1 class="fw-black text-white display-6 mb-3 lh-sm" style="letter-spacing: -0.5px;">
                    সচরাচর জিজ্ঞাসিত প্রশ্নোত্তর (FAQ)
                </h1>
                <p class="text-white-50 lead mb-4" style="font-size: 16px; line-height: 1.6;">
                    বই অর্ডার, দেশব্যাপী ডেলিভারি, লেখক পান্ডুলিপি, সম্মানী ও রয়্যালটি, পাইকারি বুকশপ এবং প্রকাশনা সেবা সম্পর্কিত বিস্তারিত তথ্য জানুন।
                </p>

                {{-- Live Instant Search Box --}}
                <div class="position-relative mx-auto" style="max-width: 580px;">
                    <div class="input-group shadow-lg rounded-pill overflow-hidden bg-white p-1 border">
                        <span class="input-group-text bg-transparent border-0 ps-3 pe-2 text-muted">
                            <i class="fa-solid fa-magnifying-glass text-primary fs-5"></i>
                        </span>
                        <input type="text" id="faqLiveSearchInput" class="form-control border-0 py-2.5 px-2 fw-medium text-dark" 
                               placeholder="আপনার প্রশ্ন বা বিষয় লিখে খুঁজুন (যেমন: ডেলিভারি, সম্মানী, পান্ডুলিপি, সেলার)..." 
                               aria-label="FAQ Search" autocomplete="off" style="font-size: 15px; outline: none; box-shadow: none;">
                        <button type="button" class="btn btn-secondary rounded-pill px-3 fw-bold" id="clearFaqSearchBtn" style="display: none; font-size: 13px;">
                            রিসেট
                        </button>
                        <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold shadow-xs d-none d-sm-inline-flex align-items-center gap-1.5" onclick="document.getElementById('faqLiveSearchInput').focus()">
                            <i class="fa-solid fa-magnifying-glass"></i> <span>অনুসন্ধান</span>
                        </button>
                    </div>
                    <div id="faqSearchResultCounter" class="small text-white-50 text-center mt-2.5 d-none fw-semibold"></div>
                </div>
            </div>
        </div>
    </section>

    {{-- 2. Category Filter Tabs & Expand All Controls --}}
    <section class="container mt-4 pt-2">
        <div class="d-flex align-items-center justify-content-start justify-content-lg-center flex-nowrap overflow-x-auto pb-2 mb-4 gap-2" id="faqCategoryTabs">
            <button class="btn faq-tab-btn active text-nowrap" data-category="all">
                <i class="fa-solid fa-layer-group me-1.5"></i> সকল প্রশ্ন <span class="badge bg-light text-dark ms-1 rounded-pill faq-count-badge" id="faqBadgeAll">১৪</span>
            </button>
            <button class="btn faq-tab-btn text-nowrap" data-category="orders">
                <i class="fa-solid fa-bag-shopping me-1.5 text-success"></i> বই ও ডেলিভারি
            </button>
            <button class="btn faq-tab-btn text-nowrap" data-category="authors">
                <i class="fa-solid fa-feather-pointed me-1.5 text-info"></i> লেখক ও সম্মানী
            </button>
            <button class="btn faq-tab-btn text-nowrap" data-category="publishing">
                <i class="fa-solid fa-book me-1.5 text-danger"></i> প্রকাশনা ও ISBN
            </button>
            <button class="btn faq-tab-btn text-nowrap" data-category="sellers">
                <i class="fa-solid fa-store me-1.5 text-warning"></i> সেলার ও বুকশপ
            </button>
            <button class="btn faq-tab-btn text-nowrap" data-category="payments">
                <i class="fa-solid fa-credit-card me-1.5 text-primary"></i> পেমেন্ট ও রিটার্ন
            </button>
            <button class="btn faq-tab-btn text-nowrap" data-category="ebooks">
                <i class="fa-solid fa-tablet-screen-button me-1.5 text-secondary"></i> ই-বুক ও ডিজিটাল
            </button>
        </div>

        {{-- Active bar with Expand/Collapse buttons --}}
        <div class="d-flex align-items-center justify-content-between mb-3 px-1">
            <div class="small text-muted fw-semibold">
                <span id="activeFaqCategoryTitle">সকল সাধারণ জিজ্ঞাসা</span> 
                <span class="text-primary fw-bold" id="visibleFaqCount">(১৪টি প্রশ্নোত্তর প্রদর্শিত)</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-bold" onclick="toggleAllFaqs(true)">
                    <i class="fa-solid fa-angles-down me-1"></i> সব খুলুন
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-bold" onclick="toggleAllFaqs(false)">
                    <i class="fa-solid fa-angles-up me-1"></i> সব বন্ধ করুন
                </button>
            </div>
        </div>

        {{-- 3. Comprehensive FAQ Accordions Grid --}}
        <div class="row justify-content-center">
            <div class="col-lg-11 col-xl-10">
                <div class="accordion accordion-flush d-flex flex-column gap-3" id="mainFaqAccordion">

                    {{-- Q1: Order Process --}}
                    <div class="faq-item-card" data-category="orders" data-keywords="order buy cart checkout বই অর্ডার কেনা কার্ট চেকআউট প্রক্রিয়া">
                        <div class="card border-0 rounded-4 overflow-hidden shadow-sm bg-white">
                            <div class="card-header bg-white border-0 p-0" id="headingQ1">
                                <button class="accordion-button collapsed fw-bold text-dark py-3.5 px-3 px-md-4 d-flex align-items-center gap-2.5" type="button" data-bs-toggle="collapse" data-bs-target="#collapseQ1" aria-expanded="false" aria-controls="collapseQ1">
                                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2.5 py-1 small flex-shrink-0">বই ও অর্ডার</span>
                                    <span class="fs-6 fw-bold">কীভাবে আইডিয়া প্রকাশন থেকে বই অর্ডার করব এবং ক্যাশ অন ডেলিভারি (COD) সুবিধা আছে কি?</span>
                                </button>
                            </div>
                            <div id="collapseQ1" class="accordion-collapse collapse" aria-labelledby="headingQ1" data-bs-parent="#mainFaqAccordion">
                                <div class="card-body px-3 px-md-4 py-3 pt-0 text-secondary" style="font-size: 14.5px; line-height: 1.7;">
                                    আইডিয়া প্রকাশন প্ল্যাটফর্মে যেকোনো বইয়ের পাতায় গিয়ে <strong>"কার্টে যোগ করুন"</strong> অথবা <strong>"এখনই কিনুন"</strong> বাটনে ক্লিক করে এক মিনিটে অর্ডার সম্পন্ন করা যায়। 
                                    আপনার নাম, মোবাইল নম্বর ও সঠিক ডেলিভারি ঠিকানা প্রদান করে <strong>ক্যাশ অন ডেলিভারি (হোম ডেলিভারি পাওয়ার পর মূল্য পরিশোধ)</strong> অথবা <strong>অনলাইন পেমেন্ট (বিকাশ, নগদ, কার্ড)</strong> যেকোনো মাধ্যমে সহজেই অর্ডার কনফার্ম করতে পারবেন।
                                    <div class="mt-3 pt-2.5 border-top d-flex align-items-center justify-content-between flex-wrap gap-2 text-muted small">
                                        <span><i class="fa-solid fa-tags me-1 text-primary"></i> ট্যাগ: বই ক্রয়, ক্যাশ অন ডেলিভারি, অনলাইন চেকআউট</span>
                                        <div class="d-flex align-items-center gap-2 feedback-box">
                                            <span>উত্তরটি কি সহায়ক ছিল?</span>
                                            <button class="btn btn-sm btn-light py-0.5 px-2.5 rounded-pill helpful-btn" onclick="rateFaq(this, true)">👍 হ্যাঁ</button>
                                            <button class="btn btn-sm btn-light py-0.5 px-2.5 rounded-pill helpful-btn" onclick="rateFaq(this, false)">👎 না</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Q2: Delivery Time & Courier Cost --}}
                    <div class="faq-item-card" data-category="orders" data-keywords="delivery time courier charge ঢাকা ডেলিভারি সময় খরচ কুরিয়ার চার্জ">
                        <div class="card border-0 rounded-4 overflow-hidden shadow-sm bg-white">
                            <div class="card-header bg-white border-0 p-0" id="headingQ2">
                                <button class="accordion-button collapsed fw-bold text-dark py-3.5 px-3 px-md-4 d-flex align-items-center gap-2.5" type="button" data-bs-toggle="collapse" data-bs-target="#collapseQ2" aria-expanded="false" aria-controls="collapseQ2">
                                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2.5 py-1 small flex-shrink-0">বই ও ডেলিভারি</span>
                                    <span class="fs-6 fw-bold">ঢাকা ও ঢাকার বাইরে বই ডেলিভারি পেতে কতদিন সময় লাগে এবং কুরিয়ার চার্জ কত?</span>
                                </button>
                            </div>
                            <div id="collapseQ2" class="accordion-collapse collapse" aria-labelledby="headingQ2" data-bs-parent="#mainFaqAccordion">
                                <div class="card-body px-3 px-md-4 py-3 pt-0 text-secondary" style="font-size: 14.5px; line-height: 1.7;">
                                    ঢাকা মেট্রোপলিটন এলাকার ভেতরে সাধারণত <strong>২৪ থেকে ৪৮ ঘণ্টার</strong> মধ্যে এবং ঢাকা সিটির বাইরে বাংলাদেশের যেকোনো জেলা বা উপজেলায় <strong>২ থেকে ৩ কার্যদিবসের</strong> মধ্যে কুরিয়ার সার্ভিসের মাধ্যমে হোম ডেলিভারি পৌঁছে দেওয়া হয়।
                                    <br>• ঢাকা সিটির ভেতরে ডেলিভারি চার্জ সাধারণত ৬০ টাকা।
                                    <br>• ঢাকা সিটির বাইরে সমগ্র বাংলাদেশে ডেলিভারি চার্জ ১০০-১২০ টাকা (অর্ডার পরিমাণ ও অফার অনুযায়ী ফ্রি ডেলিভারি সুবিধাও প্রদান করা হয়)।
                                    <div class="mt-3 pt-2.5 border-top d-flex align-items-center justify-content-between flex-wrap gap-2 text-muted small">
                                        <span><i class="fa-solid fa-tags me-1 text-primary"></i> ট্যাগ: ডেলিভারি সময়, কুরিয়ার চার্জ, সারাদেশে হোম ডেলিভারি</span>
                                        <div class="d-flex align-items-center gap-2 feedback-box">
                                            <span>উত্তরটি কি সহায়ক ছিল?</span>
                                            <button class="btn btn-sm btn-light py-0.5 px-2.5 rounded-pill helpful-btn" onclick="rateFaq(this, true)">👍 হ্যাঁ</button>
                                            <button class="btn btn-sm btn-light py-0.5 px-2.5 rounded-pill helpful-btn" onclick="rateFaq(this, false)">👎 না</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Q3: Tracking Parcel --}}
                    <div class="faq-item-card" data-category="orders" data-keywords="tracking parcel sms ট্র্যাকিং পার্সেল কুরিয়ার স্ট্যাটাস">
                        <div class="card border-0 rounded-4 overflow-hidden shadow-sm bg-white">
                            <div class="card-header bg-white border-0 p-0" id="headingQ3">
                                <button class="accordion-button collapsed fw-bold text-dark py-3.5 px-3 px-md-4 d-flex align-items-center gap-2.5" type="button" data-bs-toggle="collapse" data-bs-target="#collapseQ3" aria-expanded="false" aria-controls="collapseQ3">
                                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2.5 py-1 small flex-shrink-0">বই ও ডেলিভারি</span>
                                    <span class="fs-6 fw-bold">অর্ডার করার পর পার্সেল ট্র্যাকিং কীভাবে করব?</span>
                                </button>
                            </div>
                            <div id="collapseQ3" class="accordion-collapse collapse" aria-labelledby="headingQ3" data-bs-parent="#mainFaqAccordion">
                                <div class="card-body px-3 px-md-4 py-3 pt-0 text-secondary" style="font-size: 14.5px; line-height: 1.7;">
                                    অর্ডার কনফার্মেশনের পর আপনার রেজিস্টার্ড মোবাইল নম্বরে এসএমএস এবং ইমেইলে একটি <strong>ইনভয়েস ট্র্যাকিং কোড</strong> ও লাইভ ট্র্যাকিং লিংক পাঠানো হয়। এছাড়াও আমাদের ওয়েবসাইটের ফুটারের <strong>"অর্ডার ট্র্যাকিং"</strong> সেকশনে গিয়ে আপনার মোবাইল নম্বর ও অর্ডার আইডি দিলে পার্সেলটি বর্তমানে কুরিয়ারের কোন ধাপে রয়েছে তা রিয়েল-টাইমে দেখতে পাবেন।
                                    <div class="mt-3 pt-2.5 border-top d-flex align-items-center justify-content-between flex-wrap gap-2 text-muted small">
                                        <span><i class="fa-solid fa-tags me-1 text-primary"></i> ট্যাগ: পার্সেল ট্র্যাকিং, এসএমএস আপডেট, লাইভ লোকেশন</span>
                                        <div class="d-flex align-items-center gap-2 feedback-box">
                                            <span>উত্তরটি কি সহায়ক ছিল?</span>
                                            <button class="btn btn-sm btn-light py-0.5 px-2.5 rounded-pill helpful-btn" onclick="rateFaq(this, true)">👍 হ্যাঁ</button>
                                            <button class="btn btn-sm btn-light py-0.5 px-2.5 rounded-pill helpful-btn" onclick="rateFaq(this, false)">👎 না</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Q4: Author Manuscript Submission --}}
                    <div class="faq-item-card" data-category="authors" data-keywords="author manuscript submission লেখক পান্ডুলিপি জমা নিয়ম উপন্যাস কবিতা">
                        <div class="card border-0 rounded-4 overflow-hidden shadow-sm bg-white">
                            <div class="card-header bg-white border-0 p-0" id="headingQ4">
                                <button class="accordion-button collapsed fw-bold text-dark py-3.5 px-3 px-md-4 d-flex align-items-center gap-2.5" type="button" data-bs-toggle="collapse" data-bs-target="#collapseQ4" aria-expanded="false" aria-controls="collapseQ4">
                                    <span class="badge bg-info bg-opacity-10 text-info rounded-pill px-2.5 py-1 small flex-shrink-0">লেখক ও পান্ডুলিপি</span>
                                    <span class="fs-6 fw-bold">নতুন লেখক হিসেবে কীভাবে পান্ডুলিপি বা বই প্রকাশের প্রস্তাব জমা দেব?</span>
                                </button>
                            </div>
                            <div id="collapseQ4" class="accordion-collapse collapse" aria-labelledby="headingQ4" data-bs-parent="#mainFaqAccordion">
                                <div class="card-body px-3 px-md-4 py-3 pt-0 text-secondary" style="font-size: 14.5px; line-height: 1.7;">
                                    আইডিয়া প্রকাশনে নতুন লেখক ও প্রবীণ সাহিত্যিকদের সবসময় স্বাগত জানানো হয়। আপনি আমাদের <a href="{{ route('documents') }}" class="fw-bold text-primary text-decoration-none">ডকুমেন্টস পেজ</a> থেকে <strong>"লেখক পান্ডুলিপি জমা ফরম"</strong> ডাউনলোড করে পূরণ করে অথবা সরাসরি <a href="{{ route('register.form', 'author') }}" class="fw-bold text-primary text-decoration-none">লেখক হিসেবে রেজিস্ট্রেশন</a> করে আপনার পান্ডুলিপির সফটকপি জমা দিতে পারেন। 
                                    আমাদের প্রকাশনা ও সম্পাদকীয় পর্ষদ পান্ডুলিপিটি পর্যালোচনা করে ৭ থেকে ১০ কার্যদিবসের মধ্যে আপনার সাথে যোগাযোগ করবে।
                                    <div class="mt-3 pt-2.5 border-top d-flex align-items-center justify-content-between flex-wrap gap-2 text-muted small">
                                        <span><i class="fa-solid fa-tags me-1 text-primary"></i> ট্যাগ: লেখক পোর্টাল, পান্ডুলিপি রিভিউ, প্রকাশনা প্রস্তাব</span>
                                        <div class="d-flex align-items-center gap-2 feedback-box">
                                            <span>উত্তরটি কি সহায়ক ছিল?</span>
                                            <button class="btn btn-sm btn-light py-0.5 px-2.5 rounded-pill helpful-btn" onclick="rateFaq(this, true)">👍 হ্যাঁ</button>
                                            <button class="btn btn-sm btn-light py-0.5 px-2.5 rounded-pill helpful-btn" onclick="rateFaq(this, false)">👎 না</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Q5: Ideapatra Writing Honorarium --}}
                    <div class="faq-item-card" data-category="authors" data-keywords="ideapatra honorarium royalty সম্মানী রয়্যালটি আইডিয়াপত্র সাহিত্য ব্লগ">
                        <div class="card border-0 rounded-4 overflow-hidden shadow-sm bg-white">
                            <div class="card-header bg-white border-0 p-0" id="headingQ5">
                                <button class="accordion-button collapsed fw-bold text-dark py-3.5 px-3 px-md-4 d-flex align-items-center gap-2.5" type="button" data-bs-toggle="collapse" data-bs-target="#collapseQ5" aria-expanded="false" aria-controls="collapseQ5">
                                    <span class="badge bg-info bg-opacity-10 text-info rounded-pill px-2.5 py-1 small flex-shrink-0">লেখক ও সম্মানী</span>
                                    <span class="fs-6 fw-bold">আইডিয়াপত্র সাহিত্য ব্লগে লেখার নিয়ম ও লেখক সম্মানী কীভাবে প্রদান করা হয়?</span>
                                </button>
                            </div>
                            <div id="collapseQ5" class="accordion-collapse collapse" aria-labelledby="headingQ5" data-bs-parent="#mainFaqAccordion">
                                <div class="card-body px-3 px-md-4 py-3 pt-0 text-secondary" style="font-size: 14.5px; line-height: 1.7;">
                                    আইডিয়াপত্র সাহিত্য ও গবেষণাধর্মী ম্যাগাজিনে নির্বাচিত মৌলিক গল্প, কবিতা, প্রবন্ধ ও বই সমালোচনার জন্য নিয়মিত <strong>সম্মানী ও রয়্যালটি</strong> প্রদান করা হয়। 
                                    হেডারের <strong>"লেখা পোস্ট"</strong> বাটনে ক্লিক করে লেখকগণ লেখা জমা দিতে পারেন। লেখা প্রকাশিত হলে পাঠক এনগেজমেন্ট ও সম্পাদকীয় মূল্যায়নের ভিত্তিতে নির্ধারিত সম্মানী সরাসরি লেখকের ব্যাংক অ্যাকাউন্ট বা বিকাশ/নগদ ওয়ালেটে স্বয়ংক্রিয়ভাবে প্রদান করা হয়।
                                    <div class="mt-3 pt-2.5 border-top d-flex align-items-center justify-content-between flex-wrap gap-2 text-muted small">
                                        <span><i class="fa-solid fa-tags me-1 text-primary"></i> ট্যাগ: আইডিয়াপত্র, লেখক সম্মানী, বিকাশ পেমেন্ট</span>
                                        <div class="d-flex align-items-center gap-2 feedback-box">
                                            <span>উত্তরটি কি সহায়ক ছিল?</span>
                                            <button class="btn btn-sm btn-light py-0.5 px-2.5 rounded-pill helpful-btn" onclick="rateFaq(this, true)">👍 হ্যাঁ</button>
                                            <button class="btn btn-sm btn-light py-0.5 px-2.5 rounded-pill helpful-btn" onclick="rateFaq(this, false)">👎 না</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Q6: Publishing Services & Packages --}}
                    <div class="faq-item-card" data-category="publishing" data-keywords="publishing services packages প্রকাশনা সেবা প্যাকেজ প্রুফরিডিং প্রচ্ছদ মুদ্রণ">
                        <div class="card border-0 rounded-4 overflow-hidden shadow-sm bg-white">
                            <div class="card-header bg-white border-0 p-0" id="headingQ6">
                                <button class="accordion-button collapsed fw-bold text-dark py-3.5 px-3 px-md-4 d-flex align-items-center gap-2.5" type="button" data-bs-toggle="collapse" data-bs-target="#collapseQ6" aria-expanded="false" aria-controls="collapseQ6">
                                    <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-2.5 py-1 small flex-shrink-0">প্রকাশনা ও ISBN</span>
                                    <span class="fs-6 fw-bold">বই প্রকাশনার জন্য আইডিয়া প্রকাশন কী কী সেবা ও প্যাকেজ প্রদান করে?</span>
                                </button>
                            </div>
                            <div id="collapseQ6" class="accordion-collapse collapse" aria-labelledby="headingQ6" data-bs-parent="#mainFaqAccordion">
                                <div class="card-body px-3 px-md-4 py-3 pt-0 text-secondary" style="font-size: 14.5px; line-height: 1.7;">
                                    আইডিয়া প্রকাশন একটি সম্পূর্ণ আধুনিক ও প্রফেশনাল প্রকাশনা হাউস। আমাদের সেবাসমূহের মধ্যে রয়েছে:
                                    <br>১. প্রফেশনাল প্রুফরিডিং ও পাণ্ডুলিপি সম্পাদনা।
                                    <br>২. আন্তর্জাতিকমানের আকর্ষণীয় প্রচ্ছদ (Book Cover) ও টাইপোগ্রাফি ডিজাইন।
                                    <br>৩. প্রিমিয়াম ৮০ GSM ডিমাই/রয়েল সাইজের পেপার ও হাই-কোয়ালিটি অফসেট প্রিন্টিং।
                                    <br>৪. জাতীয় গ্রন্থকেন্দ্র থেকে অফিশিয়াল ১৩-ডিজিটের <strong>ISBN ও বারকোড সংগ্রহ</strong>।
                                    <br>৫. অনলাইন ই-কমার্স প্ল্যাটফর্ম, বাংলা একাডেমি বইমেলা ও দেশব্যাপী প্রধান বুকশপে বিপণন।
                                    <div class="mt-3 pt-2.5 border-top d-flex align-items-center justify-content-between flex-wrap gap-2 text-muted small">
                                        <span><i class="fa-solid fa-tags me-1 text-primary"></i> ট্যাগ: বই প্রকাশনা, প্রচ্ছদ ডিজাইন, প্রিন্টিং প্যাকেজ</span>
                                        <div class="d-flex align-items-center gap-2 feedback-box">
                                            <span>উত্তরটি কি সহায়ক ছিল?</span>
                                            <button class="btn btn-sm btn-light py-0.5 px-2.5 rounded-pill helpful-btn" onclick="rateFaq(this, true)">👍 হ্যাঁ</button>
                                            <button class="btn btn-sm btn-light py-0.5 px-2.5 rounded-pill helpful-btn" onclick="rateFaq(this, false)">👎 না</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Q7: ISBN & Copyright Protection --}}
                    <div class="faq-item-card" data-category="publishing" data-keywords="isbn copyright registration আইএসবিএন কপিরাইট সরকারি আইন">
                        <div class="card border-0 rounded-4 overflow-hidden shadow-sm bg-white">
                            <div class="card-header bg-white border-0 p-0" id="headingQ7">
                                <button class="accordion-button collapsed fw-bold text-dark py-3.5 px-3 px-md-4 d-flex align-items-center gap-2.5" type="button" data-bs-toggle="collapse" data-bs-target="#collapseQ7" aria-expanded="false" aria-controls="collapseQ7">
                                    <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-2.5 py-1 small flex-shrink-0">প্রকাশনা ও ISBN</span>
                                    <span class="fs-6 fw-bold">বইয়ের সরকারি ISBN নম্বর ও কপিরাইট রেজিস্ট্রেশন কীভাবে সম্পন্ন হয়?</span>
                                </button>
                            </div>
                            <div id="collapseQ7" class="accordion-collapse collapse" aria-labelledby="headingQ7" data-bs-parent="#mainFaqAccordion">
                                <div class="card-body px-3 px-md-4 py-3 pt-0 text-secondary" style="font-size: 14.5px; line-height: 1.7;">
                                    আইডিয়া প্রকাশন থেকে প্রকাশিত প্রতিটি বইয়ের জন্য গণপ্রজাতন্ত্রী বাংলাদেশ সরকারের সংস্কৃতি বিষয়ক মন্ত্রণালয়ের অধীনস্থ <strong>জাতীয় গ্রন্থকেন্দ্র (National Book Centre)</strong> থেকে বৈধ আন্তর্জাতিক প্রমিত গ্রন্থ সংখ্যা (ISBN) সংগ্রহ করা হয়। 
                                    একই সাথে বাংলাদেশ কপিরাইট আইন অনুযায়ী লেখকের মেধা ও স্বত্বাধিকার শতভাগ সুরক্ষিত রাখা হয়।
                                    <div class="mt-3 pt-2.5 border-top d-flex align-items-center justify-content-between flex-wrap gap-2 text-muted small">
                                        <span><i class="fa-solid fa-tags me-1 text-primary"></i> ট্যাগ: ISBN রেজিস্ট্রেশন, কপিরাইট সুরক্ষা, জাতীয় গ্রন্থকেন্দ্র</span>
                                        <div class="d-flex align-items-center gap-2 feedback-box">
                                            <span>উত্তরটি কি সহায়ক ছিল?</span>
                                            <button class="btn btn-sm btn-light py-0.5 px-2.5 rounded-pill helpful-btn" onclick="rateFaq(this, true)">👍 হ্যাঁ</button>
                                            <button class="btn btn-sm btn-light py-0.5 px-2.5 rounded-pill helpful-btn" onclick="rateFaq(this, false)">👎 না</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Q8: Seller & Bookshop Registration --}}
                    <div class="faq-item-card" data-category="sellers" data-keywords="seller bookshop wholesale পাইকারি বুকশপ সেলার সাইন আপ লাইব্রেরি">
                        <div class="card border-0 rounded-4 overflow-hidden shadow-sm bg-white">
                            <div class="card-header bg-white border-0 p-0" id="headingQ8">
                                <button class="accordion-button collapsed fw-bold text-dark py-3.5 px-3 px-md-4 d-flex align-items-center gap-2.5" type="button" data-bs-toggle="collapse" data-bs-target="#collapseQ8" aria-expanded="false" aria-controls="collapseQ8">
                                    <span class="badge bg-warning bg-opacity-15 text-dark rounded-pill px-2.5 py-1 small flex-shrink-0">সেলার ও বুকশপ</span>
                                    <span class="fs-6 fw-bold">লাইব্রেরি বা বুকশপ কীভাবে আইডিয়া প্রকাশনের অনুমোদিত সেলার হতে পারে?</span>
                                </button>
                            </div>
                            <div id="collapseQ8" class="accordion-collapse collapse" aria-labelledby="headingQ8" data-bs-parent="#mainFaqAccordion">
                                <div class="card-body px-3 px-md-4 py-3 pt-0 text-secondary" style="font-size: 14.5px; line-height: 1.7;">
                                    যেকোনো রেজিস্টার্ড লাইব্রেরি, বুকশপ বা অনলাইন বই বিক্রেতা আমাদের <a href="{{ route('register.form', 'seller') }}" class="fw-bold text-primary text-decoration-none">সেলার রেজিস্ট্রেশন পেজে</a> গিয়ে প্রয়োজনীয় তথ্য প্রদান করে আবেদন করতে পারেন। 
                                    অ্যাডমিন কর্তৃক যাচাই-বাছাইয়ের পর একাউন্ট অ্যাক্টিভ হলে আপনি পাইকারি মূল্যে বইয়ের বাল্ক অর্ডার দিতে পারবেন এবং ড্যাশবোর্ডে স্বয়ংক্রিয় ইনভয়েস ও লেজার স্টেটমেন্ট দেখতে পারবেন।
                                    <div class="mt-3 pt-2.5 border-top d-flex align-items-center justify-content-between flex-wrap gap-2 text-muted small">
                                        <span><i class="fa-solid fa-tags me-1 text-primary"></i> ট্যাগ: সেলার রেজিস্ট্রেশন, বুকশপ অনুমোদন, ড্যাশবোর্ড ইনভয়েস</span>
                                        <div class="d-flex align-items-center gap-2 feedback-box">
                                            <span>উত্তরটি কি সহায়ক ছিল?</span>
                                            <button class="btn btn-sm btn-light py-0.5 px-2.5 rounded-pill helpful-btn" onclick="rateFaq(this, true)">👍 হ্যাঁ</button>
                                            <button class="btn btn-sm btn-light py-0.5 px-2.5 rounded-pill helpful-btn" onclick="rateFaq(this, false)">👎 না</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Q9: Wholesale Discount Slabs --}}
                    <div class="faq-item-card" data-category="sellers" data-keywords="wholesale discount commission পাইকারি কমিশন ডিসকাউন্ট স্ল্যাব">
                        <div class="card border-0 rounded-4 overflow-hidden shadow-sm bg-white">
                            <div class="card-header bg-white border-0 p-0" id="headingQ9">
                                <button class="accordion-button collapsed fw-bold text-dark py-3.5 px-3 px-md-4 d-flex align-items-center gap-2.5" type="button" data-bs-toggle="collapse" data-bs-target="#collapseQ9" aria-expanded="false" aria-controls="collapseQ9">
                                    <span class="badge bg-warning bg-opacity-15 text-dark rounded-pill px-2.5 py-1 small flex-shrink-0">সেলার ও বুকশপ</span>
                                    <span class="fs-6 fw-bold">পাইকারি বুকশপ ডিসকাউন্ট কমিশন ও ন্যূনতম অর্ডার শর্তাবলী কী?</span>
                                </button>
                            </div>
                            <div id="collapseQ9" class="accordion-collapse collapse" aria-labelledby="headingQ9" data-bs-parent="#mainFaqAccordion">
                                <div class="card-body px-3 px-md-4 py-3 pt-0 text-secondary" style="font-size: 14.5px; line-height: 1.7;">
                                    অনুমোদিত বুকশপগুলোর জন্য আইডিয়া প্রকাশনে আকর্ষণীয় পাইকারি কমিশন পলিসি রয়েছে:
                                    <br>• <strong>১০ থেকে ৫০ কপি:</strong> ৩৫% পাইকারি ছাড়।
                                    <br>• <strong>৫১ থেকে ১০০ কপি:</strong> ৪০% পাইকারি ছাড়।
                                    <br>• <strong>১০০+ কপি বা প্রাতিষ্ঠানিক অর্ডার:</strong> সর্বোচ্চ ৪৫% পর্যন্ত বিশেষ পাইকারি কমিশন।
                                    <br>বিস্তারিত স্ল্যাব ও শর্তাবলী দেখতে <a href="{{ route('documents') }}" class="fw-bold text-primary text-decoration-none">ডকুমেন্টস পেজে</a> থাকা 'পাইকারি বুকশপ চার্ট' দেখুন।
                                    <div class="mt-3 pt-2.5 border-top d-flex align-items-center justify-content-between flex-wrap gap-2 text-muted small">
                                        <span><i class="fa-solid fa-tags me-1 text-primary"></i> ট্যাগ: পাইকারি ডিসকাউন্ট, বুকশপ কমিশন, বাল্ক ক্রয়</span>
                                        <div class="d-flex align-items-center gap-2 feedback-box">
                                            <span>উত্তরটি কি সহায়ক ছিল?</span>
                                            <button class="btn btn-sm btn-light py-0.5 px-2.5 rounded-pill helpful-btn" onclick="rateFaq(this, true)">👍 হ্যাঁ</button>
                                            <button class="btn btn-sm btn-light py-0.5 px-2.5 rounded-pill helpful-btn" onclick="rateFaq(this, false)">👎 না</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Q10: Payment Methods & Security --}}
                    <div class="faq-item-card" data-category="payments" data-keywords="payment bkash nagad rocket card পেমেন্ট বিকাশ নগদ কার্ড গেটওয়ে">
                        <div class="card border-0 rounded-4 overflow-hidden shadow-sm bg-white">
                            <div class="card-header bg-white border-0 p-0" id="headingQ10">
                                <button class="accordion-button collapsed fw-bold text-dark py-3.5 px-3 px-md-4 d-flex align-items-center gap-2.5" type="button" data-bs-toggle="collapse" data-bs-target="#collapseQ10" aria-expanded="false" aria-controls="collapseQ10">
                                    <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-2.5 py-1 small flex-shrink-0">পেমেন্ট ও রিফান্ড</span>
                                    <span class="fs-6 fw-bold">অনলাইনে পেমেন্টের জন্য কী কী মাধ্যম (বিকাশ, নগদ, রকেট, কার্ড) সমর্থিত?</span>
                                </button>
                            </div>
                            <div id="collapseQ10" class="accordion-collapse collapse" aria-labelledby="headingQ10" data-bs-parent="#mainFaqAccordion">
                                <div class="card-body px-3 px-md-4 py-3 pt-0 text-secondary" style="font-size: 14.5px; line-height: 1.7;">
                                    আইডিয়া প্রকাশন প্ল্যাটফর্মে সম্পূর্ণ এনক্রিপ্টেড ও নিরাপদ অনলাইন পেমেন্ট গেটওয়ে সংযুক্ত রয়েছে। আপনি <strong>বিকাশ, নগদ, রকেট, উপায়, ভিসা কার্ড, মাস্টারকার্ড ও ইন্টারনেট ব্যাংকিং</strong> এর মাধ্যমে তাৎক্ষণিকভাবে পেমেন্ট সম্পন্ন করতে পারবেন। এছাড়াও ক্যাশ অন ডেলিভারি (COD) পেমেন্ট সুবিধা সর্বদা চালু রয়েছে।
                                    <div class="mt-3 pt-2.5 border-top d-flex align-items-center justify-content-between flex-wrap gap-2 text-muted small">
                                        <span><i class="fa-solid fa-tags me-1 text-primary"></i> ট্যাগ: পেমেন্ট গেটওয়ে, বিকাশ, নগদ, মাস্টারকার্ড</span>
                                        <div class="d-flex align-items-center gap-2 feedback-box">
                                            <span>উত্তরটি কি সহায়ক ছিল?</span>
                                            <button class="btn btn-sm btn-light py-0.5 px-2.5 rounded-pill helpful-btn" onclick="rateFaq(this, true)">👍 হ্যাঁ</button>
                                            <button class="btn btn-sm btn-light py-0.5 px-2.5 rounded-pill helpful-btn" onclick="rateFaq(this, false)">👎 না</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Q11: 7-Day Replacement & Return Policy --}}
                    <div class="faq-item-card" data-category="payments" data-keywords="return replacement refund রিপ্লেসমেন্ট রিটার্ন পরিবর্তন নীতি ত্রুটি">
                        <div class="card border-0 rounded-4 overflow-hidden shadow-sm bg-white">
                            <div class="card-header bg-white border-0 p-0" id="headingQ11">
                                <button class="accordion-button collapsed fw-bold text-dark py-3.5 px-3 px-md-4 d-flex align-items-center gap-2.5" type="button" data-bs-toggle="collapse" data-bs-target="#collapseQ11" aria-expanded="false" aria-controls="collapseQ11">
                                    <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-2.5 py-1 small flex-shrink-0">পেমেন্ট ও রিফান্ড</span>
                                    <span class="fs-6 fw-bold">বইয়ে কোনো প্রিন্টিং বা বাঁধাই ত্রুটি থাকলে রিটার্ন ও পরিবর্তন (Replacement) পলিসি কী?</span>
                                </button>
                            </div>
                            <div id="collapseQ11" class="accordion-collapse collapse" aria-labelledby="headingQ11" data-bs-parent="#mainFaqAccordion">
                                <div class="card-body px-3 px-md-4 py-3 pt-0 text-secondary" style="font-size: 14.5px; line-height: 1.7;">
                                    ডেলিভারি পাওয়ার পর যদি কোনো বইয়ে পৃষ্ঠার ঘাটতি, বাঁধাই ত্রুটি, উল্টো প্রিন্টিং বা ভুল বই পাওয়া যায়, তবে পার্সেল গ্রহণের <strong>৭ দিনের মধ্যে</strong> আমাদের হেল্পলাইনে (+88 01726976982) অথবা হোয়াটসঅ্যাপে জানালে কোনো অতিরিক্ত চার্জ ছাড়াই সম্পূর্ণ বিনামূল্যে নতুন ফ্রেশ কপি কুরিয়ারের মাধ্যমে রিপ্লেস করে দেওয়া হবে।
                                    <div class="mt-3 pt-2.5 border-top d-flex align-items-center justify-content-between flex-wrap gap-2 text-muted small">
                                        <span><i class="fa-solid fa-tags me-1 text-primary"></i> ট্যাগ: ৭ দিনের রিটার্ন পলিসি, ফ্রি রিপ্লেসমেন্ট, রিফান্ড গ্যারান্টি</span>
                                        <div class="d-flex align-items-center gap-2 feedback-box">
                                            <span>উত্তরটি কি সহায়ক ছিল?</span>
                                            <button class="btn btn-sm btn-light py-0.5 px-2.5 rounded-pill helpful-btn" onclick="rateFaq(this, true)">👍 হ্যাঁ</button>
                                            <button class="btn btn-sm btn-light py-0.5 px-2.5 rounded-pill helpful-btn" onclick="rateFaq(this, false)">👎 না</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Q12: E-Book & Digital Reader App --}}
                    <div class="faq-item-card" data-category="ebooks" data-keywords="ebook digital reader pdf app ইবুক ডিজিটাল রিডার মোবাইল">
                        <div class="card border-0 rounded-4 overflow-hidden shadow-sm bg-white">
                            <div class="card-header bg-white border-0 p-0" id="headingQ12">
                                <button class="accordion-button collapsed fw-bold text-dark py-3.5 px-3 px-md-4 d-flex align-items-center gap-2.5" type="button" data-bs-toggle="collapse" data-bs-target="#collapseQ12" aria-expanded="false" aria-controls="collapseQ12">
                                    <span class="badge bg-secondary bg-opacity-15 text-dark rounded-pill px-2.5 py-1 small flex-shrink-0">ই-বুক ও ডিজিটাল</span>
                                    <span class="fs-6 fw-bold">ডিজিটাল ই-বুক কীভাবে ক্রয় করব এবং কোন কোন ডিভাইসে পড়া যাবে?</span>
                                </button>
                            </div>
                            <div id="collapseQ12" class="accordion-collapse collapse" aria-labelledby="headingQ12" data-bs-parent="#mainFaqAccordion">
                                <div class="card-body px-3 px-md-4 py-3 pt-0 text-secondary" style="font-size: 14.5px; line-height: 1.7;">
                                    আইডিয়া প্রকাশন প্ল্যাটফর্মে <strong>"ই-বুক"</strong> ক্যাটাগরি থেকে যেকোনো ডিজিটাল বই তাৎক্ষণিক অনলাইন পেমেন্টের মাধ্যমে আনলক করতে পারবেন। 
                                    ক্রয়কৃত বইগুলো আপনার ইউজার ড্যাশবোর্ডের <strong>"আমার লাইব্রেরি (My Library)"</strong> সেকশনে যুক্ত হবে এবং যেকোনো মোবাইল, ট্যাবলেট, ল্যাপটপ বা ডেস্কটপ ব্রাউজারে সুন্দর রিডিং এক্সপেরিয়েন্সে অফলাইন ও অনলাইনে পড়া যাবে।
                                    <div class="mt-3 pt-2.5 border-top d-flex align-items-center justify-content-between flex-wrap gap-2 text-muted small">
                                        <span><i class="fa-solid fa-tags me-1 text-primary"></i> ট্যাগ: ই-বুক লাইব্রেরি, ডিজিটাল রিডার, বুকমার্কিং</span>
                                        <div class="d-flex align-items-center gap-2 feedback-box">
                                            <span>উত্তরটি কি সহায়ক ছিল?</span>
                                            <button class="btn btn-sm btn-light py-0.5 px-2.5 rounded-pill helpful-btn" onclick="rateFaq(this, true)">👍 হ্যাঁ</button>
                                            <button class="btn btn-sm btn-light py-0.5 px-2.5 rounded-pill helpful-btn" onclick="rateFaq(this, false)">👎 না</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Q13: Institutional / University Bulk Orders --}}
                    <div class="faq-item-card" data-category="orders" data-keywords="institutional bulk university library প্রাতিষ্ঠানিক বাল্ক লাইব্রেরি বিশ্ববিদ্যালয়">
                        <div class="card border-0 rounded-4 overflow-hidden shadow-sm bg-white">
                            <div class="card-header bg-white border-0 p-0" id="headingQ13">
                                <button class="accordion-button collapsed fw-bold text-dark py-3.5 px-3 px-md-4 d-flex align-items-center gap-2.5" type="button" data-bs-toggle="collapse" data-bs-target="#collapseQ13" aria-expanded="false" aria-controls="collapseQ13">
                                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2.5 py-1 small flex-shrink-0">বই ও অর্ডার</span>
                                    <span class="fs-6 fw-bold">শিক্ষা প্রতিষ্ঠান বা বিশ্ববিদ্যালয় লাইব্রেরির জন্য বাল্ক অর্ডারে অফিশিয়াল ইনভয়েস পাওয়া যাবে?</span>
                                </button>
                            </div>
                            <div id="collapseQ13" class="accordion-collapse collapse" aria-labelledby="headingQ13" data-bs-parent="#mainFaqAccordion">
                                <div class="card-body px-3 px-md-4 py-3 pt-0 text-secondary" style="font-size: 14.5px; line-height: 1.7;">
                                    হ্যাঁ, স্কুল, কলেজ, মাদ্রাসা, পাবলিক ও প্রাইভেট বিশ্ববিদ্যালয় এবং এনজিও বা করপোরেট লাইব্রেরির জন্য অফিশিয়াল প্যাডেড ইনভয়েস, ভ্যাট চালান ও বিশেষ প্রাতিষ্ঠানিক ডিসকাউন্ট প্রদান করা হয়। 
                                    বাল্ক রিকুইজিশনের জন্য সরাসরি <a href="mailto:ideapbd@gmail.com" class="fw-bold text-primary text-decoration-none">ideapbd@gmail.com</a> এ মেইল করতে পারেন অথবা আমাদের হটলাইনে যোগাযোগ করতে পারেন।
                                    <div class="mt-3 pt-2.5 border-top d-flex align-items-center justify-content-between flex-wrap gap-2 text-muted small">
                                        <span><i class="fa-solid fa-tags me-1 text-primary"></i> ট্যাগ: প্রাতিষ্ঠানিক অর্ডার, ভ্যাট ইনভয়েস, লাইব্রেরি রিকুইজিশন</span>
                                        <div class="d-flex align-items-center gap-2 feedback-box">
                                            <span>উত্তরটি কি সহায়ক ছিল?</span>
                                            <button class="btn btn-sm btn-light py-0.5 px-2.5 rounded-pill helpful-btn" onclick="rateFaq(this, true)">👍 হ্যাঁ</button>
                                            <button class="btn btn-sm btn-light py-0.5 px-2.5 rounded-pill helpful-btn" onclick="rateFaq(this, false)">👎 না</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Q14: Customer Care & Support Time --}}
                    <div class="faq-item-card" data-category="payments" data-keywords="support helpline customer care সময়সূচী হেল্পলাইন কাস্টমার কেয়ার">
                        <div class="card border-0 rounded-4 overflow-hidden shadow-sm bg-white">
                            <div class="card-header bg-white border-0 p-0" id="headingQ14">
                                <button class="accordion-button collapsed fw-bold text-dark py-3.5 px-3 px-md-4 d-flex align-items-center gap-2.5" type="button" data-bs-toggle="collapse" data-bs-target="#collapseQ14" aria-expanded="false" aria-controls="collapseQ14">
                                    <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-2.5 py-1 small flex-shrink-0">সহায়তা ও যোগাযোগ</span>
                                    <span class="fs-6 fw-bold">আইডিয়া প্রকাশন কাস্টমার কেয়ার ও সাপোর্ট সময়সূচী কী?</span>
                                </button>
                            </div>
                            <div id="collapseQ14" class="accordion-collapse collapse" aria-labelledby="headingQ14" data-bs-parent="#mainFaqAccordion">
                                <div class="card-body px-3 px-md-4 py-3 pt-0 text-secondary" style="font-size: 14.5px; line-height: 1.7;">
                                    আমাদের কাস্টমার কেয়ার হেল্পলাইন <strong>শনিবার থেকে বৃহস্পতিবার সকাল ৯:০০ টা থেকে রাত ১১:০০ টা পর্যন্ত</strong> সরাসরি সচল থাকে। এছাড়াও হোয়াটসঅ্যাপ ও ইমেইল সাপোর্ট সপ্তাহের ৭ দিনই ২৪ ঘণ্টা উন্মুক্ত থাকে। যেকোনো প্রয়োজনে আমাদের <a href="{{ route('contact') }}" class="fw-bold text-primary text-decoration-none">যোগাযোগ পেজে</a> মেসেজ ড্রপ করতে পারেন।
                                    <div class="mt-3 pt-2.5 border-top d-flex align-items-center justify-content-between flex-wrap gap-2 text-muted small">
                                        <span><i class="fa-solid fa-tags me-1 text-primary"></i> ট্যাগ: সাপোর্ট সময়সূচী, হেল্পলাইন, কাস্টমার কেয়ার</span>
                                        <div class="d-flex align-items-center gap-2 feedback-box">
                                            <span>উত্তরটি কি সহায়ক ছিল?</span>
                                            <button class="btn btn-sm btn-light py-0.5 px-2.5 rounded-pill helpful-btn" onclick="rateFaq(this, true)">👍 হ্যাঁ</button>
                                            <button class="btn btn-sm btn-light py-0.5 px-2.5 rounded-pill helpful-btn" onclick="rateFaq(this, false)">👎 না</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- Empty Search Result State --}}
                <div id="faqNoResultState" class="text-center py-5 d-none">
                    <div class="bg-white rounded-circle shadow-sm p-4 d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="fa-solid fa-circle-question fs-2 text-muted opacity-50"></i>
                    </div>
                    <h4 class="fw-bold text-dark mb-1">কোনো প্রশ্নের সন্ধান মেলেনি</h4>
                    <p class="text-muted small mb-3">অন্য কোনো শব্দ দিয়ে সার্চ করুন অথবা আমাদের সহায়তা ডেস্কে সরাসরি প্রশ্নটি পাঠান।</p>
                    <button class="btn btn-outline-secondary rounded-pill px-4 fw-bold me-2" onclick="document.getElementById('clearFaqSearchBtn').click()">
                        <i class="fa-solid fa-rotate-left me-1"></i> রিসেট করুন
                    </button>
                    <a href="https://wa.me/8801726976982" target="_blank" class="btn btn-success rounded-pill px-4 fw-bold shadow-xs">
                        <i class="fa-brands fa-whatsapp me-1"></i> সরাসরি হোয়াটসঅ্যাপে জিজ্ঞাসা করুন
                    </a>
                </div>
            </div>
        </div>

    </section>

    {{-- 4. Direct Assistance CTA Banner --}}
    <section class="container mt-5 pt-3">
        <div class="card border-0 rounded-4 shadow-sm p-4 p-lg-5 text-white position-relative overflow-hidden text-center" 
             style="background: linear-gradient(135deg, #07192f 0%, #004d40 60%, #006a4e 100%);">
            <div class="position-relative z-1" style="max-width: 680px; margin: 0 auto;">
                <div class="d-inline-flex align-items-center gap-2 bg-white bg-opacity-20 rounded-pill px-3 py-1 mb-3 fw-bold small text-white">
                    <i class="fa-solid fa-headset text-warning"></i>
                    <span>তাৎক্ষণিক কাস্টমার সাপোর্ট</span>
                </div>
                <h3 class="fw-black mb-2 fs-3 text-white">আপনার প্রশ্নের উত্তর কি খুঁজে পাননি?</h3>
                <p class="text-white-50 mb-4" style="font-size: 15px; line-height: 1.6;">
                    আমাদের সার্বক্ষণিক সহায়তা টিম আপনার যেকোনো পরামর্শ, অভিযোগ বা প্রকাশনা অনুসন্ধানের দ্রুত সমাধান দিতে প্রস্তুত।
                </p>
                <div class="d-flex align-items-center justify-content-center gap-3 flex-wrap">
                    <a href="https://wa.me/8801726976982?text={{ urlencode('হ্যালো, আইডিয়া প্রকাশন সাপোর্ট টিমের সাথে কথা বলতে চাই।') }}" target="_blank" 
                       class="btn btn-success btn-lg rounded-pill px-4 py-2.5 fw-bold shadow-sm d-inline-flex align-items-center gap-2">
                        <i class="fa-brands fa-whatsapp fs-5"></i>
                        <span>হোয়াটসঅ্যাপে কথা বলুন</span>
                    </a>
                    <a href="{{ route('contact') }}" 
                       class="btn btn-outline-light btn-lg rounded-pill px-4 py-2.5 fw-bold shadow-sm d-inline-flex align-items-center gap-2">
                        <i class="fa-solid fa-envelope"></i>
                        <span>যোগাযোগ ফরম পূরণ করুন</span>
                    </a>
                </div>
            </div>
        </div>
    </section>

</div>

{{-- Styles --}}
<style>
.faq-tab-btn {
    background: #ffffff;
    border: 1.5px solid #e2e8f0;
    color: #475569;
    font-weight: 600;
    font-size: 13.5px;
    padding: 8px 18px;
    border-radius: 9999px;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}
.faq-tab-btn:hover {
    border-color: #006a4e;
    color: #006a4e;
    background: #f0fdf4;
    transform: translateY(-1px);
}
.faq-tab-btn.active {
    background: linear-gradient(135deg, #006a4e 0%, #004d40 100%);
    border-color: transparent;
    color: #ffffff;
    box-shadow: 0 4px 12px rgba(0, 106, 78, 0.25);
}
.faq-tab-btn.active .badge {
    background: rgba(255,255,255,0.2) !important;
    color: #ffffff !important;
}
.accordion-button {
    background-color: #ffffff;
    box-shadow: none !important;
    border: none;
}
.accordion-button:not(.collapsed) {
    background: #f0fdf4;
    color: #004d40;
    border-bottom: 1px solid rgba(0, 106, 78, 0.12);
}
.accordion-button::after {
    background-size: 14px;
    transition: transform 0.25s ease-in-out;
}
.helpful-btn {
    border: 1px solid #e2e8f0;
    transition: all 0.15s ease;
}
.helpful-btn:hover {
    background-color: #006a4e !important;
    color: #ffffff !important;
    border-color: #006a4e !important;
}
.backdrop-blur {
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
}
</style>

{{-- Script --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('faqLiveSearchInput');
    const clearBtn = document.getElementById('clearFaqSearchBtn');
    const counter = document.getElementById('faqSearchResultCounter');
    const tabBtns = document.querySelectorAll('.faq-tab-btn');
    const faqItems = document.querySelectorAll('.faq-item-card');
    const noResultState = document.getElementById('faqNoResultState');
    const visibleFaqCount = document.getElementById('visibleFaqCount');
    const activeCategoryTitle = document.getElementById('activeFaqCategoryTitle');

    let currentCategory = 'all';

    const categoryTitles = {
        'all': 'সকল সাধারণ জিজ্ঞাসা',
        'orders': 'বই ও ডেলিভারি সংক্রান্ত প্রশ্নোত্তর',
        'authors': 'লেখক ও সম্মানী সংক্রান্ত প্রশ্নোত্তর',
        'publishing': 'প্রকাশনা ও ISBN সংক্রান্ত প্রশ্নোত্তর',
        'sellers': 'সেলার ও বুকশপ সংক্রান্ত প্রশ্নোত্তর',
        'payments': 'পেমেন্ট ও রিটার্ন পলিসি সংক্রান্ত প্রশ্নোত্তর',
        'ebooks': 'ই-বুক ও ডিজিটাল রিডিং সংক্রান্ত প্রশ্নোত্তর'
    };

    function filterFaqs() {
        const query = (searchInput?.value || '').trim().toLowerCase();
        let visibleCount = 0;

        if (query.length > 0) {
            clearBtn.style.display = 'inline-block';
        } else {
            clearBtn.style.display = 'none';
        }

        faqItems.forEach(item => {
            const itemCat = item.getAttribute('data-category');
            const itemKeywords = (item.getAttribute('data-keywords') || '').toLowerCase();
            const itemText = item.textContent.toLowerCase();

            const matchesCategory = (currentCategory === 'all' || itemCat === currentCategory);
            const matchesQuery = (query === '' || itemKeywords.includes(query) || itemText.includes(query));

            if (matchesCategory && matchesQuery) {
                item.style.display = 'block';
                visibleCount++;
            } else {
                item.style.display = 'none';
            }
        });

        if (visibleCount === 0) {
            noResultState.classList.remove('d-none');
            counter.classList.add('d-none');
            visibleFaqCount.textContent = `(০টি প্রশ্ন)`;
        } else {
            noResultState.classList.add('d-none');
            visibleFaqCount.textContent = `(${visibleCount}টি প্রশ্নোত্তর প্রদর্শিত)`;
            if (query.length > 0) {
                counter.textContent = `🔍 "${query}" এর সাথে সম্পর্কিত ${visibleCount}টি উত্তর পাওয়া গেছে`;
                counter.classList.remove('d-none');
            } else {
                counter.classList.add('d-none');
            }
        }

        activeCategoryTitle.textContent = categoryTitles[currentCategory] || 'সাধারণ জিজ্ঞাসা';
    }

    if (searchInput) {
        searchInput.addEventListener('input', filterFaqs);
    }

    if (clearBtn) {
        clearBtn.addEventListener('click', function() {
            searchInput.value = '';
            filterFaqs();
            searchInput.focus();
        });
    }

    tabBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            tabBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentCategory = this.getAttribute('data-category');
            filterFaqs();
        });
    });
});

function toggleAllFaqs(expand) {
    const collapses = document.querySelectorAll('#mainFaqAccordion .accordion-collapse');
    collapses.forEach(collapseEl => {
        const bsCollapse = bootstrap.Collapse.getOrCreateInstance(collapseEl, { toggle: false });
        if (expand) {
            bsCollapse.show();
        } else {
            bsCollapse.hide();
        }
    });
}

function rateFaq(btn, isYes) {
    const parent = btn.closest('.feedback-box');
    if (parent) {
        parent.innerHTML = `<span class="badge bg-success text-white px-2.5 py-1 fw-bold"><i class="fa-solid fa-circle-check me-1"></i> আপনার মূল্যবান মতামতের জন্য ধন্যবাদ!</span>`;
    }
}
</script>
@endsection
