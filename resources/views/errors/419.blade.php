@extends('layouts.app')

@section('title', 'সেশন মেয়াদোত্তীর্ণ (৪১৯) — আইডিয়া প্রকাশন')

@section('content')
<div class="container py-5 my-md-5">
    <div class="row justify-content-center">
        <div class="col-lg-7 col-md-9 text-center">
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
                <div class="mb-4">
                    <span class="display-1 fw-bold text-info opacity-25 d-block font-monospace">৪১৯</span>
                    <div class="rounded-circle bg-info-subtle text-info d-inline-flex align-items-center justify-content-center shadow-xs" style="width: 80px; height: 80px; margin-top: -40px;">
                        <i class="fa-solid fa-hourglass-end fs-2"></i>
                    </div>
                </div>

                <h2 class="fw-bold text-dark mb-2">নিরাপত্তা সেশনের মেয়াদ শেষ হয়েছে</h2>
                <p class="text-muted mb-4 lead fs-6">
                    দীর্ঘক্ষণ নিষ্ক্রিয় থাকার কারণে নিরাপত্তা টোকেনের মেয়াদ শেষ হয়েছে। অনুগ্রহ করে পেজটি রিলোড করে পুনরায় চেষ্টা করুন।
                </p>

                <div class="d-flex flex-wrap align-items-center justify-content-center gap-2">
                    <a href="javascript:location.reload()" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold">
                        <i class="fa-solid fa-rotate-right me-1.5"></i> পেজ রিফ্রেশ করুন
                    </a>
                    <a href="{{ url('/') }}" class="btn btn-outline-secondary rounded-pill px-4 py-2 fw-semibold">
                        <i class="fa-solid fa-house me-1.5"></i> হোম পেজে যান
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
