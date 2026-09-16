<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\LoginSecurityLog;
use Illuminate\Support\Facades\Hash;

$phone = '01726976982';
$password = 'S#m*Idea@2901';

echo "=== Diagnostic for phone: {$phone} ===" . PHP_EOL;

// 1. Search for user by phone, email, name, etc.
$rawDigitsOnly = preg_replace('/[^\d]/', '', $phone);
$last10 = substr($rawDigitsOnly, -10);

$users = User::where('phone', $phone)
    ->orWhere('email', $phone)
    ->orWhere('phone', 'LIKE', "%{$last10}")
    ->orWhere('phone', '0' . $last10)
    ->orWhere('phone', '+880' . $last10)
    ->orWhere('phone', '880' . $last10)
    ->get();

echo "Found " . $users->count() . " matching user(s):" . PHP_EOL;

foreach ($users as $u) {
    echo "-------------------------------------" . PHP_EOL;
    echo "ID: " . $u->id . PHP_EOL;
    echo "Name: " . $u->name . PHP_EOL;
    echo "Email: " . $u->email . PHP_EOL;
    echo "Phone in DB: " . $u->phone . PHP_EOL;
    echo "Role: " . $u->role . PHP_EOL;
    echo "Reg Status: " . ($u->reg_status ?? 'N/A') . PHP_EOL;
    echo "Is Active: " . ($u->is_active ?? 'N/A') . PHP_EOL;
    echo "Password Hash in DB: " . substr($u->password, 0, 25) . "..." . PHP_EOL;
    $info = password_get_info($u->password);
    echo "Hash Algorithm: " . ($info['algoName'] ?? 'unknown') . PHP_EOL;
    
    $check = Hash::check($password, $u->password);
    echo "Password Match with '{$password}': " . ($check ? 'YES (PASS)' : 'NO (FAIL)') . PHP_EOL;

    if (!$check) {
        // Test common variations just in case
        $variations = [
            'S#m*Idea@2901',
            's#m*idea@2901',
            '123456',
            '12345678',
            'password',
            'admin123'
        ];
        foreach ($variations as $var) {
            if (Hash::check($var, $u->password)) {
                echo "  -> Found matching password variation: '{$var}'" . PHP_EOL;
            }
        }
    }
}

echo PHP_EOL . "=== Checking Login Security Logs ===" . PHP_EOL;
$logs = LoginSecurityLog::where('last_username', 'LIKE', "%{$phone}%")
    ->orWhere('ip_address', '127.0.0.1')
    ->get();

foreach ($logs as $l) {
    echo "IP: {$l->ip_address} | Last User: {$l->last_username} | Attempts: {$l->attempt_count} | Locked Until: {$l->locked_until} | Is Blocked: {$l->is_blocked} | Security Issue: {$l->is_security_issue}" . PHP_EOL;
}
