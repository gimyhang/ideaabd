<?php

namespace Modules\SEO\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Modules\SEO\Models\SeoRedirect;
use Illuminate\Support\Facades\Schema;

class SeoRedirectMiddleware
{
    /**
     * Handle an incoming request and check if a 301/302 SEO redirect rule applies.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only run for GET and HEAD requests on non-admin / non-api paths
        if (!$request->isMethodSafe() || $request->is('admin*') || $request->is('api*')) {
            return $next($request);
        }

        try {
            if (Schema::hasTable('seo_redirects')) {
                $path = '/' . ltrim($request->path(), '/');
                $redirect = SeoRedirect::where('is_active', true)
                    ->where(function ($q) use ($path) {
                        $q->where('source_url', $path)
                          ->orWhere('source_url', rtrim($path, '/'))
                          ->orWhere('source_url', $path . '/');
                    })
                    ->first();

                if ($redirect) {
                    // Increment hit count
                    $redirect->increment('hits_count');
                    $redirect->update(['last_hit_at' => now()]);

                    $target = str_starts_with($redirect->target_url, 'http') 
                        ? $redirect->target_url 
                        : url($redirect->target_url);

                    return redirect($target, $redirect->status_code ?: 301);
                }
            }
        } catch (\Throwable $e) {
            // Silently proceed if database is unavailable
        }

        return $next($request);
    }
}
