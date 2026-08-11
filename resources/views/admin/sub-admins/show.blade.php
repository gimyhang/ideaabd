@extends('layouts.admin')

@section('title', $staff->name)
@section('heading', $staff->name)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.sub-admins.index') }}" class="text-decoration-none">সাব-অ্যাডমিন</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $staff->name }}</li>
@endsection

@section('actions')
    <form method="POST" action="{{ route('admin.sub-admins.toggle', $staff) }}">
        @csrf @method('PATCH')
        <button class="btn {{ $staff->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}">
            <i class="fas fa-{{ $staff->is_active ? 'ban' : 'circle-check' }} me-1"></i>
            {{ $staff->is_active ? 'নিষ্ক্রিয় করুন' : 'সক্রিয় করুন' }}
        </button>
    </form>
    <a href="{{ route('admin.sub-admins.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> তালিকা
    </a>
@endsection

@section('content')

<div class="row g-3">
    {{-- Profile --}}
    <div class="col-lg-4">
        <div class="adm-card h-100">
            <div class="adm-card__body text-center">
                <span class="adm-avatar adm-avatar--lg mx-auto mb-3">{{ mb_substr($staff->name, 0, 1) }}</span>
                <h5 class="fw-bold mb-1">{{ $staff->name }}</h5>
                <span class="pill {{ $staff->role === 'sub_admin' ? 'pill--info' : 'pill--muted' }}">
                    {{ $staff->role === 'sub_admin' ? 'সাব-অ্যাডমিন' : 'সেলার' }}
                </span>
                <span class="pill {{ $staff->is_active ? 'pill--ok' : 'pill--danger' }} ms-1">
                    {{ $staff->is_active ? 'সক্রিয়' : 'নিষ্ক্রিয়' }}
                </span>

                <hr>

                <dl class="text-start small mb-0">
                    <dt class="text-muted fw-normal">ইমেইল</dt>
                    <dd class="fw-semibold">{{ $staff->email }}</dd>

                    <dt class="text-muted fw-normal">ফোন</dt>
                    <dd class="fw-semibold">{{ $staff->phone ?: '—' }}</dd>

                    <dt class="text-muted fw-normal">যোগদান</dt>
                    <dd class="fw-semibold mb-0">@bnDate($staff->created_at)</dd>
                </dl>
            </div>
        </div>
    </div>

    {{-- Billing summary + recent bills --}}
    <div class="col-lg-8">
        <div class="row g-3 mb-3">
            <div class="col-6">
                <div class="kpi" style="--bar:#0066cc">
                    <p class="kpi__label">মোট বিল</p>
                    <p class="kpi__value" style="color:#0066cc">@bn($totals['bills'])</p>
                    <span class="kpi__icon" style="background:#0066cc1a;color:#0066cc"><i class="fas fa-receipt"></i></span>
                </div>
            </div>
            <div class="col-6">
                <div class="kpi" style="--bar:#2a9d8f">
                    <p class="kpi__label">মোট রাজস্ব</p>
                    <p class="kpi__value" style="color:#2a9d8f">@takaS($totals['revenue'])</p>
                    <span class="kpi__icon" style="background:#2a9d8f1a;color:#2a9d8f"><i class="fas fa-sack-dollar"></i></span>
                </div>
            </div>
        </div>

        <div class="adm-card">
            <div class="adm-card__head">
                <h6><i class="fas fa-receipt me-2" style="color:#ff6b35"></i> সাম্প্রতিক বিল</h6>
            </div>

            @if ($bills->isEmpty())
                <div class="empty-state"><i class="fas fa-inbox"></i>এই অ্যাকাউন্টে কোনো বিল নেই</div>
            @else
                <div class="table-responsive">
                    <table class="table adm-table align-middle">
                        <thead>
                            <tr>
                                <th class="ps-3">বিল নং</th>
                                <th>ক্রেতা</th>
                                <th class="text-end">মোট</th>
                                <th>অবস্থা</th>
                                <th class="pe-3">তারিখ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($bills as $bill)
                                <tr>
                                    <td class="ps-3 fw-semibold small">{{ $bill->bill_no }}</td>
                                    <td>
                                        <div class="small">{{ $bill->customer_name ?: 'অজানা' }}</div>
                                        <div class="text-muted" style="font-size:.75rem">{{ $bill->customer_phone }}</div>
                                    </td>
                                    <td class="text-end fw-semibold">@taka($bill->total)</td>
                                    <td>
                                        <span class="pill {{ $bill->payment_status === 'paid' ? 'pill--ok' : 'pill--pending' }}">
                                            {{ $bill->payment_status === 'paid' ? 'পরিশোধিত' : 'বাকি' }}
                                        </span>
                                    </td>
                                    <td class="pe-3 text-muted small">@bnDate($bill->created_at)</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

@endsection
