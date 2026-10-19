@extends('layouts.admin')

@push('styles')
@include('inventory::admin._nav')
@endpush

@section('title', 'Stock')
@section('heading', 'Stock')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Stock</li>
@endsection

@section('actions')
    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#stockInModal">Stock in</button>
    @if ($stats['unallocated'] > 0)
        <form method="POST" action="{{ route('admin.inventory.opening-balance') }}" onsubmit="return confirm('Place unallocated stock in the central warehouse?')">
            @csrf
            <button class="btn btn-outline-secondary btn-sm">Allocate</button>
        </form>
    @endif
@endsection

@section('content')
@include('inventory::admin._tabs')

@php
    $kpis = [
        ['Locations', $stats['locations'], ''],
        ['Catalogue', number_format($stats['catalogue']), ''],
        ['Located', number_format($stats['located']), 'is-ok'],
        ['Unallocated', number_format($stats['unallocated']), $stats['unallocated'] > 0 ? 'is-warn' : ''],
        ['Low', $stats['low'], $stats['low'] > 0 ? 'is-bad' : ''],
        ['Pending', $stats['transit'], ''],
    ];
    $sortUrl = fn ($col) => request()->fullUrlWithQuery(['sort' => $col, 'dir' => request('sort') === $col && request('dir') !== 'desc' ? 'desc' : 'asc', 'page' => null]);
@endphp

<div class="iv-livebar" id="ivLiveBar"><span class="iv-dot"></span> Live · <span id="ivUpdated">now</span> · auto-refresh 30s</div>

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="iv-filter" id="ivFilter" data-export="{{ route('admin.inventory.export') }}">
            <input type="search" name="q" value="{{ $search }}" class="form-control" placeholder="Title / Author / ISBN / SKU" aria-label="Search">
            <select name="category_id" class="form-select" aria-label="Category">
                <option value="">All categories</option>
                @foreach ($categories as $c)<option value="{{ $c->id }}" @selected(request('category_id') == $c->id)>{{ $c->name }}</option>@endforeach
            </select>
            <label class="iv-check"><input type="checkbox" name="unallocated" value="1" @checked(request()->boolean('unallocated'))> Unallocated</label>
            <label class="iv-check"><input type="checkbox" name="low" value="1" @checked(request()->boolean('low'))> Low</label>
            <label class="iv-check"><input type="checkbox" name="empty" value="1" @checked(request()->boolean('empty'))> Out of stock</label>
            <button class="btn btn-primary btn-sm">Search</button>
            <a href="{{ route('admin.inventory.index') }}" class="btn btn-light btn-sm">Reset</a>
            <a href="{{ route('admin.inventory.export', request()->query()) }}" class="btn btn-outline-secondary btn-sm" id="ivExport">CSV</a>
        </form>
    </div>
</div>

<div id="ivLive">
<div class="row g-3 mb-4">
    @foreach ($kpis as $kpi)
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card iv-kpi {{ $kpi[2] }}"><div class="card-body"><span>{{ $kpi[0] }}</span><strong>{{ $kpi[1] }}</strong></div></div>
        </div>
    @endforeach
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th><a href="{{ $sortUrl('title') }}" class="iv-sort">Book</a></th>
                    <th class="text-end"><a href="{{ $sortUrl('stock_quantity') }}" class="iv-sort">Catalogue</a></th>
                    @foreach ($locations as $loc)<th class="text-end" title="{{ $loc->name }}">{{ $loc->code }}</th>@endforeach
                    <th class="text-end">Unallocated</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($books->groupBy(fn ($b) => $b->category?->name ?: 'Uncategorized') as $cat => $group)
                    <tr class="iv-cat"><td colspan="{{ 3 + $locations->count() }}"><strong>{{ $cat }}</strong> <span class="small text-muted">{{ $group->count() }} · {{ number_format($group->sum('stock_quantity')) }}</span></td></tr>
                    @foreach ($group as $book)
                    @php
                        $byLoc = $book->inventories->keyBy('location_id');
                        $free = (int) $book->stock_quantity - (int) ($book->located_qty ?? 0);
                    @endphp
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $book->title }}</div>
                            <div class="small text-muted">{{ $book->author_name }}@if ($book->isbn) · {{ $book->isbn }}@endif</div>
                        </td>
                        <td class="text-end fw-bold">{{ number_format($book->stock_quantity) }}</td>
                        @foreach ($locations as $loc)
                            @php $inv = $byLoc[$loc->id] ?? null; @endphp
                            <td class="text-end">
                                @if ($inv)
                                    <span class="iv-qty {{ $inv->quantity <= $inv->min_alert_quantity ? 'is-low' : '' }}">{{ number_format($inv->quantity) }}</span>
                                @else
                                    <span class="text-muted">0</span>
                                @endif
                            </td>
                        @endforeach
                        <td class="text-end">
                            @if ($free !== 0)<span class="badge {{ $free > 0 ? 'bg-warning-subtle text-warning-emphasis' : 'bg-danger-subtle text-danger' }}">{{ $free > 0 ? '+' : '' }}{{ number_format($free) }}</span>@else<span class="text-muted">0</span>@endif
                        </td>
                    </tr>
                    @endforeach
                @empty
                    <tr><td colspan="{{ 3 + $locations->count() }}" class="text-center text-muted py-5">No books</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($books->hasPages())<div class="card-footer">{{ $books->links() }}</div>@endif
</div>
</div>

<div class="modal fade" id="stockInModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('admin.inventory.stock-in') }}" class="modal-content">
            @csrf
            <div class="modal-header"><h5 class="modal-title">Stock in</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12">@include('inventory::admin._picker', ['name' => 'book_id', 'label' => 'Book', 'locationSelector' => '#stockInLocation'])</div>
                    <div class="col-6">
                        <label class="form-label">Location</label>
                        <select name="location_id" id="stockInLocation" class="form-select">
                            @foreach ($locations->where('type', 'warehouse') as $loc)<option value="{{ $loc->id }}" @selected($loc->is_central)>{{ $loc->name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-6"><label class="form-label">Quantity</label><input type="number" name="quantity" min="1" class="form-control" required></div>
                    <div class="col-6"><label class="form-label">Batch</label><input type="text" name="batch_no" maxlength="100" class="form-control"></div>
                    <div class="col-6"><label class="form-label">Note</label><input type="text" name="notes" maxlength="1000" class="form-control"></div>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary">Save</button></div>
        </form>
    </div>
</div>
@endsection
