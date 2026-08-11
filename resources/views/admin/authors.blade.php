@extends('layouts.admin')

@section('title', 'লেখক')
@section('heading', 'লেখক পরিচালনা')
@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">লেখক</li>
@endsection

@section('actions')
    <a href="{{ route('authors.index') }}" target="_blank" rel="noopener" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-up-right-from-square me-1"></i> সাইটে দেখুন
    </a>
@endsection

@section('content')

@include('admin.partials.filters', [
    'action'      => route('admin.authors'),
    'placeholder' => 'লেখকের নাম দিয়ে খুঁজুন...',
])

@include('admin.partials.data-table', [
    'rows'      => $authors,
    'empty'     => 'কোনো লেখক পাওয়া যায়নি',
    'emptyHint' => 'লেখকের টেবিল তৈরি না হলে এই তালিকা খালি থাকবে — মাইগ্রেশন চালান।',
    'emptyIcon' => 'pen-fancy',
    'columns'   => [
        ['key' => 'id',         'label' => '#',       'type' => 'index'],
        ['key' => 'name',       'label' => 'নাম',      'type' => 'strong', 'sub' => 'slug'],
        ['key' => 'email',      'label' => 'ইমেইল',   'type' => 'muted'],
        ['key' => 'is_active',  'label' => 'অবস্থা',  'type' => 'bool'],
        ['key' => 'created_at', 'label' => 'যোগ',     'type' => 'date'],
    ],
])

@endsection
