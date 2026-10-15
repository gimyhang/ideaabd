<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$routes = \Illuminate\Support\Facades\Route::getRoutes();

$singleSegmentRoutes = [];
foreach ($routes as $route) {
    $uri = $route->uri();
    $methods = implode('|', $route->methods());
    $actionName = $route->getActionName();
    $routeName = $route->getName() ?? '(unnamed)';
    
    $segments = explode('/', trim($uri, '/'));
    if (count($segments) === 1) {
        $singleSegmentRoutes[] = [
            'methods' => $methods,
            'uri' => $uri,
            'name' => $routeName,
            'action' => $actionName
        ];
    }
}

echo "=== All Single-Segment Routes ===\n";
foreach ($singleSegmentRoutes as $r) {
    printf("%-15s %-30s %-35s %s\n", $r['methods'], $r['uri'], $r['name'], $r['action']);
}
