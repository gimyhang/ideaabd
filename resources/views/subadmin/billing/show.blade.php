@extends('layouts.admin')

@section('title', 'বিল #' . $bill->bill_no)
@section('heading', 'বিল #' . $bill->bill_no)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('subadmin.bills.index') }}" class="text-decoration-none">বিল তালিকা</a></li>
    <li class="breadcrumb-item active" aria-current="page">#{{ $bill->bill_no }}</li>
@endsection

@section('content')
<div style="max-width:820px">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4 d-print-none">
        <h4 class="fw-bold mb-0 text-dark">
            <i class="fas fa-file-invoice-dollar text-primary me-2"></i>বিল #{{ $bill->bill_no }}
        </h4>
        <div class="d-flex flex-wrap gap-2">
            <button class="btn btn-outline-secondary" onclick="window.print()">
                <i class="fas fa-print me-1"></i> প্রিন্ট ইনভয়েস
            </button>
            <a href="{{ route('subadmin.bills.edit', $bill) }}" class="btn btn-outline-primary">
                <i class="fas fa-pen-to-square me-1"></i> এডিট করুন
            </a>
            <form method="POST" action="{{ route('subadmin.bills.destroy', $bill) }}" 
                  onsubmit="return confirm('আপনি কি নিশ্চিত যে এই বিলটি মুছে ফেলতে চান?');" class="d-inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger">
                    <i class="fas fa-trash-can me-1"></i> ডিলিট
                </button>
            </form>
            <a href="{{ route('subadmin.bills.index') }}" class="btn btn-light border">
                <i class="fas fa-list me-1"></i> সব বিল
            </a>
        </div>
    </div>

    <div class="adm-card border-0 shadow-sm">
        <div class="p-4 p-md-5">
            {{-- Header --}}
            <div class="d-flex justify-content-between align-items-start mb-4 pb-3 border-bottom">
                <div>
                    <h3 class="fw-bold text-primary mb-1">{{ config('brand.name', 'আইডিয়া প্রকাশন') }}</h3>
                    <p class="text-muted small mb-0">{{ config('brand.tagline', 'বই হোক মননশীল জীবনের অংশ') }}</p>
                    <p class="text-muted small mb-0">www.ideaabd.com | support@ideaabd.com</p>
                </div>
                <div class="text-end">
                    <span class="badge bg-primary text-white fs-6 px-3 py-1.5 mb-1">ক্যাশ মেমো / ইনভয়েস</span>
                    <p class="text-dark fw-bold mb-0 font-monospace">#{{ $bill->bill_no }}</p>
                    <p class="text-muted small mb-0">তারিখ: {{ $bill->created_at->format('d M Y, h:i A') }}</p>
                </div>
            </div>

            {{-- Bill To --}}
            <div class="row g-3 mb-4 p-3 bg-light rounded-3 border">
                <div class="col-12 col-sm-6">
                    <p class="fw-bold small text-muted text-uppercase mb-1">বিল প্রাপক (গ্রাহক):</p>
                    <p class="fw-bold text-dark fs-6 mb-0">{{ $bill->customer_name }}</p>
                    @if($bill->customer_phone)<p class="text-muted small mb-0"><i class="fas fa-phone me-1"></i>{{ $bill->customer_phone }}</p>@endif
                    @if($bill->customer_email)<p class="text-muted small mb-0"><i class="fas fa-envelope me-1"></i>{{ $bill->customer_email }}</p>@endif
                </div>
                <div class="col-12 col-sm-6 text-sm-end">
                    <p class="fw-bold small text-muted text-uppercase mb-1">বিল প্রস্তুতকারক (সেলার):</p>
                    <p class="fw-bold text-dark mb-0">{{ $bill->seller->name ?? 'অ্যাডমিন / সেলার' }}</p>
                    <p class="text-muted small mb-0">{{ $bill->seller->email ?? '' }}</p>
                    <div class="mt-2">
                        @if($bill->payment_status === 'paid')
                            <span class="badge bg-success px-3 py-1.5 fs-7"><i class="fas fa-check-circle me-1"></i>পরিশোধিত (Paid)</span>
                        @elseif($bill->payment_status === 'partial')
                            <span class="badge bg-warning text-dark px-3 py-1.5 fs-7"><i class="fas fa-clock me-1"></i>আংশিক পরিশোধ</span>
                        @else
                            <span class="badge bg-danger px-3 py-1.5 fs-7"><i class="fas fa-triangle-exclamation me-1"></i>বকেয়া (Unpaid)</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Items table --}}
            <div class="table-responsive mb-4">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 40px;">#</th>
                            <th>বইয়ের নাম</th>
                            <th class="text-center" style="width: 90px;">পরিমাণ</th>
                            <th class="text-end" style="width: 120px;">একক মূল্য</th>
                            <th class="text-center" style="width: 100px;">ছাড় (%)</th>
                            <th class="text-end" style="width: 130px;">মোট (৳)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bill->items as $i => $item)
                        @php
                            $qty = (int)($item['qty'] ?? 1);
                            $price = (float)($item['price'] ?? 0);
                            $discPct = (float)($item['discount_pct'] ?? 0);
                            $lineRaw = $qty * $price;
                            $lineDisc = $lineRaw * ($discPct / 100);
                            $lineTotal = (float)($item['line_total'] ?? ($lineRaw - $lineDisc));
                        @endphp
                        <tr>
                            <td class="text-center text-muted">@bn($i + 1)</td>
                            <td>
                                <div class="fw-bold text-dark">{{ $item['title'] }}</div>
                            </td>
                            <td class="text-center fw-bold">@bn($qty)</td>
                            <td class="text-end">৳{{ number_format($price, 2) }}</td>
                            <td class="text-center">
                                @if($discPct > 0)
                                    <span class="badge bg-success-subtle text-success border">@bn($discPct)%</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-end fw-bold text-dark">৳{{ number_format($lineTotal, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="5" class="text-end fw-semibold">বইগুলোর মূল সর্বমোট:</td>
                            <td class="text-end fw-bold">৳{{ number_format($bill->subtotal, 2) }}</td>
                        </tr>
                        @if($bill->discount > 0)
                        <tr>
                            <td colspan="5" class="text-end text-success fw-semibold">সর্বমোট ছাড় (Discount):</td>
                            <td class="text-end text-success fw-bold">-৳{{ number_format($bill->discount, 2) }}</td>
                        </tr>
                        @endif
                        <tr class="table-active">
                            <td colspan="5" class="text-end fw-bold fs-5 text-dark">সর্বমোট প্রদেয় বিল (Grand Total):</td>
                            <td class="text-end fw-bold fs-5 text-success">৳{{ number_format($bill->total, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="row g-3 pt-3 border-top">
                <div class="col-12 col-sm-6">
                    <p class="small text-muted mb-1">
                        <strong>পেমেন্ট মেথড:</strong>
                        <span class="text-dark fw-semibold">
                            {{ ['cash'=>'নগদ (Cash)','bkash'=>'বিকাশ (bKash)','nagad'=>'নগদ (Nagad)','card'=>'কার্ড (Card)'][$bill->payment_method] ?? $bill->payment_method }}
                        </span>
                    </p>
                    @if($bill->notes)
                        <p class="small text-muted mb-0"><strong>মন্তব্য / নোট:</strong> {{ $bill->notes }}</p>
                    @endif
                </div>
                <div class="col-12 col-sm-6 text-sm-end">
                    <p class="small text-muted mb-1">আইডিয়া প্রকাশনের সাথে থাকার জন্য ধন্যবাদ!</p>
                    <p class="small text-muted mb-0">www.ideaabd.com</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .d-print-none, .adm-side, .adm-topbar, .breadcrumb, footer { display: none !important; }
    .adm-main { margin-left: 0 !important; padding: 0 !important; }
    .adm-content { padding: 0 !important; }
    .adm-card { box-shadow: none !important; border: 1px solid #ddd !important; }
    body { background: #fff !important; color: #000 !important; }
}
</style>
@endsection
