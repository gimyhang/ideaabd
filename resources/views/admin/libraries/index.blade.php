@extends('layouts.admin')

@section('title', 'পাঠাগার ড্যাশবোর্ড ও বই অনুদান — ideaabd')

@push('styles')
<style>
    :root {
        --lib-theme: {{ $formSettings['theme_color'] ?? '#047857' }};
        --lib-theme-hover: #065f46;
    }
    .kpi-card-custom {
        background: #ffffff;
        border-radius: 16px;
        padding: 18px 20px;
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
        width: 46px;
        height: 46px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
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
        font-size: 12px;
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
    .div-stat-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        color: #334155;
        transition: all 0.2s;
    }
    .div-stat-pill:hover {
        background: #ecfdf5;
        border-color: #a7f3d0;
        color: #047857;
    }

    /* Live Print Mockup inside Modal */
    .live-preview-box {
        border: 1.5px solid #334155;
        background: #ffffff;
        padding: 14px 16px;
        border-radius: 8px;
        font-family: 'Hind Siliguri', Arial, sans-serif;
        font-size: 11.5px;
        color: #000;
        box-shadow: 0 4px 14px rgba(0,0,0,0.06);
    }
    .live-header-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 6px;
    }
    .live-header-table td {
        vertical-align: top;
        border: 1.2px solid #000;
        padding: 6px;
    }
    .live-banner-strip {
        background: {{ $formSettings['theme_color'] ?? '#047857' }};
        color: #fff;
        padding: 4px 8px;
        font-size: 12px;
        font-weight: bold;
        text-align: center;
        margin: 6px 0;
        border-radius: 2px;
        transition: background-color 0.2s ease;
    }
    .code-editor-box {
        font-family: 'Fira Code', 'Courier New', monospace;
        font-size: 12px;
        background: #0f172a;
        color: #e2e8f0;
        border-radius: 8px;
        padding: 12px;
        line-height: 1.5;
        max-height: 320px;
        overflow-y: auto;
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
                    <li class="breadcrumb-item active fw-semibold text-success" aria-current="page">পাঠাগার ড্যাশবোর্ড ও বই অনুদান</li>
                </ol>
            </nav>
            <h1 class="h3 fw-bold mb-0 text-gray-900 d-flex align-items-center gap-2">
                <i class="fa-solid fa-book-open-reader text-success"></i> পাঠাগার ড্যাশবোর্ড ও বই অনুদান ব্যবস্থাপনা
            </h1>
            <p class="text-muted small mb-0 mt-1">কেন্দ্রীয় পাঠাগার ডাটাবেজ, ফরম ও প্রিন্ট সেটিংস কাস্টমাইজেশন, কোড জেনারেটর এবং বই বিতরণ ট্র্যাকিং।</p>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            {{-- 1. Customizer & Code Generator Modal Trigger --}}
            <button type="button" class="btn btn-primary rounded-pill px-3.5 py-2 fw-bold shadow-sm d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#formCustomizerModal">
                <i class="fa-solid fa-palette"></i> ফরম ও কোড কাস্টমাইজার <span class="badge bg-white text-primary rounded-pill px-2 py-0.5 small">CSS/JS/PHP</span>
            </button>

            {{-- 2. CSV Export --}}
            <a href="{{ route('admin.libraries.export') }}" class="btn btn-outline-secondary rounded-pill px-3 py-2 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-2xs">
                <i class="fa-solid fa-file-csv text-success"></i> CSV Export
            </a>

            {{-- 3. Public Apply Form Link --}}
            <a href="{{ url('/pathagar') }}" target="_blank" class="btn btn-success rounded-pill px-3.5 py-2 fw-semibold shadow-sm d-inline-flex align-items-center gap-2">
                <i class="fa-solid fa-plus"></i> আবেদন ফরম দেখুন <i class="fa-solid fa-arrow-up-right-from-square small opacity-75"></i>
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

    {{-- 6 HIGH-IMPACT KPI STATS CARDS --}}
    <div class="row g-3 mb-4">
        {{-- 1. Total Libraries --}}
        <div class="col-6 col-md-4 col-xl-2">
            <div class="kpi-card-custom">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold text-uppercase" style="font-size: 11px;">মোট পাঠাগার</span>
                    <div class="kpi-icon-box bg-primary-subtle text-primary">
                        <i class="fa-solid fa-landmark"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-0 text-dark">{{ number_format($totalLibraries) }}</h3>
                <small class="text-muted d-block mt-1" style="font-size: 11px;">নিবন্ধিত প্রতিষ্ঠান</small>
            </div>
        </div>

        {{-- 2. Approved Libraries --}}
        <div class="col-6 col-md-4 col-xl-2">
            <div class="kpi-card-custom">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold text-uppercase" style="font-size: 11px;">অনুমোদিত</span>
                    <div class="kpi-icon-box bg-success-subtle text-success">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-0 text-success">{{ number_format($approvedLibraries) }}</h3>
                <small class="text-success d-block mt-1" style="font-size: 11px;">অনুদানের জন্য নির্বাচিত</small>
            </div>
        </div>

        {{-- 3. Total Books Allocated --}}
        <div class="col-6 col-md-4 col-xl-2">
            <div class="kpi-card-custom">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold text-uppercase" style="font-size: 11px;">মোট বই বরাদ্দ</span>
                    <div class="kpi-icon-box bg-info-subtle text-info">
                        <i class="fa-solid fa-boxes-stacked"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-0 text-dark">{{ number_format($totalBooksAllocated) }} <span class="fs-6 fw-normal text-muted">টি</span></h3>
                <small class="text-muted d-block mt-1" style="font-size: 11px;">প্রেরিত / বরাদ্দকৃত বই</small>
            </div>
        </div>

        {{-- 4. Acknowledged / Received --}}
        <div class="col-6 col-md-4 col-xl-2">
            <div class="kpi-card-custom">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold text-uppercase" style="font-size: 11px;">প্রাপ্তিস্বীকার</span>
                    <div class="kpi-icon-box bg-emerald-subtle text-success" style="background-color: #ecfdf5; color: #047857;">
                        <i class="fa-solid fa-signature"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-0 text-success">{{ number_format($acknowledgedCount) }} <span class="fs-6 fw-normal text-muted">পাঠাগার</span></h3>
                <small class="text-muted d-block mt-1" style="font-size: 11px;">{{ number_format($totalBooksReceived) }} টি বই পৌঁছানো সম্পন্ন</small>
            </div>
        </div>

        {{-- 5. Pending Review --}}
        <div class="col-6 col-md-4 col-xl-2">
            <div class="kpi-card-custom">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold text-uppercase" style="font-size: 11px;">যাচাই অপেক্ষায়</span>
                    <div class="kpi-icon-box bg-warning-subtle text-warning">
                        <i class="fa-solid fa-hourglass-half"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-0 text-warning">{{ number_format($pendingReview) }}</h3>
                <small class="text-muted d-block mt-1" style="font-size: 11px;">পেন্ডিং আবেদন</small>
            </div>
        </div>

        {{-- 6. Readers & District Coverage --}}
        <div class="col-6 col-md-4 col-xl-2">
            <div class="kpi-card-custom">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold text-uppercase" style="font-size: 11px;">পাঠক ও জেলা</span>
                    <div class="kpi-icon-box bg-purple-subtle text-purple" style="background: #f3e8ff; color: #7e22ce;">
                        <i class="fa-solid fa-users"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-0 text-dark">{{ number_format($totalReadersCount) }} <span class="fs-6 fw-normal text-muted">পাঠক</span></h3>
                <small class="text-muted d-block mt-1" style="font-size: 11px;">{{ $uniqueDistricts }} টি জেলায় বিস্তৃত</small>
            </div>
        </div>
    </div>

    {{-- DIVISION ANALYTICS & QUICK FILTER PILLS --}}
    <div class="card border-0 shadow-2xs rounded-4 mb-4 bg-white p-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
            <span class="fw-bold text-dark small d-flex align-items-center gap-1.5">
                <i class="fa-solid fa-chart-pie text-success"></i> বিভাগভিত্তিক আবেদন বিন্যাস (Division Distribution):
            </span>
            <span class="text-muted small">মোট বিদ্যমান বই ভাণ্ডার: <strong>{{ number_format($totalExistingBooks) }}</strong> টি</span>
        </div>
        <div class="d-flex flex-wrap gap-2">
            @foreach($divisionStats as $divName => $divCount)
                <a href="{{ route('admin.libraries.index', array_merge(request()->query(), ['division' => $divName])) }}" 
                   class="div-stat-pill text-decoration-none {{ request('division') == $divName ? 'bg-success text-white border-success' : '' }}">
                    <span>{{ $divName }}</span>
                    <span class="badge {{ request('division') == $divName ? 'bg-white text-success' : 'bg-light text-dark' }} rounded-pill">{{ $divCount }}</span>
                </a>
            @endforeach
        </div>
    </div>

    {{-- FILTER BAR --}}
    <div class="card border-0 shadow-2xs rounded-4 mb-4 bg-white">
        <div class="card-body p-3">
            <form action="{{ route('admin.libraries.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-12 col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                        <input type="text" name="search" class="form-control bg-light border-start-0 ps-0" placeholder="Search by library name, phone, rep, or Reg ID..." value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-6 col-md-2">
                    <select name="division" class="form-select bg-light">
                        <option value="">All Divisions</option>
                        @foreach(['Rangpur' => 'রংপুর', 'Dhaka' => 'ঢাকা', 'Chattogram' => 'চট্টগ্রাম', 'Rajshahi' => 'রাজশাহী', 'Khulna' => 'খুলনা', 'Barishal' => 'বরিশাল', 'Sylhet' => 'সিলেট', 'Mymensingh' => 'ময়মনসিংহ'] as $engDiv => $bngDiv)
                            <option value="{{ $bngDiv }}" {{ request('division') == $bngDiv ? 'selected' : '' }}>{{ $engDiv }} ({{ $bngDiv }})</option>
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
                                            onclick='openAckModal("{{ $lib->id }}", "{{ addslashes($libName) }}", "{{ $allocated }}", "{{ $receivedCount ?: $allocated }}", "{{ $receivedDate }}", "{{ addslashes($fd['acknowledgment_notes'] ?? '') }}")'>
                                        <i class="fa-solid fa-signature"></i>
                                    </button>

                                    {{-- 3. Print / Preview Application Form (PDF View) --}}
                                    <a href="{{ route('admin.libraries.print', $lib->id) }}" target="_blank" class="btn btn-sm btn-outline-success btn-action-icon" title="Preview Official Form / Print">
                                        <i class="fa-solid fa-file-invoice"></i>
                                    </a>

                                    {{-- 4. Direct Download PDF --}}
                                    <a href="{{ route('admin.libraries.pdf', $lib->id) }}" class="btn btn-sm btn-outline-danger btn-action-icon" title="Download Official PDF">
                                        <i class="fa-solid fa-file-pdf"></i>
                                    </a>

                                    {{-- 5. Details Modal --}}
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

{{-- ═══════════════════════════════════════════════════════════════════════════
     MODAL: Form & Print Slip Customizer + CSS / JS / PHP Code Generator
═══════════════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="formCustomizerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content rounded-4 border-0 shadow">
            <form id="formCustomizerForm" method="POST" action="{{ route('admin.libraries.settings') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-header border-bottom p-3.5 bg-success text-white rounded-top-4 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fa-solid fa-palette fs-5"></i>
                        <div>
                            <h5 class="modal-title fw-bold fs-6 mb-0">পাঠাগার ফরম ও প্রিন্ট কাস্টমাইজার (Form Settings & Code Generator)</h5>
                            <small class="opacity-75">লোগো, শিরোনাম, স্বাক্ষর ও কোড জেনারেট করে ফরম কাস্টমাইজ করুন</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4 bg-light">
                    <div class="row g-4">
                        {{-- LEFT COLUMN: Settings Controls & Code Tabs --}}
                        <div class="col-lg-7">
                            <div class="card border-0 shadow-xs rounded-3 bg-white p-3 mb-3">
                                <ul class="nav nav-pills nav-fill mb-3 gap-1 bg-light p-1 rounded-pill" id="customizerTabs" role="tablist">
                                    <li class="nav-item">
                                        <button class="nav-link active rounded-pill fw-semibold py-1.5 small" id="tab-brand-btn" data-bs-toggle="pill" data-bs-target="#tab-brand" type="button">
                                            <i class="fa-solid fa-image me-1"></i> ১. লোগো ও ব্র্যান্ডিং
                                        </button>
                                    </li>
                                    <li class="nav-item">
                                        <button class="nav-link rounded-pill fw-semibold py-1.5 small" id="tab-signature-btn" data-bs-toggle="pill" data-bs-target="#tab-signature" type="button">
                                            <i class="fa-solid fa-file-signature me-1"></i> ২. শিরোনাম ও স্বাক্ষর
                                        </button>
                                    </li>
                                    <li class="nav-item">
                                        <button class="nav-link rounded-pill fw-semibold py-1.5 small" id="tab-code-btn" data-bs-toggle="pill" data-bs-target="#tab-code" type="button">
                                            <i class="fa-solid fa-code me-1"></i> ৩. CSS / JS / PHP কোড
                                        </button>
                                    </li>
                                </ul>

                                <div class="tab-content">
                                    {{-- TAB 1: Logo & Branding --}}
                                    <div class="tab-pane fade show active" id="tab-brand">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label small fw-bold text-dark mb-1">লোগো ইমেজ ফাইল (Upload New)</label>
                                                <input type="file" name="logo_file" id="custLogoFile" class="form-control form-control-sm" accept="image/*" onchange="previewUploadedLogo(this)">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label small fw-bold text-dark mb-1">অথবা লোগো URL</label>
                                                <input type="text" name="logo_url" id="custLogoUrl" class="form-control form-control-sm" value="{{ $formSettings['logo_url'] ?? asset('images/logo.png') }}" placeholder="/images/logo.png" oninput="updateLivePreview()">
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label small fw-bold text-dark mb-1">লোগো সাইজ (W 2 : H 1 অনুপাত): <span id="logoSizeVal" class="text-success fw-bold">{{ ($formSettings['logo_size'] ?? 28) * 2 }}x{{ $formSettings['logo_size'] ?? 28 }}px</span></label>
                                                <input type="range" name="logo_size" id="custLogoSize" class="form-range" min="16" max="64" value="{{ $formSettings['logo_size'] ?? 28 }}" oninput="document.getElementById('logoSizeVal').innerText = (this.value * 2) + 'x' + this.value + 'px'; updateLivePreview();">
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label small fw-bold text-dark mb-1">থিম কালার (Primary Brand Color)</label>
                                                <div class="d-flex align-items-center gap-2">
                                                    <input type="color" name="theme_color" id="custThemeColor" class="form-control form-control-color border-0 p-0" style="width: 42px; height: 32px;" value="{{ $formSettings['theme_color'] ?? '#047857' }}" oninput="updateLivePreview()">
                                                    <input type="text" id="custThemeColorHex" class="form-control form-control-sm font-monospace text-uppercase" value="{{ $formSettings['theme_color'] ?? '#047857' }}" oninput="document.getElementById('custThemeColor').value = this.value; updateLivePreview();">
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <label class="form-label small fw-bold text-dark mb-1">পাঠাগার / ব্র্যান্ড নাম (Header Title)</label>
                                                <input type="text" name="brand_name" id="custBrandName" class="form-control form-control-sm" value="{{ $formSettings['brand_name'] ?? 'আইডিয়া পাঠাগার' }}" oninput="updateLivePreview()">
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label small fw-bold text-dark mb-1">সাব-টাইটেল (Sub Title)</label>
                                                <input type="text" name="sub_title" id="custSubTitle" class="form-control form-control-sm" value="{{ $formSettings['sub_title'] ?? 'বই অনুদান আবেদন ফরম' }}" oninput="updateLivePreview()">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label small fw-bold text-dark mb-1">প্রকাশন লাইন (Session/Org Text)</label>
                                                <input type="text" name="session_text" id="custSessionText" class="form-control form-control-sm" value="{{ $formSettings['session_text'] ?? 'আইডিয়া প্রকাশন ও বুকস অব আইডিয়া' }}" oninput="updateLivePreview()">
                                            </div>

                                            <div class="col-12">
                                                <label class="form-label small fw-bold text-dark mb-1">প্রধান কার্যালয় ও ওয়েবসাইট (Brand Tag)</label>
                                                <textarea name="brand_tag" id="custBrandTag" rows="2" class="form-control form-control-sm" oninput="updateLivePreview()">{{ $formSettings['brand_tag'] ?? "প্রধান কার্যালয়: ঢাকা, বাংলাদেশ\nwww.ideaabd.com" }}</textarea>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- TAB 2: Titles & Signatures --}}
                                    <div class="tab-pane fade" id="tab-signature">
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <label class="form-label small fw-bold text-dark mb-1">ব্যানার শিরোনাম (Banner Headline)</label>
                                                <input type="text" name="banner_title" id="custBannerTitle" class="form-control form-control-sm" value="{{ $formSettings['banner_title'] ?? 'বিনামূল্যে বই বিতরণ কর্মসূচি ও পাঠাগার নিবন্ধন আবেদন ফরম' }}" oninput="updateLivePreview()">
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label small fw-bold text-dark mb-1">কর্মসূচি সেশন (Session Field)</label>
                                                <input type="text" name="grant_session" id="custGrantSession" class="form-control form-control-sm" value="{{ $formSettings['grant_session'] ?? '২০২৬ অনুদান কর্মসূচি' }}" oninput="updateLivePreview()">
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label small fw-bold text-dark mb-1">অনুমোদনকারী কর্মকর্তার নাম (Officer Name)</label>
                                                <input type="text" name="officer_name" id="custOfficerName" class="form-control form-control-sm fw-bold" value="{{ $formSettings['officer_name'] ?? 'সাকিল মাসুদ' }}" oninput="updateLivePreview()">
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label small fw-bold text-dark mb-1">কর্মকর্তার পদবি (Designation/Title)</label>
                                                <input type="text" name="officer_designation" id="custOfficerDesignation" class="form-control form-control-sm" value="{{ $formSettings['officer_designation'] ?? 'তত্বাবধায়ক ও প্রতিষ্ঠাতা' }}" oninput="updateLivePreview()">
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label small fw-bold text-dark mb-1">কর্মকর্তার প্রতিষ্ঠান / সাব-লাইন (Optional)</label>
                                                <input type="text" name="officer_org" id="custOfficerOrg" class="form-control form-control-sm" value="{{ $formSettings['officer_org'] ?? 'আইডিয়া পাঠাগার ও প্রকাশন' }}" oninput="updateLivePreview()">
                                            </div>

                                            <div class="col-12">
                                                <label class="form-label small fw-bold text-dark mb-1">নীতিমালা ও ঘোষণা টেক্সট (Declaration Statement)</label>
                                                <textarea name="declaration_text" id="custDeclaration" rows="2" class="form-control form-control-sm" oninput="updateLivePreview()">{{ $formSettings['declaration_text'] ?? 'আইডিয়া পাঠাগার নিজ উদ্যোগে বই বিতরণ করে। বই প্রদানের ক্ষেত্রে যে কোনো সিদ্ধান্ত গ্রহণের ক্ষমতা সংরক্ষণ করে।' }}</textarea>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- TAB 3: Generated Code (CSS, JS, PHP) --}}
                                    <div class="tab-pane fade" id="tab-code">
                                        <div class="mb-3">
                                            <div class="d-flex align-items-center justify-content-between mb-1.5">
                                                <label class="form-label small fw-bold text-dark mb-0"><i class="fa-brands fa-css3-alt text-primary me-1"></i> Generated Custom CSS (কাস্টম সিএসএস কোড)</label>
                                                <button type="button" class="btn btn-xs btn-outline-primary rounded-pill px-2 py-0.5 fw-semibold" onclick="copyCode('generatedCssCode')">
                                                    <i class="fa-regular fa-copy me-1"></i> Copy CSS
                                                </button>
                                            </div>
                                            <textarea name="custom_css" id="generatedCssCode" rows="4" class="code-editor-box w-100">{{ $formSettings['custom_css'] ?? '' }}</textarea>
                                        </div>

                                        <div class="mb-3">
                                            <div class="d-flex align-items-center justify-content-between mb-1.5">
                                                <label class="form-label small fw-bold text-dark mb-0"><i class="fa-brands fa-js text-warning me-1"></i> Generated Custom JS (জাভাস্ক্রিপ্ট কোড)</label>
                                                <button type="button" class="btn btn-xs btn-outline-warning text-dark rounded-pill px-2 py-0.5 fw-semibold" onclick="copyCode('generatedJsCode')">
                                                    <i class="fa-regular fa-copy me-1"></i> Copy JS
                                                </button>
                                            </div>
                                            <textarea name="custom_js" id="generatedJsCode" rows="4" class="code-editor-box w-100">{{ $formSettings['custom_js'] ?? '' }}</textarea>
                                        </div>

                                        <div>
                                            <div class="d-flex align-items-center justify-content-between mb-1.5">
                                                <label class="form-label small fw-bold text-dark mb-0"><i class="fa-brands fa-php text-info me-1"></i> Generated PHP Array Config (পিএইচপি কনফিগারেশন)</label>
                                                <button type="button" class="btn btn-xs btn-outline-info rounded-pill px-2 py-0.5 fw-semibold" onclick="copyCode('generatedPhpCode')">
                                                    <i class="fa-regular fa-copy me-1"></i> Copy PHP
                                                </button>
                                            </div>
                                            <pre id="generatedPhpCode" class="code-editor-box mb-0"></pre>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- RIGHT COLUMN: Real-Time Live Preview Mockup --}}
                        <div class="col-lg-5">
                            <div class="card border-0 shadow-xs rounded-3 bg-white p-3 h-100">
                                <div class="d-flex align-items-center justify-content-between mb-2.5 pb-2 border-bottom">
                                    <span class="fw-bold text-dark small d-flex align-items-center gap-1.5">
                                        <i class="fa-solid fa-eye text-success"></i> লাইভ প্রিভিউ (Live Real-Time Mockup)
                                    </span>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-0.5" style="font-size: 10.5px;">Auto Sync</span>
                                </div>

                                <div class="live-preview-box" id="livePreviewContainer">
                                    {{-- RSU Style Letterhead Pad Mockup --}}
                                    <div class="d-flex align-items-center justify-content-between gap-2 pb-2 mb-2 border-bottom" style="border-bottom: 1.5px solid #000 !important;">
                                        <div style="width: 76px; height: 38px; aspect-ratio: 2 / 1; border-radius: 6px; border: 1.5px solid {{ $formSettings['theme_color'] ?? '#047857' }}; background: #fff; display: flex; align-items: center; justify-content: center; overflow: hidden; padding: 2px 4px; flex-shrink: 0;" id="prevLogoContainer">
                                            <img id="prevLogoImg" src="{{ $formSettings['logo_url'] ?? asset('images/logo.png') }}" alt="Logo" style="max-width: 100%; max-height: 100%; width: 100%; height: 100%; object-fit: contain;">
                                        </div>
                                        <div class="text-center flex-grow-1 px-1">
                                            <div id="prevBrandName" style="font-size: 14px; font-weight: 900; color: #0f172a; line-height: 1.2; font-family: 'Noto Serif Bengali', serif;">
                                                {{ $formSettings['brand_name'] ?? 'আইডিয়া পাঠাগার' }}
                                            </div>
                                            <div id="prevSubTitle" style="font-size: 10px; font-weight: 800; color: {{ $formSettings['theme_color'] ?? '#047857' }};">
                                                {{ $formSettings['sub_title'] ?? 'বই অনুদান আবেদন ফরম' }}
                                            </div>
                                            <div id="prevSessionText" style="font-size: 9px; font-weight: 700; color: #334155;">
                                                {{ $formSettings['session_text'] ?? 'আইডিয়া প্রকাশন ও বুকস অব আইডিয়া' }}
                                            </div>
                                            <div id="prevBrandTag" style="font-size: 8px; color: #64748b; line-height: 1.2;">
                                                {{ $formSettings['brand_tag'] ?? "প্রধান কার্যালয়: রংপুর, বাংলাদেশ\nwww.ideaabd.com" }}
                                            </div>
                                        </div>
                                        <div class="text-end flex-shrink-0">
                                            <span class="badge bg-dark text-white px-2 py-0.5 font-monospace text-uppercase" id="prevOfficialHeader" style="font-size: 8px;">
                                                OFFICIAL COPY
                                            </span>
                                            <div class="mt-0.5 font-monospace text-dark fw-bold" style="font-size: 9px;">
                                                #PATH-260929
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Banner Strip --}}
                                    <div class="live-banner-strip" id="prevBannerTitle">
                                        {{ $formSettings['banner_title'] ?? 'বিনামূল্যে বই বিতরণ কর্মসূচি ও পাঠাগার নিবন্ধন আবেদন ফরম' }}
                                    </div>

                                    {{-- Sample Info Block --}}
                                    <div style="border: 1px solid #000; padding: 4px; font-size: 9.5px; margin-bottom: 6px; background: #fff;">
                                        <div><strong>পাঠাগারের নাম:</strong> <span style="font-weight: bold; color: {{ $formSettings['theme_color'] ?? '#047857' }};" id="prevSampleLib">সেতুবন্ধন পাঠাগার</span></div>
                                        <div><strong>কর্মসূচি সেশন:</strong> <span id="prevGrantSession">{{ $formSettings['grant_session'] ?? '২০২৬ অনুদান কর্মসূচি' }}</span></div>
                                    </div>

                                    {{-- Declaration --}}
                                    <div style="font-size: 8.5px; color: #334155; padding: 3px 5px; background: #f8fafc; border: 1px solid #cbd5e1; text-align: center; margin-bottom: 10px;" id="prevDeclaration">
                                        <strong>ঘোষণা:</strong> {{ $formSettings['declaration_text'] ?? 'আইডিয়া পাঠাগার নিজ উদ্যোগে বই বিতরণ করে। বই প্রদানের ক্ষেত্রে যে কোনো সিদ্ধান্ত গ্রহণের ক্ষমতা সংরক্ষণ করে।' }}
                                    </div>

                                    {{-- Signatures Table --}}
                                    <table style="width: 100%; border-collapse: collapse; font-size: 9px; margin-top: 6px;">
                                        <tr>
                                            <td style="width: 50%; text-align: center; vertical-align: bottom; padding: 0 5px;">
                                                <div style="border-top: 1px dashed #000; padding-top: 2px;">
                                                    <strong>আবেদনকারী প্রতিনিধির স্বাক্ষর</strong><br>
                                                    <span style="font-size: 7.5px; color: #64748b;">পাঠাগার পরিচালনা কমিটি</span>
                                                </div>
                                            </td>
                                            <td style="width: 50%; text-align: center; vertical-align: bottom; padding: 0 5px;">
                                                <div style="border-top: 1px dashed #000; padding-top: 2px;">
                                                    <strong id="prevOfficerName">{{ $formSettings['officer_name'] ?? 'সাকিল মাসুদ' }}</strong><br>
                                                    <span style="font-size: 7.5px; color: #475569;" id="prevOfficerRole">{{ $formSettings['officer_designation'] ?? 'তত্বাবধায়ক ও প্রতিষ্ঠাতা' }} • {{ $formSettings['officer_org'] ?? 'আইডিয়া পাঠাগার ও প্রকাশন' }}</span>
                                                </div>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-top p-3 bg-white d-flex align-items-center justify-content-between">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">বাতিল</button>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-success rounded-pill px-3.5 fw-semibold" onclick="generateAllSnippets()">
                            <i class="fa-solid fa-wand-magic-sparkles me-1"></i> কোড রি-জেনারেট করুন
                        </button>
                        <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold shadow-sm" id="btnSaveCustomizer">
                            <i class="fa-solid fa-check me-1"></i> সেটিংস সংরক্ষণ করুন
                        </button>
                    </div>
                </div>
            </form>
        </div>
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

                    {{-- Dynamic Itemized Books Breakdown Table --}}
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
            <div class="modal-footer border-top p-3 bg-light d-flex align-items-center justify-content-between">
                <div class="d-flex gap-2" id="detailsModalActions">
                    {{-- Dynamically linked by JS --}}
                </div>
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
                    <h6 class="fw-bold text-primary mb-2"><i class="fa-solid fa-user-shield me-1"></i> Representative & Leadership</h6>
                    <div class="small mb-1"><strong>Representative:</strong> ${lib.name || '—'} (${lib.designation_or_class || fd.designation_or_class || '—'})</div>
                    <div class="small mb-1"><strong>Phone:</strong> <a href="tel:${lib.phone}" class="fw-semibold text-dark">${lib.phone || '—'}</a></div>
                    <div class="small mb-1"><strong>President (সভাপতি):</strong> ${fd.president_name || '—'} ${fd.president_phone ? '(<a href="tel:'+fd.president_phone+'">'+fd.president_phone+'</a>)' : ''}</div>
                    <div class="small mb-1"><strong>Secretary (সম্পাদক):</strong> ${fd.secretary_name || '—'} ${fd.secretary_phone ? '(<a href="tel:'+fd.secretary_phone+'">'+fd.secretary_phone+'</a>)' : ''}</div>
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
    
    // Set Action Buttons
    const printUrl = "{{ url('/admin/libraries') }}/" + lib.id + "/print";
    const pdfUrl = "{{ url('/admin/libraries') }}/" + lib.id + "/pdf";
    document.getElementById('detailsModalActions').innerHTML = `
        <a href="${printUrl}" target="_blank" class="btn btn-outline-success rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5">
            <i class="fa-solid fa-print"></i> Print Official Form (PDF View)
        </a>
        <a href="${pdfUrl}" class="btn btn-danger rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5">
            <i class="fa-solid fa-file-pdf"></i> Download PDF
        </a>
    `;

    new bootstrap.Modal(document.getElementById('detailsModal')).show();
}

/* ═══════════════════════════════════════════════════════════════════════════
   LIVE CUSTOMIZER & CODE GENERATOR FUNCTIONS (CSS / JS / PHP)
═══════════════════════════════════════════════════════════════════════════ */
function previewUploadedLogo(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('prevLogoImg').src = e.target.result;
            document.getElementById('custLogoUrl').value = '';
            generateAllSnippets();
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function updateLivePreview() {
    const brandName = document.getElementById('custBrandName').value || 'আইডিয়া পাঠাগার';
    const subTitle = document.getElementById('custSubTitle').value || 'বই অনুদান আবেদন ফরম';
    const sessionText = document.getElementById('custSessionText').value || 'আইডিয়া প্রকাশন ও বুকস অব আইডিয়া';
    const brandTag = document.getElementById('custBrandTag').value || '';
    const bannerTitle = document.getElementById('custBannerTitle').value || 'বিনামূল্যে বই বিতরণ কর্মসূচি ও পাঠাগার নিবন্ধন আবেদন ফরম';
    const grantSession = document.getElementById('custGrantSession').value || '২০২৬ অনুদান কর্মসূচি';
    const officerName = document.getElementById('custOfficerName').value || 'সাকিল মাসুদ';
    const officerDesignation = document.getElementById('custOfficerDesignation').value || 'তত্বাবধায়ক ও প্রতিষ্ঠাতা';
    const officerOrg = document.getElementById('custOfficerOrg').value || '';
    const declaration = document.getElementById('custDeclaration').value || '';
    const themeColor = document.getElementById('custThemeColor').value || '#047857';
    const logoSize = document.getElementById('custLogoSize').value || 28;
    const logoUrl = document.getElementById('custLogoUrl').value;

    if (logoUrl) {
        document.getElementById('prevLogoImg').src = logoUrl;
    }
    const logoH = parseInt(logoSize) || 28;
    const logoW = logoH * 2;
    const pImg = document.getElementById('prevLogoImg');
    if (pImg) {
        pImg.style.height = logoH + 'px';
        pImg.style.width = logoW + 'px';
    }
    const pCont = document.getElementById('prevLogoContainer');
    if (pCont) {
        pCont.style.height = (logoH + 8) + 'px';
        pCont.style.width = (logoW + 16) + 'px';
    }
    document.getElementById('prevBrandName').innerText = brandName;
    document.getElementById('prevBrandName').style.color = themeColor;
    document.getElementById('prevSubTitle').innerText = subTitle;
    document.getElementById('prevSessionText').innerText = sessionText;
    document.getElementById('prevBrandTag').innerText = brandTag;
    document.getElementById('prevBannerTitle').innerText = bannerTitle;
    document.getElementById('prevBannerTitle').style.background = themeColor;
    document.getElementById('prevOfficialHeader').style.background = themeColor;
    document.getElementById('prevGrantSession').innerText = grantSession;
    document.getElementById('prevDeclaration').innerHTML = '<strong>ঘোষণা:</strong> ' + declaration;
    document.getElementById('prevOfficerName').innerText = officerName;
    document.getElementById('prevOfficerRole').innerText = officerDesignation + (officerOrg ? ' • ' + officerOrg : '');
    document.getElementById('custThemeColorHex').value = themeColor.toUpperCase();

    generateAllSnippets();
}

function generateAllSnippets() {
    const brandName = document.getElementById('custBrandName').value || 'আইডিয়া পাঠাগার';
    const subTitle = document.getElementById('custSubTitle').value || 'বই অনুদান আবেদন ফরম';
    const sessionText = document.getElementById('custSessionText').value || 'আইডিয়া প্রকাশন ও বুকস অব আইডিয়া';
    const bannerTitle = document.getElementById('custBannerTitle').value || 'বিনামূল্যে বই বিতরণ কর্মসূচি ও পাঠাগার নিবন্ধন আবেদন ফরম';
    const officerName = document.getElementById('custOfficerName').value || 'সাকিল মাসুদ';
    const officerDesignation = document.getElementById('custOfficerDesignation').value || 'তত্বাবধায়ক ও প্রতিষ্ঠাতা';
    const officerOrg = document.getElementById('custOfficerOrg').value || '';
    const themeColor = document.getElementById('custThemeColor').value || '#047857';
    const logoSize = document.getElementById('custLogoSize').value || 24;
    const logoUrl = document.getElementById('custLogoUrl').value || '/images/logo.png';

    // 1. Generate CSS Snippet
    const cssCode = `/* ═════════════════════════════════════════════════════════
   IDEA Library & Book Grant Custom Stylesheet
   Theme Color: ${themeColor} | Generated: ${new Date().toISOString().slice(0,10)}
═════════════════════════════════════════════════════════ */
:root {
    --lib-primary: ${themeColor};
    --lib-logo-height: ${logoSize}px;
}
.header-box-left .inst-brand-row {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    margin-bottom: 2px;
}
.header-box-left .brand-logo-img {
    height: var(--lib-logo-height);
    max-width: 42px;
    object-fit: contain;
    vertical-align: middle;
}
.header-box-left .inst-title {
    color: var(--lib-primary);
    font-size: 15px;
    font-weight: 700;
}
.form-banner-strip {
    background-color: var(--lib-primary) !important;
    color: #ffffff !important;
    padding: 5px 10px;
    font-weight: 700;
    text-align: center;
}`;
    
    // Only update if user hasn't manually customized or on initial load
    const currentCssArea = document.getElementById('generatedCssCode');
    if (!currentCssArea.value || currentCssArea.value.includes('IDEA Library & Book Grant Custom Stylesheet')) {
        currentCssArea.value = cssCode;
    }

    // 2. Generate JS Snippet
    const jsCode = `/**
 * IDEA Library Registration & Print Utility Handler
 * Configured Signatory: ${officerName} (${officerDesignation})
 */
document.addEventListener('DOMContentLoaded', function () {
    console.log("Library System Initialized for ${brandName}");
    
    // Dynamic copy calculations & verification hooks
    const bookInputs = document.querySelectorAll('.book-copies-input');
    if (bookInputs.length > 0) {
        bookInputs.forEach(input => {
            input.addEventListener('input', function() {
                let sum = 0;
                bookInputs.forEach(i => sum += (parseInt(i.value) || 0));
                const badge = document.getElementById('calcTotalBadge');
                if (badge) badge.textContent = sum + ' Books';
            });
        });
    }
});`;
    
    const currentJsArea = document.getElementById('generatedJsCode');
    if (!currentJsArea.value || currentJsArea.value.includes('IDEA Library Registration & Print Utility Handler')) {
        currentJsArea.value = jsCode;
    }

    // 3. Generate PHP Array Configuration
    const phpCode = '<\x3Fphp\n' +
'// config/library_grant.php or EventCampaign $form_settings\n' +
'return [\n' +
"    'brand_name'          => '" + brandName.replace(/'/g, "\\'") + "',\n" +
"    'sub_title'           => '" + subTitle.replace(/'/g, "\\'") + "',\n" +
"    'session_text'        => '" + sessionText.replace(/'/g, "\\'") + "',\n" +
"    'banner_title'        => '" + bannerTitle.replace(/'/g, "\\'") + "',\n" +
"    'officer_name'        => '" + officerName.replace(/'/g, "\\'") + "',\n" +
"    'officer_designation' => '" + officerDesignation.replace(/'/g, "\\'") + "',\n" +
"    'officer_org'         => '" + officerOrg.replace(/'/g, "\\'") + "',\n" +
"    'logo_url'            => '" + logoUrl + "',\n" +
"    'logo_size'           => " + logoSize + ",\n" +
"    'theme_color'         => '" + themeColor + "',\n" +
"    'requires_approval'   => true,\n" +
"    'is_library_form'     => true,\n" +
'];';
    document.getElementById('generatedPhpCode').textContent = phpCode;
}

function copyCode(elementId) {
    const el = document.getElementById(elementId);
    const text = el.value || el.textContent;
    navigator.clipboard.writeText(text).then(() => {
        alert('কোড সফলভাবে ক্লিপবোর্ডে কপি করা হয়েছে!');
    }).catch(() => {
        alert('কপি করতে ব্যর্থ হয়েছে। অনুগ্রহ করে নিজে সিলেক্ট করে কপি করুন।');
    });
}

// Initialize live preview on modal show
document.getElementById('formCustomizerModal').addEventListener('shown.bs.modal', function () {
    updateLivePreview();
});

// Handle customizer AJAX save form submit
document.getElementById('formCustomizerForm').addEventListener('submit', function (e) {
    e.preventDefault();
    const btn = document.getElementById('btnSaveCustomizer');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> সেভ হচ্ছে...';

    const formData = new FormData(this);

    fetch(this.action, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = originalText;
        if (data.success) {
            alert('✔ ' + data.message);
            location.reload();
        } else {
            alert('সেভ করতে সমস্যা হয়েছে: ' + (data.message || 'Error occurred'));
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = originalText;
        console.error(err);
        // Fallback standard submit
        document.getElementById('formCustomizerForm').submit();
    });
});
</script>
@endpush
@endsection
