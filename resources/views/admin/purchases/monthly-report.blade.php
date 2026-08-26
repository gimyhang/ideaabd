@extends('layouts.admin')

@section('title', 'মাসিক ক্রয় ও বিক্রয় রিপোর্ট')
@section('heading', 'মাসিক ক্রয় ও বিক্রয় আর্থিক বিবরণী')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.purchases.index') }}">Purchases & Inventory</a></li>
    <li class="breadcrumb-item active" aria-current="page">মাসিক ক্রয়-বিক্রয় রিপোর্ট</li>
@endsection

@section('actions')
    <div class="d-flex align-items-center gap-2">
        <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-xs" onclick="window.print()">
            <i class="fa-solid fa-print me-1"></i> প্রিন্ট রিপোর্ট
        </button>
        <a href="{{ route('admin.purchases.index') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-xs">
            <i class="fa-solid fa-list-check me-1"></i> ক্রয় তালিকা
        </a>
    </div>
@endsection

@php
    $bengaliMonths = [
        1 => 'জানুয়ারি', 2 => 'ফেব্রুয়ারি', 3 => 'মার্চ', 4 => 'এপ্রিল',
        5 => 'মে', 6 => 'জুন', 7 => 'জুলাই', 8 => 'আগস্ট',
        9 => 'সেপ্টেম্বর', 10 => 'অক্টোবর', 11 => 'নভেম্বর', 12 => 'ডিসেম্বর'
    ];
    $currentMonthName = $bengaliMonths[$month] ?? date('F', mktime(0,0,0,$month,1));

    $prevMonth = $month - 1;
    $prevYear = $year;
    if ($prevMonth < 1) { $prevMonth = 12; $prevYear--; }

    $nextMonth = $month + 1;
    $nextYear = $year;
    if ($nextMonth > 12) { $nextMonth = 1; $nextYear++; }
@endphp

@section('content')
<div class="monthly-report-wrapper pb-5">

    {{-- Top Filter & Navigation Bar --}}
    <div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
        <div class="card-body p-3 p-md-4">
            <form action="{{ route('admin.purchases.monthly-report') }}" method="GET" class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="rounded-circle bg-primary bg-opacity-10 text-primary p-2.5 d-inline-flex align-items-center justify-content-center">
                        <i class="fa-solid fa-calendar-check fs-5"></i>
                    </span>
                    <div>
                        <h5 class="fw-bold mb-0 text-dark">{{ $currentMonthName }} {{ $year }} — আর্থিক হিসাব ও রিপোর্ট</h5>
                        <small class="text-muted">{{ date('d M, Y', strtotime($startDate)) }} থেকে {{ date('d M, Y', strtotime($endDate)) }}</small>
                    </div>
                </div>

                <div class="d-flex flex-wrap align-items-center gap-2">
                    {{-- Quick Prev / Next Buttons --}}
                    <div class="btn-group btn-group-sm rounded-pill shadow-2xs">
                        <a href="{{ route('admin.purchases.monthly-report', ['month' => $prevMonth, 'year' => $prevYear]) }}" class="btn btn-outline-secondary px-3 py-1.5" title="পূর্ববর্তী মাস">
                            <i class="fa-solid fa-chevron-left me-1"></i> {{ $bengaliMonths[$prevMonth] ?? '' }}
                        </a>
                        <a href="{{ route('admin.purchases.monthly-report', ['month' => $nextMonth, 'year' => $nextYear]) }}" class="btn btn-outline-secondary px-3 py-1.5" title="পরবর্তী মাস">
                            {{ $bengaliMonths[$nextMonth] ?? '' }} <i class="fa-solid fa-chevron-right ms-1"></i>
                        </a>
                    </div>

                    {{-- Month Dropdown --}}
                    <select name="month" class="form-select form-select-sm rounded-pill px-3 py-1.5 fw-semibold border-secondary-subtle" style="width: 130px;" onchange="this.form.submit()">
                        @foreach($bengaliMonths as $mNum => $mTitle)
                            <option value="{{ $mNum }}" @selected($month == $mNum)>{{ $mTitle }}</option>
                        @endforeach
                    </select>

                    {{-- Year Dropdown --}}
                    <select name="year" class="form-select form-select-sm rounded-pill px-3 py-1.5 fw-semibold border-secondary-subtle" style="width: 100px;" onchange="this.form.submit()">
                        @for($y = date('Y') - 3; $y <= date('Y') + 2; $y++)
                            <option value="{{ $y }}" @selected($year == $y)>{{ $y }}</option>
                        @endfor
                    </select>

                    <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3 py-1.5 fw-bold shadow-xs">
                        <i class="fa-solid fa-filter me-1"></i> ফিল্টার
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Main Financial KPI Highlights --}}
    <div class="row g-3 mb-4">
        
        {{-- Total Sales Revenue --}}
        <div class="col-xl-3 col-md-6 col-12">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-3.5 h-100 position-relative overflow-hidden" style="border-left: 4px solid #10b981 !important;">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="small fw-bold text-muted text-uppercase">মোট বই বিক্রয় (Sales)</span>
                    <span class="rounded-3 bg-success-subtle text-success p-2"><i class="fa-solid fa-chart-line-up fs-6"></i></span>
                </div>
                <h3 class="fw-bold text-success mb-1">৳{{ number_format($totalSalesAmount, 2) }}</h3>
                <div class="d-flex align-items-center justify-content-between text-muted small mt-2 pt-1 border-top" style="font-size: 11.5px;">
                    <span>মোট অর্ডার: <strong class="text-dark">{{ $totalOrdersCount }}টি</strong></span>
                    <span>ডেলিভার্ড: <strong class="text-success">৳{{ number_format($deliveredSalesAmount, 0) }}</strong></span>
                </div>
            </div>
        </div>

        {{-- Total Purchase Expenditure --}}
        <div class="col-xl-3 col-md-6 col-12">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-3.5 h-100 position-relative overflow-hidden" style="border-left: 4px solid #ef4444 !important;">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="small fw-bold text-muted text-uppercase">সর্বমোট ক্রয় ও ব্যয় (Purchases)</span>
                    <span class="rounded-3 bg-danger-subtle text-danger p-2"><i class="fa-solid fa-cart-shopping fs-6"></i></span>
                </div>
                <h3 class="fw-bold text-danger mb-1">৳{{ number_format($totalPurchaseAmount, 2) }}</h3>
                <div class="d-flex align-items-center justify-content-between text-muted small mt-2 pt-1 border-top" style="font-size: 11.5px;">
                    <span>পরিশোধ: <strong class="text-success">৳{{ number_format($totalPurchasePaid, 0) }}</strong></span>
                    <span>বকেয়া: <strong class="text-danger">৳{{ number_format($totalPurchaseDue, 0) }}</strong></span>
                </div>
            </div>
        </div>

        {{-- Net Margin / Balance --}}
        <div class="col-xl-3 col-md-6 col-12">
            @php $isProfit = $netBalance >= 0; @endphp
            <div class="card border-0 shadow-sm rounded-4 bg-white p-3.5 h-100 position-relative overflow-hidden" style="border-left: 4px solid {{ $isProfit ? '#2563eb' : '#f59e0b' }} !important;">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="small fw-bold text-muted text-uppercase">চলতি মাসের নিট ব্যালেন্স</span>
                    <span class="rounded-3 {{ $isProfit ? 'bg-primary-subtle text-primary' : 'bg-warning-subtle text-warning-emphasis' }} p-2">
                        <i class="fa-solid fa-scale-balanced fs-6"></i>
                    </span>
                </div>
                <h3 class="fw-bold {{ $isProfit ? 'text-primary' : 'text-warning' }} mb-1">
                    {{ $isProfit ? '+' : '' }}৳{{ number_format($netBalance, 2) }}
                </h3>
                <div class="text-muted small mt-2 pt-1 border-top" style="font-size: 11.5px;">
                    <span>{{ $isProfit ? '🟢 বিক্রয় ক্রয়ের চেয়ে বেশি রয়েছে' : '🟠 ক্রয় ও উৎপাদন ব্যয় বিক্রয়ের চেয়ে বেশি' }}</span>
                </div>
            </div>
        </div>

        {{-- Total Invoices Count --}}
        <div class="col-xl-3 col-md-6 col-12">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-3.5 h-100 position-relative overflow-hidden" style="border-left: 4px solid #8b5cf6 !important;">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="small fw-bold text-muted text-uppercase">ক্রয় চালানের সংখ্যা</span>
                    <span class="rounded-3 bg-purple-subtle text-purple p-2"><i class="fa-solid fa-receipt fs-6"></i></span>
                </div>
                <h3 class="fw-bold text-dark mb-1">{{ $purchases->count() }}টি চালান</h3>
                <div class="d-flex align-items-center justify-content-between text-muted small mt-2 pt-1 border-top" style="font-size: 11.5px;">
                    <span>বই: <strong>{{ $booksPurchases->count() }}টি</strong></span>
                    <span>কাঁচামাল: <strong>{{ $rawPurchases->count() }}টি</strong></span>
                    <span>অন্যান্য: <strong>{{ $otherPurchases->count() }}টি</strong></span>
                </div>
            </div>
        </div>

    </div>

    {{-- Three Classes of Purchases Breakdown --}}
    <div class="card border-0 shadow-sm rounded-4 bg-white mb-4 overflow-hidden">
        <div class="card-header bg-white py-3 px-4 border-bottom">
            <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                <i class="fa-solid fa-layer-group text-primary"></i>
                <span>ক্রয়ের শ্রেণিভিত্তিক ব্রেকডাউন (Purchase Categories Breakdown)</span>
            </h6>
        </div>
        <div class="card-body p-4">
            <div class="row g-4">
                
                {{-- 1. Books Purchase Class --}}
                <div class="col-lg-4 col-md-6 col-12">
                    <div class="p-3.5 rounded-4 border bg-light bg-opacity-50 h-100 d-flex flex-column">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="badge bg-primary text-white rounded-pill px-2.5 py-1 fw-bold">১. বই ক্রয় (Books)</span>
                            <span class="fw-bold text-primary">{{ $booksPurchases->count() }}টি চালান</span>
                        </div>
                        <h4 class="fw-bold text-dark my-2">৳{{ number_format($booksPurchaseTotal, 2) }}</h4>
                        <p class="small text-muted mb-3">বই ক্যাটালগ ও বুকশপ ইনভেন্টরিতে সরাসরি যুক্ত হওয়া চালানের মোট মূল্য।</p>
                        <div class="mt-auto pt-2 border-top small text-muted d-flex justify-content-between">
                            <span>মোট পরিশোধ: ৳{{ number_format($booksPurchases->sum('paid_amount'), 0) }}</span>
                            <span>বকেয়া: ৳{{ number_format($booksPurchases->sum('due_amount'), 0) }}</span>
                        </div>
                    </div>
                </div>

                {{-- 2. Raw Materials Class --}}
                <div class="col-lg-4 col-md-6 col-12">
                    <div class="p-3.5 rounded-4 border bg-light bg-opacity-50 h-100 d-flex flex-column" style="border-color: #fef08a !important;">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="badge bg-warning text-dark rounded-pill px-2.5 py-1 fw-bold">২. কাঁচামাল ও উৎপাদন ক্রয়</span>
                            <span class="fw-bold text-warning-emphasis">{{ $rawPurchases->count() }}টি চালান</span>
                        </div>
                        <h4 class="fw-bold text-dark my-2">৳{{ number_format($rawPurchaseTotal, 2) }}</h4>
                        <p class="small text-muted mb-3">কাগজ, কালি, ৪-কালার/১-কালার ছাপা বিল, বাইন্ডিং ও বাঁধাই বিল, প্লেট ইত্যাদির মোট খরচ।</p>
                        <div class="mt-auto pt-2 border-top small text-muted d-flex justify-content-between">
                            <span>মোট পরিশোধ: ৳{{ number_format($rawPurchases->sum('paid_amount'), 0) }}</span>
                            <span>বকেয়া: ৳{{ number_format($rawPurchases->sum('due_amount'), 0) }}</span>
                        </div>
                    </div>
                </div>

                {{-- 3. Other Purchases Class --}}
                <div class="col-lg-4 col-md-12 col-12">
                    <div class="p-3.5 rounded-4 border bg-light bg-opacity-50 h-100 d-flex flex-column">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="badge bg-info text-dark rounded-pill px-2.5 py-1 fw-bold">৩. অন্যান্য ক্রয় (Other)</span>
                            <span class="fw-bold text-info-emphasis">{{ $otherPurchases->count() }}টি চালান</span>
                        </div>
                        <h4 class="fw-bold text-dark my-2">৳{{ number_format($otherPurchaseTotal, 2) }}</h4>
                        <p class="small text-muted mb-3">অফিসের মালামাল, স্টেশনারি ও বিবিধ খরচের মোট হিসাব।</p>
                        <div class="mt-auto pt-2 border-top small text-muted d-flex justify-content-between">
                            <span>মোট পরিশোধ: ৳{{ number_format($otherPurchases->sum('paid_amount'), 0) }}</span>
                            <span>বকেয়া: ৳{{ number_format($otherPurchases->sum('due_amount'), 0) }}</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- Production & Raw Materials Specific Sub-Breakdown --}}
    @if($rawPurchaseTotal > 0)
    <div class="card border-0 shadow-sm rounded-4 bg-white mb-4 overflow-hidden">
        <div class="card-header bg-white py-3 px-4 border-bottom">
            <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                <i class="fa-solid fa-boxes-packing text-warning"></i>
                <span>কাঁচামাল ও মুদ্রণ বিল উপ-শ্রেণি (Raw Materials & Production Sub-ledger)</span>
            </h6>
        </div>
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-md-2 col-sm-4 col-6">
                    <div class="p-3 rounded-3 bg-light text-center border">
                        <div class="small text-muted mb-1">📄 কাগজ ও আর্টকার্ড</div>
                        <h5 class="fw-bold text-dark mb-0">৳{{ number_format($paperTotal, 0) }}</h5>
                    </div>
                </div>
                <div class="col-md-2 col-sm-4 col-6">
                    <div class="p-3 rounded-3 bg-light text-center border">
                        <div class="small text-muted mb-1">🖨️ ছাপা বিল (১/৪ কালার)</div>
                        <h5 class="fw-bold text-dark mb-0">৳{{ number_format($printTotal, 0) }}</h5>
                    </div>
                </div>
                <div class="col-md-2 col-sm-4 col-6">
                    <div class="p-3 rounded-3 bg-light text-center border">
                        <div class="small text-muted mb-1">📖 বাঁধাই ও বাইন্ডিং</div>
                        <h5 class="fw-bold text-dark mb-0">৳{{ number_format($bindingTotal, 0) }}</h5>
                    </div>
                </div>
                <div class="col-md-2 col-sm-4 col-6">
                    <div class="p-3 rounded-3 bg-light text-center border">
                        <div class="small text-muted mb-1">⚙️ প্লেট ও সিটিপি বিল</div>
                        <h5 class="fw-bold text-dark mb-0">৳{{ number_format($plateTotal, 0) }}</h5>
                    </div>
                </div>
                <div class="col-md-4 col-sm-8 col-12">
                    <div class="p-3 rounded-3 bg-light text-center border">
                        <div class="small text-muted mb-1">📦 অন্যান্য কাঁচামাল ও ল্যামিনেশন</div>
                        <h5 class="fw-bold text-dark mb-0">৳{{ number_format($otherRawTotal, 0) }}</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Tabs: Detailed Purchases Ledger vs Sales Orders Ledger --}}
    <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
        <div class="card-header bg-white border-bottom p-3">
            <ul class="nav nav-pills gap-2" id="reportTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active rounded-pill fw-semibold py-2 px-3.5 d-flex align-items-center gap-2" id="tab-purchases-btn" data-bs-toggle="pill" data-bs-target="#tab-purchases" type="button" role="tab">
                        <i class="fa-solid fa-file-invoice-dollar"></i>
                        <span>ক্রয় চালানের তালিকা ({{ $purchases->count() }})</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-pill fw-semibold py-2 px-3.5 d-flex align-items-center gap-2" id="tab-sales-btn" data-bs-toggle="pill" data-bs-target="#tab-sales" type="button" role="tab">
                        <i class="fa-solid fa-bag-shopping"></i>
                        <span>বই বিক্রয় ও অর্ডার তালিকা ({{ $orders->count() }})</span>
                    </button>
                </li>
            </ul>
        </div>

        <div class="card-body p-0">
            <div class="tab-content" id="reportTabsContent">
                
                {{-- TAB 1: PURCHASES LEDGER --}}
                <div class="tab-pane fade show active" id="tab-purchases" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr class="text-uppercase text-muted" style="font-size: 11.5px;">
                                    <th class="ps-4">ইনভয়েস #</th>
                                    <th>শ্রেণি / টাইপ</th>
                                    <th>সরবরাহকারী / প্রকাশক</th>
                                    <th>তারিখ</th>
                                    <th class="text-center">আইটেম সংখ্যা</th>
                                    <th class="text-end">মোট বিল (৳)</th>
                                    <th class="text-end">পরিশোধ (৳)</th>
                                    <th class="text-end">বকেয়া (৳)</th>
                                    <th class="text-center pe-4">একশন</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($purchases as $p)
                                    @php
                                        $catBadge = match($p->purchase_category) {
                                            'raw_materials' => ['bg' => 'bg-warning-subtle text-warning-emphasis border-warning-subtle', 'label' => 'কাঁচামাল'],
                                            'other' => ['bg' => 'bg-info-subtle text-info border-info-subtle', 'label' => 'অন্যান্য'],
                                            default => ['bg' => 'bg-primary-subtle text-primary border-primary-subtle', 'label' => 'বই ক্রয়']
                                        };
                                    @endphp
                                    <tr>
                                        <td class="ps-4 fw-bold">
                                            <a href="{{ route('admin.purchases.show', $p->id) }}" class="text-dark text-decoration-none hover-primary font-monospace">
                                                {{ $p->purchase_no }}
                                            </a>
                                            @if($p->publisher_memo_no)
                                                <div class="small text-muted font-monospace" style="font-size: 10.5px;">মেমো: {{ $p->publisher_memo_no }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge {{ $catBadge['bg'] }} border rounded-pill px-2.5 py-1 small fw-semibold">
                                                {{ $catBadge['label'] }}
                                            </span>
                                        </td>
                                        <td class="fw-semibold text-dark">
                                            {{ $p->party_name }}
                                        </td>
                                        <td class="small text-muted">
                                            {{ $p->purchase_date ? $p->purchase_date->format('d M, Y') : '-' }}
                                        </td>
                                        <td class="text-center font-monospace small">
                                            {{ $p->items->count() }}টি
                                        </td>
                                        <td class="text-end fw-bold text-dark font-monospace">
                                            ৳{{ number_format($p->grand_total, 2) }}
                                        </td>
                                        <td class="text-end fw-semibold text-success font-monospace">
                                            ৳{{ number_format($p->paid_amount, 2) }}
                                        </td>
                                        <td class="text-end fw-bold {{ $p->due_amount > 0 ? 'text-danger' : 'text-muted' }} font-monospace">
                                            ৳{{ number_format($p->due_amount, 2) }}
                                        </td>
                                        <td class="text-center pe-4">
                                            <a href="{{ route('admin.purchases.show', $p->id) }}" class="btn btn-outline-primary btn-xs rounded-pill px-2.5 py-1">
                                                <i class="fa-solid fa-eye me-1"></i> দেখুন
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-5 text-muted">
                                            <i class="fa-solid fa-file-circle-xmark fs-2 text-secondary mb-2"></i>
                                            <div>এই মাসে কোনো ক্রয় চালান পাওয়া যায়নি</div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- TAB 2: SALES ORDERS LEDGER --}}
                <div class="tab-pane fade" id="tab-sales" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr class="text-uppercase text-muted" style="font-size: 11.5px;">
                                    <th class="ps-4">অর্ডার #</th>
                                    <th>গ্রাহকের নাম ও ফোন</th>
                                    <th>তারিখ ও সময়</th>
                                    <th>পেমেন্ট মেথড</th>
                                    <th>স্ট্যাটাস</th>
                                    <th class="text-end pe-4">মোট টাকা (৳)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($orders as $ord)
                                    <tr>
                                        <td class="ps-4 fw-bold font-monospace text-dark">
                                            #{{ $ord->order_number }}
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-dark">{{ $ord->customer_name }}</div>
                                            <small class="text-muted font-monospace">{{ $ord->customer_phone }}</small>
                                        </td>
                                        <td class="small text-muted">
                                            {{ $ord->created_at ? $ord->created_at->format('d M, Y - h:i A') : '-' }}
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border rounded-pill px-2 py-0.5 small text-uppercase font-monospace">
                                                {{ $ord->payment_method ?: 'COD' }}
                                            </span>
                                        </td>
                                        <td>
                                            @php
                                                $stBadge = match($ord->status) {
                                                    'delivered' => 'bg-success text-white',
                                                    'processing', 'shipped' => 'bg-primary text-white',
                                                    'pending' => 'bg-warning text-dark',
                                                    default => 'bg-secondary text-white'
                                                };
                                            @endphp
                                            <span class="badge {{ $stBadge }} rounded-pill px-2.5 py-1 small">
                                                {{ ucfirst($ord->status) }}
                                            </span>
                                        </td>
                                        <td class="text-end pe-4 fw-bold text-success font-monospace">
                                            ৳{{ number_format($ord->total_amount, 2) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">
                                            <i class="fa-solid fa-box-open fs-2 text-secondary mb-2"></i>
                                            <div>এই মাসে কোনো বই বিক্রয় বা অর্ডার পাওয়া যায়নি</div>
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

</div>
@endsection
