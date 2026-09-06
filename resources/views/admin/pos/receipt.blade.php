<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt #{{ $sale->receipt_no }} — আইডিয়া প্রকাশন</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&family=JetBrains+Mono:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Hind Siliguri', 'JetBrains Mono', monospace, sans-serif;
            font-size: 13px;
            width: 78mm;
            max-width: 100%;
            margin: 0 auto;
            padding: 12px 10px;
            color: #0f172a;
            background: #fff;
            line-height: 1.35;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .fw-bold { font-weight: 700; }
        .fw-semibold { font-weight: 600; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
        .border-top-dash { border-top: 1px dashed #475569; }
        .border-bottom-dash { border-bottom: 1px dashed #475569; }
        .my-1 { margin-top: 5px; margin-bottom: 5px; }
        .my-2 { margin-top: 8px; margin-bottom: 8px; }
        .py-1 { padding-top: 3px; padding-bottom: 3px; }
        
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12.5px;
        }
        th, td {
            padding: 3px 0;
            vertical-align: top;
        }
        .void-banner {
            background: #fee2e2;
            border: 2px dashed #ef4444;
            color: #991b1b;
            padding: 6px;
            text-align: center;
            font-weight: 700;
            margin-bottom: 8px;
            border-radius: 4px;
            text-transform: uppercase;
        }
        .actions-bar {
            margin-bottom: 12px;
            display: flex;
            gap: 6px;
            justify-content: center;
        }
        .btn-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            padding: 6px 12px;
            font-size: 12px;
            font-weight: 600;
            border-radius: 20px;
            border: 1px solid #cbd5e1;
            background: #f8fafc;
            color: #1e293b;
            text-decoration: none;
            cursor: pointer;
        }
        .btn-action:hover {
            background: #e2e8f0;
        }
        .btn-whatsapp {
            background: #25d366;
            color: #fff;
            border-color: #25d366;
        }
        .btn-whatsapp:hover {
            background: #1eb956;
            color: #fff;
        }
        .btn-print {
            background: #0f172a;
            color: #fff;
            border-color: #0f172a;
        }
        .btn-print:hover {
            background: #1e293b;
            color: #fff;
        }
        
        @media print {
            .no-print { display: none !important; }
            body {
                width: 100%;
                margin: 0;
                padding: 0;
            }
        }
    </style>
</head>
<body onload="checkAutoPrint()">

    {{-- Screen Action Toolbar --}}
    <div class="actions-bar no-print">
        <button type="button" class="btn-action btn-print" onclick="window.print()">
            <i class="fas fa-print"></i> প্রিন্ট করুন
        </button>
        @php
            $waMsg = urlencode("আইডিয়া প্রকাশন — ক্যাশ মেমো #{$sale->receipt_no}\nমোট টাকা: ৳" . number_format($sale->total, 2) . "\nবিস্তারিত দেখুন: " . route('admin.pos.receipt', $sale->id));
            $waPhone = preg_replace('/[^0-9]/', '', (string)$sale->customer_phone);
            if ($waPhone && !str_starts_with($waPhone, '88')) {
                $waPhone = '88' . $waPhone;
            }
            $waUrl = $waPhone ? "https://wa.me/{$waPhone}?text={$waMsg}" : "https://wa.me/?text={$waMsg}";
        @endphp
        <a href="{{ $waUrl }}" target="_blank" class="btn-action btn-whatsapp">
            <i class="fab fa-whatsapp"></i> হোয়াটসঅ্যাপে পাঠান
        </a>
    </div>

    @if($sale->isVoided())
        <div class="void-banner">
            ⚠️ বাতিলকৃত মেমো (VOID / CANCELLED)<br>
            <span style="font-size: 11px; font-weight: normal;">কারণ: {{ $sale->void_reason }}</span>
        </div>
    @endif

    {{-- Header --}}
    <div class="text-center">
        <h2 style="font-size: 18px; font-weight: 800; margin-bottom: 2px;">আইডিয়া প্রকাশন</h2>
        <div style="font-size: 12px; font-weight: 600;">{{ $sale->register->name ?? 'অমর একুশে বইমেলা স্টল' }}</div>
        <div style="font-size: 11px; color: #475569;">{{ $sale->register->location ?? 'সোহরাওয়ার্দী উদ্যান, ঢাকা' }}</div>
        <div class="border-top-dash my-1"></div>
        <div style="font-size: 13px; font-weight: 700; letter-spacing: 0.5px;">ক্যাশ মেমো / CASH RECEIPT</div>
        <div class="border-bottom-dash my-1"></div>
    </div>

    {{-- Metadata --}}
    <table style="font-size: 11.5px; margin-bottom: 4px;">
        <tr>
            <td>মেমো নং:</td>
            <td class="text-right font-mono fw-bold">#{{ $sale->receipt_no }}</td>
        </tr>
        <tr>
            <td>তারিখ ও সময়:</td>
            <td class="text-right font-mono">{{ $sale->created_at->format('d/m/Y h:i A') }}</td>
        </tr>
        <tr>
            <td>ক্যাশিয়ার:</td>
            <td class="text-right">{{ $sale->cashier->name ?? 'স্টল স্টাফ' }}</td>
        </tr>
        @if($sale->customer_name && $sale->customer_name !== 'ওয়াক-ইন ক্রেতা (Walk-in)')
            <tr>
                <td>ক্রেতার নাম:</td>
                <td class="text-right fw-semibold">{{ $sale->customer_name }}</td>
            </tr>
        @endif
        @if($sale->customer_phone)
            <tr>
                <td>মোবাইল:</td>
                <td class="text-right font-mono">{{ $sale->customer_phone }}</td>
            </tr>
        @endif
    </table>

    <div class="border-top-dash my-1"></div>

    {{-- Items Table --}}
    <table>
        <thead>
            <tr class="border-bottom-dash">
                <th class="text-left" style="width: 58%;">বইয়ের নাম</th>
                <th class="text-center" style="width: 17%;">সংখ্যা</th>
                <th class="text-right" style="width: 25%;">মূল্য (৳)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->items_json ?? [] as $item)
                <tr>
                    <td class="text-left" style="padding-right: 4px;">
                        <div class="fw-semibold" style="line-height: 1.25;">{{ $item['title'] }}</div>
                        <small style="font-size: 10px; color: #64748b;" class="font-mono">৳{{ number_format($item['price'], 0) }} × {{ $item['quantity'] }}</small>
                    </td>
                    <td class="text-center font-mono">{{ $item['quantity'] }}</td>
                    <td class="text-right font-mono fw-semibold">৳{{ number_format($item['total'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="border-top-dash my-1"></div>

    {{-- Financial Summary --}}
    <table style="font-size: 12.5px;">
        <tr>
            <td>মোট বই সংখ্যা:</td>
            <td class="text-right font-mono fw-bold">{{ $sale->totalItemCount() }} টি</td>
        </tr>
        <tr>
            <td>উপ-মোট (Subtotal):</td>
            <td class="text-right font-mono">৳{{ number_format($sale->subtotal, 2) }}</td>
        </tr>
        @if($sale->discount > 0)
            <tr style="color: #b91c1c;">
                <td>মেলা বিশেষ ছাড় @if($sale->discount_percent > 0)({{ number_format($sale->discount_percent, 0) }}%)@endif:</td>
                <td class="text-right font-mono">-৳{{ number_format($sale->discount, 2) }}</td>
            </tr>
        @endif
        <tr class="border-top-dash fw-bold" style="font-size: 14px;">
            <td>পরিশোধযোগ্য সর্বমোট:</td>
            <td class="text-right font-mono">৳{{ number_format($sale->total, 2) }}</td>
        </tr>
        
        <tr>
            <td class="py-1">পরিশোধের মাধ্যম:</td>
            <td class="text-right font-mono fw-semibold text-uppercase py-1">
                @if($sale->payment_method === 'split')
                    স্প্লিট (ক্যাশ ৳{{ number_format($sale->paid_cash, 0) }} + অনলাইন ৳{{ number_format($sale->paid_online, 0) }})
                @else
                    {{ $sale->payment_method }}
                @endif
            </td>
        </tr>

        @if($sale->trx_id)
            <tr>
                <td>ট্রানজেকশন আইডি:</td>
                <td class="text-right font-mono fw-semibold">{{ $sale->trx_id }}</td>
            </tr>
        @endif

        @if($sale->tendered_amount > 0)
            <tr>
                <td>নগদ গ্রহণ (Tendered):</td>
                <td class="text-right font-mono">৳{{ number_format($sale->tendered_amount, 2) }}</td>
            </tr>
            @if($sale->change_amount > 0)
                <tr class="fw-bold" style="color: #15803d;">
                    <td>ফেরত টাকা (Change):</td>
                    <td class="text-right font-mono">৳{{ number_format($sale->change_amount, 2) }}</td>
                </tr>
            @endif
        @endif
    </table>

    <div class="border-top-dash my-2"></div>

    {{-- Footer & Notes --}}
    <div class="text-center" style="font-size: 11px; color: #334155;">
        <div class="fw-bold" style="font-size: 12px; margin-bottom: 2px;">বইমেলায় আইডিয়ার সাথে থাকার জন্য ধন্যবাদ!</div>
        <div>বই কিনুন, বই পড়ুন, প্রিয়জনকে বই উপহার দিন।</div>
        <div class="font-mono" style="margin-top: 4px; font-weight: 600;">www.ideaabd.com</div>
        <div style="font-size: 9px; color: #94a3b8; margin-top: 3px;">Software by Antigravity POS</div>
    </div>

    <script>
        function checkAutoPrint() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('print') === '1' || window.opener) {
                setTimeout(() => { window.print(); }, 400);
            }
        }
    </script>
</body>
</html>
