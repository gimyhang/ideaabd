<!DOCTYPE html>
<html lang="bn" class="notranslate" translate="no">
<head>
    <meta charset="UTF-8">
    <meta name="google" content="notranslate">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>লগইন — আইডিয়া প্রকাশন ডিজিটাল পোর্টাল</title>

    {{-- Favicon --}}
    @php $siteFaviconUrl = \App\Support\SiteSetting::faviconUrl(); @endphp
    @if ($siteFaviconUrl)
        <link rel="icon" href="{{ $siteFaviconUrl }}">
    @else
        <link rel="icon" href="{{ asset('favicon.ico') }}">
    @endif

    {{-- Fonts: Kalpurush, Nikosh, Hind Siliguri, Noto Sans Bengali & Inter --}}
    <link href="https://fonts.maateen.me/kalpurush/font.css" rel="stylesheet">
    <link href="https://fonts.maateen.me/nikosh/font.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&family=Noto+Sans+Bengali:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome 6 Pro / Free -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Google reCAPTCHA v2 Script -->
    <script src="https://www.google.com/recaptcha/api.js?onload=onRecaptchaLoaded&render=explicit" async defer></script>

    <style>
        :root {
            --brand-dark-green: #004d40;
            --brand-primary-green: #006a4e;
            --brand-light-green: #00875a;
            --brand-accent-red: #da291c;
            --brand-gold: #f59e0b;
            --brand-input-bg: #f8fafc;
            --brand-border: #cbd5e1;
        }

        * {
            box-sizing: border-box;
            font-family: 'Kalpurush', 'Nikosh', 'Hind Siliguri', 'Noto Sans Bengali', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            background-color: #f8fafc;
            color: #0f172a;
            display: flex;
            flex-direction: column;
        }

        /* ══════════════════════════════════════════════════════════════════
           1. CLEAN, MODERN & ELEGANT PAGE BACKGROUND
           ══════════════════════════════════════════════════════════════════ */
        .portal-wrapper {
            flex: 1 0 auto;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 100vh;
            background: linear-gradient(180deg, #f1f5f9 0%, #f8fafc 50%, #eef2f6 100%);
            position: relative;
        }

        /* Subtle ambient glow */
        .portal-wrapper::before {
            content: "";
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: radial-gradient(circle at 50% 30%, rgba(0, 106, 78, 0.04) 0%, transparent 65%);
            pointer-events: none;
            z-index: 1;
        }

        /* ══════════════════════════════════════════════════════════════════
           2. HEADER & TOPBAR (DARK GREEN WITH GOLD ACCENT)
           ══════════════════════════════════════════════════════════════════ */
        .portal-header {
            background: linear-gradient(90deg, #004d40 0%, #006a4e 60%, #00573f 100%);
            color: #ffffff;
            border-bottom: 3px solid var(--brand-gold);
            box-shadow: 0 4px 15px rgba(0, 77, 64, 0.2);
            position: relative;
            z-index: 10;
        }

        .portal-nav-link {
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            font-size: 13.5px;
            font-weight: 500;
            padding: 6px 12px;
            border-radius: 6px;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .portal-nav-link:hover {
            color: #ffffff;
            background-color: rgba(255, 255, 255, 0.15);
            transform: translateY(-1px);
        }

        /* ══════════════════════════════════════════════════════════════════
           3. CENTERED LOGIN CARD (CLEAN WHITE ON CENTERED AXIS)
           ══════════════════════════════════════════════════════════════════ */
        .login-main-container {
            position: relative;
            z-index: 5;
            padding: 40px 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-grow: 1;
        }

        .brand-login-card {
            background: #ffffff;
            width: 100%;
            max-width: 470px;
            border-radius: 16px;
            border: 1px solid rgba(0, 106, 78, 0.16);
            box-shadow: 0 20px 45px -15px rgba(15, 23, 42, 0.12), 0 0 0 1px rgba(0, 0, 0, 0.03);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .brand-card-top-strip {
            height: 5px;
            background: linear-gradient(90deg, var(--brand-accent-red) 0%, var(--brand-gold) 35%, var(--brand-primary-green) 100%);
        }

        .brand-card-header {
            padding: 24px 28px 16px 28px;
            display: flex;
            align-items: center;
            gap: 16px;
            border-bottom: 1px solid #f1f5f9;
        }

        .brand-emblem-circle {
            width: 52px;
            height: 52px;
            min-width: 52px;
            border-radius: 50%;
            background: linear-gradient(135deg, #da291c 0%, #b91c1c 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(218, 41, 28, 0.28);
            border: 2px solid #ffffff;
        }

        .brand-card-body {
            padding: 24px 28px 28px 28px;
        }

        /* Input Controls */
        .brand-form-label {
            font-size: 13.5px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 6px;
            display: block;
        }

        .brand-form-label .required-star {
            color: var(--brand-accent-red);
            font-weight: bold;
            margin-left: 2px;
        }

        .brand-input-group {
            position: relative;
            display: flex;
            align-items: stretch;
            width: 100%;
        }

        .brand-input-icon {
            background-color: var(--brand-input-bg);
            border: 1.5px solid var(--brand-border);
            border-right: none;
            border-radius: 10px 0 0 10px;
            padding: 0 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #64748b;
            font-size: 15px;
        }

        .brand-form-control {
            background-color: var(--brand-input-bg);
            border: 1.5px solid var(--brand-border);
            border-radius: 0 10px 10px 0;
            padding: 12px 14px;
            font-size: 14.5px;
            color: #0f172a;
            font-weight: 500;
            width: 100%;
            transition: all 0.2s ease;
        }

        .brand-form-control:focus {
            background-color: #ffffff;
            border-color: var(--brand-primary-green);
            outline: none;
            box-shadow: 0 0 0 3px rgba(0, 106, 78, 0.15);
        }

        .brand-form-control.has-toggle {
            border-radius: 0;
        }

        .brand-toggle-btn {
            background-color: var(--brand-input-bg);
            border: 1.5px solid var(--brand-border);
            border-left: none;
            border-radius: 0 10px 10px 0;
            padding: 0 14px;
            color: #64748b;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }

        .brand-toggle-btn:hover {
            color: var(--brand-primary-green);
            background-color: #e2e8f0;
        }

        /* Action Buttons */
        .btn-brand-primary {
            background: linear-gradient(135deg, #006a4e 0%, #004d40 100%);
            color: #ffffff;
            border: none;
            border-radius: 10px;
            padding: 12px 20px;
            font-size: 16px;
            font-weight: 700;
            width: 100%;
            letter-spacing: 0.3px;
            box-shadow: 0 4px 12px rgba(0, 106, 78, 0.25);
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-brand-primary:hover:not(:disabled) {
            background: linear-gradient(135deg, #00875a 0%, #00573f 100%);
            color: #ffffff;
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(0, 106, 78, 0.35);
        }

        .btn-brand-secondary {
            background-color: #f1f5f9;
            color: #334155;
            border: 1.5px solid #cbd5e1;
            border-radius: 10px;
            padding: 10px 18px;
            font-size: 14px;
            font-weight: 600;
            width: 100%;
            transition: all 0.2s ease;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-brand-secondary:hover {
            background-color: #e2e8f0;
            color: #0f172a;
            border-color: #94a3b8;
            text-decoration: none;
        }

        /* Captcha Frame */
        .brand-captcha-box {
            background: #f8fafc;
            border: 1.5px dashed #cbd5e1;
            border-radius: 12px;
            padding: 12px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        /* ══════════════════════════════════════════════════════════════════
           4. FOOTER BAR (DARK GREEN)
           ══════════════════════════════════════════════════════════════════ */
        .portal-footer {
            background: #00382f;
            color: rgba(255, 255, 255, 0.85);
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding: 14px 20px;
            font-size: 12.5px;
            position: relative;
            z-index: 10;
        }

        .portal-footer-link {
            color: rgba(255, 255, 255, 0.75);
            text-decoration: none;
            transition: color 0.2s;
        }

        .portal-footer-link:hover {
            color: var(--brand-gold);
            text-decoration: underline;
        }

        /* Role Quick Grid */
        .role-badge-link {
            padding: 8px 10px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            text-decoration: none;
            color: #334155;
            font-size: 12.5px;
            font-weight: 600;
            display: flex;
            flex-direction: column;
            align-items: center;
            transition: all 0.2s;
        }
        .role-badge-link:hover {
            background: #ecfdf5;
            border-color: var(--brand-primary-green);
            color: var(--brand-primary-green);
            transform: translateY(-2px);
        }

        /* Responsive Tweaks */
        @media (max-width: 576px) {
            .brand-card-header {
                padding: 18px 20px 14px 20px;
            }
            .brand-card-body {
                padding: 18px 20px 22px 20px;
            }
            .portal-header-title h5 {
                font-size: 15px !important;
            }
            .portal-header-title small {
                font-size: 11px !important;
            }
        }
    </style>
</head>
<body>

<div class="portal-wrapper">
    {{-- ========================================================================= --}}
    {{-- 1. HEADER & TOPBAR (DARK GREEN WITH GOLD ACCENT) --}}
    {{-- ========================================================================= --}}
    <header class="portal-header py-2.5 px-3 px-lg-4">
        <div class="container d-flex align-items-center justify-content-between flex-wrap gap-2.5">
            {{-- Left Side: Idea Prokashon Logo and Portal Title --}}
            <a href="{{ url('/') }}" class="d-flex align-items-center text-decoration-none text-white gap-2.5" title="হোম পেইজে যান">
                <div class="bg-white rounded-circle p-1 d-flex align-items-center justify-content-center shadow-sm" style="width: 44px; height: 44px; flex-shrink: 0;">
                    <img src="{{ \App\Support\SiteSetting::loginLogoUrl() ?: \App\Support\SiteSetting::logoUrl() }}" alt="Idea Prokashon" class="w-100 h-100 object-fit-contain" 
                         onerror="this.src='{{ asset('images/logo.png') }}';">
                </div>
                <div class="portal-header-title d-flex flex-column justify-content-center lh-sm">
                    <h5 class="fw-bold mb-0 text-white" style="font-size: 16.5px; letter-spacing: 0.2px;">
                        আইডিয়া প্রকাশন ডিজিটাল পোর্টাল
                    </h5>
                    <small class="text-white-50" style="font-size: 11.5px;">
                        অনলাইন বই ও প্রকাশনা ডিজিটাল প্ল্যাটফর্ম
                    </small>
                </div>
            </a>

            {{-- Right Side: Helpful Navigation Links --}}
            <nav class="d-flex align-items-center gap-1 gap-md-2 flex-wrap ms-auto">
                <a href="{{ url('/') }}" class="portal-nav-link">
                    <i class="fa-solid fa-house-chimney"></i> <span>Home</span>
                </a>
                <a href="{{ url('/documents') }}" class="portal-nav-link">
                    <i class="fa-solid fa-file-lines"></i> <span>Documents</span>
                </a>
                <a href="{{ url('/faq') }}" class="portal-nav-link">
                    <i class="fa-solid fa-circle-question"></i> <span>FAQ</span>
                </a>
                <a href="{{ url('/contact') }}" class="portal-nav-link">
                    <i class="fa-solid fa-book-bookmark"></i> <span>Manual & Help</span>
                </a>
            </nav>
        </div>
    </header>

    {{-- ========================================================================= --}}
    {{-- 2. CENTERED LOGIN CARD --}}
    {{-- ========================================================================= --}}
    <main class="login-main-container">
        <div class="brand-login-card">
            <div class="brand-card-top-strip"></div>

            {{-- Card Header: Main Site Logo (Round Shape) + Bold Login Title & Subtitle --}}
            <div class="brand-card-header">
                <div class="bg-white rounded-circle p-1.5 border shadow-2xs d-flex align-items-center justify-content-center overflow-hidden" style="width: 52px; height: 52px; flex-shrink: 0; border-color: rgba(0, 106, 78, 0.2) !important;">
                    <img src="{{ \App\Support\SiteSetting::loginLogoUrl() ?: \App\Support\SiteSetting::logoUrl() }}" 
                         alt="{{ \App\Support\SiteSetting::name() }}" 
                         class="w-100 h-100 object-fit-contain rounded-circle"
                         onerror="this.src='{{ asset('images/logo.png') }}';">
                </div>
                <div>
                    <h4 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2" style="font-size: 21px; letter-spacing: -0.2px;">
                        <span>Login / লগইন</span>
                    </h4>
                    <small class="text-muted fw-medium d-block mt-0.5" style="font-size: 13px;">
                        মোবাইল নম্বর ও পাসওয়ার্ড দিয়ে লগইন করুন
                    </small>
                </div>
            </div>

            <div class="brand-card-body">
                {{-- Inline Error / Alert Box --}}
                <div id="loginAlertBox" class="alert alert-danger py-2.5 px-3 rounded-3 small mb-3 border-0 bg-danger bg-opacity-10 text-danger {{ (isset($errors) && $errors->any()) || session('error') ? '' : 'd-none' }}">
                    <div class="fw-bold mb-1 d-flex align-items-center gap-1.5" id="alertTitle">
                        <i class="fa-solid fa-triangle-exclamation text-danger"></i>
                        <span>লগইন সতর্কতা:</span>
                    </div>
                    <div id="alertMessage">
                        @if(isset($errors) && $errors->any())
                            <ul class="mb-0 ps-3">
                                @foreach($errors->all() as $e)
                                    <li>{{ $e }}</li>
                                @endforeach
                            </ul>
                        @elseif(session('error'))
                            {{ session('error') }}
                        @endif
                    </div>
                </div>

                @if(session('status'))
                    <div class="alert alert-success py-2.5 px-3 rounded-3 small mb-3 border-0 bg-success bg-opacity-10 text-success">
                        <i class="fa-solid fa-circle-check me-1"></i> {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" id="loginForm" autocomplete="on">
                    @csrf

                    {{-- Invisible Anti-Bot Honeypot --}}
                    <div style="display:none !important; visibility:hidden; position:absolute; left:-9999px;" aria-hidden="true">
                        <input type="text" name="website_url_hp" tabindex="-1" autocomplete="off">
                        <input type="checkbox" name="b_check_field" tabindex="-1" autocomplete="off">
                    </div>

                    {{-- Hidden Google reCAPTCHA Token Input --}}
                    <input type="hidden" name="g-recaptcha-response" id="gRecaptchaResponseInput" value="">

                    {{-- 1. Mobile Number Input --}}
                    <div class="mb-3">
                        <label class="brand-form-label" for="loginEmailInput">
                            মোবাইল নম্বর: <span class="required-star">*</span>
                        </label>
                        <div class="brand-input-group">
                            <span class="brand-input-icon"><i class="fa-solid fa-phone"></i></span>
                            <input type="text" name="email" id="loginEmailInput" class="brand-form-control" 
                                   value="{{ old('email') }}" placeholder="আপনার মোবাইল নম্বর লিখুন..." 
                                   required autofocus autocomplete="username"
                                   autocorrect="off" autocapitalize="none" spellcheck="false">
                        </div>
                    </div>

                    {{-- 2. Password Field with Toggle --}}
                    <div class="mb-3">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <label class="brand-form-label mb-0" for="loginPasswordInput">
                                পাসওয়ার্ড: <span class="required-star">*</span>
                            </label>
                            <a href="{{ route('password.request') }}" class="text-decoration-none fw-semibold" style="color: var(--brand-primary-green); font-size: 12.5px;">
                                <i class="fa-solid fa-key me-0.5"></i> Forgot Password?
                            </a>
                        </div>
                        <div class="brand-input-group">
                            <span class="brand-input-icon"><i class="fa-solid fa-lock"></i></span>
                            <input type="password" name="password" id="loginPasswordInput" class="brand-form-control has-toggle" 
                                   placeholder="আপনার পাসওয়ার্ড লিখুন..." required autocomplete="current-password">
                            <button type="button" class="brand-toggle-btn" id="togglePasswordBtn" onclick="togglePasswordVisibility()" title="পাসওয়ার্ড দেখুন বা লুকান">
                                <i class="fa-regular fa-eye" id="togglePasswordIcon"></i>
                            </button>
                        </div>
                        {{-- Remember Me Helper --}}
                        <div class="d-flex align-items-center justify-content-end mt-1.5">
                            <div class="form-check">
                                <input type="checkbox" name="remember" id="remember" class="form-check-input" value="1" @checked(old('remember'))>
                                <label for="remember" class="form-check-label text-muted cursor-pointer" style="font-size: 12px;">Remember Me</label>
                            </div>
                        </div>
                    </div>

                    {{-- 3. Google reCAPTCHA v2 (Shown automatically only if triggered by security threshold) --}}
                    <div id="captchaWrapper" class="{{ ($requiresCaptcha ?? false) ? '' : 'd-none' }} mb-3.5">
                        <label class="brand-form-label mb-1">
                            Security Verification: <span class="required-star">*</span>
                        </label>
                        <div class="brand-captcha-box">
                            <div id="googleRecaptchaInlineWidget"></div>
                            <div id="captchaInlineFeedback" class="small fw-semibold text-muted mt-1" style="font-size: 11.5px;">
                                <i class="fa-solid fa-shield-check text-success me-1"></i> Google 256-bit reCAPTCHA Protected
                            </div>
                        </div>
                    </div>

                    {{-- 4. Action Buttons --}}
                    <div class="d-flex flex-column gap-2 mt-3 pt-1">
                        {{-- Primary Login Button (Full-width Dark Green - English Only) --}}
                        <button type="submit" class="btn-brand-primary" id="loginSubmitBtn">
                            <i class="fa-solid fa-right-to-bracket"></i> Login
                        </button>

                        {{-- Secondary Button (Reset Password - Concise English with Tooltip) --}}
                        <a href="{{ route('password.request') }}" class="btn-brand-secondary" title="পাসওয়ার্ড ভুলে গেলে রিসেট করুন">
                            <i class="fa-solid fa-key text-secondary"></i> Reset Password
                        </a>
                    </div>
                </form>

                {{-- Sign Up / Registration Options --}}
                <div class="mt-4 pt-3 border-top">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="fw-bold text-dark" style="font-size: 12.5px;">
                            <i class="fa-solid fa-user-plus text-success me-1"></i> নতুন অ্যাকাউন্ট তৈরি করুন:
                        </span>
                        <a href="{{ route('register.choose') }}" class="text-decoration-none fw-bold" style="color: var(--brand-primary-green); font-size: 12px;">
                            সবগুলো <i class="fa-solid fa-angle-right"></i>
                        </a>
                    </div>
                    <div class="row g-1.5 text-center">
                        <div class="col-3">
                            <a href="{{ route('register.form', 'author') }}" class="role-badge-link">
                                <i class="fa-solid fa-feather-pointed text-success mb-1"></i>
                                <span>লেখক</span>
                            </a>
                        </div>
                        <div class="col-3">
                            <a href="{{ route('register.form', 'buyer') }}" class="role-badge-link">
                                <i class="fa-solid fa-bag-shopping text-warning mb-1"></i>
                                <span>পাঠক</span>
                            </a>
                        </div>
                        <div class="col-3">
                            <a href="{{ route('register.form', 'publisher') }}" class="role-badge-link">
                                <i class="fa-solid fa-building text-danger mb-1"></i>
                                <span>প্রকাশক</span>
                            </a>
                        </div>
                        <div class="col-3">
                            <a href="{{ route('register.form', 'seller') }}" class="role-badge-link">
                                <i class="fa-solid fa-store text-primary mb-1"></i>
                                <span>সেলার</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    {{-- ========================================================================= --}}
    {{-- 3. FOOTER BAR (DARK GREEN) --}}
    {{-- ========================================================================= --}}
    <footer class="portal-footer">
        <div class="container-fluid d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                © ২০২৬ আইডিয়া প্রকাশন । ডিজিটাল বুক ও প্রকাশনা প্ল্যাটফর্ম । সর্বস্বত্ব সংরক্ষিত।
            </div>
            <div class="d-flex align-items-center gap-3">
                <a href="{{ url('/terms') }}" class="portal-footer-link">ব্যবহারের শর্তাবলী (Terms)</a>
                <span class="text-white-50">•</span>
                <a href="{{ url('/privacy') }}" class="portal-footer-link">গোপনীয়তা নীতি (Privacy Policy)</a>
                <span class="text-white-50">•</span>
                <a href="{{ url('/contact') }}" class="portal-footer-link">যোগাযোগ ও সহায়তা (Help & Contact)</a>
            </div>
        </div>
    </footer>
</div>

<script>
let recaptchaWidgetId = null;
let isRecaptchaReady = false;
let recaptchaSiteKey = '{{ $recaptchaSiteKey ?? "6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI" }}';

/**
 * Toggle Password Visibility with Eye Icon
 */
function togglePasswordVisibility() {
    const input = document.getElementById('loginPasswordInput');
    const icon = document.getElementById('togglePasswordIcon');
    const checkbox = document.getElementById('showPasswordCheckbox');

    if (input.type === 'password') {
        input.type = 'text';
        if (icon) {
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        }
        if (checkbox) checkbox.checked = true;
    } else {
        input.type = 'password';
        if (icon) {
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
        if (checkbox) checkbox.checked = false;
    }
}

function syncPasswordCheckbox(cb) {
    const input = document.getElementById('loginPasswordInput');
    const icon = document.getElementById('togglePasswordIcon');
    if (cb.checked) {
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
 * Google reCAPTCHA explicit onload callback
 */
window.onRecaptchaLoaded = function() {
    isRecaptchaReady = true;
    renderGoogleRecaptcha();
};

function renderGoogleRecaptcha() {
    const container = document.getElementById('googleRecaptchaInlineWidget');
    if (container && window.grecaptcha && isRecaptchaReady && recaptchaWidgetId === null) {
        try {
            recaptchaWidgetId = window.grecaptcha.render('googleRecaptchaInlineWidget', {
                'sitekey': recaptchaSiteKey,
                'callback': onCaptchaSuccessCallback,
                'expired-callback': onCaptchaExpiredCallback,
                'error-callback': onCaptchaErrorCallback,
                'theme': 'light'
            });
        } catch (e) {
            console.error('reCAPTCHA render error', e);
        }
    }
}

function onCaptchaSuccessCallback(token) {
    const tokenInput = document.getElementById('gRecaptchaResponseInput');
    const feedback = document.getElementById('captchaInlineFeedback');
    if (tokenInput) tokenInput.value = token;
    if (feedback) {
        feedback.className = 'small fw-bold text-success mt-1';
        feedback.innerHTML = '<i class="fa-solid fa-circle-check me-1"></i> Verification successful!';
    }
}

function onCaptchaExpiredCallback() {
    const tokenInput = document.getElementById('gRecaptchaResponseInput');
    const feedback = document.getElementById('captchaInlineFeedback');
    if (tokenInput) tokenInput.value = '';
    if (feedback) {
        feedback.className = 'small fw-semibold text-danger mt-1';
        feedback.innerHTML = '<i class="fa-solid fa-triangle-exclamation me-1"></i> Verification expired. Please verify again.';
    }
}

function onCaptchaErrorCallback() {
    const feedback = document.getElementById('captchaInlineFeedback');
    if (feedback) {
        feedback.className = 'small fw-semibold text-danger mt-1';
        feedback.innerHTML = '<i class="fa-solid fa-circle-exclamation me-1"></i> Verification error. Please try again.';
    }
}

/**
 * Form Submit with Client-side validation & AJAX handling
 */
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('loginForm');
    const submitBtn = document.getElementById('loginSubmitBtn');
    const alertBox = document.getElementById('loginAlertBox');
    const alertMessage = document.getElementById('alertMessage');
    const tokenInput = document.getElementById('gRecaptchaResponseInput');

    if (form) {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();

            const emailInput = document.getElementById('loginEmailInput');
            const passwordInput = document.getElementById('loginPasswordInput');

            if (!emailInput.value.trim() || !passwordInput.value) {
                if (alertBox && alertMessage) {
                    alertMessage.textContent = 'মোবাইল নম্বর এবং পাসওয়ার্ড দিন।';
                    alertBox.classList.remove('d-none');
                }
                return;
            }

            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1.5" role="status"></span> Logging in...';
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
                    if (alertBox) {
                        alertBox.className = 'alert alert-success py-2.5 px-3 rounded-3 small mb-3 border-0 bg-success bg-opacity-10 text-success';
                        alertMessage.innerHTML = '<i class="fa-solid fa-circle-check me-1"></i> ' + (data.message || 'লগইন সফল হয়েছে! ড্যাশবোর্ডে নিয়ে যাওয়া হচ্ছে...');
                        alertBox.classList.remove('d-none');
                    }
                    if (submitBtn) {
                        submitBtn.innerHTML = '<i class="fa-solid fa-circle-check me-1.5"></i> Logged In!';
                        submitBtn.className = 'btn-brand-primary bg-success';
                    }
                    window.location.href = data.redirect || '{{ route("home") }}';
                    return;
                }

                // Error handling
                const msg = data.message || 'মোবাইল নম্বর বা পাসওয়ার্ড সঠিক নয়।';
                if (alertBox && alertMessage) {
                    alertBox.className = 'alert alert-danger py-2.5 px-3 rounded-3 small mb-3 border-0 bg-danger bg-opacity-10 text-danger';
                    alertMessage.textContent = msg;
                    alertBox.classList.remove('d-none');
                }

                if (data.show_captcha) {
                    const captchaWrapper = document.getElementById('captchaWrapper');
                    if (captchaWrapper) {
                        captchaWrapper.classList.remove('d-none');
                        renderGoogleRecaptcha();
                    }
                }

                // Reset reCAPTCHA on failure
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
                    submitBtn.innerHTML = '<i class="fa-solid fa-right-to-bracket"></i> Login';
                }
            }
        });
    }
});
</script>
</body>
</html>
