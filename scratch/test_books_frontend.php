<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$req = Illuminate\Http\Request::create('/books', 'GET');
$controller = new \Modules\Book\Http\Controllers\Frontend\BookController();
$res = $controller->index($req);

echo "Books page rendered successfully!\n";
echo "Books count in view: " . count($res->getData()['books']) . "\n";
echo "Dynamic Categories count: " . count($res->getData()['dynamicCategories']) . "\n";
foreach ($res->getData()['dynamicCategories'] as $cat) {
    echo " - " . $cat->name . " (" . $cat->books_count . " books)\n";
}
