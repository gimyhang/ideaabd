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
        try {
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

            // 1. Check if manually blocked by administrator
            if ($log->is_blocked) {
                return [
                    'status'                    => 'blocked',
                    'reason'                    => $log->block_reason ?: 'নিরাপত্তা কারণে এই আইপিটি ব্লক করা হয়েছে।',
                    'blocked_at'                => $log->blocked_at,
                    'attempts'                  => $log->attempt_count,
                    'is_security_issue'         => true,
                    'threat_level'              => $log->threat_level ?: 'critical',
                    'requires_visual_challenge' => false,
                    'requires_captcha'          => false,
                    'show_captcha'              => false,
                ];
            }

            // 2. Check if currently under progressive cooldown / temporary lock
            if ($log->locked_until && Carbon::now()->lt($log->locked_until)) {
                $remainingSeconds = (int) Carbon::now()->diffInSeconds($log->locked_until);
                $remainingMinutes = (int) ceil($remainingSeconds / 60);

                return [
                    'status'                    => 'locked',
                    'remaining_seconds'         => $remainingSeconds,
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
        } catch (\Throwable $e) {
            return [
                'status'                    => 'clean',
                'attempts'                  => 0,
                'is_security_issue'         => false,
                'requires_visual_challenge' => false,
                'requires_captcha'          => false,
                'show_captcha'              => false,
            ];
        }
    }

    /**
     * Determine if an IP or User requires CAPTCHA challenge before attempting login.
     */
    public static function requiresHumanChallenge(string $ip, ?string $username = null): bool
    {
        try {
            $threshold = (int) config('services.recaptcha.threshold', 3);
            $log = self::where('ip_address', $ip)->first();
            if ($log) {
                return $log->attempt_count >= $threshold || (bool)$log->is_security_issue;
            }
        } catch (\Throwable) {}

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
     * Record a failed login attempt with Progressive Delay / Cooldown:
     * - Attempt 1-2: Normal failure, 0s cooldown.
     * - Attempt 3: Requires CAPTCHA.
     * - Attempt 4: Requires CAPTCHA + 30 seconds cooldown.
     * - Attempt 5: Requires CAPTCHA + 60 seconds cooldown.
     * - Attempt 6-7: Requires CAPTCHA + 2 minutes cooldown.
     * - Attempt 8+: Requires CAPTCHA + 15 minutes cooldown.
     */
    public static function recordFailedAttempt(string $ip, string $username): array
    {
        try {
            $threshold = (int) config('services.recaptcha.threshold', 3);

            $log = self::firstOrCreate(
                ['ip_address' => $ip],
                ['attempt_count' => 0]
            );

            $log->last_username = $username;
            $log->attempt_count += 1;

            $cooldownSeconds = 0;
            if ($log->attempt_count >= 8) {
                $cooldownSeconds = 900; // 15 minutes
                $log->threat_level = 'critical';
            } elseif ($log->attempt_count >= 6) {
                $cooldownSeconds = 120; // 2 minutes
                $log->threat_level = 'high';
            } elseif ($log->attempt_count === 5) {
                $cooldownSeconds = 60; // 1 minute
                $log->threat_level = 'high';
            } elseif ($log->attempt_count === 4) {
                $cooldownSeconds = 30; // 30 seconds
                $log->threat_level = 'medium';
            }

            if ($cooldownSeconds > 0) {
                $log->locked_until = Carbon::now()->addSeconds($cooldownSeconds);
                \App\Services\SecurityAuditService::cooldownTriggered($username, $cooldownSeconds, $log->attempt_count);
            }

            if ($log->attempt_count >= $threshold) {
                $log->is_security_issue = true;
                $log->flagged_at = Carbon::now();
            }

            $log->save();

            $requiresCaptcha = ($log->attempt_count >= $threshold);

            $message = 'ইমেইল/ইউজারনেম বা পাসওয়ার্ড সঠিক নয়।';
            if ($cooldownSeconds > 0) {
                $message = ($cooldownSeconds >= 60)
                    ? "একাধিক ভুল চেষ্টার কারণে লগইন সাময়িক স্থগিত করা হয়েছে। অনুগ্রহ করে " . ceil($cooldownSeconds / 60) . " মিনিট পর আবার চেষ্টা করুন।"
                    : "একাধিক ভুল চেষ্টার কারণে লগইন সাময়িক স্থগিত করা হয়েছে। অনুগ্রহ করে {$cooldownSeconds} সেকেন্ড পর আবার চেষ্টা করুন।";
            } elseif ($requiresCaptcha) {
                $message = "নিরাপত্তা সতর্কতা: একাধিক ব্যর্থ লগইন চেষ্টার কারণে নিরাপত্তা ভেরিফিকেশন (CAPTCHA) সম্পন্ন করে আবার চেষ্টা করুন।";
            }

            return [
                'action'                    => ($cooldownSeconds > 0 ? 'cooldown_triggered' : ($requiresCaptcha ? 'captcha_required' : 'failed')),
                'count'                     => $log->attempt_count,
                'cooldown_seconds'          => $cooldownSeconds,
                'is_security_issue'         => (bool) $log->is_security_issue,
                'requires_visual_challenge' => $requiresCaptcha,
                'requires_captcha'          => $requiresCaptcha,
                'show_captcha'              => $requiresCaptcha,
                'message'                   => $message,
            ];
        } catch (\Throwable $e) {
            return [
                'action'                    => 'failed',
                'count'                     => 1,
                'cooldown_seconds'          => 0,
                'is_security_issue'         => false,
                'requires_visual_challenge' => false,
                'requires_captcha'          => false,
                'show_captcha'              => false,
                'message'                   => "ইমেইল/ইউজারনেম বা পাসওয়ার্ড সঠিক নয়।",
            ];
        }
    }

    /**
     * Record successful completion of human visual challenge.
     */
    public static function recordChallengePassed(string $ip): void
    {
        try {
            $log = self::where('ip_address', $ip)->first();
            if ($log) {
                $log->update([
                    'human_challenge_passed_at' => Carbon::now(),
                ]);
            }
        } catch (\Throwable) {}
    }

    /**
     * Record a successful login and reset failed attempt count.
     */
    public static function recordSuccessfulLogin(string $ip, ?string $username = null): void
    {
        try {
            $updateData = [
                'attempt_count'             => 0,
                'locked_until'              => null,
                'is_security_issue'         => false,
                'threat_level'              => 'low',
                'human_challenge_passed_at' => null,
            ];
            if ($username) {
                $updateData['last_username'] = $username;
            }

            self::where('ip_address', $ip)->update($updateData);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('recordSuccessfulLogin error: ' . $e->getMessage());
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
