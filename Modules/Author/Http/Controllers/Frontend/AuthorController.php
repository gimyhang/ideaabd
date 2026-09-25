<?php

namespace Modules\Author\Http\Controllers\Frontend;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Modules\Author\Models\Author;

class AuthorController extends Controller
{
    public function index()
    {
        return app(\App\Http\Controllers\AuthorController::class)->index(request());
    }

    public function show($slug)
    {
        return app(\App\Http\Controllers\AuthorController::class)->show($slug);
    }

    /**
     * Display author registration page with all country codes and literary genres.
     */
    public function register()
    {
        return redirect()->to(route('login', ['mode' => 'register', 'role' => 'author']));
    }

    /**
     * Store new Author registration, create User account with auto-login, and sync Author profile.
     */
    public function storeRegistration(Request $request): RedirectResponse|JsonResponse
    {
        // 1. Anti-bot honeypot check
        if ($request->filled('author_hp_field') || $request->filled('website_trap')) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'রোবট ট্র্যাপ সনাক্ত হয়েছে!'], 422);
            }
            return back()->with('error', 'রোবট ট্র্যাপ সনাক্ত হয়েছে!')->withInput();
        }

        // 2. Validation
        $rules = [
            'name'                  => 'required|string|max:255',
            'pen_name'              => 'nullable|string|max:255',
            'name_en'               => 'nullable|string|max:255',
            'email'                 => 'required|email|max:255',
            'country_code'          => 'nullable|string|max:10',
            'phone'                 => 'required|string|max:30',
            'password'              => Auth::check() ? 'nullable|string|min:6' : 'required|string|min:6',
            'bio'                   => 'nullable|string|max:5000',
            'genres'                => 'nullable|array',
            'genres.*'              => 'string|max:100',
            'avatar'                => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'website'               => 'nullable|string|max:255',
            'facebook'              => 'nullable|string|max:255',
            'payout_account_type'   => 'nullable|string|max:50',
            'payout_account_details'=> 'nullable|string|max:255',
            'terms_agreed'          => 'required|accepted',
        ];

        $messages = [
            'name.required'         => 'আপনার পূর্ণ নাম প্রদান করুন।',
            'email.required'        => 'সচল ইমেইল ঠিকানা প্রদান করুন।',
            'email.email'           => 'সঠিক ইমেইল ফরম্যাট প্রদান করুন।',
            'phone.required'        => 'সচল মোবাইল নম্বর প্রদান করুন।',
            'password.required'     => 'ড্যাশবোর্ডে প্রবেশের জন্য অন্তত ৬ অক্ষরের একটি পাসওয়ার্ড দিন।',
            'password.min'          => 'পাসওয়ার্ড অন্তত ৬ অক্ষরের হতে হবে।',
            'avatar.max'            => 'প্রোফাইল ছবির সাইজ ৫ মেগাবাইট (5MB)-এর কম হতে হবে।',
            'terms_agreed.accepted' => 'লেখক নীতিমালা ও শর্তাবলীতে সম্মতি প্রদান করুন।',
            'terms_agreed.required' => 'লেখক নীতিমালা ও শর্তাবলীতে সম্মতি প্রদান করুন।',
        ];

        $validated = $request->validate($rules, $messages);

        // 3. Clean and Merge Phone Number with Country Code
        $countryCode = trim((string) ($validated['country_code'] ?? '+880'));
        if (!str_starts_with($countryCode, '+')) {
            $countryCode = '+' . ltrim($countryCode, '+');
        }
        $rawPhone = trim((string) $validated['phone']);
        
        // If phone already starts with +, use directly; otherwise normalize with selected country dial code
        if (str_starts_with($rawPhone, '+')) {
            $fullPhone = preg_replace('/[^\d+]/', '', $rawPhone);
        } else {
            $digitsOnly = preg_replace('/\D/', '', $rawPhone);
            // If Bangladesh and starts with 0 (e.g. 017xxxxxxxx), strip leading 0
            if ($countryCode === '+880' && str_starts_with($digitsOnly, '0')) {
                $digitsOnly = substr($digitsOnly, 1);
            }
            $fullPhone = $countryCode . $digitsOnly;
        }

        // 4. Handle Avatar Upload
        $avatarPath = null;
        if ($request->hasFile('avatar') && $request->file('avatar')->isValid()) {
            $avatarFile = $request->file('avatar');
            $ext = $avatarFile->getClientOriginalExtension() ?: 'jpg';
            $filename = 'author_' . Str::slug($validated['name']) . '_' . time() . '.' . $ext;
            $avatarPath = $avatarFile->storeAs('authors', $filename, 'public');
        }

        // 5. Gather Extended Metadata
        $genres = $validated['genres'] ?? [];
        $penName = !empty($validated['pen_name']) ? trim($validated['pen_name']) : $validated['name'];
        $nameEn = !empty($validated['name_en']) ? trim($validated['name_en']) : null;
        $bio = !empty($validated['bio']) ? trim($validated['bio']) : null;
        $website = !empty($validated['website']) ? trim($validated['website']) : null;
        $facebook = !empty($validated['facebook']) ? trim($validated['facebook']) : null;
        $payoutType = $validated['payout_account_type'] ?? 'bkash';
        $payoutDetails = $validated['payout_account_details'] ?? null;

        $socialLinks = array_filter([
            'facebook' => $facebook,
            'website'  => $website,
        ]);

        $regData = [
            'pen_name'        => $penName,
            'name_en'         => $nameEn,
            'genres'          => $genres,
            'social_links'    => $socialLinks,
            'payout_type'     => $payoutType,
            'payout_details'  => $payoutDetails,
            'registered_ip'   => $request->ip(),
            'registered_at'   => now()->toIso8601String(),
        ];

        // 6. User Account Creation or Role Upgrade
        $user = Auth::user();
        if (!$user) {
            $existingUser = User::where('email', strtolower($validated['email']))
                ->orWhere('phone', $fullPhone)
                ->first();

            if ($existingUser) {
                $user = $existingUser;
                // Upgrade to author role if customer/buyer
                if ($user->role === User::ROLE_CUSTOMER || $user->role === User::ROLE_BUYER || empty($user->role)) {
                    $user->role = User::ROLE_AUTHOR;
                }
                $user->reg_type = User::ROLE_AUTHOR;
                $user->reg_status = User::STATUS_PENDING;
                $user->reg_data = array_merge($user->reg_data ?? [], $regData);
                if ($avatarPath && empty($user->avatar)) {
                    $user->avatar = $avatarPath;
                }
                if (!empty($validated['password'])) {
                    $user->password = Hash::make($validated['password']);
                }
                $user->save();
            } else {
                $user = User::create([
                    'name'       => $validated['name'],
                    'email'      => strtolower($validated['email']),
                    'phone'      => $fullPhone,
                    'password'   => Hash::make($validated['password'] ?? Str::random(12)),
                    'role'       => User::ROLE_AUTHOR,
                    'reg_type'   => User::ROLE_AUTHOR,
                    'reg_status' => User::STATUS_PENDING,
                    'avatar'     => $avatarPath,
                    'is_active'  => true,
                    'reg_data'   => $regData,
                ]);
            }

            Auth::login($user);
        } else {
            // Already logged in user registering as author
            if ($user->role === User::ROLE_CUSTOMER || $user->role === User::ROLE_BUYER || empty($user->role)) {
                $user->role = User::ROLE_AUTHOR;
            }
            $user->reg_type = User::ROLE_AUTHOR;
            $user->reg_data = array_merge($user->reg_data ?? [], $regData);
            if ($avatarPath) {
                $user->avatar = $avatarPath;
            }
            $user->save();
        }

        // 7. Author Profile Creation / Unified Sync
        $author = Author::findOrCreateUnified([
            'name'                   => $validated['name'],
            'name_bn'                => $validated['name'],
            'name_en'                => $nameEn,
            'pen_name'               => $penName,
            'email'                  => strtolower($validated['email']),
            'phone'                  => $fullPhone,
            'bio'                    => $bio,
            'avatar'                 => $avatarPath ?: $user->avatar,
            'website'                => $website,
            'social_links'           => $socialLinks,
            'user_id'                => $user->id,
            'payout_account_type'    => $payoutType,
            'payout_account_details' => $payoutDetails,
            'owner_name'             => $validated['name'],
            'owner_phone'            => $fullPhone,
            'is_active'              => true,
            'is_verified'            => false,
        ]);

        // Send registration confirmation SMS
        try {
            $authorMsg = "আইডিয়া প্রকাশন — সম্মানিত লেখক ({$validated['name']}), লেখক হিসেবে আপনার রেজিস্ট্রেশন আবেদন সফলভাবে জমা হয়েছে। অ্যাডমিন অনুমোদনের পর লেখক স্টুডিও সক্রিয় হবে। হেল্পলাইন: 01726976982";
            \App\Services\SmsService::send($fullPhone, $authorMsg);
        } catch (\Throwable $smsEx) {
            \Illuminate\Support\Facades\Log::warning("Author registration SMS notice: " . $smsEx->getMessage());
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success'      => true,
                'message'      => 'অভিনন্দন! লেখক হিসেবে আপনার রেজিস্ট্রেশন সফলভাবে সম্পন্ন হয়েছে। অ্যাডমিন অনুমোদন সম্পন্ন হলে লেখক স্টুডিও সক্রিয় হবে। বর্তমানে আপনি কাস্টমার অ্যাকাউন্ট ব্যবহার করতে পারেন।',
                'redirect_url' => route('my-account'),
            ]);
        }

        return redirect()->route('my-account')
            ->with('success', 'অভিনন্দন! লেখক হিসেবে আপনার আবেদনটি গৃহীত হয়েছে। অ্যাডমিন অনুমোদনের পর লেখক স্টুডিও উন্মুক্ত হবে। বর্তমানে আপনি সাধারণ গ্রাহক হিসেবে কেনাকাটা ও অ্যাকাউন্ট ব্যবহার করতে পারছেন।');
    }

    /**
     * Curated Master Country Dial Codes & ISO Map with Flags and Bengali Names.
     */
    protected function getAllCountryCodes(): array
    {
        return [
            // Priority & High Traffic Countries
            ['code' => 'BD', 'name' => 'বাংলাদেশ', 'name_en' => 'Bangladesh', 'dial_code' => '+880', 'flag' => '🇧🇩', 'priority' => 1],
            ['code' => 'IN', 'name' => 'ভারত', 'name_en' => 'India', 'dial_code' => '+91', 'flag' => '🇮🇳', 'priority' => 1],
            ['code' => 'SA', 'name' => 'সৌদি আরব', 'name_en' => 'Saudi Arabia', 'dial_code' => '+966', 'flag' => '🇸🇦', 'priority' => 1],
            ['code' => 'AE', 'name' => 'সংযুক্ত আরব আমিরাত', 'name_en' => 'United Arab Emirates', 'dial_code' => '+971', 'flag' => '🇦🇪', 'priority' => 1],
            ['code' => 'MY', 'name' => 'মালয়েশিয়া', 'name_en' => 'Malaysia', 'dial_code' => '+60', 'flag' => '🇲🇾', 'priority' => 1],
            ['code' => 'US', 'name' => 'যুক্তরাষ্ট্র (USA)', 'name_en' => 'United States', 'dial_code' => '+1', 'flag' => '🇺🇸', 'priority' => 1],
            ['code' => 'GB', 'name' => 'যুক্তরাজ্য (UK)', 'name_en' => 'United Kingdom', 'dial_code' => '+44', 'flag' => '🇬🇧', 'priority' => 1],
            ['code' => 'QA', 'name' => 'কাতার', 'name_en' => 'Qatar', 'dial_code' => '+974', 'flag' => '🇶🇦', 'priority' => 1],
            ['code' => 'OM', 'name' => 'ওমান', 'name_en' => 'Oman', 'dial_code' => '+968', 'flag' => '🇴🇲', 'priority' => 1],
            ['code' => 'KW', 'name' => 'কুয়েত', 'name_en' => 'Kuwait', 'dial_code' => '+965', 'flag' => '🇰🇼', 'priority' => 1],
            ['code' => 'BH', 'name' => 'বাহরাইন', 'name_en' => 'Bahrain', 'dial_code' => '+973', 'flag' => '🇧🇭', 'priority' => 1],
            ['code' => 'SG', 'name' => 'সিঙ্গাপুর', 'name_en' => 'Singapore', 'dial_code' => '+65', 'flag' => '🇸🇬', 'priority' => 1],
            ['code' => 'CA', 'name' => 'কানাডা', 'name_en' => 'Canada', 'dial_code' => '+1', 'flag' => '🇨🇦', 'priority' => 1],
            ['code' => 'AU', 'name' => 'অস্ট্রেলিয়া', 'name_en' => 'Australia', 'dial_code' => '+61', 'flag' => '🇦🇺', 'priority' => 1],
            ['code' => 'IT', 'name' => 'ইতালি', 'name_en' => 'Italy', 'dial_code' => '+39', 'flag' => '🇮🇹', 'priority' => 1],
            ['code' => 'DE', 'name' => 'জার্মানি', 'name_en' => 'Germany', 'dial_code' => '+49', 'flag' => '🇩🇪', 'priority' => 1],
            ['code' => 'FR', 'name' => 'ফ্রান্স', 'name_en' => 'France', 'dial_code' => '+33', 'flag' => '🇫🇷', 'priority' => 1],
            ['code' => 'JP', 'name' => 'জাপান', 'name_en' => 'Japan', 'dial_code' => '+81', 'flag' => '🇯🇵', 'priority' => 1],
            ['code' => 'KR', 'name' => 'দক্ষিণ কোরিয়া', 'name_en' => 'South Korea', 'dial_code' => '+82', 'flag' => '🇰🇷', 'priority' => 1],

            // Other Countries (Alphabetical)
            ['code' => 'AF', 'name' => 'আফগানিস্তান', 'name_en' => 'Afghanistan', 'dial_code' => '+93', 'flag' => '🇦🇫', 'priority' => 2],
            ['code' => 'AL', 'name' => 'আলবেনিয়া', 'name_en' => 'Albania', 'dial_code' => '+355', 'flag' => '🇦🇱', 'priority' => 2],
            ['code' => 'DZ', 'name' => 'আলজেরিয়া', 'name_en' => 'Algeria', 'dial_code' => '+213', 'flag' => '🇩🇿', 'priority' => 2],
            ['code' => 'AD', 'name' => 'অ্যান্ডোরা', 'name_en' => 'Andorra', 'dial_code' => '+376', 'flag' => '🇦🇩', 'priority' => 2],
            ['code' => 'AO', 'name' => 'অ্যাঙ্গোলা', 'name_en' => 'Angola', 'dial_code' => '+244', 'flag' => '🇦🇴', 'priority' => 2],
            ['code' => 'AR', 'name' => 'আর্জেন্টিনা', 'name_en' => 'Argentina', 'dial_code' => '+54', 'flag' => '🇦🇷', 'priority' => 2],
            ['code' => 'AM', 'name' => 'আর্মেনিয়া', 'name_en' => 'Armenia', 'dial_code' => '+374', 'flag' => '🇦🇲', 'priority' => 2],
            ['code' => 'AT', 'name' => 'অস্ট্রিয়া', 'name_en' => 'Austria', 'dial_code' => '+43', 'flag' => '🇦🇹', 'priority' => 2],
            ['code' => 'AZ', 'name' => 'আজারবাইজান', 'name_en' => 'Azerbaijan', 'dial_code' => '+994', 'flag' => '🇦🇿', 'priority' => 2],
            ['code' => 'BE', 'name' => 'বেলজিয়াম', 'name_en' => 'Belgium', 'dial_code' => '+32', 'flag' => '🇧🇪', 'priority' => 2],
            ['code' => 'BT', 'name' => 'ভুটান', 'name_en' => 'Bhutan', 'dial_code' => '+975', 'flag' => '🇧🇹', 'priority' => 2],
            ['code' => 'BR', 'name' => 'ব্রাজিল', 'name_en' => 'Brazil', 'dial_code' => '+55', 'flag' => '🇧🇷', 'priority' => 2],
            ['code' => 'BN', 'name' => 'ব্রুনাই', 'name_en' => 'Brunei', 'dial_code' => '+673', 'flag' => '🇧🇳', 'priority' => 2],
            ['code' => 'BG', 'name' => 'বুলগেরিয়া', 'name_en' => 'Bulgaria', 'dial_code' => '+359', 'flag' => '🇧🇬', 'priority' => 2],
            ['code' => 'KH', 'name' => 'কম্বোডিয়া', 'name_en' => 'Cambodia', 'dial_code' => '+855', 'flag' => '🇰🇭', 'priority' => 2],
            ['code' => 'CN', 'name' => 'চীন', 'name_en' => 'China', 'dial_code' => '+86', 'flag' => '🇨🇳', 'priority' => 2],
            ['code' => 'CY', 'name' => 'সাইপ্রাস', 'name_en' => 'Cyprus', 'dial_code' => '+357', 'flag' => '🇨🇾', 'priority' => 2],
            ['code' => 'CZ', 'name' => 'চেক প্রজাতন্ত্র', 'name_en' => 'Czech Republic', 'dial_code' => '+420', 'flag' => '🇨🇿', 'priority' => 2],
            ['code' => 'DK', 'name' => 'ডেনমার্ক', 'name_en' => 'Denmark', 'dial_code' => '+45', 'flag' => '🇩🇰', 'priority' => 2],
            ['code' => 'EG', 'name' => 'মিশর', 'name_en' => 'Egypt', 'dial_code' => '+20', 'flag' => '🇪🇬', 'priority' => 2],
            ['code' => 'FI', 'name' => 'ফিনল্যান্ড', 'name_en' => 'Finland', 'dial_code' => '+358', 'flag' => '🇫🇮', 'priority' => 2],
            ['code' => 'GR', 'name' => 'গ্রীস', 'name_en' => 'Greece', 'dial_code' => '+30', 'flag' => '🇬🇷', 'priority' => 2],
            ['code' => 'HK', 'name' => 'হংকং', 'name_en' => 'Hong Kong', 'dial_code' => '+852', 'flag' => '🇭🇰', 'priority' => 2],
            ['code' => 'HU', 'name' => 'হাঙ্গেরি', 'name_en' => 'Hungary', 'dial_code' => '+36', 'flag' => '🇭🇺', 'priority' => 2],
            ['code' => 'ID', 'name' => 'ইন্দোনেশিয়া', 'name_en' => 'Indonesia', 'dial_code' => '+62', 'flag' => '🇮🇩', 'priority' => 2],
            ['code' => 'IE', 'name' => 'আয়ারল্যান্ড', 'name_en' => 'Ireland', 'dial_code' => '+353', 'flag' => '🇮🇪', 'priority' => 2],
            ['code' => 'JO', 'name' => 'জর্ডান', 'name_en' => 'Jordan', 'dial_code' => '+962', 'flag' => '🇯🇴', 'priority' => 2],
            ['code' => 'LB', 'name' => 'লেবানন', 'name_en' => 'Lebanon', 'dial_code' => '+961', 'flag' => '🇱🇧', 'priority' => 2],
            ['code' => 'MV', 'name' => 'মালদ্বীপ', 'name_en' => 'Maldives', 'dial_code' => '+960', 'flag' => '🇲🇻', 'priority' => 2],
            ['code' => 'NP', 'name' => 'নেপাল', 'name_en' => 'Nepal', 'dial_code' => '+977', 'flag' => '🇳🇵', 'priority' => 2],
            ['code' => 'NL', 'name' => 'নেদারল্যান্ডস', 'name_en' => 'Netherlands', 'dial_code' => '+31', 'flag' => '🇳🇱', 'priority' => 2],
            ['code' => 'NZ', 'name' => 'নিউজিল্যান্ড', 'name_en' => 'New Zealand', 'dial_code' => '+64', 'flag' => '🇳🇿', 'priority' => 2],
            ['code' => 'NO', 'name' => 'নরওয়ে', 'name_en' => 'Norway', 'dial_code' => '+47', 'flag' => '🇳🇴', 'priority' => 2],
            ['code' => 'PK', 'name' => 'পাকিস্তান', 'name_en' => 'Pakistan', 'dial_code' => '+92', 'flag' => '🇵🇰', 'priority' => 2],
            ['code' => 'PH', 'name' => 'ফিলিপাইন', 'name_en' => 'Philippines', 'dial_code' => '+63', 'flag' => '🇵🇭', 'priority' => 2],
            ['code' => 'PL', 'name' => 'পোল্যান্ড', 'name_en' => 'Poland', 'dial_code' => '+48', 'flag' => '🇵🇱', 'priority' => 2],
            ['code' => 'PT', 'name' => 'পর্তুগাল', 'name_en' => 'Portugal', 'dial_code' => '+351', 'flag' => '🇵🇹', 'priority' => 2],
            ['code' => 'RO', 'name' => 'রোমানিয়া', 'name_en' => 'Romania', 'dial_code' => '+40', 'flag' => '🇷🇴', 'priority' => 2],
            ['code' => 'RU', 'name' => 'রাশিয়া', 'name_en' => 'Russia', 'dial_code' => '+7', 'flag' => '🇷🇺', 'priority' => 2],
            ['code' => 'ZA', 'name' => 'দক্ষিণ আফ্রিকা', 'name_en' => 'South Africa', 'dial_code' => '+27', 'flag' => '🇿🇦', 'priority' => 2],
            ['code' => 'ES', 'name' => 'স্পেন', 'name_en' => 'Spain', 'dial_code' => '+34', 'flag' => '🇪🇸', 'priority' => 2],
            ['code' => 'LK', 'name' => 'শ্রীলঙ্কা', 'name_en' => 'Sri Lanka', 'dial_code' => '+94', 'flag' => '🇱🇰', 'priority' => 2],
            ['code' => 'SE', 'name' => 'সুইডেন', 'name_en' => 'Sweden', 'dial_code' => '+46', 'flag' => '🇸🇪', 'priority' => 2],
            ['code' => 'CH', 'name' => 'সুইজারল্যান্ড', 'name_en' => 'Switzerland', 'dial_code' => '+41', 'flag' => '🇨🇭', 'priority' => 2],
            ['code' => 'TH', 'name' => 'থাইল্যান্ড', 'name_en' => 'Thailand', 'dial_code' => '+66', 'flag' => '🇹🇭', 'priority' => 2],
            ['code' => 'TR', 'name' => 'তুরস্ক', 'name_en' => 'Turkey', 'dial_code' => '+90', 'flag' => '🇹🇷', 'priority' => 2],
            ['code' => 'VN', 'name' => 'ভিয়েতনাম', 'name_en' => 'Vietnam', 'dial_code' => '+84', 'flag' => '🇻🇳', 'priority' => 2],
        ];
    }
}
