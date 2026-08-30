<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="theme-color" content="#0066cc">
    <meta name="color-scheme" content="light">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', \App\Support\SiteSetting::name() . ' — ' . \App\Support\SiteSetting::tagline())</title>

    {{-- Universal Social Media Open Graph (Facebook, WhatsApp, LinkedIn) & Twitter / X Cards --}}
    @php
        $defaultSiteName = \App\Support\SiteSetting::name() ?: 'আইডিয়া প্রকাশন';
        $defaultSiteTagline = \App\Support\SiteSetting::tagline() ?: 'অনলাইন বই ও প্রকাশনা প্ল্যাটফর্ম';
        $defaultBanner = \App\Support\SiteSetting::blogOgBannerUrl() ?: asset('images/og-banner.jpg');
        if (!str_starts_with($defaultBanner, 'http')) {
            $defaultBanner = url($defaultBanner);
        }

        $metaPageTitle = View::hasSection('og_title') ? View::getSection('og_title') : (View::hasSection('title') ? View::getSection('title') : $defaultSiteName);
        $metaPageDescription = View::hasSection('og_description') ? View::getSection('og_description') : (View::hasSection('meta_description') ? View::getSection('meta_description') : $defaultSiteTagline);
        
        $candidateImage = View::hasSection('og_image') ? View::getSection('og_image') : null;
        if (empty($candidateImage) || str_ends_with(strtolower($candidateImage), '.svg') || str_starts_with($candidateImage, 'data:')) {
            $metaPageImage = $defaultBanner;
        } else {
            $metaPageImage = str_starts_with($candidateImage, 'http') ? $candidateImage : url($candidateImage);
        }

        // Canonical URL resolution (Google Search Console 'Duplicate without user-selected canonical' fix)
        $canonicalDomain = rtrim(config('app.url', 'https://www.ideaabd.com'), '/');
        if (str_contains($canonicalDomain, 'localhost') || str_contains($canonicalDomain, '127.0.0.1')) {
            $canonicalDomain = 'https://www.ideaabd.com';
        }

        if (View::hasSection('canonical')) {
            $rawCanonical = trim(View::getSection('canonical'));
        } elseif (View::hasSection('og_url')) {
            $rawCanonical = trim(View::getSection('og_url'));
        } else {
            $reqPath = request()->path();
            $rawCanonical = ($reqPath === '/' || $reqPath === '') ? $canonicalDomain . '/' : $canonicalDomain . '/' . ltrim($reqPath, '/');
            if (request()->filled('page') && (int)request('page') > 1) {
                $rawCanonical .= '?page=' . (int)request('page');
            }
        }

        // Standardize HTTPS and canonical domain
        if (!str_starts_with($rawCanonical, 'http')) {
            $rawCanonical = $canonicalDomain . '/' . ltrim($rawCanonical, '/');
        } else {
            $parsed = parse_url($rawCanonical);
            $parsedPath = $parsed['path'] ?? '/';
            $parsedQuery = isset($parsed['query']) ? '?' . $parsed['query'] : '';
            $rawCanonical = $canonicalDomain . ($parsedPath === '/' ? '' : $parsedPath) . $parsedQuery;
            if ($rawCanonical === $canonicalDomain) {
                $rawCanonical = $canonicalDomain . '/';
            }
        }

        $metaPageUrl = $rawCanonical;
        $metaPageType = View::hasSection('og_type') ? View::getSection('og_type') : 'website';

        $imageExt = strtolower(pathinfo(parse_url($metaPageImage, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION));
        $imageMime = ($imageExt === 'png') ? 'image/png' : (($imageExt === 'webp') ? 'image/webp' : 'image/jpeg');
    @endphp

    <meta name="description" content="{{ Str::limit(strip_tags($metaPageDescription), 220) }}">
    <meta name="keywords" content="@yield('meta_keywords', 'আইডিয়া প্রকাশন, বাংলা বই, ইবুক, সাহিত্য, ব্লগ, কবিতা, প্রবন্ধ, প্রকাশনা, বই অর্ডার, অনলাইন বই মেলা, Idea Publication, Bangla Books, Ebooks, Publishers, Research, Webzine')">
    <meta name="author" content="@yield('meta_author', $defaultSiteName)">
    <meta name="publisher" content="{{ $defaultSiteName }}">
    <meta name="copyright" content="© {{ date('Y') }} {{ $defaultSiteName }}. All Rights Reserved.">
    <meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">
    <meta name="googlebot" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">
    <meta name="bingbot" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">
    <meta name="revisit-after" content="1 days">
    <meta name="rating" content="general">
    <meta name="distribution" content="global">

    <!-- Geographic & Regional Search Targeting -->
    <meta name="geo.region" content="BD">
    <meta name="geo.placename" content="Dhaka, Bangladesh">
    <meta name="geo.position" content="23.8103;90.4125">
    <meta name="ICBM" content="23.8103, 90.4125">

    <!-- Dublin Core Metadata for Academic & Universal Search -->
    <meta name="DC.title" content="{{ $metaPageTitle }}">
    <meta name="DC.creator" content="{{ $defaultSiteName }}">
    <meta name="DC.description" content="{{ Str::limit(strip_tags($metaPageDescription), 200) }}">
    <meta name="DC.publisher" content="{{ $defaultSiteName }}">
    <meta name="DC.language" content="bn">

    <!-- Canonical & Language Alternates -->
    <link rel="canonical" href="{{ $metaPageUrl }}">
    <link rel="alternate" hreflang="bn" href="{{ $metaPageUrl }}">
    <link rel="alternate" hreflang="en" href="{{ $metaPageUrl }}">
    <link rel="alternate" hreflang="x-default" href="{{ $metaPageUrl }}">
    <link rel="alternate" type="application/rss+xml" title="{{ $defaultSiteName }} RSS Feed" href="{{ url('/feed') }}">

    <!-- Open Graph / Facebook / WhatsApp / LinkedIn -->
    <meta property="og:locale" content="bn_BD">
    <meta property="og:type" content="{{ $metaPageType }}">
    <meta property="og:url" content="{{ $metaPageUrl }}">
    <meta property="og:title" content="{{ $metaPageTitle }}">
    <meta property="og:description" content="{{ Str::limit(strip_tags($metaPageDescription), 220) }}">
    <meta property="og:image" content="{{ $metaPageImage }}">
    <meta property="og:image:secure_url" content="{{ $metaPageImage }}">
    <meta property="og:image:type" content="{{ $imageMime }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="{{ $metaPageTitle }}">
    <meta property="og:site_name" content="{{ $defaultSiteName }}">

    <!-- Twitter / X Cards with Large Image Focus -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="{{ $metaPageUrl }}">
    <meta name="twitter:title" content="{{ $metaPageTitle }}">
    <meta name="twitter:description" content="{{ Str::limit(strip_tags($metaPageDescription), 220) }}">
    <meta name="twitter:image" content="{{ $metaPageImage }}">
    <meta name="twitter:image:alt" content="{{ $metaPageTitle }}">

    {{-- Universal Google JSON-LD Schema.org Structured Data --}}
    <script type="application/ld+json">
    {
      "@@context": "https://schema.org",
      "@@graph": [
        {
          "@@type": "Organization",
          "@@id": "{{ $canonicalDomain }}/#organization",
          "name": "{{ $defaultSiteName }}",
          "url": "{{ $canonicalDomain }}",
          "logo": {
            "@@type": "ImageObject",
            "url": "{{ asset('images/logo.png') }}",
            "caption": "{{ $defaultSiteName }}"
          },
          "sameAs": [
            "https://www.facebook.com/ideaprokashon"
          ],
          "contactPoint": {
            "@@type": "ContactPoint",
            "telephone": "+8801726976982",
            "contactType": "customer service",
            "areaServed": "BD",
            "availableLanguage": ["bn", "en"]
          }
        },
        {
          "@@type": "WebSite",
          "@@id": "{{ $canonicalDomain }}/#website",
          "url": "{{ $canonicalDomain }}",
          "name": "{{ $defaultSiteName }}",
          "description": "{{ $defaultSiteTagline }}",
          "publisher": {
            "@@id": "{{ $canonicalDomain }}/#organization"
          },
          "potentialAction": {
            "@@type": "SearchAction",
            "target": {
              "@@type": "EntryPoint",
              "urlTemplate": "{{ $canonicalDomain }}/search?q={search_term_string}"
            },
            "query-input": "required name=search_term_string"
          }
        }
      ]
    }
    </script>
    @yield('schema_json')
    @stack('schema')

    {{-- Dynamic Site Favicon --}}
    @php $siteFaviconUrl = \App\Support\SiteSetting::faviconUrl(); @endphp
    @if ($siteFaviconUrl)
        <link rel="icon" href="{{ $siteFaviconUrl }}">
        <link rel="apple-touch-icon" href="{{ $siteFaviconUrl }}">
    @else
        <link rel="icon" href="{{ asset('favicon.ico') }}">
    @endif

    <!-- Google AdSense Official Script -->
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-4534355865737776" crossorigin="anonymous"></script>

    <!-- Google AdSense AMP Auto Ads Script -->
    <script async custom-element="amp-auto-ads"
            src="https://cdn.ampproject.org/v0/amp-auto-ads-0.1.js">
    </script>
    
    <!-- Fonts: Kalpurush, Nikosh, Hind Siliguri, Noto Sans Bengali & Inter -->
    <link href="https://fonts.maateen.me/kalpurush/font.css" rel="stylesheet">
    <link href="https://fonts.maateen.me/nikosh/font.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700&family=Noto+Sans+Bengali:wght@400;500;600;700;800&family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Site chrome (header/nav/footer) — served from /public, no build step needed -->
    <link rel="stylesheet" href="{{ asset('css/site.css') }}?v={{ @filemtime(public_path('css/site.css')) ?: 1 }}">
    
    <!-- Custom Styles -->
    <style>
        * {
            font-family: 'Kalpurush', 'Nikosh', 'Hind Siliguri', 'Noto Sans Bengali', sans-serif;
        }
        
        body {
            font-family: 'Kalpurush', 'Nikosh', 'Hind Siliguri', 'Noto Sans Bengali', sans-serif;
            font-size: 12px;
            background-color: #f8fafb;
            color: #333;
        }
        
        /* Light Sky Blue Theme */
        :root {
            --primary-light: #E8F4F8;
            --primary-dark: #0066cc;
            --primary-accent: #0099ff;
            --sky-blue: #87CEEB;
            --dark-blue: #1a3a52;
            --text-light: #666;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #0099ff 0%, #0066cc 100%) !important;
            border: none;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 102, 204, 0.3) !important;
        }
        
        .card {
            border: 1px solid rgba(0, 102, 204, 0.1);
            border-radius: 12px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }
        
        .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(0, 102, 204, 0.15);
        }
        
        .badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 500;
        }

        /* Hover utilities */
        .hover-lift { transition: transform .2s, box-shadow .2s; }
        .hover-lift:hover { transform: translateY(-4px); box-shadow: 0 8px 20px rgba(0,102,204,.15) !important; }
        .cat-card { transition: transform .2s, box-shadow .2s; }
        .cat-card:hover { transform: translateY(-4px); box-shadow: 0 8px 20px rgba(0,0,0,.1); }
        .author-card { transition: background .2s; }
        .author-card:hover { background: #ede8f5 !important; }
    </style>
    
    {{-- public/build is gitignored, so a git-only deploy may not have a manifest.
         Guarding this keeps the whole site from 500-ing when the assets are absent. --}}
    @if (file_exists(public_path('hot')) || file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body style="display: flex; flex-direction: column; min-height: 100vh;">
    <!-- Google AdSense AMP Auto Ads Unit -->
    <amp-auto-ads type="adsense"
            data-ad-client="ca-pub-4534355865737776">
    </amp-auto-ads>

    <!-- Header Navigation -->
    @include('layouts.header')

    <!-- Main Content -->
    <main class="flex-grow-1">
        @if (session('success'))
            <div class="container mt-3">
                <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm border-0 d-flex align-items-center gap-2 p-3" role="alert">
                    <i class="fa-solid fa-circle-check fs-4 text-success flex-shrink-0"></i>
                    <div class="fw-semibold">{{ session('success') }}</div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
            <script>
                // Clear localStorage cart on successful order
                try {
                    localStorage.removeItem('idea_cart');
                    if (typeof updateHeaderCartBadge === 'function') updateHeaderCartBadge();
                } catch(e) {}
            </script>
        @endif

        @if (session('error'))
            <div class="container mt-3">
                <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm border-0 d-flex align-items-center gap-2 p-3" role="alert">
                    <i class="fa-solid fa-circle-exclamation fs-4 text-danger flex-shrink-0"></i>
                    <div class="fw-semibold">{{ session('error') }}</div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer -->
    @include('layouts.footer')

    <!-- Global Cart Drawer -->
    @include('partials.cart-drawer')

    <!-- Bootstrap JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script>
        // Auto-dismiss top flash notification banners gently after 8 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const flashAlerts = document.querySelectorAll('main > .container > .alert.alert-dismissible');
            flashAlerts.forEach(alert => {
                setTimeout(() => {
                    try {
                        const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                        if (bsAlert) bsAlert.close();
                    } catch(e) {}
                }, 8000);
            });
        });
    </script>

    
    {{-- Google Translate Element (Hidden from view) --}}
    <div id="google_translate_element" style="display:none; position:absolute; left:-9999px;"></div>

    <script>
        // Google Translate Element Initialization
        function googleTranslateElementInit() {
            new google.translate.TranslateElement({
                pageLanguage: 'bn',
                includedLanguages: 'bn,en,ar,hi,ur,es,fr,de,zh-CN,ja,tr,ru,pt,it,ko,ms,fa',
                autoDisplay: false
            }, 'google_translate_element');
        }

        // Global Site Language Switcher
        window.switchSiteLanguage = function(langCode, langName) {
            function setCookie(name, value, days) {
                var expires = "";
                if (days) {
                    var date = new Date();
                    date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                    expires = "; expires=" + date.toUTCString();
                }
                var domain = window.location.hostname;
                document.cookie = name + "=" + (value || "") + expires + "; path=/;";
                document.cookie = name + "=" + (value || "") + expires + "; path=/; domain=" + domain;
                if (domain.indexOf('.') !== -1) {
                    document.cookie = name + "=" + (value || "") + expires + "; path=/; domain=." + domain;
                }
            }

            function deleteCookie(name) {
                var domain = window.location.hostname;
                document.cookie = name + "=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
                document.cookie = name + "=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/; domain=" + domain;
                if (domain.indexOf('.') !== -1) {
                    document.cookie = name + "=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/; domain=." + domain;
                }
            }

            if (langCode === 'bn') {
                deleteCookie('googtrans');
                setCookie('googtrans', '/bn/bn', 1);
                localStorage.setItem('site_selected_lang', 'bn');
                localStorage.setItem('site_selected_lang_name', 'বাংলা');
                fetch('/lang/bn').catch(function(){});
                window.location.reload();
            } else {
                setCookie('googtrans', '/bn/' + langCode, 30);
                localStorage.setItem('site_selected_lang', langCode);
                localStorage.setItem('site_selected_lang_name', langName || langCode);
                if (langCode === 'en') {
                    fetch('/lang/en').catch(function(){});
                }

                var select = document.querySelector('.goog-te-combo');
                if (select) {
                    select.value = langCode;
                    select.dispatchEvent(new Event('change'));
                    if (window.updateLanguageUI) {
                        window.updateLanguageUI(langCode, langName);
                    }
                } else {
                    window.location.reload();
                }
            }
        };

        window.updateLanguageUI = function(langCode, langName) {
            var displayEls = document.querySelectorAll('.current-lang-display');
            displayEls.forEach(function(el) {
                if (langName) el.textContent = langName;
            });

            document.querySelectorAll('.lang-check-icon').forEach(function(icon) {
                if (icon.getAttribute('data-lang') === langCode) {
                    icon.classList.remove('d-none');
                    var item = icon.closest('.lang-item-btn');
                    if (item) item.classList.add('active');
                } else {
                    icon.classList.add('d-none');
                    var item = icon.closest('.lang-item-btn');
                    if (item) item.classList.remove('active');
                }
            });
        };

        document.addEventListener('DOMContentLoaded', function() {
            var cookies = document.cookie.split(';');
            var currentLang = 'bn';
            var currentName = 'বাংলা';

            var langMap = {
                'bn': 'বাংলা',
                'en': 'English',
                'ar': 'العربية',
                'hi': 'हिन्दी',
                'ur': 'اردو',
                'es': 'Español',
                'fr': 'Français',
                'de': 'Deutsch',
                'zh-CN': '中文',
                'ja': '日本語',
                'tr': 'Türkçe',
                'ru': 'Русский'
            };

            for (var i = 0; i < cookies.length; i++) {
                var c = cookies[i].trim();
                if (c.indexOf('googtrans=') === 0) {
                    var val = c.substring('googtrans='.length);
                    var parts = val.split('/');
                    if (parts.length >= 3 && parts[2]) {
                        currentLang = parts[2];
                        currentName = langMap[currentLang] || currentLang.toUpperCase();
                    }
                }
            }

            if (currentLang === 'bn') {
                var localLang = localStorage.getItem('site_selected_lang');
                if (localLang && localLang !== 'bn') {
                    currentLang = localLang;
                    currentName = localStorage.getItem('site_selected_lang_name') || langMap[currentLang] || currentLang;
                }
            }

            window.updateLanguageUI(currentLang, currentName);
        });
    </script>
    <script src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit" async defer></script>
    
    {{-- Floating WhatsApp Quick Chat Button --}}
    <a href="https://wa.me/8801726976982?text={{ urlencode('আসসালামু আলাইকুম, আইডিয়া প্রকাশন থেকে তথ্য ও বই অর্ডার সংক্রান্ত সহায়তার জন্য যোগাযোগ করছি।') }}" 
       target="_blank" 
       rel="noopener" 
       class="floating-whatsapp-btn shadow-lg d-flex align-items-center justify-content-center text-decoration-none" 
       title="হোয়াটসঅ্যাপে সরাসরি যোগাযোগ করুন (+8801726976982)"
       style="position: fixed; bottom: 24px; right: 24px; width: 54px; height: 54px; background-color: #25D366; color: #ffffff; border-radius: 50%; z-index: 1050; box-shadow: 0 4px 14px rgba(37, 211, 102, 0.45); transition: transform 0.25s ease, box-shadow 0.25s ease; font-size: 28px;"
       onmouseover="this.style.transform='scale(1.1) rotate(5deg)'; this.style.color='#ffffff';"
       onmouseout="this.style.transform='scale(1) rotate(0deg)'; this.style.color='#ffffff';">
        <i class="fa-brands fa-whatsapp"></i>
    </a>

    {{-- Floating Copy Claim Notification Toast --}}
    <div id="copyClaimToast" style="position: fixed; bottom: 85px; right: 24px; z-index: 99999; background: rgba(15, 23, 42, 0.95); backdrop-filter: blur(10px); color: #ffffff; padding: 14px 20px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.15); display: none; font-size: 13px; font-weight: 500; max-width: 380px; animation: slideInUp 0.3s ease;">
        <div class="d-flex align-items-center gap-2">
            <i class="fa-solid fa-shield-halved text-warning fs-5"></i>
            <div>
                <strong class="d-block text-warning" style="font-size: 13.5px;">আইডিয়া প্রকাশন • কপিরাইট নোটিশ</strong>
                <span>লেখাটি কপি করা হয়েছে! মেধা-স্বত্ব আইন অনুসারে মূল উৎস ও কপিরাইট তথ্য যুক্ত করা হয়েছে।</span>
            </div>
        </div>
    </div>

    <!-- 0x7FA3 0x9B12 0x3F88 0x0119 0xEE43 0x889A 0x33B1 0x778A 0x22C9 0xFA4B 0x8801 0x5D3E 0x992B 0x71AF 0x44BC 0x110E -->
    <!-- [IDEA-PROKASHON-ENCRYPTED-SECURITY-STREAM-V2.6] 9f8a88c3d10e55b729a4c8e71b2390ff5b0981aa7c3d42e1 -->
    <!-- 01001001 01000100 01000101 01000001 00100000 01010000 01010010 01001111 01001011 01000001 01010011 01001000 01001111 01001110 -->

    {{-- Inspect Element, Page Source Protection & Copy Claim Attribution Engine --}}
    <script>
        (function() {

            // 2. Disable Keyboard Shortcuts (F12, View Source Ctrl+U, Inspect Ctrl+Shift+I/J/C, Save Ctrl+S)
            document.addEventListener('keydown', function(e) {
                // F12
                if (e.key === 'F12' || e.keyCode === 123) {
                    e.preventDefault();
                    e.stopPropagation();
                    return false;
                }

                var isCtrlOrCmd = e.ctrlKey || e.metaKey;

                // View Source (Ctrl + U / Cmd + Option + U)
                if (isCtrlOrCmd && (e.key === 'u' || e.key === 'U' || e.keyCode === 85)) {
                    e.preventDefault();
                    e.stopPropagation();
                    showProtectionToast('পেজ সোর্স দেখা সুরক্ষিত ও নিষিদ্ধ।');
                    return false;
                }

                // Save Page (Ctrl + S)
                if (isCtrlOrCmd && (e.key === 's' || e.key === 'S' || e.keyCode === 83)) {
                    e.preventDefault();
                    e.stopPropagation();
                    return false;
                }

                // Inspect / Developer Tools (Ctrl + Shift + I/J/C or Cmd + Option + I/J/C)
                if (isCtrlOrCmd && e.shiftKey && (
                    e.key === 'I' || e.key === 'i' || e.keyCode === 73 ||
                    e.key === 'J' || e.key === 'j' || e.keyCode === 74 ||
                    e.key === 'C' || e.key === 'c' || e.keyCode === 67
                )) {
                    e.preventDefault();
                    e.stopPropagation();
                    showProtectionToast('ইন্সপেক্ট এলিমেন্ট বন্ধ রাখা হয়েছে।');
                    return false;
                }
            }, true);

            // 3. Smart Copy Attribution & Copyright Claim Appender
            document.addEventListener('copy', function(e) {
                var selObj = window.getSelection();
                var selection = selObj ? selObj.toString() : '';
                
                // Do not intercept if copying from inputs, textareas, comments, or interactive widgets
                if (e.target && (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.isContentEditable || e.target.closest('#blogCommentsListContainer') || e.target.closest('.blog-comments-card') || e.target.closest('.allow-normal-copy'))) {
                    return; // Normal clean copy without attribution
                }

                if (selObj && selObj.anchorNode && selObj.anchorNode.parentElement) {
                    if (selObj.anchorNode.parentElement.closest('#blogCommentsListContainer') || selObj.anchorNode.parentElement.closest('.blog-comments-card')) {
                        return; // Normal clean copy for comments
                    }
                }

                if (selection && selection.length > 5) {
                    var pageTitle = document.title || 'আইডিয়া প্রকাশন';
                    var pageUrl = window.location.href;
                    var claimNotice = '\n\n' +
                        '----------------------------------------------------------------------\n' +
                        '© আইডিয়া প্রকাশন (Idea Prokashon) | সর্বস্বত্ব সংরক্ষিত\n' +
                        'শিরোনাম: ' + pageTitle + '\n' +
                        'মূল উৎস লিংক: ' + pageUrl + '\n' +
                        'আইডিয়া প্রকাশনের অনুমতি ব্যতীত এই লেখার অননুমোদিত বাণিজ্যিক বা অবাণিজ্যিক পুনঃপ্রকাশ কপিরাইট আইনে শাস্তিযোগ্য অপরাধ।\n' +
                        'ওয়েবসাইট: https://www.ideaabd.com\n' +
                        '----------------------------------------------------------------------';

                    var copyWithClaim = selection + claimNotice;

                    if (e.clipboardData) {
                        e.clipboardData.setData('text/plain', copyWithClaim);
                        e.preventDefault();
                    } else if (window.clipboardData) {
                        window.clipboardData.setData('Text', copyWithClaim);
                        e.preventDefault();
                    }

                    showCopyToast();
                }
            });

            // 4. Developer Console Warning & Obfuscated Security Stream
            try {
                console.log(
                    "%c🛑 সাবধান! STOP! | 0x7FA3 0x9B12 0x3F88 0x0119 0xEE43 0x889A",
                    "color: #ef4444; font-size: 26px; font-weight: 900; -webkit-text-stroke: 1px black;"
                );
                console.log(
                    "%c[SECURITY-HASH: 9f8a88c3d10e55b729a4c8e71b2390ff5b0981aa7c3d42e1]\n[DATA-STREAM: 0x22C9 0xFA4B 0x8801 0x5D3E 0x992B 0x71AF 0x44BC 0x110E]\nএটি আইডিয়া প্রকাশনের কপিরাইট ও মেধাস্বত্ব দ্বারা সুরক্ষিত ডিজিটাল প্ল্যাটফর্ম।\nঅনুমতি ব্যতীত সোর্স কোড দেখা, স্ক্র্যাপিং বা অননুমোদিত ব্যবহারের অপচেষ্টা আইনত দণ্ডনীয়।\n© Idea Prokashon | https://www.ideaabd.com",
                    "color: #0284c7; font-size: 13px; font-weight: bold;"
                );
            } catch(err){}

            var toastTimer = null;
            function showCopyToast() {
                var toast = document.getElementById('copyClaimToast');
                if (!toast) return;
                toast.style.display = 'block';
                clearTimeout(toastTimer);
                toastTimer = setTimeout(function() {
                    toast.style.display = 'none';
                }, 4000);
            }

            function showProtectionToast(msg) {
                var toast = document.getElementById('copyClaimToast');
                if (!toast) return;
                var textSpan = toast.querySelector('span');
                if (textSpan && msg) {
                    textSpan.textContent = msg;
                }
                toast.style.display = 'block';
                clearTimeout(toastTimer);
                toastTimer = setTimeout(function() {
                    toast.style.display = 'none';
                    if (textSpan) {
                        textSpan.textContent = 'লেখাটি কপি করা হয়েছে! মেধা-স্বত্ব আইন অনুসারে মূল উৎস ও কপিরাইট তথ্য যুক্ত করা হয়েছে।';
                    }
                }, 3000);
            }
        })();
    </script>

    {{-- Both mechanisms are supported: @section('scripts') and @push('scripts') --}}
    @yield('scripts')
    @stack('scripts')
</body>
</html>
