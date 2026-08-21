@extends('layouts.admin')

@section('title', 'Orders & Billing')
@section('heading', 'Orders & Billing History')
@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Orders & Billing</li>
@endsection

@section('actions')
    @if (Route::has('subadmin.bills.create'))
        <a href="{{ route('subadmin.bills.create') }}" class="btn btn-primary rounded-pill px-3 shadow-xs">
            <i class="fas fa-plus me-1"></i> Create New Bill
        </a>
    @endif
@endsection

@section('content')

<div class="row g-3 mb-4">
    @foreach ([
        ['label' => 'Total Orders',  'value' => $summary['count'],   'icon' => 'receipt',           'color' => '#0066cc'],
        ['label' => 'Total Revenue', 'value' => $summary['revenue'], 'icon' => 'sack-dollar',       'color' => '#2a9d8f', 'money' => true],
        ['label' => 'Due Balance',   'value' => $summary['due'],     'icon' => 'clock-rotate-left', 'color' => '#e63946', 'money' => true],
    ] as $tile)
        <div class="col-md-4">
            <div class="kpi bg-white rounded-4 shadow-sm border-0 p-3" style="--bar: {{ $tile['color'] }}">
                <p class="kpi__label small text-muted fw-semibold mb-1">{{ $tile['label'] }}</p>
                <p class="kpi__value fs-4 fw-bold mb-0" style="color: {{ $tile['color'] }}">
                    @if (is_null($tile['value']))
                        <span class="text-muted fs-5">—</span>
                    @elseif (! empty($tile['money']))
                        ৳{{ number_format($tile['value'], 2) }}
                    @else
                        {{ number_format($tile['value']) }}
                    @endif
                </p>
                <span class="kpi__icon" style="background: {{ $tile['color'] }}1a; color: {{ $tile['color'] }}">
                    <i class="fas fa-{{ $tile['icon'] }}"></i>
                </span>
            </div>
        </div>
    @endforeach
</div>

@include('admin.partials.filters', [
    'action'      => route('admin.orders'),
    'placeholder' => 'Bill no, customer name or phone...',
    'selects'     => [
        ['name' => 'status', 'label' => 'All Statuses', 'options' => [
            'paid' => 'Paid', 'pending' => 'Due', 'partial' => 'Partial',
        ]],
    ],
])

@include('admin.partials.data-table', [
    'rows'      => $bills,
    'empty'     => 'No orders found',
    'emptyHint' => 'Seller and store invoices will appear here once generated.',
    'emptyIcon' => 'receipt',
    'columns'   => [
        ['key' => 'bill_no',        'label' => 'Bill No.',        'type' => 'strong'],
        ['key' => 'customer_name',  'label' => 'Customer',        'type' => 'strong', 'sub' => 'customer_phone'],
        ['key' => 'seller.name',    'label' => 'Seller / Staff',  'type' => 'muted'],
        ['key' => 'payment_method', 'label' => 'Payment Method',  'type' => 'muted'],
        ['key' => 'total',          'label' => 'Total',           'type' => 'money', 'align' => 'text-end'],
        ['key' => 'payment_status', 'label' => 'Status',         'type' => 'pill', 'map' => [
            'paid'    => ['Paid', 'ok'],
            'pending' => ['Due', 'pending'],
            'partial' => ['Partial', 'info'],
        ]],
        ['key' => 'created_at',     'label' => 'Date',            'type' => 'date'],
    ],
])

@endsection
