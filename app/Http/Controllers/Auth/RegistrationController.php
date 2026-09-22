<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\AccountVerificationMail;
use App\Models\User;
use App\Rules\StrongPassword;
use App\Services\SecurityAuditService;
use App\Services\SmsService;
use App\Support\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class RegistrationController extends Controller
{
    /**
     * Convert Bengali digits to English digits
     */
    protected function normalizeBnToEn(string $str): string
    {
        $bn = ['০','১','২','৩','৪','৫','৬','৭','৮','৯'];
        $en = ['0','1','2','3','4','5','6','7','8','9'];
        return str_replace($bn, $en, $str);
    }

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
        if ($type === 'author') {
            return app(\Modules\Author\Http\Controllers\Frontend\AuthorController::class)->register();
        }
        if ($type === 'publisher') {
            return app(\Modules\Publisher\Http\Controllers\Frontend\PublisherController::class)->register();
        }
        return view("auth.register-{$type}");
    }

    // Send Email verification OTP for registration
    public function sendEmailOtp(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $email = strtolower(trim($request->input('email')));

        // Check if email already registered
        $existing = User::where('email', $email)->first();
        if ($existing) {
            return response()->json([
                'success' => false,
                'already_exists' => true,
                'message' => 'An account is already registered with this email address.',
            ], 422);
        }

        // Generate 6-digit OTP
        $otpCode = (string) rand(100000, 999999);
        $cacheKey = 'reg_email_otp_' . md5($email);
        Cache::put($cacheKey, $otpCode, now()->addMinutes(2));

        // Dispatch email
        try {
            Mail::to($email)->send(new AccountVerificationMail($email, $otpCode, 2));
        } catch (\Throwable $e) {
            Log::error('Registration Email OTP dispatch failed: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'A 6-digit verification code has been sent to your email (Valid for 2 minutes).',
            'cooldown' => 45,
        ]);
    }

    // Verify Email OTP for registration
    public function verifyEmailOtp(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'otp'   => ['required', 'string', 'min:4', 'max:10'],
        ]);

        $email = strtolower(trim($request->input('email')));
        $rawOtp = $this->normalizeBnToEn(trim($request->input('otp')));
        $cleanOtp = preg_replace('/[^\d]/', '', $rawOtp);

        $cacheKey = 'reg_email_otp_' . md5($email);
        $cachedOtp = Cache::get($cacheKey);

        if (!$cachedOtp || $cleanOtp !== (string) $cachedOtp) {
            return response()->json([
                'success' => false,
                'message' => 'The email verification code is invalid or has expired (2-minute limit). Please check your email and try again.',
            ], 422);
        }

        // Mark as verified in session
        Cache::forget($cacheKey);
        session(['email_verified_' . md5($email) => true]);

        return response()->json([
            'success' => true,
            'message' => 'Email address verified successfully!',
        ]);
    }

    // Send SMS verification OTP for registration
    public function sendOtp(Request $request, SmsService $smsService)
    {
        $request->validate([
            'phone' => ['required', 'string', 'max:20'],
            'country_code' => ['nullable', 'string', 'max:10'],
        ]);

        $countryCode = trim($request->input('country_code', '+880'));
        $rawPhone = $this->normalizeBnToEn(trim($request->input('phone')));
        $cleanDigits = preg_replace('/[^0-9]/', '', $rawPhone);

        // Normalize phone with country code
        if (str_starts_with($countryCode, '+880') || $countryCode === '880') {
            if (str_starts_with($cleanDigits, '880')) {
                $cleanDigits = substr($cleanDigits, 3);
            }
            if (str_starts_with($cleanDigits, '0')) {
                $cleanDigits = substr($cleanDigits, 1);
            }
            $fullPhone = '+880' . $cleanDigits;
            $localPhone = '0' . $cleanDigits;
        } else {
            $prefix = str_starts_with($countryCode, '+') ? $countryCode : '+' . $countryCode;
            $fullPhone = $prefix . ltrim($cleanDigits, '0');
            $localPhone = $fullPhone;
        }

        // Check if user already exists
        $existing = User::where('phone', $fullPhone)
            ->orWhere('phone', $localPhone)
            ->orWhere('phone', $rawPhone)
            ->orWhere('phone', '+88' . $localPhone)
            ->orWhere('phone', '88' . $localPhone)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'already_exists' => true,
                'message' => 'An account is already registered with this mobile number.',
                'phone' => $rawPhone,
                'login_url' => route('login'),
                'forgot_url' => route('password.request'),
            ], 422);
        }

        // Generate 6-digit OTP
        $otpCode = (string) rand(100000, 999999);
        
        // Cache OTP strictly for 2 minutes across all key formats
        $ttl = now()->addMinutes(2);
        Cache::put('reg_otp_' . md5($fullPhone), $otpCode, $ttl);
        Cache::put('reg_otp_' . md5($localPhone), $otpCode, $ttl);
        Cache::put('reg_otp_' . $cleanDigits, $otpCode, $ttl);

        // Dispatch SMS
        $res = $smsService->sendVerificationOtp($fullPhone, $otpCode);

        // WhatsApp integration for official helpline
        $officialWhatsApp = '+8801558712810';
        $cleanOfficialWhatsApp = '8801558712810';
        $userCleanPhone = preg_replace('/[^0-9]/', '', $fullPhone);
        $whatsappMessage = "ideaabd.com — Your Account Verification Code is: {$otpCode} (Valid for 2 minutes).\n\nOfficial Helpline: {$officialWhatsApp}";
        
        $userWhatsappUrl = 'https://api.whatsapp.com/send?phone=' . $userCleanPhone . '&text=' . urlencode($whatsappMessage);
        $supportWhatsappUrl = 'https://api.whatsapp.com/send?phone=' . $cleanOfficialWhatsApp . '&text=' . urlencode("Hello IDEA Publication, please verify my registration OTP for phone number: {$fullPhone}. OTP Code: {$otpCode}");

        return response()->json([
            'success' => true,
            'message' => '6-digit verification code sent successfully (Valid for 2 minutes).',
            'cooldown' => 60,
            'whatsapp_url' => $userWhatsappUrl,
            'support_whatsapp_url' => $supportWhatsappUrl,
            'official_whatsapp' => $officialWhatsApp,
        ]);
    }

    // Verify SMS OTP for registration
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'phone' => ['required', 'string'],
            'country_code' => ['nullable', 'string'],
            'otp' => ['required', 'string', 'min:4', 'max:10'],
        ]);

        $countryCode = trim($request->input('country_code', '+880'));
        $rawPhone = $this->normalizeBnToEn(trim($request->input('phone')));
        $cleanDigits = preg_replace('/[^0-9]/', '', $rawPhone);
        $rawOtp = $this->normalizeBnToEn(trim($request->input('otp')));
        $cleanOtp = preg_replace('/[^\d]/', '', $rawOtp);

        if (str_starts_with($countryCode, '+880') || $countryCode === '880') {
            if (str_starts_with($cleanDigits, '880')) {
                $cleanDigits = substr($cleanDigits, 3);
            }
            if (str_starts_with($cleanDigits, '0')) {
                $cleanDigits = substr($cleanDigits, 1);
            }
            $fullPhone = '+880' . $cleanDigits;
            $localPhone = '0' . $cleanDigits;
        } else {
            $prefix = str_starts_with($countryCode, '+') ? $countryCode : '+' . $countryCode;
            $fullPhone = $prefix . ltrim($cleanDigits, '0');
            $localPhone = $fullPhone;
        }

        $cachedOtp = Cache::get('reg_otp_' . md5($fullPhone))
            ?? Cache::get('reg_otp_' . md5($localPhone))
            ?? Cache::get('reg_otp_' . $cleanDigits);

        if (!$cachedOtp || $cleanOtp !== (string) $cachedOtp) {
            return response()->json([
                'success' => false,
                'message' => 'The verification code is invalid or has expired (2-minute limit). Please check your SMS and try again.',
            ], 422);
        }

        // Mark as verified in session
        Cache::forget('reg_otp_' . md5($fullPhone));
        Cache::forget('reg_otp_' . md5($localPhone));
        Cache::forget('reg_otp_' . $cleanDigits);

        session(['phone_verified_' . md5($fullPhone) => true]);
        session(['phone_verified_' . md5($localPhone) => true]);
        session(['phone_verified_' . $cleanDigits => true]);

        return response()->json([
            'success' => true,
            'message' => 'Mobile number verified successfully!',
        ]);
    }

    /**
     * Complete Unified Onboarding Registration (Amazon-style Multi-step Onboarding)
     * Handles Category selection (Buyer, Author, Publisher, Seller) + Primary Address
     * and auto-logs the user in directly to /my-account.
     */
    public function completeUnifiedRegistration(Request $request)
    {
        \Illuminate\Support\Facades\Validator::make($request->all(), [
            'name'           => ['required', 'string', 'max:255'],
            'email'          => ['required', 'email', 'max:255'],
            'phone'          => ['required', 'string', 'max:25'],
            'country_code'   => ['nullable', 'string', 'max:10'],
            'password'       => ['required', 'string', 'min:8', 'max:128', new StrongPassword([
                'name'  => (string) $request->input('name'),
                'email' => (string) $request->input('email'),
                'phone' => (string) $request->input('phone'),
            ])],
            'category'       => ['required', 'string', 'in:buyer,author,publisher,seller'],
            'author_name'    => ['nullable', 'string', 'max:255'],
            'author_name_en' => ['nullable', 'string', 'max:255'],
            'publisher_name' => ['nullable', 'string', 'max:255'],
            'shop_name'      => ['nullable', 'string', 'max:255'],
            'country'        => ['required', 'string', 'max:100'],
            'district'       => ['required', 'string', 'max:100'],
            'thana'          => ['required', 'string', 'max:100'],
            'post_code'      => ['required', 'string', 'max:20'],
            'address'        => ['required', 'string', 'max:500'],
        ])->validate();

        $countryCode = $request->input('country_code', '+880');
        $rawPhone = trim($request->input('phone'));
        $cleanDigits = preg_replace('/[^0-9]/', '', $rawPhone);

        if (str_starts_with($countryCode, '+880') || $countryCode === '880') {
            if (str_starts_with($cleanDigits, '880')) {
                $cleanDigits = substr($cleanDigits, 3);
            }
            if (str_starts_with($cleanDigits, '0')) {
                $cleanDigits = substr($cleanDigits, 1);
            }
            $fullPhone = '+880' . $cleanDigits;
            $localPhone = '0' . $cleanDigits;
        } else {
            $prefix = str_starts_with($countryCode, '+') ? $countryCode : '+' . $countryCode;
            $fullPhone = $prefix . ltrim($cleanDigits, '0');
            $localPhone = $fullPhone;
        }

        $email = trim(strtolower($request->input('email')));
        $category = $request->input('category', 'buyer');

        // Check for existing user by phone or email
        $user = User::where('phone', $fullPhone)
            ->orWhere('phone', $localPhone)
            ->orWhere('email', $email)
            ->first();

        if ($user) {
            if ($user->isAdmin()) {
                \Illuminate\Support\Facades\Auth::login($user, true);
                return response()->json([
                    'success'      => true,
                    'redirect_url' => route('admin.dashboard'),
                    'message'      => 'স্বাগতম অ্যাডমিন! অ্যাডমিন ড্যাশবোর্ডে প্রবেশ করানো হচ্ছে...',
                ]);
            }

            // If user exists, update category if previously default buyer
            if ($user->role === 'buyer' && $category !== 'buyer') {
                $user->role = $category;
                $user->reg_type = $category;
                $user->reg_status = 'pending';
                $user->is_active = false;
                $user->save();

                return response()->json([
                    'success'      => true,
                    'redirect_url' => route('register.success'),
                    'message'      => 'আপনার অ্যাকাউন্ট আপগ্রেড আবেদন সফলভাবে জমা হয়েছে। অ্যাডমিন অনুমোদনের পর সক্রিয় হবে।',
                    'is_approved'  => false,
                ]);
            }

            if (!$user->is_active && !$user->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'এই তথ্য দিয়ে নিবন্ধিত অ্যাকাউন্টটি এখনও অ্যাডমিন অনুমোদনের অপেক্ষায় রয়েছে।',
                ], 422);
            }

            \Illuminate\Support\Facades\Auth::login($user, true);

            $redir = route('my-account');
            if ($user->isAdmin()) {
                $redir = route('admin.dashboard');
            } elseif ($user->isPublisher() && $user->isApproved()) {
                $redir = route('publisher.dashboard');
            } elseif ($user->isAuthor() && $user->isApproved()) {
                $redir = route('author.dashboard');
            } elseif ($user->isSeller() && $user->isApproved()) {
                $redir = route('subadmin.dashboard');
            }

            return response()->json([
                'success'      => true,
                'redirect_url' => $redir,
                'message'      => 'Welcome back! You have successfully signed in.',
            ]);
        }

        $authorName = trim((string) $request->input('author_name', ''));
        $authorNameEn = trim((string) $request->input('author_name_en', ''));
        $publisherName = trim((string) $request->input('publisher_name', ''));
        $shopName = trim((string) $request->input('shop_name', ''));

        if ($category === 'author' && empty($authorName)) {
            $authorName = trim((string) $request->input('name'));
        }

        $displayName = $request->input('name');
        if ($category === 'author' && !empty($authorName)) {
            $displayName = $authorName;
        } elseif ($category === 'publisher' && !empty($publisherName)) {
            $displayName = $publisherName;
        } elseif ($category === 'seller' && !empty($shopName)) {
            $displayName = $shopName;
        }

        // Create new User
        $regData = [
            'category'        => $category,
            'country'         => $request->input('country', 'Bangladesh'),
            'district'        => $request->input('district', ''),
            'thana'           => $request->input('thana', ''),
            'post_code'       => $request->input('post_code', ''),
            'address'         => $request->input('address', ''),
            'country_code'    => $countryCode,
            'author_name'     => $authorName,
            'author_name_en'  => $authorNameEn,
            'author_name_bn'  => $authorName,
            'name_bn'         => $authorName,
            'name_en'         => $authorNameEn,
            'publisher_name'  => $publisherName,
            'shop_name'       => $shopName,
            'registered_ip'   => $request->ip(),
            'registered_at'   => now()->toIso8601String(),
        ];

        $isCustomer = in_array($category, ['buyer', 'customer']);
        $isActive = $isCustomer;
        $regStatus = $isCustomer ? User::STATUS_APPROVED : User::STATUS_PENDING;

        $user = User::create([
            'name'              => $displayName,
            'email'             => $email,
            'phone'             => $fullPhone,
            'password'          => Hash::make($request->input('password')),
            'role'              => $category,
            'reg_type'          => $category,
            'reg_status'        => $regStatus,
            'reg_data'          => $regData,
            'is_active'         => $isActive,
            'email_verified_at' => (!\App\Support\SiteSetting::isEmailVerificationEnabled() || $isCustomer) ? now() : null,
        ]);

        // If author category, sync unified author record so that all books, ebooks, ideapatra, and author directory link directly here
        if ($category === 'author' && class_exists(\Modules\Author\Models\Author::class)) {
            try {
                $authorRecord = \Modules\Author\Models\Author::findOrCreateUnified([
                    'name'        => $authorName,
                    'name_bn'     => $authorName,
                    'name_en'     => $authorNameEn ?: null,
                    'email'       => $user->email,
                    'phone'       => $user->phone,
                    'user_id'     => $user->id,
                    'is_active'   => false, // Pending admin approval
                    'is_verified' => false,
                ]);

                if ($authorRecord && $authorRecord->id) {
                    $regData['author_id'] = $authorRecord->id;
                    $user->reg_data = $regData;
                    $user->save();
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Author unified sync error: ' . $e->getMessage());
            }
        }

        // If publisher category, sync unified publisher record so that all books, ebooks, directory, and billing vouchers link directly here
        if ($category === 'publisher' && class_exists(\Modules\Publisher\Models\Publisher::class)) {
            try {
                $pubHouse = trim((string) ($request->input('publishing_house_name') ?: $request->input('publisher_name') ?: $request->input('name')));
                $fullAddress = trim(implode(', ', array_filter([
                    $request->input('address'),
                    $request->input('thana'),
                    $request->input('district'),
                    $request->input('post_code'),
                ])));

                $publisherRecord = \Modules\Publisher\Models\Publisher::findOrCreateUnified([
                    'name'        => $pubHouse,
                    'email'       => $user->email,
                    'phone'       => $user->phone,
                    'address'     => $fullAddress,
                    'country'     => $request->input('country', 'Bangladesh'),
                    'is_active'   => false, // Pending admin approval
                    'is_verified' => false,
                ]);

                if ($publisherRecord && $publisherRecord->id) {
                    $regData['publisher_id'] = $publisherRecord->id;
                    $regData['publishing_house_name'] = $pubHouse;
                    $regData['publisher_owner_name'] = trim((string) $request->input('publisher_owner_name', $user->name));
                    $user->reg_data = $regData;
                    $user->save();
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Publisher unified sync error: ' . $e->getMessage());
            }
        }

        // Send registration SMS notification
        try {
            if ($isCustomer) {
                $welcomeMsg = "আইডিয়া প্রকাশনে আপনাকে স্বাগতম! আপনার কাস্টমার অ্যাকাউন্ট সফলভাবে সক্রিয় হয়েছে। বই পড়ুন, জ্ঞানের সাথে থাকুন। www.ideaabd.com";
                \App\Services\SmsService::send($user->phone, $welcomeMsg);
            }
        } catch (\Throwable $smsEx) {
            \Illuminate\Support\Facades\Log::warning("Customer welcome SMS note: " . $smsEx->getMessage());
        }

        if ($isCustomer) {
            // Auto login customer and redirect to my-account
            \Illuminate\Support\Facades\Auth::login($user, true);

            return response()->json([
                'success'      => true,
                'redirect_url' => route('my-account'),
                'message'      => 'আপনার কাস্টমার অ্যাকাউন্ট সফলভাবে তৈরি ও সক্রিয় হয়েছে!',
                'is_approved'  => true,
            ]);
        }

        // Format role label for pending notice
        $typeLabels = [
            'author'    => 'লেখক (Author)',
            'publisher' => 'প্রকাশক (Publisher)',
            'seller'    => 'সেলার (Seller)',
        ];
        $typeLabel = $typeLabels[$category] ?? ucfirst($category);

        try {
            $pendingMsg = "আইডিয়া প্রকাশন — আপনার {$typeLabel} রেজিস্ট্রেশন সফলভাবে জমা হয়েছে। অ্যাডমিন পর্যালোচনার পর অনুমোদন দিলে অ্যাকাউন্টটি সক্রিয় হবে। হেল্পলাইন: 01726976982";
            \App\Services\SmsService::send($user->phone, $pendingMsg);
        } catch (\Throwable $smsEx) {
            \Illuminate\Support\Facades\Log::warning("Partner pending SMS note: " . $smsEx->getMessage());
        }

        session(['registration_summary' => [
            'user_id'        => $user->id,
            'name'           => $user->name,
            'email'          => $user->email,
            'phone'          => $user->phone,
            'type'           => $category,
            'type_label'     => $typeLabel,
            'is_active'      => false,
            'reg_status'     => 'pending',
            'created_at'     => now()->format('d M, Y - h:i A'),
            'shop_name'      => $regData['shop_name'] ?? null,
            'publisher_name' => $regData['publishing_house_name'] ?? null,
            'pen_name'       => $regData['author_name'] ?? null,
        ]]);

        return response()->json([
            'success'      => true,
            'redirect_url' => route('register.success'),
            'message'      => "আপনার {$typeLabel} রেজিস্ট্রেশন সফলভাবে জমা হয়েছে। অ্যাডমিন পর্যালোচনার পর অনুমোদন দিলে অ্যাকাউন্টটি সক্রিয় হবে।",
            'is_approved'  => false,
            'pending'      => true,
        ]);
    }

    // Handle all registration types
    public function register(Request $request, string $type)
    {
        $allowed = ['seller', 'publisher', 'author', 'buyer'];
        abort_unless(in_array($type, $allowed), 404);

        if ($type === 'author') {
            return app(\Modules\Author\Http\Controllers\Frontend\AuthorController::class)->storeRegistration($request);
        }
        if ($type === 'publisher') {
            return app(\Modules\Publisher\Http\Controllers\Frontend\PublisherController::class)->storeRegistration($request);
        }

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
            'max:128',
            new StrongPassword([
                'name'  => (string) ($request->input('name') ?? $request->input('name_bn') ?? $request->input('name_en')),
                'email' => (string) $request->input('email'),
                'phone' => (string) $request->input('phone'),
            ]),
        ];

        if ($type === 'buyer') {
            $countryCode = $request->input('country_code', '+880');
            $rawPhone = trim($request->input('phone', ''));
            $cleanDigits = preg_replace('/[^0-9]/', '', $rawPhone);
            if (str_starts_with($countryCode, '+880') || $countryCode === '880') {
                if (str_starts_with($cleanDigits, '880')) {
                    $cleanDigits = substr($cleanDigits, 3);
                }
                if (str_starts_with($cleanDigits, '0')) {
                    $cleanDigits = substr($cleanDigits, 1);
                }
                $formattedPhone = '0' . $cleanDigits;
            } else {
                $prefix = str_starts_with($countryCode, '+') ? $countryCode : '+' . $countryCode;
                $formattedPhone = $prefix . ltrim($cleanDigits, '0');
            }

            $request->merge(['phone' => $formattedPhone]);

            $base = $request->validate([
                'name'     => ['required', 'string', 'max:255'],
                'phone'    => ['required', 'string', 'max:20', 'unique:users,phone'],
                'email'    => ['nullable', 'email', 'max:255', 'unique:users,email'],
                'password' => $passwordRules,
            ], $customMessages);

            // If buyer didn't specify an email, auto-create a unique placeholder for DB uniqueness
            if (empty($base['email'])) {
                $cleanP = preg_replace('/[^0-9]/', '', $formattedPhone);
                $generatedEmail = $cleanP . '@buyer.ideaabd.com';
                $existing = User::where('email', $generatedEmail)->first();
                if ($existing) {
                    $generatedEmail = $cleanP . '_' . rand(100, 999) . '@buyer.ideaabd.com';
                }
                $base['email'] = $generatedEmail;
            }
        } elseif ($type === 'author') {
            // Author MUST provide their own real email and mobile number (acts as username)
            $base = $request->validate([
                'name_bn'  => ['required', 'string', 'max:255'],
                'name_en'  => ['required', 'string', 'max:255'],
                'phone'    => ['required', 'string', 'max:20', 'unique:users,phone'],
                'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
                'password' => $passwordRules,
            ], $customMessages);
            $base['name'] = $base['name_bn'] ?: $base['name_en'];
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
                'full_name'      => ['nullable', 'string', 'max:255'],
                'name_bn'        => ['nullable', 'string', 'max:255'],
                'name_en'        => ['nullable', 'string', 'max:255'],
                'pen_name'       => ['nullable', 'string', 'max:255'],
                'bio'            => ['nullable', 'string'],
                'genre'          => ['nullable'],
                'genres'         => ['nullable', 'array'],
                'genres.*'       => ['nullable', 'string'],
                'nid'            => ['nullable', 'string'],
                'nid_file'       => ['nullable', 'file', 'mimes:jpeg,png,jpg,webp,pdf', 'max:10240'],
                'avatar'         => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:10240'],
                'avatar_cropped' => ['nullable', 'string'],
            ]),
            'buyer' => $request->validate([
                'address'      => ['nullable', 'string'],
                'date_of_birth'=> ['nullable', 'date'],
            ]),
        };

        // Format genre list if provided as array or string
        if ($type === 'author') {
            $selectedGenres = (array) $request->input('genres', []);
            if ($request->filled('genre') && !in_array($request->input('genre'), $selectedGenres)) {
                $selectedGenres[] = $request->input('genre');
            }
            $genreString = implode(', ', array_unique(array_filter(array_map('trim', $selectedGenres))));
            $extra['genre'] = $genreString;
            $extra['genres'] = array_values(array_unique(array_filter(array_map('trim', $selectedGenres))));

            // Handle NID document upload
            if ($request->hasFile('nid_file')) {
                $extra['nid_file'] = $request->file('nid_file')->store('documents/nid', 'public');
            }
            if (!empty($base['name_bn'])) {
                $extra['name_bn'] = $base['name_bn'];
            }
            if (!empty($base['name_en'])) {
                $extra['name_en'] = $base['name_en'];
            }
        }

        // Handle avatar photo upload (Convert to .avif automatically)
        $avatarPath = null;
        if ($request->filled('avatar_cropped') && str_starts_with($request->input('avatar_cropped'), 'data:image')) {
            $avatarPath = \App\Services\ImageOptimizerService::convertBase64AndStore($request->input('avatar_cropped'), 'avatars', 'public', 85, 600, 600);
        }
        
        if (!$avatarPath && $request->hasFile('avatar')) {
            $avatarPath = \App\Services\ImageOptimizerService::convertAndStore($request->file('avatar'), 'avatars', 'public', 85, 600, 600);
        }

        // Generate 6 digit verification OTP
        $otpCode = rand(100000, 999999);
        $smsMessage = "আইডিয়া প্রকাশনে আপনাকে স্বাগতম! আপনার অ্যাকাউন্ট ভেরিফিকেশন কোড: {$otpCode}। বই পড়ুন, জ্ঞানের সাথে থাকুন। www.ideaabd.com";

        // Dispatch & Log SMS
        try {
            \App\Services\SmsService::send($base['phone'], $smsMessage);
        } catch (\Throwable $smsEx) {
            Log::warning("Could not dispatch registration SMS: " . $smsEx->getMessage());
        }
        Log::info("SMS Verification dispatched to {$base['phone']}: {$smsMessage}");

        // Only buyer is auto-approved immediately. Author, Seller, Publisher must await admin approval!
        $isActive = ($type === 'buyer');
        $regStatus = $isActive ? User::STATUS_APPROVED : User::STATUS_PENDING;

        $user = User::create([
            'name'       => $base['name'],
            'email'      => $base['email'],
            'phone'      => $base['phone'],
            'avatar'     => $avatarPath,
            'password'   => Hash::make($base['password']),
            'role'       => $type === 'buyer' ? User::ROLE_BUYER : $type,
            'reg_type'   => $type,
            'reg_status' => $regStatus,
            'reg_data'   => array_merge($extra, ['otp_code' => $otpCode, 'avatar' => $avatarPath]),
            'is_active'  => $isActive,
            'email_verified_at' => $isActive ? now() : null,
        ]);

        // Auto create/sync entry in authors table if type is author using Unified registration
        if ($type === 'author') {
            try {
                $authorName = $base['name_bn'] ?? (!empty($extra['pen_name']) ? $extra['pen_name'] : $base['name']);
                \Modules\Author\Models\Author::findOrCreateUnified([
                    'name'        => $authorName,
                    'name_bn'     => $base['name_bn'] ?? $authorName,
                    'name_en'     => $base['name_en'] ?? ($extra['name_en'] ?? null),
                    'phone'       => $base['phone'],
                    'email'       => $base['email'],
                    'bio'         => $extra['bio'] ?? null,
                    'avatar'      => $avatarPath,
                    'user_id'     => $user->id,
                    'is_active'   => false, // Pending admin approval
                    'is_verified' => false,
                ]);
            } catch (\Throwable $e) {
                Log::warning("Could not sync unified author entry on registration: " . $e->getMessage());
            }
        }

        // Format type label
        $typeLabels = [
            'buyer'     => 'সাধারণ পাঠক / ক্রেতা',
            'author'    => 'লেখক ও গবেষক',
            'seller'    => 'সেলার / বই বিক্রেতা ও ডিলার',
            'publisher' => 'প্রকাশনী ও কোম্পানি',
        ];
        $typeLabel = $typeLabels[$type] ?? ucfirst($type);

        $registrationSummary = [
            'user_id'        => $user->id,
            'name'           => $user->name,
            'email'          => $user->email,
            'phone'          => $user->phone,
            'type'           => $type,
            'type_label'     => $typeLabel,
            'is_active'      => $isActive,
            'reg_status'     => $regStatus,
            'created_at'     => now()->format('d M, Y - h:i A'),
            'shop_name'      => $extra['shop_name'] ?? null,
            'publisher_name' => $extra['publisher_name'] ?? null,
            'pen_name'       => $extra['pen_name'] ?? null,
        ];

        session(['registration_summary' => $registrationSummary]);

        return redirect()->route('register.success')
            ->with('success', 'আপনার রেজিস্ট্রেশন সফলভাবে সম্পন্ন হয়েছে!');
    }

    /**
     * Display dedicated Registration Success & Submitted confirmation page.
     */
    public function registrationSuccess(Request $request)
    {
        $summary = session('registration_summary');
        $user = auth()->user();

        if (!$summary && $user) {
            $typeLabels = [
                'buyer'     => 'সাধারণ পাঠক / ক্রেতা',
                'author'    => 'লেখক ও গবেষক',
                'seller'    => 'সেলার / বই বিক্রেতা ও ডিলার',
                'publisher' => 'প্রকাশনী ও কোম্পানি',
            ];
            $summary = [
                'user_id'    => $user->id,
                'name'       => $user->name,
                'email'      => $user->email,
                'phone'      => $user->phone,
                'type'       => $user->reg_type ?: ($user->role ?: 'buyer'),
                'type_label' => $typeLabels[$user->reg_type ?: $user->role] ?? 'ব্যবহারকারী অ্যাকাউন্ট',
                'is_active'  => (bool) $user->is_active,
                'reg_status' => $user->reg_status,
                'created_at' => $user->created_at ? $user->created_at->format('d M, Y - h:i A') : now()->format('d M, Y - h:i A'),
            ];
        }

        return view('auth.registration-success', compact('summary'));
    }

    public function pendingApproval(Request $request)
    {
        return $this->registrationSuccess($request);
    }
}
