<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoginSecurityLog;
use App\Models\PasswordResetRequest;
use App\Models\User;
use App\Services\AdminAccessService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Models\AdminActivityLog;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UserSecurityAdminController extends Controller
{
    public function __construct(private readonly AdminAccessService $accessService)
    {
    }

    /** Action types shown on the "Activity" tab. */
    private const SECURITY_ACTIONS = [
        'auto_generate_password', 'generate_otp', 'unblock_ip', 'block_ip',
        'bulk_ip', 'terminate_sessions', 'force_logout',
    ];

    /**
     * Display Security Dashboard with Blocked IPs, Password Reset Requests, activity and health checks.
     */
    public function index(Request $request): View
    {
        $tab = $request->string('tab')->trim()->value() ?: 'requests';
        if (! in_array($tab, ['requests', 'ips', 'issues', 'blocked', 'activity'], true)) {
            $tab = 'requests';
        }
        $q = Str::limit(trim((string) $request->query('q', '')), 60, '');
        $status = (string) $request->query('status', '');

        $requests = null;
        $blockedLogs = null;
        $activity = null;

        if ($tab === 'requests') {
            $requests = PasswordResetRequest::query()
                ->with(['user', 'resolvedBy'])
                ->when($q !== '', fn ($w) => $w->where(fn ($x) => $x
                    ->where('identity', 'like', "%{$q}%")
                    ->orWhere('user_name', 'like', "%{$q}%")
                    ->orWhere('user_ip', 'like', "%{$q}%")))
                ->when(in_array($status, ['pending', 'resolved', 'rejected'], true), fn ($w) => $w->where('status', $status))
                ->latest()
                ->paginate(15, ['*'], 'req_page')
                ->withQueryString();
        } elseif ($tab === 'activity') {
            $activity = Schema::hasTable('admin_activity_logs')
                ? AdminActivityLog::query()
                    ->with('user:id,name,email')
                    ->whereIn('action_type', self::SECURITY_ACTIONS)
                    ->when($q !== '', fn ($w) => $w->where(fn ($x) => $x
                        ->where('description', 'like', "%{$q}%")
                        ->orWhere('ip_address', 'like', "%{$q}%")))
                    ->latest()
                    ->paginate(20, ['*'], 'act_page')
                    ->withQueryString()
                : null;
        } else {
            $blockedLogs = $this->ipQuery($tab, $q, $status)
                ->latest('updated_at')
                ->paginate(15, ['*'], 'ip_page')
                ->withQueryString();
        }

        return view('admin.users-security', [
            'requests'    => $requests,
            'blockedLogs' => $blockedLogs,
            'activity'    => $activity,
            'stats'       => $this->stats(),
            'health'      => $this->health($request),
            'threats'     => $this->threatBreakdown(),
            'tab'         => $tab,
            'q'           => $q,
            'status'      => $status,
            'myIp'        => $request->ip(),
        ]);
    }

    /** Live KPI numbers (also served as JSON for the auto-refresh). */
    public function liveStats(): JsonResponse
    {
        return response()->json(['stats' => $this->stats(), 'threats' => $this->threatBreakdown(), 'at' => now()->format('h:i:s A')]);
    }

    private function stats(): array
    {
        return [
            'pending_requests'  => PasswordResetRequest::where('status', 'pending')->count(),
            'resolved_requests' => PasswordResetRequest::where('status', 'resolved')->count(),
            'security_issues'   => LoginSecurityLog::where('is_security_issue', true)->where('is_blocked', false)->count(),
            'total_blocked_ips' => LoginSecurityLog::where('is_blocked', true)->count(),
            'locked_10min_ips'  => LoginSecurityLog::where('is_blocked', false)->where('locked_until', '>', Carbon::now())->count(),
            'forced_changes'    => User::where('must_change_password', true)->count(),
        ];
    }

    private function threatBreakdown(): array
    {
        $rows = LoginSecurityLog::query()
            ->selectRaw("COALESCE(NULLIF(threat_level, ''), 'low') as lvl, COUNT(*) as c")
            ->groupBy('lvl')
            ->pluck('c', 'lvl')
            ->all();

        return [
            'critical' => (int) ($rows['critical'] ?? 0),
            'high'     => (int) ($rows['high'] ?? 0),
            'medium'   => (int) ($rows['medium'] ?? 0),
            'low'      => (int) ($rows['low'] ?? 0),
        ];
    }

    /** Shared filter for the IP tables and the CSV export. */
    private function ipQuery(string $tab, string $q = '', string $status = '')
    {
        $query = LoginSecurityLog::query();

        if ($tab === 'issues') {
            $query->where('is_security_issue', true);
        } elseif ($tab === 'blocked') {
            $query->where('is_blocked', true);
        }

        if ($status === 'blocked') {
            $query->where('is_blocked', true);
        } elseif ($status === 'locked') {
            $query->where('is_blocked', false)->where('locked_until', '>', Carbon::now());
        } elseif ($status === 'issue') {
            $query->where('is_security_issue', true);
        } elseif (in_array($status, ['critical', 'high', 'medium', 'low'], true)) {
            $query->where('threat_level', $status);
        }

        if ($q !== '') {
            $query->where(fn ($w) => $w->where('ip_address', 'like', "%{$q}%")->orWhere('last_username', 'like', "%{$q}%"));
        }

        return $query;
    }

    /**
     * Security posture checklist. Each check: key, label, state (ok|warn|bad), detail, weight.
     */
    private function health(Request $request): array
    {
        $isProd = app()->environment('production');
        $admins = User::where('role', User::ROLE_ADMIN)->get(['id', 'password', 'ip_whitelist', 'is_active']);

        // bcrypt checks are slow: cache the weak-password result briefly.
        $weakAdmins = Cache::remember('security.weak_admin_pw.' . $admins->pluck('id')->implode('-'), 600, function () use ($admins) {
            $defaults = ['admin123456', 'admin123', 'password', '12345678', 'Admin@123'];
            return $admins->filter(function ($a) use ($defaults) {
                foreach ($defaults as $d) {
                    if (Hash::check($d, (string) $a->password)) {
                        return true;
                    }
                }
                return false;
            })->count();
        });

        $withWhitelist = $admins->filter(fn ($a) => ! empty($a->ip_whitelist))->count();
        $lifetime = (int) config('session.lifetime', 120);
        $cookieSecure = (bool) config('session.secure') || $request->isSecure();
        $flagged = LoginSecurityLog::where('is_blocked', false)->where('is_security_issue', true)->count();

        $checks = [
            ['key' => 'debug', 'label' => 'Debug mode off', 'weight' => 20,
                'state' => ! config('app.debug') ? 'ok' : ($isProd ? 'bad' : 'warn'),
                'detail' => ! config('app.debug') ? 'APP_DEBUG=false' : ($isProd ? 'APP_DEBUG is on in production — turn it off now' : 'On locally (must be off on the live site)')],
            ['key' => 'https', 'label' => 'HTTPS & secure cookies', 'weight' => 15,
                'state' => $cookieSecure ? 'ok' : ($isProd ? 'bad' : 'warn'),
                'detail' => $cookieSecure ? 'Connection and cookies are secure' : 'Enable HTTPS and set SESSION_SECURE_COOKIE=true'],
            ['key' => 'weakpw', 'label' => 'Weak admin passwords', 'weight' => 25,
                'state' => $weakAdmins === 0 ? 'ok' : 'bad',
                'detail' => $weakAdmins === 0 ? 'No admin uses a default or easy password' : "{$weakAdmins} admin(s) use an easy password — change it now"],
            ['key' => 'session', 'label' => 'Session lifetime', 'weight' => 10,
                'state' => $lifetime <= 120 ? 'ok' : 'warn',
                'detail' => "{$lifetime} minutes" . ($lifetime <= 120 ? '' : ' — keep it at 120 minutes or less')],
            ['key' => 'whitelist', 'label' => 'Admin IP whitelist', 'weight' => 15,
                'state' => ($admins->count() && $withWhitelist === $admins->count()) ? 'ok' : 'warn',
                'detail' => "{$withWhitelist}/{$admins->count()} admins have an IP restriction"],
            ['key' => 'threats', 'label' => 'Active threats', 'weight' => 15,
                'state' => $flagged > 10 ? 'warn' : 'ok',
                'detail' => "{$flagged} flagged IP(s) (under 10 is normal)"],
        ];

        $total = array_sum(array_column($checks, 'weight'));
        $earned = 0;
        foreach ($checks as $c) {
            $earned += $c['state'] === 'ok' ? $c['weight'] : ($c['state'] === 'warn' ? $c['weight'] * 0.5 : 0);
        }
        $score = (int) round($earned / max(1, $total) * 100);

        return [
            'checks' => $checks,
            'score'  => $score,
            'grade'  => $score >= 85 ? 'strong' : ($score >= 60 ? 'fair' : 'weak'),
        ];
    }

    /** Autocomplete for the user picker in the modals. */
    public function lookup(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));
        if (mb_strlen($q) < 2) {
            return response()->json([]);
        }

        $users = User::query()
            ->where(fn ($w) => $w->where('email', 'like', "{$q}%")->orWhere('phone', 'like', "{$q}%")->orWhere('name', 'like', "%{$q}%"))
            ->orderBy('name')
            ->limit(8)
            ->get(['id', 'name', 'email', 'phone', 'role', 'is_active'])
            ->map(fn ($u) => [
                'id' => $u->id, 'name' => $u->name, 'email' => $u->email, 'phone' => $u->phone,
                'role' => $u->role, 'active' => (bool) $u->is_active,
            ]);

        return response()->json($users);
    }

    /** Sign a user out of every device (invalidates sessions and remember-me). */
    public function forceLogout(Request $request): RedirectResponse
    {
        $request->validate(['user_id' => 'required|integer|exists:users,id']);
        $user = User::findOrFail($request->integer('user_id'));

        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot force-logout your own account.');
        }

        $user->forceFill([
            'remember_token'         => Str::random(60),
            'session_invalidated_at' => now(),
        ])->save();

        $this->accessService?->log('force_logout', "User {$user->name} ({$user->email}) was signed out of all devices", 'User', $user->id);

        return back()->with('success', "'{$user->name}' was signed out of all devices.");
    }

    /** Bulk block / unblock selected IPs. */
    public function bulk(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'action' => 'required|in:unblock,block',
            'ips'    => 'required|array|min:1|max:200',
            'ips.*'  => 'required|ip',
        ]);

        $ips = array_values(array_unique($data['ips']));
        $done = 0;
        foreach ($ips as $ip) {
            if ($data['action'] === 'block') {
                if ($ip === $request->ip()) {
                    continue; // never lock the admin out
                }
                LoginSecurityLog::blockIp($ip, 'Bulk block by admin', auth()->id());
            } else {
                LoginSecurityLog::unblockIp($ip, auth()->id());
            }
            $done++;
        }

        $label = $data['action'] === 'block' ? 'blocked' : 'unblocked';
        $this->accessService?->log('bulk_ip', "{$done} IP(s) bulk {$label}");

        return back()->with('success', "{$done} IP(s) {$label}.");
    }

    /** CSV export of the current IP list (respects tab + filters). */
    public function export(Request $request): StreamedResponse
    {
        $tab = (string) $request->query('tab', 'ips');
        $query = $this->ipQuery($tab, trim((string) $request->query('q', '')), (string) $request->query('status', ''))->latest('updated_at');

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['IP', 'Last username', 'Attempts', 'Blocked', 'Security issue', 'Threat', 'Locked until', 'Block reason', 'Updated']);
            $query->chunk(500, function ($rows) use ($out) {
                foreach ($rows as $r) {
                    fputcsv($out, [
                        $r->ip_address, $r->last_username, $r->attempt_count, $r->is_blocked ? 'yes' : 'no',
                        $r->is_security_issue ? 'yes' : 'no', $r->threat_level, $r->locked_until, $r->block_reason, $r->updated_at,
                    ]);
                }
            });
            fclose($out);
        }, 'security-ips-' . now()->format('Ymd-His') . '.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * Auto-Generate Strong Secure Password for any User Account.
     */
    public function autoGeneratePassword(Request $request)
    {
        $request->validate([
            'user_id'         => 'nullable|exists:users,id',
            'identity'        => 'nullable|string|max:255',
            'custom_password' => ['nullable', 'string', 'min:10', 'max:100', 'regex:/[A-Za-z]/', 'regex:/\d/'],
            'force_change'    => 'nullable|boolean',
            'length'          => 'nullable|integer|min:8|max:32',
        ]);

        $user = null;
        if ($request->filled('user_id')) {
            $user = User::findOrFail($request->user_id);
        } elseif ($request->filled('identity')) {
            $identity = trim((string)$request->identity);
            $user = User::where('email', $identity)->orWhere('phone', $identity)->orWhere('name', $identity)->first();
        }

        if (!$user) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'No user found for the given details.'], 404);
            }
            return back()->with('error', 'No user account found for the given details.');
        }

        // Generate strong international-standard password or use provided custom password
        if ($request->filled('custom_password')) {
            $plainPassword = trim((string)$request->custom_password);
        } else {
            $length = max(10, min(24, (int)$request->input('length', 12)));
            // Strong alphanumeric + symbols password
            $chars = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789!@#$%&*';
            $plainPassword = 'Idea';
            for ($i = 0; $i < $length - 4; $i++) {
                $plainPassword .= $chars[random_int(0, strlen($chars) - 1)];
            }
        }

        $forceChange = $request->boolean('force_change', true);

        // Update User Password & force change
        $user->password = Hash::make($plainPassword);
        $user->must_change_password = $forceChange;
        $user->is_active = true;
        $user->save();

        // Also unblock IP locks for this user's last login IP if exists
        if ($user->last_login_ip) {
            LoginSecurityLog::unblockIp($user->last_login_ip, auth()->id());
        }

        if ($this->accessService) {
            $this->accessService->log('auto_generate_password', "New password generated for {$user->name} ({$user->email})");
        }

        $resultData = [
            'user_id'      => $user->id,
            'user_name'    => $user->name,
            'user_email'   => $user->email,
            'user_phone'   => $user->phone,
            'password'     => $plainPassword,
            'force_change' => $forceChange,
            'login_url'    => route('login'),
        ];

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "New password generated for '{$user->name}'.",
                'data'    => $resultData,
            ]);
        }

        return back()->with('success_generated_password', $resultData)
            ->with('success', "New password generated for '{$user->name}'.");
    }

    /**
     * Generate One-Time Password (OTP) for a User / Help Request.
     */
    public function generateOtp(Request $request): RedirectResponse
    {
        $request->validate([
            'request_id' => 'nullable|exists:password_reset_requests,id',
            'user_id'    => 'nullable|exists:users,id',
            'identity'   => 'nullable|string|max:255',
        ]);

        $user = null;
        $helpRequest = null;

        if ($request->filled('request_id')) {
            $helpRequest = PasswordResetRequest::findOrFail($request->request_id);
            $user = $helpRequest->user;
            if (!$user && $helpRequest->identity) {
                $user = User::where('email', $helpRequest->identity)
                    ->orWhere('phone', $helpRequest->identity)
                    ->first();
            }
        } elseif ($request->filled('user_id')) {
            $user = User::findOrFail($request->user_id);
        } elseif ($request->filled('identity')) {
            $identity = trim((string)$request->identity);
            $user = User::where('email', $identity)->orWhere('phone', $identity)->first();
        }

        if (!$user) {
            return back()->with('error', 'No user account found for the given details.');
        }

        // Generate 6-digit numeric or alphanumeric secure OTP
        $otp = 'ID' . random_int(100000, 999999);
        $expiresAt = Carbon::now()->addHours(24);

        // Update User Password & force change
        $user->password = Hash::make($otp);
        $user->must_change_password = true;
        $user->otp_expires_at = $expiresAt;
        $user->is_active = true; // Ensure account is unblocked/active
        $user->save();

        // Also clean any IP lock for the user's IP if exists
        if ($user->last_login_ip) {
            LoginSecurityLog::unblockIp($user->last_login_ip, auth()->id());
        }

        // Mark help request as resolved
        if ($helpRequest) {
            $helpRequest->update([
                'status'         => 'resolved',
                'otp_code'       => substr($otp, 0, 2) . '••••••',
                'otp_expires_at' => $expiresAt,
                'resolved_by'    => auth()->id(),
                'resolved_at'    => Carbon::now(),
                'admin_notes'    => $request->input('admin_notes', 'One-time password generated and issued by admin.'),
            ]);
        }

        if ($this->accessService) {
            $this->accessService->log('generate_otp', "One-time password generated for {$user->name} ({$user->email})");
        }

        return back()->with('success_otp', [
            'user_name'  => $user->name,
            'user_email' => $user->email,
            'user_phone' => $user->phone,
            'otp'        => $otp,
            'expires_at' => $expiresAt->format('d M, Y h:i A'),
        ])->with('success', "One-time password generated for '{$user->name}'.");
    }

    /**
     * Unblock / Clean an IP Address.
     */
    public function unblockIp(Request $request): RedirectResponse
    {
        $request->validate([
            'ip_address' => 'required|ip',
        ]);

        $ip = trim((string)$request->ip_address);
        LoginSecurityLog::unblockIp($ip, auth()->id());

        if ($this->accessService) {
            $this->accessService->log('unblock_ip', "IP address '{$ip}' unblocked and cleaned");
        }

        return back()->with('success', "IP '{$ip}' was unblocked and cleaned. The user can sign in again.");
    }

    /**
     * Manually Block an IP Address.
     */
    public function blockIp(Request $request): RedirectResponse
    {
        $request->validate([
            'ip_address'   => 'required|ip',
            'block_reason' => 'nullable|string|max:255',
        ]);

        $ip = trim((string)$request->ip_address);
        if ($ip === $request->ip()) {
            return back()->with('error', 'You cannot block your own current IP — you would lock yourself out.');
        }
        $reason = $request->input('block_reason', 'Manual security block by admin');

        LoginSecurityLog::blockIp($ip, $reason, auth()->id());

        if ($this->accessService) {
            $this->accessService->log('block_ip', "IP address '{$ip}' blocked manually");
        }

        return back()->with('success', "IP '{$ip}' was blocked.");
    }

    /**
     * Clean all expired temporary locks.
     */
    public function cleanExpired(Request $request): RedirectResponse
    {
        LoginSecurityLog::where('is_blocked', false)
            ->where('locked_until', '<=', Carbon::now())
            ->delete();

        return back()->with('success', 'All expired temporary locks were cleaned.');
    }
}
