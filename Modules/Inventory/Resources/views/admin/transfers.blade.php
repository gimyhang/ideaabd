@extends('layouts.admin')

@push('styles')
@include('inventory::admin._nav')
@endpush

@section('title', 'Transfers')
@section('heading', 'Transfers')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Transfers</li>
@endsection

@section('actions')
    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#transferModal">New</button>
@endsection

@php
    $statusClass = ['pending' => 'bg-warning-subtle text-warning-emphasis', 'in_transit' => 'bg-info-subtle text-info', 'received' => 'bg-success-subtle text-success', 'cancelled' => 'bg-secondary-subtle text-secondary'];
@endphp

@section('content')
@include('inventory::admin._tabs')

<div class="card">
    <div class="card-body">
        <form method="GET" class="iv-filter">
            <input type="search" name="challan_no" value="{{ request('challan_no') }}" class="form-control" aria-label="Challan">
            <select name="status" class="form-select" aria-label="Status">
                <option value="">All</option>
                @foreach (['pending', 'in_transit', 'received', 'cancelled'] as $st)<option value="{{ $st }}" @selected(request('status') === $st)>{{ ucfirst(str_replace('_', ' ', $st)) }}</option>@endforeach
            </select>
            <select name="from_location_id" class="form-select" aria-label="From"><option value="">From</option>@foreach ($locations as $l)<option value="{{ $l->id }}" @selected(request('from_location_id') == $l->id)>{{ $l->name }}</option>@endforeach</select>
            <select name="to_location_id" class="form-select" aria-label="To"><option value="">To</option>@foreach ($locations as $l)<option value="{{ $l->id }}" @selected(request('to_location_id') == $l->id)>{{ $l->name }}</option>@endforeach</select>
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control" aria-label="From date">
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control" aria-label="To date">
            <button class="btn btn-primary btn-sm">Filter</button>
            @if (request()->hasAny(['challan_no', 'status', 'from_location_id', 'to_location_id', 'date_from', 'date_to']))<a href="{{ route('admin.inventory.transfers') }}" class="btn btn-light btn-sm">Reset</a>@endif
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead><tr><th>Challan</th><th>From</th><th>To</th><th class="text-end">Items</th><th>Date</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @forelse ($transfers as $t)
                    <tr>
                        <td class="fw-semibold">{{ $t->challan_no }}</td>
                        <td>{{ $t->fromLocation?->name }}</td>
                        <td>{{ $t->toLocation?->name }}</td>
                        <td class="text-end">{{ number_format($t->items->sum("quantity_sent")) }}</td>
                        <td>{{ $t->transfer_date?->format('d M Y') }}</td>
                        <td><span class="badge {{ $statusClass[$t->status] ?? '' }}">{{ ucfirst(str_replace('_', ' ', $t->status)) }}</span></td>
                        <td class="text-end"><a href="{{ route('admin.inventory.transfers.show', $t->id) }}" class="btn btn-outline-secondary btn-sm">Open</a></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-5">No transfers</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($transfers->hasPages())<div class="card-footer">{{ $transfers->links() }}</div>@endif
</div>

<div class="modal fade" id="transferModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form method="POST" action="{{ route('admin.inventory.transfers.store') }}" class="modal-content">
            @csrf
            <div class="modal-header"><h5 class="modal-title">Transfer</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6"><label class="form-label">From</label><select name="from_location_id" id="trFrom" class="form-select" required>@foreach ($locations as $l)<option value="{{ $l->id }}" @selected($l->is_central)>{{ $l->name }}</option>@endforeach</select></div>
                    <div class="col-md-6"><label class="form-label">To</label><select name="to_location_id" class="form-select" required>@foreach ($locations as $l)<option value="{{ $l->id }}" @selected($loop->last)>{{ $l->name }}</option>@endforeach</select></div>
                </div>
                <div class="iv-rows" id="trRows" data-template="trRow"></div>
                <button type="button" class="btn btn-outline-secondary btn-sm" data-iv-add="trRows">Add book</button>
                <div class="mt-3"><label class="form-label">Note</label><input name="notes" class="form-control" maxlength="1000"></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary">Create</button></div>
        </form>
    </div>
</div>

<template id="trRow">
    <div class="iv-row">
        <div class="iv-row-book">@include('inventory::admin._picker', ['name' => 'items[__i__][book_id]', 'locationSelector' => '#trFrom'])</div>
        <input type="number" name="items[__i__][quantity]" min="1" class="form-control iv-row-qty" aria-label="Quantity" required>
        <button type="button" class="btn btn-outline-danger btn-sm" data-iv-remove aria-label="Remove">Remove</button>
    </div>
</template>
@endsection
