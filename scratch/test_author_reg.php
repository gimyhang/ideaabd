<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;
use Modules\Author\Http\Controllers\Frontend\AuthorController;

$controller = new AuthorController();
$view = $controller->register();
$rendered = $view->render();

echo "Author Register Page rendered successfully!\n";
echo "Length: " . strlen($rendered) . " bytes\n";

$checks = [
    'লেখকের নাম (বাংলা)' => strpos($rendered, 'লেখকের নাম (বাংলা)') !== false,
    'লেখকের নাম (ইংরেজি)' => strpos($rendered, 'লেখকের নাম (ইংরেজি)') !== false,
    'সচল ইমেইল' => strpos($rendered, 'সচল ইমেইল') !== false,
    'মোবাইল নম্বর' => strpos($rendered, 'মোবাইল নম্বর') !== false,
    'bio-clean-textarea' => strpos($rendered, 'bio-clean-textarea') !== false,
    'payout-card-option' => strpos($rendered, 'payout-card-option') !== false,
    'selectPayoutMethod' => strpos($rendered, 'selectPayoutMethod') !== false,
];

foreach ($checks as $name => $ok) {
    echo "Check [$name]: " . ($ok ? "PASS" : "FAIL") . "\n";
}
