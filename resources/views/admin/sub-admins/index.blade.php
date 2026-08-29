@extends('layouts.admin')

@section('title', 'Sub-Admins & Staff')
@section('heading', 'Sub-Admins & Store Staff')
@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Sub-Admins</li>
@endsection

@section('actions')
    <a href="{{ route('admin.sub-admins.create') }}" class="btn btn-primary rounded-pill px-3 shadow-xs">
        <i class="fas fa-user-plus me-1"></i> Add Sub-Admin
    </a>
@endsection

@section('content')

<div class="row g-3 mb-4">
    @foreach ([
        ['label' => 'Sub-Admins', 'value' => $counts['sub_admin'], 'icon' => 'user-shield', 'color' => '#0066cc'],
        ['label' => 'Sellers',    'value' => $counts['seller'],    'icon' => 'store',       'color' => '#ff6b35'],
    ] as $tile)
        <div class="col-md-4 col-6">
            <div class="kpi bg-white rounded-4 shadow-sm border-0 p-3" style="--bar: {{ $tile['color'] }}">
                <p class="kpi__label small text-muted fw-semibold mb-1">{{ $tile['label'] }}</p>
                <p class="kpi__value fs-4 fw-bold mb-0" style="color: {{ $tile['color'] }}">{{ number_format($tile['value']) }}</p>
                <span class="kpi__icon" style="background: {{ $tile['color'] }}1a; color: {{ $tile['color'] }}">
                    <i class="fas fa-{{ $tile['icon'] }}"></i>
                </span>
            </div>
        </div>
    @endforeach
</div>

@include('admin.partials.filters', [
    'action'      => route('admin.sub-admins.index'),
    'placeholder' => 'Search by name, email or phone...',
    'selects'     => [
        ['name' => 'role', 'label' => 'All Roles', 'options' => [
            'sub_admin' => 'Sub-Admin', 'seller' => 'Seller',
        ]],
    ],
])

<div class="adm-card bg-white rounded-4 shadow-sm border-0 p-0 overflow-hidden">
    @if ($staff->isEmpty())
        <div class="empty-state py-5 text-center">
            <i class="fas fa-user-shield fs-1 text-muted opacity-50 mb-2"></i>
            <div class="fw-semibold text-dark">No sub-admins or sellers found</div>
            <a href="{{ route('admin.sub-admins.create') }}" class="btn btn-sm btn-primary rounded-pill px-3 mt-3">
                <i class="fas fa-user-plus me-1"></i> Create First Account
            </a>
        </div>
    @else
        <div class="table-responsive">
            <table class="table adm-table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Name</th>
                        <th>Role</th>
                        <th class="text-end">Bills</th>
                        <th class="text-end">Revenue</th>
                        <th>Status</th>
                        <th>Joined</th>
                        <th class="pe-3 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($staff as $member)
                        @php $stat = $revenue[$member->id] ?? null; @endphp
                        <tr>
                            <td class="ps-3">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="adm-avatar adm-avatar--sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 13px;">{{ mb_substr($member->name, 0, 1) }}</span>
                                    <span>
                                        <span class="d-block fw-semibold small text-dark">{{ $member->name }}</span>
                                        <span class="d-block text-muted" style="font-size:.75rem">{{ $member->email }}</span>
                                    </span>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-{{ $member->role === 'sub_admin' ? 'primary' : 'secondary' }}-subtle text-{{ $member->role === 'sub_admin' ? 'primary' : 'dark' }} border rounded-pill px-2.5 py-1">
                                    {{ $member->role === 'sub_admin' ? 'Sub-Admin' : 'Seller' }}
                                </span>
                            </td>
                            <td class="text-end text-muted small">{{ number_format($stat->bills ?? 0) }}</td>
                            <td class="text-end fw-semibold">৳{{ number_format($stat->revenue ?? 0, 2) }}</td>
                            <td>
                                <span class="badge bg-{{ $member->is_active ? 'success' : 'secondary' }}-subtle text-{{ $member->is_active ? 'success' : 'secondary' }} border rounded-pill px-2.5 py-1">
                                    {{ $member->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="text-muted small">{{ $member->created_at->format('d M, Y') }}</td>
                            <td class="pe-3">
                                <div class="d-flex justify-content-end gap-1">
                                    <a href="{{ route('admin.sub-admins.show', $member) }}" class="btn btn-sm btn-outline-primary rounded-pill px-2.5" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.sub-admins.toggle', $member) }}">
                                        @csrf @method('PATCH')
                                        <button class="btn btn-sm {{ $member->is_active ? 'btn-outline-warning' : 'btn-outline-success' }} rounded-pill px-2.5"
                                                title="{{ $member->is_active ? 'Deactivate' : 'Activate' }}">
                                            <i class="fas fa-{{ $member->is_active ? 'ban' : 'circle-check' }}"></i>
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.sub-admins.destroy', $member) }}"
                                          data-confirm="আপনি কি নিশ্চিত যে {{ $member->name }} এর সাব-অ্যাডমিন অ্যাকাউন্টটি মুছে ফেলতে চান?" data-confirm-title="সাব-অ্যাডমিন ডিলিট">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger rounded-pill px-2.5" title="Delete"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($staff->hasPages())
            <div class="adm-card__foot p-3 border-top bg-light d-flex flex-wrap justify-content-between align-items-center gap-2">
                <span class="text-muted small">Showing {{ $staff->firstItem() }} to {{ $staff->lastItem() }} of {{ $staff->total() }} records</span>
                {{ $staff->onEachSide(1)->links() }}
            </div>
        @else
            <div class="adm-card__foot p-3 border-top bg-light text-muted small">Total: {{ $staff->total() }} records</div>
        @endif
    @endif
</div>

@endsection
