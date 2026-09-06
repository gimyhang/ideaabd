@extends('layouts.app')
@section('title', 'নতুন অ্যাকাউন্ট তৈরি করুন — আইডিয়া প্রকাশন')

@section('content')
<div class="container py-4 py-md-5">
    <div class="row justify-content-center">
        <div class="col-xl-11 col-lg-12">
            
            {{-- Clean, Modern Title Header (Unnecessary texts removed as requested) --}}
            <div class="text-center mb-4 mb-md-5">
                <h1 class="fw-bold text-dark mb-1" style="font-size: clamp(24px, 3.5vw, 34px); letter-spacing: -0.5px;">
                    নতুন অ্যাকাউন্ট তৈরি করুন
                </h1>
                <p class="text-muted mb-0" style="font-size: clamp(14px, 2vw, 16px);">
                    আপনার ভূমিকা নির্বাচন করে সাইন আপ করুন
                </p>
            </div>

            {{-- 4 Ultra-Modern Registration Role Cards --}}
            <div class="row g-3 g-md-4 align-items-stretch">
                
                {{-- 1. Author (লেখক) --}}
                <div class="col-12 col-sm-6 col-lg-3 d-flex">
                    <div class="card w-100 border rounded-4 shadow-sm text-decoration-none d-flex flex-column justify-content-between p-3.5 p-sm-4 bg-white reg-role-card transition-all position-relative overflow-hidden" 
                         style="border-color: #bbf7d0 !important;">
                        <div class="position-absolute top-0 end-0 bg-success text-white px-2.5 py-0.5 rounded-bottom-start-3 fw-bold shadow-2xs" style="font-size: 11px;">
                            জনপ্রিয়
                        </div>
                        <div>
                            <div class="mb-3 rounded-4 d-flex align-items-center justify-content-center shadow-xs reg-icon-box" 
                                 style="width: 64px; height: 64px; background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);">
                                <i class="fa-solid fa-feather-pointed text-success fs-3"></i>
                            </div>
                            <h3 class="fw-bold text-dark mb-1" style="font-size: 21px;">লেখক</h3>
                            <div class="mb-2">
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2.5 py-1 fw-bold" style="font-size: 11.5px;">
                                    <i class="fa-solid fa-clock me-1"></i> ২৪ ঘণ্টায় অনুমোদন
                                </span>
                            </div>
                            <p class="text-secondary mb-3.5 small" style="line-height: 1.5; color: #475569 !important;">
                                সাহিত্য, প্রবন্ধ ও গল্প প্রকাশ করুন এবং পাঠক সম্মানী ও প্রতিক্রিয়া লাভ করুন।
                            </p>
                        </div>
                        <a href="{{ route('register.form', 'author') }}" class="btn btn-success rounded-pill fw-bold w-100 py-2.5 d-flex align-items-center justify-content-center gap-2 shadow-xs reg-btn" style="min-height: 44px; font-size: 14.5px;">
                            <span>লেখক সাইন আপ</span>
                            <i class="fa-solid fa-arrow-right btn-arrow"></i>
                        </a>
                    </div>
                </div>

                {{-- 2. Buyer (পাঠক / বায়ার) --}}
                <div class="col-12 col-sm-6 col-lg-3 d-flex">
                    <div class="card w-100 border rounded-4 shadow-sm text-decoration-none d-flex flex-column justify-content-between p-3.5 p-sm-4 bg-white reg-role-card transition-all" 
                         style="border-color: #fed7aa !important;">
                        <div>
                            <div class="mb-3 rounded-4 d-flex align-items-center justify-content-center shadow-xs reg-icon-box" 
                                 style="width: 64px; height: 64px; background: linear-gradient(135deg, #ffedd5 0%, #fed7aa 100%);">
                                <i class="fa-solid fa-bag-shopping fs-3" style="color: #ea580c !important;"></i>
                            </div>
                            <h3 class="fw-bold text-dark mb-1" style="font-size: 21px;">পাঠক</h3>
                            <div class="mb-2">
                                <span class="badge bg-warning bg-opacity-15 text-dark border border-warning border-opacity-50 rounded-pill px-2.5 py-1 fw-bold" style="font-size: 11.5px;">
                                    <i class="fa-solid fa-bolt text-warning me-1"></i> ইনস্ট্যান্ট অ্যাক্টিভেশন
                                </span>
                            </div>
                            <p class="text-secondary mb-3.5 small" style="line-height: 1.5; color: #475569 !important;">
                                বই সংগ্রহ, দ্রুত ডেলিভারি, ক্যাশ অন ডেলিভারি ও আকর্ষণীয় ছাড়ে বই কিনুন।
                            </p>
                        </div>
                        <a href="{{ route('register.form', 'buyer') }}" class="btn rounded-pill fw-bold w-100 py-2.5 d-flex align-items-center justify-content-center gap-2 shadow-xs text-white reg-btn" style="min-height: 44px; font-size: 14.5px; background-color: #ea580c; border-color: #ea580c;">
                            <span>পাঠক সাইন আপ</span>
                            <i class="fa-solid fa-arrow-right btn-arrow"></i>
                        </a>
                    </div>
                </div>

                {{-- 3. Publisher (প্রকাশক) --}}
                <div class="col-12 col-sm-6 col-lg-3 d-flex">
                    <div class="card w-100 border rounded-4 shadow-sm text-decoration-none d-flex flex-column justify-content-between p-3.5 p-sm-4 bg-white reg-role-card transition-all" 
                         style="border-color: #fecdd3 !important;">
                        <div>
                            <div class="mb-3 rounded-4 d-flex align-items-center justify-content-center shadow-xs reg-icon-box" 
                                 style="width: 64px; height: 64px; background: linear-gradient(135deg, #ffe4e6 0%, #fecdd3 100%);">
                                <i class="fa-solid fa-building text-danger fs-3"></i>
                            </div>
                            <h3 class="fw-bold text-dark mb-1" style="font-size: 21px;">প্রকাশক</h3>
                            <div class="mb-2">
                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-2.5 py-1 fw-bold" style="font-size: 11.5px;">
                                    <i class="fa-solid fa-shield-halved me-1"></i> ভেরিফায়েড প্রকাশনী
                                </span>
                            </div>
                            <p class="text-secondary mb-3.5 small" style="line-height: 1.5; color: #475569 !important;">
                                আপনার প্রকাশনীর বইসমূহ ডিজিটালি প্রদর্শন, পাইকারি ও খুচরা বিক্রি করুন।
                            </p>
                        </div>
                        <a href="{{ route('register.form', 'publisher') }}" class="btn btn-danger rounded-pill fw-bold w-100 py-2.5 d-flex align-items-center justify-content-center gap-2 shadow-xs reg-btn" style="min-height: 44px; font-size: 14.5px;">
                            <span>প্রকাশক সাইন আপ</span>
                            <i class="fa-solid fa-arrow-right btn-arrow"></i>
                        </a>
                    </div>
                </div>

                {{-- 4. Seller (সেলার / বুকশপ) --}}
                <div class="col-12 col-sm-6 col-lg-3 d-flex">
                    <div class="card w-100 border rounded-4 shadow-sm text-decoration-none d-flex flex-column justify-content-between p-3.5 p-sm-4 bg-white reg-role-card transition-all" 
                         style="border-color: #bfdbfe !important;">
                        <div>
                            <div class="mb-3 rounded-4 d-flex align-items-center justify-content-center shadow-xs reg-icon-box" 
                                 style="width: 64px; height: 64px; background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);">
                                <i class="fa-solid fa-store text-primary fs-3"></i>
                            </div>
                            <h3 class="fw-bold text-dark mb-1" style="font-size: 21px;">সেলার</h3>
                            <div class="mb-2">
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-2.5 py-1 fw-bold" style="font-size: 11.5px;">
                                    <i class="fa-solid fa-shop me-1"></i> বিক্রেতা অ্যাকাউন্ট
                                </span>
                            </div>
                            <p class="text-secondary mb-3.5 small" style="line-height: 1.5; color: #475569 !important;">
                                বইয়ের দোকান বা স্টকের বই লিস্টিং করে সারা দেশে পাঠকদের কাছে বিক্রি করুন।
                            </p>
                        </div>
                        <a href="{{ route('register.form', 'seller') }}" class="btn btn-primary rounded-pill fw-bold w-100 py-2.5 d-flex align-items-center justify-content-center gap-2 shadow-xs reg-btn" style="min-height: 44px; font-size: 14.5px;">
                            <span>সেলার সাইন আপ</span>
                            <i class="fa-solid fa-arrow-right btn-arrow"></i>
                        </a>
                    </div>
                </div>

            </div>

            {{-- Bottom Already Have Account Bar --}}
            <div class="mt-4 mt-sm-5 p-3.5 p-sm-4 rounded-4 border shadow-2xs d-flex flex-column flex-sm-row align-items-center justify-content-between gap-3 text-center text-sm-start" 
                 style="background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-none d-sm-flex align-items-center justify-content-center flex-shrink-0" style="width: 46px; height: 46px;">
                        <i class="fa-solid fa-right-to-bracket fs-4 text-primary"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold text-dark mb-0.5" style="font-size: 17px;">ইতিমধ্যে আইডিয়া প্রকাশনে অ্যাকাউন্ট আছে?</h5>
                        <p class="text-muted mb-0" style="font-size: 13.5px;">সরাসরি লগইন করে আপনার ড্যাশবোর্ডে প্রবেশ করুন।</p>
                    </div>
                </div>
                <a href="{{ route('login') }}" class="btn btn-outline-primary bg-white rounded-pill px-4 py-2 fw-bold shadow-xs d-inline-flex align-items-center justify-content-center gap-2 flex-shrink-0 w-100 w-sm-auto" style="font-size: 14.5px; min-height: 44px;">
                    <i class="fa-solid fa-arrow-right-to-bracket text-primary"></i>
                    <span>লগইন করুন</span>
                </a>
            </div>

        </div>
    </div>
</div>

<style>
.reg-role-card {
    transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.25s cubic-bezier(0.4, 0, 0.2, 1), border-color 0.25s ease;
}
.reg-role-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 16px 32px -8px rgba(15, 23, 42, 0.14) !important;
}
.reg-role-card:hover .reg-icon-box {
    transform: scale(1.08) rotate(3deg);
    transition: transform 0.3s ease;
}
.reg-btn .btn-arrow {
    transition: transform 0.2s ease;
}
.reg-role-card:hover .reg-btn .btn-arrow {
    transform: translateX(4px);
}
</style>
@endsection
