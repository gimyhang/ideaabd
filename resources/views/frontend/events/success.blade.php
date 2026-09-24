@extends('layouts.app')

@section('title', $campaign->title . ' — Registration Completed')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">

            <div class="card border-0 shadow-sm rounded-4 text-center p-4 p-md-5 bg-white" style="border: 1px solid #e2e8f0;">
                <div class="rounded-circle bg-success-subtle text-success mx-auto d-flex align-items-center justify-content-center mb-3" style="width: 72px; height: 72px;">
                    <i class="fa-solid fa-circle-check fs-1"></i>
                </div>

                <h2 class="h4 fw-bold text-dark mb-1">রেজিস্ট্রেশন সফল হয়েছে!</h2>
                <p class="text-secondary small mb-4">
                    {{ $campaign->title }}-এ আপনার অংশগ্রহণ নিশ্চিত করা হয়েছে।
                </p>

                @if($summary)
                    <div class="p-3.5 rounded-3 bg-light border text-start mb-4" style="font-size: 13.5px;">
                        <div class="d-flex justify-content-between py-1 border-bottom">
                            <span class="text-muted">রেজিস্ট্রেশন নম্বর:</span>
                            <strong class="font-monospace text-primary">#{{ $summary['registration_number'] }}</strong>
                        </div>
                        <div class="d-flex justify-content-between py-1 border-bottom">
                            <span class="text-muted">নাম:</span>
                            <strong>{{ $summary['name'] }}</strong>
                        </div>
                        <div class="d-flex justify-content-between py-1 border-bottom">
                            <span class="text-muted">মোবাইল:</span>
                            <strong>{{ $summary['phone'] }}</strong>
                        </div>
                        @if($summary['amount_paid'] > 0)
                            <div class="d-flex justify-content-between py-1 border-bottom">
                                <span class="text-muted">পরিশোধিত ফি/অনুদান:</span>
                                <strong class="text-success">৳{{ number_format($summary['amount_paid'], 2) }}</strong>
                            </div>
                        @endif
                        <div class="d-flex justify-content-between py-1">
                            <span class="text-muted">তারিখ ও সময়:</span>
                            <span>{{ $summary['created_at'] }}</span>
                        </div>
                    </div>
                @endif

                <div class="alert alert-success small py-2.5 px-3 rounded-3 mb-4 text-start d-flex align-items-start gap-2 border-0 bg-success-subtle text-success-emphasis">
                    <i class="fa-solid fa-circle-info fs-5 flex-shrink-0 mt-0.5"></i>
                    <div>
                        আপনার প্রদত্ত মোবাইল নম্বরে একটি এসএমএস কনফার্মেশন পাঠানো হয়েছে। এছাড়া আপনার অ্যাকাউন্টটি স্বয়ংক্রিয়ভাবে আমাদের সিস্টেমে কাস্টমার হিসেবে যুক্ত হয়েছে।
                    </div>
                </div>

                <div class="d-flex flex-wrap justify-content-center gap-2">
                    <a href="{{ route('home') }}" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold">
                        <i class="fa-solid fa-house me-1"></i> মূল ওয়েবসাইটে ফিরে যান
                    </a>
                    <a href="{{ route('books.index') }}" class="btn btn-outline-secondary rounded-pill px-4 py-2 fw-semibold">
                        <i class="fa-solid fa-book me-1"></i> বইসমূহ দেখুন
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
