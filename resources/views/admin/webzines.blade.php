@extends('layouts.admin')

@section('title', 'Webzines & Periodicals')
@section('heading', 'Webzines & Literary Editions')
@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Webzines</li>
@endsection

@section('actions')
    <a href="{{ route('admin.content.create', 'webzines') }}" class="btn btn-primary rounded-pill px-3 shadow-xs">
        <i class="fas fa-plus me-1"></i> Add New Webzine
    </a>
    <a href="{{ route('webzine.index') }}" target="_blank" rel="noopener" class="btn btn-outline-secondary rounded-pill px-3">
        <i class="fas fa-arrow-up-right-from-square me-1"></i> View on Website
    </a>
@endsection

@section('content')

@include('admin.partials.filters', [
    'action'      => route('admin.webzines'),
    'placeholder' => 'Search by webzine title...',
])

@include('admin.partials.data-table', [
    'contentType' => 'webzines',
    'rows'      => $webzines,
    'empty'     => 'No webzines found',
    'emptyIcon' => 'newspaper',
    'columns'   => [
        ['key' => 'id',               'label' => '#',             'type' => 'index'],
        ['key' => 'title',            'label' => 'Title',         'type' => 'strong', 'sub' => 'slug'],
        ['key' => 'issue_number',     'label' => 'Issue No.',     'type' => 'muted'],
        ['key' => 'category',         'label' => 'Section',       'type' => 'muted'],
        ['key' => 'is_published',     'label' => 'Status',        'type' => 'bool', 'on' => 'Published', 'off' => 'Draft'],
        ['key' => 'view_count',       'label' => 'Views',         'type' => 'muted', 'align' => 'text-end'],
        ['key' => 'publication_date', 'label' => 'Publish Date',  'type' => 'date'],
    ],
])

@endsection
