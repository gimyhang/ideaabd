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
        if ($request->isMethod('GET') && !$request->ajax() && !$request->is('admin*', 'storage*', 'css*', 'js*', 'images*', 'vendor*', 'favicon*')) {
            try {
                if (Schema::hasTable('visitor_logs')) {
                    $userAgent = $request->userAgent() ?? '';
                    $device = 'desktop';
                    if (preg_match('/(tablet|ipad|playbook)|(android(?!.*(mobi|opera mini)))/i', $userAgent)) {
                        $device = 'tablet';
                    } elseif (preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|android|iemobile)/i', $userAgent)) {
                        $device = 'mobile';
                    }

                    $browser = 'Other';
                    if (str_contains($userAgent, 'Edge') || str_contains($userAgent, 'Edg/')) $browser = 'Edge';
                    elseif (str_contains($userAgent, 'Chrome')) $browser = 'Chrome';
                    elseif (str_contains($userAgent, 'Safari')) $browser = 'Safari';
                    elseif (str_contains($userAgent, 'Firefox')) $browser = 'Firefox';
                    elseif (str_contains($userAgent, 'Opera') || str_contains($userAgent, 'OPR/')) $browser = 'Opera';

                    $os = 'Other';
                    if (str_contains($userAgent, 'Windows')) $os = 'Windows';
                    elseif (str_contains($userAgent, 'Android')) $os = 'Android';
                    elseif (str_contains($userAgent, 'iPhone') || str_contains($userAgent, 'iPad')) $os = 'iOS';
                    elseif (str_contains($userAgent, 'Mac OS')) $os = 'macOS';
                    elseif (str_contains($userAgent, 'Linux')) $os = 'Linux';

                    VisitorLog::create([
                        'ip_address'  => $request->ip(),
                        'url'         => substr($request->fullUrl(), 0, 1000),
                        'page_title'  => $request->route()?->getName() ?: 'Page',
                        'route_name'  => $request->route()?->getName(),
                        'device'      => $device,
                        'browser'     => $browser,
                        'os'          => $os,
                        'referer'     => substr((string)$request->header('referer'), 0, 1000) ?: null,
                        'user_id'     => auth()->id(),
                        'visited_at'  => now(),
                    ]);
                }
            } catch (\Throwable $e) {
                // Fail silently so visitor experience is never affected
            }
        }

        return $response;
    }
}
