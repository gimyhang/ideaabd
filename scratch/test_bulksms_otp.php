<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$phone = '01558712810';
$otp = '849201';

echo "1. Checking BulkSMSBD Balance:\n";
$bal = \App\Services\SmsService::checkBalance();
print_r($bal);

echo "\n2. Testing sendVerificationOtp:\n";
$res = \App\Services\SmsService::sendVerificationOtp($phone, $otp);
print_r($res);
