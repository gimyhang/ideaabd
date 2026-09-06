@extends('layouts.app')
@section('title', 'নতুন অ্যাকাউন্ট তৈরি করুন — আইডিয়া প্রকাশন')

@section('content')
<div class="container py-4 py-md-5">
    <div class="row justify-content-center">
        <div class="col-xl-10 col-lg-11">
            
            {{-- Classic, Clean Header --}}
            <div class="text-center mb-4 pb-1">
                <h1 class="fw-bold text-dark mb-1" style="font-size: clamp(22px, 3.2vw, 30px); letter-spacing: -0.3px;">
                    নতুন অ্যাকাউন্ট তৈরি করুন
                </h1>
                <p class="text-muted mb-0 small" style="font-size: 14.5px;">
                    আপনার ভূমিকা নির্বাচন করে এগিয়ে যান
                </p>
            </div>

            {{-- 4 Classic Role Cards --}}
            <div class="row g-3 g-md-3 align-items-stretch">
                
                {{-- 1. Author (লেখক) --}}
                <div class="col-6 col-lg-3 d-flex">
                    <div class="card w-100 border rounded-4 shadow-none text-decoration-none d-flex flex-column justify-content-between p-3 p-sm-3.5 bg-white classic-role-card transition-all position-relative">
                        <div>
                            <div class="d-flex align-items-center justify-content-between mb-2.5">
                                <div class="rounded-3 d-flex align-items-center justify-content-center classic-icon-box bg-success bg-opacity-10 text-success" style="width: 44px; height: 44px;">
                                    <i class="fa-solid fa-feather-pointed fs-5"></i>
                                </div>
                                <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2 py-0.5" style="font-size: 10px;">২৪ ঘণ্টা</span>
                            </div>
                            <h3 class="fw-bold text-dark mb-1 fs-6">লেখক</h3>
                            <p class="text-muted small mb-3" style="font-size: 12.5px; line-height: 1.45;">
                                সাহিত্য ও লেখা প্রকাশ এবং সম্মানী অর্জন।
                            </p>
                        </div>
                        <a href="{{ route('register.form', 'author') }}" class="btn btn-success btn-sm rounded-pill fw-bold w-100 py-2 d-flex align-items-center justify-content-center gap-1.5 shadow-2xs">
                            <span>সাইন আপ</span>
                            <i class="fa-solid fa-arrow-right small"></i>
                        </a>
                    </div>
                </div>

                {{-- 2. Buyer (পাঠক) --}}
                <div class="col-6 col-lg-3 d-flex">
                    <div class="card w-100 border rounded-4 shadow-none text-decoration-none d-flex flex-column justify-content-between p-3 p-sm-3.5 bg-white classic-role-card transition-all position-relative">
                        <div>
                            <div class="d-flex align-items-center justify-content-between mb-2.5">
                                <div class="rounded-3 d-flex align-items-center justify-content-center classic-icon-box bg-warning bg-opacity-15 text-dark" style="width: 44px; height: 44px;">
                                    <i class="fa-solid fa-bag-shopping fs-5 text-warning"></i>
                                </div>
                                <span class="badge bg-warning bg-opacity-15 text-dark rounded-pill px-2 py-0.5" style="font-size: 10px;">ইনস্ট্যান্ট</span>
                            </div>
                            <h3 class="fw-bold text-dark mb-1 fs-6">পাঠক</h3>
                            <p class="text-muted small mb-3" style="font-size: 12.5px; line-height: 1.45;">
                                বই সংগ্রহ, হোম ডেলিভারি ও বিশেষ ছাড়।
                            </p>
                        </div>
                        <a href="{{ route('register.form', 'buyer') }}" class="btn btn-warning text-dark btn-sm rounded-pill fw-bold w-100 py-2 d-flex align-items-center justify-content-center gap-1.5 shadow-2xs">
                            <span>সাইন আপ</span>
                            <i class="fa-solid fa-arrow-right small"></i>
                        </a>
                    </div>
                </div>

                {{-- 3. Publisher (প্রকাশক) --}}
                <div class="col-6 col-lg-3 d-flex">
                    <div class="card w-100 border rounded-4 shadow-none text-decoration-none d-flex flex-column justify-content-between p-3 p-sm-3.5 bg-white classic-role-card transition-all position-relative">
                        <div>
                            <div class="d-flex align-items-center justify-content-between mb-2.5">
                                <div class="rounded-3 d-flex align-items-center justify-content-center classic-icon-box bg-danger bg-opacity-10 text-danger" style="width: 44px; height: 44px;">
                                    <i class="fa-solid fa-building fs-5"></i>
                                </div>
                                <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-2 py-0.5" style="font-size: 10px;">ভেরিফায়েড</span>
                            </div>
                            <h3 class="fw-bold text-dark mb-1 fs-6">প্রকাশক</h3>
                            <p class="text-muted small mb-3" style="font-size: 12.5px; line-height: 1.45;">
                                বই লিস্টিং ও ডিজিটাল ডিস্ট্রিবিউশন।
                            </p>
                        </div>
                        <a href="{{ route('register.form', 'publisher') }}" class="btn btn-danger btn-sm rounded-pill fw-bold w-100 py-2 d-flex align-items-center justify-content-center gap-1.5 shadow-2xs">
                            <span>সাইন আপ</span>
                            <i class="fa-solid fa-arrow-right small"></i>
                        </a>
                    </div>
                </div>

                {{-- 4. Seller (সেলার) --}}
                <div class="col-6 col-lg-3 d-flex">
                    <div class="card w-100 border rounded-4 shadow-none text-decoration-none d-flex flex-column justify-content-between p-3 p-sm-3.5 bg-white classic-role-card transition-all position-relative">
                        <div>
                            <div class="d-flex align-items-center justify-content-between mb-2.5">
                                <div class="rounded-3 d-flex align-items-center justify-content-center classic-icon-box bg-primary bg-opacity-10 text-primary" style="width: 44px; height: 44px;">
                                    <i class="fa-solid fa-store fs-5"></i>
                                </div>
                                <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-2 py-0.5" style="font-size: 10px;">বুকশপ</span>
                            </div>
                            <h3 class="fw-bold text-dark mb-1 fs-6">সেলার</h3>
                            <p class="text-muted small mb-3" style="font-size: 12.5px; line-height: 1.45;">
                                পাইকারি বিক্রয় ও অনলাইন বুকশপ।
                            </p>
                        </div>
                        <a href="{{ route('register.form', 'seller') }}" class="btn btn-primary btn-sm rounded-pill fw-bold w-100 py-2 d-flex align-items-center justify-content-center gap-1.5 shadow-2xs">
                            <span>সাইন আপ</span>
                            <i class="fa-solid fa-arrow-right small"></i>
                        </a>
                    </div>
                </div>

            </div>

            {{-- Centered Premium Classic Login Bar --}}
            <div class="mt-4 mt-md-5 text-center">
                <div class="d-inline-flex align-items-center justify-content-center flex-wrap gap-3 gap-md-4 px-4 px-md-5 py-3 rounded-pill shadow-sm border" 
                     style="background: linear-gradient(135deg, #07192f 0%, #004d40 100%); border-color: rgba(255,255,255,0.15) !important;">
                    <div class="d-flex align-items-center gap-2.5 text-white">
                        <i class="fa-solid fa-circle-user text-warning fs-5"></i>
                        <span class="fw-semibold" style="font-size: 15px; letter-spacing: 0.2px;">
                            ইতিমধ্যে অ্যাকাউন্ট আছে?
                        </span>
                    </div>
                    <a href="{{ route('login') }}" class="btn btn-warning text-dark rounded-pill px-4 px-sm-5 py-2.5 fw-black d-inline-flex align-items-center justify-content-center gap-2 shadow-sm hover-scale transition-all" style="font-size: 14.5px; font-weight: 800; min-height: 44px; letter-spacing: 0.3px;">
                        <i class="fa-solid fa-right-to-bracket fs-6"></i>
                        <span>লগইন করুন</span>
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>

<style>
.classic-role-card {
    border-color: #e2e8f0 !important;
    transition: all 0.2s ease-in-out;
}
.classic-role-card:hover {
    transform: translateY(-3px);
    border-color: #006a4e !important;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08) !important;
}
.classic-icon-box {
    transition: transform 0.2s ease;
}
.classic-role-card:hover .classic-icon-box {
    transform: scale(1.08);
}
.hover-scale:hover {
    transform: scale(1.04);
}
</style>
@endsection
