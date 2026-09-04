@extends('layouts.app')

@section('title', 'সেলার ড্যাশবোর্ড — ' . ($shopName ?? 'IDEA Seller Panel'))

@section('content')
<div class="container-fluid py-4 px-md-4" style="max-width: 1440px;">

    {{-- Flash alerts --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-4 rounded-4 shadow-xs" role="alert">
            <i class="fas fa-circle-check fs-5 me-2.5 text-success"></i>
            <div class="fw-medium">{{ session('success') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center mb-4 rounded-4 shadow-xs" role="alert">
            <i class="fas fa-triangle-exclamation fs-5 me-2.5 text-danger"></i>
            <div class="fw-medium">{{ session('error') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- ═════════════════════════════════════════════════════════════════════════ --}}
    {{-- 1. SELLER & SHOP HEADER BANNER                                            --}}
    {{-- ═════════════════════════════════════════════════════════════════════════ --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4 text-white overflow-hidden position-relative" 
         style="background: linear-gradient(135deg, #0f172a 0%, #064e3b 50%, #047857 100%);">
        <div class="card-body p-3.5 p-md-4 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 position-relative z-1">
            <div class="d-flex align-items-center gap-3">
                <div class="position-relative flex-shrink-0 bg-white rounded-circle p-1 shadow-sm d-flex align-items-center justify-content-center" 
                     style="width: 76px; height: 76px; min-width: 76px;">
                    <div class="w-100 h-100 rounded-circle bg-success text-white fw-bold fs-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-store"></i>
                    </div>
                    <span class="position-absolute bottom-0 end-0 bg-warning text-dark p-1 rounded-circle shadow-xs" title="অনুমোদিত বিক্রেতা" style="font-size: 11px; width: 22px; height: 22px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-check"></i>
                    </span>
                </div>
                <div>
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                        <h3 class="fw-bold mb-0 text-white fs-4 fs-md-3">{{ $shopName }}</h3>
                        <span class="badge bg-white bg-opacity-20 text-white rounded-pill px-2.5 py-1 small fw-semibold">
                            <i class="fas fa-circle-check text-warning me-1"></i>সেলার ও ডিলার পোর্টাল
                        </span>
                    </div>
                    <div class="small opacity-90 text-light d-flex flex-wrap align-items-center gap-3" style="font-size: 12px;">
                        <span><i class="fas fa-user-circle me-1"></i>{{ $user->name }}</span>
                        @if($user->phone)
                            <span><i class="fas fa-phone me-1"></i>{{ $user->phone }}</span>
                        @endif
                        @if($user->email)
                            <span><i class="fas fa-envelope me-1"></i>{{ $user->email }}</span>
                        @endif
                        @if($shopAddress)
                            <span><i class="fas fa-location-dot me-1"></i>{{ $shopAddress }}</span>
                        @endif
                        @if($tradeLicense)
                            <span><i class="fas fa-id-card me-1"></i>লাইসেন্স: {{ $tradeLicense }}</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="d-flex flex-wrap align-items-center gap-2 w-100 w-md-auto justify-content-start justify-content-md-end">
                <a href="{{ route('subadmin.bills.create') }}" class="btn btn-warning text-dark rounded-pill px-3.5 py-2 fw-bold shadow-sm d-flex align-items-center gap-1.5 flex-grow-1 flex-md-grow-0 justify-content-center">
                    <i class="fas fa-plus-circle fs-5"></i>
                    <span>নতুন বিল (POS)</span>
                </a>
                <a href="{{ route('subadmin.bills.index') }}" class="btn btn-light text-dark rounded-pill px-3.5 py-2 fw-bold shadow-sm d-flex align-items-center gap-1.5 flex-grow-1 flex-md-grow-0 justify-content-center">
                    <i class="fas fa-file-invoice-dollar text-primary"></i>
                    <span>বিল তালিকা</span>
                </a>
                <a href="{{ route('subadmin.accounts') }}" class="btn btn-outline-light rounded-pill px-3.5 py-2 fw-semibold d-flex align-items-center gap-1.5 flex-grow-1 flex-md-grow-0 justify-content-center">
                    <i class="fas fa-wallet"></i>
                    <span>হিসাব বিবরণী</span>
                </a>
            </div>
        </div>
    </div>

    {{-- Admin Seller Switcher Filter (Only for Super Admin) --}}
    @if($isAdmin && count($sellersList) > 0)
        <div class="card border-0 shadow-sm rounded-4 p-3 mb-4 bg-light">
            <form method="GET" action="{{ route('subadmin.dashboard') }}" class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-filter text-primary"></i>
                    <strong class="text-dark small">সেলার ফিল্টার (অ্যাডমিন ভিউ):</strong>
                </div>
                <div class="d-flex align-items-center gap-2 flex-grow-1 flex-md-grow-0">
                    <select name="seller_id" class="form-select form-select-sm rounded-pill px-3 fw-semibold" onchange="this.form.submit()">
                        <option value="">-- সকল বিক্রেতা / আমার ড্যাশবোর্ড --</option>
                        @foreach($sellersList as $sId => $sName)
                            <option value="{{ $sId }}" {{ request('seller_id') == $sId ? 'selected' : '' }}>{{ $sName }}</option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>
    @endif

    {{-- ═════════════════════════════════════════════════════════════════════════ --}}
    {{-- 2. KPI METRICS CARDS (সুষম ২×৩ ডেসকটপ ও ২×২ মোবাইল গ্রিড)                 --}}
    {{-- ═════════════════════════════════════════════════════════════════════════ --}}
    <div class="row g-2.5 g-md-3 mb-4">
        {{-- Metric 1: Total Sales Revenue --}}
        <div class="col-6 col-lg-2 col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-primary h-100 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="text-muted small fw-semibold text-truncate">মোট বিক্রয়</span>
                        <span class="p-1 px-1.5 bg-primary-subtle text-primary rounded-2"><i class="fas fa-sack-dollar small"></i></span>
                    </div>
                    <h4 class="fw-bold mb-0 text-dark font-monospace fs-4">৳{{ number_format($totalSales, 0) }}</h4>
                </div>
                <small class="text-muted mt-1" style="font-size: 11px;">সর্বমোট বিক্রিত পণ্য</small>
            </div>
        </div>

        {{-- Metric 2: Settled / Paid Amount --}}
        <div class="col-6 col-lg-2 col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-success h-100 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="text-muted small fw-semibold text-truncate">পরিশোধিত অর্থ</span>
                        <span class="p-1 px-1.5 bg-success-subtle text-success rounded-2"><i class="fas fa-circle-check small"></i></span>
                    </div>
                    <h4 class="fw-bold mb-0 text-success font-monospace fs-4">৳{{ number_format($totalPaid, 0) }}</h4>
                </div>
                <small class="text-success" style="font-size: 11px;">@bn($paidBillsCount) টি পরিশোধিত বিল</small>
            </div>
        </div>

        {{-- Metric 3: Pending Due Amount --}}
        <div class="col-6 col-lg-2 col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-danger h-100 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="text-muted small fw-semibold text-truncate">বকেয়া বিল</span>
                        <span class="p-1 px-1.5 bg-danger-subtle text-danger rounded-2"><i class="fas fa-clock small"></i></span>
                    </div>
                    <h4 class="fw-bold mb-0 text-danger font-monospace fs-4">৳{{ number_format($totalDue, 0) }}</h4>
                </div>
                <small class="text-danger" style="font-size: 11px;">@bn($dueBillsCount) টি বকেয়া মেমো</small>
            </div>
        </div>

        {{-- Metric 4: Today's Sales --}}
        <div class="col-6 col-lg-2 col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-warning h-100 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="text-muted small fw-semibold text-truncate">আজকের বিক্রয়</span>
                        <span class="p-1 px-1.5 bg-warning-subtle text-warning-emphasis rounded-2"><i class="fas fa-calendar-day small"></i></span>
                    </div>
                    <h4 class="fw-bold mb-0 text-dark font-monospace fs-4">৳{{ number_format($todaySales, 0) }}</h4>
                </div>
                <small class="text-muted" style="font-size: 11px;">@bn($todayBills) টি আজকের ইনভয়েস</small>
            </div>
        </div>

        {{-- Metric 5: Total Bills Count --}}
        <div class="col-6 col-lg-2 col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-info h-100 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="text-muted small fw-semibold text-truncate">সর্বমোট মেমো</span>
                        <span class="p-1 px-1.5 bg-info-subtle text-info rounded-2"><i class="fas fa-file-invoice small"></i></span>
                    </div>
                    <h4 class="fw-bold mb-0 text-info font-monospace fs-4">@bn($totalBills)</h4>
                </div>
                <small class="text-muted" style="font-size: 11px;">মোট ইনভয়েস সংখ্যা</small>
            </div>
        </div>

        {{-- Metric 6: Total Items Sold --}}
        <div class="col-6 col-lg-2 col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-secondary h-100 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="text-muted small fw-semibold text-truncate">বিক্রিত বইয়ের কপি</span>
                        <span class="p-1 px-1.5 bg-secondary-subtle text-secondary rounded-2"><i class="fas fa-boxes-stacked small"></i></span>
                    </div>
                    <h4 class="fw-bold mb-0 text-dark font-monospace fs-4">@bn($totalItemsSold)</h4>
                </div>
                <small class="text-muted" style="font-size: 11px;">মোট ডেলিভারিকৃত কপি</small>
            </div>
        </div>
    </div>

    {{-- ═════════════════════════════════════════════════════════════════════════ --}}
    {{-- 3. QUICK ACTIONS & RECENT INVOICES GRID                                    --}}
    {{-- ═════════════════════════════════════════════════════════════════════════ --}}
    <div class="row g-3 g-md-4 mb-4">
        {{-- Left: Recent Invoices & Bills Table/List --}}
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-3 p-md-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                    <h5 class="fw-bold text-dark mb-0 fs-6 fs-md-5 d-flex align-items-center gap-2">
                        <span class="badge bg-primary-subtle text-primary rounded-circle p-1.5"><i class="fas fa-receipt"></i></span>
                        <span>সাম্প্রতিক বিক্রয় ও ইনভয়েস তালিকা</span>
                    </h5>
                    <a href="{{ route('subadmin.bills.index') }}" class="small text-primary text-decoration-none fw-semibold">
                        সকল বিল (@bn($totalBills)) →
                    </a>
                </div>

                {{-- Desktop Table (>= 768px) --}}
                <div class="table-responsive d-none d-md-block">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light text-secondary">
                            <tr>
                                <th>মেমো নম্বর</th>
                                <th>ক্রেতা / কাস্টমার</th>
                                <th>তারিখ</th>
                                <th>মোট মূল্য</th>
                                <th>স্ট্যাটাস</th>
                                <th class="text-end">অ্যাকশন</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentBills as $bill)
                                <tr>
                                    <td>
                                        <a href="{{ route('subadmin.bills.show', $bill->id) }}" class="fw-bold text-decoration-none text-primary font-monospace">
                                            #{{ $bill->bill_no }}
                                        </a>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark text-truncate" style="max-width: 170px;">{{ $bill->customer_name ?: 'ক্যাশ ক্রেতা' }}</div>
                                        @if($bill->customer_phone)
                                            <small class="text-muted d-block font-monospace">{{ $bill->customer_phone }}</small>
                                        @endif
                                    </td>
                                    <td class="text-muted">{{ $bill->created_at->format('d M, Y') }}</td>
                                    <td>
                                        <span class="fw-bold text-dark font-monospace">৳{{ number_format($bill->total, 2) }}</span>
                                    </td>
                                    <td>
                                        @if($bill->payment_status === 'paid')
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1">পরিশোধিত</span>
                                        @elseif($bill->payment_status === 'partial')
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2.5 py-1">আংশিক</span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1">বকেয়া</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('subadmin.bills.receipt', $bill->id) }}" target="_blank" class="btn btn-outline-secondary" title="রসিদ প্রিন্ট">
                                                <i class="fas fa-print"></i>
                                            </a>
                                            <a href="{{ route('subadmin.bills.show', $bill->id) }}" class="btn btn-outline-primary" title="বিস্তারিত">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($bill->payment_status !== 'paid')
                                                <form action="{{ route('subadmin.bills.quick-pay', $bill->id) }}" method="POST" class="d-inline" onsubmit="return confirm('বিলটি পরিশোধিত হিসেবে চিহ্নিত করবেন?')">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline-success" title="পেইড মার্ক করুন">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        <i class="fas fa-file-invoice fs-3 opacity-25 d-block mb-1"></i>
                                        এখনও কোনো বিল তৈরি করা হয়নি। উপরের "নতুন বিল (POS)" বাটনে ক্লিক করে প্রথম বিল তৈরি করুন।
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Mobile Card List (< 768px) --}}
                <div class="d-flex flex-column gap-2.5 d-md-none">
                    @forelse($recentBills as $bill)
                        <div class="p-3 rounded-3 border bg-light bg-opacity-30">
                            <div class="d-flex align-items-center justify-content-between mb-1.5">
                                <a href="{{ route('subadmin.bills.show', $bill->id) }}" class="fw-bold text-primary font-monospace text-decoration-none">
                                    #{{ $bill->bill_no }}
                                </a>
                                @if($bill->payment_status === 'paid')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0.5" style="font-size: 10.5px;">পরিশোধিত</span>
                                @elseif($bill->payment_status === 'partial')
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2 py-0.5" style="font-size: 10.5px;">আংশিক</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-0.5" style="font-size: 10.5px;">বকেয়া</span>
                                @endif
                            </div>
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div>
                                    <div class="fw-semibold text-dark small">{{ $bill->customer_name ?: 'ক্যাশ ক্রেতা' }}</div>
                                    @if($bill->customer_phone)
                                        <small class="text-muted font-monospace" style="font-size: 11px;">{{ $bill->customer_phone }}</small>
                                    @endif
                                </div>
                                <div class="text-end">
                                    <span class="fw-bold text-dark font-monospace fs-6">৳{{ number_format($bill->total, 2) }}</span>
                                    <small class="text-muted d-block" style="font-size: 10.5px;">{{ $bill->created_at->format('d M, Y') }}</small>
                                </div>
                            </div>
                            <div class="d-flex align-items-center justify-content-between pt-2 border-top border-light-subtle">
                                <span class="text-muted" style="font-size: 11px;">
                                    <i class="fas fa-boxes-stacked me-1"></i>{{ is_array($bill->items) ? count($bill->items) : 0 }} আইটেম
                                </span>
                                <div class="d-flex gap-1.5">
                                    <a href="{{ route('subadmin.bills.receipt', $bill->id) }}" target="_blank" class="btn btn-xs btn-outline-secondary rounded-pill px-2.5 py-1" style="font-size: 11px;">
                                        <i class="fas fa-print me-1"></i> রসিদ
                                    </a>
                                    <a href="{{ route('subadmin.bills.show', $bill->id) }}" class="btn btn-xs btn-primary rounded-pill px-2.5 py-1" style="font-size: 11px;">
                                        <i class="fas fa-eye me-1"></i> দেখুন
                                    </a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted small">
                            <i class="fas fa-file-invoice fs-3 opacity-25 d-block mb-1"></i>
                            এখনও কোনো বিল তৈরি করা হয়নি।
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Right: Quick POS Hub & Financial Overview --}}
        <div class="col-12 col-lg-4">
            <div class="d-flex flex-column gap-3">
                {{-- Quick POS Tile Banner --}}
                <div class="card border-0 shadow-sm rounded-4 p-4 text-white" style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 60%, #4338ca 100%);">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="rounded-circle bg-warning text-dark d-flex align-items-center justify-content-center fs-3 flex-shrink-0" style="width: 52px; height: 52px;">
                            <i class="fas fa-cash-register"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-0 text-white">ইনস্ট্যান্ট পিওএস মেমো</h5>
                            <small class="text-white-50">দ্রুত বই স্ক্যান বা নির্বাচন করে প্রিন্ট করুন</small>
                        </div>
                    </div>
                    <a href="{{ route('subadmin.bills.create') }}" class="btn btn-warning btn-lg rounded-pill fw-bold text-dark w-100 shadow-sm d-flex align-items-center justify-content-center gap-2">
                        <i class="fas fa-plus-circle"></i>
                        <span>নতুন বিল তৈরি করুন</span>
                    </a>
                </div>

                {{-- Financial Ledger Overview Card --}}
                <div class="card border-0 shadow-sm rounded-4 p-3.5 bg-white">
                    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                        <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                            <i class="fas fa-wallet text-success"></i>
                            <span>হিসাব ও সেটেলমেন্ট বিবরণী</span>
                        </h6>
                        <a href="{{ route('subadmin.accounts') }}" class="small text-primary text-decoration-none fw-semibold">লেজার →</a>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-2 small">
                        <span class="text-muted">এই মাসের মোট বিক্রয়:</span>
                        <strong class="text-dark font-monospace">৳{{ number_format($thisMonthSales, 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2 small">
                        <span class="text-muted">সর্বমোট সংগৃহীত ক্যাশ:</span>
                        <strong class="text-success font-monospace">৳{{ number_format($totalPaid, 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center pt-2 border-top small">
                        <span class="fw-bold text-dark">সর্বমোট বকেয়া ব্যালেন্স:</span>
                        <strong class="text-danger font-monospace fs-6">৳{{ number_format($totalDue, 2) }}</strong>
                    </div>
                </div>

                {{-- Popular Books / Storefront Stock Link --}}
                <div class="card border-0 shadow-sm rounded-4 p-3.5 bg-white">
                    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                        <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                            <i class="fas fa-book-bookmark text-primary"></i>
                            <span>জনপ্রিয় বই ও স্টক</span>
                        </h6>
                        <a href="{{ route('book.index') }}" target="_blank" class="small text-primary text-decoration-none fw-semibold">স্টোরফ্রন্ট →</a>
                    </div>

                    <div class="d-flex flex-column gap-2">
                        @foreach($popularBooks as $pb)
                            <div class="d-flex align-items-center justify-content-between p-2 rounded-2 border bg-light bg-opacity-40 small">
                                <div class="text-truncate me-2" style="max-width: 180px;">
                                    <span class="fw-semibold text-dark d-block text-truncate">{{ $pb->title }}</span>
                                    <small class="text-muted font-monospace">৳{{ number_format($pb->price, 0) }}</small>
                                </div>
                                <span class="badge {{ $pb->stock_quantity > 0 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} rounded-pill px-2 py-0.5" style="font-size: 10px;">
                                    {{ $pb->stock_quantity > 0 ? $pb->stock_quantity . ' কপি' : 'স্টক শেষ' }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
