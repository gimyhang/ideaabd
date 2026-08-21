@extends('layouts.admin')

@section('title', 'E-Books Management & Search')
@section('heading', 'E-Books & Digital Publications')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">E-Books Directory</li>
@endsection

@section('actions')
    <div class="d-flex align-items-center gap-2">
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
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-0 shadow-xs rounded-3" role="alert">
            <i class="fas fa-circle-check fs-5 me-2 text-success"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- 1. KPI SUMMARY STRIP                                                      --}}
    {{-- ========================================================================= --}}
    <div class="row g-2">
        <div class="col-6 col-md-3">
            <a href="{{ route('admin.ebooks') }}" class="text-decoration-none">
                <div class="adm-card p-3 d-flex align-items-center justify-content-between h-100 bg-white rounded-4 shadow-sm border-0 {{ !request()->hasAny(['price_type', 'is_active']) ? 'border-primary border-2' : '' }}">
                    <div>
                        <small class="text-muted d-block font-sans">Total E-Books</small>
                        <h4 class="fw-bold text-dark mb-0">{{ number_format($stats['total'] ?? 0) }}</h4>
                    </div>
                    <span class="p-2 bg-primary-subtle text-primary rounded-circle fs-5"><i class="fas fa-tablet-screen-button"></i></span>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="{{ route('admin.ebooks', array_merge(request()->except(['is_active', 'page']), ['is_active' => '1'])) }}" class="text-decoration-none">
                <div class="adm-card p-3 d-flex align-items-center justify-content-between h-100 bg-white rounded-4 shadow-sm border-0 {{ request('is_active') === '1' ? 'border-success border-2' : '' }}">
                    <div>
                        <small class="text-muted d-block font-sans">Active & Live</small>
                        <h4 class="fw-bold text-success mb-0">{{ number_format($stats['active'] ?? 0) }}</h4>
                    </div>
                    <span class="p-2 bg-success-subtle text-success rounded-circle fs-5"><i class="fas fa-circle-check"></i></span>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="{{ route('admin.ebooks', array_merge(request()->except(['price_type', 'page']), ['price_type' => 'free'])) }}" class="text-decoration-none">
                <div class="adm-card p-3 d-flex align-items-center justify-content-between h-100 bg-white rounded-4 shadow-sm border-0 {{ request('price_type') === 'free' ? 'border-info border-2' : '' }}">
                    <div>
                        <small class="text-muted d-block font-sans">Free Reading</small>
                        <h4 class="fw-bold text-info mb-0">{{ number_format($stats['free'] ?? 0) }}</h4>
                    </div>
                    <span class="p-2 bg-info-subtle text-info rounded-circle fs-5"><i class="fas fa-gift"></i></span>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="{{ route('admin.ebooks', array_merge(request()->except(['price_type', 'page']), ['price_type' => 'paid'])) }}" class="text-decoration-none">
                <div class="adm-card p-3 d-flex align-items-center justify-content-between h-100 bg-white rounded-4 shadow-sm border-0 {{ request('price_type') === 'paid' ? 'border-warning border-2' : '' }}">
                    <div>
                        <small class="text-muted d-block font-sans">Paid E-Books</small>
                        <h4 class="fw-bold text-warning-emphasis mb-0">{{ number_format($stats['paid'] ?? 0) }}</h4>
                    </div>
                    <span class="p-2 bg-warning-subtle text-warning rounded-circle fs-5"><i class="fas fa-sack-dollar"></i></span>
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
                <div class="col-12 col-lg-4">
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
                        <button type="submit" class="btn btn-primary px-3 fw-semibold">Search</button>
                    </div>
                </div>

                <!-- Author Filter -->
                <div class="col-6 col-md-4 col-lg-3">
                    <select name="author_id" class="form-select form-select-sm" onchange="submitEbookFilterForm()">
                        <option value="">— All Authors —</option>
                        @foreach ($authors as $aId => $aName)
                            <option value="{{ $aId }}" @selected(request('author_id') == $aId)>{{ $aName }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Publisher Filter -->
                <div class="col-6 col-md-4 col-lg-3">
                    <select name="publisher_id" class="form-select form-select-sm" onchange="submitEbookFilterForm()">
                        <option value="">— All Publishers —</option>
                        <option value="idea" @selected(request('publisher_id') === 'idea')>⭐ Idea Prakashan (In-House)</option>
                        @foreach ($publishers as $pId => $pName)
                            <option value="{{ $pId }}" @selected(request('publisher_id') == $pId)>{{ $pName }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Category Filter -->
                <div class="col-12 col-md-4 col-lg-2">
                    <select name="category_id" class="form-select form-select-sm" onchange="submitEbookFilterForm()">
                        <option value="">— All Categories —</option>
                        @foreach ($categories as $cId => $cName)
                            <option value="{{ $cId }}" @selected(request('category_id') == $cId)>{{ $cName }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row g-2 align-items-center pt-1 border-top">
                <!-- Price Type Filter -->
                <div class="col-6 col-md-3">
                    <select name="price_type" class="form-select form-select-sm" onchange="submitEbookFilterForm()">
                        <option value="">— All Price Types —</option>
                        <option value="free" @selected(request('price_type') === 'free')>Free</option>
                        <option value="paid" @selected(request('price_type') === 'paid')>Paid</option>
                    </select>
                </div>

                <!-- Status Filter -->
                <div class="col-6 col-md-3">
                    <select name="is_active" class="form-select form-select-sm" onchange="submitEbookFilterForm()">
                        <option value="">— All Statuses —</option>
                        <option value="1" @selected(request('is_active') === '1')>Active / Live</option>
                        <option value="0" @selected(request('is_active') === '0')>Inactive / Draft</option>
                    </select>
                </div>

                <!-- Sort By -->
                <div class="col-6 col-md-3">
                    <select name="sort" class="form-select form-select-sm" onchange="submitEbookFilterForm()">
                        <option value="latest" @selected(request('sort') === 'latest' || !request('sort'))>Newest First</option>
                        <option value="oldest" @selected(request('sort') === 'oldest')>Oldest First</option>
                        <option value="title_asc" @selected(request('sort') === 'title_asc')>Title (A-Z)</option>
                        <option value="title_desc" @selected(request('sort') === 'title_desc')>Title (Z-A)</option>
                        <option value="price_low" @selected(request('sort') === 'price_low')>Price: Low to High</option>
                        <option value="price_high" @selected(request('sort') === 'price_high')>Price: High to Low</option>
                        <option value="sales_high" @selected(request('sort') === 'sales_high')>Most Read / Sold</option>
                    </select>
                </div>

                <!-- Per Page & Reset -->
                <div class="col-6 col-md-3 d-flex gap-1">
                    <select name="per_page" class="form-select form-select-sm flex-fill" onchange="submitEbookFilterForm()">
                        <option value="20" @selected(request('per_page') == 20 || !request('per_page'))>20 per page</option>
                        <option value="50" @selected(request('per_page') == 50)>50 per page</option>
                        <option value="100" @selected(request('per_page') == 100)>100 per page</option>
                    </select>
                    <a href="{{ route('admin.ebooks') }}" class="btn btn-sm btn-outline-secondary px-2.5" title="Reset All Filters">
                        <i class="fas fa-rotate-left"></i>
                    </a>
                </div>
            </div>

        </form>

        {{-- Active Filter Badges --}}
        @php
            $hasEbookFilters = request()->hasAny(['search', 'author_id', 'publisher_id', 'category_id', 'price_type', 'is_active']) || (request('sort') && request('sort') !== 'latest');
        @endphp

        @if($hasEbookFilters)
            <div class="d-flex flex-wrap align-items-center gap-1.5 pt-2.5 mt-2 border-top">
                <span class="small fw-semibold text-muted me-1"><i class="fas fa-sliders me-1"></i>Active Filters:</span>
                
                @if(request('search'))
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill py-1 px-2.5 d-inline-flex align-items-center gap-1">
                        Search: "{{ request('search') }}"
                        <a href="{{ route('admin.ebooks', request()->except('search')) }}" class="text-primary text-decoration-none"><i class="fas fa-times-circle"></i></a>
                    </span>
                @endif

                @if(request('author_id') && isset($authors[request('author_id')]))
                    <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle rounded-pill py-1 px-2.5 d-inline-flex align-items-center gap-1">
                        Author: {{ $authors[request('author_id')] }}
                        <a href="{{ route('admin.ebooks', request()->except('author_id')) }}" class="text-dark text-decoration-none"><i class="fas fa-times-circle"></i></a>
                    </span>
                @endif

                @if(request('publisher_id'))
                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill py-1 px-2.5 d-inline-flex align-items-center gap-1">
                        Publisher: {{ request('publisher_id') === 'idea' ? 'Idea Prakashan' : ($publishers[request('publisher_id')] ?? request('publisher_id')) }}
                        <a href="{{ route('admin.ebooks', request()->except('publisher_id')) }}" class="text-dark text-decoration-none"><i class="fas fa-times-circle"></i></a>
                    </span>
                @endif

                @if(request('category_id') && isset($categories[request('category_id')]))
                    <span class="badge bg-secondary-subtle text-dark border rounded-pill py-1 px-2.5 d-inline-flex align-items-center gap-1">
                        Category: {{ $categories[request('category_id')] }}
                        <a href="{{ route('admin.ebooks', request()->except('category_id')) }}" class="text-dark text-decoration-none"><i class="fas fa-times-circle"></i></a>
                    </span>
                @endif

                @if(request('price_type'))
                    <span class="badge bg-light text-dark border rounded-pill py-1 px-2.5 d-inline-flex align-items-center gap-1">
                        Price: {{ request('price_type') === 'free' ? 'Free' : 'Paid' }}
                        <a href="{{ route('admin.ebooks', request()->except('price_type')) }}" class="text-dark text-decoration-none"><i class="fas fa-times-circle"></i></a>
                    </span>
                @endif

                @if(request('is_active') !== null && request('is_active') !== '')
                    <span class="badge bg-light text-dark border rounded-pill py-1 px-2.5 d-inline-flex align-items-center gap-1">
                        Status: {{ request('is_active') === '1' ? 'Live' : 'Draft' }}
                        <a href="{{ route('admin.ebooks', request()->except('is_active')) }}" class="text-dark text-decoration-none"><i class="fas fa-times-circle"></i></a>
                    </span>
                @endif

                <a href="{{ route('admin.ebooks') }}" class="btn btn-link btn-xs text-danger text-decoration-none fw-bold ms-auto">
                    <i class="fas fa-trash-can me-1"></i> Clear All
                </a>
            </div>
        @endif

    </div>

    {{-- ========================================================================= --}}
    {{-- 3. E-BOOK MANAGEMENT TABLE                                                --}}
    {{-- ========================================================================= --}}
    <div class="adm-card p-0 overflow-hidden shadow-sm border-0 rounded-4 bg-white">
        <div class="table-responsive">
            <table class="table adm-table align-middle mb-0" id="adminEbooksTable">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" style="width: 50px;">#</th>
                        <th style="min-width: 260px;">E-Book & Cover</th>
                        <th style="min-width: 180px;">Author & Publisher</th>
                        <th>Category</th>
                        <th>File Format & Size</th>
                        <th class="text-end" style="min-width: 120px;">Price</th>
                        <th class="text-center" style="min-width: 90px;">Status</th>
                        <th class="text-end pe-3" style="min-width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($ebooks as $index => $ebook)
                        @php
                            $cover = $ebook->cover_image;
                            $coverUrl = $cover 
                                ? (str_starts_with($cover, 'http') ? $cover : (str_starts_with($cover, 'storage/') ? asset($cover) : asset('storage/' . ltrim($cover, '/'))))
                                : 'https://placehold.co/100x150/0284c7/ffffff?text=E-Book';
                            
                            $price = (float) $ebook->price;
                            $discount = (float) ($ebook->discount_price ?? 0);
                            $hasDiscount = $discount > 0 && $discount < $price;
                            $isFree = $price <= 0;
                        @endphp
                        <tr>
                            <td class="ps-3 text-muted small">
                                {{ ($ebooks->currentPage() - 1) * $ebooks->perPage() + $index + 1 }}
                            </td>
                            
                            {{-- Ebook Cover & Title --}}
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="position-relative flex-shrink-0" style="width: 48px; height: 66px;">
                                        <img src="{{ $coverUrl }}" alt="{{ $ebook->title }}" 
                                             class="rounded border shadow-xs" style="width: 100%; height: 100%; object-fit: cover;">
                                        <span class="badge bg-primary text-white position-absolute top-0 start-0 m-0.5 p-0.5 rounded-1" style="font-size: 8px;">
                                            <i class="fas fa-file-pdf"></i>
                                        </span>
                                    </div>
                                    <div class="text-truncate" style="max-width: 260px;">
                                        <a href="{{ route('ebook.show', $ebook->slug ?? $ebook->id) }}" target="_blank" 
                                           class="fw-bold text-dark text-decoration-none hover-primary d-block text-truncate mb-0.5" title="{{ $ebook->title }}">
                                            {{ $ebook->title }}
                                        </a>
                                        <div class="d-flex align-items-center gap-2 small text-muted" style="font-size: 11px;">
                                            @if($ebook->isbn)
                                                <span class="badge bg-light text-muted border px-1.5 py-0.5"><i class="fas fa-barcode me-1"></i>{{ $ebook->isbn }}</span>
                                            @endif
                                            <span><i class="fas fa-download me-0.5 text-secondary"></i> {{ number_format($ebook->download_count ?? 0) }} reads</span>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            {{-- Author & Publisher --}}
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
                                <div class="small text-muted" style="font-size: 11px;">
                                    @if($ebook->publisher)
                                        <i class="fas fa-building me-1"></i>{{ $ebook->publisher->name }}
                                    @else
                                        <i class="fas fa-building me-1 text-primary"></i>Idea Prakashan (In-House)
                                    @endif
                                </div>
                            </td>

                            {{-- Category --}}
                            <td>
                                @if($ebook->category)
                                    <a href="{{ route('admin.ebooks', ['category_id' => $ebook->category->id]) }}" class="text-decoration-none">
                                        <span class="badge bg-light text-primary border rounded-pill px-2.5 py-1">
                                            <i class="fas fa-folder me-1 text-primary-subtle"></i>{{ $ebook->category->name }}
                                        </span>
                                    </a>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>

                            {{-- File Format & Size --}}
                            <td>
                                <div class="d-flex align-items-center gap-1.5">
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-0.5" style="font-size: 11px;">
                                        <i class="fas fa-file-pdf me-1"></i>{{ strtoupper($ebook->file_type ?? 'PDF') }}
                                    </span>
                                    <span class="small text-muted" style="font-size: 11px;">{{ $ebook->file_size ?? '—' }}</span>
                                </div>
                            </td>

                            {{-- Pricing --}}
                            <td class="text-end">
                                @if($isFree)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 fw-bold">Free</span>
                                @elseif($hasDiscount)
                                    <div class="fw-bold text-primary fs-6 font-monospace">৳{{ number_format($discount, 2) }}</div>
                                    <div class="small text-muted text-decoration-line-through font-monospace">৳{{ number_format($price, 2) }}</div>
                                @else
                                    <div class="fw-bold text-dark fs-6 font-monospace">৳{{ number_format($price, 2) }}</div>
                                @endif
                            </td>

                            {{-- Status --}}
                            <td class="text-center">
                                @if($ebook->is_active)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1"><i class="fas fa-check me-1"></i>Live</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary border rounded-pill px-2 py-1">Draft</span>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td class="text-end pe-3">
                                <div class="d-inline-flex align-items-center gap-1">
                                    <a href="{{ route('ebook.show', $ebook->slug ?? $ebook->id) }}" target="_blank" 
                                       class="btn btn-sm btn-outline-secondary rounded-pill px-2 py-0.5" title="View in Digital Reader">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.content.edit', ['type' => 'ebooks', 'id' => $ebook->id]) }}" 
                                       class="btn btn-sm btn-outline-primary rounded-pill px-2 py-0.5" title="Edit">
                                        <i class="fas fa-pen-to-square"></i>
                                    </a>
                                    <form action="{{ route('admin.content.destroy', ['type' => 'ebooks', 'id' => $ebook->id]) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Are you sure you want to delete this e-book?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-2 py-0.5" title="Delete">
                                            <i class="fas fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
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
            <div class="p-3 border-top d-flex flex-column flex-md-row align-items-center justify-content-between gap-2 bg-light bg-opacity-50">
                <div class="small text-muted">
                    Showing {{ $ebooks->firstItem() }} to {{ $ebooks->lastItem() }} of {{ $ebooks->total() }} e-books
                </div>
                <div>{{ $ebooks->links() }}</div>
            </div>
        @endif
    </div>

</div>

@push('scripts')
<script>
// Dynamic Search & Clean Form Submission Engine
const searchInput = document.getElementById('ebookSearchInput');
const ebooksFilterForm = document.getElementById('ebooksFilterForm');

if (searchInput) {
    searchInput.addEventListener('input', function() {
        const query = this.value.trim().toLowerCase();
        const rows = document.querySelectorAll('#adminEbooksTable tbody tr');
        
        if (!rows || rows.length === 0) return;

        rows.forEach(row => {
            if (!query) {
                row.style.display = '';
            } else {
                const text = row.innerText.toLowerCase();
                if (text.includes(query)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            }
        });
    });

    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            submitEbookFilterForm();
        }
    });
}

function submitEbookFilterForm() {
    const form = document.getElementById('ebooksFilterForm');
    if (!form) return;

    const inputs = form.querySelectorAll('input, select');
    inputs.forEach(input => {
        if (input.type === 'checkbox' && !input.checked) {
            input.disabled = true;
        } else if (!input.value || input.value.trim() === '') {
            input.disabled = true;
        }
    });

    form.submit();
}

if (ebooksFilterForm) {
    ebooksFilterForm.addEventListener('submit', function(e) {
        const inputs = this.querySelectorAll('input, select');
        inputs.forEach(input => {
            if (input.type === 'checkbox' && !input.checked) {
                input.disabled = true;
            } else if (!input.value || input.value.trim() === '') {
                input.disabled = true;
            }
        });
    });
}

function exportEbooksToCSV() {
    const table = document.getElementById('adminEbooksTable');
    if (!table) return;

    let csv = [];
    const rows = table.querySelectorAll('tr');
    
    rows.forEach(row => {
        const cols = row.querySelectorAll('th, td');
        let rowData = [];
        cols.forEach((col, idx) => {
            if (idx === 7) return; // skip action column
            let text = col.innerText.replace(/"/g, '""').replace(/\n/g, ' ').trim();
            rowData.push('"' + text + '"');
        });
        if (rowData.length > 0) {
            csv.push(rowData.join(','));
        }
    });

    const csvContent = "data:text/csv;charset=utf-8,\uFEFF" + csv.join("\n");
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", "Idea_Ebooks_Export_" + new Date().toISOString().slice(0, 10) + ".csv");
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
</script>
@endpush

@endsection
