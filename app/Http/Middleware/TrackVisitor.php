<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\VisitorLog;
use Illuminate\Support\Facades\Schema;

class TrackVisitor
{
    /**
     * Handle an incoming request and record visitor metrics.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only track GET web requests, ignore static assets, API calls, and Admin routes to keep stats accurate
        if ($request->isMethod('GET') && !$request->ajax() && !$request->is('admin*', 'storage*', 'css*', 'js*', 'images*', 'vendor*', 'favicon*', 'api*')) {
            try {
                if (Schema::hasTable('visitor_logs')) {
                    $userAgent = (string) ($request->userAgent() ?? '');
                    
                    // 1. Device Type & Exact Hardware/Brand Model parsing
                    $device = 'desktop';
                    $deviceName = 'Desktop PC';

                    if (preg_match('/(ipad|playbook|tablet)/i', $userAgent)) {
                        $device = 'tablet';
                        $deviceName = 'Tablet';
                        if (str_contains($userAgent, 'iPad')) {
                            $deviceName = 'Apple iPad';
                        }
                    } elseif (preg_match('/(iphone|ipod|mobile|android|phone|smartphone|iemobile|opera mini|blackberry|up.browser)/i', $userAgent)) {
                        $device = 'mobile';
                        $deviceName = 'Mobile Device';
                        
                        if (str_contains($userAgent, 'iPhone')) {
                            $deviceName = 'Apple iPhone';
                        } elseif (preg_match('/SM-[A-Z0-9]+/i', $userAgent) || str_contains($userAgent, 'Samsung') || str_contains($userAgent, 'Galaxy')) {
                            $deviceName = 'Samsung Galaxy';
                        } elseif (preg_match('/(Redmi|Mi [A-Z0-9]+|POCO|Xiaomi)/i', $userAgent, $m)) {
                            $deviceName = 'Xiaomi ' . ($m[1] ?? 'Redmi');
                        } elseif (preg_match('/(CPH\d+|OPPO)/i', $userAgent)) {
                            $deviceName = 'Oppo Device';
                        } elseif (preg_match('/(V\d+|vivo)/i', $userAgent)) {
                            $deviceName = 'Vivo Smartphone';
                        } elseif (str_contains($userAgent, 'Realme') || preg_match('/RMX\d+/i', $userAgent)) {
                            $deviceName = 'Realme Smartphone';
                        } elseif (str_contains($userAgent, 'OnePlus') || preg_match('/ONEPLUS/i', $userAgent)) {
                            $deviceName = 'OnePlus';
                        } elseif (str_contains($userAgent, 'Pixel')) {
                            $deviceName = 'Google Pixel';
                        } elseif (str_contains($userAgent, 'Huawei') || str_contains($userAgent, 'HONOR')) {
                            $deviceName = 'Huawei / Honor';
                        } elseif (str_contains($userAgent, 'Android')) {
                            $deviceName = 'Android Device';
                        }
                    } else {
                        if (str_contains($userAgent, 'Macintosh') || str_contains($userAgent, 'Mac OS')) {
                            $deviceName = 'Apple Mac';
                        } elseif (str_contains($userAgent, 'Windows')) {
                            $deviceName = 'Windows PC';
                        } elseif (str_contains($userAgent, 'Linux')) {
                            $deviceName = 'Linux PC';
                        } elseif (str_contains($userAgent, 'CrOS')) {
                            $deviceName = 'Chromebook';
                        }
                    }

                    // 2. Browser parsing
                    $browser = 'Chrome';
                    if (str_contains($userAgent, 'Edg/') || str_contains($userAgent, 'Edge/')) $browser = 'Microsoft Edge';
                    elseif (str_contains($userAgent, 'OPR/') || str_contains($userAgent, 'Opera')) $browser = 'Opera';
                    elseif (str_contains($userAgent, 'Firefox/')) $browser = 'Mozilla Firefox';
                    elseif (str_contains($userAgent, 'SamsungBrowser/')) $browser = 'Samsung Internet';
                    elseif (str_contains($userAgent, 'UCBrowser/')) $browser = 'UC Browser';
                    elseif (str_contains($userAgent, 'Safari/') && !str_contains($userAgent, 'Chrome/')) $browser = 'Apple Safari';
                    elseif (str_contains($userAgent, 'Chrome/')) $browser = 'Google Chrome';
                    elseif (str_contains($userAgent, 'MSIE') || str_contains($userAgent, 'Trident/')) $browser = 'Internet Explorer';
                    else $browser = 'Web Browser';

                    // 3. Operating System parsing
                    $os = 'Windows';
                    if (str_contains($userAgent, 'Windows NT 10.0')) $os = 'Windows 10/11';
                    elseif (str_contains($userAgent, 'Windows NT 6.3')) $os = 'Windows 8.1';
                    elseif (str_contains($userAgent, 'Windows NT 6.1')) $os = 'Windows 7';
                    elseif (str_contains($userAgent, 'Windows')) $os = 'Windows';
                    elseif (str_contains($userAgent, 'Android')) {
                        if (preg_match('/Android (\d+(\.\d+)?)/i', $userAgent, $matches)) {
                            $os = 'Android ' . $matches[1];
                        } else {
                            $os = 'Android';
                        }
                    }
                    elseif (str_contains($userAgent, 'iPhone') || str_contains($userAgent, 'iPad') || str_contains($userAgent, 'iPod')) {
                        if (preg_match('/OS (\d+[_.]\d+)/i', $userAgent, $matches)) {
                            $os = 'iOS ' . str_replace('_', '.', $matches[1]);
                        } else {
                            $os = 'iOS';
                        }
                    }
                    elseif (str_contains($userAgent, 'Macintosh') || str_contains($userAgent, 'Mac OS')) $os = 'macOS';
                    elseif (str_contains($userAgent, 'Linux')) $os = 'Linux';
                    elseif (str_contains($userAgent, 'CrOS')) $os = 'ChromeOS';
                    else $os = 'Other OS';

                    // 4. Country & Geo Location parsing
                    $countryCode = strtoupper((string) ($request->header('cf-ipcountry') 
                        ?: ($request->header('x-country-code') 
                        ?: ($request->header('cloudfront-viewer-country') ?: ''))));

                    $city = $request->header('cf-ipcity') 
                        ?: ($request->header('x-city') 
                        ?: ($request->header('x-real-city') ?: null));

                    $ip = (string) $request->ip();

                    if (empty($countryCode) || $countryCode === 'XX' || $countryCode === 'T1') {
                        // Fallback default for Bangladesh-focused platform / local dev
                        $countryCode = 'BD';
                    }

                    $countryNames = [
                        'BD' => 'Bangladesh',
                        'US' => 'United States',
                        'GB' => 'United Kingdom',
                        'CA' => 'Canada',
                        'IN' => 'India',
                        'SA' => 'Saudi Arabia',
                        'AE' => 'United Arab Emirates',
                        'MY' => 'Malaysia',
                        'AU' => 'Australia',
                        'DE' => 'Germany',
                        'SG' => 'Singapore',
                        'IT' => 'Italy',
                        'FR' => 'France',
                        'JP' => 'Japan',
                        'QA' => 'Qatar',
                        'KW' => 'Kuwait',
                        'OM' => 'Oman',
                        'SE' => 'Sweden',
                        'NL' => 'Netherlands',
                        'PK' => 'Pakistan',
                        'TR' => 'Turkey',
                        'TH' => 'Thailand',
                    ];
                    $countryName = $countryNames[$countryCode] ?? ($countryCode ?: 'Bangladesh');

                    // 5. Traffic Acquisition Channel & Referer parsing
                    $rawReferer = (string) $request->header('referer');
                    $trafficSource = 'Direct / Organic';

                    if (!empty($rawReferer)) {
                        $refHost = strtolower((string) parse_url($rawReferer, PHP_URL_HOST));
                        if (str_contains($refHost, 'google.')) $trafficSource = 'Google Search';
                        elseif (str_contains($refHost, 'bing.')) $trafficSource = 'Bing Search';
                        elseif (str_contains($refHost, 'yahoo.')) $trafficSource = 'Yahoo Search';
                        elseif (str_contains($refHost, 'duckduckgo.')) $trafficSource = 'DuckDuckGo';
                        elseif (str_contains($refHost, 'yandex.')) $trafficSource = 'Yandex Search';
                        elseif (str_contains($refHost, 'facebook.') || str_contains($refHost, 'fb.') || str_contains($refHost, 'fb.me') || str_contains($refHost, 'm.me')) $trafficSource = 'Facebook';
                        elseif (str_contains($refHost, 'instagram.')) $trafficSource = 'Instagram';
                        elseif (str_contains($refHost, 'whatsapp.') || str_contains($refHost, 'wa.me')) $trafficSource = 'WhatsApp';
                        elseif (str_contains($refHost, 'youtube.') || str_contains($refHost, 'youtu.be')) $trafficSource = 'YouTube';
                        elseif (str_contains($refHost, 'twitter.') || str_contains($refHost, 't.co') || str_contains($refHost, 'x.com')) $trafficSource = 'Twitter / X';
                        elseif (str_contains($refHost, 'linkedin.')) $trafficSource = 'LinkedIn';
                        elseif (str_contains($refHost, 'pinterest.')) $trafficSource = 'Pinterest';
                        elseif (str_contains($refHost, 'tiktok.')) $trafficSource = 'TikTok';
                        elseif (str_contains($refHost, 'telegram.') || str_contains($refHost, 't.me')) $trafficSource = 'Telegram';
                        elseif (!empty($refHost) && !str_contains($refHost, 'ideaabd.com') && !str_contains($refHost, '127.0.0.1') && !str_contains($refHost, 'localhost')) {
                            $trafficSource = 'Referral (' . $refHost . ')';
                        }
                    }

                    $utmSource = $request->string('utm_source')->trim()->value() 
                        ?: ($request->string('ref')->trim()->value() ?: null);

                    if ($utmSource) {
                        $trafficSource = 'Campaign (' . $utmSource . ')';
                    }

                    // 6. Descriptive Human Page Title
                    $path = trim($request->path(), '/');
                    $pageTitle = 'Homepage';
                    if ($path === '' || $path === '/') {
                        $pageTitle = 'হোমপেজ — আইডিয়া প্রকাশন';
                    } elseif (str_starts_with($path, 'books/')) {
                        $pageTitle = 'Book: ' . urldecode(substr($path, 6));
                    } elseif ($path === 'books' || $path === 'shop') {
                        $pageTitle = 'বইয়ের ক্যাটালগ ও শপ';
                    } elseif (str_starts_with($path, 'blog/category/')) {
                        $pageTitle = 'Blog Category: ' . urldecode(substr($path, 14));
                    } elseif (str_starts_with($path, 'blog/')) {
                        $pageTitle = 'Article: ' . urldecode(substr($path, 5));
                    } elseif ($path === 'blog') {
                        $pageTitle = 'ব্লগ ও সাহিত্য পাতা';
                    } elseif (str_starts_with($path, 'authors/')) {
                        $pageTitle = 'Author Profile: ' . urldecode(substr($path, 8));
                    } elseif ($path === 'authors') {
                        $pageTitle = 'লেখক তালিকা';
                    } elseif (str_starts_with($path, 'publishers/')) {
                        $pageTitle = 'Publisher: ' . urldecode(substr($path, 11));
                    } elseif ($path === 'publishers') {
                        $pageTitle = 'প্রকাশক তালিকা';
                    } elseif (str_starts_with($path, 'webzines/') || str_starts_with($path, 'webzine/')) {
                        $pageTitle = 'Webzine: ' . urldecode(substr($path, 8));
                    } elseif ($path === 'webzine') {
                        $pageTitle = 'ওয়েবজিন সাময়িকী';
                    } elseif ($path === 'cart' || $path === 'checkout') {
                        $pageTitle = 'Shopping Cart & Checkout';
                    } elseif ($path === 'my-account') {
                        $pageTitle = 'গ্রাহক ড্যাশবোর্ড';
                    } else {
                        $pageTitle = $request->route()?->getName() ?: '/' . $path;
                    }

                    VisitorLog::create([
                        'ip_address'     => $ip,
                        'url'            => substr($request->fullUrl(), 0, 1000),
                        'page_title'     => substr($pageTitle, 0, 255),
                        'route_name'     => $request->route()?->getName(),
                        'user_agent'     => substr($userAgent, 0, 1000) ?: null,
                        'device'         => $device,
                        'device_name'    => $deviceName,
                        'browser'        => $browser,
                        'os'             => $os,
                        'country'        => $countryName,
                        'country_code'   => $countryCode,
                        'city'           => $city ? substr($city, 0, 100) : null,
                        'referer'        => substr($rawReferer, 0, 1000) ?: null,
                        'traffic_source' => $trafficSource,
                        'utm_source'     => $utmSource ? substr($utmSource, 0, 100) : null,
                        'user_id'        => auth()->id(),
                        'visited_at'     => now(),
                    ]);
                }
            } catch (\Throwable $e) {
                // Fail silently so visitor experience is never affected
            }
        }

        return $response;
    }
}
