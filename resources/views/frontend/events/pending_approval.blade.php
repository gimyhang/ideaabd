@extends('layouts.app')

@section('title', 'আবেদন জমা হয়েছে — ' . $campaign->title)

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">

            <div class="card border-0 shadow-lg rounded-4 overflow-hidden text-center">
                
                {{-- Header Bar --}}
                <div class="p-4" style="background: linear-gradient(135deg, #7f1d1d 0%, #991b1b 100%); color: #fff;">
                    <div class="mb-2">
                        <span class="badge bg-warning text-dark font-monospace px-3 py-1.5 rounded-pill fw-bold" style="font-size: 11px;">
                            <i class="fa-solid fa-hourglass-half me-1"></i> PENDING ADMIN APPROVAL
                        </span>
                    </div>
                    <h4 class="fw-bold mb-1 text-white">{{ $campaign->title }}</h4>
                    <p class="mb-0 text-white-50 small">লেখকদের অংশগ্রহণ ও ডেলিগেট কার্ড অনুমোদন</p>
                </div>

                <div class="card-body p-4 p-md-5">

                    <div class="mb-4">
                        <div class="d-inline-flex align-items-center justify-content-center bg-warning-subtle text-warning-emphasis rounded-circle" style="width: 76px; height: 76px; font-size: 32px;">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                        </div>
                    </div>

                    <h5 class="fw-bold text-dark mb-2">আপনার তথ্য সফলভাবে জমা হয়েছে!</h5>
                    
                    <p class="text-dark small mb-3 fw-semibold" style="line-height: 1.7; font-size: 14px;">
                        <i class="fa-solid fa-clock-rotate-left text-danger me-1"></i> ২৪ ঘণ্টা পর মোবাইল নম্বর দিয়ে লগিন করে কার্ড নম্বর ও আমন্ত্রণ কার্ড ডাউনলোড করুন।
                    </p>

                    <p class="text-muted small mb-4" style="font-size: 12px; line-height: 1.55;">
                        বিশেষ বিজ্ঞপ্তি: ইভেন্ট আয়োজক “ফিরেদেখা” আইডিয়া প্রকাশন ইউআরএল ব্যবহারের অনুমতি দিয়েছেন সংগঠনকে সহযোগিতা করা ও লেখকগণের সুবিধার্থে
                    </p>

                    {{-- Applicant Info Box --}}
                    <div class="bg-light p-3.5 rounded-3 text-start border mb-4" style="font-size: 13px;">
                        <div class="d-flex justify-content-between py-1 border-bottom">
                            <span class="text-muted">রেজিস্ট্রেশন নম্বর:</span>
                            <strong class="font-monospace text-primary">#{{ $registration->registration_number }}</strong>
                        </div>
                        <div class="d-flex justify-content-between py-1 border-bottom">
                            <span class="text-muted">লেখক নাম:</span>
                            <strong class="text-dark">{{ $registration->name }}</strong>
                        </div>
                        <div class="d-flex justify-content-between py-1 border-bottom">
                            <span class="text-muted">মোবাইল নম্বর:</span>
                            <strong class="font-monospace text-dark">{{ $registration->phone }}</strong>
                        </div>
                        <div class="d-flex justify-content-between py-1 border-bottom">
                            <span class="text-muted">লেখক ক্যাটাগরি:</span>
                            <span class="badge bg-secondary-subtle text-secondary">{{ $registration->designation_or_class ?: ($registration->form_data['author_category'] ?? 'লেখক') }}</span>
                        </div>
                        <div class="d-flex justify-content-between py-1">
                            <span class="text-muted">আবেদনের সময়:</span>
                            <span class="text-dark">{{ $registration->created_at->format('d M, Y - h:i A') }}</span>
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="d-flex flex-wrap justify-content-center gap-2 mb-3">
                        <button type="button" onclick="window.location.reload()" class="btn btn-outline-danger rounded-pill px-4 py-2 fw-semibold" style="font-size: 13.5px;">
                            <i class="fa-solid fa-rotate-right me-1"></i> স্ট্যাটাস রিফ্রেশ করুন
                        </button>
                        <a href="{{ url('/') }}" class="btn btn-light border rounded-pill px-4 py-2" style="font-size: 13.5px;">
                            মূল ওয়েবসাইটে ফিরুন
                        </a>
                    </div>

                    <div class="text-muted small" style="font-size: 11.5px;">
                        <i class="fa-solid fa-headset me-1 text-danger"></i> যেকোনো প্রয়োজনে যোগাযোগ: <a href="tel:{{ $campaign->contact_phone ?: '01558712810' }}" class="text-decoration-none fw-bold text-dark">{{ $campaign->contact_phone ?: '01558712810' }}</a>
                    </div>

                </div>

            </div>

        </div>
    </div>
</div>
@endsection
