@extends('layouts.admin')

@php
    $bizName = $settings['business_name'] ?? 'আইডিয়া প্রকাশন';
    $bizTagline = $settings['tagline'] ?? 'Book Publication, Printing, Binding & Distribution';
    $bizAddress = $settings['address'] ?? '38 Banglabazar, Dhaka-1100, Bangladesh';
    $bizPhone = $settings['phone'] ?? '+8801700000000';
    $bizEmail = $settings['email'] ?? 'ideaprokashon@gmail.com';
    $bizLogo = $settings['logo'] ?? '/images/logo.png';
    $logoSrc = \App\Support\SiteSetting::resolveImageUrl($bizLogo, 'images/logo.png') ?: asset('images/logo.png');

    $purchase = $payment->purchase;
    $amount = (float) $payment->amount;
    $party = $partyName ?: ($payment->vendor_name ?: 'সরবরাহকারী / ভেন্ডর');
    
    // Number to Bengali words helper
    function getAmountInBengaliWords($number) {
        $decimal = round($number - ($no = floor($number)), 2) * 100;
        $hundred = null;
        $digits_length = strlen($no);
        $i = 0;
        $str = [];
        $words = [
            0 => '', 1 => 'এক', 2 => 'দুই', 3 => 'তিন', 4 => 'চার', 5 => 'পাঁচ',
            6 => 'ছয়', 7 => 'সাত', 8 => 'আট', 9 => 'নয়', 10 => 'দশ',
            11 => 'এগারো', 12 => 'বারো', 13 => 'তেরো', 14 => 'চৌদ্দ', 15 => 'পনেরো',
            16 => 'ষোল', 17 => 'সতেরো', 18 => 'আঠারো', 19 => 'উনিশ', 20 => 'বিশ',
            30 => 'ত্রিশ', 40 => 'চল্লিশ', 50 => 'পঞ্চাশ', 60 => 'ষাট', 70 => 'সত্তুর',
            80 => 'আশি', 90 => 'নব্বই'
        ];
        $digits = ['', 'শত', 'হাজার', 'লাখ', 'কোটি'];
        while ($i < $digits_length) {
            $divider = ($i == 2) ? 10 : 100;
            $number = floor($no % $divider);
            $no = floor($no / $divider);
            $i += $divider == 10 ? 1 : 2;
            if ($number) {
                $plural = (($counter = count($str)) && $number > 9) ? '' : '';
                $hundred = ($counter == 1 && $str[0]) ? ' ' : '';
                $unit = '';
                if ($number < 21) {
                    $unit = $words[$number];
                } elseif (isset($words[$number])) {
                    $unit = $words[$number];
                } else {
                    $unit = $words[floor($number / 10) * 10] . ' ' . $words[$number % 10];
                }
                $str[] = $unit . ' ' . ($digits[$counter] ?? '') . $plural . ' ' . $hundred;
            } else {
                $str[] = null;
            }
        }
        $taka = implode('', array_reverse($str));
        $poisa = '';
        if ($decimal > 0) {
            $poisa = ' এবং ' . (isset($words[$decimal]) ? $words[$decimal] : ($words[floor($decimal / 10) * 10] . ' ' . $words[$decimal % 10])) . ' পয়সা';
        }
        return trim($taka) ? trim($taka) . ' টাকা মাত্র' . $poisa : 'শূন্য টাকা মাত্র';
    }

    $amountWords = getAmountInBengaliWords($amount);
@endphp

@section('title', "পেমেন্ট ভাউচার — {$payment->payment_no}")
@section('heading', "পেমেন্ট ভাউচার ও পরিশোধ রসিদ")
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.purchases.index') }}">Purchases</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.purchases.ledger') }}">Vendor Ledger</a></li>
    <li class="breadcrumb-item active" aria-current="page">Voucher #{{ $payment->payment_no }}</li>
@endsection

@section('actions')
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-primary btn-sm rounded-pill px-4 shadow-sm fw-semibold" onclick="window.print()">
            <i class="fas fa-print me-1.5"></i> প্রিন্ট / PDF ভাউচার
        </button>
        <a href="{{ route('admin.purchases.ledger', ['party' => $payment->publisher_id ? 'pub_' . $payment->publisher_id : 'vendor_' . $payment->vendor_name]) }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            <i class="fas fa-arrow-left me-1"></i> ভেন্ডর লেজার
        </a>
    </div>
@endsection

@section('content')
<style>
    .voucher-card {
        background: #ffffff;
        border-radius: 14px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 20px rgba(0,0,0,0.04);
        max-width: 840px;
        margin: 0 auto;
    }
    .voucher-watermark {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%) rotate(-25deg);
        font-size: 80px;
        font-weight: 900;
        color: rgba(34, 197, 94, 0.05);
        text-transform: uppercase;
        pointer-events: none;
        user-select: none;
        z-index: 0;
    }
    @media print {
        body {
            background: #ffffff !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        .main-header, .sidebar, .breadcrumb, .btn, footer, nav, .alert, .actions, header {
            display: none !important;
        }
        .content-wrapper, .container-fluid, .content {
            padding: 0 !important;
            margin: 0 !important;
            background: #ffffff !important;
        }
        .voucher-card {
            border: 1px solid #000 !important;
            box-shadow: none !important;
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
        }
    }
</style>

<div class="container-fluid py-3">
    <div class="voucher-card p-4 p-md-5 position-relative">
        <div class="voucher-watermark">PAID &bull; ডিসবার্সড</div>

        {{-- Memo Header --}}
        <div class="row align-items-center pb-4 mb-4 border-bottom position-relative" style="z-index: 1;">
            <div class="col-8">
                <div class="d-flex align-items-center gap-3">
                    @if(!empty($logoSrc))
                        <div class="logo-box d-flex align-items-center justify-content-center bg-white rounded p-1 border shadow-xs" style="width: 140px; height: 56px; max-width: 140px; flex-shrink: 0; overflow: hidden;">
                            <img src="{{ $logoSrc }}" alt="Logo" style="max-height: 100%; max-width: 100%; width: auto; height: auto; object-fit: contain; display: block;">
                        </div>
                    @endif
                    <div>
                        <h4 class="fw-bold mb-0 text-dark">{{ $bizName }}</h4>
                        <div class="text-muted small">{{ $bizTagline }}</div>
                        <div class="text-secondary small" style="font-size: 12px;">
                            {{ $bizAddress }} | ফোন: {{ $bizPhone }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-4 text-end">
                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill fw-bold fs-6">
                    <i class="fas fa-money-bill-transfer me-1"></i> ডেবিট / পেমেন্ট ভাউচার
                </span>
                <div class="text-muted small mt-2">
                    ভাউচার নং: <strong class="font-monospace text-dark">#{{ $payment->payment_no }}</strong>
                </div>
                <div class="text-muted small">
                    তারিখ: <strong class="text-dark">{{ $payment->payment_date ? $payment->payment_date->format('d M, Y') : date('d M, Y') }}</strong>
                </div>
            </div>
        </div>

        {{-- Party & Purchase Reference Info --}}
        <div class="row g-3 mb-4 position-relative" style="z-index: 1;">
            <div class="col-md-7">
                <div class="bg-light p-3.5 rounded-3 border">
                    <div class="text-muted small fw-bold text-uppercase mb-1" style="font-size: 11px;">
                        <i class="fas fa-user-tag text-primary me-1"></i>প্রাপক / সরবরাহকারী (Paid To / Supplier):
                    </div>
                    <h5 class="fw-bold text-dark mb-1">{{ $party }}</h5>
                    @if($partyPhone !== '—')
                        <div class="text-muted small"><i class="fas fa-phone me-1 text-success"></i>{{ $partyPhone }}</div>
                    @endif
                    @if($partyAddress !== '—')
                        <div class="text-muted small"><i class="fas fa-location-dot me-1 text-danger"></i>{{ $partyAddress }}</div>
                    @endif
                </div>
            </div>
            <div class="col-md-5">
                <div class="bg-light p-3.5 rounded-3 border">
                    <div class="text-muted small fw-bold text-uppercase mb-1" style="font-size: 11px;">
                        <i class="fas fa-file-invoice text-info me-1"></i>সংশ্লিষ্ট বিল ও বিবরণ:
                    </div>
                    @if($purchase)
                        <div class="fw-bold text-dark font-monospace">ক্রয় চালান/বিল #{{ $purchase->purchase_no }}</div>
                        <div class="small text-muted">মোট বিল: ৳{{ number_format($purchase->grand_total, 2) }} | বর্তমান বকেয়া: ৳{{ number_format($purchase->due_amount, 2) }}</div>
                    @else
                        <div class="fw-semibold text-dark">চলতি খাতা / একাউন্ট জমা</div>
                        <div class="small text-muted">সাপ্লায়ার চলতি লেজার থেকে সমন্বয়কৃত</div>
                    @endif
                    <div class="small text-muted mt-1">মাধ্যম: <strong>{{ ucfirst($payment->payment_method) }}</strong> @if($payment->transaction_ref) (Trx: {{ $payment->transaction_ref }}) @endif</div>
                </div>
            </div>
        </div>

        {{-- Payment Details Table --}}
        <div class="table-responsive mb-4 position-relative" style="z-index: 1;">
            <table class="table table-bordered align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;" class="text-center">#</th>
                        <th>পরিশোধের বিবরণ ও খাত (Particulars)</th>
                        <th>পেমেন্ট মেথড ও রেফারেন্স</th>
                        <th class="text-end" style="width: 170px;">পরিশোধিত অর্থ (টাকা)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="text-center fw-bold">১</td>
                        <td>
                            <div class="fw-bold text-dark">
                                @if($purchase)
                                    ক্রয় বিল #{{ $purchase->purchase_no }} এর কিস্তি / বকেয়া বিল পরিশোধ
                                @else
                                    সাপ্লায়ার/ভেন্ডরের বকেয়া খতিয়ানের বিপরীতে কিস্তি পরিশোধ
                                @endif
                            </div>
                            @if($payment->note)
                                <div class="text-muted small mt-1">নোট: {{ $payment->note }}</div>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-secondary-subtle text-dark border px-2.5 py-1">
                                {{ ucfirst($payment->payment_method) }}
                            </span>
                            @if($payment->transaction_ref)
                                <div class="small font-monospace text-muted mt-1">Ref: {{ $payment->transaction_ref }}</div>
                            @endif
                        </td>
                        <td class="text-end font-monospace fw-bold fs-5 text-success">
                            ৳{{ number_format($amount, 2) }}
                        </td>
                    </tr>
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <td colspan="3" class="text-end fw-bold">সর্বমোট পরিশোধ (Total Paid):</td>
                        <td class="text-end font-monospace fw-bold fs-5 text-success">৳{{ number_format($amount, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- Amount in words --}}
        <div class="p-3 bg-light rounded-3 border mb-5 position-relative" style="z-index: 1;">
            <span class="text-muted small fw-bold text-uppercase">কথায় (In Words):</span>
            <span class="fw-bold text-dark ms-2">{{ $amountWords }}</span>
        </div>

        {{-- Signatures --}}
        <div class="pt-5 mt-4 border-top position-relative" style="z-index: 1;">
            <div class="row text-center align-items-end">
                <div class="col-4">
                    <div class="border-top border-dark pt-1 mx-auto" style="width: 160px;">
                        <div class="small fw-bold text-dark">{{ $payment->recorder?->name ?? 'অ্যাকাউন্টস অফিসার' }}</div>
                        <div class="text-muted" style="font-size: 11px;">প্রস্তুতকারী (Prepared By)</div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="border-top border-dark pt-1 mx-auto" style="width: 160px;">
                        <div class="small fw-semibold text-dark">চেককারী / হিসাবরক্ষক</div>
                        <div class="text-muted" style="font-size: 11px;">Checked By</div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="border-top border-dark pt-1 mx-auto" style="width: 160px;">
                        <div class="small fw-bold text-dark">কর্তৃপক্ষের স্বাক্ষর</div>
                        <div class="text-muted" style="font-size: 11px;">Authorized Signature</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
