@extends('layouts.admin')

@section('title', 'Ideapatra & Blog Posts Management')
@section('heading', 'Ideapatra — Blog & Content Moderation')
@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Ideapatra & Blog</li>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css" />
<style>
    .banner-hero-card {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        border: 1px solid rgba(255, 255, 255, 0.1);
        color: #fff;
    }
    .banner-thumb-box {
        width: 100%;
        max-width: 220px;
        aspect-ratio: 16 / 9;
        border-radius: 12px;
        overflow: hidden;
        background: #020617;
        position: relative;
        border: 1px solid rgba(255, 255, 255, 0.2);
    }
    .banner-thumb-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }
    .banner-thumb-box:hover img {
        transform: scale(1.05);
    }
    .btn-featured-star {
        background: none;
        border: none;
        padding: 0;
        font-size: 1.15rem;
        cursor: pointer;
        color: #cbd5e1;
        transition: all 0.2s ease;
    }
    .btn-featured-star.active {
        color: #f59e0b;
        transform: scale(1.1);
    }
    .status-select-badge {
        font-size: 0.82rem;
        font-weight: 600;
        border-radius: 50rem;
        padding: 0.25rem 0.65rem;
        border: 1px solid transparent;
        cursor: pointer;
    }
    .status-select-badge:focus {
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);
    }
    .cropper-view-box, .cropper-face {
        border-radius: 8px;
    }
    .hover-lift {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .hover-lift:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1) !important;
    }
    @keyframes rowApprovedPulse {
        0% { background-color: rgba(34, 197, 94, 0.28); }
        50% { background-color: rgba(34, 197, 94, 0.45); }
        100% { background-color: transparent; }
    }
    .row-approved-flash {
        animation: rowApprovedPulse 2s ease-in-out;
    }
    .btn-approve-action {
        transition: all 0.2s ease-in-out;
    }
    .btn-approve-action:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(22, 163, 74, 0.35) !important;
    }
    .adm-actions-wrap {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: flex-end !important;
        gap: 5px !important;
        white-space: nowrap !important;
        flex-wrap: nowrap !important;
    }
    .adm-action-btn {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        white-space: nowrap !important;
        flex-shrink: 0 !important;
        font-size: 0.78rem !important;
        font-weight: 600 !important;
        line-height: 1 !important;
        padding: 5px 9px !important;
        height: 30px !important;
        text-decoration: none !important;
        border-radius: 50rem !important;
        transition: all 0.15s ease-in-out !important;
    }
    .adm-action-btn i {
        font-size: 0.78rem;
        line-height: 1;
    }
    .adm-action-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 3px 8px rgba(0,0,0,0.12) !important;
    }
    .adm-action-btn-icon {
        width: 30px !important;
        padding: 0 !important;
    }
</style>
@endpush

@section('actions')
    <button type="button" class="btn btn-primary rounded-pill px-3 shadow-xs fw-semibold" data-bs-toggle="modal" data-bs-target="#blogCustomizerModal">
        <i class="fas fa-palette me-1.5"></i> Design Customizer
    </button>
    <button type="button" class="btn btn-outline-success rounded-pill px-3 shadow-xs fw-semibold" data-bs-toggle="modal" data-bs-target="#bulkTypographyModal">
        <i class="fas fa-wand-magic-sparkles me-1.5"></i> Typography Engine
    </button>
    <a href="{{ route('admin.blog-categories') }}" class="btn btn-outline-secondary rounded-pill px-3 shadow-xs">
        <i class="fas fa-shapes me-1"></i> Categories
    </a>
    <a href="{{ route('admin.content.create', 'blog') }}" class="btn btn-dark rounded-pill px-3 shadow-xs fw-semibold">
        <i class="fas fa-plus me-1"></i> New Post
    </a>
    <a href="{{ route('blog.index') }}" target="_blank" rel="noopener" class="btn btn-outline-primary rounded-pill px-3 shadow-xs">
        <i class="fas fa-arrow-up-right-from-square me-1"></i> View Blog
    </a>
@endsection

@section('content')

{{-- Flash Alert Messages --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-3 rounded-4 shadow-sm" role="alert">
        <i class="fas fa-circle-check fs-5 me-2.5 text-success"></i>
        <div class="fw-semibold">{{ session('success') }}</div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center mb-3 rounded-4 shadow-sm" role="alert">
        <i class="fas fa-circle-exclamation fs-5 me-2.5 text-danger"></i>
        <div class="fw-semibold">{{ session('error') }}</div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

{{-- 1. Dynamic Hero Masthead & Management Console --}}
<div class="card banner-hero-card border-0 shadow rounded-4 mb-4 overflow-hidden position-relative">
    <div class="card-body p-4">
        <div class="row g-4 align-items-center">
            
            <!-- Left: Current Active Social & Header Banner -->
            <div class="col-lg-3 col-md-4 text-center text-md-start">
                <div class="d-inline-block position-relative">
                    <div class="banner-thumb-box shadow">
                        <img src="{{ $blogOgBannerUrl }}" alt="Active Ideapatra Banner" id="heroBannerThumb">
                        <div class="position-absolute bottom-0 start-0 end-0 p-1.5 text-center bg-dark bg-opacity-75" style="font-size: 10px; backdrop-filter: blur(4px);">
                            <i class="fas fa-share-nodes text-warning me-1"></i> Social & Blog Banner
                        </div>
                    </div>
                </div>
                <div class="mt-2">
                    <button type="button" class="btn btn-warning btn-sm rounded-pill px-3 py-1 fw-bold text-dark shadow-xs" 
                            data-bs-toggle="modal" data-bs-target="#blogCustomizerModal" onclick="switchToBannerTab()">
                        <i class="fas fa-crop-simple me-1"></i> Change Banner
                    </button>
                </div>
            </div>

            <!-- Center: Title, Typography & Live Status -->
            <div class="col-lg-6 col-md-8">
                <div class="d-flex align-items-center gap-2 mb-1.5 flex-wrap">
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1 fw-bold">
                        {{ $blogSettings['hero_badge'] ?? 'Literature, Culture, Research & Free Thought' }}
                    </span>
                    <span class="badge bg-white bg-opacity-10 text-white rounded-pill px-2.5 py-1">
                        <i class="fas fa-font me-1 text-warning"></i> Font: {{ explode(',', $blogSettings['font_family'] ?? '')[0] ?? 'Hind Siliguri' }}
                    </span>
                    <span class="badge bg-white bg-opacity-10 text-white rounded-pill px-2.5 py-1">
                        <i class="fas fa-arrows-up-down me-1 text-info"></i> Line Spacing: {{ $blogSettings['line_height'] ?? '1.6' }}
                    </span>
                </div>

                <h4 class="fw-bold mb-1.5 text-white">
                    {{ $blogSettings['hero_title'] ?? 'Ideapatra — Contemporary Literature & Thought' }}
                </h4>
                <p class="text-white-50 small mb-3 line-clamp-2" style="font-size: 0.88rem; max-width: 600px;">
                    {{ $blogSettings['hero_subtitle'] ?? 'An open digital magazine for contemporary literary discussions, essays, short stories, poems, book reviews and analytical research.' }}
                </p>

                <!-- Quick Action Pills -->
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <button type="button" class="btn btn-outline-light btn-sm rounded-pill px-3 shadow-xs" data-bs-toggle="modal" data-bs-target="#blogCustomizerModal">
                        <i class="fas fa-palette text-warning me-1"></i> Change Design
                    </button>
                    <button type="button" class="btn btn-outline-light btn-sm rounded-pill px-3 shadow-xs" data-bs-toggle="modal" data-bs-target="#bulkTypographyModal">
                        <i class="fas fa-wand-magic-sparkles text-success me-1"></i> Format Typography
                    </button>
                    <a href="{{ route('blog.index') }}" target="_blank" class="btn btn-outline-info btn-sm rounded-pill px-3 shadow-xs">
                        <i class="fas fa-external-link-alt me-1"></i> Live Ideapatra
                    </a>
                </div>
            </div>

            <!-- Right: Quick Stat Summary Ring -->
            <div class="col-lg-3 text-center text-lg-end">
                <div class="p-3 rounded-4 bg-white bg-opacity-10 border border-white border-opacity-10 d-inline-block text-start" style="min-width: 200px;">
                    <div class="d-flex justify-content-between align-items-center mb-1.5">
                        <span class="small text-white-50">Total Posts:</span>
                        <span class="fw-bold fs-6 text-white">{{ number_format($stats['total'] ?? 0) }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-1.5">
                        <span class="small text-success"><i class="fas fa-check-circle me-1"></i>Published:</span>
                        <span class="fw-bold text-success">{{ number_format($stats['published'] ?? 0) }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-1.5">
                        <span class="small text-warning"><i class="fas fa-clock me-1"></i>Pending:</span>
                        <span class="fw-bold text-warning">{{ number_format($stats['pending'] ?? 0) }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="small text-info"><i class="fas fa-star me-1 text-warning"></i>Featured:</span>
                        <span class="fw-bold text-info">{{ number_format($stats['featured'] ?? 0) }}</span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- 2. Stat Metric Cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <a href="{{ route('admin.blog') }}" class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-primary text-decoration-none hover-lift h-100">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold">Total Posts</span>
                    <h3 class="fw-bold mb-0 text-primary">{{ number_format($stats['total'] ?? 0) }}</h3>
                </div>
                <div class="rounded-circle bg-primary-subtle text-primary p-3"><i class="fas fa-blog fs-4"></i></div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="{{ route('admin.blog', ['status' => 'published']) }}" class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-success text-decoration-none hover-lift h-100">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold">Published (Approved)</span>
                    <h3 class="fw-bold mb-0 text-success">{{ number_format($stats['published'] ?? 0) }}</h3>
                </div>
                <div class="rounded-circle bg-success-subtle text-success p-3"><i class="fas fa-check-double fs-4"></i></div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="{{ route('admin.blog', ['status' => 'pending']) }}" class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-warning text-decoration-none hover-lift h-100">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold">Pending Review</span>
                    <h3 class="fw-bold mb-0 text-warning">{{ number_format($stats['pending'] ?? 0) }}</h3>
                </div>
                <div class="rounded-circle bg-warning-subtle text-warning p-3"><i class="fas fa-clock fs-4"></i></div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="{{ route('admin.blog', ['status' => 'featured']) }}" class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-info text-decoration-none hover-lift h-100">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold">Featured Posts</span>
                    <h3 class="fw-bold mb-0 text-info">{{ number_format($stats['featured'] ?? 0) }}</h3>
                </div>
                <div class="rounded-circle bg-info-subtle text-info p-3"><i class="fas fa-star fs-4 text-warning"></i></div>
            </div>
        </a>
    </div>
</div>

{{-- 3. Advanced Multi-Filter Bar --}}
<div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
    <div class="card-body p-3">
        <form action="{{ route('admin.blog') }}" method="GET" class="row g-2 align-items-center" id="blogFilterForm">
            <!-- Search Keyword -->
            <div class="col-lg-4 col-md-6">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="search" name="search" class="form-control border-start-0" 
                           placeholder="Search post title, subject, author or slug..." value="{{ request('search') }}">
                </div>
            </div>

            <!-- Category Filter -->
            <div class="col-lg-2 col-md-3 col-6">
                <select name="category" class="form-select" onchange="this.form.submit()">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->slug }}" @selected(request('category') == $cat->slug || request('category') == $cat->id)>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Status Filter -->
            <div class="col-lg-2 col-md-3 col-6">
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="all" @selected(request('status') === 'all' || !request('status'))>All Statuses</option>
                    <option value="published" @selected(request('status') === 'published')>Published</option>
                    <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                    <option value="featured" @selected(request('status') === 'featured')>Featured</option>
                    <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                    <option value="rejected" @selected(request('status') === 'rejected')>Rejected</option>
                </select>
            </div>

            <!-- Items Per Page -->
            <div class="col-lg-2 col-md-3 col-6">
                <select name="per_page" class="form-select" onchange="this.form.submit()">
                    <option value="20" @selected(($perPage ?? 20) == 20)>20 per page</option>
                    <option value="50" @selected(($perPage ?? 20) == 50)>50 per page</option>
                    <option value="100" @selected(($perPage ?? 20) == 100)>100 per page</option>
                </select>
            </div>

            <!-- Action Buttons -->
            <div class="col-lg-2 col-md-3 col-6 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100 rounded-3"><i class="fas fa-filter me-1"></i> Filter</button>
                @if(request()->hasAny(['search', 'status', 'category', 'is_featured', 'per_page']))
                    <a href="{{ route('admin.blog') }}" class="btn btn-light border rounded-3" title="Reset"><i class="fas fa-rotate-left"></i></a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- 4. Batch Actions Bar & Interactive Post Table --}}
<form action="{{ route('admin.blog.bulk-action') }}" method="POST" id="bulkActionForm">
    @csrf
    
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3 px-1">
        <div class="d-flex align-items-center gap-2">
            <div class="form-check me-2">
                <input class="form-check-input" type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAll(this)">
                <label class="form-check-label small fw-semibold text-muted" for="selectAllCheckbox">Select All</label>
            </div>
            
            <div class="input-group input-group-sm" style="max-width: 280px;">
                <select name="bulk_action" id="bulkActionSelect" class="form-select form-select-sm rounded-start-pill">
                    <option value="">Choose bulk action...</option>
                    <option value="publish">Publish & Approve Selected</option>
                    <option value="draft">Draft Selected</option>
                    <option value="delete">Delete Selected</option>
                </select>
                <button type="submit" class="btn btn-dark btn-sm rounded-end-pill px-3 fw-semibold" onclick="return confirmBulkAction()">
                    Apply
                </button>
            </div>
        </div>

        <div class="text-muted small">
            Showing {{ $posts->firstItem() ?? 0 }}–{{ $posts->lastItem() ?? 0 }} of {{ $posts->total() }} posts
        </div>
    </div>

    <div class="adm-card shadow-sm rounded-4 overflow-hidden mb-4 bg-white">
        @if ($posts->isEmpty())
            <div class="empty-state py-5 text-center">
                <i class="fas fa-newspaper fs-1 text-muted opacity-50 mb-3"></i>
                <h5 class="fw-bold text-muted">No Blog Posts Found</h5>
                <p class="text-muted small">Create a new post or try adjusting your search filters.</p>
                <a href="{{ route('admin.content.create', 'blog') }}" class="btn btn-primary btn-sm rounded-pill px-4">
                    <i class="fas fa-plus me-1"></i> Write New Post
                </a>
            </div>
        @else
            <div class="table-responsive">
                <table class="table adm-table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3" style="width: 40px;"></th>
                            <th style="width: 40px;">#</th>
                            <th style="width: 40px;" class="text-center" title="Featured Post">★</th>
                            <th style="min-width: 260px;">Post Title & Cover</th>
                            <th>Author / Submitter</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Views</th>
                            <th>Date</th>
                            <th class="text-end pe-3" style="min-width: 340px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($posts as $n => $post)
                            @php
                                $isPublished = ($post->status === 'published' || $post->mod_status === 'approved');
                                $isPending = ($post->status === 'pending' || $post->mod_status === 'pending');
                                $isRejected = ($post->status === 'rejected' || $post->mod_status === 'rejected');
                                $isDraft = ($post->status === 'draft');
                                $coverImg = $post->cover_url ?: ($post->featured_image ? (str_starts_with($post->featured_image, 'http') ? $post->featured_image : asset('storage/' . ltrim($post->featured_image, '/'))) : null);
                            @endphp
                            <tr id="postRow{{ $post->id }}">
                                <!-- Checkbox -->
                                <td class="ps-3">
                                    <input class="form-check-input row-checkbox" type="checkbox" name="selected_ids[]" value="{{ $post->id }}">
                                </td>

                                <!-- Row Number -->
                                <td class="text-muted small">{{ $posts->firstItem() + $n }}</td>

                                <!-- Featured Star Toggle -->
                                <td class="text-center">
                                    <button type="button" class="btn-featured-star {{ $post->is_featured ? 'active' : '' }}" 
                                            id="starBtn{{ $post->id }}"
                                            onclick="toggleFeatured({{ $post->id }})" 
                                            title="{{ $post->is_featured ? 'Featured post (Click to unfeature)' : 'Standard post (Click to feature)' }}">
                                        <i class="fa-solid fa-star"></i>
                                    </button>
                                </td>

                                <!-- Post Title & Thumbnail -->
                                <td>
                                    <div class="d-flex align-items-center gap-2.5">
                                        @if($coverImg)
                                            <img src="{{ $coverImg }}" alt="{{ $post->title }}" 
                                                 class="rounded-3 flex-shrink-0 shadow-xs object-fit-cover" 
                                                 style="width: 48px; height: 48px; border: 1px solid #e2e8f0;">
                                        @else
                                            <div class="rounded-3 bg-light d-flex align-items-center justify-content-center flex-shrink-0 text-muted" 
                                                 style="width: 48px; height: 48px; border: 1px solid #e2e8f0;">
                                                <i class="fas fa-file-lines opacity-50"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <div class="d-flex align-items-center gap-1.5 flex-wrap">
                                                <a href="{{ route('admin.content.edit', ['type' => 'blog', 'id' => $post->id]) }}" 
                                                   class="fw-bold text-dark text-decoration-none hover-primary d-block line-clamp-1" title="{{ $post->title }}">
                                                    {{ $post->title }}
                                                </a>
                                                @if($post->hasPendingEditRequest())
                                                    <span class="badge bg-warning text-dark rounded-pill px-2 py-0.5 small cursor-pointer shadow-xs animate-pulse" 
                                                          onclick="openBlogEditRequestModal({{ $post->id }})" title="লেখকের সংশোধনী আবেদন দেখতে ক্লিক করুন">
                                                        <i class="fas fa-code-compare me-1"></i>কারেকশন রিকোয়েস্ট
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="text-muted small" style="font-size: 0.78rem;">
                                                <span class="font-monospace text-muted">{{ $post->slug }}</span>
                                            </div>
                                            @if($isRejected && $post->rejection_reason)
                                                <div class="small text-danger mt-0.5" style="font-size: 0.76rem;">
                                                    <i class="fas fa-info-circle me-1"></i>{{ $post->rejection_reason }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    @if($post->hasPendingEditRequest())
                                        <script id="editReqData{{ $post->id }}" type="application/json">
                                        {!! json_encode([
                                            'id' => $post->id,
                                            'title' => $post->title,
                                            'subtitle' => $post->subtitle,
                                            'category' => $post->category?->name ?? 'General',
                                            'excerpt' => $post->excerpt,
                                            'content' => $post->content,
                                            'cover_url' => $coverImg,
                                            'edit_requested_at' => $post->edit_requested_at ? $post->edit_requested_at->format('d M Y, h:i A') : null,
                                            'edit_request_notes' => $post->edit_request_notes,
                                            'req_data' => $post->edit_request_data,
                                            'author_name' => $post->author?->name ?? $post->submitter?->name ?? $post->author_name ?? '—'
                                        ]) !!}
                                        </script>
                                    @endif
                                </td>

                                <!-- Author / Submitter -->
                                <td>
                                    <div class="fw-semibold text-dark">{{ $post->author?->name ?? $post->submitter?->name ?? $post->author_name ?? '—' }}</div>
                                    @if($post->author?->phone)
                                        <div class="text-muted small" style="font-size: 0.76rem;">
                                            <a href="https://wa.me/88{{ preg_replace('/[^0-9]/', '', $post->author->phone) }}" target="_blank" class="text-decoration-none text-muted">
                                                <i class="fab fa-whatsapp text-success me-1"></i>{{ $post->author->phone }}
                                            </a>
                                        </div>
                                    @endif
                                </td>

                                <!-- Category -->
                                <td>
                                    <span class="badge bg-light text-dark border rounded-pill px-2.5 py-1">
                                        {{ $post->category?->name ?? 'General' }}
                                    </span>
                                </td>

                                <!-- Dynamic Status Dropdown -->
                                <td>
                                    <select class="form-select form-select-sm status-select-badge 
                                            {{ $isPublished ? 'bg-success-subtle text-success border-success-subtle' : ($isPending ? 'bg-warning-subtle text-warning border-warning-subtle' : ($isRejected ? 'bg-danger-subtle text-danger border-danger-subtle' : 'bg-secondary-subtle text-secondary border-secondary-subtle')) }}" 
                                            id="statusSelect{{ $post->id }}"
                                            onchange="updatePostStatus({{ $post->id }}, this.value)">
                                        <option value="published" @selected($isPublished)>✓ Published</option>
                                        <option value="pending" @selected($isPending)>⏳ Pending</option>
                                        <option value="draft" @selected($isDraft)>📝 Draft</option>
                                        <option value="rejected" @selected($isRejected)>✕ Rejected</option>
                                    </select>
                                </td>

                                <!-- Views -->
                                <td>
                                    <span class="badge bg-light text-muted border rounded-pill px-2 py-1 font-monospace">
                                        <i class="fas fa-eye text-primary me-1"></i>{{ number_format($post->views_count ?? $post->view_count ?? 0) }}
                                    </span>
                                </td>

                                <!-- Date -->
                                <td class="text-muted small">
                                    {{ $post->published_at ? $post->published_at->format('d M, Y') : ($post->created_at ? $post->created_at->format('d M, Y') : '—') }}
                                </td>

                                <!-- All Action Buttons (View, Edit Request, Approve, Reject, Edit, Delete) -->
                                <td class="text-end pe-3 text-nowrap">
                                    <div class="adm-actions-wrap" id="postActions{{ $post->id }}" data-slug="{{ $post->slug }}">
                                        {{-- 1. View / Review Form Button --}}
                                        <a href="{{ route('admin.content.edit', ['type' => 'blog', 'id' => $post->id]) }}" 
                                           class="adm-action-btn btn btn-outline-info shadow-xs" id="viewBtn{{ $post->id }}" title="View & Edit / লেখা পর্যালোচনা ও এডিট করুন">
                                            <i class="fas fa-eye me-1"></i> View
                                        </a>

                                        {{-- 2. Pending Edit Request Review Button --}}
                                        @if($post->hasPendingEditRequest())
                                            <button type="button" class="adm-action-btn btn btn-warning text-dark fw-bold shadow-xs d-inline-flex align-items-center gap-1" 
                                                    onclick="openBlogEditRequestModal({{ $post->id }})" title="Review Correction / কারেকশন রিভিউ ও রিপ্লেস করুন">
                                                <i class="fas fa-code-compare"></i>
                                                <span>কারেকশন</span>
                                            </button>
                                        @endif

                                        {{-- 3. Approve Button --}}
                                        @if($isPublished)
                                            <button type="button" class="adm-action-btn btn btn-outline-success shadow-xs" 
                                                    id="approveBtn{{ $post->id }}"
                                                    onclick="updatePostStatus({{ $post->id }}, 'published', this)" title="Published (Click to re-approve)">
                                                <i class="fas fa-check-double me-1"></i> Approved
                                            </button>
                                        @else
                                            <button type="button" class="adm-action-btn btn btn-success shadow-xs btn-approve-action text-white" 
                                                    id="approveBtn{{ $post->id }}"
                                                    onclick="updatePostStatus({{ $post->id }}, 'published', this)" title="Approve & Publish Immediately">
                                                <i class="fas fa-circle-check me-1"></i> Approve
                                            </button>
                                        @endif

                                        {{-- 4. Reject Button --}}
                                        @if($isRejected)
                                            <button type="button" class="adm-action-btn btn btn-outline-danger shadow-xs" 
                                                    id="rejectBtn{{ $post->id }}"
                                                    onclick="openBlogRejectModal({{ $post->id }}, '{{ addslashes($post->title) }}')" title="Rejected (Click to edit reason)">
                                                <i class="fas fa-circle-xmark me-1"></i> Rejected
                                            </button>
                                        @else
                                            <button type="button" class="adm-action-btn btn btn-outline-danger shadow-xs" 
                                                    id="rejectBtn{{ $post->id }}"
                                                    onclick="openBlogRejectModal({{ $post->id }}, '{{ addslashes($post->title) }}')" title="Reject / Request Changes">
                                                <i class="fas fa-times me-1"></i> Reject
                                            </button>
                                        @endif

                                        {{-- 5. Edit Button --}}
                                        <a href="{{ route('admin.content.edit', ['type' => 'blog', 'id' => $post->id]) }}" 
                                           class="adm-action-btn btn btn-outline-primary shadow-xs" title="Edit Post">
                                            <i class="fas fa-pen-to-square me-1"></i> Edit
                                        </a>

                                        {{-- 6. Live Blog Link (if published) --}}
                                        @if($isPublished)
                                            <a href="{{ route('blog.show', $post->slug) }}" target="_blank" rel="noopener" 
                                               class="adm-action-btn btn btn-light border shadow-xs" title="View live on website">
                                                <i class="fas fa-arrow-up-right-from-square text-muted"></i>
                                            </a>
                                        @endif

                                        {{-- 7. Delete Button --}}
                                        <button type="button" class="adm-action-btn adm-action-btn-icon btn btn-outline-danger shadow-xs" 
                                                onclick="deletePost({{ $post->id }}, '{{ addslashes($post->title) }}')" title="Delete Post">
                                            <i class="fas fa-trash-can"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($posts->hasPages())
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 p-3 bg-white border-top">
                    <span class="text-muted small">
                        Showing {{ $posts->firstItem() }} to {{ $posts->lastItem() }} of {{ $posts->total() }} posts
                    </span>
                    {{ $posts->links() }}
                </div>
            @endif
        @endif
    </div>
</form>

{{-- ========================================================================= --}}
{{-- 5. BLOG DESIGN & BANNER CUSTOMIZER MODAL                                  --}}
{{-- ========================================================================= --}}
<div class="modal fade" id="blogCustomizerModal" tabindex="-1" aria-labelledby="blogCustomizerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header bg-dark text-white py-3 px-4">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="rounded-circle bg-primary bg-opacity-25 text-primary p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                        <i class="fas fa-palette text-white"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold fs-6 mb-0 text-white" id="blogCustomizerModalLabel">Ideapatra & Blog Design & Banner Customizer</h5>
                        <span class="small text-white-50" style="font-size: 0.78rem;">Configure social banner, header gradient, font, line spacing and reader layout</span>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{ route('admin.blog.settings.update') }}" method="POST" enctype="multipart/form-data" id="blogCustomizerForm">
                @csrf
                <div class="modal-body p-4 bg-light">
                    <div class="row g-4">
                        
                        <!-- Left Column: Controls & Tabs -->
                        <div class="col-lg-7">
                            
                            <!-- Customizer Navigation Tabs -->
                            <ul class="nav nav-pills nav-fill bg-white p-1.5 rounded-pill shadow-xs border mb-3" id="customizerTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active rounded-pill fw-semibold py-1.5 small" id="tab-banner-btn" data-bs-toggle="pill" data-bs-target="#tab-banner" type="button" role="tab">
                                        <i class="fas fa-image me-1 text-warning"></i> Banner & Header
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link rounded-pill fw-semibold py-1.5 small" id="tab-typography-btn" data-bs-toggle="pill" data-bs-target="#tab-typography" type="button" role="tab">
                                        <i class="fas fa-font me-1 text-primary"></i> Typography & Spacing
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link rounded-pill fw-semibold py-1.5 small" id="tab-reading-btn" data-bs-toggle="pill" data-bs-target="#tab-reading" type="button" role="tab">
                                        <i class="fas fa-book-open me-1 text-success"></i> Reader & Layout
                                    </button>
                                </li>
                            </ul>

                            <div class="tab-content" id="customizerTabsContent">
                                
                                <!-- Tab 1: Header & Social Media Open Graph Banner -->
                                <div class="tab-pane fade show active" id="tab-banner" role="tabpanel">
                                    <div class="card border-0 shadow-xs rounded-3 p-3.5 bg-white mb-3">
                                        <h6 class="fw-bold text-dark mb-3 pb-2 border-bottom d-flex align-items-center gap-2">
                                            <i class="fas fa-share-nodes text-primary"></i>
                                            <span>Social Media & Ideapatra Banner</span>
                                        </h6>

                                        <div class="row g-3">
                                            <!-- Current Banner Preview & Uploader -->
                                            <div class="col-12">
                                                <div class="p-3 bg-light rounded-4 border mb-3 text-center">
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <span class="badge bg-white text-dark border small fw-bold">
                                                            <i class="fas fa-crop-simple text-primary me-1"></i> 1200 × 630 px (16:9 Ratio)
                                                        </span>
                                                        <span class="small text-muted">Live cropper enabled</span>
                                                    </div>

                                                    <div class="banner-thumb-box mx-auto mb-2 shadow-xs" style="max-width: 320px; aspect-ratio: 16/9;">
                                                        <img src="{{ $blogOgBannerUrl }}" alt="Banner Preview" id="modalBannerPreview">
                                                    </div>

                                                    <div class="text-start mt-3">
                                                        <label class="form-label small fw-bold text-dark mb-1">Select Custom Banner & Crop:</label>
                                                        <input type="file" id="modalBannerInput" name="blog_og_banner" class="form-control rounded-3" accept="image/*" onchange="initBannerCropper(this)">
                                                        <input type="hidden" name="blog_og_banner_cropped" id="modalBannerCropped">
                                                        <div class="form-text small text-muted">Selecting an image file will automatically open the 16:9 crop window.</div>
                                                    </div>

                                                    <div class="form-check text-start mt-2">
                                                        <input class="form-check-input" type="checkbox" name="remove_blog_og_banner" value="1" id="rmBlogBannerModal">
                                                        <label class="form-check-label small text-danger fw-semibold" for="rmBlogBannerModal">Remove custom banner and use default</label>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Header Gradient Scheme -->
                                            <div class="col-12">
                                                <label class="form-label small fw-bold text-dark mb-1">Header Banner Gradient:</label>
                                                <select name="header_gradient" id="custHeaderGradient" class="form-select form-select-sm" onchange="updateLivePreview()">
                                                    <option value="linear-gradient(135deg, #0c4a6e 0%, #0369a1 50%, #0284c7 100%)" @selected(($blogSettings['header_gradient'] ?? '') == 'linear-gradient(135deg, #0c4a6e 0%, #0369a1 50%, #0284c7 100%)')>Ocean Blue Gradient</option>
                                                    <option value="linear-gradient(135deg, #064e3b 0%, #047857 50%, #059669 100%)" @selected(($blogSettings['header_gradient'] ?? '') == 'linear-gradient(135deg, #064e3b 0%, #047857 50%, #059669 100%)')>Emerald Green Gradient</option>
                                                    <option value="linear-gradient(135deg, #312e81 0%, #4338ca 50%, #6366f1 100%)" @selected(($blogSettings['header_gradient'] ?? '') == 'linear-gradient(135deg, #312e81 0%, #4338ca 50%, #6366f1 100%)')>Royal Indigo Gradient</option>
                                                    <option value="linear-gradient(135deg, #18181b 0%, #27272a 50%, #3f3f46 100%)" @selected(($blogSettings['header_gradient'] ?? '') == 'linear-gradient(135deg, #18181b 0%, #27272a 50%, #3f3f46 100%)')>Classic Dark Graphite</option>
                                                    <option value="linear-gradient(135deg, #881337 0%, #9f1239 50%, #be123c 100%)" @selected(($blogSettings['header_gradient'] ?? '') == 'linear-gradient(135deg, #881337 0%, #9f1239 50%, #be123c 100%)')>Deep Crimson Velvet</option>
                                                </select>
                                            </div>

                                            <!-- Hero Badge -->
                                            <div class="col-12">
                                                <label class="form-label small fw-bold text-dark mb-1">Top Badge Text:</label>
                                                <input type="text" name="hero_badge" id="custHeroBadge" class="form-control form-control-sm" 
                                                       value="{{ $blogSettings['hero_badge'] ?? 'Literature, Culture, Research & Free Thought' }}" oninput="updateLivePreview()">
                                            </div>

                                            <!-- Hero Title -->
                                            <div class="col-12">
                                                <label class="form-label small fw-bold text-dark mb-1">Hero Title:</label>
                                                <input type="text" name="hero_title" id="custHeroTitle" class="form-control form-control-sm fw-bold" 
                                                       value="{{ $blogSettings['hero_title'] ?? 'Ideapatra — Contemporary Literature & Thought' }}" oninput="updateLivePreview()">
                                            </div>

                                            <!-- Hero Subtitle -->
                                            <div class="col-12">
                                                <label class="form-label small fw-bold text-dark mb-1">Hero Subtitle & Description:</label>
                                                <textarea name="hero_subtitle" id="custHeroSubtitle" rows="2" class="form-control form-control-sm" oninput="updateLivePreview()">{{ $blogSettings['hero_subtitle'] ?? 'An open digital magazine for contemporary literary discussions, essays, short stories, poems, book reviews and analytical research.' }}</textarea>
                                            </div>

                                            <!-- Write Button Text & URL -->
                                            <div class="col-6">
                                                <label class="form-label small fw-bold text-dark mb-1">Write Button Label:</label>
                                                <input type="text" name="write_button_text" id="custWriteBtnText" class="form-control form-control-sm" 
                                                       value="{{ $blogSettings['write_button_text'] ?? 'Submit Your Post' }}" oninput="updateLivePreview()">
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label small fw-bold text-dark mb-1">Button Link (URL):</label>
                                                <input type="text" name="write_button_url" id="custWriteBtnUrl" class="form-control form-control-sm" 
                                                       value="{{ $blogSettings['write_button_url'] ?? '/blog/write' }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Tab 2: Typography & Line Spacing -->
                                <div class="tab-pane fade" id="tab-typography" role="tabpanel">
                                    <div class="card border-0 shadow-xs rounded-3 p-3.5 bg-white mb-3">
                                        <h6 class="fw-bold text-dark mb-3 pb-2 border-bottom d-flex align-items-center gap-2">
                                            <i class="fas fa-text-height text-primary"></i>
                                            <span>Typography & Line Spacing</span>
                                        </h6>

                                        <div class="row g-3">
                                            <!-- Font Family -->
                                            <div class="col-12">
                                                <label class="form-label small fw-bold text-dark mb-1">Font Family:</label>
                                                <select name="font_family" id="custFontFamily" class="form-select form-select-sm" onchange="updateLivePreview()">
                                                    <option value="'Hind Siliguri', 'Kalpurush', 'SolaimanLipi', sans-serif" @selected(($blogSettings['font_family'] ?? '') == "'Hind Siliguri', 'Kalpurush', 'SolaimanLipi', sans-serif")>Hind Siliguri (Modern & Standard)</option>
                                                    <option value="'Kalpurush', 'SolaimanLipi', Georgia, serif" @selected(($blogSettings['font_family'] ?? '') == "'Kalpurush', 'SolaimanLipi', Georgia, serif")>Kalpurush (Classic Literary Serif)</option>
                                                    <option value="'SolaimanLipi', 'Hind Siliguri', sans-serif" @selected(($blogSettings['font_family'] ?? '') == "'SolaimanLipi', 'Hind Siliguri', sans-serif")>SolaimanLipi (Clean Publication)</option>
                                                    <option value="'Nikosh', 'Kalpurush', serif" @selected(($blogSettings['font_family'] ?? '') == "'Nikosh', 'Kalpurush', serif")>Nikosh (Institutional Font)</option>
                                                </select>
                                            </div>

                                            <!-- Base Font Size -->
                                            <div class="col-6">
                                                <label class="form-label small fw-bold text-dark mb-1">Base Reading Font Size:</label>
                                                <select name="reading_font_size" id="custFontSize" class="form-select form-select-sm" onchange="updateLivePreview()">
                                                    <option value="1.0rem" @selected(($blogSettings['reading_font_size'] ?? '') == '1.0rem')>Compact (16px)</option>
                                                    <option value="1.08rem" @selected(($blogSettings['reading_font_size'] ?? '') == '1.08rem' || !isset($blogSettings['reading_font_size']))>Standard Reading (17.5px)</option>
                                                    <option value="1.15rem" @selected(($blogSettings['reading_font_size'] ?? '') == '1.15rem')>Medium Large (18.5px)</option>
                                                    <option value="1.25rem" @selected(($blogSettings['reading_font_size'] ?? '') == '1.25rem')>Large (20px)</option>
                                                </select>
                                            </div>

                                            <!-- Base Line Height -->
                                            <div class="col-6">
                                                <label class="form-label small fw-bold text-dark mb-1">Prose / Essay Line Height:</label>
                                                <select name="line_height" id="custLineHeight" class="form-select form-select-sm" onchange="updateLivePreview()">
                                                    <option value="1.45" @selected(($blogSettings['line_height'] ?? '') == '1.45')>Tight (1.45)</option>
                                                    <option value="1.55" @selected(($blogSettings['line_height'] ?? '') == '1.55')>Compact (1.55)</option>
                                                    <option value="1.6" @selected(($blogSettings['line_height'] ?? '') == '1.6' || !isset($blogSettings['line_height']))>Standard Classic (1.60)</option>
                                                    <option value="1.75" @selected(($blogSettings['line_height'] ?? '') == '1.75')>Spacious (1.75)</option>
                                                    <option value="1.9" @selected(($blogSettings['line_height'] ?? '') == '1.9')>Very Spacious (1.90)</option>
                                                </select>
                                            </div>

                                            <!-- Poetry Line Height -->
                                            <div class="col-6">
                                                <label class="form-label small fw-bold text-dark mb-1">Poetry Line Height:</label>
                                                <select name="poetry_line_height" id="custPoetryLineHeight" class="form-select form-select-sm" onchange="updateLivePreview()">
                                                    <option value="1.35" @selected(($blogSettings['poetry_line_height'] ?? '') == '1.35')>Very Compact (1.35)</option>
                                                    <option value="1.45" @selected(($blogSettings['poetry_line_height'] ?? '') == '1.45' || !isset($blogSettings['poetry_line_height']))>Standard Poetry (1.45)</option>
                                                    <option value="1.55" @selected(($blogSettings['poetry_line_height'] ?? '') == '1.55')>Medium Spacing (1.55)</option>
                                                    <option value="1.75" @selected(($blogSettings['poetry_line_height'] ?? '') == '1.75')>Spacious (1.75)</option>
                                                </select>
                                            </div>

                                            <!-- Poetry Alignment -->
                                            <div class="col-6">
                                                <label class="form-label small fw-bold text-dark mb-1">Poetry Alignment:</label>
                                                <select name="poetry_align" id="custPoetryAlign" class="form-select form-select-sm" onchange="updateLivePreview()">
                                                    <option value="left" @selected(($blogSettings['poetry_align'] ?? '') == 'left')>Left</option>
                                                    <option value="center" @selected(($blogSettings['poetry_align'] ?? '') == 'center')>Center</option>
                                                    <option value="justify" @selected(($blogSettings['poetry_align'] ?? '') == 'justify')>Justify</option>
                                                </select>
                                            </div>

                                            <!-- Paragraph Spacing Margin -->
                                            <div class="col-12">
                                                <label class="form-label small fw-bold text-dark mb-1">Paragraph & Verse Gap:</label>
                                                <select name="paragraph_margin" id="custParaMargin" class="form-select form-select-sm" onchange="updateLivePreview()">
                                                    <option value="0.55rem" @selected(($blogSettings['paragraph_margin'] ?? '') == '0.55rem')>Very Tight (0.55rem)</option>
                                                    <option value="0.85rem" @selected(($blogSettings['paragraph_margin'] ?? '') == '0.85rem' || !isset($blogSettings['paragraph_margin']))>Standard Compact (0.85rem)</option>
                                                    <option value="1.15rem" @selected(($blogSettings['paragraph_margin'] ?? '') == '1.15rem')>Regular Gap (1.15rem)</option>
                                                    <option value="1.5rem" @selected(($blogSettings['paragraph_margin'] ?? '') == '1.5rem')>Wide Gap (1.5rem)</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Tab 3: Reader & Layout Controls -->
                                <div class="tab-pane fade" id="tab-reading" role="tabpanel">
                                    <div class="card border-0 shadow-xs rounded-3 p-3.5 bg-white mb-3">
                                        <h6 class="fw-bold text-dark mb-3 pb-2 border-bottom d-flex align-items-center gap-2">
                                            <i class="fas fa-layer-group text-primary"></i>
                                            <span>Reader & External Layout Options</span>
                                        </h6>

                                        <div class="row g-3">
                                            <!-- Paper Background Color -->
                                            <div class="col-12">
                                                <label class="form-label small fw-bold text-dark mb-1">Article Sheet Background:</label>
                                                <select name="reading_bg" id="custReadingBg" class="form-select form-select-sm" onchange="updateLivePreview()">
                                                    <option value="#ffffff" @selected(($blogSettings['reading_bg'] ?? '') == '#ffffff')>Clean White Paper</option>
                                                    <option value="#fbf9f4" @selected(($blogSettings['reading_bg'] ?? '') == '#fbf9f4')>Ivory Literary Book Page</option>
                                                    <option value="#f8f4eb" @selected(($blogSettings['reading_bg'] ?? '') == '#f8f4eb')>Soft Sepia Reading Mode</option>
                                                </select>
                                            </div>

                                            <!-- Toggles -->
                                            <div class="col-12 pt-2">
                                                <div class="form-check form-switch mb-2">
                                                    <input class="form-check-input" type="checkbox" role="switch" name="show_reading_bar" id="custShowReadingBar" value="1" 
                                                           @checked(($blogSettings['show_reading_bar'] ?? '1') == '1')>
                                                    <label class="form-check-label small fw-semibold text-dark" for="custShowReadingBar">
                                                        Show top reading toolbar (print, font zoom & sepia mode)
                                                    </label>
                                                </div>
                                                <div class="form-check form-switch mb-2">
                                                    <input class="form-check-input" type="checkbox" role="switch" name="enable_share_bar" id="custEnableShareBar" value="1" 
                                                           @checked(($blogSettings['enable_share_bar'] ?? '1') == '1')>
                                                    <label class="form-check-label small fw-semibold text-dark" for="custEnableShareBar">
                                                        Enable social sharing & photo card downloads
                                                    </label>
                                                </div>
                                                <div class="form-check form-switch mb-0">
                                                    <input class="form-check-input" type="checkbox" role="switch" name="show_author_box" id="custShowAuthorBox" value="1" 
                                                           @checked(($blogSettings['show_author_box'] ?? '1') == '1')>
                                                    <label class="form-check-label small fw-semibold text-dark" for="custShowAuthorBox">
                                                        Show author bio box & related articles below post
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <!-- Right Column: Interactive Live Preview -->
                        <div class="col-lg-5">
                            <div class="card border-0 shadow-sm rounded-4 overflow-hidden sticky-top" style="top: 15px;">
                                <div class="card-header bg-dark text-white py-2 px-3 d-flex align-items-center justify-content-between">
                                    <span class="small fw-bold"><i class="fas fa-eye me-1 text-warning"></i> Real-time Live Preview</span>
                                    <span class="badge bg-primary-subtle text-primary rounded-pill small" style="font-size: 10px;">Live Update</span>
                                </div>
                                <div class="card-body p-3" style="background-color: #f1f5f9; max-height: 520px; overflow-y: auto;">
                                    
                                    <!-- Preview Hero Masthead -->
                                    <div id="prevHeaderBox" class="p-3 rounded-3 text-white mb-3 shadow-xs position-relative overflow-hidden" 
                                         style="background: {{ $blogSettings['header_gradient'] ?? 'linear-gradient(135deg, #0c4a6e 0%, #0369a1 50%, #0284c7 100%)' }};">
                                        <span id="prevBadgeText" class="badge bg-white bg-opacity-25 rounded-pill mb-1.5 px-2 py-0.5" style="font-size: 10px;">
                                            {{ $blogSettings['hero_badge'] ?? 'Literature, Culture, Research & Free Thought' }}
                                        </span>
                                        <h6 id="prevHeroTitle" class="fw-bold mb-1" style="font-size: 1.05rem;">
                                            {{ $blogSettings['hero_title'] ?? 'Idea Blog & Literary Journal' }}
                                        </h6>
                                        <p id="prevHeroSubtitle" class="small opacity-90 mb-2" style="font-size: 11px; line-height: 1.4;">
                                            {{ $blogSettings['hero_subtitle'] ?? 'Contemporary literary discussions, essays, stories, poems...' }}
                                        </p>
                                        <button type="button" id="prevWriteBtn" class="btn btn-warning btn-xs rounded-pill px-2.5 py-1 fw-bold text-dark">
                                            <i class="fas fa-feather-pointed me-1"></i> <span>{{ $blogSettings['write_button_text'] ?? 'Submit Your Post' }}</span>
                                        </button>
                                    </div>

                                    <!-- Preview Book Sheet -->
                                    <div id="prevBookSheet" class="p-3.5 rounded-3 border shadow-xs" 
                                         style="background-color: {{ $blogSettings['reading_bg'] ?? '#ffffff' }}; font-family: {{ $blogSettings['font_family'] ?? 'sans-serif' }};">
                                        
                                        <div class="border-bottom pb-2 mb-2.5">
                                            <span class="badge bg-primary-subtle text-primary mb-1" style="font-size: 10px;">Poetry</span>
                                            <h5 class="fw-bold text-dark mb-0.5" style="font-size: 1.15rem;">The Solitary Hour</h5>
                                            <small class="text-muted" style="font-size: 11px;">Author: Al Amin Islam • Literary Edition</small>
                                        </div>

                                        <!-- Preview Content -->
                                        <div id="prevArticleContent" style="font-size: {{ $blogSettings['reading_font_size'] ?? '1.08rem' }}; line-height: {{ $blogSettings['line_height'] ?? '1.6' }};">
                                            
                                            <!-- Poetry Sample -->
                                            <p id="prevPoetryVerse" class="poetry-verse p-2 border-start border-3 border-primary bg-primary bg-opacity-10 rounded-end mb-2" 
                                               style="line-height: {{ $blogSettings['poetry_line_height'] ?? '1.45' }}; margin-bottom: {{ $blogSettings['paragraph_margin'] ?? '0.85rem' }}; text-align: {{ $blogSettings['poetry_align'] ?? 'left' }}; font-size: 1.05em;">
                                                Silent is this lonely night<br>
                                                The moon wanders like a weary traveler;<br>
                                                Wind whispers softly through the trees...
                                            </p>

                                            <!-- Prose Sample -->
                                            <p id="prevProsePara" class="text-dark" style="margin-bottom: {{ $blogSettings['paragraph_margin'] ?? '0.85rem' }}; text-align: justify;">
                                                Literature is not merely a craftsmanship of words, but the profoundest expression of human emotion and intellect.
                                            </p>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="modal-footer bg-white py-3 px-4 border-top d-flex justify-content-between">
                    <button type="button" class="btn btn-light border rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-xs">
                        <i class="fas fa-save me-1.5"></i> Save Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- 6. BULK BLOG TYPOGRAPHY NORMALIZER MODAL                                  --}}
{{-- ========================================================================= --}}
<div class="modal fade" id="bulkTypographyModal" tabindex="-1" aria-labelledby="bulkTypographyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header bg-success text-white py-3 px-4">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-wand-magic-sparkles fs-5"></i>
                    <h5 class="modal-title fw-bold fs-6 mb-0 text-white" id="bulkTypographyModalLabel">Bulk Typography & Spacing Normalizer Engine</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                <div class="text-center mb-3">
                    <div class="rounded-circle bg-success bg-opacity-10 text-success p-3 d-inline-flex align-items-center justify-content-center mb-2" style="width: 60px; height: 60px;">
                        <i class="fas fa-compress-alt fs-3"></i>
                    </div>
                    <h6 class="fw-bold text-dark mb-1">Automatically Optimize Blog Post Formatting</h6>
                    <p class="small text-muted mb-0">
                        Normalizes erratic line heights, cleans up excessive empty paragraphs, and standardizes spacing across historical articles.
                    </p>
                </div>

                <div id="bulkProcessNotice" class="alert alert-info p-2.5 small mb-3 rounded-3 d-flex align-items-center gap-2">
                    <i class="fas fa-circle-info fs-5 text-info"></i>
                    <div>Zero content loss: Only extra spacing and inline line-heights will be formatted cleanly.</div>
                </div>

                <div id="bulkProgressBox" class="d-none mb-3">
                    <div class="progress" style="height: 10px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: 100%"></div>
                    </div>
                    <div class="text-center small text-muted mt-2" id="bulkProgressText">
                        Processing articles, please wait...
                    </div>
                </div>

                <div id="bulkResultAlert"></div>

                <div class="mb-2">
                    <label class="form-label small fw-bold text-dark">Which posts should be formatted?</label>
                    <select id="bulkTargetSelect" class="form-select form-select-sm">
                        <option value="all">All Posts (Approved, Draft & Pending)</option>
                        <option value="published">Published & Approved Posts Only</option>
                    </select>
                </div>
            </div>

            <div class="modal-footer bg-light py-2.5 px-4">
                <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="startBulkNormalizeBtn" class="btn btn-sm btn-success rounded-pill px-4 fw-bold shadow-xs" onclick="runBulkNormalizeTypography()">
                    <i class="fas fa-play me-1"></i> Run Normalizer
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- 7. BANNER CROPPER MODAL                                                   --}}
{{-- ========================================================================= --}}
<div class="modal fade" id="bannerCropperModal" tabindex="-1" aria-labelledby="bannerCropperModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header bg-dark text-white py-2.5 px-4">
                <h5 class="modal-title fw-bold fs-6 d-flex align-items-center gap-2" id="bannerCropperModalLabel">
                    <i class="fas fa-crop-simple text-warning"></i>
                    <span>Crop & Resize Ideapatra Banner (16:9)</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3 text-center bg-light">
                <div style="max-height: 440px; overflow: hidden; background: #000; border-radius: 8px;">
                    <img id="bannerCropperImageEl" src="" alt="Crop Target" style="max-width: 100%; max-height: 440px; display: block; margin: 0 auto;">
                </div>
                <div class="d-flex justify-content-center gap-2 mt-3 flex-wrap">
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill" onclick="blogCropper && blogCropper.zoom(0.1)" title="Zoom In">
                        <i class="fas fa-magnifying-glass-plus me-1"></i> Zoom In
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill" onclick="blogCropper && blogCropper.zoom(-0.1)" title="Zoom Out">
                        <i class="fas fa-magnifying-glass-minus me-1"></i> Zoom Out
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill" onclick="blogCropper && blogCropper.rotate(-90)" title="Rotate Left">
                        <i class="fas fa-rotate-left me-1"></i> Rotate Left
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill" onclick="blogCropper && blogCropper.rotate(90)" title="Rotate Right">
                        <i class="fas fa-rotate-right me-1"></i> Rotate Right
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill" onclick="blogCropper && blogCropper.reset()" title="Reset">
                        <i class="fas fa-arrows-rotate me-1"></i> Reset
                    </button>
                </div>
            </div>
            <div class="modal-footer bg-white py-2.5 px-4 border-top d-flex justify-content-between">
                <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-sm btn-primary rounded-pill px-4 fw-bold shadow-xs" onclick="applyBannerCrop()">
                    <i class="fas fa-check me-1"></i> Apply Crop
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- 8. BLOG POST REJECTION MODAL                                              --}}
{{-- ========================================================================= --}}
<div class="modal fade" id="rejectBlogModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <div class="modal-header bg-danger text-white py-2.5 px-4">
                <h5 class="modal-title fs-6 fw-bold">
                    <i class="fas fa-triangle-exclamation me-1.5"></i> ব্লগ পোস্ট বাতিল / সংশোধন নির্দেশ
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" id="rejectBlogPostId">
                <p class="small text-muted mb-2">
                    পোস্ট: <strong class="text-dark" id="rejectBlogPostTitle"></strong>
                </p>
                <div class="mb-3">
                    <label class="form-label small fw-bold text-dark">বাতিলের কারণ / প্রয়োজনীয় সংশোধন:</label>
                    <textarea id="rejectBlogPostReason" class="form-control rounded-3" rows="3" placeholder="যেমন: লেখার টাইপোগ্রাফি বা বানান ত্রুটি, ব্লগের নীতিমালার সাথে সামঞ্জস্যপূর্ণ নয়..."></textarea>
                </div>
            </div>
            <div class="modal-footer bg-light py-2.5 px-4">
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="confirmRejectBlogBtn" class="btn btn-sm btn-danger rounded-pill px-4 fw-bold" onclick="ajaxRejectBlogPostSubmit()">
                    <i class="fas fa-circle-xmark me-1"></i> নিশ্চিত বাতিল করুন
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js"></script>
<script>
let blogCropper = null;
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

function switchToBannerTab() {
    const tabBtn = document.getElementById('tab-banner-btn');
    if (tabBtn) {
        bootstrap.Tab.getOrCreateInstance(tabBtn).show();
    }
}

// Live Banner Cropper Logic
function initBannerCropper(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const reader = new FileReader();
        reader.onload = function(e) {
            const imageEl = document.getElementById('bannerCropperImageEl');
            imageEl.src = e.target.result;

            const cropModalEl = document.getElementById('bannerCropperModal');
            const cropModal = bootstrap.Modal.getOrCreateInstance(cropModalEl);
            cropModal.show();

            cropModalEl.addEventListener('shown.bs.modal', function onShown() {
                if (blogCropper) {
                    blogCropper.destroy();
                }
                blogCropper = new Cropper(imageEl, {
                    aspectRatio: 16 / 9,
                    viewMode: 2,
                    dragMode: 'move',
                    autoCropArea: 1,
                    restore: false,
                    guides: true,
                    center: true,
                    highlight: false,
                    cropBoxMovable: true,
                    cropBoxResizable: true,
                    toggleDragModeOnDblclick: false,
                });
                cropModalEl.removeEventListener('shown.bs.modal', onShown);
            });
        };
        reader.readAsDataURL(file);
    }
}

function applyBannerCrop() {
    if (!blogCropper) return;
    const canvas = blogCropper.getCroppedCanvas({
        width: 1200,
        height: 630,
        imageSmoothingEnabled: true,
        imageSmoothingQuality: 'high',
    });

    const croppedBase64 = canvas.toDataURL('image/jpeg', 0.92);
    document.getElementById('modalBannerCropped').value = croppedBase64;
    
    // Update live previews
    const modalPrev = document.getElementById('modalBannerPreview');
    if (modalPrev) modalPrev.src = croppedBase64;
    
    const heroThumb = document.getElementById('heroBannerThumb');
    if (heroThumb) heroThumb.src = croppedBase64;

    const cropModalEl = document.getElementById('bannerCropperModal');
    bootstrap.Modal.getInstance(cropModalEl)?.hide();
}

// AJAX Toggle Post Featured Star
function toggleFeatured(postId) {
    const starBtn = document.getElementById('starBtn' + postId);
    starBtn.disabled = true;

    fetch(`/admin/blog/${postId}/toggle-featured`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        }
    })
    .then(res => res.json())
    .then(data => {
        starBtn.disabled = false;
        if (data.success) {
            if (data.is_featured) {
                starBtn.classList.add('active');
                starBtn.title = 'Featured post (Click to unfeature)';
            } else {
                starBtn.classList.remove('active');
                starBtn.title = 'Standard post (Click to feature)';
            }
        }
    })
    .catch(() => {
        starBtn.disabled = false;
        alert('Server error occurred.');
    });
}

// Open Blog Reject Modal
function openBlogRejectModal(postId, title) {
    document.getElementById('rejectBlogPostId').value = postId;
    document.getElementById('rejectBlogPostTitle').textContent = title;
    document.getElementById('rejectBlogPostReason').value = '';
    const modalEl = document.getElementById('rejectBlogModal');
    if (modalEl) {
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }
}

// Submit Blog Rejection via AJAX
async function ajaxRejectBlogPostSubmit() {
    const postId = document.getElementById('rejectBlogPostId').value;
    const reason = document.getElementById('rejectBlogPostReason').value.trim();
    if (!reason) {
        alert('অনুগ্রহ করে বাতিলের সুনির্দিষ্ট কারণ লিখুন।');
        return;
    }

    const btn = document.getElementById('confirmRejectBlogBtn');
    const origHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = `<i class="fas fa-spinner fa-spin me-1"></i> প্রক্রিয়াকরণ হচ্ছে...`;

    try {
        await updatePostStatus(postId, 'rejected', null, reason);
        const modalEl = document.getElementById('rejectBlogModal');
        if (modalEl) {
            bootstrap.Modal.getInstance(modalEl)?.hide();
        }
    } finally {
        btn.disabled = false;
        btn.innerHTML = origHtml;
    }
}

// AJAX Update Post Status with Instant Visual Feedback
async function updatePostStatus(postId, newStatus, triggerBtn = null, reason = null) {
    const select = document.getElementById('statusSelect' + postId);
    if (select) select.disabled = true;

    let originalBtnHtml = '';
    if (triggerBtn) {
        originalBtnHtml = triggerBtn.innerHTML;
        triggerBtn.disabled = true;
        triggerBtn.innerHTML = `<i class="fas fa-spinner fa-spin"></i>`;
    }

    try {
        const payload = { status: newStatus };
        if (reason) {
            payload.rejection_reason = reason;
        }

        const res = await fetch(`/admin/blog/${postId}/toggle-status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify(payload)
        });

        const data = await res.json();

        if (select) select.disabled = false;
        if (triggerBtn) {
            triggerBtn.disabled = false;
            triggerBtn.innerHTML = originalBtnHtml;
        }

        if (data.success) {
            // 1. Update Select Dropdown Value & Classes
            if (select) {
                select.value = newStatus;
                select.className = 'form-select form-select-sm status-select-badge';
                if (newStatus === 'published') {
                    select.classList.add('bg-success-subtle', 'text-success', 'border-success-subtle');
                } else if (newStatus === 'pending') {
                    select.classList.add('bg-warning-subtle', 'text-warning', 'border-warning-subtle');
                } else if (newStatus === 'rejected') {
                    select.classList.add('bg-danger-subtle', 'text-danger', 'border-danger-subtle');
                } else {
                    select.classList.add('bg-secondary-subtle', 'text-secondary', 'border-secondary-subtle');
                }
            }

            // 2. Row Green Flash Animation
            const row = document.getElementById('postRow' + postId);
            if (row) {
                row.classList.remove('row-approved-flash');
                void row.offsetWidth; // Force Reflow
                row.classList.add('row-approved-flash');
            }

            // 3. Update Approve & Reject Button States (All 5 buttons remain present)
            const approveBtn = document.getElementById('approveBtn' + postId);
            if (approveBtn) {
                if (newStatus === 'published') {
                    approveBtn.className = 'adm-action-btn btn btn-outline-success shadow-xs';
                    approveBtn.innerHTML = '<i class="fas fa-check-double me-1"></i> Approved';
                    approveBtn.title = 'Published (Click to re-approve)';
                } else {
                    approveBtn.className = 'adm-action-btn btn btn-success shadow-xs btn-approve-action text-white';
                    approveBtn.innerHTML = '<i class="fas fa-circle-check me-1"></i> Approve';
                    approveBtn.title = 'Approve & Publish Immediately';
                }
                approveBtn.disabled = false;
            }

            const rejectBtn = document.getElementById('rejectBtn' + postId);
            if (rejectBtn) {
                if (newStatus === 'rejected') {
                    rejectBtn.className = 'adm-action-btn btn btn-outline-danger shadow-xs';
                    rejectBtn.innerHTML = '<i class="fas fa-circle-xmark me-1"></i> Rejected';
                    rejectBtn.title = 'Rejected (Click to edit reason)';
                } else {
                    rejectBtn.className = 'adm-action-btn btn btn-outline-danger shadow-xs';
                    rejectBtn.innerHTML = '<i class="fas fa-times me-1"></i> Reject';
                    rejectBtn.title = 'Reject / Request Changes';
                }
                rejectBtn.disabled = false;
            }

            // 4. Floating Toast Alert
            showBlogToast(newStatus === 'published' ? 'success' : (newStatus === 'rejected' ? 'warning' : 'info'), data.message);
        } else {
            showBlogToast('danger', data.message || 'স্ট্যাটাস পরিবর্তন ব্যর্থ হয়েছে।');
        }
    } catch (err) {
        console.error(err);
        if (select) select.disabled = false;
        if (triggerBtn) {
            triggerBtn.disabled = false;
            triggerBtn.innerHTML = originalBtnHtml;
        }
        showBlogToast('danger', 'সার্ভারের সাথে যোগাযোগে ত্রুটি হয়েছে।');
    }
}

function showBlogToast(type, msg) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed top-0 end-0 m-3 shadow-lg rounded-4`;
    alertDiv.style.zIndex = '99999';
    alertDiv.style.maxWidth = '420px';
    alertDiv.innerHTML = `
        <div class="d-flex align-items-center gap-2">
            <i class="fas ${type === 'success' ? 'fa-check-circle text-success' : (type === 'warning' ? 'fa-triangle-exclamation text-warning' : 'fa-circle-xmark text-danger')} fs-5"></i>
            <div class="small fw-bold text-dark">${msg}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    `;
    document.body.appendChild(alertDiv);
    setTimeout(() => { alertDiv.remove(); }, 4000);
}

// Delete Post Action
function deletePost(postId, title) {
    SwalConfirm({
        title: 'লেখাটি ডিলিট করতে চান?',
        html: `আপনি কি নিশ্চিত যে <strong>‘${title}’</strong> পোস্টটি ডিলিট করতে চান?<br><span class="text-danger small">এটি ব্লগ ও রিডিং পেজ থেকে মুছে যাবে।</span>`,
        icon: 'warning',
        confirmButtonText: '<i class="fas fa-trash-can me-1"></i> হ্যাঁ, ডিলিট করুন',
        confirmButtonColor: '#ef4444',
        cancelButtonText: 'বাতিল'
    }).then(function(result) {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/blog/${postId}`;
            
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = csrfToken;
            form.appendChild(csrfInput);

            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
            form.appendChild(methodInput);

            document.body.appendChild(form);
            form.submit();
        }
    });
}

// Bulk Actions Select All
function toggleSelectAll(masterCheckbox) {
    document.querySelectorAll('.row-checkbox').forEach(cb => {
        cb.checked = masterCheckbox.checked;
    });
}

function handleBulkActionSubmit(e) {
    const action = document.getElementById('bulkActionSelect').value;
    if (!action) {
        Swal.fire({ title: 'অ্যাকশন নির্বাচন করুন', text: 'অনুগ্রহ করে একটি বাল্ক অ্যাকশন বেছে নিন।', icon: 'info' });
        return false;
    }
    const checkedCount = document.querySelectorAll('.row-checkbox:checked').length;
    if (checkedCount === 0) {
        Swal.fire({ title: 'পোস্ট নির্বাচন করুন', text: 'অনুগ্রহ করে অন্তত একটি পোস্ট সিলেক্ট করুন।', icon: 'warning' });
        return false;
    }
    e.preventDefault();
    SwalConfirm({
        title: 'বাল্ক অ্যাকশন নিশ্চিতকরণ',
        text: `আপনি কি নিশ্চিত যে নির্বাচিত ${checkedCount}টি পোস্টে এই অ্যাকশন প্রয়োগ করতে চান?`,
        icon: 'question',
        confirmButtonText: '<i class="fas fa-check me-1"></i> হ্যাঁ, প্রয়োগ করুন',
    }).then(function(result) {
        if (result.isConfirmed) {
            e.target.submit();
        }
    });
}

// Live Preview Controller for Blog Customizer
function updateLivePreview() {
    const font = document.getElementById('custFontFamily')?.value || 'sans-serif';
    const fontSize = document.getElementById('custFontSize')?.value || '1.08rem';
    const lineHeight = document.getElementById('custLineHeight')?.value || '1.6';
    const poetryLineHeight = document.getElementById('custPoetryLineHeight')?.value || '1.45';
    const poetryAlign = document.getElementById('custPoetryAlign')?.value || 'left';
    const paraMargin = document.getElementById('custParaMargin')?.value || '0.85rem';
    const headerGradient = document.getElementById('custHeaderGradient')?.value || '';
    const heroBadge = document.getElementById('custHeroBadge')?.value || '';
    const heroTitle = document.getElementById('custHeroTitle')?.value || '';
    const heroSubtitle = document.getElementById('custHeroSubtitle')?.value || '';
    const writeBtnText = document.getElementById('custWriteBtnText')?.value || '';
    const readingBg = document.getElementById('custReadingBg')?.value || '#ffffff';

    const prevHeaderBox = document.getElementById('prevHeaderBox');
    if (prevHeaderBox && headerGradient) prevHeaderBox.style.background = headerGradient;

    const prevBadgeText = document.getElementById('prevBadgeText');
    if (prevBadgeText) prevBadgeText.innerText = heroBadge;

    const prevHeroTitle = document.getElementById('prevHeroTitle');
    if (prevHeroTitle) prevHeroTitle.innerText = heroTitle;

    const prevHeroSubtitle = document.getElementById('prevHeroSubtitle');
    if (prevHeroSubtitle) prevHeroSubtitle.innerText = heroSubtitle;

    const prevWriteBtn = document.getElementById('prevWriteBtn');
    if (prevWriteBtn) prevWriteBtn.querySelector('span').innerText = writeBtnText;

    const prevBookSheet = document.getElementById('prevBookSheet');
    if (prevBookSheet) {
        prevBookSheet.style.backgroundColor = readingBg;
        prevBookSheet.style.fontFamily = font;
    }

    const prevArticleContent = document.getElementById('prevArticleContent');
    if (prevArticleContent) {
        prevArticleContent.style.fontSize = fontSize;
        prevArticleContent.style.lineHeight = lineHeight;
    }

    const prevPoetryVerse = document.getElementById('prevPoetryVerse');
    if (prevPoetryVerse) {
        prevPoetryVerse.style.lineHeight = poetryLineHeight;
        prevPoetryVerse.style.marginBottom = paraMargin;
        prevPoetryVerse.style.textAlign = poetryAlign;
    }

    const prevProsePara = document.getElementById('prevProsePara');
    if (prevProsePara) {
        prevProsePara.style.marginBottom = paraMargin;
    }
}

// Bulk Normalize AJAX Engine
async function runBulkNormalizeTypography() {
    const btn = document.getElementById('startBulkNormalizeBtn');
    const target = document.getElementById('bulkTargetSelect')?.value || 'all';
    const progressBox = document.getElementById('bulkProgressBox');
    const resultAlert = document.getElementById('bulkResultAlert');

    const result = await SwalConfirm({
        title: 'ফরম্যাটিং অটোমেশন',
        text: 'আপনি কি নিশ্চিত যে সকল আর্টিকেলের লাইন স্পেসিং ও অনুচ্ছেদের মার্জিন অটো-ফরম্যাট করতে চান?',
        icon: 'question',
        confirmButtonText: '<i class="fas fa-check me-1"></i> হ্যাঁ, শুরু করুন',
    });
    if (!result.isConfirmed) return;

    btn.disabled = true;
    progressBox.classList.remove('d-none');
    resultAlert.innerHTML = '';

    fetch("{{ route('admin.blog.bulk-normalize-typography') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({ target: target })
    })
    .then(res => res.json())
    .then(data => {
        progressBox.classList.add('d-none');
        btn.disabled = false;
        if (data.success) {
            resultAlert.innerHTML = `
                <div class="alert alert-success p-3 small mb-3 rounded-3">
                    <i class="fas fa-circle-check fs-5 text-success me-2"></i>
                    <strong>Success!</strong> ${data.message}
                </div>`;
            setTimeout(() => {
                location.reload();
            }, 1200);
        } else {
            resultAlert.innerHTML = `
                <div class="alert alert-danger p-3 small mb-3 rounded-3">
                    <i class="fas fa-triangle-exclamation me-1"></i> ${data.message || 'Error occurred'}
                </div>`;
        }
    })
    .catch(() => {
        progressBox.classList.add('d-none');
        btn.disabled = false;
        resultAlert.innerHTML = `
            <div class="alert alert-danger p-3 small mb-3 rounded-3">
                <i class="fas fa-triangle-exclamation me-1"></i> Server error occurred. Please try again.
            </div>`;
    });
}
{{-- ═════════════════════════════════════════════════════════════════════════ --}}
{{-- 5. MODALS (EDIT REQUEST REVIEW & REJECT REASON)                             --}}
{{-- ═════════════════════════════════════════════════════════════════════════ --}}

{{-- Modal: Author Edit Request Review & Replace --}}
<div class="modal fade" id="blogEditRequestModal" tabindex="-1" aria-labelledby="blogEditRequestModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-dark text-white border-0 py-3 px-4">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="p-2 bg-warning text-dark rounded-circle d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                        <i class="fas fa-code-compare fs-6"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0 text-white" id="blogEditRequestModalLabel">
                            লেখকের কারেকশন রিকোয়েস্ট পর্যালোচনা ও রিপ্লেস
                        </h5>
                        <div class="small opacity-75" id="editReqMetaHeader">পোস্ট আইডি ও লেখকের তথ্য লোড হচ্ছে...</div>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4 bg-light">
                {{-- Correction Note & Author Message Alert --}}
                <div class="alert alert-warning border-0 rounded-4 shadow-xs mb-3 p-3">
                    <div class="d-flex align-items-start gap-2">
                        <i class="fas fa-comment-dots text-warning-emphasis fs-5 mt-0.5"></i>
                        <div class="w-100">
                            <strong class="text-dark d-block">লেখকের সংশোধনী নোট / কারেকশন বার্তা:</strong>
                            <p class="mb-0 text-dark small mt-0.5" id="editReqNotesDisplay">কোনো নোট প্রদান করা হয়নি।</p>
                        </div>
                    </div>
                </div>

                {{-- Comparison Container --}}
                <div class="row g-3">
                    {{-- 1. Original / Currently Live Post --}}
                    <div class="col-12 col-lg-6">
                        <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden bg-white">
                            <div class="card-header bg-secondary bg-opacity-10 border-bottom py-2.5 px-3.5 d-flex align-items-center justify-content-between">
                                <span class="fw-bold text-secondary small">
                                    <i class="fas fa-globe me-1"></i> বর্তমানে লাইভ থাকা মূল পোস্ট (Original)
                                </span>
                                <span class="badge bg-secondary rounded-pill small">Current Live</span>
                            </div>
                            <div class="card-body p-3.5">
                                <div class="mb-2">
                                    <small class="text-muted d-block fw-semibold" style="font-size: 11px;">মূল শিরোনাম</small>
                                    <h6 class="fw-bold text-dark mb-0" id="origTitleDisplay">—</h6>
                                </div>
                                <div class="mb-2" id="origSubtitleWrap">
                                    <small class="text-muted d-block fw-semibold" style="font-size: 11px;">সাবটাইটেল</small>
                                    <div class="text-muted small" id="origSubtitleDisplay">—</div>
                                </div>
                                <div class="mb-2">
                                    <small class="text-muted d-block fw-semibold" style="font-size: 11px;">ক্যাটাগরি</small>
                                    <span class="badge bg-light text-dark border" id="origCategoryDisplay">—</span>
                                </div>
                                <div class="mb-2" id="origExcerptWrap">
                                    <small class="text-muted d-block fw-semibold" style="font-size: 11px;">সারসংক্ষেপ (Excerpt)</small>
                                    <div class="p-2 bg-light rounded-3 small text-dark" style="max-height: 80px; overflow-y: auto;" id="origExcerptDisplay">—</div>
                                </div>
                                <div>
                                    <small class="text-muted d-block fw-semibold mb-1" style="font-size: 11px;">মূল লেখার কনটেন্ট</small>
                                    <div class="p-3 bg-light rounded-3 border small overflow-auto text-dark" style="max-height: 280px; line-height: 1.6;" id="origContentDisplay">
                                        —
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 2. Author's Revised / Corrected Post --}}
                    <div class="col-12 col-lg-6">
                        <div class="card h-100 border-2 border-warning shadow-sm rounded-4 overflow-hidden bg-white">
                            <div class="card-header bg-warning bg-opacity-25 border-bottom py-2.5 px-3.5 d-flex align-items-center justify-content-between">
                                <span class="fw-bold text-dark small">
                                    <i class="fas fa-feather-pointed me-1 text-warning-emphasis"></i> লেখকের প্রস্তাবিত সংশোধিত রূপ (Revised)
                                </span>
                                <span class="badge bg-warning text-dark rounded-pill small">Proposed Changes</span>
                            </div>
                            <div class="card-body p-3.5">
                                <div class="mb-2">
                                    <small class="text-warning-emphasis d-block fw-semibold" style="font-size: 11px;">সংশোধিত শিরোনাম</small>
                                    <h6 class="fw-bold text-success mb-0" id="revTitleDisplay">—</h6>
                                </div>
                                <div class="mb-2" id="revSubtitleWrap">
                                    <small class="text-warning-emphasis d-block fw-semibold" style="font-size: 11px;">সংশোধিত সাবটাইটেল</small>
                                    <div class="text-dark small" id="revSubtitleDisplay">—</div>
                                </div>
                                <div class="mb-2">
                                    <small class="text-warning-emphasis d-block fw-semibold" style="font-size: 11px;">ক্যাটাগরি</small>
                                    <span class="badge bg-warning-subtle text-dark border border-warning-subtle" id="revCategoryDisplay">—</span>
                                </div>
                                <div class="mb-2" id="revExcerptWrap">
                                    <small class="text-warning-emphasis d-block fw-semibold" style="font-size: 11px;">সংশোধিত সারসংক্ষেপ</small>
                                    <div class="p-2 bg-warning-subtle rounded-3 small text-dark" style="max-height: 80px; overflow-y: auto;" id="revExcerptDisplay">—</div>
                                </div>
                                <div>
                                    <small class="text-warning-emphasis d-block fw-semibold mb-1" style="font-size: 11px;">সংশোধিত লেখার কনটেন্ট</small>
                                    <div class="p-3 bg-white rounded-3 border border-warning-subtle small overflow-auto text-dark" style="max-height: 280px; line-height: 1.6;" id="revContentDisplay">
                                        —
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer bg-white border-0 py-3 px-4 d-flex flex-wrap justify-content-between gap-2">
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3.5 fw-semibold" id="btnModalRejectEditReq" onclick="triggerRejectEditRequest()">
                        <i class="fas fa-times me-1"></i> কারেকশন বাতিল করুন
                    </button>
                    <a href="#" target="_blank" class="btn btn-outline-primary btn-sm rounded-pill px-3.5" id="btnModalEditManual">
                        <i class="fas fa-pen-to-square me-1"></i> নিজে এডিট করতে ওপেন করুন
                    </a>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-light btn-sm rounded-pill px-3.5" data-bs-dismiss="modal">বন্ধ করুন</button>
                    <button type="button" class="btn btn-success btn-sm rounded-pill px-4 fw-bold shadow-sm" id="btnModalApproveEditReq" onclick="triggerApproveEditRequest()">
                        <i class="fas fa-circle-check me-1"></i> কারেকশন অনুমোদন ও রিপ্লেস করুন
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let currentEditReqPostId = null;

function openBlogEditRequestModal(postId) {
    const dataEl = document.getElementById(`editReqData${postId}`);
    if (!dataEl) return;

    try {
        const item = JSON.parse(dataEl.textContent);
        currentEditReqPostId = postId;
        const req = item.req_data || {};

        document.getElementById('editReqMetaHeader').textContent = `পোস্ট আইডি: #${item.id} | লেখক: ${item.author_name} | জমার সময়: ${item.edit_requested_at || 'সাম্প্রতিক'}`;
        document.getElementById('editReqNotesDisplay').textContent = item.edit_request_notes || 'কোনো আলাদা নোট দেওয়া হয়নি।';

        // Original info
        document.getElementById('origTitleDisplay').textContent = item.title;
        document.getElementById('origSubtitleDisplay').textContent = item.subtitle || '—';
        document.getElementById('origCategoryDisplay').textContent = item.category || 'General';
        document.getElementById('origExcerptDisplay').textContent = item.excerpt || '—';
        document.getElementById('origContentDisplay').innerHTML = item.content || '<em class="text-muted">খালি</em>';

        // Revised info
        document.getElementById('revTitleDisplay').textContent = req.title || item.title;
        document.getElementById('revSubtitleDisplay').textContent = req.subtitle || '—';
        document.getElementById('revCategoryDisplay').textContent = req.category_name || item.category || 'General';
        document.getElementById('revExcerptDisplay').textContent = req.excerpt || '—';
        document.getElementById('revContentDisplay').innerHTML = req.content || '<em class="text-muted">খালি</em>';

        document.getElementById('btnModalEditManual').href = `/admin/content/blog/${postId}/edit`;

        const modal = new bootstrap.Modal(document.getElementById('blogEditRequestModal'));
        modal.show();
    } catch (e) {
        console.error("Could not parse edit request payload:", e);
    }
}

function triggerApproveEditRequest() {
    if (!currentEditReqPostId) return;

    SwalConfirm({
        title: 'কারেকশন অনুমোদন নিশ্চিতকরণ',
        html: `আপনি কি নিশ্চিত যে সংশোধিত লেখাটি লাইভ পোস্টের সাথে রিপ্লেস করতে চান?<br><span class="text-success small">এটি লাইভ আর্টিকেলে অবিলম্বে সক্রিয় হয়ে যাবে।</span>`,
        icon: 'question',
        confirmButtonText: '<i class="fas fa-circle-check me-1"></i> হ্যাঁ, অনুমোদন ও রিপ্লেস করুন',
        confirmButtonColor: '#16a34a'
    }).then(function(result) {
        if (result.isConfirmed) {
            const btn = document.getElementById('btnModalApproveEditReq');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> প্রসেসিং...';
            }

            fetch(`/admin/blog/${currentEditReqPostId}/approve-edit-request`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showSuccessToast(data.message);
                    setTimeout(() => { location.reload(); }, 1000);
                } else {
                    Swal.fire({ title: 'ত্রুটি', text: data.message || 'সমস্যা হয়েছে।', icon: 'error' });
                    if (btn) {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-circle-check me-1"></i> কারেকশন অনুমোদন ও রিপ্লেস করুন';
                    }
                }
            })
            .catch(err => {
                console.error(err);
                Swal.fire({ title: 'সার্ভার ত্রুটি', text: 'অনুরোধ সম্পন্ন করতে ব্যর্থ হয়েছে।', icon: 'error' });
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-circle-check me-1"></i> কারেকশন অনুমোদন ও রিপ্লেস করুন';
                }
            });
        }
    });
}

function triggerRejectEditRequest() {
    if (!currentEditReqPostId) return;

    Swal.fire({
        title: 'কারেকশন বাতিল করুন',
        input: 'textarea',
        inputLabel: 'বাতিল করার কারণ (ঐচ্ছিক)',
        inputPlaceholder: 'লেখকের সংশোধনী কেন গ্রহণ করা গেল না তা লিখুন...',
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-ban me-1"></i> বাতিল নিশ্চিত করুন',
        confirmButtonColor: '#ef4444',
        cancelButtonText: 'ফিরে যান'
    }).then(function(result) {
        if (result.isConfirmed) {
            fetch(`/admin/blog/${currentEditReqPostId}/reject-edit-request`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ reason: result.value || '' })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showSuccessToast(data.message);
                    setTimeout(() => { location.reload(); }, 1000);
                } else {
                    Swal.fire({ title: 'ত্রুটি', text: data.message || 'সমস্যা হয়েছে।', icon: 'error' });
                }
            })
            .catch(() => {
                Swal.fire({ title: 'সার্ভার ত্রুটি', text: 'অনুরোধ সম্পন্ন করতে ব্যর্থ হয়েছে।', icon: 'error' });
            });
        }
    });
}
</script>
@endpush

@endsection
