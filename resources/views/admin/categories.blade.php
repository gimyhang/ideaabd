@extends('layouts.admin')

@section('title', 'Book Categories')
@section('heading', 'Category Management')
@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Categories</li>
@endsection

@section('actions')
    <a href="{{ route('admin.content.create', 'categories') }}" class="btn btn-primary rounded-pill px-3 shadow-xs fw-semibold">
        <i class="fas fa-plus me-1"></i> Create New Category
    </a>
@endsection

@section('content')

{{-- Stats Row --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="adm-card p-3 d-flex align-items-center gap-3 bg-white rounded-4 shadow-sm border-0">
            <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 46px; height: 46px; font-size: 1.25rem;">
                <i class="fas fa-folder-tree"></i>
            </div>
            <div>
                <div class="small text-muted fw-semibold">Total Categories</div>
                <div class="fs-4 fw-bold text-dark">{{ number_format($stats['total']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="adm-card p-3 d-flex align-items-center gap-3 bg-white rounded-4 shadow-sm border-0">
            <div class="rounded-circle bg-success-subtle text-success d-flex align-items-center justify-content-center" style="width: 46px; height: 46px; font-size: 1.25rem;">
                <i class="fas fa-check-circle"></i>
            </div>
            <div>
                <div class="small text-muted fw-semibold">Active Categories</div>
                <div class="fs-4 fw-bold text-success">{{ number_format($stats['active']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="adm-card p-3 d-flex align-items-center gap-3 bg-white rounded-4 shadow-sm border-0">
            <div class="rounded-circle bg-info-subtle text-info d-flex align-items-center justify-content-center" style="width: 46px; height: 46px; font-size: 1.25rem;">
                <i class="fas fa-folder"></i>
            </div>
            <div>
                <div class="small text-muted fw-semibold">Parent Categories</div>
                <div class="fs-4 fw-bold text-info">{{ number_format($stats['parents']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="adm-card p-3 d-flex align-items-center gap-3 bg-white rounded-4 shadow-sm border-0">
            <div class="rounded-circle bg-warning-subtle text-warning d-flex align-items-center justify-content-center" style="width: 46px; height: 46px; font-size: 1.25rem;">
                <i class="fas fa-turn-down"></i>
            </div>
            <div>
                <div class="small text-muted fw-semibold">Sub-Categories</div>
                <div class="fs-4 fw-bold text-warning">{{ number_format($stats['children']) }}</div>
            </div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="adm-card p-3 mb-4 bg-white rounded-4 shadow-sm border-0">
    <form method="GET" action="{{ route('admin.categories') }}" class="row g-2 align-items-center">
        <div class="col-12 col-md-5">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                <input type="text" name="search" class="form-control border-start-0" placeholder="Search by category name..." value="{{ request('search') }}">
            </div>
        </div>
        <div class="col-6 col-md-3">
            <select name="parent_id" class="form-select">
                <option value="">— All Levels —</option>
                <option value="root" @selected(request('parent_id') === 'root')>Parent Categories Only</option>
                @foreach ($parentCategories as $pId => $pName)
                    <option value="{{ $pId }}" @selected((string)request('parent_id') === (string)$pId)>
                        Sub: {{ $pName }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-md-2">
            <select name="is_active" class="form-select">
                <option value="">— All Statuses —</option>
                <option value="1" @selected(request('is_active') === '1')>Active</option>
                <option value="0" @selected(request('is_active') === '0')>Inactive</option>
            </select>
        </div>
        <div class="col-12 col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-primary w-100 rounded-3"><i class="fas fa-filter me-1"></i> Filter</button>
            @if(request()->hasAny(['search', 'parent_id', 'is_active']))
                <a href="{{ route('admin.categories') }}" class="btn btn-outline-secondary rounded-3" title="Reset"><i class="fas fa-rotate-left"></i></a>
            @endif
        </div>
    </form>
</div>

{{-- Table --}}
<div class="adm-card p-0 overflow-hidden bg-white rounded-4 shadow-sm border-0">
    <div class="table-responsive mb-0">
        <table class="table table-hover align-middle mb-0" style="font-size: 0.92rem;">
            <thead class="table-light">
                <tr>
                    <th class="py-3 px-3" style="width: 60px;">#</th>
                    <th class="py-3 px-3">Category Name & Slug</th>
                    <th class="py-3 px-3">Parent Category</th>
                    <th class="py-3 px-3 text-center">Linked Books</th>
                    <th class="py-3 px-3 text-center">Sort Order</th>
                    <th class="py-3 px-3 text-center">Status</th>
                    <th class="py-3 px-3 text-end" style="width: 140px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $cat)
                    <tr>
                        <td class="px-3 text-muted">{{ $loop->iteration + ($categories->currentPage() - 1) * $categories->perPage() }}</td>
                        <td class="px-3">
                            <div class="fw-bold text-dark">
                                @if($cat->parent_id)
                                    <i class="fas fa-turn-down text-muted me-1 small"></i>
                                @else
                                    <i class="fas fa-folder text-primary me-1"></i>
                                @endif
                                {{ $cat->name }}
                            </div>
                            <small class="text-muted font-monospace">{{ $cat->slug }}</small>
                            @if($cat->description)
                                <div class="text-muted small text-truncate" style="max-width: 350px;">{{ $cat->description }}</div>
                            @endif
                        </td>
                        <td class="px-3">
                            @if($cat->parent)
                                <span class="badge bg-light text-dark border">
                                    <i class="fas fa-folder-open text-primary me-1"></i> {{ $cat->parent->name }}
                                </span>
                            @else
                                <span class="badge bg-primary-subtle text-primary border">Root Category</span>
                            @endif
                        </td>
                        <td class="px-3 text-center">
                            <span class="badge bg-secondary-subtle text-secondary fw-semibold">
                                {{ number_format($cat->books_count ?? 0) }} books
                            </span>
                        </td>
                        <td class="px-3 text-center text-muted">{{ $cat->sort_order ?? 0 }}</td>
                        <td class="px-3 text-center">
                            @if($cat->is_active)
                                <span class="badge bg-success-subtle text-success border border-success-subtle">Active</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Inactive</span>
                            @endif
                        </td>
                        <td class="px-3 text-end">
                            <div class="d-inline-flex gap-1">
                                <a href="{{ route('admin.content.edit', ['type' => 'categories', 'id' => $cat->id]) }}"
                                   class="btn btn-sm btn-outline-primary py-1 px-2" title="Edit">
                                    <i class="fas fa-pen-to-square"></i>
                                </a>
                                <form method="POST" action="{{ route('admin.content.destroy', ['type' => 'categories', 'id' => $cat->id]) }}"
                                      onsubmit="return confirm('Are you sure you want to delete this category?');" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger py-1 px-2" title="Delete">
                                        <i class="fas fa-trash-can"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="fas fa-folder-open fs-2 mb-2 d-block text-muted opacity-50"></i>
                            No categories found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($categories->hasPages())
        <div class="p-3 border-top bg-white">
            {{ $categories->links() }}
        </div>
    @endif
</div>

@endsection
