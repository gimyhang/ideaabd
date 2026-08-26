@extends('layouts.app')
@section('title', 'লগইন — আইডিয়া প্রকাশন')

@section('content')
<div class="container py-5" style="max-width: 560px;">
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        <div class="card-header border-0 py-4 text-center text-white position-relative" 
             style="background: linear-gradient(135deg, #0c4a6e 0%, #0369a1 50%, #0284c7 100%);">
            <div class="bg-white rounded-circle p-2 d-inline-flex shadow-sm mb-2" style="width: 62px; height: 62px;">
                <img src="{{ asset('images/logo.svg') }}" class="w-100 h-100 object-fit-contain" alt="ideaabd">
            </div>
            <h3 class="fw-bold mb-1 text-white">আপনার অ্যাকাউন্টে লগইন করুন</h3>
            <small class="text-white-50" style="font-size: 0.85rem;">আইডিয়া প্রকাশন ডিজিটাল প্ল্যাটফর্ম</small>
        </div>

        <div class="card-body p-4 p-md-4.5">
            {{-- Error Alerts --}}
            @if(isset($errors) && $errors->any())
                <div class="alert alert-danger py-2.5 px-3 rounded-3 small mb-3 border-0 bg-danger bg-opacity-10 text-danger">
                    <div class="fw-bold mb-1"><i class="fa-solid fa-triangle-exclamation me-1"></i> লগইন ব্যর্থ হয়েছে:</div>
                    <ul class="mb-0 ps-3">
                        @foreach($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger py-2.5 px-3 rounded-3 small mb-3 border-0 bg-danger bg-opacity-10 text-danger">
                    <i class="fa-solid fa-circle-exclamation me-1"></i> {{ session('error') }}
                </div>
            @endif

            @if(session('status'))
                <div class="alert alert-success py-2.5 px-3 rounded-3 small mb-3 border-0 bg-success bg-opacity-10 text-success">
                    <i class="fa-solid fa-circle-check me-1"></i> {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" id="loginForm" autocomplete="on">
                @csrf

                {{-- Invisible Honeypot Anti-Bot Security Field --}}
                <div style="display:none !important; visibility:hidden; position:absolute; left:-9999px;" aria-hidden="true">
                    <input type="text" name="website_url_hp" tabindex="-1" autocomplete="off">
                    <input type="checkbox" name="b_check_field" tabindex="-1" autocomplete="off">
                </div>

                {{-- Identity / Username / Email / Phone --}}
                <div class="mb-3">
                    <label class="form-label fw-bold text-dark mb-1" style="font-size: 15px;">
                        <i class="fa-solid fa-user text-primary me-1"></i> Email, Phone or Username <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 fs-5"><i class="fa-solid fa-user-shield text-muted"></i></span>
                        <input type="text" name="email" id="loginEmailInput" class="form-control form-control-lg rounded-end-3" 
                               value="{{ old('email') }}" placeholder="Enter email, phone or username..." 
                               required autofocus autocomplete="username" 
                               style="font-size: 15px; height: 48px;"
                               autocorrect="off" autocapitalize="none" spellcheck="false">
                    </div>
                </div>

                {{-- Password with Show/Hide Toggle Eye --}}
                <div class="mb-3">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <label class="form-label fw-bold text-dark mb-0" style="font-size: 15px;">
                            <i class="fa-solid fa-lock text-primary me-1"></i> Password <span class="text-danger">*</span>
                        </label>
                        <a href="{{ route('password.request') }}" class="text-decoration-none text-primary fw-semibold" style="font-size: 13px;">
                            <i class="fa-solid fa-key me-0.5"></i> Forgot Password?
                        </a>
                    </div>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 fs-5"><i class="fa-solid fa-key text-muted"></i></span>
                        <input type="password" name="password" id="loginPasswordInput" class="form-control form-control-lg" 
                               placeholder="Enter your password..." required autocomplete="current-password"
                               style="font-size: 15px; height: 48px;">
                        <button type="button" class="btn btn-outline-secondary rounded-end-3" id="toggleLoginPasswordBtn" 
                                onclick="togglePasswordVisibility('loginPasswordInput', this)" title="পাসওয়ার্ড দেখুন বা লুকান">
                            <i class="fa-regular fa-eye fs-5"></i>
                        </button>
                    </div>
                </div>

                {{-- Human Bot Math Security Challenge --}}
                @php
                    $b1 = $botNum1 ?? random_int(3, 8);
                    $b2 = $botNum2 ?? random_int(1, 6);
                @endphp
                <div class="rounded-4 border mb-4 shadow-2xs p-3 p-sm-3.5" id="botChallengeBox" 
                     style="background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%); border-color: #cbd5e1 !important;">
                    <div class="d-flex align-items-center justify-content-between gap-2 gap-sm-3 flex-wrap flex-sm-nowrap">
                        {{-- Left Column: Row 1 = Equation, Row 2 = যোগফল লিখুন --}}
                        <div class="d-flex flex-column justify-content-center ps-1 ps-sm-2">
                            <div class="text-primary font-monospace fw-bold text-nowrap" id="botEquationText" 
                                 style="font-size: clamp(18px, 4.5vw, 22px); letter-spacing: 0.8px; line-height: 1.2;">
                                {{ $b1 }} + {{ $b2 }} = ?
                            </div>
                            <div class="text-secondary fw-semibold text-nowrap mt-0.5" style="font-size: clamp(11.5px, 3vw, 13px); color: #475569 !important;">
                                যোগফল লিখুন
                            </div>
                        </div>

                        {{-- Right Column: Merged Answer Input & Refresh Button --}}
                        <div class="d-flex align-items-center gap-1.5 gap-sm-2 ms-auto pe-1">
                            <input type="number" name="bot_answer" id="botAnswerInput" 
                                   class="form-control form-control-lg text-center fw-bold font-monospace bg-white border shadow-2xs px-1 px-sm-2" 
                                   style="width: 110px; min-width: 90px; max-width: 130px; font-size: clamp(16px, 4vw, 19px); height: 46px; border-radius: 10px;" 
                                   placeholder="যোগফল" required min="0" max="40" autocomplete="off">
                            <button type="button" class="btn btn-outline-primary rounded-circle d-flex align-items-center justify-content-center shadow-2xs flex-shrink-0" 
                                    id="refreshBotBtn" onclick="refreshBotChallenge(this)" title="নতুন সংখ্যা পেতে ক্লিক করুন" 
                                    style="width: 40px; height: 40px;">
                                <i class="fa-solid fa-rotate" id="refreshBotIcon"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input type="checkbox" name="remember" id="remember" class="form-check-input" value="1" @checked(old('remember'))>
                        <label for="remember" class="form-check-label small text-muted cursor-pointer" style="font-size: 13px;">Remember Me</label>
                    </div>
                    <span class="text-muted" style="font-size: 12px;">
                        <i class="fa-solid fa-shield-check text-success me-0.5"></i> 256-bit SSL Secure
                    </span>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2.5 fw-bold rounded-pill shadow-sm fs-5" id="loginSubmitBtn">
                    <i class="fa-solid fa-right-to-bracket me-1.5"></i> Login / লগইন করুন
                </button>
            </form>

            {{-- Prominent Registration / Sign Up Callout --}}
            <div class="mt-4 pt-3 border-top">
                <div class="p-3 bg-light rounded-4 border shadow-2xs">
                    <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                        <span class="fw-bold text-dark d-flex align-items-center gap-1.5" style="font-size: clamp(14px, 3.8vw, 17px);">
                            <i class="fa-solid fa-user-plus text-primary"></i>
                            <span>আপনার একাউন্ট না থাকলে সাইন আপ করুন:</span>
                        </span>
                        <a href="{{ route('register.choose') }}" class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 text-decoration-none px-3 py-1.5 rounded-pill fw-bold" style="font-size: 13px;">
                            সবগুলো <i class="fa-solid fa-angle-right ms-1"></i>
                        </a>
                    </div>
                    
                    <div class="row g-2 text-center">
                        <div class="col-6 col-sm-3">
                            <a href="{{ route('register.form', 'author') }}" class="d-block p-3 rounded-4 border bg-white text-decoration-none shadow-2xs hover-lift transition-all h-100">
                                <div class="text-success mb-1.5" style="font-size: 26px;"><i class="fa-solid fa-feather-pointed"></i></div>
                                <div class="fw-bold text-dark" style="font-size: 18px;">লেখক</div>
                                <div class="text-muted fw-semibold" style="font-size: 13.5px;">লেখা প্রকাশ</div>
                            </a>
                        </div>
                        <div class="col-6 col-sm-3">
                            <a href="{{ route('register.form', 'buyer') }}" class="d-block p-3 rounded-4 border bg-white text-decoration-none shadow-2xs hover-lift transition-all h-100">
                                <div class="text-warning mb-1.5" style="font-size: 26px;"><i class="fa-solid fa-bag-shopping"></i></div>
                                <div class="fw-bold text-dark" style="font-size: 18px;">পাঠক/বায়ার</div>
                                <div class="text-muted fw-semibold" style="font-size: 13.5px;">বই ক্রয়</div>
                            </a>
                        </div>
                        <div class="col-6 col-sm-3">
                            <a href="{{ route('register.form', 'publisher') }}" class="d-block p-3 rounded-4 border bg-white text-decoration-none shadow-2xs hover-lift transition-all h-100">
                                <div class="text-danger mb-1.5" style="font-size: 26px;"><i class="fa-solid fa-building"></i></div>
                                <div class="fw-bold text-dark" style="font-size: 18px;">প্রকাশক</div>
                                <div class="text-muted fw-semibold" style="font-size: 13.5px;">প্রকাশনী</div>
                            </a>
                        </div>
                        <div class="col-6 col-sm-3">
                            <a href="{{ route('register.form', 'seller') }}" class="d-block p-3 rounded-4 border bg-white text-decoration-none shadow-2xs hover-lift transition-all h-100">
                                <div class="text-primary mb-1.5" style="font-size: 26px;"><i class="fa-solid fa-store"></i></div>
                                <div class="fw-bold text-dark" style="font-size: 18px;">সেলার</div>
                                <div class="text-muted fw-semibold" style="font-size: 13.5px;">বই বিক্রি</div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function togglePasswordVisibility(inputId, btn) {
    const input = document.getElementById(inputId);
    const icon = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        if (icon) {
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        }
    } else {
        input.type = 'password';
        if (icon) {
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
}

async function refreshBotChallenge(btn) {
    const icon = document.getElementById('refreshBotIcon');
    const eqText = document.getElementById('botEquationText');
    const ansInput = document.getElementById('botAnswerInput');
    const challengeBox = document.getElementById('botChallengeBox');

    if (icon) icon.classList.add('fa-spin');
    if (btn) btn.disabled = true;

    try {
        const res = await fetch('{{ route("login.refresh-bot") }}', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        });
        const data = await res.json();
        if (data && data.success) {
            if (eqText) eqText.textContent = data.equation;
            if (ansInput) {
                ansInput.value = '';
                ansInput.focus();
            }
            if (challengeBox) {
                challengeBox.classList.add('animate__animated', 'animate__pulse');
                setTimeout(() => challengeBox.classList.remove('animate__animated', 'animate__pulse'), 1000);
            }
        }
    } catch (e) {
        console.error('Failed to refresh bot challenge', e);
    } finally {
        if (icon) icon.classList.remove('fa-spin');
        if (btn) btn.disabled = false;
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('loginForm');
    const btn = document.getElementById('loginSubmitBtn');
    if (form && btn) {
        form.addEventListener('submit', function() {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1.5" role="status"></span> Checking & Logging in...';
        });
    }
});
</script>
@endsection
