<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$ebook = Modules\Ebook\Models\Ebook::where('slug', 'briksh-zkhn-ktha-ble')->first();
echo "Testing ebook: " . $ebook->title . "\n";
echo "Resolved EPUB file path: " . $ebook->epub_file_path . "\n";

$req = Illuminate\Http\Request::create('/ebooks/' . $ebook->id . '/stream', 'GET', ['sample' => 1]);
$controller = app(Modules\Ebook\Http\Controllers\Frontend\EbookController::class);
try {
    $resp = $controller->stream($ebook->id, $req);
    echo "Stream response status: " . $resp->getStatusCode() . "\n";
    echo "Content type: " . $resp->headers->get('Content-Type') . "\n";
    echo "File streamed successfully!\n";
} catch (\Exception $e) {
    echo "Stream error: " . $e->getMessage() . "\n";
}
