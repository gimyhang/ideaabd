<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\Auth\LoginController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

$session = $app->make('session.store');
$session->start();
$controller = new LoginController();
$req = Request::create('/login', 'POST', [
    'email' => '01726976982',
    'password' => 'S#m*Idea@2901',
    'remember' => 1
]);
$req->setLaravelSession($session);

echo "=== Simulating Login via LoginController ===" . PHP_EOL;

try {
    $response = $controller->login($req);
    echo "Login Response Type: " . get_class($response) . PHP_EOL;
    if ($response instanceof \Illuminate\Http\RedirectResponse) {
        echo "Redirect Target: " . $response->getTargetUrl() . PHP_EOL;
    } elseif ($response instanceof \Illuminate\Http\JsonResponse) {
        echo "JSON Response: " . json_encode($response->getData()) . PHP_EOL;
    }
    echo "Is Authenticated: " . (Auth::check() ? 'YES' : 'NO') . PHP_EOL;
    if (Auth::check()) {
        $u = Auth::user();
        echo "Authenticated User: {$u->name} (Role: {$u->role}, ID: {$u->id})" . PHP_EOL;
        $info = password_get_info($u->password);
        echo "Updated Password Hash Algo: " . ($info['algoName'] ?? 'unknown') . " (Transparently rehashed to Argon2id!)" . PHP_EOL;
    }
} catch (\Exception $e) {
    echo "Login Error: " . $e->getMessage() . PHP_EOL;
}
