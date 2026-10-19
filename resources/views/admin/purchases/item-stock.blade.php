@extends('layouts.admin')

@push('styles')
@include('admin.accounting._modern')
<link rel="stylesheet" href="{{ asset('css/purchases-items.css') }}?v={{ @filemtime(public_path('css/purchases-items.css')) ?: 1 }}">
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
<div class="pi-kpis">
    @foreach ([['Items', number_format($kpis['items']), ''], ['Units', number_format($kpis['units']), ''], ['Low', number_format($kpis['low']), $kpis['low'] > 0 ? 'is-bad' : ''], ['Value', '৳' . number_format($kpis['value'], 0), '']] as $k)
        <article class="card pi-kpi {{ $k[2] }}"><span>{{ $k[0] }}</span><strong>{{ $k[1] }}</strong></article>
    @endforeach
</div>

<section class="card pi-card p-0">
    <div class="pi-toolbar">
        <form method="GET" class="pi-filter">
            <select name="type" class="form-select" aria-label="Type" onchange="this.form.submit()">
                <option value="">All</option>
                @foreach ($types as $k => $label)<option value="{{ $k }}" @selected($type === $k)>{{ $label }}</option>@endforeach
            </select>
            <input type="search" name="q" value="{{ $search }}" class="form-control" aria-label="Search">
            <label class="iv-check"><input type="checkbox" name="low" value="1" @checked(request()->boolean('low')) onchange="this.form.submit()"> Low</label>
            <button class="btn btn-primary btn-sm">Search</button>
            @if ($type || $search || request()->boolean('low'))<a href="{{ route('admin.purchases.items.stock') }}" class="btn btn-light btn-sm">Reset</a>@endif
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead><tr><th>Item</th><th>Type</th><th>SKU</th><th class="text-end">Stock</th><th class="text-end">Purchased</th><th class="text-end">Price</th><th>Last</th></tr></thead>
            <tbody>
                @forelse ($products as $p)
                    <tr>
                        <td class="fw-semibold">{{ $p->title }}</td>
                        <td><span class="pi-tag is-{{ $p->type }}">{{ $types[$p->type] ?? ucfirst($p->type) }}</span></td>
                        <td class="text-muted">{{ $p->sku ?: '—' }}</td>
                        <td class="text-end"><span class="pi-qty-chip {{ $p->stock <= 5 ? 'is-low' : '' }}">{{ number_format($p->stock) }}</span></td>
                        <td class="text-end">{{ number_format($p->purchased_qty) }}</td>
                        <td class="text-end">৳{{ number_format($p->price, 2) }}</td>
                        <td class="text-muted text-nowrap">{{ $p->last_at ? \Illuminate\Support\Carbon::parse($p->last_at)->format('d M Y') : '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="pi-empty">None</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($products->hasPages())<div class="card-footer">{{ $products->links() }}</div>@endif
</section>
@endsection
