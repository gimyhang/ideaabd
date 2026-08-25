@extends('layouts.app')
@section('title', 'সাইনআপ সফল হয়েছে — আইডিয়া প্রকাশন')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8 text-center">
            <div class="card border-0 shadow-sm p-4 p-md-5 rounded-4 bg-white">
                <div class="mb-4">
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow-sm" style="width:84px;height:84px;background:#e8f5e9;border:3px solid #81c784">
                        <i class="fas fa-check-circle fa-3x text-success"></i>
                    </div>
                    <h3 class="fw-bold text-success mb-2">আপনার সাইনআপ সফল হয়েছে!</h3>
                    <div class="badge bg-warning bg-opacity-15 text-dark border border-warning px-3 py-2 rounded-pill fs-6 fw-bold mb-2">
                        <i class="fa-solid fa-clock-rotate-left text-warning me-1.5"></i> ২৪ ঘণ্টার মধ্যে অ্যাকাউন্ট অ্যাক্টিভেশন
                    </div>
                    <p class="text-secondary fs-6 mb-0 mt-2">
                        ২৪ ঘণ্টার মধ্যে আপনার অ্যাকাউন্ট অ্যাক্টিভ হলে আপনি সরাসরি ব্লগে লেখা পোস্ট করতে পারবেন।
                    </p>
                </div>

                <div class="alert alert-light text-start rounded-4 border p-3.5 mb-4 shadow-2xs">
                    <div class="d-flex align-items-center gap-2 mb-2 text-dark fw-bold">
                        <i class="fas fa-circle-info text-primary"></i>
                        <span>লেখক অ্যাকাউন্ট অ্যাক্টিভেশন প্রক্রিয়া:</span>
                    </div>
                    <ul class="mb-0 small text-secondary ps-3">
                        <li class="mb-1.5">আমাদের সম্পাদকীয় পরিষদ আপনার প্রোফাইল ও তথ্যাদি যাচাই করবেন।</li>
                        <li class="mb-1.5"><strong>২৪ ঘণ্টার মধ্যে</strong> অ্যাকাউন্টটি অ্যাক্টিভ বা অনুমোদিত হয়ে যাবে।</li>
                        <li class="mb-1.5">অ্যাক্টিভ হওয়ার সাথে সাথে আপনি আপনার ড্যাশবোর্ডে লগইন করে নতুন ব্লগ, গল্প বা প্রবন্ধ প্রকাশ করতে পারবেন।</li>
                        <li>জরুরি সহায়তায় সরাসরি হোয়াটসঅ্যাপ হেল্পলাইনে যোগাযোগ করতে পারেন: <strong class="text-success">+8801558712810</strong></li>
                    </ul>
                </div>

                <div class="d-flex flex-wrap gap-2 justify-content-center">
                    <a href="{{ route('home') }}" class="btn btn-outline-primary rounded-pill px-4 py-2 fw-semibold">
                        <i class="fas fa-home me-1"></i> হোমপেজে ফিরে যান
                    </a>
                    <a href="https://wa.me/8801558712810" target="_blank" class="btn btn-outline-success rounded-pill px-4 py-2 fw-semibold">
                        <i class="fab fa-whatsapp me-1"></i> WhatsApp সাপোর্ট
                    </a>
                    <a href="{{ route('login') }}" class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-xs">
                        <i class="fas fa-arrow-right-to-bracket me-1"></i> লগইন পেজ
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
