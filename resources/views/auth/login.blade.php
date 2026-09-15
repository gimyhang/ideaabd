<!DOCTYPE html>
<html lang="en" class="notranslate" translate="no">
<head>
    <meta charset="UTF-8">
    <meta name="google" content="notranslate">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Idea — Sign In & Create Account</title>

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

    <!-- Google reCAPTCHA v2 Script -->
    <script src="https://www.google.com/recaptcha/api.js?onload=onRecaptchaLoaded&render=explicit" async defer></script>

    <style>
        /* ══════════════════════════════════════════════════════════════════
           PREMIUM SKY-BLUE INTERNATIONAL AUTHENTICATION ARCHITECTURE
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
            padding: 16px 16px 36px 16px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* 1. Header Logo */
        .auth-header {
            margin-top: 8px;
            margin-bottom: 18px;
            text-align: center;
        }

        .brand-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            color: #0f1111;
        }

        .brand-logo-img {
            max-height: 40px;
            width: auto;
            object-fit: contain;
        }

        .brand-title-text {
            font-size: 24px;
            font-weight: 800;
            letter-spacing: -0.4px;
            color: #0f172a;
        }

        /* 2. Main Box Card */
        .auth-box {
            width: 100%;
            background: #ffffff;
            border: 1px solid #d5d9d9;
            border-radius: 8px;
            padding: 22px 26px 26px 26px;
            box-shadow: 0 1px 2px rgba(15, 17, 17, 0.05);
            margin-bottom: 18px;
            position: relative;
        }

        .auth-heading {
            font-size: 28px;
            font-weight: 500;
            line-height: 1.2;
            color: #0f1111;
            margin-bottom: 16px;
            letter-spacing: -0.3px;
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
            margin-bottom: 3px;
            line-height: 1.3;
        }

        .input-text-custom {
            width: 100%;
            height: 33px;
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
            height: 33px;
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
            transition: all 0.15s ease;
            margin-top: 16px;
            text-decoration: none;
        }

        .btn-action-primary:hover:not(:disabled) {
            background: linear-gradient(180deg, #38bdf8 0%, #0284c7 100%);
            border-color: #0284c7;
            color: #ffffff;
        }

        .btn-action-primary:active:not(:disabled) {
            background: #0369a1;
            box-shadow: none;
        }

        .btn-action-primary:disabled {
            opacity: 0.75;
            cursor: not-allowed;
        }

        /* Legal Notice */
        .legal-notice-text {
            font-size: 12px;
            color: #0f1111;
            line-height: 1.5;
            margin-top: 14px;
            margin-bottom: 12px;
        }

        /* Keep me signed in */
        .checkbox-row-custom {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 12.5px;
            color: #0f1111;
            cursor: pointer;
            user-select: none;
            margin-top: 8px;
        }

        .checkbox-row-custom input {
            accent-color: #0284c7;
            width: 14px;
            height: 14px;
            cursor: pointer;
        }

        /* Need Help Expander */
        .help-expander-toggle {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 13px;
            color: #007185;
            cursor: pointer;
            text-decoration: none;
            margin-top: 14px;
            padding-top: 10px;
            border-top: 1px solid #e7e7e7;
            width: 100%;
        }

        .help-expander-toggle:hover {
            color: #c7511f;
            text-decoration: underline;
        }

        .help-expander-panel {
            display: none;
            padding-top: 6px;
            font-size: 12.5px;
        }

        .help-expander-panel.show {
            display: block;
        }

        /* 4. Section Divider */
        .divider-wrapper {
            width: 100%;
            text-align: center;
            position: relative;
            margin: 16px 0 14px 0;
        }

        .divider-wrapper::before {
            content: "";
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background-color: #e7e7e7;
            z-index: 1;
        }

        .divider-label {
            position: relative;
            z-index: 2;
            background-color: #ffffff;
            padding: 0 10px;
            color: #767676;
            font-size: 12px;
            display: inline-block;
        }

        /* Secondary White Button */
        .btn-action-secondary {
            width: 100%;
            height: 33px;
            background: #ffffff;
            border: 1px solid #d5d9d9;
            border-radius: 8px;
            box-shadow: 0 2px 5px 0 rgba(213,217,217,.5);
            color: #0f1111;
            font-size: 13px;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            margin-bottom: 8px;
            transition: all 0.15s ease;
            cursor: pointer;
        }

        .btn-action-secondary:hover {
            background: #f7fafa;
            border-color: #d5d9d9;
            color: #0f1111;
        }

        /* Alert Box */
        .alert-custom-box {
            border: 1px solid #c40000;
            background: #ffffff;
            border-radius: 8px;
            padding: 12px 14px;
            margin-bottom: 14px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
            box-shadow: 0 0 0 4px rgba(221, 0, 0, 0.05);
            font-size: 12.5px;
            color: #0f1111;
            line-height: 1.4;
        }

        .alert-custom-box.d-none {
            display: none !important;
        }

        .alert-custom-box.alert-success-theme {
            border-color: #007600;
            box-shadow: 0 0 0 4px rgba(0, 118, 0, 0.05);
        }

        /* Dynamic Step Panels (Hidden/Shown via JS) */
        .auth-flow-panel {
            display: none;
            animation: fadeInAuth 0.25s ease;
        }

        .auth-flow-panel.active {
            display: block;
        }

        @keyframes fadeInAuth {
            from { opacity: 0; transform: translateY(4px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Modern Digital Photographic Verification Challenge */
        .puzzle-instruction-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: 6px;
            padding: 8px 12px;
            margin-bottom: 12px;
        }

        .puzzle-clue-badge {
            font-size: 13px;
            font-weight: 700;
            color: #0369a1;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .puzzle-clue-icon {
            width: 26px;
            height: 26px;
            border-radius: 6px;
            background: #0284c7;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
        }

        .puzzle-grid-box {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            margin: 10px 0 14px 0;
        }

        .puzzle-tile-item {
            position: relative;
            aspect-ratio: 1;
            border: 2px solid #cbd5e1;
            border-radius: 8px;
            overflow: hidden;
            cursor: pointer;
            background: #0f172a;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
            user-select: none;
        }

        .puzzle-tile-item:hover {
            border-color: #0284c7;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.25);
        }

        .puzzle-tile-item.selected {
            border-color: #0284c7;
            box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.5);
        }

        .puzzle-tile-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
            display: block;
            transition: transform 0.3s ease;
        }

        .puzzle-tile-item:hover .puzzle-tile-img {
            transform: scale(1.08);
        }

        .puzzle-tile-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 14px 4px 4px 4px;
            background: linear-gradient(to top, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0) 100%);
            color: #ffffff;
            font-size: 11px;
            font-weight: 600;
            text-align: center;
            text-shadow: 0 1px 2px rgba(0,0,0,0.9);
            pointer-events: none;
            line-height: 1.2;
        }

        .puzzle-check-badge {
            position: absolute;
            top: 5px;
            right: 5px;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.95);
            border: 1.5px solid #94a3b8;
            color: transparent;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            font-weight: 900;
            transition: all 0.15s ease;
            z-index: 2;
        }

        .puzzle-tile-item.selected .puzzle-check-badge {
            background: #0284c7;
            border-color: #0284c7;
            color: #ffffff;
            transform: scale(1.1);
        }

        .puzzle-footer-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 10px;
            font-size: 12px;
            color: #64748b;
        }

        .puzzle-action-link {
            color: #0066c0;
            text-decoration: none;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            cursor: pointer;
        }
        .puzzle-action-link:hover {
            color: #c45500;
            text-decoration: underline;
        }

        /* 6-Digit OTP Box */
        .otp-inputs-wrapper {
            display: flex;
            gap: 8px;
            justify-content: center;
            margin: 14px 0;
        }

        .otp-digit-field {
            width: 44px;
            height: 48px;
            text-align: center;
            font-size: 20px;
            font-weight: 700;
            border: 1.5px solid #888c8c;
            border-radius: 6px;
            outline: none;
            transition: all 0.15s ease;
        }

        .otp-digit-field:focus {
            border-color: #0284c7;
            box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.35);
        }

        /* Country Code Selector */
        .country-input-row {
            display: flex;
            gap: 6px;
        }

        .country-select-dropdown {
            width: 148px;
            height: 33px;
            border: 1px solid #888c8c;
            border-radius: 4px;
            background: #ffffff;
            font-size: 12px;
            font-weight: 500;
            padding: 0 6px;
            outline: none;
            cursor: pointer;
        }

        .country-select-dropdown:focus {
            border-color: #0284c7;
        }

        /* 5. Footer */
        .auth-footer-wrap {
            width: 100%;
            margin-top: 28px;
            padding-top: 20px;
            background: linear-gradient(to bottom, rgba(0,0,0,.14), rgba(0,0,0,.03) 1px, transparent 1px);
            text-align: center;
        }

        .auth-footer-links {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 24px;
            margin-bottom: 10px;
            flex-wrap: wrap;
        }

        .auth-footer-link {
            font-size: 11px;
            color: #0066c0;
            text-decoration: none;
        }

        .auth-footer-link:hover {
            color: #c45500;
            text-decoration: underline;
        }

        .auth-footer-copy {
            font-size: 11px;
            color: #555555;
            margin: 0;
        }
    </style>
</head>
<body>

<div class="auth-container">
    {{-- 1. Centered Header Logo --}}
    <div class="auth-header">
        <a href="{{ url('/') }}" class="brand-link" title="Idea Homepage">
            <img src="{{ \App\Support\SiteSetting::logoUrl() ?: (\App\Support\SiteSetting::loginLogoUrl() ?: asset('images/logo.png')) }}" 
                 alt="Idea" 
                 class="brand-logo-img" 
                 onerror="this.src='{{ asset('images/logo.png') }}';">
            <span class="brand-title-text">{{ \App\Support\SiteSetting::name() ?: 'Idea' }}</span>
        </a>
    </div>

    {{-- 2. Main Box Card --}}
    <div class="auth-box">
        
        {{-- Flash / Error Alert --}}
        <div id="authAlertBox" class="alert-custom-box {{ (isset($errors) && $errors->any()) || session('error') ? '' : 'd-none' }}">
            <i class="fa-solid fa-triangle-exclamation" style="color: #c40000; font-size: 16px; margin-top: 1px;" id="alertIcon"></i>
            <div>
                <div class="fw-bold" style="color: #c40000; margin-bottom: 2px;" id="alertHeader">There was a problem</div>
                <div id="alertMessage">
                    @if(isset($errors) && $errors->any())
                        {{ $errors->first() }}
                    @elseif(session('error'))
                        {{ session('error') }}
                    @endif
                </div>
            </div>
        </div>

        @if(session('status'))
            <div class="alert-custom-box alert-success-theme">
                <i class="fa-solid fa-circle-check" style="color: #007600; font-size: 16px;"></i>
                <div>
                    <div>{{ session('status') }}</div>
                </div>
            </div>
        @endif

        {{-- ═════════════════════════════════════════════════════════════════════ --}}
        {{-- PANEL 1: SIGN IN MODE (Standard Password Login)                       --}}
        {{-- ═════════════════════════════════════════════════════════════════════ --}}
        <div class="auth-flow-panel {{ request('mode') === 'register' ? '' : 'active' }}" id="panelSignIn">
            <h1 class="auth-heading">Sign in</h1>

            <form method="POST" action="{{ route('login') }}" id="loginForm" autocomplete="on">
                @csrf
                <div style="display:none !important;" aria-hidden="true">
                    <input type="text" name="website_url_hp" tabindex="-1" autocomplete="off">
                    <input type="checkbox" name="b_check_field" tabindex="-1" autocomplete="off">
                </div>
                <input type="hidden" name="g-recaptcha-response" id="gRecaptchaResponseInput" value="">

                <div class="form-group-item">
                    <label class="form-label-custom" for="loginEmailInput">Email or mobile phone number</label>
                    <input type="text" 
                           name="email" 
                           id="loginEmailInput" 
                           class="input-text-custom" 
                           value="{{ old('email') }}" 
                           required 
                           autofocus 
                           autocomplete="username"
                           autocorrect="off" 
                           autocapitalize="none">
                </div>

                <div class="form-group-item">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 3px;">
                        <label class="form-label-custom" for="loginPasswordInput" style="margin-bottom: 0;">Password</label>
                        <a href="{{ route('password.request') }}" class="custom-link" style="font-size: 12px;">Forgot your password?</a>
                    </div>
                    <div class="pwd-field-wrap">
                        <input type="password" 
                               name="password" 
                               id="loginPasswordInput" 
                               class="input-text-custom" 
                               required 
                               autocomplete="current-password">
                        <button type="button" class="pwd-eye-btn" onclick="togglePasswordVisibility('loginPasswordInput', this)" title="Show password" aria-label="Toggle password">
                            <i class="fa-regular fa-eye"></i>
                        </button>
                    </div>
                </div>

                {{-- Google reCAPTCHA v2 (Inline when required) --}}
                <div id="captchaWrapper" class="{{ ($requiresCaptcha ?? false) ? '' : 'd-none' }}" style="margin-bottom: 14px;">
                    <div style="padding: 8px; border: 1px solid #d5d9d9; border-radius: 4px; text-align: center; background: #fcfcfc;">
                        <div id="googleRecaptchaInlineWidget" style="display: inline-block;"></div>
                        <div style="font-size: 11px; color: #555; margin-top: 4px;">Verification required</div>
                    </div>
                </div>

                <button type="submit" class="btn-action-primary" id="loginSubmitBtn">
                    <span>Sign in</span>
                </button>

                <div class="legal-notice-text">
                    By continuing, you agree to Idea's <a href="{{ route('terms') }}" class="custom-link">Conditions of Use</a> and <a href="{{ route('privacy') }}" class="custom-link">Privacy Notice</a>.
                </div>

                <label class="checkbox-row-custom" for="remember">
                    <input type="checkbox" name="remember" id="remember" value="1" @checked(old('remember'))>
                    <span>Keep me signed in</span>
                </label>

                <a href="javascript:void(0)" class="help-expander-toggle" onclick="toggleHelpExpander()">
                    <i class="fa-solid fa-caret-right" id="helpExpanderIcon"></i>
                    <span>Need help?</span>
                </a>
                <div class="help-expander-panel" id="helpExpanderPanel">
                    <div style="margin-top: 4px;"><a href="{{ route('password.request') }}" class="custom-link">Forgot your password?</a></div>
                    <div style="margin-top: 4px;"><a href="{{ route('contact') }}" class="custom-link">Other issues with Sign-In</a></div>
                </div>
            </form>
        </div>

        {{-- ═════════════════════════════════════════════════════════════════════ --}}
        {{-- PANEL 2: CREATE ACCOUNT (Step 1 of Multi-Step Registration)           --}}
        {{-- ═════════════════════════════════════════════════════════════════════ --}}
        <div class="auth-flow-panel {{ request('mode') === 'register' ? 'active' : '' }}" id="panelCreateAccount">
            <h1 class="auth-heading">Create account</h1>

            <form id="createAccountForm" onsubmit="event.preventDefault(); proceedToPuzzleChallenge();">
                <div class="form-group-item">
                    <label class="form-label-custom" for="regNameInput">Your name</label>
                    <input type="text" id="regNameInput" class="input-text-custom" placeholder="First and last name" required>
                </div>

                <div class="form-group-item">
                    <label class="form-label-custom" for="regIdentifierInput">Mobile number or email</label>
                    <input type="text" id="regIdentifierInput" class="input-text-custom" placeholder="Phone or email" required>
                </div>

                <div class="form-group-item">
                    <label class="form-label-custom" for="regPasswordInput">Password</label>
                    <div class="pwd-field-wrap">
                        <input type="password" id="regPasswordInput" class="input-text-custom" placeholder="At least 8 characters" minlength="8" required>
                        <button type="button" class="pwd-eye-btn" onclick="togglePasswordVisibility('regPasswordInput', this)" title="Show password" aria-label="Toggle password">
                            <i class="fa-regular fa-eye"></i>
                        </button>
                    </div>
                    <div style="font-size: 11.5px; color: #565959; margin-top: 3px;">
                        <i class="fa-solid fa-info-circle me-0.5"></i> Passwords must be at least 8 characters.
                    </div>
                </div>

                <div class="form-group-item">
                    <label class="form-label-custom" for="regPasswordConfirmInput">Re-enter password</label>
                    <div class="pwd-field-wrap">
                        <input type="password" id="regPasswordConfirmInput" class="input-text-custom" required>
                        <button type="button" class="pwd-eye-btn" onclick="togglePasswordVisibility('regPasswordConfirmInput', this)" title="Show password" aria-label="Toggle password">
                            <i class="fa-regular fa-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-action-primary" id="btnContinueReg">
                    <span>Continue</span>
                </button>

                <div class="legal-notice-text">
                    By creating an account, you agree to Idea's <a href="{{ route('terms') }}" class="custom-link">Conditions of Use</a> and <a href="{{ route('privacy') }}" class="custom-link">Privacy Notice</a>.
                </div>

                <div style="margin-top: 14px; padding-top: 10px; border-top: 1px solid #e7e7e7; font-size: 13px;">
                    Already a customer? 
                    <a href="javascript:void(0)" class="custom-link fw-bold" onclick="switchAuthMode('signin')">Sign in &rsaquo;</a>
                </div>
            </form>
        </div>

        {{-- ═════════════════════════════════════════════════════════════════════ --}}
        {{-- PANEL 3: SOLVE THIS PUZZLE TO PROTECT YOUR ACCOUNT                   --}}
        {{-- ═════════════════════════════════════════════════════════════════════ --}}
        <div class="auth-flow-panel" id="panelPuzzle">
            <h1 class="auth-heading" style="font-size: 22px;">Solve this puzzle to protect your account</h1>
            
            <div class="puzzle-instruction-bar">
                <div class="puzzle-clue-badge">
                    <span class="puzzle-clue-icon"><i class="fa-solid fa-camera" id="puzzleClueIcon"></i></span>
                    <span>Pick: <strong id="targetPuzzleItemName" class="text-dark">Books or E-Reader</strong></span>
                </div>
                <span class="badge bg-light text-muted border font-monospace" id="puzzleStepCounter" style="font-size: 11px;">1 of 1</span>
            </div>

            <div class="puzzle-grid-box" id="puzzleTilesGrid">
                <!-- Dynamically generated digital photographic cards -->
            </div>

            <button type="button" class="btn-action-primary" id="btnVerifyPuzzle" onclick="verifyPuzzleSolution()">
                <span>Verify & Continue</span>
            </button>

            <div class="puzzle-footer-bar">
                <a href="javascript:void(0)" class="puzzle-action-link" onclick="playAudioPuzzlePrompt()" title="Listen to challenge">
                    <i class="fa-solid fa-volume-high"></i> Audio
                </a>
                <a href="javascript:void(0)" class="puzzle-action-link" onclick="generateFreshPuzzle()" title="Try another set">
                    <i class="fa-solid fa-arrows-rotate"></i> Try different images
                </a>
            </div>
        </div>

        {{-- ═════════════════════════════════════════════════════════════════════ --}}
        {{-- PANEL 4: EMAIL VERIFY OTP                                            --}}
        {{-- ═════════════════════════════════════════════════════════════════════ --}}
        <div class="auth-flow-panel" id="panelEmailOtp">
            <h1 class="auth-heading" style="font-size: 24px;">Verify email address</h1>
            <p style="font-size: 13px; color: #565959; margin-bottom: 14px;">
                To verify your email, we've sent a One Time Password (OTP) to <strong id="displayEmailTarget" class="text-dark">user@example.com</strong> 
                (<a href="javascript:void(0)" class="custom-link" onclick="switchAuthMode('register')">Change</a>)
            </p>

            <div class="form-group-item">
                <label class="form-label-custom">Enter OTP</label>
                <div class="otp-inputs-wrapper">
                    <input type="text" maxlength="1" class="otp-digit-field" id="eOtp1" oninput="otpKeyNav(this, 'eOtp2')">
                    <input type="text" maxlength="1" class="otp-digit-field" id="eOtp2" oninput="otpKeyNav(this, 'eOtp3', 'eOtp1')">
                    <input type="text" maxlength="1" class="otp-digit-field" id="eOtp3" oninput="otpKeyNav(this, 'eOtp4', 'eOtp2')">
                    <input type="text" maxlength="1" class="otp-digit-field" id="eOtp4" oninput="otpKeyNav(this, 'eOtp5', 'eOtp3')">
                    <input type="text" maxlength="1" class="otp-digit-field" id="eOtp5" oninput="otpKeyNav(this, 'eOtp6', 'eOtp4')">
                    <input type="text" maxlength="1" class="otp-digit-field" id="eOtp6" oninput="otpKeyNav(this, null, 'eOtp5')">
                </div>
            </div>

            <button type="button" class="btn-action-primary" id="btnVerifyEmailOtp" onclick="verifyEmailOtpAndProceed()">
                <span>Create your Idea account</span>
            </button>

            <div style="margin-top: 14px; text-align: center; font-size: 12.5px;">
                <span id="emailOtpTimerText" class="text-muted">Resend code in 00:<span id="emailCountdownSec">45</span></span>
                <a href="javascript:void(0)" id="resendEmailOtpLink" class="custom-link fw-semibold d-none" onclick="resendEmailVerificationCode()">Resend OTP</a>
            </div>
        </div>

        {{-- ═════════════════════════════════════════════════════════════════════ --}}
        {{-- PANEL 5: ADD MOBILE NUMBER & MOBILE OTP VERIFY                       --}}
        {{-- ═════════════════════════════════════════════════════════════════════ --}}
        <div class="auth-flow-panel" id="panelAddMobile">
            <h1 class="auth-heading" style="font-size: 24px;">Add mobile number</h1>
            <p style="font-size: 13px; color: #565959; margin-bottom: 14px;">
                To secure your account and track orders, add your mobile number.
            </p>

            <div class="form-group-item">
                <label class="form-label-custom">Mobile number</label>
                <div class="country-input-row">
                    <select id="mobileCountryCode" class="country-select-dropdown" aria-label="Country Dial Code">
                        <option value="+880" selected>Bangladesh (+880)</option>
                        <option value="+93">Afghanistan (+93)</option>
                        <option value="+355">Albania (+355)</option>
                        <option value="+213">Algeria (+213)</option>
                        <option value="+376">Andorra (+376)</option>
                        <option value="+244">Angola (+244)</option>
                        <option value="+54">Argentina (+54)</option>
                        <option value="+374">Armenia (+374)</option>
                        <option value="+61">Australia (+61)</option>
                        <option value="+43">Austria (+43)</option>
                        <option value="+994">Azerbaijan (+994)</option>
                        <option value="+973">Bahrain (+973)</option>
                        <option value="+375">Belarus (+375)</option>
                        <option value="+32">Belgium (+32)</option>
                        <option value="+501">Belize (+501)</option>
                        <option value="+229">Benin (+229)</option>
                        <option value="+975">Bhutan (+975)</option>
                        <option value="+591">Bolivia (+591)</option>
                        <option value="+387">Bosnia & Herzegovina (+387)</option>
                        <option value="+267">Botswana (+267)</option>
                        <option value="+55">Brazil (+55)</option>
                        <option value="+673">Brunei (+673)</option>
                        <option value="+359">Bulgaria (+359)</option>
                        <option value="+226">Burkina Faso (+226)</option>
                        <option value="+257">Burundi (+257)</option>
                        <option value="+855">Cambodia (+855)</option>
                        <option value="+237">Cameroon (+237)</option>
                        <option value="+1">Canada (+1)</option>
                        <option value="+56">Chile (+56)</option>
                        <option value="+86">China (+86)</option>
                        <option value="+57">Colombia (+57)</option>
                        <option value="+506">Costa Rica (+506)</option>
                        <option value="+385">Croatia (+385)</option>
                        <option value="+53">Cuba (+53)</option>
                        <option value="+357">Cyprus (+357)</option>
                        <option value="+420">Czech Republic (+420)</option>
                        <option value="+45">Denmark (+45)</option>
                        <option value="+253">Djibouti (+253)</option>
                        <option value="+593">Ecuador (+593)</option>
                        <option value="+20">Egypt (+20)</option>
                        <option value="+503">El Salvador (+503)</option>
                        <option value="+372">Estonia (+372)</option>
                        <option value="+251">Ethiopia (+251)</option>
                        <option value="+679">Fiji (+679)</option>
                        <option value="+358">Finland (+358)</option>
                        <option value="+33">France (+33)</option>
                        <option value="+995">Georgia (+995)</option>
                        <option value="+49">Germany (+49)</option>
                        <option value="+233">Ghana (+233)</option>
                        <option value="+30">Greece (+30)</option>
                        <option value="+502">Guatemala (+502)</option>
                        <option value="+592">Guyana (+592)</option>
                        <option value="+509">Haiti (+509)</option>
                        <option value="+504">Honduras (+504)</option>
                        <option value="+852">Hong Kong (+852)</option>
                        <option value="+36">Hungary (+36)</option>
                        <option value="+354">Iceland (+354)</option>
                        <option value="+91">India (+91)</option>
                        <option value="+62">Indonesia (+62)</option>
                        <option value="+98">Iran (+98)</option>
                        <option value="+964">Iraq (+964)</option>
                        <option value="+353">Ireland (+353)</option>
                        <option value="+972">Israel (+972)</option>
                        <option value="+39">Italy (+39)</option>
                        <option value="+1876">Jamaica (+1876)</option>
                        <option value="+81">Japan (+81)</option>
                        <option value="+962">Jordan (+962)</option>
                        <option value="+7">Kazakhstan (+7)</option>
                        <option value="+254">Kenya (+254)</option>
                        <option value="+965">Kuwait (+965)</option>
                        <option value="+996">Kyrgyzstan (+996)</option>
                        <option value="+856">Laos (+856)</option>
                        <option value="+371">Latvia (+371)</option>
                        <option value="+961">Lebanon (+961)</option>
                        <option value="+218">Libya (+218)</option>
                        <option value="+370">Lithuania (+370)</option>
                        <option value="+352">Luxembourg (+352)</option>
                        <option value="+853">Macau (+853)</option>
                        <option value="+261">Madagascar (+261)</option>
                        <option value="+60">Malaysia (+60)</option>
                        <option value="+960">Maldives (+960)</option>
                        <option value="+223">Mali (+223)</option>
                        <option value="+356">Malta (+356)</option>
                        <option value="+230">Mauritius (+230)</option>
                        <option value="+52">Mexico (+52)</option>
                        <option value="+373">Moldova (+373)</option>
                        <option value="+377">Monaco (+377)</option>
                        <option value="+976">Mongolia (+976)</option>
                        <option value="+382">Montenegro (+382)</option>
                        <option value="+212">Morocco (+212)</option>
                        <option value="+95">Myanmar (+95)</option>
                        <option value="+977">Nepal (+977)</option>
                        <option value="+31">Netherlands (+31)</option>
                        <option value="+64">New Zealand (+64)</option>
                        <option value="+234">Nigeria (+234)</option>
                        <option value="+389">North Macedonia (+389)</option>
                        <option value="+47">Norway (+47)</option>
                        <option value="+968">Oman (+968)</option>
                        <option value="+92">Pakistan (+92)</option>
                        <option value="+970">Palestine (+970)</option>
                        <option value="+507">Panama (+507)</option>
                        <option value="+595">Paraguay (+595)</option>
                        <option value="+51">Peru (+51)</option>
                        <option value="+63">Philippines (+63)</option>
                        <option value="+48">Poland (+48)</option>
                        <option value="+351">Portugal (+351)</option>
                        <option value="+974">Qatar (+974)</option>
                        <option value="+40">Romania (+40)</option>
                        <option value="+7">Russia (+7)</option>
                        <option value="+250">Rwanda (+250)</option>
                        <option value="+966">Saudi Arabia (+966)</option>
                        <option value="+221">Senegal (+221)</option>
                        <option value="+381">Serbia (+381)</option>
                        <option value="+65">Singapore (+65)</option>
                        <option value="+421">Slovakia (+421)</option>
                        <option value="+386">Slovenia (+386)</option>
                        <option value="+252">Somalia (+252)</option>
                        <option value="+27">South Africa (+27)</option>
                        <option value="+82">South Korea (+82)</option>
                        <option value="+34">Spain (+34)</option>
                        <option value="+94">Sri Lanka (+94)</option>
                        <option value="+249">Sudan (+249)</option>
                        <option value="+46">Sweden (+46)</option>
                        <option value="+41">Switzerland (+41)</option>
                        <option value="+963">Syria (+963)</option>
                        <option value="+886">Taiwan (+886)</option>
                        <option value="+992">Tajikistan (+992)</option>
                        <option value="+255">Tanzania (+255)</option>
                        <option value="+66">Thailand (+66)</option>
                        <option value="+216">Tunisia (+216)</option>
                        <option value="+90">Turkey (+90)</option>
                        <option value="+256">Uganda (+256)</option>
                        <option value="+380">Ukraine (+380)</option>
                        <option value="+971">United Arab Emirates (+971)</option>
                        <option value="+44">United Kingdom (+44)</option>
                        <option value="+1">United States (+1)</option>
                        <option value="+598">Uruguay (+598)</option>
                        <option value="+998">Uzbekistan (+998)</option>
                        <option value="+39">Vatican City (+39)</option>
                        <option value="+58">Venezuela (+58)</option>
                        <option value="+84">Vietnam (+84)</option>
                        <option value="+967">Yemen (+967)</option>
                        <option value="+260">Zambia (+260)</option>
                        <option value="+263">Zimbabwe (+263)</option>
                    </select>
                    <input type="tel" id="mobileNumberInput" class="input-text-custom flex-grow-1" placeholder="Mobile number">
                </div>
            </div>

            <button type="button" class="btn-action-primary" id="btnSendMobileOtp" onclick="sendMobileVerificationOtp()">
                <span>Verify mobile number</span>
            </button>

            <!-- Inline Mobile OTP sub-step -->
            <div id="mobileOtpVerifyBox" class="d-none mt-3 pt-3 border-top">
                <label class="form-label-custom">Enter 6-digit Mobile OTP</label>
                <div class="otp-inputs-wrapper">
                    <input type="text" maxlength="1" class="otp-digit-field" id="mOtp1" oninput="otpKeyNav(this, 'mOtp2')">
                    <input type="text" maxlength="1" class="otp-digit-field" id="mOtp2" oninput="otpKeyNav(this, 'mOtp3', 'mOtp1')">
                    <input type="text" maxlength="1" class="otp-digit-field" id="mOtp3" oninput="otpKeyNav(this, 'mOtp4', 'mOtp2')">
                    <input type="text" maxlength="1" class="otp-digit-field" id="mOtp4" oninput="otpKeyNav(this, 'mOtp5', 'mOtp3')">
                    <input type="text" maxlength="1" class="otp-digit-field" id="mOtp5" oninput="otpKeyNav(this, 'mOtp6', 'mOtp4')">
                    <input type="text" maxlength="1" class="otp-digit-field" id="mOtp6" oninput="otpKeyNav(this, null, 'mOtp5')">
                </div>

                <button type="button" class="btn-action-primary" onclick="verifyMobileOtpAndGoToCategory()">
                    <span>Continue to Account Category</span>
                </button>
            </div>
        </div>

        {{-- ═════════════════════════════════════════════════════════════════════ --}}
        {{-- PANEL 6: ACCOUNT CATEGORY & ADDRESS                                   --}}
        {{-- ═════════════════════════════════════════════════════════════════════ --}}
        <div class="auth-flow-panel" id="panelCategoryAddress">
            <h1 class="auth-heading" style="font-size: 22px;">Account category & address</h1>
            <p style="font-size: 13px; color: #565959; margin-bottom: 14px;">
                Select your account type and provide your postal address.
            </p>

            <div class="form-group-item">
                <label class="form-label-custom" for="regCategorySelect">Account Category <span style="color: #c40000;">*</span></label>
                <select id="regCategorySelect" class="input-text-custom" style="height: 38px; font-weight: 600; cursor: pointer;" required onchange="onRegistrationCategoryChange(this.value)">
                    <option value="" disabled selected>Select Account Category *</option>
                    <option value="buyer">1. Buyer / Reader</option>
                    <option value="author">2. Author</option>
                    <option value="publisher">3. Publisher</option>
                    <option value="seller">4. Seller</option>
                </select>
            </div>

            <!-- Dynamic Author Identity Group -->
            <div id="authorIdentityGroup" class="d-none">
                <div class="form-group-item">
                    <label class="form-label-custom" for="regAuthorNameInput">Author Name <span style="color: #c40000;">*</span></label>
                    <input type="text" id="regAuthorNameInput" class="input-text-custom" placeholder="Author / Pen name as published on books">
                    <div style="font-size: 11px; color: #565959; margin-top: 2px;">
                        This author name connects to Author Directory, Idea Potro, eBooks, Bookshop, and Homepage.
                    </div>
                </div>
                <div class="form-group-item">
                    <label class="form-label-custom" for="regAuthorNameEnInput">Author Name (English) <span style="font-size: 11px; color: #565959; font-weight: normal;">(Optional if different)</span></label>
                    <input type="text" id="regAuthorNameEnInput" class="input-text-custom" placeholder="e.g. Humayun Ahmed">
                </div>
            </div>

            <!-- Dynamic Publisher Identity Group -->
            <div id="publisherIdentityGroup" class="d-none">
                <div class="form-group-item">
                    <label class="form-label-custom" for="regPublishingHouseNameInput">Publishing House Name <span style="color: #c40000;">*</span></label>
                    <input type="text" id="regPublishingHouseNameInput" class="input-text-custom" placeholder="e.g. Idea Prokashon">
                    <div style="font-size: 11px; color: #565959; margin-top: 2px;">
                        This publishing house connects to Publisher Directory, Homepage, Bookshop, eBooks, and Invoices.
                    </div>
                </div>
                <div class="form-group-item">
                    <label class="form-label-custom" for="regPublisherOwnerNameInput">Publisher Name <span style="color: #c40000;">*</span></label>
                    <input type="text" id="regPublisherOwnerNameInput" class="input-text-custom" placeholder="Name of Publisher / Owner / Representative">
                </div>
            </div>

            <!-- Dynamic Seller Identity Group -->
            <div id="sellerIdentityGroup" class="d-none">
                <div class="form-group-item">
                    <label class="form-label-custom" for="regShopNameInput">Bookshop / Store Name <span style="color: #c40000;">*</span></label>
                    <input type="text" id="regShopNameInput" class="input-text-custom" placeholder="e.g. Dhaka Book Corner">
                </div>
            </div>

            <div class="form-group-item">
                <label class="form-label-custom" for="regCountrySelect">Country</label>
                <select id="regCountrySelect" class="input-text-custom" style="height: 36px;" onchange="onRegistrationCountryChange(this.value)">
                    <option value="Bangladesh" selected>Bangladesh</option>
                    <option value="Afghanistan">Afghanistan</option>
                    <option value="Albania">Albania</option>
                    <option value="Algeria">Algeria</option>
                    <option value="Argentina">Argentina</option>
                    <option value="Australia">Australia</option>
                    <option value="Austria">Austria</option>
                    <option value="Bahrain">Bahrain</option>
                    <option value="Belgium">Belgium</option>
                    <option value="Bhutan">Bhutan</option>
                    <option value="Brazil">Brazil</option>
                    <option value="Brunei">Brunei</option>
                    <option value="Canada">Canada</option>
                    <option value="Chile">Chile</option>
                    <option value="China">China</option>
                    <option value="Colombia">Colombia</option>
                    <option value="Cyprus">Cyprus</option>
                    <option value="Czech Republic">Czech Republic</option>
                    <option value="Denmark">Denmark</option>
                    <option value="Egypt">Egypt</option>
                    <option value="Finland">Finland</option>
                    <option value="France">France</option>
                    <option value="Germany">Germany</option>
                    <option value="Greece">Greece</option>
                    <option value="Hong Kong">Hong Kong</option>
                    <option value="Hungary">Hungary</option>
                    <option value="India">India</option>
                    <option value="Indonesia">Indonesia</option>
                    <option value="Iran">Iran</option>
                    <option value="Iraq">Iraq</option>
                    <option value="Ireland">Ireland</option>
                    <option value="Italy">Italy</option>
                    <option value="Japan">Japan</option>
                    <option value="Jordan">Jordan</option>
                    <option value="Kuwait">Kuwait</option>
                    <option value="Lebanon">Lebanon</option>
                    <option value="Malaysia">Malaysia</option>
                    <option value="Maldives">Maldives</option>
                    <option value="Mexico">Mexico</option>
                    <option value="Morocco">Morocco</option>
                    <option value="Myanmar">Myanmar</option>
                    <option value="Nepal">Nepal</option>
                    <option value="Netherlands">Netherlands</option>
                    <option value="New Zealand">New Zealand</option>
                    <option value="Norway">Norway</option>
                    <option value="Oman">Oman</option>
                    <option value="Pakistan">Pakistan</option>
                    <option value="Palestine">Palestine</option>
                    <option value="Philippines">Philippines</option>
                    <option value="Poland">Poland</option>
                    <option value="Portugal">Portugal</option>
                    <option value="Qatar">Qatar</option>
                    <option value="Russia">Russia</option>
                    <option value="Saudi Arabia">Saudi Arabia</option>
                    <option value="Singapore">Singapore</option>
                    <option value="South Africa">South Africa</option>
                    <option value="South Korea">South Korea</option>
                    <option value="Spain">Spain</option>
                    <option value="Sri Lanka">Sri Lanka</option>
                    <option value="Sweden">Sweden</option>
                    <option value="Switzerland">Switzerland</option>
                    <option value="Thailand">Thailand</option>
                    <option value="Turkey">Turkey</option>
                    <option value="United Arab Emirates">United Arab Emirates</option>
                    <option value="United Kingdom">United Kingdom</option>
                    <option value="United States">United States</option>
                    <option value="Vietnam">Vietnam</option>
                    <option value="Other">Other Country</option>
                </select>
            </div>

            <!-- District / State Field -->
            <div class="form-group-item" id="districtDropdownGroup">
                <label class="form-label-custom" for="regDistrictSelect">District</label>
                <select id="regDistrictSelect" class="input-text-custom" style="height: 36px;" onchange="onRegistrationDistrictChange(this.value)">
                    <option value="">Select District</option>
                </select>
            </div>
            <div class="form-group-item d-none" id="districtTextGroup">
                <label class="form-label-custom" for="regDistrictTextInput">State / Province / District</label>
                <input type="text" id="regDistrictTextInput" class="input-text-custom" placeholder="e.g. California / Ontario">
            </div>

            <!-- Thana / City Field -->
            <div class="form-group-item" id="thanaDropdownGroup">
                <label class="form-label-custom" for="regThanaSelect">Thana / Upazila</label>
                <select id="regThanaSelect" class="input-text-custom" style="height: 36px;">
                    <option value="">Select Thana / Upazila</option>
                </select>
            </div>
            <div class="form-group-item d-none" id="thanaTextGroup">
                <label class="form-label-custom" for="regThanaTextInput">City / Suburb / Town</label>
                <input type="text" id="regThanaTextInput" class="input-text-custom" placeholder="e.g. New York / London">
            </div>

            <!-- Post Code -->
            <div class="form-group-item">
                <label class="form-label-custom" for="regPostCodeInput">Post Code</label>
                <input type="text" id="regPostCodeInput" class="input-text-custom" placeholder="Postal / Zip Code (e.g. 1205)">
            </div>

            <!-- Postal Address -->
            <div class="form-group-item">
                <label class="form-label-custom" for="regAddressInput">Postal Address</label>
                <textarea id="regAddressInput" class="input-text-custom" style="height: 65px; resize: vertical; padding: 6px 8px;" placeholder="Street address, house number, road, area..."></textarea>
            </div>

            <button type="button" class="btn-action-primary" id="btnFinishRegistration" onclick="submitCompleteUnifiedRegistration()">
                <span>Create Account</span>
            </button>
        </div>

    </div>

    {{-- 3. "New to Idea?" Section (When on Sign-In mode) --}}
    <div id="newToIdeaSection" class="{{ request('mode') === 'register' ? 'd-none' : '' }}" style="width: 100%;">
        <div class="divider-wrapper">
            <span class="divider-label">New to Idea?</span>
        </div>

        <button type="button" class="btn-action-secondary" onclick="switchAuthMode('register')">
            <span>Create your Idea account</span>
        </button>

        <div style="margin-top: 10px;">
            <a href="{{ route('register.author') }}" class="btn-action-secondary">
                <span>Create your Author account</span>
            </a>
            <a href="{{ route('register.publisher') }}" class="btn-action-secondary">
                <span>Create your Publisher account</span>
            </a>
            <a href="{{ route('register.form', 'seller') }}" class="btn-action-secondary">
                <span>Create your Seller account</span>
            </a>
        </div>
    </div>

    {{-- 4. Footer --}}
    <footer class="auth-footer-wrap">
        <div class="auth-footer-links">
            <a href="{{ route('terms') }}" class="auth-footer-link">Conditions of Use</a>
            <a href="{{ route('privacy') }}" class="auth-footer-link">Privacy Notice</a>
            <a href="{{ route('contact') }}" class="auth-footer-link">Help</a>
        </div>
        <p class="auth-footer-copy">
            &copy; 1996–{{ date('Y') }}, {{ \App\Support\SiteSetting::name() ?: 'Idea Prokashon' }}, Inc. or its affiliates
        </p>
    </footer>
</div>

<script>
let recaptchaWidgetId = null;
let isRecaptchaReady = false;
let recaptchaSiteKey = '{{ $recaptchaSiteKey ?? "6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI" }}';

// Temporary registration store in client flow
let regData = {
    name: '',
    identifier: '',
    password: '',
    phone: '',
    countryCode: '+880'
};

/**
 * Switch Auth Mode between Sign-In & Create Account
 */
function switchAuthMode(mode) {
    hideAlert();
    document.querySelectorAll('.auth-flow-panel').forEach(p => p.classList.remove('active'));
    const newSection = document.getElementById('newToIdeaSection');

    if (mode === 'register') {
        document.getElementById('panelCreateAccount').classList.add('active');
        if (newSection) newSection.classList.add('d-none');
    } else {
        document.getElementById('panelSignIn').classList.add('active');
        if (newSection) newSection.classList.remove('d-none');
    }
}

/**
 * Password Visibility Toggle
 */
function togglePasswordVisibility(inputId, btn) {
    const input = document.getElementById(inputId);
    if (!input) return;
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

/**
 * Need Help Expander
 */
function toggleHelpExpander() {
    const panel = document.getElementById('helpExpanderPanel');
    const icon = document.getElementById('helpExpanderIcon');
    if (panel.classList.contains('show')) {
        panel.classList.remove('show');
        icon.className = 'fa-solid fa-caret-right';
    } else {
        panel.classList.add('show');
        icon.className = 'fa-solid fa-caret-down';
    }
}

/**
 * Show / Hide Alerts
 */
function showAlert(msg, isSuccess = false) {
    const box = document.getElementById('authAlertBox');
    const msgEl = document.getElementById('alertMessage');
    const header = document.getElementById('alertHeader');
    const icon = document.getElementById('alertIcon');

    if (box && msgEl) {
        msgEl.textContent = msg;
        if (isSuccess) {
            box.className = 'alert-custom-box alert-success-theme';
            header.style.color = '#007600';
            header.textContent = 'Success';
            icon.className = 'fa-solid fa-circle-check';
            icon.style.color = '#007600';
        } else {
            box.className = 'alert-custom-box';
            header.style.color = '#c40000';
            header.textContent = 'There was a problem';
            icon.className = 'fa-solid fa-triangle-exclamation';
            icon.style.color = '#c40000';
        }
        box.classList.remove('d-none');
    }
}

function hideAlert() {
    const box = document.getElementById('authAlertBox');
    if (box) box.classList.add('d-none');
}

/**
 * ═════════════════════════════════════════════════════════════════════════
 * STEP 2: PUZZLE CHALLENGE GENERATOR & VERIFIER
 * ═════════════════════════════════════════════════════════════════════════
 */
/**
 * ═════════════════════════════════════════════════════════════════════════
 * STEP 2: DIGITAL PHOTOGRAPHIC PUZZLE CHALLENGE ENGINE
 * ═════════════════════════════════════════════════════════════════════════
 */
const PUZZLE_SETS = [
    {
        targetName: 'Books or E-Reader',
        targetIcon: 'fa-book-open',
        items: [
            { name: 'Hardcover Book', image: 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=350&q=80', match: true },
            { name: 'Hot Coffee', image: 'https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?w=350&q=80', match: false },
            { name: 'Headphones', image: 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=350&q=80', match: false },
            { name: 'Open Novel', image: 'https://images.unsplash.com/photo-1512820790803-83ca734da794?w=350&q=80', match: true },
            { name: 'Digital Camera', image: 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?w=350&q=80', match: false },
            { name: 'Library Shelf', image: 'https://images.unsplash.com/photo-1497633762265-9d179a990aa6?w=350&q=80', match: true }
        ]
    },
    {
        targetName: 'Headphones or Audio Device',
        targetIcon: 'fa-headphones',
        items: [
            { name: 'Studio Headset', image: 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=350&q=80', match: true },
            { name: 'Green Plant', image: 'https://images.unsplash.com/photo-1485955900006-10f4d324d411?w=350&q=80', match: false },
            { name: 'Audio Headphones', image: 'https://images.unsplash.com/photo-1484704849700-f032a568e944?w=350&q=80', match: true },
            { name: 'Classic Camera', image: 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?w=350&q=80', match: false },
            { name: 'Wristwatch', image: 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=350&q=80', match: false },
            { name: 'Wireless Earbuds', image: 'https://images.unsplash.com/photo-1590658268037-6bf12165a8df?w=350&q=80', match: true }
        ]
    },
    {
        targetName: 'Coffee Mug or Cup',
        targetIcon: 'fa-mug-hot',
        items: [
            { name: 'Ceramic Coffee Mug', image: 'https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?w=350&q=80', match: true },
            { name: 'Laptop Computer', image: 'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?w=350&q=80', match: false },
            { name: 'Espresso Cup', image: 'https://images.unsplash.com/photo-1511920170033-f8396924c348?w=350&q=80', match: true },
            { name: 'Bicycle', image: 'https://images.unsplash.com/photo-1485965120184-e220f721d03e?w=350&q=80', match: false },
            { name: 'Morning Tea Cup', image: 'https://images.unsplash.com/photo-1577968897966-3d4325b36b61?w=350&q=80', match: true },
            { name: 'Study Book', image: 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=350&q=80', match: false }
        ]
    },
    {
        targetName: 'Laptop or Computer Screen',
        targetIcon: 'fa-laptop',
        items: [
            { name: 'MacBook Laptop', image: 'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?w=350&q=80', match: true },
            { name: 'Potted Succulent', image: 'https://images.unsplash.com/photo-1485955900006-10f4d324d411?w=350&q=80', match: false },
            { name: 'Desktop Workspace', image: 'https://images.unsplash.com/photo-1527443224154-c4a3942d3acf?w=350&q=80', match: true },
            { name: 'Leather Bag', image: 'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?w=350&q=80', match: false },
            { name: 'Modern Laptop', image: 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?w=350&q=80', match: true },
            { name: 'Headphones', image: 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=350&q=80', match: false }
        ]
    },
    {
        targetName: 'Wristwatch or Timepiece',
        targetIcon: 'fa-clock',
        items: [
            { name: 'Classic Wristwatch', image: 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=350&q=80', match: true },
            { name: 'Coffee Cup', image: 'https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?w=350&q=80', match: false },
            { name: 'Luxury Chronograph', image: 'https://images.unsplash.com/photo-1524805444758-089113d48a6d?w=350&q=80', match: true },
            { name: 'Sunglasses', image: 'https://images.unsplash.com/photo-1511499767150-a48a237f0083?w=350&q=80', match: false },
            { name: 'Smart Watch', image: 'https://images.unsplash.com/photo-1508685096489-7aacd43bd3b1?w=350&q=80', match: true },
            { name: 'Desk Book', image: 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=350&q=80', match: false }
        ]
    },
    {
        targetName: 'Camera or Photography Gear',
        targetIcon: 'fa-camera',
        items: [
            { name: 'Vintage Camera', image: 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?w=350&q=80', match: true },
            { name: 'Headphones', image: 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=350&q=80', match: false },
            { name: 'DSLR Lens & Body', image: 'https://images.unsplash.com/photo-1502920917128-1aa500764cbd?w=350&q=80', match: true },
            { name: 'Plant Pot', image: 'https://images.unsplash.com/photo-1485955900006-10f4d324d411?w=350&q=80', match: false },
            { name: 'Film Rangefinder', image: 'https://images.unsplash.com/photo-1526170375885-4d8ecf77b99f?w=350&q=80', match: true },
            { name: 'Coffee Cup', image: 'https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?w=350&q=80', match: false }
        ]
    }
];

let currentPuzzleSet = null;
let selectedPuzzleTiles = [];

function proceedToPuzzleChallenge() {
    hideAlert();
    const name = document.getElementById('regNameInput').value.trim();
    const identifier = document.getElementById('regIdentifierInput').value.trim();
    const pwd = document.getElementById('regPasswordInput').value;
    const pwdConfirm = document.getElementById('regPasswordConfirmInput').value;

    if (!name || !identifier) {
        showAlert('Please enter your name and email or mobile number.');
        return;
    }
    if (pwd.length < 8) {
        showAlert('Passwords must be at least 8 characters.');
        return;
    }
    if (pwd !== pwdConfirm) {
        showAlert('Passwords do not match.');
        return;
    }

    regData.name = name;
    regData.identifier = identifier;
    regData.password = pwd;

    document.querySelectorAll('.auth-flow-panel').forEach(p => p.classList.remove('active'));
    document.getElementById('panelPuzzle').classList.add('active');
    generateFreshPuzzle();
}

function generateFreshPuzzle() {
    selectedPuzzleTiles = [];
    const grid = document.getElementById('puzzleTilesGrid');
    if (!grid) return;
    grid.innerHTML = '';

    // Pick random puzzle set
    currentPuzzleSet = PUZZLE_SETS[Math.floor(Math.random() * PUZZLE_SETS.length)];

    const targetLabel = document.getElementById('targetPuzzleItemName');
    const targetIcon = document.getElementById('puzzleClueIcon');
    if (targetLabel) targetLabel.textContent = currentPuzzleSet.targetName;
    if (targetIcon) targetIcon.className = 'fa-solid ' + currentPuzzleSet.targetIcon;

    // Shuffle items
    const tiles = [...currentPuzzleSet.items].sort(() => Math.random() - 0.5);

    tiles.forEach((t) => {
        const item = document.createElement('div');
        item.className = 'puzzle-tile-item';
        item.innerHTML = `
            <img src="${t.image}" alt="${t.name}" class="puzzle-tile-img" loading="lazy" onerror="this.src='https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=350&q=80'">
            <span class="puzzle-check-badge"><i class="fa-solid fa-check"></i></span>
            <div class="puzzle-tile-overlay">${t.name}</div>
        `;
        item.onclick = function() {
            item.classList.toggle('selected');
            if (item.classList.contains('selected')) {
                selectedPuzzleTiles.push(t);
            } else {
                selectedPuzzleTiles = selectedPuzzleTiles.filter(pt => pt !== t);
            }
        };
        grid.appendChild(item);
    });
}

function playAudioPuzzlePrompt() {
    if ('speechSynthesis' in window && currentPuzzleSet) {
        const text = 'Please select all images containing ' + currentPuzzleSet.targetName + ' to protect your account.';
        const utterance = new SpeechSynthesisUtterance(text);
        utterance.lang = 'en-US';
        window.speechSynthesis.speak(utterance);
    } else {
        showAlert('Audio clue: Pick all ' + (currentPuzzleSet?.targetName || 'matching items'), true);
    }
}

function verifyPuzzleSolution() {
    if (!currentPuzzleSet || selectedPuzzleTiles.length === 0) {
        showAlert('Please select the image containing ' + (currentPuzzleSet?.targetName || 'the requested item') + '.');
        return;
    }

    const hasMatch = selectedPuzzleTiles.some(t => t.match);
    const hasWrong = selectedPuzzleTiles.some(t => !t.match);

    if (!hasMatch || hasWrong) {
        showAlert('Incorrect selection. Please look closely at the photos and try again.');
        generateFreshPuzzle();
        return;
    }

    hideAlert();
    // Passed Puzzle! Move to Email / Mobile OTP
    if (regData.identifier.includes('@')) {
        document.querySelectorAll('.auth-flow-panel').forEach(p => p.classList.remove('active'));
        document.getElementById('displayEmailTarget').textContent = regData.identifier;
        document.getElementById('panelEmailOtp').classList.add('active');
        startEmailCountdown();
    } else {
        document.querySelectorAll('.auth-flow-panel').forEach(p => p.classList.remove('active'));
        const mobileInput = document.getElementById('mobileNumberInput');
        if (mobileInput) mobileInput.value = regData.identifier;
        document.getElementById('panelAddMobile').classList.add('active');
    }
}

/**
 * ═════════════════════════════════════════════════════════════════════════
 * STEP 3: EMAIL OTP VERIFICATION
 * ═════════════════════════════════════════════════════════════════════
 */
function otpKeyNav(current, nextId, prevId) {
    if (current.value.length === 1 && nextId) {
        const next = document.getElementById(nextId);
        if (next) next.focus();
    }
}

let countdownInterval = null;
function startEmailCountdown() {
    let sec = 45;
    const countEl = document.getElementById('emailCountdownSec');
    const timerText = document.getElementById('emailOtpTimerText');
    const resendLink = document.getElementById('resendEmailOtpLink');

    if (timerText) timerText.classList.remove('d-none');
    if (resendLink) resendLink.classList.add('d-none');

    if (countdownInterval) clearInterval(countdownInterval);

    countdownInterval = setInterval(() => {
        sec--;
        if (countEl) countEl.textContent = sec < 10 ? '0' + sec : sec;
        if (sec <= 0) {
            clearInterval(countdownInterval);
            if (timerText) timerText.classList.add('d-none');
            if (resendLink) resendLink.classList.remove('d-none');
        }
    }, 1000);
}

function resendEmailVerificationCode() {
    showAlert('A new 6-digit verification code has been sent to your email.', true);
    startEmailCountdown();
}

function verifyEmailOtpAndProceed() {
    let code = '';
    for (let i = 1; i <= 6; i++) {
        code += (document.getElementById('eOtp' + i)?.value || '');
    }

    if (code.length < 4) {
        showAlert('Please enter the verification code.');
        return;
    }

    hideAlert();
    // Move to Add Mobile number step
    document.querySelectorAll('.auth-flow-panel').forEach(p => p.classList.remove('active'));
    document.getElementById('panelAddMobile').classList.add('active');
}

/**
 * ═════════════════════════════════════════════════════════════════════════
 * STEP 4: MOBILE OTP & COMPLETION
 * ═════════════════════════════════════════════════════════════════════
 */
function sendMobileVerificationOtp() {
    const num = document.getElementById('mobileNumberInput').value.trim();
    if (!num) {
        showAlert('Please enter a valid mobile number.');
        return;
    }
    regData.phone = num;
    regData.countryCode = document.getElementById('mobileCountryCode').value;

    showAlert('Verification code sent to ' + regData.countryCode + ' ' + num, true);
    document.getElementById('mobileOtpVerifyBox').classList.remove('d-none');
}

const BD_DISTRICTS = [
    "Bagerhat", "Bandarban", "Barguna", "Barishal", "Bhola", "Bogura", "Brahmanbaria",
    "Chandpur", "Chattogram", "Chuadanga", "Cox's Bazar", "Cumilla", "Dhaka", "Dinajpur",
    "Faridpur", "Feni", "Gaibandha", "Gazipur", "Gopalganj", "Habiganj", "Jamalpur",
    "Jashore", "Jhalokathi", "Jhenaidah", "Joypurhat", "Khagrachhari", "Khulna", "Kishoreganj",
    "Kurigram", "Kushtia", "Lakshmipur", "Lalmonirhat", "Madaripur", "Magura", "Manikganj",
    "Meherpur", "Moulvibazar", "Munshiganj", "Mymensingh", "Naogaon", "Narail", "Narayanganj",
    "Narsingdi", "Natore", "Netrokona", "Nilphamari", "Noakhali", "Pabna", "Panchagarh",
    "Patuakhali", "Pirojpur", "Rajbari", "Rajshahi", "Rangamati", "Rangpur", "Satkhira",
    "Shariatpur", "Sherpur", "Sirajganj", "Sunamganj", "Sylhet", "Tangail", "Thakurgaon"
];

const BD_THANAS = {
    "Dhaka": ["Dhanmondi", "Gulshan", "Banani", "Mirpur", "Uttara", "Mohammadpur", "Motijheel", "Tejgaon", "Badda", "Khilgaon", "Lalbagh", "Shahbagh", "Ramna", "Paltan", "Hazaribagh", "Keraniganj", "Savar", "Dhamrai", "Ashulia", "Cantonment", "Demra", "Jatrabari", "Kadamtali", "Kafrul", "Kamrangirchar", "Khilkhet", "Kotwali", "New Market", "Pallabi", "Rampura", "Sabujbagh", "Shyampur", "Sutrapur", "Turag", "Vatara", "Wari"],
    "Gazipur": ["Gazipur Sadar", "Kaliakair", "Kapasia", "Sreepur", "Kaliganj", "Tongi"],
    "Narayanganj": ["Narayanganj Sadar", "Bandar", "Araihazar", "Rupganj", "Sonargaon", "Fatullah", "Siddhirganj"],
    "Chattogram": ["Kotwali", "Panchlaish", "Pahartali", "Double Mooring", "Halishahar", "Khulshi", "Bakalia", "Bayezid", "Chandgaon", "Patenga", "Hathazari", "Raozan", "Rangunia", "Fatikchhari", "Sitakunda", "Mirsharai", "Patiya", "Boalkhali", "Anwara", "Chandanaish", "Lohagara", "Satkania", "Banshkhali", "Sandwip", "Karnaphuli"],
    "Cox's Bazar": ["Cox's Bazar Sadar", "Chakaria", "Maheshkhali", "Teknaf", "Ukhia", "Ramu", "Pekua", "Kutubdia", "Eidgaon"],
    "Sylhet": ["Sylhet Sadar", "Beanibazar", "Golapganj", "Companiganj", "Fenchuganj", "Bishwanath", "Gowainghat", "Jaintiapur", "Kanaighat", "Zakiganj", "Dakshin Surma", "Osmani Nagar"],
    "Moulvibazar": ["Moulvibazar Sadar", "Sreemangal", "Kamalganj", "Kulaura", "Barlekha", "Juri", "Rajnagar"],
    "Habiganj": ["Habiganj Sadar", "Bahubal", "Madhabpur", "Chunarughat", "Lakhai", "Nabiganj", "Ajmiriganj", "Baniachang"],
    "Sunamganj": ["Sunamganj Sadar", "Chhatak", "Jagannathpur", "Dowarabazar", "Tahirpur", "Dharampasha", "Jamalganj", "Shantiganj", "Derai"],
    "Rajshahi": ["Boalia", "Rajpara", "Motihar", "Shah Makhdum", "Chandrima", "Kashiadanga", "Katakhali", "Paba", "Godagari", "Tanore", "Bagmara", "Durgapur", "Puthia", "Charghat", "Bagha", "Mohonpur"],
    "Bogura": ["Bogura Sadar", "Shajahanpur", "Sherpur", "Shibganj", "Kahaloo", "Nandigram", "Dupchanchia", "Adamdighi", "Gabtali", "Sonatala", "Sariakandi", "Dhunat"],
    "Khulna": ["Khulna Sadar", "Sonadanga", "Khalishpur", "Daulatpur", "Khan Jahan Ali", "Batiaghata", "Dacope", "Dumuria", "Dighalia", "Koyra", "Paikgachha", "Phultala", "Rupsha", "Terokhada"],
    "Jashore": ["Jashore Sadar", "Jhikargachha", "Sharsha", "Manirampur", "Keshabpur", "Abhaynagar", "Bagherpara", "Chaugachha", "Benapole"],
    "Barishal": ["Kotwali", "Barishal Sadar", "Bakerganj", "Babuganj", "Wazirpur", "Banaripara", "Gournadi", "Agailjhara", "Mehendiganj", "Muladi", "Hizla"],
    "Rangpur": ["Rangpur Sadar", "Kotwali", "Badarganj", "Gangachara", "Kaunia", "Mithapukur", "Pirgachha", "Pirganj", "Taraganj"],
    "Mymensingh": ["Kotwali", "Mymensingh Sadar", "Muktagachha", "Trishal", "Bhaluka", "Fulbaria", "Gafargaon", "Haluaghat", "Ishwarganj", "Dhobaura", "Nandail", "Phulpur", "Tara Khanda"],
    "Cumilla": ["Cumilla Adarsha Sadar", "Cumilla Sadar Dakshin", "Barura", "Brahmanpara", "Burichang", "Chandina", "Chauddagram", "Daudkandi", "Debidwar", "Homna", "Laksam", "Muradnagar", "Meghna", "Monohargonj", "Nangalkot", "Titas", "Lalmai"],
    "Brahmanbaria": ["Brahmanbaria Sadar", "Ashuganj", "Nasirnagar", "Nabinagar", "Sarail", "Kasba", "Akhaura", "Bancharampur", "Bijoynagar"],
    "Noakhali": ["Noakhali Sadar", "Begumganj", "Chatkhil", "Companiganj", "Hatiya", "Senbagh", "Sonaimuri", "Subarnachar", "Kabirhat"],
    "Feni": ["Feni Sadar", "Chhagalnaiya", "Daganbhuiyan", "Parshuram", "Fulgazi", "Sonagazi"],
    "Faridpur": ["Faridpur Sadar", "Boalmari", "Alfadanga", "Madhukhali", "Bhanga", "Nagarkanda", "Charbhadrasan", "Sadarpur", "Saltha"],
    "Tangail": ["Tangail Sadar", "Mirzapur", "Dhanbari", "Madhupur", "Gopalpur", "Ghatail", "Kalihati", "Sakhipur", "Basail", "Delduar", "Nagarpur", "Bhuapur"],
    "Pabna": ["Pabna Sadar", "Ishwardi", "Atgharia", "Bera", "Bhangura", "Chatmohar", "Faridpur", "Santhia", "Sujanagar"],
    "Kushtia": ["Kushtia Sadar", "Kumarkhali", "Khoksa", "Mirpur", "Bheramara", "Daulatpur"],
    "Dinajpur": ["Dinajpur Sadar", "Birganj", "Biral", "Bochaganj", "Chirirbandar", "Fulbari", "Ghoraghat", "Hakimpur", "Kaharole", "Khansama", "Nawabganj", "Parbatipur"]
};

function initDistrictDropdown() {
    const districtSelect = document.getElementById('regDistrictSelect');
    if (!districtSelect || districtSelect.options.length > 1) return;

    districtSelect.innerHTML = '<option value="">Select District</option>';
    BD_DISTRICTS.forEach(d => {
        const opt = document.createElement('option');
        opt.value = d;
        opt.textContent = d;
        districtSelect.appendChild(opt);
    });
}

function onRegistrationCountryChange(country) {
    const isBD = (country === 'Bangladesh');
    const districtDrop = document.getElementById('districtDropdownGroup');
    const districtText = document.getElementById('districtTextGroup');
    const thanaDrop = document.getElementById('thanaDropdownGroup');
    const thanaText = document.getElementById('thanaTextGroup');

    if (isBD) {
        districtDrop.classList.remove('d-none');
        districtText.classList.add('d-none');
        thanaDrop.classList.remove('d-none');
        thanaText.classList.add('d-none');
        initDistrictDropdown();
    } else {
        districtDrop.classList.add('d-none');
        districtText.classList.remove('d-none');
        thanaDrop.classList.add('d-none');
        thanaText.classList.remove('d-none');
    }
}

function onRegistrationDistrictChange(district) {
    const thanaSelect = document.getElementById('regThanaSelect');
    if (!thanaSelect) return;

    thanaSelect.innerHTML = '<option value="">Select Thana / Upazila</option>';
    if (!district) return;

    const thanas = BD_THANAS[district] || [district + " Sadar"];
    thanas.forEach(t => {
        const opt = document.createElement('option');
        opt.value = t;
        opt.textContent = t;
        thanaSelect.appendChild(opt);
    });
}

function verifyMobileOtpAndGoToCategory() {
    let code = '';
    for (let i = 1; i <= 6; i++) {
        code += (document.getElementById('mOtp' + i)?.value || '');
    }

    if (code.length < 4) {
        showAlert('Please enter the 6-digit mobile verification code.');
        return;
    }

    hideAlert();
    // Check URL params for category preset
    const urlParams = new URLSearchParams(window.location.search);
    const cat = urlParams.get('category');
    if (cat && ['buyer', 'author', 'publisher', 'seller'].includes(cat)) {
        const catSelect = document.getElementById('regCategorySelect');
        if (catSelect) catSelect.value = cat;
    }

    onRegistrationCategoryChange(document.getElementById('regCategorySelect')?.value || '');
    initDistrictDropdown();

    // Move to Account Category & Address step
    document.querySelectorAll('.auth-flow-panel').forEach(p => p.classList.remove('active'));
    document.getElementById('panelCategoryAddress').classList.add('active');
}

function onRegistrationCategoryChange(cat) {
    const authorGrp = document.getElementById('authorIdentityGroup');
    const pubGrp = document.getElementById('publisherIdentityGroup');
    const sellerGrp = document.getElementById('sellerIdentityGroup');

    if (authorGrp) authorGrp.classList.add('d-none');
    if (pubGrp) pubGrp.classList.add('d-none');
    if (sellerGrp) sellerGrp.classList.add('d-none');

    if (cat === 'author') {
        if (authorGrp) {
            authorGrp.classList.remove('d-none');
            const authorInput = document.getElementById('regAuthorNameInput');
            if (authorInput && !authorInput.value && regData.name) {
                authorInput.value = regData.name;
            }
        }
    } else if (cat === 'publisher') {
        if (pubGrp) {
            pubGrp.classList.remove('d-none');
            const pubOwnerInput = document.getElementById('regPublisherOwnerNameInput');
            if (pubOwnerInput && !pubOwnerInput.value && regData.name) {
                pubOwnerInput.value = regData.name;
            }
        }
    } else if (cat === 'seller') {
        if (sellerGrp) {
            sellerGrp.classList.remove('d-none');
            const shopInput = document.getElementById('regShopNameInput');
            if (shopInput && !shopInput.value && regData.name) {
                shopInput.value = regData.name;
            }
        }
    }
}

async function submitCompleteUnifiedRegistration() {
    const categorySelect = document.getElementById('regCategorySelect');
    const category = categorySelect ? categorySelect.value : '';

    if (!category || category === '') {
        showAlert('Please select an Account Category to proceed.');
        if (categorySelect) categorySelect.focus();
        return;
    }

    let authorName = '';
    let authorNameEn = '';
    let publishingHouseName = '';
    let publisherOwnerName = '';
    let shopName = '';

    if (category === 'author') {
        const authorInput = document.getElementById('regAuthorNameInput');
        authorName = authorInput ? authorInput.value.trim() : '';
        if (!authorName) {
            showAlert('Author Name is required for author registration.');
            if (authorInput) authorInput.focus();
            return;
        }
        authorNameEn = document.getElementById('regAuthorNameEnInput')?.value.trim() || '';
    } else if (category === 'publisher') {
        const pubHouseInput = document.getElementById('regPublishingHouseNameInput');
        publishingHouseName = pubHouseInput ? pubHouseInput.value.trim() : '';
        if (!publishingHouseName) {
            showAlert('Publishing House Name is required for publisher registration.');
            if (pubHouseInput) pubHouseInput.focus();
            return;
        }

        const pubOwnerInput = document.getElementById('regPublisherOwnerNameInput');
        publisherOwnerName = pubOwnerInput ? pubOwnerInput.value.trim() : '';
        if (!publisherOwnerName) {
            showAlert('Publisher Name is required.');
            if (pubOwnerInput) pubOwnerInput.focus();
            return;
        }
    } else if (category === 'seller') {
        const shopInput = document.getElementById('regShopNameInput');
        shopName = shopInput ? shopInput.value.trim() : '';
        if (!shopName) {
            showAlert('Bookshop / Store Name is required.');
            if (shopInput) shopInput.focus();
            return;
        }
    }

    const btn = document.getElementById('btnFinishRegistration');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Creating Account...';
    }

    const country = document.getElementById('regCountrySelect').value;
    const isBD = (country === 'Bangladesh');
    
    const district = isBD 
        ? (document.getElementById('regDistrictSelect').value.trim())
        : (document.getElementById('regDistrictTextInput').value.trim());

    const thana = isBD
        ? (document.getElementById('regThanaSelect').value.trim())
        : (document.getElementById('regThanaTextInput').value.trim());

    const postCode = document.getElementById('regPostCodeInput').value.trim();
    const address = document.getElementById('regAddressInput').value.trim();

    const formData = new FormData();
    formData.append('_token', '{{ csrf_token() }}');
    formData.append('name', regData.name);
    formData.append('email', regData.identifier.includes('@') ? regData.identifier : (regData.phone + '@buyer.ideaabd.com'));
    formData.append('phone', regData.phone || regData.identifier);
    formData.append('country_code', regData.countryCode || '+880');
    formData.append('password', regData.password);
    formData.append('category', category);
    formData.append('author_name', authorName);
    formData.append('author_name_en', authorNameEn);
    formData.append('author_name_bn', authorName);
    formData.append('publishing_house_name', publishingHouseName);
    formData.append('publisher_name', publishingHouseName);
    formData.append('publisher_owner_name', publisherOwnerName);
    formData.append('shop_name', shopName);
    formData.append('country', country);
    formData.append('district', district);
    formData.append('thana', thana);
    formData.append('post_code', postCode);
    formData.append('address', address);

    try {
        const res = await fetch('{{ route("register.complete") }}', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: formData
        });

        const data = await res.json();
        if (res.ok && data.success) {
            window.location.href = data.redirect_url || '{{ route("my-account") }}';
            return;
        }
        showAlert(data.message || 'Registration failed. Please check your information.');
    } catch(err) {
        window.location.href = '{{ route("my-account") }}';
    } finally {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<span>Create Account</span>';
        }
    }
}

/**
 * Standard Sign-in AJAX Form Handling
 */
document.addEventListener('DOMContentLoaded', function() {
    initDistrictDropdown();
    const form = document.getElementById('loginForm');
    const submitBtn = document.getElementById('loginSubmitBtn');
    const tokenInput = document.getElementById('gRecaptchaResponseInput');

    if (form) {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();

            const emailInput = document.getElementById('loginEmailInput');
            const passwordInput = document.getElementById('loginPasswordInput');

            if (!emailInput.value.trim() || !passwordInput.value) {
                showAlert('Enter your email or mobile phone number and password.');
                return;
            }

            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Signing in...';
            }

            const formData = new FormData(form);

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: formData
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    if (submitBtn) submitBtn.innerHTML = 'Success';
                    window.location.href = data.redirect || '{{ route("my-account") }}';
                    return;
                }

                // Error response
                const msg = data.message || 'We cannot find an account with that email/phone or password was incorrect.';
                showAlert(msg);

                if (data.show_captcha) {
                    const captchaWrapper = document.getElementById('captchaWrapper');
                    if (captchaWrapper) {
                        captchaWrapper.classList.remove('d-none');
                        renderGoogleRecaptcha();
                    }
                }

                if (window.grecaptcha && recaptchaWidgetId !== null) {
                    try {
                        window.grecaptcha.reset(recaptchaWidgetId);
                        if (tokenInput) tokenInput.value = '';
                    } catch(err){}
                }

            } catch (err) {
                console.error('Login error:', err);
                form.submit();
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<span>Sign in</span>';
                }
            }
        });
    }
});
</script>
</body>
</html>
