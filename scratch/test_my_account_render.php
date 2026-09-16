<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::first() ?? new \App\Models\User(['name' => 'Demo User', 'email' => 'demo@example.com', 'phone' => '01711111111', 'role' => 'author']);
\Illuminate\Support\Facades\Auth::login($user);

$rendered = view('frontend.pages.my-account', [
    'user' => $user,
    'myOrders' => \App\Models\Order::paginate(8),
    'totalOrdersCount' => 3,
    'deliveredOrdersCount' => 2,
    'totalSpentAmount' => 1500,
    'pointsEarnedTotal' => 50,
    'wishlistItems' => collect(),
    'affiliateOrders' => collect(),
    'totalCommissionEarned' => 0,
    'authorPosts' => collect(),
    'blogCategories' => collect(),
    'editPost' => null,
    'defaultAddress' => ['district' => 'Rangpur', 'thana' => 'Rangpur City', 'address' => 'House 1, Road 2'],
    'myEbooks' => collect(),
])->render();

echo "Rendered my-account blade successfully! Length: " . strlen($rendered) . " bytes\n";
