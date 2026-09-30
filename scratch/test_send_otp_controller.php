<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$request = \Illuminate\Http\Request::create(route('register.send-otp'), 'POST', [
    'phone' => '01558712810',
    'country_code' => '+880',
    'allow_existing' => true,
    'purpose' => 'library'
]);

$controller = app(\App\Http\Controllers\Auth\RegistrationController::class);
$response = $controller->sendOtp($request);

echo "Status Code: " . $response->getStatusCode() . "\n";
echo "Response JSON: " . $response->getContent() . "\n";
