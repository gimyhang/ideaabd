<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use App\Models\LoginSecurityLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AdminProfileController extends Controller
{
    /**
     * Show the Admin Profile & Customization Hub.
     */
    public function index(): View
    {
        $user = auth()->user();

        // Recent login and security activities for this admin
        $securityLogs = LoginSecurityLog::where('last_username', $user->email)
            ->orWhere('last_username', $user->phone)
            ->orWhere('last_username', $user->name)
            ->latest()
            ->take(8)
            ->get();

        $activityLogs = class_exists(AdminActivityLog::class)
            ? AdminActivityLog::where('user_id', $user->id)->latest()->take(10)->get()
            : collect([]);

        $preferences = $user->reg_data['preferences'] ?? [
            'theme' => 'auto',
            'landing_page' => 'admin.dashboard',
            'notify_orders' => true,
            'notify_registrations' => true,
            'notify_tickets' => true,
            'two_factor_auth' => false,
        ];

        return view('admin.profile.index', compact('user', 'securityLogs', 'activityLogs', 'preferences'));
    }

    /**
     * Update Admin Personal Details & Avatar.
     */
    public function updateProfile(Request $request): RedirectResponse
    {
        $user = auth()->user();

        // Convert Bengali digits (০-৯) to English (0-9) in phone number
        if ($request->filled('phone')) {
            $bnDigits = ['০','১','২','৩','৪','৫','৬','৭','৮','৯'];
            $enDigits = ['0','1','2','3','4','5','6','7','8','9'];
            $cleanPhone = trim(str_replace($bnDigits, $enDigits, (string)$request->input('phone')));
            $request->merge(['phone' => $cleanPhone]);
        }

        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'email'       => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone'       => ['nullable', 'string', 'max:30', 'unique:users,phone,' . $user->id],
            'designation' => ['nullable', 'string', 'max:150'],
            'bio'         => ['nullable', 'string', 'max:1000'],
            'avatar'      => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,svg', 'max:3072'],
        ]);

        $regData = $user->reg_data ?? [];
        $regData['designation'] = $validated['designation'] ?? ($regData['designation'] ?? null);
        $regData['bio'] = $validated['bio'] ?? ($regData['bio'] ?? null);

        if ($request->hasFile('avatar')) {
            $path = \App\Services\ImageOptimizerService::convertAndStore($request->file('avatar'), 'avatars', 'public', 85, 600, 600);
            $user->avatar = $path;
        }

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->phone = $validated['phone'];
        $user->reg_data = $regData;
        $user->save();

        return back()->with('success', 'আপনার এডমিন প্রোফাইল সফলভাবে আপডেট করা হয়েছে।');
    }

    /**
     * Change Admin Security Password.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'string', 'confirmed', Password::min(6)],
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'আপনার পাসওয়ার্ড সফলভাবে পরিবর্তন করা হয়েছে।');
    }

    /**
     * Update Dashboard Customization & System Preferences.
     */
    public function updatePreferences(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $regData = $user->reg_data ?? [];

        $preferences = [
            'theme'                => $request->input('theme', 'auto'),
            'landing_page'         => $request->input('landing_page', 'admin.dashboard'),
            'sidebar_layout'       => $request->input('sidebar_layout', 'default'),
            'table_per_page'       => (int) $request->input('table_per_page', 20),
            'color_accent'         => $request->input('color_accent', 'blue'),
            'number_format'        => $request->input('number_format', 'bengali'),
            'sound_effects'        => $request->boolean('sound_effects'),
            'notify_orders'        => $request->boolean('notify_orders'),
            'notify_registrations' => $request->boolean('notify_registrations'),
            'notify_tickets'       => $request->boolean('notify_tickets'),
            'two_factor_auth'      => $request->boolean('two_factor_auth'),
            'telegram_chat_id'     => $request->string('telegram_chat_id')->trim()->value(),
            'whatsapp_alerts'      => $request->string('whatsapp_alerts')->trim()->value(),
            'ip_whitelist'         => $request->string('ip_whitelist')->trim()->value(),
        ];

        $regData['preferences'] = $preferences;
        $user->reg_data = $regData;
        $user->save();

        return redirect()->to(route('admin.profile') . '#preferences')
            ->with('success', 'ড্যাশবোর্ড প্রেফারেন্স ও কাস্টমাইজেশন সেটিংস সফলভাবে সংরক্ষণ করা হয়েছে।');
    }

    /**
     * Upload / Update Digital Signature & Official Seal.
     */
    public function updateSignature(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $request->validate([
            'signature' => ['required', 'image', 'mimes:png,webp,svg', 'max:2048'],
        ]);

        $file = $request->file('signature');
        $path = \App\Services\ImageOptimizerService::convertAndStore($file, 'signatures', 'public', 90, 800, 800);
        $regData = $user->reg_data ?? [];
        
        if (!empty($regData['signature']) && Storage::disk('public')->exists($regData['signature'])) {
            Storage::disk('public')->delete($regData['signature']);
        }

        $regData['signature'] = $path;
        $user->reg_data = $regData;
        $user->save();

        return redirect()->to(route('admin.profile') . '#signature')
            ->with('success', 'ডিজিটাল স্বাক্ষর ও সিল সফলভাবে আপলোড করা হয়েছে।');
    }

    /**
     * Remove Digital Signature.
     */
    public function removeSignature(): RedirectResponse
    {
        $user = auth()->user();
        $regData = $user->reg_data ?? [];

        if (!empty($regData['signature']) && Storage::disk('public')->exists($regData['signature'])) {
            Storage::disk('public')->delete($regData['signature']);
        }

        unset($regData['signature']);
        $user->reg_data = $regData;
        $user->save();

        return redirect()->to(route('admin.profile') . '#signature')
            ->with('success', 'ডিজিটাল স্বাক্ষর সফলভাবে মুছে ফেলা হয়েছে।');
    }

    /**
     * Remove Custom Avatar.
     */
    public function removeAvatar(): RedirectResponse
    {
        $user = auth()->user();
        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }
        $user->avatar = null;
        $user->save();

        return redirect()->to(route('admin.profile') . '#general')
            ->with('success', 'প্রোফাইল ছবি সফলভাবে মুছে ফেলা হয়েছে।');
    }

    /**
     * Terminate / Log out from other browser sessions.
     */
    public function logoutOtherDevices(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        Auth::logoutOtherDevices($request->password);

        return redirect()->to(route('admin.profile') . '#security')
            ->with('success', 'অন্যান্য সমস্ত ডিভাইস ও ব্রাউজার সেশন থেকে লগআউট সম্পন্ন হয়েছে।');
    }
}
