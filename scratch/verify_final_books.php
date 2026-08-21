<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$req = Illuminate\Http\Request::create('/books', 'GET');
$controller = new \Modules\Book\Http\Controllers\Frontend\BookController();
$res = $controller->index($req);

echo "SUCCESS!\n";
echo "Active Books in DB: " . \Modules\Book\Models\Book::where('is_active', true)->count() . "\n";
echo "Categories in Sidebar: " . count($res->getData()['categories']) . "\n";
echo "Authors in Sidebar: " . count($res->getData()['sidebarAuthors']) . "\n";
echo "Publishers in Sidebar: " . count($res->getData()['sidebarPublishers']) . "\n";
echo "Dynamic Category Shelves: " . count($res->getData()['dynamicCategories']) . "\n";
