<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Show the login form with anti-caching headers and dynamic bot security challenge.
     */
    public function showLoginForm()
    {
        // Generate new dynamic Human Bot Challenge
        $num1 = random_int(2, 9);
        $num2 = random_int(1, 8);
        Session::put('login_bot_challenge', [
            'num1'   => $num1,
            'num2'   => $num2,
            'answer' => $num1 + $num2,
            'time'   => microtime(true),
        ]);

        return response()
            ->view('auth.login', [
                'botNum1' => $num1,
                'botNum2' => $num2,
            ])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
    }

    /**
     * AJAX endpoint to regenerate human bot security challenge numbers
     */
    public function refreshBotChallenge(Request $request)
    {
        $num1 = random_int(2, 9);
        $num2 = random_int(1, 8);
        Session::put('login_bot_challenge', [
            'num1'   => $num1,
            'num2'   => $num2,
            'answer' => $num1 + $num2,
            'time'   => microtime(true),
        ]);

        return response()->json([
            'success' => true,
            'num1'    => $num1,
            'num2'    => $num2,
            'equation'=> "{$num1} + {$num2} = ?",
        ]);
    }

    public function login(Request $request)
    {
        // 1. Invisible Honeypot Bot Check
        if ($request->filled('website_url_hp') || $request->filled('b_check_field')) {
            throw ValidationException::withMessages([
                'email' => 'স্বয়ংক্রিয় রোবট কার্যকলাপ সনাক্ত হয়েছে। অনুগ্রহ করে সাধারণ ব্রাউজার ব্যবহার করুন।',
            ]);
        }

        $loginInput = trim((string) ($request->input('email') ?? $request->input('username') ?? $request->input('login') ?? ''));
        $password   = (string) $request->input('password', '');

        if ($loginInput === '' || $password === '') {
            throw ValidationException::withMessages([
                'email' => 'ইমেইল/ইউজারনেম এবং পাসওয়ার্ড দিন।',
            ]);
        }

        // 2. Brute-Force Rate Limiting (Max 5 attempts per 60 seconds)
        $throttleKey = 'login_attempt:' . sha1($request->ip() . '|' . strtolower($loginInput));
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'email' => "খুব বেশি ভুল লগইন চেষ্টা করা হয়েছে! আপনার অ্যাকাউন্টের সুরক্ষায় লগইন সাময়িক লক করা হয়েছে। অনুগ্রহ করে {$seconds} সেকেন্ড পর আবার চেষ্টা করুন।",
            ]);
        }

        // 3. Human Bot Math Verification Check (if provided or after failed attempts)
        $attempts = RateLimiter::attempts($throttleKey);
        if ($attempts >= 2 || $request->filled('bot_answer')) {
            $challenge = Session::get('login_bot_challenge');
            $userAns = trim((string) $request->input('bot_answer'));
            if (!$challenge || (int)$userAns !== (int)($challenge['answer'] ?? -999)) {
                RateLimiter::hit($throttleKey, 60);
                throw ValidationException::withMessages([
                    'bot_answer' => 'রোবট সুরক্ষা যাচাইকরণ (ক্যাপচা) উত্তর সঠিক হয়নি। পুনরায় চেষ্টা করুন।',
                ]);
            }
        }

        try {
            // Find user candidates matching email, name/username, or phone
            $candidates = \App\Models\User::where('email', $loginInput)
                ->orWhere('name', $loginInput)
                ->orWhere('phone', $loginInput)
                ->orWhereRaw('LOWER(name) = ?', [strtolower($loginInput)])
                ->orWhereRaw('LOWER(email) = ?', [strtolower($loginInput)])
                ->orWhereRaw('email LIKE ?', [$loginInput . '@%'])
                ->get();

            $matchedUser = null;
            foreach ($candidates as $candidate) {
                if (Hash::check($password, $candidate->password)) {
                    $matchedUser = $candidate;
                    break;
                }
            }

            if ($matchedUser) {
                // Clear Rate Limiter on successful match
                RateLimiter::clear($throttleKey);
                Session::forget('login_bot_challenge');

                // Check registration approval for vendor/author/seller/publisher
                if (in_array($matchedUser->role, ['author', 'seller', 'publisher'], true)) {
                    if ($matchedUser->reg_status === 'pending' || !$matchedUser->is_active) {
                        throw ValidationException::withMessages([
                            'email' => 'আপনার অ্যাকাউন্টটি এখনও অ্যাডমিন কর্তৃক অনুমোদিত হয়নি। অনুমোদন সম্পন্ন হলে আপনার ইমেইলে নোটিফিকেশন পৌঁছে যাবে এবং আপনি লগইন করতে পারবেন।',
                        ]);
                    }
                    if ($matchedUser->reg_status === 'rejected') {
                        throw ValidationException::withMessages([
                            'email' => 'আপনার রেজিস্ট্রেশন অ্যাকাউন্টটির আবেদন প্রত্যাখ্যাত বা বাতিল করা হয়েছে।' . ($matchedUser->rejection_reason ? ' কারণ: ' . $matchedUser->rejection_reason : ''),
                        ]);
                    }
                }

                // Check active status
                if (isset($matchedUser->is_active) && ! $matchedUser->is_active) {
                    throw ValidationException::withMessages([
                        'email' => 'আপনার অ্যাকাউন্টটি নিষ্ক্রিয় করা আছে। কর্তৃপক্ষের সাথে যোগাযোগ করুন।',
                    ]);
                }

                Auth::login($matchedUser, $request->boolean('remember'));
                $request->session()->regenerate();

                // Redirect based on role
                if ($matchedUser->isAdmin()) {
                    return redirect()->intended(route('admin.dashboard'));
                }
                if ($matchedUser->isSeller() || $matchedUser->isSubAdmin()) {
                    return redirect()->intended(route('subadmin.bills.index'));
                }
                if ($matchedUser->isPublisher()) {
                    return redirect()->intended(route('publisher.dashboard'));
                }
                if ($matchedUser->isAuthor()) {
                    return redirect()->intended(route('author.dashboard'));
                }
                if ($matchedUser->isBuyer()) {
                    return redirect()->intended(route('my-account'));
                }

                return redirect()->intended(route('home'));
            }
        } catch (\Illuminate\Database\QueryException $e) {
            throw ValidationException::withMessages([
                'email' => 'সিস্টেম ডাটাবেজ অফলাইনে আছে বা কানেক্ট হতে পারছে না। অনুগ্রহ করে সার্ভার চেক করুন।',
            ]);
        }

        // Record failed attempt
        RateLimiter::hit($throttleKey, 60);

        throw ValidationException::withMessages([
            'email' => 'ইমেইল/ইউজারনেম বা পাসওয়ার্ড সঠিক নয়।',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
