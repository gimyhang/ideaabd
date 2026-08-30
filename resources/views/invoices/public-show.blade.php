<!DOCTYPE html>
<html lang="bn" class="notranslate" translate="no">
<head>
    <meta charset="UTF-8">
    <meta name="google" content="notranslate">
    <meta name="googlebot" content="notranslate">
    <meta http-equiv="Content-Language" content="bn">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <title>{{ $invoice->type_label }} #{{ $invoice->invoice_no }} — {{ $settings['business_name'] ?? 'আইডিয়া প্রকাশন' }}</title>

    <link href="https://fonts.maateen.me/kalpurush/font.css" rel="stylesheet">
    <link href="https://fonts.maateen.me/nikosh/font.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body {
            background-color: #f1f5f9;
            font-family: 'Kalpurush', 'Nikosh', 'Hind Siliguri', sans-serif;
            color: #1e293b;
            margin: 0;
            padding: 0;
        }

        .top-action-bar {
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0,0,0,0.04);
        }

        .invoice-page-card {
            font-family: 'Kalpurush', 'Nikosh', 'Hind Siliguri', sans-serif;
            font-size: 10px;
            color: #1e293b;
            min-height: 980px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding-left: 0.5in !important;
            padding-right: 0.5in !important;
            background: #ffffff;
        }

        .invoice-table th,
        .invoice-table td {
            padding: 2px 4px !important;
            vertical-align: middle;
            line-height: 1.25;
            font-size: 10px;
        }

        .invoice-footer-compact {
            margin-top: auto;
            min-height: 0.5in;
        }

        .signature-box {
            margin-top: 24px;
        }

        .destination-box {
            box-sizing: border-box !important;
            width: 100% !important;
        }

        @page {
            size: A4 portrait;
            margin: 8mm 8mm 8mm 8mm;
        }

        @media print {
            *, ::before, ::after {
                box-sizing: border-box !important;
            }

            html, body {
                background: #ffffff !important;
                color: #000000 !important;
                font-family: 'Kalpurush', 'Nikosh', 'Hind Siliguri', sans-serif !important;
                font-size: 10px !important;
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
                overflow: visible !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .top-action-bar, .d-print-none, [class*="d-print-none"], .btn {
                display: none !important;
                visibility: hidden !important;
                height: 0 !important;
                margin: 0 !important;
                padding: 0 !important;
                overflow: hidden !important;
            }

            .container, .container-fluid, #invoicePrintWrapper, .col-lg-10 {
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
                border: none !important;
                box-shadow: none !important;
                overflow: visible !important;
            }

            .row {
                display: flex !important;
                flex-direction: row !important;
                flex-wrap: wrap !important;
                margin-right: 0 !important;
                margin-left: 0 !important;
                width: 100% !important;
            }

            .row > * {
                padding-right: 4px !important;
                padding-left: 4px !important;
                box-sizing: border-box !important;
            }

            .col-7 {
                flex: 0 0 58.333333% !important;
                width: 58.333333% !important;
                max-width: 58.333333% !important;
            }

            .col-5 {
                flex: 0 0 41.666667% !important;
                width: 41.666667% !important;
                max-width: 41.666667% !important;
            }

            .col-6 {
                flex: 0 0 50% !important;
                width: 50% !important;
                max-width: 50% !important;
            }

            .col-4 {
                flex: 0 0 33.333333% !important;
                width: 33.333333% !important;
                max-width: 33.333333% !important;
            }

            .col-12 {
                flex: 0 0 100% !important;
                width: 100% !important;
                max-width: 100% !important;
            }

            .invoice-page-card {
                border: none !important;
                box-shadow: none !important;
                padding: 0 !important;
                margin: 0 0 10px 0 !important;
                width: 100% !important;
                max-width: 100% !important;
                background: #ffffff !important;
                min-height: auto !important;
                height: auto !important;
                display: block !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
                overflow: visible !important;
            }

            .destination-box {
                width: 100% !important;
                margin-left: 0 !important;
                margin-right: 0 !important;
                box-sizing: border-box !important;
                border-color: #cbd5e1 !important;
            }

            .table-responsive {
                overflow: visible !important;
                display: block !important;
                width: 100% !important;
                margin: 0 0 8px 0 !important;
                padding: 0 !important;
                border: none !important;
            }

            .invoice-table {
                width: 100% !important;
                max-width: 100% !important;
                border-collapse: collapse !important;
                margin: 0 !important;
            }

            .invoice-table th,
            .invoice-table td {
                padding: 1.5px 3.5px !important;
                font-size: 9.5px !important;
                line-height: 1.2 !important;
                border-color: #475569 !important;
            }

            .invoice-table thead th {
                background-color: #f1f5f9 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .invoice-no-text {
                font-size: 13pt !important;
                font-weight: 700 !important;
            }

            .invoice-brand-name {
                font-size: 15.5px !important;
            }

            .invoice-footer-compact {
                margin-top: 15px !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }

            .page-break {
                display: block !important;
                page-break-before: always !important;
                page-break-after: avoid !important;
                break-before: page !important;
                height: 0 !important;
                margin: 0 !important;
                padding: 0 !important;
                border: none !important;
            }
        }
    </style>
</head>
<body>

@php
    $settings = $invoiceSettings ?? \App\Http\Controllers\Admin\IdeaAccountingController::getInvoiceSettings();
    $bizLogo = $settings['logo'] ?? '/images/logo.png';
    $logoSrc = \App\Support\SiteSetting::resolveImageUrl($bizLogo, 'images/logo.png') ?: asset('images/logo.png');

    $creatorName = !empty($settings['default_creator_name']) ? $settings['default_creator_name'] : ($invoice->creator_name ?? 'আইডিয়া প্রকাশন কর্তৃপক্ষ');
    $creatorDesignation = !empty($settings['default_creator_designation']) ? $settings['default_creator_designation'] : ($invoice->creator_designation ?? 'বিল প্রস্তুতকারী / হিসাব কর্মকর্তা');

    $recipientNameSize = $settings['challan_recipient_name_size'] ?? '13px';
    $recipientPhoneSize = $settings['challan_recipient_phone_size'] ?? '12px';
    $recipientAddressSize = $settings['challan_recipient_address_size'] ?? '11.5px';
    $recipientDesigSize = $settings['challan_recipient_desig_size'] ?? '11.5px';
    $recipientOrgSize = $settings['challan_recipient_org_size'] ?? '12px';

    $invoiceUrl = $invoice->public_url;
    $qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=130x130&margin=4&data=" . urlencode($invoiceUrl);

    $totalQuantity = 0;
    foreach($invoice->items ?? [] as $it) {
        $totalQuantity += (float)($it['quantity'] ?? 1);
    }

    $bookIds = collect($invoice->items ?? [])->pluck('book_id')->filter()->unique()->toArray();
    $bookTitles = collect($invoice->items ?? [])->pluck('title')->filter()->unique()->toArray();
    $matchedBooks = \Modules\Book\Models\Book::whereIn('id', $bookIds)
        ->orWhereIn('title', $bookTitles)
        ->get()
        ->keyBy('id');
    $matchedBooksByTitle = $matchedBooks->keyBy('title');
@endphp

{{-- Top Action Bar for Customer --}}
<header class="top-action-bar py-2.5 px-3 px-md-4 d-print-none">
    <div class="container-fluid d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div class="d-flex align-items-center gap-2">
            <a href="{{ url('/') }}" class="text-decoration-none d-flex align-items-center gap-2 text-dark">
                <img src="{{ $logoSrc }}" alt="Logo" style="height: 32px; width: 64px; object-fit: contain;">
                <span class="fw-bold fs-6 d-none d-sm-inline">{{ $settings['business_name'] ?? 'আইডিয়া প্রকাশন' }}</span>
            </a>
            <span class="badge bg-primary-subtle text-primary border ms-2">
                {{ $invoice->type_label }} #{{ $invoice->invoice_no }}
            </span>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2">
            @if($invoice->type === 'invoice')
                <div class="btn-group btn-group-sm">
                    <button type="button" class="btn btn-outline-primary active" id="btnShowBoth" onclick="setViewMode('both')">
                        <i class="fas fa-file-lines me-1"></i>উভয় পেজ
                    </button>
                    <button type="button" class="btn btn-outline-primary" id="btnShowBill" onclick="setViewMode('bill')">
                        <i class="fas fa-receipt me-1"></i>বিল / মেমো
                    </button>
                    <button type="button" class="btn btn-outline-primary" id="btnShowChallan" onclick="setViewMode('challan')">
                        <i class="fas fa-truck me-1"></i>চালান
                    </button>
                </div>
            @endif

            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="copyInvoiceLink()" id="btnCopyLink">
                <i class="fas fa-copy me-1"></i>লিংক কপি
            </button>

            <button type="button" class="btn btn-primary btn-sm fw-semibold shadow-sm" onclick="window.print()">
                <i class="fas fa-print me-1"></i> প্রিন্ট / PDF ডাউনলোড
            </button>
        </div>
    </div>
</header>

<main class="container py-4">
    <div class="row justify-content-center" id="invoicePrintWrapper">
        <div class="col-lg-10">

            {{-- ========================================================================= --}}
            {{-- PAGE 1: CASH MEMO / INVOICE (or Quotation/Tender)                         --}}
            {{-- ========================================================================= --}}
            <div class="card border shadow-xs rounded-3 p-3 p-md-4 bg-white mb-3 invoice-page-card" id="pageBillMemo">
                
                {{-- Institutional / Company Header in 2-Column Single Row (No Wrapping) --}}
                <div class="row align-items-center border-bottom pb-2 mb-2 g-2">
                    <div class="col-7">
                        <div class="d-flex align-items-center gap-3 invoice-brand-header">
                            <img src="{{ $logoSrc }}" alt="{{ $settings['business_name'] ?? 'আইডিয়া প্রকাশন' }}" 
                                 class="img-fluid invoice-logo-img" style="height: 48px; width: 96px; aspect-ratio: 2/1; object-fit: contain; flex-shrink: 0; margin-right: 4px;">
                            <div class="d-flex flex-column justify-content-center" style="line-height: 1.3; padding-left: 2px;">
                                <div class="fw-bold text-primary invoice-brand-name" style="font-size: 15.5px; margin-bottom: 2px;">{{ $settings['business_name'] ?? 'আইডিয়া প্রকাশন' }}</div>
                                <div class="text-muted invoice-tagline" style="font-size: 10px; margin-bottom: 2px;">{{ $settings['tagline'] ?? 'বই প্রকাশনা, মুদ্রণ ও পরিবেশনা' }}</div>
                                <div class="text-muted invoice-contact-info" style="font-size: 9.5px; line-height: 1.35;">
                                    <span><i class="fas fa-location-dot me-0.5 text-danger"></i>{{ $settings['address'] ?? 'ঢাকা, বাংলাদেশ' }}</span>
                                    <span class="mx-1 text-muted">·</span>
                                    <span><i class="fas fa-phone me-0.5 text-primary"></i>{{ $settings['phone'] ?? '018XXXXXXXX' }}</span>
                                    <span class="mx-1 text-muted">·</span>
                                    <span><i class="fas fa-envelope me-0.5 text-primary"></i>{{ $settings['email'] ?? 'info@ideaabd.com' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-5 text-end">
                        @php
                            $badgeStyles = [
                                'challan'   => 'background-color: #e0f2fe; color: #0369a1; border-color: #7dd3fc;',
                                'quotation' => 'background-color: #fef3c7; color: #b45309; border-color: #fcd34d;',
                                'tender'    => 'background-color: #f3e8ff; color: #7e22ce; border-color: #d8b4fe;',
                                'invoice'   => 'background-color: #dcfce7; color: #15803d; border-color: #86efac;',
                            ];
                            $badgeTitles = [
                                'challan'   => 'ডেলিভারি চালান (DELIVERY CHALLAN)',
                                'quotation' => 'মূল্য কোটেশন (PRICE QUOTATION)',
                                'tender'    => 'দরপত্র প্রস্তাবনা (TENDER PROPOSAL)',
                                'invoice'   => 'ক্যাশ মেমো / বিল (INVOICE / BILL)',
                            ];
                            $computerGeneratedLabels = [
                                'challan'   => 'কম্পিউটার জেনারেটেড ডেলিভারি চালান',
                                'quotation' => 'কম্পিউটার জেনারেটেড কোটেশন',
                                'tender'    => 'কম্পিউটার জেনারেটেড দরপত্র',
                                'invoice'   => 'কম্পিউটার জেনারেট বিল',
                            ];
                        @endphp
                        <span class="badge border px-2 py-0.5 rounded-pill mb-0.5 d-inline-block fw-bold" style="font-size: 10px; {{ $badgeStyles[$invoice->type] ?? $badgeStyles['invoice'] }}">
                            {{ $badgeTitles[$invoice->type] ?? 'বিল / ক্যাশ মেমো' }}
                        </span>
                        <div class="fw-bold text-dark mb-0 font-monospace invoice-no-text" style="font-size: 13pt; line-height: 1.2;">#{{ $invoice->invoice_no }}</div>
                        
                        <div class="text-muted fw-semibold" style="font-size: 9.5px; line-height: 1.2;">
                            <i class="fas fa-desktop me-1"></i>{{ $computerGeneratedLabels[$invoice->type] ?? 'কম্পিউটার জেনারেট বিল' }}
                            · তারিখ: <strong>@bnDate($invoice->invoice_date)</strong>
                        </div>
                        @if($invoice->valid_until)
                            <div class="text-danger fw-semibold" style="font-size: 9px;"><i class="fas fa-hourglass-half me-0.5"></i>মেয়াদ: @bnDate($invoice->valid_until)</div>
                        @endif
                    </div>
                </div>

                {{-- Subject and Tender Reference (for Tender & Quotation) --}}
                @if($invoice->subject || $invoice->reference_no)
                    <div class="p-1.5 bg-light rounded-2 border mb-2" style="font-size: 10px;">
                        @if($invoice->reference_no)
                            <div class="text-muted mb-0.5">
                                <strong class="text-dark">দরপত্র / স্মারক নং:</strong> <span class="font-monospace fw-bold text-dark">{{ $invoice->reference_no }}</span>
                            </div>
                        @endif
                        @if($invoice->subject)
                            <div>
                                <strong class="text-dark">বিষয়:</strong> <span class="fw-bold text-primary">{{ $invoice->subject }}</span>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- Customer & Billed To Info (Font 12 structured format) --}}
                <div class="p-2.5 bg-light rounded-2 border mb-2.5 destination-box" style="font-size: 12px; box-sizing: border-box;">
                    <div class="row g-2 align-items-start m-0">
                        <div class="col-7 p-0 pe-2">
                            <div class="fw-bold text-dark mb-1" style="font-size: 12px;"><i class="fas fa-user-tag me-1 text-primary"></i>প্রাপক:</div>
                            <table class="table-borderless p-0 m-0 w-100" style="font-size: 12px; line-height: 1.45;">
                                @if($invoice->customer_name)
                                    <tr>
                                        <td class="text-muted pe-1 text-nowrap" style="width: 105px; vertical-align: top; font-size: 11px;">প্রাপক নাম:</td>
                                        <td class="fw-bold text-dark" style="font-size: {{ $recipientNameSize }};">{{ $invoice->customer_name }}</td>
                                    </tr>
                                @endif
                                @if(!empty($invoice->customer_designation))
                                    <tr>
                                        <td class="text-muted pe-1 text-nowrap" style="vertical-align: top; font-size: 11px;">পদবী:</td>
                                        <td class="fw-semibold text-dark" style="font-size: {{ $recipientDesigSize }};">{{ $invoice->customer_designation }}</td>
                                    </tr>
                                @endif
                                @if($invoice->customer_org)
                                    <tr>
                                        <td class="text-muted pe-1 text-nowrap" style="vertical-align: top; font-size: 11px;">প্রতিষ্ঠানের নাম:</td>
                                        <td class="fw-semibold text-primary" style="font-size: {{ $recipientOrgSize }};">{{ $invoice->customer_org }}</td>
                                    </tr>
                                @endif
                                @if($invoice->customer_address)
                                    <tr>
                                        <td class="text-muted pe-1 text-nowrap" style="vertical-align: top; font-size: 11px;">ঠিকানা:</td>
                                        <td class="text-dark" style="font-size: {{ $recipientAddressSize }}; line-height: 1.35;">{{ $invoice->customer_address }}</td>
                                    </tr>
                                @endif
                                @if($invoice->customer_phone)
                                    <tr>
                                        <td class="text-muted pe-1 text-nowrap" style="vertical-align: top; font-size: 11px;">মোবাইল:</td>
                                        <td class="text-dark fw-bold font-monospace" style="font-size: {{ $recipientPhoneSize }};">{{ $invoice->customer_phone }}</td>
                                    </tr>
                                @endif
                            </table>
                        </div>
                        <div class="col-5 p-0 ps-2 text-end">
                            <div class="text-muted text-uppercase fw-semibold mb-1" style="font-size: 11px;">অর্ডার ও পেমেন্ট বিবরণ:</div>
                            <div style="font-size: 12px; line-height: 1.5;">
                                <div>ধরন: <strong>{{ $invoice->type_label }}</strong> · মাধ্যম: <strong>{{ $invoice->payment_method ?? 'ক্যাশ / ব্যাংক' }}</strong></div>
                                @if(in_array($invoice->type, ['invoice', 'challan']))
                                <div>
                                    স্ট্যাটাস: 
                                    @if($invoice->payment_status === 'paid')
                                        <span class="badge bg-success-subtle text-success border px-2 py-0.5" style="font-size: 10.5px;">পরিশোধিত</span>
                                    @elseif($invoice->payment_status === 'partial')
                                        <span class="badge bg-warning-subtle text-dark border px-2 py-0.5" style="font-size: 10.5px;">আংশিক বকেয়া</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger border px-2 py-0.5" style="font-size: 10.5px;">বকেয়া</span>
                                    @endif
                                </div>
                            @else
                                <div>প্রস্তাবনা স্ট্যাটাস: <span class="badge bg-primary-subtle text-primary border px-2 py-0.5" style="font-size: 10.5px;">প্রস্তাবিত</span></div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Items / Price Schedule Table (Compact for 25-30 items per A4 page) --}}
                <div class="table-responsive mb-2">
                    <table class="table table-bordered table-sm align-middle invoice-table mb-0" style="font-size: 10px;">
                        <thead class="table-light">
                            <tr class="text-muted text-uppercase" style="font-size: 9px;">
                                <th class="text-center py-1 px-1" style="width: 26px;">#</th>
                                <th class="py-1 px-1.5">
                                    @if($invoice->sales_category === 'stationery')
                                        পণ্যের নাম ও বিবরণ (Item Title & Description)
                                    @elseif($invoice->sales_category === 'printing_goods')
                                        কাজের নাম ও প্রিন্টিং বিবরণ (Job / Printing Description)
                                    @elseif($invoice->sales_category === 'other')
                                        মালের বিবরণ ও বিবরণী (Description)
                                    @else
                                        বইয়ের নাম ও বিবরণ (Book Title & Description)
                                    @endif
                                </th>
                                <th class="py-1 px-1" style="width: 105px;">
                                    @if($invoice->sales_category === 'stationery' || $invoice->sales_category === 'printing_goods')
                                        স্পেক / সাইজ
                                    @elseif($invoice->sales_category === 'other')
                                        স্পেসিফিকেশন
                                    @else
                                        লেখক (Author)
                                    @endif
                                </th>
                                <th class="text-center py-1 px-1" style="width: 45px;">পরিমাণ</th>
                                <th class="text-end py-1 px-1" style="width: 70px;">
                                    @if($invoice->sales_category === 'stationery')
                                        MRP (৳)
                                    @elseif($invoice->sales_category === 'printing_goods')
                                        বেসিক রেট
                                    @else
                                        গায়ের মূল্য
                                    @endif
                                </th>
                                <th class="text-center py-1 px-1" style="width: 55px;">কমিশন %</th>
                                <th class="text-end py-1 px-1" style="width: 80px;">বিক্রয় দর</th>
                                <th class="text-end py-1 pe-1.5" style="width: 85px;">মোট টাকা (৳)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoice->items as $idx => $item)
                                @php
                                    $matchedBook = (!empty($item['book_id']) && isset($matchedBooks[$item['book_id']]))
                                        ? $matchedBooks[$item['book_id']]
                                        : ($matchedBooksByTitle[$item['title']] ?? null);
                                    
                                    $authorName = $item['author'] ?? $item['author_name'] ?? ($matchedBook->author_name ?? ($matchedBook->author->name ?? null)) ?? '—';
                                    
                                    $qty = (float)($item['quantity'] ?? 1);
                                    $netUnitPrice = (float)($item['unit_price'] ?? 0);
                                    
                                    $coverPrice = (float)($item['cover_price'] ?? $item['regular_price'] ?? $item['original_price'] ?? ($matchedBook->price ?? $netUnitPrice));
                                    if ($coverPrice <= 0) {
                                        $coverPrice = $netUnitPrice;
                                    }

                                    if (isset($item['discount_percent']) && is_numeric($item['discount_percent'])) {
                                        $commPercent = (float)$item['discount_percent'];
                                    } elseif (isset($item['commission']) && is_numeric($item['commission'])) {
                                        $commPercent = (float)$item['commission'];
                                    } elseif ($coverPrice > 0 && $coverPrice > $netUnitPrice) {
                                        $commPercent = round((($coverPrice - $netUnitPrice) / $coverPrice) * 100, 1);
                                    } else {
                                        $commPercent = 0;
                                    }

                                    $lineSubtotal = (float)($item['subtotal'] ?? ($qty * $netUnitPrice));
                                @endphp
                                <tr>
                                    <td class="text-center py-0.5 px-1 text-muted">@bn($idx + 1)</td>
                                    <td class="py-0.5 px-1.5">
                                        <span class="fw-semibold text-dark" style="white-space: pre-line; line-height: 1.35; display: inline-block;">{!! nl2br(e($item['title'] ?? '—')) !!}</span>
                                        @if(!empty($item['item_type']) && !str_starts_with($item['item_type'], 'Book'))
                                            <span class="badge bg-light text-dark border px-1 py-0 ms-1" style="font-size: 8px;">{{ $item['item_type'] }}</span>
                                        @endif
                                    </td>
                                    <td class="py-0.5 px-1 text-muted" style="font-size: 9.5px;">{{ $authorName }}</td>
                                    <td class="text-center py-0.5 px-1 fw-bold">@bn($qty)</td>
                                    <td class="text-end py-0.5 px-1">@taka($coverPrice)</td>
                                    <td class="text-center py-0.5 px-1">
                                        @if($commPercent > 0)
                                            <span class="badge bg-danger-subtle text-danger border px-1 py-0" style="font-size: 8.5px;">@bn($commPercent)%</span>
                                        @else
                                            <span class="text-muted" style="font-size: 8.5px;">—</span>
                                        @endif
                                    </td>
                                    <td class="text-end py-0.5 px-1 fw-semibold text-dark">@taka($netUnitPrice)</td>
                                    <td class="text-end py-0.5 pe-1.5 fw-bold text-dark">@taka($lineSubtotal)</td>
                                </tr>
                            @endforeach
                        </tbody>
                        @php
                            $specialCommPercent = ($invoice->subtotal > 0 && $invoice->discount > 0)
                                ? round(($invoice->discount / $invoice->subtotal) * 100, 1)
                                : 0;

                            $tfootRows = 3; // মোট টাকা + বিশেষ কমিশন + সর্বমোট বিল
                            if ($invoice->tax > 0) $tfootRows++;
                            if (in_array($invoice->type, ['invoice', 'challan'])) {
                                $tfootRows++; // পরিশোধিত
                                if ($invoice->due_amount > 0) $tfootRows++; // অবশিষ্ট বকেয়া
                            }
                        @endphp
                        <tfoot>
                            <tr>
                                <td colspan="6" rowspan="{{ $tfootRows }}" class="py-2 px-2.5 border bg-light bg-opacity-25" style="vertical-align: middle;">
                                    <div class="p-1">
                                        <span class="text-muted fw-bold d-block mb-1" style="font-size: 9.5px;">
                                            <i class="fas fa-coins me-1 text-primary"></i>Total in Words:
                                        </span>
                                        <div class="fw-bold text-dark text-wrap" style="font-size: 11.5px; line-height: 1.45;">
                                            @takaInWordsEn($invoice->grand_total)
                                        </div>
                                    </div>
                                </td>
                                <td class="text-end py-0.5 px-1.5 fw-semibold">মোট টাকা:</td>
                                <td class="text-end py-0.5 pe-1.5 fw-semibold">@taka($invoice->subtotal)</td>
                            </tr>
                            <tr>
                                <td class="text-end py-0.5 px-1.5 text-danger fw-semibold">
                                    বিশেষ কমিশন @if($specialCommPercent > 0)(@bn($specialCommPercent)%)@endif:
                                </td>
                                <td class="text-end py-0.5 pe-1.5 text-danger fw-semibold">
                                    {{ $invoice->discount > 0 ? '- ' . \App\Support\Bn::money($invoice->discount) : '৳০.০০' }}
                                </td>
                            </tr>
                            @if($invoice->tax > 0)
                                <tr>
                                    <td class="text-end py-0.5 px-1.5 text-muted fw-semibold">ভ্যাট / ট্যাক্স:</td>
                                    <td class="text-end py-0.5 pe-1.5 text-muted fw-semibold">+ @taka($invoice->tax)</td>
                                </tr>
                            @endif
                            <tr class="table-light">
                                <td class="text-end py-1 px-1.5 fw-bold text-dark">সর্বমোট বিল:</td>
                                <td class="text-end py-1 pe-1.5 fw-bold text-primary" style="font-size: 11.5px;">@taka($invoice->grand_total)</td>
                            </tr>
                            @if(in_array($invoice->type, ['invoice', 'challan']))
                                <tr>
                                    <td class="text-end py-0.5 px-1.5 text-success fw-bold">পরিশোধিত:</td>
                                    <td class="text-end py-0.5 pe-1.5 text-success fw-bold">@taka($invoice->paid_amount)</td>
                                </tr>
                                @if($invoice->due_amount > 0)
                                    <tr class="table-danger">
                                        <td class="text-end py-0.5 px-1.5 text-danger fw-bold">অবশিষ্ট বকেয়া:</td>
                                        <td class="text-end py-0.5 pe-1.5 text-danger fw-bold">@taka($invoice->due_amount)</td>
                                    </tr>
                                @endif
                            @endif
                        </tfoot>
                    </table>
                </div>

                {{-- Note at end right before signature --}}
                <div class="p-1.5 bg-light rounded-2 text-muted mb-3 border" style="font-size: 10px; line-height: 1.3;">
                    <strong class="text-dark"><i class="fas fa-circle-info me-1 text-primary"></i>(নোট):</strong> ১. ভ্যাট যুক্ত করা হয়নি।
                    @if($invoice->notes)
                        · {{ $invoice->notes }}
                    @endif
                    @if($invoice->terms_conditions)
                        · {{ $invoice->terms_conditions }}
                    @endif
                </div>

                {{-- Signature & QR Code Footer --}}
                <div class="invoice-footer-compact pt-2 mt-auto border-top">
                    <div class="row g-2 align-items-end text-center" style="font-size: 10px;">
                        <div class="col-4">
                            <div class="signature-box" style="margin-top: 36px;">
                                <div class="border-top border-dark pt-1 fw-semibold text-dark">
                                    গ্রাহকের স্বাক্ষর
                                </div>
                            </div>
                        </div>

                        {{-- QR Code & Verification Box --}}
                        <div class="col-4">
                            <div class="d-inline-flex align-items-center gap-1.5 px-2 py-1 rounded border bg-white shadow-xs">
                                <img src="{{ $qrCodeUrl }}" alt="QR" style="width: 34px; height: 34px; object-fit: contain;">
                                <div class="text-start" style="line-height: 1.15;">
                                    <span class="text-muted fw-semibold d-block" style="font-size: 8px;"><i class="fas fa-qrcode me-0.5"></i>স্ক্যান করে যাচাই</span>
                                    <span class="font-monospace text-dark fw-bold" style="font-size: 9px;">#{{ $invoice->invoice_no }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="col-4 text-center">
                            <div class="signature-box" style="margin-top: 24px;">
                                <div class="fw-bold text-dark" style="font-size: 11px; line-height: 1.25;">
                                    {{ $creatorName }}
                                </div>
                                <div class="text-muted fw-semibold" style="font-size: 9.5px; line-height: 1.25;">
                                    {{ $creatorDesignation }}
                                </div>
                                <div class="border-top border-dark pt-1 mt-1 fw-semibold text-dark" style="font-size: 9.5px;">
                                    অনুমোদিত স্বাক্ষরকারী / বিল প্রস্তুতকারক
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-center text-muted mt-2 d-flex justify-content-between align-items-center" style="font-size: 8.5px; line-height: 1;">
                        <span>পৃষ্ঠা ১ / {{ $invoice->type === 'invoice' ? '২ (ক্যাশ মেমো কপি)' : '১' }}</span>
                        <span>{{ $settings['business_name'] ?? 'আইডিয়া প্রকাশন' }} · কম্পিউটার জেনারেটেড বিল</span>
                        <span>আইডি: {{ $invoice->invoice_no }}</span>
                    </div>
                </div>
            </div>

            {{-- ========================================================================= --}}
            {{-- PAGE 2: DELIVERY CHALLAN (স্বয়ংক্রিয় ২য় পেজ চালান - বিলের জন্য)              --}}
            {{-- ========================================================================= --}}
            @if($invoice->type === 'invoice')
                <div class="page-break d-print-block" id="invoicePageBreak"></div>

                <div class="card border shadow-xs rounded-3 p-3 p-md-4 bg-white mb-3 invoice-page-card" id="pageChallanMemo">
                    
                    {{-- Institutional / Company Header in 2-Column Single Row (No Wrapping) --}}
                    <div class="row align-items-center border-bottom pb-2 mb-2 g-2">
                        <div class="col-7">
                            <div class="d-flex align-items-center gap-3 invoice-brand-header">
                                <img src="{{ $logoSrc }}" alt="{{ $settings['business_name'] ?? 'আইডিয়া প্রকাশন' }}" 
                                     class="img-fluid invoice-logo-img" style="height: 48px; width: 96px; aspect-ratio: 2/1; object-fit: contain; flex-shrink: 0; margin-right: 4px;">
                                <div class="d-flex flex-column justify-content-center" style="line-height: 1.3; padding-left: 2px;">
                                    <div class="fw-bold text-primary invoice-brand-name" style="font-size: 15.5px; margin-bottom: 2px;">{{ $settings['business_name'] ?? 'আইডিয়া প্রকাশন' }}</div>
                                    <div class="text-muted invoice-tagline" style="font-size: 10px; margin-bottom: 2px;">{{ $settings['tagline'] ?? 'বই প্রকাশনা, মুদ্রণ ও পরিবেশনা' }}</div>
                                    <div class="text-muted invoice-contact-info" style="font-size: 9.5px; line-height: 1.35;">
                                        <span><i class="fas fa-location-dot me-0.5 text-danger"></i>{{ $settings['address'] ?? 'ঢাকা, বাংলাদেশ' }}</span>
                                        <span class="mx-1 text-muted">·</span>
                                        <span><i class="fas fa-phone me-0.5 text-primary"></i>{{ $settings['phone'] ?? '018XXXXXXXX' }}</span>
                                        <span class="mx-1 text-muted">·</span>
                                        <span><i class="fas fa-envelope me-0.5 text-primary"></i>{{ $settings['email'] ?? 'info@ideaabd.com' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-5 text-end">
                            <span class="badge border px-2 py-0.5 rounded-pill mb-0.5 d-inline-block fw-bold" style="font-size: 10px; background-color: #e0f2fe; color: #0369a1; border-color: #7dd3fc;">
                                ডেলিভারি চালান (DELIVERY CHALLAN)
                            </span>
                            <div class="fw-bold text-dark mb-0 font-monospace invoice-no-text" style="font-size: 13pt; line-height: 1.2;">#{{ $invoice->invoice_no }}</div>
                            
                            <div class="text-muted fw-semibold" style="font-size: 9.5px; line-height: 1.2;">
                                <i class="fas fa-truck me-1"></i>কম্পিউটার জেনারেটেড চালান · তারিখ: <strong>@bnDate($invoice->invoice_date)</strong>
                            </div>
                            <div class="text-muted" style="font-size: 9px;">সম্পর্কিত বিল নং: <strong>#{{ $invoice->invoice_no }}</strong></div>
                        </div>
                    </div>

                    {{-- Delivery Destination & Client Details (Font 12 structured format) --}}
                    <div class="p-2.5 bg-light rounded-2 border mb-2.5 destination-box" style="font-size: 12px; box-sizing: border-box;">
                        <div class="row g-2 align-items-start m-0">
                            <div class="col-7 p-0 pe-2">
                                <div class="fw-bold text-dark mb-1" style="font-size: 12px;"><i class="fas fa-truck-ramp-box me-1 text-primary"></i>প্রাপক ও গন্তব্য:</div>
                                <table class="table-borderless p-0 m-0 w-100" style="line-height: 1.45;">
                                    @if($invoice->customer_name)
                                        <tr>
                                            <td class="text-muted pe-1 text-nowrap" style="width: 105px; vertical-align: top; font-size: 11px;">প্রাপক নাম:</td>
                                            <td class="fw-bold text-dark" style="font-size: {{ $recipientNameSize }};">{{ $invoice->customer_name }}</td>
                                        </tr>
                                    @endif
                                    @if(!empty($invoice->customer_designation))
                                        <tr>
                                            <td class="text-muted pe-1 text-nowrap" style="vertical-align: top; font-size: 11px;">পদবী:</td>
                                            <td class="fw-semibold text-dark" style="font-size: {{ $recipientDesigSize }};">{{ $invoice->customer_designation }}</td>
                                        </tr>
                                    @endif
                                    @if($invoice->customer_org)
                                        <tr>
                                            <td class="text-muted pe-1 text-nowrap" style="vertical-align: top; font-size: 11px;">প্রতিষ্ঠানের নাম:</td>
                                            <td class="fw-semibold text-primary" style="font-size: {{ $recipientOrgSize }};">{{ $invoice->customer_org }}</td>
                                        </tr>
                                    @endif
                                    @if($invoice->customer_address)
                                        <tr>
                                            <td class="text-muted pe-1 text-nowrap" style="vertical-align: top; font-size: 11px;">গন্তব্য ঠিকানা:</td>
                                            <td class="text-dark" style="font-size: {{ $recipientAddressSize }}; line-height: 1.35;">{{ $invoice->customer_address }}</td>
                                        </tr>
                                    @endif
                                    @if($invoice->customer_phone)
                                        <tr>
                                            <td class="text-muted pe-1 text-nowrap" style="vertical-align: top; font-size: 11px;">মোবাইল:</td>
                                            <td class="text-dark fw-bold font-monospace" style="font-size: {{ $recipientPhoneSize }};">{{ $invoice->customer_phone }}</td>
                                        </tr>
                                    @endif
                                </table>
                            </div>
                            <div class="col-5 p-0 ps-2 text-end">
                                <div class="text-muted text-uppercase fw-semibold mb-1" style="font-size: 11px;">চালান বিবরণ ও পরিবহন:</div>
                                <div style="font-size: 11.5px; line-height: 1.5;">
                                    <div>চালান অবস্থা: <span class="badge bg-info-subtle text-dark border px-2 py-0.5" style="font-size: 10.5px;">পণ্য ডেলিভারি সম্পন্ন</span></div>
                                    <div>পেমেন্ট মোড: <strong>{{ $invoice->payment_method ?? 'ক্যাশ / ব্যাংক' }}</strong></div>
                                    <div>ইস্যু তারিখ: <strong>@bnDate($invoice->invoice_date)</strong></div>
                                    <div class="text-muted">প্রেরক / প্যাকার: <strong>{{ $creatorName }}</strong></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Delivery Items Table (Compact for 25-30 items per A4 page) --}}
                    <div class="table-responsive mb-2">
                        <table class="table table-bordered table-sm align-middle invoice-table mb-0" style="font-size: 10px;">
                            <thead class="table-light">
                                <tr class="text-muted text-uppercase" style="font-size: 9px;">
                                    <th class="text-center py-1 px-1" style="width: 28px;">#</th>
                                    <th class="py-1 px-1.5">
                                        @if($invoice->sales_category === 'stationery')
                                            সরবরাহকৃত স্টেশনারী পণ্য ও বিবরণ
                                        @elseif($invoice->sales_category === 'printing_goods')
                                            সরবরাহকৃত মুদ্রণ সামগ্রী / প্রিন্টিং কাজ
                                        @elseif($invoice->sales_category === 'other')
                                            সরবরাহকৃত পণ্যের বিবরণ ও বিবরণী
                                        @else
                                            সরবরাহকৃত বইয়ের নাম ও বিবরণ
                                        @endif
                                    </th>
                                    <th class="py-1 px-1" style="width: 115px;">
                                        @if($invoice->sales_category === 'stationery' || $invoice->sales_category === 'printing_goods')
                                            স্পেক / সাইজ
                                        @elseif($invoice->sales_category === 'other')
                                            স্পেসিফিকেশন
                                        @else
                                            লেখক / সংস্করণ
                                        @endif
                                    </th>
                                    <th class="text-center py-1 px-1" style="width: 60px;">ধরন</th>
                                    <th class="text-center py-1 px-1" style="width: 55px;">পরিমাণ</th>
                                    <th class="text-center py-1 px-1" style="width: 75px;">প্যাকিং অবস্থা</th>
                                    <th class="py-1 px-1.5" style="width: 80px;">মন্তব্য</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($invoice->items as $idx => $item)
                                    @php
                                        $matchedBook = (!empty($item['book_id']) && isset($matchedBooks[$item['book_id']]))
                                            ? $matchedBooks[$item['book_id']]
                                            : ($matchedBooksByTitle[$item['title']] ?? null);
                                        $authorName = $item['author'] ?? $item['author_name'] ?? ($matchedBook->author_name ?? ($matchedBook->author->name ?? null)) ?? '—';
                                    @endphp
                                    <tr>
                                        <td class="text-center py-0.5 px-1 text-muted">@bn($idx + 1)</td>
                                        <td class="py-0.5 px-1.5">
                                            <span class="fw-semibold text-dark" style="white-space: pre-line; line-height: 1.35; display: inline-block;">{!! nl2br(e($item['title'] ?? '—')) !!}</span>
                                        </td>
                                        <td class="py-0.5 px-1 text-muted" style="font-size: 9.5px;">{{ $authorName }}</td>
                                        <td class="text-center py-0.5 px-1"><span class="badge bg-light text-dark border px-1 py-0" style="font-size: 8.5px;">{{ $item['item_type'] ?? 'বই' }}</span></td>
                                        <td class="text-center py-0.5 px-1 fw-bold text-primary">@bn($item['quantity'] ?? 1)</td>
                                        <td class="text-center py-0.5 px-1 text-muted">অক্ষত / নতুন কপি</td>
                                        <td class="py-0.5 px-1.5 text-muted">যাচাইকৃত</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="table-light">
                                    <td colspan="4" class="text-end py-1 px-1.5 fw-bold">সর্বমোট সরবরাহকৃত বই / পণ্য:</td>
                                    <td class="text-center py-1 px-1 fw-bold text-primary" style="font-size: 11px;">@bn($totalQuantity) টি</td>
                                    <td colspan="2" class="py-1 px-1.5 text-muted" style="font-size: 9px;">সম্পূর্ণ লট প্রস্তুত ও প্রেরিত</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    {{-- Challan Notes --}}
                    <div class="p-1.5 bg-light rounded-2 text-muted mb-3 border" style="font-size: 10px; line-height: 1.3;">
                        <strong class="text-dark"><i class="fas fa-circle-info me-1 text-success"></i>(নোট):</strong> ১. চালান অনুযায়ী বইয়ের সংখ্যা ও বাঁধাই বুঝে নিয়ে রসিদে স্বাক্ষর দিন।
                        @if($invoice->notes)
                            · {{ $invoice->notes }}
                        @endif
                    </div>

                    {{-- Signatures & Verification --}}
                    <div class="invoice-footer-compact pt-2 mt-auto border-top">
                        <div class="row g-2 align-items-end text-center" style="font-size: 10px;">
                            <div class="col-4">
                                <div class="signature-box" style="margin-top: 24px;">
                                    <div class="border-top border-dark pt-1 fw-semibold text-dark">
                                        গ্রাহকের স্বাক্ষর
                                    </div>
                                </div>
                            </div>

                            {{-- QR Code & Verification Box --}}
                            <div class="col-4">
                                <div class="d-inline-flex align-items-center gap-1.5 px-2 py-1 rounded border bg-white shadow-xs">
                                    <img src="{{ $qrCodeUrl }}" alt="QR" style="width: 34px; height: 34px; object-fit: contain;">
                                    <div class="text-start" style="line-height: 1.15;">
                                        <span class="text-muted fw-semibold d-block" style="font-size: 8px;"><i class="fas fa-qrcode me-0.5"></i>স্ক্যান করে যাচাই</span>
                                        <span class="font-monospace text-dark fw-bold" style="font-size: 9px;">#{{ $invoice->invoice_no }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-4 text-center">
                                <div class="signature-box" style="margin-top: 24px;">
                                    <div class="fw-bold text-dark" style="font-size: 11px; line-height: 1.25;">
                                        {{ $creatorName }}
                                    </div>
                                    <div class="text-muted fw-semibold" style="font-size: 9.5px; line-height: 1.25;">
                                        {{ $creatorDesignation }}
                                    </div>
                                    <div class="border-top border-dark pt-1 mt-1 fw-semibold text-dark" style="font-size: 9.5px;">
                                        অনুমোদিত স্বাক্ষরকারী / বিল প্রস্তুতকারক
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-center text-muted mt-2 d-flex justify-content-between align-items-center" style="font-size: 8.5px; line-height: 1;">
                            <span>পৃষ্ঠা ২ / ২ (ডেলিভারি চালান কপি)</span>
                            <span>{{ $settings['business_name'] ?? 'আইডিয়া প্রকাশন' }} · কম্পিউটার জেনারেটেড চালান</span>
                            <span>আইডি: {{ $invoice->invoice_no }}</span>
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </div>
</main>

<script>
function setViewMode(mode) {
    const pageBill = document.getElementById('pageBillMemo');
    const pageChallan = document.getElementById('pageChallanMemo');
    const pageBreak = document.getElementById('invoicePageBreak');
    const btnBoth = document.getElementById('btnShowBoth');
    const btnBill = document.getElementById('btnShowBill');
    const btnChallan = document.getElementById('btnShowChallan');

    if (!pageBill || !pageChallan) return;

    if (btnBoth) btnBoth.classList.remove('active');
    if (btnBill) btnBill.classList.remove('active');
    if (btnChallan) btnChallan.classList.remove('active');

    if (mode === 'bill') {
        pageBill.classList.remove('d-none', 'd-print-none');
        pageChallan.classList.add('d-none', 'd-print-none');
        if (pageBreak) pageBreak.classList.add('d-none', 'd-print-none');
        if (btnBill) btnBill.classList.add('active');
    } else if (mode === 'challan') {
        pageBill.classList.add('d-none', 'd-print-none');
        pageChallan.classList.remove('d-none', 'd-print-none');
        if (pageBreak) pageBreak.classList.add('d-none', 'd-print-none');
        if (btnChallan) btnChallan.classList.add('active');
    } else {
        pageBill.classList.remove('d-none', 'd-print-none');
        pageChallan.classList.remove('d-none', 'd-print-none');
        if (pageBreak) pageBreak.classList.remove('d-none', 'd-print-none');
        if (btnBoth) btnBoth.classList.add('active');
    }
}

function copyInvoiceLink() {
    const url = window.location.href;
    navigator.clipboard.writeText(url).then(function() {
        const btn = document.getElementById('btnCopyLink');
        if (btn) {
            const original = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-check me-1 text-success"></i>কপি হয়েছে!';
            setTimeout(() => { btn.innerHTML = original; }, 2500);
        }
    }).catch(function() {
        prompt('লিংকটি কপি করুন:', url);
    });
}
</script>

</body>
</html>
