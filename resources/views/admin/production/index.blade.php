@extends('layouts.admin')

@push('styles')
@include('admin.accounting._modern')
<link rel="stylesheet" href="{{ asset('css/production.css') }}?v={{ @filemtime(public_path('css/production.css')) ?: 1 }}">
<script src="{{ asset('js/production.js') }}?v={{ @filemtime(public_path('js/production.js')) ?: 1 }}" defer></script>
@endpush

@section('title', 'Production')
@section('heading', 'Production')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Production</li>
@endsection

@section('actions')
    @foreach ($stages as $key => $label)
        <button type="button" class="btn {{ $key === 'printed' ? 'btn-primary' : 'btn-outline-primary' }} btn-sm" data-stage="{{ $key }}" data-bs-toggle="modal" data-bs-target="#stageModal">{{ $label }}</button>
    @endforeach
@endsection

@section('content')
<div class="pd-kpis">
    @foreach ([
        ['Printed', $totals['printed'], 'is-blue'],
        ['Bound', $totals['bound'], 'is-violet'],
        ['Received', $totals['received'], 'is-green'],
        ['Press', $totals['at_press'], $totals['at_press'] > 0 ? 'is-amber' : ''],
        ['Binder', $totals['at_binder'], $totals['at_binder'] > 0 ? 'is-amber' : ''],
    ] as $k)
        <article class="card pd-kpi {{ $k[2] }}"><span>{{ $k[0] }}</span><strong>{{ number_format($k[1]) }}</strong></article>
    @endforeach
</div>

<section class="card pd-card p-0">
    <div class="pd-toolbar">
        <form method="GET" class="pd-filter">
            <input type="search" name="q" value="{{ $search }}" class="form-control" aria-label="Search">
            <input type="date" name="date_from" value="{{ $from }}" class="form-control" aria-label="From">
            <input type="date" name="date_to" value="{{ $to }}" class="form-control" aria-label="To">
            <label class="iv-check"><input type="checkbox" name="open" value="1" @checked($openOnly) onchange="this.form.submit()"> Open</label>
            <button class="btn btn-primary btn-sm">Filter</button>
            @if ($search || $from || $to || $openOnly)<a href="{{ route('admin.production.index') }}" class="btn btn-light btn-sm">Reset</a>@endif
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Book</th>
                    <th class="text-end">Printed</th><th class="text-end">Bound</th><th class="text-end">Received</th>
                    <th class="text-end">Press</th><th class="text-end">Binder</th><th class="text-end">Stock</th><th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($summary as $row)
                    <tr>
                        <td><div class="fw-semibold">{{ $row->book->title }}</div>@if ($row->book->isbn)<div class="pd-sub">{{ $row->book->isbn }}</div>@endif</td>
                        <td class="text-end">{{ number_format($row->printed) }}</td>
                        <td class="text-end">{{ number_format($row->bound) }}</td>
                        <td class="text-end">{{ number_format($row->received) }}</td>
                        <td class="text-end">@if ($row->at_press)<span class="pd-chip is-amber">{{ number_format($row->at_press) }}</span>@else<span class="text-muted">0</span>@endif</td>
                        <td class="text-end">@if ($row->at_binder)<span class="pd-chip is-amber">{{ number_format($row->at_binder) }}</span>@else<span class="text-muted">0</span>@endif</td>
                        <td class="text-end fw-bold">{{ number_format($row->book->stock_quantity) }}</td>
                        <td class="text-end"><a href="{{ route('admin.production.index', ['book_id' => $row->book->id]) }}" class="btn btn-outline-secondary btn-sm">History</a></td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="pd-empty">None</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

<section class="card pd-card p-0">
    <div class="pd-toolbar">
        <h3 class="pd-h">History</h3>
        <form method="GET" class="pd-filter">
            @foreach (['q', 'date_from', 'date_to'] as $keep)@if (request($keep))<input type="hidden" name="{{ $keep }}" value="{{ request($keep) }}">@endif @endforeach
            @if (request('book_id'))<input type="hidden" name="book_id" value="{{ request('book_id') }}">@endif
            <select name="stage" class="form-select" aria-label="Stage" onchange="this.form.submit()">
                <option value="">All</option>
                @foreach ($stages as $key => $label)<option value="{{ $key }}" @selected(request('stage') === $key)>{{ $label }}</option>@endforeach
            </select>
            @if (request('book_id'))<a href="{{ route('admin.production.index') }}" class="btn btn-light btn-sm">All books</a>@endif
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead><tr><th>Date</th><th>Book</th><th>Stage</th><th class="text-end">Qty</th><th>Batch</th><th>Vendor</th><th>By</th><th></th></tr></thead>
            <tbody>
                @forelse ($events as $e)
                    <tr>
                        <td class="text-nowrap">{{ $e->event_date->format('d M Y') }}</td>
                        <td class="fw-semibold">{{ $e->book?->title ?? '—' }}@if ($e->notes)<div class="pd-sub">{{ $e->notes }}</div>@endif</td>
                        <td><span class="pd-chip is-{{ ['printed' => 'blue', 'bound' => 'violet', 'received' => 'green'][$e->stage] ?? 'gray' }}">{{ $stages[$e->stage] ?? $e->stage }}</span></td>
                        <td class="text-end fw-bold">{{ number_format($e->quantity) }}</td>
                        <td class="text-muted">{{ $e->batch_no ?: '—' }}</td>
                        <td class="text-muted">{{ $e->vendor ?: '—' }}</td>
                        <td class="text-muted">{{ $e->creator?->name ?? '—' }}</td>
                        <td class="text-end">
                            <form method="POST" action="{{ route('admin.production.destroy', $e->id) }}" onsubmit="return confirm('Remove this entry?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-outline-danger btn-sm">Remove</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="pd-empty">None</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($events->hasPages())<div class="card-footer">{{ $events->links() }}</div>@endif
</section>

<div class="modal fade" id="stageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('admin.production.store') }}" class="modal-content" id="stageForm" data-books="{{ route('admin.production.books') }}">
            @csrf
            <div class="modal-header"><h5 class="modal-title" id="stageTitle">Printed</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
            <div class="modal-body">
                <input type="hidden" name="stage" id="stageInput" value="printed">
                <div class="row">
                    <div class="col-12 pd-pick">
                        <label class="form-label">Book</label>
                        <input type="search" class="form-control" id="bookSearch" autocomplete="off" aria-label="Book">
                        <input type="hidden" name="book_id" id="bookId">
                        <div class="pd-list" id="bookList" hidden></div>
                        <div class="pd-hint" id="bookHint" hidden></div>
                    </div>
                    <div class="col-6"><label class="form-label">Quantity</label><input type="number" name="quantity" min="1" class="form-control" required></div>
                    <div class="col-6"><label class="form-label">Date</label><input type="date" name="event_date" value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}" class="form-control" required></div>
                    <div class="col-6"><label class="form-label">Batch</label><input name="batch_no" class="form-control" maxlength="100"></div>
                    <div class="col-6"><label class="form-label" id="vendorLabel">Press</label><input name="vendor" class="form-control" maxlength="255"></div>
                    <div class="col-12"><label class="form-label">Note</label><input name="notes" class="form-control" maxlength="1000"></div>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary">Save</button></div>
        </form>
    </div>
</div>
@endsection
