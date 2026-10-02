<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ইনভয়েস #{{ $order->order_number ?? $order->id }} — {{ \App\Support\SiteSetting::name() ?: 'আইডিয়া প্রকাশন' }}</title>
    
    <!-- Google Fonts & FontAwesome -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.maateen.me/kalpurush/font.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    @php
        $siteLogo    = \App\Support\SiteSetting::logoUrl() ?: asset('images/logo.png');
        $siteName    = $invoiceSettings['sender_name'] ?? (\App\Support\SiteSetting::name() ?: 'আইডিয়া প্রকাশন');
        $siteAddress = $invoiceSettings['sender_address'] ?? (\App\Support\SiteSetting::get('contact_address') ?: 'সেন্ট্রাল রোড, রংপুর ৫৪০০, বাংলাদেশ');
        $sitePhone   = $invoiceSettings['sender_phone'] ?? (\App\Support\SiteSetting::get('contact_phone') ?: '01558712870');
        $siteEmail   = $invoiceSettings['sender_email'] ?? (\App\Support\SiteSetting::get('contact_email') ?: 'ideapbd@gmail.com');
        $siteWebsite = $invoiceSettings['sender_website'] ?? 'www.ideaabd.com';
        $invoiceTitle = $invoiceSettings['invoice_title'] ?? 'ক্যাশ মেমো / ইনভয়েস';
        $invoiceTerms = $invoiceSettings['invoice_terms'] ?? 'পণ্য গ্রহণের সময় অনুগ্রহ করে চেক করে নিন। কোনো ত্রুটি থাকলে ডেলিভারি ম্যানের উপস্থিতিতেই যোগাযোগ করুন।';
        $invoiceFooter = $invoiceSettings['invoice_footer'] ?? 'বই পড়ার আনন্দ ছড়িয়ে পড়ুক সবার মাঝে। ideaabd-এর সাথে থাকার জন্য ধন্যবাদ!';

        // Financial Calculation
        $itemUnit = $order->unit_price > 0 ? $order->unit_price : ($order->book->discount_price ?? $order->book->price ?? 0);
        $itemQty = $order->quantity ?? 1;
        $itemSubtotal = $itemUnit * $itemQty;
        $shippingCost = $order->shipping_cost ?? 0;
        $giftFee = $order->is_gift ? ($order->gift_wrap_fee > 0 ? $order->gift_wrap_fee : 20) : 0;
        $discountAmount = $order->discount_amount ?? 0;
        $grandTotal = $order->total_amount > 0 ? $order->total_amount : ($itemSubtotal + $shippingCost + $giftFee - $discountAmount);

        // Convert number to English words
        if (!function_exists('numberToWordsEnInvoice')) {
            function numberToWordsEnInvoice($num) {
                $num = (int)$num;
                if ($num == 0) return 'Zero Taka Only';

                $units = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
                $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

                $crore = floor($num / 10000000);
                $num %= 10000000;
                $lakh = floor($num / 100000);
                $num %= 100000;
                $thousand = floor($num / 1000);
                $num %= 1000;
                $hundred = floor($num / 100);
                $remainder = $num % 100;

                $words = [];
                if ($crore > 0) $words[] = ($crore < 20 ? $units[$crore] : $tens[floor($crore/10)] . ($crore%10 ? ' ' . $units[$crore%10] : '')) . ' Crore';
                if ($lakh > 0) $words[] = ($lakh < 20 ? $units[$lakh] : $tens[floor($lakh/10)] . ($lakh%10 ? ' ' . $units[$lakh%10] : '')) . ' Lakh';
                if ($thousand > 0) $words[] = ($thousand < 20 ? $units[$thousand] : $tens[floor($thousand/10)] . ($thousand%10 ? ' ' . $units[$thousand%10] : '')) . ' Thousand';
                if ($hundred > 0) $words[] = $units[$hundred] . ' Hundred';
                if ($remainder > 0) {
                    if ($remainder < 20) {
                        $words[] = $units[$remainder];
                    } else {
                        $t = floor($remainder / 10);
                        $u = $remainder % 10;
                        $words[] = $tens[$t] . ($u > 0 ? ' ' . $units[$u] : '');
                    }
                }
                return implode(' ', $words) . ' Taka Only';
            }
        }
        $amountInWords = numberToWordsEnInvoice($grandTotal);

        // Payment method label
        $paymentMethodLabel = match(strtolower($order->payment_method ?? 'cod')) {
            'cod'    => 'ক্যাশ অন ডেলিভারি (COD)',
            'bkash'  => 'বিকাশ অনলাইন (bKash)',
            'nagad'  => 'নগদ অনলাইন (Nagad)',
            'rocket' => 'রকেট (Rocket)',
            'card'   => 'কার্ড পেমেন্ট (Card)',
            default  => strtoupper($order->payment_method ?? 'COD'),
        };

        // Digital signature
        $userSignature = auth()->user()?->reg_data['signature'] ?? null;
        $hasSignature = !empty($userSignature) && \Illuminate\Support\Facades\Storage::disk('public')->exists($userSignature);

        // Tracking URL & QR Code
        $trackUrl = url('/track-order?order_no=' . ($order->order_number ?? $order->id));
        $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=80x80&margin=0&data=' . urlencode($trackUrl);

        // WhatsApp Memo Link
        $cleanPhone = preg_replace('/[^0-9]/', '', $order->customer_phone);
        if (str_starts_with($cleanPhone, '01')) {
            $cleanPhone = '88' . $cleanPhone;
        }
        $whatsappMessage = "প্রিয় {$order->customer_name}!\nআইডিয়া প্রকাশনে আপনার অর্ডারটি নিশ্চিত করা হয়েছে।\n\nইনভয়েস: #{$order->order_number}\nমোট প্রদেয়: ৳ " . number_format($grandTotal, 2) . "\nপেমেন্ট: " . ($order->payment_status === 'paid' ? 'পরিশোধিত (PAID)' : 'ক্যাশ অন ডেলিভারি (COD)') . "\n\nঅনলাইন চালান ও ট্র্যাকিং:\n{$trackUrl}\n\nধন্যবাদ,\n{$siteName}";
        $whatsappUrl = "https://api.whatsapp.com/send?phone={$cleanPhone}&text=" . urlencode($whatsappMessage);
    @endphp

    <style>
        /* ══════════════════════════════════════════════════════════════════
           MINIMALIST MONOCHROME COMMERCIAL INVOICE STYLESHEET
           সাদাকালো পরিচ্ছন্ন ডিজাইন ও সিঙ্গেল পেজ প্রিন্ট
           ══════════════════════════════════════════════════════════════════ */
        *, *::before, *::after {
            box-sizing: border-box;
        }
        body {
            font-family: 'Inter', 'Kalpurush', 'Hind Siliguri', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: #ffffff; /* সাদাকালো ক্লিন ব্যাকগ্রাউন্ড */
            color: #000000;
            font-size: 12.5px;
            line-height: 1.4;
            margin: 0;
            padding: 20px 12px;
            -webkit-font-smoothing: antialiased;
        }

        /* Top Action Bar (Screen Only - Hidden on Print) */
        .inv-action-bar {
            max-width: 800px;
            margin: 0 auto 16px auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            flex-wrap: wrap;
            padding: 8px 12px;
            background: #ffffff;
            border: 1px solid #000000;
            border-radius: 6px;
        }
        .inv-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            border: 1px solid #000000;
            background: #ffffff;
            color: #000000;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .inv-btn:hover {
            background: #000000;
            color: #ffffff;
        }
        .inv-btn-primary {
            background: #000000;
            color: #ffffff;
        }
        .inv-btn-primary:hover {
            background: #222222;
            color: #ffffff;
        }

        /* Main Printable Document Container */
        .inv-container {
            max-width: 800px;
            margin: 0 auto;
            background: #ffffff;
            border: 1.5px solid #000000;
            padding: 26px 30px;
        }

        /* Header: Brand & Document Meta (Zero Duplication) */
        .inv-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 20px;
            padding-bottom: 14px;
            border-bottom: 2px solid #000000;
            margin-bottom: 16px;
        }
        .inv-brand-box {
            display: flex;
            align-items: center;
            gap: 14px;
            flex: 1;
        }
        .inv-logo {
            max-height: 48px;
            max-width: 140px;
            width: auto;
            object-fit: contain;
            display: block;
            flex-shrink: 0;
            filter: grayscale(100%); /* সাদাকালো লোগো */
        }
        .inv-brand-text h1 {
            font-size: 17px;
            font-weight: 800;
            color: #000000;
            margin: 0 0 2px 0;
            line-height: 1.2;
        }
        .inv-brand-text p {
            font-size: 11px;
            color: #222222;
            margin: 0;
            line-height: 1.35;
        }

        .inv-meta-box {
            text-align: right;
            flex-shrink: 0;
        }
        .inv-doc-title {
            font-size: 17px;
            font-weight: 800;
            color: #000000;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0 0 3px 0;
            line-height: 1.2;
        }
        .inv-number {
            font-family: 'Courier New', Courier, monospace;
            font-size: 15px;
            font-weight: 800;
            color: #000000;
            margin-bottom: 4px;
        }
        .inv-badge-monochrome {
            display: inline-block;
            border: 1.5px solid #000000;
            color: #000000;
            background: #ffffff;
            font-size: 10.5px;
            font-weight: 800;
            padding: 2px 8px;
            letter-spacing: 0.5px;
            border-radius: 3px;
        }
        .inv-badge-monochrome.is-paid {
            background: #000000;
            color: #ffffff;
        }

        /* Information Grid: Customer Details & Order Meta (No Duplication) */
        .inv-info-grid {
            display: grid;
            grid-template-columns: 1.35fr 1fr;
            gap: 16px;
            padding: 10px 14px;
            border: 1px solid #000000;
            margin-bottom: 16px;
            font-size: 11.5px;
            background: #ffffff;
        }
        .inv-info-heading {
            font-size: 10.5px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #000000;
            border-bottom: 1px solid #000000;
            padding-bottom: 3px;
            margin-bottom: 6px;
        }
        .inv-customer-name {
            font-size: 14px;
            font-weight: 800;
            color: #000000;
            margin-bottom: 2px;
        }
        .inv-customer-phone {
            font-weight: 700;
            color: #000000;
            font-family: 'Courier New', Courier, monospace;
            font-size: 12.5px;
            margin-bottom: 2px;
        }
        .inv-customer-addr {
            color: #111111;
            line-height: 1.35;
        }

        .inv-order-meta-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
            line-height: 1.35;
        }
        .inv-order-meta-item:last-child {
            margin-bottom: 0;
        }
        .inv-order-meta-label {
            color: #333333;
        }
        .inv-order-meta-val {
            font-weight: 700;
            color: #000000;
            text-align: right;
        }

        /* Items Table (Pure Minimalist B&W) */
        .inv-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }
        .inv-table th {
            background: #ffffff;
            color: #000000;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 6px 8px;
            border-top: 1.5px solid #000000;
            border-bottom: 1.5px solid #000000;
        }
        .inv-table td {
            padding: 8px 8px;
            border-bottom: 1px solid #cccccc;
            vertical-align: middle;
            font-size: 12px;
            color: #000000;
        }
        .inv-table tbody tr:last-child td {
            border-bottom: 1.5px solid #000000;
        }

        /* Bottom Section: Policy & Financial Summary */
        .inv-bottom-grid {
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
            gap: 20px;
            align-items: start;
            margin-bottom: 16px;
        }
        
        .inv-policy-box {
            font-size: 10.5px;
            color: #222222;
            border: 1px solid #000000;
            padding: 8px 10px;
            margin-bottom: 10px;
            line-height: 1.35;
        }
        .inv-policy-title {
            font-weight: 800;
            color: #000000;
            margin-bottom: 2px;
            text-transform: uppercase;
            font-size: 10px;
            letter-spacing: 0.5px;
        }
        .inv-qr-wrap {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .inv-qr-img {
            width: 48px;
            height: 48px;
            border: 1px solid #000000;
            padding: 1px;
            background: #ffffff;
            flex-shrink: 0;
        }
        .inv-qr-info {
            font-size: 10px;
            color: #333333;
            line-height: 1.3;
        }
        .inv-qr-info strong {
            color: #000000;
            display: block;
            margin-bottom: 1px;
            font-size: 10.5px;
        }

        /* Totals Table */
        .inv-totals-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11.5px;
        }
        .inv-totals-table td {
            padding: 3px 0;
            color: #000000;
        }
        .inv-totals-label {
            color: #333333;
        }
        .inv-totals-val {
            text-align: right;
            font-family: 'Courier New', Courier, monospace;
            font-weight: 700;
            color: #000000;
            font-size: 12px;
        }
        .inv-grand-total td {
            border-top: 1.5px solid #000000;
            border-bottom: 1.5px solid #000000;
            padding: 6px 0;
            font-size: 14px;
            font-weight: 800;
            color: #000000;
        }
        .inv-grand-total .inv-totals-val {
            font-size: 15px;
        }
        .inv-words {
            font-size: 10.5px;
            color: #222222;
            text-align: right;
            margin-top: 4px;
            line-height: 1.3;
        }

        /* Footer & Signature Line */
        .inv-footer {
            border-top: 1px solid #000000;
            padding-top: 12px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            font-size: 10.5px;
            color: #333333;
        }
        .inv-sig-box {
            text-align: center;
            min-width: 130px;
        }
        .inv-sig-img {
            max-height: 32px;
            max-width: 110px;
            object-fit: contain;
            margin-bottom: 2px;
            filter: grayscale(100%);
        }
        .inv-sig-line {
            border-top: 1px solid #000000;
            padding-top: 3px;
            font-weight: 700;
            color: #000000;
            font-size: 10.5px;
        }

        /* ══════════════════════════════════════════════════════════════════
           PRINT MEDIA: STRICT SINGLE A4 SHEET FIT
           ══════════════════════════════════════════════════════════════════ */
        @media print {
            html, body {
                background: #ffffff !important;
                margin: 0 !important;
                padding: 0 !important;
                color: #000000 !important;
                font-size: 11px !important;
                line-height: 1.3 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .inv-action-bar, .modal, .d-print-none {
                display: none !important;
            }
            .inv-container {
                max-width: 100% !important;
                margin: 0 !important;
                padding: 4mm 6mm !important;
                border: none !important;
                box-shadow: none !important;
            }
            .inv-header, .inv-info-grid, .inv-table, .inv-bottom-grid, .inv-footer, tr {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
            @page {
                size: A4 portrait;
                margin: 6mm 8mm;
            }
        }

        /* Mobile Screen Adjustments */
        @media (max-width: 600px) {
            body { padding: 8px 4px; }
            .inv-container { padding: 14px 12px; }
            .inv-header { flex-direction: column; gap: 10px; }
            .inv-meta-box { text-align: left; }
            .inv-info-grid { grid-template-columns: 1fr; }
            .inv-bottom-grid { grid-template-columns: 1fr; gap: 12px; }
            .inv-words { text-align: left; }
        }
    </style>
</head>
<body>

    <!-- Top Action Bar (Hidden on Print) -->
    <div class="inv-action-bar d-print-none">
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.ecommerce-orders') }}" class="inv-btn" title="সকল অর্ডার তালিকা">
                <i class="fa-solid fa-arrow-left"></i> অর্ডার তালিকা
            </a>
            <a href="{{ route('admin.ecommerce-orders.slip', $order) }}" target="_blank" class="inv-btn" title="পার্সেল স্লিপ ও লেবেল">
                <i class="fa-solid fa-tag"></i> পার্সেল স্লিপ
            </a>
            <button type="button" class="inv-btn" data-bs-toggle="modal" data-bs-target="#modalQuickEdit" title="তথ্য দ্রুত সম্পাদন করুন">
                <i class="fa-solid fa-pen-to-square"></i> তথ্য সম্পাদন
            </button>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="{{ $whatsappUrl }}" target="_blank" class="inv-btn" title="গ্রাহকের হোয়াটসঅ্যাপে মেমো পাঠান">
                <i class="fa-brands fa-whatsapp"></i> হোয়াটসঅ্যাপ মেমো
            </a>
            <button type="button" class="inv-btn" onclick="copyInvoiceLink()" title="ট্র্যাকিং লিংক কপি করুন">
                <i class="fa-solid fa-link" id="copyLinkIcon"></i> লিংক কপি
            </button>
            <button onclick="window.print()" class="inv-btn inv-btn-primary" title="প্রিন্ট করুন">
                <i class="fa-solid fa-print"></i> প্রিন্ট ইনভয়েস
            </button>
        </div>
    </div>

    <!-- Printable Invoice Document (Pure B&W) -->
    <div class="inv-container">

        <!-- Header: Brand & Document Title (Zero Duplication) -->
        <div class="inv-header">
            <div class="inv-brand-box">
                <img src="{{ $siteLogo }}" alt="{{ $siteName }}" class="inv-logo" onerror="this.style.display='none';">
                <div class="inv-brand-text">
                    <h1>{{ $siteName }}</h1>
                    <p>
                        {{ $siteAddress }}<br>
                        হটলাইন: <strong>{{ $sitePhone }}</strong> • ইমেইল: {{ $siteEmail }}<br>
                        ওয়েবসাইট: {{ $siteWebsite }}
                    </p>
                </div>
            </div>

            <div class="inv-meta-box">
                <div class="inv-doc-title">{{ $invoiceTitle }}</div>
                <div class="inv-number">#{{ $order->order_number ?? $order->id }}</div>
                <div>
                    @if($order->payment_status === 'paid')
                        <span class="inv-badge-monochrome is-paid">পরিশোধিত / PAID</span>
                    @else
                        <span class="inv-badge-monochrome">বকেয়া / COD</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Information Grid: Customer Details & Order Meta (Zero Duplication) -->
        <div class="inv-info-grid">
            <!-- Left: Customer / Delivery Address -->
            <div>
                <div class="inv-info-heading">প্রাপক ও ডেলিভারি ঠিকানা:</div>
                <div class="inv-customer-name">{{ $order->customer_name }}</div>
                <div class="inv-customer-phone">{{ $order->customer_phone }}</div>
                <div class="inv-customer-addr">
                    @if($order->house_road){{ $order->house_road }}, @endif
                    {{ $order->customer_address }}
                    @if($order->thana) • থানা: {{ $order->thana }}@endif
                    @if($order->district || $order->district_label) • জেলা: {{ $order->district ?? $order->district_label }}@endif
                    @if($order->post_code) ({{ $order->post_code }})@endif
                </div>

                @if($order->is_gift)
                    <div style="margin-top: 4px; font-size: 10.5px; font-weight: 700;">
                        [গিফট পার্সেল] প্রাপক: {{ $order->gift_recipient_name }} ({{ $order->gift_recipient_phone }})
                        @if($order->gift_message) — "{{ $order->gift_message }}"@endif
                    </div>
                @endif
            </div>

            <!-- Right: Order Details -->
            <div>
                <div class="inv-info-heading">অর্ডারের বিবরণ:</div>
                <div class="inv-order-meta-item">
                    <span class="inv-order-meta-label">তারিখ ও সময়:</span>
                    <span class="inv-order-meta-val">{{ $order->created_at ? $order->created_at->format('d M, Y — h:i A') : date('d M, Y') }}</span>
                </div>
                <div class="inv-order-meta-item">
                    <span class="inv-order-meta-label">পেমেন্ট মাধ্যম:</span>
                    <span class="inv-order-meta-val">{{ $paymentMethodLabel }}</span>
                </div>
                @if($order->transaction_id)
                <div class="inv-order-meta-item">
                    <span class="inv-order-meta-label">লেনদেন (TrxID):</span>
                    <span class="inv-order-meta-val font-monospace">{{ $order->transaction_id }}</span>
                </div>
                @endif
                @if($order->courier_name)
                <div class="inv-order-meta-item">
                    <span class="inv-order-meta-label">কুরিয়ার:</span>
                    <span class="inv-order-meta-val">{{ $order->courier_name }}</span>
                </div>
                @endif
                @if($order->tracking_code)
                <div class="inv-order-meta-item">
                    <span class="inv-order-meta-label">ট্র্যাকিং নং:</span>
                    <span class="inv-order-meta-val font-monospace">{{ $order->tracking_code }}</span>
                </div>
                @endif
            </div>
        </div>

        <!-- Items Table -->
        <table class="inv-table">
            <thead>
                <tr>
                    <th style="width: 6%; text-align: center;">ক্র.</th>
                    <th style="width: 52%;">বই / পণ্যের বিবরণ</th>
                    <th style="width: 15%; text-align: right;">একক মূল্য</th>
                    <th style="width: 10%; text-align: center;">পরিমাণ</th>
                    <th style="width: 17%; text-align: right;">মোট (টাকা)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="text-align: center; color: #444444;">১</td>
                    <td>
                        <strong style="color: #000000; font-size: 12.5px;">
                            {{ $order->book ? $order->book->title : ('বই অর্ডার #' . ($order->order_number ?? $order->id)) }}
                        </strong>
                        @if($order->book && $order->book->authors && $order->book->authors->count())
                            <div style="font-size: 10.5px; color: #444444;">লেখক: {{ $order->book->authors->pluck('name')->implode(', ') }}</div>
                        @endif
                        @if($order->book && $order->book->isbn)
                            <div style="font-size: 10px; color: #666666;">ISBN: {{ $order->book->isbn }}</div>
                        @endif
                    </td>
                    <td style="text-align: right; font-family: 'Courier New', Courier, monospace;">
                        ৳ {{ number_format($itemUnit, 2) }}
                    </td>
                    <td style="text-align: center; font-weight: 700;">
                        {{ $itemQty }}
                    </td>
                    <td style="text-align: right; font-weight: 700; font-family: 'Courier New', Courier, monospace;">
                        ৳ {{ number_format($itemSubtotal, 2) }}
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Summary & Verification Row -->
        <div class="inv-bottom-grid">
            
            <!-- Left: Terms & QR -->
            <div>
                <div class="inv-policy-box">
                    <div class="inv-policy-title">শর্তাবলী ও গ্রাহক সহায়তা:</div>
                    <div>{{ $invoiceTerms }}</div>
                    @if($order->admin_notes)
                        <div style="margin-top: 3px; padding-top: 3px; border-top: 1px dashed #000000;">
                            <strong>নোট:</strong> {{ $order->admin_notes }}
                        </div>
                    @endif
                </div>

                <div class="inv-qr-wrap">
                    <img src="{{ $qrUrl }}" alt="QR" class="inv-qr-img">
                    <div class="inv-qr-info">
                        <strong>ডিজিটাল ভেরিফিকেশন ও ট্র্যাকিং</strong>
                        স্মার্টফোনে কিউআর স্ক্যান করে চালানের সত্যতা ও ডেলিভারি ট্র্যাকিং যাচাই করুন।
                    </div>
                </div>
            </div>

            <!-- Right: Totals -->
            <div>
                <table class="inv-totals-table">
                    <tr>
                        <td class="inv-totals-label">পণ্যের মূল্য (Subtotal):</td>
                        <td class="inv-totals-val">৳ {{ number_format($itemSubtotal, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="inv-totals-label">ডেলিভারি চার্জ:</td>
                        <td class="inv-totals-val">
                            @if($shippingCost > 0)
                                ৳ {{ number_format($shippingCost, 2) }}
                            @else
                                <span>৳ 0.00 (ফ্রি)</span>
                            @endif
                        </td>
                    </tr>
                    @if($order->is_gift && $giftFee > 0)
                    <tr>
                        <td class="inv-totals-label">গিফট র‍্যাপিং চার্জ:</td>
                        <td class="inv-totals-val">৳ {{ number_format($giftFee, 2) }}</td>
                    </tr>
                    @endif
                    @if($discountAmount > 0)
                    <tr>
                        <td class="inv-totals-label">বিশেষ ছাড় (Discount):</td>
                        <td class="inv-totals-val">- ৳ {{ number_format($discountAmount, 2) }}</td>
                    </tr>
                    @endif
                    <tr class="inv-grand-total">
                        <td>সর্বমোট প্রদেয় (Total):</td>
                        <td class="inv-totals-val">৳ {{ number_format($grandTotal, 2) }}</td>
                    </tr>
                </table>

                <div class="inv-words">
                    <strong>কথায়:</strong> {{ $amountInWords }}
                </div>
            </div>

        </div>

        <!-- Footer & Signature -->
        <div class="inv-footer">
            <div>
                <div style="font-weight: 700; color: #000000;">{{ $invoiceFooter }}</div>
                <div style="font-size: 9.5px; color: #555555; margin-top: 1px;">
                    কম্পিউটার জেনারেটেড ডিজিটাল ইনভয়েস
                </div>
            </div>

            <div class="inv-sig-box">
                @if($hasSignature)
                    <img src="{{ asset('storage/' . $userSignature) }}" alt="Signature" class="inv-sig-img">
                @else
                    <div style="height: 26px;"></div>
                @endif
                <div class="inv-sig-line">কর্তৃপক্ষের স্বাক্ষর</div>
            </div>
        </div>

    </div>

    <!-- Quick In-Place Edit Modal (Hidden on Print) -->
    <div class="modal fade d-print-none" id="modalQuickEdit" tabindex="-1" aria-labelledby="modalQuickEditLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-3">
                <div class="modal-header border-bottom p-3">
                    <h6 class="modal-title fw-bold text-dark" id="modalQuickEditLabel">
                        <i class="fa-solid fa-pen-to-square me-1.5"></i> ইনভয়েস ও অর্ডার তথ্য সম্পাদন
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formQuickEdit" onsubmit="handleQuickEditSubmit(event)">
                    <div class="modal-body p-3.5 space-y-3">
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-muted">পেমেন্ট অবস্থা <span class="text-danger">*</span></label>
                            <select name="payment_status" id="editPaymentStatus" class="form-select form-select-sm">
                                <option value="pending" @selected($order->payment_status === 'pending')>বকেয়া / Pending (COD)</option>
                                <option value="paid" @selected($order->payment_status === 'paid')>পরিশোধিত / Paid</option>
                                <option value="unpaid" @selected($order->payment_status === 'unpaid')>অপরিশোধিত / Unpaid</option>
                                <option value="partial" @selected($order->payment_status === 'partial')>আংশিক / Partial Paid</option>
                            </select>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label fw-semibold small text-muted">কুরিয়ার নাম</label>
                                <input type="text" name="courier_name" id="editCourierName" class="form-control form-control-sm" value="{{ $order->courier_name }}" placeholder="উদা: সুন্দরবন, স্টিডফাস্ট">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold small text-muted">ট্র্যাকিং কোড</label>
                                <input type="text" name="tracking_code" id="editTrackingCode" class="form-control form-control-sm" value="{{ $order->tracking_code }}" placeholder="উদা: ST12345678">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-muted">লেনদেন আইডি (TrxID)</label>
                            <input type="text" name="transaction_id" id="editTrxId" class="form-control form-control-sm font-monospace" value="{{ $order->transaction_id }}" placeholder="বিকাশ/নগদ TrxID">
                        </div>

                        <div class="mb-0">
                            <label class="form-label fw-semibold small text-muted">অ্যাডমিন নোট / বিশেষ নির্দেশনা</label>
                            <textarea name="admin_notes" id="editAdminNotes" rows="2" class="form-control form-control-sm" placeholder="প্রয়োজনীয় অভ্যন্তরীণ নোট...">{{ $order->admin_notes }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-top p-2.5">
                        <button type="button" class="btn btn-sm btn-outline-secondary px-3" data-bs-dismiss="modal">বাতিল</button>
                        <button type="submit" id="btnSaveQuickEdit" class="btn btn-sm btn-dark px-3 fw-bold">
                            <i class="fa-solid fa-floppy-disk me-1"></i> সংরক্ষণ করুন
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Copy Invoice Track Link
        function copyInvoiceLink() {
            navigator.clipboard.writeText('{{ $trackUrl }}').then(() => {
                const icon = document.getElementById('copyLinkIcon');
                if (icon) {
                    icon.className = 'fa-solid fa-check';
                    setTimeout(() => {
                        icon.className = 'fa-solid fa-link';
                    }, 2000);
                }
            });
        }

        // Quick Edit AJAX Handler
        function handleQuickEditSubmit(e) {
            e.preventDefault();
            const btn = document.getElementById('btnSaveQuickEdit');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> সংরক্ষণ হচ্ছে...';

            const payload = {
                _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                _method: 'PUT',
                payment_status: document.getElementById('editPaymentStatus').value,
                courier_name: document.getElementById('editCourierName').value,
                tracking_code: document.getElementById('editTrackingCode').value,
                transaction_id: document.getElementById('editTrxId').value,
                admin_notes: document.getElementById('editAdminNotes').value,
            };

            fetch('{{ route("admin.ecommerce-orders.update", $order) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-floppy-disk me-1"></i> সংরক্ষণ করুন';

                if (data.success) {
                    const modalEl = document.getElementById('modalQuickEdit');
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) modal.hide();
                    window.location.reload();
                } else {
                    alert(data.message || 'সংরক্ষণ ব্যর্থ হয়েছে।');
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-floppy-disk me-1"></i> সংরক্ষণ করুন';
                alert('সার্ভার এরর: ' + err.message);
            });
        }
    </script>

</body>
</html>
