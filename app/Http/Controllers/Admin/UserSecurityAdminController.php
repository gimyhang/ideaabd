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
use Illuminate\Support\Str;

class UserSecurityAdminController extends Controller
{
    public function __construct(private readonly ?AdminAccessService $accessService = null)
    {
    }

    /**
     * Display Security Dashboard with Blocked IPs and Password Reset Requests.
     */
    public function index(Request $request): View
    {
        $tab = $request->string('tab')->trim()->value() ?: 'requests';

        // 1. Password Reset Requests
        $requests = PasswordResetRequest::query()
            ->with(['user', 'resolvedBy'])
            ->latest()
            ->paginate(15, ['*'], 'req_page')
            ->withQueryString();

        // 2. Blocked / Locked IPs & Security Issues
        $blockedLogsQuery = LoginSecurityLog::query();

        if ($tab === 'issues') {
            $blockedLogsQuery->where('is_security_issue', true);
        } elseif ($tab === 'blocked') {
            $blockedLogsQuery->where('is_blocked', true);
        }

        $blockedLogs = $blockedLogsQuery
            ->latest('updated_at')
            ->paginate(15, ['*'], 'ip_page')
            ->withQueryString();

        // Statistics
        $stats = [
            'pending_requests'  => PasswordResetRequest::where('status', 'pending')->count(),
            'resolved_requests' => PasswordResetRequest::where('status', 'resolved')->count(),
            'security_issues'   => LoginSecurityLog::where('is_security_issue', true)->where('is_blocked', false)->count(),
            'total_blocked_ips' => LoginSecurityLog::where('is_blocked', true)->count(),
            'locked_10min_ips'  => LoginSecurityLog::where('is_blocked', false)
                ->where('locked_until', '>', Carbon::now())
                ->count(),
        ];

        return view('admin.users-security', compact('requests', 'blockedLogs', 'stats', 'tab'));
    }

    /**
     * Auto-Generate Strong Secure Password for any User Account.
     */
    public function autoGeneratePassword(Request $request)
    {
        $request->validate([
            'user_id'         => 'nullable|exists:users,id',
            'identity'        => 'nullable|string|max:255',
            'custom_password' => 'nullable|string|min:6|max:100',
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
                return response()->json(['success' => false, 'message' => 'প্রদত্ত তথ্য অনুযায়ী কোনো ব্যবহারকারী পাওয়া যায়নি।'], 404);
            }
            return back()->with('error', 'প্রদত্ত তথ্য অনুযায়ী কোনো ব্যবহারকারী অ্যাকাউন্ট পাওয়া যায়নি।');
        }

        // Generate strong international-standard password or use provided custom password
        if ($request->filled('custom_password')) {
            $plainPassword = trim((string)$request->custom_password);
        } else {
            $length = max(10, min(24, (int)$request->input('length', 12)));
            // Strong alphanumeric + symbols password
            $chars = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789!@#$%&*';
            $plainPassword = 'Idea' . substr(str_shuffle($chars), 0, $length - 4);
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
            $this->accessService->log('auto_generate_password', "ব্যবহারকারী {$user->name} ({$user->email}) এর জন্য নতুন পাসওয়ার্ড তৈরি করা হয়েছে");
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
                'message' => "ব্যবহারকারী '{$user->name}' এর জন্য নতুন পাসওয়ার্ড সফলভাবে তৈরি হয়েছে!",
                'data'    => $resultData,
            ]);
        }

        return back()->with('success_generated_password', $resultData)
            ->with('success', "ব্যবহারকারী '{$user->name}' এর জন্য নতুন পাসওয়ার্ড সফলভাবে তৈরি হয়েছে!");
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
            return back()->with('error', 'প্রদত্ত তথ্য অনুযায়ী কোনো ব্যবহারকারী অ্যাকাউন্ট পাওয়া যায়নি।');
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
                'otp_code'       => $otp,
                'otp_expires_at' => $expiresAt,
                'resolved_by'    => auth()->id(),
                'resolved_at'    => Carbon::now(),
                'admin_notes'    => $request->input('admin_notes', 'অ্যাডমিন কর্তৃক ওয়ানটাইম পাসওয়ার্ড তৈরি ও প্রদান করা হয়েছে।'),
            ]);
        }

        if ($this->accessService) {
            $this->accessService->log('generate_otp', "ব্যবহারকারী {$user->name} ({$user->email}) এর জন্য ওয়ানটাইম পাসওয়ার্ড তৈরি করা হয়েছে");
        }

        return back()->with('success_otp', [
            'user_name'  => $user->name,
            'user_email' => $user->email,
            'user_phone' => $user->phone,
            'otp'        => $otp,
            'expires_at' => $expiresAt->format('d M, Y h:i A'),
        ])->with('success', "ব্যবহারকারী '{$user->name}' এর জন্য ওয়ানটাইম পাসওয়ার্ড সফলভাবে তৈরি হয়েছে!");
    }

    /**
     * Unblock / Clean an IP Address.
     */
    public function unblockIp(Request $request): RedirectResponse
    {
        $request->validate([
            'ip_address' => 'required|string|max:45',
        ]);

        $ip = trim((string)$request->ip_address);
        LoginSecurityLog::unblockIp($ip, auth()->id());

        if ($this->accessService) {
            $this->accessService->log('unblock_ip', "আইপি অ্যাড্রেস '{$ip}' সফলভাবে আনব্লক ও ক্লিন করা হয়েছে");
        }

        return back()->with('success', "আইপি '{$ip}' সফলভাবে আনব্লক ও ক্লিন করা হয়েছে! ব্যবহারকারী এখন পুনরায় লগইন করতে পারবেন।");
    }

    /**
     * Manually Block an IP Address.
     */
    public function blockIp(Request $request): RedirectResponse
    {
        $request->validate([
            'ip_address'   => 'required|string|max:45',
            'block_reason' => 'nullable|string|max:255',
        ]);

        $ip = trim((string)$request->ip_address);
        $reason = $request->input('block_reason', 'অ্যাডমিন কর্তৃক ম্যানুয়াল নিরাপত্তা ব্লক');

        LoginSecurityLog::blockIp($ip, $reason, auth()->id());

        if ($this->accessService) {
            $this->accessService->log('block_ip', "আইপি অ্যাড্রেস '{$ip}' ম্যানুয়ালি ব্লক করা হয়েছে");
        }

        return back()->with('success', "আইপি '{$ip}' সফলভাবে ব্লক করা হয়েছে!");
    }

    /**
     * Clean all expired temporary locks.
     */
    public function cleanExpired(Request $request): RedirectResponse
    {
        LoginSecurityLog::where('is_blocked', false)
            ->where('locked_until', '<=', Carbon::now())
            ->delete();

        return back()->with('success', 'সমস্ত মেয়াদোত্তীর্ণ সাময়িক লক সফলভাবে ক্লিন করা হয়েছে!');
    }
}
