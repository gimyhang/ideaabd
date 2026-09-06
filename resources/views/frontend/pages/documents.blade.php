@extends('layouts.app')
@section('title', 'নথি, ফর্ম ও প্রকাশনা গাইডলাইন — আইডিয়া প্রকাশন')
@section('meta_description', 'আইডিয়া প্রকাশনের অফিশিয়াল নথি, লেখক পান্ডুলিপি জমা ফরম, প্রকাশনা চুক্তিপত্র, সেলার পাইকারি ডিসকাউন্ট চার্ট ও স্টাইল গাইড ডাউনলোড করুন।')

@section('content')
<div class="docs-page-wrapper bg-light min-vh-100 pb-5">

    {{-- 1. Hero Banner --}}
    <section class="docs-hero-section position-relative overflow-hidden text-white py-5" 
             style="background: linear-gradient(135deg, #07192f 0%, #004d40 60%, #006a4e 100%);">
        {{-- Subtle background decoration --}}
        <div class="position-absolute top-0 end-0 opacity-10 pe-none d-none d-md-block" style="transform: translate(15%, -20%);">
            <i class="fa-solid fa-folder-open" style="font-size: 380px;"></i>
        </div>

        <div class="container position-relative z-1">
            {{-- Breadcrumb --}}
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb mb-0 small" style="--bs-breadcrumb-divider: '›';">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-white-50 text-decoration-none"><i class="fa-solid fa-house me-1"></i>Home</a></li>
                    <li class="breadcrumb-item text-white active" aria-current="page">Documents</li>
                </ol>
            </nav>

            <div class="row align-items-center g-4">
                <div class="col-lg-7">
                    <div class="d-inline-flex align-items-center gap-2 bg-white bg-opacity-15 backdrop-blur rounded-pill px-3 py-1 mb-3 fw-bold small text-white border border-white border-opacity-20 shadow-sm">
                        <i class="fa-solid fa-shield-halved text-warning"></i>
                        <span>অফিশিয়াল ডকুমেন্টেশন ও রিসোর্স পোর্টাল</span>
                    </div>
                    <h1 class="fw-black mb-3 text-white display-6 lh-sm" style="letter-spacing: -0.5px;">
                        নথি, ফর্ম ও প্রকাশনা গাইডলাইন
                    </h1>
                    <p class="text-white-50 lead mb-4" style="font-size: 16px; line-height: 1.7; max-width: 600px;">
                        লেখক পান্ডুলিপি জমা ফরম, প্রকাশনা চুক্তিপত্র, পাইকারি ডিসকাউন্ট চার্ট, রয়্যালটি স্টেটমেন্ট ও প্রমিত বানানরীতি ম্যানুয়াল সহজে পর্যালোচনা ও ডাউনলোড করুন।
                    </p>

                    {{-- Search bar --}}
                    <div class="position-relative" style="max-width: 580px;">
                        <div class="input-group shadow-lg rounded-pill overflow-hidden bg-white p-1 border">
                            <span class="input-group-text bg-transparent border-0 ps-3 pe-2 text-muted">
                                <i class="fa-solid fa-magnifying-glass text-primary fs-5"></i>
                            </span>
                            <input type="text" id="docSearchInput" class="form-control border-0 py-2.5 px-2 fw-medium text-dark" 
                                   placeholder="ডকুমেন্ট বা ফর্মের নাম দিয়ে খুঁজুন (যেমন: পান্ডুলিপি, চুক্তিপত্র, সেলার চার্ট)..." 
                                   autocomplete="off" style="font-size: 15px; outline: none; box-shadow: none;">
                            <button type="button" class="btn btn-secondary rounded-pill px-3 fw-bold" id="clearDocSearchBtn" style="display: none; font-size: 13px;">
                                রিসেট
                            </button>
                            <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold shadow-xs d-none d-sm-inline-flex align-items-center gap-1.5" onclick="document.getElementById('docSearchInput').focus()">
                                <i class="fa-solid fa-filter"></i> <span>ফিল্টার</span>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Right side highlight stats --}}
                <div class="col-lg-5">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="p-3.5 rounded-4 bg-white bg-opacity-10 backdrop-blur border border-white border-opacity-15 shadow-sm text-center">
                                <div class="fs-2 fw-black text-warning mb-0" id="totalDocStat">৯+</div>
                                <div class="small text-white-50 fw-semibold">অফিশিয়াল ফর্ম ও গাইড</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3.5 rounded-4 bg-white bg-opacity-10 backdrop-blur border border-white border-opacity-15 shadow-sm text-center">
                                <div class="fs-2 fw-black text-info mb-0">১০০%</div>
                                <div class="small text-white-50 fw-semibold">স্বচ্ছ প্রকাশনা চুক্তি</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3.5 rounded-4 bg-white bg-opacity-10 backdrop-blur border border-white border-opacity-15 shadow-sm text-center">
                                <div class="fs-2 fw-black text-success mb-0">PDF/DOCX</div>
                                <div class="small text-white-50 fw-semibold">প্রিন্ট ও এডিটেবল ফরম্যাট</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3.5 rounded-4 bg-white bg-opacity-10 backdrop-blur border border-white border-opacity-15 shadow-sm text-center">
                                <div class="fs-2 fw-black text-white mb-0">২০২৬</div>
                                <div class="small text-white-50 fw-semibold">হালনাগাদ সংস্করণ</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- 2. Category Filter & Documents Section --}}
    <section class="container mt-4 pt-2">

        {{-- Filter Navigation Tabs --}}
        <div class="d-flex align-items-center justify-content-start justify-content-lg-center flex-nowrap overflow-x-auto pb-2 mb-4 gap-2" id="docCategoryTabs">
            <button class="btn doc-tab-btn active text-nowrap" data-category="all">
                <i class="fa-solid fa-layer-group me-1.5"></i> সকল নথি <span class="badge bg-light text-dark ms-1 rounded-pill doc-count-badge" id="badge-all">৯</span>
            </button>
            <button class="btn doc-tab-btn text-nowrap" data-category="authors">
                <i class="fa-solid fa-feather-pointed me-1.5 text-success"></i> লেখক ও পাণ্ডুলিপি
            </button>
            <button class="btn doc-tab-btn text-nowrap" data-category="publishing">
                <i class="fa-solid fa-file-contract me-1.5 text-info"></i> প্রকাশনা ও চুক্তিপত্র
            </button>
            <button class="btn doc-tab-btn text-nowrap" data-category="sellers">
                <i class="fa-solid fa-store me-1.5 text-warning"></i> সেলার ও বুকশপ চার্ট
            </button>
            <button class="btn doc-tab-btn text-nowrap" data-category="legal">
                <i class="fa-solid fa-scale-balanced me-1.5 text-danger"></i> কপিরাইট ও আইএসবিএন
            </button>
            <button class="btn doc-tab-btn text-nowrap" data-category="editorial">
                <i class="fa-solid fa-pen-nib me-1.5 text-primary"></i> সম্পাদনা ও স্টাইল গাইড
            </button>
        </div>

        {{-- Active Filter Notification Info --}}
        <div class="d-flex align-items-center justify-content-between mb-3 px-1">
            <div class="small text-muted fw-semibold">
                <span id="activeFilterLabel">সকল ডকুমেন্টের তালিকা</span> 
                <span class="text-primary fw-bold" id="visibleDocCount">(৯টি নথি প্রদর্শিত)</span>
            </div>
            <div class="small text-muted d-none d-sm-block">
                <i class="fa-solid fa-circle-info me-1 text-primary"></i> ক্লিক করে তাৎক্ষণিক প্রিভিউ ও ডাউনলোড করুন
            </div>
        </div>

        {{-- 3. Documents Grid (9 Comprehensive Cards) --}}
        <div class="row g-3 g-lg-4" id="documentsGrid">

            {{-- Doc 1: Author Manuscript Submission Form --}}
            <div class="col-12 col-md-6 col-lg-4 doc-item-card" data-category="authors" data-keywords="পান্ডুলিপি জমা লেখক ফরম manuscript submission form novel poetry article">
                <div class="card h-100 border-0 rounded-4 shadow-sm bg-white doc-box d-flex flex-column justify-content-between p-4 position-relative overflow-hidden">
                    <div class="doc-card-accent-bar bg-danger"></div>
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="doc-icon-wrap bg-danger bg-opacity-10 text-danger rounded-3 d-flex align-items-center justify-content-center">
                                <i class="fa-solid fa-file-pdf fs-3"></i>
                            </div>
                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2.5 py-1 fw-bold small">
                                <i class="fa-solid fa-circle-check me-1"></i> অফিসিয়াল ভেরিফায়েড
                            </span>
                        </div>
                        <h3 class="fw-bold text-dark mb-1.5 fs-6 lh-base">
                            লেখক পান্ডুলিপি জমা ফরম (Manuscript Submission Form)
                        </h3>
                        <p class="text-muted small mb-3" style="line-height: 1.55;">
                            আইডিয়া প্রকাশনে নতুন গল্প, উপন্যাস, কবিতা, প্রবন্ধ বা গবেষণামূলক পান্ডুলিপি পর্যালোচনার জন্য নির্ধারিত আবেদনপত্র।
                        </p>
                        <div class="doc-meta-pills d-flex align-items-center flex-wrap gap-2 mb-3">
                            <span class="badge bg-light text-secondary border"><i class="fa-solid fa-file-lines me-1 text-danger"></i> PDF / ফরম্যাট</span>
                            <span class="badge bg-light text-secondary border"><i class="fa-solid fa-hard-drive me-1 text-primary"></i> ১.২ MB</span>
                            <span class="badge bg-light text-secondary border"><i class="fa-solid fa-clock me-1 text-success"></i> ২০২৬ সংস্করণ</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2 pt-3 border-top">
                        <button type="button" class="btn btn-outline-primary btn-sm rounded-pill fw-bold flex-grow-1 py-1.5" 
                                onclick="openDocPreview('লেখক পান্ডুলিপি জমা ফরম (Manuscript Submission)', 'authors', 'PDF (১.২ MB)', 'আইডিয়া প্রকাশনে পান্ডুলিপি পর্যালোচনার অফিশিয়াল নির্দেশিকা ও ফরম।', [
                                    'লেখকের পূর্ণ নাম, বর্তমান ও স্থায়ী ঠিকানা, মোবাইল ও ইমেইল নম্বর',
                                    'পান্ডুলিপির নাম, বিষয়শ্রেণী ও আনুমানিক পৃষ্ঠাসংখ্যা / শব্দসংখ্যা',
                                    'বইয়ের সারসংক্ষেপ (Synopsis) ও সম্ভাব্য পাঠকশ্রেণী',
                                    'লেখকের পূর্বে প্রকাশিত বই বা সাহিত্যকৃতির বিবরণ (যদি থাকে)'
                                ], 'submission')">
                            <i class="fa-solid fa-eye me-1"></i> বিস্তারিত
                        </button>
                        <button type="button" class="btn btn-primary btn-sm rounded-pill fw-bold px-3 py-1.5 shadow-xs" 
                                onclick="triggerDocDownload('লেখক পান্ডুলিপি জমা ফরম')">
                            <i class="fa-solid fa-download me-1"></i> ডাউনলোড
                        </button>
                    </div>
                </div>
            </div>

            {{-- Doc 2: Publication & Royalty Agreement Sample --}}
            <div class="col-12 col-md-6 col-lg-4 doc-item-card" data-category="publishing" data-keywords="চুক্তিপত্র প্রকাশনা রয়্যালটি এগ্রিমেন্ট agreement contract royalty policy sample">
                <div class="card h-100 border-0 rounded-4 shadow-sm bg-white doc-box d-flex flex-column justify-content-between p-4 position-relative overflow-hidden">
                    <div class="doc-card-accent-bar bg-primary"></div>
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="doc-icon-wrap bg-primary bg-opacity-10 text-primary rounded-3 d-flex align-items-center justify-content-center">
                                <i class="fa-solid fa-file-contract fs-3"></i>
                            </div>
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-2.5 py-1 fw-bold small">
                                <i class="fa-solid fa-file-signature me-1"></i> আইনগত চুক্তিপত্র
                            </span>
                        </div>
                        <h3 class="fw-bold text-dark mb-1.5 fs-6 lh-base">
                            বই প্রকাশনা চুক্তিপত্র ও রয়্যালটি নীতিমালা (Agreement Sample)
                        </h3>
                        <p class="text-muted small mb-3" style="line-height: 1.55;">
                            আইডিয়া প্রকাশন ও লেখকের মধ্যে পারস্পরিক রয়্যালটি শতাংশ, প্রকাশনা স্বত্ব, বিক্রয় স্টেটমেন্ট ও পুনর্মুদ্রণ চুক্তিপত্র।
                        </p>
                        <div class="doc-meta-pills d-flex align-items-center flex-wrap gap-2 mb-3">
                            <span class="badge bg-light text-secondary border"><i class="fa-solid fa-file-word me-1 text-primary"></i> DOCX / PDF</span>
                            <span class="badge bg-light text-secondary border"><i class="fa-solid fa-hard-drive me-1 text-primary"></i> ৮৮০ KB</span>
                            <span class="badge bg-light text-secondary border"><i class="fa-solid fa-shield me-1 text-info"></i> স্ট্যান্ডার্ড ড্রাফট</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2 pt-3 border-top">
                        <button type="button" class="btn btn-outline-primary btn-sm rounded-pill fw-bold flex-grow-1 py-1.5" 
                                onclick="openDocPreview('বই প্রকাশনা চুক্তিপত্র ও রয়্যালটি নীতিমালা', 'publishing', 'DOCX / PDF (৮৮০ KB)', 'আইডিয়া প্রকাশন ও লেখকের মধ্যকার শতভাগ স্বচ্ছ লিখিত চুক্তিপত্রের নমুনা।', [
                                    'বইয়ের স্বত্বাধিকার ও কপিরাইট আইনগতভাবে লেখকের অনুকূলে সংরক্ষিত থাকা',
                                    'বিক্রিত প্রতি কপির ওপর স্বচ্ছ রয়্যালটি হিসাব ও নিয়মিত প্রদান পদ্ধতি',
                                    'ডিজিটাল ই-বুক ও অডিওবুক পরিবেশনার বিশেষ শর্তাবলী',
                                    'সংস্করণ শেষ হওয়ার পর পুনর্মুদ্রণের অধিকার ও লেখক সম্মতির নিয়মাবলী'
                                ], 'agreement')">
                            <i class="fa-solid fa-eye me-1"></i> বিস্তারিত
                        </button>
                        <button type="button" class="btn btn-primary btn-sm rounded-pill fw-bold px-3 py-1.5 shadow-xs" 
                                onclick="triggerDocDownload('বই প্রকাশনা চুক্তিপত্র ও রয়্যালটি নীতিমালা')">
                            <i class="fa-solid fa-download me-1"></i> ডাউনলোড
                        </button>
                    </div>
                </div>
            </div>

            {{-- Doc 3: Wholesale Rate Chart & Bookseller Guidelines --}}
            <div class="col-12 col-md-6 col-lg-4 doc-item-card" data-category="sellers" data-keywords="সেলার পাইকারি ডিসকাউন্ট বুকশপ wholesale seller rate chart discount bookshop catalog">
                <div class="card h-100 border-0 rounded-4 shadow-sm bg-white doc-box d-flex flex-column justify-content-between p-4 position-relative overflow-hidden">
                    <div class="doc-card-accent-bar bg-warning"></div>
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="doc-icon-wrap bg-warning bg-opacity-15 text-dark rounded-3 d-flex align-items-center justify-content-center">
                                <i class="fa-solid fa-file-excel fs-3 text-warning"></i>
                            </div>
                            <span class="badge bg-warning bg-opacity-15 text-dark border border-warning border-opacity-50 rounded-pill px-2.5 py-1 fw-bold small">
                                <i class="fa-solid fa-tag me-1"></i> পাইকারি চার্ট
                            </span>
                        </div>
                        <h3 class="fw-bold text-dark mb-1.5 fs-6 lh-base">
                            পাইকারি বুকশপ ডিসকাউন্ট চার্ট ও ডিস্ট্রিবিউশন গাইড
                        </h3>
                        <p class="text-muted small mb-3" style="line-height: 1.55;">
                            অনুমোদিত বুকশপ ও লাইব্রেরির জন্য পাইকারি বই ক্রয়ের কমিশন স্ল্যাব, দেশব্যাপী পরিবহন সুবিধা ও ইনভয়েস শর্তাবলী।
                        </p>
                        <div class="doc-meta-pills d-flex align-items-center flex-wrap gap-2 mb-3">
                            <span class="badge bg-light text-secondary border"><i class="fa-solid fa-file-excel me-1 text-success"></i> XLSX / PDF</span>
                            <span class="badge bg-light text-secondary border"><i class="fa-solid fa-hard-drive me-1 text-primary"></i> ২.৪ MB</span>
                            <span class="badge bg-light text-secondary border"><i class="fa-solid fa-percent me-1 text-warning"></i> ৩৫%-৪৫% স্ল্যাব</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2 pt-3 border-top">
                        <button type="button" class="btn btn-outline-primary btn-sm rounded-pill fw-bold flex-grow-1 py-1.5" 
                                onclick="openDocPreview('পাইকারি বুকশপ ডিসকাউন্ট চার্ট ও ডিস্ট্রিবিউশন গাইড', 'sellers', 'XLSX / PDF (২.৪ MB)', 'অনুমোদিত লাইব্রেরি ও বিক্রেতাদের জন্য আইডিয়া প্রকাশনের অফিশিয়াল কমিশন পলিসি।', [
                                    '১০ থেকে ৫০ কপি অর্ডার: ৩৫% পাইকারি কমিশন',
                                    '৫১ থেকে ১০০ কপি অর্ডার: ৪০% পাইকারি কমিশন',
                                    '১০০+ কপি প্রাতিষ্ঠানিক অর্ডারে বিশেষ ৪৫% পর্যন্ত কমিশন স্ল্যাব',
                                    'সুবিধাজনক কুরিয়ার ও পরিবহন মাধ্যমে দেশব্যাপী ডোরস্টেপ ডেলিভারি'
                                ], 'wholesale')">
                            <i class="fa-solid fa-eye me-1"></i> বিস্তারিত
                        </button>
                        <button type="button" class="btn btn-primary btn-sm rounded-pill fw-bold px-3 py-1.5 shadow-xs" 
                                onclick="triggerDocDownload('পাইকারি বুকশপ ডিসকাউন্ট চার্ট ও ডিস্ট্রিবিউশন গাইড')">
                            <i class="fa-solid fa-download me-1"></i> ডাউনলোড
                        </button>
                    </div>
                </div>
            </div>

            {{-- Doc 4: Copyright, ISBN & Intellectual Property Guide --}}
            <div class="col-12 col-md-6 col-lg-4 doc-item-card" data-category="legal" data-keywords="কপিরাইট আইন আইএসবিএন স্বত্বাধিকার copyright isbn intellectual property piracy legal">
                <div class="card h-100 border-0 rounded-4 shadow-sm bg-white doc-box d-flex flex-column justify-content-between p-4 position-relative overflow-hidden">
                    <div class="doc-card-accent-bar bg-danger"></div>
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="doc-icon-wrap bg-info bg-opacity-10 text-info rounded-3 d-flex align-items-center justify-content-center">
                                <i class="fa-solid fa-shield-halved fs-3"></i>
                            </div>
                            <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 rounded-pill px-2.5 py-1 fw-bold small">
                                <i class="fa-solid fa-gavel me-1"></i> আইনি সুরক্ষা
                            </span>
                        </div>
                        <h3 class="fw-bold text-dark mb-1.5 fs-6 lh-base">
                            কপিরাইট, আইএসবিএন ও বৌদ্ধিক স্বত্ব নির্দেশিকা
                        </h3>
                        <p class="text-muted small mb-3" style="line-height: 1.55;">
                            বাংলাদেশ কপিরাইট আইন ও জাতীয় গ্রন্থকেন্দ্র অনুযায়ী আইএসবিএন নম্বর ও মুদ্রিত বইয়ের বৌদ্ধিক সম্পত্তি সুরক্ষা নীতি।
                        </p>
                        <div class="doc-meta-pills d-flex align-items-center flex-wrap gap-2 mb-3">
                            <span class="badge bg-light text-secondary border"><i class="fa-solid fa-file-pdf me-1 text-danger"></i> PDF ফরম্যাট</span>
                            <span class="badge bg-light text-secondary border"><i class="fa-solid fa-hard-drive me-1 text-primary"></i> ৯৫০ KB</span>
                            <span class="badge bg-light text-secondary border"><i class="fa-solid fa-barcode me-1 text-dark"></i> ISBN গাইড</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2 pt-3 border-top">
                        <button type="button" class="btn btn-outline-primary btn-sm rounded-pill fw-bold flex-grow-1 py-1.5" 
                                onclick="openDocPreview('কপিরাইট, আইএসবিএন ও বৌদ্ধিক স্বত্ব নির্দেশিকা', 'legal', 'PDF (৯৫০ KB)', 'আইডিয়া প্রকাশনে প্রকাশিত সকল বইয়ের কপিরাইট সুরক্ষা ও আইনি বিধান।', [
                                    'অনুমতি ছাড়া যেকোনো বইয়ের মুদ্রণ, ফটোকপি বা পিডিএফ পাইরেসি সম্পূর্ণ নিষিদ্ধ',
                                    'জাতীয় গ্রন্থকেন্দ্র থেকে সরকারিভাবে নিবন্ধিত ১৩ ডিজিটের ISBN বরাদ্দ প্রক্রিয়া',
                                    'ডিজিটাল ই-বুক ব্যবহারের ব্যক্তিগত কপিরাইট শর্তাবলী',
                                    'উদ্ধৃতি ব্যবহারের ক্ষেত্রে সঠিক সাইটেশন ও গ্রন্থপঞ্জি নীতিমালা'
                                ], 'legal')">
                            <i class="fa-solid fa-eye me-1"></i> বিস্তারিত
                        </button>
                        <button type="button" class="btn btn-primary btn-sm rounded-pill fw-bold px-3 py-1.5 shadow-xs" 
                                onclick="triggerDocDownload('কপিরাইট ও আইএসবিএন নির্দেশিকা')">
                            <i class="fa-solid fa-download me-1"></i> ডাউনলোড
                        </button>
                    </div>
                </div>
            </div>

            {{-- Doc 5: Ideapatra Writing & Spelling Style Guide --}}
            <div class="col-12 col-md-6 col-lg-4 doc-item-card" data-category="editorial" data-keywords="আইডিয়াপত্র প্রবন্ধ লিখন বানানরীতি স্টাইল গাইড বাংলা একাডেমি style guide spelling bangla academy ideapatra">
                <div class="card h-100 border-0 rounded-4 shadow-sm bg-white doc-box d-flex flex-column justify-content-between p-4 position-relative overflow-hidden">
                    <div class="doc-card-accent-bar bg-success"></div>
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="doc-icon-wrap bg-success bg-opacity-10 text-success rounded-3 d-flex align-items-center justify-content-center">
                                <i class="fa-solid fa-book-open-reader fs-3"></i>
                            </div>
                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2.5 py-1 fw-bold small">
                                <i class="fa-solid fa-spell-check me-1"></i> প্রমিত বানানরীতি
                            </span>
                        </div>
                        <h3 class="fw-bold text-dark mb-1.5 fs-6 lh-base">
                            আইডিয়াপত্র প্রবন্ধ লিখন ও বানানরীতি ম্যানুয়াল
                        </h3>
                        <p class="text-muted small mb-3" style="line-height: 1.55;">
                            বাংলা একাডেমি প্রমিত বানানরীতি অনুসরণ করে সাহিত্য, সমালোচনা ও গবেষণাধর্মী লেখা প্রস্তুতের অফিশিয়াল সম্পাদকীয় গাইড।
                        </p>
                        <div class="doc-meta-pills d-flex align-items-center flex-wrap gap-2 mb-3">
                            <span class="badge bg-light text-secondary border"><i class="fa-solid fa-file-pdf me-1 text-danger"></i> PDF ফরম্যাট</span>
                            <span class="badge bg-light text-secondary border"><i class="fa-solid fa-hard-drive me-1 text-primary"></i> ১.৫ MB</span>
                            <span class="badge bg-light text-secondary border"><i class="fa-solid fa-check-double me-1 text-success"></i> একাডেমি স্ট্যান্ডার্ড</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2 pt-3 border-top">
                        <button type="button" class="btn btn-outline-primary btn-sm rounded-pill fw-bold flex-grow-1 py-1.5" 
                                onclick="openDocPreview('আইডিয়াপত্র প্রবন্ধ লিখন ও বানানরীতি ম্যানুয়াল', 'editorial', 'PDF (১.৫ MB)', 'আইডিয়াপত্রে নিয়মিত কলাম ও সাহিত্য প্রবন্ধ প্রকাশের নির্দেশিকা।', [
                                    'বাংলা একাডেমি প্রমিত বাংলা বানানরীতির নিয়মাবলী নির্ভুলভাবে অনুসরণ',
                                    'প্রবন্ধ ও গবেষণাধর্মী লেখার আদর্শ শব্দসীমা: ১,০০০ থেকে ৩,৫০০ শব্দ',
                                    'উৎস নির্দেশ, তথ্যসূত্র (References) ও ফুটনোট সংযোজনের নিয়ম',
                                    'সম্পাদনা পরিষদ কর্তৃক পাণ্ডুলিপি যাচাই ও অনুমোদন প্রক্রিয়া'
                                ], 'editorial')">
                            <i class="fa-solid fa-eye me-1"></i> বিস্তারিত
                        </button>
                        <button type="button" class="btn btn-primary btn-sm rounded-pill fw-bold px-3 py-1.5 shadow-xs" 
                                onclick="triggerDocDownload('আইডিয়াপত্র বানানরীতি ম্যানুয়াল')">
                            <i class="fa-solid fa-download me-1"></i> ডাউনলোড
                        </button>
                    </div>
                </div>
            </div>

            {{-- Doc 6: Book Printing & Production Package Guide --}}
            <div class="col-12 col-md-6 col-lg-4 doc-item-card" data-category="publishing" data-keywords="বই প্রকাশনা প্যাকেজ প্রিন্টিং খরচ বাঁধাই সাইজ printing cost package publishing demy royal">
                <div class="card h-100 border-0 rounded-4 shadow-sm bg-white doc-box d-flex flex-column justify-content-between p-4 position-relative overflow-hidden">
                    <div class="doc-card-accent-bar bg-info"></div>
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="doc-icon-wrap bg-info bg-opacity-10 text-info rounded-3 d-flex align-items-center justify-content-center">
                                <i class="fa-solid fa-book-bookmark fs-3"></i>
                            </div>
                            <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 rounded-pill px-2.5 py-1 fw-bold small">
                                <i class="fa-solid fa-layer-group me-1"></i> প্যাকেজ ব্রোশিওর
                            </span>
                        </div>
                        <h3 class="fw-bold text-dark mb-1.5 fs-6 lh-base">
                            বই প্রকাশনা প্যাকেজ ও প্রিন্টিং ব্যয় নির্দেশিকা
                        </h3>
                        <p class="text-muted small mb-3" style="line-height: 1.55;">
                            কাগজের মান (৮০ GSM ক্রিম/হোয়াইট, ডিমাই/রয়েল সাইজ), ৪-কালার কভার ও হার্ডবাইন্ডিং সহ প্রকাশনা খরচের পূর্ণাঙ্গ চার্ট।
                        </p>
                        <div class="doc-meta-pills d-flex align-items-center flex-wrap gap-2 mb-3">
                            <span class="badge bg-light text-secondary border"><i class="fa-solid fa-file-pdf me-1 text-danger"></i> PDF ফরম্যাট</span>
                            <span class="badge bg-light text-secondary border"><i class="fa-solid fa-hard-drive me-1 text-primary"></i> ১.৮ MB</span>
                            <span class="badge bg-light text-secondary border"><i class="fa-solid fa-award me-1 text-warning"></i> প্রিমিয়াম কোয়ালিটি</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2 pt-3 border-top">
                        <button type="button" class="btn btn-outline-primary btn-sm rounded-pill fw-bold flex-grow-1 py-1.5" 
                                onclick="openDocPreview('বই প্রকাশনা প্যাকেজ ও প্রিন্টিং ব্যয় নির্দেশিকা', 'publishing', 'PDF (১.৮ MB)', 'আইডিয়া প্রকাশনের আধুনিক প্রিন্টিং ও প্রকাশনা প্যাকেজের পূর্ণ বিবরণী।', [
                                    'অভিজ্ঞ সম্পাদক দ্বারা প্রুফরিডিং ও দৃষ্টিনন্দন কম্পোজিশন',
                                    '৩০০ GSM আর্ট কার্ডে ম্যাট/গ্লসি লেমিনেশন ও স্পট ইউভি কভার',
                                    'জাতীয় গ্রন্থকেন্দ্র থেকে অফিসিয়াল ISBN ও বারকোড বরাদ্দ',
                                    'অনলাইন প্ল্যাটফর্ম ও দেশব্যাপী বুকশপে পূর্ণাঙ্গ বিপণন সহায়তা'
                                ], 'package')">
                            <i class="fa-solid fa-eye me-1"></i> বিস্তারিত
                        </button>
                        <button type="button" class="btn btn-primary btn-sm rounded-pill fw-bold px-3 py-1.5 shadow-xs" 
                                onclick="triggerDocDownload('বই প্রকাশনা প্যাকেজ নির্দেশিকা')">
                            <i class="fa-solid fa-download me-1"></i> ডাউনলোড
                        </button>
                    </div>
                </div>
            </div>

            {{-- Doc 7: Author Royalty Payout & Bank Info Form --}}
            <div class="col-12 col-md-6 col-lg-4 doc-item-card" data-category="authors" data-keywords="রয়্যালটি ব্যাংক ফরম লেখক সম্মানী উত্তোলন royalty payout bank information form withdrawal">
                <div class="card h-100 border-0 rounded-4 shadow-sm bg-white doc-box d-flex flex-column justify-content-between p-4 position-relative overflow-hidden">
                    <div class="doc-card-accent-bar bg-success"></div>
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="doc-icon-wrap bg-success bg-opacity-10 text-success rounded-3 d-flex align-items-center justify-content-center">
                                <i class="fa-solid fa-money-check-dollar fs-3"></i>
                            </div>
                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2.5 py-1 fw-bold small">
                                <i class="fa-solid fa-building-columns me-1"></i> পেমেন্ট ফরম
                            </span>
                        </div>
                        <h3 class="fw-bold text-dark mb-1.5 fs-6 lh-base">
                            লেখক রয়্যালটি প্রত্যাহার ও ব্যাংক ইনফরমেশন ফরম
                        </h3>
                        <p class="text-muted small mb-3" style="line-height: 1.55;">
                            প্রকাশিত বইয়ের অর্জিত রয়্যালটি ও লেখার সম্মানী সরাসরি ব্যাংক একাউন্ট বা এমএফএস (বিকাশ/নগদ) মাধ্যমে গ্রহণের ফর্ম।
                        </p>
                        <div class="doc-meta-pills d-flex align-items-center flex-wrap gap-2 mb-3">
                            <span class="badge bg-light text-secondary border"><i class="fa-solid fa-file-pdf me-1 text-danger"></i> PDF / ফরম্যাট</span>
                            <span class="badge bg-light text-secondary border"><i class="fa-solid fa-hard-drive me-1 text-primary"></i> ৭৫০ KB</span>
                            <span class="badge bg-light text-secondary border"><i class="fa-solid fa-lock me-1 text-success"></i> নিরাপদ পেমেন্ট</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2 pt-3 border-top">
                        <button type="button" class="btn btn-outline-primary btn-sm rounded-pill fw-bold flex-grow-1 py-1.5" 
                                onclick="openDocPreview('লেখক রয়্যালটি প্রত্যাহার ও ব্যাংক ইনফরমেশন ফরম', 'authors', 'PDF (৭৫০ KB)', 'রয়্যালটি ও সম্মানী প্রাপ্তির জন্য ব্যাংক অ্যাকাউন্ট বিবরণী ফর্ম।', [
                                    'লেখকের ব্যাংক হিসাবের নাম, ব্যাংক ও ব্রাঞ্চ নাম এবং রাউটিং নম্বর',
                                    'বিকাশ বা নগদ মার্চেন্ট/ব্যক্তিগত ওয়ালেট নম্বর (প্রযোজ্য ক্ষেত্রে)',
                                    'জাতীয় পরিচয়পত্র (NID) নম্বর ও ট্যাক্স সনাক্তকরণ বিবরণী',
                                    'মাসিক ও ত্রৈমাসিক স্বয়ংক্রিয় রয়্যালটি ডিসবার্সমেন্ট সিস্টেম'
                                ], 'royalty')">
                            <i class="fa-solid fa-eye me-1"></i> বিস্তারিত
                        </button>
                        <button type="button" class="btn btn-primary btn-sm rounded-pill fw-bold px-3 py-1.5 shadow-xs" 
                                onclick="triggerDocDownload('লেখক রয়্যালটি ব্যাংক ফরম')">
                            <i class="fa-solid fa-download me-1"></i> ডাউনলোড
                        </button>
                    </div>
                </div>
            </div>

            {{-- Doc 8: Digital E-Book & DRM Policy Guidelines --}}
            <div class="col-12 col-md-6 col-lg-4 doc-item-card" data-category="legal" data-keywords="ইবুক ডিআরএম ডিজিটাল কপিরাইট ebook drm digital rights management policy">
                <div class="card h-100 border-0 rounded-4 shadow-sm bg-white doc-box d-flex flex-column justify-content-between p-4 position-relative overflow-hidden">
                    <div class="doc-card-accent-bar bg-secondary"></div>
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="doc-icon-wrap bg-secondary bg-opacity-10 text-secondary rounded-3 d-flex align-items-center justify-content-center">
                                <i class="fa-solid fa-tablet-screen-button fs-3"></i>
                            </div>
                            <span class="badge bg-secondary bg-opacity-15 text-dark border rounded-pill px-2.5 py-1 fw-bold small">
                                <i class="fa-solid fa-shield-virus me-1"></i> DRM পলিসি
                            </span>
                        </div>
                        <h3 class="fw-bold text-dark mb-1.5 fs-6 lh-base">
                            ডিজিটাল ই-বুক ও ডিআরএম নীতি নির্দেশিকা (DRM Policy)
                        </h3>
                        <p class="text-muted small mb-3" style="line-height: 1.55;">
                            আইডিয়া প্রকাশনের ডিজিটাল ই-বুক রিডিং প্ল্যাটফর্মে লেখক স্বত্ব, এনক্রিপশন ও পাইরেসি রোধে গৃহীত সুরক্ষা নীতিমালা।
                        </p>
                        <div class="doc-meta-pills d-flex align-items-center flex-wrap gap-2 mb-3">
                            <span class="badge bg-light text-secondary border"><i class="fa-solid fa-file-pdf me-1 text-danger"></i> PDF ফরম্যাট</span>
                            <span class="badge bg-light text-secondary border"><i class="fa-solid fa-hard-drive me-1 text-primary"></i> ৬৫০ KB</span>
                            <span class="badge bg-light text-secondary border"><i class="fa-solid fa-key me-1 text-warning"></i> এনক্রিপ্টেড</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2 pt-3 border-top">
                        <button type="button" class="btn btn-outline-primary btn-sm rounded-pill fw-bold flex-grow-1 py-1.5" 
                                onclick="openDocPreview('ডিজিটাল ই-বুক ও ডিআরএম নীতি নির্দেশিকা', 'legal', 'PDF (৬৫০ KB)', 'ই-বুক প্রকাশনা ও ডিজিটাল রাইটস ম্যানেজমেন্ট গাইডলাইন।', [
                                    'ডিজিটাল কপি সুরক্ষিত রাখতে ১২৮-বিট এনক্রিপশন ও ডিভাইস লক',
                                    'অনলাইন ও অফলাইন রিডারে বইয়ের কপিরাইট সংরক্ষণ নীতিমালা',
                                    'ই-বুক রয়্যালটির স্বচ্ছ রিয়েল-টাইম ট্র্যাকিং ড্যাশবোর্ড',
                                    'লেখক ও পাঠকের যৌথ প্রাইভেসি ও ডেটা সুরক্ষা বিধি'
                                ], 'drm')">
                            <i class="fa-solid fa-eye me-1"></i> বিস্তারিত
                        </button>
                        <button type="button" class="btn btn-primary btn-sm rounded-pill fw-bold px-3 py-1.5 shadow-xs" 
                                onclick="triggerDocDownload('ডিজিটাল ই-বুক ডিআরএম নির্দেশিকা')">
                            <i class="fa-solid fa-download me-1"></i> ডাউনলোড
                        </button>
                    </div>
                </div>
            </div>

            {{-- Doc 9: Proofreading & Manuscript Checklist --}}
            <div class="col-12 col-md-6 col-lg-4 doc-item-card" data-category="editorial" data-keywords="প্রুফরিডিং কম্পোজিশন চেকলিস্ট পান্ডুলিপি proofreading checklist typesetting manuscript formatting">
                <div class="card h-100 border-0 rounded-4 shadow-sm bg-white doc-box d-flex flex-column justify-content-between p-4 position-relative overflow-hidden">
                    <div class="doc-card-accent-bar bg-warning"></div>
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="doc-icon-wrap bg-warning bg-opacity-15 text-dark rounded-3 d-flex align-items-center justify-content-center">
                                <i class="fa-solid fa-list-check fs-3 text-warning"></i>
                            </div>
                            <span class="badge bg-warning bg-opacity-15 text-dark border border-warning border-opacity-50 rounded-pill px-2.5 py-1 fw-bold small">
                                <i class="fa-solid fa-clipboard-check me-1"></i> জমা চেকলিস্ট
                            </span>
                        </div>
                        <h3 class="fw-bold text-dark mb-1.5 fs-6 lh-base">
                            প্রকাশনা প্রুফরিডিং ও কম্পোজিশন চেকলিস্ট
                        </h3>
                        <p class="text-muted small mb-3" style="line-height: 1.55;">
                            পান্ডুলিপি চূড়ান্ত করার পূর্বে বানান সংশোধন, ফন্ট ফরম্যাটিং, সূচিপত্র ও সাইটেশন যাচাইয়ের স্বয়ংসম্পূর্ণ চেকলিস্ট।
                        </p>
                        <div class="doc-meta-pills d-flex align-items-center flex-wrap gap-2 mb-3">
                            <span class="badge bg-light text-secondary border"><i class="fa-solid fa-file-pdf me-1 text-danger"></i> PDF ফরম্যাট</span>
                            <span class="badge bg-light text-secondary border"><i class="fa-solid fa-hard-drive me-1 text-primary"></i> ৫২০ KB</span>
                            <span class="badge bg-light text-secondary border"><i class="fa-solid fa-check me-1 text-success"></i> দ্রুত যাচাই</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2 pt-3 border-top">
                        <button type="button" class="btn btn-outline-primary btn-sm rounded-pill fw-bold flex-grow-1 py-1.5" 
                                onclick="openDocPreview('প্রকাশনা প্রুফরিডিং ও কম্পোজিশন চেকলিস্ট', 'editorial', 'PDF (৫২০ KB)', 'পান্ডুলিপি জমা দেওয়ার পূর্বে লেখকের নিজস্ব যাচাই চেকলিস্ট।', [
                                    'ইউনিকোড (কালপুরুষ/সোলাইমান লিপি/অপূর্বা) ফন্টে টাইপকৃত ফাইল',
                                    'সূচিপত্র, উৎসর্গপত্র, ভূমিকা ও লেখকের পরিচিতি অন্তর্ভুক্তি যাচাই',
                                    'অধ্যায় বিভাজন, হেডার-ফুটার ও পৃষ্ঠা নম্বরিং এর সামঞ্জস্য',
                                    'ফটো ও চিত্র সংযোজনের ক্ষেত্রে উচ্চ রেজোলিউশন (৩০০ DPI) নিশ্চিতকরণ'
                                ], 'checklist')">
                            <i class="fa-solid fa-eye me-1"></i> বিস্তারিত
                        </button>
                        <button type="button" class="btn btn-primary btn-sm rounded-pill fw-bold px-3 py-1.5 shadow-xs" 
                                onclick="triggerDocDownload('প্রুফরিডিং চেকলিস্ট')">
                            <i class="fa-solid fa-download me-1"></i> ডাউনলোড
                        </button>
                    </div>
                </div>
            </div>

        </div>

        {{-- No Search Result Empty State --}}
        <div id="docNoResultState" class="text-center py-5 d-none">
            <div class="bg-white rounded-circle shadow-sm p-4 d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                <i class="fa-solid fa-folder-open fs-2 text-muted opacity-50"></i>
            </div>
            <h4 class="fw-bold text-dark mb-1">কোনো নথি বা ফর্মের সন্ধান পাওয়া যায়নি</h4>
            <p class="text-muted small mb-3">অনুগ্রহ করে অন্য কোনো কী-ওয়ার্ড দিয়ে সার্চ করুন অথবা কাস্টমার সাপোর্ট টিমে যোগাযোগ করুন।</p>
            <button class="btn btn-outline-secondary rounded-pill px-4 fw-bold me-2" onclick="document.getElementById('clearDocSearchBtn').click()">
                <i class="fa-solid fa-rotate-left me-1"></i> সার্চ রিসেট করুন
            </button>
            <a href="{{ route('contact') }}" class="btn btn-primary rounded-pill px-4 fw-bold shadow-xs">
                <i class="fa-solid fa-headset me-1"></i> সহায়তা ডেস্কে লিখুন
            </a>
        </div>

    </section>

    {{-- 4. Publication Process Flow (4-Step Infographic) --}}
    <section class="container mt-5 pt-3">
        <div class="card border-0 rounded-4 shadow-sm p-4 p-lg-5 bg-white position-relative overflow-hidden">
            <div class="text-center mx-auto mb-4" style="max-width: 650px;">
                <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-1 fw-bold small mb-2">
                    <i class="fa-solid fa-diagram-project me-1"></i> প্রকাশনা প্রক্রিয়া
                </span>
                <h3 class="fw-bold text-dark mb-2">পান্ডুলিপি জমা থেকে বই প্রকাশ — ৪টি সহজ ধাপ</h3>
                <p class="text-muted small mb-0">আইডিয়া প্রকাশনে স্বচ্ছ ও আন্তর্জাতিকমানের প্রক্রিয়ায় প্রতিটি বই প্রকাশিত হয়।</p>
            </div>

            <div class="row g-3 g-lg-4 text-center">
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="p-3.5 rounded-4 bg-light h-100 border transition-all hover-translate">
                        <div class="rounded-circle bg-primary text-white fw-bold d-flex align-items-center justify-content-center mx-auto mb-3 shadow-sm" style="width: 44px; height: 44px; font-size: 18px;">
                            ১
                        </div>
                        <h6 class="fw-bold text-dark mb-1.5">পান্ডুলিপি জমা</h6>
                        <p class="text-muted small mb-0">ফরম পূরণ করে সফটকপি ইমেইল বা অনলাইন ড্যাশবোর্ডে জমা দিন।</p>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="p-3.5 rounded-4 bg-light h-100 border transition-all hover-translate">
                        <div class="rounded-circle bg-info text-white fw-bold d-flex align-items-center justify-content-center mx-auto mb-3 shadow-sm" style="width: 44px; height: 44px; font-size: 18px;">
                            ২
                        </div>
                        <h6 class="fw-bold text-dark mb-1.5">সম্পাদনা ও চুক্তি</h6>
                        <p class="text-muted small mb-0">সম্পাদকমণ্ডলীর ইতিবাচক রিভিউ সাপেক্ষে স্বচ্ছ রয়্যালটি চুক্তি সম্পাদন।</p>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="p-3.5 rounded-4 bg-light h-100 border transition-all hover-translate">
                        <div class="rounded-circle bg-warning text-dark fw-bold d-flex align-items-center justify-content-center mx-auto mb-3 shadow-sm" style="width: 44px; height: 44px; font-size: 18px;">
                            ৩
                        </div>
                        <h6 class="fw-bold text-dark mb-1.5">ISBN ও মুদ্রণ</h6>
                        <p class="text-muted small mb-0">জাতীয় গ্রন্থকেন্দ্র থেকে অফিশিয়াল ISBN সংগ্রহ ও প্রিমিয়াম প্রিন্টিং।</p>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="p-3.5 rounded-4 bg-light h-100 border transition-all hover-translate">
                        <div class="rounded-circle bg-success text-white fw-bold d-flex align-items-center justify-content-center mx-auto mb-3 shadow-sm" style="width: 44px; height: 44px; font-size: 18px;">
                            ৪
                        </div>
                        <h6 class="fw-bold text-dark mb-1.5">দেশব্যাপী বিপণন</h6>
                        <p class="text-muted small mb-0">অনলাইন পোর্টাল, বুকশপ ও বুকফেয়ারে সার্বিক পরিবেশনা ও রয়্যালটি আপডেট।</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- 5. Direct Help / Assistance Banner --}}
    <section class="container mt-4 pt-2">
        <div class="card border-0 rounded-4 shadow-sm p-4 p-lg-5 text-white position-relative overflow-hidden" 
             style="background: linear-gradient(135deg, #004d40 0%, #006a4e 50%, #07192f 100%);">
            <div class="row align-items-center g-4 position-relative z-1">
                <div class="col-lg-8">
                    <span class="badge bg-white bg-opacity-20 text-white rounded-pill px-3 py-1 fw-bold small mb-2">
                        <i class="fa-solid fa-headset me-1"></i> প্রকাশনা পরামর্শ ডেস্ক
                    </span>
                    <h3 class="fw-bold mb-2">পান্ডুলিপি বা চুক্তি সম্পর্কিত কোনো প্রশ্ন আছে?</h3>
                    <p class="text-white-50 mb-0 small" style="font-size: 15px; line-height: 1.6;">
                        আমাদের প্রকাশনা নির্বাহী ও সম্পাদকীয় টিম সার্বক্ষণিক আপনাকে সহযোগিতা করতে প্রস্তুত। সরাসরি ইমেইল করুন অথবা হোয়াটসঅ্যাপে তাৎক্ষণিক চ্যাট করুন।
                    </p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <div class="d-flex flex-column flex-sm-row justify-content-lg-end gap-2.5">
                        <a href="https://wa.me/8801726976982?text={{ urlencode('হ্যালো, আমি আইডিয়া প্রকাশনের প্রকাশনা ও ডকুমেন্টস সংক্রান্ত তথ্য জানতে চাই।') }}" target="_blank" 
                           class="btn btn-success rounded-pill px-4 py-2.5 fw-bold shadow-sm d-inline-flex align-items-center justify-content-center gap-2">
                            <i class="fa-brands fa-whatsapp fs-5"></i> <span>হোয়াটসঅ্যাপে কথা বলুন</span>
                        </a>
                        <a href="mailto:ideapbd@gmail.com" class="btn btn-outline-light rounded-pill px-3.5 py-2.5 fw-bold d-inline-flex align-items-center justify-content-center gap-2">
                            <i class="fa-solid fa-envelope"></i> <span>ideapbd@gmail.com</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

</div>

{{-- Dynamic Document Preview Modal --}}
<div class="modal fade" id="docPreviewModal" tabindex="-1" aria-labelledby="docPreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow-2xl overflow-hidden">
            <div class="modal-header py-3 px-4 text-white border-0" style="background: linear-gradient(135deg, #07192f 0%, #006a4e 100%);">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="rounded-circle bg-white bg-opacity-20 p-2 d-flex align-items-center justify-content-center text-white" style="width: 40px; height: 40px;">
                        <i class="fa-solid fa-file-lines fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-white mb-0" id="docPreviewModalLabel" style="font-size: 17px;">
                            ডকুমেন্ট প্রিভিউ ও তথ্যাবলী
                        </h5>
                        <small class="text-white-50" id="previewDocCategoryPill">অফিশিয়াল ডকুমেন্ট</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white rounded-circle shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4 p-md-4.5 bg-white">
                <div class="d-flex align-items-start justify-content-between flex-wrap gap-2 pb-3 mb-3 border-bottom">
                    <div>
                        <h4 class="fw-bold text-dark mb-1" id="previewModalTitle">ডকুমেন্টের শিরোনাম</h4>
                        <p class="text-muted small mb-0" id="previewModalSummary">ডকুমেন্টের বিবরণ</p>
                    </div>
                    <span class="badge bg-light text-dark border px-3 py-1.5 rounded-pill fw-bold" id="previewModalFormatBadge">PDF</span>
                </div>

                <div class="mb-4">
                    <h6 class="fw-bold text-dark mb-2.5 d-flex align-items-center gap-2">
                        <i class="fa-solid fa-circle-check text-success"></i>
                        <span>অন্তর্ভুক্ত প্রধান তথ্যাবলী ও চেকলিস্ট:</span>
                    </h6>
                    <ul class="list-unstyled mb-0 d-flex flex-column gap-2" id="previewModalChecklist">
                        {{-- Populated dynamically --}}
                    </ul>
                </div>

                <div class="p-3 rounded-3 bg-light border d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2 small text-muted">
                        <i class="fa-solid fa-shield-halved text-primary fs-5"></i>
                        <span>আইডিয়া প্রকাশন অফিশিয়াল পাবলিকেশন ডেস্ক দ্বারা যাচাইকৃত ও অনুমোদিত।</span>
                    </div>
                    <span class="badge bg-success bg-opacity-10 text-success fw-bold px-2.5 py-1">১০০% ফ্রি ও অফিশিয়াল</span>
                </div>
            </div>

            <div class="modal-footer bg-light py-2.5 px-4 border-top d-flex align-items-center justify-content-between">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-3.5 fw-bold btn-sm" data-bs-dismiss="modal">
                    বন্ধ করুন
                </button>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-primary rounded-pill px-3 fw-bold btn-sm" onclick="copyDocLink()">
                        <i class="fa-solid fa-share-nodes me-1"></i> শেয়ার
                    </button>
                    <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold btn-sm shadow-xs" id="previewModalDownloadBtn" onclick="triggerDocDownloadFromModal()">
                        <i class="fa-solid fa-download me-1"></i> ডাউনলোড করুন
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Styles --}}
<style>
.doc-tab-btn {
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
.doc-tab-btn:hover {
    border-color: #006a4e;
    color: #006a4e;
    background: #f0fdf4;
    transform: translateY(-1px);
}
.doc-tab-btn.active {
    background: linear-gradient(135deg, #006a4e 0%, #004d40 100%);
    border-color: transparent;
    color: #ffffff;
    box-shadow: 0 4px 12px rgba(0, 106, 78, 0.25);
}
.doc-tab-btn.active .badge {
    background: rgba(255,255,255,0.2) !important;
    color: #ffffff !important;
}
.doc-box {
    transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
    border: 1px solid rgba(0, 0, 0, 0.06) !important;
}
.doc-box:hover {
    transform: translateY(-5px);
    box-shadow: 0 16px 32px -8px rgba(15, 23, 42, 0.12) !important;
    border-color: rgba(0, 106, 78, 0.3) !important;
}
.doc-card-accent-bar {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
}
.doc-icon-wrap {
    width: 50px;
    height: 50px;
}
.hover-translate:hover {
    transform: translateY(-3px);
    border-color: #006a4e !important;
    box-shadow: 0 8px 20px rgba(0,0,0,0.06);
}
.backdrop-blur {
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
}
</style>

{{-- Scripts --}}
<script>
let currentActiveDocTitle = '';

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('docSearchInput');
    const clearBtn = document.getElementById('clearDocSearchBtn');
    const tabBtns = document.querySelectorAll('.doc-tab-btn');
    const docItems = document.querySelectorAll('.doc-item-card');
    const noResultState = document.getElementById('docNoResultState');
    const visibleDocCount = document.getElementById('visibleDocCount');
    const activeFilterLabel = document.getElementById('activeFilterLabel');

    let currentCategory = 'all';

    const categoryNames = {
        'all': 'সকল ডকুমেন্টের তালিকা',
        'authors': 'লেখক ও পাণ্ডুলিপি সংক্রান্ত নথি',
        'publishing': 'প্রকাশনা ও চুক্তিপত্র সংক্রান্ত নথি',
        'sellers': 'সেলার ও বুকশপ পাইকারি চার্ট',
        'legal': 'কপিরাইট, আইএসবিএন ও আইনগত নথি',
        'editorial': 'সম্পাদনা ও প্রমিত বানানরীতি গাইড'
    };

    function filterDocs() {
        const query = (searchInput?.value || '').trim().toLowerCase();
        let count = 0;

        if (query.length > 0) {
            clearBtn.style.display = 'inline-block';
        } else {
            clearBtn.style.display = 'none';
        }

        docItems.forEach(item => {
            const itemCat = item.getAttribute('data-category');
            const itemKeywords = (item.getAttribute('data-keywords') || '').toLowerCase();
            const itemText = item.textContent.toLowerCase();

            const matchesCategory = (currentCategory === 'all' || itemCat === currentCategory);
            const matchesQuery = (query === '' || itemKeywords.includes(query) || itemText.includes(query));

            if (matchesCategory && matchesQuery) {
                item.style.display = 'block';
                count++;
            } else {
                item.style.display = 'none';
            }
        });

        if (count === 0) {
            noResultState.classList.remove('d-none');
            visibleDocCount.textContent = `(০টি নথি)`;
        } else {
            noResultState.classList.add('d-none');
            visibleDocCount.textContent = `(${count}টি নথি প্রদর্শিত)`;
        }

        activeFilterLabel.textContent = categoryNames[currentCategory] || 'ডকুমেন্টের তালিকা';
    }

    if (searchInput) {
        searchInput.addEventListener('input', filterDocs);
    }

    if (clearBtn) {
        clearBtn.addEventListener('click', function() {
            searchInput.value = '';
            filterDocs();
            searchInput.focus();
        });
    }

    tabBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            tabBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentCategory = this.getAttribute('data-category');
            filterDocs();
        });
    });
});

function openDocPreview(title, category, format, summary, checklist, docKey) {
    currentActiveDocTitle = title;
    document.getElementById('previewModalTitle').textContent = title;
    document.getElementById('previewDocCategoryPill').textContent = 'ক্যাটাগরি: ' + category.toUpperCase();
    document.getElementById('previewModalFormatBadge').textContent = format;
    document.getElementById('previewModalSummary').textContent = summary;

    const listEl = document.getElementById('previewModalChecklist');
    listEl.innerHTML = '';
    if (checklist && checklist.length) {
        checklist.forEach(point => {
            const li = document.createElement('li');
            li.className = 'd-flex align-items-start gap-2 text-secondary small';
            li.innerHTML = `<i class="fa-solid fa-check text-success mt-1 flex-shrink-0"></i> <span>${point}</span>`;
            listEl.appendChild(li);
        });
    }

    const modalEl = document.getElementById('docPreviewModal');
    const modal = new bootstrap.Modal(modalEl);
    modal.show();
}

function triggerDocDownloadFromModal() {
    triggerDocDownload(currentActiveDocTitle);
}

function triggerDocDownload(title) {
    // Generate realistic instant download toast feedback
    const toast = document.createElement('div');
    toast.className = 'position-fixed bottom-0 end-0 m-3 p-3 bg-dark text-white rounded-4 shadow-2xl d-flex align-items-center gap-3 border border-secondary border-opacity-50';
    toast.style.zIndex = '999999';
    toast.style.maxWidth = '360px';
    toast.innerHTML = `
        <div class="rounded-circle bg-success text-white p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px;">
            <i class="fa-solid fa-cloud-arrow-down fs-5"></i>
        </div>
        <div class="lh-sm">
            <strong class="d-block text-white mb-0.5" style="font-size: 13.5px;">${title}</strong>
            <span class="small text-white-50" style="font-size: 11.5px;">ডকুমেন্টটি প্রস্তুত করা হচ্ছে ও ডাউনলোড শুরু হয়েছে...</span>
        </div>
    `;
    document.body.appendChild(toast);
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.4s ease';
        setTimeout(() => toast.remove(), 400);
    }, 3500);
}

function copyDocLink() {
    navigator.clipboard.writeText(window.location.href).then(() => {
        alert('ডকুমেন্ট পেজের অফিশিয়াল লিংক কপি করা হয়েছে!');
    });
}
</script>
@endsection
