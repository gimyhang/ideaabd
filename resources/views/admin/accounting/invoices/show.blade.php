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

    $invoiceUrl = $invoice->public_url;
    $qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=160x160&margin=4&data=" . urlencode($invoiceUrl);
    $mfsQrSrc = !empty($settings['mfs_qr_image']) 
        ? \App\Support\SiteSetting::resolveImageUrl($settings['mfs_qr_image']) 
        : (!empty($settings['payment_qr_image']) ? \App\Support\SiteSetting::resolveImageUrl($settings['payment_qr_image']) : $qrCodeUrl);
    $bankQrSrc = !empty($settings['bank_qr_image']) 
        ? \App\Support\SiteSetting::resolveImageUrl($settings['bank_qr_image']) 
        : (!empty($settings['payment_qr_image']) ? \App\Support\SiteSetting::resolveImageUrl($settings['payment_qr_image']) : $qrCodeUrl);

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
    $qrCodeSize = $settings['qr_code_size'] ?? '60px';

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
                <i class="fa-solid fa-hand-holding-dollar me-1.5"></i> কিস্তি জমা নিন
            </button>
        @endif

        {{-- Customer Ledger Link --}}
        <a href="{{ route('admin.accounting.customer-ledger.index', ['customer_name' => $invoice->customer_name, 'customer_phone' => $invoice->customer_phone]) }}" class="btn btn-outline-info text-dark btn-sm rounded-pill px-3 fw-semibold shadow-sm" title="গ্রাহকের সম্পূর্ণ খতিয়ান দেখুন">
            <i class="fa-solid fa-book-bookmark me-1 text-primary"></i> গ্রাহক খতিয়ান
        </a>

        <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm fw-semibold" onclick="window.print()">
            <i class="fa-solid fa-print me-1.5"></i> Print / PDF
        </button>

        {{-- Send Invoice Link to Customer Email Button --}}
        <button type="button" class="btn btn-outline-success btn-sm rounded-pill px-3 fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#sendInvoiceEmailModal" title="Send digital invoice link to customer email">
            <i class="fa-solid fa-paper-plane me-1.5"></i> Send Email
            @if($invoice->emailed_at)
                <span class="badge bg-success text-white ms-1 px-1.5 py-0.5 rounded-pill" title="Email sent">✓</span>
            @endif
        </button>

        {{-- Copy Customer Public Link --}}
        <button type="button" class="btn btn-outline-secondary text-dark btn-sm rounded-pill px-3 fw-semibold shadow-sm" onclick="copyCustomerShareLink()" id="btnAdminCopyLink" title="Copy public share link for customer">
            <i class="fa-solid fa-share-nodes me-1 text-primary"></i> Copy Link
        </button>

        <a href="{{ $invoice->public_url }}" target="_blank" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-sm" title="Preview public view">
            <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Customer View
        </a>

        {{-- Edit Document Button --}}
        <a href="{{ route('admin.accounting.invoices.edit', $invoice->id) }}" class="btn btn-warning text-dark btn-sm rounded-pill px-3 fw-semibold shadow-sm">
            <i class="fa-solid fa-pen-to-square me-1"></i> Edit Document
        </a>

        {{-- Customize Memo Header Settings Button --}}
        <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#invoiceSettingsModal" title="Customize invoice branding header">
            <i class="fa-solid fa-palette me-1 text-primary"></i> Memo Settings
        </button>

        {{-- Convert to Invoice/Challan if currently Quotation or Tender --}}
        @if(in_array($invoice->type, ['quotation', 'tender']))
            <form action="{{ route('admin.accounting.invoices.convert', $invoice->id) }}" method="POST" class="d-inline"
                  data-confirm="আপনি কি এই কোটেশন/টেন্ডারটিকে চূড়ান্ত ইনভয়েস/বিল এ রূপান্তর করতে চান?" data-confirm-title="বিল রূপান্তর" data-confirm-icon="question">
                @csrf
                <input type="hidden" name="target_type" value="invoice">
                <button type="submit" class="btn btn-success btn-sm rounded-pill px-3 fw-semibold shadow-sm">
                    <i class="fa-solid fa-receipt me-1"></i> Convert to Bill
                </button>
            </form>

            <form action="{{ route('admin.accounting.invoices.convert', $invoice->id) }}" method="POST" class="d-inline"
                  data-confirm="আপনি কি এই কোটেশন/টেন্ডারটিকে ডেলিভারি চালানে রূপান্তর করতে চান?" data-confirm-title="চালান রূপান্তর" data-confirm-icon="question">
                @csrf
                <input type="hidden" name="target_type" value="challan">
                <button type="submit" class="btn btn-info text-white btn-sm rounded-pill px-3 fw-semibold shadow-sm">
                    <i class="fa-solid fa-truck me-1"></i> Convert to Challan
                </button>
            </form>
        @endif

        <a href="{{ route('admin.accounting.invoices.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-xs">
            <i class="fa-solid fa-arrow-left me-1"></i> Back to List
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
                <i class="fa-solid fa-scale-balanced me-1.5"></i> Income & Expense Ledger
            </a>
            <a href="{{ route('admin.accounting.invoices.index') }}" 
               class="nav-link rounded-pill px-3.5 py-2 fw-semibold text-dark hover-bg-light">
                <i class="fa-solid fa-file-invoice-dollar me-1.5"></i> Invoices, Challans & Quotations
            </a>
            <a href="{{ route('admin.accounting.customer-ledger.index') }}" 
               class="nav-link rounded-pill px-3.5 py-2 fw-semibold text-dark hover-bg-light">
                <i class="fa-solid fa-book-bookmark me-1.5"></i> Customer Ledgers & Statements
            </a>
            <a href="{{ route('admin.accounting.invoices.create') }}" 
               class="nav-link rounded-pill px-3.5 py-2 fw-semibold text-dark hover-bg-light">
                <i class="fa-solid fa-file-circle-plus me-1.5"></i> Create New Invoice
            </a>
        </div>

        @if($invoice->type === 'invoice')
            <div class="btn-group btn-group-sm">
                <button type="button" class="btn btn-outline-primary active" id="btnShowBoth" onclick="setViewMode('both')">
                    <i class="fa-solid fa-file-lines me-1"></i>Both Pages (Bill & Challan)
                </button>
                <button type="button" class="btn btn-outline-primary" id="btnShowBill" onclick="setViewMode('bill')">
                    <i class="fa-solid fa-receipt me-1"></i>Page 1 (Bill)
                </button>
                <button type="button" class="btn btn-outline-primary" id="btnShowChallan" onclick="setViewMode('challan')">
                    <i class="fa-solid fa-truck me-1"></i>Page 2 (Challan)
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
            
            {{-- Institutional / Company Header in 2-Column Responsive Layout --}}
            <div class="row align-items-center border-bottom pb-2 mb-2 g-2">
                <div class="col-12 col-md-7 col-print-7">
                    <div class="d-flex align-items-center gap-2.5 invoice-brand-header">
                        <img src="{{ $logoSrc }}" alt="{{ $settings['business_name'] ?? 'Idea Publication' }}" 
                             class="img-fluid invoice-logo-img" style="height: 48px; width: 96px; aspect-ratio: 2/1; object-fit: contain; flex-shrink: 0; margin-right: 4px;">
                        <div class="d-flex flex-column justify-content-center" style="line-height: 1.3; padding-left: 2px;">
                            <div class="fw-bold text-primary invoice-brand-name" style="font-size: 15.5px; margin-bottom: 2px;">{{ $settings['business_name'] ?? 'Idea Publication' }}</div>
                            <div class="text-muted invoice-tagline" style="font-size: 10px; margin-bottom: 2px;">{{ $settings['tagline'] ?? 'Book Publication, Printing & Distribution' }}</div>
                            <div class="text-muted invoice-contact-info" style="font-size: 9.5px; line-height: 1.35;">
                                <span><i class="fa-solid fa-location-dot me-0.5 text-danger"></i>{{ $settings['address'] ?? 'Dhaka, Bangladesh' }}</span>
                                <span class="mx-1 text-muted">·</span>
                                <span><i class="fa-solid fa-phone me-0.5 text-primary"></i>{{ $settings['phone'] ?? '018XXXXXXXX' }}</span>
                                <span class="mx-1 text-muted">·</span>
                                <span><i class="fa-solid fa-envelope me-0.5 text-primary"></i>{{ $settings['email'] ?? 'info@ideaabd.com' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-5 col-print-5 text-md-end text-start mt-2 mt-md-0">
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
                        <i class="fa-solid fa-desktop me-1"></i>{{ $computerGeneratedLabels[$invoice->type] ?? 'Computer-generated bill' }}
                        · Date: <strong>{{ $invoice->invoice_date ? $invoice->invoice_date->format('d M, Y') : '—' }}</strong>
                    </div>
                    @if($invoice->valid_until)
                        <div class="text-danger fw-semibold" style="font-size: 9px;"><i class="fa-solid fa-hourglass-half me-0.5"></i>Valid until: {{ $invoice->valid_until->format('d M, Y') }}</div>
                    @endif
                </div>
            </div>

            {{-- Subject and Reference (for Bill, Challan, Tender & Quotation) --}}
            @if($invoice->subject || $invoice->reference_no)
                <div class="p-2 bg-light rounded-2 border mb-2.5 subject-reference-box destination-box" style="font-size: 11px;">
                    <table class="table-borderless p-0 m-0 w-100 colon-table" style="line-height: 1.45;">
                        @if($invoice->reference_no)
                            <tr>
                                <td class="colon-label text-dark fw-bold" style="width: 105px;">
                                    @if($invoice->type === 'tender') Tender Ref
                                    @elseif($invoice->type === 'quotation') Quotation Ref
                                    @elseif($invoice->type === 'challan') Challan Ref
                                    @else Ref / PO No @endif
                                </td>
                                <td class="colon-sep">:</td>
                                <td class="font-monospace fw-bold text-dark">{{ $invoice->reference_no }}</td>
                            </tr>
                        @endif
                        @if($invoice->subject)
                            <tr>
                                <td class="colon-label text-dark fw-bold" style="width: 105px;">Subject</td>
                                <td class="colon-sep">:</td>
                                <td class="fw-bold text-primary" style="line-height: 1.35;">{{ $invoice->subject }}</td>
                            </tr>
                        @endif
                    </table>
                </div>
            @endif

            {{-- Customer & Billed To Info (Structured Format with Vertical Colon Alignment) --}}
            <div class="p-2.5 bg-light rounded-2 border mb-2.5 destination-box" style="font-size: 12px; box-sizing: border-box;">
                <div class="row g-2 align-items-start m-0">
                    <div class="col-12 col-md-7 col-print-7 p-0 pe-md-2 border-end-md border-bottom border-bottom-md-0 pb-2 pb-md-0 mb-2 mb-md-0">
                        <div class="fw-bold text-dark mb-1" style="font-size: 12px;"><i class="fa-solid fa-user-tag me-1 text-primary"></i>Client / Customer Information:</div>
                        <table class="table-borderless p-0 m-0 w-100 colon-table" style="line-height: 1.45;">
                            @if($invoice->customer_name)
                                <tr>
                                    <td class="colon-label" style="width: 95px;">Name</td>
                                    <td class="colon-sep">:</td>
                                    <td class="fw-bold text-dark" style="font-size: {{ $recipientNameSize }};">{{ $invoice->customer_name }}</td>
                                </tr>
                            @endif
                            @if(!empty($invoice->customer_designation))
                                <tr>
                                    <td class="colon-label" style="width: 95px;">Designation</td>
                                    <td class="colon-sep">:</td>
                                    <td class="fw-semibold text-dark" style="font-size: {{ $recipientDesigSize }};">{{ $invoice->customer_designation }}</td>
                                </tr>
                            @endif
                            @if($invoice->customer_org)
                                <tr>
                                    <td class="colon-label" style="width: 95px;">Organization</td>
                                    <td class="colon-sep">:</td>
                                    <td class="fw-semibold text-primary" style="font-size: {{ $recipientOrgSize }};">{{ $invoice->customer_org }}</td>
                                </tr>
                            @endif
                            @if($invoice->customer_address)
                                <tr>
                                    <td class="colon-label" style="width: 95px;">Address</td>
                                    <td class="colon-sep">:</td>
                                    <td class="text-dark" style="font-size: {{ $recipientAddressSize }}; line-height: 1.35;">{{ $invoice->customer_address }}</td>
                                </tr>
                            @endif
                            @if($invoice->customer_phone)
                                <tr>
                                    <td class="colon-label" style="width: 95px;">Phone</td>
                                    <td class="colon-sep">:</td>
                                    <td class="text-dark fw-bold font-monospace" style="font-size: {{ $recipientPhoneSize }};">{{ $invoice->customer_phone }}</td>
                                </tr>
                            @endif
                        </table>
                    </div>
                    <div class="col-12 col-md-5 col-print-5 p-0 ps-md-2">
                        <div class="text-muted text-uppercase fw-semibold mb-1" style="font-size: 11px;"><i class="fa-solid fa-file-invoice me-1 text-primary"></i>Order & Payment Details:</div>
                        <table class="table-borderless p-0 m-0 w-100 colon-table" style="line-height: 1.45;">
                            <tr>
                                <td class="colon-label" style="width: 85px;">Doc Type</td>
                                <td class="colon-sep">:</td>
                                <td class="fw-bold text-dark">{{ ucfirst($invoice->type) }}</td>
                            </tr>
                            <tr>
                                <td class="colon-label" style="width: 85px;">Payment</td>
                                <td class="colon-sep">:</td>
                                <td class="fw-semibold text-dark">{{ $invoice->payment_method ?? 'Cash / Bank' }}</td>
                            </tr>
                            <tr>
                                <td class="colon-label" style="width: 85px;">Status</td>
                                <td class="colon-sep">:</td>
                                <td>
                                    @if(in_array($invoice->type, ['invoice', 'challan']))
                                        @if($invoice->payment_status === 'paid')
                                             <span class="badge bg-success-subtle text-success border px-2 py-0.5" style="font-size: 10px;">Paid</span>
                                        @elseif($invoice->payment_status === 'partial')
                                             <span class="badge bg-warning-subtle text-dark border px-2 py-0.5" style="font-size: 10px;">Partially Paid</span>
                                        @else
                                             <span class="badge bg-danger-subtle text-danger border px-2 py-0.5" style="font-size: 10px;">Due</span>
                                        @endif
                                    @else
                                        <span class="badge bg-primary-subtle text-primary border px-2 py-0.5" style="font-size: 10px;">Proposed</span>
                                    @endif
                                </td>
                            </tr>
                            @if($invoice->valid_until)
                                <tr>
                                    <td class="colon-label text-danger" style="width: 85px;">Valid Until</td>
                                    <td class="colon-sep text-danger">:</td>
                                    <td class="text-danger fw-semibold">{{ $invoice->valid_until->format('d M, Y') }}</td>
                                </tr>
                            @endif
                        </table>
                    </div>
                </div>
            </div>

            {{-- Items / Price Schedule Table (with Right-Aligned Numbers) --}}
            <div class="table-responsive mb-2">
                <table class="table table-bordered table-sm align-middle invoice-table mb-0" style="font-size: 10px;">
                    <thead class="table-light">
                        <tr class="text-muted text-uppercase" style="font-size: 9px;">
                            <th class="text-center py-1 px-1" style="width: 26px;">#</th>
                            <th class="py-1 px-1.5">
                                @if($invoice->sales_category === 'stationery')
                                    Item Title & Description
                                @elseif($invoice->sales_category === 'printing_goods')
                                    Job / Printing Description
                                @elseif($invoice->sales_category === 'other')
                                    Item Description
                                @else
                                    Book Title & Description
                                @endif
                            </th>
                            <th class="py-1 px-1" style="width: 105px;">
                                @if($invoice->sales_category === 'stationery' || $invoice->sales_category === 'printing_goods')
                                    Spec / Size
                                @elseif($invoice->sales_category === 'other')
                                    Specification
                                @else
                                    Author / Spec
                                @endif
                            </th>
                            <th class="text-center py-1 px-1" style="width: 45px;">Unit</th>
                            <th class="text-end py-1 px-1" style="width: 50px;">Qty</th>
                            <th class="text-end py-1 px-1" style="width: 70px;">
                                @if($invoice->sales_category === 'stationery')
                                    MRP (৳)
                                @elseif($invoice->sales_category === 'printing_goods')
                                    Base Rate
                                @else
                                    Price (৳)
                                @endif
                            </th>
                            <th class="text-end py-1 px-1" style="width: 55px;">Disc %</th>
                            <th class="text-end py-1 px-1" style="width: 80px;">Net Price</th>
                            <th class="text-end py-1 pe-1.5" style="width: 85px;">Total (৳)</th>
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
                                <td class="text-end py-0.5 px-1 fw-bold">{{ $qty }}</td>
                                <td class="text-end py-0.5 px-1">৳{{ number_format($coverPrice, 2) }}</td>
                                <td class="text-end py-0.5 px-1">
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
                </table>
            </div>

            @php
                $specialCommPercent = ($invoice->subtotal > 0 && $invoice->discount > 0)
                    ? round(($invoice->discount / $invoice->subtotal) * 100, 1)
                    : 0;
            @endphp

            {{-- Invoice Summary & Total in Words (Flexbox Grid) --}}
            <div class="invoice-summary-container mb-2.5">
                <div class="row g-2 align-items-stretch">
                    {{-- Left Column: Total in Words & Payment Status --}}
                    <div class="col-12 col-md-6 col-print-6 d-flex flex-column">
                        <div class="p-2.5 bg-light bg-opacity-50 rounded-2 border h-100 d-flex flex-column justify-content-between">
                            <div>
                                <div class="text-muted fw-bold mb-1" style="font-size: 9.5px; text-transform: uppercase; letter-spacing: 0.3px;">
                                    <i class="fa-solid fa-coins me-1 text-primary"></i>Total in Words (টাকা কথায়):
                                </div>
                                <div class="fw-bold text-dark text-wrap" style="font-size: 11.5px; line-height: 1.45;">
                                    @takaInWordsEn($invoice->grand_total)
                                </div>
                            </div>

                            @if(in_array($invoice->type, ['invoice', 'challan']))
                                <div class="mt-2 pt-2 border-top border-secondary-subtle d-flex align-items-center justify-content-between flex-wrap gap-1" style="font-size: 9.5px;">
                                    <span class="text-muted fw-semibold">
                                        <i class="fa-solid fa-receipt me-1 text-secondary"></i>Payment Status:
                                    </span>
                                    @if($invoice->due_amount <= 0)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-0.5 fw-bold" style="font-size: 9px;">
                                            <i class="fa-solid fa-circle-check me-1"></i>FULL PAID
                                        </span>
                                    @elseif($invoice->paid_amount > 0)
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-0.5 fw-bold" style="font-size: 9px;">
                                            <i class="fa-solid fa-clock me-1"></i>PARTIAL PAID (Due: ৳{{ number_format($invoice->due_amount, 2) }})
                                        </span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-0.5 fw-bold" style="font-size: 9px;">
                                            <i class="fa-solid fa-circle-exclamation me-1"></i>UNPAID
                                        </span>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Right Column: Detailed Calculation Breakdown --}}
                    <div class="col-12 col-md-6 col-print-6 ms-auto">
                        <div class="border rounded-2 overflow-hidden bg-white">
                            <table class="table table-sm table-borderless align-middle mb-0 summary-table" style="font-size: 10px;">
                                <tbody>
                                    <tr class="border-bottom border-light">
                                        <td class="py-1 px-2 text-muted fw-semibold">Subtotal:</td>
                                        <td class="py-1 px-2 text-end fw-semibold text-dark font-monospace">৳{{ number_format($invoice->subtotal, 2) }}</td>
                                    </tr>
                                    @if($invoice->discount > 0)
                                        <tr class="border-bottom border-light">
                                            <td class="py-1 px-2 text-danger fw-semibold">
                                                Special Discount @if($specialCommPercent > 0)<span class="badge bg-danger-subtle text-danger border px-1 py-0" style="font-size: 8.5px;">{{ $specialCommPercent }}%</span>@endif:
                                            </td>
                                            <td class="py-1 px-2 text-end text-danger fw-semibold font-monospace">- ৳{{ number_format($invoice->discount, 2) }}</td>
                                        </tr>
                                    @endif
                                    @if(($invoice->previous_due ?? 0) > 0)
                                        <tr class="border-bottom border-warning-subtle table-warning bg-warning bg-opacity-10">
                                            <td class="py-1 px-2 text-dark fw-bold">Previous Due (জের):</td>
                                            <td class="py-1 px-2 text-end text-dark fw-bold font-monospace">+ ৳{{ number_format($invoice->previous_due, 2) }}</td>
                                        </tr>
                                    @endif
                                    @if($invoice->tax > 0)
                                        <tr class="border-bottom border-light">
                                            <td class="py-1 px-2 text-muted fw-semibold">VAT / Tax:</td>
                                            <td class="py-1 px-2 text-end text-muted fw-semibold font-monospace">+ ৳{{ number_format($invoice->tax, 2) }}</td>
                                        </tr>
                                    @endif
                                    <tr class="bg-primary bg-opacity-10 border-top border-primary-subtle">
                                        <td class="py-1.5 px-2 fw-bold text-dark" style="font-size: 11px;">Grand Total:</td>
                                        <td class="py-1.5 px-2 text-end fw-bold text-primary font-monospace" style="font-size: 11.5px;">৳{{ number_format($invoice->grand_total, 2) }}</td>
                                    </tr>
                                    @if(in_array($invoice->type, ['invoice', 'challan']))
                                        @if($invoice->paid_amount > 0)
                                            <tr class="border-top border-light">
                                                <td class="py-1 px-2 text-success fw-bold">Amount Paid:</td>
                                                <td class="py-1 px-2 text-end text-success fw-bold font-monospace">৳{{ number_format($invoice->paid_amount, 2) }}</td>
                                            </tr>
                                        @endif
                                        @if($invoice->due_amount > 0)
                                            <tr class="table-danger bg-danger bg-opacity-10 border-top border-danger-subtle">
                                                <td class="py-1 px-2 text-danger fw-bold">Due Balance:</td>
                                                <td class="py-1 px-2 text-end text-danger fw-bold font-monospace">৳{{ number_format($invoice->due_amount, 2) }}</td>
                                            </tr>
                                        @endif
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Note at end right before signature --}}
            <div class="p-1.5 bg-light rounded-2 text-muted mb-3 border" style="font-size: 10px; line-height: 1.3;">
                <strong class="text-dark"><i class="fa-solid fa-circle-info me-1 text-primary"></i>(Note):</strong> 1. VAT not included unless specified.
                @if($invoice->notes)
                    · {{ $invoice->notes }}
                @endif
                @if($invoice->terms_conditions)
                    · {{ $invoice->terms_conditions }}
                @endif
            </div>

            {{-- Signature, QR Code & Banking Footer (Positioned at A4/Letter page bottom) --}}
            <div class="invoice-footer-compact pt-2 mt-auto border-top">
                @if($invoice->type === 'invoice')
                    {{-- Responsive 5 Columns Layout: Customer Sig | Scan to Verify | bKash/Nagad/Rocket QR | Bank Payment QR | Authorized Sig --}}
                    <div class="row g-2 align-items-end text-center footer-signature-grid" style="font-size: 10px;">
                        {{-- 1. Customer Signature --}}
                        <div class="col-6 col-md-3 col-print-3 text-center">
                            <div class="signature-box" style="margin-top: 24px;">
                                <div class="border-top border-dark pt-1 fw-semibold text-dark" style="font-size: 9px;">
                                    Customer's Signature
                                </div>
                            </div>
                        </div>

                        {{-- 2. Scan to Verify QR --}}
                        <div class="col-6 col-md-2 col-print-2 text-center">
                            <a href="{{ $invoiceUrl }}" target="_blank" class="text-decoration-none d-inline-flex flex-column align-items-center">
                                <div class="p-1 rounded border bg-white shadow-2xs d-inline-block">
                                    <img src="{{ $qrCodeUrl }}" alt="Verify QR" style="width: {{ $qrCodeSize }}; height: {{ $qrCodeSize }}; object-fit: contain; display: block;">
                                </div>
                                <div class="text-primary fw-bold text-nowrap mt-1" style="font-size: 7.5px; line-height: 1.1;">
                                    Scan to Verify: #{{ $invoice->invoice_no }}
                                </div>
                            </a>
                        </div>

                        {{-- 3. bKash / Nagad / Rocket QR --}}
                        <div class="col-6 col-md-2 col-print-2 text-center">
                            <div class="d-inline-flex flex-column align-items-center">
                                <div class="p-1 rounded border bg-white shadow-2xs d-inline-block">
                                    <img src="{{ $mfsQrSrc }}" alt="MFS QR" style="width: {{ $qrCodeSize }}; height: {{ $qrCodeSize }}; object-fit: contain; display: block;">
                                </div>
                                <div class="text-dark fw-bold text-nowrap mt-1 font-monospace" style="font-size: 7.5px; line-height: 1.1;">
                                    {{ $settings['mfs_qr_note'] ?? 'bkash/nagad/roket' }}
                                </div>
                            </div>
                        </div>

                        {{-- 4. Bank Payment QR --}}
                        <div class="col-6 col-md-2 col-print-2 text-center">
                            <div class="d-inline-flex flex-column align-items-center">
                                <div class="p-1 rounded border bg-white shadow-2xs d-inline-block">
                                    <img src="{{ $bankQrSrc }}" alt="Bank QR" style="width: {{ $qrCodeSize }}; height: {{ $qrCodeSize }}; object-fit: contain; display: block;">
                                </div>
                                <div class="text-dark fw-bold text-nowrap mt-1 font-monospace" style="font-size: 7.5px; line-height: 1.1;">
                                    {{ $settings['bank_qr_note'] ?? 'bank payment' }}
                                </div>
                            </div>
                        </div>

                        {{-- 5. Authorized Signature --}}
                        <div class="col-12 col-md-3 col-print-3 text-center mt-3 mt-md-0">
                            <div class="signature-box" style="margin-top: 14px;">
                                <div class="fw-bold text-dark text-truncate" style="font-size: 10.5px; line-height: 1.2;">
                                    {{ $creatorName }}
                                </div>
                                <div class="text-muted fw-semibold text-truncate" style="font-size: 9px; line-height: 1.2;">
                                    {{ $creatorDesignation }}
                                </div>
                                <div class="border-top border-dark pt-1 mt-1 fw-semibold text-dark" style="font-size: 9px;">
                                    Authorized Signature
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    {{-- 3 Columns Layout for Delivery Challan / Quotation / Tender --}}
                    <div class="row g-2 align-items-end text-center footer-signature-grid" style="font-size: 10px;">
                        <div class="col-6 col-md-4 col-print-4">
                            <div class="signature-box" style="margin-top: 24px;">
                                <div class="border-top border-dark pt-1 fw-semibold text-dark">
                                    Customer's Signature
                                </div>
                            </div>
                        </div>

                        {{-- QR Code & Verification Box --}}
                        <div class="col-6 col-md-4 col-print-4 text-center">
                            <a href="{{ $invoiceUrl }}" target="_blank" class="text-decoration-none d-inline-flex flex-column align-items-center">
                                <div class="p-1 rounded border bg-white shadow-2xs d-inline-block">
                                    <img src="{{ $qrCodeUrl }}" alt="QR" style="width: {{ $qrCodeSize }}; height: {{ $qrCodeSize }}; object-fit: contain; display: block;">
                                </div>
                                <div class="text-primary fw-bold text-nowrap mt-1" style="font-size: 8px; line-height: 1.1;">
                                    Scan to Verify: #{{ $invoice->invoice_no }}
                                </div>
                            </a>
                        </div>

                        <div class="col-12 col-md-4 col-print-4 text-center mt-3 mt-md-0">
                            <div class="signature-box" style="margin-top: 14px;">
                                <div class="fw-bold text-dark" style="font-size: 11px; line-height: 1.25;">
                                    {{ $creatorName }}
                                </div>
                                <div class="text-muted fw-semibold" style="font-size: 9.5px; line-height: 1.25;">
                                    {{ $creatorDesignation }}
                                </div>
                                <div class="border-top border-dark pt-1 mt-1 fw-semibold text-dark" style="font-size: 9.5px;">
                                    Authorized Signature
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

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
                
                {{-- Institutional / Company Header in 2-Column Responsive Layout --}}
                <div class="row align-items-center border-bottom pb-2 mb-2 g-2">
                    <div class="col-12 col-md-7 col-print-7">
                        <div class="d-flex align-items-center gap-2.5 invoice-brand-header">
                            <img src="{{ $logoSrc }}" alt="{{ $settings['business_name'] ?? 'Idea Publication' }}" 
                                 class="img-fluid invoice-logo-img" style="height: 48px; width: 96px; aspect-ratio: 2/1; object-fit: contain; flex-shrink: 0; margin-right: 4px;">
                            <div class="d-flex flex-column justify-content-center" style="line-height: 1.3; padding-left: 2px;">
                                <div class="fw-bold text-primary invoice-brand-name" style="font-size: 15.5px; margin-bottom: 2px;">{{ $settings['business_name'] ?? 'Idea Publication' }}</div>
                                <div class="text-muted invoice-tagline" style="font-size: 10px; margin-bottom: 2px;">{{ $settings['tagline'] ?? 'Book Publication, Printing & Distribution' }}</div>
                                <div class="text-muted invoice-contact-info" style="font-size: 9.5px; line-height: 1.35;">
                                    <span><i class="fa-solid fa-location-dot me-0.5 text-danger"></i>{{ $settings['address'] ?? 'Dhaka, Bangladesh' }}</span>
                                    <span class="mx-1 text-muted">·</span>
                                    <span><i class="fa-solid fa-phone me-0.5 text-primary"></i>{{ $settings['phone'] ?? '018XXXXXXXX' }}</span>
                                    <span class="mx-1 text-muted">·</span>
                                    <span><i class="fa-solid fa-envelope me-0.5 text-primary"></i>{{ $settings['email'] ?? 'info@ideaabd.com' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-5 col-print-5 text-md-end text-start mt-2 mt-md-0">
                        <span class="badge border px-2 py-0.5 rounded-pill mb-0.5 d-inline-block fw-bold" style="font-size: 10px; background-color: #e0f2fe; color: #0369a1; border-color: #7dd3fc;">
                            DELIVERY CHALLAN
                        </span>
                        <div class="fw-bold text-dark mb-0 font-monospace invoice-no-text" style="font-size: 13pt; line-height: 1.2;">#{{ $invoice->invoice_no }}</div>
                        
                        <div class="text-muted fw-semibold" style="font-size: 9.5px; line-height: 1.2;">
                            <i class="fa-solid fa-truck me-1"></i>Computer-generated delivery challan · Date: <strong>{{ $invoice->invoice_date ? $invoice->invoice_date->format('d M, Y') : '—' }}</strong>
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
                        <div class="col-12 col-md-7 col-print-7 p-0 pe-md-2 border-end-md border-bottom border-bottom-md-0 pb-2 pb-md-0 mb-2 mb-md-0">
                            <div class="fw-bold text-dark mb-1 d-flex align-items-center justify-content-between" style="font-size: 12px;">
                                <span><i class="fa-solid fa-truck-ramp-box me-1 text-primary"></i>Delivery Destination & Recipient:</span>
                            </div>
                            <table class="table-borderless p-0 m-0 w-100 colon-table recipient-info-table" style="line-height: 1.45;">
                                @if($invoice->customer_name)
                                    <tr>
                                        <td class="colon-label" style="width: 95px;">Recipient</td>
                                        <td class="colon-sep">:</td>
                                        <td class="fw-bold text-dark target-recipient-name" id="challanRecipientName" style="font-size: {{ $recipientNameSize }};">{{ $invoice->customer_name }}</td>
                                    </tr>
                                @endif
                                @if(!empty($invoice->customer_designation))
                                    <tr>
                                        <td class="colon-label" style="width: 95px;">Designation</td>
                                        <td class="colon-sep">:</td>
                                        <td class="fw-semibold text-dark target-recipient-desig" id="challanRecipientDesig" style="font-size: {{ $recipientDesigSize }};">{{ $invoice->customer_designation }}</td>
                                    </tr>
                                @endif
                                @if($invoice->customer_org)
                                    <tr>
                                        <td class="colon-label" style="width: 95px;">Organization</td>
                                        <td class="colon-sep">:</td>
                                        <td class="fw-semibold text-primary target-recipient-org" id="challanRecipientOrg" style="font-size: {{ $recipientOrgSize }};">{{ $invoice->customer_org }}</td>
                                    </tr>
                                @endif
                                @if($invoice->customer_address)
                                    <tr>
                                        <td class="colon-label" style="width: 95px;">Address</td>
                                        <td class="colon-sep">:</td>
                                        <td class="text-dark target-recipient-address" id="challanRecipientAddr" style="font-size: {{ $recipientAddressSize }}; line-height: 1.35;">{{ $invoice->customer_address }}</td>
                                    </tr>
                                @endif
                                @if($invoice->customer_phone)
                                    <tr>
                                        <td class="colon-label" style="width: 95px;">Mobile</td>
                                        <td class="colon-sep">:</td>
                                        <td class="text-dark fw-bold font-monospace target-recipient-phone" id="challanRecipientPhone" style="font-size: {{ $recipientPhoneSize }};">{{ $invoice->customer_phone }}</td>
                                    </tr>
                                @endif
                            </table>
                        </div>
                        <div class="col-12 col-md-5 col-print-5 p-0 ps-md-2">
                            <div class="text-muted text-uppercase fw-semibold mb-1" style="font-size: 11px;"><i class="fa-solid fa-truck-fast me-1 text-primary"></i>Challan Tracking & Dispatch Info:</div>
                            <table class="table-borderless p-0 m-0 w-100 colon-table" style="line-height: 1.45;">
                                <tr>
                                    <td class="colon-label" style="width: 90px;">Challan Type</td>
                                    <td class="colon-sep">:</td>
                                    <td class="fw-semibold text-dark">Goods Delivery</td>
                                </tr>
                                <tr>
                                    <td class="colon-label" style="width: 90px;">Total Items</td>
                                    <td class="colon-sep">:</td>
                                    <td class="fw-semibold text-dark">{{ count($invoice->items ?? []) }} items</td>
                                </tr>
                                <tr>
                                    <td class="colon-label" style="width: 90px;">Total Qty</td>
                                    <td class="colon-sep">:</td>
                                    <td class="fw-bold text-primary">{{ $totalQuantity }} pcs</td>
                                </tr>
                                <tr>
                                    <td class="colon-label" style="width: 90px;">Dispatcher</td>
                                    <td class="colon-sep">:</td>
                                    <td class="fw-semibold text-dark">{{ $creatorName }}</td>
                                </tr>
                            </table>
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
                                <th class="text-end py-1 px-1" style="width: 50px;">Qty</th>
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
                                    <td class="text-end py-0.5 px-1 fw-bold text-primary">{{ $item['quantity'] ?? 1 }}</td>
                                    <td class="text-center py-0.5 px-1 text-muted">Brand New</td>
                                    <td class="py-0.5 px-1.5 text-muted">Verified</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="table-light">
                                <td colspan="5" class="text-end py-1 px-1.5 fw-bold">Total Delivered Items / Quantity:</td>
                                <td class="text-end py-1 px-1 fw-bold text-primary" style="font-size: 11px;">{{ $totalQuantity }}</td>
                                <td colspan="2" class="py-1 px-1.5 text-muted" style="font-size: 9px;">Complete lot dispatched</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {{-- Challan Notes --}}
                <div class="p-1.5 bg-light rounded-2 text-muted mb-3 border" style="font-size: 10px; line-height: 1.3;">
                    <strong class="text-dark"><i class="fa-solid fa-circle-info me-1 text-success"></i>(Note):</strong> 1. Please verify the quantity and binding condition before signing receipt.
                    @if($invoice->notes)
                        · {{ $invoice->notes }}
                    @endif
                </div>

                {{-- Challan Signatures & QR Code --}}
                <div class="invoice-footer-compact pt-2 mt-auto border-top">
                    <div class="row g-2 align-items-end text-center footer-signature-grid" style="font-size: 10px;">
                        <div class="col-6 col-md-4 col-print-4">
                            <div class="signature-box" style="margin-top: 24px;">
                                <div class="border-top border-dark pt-1 fw-semibold text-dark">
                                    Recipient's Signature
                                </div>
                            </div>
                        </div>

                        {{-- QR Code & Verification Box --}}
                        <div class="col-6 col-md-4 col-print-4 text-center">
                            <div class="d-inline-flex align-items-center gap-1.5 px-2 py-1 rounded border bg-white shadow-xs">
                                <img src="{{ $qrCodeUrl }}" alt="QR" style="width: {{ $qrCodeSize }}; height: {{ $qrCodeSize }}; object-fit: contain;">
                                <div class="text-start" style="line-height: 1.15;">
                                    <span class="text-muted fw-semibold d-block" style="font-size: 8px;"><i class="fa-solid fa-qrcode me-0.5"></i>Scan to Verify</span>
                                    <span class="font-monospace text-dark fw-bold" style="font-size: 9px;">#{{ $invoice->invoice_no }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-4 col-print-4 text-center mt-3 mt-md-0">
                            <div class="signature-box" style="margin-top: 14px;">
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

{{-- MULTI-BILL OUTSTANDING DUES BANNER (If customer has dues in multiple invoices) --}}
@if(($customerDueCount ?? 0) > 1 || (isset($otherDueInvoices) && $otherDueInvoices->isNotEmpty()))
<div class="row justify-content-center mt-3 d-print-none">
    <div class="col-lg-10">
        <div class="card border border-warning bg-warning bg-opacity-10 shadow-xs rounded-4 p-3.5 mb-2">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-2.5 bg-warning text-dark rounded-circle fs-5 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        <i class="fa-solid fa-layer-group"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1 text-dark">
                            গ্রাহক <span class="text-primary">{{ $invoice->customer_name }}</span>-এর একাধিক বিলে বকেয়া রয়েছে
                        </h6>
                        <div class="text-muted small">
                            মোট বকেয়া বিল: <strong class="text-dark">{{ $customerDueCount }}টি</strong> | সর্বমোট অপরিশোধিত বকেয়া: <strong class="text-danger font-monospace fs-6">৳{{ number_format($customerTotalDue, 2) }}</strong>
                            (বর্তমান ইনভয়েস বকেয়া: <strong class="text-dark font-monospace">৳{{ number_format($invoice->due_amount, 2) }}</strong>)
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('admin.accounting.customer-ledger.index', ['customer_name' => $invoice->customer_name, 'customer_phone' => $invoice->customer_phone]) }}" class="btn btn-outline-dark btn-sm rounded-pill px-3 fw-semibold shadow-xs">
                        <i class="fa-solid fa-book-bookmark me-1 text-primary"></i> খতিয়ান দেখুন
                    </a>
                    <button type="button" class="btn btn-warning text-dark btn-sm rounded-pill px-3.5 fw-bold shadow-sm" onclick="openAllDueSettlementModal()">
                        <i class="fa-solid fa-hand-holding-dollar me-1.5"></i> সকল বকেয়া একসাথে পরিশোধ করুন
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

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
                        <i class="fa-solid fa-hand-holding-dollar fs-6"></i>
                    </span>
                    <div>
                        <h5 class="card-title fw-bold mb-0 text-dark">ধাপে ধাপে কিস্তি ও জমা পরিশোধের হিসাব</h5>
                        <div class="text-muted small">ইনভয়েস #{{ $invoice->invoice_no }} — {{ $invoice->customer_name }}</div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('admin.accounting.customer-ledger.index', ['customer_name' => $invoice->customer_name, 'customer_phone' => $invoice->customer_phone]) }}" class="btn btn-outline-info text-dark btn-sm rounded-pill px-3 fw-semibold shadow-xs">
                        <i class="fa-solid fa-book-bookmark me-1 text-primary"></i> সম্পূর্ণ গ্রাহক খতিয়ান
                    </a>
                    @if($invoice->due_amount > 0 || ($customerTotalDue ?? 0) > 0)
                        <button type="button" class="btn btn-success btn-sm rounded-pill px-3 fw-bold shadow-xs" data-bs-toggle="modal" data-bs-target="#recordInvoicePaymentModal">
                            <i class="fa-solid fa-plus me-1"></i> নতুন কিস্তি জমা নিন
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
                                        <i class="fa-solid fa-calendar-day me-1"></i>{{ $invoice->due_date->format('d M, Y') }}
                                        @if($invoice->is_overdue)
                                            <span class="badge bg-danger text-white ms-1" style="font-size: 9px;">মেয়াদোত্তীর্ণ</span>
                                        @endif
                                    </span>
                                @elseif($invoice->due_amount <= 0)
                                    <span class="text-success small"><i class="fa-solid fa-circle-check me-1"></i>সম্পূর্ণ পরিশোধিত</span>
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
                    <i class="fa-solid fa-clock-rotate-left text-primary me-1.5"></i>Payment History
                </h6>

                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle mb-0" style="font-size: 13px;">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center py-2 px-3" style="width: 45px;">#</th>
                                <th class="text-center py-2 px-3" style="width: 130px;">Receipt</th>
                                <th class="text-center py-2 px-3" style="width: 110px;">Date</th>
                                <th class="text-end py-2 px-3" style="width: 130px;">Amount</th>
                                <th class="text-center py-2 px-3" style="width: 120px;">Method</th>
                                <th class="py-2 px-3">Description / Ref</th>
                                <th class="text-center py-2 px-3" style="width: 130px;">Collector</th>
                                <th class="text-center py-2 px-3" style="width: 140px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($invoice->payments as $pIdx => $pmt)
                                <tr>
                                    <td class="text-center text-muted fw-semibold py-2 px-3">{{ $pIdx + 1 }}</td>
                                    <td class="text-center py-2 px-3">
                                        <span class="font-monospace fw-bold text-dark">#{{ $pmt->payment_no }}</span>
                                    </td>
                                    <td class="text-center font-monospace py-2 px-3">
                                        {{ $pmt->payment_date ? $pmt->payment_date->format('d M, Y') : '—' }}
                                    </td>
                                    <td class="text-end font-monospace py-2 px-3">
                                        <div class="fw-bold text-success fs-6">৳{{ number_format($pmt->amount, 2) }}</div>
                                        @if($pmt->has_deductions)
                                            <div class="mt-0.5" style="font-size: 10.5px;">
                                                <span class="badge bg-warning-subtle text-dark border border-warning" title="Net: ৳{{ number_format($pmt->effective_net_amount, 2) }} | Deductions: ৳{{ number_format($pmt->total_deductions, 2) }}">
                                                    Net: ৳{{ number_format($pmt->effective_net_amount, 2) }} | TDS/VDS: ৳{{ number_format($pmt->total_deductions, 2) }}
                                                </span>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="text-center py-2 px-3">
                                        <span class="badge bg-light text-dark border px-2 py-1">
                                            {{ \App\Models\IdeaInvoicePayment::paymentMethods()[$pmt->payment_method] ?? ucfirst($pmt->payment_method) }}
                                        </span>
                                    </td>
                                    <td class="py-2 px-3">
                                        <div class="text-dark fw-medium">{{ $pmt->note ?: '—' }}</div>
                                        @if($pmt->transaction_ref)
                                            <div class="text-muted font-monospace" style="font-size: 11px;">Trx: {{ $pmt->transaction_ref }}</div>
                                        @endif
                                        @if($pmt->deduction_challan_no)
                                            <div class="text-primary font-monospace" style="font-size: 10.5px;"><i class="fa-solid fa-file-lines me-1"></i>Challan: {{ $pmt->deduction_challan_no }}</div>
                                        @endif
                                    </td>
                                    <td class="text-center text-muted small py-2 px-3">
                                        {{ $pmt->recorder?->name ?? 'Admin' }}
                                    </td>
                                    <td class="text-center py-2 px-3">
                                        <div class="d-flex align-items-center justify-content-center gap-1.5">
                                            <a href="{{ route('admin.accounting.invoices.payments.receipt', $pmt->id) }}" class="btn btn-sm btn-outline-success rounded-pill px-2.5 py-1 fw-semibold" title="View Receipt">
                                                <i class="fa-solid fa-file-shield me-1"></i>Receipt
                                            </a>

                                            <form action="{{ route('admin.accounting.invoices.payments.destroy', $pmt->id) }}" method="POST" class="d-inline"
                                                  data-confirm="Are you sure you want to delete payment record (#{{ $pmt->payment_no }})?" data-confirm-title="Delete Payment">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle p-1" style="width: 28px; height: 28px; line-height: 1;" title="Delete">
                                                    <i class="fa-solid fa-trash-can" style="font-size: 11px;"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">
                                        <i class="fa-solid fa-receipt fs-3 mb-2 d-block text-secondary"></i>
                                        No payments recorded yet.
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
                        <i class="fa-solid fa-copy me-1 text-primary"></i> Copy Link
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
                        <i class="fa-solid fa-paper-plane me-1"></i> Send Email
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
                                                    <i class="fa-solid fa-rotate-right me-0.5"></i> Resend
                                                </button>
                                                @if(!empty($log['id']))
                                                    <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2 py-0.5 shadow-2xs" 
                                                            style="font-size: 11px;" 
                                                            onclick="deleteEmailLogEntry('{{ $invoice->id }}', '{{ $log['id'] }}', this)" 
                                                            title="এই লগটি মুছে ফেলুন">
                                                        <i class="fa-solid fa-trash-can"></i>
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
                            <i class="fa-solid fa-paper-plane me-1.5"></i> এখনই গ্রাহককে ইমেইল পাঠান
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
                                            <i class="fa-solid fa-rotate-left me-1"></i>অটোফিল রিস্টোর
                                        </button>
                                        <button type="submit" class="btn btn-sm btn-success rounded-pill px-4 py-1.5 fw-bold shadow-xs" id="btnSaveCustomMsg">
                                            <i class="fa-solid fa-save me-1.5" id="btnSaveCustomMsgIcon"></i>
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
        btnIcon.className = "fa-solid fa-spinner fa-spin me-1.5";
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
            btnIcon.className = "fa-solid fa-save me-1.5";
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
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';

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
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form action="{{ route('admin.accounting.invoices.payments.store', $invoice->id) }}" method="POST" id="recordPaymentForm">
                @csrf
                <input type="hidden" name="customer_name" value="{{ $invoice->customer_name }}">
                <input type="hidden" name="customer_phone" value="{{ $invoice->customer_phone }}">
                
                <div class="modal-header bg-success text-white py-3">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fa-solid fa-hand-holding-dollar fs-5"></i>
                        <div>
                            <h5 class="modal-title fw-bold mb-0" id="recordInvoicePaymentModalLabel">
                                Record Payment
                            </h5>
                            <small class="text-white-50" style="font-size: 11.5px;">Customer: {{ $invoice->customer_name }} ({{ $invoice->customer_phone ?: 'No phone' }})</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body p-4">
                    {{-- Settlement Scope Selector (If customer has multiple due bills) --}}
                    @if(($customerDueCount ?? 0) > 1 || (isset($otherDueInvoices) && $otherDueInvoices->isNotEmpty()))
                        <div class="card border border-primary-subtle bg-primary-subtle bg-opacity-10 rounded-3 p-2.5 mb-3">
                            <label class="form-label small fw-bold text-dark mb-1.5 d-block">
                                <i class="fa-solid fa-sliders text-primary me-1"></i>Payment Scope:
                            </label>
                            <div class="d-flex gap-2 flex-wrap">
                                <div class="form-check form-check-inline m-0 p-2 bg-white rounded-2 border flex-fill">
                                    <input class="form-check-input ms-0 me-2" type="radio" name="payment_scope_selector" id="scopeSingleInvoice" value="single" checked onchange="togglePaymentScope('single')">
                                    <label class="form-check-label fw-bold text-dark small" for="scopeSingleInvoice">
                                        1. This Invoice (#{{ $invoice->invoice_no }})
                                        <span class="d-block text-danger font-monospace fw-normal" style="font-size: 11px;">Due: ৳{{ number_format($invoice->due_amount, 2) }}</span>
                                    </label>
                                </div>
                                <div class="form-check form-check-inline m-0 p-2 bg-white rounded-2 border flex-fill">
                                    <input class="form-check-input ms-0 me-2" type="radio" name="payment_scope_selector" id="scopeAllInvoices" value="all" onchange="togglePaymentScope('all')">
                                    <label class="form-check-label fw-bold text-dark small" for="scopeAllInvoices">
                                        2. Settle All Dues (FIFO)
                                        <span class="d-block text-danger font-monospace fw-normal" style="font-size: 11px;">Total {{ $customerDueCount }} Invoices: ৳{{ number_format($customerTotalDue, 2) }}</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Single Bill Summary Card --}}
                    <div id="singleInvoiceSummaryBox" class="bg-light p-3 rounded-3 border mb-3">
                        <div class="d-flex justify-content-between small text-muted mb-1">
                            <span>Invoice: <strong class="text-dark font-monospace">#{{ $invoice->invoice_no }}</strong></span>
                            <span>Total Billed: <strong class="text-dark font-monospace">৳{{ number_format($invoice->grand_total, 2) }}</strong></span>
                        </div>
                        <div class="d-flex justify-content-between small text-muted">
                            <span>Customer: <strong class="text-dark">{{ $invoice->customer_name }}</strong></span>
                            <span>Current Due: <strong class="text-danger fw-bold font-monospace fs-6" id="singleInvoiceDueDisplay">৳{{ number_format($invoice->due_amount, 2) }}</strong></span>
                        </div>
                    </div>

                    {{-- Multi-Bill Due Breakdown Table (Hidden by default, shown in 'all' mode) --}}
                    @if(isset($allDueInvoices) && $allDueInvoices->isNotEmpty())
                        <div id="allInvoicesBreakdownBox" class="mb-3 d-none">
                            <div class="d-flex justify-content-between align-items-center mb-1.5">
                                <span class="small fw-bold text-dark">
                                    <i class="fa-solid fa-list-check text-success me-1"></i>Due Invoices (FIFO order):
                                </span>
                                <span class="badge bg-danger text-white">Total Due: ৳{{ number_format($customerTotalDue, 2) }}</span>
                            </div>
                            <div class="table-responsive border rounded-3 bg-white" style="max-height: 160px; overflow-y: auto;">
                                <table class="table table-sm table-hover align-middle mb-0" style="font-size: 11.5px;">
                                    <thead class="table-light sticky-top">
                                        <tr>
                                            <th class="py-1 px-2 text-center" style="width: 40px;">#</th>
                                            <th class="py-1 px-2">Invoice</th>
                                            <th class="py-1 px-2 text-center">Date</th>
                                            <th class="py-1 px-2 text-end">Billed</th>
                                            <th class="py-1 px-2 text-end">Paid</th>
                                            <th class="py-1 px-2 text-end">Due</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($allDueInvoices as $dIdx => $dInv)
                                            <tr class="{{ $dInv->id === $invoice->id ? 'table-warning bg-warning bg-opacity-10 fw-bold' : '' }}">
                                                <td class="py-1 px-2 text-center text-muted">{{ $dIdx + 1 }}</td>
                                                <td class="py-1 px-2">
                                                    <span class="font-monospace">#{{ $dInv->invoice_no }}</span>
                                                    @if($dInv->id === $invoice->id)
                                                        <span class="badge bg-primary text-white ms-1" style="font-size: 9px;">Current</span>
                                                    @endif
                                                </td>
                                                <td class="py-1 px-2 text-center">{{ $dInv->invoice_date ? $dInv->invoice_date->format('d M, Y') : '—' }}</td>
                                                <td class="py-1 px-2 text-end font-monospace">৳{{ number_format($dInv->grand_total, 2) }}</td>
                                                <td class="py-1 px-2 text-end font-monospace text-success">৳{{ number_format($dInv->paid_amount, 2) }}</td>
                                                <td class="py-1 px-2 text-end font-monospace text-danger fw-bold">৳{{ number_format($dInv->due_amount, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    {{-- VAT & Tax Deduction Adjustment Calculator --}}
                    <div class="card border border-warning-subtle bg-warning-subtle bg-opacity-10 rounded-3 p-3 mb-3" id="vatTaxAdjustmentCard">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="form-check form-switch m-0">
                                <input class="form-check-input" type="checkbox" role="switch" id="toggleVatTaxDeduction" onchange="toggleVatTaxSection(this.checked)">
                                <label class="form-check-label fw-bold text-dark small" for="toggleVatTaxDeduction">
                                    <i class="fa-solid fa-calculator text-warning-emphasis me-1"></i> TDS & VDS Adjustment Calculator
                                </label>
                            </div>
                            <span class="badge bg-warning text-dark border font-monospace" style="font-size: 11px;">TDS / VDS</span>
                        </div>
                        
                        <div id="vatTaxCalculatorPanel" class="mt-3 pt-3 border-top border-warning-subtle d-none">
                            <div class="d-flex align-items-center justify-content-between mb-2.5 flex-wrap gap-2">
                                <span class="text-muted small fw-bold"><i class="fa-solid fa-arrow-right-arrow-left text-primary me-1"></i>Calculation Mode:</span>
                                <div class="btn-group btn-group-sm" role="group">
                                    <input type="radio" class="btn-check" name="calc_mode" id="calcModeGross" value="gross" checked onchange="switchCalcMode('gross')">
                                    <label class="btn btn-outline-primary btn-sm py-0.5 px-2.5 font-monospace" for="calcModeGross" style="font-size: 11.5px;">1. Gross (From Due)</label>
                                    
                                    <input type="radio" class="btn-check" name="calc_mode" id="calcModeNet" value="net" onchange="switchCalcMode('net')">
                                    <label class="btn btn-outline-primary btn-sm py-0.5 px-2.5 font-monospace" for="calcModeNet" style="font-size: 11.5px;">2. Net (From Received)</label>
                                </div>
                            </div>

                            <div class="row g-2.5 mb-2.5">
                                {{-- VAT / VDS --}}
                                <div class="col-md-6 col-12">
                                    <div class="p-2.5 bg-white rounded-3 border">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <label class="form-label small fw-bold text-dark mb-0">VDS (VAT):</label>
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-1.5" onclick="setVatRate(0)">0%</button>
                                                <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-1.5" onclick="setVatRate(5)">5%</button>
                                                <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-1.5" onclick="setVatRate(7.5)">7.5%</button>
                                                <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-1.5" onclick="setVatRate(15)">15%</button>
                                            </div>
                                        </div>
                                        <div class="input-group input-group-sm mb-1">
                                            <input type="number" step="0.01" min="0" max="100" name="vat_deduction_rate" id="vatDeductionRate" class="form-control font-monospace" placeholder="Rate %" value="" oninput="calculateVatTaxDeductions()">
                                            <span class="input-group-text">%</span>
                                            <input type="number" step="0.01" min="0" name="vat_deduction_amount" id="vatDeductionAmount" class="form-control font-monospace text-danger fw-bold" placeholder="Amount" value="" oninput="onDirectDeductionAmountChange()">
                                            <span class="input-group-text">৳</span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Tax / TDS / AIT --}}
                                <div class="col-md-6 col-12">
                                    <div class="p-2.5 bg-white rounded-3 border">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <label class="form-label small fw-bold text-dark mb-0">TDS (Tax):</label>
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-1.5" onclick="setTaxRate(0)">0%</button>
                                                <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-1.5" onclick="setTaxRate(2)">2%</button>
                                                <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-1.5" onclick="setTaxRate(3)">3%</button>
                                                <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-1.5" onclick="setTaxRate(5)">5%</button>
                                                <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-1.5" onclick="setTaxRate(7)">7%</button>
                                            </div>
                                        </div>
                                        <div class="input-group input-group-sm mb-1">
                                            <input type="number" step="0.01" min="0" max="100" name="tax_deduction_rate" id="taxDeductionRate" class="form-control font-monospace" placeholder="Rate %" value="" oninput="calculateVatTaxDeductions()">
                                            <span class="input-group-text">%</span>
                                            <input type="number" step="0.01" min="0" name="tax_deduction_amount" id="taxDeductionAmount" class="form-control font-monospace text-danger fw-bold" placeholder="Amount" value="" oninput="onDirectDeductionAmountChange()">
                                            <span class="input-group-text">৳</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-2.5 mb-2.5">
                                {{-- Net Received (Cheque/Cash in hand) --}}
                                <div class="col-md-6 col-12">
                                    <div class="p-2.5 bg-white rounded-3 border">
                                        <label class="form-label small fw-bold text-dark mb-1">
                                            <i class="fa-solid fa-money-bill-wave text-success me-1"></i>Net Received:
                                        </label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">৳</span>
                                            <input type="number" step="0.01" min="0" name="net_amount" id="netAmountInput" class="form-control font-monospace fw-bold text-success" placeholder="0.00" oninput="onNetChequeAmountChange()">
                                        </div>
                                    </div>
                                </div>

                                {{-- Other Deductions / Security --}}
                                <div class="col-md-6 col-12">
                                    <div class="p-2.5 bg-white rounded-3 border">
                                        <label class="form-label small fw-bold text-dark mb-1">Other Deduction:</label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">৳</span>
                                            <input type="number" step="0.01" min="0" name="other_deduction_amount" id="otherDeductionAmount" class="form-control font-monospace text-danger fw-bold" placeholder="0.00" oninput="calculateVatTaxDeductions()">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Deduction Challan & Certificate Info --}}
                            <div class="row g-2.5 mb-2.5">
                                <div class="col-md-6 col-12">
                                    <label class="form-label small fw-semibold text-dark mb-1">Challan / Mushak Ref:</label>
                                    <input type="text" name="deduction_challan_no" id="deductionChallanNo" class="form-control form-control-sm font-monospace" placeholder="e.g. TR-12345 / Mushak 6.6">
                                </div>
                                <div class="col-md-6 col-12">
                                    <label class="form-label small fw-semibold text-dark mb-1">Deduction Note:</label>
                                    <input type="text" name="deduction_notes" id="deductionNotes" class="form-control form-control-sm" placeholder="Notes on TDS/VDS deductions">
                                </div>
                            </div>

                            {{-- Live Settlement Summary Pill Bar --}}
                            <div class="p-2.5 bg-success-subtle bg-opacity-25 rounded-3 border border-success-subtle">
                                <div class="row g-2 text-center" style="font-size: 12px;">
                                    <div class="col-4 border-end border-success-subtle">
                                        <span class="text-muted d-block" style="font-size: 11px;">Net Received</span>
                                        <strong class="text-success font-monospace fs-7" id="displayCalcNet">৳0.00</strong>
                                    </div>
                                    <div class="col-4 border-end border-success-subtle">
                                        <span class="text-muted d-block" style="font-size: 11px;">Total Deductions</span>
                                        <strong class="text-danger font-monospace fs-7" id="displayCalcDeductions">৳0.00</strong>
                                    </div>
                                    <div class="col-4">
                                        <span class="text-muted d-block" style="font-size: 11px;">Total Credit</span>
                                        <strong class="text-primary font-monospace fs-7" id="displayCalcGross">৳0.00</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6 col-12">
                            <label class="form-label small fw-bold text-dark">Date: <span class="text-danger">*</span></label>
                            <input type="date" name="payment_date" class="form-control" required value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-6 col-12">
                            <label class="form-label small fw-bold text-dark" id="paymentAmountLabel">Amount: <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">৳</span>
                                <input type="number" step="0.01" min="0.01" name="amount" id="paymentAmountInput" class="form-control fw-bold font-monospace text-success fs-5" required placeholder="0.00" value="{{ $invoice->due_amount > 0 ? $invoice->due_amount : ($customerTotalDue ?? '') }}" oninput="onGrossAmountChange()">
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6 col-12">
                            <label class="form-label small fw-bold text-dark">Method: <span class="text-danger">*</span></label>
                            <select name="payment_method" class="form-select" required>
                                @foreach(\App\Models\IdeaInvoicePayment::paymentMethods() as $code => $lbl)
                                    <option value="{{ $code }}" {{ $code === 'cheque' ? 'selected' : ($code === 'cash' ? 'selected' : '') }}>{{ $lbl }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 col-12">
                            <label class="form-label small fw-bold text-dark">Trx / Ref:</label>
                            <input type="text" name="transaction_ref" class="form-control font-monospace" placeholder="Ref / Cheque number">
                        </div>
                    </div>

                    <div class="row g-3 mb-3" id="paymentDueDateContainer">
                        <div class="col-md-6 col-12">
                            <label class="form-label small fw-bold text-dark">Next Due:</label>
                            <input type="date" name="due_date" class="form-control" value="{{ $invoice->due_date ? $invoice->due_date->format('Y-m-d') : '' }}">
                        </div>
                        <div class="col-md-6 col-12">
                            <label class="form-label small fw-bold text-dark">Note:</label>
                            <input type="text" name="note" id="paymentNoteInput" class="form-control" placeholder="Installment / settlement note">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light p-3">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold shadow-sm" id="btnSubmitPayment">
                        <i class="fa-solid fa-check me-1.5"></i> Confirm Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const singleInvoiceActionUrl = @json(route('admin.accounting.invoices.payments.store', $invoice->id));
const allInvoicesActionUrl = @json(route('admin.accounting.customer-ledger.payments.store'));
const singleInvoiceDue = @json((float)$invoice->due_amount);
const customerTotalDue = @json((float)($customerTotalDue ?? $invoice->due_amount));

let currentCalcMode = 'gross';

function togglePaymentScope(scope) {
    const form = document.getElementById('recordPaymentForm');
    const amountInput = document.getElementById('paymentAmountInput');
    const singleBox = document.getElementById('singleInvoiceSummaryBox');
    const allBox = document.getElementById('allInvoicesBreakdownBox');
    const noteInput = document.getElementById('paymentNoteInput');
    const helpText = document.getElementById('paymentAmountHelp');

    if (!form || !amountInput) return;

    if (scope === 'all') {
        form.action = allInvoicesActionUrl;
        amountInput.value = customerTotalDue > 0 ? customerTotalDue.toFixed(2) : '';
        if (singleBox) singleBox.classList.add('d-none');
        if (allBox) allBox.classList.remove('d-none');
        if (noteInput && !noteInput.value) {
            noteInput.value = 'সকল বকেয়া বিল বাবদ এককালীন/কিস্তি জমা';
        }
        if (helpText) {
            helpText.textContent = 'প্রদত্ত অর্থ গ্রাহকের সবচেয়ে পুরোনো বকেয়া বিল হতে পর্যায়ক্রমে স্বয়ংক্রিয়ভাবে বণ্টন হবে।';
        }
    } else {
        form.action = singleInvoiceActionUrl;
        amountInput.value = singleInvoiceDue > 0 ? singleInvoiceDue.toFixed(2) : '';
        if (singleBox) singleBox.classList.remove('d-none');
        if (allBox) allBox.classList.add('d-none');
        if (noteInput && noteInput.value === 'সকল বকেয়া বিল বাবদ এককালীন/কিস্তি জমা') {
            noteInput.value = '';
        }
        if (helpText) {
            helpText.textContent = 'ভ্যাট-ট্যাক্স কর্তনসহ বিলের মোট যে পরিমাণ সমন্বয় বা পরিশোধ হবে।';
        }
    }
    calculateVatTaxDeductions();
}

function openAllDueSettlementModal() {
    const modalEl = document.getElementById('recordInvoicePaymentModal');
    if (!modalEl) return;
    const radioAll = document.getElementById('scopeAllInvoices');
    if (radioAll) {
        radioAll.checked = true;
        togglePaymentScope('all');
    }
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    modal.show();
}

// VAT / Tax Calculator Functions
function toggleVatTaxSection(isChecked) {
    const panel = document.getElementById('vatTaxCalculatorPanel');
    if (!panel) return;
    if (isChecked) {
        panel.classList.remove('d-none');
        calculateVatTaxDeductions();
    } else {
        panel.classList.add('d-none');
        // Reset deduction inputs
        document.getElementById('vatDeductionRate').value = '';
        document.getElementById('vatDeductionAmount').value = '';
        document.getElementById('taxDeductionRate').value = '';
        document.getElementById('taxDeductionAmount').value = '';
        document.getElementById('otherDeductionAmount').value = '';
        document.getElementById('netAmountInput').value = '';
        document.getElementById('deductionChallanNo').value = '';
        document.getElementById('deductionNotes').value = '';
    }
}

function switchCalcMode(mode) {
    currentCalcMode = mode;
    calculateVatTaxDeductions();
}

function setVatRate(rate) {
    const input = document.getElementById('vatDeductionRate');
    if (input) {
        input.value = rate > 0 ? rate : '';
        calculateVatTaxDeductions();
    }
}

function setTaxRate(rate) {
    const input = document.getElementById('taxDeductionRate');
    if (input) {
        input.value = rate > 0 ? rate : '';
        calculateVatTaxDeductions();
    }
}

function onGrossAmountChange() {
    if (currentCalcMode === 'gross') {
        calculateVatTaxDeductions();
    }
}

function onNetChequeAmountChange() {
    const radioNet = document.getElementById('calcModeNet');
    if (radioNet && !radioNet.checked) {
        radioNet.checked = true;
        currentCalcMode = 'net';
    }
    calculateVatTaxDeductions();
}

function onDirectDeductionAmountChange() {
    const grossInput = document.getElementById('paymentAmountInput');
    const vatAmtInput = document.getElementById('vatDeductionAmount');
    const taxAmtInput = document.getElementById('taxDeductionAmount');
    const otherInput = document.getElementById('otherDeductionAmount');
    const netInput = document.getElementById('netAmountInput');
    const vatRateInput = document.getElementById('vatDeductionRate');
    const taxRateInput = document.getElementById('taxDeductionRate');

    const gross = parseFloat(grossInput?.value) || 0;
    const vatAmt = parseFloat(vatAmtInput?.value) || 0;
    const taxAmt = parseFloat(taxAmtInput?.value) || 0;
    const other = parseFloat(otherInput?.value) || 0;

    if (gross > 0) {
        if (vatRateInput) vatRateInput.value = vatAmt > 0 ? ((vatAmt / gross) * 100).toFixed(2) : '';
        if (taxRateInput) taxRateInput.value = taxAmt > 0 ? ((taxAmt / gross) * 100).toFixed(2) : '';
        const net = Math.max(0, gross - (vatAmt + taxAmt + other));
        if (netInput) netInput.value = net.toFixed(2);
    }

    updateCalcDisplays(
        parseFloat(netInput?.value) || 0,
        vatAmt + taxAmt + other,
        gross
    );
}

function calculateVatTaxDeductions() {
    const toggle = document.getElementById('toggleVatTaxDeduction');
    if (!toggle || !toggle.checked) return;

    const grossInput = document.getElementById('paymentAmountInput');
    const netInput = document.getElementById('netAmountInput');
    const vatRateInput = document.getElementById('vatDeductionRate');
    const vatAmtInput = document.getElementById('vatDeductionAmount');
    const taxRateInput = document.getElementById('taxDeductionRate');
    const taxAmtInput = document.getElementById('taxDeductionAmount');
    const otherInput = document.getElementById('otherDeductionAmount');

    const vatRate = parseFloat(vatRateInput?.value) || 0;
    const taxRate = parseFloat(taxRateInput?.value) || 0;
    const other = parseFloat(otherInput?.value) || 0;

    let gross = 0;
    let net = 0;
    let vatAmt = 0;
    let taxAmt = 0;

    if (currentCalcMode === 'gross') {
        gross = parseFloat(grossInput?.value) || 0;
        vatAmt = gross * (vatRate / 100);
        taxAmt = gross * (taxRate / 100);
        net = Math.max(0, gross - (vatAmt + taxAmt + other));

        if (vatAmtInput) vatAmtInput.value = vatAmt > 0 ? vatAmt.toFixed(2) : '';
        if (taxAmtInput) taxAmtInput.value = taxAmt > 0 ? taxAmt.toFixed(2) : '';
        if (netInput) netInput.value = net.toFixed(2);
    } else {
        net = parseFloat(netInput?.value) || 0;
        const totalDeductionPercent = (vatRate + taxRate) / 100;
        if (totalDeductionPercent < 0.999) {
            gross = (net + other) / (1 - totalDeductionPercent);
        } else {
            gross = net + other;
        }
        vatAmt = gross * (vatRate / 100);
        taxAmt = gross * (taxRate / 100);

        if (grossInput) grossInput.value = gross.toFixed(2);
        if (vatAmtInput) vatAmtInput.value = vatAmt > 0 ? vatAmt.toFixed(2) : '';
        if (taxAmtInput) taxAmtInput.value = taxAmt > 0 ? taxAmt.toFixed(2) : '';
    }

    const totalDeductions = vatAmt + taxAmt + other;
    updateCalcDisplays(net, totalDeductions, gross);
}

function updateCalcDisplays(net, deductions, gross) {
    const dNet = document.getElementById('displayCalcNet');
    const dDed = document.getElementById('displayCalcDeductions');
    const dGross = document.getElementById('displayCalcGross');

    if (dNet) dNet.textContent = '৳' + Number(net || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    if (dDed) dDed.textContent = '৳' + Number(deductions || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    if (dGross) dGross.textContent = '৳' + Number(gross || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
</script>
@endif

{{-- Send Invoice Email to Customer Modal (Multiple Recipients Support) --}}
<div class="modal fade d-print-none" id="sendInvoiceEmailModal" tabindex="-1" aria-labelledby="sendInvoiceEmailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form action="{{ route('admin.accounting.invoices.send-email', $invoice->id) }}" method="POST" id="sendInvoiceEmailForm">
                @csrf
                <div class="modal-header bg-success text-white py-3 px-4">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fa-solid fa-paper-plane fs-5"></i>
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
                        <i class="fa-solid fa-circle-info me-2 fs-5 text-info"></i>
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
                                <i class="fa-solid fa-history me-1 text-success"></i> পূর্ববর্তী প্রেরণের ইতিহাস (Dispatch History):
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
                        <i class="fa-solid fa-paper-plane me-1.5"></i> Send Email Now
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

        const emailForm = document.getElementById('sendInvoiceEmailForm');
        if (emailForm) {
            emailForm.addEventListener('submit', function() {
                const btn = document.getElementById('btnSendInvoiceEmail');
                if (btn) {
                    btn.disabled = true;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1.5" role="status" aria-hidden="true"></span> Sending Email...';
                }
            });
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
                        <i class="fa-solid fa-sliders me-2"></i>Invoice & Memo Settings
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    
                    {{-- 1. Live Header Preview --}}
                    <div class="card border rounded-3 p-3 mb-3 bg-light">
                        <span class="small fw-bold text-muted text-uppercase mb-2 d-block">
                            <i class="fa-solid fa-eye me-1 text-primary"></i>Header Live Preview
                        </span>
                        <div class="d-flex align-items-center gap-3.5 p-2.5 bg-white rounded border">
                            <img src="{{ $logoSrc }}" id="previewHeaderLogo" alt="Logo Preview" style="height: 48px; width: 96px; aspect-ratio: 2/1; object-fit: contain; flex-shrink: 0; margin-right: 6px;">
                            <div class="d-flex flex-column justify-content-center" style="line-height: 1.35; padding-left: 2px;">
                                <div class="fw-bold text-primary mb-0" id="previewHeaderTitle" style="font-size: 15.5px;">{{ $settings['business_name'] ?? 'Idea Publication' }}</div>
                                <div class="text-muted small mb-0" id="previewHeaderTagline" style="font-size: 10.5px;">{{ $settings['tagline'] ?? 'Book Publication, Printing & Distribution' }}</div>
                                <div class="text-muted small mt-0.5" id="previewHeaderMeta" style="font-size: 10px;">
                                    <span><i class="fa-solid fa-location-dot me-0.5 text-danger"></i><span id="previewMetaAddr">{{ $settings['address'] ?? 'Dhaka, Bangladesh' }}</span></span>
                                    <span class="mx-1 text-muted">·</span>
                                    <span><i class="fa-solid fa-phone me-0.5 text-primary"></i><span id="previewMetaPhone">{{ $settings['phone'] ?? '018XXXXXXXX' }}</span></span>
                                    <span class="mx-1 text-muted">·</span>
                                    <span><i class="fa-solid fa-envelope me-0.5 text-primary"></i><span id="previewMetaEmail">{{ $settings['email'] ?? 'info@ideaabd.com' }}</span></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 2. Logo Upload & 2:1 Cropper Tool --}}
                    <div class="card border border-primary-subtle rounded-3 p-3 mb-3 bg-primary-subtle bg-opacity-10">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label fw-bold text-primary mb-0">
                                <i class="fa-solid fa-image me-1"></i>Company Logo (2:1 Ratio)
                            </label>
                            <span class="badge bg-primary text-white">2:1 Aspect Ratio</span>
                        </div>
                        
                        <input type="file" id="logoFileInput" class="form-control mb-2" accept="image/*">
                        
                        <div id="cropperContainer" class="d-none">
                            <div class="row g-3 align-items-center">
                                <div class="col-md-7">
                                    <div class="position-relative bg-dark rounded-3 overflow-hidden d-flex align-items-center justify-content-center" 
                                         style="height: 180px; width: 100%; border: 2px dashed #0d6efd; cursor: grab;" id="cropDragArea">
                                        <canvas id="cropCanvas" width="360" height="180" class="w-100 h-100" style="object-fit: contain;"></canvas>
                                    </div>
                                    <div class="d-flex align-items-center gap-2 mt-2">
                                        <i class="fa-solid fa-magnifying-glass-minus text-muted small"></i>
                                        <input type="range" class="form-range" id="cropZoomSlider" min="0.3" max="3.5" step="0.02" value="1">
                                        <i class="fa-solid fa-magnifying-glass-plus text-muted small"></i>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mt-1">
                                        <small class="text-muted"><i class="fa-solid fa-hand me-1"></i>Drag to position, slider to zoom</small>
                                        <button type="button" class="btn btn-sm btn-link text-decoration-none p-0" onclick="resetCrop()">Reset</button>
                                    </div>
                                </div>
                                <div class="col-md-5 text-center">
                                    <span class="small fw-semibold text-muted d-block mb-1">Cropped Preview:</span>
                                    <div class="p-2 border rounded-3 bg-white d-inline-block shadow-xs mb-1">
                                        <img id="cropperPreviewThumb" src="{{ $logoSrc }}" alt="Live Crop Thumb" style="height: 50px; width: 100px; object-fit: contain;">
                                    </div>
                                    <div class="small text-success fw-semibold"><i class="fa-solid fa-circle-check me-1"></i>Ready</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 3. Payment QR Codes --}}
                    <div class="card border border-success-subtle rounded-3 p-3 mb-3 bg-success-subtle bg-opacity-10">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label fw-bold text-success mb-0">
                                <i class="fa-solid fa-qrcode me-1"></i>Payment QR Codes
                            </label>
                            <span class="badge bg-success text-white">Bill & Invoice</span>
                        </div>
                        
                        <div class="row g-3">
                            <div class="col-md-6 border-end">
                                <div class="p-2.5 bg-white rounded-3 border h-100 d-flex flex-column justify-content-between">
                                    <div>
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <span class="small fw-bold text-dark"><i class="fa-solid fa-mobile-screen-button text-primary me-1"></i>bKash / Nagad / Rocket QR</span>
                                        </div>
                                        <input type="file" name="mfs_qr_file" id="mfsQrFileInput" class="form-control form-control-sm mb-2" accept="image/*" onchange="previewQr(this, 'mfsQrPreviewImg', 'mfsQrStatusText')">
                                        <div class="mb-2">
                                            <label class="form-label small fw-semibold text-muted mb-0.5" style="font-size: 11px;">Label Note:</label>
                                            <input type="text" name="mfs_qr_note" class="form-control form-control-sm font-monospace" 
                                                   value="{{ $settings['mfs_qr_note'] ?? 'bkash/nagad/rocket' }}" 
                                                   placeholder="bkash/nagad/rocket">
                                        </div>
                                        @if(!empty($settings['mfs_qr_image']))
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" name="remove_mfs_qr" value="1" id="removeMfsQrCheck">
                                                <label class="form-check-label small text-danger" for="removeMfsQrCheck" style="font-size: 11px;">
                                                    Remove QR
                                                </label>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="text-center pt-2 border-top">
                                        <div class="p-1 border rounded bg-light d-inline-block shadow-2xs">
                                            <img id="mfsQrPreviewImg" 
                                                 src="{{ !empty($settings['mfs_qr_image']) ? \App\Support\SiteSetting::resolveImageUrl($settings['mfs_qr_image']) : asset('images/logo.png') }}" 
                                                 alt="MFS QR Preview" 
                                                 style="width: 50px; height: 50px; object-fit: contain; {{ empty($settings['mfs_qr_image']) ? 'opacity: 0.35; filter: grayscale(1);' : '' }}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="p-2.5 bg-white rounded-3 border h-100 d-flex flex-column justify-content-between">
                                    <div>
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <span class="small fw-bold text-dark"><i class="fa-solid fa-building-columns text-success me-1"></i>Bank Account QR</span>
                                        </div>
                                        <input type="file" name="bank_qr_file" id="bankQrFileInput" class="form-control form-control-sm mb-2" accept="image/*" onchange="previewQr(this, 'bankQrPreviewImg', 'bankQrStatusText')">
                                        <div class="mb-2">
                                            <label class="form-label small fw-semibold text-muted mb-0.5" style="font-size: 11px;">Label Note:</label>
                                            <input type="text" name="bank_qr_note" class="form-control form-control-sm font-monospace" 
                                                   value="{{ $settings['bank_qr_note'] ?? 'bank payment' }}" 
                                                   placeholder="bank payment">
                                        </div>
                                        @if(!empty($settings['bank_qr_image']))
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" name="remove_bank_qr" value="1" id="removeBankQrCheck">
                                                <label class="form-check-label small text-danger" for="removeBankQrCheck" style="font-size: 11px;">
                                                    Remove QR
                                                </label>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="text-center pt-2 border-top">
                                        <div class="p-1 border rounded bg-light d-inline-block shadow-2xs">
                                            <img id="bankQrPreviewImg" 
                                                 src="{{ !empty($settings['bank_qr_image']) ? \App\Support\SiteSetting::resolveImageUrl($settings['bank_qr_image']) : asset('images/logo.png') }}" 
                                                 alt="Bank QR Preview" 
                                                 style="width: 50px; height: 50px; object-fit: contain; {{ empty($settings['bank_qr_image']) ? 'opacity: 0.35; filter: grayscale(1);' : '' }}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 mt-2 pt-2 border-top">
                                <div class="row align-items-center">
                                    <div class="col-md-5">
                                        <label class="form-label small fw-bold text-dark mb-0">
                                            <i class="fa-solid fa-expand me-1 text-success"></i>QR Code Display Size:
                                        </label>
                                    </div>
                                    <div class="col-md-7">
                                        <select name="qr_code_size" id="inputQrCodeSize" class="form-select form-select-sm font-monospace fw-bold">
                                            @foreach(['45px'=>'45px (Compact)', '55px'=>'55px (Small)', '60px'=>'60px (Standard / Recommended)', '70px'=>'70px (Medium)', '80px'=>'80px (Large)', '95px'=>'95px (Extra Large)'] as $qVal => $qLbl)
                                                <option value="{{ $qVal }}" {{ ($qrCodeSize === $qVal) ? 'selected' : '' }}>{{ $qLbl }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 4. Delivery Challan Typography --}}
                    <div class="card border border-primary-subtle rounded-3 p-3 mb-3 bg-primary bg-opacity-10">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label fw-bold text-primary mb-0">
                                <i class="fa-solid fa-truck-ramp-box me-1"></i>Delivery Challan Typography
                            </label>
                            <span class="badge bg-primary text-white">Challan Fonts</span>
                        </div>

                        {{-- Recipient Live Preview Box --}}
                        <div class="p-2.5 bg-white rounded-2 border mb-3 shadow-xs">
                            <div class="small fw-bold text-muted text-uppercase mb-1" style="font-size: 10px;">
                                <i class="fa-solid fa-eye me-1 text-primary"></i>Recipient Typography Live Preview:
                            </div>
                            <div class="p-2 bg-light rounded border" id="previewRecipientBox">
                                <div class="fw-bold text-dark mb-1" style="font-size: 11px;"><i class="fa-solid fa-truck me-1 text-primary"></i>Delivery Destination & Recipient:</div>
                                <div id="previewRecipientName" style="font-size: {{ $recipientNameSize }}; font-weight: bold; color: #0f172a;">Mohammad Abdullah / Rahim Book House</div>
                                <div id="previewRecipientDesig" class="text-muted" style="font-size: {{ $recipientDesigSize }};">Headmaster / Proprietor</div>
                                <div id="previewRecipientOrg" class="text-primary fw-semibold" style="font-size: {{ $recipientOrgSize }};">Idea Academy & Library</div>
                                <div id="previewRecipientAddr" class="text-dark" style="font-size: {{ $recipientAddressSize }};">38 Banglabazar, Dhaka-1100, Bangladesh</div>
                                <div id="previewRecipientPhone" class="text-dark fw-bold font-monospace" style="font-size: {{ $recipientPhoneSize }};">01812-345678, 01712-345678</div>
                            </div>
                        </div>

                        <div class="row g-2.5">
                            <div class="col-md-4 col-sm-6">
                                <label class="form-label small fw-semibold text-dark mb-1">Recipient Name</label>
                                <select name="challan_recipient_name_size" id="inputNameSize" class="form-select form-select-sm font-monospace" onchange="updateRecipientPreview()">
                                    @foreach(['11px'=>'11px', '12px'=>'12px', '13px'=>'13px (Default)', '14px'=>'14px', '15px'=>'15px', '16px'=>'16px', '18px'=>'18px'] as $val => $lbl)
                                        <option value="{{ $val }}" {{ ($recipientNameSize === $val) ? 'selected' : '' }}>{{ $lbl }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4 col-sm-6">
                                <label class="form-label small fw-semibold text-dark mb-1">Phone Number</label>
                                <select name="challan_recipient_phone_size" id="inputPhoneSize" class="form-select form-select-sm font-monospace" onchange="updateRecipientPreview()">
                                    @foreach(['10.5px'=>'10.5px', '11.5px'=>'11.5px', '12px'=>'12px (Default)', '13px'=>'13px', '14px'=>'14px', '15px'=>'15px'] as $val => $lbl)
                                        <option value="{{ $val }}" {{ ($recipientPhoneSize === $val) ? 'selected' : '' }}>{{ $lbl }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4 col-sm-6">
                                <label class="form-label small fw-semibold text-dark mb-1">Address</label>
                                <select name="challan_recipient_address_size" id="inputAddressSize" class="form-select form-select-sm font-monospace" onchange="updateRecipientPreview()">
                                    @foreach(['10px'=>'10px', '11px'=>'11px', '11.5px'=>'11.5px (Default)', '12px'=>'12px', '13px'=>'13px', '14px'=>'14px'] as $val => $lbl)
                                        <option value="{{ $val }}" {{ ($recipientAddressSize === $val) ? 'selected' : '' }}>{{ $lbl }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 col-sm-6">
                                <label class="form-label small fw-semibold text-dark mb-1">Designation / Org</label>
                                <select name="challan_recipient_desig_size" id="inputDesigSize" class="form-select form-select-sm font-monospace" onchange="updateRecipientPreview()">
                                    @foreach(['10px'=>'10px', '11px'=>'11px', '11.5px'=>'11.5px (Default)', '12px'=>'12px', '13px'=>'13px'] as $val => $lbl)
                                        <option value="{{ $val }}" {{ ($recipientDesigSize === $val) ? 'selected' : '' }}>{{ $lbl }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 col-sm-12">
                                <label class="form-label small fw-semibold text-dark mb-1">Signatory Title</label>
                                <input type="text" name="default_creator_designation" id="inputDefaultCreatorDesig" class="form-control form-control-sm" 
                                       value="{{ $settings['default_creator_designation'] ?? '' }}" placeholder="Authorized Signatory / Billing Officer">
                            </div>
                        </div>
                    </div>

                    {{-- 5. Quotation & Tender Defaults --}}
                    <div class="card border border-warning-subtle rounded-3 p-3 mb-3 bg-warning-subtle bg-opacity-15">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label fw-bold text-dark mb-0">
                                <i class="fa-solid fa-file-contract me-1 text-warning"></i>Quotation & Tender Presets
                            </label>
                            <span class="badge bg-warning text-dark">Quotation / Tender</span>
                        </div>
                        
                        <div class="row g-2.5">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-dark mb-1">Quotation Document Title</label>
                                <input type="text" name="quotation_title_bn" class="form-control form-control-sm" 
                                       value="{{ $settings['quotation_title_bn'] ?? 'PRICE QUOTATION' }}" 
                                       placeholder="PRICE QUOTATION">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-dark mb-1">Tender Document Title</label>
                                <input type="text" name="tender_title_bn" class="form-control form-control-sm" 
                                       value="{{ $settings['tender_title_bn'] ?? 'TENDER PROPOSAL' }}" 
                                       placeholder="TENDER PROPOSAL">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-dark mb-1">Default Validity</label>
                                <select name="quotation_default_validity_days" class="form-select form-select-sm">
                                    @php $qValDays = (int)($settings['quotation_default_validity_days'] ?? 30); @endphp
                                    @foreach([7=>'7 Days', 15=>'15 Days', 30=>'30 Days (Default)', 45=>'45 Days', 60=>'60 Days', 90=>'90 Days'] as $dKey => $dLbl)
                                        <option value="{{ $dKey }}" {{ ($qValDays === $dKey) ? 'selected' : '' }}>{{ $dLbl }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-dark mb-1">Digit & Number Format</label>
                                <select name="digit_language" class="form-select form-select-sm">
                                    @php $dLang = $settings['digit_language'] ?? 'bn'; @endphp
                                    <option value="en" {{ ($dLang === 'en') ? 'selected' : '' }}>English Digits (1, 2, 3, ৳)</option>
                                    <option value="bn" {{ ($dLang === 'bn') ? 'selected' : '' }}>Bengali Digits (১, ২, ৩, ৳)</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-dark mb-1">Default Subject Line</label>
                                <input type="text" name="quotation_default_subject" class="form-control form-control-sm" 
                                       value="{{ $settings['quotation_default_subject'] ?? 'Book Publishing, Printing & Supply' }}" 
                                       placeholder="Book Publishing, Printing & Supply">
                            </div>

                            <div class="col-md-12">
                                <label class="form-label small fw-semibold text-dark mb-1">Default Quotation Notes</label>
                                <input type="text" name="quotation_default_notes" class="form-control form-control-sm" 
                                       value="{{ $settings['quotation_default_notes'] ?? '1. VAT not included. 2. Quotation valid for 30 days.' }}" 
                                       placeholder="1. VAT not included. 2. Quotation valid for 30 days.">
                            </div>

                            <div class="col-md-12">
                                <label class="form-label small fw-semibold text-dark mb-1">Default Terms & Conditions</label>
                                <textarea name="quotation_default_terms" class="form-control form-control-sm rounded-2" rows="2" 
                                          placeholder="Enter default quotation terms...">{{ $settings['quotation_default_terms'] ?? "1. Delivery will be provided within scheduled timeline upon purchase order.\n2. Price is adjustable upon changes in specifications." }}</textarea>
                            </div>
                        </div>
                    </div>

                    {{-- 6. Tax & VAT Deduction Defaults (TDS & VDS) --}}
                    <div class="card border border-danger-subtle rounded-3 p-3 mb-3 bg-danger-subtle bg-opacity-10">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label fw-bold text-dark mb-0">
                                <i class="fa-solid fa-percent me-1 text-danger"></i>Tax & VAT Deduction Defaults (TDS & VDS)
                            </label>
                            <span class="badge bg-danger text-white font-monospace">TDS & VDS</span>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-dark mb-1">Default VAT Deduction (VDS %)</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.01" name="default_vat_rate" class="form-control font-monospace fw-bold" 
                                           value="{{ $settings['default_vat_rate'] ?? '7.5' }}" placeholder="7.5">
                                    <span class="input-group-text bg-light fw-bold">%</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-dark mb-1">Default Tax Deduction (TDS %)</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.01" name="default_tax_rate" class="form-control font-monospace fw-bold" 
                                           value="{{ $settings['default_tax_rate'] ?? '5.0' }}" placeholder="5.0">
                                    <span class="input-group-text bg-light fw-bold">%</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-dark mb-1">Custom VAT Presets (%)</label>
                                <input type="text" name="vat_presets" class="form-control form-control-sm font-monospace" 
                                       value="{{ is_array($settings['vat_presets'] ?? null) ? implode(', ', $settings['vat_presets']) : ($settings['vat_presets'] ?? '0, 2.5, 5, 7.5, 10, 15') }}" 
                                       placeholder="0, 2.5, 5, 7.5, 10, 15">
                                <small class="text-muted d-block mt-0.5" style="font-size: 11px;">Comma separated: 0, 2.5, 5, 7.5, 10, 15</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-dark mb-1">Custom Tax Presets (%)</label>
                                <input type="text" name="tax_presets" class="form-control form-control-sm font-monospace" 
                                       value="{{ is_array($settings['tax_presets'] ?? null) ? implode(', ', $settings['tax_presets']) : ($settings['tax_presets'] ?? '0, 2, 3, 5, 7, 10') }}" 
                                       placeholder="0, 2, 3, 5, 7, 10">
                                <small class="text-muted d-block mt-0.5" style="font-size: 11px;">Comma separated: 0, 2, 3, 5, 7, 10</small>
                            </div>
                        </div>
                    </div>

                    {{-- 7. Company Details --}}
                    <div class="card border rounded-3 p-3 mb-2 bg-light">
                        <span class="small fw-bold text-muted text-uppercase mb-2 d-block">
                            <i class="fa-solid fa-building me-1 text-primary"></i>Company Information
                        </span>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Company Name <span class="text-danger">*</span></label>
                                <input type="text" name="business_name" id="inputBusinessName" class="form-control" value="{{ $settings['business_name'] ?? 'Idea Publication' }}" required oninput="updateLivePreview()">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tagline</label>
                                <input type="text" name="tagline" id="inputTagline" class="form-control" value="{{ $settings['tagline'] ?? 'Book Publication, Printing & Distribution' }}" placeholder="Book Publication, Printing..." oninput="updateLivePreview()">
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Address</label>
                                <input type="text" name="address" id="inputAddress" class="form-control" value="{{ $settings['address'] ?? 'Dhaka, Bangladesh' }}" placeholder="e.g. 38 Banglabazar, Dhaka..." oninput="updateLivePreview()">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Phone</label>
                                <input type="text" name="phone" id="inputPhone" class="form-control" value="{{ $settings['phone'] ?? '018XXXXXXXX' }}" placeholder="017XXXXXXXX, 018XXXXXXXX" oninput="updateLivePreview()">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Email</label>
                                <input type="email" name="email" id="inputEmail" class="form-control" value="{{ $settings['email'] ?? 'info@ideaabd.com' }}" placeholder="info@ideaabd.com" oninput="updateLivePreview()">
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-semibold d-flex justify-content-between align-items-center">
                                    <span><i class="fa-solid fa-file-contract text-primary me-1"></i>Default Terms & Conditions (Policy Text)</span>
                                    <small class="text-muted">Auto-loads on new invoices & quotations</small>
                                </label>
                                <textarea name="terms_and_conditions" id="inputTerms" class="form-control rounded-3" rows="3" placeholder="Enter default commercial terms and conditions...">{{ $settings['terms_and_conditions'] ?? "1. Payment is due within 15 days of invoice date via Cash, Bank Transfer, or MFS (bKash/Nagad).\n2. Goods once sold in good condition are non-returnable without prior written consent.\n3. Quotations and price schedules remain valid for 30 days from date of issuance.\n4. All disputes are subject to the exclusive jurisdiction of competent courts in Bangladesh." }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top py-2.5">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-semibold px-4 shadow-sm">
                        <i class="fa-solid fa-save me-1"></i> Save Settings
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
            btn.innerHTML = '<i class="fa-solid fa-check me-1 text-success"></i>Link Copied!';
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
    padding: 5px 8px !important;
    vertical-align: middle;
    line-height: 1.35;
    font-size: 10px;
}

.invoice-footer-compact {
    margin-top: auto;
    min-height: 0.5in;
}

.signature-box {
    margin-top: 24px;
}

.destination-box,
.subject-reference-box {
    box-sizing: border-box !important;
    width: 100% !important;
}

.colon-table td {
    padding: 1.5px 0 !important;
    vertical-align: top;
}

.colon-table .colon-label {
    color: #64748b;
    white-space: nowrap;
    font-size: 11px;
    font-weight: 500;
}

.colon-table .colon-sep {
    width: 14px;
    text-align: center;
    font-weight: 700;
    color: #334155;
    user-select: none;
}

@media (min-width: 768px) {
    .border-end-md {
        border-right: 1px solid #cbd5e1 !important;
    }
    .border-bottom-md-0 {
        border-bottom: 0 !important;
    }
}

@media (max-width: 767.98px) {
    .invoice-page-card {
        padding: 12px 14px !important;
        min-height: auto !important;
        margin-bottom: 1rem;
    }

    .invoice-table {
        min-width: 560px;
    }

    .table-responsive {
        -webkit-overflow-scrolling: touch;
        overflow-x: auto;
        display: block;
        width: 100%;
    }

    .colon-table .colon-label {
        width: 85px !important;
    }

    .invoice-brand-header {
        flex-wrap: wrap;
    }

    .invoice-no-text {
        font-size: 14px !important;
    }

    .signature-box {
        margin-top: 14px;
    }
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

    .col-7, .col-print-7 {
        flex: 0 0 58.333333% !important;
        max-width: 58.333333% !important;
        width: 58.333333% !important;
    }

    .col-5, .col-print-5 {
        flex: 0 0 41.666667% !important;
        max-width: 41.666667% !important;
        width: 41.666667% !important;
    }

    .col-6, .col-print-6 {
        flex: 0 0 50% !important;
        max-width: 50% !important;
        width: 50% !important;
    }

    .col-4, .col-print-4 {
        flex: 0 0 33.333333% !important;
        max-width: 33.333333% !important;
        width: 33.333333% !important;
    }

    .col-3, .col-print-3 {
        flex: 0 0 25% !important;
        max-width: 25% !important;
        width: 25% !important;
    }

    .col-2, .col-print-2 {
        flex: 0 0 16.666667% !important;
        max-width: 16.666667% !important;
        width: 16.666667% !important;
    }

    .col-12, .col-print-12 {
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

    .destination-box,
    .subject-reference-box {
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
        min-width: 0 !important;
    }

    .invoice-table th,
    .invoice-table td {
        padding: 4px 6.5px !important;
        font-size: 9.5px !important;
        line-height: 1.25 !important;
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

    .invoice-summary-container {
        width: 100% !important;
        page-break-inside: avoid !important;
        break-inside: avoid !important;
        margin-bottom: 6px !important;
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
