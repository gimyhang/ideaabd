@extends('layouts.admin')

@php
    $typeTitles = [
        'challan'   => 'ডেলিভারি চালান #',
        'quotation' => 'কোটেশন / প্রফর্মা #',
        'tender'    => 'দরপত্র প্রস্তাবনা #',
        'invoice'   => 'বিল ও চালান #'
    ];
    $docTitle = ($typeTitles[$invoice->type] ?? 'ইনভয়েস #') . $invoice->invoice_no;
    
    $settings = $invoiceSettings ?? \App\Http\Controllers\Admin\IdeaAccountingController::getInvoiceSettings();
    $bizLogo = $settings['logo'] ?? '/images/logo.png';
    $logoSrc = \App\Support\SiteSetting::resolveImageUrl($bizLogo, 'images/logo.png') ?: asset('images/logo.png');

    $invoiceUrl = route('admin.accounting.invoices.show', $invoice->id);
    $qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=130x130&margin=4&data=" . urlencode($invoiceUrl);

    $totalQuantity = 0;
    foreach($invoice->items ?? [] as $it) {
        $totalQuantity += (float)($it['quantity'] ?? 1);
    }
@endphp

@section('title', $docTitle)
@section('heading', $invoice->type_label . ($invoice->type === 'invoice' ? ' ও ডেলিভারি চালান' : ''))
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.accounting.index') }}">আইডিয়া হিসাব</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.accounting.invoices.index') }}">বিল, চালান ও দরপত্র</a></li>
    <li class="breadcrumb-item active" aria-current="page">#{{ $invoice->invoice_no }}</li>
@endsection

@section('actions')
    <div class="d-flex flex-wrap gap-2">
        <button type="button" class="btn btn-primary shadow-sm fw-semibold" onclick="window.print()">
            <i class="fas fa-print me-1.5"></i> প্রিন্ট কপি (Print / PDF)
        </button>

        {{-- Edit Document Button --}}
        <a href="{{ route('admin.accounting.invoices.edit', $invoice->id) }}" class="btn btn-warning text-dark fw-semibold shadow-sm">
            <i class="fas fa-edit me-1"></i> এডিট করুন
        </a>

        {{-- Customize Memo Header Settings Button --}}
        <button type="button" class="btn btn-outline-secondary shadow-sm" data-bs-toggle="modal" data-bs-target="#invoiceSettingsModal" title="বিল ও মেমোর তথ্য কাস্টমাইজ করুন">
            <i class="fas fa-palette me-1 text-primary"></i> মেমো ডিজাইন ও সেটিংস
        </button>

        {{-- Convert to Invoice/Challan if currently Quotation or Tender --}}
        @if(in_array($invoice->type, ['quotation', 'tender']))
            <form action="{{ route('admin.accounting.invoices.convert', $invoice->id) }}" method="POST" class="d-inline"
                  onsubmit="return confirm('আপনি কি এই দরপত্র/কোটেশনটিকে চূড়ান্ত বিল/ইনভয়েসে রূপান্তর করতে চান?')">
                @csrf
                <input type="hidden" name="target_type" value="invoice">
                <button type="submit" class="btn btn-success fw-semibold shadow-sm">
                    <i class="fas fa-receipt me-1"></i> বিলে রূপান্তর
                </button>
            </form>

            <form action="{{ route('admin.accounting.invoices.convert', $invoice->id) }}" method="POST" class="d-inline"
                  onsubmit="return confirm('আপনি কি এই দরপত্র/কোটেশনটিকে ডেলিভারি চালানে রূপান্তর করতে চান?')">
                @csrf
                <input type="hidden" name="target_type" value="challan">
                <button type="submit" class="btn btn-info text-white fw-semibold shadow-sm">
                    <i class="fas fa-truck me-1"></i> চালানে রূপান্তর
                </button>
            </form>
        @endif

        <a href="{{ route('admin.accounting.invoices.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> তালিকায় ফিরুন
        </a>
    </div>
@endsection

@section('content')

{{-- Idea Accounting Unified Navigation Bar --}}
<div class="card border-0 shadow-sm rounded-4 mb-4 bg-white d-print-none">
    <div class="card-body p-2 d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div class="nav nav-pills gap-1.5 flex-wrap">
            <a href="{{ route('admin.accounting.index') }}" 
               class="nav-link rounded-pill px-3.5 py-2 fw-semibold text-dark hover-bg-light">
                <i class="fas fa-scale-balanced me-1.5"></i> আয়-ব্যয় ও হিসাব খাতা
            </a>
            <a href="{{ route('admin.accounting.invoices.index') }}" 
               class="nav-link rounded-pill px-3.5 py-2 fw-semibold text-dark hover-bg-light">
                <i class="fas fa-file-invoice-dollar me-1.5"></i> বিল, চালান ও দরপত্র তালিকা
            </a>
            <a href="{{ route('admin.accounting.invoices.create') }}" 
               class="nav-link rounded-pill px-3.5 py-2 fw-semibold text-dark hover-bg-light">
                <i class="fas fa-file-circle-plus me-1.5"></i> নতুন বিল, চালান ও দরপত্র তৈরি
            </a>
        </div>

        @if($invoice->type === 'invoice')
            <div class="btn-group btn-group-sm">
                <button type="button" class="btn btn-outline-primary active" id="btnShowBoth" onclick="setViewMode('both')">
                    <i class="fas fa-file-lines me-1"></i>উভয় পেজ (বিল ও চালান)
                </button>
                <button type="button" class="btn btn-outline-primary" id="btnShowBill" onclick="setViewMode('bill')">
                    <i class="fas fa-receipt me-1"></i>১ম পেজ (বিল)
                </button>
                <button type="button" class="btn btn-outline-primary" id="btnShowChallan" onclick="setViewMode('challan')">
                    <i class="fas fa-truck me-1"></i>২য় পেজ (চালান)
                </button>
            </div>
        @endif
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-10">

        {{-- ========================================================================= --}}
        {{-- PAGE 1: CASH MEMO / INVOICE (or Quotation/Tender)                         --}}
        {{-- ========================================================================= --}}
        <div class="card border shadow-xs rounded-3 p-3 p-md-4 bg-white mb-3 invoice-page-card" id="pageBillMemo">
            
            {{-- Institutional / Company Header with 2:1 Wide Logo on the left --}}
            <div class="d-flex flex-wrap justify-content-between align-items-center border-bottom pb-2 mb-2 gap-2">
                <div class="d-flex align-items-center gap-3.5 invoice-brand-header">
                    <img src="{{ $logoSrc }}" alt="{{ $settings['business_name'] ?? 'আইডিয়া প্রকাশন' }}" 
                         class="img-fluid invoice-logo-img" style="height: 48px; width: 96px; aspect-ratio: 2/1; object-fit: contain; flex-shrink: 0; margin-right: 6px;">
                    <div class="d-flex flex-column justify-content-center" style="line-height: 1.35; padding-left: 2px;">
                        <div class="fw-bold text-primary invoice-brand-name" style="font-size: 15.5px; margin-bottom: 2px;">{{ $settings['business_name'] ?? 'আইডিয়া প্রকাশন' }}</div>
                        <div class="text-muted invoice-tagline" style="font-size: 10.5px; margin-bottom: 2px;">{{ $settings['tagline'] ?? 'বই প্রকাশনা, মুদ্রণ ও পরিবেশনা' }}</div>
                        <div class="text-muted invoice-contact-info" style="font-size: 10px;">
                            {{ $settings['address'] ?? 'ঢাকা, বাংলাদেশ' }} · মোবাইল: {{ $settings['phone'] ?? '018XXXXXXXX' }} · ইমেইল: {{ $settings['email'] ?? 'info@ideaabd.com' }}
                        </div>
                    </div>
                </div>

                <div class="text-md-end">
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
            <div class="row mb-2.5 p-2 bg-light rounded-2 border g-2 align-items-start" style="font-size: 12px;">
                <div class="col-7">
                    <div class="fw-bold text-dark mb-1" style="font-size: 12px;"><i class="fas fa-user-tag me-1 text-primary"></i>প্রাপক:</div>
                    <table class="table-borderless p-0 m-0 w-100" style="font-size: 12px; line-height: 1.45;">
                        @if($invoice->customer_name)
                            <tr>
                                <td class="text-muted pe-1 text-nowrap" style="width: 110px; vertical-align: top;">প্রাপক নাম:</td>
                                <td class="fw-bold text-dark">{{ $invoice->customer_name }}</td>
                            </tr>
                        @endif
                        @if(!empty($invoice->customer_designation))
                            <tr>
                                <td class="text-muted pe-1 text-nowrap" style="vertical-align: top;">পদবী:</td>
                                <td class="fw-semibold text-dark">{{ $invoice->customer_designation }}</td>
                            </tr>
                        @endif
                        @if($invoice->customer_org)
                            <tr>
                                <td class="text-muted pe-1 text-nowrap" style="vertical-align: top;">প্রতিষ্ঠানের নাম:</td>
                                <td class="fw-semibold text-primary">{{ $invoice->customer_org }}</td>
                            </tr>
                        @endif
                        @if($invoice->customer_address)
                            <tr>
                                <td class="text-muted pe-1 text-nowrap" style="vertical-align: top;">ঠিকানা:</td>
                                <td class="text-dark">{{ $invoice->customer_address }}</td>
                            </tr>
                        @endif
                        @if($invoice->customer_phone)
                            <tr>
                                <td class="text-muted pe-1 text-nowrap" style="vertical-align: top;">মোবাইল:</td>
                                <td class="text-dark fw-bold font-monospace">{{ $invoice->customer_phone }}</td>
                            </tr>
                        @endif
                    </table>
                </div>
                <div class="col-5 text-end">
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
                                · প্রস্তুতকারী: <strong>{{ $invoice->creator->name ?? 'অ্যাডমিন' }}</strong>
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
                            <th class="text-center py-1 px-1" style="width: 30px;">#</th>
                            <th class="py-1 px-1.5">বিবরণ / বই বা সেবার নাম</th>
                            <th class="text-center py-1 px-1" style="width: 70px;">ধরন</th>
                            <th class="text-center py-1 px-1" style="width: 55px;">পরিমাণ</th>
                            <th class="text-end py-1 px-1.5" style="width: 80px;">দর / একক (৳)</th>
                            <th class="text-end py-1 pe-1.5" style="width: 90px;">মোট টাকা (৳)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->items as $idx => $item)
                            <tr>
                                <td class="text-center py-0.5 px-1 text-muted">@bn($idx + 1)</td>
                                <td class="py-0.5 px-1.5">
                                    <span class="fw-semibold text-dark">{{ $item['title'] ?? '—' }}</span>
                                </td>
                                <td class="text-center py-0.5 px-1"><span class="badge bg-light text-dark border px-1 py-0" style="font-size: 8.5px;">{{ $item['item_type'] ?? 'বই' }}</span></td>
                                <td class="text-center py-0.5 px-1 fw-bold">@bn($item['quantity'] ?? 1)</td>
                                <td class="text-end py-0.5 px-1.5">@taka($item['unit_price'] ?? 0)</td>
                                <td class="text-end py-0.5 pe-1.5 fw-bold text-dark">@taka($item['subtotal'] ?? 0)</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="border-0 p-0"></td>
                            <td class="text-end py-0.5 px-1.5 fw-semibold">উপ-যোগফল:</td>
                            <td class="text-end py-0.5 pe-1.5 fw-semibold">@taka($invoice->subtotal)</td>
                        </tr>
                        @if($invoice->discount > 0)
                            <tr>
                                <td colspan="4" class="border-0 p-0"></td>
                                <td class="text-end py-0.5 px-1.5 text-danger fw-semibold">বিশেষ ছাড়:</td>
                                <td class="text-end py-0.5 pe-1.5 text-danger fw-semibold">- @taka($invoice->discount)</td>
                            </tr>
                        @endif
                        @if($invoice->tax > 0)
                            <tr>
                                <td colspan="4" class="border-0 p-0"></td>
                                <td class="text-end py-0.5 px-1.5 text-muted fw-semibold">ভ্যাট / ট্যাক্স:</td>
                                <td class="text-end py-0.5 pe-1.5 text-muted fw-semibold">+ @taka($invoice->tax)</td>
                            </tr>
                        @endif
                        <tr class="table-light">
                            <td colspan="4" class="border-0 p-0"></td>
                            <td class="text-end py-1 px-1.5 fw-bold text-dark">সর্বমোট বিল:</td>
                            <td class="text-end py-1 pe-1.5 fw-bold text-primary" style="font-size: 11.5px;">@taka($invoice->grand_total)</td>
                        </tr>
                        @if(in_array($invoice->type, ['invoice', 'challan']))
                            <tr>
                                <td colspan="4" class="border-0 p-0"></td>
                                <td class="text-end py-0.5 px-1.5 text-success fw-bold">পরিশোধিত:</td>
                                <td class="text-end py-0.5 pe-1.5 text-success fw-bold">@taka($invoice->paid_amount)</td>
                            </tr>
                            @if($invoice->due_amount > 0)
                                <tr class="table-danger">
                                    <td colspan="4" class="border-0 p-0"></td>
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

            {{-- Signature & QR Code Footer (Positioned at A4/Letter page bottom) --}}
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

                    <div class="col-4">
                        <div class="signature-box" style="margin-top: 36px;">
                            <div class="border-top border-dark pt-1 fw-semibold text-dark">
                                বিল প্রস্তুতকারীর স্বাক্ষর
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
            <div class="page-break d-print-block"></div>

            <div class="card border shadow-xs rounded-3 p-3 p-md-4 bg-white mb-3 invoice-page-card" id="pageChallanMemo">
                
                {{-- Institutional / Company Header with 2:1 Wide Logo on the left --}}
                <div class="d-flex flex-wrap justify-content-between align-items-center border-bottom pb-2 mb-2 gap-2">
                    <div class="d-flex align-items-center gap-3.5 invoice-brand-header">
                        <img src="{{ $logoSrc }}" alt="{{ $settings['business_name'] ?? 'আইডিয়া প্রকাশন' }}" 
                             class="img-fluid invoice-logo-img" style="height: 48px; width: 96px; aspect-ratio: 2/1; object-fit: contain; flex-shrink: 0; margin-right: 6px;">
                        <div class="d-flex flex-column justify-content-center" style="line-height: 1.35; padding-left: 2px;">
                            <div class="fw-bold text-primary invoice-brand-name" style="font-size: 15.5px; margin-bottom: 2px;">{{ $settings['business_name'] ?? 'আইডিয়া প্রকাশন' }}</div>
                            <div class="text-muted invoice-tagline" style="font-size: 10.5px; margin-bottom: 2px;">{{ $settings['tagline'] ?? 'বই প্রকাশনা, মুদ্রণ ও পরিবেশনা' }}</div>
                            <div class="text-muted invoice-contact-info" style="font-size: 10px;">
                                {{ $settings['address'] ?? 'ঢাকা, বাংলাদেশ' }} · মোবাইল: {{ $settings['phone'] ?? '018XXXXXXXX' }} · ইমেইল: {{ $settings['email'] ?? 'info@ideaabd.com' }}
                            </div>
                        </div>
                    </div>

                    <div class="text-md-end">
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
                <div class="row mb-2.5 p-2 bg-light rounded-2 border g-2 align-items-start" style="font-size: 12px;">
                    <div class="col-7">
                        <div class="fw-bold text-dark mb-1" style="font-size: 12px;"><i class="fas fa-truck-ramp-box me-1 text-primary"></i>প্রাপক ও গন্তব্য:</div>
                        <table class="table-borderless p-0 m-0 w-100" style="font-size: 12px; line-height: 1.45;">
                            @if($invoice->customer_name)
                                <tr>
                                    <td class="text-muted pe-1 text-nowrap" style="width: 110px; vertical-align: top;">প্রাপক নাম:</td>
                                    <td class="fw-bold text-dark">{{ $invoice->customer_name }}</td>
                                </tr>
                            @endif
                            @if(!empty($invoice->customer_designation))
                                <tr>
                                    <td class="text-muted pe-1 text-nowrap" style="vertical-align: top;">পদবী:</td>
                                    <td class="fw-semibold text-dark">{{ $invoice->customer_designation }}</td>
                                </tr>
                            @endif
                            @if($invoice->customer_org)
                                <tr>
                                    <td class="text-muted pe-1 text-nowrap" style="vertical-align: top;">প্রতিষ্ঠানের নাম:</td>
                                    <td class="fw-semibold text-primary">{{ $invoice->customer_org }}</td>
                                </tr>
                            @endif
                            @if($invoice->customer_address)
                                <tr>
                                    <td class="text-muted pe-1 text-nowrap" style="vertical-align: top;">ঠিকানা:</td>
                                    <td class="text-dark">{{ $invoice->customer_address }}</td>
                                </tr>
                            @endif
                            @if($invoice->customer_phone)
                                <tr>
                                    <td class="text-muted pe-1 text-nowrap" style="vertical-align: top;">মোবাইল:</td>
                                    <td class="text-dark fw-bold font-monospace">{{ $invoice->customer_phone }}</td>
                                </tr>
                            @endif
                        </table>
                    </div>
                    <div class="col-5 text-end">
                        <div class="text-muted text-uppercase fw-semibold mb-1" style="font-size: 11px;">চালান ট্র্যাকিং ও প্রেরণ তথ্য:</div>
                        <div style="font-size: 12px; line-height: 1.5;">
                            <div>চালানের ধরন: <strong>পণ্য / বই সরবরাহ চালান</strong></div>
                            <div>মোট আইটেম: <strong>@bn(count($invoice->items ?? [])) টি</strong> · মোট বই: <strong class="text-primary">@bn($totalQuantity) টি</strong></div>
                            <div class="text-muted">প্রেরক / প্যাকার: <strong>{{ $invoice->creator->name ?? 'অ্যাডমিন' }}</strong></div>
                        </div>
                    </div>
                </div>

                {{-- Delivery Items Table (Compact for 25-30 items per A4 page) --}}
                <div class="table-responsive mb-2">
                    <table class="table table-bordered table-sm align-middle invoice-table mb-0" style="font-size: 10px;">
                        <thead class="table-light">
                            <tr class="text-muted text-uppercase" style="font-size: 9px;">
                                <th class="text-center py-1 px-1" style="width: 30px;">#</th>
                                <th class="py-1 px-1.5">সরবরাহকৃত পণ্য / বইয়ের বিবরণ</th>
                                <th class="text-center py-1 px-1" style="width: 75px;">ধরন</th>
                                <th class="text-center py-1 px-1" style="width: 75px;">পরিমাণ</th>
                                <th class="text-center py-1 px-1" style="width: 85px;">প্যাকিং অবস্থা</th>
                                <th class="py-1 px-1.5" style="width: 90px;">মন্তব্য</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoice->items as $idx => $item)
                                <tr>
                                    <td class="text-center py-0.5 px-1 text-muted">@bn($idx + 1)</td>
                                    <td class="py-0.5 px-1.5">
                                        <span class="fw-semibold text-dark">{{ $item['title'] ?? '—' }}</span>
                                    </td>
                                    <td class="text-center py-0.5 px-1"><span class="badge bg-light text-dark border px-1 py-0" style="font-size: 8.5px;">{{ $item['item_type'] ?? 'বই' }}</span></td>
                                    <td class="text-center py-0.5 px-1 fw-bold text-primary">@bn($item['quantity'] ?? 1)</td>
                                    <td class="text-center py-0.5 px-1 text-muted">অক্ষত / নতুন কপি</td>
                                    <td class="py-0.5 px-1.5 text-muted">যাচাইকৃত</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="table-light">
                                <td colspan="3" class="text-end py-1 px-1.5 fw-bold">সর্বমোট সরবরাহকৃত বই / পণ্য:</td>
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

                {{-- Challan Signatures & QR Code (Positioned at A4/Letter page bottom) --}}
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

                        <div class="col-4">
                            <div class="signature-box" style="margin-top: 36px;">
                                <div class="border-top border-dark pt-1 fw-semibold text-dark">
                                    বিল প্রস্তুতকারীর স্বাক্ষর
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-center text-muted mt-2 d-flex justify-content-between align-items-center" style="font-size: 8.5px; line-height: 1;">
                        <span>পৃষ্ঠা ২ / ২ (ডেলিভারি চালান কপি)</span>
                        <span>{{ $settings['business_name'] ?? 'আইডিয়া প্রকাশন' }} · ডেলিভারি চালান</span>
                        <span>আইডি: {{ $invoice->invoice_no }}</span>
                    </div>
                </div>
            </div>
        @endif

    </div>
</div>

{{-- Invoice & Memo Header Settings / Design Modal with 2:1 Cropper --}}
<div class="modal fade d-print-none" id="invoiceSettingsModal" tabindex="-1" aria-labelledby="invoiceSettingsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow">
            <form action="{{ route('admin.accounting.settings.update') }}" method="POST" enctype="multipart/form-data" id="settingsForm">
                @csrf
                <input type="hidden" name="logo_base64" id="logoCroppedBase64">

                <div class="modal-header border-bottom py-3">
                    <h5 class="modal-title fw-bold text-primary" id="invoiceSettingsModalLabel">
                        <i class="fas fa-palette me-2"></i>ইনভয়েস ডিজাইন ও মেমো ব্র্যান্ডিং সেটিংস
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    
                    {{-- Live Preview Header Card --}}
                    <div class="card border rounded-3 p-3 mb-4 bg-light">
                        <span class="small fw-bold text-muted text-uppercase mb-2 d-block"><i class="fas fa-eye me-1 text-primary"></i>ইনভয়েস হেডার লাইভ প্রিভিউ (Preview):</span>
                        <div class="d-flex align-items-center gap-3.5 p-2 bg-white rounded border">
                            <img src="{{ $logoSrc }}" id="previewHeaderLogo" alt="Logo Preview" style="height: 48px; width: 96px; aspect-ratio: 2/1; object-fit: contain; flex-shrink: 0; margin-right: 6px;">
                            <div class="d-flex flex-column justify-content-center" style="line-height: 1.35; padding-left: 2px;">
                                <div class="fw-bold text-primary mb-0" id="previewHeaderTitle" style="font-size: 15.5px;">{{ $settings['business_name'] ?? 'আইডিয়া প্রকাশন' }}</div>
                                <div class="text-muted small mb-0" id="previewHeaderTagline" style="font-size: 10.5px;">{{ $settings['tagline'] ?? 'বই প্রকাশনা, মুদ্রণ ও পরিবেশনা' }}</div>
                                <div class="text-muted small mt-0.5" id="previewHeaderMeta" style="font-size: 10px;">
                                    {{ $settings['address'] ?? 'ঢাকা, বাংলাদেশ' }} · মোবাইল: {{ $settings['phone'] ?? '018XXXXXXXX' }} · ইমেইল: {{ $settings['email'] ?? 'info@ideaabd.com' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 2:1 Aspect Ratio Logo Cropper Tool --}}
                    <div class="card border border-primary-subtle rounded-3 p-3 mb-4 bg-primary-subtle bg-opacity-10">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label fw-bold text-primary mb-0">
                                <i class="fas fa-crop-simple me-1"></i> লোগো আপলোড ও ২:১ ওয়াইড ক্রপ টুল (Wide 2:1 Ratio)
                            </label>
                            <span class="badge bg-primary text-white">রেশিও ২:১ (উচ্চতার দ্বিগুণ চওড়া)</span>
                        </div>
                        
                        <input type="file" id="logoFileInput" class="form-control mb-3" accept="image/*">
                        
                        <div id="cropperContainer" class="d-none">
                            <div class="row g-3 align-items-center">
                                <div class="col-md-7">
                                    <div class="position-relative bg-dark rounded-3 overflow-hidden d-flex align-items-center justify-content-center" 
                                         style="height: 180px; width: 100%; border: 2px dashed #0d6efd; cursor: grab;" id="cropDragArea">
                                        <canvas id="cropCanvas" width="360" height="180" class="w-100 h-100" style="object-fit: contain;"></canvas>
                                    </div>
                                    <div class="d-flex align-items-center gap-2 mt-2">
                                        <i class="fas fa-magnifying-glass-minus text-muted small"></i>
                                        <input type="range" class="form-range" id="cropZoomSlider" min="0.3" max="3.5" step="0.02" value="1">
                                        <i class="fas fa-magnifying-glass-plus text-muted small"></i>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="resetCrop()" title="রিসেট">
                                            <i class="fas fa-rotate-left"></i>
                                        </button>
                                    </div>
                                    <small class="text-muted d-block mt-1" style="font-size: 11px;">
                                        <i class="fas fa-hand me-1"></i>মাউস দিয়ে টেনে পজিশন ঠিক করুন এবং স্লাইডার দিয়ে জুম করুন।
                                    </small>
                                </div>
                                <div class="col-md-5 text-center">
                                    <span class="small fw-semibold text-muted d-block mb-1">ক্রপ প্রিভিউ (২:১ ওয়াইড):</span>
                                    <div class="p-2 bg-white rounded border d-inline-block shadow-xs">
                                        <img id="croppedResultThumb" src="{{ $logoSrc }}" style="height: 60px; width: 120px; aspect-ratio: 2/1; object-fit: contain;" class="rounded">
                                    </div>
                                    <div class="text-success small fw-bold mt-1.5"><i class="fas fa-check-circle me-1"></i>পারফেক্ট ২:১ রেশিও প্রস্তুত</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">কোম্পানি / প্রকাশনীর নাম <span class="text-danger">*</span></label>
                            <input type="text" name="business_name" id="inputBusinessName" class="form-control" value="{{ $settings['business_name'] ?? 'আইডিয়া প্রকাশন' }}" required oninput="updateLivePreview()">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">ট্যাগলাইন / স্লোগান</label>
                            <input type="text" name="tagline" id="inputTagline" class="form-control" value="{{ $settings['tagline'] ?? 'বই প্রকাশনা, মুদ্রণ ও পরিবেশনা' }}" placeholder="বই প্রকাশনা, মুদ্রণ ও পরিবেশনা..." oninput="updateLivePreview()">
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-semibold">অফিসের পূর্ণাঙ্গ ঠিকানা</label>
                            <input type="text" name="address" id="inputAddress" class="form-control" value="{{ $settings['address'] ?? 'ঢাকা, বাংলাদেশ' }}" placeholder="যেমন: সেন্ট্রাল রোড, রংপুর / ৩৮ বাংলাবাজার, ঢাকা..." oninput="updateLivePreview()">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">অফিশিয়াল মোবাইল নম্বর</label>
                            <input type="text" name="phone" id="inputPhone" class="form-control" value="{{ $settings['phone'] ?? '018XXXXXXXX' }}" placeholder="017XXXXXXXX, 018XXXXXXXX" oninput="updateLivePreview()">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">অফিশিয়াল ইমেইল ঠিকানা</label>
                            <input type="email" name="email" id="inputEmail" class="form-control" value="{{ $settings['email'] ?? 'info@ideaabd.com' }}" placeholder="info@ideaabd.com" oninput="updateLivePreview()">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top py-2.5">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">বাতিল</button>
                    <button type="submit" class="btn btn-primary fw-semibold px-4 shadow-sm">
                        <i class="fas fa-save me-1"></i> ডিজাইন ও তথ্য সংরক্ষণ করুন
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function setViewMode(mode) {
    const pageBill = document.getElementById('pageBillMemo');
    const pageChallan = document.getElementById('pageChallanMemo');
    const btnBoth = document.getElementById('btnShowBoth');
    const btnBill = document.getElementById('btnShowBill');
    const btnChallan = document.getElementById('btnShowChallan');

    if (!pageBill || !pageChallan) return;

    btnBoth.classList.remove('active');
    btnBill.classList.remove('active');
    btnChallan.classList.remove('active');

    if (mode === 'bill') {
        pageBill.classList.remove('d-none');
        pageChallan.classList.add('d-none');
        btnBill.classList.add('active');
    } else if (mode === 'challan') {
        pageBill.classList.add('d-none');
        pageChallan.classList.remove('d-none');
        btnChallan.classList.add('active');
    } else {
        pageBill.classList.remove('d-none');
        pageChallan.classList.remove('d-none');
        btnBoth.classList.add('active');
    }
}

function updateLivePreview() {
    const name = document.getElementById('inputBusinessName')?.value || 'আইডিয়া প্রকাশন';
    const tag = document.getElementById('inputTagline')?.value || '';
    const addr = document.getElementById('inputAddress')?.value || '';
    const ph = document.getElementById('inputPhone')?.value || '';
    const em = document.getElementById('inputEmail')?.value || '';

    const titleEl = document.getElementById('previewHeaderTitle');
    const tagEl = document.getElementById('previewHeaderTagline');
    const metaEl = document.getElementById('previewHeaderMeta');

    if (titleEl) titleEl.textContent = name;
    if (tagEl) tagEl.textContent = tag;
    if (metaEl) metaEl.textContent = `${addr} · মোবাইল: ${ph} · ইমেইল: ${em}`;
}

// 2:1 Aspect Ratio Canvas Cropper Logic
let rawImage = new Image();
let imageLoaded = false;
let cropX = 0, cropY = 0;
let cropScale = 1;
let isDragging = false;
let dragStartX = 0, dragStartY = 0;

const fileInput = document.getElementById('logoFileInput');
const cropperBox = document.getElementById('cropperContainer');
const canvas = document.getElementById('cropCanvas');
const ctx = canvas?.getContext('2d');
const zoomSlider = document.getElementById('cropZoomSlider');
const base64Input = document.getElementById('logoCroppedBase64');
const resultThumb = document.getElementById('croppedResultThumb');
const headerPreviewImg = document.getElementById('previewHeaderLogo');
const dragArea = document.getElementById('cropDragArea');

if (fileInput) {
    fileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function(evt) {
            rawImage = new Image();
            rawImage.onload = function() {
                imageLoaded = true;
                cropperBox.classList.remove('d-none');
                
                // Set initial scale to fit canvas
                const scaleW = canvas.width / rawImage.width;
                const scaleH = canvas.height / rawImage.height;
                cropScale = Math.max(scaleW, scaleH);
                
                zoomSlider.min = (cropScale * 0.4).toFixed(2);
                zoomSlider.max = (cropScale * 3.5).toFixed(2);
                zoomSlider.value = cropScale.toFixed(2);
                
                cropX = (canvas.width - rawImage.width * cropScale) / 2;
                cropY = (canvas.height - rawImage.height * cropScale) / 2;

                renderCrop();
            };
            rawImage.src = evt.target.result;
        };
        reader.readAsDataURL(file);
    });
}

function renderCrop() {
    if (!imageLoaded || !ctx) return;
    
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    
    // Draw background grid/fill
    ctx.fillStyle = '#ffffff';
    ctx.fillRect(0, 0, canvas.width, canvas.height);
    
    const drawW = rawImage.width * cropScale;
    const drawH = rawImage.height * cropScale;
    
    ctx.drawImage(rawImage, cropX, cropY, drawW, drawH);
    
    // Export 2:1 PNG
    const dataUrl = canvas.toDataURL('image/png', 0.95);
    if (base64Input) base64Input.value = dataUrl;
    if (resultThumb) resultThumb.src = dataUrl;
    if (headerPreviewImg) headerPreviewImg.src = dataUrl;
}

if (zoomSlider) {
    zoomSlider.addEventListener('input', function() {
        const prevScale = cropScale;
        cropScale = parseFloat(this.value);
        
        // Zoom towards center
        const centerX = canvas.width / 2;
        const centerY = canvas.height / 2;
        cropX = centerX - ((centerX - cropX) / prevScale) * cropScale;
        cropY = centerY - ((centerY - cropY) / prevScale) * cropScale;
        
        renderCrop();
    });
}

if (dragArea) {
    dragArea.addEventListener('mousedown', function(e) {
        isDragging = true;
        dragStartX = e.clientX - cropX;
        dragStartY = e.clientY - cropY;
        dragArea.style.cursor = 'grabbing';
    });

    window.addEventListener('mousemove', function(e) {
        if (!isDragging) return;
        cropX = e.clientX - dragStartX;
        cropY = e.clientY - dragStartY;
        renderCrop();
    });

    window.addEventListener('mouseup', function() {
        if (isDragging) {
            isDragging = false;
            dragArea.style.cursor = 'grab';
        }
    });

    // Touch support for mobile/tablets
    dragArea.addEventListener('touchstart', function(e) {
        if (e.touches.length === 1) {
            isDragging = true;
            dragStartX = e.touches[0].clientX - cropX;
            dragStartY = e.touches[0].clientY - cropY;
        }
    }, {passive: true});

    window.addEventListener('touchmove', function(e) {
        if (!isDragging || e.touches.length !== 1) return;
        cropX = e.touches[0].clientX - dragStartX;
        cropY = e.touches[0].clientY - dragStartY;
        renderCrop();
    }, {passive: true});

    window.addEventListener('touchend', function() {
        isDragging = false;
    });
}

function resetCrop() {
    if (!imageLoaded) return;
    const scaleW = canvas.width / rawImage.width;
    const scaleH = canvas.height / rawImage.height;
    cropScale = Math.max(scaleW, scaleH);
    zoomSlider.value = cropScale.toFixed(2);
    cropX = (canvas.width - rawImage.width * cropScale) / 2;
    cropY = (canvas.height - rawImage.height * cropScale) / 2;
    renderCrop();
}
</script>

<style>
/* Invoice styling: Screen & Print */
.invoice-page-card {
    font-family: 'Kalpurush', 'Nikosh', 'Hind Siliguri', sans-serif;
    font-size: 10px;
    color: #1e293b;
    min-height: 1020px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.invoice-table th,
.invoice-table td {
    padding: 2px 5px !important;
    vertical-align: middle;
    line-height: 1.25;
    font-size: 10px;
}

.invoice-footer-compact {
    margin-top: auto;
    min-height: 0.5in;
}

.signature-box {
    margin-top: 36px;
}

@page {
    size: A4 portrait;
    margin: 5mm 6mm 5mm 6mm;
}

@media print {
    html, body {
        background: #ffffff !important;
        color: #000000 !important;
        font-family: 'Kalpurush', 'Nikosh', 'Hind Siliguri', sans-serif !important;
        font-size: 10px !important;
        margin: 0 !important;
        padding: 0 !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    .adm-side, .adm-top, .adm-actions, .btn, .breadcrumb, footer, .adm-side__backdrop, .d-print-none, .adm-backdrop {
        display: none !important;
    }

    .adm-main, .adm-content {
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
    }

    .row, .col-lg-10 {
        width: 100% !important;
        max-width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    .card, .invoice-page-card {
        border: none !important;
        box-shadow: none !important;
        padding: 0 !important;
        margin: 0 !important;
        width: 100% !important;
        background: #ffffff !important;
        min-height: 282mm !important;
        height: 282mm !important;
        display: flex !important;
        flex-direction: column !important;
        justify-content: space-between !important;
        page-break-inside: avoid !important;
        break-inside: avoid !important;
    }

    .invoice-table th,
    .invoice-table td {
        padding: 1.5px 4px !important;
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
        font-size: 15px !important;
    }

    .invoice-footer-compact {
        margin-top: auto !important;
        max-height: 0.6in !important;
        page-break-inside: avoid !important;
    }

    .page-break {
        display: block !important;
        page-break-before: always !important;
        break-before: page !important;
        height: 0 !important;
        margin: 0 !important;
        padding: 0 !important;
    }
}
</style>

@endsection
