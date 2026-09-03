<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    /**
     * Send an SMS message to a Bangladeshi or international mobile number.
     *
     * @param string $recipient Mobile number (e.g. 01726976982 or +8801726976982)
     * @param string $message   Message text
     * @return array            ['success' => bool, 'message' => string, 'response' => mixed]
     */
    public static function send(string $recipient, string $message): array
    {
        // 1. Normalize phone number
        $bn = ['০','১','২','৩','৪','৫','৬','৭','৮','৯'];
        $en = ['0','1','2','3','4','5','6','7','8','9'];
        $cleanPhone = preg_replace('/[^\d]/', '', str_replace($bn, $en, $recipient));

        // Format to BD standard (e.g. 8801726976982 or 01726976982)
        if (str_starts_with($cleanPhone, '880') && strlen($cleanPhone) === 13) {
            $formattedPhone = $cleanPhone;
            $localPhone     = '0' . substr($cleanPhone, 3);
        } elseif (strlen($cleanPhone) === 11 && str_starts_with($cleanPhone, '01')) {
            $formattedPhone = '88' . $cleanPhone;
            $localPhone     = $cleanPhone;
        } elseif (strlen($cleanPhone) === 10 && str_starts_with($cleanPhone, '1')) {
            $formattedPhone = '880' . $cleanPhone;
            $localPhone     = '0' . $cleanPhone;
        } else {
            $formattedPhone = $cleanPhone;
            $localPhone     = $cleanPhone;
        }

        $url      = env('SMS_GATEWAY_URL');
        $apiKey   = env('SMS_GATEWAY_API_KEY');
        $senderId = env('SMS_GATEWAY_SENDER_ID', 'IdeaProkash');
        $provider = strtolower((string) env('SMS_GATEWAY_PROVIDER', 'generic'));

        Log::info("SMS dispatch requested to: {$localPhone} (Intl: {$formattedPhone}) | Msg: {$message}");

        // If no API URL or API key is set, log and return simulation success
        if (empty($url) || empty($apiKey) || $url === 'https://api.sms-provider.com/v1/send') {
            Log::info("SMS Gateway not fully configured in .env. SMS simulated for: {$localPhone}");
            return [
                'success'    => true,
                'simulated'  => true,
                'message'    => "এসএমএস প্রস্তুত করা হয়েছে (গেটওয়ে কনফিগারেশন মোড)।",
                'phone'      => $localPhone,
            ];
        }

        try {
            $response = null;

            // Provider-specific HTTP mapping
            if ($provider === 'greenweb') {
                // Greenweb API
                $response = Http::timeout(10)->post($url, [
                    'token'   => $apiKey,
                    'to'      => $formattedPhone,
                    'message' => $message,
                ]);
            } elseif ($provider === 'bulksmsbd') {
                // BulkSMSBD API
                $response = Http::timeout(10)->get($url, [
                    'api_key'  => $apiKey,
                    'type'     => 'text',
                    'number'   => $formattedPhone,
                    'senderid' => $senderId,
                    'message'  => $message,
                ]);
            } elseif ($provider === 'alphasms' || $provider === 'sms4bd') {
                // Alpha SMS / SMS4BD
                $response = Http::timeout(10)->withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                ])->post($url, [
                    'recipient' => $formattedPhone,
                    'sender_id' => $senderId,
                    'message'   => $message,
                ]);
            } else {
                // Generic POST / GET SMS Gateway
                $payload = [
                    'api_key'   => $apiKey,
                    'token'     => $apiKey,
                    'sender_id' => $senderId,
                    'senderid'  => $senderId,
                    'from'      => $senderId,
                    'to'        => $formattedPhone,
                    'number'    => $formattedPhone,
                    'phone'     => $localPhone,
                    'message'   => $message,
                    'msg'       => $message,
                ];

                $response = Http::timeout(10)->post($url, $payload);
                if (!$response->successful()) {
                    $response = Http::timeout(10)->get($url, $payload);
                }
            }

            $status = $response ? $response->status() : 500;
            $body   = $response ? $response->body() : 'No response';

            Log::info("SMS Gateway Response [{$status}]: {$body}");

            return [
                'success'  => ($status >= 200 && $status < 300),
                'status'   => $status,
                'response' => $body,
                'phone'    => $localPhone,
            ];
        } catch (\Throwable $e) {
            Log::error("SMS Gateway Exception: " . $e->getMessage());
            return [
                'success' => false,
                'error'   => $e->getMessage(),
                'phone'   => $localPhone,
            ];
        }
    }

    /**
     * Send password reset OTP SMS.
     */
    public static function sendPasswordResetOtp(string $phone, string $otpCode, string $resetUrl): array
    {
        $message = "আইডিয়া প্রকাশন — আপনার পাসওয়ার্ড রিসেট ওটিপি কোড: {$otpCode} (মেয়াদ ৩০ মিনিট)। লিংক: {$resetUrl}";
        return self::send($phone, $message);
    }
}
