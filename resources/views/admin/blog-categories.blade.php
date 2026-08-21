@extends('layouts.admin')

@section('title', 'Blog & Literature Categories')
@section('heading', 'Blog & Literature Categories Management')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.blog') }}">Articles</a></li>
    <li class="breadcrumb-item active" aria-current="page">Blog Categories</li>
@endsection

@section('actions')
    <a href="{{ route('admin.content.create', 'blog_categories') }}" class="btn btn-primary rounded-pill px-3 shadow-xs">
        <i class="fas fa-plus me-1"></i> Create Category
    </a>
    <a href="{{ route('admin.blog') }}" class="btn btn-outline-secondary rounded-pill px-3">
        <i class="fas fa-blog me-1"></i> View All Posts
    </a>
@endsection

@section('content')

{{-- Stats Row --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4">
        <div class="adm-card bg-white rounded-4 shadow-sm border-0 p-3 d-flex align-items-center gap-3">
            <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; font-size: 1.35rem;">
                <i class="fas fa-shapes"></i>
            </div>
            <div>
                <div class="small text-muted fw-semibold">Total Categories</div>
                <div class="fs-4 fw-bold text-dark">{{ number_format($stats['total']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4">
        <div class="adm-card bg-white rounded-4 shadow-sm border-0 p-3 d-flex align-items-center gap-3">
            <div class="rounded-circle bg-success-subtle text-success d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; font-size: 1.35rem;">
                <i class="fas fa-check-circle"></i>
            </div>
            <div>
                <div class="small text-muted fw-semibold">Active Categories</div>
                <div class="fs-4 fw-bold text-success">{{ number_format($stats['active']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="adm-card bg-white rounded-4 shadow-sm border-0 p-3 d-flex align-items-center gap-3">
            <div class="rounded-circle bg-info-subtle text-info d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; font-size: 1.35rem;">
                <i class="fas fa-feather-pointed"></i>
            </div>
            <div>
                <div class="small text-muted fw-semibold">Literary Portal</div>
                <div class="fs-6 fw-bold text-info">
                    <a href="{{ route('blog.index') }}" target="_blank" class="text-decoration-none">Visit Portal <i class="fas fa-external-link-alt ms-1"></i></a>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="adm-card bg-white rounded-4 shadow-sm border-0 mb-4 p-3">
    <form method="GET" action="{{ route('admin.blog-categories') }}" class="row g-2 align-items-center">
        <div class="col-md-9">
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                <input type="text" name="search" class="form-control" placeholder="Search by category name or slug..." value="{{ $search }}">
            </div>
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary flex-grow-1 rounded-pill"><i class="fas fa-filter me-1"></i> Filter</button>
            @if($search)
                <a href="{{ route('admin.blog-categories') }}" class="btn btn-outline-secondary rounded-pill" title="Reset"><i class="fas fa-rotate-left"></i></a>
            @endif
        </div>
    </form>
</div>

{{-- Categories Table --}}
<div class="adm-card bg-white rounded-4 shadow-sm border-0 p-0 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-3" style="width: 70px;">Icon / Image</th>
                    <th>Category Name</th>
                    <th>Slug</th>
                    <th>Posts Count</th>
                    <th>Status</th>
                    <th class="text-end pe-3" style="width: 160px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $category)
                    <tr>
                        <td class="ps-3">
                            <div class="rounded-3 overflow-hidden d-flex align-items-center justify-content-center bg-light border shadow-xs" style="width: 44px; height: 44px;">
                                @if($category->image)
                                    <img src="{{ str_starts_with($category->image, 'http') ? $category->image : asset('storage/' . $category->image) }}" 
                                         alt="{{ $category->name }}" class="w-100 h-100 object-fit-cover">
                                @elseif($category->icon)
                                    <i class="fa-solid fa-{{ $category->icon }} text-primary fs-5"></i>
                                @else
                                    <i class="fa-solid fa-shapes text-muted fs-5"></i>
                                @endif
                            </div>
                        </td>
                        <td>
                            <span class="fw-bold text-dark d-block">{{ $category->name }}</span>
                            @if($category->description)
                                <small class="text-muted text-truncate d-block" style="max-width: 280px;">{{ $category->description }}</small>
                            @endif
                        </td>
                        <td>
                            <code class="text-primary small">{{ $category->slug }}</code>
                        </td>
                        <td>
                            <a href="{{ route('admin.blog', ['category' => $category->slug]) }}" class="badge bg-primary-subtle text-primary border border-primary-subtle text-decoration-none px-2.5 py-1 rounded-pill">
                                {{ number_format($category->posts_count ?? 0) }} posts
                            </a>
                        </td>
                        <td>
                            @if($category->is_active)
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">Active</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill">Inactive</span>
                            @endif
                        </td>
                        <td class="text-end pe-3">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('blog.category', $category->slug) }}" target="_blank" class="btn btn-outline-secondary" title="View on Site">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.content.edit', ['type' => 'blog_categories', 'id' => $category->id]) }}" class="btn btn-outline-primary" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.content.destroy', ['type' => 'blog_categories', 'id' => $category->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this category?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="fas fa-folder-open fs-2 mb-2 d-block opacity-50"></i>
                            No categories found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($categories->hasPages())
        <div class="p-3 border-top d-flex justify-content-center bg-light">
            {{ $categories->links() }}
        </div>
    @endif
</div>

@endsection
