<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
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
                if (\Illuminate\Support\Facades\Hash::check($password, $candidate->password)) {
                    $matchedUser = $candidate;
                    break;
                }
            }

            if ($matchedUser) {
                // Check if account is active
                if (isset($matchedUser->is_active) && ! $matchedUser->is_active) {
                    throw ValidationException::withMessages([
                        'email' => 'আপনার অ্যাকাউন্টটি নিষ্ক্রিয় করা আছে। কর্তৃপক্ষের সাথে যোগাযোগ করুন।',
                    ]);
                }

                \Illuminate\Support\Facades\Auth::login($matchedUser, $request->boolean('remember'));
                $request->session()->regenerate();

                // Redirect based on role
                if ($matchedUser->isAdmin()) {
                    return redirect()->intended(route('admin.dashboard'));
                }
                if ($matchedUser->isSeller() || $matchedUser->isSubAdmin()) {
                    return redirect()->intended(route('subadmin.bills.index'));
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
