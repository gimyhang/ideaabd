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
        <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm fw-semibold" onclick="window.print()">
            <i class="fas fa-print me-1.5"></i> Print / PDF
        </button>

        {{-- Send Invoice Link to Customer Email Button --}}
        <button type="button" class="btn btn-success btn-sm rounded-pill px-3 fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#sendInvoiceEmailModal" title="Send digital invoice link to customer email">
            <i class="fas fa-paper-plane me-1.5"></i> Send Email
            @if($invoice->emailed_at)
                <span class="badge bg-white text-success ms-1 px-1.5 py-0.5 rounded-pill" title="Email sent">✓</span>
            @endif
        </button>

        {{-- Copy Customer Public Link --}}
        <button type="button" class="btn btn-outline-info text-dark btn-sm rounded-pill px-3 fw-semibold shadow-sm" onclick="copyCustomerShareLink()" id="btnAdminCopyLink" title="Copy public share link for customer">
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

            {{-- Subject and Tender Reference (for Tender & Quotation) --}}
            @if($invoice->subject || $invoice->reference_no)
                <div class="p-1.5 bg-light rounded-2 border mb-2" style="font-size: 10px;">
                    @if($invoice->reference_no)
                        <div class="text-muted mb-0.5">
                            <strong class="text-dark">Tender / Ref No:</strong> <span class="font-monospace fw-bold text-dark">{{ $invoice->reference_no }}</span>
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

{{-- Send Invoice Email to Customer Modal --}}
<div class="modal fade d-print-none" id="sendInvoiceEmailModal" tabindex="-1" aria-labelledby="sendInvoiceEmailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form action="{{ route('admin.accounting.invoices.send-email', $invoice->id) }}" method="POST">
                @csrf
                <div class="modal-header bg-success text-white py-3">
                    <h5 class="modal-title fw-bold" id="sendInvoiceEmailModalLabel">
                        <i class="fas fa-paper-plane me-2"></i>Send Invoice Link to Customer
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-info py-2 px-3 small d-flex align-items-center mb-3">
                        <i class="fas fa-circle-info me-2 fs-5"></i>
                        <div>
                            The customer will receive an email with a direct link to view their digital invoice and download a PDF copy.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Customer Name</label>
                        <input type="text" class="form-control bg-light" value="{{ $invoice->customer_name ?? '—' }}" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Customer Email Address <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope text-muted"></i></span>
                            <input type="email" name="email" class="form-control" value="{{ $invoice->customer_email }}" placeholder="customer@example.com" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Custom Message (Optional)</label>
                        <textarea name="custom_message" class="form-control" rows="3" placeholder="e.g. Your ordered books have been dispatched via courier..."></textarea>
                    </div>

                    @if($invoice->emailed_at)
                        <div class="text-muted small">
                            <i class="fas fa-history me-1 text-success"></i>Last email sent: <strong>{{ $invoice->emailed_at->format('d M, Y h:i A') }}</strong> ({{ $invoice->emailed_at->diffForHumans() }})
                        </div>
                    @endif
                </div>
                <div class="modal-footer border-top py-2.5">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success fw-semibold px-4 shadow-sm">
                        <i class="fas fa-paper-plane me-1.5"></i> Send Email
                    </button>
                </div>
            </form>
        </div>
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
