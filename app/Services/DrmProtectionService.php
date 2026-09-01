<?php

namespace App\Services;

use App\Models\EbookReadingLog;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Http\Request;
use Modules\Ebook\Models\Ebook;

class DrmProtectionService
{
    /**
     * Generate dynamic multi-layer floating watermark data.
     */
    public function generateWatermark(?User $user, Ebook $ebook, ?string $orderId = null): array
    {
        $now = now()->timezone('Asia/Dhaka')->format('d/m/Y h:i A');
        $ip = request()->ip();

        if ($user) {
            $identifier = $user->name . ' • ' . ($user->phone ?: $user->email);
            $userId = 'UID-' . str_pad((string)$user->id, 5, '0', STR_PAD_LEFT);
        } else {
            $identifier = 'আইডিয়া প্রকাশন ডিজিটাল লাইব্রেরি';
            $userId = 'GUEST-READER';
        }

        $license = $orderId ? ('Order #' . $orderId) : 'Licensed Reader';

        return [
            'visible_text'   => "{$identifier} | {$license} | {$now}",
            'invisible_hash' => hash('sha256', "{$user?->id}:{$ebook->id}:{$ip}:" . config('app.key')),
            'reader_stamp'   => "{$userId} • IP: {$ip} • {$now}",
        ];
    }

    /**
     * Log a reading session for Kindle Unlimited style page tracking.
     */
    public function logReadingSession(?int $userId, int $ebookId, int $pagesRead, int $durationSec = 60): void
    {
        $today = now()->toDateString();
        $ip = request()->ip();

        EbookReadingLog::create([
            'user_id'              => $userId,
            'ebook_id'             => $ebookId,
            'pages_read'           => max(1, $pagesRead),
            'session_duration_sec' => $durationSec,
            'ip_address'           => $ip,
            'device_signature'     => request()->userAgent() ? substr(request()->userAgent(), 0, 250) : null,
            'read_date'            => $today,
        ]);
    }

    /**
     * Check if a user has an active Kindle Unlimited subscription granting e-book access.
     */
    public function hasActiveSubscription(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        return UserSubscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->where('expires_at', '>=', now())
            ->exists();
    }
}
