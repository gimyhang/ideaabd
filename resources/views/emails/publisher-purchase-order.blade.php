<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ক্রয় আদেশ — {{ $orderData['po_number'] ?? 'PO' }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif, 'SolaimanLipi', 'Kalpurush';
            background-color: #f1f5f9;
            margin: 0;
            padding: 20px;
            color: #1e293b;
        }
        .email-container {
            max-width: 680px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.06);
            border: 1px solid #e2e8f0;
        }
        .header {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            color: #ffffff;
            padding: 30px 25px;
            text-align: center;
        }
        .header h1 {
            margin: 0 0 5px 0;
            font-size: 24px;
            font-weight: 700;
        }
        .header p {
            margin: 0;
            opacity: 0.9;
            font-size: 14px;
        }
        .body {
            padding: 30px 25px;
        }
        .badge-po {
            display: inline-block;
            background-color: #eff6ff;
            color: #1d4ed8;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            border: 1px solid #bfdbfe;
            margin-bottom: 20px;
        }
        .info-grid {
            display: table;
            width: 100%;
            margin-bottom: 25px;
            background-color: #f8fafc;
            border-radius: 8px;
            padding: 15px;
            border: 1px solid #e2e8f0;
        }
        .info-col {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }
        .info-col h4 {
            margin: 0 0 8px 0;
            font-size: 14px;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .info-col p {
            margin: 2px 0;
            font-size: 13.5px;
            color: #1e293b;
        }
        .message-box {
            background-color: #fffbeb;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 25px;
            font-size: 14px;
            color: #92400e;
        }
        .table-responsive {
            width: 100%;
            margin-bottom: 25px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13.5px;
        }
        th {
            background-color: #f1f5f9;
            color: #334155;
            text-align: left;
            padding: 10px 8px;
            border-bottom: 2px solid #cbd5e1;
            font-weight: 600;
        }
        td {
            padding: 10px 8px;
            border-bottom: 1px solid #e2e8f0;
            color: #334155;
        }
        .text-end {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .totals-section {
            float: right;
            width: 260px;
            margin-bottom: 30px;
        }
        .totals-table {
            width: 100%;
        }
        .totals-table td {
            padding: 6px 8px;
            border: none;
        }
        .totals-table tr.grand-total td {
            border-top: 2px solid #1e293b;
            font-size: 15px;
            font-weight: 700;
            color: #1e3a8a;
        }
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
        .footer {
            background-color: #f8fafc;
            padding: 20px 25px;
            text-align: center;
            border-top: 1px solid #e2e8f0;
            font-size: 12.5px;
            color: #64748b;
        }
        .footer p {
            margin: 4px 0;
        }
    </style>
</head>
<body>

<div class="email-container">
    {{-- Header --}}
    <div class="header">
        <h1>{{ $senderSettings['business_name'] ?? 'আইডিয়া প্রকাশন' }}</h1>
        <p>{{ $senderSettings['address'] ?? 'সেন্ট্রাল রোড, রংপুর ৫৪০০, বাংলাদেশ' }} | ফোন: {{ $senderSettings['phone'] ?? '01558712870' }}</p>
    </div>

    {{-- Body --}}
    <div class="body">
        <div class="badge-po">
            📄 ক্রয় আদেশ নম্বর: {{ $orderData['po_number'] ?? 'PO' }}
        </div>

        <p style="font-size: 15px; margin-top: 0;">
            শ্রদ্ধেয় <strong>{{ $publisher->name }}</strong> কর্তৃপক্ষ,<br>
            শুভেচ্ছা রইল। {{ $senderSettings['business_name'] ?? 'আইডিয়া প্রকাশন' }}-এর পক্ষ থেকে নিম্নলিখিত বইসমূহের জন্য আনুষ্ঠানিক ক্রয় আদেশ (Purchase Order) প্রদান করা হলো। অনুগ্রহপূর্বক দ্রুত বই সরবরাহের ব্যবস্থা গ্রহণ করুন।
        </p>

        {{-- Info Grid --}}
        <div class="info-grid">
            <div class="info-col">
                <h4>প্রকাশক / সরবরাহকারী:</h4>
                <p><strong>{{ $publisher->name }}</strong></p>
                @if($publisher->phone)
                    <p>ফোন: {{ $publisher->phone }}</p>
                @endif
                @if($publisher->email)
                    <p>ইমেইল: {{ $publisher->email }}</p>
                @endif
                @if($publisher->address)
                    <p>ঠিকানা: {{ $publisher->address }}</p>
                @endif
            </div>
            <div class="info-col">
                <h4>ক্রয় আদেশের বিবরণ:</h4>
                <p>তারিখ: <strong>{{ $orderData['order_date'] ?? date('d M Y') }}</strong></p>
                @if(!empty($orderData['delivery_date']))
                    <p>প্রত্যাশিত ডেলিভারি: <strong>{{ $orderData['delivery_date'] }}</strong></p>
                @endif
                <p>প্রেরক: <strong>{{ $senderSettings['business_name'] ?? 'আইডিয়া প্রকাশন' }}</strong></p>
                <p>যোগাযোগ: {{ $senderSettings['phone'] ?? '01558712870' }}</p>
            </div>
        </div>

        {{-- Custom Message if provided --}}
        @if(!empty($customMessage) || !empty($orderData['notes']))
            <div class="message-box">
                <strong>📌 বিশেষ নির্দেশিকা / মন্তব্য:</strong><br>
                {{ $customMessage ?: $orderData['notes'] }}
            </div>
        @endif

        {{-- Books Table --}}
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th style="width: 30px;" class="text-center">#</th>
                        <th>বইয়ের নাম ও বিবরণ</th>
                        <th class="text-center" style="width: 60px;">কপি</th>
                        <th class="text-end" style="width: 85px;">গায়ের মূল্য</th>
                        <th class="text-center" style="width: 75px;">কমিশন (%)</th>
                        <th class="text-end" style="width: 85px;">ক্রয় রেট</th>
                        <th class="text-end" style="width: 95px;">মোট মূল্য</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $totalQty = 0;
                        $totalMrpSum = 0;
                        $grandTotal = 0;
                    @endphp
                    @foreach($orderData['items'] as $index => $item)
                        @php
                            $qty = (int) ($item['quantity'] ?? 1);
                            $unitPrice = (float) ($item['unit_price'] ?? 0);
                            $commission = (float) ($item['commission_percent'] ?? 0);
                            $costRate = (float) ($item['cost_price'] ?? ($unitPrice * (1 - ($commission / 100))));
                            $itemTotal = (float) ($item['total_price'] ?? ($costRate * $qty));

                            $totalQty += $qty;
                            $totalMrpSum += ($unitPrice * $qty);
                            $grandTotal += $itemTotal;
                        @endphp
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td>
                                <strong>{{ $item['title'] ?? '—' }}</strong>
                                @if(!empty($item['edition']))
                                    <div style="font-size: 11.5px; color: #64748b;">সংস্করণ: {{ $item['edition'] }}</div>
                                @endif
                                @if(!empty($item['author']))
                                    <div style="font-size: 11.5px; color: #64748b;">লেখক: {{ $item['author'] }}</div>
                                @endif
                            </td>
                            <td class="text-center"><strong>{{ $qty }}</strong> টি</td>
                            <td class="text-end">৳{{ number_format($unitPrice, 2) }}</td>
                            <td class="text-center">{{ $commission > 0 ? $commission . '%' : '—' }}</td>
                            <td class="text-end">৳{{ number_format($costRate, 2) }}</td>
                            <td class="text-end"><strong>৳{{ number_format($itemTotal, 2) }}</strong></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Totals --}}
        <div class="clearfix">
            <div class="totals-section">
                <table class="totals-table">
                    <tr>
                        <td>মোট নির্বাচিত আইটেম:</td>
                        <td class="text-end"><strong>{{ count($orderData['items']) }}</strong> টি বই</td>
                    </tr>
                    <tr>
                        <td>সর্বমোট সংখ্যা (কপি):</td>
                        <td class="text-end"><strong>{{ $totalQty }}</strong> টি</td>
                    </tr>
                    <tr>
                        <td>গায়ের মূল্যের সর্বমোট:</td>
                        <td class="text-end">৳{{ number_format($totalMrpSum, 2) }}</td>
                    </tr>
                    @if($totalMrpSum > $grandTotal)
                        <tr>
                            <td style="color: #16a34a;">মোট ক্রয় ছাড় / কমিশন:</td>
                            <td class="text-end" style="color: #16a34a;">-৳{{ number_format($totalMrpSum - $grandTotal, 2) }}</td>
                        </tr>
                    @endif
                    <tr class="grand-total">
                        <td>মোট প্রদেয় ক্রয়মূল্য:</td>
                        <td class="text-end">৳{{ number_format($grandTotal, 2) }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <p style="font-size: 13.5px; color: #475569; margin-top: 15px;">
            চালান ও পার্সেল প্রস্তুত হলে আমাদের উল্লিখিত ঠিকানায় প্রেরণ করার অনুরোধ করা হলো। কোনো বিষয়ে আলোচনার প্রয়োজন হলে সরাসরি আমাদের সাথে ফোনে বা ইমেইলে যোগাযোগ করুন।
        </p>

        <div style="background-color: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 8px; padding: 12px 16px; margin-top: 18px; text-align: center; font-size: 12px; color: #64748b; line-height: 1.5;">
            🔔 <strong>বিশেষ বিজ্ঞপ্তি:</strong> এটি আইডিয়া প্রকাশনের একটি স্বয়ংক্রিয় অফিসিয়াল বার্তা, এতে রিপ্লাই (Reply) করার প্রয়োজন নেই। যেকোনো তথ্য বা জরুরি প্রয়োজনে আমাদের হেল্পলাইনে <strong>০১৭২৬-৯৭৬৯৮২ / ০১৫৫৮-৭১২৮১০</strong> নম্বরে কল করুন অথবা ভিজিট করুন <a href="https://www.ideaabd.com" style="color: #1e3a8a; text-decoration: none; font-weight: bold;">www.ideaabd.com</a>।
        </div>
    </div>

    {{-- Footer --}}
    <div class="footer">
        <p><strong>{{ $senderSettings['business_name'] ?? 'আইডিয়া প্রকাশন' }}</strong></p>
        <p>{{ $senderSettings['address'] ?? 'সেন্ট্রাল রোড, রংপুর ৫৪০০' }} | হেল্পলাইন: {{ $senderSettings['phone'] ?? '০১৭২৬-৯৭৬৯৮২, ০১৫৫৮-৭১২৮১০' }} | ইমেইল: ad@ideaabd.com</p>
        <p style="font-size: 11px; margin-top: 8px; color: #94a3b8;">© {{ date('Y') }} আইডিয়া প্রকাশন (ideaabd.com)। সর্বস্বত্ব সংরক্ষিত।</p>
    </div>
</div>

</body>
</html>
