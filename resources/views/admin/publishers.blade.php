@extends('layouts.admin')

@section('title', 'প্রকাশক')
@section('heading', 'প্রকাশক পরিচালনা')
@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">প্রকাশক</li>
@endsection

@section('actions')
    <a href="{{ route('publishers.index') }}" target="_blank" rel="noopener" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-up-right-from-square me-1"></i> সাইটে দেখুন
    </a>
@endsection

@section('content')

@include('admin.partials.filters', [
    'action'      => route('admin.publishers'),
    'placeholder' => 'প্রকাশকের নাম দিয়ে খুঁজুন...',
])

@include('admin.partials.data-table', [
    'rows'      => $publishers,
    'empty'     => 'কোনো প্রকাশক পাওয়া যায়নি',
    'emptyHint' => 'প্রকাশকের টেবিল তৈরি না হলে এই তালিকা খালি থাকবে — মাইগ্রেশন চালান।',
    'emptyIcon' => 'building',
    'columns'   => [
        ['key' => 'id',         'label' => '#',       'type' => 'index'],
        ['key' => 'name',       'label' => 'নাম',      'type' => 'strong', 'sub' => 'slug'],
        ['key' => 'email',      'label' => 'ইমেইল',   'type' => 'muted'],
        ['key' => 'phone',      'label' => 'ফোন',     'type' => 'muted'],
        ['key' => 'is_active',  'label' => 'অবস্থা',  'type' => 'bool'],
        ['key' => 'created_at', 'label' => 'যোগ',     'type' => 'date'],
    ],
])

@endsection
