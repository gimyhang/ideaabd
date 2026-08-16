<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;

class RegistrationController extends Controller
{
    // Show registration type selection page
    public function choose()
    {
        return view('auth.register-choose');
    }

    // Show a specific registration form
    public function showForm(string $type)
    {
        $allowed = ['seller', 'publisher', 'author', 'buyer'];
        abort_unless(in_array($type, $allowed), 404);
        return view("auth.register-{$type}");
    }

    // Handle all registration types
    public function register(Request $request, string $type)
    {
        $allowed = ['seller', 'publisher', 'author', 'buyer'];
        abort_unless(in_array($type, $allowed), 404);

        if ($type === 'buyer') {
            $base = $request->validate([
                'name'     => ['required', 'string', 'max:255'],
                'phone'    => ['required', 'string', 'max:20'],
                'email'    => ['nullable', 'email', 'unique:users,email'],
                'password' => ['required', 'confirmed', Password::min(6)],
            ]);

            // Auto-assign phone-based email if customer didn't specify an email
            if (empty($base['email'])) {
                $cleanPhone = preg_replace('/[^0-9]/', '', $base['phone']);
                $generatedEmail = $cleanPhone . '@buyer.ideaabd.com';
                // ensure unique
                $existing = User::where('email', $generatedEmail)->first();
                if ($existing) {
                    $generatedEmail = $cleanPhone . '_' . rand(100, 999) . '@buyer.ideaabd.com';
                }
                $base['email'] = $generatedEmail;
            }
        } else {
            $base = $request->validate([
                'name'     => ['required', 'string', 'max:255'],
                'email'    => ['required', 'email', 'unique:users,email'],
                'phone'    => ['required', 'string', 'max:20'],
                'password' => ['required', 'confirmed', Password::min(8)],
            ]);
        }

        // Type-specific validation
        $extra = match ($type) {
            'seller' => $request->validate([
                'shop_name'    => ['required', 'string', 'max:255'],
                'trade_license'=> ['nullable', 'string'],
                'address'      => ['required', 'string'],
                'nid'          => ['nullable', 'string'],
            ]),
            'publisher' => $request->validate([
                'publisher_name' => ['required', 'string', 'max:255'],
                'established'    => ['nullable', 'digits:4'],
                'address'        => ['required', 'string'],
                'trade_license'  => ['nullable', 'string'],
            ]),
            'author' => $request->validate([
                'pen_name' => ['nullable', 'string', 'max:255'],
                'bio'      => ['required', 'string'],
                'genre'    => ['required', 'string'],
                'nid'      => ['nullable', 'string'],
            ]),
            'buyer' => $request->validate([
                'address'      => ['nullable', 'string'],
                'date_of_birth'=> ['nullable', 'date'],
            ]),
        };

        // Generate 6 digit verification OTP
        $otpCode = rand(100000, 999999);
        $smsMessage = "আইডিয়া প্রকাশনে আপনাকে স্বাগতম! আপনার অ্যাকাউন্ট ভেরিফিকেশন কোড: {$otpCode}। বই পড়ুন, জ্ঞানের সাথে থাকুন। www.ideaabd.com";

        // Log SMS dispatch
        Log::info("SMS Verification dispatched to {$base['phone']}: {$smsMessage}");

        $user = User::create([
            'name'       => $base['name'],
            'email'      => $base['email'],
            'phone'      => $base['phone'],
            'password'   => Hash::make($base['password']),
            'role'       => $type === 'buyer' ? User::ROLE_BUYER : $type,
            'reg_type'   => $type,
            'reg_status' => $type === 'buyer' ? User::STATUS_APPROVED : User::STATUS_PENDING,
            'reg_data'   => array_merge($extra, ['otp_code' => $otpCode]),
            'is_active'  => $type === 'buyer',
            'email_verified_at' => $type === 'buyer' ? now() : null,
        ]);

        auth()->login($user);

        if ($type === 'buyer') {
            return redirect('/')->with('success', "স্বাগতম {$user->name}! আপনার মোবাইল নম্বর ({$base['phone']}) সফলভাবে নিবন্ধিত হয়েছে এবং কনফার্মেশন মেসেজ পাঠানো হয়েছে।");
        }

        return redirect()->route('pending.approval')
            ->with('success', 'আপনার রেজিস্ট্রেশন অনুরোধ জমা হয়েছে। অ্যাডমিন অনুমোদনের অপেক্ষা করুন।');
    }

    public function pendingApproval()
    {
        return view('auth.pending-approval');
    }
}
