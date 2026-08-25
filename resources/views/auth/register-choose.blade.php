@extends('layouts.app')
@section('title', 'রেজিস্ট্রেশন করুন — আইডিয়া প্রকাশন')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-xl-11 col-lg-12">
            
            {{-- Header Hero --}}
            <div class="text-center mb-5">
                <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-pill px-4 py-1.5 mb-3 fw-bold border border-primary border-opacity-25" style="font-size: 15px;">
                    <i class="fa-solid fa-users me-2"></i> আইডিয়া প্রকাশন পরিবারে স্বাগতম
                </div>
                <h1 class="fw-bold text-dark mb-2" style="font-size: clamp(26px, 4vw, 36px); letter-spacing: -0.5px;">
                    নতুন অ্যাকাউন্ট তৈরি করুন
                </h1>
                <p class="text-muted mx-auto" style="max-width: 650px; font-size: clamp(15px, 2.5vw, 17px); line-height: 1.6;">
                    আপনার কাঙ্ক্ষিত ভূমিকাটি নির্বাচন করে রেজিস্ট্রেশন সম্পন্ন করুন এবং আইডিয়া প্রকাশন ডিজিটাল প্ল্যাটফর্মের সকল সুবিধার অংশীদার হোন।
                </p>
            </div>

            {{-- 4 Classic Registration Choice Cards Grid (Fully Responsive on Mobile, Tablet & Desktop) --}}
            <div class="row g-3 g-sm-4">
                
                {{-- 1. Author (লেখক) --}}
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="card h-100 border-2 rounded-4 shadow-sm text-decoration-none d-flex flex-column justify-content-between p-3.5 p-sm-4 bg-white hover-classic-card transition-all position-relative overflow-hidden" 
                         style="border-color: #bbf7d0 !important;">
                        <div class="position-absolute top-0 end-0 bg-success text-white px-3 py-1 rounded-bottom-start-3 fw-bold shadow-2xs" style="font-size: 12px;">
                            জনপ্রিয় পছন্দ
                        </div>
                        <div>
                            <div class="mb-3.5 rounded-4 d-flex align-items-center justify-content-center shadow-xs" 
                                 style="width: 72px; height: 72px; background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);">
                                <i class="fa-solid fa-feather-pointed text-success" style="font-size: 32px;"></i>
                            </div>
                            <h3 class="fw-bold text-dark mb-1" style="font-size: clamp(20px, 3.5vw, 24px);">লেখক</h3>
                            <div class="mb-2.5">
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2.5 py-1 fw-bold text-wrap text-start" style="font-size: 12.5px;">
                                    <i class="fa-solid fa-clock me-1"></i> ২৪ ঘণ্টায় একাউন্ট সক্রিয়
                                </span>
                            </div>
                            <p class="text-secondary mb-4" style="font-size: clamp(14px, 2.5vw, 15px); line-height: 1.5; color: #475569 !important;">
                                সাহিত্য, প্রবন্ধ ও গল্প প্রকাশ করুন; আইডিয়াপত্র পাঠক সম্মানি ও প্রতিক্রিয়া লাভ করুন।
                            </p>
                        </div>
                        <a href="{{ route('register.form', 'author') }}" class="btn btn-success btn-lg rounded-pill fw-bold w-100 py-2.5 d-flex align-items-center justify-content-center gap-2 shadow-xs" style="font-size: clamp(15px, 2.8vw, 16px); min-height: 48px;">
                            <span>লেখক সাইন আপ</span>
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>
                </div>

                {{-- 2. Buyer (পাঠক / বায়ার) --}}
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="card h-100 border-2 rounded-4 shadow-sm text-decoration-none d-flex flex-column justify-content-between p-3.5 p-sm-4 bg-white hover-classic-card transition-all" 
                         style="border-color: #fed7aa !important;">
                        <div>
                            <div class="mb-3.5 rounded-4 d-flex align-items-center justify-content-center shadow-xs" 
                                 style="width: 72px; height: 72px; background: linear-gradient(135deg, #ffedd5 0%, #fed7aa 100%);">
                                <i class="fa-solid fa-bag-shopping" style="font-size: 32px; color: #ea580c !important;"></i>
                            </div>
                            <h3 class="fw-bold text-dark mb-1" style="font-size: clamp(20px, 3.5vw, 24px);">পাঠক / বায়ার</h3>
                            <div class="mb-2.5">
                                <span class="badge bg-warning bg-opacity-10 text-dark border border-warning border-opacity-50 rounded-pill px-2.5 py-1 fw-bold text-wrap text-start" style="font-size: 12.5px;">
                                    <i class="fa-solid fa-bolt text-warning me-1"></i> তাৎক্ষণিক অ্যাক্সেস
                                </span>
                            </div>
                            <p class="text-secondary mb-4" style="font-size: clamp(14px, 2.5vw, 15px); line-height: 1.5; color: #475569 !important;">
                                বই সংগ্রহ, দ্রুততম হোম ডেলিভারি, ক্যাশ অন ডেলিভারি ও আকর্ষণীয় ছাড়ে বই কিনুন।
                            </p>
                        </div>
                        <a href="{{ route('register.form', 'buyer') }}" class="btn btn-warning btn-lg rounded-pill fw-bold w-100 py-2.5 d-flex align-items-center justify-content-center gap-2 shadow-xs text-dark" style="font-size: clamp(15px, 2.8vw, 16px); min-height: 48px; background-color: #f97316; border-color: #f97316; color: #ffffff !important;">
                            <span>পাঠক সাইন আপ</span>
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>
                </div>

                {{-- 3. Publisher (প্রকাশক) --}}
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="card h-100 border-2 rounded-4 shadow-sm text-decoration-none d-flex flex-column justify-content-between p-3.5 p-sm-4 bg-white hover-classic-card transition-all" 
                         style="border-color: #fecdd3 !important;">
                        <div>
                            <div class="mb-3.5 rounded-4 d-flex align-items-center justify-content-center shadow-xs" 
                                 style="width: 72px; height: 72px; background: linear-gradient(135deg, #ffe4e6 0%, #fecdd3 100%);">
                                <i class="fa-solid fa-building text-danger" style="font-size: 32px;"></i>
                            </div>
                            <h3 class="fw-bold text-dark mb-1" style="font-size: clamp(20px, 3.5vw, 24px);">প্রকাশক</h3>
                            <div class="mb-2.5">
                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-2.5 py-1 fw-bold text-wrap text-start" style="font-size: 12.5px;">
                                    <i class="fa-solid fa-shield-halved me-1"></i> ভেরিফায়েড প্রকাশনী
                                </span>
                            </div>
                            <p class="text-secondary mb-4" style="font-size: clamp(14px, 2.5vw, 15px); line-height: 1.5; color: #475569 !important;">
                                আপনার প্রকাশনীর বইসমূহ ডিজিটালি প্রদর্শন, পাইকারি ও খুচরা বিক্রয় পরিচালনা করুন।
                            </p>
                        </div>
                        <a href="{{ route('register.form', 'publisher') }}" class="btn btn-danger btn-lg rounded-pill fw-bold w-100 py-2.5 d-flex align-items-center justify-content-center gap-2 shadow-xs" style="font-size: clamp(15px, 2.8vw, 16px); min-height: 48px;">
                            <span>প্রকাশক সাইন আপ</span>
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>
                </div>

                {{-- 4. Seller (সেলার / বুকশপ) --}}
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="card h-100 border-2 rounded-4 shadow-sm text-decoration-none d-flex flex-column justify-content-between p-3.5 p-sm-4 bg-white hover-classic-card transition-all" 
                         style="border-color: #bfdbfe !important;">
                        <div>
                            <div class="mb-3.5 rounded-4 d-flex align-items-center justify-content-center shadow-xs" 
                                 style="width: 72px; height: 72px; background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);">
                                <i class="fa-solid fa-store text-primary" style="font-size: 32px;"></i>
                            </div>
                            <h3 class="fw-bold text-dark mb-1" style="font-size: clamp(20px, 3.5vw, 24px);">সেলার</h3>
                            <div class="mb-2.5">
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-2.5 py-1 fw-bold text-wrap text-start" style="font-size: 12.5px;">
                                    <i class="fa-solid fa-shop me-1"></i> বিক্রেতা অ্যাকাউন্ট
                                </span>
                            </div>
                            <p class="text-secondary mb-4" style="font-size: clamp(14px, 2.5vw, 15px); line-height: 1.5; color: #475569 !important;">
                                আপনার বইয়ের দোকান বা স্টকের বই লিস্টিং করে সারা দেশে পাঠকদের কাছে বিক্রি করুন।
                            </p>
                        </div>
                        <a href="{{ route('register.form', 'seller') }}" class="btn btn-primary btn-lg rounded-pill fw-bold w-100 py-2.5 d-flex align-items-center justify-content-center gap-2 shadow-xs" style="font-size: clamp(15px, 2.8vw, 16px); min-height: 48px;">
                            <span>সেলার সাইন আপ</span>
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>
                </div>

            </div>

            {{-- Bottom Already Have Account Bar --}}
            <div class="mt-4 mt-sm-5 p-3.5 p-sm-4 rounded-4 border bg-light shadow-2xs d-flex flex-column flex-sm-row align-items-center justify-content-between gap-3 text-center text-sm-start" 
                 style="background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-none d-sm-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px;">
                        <i class="fa-solid fa-right-to-bracket fs-4"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold text-dark mb-1" style="font-size: clamp(16px, 3vw, 18px);">ইতিমধ্যে আইডিয়া প্রকাশনে অ্যাকাউন্ট আছে?</h5>
                        <p class="text-muted mb-0" style="font-size: clamp(13px, 2.5vw, 14.5px);">সরাসরি লগইন করে আপনার ড্যাশবোর্ডে প্রবেশ করুন।</p>
                    </div>
                </div>
                <a href="{{ route('login') }}" class="btn btn-primary btn-lg rounded-pill px-4.5 py-2.5 fw-bold shadow-sm d-inline-flex align-items-center justify-content-center gap-2 flex-shrink-0 w-100 w-sm-auto" style="font-size: 16px; min-height: 48px;">
                    <i class="fa-solid fa-arrow-right-to-bracket"></i>
                    <span>লগইন করুন</span>
                </a>
            </div>

        </div>
    </div>
</div>

<style>
.hover-classic-card {
    transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
}
@media (hover: hover) {
    .hover-classic-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 16px 32px -8px rgba(15, 23, 42, 0.12) !important;
    }
}
.hover-classic-card:active {
    transform: scale(0.985);
}
</style>
@endsection
