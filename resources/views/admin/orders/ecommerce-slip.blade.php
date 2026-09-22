<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>পার্সেল স্লিপ / স্টিকার #{{ $order->order_number ?? $order->id }}</title>
    <!-- Google Fonts Bangla -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    @php
        $siteLogo    = \App\Support\SiteSetting::logoUrl() ?: asset('images/logo.png');
        $siteName    = \App\Support\SiteSetting::name() ?: ($invoiceSettings['sender_name'] ?? 'আইডিয়া প্রকাশন');
        $siteAddress = \App\Support\SiteSetting::get('contact_address') ?: ($invoiceSettings['sender_address'] ?? 'সেন্ট্রাল রোড, রংপুর ৫৪০০, বাংলাদেশ');
        $sitePhone   = \App\Support\SiteSetting::get('contact_phone') ?: ($invoiceSettings['sender_phone'] ?? '01558712870');
        $siteWebsite = $invoiceSettings['sender_website'] ?? 'www.ideaabd.com';
    @endphp

    <style>
        body {
            font-family: 'Hind Siliguri', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: #f1f5f9;
            margin: 0;
            padding: 20px;
            font-size: 13px;
            color: #0f172a;
        }
        .slip-container {
            max-width: 580px;
            margin: 0 auto;
            background: #ffffff;
            border: 2px solid #0f172a;
            border-radius: 12px;
            padding: 18px 22px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
        }
        .slip-logo {
            max-height: 42px;
            width: auto;
            max-width: 140px;
            object-fit: contain;
        }
        .box-title {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1.5px solid #cbd5e1;
            padding-bottom: 3px;
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .barcode {
            font-family: 'Courier New', Courier, monospace;
            font-size: 15px;
            font-weight: bold;
            letter-spacing: 3px;
            padding: 4px 10px;
            background: #f8fafc;
            border: 1px dashed #64748b;
            border-radius: 6px;
            display: inline-block;
        }
        .cod-amount-box {
            background: #0f172a;
            color: #ffffff;
            padding: 8px 14px;
            border-radius: 8px;
            text-align: center;
        }
        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
            }
            .d-print-none {
                display: none !important;
            }
            .slip-container {
                max-width: 100% !important;
                box-shadow: none !important;
                border: 2px solid #000000 !important;
                margin: 0 !important;
            }
            @page {
                size: 105mm 148mm; /* A6 */
                margin: 4mm;
            }
        }
    </style>
</head>
<body>

    <div class="d-print-none text-center mb-3">
        <button onclick="window.print()" class="btn btn-primary btn-sm rounded-pill px-4 fw-bold shadow-xs">
            <i class="fa-solid fa-print me-1"></i> স্লিপ প্রিন্ট করুন
        </button>
        <button onclick="window.close()" class="btn btn-outline-secondary btn-sm rounded-pill px-3 ms-2">
            বন্ধ করুন
        </button>
    </div>

    <div class="slip-container">
        
        <!-- Header & Logo -->
        <div class="d-flex justify-content-between align-items-center pb-2 border-bottom border-dark mb-3">
            <div class="d-flex align-items-center gap-2.5">
                <img src="{{ $siteLogo }}" alt="{{ $siteName }}" class="slip-logo" onerror="this.src='/images/logo.png'; this.onerror=null;">
                <div>
                    <h6 class="fw-bold mb-0 text-dark">{{ $siteName }}</h6>
                    <small class="text-muted">{{ $siteWebsite }}</small>
                </div>
            </div>
            <div class="text-end">
                <span class="badge bg-dark text-white px-2.5 py-1.5 fs-6 rounded-3">বই পার্সেল</span>
            </div>
        </div>

        <!-- Order & COD Strip -->
        <div class="row g-2 align-items-center mb-3">
            <div class="col-7">
                <div class="barcode">#{{ $order->order_number ?? $order->id }}</div>
                <div class="small text-muted mt-1">তারিখ: {{ $order->created_at->format('d M, Y') }}</div>
            </div>
            <div class="col-5">
                <div class="cod-amount-box">
                    @if($order->payment_status === 'paid')
                        <span class="small d-block opacity-75">পরিশোধিত</span>
                        <strong class="fs-5 text-success-subtle">PAID</strong>
                    @else
                        <span class="small d-block opacity-75">ক্যাশ অন ডেলিভারি (COD)</span>
                        <strong class="fs-5">৳ {{ number_format($order->total_amount) }}</strong>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sender & Recipient Box -->
        <div class="row g-2 mb-3">
            <!-- Sender -->
            <div class="col-5">
                <div class="p-2 border rounded-3 h-100" style="background:#f8fafc; font-size:12px;">
                    <div class="box-title text-primary"><i class="fa-solid fa-paper-plane me-1"></i> প্রেরক (From)</div>
                    <div class="fw-bold text-dark">{{ $siteName }}</div>
                    <div class="text-muted">{{ $siteAddress }}</div>
                    <div class="fw-semibold mt-1"><i class="fa-solid fa-phone text-success me-1 small"></i>{{ $sitePhone }}</div>
                </div>
            </div>
            <!-- Recipient -->
            <div class="col-7">
                <div class="p-2.5 border border-2 border-success rounded-3" style="background:#f0fdf4; font-size:12.5px;">
                    <div class="box-title text-success"><i class="fa-solid fa-user-check me-1"></i> প্রাপক (To)</div>
                    <div class="fw-bold fs-6 text-dark">{{ $order->customer_name }}</div>
                    <div class="fw-bold text-dark"><i class="fa-solid fa-phone text-success me-1"></i><span class="fs-6 font-monospace">{{ $order->customer_phone }}</span></div>
                    <div class="text-dark mt-1">
                        @if($order->house_road){{ $order->house_road }}, @endif
                        {{ $order->customer_address }}
                    </div>
                    <div class="small text-muted mt-1">
                        @if($order->thana) থানা: {{ $order->thana }}, @endif
                        @if($order->post_code) পোস্ট: {{ $order->post_code }}, @endif
                        <strong>জেলা: {{ $order->district_label }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Book & Parcel Content -->
        <div class="p-2.5 bg-light border rounded-3 mb-2" style="font-size:12px;">
            <div class="d-flex justify-content-between">
                <div><strong>বই:</strong> {{ $order->book->title ?? 'বইয়ের অর্ডার' }}</div>
                <div><strong>পরিমাণ:</strong> {{ $order->quantity ?? 1 }} কপি</div>
            </div>
            @if($order->is_gift)
                <div class="text-warning-emphasis fw-bold mt-1"><i class="fa-solid fa-gift me-1 text-warning"></i> উপহার পার্সেল (প্রাপক: {{ $order->gift_recipient_name }})</div>
            @endif
        </div>

        @if($order->courier_name || $order->tracking_code)
        <div class="d-flex justify-content-between small text-muted pt-1 border-top">
            <span>কুরিয়ার: <strong>{{ $order->courier_name ?? 'কুরিয়ার' }}</strong></span>
            @if($order->tracking_code)<span>ট্র্যাকিং: <strong class="font-monospace text-primary">{{ $order->tracking_code }}</strong></span>@endif
        </div>
        @endif

    </div>

</body>
</html>
