<!DOCTYPE html>
<html lang="bn" class="notranslate" translate="no">
<head>
    <meta charset="UTF-8">
    <meta name="google" content="notranslate">
    <meta name="googlebot" content="notranslate">
    <meta http-equiv="Content-Language" content="bn">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="theme-color" content="#0066cc">
    <meta name="color-scheme" content="light dark">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Phone browsers that block site data throw on any localStorage access; fall back to an in-memory store so the scripts below keep running. --}}
    <script>
        (function () {
            try {
                window.localStorage.setItem('__probe', '1');
                window.localStorage.removeItem('__probe');
            } catch (e) {
                var mem = {};
                var fake = {
                    getItem: function (key) { return Object.prototype.hasOwnProperty.call(mem, key) ? mem[key] : null; },
                    setItem: function (key, v) { mem[key] = String(v); },
                    removeItem: function (key) { delete mem[key]; },
                    clear: function () { mem = {}; },
                    key: function (i) { return Object.keys(mem)[i] || null; }
                };
                try { Object.defineProperty(window, 'localStorage', { value: fake, configurable: true }); } catch (e2) {}
            }
        })();
    </script>

    {{-- Anti-FOUC Instant Theme Initialization --}}
    <script>
        (function() {
            try {
                var savedTheme = localStorage.getItem('idea_theme_preference');
                if (!savedTheme) {
                    var match = document.cookie.match(new RegExp('(^| )idea_theme=([^;]+)'));
                    if (match) savedTheme = match[2];
                }
                if (!savedTheme && window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                    savedTheme = 'dark';
                }
                var activeTheme = (savedTheme === 'dark') ? 'dark' : 'light';
                document.documentElement.setAttribute('data-bs-theme', activeTheme);
                document.documentElement.setAttribute('data-theme', activeTheme);
                if (activeTheme === 'dark') document.documentElement.classList.add('dark-mode');
            } catch(e) {}
        })();
    </script>

    {{-- Universal Social Media Open Graph (Facebook, WhatsApp, LinkedIn) & Twitter / X Cards --}}
    @php
        $defaultSiteName = \App\Support\SiteSetting::name() ?: 'আইডিয়া প্রকাশন';
        $defaultSiteTagline = \App\Support\SiteSetting::tagline() ?: 'অনলাইন বই ও প্রকাশনা প্ল্যাটফর্ম';
        
        // Canonical Domain resolution
        $canonicalDomain = rtrim(config('app.url', 'https://www.ideaabd.com'), '/');
        if (str_contains($canonicalDomain, 'localhost') || str_contains($canonicalDomain, '127.0.0.1')) {
            $canonicalDomain = 'https://www.ideaabd.com';
        }
        $canonicalDomain = preg_replace('/^http:\/\//i', 'https://', $canonicalDomain);
        // The live site is served on www (public/.htaccess redirects ideaabd.com → www). Canonical / og:url / og:image
        // must name that same host, or Facebook sees a redirect loop and shows a shared link as plain text.
        $wwwHost = fn ($u) => preg_replace('#^(https?:)?//ideaabd\.com(?=[/:?\#]|$)#i', 'https://www.ideaabd.com', (string) $u);
        $canonicalDomain = $wwwHost($canonicalDomain);

        $defaultBanner = \App\Support\SiteSetting::blogOgBannerUrl() ?: asset('images/og-banner.jpg');
        if (!str_starts_with($defaultBanner, 'http://') && !str_starts_with($defaultBanner, 'https://')) {
            $defaultBanner = $canonicalDomain . '/' . ltrim($defaultBanner, '/');
        } else {
            $parsedBanner = parse_url($defaultBanner);
            if (($parsedBanner['host'] ?? '') === '127.0.0.1' || ($parsedBanner['host'] ?? '') === 'localhost') {
                $defaultBanner = $canonicalDomain . ($parsedBanner['path'] ?? '/');
            }
        }
        $defaultBanner = preg_replace('/^http:\/\//i', 'https://', $defaultBanner);

        $currentReqPath = '/' . ltrim(request()->path(), '/');
        $dbSeo = null;
        if (class_exists(\Modules\SEO\Models\SeoMeta::class) && \App\Support\SchemaCache::has('seo_metas')) {
            try {
                $dbSeo = \Illuminate\Support\Facades\Cache::remember('seo_meta_path_' . md5($currentReqPath), 600, function () use ($currentReqPath) {
                    return \Modules\SEO\Models\SeoMeta::whereIn('url_path', array_unique([$currentReqPath, rawurldecode($currentReqPath)]))->first();
                });
            } catch (\Throwable) {}
        }

        // The page's SEO record wins: it is the text scored and edited in /admin/seo.
        $metaPageTitle = $dbSeo?->meta_title ?: (View::hasSection('og_title') ? View::getSection('og_title') : (View::hasSection('title') ? View::getSection('title') : $defaultSiteName));
        $metaPageDescription = $dbSeo?->meta_description ?: (View::hasSection('og_description') ? View::getSection('og_description') : (View::hasSection('meta_description') ? View::getSection('meta_description') : $defaultSiteTagline));
        $metaPageKeywords = $dbSeo?->meta_keywords ?: (View::hasSection('meta_keywords') ? View::getSection('meta_keywords') : 'আইডিয়া প্রকাশন, বাংলা বই, ইবুক, সাহিত্য, ব্লগ, কবিতা, প্রবন্ধ, প্রকাশনা, বই অর্ডার, অনলাইন বই মেলা, Idea Publication, Bangla Books, Ebooks, Publishers, Research, Webzine');
        
        $candidateImage = View::hasSection('og_image') ? View::getSection('og_image') : ($dbSeo?->og_image ?: null);
        if (empty($candidateImage) || str_ends_with(strtolower($candidateImage), '.svg') || str_starts_with($candidateImage, 'data:')) {
            $metaPageImage = $defaultBanner;
        } else {
            $metaPageImage = $candidateImage;
        }

        // Standardize absolute HTTPS URL for Open Graph image
        if (!str_starts_with($metaPageImage, 'http://') && !str_starts_with($metaPageImage, 'https://')) {
            $metaPageImage = $canonicalDomain . '/' . ltrim($metaPageImage, '/');
        } else {
            $parsedImg = parse_url($metaPageImage);
            $imgHost = $parsedImg['host'] ?? '';
            if ($imgHost === '127.0.0.1' || $imgHost === 'localhost' || str_contains($imgHost, '127.0.0.1')) {
                $imgPath = $parsedImg['path'] ?? '/';
                $imgQuery = isset($parsedImg['query']) ? '?' . $parsedImg['query'] : '';
                $metaPageImage = $canonicalDomain . $imgPath . $imgQuery;
            }
        }
        $metaPageImage = preg_replace('/^http:\/\//i', 'https://', $metaPageImage);

        // Canonical URL resolution (Google Search Console 'Duplicate without user-selected canonical' fix)
        if (View::hasSection('canonical')) {
            $rawCanonical = trim(View::getSection('canonical'));
        } elseif (View::hasSection('og_url')) {
            $rawCanonical = trim(View::getSection('og_url'));
        } elseif ($dbSeo?->canonical_url) {
            $rawCanonical = trim($dbSeo->canonical_url);
        } else {
            $reqPath = request()->path();
            $rawCanonical = ($reqPath === '/' || $reqPath === '') ? $canonicalDomain . '/' : $canonicalDomain . '/' . ltrim($reqPath, '/');
            if (request()->filled('page') && (int)request('page') > 1) {
                $rawCanonical .= '?page=' . (int)request('page');
            }
        }

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

        $metaPageUrl = $wwwHost(preg_replace('/^http:\/\//i', 'https://', $rawCanonical));
        $metaPageImage = $wwwHost($metaPageImage);
        $metaPageType = View::hasSection('og_type') ? View::getSection('og_type') : ($dbSeo?->og_type ?: 'website');

        $imageExt = strtolower(pathinfo(parse_url($metaPageImage, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION));
        $imageMime = ($imageExt === 'png') ? 'image/png' : (($imageExt === 'webp') ? 'image/webp' : 'image/jpeg');

        // Safe clean image URL without double-escaped ampersands (&amp;amp;)
        $cleanImageMetaUrl = htmlspecialchars(htmlspecialchars_decode($metaPageImage, ENT_QUOTES), ENT_QUOTES, 'UTF-8');
        $cleanPageMetaUrl = htmlspecialchars(htmlspecialchars_decode($metaPageUrl, ENT_QUOTES), ENT_QUOTES, 'UTF-8');
    @endphp
    <title>{{ $dbSeo?->meta_title ?: trim($__env->yieldContent('title', config('app.name', 'Idea Publication'))) . ' — ideaabd' }}</title>

    <!-- Open Graph / Facebook / WhatsApp / LinkedIn (High Priority at Top of Head) -->
    <meta property="og:locale" content="bn_BD">
    <meta property="og:type" content="{{ $metaPageType }}">
    <meta property="og:url" content="{!! $cleanPageMetaUrl !!}">
    <meta property="og:title" content="{{ $metaPageTitle }}">
    <meta property="og:description" content="{{ Str::limit(strip_tags($metaPageDescription), 220) }}">
    <meta property="og:image" content="{!! $cleanImageMetaUrl !!}">
    <meta property="og:image:secure_url" content="{!! $cleanImageMetaUrl !!}">
    <meta property="og:image:type" content="{{ $imageMime }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="{{ $metaPageTitle }}">
    <meta property="og:site_name" content="{{ $defaultSiteName }}">

    <!-- Twitter / X Cards with Large Image Focus -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="{!! $cleanPageMetaUrl !!}">
    <meta name="twitter:title" content="{{ $metaPageTitle }}">
    <meta name="twitter:description" content="{{ Str::limit(strip_tags($metaPageDescription), 220) }}">
    <meta name="twitter:image" content="{!! $cleanImageMetaUrl !!}">
    <meta name="twitter:image:alt" content="{{ $metaPageTitle }}">

    <meta name="description" content="{{ Str::limit(strip_tags($metaPageDescription), 220) }}">
    <meta name="keywords" content="{{ $metaPageKeywords }}">
    <meta name="author" content="@yield('meta_author', $defaultSiteName)">
    <meta name="publisher" content="{{ $defaultSiteName }}">
    <meta name="copyright" content="© {{ date('Y') }} {{ $defaultSiteName }}. All Rights Reserved.">
    <meta name="robots" content="{{ $dbSeo?->robots && str_contains(strtolower($dbSeo->robots), 'noindex') ? 'noindex, follow' : 'index, follow' }}, max-snippet:-1, max-image-preview:large, max-video-preview:-1">
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

    {{-- Universal Google JSON-LD Schema.org Structured Data --}}
    @php
        $universalGraph = [
            '@context' => 'https://schema.org',
            '@graph' => [
                [
                    '@type' => 'Organization',
                    '@id' => $canonicalDomain . '/#organization',
                    'name' => $defaultSiteName,
                    'url' => $canonicalDomain,
                    'logo' => [
                        '@type' => 'ImageObject',
                        'url' => asset('images/logo.png'),
                        'caption' => $defaultSiteName,
                    ],
                    'sameAs' => [
                        'https://www.facebook.com/ideaprokashon',
                    ],
                    'contactPoint' => [
                        '@type' => 'ContactPoint',
                        'telephone' => '+8801726976982',
                        'contactType' => 'customer service',
                        'areaServed' => 'BD',
                        'availableLanguage' => ['bn', 'en'],
                    ],
                ],
                [
                    '@type' => 'WebSite',
                    '@id' => $canonicalDomain . '/#website',
                    'url' => $canonicalDomain,
                    'name' => $defaultSiteName,
                    'description' => $defaultSiteTagline,
                    'publisher' => [
                        '@id' => $canonicalDomain . '/#organization',
                    ],
                    'potentialAction' => [
                        '@type' => 'SearchAction',
                        'target' => [
                            '@type' => 'EntryPoint',
                            'urlTemplate' => $canonicalDomain . '/search?q={search_term_string}',
                        ],
                        'query-input' => 'required name=search_term_string',
                    ],
                ],
            ],
        ];
    @endphp
    <script type="application/ld+json">
    {!! json_encode($universalGraph, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_TAG | JSON_HEX_AMP) !!}
    </script>
    @if(!View::hasSection('schema_json') && !empty($dbSeo?->schema_json))
    <script type="application/ld+json">
    {!! json_encode(is_array($dbSeo->schema_json) ? $dbSeo->schema_json : (json_decode((string) $dbSeo->schema_json, true) ?? []), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_TAG | JSON_HEX_AMP) !!}
    </script>
    @endif
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

    <!-- Google AdSense Official Script (Auto-Ads / Offerwalls disabled by default to prevent screen blocking) -->
    @if(config('services.google_adsense.enabled', true))
        @php
            $adsenseClientId = config('services.google_adsense.client_id', 'ca-pub-4534355865737776');
            $enableAutoAds = config('services.google_adsense.auto_ads', false);
            $adsenseSrc = $enableAutoAds 
                ? 'https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=' . e($adsenseClientId)
                : 'https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js';
        @endphp
        <meta name="google-adsense-account" content="{{ $adsenseClientId }}">
        {{-- The ad library is large and busy; the page is drawn and usable first. It loads on the visitor's first
             scroll / tap / key, or 3.5 s after the page has loaded. Ad slots queue their push() until then. --}}
        <script>
            (function () {
                var done = false, later = [], evs = ['scroll', 'pointerdown', 'keydown', 'touchstart', 'mousemove'];
                function go() {
                    if (done) return; done = true;
                    evs.forEach(function (t) { removeEventListener(t, go, true); });
                    if (!document.querySelector('script[src*="adsbygoogle.js"]')) {
                        var s = document.createElement('script');
                        s.async = true; s.crossOrigin = 'anonymous'; s.src = @json($adsenseSrc);
                        document.head.appendChild(s);
                    }
                    later.forEach(function (fn) { try { fn(); } catch (e) {} });
                }
                // ad partials wait for the same moment (partials/google-ad.blade.php)
                window.ideaAdsLater = function (fn) { if (done) fn(); else later.push(fn); };
                evs.forEach(function (t) { addEventListener(t, go, { capture: true, passive: true }); });
                addEventListener('load', function () { setTimeout(go, 3500); });
            })();
        </script>
    @endif
    
    <!-- Fonts: Kalpurush, Nikosh, Hind Siliguri, Noto Sans Bengali & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    {{-- the site's own Bengali font: every page needs it, start it before the stylesheet asks --}}
    <link rel="preload" href="{{ asset('fonts/kalpurush/kalpurush.woff2') }}" as="font" type="font/woff2" crossorigin>
    {{-- Non-blocking font CSS: text renders immediately with fallback font, swaps when loaded --}}
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700&family=Noto+Sans+Bengali:wght@400;500;600;700;800&family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet" media="print" onload="this.media='all'">
    <noscript><link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;600;700&family=Inter:wght@400;600;700&display=swap" rel="stylesheet"></noscript>
    
    {{-- Bootstrap 5.3.0 + site chrome (header/nav/footer) + product cards + theme, in this order, as ONE file
         from our own server (no extra connection to a CDN; cached for a year, renamed whenever a part changes). --}}
    {!! \App\Support\AssetBundle::css('site', [
        'vendor/bootstrap-5.3.0/bootstrap.min.css',
        'css/site.css',
        'css/storefront-products.css',
        'css/theme.css',
    ]) !!}

    <!-- Font Awesome 6.6.0 Solid & Brands -->
    {{-- Icons are not needed to paint the page, so this stylesheet no longer blocks the first render --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" media="print" onload="this.media='all'">
    <noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"></noscript>

    <!-- Custom Styles -->
    <style>
        @font-face {
            font-family: 'Kalpurush';
            src: url('{{ asset("fonts/kalpurush/kalpurush.woff2") }}') format('woff2'),
                 url('{{ asset("fonts/kalpurush/kalpurush.ttf") }}') format('truetype');
            font-weight: 100 900;
            font-style: normal;
            font-display: swap;
        }
        
        html, body, input, select, textarea, .form-control, .form-select, p, h1, h2, h3, h4, h5, h6, label {
            font-family: 'Kalpurush', 'Nikosh', 'SolaimanLipi', 'Hind Siliguri', 'Noto Sans Bengali', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
        /* Kalpurush everywhere: links, menus, spans, lists, tables, dropdowns (icons and code keep their own faces) */
        html { font-size: 19px; } /* +3px over the 16px default: every rem-based size grows with it */
        :root { --bs-body-font-family: 'Kalpurush', 'Nikosh', 'SolaimanLipi', 'Hind Siliguri', 'Noto Sans Bengali', sans-serif; }
        body *:not(i):not([class*="fa-"]):not(.fa):not(code):not(pre):not(kbd):not(samp):not(svg):not(svg *) {
            font-family: 'Kalpurush', 'Nikosh', 'SolaimanLipi', 'Hind Siliguri', 'Noto Sans Bengali', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
        button, .btn, .badge, .alert {
            font-family: 'Kalpurush', 'Nikosh', 'SolaimanLipi', 'Hind Siliguri', 'Noto Sans Bengali', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }

        /* ══════════════════════════════════════════════════════════════════
           FONT AWESOME ICON ENGINE & SOLID SHAPE RENDERING PROTECTION
           Guarantees crisp solid shapes everywhere across all pages
           ══════════════════════════════════════════════════════════════════ */
        .fa, .fas, .far, .fal, .fab, .fad, .fa-solid, .fa-regular, .fa-brands, .fa-light, .fa-thin, .fa-duotone,
        [class*=" fa-"], [class^="fa-"], i[class*="fa-"], span[class*="fa-"],
        .btn i, .badge i, button i, a i {
            font-family: "Font Awesome 6 Free", "Font Awesome 6 Brands", "FontAwesome" !important;
            font-style: normal !important;
            font-variant: normal !important;
            text-rendering: auto !important;
            -webkit-font-smoothing: antialiased !important;
            -moz-osx-font-smoothing: grayscale !important;
            display: inline-block;
            line-height: 1;
        }
        .fa-solid, .fas, i.fas, i.fa-solid {
            font-family: "Font Awesome 6 Free" !important;
            font-weight: 900 !important;
        }
        .fa-regular, .far, i.far, i.fa-regular {
            font-family: "Font Awesome 6 Free" !important;
            font-weight: 400 !important;
        }
        .fa-brands, .fab, i.fab, i.fa-brands {
            font-family: "Font Awesome 6 Brands" !important;
            font-weight: 400 !important;
        }
        
        body {
            font-size: 16px;
            line-height: 1.6;
            background-color: #f8fafb;
            color: #1e293b;
            text-rendering: optimizeLegibility;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
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

        /* ══════════════════════════════════════════════════════════════════
           PREVENT GOOGLE TRANSLATE BANNER FROM BREAKING TOPBAR / BODY
           ══════════════════════════════════════════════════════════════════ */
        .goog-te-banner-frame.skiptranslate,
        .goog-te-banner-frame,
        iframe.goog-te-banner-frame,
        .goog-te-gadget-simple,
        #goog-gt-tt,
        .goog-te-balloon-frame {
            display: none !important;
            visibility: hidden !important;
            height: 0 !important;
            width: 0 !important;
            opacity: 0 !important;
        }
        body {
            top: 0px !important;
            position: static !important;
        }
        .goog-te-gadget {
            display: none !important;
        }
        body > .skiptranslate {
            display: none !important;
        }
        .goog-tooltip {
            display: none !important;
        }
        .goog-text-highlight {
            background-color: transparent !important;
            border: none !important;
            box-shadow: none !important;
        }
    </style>
    
    @if (file_exists(public_path('hot')) || file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    {{-- Dynamic View Stylesheets & Head Stacks --}}
    @yield('styles')
    @yield('css')
    {{-- shared device rules (phones/tablets) --}}
    <link rel="stylesheet" href="{{ asset('css/responsive.css') }}?v={{ @filemtime(public_path('css/responsive.css')) }}">
    @stack('styles')
    @stack('head')
</head>
<body style="display: flex; flex-direction: column; min-height: 100vh;">
    @include('partials.impersonation-bar')
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

    {{-- Google Translate Element (Hidden from view) --}}
    <div id="google_translate_element" style="display:none; position:absolute; left:-9999px;"></div>
    
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

    <style>
        /* phones: the chat bubble must never sit on top of an order button or the cart drawer's form */
        body:has(.offcanvas.show) .floating-whatsapp-btn, body:has(dialog[open]) .floating-whatsapp-btn { display: none !important; }
        @media (max-width: 575.98px) {
            .floating-whatsapp-btn { width: 46px !important; height: 46px !important; bottom: 84px !important; right: 14px !important; opacity: .92; }
        }
    </style>

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


    {{-- Bootstrap 5.3.0 JS, modal guard, SweetAlert2, site core (flash banners, language switcher, copy notice,
         confirm dialogs: public/js/site-core.js), theme controller and site enhancements: one cached file from our
         own server, run in the same order as before. Page scripts below can still use bootstrap / Swal at once. --}}
    {!! \App\Support\AssetBundle::js('core', [
        'vendor/bootstrap-5.3.0/bootstrap.bundle.min.js',
        'js/modal-guard.js',
        'vendor/sweetalert2/sweetalert2.all.min.js',
        'js/site-core.js',
        'js/theme.js',
        'js/site-enhancements.js',
    ]) !!}
    {{-- Universal World-Class Bengali Audiobook Dock & Selection Tooltip --}}
    {{-- Audio reading is opt-in: only pages that define @section('audio_enabled') (article pages) get the player --}}
    @hasSection('audio_enabled')
        @include('partials.audiobook-dock')
        <script src="{{ asset('js/idea-audiobook-engine.js') }}" defer></script>
    @endif

    {{-- Shared quick view for product cards --}}
    <div class="modal fade" id="productQuickViewModal" tabindex="-1" aria-hidden="true" data-url="{{ route('products.quick-view', ':id') }}">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content rounded-4 border-0 overflow-hidden">
                <div class="modal-header border-0 pb-0"><button type="button" class="btn-close ms-auto" data-bs-dismiss="modal" aria-label="Close"></button></div>
                <div class="modal-body pt-0" id="qvContentBody"></div>
            </div>
        </div>
    </div>
    <script src="{{ asset('js/storefront-products.js') }}?v={{ @filemtime(public_path('js/storefront-products.js')) ?: 1 }}" defer></script>

    {{-- Both mechanisms are supported: @section('scripts') and @push('scripts') --}}
    @yield('scripts')
    <script src="{{ asset('js/responsive.js') }}?v={{ @filemtime(public_path('js/responsive.js')) }}" defer></script>
    @stack('scripts')
</body>
</html>
