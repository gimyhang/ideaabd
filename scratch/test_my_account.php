<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $user = \App\Models\User::first();
    auth()->login($user);
    $req = \Illuminate\Http\Request::create('/my-account', 'GET');
    $res = (new \App\Http\Controllers\UserController)->dashboard($req);
    echo "RENDER_STATUS: " . (is_object($res) ? get_class($res) : 'NOT_OBJECT') . "\n";
    $html = $res->render();
    echo "HTML_LEN: " . strlen($html) . "\n";
    echo "SUCCESS\n";
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "FILE: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo $e->getTraceAsString() . "\n";
}
