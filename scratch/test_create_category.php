<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\Admin\QuickResourceController;
use App\Http\Controllers\Admin\ContentController;
use Illuminate\Http\Request;

echo "=== TESTING QUICK CREATE CATEGORY 'আইন ও বিচার' ===\n";
$quickController = new QuickResourceController();
$req = Request::create('/admin/quick/category', 'POST', [
    'name' => 'আইন ও বিচার',
    'description' => 'আইন ও বিচার বিষয়ক গ্রন্থাবলি',
]);
try {
    $res = $quickController->quickStoreCategory($req);
    echo "QuickStore response: " . $res->getContent() . "\n";
} catch (\Throwable $e) {
    echo "QuickStore error: " . $e->getMessage() . "\n";
}

echo "\n=== TESTING ContentController CREATE CATEGORY 'আইন ও বিচার 2' ===\n";
$contentController = new ContentController();
$req2 = Request::create('/admin/content/categories', 'POST', [
    'name' => 'আইন ও বিচার 2',
    'is_active' => '1',
]);
try {
    $res2 = $contentController->store($req2, 'categories');
    echo "ContentController response status: " . $res2->getStatusCode() . "\n";
} catch (\Throwable $e) {
    echo "ContentController error: " . $e->getMessage() . "\n";
}
