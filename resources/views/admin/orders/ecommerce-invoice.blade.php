<!DOCTYPE html>
<html lang="en" data-theme="navy">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Invoice #{{ $order->order_number ?? $order->id }} — {{ \App\Support\SiteSetting::name() ?? 'Idea Publication' }}</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- QRCode.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

    @php
        $siteLogo    = \App\Support\SiteSetting::logoUrl() ?: asset('images/logo.png');
        $siteName    = \App\Support\SiteSetting::name() ?: 'Idea Publication';
        $siteAddress = \App\Support\SiteSetting::get('contact_address') ?: 'Central Road, Rangpur 5400, Bangladesh';
        $sitePhone   = \App\Support\SiteSetting::get('contact_phone') ?: '01558712870';
        $siteEmail   = \App\Support\SiteSetting::get('contact_email') ?: 'ideapbd@gmail.com';
        $siteWebsite = 'www.ideaabd.com';

        // Calculation
        $itemUnit = $order->unit_price > 0 ? $order->unit_price : ($order->book->discount_price ?? $order->book->price ?? 0);
        $itemQty = $order->quantity ?? 1;
        $itemSubtotal = $itemUnit * $itemQty;
        $shippingCost = $order->shipping_cost ?? 0;
        $giftFee = $order->is_gift ? ($order->gift_wrap_fee > 0 ? $order->gift_wrap_fee : 20) : 0;
        $discountAmount = $order->discount_amount ?? 0;
        $grandTotal = $order->total_amount > 0 ? $order->total_amount : ($itemSubtotal + $shippingCost + $giftFee - $discountAmount);

        // Convert number to English words helper
        function numberToWordsEn($num) {
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

        $amountInWordsEn = numberToWordsEn($grandTotal);

        // Payment method in English
        $paymentMethodEn = match(strtolower($order->payment_method ?? 'cod')) {
            'cod' => 'Cash On Delivery (COD)',
            'bkash' => 'bKash Online Payment',
            'nagad' => 'Nagad Online Payment',
            'rocket' => 'Rocket Mobile Banking',
            'card' => 'Debit / Credit Card',
            default => strtoupper($order->payment_method ?? 'COD'),
        };

        // Payment status in English
        $paymentStatusEn = match(strtolower($order->payment_status ?? 'pending')) {
            'paid' => 'PAID',
            'pending' => 'DUE ON DELIVERY',
            'unpaid' => 'UNPAID',
            'partial' => 'PARTIAL PAID',
            default => strtoupper($order->payment_status ?? 'PENDING'),
        };

        // Clean phone for WhatsApp
        $cleanPhone = preg_replace('/[^0-9]/', '', $order->customer_phone);
        if (str_starts_with($cleanPhone, '01')) {
            $cleanPhone = '88' . $cleanPhone;
        }
        $whatsappMessage = "Hello {$order->customer_name}!\nThank you for ordering with Idea Publication. Your invoice #{$order->order_number} details:\n\nTotal Payable: BDT " . number_format($grandTotal, 2) . "\nPayment Status: {$paymentStatusEn}\n\nTrack order / View invoice online:\n" . url('/track-order?order_no=' . ($order->order_number ?? $order->id)) . "\n\nWarm regards,\nIdea Publication";
        $whatsappUrl = "https://api.whatsapp.com/send?phone={$cleanPhone}&text=" . urlencode($whatsappMessage);
    @endphp

    <style>
        :root {
            --theme-primary: #1e3a8a;
            --theme-gradient: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%);
            --theme-accent: #0284c7;
            --theme-border: #cbd5e1;
            --theme-badge-bg: rgba(255, 255, 255, 0.18);
            --theme-badge-border: rgba(255, 255, 255, 0.35);
        }

        [data-theme="emerald"] {
            --theme-primary: #065f46;
            --theme-gradient: linear-gradient(135deg, #064e3b 0%, #059669 100%);
            --theme-accent: #10b981;
            --theme-border: #a7f3d0;
        }

        [data-theme="noir"] {
            --theme-primary: #18181b;
            --theme-gradient: linear-gradient(135deg, #09090b 0%, #27272a 100%);
            --theme-accent: #71717a;
            --theme-border: #e4e4e7;
        }

        [data-theme="purple"] {
            --theme-primary: #581c87;
            --theme-gradient: linear-gradient(135deg, #3b0764 0%, #7e22ce 100%);
            --theme-accent: #a855f7;
            --theme-border: #e9d5ff;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: #f1f5f9;
            color: #1e293b;
            font-size: 13.5px;
            line-height: 1.5;
            margin: 0;
            padding: 20px 0 40px 0;
        }

        /* Top Action Bar Styling */
        .action-dock {
            max-width: 860px;
            margin: 0 auto 16px auto;
            background: #ffffff;
            border-radius: 16px;
            padding: 12px 18px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
            border: 1px solid #e2e8f0;
        }

        .btn-action-pill {
            border-radius: 30px;
            font-weight: 600;
            font-size: 13px;
            padding: 6px 14px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s ease;
        }

        .btn-action-pill:hover {
            transform: translateY(-1px);
        }

        /* Main Invoice Card */
        .invoice-card {
            max-width: 860px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 35px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            border: 1px solid var(--theme-border);
            position: relative;
        }

        /* Watermark */
        .watermark-overlay {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 5.5rem;
            font-weight: 900;
            color: rgba(15, 23, 42, 0.035);
            text-transform: uppercase;
            letter-spacing: 12px;
            pointer-events: none;
            z-index: 1;
            user-select: none;
            white-space: nowrap;
        }

        .invoice-header-strip {
            background: var(--theme-gradient);
            color: #ffffff;
            padding: 24px 36px;
            position: relative;
            z-index: 2;
            transition: background 0.3s ease;
        }

        .invoice-inner {
            padding: 28px 36px 36px 36px;
            position: relative;
            z-index: 2;
        }

        .brand-logo-img {
            max-height: 56px;
            width: auto;
            max-width: 190px;
            object-fit: contain;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.12));
            background: #ffffff;
            padding: 4px 8px;
            border-radius: 8px;
        }

        .invoice-badge-pill {
            background: var(--theme-badge-bg);
            border: 1px solid var(--theme-badge-border);
            color: #ffffff;
            padding: 6px 16px;
            border-radius: 30px;
            font-weight: 700;
            font-size: 13px;
            letter-spacing: 0.5px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-transform: uppercase;
        }

        .barcode-pill {
            font-family: 'Courier New', Courier, monospace;
            letter-spacing: 3px;
            font-size: 14px;
            font-weight: 700;
            background: rgba(255, 255, 255, 0.95);
            color: #0f172a;
            padding: 4px 12px;
            border-radius: 6px;
            display: inline-block;
            margin-top: 6px;
        }

        .meta-strip {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 14px 18px;
            margin-bottom: 22px;
        }

        .party-card {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 16px 18px;
            background: #ffffff;
            height: 100%;
            position: relative;
        }

        .party-card.sender {
            border-top: 4px solid var(--theme-primary);
            background: #f8fafc;
        }

        .party-card.recipient {
            border-top: 4px solid #059669;
            background: #f0fdf4;
        }

        .party-tag {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            padding-bottom: 6px;
            margin-bottom: 10px;
            border-bottom: 1.5px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .table-memo {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: separate;
            border-spacing: 0;
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            overflow: hidden;
        }

        .table-memo thead th {
            background: var(--theme-primary);
            color: #ffffff;
            font-weight: 600;
            font-size: 12.5px;
            padding: 11px 14px;
            border-right: 1px solid rgba(255, 255, 255, 0.1);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .table-memo thead th:last-child {
            border-right: none;
        }

        .table-memo tbody td {
            padding: 12px 14px;
            border-bottom: 1px solid #e2e8f0;
            border-right: 1px solid #e2e8f0;
            vertical-align: middle;
            background: #ffffff;
        }

        .table-memo tbody td:last-child {
            border-right: none;
        }

        .table-memo tbody tr:last-child td {
            border-bottom: none;
        }

        .summary-box {
            background: #f8fafc;
            border: 1.5px solid #cbd5e1;
            border-radius: 12px;
            padding: 16px 20px;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            font-size: 13.5px;
            color: #475569;
        }

        .summary-item.total-due {
            border-top: 2px dashed #94a3b8;
            margin-top: 8px;
            padding-top: 10px;
            font-size: 17px;
            font-weight: 700;
            color: var(--theme-primary);
        }

        .paid-stamp {
            display: inline-block;
            border: 2px solid #059669;
            color: #059669;
            font-weight: 800;
            text-transform: uppercase;
            padding: 4px 14px;
            border-radius: 6px;
            letter-spacing: 1px;
            font-size: 12px;
            transform: rotate(-3deg);
        }

        .due-stamp {
            display: inline-block;
            border: 2px solid #d97706;
            color: #d97706;
            font-weight: 800;
            text-transform: uppercase;
            padding: 4px 14px;
            border-radius: 6px;
            letter-spacing: 1px;
            font-size: 12px;
            transform: rotate(-3deg);
        }

        .in-words-box {
            background: #f1f5f9;
            border: 1px dashed #cbd5e1;
            padding: 8px 14px;
            border-radius: 8px;
            font-size: 12.5px;
            color: #334155;
            margin-top: 12px;
        }

        .status-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 11.5px;
            text-transform: uppercase;
        }
        .badge-pending { background: #fef3c7; color: #92400e; }
        .badge-processing { background: #e0f2fe; color: #0369a1; }
        .badge-confirmed { background: #e0e7ff; color: #3730a3; }
        .badge-shipped { background: #ede9fe; color: #5b21b6; }
        .badge-delivered { background: #dcfce7; color: #166534; }
        .badge-cancelled { background: #fee2e2; color: #991b1b; }

        .theme-dot {
            width: 18px;
            height: 18px;
            border-radius: 50%;
            display: inline-block;
            cursor: pointer;
            border: 2px solid #ffffff;
            box-shadow: 0 0 0 1px #cbd5e1;
            transition: transform 0.2s ease;
        }
        .theme-dot:hover {
            transform: scale(1.2);
        }

        /* Print Specific Optimizations */
        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
                margin: 0 !important;
                color: #000000 !important;
                font-size: 12px !important;
            }
            .d-print-none, .action-dock, .modal {
                display: none !important;
            }
            .invoice-card {
                max-width: 100% !important;
                box-shadow: none !important;
                border: none !important;
                border-radius: 0 !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .invoice-header-strip {
                background: #0f172a !important;
                color: #ffffff !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                padding: 14px 20px !important;
            }
            .brand-logo-img {
                background: #ffffff !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .invoice-inner {
                padding: 16px 20px !important;
            }
            .party-card {
                background: #ffffff !important;
                border: 1px solid #64748b !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .table-memo thead th {
                background: #0f172a !important;
                color: #ffffff !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                padding: 8px 10px !important;
            }
            .table-memo tbody td {
                padding: 8px 10px !important;
                border-color: #cbd5e1 !important;
            }
            .summary-box {
                background: #f8fafc !important;
                border: 1px solid #64748b !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .watermark-overlay {
                display: block !important;
                color: rgba(0,0,0,0.04) !important;
            }
            @page {
                size: A4 portrait;
                margin: 6mm 8mm;
            }
        }
    </style>
</head>
<body>

    <!-- Dynamic Action Toolbar (Hidden in Print) -->
    <div class="action-dock d-print-none">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2.5">
            
            <!-- Left Controls: Back, Status, Quick Edit -->
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <a href="{{ route('admin.ecommerce-orders') }}" class="btn btn-outline-secondary btn-sm btn-action-pill">
                    <i class="fa-solid fa-arrow-left"></i> All Orders
                </a>
                <button type="button" class="btn btn-warning btn-sm btn-action-pill text-dark shadow-xs" data-bs-toggle="modal" data-bs-target="#modalQuickEdit">
                    <i class="fa-solid fa-pen-to-square"></i> Quick Edit Info
                </button>
                <a href="{{ route('admin.ecommerce-orders.slip', $order) }}" target="_blank" class="btn btn-outline-primary btn-sm btn-action-pill">
                    <i class="fa-solid fa-tag"></i> Parcel Slip
                </a>
            </div>

            <!-- Middle Controls: WhatsApp, Share Link -->
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <a href="{{ $whatsappUrl }}" target="_blank" class="btn btn-success btn-sm btn-action-pill text-white shadow-xs">
                    <i class="fa-brands fa-whatsapp"></i> WhatsApp Memo
                </a>
                <button type="button" class="btn btn-outline-info btn-sm btn-action-pill text-dark" onclick="copyInvoiceLink()">
                    <i class="fa-solid fa-link" id="copyLinkIcon"></i> Copy Link
                </button>
            </div>

            <!-- Right Controls: Themes, Print -->
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <!-- Theme Palette -->
                <div class="d-flex align-items-center gap-1.5 px-2 py-1 bg-light rounded-pill border">
                    <span class="theme-dot" style="background:#1e3a8a;" onclick="setInvoiceTheme('navy')" title="Classic Navy"></span>
                    <span class="theme-dot" style="background:#059669;" onclick="setInvoiceTheme('emerald')" title="Emerald Green"></span>
                    <span class="theme-dot" style="background:#18181b;" onclick="setInvoiceTheme('noir')" title="Corporate Noir"></span>
                    <span class="theme-dot" style="background:#7e22ce;" onclick="setInvoiceTheme('purple')" title="Royal Purple"></span>
                </div>

                <!-- Print Button -->
                <button onclick="window.print()" class="btn btn-primary btn-sm btn-action-pill px-3.5 fw-bold shadow-xs">
                    <i class="fa-solid fa-print"></i> Print Invoice
                </button>
            </div>

        </div>
    </div>

    <!-- Main Printable Invoice Card -->
    <div class="invoice-card" id="invoiceCard">
        
        <!-- Watermark -->
        <div class="watermark-overlay" id="watermarkText">
            {{ $order->payment_status === 'paid' ? 'PAID' : 'OFFICIAL' }}
        </div>

        <!-- Header Banner & Logo -->
        <div class="invoice-header-strip">
            <div class="row align-items-center">
                <div class="col-7 d-flex align-items-center gap-3">
                    <img src="{{ $siteLogo }}" alt="{{ $siteName }}" class="brand-logo-img" onerror="this.src='/images/logo.png'; this.onerror=null;">
                    <div>
                        <h4 class="fw-bold mb-0 text-white" style="letter-spacing: -0.2px;">{{ $siteName }}</h4>
                        <div class="text-white text-opacity-80 small mt-0.5">
                            <i class="fa-solid fa-globe me-1"></i> {{ $siteWebsite }} | Digital Lighthouse of Knowledge & Creativity
                        </div>
                    </div>
                </div>
                <div class="col-5 text-end">
                    <div class="invoice-badge-pill">
                        <i class="fa-solid fa-file-invoice-dollar text-warning"></i>
                        <span>CASH MEMO & INVOICE</span>
                    </div>
                    <div>
                        <span class="barcode-pill">#{{ $order->order_number ?? $order->id }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="invoice-inner">

            <!-- Meta Quick Strip -->
            <div class="meta-strip">
                <div class="row g-2 align-items-center text-center text-md-start">
                    <div class="col-6 col-md-3">
                        <span class="text-muted small d-block">Invoice / Memo No:</span>
                        <strong class="text-dark font-monospace fs-6">#{{ $order->order_number ?? $order->id }}</strong>
                    </div>
                    <div class="col-6 col-md-3">
                        <span class="text-muted small d-block">Order Date & Time:</span>
                        <strong class="text-dark">{{ $order->created_at->format('d M, Y — h:i A') }}</strong>
                    </div>
                    <div class="col-6 col-md-3">
                        <span class="text-muted small d-block">Payment Method:</span>
                        <strong class="text-dark">{{ $paymentMethodEn }}</strong>
                        @if($order->transaction_id)
                            <div class="small font-monospace text-primary" style="font-size: 11px;">TrxID: <strong>{{ $order->transaction_id }}</strong></div>
                        @endif
                    </div>
                    <div class="col-6 col-md-3 text-md-end">
                        <span class="text-muted small d-block">Payment Status:</span>
                        @if($order->payment_status === 'paid')
                            <span class="badge bg-success px-2.5 py-1 fw-bold">
                                <i class="fa-solid fa-circle-check me-1"></i> PAID
                            </span>
                        @else
                            <span class="badge bg-warning text-dark px-2.5 py-1 fw-bold">
                                <i class="fa-solid fa-clock me-1"></i> CASH ON DELIVERY
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Sender & Recipient Two-Column Layout -->
            <div class="row g-3 mb-4">
                
                <!-- Left: Sender (From) -->
                <div class="col-md-6">
                    <div class="party-card sender">
                        <div class="party-tag" style="color: var(--theme-primary);">
                            <span><i class="fa-solid fa-paper-plane me-1"></i> FROM / SENDER</span>
                            <span class="badge text-white" style="background: var(--theme-primary);">HEAD OFFICE</span>
                        </div>
                        <h6 class="fw-bold text-dark mb-1">{{ $siteName }}</h6>
                        <div class="text-muted small mb-1">
                            <i class="fa-solid fa-location-dot text-danger me-1"></i>
                            {{ $siteAddress }}
                        </div>
                        <div class="text-dark small mb-1">
                            <i class="fa-solid fa-phone text-success me-1"></i>
                            <strong>Hotline:</strong> {{ $sitePhone }}
                        </div>
                        @if(!empty($siteEmail))
                        <div class="text-muted small">
                            <i class="fa-solid fa-envelope text-primary me-1"></i>
                            {{ $siteEmail }}
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Right: Recipient (To / Customer) -->
                <div class="col-md-6">
                    <div class="party-card recipient">
                        <div class="party-tag text-success">
                            <span><i class="fa-solid fa-user-check me-1"></i> TO / RECIPIENT</span>
                            <span class="badge bg-success text-white">CUSTOMER</span>
                        </div>
                        <h6 class="fw-bold text-dark mb-1">{{ $order->customer_name }}</h6>
                        <div class="text-dark mb-1">
                            <i class="fa-solid fa-phone text-success me-1"></i>
                            <strong>Mobile:</strong> <span class="fw-bold fs-6 font-monospace">{{ $order->customer_phone }}</span>
                        </div>
                        <div class="text-dark small mb-1">
                            <i class="fa-solid fa-map-location-dot text-primary me-1"></i>
                            <strong>Delivery Address:</strong>
                            @if($order->house_road)
                                {{ $order->house_road }},
                            @endif
                            {{ $order->customer_address }}
                        </div>
                        <div class="text-muted small">
                            @if($order->thana)
                                <span class="me-2"><strong>Thana / Area:</strong> {{ $order->thana }}</span>
                            @endif
                            @if($order->post_code)
                                <span class="me-2"><strong>Postal Code:</strong> {{ $order->post_code }}</span>
                            @endif
                            <span><strong>District:</strong> {{ $order->district ?? $order->district_label }}</span>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Gift Notice Ribbon (If applicable) -->
            @if($order->is_gift)
            <div class="p-3 bg-amber-50 rounded-3 border border-warning mb-4" style="background-color: #fffbeb; border-color: #fde68a;">
                <div class="d-flex align-items-start gap-2.5">
                    <i class="fa-solid fa-gift text-warning fs-4 mt-0.5"></i>
                    <div>
                        <strong class="d-block text-amber-900 fs-6" style="color: #92400e;">GIFT PACKAGE RECIPIENT DETAILS:</strong>
                        <span class="me-3"><strong>Name:</strong> {{ $order->gift_recipient_name }}</span>
                        <span class="me-3"><strong>Mobile:</strong> {{ $order->gift_recipient_phone }}</span>
                        @if($order->gift_recipient_address)
                            <span class="d-block mt-1"><strong>Gift Address:</strong> {{ $order->gift_recipient_address }}</span>
                        @endif
                        @if($order->gift_message)
                            <div class="mt-2 small p-2 bg-white rounded border border-warning text-dark fst-italic">
                                <strong>Gift Note:</strong> "{{ $order->gift_message }}"
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            <!-- Items Table -->
            <table class="table-memo">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 6%;">SL #</th>
                        <th style="width: 52%;">Book / Item Description</th>
                        <th class="text-center" style="width: 14%;">Unit Price</th>
                        <th class="text-center" style="width: 12%;">Qty</th>
                        <th class="text-end" style="width: 16%;">Total (BDT)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="text-center fw-bold">1</td>
                        <td>
                            @if($order->book)
                                <div class="d-flex align-items-center gap-2.5">
                                    @if($order->book->cover_image)
                                        <img src="{{ asset('storage/' . $order->book->cover_image) }}" alt="{{ $order->book->title }}" style="width: 38px; height: 50px; object-fit: cover; border-radius: 4px;" class="border d-none d-sm-inline-block">
                                    @endif
                                    <div>
                                        <div class="fw-bold text-dark fs-6">{{ $order->book->title }}</div>
                                        @if($order->book->authors && $order->book->authors->count())
                                            <div class="text-muted small">Author: <strong>{{ $order->book->authors->pluck('name')->implode(', ') }}</strong></div>
                                        @endif
                                        @if($order->book->isbn)
                                            <div class="text-muted small">ISBN: {{ $order->book->isbn }}</div>
                                        @endif
                                    </div>
                                </div>
                            @else
                                <div class="fw-bold text-dark fs-6">Book Order #{{ $order->order_number ?? $order->id }}</div>
                            @endif
                        </td>
                        <td class="text-center fw-semibold font-monospace">
                            BDT {{ number_format($itemUnit, 2) }}
                        </td>
                        <td class="text-center fw-bold">
                            {{ $itemQty }}
                        </td>
                        <td class="text-end fw-bold text-dark font-monospace">
                            BDT {{ number_format($itemSubtotal, 2) }}
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Summary & Terms Section -->
            <div class="row g-4 align-items-start mb-4">
                
                <!-- Left Column: Delivery, Courier, QR Code & Terms -->
                <div class="col-md-6">
                    
                    @if($order->courier_name || $order->tracking_code)
                    <div class="p-3 bg-light rounded-3 border mb-3">
                        <div class="fw-bold text-dark mb-1 small d-flex align-items-center gap-1.5">
                            <i class="fa-solid fa-truck-fast text-primary"></i> Courier & Shipping Info:
                        </div>
                        <div class="small"><strong>Courier Partner:</strong> <span id="displayCourier">{{ $order->courier_name ?? 'Assigned Courier' }}</span></div>
                        @if($order->tracking_code)
                            <div class="small mt-1"><strong>Tracking ID:</strong> <span class="font-monospace text-primary fw-bold" id="displayTracking">{{ $order->tracking_code }}</span></div>
                        @endif
                    </div>
                    @endif

                    <div class="p-3 bg-light rounded-3 border mb-3" style="font-size: 12px;">
                        <div class="fw-bold text-dark mb-1 d-flex align-items-center gap-1.5">
                            <i class="fa-solid fa-shield-halved text-primary"></i> Terms & Policy:
                        </div>
                        <p class="text-muted mb-0">
                            Please inspect the parcel upon delivery. For any issues or replacement requests, kindly contact our customer care helpline immediately.
                        </p>
                        @if($order->admin_notes)
                        <div class="mt-2 text-dark fw-semibold pt-1 border-top" id="displayAdminNotes">
                            <strong>Admin Note:</strong> {{ $order->admin_notes }}
                        </div>
                        @endif
                    </div>

                    <!-- Live Dynamic QR Code Box -->
                    <div class="d-flex align-items-center gap-3 p-2.5 bg-white rounded-3 border">
                        <div id="invoiceQrCode" style="width: 58px; height: 58px;"></div>
                        <div class="small">
                            <strong class="d-block text-dark">Digital Order Verification</strong>
                            <span class="text-muted" style="font-size: 11px;">Scan with smartphone camera to track order status online.</span>
                        </div>
                    </div>

                </div>

                <!-- Right Column: Financial Breakdown -->
                <div class="col-md-6">
                    <div class="summary-box">
                        <div class="summary-item">
                            <span>Items Subtotal:</span>
                            <span class="fw-bold text-dark font-monospace">BDT {{ number_format($itemSubtotal, 2) }}</span>
                        </div>
                        <div class="summary-item">
                            <span>Shipping / Delivery Fee:</span>
                            <span class="fw-semibold text-dark font-monospace">
                                @if($shippingCost > 0)
                                    BDT {{ number_format($shippingCost, 2) }}
                                @else
                                    <span class="text-success fw-bold">FREE DELIVERY</span>
                                @endif
                            </span>
                        </div>
                        @if($order->is_gift)
                        <div class="summary-item">
                            <span>Gift Wrapping Fee:</span>
                            <span class="fw-semibold text-dark font-monospace">BDT {{ number_format($giftFee, 2) }}</span>
                        </div>
                        @endif
                        @if($discountAmount > 0)
                        <div class="summary-item text-danger">
                            <span>Special Discount:</span>
                            <span class="font-monospace">- BDT {{ number_format($discountAmount, 2) }}</span>
                        </div>
                        @endif
                        <div class="summary-item total-due">
                            <span>Grand Total (Payable):</span>
                            <span class="font-monospace">BDT {{ number_format($grandTotal, 2) }}</span>
                        </div>

                        <!-- Amount in Words -->
                        <div class="in-words-box">
                            <strong>In Words:</strong> {{ $amountInWordsEn }}
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                            <span class="small text-muted">Payment Status:</span>
                            @if($order->payment_status === 'paid')
                                <span class="paid-stamp">
                                    <i class="fa-solid fa-check-double me-1"></i> PAID
                                </span>
                            @else
                                <span class="due-stamp">
                                    <i class="fa-solid fa-hand-holding-dollar me-1"></i> DUE (CASH ON DELIVERY)
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

            </div>

            <!-- Footer Signature & Thank You Greeting -->
            <div class="pt-3 border-top mt-4">
                <div class="row align-items-end">
                    <div class="col-7">
                        <p class="small text-muted mb-0 fw-semibold">
                            <i class="fa-solid fa-heart text-danger me-1"></i>
                            May the joy of reading books spread to everyone. Thank you for choosing Idea Publication!
                        </p>
                        <div class="text-muted" style="font-size: 11px;">
                            Print Date: {{ date('d M, Y — h:i A') }} | Computer Generated Official Digital Invoice
                        </div>
                    </div>
                    <div class="col-5 text-end">
                        <div class="d-inline-block text-center" style="min-width: 150px;">
                            <div style="height: 40px;"></div>
                            <div class="border-top border-dark pt-1 small fw-bold text-dark">
                                Authorized Signature
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Quick In-Place Edit Modal -->
    <div class="modal fade d-print-none" id="modalQuickEdit" tabindex="-1" aria-labelledby="modalQuickEditLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header bg-light border-bottom p-3">
                    <h6 class="modal-title fw-bold text-dark d-flex align-items-center gap-2" id="modalQuickEditLabel">
                        <i class="fa-solid fa-pen-to-square text-primary"></i> Edit Order & Invoice Details
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formQuickEdit" onsubmit="handleQuickEditSubmit(event)">
                    <div class="modal-body p-4 space-y-3">
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-muted">Payment Status <span class="text-danger">*</span></label>
                            <select name="payment_status" id="editPaymentStatus" class="form-select">
                                <option value="pending" @selected($order->payment_status === 'pending')>Pending (COD)</option>
                                <option value="paid" @selected($order->payment_status === 'paid')>Paid</option>
                                <option value="unpaid" @selected($order->payment_status === 'unpaid')>Unpaid</option>
                                <option value="partial" @selected($order->payment_status === 'partial')>Partial Paid</option>
                            </select>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label fw-semibold small text-muted">Courier Partner</label>
                                <input type="text" name="courier_name" id="editCourierName" class="form-control" value="{{ $order->courier_name }}" placeholder="e.g. Sundarban, Pathao, Steadfast">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold small text-muted">Tracking Code</label>
                                <input type="text" name="tracking_code" id="editTrackingCode" class="form-control font-monospace" value="{{ $order->tracking_code }}" placeholder="TRACK123456">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-muted">Transaction ID (TrxID)</label>
                            <input type="text" name="transaction_id" id="editTrxId" class="form-control font-monospace" value="{{ $order->transaction_id }}" placeholder="e.g. 8N7K5A...">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-muted">Admin Note (Shown on Invoice)</label>
                            <textarea name="admin_notes" id="editAdminNotes" class="form-control" rows="2" placeholder="Special delivery notes...">{{ $order->admin_notes }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-top p-3 d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4 fw-semibold" id="btnSaveQuickEdit">
                            <i class="fa-solid fa-floppy-disk me-1"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Generate Live Dynamic QR Code
        document.addEventListener('DOMContentLoaded', function() {
            const qrContainer = document.getElementById('invoiceQrCode');
            if (qrContainer) {
                const trackUrl = '{{ url("/track-order?order_no=" . ($order->order_number ?? $order->id)) }}';
                new QRCode(qrContainer, {
                    text: trackUrl,
                    width: 58,
                    height: 58,
                    colorDark : "#0f172a",
                    colorLight : "#ffffff",
                    correctLevel : QRCode.CorrectLevel.M
                });
            }
        });

        // Dynamic Theme Switcher
        function setInvoiceTheme(themeName) {
            document.documentElement.setAttribute('data-theme', themeName);
            localStorage.setItem('invoice_theme_pref', themeName);
        }

        // Restore saved theme on load
        const savedTheme = localStorage.getItem('invoice_theme_pref');
        if (savedTheme) {
            document.documentElement.setAttribute('data-theme', savedTheme);
        }

        // Copy Invoice Track Link
        function copyInvoiceLink() {
            const link = '{{ url("/track-order?order_no=" . ($order->order_number ?? $order->id)) }}';
            navigator.clipboard.writeText(link).then(() => {
                const icon = document.getElementById('copyLinkIcon');
                icon.className = 'fa-solid fa-check text-success';
                setTimeout(() => {
                    icon.className = 'fa-solid fa-link';
                }, 2000);
            });
        }

        // In-Place Quick Edit AJAX Handler
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
                    const modal = bootstrap.Modal.getInstance(document.getElementById('modalQuickEdit'));
                    if (modal) modal.hide();
                    window.location.reload();
                } else {
                    alert(data.message || 'Failed to save changes.');
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-floppy-disk me-1"></i> Save Changes';
                alert('Server error: ' + err.message);
            });
        }
    </script>

</body>
</html>
