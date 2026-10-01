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
        // Trust all reverse proxies (e.g. Cloudflare / Nginx / mobile gateways)
        $middleware->trustProxies(at: '*');

        $middleware->validateCsrfTokens(except: [
            'payment/bkash/callback',
            'payment/nagad/callback',
            'payment/sslcommerz/success',
            'payment/sslcommerz/fail',
            'payment/sslcommerz/cancel',
            'payment/sslcommerz/ipn',
            'auth/captcha/*',
            'register/send-email-otp',
            'register/verify-email-otp',
            'register/check-phone',
            'register/send-otp',
            'register/verify-otp',
            'register/complete',
            'register/complete-unified',
        ]);
        
        $middleware->web(append: [
            \Modules\SEO\Http\Middleware\SeoRedirectMiddleware::class,
            \App\Http\Middleware\SecurityHeaders::class,
            \App\Http\Middleware\SetLocale::class,
            \App\Http\Middleware\AffiliateTracking::class,
            \App\Http\Middleware\TrackVisitor::class,
            \App\Http\Middleware\MinifyHtmlResponse::class,
        ]);

        $middleware->alias([
            'role'       => \App\Http\Middleware\RoleMiddleware::class,
            'permission' => \App\Http\Middleware\PermissionMiddleware::class,
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