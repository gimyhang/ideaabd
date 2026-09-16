<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PasswordSecurityService
{
    /**
     * Top common, dictionary, and easily guessable passwords blacklist.
     */
    private const COMMON_PASSWORDS = [
        '123456', '12345678', '123456789', '1234567890', '12345', '1234567',
        'password', 'password1', 'password123', 'admin', 'admin123', 'administrator',
        'qwerty', 'qwertyuiop', 'asdfghjkl', 'zxcvbnm', '111111', '000000', '123123',
        'abc123', 'abcdef', 'welcome', 'welcome1', 'welcome123', 'login', 'pass1234',
        'iloveyou', 'sunshine', 'princess', 'football', 'monkey', 'master', 'dragon',
        'bangladesh', 'dhaka123', 'idea123', 'ideaabd', 'idea2024', 'idea2025', 'idea2026',
        'secret', 'test1234', 'default', 'root', 'superman', 'trustno1', 'starwars',
        'superadmin', 'guest', 'system', 'change_me', 'changeme', 'letmein', 'access',
        'charlie', 'donald', 'freedom', 'shadow', 'whatever', 'jordan', 'harley',
        'computer', 'internet', 'database', 'security', 'secure123', 'pass@word1',
        'p@ssword', 'p@ssw0rd', 'password@123', 'admin@123', 'admin#123', 'root123',
        '123456a', '123456b', '123456c', '654321', '987654321', '0987654321',
        'qwerty123', 'qazwsx', 'wsxedc', 'rfvbgt', 'yhnmju', 'zaq12wsx', 'xsw23edc',
        'bangla123', 'bangladesh123', 'bd123456', 'amarsonarbangla', 'bismillah',
    ];

    /**
     * Minimum & Maximum password length limits.
     */
    public const MIN_LENGTH = 8;
    public const MAX_LENGTH = 128;

    /**
     * Validate a password against all OWASP security rules.
     *
     * @param string $password
     * @param array{name?: string, email?: string, phone?: string} $context
     * @param bool $checkBreached
     * @return array{valid: bool, errors: string[], strength: array}
     */
    public function validate(string $password, array $context = [], bool $checkBreached = true): array
    {
        $errors = [];
        $length = mb_strlen($password);

        // 1. Length Checks
        if ($length < self::MIN_LENGTH) {
            $errors[] = 'পাসওয়ার্ড সর্বনিম্ন ' . self::MIN_LENGTH . ' অক্ষরের হতে হবে। (Password must be at least ' . self::MIN_LENGTH . ' characters)';
        }

        if ($length > self::MAX_LENGTH) {
            $errors[] = 'পাসওয়ার্ড সর্বোচ্চ ' . self::MAX_LENGTH . ' অক্ষরের মধ্যে হতে হবে।';
        }

        // 2. Character Complexity Checks
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'পাসওয়ার্ডে অন্তত একটি বড় হাতের অক্ষর (A-Z) থাকতে হবে।';
        }

        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'পাসওয়ার্ডে অন্তত একটি ছোট হাতের অক্ষর (a-z) থাকতে হবে।';
        }

        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'পাসওয়ার্ডে অন্তত একটি সংখ্যা (0-9) থাকতে হবে।';
        }

        if (!preg_match('/[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]/', $password)) {
            $errors[] = 'পাসওয়ার্ডে অন্তত একটি বিশেষ চিহ্ন বা স্পেশাল ক্যারেক্টার (!@#$%^&* ইত্যাদি) থাকতে হবে।';
        }

        // 3. Common & Dictionary Password Blacklist
        if ($this->isCommonOrDictionary($password)) {
            $errors[] = 'এই পাসওয়ার্ডটি অত্যন্ত সহজ ও বহুল ব্যবহৃত। অনুগ্রহ করে একটি ভিন্ন ও শক্তিশালী পাসওয়ার্ড দিন।';
        }

        // 4. Sequential Patterns & Character Repetition
        if ($this->hasSequentialPattern($password)) {
            $errors[] = 'পাসওয়ার্ডে ক্রমিক অক্ষর বা সংখ্যা (যেমন: 12345, abcde, qwerty) ব্যবহার করা যাবে না।';
        }

        if ($this->hasExcessiveRepetition($password)) {
            $errors[] = 'পাসওয়ার্ডে একই অক্ষর বা সংখ্যার পুনরাবৃত্তি (যেমন: aaaa, 1111) পরিহার করুন।';
        }

        // 5. User Personal Information Matching
        if (!empty($context)) {
            $contextMatch = $this->matchesUserInfo($password, $context);
            if ($contextMatch) {
                $errors[] = 'পাসওয়ার্ডে আপনার নাম, ইমেইল বা মোবাইল নম্বর অন্তর্ভুক্ত করা যাবে না।';
            }
        }

        // 6. Compromised / Breached Password Check (HIBP k-Anonymity)
        if ($checkBreached && empty($errors)) {
            if ($this->isCompromised($password)) {
                $errors[] = 'নিরাপত্তা সতর্কতা: এই পাসওয়ার্ডটি পূর্বে ডাটা ব্রিচে ব্যাপকভাবে ফাঁস হয়েছে। নিরাপত্তার স্বার্থে অন্য একটি পাসওয়ার্ড দিন।';
            }
        }

        $strength = $this->calculateStrength($password);

        return [
            'valid'    => empty($errors),
            'errors'   => $errors,
            'strength' => $strength,
        ];
    }

    /**
     * Static helper for validating passwords.
     */
    public static function validateStatic(string $password, array $context = [], bool $checkBreached = true): array
    {
        return (new self())->validate($password, $context, $checkBreached);
    }

    /**
     * Check if password is in common passwords list or dictionary variations.
     */
    public function isCommonOrDictionary(string $password): bool
    {
        $normalized = strtolower(trim($password));
        $cleanAlnum = preg_replace('/[^a-z0-9]/', '', $normalized);

        // Direct match with common list
        if (in_array($normalized, self::COMMON_PASSWORDS, true)) {
            return true;
        }

        // Match normalized alphanumeric version
        if (in_array($cleanAlnum, self::COMMON_PASSWORDS, true)) {
            return true;
        }

        // Check common substitutions (e.g. p@ssw0rd -> password)
        $substitutions = [
            '@' => 'a', '4' => 'a',
            '3' => 'e',
            '1' => 'i', '!' => 'i',
            '0' => 'o',
            '$' => 's', '5' => 's',
            '7' => 't',
        ];
        $leetspeakReplaced = strtr($normalized, $substitutions);
        if (in_array($leetspeakReplaced, self::COMMON_PASSWORDS, true)) {
            return true;
        }

        return false;
    }

    /**
     * Detect sequential keyboard or numerical sequences (e.g. 12345, abcde, qwerty, 98765).
     */
    public function hasSequentialPattern(string $password): bool
    {
        $sequences = [
            '01234567890',
            '9876543210',
            'abcdefghijklmnopqrstuvwxyz',
            'zyxwvutsrqponmlkjihgfedcba',
            'qwertyuiop',
            'asdfghjkl',
            'zxcvbnm',
        ];

        $lower = strtolower($password);
        foreach ($sequences as $seq) {
            for ($i = 0; $i <= strlen($seq) - 4; $i++) {
                $sub = substr($seq, $i, 4);
                if (str_contains($lower, $sub)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Detect 3 or more consecutive identical characters (e.g. 'aaa', '1111').
     */
    public function hasExcessiveRepetition(string $password): bool
    {
        return (bool) preg_match('/(.)\1{2,}/u', $password);
    }

    /**
     * Check if password contains parts of user's name, email username, or phone number.
     *
     * @param string $password
     * @param array{name?: string, email?: string, phone?: string} $context
     */
    public function matchesUserInfo(string $password, array $context): bool
    {
        $lowerPass = strtolower($password);

        if (!empty($context['name'])) {
            $nameParts = array_filter(preg_split('/[\s\-_.]+/', strtolower($context['name'])));
            foreach ($nameParts as $part) {
                if (strlen($part) >= 3 && str_contains($lowerPass, $part)) {
                    return true;
                }
            }
        }

        if (!empty($context['email'])) {
            $emailUser = strtolower(explode('@', $context['email'])[0]);
            if (strlen($emailUser) >= 3 && str_contains($lowerPass, $emailUser)) {
                return true;
            }
        }

        if (!empty($context['phone'])) {
            $digitsOnly = preg_replace('/[^0-9]/', '', $context['phone']);
            if (strlen($digitsOnly) >= 6 && str_contains($lowerPass, substr($digitsOnly, -6))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check HaveIBeenPwned API using k-Anonymity (5-character SHA-1 prefix).
     * The actual password is NEVER sent across the internet!
     */
    public function isCompromised(string $password): bool
    {
        try {
            $sha1 = strtoupper(sha1($password));
            $prefix = substr($sha1, 0, 5);
            $suffix = substr($sha1, 5);

            $cacheKey = 'hibp_hash_prefix_' . $prefix;
            $cachedHashes = Cache::get($cacheKey);

            if ($cachedHashes === null) {
                // Fetch with 1.5s timeout so user registration is never blocked if network is slow
                $response = Http::timeout(1.5)->get("https://api.pwnedpasswords.com/range/{$prefix}");

                if ($response->successful()) {
                    $cachedHashes = $response->body();
                    Cache::put($cacheKey, $cachedHashes, now()->addDays(7));
                } else {
                    return false;
                }
            }

            // Parse lines formatted as SUFFIX:COUNT
            $lines = explode("\n", (string) $cachedHashes);
            foreach ($lines as $line) {
                $parts = explode(':', trim($line));
                if (isset($parts[0]) && strtoupper(trim($parts[0])) === $suffix) {
                    $count = isset($parts[1]) ? (int) $parts[1] : 1;
                    if ($count > 3) {
                        return true;
                    }
                }
            }

            return false;
        } catch (\Throwable $e) {
            Log::info('Compromised password check skipped due to timeout or network: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Calculate password strength score (0-100) and classification.
     */
    public function calculateStrength(string $password): array
    {
        $length = mb_strlen($password);
        if ($length === 0) {
            return ['score' => 0, 'label' => 'Very Weak', 'bn_label' => 'খুব দুর্বল', 'color' => '#dc2626'];
        }

        $score = 0;

        // Length contribution
        if ($length >= 8) $score += 20;
        if ($length >= 12) $score += 15;
        if ($length >= 16) $score += 10;
        if ($length >= 20) $score += 10;

        // Variety contribution
        if (preg_match('/[a-z]/', $password)) $score += 10;
        if (preg_match('/[A-Z]/', $password)) $score += 15;
        if (preg_match('/[0-9]/', $password)) $score += 10;
        if (preg_match('/[!@#$%^&*(),.?":{}|<>_\-+=\/\\\[\]]/', $password)) $score += 15;

        // Deductions
        if ($this->hasSequentialPattern($password)) $score = max(10, $score - 20);
        if ($this->hasExcessiveRepetition($password)) $score = max(10, $score - 15);
        if ($this->isCommonOrDictionary($password)) $score = min(20, $score);

        $score = min(100, max(0, $score));

        $label = match (true) {
            $score >= 85 => ['label' => 'Very Strong', 'bn_label' => 'অত্যন্ত শক্তিশালী', 'color' => '#059669'],
            $score >= 70 => ['label' => 'Strong', 'bn_label' => 'শক্তিশালী', 'color' => '#16a34a'],
            $score >= 50 => ['label' => 'Good', 'bn_label' => 'ভালো', 'color' => '#ca8a04'],
            $score >= 30 => ['label' => 'Fair', 'bn_label' => 'মোটামুটি', 'color' => '#ea580c'],
            default      => ['label' => 'Weak', 'bn_label' => 'দুর্বল', 'color' => '#dc2626'],
        };

        return array_merge(['score' => $score], $label);
    }
}
