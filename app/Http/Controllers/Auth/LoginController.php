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
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        try {
            if (Auth::attempt($credentials, $request->boolean('remember'))) {
                $request->session()->regenerate();

                $user = Auth::user();

                // Redirect based on role
                if ($user->isAdmin()) {
                    return redirect()->intended(route('admin.dashboard'));
                }
                if ($user->isSeller() || $user->isSubAdmin()) {
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
            'email' => 'ইমেইল বা পাসওয়ার্ড সঠিক নয়।',
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
