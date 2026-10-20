<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AdminAccessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * "View as user": the real administrator can open any non-admin user's own dashboard to see exactly what they see.
 * The session is read-only (see BlockWhileImpersonating), every start/stop is written to the admin activity log,
 * and an administrator can never be impersonated.
 */
class ImpersonationController extends Controller
{
    public const SESSION_KEY = 'impersonator_id';

    public function __construct(private readonly AdminAccessService $access) {}

    public function start(Request $request, User $user): RedirectResponse
    {
        $admin = $request->user();

        abort_unless($admin && $admin->isAdmin(), 403, 'শুধু মূল অ্যাডমিন এটি করতে পারেন।');
        abort_if($request->session()->has(self::SESSION_KEY), 403, 'আগে বর্তমান ইউজার-ভিউ থেকে ফিরে আসুন।');
        abort_if($user->id === $admin->id || $user->isAdmin(), 403, 'অ্যাডমিন অ্যাকাউন্ট এভাবে খোলা যায় না।');

        $this->access->log('impersonation_start', "অ্যাডমিন '{$admin->name}' ইউজার '{$user->name}' (#{$user->id}) এর ড্যাশবোর্ড দেখা শুরু করেছেন", 'user', $user->id);

        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->put(self::SESSION_KEY, $admin->id);

        return redirect()->route('user.portal')->with('info', "আপনি এখন '{$user->name}' এর ড্যাশবোর্ড দেখছেন (শুধু দেখা, পরিবর্তন করা যাবে না)।");
    }

    public function leave(Request $request): RedirectResponse
    {
        $adminId = $request->session()->get(self::SESSION_KEY);
        $admin = $adminId ? User::find($adminId) : null;

        if (!$admin || !$admin->isAdmin() || !$admin->is_active) {
            // Never trust a stale or tampered key: end the session instead of switching accounts.
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login');
        }

        $viewed = $request->user();
        Auth::login($admin);
        $request->session()->forget(self::SESSION_KEY);
        $request->session()->regenerate();

        $this->access->log('impersonation_end', "অ্যাডমিন '{$admin->name}' ইউজার '" . ($viewed?->name ?? '?') . "' এর ভিউ থেকে ফিরে এসেছেন", 'user', $viewed?->id);

        return redirect()->route('admin.users');
    }
}
