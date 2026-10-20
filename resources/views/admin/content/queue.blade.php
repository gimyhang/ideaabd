@extends('layouts.admin')

@section('title', 'Moderation')
@section('heading', 'Moderation')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Moderation</li>
@endsection

@section('content')
@php
    $tabs = ['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected', 'all' => 'All'];
    $totalPending = array_sum($pending);
    $chip = ['pending' => 'bg-warning-subtle text-warning-emphasis', 'approved' => 'bg-success-subtle text-success', 'rejected' => 'bg-danger-subtle text-danger'];
@endphp

<div class="d-flex flex-wrap align-items-center gap-2 mb-3">
    @foreach ($tabs as $key => $label)
        <a href="{{ route('admin.moderation', ['status' => $key]) }}" class="btn btn-sm rounded-pill {{ $status === $key ? 'btn-dark' : 'btn-outline-secondary' }}">
            {{ $label }}@if ($key === 'pending' && $totalPending > 0) <span class="badge bg-danger ms-1">{{ $totalPending }}</span>@endif
        </a>
    @endforeach
    <span class="ms-auto text-muted small">{{ count($items) }} items</span>
</div>

@if (session('success'))<div class="alert alert-success py-2">{{ session('success') }}</div>@endif
@if (session('error'))<div class="alert alert-danger py-2">{{ session('error') }}</div>@endif

<div class="card border-0 shadow-sm rounded-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr><th>Type</th><th>Title</th><th>Owner</th><th>Status</th><th>Created</th><th class="text-end">Action</th></tr>
            </thead>
            <tbody>
                @forelse ($items as $item)
                    <tr>
                        <td><span class="badge bg-light text-dark border">{{ $item['label'] }}</span></td>
                        <td class="fw-semibold">
                            {{ \Illuminate\Support\Str::limit($item['title'], 70) }}
                            @if ($item['status'] === 'rejected' && $item['reason'])<div class="small text-danger">{{ $item['reason'] }}</div>@endif
                        </td>
                        <td class="text-muted">{{ $item['credit'] ?: '—' }}</td>
                        <td><span class="badge rounded-pill {{ $chip[$item['status']] ?? 'bg-light text-dark' }}">{{ ucfirst($item['status']) }}</span></td>
                        <td class="text-muted text-nowrap">{{ $item['created'] ? \Illuminate\Support\Carbon::parse($item['created'])->format('d M Y') : '—' }}</td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('admin.content.edit', [$item['type'], $item['id']]) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                            @if ($item['status'] !== 'approved')
                                <form method="POST" action="{{ route('admin.content.approve', [$item['type'], $item['id']]) }}" class="d-inline">
                                    @csrf @method('PATCH')
                                    <button class="btn btn-sm btn-success">Approve</button>
                                </form>
                            @endif
                            @if ($item['status'] !== 'rejected')
                                <form method="POST" action="{{ route('admin.content.reject', [$item['type'], $item['id']]) }}" class="d-inline" onsubmit="var r = prompt('Reason (optional)'); if (r === null) return false; this.reason.value = r;">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="reason" value="">
                                    <button class="btn btn-sm btn-outline-danger">Reject</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-5">Nothing to review</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
