<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$campaigns = \App\Models\EventCampaign::all(['id', 'title', 'slug', 'type', 'is_active']);
foreach ($campaigns as $c) {
    echo "ID: {$c->id} | Title: {$c->title} | Slug: {$c->slug} | Type: {$c->type} | Active: {$c->is_active}\n";
}
