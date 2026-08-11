@extends('layouts.admin')

@section('title', 'ওয়েবজিন')
@section('heading', 'ওয়েবজিন')
@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">ওয়েবজিন</li>
@endsection

@section('actions')
    <a href="{{ route('webzine.index') }}" target="_blank" rel="noopener" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-up-right-from-square me-1"></i> সাইটে দেখুন
    </a>
@endsection

@section('content')

@include('admin.partials.filters', [
    'action'      => route('admin.webzines'),
    'placeholder' => 'ওয়েবজিনের শিরোনাম দিয়ে খুঁজুন...',
])

@include('admin.partials.data-table', [
    'rows'      => $webzines,
    'empty'     => 'কোনো ওয়েবজিন নেই',
    'emptyIcon' => 'newspaper',
    'columns'   => [
        ['key' => 'id',               'label' => '#',        'type' => 'index'],
        ['key' => 'title',            'label' => 'শিরোনাম',  'type' => 'strong', 'sub' => 'slug'],
        ['key' => 'issue_number',     'label' => 'সংখ্যা',    'type' => 'muted'],
        ['key' => 'category',         'label' => 'বিভাগ',    'type' => 'muted'],
        ['key' => 'is_published',     'label' => 'অবস্থা',   'type' => 'bool', 'on' => 'প্রকাশিত', 'off' => 'খসড়া'],
        ['key' => 'view_count',       'label' => 'ভিউ',      'type' => 'muted', 'align' => 'text-end'],
        ['key' => 'publication_date', 'label' => 'প্রকাশকাল', 'type' => 'date'],
    ],
])

@endsection
