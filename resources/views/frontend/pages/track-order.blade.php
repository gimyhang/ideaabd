@extends('layouts.app')

@section('title', 'অর্ডার ট্র্যাকিং — আইডিয়া প্রকাশন')
@section('meta_description', 'অর্ডার নম্বর ও মোবাইল নম্বর দিয়ে আপনার অর্ডারের বর্তমান অবস্থা দেখুন।')

@section('content')
@php
    $steps = ['pending' => 'অর্ডার গৃহীত', 'confirmed' => 'নিশ্চিত', 'processing' => 'প্রস্তুত হচ্ছে', 'shipped' => 'পাঠানো হয়েছে', 'delivered' => 'পৌঁছেছে'];
    $keys = array_keys($steps);
    $current = isset($order) ? array_search($order->status, $keys, true) : false;
@endphp
<div class="container py-4 py-md-5" style="max-width: 720px;">
    <h1 class="h4 fw-bold mb-1">অর্ডার ট্র্যাকিং</h1>
    <p class="text-muted small mb-4">অর্ডারের সময় পাওয়া এসএমএসে অর্ডার নম্বর আছে। ওই নম্বর ও অর্ডারে দেওয়া মোবাইল নম্বর লিখুন।</p>

    @if($errors->any())
        <div class="alert alert-danger small py-2 px-3" role="alert">@foreach($errors->all() as $m)<div>{{ $m }}</div>@endforeach</div>
    @endif

    <form method="POST" action="{{ route('order.track.lookup') }}" class="card border-0 shadow-sm rounded-4 p-3 p-md-4 mb-4">
        @csrf
        <label class="form-label small fw-bold mb-1" for="trackOrderNo">অর্ডার নম্বর</label>
        <input id="trackOrderNo" type="text" name="order_number" class="form-control form-control-lg rounded-3 mb-3 font-monospace" placeholder="যেমন IDP-2026-0001" value="{{ old('order_number', $query['order_number'] ?? '') }}" autocomplete="off" required>
        <label class="form-label small fw-bold mb-1" for="trackPhone">মোবাইল নম্বর</label>
        <input id="trackPhone" type="tel" name="phone" class="form-control form-control-lg rounded-3 mb-3 font-monospace" inputmode="numeric" autocomplete="tel" placeholder="01XXXXXXXXX" value="{{ old('phone', $query['phone'] ?? '') }}" required>
        <button class="btn btn-primary btn-lg rounded-pill fw-bold">অর্ডার দেখুন</button>
    </form>

    @isset($order)
        <div class="card border-0 shadow-sm rounded-4 p-3 p-md-4">
            <div class="d-flex justify-content-between flex-wrap gap-2 mb-3">
                <div>
                    <div class="small text-muted">অর্ডার নম্বর</div>
                    <div class="fw-bold font-monospace">{{ $order->order_number }}</div>
                </div>
                <div class="text-md-end">
                    <div class="small text-muted">মোট বিল</div>
                    <div class="fw-bold text-primary">৳ @bn(round($order->total_amount))</div>
                </div>
            </div>

            @if($order->status === 'cancelled')
                <div class="alert alert-secondary mb-3">এই অর্ডারটি বাতিল করা হয়েছে।</div>
            @else
                <ol class="list-unstyled d-grid gap-2 mb-3">
                    @foreach($steps as $key => $label)
                        @php $idx = array_search($key, $keys, true); $done = $current !== false && $idx <= $current; @endphp
                        <li class="d-flex align-items-center gap-2 {{ $done ? 'fw-semibold text-success' : 'text-muted' }}">
                            <span class="rounded-circle d-inline-block" style="width:12px;height:12px;background:{{ $done ? '#16a34a' : '#cbd5e1' }}"></span>{{ $label }}
                        </li>
                    @endforeach
                </ol>
            @endif

            @if($order->tracking_code)
                <div class="small mb-1">কুরিয়ার: <strong>{{ $order->courier_name ?: '—' }}</strong> · ট্র্যাকিং কোড: <strong class="font-monospace">{{ $order->tracking_code }}</strong></div>
            @endif
            <div class="small text-muted">পেমেন্ট: {{ $order->payment_status === 'paid' ? 'পরিশোধিত' : 'বাকি' }} · {{ $order->created_at->format('d/m/Y h:i A') }}</div>

            @if($needsVerification ?? false)
                <div class="alert alert-warning small mt-3 mb-0" role="alert">
                    আপনার অ্যাকাউন্টের মোবাইল/ইমেইল এখনও যাচাই হয়নি। অর্ডার বাতিল বা পরিবর্তন করতে হলে আগে <a href="{{ route('my-account') }}" class="fw-bold">যাচাই করুন</a>।
                </div>
            @endif
        </div>
    @endisset
</div>
@endsection
