<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$campaigns = \App\Models\EventCampaign::all(['id', 'title', 'slug', 'type']);
echo "Campaigns count: " . $campaigns->count() . PHP_EOL;
foreach ($campaigns as $c) {
    $regCount = \App\Models\EventRegistration::where('event_campaign_id', $c->id)->count();
    echo "ID: {$c->id} | Slug: {$c->slug} | Title: {$c->title} | Regs: {$regCount}" . PHP_EOL;
}

$sampleRegs = \App\Models\EventRegistration::take(10)->get();
echo "\nSample Registrations:\n";
foreach ($sampleRegs as $r) {
    echo "ID: {$r->id} | RegNo: {$r->registration_number} | Name: {$r->name} | District: '{$r->district}' | ResDist: '{$r->resolved_district}' | ResDiv: '{$r->resolved_division}' | FormDiv: '" . ($r->form_data['perm_division'] ?? '') . "'" . PHP_EOL;
}
