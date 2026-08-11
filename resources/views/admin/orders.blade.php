@extends('layouts.admin')

@section('title', 'অর্ডার ও বিল')
@section('heading', 'অর্ডার ও বিল')
@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">অর্ডার ও বিল</li>
@endsection

@section('actions')
    @if (Route::has('subadmin.bills.create'))
        <a href="{{ route('subadmin.bills.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> নতুন বিল
        </a>
    @endif
@endsection

@section('content')

<div class="row g-3 mb-4">
    @foreach ([
        ['label' => 'মোট অর্ডার',  'value' => $summary['count'],   'icon' => 'receipt',      'color' => '#0066cc'],
        ['label' => 'মোট রাজস্ব',  'value' => $summary['revenue'], 'icon' => 'sack-dollar',  'color' => '#2a9d8f', 'money' => true],
        ['label' => 'বাকি টাকা',   'value' => $summary['due'],     'icon' => 'clock-rotate-left', 'color' => '#e63946', 'money' => true],
    ] as $tile)
        <div class="col-md-4">
            <div class="kpi" style="--bar: {{ $tile['color'] }}">
                <p class="kpi__label">{{ $tile['label'] }}</p>
                <p class="kpi__value" style="color: {{ $tile['color'] }}">
                    @if (is_null($tile['value']))
                        <span class="text-muted fs-5">—</span>
                    @elseif (! empty($tile['money']))
                        @takaS($tile['value'])
                    @else
                        @bn($tile['value'])
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
    'placeholder' => 'বিল নং, ক্রেতার নাম বা ফোন...',
    'selects'     => [
        ['name' => 'status', 'label' => 'সব অবস্থা', 'options' => [
            'paid' => 'পরিশোধিত', 'pending' => 'বাকি', 'partial' => 'আংশিক',
        ]],
    ],
])

@include('admin.partials.data-table', [
    'rows'      => $bills,
    'empty'     => 'কোনো অর্ডার পাওয়া যায়নি',
    'emptyHint' => 'সেলাররা বিল তৈরি করলে সেগুলো এখানে দেখা যাবে।',
    'emptyIcon' => 'receipt',
    'columns'   => [
        ['key' => 'bill_no',        'label' => 'বিল নং', 'type' => 'strong'],
        ['key' => 'customer_name',  'label' => 'ক্রেতা', 'type' => 'strong', 'sub' => 'customer_phone'],
        ['key' => 'seller.name',    'label' => 'সেলার',  'type' => 'muted'],
        ['key' => 'payment_method', 'label' => 'পেমেন্ট', 'type' => 'muted'],
        ['key' => 'total',          'label' => 'মোট',    'type' => 'money', 'align' => 'text-end'],
        ['key' => 'payment_status', 'label' => 'অবস্থা', 'type' => 'pill', 'map' => [
            'paid'    => ['পরিশোধিত', 'ok'],
            'pending' => ['বাকি', 'pending'],
            'partial' => ['আংশিক', 'info'],
        ]],
        ['key' => 'created_at',     'label' => 'তারিখ',  'type' => 'date'],
    ],
])

@endsection
