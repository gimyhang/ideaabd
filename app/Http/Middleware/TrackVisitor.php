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
                    
                    // Device parsing
                    $device = 'desktop';
                    if (preg_match('/(tablet|ipad|playbook)|(android(?!.*(mobi|opera mini)))/i', $userAgent)) {
                        $device = 'tablet';
                    } elseif (preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|android|iemobile|iphone|ipod)/i', $userAgent)) {
                        $device = 'mobile';
                    }

                    // Browser parsing
                    $browser = 'Chrome';
                    if (str_contains($userAgent, 'Edg/') || str_contains($userAgent, 'Edge/')) $browser = 'Edge';
                    elseif (str_contains($userAgent, 'OPR/') || str_contains($userAgent, 'Opera')) $browser = 'Opera';
                    elseif (str_contains($userAgent, 'Firefox/')) $browser = 'Firefox';
                    elseif (str_contains($userAgent, 'Safari/') && !str_contains($userAgent, 'Chrome/')) $browser = 'Safari';
                    elseif (str_contains($userAgent, 'Chrome/')) $browser = 'Chrome';
                    elseif (str_contains($userAgent, 'MSIE') || str_contains($userAgent, 'Trident/')) $browser = 'Internet Explorer';
                    else $browser = 'Browser';

                    // OS parsing
                    $os = 'Windows';
                    if (str_contains($userAgent, 'Windows')) $os = 'Windows';
                    elseif (str_contains($userAgent, 'Android')) $os = 'Android';
                    elseif (str_contains($userAgent, 'iPhone') || str_contains($userAgent, 'iPad') || str_contains($userAgent, 'iPod')) $os = 'iOS';
                    elseif (str_contains($userAgent, 'Macintosh') || str_contains($userAgent, 'Mac OS')) $os = 'macOS';
                    elseif (str_contains($userAgent, 'Linux')) $os = 'Linux';
                    else $os = 'Other';

                    // Country & Geo Location parsing
                    $countryCode = strtoupper((string) ($request->header('cf-ipcountry') 
                        ?: ($request->header('x-country-code') 
                        ?: ($request->header('cloudfront-viewer-country') ?: ''))));

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
                    ];
                    $countryName = $countryNames[$countryCode] ?? ($countryCode ?: 'Bangladesh');

                    // Traffic Acquisition Channel & Referer parsing
                    $rawReferer = (string) $request->header('referer');
                    $trafficSource = 'Direct / Organic';

                    if (!empty($rawReferer)) {
                        $refHost = strtolower((string) parse_url($rawReferer, PHP_URL_HOST));
                        if (str_contains($refHost, 'google.')) $trafficSource = 'Google Search';
                        elseif (str_contains($refHost, 'bing.')) $trafficSource = 'Bing Search';
                        elseif (str_contains($refHost, 'yahoo.')) $trafficSource = 'Yahoo Search';
                        elseif (str_contains($refHost, 'duckduckgo.')) $trafficSource = 'DuckDuckGo';
                        elseif (str_contains($refHost, 'yandex.')) $trafficSource = 'Yandex Search';
                        elseif (str_contains($refHost, 'facebook.') || str_contains($refHost, 'fb.')) $trafficSource = 'Facebook';
                        elseif (str_contains($refHost, 'instagram.')) $trafficSource = 'Instagram';
                        elseif (str_contains($refHost, 'whatsapp.') || str_contains($refHost, 'wa.me')) $trafficSource = 'WhatsApp';
                        elseif (str_contains($refHost, 'youtube.')) $trafficSource = 'YouTube';
                        elseif (str_contains($refHost, 'twitter.') || str_contains($refHost, 't.co') || str_contains($refHost, 'x.com')) $trafficSource = 'Twitter / X';
                        elseif (str_contains($refHost, 'linkedin.')) $trafficSource = 'LinkedIn';
                        elseif (str_contains($refHost, 'pinterest.')) $trafficSource = 'Pinterest';
                        elseif (str_contains($refHost, 'tiktok.')) $trafficSource = 'TikTok';
                        elseif (!empty($refHost) && !str_contains($refHost, 'ideaabd.com') && !str_contains($refHost, '127.0.0.1') && !str_contains($refHost, 'localhost')) {
                            $trafficSource = 'Referral (' . $refHost . ')';
                        }
                    }

                    $utmSource = $request->string('utm_source')->trim()->value() 
                        ?: ($request->string('ref')->trim()->value() ?: null);

                    // Descriptive Human Page Title
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
                        'device'         => $device,
                        'browser'        => $browser,
                        'os'             => $os,
                        'country'        => $countryName,
                        'country_code'   => $countryCode,
                        'city'           => null,
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
