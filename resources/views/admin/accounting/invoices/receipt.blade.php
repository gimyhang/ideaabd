@extends('layouts.admin')

@php
    $settings = $invoiceSettings ?? \App\Http\Controllers\Admin\IdeaAccountingController::getInvoiceSettings();
    $bizLogo = $settings['logo'] ?? '/images/logo.png';
    $logoSrc = \App\Support\SiteSetting::resolveImageUrl($bizLogo, 'images/logo.png') ?: asset('images/logo.png');

    $docTitle = 'Receipt #' . $payment->payment_no;
    $invoice = $payment->invoice;

    // Calculate previous payments prior to this one for clean statement breakdown
    $prevPaid = 0.0;
    if ($invoice) {
        $prevPaid = (float) $invoice->payments()
            ->where('id', '<', $payment->id)
            ->sum('amount');
    }
    $thisAmount = (float) $payment->amount;
    $totalGrand = $invoice ? (float)$invoice->grand_total : $thisAmount;
    $remainingDue = $invoice ? max(0, $totalGrand - ($prevPaid + $thisAmount)) : 0.0;

    $creatorName = !empty($settings['default_creator_name']) ? $settings['default_creator_name'] : ($invoice?->creator_name ?? auth()->user()->name ?? 'Idea Publication Authority');
    $creatorDesignation = !empty($settings['default_creator_designation']) ? $settings['default_creator_designation'] : ($invoice?->creator_designation_en ?? 'Authorized Cashier / Accountant');
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
        {{-- View Switcher Buttons --}}
        <div class="btn-group btn-group-sm p-0.5 bg-light rounded-pill border shadow-2xs" role="group">
            <button type="button" class="btn btn-sm rounded-pill px-3 fw-bold active" id="btnShowBn" onclick="switchReceiptView('bn')">
                <i class="fas fa-certificate me-1 text-success"></i> বাংলা
            </button>
            <button type="button" class="btn btn-sm rounded-pill px-3 fw-bold text-muted" id="btnShowEn" onclick="switchReceiptView('en')">
                <i class="fas fa-file-invoice me-1 text-primary"></i> English
            </button>
            <button type="button" class="btn btn-sm rounded-pill px-3 fw-bold text-muted" id="btnShowBoth" onclick="switchReceiptView('both')">
                <i class="fas fa-layer-group me-1 text-secondary"></i> Both
            </button>
        </div>

        <button type="button" class="btn btn-primary btn-sm rounded-pill px-3.5 shadow-sm fw-semibold" onclick="window.print()">
            <i class="fas fa-print me-1.5"></i> Print / PDF
        </button>

        @if($invoice)
            <a href="{{ route('admin.accounting.invoices.show', $invoice->id) }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-xs">
                <i class="fas fa-arrow-left me-1"></i> Invoice
            </a>
        @endif

        <a href="{{ route('admin.accounting.customer-ledger.index', ['customer_name' => $payment->party_name, 'customer_phone' => $payment->party_phone]) }}" class="btn btn-outline-info text-dark btn-sm rounded-pill px-3 shadow-xs fw-semibold">
            <i class="fas fa-book-bookmark me-1 text-primary"></i> Ledger
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
        border-radius: 12px;
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
        font-size: 12.5px;
        letter-spacing: 0.5px;
    }
    .cert-box-bn {
        background: #fcfdfc;
        border: 1.5px solid #10b981 !important;
        border-radius: 10px;
        padding: 14px 16px;
        margin-bottom: 12px;
    }
    .cert-box-en {
        background: #f8fafc;
        border: 1.5px solid #0284c7 !important;
        border-radius: 10px;
        padding: 14px 16px;
        margin-bottom: 12px;
    }
    .cert-fill-underline {
        display: inline-block;
        border-bottom: 1.5px dashed #334155;
        padding: 0 4px 1px 4px;
        font-weight: 700;
    }
    .amount-highlight-box {
        background: #f0fdf4;
        border: 1.5px solid #bbf7d0;
        border-radius: 10px;
        padding: 12px 18px;
        margin-bottom: 12px;
    }
    .signature-section {
        padding-top: 14px;
        margin-top: 12px;
        border-top: 1px solid #e2e8f0;
    }
    .receipt-meta-table td {
        padding: 4px 8px !important;
        font-size: 12px;
        vertical-align: middle;
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
        .cert-box-bn, .cert-box-en {
            padding: 8px 12px !important;
            margin-bottom: 8px !important;
            font-size: 11px !important;
            line-height: 1.4 !important;
        }
        .amount-highlight-box {
            padding: 8px 12px !important;
            margin-bottom: 8px !important;
        }
        .signature-section {
            padding-top: 12px !important;
            margin-top: 8px !important;
        }
        .receipt-meta-table td {
            padding: 2px 6px !important;
            font-size: 10.5px !important;
        }
    }
</style>

<div class="container-fluid py-2">
    <div class="receipt-paper">
        {{-- Header & Branding --}}
        <div class="row align-items-center pb-2 mb-2 border-bottom">
            <div class="col-7">
                <div class="d-flex align-items-center gap-2.5">
                    @if(!empty($logoSrc))
                        <img src="{{ $logoSrc }}" alt="Logo" style="height: 42px; max-width: 130px; object-fit: contain;">
                    @endif
                    <div>
                        <h5 class="fw-bold mb-0 text-dark" style="font-size: 17px;">{{ $settings['business_name'] ?? 'আইডিয়া প্রকাশন' }}</h5>
                        @if(!empty($settings['tagline']))
                            <div class="text-muted small" style="font-size: 11px;">{{ $settings['tagline'] }}</div>
                        @endif
                        <div class="text-secondary small mt-0.5" style="font-size: 10.5px;">
                            {{ $settings['address'] ?? '' }}
                            @if(!empty($settings['phone'])) | Phone: {{ $settings['phone'] }} @endif
                            @if(!empty($settings['email'])) | Email: {{ $settings['email'] }} @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-5 text-end">
                <div class="receipt-title-badge mb-0.5">
                    <i class="fas fa-receipt me-1"></i> Payment Receipt
                </div>
                <div class="fw-bold text-dark fs-6 font-monospace">#{{ $payment->payment_no }}</div>
                <div class="text-muted small" style="font-size: 11px;">Date: <strong class="text-dark">{{ $payment->payment_date ? $payment->payment_date->format('d M, Y') : date('d M, Y') }}</strong></div>
            </div>
        </div>

        {{-- SECTION 1: বাংলা প্রত্যয়নপত্র --}}
        <div id="sectionBnCert" class="cert-box-bn position-relative">
            <div class="p-2.5 bg-white rounded-2 border mb-2 text-dark lh-base" style="font-size: 12.5px; text-align: justify;">
                @if($payment->has_deductions)
                    এতদ্বারা প্রত্যয়ন করা যাচ্ছে যে, 
                    <span class="cert-fill-underline text-primary">
                        {{ $invoice?->customer_org ? $invoice->customer_org . ' (প্রতিনিধি: ' . $payment->party_name . ')' : $payment->party_name }}
                    </span>-এর 
                    নিকট হতে 
                    <span class="cert-fill-underline text-dark">
                        {{ $invoice?->subject ?: ($invoice?->category_label ?? 'বই প্রকাশনা ও সরবরাহ') }}
                    </span> 
                    বাবদ বিলের মোট অর্থ 
                    <span class="cert-fill-underline text-primary font-monospace">৳{{ number_format($thisAmount, 2) }}</span> 
                    (কথায়: <span class="cert-fill-underline text-primary">@takaInWords($thisAmount) টাকা मात्र</span>) 
                    সমন্বয়পূর্বক সরকারি কোষাগারে জমাকৃত উৎসে ভ্যাট ও ট্যাক্স কর্তন বাবদ 
                    <span class="cert-fill-underline text-danger font-monospace">৳{{ number_format($payment->total_deductions, 2) }}</span> 
                    বাদ দিয়ে নগদ/ব্যাংক চেক মারফত নিট 
                    <span class="cert-fill-underline text-success font-monospace">৳{{ number_format($payment->effective_net_amount, 2) }}</span> 
                    (কথায়: <span class="cert-fill-underline text-success">@takaInWords($payment->effective_net_amount) টাকা मात्र</span>) 
                    গ্রহণ করা হলো।
                @else
                    এতদ্বারা প্রত্যয়ন করা যাচ্ছে যে, 
                    <span class="cert-fill-underline text-primary">
                        {{ $invoice?->customer_org ? $invoice->customer_org . ' (প্রতিনিধি: ' . $payment->party_name . ')' : $payment->party_name }}
                    </span>-এর 
                    নিকট হতে 
                    <span class="cert-fill-underline text-dark">
                        {{ $invoice?->subject ?: ($invoice?->category_label ?? 'বই প্রকাশনা ও সরবরাহ') }}
                    </span> 
                    বাবদ বিলের অর্থ 
                    <span class="cert-fill-underline text-success font-monospace">৳{{ number_format($thisAmount, 2) }}</span> 
                    (কথায়: <span class="cert-fill-underline text-success">@takaInWords($thisAmount) টাকা मात्र</span>) গ্রহণ করা হলো।
                @endif
            </div>

            <div class="row g-2 p-2 bg-white rounded-2 border mb-2 receipt-meta-table" style="font-size: 11.5px;">
                <div class="col-sm-4 col-6">
                    <div class="d-flex align-items-baseline">
                        <span class="text-muted fw-semibold" style="width: 80px;">Invoice:</span>
                        <span class="fw-bold text-primary font-monospace">{{ $invoice ? $invoice->invoice_no : '—' }}</span>
                    </div>
                </div>
                <div class="col-sm-4 col-6">
                    <div class="d-flex align-items-baseline">
                        <span class="text-muted fw-semibold" style="width: 80px;">Bill Date:</span>
                        <span class="fw-bold text-dark">{{ $invoice && $invoice->invoice_date ? $invoice->invoice_date->format('d/m/Y') : '—' }}</span>
                    </div>
                </div>
                <div class="col-sm-4 col-6">
                    <div class="d-flex align-items-baseline">
                        <span class="text-muted fw-semibold" style="width: 80px;">Paid Date:</span>
                        <span class="fw-bold text-dark font-monospace">{{ $payment->payment_date ? $payment->payment_date->format('d/m/Y') : date('d/m/Y') }}</span>
                    </div>
                </div>
                <div class="col-sm-4 col-6">
                    <div class="d-flex align-items-baseline">
                        <span class="text-muted fw-semibold" style="width: 80px;">Method:</span>
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-0.5 fw-bold">
                            {{ \App\Models\IdeaInvoicePayment::paymentMethods()[$payment->payment_method] ?? ucfirst($payment->payment_method) }}
                        </span>
                    </div>
                </div>
                @if($payment->transaction_ref)
                    <div class="col-sm-4 col-6">
                        <div class="d-flex align-items-baseline">
                            <span class="text-muted fw-semibold" style="width: 80px;">Trx / Ref:</span>
                            <span class="fw-bold font-monospace text-dark">{{ $payment->transaction_ref }}</span>
                        </div>
                    </div>
                @endif
                <div class="col-sm-4 col-6">
                    <div class="d-flex align-items-baseline">
                        <span class="text-muted fw-semibold" style="width: 80px;">Tracking:</span>
                        <span class="fw-bold text-secondary font-monospace">#{{ $payment->payment_no }}</span>
                    </div>
                </div>
                @if($payment->deduction_challan_no)
                    <div class="col-12">
                        <div class="d-flex align-items-baseline">
                            <span class="text-muted fw-semibold" style="width: 80px;">Challan Ref:</span>
                            <span class="fw-bold text-dark font-monospace">{{ $payment->deduction_challan_no }} (ট্রেজারি চালান / মূসক-৬.৬)</span>
                        </div>
                    </div>
                @endif
            </div>

            <div class="p-1.5 rounded-2 bg-white border d-flex align-items-center justify-content-between flex-wrap gap-2">
                <p class="mb-0 text-dark fw-medium" style="font-size: 11.5px;">
                    <i class="fas fa-check-circle text-success me-1"></i>
                    উক্ত বিলের <strong class="text-success">{{ $remainingDue <= 0 ? 'সম্পূর্ণ' : 'আংশিক (কিস্তি)' }}</strong> অর্থ পরিশোধ ও সমন্বয়ের মাধ্যমে গ্রহণ করা হয়েছে।
                </p>
                <span class="badge {{ $remainingDue <= 0 ? 'bg-success text-white' : 'bg-warning text-dark' }} px-2 py-0.5 rounded-pill fw-bold" style="font-size: 10px;">
                    {{ $remainingDue <= 0 ? 'Paid in Full' : 'Partial Paid' }}
                </span>
            </div>
        </div>

        {{-- SECTION 2: English Certificate --}}
        <div id="sectionEnCert" class="cert-box-en position-relative d-none">
            <div class="p-2.5 bg-white rounded-2 border mb-2 text-dark lh-base" style="font-size: 12px; text-align: justify;">
                @if($payment->has_deductions)
                    This is to certify that an aggregate settlement of 
                    <span class="cert-fill-underline text-primary font-monospace">
                        BDT {{ number_format($thisAmount, 2) }}
                    </span> 
                    (in words: <span class="cert-fill-underline text-primary">@takaInWordsEn($thisAmount) Taka Only</span>) 
                    has been settled from 
                    <span class="cert-fill-underline text-dark">
                        {{ $invoice?->customer_org ? $invoice->customer_org . ' (Attn: ' . $payment->party_name . ')' : $payment->party_name }}
                    </span> 
                    on account of 
                    <span class="cert-fill-underline text-dark">
                        {{ $invoice?->subject ?: ($invoice?->category_label ?? 'Book Publication & Sales Supply') }}
                    </span>, 
                    comprising Net Realized Payment of 
                    <span class="cert-fill-underline text-success font-monospace">BDT {{ number_format($payment->effective_net_amount, 2) }}</span> 
                    and TDS/VDS deductions of 
                    <span class="cert-fill-underline text-danger font-monospace">BDT {{ number_format($payment->total_deductions, 2) }}</span>.
                @else
                    This is to certify that an amount of 
                    <span class="cert-fill-underline text-primary font-monospace">
                        BDT {{ number_format($thisAmount, 2) }}
                    </span> 
                    (in words: <span class="cert-fill-underline text-primary">@takaInWordsEn($thisAmount) Taka Only</span>) 
                    has been duly received from 
                    <span class="cert-fill-underline text-dark">
                        {{ $invoice?->customer_org ? $invoice->customer_org . ' (Attn: ' . $payment->party_name . ')' : $payment->party_name }}
                    </span> 
                    on account of 
                    <span class="cert-fill-underline text-dark">
                        {{ $invoice?->subject ?: ($invoice?->category_label ?? 'Book Publication & Sales Supply') }}
                    </span>.
                @endif
            </div>

            <div class="row g-2 p-2 bg-white rounded-2 border mb-2 receipt-meta-table" style="font-size: 11.5px;">
                <div class="col-sm-4 col-6">
                    <div class="d-flex align-items-baseline">
                        <span class="text-muted fw-semibold" style="width: 80px;">Invoice:</span>
                        <span class="fw-bold text-primary font-monospace">{{ $invoice ? $invoice->invoice_no : '—' }}</span>
                    </div>
                </div>
                <div class="col-sm-4 col-6">
                    <div class="d-flex align-items-baseline">
                        <span class="text-muted fw-semibold" style="width: 80px;">Bill Date:</span>
                        <span class="fw-bold text-dark">{{ $invoice && $invoice->invoice_date ? $invoice->invoice_date->format('d M, Y') : '—' }}</span>
                    </div>
                </div>
                <div class="col-sm-4 col-6">
                    <div class="d-flex align-items-baseline">
                        <span class="text-muted fw-semibold" style="width: 80px;">Receipt Date:</span>
                        <span class="fw-bold text-dark font-monospace">{{ $payment->payment_date ? $payment->payment_date->format('d M, Y') : date('d M, Y') }}</span>
                    </div>
                </div>
                <div class="col-sm-4 col-6">
                    <div class="d-flex align-items-baseline">
                        <span class="text-muted fw-semibold" style="width: 80px;">Method:</span>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-0.5 fw-bold">
                            {{ ucfirst($payment->payment_method) }}
                        </span>
                    </div>
                </div>
                @if($payment->transaction_ref)
                    <div class="col-sm-4 col-6">
                        <div class="d-flex align-items-baseline">
                            <span class="text-muted fw-semibold" style="width: 80px;">Trx / Ref:</span>
                            <span class="fw-bold font-monospace text-dark">{{ $payment->transaction_ref }}</span>
                        </div>
                    </div>
                @endif
                <div class="col-sm-4 col-6">
                    <div class="d-flex align-items-baseline">
                        <span class="text-muted fw-semibold" style="width: 80px;">Tracking:</span>
                        <span class="fw-bold text-secondary font-monospace">#{{ $payment->payment_no }}</span>
                    </div>
                </div>
                @if($payment->deduction_challan_no)
                    <div class="col-12">
                        <div class="d-flex align-items-baseline">
                            <span class="text-muted fw-semibold" style="width: 80px;">Challan Ref:</span>
                            <span class="fw-bold text-dark font-monospace">{{ $payment->deduction_challan_no }} (Treasury / Mushak 6.6)</span>
                        </div>
                    </div>
                @endif
            </div>

            <div class="p-1.5 rounded-2 bg-white border d-flex align-items-center justify-content-between flex-wrap gap-2">
                <p class="mb-0 text-dark fw-medium" style="font-size: 11.5px;">
                    <i class="fas fa-check-circle text-primary me-1"></i>
                    Payment accepted as <strong class="text-primary">{{ $remainingDue <= 0 ? 'Full Settlement' : 'Partial Installment' }}</strong>.
                </p>
                <span class="badge {{ $remainingDue <= 0 ? 'bg-success text-white' : 'bg-warning text-dark' }} px-2 py-0.5 rounded-pill fw-bold" style="font-size: 10px;">
                    {{ $remainingDue <= 0 ? 'Paid in Full' : 'Partial Installment' }}
                </span>
            </div>
        </div>

        {{-- SECTION 3: TDS / VDS DEDUCTIONS (IF PRESENT) --}}
        @if($payment->has_deductions)
            <div class="card border border-warning-subtle bg-warning-subtle bg-opacity-10 rounded-2 p-2 mb-2">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <span class="fw-bold text-dark" style="font-size: 11.5px;">
                        <i class="fas fa-scale-balanced text-warning-emphasis me-1"></i> TDS & VDS Deduction Breakdown
                    </span>
                    <span class="badge bg-warning text-dark border font-monospace" style="font-size: 9.5px;">TDS / VDS</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered bg-white align-middle mb-0" style="font-size: 11.5px;">
                        <thead class="table-light">
                            <tr>
                                <th class="py-1 px-2">Particulars</th>
                                <th class="text-center py-1 px-2" style="width: 80px;">Rate</th>
                                <th class="text-end py-1 px-2" style="width: 120px;">Amount (BDT)</th>
                                <th class="py-1 px-2">Ref / Note</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="py-1 px-2">
                                    <strong class="text-success"><i class="fas fa-money-bill-wave me-1"></i>Net Received (Cash / Cheque)</strong>
                                </td>
                                <td class="text-center py-1 px-2">—</td>
                                <td class="text-end font-monospace fw-bold text-success py-1 px-2">৳{{ number_format($payment->effective_net_amount, 2) }}</td>
                                <td class="text-muted small py-1 px-2">{{ $payment->transaction_ref ? 'Trx: ' . $payment->transaction_ref : 'Cash / Bank' }}</td>
                            </tr>
                            @if((float)$payment->vat_deduction_amount > 0)
                                <tr>
                                    <td class="py-1 px-2">VDS (VAT Deducted at Source)</td>
                                    <td class="text-center font-monospace py-1 px-2">{{ $payment->vat_deduction_rate ? $payment->vat_deduction_rate . '%' : '—' }}</td>
                                    <td class="text-end font-monospace text-danger fw-bold py-1 px-2">৳{{ number_format($payment->vat_deduction_amount, 2) }}</td>
                                    <td class="text-muted small py-1 px-2">Treasury Deposit</td>
                                </tr>
                            @endif
                            @if((float)$payment->tax_deduction_amount > 0)
                                <tr>
                                    <td class="py-1 px-2">TDS (Tax Deducted at Source)</td>
                                    <td class="text-center font-monospace py-1 px-2">{{ $payment->tax_deduction_rate ? $payment->tax_deduction_rate . '%' : '—' }}</td>
                                    <td class="text-end font-monospace text-danger fw-bold py-1 px-2">৳{{ number_format($payment->tax_deduction_amount, 2) }}</td>
                                    <td class="text-muted small py-1 px-2">Treasury Deposit</td>
                                </tr>
                            @endif
                            @if((float)$payment->other_deduction_amount > 0)
                                <tr>
                                    <td class="py-1 px-2">Other Retention / Deduction</td>
                                    <td class="text-center py-1 px-2">—</td>
                                    <td class="text-end font-monospace text-danger fw-bold py-1 px-2">৳{{ number_format($payment->other_deduction_amount, 2) }}</td>
                                    <td class="text-muted small py-1 px-2">Contractual</td>
                                </tr>
                            @endif
                        </tbody>
                        <tfoot class="table-light fw-bold">
                            <tr>
                                <td class="py-1 px-2">Total Settled Credit</td>
                                <td class="text-center text-danger font-monospace py-1 px-2">Deduction: ৳{{ number_format($payment->total_deductions, 2) }}</td>
                                <td class="text-end font-monospace text-primary py-1 px-2">৳{{ number_format($thisAmount, 2) }}</td>
                                <td class="text-dark small py-1 px-2">{{ $payment->deduction_challan_no ? 'Challan: ' . $payment->deduction_challan_no : 'Approved' }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        @endif

        {{-- SECTION 4: Financial Breakdown Card --}}
        <div class="amount-highlight-box mb-2">
            <div class="row align-items-center">
                <div class="col-md-6 border-end-md">
                    <div class="text-success small fw-bold text-uppercase mb-0.5" style="font-size: 11px;">
                        <i class="fas fa-hand-holding-dollar me-1"></i> Settlement Amount
                    </div>
                    <div class="fs-3 fw-bold text-success font-monospace mb-0.5">
                        ৳{{ number_format($thisAmount, 2) }}
                    </div>
                    <div class="text-muted small" style="font-size: 11.5px;">
                        In Words: <strong class="text-dark">@takaInWords($thisAmount) টাকা মাত্র</strong>
                    </div>
                    @if($payment->has_deductions)
                        <div class="mt-1 pt-1 border-top border-success-subtle d-flex gap-1.5 flex-wrap" style="font-size: 10.5px;">
                            <span class="badge bg-white text-success border border-success font-monospace">Net: ৳{{ number_format($payment->effective_net_amount, 2) }}</span>
                            <span class="badge bg-white text-danger border border-danger font-monospace">TDS/VDS: ৳{{ number_format($payment->total_deductions, 2) }}</span>
                        </div>
                    @endif
                </div>

                <div class="col-md-6 ps-md-3">
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
                    <div class="d-flex justify-content-between py-0.5 small text-success fw-bold" style="font-size: 11.5px;">
                        <span>Current Settled:</span>
                        <span class="font-monospace">৳{{ number_format($thisAmount, 2) }}</span>
                    </div>
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
                            <span class="text-muted fw-bold me-1"><i class="fas fa-comment-dots me-1"></i>Note:</span>
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
                <div class="col-4">
                    <div class="border-top border-dark pt-1 mx-auto" style="width: 140px;">
                        <div class="small fw-semibold text-dark" style="font-size: 11px;">Customer Signature</div>
                        <div class="text-muted" style="font-size: 9.5px;">{{ $payment->party_name }}</div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="text-muted small" style="font-size: 10px; line-height: 1.3;">
                        Collected By: <strong class="text-dark">{{ $payment->recorder?->name ?? 'Admin' }}</strong><br>
                        Issue Date: {{ date('d/m/Y h:i A') }}
                    </div>
                </div>
                <div class="col-4">
                    <div class="border-top border-dark pt-1 mx-auto" style="width: 150px;">
                        <div class="small fw-bold text-dark" style="font-size: 11px;">{{ $creatorName }}</div>
                        <div class="text-muted" style="font-size: 9.5px;">{{ $creatorDesignation }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center text-muted mt-2 pt-1.5 border-top" style="font-size: 10px;">
            Thank you! For any queries, please call {{ $settings['phone'] ?? '' }}
        </div>
    </div>
</div>

<script>
function switchReceiptView(lang) {
    const secBn = document.getElementById('sectionBnCert');
    const secEn = document.getElementById('sectionEnCert');
    const btnBn = document.getElementById('btnShowBn');
    const btnEn = document.getElementById('btnShowEn');
    const btnBoth = document.getElementById('btnShowBoth');

    if (!secBn || !secEn) return;

    btnBn.classList.remove('active', 'btn-primary', 'btn-success', 'bg-white', 'shadow-xs');
    btnEn.classList.remove('active', 'btn-primary', 'btn-success', 'bg-white', 'shadow-xs');
    btnBoth.classList.remove('active', 'btn-primary', 'btn-success', 'bg-white', 'shadow-xs');

    if (lang === 'bn') {
        secBn.classList.remove('d-none');
        secEn.classList.add('d-none');
        btnBn.classList.add('active', 'bg-white', 'shadow-xs');
    } else if (lang === 'en') {
        secBn.classList.add('d-none');
        secEn.classList.remove('d-none');
        btnEn.classList.add('active', 'bg-white', 'shadow-xs');
    } else {
        secBn.classList.remove('d-none');
        secEn.classList.remove('d-none');
        btnBoth.classList.add('active', 'bg-white', 'shadow-xs');
    }
}
</script>
@endsection
