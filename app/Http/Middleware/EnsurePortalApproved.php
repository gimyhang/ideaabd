<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Keeps author / publisher / seller applicants out of their portals until an admin approves them.
 * Admins and already-approved users pass straight through.
 *
 * With a portal argument (`portal.approved:author`, `portal.approved:publisher`) the route also belongs to
 * that portal only: a buyer, or a member of another portal, is turned away. Admins may always enter.
 */
class EnsurePortalApproved
{
    public function handle(Request $request, Closure $next, ?string $portal = null): Response
    {
        $user = $request->user();

        if ($user && !$user->isAdmin()) {
            if ($portal !== null) {
                $belongs = match ($portal) {
                    'author'    => $user->isAuthor(),
                    'publisher' => $user->isPublisher(),
                    'seller'    => $user->isSeller(),
                    default     => false,
                };
                if (!$belongs) {
                    if ($request->expectsJson()) {
                        return response()->json(['message' => 'Forbidden'], 403);
                    }
                    abort(403, 'এই পোর্টালে প্রবেশের অনুমতি আপনার নেই।');
                }
            }

            if (in_array($user->reg_type, ['author', 'publisher', 'seller'], true) && !$user->isApproved()) {
                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Pending approval'], 403);
                }
                return redirect()->route('pending.approval');
            }
        }

        return $next($request);
    }
}
