<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        /** @var Response $response */
        $response = $next($request);

        // Prevent clickjacking
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Prevent MIME sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Basic XSS protection (older browsers)
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Referrer policy — never leak the full admin URL to third parties
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Permissions Policy (formerly Feature-Policy) — restrict features
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=(), payment=(), usb=()');

        // Admin pages must never be cached by a shared proxy or the back button.
        if ($request->is('admin', 'admin/*', 'seller/*')) {
            $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, private');
            $response->headers->set('Pragma', 'no-cache');
        }

        // CSP: allow CDN fonts/icons, Bangla webfonts (fonts.maateen.me), Google AMP, Google Translate, Google AdSense & Ads.
        if (app()->environment('production')) {
            $cdn = 'cdnjs.cloudflare.com cdn.jsdelivr.net fonts.maateen.me cdn.ampproject.org';
            $googleTranslate = 'translate.google.com translate.googleapis.com translate-pa.googleapis.com';
            $googleAds = 'pagead2.googlesyndication.com googleads.g.doubleclick.net tpc.googlesyndication.com adservice.google.com ep2.adtrafficquality.google www.google.com www.googletagservices.com';
            $csp = implode('; ', [
                "default-src 'self'",
                "script-src 'self' 'unsafe-inline' 'unsafe-eval' {$cdn} {$googleTranslate} {$googleAds}",
                "style-src 'self' 'unsafe-inline' fonts.googleapis.com {$cdn} {$googleTranslate} {$googleAds}",
                "img-src 'self' data: blob: https: {$googleTranslate} {$googleAds}",
                "font-src 'self' data: fonts.gstatic.com {$cdn}",
                "connect-src 'self' fonts.googleapis.com fonts.gstatic.com {$cdn} {$googleTranslate} {$googleAds}",
                "frame-src 'self' {$googleTranslate} {$googleAds}",
                "frame-ancestors 'self'",
                "form-action 'self'",
                "base-uri 'self'",
                "object-src 'none'",
                'upgrade-insecure-requests',
            ]) . ';';
            $response->headers->set('Content-Security-Policy', $csp);

            // Enforce HSTS
            $response->headers->set('Strict-Transport-Security', 'max-age=63072000; includeSubDomains; preload');
        }

        return $response;
    }
}
