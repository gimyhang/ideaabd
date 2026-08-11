@extends('layouts.app')
@section('title', 'রেজিস্ট্রেশন করুন - ideaabd')
@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <h2 class="fw-bold text-center mb-2" style="color:#0066cc">নতুন অ্যাকাউন্ট তৈরি করুন</h2>
            <p class="text-center text-muted mb-5">আপনার ধরন নির্বাচন করুন</p>

            <div class="row g-4">
                <!-- Seller -->
                <div class="col-md-6 col-lg-3">
                    <a href="{{ route('register.form', 'seller') }}" class="card border-0 shadow-sm text-decoration-none text-center p-4 h-100 d-flex flex-column align-items-center justify-content-center hover-card">
                        <div class="mb-3 rounded-circle d-flex align-items-center justify-content-center" style="width:70px;height:70px;background:linear-gradient(135deg,#E8F4F8,#D4E9F0)">
                            <i class="fas fa-store fa-2x" style="color:#0066cc"></i>
                        </div>
                        <h5 class="fw-bold mb-1">সেলার</h5>
                        <p class="text-muted small mb-0">বই বিক্রয় করতে চান?</p>
                        <span class="badge bg-warning text-dark mt-2">অনুমোদন প্রয়োজন</span>
                    </a>
                </div>

                <!-- Publisher -->
                <div class="col-md-6 col-lg-3">
                    <a href="{{ route('register.form', 'publisher') }}" class="card border-0 shadow-sm text-decoration-none text-center p-4 h-100 d-flex flex-column align-items-center justify-content-center hover-card">
                        <div class="mb-3 rounded-circle d-flex align-items-center justify-content-center" style="width:70px;height:70px;background:linear-gradient(135deg,#FFE5F5,#FFD4E5)">
                            <i class="fas fa-print fa-2x" style="color:#d63384"></i>
                        </div>
                        <h5 class="fw-bold mb-1">প্রকাশক</h5>
                        <p class="text-muted small mb-0">প্রকাশনী হিসেবে যোগ দিন</p>
                        <span class="badge bg-warning text-dark mt-2">অনুমোদন প্রয়োজন</span>
                    </a>
                </div>

                <!-- Author -->
                <div class="col-md-6 col-lg-3">
                    <a href="{{ route('register.form', 'author') }}" class="card border-0 shadow-sm text-decoration-none text-center p-4 h-100 d-flex flex-column align-items-center justify-content-center hover-card">
                        <div class="mb-3 rounded-circle d-flex align-items-center justify-content-center" style="width:70px;height:70px;background:linear-gradient(135deg,#E5FFE5,#D4FFD4)">
                            <i class="fas fa-pen-fancy fa-2x" style="color:#198754"></i>
                        </div>
                        <h5 class="fw-bold mb-1">লেখক</h5>
                        <p class="text-muted small mb-0">লেখক হিসেবে যোগ দিন</p>
                        <span class="badge bg-warning text-dark mt-2">অনুমোদন প্রয়োজন</span>
                    </a>
                </div>

                <!-- Buyer -->
                <div class="col-md-6 col-lg-3">
                    <a href="{{ route('register.form', 'buyer') }}" class="card border-0 shadow-sm text-decoration-none text-center p-4 h-100 d-flex flex-column align-items-center justify-content-center hover-card">
                        <div class="mb-3 rounded-circle d-flex align-items-center justify-content-center" style="width:70px;height:70px;background:linear-gradient(135deg,#FFF5E5,#FFE5D4)">
                            <i class="fas fa-shopping-bag fa-2x" style="color:#fd7e14"></i>
                        </div>
                        <h5 class="fw-bold mb-1">বুক বায়ার</h5>
                        <p class="text-muted small mb-0">বই কিনতে চান?</p>
                        <span class="badge bg-success mt-2">তাৎক্ষণিক অ্যাক্সেস</span>
                    </a>
                </div>
            </div>

            <p class="text-center mt-4 text-muted">
                ইতিমধ্যে অ্যাকাউন্ট আছে? <a href="{{ route('login') }}" class="text-primary fw-bold">লগইন করুন</a>
            </p>
        </div>
    </div>
</div>
<style>
.hover-card { transition: transform .2s, box-shadow .2s; }
.hover-card:hover { transform: translateY(-4px); box-shadow: 0 8px 24px rgba(0,102,204,.15) !important; }
</style>
@endsection
