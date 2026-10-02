<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>পার্সেল স্লিপ #{{ $order->order_number ?? $order->id }} — {{ $order->customer_name }}</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.maateen.me/kalpurush/font.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    @php
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

        $orderDate = !empty($order->created_at) ? \Carbon\Carbon::parse($order->created_at)->format('d M, Y') : date('d M, Y');
        $trackUrl = url('/track-order?order_no=' . ($order->order_number ?? $order->id));
        $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=100x100&margin=0&data=' . urlencode($trackUrl);
    @endphp

    <style>
        *, *::before, *::after {
            box-sizing: border-box;
        }
        body {
            font-family: 'Inter', 'Kalpurush', 'Hind Siliguri', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: #f1f5f9;
            margin: 0;
            padding: 24px 16px;
            font-size: 13px;
            line-height: 1.5;
            color: #0f172a;
            -webkit-font-smoothing: antialiased;
        }

        /* Top Action Bar */
        .slip-actions {
            max-width: 620px;
            margin: 0 auto 16px auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }
        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            border: 1px solid transparent;
            transition: all 0.15s ease;
        }
        .btn-action-primary {
            background: #0f172a;
            color: #ffffff;
            border-color: #0f172a;
        }
        .btn-action-primary:hover {
            background: #1e293b;
            color: #ffffff;
        }
        .btn-action-outline {
            background: #ffffff;
            color: #334155;
            border-color: #cbd5e1;
        }
        .btn-action-outline:hover {
            background: #f8fafc;
            color: #0f172a;
            border-color: #94a3b8;
        }

        /* Main Slip Card */
        .slip-card {
            max-width: 620px;
            margin: 0 auto;
            background: #ffffff;
            border: 2px solid #0f172a;
            border-radius: 12px;
            padding: 26px 28px;
            box-shadow: 0 4px 20px rgba(15, 23, 42, 0.06);
        }

        /* Header & Logo */
        .slip-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-bottom: 18px;
            margin-bottom: 20px;
            border-bottom: 2px solid #0f172a;
            gap: 16px;
        }
        .slip-brand-group {
            display: flex;
            align-items: center;
            gap: 20px; /* Generous gap between logo and text */
            flex: 1;
        }
        .slip-logo {
            max-height: 50px;
            max-width: 140px;
            width: auto;
            object-fit: contain;
            flex-shrink: 0;
            display: block;
            margin-right: 4px;
        }
        .slip-brand-info {
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .slip-brand-title {
            font-size: 18px;
            font-weight: 800;
            color: #0f172a;
            margin: 0 0 3px 0;
            letter-spacing: -0.2px;
            line-height: 1.25;
        }
        .slip-brand-sub {
            font-size: 12px;
            color: #64748b;
            margin: 0;
            font-weight: 500;
        }
        .slip-badge-parcel {
            background: #0f172a;
            color: #ffffff;
            padding: 6px 14px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            white-space: nowrap;
            display: inline-block;
        }

        /* Order & COD Info Strip */
        .slip-strip {
            display: flex;
            align-items: stretch;
            justify-content: space-between;
            gap: 14px;
            margin-bottom: 20px;
        }
        .slip-strip-left {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 12px 16px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
        }
        .slip-order-num {
            font-family: 'Courier New', Courier, monospace;
            font-size: 16px;
            font-weight: 800;
            letter-spacing: 1.5px;
            color: #0f172a;
            margin-bottom: 4px;
        }
        .slip-order-date {
            font-size: 12px;
            color: #64748b;
        }
        .slip-strip-right {
            width: 220px;
            flex-shrink: 0;
            background: #0f172a;
            color: #ffffff;
            padding: 12px 16px;
            border-radius: 8px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            text-align: center;
        }
        .slip-cod-label {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #94a3b8;
            margin-bottom: 3px;
        }
        .slip-cod-val {
            font-size: 19px;
            font-weight: 800;
            line-height: 1.2;
        }
        .slip-cod-val.is-paid {
            color: #4ade80;
        }

        /* 2-Column Sender & Recipient */
        .slip-grid {
            display: grid;
            grid-template-columns: 1fr 1.35fr;
            gap: 16px;
            margin-bottom: 20px;
        }
        .slip-box {
            border-radius: 8px;
            padding: 16px 18px; /* Generous padding so text never touches border */
            display: flex;
            flex-direction: column;
        }
        .slip-box-sender {
            background: #f8fafc;
            border: 1.5px solid #cbd5e1;
        }
        .slip-box-recipient {
            background: #ffffff;
            border: 2px solid #0f172a;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
        }
        .slip-box-header {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            padding-bottom: 8px;
            margin-bottom: 12px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .slip-box-sender .slip-box-header {
            color: #475569;
        }
        .slip-box-recipient .slip-box-header {
            color: #0f172a;
            border-bottom-color: #cbd5e1;
        }

        /* Sender Details */
        .sender-name {
            font-size: 14px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 6px;
        }
        .sender-address {
            font-size: 12px;
            color: #475569;
            line-height: 1.5;
            margin-bottom: 8px;
        }
        .sender-phone {
            font-size: 12px;
            font-weight: 600;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 6px;
            margin-top: auto;
        }

        /* Recipient Details (Fixed Padding & Clear Margins) */
        .recipient-name {
            font-size: 17px;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.35;
            margin-bottom: 6px;
            word-break: break-word;
        }
        .recipient-phone-wrap {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 15px;
            font-weight: 800;
            color: #0f172a;
            font-family: 'Courier New', Courier, monospace;
            margin-bottom: 10px;
        }
        .recipient-address {
            font-size: 13px;
            color: #1e293b;
            line-height: 1.55;
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
            font-size: 11.5px;
            font-weight: 600;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            padding: 3px 8px;
            border-radius: 4px;
            color: #334155;
        }
        .recipient-pill-dark {
            background: #0f172a;
            color: #ffffff;
            border-color: #0f172a;
            font-weight: 700;
        }

        /* Item & Parcel Content Box (Generous Padding & Clear Separation) */
        .slip-item-box {
            padding: 14px 18px; /* Generous padding - text will not touch borders */
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-radius: 8px;
            margin-bottom: 16px;
        }
        .slip-item-grid {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }
        .slip-item-title {
            font-size: 13px;
            color: #0f172a;
            line-height: 1.45;
        }
        .slip-item-title strong {
            font-weight: 700;
            color: #0f172a;
        }
        .slip-item-qty {
            font-size: 12.5px;
            color: #334155;
            white-space: nowrap;
            background: #ffffff;
            padding: 4px 10px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-weight: 600;
        }
        .slip-gift-notice {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px dashed #cbd5e1;
            font-size: 12px;
            color: #92400e;
            display: flex;
            align-items: center;
            gap: 6px;
            font-weight: 600;
        }

        /* Courier & Tracking Footer Strip */
        .slip-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            padding: 12px 16px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
        }
        .slip-courier-info {
            display: flex;
            align-items: center;
            gap: 14px;
            flex-wrap: wrap;
            font-size: 12px;
            color: #475569;
        }
        .slip-courier-info strong {
            color: #0f172a;
        }
        .slip-qr-box {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-shrink: 0;
        }
        .slip-qr-img {
            width: 44px;
            height: 44px;
            display: block;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 2px;
            background: #ffffff;
        }
        .slip-qr-text {
            font-size: 10px;
            line-height: 1.25;
            color: #64748b;
            text-align: right;
        }

        /* Print Media Styles */
        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .slip-actions {
                display: none !important;
            }
            .slip-card {
                max-width: 100% !important;
                box-shadow: none !important;
                border: 2px solid #000000 !important;
                border-radius: 0 !important;
                padding: 6mm 8mm !important;
                margin: 0 auto !important;
            }
            .slip-box-recipient {
                border: 2px solid #000000 !important;
            }
            .slip-box-sender,
            .slip-item-box,
            .slip-strip-left,
            .slip-footer {
                border-color: #000000 !important;
            }
            .slip-strip-right,
            .slip-badge-parcel,
            .recipient-pill-dark {
                background: #000000 !important;
                color: #ffffff !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            @page {
                size: 105mm 148mm; /* Standard A6 Sticker / Label format */
                margin: 5mm;
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
                border-top: 1px dashed #e2e8f0;
            }
        }
    </style>
</head>
<body>

    <!-- Top Action Bar (Hidden on Print) -->
    <div class="slip-actions">
        <a href="{{ route('admin.ecommerce-orders.invoice', $order->id) }}" class="btn-action btn-action-outline">
            <i class="fa-solid fa-file-invoice"></i> ইনভয়েস দেখুন
        </a>
        <div style="display: flex; gap: 8px;">
            <a href="{{ route('admin.ecommerce-orders') }}" class="btn-action btn-action-outline">
                <i class="fa-solid fa-arrow-left"></i> অর্ডার তালিকা
            </a>
            <button onclick="window.print()" class="btn-action btn-action-primary">
                <i class="fa-solid fa-print"></i> স্লিপ প্রিন্ট করুন
            </button>
        </div>
    </div>

    <!-- Main Slip Container -->
    <div class="slip-card">
        
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
                <span class="slip-badge-parcel">বুক পার্সেল / PARCEL</span>
            </div>
        </div>

        <!-- Order & COD Strip -->
        <div class="slip-strip">
            <div class="slip-strip-left">
                <div class="slip-order-num">#{{ $order->order_number ?? $order->id }}</div>
                <div class="slip-order-date">
                    <i class="fa-regular fa-calendar-check me-1"></i> তারিখ: {{ $orderDate }}
                </div>
            </div>
            <div class="slip-strip-right">
                @if($order->payment_status === 'paid')
                    <div class="slip-cod-label">পেমেন্ট স্ট্যাটাস</div>
                    <div class="slip-cod-val is-paid"><i class="fa-solid fa-circle-check me-1"></i> পেইড (PAID)</div>
                @else
                    <div class="slip-cod-label">ক্যাশ অন ডেলিভারি (COD)</div>
                    <div class="slip-cod-val">৳ {{ number_format($order->total_amount) }}</div>
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
                    <span style="font-size: 10px; font-weight: 700; color: #475569;">ডেলিভারি ঠিকানা</span>
                </div>
                
                <div class="recipient-name">{{ $order->customer_name }}</div>
                
                <div class="recipient-phone-wrap">
                    <i class="fa-solid fa-phone" style="font-size: 13px;"></i>
                    <span>{{ $order->customer_phone }}</span>
                </div>
                
                <div class="recipient-address">
                    @if($order->house_road)
                        {{ $order->house_road }},
                    @endif
                    {{ $order->customer_address }}
                </div>

                <div class="recipient-pills">
                    @if($order->thana)
                        <span class="recipient-pill">থানা: {{ $order->thana }}</span>
                    @endif
                    @if($order->post_code)
                        <span class="recipient-pill">পোস্ট কোড: {{ $order->post_code }}</span>
                    @endif
                    @if($order->district || $order->district_label)
                        <span class="recipient-pill recipient-pill-dark">জেলা: {{ $order->district ?? $order->district_label }}</span>
                    @endif
                </div>
            </div>

        </div>

        <!-- Book & Parcel Item Box (Generous Padding) -->
        <div class="slip-item-box">
            <div class="slip-item-grid">
                <div class="slip-item-title">
                    <strong>আইটেম / বই:</strong> {{ $order->book->title ?? 'বই অর্ডার' }}
                </div>
                <div class="slip-item-qty">
                    <strong>পরিমাণ:</strong> {{ $order->quantity ?? 1 }} কপি
                </div>
            </div>

            @if($order->is_gift)
                <div class="slip-gift-notice">
                    <i class="fa-solid fa-gift"></i> গিফট পার্সেল (প্রাপক: {{ $order->gift_recipient_name ?? $order->customer_name }} | {{ $order->gift_recipient_phone ?? '' }})
                </div>
            @endif
        </div>

        <!-- Courier & Verification Strip -->
        <div class="slip-footer">
            <div class="slip-courier-info">
                <span>কুরিয়ার: <strong>{{ $order->courier_name ?: 'সাধারণ কুরিয়ার' }}</strong></span>
                @if($order->tracking_code)
                    <span>ট্র্যাকিং নং: <strong style="font-family: monospace; font-size: 13px;">{{ $order->tracking_code }}</strong></span>
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

</body>
</html>
