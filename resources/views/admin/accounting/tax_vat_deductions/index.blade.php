@extends('layouts.admin')

@php
    $settings = $invoiceSettings ?? \App\Http\Controllers\Admin\IdeaAccountingController::getInvoiceSettings();
    $bizLogo = $settings['logo'] ?? '/images/logo.png';
    $logoSrc = \App\Support\SiteSetting::resolveImageUrl($bizLogo, 'images/logo.png') ?: asset('images/logo.png');

    $monthNameBn = '';
    if (!empty($selectedMonth)) {
        try {
            $dt = \Illuminate\Support\Carbon::parse($selectedMonth . '-01');
            $monthsMap = [
                'January' => 'জানুয়ারি', 'February' => 'ফেব্রুয়ারি', 'March' => 'মার্চ',
                'April' => 'এপ্রিল', 'May' => 'মে', 'June' => 'জুন',
                'July' => 'জুলাই', 'August' => 'আগস্ট', 'September' => 'সেপ্টেম্বর',
                'October' => 'অক্টোবর', 'November' => 'নভেম্বর', 'December' => 'ডিসেম্বর',
            ];
            $monthNameBn = ($monthsMap[$dt->format('F')] ?? $dt->format('F')) . ' ' . $dt->format('Y');
        } catch (\Throwable $e) {
            $monthNameBn = $selectedMonth;
        }
    }

    $pageTitle = $monthNameBn ? "উৎসে কর ও মূসক কর্তন রেজিস্টার — {$monthNameBn}" : "উৎসে কর ও মূসক কর্তন রেজিস্টার ও মাসিক প্রতিবেদন";
@endphp

@section('title', $pageTitle)
@section('heading')
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <span class="fs-5 fw-bold text-dark">
            <i class="fa-solid fa-scale-balanced text-primary me-2"></i>উৎসে কর ও মূসক কর্তন রেজিস্টার (TDS & VDS Report)
        </span>
        @if($monthNameBn)
            <span class="badge bg-primary-subtle text-primary border rounded-pill px-3 py-1 font-monospace">
                {{ $monthNameBn }}
            </span>
        @endif
    </div>
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.accounting.index') }}">Accounting</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.accounting.invoices.index') }}">Invoices & Challans</a></li>
    <li class="breadcrumb-item active" aria-current="page">TDS & VDS Register</li>
@endsection

@section('actions')
    <div class="d-flex flex-wrap gap-2 align-items-center">
        {{-- Customize % Presets Button --}}
        <button type="button" class="btn btn-outline-dark btn-sm rounded-pill px-3 shadow-2xs fw-semibold" data-bs-toggle="modal" data-bs-target="#customPercentPresetsModal">
            <i class="fa-solid fa-sliders text-warning me-1.5"></i> % প্রিসেট কাস্টমাইজ
        </button>

        {{-- Export Tools Dropdown --}}
        <div class="dropdown">
            <button class="btn btn-white border shadow-2xs btn-sm rounded-pill px-3 fw-semibold dropdown-toggle text-dark" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa-solid fa-file-export me-1 text-primary"></i> এক্সপোর্ট
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow rounded-3 border-0 p-2" style="min-width: 220px;">
                <li><h6 class="dropdown-header small text-uppercase fw-bold text-muted px-2 py-1">এক্সপোর্ট ফরম্যাট:</h6></li>
                <li>
                    <button type="button" class="dropdown-item rounded-2 py-2 fw-semibold" onclick="exportDeductionsToCSV('tax-vat-deductions-{{ $selectedMonth ?: date('Y-m') }}.csv')">
                        <i class="fa-solid fa-file-csv text-success me-2"></i> CSV / Excel ফাইল ডাউনলোড
                    </button>
                </li>
                <li>
                    <button type="button" class="dropdown-item rounded-2 py-2 fw-semibold" onclick="copyDeductionsToClipboard()">
                        <i class="fa-solid fa-copy text-info me-2"></i> ক্লিপবোর্ডে কপি করুন
                    </button>
                </li>
            </ul>
        </div>

        {{-- Print Statement Button --}}
        <button type="button" class="btn btn-primary btn-sm rounded-pill px-3.5 shadow-sm fw-semibold" onclick="window.print()">
            <i class="fa-solid fa-print me-1.5"></i> প্রিন্ট / PDF প্রতিবেদন
        </button>

        <a href="{{ route('admin.accounting.invoices.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-xs">
            <i class="fa-solid fa-file-invoice me-1"></i> ইনভয়েস ড্যাশবোর্ড
        </a>

        <a href="{{ route('admin.accounting.customer-ledger.index') }}" class="btn btn-outline-info text-dark btn-sm rounded-pill px-3 shadow-xs fw-semibold">
            <i class="fa-solid fa-book-bookmark me-1 text-primary"></i> গ্রাহক খতিয়ান
        </a>
    </div>
@endsection

@section('content')
<style>
    .stat-card-tax {
        background: #ffffff;
        border-radius: 14px;
        border: 1px solid #e2e8f0;
        padding: 16px 20px;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .stat-card-tax:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
    }
    .month-pill-btn {
        font-size: 11.5px;
        padding: 4px 12px;
        border-radius: 999px;
        border: 1px solid #cbd5e1;
        background: #ffffff;
        color: #334155;
        font-weight: 600;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    .month-pill-btn:hover, .month-pill-btn.active {
        background: #0ea5e9;
        border-color: #0ea5e9;
        color: #ffffff !important;
    }
    .month-pill-btn .badge-pill-count {
        font-size: 10px;
        padding: 1px 6px;
        border-radius: 999px;
        background: rgba(0,0,0,0.08);
    }
    .month-pill-btn.active .badge-pill-count {
        background: rgba(255,255,255,0.25);
        color: #ffffff;
    }
    .table-deductions th {
        font-size: 11.5px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        font-weight: 700;
        background: #f8fafc;
        border-bottom: 2px solid #cbd5e1;
        white-space: nowrap;
    }
    .table-deductions td {
        font-size: 12.5px;
        vertical-align: middle;
    }
    .print-only-header {
        display: none;
    }
    @media print {
        body {
            background: #ffffff !important;
            padding: 0 !important;
            margin: 0 !important;
            color: #000000 !important;
            font-size: 11px !important;
        }
        .adm-side, .adm-header, .adm-topbar, .breadcrumb, .btn, .no-print, footer, nav, .alert, .filter-card, .pagination {
            display: none !important;
        }
        .content-wrapper, .container-fluid, .content, .adm-main, .adm-content {
            padding: 0 !important;
            margin: 0 !important;
            background: #ffffff !important;
        }
        .print-only-header {
            display: block !important;
            border-bottom: 2px solid #000000;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }
        .card {
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
        }
        .table-responsive {
            overflow: visible !important;
        }
        .table-deductions {
            width: 100% !important;
            border: 1px solid #000000 !important;
            font-size: 10.5px !important;
        }
        .table-deductions th, .table-deductions td {
            border: 1px solid #000000 !important;
            padding: 4px 6px !important;
            color: #000000 !important;
        }
        .table-deductions th {
            background: #f1f5f9 !important;
        }
        .print-signature-section {
            display: block !important;
            margin-top: 50px !important;
            page-break-inside: avoid;
        }
    }
</style>

<div class="container-fluid py-2">

    {{-- Official Print Header (Visible Only in Print / PDF) --}}
    <div class="print-only-header">
        <div class="row align-items-center">
            <div class="col-8">
                <div class="d-flex align-items-center gap-3">
                    @if(!empty($logoSrc))
                        <img src="{{ $logoSrc }}" alt="Logo" style="height: 48px; max-width: 140px; object-fit: contain;">
                    @endif
                    <div>
                        <h4 class="fw-bold mb-0 text-dark">{{ $settings['business_name'] ?? 'আইডিয়া প্রকাশন' }}</h4>
                        <div class="small text-muted">{{ $settings['address'] ?? '' }} | ফোন: {{ $settings['phone'] ?? '' }}</div>
                    </div>
                </div>
            </div>
            <div class="col-4 text-end">
                <h5 class="fw-bold mb-0 text-dark">উৎসে কর ও মূসক কর্তন রেজিস্টার</h5>
                <div class="small fw-semibold text-primary">হিসাবকাল: {{ $monthNameBn ?: ($dateFrom ? "{$dateFrom} হতে {$dateTo}" : 'সকল সময়ের সারাংশ') }}</div>
                <div class="small text-muted">প্রিন্ট তারিখ: {{ date('d/m/Y h:i A') }}</div>
            </div>
        </div>
    </div>

    {{-- Top Executive Metrics & Deduction Summary Cards --}}
    <div class="row g-3 mb-4 no-print">
        <div class="col-6 col-md-3">
            <div class="stat-card-tax">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold">মোট সমন্বিত বিল দাবি</div>
                        <div class="fs-4 fw-bold text-dark font-monospace mt-1">৳{{ number_format($totalSettledAmount, 2) }}</div>
                        <div class="text-muted small" style="font-size: 11px;">সর্বমোট {{ $deductions->count() }}টি লেনদেন ({{ $totalClientsCount }} জন গ্রাহক)</div>
                    </div>
                    <div class="rounded-circle bg-primary-subtle p-3 text-primary">
                        <i class="fa-solid fa-file-invoice-dollar fs-5"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="stat-card-tax border-success-subtle bg-success-subtle bg-opacity-10">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-success small fw-semibold">নিট নগদ/চেক আদায়</div>
                        <div class="fs-4 fw-bold text-success font-monospace mt-1">৳{{ number_format($totalNetCollected, 2) }}</div>
                        <div class="text-success small" style="font-size: 11px;">আদায় হার: {{ $totalSettledAmount > 0 ? round(($totalNetCollected / $totalSettledAmount) * 100, 1) : 0 }}%</div>
                    </div>
                    <div class="rounded-circle bg-success-subtle p-3 text-success">
                        <i class="fa-solid fa-money-bill-wave fs-5"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="stat-card-tax border-warning-subtle bg-warning-subtle bg-opacity-10">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-warning-emphasis small fw-semibold">উৎসে মূসক কর্তন (VDS)</div>
                        <div class="fs-4 fw-bold text-warning-emphasis font-monospace mt-1">৳{{ number_format($totalVatDeducted, 2) }}</div>
                        <div class="text-muted small" style="font-size: 11px;">সরকারি কোষাগারে জমাকৃত ভ্যাট</div>
                    </div>
                    <div class="rounded-circle bg-warning-subtle p-3 text-warning-emphasis">
                        <i class="fa-solid fa-receipt fs-5"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="stat-card-tax border-danger-subtle bg-danger-subtle bg-opacity-10">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-danger small fw-semibold">উৎসে আয়কর কর্তন (TDS/AIT)</div>
                        <div class="fs-4 fw-bold text-danger font-monospace mt-1">৳{{ number_format($totalTaxDeducted, 2) }}</div>
                        <div class="text-danger small" style="font-size: 11px;">সর্বমোট সরকারি কর্তন: ৳{{ number_format($grandTotalDeductions, 2) }}</div>
                    </div>
                    <div class="rounded-circle bg-danger-subtle p-3 text-danger">
                        <i class="fa-solid fa-scale-balanced fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter & Month Picker Bar --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white no-print filter-card">
        <div class="card-body p-3.5">
            <form action="{{ route('admin.accounting.tax-vat-deductions.index') }}" method="GET" id="deductionsFilterForm">
                <div class="row g-2.5 align-items-end">
                    {{-- Month Picker --}}
                    <div class="col-md-3 col-sm-6">
                        <label class="form-label small fw-bold text-dark mb-1">
                            <i class="fa-solid fa-calendar-days text-primary me-1"></i>হিসাব মাস নির্বাচন:
                        </label>
                        <input type="month" name="month" id="filterMonthInput" class="form-control form-control-sm font-monospace fw-semibold" value="{{ $selectedMonth }}" onchange="document.getElementById('deductionsFilterForm').submit()">
                    </div>

                    {{-- Deduction Type Filter --}}
                    <div class="col-md-2 col-sm-6">
                        <label class="form-label small fw-bold text-dark mb-1">কর্তন ধরন:</label>
                        <select name="type" class="form-select form-select-sm" onchange="document.getElementById('deductionsFilterForm').submit()">
                            <option value="all" {{ ($deductionType ?? 'all') === 'all' ? 'selected' : '' }}>সকল কর্তন (All)</option>
                            <option value="vat_only" {{ ($deductionType ?? '') === 'vat_only' ? 'selected' : '' }}>শুধু মূসক/ভ্যাট (VDS)</option>
                            <option value="tax_only" {{ ($deductionType ?? '') === 'tax_only' ? 'selected' : '' }}>শুধু আয়কর/ট্যাক্স (TDS)</option>
                        </select>
                    </div>

                    {{-- Search / Party / Challan --}}
                    <div class="col-md-4 col-sm-8">
                        <label class="form-label small fw-bold text-dark mb-1">সার্চ (গ্রাহক / বিল নং / ট্রেজারি চালান নং):</label>
                        <div class="input-group input-group-sm">
                            <input type="text" name="search" class="form-control" placeholder="গ্রাহকের নাম, মোবাইল, বিল #, চালান #..." value="{{ $search }}">
                            <button class="btn btn-primary" type="submit">
                                <i class="fa-solid fa-magnifying-glass me-1"></i> খুঁজুন
                            </button>
                        </div>
                    </div>

                    {{-- Quick Filter Presets --}}
                    <div class="col-md-3 col-sm-4 d-flex gap-1.5 justify-content-md-end">
                        <a href="{{ route('admin.accounting.tax-vat-deductions.index', ['month' => date('Y-m')]) }}" class="btn btn-sm btn-outline-primary rounded-pill px-2.5 py-1 {{ $selectedMonth === date('Y-m') ? 'active' : '' }}" title="চলতি মাস">
                            চলতি মাস
                        </a>
                        <a href="{{ route('admin.accounting.tax-vat-deductions.index', ['month' => date('Y-m', strtotime('-1 month'))]) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5 py-1 {{ $selectedMonth === date('Y-m', strtotime('-1 month')) ? 'active' : '' }}" title="গত মাস">
                            গত মাস
                        </a>
                        <a href="{{ route('admin.accounting.tax-vat-deductions.index', ['all_time' => 1]) }}" class="btn btn-sm btn-outline-dark rounded-pill px-2.5 py-1 {{ empty($selectedMonth) && empty($dateFrom) ? 'active' : '' }}" title="সকল সময়ের হিসাব">
                            সকল
                        </a>
                    </div>
                </div>

                {{-- Historical Months Quick Badges --}}
                @if($monthlyDeductionSummaries->isNotEmpty())
                    <div class="mt-3 pt-2.5 border-top d-flex align-items-center gap-1.5 flex-wrap">
                        <span class="text-muted small fw-bold me-1" style="font-size: 11px;">
                            <i class="fa-solid fa-clock-rotate-left me-1"></i>বিগত মাসসমূহ:
                        </span>
                        @foreach($monthlyDeductionSummaries as $ms)
                            @php
                                $mDt = \Illuminate\Support\Carbon::parse($ms->ym . '-01');
                                $mLabel = ($monthsMap[$mDt->format('F')] ?? $mDt->format('M')) . ' ' . $mDt->format('y');
                            @endphp
                            <a href="{{ route('admin.accounting.tax-vat-deductions.index', ['month' => $ms->ym]) }}" class="month-pill-btn {{ $selectedMonth === $ms->ym ? 'active' : '' }}">
                                <span>{{ $mLabel }}</span>
                                <span class="badge-pill-count">{{ $ms->count }}টি (৳{{ number_format($ms->sum_vat + $ms->sum_tax, 0) }})</span>
                            </a>
                        @endforeach
                    </div>
                @endif
            </form>
        </div>
    </div>

    {{-- Main Deductions Register Table --}}
    <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-4">
        <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="fa-solid fa-table-list text-primary me-2"></i>উৎসে ভ্যাট ও ট্যাক্স কর্তনের বিস্তারিত তালিকা ({{ $deductions->count() }}টি রেকর্ড)
                </h6>
                <small class="text-muted">
                    হিসাবকাল: <strong class="text-dark">{{ $monthNameBn ?: ($dateFrom ? "{$dateFrom} হতে {$dateTo}" : 'সকল সময়ের সারাংশ') }}</strong>
                </small>
            </div>

            <div class="d-flex align-items-center gap-2 no-print">
                <span class="badge bg-warning-subtle text-dark border font-monospace px-2.5 py-1">
                    মোট ভ্যাট/ট্যাক্স কর্তন: ৳{{ number_format($grandTotalDeductions, 2) }}
                </span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover table-bordered table-deductions align-middle mb-0" id="deductionsTable">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 40px;">ক্র.</th>
                        <th style="width: 90px;">জমার তারিখ</th>
                        <th>গ্রাহক / প্রতিষ্ঠানের নাম</th>
                        <th>বিল নং ও তারিখ</th>
                        <th>রসিদ নং</th>
                        <th class="text-end">মোট সমন্বয় (৳)</th>
                        <th class="text-end text-success">নিট প্রাপ্তি (৳)</th>
                        <th class="text-center">মাধ্যম / চেক নং</th>
                        <th class="text-end text-warning-emphasis">মূসক (VDS)</th>
                        <th class="text-end text-danger">আয়কর (TDS)</th>
                        <th class="text-end">অন্যান্য</th>
                        <th>চালান / প্রত্যয়ন নং</th>
                        <th class="no-print text-center" style="width: 100px;">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($deductions as $idx => $d)
                        @php
                            $inv = $d->invoice;
                        @endphp
                        <tr>
                            <td class="text-center text-muted fw-semibold">{{ $idx + 1 }}</td>
                            <td class="font-monospace fw-semibold text-dark">
                                {{ $d->payment_date ? $d->payment_date->format('d/m/Y') : '—' }}
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ $d->party_name }}</div>
                                @if($inv && $inv->customer_org)
                                    <div class="text-muted small" style="font-size: 11px;">{{ $inv->customer_org }}</div>
                                @endif
                                @if($d->party_phone && $d->party_phone !== '—')
                                    <div class="text-secondary font-monospace" style="font-size: 11px;"><i class="fa-solid fa-phone me-1"></i>{{ $d->party_phone }}</div>
                                @endif
                            </td>
                            <td>
                                @if($inv)
                                    <a href="{{ route('admin.accounting.invoices.show', $inv->id) }}" class="fw-bold font-monospace text-primary text-decoration-none" target="_blank">
                                        #{{ $inv->invoice_no }}
                                    </a>
                                    <div class="text-muted" style="font-size: 10.5px;">{{ $inv->invoice_date ? $inv->invoice_date->format('d/m/Y') : '' }}</div>
                                @else
                                    <span class="badge bg-light text-dark border font-monospace">চলতি খাতা</span>
                                @endif
                            </td>
                            <td>
                                <span class="font-monospace fw-bold text-secondary">#{{ $d->payment_no }}</span>
                            </td>
                            <td class="text-end font-monospace fw-bold text-dark fs-7">
                                ৳{{ number_format($d->amount, 2) }}
                            </td>
                            <td class="text-end font-monospace fw-bold text-success">
                                ৳{{ number_format($d->effective_net_amount, 2) }}
                            </td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark border px-2 py-0.5" style="font-size: 11px;">
                                    {{ \App\Models\IdeaInvoicePayment::paymentMethods()[$d->payment_method] ?? ucfirst($d->payment_method) }}
                                </span>
                                @if($d->transaction_ref)
                                    <div class="text-muted font-monospace mt-0.5" style="font-size: 10.5px;">{{ $d->transaction_ref }}</div>
                                @endif
                            </td>
                            <td class="text-end font-monospace">
                                @if((float)$d->vat_deduction_amount > 0)
                                    <div class="fw-bold text-warning-emphasis">৳{{ number_format($d->vat_deduction_amount, 2) }}</div>
                                    <span class="badge bg-warning-subtle text-dark border" style="font-size: 10px;">{{ $d->vat_deduction_rate }}%</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-end font-monospace">
                                @if((float)$d->tax_deduction_amount > 0)
                                    <div class="fw-bold text-danger">৳{{ number_format($d->tax_deduction_amount, 2) }}</div>
                                    <span class="badge bg-danger-subtle text-danger border" style="font-size: 10px;">{{ $d->tax_deduction_rate }}%</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-end font-monospace">
                                @if((float)$d->other_deduction_amount > 0)
                                    <span class="text-danger fw-semibold">৳{{ number_format($d->other_deduction_amount, 2) }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if($d->deduction_challan_no)
                                    <div class="fw-bold font-monospace text-primary" style="font-size: 11.5px;">
                                        <i class="fa-solid fa-file-shield me-1"></i>{{ $d->deduction_challan_no }}
                                    </div>
                                @else
                                    <span class="text-muted small">চালান নেই</span>
                                @endif
                                @if($d->deduction_notes)
                                    <div class="text-muted" style="font-size: 10.5px;">{{ $d->deduction_notes }}</div>
                                @endif
                            </td>
                            <td class="text-center no-print">
                                <a href="{{ route('admin.accounting.invoices.payments.receipt', $d->id) }}" class="btn btn-xs btn-outline-success rounded-pill px-2.5 py-1 fw-bold shadow-2xs" target="_blank" title="প্রাপ্তিস্বীকারপত্র ও রসিদ দেখুন">
                                    <i class="fa-solid fa-file-shield me-1"></i>রসিদ
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="13" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-scale-balanced fs-1 mb-2 d-block text-secondary opacity-50"></i>
                                এই হিসাবকালে কোনো ভ্যাট বা ট্যাক্স কর্তনযুক্ত লেনদেন পাওয়া যায়নি।
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($deductions->isNotEmpty())
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td colspan="5" class="text-end text-dark">সর্বমোট (Total):</td>
                            <td class="text-end font-monospace text-dark fs-7">৳{{ number_format($totalSettledAmount, 2) }}</td>
                            <td class="text-end font-monospace text-success fs-7">৳{{ number_format($totalNetCollected, 2) }}</td>
                            <td class="text-center">—</td>
                            <td class="text-end font-monospace text-warning-emphasis fs-7">৳{{ number_format($totalVatDeducted, 2) }}</td>
                            <td class="text-end font-monospace text-danger fs-7">৳{{ number_format($totalTaxDeducted, 2) }}</td>
                            <td class="text-end font-monospace text-danger">৳{{ number_format($totalOtherDeducted, 2) }}</td>
                            <td colspan="2" class="text-dark small">মোট কর্তন: ৳{{ number_format($grandTotalDeductions, 2) }}</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

    {{-- Official Print Signature Section (Visible Only on Print) --}}
    <div class="print-signature-section d-none">
        <div class="row text-center pt-4" style="margin-top: 60px;">
            <div class="col-4">
                <div class="border-top border-dark pt-1 mx-auto" style="width: 170px;">
                    <div class="fw-bold small text-dark">প্রস্তুতকারী হিসাবরক্ষক</div>
                    <div class="text-muted" style="font-size: 10.5px;">Prepared By Accounts</div>
                </div>
            </div>
            <div class="col-4">
                <div class="border-top border-dark pt-1 mx-auto" style="width: 180px;">
                    <div class="fw-bold small text-dark">অভ্যন্তরীণ নিরীক্ষক / ভ্যাট কর্মকর্তা</div>
                    <div class="text-muted" style="font-size: 10.5px;">Internal Auditor / VAT Officer</div>
                </div>
            </div>
            <div class="col-4">
                <div class="border-top border-dark pt-1 mx-auto" style="width: 180px;">
                    <div class="fw-bold small text-dark">অনুমোদনকারী কর্তৃপক্ষ</div>
                    <div class="text-muted" style="font-size: 10.5px;">Managing Authority / CEO</div>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- % PRESETS CUSTOMIZATION MODAL --}}
<div class="modal fade" id="customPercentPresetsModal" tabindex="-1" aria-labelledby="customPercentPresetsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form action="{{ route('admin.accounting.settings.update') }}" method="POST">
                @csrf
                <input type="hidden" name="business_name" value="{{ $settings['business_name'] ?? 'আইডিয়া প্রকাশন' }}">
                
                <div class="modal-header bg-dark text-white py-3">
                    <h5 class="modal-title fw-bold" id="customPercentPresetsModalLabel">
                        <i class="fa-solid fa-sliders text-warning me-2"></i>ভ্যাট ও ট্যাক্স শতকরা (%) প্রিসেট কাস্টমাইজ
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body p-4">
                    <p class="text-muted small mb-3">
                        এখানে আপনার প্রতিষ্ঠানের জন্য ডিফল্ট ভ্যাট এবং ট্যাক্স কর্তন শতকরা হার এবং ক্যালকুলেটরের দ্রুত ক্লিকযোগ্য বাটনসমূহ কাস্টমাইজ করতে পারেন:
                    </p>

                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold text-dark">ডিফল্ট ভ্যাট হার (VDS %):</label>
                            <div class="input-group">
                                <input type="number" step="0.01" min="0" max="100" name="default_vat_rate" class="form-control font-monospace fw-bold" value="{{ $settings['default_vat_rate'] ?? '7.5' }}">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-dark">ডিফল্ট ট্যাক্স হার (TDS %):</label>
                            <div class="input-group">
                                <input type="number" step="0.01" min="0" max="100" name="default_tax_rate" class="form-control font-monospace fw-bold" value="{{ $settings['default_tax_rate'] ?? '5.0' }}">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">ভ্যাট কুইক প্রিসেট বাটনসমূহ (কমা দিয়ে লিখুন):</label>
                        <input type="text" name="vat_presets" class="form-control font-monospace" value="{{ $settings['vat_presets'] ?? '0, 5, 7.5, 10, 15' }}" placeholder="0, 5, 7.5, 10, 15">
                        <div class="form-text text-muted" style="font-size: 11px;">যেমন: 0, 5, 7.5, 10, 15</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">ট্যাক্স কুইক প্রিসেট বাটনসমূহ (কমা দিয়ে লিখুন):</label>
                        <input type="text" name="tax_presets" class="form-control font-monospace" value="{{ $settings['tax_presets'] ?? '0, 2, 3, 5, 7, 10' }}" placeholder="0, 2, 3, 5, 7, 10">
                        <div class="form-text text-muted" style="font-size: 11px;">যেমন: 0, 2, 3, 5, 7, 10</div>
                    </div>
                </div>

                <div class="modal-footer bg-light p-3">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">বাতিল</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">
                        <i class="fa-solid fa-save me-1.5"></i> প্রিসেট সেভ করুন
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Export Table Data to UTF-8 CSV
    function exportDeductionsToCSV(filename) {
        const table = document.getElementById('deductionsTable');
        if (!table) return;

        let csv = [];
        const rows = table.querySelectorAll('tr');

        for (let i = 0; i < rows.length; i++) {
            const row = [];
            const cols = rows[i].querySelectorAll('td, th');
            const len = cols.length > 1 ? cols.length - 1 : cols.length; // skip last action col
            for (let j = 0; j < len; j++) {
                let text = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, ' ').replace(/\s+/g, ' ').trim();
                text = text.replace(/"/g, '""');
                row.push('"' + text + '"');
            }
            csv.push(row.join(','));
        }

        const csvContent = '\uFEFF' + csv.join('\r\n');
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', filename || 'tax-vat-deductions.csv');
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    // Copy Table to Clipboard
    function copyDeductionsToClipboard() {
        const table = document.getElementById('deductionsTable');
        if (!table) return;

        let text = [];
        const rows = table.querySelectorAll('tr');

        for (let i = 0; i < rows.length; i++) {
            const row = [];
            const cols = rows[i].querySelectorAll('td, th');
            const len = cols.length > 1 ? cols.length - 1 : cols.length;
            for (let j = 0; j < len; j++) {
                row.push(cols[j].innerText.replace(/(\r\n|\n|\r)/gm, ' ').replace(/\s+/g, ' ').trim());
            }
            text.push(row.join('\t'));
        }

        navigator.clipboard.writeText(text.join('\n')).then(() => {
            alert('উৎসে কর ও মূসক কর্তনের টেবিল ক্লিপবোর্ডে কপি হয়েছে! এটি Excel বা Google Sheet-এ পেস্ট করতে পারবেন।');
        });
    }
</script>
@endsection
