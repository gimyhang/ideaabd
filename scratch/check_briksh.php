<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$b = Modules\Ebook\Models\Ebook::where('slug', 'briksh-zkhn-ktha-ble')->first();
if ($b) {
    echo "ID: " . $b->id . "\n";
    echo "Title: " . $b->title . "\n";
    echo "File Path: " . $b->file_path . "\n";
    echo "EPUB Path: " . $b->epub_file_path . "\n";
    echo "Sample Path: " . $b->sample_file_path . "\n";
    echo "File Type: " . $b->file_type . "\n";
} else {
    echo "Not found\n";
}
