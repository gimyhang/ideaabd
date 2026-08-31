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
     * Check if an IP address is blocked or locked, and whether human visual challenge is required.
     */
    public static function checkIpStatus(string $ip): array
    {
        $log = self::where('ip_address', $ip)->first();

        if (!$log) {
            return [
                'status'                    => 'clean',
                'attempts'                  => 0,
                'is_security_issue'         => false,
                'requires_visual_challenge' => false,
            ];
        }

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
            ];
        }

        // 2. Check if currently under temporary lockout (after 3 attempts)
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
            ];
        }

        // Check if 3 or more failed attempts occurred -> Requires visual image challenge to prove human
        $requiresVisualChallenge = ($log->attempt_count >= 3 || $log->is_security_issue);

        return [
            'status'                    => ($log->attempt_count >= 3 ? 'security_issue' : 'warning'),
            'attempts'                  => $log->attempt_count,
            'is_security_issue'         => (bool)$log->is_security_issue,
            'threat_level'              => $log->threat_level ?: 'medium',
            'requires_visual_challenge' => $requiresVisualChallenge,
        ];
    }

    /**
     * Determine if an IP requires human visual challenge before attempting login.
     */
    public static function requiresHumanChallenge(string $ip): bool
    {
        $log = self::where('ip_address', $ip)->first();
        if (!$log) {
            return false;
        }

        return $log->attempt_count >= 3 || $log->is_security_issue;
    }

    /**
     * Record a failed login attempt with tiered rules:
     * - Attempt 1-2: Normal failure warning.
     * - Attempt 3+: Flagged as SECURITY ISSUE + 10-min lockout + requires visual sign challenge for human verification.
     * - Attempt 4: Warning (1 chance left).
     * - Attempt 5: Auto-Blocked in DB!
     */
    public static function recordFailedAttempt(string $ip, string $username): array
    {
        $log = self::firstOrCreate(
            ['ip_address' => $ip],
            ['attempt_count' => 0]
        );

        $log->last_username = $username;
        $log->attempt_count += 1;

        if ($log->attempt_count === 3) {
            // Tier 1 Trigger: Flag as Security Issue & 10-minute temporary lockout
            $log->locked_until = Carbon::now()->addMinutes(10);
            $log->is_security_issue = true;
            $log->threat_level = 'high';
            $log->flagged_at = Carbon::now();
            $log->save();

            return [
                'action'                    => 'locked_10min',
                'count'                     => 3,
                'is_security_issue'         => true,
                'requires_visual_challenge' => true,
                'message'                   => 'নিরাপত্তা সতর্কতা: ৩ বার ভুল পাসওয়ার্ড দিয়ে চেষ্টা করায় এটি সিকিউরিটি ইস্যু হিসেবে চিহ্নিত হয়েছে! আপনার আইপি ১০ মিনিটের জন্য সাময়িক লক করা হয়েছে। এরপর ছবিতে ক্লিক করে সাইন বা মানুষ প্রমাণ সাপেক্ষে লগইনের সুযোগ পাবেন।',
            ];
        }

        if ($log->attempt_count >= 5) {
            // Tier 2 Trigger: Auto IP Block
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
                'message'                   => 'নিরাপত্তা সতর্কতা: ভুল পাসওয়ার্ড দিয়ে ৫বার ব্যর্থ চেষ্টার কারণে এই আইপি অ্যাড্রেসটি সাময়িক অটো-ব্লক করা হয়েছে। অ্যাকাউন্ট ফিরে পেতে অ্যাডমিনের সাথে যোগাযোগ করুন।',
            ];
        }

        if ($log->attempt_count >= 4) {
            $log->is_security_issue = true;
            $log->threat_level = 'high';
            $log->save();

            return [
                'action'                    => 'warning_last_chance',
                'count'                     => 4,
                'is_security_issue'         => true,
                'requires_visual_challenge' => true,
                'message'                   => 'সতর্কতা: ভুল পাসওয়ার্ড! এটি আপনার ৪র্থ প্রচেষ্টা। আর ১ বার ভুল পাসওয়ার্ড দিলে আপনার আইপি স্বয়ংক্রিয়ভাবে ব্লক হয়ে যাবে।',
            ];
        }

        $log->save();

        $remainingInTier = 3 - $log->attempt_count;
        return [
            'action'                    => 'failed',
            'count'                     => $log->attempt_count,
            'is_security_issue'         => false,
            'requires_visual_challenge' => false,
            'message'                   => "ইমেইল/ইউজারনেম বা পাসওয়ার্ড সঠিক নয়। (আর {$remainingInTier}টি সুযোগের পর সিকিউরিটি লক হবে)",
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
    public static function recordSuccessfulLogin(string $ip): void
    {
        self::where('ip_address', $ip)->delete();
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
