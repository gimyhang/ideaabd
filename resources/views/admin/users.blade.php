@extends('layouts.admin')

@section('title', 'ব্যবহারকারী')
@section('heading', 'ব্যবহারকারী পরিচালনা')
@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">ব্যবহারকারী</li>
@endsection

@section('actions')
    <a href="{{ route('admin.sub-admins.create') }}" class="btn btn-primary">
        <i class="fas fa-user-plus me-1"></i> নতুন অ্যাকাউন্ট
    </a>
@endsection

@section('content')

@php
    $roleNames = [
        'admin' => 'অ্যাডমিন', 'sub_admin' => 'সাব-অ্যাডমিন', 'seller' => 'সেলার',
        'publisher' => 'প্রকাশক', 'author' => 'লেখক', 'buyer' => 'ক্রেতা', 'customer' => 'গ্রাহক',
    ];
@endphp

{{-- Role summary chips --}}
<div class="d-flex flex-wrap gap-2 mb-3">
    <a href="{{ route('admin.users') }}" class="btn btn-sm {{ request('role') ? 'btn-outline-primary' : 'btn-primary' }}">
        সব <span class="badge bg-white text-primary ms-1">@bn(array_sum($roleCounts))</span>
    </a>
    @foreach ($roleCounts as $role => $count)
        <a href="{{ route('admin.users', ['role' => $role]) }}"
           class="btn btn-sm {{ request('role') === $role ? 'btn-primary' : 'btn-outline-secondary' }}">
            {{ $roleNames[$role] ?? $role }} <span class="badge bg-light text-dark ms-1">@bn($count)</span>
        </a>
    @endforeach
</div>

@include('admin.partials.filters', [
    'action'      => route('admin.users'),
    'placeholder' => 'নাম, ইমেইল বা ফোন দিয়ে খুঁজুন...',
    'selects'     => [
        ['name' => 'role', 'label' => 'সব ভূমিকা', 'options' => $roleNames],
    ],
])

@include('admin.partials.data-table', [
    'rows'      => $users,
    'empty'     => 'কোনো ব্যবহারকারী পাওয়া যায়নি',
    'emptyIcon' => 'users-slash',
    'columns'   => [
        ['key' => 'id',         'label' => '#',            'type' => 'index'],
        ['key' => 'name',       'label' => 'নাম',           'type' => 'strong', 'sub' => 'email'],
        ['key' => 'phone',      'label' => 'ফোন',           'type' => 'muted'],
        ['key' => 'role',       'label' => 'ভূমিকা',        'type' => 'pill', 'map' => [
            'admin'     => ['অ্যাডমিন', 'danger'],
            'sub_admin' => ['সাব-অ্যাডমিন', 'info'],
            'seller'    => ['সেলার', 'info'],
            'publisher' => ['প্রকাশক', 'muted'],
            'author'    => ['লেখক', 'muted'],
            'buyer'     => ['ক্রেতা', 'ok'],
            'customer'  => ['গ্রাহক', 'ok'],
        ]],
        ['key' => 'reg_status', 'label' => 'রেজিস্ট্রেশন',  'type' => 'pill', 'map' => [
            'pending'  => ['অপেক্ষমান', 'pending'],
            'approved' => ['অনুমোদিত', 'ok'],
            'rejected' => ['প্রত্যাখ্যাত', 'danger'],
        ]],
        ['key' => 'is_active',  'label' => 'অবস্থা',        'type' => 'bool'],
        ['key' => 'created_at', 'label' => 'যোগদান',        'type' => 'date'],
    ],
])

@endsection
