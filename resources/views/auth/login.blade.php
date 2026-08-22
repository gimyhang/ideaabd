@extends('layouts.app')
@section('title', 'লগইন — আইডিয়া প্রকাশন')

@section('content')
<div class="container py-5" style="max-width: 500px;">
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        <div class="card-header border-0 py-4 text-center text-white position-relative" 
             style="background: linear-gradient(135deg, #0c4a6e 0%, #0369a1 50%, #0284c7 100%);">
            <div class="bg-white rounded-circle p-2 d-inline-flex shadow-sm mb-2" style="width: 58px; height: 58px;">
                <img src="{{ asset('images/logo.svg') }}" class="w-100 h-100 object-fit-contain" alt="ideaabd">
            </div>
            <h4 class="fw-bold mb-1 text-white">আপনার অ্যাকাউন্টে লগইন করুন</h4>
            <small class="text-white-50" style="font-size: 0.8rem;">আইডিয়া প্রকাশন ডিজিটাল প্ল্যাটফর্ম</small>
        </div>

        <div class="card-body p-4">
            {{-- Error Alerts --}}
            @if($errors->any())
                <div class="alert alert-danger py-2.5 px-3 rounded-3 small mb-3">
                    <ul class="mb-0 ps-3">
                        @foreach($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger py-2.5 px-3 rounded-3 small mb-3">
                    <i class="fa-solid fa-circle-exclamation me-1"></i> {{ session('error') }}
                </div>
            @endif

            @if(session('status'))
                <div class="alert alert-success py-2.5 px-3 rounded-3 small mb-3">
                    <i class="fa-solid fa-circle-check me-1"></i> {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" id="loginForm" autocomplete="on">
                @csrf

                <div class="mb-3">
                    <label class="form-label small fw-bold text-dark mb-1">
                        <i class="fa-solid fa-user text-primary me-1"></i> ইমেইল, ফোন অথবা ইউজারনেম
                    </label>
                    <input type="text" name="email" class="form-control rounded-3" 
                           value="{{ old('email') }}" placeholder="ইমেইল / মোবাইল / ইউজারনেম লিখুন..." 
                           required autofocus autocomplete="username" 
                           autocorrect="off" autocapitalize="none" spellcheck="false" inputmode="email">
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold text-dark mb-1">
                        <i class="fa-solid fa-lock text-primary me-1"></i> পাসওয়ার্ড
                    </label>
                    <input type="password" name="password" class="form-control rounded-3" 
                           placeholder="আপনার পাসওয়ার্ড লিখুন..." required autocomplete="current-password">
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input type="checkbox" name="remember" id="remember" class="form-check-input" value="1" @checked(old('remember'))>
                        <label for="remember" class="form-check-label small text-muted">মনে রাখুন</label>
                    </div>
                    <a href="{{ route('password.request') }}" class="small text-decoration-none text-primary fw-semibold">
                        <i class="fa-solid fa-key me-1"></i> পাসওয়ার্ড ভুলে গেছেন?
                    </a>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2.5 fw-bold rounded-pill shadow-sm" id="loginSubmitBtn">
                    <i class="fa-solid fa-right-to-bracket me-1.5"></i> লগইন করুন
                </button>
            </form>

            <hr class="my-4 text-muted opacity-25">

            <div class="text-center">
                <p class="small text-muted mb-2 fw-semibold">নতুন অ্যাকাউন্ট তৈরি করুন:</p>
                <div class="d-flex gap-2 justify-content-center flex-wrap">
                    <a href="{{ route('register.form', 'buyer') }}" class="btn btn-sm btn-outline-warning rounded-pill px-3 fw-semibold">বুক বায়ার</a>
                    <a href="{{ route('register.form', 'seller') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold">সেলার</a>
                    <a href="{{ route('register.form', 'author') }}" class="btn btn-sm btn-outline-success rounded-pill px-3 fw-semibold">লেখক</a>
                    <a href="{{ route('register.form', 'publisher') }}" class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-semibold">প্রকাশক</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('loginForm');
    const btn = document.getElementById('loginSubmitBtn');
    if (form && btn) {
        form.addEventListener('submit', function() {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1.5" role="status"></span> যাচাই করা হচ্ছে...';
        });
    }
});
</script>
@endsection
