@extends('layouts.admin')

@push('styles')
@include('inventory::admin._nav')
@endpush

@use('App\Services\BookProductionService', 'BPS')

@section('title', $book->title . ' · খাতা')
@section('heading', 'বইয়ের খাতা')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.inventory.index') }}">Stock</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ \Illuminate\Support\Str::limit($book->title, 40) }}</li>
@endsection

@section('actions')
    <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('admin.inventory.index') }}" class="btn btn-light btn-sm"><i class="fa-solid fa-arrow-left me-1"></i>ফিরে যান</a>
    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#lifeModal"
            data-life-mode="{{ ($life->at_binder ?? 0) ? 'received' : (($life->at_press ?? 0) ? 'bound' : 'printed') }}"><i class="fa-solid fa-plus me-1"></i>এন্ট্রি</button>
@endsection

@section('content')
@php
    $l = $life ?? (object) ['printed' => 0, 'bound' => 0, 'received' => 0, 'other_in' => 0, 'store_in' => 0, 'press_waste' => 0, 'binder_waste' => 0, 'store_waste' => 0, 'waste' => 0, 'at_press' => 0, 'at_binder' => 0, 'in_store' => max(0, (int) $book->stock_quantity), 'sold' => 0, 'total' => 0, 'sell_through' => 0, 'press_since' => null, 'binder_since' => null];
    $inStore = (int) $book->stock_quantity;
    $total = max(1, $l->sold + max(0, $inStore) + $l->at_binder + $l->at_press + $l->waste);
    $pct = fn ($v) => round(max(0, $v) / $total * 100, 2);
    $tone = ['printed' => 'blue', 'bound' => 'violet', 'received' => 'green', 'press_waste' => 'red', 'binder_waste' => 'red', 'store_waste' => 'red',
             'sale_out' => 'blue', 'transfer_in' => 'gray', 'transfer_out' => 'gray', 'opening_balance' => 'gray', 'purchase_in' => 'green'];
@endphp

<section class="card lc-card lc-hero">
    <div class="lc-hero-head">
        <div>
            <h2 class="lc-hero-title">{{ $book->title }}</h2>
            <div class="lc-meta">
                @if ($book->author_name)<span>{{ $book->author_name }}</span>@endif
                @if ($book->category)<span>{{ $book->category->name }}</span>@endif
                @if ($book->isbn)<span>ISBN {{ $book->isbn }}</span>@endif
            </div>
        </div>
        <div class="lc-hero-rate">
            <strong>{{ BPS::bn($l->sell_through, $l->sell_through == floor($l->sell_through) ? 0 : 1) }}%</strong>
            <span>বিক্রি হয়েছে</span>
        </div>
    </div>

    <div class="lc-bar is-lg" role="img" aria-label="বিক্রি {{ $l->sold }}, স্টোরে {{ $inStore }}, বাঁধাইখানায় {{ $l->at_binder }}, প্রেসে {{ $l->at_press }}, নষ্ট {{ $l->waste }}">
        <i class="is-sold" style="width:{{ $pct($l->sold) }}%"></i><i class="is-store" style="width:{{ $pct($inStore) }}%"></i><i class="is-binder" style="width:{{ $pct($l->at_binder) }}%"></i><i class="is-press" style="width:{{ $pct($l->at_press) }}%"></i><i class="is-waste" style="width:{{ $pct($l->waste) }}%"></i>
    </div>

    <dl class="lc-stats">
        <div><dt>ছাপা</dt><dd>{{ BPS::bn($l->printed) }}</dd>@if ($l->other_in > 0)<small>+ {{ BPS::bn($l->other_in) }} অন্যভাবে স্টকে</small>@endif</div>
        <div class="is-amber"><dt><i class="is-press"></i>প্রেসে বাকি</dt><dd>{{ BPS::bn($l->at_press) }}</dd>@if ($l->press_since)<small class="lc-age {{ BPS::ageTone($l->press_since) }}">{{ BPS::age($l->press_since) }} ধরে</small>@endif</div>
        <div class="is-violet"><dt><i class="is-binder"></i>বাঁধাইখানায়</dt><dd>{{ BPS::bn($l->at_binder) }}</dd>@if ($l->binder_since)<small class="lc-age {{ BPS::ageTone($l->binder_since) }}">{{ BPS::age($l->binder_since) }} ধরে</small>@endif</div>
        <div class="is-green"><dt><i class="is-store"></i>স্টোরে</dt><dd>{{ BPS::bn($inStore) }}</dd><small>মোট ঢুকেছে {{ BPS::bn($l->store_in) }}</small></div>
        <div class="is-blue"><dt><i class="is-sold"></i>বিক্রি / বিতরণ</dt><dd>{{ BPS::bn($l->sold) }}</dd></div>
        <div class="is-red"><dt><i class="is-waste"></i>নষ্ট</dt><dd>{{ BPS::bn($l->waste) }}</dd><small>প্রেস {{ BPS::bn($l->press_waste) }} · বাঁধাই {{ BPS::bn($l->binder_waste) }} · স্টোর {{ BPS::bn($l->store_waste) }}</small></div>
    </dl>
</section>

<div class="row g-3">
    <div class="col-xl-6">
        <section class="card lc-card h-100">
            <header class="lc-card-head"><h3><i class="fa-solid fa-print"></i> ছাপার চালান</h3><span class="lc-dim">প্রথমে ছাপা কপি আগে বাঁধাই ধরা হয়</span></header>
            <div class="table-responsive">
                <table class="table align-middle lc-table">
                    <thead><tr><th>তারিখ</th><th>প্রেস / চালান</th><th class="text-end">ছাপা</th><th class="text-end">বাঁধাই/নষ্ট</th><th class="text-end">বাকি</th></tr></thead>
                    <tbody>
                        @forelse ($runs['printed']->reverse() as $r)
                            <tr>
                                <td class="text-nowrap">{{ $r->event->event_date->format('d M Y') }}</td>
                                <td>{{ $r->event->vendor ?: '—' }}@if ($r->event->batch_no)<div class="lc-dim">{{ $r->event->batch_no }}</div>@endif</td>
                                <td class="text-end lc-num">{{ BPS::bn($r->event->quantity) }}</td>
                                <td class="text-end lc-num">{{ BPS::bn($r->done) }}</td>
                                <td class="text-end lc-num">
                                    @if ($r->open)<span class="lc-chip is-amber">{{ BPS::bn($r->open) }}</span><div class="lc-age {{ BPS::ageTone($r->event->event_date) }}">{{ BPS::age($r->event->event_date) }}</div>
                                    @else<span class="lc-done"><i class="fa-solid fa-check"></i> শেষ</span>@endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="lc-empty">কোনো ছাপার এন্ট্রি নেই</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
    <div class="col-xl-6">
        <section class="card lc-card h-100">
            <header class="lc-card-head"><h3><i class="fa-solid fa-book"></i> বাঁধাইয়ের লট</h3><span class="lc-dim">বাঁধাইখানা থেকে স্টোরে আসা পর্যন্ত</span></header>
            <div class="table-responsive">
                <table class="table align-middle lc-table">
                    <thead><tr><th>তারিখ</th><th>বাঁধাইখানা</th><th class="text-end">বাঁধাই</th><th class="text-end">স্টোরে/নষ্ট</th><th class="text-end">পড়ে আছে</th></tr></thead>
                    <tbody>
                        @forelse ($runs['bound']->reverse() as $r)
                            <tr>
                                <td class="text-nowrap">{{ $r->event->event_date->format('d M Y') }}</td>
                                <td>{{ $r->event->vendor ?: '—' }}@if ($r->event->batch_no)<div class="lc-dim">{{ $r->event->batch_no }}</div>@endif</td>
                                <td class="text-end lc-num">{{ BPS::bn($r->event->quantity) }}</td>
                                <td class="text-end lc-num">{{ BPS::bn($r->done) }}</td>
                                <td class="text-end lc-num">
                                    @if ($r->open)<span class="lc-chip is-violet">{{ BPS::bn($r->open) }}</span><div class="lc-age {{ BPS::ageTone($r->event->event_date) }}">{{ BPS::age($r->event->event_date) }}</div>
                                    @else<span class="lc-done"><i class="fa-solid fa-check"></i> শেষ</span>@endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="lc-empty">কোনো বাঁধাইয়ের এন্ট্রি নেই</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>

<section class="card lc-card">
    <header class="lc-card-head"><h3><i class="fa-solid fa-location-dot"></i> কোথায় কত কপি</h3></header>
    <div class="lc-locgrid">
        @forelse ($book->inventories->sortByDesc('quantity') as $inv)
            <div class="lc-loc {{ $inv->quantity <= $inv->min_alert_quantity ? 'is-low' : '' }}">
                <span>{{ $inv->location?->name ?? '—' }}</span>
                <strong>{{ BPS::bn($inv->quantity) }}</strong>
                <small>{{ $inv->location?->type === 'showroom' ? 'শোরুম' : 'গুদাম' }} · সতর্কসীমা {{ BPS::bn($inv->min_alert_quantity) }}</small>
            </div>
        @empty
            <div class="lc-dim p-3">কোনো লোকেশনে স্টক নেই</div>
        @endforelse
        @if (($free = $inStore - (int) $book->inventories->sum('quantity')) !== 0)
            <div class="lc-loc is-warn"><span>লোকেশন ছাড়া</span><strong>{{ BPS::bn($free) }}</strong><small>কোনো গুদাম/শোরুমে বসানো হয়নি</small></div>
        @endif
    </div>
</section>

<section class="card lc-card">
    <header class="lc-card-head"><h3><i class="fa-solid fa-clock-rotate-left"></i> পুরো ইতিহাস</h3><span class="lc-dim">{{ BPS::bn($timeline->count()) }} এন্ট্রি</span></header>
    <div class="table-responsive">
        <table class="table align-middle lc-table">
            <thead><tr><th>তারিখ</th><th>ধাপ</th><th class="text-end">কপি</th><th>কোথায়</th><th>চালান / কারণ</th><th>কে</th><th></th></tr></thead>
            <tbody>
                @forelse ($timeline as $t)
                    <tr>
                        <td class="text-nowrap">{{ $t->at?->format('d M Y') }}</td>
                        <td><span class="lc-chip is-{{ $tone[$t->kind] ?? 'gray' }}">{{ $t->label }}</span>@if ($t->note)<div class="lc-dim">{{ $t->note }}</div>@endif</td>
                        <td class="text-end lc-num {{ $t->qty < 0 || str_ends_with($t->kind, '_waste') ? 'text-danger' : '' }}">{{ $t->qty > 0 && !in_array($t->kind, ['printed', 'bound', 'received'], true) && !str_ends_with($t->kind, '_waste') ? '+' : '' }}{{ BPS::bn($t->qty) }}</td>
                        <td>{{ $t->place ?: '—' }}</td>
                        <td class="lc-dim">{{ $t->ref ?: '—' }}</td>
                        <td class="lc-dim">{{ $t->by ?: '—' }}</td>
                        <td class="text-end">
                            @if ($t->id)
                                <form method="POST" action="{{ route('admin.production.destroy', $t->id) }}" onsubmit="return confirm('এই এন্ট্রি মুছে ফেলবেন?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-light text-danger" title="মুছুন" aria-label="মুছুন"><i class="fa-regular fa-trash-can"></i></button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="lc-empty">এখনো কোনো এন্ট্রি নেই</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

@include('inventory::admin._entry', ['presetBook' => $book, 'presetLife' => $l])
@endsection
