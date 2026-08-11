@extends('layouts.admin')

@section('title', 'সেলার অ্যাকাউন্ট')
@section('heading', 'সেলার অ্যাকাউন্ট')
@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">সেলার অ্যাকাউন্ট</li>
@endsection

@section('actions')
    @if (Route::has('admin.sub-admins.create') && auth()->user()->isAdmin())
        <a href="{{ route('admin.sub-admins.create') }}" class="btn btn-primary">
            <i class="fas fa-user-plus me-1"></i> নতুন সেলার
        </a>
    @endif
@endsection

@section('content')

<p class="text-muted small mb-3">প্রত্যেক সেলারের বিল সংখ্যা ও মোট বিক্রয়ের সারসংক্ষেপ।</p>

<div class="adm-card">
    @if ($sellers->isEmpty())
        <div class="empty-state">
            <i class="fas fa-store-slash"></i>
            <div>এখনো কোনো সেলার অ্যাকাউন্ট নেই</div>
        </div>
    @else
        <div class="table-responsive">
            <table class="table adm-table align-middle">
                <thead>
                    <tr>
                        <th class="ps-3">সেলার</th>
                        <th>যোগাযোগ</th>
                        <th class="text-end">বিল সংখ্যা</th>
                        <th class="text-end">মোট বিক্রয়</th>
                        <th>অবস্থা</th>
                        <th class="pe-3">যোগদান</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sellers as $seller)
                        @php
                            // The controller loads a grouped aggregate on the bills relation.
                            $revenue = (float) ($seller->bills->first()->revenue ?? 0);
                        @endphp
                        <tr>
                            <td class="ps-3">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="adm-avatar adm-avatar--sm">{{ mb_substr($seller->name, 0, 1) }}</span>
                                    <span class="fw-semibold small">{{ $seller->name }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="small">{{ $seller->email }}</div>
                                <div class="text-muted" style="font-size:.75rem">{{ $seller->phone ?: '—' }}</div>
                            </td>
                            <td class="text-end text-muted small">@bn($seller->bills_count)</td>
                            <td class="text-end fw-semibold">@taka($revenue)</td>
                            <td>
                                <span class="pill {{ $seller->is_active ? 'pill--ok' : 'pill--muted' }}">
                                    {{ $seller->is_active ? 'সক্রিয়' : 'নিষ্ক্রিয়' }}
                                </span>
                            </td>
                            <td class="pe-3 text-muted small">@bnDate($seller->created_at)</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($sellers->hasPages())
            <div class="adm-card__foot d-flex flex-wrap justify-content-between align-items-center gap-2">
                <span class="text-muted small">মোট @bn($sellers->total())টির মধ্যে @bn($sellers->firstItem())–@bn($sellers->lastItem())</span>
                {{ $sellers->onEachSide(1)->links() }}
            </div>
        @else
            <div class="adm-card__foot text-muted small">মোট @bn($sellers->total())টি</div>
        @endif
    @endif
</div>

@endsection
