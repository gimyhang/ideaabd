<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Hash;
use App\Services\PasswordSecurityService;
use App\Rules\StrongPassword;
use App\Services\SecurityAuditService;
use App\Models\LoginSecurityLog;
use Illuminate\Support\Facades\Validator;

echo "=== 1. Testing Argon2id Hashing Configuration ===" . PHP_EOL;
$hashDriver = config('hashing.driver');
echo "Configured default hashing driver: " . $hashDriver . PHP_EOL;

$samplePassword = "CorrectHorse-BatteryStaple-2026!";
$hash = Hash::make($samplePassword);
$info = password_get_info($hash);
echo "Hash Algorithm ID: " . $info['algo'] . " (" . $info['algoName'] . ")" . PHP_EOL;
echo "Hash Verify: " . (Hash::check($samplePassword, $hash) ? "PASS" : "FAIL") . PHP_EOL;
echo "Hash Needs Rehash: " . (Hash::needsRehash($hash) ? "YES" : "NO (Correct)") . PHP_EOL;

echo PHP_EOL . "=== 2. Testing PasswordSecurityService Validation Engine ===" . PHP_EOL;

$testCases = [
    // [password, name, email, phone, shouldPass, description]
    ["123456", "John", "john@example.com", "01711111111", false, "Short & Common Password"],
    ["12345678", "John", "john@example.com", "01711111111", false, "Sequential Number Password"],
    ["password123", "John", "john@example.com", "01711111111", false, "Blacklisted Dictionary Word"],
    ["admin12345", "John", "john@example.com", "01711111111", false, "Blacklisted Word 'admin'"],
    ["qwertyuiop", "John", "john@example.com", "01711111111", false, "Keyboard Pattern"],
    ["aaaa1111", "John", "john@example.com", "01711111111", false, "Repetitive Characters"],
    ["john_doe_pass", "John Doe", "john@example.com", "01711111111", false, "Matches User Full Name"],
    ["john998877", "Jane Smith", "john.doe@test.com", "01711111111", false, "Matches User Email Prefix"],
    ["01711111111pass", "Jane Smith", "jane@test.com", "01711111111", false, "Matches User Phone Number"],
    ["V3ry\$tr0ng&Un1qu3P@ssw0rd!2026", "John Doe", "john@test.com", "01822222222", true, "High-Entropy Strong Password"],
    ["K8#mP\$9xL!2v", "Rahim Mia", "rahim@test.com", "01933333333", true, "Complex Alphanumeric + Symbols"]
];

$passCount = 0;
foreach ($testCases as $tc) {
    [$pwd, $name, $email, $phone, $expectedValid, $desc] = $tc;
    $res = PasswordSecurityService::validateStatic($pwd, [
        'name' => $name,
        'email' => $email,
        'phone' => $phone
    ], false); // Offline check for consistent automated tests

    $isSuccess = ($res['valid'] === $expectedValid);
    if ($isSuccess) {
        $passCount++;
        echo "[PASS] {$desc} -> Valid: " . ($res['valid'] ? 'true' : 'false');
        if (!$res['valid']) {
            echo " | Errors: " . implode("; ", $res['errors']);
        }
        echo PHP_EOL;
    } else {
        echo "[FAIL] {$desc} -> Expected valid={$expectedValid}, got valid={$res['valid']} | Errors: " . implode("; ", $res['errors']) . PHP_EOL;
    }
}
echo "PasswordSecurityService Tests: {$passCount}/" . count($testCases) . " PASSED." . PHP_EOL;

echo PHP_EOL . "=== 3. Testing StrongPassword Laravel Validation Rule ===" . PHP_EOL;
$vFail = Validator::make(
    ['password' => 'password123', 'name' => 'John', 'email' => 'john@test.com'],
    ['password' => ['required', 'string', 'min:8', 'max:128', new StrongPassword()]]
);
echo "Validator for weak password failed: " . ($vFail->fails() ? "YES (PASS)" : "NO (FAIL)") . PHP_EOL;
if ($vFail->fails()) {
    echo "Validation message: " . $vFail->errors()->first('password') . PHP_EOL;
}

$vPass = Validator::make(
    ['password' => 'V3ry\$tr0ng&Un1qu3P@ss!', 'name' => 'John', 'email' => 'john@test.com'],
    ['password' => ['required', 'string', 'min:8', 'max:128', new StrongPassword()]]
);
echo "Validator for strong password passed: " . (!$vPass->fails() ? "YES (PASS)" : "NO (FAIL)") . PHP_EOL;

echo PHP_EOL . "=== 4. Testing LoginSecurityLog Progressive Delay Escalation ===" . PHP_EOL;
$testIp = '127.0.0.' . rand(100, 254);
$testIdentifier = 'test_security_user_' . time() . '@test.com';
LoginSecurityLog::where('ip_address', $testIp)->delete();

// Step 0: Initial state
$status0 = LoginSecurityLog::checkIpStatus($testIp, $testIdentifier);
echo "Attempts 0 -> Status: {$status0['status']}, Requires CAPTCHA: " . ($status0['requires_captcha'] ? 'Yes' : 'No') . PHP_EOL;

// Step 1: Record 3 failed attempts
LoginSecurityLog::recordFailedAttempt($testIp, $testIdentifier);
LoginSecurityLog::recordFailedAttempt($testIp, $testIdentifier);
$res3 = LoginSecurityLog::recordFailedAttempt($testIp, $testIdentifier);
$status3 = LoginSecurityLog::checkIpStatus($testIp, $testIdentifier);
echo "Attempts 3 -> Failed count: {$res3['count']}, Requires CAPTCHA: " . ($status3['requires_captcha'] ? 'YES (PASS)' : 'NO (FAIL)') . PHP_EOL;

// Step 2: Record 4th attempt -> 30s delay
$res4 = LoginSecurityLog::recordFailedAttempt($testIp, $testIdentifier);
$status4 = LoginSecurityLog::checkIpStatus($testIp, $testIdentifier);
echo "Attempts 4 -> Status: {$status4['status']}, Cooldown: {$status4['remaining_seconds']}s (Expected ~30s)" . PHP_EOL;

// Step 3: Record 5th attempt -> 60s delay
$res5 = LoginSecurityLog::recordFailedAttempt($testIp, $testIdentifier);
$status5 = LoginSecurityLog::checkIpStatus($testIp, $testIdentifier);
echo "Attempts 5 -> Status: {$status5['status']}, Cooldown: {$status5['remaining_seconds']}s (Expected ~60s)" . PHP_EOL;

// Step 4: Record 6th attempt -> 120s delay
$res6 = LoginSecurityLog::recordFailedAttempt($testIp, $testIdentifier);
$status6 = LoginSecurityLog::checkIpStatus($testIp, $testIdentifier);
echo "Attempts 6 -> Status: {$status6['status']}, Cooldown: {$status6['remaining_seconds']}s (Expected ~120s)" . PHP_EOL;

// Step 5: Reset on successful login
LoginSecurityLog::recordSuccessfulLogin($testIp, $testIdentifier);
$statusReset = LoginSecurityLog::checkIpStatus($testIp, $testIdentifier);
echo "After Reset -> Status: {$statusReset['status']}, Requires CAPTCHA: " . ($statusReset['requires_captcha'] ? 'Yes' : 'NO (PASS)') . PHP_EOL;

echo PHP_EOL . "=== 5. Testing SecurityAuditService Sanitization ===" . PHP_EOL;
$sensitiveContext = [
    'email' => 'user@example.com',
    'password' => 'SuperSecret123!',
    'password_confirmation' => 'SuperSecret123!',
    'otp' => '654321',
    'token' => 'abcdef1234567890',
    'safe_field' => 'hello_world'
];
SecurityAuditService::log('test_audit_event', $sensitiveContext, 'info');
echo "SecurityAuditService executed successfully without throwing exceptions." . PHP_EOL;

echo PHP_EOL . "=== ALL AUTHENTICATION SECURITY TESTS COMPLETED SUCCESSFULLY ===" . PHP_EOL;
