@extends('layouts.admin')

@section('title', ($invoice->type === 'challan' ? 'ডেলিভারি চালান #' : 'বিল ইনভয়েস #') . $invoice->invoice_no)
@section('heading', $invoice->type === 'challan' ? 'ডেলিভারি চালান কপি' : 'বিল / ক্যাশ মেমো কপি')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.accounting.index') }}">হিসাব ও আয়-ব্যয়</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.accounting.invoices.index') }}">বিল ও চালান</a></li>
    <li class="breadcrumb-item active" aria-current="page">#{{ $invoice->invoice_no }}</li>
@endsection

@section('actions')
    <button type="button" class="btn btn-outline-dark" onclick="window.print()">
        <i class="fas fa-print me-1"></i> প্রিন্ট চালান / বিল
    </button>
    <a href="{{ route('admin.accounting.invoices.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> তালিকায় ফিরুন
    </a>
@endsection

@section('content')

<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white" id="printableMemo">
            {{-- Header --}}
            <div class="d-flex flex-wrap justify-content-between align-items-start border-bottom pb-4 mb-4 gap-3">
                <div>
                    <h2 class="fw-bold text-primary mb-1">{{ config('brand.name', 'আইডিয়া প্রকাশন') }}</h2>
                    <p class="text-muted small mb-0">{{ config('brand.tagline', 'বই প্রকাশনা ও পরিবেশনা') }}</p>
                    <div class="text-muted small mt-1">ঢাকা, বাংলাদেশ · মোবাইল: 018XXXXXXXX</div>
                </div>
                <div class="text-md-end">
                    <span class="badge {{ $invoice->type === 'challan' ? 'bg-info-subtle text-dark border-info' : 'bg-primary-subtle text-primary border-primary' }} border px-3 py-1.5 rounded-pill fs-6 mb-2 d-inline-block">
                        {{ $invoice->type === 'challan' ? 'ডেলিভারি চালান (CHALLAN)' : 'ক্যাশ মেমো / বিল (INVOICE)' }}
                    </span>
                    <h4 class="fw-bold text-dark mb-1">#{{ $invoice->invoice_no }}</h4>
                    <div class="text-muted small">তারিখ: <strong>@bnDate($invoice->invoice_date)</strong></div>
                </div>
            </div>

            {{-- Customer & Billed To --}}
            <div class="row mb-4 p-3 bg-light rounded-4">
                <div class="col-md-6 mb-2 mb-md-0">
                    <span class="text-muted small text-uppercase fw-semibold">প্রাপক / গ্রাহক তথ্য:</span>
                    <h5 class="fw-bold text-dark mt-1 mb-1">{{ $invoice->customer_name }}</h5>
                    @if($invoice->customer_phone)
                        <div class="text-muted small"><i class="fas fa-phone me-1"></i>{{ $invoice->customer_phone }}</div>
                    @endif
                    @if($invoice->customer_address)
                        <div class="text-muted small"><i class="fas fa-location-dot me-1"></i>{{ $invoice->customer_address }}</div>
                    @endif
                </div>
                <div class="col-md-6 text-md-end">
                    <span class="text-muted small text-uppercase fw-semibold">পেমেন্ট ও অর্ডার বিবরণ:</span>
                    <div class="mt-1">
                        <div>পেমেন্ট মাধ্যম: <strong>{{ $invoice->payment_method }}</strong></div>
                        <div>
                            স্ট্যাটাস: 
                            @if($invoice->payment_status === 'paid')
                                <span class="badge bg-success-subtle text-success border">পরিশোধিত</span>
                            @elseif($invoice->payment_status === 'partial')
                                <span class="badge bg-warning-subtle text-dark border">আংশিক বকেয়া</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border">বকেয়া</span>
                            @endif
                        </div>
                        <div>প্রস্তুতকারী: <strong>{{ $invoice->creator->name ?? 'অ্যাডমিন' }}</strong></div>
                    </div>
                </div>
            </div>

            {{-- Items Table --}}
            <div class="table-responsive mb-4">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr class="small text-muted text-uppercase">
                            <th class="ps-3" style="width: 40px;">#</th>
                            <th>বিবরণ / আইটেম</th>
                            <th style="width: 120px;">ধরন</th>
                            <th class="text-center" style="width: 100px;">পরিমাণ</th>
                            <th class="text-end" style="width: 120px;">একক দর (৳)</th>
                            <th class="text-end pe-3" style="width: 140px;">মোট টাকা (৳)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->items as $idx => $item)
                            <tr>
                                <td class="ps-3 text-muted small">@bn($idx + 1)</td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $item['title'] ?? '—' }}</div>
                                </td>
                                <td><span class="badge bg-light text-dark border">{{ $item['item_type'] ?? 'বই' }}</span></td>
                                <td class="text-center fw-bold">@bn($item['quantity'] ?? 1)</td>
                                <td class="text-end">@taka($item['unit_price'] ?? 0)</td>
                                <td class="text-end pe-3 fw-bold text-dark">@taka($item['subtotal'] ?? 0)</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="border-0"></td>
                            <td class="text-end fw-semibold">উপ-যোগফল:</td>
                            <td class="text-end pe-3 fw-semibold">@taka($invoice->subtotal)</td>
                        </tr>
                        @if($invoice->discount > 0)
                            <tr>
                                <td colspan="4" class="border-0"></td>
                                <td class="text-end text-danger fw-semibold">বিশেষ ছাড়:</td>
                                <td class="text-end pe-3 text-danger fw-semibold">- @taka($invoice->discount)</td>
                            </tr>
                        @endif
                        @if($invoice->tax > 0)
                            <tr>
                                <td colspan="4" class="border-0"></td>
                                <td class="text-end text-muted fw-semibold">ভ্যাট / ট্যাক্স:</td>
                                <td class="text-end pe-3 text-muted fw-semibold">+ @taka($invoice->tax)</td>
                            </tr>
                        @endif
                        <tr class="table-light">
                            <td colspan="4" class="border-0"></td>
                            <td class="text-end fw-bold fs-6">সর্বমোট বিল:</td>
                            <td class="text-end pe-3 fw-bold fs-5 text-primary">@taka($invoice->grand_total)</td>
                        </tr>
                        <tr>
                            <td colspan="4" class="border-0"></td>
                            <td class="text-end text-success fw-bold">পরিশোধিত:</td>
                            <td class="text-end pe-3 text-success fw-bold">@taka($invoice->paid_amount)</td>
                        </tr>
                        @if($invoice->due_amount > 0)
                            <tr class="table-danger">
                                <td colspan="4" class="border-0"></td>
                                <td class="text-end text-danger fw-bold">অবশিষ্ট বকেয়া (Due):</td>
                                <td class="text-end pe-3 text-danger fw-bold fs-6">@taka($invoice->due_amount)</td>
                            </tr>
                        @endif
                    </tfoot>
                </table>
            </div>

            @if($invoice->notes)
                <div class="p-3 bg-light rounded-3 text-muted small mb-4">
                    <strong class="text-dark">শর্তাবলী / নোট:</strong> {{ $invoice->notes }}
                </div>
            @endif

            {{-- Signature Footers --}}
            <div class="row pt-5 mt-4 text-center">
                <div class="col-4">
                    <div class="border-top border-dark pt-2 small fw-semibold">গ্রাহকের স্বাক্ষর</div>
                </div>
                <div class="col-4"></div>
                <div class="col-4">
                    <div class="border-top border-dark pt-2 small fw-semibold">অনুমোদিত স্বাক্ষর (আইডিয়া প্রকাশন)</div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .adm-side, .adm-top, .adm-actions, .btn, .breadcrumb, footer, .adm-side__backdrop {
        display: none !important;
    }
    .adm-main {
        margin-left: 0 !important;
        padding: 0 !important;
    }
    .card {
        border: none !important;
        box-shadow: none !important;
    }
}
</style>

@endsection
