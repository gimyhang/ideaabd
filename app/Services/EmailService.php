<?php

namespace App\Services;

use App\Mail\AdminBroadcastMail;
use App\Support\SiteSetting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailService
{
    /**
     * Get active SMTP configuration from DB SiteSetting or fallback to .env and config.
     */
    public static function getSmtpSettings(): array
    {
        $settings = SiteSetting::get('smtp_settings', []);
        
        return [
            'mailer'       => $settings['mailer'] ?? config('mail.default', 'smtp'),
            'host'         => $settings['host'] ?? config('mail.mailers.smtp.host', '127.0.0.1'),
            'port'         => $settings['port'] ?? config('mail.mailers.smtp.port', 2525),
            'encryption'   => $settings['encryption'] ?? config('mail.mailers.smtp.encryption', 'tls'),
            'username'     => $settings['username'] ?? config('mail.mailers.smtp.username', ''),
            'password'     => $settings['password'] ?? config('mail.mailers.smtp.password', ''),
            'from_address' => $settings['from_address'] ?? config('mail.from.address', 'info@ideaabd.com'),
            'from_name'    => $settings['from_name'] ?? (SiteSetting::name() ?: config('mail.from.name', 'আইডিয়া প্রকাশন')),
        ];
    }

    /**
     * Apply dynamic SMTP configuration at runtime if custom settings exist.
     */
    public static function applyRuntimeSmtpConfig(): void
    {
        $smtp = self::getSmtpSettings();

        if (!empty($smtp['host']) && $smtp['host'] !== '127.0.0.1') {
            $encryption = $smtp['encryption'];
            if ($encryption === 'none' || empty($encryption)) {
                $encryption = null;
            } elseif (in_array((int)$smtp['port'], [465], true) && $encryption === 'tls') {
                $encryption = 'ssl';
            } elseif (in_array((int)$smtp['port'], [587], true) && $encryption === 'ssl') {
                $encryption = 'tls';
            }

            Config::set('mail.default', $smtp['mailer'] ?: 'smtp');
            Config::set('mail.mailers.smtp.host', $smtp['host']);
            Config::set('mail.mailers.smtp.port', (int) $smtp['port']);
            Config::set('mail.mailers.smtp.encryption', $encryption);
            Config::set('mail.mailers.smtp.username', $smtp['username']);
            Config::set('mail.mailers.smtp.password', $smtp['password']);
            
            if (!empty($smtp['from_address'])) {
                Config::set('mail.from.address', $smtp['from_address']);
                Config::set('mail.from.name', $smtp['from_name']);
            }

            try {
                Mail::purge('smtp');
                Mail::purge(config('mail.default'));
            } catch (\Throwable $e) {
                // Ignore purge errors
            }
        }
    }

    /**
     * Send a single/test email.
     *
     * @return array ['success' => bool, 'message' => string, 'recipient' => string]
     */
    public static function sendSingle(
        string $toEmail,
        string $subject,
        string $body,
        ?string $actionText = null,
        ?string $actionUrl = null,
        ?string $recipientName = null
    ): array {
        self::applyRuntimeSmtpConfig();

        $toEmail = trim($toEmail);
        if (!filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            return [
                'success'   => false,
                'message'   => 'অকার্যকর ইমেইল ঠিকানা!',
                'recipient' => $toEmail,
            ];
        }

        try {
            Mail::to($toEmail)->send(new AdminBroadcastMail(
                mailSubject: $subject,
                bodyContent: $body,
                actionText: $actionText,
                actionUrl: $actionUrl,
                recipientName: $recipientName
            ));

            Log::info("Admin Email successfully sent to {$toEmail} with subject: {$subject}");

            return [
                'success'   => true,
                'message'   => 'ইমেইল সফলভাবে পাঠানো হয়েছে!',
                'recipient' => $toEmail,
            ];
        } catch (\Throwable $e) {
            Log::error("Failed to send email to {$toEmail}: " . $e->getMessage());

            return [
                'success'   => false,
                'message'   => 'ইমেইল পাঠাতে ব্যর্থ: ' . $e->getMessage(),
                'recipient' => $toEmail,
            ];
        }
    }

    /**
     * Broadcast email to a list of recipients.
     *
     * @param array $recipients Array of email strings
     * @return array ['total' => int, 'sent' => int, 'failed' => int, 'failed_emails' => array]
     */
    public static function sendBroadcast(
        array $recipients,
        string $subject,
        string $body,
        ?string $actionText = null,
        ?string $actionUrl = null
    ): array {
        self::applyRuntimeSmtpConfig();

        $validEmails = array_unique(array_filter(array_map('trim', $recipients), function ($email) {
            return filter_var($email, FILTER_VALIDATE_EMAIL);
        }));

        $sentCount = 0;
        $failedCount = 0;
        $failedEmails = [];

        foreach ($validEmails as $email) {
            try {
                Mail::to($email)->send(new AdminBroadcastMail(
                    mailSubject: $subject,
                    bodyContent: $body,
                    actionText: $actionText,
                    actionUrl: $actionUrl
                ));
                $sentCount++;
            } catch (\Throwable $e) {
                $failedCount++;
                $failedEmails[] = $email . ' (' . $e->getMessage() . ')';
                Log::error("Broadcast email error for {$email}: " . $e->getMessage());
            }
        }

        return [
            'total'         => count($validEmails),
            'sent'          => $sentCount,
            'failed'        => $failedCount,
            'failed_emails' => $failedEmails,
        ];
    }

    /**
     * Send mobile/email verification OTP email for registration.
     */
    public static function sendRegistrationOtp(string $email, string $otpCode, ?string $userName = null): array
    {
        $siteName = SiteSetting::name() ?: 'আইডিয়া প্রকাশন';
        $subject = "{$siteName} — অ্যাকাউন্ট ভেরিফিকেশন ওটিপি কোড: {$otpCode}";
        $body = "আইডিয়া প্রকাশনে আপনাকে স্বাগতম!\n\nআপনার অ্যাকাউন্ট ভেরিফিকেশন কোড: {$otpCode}\n\nএই কোডটির মেয়াদ ১৫ মিনিট। কোডটি কারও সাথে শেয়ার করবেন না।";
        return self::sendSingle($email, $subject, $body, 'লগইন করুন', url('/login'), $userName);
    }

    /**
     * Send password reset OTP and link email.
     */
    public static function sendPasswordResetOtp(string $email, string $otpCode, string $resetUrl, ?string $userName = null): array
    {
        $siteName = SiteSetting::name() ?: 'আইডিয়া প্রকাশন';
        $subject = "{$siteName} — পাসওয়ার্ড রিসেট ওটিপি কোড: {$otpCode}";
        $body = "আপনার অ্যাকাউন্টের পাসওয়ার্ড রিসেট করার জন্য একটি অনুরোধ পাওয়া গেছে।\n\nআপনার ওটিপি কোড: {$otpCode}\n\nনিচের বাটনে ক্লিক করে সরাসরি নতুন পাসওয়ার্ড সেট করুন (মেয়াদ ৩০ মিনিট)।";
        return self::sendSingle($email, $subject, $body, 'পাসওয়ার্ড রিসেট করুন', $resetUrl, $userName);
    }

    /**
     * Send login 2FA security OTP email.
     */
    public static function sendLoginOtp(string $email, string $otpCode, ?string $userName = null): array
    {
        $siteName = SiteSetting::name() ?: 'আইডিয়া প্রকাশন';
        $subject = "{$siteName} — লগইন সিকিউরিটি ওটিপি: {$otpCode}";
        $body = "আপনার অ্যাকাউন্টে লগইন করার জন্য সিকিউরিটি ওটিপি কোড:\n\n{$otpCode}\n\nমেয়াদ ৫ মিনিট।";
        return self::sendSingle($email, $subject, $body, 'মাই একাউন্ট', url('/my-account'), $userName);
    }

    /**
     * Send order confirmation/COD OTP email.
     */
    public static function sendOrderOtp(string $email, string $otpCode, ?string $orderNumber = null, ?string $userName = null): array
    {
        $siteName = SiteSetting::name() ?: 'আইডিয়া প্রকাশন';
        $orderLabel = $orderNumber ? "#{$orderNumber}" : '';
        $subject = "{$siteName} — অর্ডার {$orderLabel} নিশ্চিতকরণ ওটিপি: {$otpCode}";
        $body = "বই অর্ডার সম্পন্ন করতে আপনার ভেরিফিকেশন ওটিপি কোড নিচে দেওয়া হলো:\n\nওটিপি কোড: {$otpCode}\n\nআমাদের প্রতিনিধি শীঘ্রই আপনার পার্সেল পাঠিয়ে দেবেন।";
        return self::sendSingle($email, $subject, $body, 'অর্ডার ট্র্যাকিং', url('/track-order'), $userName);
    }

    /**
     * Send order status update notification email.
     */
    public static function sendOrderNotification(string $email, string $orderNumber, string $statusText, ?string $trackingUrl = null, ?string $userName = null): array
    {
        $siteName = SiteSetting::name() ?: 'আইডিয়া প্রকাশন';
        $subject = "{$siteName} — অর্ডার #{$orderNumber} সংক্রান্ত আপডেট ({$statusText})";
        $body = "প্রিয় গ্রাহক,\n\nআপনার বই অর্ডার #{$orderNumber} সংক্রান্ত সর্বশেষ স্ট্যাটাস:\n\n{$statusText}\n\nধন্যবাদ আইডিয়া প্রকাশনের সাথে থাকার জন্য।";
        return self::sendSingle($email, $subject, $body, 'অর্ডার ট্র্যাক করুন', $trackingUrl ?: url('/track-order'), $userName);
    }

    /**
     * Send author royalty or honorarium payout notification email.
     */
    public static function sendRoyaltyNotification(string $email, float $amount, ?string $note = null, ?string $userName = null): array
    {
        $siteName = SiteSetting::name() ?: 'আইডিয়া প্রকাশন';
        $amt = number_format($amount, 2);
        $subject = "{$siteName} — রয়্যালটি/সম্মানি ৳{$amt} আপডেট";
        $body = "শ্রদ্ধেয় লেখক,\n\nআইডিয়া প্রকাশনের পক্ষ থেকে শুভেচ্ছা।\n\nআপনার বইয়ের বিক্রয় বাবদ রয়্যালটি/সম্মানি ৳{$amt} সফলভাবে প্রস্তুত ও পরিশোধ করা হয়েছে (" . ($note ?: 'চলতি মাস') . ")।\n\nআপনার লেখক ড্যাশবোর্ডে গিয়ে রয়্যালটি হিসেব দেখতে পারেন।";
        return self::sendSingle($email, $subject, $body, 'লেখক ড্যাশবোর্ড', url('/author/dashboard'), $userName);
    }
}
