<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoginSecurityLog extends Model
{
    protected $table = 'login_security_logs';

    protected $fillable = [
        'ip_address',
        'last_username',
        'attempt_count',
        'locked_until',
        'is_blocked',
        'is_security_issue',
        'threat_level',
        'flagged_at',
        'human_challenge_passed_at',
        'blocked_at',
        'block_reason',
        'unblocked_at',
        'unblocked_by',
    ];

    protected $casts = [
        'attempt_count'             => 'integer',
        'locked_until'              => 'datetime',
        'is_blocked'                => 'boolean',
        'is_security_issue'         => 'boolean',
        'flagged_at'                => 'datetime',
        'human_challenge_passed_at' => 'datetime',
        'blocked_at'                => 'datetime',
        'unblocked_at'              => 'datetime',
    ];

    public function unblockedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'unblocked_by');
    }

    /**
     * Check if an IP address is blocked or locked, and whether captcha is required.
     */
    public static function checkIpStatus(string $ip, ?string $username = null): array
    {
        $log = self::where('ip_address', $ip)->first();

        if (!$log) {
            return [
                'status'                    => 'clean',
                'attempts'                  => 0,
                'is_security_issue'         => false,
                'requires_visual_challenge' => false,
                'requires_captcha'          => false,
                'show_captcha'              => false,
            ];
        }

        $threshold = (int) config('services.recaptcha.threshold', 3);

        // 1. Check if permanently or auto-blocked
        if ($log->is_blocked) {
            return [
                'status'                    => 'blocked',
                'reason'                    => $log->block_reason ?: '৫ বার ভুল পাসওয়ার্ড দেওয়ার কারণে এই আইপিটি সাময়িক অটো-ব্লক করা হয়েছে।',
                'blocked_at'                => $log->blocked_at,
                'attempts'                  => $log->attempt_count,
                'is_security_issue'         => true,
                'threat_level'              => $log->threat_level ?: 'critical',
                'requires_visual_challenge' => false,
                'requires_captcha'          => false,
                'show_captcha'              => false,
            ];
        }

        // 2. Check if currently under temporary lockout (after multiple attempts)
        if ($log->locked_until && Carbon::now()->lt($log->locked_until)) {
            $remainingMinutes = Carbon::now()->diffInMinutes($log->locked_until) + 1;
            return [
                'status'                    => 'locked',
                'remaining_minutes'         => $remainingMinutes,
                'locked_until'              => $log->locked_until,
                'attempts'                  => $log->attempt_count,
                'is_security_issue'         => true,
                'threat_level'              => $log->threat_level ?: 'high',
                'requires_visual_challenge' => true,
                'requires_captcha'          => true,
                'show_captcha'              => true,
            ];
        }

        // Check if 3 or more failed attempts occurred -> Requires captcha
        $requiresCaptcha = ($log->attempt_count >= $threshold || $log->is_security_issue);

        return [
            'status'                    => ($requiresCaptcha ? 'security_issue' : ($log->attempt_count > 0 ? 'warning' : 'clean')),
            'attempts'                  => $log->attempt_count,
            'is_security_issue'         => (bool)$log->is_security_issue,
            'threat_level'              => $log->threat_level ?: ($requiresCaptcha ? 'high' : 'medium'),
            'requires_visual_challenge' => $requiresCaptcha,
            'requires_captcha'          => $requiresCaptcha,
            'show_captcha'              => $requiresCaptcha,
        ];
    }

    /**
     * Determine if an IP or User requires Google reCAPTCHA challenge before attempting login.
     */
    public static function requiresHumanChallenge(string $ip, ?string $username = null): bool
    {
        $threshold = (int) config('services.recaptcha.threshold', 3);
        $log = self::where('ip_address', $ip)->first();
        if ($log) {
            return $log->attempt_count >= $threshold || (bool)$log->is_security_issue;
        }

        return false;
    }

    /**
     * Alias for checking if captcha is required.
     */
    public static function requiresCaptcha(string $ip, ?string $username = null): bool
    {
        return self::requiresHumanChallenge($ip, $username);
    }

    /**
     * Record a failed login attempt with tiered rules:
     * - Attempt 1-2: Normal failure warning.
     * - Attempt 3+: Flagged as SECURITY ISSUE + requires Google reCAPTCHA v2 challenge.
     * - Attempt 4: Warning (1 chance left before auto-block).
     * - Attempt 5: Auto-Blocked in DB!
     */
    public static function recordFailedAttempt(string $ip, string $username): array
    {
        $threshold = (int) config('services.recaptcha.threshold', 3);

        $log = self::firstOrCreate(
            ['ip_address' => $ip],
            ['attempt_count' => 0]
        );

        $log->last_username = $username;
        $log->attempt_count += 1;

        if ($log->attempt_count >= 5) {
            // Auto IP Block on 5th failed attempt
            $log->is_blocked = true;
            $log->is_security_issue = true;
            $log->threat_level = 'critical';
            $log->blocked_at = Carbon::now();
            $log->block_reason = '৫ বার ভুল পাসওয়ার্ড দিয়ে ব্যর্থ লগইন চেষ্টার কারণে স্বয়ংক্রিয় ব্লক (সিকিউরিটি থ্রেট)';
            $log->save();

            return [
                'action'                    => 'auto_blocked',
                'count'                     => $log->attempt_count,
                'is_security_issue'         => true,
                'requires_visual_challenge' => false,
                'requires_captcha'          => false,
                'show_captcha'              => false,
                'message'                   => 'নিরাপত্তা সতর্কতা: ভুল পাসওয়ার্ড দিয়ে ৫বার ব্যর্থ চেষ্টার কারণে এই আইপি অ্যাড্রেসটি সাময়িক অটো-ব্লক করা হয়েছে। অ্যাকাউন্ট ফিরে পেতে অ্যাডমিনের সাথে যোগাযোগ করুন।',
            ];
        }

        if ($log->attempt_count >= $threshold) {
            // Trigger Captcha Requirement for 3+ attempts
            $log->is_security_issue = true;
            $log->threat_level = 'high';
            $log->flagged_at = Carbon::now();
            $log->save();

            $msg = ($log->attempt_count === 3)
                ? 'নিরাপত্তা সতর্কতা: আপনি ৩ বার ভুল পাসওয়ার্ড দিয়েছেন! নিরাপত্তার স্বার্থে Google reCAPTCHA ভেরিফিকেশন সম্পন্ন করে লগইন করুন।'
                : "সতর্কতা: ভুল পাসওয়ার্ড! এটি আপনার {$log->attempt_count}ম প্রচেষ্টা। Google reCAPTCHA সম্পন্ন করে পুনরায় চেষ্টা করুন। (৫ম প্রচেষ্টায় আইপি ব্লক হবে)";

            return [
                'action'                    => 'captcha_required',
                'count'                     => $log->attempt_count,
                'is_security_issue'         => true,
                'requires_visual_challenge' => true,
                'requires_captcha'          => true,
                'show_captcha'              => true,
                'message'                   => $msg,
            ];
        }

        $log->save();

        $remainingInTier = $threshold - $log->attempt_count;
        return [
            'action'                    => 'failed',
            'count'                     => $log->attempt_count,
            'is_security_issue'         => false,
            'requires_visual_challenge' => false,
            'requires_captcha'          => false,
            'show_captcha'              => false,
            'message'                   => "ইমেইল/ইউজারনেম বা পাসওয়ার্ড সঠিক নয়।",
        ];
    }

    /**
     * Record successful completion of human visual challenge.
     */
    public static function recordChallengePassed(string $ip): void
    {
        $log = self::where('ip_address', $ip)->first();
        if ($log) {
            $log->update([
                'human_challenge_passed_at' => Carbon::now(),
            ]);
        }
    }

    /**
     * Clear failed attempts upon successful login.
     */
    public static function recordSuccessfulLogin(string $ip, ?string $username = null): void
    {
        self::where('ip_address', $ip)->delete();
        if ($username) {
            self::where('last_username', $username)->delete();
        }
    }

    /**
     * Unblock an IP address, reset failed attempts and clear security issues.
     */
    public static function unblockIp(string $ip, ?int $adminId = null): void
    {
        $log = self::where('ip_address', $ip)->first();
        if ($log) {
            $log->update([
                'is_blocked'                => false,
                'is_security_issue'         => false,
                'threat_level'              => 'low',
                'attempt_count'             => 0,
                'locked_until'              => null,
                'unblocked_at'              => Carbon::now(),
                'unblocked_by'              => $adminId,
            ]);
        }
    }

    /**
     * Manually block an IP address.
     */
    public static function blockIp(string $ip, string $reason, ?int $adminId = null): void
    {
        self::updateOrCreate(
            ['ip_address' => $ip],
            [
                'is_blocked'        => true,
                'is_security_issue' => true,
                'threat_level'      => 'critical',
                'blocked_at'        => Carbon::now(),
                'block_reason'      => $reason,
                'attempt_count'     => 5,
            ]
        );
    }
}
