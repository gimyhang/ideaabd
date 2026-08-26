@extends('layouts.admin')

@section('title', 'Authors & Researchers Directory')
@section('heading', 'Authors & Researchers Directory Management')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Authors Directory</li>
@endsection

@section('actions')
    <div class="d-flex flex-wrap align-items-center gap-2">
        <button type="button" class="btn btn-outline-success btn-sm rounded-pill px-3 shadow-xs" onclick="exportAuthorsToCSV()" title="Export to CSV file">
            <i class="fas fa-file-csv me-1"></i> Export (CSV)
        </button>
        <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-xs" onclick="window.print()" title="Print List">
            <i class="fas fa-print me-1"></i> Print
        </button>
        <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 fw-bold shadow-xs" onclick="openAddAuthorModal()">
            <i class="fas fa-plus-circle me-1"></i> Add New Author
        </button>
        <a href="{{ route('authors.index') }}" target="_blank" rel="noopener" class="btn btn-outline-dark btn-sm rounded-pill px-3 shadow-xs">
            <i class="fas fa-arrow-up-right-from-square me-1"></i> View on Storefront
        </a>
    </div>
@endsection

@section('content')
<div class="d-flex flex-column gap-3 mb-4">

    {{-- Flash Notifications --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-0 shadow-xs rounded-3 border-0 bg-success-subtle text-success-emphasis" role="alert">
            <i class="fas fa-circle-check fs-5 me-2 text-success"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- 1. KPI STAT METRICS CARDS                                                 --}}
    {{-- ========================================================================= --}}
    <div class="row g-2 g-md-2.5">
        <div class="col-6 col-md-4 col-xl">
            <a href="{{ route('admin.authors') }}" class="text-decoration-none">
                <div class="card border-0 shadow-xs rounded-3 p-2.5 bg-white h-100 transition-hover border-start border-4 border-primary {{ !request()->hasAny(['is_active', 'is_verified', 'has_books']) ? 'ring-2 ring-primary' : '' }}">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-semibold d-block" style="font-size: 0.75rem;">Total Authors</span>
                            <h5 class="fw-bold mb-0 text-dark">{{ number_format($stats['total'] ?? 0) }} <small class="text-muted fw-normal" style="font-size: 0.72rem;">authors</small></h5>
                        </div>
                        <div class="rounded-circle bg-primary-subtle text-primary p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                            <i class="fas fa-pen-fancy small"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-4 col-xl">
            <a href="{{ route('admin.authors', array_merge(request()->except(['is_active', 'page']), ['is_active' => '1'])) }}" class="text-decoration-none">
                <div class="card border-0 shadow-xs rounded-3 p-2.5 bg-white h-100 transition-hover border-start border-4 border-success {{ request('is_active') === '1' ? 'ring-2 ring-success' : '' }}">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-semibold d-block" style="font-size: 0.75rem;">Active Authors</span>
                            <h5 class="fw-bold mb-0 text-success">{{ number_format($stats['active'] ?? 0) }} <small class="text-muted fw-normal" style="font-size: 0.72rem;">authors</small></h5>
                        </div>
                        <div class="rounded-circle bg-success-subtle text-success p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                            <i class="fas fa-user-check small"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-4 col-xl">
            <a href="{{ route('admin.authors', array_merge(request()->except(['is_verified', 'page']), ['is_verified' => '1'])) }}" class="text-decoration-none">
                <div class="card border-0 shadow-xs rounded-3 p-2.5 bg-white h-100 transition-hover border-start border-4 border-info {{ request('is_verified') === '1' ? 'ring-2 ring-info' : '' }}">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-semibold d-block" style="font-size: 0.75rem;">Verified Authors</span>
                            <h5 class="fw-bold mb-0 text-info">{{ number_format($stats['verified'] ?? 0) }} <small class="text-muted fw-normal" style="font-size: 0.72rem;">authors</small></h5>
                        </div>
                        <div class="rounded-circle bg-info-subtle text-info p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                            <i class="fas fa-certificate small"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-4 col-xl">
            <a href="{{ route('admin.authors', array_merge(request()->except(['has_books', 'page']), ['has_books' => '1'])) }}" class="text-decoration-none">
                <div class="card border-0 shadow-xs rounded-3 p-2.5 bg-white h-100 transition-hover border-start border-4 border-warning {{ request('has_books') === '1' ? 'ring-2 ring-warning' : '' }}">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-semibold d-block" style="font-size: 0.75rem;">Published Authors</span>
                            <h5 class="fw-bold mb-0 text-warning-emphasis">{{ number_format($stats['with_books'] ?? 0) }} <small class="text-muted fw-normal" style="font-size: 0.72rem;">authors</small></h5>
                        </div>
                        <div class="rounded-circle bg-warning-subtle text-warning-emphasis p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                            <i class="fas fa-book-open small"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-12 col-md-4 col-xl">
            <div class="card border-0 shadow-xs rounded-3 p-2.5 bg-white h-100 border-start border-4 border-secondary">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold d-block" style="font-size: 0.75rem;">Total Catalog Books</span>
                        <h5 class="fw-bold mb-0 text-dark">{{ number_format($stats['total_books'] ?? 0) }} <small class="text-muted fw-normal" style="font-size: 0.72rem;">books</small></h5>
                    </div>
                    <div class="rounded-circle bg-secondary-subtle text-secondary p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                        <i class="fas fa-layer-group small"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- 2. ADVANCED FILTERS & VIEW MODE SWITCHER                                   --}}
    {{-- ========================================================================= --}}
    <div class="card border-0 shadow-xs rounded-3 bg-white">
        <div class="card-body p-2.5">
            <form action="{{ route('admin.authors') }}" method="GET" id="authorsFilterForm" class="row g-2 align-items-center">
                {{-- Search Box --}}
                <div class="col-12 col-lg-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0 text-muted ps-2.5">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="search" name="search" class="form-control border-start-0 bg-light" 
                               placeholder="Search by author name, slug, phone or bio..." value="{{ request('search') }}">
                    </div>
                </div>

                {{-- Status Filter --}}
                <div class="col-6 col-md-3 col-lg-2">
                    <select name="is_active" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="" @selected(request('is_active') === null || request('is_active') === '')>All Status</option>
                        <option value="1" @selected(request('is_active') === '1')>🟢 Active</option>
                        <option value="0" @selected(request('is_active') === '0')>🔴 Inactive</option>
                    </select>
                </div>

                {{-- Verification Filter --}}
                <div class="col-6 col-md-3 col-lg-2">
                    <select name="is_verified" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="" @selected(request('is_verified') === null || request('is_verified') === '')>All Verifications</option>
                        <option value="1" @selected(request('is_verified') === '1')>✓ Verified</option>
                        <option value="0" @selected(request('is_verified') === '0')>Unverified</option>
                    </select>
                </div>

                {{-- Sort Filter --}}
                <div class="col-6 col-md-3 col-lg-2">
                    <select name="sort" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="latest" @selected(request('sort') === 'latest' || !request('sort'))>Newest Added</option>
                        <option value="oldest" @selected(request('sort') === 'oldest')>Oldest First</option>
                        <option value="name_asc" @selected(request('sort') === 'name_asc')>Name (A-Z)</option>
                        <option value="name_desc" @selected(request('sort') === 'name_desc')>Name (Z-A)</option>
                        <option value="books_desc" @selected(request('sort') === 'books_desc')>Most Books</option>
                    </select>
                </div>

                {{-- Per Page & View Buttons --}}
                <div class="col-6 col-md-3 col-lg-2 d-flex align-items-center justify-content-end gap-1.5">
                    <select name="per_page" class="form-select form-select-sm w-auto" onchange="this.form.submit()" title="Items per page">
                        <option value="14" @selected(request('per_page') == 14)>14</option>
                        <option value="21" @selected(request('per_page') == 21)>21</option>
                        <option value="28" @selected(request('per_page') == 28 || !request('per_page'))>28</option>
                        <option value="35" @selected(request('per_page') == 35)>35</option>
                        <option value="42" @selected(request('per_page') == 42)>42</option>
                        <option value="70" @selected(request('per_page') == 70)>70</option>
                    </select>

                    <div class="btn-group btn-group-sm shadow-xs" role="group" aria-label="View Mode">
                        <button type="button" class="btn btn-outline-primary active" id="btnViewGrid" onclick="switchViewMode('grid')" title="7-Column Grid View">
                            <i class="fas fa-th"></i>
                        </button>
                        <button type="button" class="btn btn-outline-primary" id="btnViewTable" onclick="switchViewMode('table')" title="Table View">
                            <i class="fas fa-list"></i>
                        </button>
                    </div>

                    @if(request()->hasAny(['search', 'is_active', 'is_verified', 'has_books', 'sort', 'per_page']))
                        <a href="{{ route('admin.authors') }}" class="btn btn-sm btn-light border text-danger" title="Reset Filters">
                            <i class="fas fa-rotate-left"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- 3. MAIN CONTENT: COMPACT 7-COLUMN GRID VIEW & TABLE VIEW                  --}}
    {{-- ========================================================================= --}}
    @if ($authors->isEmpty())
        <div class="card border-0 shadow-xs rounded-4 bg-white p-5 text-center my-3">
            <div class="mb-3">
                <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center p-3" style="width: 60px; height: 60px;">
                    <i class="fas fa-pen-fancy fs-3 text-muted opacity-50"></i>
                </div>
            </div>
            <h6 class="fw-bold text-dark mb-1">No Authors Found</h6>
            <p class="text-muted small mb-3">Adjust your search filters or add a new author to the directory.</p>
            <div class="d-flex justify-content-center gap-2">
                <a href="{{ route('admin.authors') }}" class="btn btn-sm btn-light border rounded-pill px-3">Clear Filters</a>
                <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 fw-bold" onclick="openAddAuthorModal()">
                    <i class="fas fa-plus me-1"></i> Add New Author
                </button>
            </div>
        </div>
    @else

        {{-- 3A. COMPACT 7-COLUMN GRID VIEW --}}
        <div id="authorsGridView" class="view-container">
            <div class="authors-7col-grid">
                @foreach ($authors as $author)
                    @php
                        $avatarUrl = $author->avatar_url;
                        $initials = $author->initials;
                        $bgColor = $author->avatar_bg_color;
                        $booksCount = $author->books_count ?? 0;
                    @endphp
                    <div class="author-grid-item" id="authorCard-{{ $author->id }}">
                        <div class="card h-100 border-0 shadow-xs rounded-3 overflow-hidden author-compact-card position-relative bg-white transition-all">
                            
                            {{-- Micro Top Banner Stripe --}}
                            <div class="author-card-banner position-relative d-flex align-items-center justify-content-between px-2" style="height: 28px; background: {{ $bgColor }};">
                                {{-- Status & Verified Micro Dots --}}
                                <div class="d-flex gap-1 align-items-center">
                                    <button type="button" 
                                            class="badge rounded-circle border-0 p-0 d-inline-flex align-items-center justify-content-center cursor-pointer {{ $author->is_active ? 'bg-success' : 'bg-secondary' }}"
                                            style="width: 14px; height: 14px; font-size: 7px;"
                                            onclick="toggleAuthorStatus({{ $author->id }}, this)"
                                            title="{{ $author->is_active ? 'Active (Click to disable)' : 'Inactive (Click to enable)' }}">
                                        <i class="fas fa-power-off text-white"></i>
                                    </button>

                                    @if($author->is_verified)
                                        <span class="badge bg-white text-info rounded-circle p-0 d-inline-flex align-items-center justify-content-center shadow-xs" 
                                              style="width: 14px; height: 14px; font-size: 8px;" title="Verified Author">
                                            <i class="fas fa-check"></i>
                                        </span>
                                    @endif
                                </div>

                                {{-- Dropdown Action Menu --}}
                                <div class="dropdown">
                                    <button class="btn btn-xs text-white bg-dark bg-opacity-25 border-0 rounded-circle p-0 d-flex align-items-center justify-content-center" 
                                            style="width: 18px; height: 18px; font-size: 10px;" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3 text-small" style="font-size: 0.8rem;">
                                        <li>
                                            <button class="dropdown-item py-1" type="button" onclick="openAuthorDetailsModal({{ $author->id }})">
                                                <i class="fas fa-id-card text-info me-1.5"></i>Profile
                                            </button>
                                        </li>
                                        <li>
                                            <button class="dropdown-item py-1" type="button" onclick="openEditAuthorModal({{ $author->id }})">
                                                <i class="fas fa-pen text-primary me-1.5"></i>Quick Edit
                                            </button>
                                        </li>
                                        <li>
                                            <a class="dropdown-item py-1" href="{{ route('admin.content.edit', ['type' => 'authors', 'id' => $author->id]) }}">
                                                <i class="fas fa-sliders text-secondary me-1.5"></i>Full Edit
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item py-1" href="{{ route('authors.show', $author->slug ?: $author->id) }}" target="_blank" rel="noopener">
                                                <i class="fas fa-arrow-up-right-from-square text-muted me-1.5"></i>View on Site
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider my-1"></li>
                                        <li>
                                            <form action="{{ route('admin.content.destroy', ['type' => 'authors', 'id' => $author->id]) }}" 
                                                  method="POST" onsubmit="return confirm('Are you sure you want to delete this author?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger py-1">
                                                    <i class="fas fa-trash-can me-1.5"></i>Delete
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            {{-- Avatar (Compact Overlap) --}}
                            <div class="text-center position-relative px-2" style="margin-top: -22px;">
                                <div class="rounded-circle overflow-hidden shadow-xs bg-white border border-2 border-white mx-auto position-relative"
                                     style="width: 44px; height: 44px; min-width: 44px; min-height: 44px; aspect-ratio: 1 / 1; cursor: pointer;"
                                     onclick="previewAuthorAvatar('{{ $avatarUrl }}', '{{ addslashes($author->name) }}')">
                                    @if($avatarUrl)
                                        <img src="{{ $avatarUrl }}" alt="{{ $author->name }}" 
                                             class="w-100 h-100 object-fit-cover position-absolute top-0 start-0"
                                             onerror="this.style.display='none'; this.parentElement.querySelector('.avatar-fallback').style.display='flex';">
                                        <div class="avatar-fallback w-100 h-100 align-items-center justify-content-center text-white fw-bold position-absolute top-0 start-0"
                                             style="display: none; background: {{ $bgColor }}; font-size: 0.95rem;">
                                            {{ $initials }}
                                        </div>
                                    @else
                                        <div class="w-100 h-100 d-flex align-items-center justify-content-center text-white fw-bold position-absolute top-0 start-0"
                                             style="background: {{ $bgColor }}; font-size: 0.95rem;">
                                            {{ $initials }}
                                        </div>
                                    @endif
                                </div>

                                {{-- Name & Slug --}}
                                <div class="mt-1">
                                    <div class="fw-bold text-dark text-truncate" style="font-size: 0.8rem;" title="{{ $author->name }}">
                                        <a href="javascript:void(0)" onclick="openAuthorDetailsModal({{ $author->id }})" class="text-decoration-none text-dark hover-primary">
                                            {{ $author->name }}
                                        </a>
                                    </div>
                                    <div class="text-muted text-truncate font-monospace" style="font-size: 0.65rem;" title="{{ $author->slug }}">
                                        {{ $author->slug }}
                                    </div>
                                </div>
                            </div>

                            {{-- Compact Card Body --}}
                            <div class="card-body p-2 pt-1 text-center d-flex flex-column justify-content-between flex-grow-1">
                                {{-- Book Count Badge --}}
                                <div class="mb-1">
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2 py-0.5" style="font-size: 0.68rem;">
                                        <i class="fas fa-book-bookmark me-1"></i>{{ $booksCount }} books
                                    </span>
                                </div>

                                {{-- Action Micro Buttons --}}
                                <div class="d-flex align-items-center justify-content-center gap-1 pt-1.5 border-top">
                                    <button type="button" class="btn btn-xs btn-outline-info rounded-circle p-0 d-flex align-items-center justify-content-center" 
                                            style="width: 22px; height: 22px; font-size: 9px;" onclick="openAuthorDetailsModal({{ $author->id }})" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-xs btn-outline-primary rounded-circle p-0 d-flex align-items-center justify-content-center" 
                                            style="width: 22px; height: 22px; font-size: 9px;" onclick="openEditAuthorModal({{ $author->id }})" title="Quick Edit">
                                        <i class="fas fa-pen"></i>
                                    </button>
                                    <button type="button" class="btn btn-xs btn-outline-warning rounded-circle p-0 d-flex align-items-center justify-content-center" 
                                            style="width: 22px; height: 22px; font-size: 9px;" onclick="openAuthorPasswordResetModal({{ $author->id }}, '{{ addslashes($author->name) }}', '{{ addslashes($author->email ?: ($author->phone ?: '')) }}')" title="পাসওয়ার্ড রিসেট (Reset Password)">
                                        <i class="fas fa-key"></i>
                                    </button>
                                    <a href="{{ route('authors.show', $author->slug ?: $author->id) }}" target="_blank" rel="noopener" 
                                       class="btn btn-xs btn-light border rounded-circle p-0 d-flex align-items-center justify-content-center" 
                                       style="width: 22px; height: 22px; font-size: 9px;" title="View on Site">
                                        <i class="fas fa-external-link-alt text-muted"></i>
                                    </a>
                                </div>
                            </div>

                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- 3B. REFINED TABLE VIEW --}}
        <div id="authorsTableView" class="view-container d-none">
            <div class="card border-0 shadow-xs rounded-3 overflow-hidden bg-white">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="authorsTable">
                        <thead class="table-light text-muted small text-uppercase">
                            <tr>
                                <th class="ps-3" style="width: 50px;">#</th>
                                <th style="min-width: 200px;">Author & Slug</th>
                                <th>Contact</th>
                                <th>Books Count</th>
                                <th>Verification</th>
                                <th>Status</th>
                                <th>Joined Date</th>
                                <th class="text-end pe-3" style="min-width: 120px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($authors as $n => $author)
                                @php
                                    $avatarUrl = $author->avatar_url;
                                    $initials = $author->initials;
                                    $bgColor = $author->avatar_bg_color;
                                    $booksCount = $author->books_count ?? 0;
                                @endphp
                                <tr id="authorRow-{{ $author->id }}">
                                    <td class="ps-3 text-muted small">{{ $authors->firstItem() + $n }}</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="rounded-circle overflow-hidden shadow-xs flex-shrink-0 position-relative border"
                                                 style="width: 38px; height: 38px; cursor: pointer;"
                                                 onclick="previewAuthorAvatar('{{ $avatarUrl }}', '{{ addslashes($author->name) }}')">
                                                @if($avatarUrl)
                                                    <img src="{{ $avatarUrl }}" alt="{{ $author->name }}" 
                                                         class="w-100 h-100 object-fit-cover position-absolute top-0 start-0"
                                                         onerror="this.style.display='none'; this.parentElement.querySelector('.avatar-fallback').style.display='flex';">
                                                    <div class="avatar-fallback w-100 h-100 align-items-center justify-content-center text-white fw-bold small position-absolute top-0 start-0"
                                                         style="display: none; background: {{ $bgColor }};">
                                                        {{ $initials }}
                                                    </div>
                                                @else
                                                    <div class="w-100 h-100 d-flex align-items-center justify-content-center text-white fw-bold small position-absolute top-0 start-0"
                                                         style="background: {{ $bgColor }};">
                                                        {{ $initials }}
                                                    </div>
                                                @endif
                                            </div>

                                            <div class="min-w-0">
                                                <div class="fw-bold text-dark text-truncate small">
                                                    <a href="javascript:void(0)" onclick="openAuthorDetailsModal({{ $author->id }})" class="text-decoration-none text-dark hover-primary">
                                                        {{ $author->name }}
                                                    </a>
                                                    @if($author->is_verified)
                                                        <i class="fas fa-check-circle text-info ms-1" style="font-size: 11px;" title="Verified Author"></i>
                                                    @endif
                                                </div>
                                                <div class="text-muted font-monospace d-flex align-items-center gap-1" style="font-size: 0.72rem;">
                                                    <span>{{ $author->slug }}</span>
                                                    <i class="fas fa-copy cursor-pointer text-muted hover-primary" onclick="copyToClipboard('{{ $author->slug }}', 'Slug copied to clipboard!')" title="Copy Slug"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="min-width: 140px;">
                                        @if($author->phone)
                                            <div class="text-nowrap small mb-0.5" style="font-size: 0.78rem;"><i class="fas fa-phone-alt text-muted me-1" style="font-size: 10px;"></i>{{ $author->phone }}</div>
                                        @endif
                                        @if($author->email)
                                            <div class="text-muted small text-truncate" style="font-size: 0.75rem; max-width: 160px;" title="{{ $author->email }}"><i class="fas fa-envelope text-muted me-1" style="font-size: 10px;"></i>{{ $author->email }}</div>
                                        @endif
                                        @if(!$author->phone && !$author->email)
                                            <span class="text-muted small">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-0.5 rounded-pill" style="font-size: 0.72rem;">
                                            <i class="fas fa-book me-1"></i>{{ $booksCount }} books
                                        </span>
                                    </td>
                                    <td>
                                        <button type="button" 
                                                class="badge rounded-pill border-0 shadow-xs cursor-pointer px-2 py-0.5 {{ $author->is_verified ? 'bg-info text-white' : 'bg-light text-muted border' }}"
                                                style="font-size: 0.70rem;"
                                                onclick="toggleAuthorVerified({{ $author->id }}, this)"
                                                title="Toggle Verification">
                                            <i class="fas {{ $author->is_verified ? 'fa-certificate' : 'fa-circle-question' }} me-1"></i>
                                            <span>{{ $author->is_verified ? 'Verified' : 'Regular' }}</span>
                                        </button>
                                    </td>
                                    <td>
                                        <button type="button" 
                                                class="badge rounded-pill border-0 shadow-xs cursor-pointer px-2 py-0.5 {{ $author->is_active ? 'bg-success text-white' : 'bg-secondary text-white' }}"
                                                style="font-size: 0.70rem;"
                                                onclick="toggleAuthorStatus({{ $author->id }}, this)"
                                                title="Toggle Status">
                                            <i class="fas fa-circle-dot me-1" style="font-size: 7px;"></i>
                                            <span>{{ $author->is_active ? 'Active' : 'Inactive' }}</span>
                                        </button>
                                    </td>
                                    <td class="text-muted small" style="font-size: 0.75rem;">{{ $author->created_at ? $author->created_at->format('d M, Y') : '—' }}</td>
                                    <td class="text-end pe-3">
                                        <div class="d-inline-flex gap-1 align-items-center">
                                            <button type="button" class="btn btn-xs btn-outline-info p-1" onclick="openAuthorDetailsModal({{ $author->id }})" title="View Profile">
                                                <i class="fas fa-eye small"></i>
                                            </button>
                                            <button type="button" class="btn btn-xs btn-outline-primary p-1" onclick="openEditAuthorModal({{ $author->id }})" title="Quick Edit">
                                                <i class="fas fa-pen-to-square small"></i>
                                            </button>
                                            <button type="button" class="btn btn-xs btn-outline-warning p-1" onclick="openAuthorPasswordResetModal({{ $author->id }}, '{{ addslashes($author->name) }}', '{{ addslashes($author->email ?: ($author->phone ?: '')) }}')" title="পাসওয়ার্ড রিসেট (Reset Password)">
                                                <i class="fas fa-key small"></i>
                                            </button>
                                            <a href="{{ route('authors.show', $author->slug ?: $author->id) }}" target="_blank" rel="noopener" class="btn btn-xs btn-light border p-1" title="View on Site">
                                                <i class="fas fa-arrow-up-right-from-square text-muted small"></i>
                                            </a>
                                            <form action="{{ route('admin.content.destroy', ['type' => 'authors', 'id' => $author->id]) }}" 
                                                  method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this author?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-xs btn-outline-danger p-1" title="Delete Author">
                                                    <i class="fas fa-trash-can small"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Pagination --}}
        @if ($authors->hasPages())
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 p-2.5 bg-white border-0 shadow-xs rounded-3 mt-3">
                <span class="text-muted small" style="font-size: 0.78rem;">
                    Showing {{ $authors->firstItem() }}–{{ $authors->lastItem() }} of {{ number_format($stats['total'] ?? $authors->total()) }} authors
                </span>
                <div>
                    {{ $authors->links() }}
                </div>
            </div>
        @endif

    @endif
</div>

{{-- ========================================================================= --}}
{{-- 4. MODALS (ADD, EDIT, DETAILS, AVATAR PREVIEW)                            --}}
{{-- ========================================================================= --}}

{{-- Modal: Add Author --}}
<div class="modal fade" id="addAuthorModal" tabindex="-1" aria-labelledby="addAuthorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-light border-0 py-3 px-4 rounded-top-4">
                <h5 class="modal-title fw-bold text-dark" id="addAuthorModalLabel">
                    <i class="fas fa-user-pen text-primary me-2"></i>Add New Author
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addAuthorForm" onsubmit="submitAddAuthor(event)" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-7">
                            <label class="form-label small fw-bold text-dark">Author Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Humayun Ahmed" required oninput="generateSlugPreview(this.value, 'addAuthorSlug')">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small fw-bold text-dark">URL Slug (Optional)</label>
                            <input type="text" name="slug" id="addAuthorSlug" class="form-control font-monospace" placeholder="humayun-ahmed">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Phone Number</label>
                            <input type="text" name="phone" class="form-control" placeholder="017XXXXXXXX">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Email Address</label>
                            <input type="email" name="email" class="form-control" placeholder="author@example.com">
                        </div>

                        <div class="col-md-12">
                            <label class="form-label small fw-bold text-dark">Personal Website / Portfolio</label>
                            <input type="url" name="website" class="form-control" placeholder="https://...">
                        </div>

                        <div class="col-md-12">
                            <label class="form-label small fw-bold text-dark">Author Biography & Introduction</label>
                            <textarea name="bio" class="form-control" rows="3" placeholder="Author background, literary career and profile..."></textarea>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label small fw-bold text-dark">Author Photo (Avatar)</label>
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle overflow-hidden bg-light border border-2 border-primary-subtle d-flex align-items-center justify-content-center shadow-xs flex-shrink-0" 
                                     style="width: 54px; height: 54px;" id="addAvatarPreviewBox">
                                    <i class="fas fa-image text-muted fs-4"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <input type="file" name="avatar_file" class="form-control form-control-sm" accept="image/*" onchange="previewImageInput(this, 'addAvatarPreviewBox')">
                                    <div class="form-text small text-muted">JPG, PNG or WebP format.</div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="is_active" id="addAuthorActive" value="1" checked>
                                <label class="form-check-label small fw-semibold" for="addAuthorActive">Active on Storefront</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="is_verified" id="addAuthorVerified" value="1">
                                <label class="form-check-label small fw-semibold" for="addAuthorVerified">Verified Author</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 py-3 px-4 rounded-bottom-4">
                    <button type="button" class="btn btn-light border rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold" id="btnAddAuthorSubmit">
                        <i class="fas fa-save me-1"></i> Save Author
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal: Quick Edit Author --}}
<div class="modal fade" id="editAuthorModal" tabindex="-1" aria-labelledby="editAuthorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-light border-0 py-3 px-4 rounded-top-4">
                <h5 class="modal-title fw-bold text-dark" id="editAuthorModalLabel">
                    <i class="fas fa-pen-to-square text-primary me-2"></i>Edit Author Information
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editAuthorForm" onsubmit="submitEditAuthor(event)" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="author_id" id="editAuthorId">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-7">
                            <label class="form-label small fw-bold text-dark">Author Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="editAuthorName" class="form-control" required>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small fw-bold text-dark">URL Slug</label>
                            <input type="text" name="slug" id="editAuthorSlug" class="form-control font-monospace">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Phone Number</label>
                            <input type="text" name="phone" id="editAuthorPhone" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Email Address</label>
                            <input type="email" name="email" id="editAuthorEmail" class="form-control">
                        </div>

                        <div class="col-md-12">
                            <label class="form-label small fw-bold text-dark">Personal Website / Portfolio</label>
                            <input type="url" name="website" id="editAuthorWebsite" class="form-control">
                        </div>

                        <div class="col-md-12">
                            <label class="form-label small fw-bold text-dark">Author Biography & Introduction</label>
                            <textarea name="bio" id="editAuthorBio" class="form-control" rows="3"></textarea>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label small fw-bold text-dark">Upload New Author Photo</label>
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle overflow-hidden bg-light border border-2 border-primary-subtle d-flex align-items-center justify-content-center shadow-xs flex-shrink-0" 
                                     style="width: 54px; height: 54px;" id="editAvatarPreviewBox">
                                    <i class="fas fa-image text-muted fs-4"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <input type="file" name="avatar_file" class="form-control form-control-sm" accept="image/*" onchange="previewImageInput(this, 'editAvatarPreviewBox')">
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="is_active" id="editAuthorActive" value="1">
                                <label class="form-check-label small fw-semibold" for="editAuthorActive">Active on Site</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="is_verified" id="editAuthorVerified" value="1">
                                <label class="form-check-label small fw-semibold" for="editAuthorVerified">Verified Author</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 py-3 px-4 rounded-bottom-4">
                    <button type="button" class="btn btn-light border rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold" id="btnEditAuthorSubmit">
                        <i class="fas fa-save me-1"></i> Update Author
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal: Author Details & Published Books Quick View --}}
<div class="modal fade" id="authorDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-0 py-3 px-4 text-white" id="authorDetailsHeader" style="background: linear-gradient(135deg, #1e293b, #334155);">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle overflow-hidden shadow-sm border border-2 border-white bg-white flex-shrink-0" 
                         style="width: 52px; height: 52px;" id="detailsAvatarBox"></div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="detailsAuthorName">Loading...</h5>
                        <div class="small opacity-75 font-monospace" id="detailsAuthorSlug"></div>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="authorDetailsBody">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                    <div class="small text-muted mt-2">Loading author profile and catalog books...</div>
                </div>
            </div>
            <div class="modal-footer bg-light border-0 py-3 px-4">
                <a href="#" id="detailsSiteLink" target="_blank" rel="noopener" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                    <i class="fas fa-arrow-up-right-from-square me-1"></i> View Store Profile
                </a>
                <a href="#" id="detailsEditLink" class="btn btn-primary btn-sm rounded-pill px-3 fw-bold">
                    <i class="fas fa-pen-to-square me-1"></i> Full Edit
                </a>
            </div>
        </div>
    </div>
</div>

{{-- Modal: Avatar Lightbox Preview --}}
<div class="modal fade" id="avatarLightboxModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden bg-dark text-white text-center">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title small fw-bold text-white-50" id="avatarLightboxTitle">Author Photo</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="rounded-circle overflow-hidden shadow-lg mx-auto border border-4 border-white mb-2" 
                     style="width: 160px; height: 160px; background: #334155;">
                    <img src="" id="avatarLightboxImg" class="w-100 h-100 object-fit-cover" alt="Author Photo">
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal: Author Fast Password Reset --}}
<div class="modal fade" id="resetAuthorPasswordModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-warning text-dark border-0 p-3 px-4">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="bg-white bg-opacity-50 rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                        <i class="fas fa-key text-dark"></i>
                    </div>
                    <div>
                        <h6 class="modal-title fw-bold mb-0">লেখকের পাসওয়ার্ড রিসেট</h6>
                        <small class="text-dark-50 fw-semibold" id="resetModalAuthorName">লেখক নাম</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 bg-light">
                <form id="authorPasswordResetForm" onsubmit="submitAuthorPasswordReset(event)">
                    @csrf
                    <input type="hidden" id="resetModalAuthorId" name="author_id">

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary mb-1">লগইন আইডি / ইমেইল / ফোন</label>
                        <input type="text" id="resetModalIdentity" class="form-control form-control-sm bg-white" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary mb-1 d-flex align-items-center justify-content-between">
                            <span>নতুন পাসওয়ার্ড লিখুন অথবা স্বয়ংক্রিয় তৈরি করুন:</span>
                            <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none text-primary fw-semibold small" onclick="generateRandomAuthorPassword()">
                                <i class="fas fa-dice me-1"></i>স্বয়ংক্রিয় পাসওয়ার্ড
                            </button>
                        </label>
                        <div class="input-group">
                            <input type="text" name="password" id="resetModalNewPassword" class="form-control fw-bold font-monospace bg-white" placeholder="যেমন: Idea@3842" required minlength="6">
                            <button class="btn btn-outline-secondary" type="button" onclick="copyResetPasswordToClipboard()" title="পাসওয়ার্ড কপি">
                                <i class="far fa-copy"></i>
                            </button>
                        </div>
                        <small class="text-muted" style="font-size: 11px;">সর্বনিম্ন ৬ অক্ষর (যেমন: 123456 বা Idea@1234)</small>
                    </div>

                    <div id="resetResultCard" class="d-none p-3 bg-white border border-success-subtle rounded-3 shadow-xs mb-3">
                        <div class="d-flex align-items-center gap-2 text-success fw-bold small mb-2">
                            <i class="fas fa-circle-check"></i>
                            <span>পাসওয়ার্ড সফলভাবে রিসেট হয়েছে!</span>
                        </div>
                        <div class="small text-muted mb-2">
                            <div><strong>লগইন আইডি:</strong> <span id="resLoginId" class="text-dark font-monospace fw-semibold"></span></div>
                            <div><strong>নতুন পাসওয়ার্ড:</strong> <span id="resPassword" class="text-danger fw-bold font-monospace"></span></div>
                            <div><strong>লগইন লিংক:</strong> <a href="{{ route('login') }}" target="_blank" class="text-primary text-decoration-none">{{ route('login') }}</a></div>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" onclick="copyFullCredentials()">
                                <i class="far fa-copy me-1"></i>তথ্য কপি করুন
                            </button>
                            <a href="#" id="resWhatsappBtn" target="_blank" class="btn btn-sm btn-success rounded-pill px-3 d-none">
                                <i class="fab fa-whatsapp me-1"></i>WhatsApp এ পাঠান
                            </a>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-2 border-top">
                        <button type="button" class="btn btn-sm btn-light border rounded-pill px-3" data-bs-dismiss="modal">বন্ধ করুন</button>
                        <button type="submit" class="btn btn-sm btn-warning fw-bold rounded-pill px-4" id="btnSubmitPasswordReset">
                            <i class="fas fa-save me-1"></i>পাসওয়ার্ড সংরক্ষণ করুন
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Toast Container for dynamic notifications --}}
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1090;">
    <div id="actionToast" class="toast align-items-center text-white bg-dark border-0 shadow-lg rounded-3" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body d-flex align-items-center gap-2">
                <i class="fas fa-circle-check text-success fs-5" id="toastIcon"></i>
                <span id="toastMessage">Operation completed successfully</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

{{-- Custom CSS for 7-Column Layout & Ultra-Compact Card Height --}}
<style>
.authors-7col-grid {
    display: grid;
    grid-template-columns: repeat(7, minmax(0, 1fr));
    gap: 10px;
}
@media (max-width: 1400px) {
    .authors-7col-grid { grid-template-columns: repeat(5, minmax(0, 1fr)); }
}
@media (max-width: 992px) {
    .authors-7col-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); }
}
@media (max-width: 768px) {
    .authors-7col-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
}
@media (max-width: 576px) {
    .authors-7col-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}

.author-compact-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    min-height: 165px;
}
.author-compact-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px -4px rgba(0, 0, 0, 0.1) !important;
}
.ring-2 {
    outline: 2px solid;
    outline-offset: -2px;
}
.ring-primary { outline-color: #4f46e5; }
.ring-success { outline-color: #10b981; }
.ring-info { outline-color: #0ea5e9; }
.ring-warning { outline-color: #f59e0b; }
.cursor-pointer { cursor: pointer; }
.hover-primary:hover { color: #4f46e5 !important; }
.shadow-xs {
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
}
@media print {
    .btn, .breadcrumb, .modal, .toast-container, form { display: none !important; }
    .author-compact-card { break-inside: avoid; border: 1px solid #ddd !important; }
}
</style>

{{-- ========================================================================= --}}
{{-- 5. JAVASCRIPT LOGIC FOR DYNAMIC SWITCHING, AJAX, AND MODALS               --}}
{{-- ========================================================================= --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const savedViewMode = localStorage.getItem('admin_authors_view_mode') || 'grid';
    switchViewMode(savedViewMode);
});

function switchViewMode(mode) {
    const gridView = document.getElementById('authorsGridView');
    const tableView = document.getElementById('authorsTableView');
    const btnGrid = document.getElementById('btnViewGrid');
    const btnTable = document.getElementById('btnViewTable');

    if (!gridView || !tableView) return;

    if (mode === 'table') {
        gridView.classList.add('d-none');
        tableView.classList.remove('d-none');
        if (btnTable) btnTable.classList.add('active');
        if (btnGrid) btnGrid.classList.remove('active');
        localStorage.setItem('admin_authors_view_mode', 'table');
    } else {
        tableView.classList.add('d-none');
        gridView.classList.remove('d-none');
        if (btnGrid) btnGrid.classList.add('active');
        if (btnTable) btnTable.classList.remove('active');
        localStorage.setItem('admin_authors_view_mode', 'grid');
    }
}

function showToast(message, isSuccess = true) {
    const toastEl = document.getElementById('actionToast');
    const toastMsg = document.getElementById('toastMessage');
    const toastIcon = document.getElementById('toastIcon');

    if (!toastEl || !toastMsg) return;

    toastMsg.textContent = message;
    if (toastIcon) {
        toastIcon.className = isSuccess ? 'fas fa-circle-check text-success fs-5' : 'fas fa-triangle-exclamation text-danger fs-5';
    }

    const toast = new bootstrap.Toast(toastEl, { delay: 3500 });
    toast.show();
}

function copyToClipboard(text, successMsg = 'Copied to clipboard!') {
    navigator.clipboard.writeText(text).then(() => {
        showToast(successMsg, true);
    }).catch(err => {
        console.error('Clipboard copy failed: ', err);
    });
}

function openAddAuthorModal() {
    const form = document.getElementById('addAuthorForm');
    if (form) form.reset();
    const preview = document.getElementById('addAvatarPreviewBox');
    if (preview) preview.innerHTML = '<i class="fas fa-image text-muted fs-4"></i>';
    const modal = new bootstrap.Modal(document.getElementById('addAuthorModal'));
    modal.show();
}

function previewImageInput(input, boxId) {
    const box = document.getElementById(boxId);
    if (!box) return;

    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            box.innerHTML = `<img src="${e.target.result}" class="w-100 h-100 object-fit-cover">`;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function generateSlugPreview(text, targetInputId) {
    const target = document.getElementById(targetInputId);
    if (!target) return;
    if (target.dataset.manualEdited === 'true') return;

    const slug = text.trim()
        .toLowerCase()
        .replace(/[^\w\u0980-\u09FF\s-]/g, '')
        .replace(/[\s_-]+/g, '-')
        .replace(/^-+|-+$/g, '');
    target.value = slug;
}

function submitAddAuthor(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    const submitBtn = document.getElementById('btnAddAuthorSubmit');

    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving...';
    }

    fetch("{{ route('admin.authors.quick-store') }}", {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, true);
            const modalEl = document.getElementById('addAuthorModal');
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();
            setTimeout(() => { window.location.reload(); }, 600);
        } else {
            showToast(data.message || 'An error occurred', false);
        }
    })
    .catch(err => {
        console.error(err);
        showToast('Server failed to respond.', false);
    })
    .finally(() => {
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-save me-1"></i> Save Author';
        }
    });
}

function openEditAuthorModal(id) {
    fetch(`/admin/authors/${id}/details`, {
        headers: { 'Accept': 'application/json' }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success && data.author) {
            const a = data.author;
            document.getElementById('editAuthorId').value = a.id;
            document.getElementById('editAuthorName').value = a.name || '';
            document.getElementById('editAuthorSlug').value = a.slug || '';
            document.getElementById('editAuthorPhone').value = a.phone || '';
            document.getElementById('editAuthorEmail').value = a.email || '';
            document.getElementById('editAuthorWebsite').value = a.website || '';
            document.getElementById('editAuthorBio').value = a.bio || '';
            document.getElementById('editAuthorActive').checked = !!a.is_active;
            document.getElementById('editAuthorVerified').checked = !!a.is_verified;

            const preview = document.getElementById('editAvatarPreviewBox');
            if (preview) {
                if (a.avatar_url) {
                    preview.innerHTML = `<img src="${a.avatar_url}" class="w-100 h-100 object-fit-cover">`;
                } else {
                    preview.innerHTML = `<div class="w-100 h-100 d-flex align-items-center justify-content-center text-white fw-bold" style="background: ${a.avatar_bg_color}">${a.initials}</div>`;
                }
            }

            const modal = new bootstrap.Modal(document.getElementById('editAuthorModal'));
            modal.show();
        }
    })
    .catch(err => {
        console.error(err);
        showToast('Failed to load author data', false);
    });
}

function submitEditAuthor(event) {
    event.preventDefault();
    const form = event.target;
    const authorId = document.getElementById('editAuthorId').value;
    const formData = new FormData(form);
    const submitBtn = document.getElementById('btnEditAuthorSubmit');

    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving...';
    }

    fetch(`/admin/authors/${authorId}/quick-update`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, true);
            const modalEl = document.getElementById('editAuthorModal');
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();
            setTimeout(() => { window.location.reload(); }, 600);
        } else {
            showToast(data.message || 'Failed to update author', false);
        }
    })
    .catch(err => {
        console.error(err);
        showToast('Server error occurred.', false);
    })
    .finally(() => {
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-save me-1"></i> Update Author';
        }
    });
}

function toggleAuthorStatus(id, btnElement) {
    btnElement.disabled = true;
    fetch(`/admin/authors/${id}/toggle-status`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, true);
            const isActive = data.is_active;
            btnElement.className = `badge rounded-circle border-0 p-0 d-inline-flex align-items-center justify-content-center cursor-pointer ${isActive ? 'bg-success' : 'bg-secondary'}`;
        }
    })
    .catch(err => {
        console.error(err);
        showToast('Could not toggle status', false);
    })
    .finally(() => {
        btnElement.disabled = false;
    });
}

function toggleAuthorVerified(id, btnElement) {
    btnElement.disabled = true;
    fetch(`/admin/authors/${id}/toggle-verified`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, true);
            const isVerified = data.is_verified;
            btnElement.className = `badge rounded-pill border-0 shadow-xs cursor-pointer px-2 py-0.5 ${isVerified ? 'bg-info text-white' : 'bg-light text-muted border'}`;
            btnElement.innerHTML = `<i class="fas ${isVerified ? 'fa-certificate' : 'fa-circle-question'} me-1"></i><span>${isVerified ? 'Verified' : 'Regular'}</span>`;
        }
    })
    .catch(err => {
        console.error(err);
        showToast('Could not toggle verification', false);
    })
    .finally(() => {
        btnElement.disabled = false;
    });
}

function openAuthorDetailsModal(id) {
    const modalEl = document.getElementById('authorDetailsModal');
    const modal = new bootstrap.Modal(modalEl);
    modal.show();

    fetch(`/admin/authors/${id}/details`, {
        headers: { 'Accept': 'application/json' }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success && data.author) {
            const a = data.author;
            document.getElementById('detailsAuthorName').textContent = a.name;
            document.getElementById('detailsAuthorSlug').textContent = a.slug ? `slug: ${a.slug}` : '';
            
            const headerBox = document.getElementById('authorDetailsHeader');
            if (headerBox && a.avatar_bg_color) {
                headerBox.style.background = a.avatar_bg_color;
            }

            const avatarBox = document.getElementById('detailsAvatarBox');
            if (avatarBox) {
                if (a.avatar_url) {
                    avatarBox.innerHTML = `<img src="${a.avatar_url}" class="w-100 h-100 object-fit-cover">`;
                } else {
                    avatarBox.innerHTML = `<div class="w-100 h-100 d-flex align-items-center justify-content-center text-white fw-bold fs-5" style="background: ${a.avatar_bg_color}">${a.initials}</div>`;
                }
            }

            document.getElementById('detailsEditLink').href = `/admin/content/authors/${a.id}/edit`;
            document.getElementById('detailsSiteLink').href = `/authors/${a.slug || a.id}`;

            let booksHtml = '';
            if (a.books && a.books.length > 0) {
                booksHtml = `
                    <div class="mt-4 pt-3 border-top">
                        <h6 class="fw-bold text-dark mb-3"><i class="fas fa-book text-primary me-2"></i>Catalog Books (${a.books.length} listed)</h6>
                        <div class="row row-cols-1 row-cols-sm-2 g-2">
                            ${a.books.map(b => `
                                <div class="col">
                                    <div class="p-2 border rounded-3 d-flex align-items-center gap-2 bg-light">
                                        <div class="rounded overflow-hidden bg-white border flex-shrink-0" style="width: 36px; height: 48px;">
                                            ${b.cover_image ? `<img src="/storage/${b.cover_image}" class="w-100 h-100 object-fit-cover">` : '<i class="fas fa-book text-muted m-2"></i>'}
                                        </div>
                                        <div class="min-w-0">
                                            <div class="fw-bold small text-truncate">${b.title}</div>
                                            <div class="text-muted small">৳${b.price || 0}</div>
                                        </div>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                `;
            } else {
                booksHtml = `
                    <div class="mt-4 pt-3 border-top text-center text-muted small py-3">
                        <i class="fas fa-book-open opacity-50 mb-1 d-block fs-4"></i>
                        No books listed under this author yet.
                    </div>
                `;
            }

            document.getElementById('authorDetailsBody').innerHTML = `
                <div class="row g-3">
                    <div class="col-sm-6">
                        <small class="text-muted d-block">Phone Number</small>
                        <div class="fw-semibold text-dark">${a.phone || '<span class="text-muted">—</span>'}</div>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted d-block">Email Address</small>
                        <div class="fw-semibold text-dark">${a.email || '<span class="text-muted">—</span>'}</div>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted d-block">Website / Portfolio</small>
                        <div>${a.website ? `<a href="${a.website}" target="_blank" class="text-decoration-none text-primary small text-truncate d-inline-block" style="max-width: 250px;">${a.website}</a>` : '<span class="text-muted">—</span>'}</div>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted d-block">Status & Verification</small>
                        <div class="d-flex gap-2 align-items-center mt-1">
                            <span class="badge ${a.is_active ? 'bg-success' : 'bg-secondary'} rounded-pill">${a.is_active ? 'Active' : 'Inactive'}</span>
                            <span class="badge ${a.is_verified ? 'bg-info' : 'bg-light text-dark border'} rounded-pill">${a.is_verified ? 'Verified' : 'Regular'}</span>
                        </div>
                    </div>
                    <div class="col-12">
                        <small class="text-muted d-block">Biography & Summary</small>
                        <div class="bg-light p-3 rounded-3 small text-dark mt-1" style="max-height: 160px; overflow-y: auto;">
                            ${a.bio ? a.bio : '<em class="text-muted">No biography provided</em>'}
                        </div>
                    </div>
                </div>
                ${booksHtml}
            `;
        }
    })
    .catch(err => {
        console.error(err);
        document.getElementById('authorDetailsBody').innerHTML = '<div class="alert alert-danger mb-0">Could not load author details.</div>';
    });
}

function previewAuthorAvatar(url, name) {
    if (!url) return;
    const img = document.getElementById('avatarLightboxImg');
    const title = document.getElementById('avatarLightboxTitle');
    if (img) img.src = url;
    if (title) title.textContent = name || 'Author Photo';
    const modal = new bootstrap.Modal(document.getElementById('avatarLightboxModal'));
    modal.show();
}

function exportAuthorsToCSV() {
    let csv = [];
    csv.push(['ID', 'Name', 'Slug', 'Phone', 'Email', 'Books Count', 'Status', 'Verified']);

    @foreach($authors as $a)
        csv.push([
            '{{ $a->id }}',
            '"{{ addslashes($a->name) }}"',
            '"{{ $a->slug }}"',
            '"{{ $a->phone }}"',
            '"{{ $a->email }}"',
            '{{ $a->books_count ?? 0 }}',
            '{{ $a->is_active ? "Active" : "Inactive" }}',
            '{{ $a->is_verified ? "Verified" : "Unverified" }}'
        ]);
    @endforeach

    let csvContent = "data:text/csv;charset=utf-8,\uFEFF" + csv.map(e => e.join(",")).join("\n");
    let encodedUri = encodeURI(csvContent);
    let link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", "authors_directory_ideaabd.csv");
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    showToast('CSV export downloaded successfully!', true);
}

// ═════════════════════════════════════════════════════════════════════════════
// AUTHOR FAST PASSWORD RESET JAVASCRIPT HANDLERS
// ═════════════════════════════════════════════════════════════════════════════
let currentResetPayload = null;

function openAuthorPasswordResetModal(authorId, authorName, identity) {
    document.getElementById('resetModalAuthorId').value = authorId;
    document.getElementById('resetModalAuthorName').textContent = authorName || 'লেখক';
    document.getElementById('resetModalIdentity').value = identity || 'অটো-জেনারেটেড আইডি';
    
    generateRandomAuthorPassword();
    document.getElementById('resetResultCard').classList.add('d-none');
    
    const modal = new bootstrap.Modal(document.getElementById('resetAuthorPasswordModal'));
    modal.show();
}

function generateRandomAuthorPassword() {
    const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
    let rand = 'Idea@' + Math.floor(1000 + Math.random() * 9000);
    document.getElementById('resetModalNewPassword').value = rand;
}

function copyResetPasswordToClipboard() {
    const pwd = document.getElementById('resetModalNewPassword').value;
    if (pwd) {
        copyToClipboard(pwd, 'পাসওয়ার্ড কপি করা হয়েছে!');
    }
}

function copyFullCredentials() {
    if (!currentResetPayload) return;
    const text = `আইডিয়া প্রকাশন — লেখক পোর্টাল লগইন তথ্য:\nলগইন আইডি: ${currentResetPayload.login_identity}\nপাসওয়ার্ড: ${currentResetPayload.new_password}\nলগইন লিংক: ${currentResetPayload.login_url}`;
    copyToClipboard(text, 'লগইন ও পাসওয়ার্ড তথ্য কপি হয়েছে!');
}

function submitAuthorPasswordReset(e) {
    e.preventDefault();
    const authorId = document.getElementById('resetModalAuthorId').value;
    const password = document.getElementById('resetModalNewPassword').value;
    const btn = document.getElementById('btnSubmitPasswordReset');
    const originalText = btn.innerHTML;

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>সংরক্ষণ হচ্ছে...';

    fetch(`/admin/authors/${authorId}/reset-password`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            password: password
        })
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = originalText;

        if (data.success) {
            currentResetPayload = data;
            document.getElementById('resLoginId').textContent = data.login_identity;
            document.getElementById('resPassword').textContent = data.new_password;
            
            const waBtn = document.getElementById('resWhatsappBtn');
            if (data.whatsapp_url) {
                waBtn.href = data.whatsapp_url;
                waBtn.classList.remove('d-none');
            } else {
                waBtn.classList.add('d-none');
            }

            document.getElementById('resetResultCard').classList.remove('d-none');
            showToast(data.message, true);
        } else {
            showToast(data.message || 'পাসওয়ার্ড রিসেট ব্যর্থ হয়েছে।', false);
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = originalText;
        console.error(err);
        showToast('সার্ভার এরর: পাসওয়ার্ড রিসেট করা যায়নি।', false);
    });
}
</script>
@endsection
