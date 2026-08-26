<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$inv = \App\Models\IdeaInvoice::where('invoice_no', 'IDEA-STN-TEST-001')->first();
if ($inv) {
    $inv->forceDelete();
}

$stnInv = \App\Models\IdeaInvoice::create([
    'invoice_no' => 'IDEA-STN-TEST-001',
    'type' => 'invoice',
    'sales_category' => 'stationery',
    'customer_name' => 'মডেল কলেজ লাইব্রেরি',
    'customer_org' => 'মডেল কলেজ',
    'invoice_date' => now(),
    'payment_method' => 'Cash',
    'payment_status' => 'paid',
    'items' => [
        [
            'title' => 'প্রিমিয়াম হার্ডবাউন্ড ডায়েরি',
            'author_name' => 'আইডিয়া ব্র্যান্ড ২০২৬',
            'item_type' => 'Stationery',
            'unit' => 'পিস',
            'quantity' => 50,
            'regular_price' => 450,
            'discount_percent' => 20,
            'unit_price' => 360,
            'subtotal' => 18000
        ],
        [
            'title' => 'বলপয়েন্ট কলম বক্স (১০ পিস)',
            'author_name' => 'স্মুথ ০.৭মিমি',
            'item_type' => 'Stationery',
            'unit' => 'বক্স',
            'quantity' => 20,
            'regular_price' => 120,
            'discount_percent' => 10,
            'unit_price' => 108,
            'subtotal' => 2160
        ]
    ],
    'subtotal' => 20160,
    'discount' => 160,
    'tax' => 0,
    'grand_total' => 20000,
    'paid_amount' => 20000,
    'due_amount' => 0
]);

echo "Created Invoice ID: " . $stnInv->id . "\n";
echo "Sales Category: " . $stnInv->sales_category . "\n";
echo "Category Label: " . $stnInv->category_label . "\n";
echo "Category Badge: " . json_encode($stnInv->category_badge, JSON_UNESCAPED_UNICODE) . "\n";
echo "Items Count: " . count($stnInv->items) . "\n";
echo "SUCCESS!\n";
