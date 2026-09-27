@extends('layouts.admin')

@section('title', 'Libraries & Book Grants — ideaabd')

@push('styles')
<style>
    .kpi-card-custom {
        background: #ffffff;
        border-radius: 16px;
        padding: 20px 22px;
        box-shadow: 0 4px 18px rgba(0,0,0,0.04);
        border: 1px solid #f1f5f9;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        height: 100%;
    }
    .kpi-card-custom:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.08);
    }
    .kpi-icon-box {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.35rem;
    }
    .badge-ack-received {
        background-color: #dcfce7;
        color: #166534;
        border: 1px solid #bbf7d0;
        font-weight: 600;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11.5px;
    }
    .badge-ack-dispatched {
        background-color: #fef9c3;
        color: #854d0e;
        border: 1px solid #fef08a;
        font-weight: 600;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11.5px;
    }
    .badge-ack-pending {
        background-color: #f1f5f9;
        color: #475569;
        border: 1px solid #e2e8f0;
        font-weight: 600;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11.5px;
    }
    .table-lib-custom th {
        background-color: #f8fafc;
        color: #475569;
        font-weight: 700;
        font-size: 12.5px;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        padding: 12px 14px;
    }
    .table-lib-custom td {
        padding: 14px 14px;
        vertical-align: middle;
        font-size: 13.5px;
    }
    .btn-action-icon {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        transition: all 0.15s ease;
    }
    .book-item-row {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 10px 12px;
        margin-bottom: 8px;
        transition: all 0.2s ease;
    }
    .book-item-row:hover {
        background: #ffffff;
        border-color: #cbd5e1;
        box-shadow: 0 3px 10px rgba(0,0,0,0.04);
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-3 px-md-4 py-4">

    {{-- Breadcrumb & Title --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.event-campaigns.index') }}" class="text-decoration-none text-muted">Campaigns</a></li>
                    <li class="breadcrumb-item active fw-semibold text-success" aria-current="page">Libraries & Book Grants</li>
                </ol>
            </nav>
            <h1 class="h3 fw-bold mb-0 text-gray-900 d-flex align-items-center gap-2">
                <i class="fa-solid fa-book-open-reader text-success"></i> Libraries & Book Grants Dashboard
            </h1>
            <p class="text-muted small mb-0 mt-1">Manage library registrations, itemized book allocations, delivery dispatch, and receipt verification.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.libraries.export') }}" class="btn btn-outline-secondary rounded-pill px-3 py-2 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-2xs">
                <i class="fa-solid fa-file-csv text-success"></i> CSV Export
            </a>
            <a href="{{ url('/pathagar') }}" target="_blank" class="btn btn-success rounded-pill px-3.5 py-2 fw-semibold shadow-sm d-inline-flex align-items-center gap-2">
                <i class="fa-solid fa-plus"></i> New Application Form <i class="fa-solid fa-arrow-up-right-from-square small opacity-75"></i>
            </a>
        </div>
    </div>

    {{-- Flash Notifications --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-2xs border-0 p-3 mb-4 d-flex align-items-center gap-2" role="alert">
            <i class="fa-solid fa-circle-check fs-5 text-success"></i>
            <div class="fw-semibold">{{ session('success') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- 5 KPI STATS CARDS --}}
    <div class="row g-3 mb-4">
        {{-- Total Libraries --}}
        <div class="col-6 col-lg">
            <div class="kpi-card-custom">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold text-uppercase">Total Libraries</span>
                    <div class="kpi-icon-box bg-primary-subtle text-primary">
                        <i class="fa-solid fa-landmark"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-0 text-dark">{{ number_format($totalLibraries) }}</h3>
                <small class="text-muted d-block mt-1" style="font-size: 11.5px;">Registered Institutions</small>
            </div>
        </div>

        {{-- Approved Libraries --}}
        <div class="col-6 col-lg">
            <div class="kpi-card-custom">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold text-uppercase">Approved Libraries</span>
                    <div class="kpi-icon-box bg-success-subtle text-success">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-0 text-success">{{ number_format($approvedLibraries) }}</h3>
                <small class="text-success d-block mt-1" style="font-size: 11.5px;">Selected for Grant</small>
            </div>
        </div>

        {{-- Total Books Allocated --}}
        <div class="col-6 col-lg">
            <div class="kpi-card-custom">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold text-uppercase">Total Books Allocated</span>
                    <div class="kpi-icon-box bg-info-subtle text-info">
                        <i class="fa-solid fa-boxes-stacked"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-0 text-dark">{{ number_format($totalBooksAllocated) }} <span class="fs-6 fw-normal text-muted">Books</span></h3>
                <small class="text-muted d-block mt-1" style="font-size: 11.5px;">Dispatched / Assigned</small>
            </div>
        </div>

        {{-- Acknowledged Count --}}
        <div class="col-6 col-lg">
            <div class="kpi-card-custom">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold text-uppercase">Acknowledged</span>
                    <div class="kpi-icon-box bg-emerald-subtle text-success" style="background-color: #ecfdf5; color: #047857;">
                        <i class="fa-solid fa-signature"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-0 text-success">{{ number_format($acknowledgedCount) }} <span class="fs-6 fw-normal text-muted">Libraries</span></h3>
                <small class="text-muted d-block mt-1" style="font-size: 11.5px;">{{ number_format($totalBooksReceived) }} Books Received</small>
            </div>
        </div>

        {{-- Pending Review --}}
        <div class="col-12 col-lg">
            <div class="kpi-card-custom">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold text-uppercase">Pending Review</span>
                    <div class="kpi-icon-box bg-warning-subtle text-warning">
                        <i class="fa-solid fa-hourglass-half"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-0 text-warning">{{ number_format($pendingReview) }}</h3>
                <small class="text-muted d-block mt-1" style="font-size: 11.5px;">Awaiting Verification</small>
            </div>
        </div>
    </div>

    {{-- FILTER BAR --}}
    <div class="card border-0 shadow-2xs rounded-4 mb-4 bg-white">
        <div class="card-body p-3">
            <form action="{{ route('admin.libraries.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-12 col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                        <input type="text" name="search" class="form-control bg-light border-start-0 ps-0" placeholder="Search by library name, phone, rep, or Reg No..." value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-6 col-md-2">
                    <select name="division" class="form-select bg-light">
                        <option value="">All Divisions</option>
                        @foreach(['Rangpur' => 'রংপুর', 'Dhaka' => 'ঢাকা', 'Chattogram' => 'চট্টগ্রাম', 'Rajshahi' => 'রাজশাহী', 'Khulna' => 'খুলনা', 'Barishal' => 'বরিশাল', 'Sylhet' => 'সিলেট', 'Mymensingh' => 'ময়মনসিংহ'] as $engDiv => $bngDiv)
                            <option value="{{ $bngDiv }}" {{ request('division') == $bngDiv ? 'selected' : '' }}>{{ $engDiv }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-md-2">
                    <select name="status" class="form-select bg-light">
                        <option value="">All Approvals</option>
                        <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Approved</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>

                <div class="col-6 col-md-2">
                    <select name="ack_status" class="form-select bg-light">
                        <option value="">All Receipt Status</option>
                        <option value="acknowledged" {{ request('ack_status') == 'acknowledged' ? 'selected' : '' }}>✅ Acknowledged</option>
                        <option value="dispatched" {{ request('ack_status') == 'dispatched' ? 'selected' : '' }}>📦 Dispatched (Pending)</option>
                        <option value="pending" {{ request('ack_status') == 'pending' ? 'selected' : '' }}>⏳ Not Allocated</option>
                    </select>
                </div>

                <div class="col-6 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-success rounded-pill px-3 fw-semibold flex-grow-1">
                        <i class="fa-solid fa-filter me-1"></i> Filter
                    </button>
                    @if(request()->hasAny(['search', 'division', 'status', 'ack_status']))
                        <a href="{{ route('admin.libraries.index') }}" class="btn btn-light rounded-pill px-3 border" title="Reset Filters"><i class="fa-solid fa-rotate-left"></i></a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- LIBRARIES DATA TABLE --}}
    <div class="card border-0 shadow-2xs rounded-4 bg-white overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 table-lib-custom">
                <thead>
                    <tr>
                        <th class="ps-3" style="width: 45px;">#</th>
                        <th>Library & Institution</th>
                        <th>Representative & Contact</th>
                        <th>Location & Address</th>
                        <th>Readers / Books</th>
                        <th>📦 Allocation & Dispatch</th>
                        <th>✍️ Acknowledgment</th>
                        <th>Approval</th>
                        <th class="pe-3 text-end" style="width: 140px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($libraries as $lib)
                        @php
                            $fd = $lib->form_data ?? [];
                            $isApproved = in_array($lib->status, ['confirmed', 'selected', 'approved'], true);
                            $allocated = intval($fd['books_allocated'] ?? 0);
                            $dispatchedDate = $fd['dispatched_date'] ?? null;
                            $receivedCount = intval($fd['received_books_count'] ?? 0);
                            $receivedDate = $fd['received_date'] ?? null;
                            $ackStatus = $lib->acknowledgment_status;
                            $libName = $lib->institution_or_org ?: ($fd['library_name'] ?? 'Library');
                            $libType = $fd['library_type'] ?? ($lib->designation_or_class ?: 'Public Library');
                            $bookItems = $fd['allocated_books_list'] ?? [];
                        @endphp
                        <tr id="row-lib-{{ $lib->id }}">
                            <td class="ps-3 text-muted fw-bold">{{ $lib->id }}</td>

                            {{-- Library Name & Type --}}
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-3 bg-success-subtle text-success p-2 flex-shrink-0 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                                        <i class="fa-solid fa-landmark"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark" style="font-size: 14px;">{{ $libName }}</div>
                                        <div class="d-flex align-items-center gap-1.5 flex-wrap mt-0.5">
                                            <span class="badge bg-light text-secondary border px-1.5 py-0.5" style="font-size: 10.5px;">{{ $libType }}</span>
                                            @if(!empty($fd['established_year']))
                                                <span class="badge bg-light text-muted border px-1.5 py-0.5" style="font-size: 10px;">Est: {{ $fd['established_year'] }}</span>
                                            @endif
                                            <span class="text-muted small font-monospace" style="font-size: 11px;">#{{ $lib->registration_number }}</span>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            {{-- Representative & Contact --}}
                            <td>
                                <div class="fw-semibold text-dark">{{ $lib->name }}</div>
                                <div class="small">
                                    <a href="tel:{{ $lib->phone }}" class="text-decoration-none text-muted hover-success fw-medium">
                                        <i class="fa-solid fa-phone me-1 text-success small"></i>{{ $lib->phone }}
                                    </a>
                                </div>
                                @if($lib->email)
                                    <div class="text-muted small" style="font-size: 11px;">{{ $lib->email }}</div>
                                @endif
                            </td>

                            {{-- Location & Address --}}
                            <td>
                                <div class="fw-semibold text-dark small">
                                    {{ $lib->district ?? '—' }}{{ $lib->thana ? ', ' . $lib->thana : '' }}
                                </div>
                                <div class="text-muted small text-truncate" style="max-width: 180px; font-size: 11.5px;" title="{{ $lib->address }}">
                                    {{ $fd['division'] ? $fd['division'] . ' • ' : '' }}{{ $lib->address }}
                                </div>
                            </td>

                            {{-- Readers / Current Books --}}
                            <td>
                                <div class="small">
                                    <span class="text-muted">Readers:</span> <span class="fw-semibold text-dark">{{ $fd['reader_count'] ?? '—' }}</span>
                                </div>
                                <div class="small text-muted" style="font-size: 11.5px;">
                                    Current Books: <span class="fw-medium text-dark">{{ $fd['current_book_count'] ?? '—' }}</span>
                                </div>
                            </td>

                            {{-- Allocation & Dispatch Info --}}
                            <td>
                                @if($allocated > 0)
                                    <div class="fw-bold text-success fs-6">
                                        {{ $allocated }} <span class="small fw-normal text-muted">Books</span>
                                        @if(count($bookItems) > 0)
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill ms-1" style="font-size: 10px;">{{ count($bookItems) }} titles</span>
                                        @endif
                                    </div>
                                    <div class="text-muted small" style="font-size: 11px;">
                                        <i class="fa-regular fa-calendar-check me-1"></i>{{ $dispatchedDate ? date('d M, Y', strtotime($dispatchedDate)) : 'No date' }}
                                    </div>
                                    @if(!empty($fd['dispatch_tracking_no']))
                                        <span class="badge bg-light text-muted border px-1.5 py-0.5 mt-0.5" style="font-size: 10px;">Trk: {{ $fd['dispatch_tracking_no'] }}</span>
                                    @endif
                                @else
                                    <span class="badge bg-light text-muted border">Not Allocated</span>
                                @endif
                            </td>

                            {{-- Acknowledgment Info --}}
                            <td>
                                @if($ackStatus === 'acknowledged')
                                    <span class="badge-ack-received d-inline-flex align-items-center gap-1 mb-1">
                                        <i class="fa-solid fa-circle-check text-success"></i> Received ({{ $receivedCount ?: $allocated }} Books)
                                    </span>
                                    @if($receivedDate)
                                        <div class="text-muted small" style="font-size: 11px;">
                                            {{ date('d M, Y', strtotime($receivedDate)) }}
                                        </div>
                                    @endif
                                @elseif($allocated > 0)
                                    <span class="badge-ack-dispatched d-inline-flex align-items-center gap-1">
                                        <i class="fa-solid fa-truck-fast"></i> Dispatched (Pending)
                                    </span>
                                @else
                                    <span class="badge-ack-pending">Pending Allocation</span>
                                @endif
                            </td>

                            {{-- Approval Status --}}
                            <td>
                                <button type="button" class="btn btn-sm rounded-pill px-2.5 py-1 fw-semibold {{ $isApproved ? 'btn-success-subtle text-success border-success-subtle' : 'btn-warning-subtle text-warning border-warning-subtle' }}"
                                        onclick="toggleApproval('{{ $lib->id }}', this)" title="Click to toggle status">
                                    <i class="fa-solid {{ $isApproved ? 'fa-circle-check' : 'fa-hourglass-half' }} me-1"></i>
                                    <span>{{ $isApproved ? 'Approved' : 'Pending' }}</span>
                                </button>
                            </td>

                            {{-- Action Buttons --}}
                            <td class="pe-3 text-end">
                                <div class="d-inline-flex align-items-center gap-1">
                                    {{-- 1. Dispatch Modal Button (Itemized Books Entry) --}}
                                    <button type="button" class="btn btn-sm btn-outline-success btn-action-icon" title="Allocate & Itemize Books"
                                            onclick='openDispatchModal("{{ $lib->id }}", "{{ addslashes($libName) }}", "{{ $allocated }}", "{{ $dispatchedDate }}", "{{ addslashes($fd['dispatch_tracking_no'] ?? '') }}", "{{ addslashes($fd['dispatch_notes'] ?? '') }}", {{ json_encode($bookItems) }})'>
                                        <i class="fa-solid fa-boxes-stacked"></i>
                                    </button>

                                    {{-- 2. Acknowledgment Button --}}
                                    <button type="button" class="btn btn-sm btn-outline-primary btn-action-icon" title="Receipt Acknowledgment & Verify"
                                            onclick='openAckModal("{{ $lib->id }}", "{{ addslashes($libName) }}", "{{ $allocated }}", "{{ $receivedCount ?: $allocated }}", "{{ $receivedDate }}", "{{ addslashes($fd['acknowledgment_notes'] ?? '') }}", {{ json_encode($bookItems) }})'>
                                        <i class="fa-solid fa-signature"></i>
                                    </button>

                                    {{-- 3. Print Slip --}}
                                    <a href="{{ route('admin.libraries.print', $lib->id) }}" target="_blank" class="btn btn-sm btn-outline-secondary btn-action-icon" title="Print Slip & Token">
                                        <i class="fa-solid fa-print"></i>
                                    </a>

                                    {{-- 4. Details Modal --}}
                                    <button type="button" class="btn btn-sm btn-light border btn-action-icon" title="View Full Profile & Book List"
                                            onclick="openDetailsModal({{ json_encode($lib) }})">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-book-open-reader fs-1 text-secondary mb-3 d-block opacity-50"></i>
                                <div class="fw-semibold fs-6">No library applications found.</div>
                                <small class="text-muted">Reset filters or create a new library registration.</small>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($libraries->hasPages())
            <div class="card-footer bg-white border-top p-3 d-flex align-items-center justify-content-between">
                <div class="small text-muted">
                    Showing {{ $libraries->firstItem() }} to {{ $libraries->lastItem() }} of {{ $libraries->total() }} libraries
                </div>
                <div>{{ $libraries->links('pagination::bootstrap-5') }}</div>
            </div>
        @endif
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════
     MODAL 1: Book Allocation & Itemized Books Dispatch Entry Form
══════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="dispatchModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow">
            <form id="dispatchForm" method="POST" action="">
                @csrf
                <div class="modal-header border-bottom p-3.5 bg-success text-white rounded-top-4">
                    <h5 class="modal-title fw-bold fs-6 d-flex align-items-center gap-2">
                        <i class="fa-solid fa-boxes-stacked"></i> Book Allocation & Dispatch Entry
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3 bg-light p-3 rounded-3 border">
                        <div>
                            <span class="text-muted small text-uppercase fw-semibold d-block">Library / Institution</span>
                            <span class="fw-bold text-dark fs-6" id="dispatchLibName">—</span>
                        </div>
                        <div class="text-end">
                            <span class="text-muted small text-uppercase fw-semibold d-block">Total Books Calculated</span>
                            <span class="badge bg-success fs-6 fw-bold font-monospace" id="calcTotalBadge">0 Books</span>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small text-dark">Total Books Allocated <span class="text-danger">*</span></label>
                            <input type="number" name="books_allocated" id="modalBooksAllocated" class="form-control rounded-3 fw-bold text-success" placeholder="e.g. 50" min="1" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small text-dark">Dispatch Date <span class="text-danger">*</span></label>
                            <input type="date" name="dispatched_date" id="modalDispatchedDate" class="form-control rounded-3" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small text-dark">Delivery Tracking No</label>
                            <input type="text" name="dispatch_tracking_no" id="modalDispatchTracking" class="form-control rounded-3" placeholder="e.g. SA Paribahan Trk #">
                        </div>
                    </div>

                    {{-- Dynamic Itemized Books Breakdown Table (বইয়ের তথ্য টেবিল) --}}
                    <div class="card border rounded-3 mb-3 overflow-hidden shadow-sm">
                        <div class="card-header bg-light py-2.5 px-3 d-flex align-items-center justify-content-between border-bottom">
                            <div>
                                <span class="fw-bold text-dark small d-flex align-items-center gap-1.5">
                                    <i class="fa-solid fa-table-list text-success"></i> Itemized Books Breakdown Table (বইয়ের তথ্য ও তালিকা)
                                </span>
                                <small class="text-muted" style="font-size: 11px;">Enter specific book titles, authors, categories, and copy counts</small>
                            </div>
                            <div class="d-flex align-items-center gap-1.5">
                                <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2 py-1 fw-semibold" onclick="addMultipleBookRows(3)">
                                    <i class="fa-solid fa-plus me-1"></i> +3 Rows
                                </button>
                                <button type="button" class="btn btn-xs btn-success rounded-pill px-2.5 py-1 fw-bold shadow-sm" onclick="addBookRow()">
                                    <i class="fa-solid fa-plus me-1"></i> Add Book
                                </button>
                            </div>
                        </div>
                        <div class="table-responsive" style="max-height: 280px; overflow-y: auto;">
                            <table class="table table-sm table-bordered mb-0 align-middle" id="booksBreakdownTable" style="font-size: 12.5px;">
                                <thead class="table-light text-muted sticky-top bg-light" style="font-size: 11.5px; z-index: 1;">
                                    <tr>
                                        <th class="text-center py-2" style="width: 38px;">#</th>
                                        <th class="py-2" style="min-width: 170px;">Book Title (বইয়ের নাম) <span class="text-danger">*</span></th>
                                        <th class="py-2" style="min-width: 130px;">Author / Writer (লেখক)</th>
                                        <th class="py-2" style="min-width: 120px;">Category / Subject</th>
                                        <th class="text-center py-2" style="width: 85px;">Copies <span class="text-danger">*</span></th>
                                        <th class="text-center py-2" style="width: 44px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="bookItemsContainer">
                                    {{-- Dynamically populated rows --}}
                                </tbody>
                                <tfoot class="table-light border-top fw-bold" style="font-size: 12px;">
                                    <tr>
                                        <td colspan="4" class="text-end py-2 text-dark">Total Calculated Books:</td>
                                        <td class="text-center py-2 text-success fs-6 fw-bold" id="tfootTotalCopies">0</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-dark">Package Notes / Delivery Instructions</label>
                        <textarea name="dispatch_notes" id="modalDispatchNotes" rows="2" class="form-control rounded-3" placeholder="e.g. Sent via courier in 2 cartoon boxes..."></textarea>
                    </div>

                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" name="send_sms_alert" value="1" id="dispatchSmsSwitch" checked>
                        <label class="form-check-label small fw-semibold text-dark" for="dispatchSmsSwitch">
                            Send SMS notification & receipt link to representative mobile
                        </label>
                    </div>
                </div>
                <div class="modal-footer border-top p-3">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-semibold shadow-sm" id="btnSaveDispatch">
                        <i class="fa-solid fa-check me-1"></i> Save & Dispatch
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════
     MODAL 2: Receipt Acknowledgment & Verification Form
══════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="ackModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form id="ackForm" method="POST" action="">
                @csrf
                <div class="modal-header border-bottom p-3.5 bg-primary text-white rounded-top-4">
                    <h5 class="modal-title fw-bold fs-6 d-flex align-items-center gap-2">
                        <i class="fa-solid fa-signature"></i> Book Receipt Acknowledgment & Verification
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3 bg-light p-3 rounded-3 border">
                        <label class="form-label fw-semibold small text-muted text-uppercase mb-1">Library / Institution</label>
                        <div class="fw-bold text-dark fs-6" id="ackLibName">—</div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-dark">Received Book Count <span class="text-danger">*</span></label>
                            <input type="number" name="received_books_count" id="modalReceivedCount" class="form-control rounded-3" placeholder="e.g. 50" min="1" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-dark">Received Date <span class="text-danger">*</span></label>
                            <input type="date" name="received_date" id="modalReceivedDate" class="form-control rounded-3" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-dark">Acknowledgment Notes / Representative Remarks</label>
                        <textarea name="acknowledgment_notes" id="modalAckNotes" rows="3" class="form-control rounded-3" placeholder="Received all books in good condition. Thank you..."></textarea>
                    </div>

                    <div class="alert alert-info rounded-3 p-2.5 small mb-0 d-flex align-items-center gap-2">
                        <i class="fa-solid fa-info-circle text-primary fs-5"></i>
                        <div>Saving this will mark the library status as <strong>"Receipt Acknowledged"</strong>.</div>
                    </div>
                </div>
                <div class="modal-footer border-top p-3">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold" id="btnSaveAck">
                        <i class="fa-solid fa-signature me-1"></i> Confirm Acknowledgment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════
     MODAL 3: Library Full Profile & Details
══════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="detailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom p-3.5 bg-light rounded-top-4">
                <h5 class="modal-title fw-bold fs-6 text-dark d-flex align-items-center gap-2">
                    <i class="fa-solid fa-landmark text-success"></i> Library Profile & Full Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="detailsModalBody">
                {{-- Dynamically populated by JS --}}
            </div>
            <div class="modal-footer border-top p-3 bg-light">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let bookRowIndex = 0;

function addBookRow(title = '', author = '', category = '', copies = 1) {
    const container = document.getElementById('bookItemsContainer');
    const currentRowNumber = container.querySelectorAll('.book-item-tr').length + 1;
    const rowId = `book-row-${bookRowIndex}`;
    
    const rowHtml = `
        <tr class="book-item-tr" id="${rowId}">
            <td class="text-center text-muted fw-bold row-index-td">${currentRowNumber}</td>
            <td>
                <input type="text" name="book_items[${bookRowIndex}][title]" class="form-control form-control-sm bg-white" placeholder="e.g. কালবেলা / বিশ্ব সাহিত্যের সেরা গল্প" value="${title}" required>
            </td>
            <td>
                <input type="text" name="book_items[${bookRowIndex}][author]" class="form-control form-control-sm bg-white" placeholder="e.g. সমরেশ মজুমদার" value="${author}">
            </td>
            <td>
                <input type="text" name="book_items[${bookRowIndex}][category]" class="form-control form-control-sm bg-white" placeholder="e.g. উপন্যাস / ইতিহাস" value="${category}">
            </td>
            <td>
                <input type="number" name="book_items[${bookRowIndex}][copies]" class="form-control form-control-sm bg-white book-copies-input text-center fw-bold text-success" placeholder="1" value="${copies}" min="1" oninput="recalcTotalCopies()" required>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger p-1 border-0 rounded-circle" onclick="removeBookRow('${rowId}')" title="Remove Book">
                    <i class="fa-solid fa-trash-can"></i>
                </button>
            </td>
        </tr>
    `;
    
    container.insertAdjacentHTML('beforeend', rowHtml);
    bookRowIndex++;
    updateRowIndices();
    recalcTotalCopies();
}

function addMultipleBookRows(count = 3) {
    for (let i = 0; i < count; i++) {
        addBookRow('', '', '', 1);
    }
}

function removeBookRow(rowId) {
    const row = document.getElementById(rowId);
    if (row) {
        row.remove();
        updateRowIndices();
        recalcTotalCopies();
    }
}

function updateRowIndices() {
    const rows = document.querySelectorAll('#bookItemsContainer .book-item-tr');
    rows.forEach((row, idx) => {
        const indexTd = row.querySelector('.row-index-td');
        if (indexTd) {
            indexTd.textContent = idx + 1;
        }
    });
}

function recalcTotalCopies() {
    const inputs = document.querySelectorAll('.book-copies-input');
    let total = 0;
    inputs.forEach(input => {
        const val = parseInt(input.value) || 0;
        total += val;
    });
    
    document.getElementById('calcTotalBadge').textContent = total + ' Books';
    const tfootTotal = document.getElementById('tfootTotalCopies');
    if (tfootTotal) tfootTotal.textContent = total;
    
    if (total > 0) {
        document.getElementById('modalBooksAllocated').value = total;
    }
}

function openDispatchModal(id, name, allocated, date, tracking, notes, bookItems = []) {
    document.getElementById('dispatchLibName').textContent = name;
    document.getElementById('modalBooksAllocated').value = allocated > 0 ? allocated : '';
    document.getElementById('modalDispatchedDate').value = date ? date : '{{ date("Y-m-d") }}';
    document.getElementById('modalDispatchTracking').value = tracking || '';
    document.getElementById('modalDispatchNotes').value = notes || '';
    document.getElementById('dispatchForm').action = "{{ url('/admin/libraries') }}/" + id + "/dispatch";
    
    // Clear and populate book items repeater
    const container = document.getElementById('bookItemsContainer');
    container.innerHTML = '';
    bookRowIndex = 0;
    
    if (Array.isArray(bookItems) && bookItems.length > 0) {
        bookItems.forEach(item => {
            addBookRow(item.title || '', item.author || '', item.category || '', item.copies || 1);
        });
    } else {
        // Add 2 initial rows
        addBookRow('', '', 'General / Literature', allocated > 0 ? allocated : 10);
    }
    
    recalcTotalCopies();
    new bootstrap.Modal(document.getElementById('dispatchModal')).show();
}

function openAckModal(id, name, allocated, received, date, notes) {
    document.getElementById('ackLibName').textContent = name;
    document.getElementById('modalReceivedCount').value = received > 0 ? received : (allocated > 0 ? allocated : '');
    document.getElementById('modalReceivedDate').value = date ? date : '{{ date("Y-m-d") }}';
    document.getElementById('modalAckNotes').value = notes || '';
    document.getElementById('ackForm').action = "{{ url('/admin/libraries') }}/" + id + "/acknowledgment";
    
    new bootstrap.Modal(document.getElementById('ackModal')).show();
}

function toggleApproval(id, btn) {
    btn.disabled = true;
    const originalHtml = btn.innerHTML;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';

    fetch("{{ url('/admin/libraries') }}/" + id + "/toggle-approval", {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": "{{ csrf_token() }}",
            "Accept": "application/json",
            "Content-Type": "application/json"
        }
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        if (data.approved) {
            btn.className = "btn btn-sm rounded-pill px-2.5 py-1 fw-semibold btn-success-subtle text-success border-success-subtle";
            btn.innerHTML = '<i class="fa-solid fa-circle-check me-1"></i><span>Approved</span>';
        } else {
            btn.className = "btn btn-sm rounded-pill px-2.5 py-1 fw-semibold btn-warning-subtle text-warning border-warning-subtle";
            btn.innerHTML = '<i class="fa-solid fa-hourglass-half me-1"></i><span>Pending</span>';
        }
    })
    .catch(() => {
        btn.disabled = false;
        btn.innerHTML = originalHtml;
        alert('Failed to toggle approval status.');
    });
}

function openDetailsModal(lib) {
    const fd = lib.form_data || {};
    const genres = Array.isArray(fd.preferred_genres) ? fd.preferred_genres.join(', ') : (fd.preferred_genres || 'All Genres');
    const bookList = Array.isArray(fd.allocated_books_list) ? fd.allocated_books_list : [];
    
    let bookListTable = '';
    if (bookList.length > 0) {
        bookListTable = `
            <div class="col-12 mt-3">
                <div class="card border rounded-3 overflow-hidden shadow-sm">
                    <div class="card-header bg-success-subtle py-2 px-3 fw-bold text-success small d-flex align-items-center justify-content-between">
                        <span><i class="fa-solid fa-boxes-stacked me-1"></i> Itemized Allocated Books (${bookList.length} Titles)</span>
                        <span class="badge bg-success">${fd.books_allocated || 0} Copies Total</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-striped table-bordered mb-0 small align-middle">
                            <thead class="table-light text-muted">
                                <tr>
                                    <th class="text-center py-2" style="width: 40px;">#</th>
                                    <th class="py-2">Book Title (বইয়ের নাম)</th>
                                    <th class="py-2">Author / Writer (লেখক)</th>
                                    <th class="py-2">Category / Subject</th>
                                    <th class="text-center py-2" style="width: 80px;">Copies</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${bookList.map((b, i) => `
                                    <tr>
                                        <td class="text-center text-muted fw-semibold">${i + 1}</td>
                                        <td class="fw-bold text-dark">${b.title || '—'}</td>
                                        <td class="text-muted">${b.author || '—'}</td>
                                        <td class="text-muted">${b.category || '—'}</td>
                                        <td class="text-center fw-bold text-success">${b.copies || 1}</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        `;
    }

    let html = `
        <div class="row g-3">
            <div class="col-md-6">
                <div class="card bg-light border-0 p-3 rounded-3 h-100">
                    <h6 class="fw-bold text-success mb-2"><i class="fa-solid fa-landmark me-1"></i> Library Details</h6>
                    <div class="small mb-1"><strong>Name:</strong> ${lib.institution_or_org || fd.library_name || '—'}</div>
                    <div class="small mb-1"><strong>Type:</strong> ${fd.library_type || lib.designation_or_class || '—'}</div>
                    <div class="small mb-1"><strong>Reg No:</strong> ${fd.reg_no || 'N/A'}</div>
                    <div class="small mb-1"><strong>Est. Year:</strong> ${fd.established_year || '—'}</div>
                    <div class="small mb-1"><strong>Registration ID:</strong> <span class="font-monospace text-primary">#${lib.registration_number}</span></div>
                    <div class="small"><strong>Application Date:</strong> ${lib.created_at ? lib.created_at.substring(0,10) : '—'}</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card bg-light border-0 p-3 rounded-3 h-100">
                    <h6 class="fw-bold text-primary mb-2"><i class="fa-solid fa-user-shield me-1"></i> Representative & Contact</h6>
                    <div class="small mb-1"><strong>Representative:</strong> ${lib.name || '—'}</div>
                    <div class="small mb-1"><strong>Phone:</strong> <a href="tel:${lib.phone}" class="fw-semibold text-dark">${lib.phone || '—'}</a></div>
                    <div class="small mb-1"><strong>Alt Phone:</strong> ${fd.guardian_phone || '—'}</div>
                    <div class="small mb-1"><strong>Email:</strong> ${lib.email || '—'}</div>
                    <div class="small"><strong>Address:</strong> ${lib.address || '—'}, ${lib.thana || ''}, ${lib.district || ''}</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card bg-light border-0 p-3 rounded-3 h-100">
                    <h6 class="fw-bold text-dark mb-2"><i class="fa-solid fa-book-bookmark me-1"></i> Statistics & Requirements</h6>
                    <div class="small mb-1"><strong>Reader Count:</strong> ${fd.reader_count || '—'}</div>
                    <div class="small mb-1"><strong>Current Books:</strong> ${fd.current_book_count || '—'}</div>
                    <div class="small mb-1"><strong>Preferred Subjects:</strong> <span class="text-secondary">${genres}</span></div>
                    <div class="small"><strong>Delivery Method:</strong> ${fd.delivery_method || 'Direct'}</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card bg-light border-0 p-3 rounded-3 h-100">
                    <h6 class="fw-bold text-warning text-dark mb-2"><i class="fa-solid fa-boxes-stacked me-1"></i> Allocation & Acknowledgment</h6>
                    <div class="small mb-1"><strong>Allocated Books:</strong> <span class="fw-bold text-success">${fd.books_allocated || 0} Books</span></div>
                    <div class="small mb-1"><strong>Dispatch Date:</strong> ${fd.dispatched_date || 'Not yet dispatched'}</div>
                    <div class="small mb-1"><strong>Received Books:</strong> <span class="fw-bold text-primary">${fd.received_books_count || 0} Books</span></div>
                    <div class="small mb-1"><strong>Received Date:</strong> ${fd.received_date || 'Pending'}</div>
                    <div class="small"><strong>Notes / Remarks:</strong> ${fd.acknowledgment_notes || fd.remarks || fd.scholarship_reason || '—'}</div>
                </div>
            </div>
            ${bookListTable}
        </div>
    `;

    document.getElementById('detailsModalBody').innerHTML = html;
    new bootstrap.Modal(document.getElementById('detailsModal')).show();
}
</script>
@endpush
@endsection
