@extends('layouts.app')

@section('title', 'অনুমতি নেই (৪০৩) — আইডিয়া প্রকাশন')

@section('content')
<div class="container py-5 my-md-5">
    <div class="row justify-content-center">
        <div class="col-lg-7 col-md-9 text-center">
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
                <div class="mb-4">
                    <span class="display-1 fw-bold text-warning opacity-25 d-block font-monospace">৪০৩</span>
                    <div class="rounded-circle bg-warning-subtle text-warning d-inline-flex align-items-center justify-content-center shadow-xs" style="width: 80px; height: 80px; margin-top: -40px;">
                        <i class="fa-solid fa-lock fs-2"></i>
                    </div>
                </div>

                <h2 class="fw-bold text-dark mb-2">এই পাতায় প্রবেশের অনুমতি নেই</h2>
                <p class="text-muted mb-4 lead fs-6">
                    দুঃখিত, আপনি যে পাতাটি দেখতে চাচ্ছেন সেখানে প্রবেশের জন্য প্রয়োজনীয় অনুমতি আপনার অ্যাকাউন্টে নেই।
                </p>

                <div class="d-flex flex-wrap align-items-center justify-content-center gap-2">
                    <a href="{{ url('/') }}" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold">
                        <i class="fa-solid fa-house me-1.5"></i> হোম পেজে যান
                    </a>
                    <a href="javascript:history.back()" class="btn btn-outline-secondary rounded-pill px-4 py-2 fw-semibold">
                        <i class="fa-solid fa-arrow-left me-1.5"></i> পূর্বের পাতায় ফিরুন
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
