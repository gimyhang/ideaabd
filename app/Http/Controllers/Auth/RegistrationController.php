<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

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

        $customMessages = [
            'name.required'      => 'আপনার পুরো নাম লিখুন।',
            'phone.required'     => 'মোবাইল নম্বর প্রদান করা বাধ্যতামূলক।',
            'phone.unique'       => 'এই মোবাইল নম্বরটি দিয়ে ইতিমধ্যে একটি অ্যাকাউন্ট নিবন্ধিত রয়েছে।',
            'email.required'     => 'আপনার নিজস্ব সক্রিয় ইমেইল এড্রেস প্রদান করা বাধ্যতামূলক।',
            'email.email'        => 'সঠিক ফরম্যাটের ইমেইল এড্রেস দিন।',
            'email.unique'       => 'এই ইমেইলটি ইতিমধ্যে ব্যবহৃত হচ্ছে।',
            'password.required'  => 'পাসওয়ার্ড প্রদান করুন।',
            'password.min'       => 'পাসওয়ার্ড সর্বনিম্ন ৮ অক্ষরের হতে হবে।',
            'password.max'       => 'পাসওয়ার্ড সর্বোচ্চ ২৫ অক্ষরের মধ্যে হতে হবে।',
            'password.regex'     => 'পাসওয়ার্ডে অন্তত একটি স্পেশাল ক্যারেক্টার (যেমন: @, #, $, %, !, *, ?, &) ব্যবহার করতে হবে।',
            'password.confirmed' => 'পাসওয়ার্ড এবং পাসওয়ার্ড নিশ্চিতকরণ মেলেনি।',
        ];

        $passwordRules = [
            'required',
            'confirmed',
            'string',
            'min:8',
            'max:25',
            'regex:/[!@#$%^&*(),.?":{}|<>_\-+=]/',
        ];

        if ($type === 'buyer') {
            $base = $request->validate([
                'name'     => ['required', 'string', 'max:255'],
                'phone'    => ['required', 'string', 'max:20', 'unique:users,phone'],
                'email'    => ['nullable', 'email', 'max:255', 'unique:users,email'],
                'password' => $passwordRules,
            ], $customMessages);

            // If buyer didn't specify an email, auto-create a unique placeholder for DB uniqueness
            if (empty($base['email'])) {
                $cleanPhone = preg_replace('/[^0-9]/', '', $base['phone']);
                $generatedEmail = $cleanPhone . '@buyer.ideaabd.com';
                $existing = User::where('email', $generatedEmail)->first();
                if ($existing) {
                    $generatedEmail = $cleanPhone . '_' . rand(100, 999) . '@buyer.ideaabd.com';
                }
                $base['email'] = $generatedEmail;
            }
        } elseif ($type === 'author') {
            // Author MUST provide their own real email and mobile number (acts as username)
            $base = $request->validate([
                'name'     => ['required', 'string', 'max:255'],
                'phone'    => ['required', 'string', 'max:20', 'unique:users,phone'],
                'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
                'password' => $passwordRules,
            ], $customMessages);
        } else {
            // Seller / Publisher
            $base = $request->validate([
                'name'     => ['required', 'string', 'max:255'],
                'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
                'phone'    => ['required', 'string', 'max:20', 'unique:users,phone'],
                'password' => $passwordRules,
            ], $customMessages);
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

        // Only buyer is auto-approved immediately. Author, Seller, Publisher must await admin approval!
        $isActive = ($type === 'buyer');
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
                    'is_active'   => false, // Pending admin approval
                    'is_verified' => false,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            } catch (\Throwable $e) {
                Log::warning("Could not auto-create directory author entry: " . $e->getMessage());
            }
        }

        if ($type === 'buyer') {
            auth()->login($user);
            return redirect('/')->with('success', "আপনার রেজিস্ট্রেশন সফল হয়েছে! স্বাগতম {$user->name}। আপনার অ্যাকাউন্টটি সক্রিয় রয়েছে।");
        }

        // For author, seller, publisher: Do NOT login automatically. Redirect to pending approval notice.
        return redirect()->route('pending.approval')
            ->with('success', 'আপনার রেজিস্ট্রেশন সফল হয়েছে! অ্যাডমিন পর্যালোচনা ও অনুমোদনের পর আপনি সম্পূর্ণ অ্যাক্সেস পাবেন।');
    }

    public function pendingApproval()
    {
        return view('auth.pending-approval');
    }
}
