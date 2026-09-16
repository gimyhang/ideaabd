<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

echo "Testing /login rendering with register mode...\n";
$request = Request::create('/login?mode=register', 'GET');
$response = $app->handle($request);

$content = $response->getContent();

$checks = [
    'Panel Captcha' => strpos($content, 'id="panelCaptcha"') !== false,
    'Captcha Card Box' => strpos($content, 'class="captcha-card-box"') !== false,
    'Captcha Code Input' => strpos($content, 'id="captchaCodeInput"') !== false,
    'Refresh Button' => strpos($content, 'id="btnRefreshCaptcha"') !== false,
    'Verify Button' => strpos($content, 'id="btnVerifyCaptcha"') !== false,
    'Case-Sensitive text' => strpos($content, 'case-sensitive') !== false,
    'No Old Puzzle Set' => strpos($content, 'PUZZLE_SETS') === false,
];

foreach ($checks as $name => $passed) {
    echo ($passed ? "  ✓ " : "  ✗ ") . $name . "\n";
    assert($passed, "Check {$name} failed!");
}

echo "\nRender check complete! Status Code: " . $response->getStatusCode() . "\n";
