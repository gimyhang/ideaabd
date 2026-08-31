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
     * Send password reset code (6-digit OTP & 30-minute link) via Email or WhatsApp (+8801558712810)
     */
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'identity'        => ['required', 'string', 'max:150'],
            'delivery_method' => ['nullable', 'string', 'in:email,whatsapp,auto'],
        ], [
            'identity.required' => 'আপনার নিবন্ধিত ইমেইল অ্যাড্রেস অথবা মোবাইল নম্বর প্রদান করুন।',
        ]);

        $input = trim((string) $request->input('identity'));
        $cleanPhone = preg_replace('/[^0-9]/', '', $input);
        $deliveryMethod = $request->input('delivery_method', 'auto');

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

        // Determine effective delivery method
        if ($deliveryMethod === 'auto') {
            $deliveryMethod = (!empty($user->email) && str_contains($input, '@')) ? 'email' : 'whatsapp';
        }

        // Generate 64-character token AND 6-digit numeric OTP code
        $token = Str::random(64);
        $otpCode = (string) random_int(100000, 999999);
        $expireMinutes = 30; // 30 minutes expiration
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

        // Also store OTP in cache keyed by clean phone & email
        if (!empty($user->phone)) {
            $cleanUserPhone = preg_replace('/[^0-9]/', '', $user->phone);
            Cache::put('pwd_reset_otp_' . $cleanUserPhone, $payload, $expireAt);
        }
        if (!empty($user->email)) {
            Cache::put('pwd_reset_otp_' . strtolower(trim($user->email)), $payload, $expireAt);
        }

        // Store in password_reset_tokens table
        try {
            if (!empty($user->email)) {
                DB::table('password_reset_tokens')->updateOrInsert(
                    ['email' => $user->email],
                    [
                        'token'      => Hash::make($token),
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

        // WhatsApp Message Format (from/to official WhatsApp +8801558712810)
        $whatsappMessage = "আইডিয়া প্রকাশন — আপনার পাসওয়ার্ড রিসেট ভেরিফিকেশন কোড: {$otpCode} (মেয়াদ ৩০ মিনিট)।\n\nসরাসরি রিসেট লিংক: {$resetUrl}\n\nঅফিসিয়াল হেল্পলাইন: " . self::SUPPORT_WHATSAPP_NUMBER;
        
        $userPhoneClean = preg_replace('/[^0-9]/', '', (string)$user->phone);
        if (!empty($userPhoneClean) && !str_starts_with($userPhoneClean, '88')) {
            $userPhoneClean = '88' . ltrim($userPhoneClean, '0');
        }

        $userWhatsappUrl = !empty($userPhoneClean)
            ? 'https://wa.me/' . $userPhoneClean . '?text=' . urlencode($whatsappMessage)
            : null;

        $supportWhatsappUrl = 'https://wa.me/' . self::CLEAN_WHATSAPP_NUMBER . '?text=' . urlencode("আমি পাসওয়ার্ড রিসেটের কোড পেতে চাই। আমার আইডি: " . ($user->email ?: $user->phone));

        // 1. DELIVERY VIA EMAIL
        if ($deliveryMethod === 'email' && !empty($user->email)) {
            $mailSent = false;
            try {
                Mail::to($user->email)->send(new PasswordResetLinkMail($user, $resetUrl, $expireMinutes, $otpCode));
                $mailSent = true;
                Log::info("Password reset email sent to {$user->email} with OTP: {$otpCode}");
            } catch (\Throwable $e) {
                Log::error("Failed to send password reset email via Mail facade: " . $e->getMessage());
                // Fallback native mail
                try {
                    $subject = "=?UTF-8?B?" . base64_encode("আইডিয়া প্রকাশন — পাসওয়ার্ড রিসেট কোড ও লিংক ({$otpCode})") . "?=";
                    $htmlBody = view('emails.password-reset-link', [
                        'user'          => $user,
                        'resetUrl'      => $resetUrl,
                        'expireMinutes' => $expireMinutes,
                        'otpCode'       => $otpCode,
                    ])->render();

                    $fromAddress = config('mail.from.address') ?: 'noreply@ideaabd.com';
                    $fromName    = config('mail.from.name') ?: 'আইডিয়া প্রকাশন';
                    $headers = "MIME-Version: 1.0\r\nContent-type: text/html; charset=UTF-8\r\nFrom: =?UTF-8?B?" . base64_encode($fromName) . "?= <{$fromAddress}>\r\nReply-To: {$fromAddress}\r\nX-Mailer: PHP/" . phpversion();
                    $mailSent = @mail($user->email, $subject, $htmlBody, $headers);
                } catch (\Throwable $e2) {}
            }

            $maskedEmail = $this->maskEmail($user->email);
            return back()
                ->with('status', "আপনার নিবন্ধিত ইমেইল ({$maskedEmail})-এ ৬ ডিজিটের কোড ও রিসেট লিংক পাঠানো হয়েছে (মেয়াদ ৩০ মিনিট)।")
                ->with('otp_code', $otpCode)
                ->with('support_whatsapp_url', $supportWhatsappUrl)
                ->with('user_whatsapp_url', $userWhatsappUrl);
        }

        // 2. DELIVERY VIA WHATSAPP (+8801558712810)
        Log::info("Password reset WhatsApp OTP generated for {$user->name} ({$user->phone}/{$user->email}): {$otpCode}");

        return redirect()->route('password.reset-otp', ['phone' => $user->phone ?: $user->email])
            ->with('status', "আপনার পাসওয়ার্ড রিসেট কোড প্রস্তুত করা হয়েছে। হোয়াটসঅ্যাপ অথবা ইমেইলে কোড দিয়ে নতুন পাসওয়ার্ড সেট করুন।")
            ->with('otp_code', $otpCode)
            ->with('whatsapp_message', $whatsappMessage)
            ->with('user_whatsapp_url', $userWhatsappUrl)
            ->with('support_whatsapp_url', $supportWhatsappUrl);
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
                'identity' => 'পাসওয়ার্ড রিসেট লিংকের মেয়াদ শেষ হয়ে গেছে অথবা লিংকটি ইতিমধ্যে একবার ব্যবহার করা হয়েছে। অনুগ্রহ করে আবার নতুন লিংকের জন্য চেষ্টা করুন।',
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
     * Execute password update via 64-char link token
     */
    public function resetPassword(Request $request)
    {
        $customMessages = [
            'token.required'     => 'অবৈধ বা অনুপস্থিত সিকিউরিটি টোকেন।',
            'email.required'     => 'ইমেইল বা ইউজার আইডি প্রয়োজন।',
            'password.required'  => 'নতুন পাসওয়ার্ড প্রদান করুন।',
            'password.min'       => 'পাসওয়ার্ড সর্বনিম্ন ৬ অক্ষরের হতে হবে।',
            'password.confirmed' => 'পাসওয়ার্ড এবং নিশ্চিতকরণ পাসওয়ার্ড মেলেনি।',
        ];

        $request->validate([
            'token'    => ['required', 'string'],
            'email'    => ['required', 'string'],
            'password' => [
                'required',
                'confirmed',
                'string',
                'min:6',
                'max:50',
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
                if ($createdAt->addMinutes(30)->isFuture()) {
                    $user = User::where('email', $email)->first();
                    $isValid = ($user !== null);
                }
            }
        }

        if (!$isValid || !$user) {
            return redirect()->route('password.request')->withErrors([
                'identity' => 'পাসওয়ার্ড রিসেট লিংকের মেয়াদ শেষ হয়ে গেছে অথবা এটি ইতিমধ্যে ব্যবহৃত হয়েছে। অনুগ্রহ করে আবার নতুন কোড নিয়ে চেষ্টা করুন।',
            ]);
        }

        // Update password
        $user->password = Hash::make($request->password);
        $user->save();

        // Burn token
        Cache::forget($cacheKey);
        try {
            DB::table('password_reset_tokens')->where('email', $user->email)->delete();
        } catch (\Throwable $e) {}

        Log::info("Password successfully reset for User ID: {$user->id} ({$user->email})");

        return redirect()->route('login')->with('status', 'আপনার পাসওয়ার্ড সফলভাবে পরিবর্তিত হয়েছে! এখন আপনার নতুন পাসওয়ার্ড দিয়ে লগইন করুন।');
    }

    /**
     * Execute password update via 6-digit OTP code
     */
    public function resetPasswordWithOtp(Request $request)
    {
        $customMessages = [
            'phone.required'     => 'মোবাইল নম্বর বা ইমেইল প্রদান করুন।',
            'otp.required'       => '৬ ডিজিটের ভেরিফিকেশন কোড প্রদান করুন।',
            'otp.digits'         => 'ভেরিফিকেশন কোডটি অবশ্যই ৬ ডিজিটের হতে হবে।',
            'password.required'  => 'নতুন পাসওয়ার্ড প্রদান করুন।',
            'password.min'       => 'পাসওয়ার্ড সর্বনিম্ন ৬ অক্ষরের হতে হবে।',
            'password.confirmed' => 'পাসওয়ার্ড এবং পাসওয়ার্ড নিশ্চিতকরণ মেলেনি।',
        ];

        $request->validate([
            'phone'    => ['required', 'string'],
            'otp'      => ['required', 'string', 'digits:6'],
            'password' => [
                'required',
                'confirmed',
                'string',
                'min:6',
                'max:50',
            ],
        ], $customMessages);

        $phoneInput = trim((string) $request->input('phone'));
        $cleanPhone = preg_replace('/[^0-9]/', '', $phoneInput);
        $otpInput   = trim((string) $request->input('otp'));

        $user = null;
        $isValidOtp = false;

        // Check cache by clean phone
        $cachedData = Cache::get('pwd_reset_otp_' . $cleanPhone);
        if (!$cachedData && str_contains($phoneInput, '@')) {
            $cachedData = Cache::get('pwd_reset_otp_' . strtolower($phoneInput));
        }

        if ($cachedData && isset($cachedData['otp']) && $cachedData['otp'] === $otpInput) {
            $user = User::find($cachedData['user_id']);
            $isValidOtp = ($user !== null);
        } else {
            $user = User::where('phone', $phoneInput)
                ->orWhere('phone', $cleanPhone)
                ->orWhere('email', $phoneInput)
                ->first();

            if ($user && $user->email) {
                $tokenRow = DB::table('password_reset_tokens')->where('email', $user->email)->first();
                if ($tokenRow && Hash::check($otpInput, $tokenRow->token)) {
                    if (Carbon::parse($tokenRow->created_at)->addMinutes(30)->isFuture()) {
                        $isValidOtp = true;
                    }
                }
            }
        }

        if (!$isValidOtp || !$user) {
            return back()->withInput()->withErrors([
                'otp' => 'প্রদত্ত ৬ ডিজিটের কোডটি সঠিক নয় অথবা এর মেয়াদ শেষ হয়ে গেছে। অনুগ্রহ করে আবার চেষ্টা করুন।',
            ]);
        }

        // Update password
        $user->password = Hash::make($request->password);
        $user->save();

        // Clear cache
        Cache::forget('pwd_reset_otp_' . $cleanPhone);
        if ($user->email) {
            Cache::forget('pwd_reset_otp_' . strtolower($user->email));
            try {
                DB::table('password_reset_tokens')->where('email', $user->email)->delete();
            } catch (\Throwable $e) {}
        }

        Log::info("Password successfully reset via 6-digit OTP for User ID: {$user->id}");

        return redirect()->route('login')->with('status', 'আপনার পাসওয়ার্ড সফলভাবে পরিবর্তিত হয়েছে! এখন আপনার নতুন পাসওয়ার্ড দিয়ে লগইন করুন।');
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
