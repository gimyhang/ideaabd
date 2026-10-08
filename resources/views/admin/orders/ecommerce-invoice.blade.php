<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Invoice #{{ $order->order_number ?? $order->id }} — {{ \App\Support\SiteSetting::name() ?: 'IDEA PROKASHON' }}</title>
    
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
        $siteName    = 'IDEA PROKASHON';
        $siteAddress = 'CENTRAL ROAD, RANGPUR-5400';
        $sitePhone   = '+8801726976982';
        $siteEmail   = 'ideapbd@gmail.com';
        $siteWebsite = 'www.ideaabd.com';
        
        $invoiceTerms = 'Please check the package upon receipt. In case of any issue or defect, please contact our support helpline immediately.';
        if (!empty($invoiceSettings['invoice_terms']) && !str_contains($invoiceSettings['invoice_terms'], 'পণ্য')) {
            $invoiceTerms = $invoiceSettings['invoice_terms'];
        }
        
        $invoiceFooter = 'Spreading the joy of reading books to everyone. Thank you for choosing IDEA PROKASHON!';
        if (!empty($invoiceSettings['invoice_footer']) && !str_contains($invoiceSettings['invoice_footer'], 'বই')) {
            $invoiceFooter = $invoiceSettings['invoice_footer'];
        }

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
                if ($hundred > 0) $units[$hundred] . ' Hundred';
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
        $whatsappMessage = "Dear {$order->customer_name}!\nYour order has been confirmed at IDEA PROKASHON.\n\nInvoice: #{$order->order_number}\nTotal: Tk " . number_format($grandTotal, 2) . "\nPayment: " . ($order->payment_status === 'paid' ? 'PAID' : 'Cash on Delivery (COD)') . "\n\nOnline Invoice & Tracking:\n{$trackUrl}\n\nThank you,\n{$siteName}";
        $whatsappUrl = "https://api.whatsapp.com/send?phone={$cleanPhone}&text=" . urlencode($whatsappMessage);
    @endphp

    <style>
        /* ══════════════════════════════════════════════════════════════════
           MINIMALIST MONOCHROME COMMERCIAL INVOICE STYLESHEET
           ══════════════════════════════════════════════════════════════════ */
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
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, 'Kalpurush', 'Hind Siliguri', sans-serif !important;
            background-color: #ffffff;
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

        /* Header: 3 Columns (Left: Brand, Middle: Invoice, Right: Order Meta) */
        .inv-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            position: relative;
            padding-bottom: 14px;
            border-bottom: 2px solid #000000;
            margin-bottom: 16px;
        }
        .inv-header-left {
            text-align: left;
        }
        .inv-brand-top {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 5px;
        }
        .inv-logo {
            max-height: 40px;
            max-width: 60px;
            width: auto;
            object-fit: contain;
            display: block;
            flex-shrink: 0;
            filter: grayscale(100%);
        }
        .inv-brand-details {
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .inv-brand-name {
            font-size: 16.5px;
            font-weight: 800;
            color: #000000;
            line-height: 1.2;
            letter-spacing: 0.5px;
            margin: 0 0 2px 0;
            white-space: nowrap;
        }
        .inv-brand-address {
            font-size: 11px;
            font-weight: 600;
            color: #222222;
            line-height: 1.25;
            margin: 0;
            text-transform: uppercase;
            white-space: nowrap;
        }
        .inv-brand-contact-line {
            font-size: 10.5px;
            color: #222222;
            display: flex;
            align-items: center;
            gap: 10px;
            line-height: 1.3;
            flex-wrap: nowrap;
        }
        .inv-brand-contact-line span {
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .inv-brand-contact-line i {
            font-size: 10px;
            color: #000000;
        }

        .inv-header-center {
            position: absolute;
            left: 50%;
            top: 0;
            transform: translateX(-50%);
            text-align: center;
        }
        .inv-doc-shape {
            display: inline-block;
            border: 1.2px solid #000000;
            color: #000000;
            background: #ffffff;
            font-size: 9.5px;
            font-weight: 800;
            padding: 2px 10px;
            letter-spacing: 1px;
            text-transform: uppercase;
            border-radius: 3px;
            line-height: 1.2;
        }

        .inv-header-right {
            text-align: right;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 3px;
            padding-top: 2px;
        }
        .inv-header-right .inv-number {
            font-family: 'Courier New', Courier, monospace;
            font-size: 14.5px;
            font-weight: 800;
            color: #000000;
            line-height: 1.2;
        }
        .inv-header-right .inv-date {
            font-size: 11.5px;
            font-weight: 600;
            color: #222222;
            line-height: 1.2;
        }
        .inv-badge-monochrome {
            display: inline-block;
            border: 1.5px solid #000000;
            color: #000000;
            background: #ffffff;
            font-size: 10.5px;
            font-weight: 800;
            padding: 2px 9px;
            letter-spacing: 0.5px;
            border-radius: 3px;
            line-height: 1.2;
            margin-top: 2px;
        }
        .inv-badge-monochrome.is-paid {
            background: #000000;
            color: #ffffff;
        }

        /* Recipient / Delivery Address Section */
        .inv-customer-box {
            padding: 10px 14px;
            border: 1px solid #000000;
            margin-bottom: 16px;
            font-size: 12px;
            background: #ffffff;
        }
        .inv-customer-heading {
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
            margin-bottom: 3px;
        }
        .inv-customer-addr {
            color: #111111;
            line-height: 1.4;
        }
        .inv-customer-meta {
            margin-top: 5px;
            padding-top: 4px;
            border-top: 1px dashed #cccccc;
            font-size: 11px;
            color: #333333;
        }

        /* Items Table */
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
            font-size: 13.5px;
            font-weight: 800;
            color: #000000;
        }
        .inv-grand-total .inv-totals-val {
            font-size: 14.5px;
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

        /* Print Media */
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
            .inv-header {
                display: flex !important;
                justify-content: space-between !important;
                align-items: flex-start !important;
                position: relative !important;
            }
            .inv-header-center {
                position: absolute !important;
                left: 50% !important;
                top: 0 !important;
                transform: translateX(-50%) !important;
                text-align: center !important;
            }
            .inv-header, .inv-customer-box, .inv-table, .inv-bottom-grid, .inv-footer, tr {
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
            .inv-header-center { position: static; transform: none; text-align: left; }
            .inv-header-right { align-items: flex-start; text-align: left; }
            .inv-bottom-grid { grid-template-columns: 1fr; gap: 12px; }
            .inv-words { text-align: left; }
        }
    </style>
</head>
<body>

    <!-- Top Action Bar (Hidden on Print) -->
    <div class="inv-action-bar d-print-none">
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.ecommerce-orders') }}" class="inv-btn" title="Back to Orders List">
                <i class="fa-solid fa-arrow-left"></i> Orders List
            </a>
            <a href="{{ route('admin.ecommerce-orders.slip', $order) }}" target="_blank" class="inv-btn" title="Parcel Slip & Label">
                <i class="fa-solid fa-tag"></i> Parcel Slip
            </a>
            <button type="button" class="inv-btn" data-bs-toggle="modal" data-bs-target="#modalQuickEdit" title="Quick edit order info">
                <i class="fa-solid fa-pen-to-square"></i> Quick Edit
            </button>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="{{ $whatsappUrl }}" target="_blank" class="inv-btn" title="Send memo to customer via WhatsApp">
                <i class="fa-brands fa-whatsapp"></i> WhatsApp Memo
            </a>
            <button type="button" class="inv-btn" onclick="copyInvoiceLink()" title="Copy tracking link">
                <i class="fa-solid fa-link" id="copyLinkIcon"></i> Copy Link
            </button>
            <button onclick="window.print()" class="inv-btn inv-btn-primary" title="Print invoice">
                <i class="fa-solid fa-print"></i> Print Invoice
            </button>
        </div>
    </div>

    <!-- Printable Invoice Document (Pure Monochrome) -->
    <div class="inv-container">

        <!-- Header: 3-Column Layout -->
        <div class="inv-header">
            <!-- Left Column: Brand Info (Logo level with name/address, contact in single line below) -->
            <div class="inv-header-left">
                <div class="inv-brand-top">
                    @if($siteLogo)
                        <img src="{{ $siteLogo }}" alt="IDEA" class="inv-logo" onerror="this.style.display='none';">
                    @endif
                    <div class="inv-brand-details">
                        <div class="inv-brand-name">IDEA PROKASHON</div>
                        <div class="inv-brand-address">CENTRAL ROAD, RANGPUR-5400</div>
                    </div>
                </div>
                <div class="inv-brand-contact-line">
                    <span><i class="fa-solid fa-phone"></i> +8801726976982</span>
                    <span><i class="fa-solid fa-envelope"></i> ideapbd@gmail.com</span>
                    <span><i class="fa-solid fa-globe"></i> www.ideaabd.com</span>
                </div>
            </div>

            <!-- Middle Column: Invoice Title (Top, 50% smaller, inside a shape) -->
            <div class="inv-header-center">
                <span class="inv-doc-shape">Invoice</span>
            </div>

            <!-- Right Column: Order Meta -->
            <div class="inv-header-right">
                <div class="inv-number">#{{ $order->order_number ?? $order->id }}</div>
                <div class="inv-date">Date: {{ $order->created_at ? $order->created_at->format('d M, Y') : date('d M, Y') }}</div>
                <div>
                    @if($order->payment_status === 'paid')
                        <span class="inv-badge-monochrome is-paid">PAID</span>
                    @else
                        <span class="inv-badge-monochrome">COD</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Recipient & Delivery Address (English) -->
        <div class="inv-customer-box">
            <div class="inv-customer-heading">BILL TO / DELIVERY ADDRESS:</div>
            <div class="inv-customer-name">{{ $order->customer_name }}</div>
            <div class="inv-customer-phone">{{ $order->customer_phone }}</div>
            <div class="inv-customer-addr">
                @if($order->house_road){{ $order->house_road }}, @endif
                {{ $order->customer_address }}
                @if($order->thana) &bull; Thana: {{ $order->thana }}@endif
                @if($order->district || $order->district_label) &bull; District: {{ $order->district ?? $order->district_label }}@endif
                @if($order->post_code) ({{ $order->post_code }})@endif
            </div>

            @if($order->is_gift)
                <div class="inv-customer-meta">
                    <strong>[Gift Order]</strong> Recipient: {{ $order->gift_recipient_name }} ({{ $order->gift_recipient_phone }})
                    @if($order->gift_message) &mdash; "{{ $order->gift_message }}"@endif
                </div>
            @endif

            @if($order->courier_name || $order->tracking_code)
                <div class="inv-customer-meta">
                    @if($order->courier_name)<strong>Courier:</strong> {{ $order->courier_name }}@endif
                    @if($order->tracking_code) &bull; <strong>Tracking No:</strong> {{ $order->tracking_code }}@endif
                </div>
            @endif
        </div>

        <!-- Items Table -->
        <table class="inv-table">
            <thead>
                <tr>
                    <th style="width: 6%; text-align: center;">SL</th>
                    <th style="width: 52%;">Item Description</th>
                    <th style="width: 15%; text-align: right;">Unit Price</th>
                    <th style="width: 10%; text-align: center;">Qty</th>
                    <th style="width: 17%; text-align: right;">Total Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="text-align: center; color: #444444;">1</td>
                    <td>
                        <strong style="color: #000000; font-size: 12.5px;">
                            {{ $order->book ? $order->book->title : ('Book Order #' . ($order->order_number ?? $order->id)) }}
                        </strong>
                        @if($order->book && $order->book->authors && $order->book->authors->count())
                            <div style="font-size: 10.5px; color: #444444;">Author: {{ $order->book->authors->pluck('name')->implode(', ') }}</div>
                        @endif
                        @if($order->book && $order->book->isbn)
                            <div style="font-size: 10px; color: #666666;">ISBN: {{ $order->book->isbn }}</div>
                        @endif
                    </td>
                    <td style="text-align: right; font-family: 'Courier New', Courier, monospace;">
                        Tk {{ number_format($itemUnit, 2) }}
                    </td>
                    <td style="text-align: center; font-weight: 700;">
                        {{ $itemQty }}
                    </td>
                    <td style="text-align: right; font-weight: 700; font-family: 'Courier New', Courier, monospace;">
                        Tk {{ number_format($itemSubtotal, 2) }}
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Summary & Verification Row -->
        <div class="inv-bottom-grid">
            
            <!-- Left: Terms & QR -->
            <div>
                <div class="inv-policy-box">
                    <div class="inv-policy-title">Terms & Customer Support:</div>
                    <div>{{ $invoiceTerms }}</div>
                    @if($order->admin_notes)
                        <div style="margin-top: 3px; padding-top: 3px; border-top: 1px dashed #000000;">
                            <strong>Note:</strong> {{ $order->admin_notes }}
                        </div>
                    @endif
                </div>

                <div class="inv-qr-wrap">
                    <img src="{{ $qrUrl }}" alt="QR" class="inv-qr-img">
                    <div class="inv-qr-info">
                        <strong>Digital Verification & Tracking</strong>
                        Scan QR code with smartphone to verify invoice authenticity and track order online.
                    </div>
                </div>
            </div>

            <!-- Right: Totals -->
            <div>
                <table class="inv-totals-table">
                    <tr>
                        <td class="inv-totals-label">Subtotal:</td>
                        <td class="inv-totals-val">Tk {{ number_format($itemSubtotal, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="inv-totals-label">Delivery Charge:</td>
                        <td class="inv-totals-val">
                            @if($shippingCost > 0)
                                Tk {{ number_format($shippingCost, 2) }}
                            @else
                                <span>Free</span>
                            @endif
                        </td>
                    </tr>
                    @if($order->is_gift && $giftFee > 0)
                    <tr>
                        <td class="inv-totals-label">Gift Wrapping Fee:</td>
                        <td class="inv-totals-val">Tk {{ number_format($giftFee, 2) }}</td>
                    </tr>
                    @endif
                    @if($discountAmount > 0)
                    <tr>
                        <td class="inv-totals-label">Discount:</td>
                        <td class="inv-totals-val">- Tk {{ number_format($discountAmount, 2) }}</td>
                    </tr>
                    @endif
                    <tr class="inv-grand-total">
                        <td>Total Amount:</td>
                        <td class="inv-totals-val">Tk {{ number_format($grandTotal, 2) }}</td>
                    </tr>
                </table>

                <div class="inv-words">
                    <strong>In Words:</strong> {{ $amountInWords }}
                </div>
            </div>

        </div>

        <!-- Footer & Signature -->
        <div class="inv-footer">
            <div>
                <div style="font-weight: 700; color: #000000;">{{ $invoiceFooter }}</div>
                <div style="font-size: 9.5px; color: #555555; margin-top: 1px;">
                    Computer Generated Digital Invoice
                </div>
            </div>

            <div class="inv-sig-box">
                @if($hasSignature)
                    <img src="{{ asset('storage/' . $userSignature) }}" alt="Signature" class="inv-sig-img">
                @else
                    <div style="height: 26px;"></div>
                @endif
                <div class="inv-sig-line">Authorized Signature</div>
            </div>
        </div>

    </div>

    <!-- Quick In-Place Edit Modal (Hidden on Print) -->
    <div class="modal fade d-print-none" id="modalQuickEdit" tabindex="-1" aria-labelledby="modalQuickEditLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-3">
                <div class="modal-header border-bottom p-3">
                    <h6 class="modal-title fw-bold text-dark" id="modalQuickEditLabel">
                        <i class="fa-solid fa-pen-to-square me-1.5"></i> Edit Order Information
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formQuickEdit" onsubmit="handleQuickEditSubmit(event)">
                    <div class="modal-body p-3.5 space-y-3">
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-muted">Payment Status <span class="text-danger">*</span></label>
                            <select name="payment_status" id="editPaymentStatus" class="form-select form-select-sm">
                                <option value="pending" @selected($order->payment_status === 'pending')>Pending (COD)</option>
                                <option value="paid" @selected($order->payment_status === 'paid')>Paid</option>
                                <option value="unpaid" @selected($order->payment_status === 'unpaid')>Unpaid</option>
                                <option value="partial" @selected($order->payment_status === 'partial')>Partial Paid</option>
                            </select>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label fw-semibold small text-muted">Courier Name</label>
                                <input type="text" name="courier_name" id="editCourierName" class="form-control form-control-sm" value="{{ $order->courier_name }}" placeholder="e.g. Steadfast, Sundarban">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold small text-muted">Tracking Code</label>
                                <input type="text" name="tracking_code" id="editTrackingCode" class="form-control form-control-sm" value="{{ $order->tracking_code }}" placeholder="e.g. ST12345678">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-muted">Transaction ID (TrxID)</label>
                            <input type="text" name="transaction_id" id="editTrxId" class="form-control form-control-sm font-monospace" value="{{ $order->transaction_id }}" placeholder="bKash / Nagad TrxID">
                        </div>

                        <div class="mb-0">
                            <label class="form-label fw-semibold small text-muted">Admin Notes / Instructions</label>
                            <textarea name="admin_notes" id="editAdminNotes" rows="2" class="form-control form-control-sm" placeholder="Internal order notes...">{{ $order->admin_notes }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-top p-2.5">
                        <button type="button" class="btn btn-sm btn-outline-secondary px-3" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" id="btnSaveQuickEdit" class="btn btn-sm btn-dark px-3 fw-bold">
                            <i class="fa-solid fa-floppy-disk me-1"></i> Save Changes
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
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';

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
                btn.innerHTML = '<i class="fa-solid fa-floppy-disk me-1"></i> Save Changes';

                if (data.success) {
                    const modalEl = document.getElementById('modalQuickEdit');
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) modal.hide();
                    window.location.reload();
                } else {
                    alert(data.message || 'Failed to save changes.');
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-floppy-disk me-1"></i> Save Changes';
                alert('Server Error: ' + err.message);
            });
        }
    </script>

</body>
</html>
