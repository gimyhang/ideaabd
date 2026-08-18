@extends('layouts.app')

@section('title', 'নতুন পাসওয়ার্ড নির্ধারণ — ideaabd')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-5 col-md-7 col-sm-10">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header border-0 py-4 text-center" style="background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%);">
                    <div class="rounded-circle bg-white bg-opacity-20 d-inline-flex align-items-center justify-content-center mb-2 shadow-xs" style="width: 55px; height: 55px;">
                        <i class="fa-solid fa-lock-open text-white fs-4"></i>
                    </div>
                    <h4 class="fw-bold text-white mb-1">নতুন পাসওয়ার্ড সেট করুন</h4>
                    <p class="text-white text-opacity-75 small mb-0">মোবাইলে প্রাপ্ত ৬ ডিজিটের কোডটি দিন</p>
                </div>
                
                <div class="card-body p-4 p-md-4.5">
                    @if(session('success'))
                        <div class="alert alert-success rounded-3 small mb-3">
                            <i class="fa-solid fa-circle-check me-1"></i> {{ session('success') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger rounded-3 small mb-3">
                            <ul class="mb-0 ps-3">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('password.update-otp') }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark">মোবাইল নম্বর <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-phone text-muted"></i></span>
                                <input type="text" 
                                       name="phone" 
                                       class="form-control rounded-end-3 @error('phone') is-invalid @enderror" 
                                       value="{{ old('phone', $phone) }}" 
                                       required 
                                       placeholder="01XXXXXXXXX">
                            </div>
                            @error('phone')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark">
                                ৬ ডিজিটের ভেরিফিকেশন কোড (OTP) <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-shield-halved text-success"></i></span>
                                <input type="text" 
                                       name="otp" 
                                       class="form-control rounded-end-3 font-monospace fw-bold @error('otp') is-invalid @enderror" 
                                       value="{{ old('otp') }}" 
                                       required 
                                       maxlength="6"
                                       placeholder="e.g. 123456" 
                                       style="letter-spacing: 3px; font-size: 1.1rem;">
                            </div>
                            <div class="form-text small text-muted d-flex justify-content-between align-items-center">
                                <span><i class="fa-solid fa-clock text-warning me-1"></i> কোডের মেয়াদ: <strong>১ মিনিট (৬০ সেকেন্ড)</strong></span>
                                <span id="otpCountdown" class="text-danger fw-bold"></span>
                            </div>
                            @error('otp')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                let secondsLeft = 60;
                                const timerEl = document.getElementById('otpCountdown');
                                const interval = setInterval(function() {
                                    secondsLeft--;
                                    if (secondsLeft > 0) {
                                        timerEl.textContent = 'মেয়াদ শেষ হতে বাকি: ' + secondsLeft + 's';
                                    } else {
                                        timerEl.textContent = 'কোডের মেয়াদ শেষ হয়েছে!';
                                        clearInterval(interval);
                                    }
                                }, 1000);
                            });
                        </script>

                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark">নতুন পাসওয়ার্ড <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-key text-muted"></i></span>
                                <input type="password" 
                                       name="password" 
                                       class="form-control rounded-end-3 @error('password') is-invalid @enderror" 
                                       required 
                                       minlength="8" 
                                       maxlength="25" 
                                       placeholder="৮-২৫ অক্ষর ও স্পেশাল ক্যারেক্টার">
                            </div>
                            <div class="form-text small text-muted">
                                <i class="fa-solid fa-shield-halved text-success me-1"></i> পাসওয়ার্ড ৮ থেকে ২৫ অক্ষরের মধ্যে হতে হবে এবং অন্তত একটি স্পেশাল ক্যারেক্টার (যেমন: @, #, $, %, !, *, ?, &) ব্যবহার করুন।
                            </div>
                            @error('password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold text-dark">পাসওয়ার্ড নিশ্চিত করুন <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-check text-muted"></i></span>
                                <input type="password" 
                                       name="password_confirmation" 
                                       class="form-control rounded-end-3" 
                                       required 
                                       minlength="8" 
                                       maxlength="25" 
                                       placeholder="পুনরায় পাসওয়ার্ডটি লিখুন">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2.5 rounded-pill fw-bold shadow-sm mb-3">
                            <i class="fa-solid fa-arrows-rotate me-1.5"></i> পাসওয়ার্ড আপডেট করুন
                        </button>

                        <div class="d-flex justify-content-between align-items-center text-center">
                            <a href="{{ route('password.request') }}" class="text-decoration-none small text-muted hover-primary">
                                <i class="fa-solid fa-redo me-1"></i> কোড পাননি? পুনরায় পাঠান
                            </a>
                            <a href="{{ route('login') }}" class="text-decoration-none small text-muted hover-primary">
                                <i class="fa-solid fa-arrow-left me-1"></i> লগইন
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
