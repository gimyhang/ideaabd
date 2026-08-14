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

        // CSP: allow CDN fonts/icons and inline scripts needed by Bootstrap/Blade.
        // connect-src has to include the CDNs too — Bootstrap and Font Awesome ship
        // sourcemap references that the browser fetches over XHR, and a bare
        // 'self' blocks them (visible as CSP errors in the console).
        if (app()->environment('production')) {
            $cdn = 'cdnjs.cloudflare.com cdn.jsdelivr.net';
            $csp = implode('; ', [
                "default-src 'self'",
                "script-src 'self' 'unsafe-inline' {$cdn}",
                "style-src 'self' 'unsafe-inline' fonts.googleapis.com {$cdn}",
                "img-src 'self' data: blob: https:",
                "font-src 'self' data: fonts.gstatic.com {$cdn}",
                "connect-src 'self' fonts.googleapis.com fonts.gstatic.com {$cdn}",
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
