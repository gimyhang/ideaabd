@extends('layouts.app')
@section('title', 'রেজিস্ট্রেশন সফল হয়েছে - ideaabd')
@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8 text-center">
            <div class="card border-0 shadow-sm p-4 p-md-5 rounded-4">
                <div class="mb-4">
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow-xs" style="width:88px;height:88px;background:#e8f5e9;border:3px solid #81c784">
                        <i class="fas fa-check-circle fa-3x text-success"></i>
                    </div>
                    <h3 class="fw-bold text-success mb-2">আপনার রেজিস্ট্রেশন সফল হয়েছে!</h3>
                    <p class="text-secondary fs-6 mb-0">আপনার অ্যাকাউন্টটি সফলভাবে আইডিয়া প্রকাশন সিস্টেমে জমা হয়েছে।</p>
                </div>

                <div class="alert alert-warning text-start rounded-3 border-0 bg-warning bg-opacity-10 p-3.5 mb-4">
                    <div class="d-flex align-items-center gap-2 mb-2 text-dark fw-bold">
                        <i class="fas fa-hourglass-half text-warning"></i>
                        <span>অ্যাকাউন্ট ভেরিফিকেশন ও অনুমোদন প্রক্রিয়া:</span>
                    </div>
                    <ul class="mb-0 small text-secondary ps-3">
                        <li class="mb-1">আমাদের সম্পাদকীয় দল আপনার প্রদানকৃত তথ্যাদি পর্যালোচনা করবেন।</li>
                        <li class="mb-1">সাধারণত খুব দ্রুত (২৪ ঘণ্টার মধ্যে) পর্যালোচনা সম্পন্ন হয়।</li>
                        <li>অনুমোদন সম্পন্ন হলে আপনার ইমেইল ও মোবাইলে কনফার্মেশন পৌঁছে যাবে এবং আপনি লগইন করে লেখা প্রকাশ করতে পারবেন।</li>
                    </ul>
                </div>

                <div class="d-flex flex-wrap gap-2 justify-content-center">
                    <a href="{{ route('home') }}" class="btn btn-outline-primary rounded-pill px-4 py-2 fw-semibold">
                        <i class="fas fa-home me-1"></i> হোমপেজে যান
                    </a>
                    <a href="{{ route('login') }}" class="btn btn-success rounded-pill px-4 py-2 fw-bold">
                        <i class="fas fa-arrow-right-to-bracket me-1"></i> লগইন পেজ
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
