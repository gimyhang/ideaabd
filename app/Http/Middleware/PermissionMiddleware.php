<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    /**
     * Handle an incoming request and evaluate dynamic granular permissions.
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        // 1. Account Active & Status Check
        if (! $user->is_active) {
            auth()->logout();
            return redirect()->route('login')->withErrors([
                'email' => 'আপনার অ্যাকাউন্টটি সাময়িকভাবে স্থগিত (Suspended) করা হয়েছে। অনুগ্রহ করে সিইও/প্রধান অ্যাডমিনের সাথে যোগাযোগ করুন।',
            ]);
        }

        // 2. IP Whitelist Check (if configured for this staff member)
        if (! empty($user->ip_whitelist)) {
            $allowedIps = array_filter(array_map('trim', explode(',', $user->ip_whitelist)));
            $clientIp = $request->ip();
            if (! empty($allowedIps) && ! in_array($clientIp, $allowedIps, true) && ! in_array('*', $allowedIps, true)) {
                auth()->logout();
                return redirect()->route('login')->withErrors([
                    'email' => "আপনার বর্তমান আইপি ({$clientIp}) থেকে প্রবেশের অনুমতি নেই। সিকিউরিটি রেস্ট্রিকশন সক্রিয় রয়েছে।",
                ]);
            }
        }

        // 3. Super Admin has unrestricted access to all permissions
        if ($user->isAdmin()) {
            return $next($request);
        }

        // 4. Check requested permissions (OR logic if multiple supplied)
        $hasAccess = false;
        foreach ($permissions as $permKey) {
            if ($user->hasPermission($permKey)) {
                $hasAccess = true;
                break;
            }
        }

        if (! $hasAccess) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'অনুমতি নেই: এই রিসোর্স বা অ্যাকশনে প্রবেশের অনুমতি আপনার রোলে বরাদ্দ নেই।',
                ], 403);
            }

            abort(403, 'অ্যাক্সেস ডিনাইড: আপনার এই ফিচার বা পেজটিতে প্রবেশের অনুমতি নেই। বিস্তারিত জানতে অ্যাডমিনের সাথে যোগাযোগ করুন।');
        }

        return $next($request);
    }
}
