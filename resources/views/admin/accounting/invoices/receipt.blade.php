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
    <div class="d-flex flex-wrap gap-2 align-items-center">
        <button type="button" class="btn btn-primary btn-sm rounded-pill px-3.5 shadow-sm fw-semibold" onclick="window.print()">
            <i class="fas fa-print me-1.5"></i> Print Receipt
        </button>

        @if($invoice)
            <a href="{{ route('admin.accounting.invoices.show', $invoice->id) }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-xs">
                <i class="fas fa-arrow-left me-1"></i> Invoice #{{ $invoice->invoice_no }}
            </a>
        @endif

        <a href="{{ route('admin.accounting.customer-ledger.index', ['customer_name' => $payment->party_name, 'customer_phone' => $payment->party_phone]) }}" class="btn btn-outline-info text-dark btn-sm rounded-pill px-3 shadow-xs fw-semibold">
            <i class="fas fa-book-bookmark me-1 text-primary"></i> Customer Ledger
        </a>
    </div>
@endsection

@section('content')
<style>
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
    }
    .receipt-paper::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #10b981 0%, #059669 50%, #047857 100%);
    }
    .receipt-title-badge {
        display: inline-block;
        background: #f0fdf4;
        color: #166534;
        border: 1px dashed #86efac;
        padding: 4px 14px;
        border-radius: 999px;
        font-weight: 700;
        font-size: 12px;
        letter-spacing: 0.5px;
    }
    .cert-box-en {
        background: #f8fafc;
        border: 1.5px solid #0284c7 !important;
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

    /* Rubber Stamp Effect */
    .rubber-stamp-container {
        position: absolute;
        right: 218px;
        top: 50%;
        transform: translateY(-50%) rotate(-12deg);
        pointer-events: none;
        user-select: none;
        z-index: 5;
    }
    .rubber-stamp-paid {
        display: inline-flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        width: 220px;
        padding: 8px 12px;
        border: 4px dashed #6b21a8;
        border-radius: 12px;
        color: #581c87;
        text-transform: uppercase;
        font-family: 'Arial Black', Impact, 'Trebuchet MS', sans-serif;
        text-align: center;
        line-height: 1.05;
        opacity: 0.82;
        mix-blend-mode: multiply;
        box-shadow: inset 0 0 0 2px #7e22ce, 0 0 3px rgba(107, 33, 168, 0.45);
        background: radial-gradient(circle, rgba(255, 255, 255, 0.65) 0%, rgba(243, 232, 255, 0.4) 100%);
        letter-spacing: 1px;
    }
    .rubber-stamp-paid .stamp-org {
        font-size: 8.5px;
        font-weight: 800;
        letter-spacing: 1.5px;
        color: #6b21a8;
        margin-bottom: 2px;
        opacity: 0.9;
    }
    .rubber-stamp-paid .stamp-title {
        font-size: 32px;
        font-weight: 900;
        letter-spacing: 4px;
        line-height: 0.95;
        color: #581c87;
        text-shadow: 0 0 1px rgba(88, 28, 135, 0.5);
    }
    .rubber-stamp-paid .stamp-date {
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 1px;
        color: #6b21a8;
        margin: 3px 0 2px 0;
        padding: 1px 8px;
        border-top: 1.5px solid #7e22ce;
        border-bottom: 1.5px solid #7e22ce;
        font-family: 'Courier New', Courier, monospace, sans-serif;
        display: inline-block;
    }
    .rubber-stamp-paid .stamp-sub {
        font-size: 9px;
        font-weight: 800;
        letter-spacing: 1.5px;
        margin-top: 2px;
        color: #7e22ce;
        display: block;
    }

    .rubber-stamp-partial {
        display: inline-flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        width: 220px;
        padding: 8px 12px;
        border: 4px dashed #6b21a8;
        border-radius: 12px;
        color: #581c87;
        text-transform: uppercase;
        font-family: 'Arial Black', Impact, 'Trebuchet MS', sans-serif;
        text-align: center;
        line-height: 1.05;
        opacity: 0.82;
        mix-blend-mode: multiply;
        box-shadow: inset 0 0 0 2px #7e22ce, 0 0 3px rgba(107, 33, 168, 0.45);
        background: radial-gradient(circle, rgba(255, 255, 255, 0.65) 0%, rgba(243, 232, 255, 0.4) 100%);
        letter-spacing: 1px;
    }
    .rubber-stamp-partial .stamp-org {
        font-size: 8.5px;
        font-weight: 800;
        letter-spacing: 1.5px;
        color: #6b21a8;
        margin-bottom: 2px;
        opacity: 0.9;
    }
    .rubber-stamp-partial .stamp-title {
        font-size: 28px;
        font-weight: 900;
        letter-spacing: 3px;
        line-height: 0.95;
        color: #581c87;
    }
    .rubber-stamp-partial .stamp-date {
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 1px;
        color: #6b21a8;
        margin: 3px 0 2px 0;
        padding: 1px 8px;
        border-top: 1.5px solid #7e22ce;
        border-bottom: 1.5px solid #7e22ce;
        font-family: 'Courier New', Courier, monospace, sans-serif;
        display: inline-block;
    }
    .rubber-stamp-partial .stamp-sub {
        font-size: 8.5px;
        font-weight: 800;
        letter-spacing: 1.2px;
        margin-top: 2px;
        color: #7e22ce;
        display: block;
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
        .adm-side, .adm-header, .adm-topbar, .breadcrumb, .btn, .no-print, footer, nav, .alert, header {
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
        .receipt-meta-cell {
            padding: 5px 8px !important;
        }
        .amount-highlight-box {
            padding: 8px 12px !important;
            margin-bottom: 8px !important;
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
            <div class="col-7">
                <div class="d-flex align-items-center gap-3">
                    @if(!empty($logoSrc))
                        <div class="pe-2 me-1" style="border-right: 1.5px solid #f1f5f9;">
                            <img src="{{ $logoSrc }}" alt="Logo" style="height: 44px; max-width: 130px; object-fit: contain;">
                        </div>
                    @endif
                    <div>
                        <h5 class="fw-bold mb-0 text-dark" style="font-size: 17px; letter-spacing: -0.2px;">{{ $settings['business_name'] ?? 'Idea Publication' }}</h5>
                        @if(!empty($settings['tagline']))
                            <div class="text-muted small" style="font-size: 11px; margin-top: 1px;">{{ $settings['tagline'] }}</div>
                        @endif
                        <div class="text-secondary small mt-0.5" style="font-size: 10.5px;">
                            {{ $settings['address'] ?? 'Dhaka, Bangladesh' }}
                            @if(!empty($settings['phone'])) | Phone: {{ $settings['phone'] }} @endif
                            @if(!empty($settings['email'])) | Email: {{ $settings['email'] }} @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-5 text-end">
                <div class="receipt-title-badge mb-0.5">
                    <i class="fas fa-receipt me-1"></i> MONEY RECEIPT
                </div>
                <div class="fw-bold text-dark fs-6 font-monospace">#{{ $payment->payment_no }}</div>
                <div class="text-muted small" style="font-size: 11px;">Date: <strong class="text-dark">{{ $payment->payment_date ? $payment->payment_date->format('d M, Y') : date('d M, Y') }}</strong></div>
            </div>
        </div>

        {{-- Formal English Official Certificate --}}
        <div class="cert-box-en position-relative">
            <div class="p-2.5 bg-white rounded-2 border mb-2 text-dark lh-base" style="font-size: 12px; text-align: justify;">
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
                    <i class="fas fa-shield-halved text-success me-1"></i>
                    Official Status: <strong class="text-success">{{ $remainingDue <= 0 ? 'Full Settlement Achieved' : 'Partial Installment Acknowledged' }}</strong>.
                </p>
                <span class="badge {{ $remainingDue <= 0 ? 'bg-success text-white' : 'bg-warning text-dark' }} px-2 py-0.5 rounded-pill fw-bold" style="font-size: 10px;">
                    {{ $remainingDue <= 0 ? 'Paid in Full' : 'Partial Paid' }}
                </span>
            </div>
        </div>

        {{-- TDS / VDS Deductions Breakdown Table (If Present) --}}
        @if($payment->has_deductions)
            <div class="card border border-warning-subtle bg-warning-subtle bg-opacity-10 rounded-2 p-2 mb-2">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <span class="fw-bold text-dark" style="font-size: 11.5px;">
                        <i class="fas fa-scale-balanced text-warning-emphasis me-1"></i> Statutory Tax & VAT Deduction Breakdown
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
                                    <strong class="text-success"><i class="fas fa-money-check-dollar me-1"></i>Net Realized (Cheque / Cash)</strong>
                                </td>
                                <td class="text-center py-1 px-2">—</td>
                                <td class="text-end font-monospace fw-bold text-success py-1 px-2">৳{{ number_format($payment->effective_net_amount, 2) }}</td>
                                <td class="text-muted small py-1 px-2">{{ $payment->transaction_ref ? 'Ref: ' . $payment->transaction_ref : 'Bank / Cash' }}</td>
                            </tr>
                            @if((float)$payment->vat_deduction_amount > 0)
                                <tr>
                                    <td class="py-1 px-2">VDS (VAT Deducted at Source)</td>
                                    <td class="text-center font-monospace py-1 px-2">{{ $payment->vat_deduction_rate ? $payment->vat_deduction_rate . '%' : '—' }}</td>
                                    <td class="text-end font-monospace text-danger fw-bold py-1 px-2">৳{{ number_format($payment->vat_deduction_amount, 2) }}</td>
                                    <td class="text-muted small py-1 px-2">Govt. Treasury (Mushak 6.6)</td>
                                </tr>
                            @endif
                            @if((float)$payment->tax_deduction_amount > 0)
                                <tr>
                                    <td class="py-1 px-2">TDS (Tax Deducted at Source)</td>
                                    <td class="text-center font-monospace py-1 px-2">{{ $payment->tax_deduction_rate ? $payment->tax_deduction_rate . '%' : '—' }}</td>
                                    <td class="text-end font-monospace text-danger fw-bold py-1 px-2">৳{{ number_format($payment->tax_deduction_amount, 2) }}</td>
                                    <td class="text-muted small py-1 px-2">Govt. Treasury Deposit</td>
                                </tr>
                            @endif
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
                                <td class="text-dark small py-1 px-2">{{ $payment->deduction_challan_no ? 'Challan: ' . $payment->deduction_challan_no . ' (Audited & Approved)' : 'Audited & Approved' }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        @endif

        {{-- Financial Breakdown Card with Authentic Rubber Stamp Pad Seal --}}
        <div class="amount-highlight-box mb-2">
            {{-- Realistic Rubber Stamp Seal --}}
            <div class="rubber-stamp-container">
                @if($remainingDue <= 0)
                    <div class="rubber-stamp-paid">
                        <span class="stamp-org">IDEA PUBLICATION</span>
                        <span class="stamp-title">PAID</span>
                        <span class="stamp-date">{{ $payment->payment_date ? $payment->payment_date->format('d M, Y') : date('d M, Y') }}</span>
                        <span class="stamp-sub">FULL SETTLEMENT</span>
                    </div>
                @else
                    <div class="rubber-stamp-partial">
                        <span class="stamp-org">IDEA PUBLICATION</span>
                        <span class="stamp-title">PAID</span>
                        <span class="stamp-date">{{ $payment->payment_date ? $payment->payment_date->format('d M, Y') : date('d M, Y') }}</span>
                        <span class="stamp-sub">PARTIAL INSTALMENT</span>
                    </div>
                @endif
            </div>

            <div class="row align-items-center">
                <div class="col-md-6 border-end-md pe-md-4">
                    <div class="text-success small fw-bold text-uppercase mb-0.5" style="font-size: 11px;">
                        <i class="fas fa-money-check-dollar me-1"></i> Net Received Amount (Cheque / Cash)
                    </div>
                    <div class="fs-3 fw-bold text-success font-monospace mb-0.5">
                        ৳{{ number_format($payment->effective_net_amount, 2) }}
                    </div>
                    <div class="text-muted small" style="font-size: 11.5px;">
                        In Words: <strong class="text-dark">@takaInWordsEn($payment->effective_net_amount)</strong>
                    </div>
                    @if($payment->has_deductions)
                        <div class="mt-1.5 pt-1 border-top border-success-subtle d-flex gap-2 flex-wrap" style="font-size: 10px;">
                            <span class="badge bg-white text-primary border border-primary font-monospace">Gross Settled: ৳{{ number_format($thisAmount, 2) }}</span>
                            <span class="badge bg-white text-danger border border-danger font-monospace">TDS/VDS: ৳{{ number_format($payment->total_deductions, 2) }}</span>
                        </div>
                    @endif
                </div>

                <div class="col-md-6 ps-md-4">
                    <div class="text-muted small fw-bold text-uppercase mb-1" style="font-size: 10px;">
                        Statement Summary
                    </div>
                    <div class="d-flex justify-content-between py-0.5 small" style="font-size: 11.5px;">
                        <span class="text-muted">Total Billed:</span>
                        <span class="fw-semibold font-monospace">৳{{ number_format($totalGrand, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-0.5 small" style="font-size: 11.5px;">
                        <span class="text-muted">Previous Paid:</span>
                        <span class="fw-semibold font-monospace">৳{{ number_format($prevPaid, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-0.5 small text-primary fw-bold" style="font-size: 11.5px;">
                        <span>Current Settled:</span>
                        <span class="font-monospace">৳{{ number_format($thisAmount, 2) }}</span>
                    </div>
                    @if($payment->has_deductions)
                        <div class="d-flex justify-content-between py-0.5 small text-success fw-bold" style="font-size: 11.5px;">
                            <span>Net Received:</span>
                            <span class="font-monospace">৳{{ number_format($payment->effective_net_amount, 2) }}</span>
                        </div>
                    @endif
                    <div class="d-flex justify-content-between py-0.5 mt-0.5 border-top fw-bold {{ $remainingDue > 0 ? 'text-danger' : 'text-success' }}" style="font-size: 12px;">
                        <span>Remaining Due:</span>
                        <span class="font-monospace">৳{{ number_format($remainingDue, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Note & Next Due Date (If present) --}}
        @if(!empty($payment->note) || ($invoice && $invoice->due_date && $remainingDue > 0))
            <div class="row g-2 mb-2">
                @if(!empty($payment->note))
                    <div class="col-md-8">
                        <div class="p-1.5 bg-light rounded-2 border" style="font-size: 11px;">
                            <span class="text-muted fw-bold me-1"><i class="fas fa-comment-dots me-1"></i>Remarks:</span>
                            <span class="text-dark">{{ $payment->note }}</span>
                        </div>
                    </div>
                @endif

                @if($invoice && $invoice->due_date && $remainingDue > 0)
                    <div class="col-md-4 text-md-end">
                        <div class="p-1.5 bg-danger-subtle rounded-2 border border-danger-subtle text-danger small fw-semibold d-inline-block text-start" style="font-size: 11px;">
                            <i class="fas fa-calendar-day me-1"></i> Next Due: 
                            <strong class="text-danger font-monospace">{{ $invoice->due_date->format('d M, Y') }}</strong>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        {{-- Signatures & Acknowledgement --}}
        <div class="signature-section">
            <div class="row align-items-center text-center">
                {{-- Left: Customer / Payer Signature --}}
                <div class="col-4 text-start">
                    <div class="d-inline-block text-center" style="min-width: 145px;">
                        <div class="small fw-bold text-dark mb-0.5" style="font-size: 11px;">Customer Signature</div>
                        <div class="text-muted" style="font-size: 9.5px;">Executive Director</div>
                        <div class="border-bottom border-dark mt-1 mx-auto" style="width: 140px;"></div>
                    </div>
                </div>

                {{-- Center: Verify QR Code & Collector Info --}}
                <div class="col-4">
                    <div class="d-flex align-items-center justify-content-center gap-2.5">
                        <div class="p-1 border rounded bg-white shadow-2xs d-flex flex-column align-items-center" style="width: 44px; height: 44px;">
                            <img src="{{ $qrCodeUrl }}" alt="Verify QR" style="width: 32px; height: 32px; object-fit: contain; display: block;">
                            <span class="text-muted fw-bold" style="font-size: 6.5px; text-transform: uppercase; line-height: 1;">Verify</span>
                        </div>
                        <div class="text-start text-muted" style="font-size: 9.5px; line-height: 1.35;">
                            <div>Collected By: <strong class="text-dark">{{ $payment->recorder?->name ?? 'Admin' }}</strong></div>
                            <div class="font-monospace text-secondary" style="font-size: 9px;">Issued: {{ $payment->payment_date ? $payment->payment_date->format('d/m/Y') : date('d/m/Y') }}</div>
                        </div>
                    </div>
                </div>

                {{-- Right: Authorized Signatory --}}
                <div class="col-4 text-end">
                    <div class="d-inline-block text-center" style="min-width: 145px;">
                        <div class="small fw-bold text-dark mb-0.5" style="font-size: 11px;">{{ $creatorName ?: 'Shakil Masud' }}</div>
                        <div class="text-muted" style="font-size: 9.5px;">{{ $creatorDesignation ?: 'CEO & Publisher' }}</div>
                        <div class="border-bottom border-dark mt-1 mx-auto" style="width: 140px;"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center text-muted mt-2 pt-1.5 border-top" style="font-size: 9.5px;">
            Thank you! This is an official computer-generated receipt from {{ $settings['business_name'] ?? 'Idea Publication' }}. Contact: {{ $settings['phone'] ?? '' }}
        </div>
    </div>
</div>
@endsection
