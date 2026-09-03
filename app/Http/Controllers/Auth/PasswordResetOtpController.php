<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class PasswordResetOtpController extends Controller
{
    /**
     * Show form to request mobile verification code
     */
    public function showRequestForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Send 6-digit OTP code to registered mobile number
     */
    public function sendOtp(Request $request)
    {
        $request->validate([
            'phone' => ['required', 'string', 'max:50'],
        ], [
            'phone.required' => 'আপনার নিবন্ধিত মোবাইল নম্বর বা ইমেইল লিখুন।',
        ]);

        $input = trim((string) $request->input('phone'));
        $cleanPhone = preg_replace('/[^0-9]/', '', $input);

        // Find user by phone, email, or username
        $user = User::where('phone', $input)
            ->orWhere('phone', $cleanPhone)
            ->orWhere('email', $input)
            ->orWhere('name', $input)
            ->first();

        if (!$user) {
            return back()->withInput()->withErrors([
                'phone' => 'প্রদত্ত তথ্য অনুযায়ী কোনো নিবন্ধিত ব্যবহারকারী পাওয়া যায়নি।',
            ]);
        }

        if (empty($user->phone)) {
            return back()->withInput()->withErrors([
                'phone' => 'এই অ্যাকাউন্টে কোনো মোবাইল নম্বর যুক্ত নেই। সাপোর্টে যোগাযোগ করুন।',
            ]);
        }

        // Generate 6-digit OTP
        $otpCode = (string) random_int(100000, 999999);
        $cacheKey = 'pwd_reset_otp_' . preg_replace('/[^0-9]/', '', $user->phone);

        // Store OTP in cache for 30 minutes
        Cache::put($cacheKey, [
            'user_id' => $user->id,
            'otp'     => $otpCode,
            'phone'   => $user->phone,
        ], now()->addMinutes(30));

        // Also store in password_reset_tokens table if available
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
            Log::info("Note: password_reset_tokens update skipped: " . $e->getMessage());
        }

        // Send SMS via SmsService
        $resetUrl = route('password.reset-otp', ['phone' => $user->phone]);
        \App\Services\SmsService::sendPasswordResetOtp($user->phone, $otpCode, $resetUrl);

        return redirect()->route('password.reset-otp', ['phone' => $user->phone])
            ->with('status', "আপনার মোবাইল নম্বর ({$user->phone})-এ ৬ ডিজিটের ভেরিফিকেশন কোড পাঠানো হয়েছে (মেয়াদ ৩০ মিনিট)। কোডটি দিয়ে নতুন পাসওয়ার্ড সেট করুন।")
            ->with('otp_code', $otpCode);
    }

    /**
     * Show form to verify OTP and enter new password
     */
    public function showResetForm(Request $request)
    {
        $phone = $request->input('phone', '');
        return view('auth.reset-password-otp', compact('phone'));
    }

    /**
     * Verify OTP and reset user password
     */
    public function resetPassword(Request $request)
    {
        $customMessages = [
            'phone.required'     => 'মোবাইল নম্বর প্রদান করুন।',
            'otp.required'       => '৬ ডিজিটের ভেরিফিকেশন কোড প্রদান করুন।',
            'otp.digits'         => 'ভেরিফিকেশন কোডটি অবশ্যই ৬ ডিজিটের হতে হবে।',
            'password.required'  => 'নতুন পাসওয়ার্ড প্রদান করুন।',
            'password.min'       => 'পাসওয়ার্ড সর্বনিম্ন ৮ অক্ষরের হতে হবে।',
            'password.max'       => 'পাসওয়ার্ড সর্বোচ্চ ২৫ অক্ষরের মধ্যে হতে হবে।',
            'password.regex'     => 'পাসওয়ার্ডে অন্তত একটি স্পেশাল ক্যারেক্টার (যেমন: @, #, $, %, !, *, ?, &) ব্যবহার করতে হবে।',
            'password.confirmed' => 'পাসওয়ার্ড এবং পাসওয়ার্ড নিশ্চিতকরণ মেলেনি।',
        ];

        $request->validate([
            'phone'    => ['required', 'string'],
            'otp'      => ['required', 'string', 'digits:6'],
            'password' => [
                'required',
                'confirmed',
                'string',
                'min:8',
                'max:25',
                'regex:/[!@#$%^&*(),.?":{}|<>_\-+=]/',
            ],
        ], $customMessages);

        $phoneInput = trim((string) $request->input('phone'));
        $cleanPhone = preg_replace('/[^0-9]/', '', $phoneInput);
        $otpInput   = trim((string) $request->input('otp'));

        $cacheKey = 'pwd_reset_otp_' . $cleanPhone;
        $cachedData = Cache::get($cacheKey);

        $user = null;
        $isValidOtp = false;

        if ($cachedData && isset($cachedData['otp']) && $cachedData['otp'] === $otpInput) {
            $user = User::find($cachedData['user_id']);
            $isValidOtp = true;
        } else {
            // Check matching user by phone
            $user = User::where('phone', $phoneInput)->orWhere('phone', $cleanPhone)->first();
            if ($user && $user->email) {
                $tokenRow = DB::table('password_reset_tokens')->where('email', $user->email)->first();
                if ($tokenRow && Hash::check($otpInput, $tokenRow->token)) {
                    // check 1 minute expiry
                    if (\Carbon\Carbon::parse($tokenRow->created_at)->addMinutes(1)->isFuture()) {
                        $isValidOtp = true;
                    }
                }
            }
        }

        if (!$isValidOtp || !$user) {
            return back()->withInput()->withErrors([
                'otp' => 'ভেরিফিকেশন কোডটি সঠিক নয় বা এর মেয়াদ শেষ হয়ে গেছে। অনুগ্রহ করে পুনরায় কোড পাঠিয়ে চেষ্টা করুন।',
            ]);
        }

        // Update user password
        $user->password = Hash::make($request->password);
        $user->save();

        // Invalidate OTP
        Cache::forget($cacheKey);
        try {
            DB::table('password_reset_tokens')->where('email', $user->email)->delete();
        } catch (\Throwable $e) {}

        Log::info("Password successfully reset via mobile OTP for User ID: {$user->id} ({$user->phone})");

        return redirect()->route('login')
            ->with('success', 'আপনার পাসওয়ার্ড সফলভাবে পরিবর্তিত হয়েছে! এখন আপনার মোবাইল নম্বর ও নতুন পাসওয়ার্ড দিয়ে লগইন করুন।');
    }
}
