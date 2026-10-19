@extends('layouts.admin')

@section('title', 'Products')
@section('heading', 'Products')
@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Products</li>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin-products.css') }}?v={{ @filemtime(public_path('css/admin-products.css')) ?: 1 }}">
@endpush

@section('content')
@php
    $tone = ['electronics' => 'info', 'stationery' => 'success', 'appliance' => 'warning'];
    $blurb = ['electronics' => 'Gadgets & devices', 'stationery' => 'Office & school', 'appliance' => 'Home & kitchen'];
@endphp
<div class="d-flex flex-column gap-3">

    <div class="hub-grid">
        @foreach($stats as $t => $st)
            <div class="hub-card hub-card--{{ $tone[$t] ?? 'info' }}">
                <div>
                    <div class="hub-card__name">{{ $st['label'] }}</div>
                    <div class="text-muted small fw-semibold">{{ $blurb[$t] ?? '' }}</div>
                </div>
                <div class="hub-card__num">{{ number_format($st['total']) }}</div>
                <div class="hub-card__stats">
                    <span><b>{{ $st['active'] }}</b> active</span>
                    <span><b>{{ $st['low'] }}</b> low</span>
                    <span><b>{{ $st['categories'] }}</b> categories</span>
                </div>
                <div class="d-flex gap-2 hub-card__go">
                    <a href="{{ route('admin.products.' . $t) }}" class="btn btn-primary pr-btn flex-fill">Open</a>
                    <a href="{{ route('admin.products.create', ['type' => $t]) }}" class="btn btn-outline-primary pr-btn flex-fill">Add</a>
                </div>
            </div>
        @endforeach
    </div>

    <div class="adm-card p-0 overflow-hidden">
        <div class="px-3 py-3 fw-bold border-bottom">Recent</div>
        @if($recent->isEmpty())
            <div class="pr-empty"><div class="pr-empty__title">No products</div><p class="text-muted mb-0">Open a family above and add the first item.</p></div>
        @else
            <div class="table-responsive">
                <table class="table align-middle mb-0 pr-table">
                    <thead>
                        <tr><th class="ps-3">Product</th><th>Family</th><th>Category</th><th class="text-end">Price</th><th class="text-center">Stock</th><th class="text-end pe-3">Actions</th></tr>
                    </thead>
                    <tbody>
                        @foreach($recent as $prod)
                            <tr>
                                <td class="ps-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="{{ $prod->image_url }}" alt="" width="46" height="46" loading="lazy" class="pr-thumb"
                                             onerror="this.onerror=null;this.src='https://placehold.co/100x100/f1f5f9/64748b?text=%20';">
                                        <div class="min-w-0">
                                            <a href="{{ route('admin.products.edit', $prod->id) }}" class="pr-t-title">{{ $prod->title }}</a>
                                            <div class="pr-t-sub">{{ $prod->brand }}@if($prod->brand && $prod->sku) · @endif{{ $prod->sku }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="pr-pill pr-pill--{{ $tone[$prod->type] ?? 'info' }}">{{ \App\Models\Product::TYPES[$prod->type] ?? ucfirst($prod->type) }}</span></td>
                                <td class="text-muted">{{ $prod->category?->name ?? '—' }}</td>
                                <td class="text-end"><strong>৳{{ number_format($prod->final_price, 0) }}</strong></td>
                                <td class="text-center">
                                    <span class="pr-pill pr-pill--{{ $prod->stock <= 0 ? 'danger' : ($prod->stock <= 5 ? 'warning' : 'success') }}">{{ $prod->stock <= 0 ? 'Out' : $prod->stock }}</span>
                                </td>
                                <td class="text-end pe-3"><a href="{{ route('admin.products.edit', $prod->id) }}" class="btn btn-primary pr-act">Edit</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
