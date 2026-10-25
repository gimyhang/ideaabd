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
            'phone.required' => 'Enter your phone or email.',
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
                'phone' => 'No account found.',
            ]);
        }

        if (empty($user->phone)) {
            return back()->withInput()->withErrors([
                'phone' => 'No phone number on this account.',
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
        $smsResult = \App\Services\SmsService::sendPasswordResetOtp($user->phone, $otpCode, $resetUrl);
        if (empty($smsResult['success']) && !empty($smsResult['code'])) {
            Cache::forget($cacheKey);

            return back()->withInput()->withErrors(['phone' => $smsResult['message']]);
        }

        return redirect()->route('password.reset-otp', ['phone' => $user->phone])
            ->with('status', "A 6-digit code was sent to {$user->phone}.")
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
            'phone.required'     => 'Phone is required.',
            'otp.required'       => 'Enter the 6-digit code.',
            'otp.digits'         => 'The code must be 6 digits.',
            'password.required'  => 'New password is required.',
            'password.min'       => 'Minimum 8 characters.',
            'password.max'       => 'Maximum 25 characters.',
            'password.regex'     => 'Add a special character.',
            'password.confirmed' => 'Passwords do not match.',
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
                'otp' => 'Wrong or expired code.',
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
            ->with('success', 'Password changed. Sign in now.');
    }
}
