@extends('layouts.admin')

@push('styles')
@include('inventory::admin._nav')
@endpush

@section('title', 'Movements')
@section('heading', 'Movements')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Movements</li>
@endsection

@section('content')
@include('inventory::admin._tabs')

<div class="card">
    <div class="card-body">
        <form method="GET" class="iv-filter">
            <select name="location_id" class="form-select" aria-label="Location" onchange="this.form.submit()">
                <option value="">All locations</option>
                @foreach ($locations as $l)<option value="{{ $l->id }}" @selected(request('location_id') == $l->id)>{{ $l->name }}</option>@endforeach
            </select>
            <select name="type" class="form-select" aria-label="Type" onchange="this.form.submit()">
                <option value="">All types</option>
                @foreach ($types as $key => $label)<option value="{{ $key }}" @selected(request('type') === $key)>{{ $label }}</option>@endforeach
            </select>
            @if (request()->hasAny(['location_id', 'type']))<a href="{{ route('admin.inventory.logs') }}" class="btn btn-light btn-sm">Reset</a>@endif
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead><tr><th>Date</th><th>Book</th><th>Location</th><th>Type</th><th class="text-end">Change</th><th class="text-end">Before</th><th class="text-end">After</th><th>By</th></tr></thead>
            <tbody>
                @forelse ($logs as $log)
                    <tr>
                        <td class="text-nowrap">{{ $log->created_at?->format('d M Y H:i') }}</td>
                        <td class="fw-semibold">{{ $log->book?->title ?? '—' }}@if ($log->notes)<div class="small text-muted fw-normal">{{ $log->notes }}</div>@endif</td>
                        <td>{{ $log->location?->name }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $types[$log->type] ?? $log->type }}</span></td>
                        <td class="text-end fw-bold {{ $log->quantity_change >= 0 ? 'text-success' : 'text-danger' }}">{{ $log->quantity_change > 0 ? '+' : '' }}{{ number_format($log->quantity_change) }}</td>
                        <td class="text-end">{{ number_format($log->quantity_before) }}</td>
                        <td class="text-end">{{ number_format($log->quantity_after) }}</td>
                        <td>{{ $log->user?->name ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-5">No movements</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($logs->hasPages())<div class="card-footer">{{ $logs->links() }}</div>@endif
</div>
@endsection
