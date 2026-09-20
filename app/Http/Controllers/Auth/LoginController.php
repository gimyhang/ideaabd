<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\LoginSecurityLog;
use App\Services\CaptchaService;
use App\Services\RecaptchaService;
use App\Services\SecurityAuditService;
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

        // 2. Check IP status and whether captcha challenge is required
        $ipStatus = LoginSecurityLog::checkIpStatus($request->ip());
        $requiresCaptcha = (bool)($ipStatus['requires_captcha'] ?? $ipStatus['requires_visual_challenge'] ?? false) || Session::get('show_captcha', false);
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
                'requiresCaptcha'         => $requiresCaptcha,
                'showCaptcha'             => $requiresCaptcha,
                'recaptchaSiteKey'        => RecaptchaService::getSiteKey(),
                'recaptchaEnabled'        => RecaptchaService::isEnabled(),
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
        $isAjax = $request->ajax() || $request->wantsJson();

        // 1. Invisible Honeypot Bot Check
        if ($request->filled('website_url_hp') || $request->filled('b_check_field')) {
            $msg = 'স্বয়ংক্রিয় রোবট কার্যকলাপ সনাক্ত হয়েছে। অনুগ্রহ করে সাধারণ ব্রাউজার ব্যবহার করুন।';
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            throw ValidationException::withMessages(['email' => $msg]);
        }

        $loginInput = trim((string) ($request->input('email') ?? $request->input('username') ?? $request->input('login') ?? ''));
        $password   = (string) $request->input('password', '');

        if ($loginInput === '' || $password === '') {
            $msg = 'ইমেইল/ইউজারনেম এবং পাসওয়ার্ড দিন।';
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            throw ValidationException::withMessages(['email' => $msg]);
        }

        // 2. Intelligent IP Security & Progressive Delay Check
        $ipStatus = LoginSecurityLog::checkIpStatus($request->ip(), $loginInput);
        if ($ipStatus['status'] === 'blocked') {
            $msg = "নিরাপত্তা সতর্কতা: একাধিক ব্যর্থ চেষ্টার কারণে এই আইপি অ্যাড্রেসটি ({$request->ip()}) ব্লক করা হয়েছে। অ্যাকাউন্ট ফিরে পেতে অ্যাডমিনের সাথে যোগাযোগ করুন।";
            if ($isAjax) {
                return response()->json(['success' => false, 'is_blocked' => true, 'message' => $msg], 422);
            }
            throw ValidationException::withMessages(['email' => $msg]);
        }

        if ($ipStatus['status'] === 'locked') {
            $remainingSec = $ipStatus['remaining_seconds'] ?? 30;
            $msg = ($remainingSec >= 60)
                ? "একাধিক ভুল চেষ্টার কারণে লগইন সাময়িক স্থগিত করা হয়েছে। অনুগ্রহ করে " . ceil($remainingSec / 60) . " মিনিট পর আবার চেষ্টা করুন।"
                : "একাধিক ভুল চেষ্টার কারণে লগইন সাময়িক স্থগিত করা হয়েছে। অনুগ্রহ করে {$remainingSec} সেকেন্ড পর আবার চেষ্টা করুন।";
            if ($isAjax) {
                return response()->json([
                    'success'           => false,
                    'is_locked'         => true,
                    'remaining_seconds' => $remainingSec,
                    'show_captcha'      => true,
                    'message'           => $msg,
                ], 429);
            }
            throw ValidationException::withMessages(['email' => $msg]);
        }

        // 3. CAPTCHA Verification Check (Triggered after 3+ failed attempts)
        $mustVerifyCaptcha = LoginSecurityLog::requiresCaptcha($request->ip(), $loginInput);
        if ($mustVerifyCaptcha) {
            $captchaToken = (string) ($request->input('captcha_token') ?? $request->input('g-recaptcha-response') ?? $request->input('recaptcha_token') ?? '');
            $captchaCode = (string) $request->input('captcha_code', '');

            $isVerified = false;
            if (!empty($captchaToken) && !empty($captchaCode)) {
                $verifyRes = app(CaptchaService::class)->verify($captchaToken, $captchaCode, $request->ip());
                $isVerified = (bool) ($verifyRes['success'] ?? false);
            } elseif (!empty($captchaToken)) {
                $isVerified = RecaptchaService::verify($captchaToken, $request->ip());
            }

            if (!$isVerified) {
                Session::flash('show_captcha', true);
                $msg = 'নিরাপত্তা সতর্কতা: একাধিক ব্যর্থ লগইন চেষ্টার কারণে CAPTCHA যাচাইকরণ আবশ্যক। সঠিক কোড দিন।';
                if ($isAjax) {
                    return response()->json([
                        'success'          => false,
                        'show_captcha'     => true,
                        'captcha_required' => true,
                        'message'          => $msg,
                    ], 422);
                }
                throw ValidationException::withMessages(['email' => $msg]);
            }

            LoginSecurityLog::recordChallengePassed($request->ip());
        }

        // 4. Brute-Force Rate Limiting Throttling (Max 10 attempts per minute)
        $throttleKey = 'login_attempt:' . sha1($request->ip() . '|' . strtolower($loginInput));
        if (RateLimiter::tooManyAttempts($throttleKey, 10)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $msg = "খুব বেশি ভুল লগইন চেষ্টা করা হয়েছে! অনুগ্রহ করে {$seconds} সেকেন্ড পর আবার চেষ্টা করুন।";
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => $msg], 429);
            }
            throw ValidationException::withMessages(['email' => $msg]);
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

                // 4. Admin identifier fallback: allow logging in with 'admin', ADMIN_USERNAME, or admin emails
                $adminUsername = strtolower(env('ADMIN_USERNAME', 'admin'));
                if ($cleanLower === 'admin' || $cleanLower === $adminUsername || in_array($cleanLower, ['ideapbd@gmail.com', 'adideabd@gmail.com', 'admin@ideaabd.com'], true)) {
                    $query->orWhere('role', \App\Models\User::ROLE_ADMIN)
                          ->orWhere('id', 1);
                }
            })
            ->orderByRaw("CASE 
                WHEN LOWER(email) = ? THEN 1 
                WHEN phone = ? THEN 2 
                WHEN role = 'admin' THEN 3 
                ELSE 4 
            END", [$cleanLower, $normalizedInput])
            ->orderByDesc('is_active')
            ->orderByDesc('id')
            ->get();

            $matchedUser = null;
            foreach ($candidates as $candidate) {
                if (Hash::check($password, $candidate->password)) {
                    $matchedUser = $candidate;
                    break;
                }
            }

            if ($matchedUser) {
                // Transparently rehash password to Argon2id if needed
                if (Hash::needsRehash($matchedUser->password)) {
                    $matchedUser->password = Hash::make($password);
                    $matchedUser->save();
                }

                // Clear Rate Limiter & IP security attempts on successful login
                RateLimiter::clear($throttleKey);
                LoginSecurityLog::recordSuccessfulLogin($request->ip(), $loginInput);
                SecurityAuditService::loginSuccess($matchedUser->id, $loginInput);

                Session::forget('login_bot_challenge');
                Session::forget('login_visual_challenge');
                Session::forget('show_captcha');

                // Check active status & pending approval for non-customer roles
                $roleName = $matchedUser->getRoleDisplayName();
                $isPendingAccount = ($matchedUser->reg_status === 'pending') || in_array($matchedUser->role, ['author', 'publisher', 'seller', 'vendor'], true);

                if (isset($matchedUser->is_active) && ! $matchedUser->is_active) {
                    if ($isPendingAccount) {
                        $msg = "আপনার {$roleName} অ্যাকাউন্টটি অ্যাডমিন অনুমোদনের অপেক্ষায় রয়েছে। অ্যাডমিন কর্তৃক অনুমোদিত হওয়ার পর আপনি লগইন করতে পারবেন।";
                    } else {
                        $msg = 'আপনার অ্যাকাউন্টটি নিষ্ক্রিয় করা আছে। কর্তৃপক্ষের সাথে যোগাযোগ করুন।';
                    }
                    if ($isAjax) {
                        return response()->json(['success' => false, 'message' => $msg, 'pending_approval' => true], 422);
                    }
                    throw ValidationException::withMessages(['email' => $msg]);
                }

                if ($matchedUser->reg_status === 'pending' && in_array($matchedUser->role, ['author', 'publisher', 'seller', 'vendor'], true)) {
                    $msg = "আপনার {$roleName} অ্যাকাউন্টটি অ্যাডমিন অনুমোদনের অপেক্ষায় রয়েছে। অ্যাডমিন কর্তৃক অনুমোদিত হওয়ার পর আপনি লগইন করতে পারবেন।";
                    if ($isAjax) {
                        return response()->json(['success' => false, 'message' => $msg, 'pending_approval' => true], 422);
                    }
                    throw ValidationException::withMessages(['email' => $msg]);
                }

                Auth::login($matchedUser, $request->boolean('remember'));
                $request->session()->regenerate();

                $redirectUrl = route('home');

                if ($isAjax) {
                    return response()->json([
                        'success'  => true,
                        'message'  => 'লগইন সফল হয়েছে! হোমপেজে প্রবেশ করানো হচ্ছে...',
                        'redirect' => $redirectUrl,
                    ]);
                }

                if (!empty($matchedUser->must_change_password)) {
                    return redirect()->route('my-account')->with('warning', 'আপনি নতুন পাসওয়ার্ড/ওটিপি দিয়ে লগইন করেছেন। অনুগ্রহ করে প্রোফাইল থেকে একটি স্থায়ী পাসওয়ার্ড সেট করুন।');
                }

                return redirect()->route('home');
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Login process error: ' . $e->getMessage(), [
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            $msg = 'লগইন প্রক্রিয়ায় সাময়িক সমস্যা দেখা দিয়েছে। অনুগ্রহ করে আবার চেষ্টা করুন বা অ্যাডমিনের সাথে যোগাযোগ করুন।';
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            throw ValidationException::withMessages(['email' => $msg]);
        }

        RateLimiter::hit($throttleKey, 60);
        $failResult = LoginSecurityLog::recordFailedAttempt($request->ip(), $loginInput);
        SecurityAuditService::loginFailed($loginInput, $failResult['count'] ?? 1);

        $showCaptcha = (bool) ($failResult['show_captcha'] ?? false);
        if ($showCaptcha) {
            Session::flash('show_captcha', true);
        }

        if ($isAjax) {
            return response()->json([
                'success'          => false,
                'show_captcha'     => $showCaptcha,
                'captcha_required' => (bool) ($failResult['requires_captcha'] ?? false),
                'attempts'         => $failResult['count'] ?? 1,
                'cooldown_seconds' => $failResult['cooldown_seconds'] ?? 0,
                'is_blocked'       => ($failResult['action'] ?? '') === 'auto_blocked',
                'message'          => $failResult['message'] ?? 'ইমেইল/ইউজারনেম বা পাসওয়ার্ড সঠিক নয়।',
            ], 422);
        }

        throw ValidationException::withMessages([
            'email' => $failResult['message'] ?? 'ইমেইল/ইউজারনেম বা পাসওয়ার্ড সঠিক নয়।',
        ]);
    }

    public function logout(Request $request)
    {
        try {
            if (Auth::check()) {
                Auth::logout();
            }
        } catch (\Throwable $e) {
            // Ignore auth driver exceptions
        }

        try {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        } catch (\Throwable $e) {
            // Ignore session invalidation exceptions
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'  => true,
                'message'  => 'Logged out successfully.',
                'redirect' => url('/'),
            ]);
        }

        return redirect('/')->with('success', 'Logged out successfully.');
    }
}
