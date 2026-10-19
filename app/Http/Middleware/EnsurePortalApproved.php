<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Keeps author / publisher / seller applicants out of their portals until an admin approves them.
 * Admins and already-approved users pass straight through.
 */
class EnsurePortalApproved
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && !$user->isAdmin()
            && in_array($user->reg_type, ['author', 'publisher', 'seller'], true)
            && !$user->isApproved()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Pending approval'], 403);
            }
            return redirect()->route('pending.approval');
        }

        return $next($request);
    }
}
