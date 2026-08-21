<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$tables = DB::select('SHOW TABLES');
foreach ($tables as $t) {
    $table = array_values((array)$t)[0];
    try {
        $columns = \Illuminate\Support\Facades\Schema::getColumnListing($table);
        $textCols = [];
        foreach ($columns as $col) {
            $type = \Illuminate\Support\Facades\Schema::getColumnType($table, $col);
            if (in_array($type, ['string', 'text', 'varchar'])) {
                $textCols[] = $col;
            }
        }
        if (!empty($textCols)) {
            $query = DB::table($table);
            $query->where(function($q) use ($textCols) {
                foreach ($textCols as $col) {
                    $q->orWhere($col, 'LIKE', '%আইন%')
                      ->orWhere($col, 'LIKE', '%বিচার%');
                }
            });
            $count = $query->count();
            if ($count > 0) {
                echo "Found {$count} rows in table '{$table}':\n";
                $rows = $query->take(5)->get();
                foreach ($rows as $r) {
                    echo "  -> " . json_encode($r, JSON_UNESCAPED_UNICODE) . "\n";
                }
            }
        }
    } catch (\Throwable $e) {
        // ignore
    }
}
