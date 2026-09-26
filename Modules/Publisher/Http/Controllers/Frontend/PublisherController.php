<?php

declare(strict_types=1);

namespace Modules\Publisher\Http\Controllers\Frontend;

use App\Http\Controllers\PublisherController as BasePublisherController;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Modules\Publisher\Models\Publisher;

class PublisherController extends Controller
{
    protected BasePublisherController $base;

    public function __construct()
    {
        $this->base = new BasePublisherController();
    }

    public function index(Request $request)
    {
        return $this->base->index($request);
    }

    public function show(string $slug)
    {
        return $this->base->show($slug);
    }

    /**
     * Display publisher registration page with all country codes and publication genres.
     */
    public function register()
    {
        return redirect()->to(route('login', ['mode' => 'register', 'role' => 'publisher']));
    }

    /**
     * Store new Publisher registration, create User account with auto-login, and sync Publisher profile.
     */
    public function storeRegistration(Request $request): RedirectResponse|JsonResponse
    {
        // 1. Anti-bot honeypot check
        if ($request->filled('publisher_hp_field') || $request->filled('website_trap')) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'রোবট ট্র্যাপ সনাক্ত হয়েছে!'], 422);
            }
            return back()->with('error', 'রোবট ট্র্যাপ সনাক্ত হয়েছে!')->withInput();
        }

        // 2. Validation
        $rules = [
            'name'                  => 'required|string|max:255',
            'contact_person'        => 'required|string|max:255',
            'email'                 => 'required|email|max:255',
            'country_code'          => 'nullable|string|max:10',
            'phone'                 => 'required|string|max:30',
            'password'              => Auth::check() ? 'nullable|string|min:6' : 'required|string|min:6',
            'trade_license_no'      => 'nullable|string|max:100',
            'established_year'      => 'nullable|integer|min:1800|max:2030',
            'address'               => 'nullable|string|max:500',
            'country'               => 'nullable|string|max:100',
            'website'               => 'nullable|string|max:255',
            'description'           => 'nullable|string|max:5000',
            'categories'            => 'nullable|array',
            'categories.*'          => 'string|max:100',
            'logo'                  => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'terms_agreed'          => 'required|accepted',
        ];

        $messages = [
            'name.required'             => 'প্রকাশনী বা প্রতিষ্ঠানের পূর্ণ নাম প্রদান করুন।',
            'contact_person.required'   => 'দায়িত্বপ্রাপ্ত কর্মকর্তার নাম প্রদান করুন।',
            'email.required'            => 'অফিসিয়াল ইমেইল ঠিকানা প্রদান করুন।',
            'email.email'               => 'সঠিক ইমেইল ফরম্যাট প্রদান করুন।',
            'phone.required'            => 'অফিসিয়াল মোবাইল / ফোন নম্বর প্রদান করুন।',
            'password.required'         => 'ড্যাশবোর্ডে প্রবেশের জন্য অন্তত ৬ অক্ষরের একটি পাসওয়ার্ড দিন।',
            'password.min'              => 'পাসওয়ার্ড অন্তত ৬ অক্ষরের হতে হবে।',
            'logo.max'                  => 'লোগোর সাইজ ৫ মেগাবাইট (5MB)-এর কম হতে হবে।',
            'terms_agreed.accepted'     => 'প্রকাশনা ও পরিবেশনা নীতিমালা এবং শর্তাবলীতে সম্মতি প্রদান করুন।',
            'terms_agreed.required'     => 'প্রকাশনা ও পরিবেশনা নীতিমালা এবং শর্তাবলীতে সম্মতি প্রদান করুন।',
        ];

        $validated = $request->validate($rules, $messages);

        // 3. Clean and Merge Phone Number with Country Code
        $countryCode = trim((string) ($validated['country_code'] ?? '+880'));
        if (!str_starts_with($countryCode, '+')) {
            $countryCode = '+' . ltrim($countryCode, '+');
        }
        $rawPhone = trim((string) $validated['phone']);

        if (str_starts_with($rawPhone, '+')) {
            $fullPhone = preg_replace('/[^\d+]/', '', $rawPhone);
        } else {
            $digitsOnly = preg_replace('/\D/', '', $rawPhone);
            if ($countryCode === '+880' && str_starts_with($digitsOnly, '0')) {
                $digitsOnly = substr($digitsOnly, 1);
            }
            $fullPhone = $countryCode . $digitsOnly;
        }

        // 4. Handle Logo Upload
        $logoPath = null;
        if ($request->hasFile('logo') && $request->file('logo')->isValid()) {
            $logoFile = $request->file('logo');
            $ext = $logoFile->getClientOriginalExtension() ?: 'jpg';
            $filename = 'pub_' . Str::slug($validated['name']) . '_' . time() . '.' . $ext;
            $logoPath = $logoFile->storeAs('publishers', $filename, 'public');
        }

        // 5. Extended Metadata & Social Links
        $categories = $validated['categories'] ?? [];
        $address = !empty($validated['address']) ? trim($validated['address']) : null;
        $country = !empty($validated['country']) ? trim($validated['country']) : 'বাংলাদেশ';
        $website = !empty($validated['website']) ? trim($validated['website']) : null;
        $tradeLicense = !empty($validated['trade_license_no']) ? trim($validated['trade_license_no']) : null;
        $estYear = !empty($validated['established_year']) ? (int)$validated['established_year'] : null;
        $description = !empty($validated['description']) ? trim($validated['description']) : null;

        $socialLinks = array_filter([
            'website'  => $website,
            'address'  => $address,
        ]);

        $regData = [
            'company_name'      => $validated['name'],
            'contact_person'    => $validated['contact_person'],
            'trade_license_no'  => $tradeLicense,
            'established_year'  => $estYear,
            'address'           => $address,
            'country'           => $country,
            'categories'        => $categories,
            'registered_ip'     => $request->ip(),
            'registered_at'     => now()->toIso8601String(),
        ];

        // 6. User Account Creation / Upgrade
        $user = Auth::user();
        if (!$user) {
            $existingUser = User::where('email', strtolower($validated['email']))
                ->orWhere('phone', $fullPhone)
                ->first();

            if ($existingUser) {
                $user = $existingUser;
                if ($user->role === User::ROLE_CUSTOMER || $user->role === User::ROLE_BUYER || empty($user->role)) {
                    $user->role = User::ROLE_PUBLISHER;
                }
                $user->reg_type = User::ROLE_PUBLISHER;
                $user->reg_status = User::STATUS_PENDING;
                $user->reg_data = array_merge($user->reg_data ?? [], $regData);
                if ($logoPath && empty($user->avatar)) {
                    $user->avatar = $logoPath;
                }
                if (!empty($validated['password'])) {
                    $user->password = Hash::make($validated['password']);
                }
                $user->is_active = false;
                $user->save();
            } else {
                $user = User::create([
                    'name'       => $validated['name'],
                    'email'      => strtolower($validated['email']),
                    'phone'      => $fullPhone,
                    'password'   => Hash::make($validated['password'] ?? Str::random(12)),
                    'role'       => User::ROLE_PUBLISHER,
                    'reg_type'   => User::ROLE_PUBLISHER,
                    'reg_status' => User::STATUS_PENDING,
                    'avatar'     => $logoPath,
                    'is_active'  => false,
                    'reg_data'   => $regData,
                ]);
            }
        } else {
            // Logged in user registering publisher portal
            if ($user->role === User::ROLE_CUSTOMER || $user->role === User::ROLE_BUYER || empty($user->role)) {
                $user->role = User::ROLE_PUBLISHER;
            }
            $user->reg_type = User::ROLE_PUBLISHER;
            $user->reg_status = User::STATUS_PENDING;
            $user->is_active = false;
            $user->reg_data = array_merge($user->reg_data ?? [], $regData);
            if ($logoPath) {
                $user->avatar = $logoPath;
            }
            $user->save();
        }

        // 7. Publisher Model Creation / Sync (is_active = false until admin approval)
        $publisher = Publisher::where('name', $validated['name'])
            ->orWhere('email', strtolower($validated['email']))
            ->orWhere('phone', $fullPhone)
            ->first();

        $slug = Str::slug($validated['name']) ?: ('pub-' . Str::random(6));
        $baseSlug = $slug;
        $counter = 1;
        while (Publisher::where('slug', $slug)->when($publisher, fn($q) => $q->where('id', '!=', $publisher->id))->exists()) {
            $slug = $baseSlug . '-' . (++$counter);
        }

        if ($publisher) {
            $publisher->update([
                'name'         => $validated['name'],
                'description'  => $description ?: $publisher->description,
                'logo'         => $logoPath ?: $publisher->logo,
                'email'        => strtolower($validated['email']),
                'phone'        => $fullPhone,
                'website'      => $website ?: $publisher->website,
                'address'      => $address ?: $publisher->address,
                'country'      => $country ?: $publisher->country,
                'social_links' => $socialLinks,
                'is_active'    => false,
                'is_verified'  => false,
            ]);
        } else {
            $publisher = Publisher::create([
                'name'         => $validated['name'],
                'slug'         => $slug,
                'description'  => $description,
                'logo'         => $logoPath,
                'email'        => strtolower($validated['email']),
                'phone'        => $fullPhone,
                'website'      => $website,
                'address'      => $address,
                'country'      => $country,
                'social_links' => $socialLinks,
                'is_active'    => false,
                'is_verified'  => false,
            ]);
        }

        // Send registration confirmation SMS
        try {
            $pubMsg = "আইডিয়া প্রকাশন — সম্মানিত প্রকাশক ({$validated['name']}), প্রকাশনী হিসেবে আপনার রেজিস্ট্রেশন আবেদন সফলভাবে জমা হয়েছে। অ্যাডমিন অনুমোদনের পর পাবলিশার পোর্টাল সক্রিয় হবে। হেল্পলাইন: 01726976982";
            \App\Services\SmsService::send($fullPhone, $pubMsg);
        } catch (\Throwable $smsEx) {
            \Illuminate\Support\Facades\Log::warning("Publisher registration SMS notice: " . $smsEx->getMessage());
        }

        $registrationSummary = [
            'user_id'        => $user->id,
            'name'           => $user->name,
            'email'          => $user->email,
            'phone'          => $user->phone,
            'type'           => 'publisher',
            'type_label'     => 'প্রকাশনী ও কোম্পানি',
            'is_active'      => false,
            'reg_status'     => 'pending',
            'created_at'     => now()->format('d M, Y - h:i A'),
            'publisher_name' => $validated['name'],
        ];
        session(['registration_summary' => $registrationSummary]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success'      => true,
                'message'      => 'অভিনন্দন! প্রকাশনী হিসেবে আপনার রেজিস্ট্রেশন আবেদন সফলভাবে জমা হয়েছে। অ্যাডমিন অনুমোদন সম্পন্ন হলে পাবলিশার পোর্টাল সক্রিয় হবে।',
                'redirect_url' => route('register.success'),
            ]);
        }

        return redirect()->route('register.success')
            ->with('success', 'অভিনন্দন! প্রকাশনী হিসেবে আপনার আবেদনটি গৃহীত হয়েছে। অ্যাডমিন অনুমোদনের পর পাবলিশার পোর্টাল সক্রিয় হবে।');
    }

    /**
     * Master Country Dial Codes & ISO Map with Flags and Bengali Names.
     */
    protected function getAllCountryCodes(): array
    {
        return [
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
