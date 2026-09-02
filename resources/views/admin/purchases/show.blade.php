@extends('layouts.admin')

@php
    $typeTitles = [
        'books'         => 'বই ক্রয় চালান ও ইনভেন্টরি #',
        'raw_materials' => 'কাঁচামাল ও প্রেস ক্রয় চালান #',
        'other'         => 'অন্যান্য ও বিবিধ ক্রয় চালান #',
    ];
    $cat = $purchase->purchase_category ?: 'books';
    $docTitle = ($typeTitles[$cat] ?? 'Purchase Invoice #') . $purchase->purchase_no;

    $settings = $settings ?? $invoiceSettings ?? \App\Http\Controllers\Admin\IdeaAccountingController::getInvoiceSettings();
    $bizLogo = $settings['logo'] ?? '/images/logo.png';
    $logoSrc = \App\Support\SiteSetting::resolveImageUrl($bizLogo, 'images/logo.png') ?: asset('images/logo.png');

    $purchaseUrl = route('admin.purchases.show', $purchase->id);
    $qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=130x130&margin=4&data=" . urlencode($purchaseUrl);

    $totalQuantity = 0;
    $totalReams = 0;
    foreach($purchase->items ?? [] as $it) {
        $totalQuantity += (float)($it->quantity ?? 1);
        if (!empty($it->reams_quantity)) {
            $totalReams += (float)$it->reams_quantity;
        }
    }

    $creatorName = !empty($settings['default_creator_name']) ? $settings['default_creator_name'] : ($purchase->creator->name ?? 'Idea Publication Authority');
    $creatorDesignation = !empty($settings['default_creator_designation']) ? $settings['default_creator_designation'] : 'Authorized Signatory / Purchase In-Charge';

    $recipientNameSize = $settings['challan_recipient_name_size'] ?? '13px';
    $recipientPhoneSize = $settings['challan_recipient_phone_size'] ?? '12px';
    $recipientAddressSize = $settings['challan_recipient_address_size'] ?? '11.5px';
    $recipientDesigSize = $settings['challan_recipient_desig_size'] ?? '11.5px';
    $recipientOrgSize = $settings['challan_recipient_org_size'] ?? '12px';

    $partyPhone = $purchase->party_phone ?: ($purchase->publisher?->phone ?? '—');
    $partyAddress = $purchase->party_address ?: ($purchase->publisher?->address ?? '—');
@endphp

@section('title', $docTitle)
@section('heading', $docTitle)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.purchases.index') }}">Purchases</a></li>
    <li class="breadcrumb-item active" aria-current="page">#{{ $purchase->purchase_no }}</li>
@endsection

@section('actions')
    <div class="d-flex flex-wrap gap-2 align-items-center">
        {{-- Collect / Pay Due Installment Button --}}
        @if($purchase->due_amount > 0)
            <button type="button" class="btn btn-success btn-sm rounded-pill px-3 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#paymentModal">
                <i class="fas fa-hand-holding-dollar me-1.5"></i> বকেয়া পরিশোধ / কিস্তি জমা
            </button>
        @endif

        {{-- Vendor Ledger Link --}}
        <a href="{{ route('admin.purchases.ledger', ['party' => $purchase->publisher_id ? 'pub_' . $purchase->publisher_id : 'vendor_' . ($purchase->vendor_name ?: $purchase->supplier_name)]) }}" class="btn btn-outline-info text-dark btn-sm rounded-pill px-3 fw-semibold shadow-sm" title="সরবরাহকারীর খতিয়ান ও রানিং স্টেটমেন্ট দেখুন">
            <i class="fas fa-book-bookmark me-1 text-primary"></i> ভেন্ডর খতিয়ান
        </a>

        {{-- Print / PDF Button --}}
        <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm fw-semibold" onclick="window.print()">
            <i class="fas fa-print me-1.5"></i> Print / PDF
        </button>

        {{-- Edit Document Button --}}
        <a href="{{ route('admin.purchases.edit', $purchase->id) }}" class="btn btn-warning text-dark btn-sm rounded-pill px-3 fw-semibold shadow-sm">
            <i class="fas fa-edit me-1"></i> Edit Invoice
        </a>

        {{-- Customize Memo Header Settings Button --}}
        <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#invoiceSettingsModal" title="Customize purchases & memo branding header">
            <i class="fas fa-palette me-1 text-primary"></i> Memo Settings
        </button>

        {{-- Back to List --}}
        <a href="{{ route('admin.purchases.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-xs">
            <i class="fas fa-arrow-left me-1"></i> Back to List
        </a>
    </div>
@endsection

@section('content')

{{-- Unified Purchases & Accounting Navigation Bar --}}
<div class="card border-0 shadow-sm rounded-4 mb-4 bg-white d-print-none">
    <div class="card-body p-2 d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div class="nav nav-pills gap-1.5 flex-wrap">
            <a href="{{ route('admin.purchases.index') }}" 
               class="nav-link rounded-pill px-3.5 py-2 fw-semibold text-dark hover-bg-light">
                <i class="fas fa-cart-flatbed me-1.5 text-primary"></i> Purchases & Invoices
            </a>
            <a href="{{ route('admin.purchases.payments') }}" 
               class="nav-link rounded-pill px-3.5 py-2 fw-semibold text-dark hover-bg-light">
                <i class="fas fa-hand-holding-dollar me-1.5 text-success"></i> Payments & Ledgers
            </a>
            <a href="{{ route('admin.purchases.ledger') }}" 
               class="nav-link rounded-pill px-3.5 py-2 fw-semibold text-dark hover-bg-light">
                <i class="fas fa-book-bookmark me-1.5 text-info"></i> Vendor Statements
            </a>
            <a href="{{ route('admin.purchases.monthly-report') }}" 
               class="nav-link rounded-pill px-3.5 py-2 fw-semibold text-dark hover-bg-light">
                <i class="fas fa-chart-pie me-1.5 text-warning"></i> Monthly Report
            </a>
            <a href="{{ route('admin.purchases.create') }}" 
               class="nav-link rounded-pill px-3.5 py-2 fw-semibold text-dark hover-bg-light">
                <i class="fas fa-file-circle-plus me-1.5 text-danger"></i> New Purchase Order
            </a>
        </div>

        <div class="btn-group btn-group-sm">
            <button type="button" class="btn btn-outline-primary active" id="btnShowBoth" onclick="setViewMode('both')">
                <i class="fas fa-file-lines me-1"></i>Both Pages (Bill & Challan)
            </button>
            <button type="button" class="btn btn-outline-primary" id="btnShowBill" onclick="setViewMode('bill')">
                <i class="fas fa-receipt me-1"></i>Page 1 (Purchase Bill)
            </button>
            <button type="button" class="btn btn-outline-primary" id="btnShowChallan" onclick="setViewMode('challan')">
                <i class="fas fa-truck me-1"></i>Page 2 (Receiving Challan)
            </button>
        </div>
    </div>
</div>

<div class="row justify-content-center" id="invoicePrintWrapper">
    <div class="col-lg-10">

        {{-- ========================================================================= --}}
        {{-- PAGE 1: PURCHASE ORDER & INVOICE / BILL MEMO                              --}}
        {{-- ========================================================================= --}}
        <div class="card border shadow-xs rounded-3 p-3 p-md-4 bg-white mb-3 invoice-page-card" id="pageBillMemo">
            
            {{-- Institutional / Company Header in 2-Column Single Row (No Wrapping) --}}
            <div class="row align-items-center border-bottom pb-2 mb-2 g-2">
                <div class="col-7">
                    <div class="d-flex align-items-center gap-3 invoice-brand-header">
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

                <div class="col-5 text-end">
                    @php
                        $badgeStyles = [
                            'books'         => 'background-color: #e0e7ff; color: #3730a3; border-color: #c7d2fe;',
                            'raw_materials' => 'background-color: #fef3c7; color: #b45309; border-color: #fcd34d;',
                            'other'         => 'background-color: #e0f2fe; color: #0369a1; border-color: #7dd3fc;',
                        ];
                        $badgeTitles = [
                            'books'         => 'BOOK PURCHASE & INVENTORY BILL',
                            'raw_materials' => 'RAW MATERIALS & PRESS INVOICE',
                            'other'         => 'PURCHASE & EXPENSE INVOICE',
                        ];
                        $cat = $purchase->purchase_category ?: 'books';
                    @endphp
                    <span class="badge border px-2 py-0.5 rounded-pill mb-0.5 d-inline-block fw-bold" style="font-size: 10px; {{ $badgeStyles[$cat] ?? $badgeStyles['books'] }}">
                        {{ $badgeTitles[$cat] ?? 'PURCHASE ORDER & BILL' }}
                    </span>
                    <div class="fw-bold text-dark mb-0 font-monospace invoice-no-text" style="font-size: 13pt; line-height: 1.2;">#{{ $purchase->purchase_no }}</div>
                    
                    <div class="text-muted fw-semibold" style="font-size: 9.5px; line-height: 1.2;">
                        <i class="fas fa-desktop me-1"></i>Computer-generated purchase invoice
                        · Date: <strong>{{ $purchase->purchase_date ? $purchase->purchase_date->format('d M, Y') : '—' }}</strong>
                    </div>
                    <div class="mt-1">
                        @if($purchase->payment_status === 'paid')
                            <span class="badge bg-success-subtle text-success border px-2 py-0.5" style="font-size: 9.5px;">Paid in Full</span>
                        @elseif($purchase->payment_status === 'partial')
                            <span class="badge bg-warning-subtle text-dark border px-2 py-0.5" style="font-size: 9.5px;">Partially Paid</span>
                        @else
                            <span class="badge bg-danger-subtle text-danger border px-2 py-0.5" style="font-size: 9.5px;">Due</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Supplier / Publisher / Vendor Info Card --}}
            <div class="p-2.5 bg-light rounded-2 border mb-2.5 destination-box" style="font-size: 12px; box-sizing: border-box;">
                <div class="row g-2 align-items-start m-0">
                    <div class="col-7 p-0 pe-2">
                        <div class="fw-bold text-dark mb-1" style="font-size: 12px;">
                            <i class="fas fa-truck-field me-1 text-primary"></i>
                            @if($purchase->purchase_category === 'raw_materials')
                                কাঁচামাল সরবরাহকারী / প্রেস ও সাপ্লায়ার বিবরণ:
                            @elseif($purchase->purchase_category === 'other')
                                সরবরাহকারী / ভেন্ডর বিবরণ:
                            @else
                                প্রকাশক ও সরবরাহকারী বিবরণ (Publisher / Supplier):
                            @endif
                        </div>
                        <table class="table-borderless p-0 m-0 w-100" style="font-size: 12px; line-height: 1.45;">
                            @if($purchase->party_name)
                                <tr>
                                    <td class="text-muted pe-1 text-nowrap" style="width: 110px; vertical-align: top; font-size: 11px;">Supplier / Party:</td>
                                    <td class="fw-bold text-dark" style="font-size: {{ $recipientNameSize }};">{{ $purchase->party_name }}</td>
                                </tr>
                            @endif
                            @if(!empty($partyAddress) && $partyAddress !== '—')
                                <tr>
                                    <td class="text-muted pe-1 text-nowrap" style="vertical-align: top; font-size: 11px;">Address:</td>
                                    <td class="text-dark" style="font-size: {{ $recipientAddressSize }}; line-height: 1.35;">{{ $partyAddress }}</td>
                                </tr>
                            @endif
                            @if(!empty($partyPhone) && $partyPhone !== '—')
                                <tr>
                                    <td class="text-muted pe-1 text-nowrap" style="vertical-align: top; font-size: 11px;">Phone / Mobile:</td>
                                    <td class="text-dark fw-bold font-monospace" style="font-size: {{ $recipientPhoneSize }};">{{ $partyPhone }}</td>
                                </tr>
                            @endif
                        </table>
                    </div>
                    <div class="col-5 p-0 ps-2 text-end">
                        <div class="text-muted text-uppercase fw-semibold mb-1" style="font-size: 11px;">Purchase & Payment Terms:</div>
                        <div style="font-size: 12px; line-height: 1.5;">
                            @if($purchase->publisher_memo_no)
                                <div>Vendor Memo #: <strong class="text-primary font-monospace">{{ $purchase->publisher_memo_no }}</strong></div>
                            @endif
                            <div>Payment Term: <strong>{{ ['cash' => 'নগদ (Cash)', 'credit' => 'বাকি (Credit)', 'partial' => 'আংশিক (Partial)', 'installment' => 'কিস্তি (Installment)'][$purchase->payment_type] ?? ucfirst($purchase->payment_type) }}</strong></div>
                            @if($purchase->due_date)
                                <div class="text-danger fw-semibold" style="font-size: 10.5px;"><i class="fas fa-calendar-day me-0.5"></i>Due Date: {{ $purchase->due_date->format('d M, Y') }}</div>
                            @endif
                            <div class="text-muted small">Recorded by: <strong>{{ $purchase->creator->name ?? 'Admin' }}</strong></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Items / Purchase Schedule Table --}}
            <div class="table-responsive mb-2">
                <table class="table table-bordered table-sm align-middle invoice-table mb-0" style="font-size: 10px;">
                    @if($purchase->purchase_category === 'raw_materials')
                        <thead class="table-light">
                            <tr class="text-muted text-uppercase" style="font-size: 9px;">
                                <th class="text-center py-1 px-1" style="width: 26px;">#</th>
                                <th class="py-1 px-1.5">কাজের বিবরণ / কাঁচামাল (Item Description / Press Work)</th>
                                <th class="py-1 px-1" style="width: 110px;">কোয়ালিটি / পেপার</th>
                                <th class="py-1 px-1 text-center" style="width: 85px;">সাইজ / ফর্মা</th>
                                <th class="text-center py-1 px-1" style="width: 50px;">পরিমাণ</th>
                                <th class="text-center py-1 px-1" style="width: 55px;">রিম</th>
                                <th class="text-end py-1 px-1" style="width: 75px;">একক দর (৳)</th>
                                <th class="text-end py-1 pe-1.5" style="width: 85px;">মোট টাকা (৳)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($purchase->items as $i => $item)
                                @php
                                    $quality = ($item->quality_spec && !in_array(strtolower($item->quality_spec), ['paperback', 'hardcover', 'standard'])) ? $item->quality_spec : ($item->author_name ?: '—');
                                    $size = $item->size_spec ?: ($item->book_size ?: ($item->category?->name ?? '—'));
                                @endphp
                                <tr>
                                    <td class="text-center py-0.5 px-1 text-muted">{{ $i + 1 }}</td>
                                    <td class="py-0.5 px-1.5">
                                        <div class="fw-semibold text-dark">{{ $item->displayName }}</div>
                                        @if($item->item_notes)
                                            <div class="text-muted" style="font-size: 8.5px;">মন্তব্য: {{ $item->item_notes }}</div>
                                        @endif
                                    </td>
                                    <td class="py-0.5 px-1 text-muted" style="font-size: 9.5px;">{{ $quality }}</td>
                                    <td class="text-center py-0.5 px-1 text-muted font-monospace" style="font-size: 9px;">{{ $size }}</td>
                                    <td class="text-center py-0.5 px-1 fw-bold text-dark font-monospace">{{ $item->quantity }} {{ $item->unit ?: 'পিস' }}</td>
                                    <td class="text-center py-0.5 px-1 font-monospace text-primary fw-semibold">{{ $item->reams_quantity ? number_format($item->reams_quantity, 2) : '—' }}</td>
                                    <td class="text-end py-0.5 px-1 font-monospace text-danger fw-semibold">৳{{ number_format($item->unit_cost_price, 2) }}</td>
                                    <td class="text-end py-0.5 pe-1.5 fw-bold text-dark font-monospace">৳{{ number_format($item->subtotal, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    @elseif($purchase->purchase_category === 'other')
                        <thead class="table-light">
                            <tr class="text-muted text-uppercase" style="font-size: 9px;">
                                <th class="text-center py-1 px-1" style="width: 26px;">#</th>
                                <th class="py-1 px-1.5">মালের বিবরণ ও খরচ খাত (Item Description)</th>
                                <th class="text-center py-1 px-1" style="width: 60px;">একক</th>
                                <th class="text-center py-1 px-1" style="width: 60px;">পরিমাণ</th>
                                <th class="text-end py-1 px-1" style="width: 85px;">একক দর (৳)</th>
                                <th class="text-end py-1 pe-1.5" style="width: 95px;">মোট টাকা (৳)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($purchase->items as $i => $item)
                                <tr>
                                    <td class="text-center py-0.5 px-1 text-muted">{{ $i + 1 }}</td>
                                    <td class="py-0.5 px-1.5">
                                        <div class="fw-semibold text-dark">{{ $item->displayName }}</div>
                                        @if($item->item_notes)
                                            <div class="text-muted" style="font-size: 8.5px;">মন্তব্য: {{ $item->item_notes }}</div>
                                        @endif
                                    </td>
                                    <td class="text-center py-0.5 px-1 text-muted">{{ $item->unit ?: 'পিস' }}</td>
                                    <td class="text-center py-0.5 px-1 fw-bold text-dark font-monospace">{{ $item->quantity }}</td>
                                    <td class="text-end py-0.5 px-1 font-monospace text-danger fw-semibold">৳{{ number_format($item->unit_cost_price, 2) }}</td>
                                    <td class="text-end py-0.5 pe-1.5 fw-bold text-dark font-monospace">৳{{ number_format($item->subtotal, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    @else
                        <thead class="table-light">
                            <tr class="text-muted text-uppercase" style="font-size: 9px;">
                                <th class="text-center py-1 px-1" style="width: 26px;">#</th>
                                <th class="py-1 px-1.5">বইয়ের নাম ও বিবরণ (Book Title & Description)</th>
                                <th class="py-1 px-1" style="width: 105px;">লেখক / ক্যাটাগরি</th>
                                <th class="text-center py-1 px-1" style="width: 45px;">পরিমাণ</th>
                                <th class="text-end py-1 px-1" style="width: 65px;">MRP (৳)</th>
                                <th class="text-center py-1 px-1" style="width: 50px;">কমিশন %</th>
                                <th class="text-end py-1 px-1" style="width: 75px;">ক্রয়দর (৳)</th>
                                <th class="text-end py-1 px-1" style="width: 75px;">বিক্রয়দর (৳)</th>
                                <th class="text-end py-1 pe-1.5" style="width: 80px;">মোট (৳)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($purchase->items as $i => $item)
                                <tr>
                                    <td class="text-center py-0.5 px-1 text-muted">{{ $i + 1 }}</td>
                                    <td class="py-0.5 px-1.5">
                                        <div class="fw-semibold text-dark">{{ $item->displayName }}</div>
                                        @if($item->book)
                                            <a href="{{ route('shop.show', $item->book->slug) }}" target="_blank" class="text-primary text-decoration-none d-print-none" style="font-size: 8.5px;">
                                                <i class="fas fa-arrow-up-right-from-square me-0.5"></i>Store (Stock: {{ $item->book->stock_quantity }})
                                            </a>
                                        @endif
                                    </td>
                                    <td class="py-0.5 px-1 text-muted" style="font-size: 9px;">
                                        {{ $item->author_name ?? '—' }}
                                    </td>
                                    <td class="text-center py-0.5 px-1 fw-bold text-dark font-monospace">{{ $item->quantity }}</td>
                                    <td class="text-end py-0.5 px-1 font-monospace text-muted">৳{{ number_format($item->mrp_price > 0 ? $item->mrp_price : $item->unit_cost_price, 2) }}</td>
                                    <td class="text-center py-0.5 px-1 font-monospace text-danger fw-semibold">
                                        {{ $item->purchase_commission_percent > 0 ? $item->purchase_commission_percent . '%' : '—' }}
                                    </td>
                                    <td class="text-end py-0.5 px-1 font-monospace text-danger fw-bold">৳{{ number_format($item->unit_cost_price, 2) }}</td>
                                    <td class="text-end py-0.5 px-1 font-monospace text-success fw-semibold">৳{{ number_format($item->unit_sale_price, 2) }}</td>
                                    <td class="text-end py-0.5 pe-1.5 fw-bold text-dark font-monospace">৳{{ number_format($item->subtotal, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    @endif

                    @php
                        $tfootRows = 2; // Subtotal, Current Bill
                        if ($purchase->discount_amount > 0) $tfootRows++;
                        if ($previousDue > 0) $tfootRows += 2; // Previous Due, Total Payable
                        if ($purchase->paid_amount > 0) $tfootRows++;
                        if ($netTotalDue > 0 || $purchase->due_amount > 0) $tfootRows++;
                    @endphp
                    <tfoot>
                        <tr>
                            <td colspan="{{ $purchase->purchase_category === 'raw_materials' ? 6 : ($purchase->purchase_category === 'other' ? 4 : 7) }}" 
                                rowspan="{{ $tfootRows }}" class="py-2 px-2.5 border bg-light bg-opacity-25" style="vertical-align: middle;">
                                <div class="p-1">
                                    <span class="text-muted fw-bold d-block mb-1" style="font-size: 9.5px;">
                                        <i class="fas fa-coins me-1 text-primary"></i>টাকা কথায় (In Words):
                                    </span>
                                    <div class="fw-bold text-dark text-wrap" style="font-size: 11.5px; line-height: 1.45;">
                                        @takaInWordsEn($totalPayable > 0 ? $totalPayable : $purchase->grand_total)
                                    </div>
                                    @if($previousDue > 0 && !empty($previousInvoices))
                                        <div class="mt-2 pt-1.5 border-top border-secondary-subtle">
                                            <span class="text-muted fw-bold d-block mb-1" style="font-size: 9px;">
                                                <i class="fas fa-clock-rotate-left me-1 text-danger"></i>পূর্বের বকেয়া মেমো ও তারিখ:
                                            </span>
                                            <div class="d-flex flex-wrap gap-1">
                                                @foreach($previousInvoices as $pi)
                                                    <span class="badge bg-white text-dark border px-1.5 py-0.5 font-monospace" style="font-size: 8.5px;">
                                                        #{{ $pi['purchase_no'] }} ({{ $pi['purchase_date'] }}): ৳{{ number_format($pi['due_amount'], 2) }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="text-end py-0.5 px-1.5 fw-semibold">উপমোট:</td>
                            <td class="text-end py-0.5 pe-1.5 fw-semibold font-monospace">৳{{ number_format($purchase->total_amount, 2) }}</td>
                        </tr>
                        @if($purchase->discount_amount > 0)
                            <tr>
                                <td class="text-end py-0.5 px-1.5 text-danger fw-semibold">ছাড়:</td>
                                <td class="text-end py-0.5 pe-1.5 text-danger fw-semibold font-monospace">- ৳{{ number_format($purchase->discount_amount, 2) }}</td>
                            </tr>
                        @endif
                        <tr class="table-light">
                            <td class="text-end py-0.5 px-1.5 fw-bold text-dark">চলতি ক্রয় বিল:</td>
                            <td class="text-end py-0.5 pe-1.5 fw-bold text-dark font-monospace">৳{{ number_format($purchase->grand_total, 2) }}</td>
                        </tr>
                        @if($previousDue > 0)
                            <tr class="table-warning bg-opacity-25">
                                <td class="text-end py-0.5 px-1.5 text-danger fw-bold">
                                    পূর্বের বকেয়া জের:
                                </td>
                                <td class="text-end py-0.5 pe-1.5 text-danger fw-bold font-monospace">+ ৳{{ number_format($previousDue, 2) }}</td>
                            </tr>
                            <tr class="table-light border-top border-dark">
                                <td class="text-end py-1 px-1.5 fw-bold text-dark">সর্বমোট প্রদেয়:</td>
                                <td class="text-end py-1 pe-1.5 fw-bold text-primary font-monospace" style="font-size: 11.5px;">৳{{ number_format($totalPayable, 2) }}</td>
                            </tr>
                        @endif
                        @if($purchase->paid_amount > 0)
                            <tr>
                                <td class="text-end py-0.5 px-1.5 text-success fw-bold">চলতি পরিশোধ:</td>
                                <td class="text-end py-0.5 pe-1.5 text-success fw-bold font-monospace">৳{{ number_format($purchase->paid_amount, 2) }}</td>
                            </tr>
                        @endif
                        @if($netTotalDue > 0)
                            <tr class="table-danger">
                                <td class="text-end py-0.5 px-1.5 text-danger fw-bold">
                                    {{ $previousDue > 0 ? 'সর্বমোট বকেয়া জের:' : 'বকেয়া বিল:' }}
                                </td>
                                <td class="text-end py-0.5 pe-1.5 text-danger fw-bold font-monospace">৳{{ number_format($netTotalDue, 2) }}</td>
                            </tr>
                        @endif
                    </tfoot>
                </table>
            </div>

            {{-- Notes & Terms --}}
            <div class="p-1.5 bg-light rounded-2 text-muted mb-3 border" style="font-size: 10px; line-height: 1.3;">
                <strong class="text-dark"><i class="fas fa-circle-info me-1 text-primary"></i>(Note):</strong> 1. Goods once received and verified are entered into bookshop inventory.
                @if($purchase->notes)
                    · {{ $purchase->notes }}
                @endif
                @if(!empty($settings['terms_and_conditions']))
                    · {{ $settings['terms_and_conditions'] }}
                @endif
            </div>

            {{-- Signature & QR Code Footer (Positioned at A4/Letter page bottom) --}}
            <div class="invoice-footer-compact pt-2 mt-auto border-top">
                <div class="row g-2 align-items-end text-center" style="font-size: 10px;">
                    <div class="col-4">
                        <div class="signature-box" style="margin-top: 36px;">
                            <div class="border-top border-dark pt-1 fw-semibold text-dark">
                                Supplier / Vendor Signature
                            </div>
                        </div>
                    </div>

                    {{-- QR Code & Verification Box --}}
                    <div class="col-4">
                        <div class="d-inline-flex align-items-center gap-1.5 px-2 py-1 rounded border bg-white shadow-xs">
                            <img src="{{ $qrCodeUrl }}" alt="QR" style="width: 34px; height: 34px; object-fit: contain;">
                            <div class="text-start" style="line-height: 1.15;">
                                <span class="text-muted fw-semibold d-block" style="font-size: 8px;"><i class="fas fa-qrcode me-0.5"></i>Scan to Verify</span>
                                <span class="font-monospace text-dark fw-bold" style="font-size: 9px;">#{{ $purchase->purchase_no }}</span>
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
                                Authorized Signature / In-Charge
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-center text-muted mt-2 d-flex justify-content-between align-items-center" style="font-size: 8.5px; line-height: 1;">
                    <span>Page 1 / 2 (Purchase Copy)</span>
                    <span>{{ $settings['business_name'] ?? 'Idea Publication' }} · Computer Generated Purchase Order</span>
                    <span>ID: {{ $purchase->purchase_no }}</span>
                </div>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- PAGE 2: GOODS RECEIVED NOTE (GRN) / RECEIVING CHALLAN                     --}}
        {{-- ========================================================================= --}}
        <div class="page-break d-print-block" id="invoicePageBreak"></div>

        <div class="card border shadow-xs rounded-3 p-3 p-md-4 bg-white mb-3 invoice-page-card" id="pageChallanMemo">
            
            {{-- Institutional / Company Header in 2-Column Single Row (No Wrapping) --}}
            <div class="row align-items-center border-bottom pb-2 mb-2 g-2">
                <div class="col-7">
                    <div class="d-flex align-items-center gap-3 invoice-brand-header">
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

                <div class="col-5 text-end">
                    <span class="badge border px-2 py-0.5 rounded-pill mb-0.5 d-inline-block fw-bold" style="font-size: 10px; background-color: #dcfce7; color: #15803d; border-color: #86efac;">
                        GOODS RECEIVED NOTE / STORE CHALLAN
                    </span>
                    <div class="fw-bold text-dark mb-0 font-monospace invoice-no-text" style="font-size: 13pt; line-height: 1.2;">#{{ $purchase->purchase_no }}</div>
                    
                    <div class="text-muted fw-semibold" style="font-size: 9.5px; line-height: 1.2;">
                        <i class="fas fa-boxes-packing me-1"></i>Computer-generated inventory receiving memo · Date: <strong>{{ $purchase->purchase_date ? $purchase->purchase_date->format('d M, Y') : '—' }}</strong>
                    </div>
                    <div class="text-muted" style="font-size: 9px;">Linked Purchase Order #: <strong>#{{ $purchase->purchase_no }}</strong></div>
                </div>
            </div>

            {{-- Delivery Destination & Supplier Details --}}
            <div class="p-2.5 bg-light rounded-2 border mb-2.5 destination-box" style="font-size: 12px; box-sizing: border-box;">
                <div class="row g-2 align-items-start m-0">
                    <div class="col-7 p-0 pe-2">
                        <div class="fw-bold text-dark mb-1 d-flex align-items-center justify-content-between" style="font-size: 12px;">
                            <span><i class="fas fa-truck-ramp-box me-1 text-primary"></i>Supplier / Dispatcher Information:</span>
                        </div>
                        <table class="table-borderless p-0 m-0 w-100 recipient-info-table" style="line-height: 1.45;">
                            @if($purchase->party_name)
                                <tr>
                                    <td class="text-muted pe-1 text-nowrap" style="width: 115px; vertical-align: top; font-size: 11px;">Supplier / Party:</td>
                                    <td class="fw-bold text-dark target-recipient-name" id="challanRecipientName" style="font-size: {{ $recipientNameSize }};">{{ $purchase->party_name }}</td>
                                </tr>
                            @endif
                            @if(!empty($partyAddress) && $partyAddress !== '—')
                                <tr>
                                    <td class="text-muted pe-1 text-nowrap" style="vertical-align: top; font-size: 11px;">Address:</td>
                                    <td class="text-dark target-recipient-address" id="challanRecipientAddr" style="font-size: {{ $recipientAddressSize }}; line-height: 1.35;">{{ $partyAddress }}</td>
                                </tr>
                            @endif
                            @if(!empty($partyPhone) && $partyPhone !== '—')
                                <tr>
                                    <td class="text-muted pe-1 text-nowrap" style="vertical-align: top; font-size: 11px;">Phone / Mobile:</td>
                                    <td class="text-dark fw-bold font-monospace target-recipient-phone" id="challanRecipientPhone" style="font-size: {{ $recipientPhoneSize }};">{{ $partyPhone }}</td>
                                </tr>
                            @endif
                        </table>
                    </div>
                    <div class="col-5 p-0 ps-2 text-end">
                        <div class="text-muted text-uppercase fw-semibold mb-1" style="font-size: 11px;">Inventory Store & Receiving Info:</div>
                        <div style="font-size: 11.5px; line-height: 1.5;">
                            <div>Receiving Type: <strong>Inward Goods / Store Stock Entry</strong></div>
                            <div>Total Items: <strong>{{ count($purchase->items ?? []) }} items</strong> · Total Qty: <strong class="text-primary">{{ $totalQuantity }} pcs</strong></div>
                            <div class="text-muted">Receiver / In-Charge: <strong>{{ $creatorName }}</strong></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Delivered / Received Items Table --}}
            <div class="table-responsive mb-2">
                <table class="table table-bordered table-sm align-middle invoice-table mb-0" style="font-size: 10px;">
                    <thead class="table-light">
                        <tr class="text-muted text-uppercase" style="font-size: 9px;">
                            <th class="text-center py-1 px-1" style="width: 28px;">#</th>
                            <th class="py-1 px-1.5">
                                @if($purchase->purchase_category === 'raw_materials')
                                    গ্রহণকৃত মালামাল / পেপার / প্রেস কাজের বিবরণ (Item Description)
                                @elseif($purchase->purchase_category === 'other')
                                    গ্রহণকৃত মালামাল ও আইটেমের বিবরণ (Item Description)
                                @else
                                    গ্রহণকৃত বইয়ের নাম ও বিবরণ (Book Title / Description)
                                @endif
                            </th>
                            <th class="py-1 px-1" style="width: 115px;">
                                @if($purchase->purchase_category === 'raw_materials')
                                    কোয়ালিটি / পেপার
                                @elseif($purchase->purchase_category === 'other')
                                    স্পেসিফিকেশন
                                @else
                                    লেখক / ক্যাটাগরি
                                @endif
                            </th>
                            <th class="text-center py-1 px-1" style="width: 55px;">একক</th>
                            <th class="text-center py-1 px-1" style="width: 50px;">পরিমাণ</th>
                            <th class="text-center py-1 px-1" style="width: 55px;">রিম</th>
                            <th class="text-center py-1 px-1" style="width: 75px;">অবস্থা (Condition)</th>
                            <th class="py-1 px-1.5" style="width: 75px;">মন্তব্য</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($purchase->items as $idx => $item)
                            @php
                                $quality = ($item->quality_spec && !in_array(strtolower($item->quality_spec), ['paperback', 'hardcover', 'standard'])) ? $item->quality_spec : ($item->author_name ?: '—');
                                $unitName = $item->unit ?: ($purchase->purchase_category === 'books' ? 'কপি' : 'পিস');
                            @endphp
                            <tr>
                                <td class="text-center py-0.5 px-1 text-muted">{{ $idx + 1 }}</td>
                                <td class="py-0.5 px-1.5">
                                    <span class="fw-semibold text-dark" style="line-height: 1.35;">{{ $item->displayName }}</span>
                                </td>
                                <td class="py-0.5 px-1 text-muted" style="font-size: 9.5px;">{{ $quality }}</td>
                                <td class="text-center py-0.5 px-1 text-muted font-monospace" style="font-size: 8.5px;">{{ $unitName }}</td>
                                <td class="text-center py-0.5 px-1 fw-bold text-primary">{{ $item->quantity }}</td>
                                <td class="text-center py-0.5 px-1 font-monospace text-muted">{{ $item->reams_quantity ? number_format($item->reams_quantity, 2) : '—' }}</td>
                                <td class="text-center py-0.5 px-1 text-success fw-semibold" style="font-size: 8.5px;"><i class="fas fa-circle-check me-0.5"></i>Stocked In</td>
                                <td class="py-0.5 px-1.5 text-muted" style="font-size: 8.5px;">{{ $item->item_notes ?: 'Verified' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="table-light">
                            <td colspan="4" class="text-end py-1 px-1.5 fw-bold">Total Received Stock Quantity:</td>
                            <td class="text-center py-1 px-1 fw-bold text-primary" style="font-size: 11px;">{{ $totalQuantity }}</td>
                            <td class="text-center py-1 px-1 fw-bold text-primary" style="font-size: 10px;">{{ $totalReams > 0 ? number_format($totalReams, 2) : '—' }}</td>
                            <td colspan="2" class="py-1 px-1.5 text-muted" style="font-size: 9px;">Stock Updated in System</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- Challan Notes --}}
            <div class="p-1.5 bg-light rounded-2 text-muted mb-3 border" style="font-size: 10px; line-height: 1.3;">
                <strong class="text-dark"><i class="fas fa-circle-info me-1 text-success"></i>(Note):</strong> 1. Goods physically received and verified against supplier delivery order.
                @if($purchase->notes)
                    · {{ $purchase->notes }}
                @endif
            </div>

            {{-- Challan Signatures & QR Code --}}
            <div class="invoice-footer-compact pt-2 mt-auto border-top">
                <div class="row g-2 align-items-end text-center" style="font-size: 10px;">
                    <div class="col-4">
                        <div class="signature-box" style="margin-top: 24px;">
                            <div class="border-top border-dark pt-1 fw-semibold text-dark">
                                Supplier / Carrier Signature
                            </div>
                        </div>
                    </div>

                    {{-- QR Code & Verification Box --}}
                    <div class="col-4">
                        <div class="d-inline-flex align-items-center gap-1.5 px-2 py-1 rounded border bg-white shadow-xs">
                            <img src="{{ $qrCodeUrl }}" alt="QR" style="width: 34px; height: 34px; object-fit: contain;">
                            <div class="text-start" style="line-height: 1.15;">
                                <span class="text-muted fw-semibold d-block" style="font-size: 8px;"><i class="fas fa-qrcode me-0.5"></i>Scan to Verify</span>
                                <span class="font-monospace text-dark fw-bold" style="font-size: 9px;">#{{ $purchase->purchase_no }}</span>
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
                                Store In-Charge / Receiver
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-center text-muted mt-2 d-flex justify-content-between align-items-center" style="font-size: 8.5px; line-height: 1;">
                    <span>Page 2 / 2 (Receiving Challan Copy)</span>
                    <span>{{ $settings['business_name'] ?? 'Idea Publication' }} · Store Inventory Inward Memo</span>
                    <span>ID: {{ $purchase->purchase_no }}</span>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- ========================================================================= --}}
{{-- STEP-BY-STEP PAYMENT & INSTALLMENTS TRACKER CARD (ধাপে ধাপে জমা ও কিস্তি হিসাব) --}}
{{-- ========================================================================= --}}
<div class="row justify-content-center mt-3 d-print-none">
    <div class="col-lg-10">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-success-subtle text-success p-2 rounded-circle">
                        <i class="fas fa-hand-holding-dollar fs-6"></i>
                    </span>
                    <div>
                        <h5 class="fw-bold mb-0 text-dark">টাকা পরিশোধ ও কিস্তির খতিয়ান (Payment & Installments History)</h5>
                        <small class="text-muted">এই ক্রয়ের বিপরীতে দেওয়া সকল কিস্তি ও পেমেন্ট ভাউচারের পূর্ণাঙ্গ হিসাব</small>
                    </div>
                </div>
                @if($purchase->due_amount > 0)
                    <button type="button" class="btn btn-sm btn-success rounded-pill px-3 fw-semibold shadow-xs" data-bs-toggle="modal" data-bs-target="#paymentModal">
                        <i class="fas fa-plus me-1"></i> + কিস্তি / পরিশোধ জমা নিন
                    </button>
                @endif
            </div>
            <div class="card-body p-0">
                @if($purchase->payments->isEmpty())
                    <div class="p-4 text-center text-muted">
                        <i class="fas fa-receipt fs-2 opacity-50 mb-2"></i>
                        <p class="mb-0">এখনও পর্যন্ত কোনো পরিশোধ বা কিস্তি রেকর্ড করা হয়নি।</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr class="small text-muted text-uppercase" style="font-size: 11px;">
                                    <th class="ps-3">ভাউচার নং</th>
                                    <th>পরিশোধের তারিখ</th>
                                    <th>পরিশোধের পরিমাণ</th>
                                    <th>পেমেন্ট মাধ্যম</th>
                                    <th>রেফারেন্স / বিবরণ</th>
                                    <th>এন্ট্রি করেছেন</th>
                                    <th class="text-end pe-3">অ্যাকশন</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($purchase->payments as $pay)
                                    <tr>
                                        <td class="ps-3 fw-bold font-monospace">
                                            <a href="{{ route('admin.purchases.payments.voucher', $pay->id) }}" class="text-primary text-decoration-none">
                                                <i class="fas fa-receipt me-1"></i>{{ $pay->payment_no }}
                                            </a>
                                        </td>
                                        <td>{{ $pay->payment_date ? $pay->payment_date->format('d M, Y') : '—' }}</td>
                                        <td class="fw-bold text-success font-monospace fs-6">৳{{ number_format($pay->amount, 2) }}</td>
                                        <td>
                                            <span class="badge bg-light text-dark border">
                                                {{ $paymentMethods[$pay->payment_method] ?? ucfirst($pay->payment_method) }}
                                            </span>
                                        </td>
                                        <td class="text-muted small">{{ $pay->transaction_ref ?: ($pay->note ?: '—') }}</td>
                                        <td class="text-muted small">{{ $pay->recorder->name ?? 'Admin' }}</td>
                                        <td class="text-end pe-3">
                                            <a href="{{ route('admin.purchases.payments.voucher', $pay->id) }}" class="btn btn-xs btn-outline-primary rounded-pill px-2.5" title="ভাউচার স্লিপ প্রিন্ট করুন">
                                                <i class="fas fa-print me-1"></i>ভাউচার
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Payment Modal --}}
@if($purchase->due_amount > 0)
<div class="modal fade d-print-none" id="paymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom py-3 bg-light">
                <h5 class="modal-title fw-bold text-success"><i class="fas fa-hand-holding-dollar me-2"></i>Record Installment / Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.purchases.payments.store') }}" method="POST">
                @csrf
                <input type="hidden" name="purchase_id" value="{{ $purchase->id }}">

                <div class="modal-body p-4">
                    <div class="alert alert-info py-2 rounded-3 small mb-3">
                        সরবরাহকারী / প্রতিষ্ঠান: <strong>{{ $purchase->party_name }}</strong> | Current Due: <strong>৳{{ number_format($purchase->due_amount, 2) }}</strong>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Payment Date <span class="text-danger">*</span></label>
                        <input type="date" name="payment_date" class="form-control rounded-3" value="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Payment Amount (৳) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="amount" class="form-control rounded-3 fw-bold text-success fs-5" 
                               value="{{ $purchase->due_amount }}" max="{{ $purchase->due_amount }}" min="1" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Payment Method <span class="text-danger">*</span></label>
                        <select name="payment_method" class="form-select rounded-3" required>
                            @foreach($paymentMethods as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Check / Transaction Reference Number</label>
                        <input type="text" name="transaction_ref" class="form-control rounded-3" placeholder="Optional (Bank Trx ID / Check No)">
                    </div>

                    <div class="mb-2">
                        <label class="form-label small fw-semibold text-muted">Notes / Remarks</label>
                        <textarea name="note" rows="2" class="form-control rounded-3" placeholder="Payment details or installment remarks..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold">Confirm Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- Unified Purchases Branding & Memo Settings Modal Partial --}}
@include('admin.purchases.partials.branding-modal')

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
</script>

<style>
/* Invoice styling: Screen & Print */
.invoice-page-card {
    font-size: 10px;
    color: #1e293b;
    min-height: 980px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding-left: 0.4in !important;
    padding-right: 0.4in !important;
    box-sizing: border-box !important;
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
        font-size: 10px !important;
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
        overflow: visible !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    /* Suppress ALL Admin Layout Chrome */
    .adm-sidebar, .adm-side, .adm-topbar, .adm-top, .adm-backdrop,
    .adm-header, .breadcrumb, .alert, nav, footer, .btn,
    .d-print-none, [class*="d-print-none"],
    .adm-content > .d-flex.mb-4,
    .adm-content > .alert,
    .card.d-print-none {
        display: none !important;
        visibility: hidden !important;
        height: 0 !important;
        margin: 0 !important;
        padding: 0 !important;
        overflow: hidden !important;
    }

    .adm-main, .adm-content, .container-fluid {
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
        border: none !important;
        box-shadow: none !important;
        overflow: visible !important;
    }

    #invoicePrintWrapper, .col-lg-10 {
        width: 100% !important;
        max-width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        border: none !important;
        box-shadow: none !important;
        float: none !important;
    }

    /* Grid Flex Maintenance in Print */
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
        max-width: 58.333333% !important;
        width: 58.333333% !important;
    }

    .col-5 {
        flex: 0 0 41.666667% !important;
        max-width: 41.666667% !important;
        width: 41.666667% !important;
    }

    .col-6 {
        flex: 0 0 50% !important;
        max-width: 50% !important;
        width: 50% !important;
    }

    .col-4 {
        flex: 0 0 33.333333% !important;
        max-width: 33.333333% !important;
        width: 33.333333% !important;
    }

    .col-12 {
        flex: 0 0 100% !important;
        max-width: 100% !important;
        width: 100% !important;
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

@endsection
