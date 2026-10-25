@extends('layouts.app')

@section('title', 'ড্যাশবোর্ড — ' . $shopName)

@section('content')
<div class="container-fluid py-4 px-md-4" style="max-width: 1280px;">

    @include('seller.partials.header')

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="বন্ধ"></button></div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="বন্ধ"></button></div>
    @endif

    {{-- Admin: look at one seller or at everyone --}}
    @if($isAdmin && count($sellersList))
        <form method="GET" action="{{ route('subadmin.dashboard') }}" class="d-flex align-items-center gap-2 mb-3">
            <label for="sellerFilter" class="small fw-semibold text-muted mb-0">বিক্রেতা</label>
            <select id="sellerFilter" name="seller_id" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                <option value="">সকল বিক্রেতা</option>
                @foreach($sellersList as $id => $name)
                    <option value="{{ $id }}" @selected($sellerId === $id)>{{ $name }}</option>
                @endforeach
            </select>
            <noscript><button class="btn btn-sm btn-primary">দেখুন</button></noscript>
        </form>
    @endif

    {{-- Numbers --}}
    <div class="sp-grid">
        <div class="sp-tile">
            <span class="sp-label">মোট বিক্রি</span>
            <span class="sp-value">৳@bn($stats->sales)</span>
            <span class="sp-sub">@bn($stats->bills)টি বিল</span>
        </div>
        <div class="sp-tile">
            <span class="sp-label">আদায়</span>
            <span class="sp-value text-success">৳@bn($stats->paid)</span>
            <span class="sp-sub">@bn($stats->paid_bills)টি পরিশোধিত বিল</span>
        </div>
        <div class="sp-tile">
            <span class="sp-label">বকেয়া</span>
            <span class="sp-value {{ $stats->due > 0 ? 'text-danger' : '' }}">৳@bn($stats->due)</span>
            <span class="sp-sub">@bn($stats->open_bills)টি অপরিশোধিত বিল</span>
        </div>
        <div class="sp-tile">
            <span class="sp-label">আজ</span>
            <span class="sp-value">৳@bn($stats->today_sales)</span>
            <span class="sp-sub">@bn($stats->today_bills)টি বিল</span>
        </div>
        <div class="sp-tile">
            <span class="sp-label">এ মাস</span>
            <span class="sp-value">৳@bn($stats->month_sales)</span>
            <span class="sp-sub">চলতি মাস</span>
        </div>
        <div class="sp-tile">
            <span class="sp-label">বিক্রিত কপি</span>
            <span class="sp-value">@bn($itemsSold)</span>
            <span class="sp-sub">সব বিল মিলিয়ে</span>
        </div>
    </div>

    <div class="row g-3">
        {{-- Latest bills --}}
        <div class="col-12 col-lg-8">
            <section class="sp-card h-100">
                <div class="sp-card-head">
                    <h2>সাম্প্রতিক বিল</h2>
                    <a href="{{ route('subadmin.bills.index', $sellerId ? ['seller_id' => $sellerId] : []) }}" class="small text-decoration-none">সব বিল (@bn($stats->bills)) →</a>
                </div>

                @if($recentBills->isEmpty())
                    <div class="sp-empty">
                        <i class="fas fa-file-invoice fs-3 d-block mb-2 opacity-50"></i>
                        এখনো কোনো বিল নেই।
                        <div class="mt-2"><a href="{{ route('subadmin.bills.create') }}" class="btn btn-sm btn-success rounded-pill px-3">প্রথম বিল তৈরি করুন</a></div>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table sp-table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>বিল</th>
                                    <th>গ্রাহক</th>
                                    @if($isAdmin && !$sellerId)<th>বিক্রেতা</th>@endif
                                    <th class="text-end">পরিমাণ</th>
                                    <th>অবস্থা</th>
                                    <th class="text-end">অ্যাকশন</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentBills as $bill)
                                    @php
                                        $tone  = match ($bill->payment_status) { 'paid' => ['success', 'পরিশোধিত'], 'partial' => ['warning', 'আংশিক'], default => ['danger', 'বকেয়া'] };
                                    @endphp
                                    <tr>
                                        <td>
                                            <a href="{{ route('subadmin.bills.show', $bill->id) }}" class="fw-semibold text-decoration-none font-monospace">{{ $bill->bill_no }}</a>
                                            <small class="d-block text-muted">{{ $bill->created_at->format('d M Y') }}</small>
                                        </td>
                                        <td>
                                            <div class="text-truncate" style="max-width: 180px">{{ $bill->customer_name ?: 'নগদ গ্রাহক' }}</div>
                                            @if($bill->customer_phone)<small class="text-muted font-monospace">{{ $bill->customer_phone }}</small>@endif
                                        </td>
                                        @if($isAdmin && !$sellerId)
                                            <td class="text-muted">{{ $bill->seller?->name ?? '—' }}</td>
                                        @endif
                                        <td class="text-end">
                                            <span class="fw-semibold">৳@bn($bill->total, 2)</span>
                                            @if($bill->payment_status !== 'paid' && (float) $bill->due_amount > 0)
                                                <small class="d-block text-danger">বাকি ৳@bn($bill->due_amount, 2)</small>
                                            @endif
                                        </td>
                                        <td><span class="badge rounded-pill text-bg-{{ $tone[0] }}">{{ $tone[1] }}</span></td>
                                        <td class="text-end text-nowrap">
                                            <a href="{{ route('subadmin.bills.show', $bill->id) }}" class="btn btn-sm btn-light rounded-circle" title="দেখুন"><i class="fas fa-eye"></i></a>
                                            <a href="{{ route('subadmin.bills.receipt', $bill->id) }}" target="_blank" rel="noopener" class="btn btn-sm btn-light rounded-circle" title="রসিদ প্রিন্ট"><i class="fas fa-print"></i></a>
                                            @if($bill->payment_status !== 'paid')
                                                <form action="{{ route('subadmin.bills.quick-pay', $bill->id) }}" method="POST" class="d-inline" onsubmit="return confirm('বিলটি পরিশোধিত হিসেবে চিহ্নিত করবেন?')">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-success rounded-circle" title="পরিশোধিত করুন"><i class="fas fa-check"></i></button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>
        </div>

        {{-- Books --}}
        <div class="col-12 col-lg-4">
            <section class="sp-card h-100">
                <div class="sp-card-head">
                    <h2>জনপ্রিয় বই</h2>
                    <a href="{{ route('book.index') }}" target="_blank" rel="noopener" class="small text-decoration-none">সব বই →</a>
                </div>
                @forelse($popularBooks as $book)
                    <div class="sp-book">
                        <div class="min-w-0">
                            <div class="fw-semibold text-truncate">{{ $book->title }}</div>
                            <small class="text-muted">৳@bn($book->price)</small>
                        </div>
                        <span class="badge rounded-pill text-bg-{{ $book->stock_quantity > 0 ? 'success' : 'danger' }} flex-shrink-0">
                            {{ $book->stock_quantity > 0 ? 'স্টক ' . \App\Support\Bn::num($book->stock_quantity) : 'স্টক নেই' }}
                        </span>
                    </div>
                @empty
                    <div class="sp-empty">কোনো বই নেই।</div>
                @endforelse
            </section>
        </div>
    </div>
</div>
@endsection
