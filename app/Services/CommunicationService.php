<?php

namespace App\Services;

use App\Models\User;
use App\Support\SiteSetting;
use Illuminate\Support\Facades\Log;

class CommunicationService
{
    /**
     * Send Registration / Mobile Verification OTP via SMS, Email, or Both.
     *
     * @param string|User $recipient Phone number, Email, or User model
     * @param string $otpCode 6-digit verification code
     * @param string $channel 'both', 'sms', 'email', 'auto'
     * @return array
     */
    public static function sendRegistrationOtp(mixed $recipient, string $otpCode, string $channel = 'both'): array
    {
        $phone = self::extractPhone($recipient);
        $email = self::extractEmail($recipient);
        $name  = self::extractName($recipient);
        $siteName = SiteSetting::name() ?: 'আইডিয়া প্রকাশন';

        $results = ['sms' => null, 'email' => null, 'success' => false];

        // SMS Dispatch
        if (($channel === 'both' || $channel === 'sms' || $channel === 'auto') && !empty($phone)) {
            $smsMessage = "{$siteName} — আপনার একাউন্ট ভেরিফিকেশন ওটিপি কোড: {$otpCode} (মেয়াদ ১৫ মিনিট)। কোডটি কাউকে শেয়ার করবেন না।";
            $results['sms'] = SmsService::send($phone, $smsMessage);
        }

        // Email Dispatch
        if (($channel === 'both' || $channel === 'email' || $channel === 'auto') && !empty($email)) {
            $emailSubject = "{$siteName} — অ্যাকাউন্ট ভেরিফিকেশন কোড: {$otpCode}";
            $emailBody = "আইডিয়া প্রকাশনে আপনাকে স্বাগতম!\n\nআপনার অ্যাকাউন্ট ভেরিফিকেশন কোড: {$otpCode}\n\nএই কোডটির মেয়াদ ১৫ মিনিট। কোডটি গোপন রাখুন এবং কারও সাথে শেয়ার করবেন না।";
            $results['email'] = EmailService::sendSingle($email, $emailSubject, $emailBody, 'লগইন করুন', url('/login'), $name);
        }

        $results['success'] = (!empty($results['sms']['success']) || !empty($results['email']['success']));
        return $results;
    }

    /**
     * Send Password Reset OTP & Link via SMS, Email, or Both.
     *
     * @param string|User $recipient Phone number, Email, or User model
     * @param string $otpCode 6-digit OTP code
     * @param string $resetUrl Direct password reset URL
     * @param string $channel 'both', 'sms', 'email', 'auto'
     * @return array
     */
    public static function sendPasswordResetOtp(mixed $recipient, string $otpCode, string $resetUrl, string $channel = 'both'): array
    {
        $phone = self::extractPhone($recipient);
        $email = self::extractEmail($recipient);
        $name  = self::extractName($recipient);
        $siteName = SiteSetting::name() ?: 'আইডিয়া প্রকাশন';

        $results = ['sms' => null, 'email' => null, 'success' => false];

        // SMS Dispatch
        if (($channel === 'both' || $channel === 'sms' || $channel === 'auto') && !empty($phone)) {
            $smsMessage = "{$siteName} — পাসওয়ার্ড রিসেট ওটিপি কোড: {$otpCode} (মেয়াদ ৩০ মিনিট)। রিসেট লিংক: {$resetUrl}";
            $results['sms'] = SmsService::send($phone, $smsMessage);
        }

        // Email Dispatch
        if (($channel === 'both' || $channel === 'email' || $channel === 'auto') && !empty($email)) {
            $emailSubject = "{$siteName} — পাসওয়ার্ড রিসেট ওটিপি কোড: {$otpCode}";
            $emailBody = "আপনার অ্যাকাউন্টের পাসওয়ার্ড রিসেট করার জন্য একটি অনুরোধ পাওয়া গেছে।\n\nআপনার ওটিপি কোড: {$otpCode}\n\nনিচের বাটনে ক্লিক করে সরাসরি নতুন পাসওয়ার্ড সেট করতে পারেন (মেয়াদ ৩০ মিনিট)। আপনি এই অনুরোধ না করে থাকলে অবিলম্বে আমাদের সাথে যোগাযোগ করুন।";
            $results['email'] = EmailService::sendSingle($email, $emailSubject, $emailBody, 'পাসওয়ার্ড রিসেট করুন', $resetUrl, $name);
        }

        $results['success'] = (!empty($results['sms']['success']) || !empty($results['email']['success']));
        return $results;
    }

    /**
     * Send Login 2FA / Security OTP via SMS, Email, or Both.
     */
    public static function sendLoginOtp(mixed $recipient, string $otpCode, string $channel = 'both'): array
    {
        $phone = self::extractPhone($recipient);
        $email = self::extractEmail($recipient);
        $name  = self::extractName($recipient);
        $siteName = SiteSetting::name() ?: 'আইডিয়া প্রকাশন';

        $results = ['sms' => null, 'email' => null, 'success' => false];

        if (($channel === 'both' || $channel === 'sms' || $channel === 'auto') && !empty($phone)) {
            $smsMessage = "{$siteName} — আপনার লগইন ওটিপি কোড: {$otpCode} (মেয়াদ ৫ মিনিট)।";
            $results['sms'] = SmsService::send($phone, $smsMessage);
        }

        if (($channel === 'both' || $channel === 'email' || $channel === 'auto') && !empty($email)) {
            $emailSubject = "{$siteName} — সিকিউরিটি লগইন ওটিপি কোড: {$otpCode}";
            $emailBody = "আপনার অ্যাকাউন্টে লগইন করার জন্য সিকিউরিটি ওটিপি কোড নিচে দেওয়া হলো:\n\nওটিপি কোড: {$otpCode}\n\nমেয়াদ ৫ মিনিট। আপনি লগইন করতে না চাইলে সাথে সাথে অ্যাকাউন্ট পাসওয়ার্ড পরিবর্তন করুন।";
            $results['email'] = EmailService::sendSingle($email, $emailSubject, $emailBody, 'সিকিউরিটি ড্যাশবোর্ড', url('/my-account'), $name);
        }

        $results['success'] = (!empty($results['sms']['success']) || !empty($results['email']['success']));
        return $results;
    }

    /**
     * Send Order Placement Confirmation / COD Verification OTP via SMS, Email, or Both.
     */
    public static function sendOrderOtp(mixed $recipient, string $otpCode, ?string $orderNumber = null, string $channel = 'both'): array
    {
        $phone = self::extractPhone($recipient);
        $email = self::extractEmail($recipient);
        $name  = self::extractName($recipient);
        $siteName = SiteSetting::name() ?: 'আইডিয়া প্রকাশন';
        $orderLabel = $orderNumber ? "#{$orderNumber}" : 'নতুন অর্ডার';

        $results = ['sms' => null, 'email' => null, 'success' => false];

        if (($channel === 'both' || $channel === 'sms' || $channel === 'auto') && !empty($phone)) {
            $smsMessage = "{$siteName} — আপনার বই অর্ডার {$orderLabel} নিশ্চিত করতে ওটিপি কোড: {$otpCode}।";
            $results['sms'] = SmsService::send($phone, $smsMessage);
        }

        if (($channel === 'both' || $channel === 'email' || $channel === 'auto') && !empty($email)) {
            $emailSubject = "{$siteName} — অর্ডার {$orderLabel} নিশ্চিতকরণ ওটিপি: {$otpCode}";
            $emailBody = "আইডিয়া প্রকাশনে বই অর্ডার করার জন্য ধন্যবাদ!\n\nআপনার অর্ডার {$orderLabel} সম্পন্ন ও নিশ্চিত করার ওটিপি কোড: {$otpCode}\n\nআমাদের প্রতিনিধি শীঘ্রই পার্সেলটি পাঠিয়ে দেবে।";
            $results['email'] = EmailService::sendSingle($email, $emailSubject, $emailBody, 'অর্ডার ট্র্যাকিং', url('/track-order'), $name);
        }

        $results['success'] = (!empty($results['sms']['success']) || !empty($results['email']['success']));
        return $results;
    }

    /**
     * Send Unified Marketing / Promotional Campaign via SMS, Email, or Both.
     *
     * @param array $recipients Array of items: [['phone' => '...', 'email' => '...', 'name' => '...'], ...]
     * @param string $subject Subject for email
     * @param string $message Text message body (sent via SMS and styled in Email)
     * @param string|null $actionText CTA button text for Email
     * @param string|null $actionUrl CTA button link for Email
     * @param string $channel 'both', 'sms', 'email'
     * @return array
     */
    public static function sendMarketingCampaign(
        array $recipients,
        string $subject,
        string $message,
        ?string $actionText = null,
        ?string $actionUrl = null,
        string $channel = 'both'
    ): array {
        $smsPhones = [];
        $emailAddresses = [];

        foreach ($recipients as $item) {
            if (is_array($item)) {
                if (!empty($item['phone'])) $smsPhones[] = $item['phone'];
                if (!empty($item['email'])) $emailAddresses[] = $item['email'];
            } elseif (is_string($item)) {
                if (filter_var($item, FILTER_VALIDATE_EMAIL)) {
                    $emailAddresses[] = $item;
                } else {
                    $smsPhones[] = $item;
                }
            } elseif ($item instanceof User) {
                if (!empty($item->phone)) $smsPhones[] = $item->phone;
                if (!empty($item->email)) $emailAddresses[] = $item->email;
            }
        }

        $smsPhones = array_unique(array_filter($smsPhones));
        $emailAddresses = array_unique(array_filter($emailAddresses));

        $summary = [
            'sms_sent'   => 0,
            'email_sent' => 0,
            'sms_total'  => count($smsPhones),
            'email_total'=> count($emailAddresses),
            'channel'    => $channel,
        ];

        // 1. Send SMS
        if (($channel === 'both' || $channel === 'sms') && !empty($smsPhones)) {
            $chunks = array_chunk($smsPhones, 50);
            foreach ($chunks as $chunk) {
                $res = SmsService::send($chunk, $message);
                if (!empty($res['success'])) {
                    $summary['sms_sent'] += count($chunk);
                }
            }
        }

        // 2. Send Email
        if (($channel === 'both' || $channel === 'email') && !empty($emailAddresses)) {
            $emailRes = EmailService::sendBroadcast($emailAddresses, $subject, $message, $actionText, $actionUrl);
            $summary['email_sent'] = $emailRes['sent'] ?? 0;
        }

        Log::info("Unified Marketing Campaign dispatched | Channel: {$channel} | SMS Sent: {$summary['sms_sent']}/{$summary['sms_total']} | Email Sent: {$summary['email_sent']}/{$summary['email_total']}");

        return $summary;
    }

    /**
     * Send Order Status Update (Order Placed, Shipped, Delivered) via SMS, Email, or Both.
     */
    public static function sendOrderNotification(
        mixed $recipient,
        string $orderNumber,
        string $statusText,
        ?string $trackingUrl = null,
        string $channel = 'both'
    ): array {
        $phone = self::extractPhone($recipient);
        $email = self::extractEmail($recipient);
        $name  = self::extractName($recipient);
        $siteName = SiteSetting::name() ?: 'আইডিয়া প্রকাশন';

        $results = ['sms' => null, 'email' => null, 'success' => false];

        if (($channel === 'both' || $channel === 'sms' || $channel === 'auto') && !empty($phone)) {
            $smsMessage = "{$siteName} — আপনার অর্ডার #{$orderNumber} {$statusText}। ট্র্যাকিং: " . ($trackingUrl ?: url('/track-order'));
            $results['sms'] = SmsService::send($phone, $smsMessage);
        }

        if (($channel === 'both' || $channel === 'email' || $channel === 'auto') && !empty($email)) {
            $emailSubject = "{$siteName} — আপনার অর্ডার #{$orderNumber} {$statusText}";
            $emailBody = "প্রিয় গ্রাহক,\n\nআপনার বই অর্ডার #{$orderNumber} সংক্রান্ত সর্বশেষ আপডেট:\n\nস্ট্যাটাস: {$statusText}\n\nআপনার অর্ডার সংক্রান্ত বিস্তারিত দেখতে নিচের বাটনে ক্লিক করুন।";
            $results['email'] = EmailService::sendSingle($email, $emailSubject, $emailBody, 'অর্ডার ট্র্যাকিং', $trackingUrl ?: url('/track-order'), $name);
        }

        $results['success'] = (!empty($results['sms']['success']) || !empty($results['email']['success']));
        return $results;
    }

    /**
     * Send Author Royalty / Honorarium Notification via SMS, Email, or Both.
     */
    public static function sendAuthorRoyaltyNotification(
        mixed $recipient,
        float $amount,
        ?string $periodOrNote = null,
        string $channel = 'both'
    ): array {
        $phone = self::extractPhone($recipient);
        $email = self::extractEmail($recipient);
        $name  = self::extractName($recipient);
        $siteName = SiteSetting::name() ?: 'আইডিয়া প্রকাশন';
        $formattedAmount = '৳' . number_format($amount, 2);

        $results = ['sms' => null, 'email' => null, 'success' => false];

        if (($channel === 'both' || $channel === 'sms' || $channel === 'auto') && !empty($phone)) {
            $smsMessage = "{$siteName} — সম্মানিত লেখক, আপনার {$formattedAmount} রয়্যালটি/সম্মানি সফলভাবে প্রস্তুত/পরিশোধ করা হয়েছে (" . ($periodOrNote ?: 'চলতি মাস') . ")।";
            $results['sms'] = SmsService::send($phone, $smsMessage);
        }

        if (($channel === 'both' || $channel === 'email' || $channel === 'auto') && !empty($email)) {
            $emailSubject = "{$siteName} — আপনার {$formattedAmount} রয়্যালটি/সম্মানি আপডেট";
            $emailBody = "শ্রদ্ধেয় লেখক,\n\nআইডিয়া প্রকাশনের পক্ষ থেকে আন্তরিক শুভেচ্ছা।\n\nআপনার বইয়ের বিক্রয় বাবদ রয়্যালটি/সম্মানি: {$formattedAmount} সফলভাবে প্রস্তুত/পরিশোধ করা হয়েছে (" . ($periodOrNote ?: 'চলতি মাস') . ")।\n\nআপনার লেখক ড্যাশবোর্ডে গিয়ে পূর্ণাঙ্গ হিসেব দেখতে পারেন।";
            $results['email'] = EmailService::sendSingle($email, $emailSubject, $emailBody, 'লেখক ড্যাশবোর্ড', url('/author/dashboard'), $name);
        }

        $results['success'] = (!empty($results['sms']['success']) || !empty($results['email']['success']));
        return $results;
    }

    // --- Helper Extraction Methods ---

    protected static function extractPhone(mixed $recipient): ?string
    {
        if ($recipient instanceof User) {
            return $recipient->phone;
        }
        if (is_array($recipient)) {
            return $recipient['phone'] ?? null;
        }
        if (is_string($recipient) && !str_contains($recipient, '@')) {
            return $recipient;
        }
        return null;
    }

    protected static function extractEmail(mixed $recipient): ?string
    {
        if ($recipient instanceof User) {
            return $recipient->email;
        }
        if (is_array($recipient)) {
            return $recipient['email'] ?? null;
        }
        if (is_string($recipient) && str_contains($recipient, '@')) {
            return $recipient;
        }
        return null;
    }

    protected static function extractName(mixed $recipient): ?string
    {
        if ($recipient instanceof User) {
            return $recipient->name;
        }
        if (is_array($recipient)) {
            return $recipient['name'] ?? null;
        }
        return null;
    }
}
