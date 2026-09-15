<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    /**
     * Normalize a phone number to Bangladeshi international format (8801XXXXXXXXX) or international E.164.
     */
    public static function normalizePhone(string $recipient): string
    {
        $bn = ['০','১','২','৩','৪','৫','৬','৭','৮','৯'];
        $en = ['0','1','2','3','4','5','6','7','8','9'];
        $cleanPhone = preg_replace('/[^\d]/', '', str_replace($bn, $en, $recipient));

        // If Bangladeshi number
        if (str_starts_with($cleanPhone, '880') && strlen($cleanPhone) === 13) {
            return $cleanPhone;
        } elseif (strlen($cleanPhone) === 11 && str_starts_with($cleanPhone, '01')) {
            return '88' . $cleanPhone;
        } elseif (strlen($cleanPhone) === 10 && str_starts_with($cleanPhone, '1')) {
            return '880' . $cleanPhone;
        }

        return $cleanPhone;
    }

    /**
     * Send an SMS message to one or multiple recipients (comma-separated or array).
     *
     * @param string|array $recipient Mobile number(s) e.g. 01726976982 or 8801726976982,88018XXXXXXXX
     * @param string $message Message body (special characters are automatically URL-encoded)
     * @return array ['success' => bool, 'response_code' => int|null, 'message' => string, 'response' => mixed]
     */
    public static function send($recipient, string $message): array
    {
        // 1. Normalize numbers
        if (is_array($recipient)) {
            $numbers = array_map([self::class, 'normalizePhone'], $recipient);
            $formattedNumbers = implode(',', array_filter($numbers));
        } else {
            $parts = explode(',', (string) $recipient);
            $numbers = array_map([self::class, 'normalizePhone'], $parts);
            $formattedNumbers = implode(',', array_filter($numbers));
        }

        $url      = env('SMS_GATEWAY_URL', 'http://bulksmsbd.net/api/smsapi');
        $apiKey   = env('SMS_GATEWAY_API_KEY', 'NDZQOR8CI0fWSxqk1go8');
        $senderId = env('SMS_GATEWAY_SENDER_ID', 'IdeaProkash');
        $provider = strtolower((string) env('SMS_GATEWAY_PROVIDER', 'bulksmsbd'));

        Log::info("SMS dispatch requested to: {$formattedNumbers} | Provider: {$provider} | Sender: {$senderId}");

        // If no API key is set, simulate
        if (empty($apiKey) || $apiKey === 'your_sms_api_key') {
            Log::info("SMS Gateway not fully configured in .env. SMS simulated for: {$formattedNumbers}");
            return [
                'success'       => true,
                'simulated'     => true,
                'response_code' => 1000,
                'message'       => 'এসএমএস প্রস্তুত করা হয়েছে (গেটওয়ে সিমুলেশন মোড)।',
                'numbers'       => $formattedNumbers,
            ];
        }

        try {
            $response = null;

            if ($provider === 'bulksmsbd' || $provider === 'balksms' || str_contains((string) $url, 'bulksmsbd.net')) {
                // BulkSMSBD API (Single / Comma-separated Bulk)
                $apiUrl = (str_contains((string) $url, '/api/smsapi')) ? $url : 'http://bulksmsbd.net/api/smsapi';
                
                // BulkSMSBD supports GET & POST
                $payload = [
                    'api_key'  => $apiKey,
                    'type'     => 'text',
                    'number'   => $formattedNumbers,
                    'senderid' => $senderId,
                    'message'  => $message,
                ];

                $response = Http::timeout(12)->asForm()->post($apiUrl, $payload);
                if (!$response->successful()) {
                    $response = Http::timeout(12)->get($apiUrl, [
                        'api_key'  => $apiKey,
                        'type'     => 'text',
                        'number'   => $formattedNumbers,
                        'senderid' => $senderId,
                        'message'  => urlencode($message),
                    ]);
                }
            } elseif ($provider === 'greenweb') {
                $response = Http::timeout(10)->post($url, [
                    'token'   => $apiKey,
                    'to'      => $formattedNumbers,
                    'message' => $message,
                ]);
            } elseif ($provider === 'alphasms' || $provider === 'sms4bd') {
                $response = Http::timeout(10)->withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                ])->post($url, [
                    'recipient' => $formattedNumbers,
                    'sender_id' => $senderId,
                    'message'   => $message,
                ]);
            } else {
                // Generic POST Gateway
                $payload = [
                    'api_key'   => $apiKey,
                    'senderid'  => $senderId,
                    'sender_id' => $senderId,
                    'number'    => $formattedNumbers,
                    'message'   => $message,
                ];
                $response = Http::timeout(10)->post($url, $payload);
            }

            $status = $response ? $response->status() : 500;
            $body   = $response ? $response->body() : '';
            $json   = $response ? $response->json() : null;

            Log::info("SMS Gateway Response [{$status}]: {$body}");

            $responseCode = $json['response_code'] ?? null;
            $isSuccess = ($status >= 200 && $status < 300) && ($responseCode === 1000 || ($responseCode === null && !str_contains($body, 'error')));

            return [
                'success'       => $isSuccess,
                'status'        => $status,
                'response_code' => $responseCode,
                'message'       => $json['success_message'] ?? ($json['error_message'] ?? $body),
                'raw_response'  => $body,
                'numbers'       => $formattedNumbers,
            ];
        } catch (\Throwable $e) {
            Log::error("SMS Gateway Exception: " . $e->getMessage());
            return [
                'success' => false,
                'error'   => $e->getMessage(),
                'numbers' => $formattedNumbers,
            ];
        }
    }

    /**
     * Send different custom messages to different numbers via BulkSMSBD Many SMS API.
     *
     * @param array $messages Array of items: [['to' => '88017XXXXXXXX', 'message' => 'Custom text'], ...]
     * @return array
     */
    public static function sendBulk(array $messages): array
    {
        $apiKey   = env('SMS_GATEWAY_API_KEY', 'NDZQOR8CI0fWSxqk1go8');
        $senderId = env('SMS_GATEWAY_SENDER_ID', 'IdeaProkash');
        $url      = 'http://bulksmsbd.net/api/smsapimany';

        $formattedMessages = [];
        foreach ($messages as $item) {
            if (!empty($item['to']) && !empty($item['message'])) {
                $formattedMessages[] = [
                    'to'      => self::normalizePhone($item['to']),
                    'message' => $item['message'],
                ];
            }
        }

        if (empty($formattedMessages)) {
            return ['success' => false, 'message' => 'No valid messages provided for bulk dispatch.'];
        }

        try {
            $response = Http::timeout(15)->asForm()->post($url, [
                'api_key'  => $apiKey,
                'senderid' => $senderId,
                'messages' => json_encode($formattedMessages),
            ]);

            return [
                'success'      => $response->successful(),
                'status'       => $response->status(),
                'response'     => $response->json() ?: $response->body(),
            ];
        } catch (\Throwable $e) {
            Log::error("Bulk SMS API Exception: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Check SMS account balance with BulkSMSBD API.
     */
    public static function checkBalance(): array
    {
        $apiKey = env('SMS_GATEWAY_API_KEY', 'NDZQOR8CI0fWSxqk1go8');
        $url    = 'http://bulksmsbd.net/api/smsapicheckbalance';

        try {
            $response = Http::timeout(8)->get($url, [
                'api_key' => $apiKey,
            ]);

            return [
                'success'  => $response->successful(),
                'status'   => $response->status(),
                'data'     => $response->json() ?: $response->body(),
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
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

    /**
     * Send mobile verification OTP SMS for registration.
     */
    public static function sendVerificationOtp(string $phone, string $otpCode): array
    {
        $message = "আইডিয়া প্রকাশন — আপনার মোবাইল ভেরিফিকেশন কোড: {$otpCode} (মেয়াদ ১৫ মিনিট)। কোডটি কাউকে শেয়ার করবেন না।";
        return self::send($phone, $message);
    }
}

