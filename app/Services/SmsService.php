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
     * Get active SMS credentials from DB SiteSetting or fallback to .env and defaults.
     */
    public static function getCredentials(): array
    {
        $settings = \App\Support\SiteSetting::get('sms_gateway_settings', []);
        return [
            'provider'  => $settings['provider'] ?? env('SMS_GATEWAY_PROVIDER', 'alaapcloud'),
            'url'       => $settings['url'] ?? env('SMS_GATEWAY_URL', 'https://www.alaapcloud.gov.bd/api/sms/send'),
            'api_key'   => $settings['api_key'] ?? env('SMS_GATEWAY_API_KEY', env('ALAAP_SMS_API_KEY', '')),
            'sender_id' => $settings['sender_id'] ?? env('SMS_GATEWAY_SENDER_ID', env('ALAAP_SMS_SENDER_ID', 'IdeaProkash')),
            'client_id' => $settings['client_id'] ?? env('ALAAP_SMS_CLIENT_ID', ''),
        ];
    }

    /**
     * Check if a string contains non-ASCII (e.g. Bengali / Unicode) characters.
     */
    public static function isUnicode(string $text): bool
    {
        return strlen($text) !== mb_strlen($text, 'UTF-8');
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

        $creds    = self::getCredentials();
        $url      = $creds['url'];
        $apiKey   = $creds['api_key'];
        $senderId = $creds['sender_id'];
        $provider = strtolower((string) $creds['provider']);
        $isUnicode = self::isUnicode($message);

        Log::info("SMS dispatch requested to: {$formattedNumbers} | Provider: {$provider} | Sender: {$senderId} | Unicode: " . ($isUnicode ? 'Yes' : 'No'));

        // If no API key is set, simulate
        if (empty($apiKey) || $apiKey === 'your_sms_api_key') {
            Log::info("SMS Gateway (Alaap Cloud) not fully configured. SMS simulated for: {$formattedNumbers}");
            return [
                'success'       => true,
                'simulated'     => true,
                'response_code' => 1000,
                'message'       => 'এসএমএস প্রস্তুত করা হয়েছে (সিমুলেশন মোড — Alaap Cloud এপিআই কি প্রয়োজন)।',
                'numbers'       => $formattedNumbers,
            ];
        }

        try {
            $response = null;

            if ($provider === 'alaapcloud' || str_contains((string) $url, 'alaapcloud')) {
                // Alaap Cloud (BTCL) REST API Gateway
                $payload = [
                    'api_key'   => $apiKey,
                    'token'     => $apiKey,
                    'sender_id' => $senderId,
                    'senderid'  => $senderId,
                    'recipient' => $formattedNumbers,
                    'to'        => $formattedNumbers,
                    'number'    => $formattedNumbers,
                    'msisdn'    => $formattedNumbers,
                    'message'   => $message,
                    'text'      => $message,
                    'type'      => $isUnicode ? 'unicode' : 'text',
                ];

                try {
                    // Try JSON POST first with Bearer Token header
                    $response = Http::timeout(15)
                        ->withHeaders([
                            'Authorization' => 'Bearer ' . $apiKey,
                            'Accept'        => 'application/json',
                        ])
                        ->asJson()
                        ->post($url, $payload);

                    if (!$response->successful()) {
                        // Fallback to Form-encoded POST
                        $response = Http::timeout(15)->asForm()->post($url, $payload);
                    }
                } catch (\Throwable $httpEx) {
                    $rawCurl = self::executeCurl($url, $payload);
                    if (!empty($rawCurl)) {
                        $jsonCurl = json_decode($rawCurl, true);
                        return [
                            'success'       => !empty($jsonCurl) && !isset($jsonCurl['error']),
                            'status'        => 200,
                            'response_code' => $jsonCurl['status_code'] ?? ($jsonCurl['code'] ?? null),
                            'message'       => $jsonCurl['message'] ?? ($jsonCurl['status'] ?? $rawCurl),
                            'raw_response'  => $rawCurl,
                            'numbers'       => $formattedNumbers,
                        ];
                    }
                    throw $httpEx;
                }
            } elseif ($provider === 'bulksmsbd' || str_contains((string) $url, 'bulksmsbd.net')) {
                // BulkSMSBD API
                $apiUrl = (str_contains((string) $url, '/api/smsapi')) ? $url : 'http://bulksmsbd.net/api/smsapi';
                $smsType = $isUnicode ? 'unicode' : 'text';
                
                $payload = [
                    'api_key'  => $apiKey,
                    'type'     => $smsType,
                    'number'   => $formattedNumbers,
                    'senderid' => $senderId ?: '',
                    'message'  => $message,
                ];

                try {
                    $response = Http::timeout(12)->asForm()->post($apiUrl, $payload);
                    if (!$response->successful()) {
                        $response = Http::timeout(12)->get($apiUrl, [
                            'api_key'  => $apiKey,
                            'type'     => $smsType,
                            'number'   => $formattedNumbers,
                            'senderid' => $senderId ?: '',
                            'message'  => urlencode($message),
                        ]);
                    }
                } catch (\Throwable $httpEx) {
                    $rawCurl = self::executeCurl($apiUrl, $payload);
                    if (!empty($rawCurl)) {
                        $jsonCurl = json_decode($rawCurl, true);
                        $respCode = $jsonCurl['response_code'] ?? null;
                        return [
                            'success'       => ($respCode === 1000 || ($respCode === null && !str_contains($rawCurl, 'error'))),
                            'status'        => 200,
                            'response_code' => $respCode,
                            'message'       => $jsonCurl['success_message'] ?? ($jsonCurl['error_message'] ?? $rawCurl),
                            'raw_response'  => $rawCurl,
                            'numbers'       => $formattedNumbers,
                        ];
                    }
                    throw $httpEx;
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
                    'type'      => $isUnicode ? 'unicode' : 'text',
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
        $creds    = self::getCredentials();
        $apiKey   = $creds['api_key'];
        $senderId = $creds['sender_id'];
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
     * Supports both POST (recommended by BulkSMSBD) and GET requests.
     */
    public static function checkBalance(): array
    {
        $creds  = self::getCredentials();
        $apiKey = $creds['api_key'];
        $url    = 'http://bulksmsbd.net/api/getBalanceApi';

        try {
            // First try POST as per BulkSMSBD API documentation
            $response = Http::timeout(8)->asForm()->post($url, [
                'api_key' => $apiKey,
            ]);

            if (!$response->successful()) {
                // Fallback to GET
                $response = Http::timeout(8)->get($url, [
                    'api_key' => $apiKey,
                ]);
            }

            $json = $response->json();
            $balance = $json['balance'] ?? ($json['balance_sms'] ?? null);

            return [
                'success'       => $response->successful() && ($response->status() >= 200 && $response->status() < 300),
                'status'        => $response->status(),
                'balance'       => $balance,
                'response_code' => $json['response_code'] ?? null,
                'data'          => $json ?: $response->body(),
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Alias helper for checkBalance().
     */
    public static function getBalance(): array
    {
        return self::checkBalance();
    }

    /**
     * Send password reset OTP SMS.
     */
    public static function sendPasswordResetOtp(string $phone, string $otpCode, string $resetUrl): array
    {
        $message = "Idea Prokashon: Your password reset OTP is {$otpCode} (Valid for 30 mins). Reset link: {$resetUrl}";
        return self::send($phone, $message);
    }

    /**
     * Send mobile verification OTP SMS for registration.
     */
    public static function sendVerificationOtp(string $phone, string $otpCode): array
    {
        $message = "Idea Prokashon: Your verification code is {$otpCode} (Valid for 15 mins). Do not share this code.";
        return self::send($phone, $message);
    }

    /**
     * Send login 2FA security OTP SMS.
     */
    public static function sendLoginOtp(string $phone, string $otpCode): array
    {
        $message = "Idea Prokashon: Your security login OTP is {$otpCode} (Valid for 5 mins).";
        return self::send($phone, $message);
    }

    /**
     * Send order confirmation/COD OTP SMS.
     */
    public static function sendOrderOtp(string $phone, string $otpCode, ?string $orderNumber = null): array
    {
        $orderLabel = $orderNumber ? " #{$orderNumber}" : '';
        $message = "Idea Prokashon: Your OTP to confirm book order{$orderLabel} is {$otpCode}.";
        return self::send($phone, $message);
    }

    /**
     * Send order status update SMS.
     */
    public static function sendOrderNotification(string $phone, string $orderNumber, string $statusText): array
    {
        $trackUrl = url('/track-order');
        $message = "Idea Prokashon: Your order #{$orderNumber} is {$statusText}. Track here: {$trackUrl}";
        return self::send($phone, $message);
    }

    /**
     * Send author royalty or honorarium payout SMS.
     */
    public static function sendRoyaltyNotification(string $phone, float $amount, ?string $note = null): array
    {
        $amt = number_format($amount, 2);
        $period = $note ?: 'Current Cycle';
        $message = "Idea Prokashon: Dear Author, your royalty payment of BDT {$amt} has been processed ({$period}).";
        return self::send($phone, $message);
    }

    /**
     * Native cURL executor helper for BulkSMSBD.
     */
    public static function executeCurl(string $url, array $data): string
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 12);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response ?: '';
    }
}

