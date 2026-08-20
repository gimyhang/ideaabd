<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!$request->user()) {
            return redirect()->route('login');
        }

        if (!in_array($request->user()->role, $roles)) {
            abort(403, 'আপনার এই পেজে প্রবেশের অনুমতি নেই।');
        }

        // Ensure user is active and approved before granting portal access
        if (!$request->user()->is_active || (in_array($request->user()->role, ['author', 'seller', 'publisher'], true) && $request->user()->reg_status !== 'approved')) {
            auth()->logout();
            return redirect()->route('login')->withErrors([
                'email' => 'আপনার রেজিস্ট্রেশনটি এখনও অ্যাডমিন কর্তৃক অনুমোদিত হয়নি। অনুমোদন সম্পন্ন হলে আপনি ড্যাশবোর্ডে প্রবেশ করতে পারবেন।'
            ]);
        }

        return $next($request);
    }
}
