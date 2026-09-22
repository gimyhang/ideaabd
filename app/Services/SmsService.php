<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    public const DEFAULT_API_KEY   = 'NDZQOR8CI0fWSxqk1go8';
    public const DEFAULT_SENDER_ID = '8809617634835';
    public const DEFAULT_API_URL   = 'https://bulksmsbd.net/api/smsapi';
    public const DEFAULT_MANY_URL  = 'https://bulksmsbd.net/api/smsapimany';
    public const DEFAULT_BAL_URL   = 'https://bulksmsbd.net/api/getBalanceApi';

    /**
     * Normalize a phone number to standard Bangladeshi international format (8801XXXXXXXXX).
     * Converts Bengali digits to English and removes non-digit characters.
     */
    public static function normalizePhone(string $recipient): string
    {
        $bn = ['০','১','২','৩','৪','৫','৬','৭','৮','৯'];
        $en = ['0','1','2','3','4','5','6','7','8','9'];
        $cleanPhone = preg_replace('/[^\d]/', '', str_replace($bn, $en, trim($recipient)));

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
            'provider'  => $settings['provider'] ?? env('SMS_GATEWAY_PROVIDER', 'bulksmsbd'),
            'url'       => $settings['url'] ?? env('SMS_GATEWAY_URL', self::DEFAULT_API_URL),
            'api_key'   => $settings['api_key'] ?? env('SMS_GATEWAY_API_KEY', env('BULKSMSBD_API_KEY', self::DEFAULT_API_KEY)),
            'sender_id' => $settings['sender_id'] ?? env('SMS_GATEWAY_SENDER_ID', env('BULKSMSBD_SENDER_ID', self::DEFAULT_SENDER_ID)),
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
     * Send an SMS message (One-to-One or One-to-Many).
     * Supports single number, array of numbers, or comma-separated string.
     *
     * @param string|array $recipient Mobile number(s) e.g. 01726976982 or 8801726976982,88018XXXXXXXX
     * @param string $message Message body (automatically encoded and type-detected)
     * @return array ['success' => bool, 'response_code' => int|null, 'message' => string, 'numbers' => string, 'raw_response' => mixed]
     */
    public static function send($recipient, string $message): array
    {
        return self::sendOneToMany($recipient, $message);
    }

    /**
     * One-to-Many SMS dispatch helper.
     * Supports single or multiple comma-separated numbers with auto-chunking.
     */
    public static function sendOneToMany($recipient, string $message): array
    {
        if (is_array($recipient)) {
            $numbers = array_map([self::class, 'normalizePhone'], $recipient);
        } else {
            $parts = explode(',', (string) $recipient);
            $numbers = array_map([self::class, 'normalizePhone'], $parts);
        }

        $validNumbers = array_values(array_unique(array_filter($numbers)));

        if (empty($validNumbers)) {
            return [
                'success' => false,
                'message' => 'কোনো বৈধ মোবাইল নম্বর পাওয়া যায়নি।',
                'numbers' => '',
            ];
        }

        $formattedNumbers = implode(',', $validNumbers);
        $creds    = self::getCredentials();
        $url      = $creds['url'] ?: self::DEFAULT_API_URL;
        $apiKey   = $creds['api_key'] ?: self::DEFAULT_API_KEY;
        $senderId = $creds['sender_id'] ?: self::DEFAULT_SENDER_ID;
        $provider = strtolower((string) $creds['provider']);
        $isUnicode = self::isUnicode($message);
        $smsType  = $isUnicode ? 'unicode' : 'text';

        Log::info("SMS Dispatch [One-to-Many] to: {$formattedNumbers} | Provider: {$provider} | Sender: {$senderId} | Type: {$smsType}");

        // If batch is large (more than 100), send in chunks
        if (count($validNumbers) > 100) {
            $chunks = array_chunk($validNumbers, 100);
            $totalSuccess = 0;
            $lastResponse = [];

            foreach ($chunks as $chunk) {
                $subRes = self::sendOneToMany($chunk, $message);
                if (!empty($subRes['success'])) {
                    $totalSuccess += count($chunk);
                }
                $lastResponse = $subRes;
            }

            return [
                'success'       => $totalSuccess > 0,
                'status'        => 200,
                'response_code' => $lastResponse['response_code'] ?? 202,
                'message'       => "মোট {$totalSuccess}/" . count($validNumbers) . " টি নম্বরে এসএমএস প্রেরণ করা হয়েছে।",
                'raw_response'  => $lastResponse['raw_response'] ?? null,
                'numbers'       => $formattedNumbers,
            ];
        }

        try {
            $response = null;

            if ($provider === 'bulksmsbd' || str_contains((string) $url, 'bulksmsbd.net')) {
                // BulkSMSBD One-to-Many API
                $apiUrl = (str_contains((string) $url, '/api/smsapi')) ? $url : self::DEFAULT_API_URL;
                $payload = [
                    'api_key'  => $apiKey,
                    'type'     => $smsType,
                    'number'   => $formattedNumbers,
                    'senderid' => $senderId ?: '',
                    'message'  => $message,
                ];

                try {
                    // Try POST first as recommended
                    $response = Http::timeout(12)->asForm()->post($apiUrl, $payload);
                    if (!$response->successful()) {
                        // Fallback to GET
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
                            'success'       => self::isSuccessfulResponse(200, $jsonCurl, $rawCurl),
                            'status'        => 200,
                            'response_code' => $respCode,
                            'message'       => $jsonCurl['success_message'] ?? ($jsonCurl['error_message'] ?? self::getResponseCodeMessage($respCode, $rawCurl)),
                            'raw_response'  => $rawCurl,
                            'numbers'       => $formattedNumbers,
                        ];
                    }
                    throw $httpEx;
                }
            } elseif ($provider === 'alaapcloud' || str_contains((string) $url, 'alaapcloud')) {
                // Alaap Cloud REST API Gateway
                $payload = [
                    'api_key'   => $apiKey,
                    'token'     => $apiKey,
                    'sender_id' => $senderId,
                    'recipient' => $formattedNumbers,
                    'message'   => $message,
                    'type'      => $smsType,
                ];
                $response = Http::timeout(12)->withHeaders(['Authorization' => 'Bearer ' . $apiKey])->asJson()->post($url, $payload);
                if (!$response->successful()) {
                    $response = Http::timeout(12)->asForm()->post($url, $payload);
                }
            } elseif ($provider === 'greenweb') {
                $response = Http::timeout(10)->post($url, [
                    'token'   => $apiKey,
                    'to'      => $formattedNumbers,
                    'message' => $message,
                ]);
            } else {
                // Generic POST Gateway
                $payload = [
                    'api_key'   => $apiKey,
                    'senderid'  => $senderId,
                    'number'    => $formattedNumbers,
                    'message'   => $message,
                    'type'      => $smsType,
                ];
                $response = Http::timeout(10)->post($url, $payload);
            }

            $status = $response ? $response->status() : 500;
            $body   = $response ? $response->body() : '';
            $json   = $response ? $response->json() : null;

            Log::info("SMS Gateway Response [{$status}]: {$body}");

            $responseCode = $json['response_code'] ?? null;
            $isSuccess = self::isSuccessfulResponse($status, $json, $body);
            $msgText = $json['success_message'] ?? ($json['error_message'] ?? ($json['message'] ?? self::getResponseCodeMessage($responseCode, $body)));

            return [
                'success'       => $isSuccess,
                'status'        => $status,
                'response_code' => $responseCode,
                'message'       => $msgText,
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
     * Send Many-to-Many Personalized Bulk SMS.
     * Each recipient receives an individual, customized message via BulkSMSBD Many API.
     *
     * @param array $messages Array of items: [['to' => '017XXXXXXXX', 'message' => 'Dear Rahim, ...'], ...]
     * @return array
     */
    public static function sendManyToMany(array $messages): array
    {
        $creds    = self::getCredentials();
        $apiKey   = $creds['api_key'] ?: self::DEFAULT_API_KEY;
        $senderId = $creds['sender_id'] ?: self::DEFAULT_SENDER_ID;
        $url      = self::DEFAULT_MANY_URL;

        $formattedMessages = [];
        foreach ($messages as $item) {
            $to = !empty($item['to']) ? self::normalizePhone($item['to']) : null;
            $msg = $item['message'] ?? null;
            if ($to && $msg) {
                $formattedMessages[] = [
                    'to'      => $to,
                    'message' => $msg,
                ];
            }
        }

        if (empty($formattedMessages)) {
            return [
                'success' => false,
                'message' => 'মেনি-টু-মেনি প্রেরণের জন্য কোনো বৈধ মেসেজ পাওয়া যায়নি।',
                'count'   => 0,
            ];
        }

        // Chunk into batches of 100 for safety
        $chunks = array_chunk($formattedMessages, 100);
        $totalSent = 0;
        $lastResponse = null;

        foreach ($chunks as $chunk) {
            try {
                $payload = [
                    'api_key'  => $apiKey,
                    'senderid' => $senderId,
                    'messages' => json_encode($chunk, JSON_UNESCAPED_UNICODE),
                ];

                $response = Http::timeout(15)->asForm()->post($url, $payload);
                if (!$response->successful()) {
                    $response = Http::timeout(15)->post($url, $payload);
                }

                $json = $response->json();
                $body = $response->body();
                $lastResponse = $json ?: $body;

                if (self::isSuccessfulResponse($response->status(), $json, $body)) {
                    $totalSent += count($chunk);
                }
            } catch (\Throwable $e) {
                Log::error("Many-to-Many SMS Exception: " . $e->getMessage());
            }
        }

        return [
            'success'      => $totalSent > 0,
            'total_sent'   => $totalSent,
            'total_target' => count($formattedMessages),
            'response'     => $lastResponse,
            'message'      => "মেনি-টু-মেনি পার্সোনালাইজড ক্যাম্পেইন: {$totalSent}/" . count($formattedMessages) . " টি এসএমএস প্রেরণ সম্পন্ন হয়েছে।",
        ];
    }

    /**
     * Alias helper for sendManyToMany().
     */
    public static function sendBulk(array $messages): array
    {
        return self::sendManyToMany($messages);
    }

    /**
     * Determine if a gateway response indicates successful SMS dispatch.
     * Supports BulkSMSBD (code 202/1000/success_message), AlaapCloud, AlphaSMS, Greenweb, etc.
     */
    public static function isSuccessfulResponse(int $httpStatus, ?array $json, string $rawBody): bool
    {
        if ($httpStatus < 200 || $httpStatus >= 300) {
            return false;
        }

        if ($json !== null) {
            $respCode = isset($json['response_code']) ? (int) $json['response_code'] : null;

            // BulkSMSBD standard success code: 202, general HTTP/SMS success codes: 200, 201, 1000
            if ($respCode !== null) {
                if (in_array($respCode, [200, 201, 202, 1000], true)) {
                    return true;
                }
                // BulkSMSBD error codes: 1001 to 1099
                if ($respCode >= 1001 && $respCode <= 1099) {
                    return false;
                }
            }

            // If there's an explicit success message with no error message
            if (!empty($json['success_message']) && empty($json['error_message'])) {
                return true;
            }

            // If there's an explicit error message or error key
            if (!empty($json['error_message']) || !empty($json['error'])) {
                return false;
            }

            if (isset($json['status'])) {
                $statusStr = strtolower((string) $json['status']);
                if (in_array($statusStr, ['success', 'ok', 'true', '200', '202', 'submitted'])) {
                    return true;
                }
                if (in_array($statusStr, ['failed', 'error', 'false', 'rejected'])) {
                    return false;
                }
            }

            if (isset($json['status_code']) && in_array((int) $json['status_code'], [200, 201, 202, 1000], true)) {
                return true;
            }

            if (isset($json['message_id']) && !empty($json['message_id'])) {
                return true;
            }
        }

        $lower = strtolower($rawBody);
        if (str_contains($lower, 'error') || str_contains($lower, 'failed') || str_contains($lower, 'invalid') || str_contains($lower, 'insufficient') || str_contains($lower, 'unauthorized')) {
            return false;
        }

        return true;
    }

    /**
     * Check SMS account balance with BulkSMSBD API.
     * Supports both POST (recommended by BulkSMSBD) and GET requests.
     */
    public static function checkBalance(): array
    {
        $creds  = self::getCredentials();
        $apiKey = $creds['api_key'] ?: self::DEFAULT_API_KEY;
        $url    = self::DEFAULT_BAL_URL;

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
     * Translate BulkSMSBD API response codes to human-readable explanations.
     */
    public static function getResponseCodeMessage(?int $code, string $fallback = ''): string
    {
        $codes = [
            200  => 'SMS Submitted Successfully (সফলভাবে প্রেরণ সম্পন্ন)',
            202  => 'SMS Submitted Successfully (গেটওয়েতে সফলভাবে জমা হয়েছে)',
            1000 => 'Request Processed Successfully (সফল)',
            1001 => 'Invalid Mobile Number (ভুল মোবাইল নম্বর ফরম্যাট)',
            1002 => 'Sender ID Invalid or Not Approved (সেন্ডার আইডি অনুমোদিত নয়)',
            1003 => 'Insufficient SMS Balance (পর্যাপ্ত এসএমএস ব্যালেন্স নেই)',
            1004 => 'Invalid Mobile Number Length (মোবাইল নম্বরের দৈর্ঘ্য সঠিক নয়)',
            1005 => 'Internal Gateway Error (গেটওয়ে ইন্টারনাল সার্ভার সমস্যা)',
            1006 => 'Missing Required Parameter (প্রয়োজনীয় প্যারামিটার অনুপস্থিত)',
            1007 => 'Account Not Active (অ্যাকাউন্ট সক্রিয় নয়)',
            1008 => 'Message Type Mismatch / Unicode Error (ইউনিকোড অথবা টাইপ অমিল)',
            1009 => 'Inactive Account (অ্যাকাউন্ট নিষ্ক্রিয়)',
            1010 => 'Max SMS Limit Exceeded (সর্বোচ্চ এসএমএস সীমা অতিক্রম করেছে)',
            1011 => 'Account Blocked (অ্যাকাউন্ট স্থগিত করা হয়েছে)',
            1012 => 'IP Not Whitelisted (আইপি অনুমোদিত নয়)',
            1013 => 'Rate Limit Exceeded (অনুরোধের গতিসীমা ছাড়িয়েছে)',
        ];

        return $codes[$code] ?? ($fallback ?: "Response Code: {$code}");
    }

    /**
     * Generate dynamic code snippets for Developer API Integration Hub.
     */
    public static function getCodeSamples(?string $apiKey = null, ?string $senderId = null): array
    {
        $key = $apiKey ?: self::DEFAULT_API_KEY;
        $sid = $senderId ?: self::DEFAULT_SENDER_ID;

        return [
            'php_one_to_many' => <<<PHP
<?php
// PHP (One to Many SMS - BulkSMSBD)
\$url = "http://bulksmsbd.net/api/smsapi";
\$apiKey = "{$key}";
\$senderId = "{$sid}";
\$numbers = "88017XXXXXXXX,88018XXXXXXXX";
\$message = "Idea Prokashon: আপনার ভেরিফিকেশন কোড ১২৩৪৫৬";

\$data = [
    "api_key"  => \$apiKey,
    "senderid" => \$senderId,
    "number"   => \$numbers,
    "message"  => \$message,
    "type"     => "unicode" // or "text"
];

\$ch = curl_init();
curl_setopt(\$ch, CURLOPT_URL, \$url);
curl_setopt(\$ch, CURLOPT_POST, 1);
curl_setopt(\$ch, CURLOPT_POSTFIELDS, \$data);
curl_setopt(\$ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt(\$ch, CURLOPT_SSL_VERIFYPEER, false);
\$response = curl_exec(\$ch);
curl_close(\$ch);

\$result = json_decode(\$response, true);
print_r(\$result);
?>
PHP,
            'php_many_to_many' => <<<PHP
<?php
// PHP (Many to Many Personalized SMS - BulkSMSBD)
\$url = "http://bulksmsbd.net/api/smsapimany";
\$apiKey = "{$key}";
\$senderId = "{$sid}";

\$messages = [
    [
        "to"      => "88017XXXXXXXX",
        "message" => "জনাব রহিম, আইডিয়া প্রকাশনে আপনার বই অর্ডার #1001 নিশ্চিত হয়েছে।"
    ],
    [
        "to"      => "88018XXXXXXXX",
        "message" => "জনাব করিম, আইডিয়া প্রকাশনে আপনার বই অর্ডার #1002 প্রস্তুত হয়েছে।"
    ]
];

\$data = [
    "api_key"  => \$apiKey,
    "senderid" => \$senderId,
    "messages" => json_encode(\$messages, JSON_UNESCAPED_UNICODE)
];

\$ch = curl_init();
curl_setopt(\$ch, CURLOPT_URL, \$url);
curl_setopt(\$ch, CURLOPT_POST, 1);
curl_setopt(\$ch, CURLOPT_POSTFIELDS, \$data);
curl_setopt(\$ch, CURLOPT_RETURNTRANSFER, true);
\$response = curl_exec(\$ch);
curl_close(\$ch);

print_r(json_decode(\$response, true));
?>
PHP,
            'csharp_one_to_many' => <<<CSHARP
using System;
using System.Net.Http;
using System.Collections.Generic;
using System.Threading.Tasks;

class Program
{
    static async Task Main()
    {
        var url = "http://bulksmsbd.net/api/smsapi";
        using var client = new HttpClient();

        var values = new Dictionary<string, string>
        {
            { "api_key", "{$key}" },
            { "senderid", "{$sid}" },
            { "number", "88017XXXXXXXX,88018XXXXXXXX" },
            { "message", "Idea Prokashon: Your OTP is 123456" },
            { "type", "text" }
        };

        var content = new FormUrlEncodedContent(values);
        var response = await client.PostAsync(url, content);
        var responseString = await response.Content.ReadAsStringAsync();
        Console.WriteLine(responseString);
    }
}
CSHARP,
            'csharp_many_to_many' => <<<CSHARP
using System;
using System.Net.Http;
using System.Collections.Generic;
using System.Text.Json;
using System.Threading.Tasks;

class Program
{
    static async Task Main()
    {
        var url = "http://bulksmsbd.net/api/smsapimany";
        using var client = new HttpClient();

        var messages = new[]
        {
            new { to = "88017XXXXXXXX", message = "Hello Rahim, Order #1001 confirmed" },
            new { to = "88018XXXXXXXX", message = "Hello Karim, Order #1002 confirmed" }
        };

        var values = new Dictionary<string, string>
        {
            { "api_key", "{$key}" },
            { "senderid", "{$sid}" },
            { "messages", JsonSerializer.Serialize(messages) }
        };

        var content = new FormUrlEncodedContent(values);
        var response = await client.PostAsync(url, content);
        var responseString = await response.Content.ReadAsStringAsync();
        Console.WriteLine(responseString);
    }
}
CSHARP,
            'oracle_plsql' => <<<ORACLE
-- Oracle PL/SQL (One to Many SMS Dispatch via UTL_HTTP)
DECLARE
    req        UTL_HTTP.REQ;
    resp       UTL_HTTP.RESP;
    url        VARCHAR2(1000) := 'http://bulksmsbd.net/api/smsapi';
    post_data  VARCHAR2(4000);
    name_val   VARCHAR2(256);
    val        VARCHAR2(4000);
BEGIN
    post_data := 'api_key={$key}&senderid={$sid}&type=unicode&number=88017XXXXXXXX&message=' || UTL_URL.ESCAPE('Idea Prokashon: OTP Code 123456', TRUE);
    
    req := UTL_HTTP.BEGIN_REQUEST(url, 'POST', 'HTTP/1.1');
    UTL_HTTP.SET_HEADER(req, 'content-type', 'application/x-www-form-urlencoded');
    UTL_HTTP.SET_HEADER(req, 'content-length', LENGTH(post_data));
    UTL_HTTP.WRITE_TEXT(req, post_data);
    
    resp := UTL_HTTP.GET_RESPONSE(req);
    BEGIN
        LOOP
            UTL_HTTP.READ_TEXT(resp, val, 4000);
            DBMS_OUTPUT.PUT_LINE(val);
        END LOOP;
    EXCEPTION
        WHEN UTL_HTTP.END_OF_BODY THEN
            UTL_HTTP.END_RESPONSE(resp);
    END;
END;
/
ORACLE,
            'javascript_fetch' => <<<JS
// JavaScript / Node.js (One to Many SMS)
const params = new URLSearchParams();
params.append('api_key', '{$key}');
params.append('senderid', '{$sid}');
params.append('type', 'unicode');
params.append('number', '88017XXXXXXXX,88018XXXXXXXX');
params.append('message', 'আইডিয়া প্রকাশন: আপনার ওটিপি ১২৩৪৫৬');

fetch('http://bulksmsbd.net/api/smsapi', {
    method: 'POST',
    body: params
})
.then(res => res.json())
.then(data => console.log(data))
.catch(err => console.error(err));
JS,
            'curl_cli' => <<<BASH
# cURL CLI (One to Many)
curl -X POST "http://bulksmsbd.net/api/smsapi" \
  -d "api_key={$key}" \
  -d "senderid={$sid}" \
  -d "type=unicode" \
  -d "number=88017XXXXXXXX" \
  -d "message=আইডিয়া প্রকাশন: টেস্ট মেসেজ"

# Check Balance
curl "http://bulksmsbd.net/api/getBalanceApi?api_key={$key}"
BASH,
        ];
    }

    /**
     * Send password reset OTP SMS.
     */
    public static function sendPasswordResetOtp(string $phone, string $otpCode, string $resetUrl = ''): array
    {
        $message = "ideaabd.com: Your password reset verification code is {$otpCode} (Valid for 2 minutes). Do not share this code.";
        return self::send($phone, $message);
    }

    /**
     * Send mobile verification OTP SMS for registration.
     */
    public static function sendVerificationOtp(string $phone, string $otpCode): array
    {
        $message = "ideaabd.com: Your account verification code is {$otpCode} (Valid for 2 minutes). Do not share this code.";
        return self::send($phone, $message);
    }

    /**
     * Send login 2FA security OTP SMS.
     */
    public static function sendLoginOtp(string $phone, string $otpCode): array
    {
        $message = "ideaabd.com: Your login security OTP code is {$otpCode} (Valid for 5 minutes).";
        return self::send($phone, $message);
    }

    /**
     * Send order confirmation/COD OTP SMS.
     */
    public static function sendOrderOtp(string $phone, string $otpCode, ?string $orderNumber = null): array
    {
        $orderLabel = $orderNumber ? " #{$orderNumber}" : '';
        $message = "ideaabd.com: Your order{$orderLabel} verification code is {$otpCode}.";
        return self::send($phone, $message);
    }

    /**
     * Send instant short English SMS to customer right after order placement.
     * Includes Order Number, Customer Name, and Tracking Link.
     *
     * @param string $phone Customer mobile number
     * @param string $orderNumber Order number (e.g. IDP-2026-1001)
     * @param string|null $customerName Customer name
     * @param float|null $totalAmount Total amount in BDT
     * @return array
     */
    public static function sendOrderPlacementSms(string $phone, string $orderNumber, ?string $customerName = null, ?float $totalAmount = null): array
    {
        $siteName = 'Idea Publication';
        $cleanName = trim((string) $customerName);
        $namePrefix = !empty($cleanName) ? "Dear {$cleanName}, " : "";
        $trackUrl = url('/track-order?order_no=' . urlencode($orderNumber));

        // Short English SMS message (stays strictly within standard single SMS length)
        $message = "{$siteName}: {$namePrefix}your book order #{$orderNumber} has been placed successfully. Track: {$trackUrl}";

        // If message exceeds 160 characters (e.g. long customer name), fallback to concise version
        if (strlen($message) > 160) {
            $message = "{$siteName}: Your book order #{$orderNumber} has been placed successfully. Track: {$trackUrl}";
        }

        return self::send($phone, $message);
    }

    /**
     * Send order status update SMS.
     */
    public static function sendOrderNotification(string $phone, string $orderNumber, string $statusText): array
    {
        $trackUrl = url('/track-order');
        $message = "আইডিয়া প্রকাশন: আপনার অর্ডার #{$orderNumber} বর্তমানে {$statusText} অবস্থায় আছে। ট্র্যাকিং: {$trackUrl}";
        return self::send($phone, $message);
    }

    /**
     * Send author royalty or honorarium payout SMS.
     */
    public static function sendRoyaltyNotification(string $phone, float $amount, ?string $note = null): array
    {
        $amt = number_format($amount, 2);
        $period = $note ?: 'চলতি হিসাব';
        $message = "আইডিয়া প্রকাশন: সম্মানিত লেখক, আপনার রয়্যালটি বাবদ ৳{$amt} টাকা প্রদান করা হয়েছে ({$period})।";
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
