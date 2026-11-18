<?php

namespace Modules\SEO\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Modules\SEO\Models\SeoRedirect;

/**
 * Applies 301/302 rules to addresses that DO match a route (e.g. an old book slug).
 * Addresses that match no route never reach this middleware; those are redirected from the
 * 404 handler in bootstrap/app.php, using the same SeoRedirect::findForPath() lookup.
 */
class SeoRedirectMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Only run for GET and HEAD requests on non-admin / non-api paths
        if (!$request->isMethodSafe() || $request->is('admin*') || $request->is('api*')) {
            return $next($request);
        }

        try {
            // Cached table check: avoids an information_schema query on every page view.
            if (\App\Support\SchemaCache::has('seo_redirects')) {
                $redirect = SeoRedirect::findForPath('/' . ltrim($request->getPathInfo(), '/'));
                if ($redirect) {
                    return $redirect->toResponse($request);
                }
            }
        } catch (\Throwable $e) {
            // Silently proceed if database is unavailable
        }

        return $next($request);
    }
}
