@extends('layouts.admin')

@section('title', 'ক্রয় ইনভয়েস #' . $purchase->purchase_no)
@section('heading', 'ক্রয় ইনভয়েস ও পেমেন্ট বিবরণী')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.purchases.index') }}">প্রকাশনী ক্রয়</a></li>
    <li class="breadcrumb-item active" aria-current="page">#{{ $purchase->purchase_no }}</li>
@endsection

@section('actions')
    <button type="button" class="btn btn-outline-dark" onclick="window.print()">
        <i class="fas fa-print me-1"></i> প্রিন্ট ইনভয়েস
    </button>
    @if($purchase->due_amount > 0)
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#paymentModal">
            <i class="fas fa-hand-holding-dollar me-1"></i> কিস্তি / বকেয়া পরিশোধ করুন
        </button>
    @endif
    <a href="{{ route('admin.purchases.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> তালিকায় ফিরুন
    </a>
@endsection

@section('content')

<div class="row g-4">
    {{-- Invoice Main Card --}}
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 mb-4 p-4 bg-white" id="printableInvoice">
            <div class="d-flex flex-wrap justify-content-between align-items-start border-bottom pb-4 mb-4 gap-3">
                <div>
                    <h3 class="fw-bold text-primary mb-1">{{ config('brand.name', 'IdeaABD') }}</h3>
                    <p class="text-muted small mb-0">প্রকাশনী ক্রয় ইনভয়েস ও চালান</p>
                </div>
                <div class="text-md-end">
                    <h4 class="fw-bold text-dark mb-1">ইনভয়েস #{{ $purchase->purchase_no }}</h4>
                    <div class="text-muted small">তারিখ: <strong>@bnDate($purchase->purchase_date)</strong></div>
                    <div class="mt-2">
                        @if($purchase->payment_status === 'paid')
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1.5 rounded-pill fs-6">
                                <i class="fas fa-circle-check me-1"></i> সম্পূর্ণ পরিশোধিত
                            </span>
                        @elseif($purchase->payment_status === 'partial')
                            <span class="badge bg-warning-subtle text-dark border border-warning-subtle px-3 py-1.5 rounded-pill fs-6">
                                <i class="fas fa-circle-half-stroke me-1"></i> আংশিক বকেয়া
                            </span>
                        @else
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-1.5 rounded-pill fs-6">
                                <i class="fas fa-circle-exclamation me-1"></i> সম্পূর্ণ বকেয়া
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Publisher Info --}}
            <div class="row mb-4 p-3 bg-light rounded-4">
                <div class="col-md-6 mb-2 mb-md-0">
                    <span class="text-muted small text-uppercase fw-semibold">প্রকাশনী / সরবরাহকারী:</span>
                    <h5 class="fw-bold text-dark mt-1 mb-1">{{ $purchase->publisher->name ?? '—' }}</h5>
                    @if($purchase->publisher?->address)
                        <div class="text-muted small"><i class="fas fa-map-marker-alt me-1"></i>{{ $purchase->publisher->address }}</div>
                    @endif
                    @if($purchase->publisher?->phone)
                        <div class="text-muted small"><i class="fas fa-phone me-1"></i>{{ $purchase->publisher->phone }}</div>
                    @endif
                </div>
                <div class="col-md-6 text-md-end">
                    <span class="text-muted small text-uppercase fw-semibold">ক্রয়ের বিবরণ:</span>
                    <div class="mt-1">
                        @if($purchase->publisher_memo_no)
                            <div class="text-primary fw-bold mb-1"><i class="fas fa-receipt me-1"></i>প্রকাশকের মেমো নং: #{{ $purchase->publisher_memo_no }}</div>
                        @endif
                        <div>ক্রয়ের ধরন: <strong>{{ ['cash' => 'নগদে ক্রয়', 'credit' => 'বাকিতে ক্রয়', 'partial' => 'আংশিক বাকি'][$purchase->payment_type] ?? $purchase->payment_type }}</strong></div>
                        <div>এন্ট্রি করেছেন: <strong>{{ $purchase->creator->name ?? 'অ্যাডমিন' }}</strong></div>
                    </div>
                </div>
            </div>

            {{-- Items Table --}}
            <div class="table-responsive mb-4">
                <table class="table table-bordered align-middle">
                    <thead class="table-light text-center small text-muted text-uppercase">
                        <tr>
                            <th class="ps-3" style="width: 40px;">#</th>
                            <th class="text-start">বইয়ের বিবরণ</th>
                            <th>পরিমাণ</th>
                            <th>মূল্য (MRP)</th>
                            <th>কমিশন %</th>
                            <th>ক্রয়মূল্য</th>
                            <th>শপ বিক্রয়মূল্য</th>
                            <th class="text-end pe-3">মোট ক্রয়</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($purchase->items as $i => $item)
                            <tr>
                                <td class="ps-3 text-muted small text-center">@bn($i + 1)</td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $item->book_title }}</div>
                                    <div class="small text-muted">
                                        লেখক: {{ $item->author_name ?? '—' }} | ক্যাটাগরি: {{ $item->category->name ?? $item->book?->category?->name ?? '—' }}
                                    </div>
                                    @if($item->book)
                                        <a href="{{ route('shop.show', $item->book->slug) }}" target="_blank" class="small text-primary text-decoration-none d-inline-block mt-0.5">
                                            <i class="fas fa-arrow-up-right-from-square me-1"></i>বুকশপে লাইভ দেখুন (স্টক: @bn($item->book->stock_quantity))
                                        </a>
                                    @endif
                                </td>
                                <td class="text-center fw-bold">@bn($item->quantity) টি</td>
                                <td class="text-center">@taka($item->mrp_price > 0 ? $item->mrp_price : $item->unit_cost_price)</td>
                                <td class="text-center text-danger fw-semibold">
                                    {{ $item->purchase_commission_percent > 0 ? $item->purchase_commission_percent . '%' : '—' }}
                                </td>
                                <td class="text-center fw-bold text-danger">@taka($item->unit_cost_price)</td>
                                <td class="text-center text-success">
                                    <strong>@taka($item->unit_sale_price)</strong>
                                    @if($item->shop_discount_percent > 0)
                                        <div class="small text-muted">(@bn($item->shop_discount_percent)% ছাড়)</div>
                                    @endif
                                </td>
                                <td class="text-end pe-3 fw-bold text-dark">@taka($item->subtotal)</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($purchase->notes)
                <div class="p-3 bg-light rounded-3 text-muted small mb-4">
                    <strong class="text-dark">নোট / মেমো:</strong> {{ $purchase->notes }}
                </div>
            @endif
        </div>

        {{-- Repayment Installments History Table --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h5 class="fw-bold mb-0 text-success"><i class="fas fa-clock-rotate-left me-2"></i>পরিশোধের ইতিহাস ও কিস্তি তালিকা</h5>
                @if($purchase->due_amount > 0)
                    <button type="button" class="btn btn-sm btn-success rounded-pill px-3 fw-semibold" data-bs-toggle="modal" data-bs-target="#paymentModal">
                        <i class="fas fa-plus me-1"></i> নতুন কিস্তি পরিশোধ
                    </button>
                @endif
            </div>
            <div class="card-body p-0">
                @if($purchase->payments->isEmpty())
                    <div class="p-4 text-center text-muted">
                        <i class="fas fa-money-bill-wave fs-2 opacity-50 mb-2"></i>
                        <p class="mb-0">এখনো কোনো পেমেন্ট রেকর্ড করা হয়নি।</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr class="small text-muted text-uppercase">
                                    <th class="ps-3">রসিদ #</th>
                                    <th>তারিখ</th>
                                    <th>পরিশোধের পরিমাণ</th>
                                    <th>মাধ্যম</th>
                                    <th>রেফারেন্স</th>
                                    <th>গ্রহণকারী</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($purchase->payments as $pay)
                                    <tr>
                                        <td class="ps-3 fw-semibold text-primary">{{ $pay->payment_no }}</td>
                                        <td>@bnDate($pay->payment_date)</td>
                                        <td class="fw-bold text-success">@taka($pay->amount)</td>
                                        <td>
                                            <span class="badge bg-light text-dark border">
                                                {{ $paymentMethods[$pay->payment_method] ?? $pay->payment_method }}
                                            </span>
                                        </td>
                                        <td class="text-muted small">{{ $pay->transaction_ref ?? '—' }}</td>
                                        <td class="text-muted small">{{ $pay->recorder->name ?? 'অ্যাডমিন' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Right Summary Card --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white sticky-top" style="top: 80px;">
            <h5 class="fw-bold text-dark border-bottom pb-3 mb-3"><i class="fas fa-coins me-2 text-warning"></i>পেমেন্ট সামারি</h5>

            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted">বইয়ের মোট ক্রয়মূল্য:</span>
                <span class="fw-semibold text-dark">@taka($purchase->total_amount)</span>
            </div>

            @if($purchase->discount_amount > 0)
                <div class="d-flex justify-content-between align-items-center mb-2 text-danger">
                    <span>ছাড় / ডিসকাউন্ট:</span>
                    <span>- @taka($purchase->discount_amount)</span>
                </div>
            @endif

            <hr>

            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="fw-bold fs-6">সর্বমোট প্রদেয়:</span>
                <span class="fw-bold fs-5 text-primary">@taka($purchase->grand_total)</span>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-2 text-success">
                <span class="fw-semibold">মোট পরিশোধিত:</span>
                <span class="fw-bold fs-5">@taka($purchase->paid_amount)</span>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4 p-3 bg-danger-subtle text-danger rounded-3">
                <span class="fw-bold">অবশিষ্ট বকেয়া (Due):</span>
                <span class="fw-bold fs-4">@taka($purchase->due_amount)</span>
            </div>

            @if($purchase->due_amount > 0)
                <button type="button" class="btn btn-success w-100 py-2.5 rounded-pill fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#paymentModal">
                    <i class="fas fa-hand-holding-dollar me-1.5"></i> বকেয়া / কিস্তি পরিশোধ করুন
                </button>
            @else
                <div class="alert alert-success text-center mb-0 rounded-pill py-2">
                    <i class="fas fa-check-circle me-1"></i> এই ইনভয়েসের সম্পূর্ণ টাকা পরিশোধিত
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Payment Modal --}}
@if($purchase->due_amount > 0)
<div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom py-3 bg-light">
                <h5 class="modal-title fw-bold text-success"><i class="fas fa-hand-holding-dollar me-2"></i>কিস্তি / বকেয়া পেমেন্ট এন্ট্রি</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.purchases.payments.store') }}" method="POST">
                @csrf
                <input type="hidden" name="purchase_id" value="{{ $purchase->id }}">

                <div class="modal-body p-4">
                    <div class="alert alert-info py-2 rounded-3 small mb-3">
                        প্রকাশনী: <strong>{{ $purchase->publisher->name }}</strong> | বর্তমান বকেয়া: <strong>@taka($purchase->due_amount)</strong>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">পরিশোধের তারিখ <span class="text-danger">*</span></label>
                        <input type="date" name="payment_date" class="form-control rounded-3" value="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">টাকার পরিমাণ (৳) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="amount" class="form-control rounded-3 fw-bold text-success fs-5" 
                               value="{{ $purchase->due_amount }}" max="{{ $purchase->due_amount }}" min="1" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">পেমেন্ট মাধ্যম <span class="text-danger">*</span></label>
                        <select name="payment_method" class="form-select rounded-3" required>
                            @foreach($paymentMethods as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">চেক / ট্রানজেকশন রেফারেন্স নম্বর</label>
                        <input type="text" name="transaction_ref" class="form-control rounded-3" placeholder="ঐচ্ছিক (Bank Trx ID / Check No)">
                    </div>

                    <div class="mb-2">
                        <label class="form-label small fw-semibold text-muted">মন্তব্য / নোট</label>
                        <textarea name="note" rows="2" class="form-control rounded-3" placeholder="পেমেন্ট সংক্রান্ত কোনো বিবরণ থাকলে লিখুন..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal">বন্ধ করুন</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold">পেমেন্ট নিশ্চিত করুন</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@endsection
