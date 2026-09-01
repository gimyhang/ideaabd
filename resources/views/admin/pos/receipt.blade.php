<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <title>Receipt #{{ $sale->receipt_no }} — আইডিয়া প্রকাশন</title>
    <style>
        body {
            font-family: monospace, 'Hind Siliguri', sans-serif;
            font-size: 12px;
            width: 72mm;
            margin: 0 auto;
            padding: 10px 5px;
            color: #000;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .fw-bold { font-weight: bold; }
        .border-top { border-top: 1px dashed #000; }
        .border-bottom { border-bottom: 1px dashed #000; }
        .my-1 { margin-top: 4px; margin-bottom: 4px; }
        .py-1 { padding-top: 4px; padding-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; }
        @media print {
            body { width: 100%; padding: 0; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="text-center">
        <h3 style="margin:0 0 2px 0;">আইডিয়া প্রকাশন</h3>
        <div>{{ $sale->register->name ?? 'বইমেলা স্টল' }}</div>
        <div style="font-size: 10px;">{{ $sale->register->location ?? 'ঢাকা' }}</div>
        <div class="border-top my-1"></div>
        <div><strong>CASH RECEIPT</strong></div>
        <div>Receipt: #{{ $sale->receipt_no }}</div>
        <div>Date: {{ $sale->created_at->format('d/m/Y h:i A') }}</div>
        <div>Cashier: {{ $sale->cashier->name ?? 'Stall Staff' }}</div>
        <div class="border-bottom my-1"></div>
    </div>

    <table>
        <thead>
            <tr class="border-bottom">
                <th align="left">Item</th>
                <th align="center">Qty</th>
                <th align="right">Price</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->items_json ?? [] as $item)
                <tr>
                    <td>{{ $item['title'] }}</td>
                    <td align="center">{{ $item['quantity'] }}</td>
                    <td align="right">{{ number_format($item['total'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="border-top my-1"></div>
    <table>
        <tr>
            <td>Subtotal:</td>
            <td align="right">৳{{ number_format($sale->subtotal, 2) }}</td>
        </tr>
        @if($sale->discount > 0)
            <tr>
                <td>Discount:</td>
                <td align="right">-৳{{ number_format($sale->discount, 2) }}</td>
            </tr>
        @endif
        <tr class="fw-bold" style="font-size: 13px;">
            <td>NET TOTAL:</td>
            <td align="right">৳{{ number_format($sale->total, 2) }}</td>
        </tr>
        <tr>
            <td>Payment ({{ strtoupper($sale->payment_method) }}):</td>
            <td align="right">৳{{ number_format($sale->total, 2) }}</td>
        </tr>
    </table>

    <div class="border-top my-1"></div>
    <div class="text-center" style="font-size: 10px;">
        <div>ধন্যবাদ! আবার আসবেন।</div>
        <div>www.ideaprakashan.com</div>
    </div>
</body>
</html>
