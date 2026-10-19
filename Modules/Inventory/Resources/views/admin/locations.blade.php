@extends('layouts.admin')

@push('styles')
@include('inventory::admin._nav')
@endpush

@section('title', 'Locations')
@section('heading', 'Locations')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Locations</li>
@endsection

@php
    $locFields = ['id', 'name', 'code', 'type', 'is_central', 'is_active', 'contact_person', 'phone', 'email', 'address'];
    $newLocation = json_encode(['id' => null, 'type' => 'warehouse', 'is_active' => true]);
@endphp

@section('actions')
    <button type="button" class="btn btn-primary btn-sm" data-iv-location="{{ $newLocation }}" data-bs-toggle="modal" data-bs-target="#locationModal">New</button>
@endsection

@section('content')
@include('inventory::admin._tabs')

<div class="row g-3">
    @forelse ($locations as $loc)
        <div class="col-12 col-md-6 col-xl-4">
            <div class="card iv-loc {{ $loc->is_active ? '' : 'is-off' }}">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <div>
                            <h6 class="mb-1 fw-bold">{{ $loc->name }}</h6>
                            <span class="small text-muted">{{ $loc->code }}</span>
                        </div>
                        <div class="d-flex flex-wrap gap-1 justify-content-end">
                            <span class="badge {{ $loc->type === 'warehouse' ? 'bg-primary-subtle text-primary' : 'bg-info-subtle text-info' }}">{{ ucfirst($loc->type) }}</span>
                            @if ($loc->is_central)<span class="badge bg-success-subtle text-success">Central</span>@endif
                            @unless ($loc->is_active)<span class="badge bg-secondary-subtle text-secondary">Off</span>@endunless
                        </div>
                    </div>
                    <dl class="iv-stats">
                        <div><dt>Titles</dt><dd>{{ number_format($loc->titles) }}</dd></div>
                        <div><dt>Units</dt><dd>{{ number_format((int) $loc->units) }}</dd></div>
                        <div><dt>Out</dt><dd>{{ $loc->outgoing_transfers_count }}</dd></div>
                        <div><dt>In</dt><dd>{{ $loc->incoming_transfers_count }}</dd></div>
                    </dl>
                    @if ($loc->contact_person || $loc->phone)
                        <div class="small text-muted mb-2">{{ $loc->contact_person }} {{ $loc->phone }}</div>
                    @endif
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-iv-location="{{ json_encode($loc->only($locFields)) }}" data-bs-toggle="modal" data-bs-target="#locationModal">Edit</button>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12"><div class="card"><div class="card-body text-center text-muted py-5">No locations</div></div></div>
    @endforelse
</div>

<div class="modal fade" id="locationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" class="modal-content" id="locationForm" data-store="{{ route('admin.inventory.locations.store') }}" data-update="{{ url('admin/inventory/locations') }}">
            @csrf
            <input type="hidden" name="_method" value="POST" id="locationMethod">
            <div class="modal-header"><h5 class="modal-title">Location</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-8"><label class="form-label">Name</label><input name="name" class="form-control" maxlength="255" required></div>
                    <div class="col-4"><label class="form-label">Code</label><input name="code" class="form-control" maxlength="50" required></div>
                    <div class="col-6">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-select"><option value="warehouse">Warehouse</option><option value="showroom">Showroom</option></select>
                    </div>
                    <div class="col-6"><label class="form-label">Contact</label><input name="contact_person" class="form-control" maxlength="255"></div>
                    <div class="col-6"><label class="form-label">Phone</label><input name="phone" class="form-control" maxlength="30"></div>
                    <div class="col-6"><label class="form-label">Email</label><input type="email" name="email" class="form-control" maxlength="255"></div>
                    <div class="col-12"><label class="form-label">Address</label><input name="address" class="form-control" maxlength="500"></div>
                    <div class="col-6"><label class="iv-check"><input type="checkbox" name="is_central" value="1"> Central</label></div>
                    <div class="col-6"><label class="iv-check"><input type="checkbox" name="is_active" value="1" checked> Active</label></div>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary">Save</button></div>
        </form>
    </div>
</div>
@endsection
