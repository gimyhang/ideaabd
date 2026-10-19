@extends('layouts.admin')

@push('styles')
@include('inventory::admin._nav')
@endpush

@section('title', 'Wastage')
@section('heading', 'Wastage')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Wastage</li>
@endsection

@section('actions')
    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#wastageModal">Record</button>
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
            <select name="reason" class="form-select" aria-label="Reason" onchange="this.form.submit()">
                <option value="">All reasons</option>
                @foreach ($reasons as $key => $label)<option value="{{ $key }}" @selected(request('reason') === $key)>{{ $label }}</option>@endforeach
            </select>
            @if (request()->hasAny(['location_id', 'reason']))<a href="{{ route('admin.inventory.wastages') }}" class="btn btn-light btn-sm">Reset</a>@endif
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead><tr><th>Date</th><th>Book</th><th>Location</th><th class="text-end">Qty</th><th>Reason</th><th>Action</th><th>By</th></tr></thead>
            <tbody>
                @forelse ($wastages as $w)
                    <tr>
                        <td>{{ $w->created_at?->format('d M Y') }}</td>
                        <td class="fw-semibold">{{ $w->book?->title ?? '—' }}@if ($w->notes)<div class="small text-muted fw-normal">{{ $w->notes }}</div>@endif</td>
                        <td>{{ $w->location?->name }}</td>
                        <td class="text-end fw-bold text-danger">{{ number_format($w->quantity) }}</td>
                        <td>{{ $reasons[$w->reason] ?? $w->reason }}</td>
                        <td>{{ ucfirst(str_replace('_', ' ', (string) $w->action_taken)) }}</td>
                        <td>{{ $w->reporter?->name ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-5">No records</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($wastages->hasPages())<div class="card-footer">{{ $wastages->links() }}</div>@endif
</div>

<div class="modal fade" id="wastageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('admin.inventory.wastages.store') }}" class="modal-content">
            @csrf
            <div class="modal-header"><h5 class="modal-title">Wastage</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12">@include('inventory::admin._picker', ['name' => 'book_id', 'label' => 'Book', 'locationSelector' => '#wsLocation'])</div>
                    <div class="col-6"><label class="form-label">Location</label><select name="location_id" id="wsLocation" class="form-select" required>@foreach ($locations as $l)<option value="{{ $l->id }}">{{ $l->name }}</option>@endforeach</select></div>
                    <div class="col-6"><label class="form-label">Quantity</label><input type="number" name="quantity" min="1" class="form-control" required></div>
                    <div class="col-6"><label class="form-label">Reason</label><select name="reason" class="form-select" required>@foreach ($reasons as $key => $label)<option value="{{ $key }}">{{ $label }}</option>@endforeach</select></div>
                    <div class="col-6">
                        <label class="form-label">Action</label>
                        <select name="action_taken" class="form-select">@foreach (['disposed', 'returned_to_press', 'repaired', 'other'] as $a)<option value="{{ $a }}">{{ ucfirst(str_replace('_', ' ', $a)) }}</option>@endforeach</select>
                    </div>
                    <div class="col-12"><label class="form-label">Note</label><input name="notes" class="form-control" maxlength="1000"></div>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary">Save</button></div>
        </form>
    </div>
</div>
@endsection
