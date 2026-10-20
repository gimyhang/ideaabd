<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Admin\ImpersonationController;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * While an administrator is "viewing as" another user the session is read-only: nothing can be changed in that
 * user's name (no purchases, payouts, passwords, profile edits). Only leaving the view and signing out are allowed.
 */
class BlockWhileImpersonating
{
    private const ALLOWED = ['impersonate.leave', 'logout'];

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->hasSession() && $request->session()->has(ImpersonationController::SESSION_KEY)
            && !$request->isMethodSafe()
            && !in_array($request->route()?->getName(), self::ALLOWED, true)) {
            $message = 'ইউজার-ভিউ মোডে কিছু পরিবর্তন করা যায় না। ওপরের বার থেকে অ্যাডমিনে ফিরে যান।';

            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message], 403);
            }

            return redirect()->back()->with('error', $message);
        }

        return $next($request);
    }
}
