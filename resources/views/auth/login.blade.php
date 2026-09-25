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

    <!-- Kalpurush Bangla Font -->
    <link href="https://fonts.maateen.me/kalpurush/font.css" rel="stylesheet">

    <!-- Bangladesh Geodata (Districts & Upazilas) -->
    <script src="{{ asset('js/bd-geo-data.js') }}"></script>

    <style>
        /* ══════════════════════════════════════════════════════════════════
           PREMIUM SKY-BLUE INTERNATIONAL AUTHENTICATION ARCHITECTURE
           ══════════════════════════════════════════════════════════════════ */
        @font-face {
            font-family: 'Kalpurush';
            src: url('{{ asset("fonts/kalpurush/kalpurush.woff2") }}') format('woff2'),
                 url('{{ asset("fonts/kalpurush/kalpurush.ttf") }}') format('truetype');
            font-weight: normal;
            font-style: normal;
            font-display: swap;
        }

        .font-kalpurush {
            font-family: 'Kalpurush', 'SolaimanLipi', 'Hind Siliguri', sans-serif !important;
        }

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
            transition: max-width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .auth-container.register-mode {
            max-width: 530px;
        }

        @media (max-width: 576px) {
            .auth-container.register-mode {
                max-width: 100%;
                padding-left: 12px;
                padding-right: 12px;
            }
        }

        /* 1. Header Logo */
        .auth-header {
            margin-top: 12px;
            margin-bottom: 18px;
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

        /* Highly Attractive & Prominent Featured Create Account Button */
        .btn-create-account-featured {
            width: 100%;
            min-height: 44px;
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
            border: 1.5px solid #cbd5e1;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04), 0 1px 2px rgba(0, 0, 0, 0.02);
            color: #0f172a;
            font-size: 14px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
            overflow: hidden;
            padding: 8px 16px;
        }

        .btn-create-account-featured::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(2, 132, 199, 0.09), transparent);
            transition: left 0.55s ease;
        }

        .btn-create-account-featured:hover {
            background: #ffffff;
            border-color: #0284c7;
            color: #0284c7;
            box-shadow: 0 6px 18px rgba(2, 132, 199, 0.18);
            transform: translateY(-1.5px);
        }

        .btn-create-account-featured:hover::before {
            left: 100%;
        }

        .btn-create-account-featured:active {
            transform: translateY(0);
            box-shadow: 0 2px 5px rgba(2, 132, 199, 0.12);
        }

        .btn-create-account-featured .btn-icon-wrap {
            width: 28px;
            height: 28px;
            border-radius: 7px;
            background: rgba(2, 132, 199, 0.1);
            color: #0284c7;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            transition: all 0.2s ease;
            flex-shrink: 0;
        }

        .btn-create-account-featured:hover .btn-icon-wrap {
            background: #0284c7;
            color: #ffffff;
            transform: scale(1.05);
        }

        .btn-create-account-featured .btn-arrow-icon {
            font-size: 12px;
            color: #94a3b8;
            margin-left: auto;
            transition: transform 0.2s ease, color 0.2s ease;
        }

        .btn-create-account-featured:hover .btn-arrow-icon {
            color: #0284c7;
            transform: translateX(3px);
        }

        .create-account-roles-hint {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
            font-size: 11.5px;
            color: #64748b;
            font-weight: 500;
            margin-top: 8px;
            text-align: center;
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

        /* Modern Server-Side Image CAPTCHA Widget */
        .captcha-card-box {
            background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.03);
            transition: all 0.25s ease;
        }
        .captcha-card-box:hover {
            border-color: #cbd5e1;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        .captcha-img-wrapper {
            position: relative;
            background: #ffffff;
            border: 1.5px solid #cbd5e1;
            border-radius: 10px;
            padding: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 88px;
            box-shadow: inset 0 1px 3px rgba(0,0,0,0.04);
            user-select: none;
            -webkit-user-select: none;
            overflow: hidden;
            transition: all 0.2s ease;
        }
        .captcha-img-wrapper:hover {
            border-color: #0284c7;
            background: #fafafa;
        }
        .captcha-img-element {
            max-width: 100%;
            height: auto;
            display: block;
            border-radius: 6px;
            pointer-events: none;
            transition: opacity 0.2s ease, transform 0.2s ease;
        }
        .captcha-refresh-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            font-weight: 600;
            color: #0284c7;
            background: #ffffff;
            border: 1px solid #bae6fd;
            border-radius: 6px;
            padding: 5px 11px;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 1px 2px rgba(2,132,199,0.08);
            text-decoration: none;
        }
        .captcha-refresh-btn:hover {
            color: #0369a1;
            background: #f0f9ff;
            border-color: #38bdf8;
            transform: translateY(-1px);
        }
        .captcha-refresh-btn.spinning i {
            animation: spinCaptchaRefresh 0.6s linear infinite;
        }
        @keyframes spinCaptchaRefresh {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        .captcha-case-hint {
            font-size: 11.5px;
            color: #64748b;
            margin-top: 6px;
            display: flex;
            align-items: center;
            gap: 5px;
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

        /* Role Selection Cards */
        .role-select-box {
            border: 1.5px solid #cbd5e1 !important;
            background: #ffffff;
            cursor: pointer;
            transition: all 0.18s ease-in-out;
            user-select: none;
        }
        .role-select-box:hover {
            border-color: #0284c7 !important;
            background: #f8fafc;
            transform: translateY(-1px);
        }
        .role-select-box.active-role-buyer {
            border-color: #f59e0b !important;
            background: rgba(245, 158, 11, 0.08) !important;
            box-shadow: 0 0 0 2px rgba(245, 158, 11, 0.25);
        }
        .role-select-box.active-role-author {
            border-color: #10b981 !important;
            background: rgba(16, 185, 129, 0.08) !important;
            box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.25);
        }
        .role-select-box.active-role-publisher {
            border-color: #ef4444 !important;
            background: rgba(239, 68, 68, 0.08) !important;
            box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.25);
        }
        .role-select-box.active-role-seller {
            border-color: #0284c7 !important;
            background: rgba(2, 132, 199, 0.08) !important;
            box-shadow: 0 0 0 2px rgba(2, 132, 199, 0.25);
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

        /* Interactive Password Strength Meter */
        .pwd-strength-wrap {
            margin-top: 6px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 8px 10px;
        }
        .pwd-strength-bar-bg {
            height: 5px;
            background: #e2e8f0;
            border-radius: 3px;
            overflow: hidden;
            margin-bottom: 6px;
        }
        .pwd-strength-bar-fill {
            height: 100%;
            width: 0%;
            border-radius: 3px;
            transition: width 0.3s ease, background-color 0.3s ease;
        }
        .pwd-hint-text {
            font-size: 11.5px;
            color: #64748b;
            margin-top: 5px;
            line-height: 1.4;
            transition: color 0.2s ease;
        }
        .pwd-hint-text.has-missing {
            color: #dc2626;
            font-weight: 500;
        }
        .pwd-hint-text.is-valid {
            color: #16a34a;
            font-weight: 600;
        }

        /* ── Multi-Step Registration Stepper ── */
        .reg-stepper {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            margin-bottom: 20px;
            padding: 2px 6px;
        }
        .step-node {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            position: relative;
            z-index: 2;
        }
        .step-circle {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: #f1f5f9;
            color: #64748b;
            font-size: 11.5px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.25s ease;
            border: 2px solid #ffffff;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .step-text {
            font-size: 11px;
            font-weight: 600;
            color: #64748b;
            white-space: nowrap;
        }
        .step-line {
            flex: 1;
            height: 3px;
            background: #e2e8f0;
            margin: 0 4px -14px 4px;
            transition: background-color 0.3s ease;
            z-index: 1;
        }
        @keyframes activeStepPulse {
            0% { box-shadow: 0 0 0 0 rgba(2, 132, 199, 0.5); }
            70% { box-shadow: 0 0 0 7px rgba(2, 132, 199, 0); }
            100% { box-shadow: 0 0 0 0 rgba(2, 132, 199, 0); }
        }
        .step-node.active .step-circle {
            background: #0284c7;
            color: #ffffff;
            animation: activeStepPulse 2.2s infinite;
        }
        .step-node.active .step-text {
            color: #0284c7;
            font-weight: 700;
        }
        .step-node.completed {
            cursor: pointer;
        }
        .step-node.completed .step-circle {
            background: #10b981;
            color: #ffffff;
            border-color: #10b981;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .step-node.completed:hover .step-circle {
            transform: scale(1.12);
            box-shadow: 0 2px 6px rgba(16, 185, 129, 0.35);
        }
        .step-node.completed .step-text {
            color: #10b981;
        }
        .step-line.completed {
            background: #10b981;
        }

        /* ── Role Banner & Partner KYC Components ── */
        .category-indicator-banner {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            border: 1px solid #bae6fd;
            border-radius: 8px;
            padding: 10px 12px;
        }
        .badge-role-icon {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #0284c7;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            flex-shrink: 0;
        }
        .partner-kyc-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px;
        }
        .genre-chips-container {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }
        .genre-chip {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 16px;
            font-size: 11.5px;
            background: #ffffff;
            color: #334155;
            border: 1px solid #cbd5e1;
            cursor: pointer;
            user-select: none;
            transition: all 0.15s ease;
        }
        .genre-chip:hover {
            border-color: #0284c7;
            background: #f0f9ff;
            color: #0284c7;
        }
        .genre-chip.selected {
            background: #0284c7;
            color: #ffffff;
            border-color: #0369a1;
            box-shadow: 0 1px 2px rgba(2, 132, 199, 0.3);
        }
    </style>
</head>
<body>

<div class="auth-container {{ request('mode') === 'register' ? 'register-mode' : '' }}" id="authContainer">
    {{-- 1. Centered Header Logo --}}
    <div class="auth-header">
        <a href="{{ url('/') }}" class="brand-link" title="IDEA PROKASHON">
            <img src="{{ \App\Support\SiteSetting::logoUrl() ?: (\App\Support\SiteSetting::loginLogoUrl() ?: asset('images/logo.png')) }}" 
                 alt="IDEA PROKASHON" 
                 class="brand-logo-img" 
                 onerror="this.src='{{ asset('images/logo.png') }}';">
            <span class="brand-title-text">IDEA PROKASHON</span>
        </a>
    </div>

    {{-- 2. Main Box Card --}}
    <div class="auth-box">

        {{-- Multi-Step Registration Stepper --}}
        <div class="reg-stepper {{ request('mode') === 'register' ? '' : 'd-none' }}" id="regProgressStepper">
            <div class="step-node active" id="stepNode1" data-step="1" onclick="onStepNodeClick(1)" title="Step 1: Info">
                <div class="step-circle">1</div>
                <span class="step-text">Info</span>
            </div>
            <div class="step-line" id="stepLine1"></div>
            <div class="step-node" id="stepNode2" data-step="2" onclick="onStepNodeClick(2)" title="Step 2: Security">
                <div class="step-circle">2</div>
                <span class="step-text">Security</span>
            </div>
            <div class="step-line" id="stepLine2"></div>
            <div class="step-node" id="stepNode3" data-step="3" onclick="onStepNodeClick(3)" title="Step 3: Email">
                <div class="step-circle">3</div>
                <span class="step-text">Email</span>
            </div>
            <div class="step-line" id="stepLine3"></div>
            <div class="step-node" id="stepNode4" data-step="4" onclick="onStepNodeClick(4)" title="Step 4: Mobile">
                <div class="step-circle">4</div>
                <span class="step-text">Mobile</span>
            </div>
            <div class="step-line" id="stepLine4"></div>
            <div class="step-node" id="stepNode5" data-step="5" onclick="onStepNodeClick(5)" title="Step 5: Profile">
                <div class="step-circle">5</div>
                <span class="step-text">Profile</span>
            </div>
        </div>
        
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

            {{-- Category / Role Indicator Banner --}}
            <div id="categoryIndicatorBanner" class="category-indicator-banner d-none mb-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge-role-icon" id="catBannerIcon"><i class="fa-solid fa-user"></i></span>
                        <div>
                            <strong id="catBannerTitle" class="d-block" style="font-size: 13px; color: #0369a1;">Registration</strong>
                            <span id="catBannerSubtitle" style="font-size: 11px; color: #64748b;">Provide details to proceed</span>
                        </div>
                    </div>
                    <a href="{{ route('register.choose') }}" class="small text-decoration-none" style="font-size: 11.5px; color: #0284c7; font-weight: 600;">Change</a>
                </div>
            </div>

            <form id="createAccountForm" onsubmit="event.preventDefault(); proceedToCaptchaChallenge();">
                {{-- Role Dropdown (Clean, Single-Word English) --}}
                <div class="form-group-item mb-3">
                    <label class="form-label-custom mb-1.5" for="regCategoryDropdown">
                        Role <span style="color: #c40000; font-weight: bold;">*</span>
                    </label>
                    <div class="position-relative">
                        <select id="regCategoryDropdown" name="category" class="input-text-custom" style="height: 40px; font-weight: 600; cursor: pointer; padding-left: 12px; font-size: 13.5px; border-color: #cbd5e1;" onchange="onRegistrationTypeDropdownChange(this.value)" required>
                            <option value="buyer" selected>Customer</option>
                            <option value="author">Author</option>
                            <option value="publisher">Publisher</option>
                            <option value="seller">Seller</option>
                        </select>
                    </div>
                </div>

                <div class="form-group-item">
                    <label class="form-label-custom" for="regNameInput">Your name <span style="color: #c40000; font-weight: bold;">*</span></label>
                    <input type="text" id="regNameInput" class="input-text-custom" placeholder="First and last name" required>
                </div>

                <div class="form-group-item">
                    <label class="form-label-custom" for="regPhoneInput">Mobile number <span style="color: #c40000; font-weight: bold;">*</span></label>
                    <div class="country-input-row">
                        <select class="country-select-dropdown" id="regCountryCodeSelect" aria-label="Country code">
                            <option value="+880" selected>BD +880</option>
                            <option value="+1">US +1</option>
                            <option value="+44">UK +44</option>
                            <option value="+91">IN +91</option>
                            <option value="+971">AE +971</option>
                            <option value="+966">SA +966</option>
                            <option value="+60">MY +60</option>
                            <option value="+65">SG +65</option>
                            <option value="+61">AU +61</option>
                            <option value="+1">CA +1</option>
                            <option value="+39">IT +39</option>
                            <option value="+49">DE +49</option>
                            <option value="+33">FR +33</option>
                            <option value="+81">JP +81</option>
                            <option value="+82">KR +82</option>
                            <option value="+974">QA +974</option>
                            <option value="+968">OM +968</option>
                            <option value="+965">KW +965</option>
                            <option value="+973">BH +973</option>
                            <option value="+92">PK +92</option>
                            <option value="+977">NP +977</option>
                            <option value="+94">LK +94</option>
                        </select>
                        <input type="tel" id="regPhoneInput" class="input-text-custom flex-grow-1" placeholder="Mobile number (e.g. 01712345678)" required>
                    </div>
                </div>

                <div class="form-group-item">
                    <label class="form-label-custom" for="regEmailInput">Email <span style="color: #c40000; font-weight: bold;">*</span></label>
                    <input type="email" id="regEmailInput" class="input-text-custom" placeholder="Email address (e.g. name@example.com)" required>
                </div>

                <div class="form-group-item">
                    <label class="form-label-custom" for="regPasswordInput">Password <span style="color: #c40000; font-weight: bold;">*</span></label>
                    <div class="pwd-field-wrap">
                        <input type="password" id="regPasswordInput" class="input-text-custom" placeholder="At least 8 characters" minlength="8" maxlength="30" required oninput="evaluatePasswordStrength(this.value)">
                        <button type="button" class="pwd-eye-btn" onclick="togglePasswordVisibility('regPasswordInput', this)" title="Show password" aria-label="Toggle password">
                            <i class="fa-regular fa-eye"></i>
                        </button>
                    </div>

                    {{-- Live Password Strength Meter --}}
                    <div class="pwd-strength-wrap" id="pwdStrengthWrapper">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span style="font-size: 11px; color: #565959;">Strength:</span>
                            <span id="pwdStrengthLabel" style="font-size: 11px; font-weight: 700; color: #dc2626;">Too Weak</span>
                        </div>
                        <div class="pwd-strength-bar-bg">
                            <div class="pwd-strength-bar-fill" id="pwdStrengthBar"></div>
                        </div>
                        <div id="pwdHintMsg" class="pwd-hint-text d-none"></div>
                    </div>
                </div>

                <div class="form-group-item">
                    <label class="form-label-custom" for="regPasswordConfirmInput">Re-enter password <span style="color: #c40000; font-weight: bold;">*</span></label>
                    <div class="pwd-field-wrap">
                        <input type="password" id="regPasswordConfirmInput" class="input-text-custom" placeholder="Re-enter password" maxlength="30" required>
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
        {{-- PANEL 3: SECURITY VERIFICATION (IMAGE CAPTCHA)                        --}}
        {{-- ═════════════════════════════════════════════════════════════════════ --}}
        <div class="auth-flow-panel" id="panelCaptcha">
            <h1 class="auth-heading" style="font-size: 22px;">Security Verification</h1>
            <p style="font-size: 13px; color: #565959; margin-bottom: 12px;">
                Enter the characters shown in the image below to protect your account.
            </p>

            <div class="captcha-card-box">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="fw-bold text-dark" style="font-size: 12px;">
                        <i class="fa-solid fa-shield-halved text-primary me-1"></i> CAPTCHA IMAGE
                    </span>
                    <button type="button" class="captcha-refresh-btn" id="btnRefreshCaptcha" onclick="refreshCaptchaChallenge()" title="Refresh CAPTCHA">
                        <i class="fa-solid fa-arrows-rotate"></i>
                        <span>Refresh CAPTCHA</span>
                    </button>
                </div>

                <div class="captcha-img-wrapper" id="captchaImageContainer" onclick="refreshCaptchaChallenge()" title="Click image to reload new CAPTCHA" style="cursor: pointer;">
                    <div class="spinner-border spinner-border-sm text-primary" role="status" id="captchaLoadingSpinner">
                        <span class="visually-hidden">Loading CAPTCHA...</span>
                    </div>
                    <img src="" alt="CAPTCHA Code" id="captchaImageElement" class="captcha-img-element d-none" draggable="false">
                </div>
                <input type="hidden" id="captchaTokenInput" value="">
            </div>

            <form id="captchaVerificationForm" onsubmit="event.preventDefault(); verifyCaptchaSolution();">
                <div class="form-group-item">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label-custom mb-0" for="captchaCodeInput">Enter the CAPTCHA:</label>
                        <span id="captchaCharCount" class="badge bg-light text-secondary border font-monospace" style="font-size: 11px;">0 / 4</span>
                    </div>
                    <input type="text" id="captchaCodeInput" class="input-text-custom font-monospace text-uppercase" placeholder="Type characters here" maxlength="8" autocomplete="off" autocorrect="off" autocapitalize="characters" spellcheck="false" required style="letter-spacing: 4px; font-weight: 700; font-size: 16px; text-transform: uppercase;" oninput="onCaptchaInput(this)">
                    <div class="captcha-case-hint">
                        <i class="fa-solid fa-circle-info text-secondary"></i>
                        <span>Letters and numbers are <strong>not case-sensitive</strong> (A-Z, 0-9).</span>
                    </div>
                </div>

                <button type="submit" class="btn-action-primary" id="btnVerifyCaptcha">
                    <span id="btnVerifyCaptchaText">Verify & Continue</span>
                </button>
            </form>

            <div style="margin-top: 14px; padding-top: 10px; border-top: 1px solid #e7e7e7; font-size: 13px; text-align: center;">
                <a href="javascript:void(0)" class="custom-link" onclick="goToStep(1)">&larr; Back to registration details</a>
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
                <span>Verify Email & Continue &rsaquo;</span>
            </button>

            <div style="margin-top: 14px; text-align: center; font-size: 12.5px;">
                <span id="emailOtpTimerText" class="text-muted">Resend code in 00:<span id="emailCountdownSec">45</span></span>
                <a href="javascript:void(0)" id="resendEmailOtpLink" class="custom-link fw-semibold d-none" onclick="resendEmailVerificationCode()">Resend OTP</a>
            </div>
        </div>

        {{-- ═════════════════════════════════════════════════════════════════════ --}}
        {{-- PANEL 5: MOBILE OTP VERIFY (Streamlined - No duplicate input)        --}}
        {{-- ═════════════════════════════════════════════════════════════════════ --}}
        <div class="auth-flow-panel" id="panelAddMobile">
            <h1 class="auth-heading" style="font-size: 22px;">Verify mobile number</h1>
            <p style="font-size: 13px; color: #565959; margin-bottom: 14px;">
                We sent a 6-digit verification code to <strong id="displayMobileTarget" class="text-dark">+880 1712-345678</strong>.
                <a href="javascript:void(0)" class="custom-link ms-1" onclick="toggleEditMobileBox()" style="font-size: 12px;">(Change)</a>
            </p>

            <!-- Optional Change Mobile Number Accordion -->
            <div id="changeMobileBox" class="d-none mb-3 p-2.5 bg-light rounded border">
                <label class="form-label-custom" style="font-size: 12px;">Mobile:</label>
                <div class="country-input-row mb-2">
                    <select id="mobileCountryCode" class="country-select-dropdown" style="width: 105px;">
                        <option value="+880" selected>BD +880</option>
                        <option value="+1">US +1</option>
                        <option value="+44">UK +44</option>
                        <option value="+91">IN +91</option>
                        <option value="+971">AE +971</option>
                        <option value="+966">SA +966</option>
                        <option value="+60">MY +60</option>
                    </select>
                    <input type="tel" id="mobileNumberInput" class="input-text-custom flex-grow-1" placeholder="Mobile number">
                </div>
                <button type="button" class="btn btn-sm btn-outline-primary w-100 py-1" onclick="updateMobileNumberAndResend()">
                    <i class="fa-solid fa-paper-plane me-1"></i> Resend Code
                </button>
            </div>

            <div class="form-group-item">
                <label class="form-label-custom">Enter 6-digit Mobile OTP</label>
                <div class="otp-inputs-wrapper">
                    <input type="text" maxlength="1" class="otp-digit-field" id="mOtp1" oninput="otpKeyNav(this, 'mOtp2')">
                    <input type="text" maxlength="1" class="otp-digit-field" id="mOtp2" oninput="otpKeyNav(this, 'mOtp3', 'mOtp1')">
                    <input type="text" maxlength="1" class="otp-digit-field" id="mOtp3" oninput="otpKeyNav(this, 'mOtp4', 'mOtp2')">
                    <input type="text" maxlength="1" class="otp-digit-field" id="mOtp4" oninput="otpKeyNav(this, 'mOtp5', 'mOtp3')">
                    <input type="text" maxlength="1" class="otp-digit-field" id="mOtp5" oninput="otpKeyNav(this, 'mOtp6', 'mOtp4')">
                    <input type="text" maxlength="1" class="otp-digit-field" id="mOtp6" oninput="otpKeyNav(this, null, 'mOtp5')">
                </div>
            </div>

            <button type="button" class="btn-action-primary" id="btnVerifyMobileOtp" onclick="verifyMobileOtpAndGoToCategory()">
                <span>Verify & Continue &rsaquo;</span>
            </button>

            <div style="margin-top: 14px; text-align: center; font-size: 12.5px;">
                <span id="mobileOtpTimerText" class="text-muted">Resend code in 00:<span id="mobileCountdownSec">45</span></span>
                <a href="javascript:void(0)" id="resendMobileOtpLink" class="custom-link fw-semibold d-none" onclick="sendMobileVerificationOtp()">Resend OTP</a>
            </div>

            <div id="mobileSupportBox" class="mt-2 text-center" style="font-size: 12px;">
                <span class="text-muted">Support:</span>
                <a href="https://api.whatsapp.com/send?phone=8801700000000&text=Need+help+with+registration+OTP" target="_blank" id="whatsappSupportBtn" class="text-success text-decoration-none fw-semibold ms-1">
                    <i class="fa-brands fa-whatsapp"></i> WhatsApp
                </a>
            </div>
        </div>

        {{-- ═════════════════════════════════════════════════════════════════════ --}}
        {{-- PANEL 6: ACCOUNT CATEGORY & ADDRESS                                   --}}
        {{-- ═════════════════════════════════════════════════════════════════════ --}}
        <div class="auth-flow-panel" id="panelCategoryAddress">
            <h1 class="auth-heading" style="font-size: 22px;">Profile</h1>
            <p style="font-size: 13px; color: #565959; margin-bottom: 14px;">
                Enter your role details and primary address. (<span style="color: #c40000; font-weight: bold;">*</span>) indicates required fields.
            </p>

            <div class="form-group-item">
                <label class="form-label-custom" for="regCategorySelect">Role <span style="color: #c40000; font-weight: bold;">*</span></label>
                <select id="regCategorySelect" class="input-text-custom" style="height: 38px; font-weight: 600; cursor: pointer;" required onchange="onRegistrationCategoryChange(this.value)">
                    <option value="" disabled selected>Select Role *</option>
                    <option value="buyer">Customer</option>
                    <option value="author">Author</option>
                    <option value="publisher">Publisher</option>
                    <option value="seller">Seller</option>
                </select>
            </div>

            <!-- Dynamic Author Identity Group -->
            <div id="authorIdentityGroup" class="d-none partner-kyc-box mb-3">
                <div class="d-flex align-items-center gap-2 mb-2 pb-1 border-bottom">
                    <i class="fa-solid fa-feather-pointed text-success fs-5"></i>
                    <span class="fw-bold text-dark" style="font-size: 13px;">Author Details</span>
                </div>
                <div class="row g-2">
                    <div class="col-sm-6 form-group-item mb-2">
                        <label class="form-label-custom" for="regAuthorNameInput">Name (Bengali) <span style="color: #c40000; font-weight: bold;">*</span></label>
                        <input type="text" id="regAuthorNameInput" class="input-text-custom font-kalpurush" placeholder="Author name (Bengali)" style="font-family: 'Kalpurush', 'SolaimanLipi', sans-serif !important; font-size: 14.5px;">
                    </div>
                    <div class="col-sm-6 form-group-item mb-2">
                        <label class="form-label-custom" for="regAuthorNameEnInput">Name (English) <span style="color: #c40000; font-weight: bold;">*</span></label>
                        <input type="text" id="regAuthorNameEnInput" class="input-text-custom" placeholder="e.g. Humayun Ahmed">
                    </div>
                </div>
                <div class="form-group-item mb-2">
                    <label class="form-label-custom" for="regPenNameInput">Pen Name</label>
                    <input type="text" id="regPenNameInput" class="input-text-custom" placeholder="e.g. Banaphool (Optional)">
                </div>
                <div class="form-group-item mb-2">
                    <label class="form-label-custom">Genres</label>
                    <div class="genre-chips-container mb-1.5" id="genreChipsContainer">
                        <span class="genre-chip" onclick="toggleGenreChip(this, 'Fiction')">Fiction</span>
                        <span class="genre-chip" onclick="toggleGenreChip(this, 'Novel')">Novel</span>
                        <span class="genre-chip" onclick="toggleGenreChip(this, 'Stories')">Stories</span>
                        <span class="genre-chip" onclick="toggleGenreChip(this, 'Poetry')">Poetry</span>
                        <span class="genre-chip" onclick="toggleGenreChip(this, 'Research')">Research</span>
                        <span class="genre-chip" onclick="toggleGenreChip(this, 'History')">History</span>
                        <span class="genre-chip" onclick="toggleGenreChip(this, 'Translation')">Translation</span>
                        <span class="genre-chip" onclick="toggleGenreChip(this, 'Children')">Children</span>
                        <span class="genre-chip" onclick="toggleGenreChip(this, 'Sci-Fi')">Sci-Fi</span>
                        <span class="genre-chip" onclick="toggleGenreChip(this, 'Islamic')">Islamic</span>
                    </div>
                    <input type="text" id="regGenresCustomInput" class="input-text-custom mt-1" placeholder="Other genres (comma separated)">
                </div>
                <div class="form-group-item mb-1">
                    <label class="form-label-custom" for="regAuthorBioInput">Bio</label>
                    <textarea id="regAuthorBioInput" class="input-text-custom" style="height: 60px; resize: vertical;" placeholder="Short literary biography or published books..."></textarea>
                </div>
            </div>

            <!-- Dynamic Publisher Identity Group -->
            <div id="publisherIdentityGroup" class="d-none partner-kyc-box mb-3">
                <div class="d-flex align-items-center gap-2 mb-2 pb-1 border-bottom">
                    <i class="fa-solid fa-building text-danger fs-5"></i>
                    <span class="fw-bold text-dark" style="font-size: 13px;">Publisher Details</span>
                </div>
                <div class="row g-2">
                    <div class="col-sm-6 form-group-item mb-2">
                        <label class="form-label-custom" for="regPublishingHouseNameInput">Publishing House <span style="color: #c40000; font-weight: bold;">*</span></label>
                        <input type="text" id="regPublishingHouseNameInput" class="input-text-custom" placeholder="e.g. Idea Prokashon">
                    </div>
                    <div class="col-sm-6 form-group-item mb-2">
                        <label class="form-label-custom" for="regPublisherOwnerNameInput">Owner <span style="color: #c40000; font-weight: bold;">*</span></label>
                        <input type="text" id="regPublisherOwnerNameInput" class="input-text-custom" placeholder="Name of Owner">
                    </div>
                </div>
                <div class="row g-2">
                    <div class="col-sm-6 form-group-item mb-1">
                        <label class="form-label-custom" for="regEstablishedInput">Established</label>
                        <input type="text" id="regEstablishedInput" class="input-text-custom" placeholder="e.g. 2015">
                    </div>
                    <div class="col-sm-6 form-group-item mb-1">
                        <label class="form-label-custom" for="regTradeLicenseInput">Trade License</label>
                        <input type="text" id="regTradeLicenseInput" class="input-text-custom" placeholder="e.g. TRAD/DNCC/12345">
                    </div>
                </div>
            </div>

            <!-- Dynamic Seller Identity Group -->
            <div id="sellerIdentityGroup" class="d-none partner-kyc-box mb-3">
                <div class="d-flex align-items-center gap-2 mb-2 pb-1 border-bottom">
                    <i class="fa-solid fa-store text-primary fs-5"></i>
                    <span class="fw-bold text-dark" style="font-size: 13px;">Seller Details</span>
                </div>
                <div class="row g-2">
                    <div class="col-sm-6 form-group-item mb-2">
                        <label class="form-label-custom" for="regShopNameInput">Bookshop <span style="color: #c40000; font-weight: bold;">*</span></label>
                        <input type="text" id="regShopNameInput" class="input-text-custom" placeholder="e.g. Dhaka Book Corner">
                    </div>
                    <div class="col-sm-6 form-group-item mb-2">
                        <label class="form-label-custom" for="regSellerTradeLicenseInput">Trade License / NID</label>
                        <input type="text" id="regSellerTradeLicenseInput" class="input-text-custom" placeholder="Trade License or NID">
                    </div>
                </div>
            </div>

            <!-- Responsive 2-Column Address Group -->
            <div class="address-section-box">
                <div class="row g-2">
                    <div class="col-sm-6 form-group-item mb-2">
                        <label class="form-label-custom" for="regCountrySelect">Country <span style="color: #c40000; font-weight: bold;">*</span></label>
                        <select id="regCountrySelect" class="input-text-custom" style="height: 36px;" required onchange="onRegistrationCountryChange(this.value)">
                            <option value="" disabled>Select Country *</option>
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
                    <div class="col-sm-6 form-group-item mb-2" id="districtDropdownGroup">
                        <label class="form-label-custom" for="regDistrictSelect">District <span style="color: #c40000; font-weight: bold;">*</span></label>
                        <select id="regDistrictSelect" class="input-text-custom" style="height: 36px;" required onchange="onRegistrationDistrictChange(this.value)">
                            <option value="">Select District *</option>
                        </select>
                    </div>
                    <div class="col-sm-6 form-group-item mb-2 d-none" id="districtTextGroup">
                        <label class="form-label-custom" for="regDistrictTextInput">State / Province <span style="color: #c40000; font-weight: bold;">*</span></label>
                        <input type="text" id="regDistrictTextInput" class="input-text-custom" placeholder="e.g. California / Ontario">
                    </div>

                    <!-- Thana / City Field -->
                    <div class="col-sm-6 form-group-item mb-2" id="thanaDropdownGroup">
                        <label class="form-label-custom" for="regThanaSelect">Thana / Upazila <span style="color: #c40000; font-weight: bold;">*</span></label>
                        <select id="regThanaSelect" class="input-text-custom" style="height: 36px;" required>
                            <option value="">Select Thana / Upazila *</option>
                        </select>
                    </div>
                    <div class="col-sm-6 form-group-item mb-2 d-none" id="thanaTextGroup">
                        <label class="form-label-custom" for="regThanaTextInput">City / Town <span style="color: #c40000; font-weight: bold;">*</span></label>
                        <input type="text" id="regThanaTextInput" class="input-text-custom" placeholder="e.g. New York / London">
                    </div>

                    <!-- Post Code -->
                    <div class="col-sm-6 form-group-item mb-2">
                        <label class="form-label-custom" for="regPostCodeInput">Post Code <span style="color: #c40000; font-weight: bold;">*</span></label>
                        <input type="text" id="regPostCodeInput" class="input-text-custom" placeholder="Postal / Zip Code (e.g. 1205)" required>
                    </div>
                </div>

                <!-- Postal Address -->
                <div class="form-group-item mb-2">
                    <label class="form-label-custom" for="regAddressInput">Postal Address <span style="color: #c40000; font-weight: bold;">*</span></label>
                    <textarea id="regAddressInput" class="input-text-custom" style="height: 60px; resize: vertical; padding: 6px 8px;" placeholder="Street address, house number, road, area..." required></textarea>
                </div>
            </div>

            <button type="button" class="btn-action-primary" id="btnFinishRegistration" onclick="submitCompleteUnifiedRegistration()">
                <span>Complete Registration &rsaquo;</span>
            </button>
        </div>

    </div>

    {{-- 3. "New to Idea?" Section (When on Sign-In mode) --}}
    <div id="newToIdeaSection" class="{{ request('mode') === 'register' ? 'd-none' : '' }}" style="width: 100%;">
        <div class="divider-wrapper">
            <span class="divider-label">New to Idea?</span>
        </div>

        <button type="button" class="btn-create-account-featured" onclick="switchAuthMode('register')" id="btnOpenCreateAccount">
            <span class="btn-icon-wrap">
                <i class="fa-solid fa-user-plus"></i>
            </span>
            <span class="fw-semibold">Create your Idea account</span>
            <i class="fa-solid fa-arrow-right btn-arrow-icon"></i>
        </button>
        <div class="create-account-roles-hint">
            <i class="fa-solid fa-users me-1 text-primary"></i>Customer • Author • Publisher • Seller
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
            &copy; 2020-26, Shakil Masud, idea prokashon
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
    countryCode: '+880',
    captcha_proof_token: '',
    category: ''
};

function saveRegState() {
    try {
        sessionStorage.setItem('idea_reg_state', JSON.stringify({
            name: regData.name,
            phone: regData.phone,
            countryCode: regData.countryCode,
            email: regData.email,
            captcha_proof_token: regData.captcha_proof_token,
            category: regData.category
        }));
    } catch(e) {}
}

function restoreRegState() {
    try {
        const saved = sessionStorage.getItem('idea_reg_state');
        if (saved) {
            const parsed = JSON.parse(saved);
            if (parsed.name) {
                regData.name = parsed.name;
                const el = document.getElementById('regNameInput');
                if (el) el.value = parsed.name;
            }
            if (parsed.phone) {
                regData.phone = parsed.phone;
                const el = document.getElementById('regPhoneInput');
                if (el) el.value = parsed.phone;
            }
            if (parsed.countryCode) {
                regData.countryCode = parsed.countryCode;
                const el = document.getElementById('regCountryCodeSelect');
                if (el) el.value = parsed.countryCode;
            }
            if (parsed.email) {
                regData.email = parsed.email;
                const el = document.getElementById('regEmailInput');
                if (el) el.value = parsed.email;
            }
            if (parsed.captcha_proof_token) {
                regData.captcha_proof_token = parsed.captcha_proof_token;
            }
            if (parsed.category) {
                regData.category = parsed.category;
                const catDropdown = document.getElementById('regCategoryDropdown');
                if (catDropdown) {
                    catDropdown.value = parsed.category;
                    if (typeof onRegistrationTypeDropdownChange === 'function') {
                        onRegistrationTypeDropdownChange(parsed.category);
                    }
                }
            }
        }
    } catch(e) {}
}

function updateRegStepper(stepNum) {
    const stepper = document.getElementById('regProgressStepper');
    if (!stepper) return;
    for (let i = 1; i <= 5; i++) {
        const node = document.getElementById('stepNode' + i);
        const line = document.getElementById('stepLine' + (i - 1));
        if (node) {
            node.classList.remove('active', 'completed');
            if (i < stepNum) {
                node.classList.add('completed');
                const circle = node.querySelector('.step-circle');
                if (circle) circle.innerHTML = '<i class="fa-solid fa-check"></i>';
            } else if (i === stepNum) {
                node.classList.add('active');
                const circle = node.querySelector('.step-circle');
                if (circle) circle.textContent = i;
            } else {
                const circle = node.querySelector('.step-circle');
                if (circle) circle.textContent = i;
            }
        }
        if (line) {
            line.classList.toggle('completed', i <= stepNum);
        }
    }
}

function goToStep(stepNum) {
    hideAlert();
    document.querySelectorAll('.auth-flow-panel').forEach(p => p.classList.remove('active'));

    if (stepNum === 1) {
        document.getElementById('panelCreateAccount').classList.add('active');
        updateRegStepper(1);
    } else if (stepNum === 2) {
        document.getElementById('panelCaptcha').classList.add('active');
        updateRegStepper(2);
        loadCaptchaChallenge();
        setTimeout(() => document.getElementById('captchaCodeInput')?.focus(), 100);
    } else if (stepNum === 3) {
        const displayTarget = document.getElementById('displayEmailTarget');
        if (displayTarget) displayTarget.textContent = regData.email;
        document.getElementById('panelEmailOtp').classList.add('active');
        updateRegStepper(3);
        setTimeout(() => document.getElementById('eOtp1')?.focus(), 100);
    } else if (stepNum === 4) {
        const displayTarget = document.getElementById('displayMobileTarget');
        if (displayTarget) displayTarget.textContent = (regData.countryCode || '+880') + ' ' + regData.phone;
        document.getElementById('panelAddMobile').classList.add('active');
        updateRegStepper(4);
        setTimeout(() => document.getElementById('mOtp1')?.focus(), 100);
    } else if (stepNum === 5) {
        document.getElementById('panelCategoryAddress').classList.add('active');
        updateRegStepper(5);
    }
}

function onStepNodeClick(step) {
    const node = document.getElementById('stepNode' + step);
    if (node && (node.classList.contains('completed') || node.classList.contains('active'))) {
        goToStep(step);
    }
}

function updateCategoryBanner() {
    const urlParams = new URLSearchParams(window.location.search);
    const cat = urlParams.get('category') || regData.category;
    const banner = document.getElementById('categoryIndicatorBanner');
    const title = document.getElementById('catBannerTitle');
    const sub = document.getElementById('catBannerSubtitle');
    const icon = document.getElementById('catBannerIcon');
    if (!banner) return;

    if (cat === 'author') {
        banner.classList.remove('d-none');
        if (title) title.textContent = 'Author';
        if (sub) sub.textContent = 'Publish manuscripts and books';
        if (icon) icon.innerHTML = '<i class="fa-solid fa-feather-pointed"></i>';
        regData.category = 'author';
    } else if (cat === 'publisher') {
        banner.classList.remove('d-none');
        if (title) title.textContent = 'Publisher';
        if (sub) sub.textContent = 'Manage catalog and distribution';
        if (icon) icon.innerHTML = '<i class="fa-solid fa-building"></i>';
        regData.category = 'publisher';
    } else if (cat === 'seller') {
        banner.classList.remove('d-none');
        if (title) title.textContent = 'Seller';
        if (sub) sub.textContent = 'Wholesale and retail bookshop';
        if (icon) icon.innerHTML = '<i class="fa-solid fa-store"></i>';
        regData.category = 'seller';
    } else {
        banner.classList.add('d-none');
    }
}

/**
 * Switch Auth Mode between Sign-In & Create Account
 */
function switchAuthMode(mode) {
    hideAlert();
    document.querySelectorAll('.auth-flow-panel').forEach(p => p.classList.remove('active'));
    const newSection = document.getElementById('newToIdeaSection');
    const authContainer = document.getElementById('authContainer');
    const stepper = document.getElementById('regProgressStepper');

    if (mode === 'register') {
        if (authContainer) authContainer.classList.add('register-mode');
        if (stepper) stepper.classList.remove('d-none');
        document.getElementById('panelCreateAccount').classList.add('active');
        if (newSection) newSection.classList.add('d-none');
        updateRegStepper(1);
        updateCategoryBanner();
        restoreRegState();
        loadCaptchaChallenge();
    } else {
        if (authContainer) authContainer.classList.remove('register-mode');
        if (stepper) stepper.classList.add('d-none');
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
 * Real-Time Password Strength Evaluator
 */
function evaluatePasswordStrength(pwd) {
    const bar = document.getElementById('pwdStrengthBar');
    const label = document.getElementById('pwdStrengthLabel');
    const hint = document.getElementById('pwdHintMsg');

    if (!bar || !label) return;

    if (!pwd || pwd.length === 0) {
        bar.style.width = '0%';
        bar.style.backgroundColor = '#dc2626';
        label.textContent = 'Too Weak';
        label.style.color = '#dc2626';
        if (hint) {
            hint.className = 'pwd-hint-text d-none';
            hint.textContent = '';
        }
        return;
    }

    const hasLength = pwd.length >= 8;
    const hasUpper = /[A-Z]/.test(pwd);
    const hasLower = /[a-z]/.test(pwd);
    const hasNumber = /[0-9]/.test(pwd);
    const hasSpecial = /[^A-Za-z0-9]/.test(pwd);

    const missing = [];
    if (!hasLength) missing.push('at least 8 characters');
    if (!hasUpper) missing.push('1 uppercase letter (A-Z)');
    if (!hasLower) missing.push('1 lowercase letter (a-z)');
    if (!hasNumber) missing.push('1 number (0-9)');
    if (!hasSpecial) missing.push('1 special character (!@#$)');

    if (hint) {
        hint.classList.remove('d-none');
        if (missing.length > 0) {
            hint.className = 'pwd-hint-text has-missing';
            hint.innerHTML = '<i class="fa-solid fa-circle-exclamation me-1" style="color: #dc2626;"></i> Please add: ' + missing.join(', ') + '.';
        } else {
            hint.className = 'pwd-hint-text is-valid';
            hint.innerHTML = '<i class="fa-solid fa-circle-check me-1" style="color: #16a34a;"></i> Strong password met all requirements.';
        }
    }

    let score = 0;
    if (hasLength) score += 25;
    if (pwd.length >= 12) score += 15;
    if (hasUpper) score += 15;
    if (hasLower) score += 15;
    if (hasNumber) score += 15;
    if (hasSpecial) score += 15;

    // Check for obvious sequential / repetitive patterns
    const isSequential = /(?:012|123|234|345|456|567|678|789|890|abc|bcd|cde|def|efg|fgh|ghi|ijk|jkl|klm|lmn|mno|nop|opq|pqr|qrs|rst|stu|tuv|uvw|xyz|qwerty)/i.test(pwd);
    const isRepeated = /(.)\1{2,}/.test(pwd);
    if (isSequential || isRepeated) {
        score = Math.max(10, score - 25);
    }

    score = Math.min(100, score);

    if (score < 30 || pwd.length < 6) {
        bar.style.width = Math.max(12, score) + '%';
        bar.style.backgroundColor = '#dc2626';
        label.textContent = 'Too Weak';
        label.style.color = '#dc2626';
    } else if (score < 50) {
        bar.style.width = score + '%';
        bar.style.backgroundColor = '#ea580c';
        label.textContent = 'Weak';
        label.style.color = '#ea580c';
    } else if (score < 75) {
        bar.style.width = score + '%';
        bar.style.backgroundColor = '#d97706';
        label.textContent = 'Fair';
        label.style.color = '#d97706';
    } else if (score < 90 || missing.length > 0) {
        bar.style.width = score + '%';
        bar.style.backgroundColor = '#2563eb';
        label.textContent = 'Good';
        label.style.color = '#2563eb';
    } else {
        bar.style.width = '100%';
        bar.style.backgroundColor = '#059669';
        label.textContent = 'Strong';
        label.style.color = '#059669';
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
 * Dynamic CSRF-resilient Fetch Wrapper
 * Automatically detects 419 token mismatches, fetches a fresh token, and retries seamlessly.
 */
async function fetchWithCsrfRetry(url, options = {}) {
    if (!options.headers) options.headers = {};

    const getMetaToken = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const token = getMetaToken();
    if (token && !options.headers['X-CSRF-TOKEN']) {
        options.headers['X-CSRF-TOKEN'] = token;
    }
    if (!options.headers['X-Requested-With']) {
        options.headers['X-Requested-With'] = 'XMLHttpRequest';
    }
    if (!options.headers['Accept']) {
        options.headers['Accept'] = 'application/json';
    }
    if (options.body && typeof options.body === 'string' && !options.headers['Content-Type']) {
        try {
            JSON.parse(options.body);
            options.headers['Content-Type'] = 'application/json';
        } catch(e) {}
    }

    let response = await fetch(url, options);

    if (response.status === 419) {
        try {
            const tokenRes = await fetch('{{ route("auth.csrf-token") }}');
            if (tokenRes.ok) {
                const tokenData = await tokenRes.json();
                if (tokenData && tokenData.csrf_token) {
                    const meta = document.querySelector('meta[name="csrf-token"]');
                    if (meta) meta.setAttribute('content', tokenData.csrf_token);
                    options.headers['X-CSRF-TOKEN'] = tokenData.csrf_token;
                    if (options.body instanceof FormData && options.body.has('_token')) {
                        options.body.set('_token', tokenData.csrf_token);
                    }
                    response = await fetch(url, options);
                }
            }
        } catch (e) {
            console.warn('Auto CSRF token renewal failed:', e);
        }
    }

    return response;
}

/**
 * ═════════════════════════════════════════════════════════════════════════
 * STEP 2: MODERN SERVER-SIDE IMAGE CAPTCHA ENGINE
 * ═════════════════════════════════════════════════════════════════════════
 */
let isCaptchaLoading = false;
let currentCaptchaLength = 4;

function updateCaptchaCharCounter(currentLen, maxLen) {
    const counter = document.getElementById('captchaCharCount');
    if (!counter) return;
    const target = maxLen || currentCaptchaLength || 4;
    if (currentLen === target && target > 0) {
        counter.className = 'badge bg-success text-white border border-success font-monospace';
        counter.innerHTML = '<i class="fa-solid fa-check me-1"></i>' + currentLen + ' / ' + target;
    } else {
        counter.className = 'badge bg-light text-secondary border font-monospace';
        counter.textContent = currentLen + ' / ' + target;
    }
}

function onCaptchaInput(el) {
    el.value = el.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
    updateCaptchaCharCounter(el.value.length, currentCaptchaLength);
}

async function loadCaptchaChallenge(forceRefresh = false) {
    const tokenInput = document.getElementById('captchaTokenInput');
    const imgEl = document.getElementById('captchaImageElement');
    const spinner = document.getElementById('captchaLoadingSpinner');
    const refreshBtn = document.getElementById('btnRefreshCaptcha');
    const codeInput = document.getElementById('captchaCodeInput');

    if (!forceRefresh && tokenInput && tokenInput.value && imgEl && imgEl.src && !imgEl.classList.contains('d-none')) {
        return;
    }

    if (isCaptchaLoading) return;
    isCaptchaLoading = true;

    if (spinner) spinner.classList.remove('d-none');
    if (imgEl) imgEl.classList.add('d-none');
    if (refreshBtn) refreshBtn.classList.add('spinning');

    try {
        const response = await fetchWithCsrfRetry('{{ route("auth.captcha.generate") }}', {
            method: 'GET'
        });

        const data = await response.json();
        if (data && data.success && data.token && data.image) {
            if (tokenInput) tokenInput.value = data.token;
            if (imgEl) {
                imgEl.src = data.image;
                imgEl.classList.remove('d-none');
            }
            if (data.length) {
                currentCaptchaLength = data.length;
            }
            if (codeInput) {
                updateCaptchaCharCounter(codeInput.value.length, currentCaptchaLength);
            }
            if (data.csrf_token) {
                const meta = document.querySelector('meta[name="csrf-token"]');
                if (meta) meta.setAttribute('content', data.csrf_token);
            }
        }
    } catch (err) {
        console.error('Failed to load CAPTCHA:', err);
    } finally {
        if (spinner) spinner.classList.add('d-none');
        if (refreshBtn) refreshBtn.classList.remove('spinning');
        isCaptchaLoading = false;
    }
}

function refreshCaptchaChallenge() {
    hideAlert();
    const codeInput = document.getElementById('captchaCodeInput');
    if (codeInput) {
        codeInput.value = '';
        codeInput.focus();
        updateCaptchaCharCounter(0, currentCaptchaLength);
    }
    loadCaptchaChallenge(true);
}

function proceedToCaptchaChallenge() {
    hideAlert();
    const name = document.getElementById('regNameInput') ? document.getElementById('regNameInput').value.trim() : '';
    const phone = document.getElementById('regPhoneInput') ? document.getElementById('regPhoneInput').value.trim() : '';
    const countryCode = document.getElementById('regCountryCodeSelect') ? document.getElementById('regCountryCodeSelect').value : '+880';
    const email = document.getElementById('regEmailInput') ? document.getElementById('regEmailInput').value.trim() : '';
    const pwd = document.getElementById('regPasswordInput') ? document.getElementById('regPasswordInput').value : '';
    const pwdConfirm = document.getElementById('regPasswordConfirmInput') ? document.getElementById('regPasswordConfirmInput').value : '';

    if (!name) {
        showAlert('Please enter your name.');
        const nameInput = document.getElementById('regNameInput');
        if (nameInput) nameInput.focus();
        return;
    }

    if (!phone) {
        showAlert('Please enter your mobile number.');
        const phoneInput = document.getElementById('regPhoneInput');
        if (phoneInput) phoneInput.focus();
        return;
    }

    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!email || !emailRegex.test(email)) {
        showAlert('Please enter a valid email address.');
        const emailInput = document.getElementById('regEmailInput');
        if (emailInput) emailInput.focus();
        return;
    }

    const errors = [];
    if (pwd.length < 8) {
        errors.push('Password must be at least 8 characters.');
    }
    if (pwd.length > 30) {
        errors.push('Password must not exceed 30 characters.');
    }
    if (!/[A-Z]/.test(pwd)) {
        errors.push('Password must contain at least one uppercase letter (A-Z).');
    }
    if (!/[a-z]/.test(pwd)) {
        errors.push('Password must contain at least one lowercase letter (a-z).');
    }
    if (!/[0-9]/.test(pwd)) {
        errors.push('Password must contain at least one number (0-9).');
    }
    if (!/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(pwd)) {
        errors.push('Password must contain at least one special character (!@#$%^&* etc.).');
    }
    if (pwd !== pwdConfirm) {
        errors.push('Passwords do not match.');
    }

    if (errors.length > 0) {
        if (errors.length === 1) {
            showAlert(errors[0]);
        } else {
            showAlert(errors[0] + ' (and ' + (errors.length - 1) + ' more error' + (errors.length > 2 ? 's' : '') + ')');
        }
        const pwdInput = document.getElementById('regPasswordInput');
        if (pwdInput) pwdInput.focus();
        return;
    }

    regData.name = name;
    regData.phone = phone;
    regData.countryCode = countryCode;
    regData.email = email;
    regData.identifier = email;
    regData.password = pwd;
    const catDropdown = document.getElementById('regCategoryDropdown');
    regData.category = catDropdown ? catDropdown.value : (regData.category || 'buyer');
    saveRegState();

    goToStep(2);
}

// Backward compatibility alias
const proceedToPuzzleChallenge = proceedToCaptchaChallenge;

async function verifyCaptchaSolution() {
    hideAlert();
    const tokenInput = document.getElementById('captchaTokenInput');
    const codeInput = document.getElementById('captchaCodeInput');
    const verifyBtn = document.getElementById('btnVerifyCaptcha');
    const btnText = document.getElementById('btnVerifyCaptchaText');

    const token = tokenInput ? tokenInput.value.trim() : '';
    const code = codeInput ? codeInput.value.trim() : '';

    if (!token) {
        showAlert('CAPTCHA challenge expired. Loading a fresh one...');
        refreshCaptchaChallenge();
        return;
    }

    if (!code) {
        showAlert('Please enter the characters shown in the CAPTCHA image.');
        if (codeInput) codeInput.focus();
        return;
    }

    if (verifyBtn) verifyBtn.disabled = true;
    if (btnText) btnText.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Verifying...';

    try {
        const response = await fetchWithCsrfRetry('{{ route("auth.captcha.verify") }}', {
            method: 'POST',
            body: JSON.stringify({
                captcha_token: token,
                captcha_code: code
            })
        });

        const data = await response.json();

        if (response.ok && data.success) {
            regData.captcha_proof_token = data.proof_token || '';
            saveRegState();
            hideAlert();

            // Transition to Step 3 (Email OTP verification)
            goToStep(3);
            sendEmailVerificationCode();
            return;
        }

        // On Failure: Show required error message & automatically update CAPTCHA
        const errorMsg = (data && data.message) ? data.message : 'Invalid CAPTCHA. Please enter the characters shown in the image.';
        showAlert(errorMsg);

        if (codeInput) {
            codeInput.value = '';
            codeInput.focus();
        }

        // Fast update using returned fresh CAPTCHA without extra HTTP request
        if (data && data.fresh_token && data.fresh_image) {
            if (tokenInput) tokenInput.value = data.fresh_token;
            const imgEl = document.getElementById('captchaImageElement');
            if (imgEl) {
                imgEl.src = data.fresh_image;
                imgEl.classList.remove('d-none');
            }
            if (data.fresh_length) {
                currentCaptchaLength = data.fresh_length;
            }
            updateCaptchaCharCounter(0, currentCaptchaLength);
        } else {
            loadCaptchaChallenge(true);
        }

    } catch (err) {
        console.error('CAPTCHA verification error:', err);
        showAlert('An error occurred during verification. Please try again.');
        loadCaptchaChallenge(true);
    } finally {
        if (verifyBtn) verifyBtn.disabled = false;
        if (btnText) btnText.textContent = 'Verify & Continue';
    }
}

/**
 * ═════════════════════════════════════════════════════════════════════════
 * STEP 3: EMAIL OTP VERIFICATION
 * ═════════════════════════════════════════════════════════════════════
 */
function normalizeDigits(str) {
    if (!str) return '';
    const bn = ['০','১','২','৩','৪','৫','৬','৭','৮','৯'];
    const en = ['0','1','2','3','4','5','6','7','8','9'];
    let res = str.toString();
    for (let i = 0; i < bn.length; i++) {
        res = res.replaceAll(bn[i], en[i]);
    }
    return res.replace(/[^\d]/g, '');
}

function otpKeyNav(current, nextId, prevId) {
    current.value = normalizeDigits(current.value);
    if (current.value.length === 1 && nextId) {
        const next = document.getElementById(nextId);
        if (next) next.focus();
    }
}

function setupOtpInputs() {
    ['eOtp', 'mOtp'].forEach(prefix => {
        const inputs = [];
        for (let i = 1; i <= 6; i++) {
            const el = document.getElementById(prefix + i);
            if (el) inputs.push(el);
        }

        inputs.forEach((input, idx) => {
            // Handle paste (distributes full 6 digits & triggers submission)
            input.addEventListener('paste', function(e) {
                e.preventDefault();
                const pasteData = (e.clipboardData || window.clipboardData).getData('text');
                const cleanDigits = normalizeDigits(pasteData);
                if (!cleanDigits) return;

                for (let i = 0; i < inputs.length; i++) {
                    if (cleanDigits[i]) {
                        inputs[i].value = cleanDigits[i];
                    }
                }

                const targetIdx = Math.min(cleanDigits.length, inputs.length) - 1;
                if (targetIdx >= 0 && inputs[targetIdx]) {
                    inputs[targetIdx].focus();
                }

                if (cleanDigits.length >= 6) {
                    if (prefix === 'eOtp') {
                        verifyEmailOtpAndProceed();
                    } else if (prefix === 'mOtp') {
                        verifyMobileOtpAndGoToCategory();
                    }
                }
            });

            // Backspace navigation & Enter key submit
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Backspace' && !this.value && idx > 0) {
                    inputs[idx - 1].focus();
                    inputs[idx - 1].value = '';
                } else if (e.key === 'Enter') {
                    e.preventDefault();
                    if (prefix === 'eOtp') {
                        verifyEmailOtpAndProceed();
                    } else if (prefix === 'mOtp') {
                        verifyMobileOtpAndGoToCategory();
                    }
                }
            });
        });
    });
}

let countdownInterval = null;
function startEmailCountdown(seconds = 45) {
    let sec = seconds;
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

async function sendEmailVerificationCode() {
    if (!regData.email) return;
    try {
        const res = await fetchWithCsrfRetry('{{ route("register.send-email-otp") }}', {
            method: 'POST',
            body: JSON.stringify({ email: regData.email })
        });
        const data = await res.json();
        if (res.ok && data.success) {
            showAlert(data.message, true);
            startEmailCountdown(data.cooldown || 45);
        } else {
            showAlert(data.message || 'Failed to send email verification code.');
        }
    } catch (e) {
        showAlert('Unable to send verification code. Please check your connection.');
    }
}

function resendEmailVerificationCode() {
    sendEmailVerificationCode();
}

async function verifyEmailOtpAndProceed() {
    let code = '';
    for (let i = 1; i <= 6; i++) {
        code += (document.getElementById('eOtp' + i)?.value || '');
    }
    code = normalizeDigits(code);

    if (code.length !== 6) {
        showAlert('Please enter the complete 6-digit email verification code.');
        return;
    }

    const btn = document.getElementById('btnVerifyEmailOtp');
    if (btn) btn.disabled = true;

    try {
        const res = await fetchWithCsrfRetry('{{ route("register.verify-email-otp") }}', {
            method: 'POST',
            body: JSON.stringify({ email: regData.email, otp: code })
        });
        const data = await res.json();
        if (res.ok && data.success) {
            hideAlert();
            saveRegState();

            // Transition to Step 4 (Mobile OTP verification)
            goToStep(4);

            const displayTarget = document.getElementById('displayMobileTarget');
            if (displayTarget) {
                displayTarget.textContent = (regData.countryCode || '+880') + ' ' + regData.phone;
            }
            const mobileInput = document.getElementById('mobileNumberInput');
            if (mobileInput) mobileInput.value = regData.phone;
            const mobileCode = document.getElementById('mobileCountryCode');
            if (mobileCode && regData.countryCode) mobileCode.value = regData.countryCode;

            // Auto-trigger mobile OTP sending
            sendMobileVerificationOtp();
        } else {
            showAlert(data.message || 'The verification code is invalid or has expired.');
        }
    } catch (e) {
        showAlert('An error occurred during email verification. Please try again.');
    } finally {
        if (btn) btn.disabled = false;
    }
}

/**
 * ═════════════════════════════════════════════════════════════════════════
 * STEP 4: MOBILE OTP & COMPLETION
 * ═════════════════════════════════════════════════════════════════════════
 */
let mobileCountdownInterval = null;
function startMobileCountdown(seconds = 45) {
    let sec = seconds;
    const countEl = document.getElementById('mobileCountdownSec');
    const timerText = document.getElementById('mobileOtpTimerText');
    const resendLink = document.getElementById('resendMobileOtpLink');

    if (timerText) timerText.classList.remove('d-none');
    if (resendLink) resendLink.classList.add('d-none');

    if (mobileCountdownInterval) clearInterval(mobileCountdownInterval);

    mobileCountdownInterval = setInterval(() => {
        sec--;
        if (countEl) countEl.textContent = sec < 10 ? '0' + sec : sec;
        if (sec <= 0) {
            clearInterval(mobileCountdownInterval);
            if (timerText) timerText.classList.add('d-none');
            if (resendLink) resendLink.classList.remove('d-none');
        }
    }, 1000);
}

function toggleEditMobileBox() {
    const box = document.getElementById('changeMobileBox');
    if (box) box.classList.toggle('d-none');
}

async function updateMobileNumberAndResend() {
    const numInput = document.getElementById('mobileNumberInput');
    const codeSelect = document.getElementById('mobileCountryCode');
    const newNum = numInput ? numInput.value.trim() : '';
    const newCode = codeSelect ? codeSelect.value : '+880';
    if (!newNum || newNum.length < 6) {
        showAlert('Please enter a valid mobile number.');
        return;
    }
    regData.phone = newNum;
    regData.countryCode = newCode;
    saveRegState();
    const displayTarget = document.getElementById('displayMobileTarget');
    if (displayTarget) displayTarget.textContent = newCode + ' ' + newNum;
    toggleEditMobileBox();
    await sendMobileVerificationOtp();
}

async function sendMobileVerificationOtp() {
    const phone = regData.phone;
    const countryCode = regData.countryCode || '+880';

    if (!phone) {
        showAlert('Please provide a valid mobile number.');
        return;
    }

    const btn = document.getElementById('btnVerifyMobileOtp');

    try {
        const res = await fetchWithCsrfRetry('{{ route("register.send-otp") }}', {
            method: 'POST',
            body: JSON.stringify({ phone: phone, country_code: countryCode })
        });
        const data = await res.json();
        if (res.ok && data.success) {
            showAlert(data.message, true);
            startMobileCountdown(data.cooldown || 45);
            document.getElementById('mOtp1')?.focus();
            if (data.support_whatsapp_url) {
                const waBtn = document.getElementById('whatsappSupportBtn');
                if (waBtn) waBtn.href = data.support_whatsapp_url;
            }
        } else {
            showAlert(data.message || 'Failed to send mobile verification code.');
        }
    } catch (e) {
        showAlert('Unable to send mobile verification code. Please check your connection.');
    }
}

async function verifyMobileOtpAndGoToCategory() {
    let code = '';
    for (let i = 1; i <= 6; i++) {
        code += (document.getElementById('mOtp' + i)?.value || '');
    }
    code = normalizeDigits(code);

    if (code.length !== 6) {
        showAlert('Please enter the complete 6-digit mobile verification code.');
        return;
    }

    const btn = document.getElementById('btnVerifyMobileOtp');
    if (btn) btn.disabled = true;

    try {
        const res = await fetchWithCsrfRetry('{{ route("register.verify-otp") }}', {
            method: 'POST',
            body: JSON.stringify({ phone: regData.phone, country_code: regData.countryCode || '+880', otp: code })
        });
        const data = await res.json();
        if (res.ok && data.success) {
            hideAlert();
            saveRegState();

            // Directly submit registration seamlessly
            submitCompleteUnifiedRegistration();
            return;
        } else {
            showAlert(data.message || 'The mobile verification code is invalid or has expired.');
        }
    } catch (e) {
        showAlert('An error occurred during mobile verification. Please try again.');
    } finally {
        if (btn) btn.disabled = false;
    }
}

/**
 * ═════════════════════════════════════════════════════════════════════════
 * STEP 5: CATEGORY, KYC DATA & REGISTRATION COMPLETION
 * ═════════════════════════════════════════════════════════════════════════
 */
function toggleGenreChip(chip, genreName) {
    chip.classList.toggle('selected');
}

function initDistrictDropdown() {
    const districtSelect = document.getElementById('regDistrictSelect');
    if (!districtSelect || districtSelect.options.length > 1) return;

    districtSelect.innerHTML = '<option value="">Select District *</option>';
    const districts = window.BD_DISTRICTS || [
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

    districts.forEach(d => {
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
        if (districtDrop) districtDrop.classList.remove('d-none');
        if (districtText) districtText.classList.add('d-none');
        if (thanaDrop) thanaDrop.classList.remove('d-none');
        if (thanaText) thanaText.classList.add('d-none');
        initDistrictDropdown();
    } else {
        if (districtDrop) districtDrop.classList.add('d-none');
        if (districtText) districtText.classList.remove('d-none');
        if (thanaDrop) thanaDrop.classList.add('d-none');
        if (thanaText) thanaText.classList.remove('d-none');
    }
}

function onRegistrationDistrictChange(district) {
    const thanaSelect = document.getElementById('regThanaSelect');
    if (!thanaSelect) return;

    thanaSelect.innerHTML = '<option value="">Select Thana / Upazila *</option>';
    if (!district) return;

    const thanaMap = window.BD_THANAS || {};
    const thanas = thanaMap[district] || [district + " Sadar"];
    thanas.forEach(t => {
        const opt = document.createElement('option');
        opt.value = t;
        opt.textContent = t;
        thanaSelect.appendChild(opt);
    });
}

function onRegistrationCategoryChange(cat) {
    const authorGrp = document.getElementById('authorIdentityGroup');
    const pubGrp = document.getElementById('publisherIdentityGroup');
    const sellerGrp = document.getElementById('sellerIdentityGroup');

    if (authorGrp) authorGrp.classList.add('d-none');
    if (pubGrp) pubGrp.classList.add('d-none');
    if (sellerGrp) sellerGrp.classList.add('d-none');

    regData.category = cat;
    saveRegState();

    if (cat === 'author') {
        if (authorGrp) authorGrp.classList.remove('d-none');
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

function onRegistrationTypeDropdownChange(role) {
    if (!role) role = 'buyer';
    regData.category = role;

    const dropdown = document.getElementById('regCategoryDropdown');
    if (dropdown && dropdown.value !== role) {
        dropdown.value = role;
    }

    if (typeof onRegistrationCategoryChange === 'function') {
        onRegistrationCategoryChange(role);
    }
}

function selectUnifiedRole(role) {
    onRegistrationTypeDropdownChange(role);
}

async function submitCompleteUnifiedRegistration() {
    const catDropdown = document.getElementById('regCategoryDropdown');
    const categorySelect = document.getElementById('regCategorySelect');
    const category = regData.category || (catDropdown ? catDropdown.value : (categorySelect ? categorySelect.value : 'buyer')) || 'buyer';

    let authorName = regData.name || '';
    let authorNameEn = regData.name || '';
    let penName = '';
    let genresStr = '';
    let authorBio = '';
    let publishingHouseName = regData.name || '';
    let publisherOwnerName = regData.name || '';
    let established = '';
    let tradeLicense = '';
    let shopName = regData.name || '';
    let sellerTradeLicense = '';

    if (category === 'author') {
        const authorInput = document.getElementById('regAuthorNameInput');
        if (authorInput && authorInput.value.trim()) authorName = authorInput.value.trim();
        const authorEnInput = document.getElementById('regAuthorNameEnInput');
        if (authorEnInput && authorEnInput.value.trim()) authorNameEn = authorEnInput.value.trim();
        penName = document.getElementById('regPenNameInput')?.value.trim() || '';
        
        const selectedChips = Array.from(document.querySelectorAll('.genre-chip.selected')).map(c => c.textContent.trim());
        const customGenres = document.getElementById('regGenresCustomInput')?.value.trim() || '';
        let allGenres = [...selectedChips];
        if (customGenres) {
            allGenres = allGenres.concat(customGenres.split(',').map(s => s.trim()).filter(Boolean));
        }
        genresStr = allGenres.join(', ');
        authorBio = document.getElementById('regAuthorBioInput')?.value.trim() || '';

    } else if (category === 'publisher') {
        const pubHouseInput = document.getElementById('regPublishingHouseNameInput');
        if (pubHouseInput && pubHouseInput.value.trim()) publishingHouseName = pubHouseInput.value.trim();
        const pubOwnerInput = document.getElementById('regPublisherOwnerNameInput');
        if (pubOwnerInput && pubOwnerInput.value.trim()) publisherOwnerName = pubOwnerInput.value.trim();
        established = document.getElementById('regEstablishedInput')?.value.trim() || '';
        tradeLicense = document.getElementById('regTradeLicenseInput')?.value.trim() || '';

    } else if (category === 'seller') {
        const shopInput = document.getElementById('regShopNameInput');
        if (shopInput && shopInput.value.trim()) shopName = shopInput.value.trim();
        sellerTradeLicense = document.getElementById('regSellerTradeLicenseInput')?.value.trim() || '';
    }

    const countryElem = document.getElementById('regCountrySelect');
    const country = countryElem && countryElem.value.trim() ? countryElem.value.trim() : 'Bangladesh';
    
    const isBD = (country === 'Bangladesh');
    const districtElem = isBD 
        ? document.getElementById('regDistrictSelect')
        : document.getElementById('regDistrictTextInput');
    const district = districtElem ? districtElem.value.trim() : '';

    const thanaElem = isBD
        ? document.getElementById('regThanaSelect')
        : document.getElementById('regThanaTextInput');
    const thana = thanaElem ? thanaElem.value.trim() : '';

    const postCodeElem = document.getElementById('regPostCodeInput');
    const postCode = postCodeElem ? postCodeElem.value.trim() : '';

    const addressElem = document.getElementById('regAddressInput');
    const address = addressElem ? addressElem.value.trim() : '';

    const btn = document.getElementById('btnFinishRegistration') || document.getElementById('btnVerifyMobileOtp');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Creating Account...';
    }

    const formData = new FormData();
    formData.append('_token', '{{ csrf_token() }}');
    formData.append('name', regData.name);
    formData.append('email', regData.email);
    formData.append('phone', regData.phone);
    formData.append('country_code', regData.countryCode || '+880');
    formData.append('password', regData.password);
    formData.append('category', category);
    formData.append('author_name', authorName);
    formData.append('author_name_en', authorNameEn);
    formData.append('author_name_bn', authorName);
    formData.append('pen_name', penName);
    formData.append('genres', genresStr);
    formData.append('bio', authorBio);
    formData.append('publishing_house_name', publishingHouseName);
    formData.append('publisher_name', publishingHouseName);
    formData.append('publisher_owner_name', publisherOwnerName);
    formData.append('established', established);
    formData.append('trade_license', tradeLicense || sellerTradeLicense);
    formData.append('shop_name', shopName);
    formData.append('nid', sellerTradeLicense);
    formData.append('country', country);
    formData.append('district', district);
    formData.append('thana', thana);
    formData.append('post_code', postCode);
    formData.append('address', address);
    formData.append('captcha_proof_token', regData.captcha_proof_token || '');

    try {
        const res = await fetchWithCsrfRetry('{{ route("register.complete") }}', {
            method: 'POST',
            body: formData
        });

        const data = await res.json();
        if (res.ok && data.success) {
            try { sessionStorage.removeItem('idea_reg_state'); } catch(e) {}
            window.location.href = data.redirect_url || '{{ route("my-account") }}';
            return;
        }
        showAlert(data.message || 'Registration failed. Please check your information.');
    } catch(err) {
        console.error('Registration submission error:', err);
        showAlert('An unexpected network or server error occurred. Please try again.');
    } finally {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<span>Complete Registration &rsaquo;</span>';
        }
    }
}

/**
 * Standard Sign-in AJAX Form Handling
 */
document.addEventListener('DOMContentLoaded', function() {
    initDistrictDropdown();
    setupOtpInputs();

    // Check URL parameters for mode and category/role
    const urlParams = new URLSearchParams(window.location.search);
    const initialMode = urlParams.get('mode');
    const initialCategory = urlParams.get('category') || urlParams.get('role');
    if (initialCategory) {
        regData.category = initialCategory;
    }
    if (initialMode === 'register') {
        switchAuthMode('register');
    } else {
        // Pre-fetch CAPTCHA in idle time so there is 0ms delay when switching to register
        setTimeout(() => loadCaptchaChallenge(), 1000);
    }
    if (initialCategory) {
        if (typeof selectUnifiedRole === 'function') {
            selectUnifiedRole(initialCategory);
        }
        const categorySelect = document.getElementById('regCategorySelect');
        if (categorySelect) {
            categorySelect.value = initialCategory;
            if (typeof onRegistrationCategoryChange === 'function') {
                onRegistrationCategoryChange(initialCategory);
            }
        }
        updateCategoryBanner();
    }

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
                const response = await fetchWithCsrfRetry(form.action, {
                    method: 'POST',
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
