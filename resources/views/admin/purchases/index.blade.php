@extends('layouts.admin')

@section('title', 'Publisher Purchases & Accounts')
@section('heading', 'Publisher Purchases & Payment Management')
@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Purchases & Invoices</li>
@endsection

@section('actions')
    <div class="d-flex flex-wrap align-items-center gap-2">
        <a href="{{ route('admin.purchases.monthly-report') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-xs fw-bold">
            <i class="fa-solid fa-chart-pie me-1"></i> মাসিক রিপোর্ট
        </a>
        <a href="{{ route('admin.purchases.create', ['type' => 'books']) }}" class="btn btn-primary btn-sm rounded-pill px-3 shadow-xs fw-bold">
            <i class="fas fa-book me-1"></i> নতুন বই ক্রয়
        </a>
        <a href="{{ route('admin.purchases.create', ['type' => 'raw_materials']) }}" class="btn btn-warning text-dark btn-sm rounded-pill px-3 shadow-xs fw-bold">
            <i class="fas fa-boxes-stacked me-1"></i> কাঁচামাল ও প্রেস ক্রয়
        </a>
        <a href="{{ route('admin.purchases.create', ['type' => 'other']) }}" class="btn btn-info text-dark btn-sm rounded-pill px-3 shadow-xs fw-bold">
            <i class="fas fa-cart-shopping me-1"></i> অন্যান্য ক্রয়
        </a>
        <a href="{{ route('admin.purchases.payments') }}" class="btn btn-outline-success btn-sm rounded-pill px-3 shadow-xs">
            <i class="fas fa-money-bill-transfer me-1"></i> কিস্তি ও পেমেন্ট
        </a>
    </div>
@endsection

@section('content')

{{-- 3-Class Category Selector Bar --}}
<div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
    <div class="card-body p-3">
        <div class="d-flex flex-column flex-lg-row align-items-center justify-content-between gap-3">
            <div class="btn-group shadow-2xs rounded-pill p-1 bg-light border w-100 w-lg-auto" role="group">
                <a href="{{ route('admin.purchases.index') }}" 
                   class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold {{ empty($category) ? 'btn-white text-primary shadow-xs' : 'btn-light text-muted' }}">
                    <i class="fa-solid fa-layer-group me-1"></i> সকল ক্রয় ({{ $stats['total_invoices'] }})
                </a>
                <a href="{{ route('admin.purchases.index', ['category' => 'books']) }}" 
                   class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold {{ $category === 'books' ? 'btn-white text-primary shadow-xs' : 'btn-light text-muted' }}">
                    <i class="fa-solid fa-book-open me-1"></i> ১. বই ক্রয় ({{ $stats['books_count'] }}টি | ৳{{ number_format($stats['books_total'], 0) }})
                </a>
                <a href="{{ route('admin.purchases.index', ['category' => 'raw_materials']) }}" 
                   class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold {{ $category === 'raw_materials' ? 'btn-white text-warning text-dark shadow-xs' : 'btn-light text-muted' }}">
                    <i class="fa-solid fa-boxes-stacked me-1"></i> ২. কাঁচামাল ক্রয় ({{ $stats['raw_count'] }}টি | ৳{{ number_format($stats['raw_total'], 0) }})
                </a>
                <a href="{{ route('admin.purchases.index', ['category' => 'other']) }}" 
                   class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold {{ $category === 'other' ? 'btn-white text-info text-dark shadow-xs' : 'btn-light text-muted' }}">
                    <i class="fa-solid fa-cart-shopping me-1"></i> ৩. অন্যান্য ক্রয় ({{ $stats['other_count'] }}টি | ৳{{ number_format($stats['other_total'], 0) }})
                </a>
            </div>

            <span class="small text-muted d-none d-xl-inline">
                <i class="fa-solid fa-circle-check text-success me-1"></i> বই, কাঁচামাল ও বিবিধ ক্রয়ের হিসাব আলাদা ক্লাসে সম্পূর্ণ কাস্টমাইজড।
            </span>
        </div>
    </div>
</div>

{{-- Summary Cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-primary">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold">মোট ইনভয়েস সংখ্যা</span>
                    <h3 class="fw-bold mb-0 text-primary">{{ number_format($stats['total_invoices']) }}</h3>
                </div>
                <div class="rounded-circle bg-primary-subtle text-primary p-3"><i class="fas fa-receipt fs-4"></i></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-dark">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold">সর্বমোট ক্রয়মূল্য</span>
                    <h3 class="fw-bold mb-0 text-dark">৳{{ number_format($stats['total_purchase'], 2) }}</h3>
                </div>
                <div class="rounded-circle bg-dark-subtle text-dark p-3"><i class="fas fa-cart-flatbed fs-4"></i></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-success">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold">পরিশোধিত অর্থ</span>
                    <h3 class="fw-bold mb-0 text-success">৳{{ number_format($stats['total_paid'], 2) }}</h3>
                </div>
                <div class="rounded-circle bg-success-subtle text-success p-3"><i class="fas fa-hand-holding-dollar fs-4"></i></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-danger">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold">বকেয়া দেনা (Due)</span>
                    <h3 class="fw-bold mb-0 text-danger">৳{{ number_format($stats['total_due'], 2) }}</h3>
                </div>
                <div class="rounded-circle bg-danger-subtle text-danger p-3"><i class="fas fa-clock-rotate-left fs-4"></i></div>
            </div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-3">
        <form action="{{ route('admin.purchases.index') }}" method="GET" class="row g-2 align-items-center">
            @if($category)
                <input type="hidden" name="category" value="{{ $category }}">
            @endif
            <div class="col-md-3">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="search" name="search" class="form-control border-start-0" 
                           placeholder="ইনভয়েস নং, বই বা সরবরাহকারী..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3">
                <select name="publisher_id" class="form-select" onchange="this.form.submit()">
                    <option value="">সকল প্রকাশনী / সরবরাহকারী</option>
                    @foreach($publishers as $id => $name)
                        <option value="{{ $id }}" @selected(request('publisher_id') == $id)>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="payment_status" class="form-select" onchange="this.form.submit()">
                    <option value="all">সকল পেমেন্ট স্ট্যাটাস</option>
                    <option value="paid" @selected(request('payment_status') === 'paid')>পরিশোধিত (Paid)</option>
                    <option value="partial" @selected(request('payment_status') === 'partial')>আংশিক পরিশোধ</option>
                    <option value="due" @selected(request('payment_status') === 'due')>বকেয়া (Due)</option>
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}" title="Start Date">
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter me-1"></i> ফিল্টার</button>
                @if(request()->hasAny(['search', 'publisher_id', 'payment_status', 'date_from']))
                    <a href="{{ route('admin.purchases.index', $category ? ['category' => $category] : []) }}" class="btn btn-light border" title="Reset"><i class="fas fa-rotate-left"></i></a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- Purchase Invoices Table --}}
<div class="adm-card shadow-sm rounded-4 overflow-hidden bg-white">
    @if ($purchases->isEmpty())
        <div class="empty-state py-5 text-center">
            <i class="fas fa-receipt fs-1 text-muted opacity-50 mb-3"></i>
            <h5 class="fw-bold text-muted">কোনো ক্রয় চালান পাওয়া যায়নি</h5>
            <p class="text-muted small">উপরের বাটন দিয়ে নতুন বই ক্রয়, কাঁচামাল ক্রয় অথবা অন্যান্য ক্রয় চালান যোগ করুন।</p>
            <div class="d-flex justify-content-center flex-wrap gap-2">
                <a href="{{ route('admin.purchases.create', ['type' => 'books']) }}" class="btn btn-primary rounded-pill px-4">
                    <i class="fas fa-book me-1"></i> বই ক্রয়
                </a>
                <a href="{{ route('admin.purchases.create', ['type' => 'raw_materials']) }}" class="btn btn-warning rounded-pill px-4 text-dark fw-bold">
                    <i class="fas fa-boxes-stacked me-1"></i> কাঁচামাল ক্রয়
                </a>
                <a href="{{ route('admin.purchases.create', ['type' => 'other']) }}" class="btn btn-info rounded-pill px-4 text-dark fw-bold">
                    <i class="fas fa-cart-shopping me-1"></i> অন্যান্য ক্রয়
                </a>
            </div>
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light table-light small text-muted">
                    <tr>
                        <th class="ps-3.5">ইনভয়েস ও শ্রেণি</th>
                        <th>সরবরাহকারী / প্রকাশনী / ভেন্ডর</th>
                        <th>তারিখ</th>
                        <th>আইটেম সংখ্যা</th>
                        <th class="text-end">মোট টাকা</th>
                        <th class="text-end">পরিশোধিত</th>
                        <th class="text-end">বকেয়া</th>
                        <th class="text-center">স্ট্যাটাস</th>
                        <th class="text-end pe-3.5">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($purchases as $purchase)
                        @php 
                            $catType = $purchase->purchase_category;
                        @endphp
                        <tr>
                            <td class="ps-3.5">
                                <a href="{{ route('admin.purchases.show', $purchase->id) }}" class="fw-bold text-dark text-decoration-none font-monospace">
                                    {{ $purchase->purchase_no }}
                                </a>
                                <div>
                                    @if($catType === 'raw_materials')
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2 py-0.5" style="font-size: 11px;">
                                            <i class="fa-solid fa-boxes-stacked me-1"></i>কাঁচামাল ও প্রেস
                                        </span>
                                    @elseif($catType === 'other')
                                        <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle rounded-pill px-2 py-0.5" style="font-size: 11px;">
                                            <i class="fa-solid fa-cart-shopping me-1"></i>অন্যান্য ক্রয়
                                        </span>
                                    @else
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2 py-0.5" style="font-size: 11px;">
                                            <i class="fa-solid fa-book me-1"></i>বই ক্রয়
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="fw-bold text-dark">
                                    {{ $purchase->vendor_name ?: ($purchase->supplier_name ?: ($purchase->publisher->name ?? '—')) }}
                                </div>
                                @if($purchase->publisher_memo_no)
                                    <span class="small text-muted font-monospace">মেমো: {{ $purchase->publisher_memo_no }}</span>
                                @endif
                            </td>
                            <td class="small text-muted text-nowrap">
                                {{ $purchase->purchase_date ? $purchase->purchase_date->format('d M, Y') : '' }}
                            </td>
                            <td class="small text-muted">
                                <span class="badge bg-light text-dark border rounded-pill px-2.5 py-1">
                                    {{ $purchase->items->count() }} টি আইটেম
                                </span>
                            </td>
                            <td class="text-end fw-bold text-dark font-monospace">
                                ৳{{ number_format($purchase->grand_total, 2) }}
                            </td>
                            <td class="text-end fw-semibold text-success font-monospace">
                                ৳{{ number_format($purchase->paid_amount, 2) }}
                            </td>
                            <td class="text-end fw-bold font-monospace {{ $purchase->due_amount > 0 ? 'text-danger' : 'text-muted' }}">
                                ৳{{ number_format($purchase->due_amount, 2) }}
                            </td>
                            <td class="text-center">
                                @if($purchase->payment_status === 'paid')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 small">পরিশোধিত</span>
                                @elseif($purchase->payment_status === 'partial')
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2.5 py-1 small">আংশিক বাকি</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1 small">সম্পূর্ণ বকেয়া</span>
                                @endif
                            </td>
                            <td class="text-end pe-3.5">
                                <a href="{{ route('admin.purchases.show', $purchase->id) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 fw-semibold">
                                    দেখুন <i class="fa-solid fa-arrow-right ms-1"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
            </table>
        </div>

        @if ($purchases->hasPages())
            <div class="adm-card__foot d-flex flex-wrap justify-content-between align-items-center gap-2 p-3 bg-white border-top">
                <span class="text-muted small">
                    Showing {{ $purchases->firstItem() }}–{{ $purchases->lastItem() }} of {{ number_format($purchases->total()) }} invoices
                </span>
                {{ $purchases->links() }}
            </div>
        @endif
    @endif
</div>

@endsection
