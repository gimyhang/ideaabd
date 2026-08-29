@extends('layouts.admin')

@section('title', 'Book Requests Hub')
@section('heading', 'Special Book Requests Hub')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Book Requests</li>
@endsection

@section('actions')
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-primary btn-sm rounded-pill px-3.5 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#addRequestModal">
            <i class="fas fa-plus-circle me-1.5"></i> Add New Request
        </button>
        <a href="{{ route('admin.purchases.create') }}" class="btn btn-outline-success btn-sm rounded-pill px-3 fw-semibold">
            <i class="fas fa-cart-plus me-1.5"></i> Purchase Order
        </a>
    </div>
@endsection

@section('content')

{{-- ========================================================================= --}}
{{-- 1. REAL-TIME METRICS & STATS CARDS                                        --}}
{{-- ========================================================================= --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-xl">
        <a href="{{ route('admin.book-requests.index') }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 transition-hover border-start border-4 border-primary">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold">Total Requests</span>
                        <h3 class="fw-bold text-dark mb-0 mt-1">{{ number_format($stats['total']) }}</h3>
                    </div>
                    <div class="rounded-circle bg-primary-subtle text-primary p-3 d-flex align-items-center justify-content-center" style="width: 46px; height: 46px;">
                        <i class="fas fa-book-open fs-5"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-6 col-md-4 col-xl">
        <a href="{{ route('admin.book-requests.index', ['status' => 'pending']) }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 transition-hover border-start border-4 border-warning">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold">Pending</span>
                        <h3 class="fw-bold text-warning mb-0 mt-1">{{ number_format($stats['pending']) }}</h3>
                    </div>
                    <div class="rounded-circle bg-warning-subtle text-warning p-3 d-flex align-items-center justify-content-center" style="width: 46px; height: 46px;">
                        <i class="fas fa-clock fs-5"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-6 col-md-4 col-xl">
        <a href="{{ route('admin.book-requests.index', ['status' => 'processing']) }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 transition-hover border-start border-4 border-info">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold">Processing</span>
                        <h3 class="fw-bold text-info mb-0 mt-1">{{ number_format($stats['processing']) }}</h3>
                    </div>
                    <div class="rounded-circle bg-info-subtle text-info p-3 d-flex align-items-center justify-content-center" style="width: 46px; height: 46px;">
                        <i class="fas fa-spinner fs-5"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-6 col-md-6 col-xl">
        <a href="{{ route('admin.book-requests.index', ['status' => 'available']) }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 transition-hover border-start border-4 border-success">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold">Available / Ready</span>
                        <h3 class="fw-bold text-success mb-0 mt-1">{{ number_format($stats['available']) }}</h3>
                    </div>
                    <div class="rounded-circle bg-success-subtle text-success p-3 d-flex align-items-center justify-content-center" style="width: 46px; height: 46px;">
                        <i class="fas fa-check-circle fs-5"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-12 col-md-6 col-xl">
        <a href="{{ route('admin.book-requests.index', ['status' => 'closed']) }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 transition-hover border-start border-4 border-secondary">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold">Closed / Completed</span>
                        <h3 class="fw-bold text-secondary mb-0 mt-1">{{ number_format($stats['closed']) }}</h3>
                    </div>
                    <div class="rounded-circle bg-secondary-subtle text-secondary p-3 d-flex align-items-center justify-content-center" style="width: 46px; height: 46px;">
                        <i class="fas fa-circle-xmark fs-5"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- 2. SMART SEARCH & MULTI-FILTER BAR                                        --}}
{{-- ========================================================================= --}}
<div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
    <div class="card-body p-3.5">
        <form action="{{ route('admin.book-requests.index') }}" method="GET" class="row g-2.5 align-items-center">
            
            {{-- Search Input --}}
            <div class="col-12 col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="fas fa-magnifying-glass"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0 bg-light" 
                           placeholder="Search book title, author, customer or phone..." value="{{ $search }}">
                    @if($search)
                        <a href="{{ route('admin.book-requests.index', array_filter(['status' => $status, 'date_from' => $dateFrom, 'date_to' => $dateTo])) }}" class="input-group-text bg-light text-muted text-decoration-none">
                            <i class="fas fa-times"></i>
                        </a>
                    @endif
                </div>
            </div>

            {{-- Status Filter --}}
            <div class="col-6 col-md-2">
                <select name="status" class="form-select bg-light" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <option value="pending" @selected($status === 'pending')>⏳ Pending</option>
                    <option value="processing" @selected($status === 'processing')>⚙️ Processing</option>
                    <option value="available" @selected($status === 'available')>✅ Available</option>
                    <option value="closed" @selected($status === 'closed')>❌ Closed</option>
                </select>
            </div>

            {{-- Date Range --}}
            <div class="col-6 col-md-2">
                <input type="date" name="date_from" class="form-control bg-light" value="{{ $dateFrom }}" placeholder="From Date" onchange="this.form.submit()">
            </div>
            <div class="col-6 col-md-2">
                <input type="date" name="date_to" class="form-control bg-light" value="{{ $dateTo }}" placeholder="To Date" onchange="this.form.submit()">
            </div>

            {{-- Actions --}}
            <div class="col-6 col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100 rounded-3 fw-semibold">
                    <i class="fas fa-filter me-1"></i> Filter
                </button>
                @if($search || $status || $dateFrom || $dateTo)
                    <a href="{{ route('admin.book-requests.index') }}" class="btn btn-light border rounded-3 text-danger" title="Clear Filters">
                        <i class="fas fa-rotate-left"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- 3. MAIN TABLE & BULK ACTIONS                                              --}}
{{-- ========================================================================= --}}
<div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4">
    
    {{-- Bulk Action Bar (Hidden by default, shown when items selected) --}}
    <form id="bulkForm" action="{{ route('admin.book-requests.bulk-action') }}" method="POST">
        @csrf
        
        <div id="bulkActionBar" class="p-2.5 bg-primary-subtle border-bottom d-flex align-items-center justify-content-between px-3" style="display: none;">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary rounded-pill px-2.5" id="selectedCountBadge">0</span>
                <span class="small fw-bold text-primary">request(s) selected</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <select name="bulk_action" class="form-select form-select-sm rounded-pill" style="width: 170px;" required>
                    <option value="">Choose action...</option>
                    <option value="pending">Status: Pending</option>
                    <option value="processing">Status: Processing</option>
                    <option value="available">Status: Available</option>
                    <option value="closed">Status: Closed</option>
                    <option value="delete">🗑️ Delete Selected</option>
                </select>
                <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 fw-bold" onclick="handleBulkActionSubmit()">
                    Apply
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="requestsTable">
                <thead class="table-light text-muted small text-uppercase">
                    <tr>
                        <th class="ps-3" style="width: 40px;">
                            <input type="checkbox" class="form-check-input" id="selectAllCheckbox" onchange="toggleSelectAll(this)">
                        </th>
                        <th style="min-width: 220px;">Book & Author Details</th>
                        <th style="min-width: 180px;">Customer Info</th>
                        <th style="min-width: 180px;">Customer Notes</th>
                        <th style="min-width: 160px;">Admin Notes</th>
                        <th style="min-width: 140px;">Status</th>
                        <th style="min-width: 110px;">Date</th>
                        <th class="text-end pe-3" style="min-width: 130px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $req)
                        @php
                            $cleanPhone = $req->clean_phone;
                            $waText = urlencode("Dear customer, regarding your requested book '{$req->book_title}' at Idea Prakashan.");
                        @endphp
                        <tr id="reqRow-{{ $req->id }}">
                            {{-- Checkbox --}}
                            <td class="ps-3">
                                <input type="checkbox" name="selected_ids[]" value="{{ $req->id }}" class="form-check-input row-checkbox" onchange="onRowCheckboxChange()">
                            </td>

                            {{-- Book Title & Author --}}
                            <td>
                                <div class="fw-bold text-dark fs-6 mb-0.5">
                                    <span class="text-primary fw-bold font-monospace small me-1">#{{ $req->id }}</span>
                                    {{ $req->book_title }}
                                </div>
                                <div class="d-flex flex-wrap align-items-center gap-2 small text-muted">
                                    @if($req->author_name)
                                        <span><i class="fas fa-user-pen me-1 text-secondary"></i>{{ $req->author_name }}</span>
                                    @endif
                                    @if($req->edition)
                                        <span class="badge bg-light text-dark border"><i class="fas fa-bookmark me-1 text-warning"></i>{{ $req->edition }}</span>
                                    @endif
                                </div>
                                {{-- Quick Link to check in catalog / purchase --}}
                                <div class="mt-1">
                                    <a href="{{ route('admin.books', ['search' => $req->book_title]) }}" target="_blank" class="text-decoration-none small text-muted hover-primary" style="font-size: 0.73rem;">
                                        <i class="fas fa-search me-0.5"></i>Check Catalog
                                    </a>
                                    <span class="text-muted mx-1">•</span>
                                    <a href="{{ route('admin.purchases.create') }}" class="text-decoration-none small text-success hover-underline" style="font-size: 0.73rem;">
                                        <i class="fas fa-cart-plus me-0.5"></i>Purchase Entry
                                    </a>
                                </div>
                            </td>

                            {{-- Customer Info --}}
                            <td>
                                <div class="fw-semibold text-dark">{{ $req->customer_name ?: 'Unnamed Customer' }}</div>
                                @if($req->customer_phone)
                                    <div class="d-flex align-items-center gap-1.5 mt-0.5">
                                        <span class="small font-monospace text-muted">{{ $req->customer_phone }}</span>
                                        <a href="tel:{{ $req->customer_phone }}" class="badge bg-primary-subtle text-primary p-1 rounded-circle text-decoration-none" title="Call directly">
                                            <i class="fas fa-phone" style="font-size: 9px;"></i>
                                        </a>
                                        <a href="https://wa.me/{{ $cleanPhone }}?text={{ $waText }}" target="_blank" class="badge bg-success-subtle text-success p-1 rounded-circle text-decoration-none" title="Send WhatsApp message">
                                            <i class="fab fa-whatsapp" style="font-size: 10px;"></i>
                                        </a>
                                    </div>
                                @endif
                                @if($req->customer_email)
                                    <div class="small text-muted text-truncate" style="max-width: 160px;" title="{{ $req->customer_email }}">
                                        <i class="fas fa-envelope me-1" style="font-size: 10px;"></i>{{ $req->customer_email }}
                                    </div>
                                @endif
                            </td>

                            {{-- Additional Info --}}
                            <td>
                                @if($req->additional_info)
                                    <div class="small text-muted line-clamp-2" style="font-size: 0.8rem; line-height: 1.4;" title="{{ $req->additional_info }}">
                                        {{ $req->additional_info }}
                                    </div>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>

                            {{-- Admin Notes --}}
                            <td>
                                <div class="d-flex align-items-center gap-1">
                                    <div class="small text-dark font-monospace line-clamp-2" style="font-size: 0.78rem;" id="notesText-{{ $req->id }}" title="{{ $req->admin_notes }}">
                                        {{ $req->admin_notes ?: 'No notes' }}
                                    </div>
                                    <button type="button" class="btn btn-xs btn-light border-0 p-1 text-muted" onclick="openNotesModal({{ $req->id }}, '{{ addslashes($req->admin_notes ?? '') }}')" title="Edit Note">
                                        <i class="fas fa-pen" style="font-size: 10px;"></i>
                                    </button>
                                </div>
                            </td>

                            {{-- Status Dropdown --}}
                            <td>
                                <select class="form-select form-select-sm rounded-pill fw-semibold {{ $req->status_badge_class }}" 
                                        style="font-size: 0.76rem;" 
                                        onchange="updateRequestStatus({{ $req->id }}, this.value, this)">
                                    <option value="pending" @selected($req->status === 'pending')>⏳ Pending</option>
                                    <option value="processing" @selected($req->status === 'processing')>⚙️ Processing</option>
                                    <option value="available" @selected($req->status === 'available')>✅ Available</option>
                                    <option value="closed" @selected($req->status === 'closed')>❌ Closed</option>
                                </select>
                            </td>

                            {{-- Created Date --}}
                            <td>
                                <div class="small text-dark">{{ $req->created_at->format('d M, Y') }}</div>
                                <small class="text-muted" style="font-size: 0.7rem;">{{ $req->created_at->diffForHumans() }}</small>
                            </td>

                            {{-- Actions --}}
                            <td class="text-end pe-3">
                                <div class="d-flex align-items-center justify-content-end gap-1">
                                    <button type="button" class="btn btn-sm btn-light border p-1 rounded-circle shadow-xs" 
                                            onclick="openViewModal({{ json_encode($req) }})" title="View Details">
                                        <i class="fas fa-eye text-primary" style="font-size: 11px;"></i>
                                    </button>

                                    <form action="{{ route('admin.book-requests.destroy', $req->id) }}" method="POST" 
                                          data-confirm="আপনি কি নিশ্চিত যে এই বইয়ের অনুরোধটি মুছে ফেলতে চান?" data-confirm-title="অনুরোধ ডিলিট" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-light border p-1 rounded-circle shadow-xs text-danger" title="Delete">
                                            <i class="fas fa-trash-can" style="font-size: 11px;"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <div class="p-4">
                                    <i class="fas fa-book-open fs-1 opacity-25 mb-3 d-block"></i>
                                    <h6 class="fw-bold text-dark">No Book Requests Found</h6>
                                    <p class="small text-muted mb-3">Requests submitted by readers will appear here automatically.</p>
                                    <button type="button" class="btn btn-sm btn-primary rounded-pill px-3.5" data-bs-toggle="modal" data-bs-target="#addRequestModal">
                                        <i class="fas fa-plus me-1"></i> Add New Request
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </form>

    {{-- Pagination Footer --}}
    @if($requests->hasPages())
        <div class="card-footer bg-white border-top py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div class="text-muted small">
                Showing <strong>{{ $requests->firstItem() }} - {{ $requests->lastItem() }}</strong> of <strong>{{ $requests->total() }}</strong> requests
            </div>
            <div>
                {{ $requests->links() }}
            </div>
        </div>
    @endif
</div>

{{-- ========================================================================= --}}
{{-- 4. MODAL 1: ADD NEW BOOK REQUEST MANUALLY                                 --}}
{{-- ========================================================================= --}}
<div class="modal fade" id="addRequestModal" tabindex="-1" aria-labelledby="addRequestModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-primary text-white py-3 px-4">
                <h5 class="modal-title fw-bold" id="addRequestModalLabel">
                    <i class="fas fa-plus-circle me-1.5"></i> New Book Request Entry
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.book-requests.admin-store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Book Title <span class="text-danger">*</span></label>
                        <input type="text" name="book_title" class="form-control rounded-3" placeholder="Full book title..." required>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-sm-6">
                            <label class="form-label small fw-bold">Author Name</label>
                            <input type="text" name="author_name" class="form-control rounded-3" placeholder="Author name...">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label small fw-bold">Edition / Year</label>
                            <input type="text" name="edition" class="form-control rounded-3" placeholder="e.g. 1st Edition / 2026">
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-sm-6">
                            <label class="form-label small fw-bold">Customer Name</label>
                            <input type="text" name="customer_name" class="form-control rounded-3" placeholder="Customer name...">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label small fw-bold">Phone Number <span class="text-danger">*</span></label>
                            <input type="text" name="customer_phone" class="form-control rounded-3 font-monospace" placeholder="01710..." required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Email Address</label>
                        <input type="email" name="customer_email" class="form-control rounded-3" placeholder="customer@gmail.com">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Customer Notes / Additional Details</label>
                        <textarea name="additional_info" rows="2" class="form-control rounded-3" placeholder="Specific notes or requirements..."></textarea>
                    </div>

                    <div class="row g-2">
                        <div class="col-sm-6">
                            <label class="form-label small fw-bold">Status</label>
                            <select name="status" class="form-select rounded-3">
                                <option value="pending">⏳ Pending</option>
                                <option value="processing">⚙️ Processing</option>
                                <option value="available">✅ Available</option>
                                <option value="closed">❌ Closed</option>
                            </select>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label small fw-bold">Admin Notes (Internal)</label>
                            <input type="text" name="admin_notes" class="form-control rounded-3" placeholder="Internal remarks...">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2.5 px-4 border-top">
                    <button type="button" class="btn btn-light border rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Save Request</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- 5. MODAL 2: EDIT ADMIN NOTES                                              --}}
{{-- ========================================================================= --}}
<div class="modal fade" id="notesModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-dark text-white py-2.5 px-3">
                <h6 class="modal-title fw-bold mb-0"><i class="fas fa-note-sticky text-warning me-1.5"></i>Admin Notes</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="notesForm" onsubmit="saveAdminNotes(event)">
                <div class="modal-body p-3">
                    <input type="hidden" id="notesReqId" value="">
                    <textarea id="notesTextarea" class="form-control rounded-3" rows="4" placeholder="Internal tracking notes..."></textarea>
                </div>
                <div class="modal-footer bg-light p-2 border-top">
                    <button type="button" class="btn btn-light btn-sm border rounded-pill px-3" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3 fw-bold">Save Notes</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- 6. MODAL 3: VIEW FULL REQUEST DETAILS                                     --}}
{{-- ========================================================================= --}}
<div class="modal fade" id="viewRequestModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-light py-3 px-4 border-bottom">
                <h5 class="modal-title fw-bold text-dark mb-0">
                    <i class="fas fa-circle-info text-primary me-1.5"></i>Request Details (#<span id="vReqId"></span>)
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="p-3 bg-light rounded-3 mb-3 border">
                    <span class="text-muted small fw-bold d-block">Book Title:</span>
                    <h5 class="fw-bold text-dark mb-1" id="vBookTitle"></h5>
                    <div class="text-muted small" id="vAuthorName"></div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-sm-6">
                        <span class="text-muted small d-block">Customer Name:</span>
                        <div class="fw-bold text-dark" id="vCustomerName"></div>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted small d-block">Phone Number:</span>
                        <div class="fw-bold text-dark font-monospace" id="vCustomerPhone"></div>
                    </div>
                    <div class="col-12" id="vEmailWrapper">
                        <span class="text-muted small d-block">Email:</span>
                        <div class="text-dark" id="vCustomerEmail"></div>
                    </div>
                </div>

                <div class="mb-3">
                    <span class="text-muted small d-block">Customer Additional Details:</span>
                    <div class="p-2.5 bg-light rounded-3 text-dark small" id="vAdditionalInfo"></div>
                </div>

                <div class="mb-2">
                    <span class="text-muted small d-block">Admin Notes:</span>
                    <div class="p-2.5 bg-warning-subtle rounded-3 text-dark small font-monospace" id="vAdminNotes"></div>
                </div>
            </div>
            <div class="modal-footer bg-light py-2.5 px-4 border-top">
                <button type="button" class="btn btn-secondary btn-sm rounded-pill px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function updateRequestStatus(id, newStatus, selectEl) {
    fetch(`/admin/book-requests/${id}`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ status: newStatus })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            selectEl.className = `form-select form-select-sm rounded-pill fw-semibold ${data.badge_class}`;
            showToast(data.message);
        } else {
            alert('Failed to update status.');
        }
    })
    .catch(() => alert('Server connection error.'));
}

function openNotesModal(id, currentNotes) {
    document.getElementById('notesReqId').value = id;
    document.getElementById('notesTextarea').value = currentNotes || '';
    new bootstrap.Modal(document.getElementById('notesModal')).show();
}

function saveAdminNotes(e) {
    e.preventDefault();
    const id = document.getElementById('notesReqId').value;
    const notes = document.getElementById('notesTextarea').value;

    fetch(`/admin/book-requests/${id}/notes`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ admin_notes: notes })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const noteEl = document.getElementById(`notesText-${id}`);
            if (noteEl) noteEl.textContent = notes || 'No notes';
            bootstrap.Modal.getInstance(document.getElementById('notesModal')).hide();
            showToast('Admin notes saved successfully!');
        }
    })
    .catch(() => alert('Failed to save notes.'));
}

function openViewModal(req) {
    document.getElementById('vReqId').textContent = req.id;
    document.getElementById('vBookTitle').textContent = req.book_title;
    document.getElementById('vAuthorName').textContent = req.author_name ? 'Author: ' + req.author_name : 'Author not specified';
    document.getElementById('vCustomerName').textContent = req.customer_name || 'Unnamed';
    document.getElementById('vCustomerPhone').textContent = req.customer_phone || '-';
    document.getElementById('vCustomerEmail').textContent = req.customer_email || '-';
    document.getElementById('vAdditionalInfo').textContent = req.additional_info || 'No additional information';
    document.getElementById('vAdminNotes').textContent = req.admin_notes || 'No internal notes';
    new bootstrap.Modal(document.getElementById('viewRequestModal')).show();
}

function toggleSelectAll(master) {
    document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = master.checked);
    onRowCheckboxChange();
}

function onRowCheckboxChange() {
    const checked = document.querySelectorAll('.row-checkbox:checked');
    const bulkBar = document.getElementById('bulkActionBar');
    const badge = document.getElementById('selectedCountBadge');

    if (checked.length > 0) {
        bulkBar.style.display = 'flex';
        badge.textContent = checked.length;
    } else {
        bulkBar.style.display = 'none';
        document.getElementById('selectAllCheckbox').checked = false;
    }
}

function handleBulkActionSubmit() {
    const checked = document.querySelectorAll('.row-checkbox:checked');
    if (checked.length === 0) {
        Swal.fire({ title: 'অনুরোধ নির্বাচন করুন', text: 'অনুগ্রহ করে অন্তত একটি অনুরোধ সিলেক্ট করুন।', icon: 'warning' });
        return;
    }
    const action = document.querySelector('select[name="bulk_action"]').value;
    if (!action) {
        Swal.fire({ title: 'অ্যাকশন নির্বাচন করুন', text: 'অনুগ্রহ করে একটি অ্যাকশন বেছে নিন।', icon: 'info' });
        return;
    }

    SwalConfirm({
        title: 'বাল্ক অ্যাকশন নিশ্চিতকরণ',
        text: `আপনি কি নিশ্চিত যে নির্বাচিত ${checked.length}টি অনুরোধে এই অ্যাকশন প্রয়োগ করতে চান?`,
        icon: 'question',
        confirmButtonText: '<i class="fas fa-check me-1"></i> হ্যাঁ, প্রয়োগ করুন',
    }).then(function(result) {
        if (result.isConfirmed) {
            document.getElementById('bulkForm').submit();
        }
    });
}

function showToast(msg) {
    const alertDiv = document.createElement('div');
    alertDiv.className = 'position-fixed bottom-0 end-0 p-3 z-3';
    alertDiv.innerHTML = `
        <div class="toast show align-items-center text-white bg-dark border-0 shadow-lg rounded-3" role="alert">
            <div class="d-flex">
                <div class="toast-body small fw-semibold">
                    <i class="fas fa-check-circle text-success me-1.5"></i> ${msg}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;
    document.body.appendChild(alertDiv);
    setTimeout(() => alertDiv.remove(), 3500);
}
</script>

<style>
.transition-hover {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.transition-hover:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px -4px rgba(0, 0, 0, 0.08) !important;
}
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.shadow-xs {
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
}
</style>

@endsection
