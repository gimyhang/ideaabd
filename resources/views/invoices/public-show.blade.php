<!DOCTYPE html>
<html lang="en" class="notranslate" translate="no">
<head>
    <meta charset="UTF-8">
    <meta name="google" content="notranslate">
    <meta name="googlebot" content="notranslate">
    <meta http-equiv="Content-Language" content="en">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <title>{{ ucfirst($invoice->type) }} #{{ $invoice->invoice_no }} — {{ $settings['business_name'] ?? 'Idea Publication' }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body {
            background-color: #f1f5f9;
            font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
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
            font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            font-size: 10px;
            color: #1e293b;
            min-height: 980px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding-left: 0.4in !important;
            padding-right: 0.4in !important;
            background: #ffffff;
            box-sizing: border-box !important;
        }

        .invoice-table th,
        .invoice-table td {
            padding: 5px 8px !important;
            vertical-align: middle;
            line-height: 1.35;
            font-size: 10px;
        }

        .invoice-footer-compact {
            margin-top: auto;
            min-height: 0.5in;
        }

        .signature-box {
            margin-top: 14px;
        }

        .invoice-signature-row {
            display: flex !important;
            flex-direction: row !important;
            justify-content: space-between !important;
            align-items: flex-end !important;
            flex-wrap: nowrap !important;
            width: 100% !important;
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }

        .invoice-signature-row .signature-col {
            flex-shrink: 0 !important;
            box-sizing: border-box !important;
        }

        .invoice-signature-row .customer-sign-col {
            width: 28% !important;
            max-width: 28% !important;
        }

        .invoice-signature-row .qr-col {
            width: 44% !important;
            max-width: 44% !important;
        }

        .invoice-signature-row .auth-sign-col {
            width: 28% !important;
            max-width: 28% !important;
        }

        .destination-box,
        .subject-reference-box {
            box-sizing: border-box !important;
            width: 100% !important;
        }

        .colon-table td {
            padding: 1.5px 0 !important;
            vertical-align: top;
        }

        .colon-table .colon-label {
            color: #64748b;
            white-space: nowrap;
            font-size: 11px;
            font-weight: 500;
        }

        .colon-table .colon-sep {
            width: 14px;
            text-align: center;
            font-weight: 700;
            color: #334155;
            user-select: none;
        }

        @media (min-width: 768px) {
            .border-end-md {
                border-right: 1px solid #cbd5e1 !important;
            }
            .border-bottom-md-0 {
                border-bottom: 0 !important;
            }
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
                font-family: 'Inter', system-ui, -apple-system, sans-serif !important;
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

            .col-7, .col-print-7 {
                flex: 0 0 58.333333% !important;
                width: 58.333333% !important;
                max-width: 58.333333% !important;
            }

            .col-5, .col-print-5 {
                flex: 0 0 41.666667% !important;
                width: 41.666667% !important;
                max-width: 41.666667% !important;
            }

            .col-6, .col-print-6 {
                flex: 0 0 50% !important;
                width: 50% !important;
                max-width: 50% !important;
            }

            .col-4, .col-print-4 {
                flex: 0 0 33.333333% !important;
                width: 33.333333% !important;
                max-width: 33.333333% !important;
            }

            .col-3, .col-print-3 {
                flex: 0 0 25% !important;
                width: 25% !important;
                max-width: 25% !important;
            }

            .col-2, .col-print-2 {
                flex: 0 0 16.666667% !important;
                width: 16.666667% !important;
                max-width: 16.666667% !important;
            }

            .col-12, .col-print-12 {
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

            .destination-box,
            .subject-reference-box {
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
                padding: 4px 6.5px !important;
                font-size: 9.5px !important;
                line-height: 1.25 !important;
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

            .invoice-summary-container {
                width: 100% !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
                margin-bottom: 6px !important;
            }

            .invoice-footer-compact {
                margin-top: 15px !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }

            .invoice-signature-row {
                display: flex !important;
                flex-direction: row !important;
                justify-content: space-between !important;
                align-items: flex-end !important;
                flex-wrap: nowrap !important;
                width: 100% !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }

            .invoice-signature-row .customer-sign-col {
                width: 28% !important;
                max-width: 28% !important;
            }

            .invoice-signature-row .qr-col {
                width: 44% !important;
                max-width: 44% !important;
            }

            .invoice-signature-row .auth-sign-col {
                width: 28% !important;
                max-width: 28% !important;
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

    $creatorName = !empty($settings['default_creator_name']) ? $settings['default_creator_name'] : ($invoice->creator_name ?? 'Idea Publication Authority');
    $creatorDesignation = !empty($settings['default_creator_designation']) ? $settings['default_creator_designation'] : ($invoice->creator_designation_en ?? ($invoice->creator_designation ?? 'Authorized Signatory / Billing In-Charge'));

    $recipientNameSize = $settings['challan_recipient_name_size'] ?? '13px';
    $recipientPhoneSize = $settings['challan_recipient_phone_size'] ?? '12px';
    $recipientAddressSize = $settings['challan_recipient_address_size'] ?? '11.5px';
    $recipientDesigSize = $settings['challan_recipient_desig_size'] ?? '11.5px';
    $recipientOrgSize = $settings['challan_recipient_org_size'] ?? '12px';

    $invoiceUrl = $invoice->public_url;
    $qrCodeSize = $settings['qr_code_size'] ?? '60px';
    $qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=160x160&margin=4&data=" . urlencode($invoiceUrl);
    $mfsQrSrc = !empty($settings['mfs_qr_image']) 
        ? \App\Support\SiteSetting::resolveImageUrl($settings['mfs_qr_image']) 
        : (!empty($settings['payment_qr_image']) ? \App\Support\SiteSetting::resolveImageUrl($settings['payment_qr_image']) : $qrCodeUrl);
    $bankQrSrc = !empty($settings['bank_qr_image']) 
        ? \App\Support\SiteSetting::resolveImageUrl($settings['bank_qr_image']) 
        : (!empty($settings['payment_qr_image']) ? \App\Support\SiteSetting::resolveImageUrl($settings['payment_qr_image']) : $qrCodeUrl);

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
                <span class="fw-bold fs-6 d-none d-sm-inline">{{ $settings['business_name'] ?? 'Idea Publication' }}</span>
            </a>
            <span class="badge bg-primary-subtle text-primary border ms-2">
                {{ ucfirst($invoice->type) }} #{{ $invoice->invoice_no }}
            </span>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2">
            @if($invoice->type === 'invoice')
                <div class="btn-group btn-group-sm">
                    <button type="button" class="btn btn-outline-primary active" id="btnShowBoth" onclick="setViewMode('both')">
                        <i class="fas fa-file-lines me-1"></i>Both Pages (Bill & Challan)
                    </button>
                    <button type="button" class="btn btn-outline-primary" id="btnShowBill" onclick="setViewMode('bill')">
                        <i class="fas fa-receipt me-1"></i>Page 1 (Bill)
                    </button>
                    <button type="button" class="btn btn-outline-primary" id="btnShowChallan" onclick="setViewMode('challan')">
                        <i class="fas fa-truck me-1"></i>Page 2 (Challan)
                    </button>
                </div>
            @endif

            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="copyInvoiceLink()" id="btnCopyLink">
                <i class="fas fa-copy me-1"></i>Copy Link
            </button>

            <button type="button" class="btn btn-primary btn-sm fw-semibold shadow-sm" onclick="window.print()">
                <i class="fas fa-print me-1"></i> Print / Download PDF
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
                
                {{-- Institutional / Company Header in 2-Column Responsive Layout --}}
                <div class="row align-items-center border-bottom pb-2 mb-2 g-2">
                    <div class="col-12 col-md-7 col-print-7">
                        <div class="d-flex align-items-center gap-2.5 invoice-brand-header">
                            <img src="{{ $logoSrc }}" alt="{{ $settings['business_name'] ?? 'Idea Publication' }}" 
                                 class="img-fluid invoice-logo-img" style="height: 48px; width: 96px; aspect-ratio: 2/1; object-fit: contain; flex-shrink: 0; margin-right: 4px;">
                            <div class="d-flex flex-column justify-content-center" style="line-height: 1.3; padding-left: 2px;">
                                <div class="fw-bold text-primary invoice-brand-name" style="font-size: 15.5px; margin-bottom: 2px;">{{ $settings['business_name'] ?? 'Idea Publication' }}</div>
                                <div class="text-muted invoice-tagline" style="font-size: 10px; margin-bottom: 2px;">{{ $settings['tagline'] ?? 'Book Publication, Printing & Distribution' }}</div>
                                <div class="text-muted invoice-contact-info" style="font-size: 9.5px; line-height: 1.35;">
                                    <span><i class="fas fa-location-dot me-0.5 text-danger"></i>{{ $settings['address'] ?? 'Dhaka, Bangladesh' }}</span>
                                    <span class="mx-1 text-muted">·</span>
                                    <span><i class="fas fa-phone me-0.5 text-primary"></i>{{ $settings['phone'] ?? '018XXXXXXXX' }}</span>
                                    <span class="mx-1 text-muted">·</span>
                                    <span><i class="fas fa-envelope me-0.5 text-primary"></i>{{ $settings['email'] ?? 'info@ideaabd.com' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-5 col-print-5 text-md-end text-start mt-2 mt-md-0">
                        @php
                            $badgeStyles = [
                                'challan'   => 'background-color: #e0f2fe; color: #0369a1; border-color: #7dd3fc;',
                                'quotation' => 'background-color: #fef3c7; color: #b45309; border-color: #fcd34d;',
                                'tender'    => 'background-color: #f3e8ff; color: #7e22ce; border-color: #d8b4fe;',
                                'invoice'   => 'background-color: #dcfce7; color: #15803d; border-color: #86efac;',
                            ];
                            $badgeTitles = [
                                'challan'   => 'DELIVERY CHALLAN',
                                'quotation' => 'PRICE QUOTATION',
                                'tender'    => 'TENDER PROPOSAL',
                                'invoice'   => 'INVOICE / BILL',
                            ];
                            $computerGeneratedLabels = [
                                'challan'   => 'Computer-generated delivery challan',
                                'quotation' => 'Computer-generated quotation',
                                'tender'    => 'Computer-generated tender',
                                'invoice'   => 'Computer-generated bill',
                            ];
                            $catBadge = $invoice->category_badge;
                        @endphp
                        <span class="badge border px-2 py-0.5 rounded-pill mb-0.5 d-inline-block fw-bold" style="font-size: 10px; {{ $badgeStyles[$invoice->type] ?? $badgeStyles['invoice'] }}">
                            {{ $badgeTitles[$invoice->type] ?? 'INVOICE / BILL' }}
                        </span>
                        @if($invoice->sales_category && $invoice->sales_category !== 'books')
                            <span class="badge border {{ $catBadge['bg'] }} px-2 py-0.5 rounded-pill mb-0.5 d-inline-block fw-bold ms-1" style="font-size: 10px;">
                                {{ $catBadge['label'] }}
                            </span>
                        @endif
                        <div class="fw-bold text-dark mb-0 font-monospace invoice-no-text" style="font-size: 13pt; line-height: 1.2;">#{{ $invoice->invoice_no }}</div>
                        
                        <div class="text-muted fw-semibold" style="font-size: 9.5px; line-height: 1.2;">
                            <i class="fas fa-desktop me-1"></i>{{ $computerGeneratedLabels[$invoice->type] ?? 'Computer-generated bill' }}
                            · Date: <strong>{{ $invoice->invoice_date ? $invoice->invoice_date->format('d M, Y') : '—' }}</strong>
                        </div>
                        @if($invoice->valid_until)
                            <div class="text-danger fw-semibold" style="font-size: 9px;"><i class="fas fa-hourglass-half me-0.5"></i>Valid until: {{ $invoice->valid_until->format('d M, Y') }}</div>
                        @endif
                    </div>
                </div>

                {{-- Subject and Reference (for Bill, Challan, Tender & Quotation) --}}
                @if($invoice->subject || $invoice->reference_no)
                    <div class="p-2 bg-light rounded-2 border mb-2.5 subject-reference-box destination-box" style="font-size: 11px;">
                        <table class="table-borderless p-0 m-0 w-100 colon-table" style="line-height: 1.45;">
                            @if($invoice->reference_no)
                                <tr>
                                    <td class="colon-label text-dark fw-bold" style="width: 105px;">
                                        @if($invoice->type === 'tender') Tender Ref
                                        @elseif($invoice->type === 'quotation') Quotation Ref
                                        @elseif($invoice->type === 'challan') Challan Ref
                                        @else Ref / PO No @endif
                                    </td>
                                    <td class="colon-sep">:</td>
                                    <td class="font-monospace fw-bold text-dark">{{ $invoice->reference_no }}</td>
                                </tr>
                            @endif
                            @if($invoice->subject)
                                <tr>
                                    <td class="colon-label text-dark fw-bold" style="width: 105px;">Subject</td>
                                    <td class="colon-sep">:</td>
                                    <td class="fw-bold text-primary" style="line-height: 1.35;">{{ $invoice->subject }}</td>
                                </tr>
                            @endif
                        </table>
                    </div>
                @endif

                {{-- Customer & Billed To Info (Structured Format with Vertical Colon Alignment) --}}
                <div class="p-2.5 bg-light rounded-2 border mb-2.5 destination-box" style="font-size: 12px; box-sizing: border-box;">
                    <div class="row g-2 align-items-start m-0">
                        <div class="col-12 col-md-7 col-print-7 p-0 pe-md-2 border-end-md border-bottom border-bottom-md-0 pb-2 pb-md-0 mb-2 mb-md-0">
                            <div class="fw-bold text-dark mb-1" style="font-size: 12px;"><i class="fas fa-user-tag me-1 text-primary"></i>Client / Customer Information:</div>
                            <table class="table-borderless p-0 m-0 w-100 colon-table" style="line-height: 1.45;">
                                @if($invoice->customer_name)
                                    <tr>
                                        <td class="colon-label" style="width: 95px;">Name</td>
                                        <td class="colon-sep">:</td>
                                        <td class="fw-bold text-dark" style="font-size: {{ $recipientNameSize }};">{{ $invoice->customer_name }}</td>
                                    </tr>
                                @endif
                                @if(!empty($invoice->customer_designation))
                                    <tr>
                                        <td class="colon-label" style="width: 95px;">Designation</td>
                                        <td class="colon-sep">:</td>
                                        <td class="fw-semibold text-dark" style="font-size: {{ $recipientDesigSize }};">{{ $invoice->customer_designation }}</td>
                                    </tr>
                                @endif
                                @if($invoice->customer_org)
                                    <tr>
                                        <td class="colon-label" style="width: 95px;">Organization</td>
                                        <td class="colon-sep">:</td>
                                        <td class="fw-semibold text-primary" style="font-size: {{ $recipientOrgSize }};">{{ $invoice->customer_org }}</td>
                                    </tr>
                                @endif
                                @if($invoice->customer_address)
                                    <tr>
                                        <td class="colon-label" style="width: 95px;">Address</td>
                                        <td class="colon-sep">:</td>
                                        <td class="text-dark" style="font-size: {{ $recipientAddressSize }}; line-height: 1.35;">{{ $invoice->customer_address }}</td>
                                    </tr>
                                @endif
                                @if($invoice->customer_phone)
                                    <tr>
                                        <td class="colon-label" style="width: 95px;">Phone</td>
                                        <td class="colon-sep">:</td>
                                        <td class="text-dark fw-bold font-monospace" style="font-size: {{ $recipientPhoneSize }};">{{ $invoice->customer_phone }}</td>
                                    </tr>
                                @endif
                            </table>
                        </div>
                        <div class="col-12 col-md-5 col-print-5 p-0 ps-md-2">
                            <div class="text-muted text-uppercase fw-semibold mb-1" style="font-size: 11px;"><i class="fas fa-file-invoice me-1 text-primary"></i>Order & Payment Details:</div>
                            <table class="table-borderless p-0 m-0 w-100 colon-table" style="line-height: 1.45;">
                                <tr>
                                    <td class="colon-label" style="width: 85px;">Doc Type</td>
                                    <td class="colon-sep">:</td>
                                    <td class="fw-bold text-dark">{{ ucfirst($invoice->type) }}</td>
                                </tr>
                                <tr>
                                    <td class="colon-label" style="width: 85px;">Payment</td>
                                    <td class="colon-sep">:</td>
                                    <td class="fw-semibold text-dark">{{ $invoice->payment_method ?? 'Cash / Bank' }}</td>
                                </tr>
                                <tr>
                                    <td class="colon-label" style="width: 85px;">Status</td>
                                    <td class="colon-sep">:</td>
                                    <td>
                                        @if(in_array($invoice->type, ['invoice', 'challan']))
                                            @if($invoice->payment_status === 'paid')
                                                 <span class="badge bg-success-subtle text-success border px-2 py-0.5" style="font-size: 10px;">Paid</span>
                                            @elseif($invoice->payment_status === 'partial')
                                                 <span class="badge bg-warning-subtle text-dark border px-2 py-0.5" style="font-size: 10px;">Partially Paid</span>
                                            @else
                                                 <span class="badge bg-danger-subtle text-danger border px-2 py-0.5" style="font-size: 10px;">Due</span>
                                            @endif
                                        @else
                                            <span class="badge bg-primary-subtle text-primary border px-2 py-0.5" style="font-size: 10px;">Proposed</span>
                                        @endif
                                    </td>
                                </tr>
                                @if($invoice->valid_until)
                                    <tr>
                                        <td class="colon-label text-danger" style="width: 85px;">Valid Until</td>
                                        <td class="colon-sep text-danger">:</td>
                                        <td class="text-danger fw-semibold">{{ $invoice->valid_until->format('d M, Y') }}</td>
                                    </tr>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Items / Price Schedule Table (Category-Aware Headers) --}}
                <div class="table-responsive mb-2">
                    <table class="table table-bordered table-sm align-middle invoice-table mb-0" style="font-size: 10px;">
                        <thead class="table-light">
                            <tr class="text-muted text-uppercase" style="font-size: 9px;">
                                <th class="text-center py-1 px-1" style="width: 26px;">#</th>
                                <th class="py-1 px-1.5">
                                    @if($invoice->sales_category === 'stationery')
                                        Delivered Stationery Items / Description
                                    @elseif($invoice->sales_category === 'printing_goods')
                                        Delivered Printing Goods / Press Work
                                    @elseif($invoice->sales_category === 'other')
                                        Delivered Products / Goods Description
                                    @else
                                        Book Title & Description
                                    @endif
                                </th>
                                <th class="py-1 px-1" style="width: 105px;">
                                    @if($invoice->sales_category === 'stationery' || $invoice->sales_category === 'printing_goods')
                                        Spec / Size
                                    @elseif($invoice->sales_category === 'other')
                                        Specification
                                    @else
                                        Author / Spec
                                    @endif
                                </th>
                                <th class="text-end py-1 px-1" style="width: 50px;">Qty</th>
                                <th class="text-end py-1 px-1" style="width: 70px;">
                                    @if($invoice->sales_category === 'stationery')
                                        MRP (৳)
                                    @elseif($invoice->sales_category === 'printing_goods')
                                        Base Rate
                                    @else
                                        Price (৳)
                                    @endif
                                </th>
                                <th class="text-end py-1 px-1" style="width: 55px;">Disc %</th>
                                <th class="text-end py-1 px-1" style="width: 80px;">Net Price</th>
                                <th class="text-end py-1 pe-1.5" style="width: 85px;">Total (৳)</th>
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
                                    <td class="text-center py-0.5 px-1 text-muted">{{ $idx + 1 }}</td>
                                    <td class="py-0.5 px-1.5">
                                        <span class="fw-semibold text-dark" style="white-space: pre-line; line-height: 1.35; display: inline-block;">{!! nl2br(e($item['title'] ?? '—')) !!}</span>
                                        @if(!empty($item['item_type']) && !str_starts_with($item['item_type'], 'Book'))
                                            <span class="badge bg-light text-dark border px-1 py-0 ms-1" style="font-size: 8px;">{{ $item['item_type'] }}</span>
                                        @endif
                                    </td>
                                    <td class="py-0.5 px-1 text-muted" style="font-size: 9.5px;">{{ $authorName }}</td>
                                    <td class="text-end py-0.5 px-1 fw-bold">{{ $qty }}</td>
                                    <td class="text-end py-0.5 px-1 font-monospace">৳{{ number_format($coverPrice, 2) }}</td>
                                    <td class="text-end py-0.5 px-1">
                                        @if($commPercent > 0)
                                            <span class="badge bg-danger-subtle text-danger border px-1 py-0" style="font-size: 8.5px;">{{ $commPercent }}%</span>
                                        @else
                                            <span class="text-muted" style="font-size: 8.5px;">—</span>
                                        @endif
                                    </td>
                                    <td class="text-end py-0.5 px-1 fw-semibold text-dark font-monospace">৳{{ number_format($netUnitPrice, 2) }}</td>
                                    <td class="text-end py-0.5 pe-1.5 fw-bold text-dark font-monospace">৳{{ number_format($lineSubtotal, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @php
                    $specialCommPercent = ($invoice->subtotal > 0 && $invoice->discount > 0)
                        ? round(($invoice->discount / $invoice->subtotal) * 100, 1)
                        : 0;
                @endphp

                {{-- Invoice Summary & Total in Words (Flexbox Grid) --}}
                <div class="invoice-summary-container mb-2.5">
                    <div class="row g-2 align-items-stretch">
                        {{-- Left Column: Total in Words & Payment Status --}}
                        <div class="col-12 col-md-6 col-print-6 d-flex flex-column">
                            <div class="p-2.5 bg-light bg-opacity-50 rounded-2 border h-100 d-flex flex-column justify-content-between">
                                <div>
                                    <div class="text-muted fw-bold mb-1" style="font-size: 9.5px; text-transform: uppercase; letter-spacing: 0.3px;">
                                        <i class="fas fa-coins me-1 text-primary"></i>Total in Words:
                                    </div>
                                    <div class="fw-bold text-dark text-wrap" style="font-size: 11.5px; line-height: 1.45;">
                                        @takaInWordsEn($invoice->grand_total)
                                    </div>
                                </div>

                                @if(in_array($invoice->type, ['invoice', 'challan']))
                                    <div class="mt-2 pt-2 border-top border-secondary-subtle d-flex align-items-center justify-content-between flex-wrap gap-1" style="font-size: 9.5px;">
                                        <span class="text-muted fw-semibold">
                                            <i class="fas fa-receipt me-1 text-secondary"></i>Payment Status:
                                        </span>
                                        @if($invoice->due_amount <= 0)
                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-0.5 fw-bold" style="font-size: 9px;">
                                                <i class="fas fa-circle-check me-1"></i>FULL PAID
                                            </span>
                                        @elseif($invoice->paid_amount > 0)
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-0.5 fw-bold" style="font-size: 9px;">
                                                <i class="fas fa-clock me-1"></i>PARTIAL PAID (Due: ৳{{ number_format($invoice->due_amount, 2) }})
                                            </span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-0.5 fw-bold" style="font-size: 9px;">
                                                <i class="fas fa-circle-exclamation me-1"></i>UNPAID
                                            </span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Right Column: Detailed Calculation Breakdown --}}
                        <div class="col-12 col-md-6 col-print-6 ms-auto">
                            <div class="border rounded-2 overflow-hidden bg-white">
                                <table class="table table-sm table-borderless align-middle mb-0 summary-table" style="font-size: 10px;">
                                    <tbody>
                                        <tr class="border-bottom border-light">
                                            <td class="py-1 px-2 text-muted fw-semibold">Subtotal:</td>
                                            <td class="py-1 px-2 text-end fw-semibold text-dark font-monospace">৳{{ number_format($invoice->subtotal, 2) }}</td>
                                        </tr>
                                        @if($invoice->discount > 0)
                                            <tr class="border-bottom border-light">
                                                <td class="py-1 px-2 text-danger fw-semibold">
                                                    Special Discount @if($specialCommPercent > 0)<span class="badge bg-danger-subtle text-danger border px-1 py-0" style="font-size: 8.5px;">{{ $specialCommPercent }}%</span>@endif:
                                                </td>
                                                <td class="py-1 px-2 text-end text-danger fw-semibold font-monospace">- ৳{{ number_format($invoice->discount, 2) }}</td>
                                            </tr>
                                        @endif
                                        @if(($invoice->previous_due ?? 0) > 0)
                                            <tr class="border-bottom border-warning-subtle table-warning bg-warning bg-opacity-10">
                                                <td class="py-1 px-2 text-dark fw-bold">Previous Due:</td>
                                                <td class="py-1 px-2 text-end text-dark fw-bold font-monospace">+ ৳{{ number_format($invoice->previous_due, 2) }}</td>
                                            </tr>
                                        @endif
                                        @if($invoice->tax > 0)
                                            <tr class="border-bottom border-light">
                                                <td class="py-1 px-2 text-muted fw-semibold">VAT / Tax:</td>
                                                <td class="py-1 px-2 text-end text-muted fw-semibold font-monospace">+ ৳{{ number_format($invoice->tax, 2) }}</td>
                                            </tr>
                                        @endif
                                        <tr class="bg-primary bg-opacity-10 border-top border-primary-subtle">
                                            <td class="py-1.5 px-2 fw-bold text-dark" style="font-size: 11px;">Grand Total:</td>
                                            <td class="py-1.5 px-2 text-end fw-bold text-primary font-monospace" style="font-size: 11.5px;">৳{{ number_format($invoice->grand_total, 2) }}</td>
                                        </tr>
                                        @if(in_array($invoice->type, ['invoice', 'challan']))
                                            @if($invoice->paid_amount > 0)
                                                <tr class="border-top border-light">
                                                    <td class="py-1 px-2 text-success fw-bold">Amount Paid:</td>
                                                    <td class="py-1 px-2 text-end text-success fw-bold font-monospace">৳{{ number_format($invoice->paid_amount, 2) }}</td>
                                                </tr>
                                            @endif
                                            @if($invoice->due_amount > 0)
                                                <tr class="table-danger bg-danger bg-opacity-10 border-top border-danger-subtle">
                                                    <td class="py-1 px-2 text-danger fw-bold">Due Balance:</td>
                                                    <td class="py-1 px-2 text-end text-danger fw-bold font-monospace">৳{{ number_format($invoice->due_amount, 2) }}</td>
                                                </tr>
                                            @endif
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Note at end right before signature --}}
                <div class="p-1.5 bg-light rounded-2 text-muted mb-3 border" style="font-size: 10px; line-height: 1.3;">
                    <strong class="text-dark"><i class="fas fa-circle-info me-1 text-primary"></i>(Note):</strong> 1. VAT not included unless specified.
                    @if($invoice->notes)
                        · {{ $invoice->notes }}
                    @endif
                    @if($invoice->terms_conditions)
                        · {{ $invoice->terms_conditions }}
                    @endif
                </div>

                {{-- Signature, QR Code & Banking Footer (Single Line Layout) --}}
                <div class="invoice-footer-compact pt-2 mt-auto border-top">
                    <div class="invoice-signature-row d-flex justify-content-between align-items-end w-100" style="font-size: 10px;">
                        {{-- 1. Left: Customer Signature --}}
                        <div class="signature-col customer-sign-col text-center">
                            <div class="signature-box">
                                <div class="signature-line border-top border-dark pt-1 fw-semibold text-dark" style="font-size: 9px;">
                                    Customer's Signature
                                </div>
                            </div>
                        </div>

                        {{-- 2. Center: QR Codes (Scan to verify + Payment QRs) --}}
                        <div class="signature-col qr-col text-center d-flex justify-content-center align-items-end">
                            <div class="d-flex align-items-end justify-content-center gap-2 flex-nowrap">
                                {{-- Scan to Verify QR --}}
                                <a href="{{ $invoiceUrl }}" target="_blank" class="text-decoration-none d-inline-flex flex-column align-items-center">
                                    <div class="p-1 rounded border bg-white shadow-2xs d-inline-block">
                                        <img src="{{ $qrCodeUrl }}" alt="Verify QR" style="width: {{ $qrCodeSize }}; height: {{ $qrCodeSize }}; object-fit: contain; display: block;">
                                    </div>
                                    <div class="text-primary fw-bold text-nowrap mt-0.5" style="font-size: 7.5px; line-height: 1.1;">
                                        Scan: #{{ $invoice->invoice_no }}
                                    </div>
                                </a>

                                @if($invoice->type === 'invoice')
                                    {{-- bKash / Nagad / Rocket QR --}}
                                    @if(!empty($settings['mfs_qr_image']))
                                        <div class="d-inline-flex flex-column align-items-center">
                                            <div class="p-1 rounded border bg-white shadow-2xs d-inline-block">
                                                <img src="{{ $mfsQrSrc }}" alt="MFS QR" style="width: {{ $qrCodeSize }}; height: {{ $qrCodeSize }}; object-fit: contain; display: block;">
                                            </div>
                                            <div class="text-dark fw-bold text-nowrap mt-0.5 font-monospace" style="font-size: 7.5px; line-height: 1.1;">
                                                {{ $settings['mfs_qr_note'] ?? 'bkash/nagad' }}
                                            </div>
                                        </div>
                                    @endif

                                    {{-- Bank Payment QR --}}
                                    @if(!empty($settings['bank_qr_image']))
                                        <div class="d-inline-flex flex-column align-items-center">
                                            <div class="p-1 rounded border bg-white shadow-2xs d-inline-block">
                                                <img src="{{ $bankQrSrc }}" alt="Bank QR" style="width: {{ $qrCodeSize }}; height: {{ $qrCodeSize }}; object-fit: contain; display: block;">
                                            </div>
                                            <div class="text-dark fw-bold text-nowrap mt-0.5 font-monospace" style="font-size: 7.5px; line-height: 1.1;">
                                                {{ $settings['bank_qr_note'] ?? 'bank payment' }}
                                            </div>
                                        </div>
                                    @endif
                                @endif
                            </div>
                        </div>

                        {{-- 3. Right: Authorized Signature --}}
                        <div class="signature-col auth-sign-col text-center">
                            <div class="signature-box">
                                <div class="fw-bold text-dark" style="font-size: 10.5px; line-height: 1.25; white-space: normal;">
                                    {{ $creatorName }}
                                </div>
                                <div class="text-muted fw-semibold" style="font-size: 9px; line-height: 1.2; white-space: normal;">
                                    {{ $creatorDesignation }}
                                </div>
                                <div class="signature-line border-top border-dark pt-1 mt-1 fw-semibold text-dark" style="font-size: 9px;">
                                    Authorized Signature
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-center text-muted mt-2 d-flex justify-content-between align-items-center" style="font-size: 8.5px; line-height: 1;">
                        <span>Page 1 / {{ $invoice->type === 'invoice' ? '2 (Invoice Copy)' : '1' }}</span>
                        <span>{{ $settings['business_name'] ?? 'Idea Publication' }} · Computer Generated Document</span>
                        <span>ID: {{ $invoice->invoice_no }}</span>
                    </div>
                </div>
            </div>

            {{-- ========================================================================= --}}
            {{-- PAGE 2: DELIVERY CHALLAN (Automatic 2nd page for Invoices)                --}}
            {{-- ========================================================================= --}}
            @if($invoice->type === 'invoice')
                <div class="page-break d-print-block" id="invoicePageBreak"></div>

                <div class="card border shadow-xs rounded-3 p-3 p-md-4 bg-white mb-3 invoice-page-card" id="pageChallanMemo">
                    
                    {{-- Institutional / Company Header in 2-Column Responsive Layout --}}
                    <div class="row align-items-center border-bottom pb-2 mb-2 g-2">
                        <div class="col-12 col-md-7 col-print-7">
                            <div class="d-flex align-items-center gap-2.5 invoice-brand-header">
                                <img src="{{ $logoSrc }}" alt="{{ $settings['business_name'] ?? 'Idea Publication' }}" 
                                     class="img-fluid invoice-logo-img" style="height: 48px; width: 96px; aspect-ratio: 2/1; object-fit: contain; flex-shrink: 0; margin-right: 4px;">
                                <div class="d-flex flex-column justify-content-center" style="line-height: 1.3; padding-left: 2px;">
                                    <div class="fw-bold text-primary invoice-brand-name" style="font-size: 15.5px; margin-bottom: 2px;">{{ $settings['business_name'] ?? 'Idea Publication' }}</div>
                                    <div class="text-muted invoice-tagline" style="font-size: 10px; margin-bottom: 2px;">{{ $settings['tagline'] ?? 'Book Publication, Printing & Distribution' }}</div>
                                    <div class="text-muted invoice-contact-info" style="font-size: 9.5px; line-height: 1.35;">
                                        <span><i class="fas fa-location-dot me-0.5 text-danger"></i>{{ $settings['address'] ?? 'Dhaka, Bangladesh' }}</span>
                                        <span class="mx-1 text-muted">·</span>
                                        <span><i class="fas fa-phone me-0.5 text-primary"></i>{{ $settings['phone'] ?? '018XXXXXXXX' }}</span>
                                        <span class="mx-1 text-muted">·</span>
                                        <span><i class="fas fa-envelope me-0.5 text-primary"></i>{{ $settings['email'] ?? 'info@ideaabd.com' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-5 col-print-5 text-md-end text-start mt-2 mt-md-0">
                            <span class="badge border px-2 py-0.5 rounded-pill mb-0.5 d-inline-block fw-bold" style="font-size: 10px; background-color: #e0f2fe; color: #0369a1; border-color: #7dd3fc;">
                                DELIVERY CHALLAN
                            </span>
                            <div class="fw-bold text-dark mb-0 font-monospace invoice-no-text" style="font-size: 13pt; line-height: 1.2;">#{{ $invoice->invoice_no }}</div>
                            
                            <div class="text-muted fw-semibold" style="font-size: 9.5px; line-height: 1.2;">
                                <i class="fas fa-truck me-1"></i>Computer-generated delivery challan · Date: <strong>{{ $invoice->invoice_date ? $invoice->invoice_date->format('d M, Y') : '—' }}</strong>
                            </div>
                            <div class="text-muted" style="font-size: 9px;">Linked Bill #: <strong>#{{ $invoice->invoice_no }}</strong></div>
                        </div>
                    </div>

                    {{-- Challan Subject and Reference --}}
                    @if($invoice->subject || $invoice->reference_no)
                        <div class="p-1.5 bg-light rounded-2 border mb-2" style="font-size: 10px;">
                            @if($invoice->reference_no)
                                <div class="text-muted mb-0.5">
                                    <strong class="text-dark">Challan Ref:</strong> 
                                    <span class="font-monospace fw-bold text-dark">{{ $invoice->reference_no }}</span>
                                </div>
                            @endif
                            @if($invoice->subject)
                                <div>
                                    <strong class="text-dark">Subject:</strong> <span class="fw-bold text-primary">{{ $invoice->subject }}</span>
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- Delivery Destination & Client Details --}}
                    <div class="p-2.5 bg-light rounded-2 border mb-2.5 destination-box" style="font-size: 12px; box-sizing: border-box;">
                        <div class="row g-2 align-items-start m-0">
                            <div class="col-12 col-md-7 col-print-7 p-0 pe-md-2 border-end-md border-bottom border-bottom-md-0 pb-2 pb-md-0 mb-2 mb-md-0">
                                <div class="fw-bold text-dark mb-1 d-flex align-items-center justify-content-between" style="font-size: 12px;">
                                    <span><i class="fas fa-truck-ramp-box me-1 text-primary"></i>Delivery Destination & Recipient:</span>
                                </div>
                                <table class="table-borderless p-0 m-0 w-100 colon-table recipient-info-table" style="line-height: 1.45;">
                                    @if($invoice->customer_name)
                                        <tr>
                                            <td class="colon-label" style="width: 95px;">Recipient</td>
                                            <td class="colon-sep">:</td>
                                            <td class="fw-bold text-dark target-recipient-name" id="challanRecipientName" style="font-size: {{ $recipientNameSize }};">{{ $invoice->customer_name }}</td>
                                        </tr>
                                    @endif
                                    @if(!empty($invoice->customer_designation))
                                        <tr>
                                            <td class="colon-label" style="width: 95px;">Designation</td>
                                            <td class="colon-sep">:</td>
                                            <td class="fw-semibold text-dark target-recipient-desig" id="challanRecipientDesig" style="font-size: {{ $recipientDesigSize }};">{{ $invoice->customer_designation }}</td>
                                        </tr>
                                    @endif
                                    @if($invoice->customer_org)
                                        <tr>
                                            <td class="colon-label" style="width: 95px;">Organization</td>
                                            <td class="colon-sep">:</td>
                                            <td class="fw-semibold text-primary target-recipient-org" id="challanRecipientOrg" style="font-size: {{ $recipientOrgSize }};">{{ $invoice->customer_org }}</td>
                                        </tr>
                                    @endif
                                    @if($invoice->customer_address)
                                        <tr>
                                            <td class="colon-label" style="width: 95px;">Address</td>
                                            <td class="colon-sep">:</td>
                                            <td class="text-dark target-recipient-address" id="challanRecipientAddr" style="font-size: {{ $recipientAddressSize }}; line-height: 1.35;">{{ $invoice->customer_address }}</td>
                                        </tr>
                                    @endif
                                    @if($invoice->customer_phone)
                                        <tr>
                                            <td class="colon-label" style="width: 95px;">Mobile</td>
                                            <td class="colon-sep">:</td>
                                            <td class="text-dark fw-bold font-monospace target-recipient-phone" id="challanRecipientPhone" style="font-size: {{ $recipientPhoneSize }};">{{ $invoice->customer_phone }}</td>
                                        </tr>
                                    @endif
                                </table>
                            </div>
                            <div class="col-12 col-md-5 col-print-5 p-0 ps-md-2">
                                <div class="text-muted text-uppercase fw-semibold mb-1" style="font-size: 11px;"><i class="fas fa-truck-fast me-1 text-primary"></i>Challan Tracking & Dispatch Info:</div>
                                <table class="table-borderless p-0 m-0 w-100 colon-table" style="line-height: 1.45;">
                                    <tr>
                                        <td class="colon-label" style="width: 90px;">Challan Type</td>
                                        <td class="colon-sep">:</td>
                                        <td class="fw-semibold text-dark">Goods Delivery</td>
                                    </tr>
                                    <tr>
                                        <td class="colon-label" style="width: 90px;">Total Items</td>
                                        <td class="colon-sep">:</td>
                                        <td class="fw-semibold text-dark">{{ count($invoice->items ?? []) }} items</td>
                                    </tr>
                                    <tr>
                                        <td class="colon-label" style="width: 90px;">Total Qty</td>
                                        <td class="colon-sep">:</td>
                                        <td class="fw-bold text-primary">{{ $totalQuantity }} pcs</td>
                                    </tr>
                                    <tr>
                                        <td class="colon-label" style="width: 90px;">Dispatcher</td>
                                        <td class="colon-sep">:</td>
                                        <td class="fw-semibold text-dark">{{ $creatorName }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- Delivery Items Table --}}
                    <div class="table-responsive mb-2">
                        <table class="table table-bordered table-sm align-middle invoice-table mb-0" style="font-size: 10px;">
                            <thead class="table-light">
                                <tr class="text-muted text-uppercase" style="font-size: 9px;">
                                    <th class="text-center py-1 px-1" style="width: 28px;">#</th>
                                    <th class="py-1 px-1.5">
                                        @if($invoice->sales_category === 'stationery')
                                            Delivered Stationery Items / Description
                                        @elseif($invoice->sales_category === 'printing_goods')
                                            Delivered Printing Goods / Press Work
                                        @elseif($invoice->sales_category === 'other')
                                            Delivered Products / Goods Description
                                        @else
                                            Delivered Book Title / Description
                                        @endif
                                    </th>
                                    <th class="py-1 px-1" style="width: 110px;">
                                        @if($invoice->sales_category === 'stationery' || $invoice->sales_category === 'printing_goods')
                                            Spec / Size
                                        @elseif($invoice->sales_category === 'other')
                                            Specification
                                        @else
                                            Author / Edition
                                        @endif
                                    </th>
                                    <th class="text-center py-1 px-1" style="width: 55px;">Type</th>
                                    <th class="text-center py-1 px-1" style="width: 45px;">Unit</th>
                                    <th class="text-end py-1 px-1" style="width: 50px;">Qty</th>
                                    <th class="text-center py-1 px-1" style="width: 70px;">Condition</th>
                                    <th class="py-1 px-1.5" style="width: 75px;">Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($invoice->items as $idx => $item)
                                    @php
                                        $matchedBook = (!empty($item['book_id']) && isset($matchedBooks[$item['book_id']]))
                                            ? $matchedBooks[$item['book_id']]
                                            : ($matchedBooksByTitle[$item['title']] ?? null);
                                        $authorName = $item['author'] ?? $item['author_name'] ?? ($matchedBook->author_name ?? ($matchedBook->author->name ?? null)) ?? '—';
                                        $unitName = $item['unit'] ?? ($invoice->sales_category === 'books' ? 'কপি' : 'পিস');
                                    @endphp
                                    <tr>
                                        <td class="text-center py-0.5 px-1 text-muted">{{ $idx + 1 }}</td>
                                        <td class="py-0.5 px-1.5">
                                            <span class="fw-semibold text-dark" style="white-space: pre-line; line-height: 1.35; display: inline-block;">{!! nl2br(e($item['title'] ?? '—')) !!}</span>
                                        </td>
                                        <td class="py-0.5 px-1 text-muted" style="font-size: 9.5px;">{{ $authorName }}</td>
                                        <td class="text-center py-0.5 px-1"><span class="badge bg-light text-dark border px-1 py-0" style="font-size: 8.5px;">{{ $item['item_type'] ?? 'Book' }}</span></td>
                                        <td class="text-center py-0.5 px-1 text-muted font-monospace" style="font-size: 8.5px;">{{ $unitName }}</td>
                                        <td class="text-end py-0.5 px-1 fw-bold text-primary">{{ $item['quantity'] ?? 1 }}</td>
                                        <td class="text-center py-0.5 px-1 text-muted">Brand New</td>
                                        <td class="py-0.5 px-1.5 text-muted">Verified</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="table-light">
                                    <td colspan="5" class="text-end py-1 px-1.5 fw-bold">Total Delivered Items / Quantity:</td>
                                    <td class="text-end py-1 px-1 fw-bold text-primary" style="font-size: 11px;">{{ $totalQuantity }}</td>
                                    <td colspan="2" class="py-1 px-1.5 text-muted" style="font-size: 9px;">Complete lot dispatched</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    {{-- Challan Notes --}}
                    <div class="p-1.5 bg-light rounded-2 text-muted mb-3 border" style="font-size: 10px; line-height: 1.3;">
                        <strong class="text-dark"><i class="fas fa-circle-info me-1 text-success"></i>(Note):</strong> 1. Please verify the quantity and binding condition before signing receipt.
                        @if($invoice->notes)
                            · {{ $invoice->notes }}
                        @endif
                    </div>

                    {{-- Challan Signatures & QR Code (Single Line Layout) --}}
                    <div class="invoice-footer-compact pt-2 mt-auto border-top">
                        <div class="invoice-signature-row d-flex justify-content-between align-items-end w-100" style="font-size: 10px;">
                            {{-- 1. Left: Recipient Signature --}}
                            <div class="signature-col customer-sign-col text-center">
                                <div class="signature-box">
                                    <div class="signature-line border-top border-dark pt-1 fw-semibold text-dark" style="font-size: 9.5px;">
                                        Recipient's Signature
                                    </div>
                                </div>
                            </div>

                            {{-- 2. Center: QR Code & Verification Box --}}
                            <div class="signature-col qr-col text-center d-flex justify-content-center align-items-end">
                                <div class="d-inline-flex align-items-center gap-1.5 px-2 py-1 rounded border bg-white shadow-xs">
                                    <img src="{{ $qrCodeUrl }}" alt="QR" style="width: {{ $qrCodeSize }}; height: {{ $qrCodeSize }}; object-fit: contain;">
                                    <div class="text-start" style="line-height: 1.15;">
                                        <span class="text-muted fw-semibold d-block" style="font-size: 8px;"><i class="fas fa-qrcode me-0.5"></i>Scan to Verify</span>
                                        <span class="font-monospace text-dark fw-bold" style="font-size: 9px;">#{{ $invoice->invoice_no }}</span>
                                    </div>
                                </div>
                            </div>

                            {{-- 3. Right: Authorized Signature --}}
                            <div class="signature-col auth-sign-col text-center">
                                <div class="signature-box">
                                    <div class="fw-bold text-dark" style="font-size: 10.5px; line-height: 1.25; white-space: normal;">
                                        {{ $creatorName }}
                                    </div>
                                    <div class="text-muted fw-semibold" style="font-size: 9px; line-height: 1.2; white-space: normal;">
                                        {{ $creatorDesignation }}
                                    </div>
                                    <div class="signature-line border-top border-dark pt-1 mt-1 fw-semibold text-dark" style="font-size: 9.5px;">
                                        Authorized Signature / Bill Creator
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-center text-muted mt-2 d-flex justify-content-between align-items-center" style="font-size: 8.5px; line-height: 1;">
                            <span>Page 2 / 2 (Delivery Challan Copy)</span>
                            <span>{{ $settings['business_name'] ?? 'Idea Publication' }} · Delivery Challan</span>
                            <span>ID: {{ $invoice->invoice_no }}</span>
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
            btn.innerHTML = '<i class="fas fa-check me-1 text-success"></i>Link Copied!';
            setTimeout(() => { btn.innerHTML = original; }, 2500);
        }
    }).catch(function() {
        prompt('Copy this link:', url);
    });
}
</script>

</body>
</html>
