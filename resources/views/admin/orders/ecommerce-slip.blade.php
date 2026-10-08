<!DOCTYPE html>
<html lang="en">
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
                'type_badge'       => 'PARCEL',
                'doc_no'           => $order->order_number ?? $order->id,
                'date'             => !empty($order->created_at) ? \Carbon\Carbon::parse($order->created_at)->format('d M, Y') : date('d M, Y'),
                'customer_name'    => $order->customer_name ?: 'Customer',
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
                'items_title'      => $order->book->title ?? 'Book Order',
                'is_gift'          => (bool) $order->is_gift,
                'gift_recipient'   => $order->gift_recipient_name,
                'gift_phone'       => $order->gift_recipient_phone,
                'courier_name'     => $order->courier_name,
                'tracking_code'    => $order->tracking_code,
                'back_url'         => route('admin.ecommerce-orders'),
                'invoice_url'      => route('admin.ecommerce-orders.invoice', $order->id),
            ];
        }

        $currentFormat     = request('format');
        if (!in_array($currentFormat, ['half', 'pos', 'full'])) {
            $currentFormat = 'half';
        }

        $slipType          = $slip['type'] ?? 'order';
        $slipBadge         = 'PARCEL';
        $docNo             = $slip['doc_no'] ?? 'N/A';
        $docDate           = $slip['date'] ?? date('d M, Y');
        $customerName      = $slip['customer_name'] ?? 'Customer';
        $customerPhone     = $slip['customer_phone'] ?? null;
        $customerAddress   = $slip['customer_address'] ?? 'No Address';
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
        $itemsTitle        = $slip['items_title'] ?? 'Books / Items';
        $isGift            = !empty($slip['is_gift']);
        $giftRecipient     = $slip['gift_recipient'] ?? null;
        $giftPhone         = $slip['gift_phone'] ?? null;
        $courierName       = $slip['courier_name'] ?? null;
        $trackingCode      = $slip['tracking_code'] ?? null;
        $backUrl           = $slip['back_url'] ?? route('admin.accounting.index');
        $invoiceUrl        = $slip['invoice_url'] ?? null;
        $notes             = $slip['notes'] ?? null;

        $senderName    = $invoiceSettings['sender_name'] ?? (\App\Support\SiteSetting::name() ?: 'IDEA PROKASHON');
        $senderAddress = $invoiceSettings['sender_address'] ?? (\App\Support\SiteSetting::get('contact_address') ?: 'Central Road, Rangpur 5400, Bangladesh');
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

    <title>Slip #{{ $docNo }} — {{ $customerName }}</title>
    
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
            max-width: 480px;
            margin: 0 auto 14px auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            flex-wrap: wrap;
            transition: max-width 0.2s ease;
        }
        .slip-actions.format-full {
            max-width: 650px;
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
            padding: 7px 12px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            background: #ffffff;
            color: #334155;
            border: 1px solid #cbd5e1;
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
            border: 1px solid #0f172a;
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
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 3px;
            gap: 2px;
        }
        .format-pill {
            padding: 4px 8px;
            border-radius: 6px;
            border: none;
            background: transparent;
            color: #64748b;
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .format-pill.active {
            background: #0f172a;
            color: #ffffff;
        }

        /* Main Slip Card (+100px width: 480px, no outer side border) */
        .slip-card {
            max-width: 480px;
            margin: 0 auto;
            background: #ffffff;
            border: none;
            border-radius: 8px;
            padding: 16px 18px;
            box-shadow: 0 4px 16px rgba(15, 23, 42, 0.05);
            page-break-inside: avoid !important;
            break-inside: avoid !important;
            transition: max-width 0.2s ease;
        }

        /* Format Variants - Dynamic sizing (Half, POS, Full) */
        .slip-card.format-half {
            max-width: 480px;
            padding: 16px 18px;
        }
        .slip-actions.format-half {
            max-width: 480px;
        }

        .slip-card.format-full {
            max-width: 720px;
            padding: 22px 24px;
        }
        .slip-actions.format-full {
            max-width: 720px;
        }
        .slip-card.format-full .slip-grid {
            grid-template-columns: 1fr 1.35fr;
            gap: 16px;
        }

        .slip-card.format-pos {
            max-width: 320px;
            padding: 10px 12px;
        }
        .slip-actions.format-pos {
            max-width: 320px;
        }
        .slip-card.format-pos .slip-header {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            gap: 6px;
        }
        .slip-card.format-pos .slip-header-brand {
            flex-direction: column;
            align-items: center;
            text-align: center;
            gap: 4px;
        }
        .slip-card.format-pos .slip-header-center {
            order: -1;
        }
        .slip-card.format-pos .slip-header-meta {
            align-items: center;
            text-align: center;
        }
        .slip-card.format-pos .slip-meta-line {
            justify-content: center;
        }
        .slip-card.format-pos .slip-grid {
            grid-template-columns: 1fr !important;
            gap: 10px !important;
        }
        .slip-card.format-pos .slip-footer {
            flex-direction: column;
            align-items: flex-start;
            gap: 8px;
        }
        .slip-card.format-pos .slip-qr-box {
            width: 100%;
            justify-content: space-between;
            border-top: 0.5px dashed #cbd5e1;
            padding-top: 6px;
        }

        /* Header & Logo (3 Columns) */
        .slip-header {
            display: grid;
            grid-template-columns: 1.35fr auto 1.35fr;
            align-items: start;
            justify-content: space-between;
            padding-bottom: 12px;
            margin-bottom: 14px;
            border-bottom: 1px solid #0f172a;
            gap: 10px;
        }
        .slip-header-brand {
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }
        .slip-logo {
            max-height: 38px;
            max-width: 100px;
            width: auto;
            object-fit: contain;
            flex-shrink: 0;
            display: block;
        }
        .slip-brand-info {
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
        }
        .slip-brand-title {
            font-size: 14.5px;
            font-weight: 800;
            color: #0f172a;
            margin: 0 0 2px 0;
            letter-spacing: -0.2px;
            line-height: 1.25;
        }
        .slip-brand-sub {
            font-size: 11px;
            color: #64748b;
            margin: 0;
            font-weight: 500;
            line-height: 1.35;
        }
        .slip-header-center {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding-top: 1px;
        }
        .slip-badge-outline {
            display: inline-block;
            background: #ffffff;
            color: #0f172a;
            border: 1px solid #0f172a;
            padding: 3px 10px;
            border-radius: 5px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            white-space: nowrap;
        }
        .slip-header-meta {
            text-align: right;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            justify-content: flex-start;
            gap: 3px;
        }
        .slip-meta-line {
            font-size: 11.5px; /* Uniform font size across all lines */
            line-height: 1.4;
            color: #334155;
            display: inline-flex;
            align-items: center;
            justify-content: flex-end;
            gap: 4px;
            white-space: nowrap;
        }
        .slip-meta-line strong {
            font-size: 11.5px; /* Same size as text */
            font-weight: 700;
            color: #0f172a;
        }

        /* 2-Column Sender & Recipient */
        .slip-grid {
            display: grid;
            grid-template-columns: 1fr 1.35fr;
            gap: 12px;
            margin-bottom: 12px;
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }
        .slip-box {
            border-radius: 8px;
            padding: 10px 12px;
            display: flex;
            flex-direction: column;
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }
        .slip-box-sender {
            background: #f8fafc;
            border: 0.5px solid #cbd5e1;
        }
        .slip-box-recipient {
            background: #ffffff;
            border: 1px solid #0f172a;
        }
        .slip-box-header {
            font-size: 12.5px; /* +2px */
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            padding-bottom: 5px;
            margin-bottom: 8px;
            border-bottom: 0.5px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .slip-box-sender .slip-box-header {
            color: #64748b;
        }
        .slip-box-recipient .slip-box-header {
            color: #0f172a;
            border-bottom: 0.5px solid #cbd5e1;
        }

        /* Sender Details (+2px font size) */
        .sender-name {
            font-size: 15.5px; /* +2px */
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 4px;
            line-height: 1.35;
        }
        .sender-address {
            font-size: 13.5px; /* +2px */
            color: #475569;
            line-height: 1.45;
            margin-bottom: 6px;
        }
        .sender-phone {
            font-size: 13.5px; /* +2px */
            font-weight: 600;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 6px;
            margin-top: auto;
        }

        /* Recipient Details (+2px font size) */
        .recipient-name {
            font-size: 18px; /* +2px */
            font-weight: 800;
            color: #0f172a;
            line-height: 1.3;
            margin-bottom: 4px;
            word-break: break-word;
        }
        .recipient-phone-wrap {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 16.5px; /* +2px */
            font-weight: 800;
            color: #0f172a;
            font-family: 'Courier New', Courier, monospace;
            margin-bottom: 6px;
        }
        .recipient-address {
            font-size: 14.5px; /* +2px */
            color: #1e293b;
            line-height: 1.45;
            margin-bottom: 8px;
            word-break: break-word;
        }
        .recipient-pills {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            margin-top: auto;
        }
        .recipient-pill {
            font-size: 13px; /* +2px */
            font-weight: 600;
            background: #ffffff;
            border: 0.5px solid #cbd5e1;
            padding: 2px 7px;
            border-radius: 4px;
            color: #334155;
        }
        .recipient-pill-dark {
            border: 1px solid #0f172a;
            color: #0f172a;
            background: #ffffff;
            font-weight: 700;
        }

        /* Item & Parcel Content Box */
        .slip-item-box {
            padding: 10px 14px;
            background: #f8fafc;
            border: 0.5px solid #cbd5e1;
            border-radius: 8px;
            margin-bottom: 12px;
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }
        .slip-item-grid {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }
        .slip-item-title {
            font-size: 12px;
            color: #0f172a;
            line-height: 1.4;
            flex: 1;
        }
        .slip-item-title strong {
            font-weight: 700;
            color: #0f172a;
        }
        .slip-item-qty {
            font-size: 11px;
            color: #334155;
            white-space: nowrap;
            background: #ffffff;
            padding: 2px 7px;
            border: 0.5px solid #cbd5e1;
            border-radius: 5px;
            font-weight: 600;
        }
        .slip-gift-notice {
            margin-top: 6px;
            padding-top: 6px;
            border-top: 0.5px dashed #cbd5e1;
            font-size: 11px;
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
            font-size: 11.5px;
            margin-top: 4px;
        }
        .slip-items-table th {
            text-align: left;
            padding: 4px 6px;
            border-bottom: 0.5px solid #cbd5e1;
            color: #64748b;
            font-size: 10px;
            text-transform: uppercase;
        }
        .slip-items-table td {
            padding: 4px 6px;
            border-bottom: 0.5px solid #e2e8f0;
            color: #0f172a;
        }

        /* Courier & Tracking Footer Strip */
        .slip-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 8px 12px;
            background: #ffffff;
            border: 0.5px solid #cbd5e1;
            border-radius: 8px;
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }
        .slip-courier-info {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            font-size: 10.5px;
            color: #475569;
        }
        .slip-courier-info strong {
            color: #0f172a;
        }
        .slip-qr-box {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-shrink: 0;
        }
        .slip-qr-img {
            width: 36px;
            height: 36px;
            display: block;
            border: 0.5px solid #cbd5e1;
            border-radius: 4px;
            padding: 2px;
            background: #ffffff;
        }
        .slip-qr-text {
            font-size: 9px;
            line-height: 1.2;
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
         * Dynamic formats (Half, Full, POS) - Never break!
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
                max-width: 480px !important;
                width: 480px !important;
                box-shadow: none !important;
                border: none !important;
                border-radius: 0 !important;
                padding: 2mm 0 !important;
                margin: 0 auto !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
            .slip-card.format-half {
                max-width: 480px !important;
                width: 480px !important;
                padding: 2mm 0 !important;
            }
            .slip-card.format-full {
                max-width: 100% !important;
                width: 100% !important;
                padding: 4mm 0 !important;
            }
            .slip-card.format-full .slip-grid {
                grid-template-columns: 1fr 1.35fr !important;
                gap: 16px !important;
            }
            .slip-card.format-pos {
                max-width: 76mm !important;
                width: 76mm !important;
                padding: 2mm 0 !important;
            }
            .slip-header {
                display: grid !important;
                grid-template-columns: 1.35fr auto 1.35fr !important;
                align-items: start !important;
                justify-content: space-between !important;
                border-bottom: 1pt solid #000000 !important;
                gap: 8px !important;
            }
            .slip-card.format-pos .slip-header {
                display: flex !important;
                flex-direction: column !important;
                align-items: center !important;
                text-align: center !important;
                gap: 6px !important;
            }
            .slip-card.format-pos .slip-header-brand {
                flex-direction: column !important;
                align-items: center !important;
                text-align: center !important;
                gap: 4px !important;
            }
            .slip-card.format-pos .slip-header-center {
                order: -1 !important;
            }
            .slip-card.format-pos .slip-header-meta {
                align-items: center !important;
                text-align: center !important;
            }
            .slip-card.format-pos .slip-meta-line {
                justify-content: center !important;
            }
            .slip-card.format-pos .slip-grid {
                grid-template-columns: 1fr !important;
                gap: 8px !important;
            }
            .slip-card.format-pos .slip-footer {
                flex-direction: column !important;
                align-items: flex-start !important;
                gap: 6px !important;
            }
            .slip-card.format-pos .slip-qr-box {
                width: 100% !important;
                justify-content: space-between !important;
                border-top: 0.5pt dashed #000000 !important;
                padding-top: 4px !important;
            }
            .slip-grid,
            .slip-box,
            .slip-item-box,
            .slip-footer {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
            .slip-badge-outline,
            .slip-box-recipient,
            .recipient-pill-dark {
                border: 1pt solid #000000 !important;
                color: #000000 !important;
                background: #ffffff !important;
            }
            .slip-box-sender,
            .slip-item-box,
            .slip-footer {
                border: 0.5pt solid #000000 !important;
                background: #ffffff !important;
            }
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            @page {
                size: auto;
                margin: 5mm;
            }
        }

        /* Mobile & Narrow Responsive */
        @media (max-width: 480px) {
            body {
                padding: 12px 8px;
            }
            .slip-card {
                padding: 12px 10px;
            }
            .slip-header {
                grid-template-columns: 1.2fr auto 1.2fr;
                gap: 6px;
            }
            .slip-logo {
                max-width: 70px;
            }
            .slip-meta-line {
                font-size: 10.5px;
            }
            .slip-meta-line strong {
                font-size: 10.5px;
            }
            .slip-grid {
                grid-template-columns: 1fr;
                gap: 10px;
            }
            .slip-footer {
                flex-direction: column;
                align-items: flex-start;
            }
            .slip-qr-box {
                width: 100%;
                justify-content: space-between;
                padding-top: 8px;
                border-top: 0.5px dashed #cbd5e1;
            }
        }
    </style>
</head>
<body>

    <!-- Top Action Bar (Clean Outline - No Heavy Solid Color Buttons) -->
    <div class="slip-actions format-{{ $currentFormat }}">
        <div class="slip-actions-group">
            <a href="{{ $backUrl }}" class="btn-clean" title="Go back">
                <i class="fa-solid fa-arrow-left"></i> Back
            </a>
            @if($invoiceUrl)
                <a href="{{ $invoiceUrl }}" class="btn-clean" title="View invoice">
                    <i class="fa-solid fa-file-invoice"></i> Invoice
                </a>
            @endif
        </div>

        <!-- Dynamic Format Selector & Print Trigger -->
        <div class="slip-actions-group">
            <div class="format-selector" title="Select print format">
                <button type="button" class="format-pill {{ $currentFormat === 'half' ? 'active' : '' }}" onclick="switchFormat('half', this)">হাফ সাইজ (ডিফল্ট)</button>
                <button type="button" class="format-pill {{ $currentFormat === 'pos' ? 'active' : '' }}" onclick="switchFormat('pos', this)">পজ (80mm)</button>
                <button type="button" class="format-pill {{ $currentFormat === 'full' ? 'active' : '' }}" onclick="switchFormat('full', this)">ফুল পেইজ (A4)</button>
            </div>

            <button type="button" onclick="window.print()" class="btn-clean btn-clean-primary" title="Print slip (Ctrl + P)">
                <i class="fa-solid fa-print"></i> Print Slip
            </button>
        </div>
    </div>

    <!-- Main Slip Container -->
    <div class="slip-card format-{{ $currentFormat }}" id="slipCard">
        
        <!-- Header & Logo (3 Columns) -->
        <div class="slip-header">
            <!-- Column 1: Brand Info -->
            <div class="slip-header-brand">
                <img src="{{ $logoUrl }}" alt="{{ $senderName }}" class="slip-logo" onerror="this.src='/images/logo.png'; this.onerror=null;">
                <div class="slip-brand-info">
                    <h1 class="slip-brand-title">{{ $senderName }}</h1>
                    <div class="slip-brand-sub">{{ $senderWebsite }}</div>
                    <div class="slip-brand-sub">• {{ $senderPhone }}</div>
                </div>
            </div>

            <!-- Column 2: Center (PARCEL) -->
            <div class="slip-header-center">
                <span class="slip-badge-outline">PARCEL</span>
            </div>

            <!-- Column 3: Order Metadata (Uniform Font Size) -->
            <div class="slip-header-meta">
                <div class="slip-meta-line">
                    <strong>#{{ $docNo }}</strong>
                    <i class="fa-regular fa-copy slip-copy-icon" onclick="copyText('{{ $docNo }}', 'Order No')" title="Copy"></i>
                </div>
                <div class="slip-meta-line">Date: {{ $docDate }}</div>
                <div class="slip-meta-line">
                    Payment Status: 
                    @if($paymentStatus === 'paid')
                        <strong style="color: #16a34a;">PAID</strong>
                    @else
                        <strong style="color: #dc2626;">COD (৳ {{ number_format($dueAmount > 0.001 ? $dueAmount : $totalAmount) }})</strong>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sender & Recipient Box -->
        <div class="slip-grid">
            
            <!-- Sender (FROM) -->
            <div class="slip-box slip-box-sender">
                <div class="slip-box-header">
                    <span><i class="fa-solid fa-paper-plane me-1"></i> FROM</span>
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
                    <span><i class="fa-solid fa-user-check me-1"></i> TO</span>
                    <span style="font-size: 12px; font-weight: 700; color: #64748b;">Delivery Address</span>
                </div>
                
                <div class="recipient-name">{{ $customerName }}</div>
                
                @if($customerPhone)
                    <div class="recipient-phone-wrap">
                        <i class="fa-solid fa-phone" style="font-size: 14px;"></i>
                        <span>{{ $customerPhone }}</span>
                        <i class="fa-regular fa-copy slip-copy-icon" onclick="copyText('{{ $customerPhone }}', 'Phone')" title="Copy"></i>
                    </div>
                @endif

                @if($customerOrg)
                    <div style="font-size: 13.5px; color: #475569; margin-bottom: 4px; font-weight: 600;">
                        <i class="fa-regular fa-building me-1"></i> {{ $customerOrg }}
                    </div>
                @endif
                
                <div class="recipient-address">
                    {{ $customerAddress }}
                </div>

                <div class="recipient-pills">
                    @if($customerThana)
                        <span class="recipient-pill">Thana: {{ $customerThana }}</span>
                    @endif
                    @if($customerPostCode)
                        <span class="recipient-pill">Post Code: {{ $customerPostCode }}</span>
                    @endif
                    @if($customerDistrict)
                        <span class="recipient-pill recipient-pill-dark">District: {{ $customerDistrict }}</span>
                    @endif
                </div>
            </div>

        </div>

        <!-- Book & Parcel Item Box -->
        <div class="slip-item-box">
            @if(!empty($itemsList) && count($itemsList) > 0)
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <strong style="font-size: 11px; text-transform: uppercase; color: #475569;">Item Details ({{ count($itemsList) }})</strong>
                    <span class="slip-item-qty">Total: {{ $itemsCount }} Qty</span>
                </div>
                <table class="slip-items-table">
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th style="width: 60px; text-align: center;">Qty</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($itemsList as $it)
                            @php
                                $name = $it['name'] ?? $it['title'] ?? 'Book / Product';
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
                        <strong>Item / Book:</strong> {{ $itemsTitle }}
                    </div>
                    <div class="slip-item-qty">
                        <strong>Qty:</strong> {{ $itemsCount }}
                    </div>
                </div>
            @endif

            @if($isGift)
                <div class="slip-gift-notice">
                    <i class="fa-solid fa-gift"></i> Gift Parcel (To: {{ $giftRecipient ?? $customerName }} {{ $giftPhone ? ' | ' . $giftPhone : '' }})
                </div>
            @endif

            @if($notes)
                <div style="margin-top: 6px; font-size: 11px; color: #64748b; font-style: italic;">
                    Note: {{ $notes }}
                </div>
            @endif
        </div>

        <!-- Courier & Verification Strip -->
        <div class="slip-footer">
            <div class="slip-courier-info">
                @if($courierName && !str_contains($courierName, 'সাধারণ'))
                    <span>Courier: <strong>{{ $courierName }}</strong></span>
                @endif
                @if($trackingCode)
                    <span>Tracking No: 
                        <strong style="font-family: monospace; font-size: 12.5px;">{{ $trackingCode }}</strong>
                        <i class="fa-regular fa-copy slip-copy-icon" onclick="copyText('{{ $trackingCode }}', 'Tracking Code')" title="Copy"></i>
                    </span>
                @endif
                <span>Helpline: <strong>{{ $senderPhone }}</strong></span>
            </div>

            <div class="slip-qr-box">
                <div class="slip-qr-text">
                    Scan to<br>Track Order
                </div>
                <img src="{{ $qrUrl }}" alt="QR" class="slip-qr-img">
            </div>
        </div>

    </div>

    <!-- Toast Notification for Quick Copy -->
    <div id="copyToast">Copied!</div>

    <!-- Dynamic JavaScript Helper -->
    <script>
        // Dynamic Format Switcher Live Preview
        function switchFormat(format, btn) {
            const card = document.getElementById('slipCard');
            const actions = document.querySelector('.slip-actions');
            document.querySelectorAll('.format-pill').forEach(el => el.classList.remove('active'));
            if (btn) btn.classList.add('active');

            if (card) {
                card.classList.remove('format-half', 'format-full', 'format-pos');
                card.classList.add('format-' + format);
            }
            if (actions) {
                actions.classList.remove('format-half', 'format-full', 'format-pos');
                actions.classList.add('format-' + format);
            }

            try {
                localStorage.setItem('idea_slip_format_pref', format);
                const url = new URL(window.location);
                url.searchParams.set('format', format);
                window.history.replaceState({}, '', url);
            } catch(e) {}
        }

        // Restore Format Preference
        document.addEventListener('DOMContentLoaded', function() {
            try {
                const urlParams = new URLSearchParams(window.location.search);
                const urlFormat = urlParams.get('format');
                const savedFormat = localStorage.getItem('idea_slip_format_pref');
                const targetFormat = urlFormat || savedFormat || '{{ $currentFormat }}';

                if (targetFormat && ['half', 'pos', 'full'].includes(targetFormat)) {
                    const btn = document.querySelector(`.format-pill[onclick*="'${targetFormat}'"]`);
                    if (btn) switchFormat(targetFormat, btn);
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
                showToast((label || 'Text') + ' copied!');
            }).catch(() => {
                const temp = document.createElement('input');
                temp.value = text;
                document.body.appendChild(temp);
                temp.select();
                document.execCommand('copy');
                document.body.removeChild(temp);
                showToast((label || 'Text') + ' copied!');
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
