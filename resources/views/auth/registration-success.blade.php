@extends('layouts.app')

@section('title', 'Registration Complete')

@section('content')
<div class="container py-4 py-md-5">
    <div class="row justify-content-center">
        <div class="col-lg-7 col-md-9">

            @php
                $isBuyer = ($summary['type'] ?? '') === 'buyer';
                $isApproved = !empty($summary['is_active']);
                $typeLabel = $summary['type_label'] ?? 'ব্যবহারকারী অ্যাকাউন্ট';
                $siteLogo = \App\Support\SiteSetting::logoUrl() ?: (\App\Support\SiteSetting::loginLogoUrl() ?: asset('images/logo.png'));
            @endphp

            <div class="card bg-white shadow-sm rounded-4 overflow-hidden text-center" style="border: 1px solid #e2e8f0;">
                
                {{-- Top Visual Header --}}
                <div class="p-4 p-md-5 position-relative text-white" 
                     style="background: linear-gradient(135deg, #064e3b 0%, #047857 50%, #0f172a 100%);">
                    
                    {{-- Big Glowing Checkmark Icon --}}
                    <div class="rounded-circle bg-white text-success d-inline-flex align-items-center justify-content-center shadow-lg mb-3 border border-4 border-success border-opacity-25" 
                         style="width: 84px; height: 84px; min-width: 84px;">
                        <i class="fas fa-circle-check fa-3x"></i>
                    </div>

                    @if($isBuyer)
                        <h2 class="fw-bold mb-2 text-white fs-3 fs-md-2">আপনার রেজিস্ট্রেশন সফল হয়েছে।</h2>
                        <p class="text-white-50 mb-0 small fs-6">
                            আইডিয়া প্রকাশনে আপনাকে আন্তরিক স্বাগতম।
                        </p>
                    @else
                        <h2 class="fw-bold mb-2 text-white fs-3 fs-md-2">আপনার আবেদন সফলভাবে গৃহীত হয়েছে!</h2>
                        <p class="text-white-50 mb-0 small fs-6">
                            আইডিয়া প্রকাশন ও কমিউনিটিতে আপনাকে আন্তরিক শুভেচ্ছা ও স্বাগতম।
                        </p>
                    @endif
                </div>

                <div class="p-3.5 p-md-4">
                    
                    {{-- Status Banner --}}
                    @if($isBuyer)
                        <div class="alert alert-success d-flex align-items-start gap-3 rounded-4 text-start p-3.5 mb-4 shadow-2xs" style="background: #f0fdf4; border: 1px solid #86efac;">
                            <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center flex-shrink-0 mt-0.5" style="width: 34px; height: 34px;">
                                <i class="fas fa-check fs-5"></i>
                            </div>
                            <div>
                                <h6 class="text-success-emphasis fw-bold mb-1 fs-6">গ্রাহক লগইনের ক্ষেত্রে অটোমেটিক একাউন্ট একটিভ থাকবে।</h6>
                                <p class="small text-dark mb-0" style="font-size: 0.92rem; line-height: 1.5;">
                                    আপনার মোবাইল নম্বর ও পাসওয়ার্ড দিয়ে লগইন করুন.....।
                                </p>
                            </div>
                        </div>
                    @elseif($isApproved)
                        <div class="alert alert-success d-flex align-items-center gap-2.5 rounded-4 text-start p-3 mb-4 shadow-2xs border-success-subtle">
                            <i class="fas fa-circle-check fs-4 text-success flex-shrink-0"></i>
                            <div>
                                <strong class="d-block text-success-emphasis fw-bold">অ্যাকাউন্ট তাৎক্ষণিক সক্রিয় হয়েছে!</strong>
                                <span class="small text-secondary">আপনার অ্যাকাউন্টটি সম্পূর্ণ প্রস্তুত। আপনি এখনই লগইন করতে পারেন।</span>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-warning d-flex align-items-center gap-2.5 rounded-4 text-start p-3 mb-4 shadow-2xs border-warning-subtle" style="background: #fffbeb;">
                            <i class="fas fa-clock-rotate-left fs-4 text-warning-emphasis flex-shrink-0"></i>
                            <div>
                                <strong class="d-block text-warning-emphasis fw-bold">আবেদন পর্যালোচনায় রয়েছে (Under Review)</strong>
                                <span class="small text-secondary">আমাদের অ্যাডমিন টিম তথ্য যাচাইপূর্বক <strong>২৪ ঘণ্টার মধ্যে</strong> অ্যাকাউন্টটি অনুমোদন ও সক্রিয় করে দেবে।</span>
                            </div>
                        </div>
                    @endif

                    {{-- Submitted Application Details Box --}}
                    <div class="card rounded-4 p-3.5 mb-4 bg-light bg-opacity-50 text-start" style="border: 1px solid #e2e8f0;">
                        <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom" style="border-color: #e2e8f0 !important;">
                            <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                                <i class="fas fa-id-card text-primary"></i>
                                <span>রেজিস্ট্রেশনের সারসংক্ষেপ</span>
                            </h6>
                            <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-1.5 fw-bold" style="font-size: 11.5px; border: 1px solid rgba(13,110,253,0.2);">
                                {{ $typeLabel }}
                            </span>
                        </div>

                        <div class="row g-2.5 small">
                            <div class="col-12 col-sm-6">
                                <span class="text-muted d-block" style="font-size: 11.5px;">নাম:</span>
                                <strong class="text-dark fs-6">{{ $summary['name'] ?? auth()->user()?->name ?? '—' }}</strong>
                            </div>
                            <div class="col-12 col-sm-6">
                                <span class="text-muted d-block" style="font-size: 11.5px;">মোবাইল নম্বর:</span>
                                <strong class="text-dark font-monospace fs-6">{{ $summary['phone'] ?? auth()->user()?->phone ?? '—' }}</strong>
                            </div>
                            <div class="col-12 col-sm-6">
                                <span class="text-muted d-block" style="font-size: 11.5px;">ইমেইল এড্রেস:</span>
                                <strong class="text-dark">{{ $summary['email'] ?? auth()->user()?->email ?? '—' }}</strong>
                            </div>
                            <div class="col-12 col-sm-6">
                                <span class="text-muted d-block" style="font-size: 11.5px;">নিবন্ধনের সময়:</span>
                                <span class="text-secondary font-monospace">{{ $summary['created_at'] ?? now()->format('d M, Y - h:i A') }}</span>
                            </div>
                            @if(!empty($summary['shop_name']))
                                <div class="col-12 border-top pt-2 mt-1">
                                    <span class="text-muted d-block" style="font-size: 11px;">দোকান / প্রতিষ্ঠানের নাম:</span>
                                    <strong class="text-primary">{{ $summary['shop_name'] }}</strong>
                                </div>
                            @endif
                            @if(!empty($summary['publisher_name']))
                                <div class="col-12 border-top pt-2 mt-1">
                                    <span class="text-muted d-block" style="font-size: 11px;">প্রকাশনীর নাম:</span>
                                    <strong class="text-primary">{{ $summary['publisher_name'] }}</strong>
                                </div>
                            @endif
                            @if(!empty($summary['pen_name']))
                                <div class="col-12 border-top pt-2 mt-1">
                                    <span class="text-muted d-block" style="font-size: 11px;">কলমনাম:</span>
                                    <strong class="text-primary">{{ $summary['pen_name'] }}</strong>
                                </div>
                            @endif
                        </div>
                    </div>

                    @if(!$isBuyer)
                        {{-- Next Steps Guide (পরবর্তী করণীয়) for non-buyer --}}
                        <div class="card border-0 rounded-4 p-3.5 mb-4 text-start" style="background: #f8fafc; border: 1px dashed #cbd5e1 !important;">
                            <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                                <i class="fas fa-list-check text-success"></i>
                                <span>পরবর্তী ধাপসমূহ (Next Steps):</span>
                            </h6>
                            
                            <div class="d-flex flex-column gap-2.5">
                                <div class="d-flex align-items-start gap-2.5">
                                    <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center flex-shrink-0 fw-bold" style="width: 24px; height: 24px; font-size: 11px;">1</div>
                                    <div class="small text-secondary">
                                        <strong class="text-dark">তথ্য যাচাইকরণ:</strong> আমাদের টিম আপনার রেজিস্ট্রেশন ও তথ্য দ্রুত রিভিউ করবেন।
                                    </div>
                                </div>
                                <div class="d-flex align-items-start gap-2.5">
                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center flex-shrink-0 fw-bold" style="width: 24px; height: 24px; font-size: 11px;">2</div>
                                    <div class="small text-secondary">
                                        <strong class="text-dark">নিশ্চিতকরণ নোটিফিকেশন:</strong> অ্যাকাউন্ট অনুমোদিত হওয়ার সাথে সাথে আপনার মোবাইল ও ইমেইলে নিশ্চিতকরণ বার্তা পৌঁছে যাবে।
                                    </div>
                                </div>
                                <div class="d-flex align-items-start gap-2.5">
                                    <div class="rounded-circle bg-warning text-dark d-flex align-items-center justify-content-center flex-shrink-0 fw-bold" style="width: 24px; height: 24px; font-size: 11px;">3</div>
                                    <div class="small text-secondary">
                                        <strong class="text-dark">লগইন ও ড্যাশবোর্ড অ্যাক্সেস:</strong> অনুমোদনের পর সরাসরি আপনার নির্দিষ্ট পোর্টাল ও সেবা ব্যবহার শুরু করতে পারবেন।
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Action CTA Buttons --}}
                    <div class="d-flex flex-wrap gap-2.5 justify-content-center mt-2">
                        @if($isBuyer)
                            <a href="{{ route('login') }}" class="btn btn-primary rounded-3 px-4 py-2.5 fw-bold shadow-xs d-flex align-items-center gap-2" style="font-size: 15px;">
                                <i class="fas fa-arrow-right-to-bracket"></i>
                                <span>লগইন করুন</span>
                            </a>
                            <a href="{{ route('book.index') }}" class="btn btn-outline-secondary rounded-3 px-4 py-2.5 fw-semibold" style="border: 1px solid #cbd5e1;">
                                <i class="fas fa-book-open me-1"></i> বইয়ের সমাহার দেখুন
                            </a>
                        @elseif($isApproved)
                            <a href="{{ route('login') }}" class="btn btn-primary rounded-3 px-4 py-2.5 fw-bold shadow-xs">
                                <i class="fas fa-arrow-right-to-bracket me-1"></i> লগইন করুন
                            </a>
                            <a href="{{ route('home') }}" class="btn btn-outline-primary rounded-3 px-4 py-2.5 fw-semibold">
                                <i class="fas fa-home me-1"></i> হোমপেজে যান
                            </a>
                        @else
                            <a href="{{ route('home') }}" class="btn btn-outline-primary rounded-3 px-4 py-2.5 fw-semibold">
                                <i class="fas fa-home me-1"></i> হোমপেজে যান
                            </a>
                            <a href="{{ route('login') }}" class="btn btn-primary rounded-3 px-4 py-2.5 fw-bold shadow-xs">
                                <i class="fas fa-arrow-right-to-bracket me-1"></i> লগইন পেজ
                            </a>
                        @endif
                        <a href="https://wa.me/8801558712810" target="_blank" class="btn btn-outline-success rounded-3 px-3.5 py-2.5 fw-semibold" style="border: 1px solid #86efac;">
                            <i class="fab fa-whatsapp me-1"></i> WhatsApp সাপোর্ট
                        </a>
                    </div>

                </div>

            </div>

        </div>
    </div>
</div>
@endsection
