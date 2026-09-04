@extends('layouts.app')

@php
    $settings = $invoiceSettings ?? \App\Http\Controllers\Admin\IdeaAccountingController::getInvoiceSettings();
    $bizLogo = $settings['logo'] ?? '/images/logo.png';
    $logoSrc = \App\Support\SiteSetting::resolveImageUrl($bizLogo, 'images/logo.png') ?: asset('images/logo.png');

    $isChallan = ($bill->type === 'challan');
    $isQuotation = ($bill->type === 'quotation');
    $docTitle = $bill->type_label . ' #' . $bill->bill_no;

    $totalQty = 0;
    foreach($bill->items ?? [] as $it) {
        $totalQty += (int)($it['qty'] ?? 1);
    }
@endphp

@section('title', $docTitle . ' — আইডিয়া প্রকাশন')

@section('content')
<div class="container-fluid py-4 px-md-4" style="max-width: 1440px;">

    {{-- Top Seller Header (Hidden on Print) --}}
    <div class="d-print-none">
        @include('seller.partials.header')
    </div>

    <div style="max-width: 960px;" class="mx-auto">

        {{-- Top Action Toolbar (Hidden on Print) --}}
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4 d-print-none">
            <div class="d-flex align-items-center gap-2">
                <span class="badge {{ $bill->type_badge_class }} px-3 py-1.5 fs-6 rounded-pill">
                    <i class="fas {{ $isChallan ? 'fa-truck' : ($isQuotation ? 'fa-file-lines' : 'fa-receipt') }} me-1"></i>
                    {{ $bill->type_label }}
                </span>
                <h4 class="fw-bold mb-0 text-dark font-monospace">#{{ $bill->bill_no }}</h4>
            </div>

            <div class="d-flex flex-wrap gap-2">
                <button class="btn btn-primary rounded-pill px-3.5 shadow-sm fw-semibold" onclick="window.print()">
                    <i class="fas fa-print me-1.5"></i> প্রিন্ট / PDF
                </button>
                <a href="{{ route('subadmin.bills.receipt', $bill) }}" target="_blank" class="btn btn-outline-secondary rounded-pill px-3 fw-semibold">
                    <i class="fas fa-receipt me-1"></i> রিসিট প্রিন্ট (POS)
                </a>
                <a href="{{ route('subadmin.bills.edit', $bill) }}" class="btn btn-warning text-dark rounded-pill px-3 fw-semibold">
                    <i class="fas fa-pen-to-square me-1"></i> এডিট করুন
                </a>
                <a href="{{ route('subadmin.bills.create') }}" class="btn btn-success rounded-pill px-3 fw-semibold">
                    <i class="fas fa-plus-circle me-1"></i> নতুন বিল
                </a>
                <a href="{{ route('subadmin.bills.index') }}" class="btn btn-light border rounded-pill px-3">
                    <i class="fas fa-list me-1"></i> সব বিল
                </a>
            </div>
        </div>

        {{-- ══ Printable Memo Card (Official Idea Publication Format) ══ --}}
        <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden memo-printable-card mb-5">
            <div class="p-4 p-md-5">

                {{-- Official Memo Branding Header --}}
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-start gap-3 pb-4 mb-4 border-bottom border-2">
                    <div class="d-flex align-items-center gap-3">
                        <img src="{{ $logoSrc }}" alt="Logo" style="height: 65px; width: auto; max-width: 150px;" class="object-fit-contain" onerror="this.style.display='none'">
                        <div>
                            <h3 class="fw-bold text-dark mb-0 fs-4">{{ $settings['company_name'] ?? config('brand.name', 'আইডিয়া প্রকাশন') }}</h3>
                            <p class="text-muted small mb-0">{{ $settings['company_tagline'] ?? config('brand.tagline', 'বই হোক মননশীল জীবনের অংশ') }}</p>
                            <p class="text-muted small mb-0" style="font-size: 11.5px;">
                                <i class="fas fa-location-dot me-1 text-primary"></i>{{ $settings['office_address'] ?? 'বাংলাবাজার / ঢাকা' }} | 
                                <i class="fas fa-phone me-1 text-primary"></i>{{ $settings['office_phone'] ?? '01712-345678' }}
                            </p>
                            @if(!empty($settings['office_email']))
                                <p class="text-muted small mb-0" style="font-size: 11px;">
                                    <i class="fas fa-envelope me-1 text-primary"></i>{{ $settings['office_email'] }} | 
                                    <i class="fas fa-globe me-1 text-primary"></i>www.ideaabd.com
                                </p>
                            @endif
                        </div>
                    </div>

                    <div class="text-sm-end">
                        <div class="d-inline-block p-2 px-3 rounded-3 text-white fw-bold {{ $isChallan ? 'bg-info' : ($isQuotation ? 'bg-warning text-dark' : 'bg-primary') }} mb-2">
                            <span class="fs-6 text-uppercase">
                                @if($isChallan) ডেলিভারি চালান (DELIVERY CHALLAN)
                                @elseif($isQuotation) কোটেশন (PROFORMA QUOTATION)
                                @else ক্যাশ মেমো ও ইনভয়েস (CASH MEMO) @endif
                            </span>
                        </div>
                        <div class="font-monospace fw-bold text-dark fs-6">#{{ $bill->bill_no }}</div>
                        <div class="small text-muted">তারিখ: <strong>{{ ($bill->bill_date ?? $bill->created_at)->format('d F, Y') }}</strong></div>
                        @if($bill->reference_no)
                            <div class="small text-muted">স্মারক / Ref: <strong>{{ $bill->reference_no }}</strong></div>
                        @endif
                    </div>
                </div>

                {{-- Subject Banner if provided --}}
                @if($bill->subject)
                    <div class="p-2.5 px-3 bg-light rounded-3 border mb-4">
                        <span class="fw-bold text-dark small"><i class="fas fa-bookmark text-primary me-1.5"></i>বিষয়:</span>
                        <span class="text-dark small fw-semibold">{{ $bill->subject }}</span>
                    </div>
                @endif

                {{-- Customer & Billing Meta Grid --}}
                <div class="row g-3 mb-4">
                    {{-- Bill To / Client --}}
                    <div class="col-12 col-sm-6">
                        <div class="p-3 bg-light rounded-3 border h-100">
                            <div class="small text-muted text-uppercase fw-bold mb-2 pb-1 border-bottom">
                                <i class="fas fa-user-check text-primary me-1"></i>প্রাপক / বিল গ্রাহকের বিবরণ:
                            </div>
                            <h6 class="fw-bold text-dark mb-1">{{ $bill->customer_name }}</h6>
                            @if($bill->customer_org)
                                <p class="text-dark small mb-1 fw-semibold"><i class="fas fa-building text-muted me-1"></i>{{ $bill->customer_org }}</p>
                            @endif
                            @if($bill->customer_designation)
                                <p class="text-muted small mb-1"><i class="fas fa-id-badge text-muted me-1"></i>{{ $bill->customer_designation }}</p>
                            @endif
                            @if($bill->customer_phone)
                                <p class="text-muted small mb-1"><i class="fas fa-phone text-muted me-1"></i>{{ $bill->customer_phone }}</p>
                            @endif
                            @if($bill->customer_email)
                                <p class="text-muted small mb-1"><i class="fas fa-envelope text-muted me-1"></i>{{ $bill->customer_email }}</p>
                            @endif
                            @if($bill->customer_address)
                                <p class="text-muted small mb-0"><i class="fas fa-map-marker-alt text-muted me-1"></i>{{ $bill->customer_address }}</p>
                            @endif
                        </div>
                    </div>

                    {{-- Billing Authority & Payment Status --}}
                    <div class="col-12 col-sm-6">
                        <div class="p-3 bg-light rounded-3 border h-100 text-sm-end">
                            <div class="small text-muted text-uppercase fw-bold mb-2 pb-1 border-bottom">
                                <i class="fas fa-shield-check text-success me-1"></i>প্রস্তুতকারক ও স্ট্যাটাস:
                            </div>
                            <p class="text-dark small mb-1">
                                সেলার / প্রস্তুতকারক: <strong>{{ $bill->seller->name ?? 'আইডিয়া সেলস পয়েন্ট' }}</strong>
                            </p>
                            <p class="text-muted small mb-2">
                                পেমেন্ট মেথড: <strong>{{ ['cash'=>'নগদ (Cash)','bkash'=>'বিকাশ (bKash)','nagad'=>'নগদ (Nagad)','card'=>'ব্যাংক / কার্ড'][$bill->payment_method] ?? $bill->payment_method }}</strong>
                            </p>
                            <div>
                                @if($bill->payment_status === 'paid')
                                    <span class="badge bg-success text-white px-3 py-1.5 fs-7 rounded-pill">
                                        <i class="fas fa-check-circle me-1"></i>সম্পূর্ণ পরিশোধিত (Paid)
                                    </span>
                                @elseif($bill->payment_status === 'partial')
                                    <span class="badge bg-warning text-dark px-3 py-1.5 fs-7 rounded-pill">
                                        <i class="fas fa-clock me-1"></i>আংশিক পরিশোধ (Partial)
                                    </span>
                                @else
                                    <span class="badge bg-danger text-white px-3 py-1.5 fs-7 rounded-pill">
                                        <i class="fas fa-triangle-exclamation me-1"></i>বকেয়া (Unpaid)
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ══ Itemized Table ══ --}}
                <div class="table-responsive mb-4">
                    <table class="table table-bordered align-middle mb-0">
                        <thead class="table-light text-center small fw-bold text-dark">
                            <tr>
                                <th style="width: 45px;">ক্রম</th>
                                <th class="text-start">বই ও পণ্যের বিবরণ</th>
                                <th class="text-start" style="width: 160px;">লেখক</th>
                                <th style="width: 90px;">পরিমাণ</th>
                                @if(!$isChallan)
                                    <th class="text-end" style="width: 120px;">একক মূল্য</th>
                                    <th style="width: 90px;">ছাড় (%)</th>
                                    <th class="text-end" style="width: 130px;">মোট (৳)</th>
                                @else
                                    <th style="width: 150px;">মন্তব্য / অবস্থা</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bill->items ?? [] as $i => $item)
                            @php
                                $qty = (int)($item['qty'] ?? 1);
                                $price = (float)($item['price'] ?? 0);
                                $discPct = (float)($item['discount_pct'] ?? 0);
                                $lineRaw = $qty * $price;
                                $lineDisc = $lineRaw * ($discPct / 100);
                                $lineTotal = (float)($item['line_total'] ?? ($lineRaw - $lineDisc));
                            @endphp
                            <tr>
                                <td class="text-center text-muted fw-bold">@bn($i + 1)</td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $item['title'] ?? 'বই' }}</div>
                                </td>
                                <td class="small text-muted">{{ $item['author'] ?? '—' }}</td>
                                <td class="text-center fw-bold text-dark">@bn($qty)</td>
                                @if(!$isChallan)
                                    <td class="text-end font-monospace">৳{{ number_format($price, 2) }}</td>
                                    <td class="text-center">
                                        @if($discPct > 0)
                                            <span class="badge bg-success-subtle text-success border">@bn($discPct)%</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-end fw-bold text-dark font-monospace">৳{{ number_format($lineTotal, 2) }}</td>
                                @else
                                    <td class="text-center small text-success fw-semibold"><i class="fas fa-check-circle me-1"></i>উত্তম কপি</td>
                                @endif
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            @if(!$isChallan)
                                <tr>
                                    <td colspan="3" class="text-end fw-semibold">বইগুলোর সর্বমোট পরিমাণ ও গায়ের মূল্য:</td>
                                    <td class="text-center fw-bold">@bn($totalQty) টি</td>
                                    <td colspan="2" class="text-end fw-semibold">উপমোট (Subtotal):</td>
                                    <td class="text-end fw-bold font-monospace">৳{{ number_format($bill->subtotal, 2) }}</td>
                                </tr>
                                @if($bill->discount > 0)
                                <tr>
                                    <td colspan="6" class="text-end text-danger fw-semibold">সর্বমোট ছাড় (Discount):</td>
                                    <td class="text-end text-danger fw-bold font-monospace">- ৳{{ number_format($bill->discount, 2) }}</td>
                                </tr>
                                @endif
                                <tr class="table-active">
                                    <td colspan="6" class="text-end fw-bold fs-6 text-dark">সর্বমোট প্রদেয় বিল (Grand Total):</td>
                                    <td class="text-end fw-bold fs-5 text-primary font-monospace">৳{{ number_format($bill->total, 2) }}</td>
                                </tr>
                                <tr>
                                    <td colspan="6" class="text-end text-success fw-semibold">পরিশোধিত / নগদ জমা (Paid):</td>
                                    <td class="text-end text-success fw-bold font-monospace">৳{{ number_format($bill->paid_amount ?? $bill->total, 2) }}</td>
                                </tr>
                                @if($bill->due_amount > 0)
                                <tr class="bg-danger-subtle bg-opacity-25">
                                    <td colspan="6" class="text-end text-danger fw-bold fs-6">অবশিষ্ট বকেয়া (Due Amount):</td>
                                    <td class="text-end text-danger fw-bold fs-6 font-monospace">৳{{ number_format($bill->due_amount, 2) }}</td>
                                </tr>
                                @endif
                            @else
                                <tr>
                                    <td colspan="3" class="text-end fw-bold">ডেলিভারিকৃত মোট বইয়ের সংখ্যা:</td>
                                    <td class="text-center fw-bold fs-6 text-primary">@bn($totalQty) টি</td>
                                    <td></td>
                                </tr>
                            @endif
                        </tfoot>
                    </table>
                </div>

                {{-- Amount in words (For Invoices/Quotations) --}}
                @if(!$isChallan)
                    <div class="p-3 bg-light rounded-3 border mb-4">
                        <div class="small text-muted fw-semibold">কথায় (টাকা):</div>
                        <div class="fw-bold text-dark fs-6 text-capitalize">
                            @takaInWords($bill->total) মাত্র (@takaInWordsEn($bill->total) Only)
                        </div>
                    </div>
                @endif

                {{-- Notes & Terms --}}
                @if($bill->notes || $bill->terms_conditions)
                    <div class="row g-3 mb-4">
                        @if($bill->notes)
                            <div class="col-12 col-md-6">
                                <div class="p-3 bg-light rounded-3 border h-100">
                                    <div class="small fw-bold text-dark mb-1"><i class="fas fa-note-sticky text-primary me-1"></i>মন্তব্য / রেফারেন্স:</div>
                                    <p class="small text-muted mb-0">{{ $bill->notes }}</p>
                                </div>
                            </div>
                        @endif
                        @if($bill->terms_conditions)
                            <div class="col-12 col-md-6">
                                <div class="p-3 bg-light rounded-3 border h-100">
                                    <div class="small fw-bold text-dark mb-1"><i class="fas fa-circle-info text-primary me-1"></i>শর্তাবলী:</div>
                                    <p class="small text-muted mb-0">{{ $bill->terms_conditions }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- Official Signature Blocks --}}
                <div class="row g-4 pt-5 mt-4 border-top">
                    <div class="col-4 text-center">
                        <div class="border-top border-dark pt-2 mx-auto" style="width: 140px;">
                            <p class="small fw-bold text-dark mb-0">{{ $bill->seller->name ?? 'সেলার' }}</p>
                            <p class="text-muted" style="font-size: 11px;">প্রস্তুতকারকের স্বাক্ষর</p>
                        </div>
                    </div>
                    <div class="col-4 text-center">
                        <div class="border-top border-dark pt-2 mx-auto" style="width: 140px;">
                            <p class="small fw-bold text-dark mb-0">{{ $bill->customer_name }}</p>
                            <p class="text-muted" style="font-size: 11px;">গ্রহীতার স্বাক্ষর</p>
                        </div>
                    </div>
                    <div class="col-4 text-center">
                        <div class="border-top border-dark pt-2 mx-auto" style="width: 140px;">
                            <p class="small fw-bold text-dark mb-0">{{ $settings['default_creator_name'] ?? 'কর্তৃপক্ষ' }}</p>
                            <p class="text-muted" style="font-size: 11px;">অনুমোদিত স্বাক্ষর</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>

<style>
@media print {
    body {
        background-color: #fff !important;
    }
    .d-print-none {
        display: none !important;
    }
    .memo-printable-card {
        box-shadow: none !important;
        border: none !important;
        padding: 0 !important;
        margin: 0 !important;
    }
}
</style>
@endsection
