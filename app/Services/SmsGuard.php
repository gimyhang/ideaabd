<?php

namespace App\Services;

use App\Models\SmsLog;
use App\Models\SmsNumberRestriction;
use App\Support\PhoneNormalizer;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Decides which numbers an SMS may be sent to, and writes the transaction history.
 *
 *  1. Fake or malformed numbers never reach the gateway (01711000001, 01712345678, 019XXXXXXXX …).
 *  2. Automatic messages (OTP, order and registration notices) are limited per number: 2 per rolling 24 hours.
 *     The 3rd attempt locks the number for 24 hours. If that happens on 3 different days the number is blocked for
 *     OTP until an admin releases it.
 *  3. Messages an admin sends from /admin skip the limit (campaigns), but not the fake-number check.
 *
 * Every number, sent or refused, gets one row in sms_logs.
 */
class SmsGuard
{
    private static ?bool $tablesReady = null;

    /** Rules (fake-number check, daily limit) are skipped in unit tests, like AbuseGuard, unless a test turns sms.guard.test_mode on. */
    public static function rulesEnabled(): bool
    {
        if (!config('sms.guard.enabled', true)) {
            return false;
        }

        return !(app()->runningUnitTests() && !config('sms.guard.test_mode'));
    }

    /** History and the daily limit need the sms_logs tables; before "php artisan migrate" only the number check runs. */
    public static function enabled(): bool
    {
        return self::rulesEnabled() && self::tablesReady();
    }

    private static function tablesReady(): bool
    {
        if (self::$tablesReady === null || app()->runningUnitTests()) {
            try {
                self::$tablesReady = Schema::hasTable('sms_logs') && Schema::hasTable('sms_number_restrictions') && Schema::hasColumn('sms_logs', 'dedupe_key');
            } catch (\Throwable) {
                self::$tablesReady = false;
            }
            if (!self::$tablesReady) {
                Log::warning('SmsGuard is off: run "php artisan migrate" to create the sms_logs tables (and the dedupe_key column).');
            }
        }

        return self::$tablesReady;
    }

    /** Where this send came from: an admin working in /admin, or the site itself (visitors, queues, commands). */
    public static function detectSource(): string
    {
        try {
            return (request()->is('admin/*') && auth()->check()) ? 'admin' : 'system';
        } catch (\Throwable) {
            return 'system';
        }
    }

    /** otp / order / welcome / notification, guessed from the text when the caller did not say. */
    public static function detectPurpose(string $message): string
    {
        if (preg_match('/\botp\b|verification code|verify|ওটিপি|ভেরিফিকেশন|\bcode\b|কোড/iu', $message)) {
            return 'otp';
        }
        if (preg_match('/\border\b|অর্ডার/iu', $message)) {
            return 'order';
        }

        return preg_match('/\bwelcome\b|স্বাগত/iu', $message) ? 'welcome' : 'notification';
    }

    /**
     * Why a number cannot receive SMS at all, or null when it looks like a real mobile number.
     * $info is PhoneNormalizer::normalize() output.
     */
    public static function inspectNumber(array $info): ?string
    {
        $digits = (string) $info['e164'];

        if (!empty($info['is_bd'])) {
            // 8801 + operator digit 3-9 + 8 digits
            if (!preg_match('/^8801[3-9]\d{8}$/', $digits)) {
                return 'invalid_number';
            }

            return self::looksFake(substr($digits, 5)) ? 'fake_number' : null;
        }

        return preg_match('/^\d{8,15}$/', $digits) ? null : 'invalid_number';
    }

    /** The 8 subscriber digits of a BD mobile number: placeholders and keyboard-mashing, not real SIMs. */
    public static function looksFake(string $subscriber): bool
    {
        if ($subscriber === '' || !ctype_digit($subscriber)) {
            return true;
        }
        // 11111111, 11000001, 99999912 …
        if (preg_match('/(\d)\1{4,}/', $subscriber) || substr_count($subscriber, '0') >= 6) {
            return true;
        }
        // 12121212, 10101010 …
        if (count(array_unique(str_split($subscriber))) <= 2) {
            return true;
        }
        // 12345678, 87654321, 23456789 … (6 or more digits in a row going up or down by 1)
        $best = $run = 1;
        $prev = null;
        for ($i = 1, $n = strlen($subscriber); $i < $n; $i++) {
            $step = (int) $subscriber[$i] - (int) $subscriber[$i - 1];
            if (abs($step) === 1) {
                $run  = $prev === $step ? $run + 1 : 2;
                $prev = $step;
            } else {
                $run  = 1;
                $prev = null;
            }
            $best = max($best, $run);
        }

        return $best >= 6;
    }

    /**
     * Check every recipient before anything is sent.
     *
     * @param  array<int, array{to: string, message: string}> $items one entry per message
     * @param  array $ctx source, purpose, campaign (optional)
     * @return array{allowed: array<string, string>, denied: array<int, array>, batch: string, logging: bool}
     *               allowed = e164 number => its message
     */
    public static function screen(array $items, array $ctx): array
    {
        $batch    = (string) Str::uuid();
        $validate = self::rulesEnabled();
        $logging  = $validate && self::tablesReady();
        $allowed  = [];
        $denied   = [];
        $pending  = [];

        $whitelist = $validate ? self::whitelist() : [];

        foreach ($items as $item) {
            $raw     = trim((string) ($item['to'] ?? ''));
            $message = (string) ($item['message'] ?? '');
            if ($raw === '') {
                continue;
            }

            $info  = PhoneNormalizer::normalize($raw);
            $phone = (string) $info['e164'];

            if (isset($allowed[$phone])) {
                continue; // the same number twice in one send
            }

            if (!$validate) {
                $allowed[$phone] = $message;
                continue;
            }

            if (!in_array($phone, $whitelist, true) && ($code = self::inspectNumber($info))) {
                $denied[] = self::refuse($phone, $code, $message, $ctx, $batch, null, $logging);
                continue;
            }

            if (!$logging) {
                $allowed[$phone] = $message; // number is fine, but there are no tables for limits and history yet
                continue;
            }

            if ($ctx['source'] === 'system' && !in_array($phone, $whitelist, true)) {
                $refusal = self::admitAutomatic($phone, $message, $ctx, $batch);
                if ($refusal !== null) {
                    $denied[] = $refusal;
                    continue;
                }
                $allowed[$phone] = $message; // row already written inside the lock
                continue;
            }

            $allowed[$phone] = $message;
            $pending[] = self::row($phone, $message, $ctx, $batch, 'pending');
        }

        // Admin sends can be thousands of numbers: one insert per 500 rows.
        foreach (array_chunk($pending, 500) as $rows) {
            SmsLog::insert($rows);
        }

        return ['allowed' => $allowed, 'denied' => $denied, 'batch' => $batch, 'logging' => $logging];
    }

    /** Apply the daily limit to one automatic message. Returns the refusal, or null when it may go (a pending row is then written). */
    private static function admitAutomatic(string $phone, string $message, array $ctx, string $batch): ?array
    {
        $lock = null;
        try {
            $lock = Cache::lock('sms-guard:' . $phone, 5);
            $lock->block(3);
        } catch (\Throwable) {
            $lock = null; // no lock support or timeout: carry on, a rare double send beats a lost OTP
        }

        try {
            // An order message goes out every time a customer confirms an order: no limit, no lock.
            if ($ctx['purpose'] === 'order') {
                if (!empty($ctx['once_key']) && SmsLog::where('phone', $phone)->where('dedupe_key', $ctx['once_key'])->whereIn('status', ['pending', 'sent'])->exists()) {
                    return self::refuse($phone, 'order_sent', $message, $ctx, $batch);
                }
                SmsLog::create(self::row($phone, $message, $ctx, $batch, 'pending'));

                return null;
            }

            // The welcome message is sent to a number once.
            if ($ctx['purpose'] === 'welcome') {
                $already = SmsLog::where('phone', $phone)->where('purpose', 'welcome')->whereIn('status', ['pending', 'sent'])->exists();
                if ($already) {
                    return self::refuse($phone, 'welcome_sent', $message, $ctx, $batch);
                }
                SmsLog::create(self::row($phone, $message, $ctx, $batch, 'pending'));

                return null;
            }

            $restriction = SmsNumberRestriction::firstOrNew(['phone' => $phone]);
            $restriction->last_attempt_at = now();

            if ($restriction->isBlocked() && $ctx['purpose'] === 'otp') {
                $restriction->save();

                return self::refuse($phone, 'number_blocked', $message, $ctx, $batch);
            }

            // The 2-a-day lock covers OTP only (scope "otp", default) or every automatic message (scope "all").
            $isOtp   = $ctx['purpose'] === 'otp';
            $limited = $isOtp || config('sms.guard.limit_scope', 'otp') === 'all';

            if (!$limited) {
                // order notices, welcome messages …: a looser cap that only stops someone using a form to flood a number
                $cap  = max(1, (int) config('sms.guard.notification_limit', 5));
                $sent = SmsLog::where('phone', $phone)->where('source', 'system')->where('purpose', '!=', 'otp')
                    ->whereIn('status', ['pending', 'sent'])->where('created_at', '>', now()->subDay())->count();
                if ($sent >= $cap) {
                    return self::refuse($phone, 'daily_limit', $message, $ctx, $batch, 3600);
                }
                SmsLog::create(self::row($phone, $message, $ctx, $batch, 'pending'));

                return null;
            }

            if ($restriction->isRestricted()) {
                $restriction->save();

                return self::refuse($phone, 'restricted', $message, $ctx, $batch, max(60, $restriction->restricted_until->getTimestamp() - time()));
            }

            $limit = max(1, (int) config('sms.guard.daily_limit', 2));
            $sentToday = SmsLog::where('phone', $phone)
                ->where('source', 'system')
                ->when(config('sms.guard.limit_scope', 'otp') === 'otp', fn ($q) => $q->where('purpose', 'otp'))
                ->whereIn('status', ['pending', 'sent'])
                ->where('created_at', '>', now()->subDay())
                ->where('id', '>', (int) $restriction->count_after_id) // after an admin release, earlier rows no longer count
                ->count();

            if ($sentToday >= $limit) {
                $hours = max(1, (int) config('sms.guard.restrict_hours', 24));
                $restriction->restricted_until = now()->addHours($hours);
                $restriction->violations = (int) $restriction->violations + 1;

                $window = max(1, (int) config('sms.guard.violation_window_days', 7));
                $days = collect($restriction->violation_days ?? [])
                    ->push(now()->toDateString())
                    ->unique()
                    ->filter(fn ($d) => $d >= now()->subDays($window)->toDateString())
                    ->values()
                    ->all();
                $restriction->violation_days = $days;

                if (count($days) >= max(1, (int) config('sms.guard.block_after_days', 3))) {
                    $restriction->blocked_at = now();
                    $restriction->reason = 'আলাদা ' . count($days) . ' দিন দৈনিক সীমা অতিক্রম করে বারবার ওটিপি/মেসেজ চেষ্টা';
                    Log::warning("SmsGuard: {$phone} blocked for OTP after repeated daily-limit violations", ['days' => $days]);
                } else {
                    $restriction->reason = 'দৈনিক সীমা অতিক্রম (৩য় চেষ্টা)';
                }
                $restriction->save();

                return self::refuse($phone, $restriction->isBlocked() ? 'number_blocked' : 'restricted', $message, $ctx, $batch, $hours * 3600);
            }

            if ($restriction->exists) {
                $restriction->save();
            }
            SmsLog::create(self::row($phone, $message, $ctx, $batch, 'pending'));

            return null;
        } finally {
            optional($lock)->release();
        }
    }

    /** Write a refused attempt to the history (at most one such row per number per hour) and describe it for the caller. */
    private static function refuse(string $phone, string $code, string $message, array $ctx, string $batch, ?int $retryAfter = null, bool $logging = true): array
    {
        $status = in_array($code, ['fake_number', 'invalid_number'], true) ? 'rejected' : 'blocked';

        try {
            $recent = $logging && SmsLog::where('phone', $phone)
                ->whereIn('status', ['rejected', 'blocked'])
                ->where('created_at', '>', now()->subHour())
                ->exists();
            if ($logging && !$recent) {
                SmsLog::create(self::row($phone, $message, $ctx, $batch, $status, $code));
            }
        } catch (\Throwable $e) {
            Log::warning('SmsGuard could not write a refusal row: ' . $e->getMessage());
        }

        return [
            'phone'       => $phone,
            'code'        => $code,
            'message'     => self::refusalMessage($code, $retryAfter),
            'retry_after' => $retryAfter,
        ];
    }

    public static function refusalMessage(string $code, ?int $retryAfter = null): string
    {
        return match ($code) {
            'fake_number', 'invalid_number' => 'সঠিক বাংলাদেশি মোবাইল নম্বর দিন (যেমন 01712345678)। এই নম্বরে মেসেজ পাঠানো হবে না।',
            'number_blocked'                => 'বারবার চেষ্টার কারণে এই নম্বরে ওটিপি পাঠানো নিরাপত্তার জন্য বন্ধ আছে। সহায়তার জন্য হেল্পলাইনে যোগাযোগ করুন।',
            'order_sent'                    => 'এই অর্ডারের মেসেজ আগেই পাঠানো হয়েছে।',
            'welcome_sent'                  => 'এই নম্বরে ওয়েলকাম মেসেজ আগেই পাঠানো হয়েছে।',
            'daily_limit'                   => 'এই নম্বরে আজকের সর্বোচ্চ মেসেজ সীমা শেষ। ' . self::humanDuration($retryAfter) . ' পরে আবার চেষ্টা করুন।',
            'restricted'                    => 'এই নম্বরে আজকের সর্বোচ্চ মেসেজ সীমা শেষ। ' . self::humanDuration($retryAfter) . ' পরে আবার চেষ্টা করুন।',
            default                         => 'এই নম্বরে এখন মেসেজ পাঠানো যাচ্ছে না।',
        };
    }

    private static function humanDuration(?int $seconds): string
    {
        $seconds = max(60, (int) $seconds);
        $h = intdiv($seconds, 3600);
        $m = intdiv($seconds % 3600, 60);

        return $h > 0 ? "{$h} ঘণ্টা" . ($m > 0 ? " {$m} মিনিট" : '') : "{$m} মিনিট";
    }

    /** Fill the pending rows of one gateway call with what the gateway answered. */
    public static function settle(string $batch, array $phones, array $outcome, bool $logging = true): void
    {
        if (!$logging || $phones === []) {
            return;
        }

        $ok = !empty($outcome['success']);

        try {
            foreach (array_chunk($phones, 500) as $chunk) {
                $query = SmsLog::where('batch', $batch)->whereIn('phone', $chunk)->where('status', 'pending');
                if ($ok) {
                    // cost per row = segments x price
                    $query->update([
                        'status'           => 'sent',
                        'cost'             => \DB::raw('segments * ' . (float) config('sms.cost_per_sms', 0.35)),
                        'provider'         => $outcome['provider'] ?? null,
                        'response_code'    => isset($outcome['response_code']) ? Str::limit((string) $outcome['response_code'], 10, '') : null,
                        'gateway_response' => Str::limit((string) ($outcome['message'] ?? ''), 480, ''),
                    ]);
                } else {
                    $query->update([
                        'status'           => 'failed',
                        'reason'           => 'gateway_error',
                        'provider'         => $outcome['provider'] ?? null,
                        'response_code'    => isset($outcome['response_code']) ? Str::limit((string) $outcome['response_code'], 10, '') : null,
                        'gateway_response' => Str::limit((string) ($outcome['message'] ?? $outcome['error'] ?? ''), 480, ''),
                    ]);
                }
            }
        } catch (\Throwable $e) {
            Log::warning('SmsGuard could not update the history rows: ' . $e->getMessage());
        }
    }

    /** Admin action: lift a restriction or block. */
    public static function release(string $phone): bool
    {
        return SmsNumberRestriction::where('phone', $phone)->update([
            'restricted_until' => null,
            'blocked_at'       => null,
            'violation_days'   => null,
            'violations'       => 0,
            'reason'           => null,
            'blocked_by'       => null,
            'count_after_id'   => (int) SmsLog::max('id'),
        ]) > 0;
    }

    /** Admin action: block a number for OTP by hand. */
    public static function blockNumber(string $phone, ?string $reason = null, ?int $adminId = null): SmsNumberRestriction
    {
        $row = SmsNumberRestriction::firstOrNew(['phone' => $phone]);
        $row->blocked_at = now();
        $row->reason     = $reason ?: 'অ্যাডমিন কর্তৃক ম্যানুয়ালি ব্লক';
        $row->blocked_by = $adminId;
        $row->save();

        return $row;
    }

    /** @return string[] */
    private static function whitelist(): array
    {
        return array_map(fn ($n) => PhoneNormalizer::toE164($n), (array) config('sms.guard.allowed_numbers', []));
    }

    /** OTP codes are not kept in the history. */
    public static function redact(string $message, string $purpose): string
    {
        return $purpose === 'otp' ? preg_replace('/\d{4,8}/u', '••••', $message) : $message;
    }

    private static function row(string $phone, string $message, array $ctx, string $batch, string $status, ?string $reason = null): array
    {
        $now = now();

        return [
            'batch'      => $batch,
            'phone'      => $phone,
            'purpose'    => $ctx['purpose'],
            'source'     => $ctx['source'],
            'status'     => $status,
            'reason'     => $reason,
            'message'    => self::redact($message, $ctx['purpose']),
            'segments'   => SmsService::segments($message),
            'cost'       => 0,
            'campaign'   => isset($ctx['campaign']) ? Str::limit((string) $ctx['campaign'], 100, '') : null,
            'dedupe_key' => isset($ctx['once_key']) ? Str::limit((string) $ctx['once_key'], 120, '') : null,
            'user_id'    => auth()->id(),
            'ip'         => request()?->ip(),
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }
}
