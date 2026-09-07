@extends('layouts.app')

@section('title', 'রিটার্ন ও রিফান্ড পলিসি (Return & Refund Policy) — আইডিয়া প্রকাশন')
@section('meta_description', 'আইডিয়া প্রকাশন (ideaabd.com) এর রিটার্ন, রিফান্ড ও ক্ষতিগ্রস্ত বই প্রতিস্থাপন নীতিমালা। সহজ ৭ দিনের রিটার্ন গ্যারান্টি ও দ্রুততম রিফান্ড ব্যবস্থা।')

@section('content')
@php
    $termsReturnDays = \App\Support\SiteSetting::termsReturnDays();
    $termsRefundTimeline = \App\Support\SiteSetting::termsRefundTimeline();
    $termsReturnConditions = \App\Support\SiteSetting::termsReturnConditions();
    $termsReturnExcluded = \App\Support\SiteSetting::termsReturnExcluded();
@endphp
<div class="refund-page-wrapper bg-light min-vh-100 pb-5">

    {{-- ══════════════════════════════════════════════════════════════════
         1. HERO HEADER SECTION
    ══════════════════════════════════════════════════════════════════ --}}
    <section class="refund-hero position-relative overflow-hidden text-white py-5" 
             style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 40%, #065f46 100%);">
        
        {{-- Background decorative icon --}}
        <div class="position-absolute top-0 end-0 opacity-10 pe-none d-none d-lg-block" style="transform: translate(15%, -15%);">
            <i class="fa-solid fa-arrows-rotate" style="font-size: 380px;"></i>
        </div>

        <div class="container position-relative z-1">
            {{-- Breadcrumb --}}
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb mb-0 small" style="--bs-breadcrumb-divider: '›';">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-white-50 text-decoration-none"><i class="fa-solid fa-house me-1"></i>হোম</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('terms') }}" class="text-white-50 text-decoration-none">ব্যবহারের শর্তাবলী</a></li>
                    <li class="breadcrumb-item text-white active" aria-current="page">রিটার্ন ও রিফান্ড পলিসি</li>
                </ol>
            </nav>

            <div class="row align-items-center g-4">
                <div class="col-lg-8">
                    <div class="d-inline-flex align-items-center gap-2 bg-white bg-opacity-15 backdrop-blur rounded-pill px-3.5 py-1 mb-3 fw-bold small text-white border border-white border-opacity-20 shadow-sm">
                        <i class="fa-solid fa-hand-holding-dollar text-warning"></i>
                        <span>গ্রাহক সন্তুষ্টি ও ১০০% নির্ভরযোগ্যতা গ্যারান্টি</span>
                    </div>
                    <h1 class="fw-black mb-3 text-white display-6 lh-sm" style="letter-spacing: -0.5px;">
                        রিটার্ন, রিফান্ড ও প্রতিস্থাপন নীতিমালা
                    </h1>
                    <p class="text-white-50 lead mb-4" style="font-size: 16px; line-height: 1.7; max-width: 680px;">
                        বইপ্রেমী পাঠকদের স্বস্তি ও আস্থাই আমাদের মূল লক্ষ্য। ডেলিভারিকৃত বইয়ে যেকোনো ধরনের প্রকাশনা ত্রুটি, পাতা ছেঁড়া বা ভুল বই পৌঁছালে রয়েছে সহজ {{ $termsReturnDays }} দিনের রিটার্ন ও দ্রুততম রিফান্ড সুবিধা।
                    </p>

                    <div class="d-flex flex-wrap align-items-center gap-2 pt-1">
                        <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-bold" style="font-size: 12px;">
                            <i class="fa-solid fa-calendar-check me-1"></i> সহজ {{ $termsReturnDays }} দিনের রিটার্ন গ্যারান্টি
                        </span>
                        <span class="badge bg-white bg-opacity-20 text-white border border-white border-opacity-20 px-3 py-2 rounded-pill font-monospace" style="font-size: 12px;">
                            <i class="fa-solid fa-bolt me-1"></i> {{ $termsRefundTimeline }}-এর মধ্যে রিফান্ড নিষ্পত্তি
                        </span>
                        <button onclick="window.print()" class="btn btn-sm btn-outline-light rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5 ms-lg-2">
                            <i class="fa-solid fa-print"></i> <span>প্রিন্ট / সেভ</span>
                        </button>
                    </div>
                </div>

                {{-- Right quick summary card --}}
                <div class="col-lg-4">
                    <div class="card border-0 rounded-4 p-3.5 shadow-lg" style="background: rgba(255,255,255,0.08); backdrop-filter: blur(16px); border: 1px solid rgba(255,255,255,0.18) !important;">
                        <h6 class="text-warning fw-bold mb-2.5 small text-uppercase tracking-wider">
                            <i class="fa-solid fa-circle-info me-1.5"></i> এক নজরে রিটার্ন নিয়ম
                        </h6>
                        <ul class="list-unstyled mb-0 d-flex flex-column gap-2 text-white small" style="font-size: 12.5px; line-height: 1.5;">
                            <li class="d-flex align-items-start gap-2">
                                <i class="fa-solid fa-check text-success mt-0.5"></i>
                                <span>ভুল বা ডিফেক্টিভ বইয়ে কোনো অতিরিক্ত ডেলিভারি চার্জ ছাড়া নতুন কপি প্রদান।</span>
                            </li>
                            <li class="d-flex align-items-start gap-2">
                                <i class="fa-solid fa-check text-success mt-0.5"></i>
                                <span>বিকাশ/নগদ বা মূল পেমেন্ট মাধ্যমে সরাসরি রিফান্ড স্থানান্তর।</span>
                            </li>
                            <li class="d-flex align-items-start gap-2">
                                <i class="fa-solid fa-check text-success mt-0.5"></i>
                                <span>ডিজিটাল ই-বুকের ক্ষেত্রে ডাউনলোড ব্যর্থ হলে টেকনিক্যাল সাপোর্ট ও রিফান্ড।</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ══════════════════════════════════════════════════════════════════
         2. MAIN BODY CONTENT
    ══════════════════════════════════════════════════════════════════ --}}
    <div class="container py-5">
        <div class="row g-4">
            
            {{-- Quick Step Process Cards --}}
            <div class="col-12">
                <div class="row g-3 text-center mb-2">
                    <div class="col-md-3 col-6">
                        <div class="p-3.5 bg-white rounded-4 border shadow-xs h-100">
                            <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-inline-flex align-items-center justify-content-center mb-2" style="width: 48px; height: 48px; font-size: 20px;">
                                <i class="fa-solid fa-camera"></i>
                            </div>
                            <h6 class="fw-bold text-dark mb-1">১. ছবি/ভিডিও তুলুন</h6>
                            <p class="small text-muted mb-0">ত্রুটিপূর্ণ বইয়ের পাতার ছবি বা ভিডিও তুলুন।</p>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="p-3.5 bg-white rounded-4 border shadow-xs h-100">
                            <div class="rounded-circle bg-success bg-opacity-10 text-success d-inline-flex align-items-center justify-content-center mb-2" style="width: 48px; height: 48px; font-size: 20px;">
                                <i class="fa-brands fa-whatsapp"></i>
                            </div>
                            <h6 class="fw-bold text-dark mb-1">২. হোয়াটসঅ্যাপে জানান</h6>
                            <p class="small text-muted mb-0">অর্ডার নম্বরসহ আমাদের ইনবক্স করুন।</p>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="p-3.5 bg-white rounded-4 border shadow-xs h-100">
                            <div class="rounded-circle bg-warning bg-opacity-10 text-warning d-inline-flex align-items-center justify-content-center mb-2" style="width: 48px; height: 48px; font-size: 20px;">
                                <i class="fa-solid fa-box-open"></i>
                            </div>
                            <h6 class="fw-bold text-dark mb-1">৩. পার্সেল প্রতিস্থাপন</h6>
                            <p class="small text-muted mb-0">কুরিয়ারের মাধ্যমে নতুন কপি পাঠানো হবে।</p>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="p-3.5 bg-white rounded-4 border shadow-xs h-100">
                            <div class="rounded-circle bg-info bg-opacity-10 text-info d-inline-flex align-items-center justify-content-center mb-2" style="width: 48px; height: 48px; font-size: 20px;">
                                <i class="fa-solid fa-money-bill-transfer"></i>
                            </div>
                            <h6 class="fw-bold text-dark mb-1">৪. রিফান্ড সম্পন্ন</h6>
                            <p class="small text-muted mb-0">স্টক না থাকলে পুরো টাকা ফেরত পাবেন।</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Policy Articles Container --}}
            <div class="col-lg-8">
                <div class="d-flex flex-column gap-4">

                    {{-- SECTION 1 --}}
                    <article class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <span class="rounded-circle bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; font-size: 20px;">
                                <i class="fa-solid fa-circle-check"></i>
                            </span>
                            <div>
                                <h4 class="h5 fw-bold text-dark mb-0">যেসব ক্ষেত্রে রিটার্ন ও প্রতিস্থাপন প্রযোজ্য</h4>
                            </div>
                        </div>
                        <div class="text-secondary small" style="line-height: 1.8; font-size: 14px;">
                            @if(!empty($termsReturnConditions))
                                <div class="ps-1" style="line-height: 1.8; white-space: pre-line;">{!! nl2br(e($termsReturnConditions)) !!}</div>
                            @else
                                <ul class="mb-0 ps-3">
                                    <li><strong>মুদ্রণ বা বাঁধাই ত্রুটি:</strong> বইয়ের পাতা উল্টো ছাপা, পাতা মিসিং, অস্পষ্ট লেখা বা বাঁধাই খুলে যাওয়া।</li>
                                    <li><strong>ভুল বই সরবরাহ:</strong> অর্ডারকৃত বইয়ের পরিবর্তে অসাবধানতাবশত অন্য কোনো বই ডেলিভারি হলে।</li>
                                    <li><strong>কুরিয়ার ড্যামেজ:</strong> পরিবহনের সময় বই ভিজে যাওয়া, মলাট ফেটে যাওয়া বা মারাত্মক ক্ষতিগ্রস্ত হলে।</li>
                                    <li><strong>কপিরাইট ও মানগত নিশ্চয়তা:</strong> কোনো বই নকল বা পাইরেটেড প্রমাণ হলে তাত্ক্ষণিক ১০০% রিফান্ড ও ক্ষতিপূরণ।</li>
                                </ul>
                            @endif
                        </div>
                    </article>

                    {{-- SECTION 2 --}}
                    <article class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <span class="rounded-circle bg-danger bg-opacity-10 text-danger d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; font-size: 20px;">
                                <i class="fa-solid fa-circle-xmark"></i>
                            </span>
                            <div>
                                <h4 class="h5 fw-bold text-dark mb-0">যেসব ক্ষেত্রে রিটার্ন প্রযোজ্য নয়</h4>
                            </div>
                        </div>
                        <div class="text-secondary small" style="line-height: 1.8; font-size: 14px;">
                            @if(!empty($termsReturnExcluded))
                                <div class="ps-1" style="line-height: 1.8; white-space: pre-line;">{!! nl2br(e($termsReturnExcluded)) !!}</div>
                            @else
                                <ul class="mb-0 ps-3">
                                    <li>পার্সেল গ্রহণের পর পাঠক নিজ দায়িত্বে বই ব্যবহার বা নষ্ট করলে (যেমন: পেন দিয়ে দাগানো, পাতা ছেঁড়া)।</li>
                                    <li>ডেলিভারি পাওয়ার {{ $termsReturnDays }} দিন অতিক্রম করার পর আবেদন জানালে।</li>
                                    <li>'মন পরিবর্তন' (Mind change) বা বইয়ের বিষয়বস্তু ব্যক্তিগত পছন্দ না হওয়ার কারণে।</li>
                                    <li>ক্লিয়ারেন্স সেল বা ফ্ল্যাশ সেলে 'অ্যাজ ইজ' হিসেবে বিশেষ ছাড়ে বিক্রি হওয়া বই।</li>
                                </ul>
                            @endif
                        </div>
                    </article>

                    {{-- SECTION 3 --}}
                    <article class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <span class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; font-size: 20px;">
                                <i class="fa-solid fa-clock-rotate-left"></i>
                            </span>
                            <div>
                                <h4 class="h5 fw-bold text-dark mb-0">রিফান্ড প্রদানের সময়সীমা ও মাধ্যম</h4>
                            </div>
                        </div>
                        <div class="text-secondary small" style="line-height: 1.8; font-size: 14px;">
                            <p>রিটার্ন অনুরোধ অনুমোদিত হওয়ার পর অথবা আউট-অব-স্টক জনিত কারণে অর্ডার বাতিল হলে:</p>
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm small align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>পেমেন্ট মাধ্যম</th>
                                            <th>রিফান্ড নিষ্পত্তির সময়সীমা</th>
                                            <th>চার্জ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><strong>বিকাশ / নগদ / রকেট / উপায়</strong></td>
                                            <td>২৪ থেকে ৪৮ ঘণ্টা</td>
                                            <td><span class="badge bg-success">ফ্রি (০%)</span></td>
                                        </tr>
                                        <tr>
                                            <td><strong>ডেবিট / ক্রেডিট কার্ড (ভিসা/মাস্টারকার্ড)</strong></td>
                                            <td>৩ থেকে ৫ কার্যদিবস (ব্যাংক প্রসেসিং)</td>
                                            <td><span class="badge bg-success">ফ্রি (০%)</span></td>
                                        </tr>
                                        <tr>
                                            <td><strong>ক্যাশ অন ডেলিভারি (COD)</strong></td>
                                            <td>পার্সেল ফেরত পাওয়ার পর মোবাইল ব্যাংকিংয়ে তাৎক্ষণিক</td>
                                            <td><span class="badge bg-success">ফ্রি (০%)</span></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </article>

                </div>
            </div>

            {{-- Sidebar Help & Contact Box --}}
            <div class="col-lg-4">
                <div class="sticky-top" style="top: 90px; z-index: 10;">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-3">
                        <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                            <i class="fa-solid fa-headset text-success"></i>
                            <span>সরাসরি রিফান্ড সাপোর্ট</span>
                        </h6>
                        <p class="small text-muted mb-3" style="line-height: 1.6;">
                            আপনার অর্ডারকৃত কোনো বইয়ে সমস্যা থাকলে দ্বিধাহীনভাবে আমাদের সাপোর্ট টিমে বার্তা দিন:
                        </p>
                        <div class="d-flex flex-column gap-2.5">
                            <a href="https://wa.me/8801558712810" target="_blank" class="btn btn-success rounded-pill fw-bold text-white d-flex align-items-center justify-content-center gap-2 py-2">
                                <i class="fa-brands fa-whatsapp fs-5"></i>
                                <span>হোয়াটসঅ্যাপে মেসেজ দিন</span>
                            </a>
                            <a href="tel:+8801558712810" class="btn btn-outline-dark rounded-pill fw-semibold d-flex align-items-center justify-content-center gap-2 py-2 small">
                                <i class="fa-solid fa-phone"></i>
                                <span>কল করুন: 01558-712810</span>
                            </a>
                            <a href="{{ route('contact') }}" class="btn btn-outline-secondary rounded-pill fw-semibold d-flex align-items-center justify-content-center gap-2 py-2 small">
                                <i class="fa-solid fa-envelope"></i>
                                <span>যোগাযোগ ফর্ম পূরণ করুন</span>
                            </a>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm rounded-4 p-3.5 bg-light">
                        <h6 class="fw-bold text-dark mb-2 small"><i class="fa-solid fa-book-bookmark text-primary me-1.5"></i> সম্পর্কিত অন্যান্য পাতা</h6>
                        <ul class="list-unstyled mb-0 small d-flex flex-column gap-1.5">
                            <li><a href="{{ route('terms') }}" class="text-decoration-none text-dark fw-semibold hover-primary">› ব্যবহারের শর্তাবলী (Terms & Conditions)</a></li>
                            <li><a href="{{ route('privacy') }}" class="text-decoration-none text-dark fw-semibold hover-primary">› গোপনীয়তা নীতিমালা (Privacy Policy)</a></li>
                            <li><a href="{{ route('faq') }}" class="text-decoration-none text-dark fw-semibold hover-primary">› সাধারণ জিজ্ঞাসা (FAQ)</a></li>
                        </ul>
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>

<style>
.refund-page-wrapper {
    font-family: 'Hind Siliguri', 'Segoe UI', system-ui, -apple-system, sans-serif;
}
.hover-primary:hover {
    color: #0284c7 !important;
}
@media print {
    .refund-hero button, .site-header, .site-footer, nav[aria-label="breadcrumb"], .btn {
        display: none !important;
    }
    .col-lg-8 {
        width: 100% !important;
    }
}
</style>
@endsection
