@extends('layouts.admin')

@php
    $typeTitles = [
        'challan'   => 'ডেলিভারি চালান #',
        'quotation' => 'কোটেশন / প্রফর্মা #',
        'tender'    => 'দরপত্র প্রস্তাবনা #',
        'invoice'   => 'বিল / ক্যাশ মেমো #'
    ];
    $docTitle = ($typeTitles[$invoice->type] ?? 'ইনভয়েস #') . $invoice->invoice_no;
@endphp

@section('title', $docTitle)
@section('heading', $invoice->type_label . ' কপি')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.accounting.index') }}">হিসাব ও আয়-ব্যয়</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.accounting.invoices.index') }}">বিল, চালান ও দরপত্র</a></li>
    <li class="breadcrumb-item active" aria-current="page">#{{ $invoice->invoice_no }}</li>
@endsection

@section('actions')
    <div class="d-flex flex-wrap gap-2">
        <button type="button" class="btn btn-primary shadow-sm fw-semibold" onclick="window.print()">
            <i class="fas fa-print me-1.5"></i> প্রিন্ট কপি (Print / PDF)
        </button>

        {{-- Convert to Invoice/Challan if currently Quotation or Tender --}}
        @if(in_array($invoice->type, ['quotation', 'tender']))
            <form action="{{ route('admin.accounting.invoices.convert', $invoice->id) }}" method="POST" class="d-inline"
                  onsubmit="return confirm('আপনি কি এই দরপত্র/কোটেশনটিকে চূড়ান্ত বিল/ইনভয়েসে রূপান্তর করতে চান?')">
                @csrf
                <input type="hidden" name="target_type" value="invoice">
                <button type="submit" class="btn btn-success fw-semibold shadow-sm">
                    <i class="fas fa-receipt me-1"></i> বিলে রূপান্তর করুন
                </button>
            </form>

            <form action="{{ route('admin.accounting.invoices.convert', $invoice->id) }}" method="POST" class="d-inline"
                  onsubmit="return confirm('আপনি কি এই দরপত্র/কোটেশনটিকে ডেলিভারি চালানে রূপান্তর করতে চান?')">
                @csrf
                <input type="hidden" name="target_type" value="challan">
                <button type="submit" class="btn btn-info text-white fw-semibold shadow-sm">
                    <i class="fas fa-truck me-1"></i> চালানে রূপান্তর
                </button>
            </form>
        @endif

        <a href="{{ route('admin.accounting.invoices.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> তালিকায় ফিরুন
        </a>
    </div>
@endsection

@section('content')

<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white" id="printableMemo">
            
            {{-- Institutional / Company Header --}}
            <div class="d-flex flex-wrap justify-content-between align-items-start border-bottom pb-4 mb-4 gap-3">
                <div>
                    <h2 class="fw-bold text-primary mb-1">{{ config('brand.name', 'আইডিয়া প্রকাশন') }}</h2>
                    <p class="text-muted small mb-0">{{ config('brand.tagline', 'বই প্রকাশনা, মুদ্রণ ও পরিবেশনা') }}</p>
                    <div class="text-muted small mt-1">ঢাকা, বাংলাদেশ · মোবাইল: 018XXXXXXXX · ইমেইল: info@ideaabd.com</div>
                </div>
                <div class="text-md-end">
                    @php
                        $badgeStyles = [
                            'challan'   => 'background-color: #e0f2fe; color: #0369a1; border-color: #7dd3fc;',
                            'quotation' => 'background-color: #fef3c7; color: #b45309; border-color: #fcd34d;',
                            'tender'    => 'background-color: #f3e8ff; color: #7e22ce; border-color: #d8b4fe;',
                            'invoice'   => 'background-color: #dcfce7; color: #15803d; border-color: #86efac;',
                        ];
                        $badgeTitles = [
                            'challan'   => 'ডেলিভারি চালান (DELIVERY CHALLAN)',
                            'quotation' => 'মূল্য কোটেশন (PRICE QUOTATION)',
                            'tender'    => 'দরপত্র প্রস্তাবনা (TENDER PROPOSAL)',
                            'invoice'   => 'ক্যাশ মেমো / বিল (INVOICE / BILL)',
                        ];
                    @endphp
                    <span class="badge border px-3 py-1.5 rounded-pill fs-6 mb-2 d-inline-block fw-bold" style="{{ $badgeStyles[$invoice->type] ?? $badgeStyles['invoice'] }}">
                        {{ $badgeTitles[$invoice->type] ?? 'বিল / ক্যাশ মেমো' }}
                    </span>
                    <h4 class="fw-bold text-dark mb-1">#{{ $invoice->invoice_no }}</h4>
                    <div class="text-muted small">তারিখ: <strong>@bnDate($invoice->invoice_date)</strong></div>
                    @if($invoice->valid_until)
                        <div class="text-danger small mt-0.5 fw-semibold"><i class="fas fa-hourglass-half me-1"></i>কার্যকারিতা মেয়াদ: @bnDate($invoice->valid_until) পর্যন্ত</div>
                    @endif
                </div>
            </div>

            {{-- Subject and Tender Reference (for Tender & Quotation) --}}
            @if($invoice->subject || $invoice->reference_no)
                <div class="p-3 bg-light rounded-3 border mb-4">
                    @if($invoice->reference_no)
                        <div class="small text-muted mb-1">
                            <strong class="text-dark">দরপত্র / স্মারক নং:</strong> <span class="font-monospace fw-bold text-dark">{{ $invoice->reference_no }}</span>
                        </div>
                    @endif
                    @if($invoice->subject)
                        <div class="small">
                            <strong class="text-dark">বিষয়:</strong> <span class="fw-bold text-primary">{{ $invoice->subject }}</span>
                        </div>
                    @endif
                </div>
            @endif

            {{-- Customer & Billed To Info --}}
            <div class="row mb-4 p-3 bg-light rounded-4">
                <div class="col-md-6 mb-2 mb-md-0">
                    <span class="text-muted small text-uppercase fw-semibold">প্রাপক / প্রতিষ্ঠান তথ্য:</span>
                    <h5 class="fw-bold text-dark mt-1 mb-1">{{ $invoice->customer_name }}</h5>
                    @if($invoice->customer_phone)
                        <div class="text-muted small"><i class="fas fa-phone me-1"></i>{{ $invoice->customer_phone }}</div>
                    @endif
                    @if($invoice->customer_address)
                        <div class="text-muted small"><i class="fas fa-location-dot me-1"></i>{{ $invoice->customer_address }}</div>
                    @endif
                </div>
                <div class="col-md-6 text-md-end">
                    <span class="text-muted small text-uppercase fw-semibold">অর্ডার ও পেমেন্ট বিবরণ:</span>
                    <div class="mt-1">
                        <div>ডকুমেন্ট ধরন: <strong>{{ $invoice->type_label }}</strong></div>
                        @if(in_array($invoice->type, ['invoice', 'challan']))
                            <div>পেমেন্ট মাধ্যম: <strong>{{ $invoice->payment_method }}</strong></div>
                            <div>
                                পেমেন্ট স্ট্যাটাস: 
                                @if($invoice->payment_status === 'paid')
                                    <span class="badge bg-success-subtle text-success border">পরিশোধিত</span>
                                @elseif($invoice->payment_status === 'partial')
                                    <span class="badge bg-warning-subtle text-dark border">আংশিক বকেয়া</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border">বকেয়া</span>
                                @endif
                            </div>
                        @else
                            <div>প্রস্তাবনা স্ট্যাটাস: <span class="badge bg-primary-subtle text-primary border">প্রস্তাবিত (Draft/Offered)</span></div>
                        @endif
                        <div class="text-muted small mt-1">প্রস্তুতকারী: <strong>{{ $invoice->creator->name ?? 'অ্যাডমিন' }}</strong></div>
                    </div>
                </div>
            </div>

            {{-- Items / Price Schedule Table --}}
            <div class="table-responsive mb-4">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr class="small text-muted text-uppercase">
                            <th class="ps-3" style="width: 40px;">#</th>
                            <th>বিবরণ / বই বা সেবার নাম</th>
                            <th style="width: 130px;">ধরন</th>
                            <th class="text-center" style="width: 100px;">পরিমাণ</th>
                            <th class="text-end" style="width: 120px;">দর / একক মূল্য (৳)</th>
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
                            <td class="text-end fw-bold fs-6">সর্বমোট প্রস্তাবিত মূল্য:</td>
                            <td class="text-end pe-3 fw-bold fs-5 text-primary">@taka($invoice->grand_total)</td>
                        </tr>
                        @if(in_array($invoice->type, ['invoice', 'challan']))
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
                        @endif
                    </tfoot>
                </table>
            </div>

            {{-- Notes --}}
            @if($invoice->notes)
                <div class="p-3 bg-light rounded-3 text-muted small mb-3">
                    <strong class="text-dark"><i class="fas fa-note-sticky me-1 text-primary"></i>বিশেষ নোট:</strong> {{ $invoice->notes }}
                </div>
            @endif

            {{-- Terms & Conditions for Tender / Quotation --}}
            @if($invoice->terms_conditions)
                <div class="p-3 bg-light rounded-3 text-muted small mb-4 border">
                    <strong class="text-dark d-block mb-1"><i class="fas fa-file-contract me-1 text-primary"></i>দরপত্র / কোটেশনের শর্তাবলী (Terms & Conditions):</strong>
                    <div style="white-space: pre-line;">{{ $invoice->terms_conditions }}</div>
                </div>
            @endif

            {{-- Institutional Signature Footers --}}
            <div class="row pt-5 mt-4 text-center">
                <div class="col-4">
                    <div class="border-top border-dark pt-2 small fw-semibold">
                        {{ in_array($invoice->type, ['quotation', 'tender']) ? 'দরপত্র আহ্বানকারী / গ্রাহক স্বাক্ষর' : 'গ্রহীতার স্বাক্ষর' }}
                    </div>
                </div>
                <div class="col-4"></div>
                <div class="col-4">
                    <div class="border-top border-dark pt-2 small fw-semibold">
                        অনুমোদিত স্বাক্ষর ও সিল<br>
                        <span class="text-muted" style="font-size: 11px;">(আইডিয়া প্রকাশন)</span>
                    </div>
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
