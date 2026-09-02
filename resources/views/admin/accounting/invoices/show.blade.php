@extends('layouts.admin')

@php
    $typeTitles = [
        'challan'   => 'Delivery Challan #',
        'quotation' => 'Quotation / Proforma #',
        'tender'    => 'Tender Proposal #',
        'invoice'   => 'Bill & Invoice #'
    ];
    $docTitle = ($typeTitles[$invoice->type] ?? 'Invoice #') . $invoice->invoice_no;
    
    $settings = $invoiceSettings ?? \App\Http\Controllers\Admin\IdeaAccountingController::getInvoiceSettings();
    $bizLogo = $settings['logo'] ?? '/images/logo.png';
    $logoSrc = \App\Support\SiteSetting::resolveImageUrl($bizLogo, 'images/logo.png') ?: asset('images/logo.png');

    $invoiceUrl = route('admin.accounting.invoices.show', $invoice->id);
    $qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=130x130&margin=4&data=" . urlencode($invoiceUrl);

    $totalQuantity = 0;
    foreach($invoice->items ?? [] as $it) {
        $totalQuantity += (float)($it['quantity'] ?? 1);
    }

    $creatorName = !empty($settings['default_creator_name']) ? $settings['default_creator_name'] : ($invoice->creator_name ?? 'Idea Publication Authority');
    $creatorDesignation = !empty($settings['default_creator_designation']) ? $settings['default_creator_designation'] : ($invoice->creator_designation_en ?? 'Authorized Signatory / Billing In-Charge');
    
    $recipientNameSize = $settings['challan_recipient_name_size'] ?? '13px';
    $recipientPhoneSize = $settings['challan_recipient_phone_size'] ?? '12px';
    $recipientAddressSize = $settings['challan_recipient_address_size'] ?? '11.5px';
    $recipientDesigSize = $settings['challan_recipient_desig_size'] ?? '11.5px';
    $recipientOrgSize = $settings['challan_recipient_org_size'] ?? '12px';

    // Fetch books map for rich details like author_name, cover price etc.
    $bookIds = collect($invoice->items ?? [])->pluck('book_id')->filter()->unique()->toArray();
    $bookTitles = collect($invoice->items ?? [])->pluck('title')->filter()->unique()->toArray();
    $matchedBooks = \Modules\Book\Models\Book::whereIn('id', $bookIds)
        ->orWhereIn('title', $bookTitles)
        ->get()
        ->keyBy('id');
    $matchedBooksByTitle = $matchedBooks->keyBy('title');
@endphp

@section('title', $docTitle)
@section('heading', $docTitle)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.accounting.index') }}">Accounting</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.accounting.invoices.index') }}">Invoices & Challans</a></li>
    <li class="breadcrumb-item active" aria-current="page">#{{ $invoice->invoice_no }}</li>
@endsection

@section('actions')
    <div class="d-flex flex-wrap gap-2">
        {{-- Collect Installment Payment Button --}}
        @if(in_array($invoice->type, ['invoice', 'challan']) && $invoice->due_amount > 0)
            <button type="button" class="btn btn-success btn-sm rounded-pill px-3 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#recordInvoicePaymentModal">
                <i class="fas fa-hand-holding-dollar me-1.5"></i> কিস্তি জমা নিন
            </button>
        @endif

        {{-- Customer Ledger Link --}}
        <a href="{{ route('admin.accounting.customer-ledger.index', ['customer_name' => $invoice->customer_name, 'customer_phone' => $invoice->customer_phone]) }}" class="btn btn-outline-info text-dark btn-sm rounded-pill px-3 fw-semibold shadow-sm" title="গ্রাহকের সম্পূর্ণ খতিয়ান দেখুন">
            <i class="fas fa-book-bookmark me-1 text-primary"></i> গ্রাহক খতিয়ান
        </a>

        <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm fw-semibold" onclick="window.print()">
            <i class="fas fa-print me-1.5"></i> Print / PDF
        </button>

        {{-- Send Invoice Link to Customer Email Button --}}
        <button type="button" class="btn btn-outline-success btn-sm rounded-pill px-3 fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#sendInvoiceEmailModal" title="Send digital invoice link to customer email">
            <i class="fas fa-paper-plane me-1.5"></i> Send Email
            @if($invoice->emailed_at)
                <span class="badge bg-success text-white ms-1 px-1.5 py-0.5 rounded-pill" title="Email sent">✓</span>
            @endif
        </button>

        {{-- Copy Customer Public Link --}}
        <button type="button" class="btn btn-outline-secondary text-dark btn-sm rounded-pill px-3 fw-semibold shadow-sm" onclick="copyCustomerShareLink()" id="btnAdminCopyLink" title="Copy public share link for customer">
            <i class="fas fa-share-nodes me-1 text-primary"></i> Copy Link
        </button>

        <a href="{{ $invoice->public_url }}" target="_blank" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-sm" title="Preview public view">
            <i class="fas fa-arrow-up-right-from-square me-1"></i> Customer View
        </a>

        {{-- Edit Document Button --}}
        <a href="{{ route('admin.accounting.invoices.edit', $invoice->id) }}" class="btn btn-warning text-dark btn-sm rounded-pill px-3 fw-semibold shadow-sm">
            <i class="fas fa-edit me-1"></i> Edit Document
        </a>

        {{-- Customize Memo Header Settings Button --}}
        <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#invoiceSettingsModal" title="Customize invoice branding header">
            <i class="fas fa-palette me-1 text-primary"></i> Memo Settings
        </button>

        {{-- Convert to Invoice/Challan if currently Quotation or Tender --}}
        @if(in_array($invoice->type, ['quotation', 'tender']))
            <form action="{{ route('admin.accounting.invoices.convert', $invoice->id) }}" method="POST" class="d-inline"
                  data-confirm="আপনি কি এই কোটেশন/টেন্ডারটিকে চূড়ান্ত ইনভয়েস/বিল এ রূপান্তর করতে চান?" data-confirm-title="বিল রূপান্তর" data-confirm-icon="question">
                @csrf
                <input type="hidden" name="target_type" value="invoice">
                <button type="submit" class="btn btn-success btn-sm rounded-pill px-3 fw-semibold shadow-sm">
                    <i class="fas fa-receipt me-1"></i> Convert to Bill
                </button>
            </form>

            <form action="{{ route('admin.accounting.invoices.convert', $invoice->id) }}" method="POST" class="d-inline"
                  data-confirm="আপনি কি এই কোটেশন/টেন্ডারটিকে ডেলিভারি চালানে রূপান্তর করতে চান?" data-confirm-title="চালান রূপান্তর" data-confirm-icon="question">
                @csrf
                <input type="hidden" name="target_type" value="challan">
                <button type="submit" class="btn btn-info text-white btn-sm rounded-pill px-3 fw-semibold shadow-sm">
                    <i class="fas fa-truck me-1"></i> Convert to Challan
                </button>
            </form>
        @endif

        <a href="{{ route('admin.accounting.invoices.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-xs">
            <i class="fas fa-arrow-left me-1"></i> Back to List
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
                <i class="fas fa-scale-balanced me-1.5"></i> Income & Expense Ledger
            </a>
            <a href="{{ route('admin.accounting.invoices.index') }}" 
               class="nav-link rounded-pill px-3.5 py-2 fw-semibold text-dark hover-bg-light">
                <i class="fas fa-file-invoice-dollar me-1.5"></i> Invoices, Challans & Quotations
            </a>
            <a href="{{ route('admin.accounting.customer-ledger.index') }}" 
               class="nav-link rounded-pill px-3.5 py-2 fw-semibold text-dark hover-bg-light">
                <i class="fas fa-book-bookmark me-1.5"></i> Customer Ledgers & Statements
            </a>
            <a href="{{ route('admin.accounting.invoices.create') }}" 
               class="nav-link rounded-pill px-3.5 py-2 fw-semibold text-dark hover-bg-light">
                <i class="fas fa-file-circle-plus me-1.5"></i> Create New Invoice
            </a>
        </div>

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
    </div>
</div>

<div class="row justify-content-center" id="invoicePrintWrapper">
    <div class="col-lg-10">

        {{-- ========================================================================= --}}
        {{-- PAGE 1: CASH MEMO / INVOICE (or Quotation/Tender)                         --}}
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
                <div class="p-1.5 bg-light rounded-2 border mb-2" style="font-size: 10px;">
                    @if($invoice->reference_no)
                        <div class="text-muted mb-0.5">
                            <strong class="text-dark">
                                @if($invoice->type === 'tender') Tender Ref:
                                @elseif($invoice->type === 'quotation') Quotation Ref:
                                @elseif($invoice->type === 'challan') Challan Ref:
                                @else Ref / PO No: @endif
                            </strong> 
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

            {{-- Customer & Billed To Info --}}
            <div class="p-2.5 bg-light rounded-2 border mb-2.5 destination-box" style="font-size: 12px; box-sizing: border-box;">
                <div class="row g-2 align-items-start m-0">
                    <div class="col-7 p-0 pe-2">
                        <div class="fw-bold text-dark mb-1" style="font-size: 12px;"><i class="fas fa-user-tag me-1 text-primary"></i>Client / Customer Information:</div>
                        <table class="table-borderless p-0 m-0 w-100" style="font-size: 12px; line-height: 1.45;">
                            @if($invoice->customer_name)
                                <tr>
                                    <td class="text-muted pe-1 text-nowrap" style="width: 110px; vertical-align: top; font-size: 11px;">Name:</td>
                                    <td class="fw-bold text-dark" style="font-size: {{ $recipientNameSize }};">{{ $invoice->customer_name }}</td>
                                </tr>
                            @endif
                            @if(!empty($invoice->customer_designation))
                                <tr>
                                    <td class="text-muted pe-1 text-nowrap" style="vertical-align: top; font-size: 11px;">Designation:</td>
                                    <td class="fw-semibold text-dark" style="font-size: {{ $recipientDesigSize }};">{{ $invoice->customer_designation }}</td>
                                </tr>
                            @endif
                            @if($invoice->customer_org)
                                <tr>
                                    <td class="text-muted pe-1 text-nowrap" style="vertical-align: top; font-size: 11px;">Organization:</td>
                                    <td class="fw-semibold text-primary" style="font-size: {{ $recipientOrgSize }};">{{ $invoice->customer_org }}</td>
                                </tr>
                            @endif
                            @if($invoice->customer_address)
                                <tr>
                                    <td class="text-muted pe-1 text-nowrap" style="vertical-align: top; font-size: 11px;">Address:</td>
                                    <td class="text-dark" style="font-size: {{ $recipientAddressSize }}; line-height: 1.35;">{{ $invoice->customer_address }}</td>
                                </tr>
                            @endif
                            @if($invoice->customer_phone)
                                <tr>
                                    <td class="text-muted pe-1 text-nowrap" style="vertical-align: top; font-size: 11px;">Phone:</td>
                                    <td class="text-dark fw-bold font-monospace" style="font-size: {{ $recipientPhoneSize }};">{{ $invoice->customer_phone }}</td>
                                </tr>
                            @endif
                        </table>
                    </div>
                    <div class="col-5 p-0 ps-2 text-end">
                        <div class="text-muted text-uppercase fw-semibold mb-1" style="font-size: 11px;">Order & Payment Details:</div>
                        <div style="font-size: 12px; line-height: 1.5;">
                            <div>Type: <strong>{{ ucfirst($invoice->type) }}</strong> · Method: <strong>{{ $invoice->payment_method ?? 'Cash / Bank' }}</strong></div>
                            @if(in_array($invoice->type, ['invoice', 'challan']))
                            <div>
                                Status: 
                                @if($invoice->payment_status === 'paid')
                                    <span class="badge bg-success-subtle text-success border px-2 py-0.5" style="font-size: 10.5px;">Paid</span>
                                @elseif($invoice->payment_status === 'partial')
                                    <span class="badge bg-warning-subtle text-dark border px-2 py-0.5" style="font-size: 10.5px;">Partially Paid</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border px-2 py-0.5" style="font-size: 10.5px;">Due</span>
                                @endif
                                · Prepared by: <strong>{{ $invoice->creator->name ?? 'Admin' }}</strong>
                            </div>
                        @else
                            <div>Proposal Status: <span class="badge bg-primary-subtle text-primary border px-2 py-0.5" style="font-size: 10.5px;">Proposed</span></div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Items / Price Schedule Table --}}
            <div class="table-responsive mb-2">
                <table class="table table-bordered table-sm align-middle invoice-table mb-0" style="font-size: 10px;">
                    <thead class="table-light">
                        <tr class="text-muted text-uppercase" style="font-size: 9px;">
                            <th class="text-center py-1 px-1" style="width: 26px;">#</th>
                            <th class="py-1 px-1.5">
                                @if($invoice->sales_category === 'stationery')
                                    পণ্যের নাম ও বিবরণ (Item Title & Description)
                                @elseif($invoice->sales_category === 'printing_goods')
                                    কাজের নাম ও প্রিন্টিং বিবরণ (Job / Printing Description)
                                @elseif($invoice->sales_category === 'other')
                                    মালের বিবরণ ও বিবরণী (Description)
                                @else
                                    বইয়ের নাম ও বিবরণ (Book Title & Description)
                                @endif
                            </th>
                            <th class="py-1 px-1" style="width: 105px;">
                                @if($invoice->sales_category === 'stationery' || $invoice->sales_category === 'printing_goods')
                                    স্পেক / সাইজ
                                @elseif($invoice->sales_category === 'other')
                                    স্পেসিফিকেশন
                                @else
                                    লেখক (Author)
                                @endif
                            </th>
                            <th class="text-center py-1 px-1" style="width: 45px;">একক</th>
                            <th class="text-center py-1 px-1" style="width: 45px;">পরিমাণ</th>
                            <th class="text-end py-1 px-1" style="width: 70px;">
                                @if($invoice->sales_category === 'stationery')
                                    MRP (৳)
                                @elseif($invoice->sales_category === 'printing_goods')
                                    বেসিক রেট
                                @else
                                    গায়ের দর
                                @endif
                            </th>
                            <th class="text-center py-1 px-1" style="width: 55px;">কমিশন %</th>
                            <th class="text-end py-1 px-1" style="width: 80px;">বিক্রয় দর</th>
                            <th class="text-end py-1 pe-1.5" style="width: 85px;">মোট (৳)</th>
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
                                $unitName = $item['unit'] ?? ($invoice->sales_category === 'books' ? 'কপি' : 'পিস');
                                
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
                                <td class="text-center py-0.5 px-1 text-muted font-monospace" style="font-size: 9px;">{{ $unitName }}</td>
                                <td class="text-center py-0.5 px-1 fw-bold">{{ $qty }}</td>
                                <td class="text-end py-0.5 px-1">৳{{ number_format($coverPrice, 2) }}</td>
                                <td class="text-center py-0.5 px-1">
                                    @if($commPercent > 0)
                                        <span class="badge bg-danger-subtle text-danger border px-1 py-0" style="font-size: 8.5px;">{{ $commPercent }}%</span>
                                    @else
                                        <span class="text-muted" style="font-size: 8.5px;">—</span>
                                    @endif
                                </td>
                                <td class="text-end py-0.5 px-1 fw-semibold text-dark">৳{{ number_format($netUnitPrice, 2) }}</td>
                                <td class="text-end py-0.5 pe-1.5 fw-bold text-dark">৳{{ number_format($lineSubtotal, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    @php
                        $specialCommPercent = ($invoice->subtotal > 0 && $invoice->discount > 0)
                            ? round(($invoice->discount / $invoice->subtotal) * 100, 1)
                            : 0;

                        $tfootRows = 3; // Subtotal + Special Discount + Grand Total
                        if ($invoice->tax > 0) $tfootRows++;
                        if (in_array($invoice->type, ['invoice', 'challan'])) {
                            $tfootRows++; // Paid
                            if ($invoice->due_amount > 0) $tfootRows++; // Due
                        }
                    @endphp
                    <tfoot>
                        <tr>
                            <td colspan="6" rowspan="{{ $tfootRows }}" class="py-2 px-2.5 border bg-light bg-opacity-25" style="vertical-align: middle;">
                                <div class="p-1">
                                    <span class="text-muted fw-bold d-block mb-1" style="font-size: 9.5px;">
                                        <i class="fas fa-coins me-1 text-primary"></i>Total in Words:
                                    </span>
                                    <div class="fw-bold text-dark text-wrap" style="font-size: 11.5px; line-height: 1.45;">
                                        @takaInWordsEn($invoice->grand_total)
                                    </div>
                                </div>
                            </td>
                            <td class="text-end py-0.5 px-1.5 fw-semibold">Subtotal:</td>
                            <td class="text-end py-0.5 pe-1.5 fw-semibold">৳{{ number_format($invoice->subtotal, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="text-end py-0.5 px-1.5 text-danger fw-semibold">
                                Special Discount @if($specialCommPercent > 0)({{ $specialCommPercent }}%)@endif:
                            </td>
                            <td class="text-end py-0.5 pe-1.5 text-danger fw-semibold">
                                {{ $invoice->discount > 0 ? '- ৳' . number_format($invoice->discount, 2) : '৳0.00' }}
                            </td>
                        </tr>
                        @if($invoice->tax > 0)
                            <tr>
                                <td class="text-end py-0.5 px-1.5 text-muted fw-semibold">VAT / Tax:</td>
                                <td class="text-end py-0.5 pe-1.5 text-muted fw-semibold">+ ৳{{ number_format($invoice->tax, 2) }}</td>
                            </tr>
                        @endif
                        <tr class="table-light">
                            <td class="text-end py-1 px-1.5 fw-bold text-dark">Grand Total:</td>
                            <td class="text-end py-1 pe-1.5 fw-bold text-primary" style="font-size: 11.5px;">৳{{ number_format($invoice->grand_total, 2) }}</td>
                        </tr>
                        @if(in_array($invoice->type, ['invoice', 'challan']))
                            <tr>
                                <td class="text-end py-0.5 px-1.5 text-success fw-bold">Amount Paid:</td>
                                <td class="text-end py-0.5 pe-1.5 text-success fw-bold">৳{{ number_format($invoice->paid_amount, 2) }}</td>
                            </tr>
                            @if($invoice->due_amount > 0)
                                <tr class="table-danger">
                                    <td class="text-end py-0.5 px-1.5 text-danger fw-bold">Due Balance:</td>
                                    <td class="text-end py-0.5 pe-1.5 text-danger fw-bold">৳{{ number_format($invoice->due_amount, 2) }}</td>
                                </tr>
                            @endif
                        @endif
                    </tfoot>
                </table>
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

            {{-- Signature & QR Code Footer (Positioned at A4/Letter page bottom) --}}
            <div class="invoice-footer-compact pt-2 mt-auto border-top">
                <div class="row g-2 align-items-end text-center" style="font-size: 10px;">
                    <div class="col-4">
                        <div class="signature-box" style="margin-top: 36px;">
                            <div class="border-top border-dark pt-1 fw-semibold text-dark">
                                Customer's Signature
                            </div>
                        </div>
                    </div>

                    {{-- QR Code & Verification Box --}}
                    <div class="col-4">
                        <div class="d-inline-flex align-items-center gap-1.5 px-2 py-1 rounded border bg-white shadow-xs">
                            <img src="{{ $qrCodeUrl }}" alt="QR" style="width: 34px; height: 34px; object-fit: contain;">
                            <div class="text-start" style="line-height: 1.15;">
                                <span class="text-muted fw-semibold d-block" style="font-size: 8px;"><i class="fas fa-qrcode me-0.5"></i>Scan to Verify</span>
                                <span class="font-monospace text-dark fw-bold" style="font-size: 9px;">#{{ $invoice->invoice_no }}</span>
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
                                Authorized Signature / Bill Creator
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
                        <div class="col-7 p-0 pe-2">
                            <div class="fw-bold text-dark mb-1 d-flex align-items-center justify-content-between" style="font-size: 12px;">
                                <span><i class="fas fa-truck-ramp-box me-1 text-primary"></i>Delivery Destination & Recipient:</span>
                            </div>
                            <table class="table-borderless p-0 m-0 w-100 recipient-info-table" style="line-height: 1.45;">
                                @if($invoice->customer_name)
                                    <tr>
                                        <td class="text-muted pe-1 text-nowrap" style="width: 115px; vertical-align: top; font-size: 11px;">Recipient Name:</td>
                                        <td class="fw-bold text-dark target-recipient-name" id="challanRecipientName" style="font-size: {{ $recipientNameSize }};">{{ $invoice->customer_name }}</td>
                                    </tr>
                                @endif
                                @if(!empty($invoice->customer_designation))
                                    <tr>
                                        <td class="text-muted pe-1 text-nowrap" style="vertical-align: top; font-size: 11px;">Designation:</td>
                                        <td class="fw-semibold text-dark target-recipient-desig" id="challanRecipientDesig" style="font-size: {{ $recipientDesigSize }};">{{ $invoice->customer_designation }}</td>
                                    </tr>
                                @endif
                                @if($invoice->customer_org)
                                    <tr>
                                        <td class="text-muted pe-1 text-nowrap" style="vertical-align: top; font-size: 11px;">Organization:</td>
                                        <td class="fw-semibold text-primary target-recipient-org" id="challanRecipientOrg" style="font-size: {{ $recipientOrgSize }};">{{ $invoice->customer_org }}</td>
                                    </tr>
                                @endif
                                @if($invoice->customer_address)
                                    <tr>
                                        <td class="text-muted pe-1 text-nowrap" style="vertical-align: top; font-size: 11px;">Address:</td>
                                        <td class="text-dark target-recipient-address" id="challanRecipientAddr" style="font-size: {{ $recipientAddressSize }}; line-height: 1.35;">{{ $invoice->customer_address }}</td>
                                    </tr>
                                @endif
                                @if($invoice->customer_phone)
                                    <tr>
                                        <td class="text-muted pe-1 text-nowrap" style="vertical-align: top; font-size: 11px;">Phone / Mobile:</td>
                                        <td class="text-dark fw-bold font-monospace target-recipient-phone" id="challanRecipientPhone" style="font-size: {{ $recipientPhoneSize }};">{{ $invoice->customer_phone }}</td>
                                    </tr>
                                @endif
                            </table>
                        </div>
                        <div class="col-5 p-0 ps-2 text-end">
                            <div class="text-muted text-uppercase fw-semibold mb-1" style="font-size: 11px;">Challan Tracking & Dispatch Info:</div>
                            <div style="font-size: 11.5px; line-height: 1.5;">
                                <div>Challan Type: <strong>Goods / Book Delivery</strong></div>
                                <div>Total Items: <strong>{{ count($invoice->items ?? []) }} items</strong> · Total Qty: <strong class="text-primary">{{ $totalQuantity }} pcs</strong></div>
                                <div class="text-muted">Dispatcher / Packer: <strong>{{ $creatorName }}</strong></div>
                            </div>
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
                                <th class="text-center py-1 px-1" style="width: 50px;">Qty</th>
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
                                    <td class="text-center py-0.5 px-1 fw-bold text-primary">{{ $item['quantity'] ?? 1 }}</td>
                                    <td class="text-center py-0.5 px-1 text-muted">Brand New</td>
                                    <td class="py-0.5 px-1.5 text-muted">Verified</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="table-light">
                                <td colspan="5" class="text-end py-1 px-1.5 fw-bold">Total Delivered Items / Quantity:</td>
                                <td class="text-center py-1 px-1 fw-bold text-primary" style="font-size: 11px;">{{ $totalQuantity }}</td>
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

                {{-- Challan Signatures & QR Code --}}
                <div class="invoice-footer-compact pt-2 mt-auto border-top">
                    <div class="row g-2 align-items-end text-center" style="font-size: 10px;">
                        <div class="col-4">
                            <div class="signature-box" style="margin-top: 24px;">
                                <div class="border-top border-dark pt-1 fw-semibold text-dark">
                                    Recipient's Signature
                                </div>
                            </div>
                        </div>

                        {{-- QR Code & Verification Box --}}
                        <div class="col-4">
                            <div class="d-inline-flex align-items-center gap-1.5 px-2 py-1 rounded border bg-white shadow-xs">
                                <img src="{{ $qrCodeUrl }}" alt="QR" style="width: 34px; height: 34px; object-fit: contain;">
                                <div class="text-start" style="line-height: 1.15;">
                                    <span class="text-muted fw-semibold d-block" style="font-size: 8px;"><i class="fas fa-qrcode me-0.5"></i>Scan to Verify</span>
                                    <span class="font-monospace text-dark fw-bold" style="font-size: 9px;">#{{ $invoice->invoice_no }}</span>
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

{{-- ========================================================================= --}}
{{-- STEP-BY-STEP PAYMENT & INSTALLMENTS TRACKER CARD (ধাপে ধাপে জমা ও কিস্তি হিসাব) --}}
{{-- ========================================================================= --}}
@if(in_array($invoice->type, ['invoice', 'challan']))
<div class="row justify-content-center mt-3 d-print-none">
    <div class="col-lg-10">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-success-subtle text-success p-2 rounded-circle">
                        <i class="fas fa-hand-holding-dollar fs-6"></i>
                    </span>
                    <div>
                        <h5 class="card-title fw-bold mb-0 text-dark">ধাপে ধাপে কিস্তি ও জমা পরিশোধের হিসাব</h5>
                        <div class="text-muted small">ইনভয়েস #{{ $invoice->invoice_no }} — {{ $invoice->customer_name }}</div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('admin.accounting.customer-ledger.index', ['customer_name' => $invoice->customer_name, 'customer_phone' => $invoice->customer_phone]) }}" class="btn btn-outline-info text-dark btn-sm rounded-pill px-3 fw-semibold shadow-xs">
                        <i class="fas fa-book-bookmark me-1 text-primary"></i> সম্পূর্ণ গ্রাহক খতিয়ান
                    </a>
                    @if($invoice->due_amount > 0)
                        <button type="button" class="btn btn-success btn-sm rounded-pill px-3 fw-bold shadow-xs" data-bs-toggle="modal" data-bs-target="#recordInvoicePaymentModal">
                            <i class="fas fa-plus me-1"></i> নতুন কিস্তি জমা নিন
                        </button>
                    @endif
                </div>
            </div>

            <div class="card-body p-4">
                {{-- Payment Progress & Stats Row --}}
                @php
                    $pctPaid = ($invoice->grand_total > 0) ? min(100, round(($invoice->paid_amount / $invoice->grand_total) * 100, 1)) : 0;
                @endphp
                <div class="row g-3 mb-4">
                    <div class="col-md-3 col-6">
                        <div class="p-3 bg-light rounded-3 border text-center">
                            <div class="text-muted small fw-semibold">মোট বিলের দাবি</div>
                            <div class="fs-5 fw-bold text-dark font-monospace mt-1">৳{{ number_format($invoice->grand_total, 2) }}</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="p-3 bg-success-subtle rounded-3 border border-success-subtle text-center">
                            <div class="text-success small fw-semibold">মোট জমা / পরিশোধ</div>
                            <div class="fs-5 fw-bold text-success font-monospace mt-1">৳{{ number_format($invoice->paid_amount, 2) }}</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="p-3 {{ $invoice->due_amount > 0 ? 'bg-danger-subtle border-danger-subtle' : 'bg-light' }} rounded-3 border text-center">
                            <div class="{{ $invoice->due_amount > 0 ? 'text-danger' : 'text-muted' }} small fw-semibold">বর্তমান বকেয়া জের</div>
                            <div class="fs-5 fw-bold {{ $invoice->due_amount > 0 ? 'text-danger' : 'text-success' }} font-monospace mt-1">৳{{ number_format($invoice->due_amount, 2) }}</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="p-3 bg-light rounded-3 border text-center">
                            <div class="text-muted small fw-semibold">পরিশোধের শেষ তারিখ (ঐচ্ছিক)</div>
                            <div class="fs-6 fw-bold text-dark font-monospace mt-1">
                                @if($invoice->due_date && $invoice->due_amount > 0)
                                    <span class="{{ $invoice->is_overdue ? 'text-danger' : 'text-primary' }}">
                                        <i class="fas fa-calendar-day me-1"></i>{{ $invoice->due_date->format('d M, Y') }}
                                        @if($invoice->is_overdue)
                                            <span class="badge bg-danger text-white ms-1" style="font-size: 9px;">মেয়াদোত্তীর্ণ</span>
                                        @endif
                                    </span>
                                @elseif($invoice->due_amount <= 0)
                                    <span class="text-success small"><i class="fas fa-check-circle me-1"></i>সম্পূর্ণ পরিশোধিত</span>
                                @else
                                    <span class="text-muted small">নির্ধারিত নেই</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Visual Progress Bar --}}
                <div class="mb-4">
                    <div class="d-flex justify-content-between small text-muted mb-1.5 fw-semibold">
                        <span>পরিশোধের অগ্রগতি: <strong class="text-dark">{{ $pctPaid }}%</strong></span>
                        <span>স্থিতি: 
                            @if($invoice->payment_status === 'paid')
                                <span class="badge bg-success">পরিশোধিত (Paid)</span>
                            @elseif($invoice->payment_status === 'partial')
                                <span class="badge bg-warning text-dark">আংশিক জমা (Partial)</span>
                            @else
                                <span class="badge bg-danger">বকেয়া (Unpaid)</span>
                            @endif
                        </span>
                    </div>
                    <div class="progress" style="height: 10px; border-radius: 999px;">
                        <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" role="progressbar" style="width: {{ $pctPaid }}%" aria-valuenow="{{ $pctPaid }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>

                {{-- Installment Payments History Table --}}
                <h6 class="fw-bold text-dark mb-2.5">
                    <i class="fas fa-clock-rotate-left text-primary me-1.5"></i>জমা ও কিস্তির বিবরণী (Payment Logs)
                </h6>

                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle mb-0" style="font-size: 13px;">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 45px;">#</th>
                                <th style="width: 130px;">রসিদ নম্বর</th>
                                <th style="width: 110px;">জমার তারিখ</th>
                                <th class="text-end" style="width: 120px;">টাকার পরিমাণ</th>
                                <th style="width: 120px;">পেমেন্ট মাধ্যম</th>
                                <th>রেফারেন্স / বিবরণ</th>
                                <th style="width: 130px;">আদায়কারী</th>
                                <th class="text-center" style="width: 110px;">অ্যাকশন</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($invoice->payments as $pIdx => $pmt)
                                <tr>
                                    <td class="text-center text-muted fw-semibold">{{ $pIdx + 1 }}</td>
                                    <td>
                                        <span class="font-monospace fw-bold text-dark">#{{ $pmt->payment_no }}</span>
                                    </td>
                                    <td class="font-monospace">
                                        {{ $pmt->payment_date ? $pmt->payment_date->format('d M, Y') : '—' }}
                                    </td>
                                    <td class="text-end font-monospace fw-bold text-success">
                                        ৳{{ number_format($pmt->amount, 2) }}
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border px-2 py-1">
                                            {{ \App\Models\IdeaInvoicePayment::paymentMethods()[$pmt->payment_method] ?? ucfirst($pmt->payment_method) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="text-dark fw-medium">{{ $pmt->note ?: '—' }}</div>
                                        @if($pmt->transaction_ref)
                                            <div class="text-muted font-monospace" style="font-size: 11px;">Trx: {{ $pmt->transaction_ref }}</div>
                                        @endif
                                    </td>
                                    <td class="text-muted small">
                                        {{ $pmt->recorder?->name ?? 'Admin' }}
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex align-items-center justify-content-center gap-1">
                                            <a href="{{ route('admin.accounting.invoices.payments.receipt', $pmt->id) }}" class="btn btn-sm btn-outline-primary rounded-pill px-2 py-0.5" title="টাকা প্রাপ্তি রসিদ প্রিন্ট করুন">
                                                <i class="fas fa-receipt me-1"></i>রসিদ
                                            </a>

                                            <form action="{{ route('admin.accounting.invoices.payments.destroy', $pmt->id) }}" method="POST" class="d-inline"
                                                  data-confirm="আপনি কি নিশ্চিত যে এই পেমেন্ট রেকর্ডটি (#{{ $pmt->payment_no }}) মুছে ফেলতে চান?" data-confirm-title="পেমেন্ট ডিলিট">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle p-1" style="width: 26px; height: 26px; line-height: 1;" title="মুছে ফেলুন">
                                                    <i class="fas fa-trash-alt" style="font-size: 10px;"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">
                                        <i class="fas fa-receipt fs-3 mb-2 d-block text-secondary"></i>
                                        এখনও কোনো কিস্তি জমা রেকর্ড করা হয়নি।
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

{{-- 📧 EMAIL DISPATCH & DELIVERY REPORT SECTION (D-PRINT-NONE) --}}
<div class="row justify-content-center mt-3 mb-4 d-print-none">
    <div class="col-lg-10">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
            {{-- Card Header --}}
            <div class="card-header bg-white py-3 px-4 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-3">
                {{-- Left: Clean Title (No Bangla sentences) --}}
                <div class="d-flex align-items-center gap-3">
                    <div class="p-2.5 bg-success-subtle text-success rounded-circle fs-5 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                        <i class="fa-solid fa-envelope-circle-check"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">
                            Email Dispatch & Delivery Report
                        </h6>
                    </div>
                </div>
                
                {{-- Right: Header Tabs & Quick Actions --}}
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    @php
                        $emailLogs = $invoice->email_logs ?? [];
                        $totalDispatches = count($emailLogs);
                        $successCount = 0;
                        $failedCount = 0;
                        foreach($emailLogs as $l) {
                            if (($l['status'] ?? '') === 'failed') {
                                $failedCount++;
                            } else {
                                $successCount++;
                            }
                        }
                        $latestLog = !empty($emailLogs) ? reset($emailLogs) : null;
                        $latestSentAt = $invoice->emailed_at ?: (!empty($latestLog['sent_at']) ? \Carbon\Carbon::parse($latestLog['sent_at']) : null);
                    @endphp

                    {{-- Section Tabs --}}
                    <div class="btn-group btn-group-sm p-0.5 bg-light rounded-pill border">
                        <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 py-1 fw-semibold shadow-2xs" id="dispatchNavLogsBtn" onclick="switchDispatchTab('logs')">
                            <i class="fa-solid fa-list-check me-1"></i>Logs ({{ $totalDispatches }})
                        </button>
                        <button type="button" class="btn btn-sm btn-light rounded-pill px-3 py-1 fw-semibold text-dark" id="dispatchNavMsgBtn" onclick="switchDispatchTab('message')">
                            <i class="fa-solid fa-comment-dots me-1 text-success"></i>কাস্টমার মেসেজ ও অভিবাদন বার্তা কাস্টমাইজেশন
                        </button>
                    </div>

                    {{-- Copy Link --}}
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1.5 fw-semibold shadow-2xs text-dark" onclick="copyCustomerShareLink()" title="গ্রাহকের জন্য সরাসরি পাবলিক লিংক কপি করুন">
                        <i class="fas fa-copy me-1 text-primary"></i> Copy Link
                    </button>
                    
                    {{-- WhatsApp Share --}}
                    @php
                        $docTypeBn = ($invoice->type === 'challan' ? 'ডেলিভারি চালান' : ($invoice->type === 'quotation' ? 'মূল্য কোটেশন' : ($invoice->type === 'tender' ? 'টেন্ডার প্রপোজাল' : 'বিল / ইনভয়েস')));
                        $waTemplate = !empty($settings['whatsapp_message_template']) 
                            ? $settings['whatsapp_message_template'] 
                            : "{business_name} থেকে আপনার {doc_type} (#{invoice_no}) প্রস্তুত করা হয়েছে। সরাসরি দেখতে ভিজিট করুন: {invoice_url}";

                        $waMsg = str_replace(
                            ['{customer_name}', '{business_name}', '{doc_type}', '{invoice_no}', '{invoice_url}'],
                            [$invoice->customer_name ?? '', $settings['business_name'] ?? 'Idea Publication', $docTypeBn, $invoice->invoice_no, $invoice->public_url],
                            $waTemplate
                        );
                    @endphp
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $invoice->customer_phone ?? '') }}?text={{ urlencode($waMsg) }}" target="_blank" id="headerWhatsAppShareBtn" 
                       class="btn btn-sm btn-outline-success rounded-pill px-3 py-1.5 fw-semibold shadow-2xs" title="হোয়াটসঅ্যাপে গ্রাহককে ইনভয়েস লিংক পাঠান">
                        <i class="fab fa-whatsapp me-1 text-success"></i> WhatsApp
                    </a>

                    {{-- Send Email Modal Trigger --}}
                    <button type="button" class="btn btn-sm btn-success rounded-pill px-3.5 py-1.5 fw-semibold shadow-xs" data-bs-toggle="modal" data-bs-target="#sendInvoiceEmailModal">
                        <i class="fas fa-paper-plane me-1"></i> Send Email
                    </button>
                </div>
            </div>

            <div class="card-body p-4">
                {{-- ======================================================== --}}
                {{-- TAB PANEL 1: DISPATCH LOGS & STATS (Default View)         --}}
                {{-- ======================================================== --}}
                <div id="dispatchPanelLogs">
                    {{-- KPI Stats Row --}}
                    <div class="row g-3 mb-4">
                        {{-- Customer Email --}}
                        <div class="col-6 col-md-3">
                            <div class="p-3 bg-light rounded-3 border h-100 d-flex flex-column justify-content-between">
                                <span class="text-muted small fw-semibold d-block mb-1">
                                    <i class="fa-solid fa-user text-primary me-1"></i>গ্রাহকের ইমেইল
                                </span>
                                <div class="font-monospace fw-bold text-dark text-truncate" title="{{ $invoice->customer_email ?: 'নির্ধারিত নেই' }}" style="font-size: 13px;">
                                    {{ $invoice->customer_email ?: '—' }}
                                </div>
                            </div>
                        </div>

                        {{-- Total Dispatches --}}
                        <div class="col-6 col-md-3">
                            <div class="p-3 bg-light rounded-3 border h-100 d-flex flex-column justify-content-between">
                                <span class="text-muted small fw-semibold d-block mb-1">
                                    <i class="fa-solid fa-paper-plane text-info me-1"></i>মোট প্রেরণ সংখ্যা
                                </span>
                                <div class="fs-5 fw-bold text-dark font-monospace">
                                    {{ $totalDispatches }} <span class="fs-6 fw-normal text-muted">বার</span>
                                </div>
                            </div>
                        </div>

                        {{-- Latest Sent Date --}}
                        <div class="col-6 col-md-3">
                            <div class="p-3 bg-light rounded-3 border h-100 d-flex flex-column justify-content-between">
                                <span class="text-muted small fw-semibold d-block mb-1">
                                    <i class="fa-solid fa-clock text-warning me-1"></i>সর্বশেষ প্রেরণ
                                </span>
                                <div class="text-dark fw-bold" style="font-size: 12.5px;">
                                    @if($latestSentAt)
                                        <div>{{ $latestSentAt->format('d M, Y') }}</div>
                                        <small class="text-muted fw-normal">{{ $latestSentAt->format('h:i A') }} ({{ $latestSentAt->diffForHumans() }})</small>
                                    @else
                                        <span class="text-muted">— এখনও পাঠানো হয়নি —</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Delivery Health Status --}}
                        <div class="col-6 col-md-3">
                            <div class="p-3 {{ $totalDispatches > 0 ? 'bg-success-subtle border-success-subtle' : 'bg-light' }} rounded-3 border h-100 d-flex flex-column justify-content-between">
                                <span class="text-muted small fw-semibold d-block mb-1">
                                    <i class="fa-solid fa-shield-check text-success me-1"></i>ডেলিভারি হেলথ
                                </span>
                                <div>
                                    @if($totalDispatches > 0)
                                        <span class="badge bg-success text-white px-2.5 py-1 rounded-pill shadow-2xs">
                                            <i class="fa-solid fa-circle-check me-1"></i> সক্রিয় (Active)
                                        </span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary px-2.5 py-1 rounded-pill">
                                            <i class="fa-regular fa-clock me-1"></i> অপেক্ষমান
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Interactive Filter & Search Bar --}}
                    @if(!empty($emailLogs) && count($emailLogs) > 0)
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2.5 mb-3 p-2 bg-light rounded-3 border">
                            {{-- Filter Tabs --}}
                            <div class="d-flex align-items-center gap-1.5 flex-wrap">
                                <span class="small text-muted fw-semibold me-1"><i class="fa-solid fa-filter me-1 text-secondary"></i>ফিল্টার:</span>
                                <button type="button" class="btn btn-sm btn-white border rounded-pill px-3 py-1 active-filter fw-bold shadow-2xs text-dark" id="filterAllBtn" onclick="filterEmailLogs('all', this)">
                                    All ({{ $totalDispatches }})
                                </button>
                                <button type="button" class="btn btn-sm btn-white border rounded-pill px-3 py-1 shadow-2xs text-success fw-semibold" id="filterDeliveredBtn" onclick="filterEmailLogs('success', this)">
                                    <i class="fa-solid fa-circle-check me-1"></i>Delivered ({{ $successCount }})
                                </button>
                                @if($failedCount > 0)
                                    <button type="button" class="btn btn-sm btn-white border rounded-pill px-3 py-1 shadow-2xs text-danger fw-semibold" id="filterFailedBtn" onclick="filterEmailLogs('failed', this)">
                                        <i class="fa-solid fa-circle-xmark me-1"></i>Failed ({{ $failedCount }})
                                    </button>
                                @endif
                            </div>

                            {{-- Search Input --}}
                            <div class="input-group input-group-sm" style="max-width: 240px;">
                                <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                                <input type="text" id="emailLogsSearch" class="form-control bg-white border-start-0" placeholder="ইমেইল বা মেসেজ খুঁজুন..." oninput="searchEmailLogs(this.value)">
                            </div>
                        </div>

                        {{-- Dispatch Logs Table --}}
                        <div class="table-responsive rounded-3 border shadow-2xs">
                            <table class="table table-hover align-middle mb-0" id="emailDispatchLogsTable" style="font-size: 12.5px; width: 100%;">
                                <thead class="table-light text-secondary border-bottom">
                                    <tr>
                                        <th class="text-center" style="width: 40px;">#</th>
                                        <th style="width: 135px;">তারিখ ও সময়</th>
                                        <th style="min-width: 200px;">প্রাপক তালিকা (Recipients)</th>
                                        <th style="width: 140px;">প্রেরক (Sender)</th>
                                        <th style="min-width: 150px;">বার্তা / বিবরণ</th>
                                        <th style="width: 100px;">প্রেরণকারী</th>
                                        <th class="text-center" style="width: 105px;">স্ট্যাটাস</th>
                                        <th class="text-center" style="width: 85px;">অ্যাকশন</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($emailLogs as $idx => $log)
                                        @php
                                            $sentAt = !empty($log['sent_at']) ? \Carbon\Carbon::parse($log['sent_at']) : null;
                                            $recipients = $log['recipients'] ?? [];
                                            $failedRawList = $log['failed'] ?? [];
                                            $status = $log['status'] ?? 'success';
                                            
                                            // Collect all valid emails for re-sending
                                            $allRecipientEmails = $recipients;
                                        @endphp
                                        <tr class="email-log-row" data-status="{{ $status }}" data-search="{{ strtolower(implode(' ', array_merge($recipients, (array)$failedRawList, [$log['sender'] ?? '', $log['custom_message'] ?? '', $log['sent_by'] ?? '']))) }}">
                                            {{-- Row Number --}}
                                            <td class="text-center text-muted fw-semibold">{{ $totalDispatches - $idx }}</td>
                                            
                                            {{-- Date & Time --}}
                                            <td class="text-nowrap">
                                                <div class="fw-bold text-dark font-monospace" style="font-size: 12px;">
                                                    {{ $sentAt ? $sentAt->format('d M, Y') : '—' }}
                                                </div>
                                                <small class="text-muted d-block" style="font-size: 11px;">
                                                    {{ $sentAt ? $sentAt->format('h:i A') : '' }} · {{ $sentAt ? $sentAt->diffForHumans() : '' }}
                                                </small>
                                            </td>

                                            {{-- Recipients List with Clean Email Badges and Error Tooltips --}}
                                            <td>
                                                <div class="d-flex flex-column gap-1">
                                                    {{-- Successful Recipients --}}
                                                    @if(!empty($recipients))
                                                        <div class="d-flex flex-wrap gap-1">
                                                            @foreach($recipients as $recEmail)
                                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1 font-monospace" style="font-size: 11px;" title="সফলভাবে প্রেরিত">
                                                                    <i class="fa-solid fa-circle-check text-success me-1"></i>{{ $recEmail }}
                                                                </span>
                                                            @endforeach
                                                        </div>
                                                    @endif

                                                    {{-- Failed Recipients (Cleanly Parsed) --}}
                                                    @if(!empty($failedRawList))
                                                        <div class="d-flex flex-column gap-1">
                                                            @foreach($failedRawList as $fRaw)
                                                                @php
                                                                $fEmail = $fRaw;
                                                                $fError = null;
                                                                if (is_array($fRaw)) {
                                                                    $fEmail = $fRaw['email'] ?? 'Unknown';
                                                                    $fError = $fRaw['error'] ?? null;
                                                                } elseif (preg_match('/^([^\s(]+)\s*\((.*)\)$/', $fRaw, $matches)) {
                                                                    $fEmail = $matches[1];
                                                                    $fError = $matches[2];
                                                                }
                                                                if (!in_array($fEmail, $allRecipientEmails)) {
                                                                    $allRecipientEmails[] = $fEmail;
                                                                }
                                                            @endphp
                                                            <div class="d-flex align-items-center gap-1 flex-wrap">
                                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1 font-monospace" style="font-size: 11px;">
                                                                    <i class="fa-solid fa-circle-xmark text-danger me-1"></i>{{ $fEmail }}
                                                                </span>
                                                                @if($fError)
                                                                    <button type="button" class="btn btn-link p-0 text-danger small text-decoration-none" 
                                                                            data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $fError }}" style="font-size: 11px;">
                                                                        <i class="fa-solid fa-circle-info"></i> <span style="font-size: 10px;">ত্রুটি দেখুন</span>
                                                                    </button>
                                                                @endif
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        </td>

                                        {{-- Sender Address --}}
                                        <td>
                                            <span class="text-secondary font-monospace small text-truncate d-block" style="font-size: 11.5px;" title="{{ $log['sender'] ?? (config('mail.from.address') ?: 'ad@ideaabd.com') }}">
                                                {{ $log['sender'] ?? (config('mail.from.address') ?: 'ad@ideaabd.com') }}
                                            </span>
                                        </td>

                                        {{-- Message Note --}}
                                        <td>
                                            @if(!empty($log['custom_message']))
                                                <div class="p-1.5 bg-light rounded border text-dark small" style="font-size: 11.5px; line-height: 1.3;" title="{{ $log['custom_message'] }}">
                                                    {{ Str::limit($log['custom_message'], 60) }}
                                                </div>
                                            @else
                                                <span class="text-muted small" style="font-size: 11px;">— ডিজিটাল ইনভয়েস লিংক —</span>
                                            @endif
                                        </td>

                                        {{-- Sent By --}}
                                        <td class="text-nowrap">
                                            <span class="small fw-semibold text-dark"><i class="fa-solid fa-user-tie text-secondary me-1"></i>{{ $log['sent_by'] ?? 'Admin' }}</span>
                                        </td>

                                        {{-- Status --}}
                                        <td class="text-center text-nowrap">
                                            @if($status === 'success')
                                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 fw-bold" style="font-size: 11px;">
                                                    <i class="fa-solid fa-circle-check me-1"></i> ডেলিভার্ড
                                                </span>
                                            @elseif($status === 'partial')
                                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2.5 py-1 fw-bold" style="font-size: 11px;">
                                                    <i class="fa-solid fa-triangle-exclamation me-1"></i> আংশিক
                                                </span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1 fw-bold" style="font-size: 11px;">
                                                    <i class="fa-solid fa-circle-xmark me-1"></i> ব্যর্থ
                                                </span>
                                            @endif
                                        </td>

                                                                                {{-- Action: Resend & Delete Buttons --}}
                                        <td class="text-center text-nowrap">
                                            <div class="d-inline-flex align-items-center gap-1">
                                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-2.5 py-0.5 shadow-2xs fw-semibold" 
                                                        style="font-size: 11px;" 
                                                        onclick="openResendModal('{{ implode(', ', $allRecipientEmails) }}', '{{ addslashes($log['custom_message'] ?? '') }}')" 
                                                        title="এই ঠিকানায় পুনরায় ইনভয়েস মেইল পাঠান">
                                                    <i class="fas fa-rotate-right me-0.5"></i> Resend
                                                </button>
                                                @if(!empty($log['id']))
                                                    <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2 py-0.5 shadow-2xs" 
                                                            style="font-size: 11px;" 
                                                            onclick="deleteEmailLogEntry('{{ $invoice->id }}', '{{ $log['id'] }}', this)" 
                                                            title="এই লগটি মুছে ফেলুন">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @elseif($invoice->emailed_at)
                    <div class="alert alert-success-subtle border border-success-subtle rounded-3 p-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <div class="d-flex align-items-center gap-3">
                            <i class="fa-solid fa-circle-check fs-3 text-success"></i>
                            <div>
                                <h6 class="fw-bold mb-0 text-success">ইমেইল সফলভাবে পাঠানো হয়েছিল</h6>
                                <small class="text-muted">
                                    সর্বশেষ মেইল পাঠানো হয়েছে: <strong>{{ $invoice->emailed_at->format('d M, Y h:i A') }}</strong> ({{ $invoice->emailed_at->diffForHumans() }}) — প্রাপক: <strong class="font-monospace text-dark">{{ $invoice->customer_email ?: 'গ্রাহকের ইমেইল' }}</strong>
                                </small>
                            </div>
                        </div>
                        <button type="button" class="btn btn-outline-success btn-sm rounded-pill px-3 fw-semibold" data-bs-toggle="modal" data-bs-target="#sendInvoiceEmailModal">
                            পুনরায় পাঠান
                        </button>
                    </div>
                @else
                    {{-- World-Class Empty State Banner --}}
                    <div class="text-center py-4 px-3 bg-light rounded-4 border border-dashed">
                        <div class="rounded-circle bg-white shadow-2xs d-inline-flex align-items-center justify-content-center p-3 mb-2">
                            <i class="fa-solid fa-paper-plane fs-2 text-success"></i>
                        </div>
                        <h6 class="fw-bold text-dark mb-1">এখনো কোনো ইমেইল প্রেরণ করা হয়নি</h6>
                        <p class="text-muted small mb-3 mx-auto" style="max-width: 500px;">
                            গ্রাহক বা প্রতিষ্ঠানের ঠিকানায় এক ক্লিকে ডিজিটাল বিল ও ডেলিভারি চালানের সরাসরি লিংক এবং পিডিএফ কপি পাঠাতে নিচের বাটনে ক্লিক করুন।
                        </p>
                        <button type="button" class="btn btn-success btn-sm rounded-pill px-4 py-2 fw-semibold shadow-xs" data-bs-toggle="modal" data-bs-target="#sendInvoiceEmailModal">
                            <i class="fas fa-paper-plane me-1.5"></i> এখনই গ্রাহককে ইমেইল পাঠান
                        </button>
                    </div>
                @endif
                </div>

                {{-- ======================================================== --}}
                {{-- TAB PANEL 2: CUSTOMER MESSAGE & GREETING CUSTOMIZATION    --}}
                {{-- ======================================================== --}}
                <div id="dispatchPanelMsg" style="display: none;">
                    @php
                        $autoFilledWaMsg = str_replace(
                            ['{customer_name}', '{business_name}', '{doc_type}', '{invoice_no}', '{invoice_url}'],
                            [$invoice->customer_name ?? '', $settings['business_name'] ?? 'আইডিয়া প্রকাশন', $docTypeBn, $invoice->invoice_no, $invoice->public_url],
                            !empty($settings['whatsapp_message_template']) 
                                ? $settings['whatsapp_message_template'] 
                                : "{business_name} থেকে আপনার {doc_type} (#{invoice_no}) প্রস্তুত করা হয়েছে। সরাসরি দেখতে ভিজিট করুন: {invoice_url}"
                        );

                        $autoFilledEmailIntro = str_replace(
                            ['{customer_name}', '{business_name}', '{doc_type}', '{invoice_no}', '{invoice_url}'],
                            [$invoice->customer_name ?? '', $settings['business_name'] ?? 'আইডিয়া প্রকাশন', $docTypeBn, $invoice->invoice_no, $invoice->public_url],
                            !empty($settings['email_intro_text']) 
                                ? $settings['email_intro_text'] 
                                : "{business_name} থেকে আপনার অর্ডারের {doc_type} প্রস্তুত করা হয়েছে।"
                        );

                        $autoFilledGreeting = !empty($settings['email_greeting_salutation']) 
                            ? $settings['email_greeting_salutation'] 
                            : 'সম্মানিত গ্রাহক';
                    @endphp

                    <form action="{{ route('admin.accounting.settings.update') }}" method="POST" id="customMessageSettingsForm" onsubmit="handleCustomMessageSubmit(event)" class="p-3.5 bg-light rounded-4 border">
                        @csrf
                        {{-- Preserved Settings Fields --}}
                        <input type="hidden" name="business_name" id="msgHiddenBizName" value="{{ $settings['business_name'] ?? 'Idea Publication' }}">
                        <input type="hidden" name="tagline" value="{{ $settings['tagline'] ?? '' }}">
                        <input type="hidden" name="address" value="{{ $settings['address'] ?? '' }}">
                        <input type="hidden" name="phone" value="{{ $settings['phone'] ?? '' }}">
                        <input type="hidden" name="email" value="{{ $settings['email'] ?? '' }}">
                        <input type="hidden" name="terms_and_conditions" value="{{ $settings['terms_and_conditions'] ?? '' }}">

                        {{-- Alert feedback container --}}
                        <div id="customMsgAlertContainer" class="mb-3" style="display: none;"></div>

                        <div class="row g-3.5 align-items-stretch">
                            {{-- LEFT COLUMN: Settings Form --}}
                            <div class="col-lg-7 d-flex flex-column justify-content-between">
                                <div class="bg-white p-3.5 rounded-3 border h-100 d-flex flex-column justify-content-between">
                                    <div>
                                        {{-- WhatsApp Template Section --}}
                                        <div class="mb-3">
                                            <div class="d-flex align-items-center justify-content-between mb-1.5 flex-wrap gap-1">
                                                <label class="form-label small fw-bold text-dark mb-0">
                                                    <i class="fab fa-whatsapp text-success me-1"></i>WhatsApp / Social Share বার্তা (অটোফিল):
                                                </label>
                                                {{-- Inline Auto-fill Value Insertion Chips --}}
                                                <div class="d-flex align-items-center gap-1 flex-wrap">
                                                    <button type="button" class="btn btn-xs btn-outline-primary py-0 px-1.5 rounded-pill" style="font-size: 10px;" onclick="insertAutoFillValue('whatsappMsgTemplateInput', 'customer_name')" title="গ্রাহকের নাম বসান">{{ $invoice->customer_name ?: '{customer_name}' }}</button>
                                                    <button type="button" class="btn btn-xs btn-outline-success py-0 px-1.5 rounded-pill" style="font-size: 10px;" onclick="insertAutoFillValue('whatsappMsgTemplateInput', 'business_name')" title="প্রতিষ্ঠানের নাম বসান">{{ $settings['business_name'] ?? 'Idea Publication' }}</button>
                                                    <button type="button" class="btn btn-xs btn-outline-info py-0 px-1.5 rounded-pill" style="font-size: 10px;" onclick="insertAutoFillValue('whatsappMsgTemplateInput', 'doc_type')" title="ডকুমেন্টের ধরন বসান">{{ $docTypeBn }}</button>
                                                    <button type="button" class="btn btn-xs btn-outline-warning text-dark py-0 px-1.5 rounded-pill" style="font-size: 10px;" onclick="insertAutoFillValue('whatsappMsgTemplateInput', 'invoice_no')" title="ইনভয়েস নম্বর বসান">#{{ $invoice->invoice_no }}</button>
                                                    <button type="button" class="btn btn-xs btn-outline-danger py-0 px-1.5 rounded-pill" style="font-size: 10px;" onclick="insertAutoFillValue('whatsappMsgTemplateInput', 'invoice_url')" title="অনলাইন লিংক বসান">🔗 Link</button>
                                                </div>
                                            </div>
                                            <textarea name="whatsapp_message_template" id="whatsappMsgTemplateInput" class="form-control rounded-2 font-sans" rows="3" 
                                                      oninput="handleMessageInput()"
                                                      placeholder="{{ $autoFilledWaMsg }}">{{ $autoFilledWaMsg }}</textarea>
                                        </div>

                                        {{-- Email Section: Greeting & Intro --}}
                                        <div class="row g-2.5">
                                            <div class="col-md-5">
                                                <label class="form-label small fw-bold text-dark mb-1">
                                                    <i class="fa-solid fa-envelope text-primary me-1"></i>ইমেইল সম্ভাষণ:
                                                </label>
                                                <input type="text" name="email_greeting_salutation" id="emailGreetingInput" class="form-control form-control-sm rounded-2" 
                                                       value="{{ $autoFilledGreeting }}" 
                                                       oninput="handleMessageInput()"
                                                       placeholder="সম্মানিত গ্রাহক">
                                            </div>
                                            <div class="col-md-7">
                                                <div class="d-flex align-items-center justify-content-between mb-1">
                                                    <label class="form-label small fw-bold text-dark mb-0">
                                                        <i class="fa-solid fa-file-lines text-info me-1"></i>ইমেইল ভূমিকা বার্তা:
                                                    </label>
                                                    <div class="d-flex gap-1">
                                                        <button type="button" class="btn btn-xs btn-outline-success py-0 px-1 rounded-pill" style="font-size: 9.5px;" onclick="insertAutoFillValue('emailIntroInput', 'business_name')">{{ $settings['business_name'] ?? 'Idea Publication' }}</button>
                                                        <button type="button" class="btn btn-xs btn-outline-info py-0 px-1 rounded-pill" style="font-size: 9.5px;" onclick="insertAutoFillValue('emailIntroInput', 'doc_type')">{{ $docTypeBn }}</button>
                                                    </div>
                                                </div>
                                                <input type="text" name="email_intro_text" id="emailIntroInput" class="form-control form-control-sm rounded-2" 
                                                       value="{{ $autoFilledEmailIntro }}" 
                                                       oninput="handleMessageInput()"
                                                       placeholder="{{ $autoFilledEmailIntro }}">
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Save and Reset Toolbar --}}
                                    <div class="d-flex align-items-center justify-content-between pt-3 mt-3 border-top">
                                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1" style="font-size: 12px;" onclick="resetToAutoFilledMessages()">
                                            <i class="fas fa-rotate-left me-1"></i>অটোফিল রিস্টোর
                                        </button>
                                        <button type="submit" class="btn btn-sm btn-success rounded-pill px-4 py-1.5 fw-bold shadow-xs" id="btnSaveCustomMsg">
                                            <i class="fas fa-save me-1.5" id="btnSaveCustomMsgIcon"></i>
                                            <span id="btnSaveCustomMsgText">বার্তা সংরক্ষণ করুন</span>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            {{-- RIGHT COLUMN: Live Previews --}}
                            <div class="col-lg-5 d-flex flex-column justify-content-between">
                                <div class="bg-white p-3.5 rounded-3 border h-100 d-flex flex-column justify-content-between gap-2.5">
                                    {{-- WhatsApp Preview --}}
                                    <div>
                                        <div class="d-flex align-items-center justify-content-between mb-1.5">
                                            <span class="small fw-bold text-success">
                                                <i class="fab fa-whatsapp me-1"></i>WhatsApp প্রিভিউ
                                            </span>
                                            <a href="#" id="previewWaActionBtn" target="_blank" class="btn btn-xs btn-outline-success rounded-pill px-2 py-0.5" style="font-size: 11px;">
                                                <i class="fab fa-whatsapp me-1"></i>টেস্ট লিংক
                                            </a>
                                        </div>
                                        <div class="p-2.5 rounded-3 border" style="background-color: #e7f7e4;">
                                            <div class="small text-dark font-sans" id="liveWaPreviewText" style="white-space: pre-wrap; font-size: 12px; line-height: 1.45;"></div>
                                            <div class="d-flex align-items-center justify-content-end gap-1 mt-1 text-muted" style="font-size: 9.5px;">
                                                <span>{{ date('h:i A') }}</span>
                                                <i class="fa-solid fa-check-double text-primary"></i>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Email Preview --}}
                                    <div class="border-top pt-2">
                                        <span class="small fw-bold text-primary d-block mb-1">
                                            <i class="fa-solid fa-envelope me-1"></i>ইমেইল বার্তা প্রিভিউ
                                        </span>
                                        <div class="p-2 rounded-2 bg-light border small text-dark" style="font-size: 11.5px; line-height: 1.4;">
                                            <div class="fw-bold text-dark" id="liveEmailGreetingText"></div>
                                            <div class="text-secondary mt-0.5" id="liveEmailIntroText"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Global Dynamic Variables (Auto-filled from settings and invoice)
const invoiceDynamicVars = {
    customer_name: @json($invoice->customer_name ?: 'সম্মানিত গ্রাহক'),
    business_name: @json($settings['business_name'] ?? 'আইডিয়া প্রকাশন'),
    doc_type: @json($docTypeBn),
    invoice_no: @json($invoice->invoice_no),
    invoice_url: @json($invoice->public_url),
    customer_phone: @json(preg_replace('/[^0-9]/', '', $invoice->customer_phone ?? ''))
};

const defaultAutoFilledWaMsg = @json($autoFilledWaMsg);
const defaultAutoFilledIntro = @json($autoFilledEmailIntro);
const defaultAutoFilledGreeting = @json($autoFilledGreeting);

// Replace any remaining placeholders in string with actual real values
function parseDynamicText(tpl) {
    if (!tpl) return "";
    let str = tpl;
    for (const [key, val] of Object.entries(invoiceDynamicVars)) {
        const regex = new RegExp("{" + key + "}", "g");
        str = str.replace(regex, val || "");
    }
    return str;
}

// Update Live Previews in Real-Time
function handleMessageInput() {
    const waInput = document.getElementById("whatsappMsgTemplateInput");
    const greetingInput = document.getElementById("emailGreetingInput");
    const emailIntroInput = document.getElementById("emailIntroInput");

    const waRaw = waInput ? waInput.value.trim() : "";
    const greetingRaw = greetingInput ? greetingInput.value.trim() : defaultAutoFilledGreeting;
    const emailIntroRaw = emailIntroInput ? emailIntroInput.value.trim() : "";

    // 1. Parse dynamic WhatsApp message
    const parsedWaMsg = parseDynamicText(waRaw || defaultAutoFilledWaMsg);
    const liveWaPreviewText = document.getElementById("liveWaPreviewText");
    if (liveWaPreviewText) {
        liveWaPreviewText.textContent = parsedWaMsg;
    }

    // 2. Update WhatsApp links (Top Header Button & Preview Test Button)
    const phone = invoiceDynamicVars.customer_phone || "";
    const waUrl = "https://wa.me/" + phone + "?text=" + encodeURIComponent(parsedWaMsg);
    
    const headerWaBtn = document.getElementById("headerWhatsAppShareBtn");
    if (headerWaBtn) {
        headerWaBtn.href = waUrl;
    }
    const previewWaBtn = document.getElementById("previewWaActionBtn");
    if (previewWaBtn) {
        previewWaBtn.href = waUrl;
    }

    // 3. Update Email Live Preview
    const parsedGreeting = parseDynamicText(greetingRaw || defaultAutoFilledGreeting);
    const liveGreetingEl = document.getElementById("liveEmailGreetingText");
    if (liveGreetingEl) {
        liveGreetingEl.textContent = parsedGreeting + (parsedGreeting.includes(invoiceDynamicVars.customer_name) ? "," : " " + (invoiceDynamicVars.customer_name || "") + ",");
    }

    const liveEmailIntroEl = document.getElementById("liveEmailIntroText");
    if (liveEmailIntroEl) {
        liveEmailIntroEl.textContent = emailIntroRaw ? parseDynamicText(emailIntroRaw) : defaultAutoFilledIntro;
    }
}

// Insert auto-filled value directly into target input at caret position
function insertAutoFillValue(targetId, key) {
    const el = document.getElementById(targetId);
    if (!el) return;

    const valueToInsert = invoiceDynamicVars[key] || "{" + key + "}";
    el.focus();
    if (document.selection) {
        const sel = document.selection.createRange();
        sel.text = valueToInsert;
    } else if (el.selectionStart || el.selectionStart === 0) {
        const startPos = el.selectionStart;
        const endPos = el.selectionEnd;
        el.value = el.value.substring(0, startPos) + valueToInsert + el.value.substring(endPos, el.value.length);
        el.selectionStart = startPos + valueToInsert.length;
        el.selectionEnd = startPos + valueToInsert.length;
    } else {
        el.value += valueToInsert;
    }
    handleMessageInput();
}

// Reset to Auto-filled Defaults
function resetToAutoFilledMessages() {
    const waInput = document.getElementById("whatsappMsgTemplateInput");
    const greetingInput = document.getElementById("emailGreetingInput");
    const emailIntroInput = document.getElementById("emailIntroInput");

    if (waInput) waInput.value = defaultAutoFilledWaMsg;
    if (greetingInput) greetingInput.value = defaultAutoFilledGreeting;
    if (emailIntroInput) emailIntroInput.value = defaultAutoFilledIntro;

    handleMessageInput();
}

// Submit Custom Message Form via AJAX
function handleCustomMessageSubmit(e) {
    e.preventDefault();
    const form = e.target;
    const btn = document.getElementById("btnSaveCustomMsg");
    const btnText = document.getElementById("btnSaveCustomMsgText");
    const btnIcon = document.getElementById("btnSaveCustomMsgIcon");
    const alertBox = document.getElementById("customMsgAlertContainer");

    if (btn) btn.disabled = true;
    if (btnIcon) {
        btnIcon.className = "fas fa-spinner fa-spin me-1.5";
    }
    if (btnText) btnText.textContent = "সংরক্ষণ হচ্ছে...";

    const formData = new FormData(form);

    fetch(form.action, {
        method: "POST",
        headers: {
            "X-Requested-With": "XMLHttpRequest",
            "Accept": "application/json"
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (alertBox) {
            alertBox.style.display = "block";
            if (data.success) {
                alertBox.className = "alert alert-success border-success-subtle rounded-3 py-2 px-3 d-flex align-items-center justify-content-between mb-3 shadow-2xs";
                alertBox.innerHTML = `<div><i class="fa-solid fa-circle-check text-success me-2"></i><strong>সফল:</strong> ${data.message || "বার্তা সফলভাবে সংরক্ষিত হয়েছে!"}</div><button type="button" class="btn-close btn-sm" onclick="this.parentElement.style.display='none'"></button>`;
                setTimeout(() => {
                    if (alertBox) alertBox.style.display = "none";
                }, 5000);
            } else {
                alertBox.className = "alert alert-danger border-danger-subtle rounded-3 py-2 px-3 d-flex align-items-center justify-content-between mb-3 shadow-2xs";
                alertBox.innerHTML = `<div><i class="fa-solid fa-circle-exmark text-danger me-2"></i><strong>ত্রুটি:</strong> ${data.message || "সংরক্ষণে সমস্যা হয়েছে।"}</div><button type="button" class="btn-close btn-sm" onclick="this.parentElement.style.display='none'"></button>`;
            }
        }
        handleMessageInput();
    })
    .catch(err => {
        if (alertBox) {
            alertBox.style.display = "block";
            alertBox.className = "alert alert-danger border-danger-subtle rounded-3 py-2 px-3 d-flex align-items-center justify-content-between mb-3 shadow-2xs";
            alertBox.innerHTML = `<div><i class="fa-solid fa-circle-exmark text-danger me-2"></i><strong>ত্রুটি:</strong> সংরক্ষণে সমস্যা হয়েছে (${err.message})</div><button type="button" class="btn-close btn-sm" onclick="this.parentElement.style.display='none'"></button>`;
        }
    })
    .finally(() => {
        if (btn) btn.disabled = false;
        if (btnIcon) {
            btnIcon.className = "fas fa-save me-1.5";
        }
        if (btnText) btnText.textContent = "বার্তা সংরক্ষণ করুন";
    });
}

// Initialize live preview on page load
document.addEventListener("DOMContentLoaded", function() {
    handleMessageInput();
});

// Tab Switcher Function
function switchDispatchTab(tab) {
    const logsBtn = document.getElementById("dispatchNavLogsBtn");
    const msgBtn = document.getElementById("dispatchNavMsgBtn");
    const logsPanel = document.getElementById("dispatchPanelLogs");
    const msgPanel = document.getElementById("dispatchPanelMsg");

    if (tab === "message") {
        if (msgBtn) {
            msgBtn.classList.remove("btn-light", "text-dark");
            msgBtn.classList.add("btn-primary", "text-white", "shadow-2xs");
        }
        if (logsBtn) {
            logsBtn.classList.remove("btn-primary", "text-white", "shadow-2xs");
            logsBtn.classList.add("btn-light", "text-dark");
        }
        if (logsPanel) logsPanel.style.display = "none";
        if (msgPanel) {
            msgPanel.style.display = "block";
            handleMessageInput();
        }
    } else {
        if (logsBtn) {
            logsBtn.classList.remove("btn-light", "text-dark");
            logsBtn.classList.add("btn-primary", "text-white", "shadow-2xs");
        }
        if (msgBtn) {
            msgBtn.classList.remove("btn-primary", "text-white", "shadow-2xs");
            msgBtn.classList.add("btn-light", "text-dark");
        }
        if (logsPanel) logsPanel.style.display = "block";
        if (msgPanel) msgPanel.style.display = "none";
    }
}

function filterEmailLogs(status, btn) {
    // Update active button styling
    document.querySelectorAll("#filterAllBtn, #filterDeliveredBtn, #filterFailedBtn").forEach(b => {
        b.classList.remove("bg-primary", "text-white", "fw-bold", "bg-success", "bg-danger");
        b.classList.add("bg-white");
    });
    if (status === "all") {
        btn.classList.add("bg-primary", "text-white", "fw-bold");
        btn.classList.remove("bg-white", "text-dark");
    } else if (status === "success") {
        btn.classList.add("bg-success", "text-white", "fw-bold");
        btn.classList.remove("bg-white", "text-success");
    } else if (status === "failed") {
        btn.classList.add("bg-danger", "text-white", "fw-bold");
        btn.classList.remove("bg-white", "text-danger");
    }

    const rows = document.querySelectorAll(".email-log-row");
    rows.forEach(row => {
        const rowStatus = row.getAttribute("data-status");
        if (status === "all" || rowStatus === status || (status === "success" && rowStatus === "partial")) {
            row.style.display = "";
        } else {
            row.style.display = "none";
        }
    });
}

function searchEmailLogs(query) {
    const q = query.toLowerCase().trim();
    const rows = document.querySelectorAll(".email-log-row");
    rows.forEach(row => {
        const searchContent = row.getAttribute("data-search") || "";
        if (!q || searchContent.includes(q)) {
            row.style.display = "";
        } else {
            row.style.display = "none";
        }
    });
}

function deleteEmailLogEntry(invoiceId, logId, btn) {
    if (!confirm('আপনি কি নিশ্চিত যে এই ইমেইল লগটি তালিকা থেকে মুছে ফেলতে চান?')) {
        return;
    }

    const row = btn.closest('tr');
    const originalContent = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

    fetch(`/admin/accounting/invoices/${invoiceId}/email-logs/${logId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            if (row) {
                row.style.transition = 'all 0.3s ease';
                row.style.opacity = '0';
                row.style.transform = 'translateX(20px)';
                setTimeout(() => {
                    row.remove();
                    const remainingRows = document.querySelectorAll('.email-log-row');
                    if (remainingRows.length === 0) {
                        window.location.reload();
                    }
                }, 300);
            }
        } else {
            alert('লগ মুছতে সমস্যা হয়েছে: ' + (data.message || 'অজানা ত্রুটি'));
            btn.disabled = false;
            btn.innerHTML = originalContent;
        }
    })
    .catch(err => {
        alert('ত্রুটি: ' + err.message);
        btn.disabled = false;
        btn.innerHTML = originalContent;
    });
}

function openResendModal(emails, customMsg) {
    const emailTextarea = document.getElementById("invoiceRecipientEmails");
    const msgTextarea = document.querySelector('#sendInvoiceEmailForm textarea[name="custom_message"]');
    if (emailTextarea) {
        emailTextarea.value = emails;
        if (typeof updateRecipientCount === "function") {
            updateRecipientCount(emailTextarea);
        }
    }
    if (msgTextarea && customMsg) {
        msgTextarea.value = customMsg;
    }
    const modalEl = document.getElementById("sendInvoiceEmailModal");
    if (modalEl) {
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    }
}
</script>
{{-- RECORD INVOICE PAYMENT MODAL --}}
@if(in_array($invoice->type, ['invoice', 'challan']))
<div class="modal fade d-print-none" id="recordInvoicePaymentModal" tabindex="-1" aria-labelledby="recordInvoicePaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form action="{{ route('admin.accounting.invoices.payments.store', $invoice->id) }}" method="POST">
                @csrf
                <div class="modal-header bg-success text-white py-3">
                    <h5 class="modal-title fw-bold" id="recordInvoicePaymentModalLabel">
                        <i class="fas fa-hand-holding-dollar me-2"></i>কিস্তি / বকেয়া টাকা জমা গ্রহণ
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="bg-light p-3 rounded-3 border mb-3">
                        <div class="d-flex justify-content-between small text-muted mb-1">
                            <span>ইনভয়েস নম্বর: <strong class="text-dark font-monospace">#{{ $invoice->invoice_no }}</strong></span>
                            <span>মোট বিল: <strong class="text-dark font-monospace">৳{{ number_format($invoice->grand_total, 2) }}</strong></span>
                        </div>
                        <div class="d-flex justify-content-between small text-muted">
                            <span>গ্রাহক: <strong class="text-dark">{{ $invoice->customer_name }}</strong></span>
                            <span>বর্তমান বকেয়া: <strong class="text-danger fw-bold font-monospace fs-6">৳{{ number_format($invoice->due_amount, 2) }}</strong></span>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold text-dark">জমার তারিখ: <span class="text-danger">*</span></label>
                            <input type="date" name="payment_date" class="form-control" required value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-dark">জমার পরিমাণ (টাকা): <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">৳</span>
                                <input type="number" step="0.01" min="0.01" name="amount" class="form-control fw-bold font-monospace text-success" required placeholder="0.00" value="{{ $invoice->due_amount > 0 ? $invoice->due_amount : '' }}">
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold text-dark">পেমেন্ট মাধ্যম: <span class="text-danger">*</span></label>
                            <select name="payment_method" class="form-select" required>
                                @foreach(\App\Models\IdeaInvoicePayment::paymentMethods() as $code => $lbl)
                                    <option value="{{ $code }}" {{ $code === 'cash' ? 'selected' : '' }}>{{ $lbl }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-dark">Trx / ভাউচার / চেক নং:</label>
                            <input type="text" name="transaction_ref" class="form-control font-monospace" placeholder="রেফারেন্স নম্বর">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">পরবর্তী কিস্তি / পরিশোধের শেষ তারিখ (ঐচ্ছিক):</label>
                        <input type="date" name="due_date" class="form-control" value="{{ $invoice->due_date ? $invoice->due_date->format('Y-m-d') : '' }}">
                        <div class="form-text text-muted" style="font-size: 11px;">যদি বকেয়া থাকে এবং পরবর্তী কিস্তির তারিখ নির্ধারণ করতে চান, তবে দিন। অন্যথায় খালি রাখুন।</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">বিবরণ / নোট (ঐচ্ছিক):</label>
                        <input type="text" name="note" class="form-control" placeholder="যেমন: ২য় কিস্তি পরিশোধ / বিকাশ ক্যাশ ইন">
                    </div>
                </div>
                <div class="modal-footer bg-light p-3">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">বাতিল</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold">
                        <i class="fas fa-check me-1.5"></i> জমা গ্রহণ কনফার্ম করুন
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- Send Invoice Email to Customer Modal (Multiple Recipients Support) --}}
<div class="modal fade d-print-none" id="sendInvoiceEmailModal" tabindex="-1" aria-labelledby="sendInvoiceEmailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form action="{{ route('admin.accounting.invoices.send-email', $invoice->id) }}" method="POST" id="sendInvoiceEmailForm">
                @csrf
                <div class="modal-header bg-success text-white py-3 px-4">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-paper-plane fs-5"></i>
                        <div>
                            <h5 class="modal-title fw-bold mb-0" id="sendInvoiceEmailModalLabel">
                                Send Invoice Link to Customer
                            </h5>
                            <small class="text-white-50" style="font-size: 11.5px;">ডিজিটাল ইনভয়েস ও ডেলিভারি চালানের লাইভ লিংক ইমেইলে প্রেরণ</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-info py-2 px-3 small d-flex align-items-center mb-3 rounded-3 border-info-subtle">
                        <i class="fas fa-circle-info me-2 fs-5 text-info"></i>
                        <div style="font-size: 12.5px;">
                            গ্রাহক বা প্রতিষ্ঠানের ঠিকানায় সরাসরি ডিজিটাল ইনভয়েস দেখা এবং পিডিএফ (PDF) ডাউনলোড করার লিংক স্বয়ংক্রিয়ভাবে প্রেরিত হবে।
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-muted small mb-1">Customer / Organization</label>
                            <input type="text" class="form-control bg-light fw-bold" value="{{ $invoice->customer_name ?? '—' }} {{ $invoice->customer_org ? '(' . $invoice->customer_org . ')' : '' }}" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-muted small mb-1">Sender Email (প্রেরক)</label>
                            <input type="text" class="form-control bg-light font-monospace" value="{{ config('mail.from.address') ?: 'ad@ideaabd.com' }} ({{ config('mail.from.name') ?: 'Idea Prokashon' }})" readonly>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <label class="form-label fw-semibold mb-0">
                                Recipient Email Address(es) <span class="text-danger">*</span>
                            </label>
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-0.5" id="recipientCountBadge" style="font-size: 11px;">
                                1 Recipient
                            </span>
                        </div>
                        <textarea name="email" id="invoiceRecipientEmails" class="form-control font-monospace" rows="2" 
                                  placeholder="client@example.com, manager@domain.com, accounts@company.com" 
                                  required oninput="updateRecipientCount(this)">{{ $invoice->customer_email }}</textarea>
                        <div class="d-flex align-items-center justify-content-between mt-1">
                            <div class="text-muted" style="font-size: 11.5px;">
                                <i class="fa-solid fa-circle-nodes text-primary me-1"></i>একাধিক ইমেইল দিতে কমা (<code>,</code>), সেমিকোলন (<code>;</code>) বা স্পেস দিয়ে লিখুন।
                            </div>
                        </div>
                        <div id="emailPillsContainer" class="d-flex flex-wrap gap-1 mt-2"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold mb-1">Custom Message / Note (Optional)</label>
                        <textarea name="custom_message" class="form-control rounded-3" rows="3" placeholder="e.g. আপনার ক্রয়কৃত বইসমূহ কুরিয়ারে বুকিং দেওয়া হয়েছে। ট্র্যাকিং নং..."></textarea>
                    </div>

                    @if(!empty($invoice->email_logs))
                        <div class="mt-3 p-3 bg-light rounded-3 border">
                            <span class="small fw-bold text-dark d-block mb-1">
                                <i class="fas fa-history me-1 text-success"></i> পূর্ববর্তী প্রেরণের ইতিহাস (Dispatch History):
                            </span>
                            <div class="d-flex flex-column gap-1" style="max-height: 120px; overflow-y: auto;">
                                @foreach(array_slice($invoice->email_logs, 0, 3) as $hLog)
                                    <div class="small text-muted d-flex justify-content-between align-items-center border-bottom pb-1">
                                        <span>
                                            <i class="fa-solid fa-check text-success me-1"></i>
                                            {{ \Carbon\Carbon::parse($hLog['sent_at'])->format('d M, Y h:i A') }} — 
                                            <strong class="text-dark font-monospace">{{ implode(', ', $hLog['recipients'] ?? []) }}</strong>
                                        </span>
                                        <span class="badge bg-success-subtle text-success">Sent</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
                <div class="modal-footer border-top py-2.5 bg-light">
                    <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success rounded-pill fw-semibold px-4 shadow-sm" id="btnSendInvoiceEmail">
                        <i class="fas fa-paper-plane me-1.5"></i> Send Email Now
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function updateRecipientCount(textarea) {
        if (!textarea) return;
        const val = textarea.value.trim();
        const badge = document.getElementById('recipientCountBadge');
        const container = document.getElementById('emailPillsContainer');
        
        if (!val) {
            if (badge) badge.textContent = '0 Recipients';
            if (container) container.innerHTML = '';
            return;
        }

        const emails = val.split(/[\s,;]+/).filter(e => e.trim().length > 0);
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        let validCount = 0;
        let pillsHtml = '';

        emails.forEach(em => {
            const isValid = emailRegex.test(em);
            if (isValid) validCount++;
            pillsHtml += `<span class="badge ${isValid ? 'bg-primary-subtle text-primary border-primary-subtle' : 'bg-danger-subtle text-danger border-danger-subtle'} border rounded-pill px-2 py-0.5" style="font-size: 10.5px;">
                <i class="fa-solid ${isValid ? 'fa-envelope' : 'fa-triangle-exclamation'} me-1"></i>${em}
            </span>`;
        });

        if (badge) {
            badge.textContent = `${validCount} ${validCount === 1 ? 'Recipient' : 'Recipients'}`;
            badge.className = `badge ${validCount > 0 ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-secondary-subtle text-secondary'} rounded-pill px-2 py-0.5`;
        }
        if (container) {
            container.innerHTML = pillsHtml;
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const emailTextarea = document.getElementById('invoiceRecipientEmails');
        if (emailTextarea && emailTextarea.value) {
            updateRecipientCount(emailTextarea);
        }
    });
</script>

{{-- Invoice & Memo Header Settings / Design Modal with 2:1 Cropper --}}
<div class="modal fade d-print-none" id="invoiceSettingsModal" tabindex="-1" aria-labelledby="invoiceSettingsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow">
            <form action="{{ route('admin.accounting.settings.update') }}" method="POST" enctype="multipart/form-data" id="settingsForm">
                @csrf
                <input type="hidden" name="logo_base64" id="logoCroppedBase64">

                <div class="modal-header border-bottom py-3">
                    <h5 class="modal-title fw-bold text-primary" id="invoiceSettingsModalLabel">
                        <i class="fas fa-palette me-2"></i>Invoice Design & Memo Branding Settings
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    
                    {{-- Live Preview Header Card --}}
                    <div class="card border rounded-3 p-3 mb-4 bg-light">
                        <span class="small fw-bold text-muted text-uppercase mb-2 d-block"><i class="fas fa-eye me-1 text-primary"></i>Invoice Header Live Preview:</span>
                        <div class="d-flex align-items-center gap-3.5 p-2 bg-white rounded border">
                            <img src="{{ $logoSrc }}" id="previewHeaderLogo" alt="Logo Preview" style="height: 48px; width: 96px; aspect-ratio: 2/1; object-fit: contain; flex-shrink: 0; margin-right: 6px;">
                            <div class="d-flex flex-column justify-content-center" style="line-height: 1.35; padding-left: 2px;">
                                <div class="fw-bold text-primary mb-0" id="previewHeaderTitle" style="font-size: 15.5px;">{{ $settings['business_name'] ?? 'Idea Publication' }}</div>
                                <div class="text-muted small mb-0" id="previewHeaderTagline" style="font-size: 10.5px;">{{ $settings['tagline'] ?? 'Book Publication, Printing & Distribution' }}</div>
                                <div class="text-muted small mt-0.5" id="previewHeaderMeta" style="font-size: 10px;">
                                    <span><i class="fas fa-location-dot me-0.5 text-danger"></i><span id="previewMetaAddr">{{ $settings['address'] ?? 'Dhaka, Bangladesh' }}</span></span>
                                    <span class="mx-1 text-muted">·</span>
                                    <span><i class="fas fa-phone me-0.5 text-primary"></i><span id="previewMetaPhone">{{ $settings['phone'] ?? '018XXXXXXXX' }}</span></span>
                                    <span class="mx-1 text-muted">·</span>
                                    <span><i class="fas fa-envelope me-0.5 text-primary"></i><span id="previewMetaEmail">{{ $settings['email'] ?? 'info@ideaabd.com' }}</span></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 2:1 Aspect Ratio Logo Cropper Tool --}}
                    <div class="card border border-primary-subtle rounded-3 p-3 mb-4 bg-primary-subtle bg-opacity-10">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label fw-bold text-primary mb-0">
                                <i class="fas fa-crop-simple me-1"></i> Logo Upload & 2:1 Wide Crop Tool
                            </label>
                            <span class="badge bg-primary text-white">Ratio 2:1 (Double Width)</span>
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
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mt-1">
                                        <small class="text-muted"><i class="fas fa-hand-pointer me-1"></i>Drag to reposition, slider to zoom</small>
                                        <button type="button" class="btn btn-sm btn-link text-decoration-none p-0" onclick="resetCrop()">Reset</button>
                                    </div>
                                </div>
                                <div class="col-md-5 text-center">
                                    <span class="small fw-semibold text-muted d-block mb-2">Crop Preview (2:1 Ratio):</span>
                                    <div class="p-2 border rounded-3 bg-white d-inline-block shadow-xs mb-2">
                                        <img id="cropperPreviewThumb" src="{{ $logoSrc }}" alt="Live Crop Thumb" style="height: 50px; width: 100px; object-fit: contain;">
                                    </div>
                                    <div class="small text-success fw-semibold"><i class="fas fa-circle-check me-1"></i>Logo ready to save</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Challan Destination & Recipient Typography Controls --}}
                    <div class="card border border-primary-subtle rounded-3 p-3 mb-3 bg-primary bg-opacity-10">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label fw-bold text-primary mb-0">
                                <i class="fas fa-truck-ramp-box me-1"></i> Delivery Destination & Recipient ফন্ট সাইজ নিয়ন্ত্রণ
                            </label>
                            <span class="badge bg-primary text-white">Challan Typography</span>
                        </div>
                        <p class="small text-muted mb-3" style="font-size: 11px;">
                            চালানের <strong>Delivery Destination & Recipient:</strong> সেকশনে প্রাপকের নাম, মোবাইল নম্বর, ঠিকানা ও পদবির ফন্ট সাইজ বড় বা ছোট করুন।
                        </p>

                        {{-- Recipient Live Preview Box --}}
                        <div class="p-2.5 bg-white rounded-2 border mb-3 shadow-xs">
                            <div class="small fw-bold text-muted text-uppercase mb-1" style="font-size: 10px;">
                                <i class="fas fa-eye me-1 text-primary"></i>Recipient Typography Live Preview:
                            </div>
                            <div class="p-2 bg-light rounded border" id="previewRecipientBox">
                                <div class="fw-bold text-dark mb-1" style="font-size: 11px;"><i class="fas fa-truck me-1 text-primary"></i>Delivery Destination & Recipient:</div>
                                <div id="previewRecipientName" style="font-size: {{ $recipientNameSize }}; font-weight: bold; color: #0f172a;">মোহাম্মদ আবদুল্লাহ / Rahim Book House</div>
                                <div id="previewRecipientDesig" class="text-muted" style="font-size: {{ $recipientDesigSize }};">প্রধান শিক্ষক / সত্ত্বাধিকারী</div>
                                <div id="previewRecipientOrg" class="text-primary fw-semibold" style="font-size: {{ $recipientOrgSize }};">আইডিয়া একাডেমি ও লাইব্রেরি</div>
                                <div id="previewRecipientAddr" class="text-dark" style="font-size: {{ $recipientAddressSize }};">৩৮ বাংলাবাজার, ঢাকা-১১০০, বাংলাদেশ</div>
                                <div id="previewRecipientPhone" class="text-dark fw-bold font-monospace" style="font-size: {{ $recipientPhoneSize }};">01812-345678, 01712-345678</div>
                            </div>
                        </div>

                        <div class="row g-2.5">
                            <div class="col-md-4 col-sm-6">
                                <label class="form-label small fw-semibold text-dark mb-1">
                                    প্রাপকের নাম সাইজ (Name)
                                </label>
                                <select name="challan_recipient_name_size" id="inputNameSize" class="form-select form-select-sm" onchange="updateRecipientPreview()">
                                    @foreach(['11px'=>'ছোট (11px)', '12px'=>'স্বাভাবিক (12px)', '13px'=>'মাঝারি (13px)', '14px'=>'বড় (14px)', '15px'=>'অনেক বড় (15px)', '16px'=>'অতিরিক্ত বড় (16px)', '18px'=>'বিশাল (18px)'] as $val => $lbl)
                                        <option value="{{ $val }}" {{ ($recipientNameSize === $val) ? 'selected' : '' }}>{{ $lbl }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4 col-sm-6">
                                <label class="form-label small fw-semibold text-dark mb-1">
                                    মোবাইল নম্বর সাইজ (Mobile)
                                </label>
                                <select name="challan_recipient_phone_size" id="inputPhoneSize" class="form-select form-select-sm" onchange="updateRecipientPreview()">
                                    @foreach(['10.5px'=>'ছোট (10.5px)', '11.5px'=>'স্বাভাবিক (11.5px)', '12px'=>'মাঝারি (12px)', '13px'=>'বড় (13px)', '14px'=>'অনেক বড় (14px)', '15px'=>'অতিরিক্ত বড় (15px)'] as $val => $lbl)
                                        <option value="{{ $val }}" {{ ($recipientPhoneSize === $val) ? 'selected' : '' }}>{{ $lbl }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4 col-sm-6">
                                <label class="form-label small fw-semibold text-dark mb-1">
                                    ঠিকানা সাইজ (Address)
                                </label>
                                <select name="challan_recipient_address_size" id="inputAddressSize" class="form-select form-select-sm" onchange="updateRecipientPreview()">
                                    @foreach(['10px'=>'ছোট (10px)', '11px'=>'স্বাভাবিক (11px)', '11.5px'=>'মাঝারি (11.5px)', '12px'=>'বড় (12px)', '13px'=>'অনেক বড় (13px)', '14px'=>'অতিরিক্ত বড় (14px)'] as $val => $lbl)
                                        <option value="{{ $val }}" {{ ($recipientAddressSize === $val) ? 'selected' : '' }}>{{ $lbl }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 col-sm-6">
                                <label class="form-label small fw-semibold text-dark mb-1">
                                    পদবি ও প্রতিষ্ঠান সাইজ (Designation/Org)
                                </label>
                                <select name="challan_recipient_desig_size" id="inputDesigSize" class="form-select form-select-sm" onchange="updateRecipientPreview()">
                                    @foreach(['10px'=>'ছোট (10px)', '11px'=>'স্বাভাবিক (11px)', '11.5px'=>'মাঝারি (11.5px)', '12px'=>'বড় (12px)', '13px'=>'অনেক বড় (13px)'] as $val => $lbl)
                                        <option value="{{ $val }}" {{ ($recipientDesigSize === $val) ? 'selected' : '' }}>{{ $lbl }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 col-sm-12">
                                <label class="form-label small fw-semibold text-dark mb-1">
                                    স্বাক্ষরকারীর ডিফল্ট পদবি (Signatory Title)
                                </label>
                                <input type="text" name="default_creator_designation" id="inputDefaultCreatorDesig" class="form-control form-control-sm" 
                                       value="{{ $settings['default_creator_designation'] ?? '' }}" placeholder="যেমন: Authorized Signatory / Billing Officer">
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Company / Imprint Name (Header Title)</label>
                            <input type="text" name="business_name" id="inputBusinessName" class="form-control" value="{{ $settings['business_name'] ?? 'Idea Publication' }}" required oninput="updateLivePreview()">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tagline / Slogan</label>
                            <input type="text" name="tagline" id="inputTagline" class="form-control" value="{{ $settings['tagline'] ?? 'Book Publication, Printing & Distribution' }}" placeholder="Book Publication, Printing..." oninput="updateLivePreview()">
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Full Official Address</label>
                            <input type="text" name="address" id="inputAddress" class="form-control" value="{{ $settings['address'] ?? 'Dhaka, Bangladesh' }}" placeholder="e.g. 38 Banglabazar, Dhaka..." oninput="updateLivePreview()">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Official Phone Number</label>
                            <input type="text" name="phone" id="inputPhone" class="form-control" value="{{ $settings['phone'] ?? '018XXXXXXXX' }}" placeholder="017XXXXXXXX, 018XXXXXXXX" oninput="updateLivePreview()">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Official Email Address</label>
                            <input type="email" name="email" id="inputEmail" class="form-control" value="{{ $settings['email'] ?? 'info@ideaabd.com' }}" placeholder="info@ideaabd.com" oninput="updateLivePreview()">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top py-2.5">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-semibold px-4 shadow-sm">
                        <i class="fas fa-save me-1"></i> Save Design & Settings
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

function updateLivePreview() {
    const name = document.getElementById('inputBusinessName')?.value || 'Idea Publication';
    const tag = document.getElementById('inputTagline')?.value || '';
    const addr = document.getElementById('inputAddress')?.value || '';
    const ph = document.getElementById('inputPhone')?.value || '';
    const em = document.getElementById('inputEmail')?.value || '';

    const titleEl = document.getElementById('previewHeaderTitle');
    const tagEl = document.getElementById('previewHeaderTagline');
    const addrEl = document.getElementById('previewMetaAddr');
    const phoneEl = document.getElementById('previewMetaPhone');
    const emailEl = document.getElementById('previewMetaEmail');

    if (titleEl) titleEl.textContent = name;
    if (tagEl) tagEl.textContent = tag;
    if (addrEl) addrEl.textContent = addr;
    if (phoneEl) phoneEl.textContent = ph;
    if (emailEl) emailEl.textContent = em;
}

function updateRecipientPreview() {
    const nameSize = document.getElementById('inputNameSize')?.value || '13px';
    const phoneSize = document.getElementById('inputPhoneSize')?.value || '12px';
    const addrSize = document.getElementById('inputAddressSize')?.value || '11.5px';
    const desigSize = document.getElementById('inputDesigSize')?.value || '11.5px';

    const pName = document.getElementById('previewRecipientName');
    const pPhone = document.getElementById('previewRecipientPhone');
    const pAddr = document.getElementById('previewRecipientAddr');
    const pDesig = document.getElementById('previewRecipientDesig');
    const pOrg = document.getElementById('previewRecipientOrg');

    if (pName) pName.style.fontSize = nameSize;
    if (pPhone) pPhone.style.fontSize = phoneSize;
    if (pAddr) pAddr.style.fontSize = addrSize;
    if (pDesig) pDesig.style.fontSize = desigSize;
    if (pOrg) pOrg.style.fontSize = desigSize;

    // Also update on-page challan target elements if present
    const cName = document.getElementById('challanRecipientName');
    const cPhone = document.getElementById('challanRecipientPhone');
    const cAddr = document.getElementById('challanRecipientAddr');
    const cDesig = document.getElementById('challanRecipientDesig');
    const cOrg = document.getElementById('challanRecipientOrg');

    if (cName) cName.style.fontSize = nameSize;
    if (cPhone) cPhone.style.fontSize = phoneSize;
    if (cAddr) cAddr.style.fontSize = addrSize;
    if (cDesig) cDesig.style.fontSize = desigSize;
    if (cOrg) cOrg.style.fontSize = desigSize;
}

function copyCustomerShareLink() {
    const url = "{{ $invoice->public_url }}";
    navigator.clipboard.writeText(url).then(function() {
        const btn = document.getElementById('btnAdminCopyLink');
        if (btn) {
            const original = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-check me-1 text-success"></i>Link Copied!';
            setTimeout(() => { btn.innerHTML = original; }, 2500);
        }
    }).catch(function() {
        prompt('Customer Public Link:', url);
    });
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
const resultThumb = document.getElementById('cropperPreviewThumb');
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

    /* Grid Flex Maintenance in Print - no negative margins to avoid right line artifacts */
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
