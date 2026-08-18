<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\PasswordResetLinkMail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    /**
     * Show form to request one-time password reset link
     */
    public function showRequestForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Send 3-minute one-time password reset link to user's registered email
     */
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'identity' => ['required', 'string', 'max:150'],
        ], [
            'identity.required' => 'আপনার নিবন্ধিত ইমেইল অ্যাড্রেস অথবা মোবাইল নম্বর প্রদান করুন।',
        ]);

        $input = trim((string) $request->input('identity'));
        $cleanPhone = preg_replace('/[^0-9]/', '', $input);

        // Find user by email, phone, clean phone, or name
        $user = User::where('email', $input)
            ->orWhere('phone', $input)
            ->orWhere(function ($query) use ($cleanPhone) {
                if (!empty($cleanPhone) && strlen($cleanPhone) >= 10) {
                    $query->where('phone', $cleanPhone);
                }
            })
            ->orWhere('name', $input)
            ->first();

        if (!$user) {
            return back()->withInput()->withErrors([
                'identity' => 'প্রদত্ত তথ্য অনুযায়ী কোনো নিবন্ধিত ব্যবহারকারী পাওয়া যায়নি। সঠিক ইমেইল বা মোবাইল নম্বর দিন।',
            ]);
        }

        if (empty($user->email)) {
            return back()->withInput()->withErrors([
                'identity' => 'এই অ্যাকাউন্টে কোনো ইমেইল ঠিকানা যুক্ত নেই। পাসওয়ার্ড পুনরুদ্ধারের জন্য সরাসরি সাপোর্টে (support@ideaabd.com) যোগাযোগ করুন।',
            ]);
        }

        // Generate 64-character cryptographically secure token
        $token = Str::random(64);
        $expireMinutes = 3;
        $expireAt = now()->addMinutes($expireMinutes);

        // Store in Cache with exact 3-minute TTL
        $cacheKey = 'pwd_reset_token_' . $token;
        Cache::put($cacheKey, [
            'user_id'    => $user->id,
            'email'      => $user->email,
            'created_at' => now()->timestamp,
            'expires_at' => $expireAt->timestamp,
        ], $expireAt);

        // Also store token in password_reset_tokens table
        try {
            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $user->email],
                [
                    'token'      => Hash::make($token),
                    'created_at' => now(),
                ]
            );
        } catch (\Throwable $e) {
            Log::warning("password_reset_tokens table update note: " . $e->getMessage());
        }

        // Generate the reset URL
        $resetUrl = route('password.reset', [
            'token' => $token,
            'email' => $user->email,
        ]);

        // Send Email
        try {
            Mail::to($user->email)->send(new PasswordResetLinkMail($user, $resetUrl, $expireMinutes));
            Log::info("Password reset one-time email link sent to {$user->email} (expires in 3 min): {$resetUrl}");
        } catch (\Throwable $e) {
            Log::error("Failed to send password reset email to {$user->email}: " . $e->getMessage());
            Log::info("Generated fallback password reset URL: {$resetUrl}");
        }

        // Mask email for user privacy (e.g. j***e@gmail.com)
        $maskedEmail = $this->maskEmail($user->email);

        return back()->with('status', "আপনার নিবন্ধিত ইমেইল ({$maskedEmail})-এ একটি ওয়ান-টাইম পাসওয়ার্ড রিসেট লিংক পাঠানো হয়েছে। লিংকটির মেয়াদ ৩ মিনিট। অনুগ্রহ করে আপনার ইনবক্স অথবা স্প্যাম (Spam) ফোল্ডার চেক করুন।");
    }

    /**
     * Show form to reset password if token is valid and within 3 minutes
     */
    public function showResetForm(Request $request, string $token)
    {
        $cacheKey = 'pwd_reset_token_' . $token;
        $cachedData = Cache::get($cacheKey);
        $email = $request->input('email', $cachedData['email'] ?? '');

        $isValid = false;
        $remainingSeconds = 180;

        if ($cachedData && isset($cachedData['user_id'])) {
            $isValid = true;
            if (isset($cachedData['expires_at'])) {
                $remainingSeconds = max(0, $cachedData['expires_at'] - time());
            }
        } elseif (!empty($email)) {
            $tokenRecord = DB::table('password_reset_tokens')->where('email', $email)->first();
            if ($tokenRecord && Hash::check($token, $tokenRecord->token)) {
                $createdAt = Carbon::parse($tokenRecord->created_at);
                if ($createdAt->addMinutes(3)->isFuture()) {
                    $isValid = true;
                    $remainingSeconds = max(0, $createdAt->addMinutes(3)->diffInSeconds(now()));
                }
            }
        }

        if (!$isValid || $remainingSeconds <= 0) {
            return redirect()->route('password.request')->withErrors([
                'identity' => 'পাসওয়ার্ড রিসেট লিংকের মেয়াদ (৩ মিনিট) শেষ হয়ে গেছে অথবা লিংকটি ইতিমধ্যে একবার ব্যবহার করা হয়েছে। অনুগ্রহ করে আবার নতুন লিংকের জন্য রিকোয়েস্ট করুন।',
            ]);
        }

        return view('auth.reset-password', [
            'token'            => $token,
            'email'            => $email,
            'remainingSeconds' => $remainingSeconds,
        ]);
    }

    /**
     * Execute password update and invalidate one-time token immediately
     */
    public function resetPassword(Request $request)
    {
        $customMessages = [
            'token.required'     => 'অবৈধ বা অনুপস্থিত সিকিউরিটি টোকেন।',
            'email.required'     => 'ইমেইল অ্যাড্রেস প্রয়োজন।',
            'email.email'        => 'সঠিক ইমেইল ফরম্যাট প্রদান করুন।',
            'password.required'  => 'নতুন পাসওয়ার্ড প্রদান করুন।',
            'password.min'       => 'পাসওয়ার্ড সর্বনিম্ন ৮ অক্ষরের হতে হবে।',
            'password.max'       => 'পাসওয়ার্ড সর্বোচ্চ ২৫ অক্ষরের মধ্যে হতে হবে।',
            'password.regex'     => 'পাসওয়ার্ডে অন্তত একটি স্পেশাল ক্যারেক্টার (যেমন: @, #, $, %, !, *, ?, &) থাকতে হবে।',
            'password.confirmed' => 'পাসওয়ার্ড এবং নিশ্চিতকরণ পাসওয়ার্ড মেলেনি।',
        ];

        $request->validate([
            'token'    => ['required', 'string'],
            'email'    => ['required', 'email'],
            'password' => [
                'required',
                'confirmed',
                'string',
                'min:8',
                'max:25',
                'regex:/[!@#$%^&*(),.?":{}|<>_\-+=]/',
            ],
        ], $customMessages);

        $token = (string) $request->input('token');
        $email = (string) $request->input('email');
        $cacheKey = 'pwd_reset_token_' . $token;
        $cachedData = Cache::get($cacheKey);

        $user = null;
        $isValid = false;

        if ($cachedData && isset($cachedData['user_id'])) {
            $user = User::find($cachedData['user_id']);
            $isValid = ($user !== null);
        } else {
            $tokenRecord = DB::table('password_reset_tokens')->where('email', $email)->first();
            if ($tokenRecord && Hash::check($token, $tokenRecord->token)) {
                $createdAt = Carbon::parse($tokenRecord->created_at);
                if ($createdAt->addMinutes(3)->isFuture()) {
                    $user = User::where('email', $email)->first();
                    $isValid = ($user !== null);
                }
            }
        }

        if (!$isValid || !$user) {
            return redirect()->route('password.request')->withErrors([
                'identity' => 'পাসওয়ার্ড রিসেট লিংকের মেয়াদ (৩ মিনিট) শেষ হয়ে গেছে অথবা এটি ইতিমধ্যে ব্যবহৃত হয়েছে। অনুগ্রহ করে আবার নতুন লিংক চেয়ে চেষ্টা করুন।',
            ]);
        }

        // Update password
        $user->password = Hash::make($request->password);
        $user->save();

        // Immediately burn/invalidate the token (One-Time use only)
        Cache::forget($cacheKey);
        try {
            DB::table('password_reset_tokens')->where('email', $user->email)->delete();
        } catch (\Throwable $e) {}

        Log::info("Password successfully reset via 3-min email one-time link for User ID: {$user->id} ({$user->email})");

        return redirect()->route('login')->with('status', 'আপনার পাসওয়ার্ড সফলভাবে পরিবর্তিত হয়েছে! এখন আপনার নতুন পাসওয়ার্ড দিয়ে লগইন করুন।');
    }

    /**
     * Mask email address for user privacy (e.g. r***t@example.com)
     */
    private function maskEmail(string $email): string
    {
        $parts = explode('@', $email);
        if (count($parts) < 2) return $email;

        $name = $parts[0];
        $domain = $parts[1];

        $len = strlen($name);
        if ($len <= 2) {
            $maskedName = substr($name, 0, 1) . '*';
        } else {
            $maskedName = substr($name, 0, 1) . str_repeat('*', min(4, $len - 2)) . substr($name, -1);
        }

        return $maskedName . '@' . $domain;
    }
}
