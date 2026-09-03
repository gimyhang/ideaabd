@extends('layouts.app')

@section('title', 'নতুন পাসওয়ার্ড নির্ধারণ — ideaabd')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-5 col-md-7 col-sm-10">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header border-0 py-4 text-center" style="background: linear-gradient(135deg, #003366 0%, #0066cc 100%);">
                    <div class="rounded-circle bg-white bg-opacity-20 d-inline-flex align-items-center justify-content-center mb-2 shadow-xs" style="width: 55px; height: 55px;">
                        <i class="fa-solid fa-lock-open text-white fs-4"></i>
                    </div>
                    <h4 class="fw-bold text-white mb-1">নতুন পাসওয়ার্ড সেট করুন</h4>
                    <p class="text-white text-opacity-75 small mb-0">মোবাইল SMS, WhatsApp বা ইমেইলে প্রাপ্ত ৬ ডিজিটের কোড দিন</p>
                </div>
                
                <div class="card-body p-4 p-md-4.5">
                    @if(session('status') || session('success'))
                        <div class="alert alert-success rounded-3 small mb-3 p-3 border-0 bg-success bg-opacity-10 text-success fw-medium">
                            <i class="fa-solid fa-circle-check me-1"></i> {{ session('status') ?: session('success') }}
                        </div>
                    @endif

                    @if(isset($errors) && $errors->any())
                        <div class="alert alert-danger rounded-3 small mb-3 p-3 border-0 bg-danger bg-opacity-10 text-danger">
                            <div class="fw-bold mb-1"><i class="fa-solid fa-triangle-exclamation me-1"></i> সমস্যা দেখা দিয়েছে:</div>
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
                            <label class="form-label fw-semibold text-dark">মোবাইল নম্বর অথবা ইমেইল <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-user-shield text-muted"></i></span>
                                <input type="text" 
                                       name="phone" 
                                       class="form-control rounded-end-3 @error('phone') is-invalid @enderror" 
                                       value="{{ old('phone', $phone ?? '') }}" 
                                       required 
                                       placeholder="01XXXXXXXXX অথবা example@mail.com">
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
                                       value="{{ old('otp', session('otp_code')) }}" 
                                       required 
                                       maxlength="6"
                                       placeholder="যেমন: 123456" 
                                       style="letter-spacing: 4px; font-size: 1.15rem;">
                            </div>
                            <div class="form-text small text-muted d-flex justify-content-between align-items-center mt-1.5">
                                <span><i class="fa-solid fa-clock text-warning me-1"></i> কোডের মেয়াদ: <strong>৩০ মিনিট</strong></span>
                            </div>
                            @error('otp')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark">নতুন পাসওয়ার্ড <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-key text-muted"></i></span>
                                <input type="password" 
                                       id="newPasswordInput"
                                       name="password" 
                                       class="form-control @error('password') is-invalid @enderror" 
                                       required 
                                       minlength="6" 
                                       placeholder="পাসওয়ার্ড (ন্যূনতম ৬ অক্ষর)">
                                <button type="button" class="btn btn-outline-secondary rounded-end-3" onclick="toggleOtpPasswordVisibility('newPasswordInput', this)">
                                    <i class="fa-regular fa-eye"></i>
                                </button>
                            </div>
                            @error('password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold text-dark">পাসওয়ার্ড নিশ্চিত করুন <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-check-double text-muted"></i></span>
                                <input type="password" 
                                       id="confirmPasswordInput"
                                       name="password_confirmation" 
                                       class="form-control" 
                                       required 
                                       minlength="6" 
                                       placeholder="পুনরায় নতুন পাসওয়ার্ডটি লিখুন">
                                <button type="button" class="btn btn-outline-secondary rounded-end-3" onclick="toggleOtpPasswordVisibility('confirmPasswordInput', this)">
                                    <i class="fa-regular fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2.5 rounded-pill fw-bold shadow-sm mb-3">
                            <i class="fa-solid fa-arrows-rotate me-1.5"></i> নতুন পাসওয়ার্ড সংরক্ষণ করুন
                        </button>

                        <div class="d-flex justify-content-between align-items-center text-center pt-2 border-top">
                            <a href="{{ route('password.request') }}" class="text-decoration-none small text-muted hover-primary">
                                <i class="fa-solid fa-redo me-1"></i> কোড পাননি? পুনরায় পাঠান
                            </a>
                            <a href="https://wa.me/8801558712810" target="_blank" class="text-decoration-none small text-success fw-semibold">
                                <i class="fa-brands fa-whatsapp me-1"></i> WhatsApp হেল্পলাইন
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleOtpPasswordVisibility(id, btn) {
    const input = document.getElementById(id);
    const icon = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}
</script>
@endsection
