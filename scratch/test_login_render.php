<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;
use App\Http\Controllers\Auth\LoginController;

$controller = new LoginController();
$request = Request::create('/login', 'GET');
$response = $controller->showLoginForm($request);

echo "Status: " . $response->getStatusCode() . "\n";
echo "Content length: " . strlen($response->getContent()) . "\n";

$checks = [
    'Sign in' => strpos($response->getContent(), 'Sign in') !== false,
    'Create account' => strpos($response->getContent(), 'Create account') !== false,
    'Solve this puzzle' => strpos($response->getContent(), 'Solve this puzzle to protect your account') !== false,
    'Verify email address' => strpos($response->getContent(), 'Verify email address') !== false,
    'Add mobile number' => strpos($response->getContent(), 'Add mobile number') !== false,
    'New to Idea?' => strpos($response->getContent(), 'New to Idea?') !== false,
    'No Amazon keyword' => stripos($response->getContent(), 'Amazon') === false,
];

foreach ($checks as $name => $ok) {
    echo "Check [$name]: " . ($ok ? "PASS" : "FAIL") . "\n";
}
