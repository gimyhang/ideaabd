@extends('layouts.admin')

@php
    $settings = $invoiceSettings ?? \App\Http\Controllers\Admin\IdeaAccountingController::getInvoiceSettings();
    $bizLogo = $settings['logo'] ?? '/images/logo.png';
    $logoSrc = \App\Support\SiteSetting::resolveImageUrl($bizLogo, 'images/logo.png') ?: asset('images/logo.png');

    $docTitle = 'বিল পরিশোধ প্রাপ্তিস্বীকারপত্র #' . $payment->payment_no;
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
@section('heading', 'বিল পরিশোধ প্রাপ্তিস্বীকারপত্র (Payment Acknowledgment)')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.accounting.index') }}">Accounting</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.accounting.invoices.index') }}">Invoices & Challans</a></li>
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
                <i class="fas fa-certificate me-1 text-success"></i> বাংলা প্রত্যয়ন
            </button>
            <button type="button" class="btn btn-sm rounded-pill px-3 fw-bold text-muted" id="btnShowEn" onclick="switchReceiptView('en')">
                <i class="fas fa-file-invoice me-1 text-primary"></i> English Version
            </button>
            <button type="button" class="btn btn-sm rounded-pill px-3 fw-bold text-muted" id="btnShowBoth" onclick="switchReceiptView('both')">
                <i class="fas fa-layer-group me-1 text-secondary"></i> উভয় সংস্করণ
            </button>
        </div>

        <button type="button" class="btn btn-primary btn-sm rounded-pill px-3.5 shadow-sm fw-semibold" onclick="window.print()">
            <i class="fas fa-print me-1.5"></i> প্রিন্ট / PDF
        </button>

        @if($invoice)
            <a href="{{ route('admin.accounting.invoices.show', $invoice->id) }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-xs">
                <i class="fas fa-arrow-left me-1"></i> ইনভয়েসে ফিরে যান
            </a>
        @endif

        <a href="{{ route('admin.accounting.customer-ledger.index', ['customer_name' => $payment->party_name, 'customer_phone' => $payment->party_phone]) }}" class="btn btn-outline-info text-dark btn-sm rounded-pill px-3 shadow-xs fw-semibold">
            <i class="fas fa-book-bookmark me-1 text-primary"></i> গ্রাহক খতিয়ান
        </a>
    </div>
@endsection

@section('content')
<style>
    .receipt-paper {
        max-width: 860px;
        margin: 0 auto;
        background: #ffffff;
        border-radius: 14px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06);
        padding: 36px 44px;
        color: #1e293b;
        position: relative;
        overflow: hidden;
    }
    .receipt-paper::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 6px;
        background: linear-gradient(90deg, #10b981 0%, #059669 50%, #047857 100%);
    }
    .receipt-watermark {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%) rotate(-25deg);
        font-size: 80px;
        font-weight: 900;
        color: rgba(16, 185, 129, 0.04);
        pointer-events: none;
        user-select: none;
        white-space: nowrap;
        text-transform: uppercase;
        letter-spacing: 6px;
    }
    .receipt-title-badge {
        display: inline-block;
        background: #f0fdf4;
        color: #166534;
        border: 1.5px dashed #86efac;
        padding: 6px 18px;
        border-radius: 999px;
        font-weight: 700;
        font-size: 13.5px;
        letter-spacing: 0.5px;
    }
    .cert-box-bn {
        background: linear-gradient(135deg, #f0fdf4 0%, #ffffff 100%);
        border: 2px solid #10b981 !important;
        border-radius: 12px;
    }
    .cert-box-en {
        background: linear-gradient(135deg, #f0f9ff 0%, #ffffff 100%);
        border: 2px solid #0284c7 !important;
        border-radius: 12px;
    }
    .cert-fill-underline {
        display: inline-block;
        border-bottom: 1.5px dashed #334155;
        padding: 0 6px 1px 6px;
        font-weight: 700;
    }
    .info-kv-row {
        display: flex;
        align-items: baseline;
        padding: 5px 0;
        border-bottom: 1px dashed #e2e8f0;
    }
    .info-kv-label {
        width: 140px;
        color: #64748b;
        font-size: 13px;
        font-weight: 600;
        flex-shrink: 0;
    }
    .info-kv-val {
        color: #0f172a;
        font-weight: 600;
        font-size: 13px;
        flex-grow: 1;
    }
    .amount-highlight-box {
        background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
        border: 2px solid #bbf7d0;
        border-radius: 12px;
        padding: 16px 20px;
    }
    @media print {
        body {
            background: #ffffff !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        .adm-side, .adm-header, .adm-topbar, .breadcrumb, .btn, .no-print, footer, nav, .alert {
            display: none !important;
        }
        .content-wrapper, .container-fluid, .content, .adm-main, .adm-content {
            padding: 0 !important;
            margin: 0 !important;
            background: #ffffff !important;
        }
        .receipt-paper {
            box-shadow: none !important;
            padding: 16px 20px !important;
            margin: 0 auto !important;
            width: 100% !important;
            max-width: 100% !important;
            border: 1px solid #cbd5e1 !important;
        }
    }
</style>

<div class="container-fluid py-3">
    <div class="receipt-paper">
        <div class="receipt-watermark">PAID RECEIPT</div>

        {{-- Header & Branding --}}
        <div class="row align-items-center pb-3 mb-3 border-bottom">
            <div class="col-8">
                <div class="d-flex align-items-center gap-3">
                    @if(!empty($logoSrc))
                        <img src="{{ $logoSrc }}" alt="Logo" style="height: 52px; max-width: 150px; object-fit: contain;">
                    @endif
                    <div>
                        <h4 class="fw-bold mb-0 text-dark">{{ $settings['business_name'] ?? 'আইডিয়া প্রকাশন' }}</h4>
                        @if(!empty($settings['tagline']))
                            <div class="text-muted small fw-medium">{{ $settings['tagline'] }}</div>
                        @endif
                        <div class="text-secondary small mt-0.5" style="font-size: 11.5px;">
                            {{ $settings['address'] ?? '' }}
                            @if(!empty($settings['phone'])) | ফোন: {{ $settings['phone'] }} @endif
                            @if(!empty($settings['email'])) | ইমেইল: {{ $settings['email'] }} @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-4 text-end">
                <div class="receipt-title-badge mb-1">
                    <i class="fas fa-receipt me-1"></i> বিল পরিশোধ প্রাপ্তিস্বীকার
                </div>
                <div class="fw-bold text-dark fs-6 font-monospace">#{{ $payment->payment_no }}</div>
                <div class="text-muted small">তারিখ: <strong class="text-dark">{{ $payment->payment_date ? $payment->payment_date->format('d M, Y') : date('d M, Y') }}</strong></div>
            </div>
        </div>

        {{-- 🇧🇩 SECTION 1: বাংলা বিল পরিশোধ প্রাপ্তিস্বীকারপত্র (Certificate of Acknowledgment) --}}
        <div id="sectionBnCert" class="cert-box-bn p-4 mb-4 position-relative">
            <div class="text-center mb-3">
                <span class="badge bg-success text-white px-3 py-1.5 rounded-pill fw-bold text-uppercase fs-7 mb-1 shadow-2xs">
                    <i class="fas fa-file-shield me-1"></i> বিল পরিশোধ প্রাপ্তিস্বীকারপত্র
                </span>
                <div class="text-muted small">Certificate of Bill Payment & Money Receipt</div>
            </div>

            <div class="p-3 bg-white rounded-3 border mb-3 text-dark lh-lg" style="font-size: 14.5px; text-align: justify;">
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
                    (কথায়: <span class="cert-fill-underline text-primary">@takaInWords($thisAmount) টাকা মাত্র</span>) 
                    সমন্বয়পূর্বক গ্রাহক কর্তৃক সরকারি কোষাগারে জমাকৃত উৎসে ভ্যাট ও ট্যাক্স কর্তন বাবদ 
                    <span class="cert-fill-underline text-danger font-monospace">৳{{ number_format($payment->total_deductions, 2) }}</span> 
                    বাদ দিয়ে নগদ/ব্যাংক চেক মারফত নিট 
                    <span class="cert-fill-underline text-success font-monospace">৳{{ number_format($payment->effective_net_amount, 2) }}</span> 
                    (কথায়: <span class="cert-fill-underline text-success">@takaInWords($payment->effective_net_amount) টাকা মাত্র</span>) 
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
                    (কথায়: <span class="cert-fill-underline text-success">@takaInWords($thisAmount) টাকা মাত্র</span>) গ্রহণ করা হলো।
                @endif
            </div>

            <div class="row g-2.5 p-3 bg-white rounded-3 border mb-3" style="font-size: 13px;">
                <div class="col-sm-6">
                    <div class="d-flex align-items-baseline">
                        <span class="text-muted fw-semibold" style="width: 130px;">বিল নং:</span>
                        <span class="fw-bold text-primary font-monospace">{{ $invoice ? $invoice->invoice_no : '—' }}</span>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="d-flex align-items-baseline">
                        <span class="text-muted fw-semibold" style="width: 130px;">বিলের তারিখ:</span>
                        <span class="fw-bold text-dark">{{ $invoice && $invoice->invoice_date ? $invoice->invoice_date->format('d/m/Y') : '—' }}</span>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="d-flex align-items-baseline">
                        <span class="text-muted fw-semibold" style="width: 130px;">পরিশোধের তারিখ:</span>
                        <span class="fw-bold text-dark font-monospace">{{ $payment->payment_date ? $payment->payment_date->format('d/m/Y') : date('d/m/Y') }}</span>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="d-flex align-items-baseline">
                        <span class="text-muted fw-semibold" style="width: 130px;">পরিশোধের মাধ্যম:</span>
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-0.5 fw-bold">
                            {{ \App\Models\IdeaInvoicePayment::paymentMethods()[$payment->payment_method] ?? ucfirst($payment->payment_method) }}
                        </span>
                    </div>
                </div>
                @if($payment->transaction_ref)
                    <div class="col-sm-6">
                        <div class="d-flex align-items-baseline">
                            <span class="text-muted fw-semibold" style="width: 130px;">চেক/Trx নং:</span>
                            <span class="fw-bold font-monospace text-dark">{{ $payment->transaction_ref }}</span>
                        </div>
                    </div>
                @endif
                <div class="col-sm-6">
                    <div class="d-flex align-items-baseline">
                        <span class="text-muted fw-semibold" style="width: 130px;">রসিদ ট্র্যাকিং:</span>
                        <span class="fw-bold text-secondary font-monospace">#{{ $payment->payment_no }}</span>
                    </div>
                </div>
                @if($payment->deduction_challan_no)
                    <div class="col-12">
                        <div class="d-flex align-items-baseline">
                            <span class="text-muted fw-semibold" style="width: 130px;">চালান/সনদ নং:</span>
                            <span class="fw-bold text-dark font-monospace">{{ $payment->deduction_challan_no }} (ট্রেজারি চালান / মূসক-৬.৬ প্রত্যয়নপত্র)</span>
                        </div>
                    </div>
                @endif
            </div>

            <div class="p-2.5 rounded-3 bg-white border d-flex align-items-center justify-content-between flex-wrap gap-2">
                <p class="mb-0 text-dark fw-medium" style="font-size: 13px;">
                    <i class="fas fa-check-circle text-success me-1"></i>
                    উক্ত বিলের <strong class="text-success">{{ $remainingDue <= 0 ? 'সম্পূর্ণ' : 'আংশিক (কিস্তি)' }}</strong> অর্থ পরিশোধ ও সমন্বয়ের মাধ্যমে গ্রহণ করা হয়েছে। এ বিষয়ে প্রাপ্তির স্বীকৃতিস্বরূপ এই পত্র প্রদান করা হলো।
                </p>
                <span class="badge {{ $remainingDue <= 0 ? 'bg-success text-white' : 'bg-warning text-dark' }} px-2.5 py-1 rounded-pill fw-bold">
                    {{ $remainingDue <= 0 ? 'সম্পূর্ণ পরিশোধিত (Paid in Full)' : 'আংশিক জমা (Partial Paid)' }}
                </span>
            </div>
        </div>

        {{-- 🇬🇧 SECTION 2: English Official Payment Acknowledgment Certificate --}}
        <div id="sectionEnCert" class="cert-box-en p-4 mb-4 position-relative d-none">
            <div class="text-center mb-3">
                <span class="badge bg-primary text-white px-3 py-1.5 rounded-pill fw-bold text-uppercase fs-7 mb-1 shadow-2xs">
                    <i class="fas fa-certificate me-1"></i> Official Payment Acknowledgment
                </span>
                <div class="text-muted small">Certificate of Bill Settlement & Money Receipt</div>
            </div>

            <div class="p-3 bg-white rounded-3 border mb-3 text-dark lh-lg" style="font-size: 14px; text-align: justify;">
                @if($payment->has_deductions)
                    This is to formally certify and acknowledge that an aggregate bill settlement of 
                    <span class="cert-fill-underline text-primary font-monospace">
                        BDT {{ number_format($thisAmount, 2) }}
                    </span> 
                    (in words: <span class="cert-fill-underline text-primary">@takaInWordsEn($thisAmount) Taka Only</span>) 
                    has been duly settled and accepted from 
                    <span class="cert-fill-underline text-dark">
                        {{ $invoice?->customer_org ? $invoice->customer_org . ' (Attn: ' . $payment->party_name . ')' : $payment->party_name }}
                    </span> 
                    on account of 
                    <span class="cert-fill-underline text-dark">
                        {{ $invoice?->subject ?: ($invoice?->category_label ?? 'Book Publication & Sales Supply') }}
                    </span>, 
                    comprising Net Realized Payment of 
                    <span class="cert-fill-underline text-success font-monospace">BDT {{ number_format($payment->effective_net_amount, 2) }}</span> 
                    and Tax & VAT Deductions at Source (TDS/VDS) amounting to 
                    <span class="cert-fill-underline text-danger font-monospace">BDT {{ number_format($payment->total_deductions, 2) }}</span> 
                    deposited to the Government Treasury on behalf of our organization.
                @else
                    This is to formally certify and acknowledge that an amount of 
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

            <div class="row g-2.5 p-3 bg-white rounded-3 border mb-3" style="font-size: 13px;">
                <div class="col-sm-6">
                    <div class="d-flex align-items-baseline">
                        <span class="text-muted fw-semibold" style="width: 140px;">Bill / Invoice No:</span>
                        <span class="fw-bold text-primary font-monospace">{{ $invoice ? $invoice->invoice_no : '—' }}</span>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="d-flex align-items-baseline">
                        <span class="text-muted fw-semibold" style="width: 140px;">Bill Date:</span>
                        <span class="fw-bold text-dark">{{ $invoice && $invoice->invoice_date ? $invoice->invoice_date->format('d M, Y') : '—' }}</span>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="d-flex align-items-baseline">
                        <span class="text-muted fw-semibold" style="width: 140px;">Receipt Date:</span>
                        <span class="fw-bold text-dark font-monospace">{{ $payment->payment_date ? $payment->payment_date->format('d M, Y') : date('d M, Y') }}</span>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="d-flex align-items-baseline">
                        <span class="text-muted fw-semibold" style="width: 140px;">Payment Mode:</span>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-0.5 fw-bold">
                            {{ ucfirst($payment->payment_method) }}
                        </span>
                    </div>
                </div>
                @if($payment->transaction_ref)
                    <div class="col-sm-6">
                        <div class="d-flex align-items-baseline">
                            <span class="text-muted fw-semibold" style="width: 140px;">Cheque / Trx ID:</span>
                            <span class="fw-bold font-monospace text-dark">{{ $payment->transaction_ref }}</span>
                        </div>
                    </div>
                @endif
                <div class="col-sm-6">
                    <div class="d-flex align-items-baseline">
                        <span class="text-muted fw-semibold" style="width: 140px;">Receipt Reference:</span>
                        <span class="fw-bold text-secondary font-monospace">#{{ $payment->payment_no }}</span>
                    </div>
                </div>
                @if($payment->deduction_challan_no)
                    <div class="col-12">
                        <div class="d-flex align-items-baseline">
                            <span class="text-muted fw-semibold" style="width: 140px;">Challan / Mushak Ref:</span>
                            <span class="fw-bold text-dark font-monospace">{{ $payment->deduction_challan_no }} (Treasury Challan / Mushak 6.6 Certificate)</span>
                        </div>
                    </div>
                @endif
            </div>

            <div class="p-2.5 rounded-3 bg-white border d-flex align-items-center justify-content-between flex-wrap gap-2">
                <p class="mb-0 text-dark fw-medium" style="font-size: 13px;">
                    <i class="fas fa-check-circle text-primary me-1"></i>
                    This payment has been accepted as <strong class="text-primary">{{ $remainingDue <= 0 ? 'Full & Final Payment' : 'Partial Installment Payment' }}</strong> against the aforementioned bill. This document is issued as a verified acknowledgment of receipt.
                </p>
                <span class="badge {{ $remainingDue <= 0 ? 'bg-success text-white' : 'bg-warning text-dark' }} px-2.5 py-1 rounded-pill fw-bold">
                    {{ $remainingDue <= 0 ? 'Paid in Full' : 'Partial Installment' }}
                </span>
            </div>
        </div>

        {{-- 🏛️ SECTION 3: VAT & TAX (TDS / VDS) DEDUCTION BREAKDOWN (IF APPLICABLE) --}}
        @if($payment->has_deductions)
            <div class="card border border-warning-subtle bg-warning-subtle bg-opacity-10 rounded-3 p-3 mb-4">
                <div class="d-flex align-items-center justify-content-between mb-2 pb-1 border-bottom border-warning-subtle">
                    <span class="fw-bold text-dark small">
                        <i class="fas fa-scale-balanced text-warning-emphasis me-1.5"></i>উৎসে কর ও মূসক (TDS & VDS) কর্তন ও বকেয়া সমন্বয় বিবরণী
                    </span>
                    <span class="badge bg-warning text-dark border font-monospace" style="font-size: 10.5px;">সমন্বিত সরকারি কর্তন</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered bg-white align-middle mb-1" style="font-size: 12.5px;">
                        <thead class="table-light">
                            <tr>
                                <th>বিবরণ (Particulars)</th>
                                <th class="text-center" style="width: 100px;">কর্তন হার (%)</th>
                                <th class="text-end" style="width: 150px;">টাকার পরিমাণ (BDT)</th>
                                <th>রেফারেন্স ও মন্তব্য</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <strong class="text-success"><i class="fas fa-money-bill-wave me-1"></i>নিট গৃহীত অর্থ (Net Cheque/Cash Received)</strong>
                                </td>
                                <td class="text-center">—</td>
                                <td class="text-end font-monospace fw-bold text-success">৳{{ number_format($payment->effective_net_amount, 2) }}</td>
                                <td class="text-muted small">{{ $payment->transaction_ref ? 'চেক/Trx: ' . $payment->transaction_ref : 'নগদ/ব্যাংক জমা' }}</td>
                            </tr>
                            @if((float)$payment->vat_deduction_amount > 0)
                                <tr>
                                    <td>
                                        <span class="text-dark">উৎসে কর্তনকৃত মূসক (VDS - VAT Deducted at Source)</span>
                                    </td>
                                    <td class="text-center font-monospace">{{ $payment->vat_deduction_rate ? $payment->vat_deduction_rate . '%' : '—' }}</td>
                                    <td class="text-end font-monospace text-danger fw-bold">৳{{ number_format($payment->vat_deduction_amount, 2) }}</td>
                                    <td class="text-muted small">গ্রাহক কর্তৃক সরকারি কোষাগারে জমাকৃত</td>
                                </tr>
                            @endif
                            @if((float)$payment->tax_deduction_amount > 0)
                                <tr>
                                    <td>
                                        <span class="text-dark">উৎসে কর্তনকৃত আয়কর (TDS - Tax/AIT Deducted at Source)</span>
                                    </td>
                                    <td class="text-center font-monospace">{{ $payment->tax_deduction_rate ? $payment->tax_deduction_rate . '%' : '—' }}</td>
                                    <td class="text-end font-monospace text-danger fw-bold">৳{{ number_format($payment->tax_deduction_amount, 2) }}</td>
                                    <td class="text-muted small">গ্রাহক কর্তৃক সরকারি কোষাগারে জমাকৃত</td>
                                </tr>
                            @endif
                            @if((float)$payment->other_deduction_amount > 0)
                                <tr>
                                    <td>
                                        <span class="text-dark">অন্যান্য সমন্বিত কর্তন (Other Deductions/Retention)</span>
                                    </td>
                                    <td class="text-center">—</td>
                                    <td class="text-end font-monospace text-danger fw-bold">৳{{ number_format($payment->other_deduction_amount, 2) }}</td>
                                    <td class="text-muted small">জামানত / চুক্তিভিত্তিক কর্তন</td>
                                </tr>
                            @endif
                        </tbody>
                        <tfoot class="table-light fw-bold">
                            <tr>
                                <td>মোট সমন্বিত বিলের অর্থ (Total Bill Settlement Credit)</td>
                                <td class="text-center text-danger font-monospace">মোট কর্তন: ৳{{ number_format($payment->total_deductions, 2) }}</td>
                                <td class="text-end font-monospace text-primary fs-7">৳{{ number_format($thisAmount, 2) }}</td>
                                <td class="text-dark small">{{ $payment->deduction_challan_no ? 'চালান/প্রত্যয়ন: ' . $payment->deduction_challan_no : 'সমন্বয় অনুমোদিত' }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @if($payment->deduction_notes)
                    <div class="text-muted small mt-1" style="font-size: 11.5px;">
                        <strong>কর্তন নোট:</strong> {{ $payment->deduction_notes }}
                    </div>
                @endif
            </div>
        @endif

        {{-- Financial Breakdown & Statement Card --}}
        <div class="amount-highlight-box mb-4">
            <div class="row align-items-center">
                <div class="col-md-6 border-end-md">
                    <div class="text-success small fw-bold text-uppercase mb-1">
                        <i class="fas fa-hand-holding-dollar me-1"></i> মোট সমন্বিত জমার পরিমাণ (Settled Amount)
                    </div>
                    <div class="fs-2 fw-bold text-success font-monospace mb-1">
                        ৳{{ number_format($thisAmount, 2) }}
                    </div>
                    <div class="text-muted small" style="font-size: 12px;">
                        কথায়: <strong class="text-dark">@takaInWords($thisAmount) টাকা মাত্র</strong>
                    </div>
                    @if($payment->has_deductions)
                        <div class="mt-2 pt-1.5 border-top border-success-subtle d-flex gap-2 flex-wrap" style="font-size: 11.5px;">
                            <span class="badge bg-white text-success border border-success font-monospace">নিট নগদ/চেক: ৳{{ number_format($payment->effective_net_amount, 2) }}</span>
                            <span class="badge bg-white text-danger border border-danger font-monospace">ভ্যাট/ট্যাক্স কর্তন: ৳{{ number_format($payment->total_deductions, 2) }}</span>
                        </div>
                    @endif
                </div>

                <div class="col-md-6 ps-md-4">
                    <div class="text-muted small fw-bold text-uppercase mb-1.5" style="font-size: 11px;">
                        হিসাবের বর্তমান জের (Payment Breakdown)
                    </div>
                    <div class="d-flex justify-content-between py-0.5 small">
                        <span class="text-muted">মোট বিলের দাবি:</span>
                        <span class="fw-semibold font-monospace">৳{{ number_format($totalGrand, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-0.5 small">
                        <span class="text-muted">পূর্বের মোট জমা:</span>
                        <span class="fw-semibold font-monospace">৳{{ number_format($prevPaid, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-0.5 small text-success fw-bold">
                        <span>বর্তমান মোট সমন্বয়:</span>
                        <span class="font-monospace">৳{{ number_format($thisAmount, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-1 mt-1 border-top fw-bold {{ $remainingDue > 0 ? 'text-danger' : 'text-success' }}">
                        <span>বর্তমান অবশিষ্ট বকেয়া জের:</span>
                        <span class="font-monospace fs-6">৳{{ number_format($remainingDue, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Note & Next Due Date --}}
        @if(!empty($payment->note) || ($invoice && $invoice->due_date && $remainingDue > 0))
            <div class="row g-3 mb-4">
                <div class="col-md-8">
                    @if(!empty($payment->note))
                        <div class="p-2.5 bg-light rounded-3 border">
                            <span class="text-muted small fw-bold me-2"><i class="fas fa-comment-dots me-1"></i>বিবরণ / নোট:</span>
                            <span class="small text-dark">{{ $payment->note }}</span>
                        </div>
                    @endif
                </div>

                <div class="col-md-4 text-md-end">
                    @if($invoice && $invoice->due_date && $remainingDue > 0)
                        <div class="p-2 bg-danger-subtle rounded-3 border border-danger-subtle text-danger small fw-semibold d-inline-block text-start">
                            <i class="fas fa-calendar-day me-1"></i> পরবর্তী কিস্তির তারিখ: 
                            <strong class="text-danger font-monospace">{{ $invoice->due_date->format('d M, Y') }}</strong>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- Signatures & Acknowledgement --}}
        <div class="pt-5 mt-4 border-top">
            <div class="row align-items-end text-center">
                <div class="col-4">
                    <div class="border-top border-dark pt-1 mx-auto" style="width: 170px;">
                        <div class="small fw-semibold text-dark">টাকা প্রদানকারীর স্বাক্ষর</div>
                        <div class="text-muted" style="font-size: 11px;">Customer Signature</div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="text-muted small" style="font-size: 11.5px;">
                        আদায়কারী: <strong class="text-dark">{{ $payment->recorder?->name ?? $creatorName }}</strong><br>
                        রসিদ ইস্যু তারিখ: {{ date('d/m/Y h:i A') }}
                    </div>
                </div>
                <div class="col-4">
                    <div class="border-top border-dark pt-1 mx-auto" style="width: 180px;">
                        <div class="small fw-bold text-dark">{{ $creatorName }}</div>
                        <div class="text-muted" style="font-size: 11px;">{{ $creatorDesignation }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center text-muted mt-4 pt-3 border-top" style="font-size: 11.5px;">
            ধন্যবাদ! আপনার যেকোনো প্রয়োজনে যোগাযোগ করুন — {{ $settings['phone'] ?? '' }}
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

