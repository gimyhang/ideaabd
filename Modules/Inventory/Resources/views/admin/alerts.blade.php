@extends('layouts.admin')

@push('styles')
@include('inventory::admin._nav')
@endpush

@section('title', 'Alerts')
@section('heading', 'Alerts')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Alerts</li>
@endsection

@section('content')
@include('inventory::admin._tabs')

<div class="card">
    <div class="card-body">
        <form method="GET" class="iv-filter">
            <select name="type" class="form-select" aria-label="Type" onchange="this.form.submit()">
                <option value="">All types</option>
                <option value="showroom" @selected($type === 'showroom')>Showroom</option>
                <option value="warehouse" @selected($type === 'warehouse')>Warehouse</option>
            </select>
            <select name="location_id" class="form-select" aria-label="Location" onchange="this.form.submit()">
                <option value="">All locations</option>
                @foreach ($locations as $l)<option value="{{ $l->id }}" @selected(request('location_id') == $l->id)>{{ $l->name }}</option>@endforeach
            </select>
            <span class="iv-count">{{ $alerts->count() }}</span>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead><tr><th>Book</th><th>Location</th><th class="text-end">Stock</th><th class="text-end">Minimum</th><th></th></tr></thead>
            <tbody>
                @forelse ($alerts as $a)
                    <tr>
                        <td class="fw-semibold">{{ $a->book?->title ?? '—' }}</td>
                        <td>{{ $a->location?->name }}</td>
                        <td class="text-end"><span class="iv-qty is-low">{{ number_format($a->quantity) }}</span></td>
                        <td class="text-end" style="width:200px">
                            <form method="POST" action="{{ route('admin.inventory.alerts.update', $a->id) }}" class="iv-inline">
                                @csrf @method('PUT')
                                <input type="number" name="min_alert_quantity" min="0" value="{{ $a->min_alert_quantity }}" class="form-control form-control-sm text-end" aria-label="Minimum">
                                <button class="btn btn-outline-secondary btn-sm">Save</button>
                            </form>
                        </td>
                        <td class="text-end"><a href="{{ route('admin.inventory.transfers') }}" class="btn btn-outline-primary btn-sm">Transfer</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-5">No alerts</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
