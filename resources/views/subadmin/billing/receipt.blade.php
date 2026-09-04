<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <title>Receipt #{{ $bill->bill_no }} — {{ $invoiceSettings['company_name'] ?? config('brand.name', 'আইডিয়া প্রকাশন') }}</title>
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
        .text-end { text-align: right; }
        .fw-bold { font-weight: bold; }
        .border-top { border-top: 1px dashed #000; }
        .border-bottom { border-bottom: 1px dashed #000; }
        .my-1 { margin-top: 5px; margin-bottom: 5px; }
        .py-1 { padding-top: 4px; padding-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; }
        @media print {
            body { width: 100%; padding: 0; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="no-print text-center my-1" style="margin-bottom: 15px;">
        <button onclick="window.print()" style="padding: 6px 15px; font-weight: bold; cursor: pointer; background: #0066cc; color: #fff; border: 0; border-radius: 4px;">
            🖨️ প্রিন্ট করুন
        </button>
        <button onclick="window.close()" style="padding: 6px 12px; cursor: pointer; border: 1px solid #ccc; background: #fff; border-radius: 4px; margin-left: 5px;">
            বন্ধ করুন
        </button>
    </div>

    <div class="text-center">
        <h3 style="margin:0 0 2px 0;">{{ $invoiceSettings['company_name'] ?? config('brand.name', 'আইডিয়া প্রকাশন') }}</h3>
        <div style="font-size: 11px;">{{ $invoiceSettings['company_tagline'] ?? config('brand.tagline', 'বই হোক মননশীল জীবনের অংশ') }}</div>
        <div style="font-size: 10px;">{{ $invoiceSettings['office_phone'] ?? '01712-345678' }} | www.ideaabd.com</div>
        <div class="border-top my-1"></div>
        <div><strong>{{ strtoupper($bill->type_label) }}</strong></div>
        <div>Memo: #{{ $bill->bill_no }}</div>
        <div>Date: {{ ($bill->bill_date ?? $bill->created_at)->format('d/m/Y h:i A') }}</div>
        <div>Seller: {{ $bill->seller->name ?? 'সেলার' }}</div>
        @if($bill->customer_name)
            <div>Customer: {{ $bill->customer_name }}</div>
            @if($bill->customer_org) <div>Org: {{ $bill->customer_org }}</div> @endif
            @if($bill->customer_phone) <div>Phone: {{ $bill->customer_phone }}</div> @endif
        @endif
        <div class="border-bottom my-1"></div>
    </div>

    <table>
        <thead>
            <tr class="border-bottom">
                <th align="left">বইয়ের নাম</th>
                <th align="center">পরিমাণ</th>
                <th align="right">মূল্য (৳)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($bill->items ?? [] as $item)
                @php
                    $qty = (int)($item['qty'] ?? 1);
                    $price = (float)($item['price'] ?? 0);
                    $disc = (float)($item['discount_pct'] ?? 0);
                    $lineTotal = (float)($item['line_total'] ?? ($qty * $price * (1 - $disc / 100)));
                @endphp
                <tr>
                    <td style="padding: 3px 0;">
                        {{ $item['title'] ?? 'বই' }}
                        @if($disc > 0)
                            <br><small style="font-size: 10px; color: #555;">(ছাড়: {{ $disc }}%)</small>
                        @endif
                    </td>
                    <td align="center" style="vertical-align: top; padding: 3px 0;">{{ $qty }}</td>
                    <td align="right" style="vertical-align: top; padding: 3px 0;">{{ number_format($lineTotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="border-top my-1"></div>

    <table>
        <tr>
            <td>সাবটোটাল:</td>
            <td align="right">৳{{ number_format($bill->subtotal, 2) }}</td>
        </tr>
        @if($bill->discount > 0)
        <tr>
            <td>মোট ছাড়:</td>
            <td align="right">-৳{{ number_format($bill->discount, 2) }}</td>
        </tr>
        @endif
        <tr class="fw-bold" style="font-size: 13px;">
            <td style="padding-top: 4px;">সর্বমোট (Net):</td>
            <td align="right" style="padding-top: 4px;">৳{{ number_format($bill->total, 2) }}</td>
        </tr>
        <tr>
            <td>পরিশোধিত (Paid):</td>
            <td align="right">৳{{ number_format($bill->paid_amount ?? $bill->total, 2) }}</td>
        </tr>
        @if($bill->due_amount > 0)
        <tr style="color: #c00; font-weight: bold;">
            <td>বকেয়া (Due):</td>
            <td align="right">৳{{ number_format($bill->due_amount, 2) }}</td>
        </tr>
        @endif
        <tr>
            <td>পেমেন্ট মেথড:</td>
            <td align="right">{{ strtoupper($bill->payment_method ?? 'CASH') }}</td>
        </tr>
        <tr>
            <td>পেমেন্ট স্ট্যাটাস:</td>
            <td align="right">
                @if($bill->payment_status === 'paid')
                    [ PAID / পরিশোধিত ]
                @elseif($bill->payment_status === 'partial')
                    [ PARTIAL / আংশিক ]
                @else
                    [ UNPAID / বকেয়া ]
                @endif
            </td>
        </tr>
    </table>

    <div class="border-top my-1"></div>
    <div class="text-center" style="font-size: 10px; margin-top: 8px;">
        *** ধন্যবাদ, আবার আসবেন ***<br>
        {{ $invoiceSettings['company_name'] ?? 'আইডিয়া প্রকাশন' }} — জ্ঞান ও চিন্তার মেলবন্ধন
    </div>
</body>
</html>
