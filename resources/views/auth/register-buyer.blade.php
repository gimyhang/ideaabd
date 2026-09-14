@extends('layouts.app')
@section('title', 'Buyer Registration — IDEA Publication')

@php
    $siteLogo = \App\Support\SiteSetting::logoUrl() ?: (\App\Support\SiteSetting::loginLogoUrl() ?: asset('images/logo.png'));
    $siteName = \App\Support\SiteSetting::siteName() ?: config('app.name', 'Idea Prokashon');
@endphp

@section('content')
<div class="container py-4 py-md-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8 col-sm-11">
            
            <div class="card bg-white shadow-sm rounded-4 overflow-hidden" style="border: 1px solid #e2e8f0;">
                
                {{-- Header with Dynamic Site Logo on the Left --}}
                <div class="card-header py-3.5 px-4" style="background: linear-gradient(135deg, #fff9f2 0%, #ffedd5 100%); border-bottom: 1px solid #fed7aa;">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-white rounded-3 p-1.5 shadow-2xs d-flex align-items-center justify-content-center flex-shrink-0" style="border: 1px solid #fed7aa; min-width: 54px; height: 54px;">
                            @if($siteLogo)
                                <img src="{{ $siteLogo }}" alt="{{ $siteName }}" class="img-fluid" style="max-height: 44px; max-width: 70px; object-fit: contain;" onerror="this.onerror=null; this.parentElement.innerHTML='<i class=\'fa-solid fa-cart-shopping text-warning fs-3\'></i>';">
                            @else
                                <i class="fa-solid fa-cart-shopping text-warning fs-3"></i>
                            @endif
                        </div>
                        <div class="flex-grow-1">
                            <h4 class="fw-bold mb-0 text-dark" style="color: #c2410c !important; font-size: 1.25rem;">Create Buyer Account</h4>
                            <small class="text-secondary" style="font-size: 0.84rem;">Instant access, quick checkout & order tracking</small>
                        </div>
                    </div>
                </div>

                <div class="card-body p-4 p-md-4">
                    
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show rounded-3 p-3 mb-3" style="border: 1px solid #fca5a5;" role="alert">
                            <i class="fa-solid fa-circle-exclamation me-1.5"></i> {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if(isset($errors) && $errors->any())
                        <div class="alert alert-danger rounded-3 p-3 mb-3" style="border: 1px solid #fca5a5;">
                            <div class="fw-bold mb-1"><i class="fa-solid fa-circle-exclamation me-1"></i> Please fix the following errors:</div>
                            <ul class="mb-0 ps-3 small">
                                @foreach($errors->all() as $e)
                                    <li>{{ $e }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('register.submit', 'buyer') }}" id="buyerRegisterForm">
                        @csrf
                        
                        {{-- Full Name --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark small mb-1">
                                Full Name <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted" style="border: 1px solid #cbd5e1; border-right: none;">
                                    <i class="fa-solid fa-user"></i>
                                </span>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name') }}" required placeholder="Enter your full name"
                                       style="border: 1px solid #cbd5e1; font-size: 0.95rem;">
                            </div>
                            @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        {{-- Mobile Number with Worldwide Country Code --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark small mb-1 d-flex justify-content-between align-items-center">
                                <span>Mobile No <span class="text-danger">*</span></span>
                                <span id="otpStatusBadge" class="badge bg-secondary-subtle text-secondary border px-2 py-0.5" style="font-size: 11px; transition: all 0.3s ease;">
                                    <i class="fa-solid fa-shield-halved me-1"></i> SMS Verification
                                </span>
                            </label>

                            <div class="input-group">
                                {{-- Country Code Selector --}}
                                <select name="country_code" id="countryCodeSelect" class="form-select bg-light flex-grow-0" style="width: auto; min-width: 110px; max-width: 130px; border: 1px solid #cbd5e1; font-size: 0.92rem; font-weight: 600;" onchange="resetPhoneExistsAlert()">
                                    <option value="+880" selected>🇧🇩 +880</option>
                                    <option value="+91">🇮🇳 +91</option>
                                    <option value="+966">🇸🇦 +966</option>
                                    <option value="+971">🇦🇪 +971</option>
                                    <option value="+974">🇶🇦 +974</option>
                                    <option value="+965">🇰🇼 +965</option>
                                    <option value="+968">🇴🇲 +968</option>
                                    <option value="+973">🇧🇭 +973</option>
                                    <option value="+60">🇲🇾 +60</option>
                                    <option value="+65">🇸🇬 +65</option>
                                    <option value="+1">🇺🇸 +1</option>
                                    <option value="+44">🇬🇧 +44</option>
                                    <option value="+1">🇨🇦 +1</option>
                                    <option value="+61">🇦🇺 +61</option>
                                    <option value="+39">🇮🇹 +39</option>
                                    <option value="+49">🇩🇪 +49</option>
                                    <option value="+33">🇫🇷 +33</option>
                                    <option value="+81">🇯🇵 +81</option>
                                    <option value="+82">🇰🇷 +82</option>
                                    <option value="+92">🇵🇰 +92</option>
                                    <option value="+977">🇳🇵 +977</option>
                                    <option value="+94">🇱🇰 +94</option>
                                    <option value="+960">🇲🇻 +960</option>
                                    <option value="+975">🇧🇹 +975</option>
                                    <option value="+66">🇹🇭 +66</option>
                                    <option value="+62">🇮🇩 +62</option>
                                    <option value="+90">🇹🇷 +90</option>
                                    <option value="+20">🇪🇬 +20</option>
                                    <option value="+27">🇿🇦 +27</option>
                                    <option value="+64">🇳🇿 +64</option>
                                    <option value="+34">🇪🇸 +34</option>
                                    <option value="+31">🇳🇱 +31</option>
                                    <option value="+46">🇸🇪 +46</option>
                                    <option value="+41">🇨🇭 +41</option>
                                    <option value="+47">🇳🇴 +47</option>
                                    <option value="+45">🇩🇰 +45</option>
                                    <option value="+358">🇫🇮 +358</option>
                                    <option value="+353">🇮🇪 +353</option>
                                    <option value="+351">🇵🇹 +351</option>
                                    <option value="+30">🇬🇷 +30</option>
                                    <option value="+43">🇦🇹 +43</option>
                                    <option value="+32">🇧🇪 +32</option>
                                    <option value="+55">🇧🇷 +55</option>
                                    <option value="+52">🇲🇽 +52</option>
                                    <option value="+86">🇨🇳 +86</option>
                                    <option value="+852">🇭🇰 +852</option>
                                    <option value="+886">🇹🇼 +886</option>
                                    <option value="+63">🇵🇭 +63</option>
                                    <option value="+84">🇻🇳 +84</option>
                                    <option value="+962">🇯🇴 +962</option>
                                    <option value="+961">🇱🇧 +961</option>
                                    <option value="+964">🇮🇶 +964</option>
                                    <option value="+212">🇲🇦 +212</option>
                                    <option value="+234">🇳🇬 +234</option>
                                    <option value="+254">🇰🇪 +254</option>
                                </select>

                                <input type="tel" name="phone" id="buyerPhoneInput" class="form-control @error('phone') is-invalid @enderror"
                                       value="{{ old('phone') }}" required placeholder="01XXXXXXXXX"
                                       style="border: 1px solid #cbd5e1; font-size: 0.95rem; transition: border-color 0.2s ease;"
                                       oninput="resetPhoneExistsAlert()">

                                <button type="button" class="btn btn-outline-primary fw-semibold px-3" id="sendOtpBtn" onclick="handleSendOtp()" style="border: 1px solid #cbd5e1; border-left: none; font-size: 0.88rem;">
                                    <span class="spinner-border spinner-border-sm d-none" id="otpSpinner" role="status"></span>
                                    <span id="sendOtpText"><i class="fa-solid fa-paper-plane me-1"></i> Send Code</span>
                                </button>
                            </div>
                            @error('phone')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror

                            {{-- Dynamic Interactive "Already Registered" Banner --}}
                            <div id="phoneExistsAlert" class="card border-0 rounded-3 p-3 mt-2.5 shadow-xs d-none" style="background: #fffbeb; border: 1.5px solid #fcd34d !important; animation: fadeIn 0.3s ease;">
                                <div class="d-flex align-items-start gap-2.5">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 mt-0.5" style="width: 32px; height: 32px; background: #fef3c7; color: #d97706;">
                                        <i class="fa-solid fa-triangle-exclamation fs-6"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center justify-content-between mb-0.5">
                                            <strong class="text-dark fw-bold" style="font-size: 13.5px;">Account Already Registered</strong>
                                            <button type="button" class="btn-close" style="font-size: 9px;" onclick="resetPhoneExistsAlert()" aria-label="Close"></button>
                                        </div>
                                        <p class="text-secondary small mb-2.5" style="font-size: 12.5px; line-height: 1.4;">
                                            An account is already associated with this mobile number. You can log in directly or reset your password if forgotten.
                                        </p>
                                        <div class="d-flex flex-wrap align-items-center gap-2">
                                            <a href="{{ route('login') }}" id="dynamicLoginBtn" class="btn btn-warning btn-sm fw-bold px-3 py-1.5 shadow-2xs d-inline-flex align-items-center gap-1.5" style="background: #f59e0b; color: #fff; border: none; font-size: 12.5px; border-radius: 6px;">
                                                <i class="fa-solid fa-arrow-right-to-bracket"></i> Log In Now
                                            </a>
                                            <a href="{{ route('password.request') }}" class="btn btn-outline-secondary btn-sm py-1.5 px-2.5 d-inline-flex align-items-center gap-1" style="font-size: 12px; border-color: #cbd5e1; border-radius: 6px;">
                                                <i class="fa-solid fa-key text-muted"></i> Forgot Password?
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Always-Visible OTP Verification Panel --}}
                            <div id="otpInputContainer" class="mt-2.5 p-3 rounded-3 bg-light" style="border: 1px solid #cbd5e1;">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <label class="form-label small fw-bold mb-0 text-dark">
                                        <i class="fa-solid fa-key text-warning me-1"></i> Enter 6-digit OTP code sent to your mobile:
                                    </label>
                                    <span id="otpCountdownText" class="badge bg-white text-muted border font-monospace" style="font-size: 11px;">60s</span>
                                </div>
                                <div class="input-group mb-2">
                                    <input type="text" id="buyerOtpCode" class="form-control font-monospace text-center fw-bold fs-6" maxlength="6" placeholder="______" style="letter-spacing: 4px; border: 1px solid #cbd5e1; background: #fff;">
                                    <button type="button" class="btn btn-success fw-bold px-3" id="verifyOtpBtn" onclick="handleVerifyOtp()">
                                        <span class="spinner-border spinner-border-sm d-none" id="verifySpinner"></span>
                                        <span id="verifyOtpText"><i class="fa-solid fa-circle-check me-1"></i> Verify</span>
                                    </button>
                                </div>
                                <div id="otpFeedback" class="small d-none"></div>
                            </div>
                        </div>

                        {{-- Email Address (Optional) --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark small mb-1 d-flex justify-content-between">
                                <span>Email Address</span>
                                <span class="badge bg-light text-muted border px-1.5 py-0.5" style="font-size: 10.5px;">Optional</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted" style="border: 1px solid #cbd5e1; border-right: none;">
                                    <i class="fa-solid fa-envelope"></i>
                                </span>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                       value="{{ old('email') }}" placeholder="email@example.com"
                                       style="border: 1px solid #cbd5e1; font-size: 0.95rem;">
                            </div>
                            @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        {{-- Password & Confirm Password with Gmail-style Strength Indicator --}}
                        <div class="row g-2">
                            <div class="col-sm-6 mb-2">
                                <label class="form-label fw-bold text-dark small mb-1">
                                    Password <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="password" name="password" id="buyerRegPassword" class="form-control @error('password') is-invalid @enderror" 
                                           required minlength="8" maxlength="25" placeholder="Min 8 chars & symbol" 
                                           style="border: 1px solid #cbd5e1;"
                                           oninput="checkPasswordStrength(this.value)">
                                    <button type="button" class="btn btn-outline-secondary" style="border: 1px solid #cbd5e1; border-left: none;" onclick="togglePasswordVisibility('buyerRegPassword', this)" title="Show/Hide Password">
                                        <i class="fa-regular fa-eye"></i>
                                    </button>
                                </div>
                                @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-sm-6 mb-2">
                                <label class="form-label fw-bold text-dark small mb-1">
                                    Confirm Password <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="password" name="password_confirmation" id="buyerRegPasswordConfirm" class="form-control" 
                                           required minlength="8" maxlength="25" placeholder="Retype password"
                                           style="border: 1px solid #cbd5e1;"
                                           oninput="checkPasswordMatch()">
                                    <button type="button" class="btn btn-outline-secondary" style="border: 1px solid #cbd5e1; border-left: none;" onclick="togglePasswordVisibility('buyerRegPasswordConfirm', this)" title="Show/Hide Password">
                                        <i class="fa-regular fa-eye"></i>
                                    </button>
                                </div>
                            </div>

                            {{-- Gmail-Style Multi-Segment Strength Indicator --}}
                            <div class="col-12 mb-3">
                                <div class="p-2.5 rounded-3 bg-light border" style="border: 1px solid #cbd5e1 !important;">
                                    <div class="d-flex align-items-center justify-content-between mb-1.5" style="font-size: 12px;">
                                        <span class="text-muted fw-semibold">Password Strength:</span>
                                        <span id="buyerPwdStrengthText" class="fw-bold text-secondary">Use 8+ characters</span>
                                    </div>
                                    {{-- 3-Segment Google/Gmail Style Bar --}}
                                    <div class="d-flex gap-1.5" style="height: 5px;">
                                        <div id="pwdSeg1" class="flex-grow-1 rounded-pill" style="background-color: #e2e8f0; transition: background-color 0.3s ease;"></div>
                                        <div id="pwdSeg2" class="flex-grow-1 rounded-pill" style="background-color: #e2e8f0; transition: background-color 0.3s ease;"></div>
                                        <div id="pwdSeg3" class="flex-grow-1 rounded-pill" style="background-color: #e2e8f0; transition: background-color 0.3s ease;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Delivery Address (Optional) --}}
                        <div class="mb-3.5">
                            <label class="form-label fw-bold text-dark small mb-1 d-flex justify-content-between">
                                <span>Delivery Address</span>
                                <span class="badge bg-light text-muted border px-1.5 py-0.5" style="font-size: 10.5px;">Optional</span>
                            </label>
                            <textarea name="address" rows="2" class="form-control" placeholder="House/Road, Area, City/District" style="border: 1px solid #cbd5e1; font-size: 0.92rem;">{{ old('address') }}</textarea>
                        </div>

                        {{-- Quick Perks Alert --}}
                        <div class="alert alert-success small py-2.5 px-3 rounded-3 d-flex align-items-center gap-2 mb-3.5" style="border: 1px solid #86efac; background: #f0fdf4;">
                            <i class="fa-solid fa-circle-check text-success fs-5 flex-shrink-0"></i>
                            <span class="text-success-emphasis" style="font-size: 0.86rem;">
                                Order books instantly via Cash on Delivery or Online Payment.
                            </span>
                        </div>

                        {{-- Create Account Submit Button --}}
                        <button type="submit" class="btn w-100 py-3 fw-bold text-white rounded-3 shadow-sm d-flex align-items-center justify-content-center gap-2" id="buyerSubmitBtn" style="background: #ea580c; border: 1px solid #c2410c; font-size: 15.5px;">
                            <span class="spinner-border spinner-border-sm d-none" id="buyerSubmitSpinner" role="status"></span>
                            <i class="fa-solid fa-user-plus" id="buyerSubmitIcon"></i>
                            <span id="buyerSubmitText">Create Account</span>
                        </button>
                        
                        <p class="text-center mt-3 mb-0">
                            <a href="{{ route('register.choose') }}" class="text-muted small text-decoration-none hover-primary">
                                <i class="fa-solid fa-arrow-left me-1"></i> Other Accounts (Author / Publisher / Seller)
                            </a>
                        </p>
                    </form>
                </div>
            </div>

            {{-- Login Link Helper --}}
            <div class="text-center mt-3 small text-muted">
                Already have an account? <a href="{{ route('login') }}" class="fw-bold text-primary text-decoration-none">Log In</a>
            </div>

        </div>
    </div>
</div>

<script>
// Show/Hide password toggle
function togglePasswordVisibility(inputId, btn) {
    const input = document.getElementById(inputId);
    const icon = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        if (icon) {
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        }
    } else {
        input.type = 'password';
        if (icon) {
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }
}

// Reset the "Already Registered" alert on phone input modification
function resetPhoneExistsAlert() {
    const alertBox = document.getElementById('phoneExistsAlert');
    const phoneInput = document.getElementById('buyerPhoneInput');
    const statusBadge = document.getElementById('otpStatusBadge');

    if (alertBox && !alertBox.classList.contains('d-none')) {
        alertBox.classList.add('d-none');
    }
    if (phoneInput) {
        phoneInput.style.borderColor = '#cbd5e1';
    }
    if (statusBadge && statusBadge.textContent.includes('Registered')) {
        statusBadge.className = 'badge bg-secondary-subtle text-secondary border px-2 py-0.5';
        statusBadge.innerHTML = '<i class="fa-solid fa-shield-halved me-1"></i> SMS Verification';
    }
}

// Gmail-Style 3-Segment Password Strength Evaluation (Weak, Medium, Strong)
function checkPasswordStrength(password) {
    const seg1 = document.getElementById('pwdSeg1');
    const seg2 = document.getElementById('pwdSeg2');
    const seg3 = document.getElementById('pwdSeg3');
    const text = document.getElementById('buyerPwdStrengthText');

    if (!seg1 || !seg2 || !seg3 || !text) return;

    if (!password) {
        seg1.style.backgroundColor = '#e2e8f0';
        seg2.style.backgroundColor = '#e2e8f0';
        seg3.style.backgroundColor = '#e2e8f0';
        text.textContent = 'Use 8+ characters';
        text.className = 'fw-bold text-secondary';
        return;
    }

    let score = 0;
    if (password.length >= 8) score += 30;
    if (/[a-z]/.test(password) && /[A-Z]/.test(password)) score += 25;
    if (/[0-9]/.test(password)) score += 25;
    if (/[!@#$%^&*(),.?":{}|<>_\-+=]/.test(password)) score += 20;

    if (score < 50 || password.length < 8) {
        // Weak: Segment 1 Red, rest gray
        seg1.style.backgroundColor = '#ea4335'; // Gmail Red
        seg2.style.backgroundColor = '#e2e8f0';
        seg3.style.backgroundColor = '#e2e8f0';
        text.textContent = 'Weak';
        text.className = 'fw-bold text-danger';
    } else if (score < 80) {
        // Medium: Segments 1 & 2 Yellow/Orange, rest gray
        seg1.style.backgroundColor = '#fbbc05'; // Gmail Yellow
        seg2.style.backgroundColor = '#fbbc05';
        seg3.style.backgroundColor = '#e2e8f0';
        text.textContent = 'Medium';
        text.className = 'fw-bold text-warning';
    } else {
        // Strong: Segments 1, 2, 3 Green
        seg1.style.backgroundColor = '#34a853'; // Gmail Green
        seg2.style.backgroundColor = '#34a853';
        seg3.style.backgroundColor = '#34a853';
        text.textContent = 'Strong';
        text.className = 'fw-bold text-success';
    }
}

// Check confirm password match
function checkPasswordMatch() {
    const pwd = document.getElementById('buyerRegPassword').value;
    const confirmPwd = document.getElementById('buyerRegPasswordConfirm').value;
    const confirmInput = document.getElementById('buyerRegPasswordConfirm');
    if (confirmPwd.length > 0) {
        if (pwd === confirmPwd) {
            confirmInput.style.borderColor = '#34a853';
        } else {
            confirmInput.style.borderColor = '#ea4335';
        }
    } else {
        confirmInput.style.borderColor = '#cbd5e1';
    }
}

// Handle SMS OTP Sending
let otpCooldownTimer = null;

function handleSendOtp() {
    const phoneInput = document.getElementById('buyerPhoneInput');
    const countryCode = document.getElementById('countryCodeSelect').value;
    const sendBtn = document.getElementById('sendOtpBtn');
    const spinner = document.getElementById('otpSpinner');
    const sendText = document.getElementById('sendOtpText');
    const otpFeedback = document.getElementById('otpFeedback');
    const phoneExistsAlert = document.getElementById('phoneExistsAlert');
    const statusBadge = document.getElementById('otpStatusBadge');

    const phone = phoneInput.value.trim();
    if (!phone) {
        alert('Please enter your mobile number first.');
        phoneInput.focus();
        return;
    }

    sendBtn.disabled = true;
    spinner.classList.remove('d-none');
    sendText.innerHTML = 'Sending...';

    fetch('{{ url("/register/send-otp") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            phone: phone,
            country_code: countryCode
        })
    })
    .then(res => res.json().then(data => ({ status: res.status, body: data })))
    .then(result => {
        spinner.classList.add('d-none');

        // Case 1: Account Already Exists -> Show Interactive Dynamic Card
        if (result.status === 422 && (result.body.already_exists || (result.body.message && result.body.message.toLowerCase().includes('already registered')))) {
            sendBtn.disabled = false;
            sendText.innerHTML = '<i class="fa-solid fa-paper-plane me-1"></i> Send Code';

            phoneInput.style.borderColor = '#f59e0b';
            if (phoneExistsAlert) {
                phoneExistsAlert.classList.remove('d-none');
                phoneExistsAlert.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
            if (statusBadge) {
                statusBadge.className = 'badge bg-warning text-dark border border-warning px-2 py-0.5';
                statusBadge.innerHTML = '<i class="fa-solid fa-circle-exclamation me-1"></i> Already Registered';
            }
            return;
        }

        // Case 2: Successful OTP Dispatch
        if (result.status === 200 && result.body.success) {
            resetPhoneExistsAlert();
            otpFeedback.classList.remove('d-none', 'text-danger');
            otpFeedback.classList.add('text-success');
            otpFeedback.innerHTML = '<i class="fa-solid fa-circle-check me-1"></i> ' + result.body.message;

            const waLink = document.getElementById('whatsappOtpLink');
            if (waLink && result.body.whatsapp_url) {
                waLink.href = result.body.whatsapp_url;
            }

            startOtpCooldown(result.body.cooldown || 60);
            document.getElementById('buyerOtpCode').focus();
        } else {
            sendBtn.disabled = false;
            sendText.innerHTML = '<i class="fa-solid fa-paper-plane me-1"></i> Send Code';
            const errMsg = result.body.message || (result.body.errors ? Object.values(result.body.errors)[0][0] : 'Failed to send verification code.');
            alert(errMsg);
        }
    })
    .catch(err => {
        spinner.classList.add('d-none');
        sendBtn.disabled = false;
        sendText.innerHTML = '<i class="fa-solid fa-paper-plane me-1"></i> Send Code';
        alert('Unable to connect to server. Please check your internet connection.');
    });
}

function startOtpCooldown(seconds) {
    const sendBtn = document.getElementById('sendOtpBtn');
    const sendText = document.getElementById('sendOtpText');
    const countdownBadge = document.getElementById('otpCountdownText');

    let remaining = seconds;
    sendBtn.disabled = true;
    if (otpCooldownTimer) clearInterval(otpCooldownTimer);

    otpCooldownTimer = setInterval(() => {
        remaining--;
        if (countdownBadge) countdownBadge.textContent = remaining + 's';
        sendText.innerHTML = `Resend (${remaining}s)`;

        if (remaining <= 0) {
            clearInterval(otpCooldownTimer);
            sendBtn.disabled = false;
            sendText.innerHTML = '<i class="fa-solid fa-rotate-right me-1"></i> Resend Code';
            if (countdownBadge) countdownBadge.textContent = '60s';
        }
    }, 1000);
}

// Handle SMS OTP Verification
function handleVerifyOtp() {
    const phone = document.getElementById('buyerPhoneInput').value.trim();
    const countryCode = document.getElementById('countryCodeSelect').value;
    const otp = document.getElementById('buyerOtpCode').value.trim();
    const verifyBtn = document.getElementById('verifyOtpBtn');
    const spinner = document.getElementById('verifySpinner');
    const verifyText = document.getElementById('verifyOtpText');
    const otpFeedback = document.getElementById('otpFeedback');
    const statusBadge = document.getElementById('otpStatusBadge');

    if (!otp || otp.length < 4) {
        alert('Please enter the 6-digit OTP code.');
        document.getElementById('buyerOtpCode').focus();
        return;
    }

    verifyBtn.disabled = true;
    spinner.classList.remove('d-none');
    verifyText.innerHTML = 'Verifying...';

    fetch('{{ url("/register/verify-otp") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            phone: phone,
            country_code: countryCode,
            otp: otp
        })
    })
    .then(res => res.json().then(data => ({ status: res.status, body: data })))
    .then(result => {
        spinner.classList.add('d-none');
        verifyBtn.disabled = false;
        verifyText.innerHTML = '<i class="fa-solid fa-circle-check me-1"></i> Verify';

        if (result.status === 200 && result.body.success) {
            otpFeedback.classList.remove('d-none', 'text-danger');
            otpFeedback.classList.add('text-success');
            otpFeedback.innerHTML = '<i class="fa-solid fa-circle-check me-1"></i> ' + result.body.message;

            statusBadge.className = 'badge bg-success-subtle text-success border border-success-subtle px-2 py-0.5';
            statusBadge.innerHTML = '<i class="fa-solid fa-circle-check me-1"></i> Verified';

            document.getElementById('buyerPhoneInput').readOnly = true;
            document.getElementById('countryCodeSelect').disabled = true;
            document.getElementById('sendOtpBtn').disabled = true;
            document.getElementById('buyerOtpCode').readOnly = true;
            verifyBtn.classList.replace('btn-success', 'btn-secondary');
            verifyBtn.disabled = true;
            verifyBtn.innerHTML = '<i class="fa-solid fa-check"></i> Verified';
        } else {
            otpFeedback.classList.remove('d-none', 'text-success');
            otpFeedback.classList.add('text-danger');
            otpFeedback.innerHTML = '<i class="fa-solid fa-circle-xmark me-1"></i> ' + (result.body.message || 'Invalid OTP code.');
        }
    })
    .catch(err => {
        spinner.classList.add('d-none');
        verifyBtn.disabled = false;
        verifyText.innerHTML = '<i class="fa-solid fa-circle-check me-1"></i> Verify';
        alert('An error occurred during verification.');
    });
}
</script>
@endsection
