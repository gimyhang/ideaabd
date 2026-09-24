@extends('layouts.app')

@section('title', $campaign->title . ' — Registration Closed')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">

            <div class="card border-0 shadow-sm rounded-4 text-center p-4 p-md-5 bg-white" style="border: 1px solid #e2e8f0;">
                <div class="rounded-circle bg-warning-subtle text-warning mx-auto d-flex align-items-center justify-content-center mb-3" style="width: 72px; height: 72px;">
                    <i class="fa-solid fa-clock-rotate-left fs-1"></i>
                </div>

                <h2 class="h4 fw-bold text-dark mb-1">{{ $campaign->title }}</h2>
                <p class="text-danger fw-semibold small mb-4">
                    {{ $message ?? 'এই রেজিস্ট্রেশন কার্যক্রমটি বর্তমানে বন্ধ রয়েছে।' }}
                </p>

                <p class="text-secondary small mb-4">
                    পরবর্তী ইভেন্ট বা নোটিশ জানতে আমাদের ওয়েবসাইটের সাথে যুক্ত থাকুন।
                </p>

                <div class="d-flex justify-content-center gap-2">
                    <a href="{{ route('home') }}" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold">
                        <i class="fa-solid fa-house me-1"></i> হোমপেজে যান
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
