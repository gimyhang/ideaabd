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
        $defaultBanner = asset('images/og-banner.jpg');
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
    <link rel="canonical" href="{{ $metaPageUrl }}">

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

    {{-- Dynamic Site Favicon --}}
    @php $siteFaviconUrl = \App\Support\SiteSetting::faviconUrl(); @endphp
    @if ($siteFaviconUrl)
        <link rel="icon" href="{{ $siteFaviconUrl }}">
        <link rel="apple-touch-icon" href="{{ $siteFaviconUrl }}">
    @else
        <link rel="icon" href="{{ asset('favicon.ico') }}">
    @endif
    
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
    
    <!-- Social Proof Bubble -->
    <div id="social-proof-bubble" class="social-proof-bubble d-none">
        <div class="d-flex align-items-center">
            <div class="bubble-icon">
                <i class="fas fa-shopping-bag text-primary"></i>
            </div>
            <div class="bubble-text ms-3">
                <p class="mb-0 small"><strong id="sp-name">রহিম</strong> (<span id="sp-district">ঢাকা</span>) এইমাত্র কিনেছেন</p>
                <h6 class="mb-0 text-primary" id="sp-book">শঙ্খনীল কারাগার</h6>
                <small class="text-muted" id="sp-time">5 minutes ago</small>
            </div>
        </div>
    </div>

    <style>
        .social-proof-bubble {
            position: fixed;
            bottom: 20px;
            left: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            padding: 12px 15px;
            z-index: 1050;
            max-width: 300px;
            border-left: 4px solid var(--primary-accent);
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.5s ease, transform 0.5s ease;
        }
        .social-proof-bubble.show {
            opacity: 1;
            transform: translateY(0);
        }
        .bubble-icon {
            width: 40px;
            height: 40px;
            background: var(--primary-light);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Social Proof Logic
            fetch('/api/recent-orders')
                .then(res => res.json())
                .then(data => {
                    if(data.length > 0) {
                        let currentIndex = 0;
                        const bubble = document.getElementById('social-proof-bubble');
                        
                        setInterval(() => {
                            // Hide bubble first if showing
                            bubble.classList.remove('show');
                            
                            setTimeout(() => {
                                const order = data[currentIndex];
                                document.getElementById('sp-name').innerText = order.customer_name;
                                document.getElementById('sp-district').innerText = order.district;
                                document.getElementById('sp-book').innerText = order.book_title;
                                document.getElementById('sp-time').innerText = order.time_ago;
                                
                                bubble.classList.remove('d-none');
                                // Force reflow
                                void bubble.offsetWidth;
                                bubble.classList.add('show');
                                
                                currentIndex = (currentIndex + 1) % data.length;
                                
                                // Hide after 5 seconds
                                setTimeout(() => {
                                    bubble.classList.remove('show');
                                }, 5000);
                                
                            }, 500); // wait for fade out
                            
                        }, 25000); // Show every 25 seconds
                    }
                })
                .catch(err => console.log('Social proof error:', err));
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

    {{-- Both mechanisms are supported: @section('scripts') and @push('scripts') --}}
    @yield('scripts')
    @stack('scripts')
</body>
</html>
