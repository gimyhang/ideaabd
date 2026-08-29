@extends('layouts.admin')

@section('title', 'E-Books Management & Search')
@section('heading', 'E-Books & Digital Publications')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">E-Books Directory</li>
@endsection

@section('actions')
    <div class="d-flex align-items-center gap-2">
        <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-xs" data-bs-toggle="modal" data-bs-target="#ebookSettingsModal" title="ই-বুক সেটিংস ও প্রিভিউ পেজ লিমিট">
            <i class="fas fa-sliders me-1"></i> Settings
        </button>
        <button type="button" class="btn btn-outline-success btn-sm rounded-pill px-3 shadow-xs" onclick="exportEbooksToCSV()" title="CSV Export">
            <i class="fas fa-file-csv me-1"></i> Export (CSV)
        </button>
        <a href="{{ route('admin.content.create', 'ebooks') }}" class="btn btn-primary btn-sm rounded-pill px-3 fw-bold shadow-xs">
            <i class="fas fa-plus-circle me-1"></i> Upload New E-Book
        </a>
        <a href="{{ route('ebook.index') }}" target="_blank" rel="noopener" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-xs">
            <i class="fas fa-arrow-up-right-from-square me-1"></i> View Library
        </a>
    </div>
@endsection

@section('content')
<div class="d-flex flex-column gap-3">

    {{-- Flash Notifications --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-0 shadow-xs rounded-4" role="alert">
            <i class="fas fa-circle-check fs-5 me-2 text-success"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- 1. KPI SUMMARY STRIP                                                      --}}
    {{-- ========================================================================= --}}
    <div class="row g-2">
        {{-- 1. Total E-Books --}}
        <div class="col-6 col-md-3">
            <a href="{{ route('admin.ebooks') }}" class="text-decoration-none">
                <div class="adm-card p-3 d-flex align-items-center justify-content-between h-100 bg-white rounded-4 shadow-sm border-0 {{ !request()->hasAny(['price_type', 'is_active', 'mod_status']) ? 'border-primary border-2' : '' }}">
                    <div>
                        <small class="text-muted d-block font-sans">Total E-Books</small>
                        <h4 class="fw-bold text-dark mb-0 font-monospace">{{ number_format($stats['total'] ?? 0) }}</h4>
                    </div>
                    <span class="p-2 bg-primary-subtle text-primary rounded-circle fs-5"><i class="fas fa-tablet-screen-button"></i></span>
                </div>
            </a>
        </div>

        {{-- 2. Pending Moderation Queue --}}
        <div class="col-6 col-md-3">
            <a href="{{ route('admin.ebooks', array_merge(request()->except(['mod_status', 'page']), ['mod_status' => 'pending'])) }}" class="text-decoration-none">
                <div class="adm-card p-3 d-flex align-items-center justify-content-between h-100 bg-white rounded-4 shadow-sm border-0 {{ request('mod_status') === 'pending' ? 'border-warning border-2' : '' }}">
                    <div>
                        <small class="text-muted d-block font-sans">Pending Review (KDP)</small>
                        <h4 class="fw-bold text-warning-emphasis mb-0 font-monospace">
                            {{ number_format($stats['pending'] ?? 0) }}
                            @if(($stats['pending'] ?? 0) > 0)
                                <span class="badge bg-danger rounded-pill fs-6 ms-1 animate-pulse">New</span>
                            @endif
                        </h4>
                    </div>
                    <span class="p-2 bg-warning-subtle text-warning rounded-circle fs-5"><i class="fas fa-hourglass-half"></i></span>
                </div>
            </a>
        </div>

        {{-- 3. Active & Live --}}
        <div class="col-6 col-md-3">
            <a href="{{ route('admin.ebooks', array_merge(request()->except(['is_active', 'page']), ['is_active' => '1'])) }}" class="text-decoration-none">
                <div class="adm-card p-3 d-flex align-items-center justify-content-between h-100 bg-white rounded-4 shadow-sm border-0 {{ request('is_active') === '1' ? 'border-success border-2' : '' }}">
                    <div>
                        <small class="text-muted d-block font-sans">Active & Live</small>
                        <h4 class="fw-bold text-success mb-0 font-monospace">{{ number_format($stats['active'] ?? 0) }}</h4>
                    </div>
                    <span class="p-2 bg-success-subtle text-success rounded-circle fs-5"><i class="fas fa-circle-check"></i></span>
                </div>
            </a>
        </div>

        {{-- 4. Total Sold Copies --}}
        <div class="col-6 col-md-3">
            <a href="{{ route('admin.ebooks', array_merge(request()->except(['price_type', 'page']), ['price_type' => 'paid'])) }}" class="text-decoration-none">
                <div class="adm-card p-3 d-flex align-items-center justify-content-between h-100 bg-white rounded-4 shadow-sm border-0 {{ request('price_type') === 'paid' ? 'border-info border-2' : '' }}">
                    <div>
                        <small class="text-muted d-block font-sans">Total Copies Sold</small>
                        <h4 class="fw-bold text-info mb-0 font-monospace">{{ number_format($stats['total_sales'] ?? 0) }}</h4>
                    </div>
                    <span class="p-2 bg-info-subtle text-info rounded-circle fs-5"><i class="fas fa-bag-shopping"></i></span>
                </div>
            </a>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- 2. ADVANCED FILTER & SEARCH TOOLBAR                                       --}}
    {{-- ========================================================================= --}}
    <div class="adm-card p-3 shadow-sm border-0 bg-white rounded-4">
        <form action="{{ route('admin.ebooks') }}" method="GET" id="ebooksFilterForm" class="d-flex flex-column gap-2.5">
            
            <div class="row g-2 align-items-center">
                <!-- Search Bar -->
                <div class="col-12 col-lg-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-search"></i></span>
                        <input type="text" name="search" id="ebookSearchInput" value="{{ request('search') }}" 
                               class="form-control border-start-0 border-end-0 ps-0" 
                               placeholder="Search title, author, publisher, ISBN..." autocomplete="off">
                        @if(request('search'))
                            <a href="{{ route('admin.ebooks', request()->except('search')) }}" class="input-group-text bg-white border-start-0 text-muted hover-danger" title="Clear Search">
                                <i class="fas fa-times"></i>
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Moderation Status Filter -->
                <div class="col-6 col-md-3 col-lg-2">
                    <select name="mod_status" class="form-select form-select-sm rounded-3" onchange="this.form.submit()">
                        <option value="">All Review Status</option>
                        <option value="approved" @selected(request('mod_status') === 'approved')>Approved (Live)</option>
                        <option value="pending" @selected(request('mod_status') === 'pending')>Pending Moderation</option>
                        <option value="rejected" @selected(request('mod_status') === 'rejected')>Revision Needed</option>
                    </select>
                </div>

                <!-- Category Filter -->
                <div class="col-6 col-md-3 col-lg-2">
                    <select name="category_id" class="form-select form-select-sm rounded-3" onchange="this.form.submit()">
                        <option value="">All Categories</option>
                        @foreach ($categories as $catId => $catName)
                            <option value="{{ $catId }}" @selected((string)request('category_id') === (string)$catId)>
                                {{ $catName }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Author Filter -->
                <div class="col-6 col-md-3 col-lg-2">
                    <select name="author_id" class="form-select form-select-sm rounded-3" onchange="this.form.submit()">
                        <option value="">All Authors</option>
                        @foreach ($authors as $aId => $aName)
                            <option value="{{ $aId }}" @selected((string)request('author_id') === (string)$aId)>
                                {{ $aName }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Price Filter -->
                <div class="col-6 col-md-3 col-lg-1.5">
                    <select name="price_type" class="form-select form-select-sm rounded-3" onchange="this.form.submit()">
                        <option value="">All Prices</option>
                        <option value="free" @selected(request('price_type') === 'free')>Free</option>
                        <option value="paid" @selected(request('price_type') === 'paid')>Paid</option>
                    </select>
                </div>

                <!-- Active Status Filter -->
                <div class="col-6 col-md-3 col-lg-1.5">
                    <select name="is_active" class="form-select form-select-sm rounded-3" onchange="this.form.submit()">
                        <option value="">All Status</option>
                        <option value="1" @selected(request('is_active') === '1')>Active Only</option>
                        <option value="0" @selected(request('is_active') === '0')>Inactive Only</option>
                    </select>
                </div>
            </div>

            <!-- Sorting & Per Page -->
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 pt-2 border-top">
                <div class="d-flex align-items-center gap-2">
                    <span class="small text-muted fw-semibold">Sort By:</span>
                    <select name="sort" class="form-select form-select-sm rounded-pill py-0.5" style="width: auto;" onchange="this.form.submit()">
                        <option value="latest" @selected(request('sort', 'latest') === 'latest')>Newest First</option>
                        <option value="oldest" @selected(request('sort') === 'oldest')>Oldest First</option>
                        <option value="title_asc" @selected(request('sort') === 'title_asc')>Title (A-Z)</option>
                        <option value="price_low" @selected(request('sort') === 'price_low')>Price (Low to High)</option>
                        <option value="price_high" @selected(request('sort') === 'price_high')>Price (High to Low)</option>
                        <option value="sales_high" @selected(request('sort') === 'sales_high')>Top Selling Copies</option>
                    </select>

                    <span class="small text-muted fw-semibold ms-2">Show:</span>
                    <select name="per_page" class="form-select form-select-sm rounded-pill py-0.5" style="width: auto;" onchange="this.form.submit()">
                        <option value="10" @selected(request('per_page', 20) == 10)>10</option>
                        <option value="20" @selected(request('per_page', 20) == 20)>20</option>
                        <option value="50" @selected(request('per_page', 20) == 50)>50</option>
                        <option value="100" @selected(request('per_page', 20) == 100)>100</option>
                    </select>
                </div>

                <div class="d-flex align-items-center gap-2">
                    @if(request()->hasAny(['search', 'author_id', 'publisher_id', 'category_id', 'price_type', 'is_active', 'mod_status']))
                        <a href="{{ route('admin.ebooks') }}" class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-semibold">
                            <i class="fas fa-rotate-left me-1"></i> Reset Filters
                        </a>
                    @endif
                </div>
            </div>

        </form>
    </div>

    {{-- ========================================================================= --}}
    {{-- 3. DYNAMIC E-BOOKS DATA TABLE                                             --}}
    {{-- ========================================================================= --}}
    <div class="adm-card p-0 overflow-hidden shadow-sm border-0 rounded-4 bg-white">
        <div class="table-responsive">
            <table class="table adm-table align-middle mb-0" id="adminEbooksTable">
                <thead class="table-light small fw-bold text-secondary">
                    <tr>
                        <th class="ps-3" style="width: 45px;">#</th>
                        <th style="min-width: 250px;">E-Book Title & Specs</th>
                        <th style="min-width: 170px;">Author & Source</th>
                        <th>Category</th>
                        <th>Pricing & Royalty</th>
                        <th>Sales / Reads</th>
                        <th class="text-center" style="min-width: 110px;">Review Status</th>
                        <th class="text-center" style="min-width: 90px;">Live Store</th>
                        <th class="text-end pe-3" style="min-width: 160px;">Actions</th>
                    </tr>
                </thead>
                <tbody class="small">
                    @forelse ($ebooks as $index => $ebook)
                        @php
                            $coverUrl = $ebook->cover_url ?: 'https://placehold.co/100x150/0284c7/ffffff?text=E-Book';
                            $price = (float) $ebook->price;
                            $discount = (float) ($ebook->discount_price ?? 0);
                            $hasDiscount = $discount > 0 && $discount < $price;
                            $isFree = $price <= 0;
                            $isAuthorKdp = !empty($ebook->author_user_id) || !empty($ebook->submitted_by);
                        @endphp
                        <tr id="ebook-row-{{ $ebook->id }}">
                            {{-- Index --}}
                            <td class="ps-3 text-muted font-monospace">
                                {{ ($ebooks->currentPage() - 1) * $ebooks->perPage() + $index + 1 }}
                            </td>
                            
                            {{-- Ebook Cover & Details --}}
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="position-relative flex-shrink-0" style="width: 48px; height: 68px;">
                                        <img src="{{ $coverUrl }}" alt="{{ $ebook->title }}" 
                                             class="rounded border shadow-xs" style="width: 100%; height: 100%; object-fit: cover;">
                                        <span class="badge bg-dark bg-opacity-75 text-white position-absolute bottom-0 start-0 m-0.5 p-0.5 rounded" style="font-size: 8px;">
                                            {{ $ebook->format_badge }}
                                        </span>
                                    </div>
                                    <div class="overflow-hidden" style="max-width: 260px;">
                                        <a href="{{ route('ebook.show', $ebook->slug ?? $ebook->id) }}" target="_blank" 
                                           class="fw-bold text-dark text-decoration-none hover-primary d-block text-truncate mb-0.5" title="{{ $ebook->title }}">
                                            {{ $ebook->title }}
                                        </a>
                                        <div class="d-flex flex-wrap align-items-center gap-1.5 small text-muted" style="font-size: 11px;">
                                            @if($ebook->isbn)
                                                <span class="badge bg-light text-muted border font-monospace px-1.5 py-0.5">{{ $ebook->isbn }}</span>
                                            @endif
                                            @if($ebook->pages)
                                                <span><i class="fas fa-file-lines me-0.5"></i>{{ $ebook->pages }}p</span>
                                            @endif
                                            @if($ebook->sample_file_path)
                                                <span class="badge bg-info-subtle text-info" style="font-size: 9.5px;">Sample Preview</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>

                            {{-- Author & Submitter Source --}}
                            <td>
                                <div class="fw-semibold text-dark small mb-0.5">
                                    @if($ebook->authorLink)
                                        <a href="{{ route('admin.ebooks', ['author_id' => $ebook->authorLink->id]) }}" class="text-decoration-none text-primary hover-underline">
                                            <i class="fas fa-user-pen me-1 text-muted"></i>{{ $ebook->authorLink->name }}
                                        </a>
                                    @elseif($ebook->author)
                                        <a href="{{ route('admin.ebooks', ['author_id' => $ebook->author->id]) }}" class="text-decoration-none text-primary hover-underline">
                                            <i class="fas fa-user-pen me-1 text-muted"></i>{{ $ebook->author->name }}
                                        </a>
                                    @else
                                        <span class="text-dark">{{ $ebook->author_name ?? '—' }}</span>
                                    @endif
                                </div>
                                <div>
                                    @if($isAuthorKdp)
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size: 10px;">
                                            <i class="fas fa-feather-pointed me-0.5"></i> Author Self-Published
                                        </span>
                                    @else
                                        <span class="small text-muted" style="font-size: 11px;">
                                            <i class="fas fa-building me-1 text-secondary"></i>{{ $ebook->publisher?->name ?? 'Idea Prakashan' }}
                                        </span>
                                    @endif
                                </div>
                            </td>

                            {{-- Category --}}
                            <td>
                                @if($ebook->category)
                                    <a href="{{ route('admin.ebooks', ['category_id' => $ebook->category->id]) }}" class="text-decoration-none">
                                        <span class="badge bg-light text-primary border rounded-pill px-2.5 py-1">
                                            {{ $ebook->category->name }}
                                        </span>
                                    </a>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>

                            {{-- Pricing & Royalty --}}
                            <td>
                                @if($isFree)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 fw-bold">Free</span>
                                @elseif($hasDiscount)
                                    <div class="fw-bold text-primary font-monospace">৳{{ number_format($discount, 2) }}</div>
                                    <div class="small text-muted text-decoration-line-through font-monospace">৳{{ number_format($price, 2) }}</div>
                                @else
                                    <div class="fw-bold text-dark font-monospace">৳{{ number_format($price, 2) }}</div>
                                @endif
                                <div class="small text-success fw-semibold font-monospace mt-0.5" style="font-size: 10.5px;">
                                    Royalty: {{ $ebook->royalty_percentage ?: '50' }}%
                                </div>
                            </td>

                            {{-- Sales / Downloads --}}
                            <td>
                                <strong class="text-dark font-monospace fs-6">{{ number_format($ebook->sales_count ?? 0) }}</strong>
                                <small class="text-muted d-block">sold • {{ number_format($ebook->read_count ?? $ebook->download_count ?? 0) }} reads</small>
                            </td>

                            {{-- Moderation Review Status --}}
                            <td class="text-center">
                                @if($ebook->mod_status === 'approved')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1">
                                        <i class="fas fa-circle-check me-1"></i> Approved
                                    </span>
                                @elseif($ebook->mod_status === 'rejected')
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1" 
                                          title="{{ $ebook->rejection_reason ?? 'Needs Revision' }}" data-bs-toggle="tooltip">
                                        <i class="fas fa-circle-xmark me-1"></i> Rejected
                                    </span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2.5 py-1">
                                        <i class="fas fa-hourglass-half me-1"></i> Pending
                                    </span>
                                @endif
                            </td>

                            {{-- Live Store Toggle --}}
                            <td class="text-center">
                                <div class="form-check form-switch d-inline-block">
                                    <input class="form-check-input" type="checkbox" role="switch" id="toggle-{{ $ebook->id }}" 
                                           @checked($ebook->is_active) onchange="toggleEbookActive({{ $ebook->id }})">
                                </div>
                            </td>

                            {{-- Actions --}}
                            <td class="text-end pe-3">
                                <div class="d-inline-flex align-items-center gap-1" id="ebookActionBtns_{{ $ebook->id }}">
                                    {{-- If Pending: Show 1-Click Approve & Reject buttons --}}
                                    @if($ebook->mod_status === 'pending')
                                        <button type="button" class="btn btn-sm btn-success rounded-pill px-2.5 py-0.5 fw-semibold shadow-xs" 
                                                onclick="ajaxApproveEbook({{ $ebook->id }})" title="Approve & Publish to Live Store">
                                            <i class="fas fa-check me-1"></i> Approve
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2 py-0.5" 
                                                data-bs-toggle="modal" data-bs-target="#rejectModal{{ $ebook->id }}" title="Reject / Request Revision">
                                            <i class="fas fa-xmark"></i>
                                        </button>
                                    @elseif($ebook->mod_status === 'rejected')
                                        <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-2 py-0.5 fw-semibold" 
                                                onclick="ajaxApproveEbook({{ $ebook->id }})" title="Re-approve E-Book">
                                            <i class="fas fa-check me-1"></i> Approve
                                        </button>
                                    @endif

                                    {{-- DRM Reader Preview --}}
                                    <a href="{{ route('ebook.read', $ebook->slug ?? $ebook->id) }}" target="_blank" 
                                       class="btn btn-sm btn-outline-info rounded-pill px-2 py-0.5" title="Open DRM Reader">
                                        <i class="fas fa-book-open"></i>
                                    </a>

                                    {{-- Edit --}}
                                    <a href="{{ route('admin.content.edit', ['type' => 'ebooks', 'id' => $ebook->id]) }}" 
                                       class="btn btn-sm btn-outline-primary rounded-pill px-2 py-0.5" title="Edit Form">
                                        <i class="fas fa-pen-to-square"></i>
                                    </a>

                                    {{-- Delete --}}
                                    <form action="{{ route('admin.content.destroy', ['type' => 'ebooks', 'id' => $ebook->id]) }}" method="POST" class="d-inline"
                                          data-confirm="আপনি কি নিশ্চিত যে এই ই-বুকটি ডিলিট করতে চান?" data-confirm-title="ই-বুক ডিলিট">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-2 py-0.5" title="Delete">
                                            <i class="fas fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>

                                {{-- Reject Modal --}}
                                <div class="modal fade text-start" id="rejectModal{{ $ebook->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content rounded-4 border-0 shadow">
                                            <form action="{{ route('admin.ebooks.reject', $ebook->id) }}" method="POST">
                                                @csrf
                                                <div class="modal-header border-bottom">
                                                    <h6 class="modal-title fw-bold text-dark">
                                                        <i class="fas fa-triangle-exclamation text-warning me-1.5"></i> Request Revision / Reject E-Book
                                                    </h6>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body p-4">
                                                    <p class="small text-muted mb-2">
                                                        বই: <strong>{{ $ebook->title }}</strong> (লেখক: {{ $ebook->author_name }})
                                                    </p>
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-bold text-dark">সংশোধনের বিবরণ / কারণ <span class="text-danger">*</span></label>
                                                        <textarea name="rejection_reason" class="form-control form-control-sm rounded-3" rows="4" required
                                                                  placeholder="কেন বইটি লাইভ করা যাচ্ছে না বা কী কী সংশোধন প্রয়োজন তা বিস্তারিত লিখুন (লেখকের ড্যাশবোর্ডে প্রদর্শিত হবে)..."></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer bg-light">
                                                    <button type="button" class="btn btn-sm btn-light rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-sm btn-danger rounded-pill px-4 fw-bold shadow-xs">Send Revision Note</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="9">
                                <div class="empty-state py-5 text-center">
                                    <div class="rounded-circle bg-light d-inline-flex p-4 mb-3">
                                        <i class="fas fa-tablet-screen-button fs-1 text-muted"></i>
                                    </div>
                                    <h5 class="fw-bold text-dark mb-1">No E-Books Found</h5>
                                    <p class="text-muted small mb-3">Try adjusting your search filters or upload a new digital e-book.</p>
                                    <a href="{{ route('admin.ebooks') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                        <i class="fas fa-rotate-left me-1"></i> Clear All Filters
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($ebooks->hasPages())
            <div class="p-3 border-top d-flex flex-wrap align-items-center justify-content-between gap-2">
                <small class="text-muted">
                    Showing {{ $ebooks->firstItem() }} to {{ $ebooks->lastItem() }} of {{ $ebooks->total() }} e-books
                </small>
                <div>{{ $ebooks->links() }}</div>
            </div>
        @endif

        {{-- E-Book Global Settings Modal --}}
        <div class="modal fade" id="ebookSettingsModal" tabindex="-1" aria-labelledby="ebookSettingsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content rounded-4 border-0 shadow-lg">
                    <div class="modal-header border-bottom py-3">
                        <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2" id="ebookSettingsModalLabel">
                            <span class="p-2 bg-primary-subtle text-primary rounded-circle"><i class="fas fa-sliders"></i></span>
                            <span>ই-বুক গ্লোবাল সেটিংস ও প্রিভিউ লিমিট</span>
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('admin.ebooks.settings') }}" method="POST">
                        @csrf
                        <div class="modal-body p-4">
                            <div class="mb-3">
                                <label for="default_preview_pages" class="form-label fw-bold text-dark small mb-1">
                                    <i class="fas fa-book-open-reader text-warning me-1"></i> ডিফল্ট অটো-প্রিভিউ পৃষ্ঠা সংখ্যা (Default Preview Pages)
                                </label>
                                <p class="text-muted small mb-2" style="font-size: 12px; line-height: 1.5;">
                                    পাঠক কোনো ই-বুক কেনার আগে সর্বোচ্চ কত পৃষ্ঠা পর্যন্ত ফ্রিতে পড়তে পারবেন তা নির্ধারণ করুন (যেমন: ১, ৩, ৫, ১০, ১৬, ২০ ইত্যাদি)।
                                </p>
                                <div class="input-group">
                                    <input type="number" min="1" max="100" class="form-control rounded-start-3 font-monospace fw-bold" 
                                           id="default_preview_pages" name="default_preview_pages" 
                                           value="{{ $defaultPreviewPages ?? 16 }}" required>
                                    <span class="input-group-text bg-light text-muted small">পৃষ্ঠা (Pages)</span>
                                </div>
                                <div class="d-flex gap-1.5 mt-2">
                                    <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill py-0.5 px-2" onclick="document.getElementById('default_preview_pages').value = 5;">৫ পৃষ্ঠা</button>
                                    <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill py-0.5 px-2" onclick="document.getElementById('default_preview_pages').value = 10;">১০ পৃষ্ঠা</button>
                                    <button type="button" class="btn btn-xs btn-outline-primary rounded-pill py-0.5 px-2 active fw-bold" onclick="document.getElementById('default_preview_pages').value = 16;">১৬ পৃষ্ঠা (প্রস্তাবিত)</button>
                                    <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill py-0.5 px-2" onclick="document.getElementById('default_preview_pages').value = 20;">২০ পৃষ্ঠা</button>
                                </div>
                            </div>

                            <div class="p-3 bg-light rounded-3 border">
                                <div class="d-flex gap-2">
                                    <i class="fas fa-circle-info text-primary mt-0.5"></i>
                                    <div class="small text-muted" style="font-size: 11.5px; line-height: 1.4;">
                                        <strong>স্বয়ংক্রিয় প্রিভিউ:</strong> লেখক আলাদা স্যাম্পল ফাইল না দিলে সিস্টেম স্বয়ংক্রিয়ভাবে মূল ফাইল থেকে এই নির্ধারিত পৃষ্ঠাসংখ্যা পর্যন্ত প্রিভিউ রিডারে পরিবেশন করবে।
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-top py-2.5 px-4 bg-light bg-opacity-50">
                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">বন্ধ করুন</button>
                            <button type="submit" class="btn btn-sm btn-primary rounded-pill px-4 fw-bold">
                                <i class="fas fa-check me-1"></i> সেটিংস সংরক্ষণ করুন
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
// 1-Click Instant AJAX Ebook Approval
async function ajaxApproveEbook(id) {
    const result = await SwalConfirm({
        title: 'ই-বুক অনুমোদন ও লাইভ প্রকাশ',
        text: 'আপনি কি এই ই-বুকটি অনুমোদন করে লাইভ স্টোরে প্রকাশ করতে চান?',
        icon: 'question',
        confirmButtonText: '<i class="fas fa-check-circle me-1"></i> হ্যাঁ, অনুমোদন করুন',
        confirmButtonColor: '#10b981',
        cancelButtonText: 'বাতিল'
    });
    if (!result.isConfirmed) return;

    try {
        const res = await fetch('/admin/ebooks/' + id + '/approve', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        });
        const data = await res.json();
        if (data.success) {
            // Update toggle switch
            const toggleEl = document.getElementById('toggle-' + id);
            if (toggleEl) toggleEl.checked = true;

            // Update row badge
            const row = document.getElementById('ebook-row-' + id);
            if (row) {
                const badgeTd = row.children[6];
                if (badgeTd) {
                    badgeTd.innerHTML = `<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1"><i class="fas fa-circle-check me-1"></i> Approved</span>`;
                }
            }

            // Remove approve button
            const actionsEl = document.getElementById('ebookActionBtns_' + id);
            if (actionsEl) {
                const approveBtn = actionsEl.querySelector('.btn-success, .btn-outline-success');
                if (approveBtn) approveBtn.remove();
                const rejectBtn = actionsEl.querySelector('.btn-outline-danger');
                if (rejectBtn) rejectBtn.remove();
            }

            showEbookToast('success', data.message || 'ই-বুকটি সফলভাবে অনুমোদিত হয়েছে!');
        } else {
            showEbookToast('error', data.message || 'অনুমোদনে ত্রুটি হয়েছে।');
        }
    } catch (err) {
        console.error(err);
        showEbookToast('error', 'সার্ভারে সংযোগ করতে সমস্যা হয়েছে।');
    }
}

// AJAX Toggle Active State
function toggleEbookActive(id) {
    fetch('/admin/ebooks/' + id + '/toggle-status', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showEbookToast('success', data.message);
        }
    })
    .catch(err => {
        console.error('Error updating status:', err);
        showEbookToast('error', 'স্ট্যাটাস আপডেটে সমস্যা হয়েছে।');
    });
}

function showEbookToast(type, msg) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed top-0 end-0 m-3 shadow-lg rounded-4`;
    alertDiv.style.zIndex = '99999';
    alertDiv.innerHTML = `
        <div class="d-flex align-items-center gap-2">
            <i class="fas ${type === 'success' ? 'fa-check-circle text-success' : (type === 'warning' ? 'fa-triangle-exclamation text-warning' : 'fa-circle-xmark text-danger')} fs-5"></i>
            <div class="small fw-semibold">${msg}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    `;
    document.body.appendChild(alertDiv);
    setTimeout(() => { alertDiv.remove(); }, 4000);
}

// CSV Export
function exportEbooksToCSV() {
    const table = document.getElementById('adminEbooksTable');
    let csv = [];
    const rows = table.querySelectorAll('tr');
    
    for (let i = 0; i < rows.length; i++) {
        let row = [], cols = rows[i].querySelectorAll('td, th');
        for (let j = 0; j < cols.length - 1; j++) {
            let data = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, '').replace(/(\s\s+)/gm, ' ');
            data = data.replace(/"/g, '""');
            row.push('"' + data + '"');
        }
        csv.push(row.join(','));
    }
    
    const csvFile = new Blob(["\uFEFF" + csv.join('\n')], { type: "text/csv;charset=utf-8;" });
    const downloadLink = document.createElement("a");
    downloadLink.download = "Idea_Ebooks_Export_" + new Date().toISOString().slice(0, 10) + ".csv";
    downloadLink.href = window.URL.createObjectURL(csvFile);
    downloadLink.style.display = "none";
    document.body.appendChild(downloadLink);
    downloadLink.click();
    document.body.removeChild(downloadLink);
}
</script>
@endpush
@endsection
