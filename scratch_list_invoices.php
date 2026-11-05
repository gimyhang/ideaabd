<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\IdeaInvoice;

$invoices = IdeaInvoice::latest('id')->limit(10)->get(['id', 'invoice_no', 'type', 'customer_name', 'grand_total', 'paid_amount', 'due_amount', 'payment_status', 'previous_due']);

echo "Recent Invoices:\n";
foreach ($invoices as $inv) {
    echo "ID: {$inv->id} | No: {$inv->invoice_no} | Type: {$inv->type} | Cust: {$inv->customer_name} | Grand: {$inv->grand_total} | Paid: {$inv->paid_amount} | Due: {$inv->due_amount} | PrevDue: {$inv->previous_due} | Status: {$inv->payment_status}\n";
    $paymentsCount = $inv->payments()->count();
    echo "   Payments count: {$paymentsCount}\n";
}

