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

    /* Authentic Vector Round Rubber Stamp Effect */
    .round-rubber-stamp-container {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        user-select: none;
    }
    .round-rubber-stamp-svg {
        width: 140px;
        height: 140px;
        display: block;
        transform: rotate(-10deg);
        filter: drop-shadow(0 0 1px rgba(107, 33, 168, 0.45));
        mix-blend-mode: multiply;
        opacity: 0.92;
        pointer-events: none;
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
        .round-rubber-stamp-svg {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            mix-blend-mode: multiply !important;
            opacity: 0.95 !important;
            filter: none !important;
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
                        <div class="text-secondary small mt-1 d-flex align-items-center flex-wrap" style="font-size: 11px; line-height: 1.4;">
                            <span>{{ $settings['address'] ?? 'Dhaka, Bangladesh' }}</span>
                            @if(!empty($settings['phone']))
                                <span class="text-muted mx-2">|</span>
                                <span class="d-inline-flex align-items-center"><i class="fas fa-phone-alt text-secondary me-1.5" style="font-size: 10px;"></i>{{ $settings['phone'] }}</span>
                            @endif
                            @if(!empty($settings['email']))
                                <span class="text-muted mx-2">|</span>
                                <span class="d-inline-flex align-items-center"><i class="fas fa-envelope text-secondary me-1.5" style="font-size: 10.5px;"></i>{{ $settings['email'] }}</span>
                            @endif
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
                                <td class="text-dark small py-1 px-2">{{ $payment->deduction_challan_no ? 'Challan: ' . $payment->deduction_challan_no . ' (Approved)' : 'Approved' }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        @endif

        {{-- Financial Breakdown Card with Authentic Round Rubber Stamp Seal --}}
        <div class="amount-highlight-box mb-2" style="background: #f8fafc; border: 1.5px solid #e2e8f0; padding: 14px 16px;">
            <div class="row align-items-center g-3">
                {{-- Column 1 & 2: Clean Structured Billing & Highlighted Paid to Date --}}
                <div class="col-md-9 border-end-md pe-md-4">
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
                                    <i class="fas fa-money-check-dollar me-1.5"></i> Total Paid to Date:
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
                                    <i class="fas fa-circle-exclamation me-1.5"></i> Due:
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

                {{-- Column 3: Official Round Rubber Stamp (SVG with curved border text) --}}
                <div class="col-md-3 text-center d-flex align-items-center justify-content-center py-1">
                    <div class="round-rubber-stamp-container">
                        <svg class="round-rubber-stamp-svg" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
                            <defs>
                                {{-- Top Curved Text Path for "IDEA PROKASHON" --}}
                                <path id="stampTopPath" d="M 24,100 A 76,76 0 0,1 176,100" fill="none" />
                                {{-- Bottom Curved Text Path --}}
                                <path id="stampBottomPath" d="M 176,100 A 76,76 0 0,1 24,100" fill="none" />
                            </defs>

                            {{-- Outer Dashed Ring --}}
                            <circle cx="100" cy="100" r="94" fill="none" stroke="#6b21a8" stroke-width="2.5" stroke-dasharray="6,4" />
                            {{-- Inner Solid Ring --}}
                            <circle cx="100" cy="100" r="88" fill="none" stroke="#7e22ce" stroke-width="1.5" />
                            
                            {{-- Curved Top Text: IDEA PROKASHON along the border --}}
                            <text fill="#6b21a8" font-size="16" font-weight="900" font-family="'Arial Black', Impact, sans-serif" letter-spacing="3.5">
                                <textPath href="#stampTopPath" xlink:href="#stampTopPath" startOffset="50%" text-anchor="middle">
                                    IDEA PROKASHON
                                </textPath>
                            </text>

                            {{-- Curved Bottom Text --}}
                            <text fill="#7e22ce" font-size="11.5" font-weight="800" font-family="'Arial Black', Impact, sans-serif" letter-spacing="2">
                                <textPath href="#stampBottomPath" xlink:href="#stampBottomPath" startOffset="50%" text-anchor="middle">
                                    {{ $remainingDue <= 0 ? '★ FULL SETTLEMENT ★' : '★ PARTIAL PAYMENT ★' }}
                                </textPath>
                            </text>

                            {{-- Inner Center Border Ring --}}
                            <circle cx="100" cy="100" r="56" fill="rgba(243, 232, 255, 0.25)" stroke="#7e22ce" stroke-width="1.2" stroke-dasharray="4,2.5" />

                            {{-- Center Big Status: PAID / DUE (+30px larger) --}}
                            @if($remainingDue <= 0)
                                <text x="100" y="93" text-anchor="middle" fill="#581c87" font-size="34" font-weight="900" font-family="'Arial Black', Impact, sans-serif" letter-spacing="3">
                                    PAID
                                </text>
                            @else
                                <text x="100" y="93" text-anchor="middle" fill="#7e22ce" font-size="34" font-weight="900" font-family="'Arial Black', Impact, sans-serif" letter-spacing="3">
                                    DUE
                                </text>
                            @endif

                            {{-- Center Horizontal Date Lines --}}
                            <line x1="50" y1="104" x2="150" y2="104" stroke="#7e22ce" stroke-width="1.2" />
                            <text x="100" y="117" text-anchor="middle" fill="#6b21a8" font-size="12" font-weight="800" font-family="'Courier New', Courier, monospace" letter-spacing="1">
                                {{ $payment->payment_date ? $payment->payment_date->format('d M, Y') : date('d M, Y') }}
                            </text>
                            <line x1="50" y1="123" x2="150" y2="123" stroke="#7e22ce" stroke-width="1.2" />
                        </svg>
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
            <div class="row align-items-end text-center">
                {{-- Left: Customer / Payer Signature (Matched baseline with Collected By:) --}}
                <div class="col-4 text-start">
                    <div class="d-inline-flex flex-column align-items-center justify-content-end text-center" style="min-width: 145px; min-height: 54px;">
                        <div class="border-top border-dark mb-1" style="width: 140px;"></div>
                        <div class="small fw-bold text-dark" style="font-size: 11px; line-height: 1.3;">Customer Signature</div>
                    </div>
                </div>

                {{-- Center: Verify QR Code & Document Info --}}
                <div class="col-4">
                    <div class="d-flex align-items-center justify-content-center gap-2.5">
                        <div class="p-1 border rounded bg-white shadow-2xs d-flex flex-column align-items-center" style="width: 83px; height: 83px;">
                            <img src="{{ $qrCodeUrl }}" alt="Verify QR" style="width: 75px; height: 75px; object-fit: contain; display: block;">
                        </div>
                        <div class="text-start text-muted" style="font-size: 10px; line-height: 1.4;">
                            <div class="fw-bold text-dark" style="font-size: 11px;"><i class="fas fa-shield-check text-success me-1"></i>Official Verification</div>
                            <div class="font-monospace text-secondary" style="font-size: 9.5px;">Issued: {{ $payment->payment_date ? $payment->payment_date->format('d/m/Y') : date('d/m/Y') }}</div>
                            <div class="text-muted small" style="font-size: 9px;">Scan to Verify Online</div>
                        </div>
                    </div>
                </div>

                {{-- Right: Authorized Collector / Signatory --}}
                <div class="col-4 text-end">
                    <div class="d-inline-flex flex-column align-items-center justify-content-end text-center" style="min-width: 145px; min-height: 54px;">
                        <div class="small fw-bold text-dark mb-0.5" style="font-size: 11.5px; line-height: 1.2;">{{ $creatorName ?: 'Shakil Masud' }}</div>
                        <div class="text-muted mb-1" style="font-size: 9.5px; line-height: 1.2;">{{ $creatorDesignation ?: 'CEO & Publisher' }}</div>
                        <div class="border-top border-dark mb-1" style="width: 140px;"></div>
                        <div class="text-muted fw-semibold" style="font-size: 9.5px; text-transform: uppercase; line-height: 1.3;">Collected By:</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center text-muted mt-2 pt-1.5 border-top" style="font-size: 9.5px;">
            Thank you! This is an official computer-generated receipt from {{ $settings['business_name'] ?? 'Idea Publication' }}.
            @if(!empty($settings['phone']))
                <span class="ms-1.5"><i class="fas fa-phone-alt me-1"></i>{{ $settings['phone'] }}</span>
            @endif
        </div>
    </div>
</div>
@endsection
