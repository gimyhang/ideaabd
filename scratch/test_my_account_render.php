<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

$user = User::first();
if (!$user) {
    $user = User::factory()->make(['id' => 1, 'name' => 'Demo Customer', 'email' => 'customer@ideaabd.com', 'phone' => '01700000000']);
}
Auth::login($user);

$controller = new UserController();
$request = Request::create('/my-account', 'GET');
$response = $controller->dashboard($request);

$rendered = $response->render();

echo "My Account Render: SUCCESS\n";
echo "Length: " . strlen($rendered) . " bytes\n";

$checks = [
    'Your Account Title' => strpos($rendered, 'Your Account') !== false,
    'Orders & Purchases' => strpos($rendered, 'Orders & Purchases') !== false,
    'Your Orders' => strpos($rendered, 'Your Orders') !== false,
    'Your Wishlist' => strpos($rendered, 'Your Wishlist') !== false,
    'Buy Again' => strpos($rendered, 'Buy Again') !== false,
    'Account & Security' => strpos($rendered, 'Account & Security') !== false,
    'Login & Security' => strpos($rendered, 'Login & Security') !== false,
    'Your Addresses' => strpos($rendered, 'Your Addresses') !== false,
    'Your Payments' => strpos($rendered, 'Your Payments') !== false,
    'Subscriptions & Gifting' => strpos($rendered, 'Subscriptions & Gifting') !== false,
    'Idea Premium Membership' => strpos($rendered, 'Idea Premium Membership') !== false,
    'Gift Cards & Vouchers' => strpos($rendered, 'Gift Cards & Vouchers') !== false,
    'Digital Services & E-Reader Support' => strpos($rendered, 'Digital Services & E-Reader Support') !== false,
    'Corporate & Family' => strpos($rendered, 'Corporate & Family') !== false,
    'Business Account' => strpos($rendered, 'Business Account') !== false,
    'Family Profiles' => strpos($rendered, 'Family Profiles') !== false,
    'Communication & Help' => strpos($rendered, 'Communication & Help') !== false,
    'Your Messages' => strpos($rendered, 'Your Messages') !== false,
    'Customer Service' => strpos($rendered, 'Customer Service') !== false,
    'No Amazon Keyword' => stripos($rendered, 'Amazon') === false,
];

foreach ($checks as $name => $ok) {
    echo "Check [$name]: " . ($ok ? "PASS" : "FAIL") . "\n";
}
