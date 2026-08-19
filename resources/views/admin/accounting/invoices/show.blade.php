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
        <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white mb-4 invoice-page-card" id="pageBillMemo">
            
            {{-- Institutional / Company Header with 2:1 Wide Logo on the left --}}
            <div class="d-flex flex-wrap justify-content-between align-items-start border-bottom pb-4 mb-4 gap-3">
                <div class="d-flex align-items-center gap-3">
                    <img src="{{ $logoSrc }}" alt="{{ $settings['business_name'] ?? 'আইডিয়া প্রকাশন' }}" 
                         class="img-fluid" style="height: 65px; width: 130px; aspect-ratio: 2/1; object-fit: contain;">
                    <div>
                        <h2 class="fw-bold text-primary mb-0">{{ $settings['business_name'] ?? 'আইডিয়া প্রকাশন' }}</h2>
                        <p class="text-muted small mb-0">{{ $settings['tagline'] ?? 'বই প্রকাশনা, মুদ্রণ ও পরিবেশনা' }}</p>
                        <div class="text-muted small mt-1">
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
                    <span class="badge border px-3 py-1.5 rounded-pill fs-6 mb-1 d-inline-block fw-bold" style="{{ $badgeStyles[$invoice->type] ?? $badgeStyles['invoice'] }}">
                        {{ $badgeTitles[$invoice->type] ?? 'বিল / ক্যাশ মেমো' }}
                    </span>
                    <h4 class="fw-bold text-dark mb-0 font-monospace">#{{ $invoice->invoice_no }}</h4>
                    
                    {{-- Computer Generated Bill Subtitle --}}
                    <div class="text-muted small fw-semibold mt-1">
                        <i class="fas fa-desktop me-1"></i>{{ $computerGeneratedLabels[$invoice->type] ?? 'কম্পিউটার জেনারেট বিল' }}
                    </div>

                    <div class="text-muted small mt-1">তারিখ: <strong>@bnDate($invoice->invoice_date)</strong></div>
                    @if($invoice->valid_until)
                        <div class="text-danger small mt-0.5 fw-semibold"><i class="fas fa-hourglass-half me-1"></i>কার্যকারিতা মেয়াদ: @bnDate($invoice->valid_until) পর্যন্ত</div>
                    @endif
                </div>
            </div>

            {{-- Subject and Tender Reference (for Tender & Quotation) --}}
            @if($invoice->subject || $invoice->reference_no)
                <div class="p-3 bg-light rounded-3 border mb-4">
                    @if($invoice->reference_no)
                        <div class="small text-muted mb-1">
                            <strong class="text-dark">দরপত্র / স্মারক নং:</strong> <span class="font-monospace fw-bold text-dark">{{ $invoice->reference_no }}</span>
                        </div>
                    @endif
                    @if($invoice->subject)
                        <div class="small">
                            <strong class="text-dark">বিষয়:</strong> <span class="fw-bold text-primary">{{ $invoice->subject }}</span>
                        </div>
                    @endif
                </div>
            @endif

            {{-- Customer & Billed To Info --}}
            <div class="row mb-4 p-3 bg-light rounded-4">
                <div class="col-md-6 mb-2 mb-md-0">
                    <span class="text-muted small text-uppercase fw-semibold">প্রাপক / প্রতিষ্ঠান তথ্য:</span>
                    @if($invoice->customer_org)
                        <h5 class="fw-bold text-primary mt-1 mb-0.5"><i class="fas fa-building me-1.5 text-primary"></i>{{ $invoice->customer_org }}</h5>
                        <div class="text-dark fw-semibold small mb-1"><i class="fas fa-user me-1 text-muted"></i>প্রতিনিধি: {{ $invoice->customer_name }}</div>
                    @else
                        <h5 class="fw-bold text-dark mt-1 mb-1"><i class="fas fa-user me-1.5 text-primary"></i>{{ $invoice->customer_name }}</h5>
                    @endif
                    @if($invoice->customer_phone)
                        <div class="text-muted small"><i class="fas fa-phone me-1"></i>{{ $invoice->customer_phone }}</div>
                    @endif
                    @if($invoice->customer_address)
                        <div class="text-muted small"><i class="fas fa-location-dot me-1"></i>{{ $invoice->customer_address }}</div>
                    @endif
                </div>
                <div class="col-md-6 text-md-end">
                    <span class="text-muted small text-uppercase fw-semibold">অর্ডার ও পেমেন্ট বিবরণ:</span>
                    <div class="mt-1">
                        <div>ডকুমেন্ট ধরন: <strong>{{ $invoice->type_label }}</strong></div>
                        @if(in_array($invoice->type, ['invoice', 'challan']))
                            <div>পেমেন্ট মাধ্যম: <strong>{{ $invoice->payment_method }}</strong></div>
                            <div>
                                পেমেন্ট স্ট্যাটাস: 
                                @if($invoice->payment_status === 'paid')
                                    <span class="badge bg-success-subtle text-success border">পরিশোধিত</span>
                                @elseif($invoice->payment_status === 'partial')
                                    <span class="badge bg-warning-subtle text-dark border">আংশিক বকেয়া</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border">বকেয়া</span>
                                @endif
                            </div>
                        @else
                            <div>প্রস্তাবনা স্ট্যাটাস: <span class="badge bg-primary-subtle text-primary border">প্রস্তাবিত (Draft/Offered)</span></div>
                        @endif
                        <div class="text-muted small mt-1">প্রস্তুতকারী: <strong>{{ $invoice->creator->name ?? 'অ্যাডমিন' }}</strong></div>
                    </div>
                </div>
            </div>

            {{-- Items / Price Schedule Table --}}
            <div class="table-responsive mb-4">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr class="small text-muted text-uppercase">
                            <th class="ps-3" style="width: 40px;">#</th>
                            <th>বিবরণ / বই বা সেবার নাম</th>
                            <th style="width: 130px;">ধরন</th>
                            <th class="text-center" style="width: 100px;">পরিমাণ</th>
                            <th class="text-end" style="width: 120px;">দর / একক মূল্য (৳)</th>
                            <th class="text-end pe-3" style="width: 140px;">মোট টাকা (৳)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->items as $idx => $item)
                            <tr>
                                <td class="ps-3 text-muted small">@bn($idx + 1)</td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $item['title'] ?? '—' }}</div>
                                </td>
                                <td><span class="badge bg-light text-dark border">{{ $item['item_type'] ?? 'বই' }}</span></td>
                                <td class="text-center fw-bold">@bn($item['quantity'] ?? 1)</td>
                                <td class="text-end">@taka($item['unit_price'] ?? 0)</td>
                                <td class="text-end pe-3 fw-bold text-dark">@taka($item['subtotal'] ?? 0)</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="border-0"></td>
                            <td class="text-end fw-semibold">উপ-যোগফল:</td>
                            <td class="text-end pe-3 fw-semibold">@taka($invoice->subtotal)</td>
                        </tr>
                        @if($invoice->discount > 0)
                            <tr>
                                <td colspan="4" class="border-0"></td>
                                <td class="text-end text-danger fw-semibold">বিশেষ ছাড়:</td>
                                <td class="text-end pe-3 text-danger fw-semibold">- @taka($invoice->discount)</td>
                            </tr>
                        @endif
                        @if($invoice->tax > 0)
                            <tr>
                                <td colspan="4" class="border-0"></td>
                                <td class="text-end text-muted fw-semibold">ভ্যাট / ট্যাক্স:</td>
                                <td class="text-end pe-3 text-muted fw-semibold">+ @taka($invoice->tax)</td>
                            </tr>
                        @endif
                        <tr class="table-light">
                            <td colspan="4" class="border-0"></td>
                            <td class="text-end fw-bold fs-6">সর্বমোট বিল:</td>
                            <td class="text-end pe-3 fw-bold fs-5 text-primary">@taka($invoice->grand_total)</td>
                        </tr>
                        @if(in_array($invoice->type, ['invoice', 'challan']))
                            <tr>
                                <td colspan="4" class="border-0"></td>
                                <td class="text-end text-success fw-bold">পরিশোধিত:</td>
                                <td class="text-end pe-3 text-success fw-bold">@taka($invoice->paid_amount)</td>
                            </tr>
                            @if($invoice->due_amount > 0)
                                <tr class="table-danger">
                                    <td colspan="4" class="border-0"></td>
                                    <td class="text-end text-danger fw-bold">অবশিষ্ট বকেয়া (Due):</td>
                                    <td class="text-end pe-3 text-danger fw-bold fs-6">@taka($invoice->due_amount)</td>
                                </tr>
                            @endif
                        @endif
                    </tfoot>
                </table>
            </div>

            {{-- Notes --}}
            @if($invoice->notes)
                <div class="p-3 bg-light rounded-3 text-muted small mb-3">
                    <strong class="text-dark"><i class="fas fa-note-sticky me-1 text-primary"></i>বিশেষ নোট:</strong> {{ $invoice->notes }}
                </div>
            @endif

            {{-- Terms & Conditions for Tender / Quotation --}}
            @if($invoice->terms_conditions)
                <div class="p-3 bg-light rounded-3 text-muted small mb-4 border">
                    <strong class="text-dark d-block mb-1"><i class="fas fa-file-contract me-1 text-primary"></i>দরপত্র / কোটেশনের শর্তাবলী (Terms & Conditions):</strong>
                    <div style="white-space: pre-line;">{{ $invoice->terms_conditions }}</div>
                </div>
            @endif

            {{-- Institutional Signature & QR Code Footer --}}
            <div class="row pt-4 mt-3 align-items-end">
                <div class="col-4 text-center">
                    <div class="border-top border-dark pt-2 small fw-semibold">
                        {{ in_array($invoice->type, ['quotation', 'tender']) ? 'দরপত্র আহ্বানকারী / গ্রাহক স্বাক্ষর' : 'গ্রহীতার স্বাক্ষর' }}
                    </div>
                </div>

                {{-- QR Code & Barcode Verification Box --}}
                <div class="col-4 text-center">
                    <div class="d-inline-flex flex-column align-items-center p-2 rounded-3 border bg-white shadow-xs">
                        <img src="{{ $qrCodeUrl }}" alt="QR Code" style="width: 85px; height: 85px; object-fit: contain;">
                        <span class="text-muted fw-semibold mt-1" style="font-size: 9.5px;">
                            <i class="fas fa-qrcode me-0.5"></i>স্ক্যান করে বিল দেখুন
                        </span>
                        <div class="font-monospace text-dark fw-bold mt-0.5" style="font-size: 9px; letter-spacing: 0.5px;">
                            *{{ $invoice->invoice_no }}*
                        </div>
                    </div>
                </div>

                <div class="col-4 text-center">
                    <div class="border-top border-dark pt-2 small fw-semibold">
                        অনুমোদিত স্বাক্ষর ও সিল<br>
                        <span class="text-muted" style="font-size: 11px;">({{ $settings['business_name'] ?? 'আইডিয়া প্রকাশন' }})</span>
                    </div>
                </div>
            </div>

            <div class="text-center text-muted small mt-4 pt-2 border-top d-flex justify-content-between align-items-center" style="font-size: 11px;">
                <span>পৃষ্ঠা ১ / {{ $invoice->type === 'invoice' ? '২ (ক্যাশ মেমো কপি)' : '১' }}</span>
                <span>{{ $settings['business_name'] ?? 'আইডিয়া প্রকাশন' }} · কম্পিউটার জেনারেটেড বিল</span>
                <span>আইডি: {{ $invoice->invoice_no }}</span>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- PAGE 2: DELIVERY CHALLAN (স্বয়ংক্রিয় ২য় পেজ চালান - বিলের জন্য)              --}}
        {{-- ========================================================================= --}}
        @if($invoice->type === 'invoice')
            <div class="page-break d-print-block"></div>

            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white mb-4 invoice-page-card" id="pageChallanMemo">
                
                {{-- Institutional / Company Header with 2:1 Wide Logo on the left --}}
                <div class="d-flex flex-wrap justify-content-between align-items-start border-bottom pb-4 mb-4 gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <img src="{{ $logoSrc }}" alt="{{ $settings['business_name'] ?? 'আইডিয়া প্রকাশন' }}" 
                             class="img-fluid" style="height: 65px; width: 130px; aspect-ratio: 2/1; object-fit: contain;">
                        <div>
                            <h2 class="fw-bold text-primary mb-0">{{ $settings['business_name'] ?? 'আইডিয়া প্রকাশন' }}</h2>
                            <p class="text-muted small mb-0">{{ $settings['tagline'] ?? 'বই প্রকাশনা, মুদ্রণ ও পরিবেশনা' }}</p>
                            <div class="text-muted small mt-1">
                                {{ $settings['address'] ?? 'ঢাকা, বাংলাদেশ' }} · মোবাইল: {{ $settings['phone'] ?? '018XXXXXXXX' }} · ইমেইল: {{ $settings['email'] ?? 'info@ideaabd.com' }}
                            </div>
                        </div>
                    </div>

                    <div class="text-md-end">
                        <span class="badge border px-3 py-1.5 rounded-pill fs-6 mb-1 d-inline-block fw-bold" style="background-color: #e0f2fe; color: #0369a1; border-color: #7dd3fc;">
                            ডেলিভারি চালান (DELIVERY CHALLAN)
                        </span>
                        <h4 class="fw-bold text-dark mb-0 font-monospace">#{{ $invoice->invoice_no }}</h4>
                        
                        <div class="text-muted small fw-semibold mt-1">
                            <i class="fas fa-truck me-1"></i>কম্পিউটার জেনারেটেড ডেলিভারি চালান
                        </div>

                        <div class="text-muted small mt-1">চালান তারিখ: <strong>@bnDate($invoice->invoice_date)</strong></div>
                        <div class="text-muted small">সম্পর্কিত বিল নং: <strong>#{{ $invoice->invoice_no }}</strong></div>
                    </div>
                </div>

                {{-- Delivery Destination & Client Details --}}
                <div class="row mb-4 p-3 bg-light rounded-4">
                    <div class="col-md-6 mb-2 mb-md-0">
                        <span class="text-muted small text-uppercase fw-semibold"><i class="fas fa-truck-ramp-box me-1 text-primary"></i>প্রাপক ও গন্তব্যের তথ্য:</span>
                        @if($invoice->customer_org)
                            <h5 class="fw-bold text-primary mt-1 mb-0.5"><i class="fas fa-building me-1.5 text-primary"></i>{{ $invoice->customer_org }}</h5>
                            <div class="text-dark fw-semibold small mb-1"><i class="fas fa-user me-1 text-muted"></i>প্রতিনিধি: {{ $invoice->customer_name }}</div>
                        @else
                            <h5 class="fw-bold text-dark mt-1 mb-1"><i class="fas fa-user me-1.5 text-primary"></i>{{ $invoice->customer_name }}</h5>
                        @endif
                        @if($invoice->customer_phone)
                            <div class="text-muted small"><i class="fas fa-phone me-1"></i>{{ $invoice->customer_phone }}</div>
                        @endif
                        @if($invoice->customer_address)
                            <div class="text-muted small fw-semibold text-dark"><i class="fas fa-location-dot me-1 text-danger"></i>{{ $invoice->customer_address }}</div>
                        @else
                            <div class="text-muted small">গন্তব্য: সরাসরি সরবরাহ / কাউন্টার ডেলিভারি</div>
                        @endif
                    </div>
                    <div class="col-md-6 text-md-end">
                        <span class="text-muted small text-uppercase fw-semibold">ডেলিভারি ট্র্যাকিং ও প্রেরণ তথ্য:</span>
                        <div class="mt-1">
                            <div>চালানের ধরন: <strong>পণ্য / বই সরবরাহ চালান</strong></div>
                            <div>মোট আইটেম সংখ্যা: <strong>@bn(count($invoice->items ?? [])) টি</strong></div>
                            <div>মোট বই / কপির পরিমাণ: <strong class="text-primary fs-6">@bn($totalQuantity) টি</strong></div>
                            <div class="text-muted small mt-1">প্রেরক / প্যাকার: <strong>{{ $invoice->creator->name ?? 'অ্যাডমিন' }}</strong></div>
                        </div>
                    </div>
                </div>

                {{-- Delivery Items Table (Focuses on Quantities & Items) --}}
                <div class="table-responsive mb-4">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr class="small text-muted text-uppercase">
                                <th class="ps-3" style="width: 40px;">#</th>
                                <th>সরবরাহকৃত পণ্য / বইয়ের বিবরণ</th>
                                <th style="width: 140px;">ধরন</th>
                                <th class="text-center" style="width: 120px;">সরবরাহের পরিমাণ</th>
                                <th class="text-center" style="width: 140px;">প্যাকিং অবস্থা</th>
                                <th style="width: 150px;">মন্তব্য / রিমার্কস</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoice->items as $idx => $item)
                                <tr>
                                    <td class="ps-3 text-muted small">@bn($idx + 1)</td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $item['title'] ?? '—' }}</div>
                                    </td>
                                    <td><span class="badge bg-light text-dark border">{{ $item['item_type'] ?? 'বই' }}</span></td>
                                    <td class="text-center fw-bold text-primary fs-6">@bn($item['quantity'] ?? 1)</td>
                                    <td class="text-center text-muted small">অক্ষত / নতুন কপি</td>
                                    <td class="text-muted small">যাচাইকৃত</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="table-light">
                                <td colspan="3" class="text-end fw-bold">সর্বমোট সরবরাহকৃত বই / পণ্য:</td>
                                <td class="text-center fw-bold text-primary fs-5">@bn($totalQuantity) টি</td>
                                <td colspan="2" class="text-muted small">সম্পূর্ণ লট প্রস্তুত ও প্রেরিত</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {{-- Challan Delivery Notes & Terms --}}
                <div class="p-3 bg-light rounded-3 text-muted small mb-4 border">
                    <strong class="text-dark d-block mb-1"><i class="fas fa-circle-check me-1 text-success"></i>পণ্য প্রাপ্তি ও চালান সংক্রান্ত শর্তাবলি:</strong>
                    <div class="small">
                        ১. অনুগ্রহ করে চালান অনুযায়ী বই/পণ্যের সংখ্যা ও বাঁধাই অক্ষত রয়েছে কিনা তা বুঝে নিয়ে রসিদে স্বাক্ষর প্রদান করুন।<br>
                        ২. কার্টনে কোনো ত্রুটি বা শর্টেজ পরিলক্ষিত হলে ডেলিভারি প্রতিনিধির উপস্থিতিতেই অবহিত করুন।<br>
                        @if($invoice->notes)
                            ৩. বিশেষ নির্দেশ: {{ $invoice->notes }}
                        @endif
                    </div>
                </div>

                {{-- Challan Dual Signatures & QR Code --}}
                <div class="row pt-4 mt-3 align-items-end">
                    <div class="col-4 text-center">
                        <div class="border-top border-dark pt-2 small fw-semibold">
                            পণ্য গ্রহণকারীর স্বাক্ষর ও সিল<br>
                            <span class="text-muted" style="font-size: 11px;">(গ্রহীতার তারিখসহ স্বাক্ষর)</span>
                        </div>
                    </div>

                    {{-- QR Code & Barcode Verification Box --}}
                    <div class="col-4 text-center">
                        <div class="d-inline-flex flex-column align-items-center p-2 rounded-3 border bg-white shadow-xs">
                            <img src="{{ $qrCodeUrl }}" alt="QR Code" style="width: 85px; height: 85px; object-fit: contain;">
                            <span class="text-muted fw-semibold mt-1" style="font-size: 9.5px;">
                                <i class="fas fa-qrcode me-0.5"></i>স্ক্যান করে চালান যাচাই
                            </span>
                            <div class="font-monospace text-dark fw-bold mt-0.5" style="font-size: 9px; letter-spacing: 0.5px;">
                                *{{ $invoice->invoice_no }}*
                            </div>
                        </div>
                    </div>

                    <div class="col-4 text-center">
                        <div class="border-top border-dark pt-2 small fw-semibold">
                            পণ্য প্রেরণকারীর স্বাক্ষর ও সিল<br>
                            <span class="text-muted" style="font-size: 11px;">({{ $settings['business_name'] ?? 'আইডিয়া প্রকাশন' }})</span>
                        </div>
                    </div>
                </div>

                <div class="text-center text-muted small mt-4 pt-2 border-top d-flex justify-content-between align-items-center" style="font-size: 11px;">
                    <span>পৃষ্ঠা ২ / ২ (ডেলিভারি চালান কপি)</span>
                    <span>{{ $settings['business_name'] ?? 'আইডিয়া প্রকাশন' }} · ডেলিভারি চালান</span>
                    <span>আইডি: {{ $invoice->invoice_no }}</span>
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
                        <div class="d-flex align-items-center gap-3 p-2 bg-white rounded border">
                            <img src="{{ $logoSrc }}" id="previewHeaderLogo" alt="Logo Preview" style="height: 55px; width: 110px; aspect-ratio: 2/1; object-fit: contain;">
                            <div>
                                <h4 class="fw-bold text-primary mb-0" id="previewHeaderTitle">{{ $settings['business_name'] ?? 'আইডিয়া প্রকাশন' }}</h4>
                                <p class="text-muted small mb-0" id="previewHeaderTagline">{{ $settings['tagline'] ?? 'বই প্রকাশনা, মুদ্রণ ও পরিবেশনা' }}</p>
                                <div class="text-muted small mt-0.5" id="previewHeaderMeta" style="font-size: 11.5px;">
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
@media print {
    .adm-side, .adm-top, .adm-actions, .btn, .breadcrumb, footer, .adm-side__backdrop, .d-print-none {
        display: none !important;
    }
    .adm-main {
        margin-left: 0 !important;
        padding: 0 !important;
    }
    .card {
        border: none !important;
        box-shadow: none !important;
    }
    .page-break {
        display: block !important;
        page-break-before: always !important;
        break-before: page !important;
        height: 0 !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    .invoice-page-card {
        page-break-inside: avoid !important;
        break-inside: avoid !important;
    }
}
</style>

@endsection
