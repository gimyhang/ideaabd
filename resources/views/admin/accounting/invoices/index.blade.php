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
        <a href="{{ route('admin.accounting.tax-vat-deductions.index') }}" class="btn btn-outline-warning text-dark btn-sm rounded-pill px-3 fw-semibold shadow-xs" title="উৎসে কর ও মূসক কর্তন রেজিস্টার (TDS & VDS)">
            <i class="fas fa-receipt me-1 text-warning"></i> TDS & VDS Register
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

{{-- 1. Dedicated Document Folder Tabs (ডকুমেন্ট ফোল্ডার হাব) --}}
<div class="mb-4">
    <div class="row row-cols-2 row-cols-md-3 row-cols-lg-5 g-2.5">
        {{-- Folder 1: Invoices / Bills --}}
        <div class="col">
            <a href="{{ route('admin.accounting.invoices.index', array_merge(request()->except('type', 'page'), ['type' => 'invoice'])) }}" 
               class="card h-100 border text-decoration-none transition-all rounded-4 p-3 shadow-2xs position-relative overflow-hidden {{ $currentType === 'invoice' ? 'bg-primary-subtle border-primary shadow-sm ring-2 ring-primary' : 'bg-white hover-shadow-md border-light-subtle' }}"
               style="{{ $currentType === 'invoice' ? 'border-width: 2px !important; background: linear-gradient(135deg, rgba(13,110,253,0.06) 0%, #ffffff 100%);' : '' }}">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="rounded-3 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; background: rgba(13, 110, 253, 0.12); color: #0d6efd;">
                        <i class="fas fa-receipt fs-5"></i>
                    </div>
                    <span class="badge {{ $currentType === 'invoice' ? 'bg-primary text-white' : 'bg-primary-subtle text-primary border border-primary-subtle' }} rounded-pill px-2.5 py-1 fw-bold fs-7 font-monospace">
                        {{ number_format($stats['total_bills']) }}
                    </span>
                </div>
                <h6 class="fw-bold text-dark mb-0.5 d-flex align-items-center gap-1.5" style="font-size: 14.5px;">
                    <span>বিল ও ইনভয়েস</span>
                </h6>
                <p class="text-muted small mb-2" style="font-size: 11px;">নিয়মিত বিক্রয় বিল ও মেমো</p>
                <div class="pt-1.5 border-top d-flex justify-content-between align-items-center">
                    <span class="text-muted" style="font-size: 10.5px;">মোট বিল:</span>
                    <span class="fw-bold text-primary font-monospace" style="font-size: 11.5px;">৳{{ number_format($stats['bills_amount']) }}</span>
                </div>
                @if($currentType === 'invoice')
                    <div class="position-absolute bottom-0 start-0 w-100 bg-primary" style="height: 3.5px;"></div>
                @endif
            </a>
        </div>

        {{-- Folder 2: Delivery Challans --}}
        <div class="col">
            <a href="{{ route('admin.accounting.invoices.index', array_merge(request()->except('type', 'page'), ['type' => 'challan'])) }}" 
               class="card h-100 border text-decoration-none transition-all rounded-4 p-3 shadow-2xs position-relative overflow-hidden {{ $currentType === 'challan' ? 'bg-info-subtle border-info shadow-sm' : 'bg-white hover-shadow-md border-light-subtle' }}"
               style="{{ $currentType === 'challan' ? 'border-width: 2px !important; background: linear-gradient(135deg, rgba(13,202,240,0.08) 0%, #ffffff 100%);' : '' }}">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="rounded-3 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; background: rgba(13, 202, 240, 0.15); color: #087990;">
                        <i class="fas fa-truck-fast fs-5"></i>
                    </div>
                    <span class="badge {{ $currentType === 'challan' ? 'bg-info text-white' : 'bg-info-subtle text-info-emphasis border border-info-subtle' }} rounded-pill px-2.5 py-1 fw-bold fs-7 font-monospace">
                        {{ number_format($stats['total_challans']) }}
                    </span>
                </div>
                <h6 class="fw-bold text-dark mb-0.5 d-flex align-items-center gap-1.5" style="font-size: 14.5px;">
                    <span>ডেলিভারি চালান</span>
                </h6>
                <p class="text-muted small mb-2" style="font-size: 11px;">পণ্য সরবরাহ ও চালান কপি</p>
                <div class="pt-1.5 border-top d-flex justify-content-between align-items-center">
                    <span class="text-muted" style="font-size: 10.5px;">বই চালান:</span>
                    <span class="fw-bold text-info-emphasis font-monospace" style="font-size: 11.5px;">{{ $stats['challans_books_count'] }} টি</span>
                </div>
                @if($currentType === 'challan')
                    <div class="position-absolute bottom-0 start-0 w-100 bg-info" style="height: 3.5px;"></div>
                @endif
            </a>
        </div>

        {{-- Folder 3: Price Quotations --}}
        <div class="col">
            <a href="{{ route('admin.accounting.invoices.index', array_merge(request()->except('type', 'page'), ['type' => 'quotation'])) }}" 
               class="card h-100 border text-decoration-none transition-all rounded-4 p-3 shadow-2xs position-relative overflow-hidden {{ $currentType === 'quotation' ? 'bg-warning-subtle border-warning shadow-sm' : 'bg-white hover-shadow-md border-light-subtle' }}"
               style="{{ $currentType === 'quotation' ? 'border-width: 2px !important; background: linear-gradient(135deg, rgba(255,193,7,0.12) 0%, #ffffff 100%);' : '' }}">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="rounded-3 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; background: rgba(255, 193, 7, 0.2); color: #997404;">
                        <i class="fas fa-file-lines fs-5"></i>
                    </div>
                    <span class="badge {{ $currentType === 'quotation' ? 'bg-warning text-dark' : 'bg-warning-subtle text-warning-emphasis border border-warning-subtle' }} rounded-pill px-2.5 py-1 fw-bold fs-7 font-monospace">
                        {{ number_format($stats['total_quotations']) }}
                    </span>
                </div>
                <h6 class="fw-bold text-dark mb-0.5 d-flex align-items-center gap-1.5" style="font-size: 14.5px;">
                    <span>দরপত্র ও কোটেশন</span>
                </h6>
                <p class="text-muted small mb-2" style="font-size: 11px;">প্রস্তাবিত মূল্য তালিকা</p>
                <div class="pt-1.5 border-top d-flex justify-content-between align-items-center">
                    <span class="text-muted" style="font-size: 10.5px;">প্রস্তাবিত মান:</span>
                    <span class="fw-bold text-warning-emphasis font-monospace" style="font-size: 11.5px;">৳{{ number_format($stats['quotations_amount']) }}</span>
                </div>
                @if($currentType === 'quotation')
                    <div class="position-absolute bottom-0 start-0 w-100 bg-warning" style="height: 3.5px;"></div>
                @endif
            </a>
        </div>

        {{-- Folder 4: Tender Documents --}}
        <div class="col">
            <a href="{{ route('admin.accounting.invoices.index', array_merge(request()->except('type', 'page'), ['type' => 'tender'])) }}" 
               class="card h-100 border text-decoration-none transition-all rounded-4 p-3 shadow-2xs position-relative overflow-hidden {{ $currentType === 'tender' ? 'border-purple shadow-sm' : 'bg-white hover-shadow-md border-light-subtle' }}"
               style="{{ $currentType === 'tender' ? 'border-color: #9333ea !important; border-width: 2px !important; background: linear-gradient(135deg, rgba(147,51,234,0.08) 0%, #ffffff 100%);' : '' }}">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="rounded-3 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; background: rgba(147, 51, 234, 0.12); color: #7e22ce;">
                        <i class="fas fa-landmark fs-5"></i>
                    </div>
                    <span class="badge {{ $currentType === 'tender' ? 'bg-purple text-white' : 'border' }} rounded-pill px-2.5 py-1 fw-bold fs-7 font-monospace" style="{{ $currentType === 'tender' ? 'background-color: #9333ea;' : 'background-color: #f3e8ff; color: #7e22ce; border-color: #d8b4fe;' }}">
                        {{ number_format($stats['total_tenders']) }}
                    </span>
                </div>
                <h6 class="fw-bold text-dark mb-0.5 d-flex align-items-center gap-1.5" style="font-size: 14.5px;">
                    <span>টেন্ডার ফাইল</span>
                </h6>
                <p class="text-muted small mb-2" style="font-size: 11px;">প্রাতিষ্ঠানিক দরপত্র ও ফাইল</p>
                <div class="pt-1.5 border-top d-flex justify-content-between align-items-center">
                    <span class="text-muted" style="font-size: 10.5px;">টেন্ডার মূল্য:</span>
                    <span class="fw-bold font-monospace" style="font-size: 11.5px; color: #7e22ce;">৳{{ number_format($stats['tenders_amount']) }}</span>
                </div>
                @if($currentType === 'tender')
                    <div class="position-absolute bottom-0 start-0 w-100" style="height: 3.5px; background-color: #9333ea;"></div>
                @endif
            </a>
        </div>

        {{-- Folder 5: All Documents Archive --}}
        <div class="col">
            <a href="{{ route('admin.accounting.invoices.index', array_merge(request()->except('type', 'page'))) }}" 
               class="card h-100 border text-decoration-none transition-all rounded-4 p-3 shadow-2xs position-relative overflow-hidden {{ empty($currentType) ? 'bg-secondary-subtle border-secondary shadow-sm' : 'bg-white hover-shadow-md border-light-subtle' }}"
               style="{{ empty($currentType) ? 'border-width: 2px !important; background: linear-gradient(135deg, rgba(108,117,125,0.08) 0%, #ffffff 100%);' : '' }}">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="rounded-3 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; background: rgba(108, 117, 125, 0.15); color: #495057;">
                        <i class="fas fa-folder-tree fs-5"></i>
                    </div>
                    <span class="badge {{ empty($currentType) ? 'bg-dark text-white' : 'bg-secondary-subtle text-dark border border-secondary-subtle' }} rounded-pill px-2.5 py-1 fw-bold fs-7 font-monospace">
                        {{ number_format($stats['total_invoices']) }}
                    </span>
                </div>
                <h6 class="fw-bold text-dark mb-0.5 d-flex align-items-center gap-1.5" style="font-size: 14.5px;">
                    <span>সকল ডকুমেন্ট</span>
                </h6>
                <p class="text-muted small mb-2" style="font-size: 11px;">সার্বিক ফাইল আর্কাইভ</p>
                <div class="pt-1.5 border-top d-flex justify-content-between align-items-center">
                    <span class="text-muted" style="font-size: 10.5px;">ক্যাটাগরি:</span>
                    <span class="fw-bold text-dark" style="font-size: 11px;">৪টি ফোল্ডার</span>
                </div>
                @if(empty($currentType))
                    <div class="position-absolute bottom-0 start-0 w-100 bg-secondary" style="height: 3.5px;"></div>
                @endif
            </a>
        </div>
    </div>
</div>

{{-- 2. Context-Aware Sub-Dashboard Metrics (সক্রিয় ফোল্ডারের নির্দিষ্ট ড্যাশবোর্ড) --}}
@if($currentType === 'invoice')
    {{-- Invoice / Bills Sub-Dashboard --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-primary">
                <span class="text-muted small fw-semibold">মোট ইনভয়েস / বিল</span>
                <h3 class="fw-bold mb-0 text-primary font-monospace">{{ number_format($stats['total_bills']) }}</h3>
                <div class="text-muted small mt-1" style="font-size: 11.5px;">নিয়মিত বিক্রয় ও ক্যাশ মেমো</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-info">
                <span class="text-muted small fw-semibold">মোট বিক্রয় মূল্য</span>
                <h3 class="fw-bold mb-0 text-dark font-monospace">৳{{ number_format($stats['bills_amount'], 2) }}</h3>
                <div class="text-muted small mt-1" style="font-size: 11.5px;">মোট বিলের পরিমাণ</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-success">
                <span class="text-muted small fw-semibold">মোট আদায়কৃত টাকা</span>
                <h3 class="fw-bold mb-0 text-success font-monospace">৳{{ number_format($stats['bills_paid'], 2) }}</h3>
                <div class="text-muted small mt-1 text-success" style="font-size: 11.5px;"><i class="fas fa-check-circle me-1"></i>ক্যাশ ও ব্যাংক জমা</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-danger">
                <span class="text-muted small fw-semibold">মোট বকেয়া জের</span>
                <h3 class="fw-bold mb-0 text-danger font-monospace">৳{{ number_format($stats['bills_due'], 2) }}</h3>
                <div class="text-muted small mt-1 text-danger" style="font-size: 11.5px;"><i class="fas fa-clock me-1"></i>গ্রাহক থেকে প্রাপ্য</div>
            </div>
        </div>
    </div>
@elseif($currentType === 'challan')
    {{-- Delivery Challans Sub-Dashboard --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-info">
                <span class="text-muted small fw-semibold">মোট ডেলিভারি চালান</span>
                <h3 class="fw-bold mb-0 text-info font-monospace">{{ number_format($stats['total_challans']) }}</h3>
                <div class="text-muted small mt-1" style="font-size: 11.5px;">পণ্য সরবরাহ মেমো</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-primary">
                <span class="text-muted small fw-semibold">বই ও প্রকাশনা চালান</span>
                <h3 class="fw-bold mb-0 text-primary font-monospace">{{ number_format($stats['challans_books_count']) }}</h3>
                <div class="text-muted small mt-1" style="font-size: 11.5px;">আইডিয়া প্রকাশন বুক চালান</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-warning">
                <span class="text-muted small fw-semibold">স্টেশনারি ও প্রিন্টিং চালান</span>
                <h3 class="fw-bold mb-0 text-warning-emphasis font-monospace">{{ number_format($stats['challans_other_count']) }}</h3>
                <div class="text-muted small mt-1" style="font-size: 11.5px;">অন্যান্য পণ্য ও সেবা</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-success">
                <span class="text-muted small fw-semibold">চালান পণ্যের মূল্যমান</span>
                <h3 class="fw-bold mb-0 text-success font-monospace">৳{{ number_format($stats['challans_amount'], 2) }}</h3>
                <div class="text-muted small mt-1" style="font-size: 11.5px;">আদায়: ৳{{ number_format($stats['challans_paid'], 2) }}</div>
            </div>
        </div>
    </div>
@elseif($currentType === 'quotation')
    {{-- Quotations Sub-Dashboard --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-warning">
                <span class="text-muted small fw-semibold">মোট কোটেশন ফাইল</span>
                <h3 class="fw-bold mb-0 text-warning-emphasis font-monospace">{{ number_format($stats['total_quotations']) }}</h3>
                <div class="text-muted small mt-1" style="font-size: 11.5px;">মূল্য প্রস্তাবনা সমূহ</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-primary">
                <span class="text-muted small fw-semibold">প্রস্তাবিত মোট মূল্য</span>
                <h3 class="fw-bold mb-0 text-primary font-monospace">৳{{ number_format($stats['quotations_amount'], 2) }}</h3>
                <div class="text-muted small mt-1" style="font-size: 11.5px;">কোটেশন সম্ভাব্য বিক্রয় মান</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-success">
                <span class="text-muted small fw-semibold">সক্রিয় ও বৈধ কোটেশন</span>
                <h3 class="fw-bold mb-0 text-success font-monospace">{{ number_format($stats['quotations_active']) }}</h3>
                <div class="text-muted small mt-1 text-success" style="font-size: 11.5px;"><i class="fas fa-check-circle me-1"></i>মেয়াদ অবশিষ্ট আছে</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-danger">
                <span class="text-muted small fw-semibold">মেয়াদোত্তীর্ণ / ফলোআপ</span>
                <h3 class="fw-bold mb-0 text-danger font-monospace">{{ number_format($stats['quotations_expired']) }}</h3>
                <div class="text-muted small mt-1 text-danger" style="font-size: 11.5px;"><i class="fas fa-triangle-exclamation me-1"></i>রিভিউ বা ফলোআপ প্রয়োজন</div>
            </div>
        </div>
    </div>
@elseif($currentType === 'tender')
    {{-- Tenders Sub-Dashboard --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4" style="border-left-color: #9333ea !important;">
                <span class="text-muted small fw-semibold">মোট টেন্ডার ফাইল</span>
                <h3 class="fw-bold mb-0 font-monospace" style="color: #7e22ce;">{{ number_format($stats['total_tenders']) }}</h3>
                <div class="text-muted small mt-1" style="font-size: 11.5px;">প্রাতিষ্ঠানিক দরপত্র ও বিড</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-primary">
                <span class="text-muted small fw-semibold">টেন্ডারে প্রস্তাবিত মূল্য</span>
                <h3 class="fw-bold mb-0 text-primary font-monospace">৳{{ number_format($stats['tenders_amount'], 2) }}</h3>
                <div class="text-muted small mt-1" style="font-size: 11.5px;">মোট প্রস্তাবিত প্রকল্প মূল্য</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-info">
                <span class="text-muted small fw-semibold">অংশীদারী প্রতিষ্ঠান/দপ্তর</span>
                <h3 class="fw-bold mb-0 text-info font-monospace">{{ number_format($stats['tenders_orgs']) }}</h3>
                <div class="text-muted small mt-1" style="font-size: 11.5px;">সরকারি/বেসরকারি প্রতিষ্ঠান</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-success">
                <span class="text-muted small fw-semibold">সক্রিয় টেন্ডার প্রস্তাবনা</span>
                <h3 class="fw-bold mb-0 text-success font-monospace">{{ number_format($stats['tenders_active']) }}</h3>
                <div class="text-muted small mt-1 text-success" style="font-size: 11.5px;"><i class="fas fa-bolt me-1"></i>চলমান প্রক্রিয়ায় আছে</div>
            </div>
        </div>
    </div>
@else
    {{-- All Documents Overview --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-primary">
                <span class="text-muted small fw-semibold">সর্বমোট ফাইল</span>
                <h3 class="fw-bold mb-0 text-primary font-monospace">{{ number_format($stats['total_invoices']) }}</h3>
                <div class="text-muted small mt-1" style="font-size: 11.5px;">
                    বিল: {{ number_format($stats['total_bills']) }} | চালান: {{ number_format($stats['total_challans']) }}
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4" style="border-left-color: #6f42c1 !important;">
                <span class="text-muted small fw-semibold">কোটেশন ও টেন্ডার</span>
                <h3 class="fw-bold mb-0 font-monospace" style="color: #6f42c1;">{{ number_format($stats['total_quotations'] + $stats['total_tenders']) }}</h3>
                <div class="text-muted small mt-1" style="font-size: 11.5px;">
                    কোটেশন: {{ number_format($stats['total_quotations']) }} | টেন্ডার: {{ number_format($stats['total_tenders']) }}
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-success">
                <span class="text-muted small fw-semibold">মোট আদায়কৃত টাকা</span>
                <h3 class="fw-bold mb-0 text-success font-monospace">৳{{ number_format($stats['total_paid'], 2) }}</h3>
                <div class="text-muted small mt-1" style="font-size: 11.5px;">মোট বিক্রয়: ৳{{ number_format($stats['total_amount'], 2) }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-danger">
                <span class="text-muted small fw-semibold">মোট বকেয়া জের</span>
                <h3 class="fw-bold mb-0 text-danger font-monospace">৳{{ number_format($stats['total_due'], 2) }}</h3>
                <div class="text-muted small mt-1" style="font-size: 11.5px;">গ্রাহকদের থেকে মোট পাওনা</div>
            </div>
        </div>
    </div>
@endif

{{-- 3. Filter & Search Toolbar --}}
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-3">
        <form action="{{ route('admin.accounting.invoices.index') }}" method="GET" class="row g-2 align-items-center">
            @if($type)
                <input type="hidden" name="type" value="{{ $type }}">
            @endif

            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-1" placeholder="ডকুমেন্ট # / গ্রাহক / প্রতিষ্ঠান / বিষয়..." value="{{ $search }}">
                </div>
            </div>
            
            <div class="col-md-3">
                <select name="sales_category" class="form-select" onchange="this.form.submit()">
                    <option value="">সকল পণ্য ক্যাটাগরি (All Categories)</option>
                    <option value="books" @selected($salesCategory === 'books')>বই ও প্রকাশনা (Books)</option>
                    <option value="stationery" @selected($salesCategory === 'stationery')>স্টেশনারি সামগ্রী (Stationery)</option>
                    <option value="printing_goods" @selected($salesCategory === 'printing_goods')>মুদ্রণ ও প্রিন্টিং (Printing)</option>
                    <option value="other" @selected($salesCategory === 'other')>অন্যান্য পণ্য (Others)</option>
                </select>
            </div>

            @if(in_array($currentType, ['invoice', 'challan', null, '']))
                <div class="col-md-2">
                    <select name="payment_status" class="form-select" onchange="this.form.submit()">
                        <option value="">পেমেন্ট স্ট্যাটাস (All)</option>
                        <option value="paid" @selected($status === 'paid')>পরিশোধিত (Paid)</option>
                        <option value="partial" @selected($status === 'partial')>আংশিক পরিশোধ (Partial)</option>
                        <option value="unpaid" @selected($status === 'unpaid')>বকেয়া (Unpaid / Due)</option>
                    </select>
                </div>
            @endif

            <div class="col-md-2">
                <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}" title="তারিখ থেকে">
            </div>

            <div class="col-md-1 d-flex gap-1">
                <button type="submit" class="btn btn-primary w-100 fw-semibold" title="ফিল্টার প্রয়োগ করুন"><i class="fas fa-filter"></i></button>
                @if(request()->hasAny(['search', 'sales_category', 'payment_status', 'date_from', 'date_to']))
                    <a href="{{ route('admin.accounting.invoices.index', $type ? ['type' => $type] : []) }}" class="btn btn-light border" title="রিসেট"><i class="fas fa-rotate-left"></i></a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- 4. Documents Data Table --}}
<div class="adm-card shadow-sm rounded-4 overflow-hidden mb-4 bg-white">
    <div class="card-header bg-white border-bottom py-3 px-3.5 d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div class="d-flex align-items-center gap-2">
            @if($currentType === 'invoice')
                <i class="fas fa-receipt text-primary fs-5"></i>
                <h5 class="fw-bold mb-0 text-dark">বিল ও ক্যাশ মেমো তালিকা</h5>
            @elseif($currentType === 'challan')
                <i class="fas fa-truck-fast text-info fs-5"></i>
                <h5 class="fw-bold mb-0 text-dark">ডেলিভারি চালান তালিকা</h5>
            @elseif($currentType === 'quotation')
                <i class="fas fa-file-lines text-warning fs-5"></i>
                <h5 class="fw-bold mb-0 text-dark">দরপত্র ও কোটেশন তালিকা</h5>
            @elseif($currentType === 'tender')
                <i class="fas fa-landmark text-purple fs-5" style="color: #7e22ce;"></i>
                <h5 class="fw-bold mb-0 text-dark">টেন্ডার ডকুমেন্ট ও প্রস্তাবনা তালিকা</h5>
            @else
                <i class="fas fa-folder-tree text-secondary fs-5"></i>
                <h5 class="fw-bold mb-0 text-dark">সকল সংরক্ষিত ফাইল ও ডকুমেন্টস</h5>
            @endif
            <span class="badge bg-light text-muted border rounded-pill font-monospace">{{ $invoices->total() }} টি ফাইল</span>
        </div>

        <div>
            @if($currentType === 'quotation')
                <a href="{{ route('admin.accounting.invoices.create', ['type' => 'quotation']) }}" class="btn btn-warning btn-sm rounded-pill px-3 fw-semibold shadow-xs">
                    <i class="fas fa-plus-circle me-1"></i> নতুন কোটেশন তৈরি
                </a>
            @elseif($currentType === 'tender')
                <a href="{{ route('admin.accounting.invoices.create', ['type' => 'tender']) }}" class="btn btn-sm rounded-pill px-3 fw-semibold text-white shadow-xs" style="background-color: #9333ea;">
                    <i class="fas fa-plus-circle me-1"></i> নতুন টেন্ডার প্রস্তাবনা
                </a>
            @elseif($currentType === 'challan')
                <a href="{{ route('admin.accounting.invoices.create', ['type' => 'challan']) }}" class="btn btn-info btn-sm rounded-pill px-3 fw-semibold text-white shadow-xs">
                    <i class="fas fa-plus-circle me-1"></i> নতুন ডেলিভারি চালান
                </a>
            @elseif($currentType === 'invoice')
                <a href="{{ route('admin.accounting.invoices.create', ['type' => 'invoice']) }}" class="btn btn-primary btn-sm rounded-pill px-3 fw-semibold shadow-xs">
                    <i class="fas fa-plus-circle me-1"></i> নতুন বিক্রয় বিল
                </a>
            @endif
        </div>
    </div>

    @if ($invoices->isEmpty())
        <div class="empty-state py-5 text-center">
            <div class="mb-3">
                <i class="fas fa-folder-open fs-1 text-muted opacity-50"></i>
            </div>
            <h5 class="fw-bold text-muted">কোনো ফাইল পাওয়া যায়নি</h5>
            <p class="text-muted small">এই ফোল্ডারে নতুন ফাইল তৈরি করতে উপরের বাটনে ক্লিক করুন।</p>
            @if($currentType === 'quotation')
                <a href="{{ route('admin.accounting.invoices.create', ['type' => 'quotation']) }}" class="btn btn-warning btn-sm rounded-pill px-3.5 fw-semibold mt-2">
                    <i class="fas fa-plus-circle me-1"></i> নতুন কোটেশন তৈরি করুন
                </a>
            @elseif($currentType === 'tender')
                <a href="{{ route('admin.accounting.invoices.create', ['type' => 'tender']) }}" class="btn btn-sm rounded-pill px-3.5 fw-semibold text-white mt-2" style="background-color: #9333ea;">
                    <i class="fas fa-plus-circle me-1"></i> নতুন টেন্ডার ডকুমেন্ট তৈরি করুন
                </a>
            @elseif($currentType === 'challan')
                <a href="{{ route('admin.accounting.invoices.create', ['type' => 'challan']) }}" class="btn btn-info btn-sm rounded-pill px-3.5 fw-semibold text-white mt-2">
                    <i class="fas fa-plus-circle me-1"></i> নতুন চালান তৈরি করুন
                </a>
            @else
                <a href="{{ route('admin.accounting.invoices.create', ['type' => 'invoice']) }}" class="btn btn-primary btn-sm rounded-pill px-3.5 fw-semibold mt-2">
                    <i class="fas fa-plus-circle me-1"></i> নতুন বিল তৈরি করুন
                </a>
            @endif
        </div>
    @else
        <div class="table-responsive">
            @if(in_array($currentType, ['quotation', 'tender']))
                {{-- Quotation & Tender Specialized Table Layout --}}
                <table class="table adm-table align-middle mb-0" style="min-width: 1000px;">
                    <thead>
                        <tr>
                            <th class="ps-3 py-3" style="width: 160px;">{{ $currentType === 'tender' ? 'টেন্ডার #' : 'কোটেশন #' }}</th>
                            <th class="py-3" style="width: 120px;">তারিখ</th>
                            <th class="py-3" style="min-width: 230px;">প্রতিষ্ঠান ও গ্রাহক</th>
                            <th class="py-3" style="min-width: 220px;">বিষয় ও আইটেম</th>
                            <th class="py-3 text-end" style="width: 130px;">প্রস্তাবিত মোট মূল্য</th>
                            <th class="py-3 text-center" style="width: 130px;">মেয়াদ / স্ট্যাটাস</th>
                            <th class="text-center pe-3 py-3" style="width: 160px;">অ্যাকশন</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($invoices as $inv)
                            @php
                                $isExpired = $inv->valid_until && $inv->valid_until->isPast() && !$inv->valid_until->isToday();
                            @endphp
                            <tr class="{{ $isExpired ? 'table-warning-subtle' : '' }}">
                                <td class="ps-3 fw-bold font-monospace">
                                    <a href="{{ route('admin.accounting.invoices.show', $inv->id) }}" class="text-decoration-none text-primary fw-bold">
                                        {{ $inv->invoice_no }}
                                    </a>
                                    @if($inv->reference_no)
                                        <div class="text-muted small fw-normal" style="font-size: 11px;">সূত্র: {{ $inv->reference_no }}</div>
                                    @endif
                                    <div class="mt-1">
                                        <span class="badge border {{ $inv->category_badge['bg'] }} px-2 py-0.5 fw-semibold" style="font-size: 10px;">
                                            {{ $inv->category_badge['label'] }}
                                        </span>
                                    </div>
                                </td>
                                <td class="text-muted small">
                                    <div class="fw-semibold text-dark">{{ $inv->invoice_date ? $inv->invoice_date->format('d M, Y') : '—' }}</div>
                                    <div class="text-muted" style="font-size: 10.5px;">{{ $inv->invoice_date ? $inv->invoice_date->diffForHumans() : '' }}</div>
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
                                        <div class="text-muted small font-monospace" style="font-size: 11px;"><i class="fas fa-phone me-1 text-success"></i>{{ $inv->customer_phone }}</div>
                                    @endif
                                </td>
                                <td>
                                    @if($inv->subject)
                                        <div class="fw-semibold text-dark text-truncate mb-1" style="max-width: 250px;" title="{{ $inv->subject }}">
                                            <i class="fas fa-file-lines text-warning me-1" style="font-size: 11px;"></i>{{ $inv->subject }}
                                        </div>
                                    @endif
                                    <span class="badge bg-light text-dark border px-2 py-0.5" style="font-size: 11px;">
                                        <i class="fas fa-cubes me-1 text-muted"></i>{{ count($inv->items ?? []) }} টি আইটেম
                                    </span>
                                </td>
                                <td class="fw-bold text-primary font-monospace text-end fs-6">
                                    ৳{{ number_format($inv->grand_total, 2) }}
                                </td>
                                <td class="text-center">
                                    @if($inv->valid_until)
                                        @if($isExpired)
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 rounded-pill">
                                                <i class="fas fa-calendar-xmark me-1"></i>মেয়াদোত্তীর্ণ
                                            </span>
                                            <div class="small text-danger fw-medium mt-1" style="font-size: 10.5px;">{{ $inv->valid_until->format('d M, Y') }}</div>
                                        @else
                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill">
                                                <i class="fas fa-calendar-check me-1"></i>সক্রিয়
                                            </span>
                                            <div class="small text-muted mt-1" style="font-size: 10.5px;">মেয়াদ: {{ $inv->valid_until->format('d M, Y') }}</div>
                                        @endif
                                    @else
                                        <span class="badge bg-light text-muted border px-2 py-1 rounded-pill">ওপেন প্রস্তাবনা</span>
                                    @endif
                                </td>
                                <td class="text-center pe-3">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.accounting.invoices.show', $inv->id) }}" class="btn btn-outline-primary" title="দেখুন ও প্রিন্ট করুন">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        {{-- Quick Convert Dropdown --}}
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-success dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" title="বিল বা চালানে রূপান্তর করুন">
                                                <i class="fas fa-share-nodes"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow-sm rounded-3 border-0 p-2" style="min-width: 190px; font-size: 12.5px;">
                                                <li><h6 class="dropdown-header small text-uppercase fw-bold text-muted px-2 py-1">রূপান্তর করুন:</h6></li>
                                                <li>
                                                    <form action="{{ route('admin.accounting.invoices.convert', $inv->id) }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="target_type" value="invoice">
                                                        <button type="submit" class="dropdown-item rounded-2 py-1.5 fw-semibold d-flex align-items-center gap-2">
                                                            <i class="fas fa-receipt text-primary"></i> চূড়ান্ত বিলে রূপান্তর
                                                        </button>
                                                    </form>
                                                </li>
                                                <li>
                                                    <form action="{{ route('admin.accounting.invoices.convert', $inv->id) }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="target_type" value="challan">
                                                        <button type="submit" class="dropdown-item rounded-2 py-1.5 fw-semibold d-flex align-items-center gap-2">
                                                            <i class="fas fa-truck text-success"></i> ডেলিভারি চালানে রূপান্তর
                                                        </button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                        <a href="{{ route('admin.accounting.invoices.edit', $inv->id) }}" class="btn btn-outline-warning text-dark" title="এডিট করুন">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.accounting.invoices.destroy', $inv->id) }}" method="POST" class="d-inline" data-confirm="আপনি কি নিশ্চিত যে এই ফাইলটি মুছে ফেলতে চান?" data-confirm-title="ফাইল ডিলিট">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="মুছে ফেলুন">
                                                <i class="fas fa-trash-can"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                {{-- Invoices, Challans & All Standard Table Layout --}}
                <table class="table adm-table align-middle mb-0" style="min-width: 1080px;">
                    <thead>
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
                                    <div class="mt-1">
                                        <span class="badge border {{ $inv->category_badge['bg'] }} px-2 py-0.5 fw-semibold" style="font-size: 10px;">
                                            {{ $inv->category_badge['label'] }}
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
                                                <div class="text-primary-emphasis fw-medium mt-1 d-flex align-items-center gap-1.5" style="font-size: 11px; max-width: 280px;" title="Subject: {{ $inv->subject }}">
                                                    <i class="fas fa-heading text-primary opacity-75" style="font-size: 10px;"></i>
                                                    <span class="text-truncate">{{ $inv->subject }}</span>
                                                </div>
                                            @endif
                                        </div>
                                        <a href="{{ route('admin.accounting.customer-ledger.index', ['customer_name' => $inv->customer_name, 'customer_phone' => $inv->customer_phone]) }}" class="badge bg-light text-primary border text-decoration-none px-2 py-1 ms-1" title="View customer ledger">
                                            <i class="fas fa-book-bookmark me-0.5"></i>Ledger
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
                                            <div class="text-muted" style="font-size: 10px;">({{ $inv->payments->count() }} installments)</div>
                                        @endif
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                                <td class="fw-bold font-monospace text-end {{ $inv->due_amount > 0 ? 'text-danger' : 'text-muted' }}">
                                    @if(in_array($inv->type, ['invoice', 'challan']))
                                        ৳{{ number_format($inv->due_amount, 2) }}
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
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
                                <td class="text-center pe-3">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.accounting.invoices.show', $inv->id) }}" class="btn btn-outline-primary" title="View & Print">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.accounting.invoices.edit', $inv->id) }}" class="btn btn-outline-warning text-dark" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.accounting.invoices.destroy', $inv->id) }}" method="POST" class="d-inline" data-confirm="আপনি কি নিশ্চিত যে এই ইনভয়েসটি মুছে ফেলতে চান?" data-confirm-title="ইনভয়েস ডিলিট">
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
            @endif
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
                        <i class="fas fa-palette me-2"></i>Invoice & Memo Settings
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
                            <span class="badge bg-primary text-white">Ratio 2:1</span>
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

                    {{-- Payment QR --}}
                    <div class="card border border-success-subtle rounded-3 p-3 mb-3 bg-success-subtle bg-opacity-10">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label fw-bold text-success mb-0">
                                <i class="fa-solid fa-qrcode me-1"></i> Payment QR
                            </label>
                            <span class="badge bg-success text-white">Bill Only</span>
                        </div>
                        
                        <div class="row g-3">
                            {{-- 1. bKash / Nagad / Rocket QR --}}
                            <div class="col-md-6 border-end">
                                <div class="p-2.5 bg-white rounded-3 border h-100 d-flex flex-column justify-content-between">
                                    <div>
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <span class="small fw-bold text-dark"><i class="fas fa-mobile-screen-button text-primary me-1"></i>bKash / Nagad / Rocket</span>
                                        </div>
                                        <input type="file" name="mfs_qr_file" id="mfsQrFileInput" class="form-control form-control-sm mb-2" accept="image/*" onchange="previewQr(this, 'mfsQrPreviewImg', 'mfsQrStatusText')">
                                        <div class="mb-2">
                                            <label class="form-label small fw-semibold text-muted mb-0.5" style="font-size: 10.5px;">Label Text:</label>
                                            <input type="text" name="mfs_qr_note" class="form-control form-control-sm font-monospace" 
                                                   value="{{ $settings['mfs_qr_note'] ?? 'bkash/nagad/roket' }}" 
                                                   placeholder="bkash/nagad/roket">
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
                                                 style="width: 55px; height: 55px; object-fit: contain; {{ empty($settings['mfs_qr_image']) ? 'opacity: 0.35; filter: grayscale(1);' : '' }}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- 2. Bank Payment QR --}}
                            <div class="col-md-6">
                                <div class="p-2.5 bg-white rounded-3 border h-100 d-flex flex-column justify-content-between">
                                    <div>
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <span class="small fw-bold text-dark"><i class="fa-solid fa-building-columns text-success me-1"></i>Bank Payment</span>
                                        </div>
                                        <input type="file" name="bank_qr_file" id="bankQrFileInput" class="form-control form-control-sm mb-2" accept="image/*" onchange="previewQr(this, 'bankQrPreviewImg', 'bankQrStatusText')">
                                        <div class="mb-2">
                                            <label class="form-label small fw-semibold text-muted mb-0.5" style="font-size: 10.5px;">Label Text:</label>
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
                                                 style="width: 55px; height: 55px; object-fit: contain; {{ empty($settings['bank_qr_image']) ? 'opacity: 0.35; filter: grayscale(1);' : '' }}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- QR Code Size Selector --}}
                            <div class="col-12 mt-2 pt-2 border-top">
                                <div class="row align-items-center">
                                    <div class="col-md-5">
                                        <label class="form-label small fw-bold text-dark mb-0">
                                            <i class="fas fa-expand me-1 text-success"></i>QR Code Size:
                                        </label>
                                    </div>
                                    <div class="col-md-7">
                                        @php $qrCodeSize = $settings['qr_code_size'] ?? '60px'; @endphp
                                        <select name="qr_code_size" id="indexInputQrCodeSize" class="form-select form-select-sm font-monospace fw-bold">
                                            @foreach(['45px'=>'45px (Compact)', '55px'=>'55px (Small)', '60px'=>'60px (Standard / Recommended)', '70px'=>'70px (Medium / Clear)', '80px'=>'80px (Large)', '95px'=>'95px (Extra Large)'] as $qVal => $qLbl)
                                                <option value="{{ $qVal }}" {{ ($qrCodeSize === $qVal) ? 'selected' : '' }}>{{ $qLbl }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Delivery To --}}
                    <div class="card border border-primary-subtle rounded-3 p-3 mb-3 bg-primary bg-opacity-10">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label fw-bold text-primary mb-0">
                                <i class="fas fa-truck-ramp-box me-1"></i> Delivery To
                            </label>
                            <span class="badge bg-primary text-white">Challan Typography</span>
                        </div>

                        <div class="row g-2.5">
                            <div class="col-md-4 col-sm-6">
                                <label class="form-label small fw-semibold text-dark mb-1">
                                    Name
                                </label>
                                <select name="challan_recipient_name_size" class="form-select form-select-sm">
                                    @php $recNameSize = $settings['challan_recipient_name_size'] ?? '13px'; @endphp
                                    @foreach(['11px'=>'11px', '12px'=>'12px', '13px'=>'13px (Default)', '14px'=>'14px', '15px'=>'15px', '16px'=>'16px', '18px'=>'18px'] as $val => $lbl)
                                        <option value="{{ $val }}" {{ ($recNameSize === $val) ? 'selected' : '' }}>{{ $lbl }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4 col-sm-6">
                                <label class="form-label small fw-semibold text-dark mb-1">
                                    Mobile
                                </label>
                                <select name="challan_recipient_phone_size" class="form-select form-select-sm">
                                    @php $recPhoneSize = $settings['challan_recipient_phone_size'] ?? '12px'; @endphp
                                    @foreach(['10.5px'=>'10.5px', '11.5px'=>'11.5px', '12px'=>'12px (Default)', '13px'=>'13px', '14px'=>'14px', '15px'=>'15px'] as $val => $lbl)
                                        <option value="{{ $val }}" {{ ($recPhoneSize === $val) ? 'selected' : '' }}>{{ $lbl }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4 col-sm-6">
                                <label class="form-label small fw-semibold text-dark mb-1">
                                    Address
                                </label>
                                <select name="challan_recipient_address_size" class="form-select form-select-sm">
                                    @php $recAddrSize = $settings['challan_recipient_address_size'] ?? '11.5px'; @endphp
                                    @foreach(['10px'=>'10px', '11px'=>'11px', '11.5px'=>'11.5px (Default)', '12px'=>'12px', '13px'=>'13px', '14px'=>'14px'] as $val => $lbl)
                                        <option value="{{ $val }}" {{ ($recAddrSize === $val) ? 'selected' : '' }}>{{ $lbl }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 col-sm-6">
                                <label class="form-label small fw-semibold text-dark mb-1">
                                    Designation/Org
                                </label>
                                <select name="challan_recipient_desig_size" class="form-select form-select-sm">
                                    @php $recDesigSize = $settings['challan_recipient_desig_size'] ?? '11.5px'; @endphp
                                    @foreach(['10px'=>'10px', '11px'=>'11px', '11.5px'=>'11.5px (Default)', '12px'=>'12px', '13px'=>'13px'] as $val => $lbl)
                                        <option value="{{ $val }}" {{ ($recDesigSize === $val) ? 'selected' : '' }}>{{ $lbl }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 col-sm-12">
                                <label class="form-label small fw-semibold text-dark mb-1">
                                    Signatory Title
                                </label>
                                <input type="text" name="default_creator_designation" class="form-control form-control-sm" 
                                       value="{{ $settings['default_creator_designation'] ?? '' }}" placeholder="Authorized Signatory / Billing Officer">
                            </div>
                        </div>
                    </div>

                    {{-- Quotation & Tender Function Customization Settings --}}
                    <div class="card border border-warning-subtle rounded-3 p-3 mb-3 bg-warning-subtle bg-opacity-15">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label fw-bold text-warning-emphasis mb-0">
                                <i class="fas fa-file-invoice-dollar me-1 text-warning"></i> Quotation & Tender Settings (কোটেশন ও দরপত্র সেটিংস)
                            </label>
                            <span class="badge bg-warning text-dark">Quotation / Tender</span>
                        </div>
                        
                        <div class="row g-2.5">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-dark mb-1">
                                    Quotation Title (কোটেশন শিরোনাম)
                                </label>
                                <input type="text" name="quotation_title_bn" class="form-control form-control-sm" 
                                       value="{{ $settings['quotation_title_bn'] ?? 'মূল্য কোটেশন (PRICE QUOTATION)' }}" 
                                       placeholder="মূল্য কোটেশন (PRICE QUOTATION)">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-dark mb-1">
                                    Tender Title (দরপত্র শিরোনাম)
                                </label>
                                <input type="text" name="tender_title_bn" class="form-control form-control-sm" 
                                       value="{{ $settings['tender_title_bn'] ?? 'দরপত্র প্রস্তাবনা (TENDER PROPOSAL)' }}" 
                                       placeholder="দরপত্র প্রস্তাবনা (TENDER PROPOSAL)">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-dark mb-1">
                                    Default Validity (ডিফল্ট মেয়াদ)
                                </label>
                                <select name="quotation_default_validity_days" class="form-select form-select-sm">
                                    @php $qValDays = (int)($settings['quotation_default_validity_days'] ?? 30); @endphp
                                    @foreach([7=>'৭ দিন (7 Days)', 15=>'১৫ দিন (15 Days)', 30=>'৩০ দিন (30 Days - Default)', 45=>'৪৫ দিন (45 Days)', 60=>'৬০ দিন (60 Days)', 90=>'৯০ দিন (90 Days)'] as $dKey => $dLbl)
                                        <option value="{{ $dKey }}" {{ ($qValDays === $dKey) ? 'selected' : '' }}>{{ $dLbl }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-dark mb-1">
                                    Digit & Number Format (সংখ্যা ফরম্যাট)
                                </label>
                                <select name="digit_language" class="form-select form-select-sm">
                                    @php $dLang = $settings['digit_language'] ?? 'bn'; @endphp
                                    <option value="bn" {{ ($dLang === 'bn') ? 'selected' : '' }}>বাংলা সংখ্যা (১, ২, ৩, ৳)</option>
                                    <option value="en" {{ ($dLang === 'en') ? 'selected' : '' }}>English Digits (1, 2, 3, ৳)</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-dark mb-1">
                                    Default Subject Line (ডিফল্ট বিষয়)
                                </label>
                                <input type="text" name="quotation_default_subject" class="form-control form-control-sm" 
                                       value="{{ $settings['quotation_default_subject'] ?? 'বই প্রকাশনা, মুদ্রণ ও সরবরাহ প্রসঙ্গে' }}" 
                                       placeholder="বই প্রকাশনা, মুদ্রণ ও সরবরাহ প্রসঙ্গে">
                            </div>

                            <div class="col-md-12">
                                <label class="form-label small fw-semibold text-dark mb-1">
                                    Default Quotation Notes (কোটেশন নোট)
                                </label>
                                <input type="text" name="quotation_default_notes" class="form-control form-control-sm" 
                                       value="{{ $settings['quotation_default_notes'] ?? '১. ভ্যাট যুক্ত করা হয়নি। ২. কোটেশনের মেয়াদ ৩০ দিন পর্যন্ত কার্যকর থাকবে।' }}" 
                                       placeholder="১. ভ্যাট যুক্ত করা হয়নি। ২. কোটেশনের মেয়াদ ৩০ দিন পর্যন্ত কার্যকর থাকবে।">
                            </div>

                            <div class="col-md-12">
                                <label class="form-label small fw-semibold text-dark mb-1">
                                    Quotation Terms & Conditions (কোটেশন শর্তাবলী)
                                </label>
                                <textarea name="quotation_default_terms" class="form-control form-control-sm rounded-2" rows="2" 
                                          placeholder="কোটেশনের নির্দিষ্ট শর্তাবলী লিখুন...">{{ $settings['quotation_default_terms'] ?? "১. কার্যাদেশ পাওয়ার পর নির্ধারিত সময়ের মধ্যে ডেলিভারি প্রদান করা হবে।\n২. কাজের পরিধি ও স্পেসিফিকেশন পরিবর্তন হলে দর সমন্বয়যোগ্য।" }}</textarea>
                            </div>
                        </div>
                    </div>

                    {{-- VAT & Tax Deduction (%) Preset Settings --}}
                    <div class="card border border-warning-subtle rounded-3 p-3 mb-3 bg-warning-subtle bg-opacity-10">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label fw-bold text-dark mb-0">
                                <i class="fas fa-percent me-1 text-warning"></i> ভ্যাট ও ট্যাক্স কর্তন হার ও প্রিসেট (%) কনফিগারেশন
                            </label>
                            <span class="badge bg-warning text-dark font-monospace">TDS & VDS</span>
                        </div>
                        <p class="text-muted small mb-3">
                            বিল ও লেজার পরিশোধের সময় স্বয়ংক্রিয় কর্তন হিসাবের জন্য ডিফল্ট হার ও দ্রুত নির্বাচনের প্রিসেট তালিকা নির্ধারণ করুন।
                        </p>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-dark mb-1">
                                    <i class="fas fa-file-invoice-dollar text-primary me-1"></i>ডিফল্ট ভ্যাট কর্তন হার (VDS %)
                                </label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.01" name="default_vat_rate" class="form-control font-monospace fw-bold" 
                                           value="{{ $settings['default_vat_rate'] ?? '7.5' }}" placeholder="7.5">
                                    <span class="input-group-text bg-light fw-bold">%</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-dark mb-1">
                                    <i class="fas fa-landmark text-danger me-1"></i>ডিফল্ট ট্যাক্স কর্তন হার (TDS %)
                                </label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.01" name="default_tax_rate" class="form-control font-monospace fw-bold" 
                                           value="{{ $settings['default_tax_rate'] ?? '5.0' }}" placeholder="5.0">
                                    <span class="input-group-text bg-light fw-bold">%</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-dark mb-1">
                                    কাস্টম ভ্যাট প্রিসেট (%) তালিকা (কমা দিয়ে আলাদা করুন)
                                </label>
                                <input type="text" name="vat_presets" class="form-control form-control-sm font-monospace" 
                                       value="{{ is_array($settings['vat_presets'] ?? null) ? implode(',', $settings['vat_presets']) : ($settings['vat_presets'] ?? '0, 2.5, 5, 7.5, 10, 15') }}" 
                                       placeholder="0, 2.5, 5, 7.5, 10, 15">
                                <small class="text-muted d-block mt-1" style="font-size: 11px;">যেমন: 0, 2.5, 5, 7.5, 10, 15</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-dark mb-1">
                                    কাস্টম ট্যাক্স প্রিসেট (%) তালিকা (কমা দিয়ে আলাদা করুন)
                                </label>
                                <input type="text" name="tax_presets" class="form-control form-control-sm font-monospace" 
                                       value="{{ is_array($settings['tax_presets'] ?? null) ? implode(',', $settings['tax_presets']) : ($settings['tax_presets'] ?? '0, 2, 3, 5, 7, 10') }}" 
                                       placeholder="0, 2, 3, 5, 7, 10">
                                <small class="text-muted d-block mt-1" style="font-size: 11px;">যেমন: 0, 2, 3, 5, 7, 10</small>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Company Name <span class="text-danger">*</span></label>
                            <input type="text" name="business_name" id="indexInputBusinessName" class="form-control" value="{{ $settings['business_name'] ?? 'Idea Publication' }}" required oninput="updateIndexLivePreview()">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tagline</label>
                            <input type="text" name="tagline" id="indexInputTagline" class="form-control" value="{{ $settings['tagline'] ?? 'Book Publication, Printing & Distribution' }}" placeholder="Book Publication, Printing..." oninput="updateIndexLivePreview()">
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Address</label>
                            <input type="text" name="address" id="indexInputAddress" class="form-control" value="{{ $settings['address'] ?? 'Dhaka, Bangladesh' }}" placeholder="e.g. 38 Banglabazar, Dhaka..." oninput="updateIndexLivePreview()">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Phone</label>
                            <input type="text" name="phone" id="indexInputPhone" class="form-control" value="{{ $settings['phone'] ?? '018XXXXXXXX' }}" placeholder="017XXXXXXXX, 018XXXXXXXX" oninput="updateIndexLivePreview()">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email</label>
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
