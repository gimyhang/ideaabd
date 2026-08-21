<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$controller = new \Modules\Book\Http\Controllers\Frontend\BookController();

// Test Category Slug
$req1 = Illuminate\Http\Request::create('/books?category=upnzas', 'GET');
$res1 = $controller->index($req1);
echo "Category 'upnzas' returned: " . count($res1->getData()['books']) . " books\n";

// Test Category ID
$req2 = Illuminate\Http\Request::create('/books?category=12', 'GET');
$res2 = $controller->index($req2);
echo "Category ID '12' returned: " . count($res2->getData()['books']) . " books\n";
