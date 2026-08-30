@extends('layouts.admin')

@php
    $settings = $invoiceSettings ?? \App\Http\Controllers\Admin\IdeaAccountingController::getInvoiceSettings();
    $bizLogo = $settings['logo'] ?? '/images/logo.png';
    $logoSrc = \App\Support\SiteSetting::resolveImageUrl($bizLogo, 'images/logo.png') ?: asset('images/logo.png');

    $docTitle = 'Money Receipt #' . $payment->payment_no;
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
@section('heading', 'টাকা প্রাপ্তি রসিদ (Money Receipt)')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.accounting.index') }}">Accounting</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.accounting.invoices.index') }}">Invoices & Challans</a></li>
    @if($invoice)
        <li class="breadcrumb-item"><a href="{{ route('admin.accounting.invoices.show', $invoice->id) }}">#{{ $invoice->invoice_no }}</a></li>
    @endif
    <li class="breadcrumb-item active" aria-current="page">#{{ $payment->payment_no }}</li>
@endsection

@section('actions')
    <div class="d-flex flex-wrap gap-2">
        <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm fw-semibold" onclick="window.print()">
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
        max-width: 820px;
        margin: 0 auto;
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06);
        padding: 40px 48px;
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
        font-size: 85px;
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
        padding: 6px 20px;
        border-radius: 999px;
        font-weight: 700;
        font-size: 14px;
        letter-spacing: 0.5px;
    }
    .info-kv-row {
        display: flex;
        align-items: baseline;
        padding: 6px 0;
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
        font-size: 13.5px;
        flex-grow: 1;
    }
    .amount-highlight-box {
        background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
        border: 2px solid #bbf7d0;
        border-radius: 12px;
        padding: 16px 24px;
    }
    @media print {
        body {
            background: #ffffff !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        .main-header, .sidebar, .breadcrumb, .btn, .no-print, footer, nav, .alert {
            display: none !important;
        }
        .content-wrapper, .container-fluid, .content {
            padding: 0 !important;
            margin: 0 !important;
            background: #ffffff !important;
        }
        .receipt-paper {
            box-shadow: none !important;
            padding: 20px 24px !important;
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
                        <img src="{{ $logoSrc }}" alt="Logo" style="height: 52px; max-width: 160px; object-fit: contain;">
                    @endif
                    <div>
                        <h4 class="fw-bold mb-0 text-dark">{{ $settings['business_name'] ?? 'আইডিয়া প্রকাশন' }}</h4>
                        @if(!empty($settings['tagline']))
                            <div class="text-muted small fw-medium">{{ $settings['tagline'] }}</div>
                        @endif
                        <div class="text-secondary small mt-0.5" style="font-size: 12px;">
                            {{ $settings['address'] ?? '' }}
                            @if(!empty($settings['phone'])) | ফোন: {{ $settings['phone'] }} @endif
                            @if(!empty($settings['email'])) | ইমেইল: {{ $settings['email'] }} @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-4 text-end">
                <div class="receipt-title-badge mb-1">
                    <i class="fas fa-receipt me-1"></i> টাকা প্রাপ্তি রসিদ
                </div>
                <div class="fw-bold text-dark fs-6 font-monospace">#{{ $payment->payment_no }}</div>
                <div class="text-muted small">তারিখ: <strong class="text-dark">{{ $payment->payment_date ? $payment->payment_date->format('d M, Y') : date('d M, Y') }}</strong></div>
            </div>
        </div>

        {{-- Party & Reference Details --}}
        <div class="row g-3 mb-4">
            <div class="col-md-7">
                <div class="bg-light p-3 rounded-3 border">
                    <div class="text-muted small fw-bold text-uppercase mb-2 text-primary" style="font-size: 11px; letter-spacing: 0.5px;">
                        <i class="fas fa-user me-1"></i> গ্রাহক / প্রতিনিধির বিবরণ
                    </div>
                    <div class="info-kv-row">
                        <span class="info-kv-label">গ্রাহকের নাম:</span>
                        <span class="info-kv-val text-dark fs-6">{{ $payment->party_name }}</span>
                    </div>
                    @if($invoice && !empty($invoice->customer_org))
                        <div class="info-kv-row">
                            <span class="info-kv-label">প্রতিষ্ঠান / স্কুল:</span>
                            <span class="info-kv-val">{{ $invoice->customer_org }}</span>
                        </div>
                    @endif
                    @if($invoice && !empty($invoice->customer_designation))
                        <div class="info-kv-row">
                            <span class="info-kv-label">পদবি:</span>
                            <span class="info-kv-val">{{ $invoice->customer_designation }}</span>
                        </div>
                    @endif
                    <div class="info-kv-row">
                        <span class="info-kv-label">মোবাইল নম্বর:</span>
                        <span class="info-kv-val font-monospace">{{ $payment->party_phone }}</span>
                    </div>
                    @if($invoice && !empty($invoice->customer_address))
                        <div class="info-kv-row border-bottom-0 pb-0">
                            <span class="info-kv-label">ঠিকানা:</span>
                            <span class="info-kv-val">{{ $invoice->customer_address }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <div class="col-md-5">
                <div class="bg-light p-3 rounded-3 border h-100">
                    <div class="text-muted small fw-bold text-uppercase mb-2 text-primary" style="font-size: 11px; letter-spacing: 0.5px;">
                        <i class="fas fa-file-invoice me-1"></i> সম্পর্কিত বিলের তথ্য
                    </div>
                    @if($invoice)
                        <div class="info-kv-row">
                            <span class="info-kv-label">ইনভয়েস নম্বর:</span>
                            <span class="info-kv-val">
                                <a href="{{ route('admin.accounting.invoices.show', $invoice->id) }}" class="text-primary text-decoration-none fw-bold font-monospace">
                                    #{{ $invoice->invoice_no }}
                                </a>
                            </span>
                        </div>
                        <div class="info-kv-row">
                            <span class="info-kv-label">ডকুমেন্ট টাইপ:</span>
                            <span class="info-kv-val">{{ $invoice->type_label }}</span>
                        </div>
                        <div class="info-kv-row">
                            <span class="info-kv-label">বিলের তারিখ:</span>
                            <span class="info-kv-val">{{ $invoice->invoice_date ? $invoice->invoice_date->format('d M, Y') : '—' }}</span>
                        </div>
                        <div class="info-kv-row border-bottom-0 pb-0">
                            <span class="info-kv-label">মোট বিলের দাবি:</span>
                            <span class="info-kv-val font-monospace text-dark">৳{{ number_format($invoice->grand_total, 2) }}</span>
                        </div>
                    @else
                        <div class="text-muted small py-3 text-center">
                            <em>চলতি খাতা / অগ্রিম জমা (সাধারণ জমা)</em>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Highlighted Payment Box --}}
        <div class="amount-highlight-box mb-4">
            <div class="row align-items-center">
                <div class="col-md-7">
                    <div class="text-success small fw-bold text-uppercase mb-1">
                        <i class="fas fa-hand-holding-dollar me-1"></i> প্রাপ্ত টাকার পরিমাণ
                    </div>
                    <div class="fs-2 fw-bold text-success font-monospace">
                        ৳{{ number_format($thisAmount, 2) }}
                    </div>
                    <div class="text-muted small mt-1">
                        <strong>পেমেন্ট মাধ্যম:</strong> 
                        <span class="badge bg-white text-dark border px-2 py-1">{{ IdeaInvoicePayment::paymentMethods()[$payment->payment_method] ?? ucfirst($payment->payment_method) }}</span>
                        @if($payment->transaction_ref)
                            <span class="ms-2">| Trx Ref: <strong class="font-monospace text-dark">{{ $payment->transaction_ref }}</strong></span>
                        @endif
                    </div>
                </div>

                <div class="col-md-5 border-start-md ps-md-4">
                    <div class="text-muted small fw-bold text-uppercase mb-1" style="font-size: 11px;">
                        হিসাবের বর্তমান জের (Payment Breakdown)
                    </div>
                    <div class="d-flex justify-content-between py-0.5 small">
                        <span class="text-muted">মোট বিল:</span>
                        <span class="fw-semibold font-monospace">৳{{ number_format($totalGrand, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-0.5 small">
                        <span class="text-muted">পূর্বের মোট জমা:</span>
                        <span class="fw-semibold font-monospace">৳{{ number_format($prevPaid, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-0.5 small text-success fw-bold">
                        <span>বর্তমান জমা:</span>
                        <span class="font-monospace">৳{{ number_format($thisAmount, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-1 mt-1 border-top fw-bold {{ $remainingDue > 0 ? 'text-danger' : 'text-success' }}">
                        <span>বর্তমান বকেয়া জের:</span>
                        <span class="font-monospace fs-6">৳{{ number_format($remainingDue, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Note & Next Due Date --}}
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
                        <i class="fas fa-calendar-day me-1"></i> পরবর্তী কিস্তির তারিখ (ঐচ্ছিক): 
                        <strong class="text-danger font-monospace">{{ $invoice->due_date->format('d M, Y') }}</strong>
                    </div>
                @elseif($remainingDue <= 0)
                    <div class="p-2 bg-success-subtle rounded-3 border border-success-subtle text-success small fw-bold d-inline-block">
                        <i class="fas fa-check-circle me-1"></i> সম্পূর্ণ বিল পরিশোধিত (Paid in Full)
                    </div>
                @endif
            </div>
        </div>

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
@endsection
