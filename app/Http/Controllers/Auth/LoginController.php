<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\LoginSecurityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Visual Sign & Image Challenge Categories for Human Verification
     */
    private static array $challengeCategories = [
        'traffic_signs' => [
            'key'   => 'traffic_signs',
            'title' => 'ট্রাফিক সাইন / Traffic Signs',
            'desc'  => 'ট্রাফিক লাইট বা রোডের সংকেত চিহ্ন',
            'icon'  => 'fa-traffic-light',
            'color' => '#dc2626',
            'items' => [
                ['name' => 'Traffic Light', 'bn' => 'ট্রাফিক লাইট', 'icon' => 'fa-traffic-light'],
                ['name' => 'Road Sign', 'bn' => 'রোড সাইন', 'icon' => 'fa-signs-post'],
                ['name' => 'Parking Sign', 'bn' => 'পার্কিং সাইন', 'icon' => 'fa-square-parking'],
                ['name' => 'Road Barrier', 'bn' => 'রোড ব্যারিয়ার', 'icon' => 'fa-road-barrier'],
                ['name' => 'Turn Sign', 'bn' => 'টার্ন সংকেত', 'icon' => 'fa-arrow-turn-down'],
                ['name' => 'No Entry', 'bn' => 'নো এন্ট্রি', 'icon' => 'fa-ban'],
            ],
        ],
        'vehicles' => [
            'key'   => 'vehicles',
            'title' => 'যানবাহন বা গাড়ি / Vehicles & Cars',
            'desc'  => 'গাড়ি, বাস, ট্রাক বা যানবাহন',
            'icon'  => 'fa-car-side',
            'color' => '#0284c7',
            'items' => [
                ['name' => 'Sedan Car', 'bn' => 'প্রাইভেট কার', 'icon' => 'fa-car-side'],
                ['name' => 'Public Bus', 'bn' => 'পাবলিক বাস', 'icon' => 'fa-bus'],
                ['name' => 'Delivery Truck', 'bn' => 'মালবাহী ট্রাক', 'icon' => 'fa-truck'],
                ['name' => 'Bicycle', 'bn' => 'বাইসাইকেল', 'icon' => 'fa-bicycle'],
                ['name' => 'Motorcycle', 'bn' => 'মোটরসাইকেল', 'icon' => 'fa-motorcycle'],
                ['name' => 'Airplane', 'bn' => 'বিমান', 'icon' => 'fa-plane'],
            ],
        ],
        'books' => [
            'key'   => 'books',
            'title' => 'বই ও পড়াশোনা / Books & Study',
            'desc'  => 'বই, লাইব্রেরি বা পড়ার সামগ্রী',
            'icon'  => 'fa-book-open',
            'color' => '#16a34a',
            'items' => [
                ['name' => 'Open Book', 'bn' => 'খোলা বই', 'icon' => 'fa-book-open'],
                ['name' => 'Book Stack', 'bn' => 'বইয়ের স্তূপ', 'icon' => 'fa-book-bookmark'],
                ['name' => 'Graduation Cap', 'bn' => 'গ্রাজুয়েশন ক্যাপ', 'icon' => 'fa-graduation-cap'],
                ['name' => 'Fountain Pen', 'bn' => 'কলম', 'icon' => 'fa-pen-fancy'],
                ['name' => 'Bookmark', 'bn' => 'বুকমার্ক', 'icon' => 'fa-bookmark'],
                ['name' => 'Journal', 'bn' => 'পত্রিকা', 'icon' => 'fa-newspaper'],
            ],
        ],
        'nature' => [
            'key'   => 'nature',
            'title' => 'গাছ ও প্রকৃতি / Trees & Nature',
            'desc'  => 'গাছ, পাতা বা প্রাকৃতিক উপাদান',
            'icon'  => 'fa-tree',
            'color' => '#059669',
            'items' => [
                ['name' => 'Green Tree', 'bn' => 'বড় গাছ', 'icon' => 'fa-tree'],
                ['name' => 'Seedling', 'bn' => 'চারাগাছ', 'icon' => 'fa-seedling'],
                ['name' => 'Plant Leaf', 'bn' => 'গাছের পাতা', 'icon' => 'fa-leaf'],
                ['name' => 'Flower', 'bn' => 'পাপড়ি/ফুল', 'icon' => 'fa-spa'],
                ['name' => 'Mountain', 'bn' => 'পাহাড় ও সূর্য', 'icon' => 'fa-mountain-sun'],
                ['name' => 'Sun & Cloud', 'bn' => 'মেঘ ও রোদ', 'icon' => 'fa-cloud-sun'],
            ],
        ],
        'animals' => [
            'key'   => 'animals',
            'title' => 'পশুপাখি / Animals & Birds',
            'desc'  => 'বিড়াল, কুকুর বা জীবজন্তু',
            'icon'  => 'fa-paw',
            'color' => '#d97706',
            'items' => [
                ['name' => 'Cat', 'bn' => 'বিড়াল', 'icon' => 'fa-cat'],
                ['name' => 'Dog', 'bn' => 'কুকুর', 'icon' => 'fa-dog'],
                ['name' => 'Bird', 'bn' => 'পাখি', 'icon' => 'fa-crow'],
                ['name' => 'Fish', 'bn' => 'মাছ', 'icon' => 'fa-fish'],
                ['name' => 'Horse', 'bn' => 'ঘোড়া', 'icon' => 'fa-horse'],
                ['name' => 'Dove Bird', 'bn' => 'ঘুঘু পাখি', 'icon' => 'fa-dove'],
            ],
        ],
        'food' => [
            'key'   => 'food',
            'title' => 'খাবার ও পানীয় / Food & Beverages',
            'desc'  => 'চা, কফি বা খাবার সামগ্রী',
            'icon'  => 'fa-mug-hot',
            'color' => '#ea580c',
            'items' => [
                ['name' => 'Hot Tea / Coffee', 'bn' => 'গরম চা/কফি', 'icon' => 'fa-mug-hot'],
                ['name' => 'Fresh Apple', 'bn' => 'তাজা আপেল', 'icon' => 'fa-apple-whole'],
                ['name' => 'Burger', 'bn' => 'বার্গার', 'icon' => 'fa-burger'],
                ['name' => 'Pizza', 'bn' => 'পিজ্জা', 'icon' => 'fa-pizza-slice'],
                ['name' => 'Ice Cream', 'bn' => 'আইসক্রিম', 'icon' => 'fa-ice-cream'],
                ['name' => 'Food Bowl', 'bn' => 'খাবারের বাটি', 'icon' => 'fa-bowl-food'],
            ],
        ],
        'tech' => [
            'key'   => 'tech',
            'title' => 'ডিজিটাল ডিভাইস / Tech Devices',
            'desc'  => 'ল্যাপটপ, কম্পিউটার বা গ্যাজেট',
            'icon'  => 'fa-laptop',
            'color' => '#4f46e5',
            'items' => [
                ['name' => 'Laptop Computer', 'bn' => 'ল্যাপটপ', 'icon' => 'fa-laptop'],
                ['name' => 'Desktop PC', 'bn' => 'ডেস্কটপ', 'icon' => 'fa-desktop'],
                ['name' => 'Smartphone', 'bn' => 'স্মার্টফোন', 'icon' => 'fa-mobile-screen-button'],
                ['name' => 'Headphones', 'bn' => 'হেডফোন', 'icon' => 'fa-headphones'],
                ['name' => 'Camera', 'bn' => 'ডিজিটাল ক্যামেরা', 'icon' => 'fa-camera'],
                ['name' => 'Keyboard', 'bn' => 'কীবোর্ড', 'icon' => 'fa-keyboard'],
            ],
        ],
    ];

    /**
     * Generate visual 3x3 challenge payload.
     */
    public static function createVisualChallenge(): array
    {
        $categoryKeys = array_keys(self::$challengeCategories);
        $targetKey = $categoryKeys[array_rand($categoryKeys)];
        $targetCategory = self::$challengeCategories[$targetKey];

        // 1. Pick 3 target items
        $targetItems = $targetCategory['items'];
        shuffle($targetItems);
        $selectedTargets = array_slice($targetItems, 0, 3);

        // 2. Pick 6 decoy items from other categories
        $decoyPool = [];
        foreach (self::$challengeCategories as $key => $cat) {
            if ($key !== $targetKey) {
                foreach ($cat['items'] as $item) {
                    $decoyPool[] = $item;
                }
            }
        }
        shuffle($decoyPool);
        $selectedDecoys = array_slice($decoyPool, 0, 6);

        // 3. Merge & Shuffle tiles
        $allTiles = [];
        foreach ($selectedTargets as $item) {
            $allTiles[] = [
                'icon'     => $item['icon'],
                'label'    => $item['bn'],
                'is_match' => true,
            ];
        }
        foreach ($selectedDecoys as $item) {
            $allTiles[] = [
                'icon'     => $item['icon'],
                'label'    => $item['bn'],
                'is_match' => false,
            ];
        }
        shuffle($allTiles);

        $solutionIndices = [];
        $publicTiles = [];
        foreach ($allTiles as $index => $tile) {
            if ($tile['is_match']) {
                $solutionIndices[] = $index;
            }
            $publicTiles[] = [
                'index' => $index,
                'icon'  => $tile['icon'],
                'label' => $tile['label'],
            ];
        }

        sort($solutionIndices);
        $token = Str::random(32);

        Session::put('login_visual_challenge', [
            'token'        => $token,
            'target_key'   => $targetKey,
            'target_title' => $targetCategory['title'],
            'target_desc'  => $targetCategory['desc'],
            'target_icon'  => $targetCategory['icon'],
            'target_color' => $targetCategory['color'],
            'solution'     => $solutionIndices,
            'verified'     => false,
            'time'         => microtime(true),
        ]);

        return [
            'token'        => $token,
            'target_title' => $targetCategory['title'],
            'target_desc'  => $targetCategory['desc'],
            'target_icon'  => $targetCategory['icon'],
            'target_color' => $targetCategory['color'],
            'tiles'        => $publicTiles,
        ];
    }

    /**
     * Show the login form with anti-caching headers and dynamic bot security challenge.
     */
    public function showLoginForm(Request $request)
    {
        // 1. Math bot challenge for standard lightweight check
        $num1 = random_int(2, 9);
        $num2 = random_int(1, 8);
        Session::put('login_bot_challenge', [
            'num1'   => $num1,
            'num2'   => $num2,
            'answer' => $num1 + $num2,
            'time'   => microtime(true),
        ]);

        // 2. Check IP status and whether visual sign challenge is required
        $ipStatus = LoginSecurityLog::checkIpStatus($request->ip());
        $requiresVisualChallenge = (bool)($ipStatus['requires_visual_challenge'] ?? false);
        $visualChallenge = null;

        if ($requiresVisualChallenge) {
            $visualChallenge = self::createVisualChallenge();
        }

        return response()
            ->view('auth.login', [
                'botNum1'                 => $num1,
                'botNum2'                 => $num2,
                'requiresVisualChallenge' => $requiresVisualChallenge,
                'visualChallenge'         => $visualChallenge,
                'ipStatus'                => $ipStatus,
            ])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
    }

    /**
     * AJAX endpoint to regenerate human bot security challenge numbers
     */
    public function refreshBotChallenge(Request $request): JsonResponse
    {
        $num1 = random_int(2, 9);
        $num2 = random_int(1, 8);
        Session::put('login_bot_challenge', [
            'num1'   => $num1,
            'num2'   => $num2,
            'answer' => $num1 + $num2,
            'time'   => microtime(true),
        ]);

        return response()->json([
            'success'  => true,
            'num1'     => $num1,
            'num2'     => $num2,
            'equation' => "{$num1} + {$num2} = ?",
        ]);
    }

    /**
     * AJAX endpoint to fetch a fresh Visual Sign Challenge
     */
    public function getVisualChallenge(Request $request): JsonResponse
    {
        $challenge = self::createVisualChallenge();

        return response()->json([
            'success'   => true,
            'challenge' => $challenge,
        ]);
    }

    /**
     * AJAX endpoint to verify selected tiles for the Visual Sign Challenge
     */
    public function verifyVisualChallenge(Request $request): JsonResponse
    {
        $request->validate([
            'selected' => 'required|array',
            'token'    => 'nullable|string',
        ]);

        $storedChallenge = Session::get('login_visual_challenge');
        if (!$storedChallenge) {
            return response()->json([
                'success' => false,
                'message' => 'চ্যালেঞ্জ মেয়াদোত্তীর্ণ হয়েছে। অনুগ্রহ করে নতুন চ্যালেঞ্জ নিন।',
                'new_challenge' => self::createVisualChallenge(),
            ], 422);
        }

        $userSelected = array_map('intval', (array)$request->input('selected', []));
        sort($userSelected);
        $solution = (array)($storedChallenge['solution'] ?? []);
        sort($solution);

        if ($userSelected === $solution && count($solution) > 0) {
            // Passed human verification!
            $storedChallenge['verified'] = true;
            $storedChallenge['verified_at'] = microtime(true);
            Session::put('login_visual_challenge', $storedChallenge);

            LoginSecurityLog::recordChallengePassed($request->ip());

            return response()->json([
                'success' => true,
                'message' => 'মানুষ প্রমাণ যাচাই সফল হয়েছে! এখন পাসওয়ার্ড দিয়ে লগইন করুন।',
            ]);
        }

        // Failed verification -> Generate new challenge
        $newChallenge = self::createVisualChallenge();

        return response()->json([
            'success'       => false,
            'message'       => 'সঠিক ছবিগুলো চিহ্নিত করা হয়নি! অনুগ্রহ করে নতুন ছবিতে সাইনগুলো চিহ্নিত করুন।',
            'new_challenge' => $newChallenge,
        ], 422);
    }

    public function login(Request $request)
    {
        // 1. Invisible Honeypot Bot Check
        if ($request->filled('website_url_hp') || $request->filled('b_check_field')) {
            throw ValidationException::withMessages([
                'email' => 'স্বয়ংক্রিয় রোবট কার্যকলাপ সনাক্ত হয়েছে। অনুগ্রহ করে সাধারণ ব্রাউজার ব্যবহার করুন।',
            ]);
        }

        $loginInput = trim((string) ($request->input('email') ?? $request->input('username') ?? $request->input('login') ?? ''));
        $password   = (string) $request->input('password', '');

        if ($loginInput === '' || $password === '') {
            throw ValidationException::withMessages([
                'email' => 'ইমেইল/ইউজারনেম এবং পাসওয়ার্ড দিন।',
            ]);
        }

        // 2. Intelligent IP Security & Block Check
        $ipStatus = LoginSecurityLog::checkIpStatus($request->ip());
        if ($ipStatus['status'] === 'blocked') {
            throw ValidationException::withMessages([
                'email' => "নিরাপত্তা সতর্কতা: ভুল পাসওয়ার্ড দিয়ে ৫বার ব্যর্থ চেষ্টার কারণে এই আইপি অ্যাড্রেসটি ({$request->ip()}) সাময়িক অটো-ব্লক করা হয়েছে। অ্যাকাউন্ট ফিরে পেতে অ্যাডমিনের সাথে যোগাযোগ করুন।",
            ]);
        }
        if ($ipStatus['status'] === 'locked') {
            $remMin = $ipStatus['remaining_minutes'] ?? 10;
            throw ValidationException::withMessages([
                'email' => "ভুল পাসওয়ার্ড দিয়ে ৩বার চেষ্টা করা হয়েছে! নিরাপত্তার স্বার্থে আপনার আইপি সাময়িক লক করা হয়েছে। অনুগ্রহ করে আরও {$remMin} মিনিট পর ছবিতে সাইন চিহ্নিত করে আবার চেষ্টা করুন।",
            ]);
        }

        // 3. Visual Sign Challenge Check for 3+ failed attempts or security issue
        $mustVerifyVisual = LoginSecurityLog::requiresHumanChallenge($request->ip());
        if ($mustVerifyVisual) {
            $challengeSession = Session::get('login_visual_challenge');
            $isVerified = !empty($challengeSession['verified']);

            // Direct check if submitted in form payload
            if (!$isVerified && $request->filled('visual_selected_indices')) {
                $rawIndices = json_decode((string)$request->input('visual_selected_indices'), true);
                if (is_array($rawIndices)) {
                    $userSelected = array_map('intval', $rawIndices);
                    sort($userSelected);
                    $solution = (array)($challengeSession['solution'] ?? []);
                    sort($solution);
                    if ($userSelected === $solution && count($solution) > 0) {
                        $isVerified = true;
                        LoginSecurityLog::recordChallengePassed($request->ip());
                    }
                }
            }

            if (!$isVerified) {
                // Regenerate challenge if not valid
                self::createVisualChallenge();
                throw ValidationException::withMessages([
                    'visual_challenge' => 'নিরাপত্তা সতর্কতা: আপনি ৩ বার ভুল পাসওয়ার্ড দিয়েছেন। মানুষের উপস্থিতি প্রমাণ করতে নিচের ছবিতে সঠিক সাইনগুলো ক্লিক করে যাচাই সম্পন্ন করুন।',
                ]);
            }
        }

        // 4. Brute-Force Rate Limiting (Max 5 attempts per 60 seconds)
        $throttleKey = 'login_attempt:' . sha1($request->ip() . '|' . strtolower($loginInput));
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'email' => "খুব বেশি ভুল লগইন চেষ্টা করা হয়েছে! আপনার অ্যাকাউন্টের সুরক্ষায় লগইন সাময়িক লক করা হয়েছে। অনুগ্রহ করে {$seconds} সেকেন্ড পর আবার চেষ্টা করুন।",
            ]);
        }

        // 5. Human Bot Math Verification Check (if not in visual challenge mode)
        if (!$mustVerifyVisual) {
            $attempts = RateLimiter::attempts($throttleKey);
            if ($attempts >= 2 || $request->filled('bot_answer')) {
                $challenge = Session::get('login_bot_challenge');
                $userAns = trim((string) $request->input('bot_answer'));
                if (!$challenge || (int)$userAns !== (int)($challenge['answer'] ?? -999)) {
                    RateLimiter::hit($throttleKey, 60);
                    throw ValidationException::withMessages([
                        'bot_answer' => 'রোবট সুরক্ষা যাচাইকরণ (ক্যাপচা) উত্তর সঠিক হয়নি। পুনরায় চেষ্টা করুন।',
                    ]);
                }
            }
        }

        try {
            // Multi-format normalization (Bangla to English digits, lowercase, trimmed)
            $bnDigits = ['০','১','২','৩','৪','৫','৬','৭','৮','৯'];
            $enDigits = ['0','1','2','3','4','5','6','7','8','9'];
            $normalizedInput = str_replace($bnDigits, $enDigits, $loginInput);
            $cleanLower = strtolower($normalizedInput);
            $rawDigitsOnly = preg_replace('/[^\d]/', '', $normalizedInput);

            // Find user candidates matching email, phone (flexible formats), name, or admin role
            $candidates = \App\Models\User::where(function ($query) use ($loginInput, $normalizedInput, $cleanLower, $rawDigitsOnly) {
                // 1. Email matching (exact, normalized, lowercase)
                $query->where('email', $loginInput)
                    ->orWhere('email', $normalizedInput)
                    ->orWhereRaw('LOWER(email) = ?', [$cleanLower])
                    ->orWhereRaw('email LIKE ?', [$loginInput . '@%']);

                // 2. Full Name / Display Name matching
                $query->orWhere('name', $loginInput)
                    ->orWhere('name', $normalizedInput)
                    ->orWhereRaw('LOWER(name) = ?', [strtolower($loginInput)]);

                // 3. Mobile Phone matching (Bangla/English, with or without +88 / 880 / 0 prefix)
                $query->orWhere('phone', $loginInput)
                    ->orWhere('phone', $normalizedInput);

                if (!empty($rawDigitsOnly) && strlen($rawDigitsOnly) >= 8) {
                    $last10 = substr($rawDigitsOnly, -10);
                    $query->orWhere('phone', 'LIKE', '%' . $last10)
                        ->orWhere('phone', '0' . $last10)
                        ->orWhere('phone', '+880' . $last10)
                        ->orWhere('phone', '880' . $last10);
                }

                // 4. Admin identifier fallback: allow logging in with 'admin' or ADMIN_USERNAME even if real name is set
                $adminUsername = strtolower(env('ADMIN_USERNAME', 'admin'));
                if ($cleanLower === 'admin' || $cleanLower === $adminUsername) {
                    $query->orWhere('role', \App\Models\User::ROLE_ADMIN);
                }
            })->get();

            $matchedUser = null;
            foreach ($candidates as $candidate) {
                if (Hash::check($password, $candidate->password)) {
                    $matchedUser = $candidate;
                    break;
                }
            }

            if ($matchedUser) {
                // Clear Rate Limiter & IP security attempts on successful login
                RateLimiter::clear($throttleKey);
                LoginSecurityLog::recordSuccessfulLogin($request->ip());
                Session::forget('login_bot_challenge');
                Session::forget('login_visual_challenge');

                // Check registration approval for vendor/author/seller/publisher
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

                // Check active status
                if (isset($matchedUser->is_active) && ! $matchedUser->is_active) {
                    throw ValidationException::withMessages([
                        'email' => 'আপনার অ্যাকাউন্টটি নিষ্ক্রিয় করা আছে। কর্তৃপক্ষের সাথে যোগাযোগ করুন।',
                    ]);
                }

                Auth::login($matchedUser, $request->boolean('remember'));
                $request->session()->regenerate();

                // If user logged in via Admin One-Time Password / Auto-generated password, redirect to reset password
                if (!empty($matchedUser->must_change_password)) {
                    return redirect()->route('my-account')->with('warning', 'আপনি অ্যাডমিন কর্তৃক তৈরি নতুন পাসওয়ার্ড/ওটিপি দিয়ে লগইন করেছেন। নিরাপত্তার স্বার্থে অনুগ্রহ করে অবিলম্বে আপনার প্রোফাইল থেকে একটি স্থায়ী পাসওয়ার্ড সেট করুন।');
                }

                // Redirect based on role
                if ($matchedUser->isAdmin()) {
                    return redirect()->intended(route('admin.dashboard'));
                }
                if ($matchedUser->isSeller() || $matchedUser->isSubAdmin() || $matchedUser->reg_type === 'seller') {
                    return redirect()->intended(route('subadmin.dashboard'));
                }
                if ($matchedUser->isPublisher() || $matchedUser->reg_type === 'publisher') {
                    return redirect()->intended(route('publisher.dashboard'));
                }
                if ($matchedUser->isAuthor() || $matchedUser->reg_type === 'author') {
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

        // Record failed attempt in RateLimiter and LoginSecurityLog
        RateLimiter::hit($throttleKey, 60);
        $failResult = LoginSecurityLog::recordFailedAttempt($request->ip(), $loginInput);

        // Reset visual verification after a failed login chance
        if (Session::has('login_visual_challenge')) {
            $s = Session::get('login_visual_challenge');
            $s['verified'] = false;
            Session::put('login_visual_challenge', $s);
        }

        throw ValidationException::withMessages([
            'email' => $failResult['message'] ?? 'ইমেইল/ইউজারনেম বা পাসওয়ার্ড সঠিক নয়।',
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
