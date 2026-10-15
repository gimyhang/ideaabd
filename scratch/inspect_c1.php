<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$regs = \App\Models\EventRegistration::where('event_campaign_id', 1)->get();
foreach ($regs as $r) {
    echo "ID: {$r->id} | RegNo: {$r->registration_number} | Name: {$r->name} | Phone: {$r->phone} | District: '{$r->district}' | FormDist: '" . ($r->form_data['district'] ?? '') . "' | FormPermDist: '" . ($r->form_data['perm_district'] ?? '') . "' | FormDiv: '" . ($r->form_data['perm_division'] ?? '') . "'" . PHP_EOL;
}
