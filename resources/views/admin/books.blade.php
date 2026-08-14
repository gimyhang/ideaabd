@extends('layouts.admin')

@section('title', 'বই')
@section('heading', 'বই পরিচালনা')
@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">বই</li>
@endsection

@section('actions')
    <a href="{{ route('admin.content.create', 'books') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i> নতুন বই যোগ করুন
    </a>
    <a href="{{ route('book.index') }}" target="_blank" rel="noopener" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-up-right-from-square me-1"></i> সাইটে দেখুন
    </a>
@endsection

@section('content')

@include('admin.partials.filters', [
    'action'      => route('admin.books'),
    'placeholder' => 'বইয়ের নাম বা slug দিয়ে খুঁজুন...',
])

@include('admin.partials.data-table', [
    'contentType' => 'books',
    'rows'      => $books,
    'empty'     => 'কোনো বই পাওয়া যায়নি',
    'emptyHint' => 'বইয়ের টেবিল তৈরি না হলে এই তালিকা খালি থাকবে — মাইগ্রেশন চালান।',
    'emptyIcon' => 'book',
    'columns'   => [
        ['key' => 'id',         'label' => '#',        'type' => 'index'],
        ['key' => 'title',      'label' => 'বইয়ের নাম', 'type' => 'strong', 'sub' => 'slug'],
        ['key' => 'isbn',       'label' => 'ISBN',     'type' => 'muted'],
        ['key' => 'price',      'label' => 'দাম',      'type' => 'money', 'align' => 'text-end'],
        ['key' => 'stock',      'label' => 'স্টক',      'type' => 'muted', 'align' => 'text-end'],
        ['key' => 'is_active',  'label' => 'অবস্থা',   'type' => 'bool'],
        ['key' => 'created_at', 'label' => 'যোগ',      'type' => 'date'],
    ],
])

@endsection
