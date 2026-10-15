<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$request = Illuminate\Http\Request::create('/admin/event-campaigns/1', 'GET');
$controller = new \App\Http\Controllers\Admin\EventCampaignAdminController();
$campaign = \App\Models\EventCampaign::find(1);

try {
    $response = $controller->show($request, $campaign);
    echo "Show method returned view successfully!\n";
    $viewContent = $response->render();
    echo "View rendered successfully, length: " . strlen($viewContent) . " bytes\n";
    
    // Check if new division and district columns are present in HTML
    if (strpos($viewContent, 'col-division') !== false && strpos($viewContent, 'col-district') !== false) {
        echo "SUCCESS: 'col-division' and 'col-district' exist in rendered HTML!\n";
    } else {
        echo "WARNING: Column classes not found in HTML!\n";
    }
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}
