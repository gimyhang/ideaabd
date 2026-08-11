@extends('layouts.admin')

@section('title', 'বিল #' . $bill->bill_no)
@section('heading', 'বিল #' . $bill->bill_no)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('subadmin.bills.index') }}" class="text-decoration-none">বিল তালিকা</a></li>
    <li class="breadcrumb-item active" aria-current="page">#{{ $bill->bill_no }}</li>
@endsection

@section('content')
<div style="max-width:760px">
    <div class="d-flex justify-content-between align-items-center mb-4 d-print-none">
        <h4 class="fw-bold mb-0" style="color:#0066cc">বিল #{{ $bill->bill_no }}</h4>
        <div class="d-flex gap-2">
            <button class="btn btn-sm btn-outline-secondary" onclick="window.print()"><i class="fas fa-print me-1"></i>প্রিন্ট</button>
            <a href="{{ route('subadmin.bills.index') }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-list me-1"></i>সব বিল</a>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            {{-- Header --}}
            <div class="d-flex justify-content-between align-items-start mb-4">
                <div>
                    <img src="{{ asset('images/logo.svg') }}" alt="ideaabd" width="40" class="me-2">
                    <span class="fw-bold fs-5" style="color:#0066cc">ideaabd</span>
                    <p class="text-muted small mt-1 mb-0">www.ideaabd.com | support@ideaabd.com</p>
                </div>
                <div class="text-end">
                    <p class="fw-bold mb-0 fs-5">ইনভয়েস</p>
                    <p class="text-muted small mb-0">#{{ $bill->bill_no }}</p>
                    <p class="text-muted small mb-0">তারিখ: {{ $bill->created_at->format('d M Y') }}</p>
                </div>
            </div>

            <hr>

            {{-- Bill To --}}
            <div class="row mb-4">
                <div class="col-6">
                    <p class="fw-bold small text-muted mb-1">বিল করা হয়েছে:</p>
                    <p class="fw-bold mb-0">{{ $bill->customer_name }}</p>
                    @if($bill->customer_phone)<p class="text-muted small mb-0">{{ $bill->customer_phone }}</p>@endif
                    @if($bill->customer_email)<p class="text-muted small mb-0">{{ $bill->customer_email }}</p>@endif
                </div>
                <div class="col-6 text-end">
                    <p class="fw-bold small text-muted mb-1">সেলার:</p>
                    <p class="fw-bold mb-0">{{ $bill->seller->name }}</p>
                    <p class="text-muted small mb-0">{{ $bill->seller->email }}</p>
                    <p class="mt-2 mb-0">
                        @if($bill->payment_status === 'paid')
                            <span class="badge bg-success px-3 py-2">পরিশোধিত</span>
                        @elseif($bill->payment_status === 'partial')
                            <span class="badge bg-warning text-dark px-3 py-2">আংশিক পরিশোধিত</span>
                        @else
                            <span class="badge bg-danger px-3 py-2">বকেয়া</span>
                        @endif
                    </p>
                </div>
            </div>

            {{-- Items table --}}
            <table class="table table-sm table-bordered mb-3">
                <thead style="background:#E8F4F8">
                    <tr>
                        <th>#</th>
                        <th>বইয়ের নাম</th>
                        <th class="text-center">পরিমাণ</th>
                        <th class="text-end">একক মূল্য</th>
                        <th class="text-end">মোট</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bill->items as $i => $item)
                    <tr>
                        <td>{{ $i+1 }}</td>
                        <td>{{ $item['title'] }}</td>
                        <td class="text-center">{{ $item['qty'] }}</td>
                        <td class="text-end">৳{{ number_format($item['price'],2) }}</td>
                        <td class="text-end">৳{{ number_format($item['qty']*$item['price'],2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr><td colspan="4" class="text-end fw-semibold">সাবটোটাল:</td><td class="text-end">৳{{ number_format($bill->subtotal,2) }}</td></tr>
                    @if($bill->discount > 0)
                    <tr><td colspan="4" class="text-end text-danger">ডিসকাউন্ট:</td><td class="text-end text-danger">-৳{{ number_format($bill->discount,2) }}</td></tr>
                    @endif
                    <tr class="table-active fw-bold"><td colspan="4" class="text-end fs-6">সর্বমোট:</td><td class="text-end fs-6 text-success">৳{{ number_format($bill->total,2) }}</td></tr>
                </tfoot>
            </table>

            <div class="row">
                <div class="col-6">
                    <p class="small text-muted mb-1"><strong>পেমেন্ট মেথড:</strong>
                        {{ ['cash'=>'নগদ','bkash'=>'বিকাশ','nagad'=>'নগদ','card'=>'কার্ড'][$bill->payment_method] ?? $bill->payment_method }}
                    </p>
                    @if($bill->notes)<p class="small text-muted mb-0"><strong>নোট:</strong> {{ $bill->notes }}</p>@endif
                </div>
                <div class="col-6 text-end">
                    <p class="small text-muted">ধন্যবাদ আপনার ক্রয়ের জন্য!</p>
                    <p class="small text-muted mb-0">ideaabd - আপনার বিশ্বস্ত বইয়ের সঙ্গী</p>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
@media print {
    .d-print-none { display:none !important; }
    .card { box-shadow:none !important; border:1px solid #ddd !important; }
    body { padding:0; }
}
</style>
@endsection
