<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$tables = DB::select('SHOW TABLES');
foreach ($tables as $t) {
    $tableName = array_values((array)$t)[0];
    if (str_contains($tableName, 'cat') || str_contains($tableName, 'book')) {
        $count = DB::table($tableName)->count();
        echo "Table: {$tableName} -> {$count} rows\n";
    }
}
