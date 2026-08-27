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
        ]);
        
        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
            \App\Http\Middleware\AffiliateTracking::class,
            \App\Http\Middleware\TrackVisitor::class,
            \App\Http\Middleware\MinifyHtmlResponse::class,
        ]);

        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
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
    })->create();