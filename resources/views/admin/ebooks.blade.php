@extends('layouts.admin')

@section('title', 'ই-বুক')
@section('heading', 'ই-বুক পরিচালনা')
@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">ই-বুক</li>
@endsection

@section('actions')
    <a href="{{ route('admin.content.create', 'ebooks') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i> নতুন ই-বুক যোগ করুন
    </a>
    <a href="{{ route('ebook.index') }}" target="_blank" rel="noopener" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-up-right-from-square me-1"></i> সাইটে দেখুন
    </a>
@endsection

@section('content')

@include('admin.partials.filters', [
    'action'      => route('admin.ebooks'),
    'placeholder' => 'ই-বুকের নাম দিয়ে খুঁজুন...',
])

@include('admin.partials.data-table', [
    'contentType' => 'ebooks',
    'rows'      => $ebooks,
    'empty'     => 'কোনো ই-বুক পাওয়া যায়নি',
    'emptyHint' => 'ই-বুকের টেবিল তৈরি না হলে এই তালিকা খালি থাকবে — মাইগ্রেশন চালান।',
    'emptyIcon' => 'tablet-screen-button',
    'columns'   => [
        ['key' => 'id',         'label' => '#',        'type' => 'index'],
        ['key' => 'title',      'label' => 'শিরোনাম',  'type' => 'strong', 'sub' => 'slug'],
        ['key' => 'price',      'label' => 'দাম',      'type' => 'money', 'align' => 'text-end'],
        ['key' => 'file_size',  'label' => 'ফাইল',     'type' => 'muted'],
        ['key' => 'is_active',  'label' => 'অবস্থা',   'type' => 'bool'],
        ['key' => 'created_at', 'label' => 'যোগ',      'type' => 'date'],
    ],
])

@endsection
