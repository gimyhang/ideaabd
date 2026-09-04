@extends('layouts.admin')

@php
    $settings = $invoiceSettings ?? \App\Http\Controllers\Admin\IdeaAccountingController::getInvoiceSettings();
    $bizLogo = $settings['logo'] ?? '/images/logo.png';
    $logoSrc = \App\Support\SiteSetting::resolveImageUrl($bizLogo, 'images/logo.png') ?: asset('images/logo.png');

    $currentType = request('type');
    $currentTypeLabel = match($currentType) {
        'invoice'   => 'Invoices / Bills',
        'challan'   => 'Delivery Challans',
        'quotation' => 'Quotations',
        'tender'    => 'Tender Documents',
        default     => 'All Documents'
    };
    $currentCount = match($currentType) {
        'invoice'   => $stats['total_bills'],
        'challan'   => $stats['total_challans'],
        'quotation' => $stats['total_quotations'],
        'tender'    => $stats['total_tenders'],
        default     => $stats['total_invoices']
    };

    $currentCategory = request('sales_category');
    $currentCategoryLabel = match($currentCategory) {
        'books'          => 'Books (বই)',
        'stationery'     => 'Stationery (স্টেশনারি)',
        'printing_goods' => 'Printing (মুদ্রণ)',
        'other'          => 'Others (অন্যান্য)',
        default          => 'All Categories'
    };
    $currentCategoryCount = match($currentCategory) {
        'books'          => $stats['books_count'],
        'stationery'     => $stats['stationery_count'],
        'printing_goods' => $stats['printing_count'],
        'other'          => $stats['other_count'],
        default          => $stats['total_invoices']
    };
@endphp

@section('title', 'Invoices & Documents')
@section('heading')
    <div class="d-flex align-items-center gap-2 flex-wrap">
        {{-- Filter Document Type Dropdown --}}
        <div class="dropdown">
            <button class="btn btn-white border shadow-2xs dropdown-toggle fw-bold text-dark rounded-pill px-3 py-1.5 fs-6 d-inline-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-file-invoice-dollar text-primary"></i>
                <span>{{ $currentTypeLabel }}</span>
                <span class="badge bg-primary-subtle text-primary border rounded-pill fs-7 px-2.5 py-0.5">{{ number_format($currentCount) }}</span>
            </button>
            <ul class="dropdown-menu shadow-lg rounded-4 border-0 p-2" style="min-width: 270px; z-index: 1060;">
                <li><h6 class="dropdown-header small text-uppercase fw-bold text-muted px-2 py-1">Filter Document Type:</h6></li>
                <li>
                    <a class="dropdown-item rounded-3 py-2 fw-semibold d-flex align-items-center justify-content-between {{ empty($currentType) ? 'active bg-primary text-white' : '' }}" href="{{ route('admin.accounting.invoices.index', array_merge(request()->except('type', 'page'))) }}">
                        <span><i class="fa-solid fa-layer-group me-2 {{ empty($currentType) ? 'text-white' : 'text-primary' }}"></i>All Documents</span>
                        <span class="badge {{ empty($currentType) ? 'bg-white text-primary' : 'bg-light text-dark' }} rounded-pill">{{ $stats['total_invoices'] }}</span>
                    </a>
                </li>
                <li>
                    <a class="dropdown-item rounded-3 py-2 fw-semibold d-flex align-items-center justify-content-between {{ $currentType === 'invoice' ? 'active bg-primary text-white' : '' }}" href="{{ route('admin.accounting.invoices.index', array_merge(request()->except('type', 'page'), ['type' => 'invoice'])) }}">
                        <span><i class="fas fa-receipt me-2 {{ $currentType === 'invoice' ? 'text-white' : 'text-primary' }}"></i>Invoices / Bills</span>
                        <span class="badge {{ $currentType === 'invoice' ? 'bg-white text-primary' : 'bg-light text-dark' }} rounded-pill">{{ $stats['total_bills'] }}</span>
                    </a>
                </li>
                <li>
                    <a class="dropdown-item rounded-3 py-2 fw-semibold d-flex align-items-center justify-content-between {{ $currentType === 'challan' ? 'active bg-primary text-white' : '' }}" href="{{ route('admin.accounting.invoices.index', array_merge(request()->except('type', 'page'), ['type' => 'challan'])) }}">
                        <span><i class="fas fa-truck me-2 {{ $currentType === 'challan' ? 'text-white' : 'text-success' }}"></i>Delivery Challans</span>
                        <span class="badge {{ $currentType === 'challan' ? 'bg-white text-primary' : 'bg-light text-dark' }} rounded-pill">{{ $stats['total_challans'] }}</span>
                    </a>
                </li>
                <li>
                    <a class="dropdown-item rounded-3 py-2 fw-semibold d-flex align-items-center justify-content-between {{ $currentType === 'quotation' ? 'active bg-primary text-white' : '' }}" href="{{ route('admin.accounting.invoices.index', array_merge(request()->except('type', 'page'), ['type' => 'quotation'])) }}">
                        <span><i class="fas fa-file-lines me-2 {{ $currentType === 'quotation' ? 'text-white' : 'text-warning' }}"></i>Quotations</span>
                        <span class="badge {{ $currentType === 'quotation' ? 'bg-white text-primary' : 'bg-light text-dark' }} rounded-pill">{{ $stats['total_quotations'] }}</span>
                    </a>
                </li>
                <li>
                    <a class="dropdown-item rounded-3 py-2 fw-semibold d-flex align-items-center justify-content-between {{ $currentType === 'tender' ? 'active bg-primary text-white' : '' }}" href="{{ route('admin.accounting.invoices.index', array_merge(request()->except('type', 'page'), ['type' => 'tender'])) }}">
                        <span><i class="fas fa-landmark me-2 {{ $currentType === 'tender' ? 'text-white' : 'text-purple' }}" style="color: #6f42c1;"></i>Tender Documents</span>
                        <span class="badge {{ $currentType === 'tender' ? 'bg-white text-primary' : 'bg-light text-dark' }} rounded-pill">{{ $stats['total_tenders'] }}</span>
                    </a>
                </li>
            </ul>
        </div>

        {{-- Sales Category Dropdown (বুকস, স্টেশনারি, প্রিন্টিং...) --}}
        <div class="dropdown">
            <button class="btn btn-white border shadow-2xs dropdown-toggle fw-bold text-dark rounded-pill px-3 py-1.5 fs-6 d-inline-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa-solid fa-tags text-info"></i>
                <span>{{ $currentCategoryLabel }}</span>
                <span class="badge bg-light text-dark border rounded-pill fs-7 px-2 py-0.5">{{ number_format($currentCategoryCount) }}</span>
            </button>
            <ul class="dropdown-menu shadow-lg rounded-4 border-0 p-2" style="min-width: 250px; z-index: 1060;">
                <li><h6 class="dropdown-header small text-uppercase fw-bold text-muted px-2 py-1">বিক্রয় ক্যাটাগরি:</h6></li>
                <li>
                    <a class="dropdown-item rounded-3 py-2 fw-semibold d-flex align-items-center justify-content-between {{ empty($currentCategory) ? 'active bg-primary text-white' : '' }}" href="{{ route('admin.accounting.invoices.index', array_merge(request()->except('sales_category', 'page'))) }}">
                        <span><i class="fa-solid fa-layer-group me-2 {{ empty($currentCategory) ? 'text-white' : 'text-primary' }}"></i>সকল ক্যাটাগরি (All)</span>
                        <span class="badge {{ empty($currentCategory) ? 'bg-white text-primary' : 'bg-light text-dark' }} rounded-pill">{{ $stats['total_invoices'] }}</span>
                    </a>
                </li>
                <li>
                    <a class="dropdown-item rounded-3 py-2 fw-semibold d-flex align-items-center justify-content-between {{ $currentCategory === 'books' ? 'active bg-primary text-white' : '' }}" href="{{ route('admin.accounting.invoices.index', array_merge(request()->except('sales_category', 'page'), ['sales_category' => 'books'])) }}">
                        <span><i class="fa-solid fa-book me-2 {{ $currentCategory === 'books' ? 'text-white' : 'text-primary' }}"></i>বই ও প্রকাশনা (Books)</span>
                        <span class="badge {{ $currentCategory === 'books' ? 'bg-white text-primary' : 'bg-light text-dark' }} rounded-pill">{{ $stats['books_count'] }}</span>
                    </a>
                </li>
                <li>
                    <a class="dropdown-item rounded-3 py-2 fw-semibold d-flex align-items-center justify-content-between {{ $currentCategory === 'stationery' ? 'active bg-primary text-white' : '' }}" href="{{ route('admin.accounting.invoices.index', array_merge(request()->except('sales_category', 'page'), ['sales_category' => 'stationery'])) }}">
                        <span><i class="fa-solid fa-pen-ruler me-2 {{ $currentCategory === 'stationery' ? 'text-white' : 'text-info' }}"></i>স্টেশনারি (Stationery)</span>
                        <span class="badge {{ $currentCategory === 'stationery' ? 'bg-white text-primary' : 'bg-light text-dark' }} rounded-pill">{{ $stats['stationery_count'] }}</span>
                    </a>
                </li>
                <li>
                    <a class="dropdown-item rounded-3 py-2 fw-semibold d-flex align-items-center justify-content-between {{ $currentCategory === 'printing_goods' ? 'active bg-primary text-white' : '' }}" href="{{ route('admin.accounting.invoices.index', array_merge(request()->except('sales_category', 'page'), ['sales_category' => 'printing_goods'])) }}">
                        <span><i class="fa-solid fa-print me-2 {{ $currentCategory === 'printing_goods' ? 'text-white' : 'text-warning' }}"></i>মুদ্রণ সামগ্রী (Printing)</span>
                        <span class="badge {{ $currentCategory === 'printing_goods' ? 'bg-white text-primary' : 'bg-light text-dark' }} rounded-pill">{{ $stats['printing_count'] }}</span>
                    </a>
                </li>
                <li>
                    <a class="dropdown-item rounded-3 py-2 fw-semibold d-flex align-items-center justify-content-between {{ $currentCategory === 'other' ? 'active bg-primary text-white' : '' }}" href="{{ route('admin.accounting.invoices.index', array_merge(request()->except('sales_category', 'page'), ['sales_category' => 'other'])) }}">
                        <span><i class="fa-solid fa-cart-plus me-2 {{ $currentCategory === 'other' ? 'text-white' : 'text-secondary' }}"></i>অন্যান্য পণ্য (Others)</span>
                        <span class="badge {{ $currentCategory === 'other' ? 'bg-white text-primary' : 'bg-light text-dark' }} rounded-pill">{{ $stats['other_count'] }}</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.accounting.index') }}">Accounting</a></li>
    <li class="breadcrumb-item active" aria-current="page">Invoices</li>
@endsection

@section('actions')
    <div class="d-flex flex-wrap align-items-center gap-2">
        <div class="dropdown">
            <button class="btn btn-primary btn-sm rounded-pill px-3.5 fw-semibold shadow-xs dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-plus-circle me-1"></i> New Document
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow rounded-3 border-0 p-2" style="min-width: 220px;">
                <li><h6 class="dropdown-header small text-uppercase fw-bold text-muted px-2 py-1">Document Type:</h6></li>
                <li>
                    <a class="dropdown-item rounded-2 py-2 fw-semibold d-flex align-items-center gap-2" href="{{ route('admin.accounting.invoices.create', ['type' => 'invoice', 'sales_category' => 'books']) }}">
                        <i class="fas fa-receipt text-primary"></i> Sales Invoice / Bill
                    </a>
                </li>
                <li>
                    <a class="dropdown-item rounded-2 py-2 fw-semibold d-flex align-items-center gap-2" href="{{ route('admin.accounting.invoices.create', ['type' => 'challan', 'sales_category' => 'books']) }}">
                        <i class="fas fa-truck text-success"></i> Delivery Challan
                    </a>
                </li>
                <li>
                    <a class="dropdown-item rounded-2 py-2 fw-semibold d-flex align-items-center gap-2" href="{{ route('admin.accounting.invoices.create', ['type' => 'quotation']) }}">
                        <i class="fas fa-file-lines text-warning"></i> Price Quotation
                    </a>
                </li>
                <li>
                    <a class="dropdown-item rounded-2 py-2 fw-semibold d-flex align-items-center gap-2" href="{{ route('admin.accounting.invoices.create', ['type' => 'tender']) }}">
                        <i class="fas fa-landmark" style="color: #6f42c1;"></i> Tender Proposal
                    </a>
                </li>
            </ul>
        </div>
        <a href="{{ route('admin.accounting.customer-ledger.index') }}" class="btn btn-outline-info text-dark btn-sm rounded-pill px-3 fw-semibold shadow-xs" title="গ্রাহকদের খতিয়ান ও বকেয়া জের">
            <i class="fas fa-book-bookmark me-1 text-primary"></i> Customer Ledgers
        </a>
        <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-semibold shadow-xs" data-bs-toggle="modal" data-bs-target="#invoiceSettingsModal" title="Design & Typography Settings">
            <i class="fas fa-palette me-1 text-primary"></i> Design Settings
        </button>
        <a href="{{ route('subadmin.bills.index') }}" class="btn btn-outline-success btn-sm rounded-pill px-3 fw-semibold shadow-xs" title="সেলারদের সকল বিক্রয় ও ডেলিভারি চালান চেক করুন">
            <i class="fas fa-store me-1"></i> Seller Bills & Challans
        </a>
        <a href="{{ route('admin.accounting.index') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-semibold shadow-xs">
            <i class="fas fa-scale-balanced me-1"></i> Cashbook
        </a>
    </div>
@endsection

@section('content')

{{-- Summary Stat Cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-primary">
            <span class="text-muted small fw-semibold">Total Documents</span>
            <h3 class="fw-bold mb-0 text-primary">{{ number_format($stats['total_invoices']) }}</h3>
            <div class="text-muted small mt-1" style="font-size: 11.5px;">
                Bills: {{ number_format($stats['total_bills']) }} | Challans: {{ number_format($stats['total_challans']) }}
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4" style="border-left-color: #6f42c1 !important;">
            <span class="text-muted small fw-semibold">Quotations & Tenders</span>
            <h3 class="fw-bold mb-0" style="color: #6f42c1;">{{ number_format($stats['total_quotations'] + $stats['total_tenders']) }}</h3>
            <div class="text-muted small mt-1" style="font-size: 11.5px;">
                Quotations: {{ number_format($stats['total_quotations']) }} | Tenders: {{ number_format($stats['total_tenders']) }}
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-success">
            <span class="text-muted small fw-semibold">Total Collected / Paid</span>
            <h3 class="fw-bold mb-0 text-success">৳{{ number_format($stats['total_paid'], 2) }}</h3>
            <div class="text-muted small mt-1" style="font-size: 11.5px;">Total Billed: ৳{{ number_format($stats['total_amount'], 2) }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-danger">
            <span class="text-muted small fw-semibold">Total Due Balance</span>
            <h3 class="fw-bold mb-0 text-danger">৳{{ number_format($stats['total_due'], 2) }}</h3>
            <div class="text-muted small mt-1" style="font-size: 11.5px;">Receivable from bills</div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-3">
        <form action="{{ route('admin.accounting.invoices.index') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-md-3">
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="fas fa-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Document # / Customer / Org..." value="{{ $search }}">
                </div>
            </div>
            <div class="col-md-2">
                <select name="type" class="form-select" onchange="this.form.submit()">
                    <option value="">All Document Types</option>
                    <option value="invoice" @selected($type === 'invoice')>Bill / Cash Memo</option>
                    <option value="challan" @selected($type === 'challan')>Delivery Challan</option>
                    <option value="quotation" @selected($type === 'quotation')>Quotation / Proforma</option>
                    <option value="tender" @selected($type === 'tender')>Tender Document</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="sales_category" class="form-select" onchange="this.form.submit()">
                    <option value="">All Categories (সকল পণ্য)</option>
                    <option value="books" @selected($salesCategory === 'books')>Books (বই)</option>
                    <option value="stationery" @selected($salesCategory === 'stationery')>Stationery (স্টেশনারি)</option>
                    <option value="printing_goods" @selected($salesCategory === 'printing_goods')>Printing (মুদ্রণ)</option>
                    <option value="other" @selected($salesCategory === 'other')>Others (অন্যান্য)</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="payment_status" class="form-select" onchange="this.form.submit()">
                    <option value="">All Payment Status</option>
                    <option value="paid" @selected($status === 'paid')>Paid (পরিশোধিত)</option>
                    <option value="partial" @selected($status === 'partial')>Partially Paid (আংশিক)</option>
                    <option value="unpaid" @selected($status === 'unpaid')>Unpaid / Due (বকেয়া)</option>
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}" title="Date">
            </div>
            <div class="col-md-1 d-flex gap-1">
                <button type="submit" class="btn btn-primary w-100" title="Filter"><i class="fas fa-filter"></i></button>
                @if(request()->hasAny(['search', 'type', 'sales_category', 'payment_status', 'date_from', 'date_to']))
                    <a href="{{ route('admin.accounting.invoices.index') }}" class="btn btn-light border" title="Reset"><i class="fas fa-rotate-left"></i></a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- Invoices Table --}}
<div class="adm-card shadow-sm rounded-4 overflow-hidden mb-4 bg-white">
    @if ($invoices->isEmpty())
        <div class="empty-state py-5 text-center">
            <i class="fas fa-file-invoice fs-1 text-muted opacity-50 mb-3"></i>
            <h5 class="fw-bold text-muted">No Documents Found</h5>
            <p class="text-muted small">Create a new bill, delivery challan, quotation or tender document using the button above.</p>
        </div>
    @else
        <div class="table-responsive rounded-bottom-4">
            <table class="table adm-table align-middle mb-0" style="min-width: 1080px;">
                    <tr>
                        <th class="ps-3 py-3" style="width: 170px;">Document #</th>
                        <th class="py-3" style="width: 120px;">Type</th>
                        <th class="py-3" style="width: 120px;">Date</th>
                        <th class="py-3" style="min-width: 220px;">Client & Ledger</th>
                        <th class="py-3" style="width: 100px;">Items</th>
                        <th class="py-3 text-end" style="width: 130px;">Grand Total</th>
                        <th class="py-3 text-end" style="width: 120px;">Paid</th>
                        <th class="py-3 text-end" style="width: 120px;">Due</th>
                        <th class="py-3 text-center" style="width: 130px;">Due Date / Status</th>
                        <th class="text-center pe-3 py-3" style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($invoices as $inv)
                        <tr class="{{ $inv->is_overdue ? 'table-danger-subtle' : '' }}">
                            <td class="ps-3 fw-bold text-primary font-monospace">
                                <a href="{{ route('admin.accounting.invoices.show', $inv->id) }}" class="text-decoration-none text-primary">
                                    {{ $inv->invoice_no }}
                                </a>
                                @if($inv->reference_no)
                                    <div class="text-muted small fw-normal" style="font-size: 11px;">Ref: {{ $inv->reference_no }}</div>
                                @endif
                                @php
                                    $catBadge = $inv->category_badge;
                                @endphp
                                <div class="mt-1">
                                    <span class="badge border {{ $catBadge['bg'] }} px-2 py-0.5 fw-semibold" style="font-size: 10px;">
                                        {{ $catBadge['label'] }}
                                    </span>
                                </div>
                            </td>
                            <td>
                                @if($inv->type === 'tender')
                                    <span class="badge border px-2.5 py-1 rounded-pill" style="background-color: #f3e8ff; color: #7e22ce; border-color: #d8b4fe;">
                                        <i class="fas fa-landmark me-1"></i>Tender
                                    </span>
                                @elseif($inv->type === 'quotation')
                                    <span class="badge border px-2.5 py-1 rounded-pill" style="background-color: #fef3c7; color: #b45309; border-color: #fcd34d;">
                                        <i class="fas fa-file-lines me-1"></i>Quotation
                                    </span>
                                @elseif($inv->type === 'challan')
                                    <span class="badge bg-info-subtle text-dark border border-info-subtle px-2.5 py-1 rounded-pill">
                                        <i class="fas fa-truck me-1"></i>Challan
                                    </span>
                                @else
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 rounded-pill">
                                        <i class="fas fa-receipt me-1"></i>Bill / Memo
                                    </span>
                                @endif
                            </td>
                            <td class="text-muted small">
                                <div class="fw-medium text-dark">{{ $inv->invoice_date ? $inv->invoice_date->format('d M, Y') : '—' }}</div>
                                @if($inv->valid_until)
                                    <div class="text-danger" style="font-size: 10.5px;">Valid: {{ $inv->valid_until->format('d M, Y') }}</div>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex align-items-start justify-content-between">
                                    <div>
                                        @if($inv->customer_org)
                                            <div class="fw-bold text-primary">
                                                <i class="fas fa-building me-1 text-primary opacity-75" style="font-size: 11px;"></i>{{ $inv->customer_org }}
                                            </div>
                                            <div class="text-dark small">
                                                <i class="fas fa-user me-1 text-muted" style="font-size: 10px;"></i>{{ $inv->customer_name }}
                                            </div>
                                        @else
                                            <div class="fw-bold text-dark">
                                                <i class="fas fa-user me-1 text-primary opacity-75" style="font-size: 11px;"></i>{{ $inv->customer_name }}
                                            </div>
                                        @endif

                                        @if($inv->customer_phone)
                                            <div class="text-muted small font-monospace" style="font-size: 11px;"><i class="fas fa-phone me-1 text-success"></i>{{ $inv->customer_phone }}</div>
                                        @endif

                                        @if($inv->subject)
                                            <div class="text-primary-emphasis fw-medium mt-1 d-flex align-items-center gap-1.5" style="font-size: 11px; max-width: 280px;" title="বিষয়: {{ $inv->subject }}">
                                                <i class="fas fa-heading text-primary opacity-75" style="font-size: 10px;"></i>
                                                <span class="text-truncate">{{ $inv->subject }}</span>
                                            </div>
                                        @endif
                                    </div>
                                    <a href="{{ route('admin.accounting.customer-ledger.index', ['customer_name' => $inv->customer_name, 'customer_phone' => $inv->customer_phone]) }}" class="badge bg-light text-primary border text-decoration-none px-2 py-1 ms-1" title="গ্রাহকের সম্পূর্ণ খতিয়ান দেখুন">
                                        <i class="fas fa-book-bookmark me-0.5"></i>লেজার
                                    </a>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ count($inv->items ?? []) }} items</span>
                            </td>
                            <td class="fw-bold text-dark font-monospace text-end">৳{{ number_format($inv->grand_total, 2) }}</td>
                            <td class="fw-bold text-success font-monospace text-end">
                                @if(in_array($inv->type, ['invoice', 'challan']))
                                    ৳{{ number_format($inv->paid_amount, 2) }}
                                    @if($inv->payments->count() > 1)
                                        <div class="text-muted" style="font-size: 10px;">({{ $inv->payments->count() }}টি কিস্তিতে)</div>
                                    @endif
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td class="fw-bold font-monospace text-end {{ $inv->due_amount > 0 ? 'text-danger' : 'text-muted' }}">
                                @if(in_array($inv->type, ['invoice', 'challan']))
                                    ৳{{ number_format($inv->due_amount, 2) }}
                                </td>
                                <td class="text-center">
                                    @if(in_array($inv->type, ['quotation', 'tender']))
                                        <span class="badge bg-light text-dark border px-2 py-1 rounded-pill">
                                            Proposed
                                        </span>
                                    @elseif($inv->payment_status === 'paid')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 rounded-pill">
                                            <i class="fas fa-check-circle me-1"></i>Paid
                                        </span>
                                    @elseif($inv->payment_status === 'partial')
                                        <span class="badge bg-warning-subtle text-dark border border-warning-subtle px-2.5 py-1 rounded-pill">
                                            Partial
                                        </span>
                                        @if($inv->due_date)
                                            <div class="small mt-1 {{ $inv->is_overdue ? 'text-danger fw-bold' : 'text-muted' }}" style="font-size: 10px;">
                                                <i class="fas fa-calendar-day me-0.5"></i>{{ $inv->due_date->format('d M') }}
                                                @if($inv->is_overdue)<span class="badge bg-danger text-white px-1 py-0 ms-0.5">Overdue</span>@endif
                                            </div>
                                        @endif
                                    @else
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1 rounded-pill">
                                            Due
                                        </span>
                                        @if($inv->due_date)
                                            <div class="small mt-1 {{ $inv->is_overdue ? 'text-danger fw-bold' : 'text-muted' }}" style="font-size: 10px;">
                                                <i class="fas fa-calendar-day me-0.5"></i>{{ $inv->due_date->format('d M') }}
                                                @if($inv->is_overdue)<span class="badge bg-danger text-white px-1 py-0 ms-0.5">Overdue</span>@endif
                                            </div>
                                        @endif
                                    @endif
                                </td>
                            @else
                                <td>—</td>
                                <td>—</td>
                            @endif
                            <td class="text-center pe-3">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.accounting.invoices.show', $inv->id) }}" class="btn btn-outline-primary" title="View & Print">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.accounting.invoices.edit', $inv->id) }}" class="btn btn-outline-warning text-dark" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.accounting.invoices.destroy', $inv->id) }}" method="POST" class="d-inline" data-confirm="আপনি কি নিশ্চিত যে এই ইনভয়েসটি (#{{ $inv->invoice_number }}) মুছে ফেলতে চান?" data-confirm-title="ইনভয়েস ডিলিট">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Delete">
                                            <i class="fas fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($invoices->hasPages())
            <div class="p-3 border-top d-flex justify-content-end bg-white">
                {{ $invoices->links() }}
            </div>
        @endif
    @endif
</div>

{{-- Invoice & Memo Header Settings / Design Modal with 2:1 Cropper --}}
<div class="modal fade" id="invoiceSettingsModal" tabindex="-1" aria-labelledby="invoiceSettingsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow">
            <form action="{{ route('admin.accounting.settings.update') }}" method="POST" enctype="multipart/form-data" id="indexSettingsForm">
                @csrf
                <input type="hidden" name="logo_base64" id="indexLogoCroppedBase64">

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
                        <div class="d-flex align-items-center gap-3 p-2 bg-white rounded border">
                            <img src="{{ $logoSrc }}" id="indexPreviewHeaderLogo" alt="Logo Preview" style="height: 55px; width: 110px; aspect-ratio: 2/1; object-fit: contain;">
                            <div>
                                <h4 class="fw-bold text-primary mb-0" id="indexPreviewHeaderTitle">{{ $settings['business_name'] ?? 'Idea Publication' }}</h4>
                                <p class="text-muted small mb-0" id="indexPreviewHeaderTagline">{{ $settings['tagline'] ?? 'Book Publication, Printing & Distribution' }}</p>
                                <div class="text-muted small mt-0.5" id="indexPreviewHeaderMeta" style="font-size: 11.5px;">
                                    {{ $settings['address'] ?? 'Dhaka, Bangladesh' }} · Phone: {{ $settings['phone'] ?? '018XXXXXXXX' }} · Email: {{ $settings['email'] ?? 'info@ideaabd.com' }}
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
                        
                        <input type="file" id="indexLogoFileInput" class="form-control mb-3" accept="image/*">
                        
                        <div id="indexCropperContainer" class="d-none">
                            <div class="row g-3 align-items-center">
                                <div class="col-md-7">
                                    <div class="position-relative bg-dark rounded-3 overflow-hidden d-flex align-items-center justify-content-center" 
                                         style="height: 180px; width: 100%; border: 2px dashed #0d6efd; cursor: grab;" id="indexCropDragArea">
                                        <canvas id="indexCropCanvas" width="360" height="180" class="w-100 h-100" style="object-fit: contain;"></canvas>
                                    </div>
                                    <div class="d-flex align-items-center gap-2 mt-2">
                                        <i class="fas fa-magnifying-glass-minus text-muted small"></i>
                                        <input type="range" class="form-range" id="indexCropZoomSlider" min="0.3" max="3.5" step="0.02" value="1">
                                        <i class="fas fa-magnifying-glass-plus text-muted small"></i>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="indexResetCrop()" title="Reset">
                                            <i class="fas fa-rotate-left"></i>
                                        </button>
                                    </div>
                                    <small class="text-muted d-block mt-1" style="font-size: 11px;">
                                        <i class="fas fa-hand me-1"></i>Drag to reposition, use slider to zoom.
                                    </small>
                                </div>
                                <div class="col-md-5 text-center">
                                    <span class="small fw-semibold text-muted d-block mb-1">Crop Preview (2:1 Wide):</span>
                                    <div class="p-2 bg-white rounded border d-inline-block shadow-xs">
                                        <img id="indexCroppedResultThumb" src="{{ $logoSrc }}" style="height: 60px; width: 120px; aspect-ratio: 2/1; object-fit: contain;" class="rounded">
                                    </div>
                                    <div class="text-success small fw-bold mt-1.5"><i class="fas fa-check-circle me-1"></i>2:1 Aspect Ratio Ready</div>
                                </div>
                            </div>
                        </div>
                    </div>

                                        {{-- Dual Payment QR Codes (MFS & Bank Payment QR) --}}
                    <div class="card border border-success-subtle rounded-3 p-3 mb-3 bg-success-subtle bg-opacity-10">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label fw-bold text-success mb-0">
                                <i class="fa-solid fa-qrcode me-1"></i> পেমেন্ট কিউআর কোডসমূহ (MFS & Bank Payment QR)
                            </label>
                            <span class="badge bg-success text-white">শুধুমাত্র বিলে থাকবে</span>
                        </div>
                        <p class="small text-muted mb-3" style="font-size: 11px;">
                            বিলের ফুটার কলামে প্রদর্শনের জন্য বিকাশ/নগদ/রকেট কিউআর এবং ব্যাংক পেমেন্ট কিউআর ছবি ও বিবরণ যুক্ত করুন:
                        </p>
                        
                        <div class="row g-3">
                            {{-- 1. MFS (bKash / Nagad / Rocket) QR --}}
                            <div class="col-md-6 border-end">
                                <div class="p-2.5 bg-white rounded-3 border h-100 d-flex flex-column justify-content-between">
                                    <div>
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <span class="small fw-bold text-dark"><i class="fas fa-mobile-screen-button text-primary me-1"></i>১. bKash / Nagad / Rocket কিউআর</span>
                                        </div>
                                        <input type="file" name="mfs_qr_file" id="mfsQrFileInput" class="form-control form-control-sm mb-2" accept="image/*" onchange="previewQr(this, 'mfsQrPreviewImg', 'mfsQrStatusText')">
                                        <div class="mb-2">
                                            <label class="form-label small fw-semibold text-muted mb-0.5" style="font-size: 10.5px;">কিউআর এর নিচের লেখা (এক লাইনে):</label>
                                            <input type="text" name="mfs_qr_note" class="form-control form-control-sm font-monospace" 
                                                   value="{{ $settings['mfs_qr_note'] ?? 'bkash/nagad/roket' }}" 
                                                   placeholder="bkash/nagad/roket">
                                        </div>
                                        @if(!empty($settings['mfs_qr_image']))
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" name="remove_mfs_qr" value="1" id="removeMfsQrCheck">
                                                <label class="form-check-label small text-danger" for="removeMfsQrCheck" style="font-size: 11px;">
                                                    বর্তমান MFS কিউআর মুছুন
                                                </label>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="text-center pt-2 border-top">
                                        <div class="p-1 border rounded bg-light d-inline-block shadow-2xs">
                                            <img id="mfsQrPreviewImg" 
                                                 src="{{ !empty($settings['mfs_qr_image']) ? \App\Support\SiteSetting::resolveImageUrl($settings['mfs_qr_image']) : asset('images/logo.png') }}" 
                                                 alt="MFS QR Preview" 
                                                 style="width: 55px; height: 55px; object-fit: contain; {{ empty($settings['mfs_qr_image']) ? 'opacity: 0.35; filter: grayscale(1);' : '' }}">
                                        </div>
                                        <div class="small text-muted mt-0.5" id="mfsQrStatusText" style="font-size: 10px;">
                                            {{ !empty($settings['mfs_qr_image']) ? 'MFS কিউআর সক্রিয়' : 'ছবি আপলোড করুন' }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- 2. Bank Payment QR --}}
                            <div class="col-md-6">
                                <div class="p-2.5 bg-white rounded-3 border h-100 d-flex flex-column justify-content-between">
                                    <div>
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <span class="small fw-bold text-dark"><i class="fa-solid fa-building-columns text-success me-1"></i>২. Bank Payment কিউআর</span>
                                        </div>
                                        <input type="file" name="bank_qr_file" id="bankQrFileInput" class="form-control form-control-sm mb-2" accept="image/*" onchange="previewQr(this, 'bankQrPreviewImg', 'bankQrStatusText')">
                                        <div class="mb-2">
                                            <label class="form-label small fw-semibold text-muted mb-0.5" style="font-size: 10.5px;">কিউআর এর নিচের লেখা (এক লাইনে):</label>
                                            <input type="text" name="bank_qr_note" class="form-control form-control-sm font-monospace" 
                                                   value="{{ $settings['bank_qr_note'] ?? 'bank payment' }}" 
                                                   placeholder="bank payment">
                                        </div>
                                        @if(!empty($settings['bank_qr_image']))
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" name="remove_bank_qr" value="1" id="removeBankQrCheck">
                                                <label class="form-check-label small text-danger" for="removeBankQrCheck" style="font-size: 11px;">
                                                    বর্তমান ব্যাংক কিউআর মুছুন
                                                </label>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="text-center pt-2 border-top">
                                        <div class="p-1 border rounded bg-light d-inline-block shadow-2xs">
                                            <img id="bankQrPreviewImg" 
                                                 src="{{ !empty($settings['bank_qr_image']) ? \App\Support\SiteSetting::resolveImageUrl($settings['bank_qr_image']) : asset('images/logo.png') }}" 
                                                 alt="Bank QR Preview" 
                                                 style="width: 55px; height: 55px; object-fit: contain; {{ empty($settings['bank_qr_image']) ? 'opacity: 0.35; filter: grayscale(1);' : '' }}">
                                        </div>
                                        <div class="small text-muted mt-0.5" id="bankQrStatusText" style="font-size: 10px;">
                                            {{ !empty($settings['bank_qr_image']) ? 'ব্যাংক কিউআর সক্রিয়' : 'ছবি আপলোড করুন' }}
                                        </div>
                                    </div>
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
                            চালানের <strong>Delivery Destination & Recipient:</strong> সেকশনে প্রাপকের নাম, মোবাইল নম্বর ও ঠিকানার ফন্ট সাইজ নিয়ন্ত্রণ করুন।
                        </p>

                        <div class="row g-2.5">
                            <div class="col-md-4 col-sm-6">
                                <label class="form-label small fw-semibold text-dark mb-1">
                                    প্রাপকের নাম সাইজ (Name)
                                </label>
                                <select name="challan_recipient_name_size" class="form-select form-select-sm">
                                    @php $recNameSize = $settings['challan_recipient_name_size'] ?? '13px'; @endphp
                                    @foreach(['11px'=>'ছোট (11px)', '12px'=>'স্বাভাবিক (12px)', '13px'=>'মাঝারি (13px)', '14px'=>'বড় (14px)', '15px'=>'অনেক বড় (15px)', '16px'=>'অতিরিক্ত বড় (16px)', '18px'=>'বিশাল (18px)'] as $val => $lbl)
                                        <option value="{{ $val }}" {{ ($recNameSize === $val) ? 'selected' : '' }}>{{ $lbl }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4 col-sm-6">
                                <label class="form-label small fw-semibold text-dark mb-1">
                                    মোবাইল নম্বর সাইজ (Mobile)
                                </label>
                                <select name="challan_recipient_phone_size" class="form-select form-select-sm">
                                    @php $recPhoneSize = $settings['challan_recipient_phone_size'] ?? '12px'; @endphp
                                    @foreach(['10.5px'=>'ছোট (10.5px)', '11.5px'=>'স্বাভাবিক (11.5px)', '12px'=>'মাঝারি (12px)', '13px'=>'বড় (13px)', '14px'=>'অনেক বড় (14px)', '15px'=>'অতিরিক্ত বড় (15px)'] as $val => $lbl)
                                        <option value="{{ $val }}" {{ ($recPhoneSize === $val) ? 'selected' : '' }}>{{ $lbl }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4 col-sm-6">
                                <label class="form-label small fw-semibold text-dark mb-1">
                                    ঠিকানা সাইজ (Address)
                                </label>
                                <select name="challan_recipient_address_size" class="form-select form-select-sm">
                                    @php $recAddrSize = $settings['challan_recipient_address_size'] ?? '11.5px'; @endphp
                                    @foreach(['10px'=>'ছোট (10px)', '11px'=>'স্বাভাবিক (11px)', '11.5px'=>'মাঝারি (11.5px)', '12px'=>'বড় (12px)', '13px'=>'অনেক বড় (13px)', '14px'=>'অতিরিক্ত বড় (14px)'] as $val => $lbl)
                                        <option value="{{ $val }}" {{ ($recAddrSize === $val) ? 'selected' : '' }}>{{ $lbl }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 col-sm-6">
                                <label class="form-label small fw-semibold text-dark mb-1">
                                    পদবি ও প্রতিষ্ঠান সাইজ (Designation/Org)
                                </label>
                                <select name="challan_recipient_desig_size" class="form-select form-select-sm">
                                    @php $recDesigSize = $settings['challan_recipient_desig_size'] ?? '11.5px'; @endphp
                                    @foreach(['10px'=>'ছোট (10px)', '11px'=>'স্বাভাবিক (11px)', '11.5px'=>'মাঝারি (11.5px)', '12px'=>'বড় (12px)', '13px'=>'অনেক বড় (13px)'] as $val => $lbl)
                                        <option value="{{ $val }}" {{ ($recDesigSize === $val) ? 'selected' : '' }}>{{ $lbl }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 col-sm-12">
                                <label class="form-label small fw-semibold text-dark mb-1">
                                    স্বাক্ষরকারীর ডিফল্ট পদবি (Signatory Title)
                                </label>
                                <input type="text" name="default_creator_designation" class="form-control form-control-sm" 
                                       value="{{ $settings['default_creator_designation'] ?? '' }}" placeholder="যেমন: Authorized Signatory / Billing Officer">
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Company / Imprint Name <span class="text-danger">*</span></label>
                            <input type="text" name="business_name" id="indexInputBusinessName" class="form-control" value="{{ $settings['business_name'] ?? 'Idea Publication' }}" required oninput="updateIndexLivePreview()">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tagline / Slogan</label>
                            <input type="text" name="tagline" id="indexInputTagline" class="form-control" value="{{ $settings['tagline'] ?? 'Book Publication, Printing & Distribution' }}" placeholder="Book Publication, Printing..." oninput="updateIndexLivePreview()">
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Full Official Address</label>
                            <input type="text" name="address" id="indexInputAddress" class="form-control" value="{{ $settings['address'] ?? 'Dhaka, Bangladesh' }}" placeholder="e.g. 38 Banglabazar, Dhaka..." oninput="updateIndexLivePreview()">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Official Phone Number</label>
                            <input type="text" name="phone" id="indexInputPhone" class="form-control" value="{{ $settings['phone'] ?? '018XXXXXXXX' }}" placeholder="017XXXXXXXX, 018XXXXXXXX" oninput="updateIndexLivePreview()">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Official Email Address</label>
                            <input type="email" name="email" id="indexInputEmail" class="form-control" value="{{ $settings['email'] ?? 'info@ideaabd.com' }}" placeholder="info@ideaabd.com" oninput="updateIndexLivePreview()">
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-semibold d-flex justify-content-between align-items-center">
                                <span><i class="fa-solid fa-file-contract text-primary me-1"></i>Default Terms & Conditions (Policy Text)</span>
                                <small class="text-muted">Auto-loads on new invoices & quotations</small>
                            </label>
                            <textarea name="terms_and_conditions" id="indexInputTerms" class="form-control rounded-3" rows="4" placeholder="Enter default commercial terms and conditions...">{{ $settings['terms_and_conditions'] ?? "1. Payment is due within 15 days of invoice date via Cash, Bank Transfer, or MFS (bKash/Nagad).\n2. Goods once sold in good condition are non-returnable without prior written consent.\n3. Quotations and price schedules remain valid for 30 days from date of issuance.\n4. All disputes are subject to the exclusive jurisdiction of competent courts in Bangladesh." }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top py-2.5 d-flex justify-content-between">
                    <a href="{{ route('admin.payments.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                        <i class="fa-solid fa-credit-card me-1 text-primary"></i> Payment Gateways API
                    </a>
                    <div>
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary fw-semibold px-4 shadow-sm">
                            <i class="fas fa-save me-1"></i> Save Design & Settings
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function updateIndexLivePreview() {
    const name = document.getElementById('indexInputBusinessName')?.value || 'Idea Publication';
    const tag = document.getElementById('indexInputTagline')?.value || '';
    const addr = document.getElementById('indexInputAddress')?.value || '';
    const ph = document.getElementById('indexInputPhone')?.value || '';
    const em = document.getElementById('indexInputEmail')?.value || '';

    const titleEl = document.getElementById('indexPreviewHeaderTitle');
    const tagEl = document.getElementById('indexPreviewHeaderTagline');
    const metaEl = document.getElementById('indexPreviewHeaderMeta');

    if (titleEl) titleEl.textContent = name;
    if (tagEl) tagEl.textContent = tag;
    if (metaEl) metaEl.textContent = `${addr} · Phone: ${ph} · Email: ${em}`;
}

// 2:1 Aspect Ratio Canvas Cropper Logic for Index
let indexRawImage = new Image();
let indexImageLoaded = false;
let indexCropX = 0, indexCropY = 0;
let indexCropScale = 1;
let indexIsDragging = false;
let indexDragStartX = 0, indexDragStartY = 0;

const idxFileInput = document.getElementById('indexLogoFileInput');
const idxCropperBox = document.getElementById('indexCropperContainer');
const idxCanvas = document.getElementById('indexCropCanvas');
const idxCtx = idxCanvas?.getContext('2d');
const idxZoomSlider = document.getElementById('indexCropZoomSlider');
const idxBase64Input = document.getElementById('indexLogoCroppedBase64');
const idxResultThumb = document.getElementById('indexCroppedResultThumb');
const idxHeaderPreviewImg = document.getElementById('indexPreviewHeaderLogo');
const idxDragArea = document.getElementById('indexCropDragArea');

if (idxFileInput) {
    idxFileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function(evt) {
            indexRawImage = new Image();
            indexRawImage.onload = function() {
                indexImageLoaded = true;
                idxCropperBox.classList.remove('d-none');
                
                const scaleW = idxCanvas.width / indexRawImage.width;
                const scaleH = idxCanvas.height / indexRawImage.height;
                indexCropScale = Math.max(scaleW, scaleH);
                
                idxZoomSlider.min = (indexCropScale * 0.4).toFixed(2);
                idxZoomSlider.max = (indexCropScale * 3.5).toFixed(2);
                idxZoomSlider.value = indexCropScale.toFixed(2);
                
                indexCropX = (idxCanvas.width - indexRawImage.width * indexCropScale) / 2;
                indexCropY = (idxCanvas.height - indexRawImage.height * indexCropScale) / 2;

                renderIndexCrop();
            };
            indexRawImage.src = evt.target.result;
        };
        reader.readAsDataURL(file);
    });
}

function renderIndexCrop() {
    if (!indexImageLoaded || !idxCtx) return;
    
    idxCtx.clearRect(0, 0, idxCanvas.width, idxCanvas.height);
    idxCtx.fillStyle = '#ffffff';
    idxCtx.fillRect(0, 0, idxCanvas.width, idxCanvas.height);
    
    const drawW = indexRawImage.width * indexCropScale;
    const drawH = indexRawImage.height * indexCropScale;
    
    idxCtx.drawImage(indexRawImage, indexCropX, indexCropY, drawW, drawH);
    
    const dataUrl = idxCanvas.toDataURL('image/png', 0.95);
    if (idxBase64Input) idxBase64Input.value = dataUrl;
    if (idxResultThumb) idxResultThumb.src = dataUrl;
    if (idxHeaderPreviewImg) idxHeaderPreviewImg.src = dataUrl;
}

if (idxZoomSlider) {
    idxZoomSlider.addEventListener('input', function() {
        const prevScale = indexCropScale;
        indexCropScale = parseFloat(this.value);
        
        const centerX = idxCanvas.width / 2;
        const centerY = idxCanvas.height / 2;
        indexCropX = centerX - ((centerX - indexCropX) / prevScale) * indexCropScale;
        indexCropY = centerY - ((centerY - indexCropY) / prevScale) * indexCropScale;
        
        renderIndexCrop();
    });
}

if (idxDragArea) {
    idxDragArea.addEventListener('mousedown', function(e) {
        indexIsDragging = true;
        indexDragStartX = e.clientX - indexCropX;
        indexDragStartY = e.clientY - indexCropY;
        idxDragArea.style.cursor = 'grabbing';
    });

    window.addEventListener('mousemove', function(e) {
        if (!indexIsDragging) return;
        indexCropX = e.clientX - indexDragStartX;
        indexCropY = e.clientY - indexCropY;
        renderIndexCrop();
    });

    window.addEventListener('mouseup', function() {
        if (indexIsDragging) {
            indexIsDragging = false;
            idxDragArea.style.cursor = 'grab';
        }
    });

    idxDragArea.addEventListener('touchstart', function(e) {
        if (e.touches.length === 1) {
            indexIsDragging = true;
            indexDragStartX = e.touches[0].clientX - indexCropX;
            indexDragStartY = e.touches[0].clientY - indexCropY;
        }
    }, {passive: true});

    window.addEventListener('touchmove', function(e) {
        if (!indexIsDragging || e.touches.length !== 1) return;
        indexCropX = e.touches[0].clientX - indexDragStartX;
        indexCropY = e.touches[0].clientY - indexCropY;
        renderIndexCrop();
    }, {passive: true});

    window.addEventListener('touchend', function() {
        indexIsDragging = false;
    });
}

function indexResetCrop() {
    if (!indexImageLoaded) return;
    const scaleW = idxCanvas.width / indexRawImage.width;
    const scaleH = idxCanvas.height / indexRawImage.height;
    indexCropScale = Math.max(scaleW, scaleH);
    idxZoomSlider.value = indexCropScale.toFixed(2);
    indexCropX = (idxCanvas.width - indexRawImage.width * indexCropScale) / 2;
    indexCropY = (idxCanvas.height - indexRawImage.height * indexCropScale) / 2;
    renderIndexCrop();
}
</script>

@endsection
