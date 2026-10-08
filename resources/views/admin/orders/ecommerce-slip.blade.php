<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
        // 1. Adapter: Normalize $order or $slip for universal bill compatibility
        if (empty($slip) && !empty($order)) {
            $paid = ($order->payment_status === 'paid') ? (float)$order->total_amount : 0.0;
            $due = max(0.0, (float)$order->total_amount - $paid);
            $slip = [
                'type'             => 'order',
                'type_badge'       => 'বুক পার্সেল / PARCEL',
                'doc_no'           => $order->order_number ?? $order->id,
                'date'             => !empty($order->created_at) ? \Carbon\Carbon::parse($order->created_at)->format('d M, Y') : date('d M, Y'),
                'customer_name'    => $order->customer_name ?: 'সম্মানিত গ্রাহক',
                'customer_phone'   => $order->customer_phone,
                'customer_address' => trim(($order->house_road ? $order->house_road . ', ' : '') . ($order->customer_address ?: '')),
                'customer_org'     => null,
                'thana'            => $order->thana,
                'post_code'        => $order->post_code,
                'district'         => $order->district ?? $order->district_label,
                'total_amount'     => (float) $order->total_amount,
                'paid_amount'      => $paid,
                'due_amount'       => $due,
                'payment_status'   => $order->payment_status,
                'payment_method'   => $order->payment_method ?: 'cod',
                'items'            => [],
                'items_count'      => (int) ($order->quantity ?: 1),
                'items_title'      => $order->book->title ?? 'বই অর্ডার',
                'is_gift'          => (bool) $order->is_gift,
                'gift_recipient'   => $order->gift_recipient_name,
                'gift_phone'       => $order->gift_recipient_phone,
                'courier_name'     => $order->courier_name ?: 'সাধারণ কুরিয়ার',
                'tracking_code'    => $order->tracking_code,
                'back_url'         => route('admin.ecommerce-orders'),
                'invoice_url'      => route('admin.ecommerce-orders.invoice', $order->id),
            ];
        }

        $slipType          = $slip['type'] ?? 'order';
        $slipBadge         = $slip['type_badge'] ?? 'ডকুমেন্ট স্লিপ / SLIP';
        $docNo             = $slip['doc_no'] ?? 'N/A';
        $docDate           = $slip['date'] ?? date('d M, Y');
        $customerName      = $slip['customer_name'] ?? 'সম্মানিত গ্রাহক';
        $customerPhone     = $slip['customer_phone'] ?? null;
        $customerAddress   = $slip['customer_address'] ?? 'ঠিকানা উল্লেখ নেই';
        $customerOrg       = $slip['customer_org'] ?? null;
        $customerThana     = $slip['thana'] ?? null;
        $customerPostCode  = $slip['post_code'] ?? null;
        $customerDistrict  = $slip['district'] ?? null;
        $totalAmount       = (float) ($slip['total_amount'] ?? 0);
        $paidAmount        = (float) ($slip['paid_amount'] ?? 0);
        $dueAmount         = (float) ($slip['due_amount'] ?? 0);
        $paymentStatus     = $slip['payment_status'] ?? 'paid';
        $paymentMethod     = $slip['payment_method'] ?? 'cash';
        $itemsList         = $slip['items'] ?? [];
        $itemsCount        = (int) ($slip['items_count'] ?? 1);
        $itemsTitle        = $slip['items_title'] ?? 'পণ্য ও বই';
        $isGift            = !empty($slip['is_gift']);
        $giftRecipient     = $slip['gift_recipient'] ?? null;
        $giftPhone         = $slip['gift_phone'] ?? null;
        $courierName       = $slip['courier_name'] ?? null;
        $trackingCode      = $slip['tracking_code'] ?? null;
        $backUrl           = $slip['back_url'] ?? route('admin.accounting.index');
        $invoiceUrl        = $slip['invoice_url'] ?? null;
        $notes             = $slip['notes'] ?? null;

        $senderName    = $invoiceSettings['sender_name'] ?? (\App\Support\SiteSetting::name() ?: 'আইডিয়া প্রকাশন');
        $senderAddress = $invoiceSettings['sender_address'] ?? (\App\Support\SiteSetting::get('contact_address') ?: 'সেন্ট্রাল রোড, রংপুর ৫৪০০, বাংলাদেশ');
        $senderPhone   = $invoiceSettings['sender_phone'] ?? (\App\Support\SiteSetting::get('contact_phone') ?: '01558712870');
        $senderEmail   = $invoiceSettings['sender_email'] ?? 'ideapbd@gmail.com';
        $senderWebsite = $invoiceSettings['sender_website'] ?? 'www.ideaabd.com';

        $logoUrl = null;
        $invLogo = $invoiceSettings['invoice_logo'] ?? null;
        if ($invLogo && file_exists(public_path('images/settings/' . $invLogo))) {
            $logoUrl = asset('images/settings/' . $invLogo);
        } elseif ($invLogo && file_exists(public_path($invLogo))) {
            $logoUrl = asset($invLogo);
        } elseif (class_exists(\App\Support\SiteSetting::class) && \App\Support\SiteSetting::logoUrl()) {
            $logoUrl = \App\Support\SiteSetting::logoUrl();
        } elseif (file_exists(public_path('images/logo.png'))) {
            $logoUrl = asset('images/logo.png');
        }

        $trackUrl = $trackingCode ? url('/track-order?order_no=' . urlencode($trackingCode)) : url('/track-order?order_no=' . urlencode($docNo));
        $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=100x100&margin=0&data=' . urlencode($trackUrl);
    @endphp

    <title>স্লিপ #{{ $docNo }} — {{ $customerName }}</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.maateen.me/kalpurush/font.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        @font-face {
            font-family: 'Kalpurush';
            src: url('{{ asset("fonts/kalpurush/kalpurush.woff2") }}') format('woff2'),
                 url('{{ asset("fonts/kalpurush/kalpurush.ttf") }}') format('truetype');
            font-weight: normal;
            font-style: normal;
            font-display: swap;
        }

        *, *::before, *::after {
            box-sizing: border-box;
        }
        body, table, input, button, select, textarea {
            font-family: 'Kalpurush', 'Hind Siliguri', 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
            background: #f8fafc;
            margin: 0;
            padding: 24px 16px;
            font-size: 13px;
            line-height: 1.5;
            color: #0f172a;
            -webkit-font-smoothing: antialiased;
        }

        /* Top Action Bar (Clean Outline Design - No Heavy Solid Blocks) */
        .slip-actions {
            max-width: 650px;
            margin: 0 auto 18px auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }
        .slip-actions-group {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }
        .btn-clean {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 14px;
            border-radius: 8px;
            font-size: 12.5px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            background: #ffffff;
            color: #334155;
            border: 1.5px solid #cbd5e1;
            transition: all 0.15s ease;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
        }
        .btn-clean:hover {
            background: #f1f5f9;
            color: #0f172a;
            border-color: #94a3b8;
        }
        .btn-clean-primary {
            background: #ffffff;
            color: #0f172a;
            border: 1.5px solid #0f172a;
            font-weight: 700;
        }
        .btn-clean-primary:hover {
            background: #0f172a;
            color: #ffffff;
        }

        /* Format Switcher Pills */
        .format-selector {
            display: inline-flex;
            align-items: center;
            background: #ffffff;
            border: 1.5px solid #cbd5e1;
            border-radius: 8px;
            padding: 3px;
            gap: 2px;
        }
        .format-pill {
            padding: 4px 10px;
            border-radius: 6px;
            border: none;
            background: transparent;
            color: #64748b;
            font-size: 11.5px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .format-pill.active {
            background: #0f172a;
            color: #ffffff;
        }

        /* Main Slip Card (Classic Editorial Design) */
        .slip-card {
            max-width: 650px;
            margin: 0 auto;
            background: #ffffff;
            border: 1.5px solid #0f172a;
            border-radius: 10px;
            padding: 24px 26px;
            box-shadow: 0 4px 16px rgba(15, 23, 42, 0.04);
            page-break-inside: avoid !important;
            break-inside: avoid !important;
            transition: max-width 0.2s ease;
        }

        /* Format Variants */
        .slip-card.format-a6 {
            max-width: 480px;
            padding: 18px 20px;
        }
        .slip-card.format-pos {
            max-width: 380px;
            padding: 14px 16px;
            border-style: dashed;
        }

        /* Header & Logo */
        .slip-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-bottom: 14px;
            margin-bottom: 16px;
            border-bottom: 1.5px solid #0f172a;
            gap: 16px;
        }
        .slip-brand-group {
            display: flex;
            align-items: center;
            gap: 16px;
            flex: 1;
        }
        .slip-logo {
            max-height: 44px;
            max-width: 130px;
            width: auto;
            object-fit: contain;
            flex-shrink: 0;
            display: block;
        }
        .slip-brand-info {
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .slip-brand-title {
            font-size: 17px;
            font-weight: 800;
            color: #0f172a;
            margin: 0 0 2px 0;
            letter-spacing: -0.2px;
            line-height: 1.25;
        }
        .slip-brand-sub {
            font-size: 11.5px;
            color: #64748b;
            margin: 0;
            font-weight: 500;
        }
        .slip-badge-outline {
            display: inline-block;
            background: #ffffff;
            color: #0f172a;
            border: 1.5px solid #0f172a;
            padding: 4px 12px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            white-space: nowrap;
        }

        /* Document Strip (Clean Outline / High Contrast) */
        .slip-strip {
            display: flex;
            align-items: stretch;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 16px;
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }
        .slip-strip-left {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 10px 14px;
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
        }
        .slip-order-num {
            font-family: 'Courier New', Courier, monospace;
            font-size: 15px;
            font-weight: 800;
            letter-spacing: 1.2px;
            color: #0f172a;
            margin-bottom: 3px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .slip-copy-icon {
            font-size: 12px;
            color: #94a3b8;
            cursor: pointer;
            transition: color 0.15s ease;
        }
        .slip-copy-icon:hover {
            color: #0f172a;
        }
        .slip-order-date {
            font-size: 11.5px;
            color: #64748b;
        }

        /* Clean COD & Payment Status Box (No Solid Black Block) */
        .slip-strip-right {
            width: 210px;
            flex-shrink: 0;
            background: #ffffff;
            border: 1.5px solid #0f172a;
            border-radius: 8px;
            padding: 10px 14px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            text-align: center;
        }
        .slip-strip-right.is-paid {
            border-color: #16a34a;
            background: #f0fdf4;
        }
        .slip-cod-label {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #64748b;
            margin-bottom: 2px;
        }
        .slip-strip-right.is-paid .slip-cod-label {
            color: #15803d;
        }
        .slip-cod-val {
            font-size: 18px;
            font-weight: 800;
            line-height: 1.2;
            color: #0f172a;
            font-family: 'Courier New', Courier, monospace;
        }
        .slip-strip-right.is-paid .slip-cod-val {
            color: #15803d;
            font-family: inherit;
            font-size: 15px;
        }

        /* 2-Column Sender & Recipient */
        .slip-grid {
            display: grid;
            grid-template-columns: 1fr 1.35fr;
            gap: 14px;
            margin-bottom: 16px;
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }
        .slip-box {
            border-radius: 8px;
            padding: 14px 16px;
            display: flex;
            flex-direction: column;
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }
        .slip-box-sender {
            background: #f8fafc;
            border: 1px solid #cbd5e1;
        }
        .slip-box-recipient {
            background: #ffffff;
            border: 1.5px solid #0f172a;
        }
        .slip-box-header {
            font-size: 10.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            padding-bottom: 6px;
            margin-bottom: 10px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .slip-box-sender .slip-box-header {
            color: #64748b;
        }
        .slip-box-recipient .slip-box-header {
            color: #0f172a;
            border-bottom-color: #cbd5e1;
        }

        /* Sender Details */
        .sender-name {
            font-size: 13.5px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 4px;
        }
        .sender-address {
            font-size: 11.5px;
            color: #475569;
            line-height: 1.5;
            margin-bottom: 6px;
        }
        .sender-phone {
            font-size: 11.5px;
            font-weight: 600;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 6px;
            margin-top: auto;
        }

        /* Recipient Details */
        .recipient-name {
            font-size: 16px;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.35;
            margin-bottom: 4px;
            word-break: break-word;
        }
        .recipient-phone-wrap {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 14.5px;
            font-weight: 800;
            color: #0f172a;
            font-family: 'Courier New', Courier, monospace;
            margin-bottom: 8px;
        }
        .recipient-address {
            font-size: 12.5px;
            color: #1e293b;
            line-height: 1.5;
            margin-bottom: 10px;
            word-break: break-word;
        }
        .recipient-pills {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: auto;
        }
        .recipient-pill {
            font-size: 11px;
            font-weight: 600;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            padding: 2px 7px;
            border-radius: 4px;
            color: #334155;
        }
        .recipient-pill-dark {
            border: 1.5px solid #0f172a;
            color: #0f172a;
            background: #ffffff;
            font-weight: 700;
        }

        /* Item & Parcel Content Box */
        .slip-item-box {
            padding: 12px 16px;
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            margin-bottom: 14px;
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }
        .slip-item-grid {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
        }
        .slip-item-title {
            font-size: 12.5px;
            color: #0f172a;
            line-height: 1.45;
            flex: 1;
        }
        .slip-item-title strong {
            font-weight: 700;
            color: #0f172a;
        }
        .slip-item-qty {
            font-size: 12px;
            color: #334155;
            white-space: nowrap;
            background: #ffffff;
            padding: 3px 8px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-weight: 600;
        }
        .slip-gift-notice {
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px dashed #cbd5e1;
            font-size: 11.5px;
            color: #92400e;
            display: flex;
            align-items: center;
            gap: 6px;
            font-weight: 600;
        }

        /* Multi-item table if list exists */
        .slip-items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            margin-top: 6px;
        }
        .slip-items-table th {
            text-align: left;
            padding: 4px 6px;
            border-bottom: 1px solid #cbd5e1;
            color: #64748b;
            font-size: 10.5px;
            text-transform: uppercase;
        }
        .slip-items-table td {
            padding: 5px 6px;
            border-bottom: 1px solid #e2e8f0;
            color: #0f172a;
        }

        /* Courier & Tracking Footer Strip */
        .slip-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 10px 14px;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }
        .slip-courier-info {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            font-size: 11.5px;
            color: #475569;
        }
        .slip-courier-info strong {
            color: #0f172a;
        }
        .slip-qr-box {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-shrink: 0;
        }
        .slip-qr-img {
            width: 40px;
            height: 40px;
            display: block;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 2px;
            background: #ffffff;
        }
        .slip-qr-text {
            font-size: 9.5px;
            line-height: 1.25;
            color: #64748b;
            text-align: right;
        }

        /* Toast Feedback */
        #copyToast {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #0f172a;
            color: #ffffff;
            padding: 8px 14px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.2s ease;
            z-index: 9999;
        }
        #copyToast.show {
            opacity: 1;
        }

        /* 
         * ROCK SOLID PRINT MEDIA STYLES 
         * No page breaks, no split elements, no disappearing texts
         */
        @media print {
            html, body {
                background: #ffffff !important;
                padding: 0 !important;
                margin: 0 !important;
                color: #000000 !important;
                width: 100% !important;
                height: auto !important;
            }
            .slip-actions, #copyToast {
                display: none !important;
            }
            .slip-card {
                max-width: 100% !important;
                width: 100% !important;
                box-shadow: none !important;
                border: 1.5pt solid #000000 !important;
                border-radius: 4px !important;
                padding: 5mm 6mm !important;
                margin: 0 auto !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
            .slip-strip,
            .slip-grid,
            .slip-box,
            .slip-item-box,
            .slip-footer {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
            .slip-header {
                border-bottom: 1.5pt solid #000000 !important;
            }
            .slip-badge-outline,
            .slip-box-recipient,
            .recipient-pill-dark,
            .slip-strip-right {
                border: 1.5pt solid #000000 !important;
                color: #000000 !important;
                background: #ffffff !important;
            }
            .slip-box-sender,
            .slip-item-box,
            .slip-strip-left,
            .slip-footer {
                border: 1pt solid #000000 !important;
                background: #ffffff !important;
            }
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            @page {
                size: auto;
                margin: 6mm;
            }
        }

        /* Mobile Responsive */
        @media (max-width: 580px) {
            body {
                padding: 12px 8px;
            }
            .slip-card {
                padding: 16px 14px;
            }
            .slip-grid {
                grid-template-columns: 1fr;
                gap: 12px;
            }
            .slip-strip {
                flex-direction: column;
            }
            .slip-strip-right {
                width: 100%;
            }
            .slip-footer {
                flex-direction: column;
                align-items: flex-start;
            }
            .slip-qr-box {
                width: 100%;
                justify-content: space-between;
                padding-top: 8px;
                border-top: 1px dashed #cbd5e1;
            }
        }
    </style>
</head>
<body>

    <!-- Top Action Bar (Clean Outline - No Heavy Solid Color Buttons) -->
    <div class="slip-actions">
        <div class="slip-actions-group">
            <a href="{{ $backUrl }}" class="btn-clean" title="পূর্ববর্তী তালিকায় ফিরুন">
                <i class="fa-solid fa-arrow-left"></i> ফিরে যান
            </a>
            @if($invoiceUrl)
                <a href="{{ $invoiceUrl }}" class="btn-clean" title="ইনভয়েস বা মেমো দেখুন">
                    <i class="fa-solid fa-file-invoice"></i> ইনভয়েস
                </a>
            @endif
        </div>

        <!-- Dynamic Format Selector & Print Trigger -->
        <div class="slip-actions-group">
            <div class="format-selector" title="প্রিন্ট ফরম্যাট নির্বাচন করুন">
                <button type="button" class="format-pill active" onclick="switchFormat('standard', this)">স্ট্যান্ডার্ড (A4)</button>
                <button type="button" class="format-pill" onclick="switchFormat('a6', this)">লেবেল (A6)</button>
                <button type="button" class="format-pill" onclick="switchFormat('pos', this)">পজ (80mm)</button>
            </div>

            <button type="button" onclick="window.print()" class="btn-clean btn-clean-primary" title="প্রিন্ট করুন (Ctrl + P)">
                <i class="fa-solid fa-print"></i> প্রিন্ট স্লিপ
            </button>
        </div>
    </div>

    <!-- Main Slip Container -->
    <div class="slip-card" id="slipCard">
        
        <!-- Header & Logo -->
        <div class="slip-header">
            <div class="slip-brand-group">
                <img src="{{ $logoUrl }}" alt="{{ $senderName }}" class="slip-logo" onerror="this.src='/images/logo.png'; this.onerror=null;">
                <div class="slip-brand-info">
                    <h1 class="slip-brand-title">{{ $senderName }}</h1>
                    <p class="slip-brand-sub">{{ $senderWebsite }} • {{ $senderPhone }}</p>
                </div>
            </div>
            <div>
                <span class="slip-badge-outline">{{ $slipBadge }}</span>
            </div>
        </div>

        <!-- Document & COD / Payment Strip -->
        <div class="slip-strip">
            <div class="slip-strip-left">
                <div class="slip-order-num">
                    <span>#{{ $docNo }}</span>
                    <i class="fa-regular fa-copy slip-copy-icon" onclick="copyText('{{ $docNo }}', 'ডকুমেন্ট নম্বর')" title="কপি করুন"></i>
                </div>
                <div class="slip-order-date">
                    <i class="fa-regular fa-calendar-check me-1"></i> তারিখ: {{ $docDate }}
                </div>
            </div>
            
            <div class="slip-strip-right {{ $paymentStatus === 'paid' ? 'is-paid' : '' }}">
                @if($paymentStatus === 'paid')
                    <div class="slip-cod-label">পেমেন্ট স্ট্যাটাস</div>
                    <div class="slip-cod-val">
                        <i class="fa-solid fa-circle-check me-1"></i> পেইড (PAID)
                    </div>
                @else
                    <div class="slip-cod-label">ক্যাশ অন ডেলিভারি (COD)</div>
                    <div class="slip-cod-val">
                        ৳ {{ number_format($dueAmount > 0.001 ? $dueAmount : $totalAmount) }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Sender & Recipient Box -->
        <div class="slip-grid">
            
            <!-- Sender (FROM) -->
            <div class="slip-box slip-box-sender">
                <div class="slip-box-header">
                    <span><i class="fa-solid fa-paper-plane me-1"></i> প্রেরক / FROM</span>
                </div>
                <div class="sender-name">{{ $senderName }}</div>
                <div class="sender-address">{{ $senderAddress }}</div>
                <div class="sender-phone">
                    <i class="fa-solid fa-phone small text-muted"></i> {{ $senderPhone }}
                </div>
            </div>

            <!-- Recipient (TO / DELIVERY) -->
            <div class="slip-box slip-box-recipient">
                <div class="slip-box-header">
                    <span><i class="fa-solid fa-user-check me-1"></i> প্রাপক / TO</span>
                    <span style="font-size: 10px; font-weight: 700; color: #64748b;">ডেলিভারি ঠিকানা</span>
                </div>
                
                <div class="recipient-name">{{ $customerName }}</div>
                
                @if($customerPhone)
                    <div class="recipient-phone-wrap">
                        <i class="fa-solid fa-phone" style="font-size: 12px;"></i>
                        <span>{{ $customerPhone }}</span>
                        <i class="fa-regular fa-copy slip-copy-icon" onclick="copyText('{{ $customerPhone }}', 'ফোন নম্বর')" title="কপি করুন"></i>
                    </div>
                @endif

                @if($customerOrg)
                    <div style="font-size: 11.5px; color: #475569; margin-bottom: 4px; font-weight: 600;">
                        <i class="fa-regular fa-building me-1"></i> {{ $customerOrg }}
                    </div>
                @endif
                
                <div class="recipient-address">
                    {{ $customerAddress }}
                </div>

                <div class="recipient-pills">
                    @if($customerThana)
                        <span class="recipient-pill">থানা: {{ $customerThana }}</span>
                    @endif
                    @if($customerPostCode)
                        <span class="recipient-pill">পোস্ট কোড: {{ $customerPostCode }}</span>
                    @endif
                    @if($customerDistrict)
                        <span class="recipient-pill recipient-pill-dark">জেলা: {{ $customerDistrict }}</span>
                    @endif
                </div>
            </div>

        </div>

        <!-- Book & Parcel Item Box -->
        <div class="slip-item-box">
            @if(!empty($itemsList) && count($itemsList) > 0)
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <strong style="font-size: 11px; text-transform: uppercase; color: #475569;">আইটেমের বিবরণ ({{ count($itemsList) }} টি)</strong>
                    <span class="slip-item-qty">মোট: {{ $itemsCount }} কপি/একক</span>
                </div>
                <table class="slip-items-table">
                    <thead>
                        <tr>
                            <th>বিবরণ</th>
                            <th style="width: 50px; text-align: center;">পরিমাণ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($itemsList as $it)
                            @php
                                $name = $it['name'] ?? $it['title'] ?? 'বই / পণ্য';
                                $qty = $it['qty'] ?? $it['quantity'] ?? 1;
                            @endphp
                            <tr>
                                <td>{{ $name }}</td>
                                <td style="text-align: center; font-weight: 600;">{{ $qty }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="slip-item-grid">
                    <div class="slip-item-title">
                        <strong>আইটেম / বই:</strong> {{ $itemsTitle }}
                    </div>
                    <div class="slip-item-qty">
                        <strong>পরিমাণ:</strong> {{ $itemsCount }} কপি
                    </div>
                </div>
            @endif

            @if($isGift)
                <div class="slip-gift-notice">
                    <i class="fa-solid fa-gift"></i> গিফট পার্সেল (প্রাপক: {{ $giftRecipient ?? $customerName }} {{ $giftPhone ? ' | ' . $giftPhone : '' }})
                </div>
            @endif

            @if($notes)
                <div style="margin-top: 6px; font-size: 11px; color: #64748b; font-style: italic;">
                    নোট: {{ $notes }}
                </div>
            @endif
        </div>

        <!-- Courier & Verification Strip -->
        <div class="slip-footer">
            <div class="slip-courier-info">
                <span>কুরিয়ার: <strong>{{ $courierName ?: 'সাধারণ কুরিয়ার / ডেলিভারি' }}</strong></span>
                @if($trackingCode)
                    <span>ট্র্যাকিং নং: 
                        <strong style="font-family: monospace; font-size: 12.5px;">{{ $trackingCode }}</strong>
                        <i class="fa-regular fa-copy slip-copy-icon" onclick="copyText('{{ $trackingCode }}', 'ট্র্যাকিং নম্বর')" title="কপি করুন"></i>
                    </span>
                @endif
                <span>সহায়তা: <strong>{{ $senderPhone }}</strong></span>
            </div>

            <div class="slip-qr-box">
                <div class="slip-qr-text">
                    স্ক্যান করে<br>ট্র্যাক করুন
                </div>
                <img src="{{ $qrUrl }}" alt="QR" class="slip-qr-img">
            </div>
        </div>

    </div>

    <!-- Toast Notification for Quick Copy -->
    <div id="copyToast">কপি হয়েছে!</div>

    <!-- Dynamic JavaScript Helper -->
    <script>
        // Format Switcher Live Preview
        function switchFormat(format, btn) {
            const card = document.getElementById('slipCard');
            document.querySelectorAll('.format-pill').forEach(el => el.classList.remove('active'));
            if (btn) btn.classList.add('active');

            card.classList.remove('format-a6', 'format-pos');
            if (format === 'a6') {
                card.classList.add('format-a6');
            } else if (format === 'pos') {
                card.classList.add('format-pos');
            }
            try {
                localStorage.setItem('idea_slip_format_pref', format);
            } catch(e) {}
        }

        // Restore Format Preference
        document.addEventListener('DOMContentLoaded', function() {
            try {
                const saved = localStorage.getItem('idea_slip_format_pref');
                if (saved && (saved === 'a6' || saved === 'pos')) {
                    const btn = document.querySelector(`.format-pill[onclick*="${saved}"]`);
                    switchFormat(saved, btn);
                }
            } catch(e) {}

            // Auto-print support if URL has ?print=1
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('print') === '1') {
                window.print();
            }
        });

        // Quick Copy to Clipboard with Toast
        function copyText(text, label) {
            if (!text) return;
            navigator.clipboard.writeText(text).then(() => {
                showToast((label || 'টেক্সট') + ' কপি হয়েছে!');
            }).catch(() => {
                const temp = document.createElement('input');
                temp.value = text;
                document.body.appendChild(temp);
                temp.select();
                document.execCommand('copy');
                document.body.removeChild(temp);
                showToast((label || 'টেক্সট') + ' কপি হয়েছে!');
            });
        }

        function showToast(msg) {
            const toast = document.getElementById('copyToast');
            if (!toast) return;
            toast.textContent = msg;
            toast.classList.add('show');
            setTimeout(() => {
                toast.classList.remove('show');
            }, 1800);
        }
    </script>

</body>
</html>
