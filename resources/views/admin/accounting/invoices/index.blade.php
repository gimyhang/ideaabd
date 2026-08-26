@extends('layouts.admin')

@php
    $settings = $invoiceSettings ?? \App\Http\Controllers\Admin\IdeaAccountingController::getInvoiceSettings();
    $bizLogo = $settings['logo'] ?? '/images/logo.png';
    $logoSrc = \App\Support\SiteSetting::resolveImageUrl($bizLogo, 'images/logo.png') ?: asset('images/logo.png');
@endphp

@section('title', 'Invoices, Delivery Challans & Quotations')
@section('heading', 'Invoices, Delivery Challans, Quotations & Tender List')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.accounting.index') }}">Accounting & Cashbook</a></li>
    <li class="breadcrumb-item active" aria-current="page">Invoices & Challans</li>
@endsection

@section('actions')
    <div class="d-flex flex-wrap align-items-center gap-2">
        <div class="dropdown">
            <button class="btn btn-primary btn-sm rounded-pill px-3.5 fw-bold shadow-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-plus-circle me-1"></i> + নতুন ইনভয়েস / চালান
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow rounded-4 border-0 p-2" style="min-width: 240px;">
                <li><h6 class="dropdown-header small text-uppercase fw-bold text-muted">ইনভয়েসের শ্রেণি নির্বাচন করুন:</h6></li>
                <li>
                    <a class="dropdown-item rounded-3 py-2 fw-semibold d-flex align-items-center gap-2" href="{{ route('admin.accounting.invoices.create', ['sales_category' => 'books']) }}">
                        <i class="fa-solid fa-book text-primary"></i> 📚 ১. বই বিক্রয় ইনভয়েস
                    </a>
                </li>
                <li>
                    <a class="dropdown-item rounded-3 py-2 fw-semibold d-flex align-items-center gap-2" href="{{ route('admin.accounting.invoices.create', ['sales_category' => 'stationery']) }}">
                        <i class="fa-solid fa-pen-ruler text-info"></i> ✏️ ২. স্টেশনারী বিক্রয় ইনভয়েস
                    </a>
                </li>
                <li>
                    <a class="dropdown-item rounded-3 py-2 fw-semibold d-flex align-items-center gap-2" href="{{ route('admin.accounting.invoices.create', ['sales_category' => 'printing_goods']) }}">
                        <i class="fa-solid fa-print text-warning"></i> 🖨️ ৩. প্রিন্টিং গুডস ও সেবা ইনভয়েস
                    </a>
                </li>
                <li>
                    <a class="dropdown-item rounded-3 py-2 fw-semibold d-flex align-items-center gap-2" href="{{ route('admin.accounting.invoices.create', ['sales_category' => 'other']) }}">
                        <i class="fa-solid fa-cart-plus text-secondary"></i> 🛒 ৪. অন্যান্য ও বিবিধ বিক্রয়
                    </a>
                </li>
            </ul>
        </div>
        <a href="{{ route('admin.accounting.invoices.create', ['type' => 'tender']) }}" class="btn btn-purple text-white btn-sm rounded-pill px-3 fw-semibold shadow-sm" style="background-color: #6f42c1;">
            <i class="fas fa-landmark me-1"></i> + Tender Document
        </a>
        <button type="button" class="btn btn-outline-dark btn-sm rounded-pill px-3 fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#invoiceSettingsModal" title="Customize invoice branding and layout">
            <i class="fas fa-palette me-1.5 text-primary"></i> Branding
        </button>
        <a href="{{ route('admin.accounting.index') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-semibold shadow-xs">
            <i class="fas fa-scale-balanced me-1"></i> Cashbook
        </a>
    </div>
@endsection

@section('content')

{{-- 4-Class Sales Category Switcher Bar --}}
<div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
    <div class="card-body p-3">
        <div class="d-flex flex-column flex-lg-row align-items-center justify-content-between gap-3">
            <div class="btn-group shadow-2xs rounded-pill p-1 bg-light border w-100 w-lg-auto" role="group">
                <a href="{{ route('admin.accounting.invoices.index') }}" 
                   class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold {{ empty($salesCategory) ? 'btn-white text-primary shadow-xs' : 'btn-light text-muted' }}">
                    <i class="fa-solid fa-layer-group me-1"></i> সকল ইনভয়েস ({{ $stats['total_invoices'] }})
                </a>
                <a href="{{ route('admin.accounting.invoices.index', ['sales_category' => 'books']) }}" 
                   class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold {{ $salesCategory === 'books' ? 'btn-white text-primary shadow-xs' : 'btn-light text-muted' }}">
                    <i class="fa-solid fa-book me-1"></i> ১. বই বিক্রয় ({{ $stats['books_count'] }})
                </a>
                <a href="{{ route('admin.accounting.invoices.index', ['sales_category' => 'stationery']) }}" 
                   class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold {{ $salesCategory === 'stationery' ? 'btn-white text-info shadow-xs' : 'btn-light text-muted' }}">
                    <i class="fa-solid fa-pen-ruler me-1"></i> ২. স্টেশনারী ({{ $stats['stationery_count'] }})
                </a>
                <a href="{{ route('admin.accounting.invoices.index', ['sales_category' => 'printing_goods']) }}" 
                   class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold {{ $salesCategory === 'printing_goods' ? 'btn-white text-warning shadow-xs' : 'btn-light text-muted' }}">
                    <i class="fa-solid fa-print me-1"></i> ৩. প্রিন্টিং গুডস ({{ $stats['printing_count'] }})
                </a>
                <a href="{{ route('admin.accounting.invoices.index', ['sales_category' => 'other']) }}" 
                   class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold {{ $salesCategory === 'other' ? 'btn-white text-secondary shadow-xs' : 'btn-light text-muted' }}">
                    <i class="fa-solid fa-cart-plus me-1"></i> ৪. অন্যান্য ({{ $stats['other_count'] }})
                </a>
            </div>

            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-semibold" data-bs-toggle="modal" data-bs-target="#invoiceSettingsModal">
                    <i class="fas fa-sliders me-1 text-primary"></i> মেমো ব্র্যান্ডিং সেটিংস
                </button>
            </div>
        </div>
    </div>
</div>

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
                <select name="payment_status" class="form-select" onchange="this.form.submit()">
                    <option value="">All Payment Status</option>
                    <option value="paid" @selected($status === 'paid')>Paid</option>
                    <option value="partial" @selected($status === 'partial')>Partially Paid</option>
                    <option value="unpaid" @selected($status === 'unpaid')>Unpaid / Due</option>
                </select>
            </div>
            <div class="col-md-3">
                <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}" title="Date">
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter me-1"></i> Filter</button>
                @if(request()->hasAny(['search', 'type', 'payment_status', 'date_from', 'date_to']))
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
        <div class="table-responsive">
            <table class="table adm-table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Document #</th>
                        <th>Type</th>
                        <th>Date</th>
                        <th>Client & Organization</th>
                        <th>Items</th>
                        <th>Grand Total</th>
                        <th>Paid</th>
                        <th>Due</th>
                        <th>Status</th>
                        <th class="text-center pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($invoices as $inv)
                        <tr>
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
                                {{ $inv->invoice_date ? $inv->invoice_date->format('d M, Y') : '—' }}
                                @if($inv->valid_until)
                                    <div class="text-danger" style="font-size: 10.5px;">Valid until: {{ $inv->valid_until->format('d M, Y') }}</div>
                                @endif
                            </td>
                            <td>
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
                                    <div class="text-muted small" style="font-size: 11px;"><i class="fas fa-phone me-1"></i>{{ $inv->customer_phone }}</div>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ count($inv->items ?? []) }} items</span>
                            </td>
                            <td class="fw-bold text-dark">৳{{ number_format($inv->grand_total, 2) }}</td>
                            <td class="fw-bold text-success">
                                @if(in_array($inv->type, ['invoice', 'challan']))
                                    ৳{{ number_format($inv->paid_amount, 2) }}
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td class="fw-bold {{ $inv->due_amount > 0 ? 'text-danger' : 'text-muted' }}">
                                @if(in_array($inv->type, ['invoice', 'challan']))
                                    ৳{{ number_format($inv->due_amount, 2) }}
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td>
                                @if(in_array($inv->type, ['quotation', 'tender']))
                                    <span class="badge bg-light text-dark border px-2 py-1 rounded-pill">
                                        Proposed
                                    </span>
                                @elseif($inv->payment_status === 'paid')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 rounded-pill">
                                        Paid
                                    </span>
                                @elseif($inv->payment_status === 'partial')
                                    <span class="badge bg-warning-subtle text-dark border border-warning-subtle px-2.5 py-1 rounded-pill">
                                        Partial
                                    </span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1 rounded-pill">
                                        Due
                                    </span>
                                @endif
                            </td>
                            <td class="text-center pe-3">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.accounting.invoices.show', $inv->id) }}" class="btn btn-outline-primary" title="View & Print">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.accounting.invoices.edit', $inv->id) }}" class="btn btn-outline-warning text-dark" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.accounting.invoices.destroy', $inv->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this invoice document?')">
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
