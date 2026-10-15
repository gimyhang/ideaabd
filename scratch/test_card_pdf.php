<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$reg = \App\Models\EventRegistration::with('campaign', 'user')->where('event_campaign_id', 1)->first();
if (!$reg) {
    echo "No registration found\n";
    exit;
}

try {
    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('frontend.events.ticket_print', [
        'registration' => $reg,
        'campaign' => $reg->campaign,
        'isPdf' => true,
    ]);
    $pdf->setPaper([0, 0, 273.6, 388.8], 'portrait'); // 3.8 x 5.4 in
    $content = $pdf->output();
    echo "DomPDF success! Output bytes: " . strlen($content) . "\n";
} catch (\Throwable $e) {
    echo "DomPDF error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
