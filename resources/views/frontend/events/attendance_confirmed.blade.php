@extends('layouts.app')

@section('title', 'উপস্থিতি নিশ্চিতকরণ — ' . ($campaign ? $campaign->title : 'অনুষ্ঠান'))

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-9 col-lg-7">

            <div class="card border-0 shadow-xl rounded-4 overflow-hidden text-center position-relative" style="box-shadow: 0 20px 45px rgba(16, 185, 129, 0.15), 0 8px 20px rgba(0,0,0,0.06); border: 1px solid rgba(16, 185, 129, 0.2) !important;">
                
                {{-- Top Celebratory Banner --}}
                <div class="p-4 p-md-5 position-relative overflow-hidden" style="background: linear-gradient(135deg, #065f46 0%, #047857 50%, #0f766e 100%); color: #ffffff;">
                    {{-- Background Decorative Rings --}}
                    <div style="position: absolute; top: -50px; right: -50px; width: 180px; height: 180px; border-radius: 50%; background: radial-gradient(circle, rgba(255,255,255,0.18) 0%, rgba(255,255,255,0) 70%); pointer-events: none;"></div>
                    <div style="position: absolute; bottom: -60px; left: -40px; width: 160px; height: 160px; border-radius: 50%; background: radial-gradient(circle, rgba(250,204,21,0.2) 0%, rgba(255,255,255,0) 70%); pointer-events: none;"></div>

                    {{-- Live Verified Badge --}}
                    <div class="mb-3">
                        <span class="badge px-3 py-1.5 rounded-pill fw-bold text-uppercase d-inline-flex align-items-center gap-1.5 shadow-sm" style="background: rgba(255, 255, 255, 0.22); color: #ffffff; backdrop-filter: blur(8px); border: 1px solid rgba(255, 255, 255, 0.35); font-size: 11px; letter-spacing: 0.5px;">
                            <i class="fa-solid fa-circle-check text-warning"></i> OFFICIAL ATTENDANCE VERIFIED
                        </span>
                    </div>

                    {{-- Big Animated Pulsing Check Icon --}}
                    <div class="mb-3 position-relative d-inline-block">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle shadow-lg" style="width: 88px; height: 88px; background: #ffffff; color: #059669; font-size: 42px; border: 4px solid rgba(255, 255, 255, 0.35); animation: pulseGreen 2.2s infinite ease-in-out;">
                            <i class="fa-solid fa-check"></i>
                        </div>
                    </div>

                    <h3 class="fw-bold mb-1 text-white" style="letter-spacing: -0.01em;">
                        {{ $alreadyAttended ? 'উপস্থিতি ইতিপূর্বে নিশ্চিত রয়েছে' : 'উপস্থিতি সফলভাবে নিশ্চিত হয়েছে!' }}
                    </h3>
                    <p class="mb-0 text-white-50 small" style="font-size: 13px;">
                        {{ $campaign ? $campaign->title : 'রংপুর সাহিত্য উৎসব ও ৩য় লিটিলম্যাগ মেলা ২০২৬' }}
                    </p>
                </div>

                {{-- Card Body --}}
                <div class="card-body p-4 p-md-5 bg-white">

                    {{-- Success Notice Alert --}}
                    <div class="alert border-0 rounded-3 py-3 px-3 mb-4 text-start shadow-xs" style="background-color: #ecfdf5; border-left: 4px solid #10b981 !important;">
                        <div class="d-flex align-items-start gap-2.5">
                            <i class="fa-solid fa-circle-check text-success fs-5 mt-0.5"></i>
                            <div class="small text-dark" style="line-height: 1.6;">
                                <strong class="d-block" style="color: #065f46; font-size: 13.5px;">
                                    @if($alreadyAttended)
                                        উপস্থিতি রেকর্ড সংরক্ষিত রয়েছে
                                    @else
                                        ডিজিটাল চেক-ইন সফল হয়েছে!
                                    @endif
                                </strong>
                                <span>
                                    ধন্যবাদ <strong>{{ $registration->name }}</strong>! অনুষ্ঠানের কেন্দ্রীয় ডাটাবেজে আপনার উপস্থিতি নিশ্চিত হিসেবে গণ্য করা হয়েছে।
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Participant Details Box --}}
                    <div class="rounded-4 p-3.5 p-md-4 text-start border mb-4" style="background: #f8fafc; border-color: #e2e8f0 !important;">
                        
                        <div class="d-flex align-items-center gap-3 pb-3 mb-3 border-bottom">
                            @php
                                $photoUrl = null;
                                if (!empty($registration->form_data['profile_photo'])) {
                                    $photoUrl = asset('storage/' . $registration->form_data['profile_photo']);
                                } elseif (!empty($registration->form_data['photo'])) {
                                    $photoUrl = asset('storage/' . $registration->form_data['photo']);
                                }
                            @endphp

                            @if($photoUrl)
                                <img src="{{ $photoUrl }}" alt="{{ $registration->name }}" style="width: 64px; height: 64px; border-radius: 50%; object-fit: cover; border: 3px solid #10b981; box-shadow: 0 4px 10px rgba(0,0,0,0.1);" onerror="this.style.display='none'; document.getElementById('avatarFallback').style.display='flex';">
                                <div id="avatarFallback" class="align-items-center justify-content-center bg-primary text-white rounded-circle fw-bold shadow-xs" style="width: 64px; height: 64px; font-size: 24px; display: none;">
                                    {{ mb_substr($registration->name, 0, 1) }}
                                </div>
                            @else
                                <div class="d-inline-flex align-items-center justify-content-center bg-primary text-white rounded-circle fw-bold shadow-xs" style="width: 64px; height: 64px; font-size: 24px;">
                                    {{ mb_substr($registration->name, 0, 1) }}
                                </div>
                            @endif

                            <div>
                                <h5 class="fw-bold text-dark mb-0.5">{{ $registration->name }}</h5>
                                @if(!empty($registration->designation_or_class))
                                    <div class="text-muted small fw-medium">{{ $registration->designation_or_class }}</div>
                                @endif
                                @if(!empty($registration->institution_or_org))
                                    <div class="text-muted small">{{ $registration->institution_or_org }}</div>
                                @endif
                            </div>
                        </div>

                        {{-- Metadata List --}}
                        <div class="d-flex flex-column gap-2" style="font-size: 13px;">
                            <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                                <span class="text-muted"><i class="fa-solid fa-id-badge text-primary me-1.5"></i> রেজিস্ট্রেশন নম্বর:</span>
                                <span class="badge bg-primary-subtle text-primary font-monospace px-2.5 py-1 rounded-pill fw-bold" style="font-size: 12px;">
                                    #{{ $registration->registration_number }}
                                </span>
                            </div>

                            <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                                <span class="text-muted"><i class="fa-solid fa-signal text-success me-1.5"></i> বর্তমান স্ট্যাটাস:</span>
                                <span class="badge bg-success text-white px-2.5 py-1 rounded-pill fw-bold" style="font-size: 11px;">
                                    <i class="fa-solid fa-circle-check me-1"></i> ATTENDED (উপস্থিত)
                                </span>
                            </div>

                            @if(!empty($attendanceTime))
                                <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                                    <span class="text-muted"><i class="fa-regular fa-clock text-warning me-1.5"></i> নিশ্চিতকরণের সময়:</span>
                                    <strong class="text-dark font-monospace" style="font-size: 12px;">{{ $attendanceTime }}</strong>
                                </div>
                            @endif

                            @if(!empty($registration->phone))
                                <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                                    <span class="text-muted"><i class="fa-solid fa-phone text-secondary me-1.5"></i> মোবাইল নম্বর:</span>
                                    <span class="text-dark font-monospace">{{ $registration->phone }}</span>
                                </div>
                            @endif

                            @if(!empty($registration->district) || !empty($registration->thana))
                                <div class="d-flex justify-content-between align-items-center py-1">
                                    <span class="text-muted"><i class="fa-solid fa-location-dot text-danger me-1.5"></i> জেলা / এলাকা:</span>
                                    <span class="text-dark fw-medium">{{ $registration->thana ? $registration->thana . ', ' : '' }}{{ $registration->district }}</span>
                                </div>
                            @endif
                        </div>

                        {{-- Digital Attendance Signature Box --}}
                        <div class="mt-3.5 p-3 rounded-3 border text-center position-relative overflow-hidden" style="background: linear-gradient(135deg, #f0fdf4 0%, #ffffff 100%); border-color: #86efac !important;">
                            <div class="d-flex align-items-center justify-content-between mb-2 pb-1.5 border-bottom border-success-subtle">
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-0.5 rounded-pill fw-bold" style="font-size: 10px;">
                                    <i class="fa-solid fa-signature me-1"></i> ডিজিটাল উপস্থিতি স্বাক্ষর
                                </span>
                                <span class="font-monospace text-muted small" style="font-size: 10px;">
                                    {{ $signatureToken ?? ('SIG-' . strtoupper(substr(md5($registration->registration_number), 0, 10))) }}
                                </span>
                            </div>

                            <div class="py-1">
                                <div class="fw-bold fst-italic mb-1" style="font-size: 22px; font-family: 'Brush Script MT', 'Segoe Script', cursive, serif; letter-spacing: 1px; color: #047857;">
                                    {{ $registration->name }}
                                </div>
                                <div class="small text-muted" style="font-size: 11px;">
                                    <i class="fa-solid fa-check-double text-success me-1"></i> কিউআর কোড স্ক্যানের মাধ্যমে উপস্থিতি স্বাক্ষর স্বয়ংক্রিয়ভাবে ডিজিটাল রেজিস্টারে সত্যায়িত হয়েছে
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="d-flex flex-column flex-sm-row align-items-center justify-content-center gap-2.5 pt-2">
                        <a href="{{ route('event.registration.print', $registration->registration_number) }}" class="btn btn-primary btn-lg rounded-pill px-4 fw-bold shadow-sm w-100 w-sm-auto d-inline-flex align-items-center justify-content-center gap-2" style="font-size: 14.5px;">
                            <i class="fa-solid fa-id-card"></i> আমন্ত্রণ কার্ড দেখুন / প্রিন্ট করুন
                        </a>

                        <a href="{{ url('/') }}" class="btn btn-outline-secondary rounded-pill px-4 fw-bold w-100 w-sm-auto" style="font-size: 14px;">
                            <i class="fa-solid fa-house me-1"></i> হোমপেজ
                        </a>
                    </div>

                    {{-- Security Footer Watermark --}}
                    <div class="text-muted small mt-4 pt-3 border-top" style="font-size: 11px;">
                        <i class="fa-solid fa-shield-halved text-success me-1"></i> ডিজিটাল ভেরিফিকেশন সিস্টেম — আইডিয়া প্রকাশন ও ফিরেদেখা
                    </div>

                </div>

            </div>

        </div>
    </div>
</div>

<style>
@keyframes pulseGreen {
    0% {
        box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.45);
    }
    70% {
        box-shadow: 0 0 0 16px rgba(16, 185, 129, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(16, 185, 129, 0);
    }
}
</style>
@endsection
