<?php
require __DIR__ . '/vendor/autoload.php';

$hosts = ['127.0.0.1', 'localhost', '::1'];

foreach ($hosts as $host) {
    $t0 = microtime(true);
    try {
        $pdo = new PDO("mysql:host={$host};port=3306;dbname=ideaabd_db", 'root', '', [
            PDO::ATTR_TIMEOUT => 2,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        $time = round((microtime(true) - $t0) * 1000, 2);
        echo "Host: {$host} -> SUCCESS ({$time} ms)\n";
    } catch (Throwable $e) {
        $time = round((microtime(true) - $t0) * 1000, 2);
        echo "Host: {$host} -> ERROR ({$time} ms): " . $e->getMessage() . "\n";
    }
}
