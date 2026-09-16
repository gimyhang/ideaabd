<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\AdminController;
use Illuminate\Http\Request;

$admin = User::where('role', 'admin')->first();
if ($admin) {
    Auth::login($admin);
}

$controller = app(AdminController::class);
$req = Request::create('/admin/dashboard', 'GET');

try {
    $view = $controller->dashboard($req);
    $rendered = $view->render();
    echo "Dashboard Blade rendered successfully via AdminController! Length: " . strlen($rendered) . " bytes." . PHP_EOL;
} catch (\Throwable $e) {
    echo "Dashboard render error: " . $e->getMessage() . " on line " . $e->getLine() . " in " . $e->getFile() . PHP_EOL;
}
