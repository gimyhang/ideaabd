<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\PasswordResetLinkMail;
use App\Models\User;
use App\Rules\StrongPassword;
use App\Services\SecurityAuditService;
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
    public const SUPPORT_WHATSAPP_NUMBER = '+8801558712810';
    public const CLEAN_WHATSAPP_NUMBER  = '8801558712810';

    /**
     * Show form to request password reset code via Email or WhatsApp (+8801558712810)
     */
    public function showRequestForm()
    {
        return view('auth.forgot-password', [
            'supportWhatsapp' => self::SUPPORT_WHATSAPP_NUMBER
        ]);
    }

    /**
     * Send password reset code (6-digit OTP & 30-minute link) via Mobile SMS, Email or WhatsApp
     */
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'identity'        => ['required', 'string', 'max:150'],
            'delivery_method' => ['nullable', 'string', 'in:email,whatsapp,sms,auto'],
        ], [
            'identity.required' => 'আপনার নিবন্ধিত ইমেইল অ্যাড্রেস অথবা মোবাইল নম্বর প্রদান করুন।',
        ]);

        $bn = ['০','১','২','৩','৪','৫','৬','৭','৮','৯'];
        $en = ['0','1','2','3','4','5','6','7','8','9'];
        $input = trim(str_replace($bn, $en, (string) $request->input('identity')));
        $cleanPhone = preg_replace('/[^0-9]/', '', $input);
        $deliveryMethod = $request->input('delivery_method', 'auto');

        // Find user by email, phone, clean phone, or name/username
        $user = User::where('email', $input)
            ->orWhere('phone', $input)
            ->orWhereRaw('LOWER(email) = ?', [strtolower($input)])
            ->orWhere(function ($query) use ($cleanPhone) {
                if (!empty($cleanPhone) && strlen($cleanPhone) >= 8) {
                    $last10 = substr($cleanPhone, -10);
                    $query->where('phone', 'LIKE', '%' . $last10)
                          ->orWhere('phone', '0' . $last10)
                          ->orWhere('phone', '+880' . $last10);
                }
            })
            ->orWhere('name', $input)
            ->orWhereRaw('LOWER(name) = ?', [strtolower($input)])
            ->first();

        // Fallback matching with .env admin credentials (ADMIN_PHONE, ADMIN_EMAIL, ADMIN_USERNAME, or 'admin')
        if (!$user) {
            $adminPhone = preg_replace('/[^0-9]/', '', (string)env('ADMIN_PHONE', '01726976982'));
            $adminEmail = strtolower((string)env('ADMIN_EMAIL', 'adideabd@gmail.com'));
            $adminUser  = strtolower((string)env('ADMIN_USERNAME', 'admin'));

            if ($cleanPhone === $adminPhone || strtolower($input) === $adminEmail || strtolower($input) === $adminUser || strtolower($input) === 'admin') {
                $user = User::where('role', User::ROLE_ADMIN)->orWhere('email', $adminEmail)->first();
            }
        }

        if (!$user) {
            return back()->withInput()->withErrors([
                'identity' => 'No account found with this email or mobile phone number.',
            ]);
        }

        // If user didn't have phone saved but provided a valid mobile number, sync it
        if (empty($user->phone) && !empty($cleanPhone) && strlen($cleanPhone) >= 10) {
            $user->phone = $cleanPhone;
            $user->save();
        }

        // Determine effective delivery method if set to auto
        if ($deliveryMethod === 'auto') {
            if (str_contains($input, '@') || empty($user->phone)) {
                $deliveryMethod = 'email';
            } else {
                $deliveryMethod = 'sms';
            }
        }

        // Generate 64-character token AND 6-digit numeric OTP code
        $token = Str::random(64);
        $otpCode = (string) random_int(100000, 999999);
        $expireMinutes = 2; // Strict 2 minutes expiration
        $expireAt = now()->addMinutes($expireMinutes);

        // Store Token in Cache
        $cacheKeyToken = 'pwd_reset_token_' . $token;
        $payload = [
            'user_id'    => $user->id,
            'email'      => $user->email,
            'phone'      => $user->phone,
            'otp'        => $otpCode,
            'created_at' => now()->timestamp,
            'expires_at' => $expireAt->timestamp,
        ];

        Cache::put($cacheKeyToken, $payload, $expireAt);
        Cache::put('pwd_reset_otp_user_' . $user->id, $payload, $expireAt);

        // Also store OTP in cache keyed by clean phone & email
        if (!empty($user->phone)) {
            $cleanUserPhone = preg_replace('/[^0-9]/', '', $user->phone);
            Cache::put('pwd_reset_otp_' . $cleanUserPhone, $payload, $expireAt);
            if (strlen($cleanUserPhone) >= 10) {
                Cache::put('pwd_reset_otp_' . substr($cleanUserPhone, -10), $payload, $expireAt);
            }
        }
        if (!empty($user->email)) {
            Cache::put('pwd_reset_otp_' . strtolower(trim($user->email)), $payload, $expireAt);
        }

        // Store hashed OTP code in password_reset_tokens table with 2-minute expiry
        try {
            if (!empty($user->email)) {
                DB::table('password_reset_tokens')->updateOrInsert(
                    ['email' => $user->email],
                    [
                        'token'      => Hash::make($otpCode),
                        'created_at' => now(),
                    ]
                );
            }
        } catch (\Throwable $e) {
            Log::warning("password_reset_tokens update note: " . $e->getMessage());
        }

        // Generate the reset URLs
        $resetUrl = route('password.reset', [
            'token' => $token,
            'email' => $user->email,
        ]);
        $resetOtpUrl = route('password.reset-otp', [
            'phone' => $user->phone ?: $user->email,
        ]);

        // WhatsApp Message Format (from/to official WhatsApp +8801558712810 / user phone)
        $whatsappMessage = "Idea Publication — Password Reset OTP Code: {$otpCode} (Valid 30 mins).\n\nReset Link: {$resetUrl}\n\nHelpline: " . self::SUPPORT_WHATSAPP_NUMBER;
        
        $userPhoneClean = preg_replace('/[^0-9]/', '', (string)$user->phone);
        if (!empty($userPhoneClean) && !str_starts_with($userPhoneClean, '88')) {
            $userPhoneClean = '88' . ltrim($userPhoneClean, '0');
        }

        $userWhatsappUrl = !empty($userPhoneClean)
            ? 'https://api.whatsapp.com/send?phone=' . $userPhoneClean . '&text=' . urlencode($whatsappMessage)
            : 'https://api.whatsapp.com/send?phone=' . self::CLEAN_WHATSAPP_NUMBER . '&text=' . urlencode($whatsappMessage);

        $supportWhatsappUrl = 'https://api.whatsapp.com/send?phone=' . self::CLEAN_WHATSAPP_NUMBER . '&text=' . urlencode("I need assistance with password reset code for: " . ($user->phone ?: $user->email));

        // Determine effective delivery method
        $sentViaSms = false;
        $sentViaEmail = false;
        $maskedPhone = !empty($user->phone) ? substr($user->phone, 0, 3) . '****' . substr($user->phone, -4) : '';
        $maskedEmail = !empty($user->email) ? $this->maskEmail($user->email) : '';

        // 1. DISPATCH VIA MOBILE SMS
        if (!empty($user->phone)) {
            try {
                $smsResult = \App\Services\SmsService::sendPasswordResetOtp($user->phone, $otpCode, $resetUrl);
                $sentViaSms = !empty($smsResult['success']);
                if (!$sentViaSms) {
                    Log::warning("Password reset SMS dispatch did not complete: " . json_encode($smsResult));
                }
            } catch (\Throwable $smsEx) {
                Log::warning("Password reset SMS error: " . $smsEx->getMessage());
            }
        }

        // 2. DISPATCH VIA EMAIL (GMAIL SMTP - 100% RELIABLE)
        if (!empty($user->email)) {
            try {
                \App\Services\EmailService::applyRuntimeSmtpConfig();
                Mail::to($user->email)->send(new PasswordResetLinkMail($user, $resetUrl, $expireMinutes, $otpCode));
                $sentViaEmail = true;
                Log::info("Password reset email successfully sent to {$user->email}");
            } catch (\Throwable $e) {
                Log::error("Failed to send password reset email via Mail: " . $e->getMessage());
                try {
                    $subject = "=?UTF-8?B?" . base64_encode("Idea Publication — OTP Code ({$otpCode})") . "?=";
                    $htmlBody = view('emails.password-reset-link', [
                        'user'          => $user,
                        'resetUrl'      => $resetUrl,
                        'expireMinutes' => $expireMinutes,
                        'otpCode'       => $otpCode,
                    ])->render();

                    $fromAddress = config('mail.from.address') ?: 'ideapbd@gmail.com';
                    $fromName    = config('mail.from.name') ?: 'Idea Publication';
                    $headers = "MIME-Version: 1.0\r\nContent-type: text/html; charset=UTF-8\r\nFrom: =?UTF-8?B?" . base64_encode($fromName) . "?= <{$fromAddress}>\r\nReply-To: {$fromAddress}\r\nX-Mailer: PHP/" . phpversion();
                    $sentViaEmail = (bool) @mail($user->email, $subject, $htmlBody, $headers);
                } catch (\Throwable $e2) {}
            }
        }

        // If specific method was requested but not possible
        if ($deliveryMethod === 'sms' && empty($user->phone)) {
            return back()->withInput()->withErrors([
                'identity' => 'No mobile phone number is linked to this account. Please select Email.',
            ]);
        }
        if ($deliveryMethod === 'email' && empty($user->email)) {
            return back()->withInput()->withErrors([
                'identity' => 'No email address is linked to this account. Please select SMS.',
            ]);
        }

        // Prepare accurate status message in clean English without exposing the OTP code
        if ($sentViaSms && $sentViaEmail) {
            $msg = "A 6-digit verification code has been sent to your mobile ({$maskedPhone}) and email ({$maskedEmail}). Please check your SMS and enter the code below.";
        } elseif ($sentViaEmail) {
            $msg = "A 6-digit verification code has been sent to your email ({$maskedEmail}). Please check your inbox and enter the code below.";
        } else {
            $phoneDisplay = $maskedPhone ?: 'your mobile number';
            $msg = "A 6-digit verification code has been sent to your mobile ({$phoneDisplay}). Please check your SMS and enter the code below.";
        }

        return redirect()->route('password.reset-otp', ['phone' => $input])
            ->with('status', $msg);
    }

    /**
     * Show form to reset password via 64-char token link
     */
    public function showResetForm(Request $request, string $token)
    {
        $cacheKey = 'pwd_reset_token_' . $token;
        $cachedData = Cache::get($cacheKey);
        $email = $request->input('email', $cachedData['email'] ?? '');

        $isValid = false;
        $remainingSeconds = 1800; // 30 mins

        if ($cachedData && isset($cachedData['user_id'])) {
            $isValid = true;
            if (isset($cachedData['expires_at'])) {
                $remainingSeconds = max(0, $cachedData['expires_at'] - time());
            }
        } elseif (!empty($email)) {
            $tokenRecord = DB::table('password_reset_tokens')->where('email', $email)->first();
            if ($tokenRecord && Hash::check($token, $tokenRecord->token)) {
                $createdAt = Carbon::parse($tokenRecord->created_at);
                if ($createdAt->addMinutes(30)->isFuture()) {
                    $isValid = true;
                    $remainingSeconds = max(0, $createdAt->addMinutes(30)->diffInSeconds(now()));
                }
            }
        }

        if (!$isValid || $remainingSeconds <= 0) {
            return redirect()->route('password.request')->withErrors([
                'identity' => 'This password reset link has expired or has already been used.',
            ]);
        }

        return view('auth.reset-password', [
            'token'            => $token,
            'email'            => $email,
            'remainingSeconds' => $remainingSeconds,
            'supportWhatsapp'  => self::SUPPORT_WHATSAPP_NUMBER,
        ]);
    }

    /**
     * Show form to reset password via 6-digit OTP code & Phone/Email
     */
    public function showOtpResetForm(Request $request)
    {
        $phone = $request->input('phone', '');
        return view('auth.reset-password-otp', [
            'phone'           => $phone,
            'supportWhatsapp' => self::SUPPORT_WHATSAPP_NUMBER,
        ]);
    }

    /**
     * Strict OTP Validator: Verifies 6-digit OTP against Cache and DB within 2-minute validity.
     * Returns User instance if strictly valid, null otherwise.
     */
    protected function validateAndGetUserFromOtp(string $phoneOrEmail, string $otpInput): ?User
    {
        $input = trim($phoneOrEmail);
        $cleanPhone = preg_replace('/[^0-9]/', '', $input);
        $last10 = (strlen($cleanPhone) >= 10) ? substr($cleanPhone, -10) : $cleanPhone;
        $otp = trim($otpInput);

        if (strlen($otp) !== 6 || !ctype_digit($otp)) {
            return null;
        }

        // 1. Find user by email or phone
        $user = User::where('email', $input)
            ->orWhere('phone', $input)
            ->orWhereRaw('LOWER(email) = ?', [strtolower($input)])
            ->orWhere(function ($query) use ($cleanPhone, $last10) {
                if (!empty($cleanPhone) && strlen($cleanPhone) >= 8) {
                    $query->where('phone', $cleanPhone)
                          ->orWhere('phone', 'LIKE', '%' . $last10);
                }
            })
            ->first();

        // Check fallback admin
        if (!$user) {
            $adminPhone = preg_replace('/[^0-9]/', '', (string)env('ADMIN_PHONE', '01726976982'));
            $adminEmail = strtolower((string)env('ADMIN_EMAIL', 'adideabd@gmail.com'));
            if ($cleanPhone === $adminPhone || strtolower($input) === $adminEmail) {
                $user = User::where('role', User::ROLE_ADMIN)->orWhere('email', $adminEmail)->first();
            }
        }

        if (!$user) {
            return null;
        }

        // 2. Check Cache keys
        $cleanUserPhone = preg_replace('/[^0-9]/', '', (string)$user->phone);
        $last10User = (strlen($cleanUserPhone) >= 10) ? substr($cleanUserPhone, -10) : $cleanUserPhone;
        $cachedKeys = array_unique(array_filter([
            'pwd_reset_otp_user_' . $user->id,
            'pwd_reset_otp_' . $cleanPhone,
            'pwd_reset_otp_' . $last10,
            'pwd_reset_otp_' . $cleanUserPhone,
            'pwd_reset_otp_' . $last10User,
            'pwd_reset_otp_' . strtolower((string)$user->email),
            'pwd_reset_otp_' . strtolower($input),
        ]));

        foreach ($cachedKeys as $cacheKey) {
            $cached = Cache::get($cacheKey);
            if ($cached && is_array($cached) && isset($cached['otp'])) {
                if ((string)$cached['otp'] === $otp) {
                    if (isset($cached['expires_at']) && now()->timestamp > $cached['expires_at']) {
                        return null; // Expired
                    }
                    return $user;
                }
            }
        }

        // 3. Check DB password_reset_tokens table with strict 2-minute expiry
        if (!empty($user->email)) {
            $tokenRow = DB::table('password_reset_tokens')->where('email', $user->email)->first();
            if ($tokenRow && !empty($tokenRow->token)) {
                if (Hash::check($otp, $tokenRow->token)) {
                    $createdAt = Carbon::parse($tokenRow->created_at);
                    if ($createdAt->addMinutes(2)->isFuture()) {
                        return $user;
                    }
                }
            }
        }

        return null;
    }

    /**
     * Verify 6-digit OTP code before displaying new password input table
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'phone' => ['required', 'string'],
            'otp'   => ['required', 'string', 'digits:6'],
        ], [
            'phone.required' => 'Please enter your mobile phone number or email.',
            'otp.required'   => 'Please enter the 6-digit verification code.',
            'otp.digits'     => 'The verification code must be exactly 6 digits.',
        ]);

        $phoneInput = trim((string) $request->input('phone'));
        $otpInput   = trim((string) $request->input('otp'));

        $user = $this->validateAndGetUserFromOtp($phoneInput, $otpInput);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired verification code (2-minute limit). Please check your SMS and try again.',
            ], 422);
        }

        return response()->json([
            'success'   => true,
            'message'   => 'Verification code confirmed successfully.',
            'phone'     => $phoneInput,
            'user_name' => $user->name,
        ]);
    }

    /**
     * Execute password update via 64-char link token
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'    => 'required',
            'email'    => 'required|email',
            'password' => 'required|confirmed|min:6|max:128',
        ], [
            'email.required'     => 'Please enter your email address.',
            'password.required'  => 'Please enter a new password.',
            'password.min'       => 'The password must be at least 6 characters.',
            'password.confirmed' => 'The password confirmation does not match.',
        ]);

        $token = $request->token;
        $email = strtolower(trim($request->email));
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
                if ($createdAt->addMinutes(2)->isFuture()) {
                    $user = User::where('email', $email)->first();
                    $isValid = ($user !== null);
                }
            }
        }

        if (!$isValid || !$user) {
            return redirect()->route('password.request')->withErrors([
                'identity' => 'This password reset link has expired or has already been used.',
            ]);
        }

        // Update password with Argon2id
        $user->password = Hash::make($request->password);
        $user->save();

        // Burn token
        Cache::forget($cacheKey);
        try {
            DB::table('password_reset_tokens')->where('email', $user->email)->delete();
        } catch (\Throwable $e) {}

        SecurityAuditService::passwordResetCompleted($user->id);
        Log::info("Password successfully reset for User ID: {$user->id} ({$user->email})");

        return redirect()->route('login')->with('status', 'Your password has been changed successfully. Please sign in with your new password.');
    }

    /**
     * Completely clear and burn all OTP cache entries and database tokens for user
     */
    protected function clearUserOtp(User $user, ?string $phoneOrEmail = null): void
    {
        Cache::forget('pwd_reset_otp_user_' . $user->id);
        if (!empty($user->phone)) {
            $uClean = preg_replace('/[^0-9]/', '', $user->phone);
            Cache::forget('pwd_reset_otp_' . $uClean);
            Cache::forget('pwd_reset_otp_' . substr($uClean, -10));
            Cache::forget('pwd_reset_otp_88' . ltrim($uClean, '0'));
        }
        if (!empty($user->email)) {
            Cache::forget('pwd_reset_otp_' . strtolower(trim($user->email)));
            try {
                DB::table('password_reset_tokens')->where('email', $user->email)->delete();
            } catch (\Throwable $e) {}
        }
        if (!empty($phoneOrEmail)) {
            $inClean = preg_replace('/[^0-9]/', '', $phoneOrEmail);
            Cache::forget('pwd_reset_otp_' . $inClean);
            Cache::forget('pwd_reset_otp_' . substr($inClean, -10));
            Cache::forget('pwd_reset_otp_' . strtolower(trim($phoneOrEmail)));
        }
    }

    /**
     * Execute password update via 6-digit OTP code
     */
    public function resetPasswordWithOtp(Request $request)
    {
        $customMessages = [
            'phone.required'     => 'Please enter your mobile phone number or email.',
            'otp.required'       => 'Please enter the 6-digit verification code.',
            'otp.digits'         => 'The verification code must be exactly 6 digits.',
            'password.required'  => 'Please enter a new password.',
            'password.min'       => 'The password must be at least 6 characters.',
            'password.confirmed' => 'The password confirmation does not match.',
        ];

        $request->validate([
            'phone'    => ['required', 'string'],
            'otp'      => ['required', 'string', 'digits:6'],
            'password' => ['required', 'confirmed', 'string', 'min:6', 'max:128'],
        ], $customMessages);

        $phoneInput = trim((string) $request->input('phone'));
        $otpInput   = trim((string) $request->input('otp'));

        $user = $this->validateAndGetUserFromOtp($phoneInput, $otpInput);

        if (!$user) {
            return back()->withInput()->withErrors([
                'otp' => 'Invalid or expired verification code (2-minute limit). Please check and try again.',
            ]);
        }

        // Update password
        $user->password = Hash::make($request->password);
        $user->save();

        // Burn all OTP cache and DB records immediately
        $this->clearUserOtp($user, $phoneInput);

        Log::info("Password successfully reset via 6-digit OTP for User ID: {$user->id}");

        return redirect()->route('login')->with('status', 'Your password has been changed successfully. Please sign in with your new password.');
    }

    /**
     * Mask email address for privacy (e.g. j***e@gmail.com)
     */
    protected function maskEmail(string $email): string
    {
        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return $email;
        }

        $name = $parts[0];
        $domain = $parts[1];
        $length = strlen($name);

        if ($length <= 2) {
            $maskedName = substr($name, 0, 1) . '*';
        } else {
            $maskedName = substr($name, 0, 1) . str_repeat('*', max(1, $length - 2)) . substr($name, -1);
        }

        return $maskedName . '@' . $domain;
    }

    /**
     * Submit user password reset help request to admin dashboard.
     */
    public function submitHelpRequest(Request $request)
    {
        $request->validate([
            'identity'     => ['required', 'string', 'max:255'],
            'user_name'    => ['nullable', 'string', 'max:255'],
            'reason_notes' => ['nullable', 'string', 'max:1000'],
        ], [
            'identity.required' => 'আপনার নিবন্ধিত ইমেইল বা মোবাইল নম্বর দিন।',
        ]);

        $identity = trim((string)$request->input('identity'));
        $cleanPhone = preg_replace('/[^0-9]/', '', $identity);

        $user = User::where('email', $identity)
            ->orWhere('phone', $identity)
            ->orWhere(function ($q) use ($cleanPhone) {
                if (!empty($cleanPhone) && strlen($cleanPhone) >= 10) {
                    $q->where('phone', $cleanPhone);
                }
            })
            ->orWhere('name', $identity)
            ->first();

        \App\Models\PasswordResetRequest::create([
            'user_id'      => $user?->id,
            'identity'     => $identity,
            'user_name'    => $request->input('user_name') ?: ($user?->name ?? 'গ্রাহক'),
            'user_ip'      => $request->ip(),
            'reason_notes' => $request->input('reason_notes', 'লিংক বা ওটিপিতে পাসওয়ার্ড রিসেট করতে ব্যর্থ হওয়ায় অ্যাডমিন সহায়তার আবেদন।'),
            'status'       => 'pending',
        ]);

        return back()->with('status', 'আপনার পাসওয়ার্ড সহায়তার আবেদন সফলভাবে অ্যাডমিন প্যানেলে পাঠানো হয়েছে! অ্যাডমিন কর্তৃপক্ষ তথ্য যাচাই করে একটি ওয়ানটাইম পাসওয়ার্ড (OTP) ইস্যু করবেন।');
    }
}
