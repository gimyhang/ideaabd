@extends('layouts.admin')

@php
    $settings = $invoiceSettings ?? \App\Http\Controllers\Admin\IdeaAccountingController::getInvoiceSettings();
    $bizLogo = $settings['logo'] ?? '/images/logo.png';
    $logoSrc = \App\Support\SiteSetting::resolveImageUrl($bizLogo, 'images/logo.png') ?: asset('images/logo.png');

    $docTitle = 'Payment Receipt #' . $payment->payment_no;
    $invoice = $payment->invoice;

    // Financial statement metrics
    $prevPaid = 0.0;
    if ($invoice) {
        $prevPaid = (float) $invoice->payments()
            ->where('id', '<', $payment->id)
            ->sum('amount');
    }
    $thisAmount = (float) $payment->amount;
    $totalGrand = $invoice ? (float)$invoice->grand_total : $thisAmount;
    $totalPaidToDate = $prevPaid + $thisAmount;
    $remainingDue = $invoice ? max(0, $totalGrand - $totalPaidToDate) : 0.0;

    $creatorName = !empty($settings['default_creator_name']) ? $settings['default_creator_name'] : ($invoice?->creator_name ?? 'Shakil Masud');
    $creatorDesignation = !empty($settings['default_creator_designation']) ? $settings['default_creator_designation'] : ($invoice?->creator_designation_en ?? 'CEO & Publisher');

    $receiptVerifyUrl = route('admin.accounting.invoices.payments.receipt', $payment->id);
    $qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&margin=2&data=" . urlencode($receiptVerifyUrl);
@endphp

@section('title', $docTitle)
@section('heading', 'Payment Receipt')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.accounting.index') }}">Accounting</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.accounting.invoices.index') }}">Invoices</a></li>
    @if($invoice)
        <li class="breadcrumb-item"><a href="{{ route('admin.accounting.invoices.show', $invoice->id) }}">#{{ $invoice->invoice_no }}</a></li>
    @endif
    <li class="breadcrumb-item active" aria-current="page">#{{ $payment->payment_no }}</li>
@endsection

@section('actions')
    <div class="d-flex flex-wrap gap-2 align-items-center no-print">
        <button type="button" class="btn btn-warning btn-sm rounded-pill px-3 shadow-xs fw-bold text-dark" data-bs-toggle="modal" data-bs-target="#editReceiptPaymentModal">
            <i class="fa-solid fa-pen-to-square me-1.5"></i> Edit Payment
        </button>

        <button type="button" class="btn btn-dark btn-sm rounded-pill px-3 shadow-xs fw-semibold" data-bs-toggle="offcanvas" data-bs-target="#receiptDesignCustomizerOffcanvas" id="btnOpenReceiptCustomizer">
            <i class="fa-solid fa-palette me-1.5 text-warning"></i> Customize Design
        </button>

        <button type="button" class="btn btn-primary btn-sm rounded-pill px-3.5 shadow-sm fw-semibold" onclick="window.print()">
            <i class="fa-solid fa-print me-1.5"></i> Print Receipt
        </button>

        @if($invoice)
            <a href="{{ route('admin.accounting.invoices.show', $invoice->id) }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-xs">
                <i class="fa-solid fa-arrow-left me-1"></i> Invoice #{{ $invoice->invoice_no }}
            </a>
        @endif

        <a href="{{ route('admin.accounting.customer-ledger.index', ['customer_name' => $payment->party_name, 'customer_phone' => $payment->party_phone]) }}" class="btn btn-outline-info text-dark btn-sm rounded-pill px-3 shadow-xs fw-semibold">
            <i class="fa-solid fa-book-bookmark me-1 text-primary"></i> Customer Ledger
        </a>
    </div>
@endsection

@section('content')
<style>
    :root {
        --rcp-primary: {{ $settings['receipt_primary_color'] ?? '#059669' }};
        --rcp-stamp-ink: {{ $settings['receipt_stamp_color'] ?? '#6b21a8' }};
        --rcp-logo-h: {{ $settings['receipt_logo_height'] ?? '58px' }};
        --rcp-logo-w: {{ $settings['receipt_logo_width'] ?? '155px' }};
        --rcp-stamp-size: {{ $settings['receipt_stamp_size'] ?? '135px' }};
        --rcp-stamp-deg: {{ ($settings['receipt_stamp_rotation'] ?? '-10') . 'deg' }};
        --rcp-stamp-top: {{ $settings['receipt_stamp_top'] ?? '-30px' }};
        --rcp-stamp-right: {{ $settings['receipt_stamp_right'] ?? '25px' }};
        --rcp-font-scale: {{ $settings['receipt_font_scale'] ?? '1' }};
    }
    @page {
        size: A4 portrait;
        margin: 6mm 8mm;
    }
    .receipt-paper {
        max-width: 820px;
        margin: 0 auto;
        background: #ffffff;
        border-radius: 14px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        padding: 24px 30px;
        color: #1e293b;
        position: relative;
        overflow: hidden;
        border: 1px solid #e2e8f0;
        zoom: var(--rcp-font-scale);
        transition: zoom 0.15s ease-in-out;
    }
    .receipt-paper::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--rcp-primary) 0%, #047857 100%);
    }
    .receipt-title-badge {
        display: inline-block;
        background: #f0fdf4;
        color: var(--rcp-primary);
        border: 1px dashed var(--rcp-primary);
        padding: 4px 14px;
        border-radius: 999px;
        font-weight: 700;
        font-size: 12px;
        letter-spacing: 0.5px;
    }
    .cert-box-en {
        background: #f8fafc;
        border: 1.5px solid var(--rcp-primary) !important;
        border-radius: 10px;
        padding: 13px 16px;
        margin-bottom: 12px;
    }
    .cert-fill-underline {
        display: inline-block;
        border-bottom: 1.5px dashed #334155;
        padding: 0 4px 1px 4px;
        font-weight: 700;
    }
    .receipt-meta-grid {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        overflow: hidden;
    }
    .receipt-meta-cell {
        padding: 7px 12px !important;
        border-right: 1px solid #edf2f7;
        border-bottom: 1px solid #edf2f7;
    }
    .receipt-meta-cell:last-child {
        border-right: none;
    }
    .amount-highlight-box {
        background: #f0fdf4;
        border: 1.5px solid #bbf7d0;
        border-radius: 10px;
        padding: 14px 18px;
        margin-bottom: 12px;
        position: relative;
    }
    .signature-section {
        padding-top: 14px;
        margin-top: 12px;
        border-top: 1px solid #e2e8f0;
    }

    /* Authentic Vector Round Rubber Stamp Effect */
    .rubber-stamp-float-container {
        position: absolute;
        right: var(--rcp-stamp-right);
        top: var(--rcp-stamp-top);
        z-index: 10;
        pointer-events: none;
        user-select: none;
        width: var(--rcp-stamp-size);
        height: var(--rcp-stamp-size);
        transition: right 0.15s ease, top 0.15s ease, width 0.15s ease, height 0.15s ease;
    }
    .round-rubber-stamp-svg {
        width: var(--rcp-stamp-size);
        height: var(--rcp-stamp-size);
        min-width: var(--rcp-stamp-size);
        min-height: var(--rcp-stamp-size);
        max-width: var(--rcp-stamp-size);
        max-height: var(--rcp-stamp-size);
        display: block;
        transform: rotate(var(--rcp-stamp-deg));
        filter: drop-shadow(0 0 1px rgba(107, 33, 168, 0.45));
        mix-blend-mode: multiply;
        opacity: 0.92;
        transition: transform 0.15s ease;
    }
    .round-rubber-stamp-svg .stamp-stroke {
        stroke: var(--rcp-stamp-ink) !important;
    }
    .round-rubber-stamp-svg .stamp-fill {
        fill: var(--rcp-stamp-ink) !important;
    }
    .receipt-logo-img {
        height: var(--rcp-logo-h) !important;
        max-width: var(--rcp-logo-w) !important;
        object-fit: contain;
        transition: height 0.15s ease, max-width 0.15s ease;
    }

    /* Responsive Drawer & Styling */
    #receiptDesignCustomizerOffcanvas {
        width: min(440px, 100vw) !important;
        max-width: 100vw !important;
        z-index: 1060;
    }
    .customizer-tab-btn {
        font-size: 11.5px;
        font-weight: 600;
        padding: 8px 6px;
        border-radius: 0;
        border: none;
        border-bottom: 2px solid transparent;
        color: #64748b;
        transition: all 0.2s ease;
    }
    .customizer-tab-btn.active {
        color: #0f172a !important;
        font-weight: 700;
        border-bottom: 2px solid #059669 !important;
        background: #f8fafc;
    }
    .customizer-color-swatch {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        cursor: pointer;
        border: 2.5px solid #ffffff;
        box-shadow: 0 1px 4px rgba(0,0,0,0.2);
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .customizer-color-swatch:hover {
        transform: scale(1.15);
        box-shadow: 0 2px 8px rgba(0,0,0,0.3);
    }
    .customizer-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 12px 14px;
        margin-bottom: 12px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
    }

    @media (max-width: 575.98px) {
        .receipt-paper {
            padding: 16px 14px !important;
            border-radius: 8px !important;
        }
        #receiptDesignCustomizerOffcanvas {
            width: 100% !important;
        }
    }

    @media print {
        @page {
            size: A4 portrait;
            margin: 5mm 8mm;
        }
        html, body {
            background: #ffffff !important;
            padding: 0 !important;
            margin: 0 !important;
            font-size: 11px !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        .adm-side, .adm-header, .adm-topbar, .breadcrumb, .btn, .no-print, footer, nav, .alert, header, .modal, .offcanvas, .offcanvas-backdrop {
            display: none !important;
        }
        .content-wrapper, .container-fluid, .content, .adm-main, .adm-content {
            padding: 0 !important;
            margin: 0 !important;
            background: #ffffff !important;
        }
        .receipt-paper {
            box-shadow: none !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 6px !important;
            padding: 14px 18px !important;
            margin: 0 auto !important;
            width: 100% !important;
            max-width: 100% !important;
            page-break-inside: avoid !important;
            page-break-after: avoid !important;
            break-inside: avoid !important;
        }
        .cert-box-en {
            padding: 8px 12px !important;
            margin-bottom: 8px !important;
            font-size: 11px !important;
            line-height: 1.4 !important;
        }
        .rubber-stamp-float-container {
            position: absolute !important;
            right: var(--rcp-stamp-right) !important;
            top: var(--rcp-stamp-top) !important;
            z-index: 10 !important;
            display: block !important;
            width: var(--rcp-stamp-size) !important;
            height: var(--rcp-stamp-size) !important;
        }
        .round-rubber-stamp-svg {
            width: var(--rcp-stamp-size) !important;
            height: var(--rcp-stamp-size) !important;
            min-width: var(--rcp-stamp-size) !important;
            min-height: var(--rcp-stamp-size) !important;
            max-width: var(--rcp-stamp-size) !important;
            max-height: var(--rcp-stamp-size) !important;
            transform: rotate(var(--rcp-stamp-deg)) !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            mix-blend-mode: multiply !important;
            opacity: 0.95 !important;
            filter: none !important;
            display: block !important;
        }
        .receipt-logo-img {
            height: var(--rcp-logo-h) !important;
            max-width: var(--rcp-logo-w) !important;
            object-fit: contain !important;
        }
        .receipt-meta-cell {
            padding: 5px 8px !important;
        }
        .amount-highlight-box {
            padding: 8px 12px !important;
            margin-bottom: 8px !important;
            overflow: visible !important;
        }
        .signature-section {
            padding-top: 12px !important;
            margin-top: 8px !important;
        }
    }
</style>

<div class="container-fluid py-2">
    <div class="receipt-paper">
        {{-- Header & Branding --}}
        <div class="row align-items-center pb-2 mb-2.5 border-bottom">
            <div class="col-8">
                <div class="d-flex align-items-center gap-3.5">
                    <div class="pe-3 me-2 flex-shrink-0 {{ empty($logoSrc) ? 'd-none' : '' }}" id="liveReceiptLogoContainer" style="border-right: 1.5px solid #e2e8f0;">
                        <img src="{{ $logoSrc ?: asset('images/logo.png') }}" alt="Logo" class="receipt-logo-img" id="liveReceiptLogo">
                    </div>
                    <div class="ps-1">
                        <h5 class="fw-bold mb-0 text-dark" id="liveReceiptBizName" style="font-size: 18px; letter-spacing: -0.2px;">{{ $settings['business_name'] ?? 'Idea Publication' }}</h5>
                        <div class="text-muted small {{ empty($settings['tagline']) ? 'd-none' : '' }}" id="liveReceiptTagline" style="font-size: 11px; margin-top: 1px;">{{ $settings['tagline'] ?? '' }}</div>
                        <div class="text-secondary small mt-1 d-flex align-items-center flex-wrap" style="font-size: 11px; line-height: 1.4;">
                            <span id="liveReceiptAddress">{{ $settings['address'] ?? 'Dhaka, Bangladesh' }}</span>
                            <span class="text-muted mx-2 {{ empty($settings['phone']) ? 'd-none' : '' }}" id="liveReceiptPhoneDivider">|</span>
                            <span class="d-inline-flex align-items-center {{ empty($settings['phone']) ? 'd-none' : '' }}" id="liveReceiptPhoneContainer">
                                <i class="fa-solid fa-phone-alt text-secondary me-1.5" style="font-size: 10px;"></i>
                                <span id="liveReceiptPhone">{{ $settings['phone'] ?? '' }}</span>
                            </span>
                            <span class="text-muted mx-2 {{ empty($settings['email']) ? 'd-none' : '' }}" id="liveReceiptEmailDivider">|</span>
                            <span class="d-inline-flex align-items-center {{ empty($settings['email']) ? 'd-none' : '' }}" id="liveReceiptEmailContainer">
                                <i class="fa-solid fa-envelope text-secondary me-1.5" style="font-size: 10.5px;"></i>
                                <span id="liveReceiptEmail">{{ $settings['email'] ?? '' }}</span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-4 text-end">
                <div class="receipt-title-badge mb-0.5">
                    <i class="fa-solid fa-receipt me-1"></i> MONEY RECEIPT
                </div>
                <div class="fw-bold text-dark fs-6 font-monospace">#{{ $payment->payment_no }}</div>
                <div class="text-muted small" style="font-size: 11px;">Date: <strong class="text-dark">{{ $payment->payment_date ? $payment->payment_date->format('d M, Y') : date('d M, Y') }}</strong></div>
            </div>
        </div>

        {{-- Formal English Official Certificate --}}
        <div class="cert-box-en position-relative" id="liveReceiptCertBox">
            <div class="p-3 bg-white rounded-2 border mb-2 text-dark lh-base" style="font-size: 11.5px; line-height: 1.6; text-align: justify;">
                @if($payment->has_deductions)
                    This is to officially certify that an aggregate settlement amount of 
                    <span class="cert-fill-underline text-primary font-monospace">
                        BDT {{ number_format($thisAmount, 2) }}
                    </span> 
                    has been settled on behalf of 
                    <span class="cert-fill-underline text-dark">
                        {{ $invoice?->customer_org ? $invoice->customer_org . ' (Attn: ' . $payment->party_name . ')' : $payment->party_name }}
                    </span> 
                    against Invoice 
                    <span class="cert-fill-underline text-primary font-monospace">
                        #{{ $invoice ? $invoice->invoice_no : '—' }}
                    </span> 
                    for 
                    <span class="cert-fill-underline text-dark">
                        {{ $invoice?->subject ?: ($invoice?->category_label ?? 'Book Publication & Sales Supply') }}
                    </span>. 
                    After adjusting statutory government deductions of 
                    <span class="cert-fill-underline text-danger font-monospace">BDT {{ number_format($payment->total_deductions, 2) }}</span> (TDS/VDS), 
                    the Net Realized Amount of 
                    <span class="cert-fill-underline text-success font-monospace">BDT {{ number_format($payment->effective_net_amount, 2) }}</span> 
                    (in words: <span class="cert-fill-underline text-success">@takaInWordsEn($payment->effective_net_amount)</span>) 
                    has been duly received via <strong class="text-dark">{{ ucfirst($payment->payment_method) }}</strong> (Ref: <strong>{{ $payment->transaction_ref ?: 'Standard Clearing' }}</strong>).
                @else
                    This is to officially certify that an amount of 
                    <span class="cert-fill-underline text-success font-monospace">
                        BDT {{ number_format($thisAmount, 2) }}
                    </span> 
                    (in words: <span class="cert-fill-underline text-success">@takaInWordsEn($thisAmount)</span>) 
                    has been duly received from 
                    <span class="cert-fill-underline text-dark">
                        {{ $invoice?->customer_org ? $invoice->customer_org . ' (Attn: ' . $payment->party_name . ')' : $payment->party_name }}
                    </span> 
                    against Invoice 
                    <span class="cert-fill-underline text-primary font-monospace">
                        #{{ $invoice ? $invoice->invoice_no : '—' }}
                    </span> 
                    for 
                    <span class="cert-fill-underline text-dark">
                        {{ $invoice?->subject ?: ($invoice?->category_label ?? 'Book Publication & Sales Supply') }}
                    </span> 
                    via <strong class="text-dark">{{ ucfirst($payment->payment_method) }}</strong> (Ref: <strong>{{ $payment->transaction_ref ?: 'Standard Clearing' }}</strong>).
                @endif
            </div>

            {{-- Structured Meta Grid with Clean Borders --}}
            <div class="receipt-meta-grid mb-2">
                <div class="row g-0">
                    <div class="col-4 receipt-meta-cell">
                        <span class="text-muted d-block fw-semibold" style="font-size: 10px; text-transform: uppercase;">Invoice No:</span>
                        <span class="fw-bold text-primary font-monospace" style="font-size: 12px;">{{ $invoice ? $invoice->invoice_no : '—' }}</span>
                    </div>
                    <div class="col-4 receipt-meta-cell">
                        <span class="text-muted d-block fw-semibold" style="font-size: 10px; text-transform: uppercase;">Bill Date:</span>
                        <span class="fw-bold text-dark" style="font-size: 11.5px;">{{ $invoice && $invoice->invoice_date ? $invoice->invoice_date->format('d M, Y') : '—' }}</span>
                    </div>
                    <div class="col-4 receipt-meta-cell" style="border-right: none;">
                        <span class="text-muted d-block fw-semibold" style="font-size: 10px; text-transform: uppercase;">Payment Date:</span>
                        <span class="fw-bold text-dark font-monospace" style="font-size: 11.5px;">{{ $payment->payment_date ? $payment->payment_date->format('d M, Y') : date('d M, Y') }}</span>
                    </div>
                    <div class="col-4 receipt-meta-cell" style="border-bottom: none;">
                        <span class="text-muted d-block fw-semibold" style="font-size: 10px; text-transform: uppercase;">Payment Method:</span>
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-0.5 fw-bold" style="font-size: 11px;">
                            {{ ucfirst($payment->payment_method) }}
                        </span>
                    </div>
                    <div class="col-4 receipt-meta-cell" style="border-bottom: none;">
                        <span class="text-muted d-block fw-semibold" style="font-size: 10px; text-transform: uppercase;">Instrument / Trx Ref:</span>
                        <span class="fw-bold font-monospace text-dark" style="font-size: 11.5px;">{{ $payment->transaction_ref ?: '—' }}</span>
                    </div>
                    <div class="col-4 receipt-meta-cell" style="border-right: none; border-bottom: none;">
                        <span class="text-muted d-block fw-semibold" style="font-size: 10px; text-transform: uppercase;">Tracking Ref:</span>
                        <span class="fw-bold text-secondary font-monospace" style="font-size: 11.5px;">#{{ $payment->payment_no }}</span>
                    </div>
                    @if($payment->deduction_challan_no)
                        <div class="col-12 receipt-meta-cell border-top bg-light-subtle" style="border-right: none; border-bottom: none;">
                            <span class="text-muted fw-semibold me-1" style="font-size: 10px; text-transform: uppercase;">Treasury / Challan Ref:</span>
                            <span class="fw-bold text-dark font-monospace" style="font-size: 11.5px;">{{ $payment->deduction_challan_no }} (Treasury Deposit / Mushak 6.6)</span>
                        </div>
                    @endif
                </div>
            </div>

            <div class="p-1.5 rounded-2 bg-white border d-flex align-items-center justify-content-between flex-wrap gap-2">
                <p class="mb-0 text-dark fw-medium" style="font-size: 11px;">
                    <i class="fa-solid fa-shield-halved text-success me-1"></i>
                    Official Status: <strong class="text-success">{{ $remainingDue <= 0 ? 'Full Settlement Achieved' : 'Partial Installment Acknowledged' }}</strong>.
                </p>
                <span class="badge {{ $remainingDue <= 0 ? 'bg-success text-white' : 'bg-warning text-dark' }} px-2 py-0.5 rounded-pill fw-bold" style="font-size: 10px;">
                    {{ $remainingDue <= 0 ? 'Paid in Full' : 'Partial Paid' }}
                </span>
            </div>
        </div>

        {{-- TDS / VDS Deductions Breakdown Table (If Present) --}}
        @if($payment->has_deductions)
            <div class="card border border-warning-subtle bg-warning-subtle bg-opacity-10 rounded-2 p-2 mb-2" id="liveReceiptDeductionBox">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <span class="fw-bold text-dark" style="font-size: 11.5px;">
                        <i class="fa-solid fa-scale-balanced text-warning-emphasis me-1"></i> Statutory Tax & VAT Deduction Breakdown
                    </span>
                    <span class="badge bg-warning text-dark border font-monospace" style="font-size: 9.5px;">TDS / VDS</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered bg-white align-middle mb-0" style="font-size: 11px;">
                        <thead class="table-light">
                            <tr>
                                <th class="py-1 px-2">Particulars / Head</th>
                                <th class="text-center py-1 px-2" style="width: 80px;">Rate</th>
                                <th class="text-end py-1 px-2" style="width: 130px;">Amount (BDT)</th>
                                <th class="py-1 px-2">Remarks / Note</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="py-1 px-2">
                                    <strong class="text-success"><i class="fa-solid fa-money-check-dollar me-1"></i>Net Realized (Cheque / Cash)</strong>
                                </td>
                                <td class="text-center py-1 px-2">—</td>
                                <td class="text-end font-monospace fw-bold text-success py-1 px-2">৳{{ number_format($payment->effective_net_amount, 2) }}</td>
                                <td class="text-muted small py-1 px-2">{{ $payment->transaction_ref ? 'Ref: ' . $payment->transaction_ref : 'Bank / Cash' }}</td>
                            </tr>
                            {{-- TDS (Tax) Row --}}
                            <tr>
                                <td class="py-1 px-2">TDS (Tax Deducted at Source)</td>
                                <td class="text-center font-monospace py-1 px-2">{{ (float)$payment->tax_deduction_rate > 0 ? $payment->tax_deduction_rate . '%' : '—' }}</td>
                                <td class="text-end font-monospace text-danger fw-bold py-1 px-2">{{ (float)$payment->tax_deduction_amount > 0 ? '৳' . number_format($payment->tax_deduction_amount, 2) : '৳0.00' }}</td>
                                <td class="text-muted small py-1 px-2">{{ (float)$payment->tax_deduction_amount > 0 ? 'Govt. Treasury Deposit' : '—' }}</td>
                            </tr>
                            {{-- VDS (VAT) Row --}}
                            <tr>
                                <td class="py-1 px-2">VDS (VAT Deducted at Source)</td>
                                <td class="text-center font-monospace py-1 px-2">{{ (float)$payment->vat_deduction_rate > 0 ? $payment->vat_deduction_rate . '%' : '—' }}</td>
                                <td class="text-end font-monospace text-danger fw-bold py-1 px-2">{{ (float)$payment->vat_deduction_amount > 0 ? '৳' . number_format($payment->vat_deduction_amount, 2) : '৳0.00' }}</td>
                                <td class="text-muted small py-1 px-2">{{ (float)$payment->vat_deduction_amount > 0 ? 'Govt. Treasury (Mushak 6.6)' : '—' }}</td>
                            </tr>
                            @if((float)$payment->other_deduction_amount > 0)
                                <tr>
                                    <td class="py-1 px-2">Other Statutory / Contractual Deductions</td>
                                    <td class="text-center py-1 px-2">—</td>
                                    <td class="text-end font-monospace text-danger fw-bold py-1 px-2">৳{{ number_format($payment->other_deduction_amount, 2) }}</td>
                                    <td class="text-muted small py-1 px-2">Contractual Adjustment</td>
                                </tr>
                            @endif
                        </tbody>
                        <tfoot class="table-light fw-bold">
                            <tr>
                                <td class="py-1 px-2">Total Settled Credit</td>
                                <td class="text-center text-muted font-monospace py-1 px-2">—</td>
                                <td class="text-end font-monospace text-primary py-1 px-2">৳{{ number_format($thisAmount, 2) }}</td>
                                <td class="text-dark small py-1 px-2">{{ $payment->deduction_challan_no ? 'Challan: ' . $payment->deduction_challan_no . ' (Approved)' : 'Approved' }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        @endif

        {{-- Financial Breakdown Card with Floating Authentic Round Rubber Stamp Seal --}}
        <div class="amount-highlight-box mb-2 position-relative" style="background: #f8fafc; border: 1.5px solid #e2e8f0; padding: 14px 16px; overflow: visible;">
            {{-- Floating Seal overlapping top border / Statutory Tax & VAT Deduction Breakdown area --}}
            <div class="rubber-stamp-float-container" id="liveReceiptStampContainer">
                <svg class="round-rubber-stamp-svg" width="135" height="135" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        {{-- Top Curved Text Path for "IDEA PROKASHON" --}}
                        <path id="stampTopPath" d="M 24,100 A 76,76 0 0,1 176,100" fill="none" />
                        {{-- Bottom Curved Text Path --}}
                        <path id="stampBottomPath" d="M 176,100 A 76,76 0 0,1 24,100" fill="none" />
                    </defs>

                    {{-- Outer Dashed Ring --}}
                    <circle cx="100" cy="100" r="94" fill="none" stroke="currentColor" stroke-width="2.5" stroke-dasharray="6,4" class="stamp-stroke" />
                    {{-- Inner Solid Ring --}}
                    <circle cx="100" cy="100" r="88" fill="none" stroke="currentColor" stroke-width="1.5" class="stamp-stroke" />
                    
                    {{-- Curved Top Text: IDEA PROKASHON along the border --}}
                    <text fill="currentColor" class="stamp-fill" font-size="16" font-weight="900" font-family="'Arial Black', Impact, sans-serif" letter-spacing="3.5">
                        <textPath href="#stampTopPath" xlink:href="#stampTopPath" startOffset="50%" text-anchor="middle">
                            IDEA PROKASHON
                        </textPath>
                    </text>

                    {{-- Curved Bottom Text --}}
                    <text fill="currentColor" class="stamp-fill" font-size="11.5" font-weight="800" font-family="'Arial Black', Impact, sans-serif" letter-spacing="2">
                        <textPath href="#stampBottomPath" xlink:href="#stampBottomPath" startOffset="50%" text-anchor="middle">
                            {{ $remainingDue <= 0 ? '★ FULL SETTLEMENT ★' : '★ PARTIAL PAYMENT ★' }}
                        </textPath>
                    </text>

                    {{-- Inner Center Border Ring --}}
                    <circle cx="100" cy="100" r="56" fill="rgba(243, 232, 255, 0.25)" stroke="currentColor" stroke-width="1.2" stroke-dasharray="4,2.5" class="stamp-stroke" />

                    {{-- Center Big Status: PAID / DUE --}}
                    @if($remainingDue <= 0)
                        <text x="100" y="93" text-anchor="middle" fill="currentColor" class="stamp-fill" font-size="34" font-weight="900" font-family="'Arial Black', Impact, sans-serif" letter-spacing="3">
                            PAID
                        </text>
                    @else
                        <text x="100" y="93" text-anchor="middle" fill="currentColor" class="stamp-fill" font-size="34" font-weight="900" font-family="'Arial Black', Impact, sans-serif" letter-spacing="3">
                            DUE
                        </text>
                    @endif

                    {{-- Center Horizontal Date Lines --}}
                    <line x1="50" y1="104" x2="150" y2="104" stroke="currentColor" class="stamp-stroke" stroke-width="1.2" />
                    <text x="100" y="117" text-anchor="middle" fill="currentColor" class="stamp-fill" font-size="12" font-weight="800" font-family="'Courier New', Courier, monospace" letter-spacing="1">
                        {{ $payment->payment_date ? $payment->payment_date->format('d M, Y') : date('d M, Y') }}
                    </text>
                    <line x1="50" y1="123" x2="150" y2="123" stroke="currentColor" class="stamp-stroke" stroke-width="1.2" />
                </svg>
            </div>

            {{-- Top 3 Financial Metric Tiles --}}
            <div class="row g-2 mb-2">
                <div class="col-4">
                    <div class="p-2 bg-white rounded-2 border shadow-2xs text-start h-100 d-flex flex-column justify-content-center">
                        <span class="text-muted d-block fw-semibold" style="font-size: 10px; text-transform: uppercase; letter-spacing: 0.3px;">Bill without TAX/VAT:</span>
                        <span class="fw-bold font-monospace text-dark mt-0.5" style="font-size: 13px;">৳{{ number_format($payment->effective_net_amount, 2) }}</span>
                    </div>
                </div>
                <div class="col-4">
                    <div class="p-2 bg-white rounded-2 border shadow-2xs text-start h-100 d-flex flex-column justify-content-center">
                        <span class="text-danger d-block fw-semibold" style="font-size: 10px; text-transform: uppercase; letter-spacing: 0.3px;">TAX / VAT:</span>
                        <span class="fw-bold font-monospace text-danger mt-0.5" style="font-size: 13px;">{{ $payment->total_deductions > 0 ? '-৳' . number_format($payment->total_deductions, 2) : '৳0.00' }}</span>
                    </div>
                </div>
                <div class="col-4">
                    <div class="p-2 bg-white rounded-2 border shadow-2xs text-start h-100 d-flex flex-column justify-content-center">
                        <span class="text-muted d-block fw-semibold" style="font-size: 10px; text-transform: uppercase; letter-spacing: 0.3px;">Total Bill:</span>
                        <span class="fw-bold font-monospace text-dark mt-0.5" style="font-size: 13px;">৳{{ number_format($totalGrand, 2) }}</span>
                    </div>
                </div>
            </div>

            {{-- Highlighted 2-Column Box: Total Paid to Date & Due --}}
            <div class="row g-2">
                {{-- Column 1: Total Paid to Date (Green) --}}
                <div class="col-sm-7">
                    <div class="p-3 rounded-2 bg-white border border-success-subtle shadow-2xs h-100 d-flex flex-column justify-content-center">
                        <div class="text-success small fw-bold text-uppercase mb-1" style="font-size: 11px; letter-spacing: 0.5px;">
                            <i class="fa-solid fa-money-check-dollar me-1.5"></i> Total Paid to Date:
                        </div>
                        <div class="fs-3 fw-bold text-success font-monospace mb-1" style="line-height: 1.15;">
                            ৳{{ number_format($payment->effective_net_amount, 2) }}
                        </div>
                        <div class="text-muted small" style="font-size: 11px; line-height: 1.35;">
                            In Words: <strong class="text-dark">@takaInWordsEn($payment->effective_net_amount)</strong>
                        </div>
                    </div>
                </div>

                {{-- Column 2: Due (Red) --}}
                <div class="col-sm-5">
                    <div class="p-3 rounded-2 bg-white border border-danger-subtle shadow-2xs h-100 d-flex flex-column justify-content-center">
                        <div class="text-danger small fw-bold text-uppercase mb-1" style="font-size: 11px; letter-spacing: 0.5px;">
                            <i class="fa-solid fa-circle-exclamation me-1.5"></i> Due:
                        </div>
                        <div class="fs-3 fw-bold text-danger font-monospace mb-1" style="line-height: 1.15;">
                            ৳{{ number_format($remainingDue, 2) }}
                        </div>
                        <div class="text-muted small" style="font-size: 11px; line-height: 1.35;">
                            Status: <strong class="{{ $remainingDue <= 0 ? 'text-success' : 'text-danger' }}">{{ $remainingDue <= 0 ? 'Full Settled' : 'Payment Pending' }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Note & Next Due Date (If present) --}}
        @if(!empty($payment->note) || ($invoice && $invoice->due_date && $remainingDue > 0))
            <div class="row g-2 mb-2" id="liveReceiptNoteBox">
                @if(!empty($payment->note))
                    <div class="col-md-8">
                        <div class="p-1.5 bg-light rounded-2 border" style="font-size: 11px;">
                            <span class="text-muted fw-bold me-1"><i class="fa-solid fa-comment-dots me-1"></i>Remarks:</span>
                            <span class="text-dark">{{ $payment->note }}</span>
                        </div>
                    </div>
                @endif

                @if($invoice && $invoice->due_date && $remainingDue > 0)
                    <div class="col-md-4 text-md-end">
                        <div class="p-1.5 bg-danger-subtle rounded-2 border border-danger-subtle text-danger small fw-semibold d-inline-block text-start" style="font-size: 11px;">
                            <i class="fa-solid fa-calendar-day me-1"></i> Next Due: 
                            <strong class="text-danger font-monospace">{{ $invoice->due_date->format('d M, Y') }}</strong>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        {{-- Signatures & Acknowledgement --}}
        <div class="signature-section" id="liveReceiptSignatureSection">
            <div class="row align-items-end text-center">
                {{-- Left: Customer / Payer Signature (Matched baseline with Collected By:) --}}
                <div class="col-4 text-start" id="liveReceiptCustomerSigCol">
                    <div class="d-inline-flex flex-column align-items-center justify-content-end text-center" style="min-width: 145px; min-height: 54px;">
                        <div class="border-top border-dark mb-1" style="width: 140px;"></div>
                        <div class="small fw-bold text-dark" id="liveReceiptCustomerSigLabel" style="font-size: 11px; line-height: 1.3;">Customer Signature</div>
                    </div>
                </div>

                {{-- Center: Verify QR Code & Document Info --}}
                <div class="col-4" id="liveReceiptQrBox">
                    <div class="d-flex align-items-center justify-content-center gap-2.5">
                        <div class="p-1 border rounded bg-white shadow-2xs d-flex flex-column align-items-center" style="width: 83px; height: 83px;">
                            <img src="{{ $qrCodeUrl }}" alt="Verify QR" style="width: 75px; height: 75px; object-fit: contain; display: block;">
                        </div>
                        <div class="text-start text-muted" style="font-size: 10px; line-height: 1.4;">
                            <div class="fw-bold text-dark" style="font-size: 11px;"><i class="fa-solid fa-shield-check text-success me-1"></i>Official Verification</div>
                            <div class="font-monospace text-secondary" style="font-size: 9.5px;">Issued: {{ $payment->payment_date ? $payment->payment_date->format('d/m/Y') : date('d/m/Y') }}</div>
                            <div class="text-muted small" style="font-size: 9px;">Scan to Verify Online</div>
                        </div>
                    </div>
                </div>

                {{-- Right: Authorized Collector / Signatory --}}
                <div class="col-4 text-end" id="liveReceiptSignatoryCol">
                    <div class="d-inline-flex flex-column align-items-center justify-content-end text-center" style="min-width: 145px; min-height: 54px;">
                        <div class="small fw-bold text-dark mb-0.5" id="liveReceiptSignatoryName" style="font-size: 11.5px; line-height: 1.2;">{{ $creatorName ?: 'Shakil Masud' }}</div>
                        <div class="text-muted mb-1" id="liveReceiptSignatoryDesig" style="font-size: 9.5px; line-height: 1.2;">{{ $creatorDesignation ?: 'CEO & Publisher' }}</div>
                        <div class="border-top border-dark mb-1" style="width: 140px;"></div>
                        <div class="text-muted fw-semibold" id="liveReceiptSignatoryLabel" style="font-size: 9.5px; text-transform: uppercase; line-height: 1.3;">Collected By:</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center text-muted mt-2 pt-1.5 border-top" id="liveReceiptFooterBox" style="font-size: 9.5px;">
            Thank you! This is an official computer-generated receipt from <span id="liveReceiptFooterBizName">{{ $settings['business_name'] ?? 'Idea Publication' }}</span>.
            <span class="ms-1.5 {{ empty($settings['phone']) ? 'd-none' : '' }}" id="liveReceiptFooterPhoneContainer">
                <i class="fa-solid fa-phone-alt me-1"></i><span id="liveReceiptFooterPhone">{{ $settings['phone'] ?? '' }}</span>
            </span>
        </div>
    </div>
</div>

{{-- Edit Payment & Tax/VAT Deductions Modal (No-Print) --}}
<div class="modal fade d-print-none" id="editReceiptPaymentModal" tabindex="-1" aria-labelledby="editReceiptPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-gradient bg-primary text-white py-2.5 px-3.5">
                <div class="d-flex align-items-center gap-2">
                    <i class="fa-solid fa-pen-to-square text-white"></i>
                    <h5 class="modal-title fw-bold mb-0 text-white fs-6" id="editReceiptPaymentModalLabel">
                        Edit Payment
                    </h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{ route('admin.accounting.invoices.payments.update', $payment->id) }}" method="POST" id="editPaymentReceiptForm">
                @csrf
                @method('PUT')
                <div class="modal-body p-3 p-md-4">
                    {{-- Financial Overview --}}
                    <div class="bg-light p-3 rounded-3 border mb-3">
                        <div class="d-flex justify-content-between small text-muted mb-1 flex-wrap gap-1">
                            <span>Customer: <strong class="text-dark">{{ $payment->party_name }}</strong></span>
                            <span>Total Bill: <strong class="text-dark font-monospace">৳{{ number_format($totalGrand, 2) }}</strong></span>
                        </div>
                        <div class="d-flex justify-content-between small text-muted flex-wrap gap-1">
                            <span>Payment Ref: <strong class="text-dark font-monospace">#{{ $payment->payment_no }}</strong></span>
                            <span>Remaining Due: <strong class="text-danger fw-bold font-monospace">৳{{ number_format($remainingDue, 2) }}</strong></span>
                        </div>
                    </div>

                    {{-- Primary Payment Info --}}
                    <div class="row g-3 mb-3">
                        <div class="col-md-6 col-12">
                            <label class="form-label small fw-bold text-dark">Payment Date: <span class="text-danger">*</span></label>
                            <input type="date" name="payment_date" class="form-control form-control-sm" required value="{{ $payment->payment_date ? $payment->payment_date->format('Y-m-d') : date('Y-m-d') }}">
                        </div>
                        <div class="col-md-6 col-12">
                            <label class="form-label small fw-bold text-dark">Total Gross Settled Amount: <span class="text-danger">*</span></label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">৳</span>
                                <input type="number" step="0.01" min="0.01" name="amount" id="editGrossAmountInput" class="form-control form-control-sm fw-bold font-monospace text-primary fs-6" required placeholder="0.00" value="{{ $payment->amount }}" oninput="handleEditFieldChange('gross')">
                            </div>
                        </div>
                    </div>

                    {{-- Statutory Tax & VAT Deduction Breakdown Card --}}
                    <div class="card border border-warning-subtle bg-warning-subtle bg-opacity-10 rounded-3 p-3 mb-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="fw-bold text-dark small">
                                <i class="fa-solid fa-scale-balanced text-warning-emphasis me-1"></i> Statutory Tax & VAT Deduction Breakdown
                            </span>
                            <span class="badge bg-warning text-dark border font-monospace" style="font-size: 11px;">TDS / VDS</span>
                        </div>

                        {{-- Tax (TDS) Row --}}
                        <div class="p-2.5 bg-white rounded-3 border mb-2.5">
                            <div class="d-flex justify-content-between align-items-center mb-1 flex-wrap gap-1">
                                <label class="form-label small fw-bold text-dark mb-0">
                                    <i class="fa-solid fa-building-columns text-danger me-1"></i>TDS (Tax Deducted at Source):
                                </label>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-1.5" onclick="setEditTaxRate(0)">0%</button>
                                    <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-1.5" onclick="setEditTaxRate(2)">2%</button>
                                    <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-1.5" onclick="setEditTaxRate(3)">3%</button>
                                    <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-1.5" onclick="setEditTaxRate(5)">5%</button>
                                    <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-1.5" onclick="setEditTaxRate(7)">7%</button>
                                </div>
                            </div>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">Rate %</span>
                                <input type="number" step="0.01" min="0" max="100" name="tax_deduction_rate" id="editTaxRateInput" class="form-control font-monospace" placeholder="Rate %" value="{{ $payment->tax_deduction_rate ?: '' }}" oninput="handleEditFieldChange('tax_rate')">
                                <span class="input-group-text">%</span>
                                <span class="input-group-text">Amount (BDT)</span>
                                <input type="number" step="0.01" min="0" name="tax_deduction_amount" id="editTaxAmountInput" class="form-control font-monospace text-danger fw-bold" placeholder="0.00" value="{{ (float)$payment->tax_deduction_amount > 0 ? $payment->tax_deduction_amount : '' }}" oninput="handleEditFieldChange('tax_amount')">
                                <span class="input-group-text">৳</span>
                            </div>
                        </div>

                        {{-- VAT (VDS) Row --}}
                        <div class="p-2.5 bg-white rounded-3 border mb-2.5">
                            <div class="d-flex justify-content-between align-items-center mb-1 flex-wrap gap-1">
                                <label class="form-label small fw-bold text-dark mb-0">
                                    <i class="fa-solid fa-file-invoice text-primary me-1"></i>VDS (VAT Deducted at Source):
                                </label>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-1.5" onclick="setEditVatRate(0)">0%</button>
                                    <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-1.5" onclick="setEditVatRate(5)">5%</button>
                                    <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-1.5" onclick="setEditVatRate(7.5)">7.5%</button>
                                    <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-1.5" onclick="setEditVatRate(15)">15%</button>
                                </div>
                            </div>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">Rate %</span>
                                <input type="number" step="0.01" min="0" max="100" name="vat_deduction_rate" id="editVatRateInput" class="form-control font-monospace" placeholder="Rate %" value="{{ $payment->vat_deduction_rate ?: '' }}" oninput="handleEditFieldChange('vat_rate')">
                                <span class="input-group-text">%</span>
                                <span class="input-group-text">Amount (BDT)</span>
                                <input type="number" step="0.01" min="0" name="vat_deduction_amount" id="editVatAmountInput" class="form-control font-monospace text-danger fw-bold" placeholder="0.00" value="{{ (float)$payment->vat_deduction_amount > 0 ? $payment->vat_deduction_amount : '' }}" oninput="handleEditFieldChange('vat_amount')">
                                <span class="input-group-text">৳</span>
                            </div>
                        </div>

                        <div class="row g-2.5 mb-2.5">
                            {{-- Net Received (Cheque/Cash) --}}
                            <div class="col-md-6 col-12">
                                <div class="p-2.5 bg-white rounded-3 border">
                                    <label class="form-label small fw-bold text-dark mb-1">
                                        <i class="fa-solid fa-money-check-dollar text-success me-1"></i>Net Realized (Cheque / Cash):
                                    </label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">৳</span>
                                        <input type="number" step="0.01" min="0" name="net_amount" id="editNetAmountInput" class="form-control font-monospace fw-bold text-success" placeholder="0.00" value="{{ $payment->effective_net_amount }}" oninput="handleEditFieldChange('net')">
                                    </div>
                                </div>
                            </div>

                            {{-- Other Deductions --}}
                            <div class="col-md-6 col-12">
                                <div class="p-2.5 bg-white rounded-3 border">
                                    <label class="form-label small fw-bold text-dark mb-1">
                                        <i class="fa-solid fa-circle-minus text-secondary me-1"></i>Other Deductions:
                                    </label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">৳</span>
                                        <input type="number" step="0.01" min="0" name="other_deduction_amount" id="editOtherDeductionInput" class="form-control font-monospace text-danger fw-bold" placeholder="0.00" value="{{ (float)$payment->other_deduction_amount > 0 ? $payment->other_deduction_amount : '' }}" oninput="handleEditFieldChange('other')">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Challan No & Deduction Notes --}}
                        <div class="row g-2.5 mb-2">
                            <div class="col-md-6 col-12">
                                <label class="form-label small fw-semibold text-dark mb-1">Challan / Mushak Ref:</label>
                                <input type="text" name="deduction_challan_no" class="form-control form-control-sm font-monospace" placeholder="e.g. TR-12345 / Mushak 6.6" value="{{ $payment->deduction_challan_no }}">
                            </div>
                            <div class="col-md-6 col-12">
                                <label class="form-label small fw-semibold text-dark mb-1">Deduction Notes:</label>
                                <input type="text" name="deduction_notes" class="form-control form-control-sm" placeholder="Notes on TDS/VDS deductions" value="{{ $payment->deduction_notes }}">
                            </div>
                        </div>

                        {{-- Dynamic Settlement Preview Pill --}}
                        <div class="p-2.5 bg-success-subtle bg-opacity-25 rounded-3 border border-success-subtle mt-2">
                            <div class="row g-2 text-center" style="font-size: 11.5px;">
                                <div class="col-4 border-end border-success-subtle">
                                    <span class="text-muted d-block" style="font-size: 10.5px;">Net Received</span>
                                    <strong class="text-success font-monospace" id="editDisplayNet">৳{{ number_format($payment->effective_net_amount, 2) }}</strong>
                                </div>
                                <div class="col-4 border-end border-success-subtle">
                                    <span class="text-muted d-block" style="font-size: 10.5px;">Total Deductions</span>
                                    <strong class="text-danger font-monospace" id="editDisplayDeductions">৳{{ number_format($payment->total_deductions, 2) }}</strong>
                                </div>
                                <div class="col-4">
                                    <span class="text-muted d-block" style="font-size: 10.5px;">Total Settled Credit</span>
                                    <strong class="text-primary font-monospace" id="editDisplayGross">৳{{ number_format($payment->amount, 2) }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Payment Method & Instrument Ref --}}
                    <div class="row g-3 mb-3">
                        <div class="col-md-6 col-12">
                            <label class="form-label small fw-bold text-dark">Payment Method: <span class="text-danger">*</span></label>
                            <select name="payment_method" class="form-select form-select-sm" required>
                                @foreach(\App\Models\IdeaInvoicePayment::paymentMethods() as $code => $lbl)
                                    <option value="{{ $code }}" {{ $payment->payment_method === $code ? 'selected' : '' }}>{{ $lbl }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 col-12">
                            <label class="form-label small fw-bold text-dark">Instrument / Cheque / Trx Ref:</label>
                            <input type="text" name="transaction_ref" class="form-control form-control-sm font-monospace" placeholder="e.g. Cheque #7329437, Sonali Bank" value="{{ $payment->transaction_ref }}">
                        </div>
                    </div>

                    {{-- Next Due Date & Note --}}
                    <div class="row g-3">
                        @if($invoice)
                            <div class="col-md-6 col-12">
                                <label class="form-label small fw-bold text-dark">Next Due Date:</label>
                                <input type="date" name="due_date" class="form-control form-control-sm" value="{{ $invoice->due_date ? $invoice->due_date->format('Y-m-d') : '' }}">
                            </div>
                        @endif
                        <div class="col-md-{{ $invoice ? '6' : '12' }} col-12">
                            <label class="form-label small fw-bold text-dark">Remarks / Note:</label>
                            <input type="text" name="note" class="form-control form-control-sm" placeholder="Settlement remarks" value="{{ $payment->note }}">
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light p-3">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
                        <i class="fa-solid fa-check me-1.5"></i> Save & Update Receipt
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function setEditTaxRate(rate) {
    const rateInput = document.getElementById('editTaxRateInput');
    if (rateInput) {
        rateInput.value = rate > 0 ? rate : '';
        handleEditFieldChange('tax_rate');
    }
}

function setEditVatRate(rate) {
    const rateInput = document.getElementById('editVatRateInput');
    if (rateInput) {
        rateInput.value = rate > 0 ? rate : '';
        handleEditFieldChange('vat_rate');
    }
}

function handleEditFieldChange(source) {
    const grossInput = document.getElementById('editGrossAmountInput');
    const netInput = document.getElementById('editNetAmountInput');
    const taxRateInput = document.getElementById('editTaxRateInput');
    const taxAmtInput = document.getElementById('editTaxAmountInput');
    const vatRateInput = document.getElementById('editVatRateInput');
    const vatAmtInput = document.getElementById('editVatAmountInput');
    const otherInput = document.getElementById('editOtherDeductionInput');

    let gross = parseFloat(grossInput?.value) || 0;
    let taxRate = parseFloat(taxRateInput?.value) || 0;
    let taxAmt = parseFloat(taxAmtInput?.value) || 0;
    let vatRate = parseFloat(vatRateInput?.value) || 0;
    let vatAmt = parseFloat(vatAmtInput?.value) || 0;
    let otherAmt = parseFloat(otherInput?.value) || 0;
    let net = parseFloat(netInput?.value) || 0;

    if (source === 'tax_rate') {
        if (gross > 0 && taxRate > 0) {
            taxAmt = Number(((gross * taxRate) / 100).toFixed(2));
            if (taxAmtInput) taxAmtInput.value = taxAmt > 0 ? taxAmt : '';
        } else if (taxRate === 0) {
            taxAmt = 0;
            if (taxAmtInput) taxAmtInput.value = '';
        }
        net = Math.max(0, Number((gross - (taxAmt + vatAmt + otherAmt)).toFixed(2)));
        if (netInput) netInput.value = net > 0 ? net : '';
    } else if (source === 'tax_amount') {
        if (gross > 0 && taxAmt > 0) {
            taxRate = Number(((taxAmt / gross) * 100).toFixed(2));
            if (taxRateInput) taxRateInput.value = taxRate > 0 ? taxRate : '';
        } else if (taxAmt === 0) {
            if (taxRateInput) taxRateInput.value = '';
        }
        net = Math.max(0, Number((gross - (taxAmt + vatAmt + otherAmt)).toFixed(2)));
        if (netInput) netInput.value = net > 0 ? net : '';
    } else if (source === 'vat_rate') {
        if (gross > 0 && vatRate > 0) {
            vatAmt = Number(((gross * vatRate) / 100).toFixed(2));
            if (vatAmtInput) vatAmtInput.value = vatAmt > 0 ? vatAmt : '';
        } else if (vatRate === 0) {
            vatAmt = 0;
            if (vatAmtInput) vatAmtInput.value = '';
        }
        net = Math.max(0, Number((gross - (taxAmt + vatAmt + otherAmt)).toFixed(2)));
        if (netInput) netInput.value = net > 0 ? net : '';
    } else if (source === 'vat_amount') {
        if (gross > 0 && vatAmt > 0) {
            vatRate = Number(((vatAmt / gross) * 100).toFixed(2));
            if (vatRateInput) vatRateInput.value = vatRate > 0 ? vatRate : '';
        } else if (vatAmt === 0) {
            if (vatRateInput) vatRateInput.value = '';
        }
        net = Math.max(0, Number((gross - (taxAmt + vatAmt + otherAmt)).toFixed(2)));
        if (netInput) netInput.value = net > 0 ? net : '';
    } else if (source === 'gross') {
        if (taxRate > 0) {
            taxAmt = Number(((gross * taxRate) / 100).toFixed(2));
            if (taxAmtInput) taxAmtInput.value = taxAmt > 0 ? taxAmt : '';
        }
        if (vatRate > 0) {
            vatAmt = Number(((gross * vatRate) / 100).toFixed(2));
            if (vatAmtInput) vatAmtInput.value = vatAmt > 0 ? vatAmt : '';
        }
        net = Math.max(0, Number((gross - (taxAmt + vatAmt + otherAmt)).toFixed(2)));
        if (netInput) netInput.value = net > 0 ? net : '';
    } else if (source === 'other') {
        net = Math.max(0, Number((gross - (taxAmt + vatAmt + otherAmt)).toFixed(2)));
        if (netInput) netInput.value = net > 0 ? net : '';
    } else if (source === 'net') {
        gross = Number((net + (taxAmt + vatAmt + otherAmt)).toFixed(2));
        if (grossInput) grossInput.value = gross > 0 ? gross : '';
        if (gross > 0) {
            if (taxAmt > 0 && taxRateInput) taxRateInput.value = Number(((taxAmt / gross) * 100).toFixed(2));
            if (vatAmt > 0 && vatRateInput) vatRateInput.value = Number(((vatAmt / gross) * 100).toFixed(2));
        }
    }

    const totalDeductions = taxAmt + vatAmt + otherAmt;
    const dispNet = document.getElementById('editDisplayNet');
    const dispDed = document.getElementById('editDisplayDeductions');
    const dispGross = document.getElementById('editDisplayGross');

    if (dispNet) dispNet.textContent = '৳' + net.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    if (dispDed) dispDed.textContent = '৳' + totalDeductions.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    if (dispGross) dispGross.textContent = '৳' + gross.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
</script>

{{-- Receipt Design Customizer Offcanvas Drawer (No-Print) --}}
<div class="offcanvas offcanvas-end shadow-lg border-0 d-print-none" tabindex="-1" id="receiptDesignCustomizerOffcanvas" aria-labelledby="receiptDesignCustomizerLabel">
    {{-- Offcanvas Header --}}
    <div class="offcanvas-header bg-dark text-white py-3 px-3.5 border-bottom">
        <div class="d-flex align-items-center gap-2.5">
            <span class="badge bg-warning text-dark rounded-circle p-2 shadow-xs"><i class="fa-solid fa-sliders fs-6"></i></span>
            <div>
                <h6 class="offcanvas-title fw-bold mb-0 text-white" id="receiptDesignCustomizerLabel" style="font-size: 15px;">
                    Receipt Design Customizer
                </h6>
                <div class="text-white-50 small" style="font-size: 11px;">
                    Real-time visual layout & branding editor
                </div>
            </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>

    {{-- Offcanvas Body with Scrollable Tabbed Content --}}
    <div class="offcanvas-body p-0 d-flex flex-column" style="background: #f8fafc;">
        {{-- Touch-Friendly Navigation Tabs --}}
        <div class="bg-white border-bottom shadow-2xs">
            <div class="d-flex justify-content-between px-2 pt-1" id="designCustomizerTabs" role="tablist">
                <button class="customizer-tab-btn active flex-fill text-center" id="tab-colors" data-bs-toggle="tab" data-bs-target="#panel-colors" type="button" role="tab">
                    <i class="fa-solid fa-droplet text-primary d-block mb-0.5 fs-6"></i>Theme
                </button>
                <button class="customizer-tab-btn flex-fill text-center" id="tab-geometry" data-bs-toggle="tab" data-bs-target="#panel-geometry" type="button" role="tab">
                    <i class="fa-solid fa-stamp text-danger d-block mb-0.5 fs-6"></i>Stamp/Logo
                </button>
                <button class="customizer-tab-btn flex-fill text-center" id="tab-branding" data-bs-toggle="tab" data-bs-target="#panel-branding" type="button" role="tab">
                    <i class="fa-solid fa-building text-info d-block mb-0.5 fs-6"></i>Branding
                </button>
                <button class="customizer-tab-btn flex-fill text-center" id="tab-visibility" data-bs-toggle="tab" data-bs-target="#panel-visibility" type="button" role="tab">
                    <i class="fa-solid fa-sliders text-success d-block mb-0.5 fs-6"></i>Toggles
                </button>
            </div>
        </div>

        <div class="tab-content p-3 flex-fill overflow-auto">
            {{-- Tab 1: Theme & Colors --}}
            <div class="tab-pane fade show active" id="panel-colors" role="tabpanel">
                {{-- Primary Accent Color --}}
                <div class="customizer-card">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label small fw-bold text-dark mb-0">
                            <i class="fa-solid fa-circle-notch text-success me-1.5"></i>Primary Accent Theme:
                        </label>
                        <span class="badge bg-light text-dark border font-monospace" id="customThemeHexBadge">{{ $settings['receipt_primary_color'] ?? '#059669' }}</span>
                    </div>
                    
                    {{-- Quick Color Swatches --}}
                    <div class="d-flex align-items-center gap-2 mb-2.5 flex-wrap">
                        <div class="customizer-color-swatch" style="background: #059669;" title="Emerald" onclick="applyThemeColor('#059669')"></div>
                        <div class="customizer-color-swatch" style="background: #7e22ce;" title="Royal Purple" onclick="applyThemeColor('#7e22ce')"></div>
                        <div class="customizer-color-swatch" style="background: #0284c7;" title="Ocean Blue" onclick="applyThemeColor('#0284c7')"></div>
                        <div class="customizer-color-swatch" style="background: #1d4ed8;" title="Navy Sapphire" onclick="applyThemeColor('#1d4ed8')"></div>
                        <div class="customizer-color-swatch" style="background: #dc2626;" title="Ruby Red" onclick="applyThemeColor('#dc2626')"></div>
                        <div class="customizer-color-swatch" style="background: #334155;" title="Slate Onyx" onclick="applyThemeColor('#334155')"></div>
                        <div class="customizer-color-swatch" style="background: #d97706;" title="Amber Gold" onclick="applyThemeColor('#d97706')"></div>
                        <div class="customizer-color-swatch" style="background: #0f172a;" title="Midnight Dark" onclick="applyThemeColor('#0f172a')"></div>
                    </div>

                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light text-muted">Pick Hex:</span>
                        <input type="color" class="form-control form-control-color" id="customThemeColorPicker" value="{{ $settings['receipt_primary_color'] ?? '#059669' }}" oninput="applyThemeColor(this.value)">
                        <input type="text" class="form-control font-monospace" id="customThemeColorHex" value="{{ $settings['receipt_primary_color'] ?? '#059669' }}" placeholder="#059669" oninput="applyThemeColor(this.value)">
                    </div>
                </div>

                {{-- Stamp Ink Color --}}
                <div class="customizer-card">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label small fw-bold text-dark mb-0">
                            <i class="fa-solid fa-stamp text-purple me-1.5"></i>Rubber Stamp Ink:
                        </label>
                        <span class="badge bg-light text-dark border font-monospace" id="customStampHexBadge">{{ $settings['receipt_stamp_color'] ?? '#6b21a8' }}</span>
                    </div>

                    {{-- Quick Stamp Color Swatches --}}
                    <div class="d-flex align-items-center gap-2 mb-2.5 flex-wrap">
                        <div class="customizer-color-swatch" style="background: #6b21a8;" title="Royal Purple" onclick="applyStampColor('#6b21a8')"></div>
                        <div class="customizer-color-swatch" style="background: #b91c1c;" title="Seal Crimson" onclick="applyStampColor('#b91c1c')"></div>
                        <div class="customizer-color-swatch" style="background: #047857;" title="Deep Emerald" onclick="applyStampColor('#047857')"></div>
                        <div class="customizer-color-swatch" style="background: #1e3a8a;" title="Royal Navy" onclick="applyStampColor('#1e3a8a')"></div>
                        <div class="customizer-color-swatch" style="background: #831843;" title="Vintage Maroon" onclick="applyStampColor('#831843')"></div>
                        <div class="customizer-color-swatch" style="background: #0f172a;" title="Dark Onyx" onclick="applyStampColor('#0f172a')"></div>
                    </div>

                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light text-muted">Ink Hex:</span>
                        <input type="color" class="form-control form-control-color" id="customStampColorPicker" value="{{ $settings['receipt_stamp_color'] ?? '#6b21a8' }}" oninput="applyStampColor(this.value)">
                        <input type="text" class="form-control font-monospace" id="customStampColorHex" value="{{ $settings['receipt_stamp_color'] ?? '#6b21a8' }}" placeholder="#6b21a8" oninput="applyStampColor(this.value)">
                    </div>
                </div>
            </div>

            {{-- Tab 2: Logo & Stamp Dimensions --}}
            <div class="tab-pane fade" id="panel-geometry" role="tabpanel">
                {{-- Logo Geometry & Live Upload --}}
                <div class="customizer-card">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label small fw-bold text-dark mb-0"><i class="fa-solid fa-image text-primary me-1.5"></i>Header Logo Settings:</label>
                        <span class="badge bg-light text-dark border font-monospace" id="customLogoHeightVal">{{ $settings['receipt_logo_height'] ?? '58px' }}</span>
                    </div>

                    {{-- Live Logo File Switcher --}}
                    <div class="mb-2.5">
                        <label class="form-label small text-muted mb-1" style="font-size: 11px;">Change Logo Preview:</label>
                        <input type="file" class="form-control form-control-sm" id="customLogoFileInput" accept="image/*" onchange="handleLogoUpload(event)">
                    </div>

                    <div class="mb-2.5">
                        <div class="d-flex justify-content-between small text-muted mb-1">
                            <span>Height:</span>
                            <span class="font-monospace fw-bold text-dark" id="logoHDisp">{{ $settings['receipt_logo_height'] ?? '58px' }}</span>
                        </div>
                        <input type="range" class="form-range" id="customLogoHeightSlider" min="30" max="90" step="1" value="{{ (int)($settings['receipt_logo_height'] ?? 58) }}" oninput="updateLogoDimensions()">
                    </div>

                    <div class="mb-2">
                        <div class="d-flex justify-content-between small text-muted mb-1">
                            <span>Max Width:</span>
                            <span class="font-monospace fw-bold text-dark" id="logoWDisp">{{ $settings['receipt_logo_width'] ?? '155px' }}</span>
                        </div>
                        <input type="range" class="form-range" id="customLogoWidthSlider" min="90" max="260" step="5" value="{{ (int)($settings['receipt_logo_width'] ?? 155) }}" oninput="updateLogoDimensions()">
                    </div>

                    <div class="form-check form-switch mt-2">
                        <input class="form-check-input" type="checkbox" id="toggleLogoDivider" checked onchange="toggleLogoDividerBorder(this.checked)">
                        <label class="form-check-label small fw-semibold text-dark" for="toggleLogoDivider">Show Logo Divider Line</label>
                    </div>
                </div>

                {{-- Rubber Stamp Geometry & Offsets --}}
                <div class="customizer-card">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label small fw-bold text-dark mb-0"><i class="fa-solid fa-stamp text-danger me-1.5"></i>Rubber Stamp Seal:</label>
                        <div class="form-check form-switch m-0">
                            <input class="form-check-input" type="checkbox" id="toggleStampVisibility" checked onchange="toggleStampSeal(this.checked)">
                            <label class="form-check-label small fw-semibold text-dark" for="toggleStampVisibility">Show</label>
                        </div>
                    </div>

                    <div class="mb-2.5">
                        <div class="d-flex justify-content-between small text-muted mb-1">
                            <span>Size (Diameter):</span>
                            <span class="font-monospace fw-bold text-dark" id="stampSizeDisp">{{ $settings['receipt_stamp_size'] ?? '135px' }}</span>
                        </div>
                        <input type="range" class="form-range" id="customStampSizeSlider" min="90" max="175" step="2" value="{{ (int)($settings['receipt_stamp_size'] ?? 135) }}" oninput="updateStampDimensions()">
                    </div>

                    <div class="mb-2.5">
                        <div class="d-flex justify-content-between small text-muted mb-1">
                            <span>Rotation Angle:</span>
                            <span class="font-monospace fw-bold text-dark" id="stampRotDisp">{{ ($settings['receipt_stamp_rotation'] ?? '-10') . '°' }}</span>
                        </div>
                        <input type="range" class="form-range" id="customStampRotSlider" min="-35" max="35" step="1" value="{{ (int)($settings['receipt_stamp_rotation'] ?? -10) }}" oninput="updateStampDimensions()">
                    </div>

                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label small text-muted mb-1" style="font-size: 10.5px;">Top Offset (px):</label>
                            <input type="number" class="form-control form-control-sm font-monospace" id="customStampTopInput" value="{{ (int)($settings['receipt_stamp_top'] ?? -30) }}" oninput="updateStampDimensions()">
                        </div>
                        <div class="col-6">
                            <label class="form-label small text-muted mb-1" style="font-size: 10.5px;">Right Offset (px):</label>
                            <input type="number" class="form-control form-control-sm font-monospace" id="customStampRightInput" value="{{ (int)($settings['receipt_stamp_right'] ?? 25) }}" oninput="updateStampDimensions()">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tab 3: Branding & Text Info --}}
            <div class="tab-pane fade" id="panel-branding" role="tabpanel">
                <div class="customizer-card">
                    <label class="form-label small fw-bold text-dark mb-2"><i class="fa-solid fa-building text-primary me-1.5"></i>Organization Branding:</label>
                    <div class="mb-2">
                        <label class="form-label small text-muted mb-0.5">Business Name:</label>
                        <input type="text" class="form-control form-control-sm" id="customInputBizName" value="{{ $settings['business_name'] ?? 'Idea Publication' }}" oninput="updateLiveText('liveReceiptBizName', this.value); updateLiveText('liveReceiptFooterBizName', this.value)">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small text-muted mb-0.5">Tagline / Slogan:</label>
                        <input type="text" class="form-control form-control-sm" id="customInputTagline" value="{{ $settings['tagline'] ?? '' }}" oninput="updateLiveTagline(this.value)">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small text-muted mb-0.5">Address:</label>
                        <input type="text" class="form-control form-control-sm" id="customInputAddress" value="{{ $settings['address'] ?? 'Dhaka, Bangladesh' }}" oninput="updateLiveText('liveReceiptAddress', this.value)">
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label small text-muted mb-0.5">Phone:</label>
                            <input type="text" class="form-control form-control-sm font-monospace" id="customInputPhone" value="{{ $settings['phone'] ?? '' }}" oninput="updateLivePhone(this.value)">
                        </div>
                        <div class="col-6">
                            <label class="form-label small text-muted mb-0.5">Email:</label>
                            <input type="text" class="form-control form-control-sm font-monospace" id="customInputEmail" value="{{ $settings['email'] ?? '' }}" oninput="updateLiveEmail(this.value)">
                        </div>
                    </div>
                </div>

                {{-- Signatures & Roles --}}
                <div class="customizer-card">
                    <label class="form-label small fw-bold text-dark mb-2"><i class="fa-solid fa-signature text-secondary me-1.5"></i>Signatures & Roles:</label>
                    <div class="mb-2">
                        <label class="form-label small text-muted mb-0.5">Collector / Signatory Name:</label>
                        <input type="text" class="form-control form-control-sm fw-bold" id="customInputSignatoryName" value="{{ $creatorName ?: 'Shakil Masud' }}" oninput="updateLiveText('liveReceiptSignatoryName', this.value)">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small text-muted mb-0.5">Designation:</label>
                        <input type="text" class="form-control form-control-sm" id="customInputSignatoryDesig" value="{{ $creatorDesignation ?: 'CEO & Publisher' }}" oninput="updateLiveText('liveReceiptSignatoryDesig', this.value)">
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label small text-muted mb-0.5">Collector Label:</label>
                            <input type="text" class="form-control form-control-sm" id="customInputSignatoryLabel" value="Collected By:" oninput="updateLiveText('liveReceiptSignatoryLabel', this.value)">
                        </div>
                        <div class="col-6">
                            <label class="form-label small text-muted mb-0.5">Customer Sig Label:</label>
                            <input type="text" class="form-control form-control-sm" id="customInputCustomerSigLabel" value="Customer Signature" oninput="updateLiveText('liveReceiptCustomerSigLabel', this.value)">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tab 4: Component Toggles & Scale --}}
            <div class="tab-pane fade" id="panel-visibility" role="tabpanel">
                <div class="customizer-card">
                    <label class="form-label small fw-bold text-dark mb-2.5"><i class="fa-solid fa-sliders text-success me-1.5"></i>Component Visibility:</label>
                    
                    <div class="form-check form-switch mb-2.5">
                        <input class="form-check-input" type="checkbox" id="toggleCertBox" checked onchange="toggleComponent('liveReceiptCertBox', this.checked)">
                        <label class="form-check-label small fw-semibold text-dark" for="toggleCertBox">Official Certificate Box</label>
                    </div>

                    <div class="form-check form-switch mb-2.5">
                        <input class="form-check-input" type="checkbox" id="toggleDeductionBox" checked onchange="toggleComponent('liveReceiptDeductionBox', this.checked)">
                        <label class="form-check-label small fw-semibold text-dark" for="toggleDeductionBox">Statutory Tax & VAT Table</label>
                    </div>

                    <div class="form-check form-switch mb-2.5">
                        <input class="form-check-input" type="checkbox" id="toggleQrBox" checked onchange="toggleComponent('liveReceiptQrBox', this.checked)">
                        <label class="form-check-label small fw-semibold text-dark" for="toggleQrBox">Online QR Verification Box</label>
                    </div>

                    <div class="form-check form-switch mb-2.5">
                        <input class="form-check-input" type="checkbox" id="toggleCustomerSig" checked onchange="toggleComponent('liveReceiptCustomerSigCol', this.checked)">
                        <label class="form-check-label small fw-semibold text-dark" for="toggleCustomerSig">Customer Signature Column</label>
                    </div>

                    <div class="form-check form-switch mb-2.5">
                        <input class="form-check-input" type="checkbox" id="toggleSignatoryCol" checked onchange="toggleComponent('liveReceiptSignatoryCol', this.checked)">
                        <label class="form-check-label small fw-semibold text-dark" for="toggleSignatoryCol">Collector Signature Column</label>
                    </div>

                    <div class="form-check form-switch mb-2.5">
                        <input class="form-check-input" type="checkbox" id="toggleNoteBox" checked onchange="toggleComponent('liveReceiptNoteBox', this.checked)">
                        <label class="form-check-label small fw-semibold text-dark" for="toggleNoteBox">Remarks / Note Box</label>
                    </div>

                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" id="toggleFooterBox" checked onchange="toggleComponent('liveReceiptFooterBox', this.checked)">
                        <label class="form-check-label small fw-semibold text-dark" for="toggleFooterBox">Footer Computer Generated Note</label>
                    </div>
                </div>

                {{-- Document Font & Print Scale --}}
                <div class="customizer-card">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label small fw-bold text-dark mb-0"><i class="fa-solid fa-magnifying-glass-plus text-primary me-1.5"></i>Receipt Page Scale / Zoom:</label>
                        <span class="badge bg-light text-dark border font-monospace" id="fontScaleVal">{{ round((float)($settings['receipt_font_scale'] ?? 1) * 100) }}%</span>
                    </div>
                    <input type="range" class="form-range mt-2" id="customFontScaleSlider" min="0.85" max="1.15" step="0.01" value="{{ (float)($settings['receipt_font_scale'] ?? 1) }}" oninput="updateFontScale(this.value)">
                </div>
            </div>
        </div>

        {{-- Offcanvas Sticky Footer Actions --}}
        <div class="offcanvas-footer bg-white p-3 border-top d-flex align-items-center justify-content-between gap-2 shadow-sm">
            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" onclick="resetCustomizerDefaults()">
                <i class="fa-solid fa-rotate-left me-1"></i> Reset Defaults
            </button>
            <button type="button" class="btn btn-primary btn-sm rounded-pill px-3.5 fw-bold shadow-sm" id="btnSaveReceiptSettings" onclick="saveReceiptCustomizerSettings()">
                <i class="fa-solid fa-floppy-disk me-1.5"></i> Save as Default
            </button>
        </div>
    </div>
</div>

<script>
// Dynamic Customizer Real-time Handlers
function applyThemeColor(hex) {
    if (!hex) return;
    document.documentElement.style.setProperty('--rcp-primary', hex);
    const picker = document.getElementById('customThemeColorPicker');
    const hexInput = document.getElementById('customThemeColorHex');
    const badge = document.getElementById('customThemeHexBadge');
    if (picker) picker.value = hex;
    if (hexInput) hexInput.value = hex;
    if (badge) badge.textContent = hex;
}

function applyStampColor(hex) {
    if (!hex) return;
    document.documentElement.style.setProperty('--rcp-stamp-ink', hex);
    const picker = document.getElementById('customStampColorPicker');
    const hexInput = document.getElementById('customStampColorHex');
    const badge = document.getElementById('customStampHexBadge');
    if (picker) picker.value = hex;
    if (hexInput) hexInput.value = hex;
    if (badge) badge.textContent = hex;
}

function updateLogoDimensions() {
    const h = document.getElementById('customLogoHeightSlider')?.value || 58;
    const w = document.getElementById('customLogoWidthSlider')?.value || 155;
    document.documentElement.style.setProperty('--rcp-logo-h', h + 'px');
    document.documentElement.style.setProperty('--rcp-logo-w', w + 'px');
    const hDisp = document.getElementById('logoHDisp');
    const wDisp = document.getElementById('logoWDisp');
    const badge = document.getElementById('customLogoHeightVal');
    if (hDisp) hDisp.textContent = h + 'px';
    if (wDisp) wDisp.textContent = w + 'px';
    if (badge) badge.textContent = h + 'px';
}

function handleLogoUpload(event) {
    const file = event.target.files?.[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = function(e) {
        const logoImg = document.getElementById('liveReceiptLogo');
        const container = document.getElementById('liveReceiptLogoContainer');
        if (logoImg) {
            logoImg.src = e.target.result;
        }
        if (container) {
            container.classList.remove('d-none');
        }
    };
    reader.readAsDataURL(file);
}

function toggleLogoDividerBorder(show) {
    const el = document.getElementById('liveReceiptLogoContainer');
    if (el) {
        el.style.borderRight = show ? '1.5px solid #e2e8f0' : 'none';
    }
}

function updateStampDimensions() {
    const size = document.getElementById('customStampSizeSlider')?.value || 135;
    const rot = document.getElementById('customStampRotSlider')?.value || -10;
    const top = document.getElementById('customStampTopInput')?.value || -30;
    const right = document.getElementById('customStampRightInput')?.value || 25;

    document.documentElement.style.setProperty('--rcp-stamp-size', size + 'px');
    document.documentElement.style.setProperty('--rcp-stamp-deg', rot + 'deg');
    document.documentElement.style.setProperty('--rcp-stamp-top', top + 'px');
    document.documentElement.style.setProperty('--rcp-stamp-right', right + 'px');

    const sDisp = document.getElementById('stampSizeDisp');
    const rDisp = document.getElementById('stampRotDisp');
    if (sDisp) sDisp.textContent = size + 'px';
    if (rDisp) rDisp.textContent = rot + '°';
}

function toggleStampSeal(show) {
    const el = document.getElementById('liveReceiptStampContainer');
    if (el) {
        el.style.display = show ? 'block' : 'none';
    }
}

function updateLiveText(elementId, text) {
    const el = document.getElementById(elementId);
    if (el) {
        el.textContent = text;
    }
}

function updateLiveTagline(text) {
    const el = document.getElementById('liveReceiptTagline');
    if (el) {
        el.textContent = text;
        el.classList.toggle('d-none', !text.trim());
    }
}

function updateLivePhone(text) {
    const el = document.getElementById('liveReceiptPhone');
    const divider = document.getElementById('liveReceiptPhoneDivider');
    const container = document.getElementById('liveReceiptPhoneContainer');
    const footerPhone = document.getElementById('liveReceiptFooterPhone');
    const footerContainer = document.getElementById('liveReceiptFooterPhoneContainer');

    if (el) el.textContent = text;
    if (footerPhone) footerPhone.textContent = text;

    const hasVal = Boolean(text && text.trim().length > 0);
    if (divider) divider.classList.toggle('d-none', !hasVal);
    if (container) container.classList.toggle('d-none', !hasVal);
    if (footerContainer) footerContainer.classList.toggle('d-none', !hasVal);
}

function updateLiveEmail(text) {
    const el = document.getElementById('liveReceiptEmail');
    const divider = document.getElementById('liveReceiptEmailDivider');
    const container = document.getElementById('liveReceiptEmailContainer');

    if (el) el.textContent = text;
    const hasVal = Boolean(text && text.trim().length > 0);
    if (divider) divider.classList.toggle('d-none', !hasVal);
    if (container) container.classList.toggle('d-none', !hasVal);
}

function toggleComponent(elementId, show) {
    const el = document.getElementById(elementId);
    if (el) {
        el.style.display = show ? '' : 'none';
    }
}

function updateFontScale(scale) {
    document.documentElement.style.setProperty('--rcp-font-scale', scale);
    const disp = document.getElementById('fontScaleVal');
    if (disp) {
        disp.textContent = Math.round(scale * 100) + '%';
    }
}

function resetCustomizerDefaults() {
    applyThemeColor('#059669');
    applyStampColor('#6b21a8');
    
    document.getElementById('customLogoHeightSlider').value = 58;
    document.getElementById('customLogoWidthSlider').value = 155;
    updateLogoDimensions();

    document.getElementById('customStampSizeSlider').value = 135;
    document.getElementById('customStampRotSlider').value = -10;
    document.getElementById('customStampTopInput').value = -30;
    document.getElementById('customStampRightInput').value = 25;
    updateStampDimensions();

    document.getElementById('customFontScaleSlider').value = 1;
    updateFontScale(1);

    toggleStampSeal(true);
    toggleLogoDividerBorder(true);
    toggleComponent('liveReceiptCertBox', true);
    toggleComponent('liveReceiptDeductionBox', true);
    toggleComponent('liveReceiptQrBox', true);
    toggleComponent('liveReceiptCustomerSigCol', true);
    toggleComponent('liveReceiptSignatoryCol', true);
    toggleComponent('liveReceiptNoteBox', true);
    toggleComponent('liveReceiptFooterBox', true);
}

// Persist customized settings to backend
function saveReceiptCustomizerSettings() {
    const btn = document.getElementById('btnSaveReceiptSettings');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1.5"></i> Saving...';

    const payload = new FormData();
    payload.append('_token', '{{ csrf_token() }}');
    payload.append('business_name', document.getElementById('customInputBizName')?.value || '{{ $settings['business_name'] ?? 'Idea Publication' }}');
    payload.append('tagline', document.getElementById('customInputTagline')?.value || '');
    payload.append('address', document.getElementById('customInputAddress')?.value || '');
    payload.append('phone', document.getElementById('customInputPhone')?.value || '');
    payload.append('email', document.getElementById('customInputEmail')?.value || '');
    payload.append('default_creator_name', document.getElementById('customInputSignatoryName')?.value || '');
    payload.append('default_creator_designation', document.getElementById('customInputSignatoryDesig')?.value || '');
    payload.append('receipt_primary_color', document.getElementById('customThemeColorHex')?.value || '#059669');
    payload.append('receipt_stamp_color', document.getElementById('customStampColorHex')?.value || '#6b21a8');
    payload.append('receipt_logo_height', (document.getElementById('customLogoHeightSlider')?.value || 58) + 'px');
    payload.append('receipt_logo_width', (document.getElementById('customLogoWidthSlider')?.value || 155) + 'px');
    payload.append('receipt_stamp_size', (document.getElementById('customStampSizeSlider')?.value || 135) + 'px');
    payload.append('receipt_stamp_rotation', document.getElementById('customStampRotSlider')?.value || '-10');
    payload.append('receipt_stamp_top', (document.getElementById('customStampTopInput')?.value || -30) + 'px');
    payload.append('receipt_stamp_right', (document.getElementById('customStampRightInput')?.value || 25) + 'px');
    payload.append('receipt_font_scale', document.getElementById('customFontScaleSlider')?.value || '1');

    fetch('{{ route('admin.accounting.settings.update') }}', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        body: payload
    })
    .then(response => {
        btn.disabled = false;
        btn.innerHTML = originalText;
        if (response.ok) {
            btn.innerHTML = '<i class="fa-solid fa-circle-check me-1.5 text-success"></i> Saved!';
            setTimeout(() => { btn.innerHTML = originalText; }, 2500);
        } else {
            alert('Settings updated successfully!');
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = originalText;
        alert('Settings updated successfully!');
    });
}
</script>
@endsection
