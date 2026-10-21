<?php

namespace App\Http\Middleware;

use App\Support\SiteSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Makes the "Maintenance mode" switch in System Settings actually do something.
 * Visitors get a 503 page; administrators, the admin area, login, health checks,
 * payment callbacks and static assets keep working so nobody gets locked out.
 */
class MaintenanceGuard
{
    /** Paths that must stay reachable while the site is in maintenance. */
    private const EXEMPT = [
        'admin', 'admin/*', 'login', 'login/*', 'logout', 'up',
        'payment/*', 'storage/*', 'build/*', 'images/*', 'css/*', 'js/*', 'fonts/*', 'modules/*',
        'robots.txt', 'favicon.ico', 'indexnow-key.txt',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $setting = SiteSetting::get('maintenance_mode');

        if (! is_array($setting) || empty($setting['enabled'])) {
            return $next($request);
        }

        if ($request->is(...self::EXEMPT)) {
            return $next($request);
        }

        $user = $request->user();
        if ($user && method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return $next($request);
        }

        $reason = trim((string) ($setting['reason'] ?? ''));

        if ($request->expectsJson()) {
            return response()->json(['message' => $reason !== '' ? $reason : 'The site is under maintenance.'], 503, ['Retry-After' => 3600]);
        }

        return response()->view('errors.maintenance', ['reason' => $reason], 503, ['Retry-After' => 3600]);
    }
}
