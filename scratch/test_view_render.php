<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$rendered = view('auth.login')->render();
echo "Rendered login blade successfully! Size: " . strlen($rendered) . " bytes." . PHP_EOL;
