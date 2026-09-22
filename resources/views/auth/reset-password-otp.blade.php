<!DOCTYPE html>
<html lang="en" class="notranslate" translate="no">
<head>
    <meta charset="UTF-8">
    <meta name="google" content="notranslate">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Reset Password — ideaabd</title>

    {{-- Favicon --}}
    @php $siteFaviconUrl = \App\Support\SiteSetting::faviconUrl(); @endphp
    @if ($siteFaviconUrl)
        <link rel="icon" href="{{ $siteFaviconUrl }}">
    @else
        <link rel="icon" href="{{ asset('favicon.ico') }}">
    @endif

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome 6 Pro / Free -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* ══════════════════════════════════════════════════════════════════
           CLEAN SKY-BLUE AUTHENTICATION ARCHITECTURE
           ══════════════════════════════════════════════════════════════════ */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }

        html, body {
            min-height: 100%;
            background-color: #ffffff;
            color: #0f1111;
            display: flex;
            flex-direction: column;
            align-items: center;
            -webkit-font-smoothing: antialiased;
        }

        .auth-container {
            width: 100%;
            max-width: 380px;
            margin: 0 auto;
            padding: 24px 16px 40px 16px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* 1. Header Logo */
        .auth-header {
            margin-top: 8px;
            margin-bottom: 20px;
            text-align: center;
        }

        .brand-link {
            display: inline-flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 6px;
            text-decoration: none;
            color: #0f1111;
        }

        .brand-logo-img {
            max-height: 48px;
            width: auto;
            object-fit: contain;
            display: block;
        }

        .brand-title-text {
            font-size: 15px;
            font-weight: 700;
            letter-spacing: 0.5px;
            color: #0f172a;
            text-transform: uppercase;
            line-height: 1.2;
            display: block;
            text-align: center;
        }

        /* 2. Main Box Card */
        .auth-box {
            width: 100%;
            background: #ffffff;
            border: 1px solid #d5d9d9;
            border-radius: 8px;
            padding: 24px 26px 26px 26px;
            box-shadow: 0 1px 2px rgba(15, 17, 17, 0.05);
            margin-bottom: 20px;
            position: relative;
            transition: all 0.3s ease;
        }

        .auth-heading {
            font-size: 26px;
            font-weight: 500;
            line-height: 1.2;
            color: #0f1111;
            margin-bottom: 8px;
            letter-spacing: -0.3px;
        }

        .auth-subtext {
            font-size: 13px;
            line-height: 1.5;
            color: #333333;
            margin-bottom: 16px;
        }

        /* Step Progress Indicator */
        .step-indicator {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 18px;
            padding-bottom: 12px;
            border-bottom: 1px solid #f0f2f2;
        }

        .step-pill {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 11.5px;
            font-weight: 600;
            color: #767676;
        }

        .step-pill.active {
            color: #0284c7;
        }

        .step-dot {
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background: #e5e7eb;
            color: #4b5563;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            font-weight: 700;
        }

        .step-pill.active .step-dot {
            background: #0284c7;
            color: #ffffff;
        }

        .step-pill.done .step-dot {
            background: #10b981;
            color: #ffffff;
        }

        /* Form Controls */
        .form-group-item {
            margin-bottom: 14px;
        }

        .form-label-custom {
            display: block;
            font-size: 13px;
            font-weight: 700;
            color: #0f1111;
            margin-bottom: 4px;
            line-height: 1.3;
        }

        .input-text-custom {
            width: 100%;
            height: 34px;
            background-color: #ffffff;
            border: 1px solid #888c8c;
            border-radius: 4px;
            box-shadow: 0 1px 2px rgba(15, 17, 17, 0.15) inset;
            font-size: 13.5px;
            padding: 3px 8px;
            color: #0f1111;
            outline: 0;
            transition: all 0.15s ease;
        }

        .input-text-custom:focus {
            border-color: #0284c7;
            box-shadow: 0 0 3px 2px rgba(14, 165, 233, 0.45), 0 1px 2px rgba(15, 17, 17, 0.15) inset;
        }

        .input-otp-custom {
            letter-spacing: 6px;
            font-size: 16px;
            text-align: center;
            font-family: monospace;
            font-weight: 700;
        }

        .pwd-field-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }

        .pwd-field-wrap .input-text-custom {
            padding-right: 36px;
        }

        .pwd-eye-btn {
            position: absolute;
            right: 1px;
            top: 1px;
            bottom: 1px;
            width: 32px;
            background: transparent;
            border: none;
            color: #565959;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 13px;
        }

        .pwd-eye-btn:hover {
            color: #0f1111;
        }

        .custom-link {
            color: #007185;
            text-decoration: none;
            font-size: 13px;
            transition: color 0.15s;
        }

        .custom-link:hover {
            color: #c7511f;
            text-decoration: underline;
        }

        /* 3. Primary Button (Sky-Blue Action) */
        .btn-action-primary {
            width: 100%;
            height: 34px;
            background: linear-gradient(180deg, #0ea5e9 0%, #0284c7 100%);
            border: 1px solid #0369a1;
            border-radius: 8px;
            box-shadow: 0 2px 5px 0 rgba(2, 132, 199, 0.3);
            color: #ffffff;
            font-size: 13px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.15s ease;
            margin-top: 14px;
        }

        .btn-action-primary:hover {
            background: linear-gradient(180deg, #0284c7 0%, #0369a1 100%);
            color: #ffffff;
            border-color: #075985;
        }

        .btn-action-primary:disabled {
            opacity: 0.65;
            cursor: not-allowed;
        }

        /* Step Panels */
        .auth-step-panel {
            display: block;
        }

        .auth-step-panel.d-none {
            display: none !important;
        }

        /* 4. Footer */
        .auth-footer {
            width: 100%;
            border-top: 1px solid #eaeded;
            margin-top: 20px;
            padding-top: 20px;
            text-align: center;
        }

        .footer-links {
            display: flex;
            justify-content: center;
            gap: 24px;
            margin-bottom: 12px;
            flex-wrap: wrap;
        }

        .footer-link-item {
            font-size: 11.5px;
            color: #007185;
            text-decoration: none;
        }

        .footer-link-item:hover {
            color: #c7511f;
            text-decoration: underline;
        }

        .footer-copy {
            font-size: 11px;
            color: #555555;
        }
    </style>
</head>
<body>

<div class="auth-container">
    
    {{-- Header Logo --}}
    <div class="auth-header">
        <a href="{{ url('/') }}" class="brand-link" title="Idea Publication">
            @php $siteLogoUrl = \App\Support\SiteSetting::loginLogoUrl() ?: \App\Support\SiteSetting::logoUrl(); @endphp
            @if ($siteLogoUrl)
                <img src="{{ $siteLogoUrl }}" alt="Idea Logo" class="brand-logo-img">
            @else
                <span class="brand-title-text">{{ config('app.name', 'Idea Prokashon') }}</span>
            @endif
        </a>
    </div>

    {{-- Main Box --}}
    <div class="auth-box">
        
        {{-- Step Progress Indicator --}}
        <div class="step-indicator">
            <div class="step-pill active" id="pillStep1">
                <span class="step-dot" id="dotStep1">1</span>
                <span>Verify OTP</span>
            </div>
            <i class="fa-solid fa-chevron-right text-muted" style="font-size: 9px;"></i>
            <div class="step-pill" id="pillStep2">
                <span class="step-dot" id="dotStep2">2</span>
                <span>New Password</span>
            </div>
        </div>

        {{-- Dynamic Alerts --}}
        <div id="alertBox" class="alert py-2 px-3 small rounded-2 mb-3 border-0 d-none"></div>

        @if(session('status') || session('success'))
            <div class="alert alert-success py-2 px-3 small rounded-2 mb-3 border-0 bg-success bg-opacity-10 text-success fw-medium" id="serverStatusAlert">
                <i class="fa-solid fa-circle-check me-1"></i> {{ session('status') ?: session('success') }}
            </div>
        @endif

        @if(isset($errors) && $errors->any())
            <div class="alert alert-danger py-2 px-3 small rounded-2 mb-3 border-0 bg-danger bg-opacity-10 text-danger" id="serverErrorAlert">
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- ========================================================================= --}}
        {{-- STEP 1: OTP VERIFICATION TABLE / FORM --}}
        {{-- ========================================================================= --}}
        <div id="step1Panel" class="auth-step-panel">
            <h1 class="auth-heading">Verify Code</h1>
            <p class="auth-subtext">Enter the 6-digit OTP code sent to your mobile SMS or email to verify your identity.</p>

            <form id="verifyOtpForm" onsubmit="handleVerifyOtp(event)">
                <div class="form-group-item">
                    <label for="phone" class="form-label-custom">Email or mobile phone number</label>
                    <input type="text" 
                           id="phone" 
                           name="phone" 
                           class="input-text-custom" 
                           value="{{ old('phone', $phone ?? '') }}" 
                           required 
                           placeholder="example@mail.com or 01XXXXXXXXX">
                </div>

                <div class="form-group-item">
                    <label for="otp" class="form-label-custom">6-digit Verification Code (OTP)</label>
                    <input type="text" 
                           id="otp" 
                           name="otp" 
                           class="input-text-custom input-otp-custom" 
                           value="{{ old('otp') }}" 
                           required 
                           maxlength="6"
                           autocomplete="one-time-code"
                           placeholder="------">
                </div>

                <button type="submit" id="btnVerifyOtp" class="btn-action-primary">
                    <span id="btnVerifyText">Continue</span>
                    <i class="fa-solid fa-arrow-right ms-1"></i>
                </button>
            </form>

            <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                <a href="{{ route('password.request') }}" class="custom-link" style="font-size: 12px;">
                    Resend code
                </a>
                <a href="https://api.whatsapp.com/send?phone=8801558712810&text={{ urlencode('I need assistance with password reset code.') }}" target="_blank" class="custom-link" style="font-size: 12px;">
                    <i class="fab fa-whatsapp text-success me-1"></i> Helpline
                </a>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- STEP 2: NEW PASSWORD TABLE / FORM (Appears upon verification) --}}
        {{-- ========================================================================= --}}
        <div id="step2Panel" class="auth-step-panel d-none">
            <h1 class="auth-heading">Create New Password</h1>
            <p class="auth-subtext" id="step2Subtext">Verification confirmed. Please enter your new password below.</p>

            <form method="POST" action="{{ route('password.update-otp') }}" id="finalResetForm">
                @csrf

                <input type="hidden" id="finalPhone" name="phone" value="{{ old('phone', $phone ?? '') }}">
                <input type="hidden" id="finalOtp" name="otp" value="{{ old('otp') }}">

                <div class="form-group-item">
                    <label for="new_password" class="form-label-custom">New password</label>
                    <div class="pwd-field-wrap">
                        <input type="password" 
                               id="new_password" 
                               name="password" 
                               class="input-text-custom @error('password') is-invalid @enderror" 
                               required 
                               minlength="6" 
                               placeholder="At least 6 characters">
                        <button type="button" class="pwd-eye-btn" onclick="togglePasswordVisibility('new_password', this)">
                            <i class="fa-regular fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="form-group-item">
                    <label for="password_confirmation" class="form-label-custom">Confirm new password</label>
                    <div class="pwd-field-wrap">
                        <input type="password" 
                               id="password_confirmation" 
                               name="password_confirmation" 
                               class="input-text-custom" 
                               required 
                               minlength="6" 
                               placeholder="Re-enter new password">
                        <button type="button" class="pwd-eye-btn" onclick="togglePasswordVisibility('password_confirmation', this)">
                            <i class="fa-regular fa-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-action-primary" id="btnSavePassword">
                    Save Changes and Sign In
                </button>
            </form>

            <div class="text-center mt-3 pt-2 border-top">
                <a href="javascript:void(0)" onclick="backToStep1()" class="custom-link" style="font-size: 12px;">
                    <i class="fa-solid fa-arrow-left me-1"></i> Change verification code
                </a>
            </div>
        </div>

    </div>

    {{-- Back to sign-in --}}
    <div class="text-center mb-3">
        <a href="{{ route('login') }}" class="custom-link">
            <i class="fa-solid fa-arrow-left me-1"></i> Back to Sign In
        </a>
    </div>

    {{-- Footer --}}
    <footer class="auth-footer">
        <div class="footer-links">
            <a href="{{ url('/pages/terms') }}" class="footer-link-item">Conditions of Use</a>
            <a href="{{ url('/pages/privacy') }}" class="footer-link-item">Privacy Notice</a>
            <a href="{{ url('/pages/help') }}" class="footer-link-item">Help</a>
        </div>
        <p class="footer-copy">© {{ date('Y') }}, Idea Publication or its affiliates. All rights reserved.</p>
    </footer>

</div>

<script>
    function showAlert(type, message) {
        const alertBox = document.getElementById('alertBox');
        alertBox.className = 'alert py-2 px-3 small rounded-2 mb-3 border-0 ' + 
            (type === 'success' ? 'bg-success bg-opacity-10 text-success fw-medium' : 'bg-danger bg-opacity-10 text-danger');
        alertBox.innerHTML = (type === 'success' ? '<i class="fa-solid fa-circle-check me-1"></i> ' : '<i class="fa-solid fa-circle-exclamation me-1"></i> ') + message;
        alertBox.classList.remove('d-none');
    }

    function hideAlert() {
        const alertBox = document.getElementById('alertBox');
        alertBox.classList.add('d-none');
    }

    function normalizeDigits(str) {
        const bn = ['০','১','২','৩','৪','৫','৬','৭','৮','৯'];
        const en = ['0','1','2','3','4','5','6','7','8','9'];
        let res = str || '';
        for (let i = 0; i < 10; i++) {
            res = res.replaceAll(bn[i], en[i]);
        }
        return res;
    }

    function handleVerifyOtp(e) {
        e.preventDefault();
        hideAlert();

        const phoneInput = document.getElementById('phone');
        const otpInput = document.getElementById('otp');
        const phone = normalizeDigits(phoneInput.value.trim());
        const otp = normalizeDigits(otpInput.value.trim());
        const btn = document.getElementById('btnVerifyOtp');
        const btnText = document.getElementById('btnVerifyText');

        phoneInput.value = phone;
        otpInput.value = otp;

        if (!phone) {
            showAlert('danger', 'Please enter your email or mobile phone number.');
            return;
        }

        if (!otp || otp.length !== 6 || !/^\d{6}$/.test(otp)) {
            showAlert('danger', 'Please enter the exact 6-digit verification code received on your phone/email.');
            return;
        }

        // Disable button & show spinner
        btn.disabled = true;
        btnText.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin me-1"></i> Verifying...';

        fetch("{{ route('password.verify-otp') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ phone: phone, otp: otp })
        })
        .then(async response => {
            const isJson = response.headers.get('content-type')?.includes('application/json');
            const data = isJson ? await response.json() : null;
            return { ok: response.ok, status: response.status, data: data };
        })
        .then(({ ok, status, data }) => {
            btn.disabled = false;
            btnText.innerHTML = 'Continue';

            if (ok && data && data.success === true) {
                // Success: Transition to Step 2
                document.getElementById('finalPhone').value = phone;
                document.getElementById('finalOtp').value = otp;

                // Update Step Indicators
                document.getElementById('pillStep1').className = 'step-pill done';
                document.getElementById('dotStep1').innerHTML = '<i class="fa-solid fa-check"></i>';
                document.getElementById('pillStep2').className = 'step-pill active';

                // Switch Panels
                document.getElementById('step1Panel').classList.add('d-none');
                document.getElementById('step2Panel').classList.remove('d-none');

                if (data.user_name) {
                    document.getElementById('step2Subtext').innerText = 'Verified for ' + data.user_name + ' (' + phone + '). Please create your new password.';
                } else {
                    document.getElementById('step2Subtext').innerText = 'Verified for ' + phone + '. Please create your new password.';
                }

                showAlert('success', 'Verification code confirmed. Please set your new password.');
                document.getElementById('new_password').focus();
            } else {
                const errMsg = (data && data.message) ? data.message : 'Invalid verification code. Please enter the exact 6-digit OTP code sent to your phone.';
                showAlert('danger', errMsg);
            }
        })
        .catch(err => {
            btn.disabled = false;
            btnText.innerHTML = 'Continue';
            showAlert('danger', 'Verification failed. Please check your connection and try again.');
        });
    }

    function backToStep1() {
        hideAlert();
        document.getElementById('pillStep1').className = 'step-pill active';
        document.getElementById('dotStep1').innerText = '1';
        document.getElementById('pillStep2').className = 'step-pill';

        document.getElementById('step2Panel').classList.add('d-none');
        document.getElementById('step1Panel').classList.remove('d-none');
    }

    function togglePasswordVisibility(inputId, btn) {
        const input = document.getElementById(inputId);
        const icon = btn.querySelector('i');
        if (!input) return;

        if (input.type === 'password') {
            input.type = 'text';
            icon.className = 'fa-regular fa-eye-slash';
        } else {
            input.type = 'password';
            icon.className = 'fa-regular fa-eye';
        }
    }
</script>

</body>
</html>
