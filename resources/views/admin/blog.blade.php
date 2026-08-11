@extends('layouts.admin')

@section('title', 'ব্লগ')
@section('heading', 'ব্লগ পোস্ট')
@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">ব্লগ</li>
@endsection

@section('actions')
    <a href="{{ route('blog.index') }}" target="_blank" rel="noopener" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-up-right-from-square me-1"></i> সাইটে দেখুন
    </a>
@endsection

@section('content')

@include('admin.partials.filters', [
    'action'      => route('admin.blog'),
    'placeholder' => 'পোস্টের শিরোনাম বা slug দিয়ে খুঁজুন...',
])

@include('admin.partials.data-table', [
    'rows'      => $posts,
    'empty'     => 'কোনো ব্লগ পোস্ট নেই',
    'emptyIcon' => 'blog',
    'columns'   => [
        ['key' => 'id',           'label' => '#',          'type' => 'index'],
        ['key' => 'title',        'label' => 'শিরোনাম',    'type' => 'strong', 'sub' => 'slug'],
        ['key' => 'status',       'label' => 'অবস্থা',     'type' => 'pill', 'map' => [
            'published' => ['প্রকাশিত', 'ok'],
            'draft'     => ['খসড়া', 'pending'],
            'archived'  => ['আর্কাইভ', 'muted'],
        ]],
        ['key' => 'is_featured',  'label' => 'ফিচার্ড',    'type' => 'bool', 'on' => 'হ্যাঁ', 'off' => 'না'],
        ['key' => 'view_count',   'label' => 'ভিউ',        'type' => 'muted', 'align' => 'text-end'],
        ['key' => 'published_at', 'label' => 'প্রকাশকাল',  'type' => 'date'],
    ],
])

@endsection
