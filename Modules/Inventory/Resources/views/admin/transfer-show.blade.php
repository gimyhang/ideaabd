@extends('layouts.admin')

@push('styles')
@include('inventory::admin._nav')
@endpush

@section('title', $transfer->challan_no)
@section('heading', $transfer->challan_no)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.inventory.transfers') }}">Transfers</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $transfer->challan_no }}</li>
@endsection

@php
    $open = in_array($transfer->status, ['pending', 'in_transit'], true);
    $statusClass = ['pending' => 'bg-warning-subtle text-warning-emphasis', 'in_transit' => 'bg-info-subtle text-info', 'received' => 'bg-success-subtle text-success', 'cancelled' => 'bg-secondary-subtle text-secondary'];
@endphp

@section('actions')
    @if ($open)
        <form method="POST" action="{{ route('admin.inventory.transfers.receive', $transfer->id) }}">@csrf<button class="btn btn-success btn-sm">Receive</button></form>
        <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#cancelModal">Cancel</button>
    @endif
    <a href="{{ route('admin.inventory.transfers') }}" class="btn btn-light btn-sm">Back</a>
@endsection

@section('content')
@include('inventory::admin._tabs')

<div class="row g-3">
    <div class="col-lg-4">
        <div class="card"><div class="card-body">
            <dl class="iv-meta">
                <div><dt>Status</dt><dd><span class="badge {{ $statusClass[$transfer->status] ?? '' }}">{{ ucfirst(str_replace('_', ' ', $transfer->status)) }}</span></dd></div>
                <div><dt>From</dt><dd>{{ $transfer->fromLocation?->name }}</dd></div>
                <div><dt>To</dt><dd>{{ $transfer->toLocation?->name }}</dd></div>
                <div><dt>Date</dt><dd>{{ $transfer->transfer_date?->format('d M Y') }}</dd></div>
                @if ($transfer->received_date)<div><dt>Received</dt><dd>{{ $transfer->received_date->format('d M Y H:i') }}</dd></div>@endif
                <div><dt>Created by</dt><dd>{{ $transfer->creator?->name ?? '—' }}</dd></div>
                @if ($transfer->receiver)<div><dt>Received by</dt><dd>{{ $transfer->receiver->name }}</dd></div>@endif
                @if ($transfer->notes)<div><dt>Note</dt><dd>{{ $transfer->notes }}</dd></div>@endif
            </dl>
        </div></div>
    </div>
    <div class="col-lg-8">
        <div class="card">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th>Book</th><th class="text-end">Sent</th><th class="text-end">Received</th></tr></thead>
                    <tbody>
                        @foreach ($transfer->items as $it)
                            <tr>
                                <td class="fw-semibold">{{ $it->book?->title ?? '—' }}</td>
                                <td class="text-end">{{ number_format($it->quantity_sent) }}</td>
                                <td class="text-end">{{ $transfer->status === 'received' ? number_format((int) ($it->quantity_received ?? $it->quantity_sent)) : '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@if ($open)
<div class="modal fade" id="cancelModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('admin.inventory.transfers.cancel', $transfer->id) }}" class="modal-content">
            @csrf
            <div class="modal-header"><h5 class="modal-title">Cancel</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
            <div class="modal-body"><label class="form-label">Reason</label><input name="reason" class="form-control" maxlength="500"></div>
            <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button><button class="btn btn-danger">Cancel transfer</button></div>
        </form>
    </div>
</div>
@endif
@endsection
