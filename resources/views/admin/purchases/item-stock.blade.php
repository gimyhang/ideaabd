@extends('layouts.admin')

@push('styles')
@include('admin.accounting._modern')
<link rel="stylesheet" href="{{ asset('css/purchases-items.css') }}?v={{ @filemtime(public_path('css/purchases-items.css')) ?: 1 }}">
<script src="{{ asset('js/purchases-items.js') }}?v={{ @filemtime(public_path('js/purchases-items.js')) ?: 1 }}" defer></script>
@endpush

@section('title', 'Items')
@section('heading', 'Items')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.purchases.index') }}">Purchases</a></li>
    <li class="breadcrumb-item active" aria-current="page">Items</li>
@endsection

@section('actions')
    <a href="{{ route('admin.purchases.stationery') }}" class="btn btn-primary btn-sm">Stationery</a>
    <a href="{{ route('admin.purchases.electronics') }}" class="btn btn-primary btn-sm">Electronics</a>
@endsection

@section('content')
<section class="card pi-card p-0 pi-stock" id="piStock" data-url="{{ route('admin.purchases.items.stock-list') }}" data-merge="{{ route('admin.purchases.items.merge') }}" data-type="{{ $type }}" data-csrf="{{ csrf_token() }}">
    <div class="pi-toolbar">
        <h3 class="pi-h">Stock <span class="pi-live"><span class="pi-dot"></span> <span id="piStockAt">live</span></span></h3>
        <form class="pi-filter" id="piStockFilter" onsubmit="return false">
            <input type="hidden" name="category_id" value="{{ request('category_id') }}">
            <select name="type" class="form-select" aria-label="Type">
                <option value="">All types</option>
                @foreach ($types as $k => $label)<option value="{{ $k }}" @selected($type === $k)>{{ $label }}</option>@endforeach
            </select>
            <input type="search" name="q" value="{{ $search }}" class="form-control" placeholder="Name / SKU / Brand" aria-label="Search stock">
            <label class="iv-check"><input type="checkbox" name="low" value="1" @checked(request()->boolean('low'))> Low</label>
        </form>
    </div>
    <div id="piStockBody">@include('admin.purchases.partials.item-stock-list')</div>
</section>
@endsection
