<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Show the login form with anti-caching headers for mobile/PWA stability.
     */
    public function showLoginForm()
    {
        return response()
            ->view('auth.login')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
    }

    public function login(Request $request)
    {
        $loginInput = trim((string) ($request->input('email') ?? $request->input('username') ?? $request->input('login') ?? ''));
        $password   = (string) $request->input('password', '');

        if ($loginInput === '' || $password === '') {
            throw ValidationException::withMessages([
                'email' => 'ইমেইল/ইউজারনেম এবং পাসওয়ার্ড দিন।',
            ]);
        }

        try {
            // Find all potential user candidates matching email, name/username, or phone
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
                // Check if registration is pending approval for author/seller/publisher
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

                // Check if account is active
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
