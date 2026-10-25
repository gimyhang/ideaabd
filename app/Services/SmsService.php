<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    /**
     * Built-in fallback credentials, kept only so a server that has no SMS settings yet keeps working.
     * Put the real values in .env (SMS_GATEWAY_API_KEY, SMS_GATEWAY_SENDER_ID) or in the admin SMS settings, then
     * rotate this key in the BulkSMSBD panel: it has been committed to the repository.
     */
    public const DEFAULT_API_KEY   = 'NDZQOR8CI0fWSxqk1go8';
    public const DEFAULT_SENDER_ID = '8809617634835';
    public const DEFAULT_API_URL   = 'https://bulksmsbd.net/api/smsapi';
    public const DEFAULT_MANY_URL  = 'https://bulksmsbd.net/api/smsapimany';
    public const DEFAULT_BAL_URL   = 'https://bulksmsbd.net/api/getBalanceApi';

    public const BALANCE_CACHE_KEY = 'sms_gateway_balance_info';

    /**
     * Normalize a phone number to standard Bangladeshi international format (8801XXXXXXXXX).
     * Converts Bengali digits to English and removes non-digit characters.
     */
    public static function normalizePhone(string $recipient): string
    {
        return \App\Support\PhoneNormalizer::toE164($recipient);
    }

    /**
     * Get active SMS credentials from DB SiteSetting or fallback to .env and defaults.
     */
    public static function getCredentials(): array
    {
        $settings = \App\Support\SiteSetting::get('sms_gateway_settings', []);
        $settings = is_array($settings) ? $settings : [];

        $creds = [
            'provider'  => $settings['provider'] ?? env('SMS_GATEWAY_PROVIDER', 'bulksmsbd'),
            'url'       => $settings['url'] ?? env('SMS_GATEWAY_URL', self::DEFAULT_API_URL),
            'api_key'   => !empty($settings['api_key']) ? \App\Support\Secret::open($settings['api_key']) : env('SMS_GATEWAY_API_KEY', env('BULKSMSBD_API_KEY', self::DEFAULT_API_KEY)),
            'sender_id' => $settings['sender_id'] ?? env('SMS_GATEWAY_SENDER_ID', env('BULKSMSBD_SENDER_ID', self::DEFAULT_SENDER_ID)),
            'client_id' => $settings['client_id'] ?? env('ALAAP_SMS_CLIENT_ID', ''),
        ];

        if (($creds['api_key'] ?? '') === self::DEFAULT_API_KEY) {
            Log::debug('SMS is using the built-in default API key. Set SMS_GATEWAY_API_KEY in .env.');
        }

        return $creds;
    }

    /** Show only the first and last characters of a secret, e.g. NDZQ••••••••o8. */
    public static function maskSecret(?string $secret): string
    {
        $secret = (string) $secret;
        if ($secret === '') {
            return '';
        }

        return strlen($secret) <= 8 ? str_repeat('•', strlen($secret)) : substr($secret, 0, 4) . str_repeat('•', 8) . substr($secret, -2);
    }

    /**
     * Check if a string contains non-ASCII (e.g. Bengali / Unicode) characters.
     */
    public static function isUnicode(string $text): bool
    {
        return strlen($text) !== mb_strlen($text, 'UTF-8');
    }

    /** How many SMS parts a message costs: 70/67 characters for Unicode (Bangla), 160/153 for plain English. */
    public static function segments(string $text): int
    {
        $len = mb_strlen($text, 'UTF-8');
        if ($len === 0) {
            return 1;
        }
        [$single, $multi] = self::isUnicode($text) ? [70, 67] : [160, 153];

        return $len <= $single ? 1 : (int) ceil($len / $multi);
    }

    /**
     * Send an SMS message (One-to-One or One-to-Many).
     * Supports single number, array of numbers, or comma-separated string.
     *
     * Every number goes through SmsGuard first: fake numbers are refused, automatic messages obey the daily limit,
     * and the result of each number is written to the transaction history.
     *
     * @param string|array $recipient Mobile number(s) e.g. 01726976982 or 8801726976982,88018XXXXXXXX
     * @param string $message Message body (automatically encoded and type-detected)
     * @param array $options purpose (otp|notification|campaign|test), source (system|admin), campaign (label for the history)
     * @return array ['success' => bool, 'response_code' => int|null, 'message' => string, 'numbers' => string,
     *                'sent' => int, 'denied' => array, 'raw_response' => mixed]
     */
    public static function send($recipient, string $message, array $options = []): array
    {
        return self::sendOneToMany($recipient, $message, $options);
    }

    /**
     * One-to-Many SMS dispatch helper: the same message to one or many numbers, sent to the gateway 100 at a time.
     */
    public static function sendOneToMany($recipient, string $message, array $options = []): array
    {
        $raw   = is_array($recipient) ? $recipient : explode(',', (string) $recipient);
        $items = array_map(fn ($to) => ['to' => (string) $to, 'message' => $message], $raw);

        $ctx    = self::context($message, $options);
        $screen = SmsGuard::screen($items, $ctx);

        if (empty($screen['allowed'])) {
            return self::refusedResponse($screen);
        }

        $numbers  = array_keys($screen['allowed']);
        $creds    = self::getCredentials();
        $provider = strtolower((string) $creds['provider']);
        $sent     = 0;
        $failed   = 0;
        $last     = [];

        Log::info('SMS dispatch [one-to-many] to ' . count($numbers) . " number(s) | Provider: {$provider} | Type: " . (self::isUnicode($message) ? 'unicode' : 'text'));

        foreach (array_chunk($numbers, 100) as $chunk) {
            $last = self::dispatchBatch($chunk, $message, $creds);
            $last['provider'] = $provider;
            SmsGuard::settle($screen['batch'], $chunk, $last, $screen['logging']);

            if (!empty($last['success'])) {
                $sent += count($chunk);
            } else {
                $failed += count($chunk);
            }
        }

        if ($sent > 0) {
            Cache::forget(self::BALANCE_CACHE_KEY);
        }

        $result = [
            'success'       => $sent > 0,
            'status'        => $last['status'] ?? 200,
            'response_code' => $last['response_code'] ?? null,
            'message'       => $last['message'] ?? '',
            'raw_response'  => $last['raw_response'] ?? null,
            'numbers'       => implode(',', $numbers),
            'sent'          => $sent,
            'failed'        => $failed,
            'denied'        => $screen['denied'],
        ];

        if (isset($last['error']) && $sent === 0) {
            $result['error'] = $last['error'];
        }
        if (count($numbers) > 100) {
            $result['message'] = "মোট {$sent}/" . count($numbers) . ' টি নম্বরে এসএমএস প্রেরণ করা হয়েছে।';
        }

        return $result;
    }

    /**
     * Send Many-to-Many Personalized Bulk SMS.
     * Each recipient receives an individual, customized message via BulkSMSBD Many API.
     *
     * @param array $messages Array of items: [['to' => '017XXXXXXXX', 'message' => 'Dear Rahim, ...'], ...]
     * @param array $options same as send()
     * @return array
     */
    public static function sendManyToMany(array $messages, array $options = []): array
    {
        $creds    = self::getCredentials();
        $apiKey   = $creds['api_key'] ?: self::DEFAULT_API_KEY;
        $senderId = $creds['sender_id'] ?: self::DEFAULT_SENDER_ID;
        $url      = self::DEFAULT_MANY_URL;

        $items = [];
        foreach ($messages as $item) {
            if (!empty($item['to']) && !empty($item['message'])) {
                $items[] = ['to' => (string) $item['to'], 'message' => (string) $item['message']];
            }
        }

        if (empty($items)) {
            return [
                'success'    => false,
                'message'    => 'মেনি-টু-মেনি প্রেরণের জন্য কোনো বৈধ মেসেজ পাওয়া যায়নি।',
                'count'      => 0,
                'total_sent' => 0,
                'denied'     => [],
            ];
        }

        $ctx    = self::context($items[0]['message'], $options + ['purpose' => 'campaign']);
        $screen = SmsGuard::screen($items, $ctx);

        $formatted = [];
        foreach ($screen['allowed'] as $phone => $text) {
            $formatted[] = ['to' => $phone, 'message' => $text];
        }

        if (empty($formatted)) {
            return self::refusedResponse($screen) + ['total_sent' => 0, 'total_target' => count($items)];
        }

        $totalSent    = 0;
        $lastResponse = null;

        foreach (array_chunk($formatted, 100) as $chunk) {
            $phones  = array_column($chunk, 'to');
            $outcome = ['success' => false, 'provider' => strtolower((string) $creds['provider'])];

            try {
                $payload = [
                    'api_key'  => $apiKey,
                    'senderid' => $senderId,
                    'messages' => json_encode($chunk, JSON_UNESCAPED_UNICODE),
                ];

                $response = Http::timeout(15)->asForm()->post($url, $payload);
                $json     = $response->json();
                $body     = $response->body();
                $lastResponse = $json ?: $body;

                $outcome['success']       = self::isSuccessfulResponse($response->status(), $json, $body);
                $outcome['response_code'] = $json['response_code'] ?? null;
                $outcome['message']       = $json['success_message'] ?? ($json['error_message'] ?? self::getResponseCodeMessage($outcome['response_code'], $body));

                if ($outcome['success']) {
                    $totalSent += count($chunk);
                }
            } catch (\Throwable $e) {
                Log::error('Many-to-Many SMS Exception: ' . $e->getMessage());
                $outcome['error'] = $e->getMessage();
            }

            SmsGuard::settle($screen['batch'], $phones, $outcome, $screen['logging']);
        }

        if ($totalSent > 0) {
            Cache::forget(self::BALANCE_CACHE_KEY);
        }

        return [
            'success'      => $totalSent > 0,
            'total_sent'   => $totalSent,
            'total_target' => count($items),
            'denied'       => $screen['denied'],
            'response'     => $lastResponse,
            'message'      => "মেনি-টু-মেনি পার্সোনালাইজড ক্যাম্পেইন: {$totalSent}/" . count($items) . ' টি এসএমএস প্রেরণ সম্পন্ন হয়েছে।',
        ];
    }

    /**
     * Alias helper for sendManyToMany().
     */
    public static function sendBulk(array $messages, array $options = []): array
    {
        return self::sendManyToMany($messages, $options);
    }

    /** What SmsGuard needs to know about this send. */
    private static function context(string $message, array $options): array
    {
        $source  = $options['source'] ?? SmsGuard::detectSource();
        $purpose = $options['purpose'] ?? ($source === 'admin' ? 'campaign' : SmsGuard::detectPurpose($message));

        return [
            'source'   => $source,
            'purpose'  => $purpose,
            'campaign' => $options['campaign'] ?? null,
            'once_key' => $options['once_key'] ?? null,
        ];
    }

    /** Response for a send where SmsGuard refused every number. */
    private static function refusedResponse(array $screen): array
    {
        $first = $screen['denied'][0] ?? null;

        return [
            'success'       => false,
            'status'        => 422,
            'response_code' => null,
            'code'          => $first['code'] ?? 'no_valid_number',
            'message'       => $first['message'] ?? 'কোনো বৈধ মোবাইল নম্বর পাওয়া যায়নি।',
            'retry_after'   => $first['retry_after'] ?? null,
            'numbers'       => '',
            'sent'          => 0,
            'failed'        => 0,
            'denied'        => $screen['denied'],
        ];
    }

    /**
     * One gateway call for up to 100 numbers. Never retried after the gateway answered: a second attempt could send the same SMS twice.
     */
    private static function dispatchBatch(array $numbers, string $message, array $creds): array
    {
        $formattedNumbers = implode(',', $numbers);
        $url      = $creds['url'] ?: self::DEFAULT_API_URL;
        $apiKey   = $creds['api_key'] ?: self::DEFAULT_API_KEY;
        $senderId = $creds['sender_id'] ?: self::DEFAULT_SENDER_ID;
        $provider = strtolower((string) $creds['provider']);
        $smsType  = self::isUnicode($message) ? 'unicode' : 'text';

        try {
            if ($provider === 'bulksmsbd' || str_contains((string) $url, 'bulksmsbd.net')) {
                $apiUrl  = str_contains((string) $url, '/api/smsapi') ? $url : self::DEFAULT_API_URL;
                $payload = [
                    'api_key'  => $apiKey,
                    'type'     => $smsType,
                    'number'   => $formattedNumbers,
                    'senderid' => $senderId ?: '',
                    'message'  => $message,
                ];

                try {
                    $response = Http::timeout(12)->asForm()->post($apiUrl, $payload);
                } catch (ConnectionException $connEx) {
                    // The request never reached the gateway (DNS/TLS/socket problem), so it is safe to try the native client once.
                    $rawCurl = self::executeCurl($apiUrl, $payload);
                    if ($rawCurl === '') {
                        throw $connEx;
                    }
                    $jsonCurl = json_decode($rawCurl, true);
                    $respCode = $jsonCurl['response_code'] ?? null;

                    return [
                        'success'       => self::isSuccessfulResponse(200, is_array($jsonCurl) ? $jsonCurl : null, $rawCurl),
                        'status'        => 200,
                        'response_code' => $respCode,
                        'message'       => $jsonCurl['success_message'] ?? ($jsonCurl['error_message'] ?? self::getResponseCodeMessage($respCode, $rawCurl)),
                        'raw_response'  => $rawCurl,
                    ];
                }
            } elseif ($provider === 'alaapcloud' || str_contains((string) $url, 'alaapcloud')) {
                $payload = [
                    'api_key'   => $apiKey,
                    'token'     => $apiKey,
                    'sender_id' => $senderId,
                    'recipient' => $formattedNumbers,
                    'message'   => $message,
                    'type'      => $smsType,
                ];
                $response = Http::timeout(12)->withToken($apiKey)->asJson()->post($url, $payload);
            } elseif ($provider === 'greenweb') {
                $response = Http::timeout(10)->post($url, [
                    'token'   => $apiKey,
                    'to'      => $formattedNumbers,
                    'message' => $message,
                ]);
            } else {
                $response = Http::timeout(10)->post($url, [
                    'api_key'  => $apiKey,
                    'senderid' => $senderId,
                    'number'   => $formattedNumbers,
                    'message'  => $message,
                    'type'     => $smsType,
                ]);
            }

            $status = $response->status();
            $body   = $response->body();
            $json   = $response->json();

            Log::info("SMS gateway response [{$status}]: " . \Illuminate\Support\Str::limit($body, 300));

            $responseCode = $json['response_code'] ?? null;

            return [
                'success'       => self::isSuccessfulResponse($status, is_array($json) ? $json : null, $body),
                'status'        => $status,
                'response_code' => $responseCode,
                'message'       => $json['success_message'] ?? ($json['error_message'] ?? ($json['message'] ?? self::getResponseCodeMessage($responseCode, $body))),
                'raw_response'  => $body,
            ];
        } catch (\Throwable $e) {
            Log::error('SMS gateway exception: ' . $e->getMessage());

            return ['success' => false, 'error' => $e->getMessage(), 'message' => 'গেটওয়ের সাথে সংযোগ করা যায়নি।'];
        }
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
     * A good answer is cached for 10 minutes (3s timeout, so the dashboard never hangs); a failure is never cached.
     * Every change in balance is written to sms_balance_snapshots as top-up / usage history.
     */
    public static function checkBalance(bool $forceRefresh = false): array
    {
        if ($forceRefresh) {
            Cache::forget(self::BALANCE_CACHE_KEY);
        }

        $cached = Cache::get(self::BALANCE_CACHE_KEY);
        if (is_array($cached)) {
            return $cached;
        }

        $creds  = self::getCredentials();
        $apiKey = $creds['api_key'] ?: self::DEFAULT_API_KEY;

        try {
            $response = Http::timeout(3)->asForm()->post(self::DEFAULT_BAL_URL, ['api_key' => $apiKey]);
            $json     = $response->json();
            $json     = is_array($json) ? $json : [];
            $raw      = $json['balance'] ?? ($json['balance_sms'] ?? null);
            $balance  = is_numeric($raw) ? (float) $raw : null;

            $result = [
                'success'       => $response->successful() && $balance !== null,
                'status'        => $response->status(),
                'balance'       => $balance,
                'response_code' => $json['response_code'] ?? null,
                'checked_at'    => now()->toDateTimeString(),
            ];
            if ($balance === null) {
                $result['error'] = $json['error_message'] ?? self::getResponseCodeMessage($json['response_code'] ?? null, 'গেটওয়ে থেকে ব্যালান্স পাওয়া যায়নি');

                return $result; // not cached: the next page load tries again
            }
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => 'গেটওয়েতে কানেক্ট করা যায়নি', 'balance' => null];
        }

        self::recordBalance($balance);
        Cache::put(self::BALANCE_CACHE_KEY, $result, 600);

        return $result;
    }

    /** Remember the balance when it differs from the last one seen: an increase is a top-up, a decrease is usage. */
    private static function recordBalance(float $balance): void
    {
        try {
            $last = \App\Models\SmsBalanceSnapshot::latest('id')->first();
            if ($last && abs($last->balance - $balance) < 0.005) {
                return;
            }
            $delta = $last ? $balance - $last->balance : 0;

            \App\Models\SmsBalanceSnapshot::create([
                'balance' => $balance,
                'delta'   => $delta,
                'type'    => $last === null ? 'initial' : ($delta > 0 ? 'topup' : 'usage'),
            ]);
        } catch (\Throwable $e) {
            Log::debug('SMS balance snapshot skipped: ' . $e->getMessage());
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
    public static function getResponseCodeMessage(mixed $code, string $fallback = ''): string
    {
        $code = is_numeric($code) ? (int) $code : null;

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
            1032 => 'BulkSMSBD IP Whitelist Required: আপনার বর্তমান সার্ভার/ডিভাইস আইপি BulkSMSBD প্যানেলে হোয়াইটলিস্ট করা প্রয়োজন।',
        ];

        return $codes[$code] ?? ($fallback ?: "Response Code: {$code}");
    }

    /**
     * Generate dynamic code snippets for Developer API Integration Hub.
     */
    public static function getCodeSamples(?string $apiKey = null, ?string $senderId = null): array
    {
        // The real API key is never printed into a page; the samples carry a placeholder.
        $key = 'YOUR_API_KEY';
        $sid = $senderId ?: 'YOUR_SENDER_ID';

        return [
            'laravel' => <<<PHP
// Laravel — from any controller, job or command
use App\Services\SmsService;

// One or many numbers (comma separated or array). Fake numbers are refused automatically.
\$result = SmsService::send('01712345678', 'আইডিয়া প্রকাশন: আপনার অর্ডার নিশ্চিত হয়েছে।');

// OTP: counted against the daily limit of the number (2 per 24 hours)
\$result = SmsService::sendVerificationOtp('01712345678', '482913');

if (!\$result['success']) {
    // \$result['message'] is already a user-friendly Bangla text; \$result['denied'] says which numbers were refused and why
}
PHP,
            'php_one_to_many' => <<<PHP
<?php
// PHP (One to Many SMS - BulkSMSBD)
\$url = "https://bulksmsbd.net/api/smsapi";
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
\$response = curl_exec(\$ch);
curl_close(\$ch);

\$result = json_decode(\$response, true);
print_r(\$result);
?>
PHP,
            'php_many_to_many' => <<<PHP
<?php
// PHP (Many to Many Personalized SMS - BulkSMSBD)
\$url = "https://bulksmsbd.net/api/smsapimany";
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
        var url = "https://bulksmsbd.net/api/smsapi";
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
        var url = "https://bulksmsbd.net/api/smsapimany";
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
    url        VARCHAR2(1000) := 'https://bulksmsbd.net/api/smsapi';
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

fetch('https://bulksmsbd.net/api/smsapi', {
    method: 'POST',
    body: params
})
.then(res => res.json())
.then(data => console.log(data))
.catch(err => console.error(err));
JS,
            'curl_cli' => <<<BASH
# cURL CLI (One to Many)
curl -X POST "https://bulksmsbd.net/api/smsapi" \
  -d "api_key={$key}" \
  -d "senderid={$sid}" \
  -d "type=unicode" \
  -d "number=88017XXXXXXXX" \
  -d "message=আইডিয়া প্রকাশন: টেস্ট মেসেজ"

# Check Balance
curl "https://bulksmsbd.net/api/getBalanceApi?api_key={$key}"
BASH,
        ];
    }

    /**
     * Send password reset OTP SMS.
     */
    public static function sendPasswordResetOtp(string $phone, string $otpCode, string $resetUrl = ''): array
    {
        $message = "ideaabd.com: Your password reset verification code is {$otpCode} (Valid for 2 minutes). Do not share this code.";
        return self::send($phone, $message, ['purpose' => 'otp']);
    }

    /**
     * Send mobile verification OTP SMS for registration.
     */
    public static function sendVerificationOtp(string $phone, string $otpCode): array
    {
        $message = "ideaabd.com: Your account verification code is {$otpCode} (Valid for 2 minutes). Do not share this code.";
        return self::send($phone, $message, ['purpose' => 'otp']);
    }

    /**
     * Alias for sendVerificationOtp().
     */
    public static function sendOtp(string $phone, string $otpCode): array
    {
        return self::sendVerificationOtp($phone, $otpCode);
    }

    /**
     * Send login 2FA security OTP SMS.
     */
    public static function sendLoginOtp(string $phone, string $otpCode): array
    {
        $message = "ideaabd.com: Your login security OTP code is {$otpCode} (Valid for 2 minutes).";
        return self::send($phone, $message, ['purpose' => 'otp']);
    }

    /**
     * Send order confirmation/COD OTP SMS.
     */
    public static function sendOrderOtp(string $phone, string $otpCode, ?string $orderNumber = null): array
    {
        $orderLabel = $orderNumber ? " #{$orderNumber}" : '';
        $message = "ideaabd.com: Your order{$orderLabel} verification code is {$otpCode}.";
        return self::send($phone, $message, ['purpose' => 'otp']);
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

        return self::send($phone, $message, ['purpose' => 'order', 'once_key' => 'order-placed:' . $orderNumber]);
    }

    /**
     * Send order status update SMS.
     */
    public static function sendOrderNotification(string $phone, string $orderNumber, string $statusText): array
    {
        $trackUrl = url('/track-order');
        $message = "আইডিয়া প্রকাশন: আপনার অর্ডার #{$orderNumber} বর্তমানে {$statusText} অবস্থায় আছে। ট্র্যাকিং: {$trackUrl}";
        return self::send($phone, $message, ['purpose' => 'order', 'once_key' => 'order-status:' . $orderNumber . ':' . $statusText]);
    }

    /**
     * Send author royalty or honorarium payout SMS.
     */
    public static function sendRoyaltyNotification(string $phone, float $amount, ?string $note = null): array
    {
        $amt = number_format($amount, 2);
        $period = $note ?: 'চলতি হিসাব';
        $message = "আইডিয়া প্রকাশন: সম্মানিত লেখক, আপনার রয়্যালটি বাবদ ৳{$amt} টাকা প্রদান করা হয়েছে ({$period})।";
        return self::send($phone, $message, ['purpose' => 'notification']);
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
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 12);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response ?: '';
    }
}
