@extends('layouts.admin')

@section('title', 'সাব-অ্যাডমিন')
@section('heading', 'সাব-অ্যাডমিন ও সেলার')
@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">সাব-অ্যাডমিন</li>
@endsection

@section('actions')
    <a href="{{ route('admin.sub-admins.create') }}" class="btn btn-primary">
        <i class="fas fa-user-plus me-1"></i> নতুন সাব-অ্যাডমিন
    </a>
@endsection

@section('content')

<div class="row g-3 mb-4">
    @foreach ([
        ['label' => 'সাব-অ্যাডমিন', 'value' => $counts['sub_admin'], 'icon' => 'user-shield', 'color' => '#0066cc'],
        ['label' => 'সেলার',        'value' => $counts['seller'],    'icon' => 'store',       'color' => '#ff6b35'],
    ] as $tile)
        <div class="col-md-4 col-6">
            <div class="kpi" style="--bar: {{ $tile['color'] }}">
                <p class="kpi__label">{{ $tile['label'] }}</p>
                <p class="kpi__value" style="color: {{ $tile['color'] }}">@bn($tile['value'])</p>
                <span class="kpi__icon" style="background: {{ $tile['color'] }}1a; color: {{ $tile['color'] }}">
                    <i class="fas fa-{{ $tile['icon'] }}"></i>
                </span>
            </div>
        </div>
    @endforeach
</div>

@include('admin.partials.filters', [
    'action'      => route('admin.sub-admins.index'),
    'placeholder' => 'নাম, ইমেইল বা ফোন দিয়ে খুঁজুন...',
    'selects'     => [
        ['name' => 'role', 'label' => 'সব ভূমিকা', 'options' => [
            'sub_admin' => 'সাব-অ্যাডমিন', 'seller' => 'সেলার',
        ]],
    ],
])

<div class="adm-card">
    @if ($staff->isEmpty())
        <div class="empty-state">
            <i class="fas fa-user-shield"></i>
            <div>এখনো কোনো সাব-অ্যাডমিন বা সেলার নেই</div>
            <a href="{{ route('admin.sub-admins.create') }}" class="btn btn-sm btn-primary mt-3">
                <i class="fas fa-user-plus me-1"></i> প্রথম অ্যাকাউন্ট তৈরি করুন
            </a>
        </div>
    @else
        <div class="table-responsive">
            <table class="table adm-table align-middle">
                <thead>
                    <tr>
                        <th class="ps-3">নাম</th>
                        <th>ভূমিকা</th>
                        <th class="text-end">বিল</th>
                        <th class="text-end">রাজস্ব</th>
                        <th>অবস্থা</th>
                        <th>যোগদান</th>
                        <th class="pe-3 text-end">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($staff as $member)
                        @php $stat = $revenue[$member->id] ?? null; @endphp
                        <tr>
                            <td class="ps-3">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="adm-avatar adm-avatar--sm">{{ mb_substr($member->name, 0, 1) }}</span>
                                    <span>
                                        <span class="d-block fw-semibold small">{{ $member->name }}</span>
                                        <span class="d-block text-muted" style="font-size:.75rem">{{ $member->email }}</span>
                                    </span>
                                </div>
                            </td>
                            <td>
                                <span class="pill {{ $member->role === 'sub_admin' ? 'pill--info' : 'pill--muted' }}">
                                    {{ $member->role === 'sub_admin' ? 'সাব-অ্যাডমিন' : 'সেলার' }}
                                </span>
                            </td>
                            <td class="text-end text-muted small">@bn($stat->bills ?? 0)</td>
                            <td class="text-end fw-semibold">@taka($stat->revenue ?? 0)</td>
                            <td>
                                <span class="pill {{ $member->is_active ? 'pill--ok' : 'pill--muted' }}">
                                    {{ $member->is_active ? 'সক্রিয়' : 'নিষ্ক্রিয়' }}
                                </span>
                            </td>
                            <td class="text-muted small">@bnDate($member->created_at)</td>
                            <td class="pe-3">
                                <div class="d-flex justify-content-end gap-1">
                                    <a href="{{ route('admin.sub-admins.show', $member) }}" class="btn btn-sm btn-outline-primary" title="বিস্তারিত">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.sub-admins.toggle', $member) }}">
                                        @csrf @method('PATCH')
                                        <button class="btn btn-sm {{ $member->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                                title="{{ $member->is_active ? 'নিষ্ক্রিয় করুন' : 'সক্রিয় করুন' }}">
                                            <i class="fas fa-{{ $member->is_active ? 'ban' : 'circle-check' }}"></i>
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.sub-admins.destroy', $member) }}"
                                          onsubmit="return confirm('{{ $member->name }} — অ্যাকাউন্টটি সরিয়ে ফেলবেন?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" title="সরান"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($staff->hasPages())
            <div class="adm-card__foot d-flex flex-wrap justify-content-between align-items-center gap-2">
                <span class="text-muted small">মোট @bn($staff->total())টির মধ্যে @bn($staff->firstItem())–@bn($staff->lastItem())</span>
                {{ $staff->onEachSide(1)->links() }}
            </div>
        @else
            <div class="adm-card__foot text-muted small">মোট @bn($staff->total())টি</div>
        @endif
    @endif
</div>

@endsection
