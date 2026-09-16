<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\CaptchaService;

$service = app(CaptchaService::class);

echo "=== Testing CAPTCHA Generation ===" . PHP_EOL;
$gen = $service->generate('127.0.0.1');
echo "Generated Token: " . $gen['token'] . PHP_EOL;
echo "Expires in: " . $gen['expires_in'] . "s" . PHP_EOL;

// Read cached code
$cached = \Illuminate\Support\Facades\Cache::get('captcha_challenge_' . $gen['token']);
echo "Cached Code: " . ($cached['code'] ?? 'NOT FOUND') . PHP_EOL;

// Test case 1: Exact match
$res1 = $service->verify($gen['token'], $cached['code'], '127.0.0.1');
echo "Exact match verification: " . ($res1['success'] ? 'PASS' : 'FAIL: ' . $res1['message']) . PHP_EOL;

// Test case 2: Lowercase input
$gen2 = $service->generate('127.0.0.1');
$cached2 = \Illuminate\Support\Facades\Cache::get('captcha_challenge_' . $gen2['token']);
$lowerCode = strtolower($cached2['code']);
$res2 = $service->verify($gen2['token'], $lowerCode, '127.0.0.1');
echo "Lowercase verification (input='{$lowerCode}', actual='{$cached2['code']}'): " . ($res2['success'] ? 'PASS' : 'FAIL: ' . $res2['message']) . PHP_EOL;

// Test case 3: Uppercase input with extra spaces
$gen3 = $service->generate('127.0.0.1');
$cached3 = \Illuminate\Support\Facades\Cache::get('captcha_challenge_' . $gen3['token']);
$spacedCode = ' ' . strtoupper($cached3['code']) . ' ';
$res3 = $service->verify($gen3['token'], $spacedCode, '127.0.0.1');
echo "Spaced uppercase verification (input='{$spacedCode}', actual='{$cached3['code']}'): " . ($res3['success'] ? 'PASS' : 'FAIL: ' . $res3['message']) . PHP_EOL;
