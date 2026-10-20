<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Session\TokenMismatchException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Reverse proxies whose X-Forwarded-* headers are believed. Set TRUSTED_PROXIES in .env to the proxy IPs/CIDRs
        // (comma separated, e.g. "127.0.0.1,173.245.48.0/20" for Nginx + Cloudflare). Left empty it trusts every sender,
        // which lets a client fake its IP and slip past the login rate limit and IP whitelists.
        $trustedProxies = trim((string) env('TRUSTED_PROXIES', ''));
        $middleware->trustProxies(at: $trustedProxies !== '' ? array_map('trim', explode(',', $trustedProxies)) : '*');

        $middleware->validateCsrfTokens(except: [
            'payment/bkash/callback',
            'payment/nagad/callback',
            'payment/sslcommerz/success',
            'payment/sslcommerz/fail',
            'payment/sslcommerz/cancel',
            'payment/sslcommerz/ipn',
            'auth/captcha/*',
        ]);
        
        // Appended (not prepended) so TrustProxies has already worked out the real visitor IP.
        $middleware->append(\App\Http\Middleware\AbuseGuard::class);

        $middleware->web(prepend: [
            \App\Http\Middleware\AbuseGuard::class,
        ]);

        $middleware->web(append: [
            \Modules\SEO\Http\Middleware\SeoRedirectMiddleware::class,
            \App\Http\Middleware\SecurityHeaders::class,
            \App\Http\Middleware\SetLocale::class,
            \App\Http\Middleware\AffiliateTracking::class,
            \App\Http\Middleware\TrackVisitor::class,
            \App\Http\Middleware\BlockWhileImpersonating::class,
            \App\Http\Middleware\MinifyHtmlResponse::class,
        ]);

        $middleware->alias([
            'role'       => \App\Http\Middleware\RoleMiddleware::class,
            'permission' => \App\Http\Middleware\PermissionMiddleware::class,
            'portal.approved' => \App\Http\Middleware\EnsurePortalApproved::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Gracefully handle expired CSRF session tokens from mobile browsers/PWA
        $exceptions->render(function (TokenMismatchException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'আপনার ব্রাউজার সেশনের মেয়াদ শেষ হয়েছিল। অনুগ্রহ করে পুনরায় চেষ্টা করুন।'
                ], 419);
            }
            return redirect()->back()
                ->withInput($request->except('password', 'password_confirmation', '_token'))
                ->with('error', 'আপনার ব্রাউজার সেশনের মেয়াদ শেষ হয়েছিল। অনুগ্রহ করে পুনরায় লগইন বা সাবমিট করুন।');
        });

        // Track and log 404 broken links for SEO monitoring & 301 redirection
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, $request) {
            try {
                if (!$request->is('admin*') && !$request->is('api*') && class_exists(\Modules\SEO\Models\SeoBrokenLink::class) && \Illuminate\Support\Facades\Schema::hasTable('seo_broken_links')) {
                    \Modules\SEO\Models\SeoBrokenLink::logHit(
                        $request->fullUrl(),
                        $request->header('referer'),
                        $request->userAgent(),
                        $request->ip()
                    );
                }
            } catch (\Throwable) {}
        });
    })->create();