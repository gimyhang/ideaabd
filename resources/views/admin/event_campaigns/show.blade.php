@extends('layouts.admin')

@section('title', $campaign->title . ' — Participants')

@section('content')
@php
    $tableSettings = $campaign->table_settings ?? [];
    $visibleCols = $tableSettings['columns'] ?? ['reg_no', 'participant', 'contact', 'location', 'fee', 'payment', 'status', 'date', 'actions'];
    $density = $tableSettings['density'] ?? 'standard';
    $customFields = $campaign->custom_fields ?? [];
@endphp

<div class="container-fluid px-3 px-md-4 py-4">

    {{-- Breadcrumb & Actions --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.event-campaigns.index') }}" class="text-decoration-none text-muted">Campaigns</a></li>
                    <li class="breadcrumb-item active text-primary" aria-current="page" id="breadcrumbTitle">{{ $campaign->title }}</li>
                </ol>
            </nav>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <h1 class="h3 fw-bold mb-0 text-gray-900 d-flex align-items-center gap-2" id="headerTitle">
                    {{ $campaign->title }}
                </h1>
                <span id="headerBadge">
                    @if($campaign->badge_text)
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle fs-6">{{ $campaign->badge_text }}</span>
                    @endif
                </span>
                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-2.5 py-0.5" data-bs-toggle="modal" data-bs-target="#editTitleModal" title="Change Title">
                    <i class="fa-solid fa-pen me-1"></i> Edit Title
                </button>
            </div>
            <div class="d-flex align-items-center gap-2 mt-1">
                <span class="text-muted small">URL:</span>
                <code class="text-primary font-monospace small" id="publicUrlText">{{ $campaign->public_url }}</code>
                <button type="button" class="btn btn-sm btn-light border rounded-pill px-2 py-0.5 text-muted copy-slug-btn" data-url="{{ $campaign->public_url }}" title="Copy URL">
                    <i class="fa-regular fa-copy"></i>
                </button>
                <a href="{{ $campaign->public_url }}" target="_blank" class="btn btn-sm btn-link text-decoration-none p-0 text-muted" title="Open Link" id="publicUrlLink">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                </a>
            </div>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2">
            <button type="button" class="btn btn-outline-dark rounded-pill px-3 py-1.5 small fw-semibold" data-bs-toggle="modal" data-bs-target="#tableSettingsModal">
                <i class="fa-solid fa-sliders me-1"></i> Customize Table
            </button>
            <a href="{{ route('admin.event-campaigns.export', $campaign->id) }}" class="btn btn-outline-success rounded-pill px-3 py-1.5 small fw-semibold">
                <i class="fa-solid fa-file-excel me-1"></i> Export
            </a>
            <a href="{{ route('admin.event-campaigns.edit', $campaign->id) }}" class="btn btn-outline-primary rounded-pill px-3 py-1.5 small fw-semibold">
                <i class="fa-solid fa-pen-to-square me-1"></i> Edit
            </a>
            <form action="{{ route('admin.event-campaigns.clone', $campaign->id) }}" method="POST" class="d-inline m-0">
                @csrf
                <button type="submit" class="btn btn-outline-info rounded-pill px-3 py-1.5 small fw-semibold">
                    <i class="fa-solid fa-copy me-1"></i> Clone
                </button>
            </form>
            <a href="{{ route('admin.event-campaigns.index') }}" class="btn btn-outline-secondary rounded-pill px-3 py-1.5 small">
                <i class="fa-solid fa-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 p-3 mb-4" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="fa-solid fa-circle-check fs-5 text-success"></i>
                <div class="fw-medium">{{ session('success') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Stats Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-xs rounded-4 p-3 bg-white h-100">
                <span class="text-muted small fw-semibold text-uppercase">Registrations</span>
                <h3 class="fw-bold mb-0 mt-1 text-dark">{{ number_format($totalRegistrations) }}</h3>
                <small class="text-muted">Total Participants</small>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-xs rounded-4 p-3 bg-white h-100">
                <span class="text-muted small fw-semibold text-uppercase">Collected</span>
                <h3 class="fw-bold mb-0 mt-1 text-success">
                    @if($campaign->has_fee_or_donation)
                        ৳{{ number_format($totalCollected, 2) }}
                    @else
                        Free
                    @endif
                </h3>
                <small class="text-muted">{{ $campaign->has_fee_or_donation ? 'Verified Fees' : 'Free Entry' }}</small>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-xs rounded-4 p-3 bg-white h-100">
                <span class="text-muted small fw-semibold text-uppercase">Status</span>
                <h3 class="fw-bold mb-0 mt-1">
                    @if($campaign->canAcceptRegistrations())
                        <span class="text-success fs-5">Active</span>
                    @else
                        <span class="text-danger fs-5">Closed</span>
                    @endif
                </h3>
                <small class="text-muted">{{ $campaign->ends_at ? 'Ends ' . $campaign->ends_at->format('d M, Y') : 'Open' }}</small>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-xs rounded-4 p-3 bg-white h-100">
                <span class="text-muted small fw-semibold text-uppercase">Capacity</span>
                <h3 class="fw-bold mb-0 mt-1 text-info fs-5">
                    {{ $campaign->max_participants ? number_format($campaign->max_participants) . ' Max' : 'Unlimited' }}
                </h3>
                <small class="text-muted">Users Auto-Synced</small>
            </div>
        </div>
    </div>

    {{-- Filter & Search Form with Per-Page Selector --}}
    <div class="card border-0 shadow-xs rounded-4 mb-4 bg-white">
        <div class="card-body p-3">
            <form action="{{ route('admin.event-campaigns.show', $campaign->id) }}" method="GET" class="row g-2 align-items-center" id="filterForm">
                <div class="col-12 col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                        <input type="text" name="search" class="form-control bg-light border-start-0 ps-0" placeholder="Search name, phone, email, reg no..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-6 col-md-2">
                    <select name="status" class="form-select bg-light">
                        <option value="">All Status</option>
                        <option value="selected" {{ request('status') == 'selected' ? 'selected' : '' }}>★ Selected (Scholarship)</option>
                        <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="attended" {{ request('status') == 'attended' ? 'selected' : '' }}>Attended</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <select name="payment_status" class="form-select bg-light">
                        <option value="">All Payments</option>
                        <option value="verified" {{ request('payment_status') == 'verified' ? 'selected' : '' }}>Verified</option>
                        <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="free" {{ request('payment_status') == 'free' ? 'selected' : '' }}>Free</option>
                        <option value="unpaid" {{ request('payment_status') == 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <select name="per_page" class="form-select bg-light" onchange="document.getElementById('filterForm').submit()">
                        <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10 / page</option>
                        <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25 / page</option>
                        <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50 / page</option>
                        <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100 / page</option>
                        <option value="250" {{ $perPage == 250 ? 'selected' : '' }}>250 / page</option>
                    </select>
                </div>
                <div class="col-6 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary rounded-pill px-3 fw-semibold flex-grow-1">Filter</button>
                    @if(request()->hasAny(['search', 'status', 'payment_status', 'per_page']))
                        <a href="{{ route('admin.event-campaigns.show', $campaign->id) }}" class="btn btn-light rounded-pill px-3 border" title="Reset"><i class="fa-solid fa-rotate-left"></i></a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Registrations Table --}}
    <div class="card border-0 shadow-xs rounded-4 bg-white overflow-hidden" id="participantsTableCard">
        <div class="card-header bg-white py-3 px-3 d-flex align-items-center justify-content-between border-bottom">
            <div class="d-flex align-items-center gap-2">
                <h6 class="fw-bold mb-0 text-dark">Participants ({{ $registrations->total() }})</h6>
                <span class="badge bg-light text-muted border font-monospace">{{ $perPage }} per page</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#cardDesignModal">
                    <i class="fa-solid fa-palette me-1"></i> Card Background & Design
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#tableSettingsModal">
                    <i class="fa-solid fa-table-columns me-1"></i> Columns & Rows
                </button>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 {{ $density === 'compact' ? 'table-sm' : '' }}" style="font-size: 13.5px;" id="participantsTable">
                <thead class="table-light text-secondary text-uppercase" style="font-size: 11.5px; letter-spacing: 0.5px;">
                    <tr>
                        <th class="ps-3 py-3 col-reg_no {{ in_array('reg_no', $visibleCols) ? '' : 'd-none' }}">Reg #</th>
                        <th class="py-3 col-participant {{ in_array('participant', $visibleCols) ? '' : 'd-none' }}">Participant</th>
                        <th class="py-3 col-contact {{ in_array('contact', $visibleCols) ? '' : 'd-none' }}">Contact</th>
                        <th class="py-3 col-location {{ in_array('location', $visibleCols) ? '' : 'd-none' }}">Location</th>
                        <th class="py-3 col-institution {{ in_array('institution', $visibleCols) ? '' : 'd-none' }}">Institution / Org</th>
                        
                        {{-- Custom Dynamic Columns --}}
                        @foreach($customFields as $cf)
                            @php
                                $cfKey = 'custom_' . ($cf['name'] ?? $loop->iteration);
                            @endphp
                            <th class="py-3 col-{{ $cfKey }} {{ in_array($cfKey, $visibleCols) ? '' : 'd-none' }}">
                                {{ $cf['label'] ?? ucfirst($cf['name'] ?? 'Custom') }}
                            </th>
                        @endforeach

                        <th class="py-3 col-fee {{ in_array('fee', $visibleCols) ? '' : 'd-none' }}">Payment</th>
                        <th class="py-3 col-payment {{ in_array('payment', $visibleCols) ? '' : 'd-none' }}">Pay Status</th>
                        <th class="py-3 col-status {{ in_array('status', $visibleCols) ? '' : 'd-none' }}">Status</th>
                        <th class="py-3 col-date {{ in_array('date', $visibleCols) ? '' : 'd-none' }}">Date</th>
                        <th class="pe-3 py-3 text-end col-actions {{ in_array('actions', $visibleCols) ? '' : 'd-none' }}">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($registrations as $reg)
                        <tr>
                            <td class="ps-3 col-reg_no {{ in_array('reg_no', $visibleCols) ? '' : 'd-none' }}">
                                <span class="badge bg-light text-primary border font-monospace px-2 py-1">
                                    {{ $reg->registration_number }}
                                </span>
                            </td>
                            <td class="col-participant {{ in_array('participant', $visibleCols) ? '' : 'd-none' }}">
                                <div class="fw-bold text-dark">{{ $reg->name }}</div>
                                @if($reg->institution_or_org)
                                    <small class="text-muted d-block">{{ $reg->institution_or_org }}</small>
                                @endif
                                @if($reg->user)
                                    <a href="{{ route('admin.users') }}?search={{ $reg->phone }}" target="_blank" class="badge bg-secondary-subtle text-secondary text-decoration-none" style="font-size: 10px;">Customer #{{ $reg->user_id }}</a>
                                @endif
                            </td>
                            <td class="col-contact {{ in_array('contact', $visibleCols) ? '' : 'd-none' }}">
                                <div><a href="tel:{{ $reg->phone }}" class="text-decoration-none text-dark font-monospace">{{ $reg->phone }}</a></div>
                                @if($reg->email)
                                    <div class="text-muted small">{{ $reg->email }}</div>
                                @endif
                            </td>
                            <td class="col-location {{ in_array('location', $visibleCols) ? '' : 'd-none' }}">
                                @if($reg->district || $reg->thana)
                                    <div>{{ implode(', ', array_filter([$reg->thana, $reg->district])) }}</div>
                                    @if($reg->address)
                                        <small class="text-muted d-block text-truncate" style="max-width: 150px;">{{ $reg->address }}</small>
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="col-institution {{ in_array('institution', $visibleCols) ? '' : 'd-none' }}">
                                {{ $reg->institution_or_org ?: '-' }}
                            </td>

                            {{-- Custom dynamic fields column values --}}
                            @foreach($customFields as $cf)
                                @php
                                    $cfKey = 'custom_' . ($cf['name'] ?? $loop->iteration);
                                    $cVal = $reg->form_data[$cf['name']] ?? null;
                                @endphp
                                <td class="col-{{ $cfKey }} {{ in_array($cfKey, $visibleCols) ? '' : 'd-none' }}">
                                    @if($cVal !== null)
                                        {{ is_array($cVal) ? implode(', ', $cVal) : $cVal }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            @endforeach

                            <td class="col-fee {{ in_array('fee', $visibleCols) ? '' : 'd-none' }}">
                                @if($campaign->has_fee_or_donation)
                                    <div class="fw-bold text-dark">৳{{ number_format($reg->amount_paid) }}</div>
                                    @if($reg->transaction_id)
                                        <small class="text-muted font-monospace d-block">Trx: {{ $reg->transaction_id }}</small>
                                    @endif
                                @else
                                    <span class="badge bg-light text-muted">Free</span>
                                @endif
                            </td>
                            <td class="col-payment {{ in_array('payment', $visibleCols) ? '' : 'd-none' }}">
                                @if($campaign->has_fee_or_donation)
                                    <span class="badge {{ $reg->payment_status === 'verified' ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }}" style="font-size: 10px;">
                                        {{ ucfirst($reg->payment_status) }}
                                    </span>
                                @else
                                    <span class="badge bg-light text-muted">-</span>
                                @endif
                            </td>
                            <td class="col-status {{ in_array('status', $visibleCols) ? '' : 'd-none' }}">
                                @if($reg->status === 'selected' || !empty($reg->form_data['is_scholarship_awarded']))
                                    <span class="badge bg-success text-white shadow-xs">
                                        <i class="fa-solid fa-award me-1"></i> Selected
                                    </span>
                                @else
                                    <span class="badge {{ $reg->status === 'confirmed' ? 'bg-success-subtle text-success' : ($reg->status === 'attended' ? 'bg-info-subtle text-info' : 'bg-secondary-subtle text-secondary') }}">
                                        {{ ucfirst($reg->status) }}
                                    </span>
                                @endif
                            </td>
                            <td class="text-muted small col-date {{ in_array('date', $visibleCols) ? '' : 'd-none' }}">
                                {{ $reg->created_at->format('d M, Y') }}
                            </td>
                            <td class="pe-3 text-end col-actions {{ in_array('actions', $visibleCols) ? '' : 'd-none' }}">
                                <div class="d-inline-flex gap-1 align-items-center">
                                    @php
                                        $isScholarshipCampaign = ($campaign->type === 'scholarship' || $campaign->slug === 'jshikkhabritti' || !empty($campaign->form_settings['is_scholarship_form']));
                                    @endphp

                                    @if($isScholarshipCampaign)
                                        {{-- Toggle Scholarship Selection Button (বৃত্তিপ্রাপ্ত নির্বাচিত বাটন) --}}
                                        <form action="{{ route('admin.event-campaigns.registrations.toggle-scholarship', $reg->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @if($reg->status === 'selected' || !empty($reg->form_data['is_scholarship_awarded']))
                                                <button type="submit" class="btn btn-sm btn-success rounded-pill px-2.5 py-1 text-nowrap" style="font-size: 11px;" title="Selected for Scholarship (Click to Remove)">
                                                    <i class="fa-solid fa-award me-1"></i> Awarded
                                                </button>
                                            @else
                                                <button type="submit" class="btn btn-sm btn-outline-success rounded-pill px-2.5 py-1 text-nowrap" style="font-size: 11px;" title="Select / Award Scholarship">
                                                    <i class="fa-solid fa-star me-1"></i> Award
                                                </button>
                                            @endif
                                        </form>

                                        {{-- Viva Evaluation (50 Marks) Button --}}
                                        @php
                                            $vTotal = $reg->form_data['viva_total'] ?? null;
                                        @endphp
                                        <button type="button" class="btn btn-sm {{ $vTotal !== null ? 'btn-info text-white' : 'btn-outline-info' }} rounded-pill px-2.5 py-1 text-nowrap" style="font-size: 11px;" data-bs-toggle="modal" data-bs-target="#vivaModal{{ $reg->id }}" title="Viva Assessment (50 Marks)">
                                            <i class="fa-solid fa-clipboard-check me-1"></i> {{ $vTotal !== null ? "Viva: {$vTotal}/50" : 'Viva' }}
                                        </button>
                                    @else
                                        {{-- Toggle Delegate Approval Button (অনুমোদন বাটন) --}}
                                        <form action="{{ route('admin.event-campaigns.registrations.toggle-approval', $reg->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @if($reg->status === 'confirmed' || $reg->status === 'approved' || $reg->status === 'selected')
                                                <button type="submit" class="btn btn-sm btn-success rounded-pill px-2.5 py-1 text-nowrap" style="font-size: 11px;" title="Approved (Click to mark Pending)">
                                                    <i class="fa-solid fa-circle-check me-1"></i> Approved
                                                </button>
                                            @else
                                                <button type="submit" class="btn btn-sm btn-warning text-dark rounded-pill px-2.5 py-1 text-nowrap" style="font-size: 11px;" title="Click to Approve Participant">
                                                    <i class="fa-solid fa-check me-1"></i> Approve
                                                </button>
                                            @endif
                                        </form>
                                    @endif

                                    {{-- Print Form Button --}}
                                    <a href="{{ route('admin.event-campaigns.registrations.print', $reg->id) }}" target="_blank" class="btn btn-sm btn-outline-dark rounded-pill px-2 py-1" title="Print Application Form">
                                        <i class="fa-solid fa-print"></i>
                                    </a>

                                    {{-- Download PDF Button --}}
                                    <a href="{{ route('admin.event-campaigns.registrations.pdf', $reg->id) }}" class="btn btn-sm btn-outline-danger rounded-pill px-2 py-1" title="Download PDF Form">
                                        <i class="fa-solid fa-file-pdf"></i>
                                    </a>

                                    @if(!empty($reg->form_data) && is_array($reg->form_data))
                                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-2 py-1" data-bs-toggle="modal" data-bs-target="#formDataModal{{ $reg->id }}" title="Custom Fields">
                                            <i class="fa-solid fa-list-check"></i>
                                        </button>
                                    @endif

                                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5 py-1" data-bs-toggle="modal" data-bs-target="#editRegModal{{ $reg->id }}" title="Edit">
                                        <i class="fa-solid fa-sliders"></i>
                                    </button>
                                </div>

                                {{-- Viva Assessment Modal (50 Marks) --}}
                                <div class="modal fade" id="vivaModal{{ $reg->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered text-start">
                                        <div class="modal-content rounded-4 border-0 shadow">
                                            <form action="{{ route('admin.event-campaigns.registrations.viva-evaluation', $reg->id) }}" method="POST" id="vivaForm{{ $reg->id }}">
                                                @csrf
                                                <div class="modal-header border-bottom py-3 bg-light">
                                                    <div>
                                                        <h6 class="modal-title fw-bold mb-0 text-dark">
                                                            <i class="fa-solid fa-clipboard-check text-primary me-1"></i> Viva Evaluation (ভাইভা মূল্যায়ন — ৫০ নম্বর)
                                                        </h6>
                                                        <small class="text-muted">#{{ $reg->registration_number }} — {{ $reg->name }}</small>
                                                    </div>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body p-3">
                                                    <table class="table table-bordered table-sm align-middle mb-3">
                                                        <thead class="table-light small fw-bold">
                                                            <tr>
                                                                <th>মূল্যায়ন সূচক (Assessment Criteria)</th>
                                                                <th style="width: 25%;">পূর্ণমান</th>
                                                                <th style="width: 30%;">প্রাপ্ত নম্বর</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr>
                                                                <td>উপস্থিতি (Attendance)</td>
                                                                <td class="text-center font-monospace">১</td>
                                                                <td><input type="number" step="0.5" max="1" min="0" name="viva_attendance" class="form-control form-control-sm font-monospace text-center viva-input-{{ $reg->id }}" value="{{ $reg->form_data['viva_attendance'] ?? '' }}" oninput="calcVivaTotal({{ $reg->id }})"></td>
                                                            </tr>
                                                            <tr>
                                                                <td>কাগজপত্র (Documents Verification)</td>
                                                                <td class="text-center font-monospace">২</td>
                                                                <td><input type="number" step="0.5" max="2" min="0" name="viva_documents" class="form-control form-control-sm font-monospace text-center viva-input-{{ $reg->id }}" value="{{ $reg->form_data['viva_documents'] ?? '' }}" oninput="calcVivaTotal({{ $reg->id }})"></td>
                                                            </tr>
                                                            <tr>
                                                                <td>পোশাক পরিচ্ছেদ (Attire & Decorum)</td>
                                                                <td class="text-center font-monospace">১০</td>
                                                                <td><input type="number" step="0.5" max="10" min="0" name="viva_attire" class="form-control form-control-sm font-monospace text-center viva-input-{{ $reg->id }}" value="{{ $reg->form_data['viva_attire'] ?? '' }}" oninput="calcVivaTotal({{ $reg->id }})"></td>
                                                            </tr>
                                                            <tr>
                                                                <td>ফিউচার প্লান (Future Career Plan)</td>
                                                                <td class="text-center font-monospace">১০</td>
                                                                <td><input type="number" step="0.5" max="10" min="0" name="viva_future_plan" class="form-control form-control-sm font-monospace text-center viva-input-{{ $reg->id }}" value="{{ $reg->form_data['viva_future_plan'] ?? '' }}" oninput="calcVivaTotal({{ $reg->id }})"></td>
                                                            </tr>
                                                            <tr>
                                                                <td>পাঠ অভ্যাস (Reading Habits & Books)</td>
                                                                <td class="text-center font-monospace">১০</td>
                                                                <td><input type="number" step="0.5" max="10" min="0" name="viva_reading_habit" class="form-control form-control-sm font-monospace text-center viva-input-{{ $reg->id }}" value="{{ $reg->form_data['viva_reading_habit'] ?? '' }}" oninput="calcVivaTotal({{ $reg->id }})"></td>
                                                            </tr>
                                                            <tr>
                                                                <td>স্বেচ্ছাসেবী অভিজ্ঞতা (Volunteer Exp.)</td>
                                                                <td class="text-center font-monospace">১০</td>
                                                                <td><input type="number" step="0.5" max="10" min="0" name="viva_volunteer_exp" class="form-control form-control-sm font-monospace text-center viva-input-{{ $reg->id }}" value="{{ $reg->form_data['viva_volunteer_exp'] ?? '' }}" oninput="calcVivaTotal({{ $reg->id }})"></td>
                                                            </tr>
                                                            <tr>
                                                                <td>উপস্থিত বুদ্ধিমত্তা (Presence of Mind / IQ)</td>
                                                                <td class="text-center font-monospace">৭</td>
                                                                <td><input type="number" step="0.5" max="7" min="0" name="viva_iq" class="form-control form-control-sm font-monospace text-center viva-input-{{ $reg->id }}" value="{{ $reg->form_data['viva_iq'] ?? '' }}" oninput="calcVivaTotal({{ $reg->id }})"></td>
                                                            </tr>
                                                            <tr class="table-primary fw-bold">
                                                                <td>সর্বমোট প্রাপ্ত নম্বর (Total Score)</td>
                                                                <td class="text-center font-monospace">৫০</td>
                                                                <td class="text-center font-monospace fs-6 text-primary" id="vivaTotalDisplay{{ $reg->id }}">
                                                                    {{ $reg->form_data['viva_total'] ?? 0 }} / ৫০
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>

                                                    <div class="form-check p-2 bg-light rounded border mb-3">
                                                        <input class="form-check-input ms-0 me-2" type="checkbox" name="is_awarded" id="awardCheck{{ $reg->id }}" value="1" {{ ($reg->status === 'selected' || !empty($reg->form_data['is_scholarship_awarded'])) ? 'checked' : '' }}>
                                                        <label class="form-check-label small fw-bold text-success" for="awardCheck{{ $reg->id }}">
                                                            <i class="fa-solid fa-award me-1"></i> এই শিক্ষার্থীকে শিক্ষাবৃত্তি প্রদান (Selected for Scholarship) হিসেবে নির্বাচন করুন
                                                        </label>
                                                    </div>

                                                    <div class="d-flex justify-content-end gap-2">
                                                        <button type="button" class="btn btn-light border btn-sm rounded-pill px-3" data-bs-dismiss="modal">Close</button>
                                                        <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4 fw-bold">
                                                            <i class="fa-solid fa-save me-1"></i> Save Evaluation
                                                        </button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                {{-- Custom Form Data Modal --}}
                                @if(!empty($reg->form_data) && is_array($reg->form_data))
                                    <div class="modal fade" id="formDataModal{{ $reg->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered text-start">
                                            <div class="modal-content rounded-4 border-0 shadow">
                                                <div class="modal-header border-bottom py-3">
                                                    <h6 class="modal-title fw-bold">Custom Data: #{{ $reg->registration_number }}</h6>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body p-3">
                                                    <table class="table table-bordered table-sm mb-0">
                                                        <tbody>
                                                            @foreach($reg->form_data as $key => $val)
                                                                <tr>
                                                                    <th class="bg-light text-muted w-40 text-capitalize">{{ str_replace('_', ' ', $key) }}</th>
                                                                    <td>{{ is_array($val) ? implode(', ', $val) : $val }}</td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                {{-- Quick Edit Participant Modal --}}
                                <div class="modal fade" id="editRegModal{{ $reg->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered text-start">
                                        <div class="modal-content rounded-4 border-0 shadow">
                                            <form action="{{ route('admin.event-campaigns.registrations.update', $reg->id) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <div class="modal-header border-bottom py-3">
                                                    <h6 class="modal-title fw-bold">Participant #{{ $reg->registration_number }}</h6>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body p-3">
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-semibold text-muted">Status</label>
                                                        <select name="status" class="form-select">
                                                            <option value="confirmed" {{ $reg->status === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                                            <option value="attended" {{ $reg->status === 'attended' ? 'selected' : '' }}>Attended</option>
                                                            <option value="pending" {{ $reg->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                                            <option value="rejected" {{ $reg->status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                                                        </select>
                                                    </div>
                                                    @if($campaign->has_fee_or_donation)
                                                        <div class="mb-3">
                                                            <label class="form-label small fw-semibold text-muted">Payment</label>
                                                            <select name="payment_status" class="form-select">
                                                                <option value="pending" {{ $reg->payment_status === 'pending' ? 'selected' : '' }}>Pending</option>
                                                                <option value="verified" {{ $reg->payment_status === 'verified' ? 'selected' : '' }}>Verified</option>
                                                                <option value="waived" {{ $reg->payment_status === 'waived' ? 'selected' : '' }}>Waived</option>
                                                                <option value="refunded" {{ $reg->payment_status === 'refunded' ? 'selected' : '' }}>Refunded</option>
                                                            </select>
                                                        </div>
                                                    @endif
                                                    <div class="mb-2">
                                                        <label class="form-label small fw-semibold text-muted">Notes</label>
                                                        <textarea name="admin_notes" rows="2" class="form-control" placeholder="Internal notes...">{{ $reg->admin_notes }}</textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-top py-2">
                                                    <button type="button" class="btn btn-light rounded-pill btn-sm px-3" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-primary rounded-pill btn-sm px-4 fw-semibold">Save</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="text-center py-5 text-muted">
                                <div class="mb-2"><i class="fa-solid fa-user-xmark fs-2 text-secondary"></i></div>
                                <h6 class="fw-bold">No Participants</h6>
                                <p class="small mb-0">Share the link <a href="{{ $campaign->public_url }}" target="_blank">{{ $campaign->public_url }}</a> to get registrations.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($registrations->hasPages())
            <div class="card-footer bg-white py-3 border-top d-flex justify-content-end">
                {{ $registrations->links() }}
            </div>
        @endif
    </div>

</div>

{{-- Modal 1: Quick Edit Title & Badge & Slug --}}
<div class="modal fade" id="editTitleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered text-start">
        <div class="modal-content rounded-4 border-0 shadow">
            <form id="editTitleForm" action="{{ route('admin.event-campaigns.update-title', $campaign->id) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-header border-bottom py-3">
                    <h6 class="modal-title fw-bold d-flex align-items-center gap-2">
                        <i class="fa-solid fa-pen-to-square text-primary"></i> Change Title & URL
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-dark">Campaign Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="modalInputTitle" class="form-control" value="{{ $campaign->title }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-dark">Badge / Sub-heading</label>
                        <input type="text" name="badge_text" id="modalInputBadge" class="form-control" placeholder="e.g. Open, Registration Live" value="{{ $campaign->badge_text }}">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-semibold text-dark">URL Slug</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted fw-monospace small">{{ url('/') }}/</span>
                            <input type="text" name="slug" id="modalInputSlug" class="form-control font-monospace" value="{{ $campaign->slug }}" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top py-2.5">
                    <button type="button" class="btn btn-light rounded-pill btn-sm px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill btn-sm px-4 fw-semibold" id="saveTitleBtn">Save Title</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal 2: Table Customization & Column Settings --}}
<div class="modal fade" id="tableSettingsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered text-start">
        <div class="modal-content rounded-4 border-0 shadow">
            <form action="{{ route('admin.event-campaigns.table-settings', $campaign->id) }}" method="POST" id="tableSettingsForm">
                @csrf
                <div class="modal-header border-bottom py-3">
                    <h6 class="modal-title fw-bold d-flex align-items-center gap-2">
                        <i class="fa-solid fa-table-columns text-primary"></i> Customize Table
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    
                    {{-- Rows Per Page ("কমানো বাড়ানো") --}}
                    <div class="mb-4">
                        <label class="form-label small fw-semibold text-dark mb-2">Rows Per Page (কমানো / বাড়ানো)</label>
                        <div class="d-flex gap-2">
                            @foreach([10, 25, 50, 100, 250] as $rNum)
                                <div class="flex-grow-1">
                                    <input type="radio" class="btn-check" name="per_page" id="perPage_{{ $rNum }}" value="{{ $rNum }}" {{ $perPage == $rNum ? 'checked' : '' }}>
                                    <label class="btn btn-outline-secondary btn-sm w-100 rounded-3 py-1.5" for="perPage_{{ $rNum }}">{{ $rNum }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Table Density --}}
                    <div class="mb-4">
                        <label class="form-label small fw-semibold text-dark mb-2">Row Density</label>
                        <div class="d-flex gap-2">
                            <div class="flex-grow-1">
                                <input type="radio" class="btn-check" name="density" id="density_compact" value="compact" {{ $density === 'compact' ? 'checked' : '' }}>
                                <label class="btn btn-outline-secondary btn-sm w-100 rounded-3 py-1.5" for="density_compact">Compact</label>
                            </div>
                            <div class="flex-grow-1">
                                <input type="radio" class="btn-check" name="density" id="density_standard" value="standard" {{ $density === 'standard' ? 'checked' : '' }}>
                                <label class="btn btn-outline-secondary btn-sm w-100 rounded-3 py-1.5" for="density_standard">Standard</label>
                            </div>
                        </div>
                    </div>

                    {{-- Columns Toggle Selector --}}
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <label class="form-label small fw-semibold text-dark mb-0">Visible Columns</label>
                            <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none small" onclick="selectAllColumns(true)">Select All</button>
                        </div>
                        
                        <div class="p-3 bg-light rounded-3 border d-flex flex-column gap-2" style="max-height: 220px; overflow-y: auto;">
                            @php
                                $availableCols = [
                                    'reg_no'       => 'Registration #',
                                    'participant'  => 'Participant Name',
                                    'contact'      => 'Contact (Phone & Email)',
                                    'location'     => 'Location (District & Thana)',
                                    'institution'  => 'Institution / Org',
                                    'fee'          => 'Fee / Donation Amount',
                                    'payment'      => 'Payment Verification',
                                    'status'       => 'Registration Status',
                                    'date'         => 'Registration Date',
                                    'actions'      => 'Action Buttons',
                                ];
                            @endphp

                            @foreach($availableCols as $colKey => $colName)
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input col-toggle-check" type="checkbox" name="columns[]" value="{{ $colKey }}" id="col_{{ $colKey }}" {{ in_array($colKey, $visibleCols) ? 'checked' : '' }}>
                                    <label class="form-check-label small text-dark" for="col_{{ $colKey }}">{{ $colName }}</label>
                                </div>
                            @endforeach

                            {{-- Custom fields dynamic toggles --}}
                            @if(!empty($customFields))
                                <div class="border-top pt-2 mt-1">
                                    <small class="text-muted fw-bold text-uppercase d-block mb-1" style="font-size: 10px;">Custom Fields Columns</small>
                                    @foreach($customFields as $cf)
                                        @php $cfKey = 'custom_' . ($cf['name'] ?? $loop->iteration); @endphp
                                        <div class="form-check form-switch m-0">
                                            <input class="form-check-input col-toggle-check" type="checkbox" name="columns[]" value="{{ $cfKey }}" id="col_{{ $cfKey }}" {{ in_array($cfKey, $visibleCols) ? 'checked' : '' }}>
                                            <label class="form-check-label small text-dark" for="col_{{ $cfKey }}">
                                                {{ $cf['label'] ?? ucfirst($cf['name'] ?? 'Custom Field') }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                </div>
                <div class="modal-footer border-top py-2.5">
                    <button type="button" class="btn btn-light rounded-pill btn-sm px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill btn-sm px-4 fw-semibold">Apply & Save</button>
                </div>
            </form>
        </div>
    {{-- Modal 3: Delegate Card Background & Customizer --}}
@php
    $cCardDesign = $campaign->form_settings['card_design'] ?? [];
    $cBgImg = $cCardDesign['bg_image'] ?? null;
    $cTheme = $cCardDesign['theme_color'] ?? ($campaign->theme_color ?: '#7f1d1d');
    $cBadge = $cCardDesign['badge_text'] ?? ($campaign->badge_text ?: 'DELEGATE PASS');
@endphp
<div class="modal fade" id="cardDesignModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered text-start">
        <div class="modal-content rounded-4 border-0 shadow">
            <form action="{{ route('admin.event-campaigns.card-design', $campaign->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header border-bottom py-3">
                    <h6 class="modal-title fw-bold d-flex align-items-center gap-2">
                        <i class="fa-solid fa-palette text-primary"></i> Delegate Card Design & Background
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    
                    {{-- Current Card Background Preview --}}
                    @if($cBgImg)
                        <div class="mb-3 p-2 border rounded-3 bg-light text-center">
                            <small class="text-muted d-block mb-1">Current Card Background Image:</small>
                            <img src="{{ asset('storage/' . $cBgImg) }}" alt="Card Background" style="max-height: 90px; max-width: 100%; border-radius: 6px; object-fit: cover;">
                        </div>
                    @endif

                    {{-- Upload Custom Background Image --}}
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-dark">Upload Event Card Background (ডিজাইন ইমেজ)</label>
                        <input type="file" name="card_bg_image" class="form-control" accept="image/*">
                        <small class="text-muted" style="font-size: 11px;">Recommended: High-resolution PNG/JPG festival background or watermark.</small>
                    </div>

                    {{-- Card Theme Color --}}
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-dark">Card Header & Border Color</label>
                        <div class="d-flex align-items-center gap-2">
                            <input type="color" name="card_theme_color" class="form-control form-control-color" value="{{ $cTheme }}" style="width: 50px; height: 38px;">
                            <input type="text" name="card_theme_color" class="form-control font-monospace" value="{{ $cTheme }}" placeholder="#7f1d1d">
                        </div>
                    </div>

                    {{-- Card Badge Text --}}
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-dark">Badge / Pass Title</label>
                        <input type="text" name="card_badge_text" class="form-control" value="{{ $cBadge }}" placeholder="e.g. DELEGATE PASS, AUTHOR PASS">
                    </div>

                </div>
                <div class="modal-footer border-top py-2.5">
                    <button type="button" class="btn btn-light rounded-pill btn-sm px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill btn-sm px-4 fw-semibold">Save Card Design</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Toast Container for dynamic copy / title feedback --}}
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1090;">
    <div id="liveToast" class="toast align-items-center text-bg-dark border-0 rounded-4 shadow" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body small fw-medium" id="toastMessage">Done</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const toastEl = document.getElementById('liveToast');
    const toastMsg = document.getElementById('toastMessage');
    const toast = new bootstrap.Toast(toastEl, { delay: 2500 });

    function showToast(msg) {
        toastMsg.textContent = msg;
        toast.show();
    }

    // 1. Quick Copy URL
    document.querySelectorAll('.copy-slug-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const url = this.getAttribute('data-url');
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(url).then(() => {
                    showToast('Copied: ' + url);
                });
            } else {
                const tempInput = document.createElement('input');
                tempInput.value = url;
                document.body.appendChild(tempInput);
                tempInput.select();
                document.execCommand('copy');
                document.body.removeChild(tempInput);
                showToast('Copied: ' + url);
            }
        });
    });

    // 2. AJAX Title Change Handler
    const titleForm = document.getElementById('editTitleForm');
    const titleModalEl = document.getElementById('editTitleModal');
    const titleModal = bootstrap.Modal.getInstance(titleModalEl) || new bootstrap.Modal(titleModalEl);

    titleForm.addEventListener('submit', function (e) {
        e.preventDefault();
        const submitBtn = document.getElementById('saveTitleBtn');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Saving...';

        const formData = new FormData(titleForm);

        fetch(titleForm.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Save Title';
            if (data.success) {
                document.getElementById('headerTitle').textContent = data.title;
                document.getElementById('breadcrumbTitle').textContent = data.title;
                
                const badgeEl = document.getElementById('headerBadge');
                if (data.badge_text) {
                    badgeEl.innerHTML = `<span class="badge bg-primary-subtle text-primary border border-primary-subtle fs-6">${data.badge_text}</span>`;
                } else {
                    badgeEl.innerHTML = '';
                }

                if (data.public_url) {
                    document.getElementById('publicUrlText').textContent = data.public_url;
                    document.getElementById('publicUrlLink').href = data.public_url;
                    document.querySelectorAll('.copy-slug-btn').forEach(b => b.setAttribute('data-url', data.public_url));
                }

                titleModal.hide();
                showToast(data.message || 'Title updated successfully');
            } else {
                alert(data.message || 'Error updating title');
            }
        })
        .catch(err => {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Save Title';
            console.error(err);
            titleForm.submit(); // fallback to normal submit
        });
    });

});

function selectAllColumns(check) {
    document.querySelectorAll('.col-toggle-check').forEach(c => c.checked = check);
}

function calcVivaTotal(id) {
    const inputs = document.querySelectorAll('.viva-input-' + id);
    let total = 0;
    inputs.forEach(input => {
        const val = parseFloat(input.value);
        if (!isNaN(val)) {
            total += val;
        }
    });
    const display = document.getElementById('vivaTotalDisplay' + id);
    if (display) {
        display.textContent = (total % 1 === 0 ? total : total.toFixed(1)) + ' / ৫০';
    }
}
</script>
@endsection
