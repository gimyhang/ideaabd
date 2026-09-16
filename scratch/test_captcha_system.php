<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\CaptchaService;
use Illuminate\Support\Facades\Cache;

echo "========================================================\n";
echo "           TESTING IMAGE CAPTCHA SYSTEM                 \n";
echo "========================================================\n\n";

$captchaService = new CaptchaService();
$testIp = '192.168.1.100';

// 1. Test CAPTCHA Generation
echo "[Test 1] Testing CAPTCHA Generation...\n";
$challenge1 = $captchaService->generate($testIp);
assert(!empty($challenge1['token']), 'Token must not be empty');
assert(!empty($challenge1['image']), 'Image data must not be empty');
assert(str_starts_with($challenge1['image'], 'data:image/svg+xml;base64,'), 'Image must be base64 SVG');
assert($challenge1['expires_in'] === 300, 'Expiration must be 300s (5 mins)');
echo "  ✓ Token generated: " . substr($challenge1['token'], 0, 8) . "...\n";
echo "  ✓ SVG Image Data length: " . strlen($challenge1['image']) . " bytes\n";
echo "  ✓ Expires in: {$challenge1['expires_in']}s\n\n";

// 2. Test Code Properties & Deduplication
echo "[Test 2] Testing Code Deduplication & Alphanumeric Composition...\n";
$cachedData1 = Cache::get('captcha_challenge_' . $challenge1['token']);
$code1 = $cachedData1['code'];
echo "  ✓ Code 1: {$code1} (Length: " . strlen($code1) . ")\n";
assert(strlen($code1) >= 3 && strlen($code1) <= 8, 'Length must be between 3 and 8');
assert(preg_match('/^[a-zA-Z0-9]+$/', $code1) === 1, 'Code must be alphanumeric');

$challenge2 = $captchaService->generate($testIp);
$cachedData2 = Cache::get('captcha_challenge_' . $challenge2['token']);
$code2 = $cachedData2['code'];
echo "  ✓ Code 2: {$code2} (Length: " . strlen($code2) . ")\n";
assert($code1 !== $code2, 'Consecutive generated codes must not be identical');
echo "  ✓ Consecutive codes are unique!\n\n";

// 3. Test Case Sensitivity
echo "[Test 3] Testing Case Sensitivity...\n";
// Create inverted case string if letters exist
$invertedCase = '';
for ($i = 0; $i < strlen($code2); $i++) {
    $c = $code2[$i];
    if (ctype_upper($c)) $invertedCase .= strtolower($c);
    elseif (ctype_lower($c)) $invertedCase .= strtoupper($c);
    else $invertedCase .= $c;
}

if ($invertedCase !== $code2) {
    echo "  Testing inverted case: '{$invertedCase}' against actual '{$code2}'...\n";
    $resultWrongCase = $captchaService->verify($challenge2['token'], $invertedCase, $testIp);
    assert($resultWrongCase['success'] === false, 'Wrong case must fail');
    assert($resultWrongCase['message'] === 'Invalid CAPTCHA. Please try again.', 'Message must match');
    echo "  ✓ Wrong case was correctly rejected!\n";
} else {
    echo "  (Code was all digits, skipping inverted case test)\n";
}

// 4. Test Single-Use Consumption
echo "\n[Test 4] Testing Single-Use Token Invalidation...\n";
$challenge3 = $captchaService->generate($testIp);
$code3 = Cache::get('captcha_challenge_' . $challenge3['token'])['code'];

$resultPass = $captchaService->verify($challenge3['token'], $code3, $testIp);
assert($resultPass['success'] === true, 'Correct code must pass');
assert(!empty($resultPass['proof_token']), 'Proof token must be issued');
echo "  ✓ Verified successfully with proof token!\n";

// Try verifying same token again
$resultReuse = $captchaService->verify($challenge3['token'], $code3, $testIp);
assert($resultReuse['success'] === false, 'Token reuse must fail');
echo "  ✓ Reusing consumed token correctly failed!\n\n";

// 5. Test Expiration
echo "[Test 5] Testing Expiration Handling...\n";
$challenge4 = $captchaService->generate($testIp);
// Simulate expiration by forgetting cache
Cache::forget('captcha_challenge_' . $challenge4['token']);
$resultExpired = $captchaService->verify($challenge4['token'], 'ABC123', $testIp);
assert($resultExpired['success'] === false, 'Expired token must fail');
echo "  ✓ Expired token correctly rejected!\n\n";

// 6. Test Rate Limiting
echo "[Test 6] Testing Rate Limiting on Consecutive Failed Attempts...\n";
$spamIp = '10.0.0.99';
for ($i = 1; $i <= 5; $i++) {
    $c = $captchaService->generate($spamIp);
    $res = $captchaService->verify($c['token'], 'WRONG_CODE_' . $i, $spamIp);
    echo "  Attempt {$i}: {$res['message']}\n";
}

$cSpam = $captchaService->generate($spamIp);
$throttledRes = $captchaService->verify($cSpam['token'], 'ANY_CODE', $spamIp);
assert($throttledRes['success'] === false, 'Throttled request must fail');
assert(str_contains($throttledRes['message'], 'Too many failed CAPTCHA attempts'), 'Throttled message expected');
echo "  ✓ Rate limiting activated and throttled correctly!\n\n";

// 7. Test HTTP Endpoint Simulation
echo "[Test 7] Testing Route and Controller JSON Response...\n";
$request = Illuminate\Http\Request::create('/auth/captcha/generate', 'GET');
$controller = new App\Http\Controllers\Auth\CaptchaController();
$response = $controller->generate($request, $captchaService);
$data = json_decode($response->getContent(), true);

assert($data['success'] === true, 'HTTP generate must return success');
assert(!empty($data['token']), 'HTTP generate must have token');
assert(!empty($data['image']), 'HTTP generate must have image');
echo "  ✓ /auth/captcha/generate returned valid JSON and Image!\n\n";

echo "========================================================\n";
echo "       ALL CAPTCHA TESTS PASSED SUCCESSFULLY!           \n";
echo "========================================================\n";
