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
        }

        .auth-heading {
            font-size: 26px;
            font-weight: 500;
            line-height: 1.2;
            color: #0f1111;
            margin-bottom: 12px;
            letter-spacing: -0.3px;
        }

        .auth-subtext {
            font-size: 13px;
            line-height: 1.5;
            color: #333333;
            margin-bottom: 16px;
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
        <h1 class="auth-heading">Create new password</h1>
        <p class="auth-subtext">We'll ask for this password whenever you sign in.</p>

        @if(isset($errors) && $errors->any())
            <div class="alert alert-danger py-2 px-3 small rounded-2 mb-3 border-0 bg-danger bg-opacity-10 text-danger">
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('password.update') }}">
            @csrf

            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="email" value="{{ old('email', $email ?? '') }}">

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
                @error('password')
                    <div class="text-danger small mt-1" style="font-size: 11.5px;">{{ $message }}</div>
                @enderror
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

            <button type="submit" class="btn-action-primary">
                Save changes and sign in
            </button>
        </form>
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
