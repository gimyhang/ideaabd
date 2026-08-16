<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Cookie;

class AffiliateTracking
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->has('ref')) {
            // Check if the ref parameter contains a valid user ID (or affiliate link ID in the future)
            $refId = $request->query('ref');
            
            // Store the affiliate ID in a cookie for 30 days (43200 minutes)
            if (!Cookie::has('ref_id') || Cookie::get('ref_id') != $refId) {
                Cookie::queue('ref_id', $refId, 43200);
            }
        }

        return $response;
    }
}
