<!DOCTYPE html>
<html lang="bn" data-theme="navy" data-lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ক্যাশ মেমো / ইনভয়েস #{{ $order->order_number ?? $order->id }} — {{ \App\Support\SiteSetting::name() ?? 'আইডিয়া প্রকাশন' }}</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&family=Inter:wght@400;500;600;700;800&family=Cinzel:wght@600;700&display=swap" rel="stylesheet">
    
    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- QRCode.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

    @php
        $siteLogo    = \App\Support\SiteSetting::logoUrl() ?: asset('images/logo.png');
        $siteName    = \App\Support\SiteSetting::name() ?: ($invoiceSettings['sender_name'] ?? 'আইডিয়া প্রকাশন');
        $siteAddress = \App\Support\SiteSetting::get('contact_address') ?: ($invoiceSettings['sender_address'] ?? 'সেন্ট্রাল রোড, রংপুর ৫৪০০, বাংলাদেশ');
        $sitePhone   = \App\Support\SiteSetting::get('contact_phone') ?: ($invoiceSettings['sender_phone'] ?? '01558712870');
        $siteEmail   = \App\Support\SiteSetting::get('contact_email') ?: ($invoiceSettings['sender_email'] ?? 'ideapbd@gmail.com');
        $siteWebsite = $invoiceSettings['sender_website'] ?? 'www.ideaabd.com';

        // Calculation
        $itemUnit = $order->unit_price > 0 ? $order->unit_price : ($order->book->discount_price ?? $order->book->price ?? 0);
        $itemQty = $order->quantity ?? 1;
        $itemSubtotal = $itemUnit * $itemQty;
        $shippingCost = $order->shipping_cost ?? 0;
        $giftFee = $order->is_gift ? ($order->gift_wrap_fee > 0 ? $order->gift_wrap_fee : 20) : 0;
        $discountAmount = $order->discount_amount ?? 0;
        $grandTotal = $order->total_amount > 0 ? $order->total_amount : ($itemSubtotal + $shippingCost + $giftFee - $discountAmount);

        // Convert number to Bengali words helper
        function numberToWordsBn($num) {
            $bnDigits = ['০','১','২','৩','৪','৫','৬','৭','৮','৯'];
            $num = (int)$num;
            if ($num == 0) return 'শূন্য টাকা মাত্র';
            
            $units = ['', 'এক', 'দুই', 'তিন', 'চার', 'পাঁচ', 'ছয়', 'সাত', 'আট', 'নয়', 'দশ', 'এগারো', 'বারো', 'তেরো', 'চৌদ্দ', 'পনেরো', 'ষোলো', 'সতেরো', 'আঠারো', 'উনিশ', 'বিশ'];
            $tens = ['', '', 'বিশ', 'ত্রিশ', 'চল্লিশ', 'পঞ্চাশ', 'ষাট', 'সত্তর', 'আশি', 'নব্বই'];

            $crore = floor($num / 10000000);
            $num %= 10000000;
            $lakh = floor($num / 100000);
            $num %= 100000;
            $thousand = floor($num / 1000);
            $num %= 1000;
            $hundred = floor($num / 100);
            $remainder = $num % 100;

            $words = [];
            if ($crore > 0) $words[] = ($crore <= 20 ? $units[$crore] : $crore) . ' কোটি';
            if ($lakh > 0) $words[] = ($lakh <= 20 ? $units[$lakh] : $lakh) . ' লাখ';
            if ($thousand > 0) $words[] = ($thousand <= 20 ? $units[$thousand] : $thousand) . ' হাজার';
            if ($hundred > 0) $words[] = ($hundred <= 20 ? $units[$hundred] : $hundred) . 'শত';
            if ($remainder > 0) {
                if ($remainder <= 20) {
                    $words[] = $units[$remainder];
                } else {
                    $t = floor($remainder / 10);
                    $u = $remainder % 10;
                    $words[] = $tens[$t] . ($u > 0 ? ' ' . $units[$u] : '');
                }
            }
            return implode(' ', $words) . ' টাকা মাত্র';
        }

        $amountInWordsBn = numberToWordsBn($grandTotal);
        $cleanPhone = preg_replace('/[^0-9]/', '', $order->customer_phone);
        if (str_starts_with($cleanPhone, '01')) {
            $cleanPhone = '88' . $cleanPhone;
        }
        $whatsappMessage = "আসসালামু আলাইকুম {$order->customer_name}!\nআইডিয়া প্রকাশনে আপনার বই অর্ডার #{$order->order_number} এর মেমো ও বিস্তারিত নিচে দেওয়া হলো:\n\nসর্বমোট প্রদেয়: ৳ " . number_format($grandTotal) . "\nপেমেন্ট স্ট্যাটাস: " . ($order->payment_status === 'paid' ? 'পরিশোধিত (PAID)' : 'ক্যাশ অন ডেলিভারি (COD)') . "\n\nঅনলাইন মেমো ও অর্ডার ট্র্যাকিং লিংক:\n" . url('/track-order?order_no=' . ($order->order_number ?? $order->id)) . "\n\nআইডিয়া প্রকাশনের সাথে থাকার জন্য ধন্যবাদ!";
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
            font-family: 'Hind Siliguri', 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
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
            font-size: 5rem;
            font-weight: 900;
            color: rgba(15, 23, 42, 0.035);
            text-transform: uppercase;
            letter-spacing: 12px;
            pointer-events: none;
            z-index: 1;
            user-select: none;
            white-space: nowrap;
            font-family: 'Inter', sans-serif;
        }

        .invoice-header-strip {
            background: var(--theme-gradient);
            color: #ffffff;
            padding: 22px 36px;
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
            max-height: 54px;
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
            font-size: 13.5px;
            letter-spacing: 0.5px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
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
            font-size: 13px;
            padding: 11px 14px;
            border-right: 1px solid rgba(255, 255, 255, 0.1);
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
                    <i class="fa-solid fa-arrow-left"></i> সকল অর্ডার
                </a>
                <button type="button" class="btn btn-warning btn-sm btn-action-pill text-dark shadow-xs" data-bs-toggle="modal" data-bs-target="#modalQuickEdit">
                    <i class="fa-solid fa-pen-to-square"></i> তথ্য সম্পাদন
                </button>
                <a href="{{ route('admin.ecommerce-orders.slip', $order) }}" target="_blank" class="btn btn-outline-primary btn-sm btn-action-pill">
                    <i class="fa-solid fa-tag"></i> পার্সেল স্লিপ
                </a>
            </div>

            <!-- Middle Controls: WhatsApp, Share, Direct SMS -->
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <a href="{{ $whatsappUrl }}" target="_blank" class="btn btn-success btn-sm btn-action-pill text-white shadow-xs">
                    <i class="fa-brands fa-whatsapp"></i> WhatsApp মেমো
                </a>
                <button type="button" class="btn btn-outline-info btn-sm btn-action-pill text-dark" onclick="copyInvoiceLink()">
                    <i class="fa-solid fa-link" id="copyLinkIcon"></i> লিংক কপি
                </button>
            </div>

            <!-- Right Controls: Themes, Language, Print -->
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <!-- Theme Palette -->
                <div class="d-flex align-items-center gap-1.5 px-2 py-1 bg-light rounded-pill border">
                    <span class="theme-dot" style="background:#1e3a8a;" onclick="setInvoiceTheme('navy')" title="Classic Navy"></span>
                    <span class="theme-dot" style="background:#059669;" onclick="setInvoiceTheme('emerald')" title="Emerald Green"></span>
                    <span class="theme-dot" style="background:#18181b;" onclick="setInvoiceTheme('noir')" title="Corporate Noir"></span>
                    <span class="theme-dot" style="background:#7e22ce;" onclick="setInvoiceTheme('purple')" title="Royal Purple"></span>
                </div>

                <!-- Language Toggle -->
                <button type="button" class="btn btn-outline-secondary btn-sm btn-action-pill" onclick="toggleLanguage()" id="btnLangToggle">
                    <i class="fa-solid fa-language"></i> <span id="langLabel">English</span>
                </button>

                <!-- Print Button -->
                <button onclick="window.print()" class="btn btn-primary btn-sm btn-action-pill px-3.5 fw-bold shadow-xs">
                    <i class="fa-solid fa-print"></i> প্রিন্ট ইনভয়েস
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
                            <i class="fa-solid fa-globe me-1"></i> {{ $siteWebsite }} | <span data-bn="জ্ঞান ও সৃজনশীলতার ডিজিটাল বাতিঘর" data-en="Digital Lighthouse of Knowledge & Creativity">জ্ঞান ও সৃজনশীলতার ডিজিটাল বাতিঘর</span>
                        </div>
                    </div>
                </div>
                <div class="col-5 text-end">
                    <div class="invoice-badge-pill">
                        <i class="fa-solid fa-file-invoice-dollar text-warning"></i>
                        <span data-bn="{{ $invoiceSettings['invoice_title'] ?? 'অফিসিয়াল ক্যাশ মেমো' }}" data-en="OFFICIAL CASH MEMO & INVOICE">{{ $invoiceSettings['invoice_title'] ?? 'অফিসিয়াল ক্যাশ মেমো' }}</span>
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
                        <span class="text-muted small d-block" data-bn="ইনভয়েস / মেমো নম্বর:" data-en="Invoice / Memo No:">ইনভয়েস / মেমো নম্বর:</span>
                        <strong class="text-dark font-monospace fs-6">#{{ $order->order_number ?? $order->id }}</strong>
                    </div>
                    <div class="col-6 col-md-3">
                        <span class="text-muted small d-block" data-bn="অর্ডারের তারিখ ও সময়:" data-en="Order Date & Time:">অর্ডারের তারিখ ও সময়:</span>
                        <strong class="text-dark">{{ $order->created_at->format('d M, Y — h:i A') }}</strong>
                    </div>
                    <div class="col-6 col-md-3">
                        <span class="text-muted small d-block" data-bn="পেমেন্ট মাধ্যম:" data-en="Payment Method:">পেমেন্ট মাধ্যম:</span>
                        <strong class="text-dark">{{ $order->payment_method_label }}</strong>
                        @if($order->transaction_id)
                            <div class="small font-monospace text-primary" style="font-size: 11px;">TrxID: <strong>{{ $order->transaction_id }}</strong></div>
                        @endif
                    </div>
                    <div class="col-6 col-md-3 text-md-end">
                        <span class="text-muted small d-block" data-bn="পেমেন্ট অবস্থা:" data-en="Payment Status:">পেমেন্ট অবস্থা:</span>
                        @if($order->payment_status === 'paid')
                            <span class="badge bg-success px-2.5 py-1 fw-bold">
                                <i class="fa-solid fa-circle-check me-1"></i> <span data-bn="পরিশোধিত (PAID)" data-en="PAID">পরিশোধিত (PAID)</span>
                            </span>
                        @else
                            <span class="badge bg-warning text-dark px-2.5 py-1 fw-bold">
                                <i class="fa-solid fa-clock me-1"></i> <span data-bn="ক্যাশ অন ডেলিভারি (COD)" data-en="Cash On Delivery (COD)">ক্যাশ অন ডেলিভারি (COD)</span>
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Sender & Recipient Two-Column Layout -->
            <div class="row g-3 mb-4">
                
                <!-- Left: Sender (প্রেরক) -->
                <div class="col-md-6">
                    <div class="party-card sender">
                        <div class="party-tag" style="color: var(--theme-primary);">
                            <span><i class="fa-solid fa-paper-plane me-1"></i> <span data-bn="প্রেরক (From / Sender)" data-en="From / Sender">প্রেরক (From / Sender)</span></span>
                            <span class="badge text-white" style="background: var(--theme-primary);">হেড অফিস</span>
                        </div>
                        <h6 class="fw-bold text-dark mb-1">{{ $siteName }}</h6>
                        <div class="text-muted small mb-1">
                            <i class="fa-solid fa-location-dot text-danger me-1"></i>
                            {{ $siteAddress }}
                        </div>
                        <div class="text-dark small mb-1">
                            <i class="fa-solid fa-phone text-success me-1"></i>
                            <strong>হটলাইন:</strong> {{ $sitePhone }}
                        </div>
                        @if(!empty($siteEmail))
                        <div class="text-muted small">
                            <i class="fa-solid fa-envelope text-primary me-1"></i>
                            {{ $siteEmail }}
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Right: Recipient (প্রাপক / গ্রাহক) -->
                <div class="col-md-6">
                    <div class="party-card recipient">
                        <div class="party-tag text-success">
                            <span><i class="fa-solid fa-user-check me-1"></i> <span data-bn="প্রাপক (To / Recipient)" data-en="To / Recipient">প্রাপক (To / Recipient)</span></span>
                            <span class="badge bg-success text-white">গ্রাহক</span>
                        </div>
                        <h6 class="fw-bold text-dark mb-1">{{ $order->customer_name }}</h6>
                        <div class="text-dark mb-1">
                            <i class="fa-solid fa-phone text-success me-1"></i>
                            <strong>মোবাইল:</strong> <span class="fw-bold fs-6 font-monospace">{{ $order->customer_phone }}</span>
                        </div>
                        <div class="text-dark small mb-1">
                            <i class="fa-solid fa-map-location-dot text-primary me-1"></i>
                            <strong>ডেলিভারি ঠিকানা:</strong>
                            @if($order->house_road)
                                {{ $order->house_road }},
                            @endif
                            {{ $order->customer_address }}
                        </div>
                        <div class="text-muted small">
                            @if($order->thana)
                                <span class="me-2"><strong>থানা:</strong> {{ $order->thana }}</span>
                            @endif
                            @if($order->post_code)
                                <span class="me-2"><strong>পোস্ট:</strong> {{ $order->post_code }}</span>
                            @endif
                            <span><strong>জেলা:</strong> {{ $order->district_label }}</span>
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
                        <strong class="d-block text-amber-900 fs-6" style="color: #92400e;">উপহার প্যাকেজ ও প্রাপকের তথ্য (Gift Package):</strong>
                        <span class="me-3"><strong>নাম:</strong> {{ $order->gift_recipient_name }}</span>
                        <span class="me-3"><strong>মোবাইল:</strong> {{ $order->gift_recipient_phone }}</span>
                        @if($order->gift_recipient_address)
                            <span class="d-block mt-1"><strong>উপহার ঠিকানা:</strong> {{ $order->gift_recipient_address }}</span>
                        @endif
                        @if($order->gift_message)
                            <div class="mt-2 small p-2 bg-white rounded border border-warning text-dark fst-italic">
                                <strong>উপহার বার্তা:</strong> "{{ $order->gift_message }}"
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            <!-- Items Table (বিলের পণ্যের বিবরণ) -->
            <table class="table-memo">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 6%;">#</th>
                        <th style="width: 52%;" data-bn="বই / পণ্যের বিবরণ" data-en="Book / Item Description">বই / পণ্যের বিবরণ</th>
                        <th class="text-center" style="width: 14%;" data-bn="একক মূল্য" data-en="Unit Price">একক মূল্য</th>
                        <th class="text-center" style="width: 12%;" data-bn="পরিমাণ" data-en="Quantity">পরিমাণ</th>
                        <th class="text-end" style="width: 16%;" data-bn="মোট টাকা" data-en="Total (BDT)">মোট টাকা</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="text-center fw-bold">১</td>
                        <td>
                            @if($order->book)
                                <div class="d-flex align-items-center gap-2.5">
                                    @if($order->book->cover_image)
                                        <img src="{{ asset('storage/' . $order->book->cover_image) }}" alt="{{ $order->book->title }}" style="width: 38px; height: 50px; object-fit: cover; border-radius: 4px;" class="border d-none d-sm-inline-block">
                                    @endif
                                    <div>
                                        <div class="fw-bold text-dark fs-6">{{ $order->book->title }}</div>
                                        @if($order->book->authors && $order->book->authors->count())
                                            <div class="text-muted small">লেখক: <strong>{{ $order->book->authors->pluck('name')->implode(', ') }}</strong></div>
                                        @endif
                                        @if($order->book->isbn)
                                            <div class="text-muted small">ISBN: {{ $order->book->isbn }}</div>
                                        @endif
                                    </div>
                                </div>
                            @else
                                <div class="fw-bold text-dark fs-6">বইয়ের অর্ডার #{{ $order->order_number ?? $order->id }}</div>
                            @endif
                        </td>
                        <td class="text-center fw-semibold font-monospace">
                            ৳ {{ number_format($itemUnit, 2) }}
                        </td>
                        <td class="text-center fw-bold">
                            {{ $itemQty }} টি
                        </td>
                        <td class="text-end fw-bold text-dark font-monospace">
                            ৳ {{ number_format($itemSubtotal, 2) }}
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
                            <i class="fa-solid fa-truck-fast text-primary"></i> <span data-bn="ডেলিভারি ও কুরিয়ার তথ্য:" data-en="Courier & Shipping Details:">ডেলিভারি ও কুরিয়ার তথ্য:</span>
                        </div>
                        <div class="small"><strong>কুরিয়ার পার্টনার:</strong> <span id="displayCourier">{{ $order->courier_name ?? 'নির্ধারিত কুরিয়ার' }}</span></div>
                        @if($order->tracking_code)
                            <div class="small mt-1"><strong>ট্র্যাকিং আইডি:</strong> <span class="font-monospace text-primary fw-bold" id="displayTracking">{{ $order->tracking_code }}</span></div>
                        @endif
                    </div>
                    @endif

                    <div class="p-3 bg-light rounded-3 border mb-3" style="font-size: 12px;">
                        <div class="fw-bold text-dark mb-1 d-flex align-items-center gap-1.5">
                            <i class="fa-solid fa-shield-halved text-primary"></i> <span data-bn="পলিসি ও শর্তাবলী:" data-en="Terms & Policy:">পলিসি ও শর্তাবলী:</span>
                        </div>
                        <p class="text-muted mb-0">
                            {{ $invoiceSettings['invoice_terms'] ?? 'পণ্য গ্রহণের সময় অনুগ্রহ করে চেক করে নিন। কোনো ত্রুটি থাকলে ডেলিভারি ম্যানের সামনেই আমাদের হেল্পলাইনে যোগাযোগ করুন।' }}
                        </p>
                        @if($order->admin_notes)
                        <div class="mt-2 text-dark fw-semibold pt-1 border-top" id="displayAdminNotes">
                            <strong>অ্যাডমিন নোট:</strong> {{ $order->admin_notes }}
                        </div>
                        @endif
                    </div>

                    <!-- Live Dynamic QR Code Box -->
                    <div class="d-flex align-items-center gap-3 p-2.5 bg-white rounded-3 border">
                        <div id="invoiceQrCode" style="width: 58px; height: 58px;"></div>
                        <div class="small">
                            <strong class="d-block text-dark" data-bn="ডিজিটাল ভেরিফিকেশন কিউআর" data-en="Digital Verification QR">ডিজিটাল ভেরিফিকেশন কিউআর</strong>
                            <span class="text-muted" style="font-size: 11px;">মোবাইল দিয়ে স্ক্যান করে অর্ডারের বর্তমান অবস্থা ট্র্যাক করুন।</span>
                        </div>
                    </div>

                </div>

                <!-- Right Column: Financial Breakdown -->
                <div class="col-md-6">
                    <div class="summary-box">
                        <div class="summary-item">
                            <span data-bn="পণ্যের সাবটোটাল:" data-en="Items Subtotal:">পণ্যের সাবটোটাল:</span>
                            <span class="fw-bold text-dark font-monospace">৳ {{ number_format($itemSubtotal, 2) }}</span>
                        </div>
                        <div class="summary-item">
                            <span data-bn="ডেলিভারি চার্জ:" data-en="Shipping Cost:">ডেলিভারি চার্জ:</span>
                            <span class="fw-semibold text-dark font-monospace">
                                @if($shippingCost > 0)
                                    ৳ {{ number_format($shippingCost, 2) }}
                                @else
                                    <span class="text-success fw-bold" data-bn="ফ্রি ডেলিভারি" data-en="Free Delivery">ফ্রি ডেলিভারি</span>
                                @endif
                            </span>
                        </div>
                        @if($order->is_gift)
                        <div class="summary-item">
                            <span data-bn="উপহার মোড়কীকরণ (Gift Wrap):" data-en="Gift Wrap Fee:">উপহার মোড়কীকরণ (Gift Wrap):</span>
                            <span class="fw-semibold text-dark font-monospace">৳ {{ number_format($giftFee, 2) }}</span>
                        </div>
                        @endif
                        @if($discountAmount > 0)
                        <div class="summary-item text-danger">
                            <span data-bn="বিশেষ ছাড় (Discount):" data-en="Discount:">বিশেষ ছাড় (Discount):</span>
                            <span class="font-monospace">- ৳ {{ number_format($discountAmount, 2) }}</span>
                        </div>
                        @endif
                        <div class="summary-item total-due">
                            <span data-bn="সর্বমোট প্রদেয় (Grand Total):" data-en="Grand Total (Due):">সর্বমোট প্রদেয় (Grand Total):</span>
                            <span class="font-monospace">৳ {{ number_format($grandTotal, 2) }}</span>
                        </div>

                        <!-- Amount in Words -->
                        <div class="in-words-box">
                            <strong>কথায়:</strong> {{ $amountInWordsBn }}
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                            <span class="small text-muted" data-bn="পেমেন্ট অবস্থা:" data-en="Payment Status:">পেমেন্ট অবস্থা:</span>
                            @if($order->payment_status === 'paid')
                                <span class="paid-stamp">
                                    <i class="fa-solid fa-check-double me-1"></i> PAID (পরিশোধিত)
                                </span>
                            @else
                                <span class="due-stamp">
                                    <i class="fa-solid fa-hand-holding-dollar me-1"></i> DUE (ডেলিভারিতে প্রদেয়)
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
                            <span data-bn="{{ $invoiceSettings['invoice_footer'] ?? 'বই পড়ার আনন্দ ছড়িয়ে পড়ুক সবার মাঝে। ideaabd-এর সাথে থাকার জন্য ধন্যবাদ!' }}" data-en="May the joy of reading books spread to everyone. Thank you for choosing ideaabd!">{{ $invoiceSettings['invoice_footer'] ?? 'বই পড়ার আনন্দ ছড়িয়ে পড়ুক সবার মাঝে। ideaabd-এর সাথে থাকার জন্য ধন্যবাদ!' }}</span>
                        </p>
                        <div class="text-muted" style="font-size: 11px;">
                            প্রিন্ট তারিখ: {{ date('d M, Y — h:i A') }} | কম্পিউটার জেনারেটেড ডিজিটাল ইনভয়েস
                        </div>
                    </div>
                    <div class="col-5 text-end">
                        <div class="d-inline-block text-center" style="min-width: 150px;">
                            <div style="height: 40px;"></div>
                            <div class="border-top border-dark pt-1 small fw-bold text-dark" data-bn="অনুমোদিত স্বাক্ষরকারী" data-en="Authorized Signature">
                                অনুমোদিত স্বাক্ষরকারী
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
                        <i class="fa-solid fa-pen-to-square text-primary"></i> ইনভয়েস ও অর্ডার তথ্য দ্রুত সম্পাদন
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formQuickEdit" onsubmit="handleQuickEditSubmit(event)">
                    <div class="modal-body p-4 space-y-3">
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-muted">পেমেন্ট অবস্থা <span class="text-danger">*</span></label>
                            <select name="payment_status" id="editPaymentStatus" class="form-select">
                                <option value="pending" @selected($order->payment_status === 'pending')>অপেক্ষমান (Pending / COD)</option>
                                <option value="paid" @selected($order->payment_status === 'paid')>পরিশোধিত (Paid)</option>
                                <option value="unpaid" @selected($order->payment_status === 'unpaid')>অপরিশোধিত (Unpaid)</option>
                                <option value="partial" @selected($order->payment_status === 'partial')>আংশিক পরিশোধিত (Partial)</option>
                            </select>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label fw-semibold small text-muted">কুরিয়ার পার্টনার</label>
                                <input type="text" name="courier_name" id="editCourierName" class="form-control" value="{{ $order->courier_name }}" placeholder="যেমন: সুন্দরবন, পাঠাও, স্টেডফাস্ট">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold small text-muted">ট্র্যাকিং কোড</label>
                                <input type="text" name="tracking_code" id="editTrackingCode" class="form-control font-monospace" value="{{ $order->tracking_code }}" placeholder="TRACK123456">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-muted">অর্ডার ট্রানজেকশন আইডি (TrxID)</label>
                            <input type="text" name="transaction_id" id="editTrxId" class="form-control font-monospace" value="{{ $order->transaction_id }}" placeholder="যেমন: 8N7K5A...">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-muted">অ্যাডমিন নোট (মেমোতে প্রদর্শিত)</label>
                            <textarea name="admin_notes" id="editAdminNotes" class="form-control" rows="2" placeholder="প্রয়োজনীয় নির্দেশনা...">{{ $order->admin_notes }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-top p-3 d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">বাতিল</button>
                        <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4 fw-semibold" id="btnSaveQuickEdit">
                            <i class="fa-solid fa-floppy-disk me-1"></i> সংরক্ষণ করুন
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

        // Dynamic Language Switcher (বাংলা ↔ English)
        let currentLang = 'bn';
        function toggleLanguage() {
            currentLang = (currentLang === 'bn') ? 'en' : 'bn';
            document.documentElement.setAttribute('data-lang', currentLang);
            document.getElementById('langLabel').textContent = (currentLang === 'bn') ? 'English' : 'বাংলা';

            document.querySelectorAll('[data-bn]').forEach(el => {
                const text = el.getAttribute('data-' + currentLang);
                if (text) {
                    el.textContent = text;
                }
            });
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
                    // Hide Modal & reload to display updated values
                    const modal = bootstrap.Modal.getInstance(document.getElementById('modalQuickEdit'));
                    if (modal) modal.hide();
                    window.location.reload();
                } else {
                    alert(data.message || 'সংরক্ষণে সমস্যা হয়েছে।');
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-floppy-disk me-1"></i> সংরক্ষণ করুন';
                alert('সার্ভার যোগাযোগ ত্রুটি: ' + err.message);
            });
        }
    </script>

</body>
</html>
