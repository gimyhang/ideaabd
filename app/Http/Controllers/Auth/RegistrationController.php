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

        if (in_array($type, ['buyer', 'author'])) {
            $base = $request->validate([
                'name'     => ['required', 'string', 'max:255'],
                'phone'    => ['required', 'string', 'max:20'],
                'email'    => ['nullable', 'email', 'unique:users,email'],
                'password' => ['required', 'confirmed', Password::min(6)],
            ]);

            // Auto-assign phone-based email if user didn't specify an email
            if (empty($base['email'])) {
                $cleanPhone = preg_replace('/[^0-9]/', '', $base['phone']);
                $domain = $type === 'author' ? 'author.ideaabd.com' : 'buyer.ideaabd.com';
                $generatedEmail = $cleanPhone . '@' . $domain;
                // ensure unique
                $existing = User::where('email', $generatedEmail)->first();
                if ($existing) {
                    $generatedEmail = $cleanPhone . '_' . rand(100, 999) . '@' . $domain;
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
                'bio'      => ['nullable', 'string'],
                'genre'    => ['nullable', 'string'],
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

        $isActive = in_array($type, ['buyer', 'author'], true);
        $regStatus = $isActive ? User::STATUS_APPROVED : User::STATUS_PENDING;

        $user = User::create([
            'name'       => $base['name'],
            'email'      => $base['email'],
            'phone'      => $base['phone'],
            'password'   => Hash::make($base['password']),
            'role'       => $type === 'buyer' ? User::ROLE_BUYER : $type,
            'reg_type'   => $type,
            'reg_status' => $regStatus,
            'reg_data'   => array_merge($extra, ['otp_code' => $otpCode]),
            'is_active'  => $isActive,
            'email_verified_at' => $isActive ? now() : null,
        ]);

        // Auto create/sync entry in authors table if type is author
        if ($type === 'author') {
            try {
                $authorName = !empty($extra['pen_name']) ? $extra['pen_name'] : $base['name'];
                $authorSlug = \Illuminate\Support\Str::slug($authorName) ?: 'author-' . $user->id;
                if (\Illuminate\Support\Facades\DB::table('authors')->where('slug', $authorSlug)->exists()) {
                    $authorSlug .= '-' . $user->id;
                }
                \Illuminate\Support\Facades\DB::table('authors')->insertOrIgnore([
                    'name'        => $authorName,
                    'slug'        => $authorSlug,
                    'bio'         => $extra['bio'] ?? null,
                    'phone'       => $base['phone'],
                    'email'       => $base['email'],
                    'is_active'   => true,
                    'is_verified' => false,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            } catch (\Throwable $e) {
                Log::warning("Could not auto-create directory author entry: " . $e->getMessage());
            }
        }

        auth()->login($user);

        if ($type === 'buyer') {
            return redirect('/')->with('success', "স্বাগতম {$user->name}! আপনার মোবাইল নম্বর ({$base['phone']}) সফলভাবে নিবন্ধিত হয়েছে।");
        }

        if ($type === 'author') {
            return redirect()->route('my-account')->with('success', "স্বাগতম লেখক {$user->name}! আপনার মোবাইল নম্বরটি ইউজারনেম হিসেবে সেট হয়েছে। আপনি এখন ব্লগ পোস্ট লিখতে ও ড্রাফট করতে পারেন।");
        }

        return redirect()->route('pending.approval')
            ->with('success', 'আপনার রেজিস্ট্রেশন অনুরোধ জমা হয়েছে। অ্যাডমিন অনুমোদনের অপেক্ষা করুন।');
    }

    public function pendingApproval()
    {
        return view('auth.pending-approval');
    }
}
