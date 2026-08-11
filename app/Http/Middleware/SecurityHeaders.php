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

        // Referrer policy
        $response->headers->set('Referrer-Policy', 'no-referrer-when-downgrade');

        // Permissions Policy (formerly Feature-Policy) — restrict features
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');

        // CSP: allow CDN fonts/icons and inline scripts needed by Bootstrap/Blade
        if (app()->environment('production')) {
            $csp = "default-src 'self'; script-src 'self' 'unsafe-inline' cdnjs.cloudflare.com cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' fonts.googleapis.com cdnjs.cloudflare.com; img-src 'self' data: https:; font-src 'self' fonts.gstatic.com cdnjs.cloudflare.com; connect-src 'self';";
            $response->headers->set('Content-Security-Policy', $csp);

            // Enforce HSTS
            $response->headers->set('Strict-Transport-Security', 'max-age=63072000; includeSubDomains; preload');
        }

        return $response;
    }
}
