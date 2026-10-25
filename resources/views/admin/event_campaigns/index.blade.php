@extends('layouts.admin')

@section('title', 'Campaigns & Events — ideaabd')
@section('hide_header', true)

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin-event-campaign.css') }}?v={{ @filemtime(public_path('css/admin-event-campaign.css')) ?: 1 }}">
    <style>
        .aec-table-row {
            transition: all 0.2s ease;
        }
        .aec-table-row:hover {
            background-color: rgba(241, 245, 249, 0.6) !important;
        }
        .dark-mode .aec-table-row:hover {
            background-color: rgba(30, 41, 59, 0.7) !important;
        }
    </style>
@endpush

@section('content')
<div class="container-fluid px-2 px-md-4 py-3">

    {{-- 1. Modern Glassmorphic Hero Banner --}}
    <div class="aec-index-hero">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-2 small text-white-50">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-white text-decoration-none opacity-75 hover-opacity-100"><i class="fa-solid fa-house me-1"></i> Dashboard</a></li>
                        <li class="breadcrumb-item active text-white fw-bold" aria-current="page">Campaigns & Events</li>
                    </ol>
                </nav>
                <h1 class="h3 fw-bolder mb-1 text-white d-flex align-items-center gap-2.5">
                    <i class="fa-solid fa-bullhorn text-warning fs-3"></i> Campaigns & Registration Studio
                </h1>
                <p class="text-white-50 small mb-0 font-monospace">

                </p>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <a href="{{ route('admin.event-campaigns.pathagar') }}" class="btn btn-outline-light rounded-pill px-3 py-2 fw-semibold aec-btn-hover-lift d-inline-flex align-items-center gap-2">
                    <i class="fa-solid fa-book-open-reader text-warning"></i> <span>পাঠাগার নিবন্ধন</span>
                </a>
                <a href="{{ route('admin.event-campaigns.create') }}" class="btn btn-warning text-dark rounded-pill px-3.5 py-2 fw-bold shadow-sm aec-btn-hover-lift d-inline-flex align-items-center gap-2">
                    <i class="fa-solid fa-circle-plus"></i> <span>New Campaign</span>
                </a>
            </div>
        </div>
    </div>

    {{-- Flash Notifications --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-xs border-0 p-3 mb-4" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="fa-solid fa-circle-check fs-5 text-success"></i>
                <div class="fw-semibold">{{ session('success') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- 2. Interactive KPI Stats Cards --}}
    <div class="row g-3 mb-4">
        {{-- Total Campaigns --}}
        <div class="col-6 col-lg-3">
            <a href="{{ route('admin.event-campaigns.index') }}" class="aec-kpi-card" title="Click to view all campaigns">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="kpi-label">Total Campaigns</span>
                    <div class="kpi-icon-wrap bg-primary-subtle text-primary">
                        <i class="fa-solid fa-layer-group"></i>
                    </div>
                </div>
                <div class="kpi-value">{{ number_format($totalCampaigns) }}</div>
                <div class="text-muted small mt-1 d-flex align-items-center gap-1">
                    <i class="fa-solid fa-arrow-right text-primary small"></i> All registered programs
                </div>
            </a>
        </div>

        {{-- Active Campaigns --}}
        <div class="col-6 col-lg-3">
            <a href="{{ route('admin.event-campaigns.index', ['status' => 'active']) }}" class="aec-kpi-card" title="Click to filter active campaigns">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="kpi-label text-success">Active Live</span>
                    <div class="kpi-icon-wrap bg-success-subtle text-success">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                </div>
                <div class="kpi-value text-success">{{ number_format($activeCampaigns) }}</div>
                <div class="text-muted small mt-1 d-flex align-items-center gap-1">
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-1.5 py-0.5">Live Form</span> Open for entries
                </div>
            </a>
        </div>

        {{-- Total Registrations --}}
        <div class="col-6 col-lg-3">
            <div class="aec-kpi-card">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="kpi-label text-info">Total Registrants</span>
                    <div class="kpi-icon-wrap bg-info-subtle text-info">
                        <i class="fa-solid fa-users"></i>
                    </div>
                </div>
                <div class="kpi-value text-dark">{{ number_format($totalRegistrations) }}</div>
                <div class="text-muted small mt-1 d-flex align-items-center gap-1">
                    <i class="fa-solid fa-user-check text-info"></i> Synced across forms
                </div>
            </div>
        </div>

        {{-- Total Revenue --}}
        <div class="col-6 col-lg-3">
            <div class="aec-kpi-card">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="kpi-label text-warning-emphasis">Total Revenue</span>
                    <div class="kpi-icon-wrap bg-warning-subtle text-warning">
                        <i class="fa-solid fa-hand-holding-dollar"></i>
                    </div>
                </div>
                <div class="kpi-value text-dark">৳{{ number_format($totalCollected, 2) }}</div>
                <div class="text-muted small mt-1 d-flex align-items-center gap-1">
                    <i class="fa-solid fa-shield-check text-success"></i> Verified Payments
                </div>
            </div>
        </div>
    </div>

    {{-- 3. Filter Tabs Bar & Search Box --}}
    <div class="card border-0 shadow-xs rounded-4 mb-4 bg-white">
        <div class="card-body p-3">
            {{-- Quick Filter Pills --}}
            <div class="d-flex flex-wrap align-items-center gap-2 mb-3 aec-filter-pills-bar">
                <a href="{{ route('admin.event-campaigns.index') }}" class="aec-tab-btn {{ !request('status') && !request('type') ? 'active' : '' }}">
                    All Campaigns <span class="badge bg-secondary">{{ $totalCampaigns }}</span>
                </a>
                <a href="{{ route('admin.event-campaigns.index', ['status' => 'active']) }}" class="aec-tab-btn {{ request('status') === 'active' ? 'active' : '' }}">
                    <i class="fa-solid fa-circle text-success" style="font-size: 8px;"></i> Active <span class="badge bg-secondary">{{ $activeCampaigns }}</span>
                </a>
                <a href="{{ route('admin.event-campaigns.index', ['status' => 'inactive']) }}" class="aec-tab-btn {{ request('status') === 'inactive' ? 'active' : '' }}">
                    Inactive
                </a>
                <a href="{{ route('admin.event-campaigns.index', ['status' => 'expired']) }}" class="aec-tab-btn {{ request('status') === 'expired' ? 'active' : '' }}">
                    Expired
                </a>
                <span class="text-muted opacity-50 px-1">|</span>
                <a href="{{ route('admin.event-campaigns.index', ['type' => 'scholarship']) }}" class="aec-tab-btn {{ request('type') === 'scholarship' ? 'active' : '' }}">
                    <i class="fa-solid fa-graduation-cap text-indigo"></i> শিক্ষাবৃত্তি (Scholarship)
                </a>
                <a href="{{ route('admin.event-campaigns.index', ['type' => 'library']) }}" class="aec-tab-btn {{ request('type') === 'library' ? 'active' : '' }}">
                    <i class="fa-solid fa-book-open-reader text-success"></i> পাঠাগার (Library)
                </a>
                <a href="{{ route('admin.event-campaigns.index', ['type' => 'event']) }}" class="aec-tab-btn {{ request('type') === 'event' ? 'active' : '' }}">
                    <i class="fa-solid fa-calendar-check text-primary"></i> ইভেন্ট (Event)
                </a>
            </div>

            {{-- Full Search & Filter Form --}}
            <form action="{{ route('admin.event-campaigns.index') }}" method="GET" class="row g-2 align-items-center" id="campaignFilterForm">
                <div class="col-12 col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                        <input type="text" name="search" id="campaignSearchInput" class="form-control bg-light border-start-0 ps-0" placeholder="Instant Search title, slug, badge, type..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <select name="type" class="form-select bg-light" onchange="this.form.submit()">
                        <option value="">All Categories & Types</option>
                        <option value="scholarship" {{ request('type') == 'scholarship' ? 'selected' : '' }}>🎓 Scholarship (শিক্ষাবৃত্তি)</option>
                        <option value="library" {{ request('type') == 'library' ? 'selected' : '' }}>📚 Library / Book Grant (পাঠাগার)</option>
                        <option value="event" {{ request('type') == 'event' ? 'selected' : '' }}>🎪 Festival / Event (উৎসব)</option>
                        <option value="donation" {{ request('type') == 'donation' ? 'selected' : '' }}>💖 Donation (অনুদান)</option>
                        <option value="competition" {{ request('type') == 'competition' ? 'selected' : '' }}>🏆 Competition (প্রতিযোগিতা)</option>
                        <option value="workshop" {{ request('type') == 'workshop' ? 'selected' : '' }}>💡 Workshop (কর্মশালা)</option>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <select name="status" class="form-select bg-light" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active Live</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive / Paused</option>
                        <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired Deadline</option>
                    </select>
                </div>
                <div class="col-12 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary rounded-pill px-3 fw-bold flex-grow-1">
                        <i class="fa-solid fa-filter me-1"></i> Filter
                    </button>
                    @if(request()->hasAny(['search', 'type', 'status']))
                        <a href="{{ route('admin.event-campaigns.index') }}" class="btn btn-light rounded-pill px-3 border" title="Reset Filters">
                            <i class="fa-solid fa-rotate-left"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- 4. Campaigns Table --}}
    <div class="card border-0 shadow-xs rounded-4 bg-white overflow-hidden">
        <div class="table-responsive">
            <table class="table align-middle mb-0 aec-campaigns-table" id="campaignsTable">
                <thead>
                    <tr>
                        <th class="ps-3 py-3" style="width: 50px;">#</th>
                        <th class="py-3">Campaign Name & Badge</th>
                        <th class="py-3">Type</th>
                        <th class="py-3">URL Slug</th>
                        <th class="py-3 text-center">Registrants</th>
                        <th class="py-3">Fee / Goal</th>
                        <th class="py-3">Status</th>
                        <th class="pe-3 py-3 text-end" style="min-width: 170px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="campaignsTableBody">
                    @forelse($campaigns as $campaign)
                        @php
                            $typeClass = match($campaign->type) {
                                'scholarship' => 'badge-type-scholarship',
                                'library'     => 'badge-type-library',
                                'event'       => 'badge-type-event',
                                'competition' => 'badge-type-competition',
                                'donation'    => 'badge-type-donation',
                                'workshop'    => 'badge-type-workshop',
                                default       => 'bg-light text-dark border',
                            };
                            $themeCol = $campaign->theme_color ?: '#0284c7';
                            $searchHaystack = strtolower($campaign->title . ' ' . $campaign->slug . ' ' . $campaign->badge_text . ' ' . $campaign->type);
                        @endphp
                        <tr class="aec-table-row campaign-row" id="row-campaign-{{ $campaign->id }}" data-search="{{ $searchHaystack }}">
                            <td class="ps-3 fw-bold text-muted">{{ $campaign->id }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="aec-campaign-avatar" style="border-left: 4px solid {{ $themeCol }} !important;">
                                        @if($campaign->banner_image)
                                            <img src="{{ asset('storage/' . $campaign->banner_image) }}" alt="{{ $campaign->title }}" class="img-fluid" style="max-height: 42px; object-fit: cover;">
                                        @else
                                            <i class="fa-solid fa-bullhorn text-secondary fs-5"></i>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="d-flex align-items-center gap-1.5 flex-wrap">
                                            <a href="{{ route('admin.event-campaigns.show', $campaign->id) }}" class="fw-bold text-dark text-decoration-none hover-primary fs-6">
                                                {{ $campaign->title }}
                                            </a>
                                            @if($campaign->badge_text)
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-0.5" style="font-size: 10.5px;">
                                                    {{ $campaign->badge_text }}
                                                </span>
                                            @endif
                                        </div>
                                        <div class="text-muted small mt-0.5 d-flex align-items-center gap-2" style="font-size: 12px;">
                                            <span><i class="fa-regular fa-calendar-days me-1"></i> {{ $campaign->created_at->format('d M, Y') }}</span>
                                            @if($campaign->starts_at)
                                                <span>&bull; Starts: {{ \Carbon\Carbon::parse($campaign->starts_at)->format('d M') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge {{ $typeClass }} px-2.5 py-1 text-capitalize">
                                    {{ $campaign->type }}
                                </span>
                            </td>
                            <td>
                                <div class="aec-slug-pill">
                                    <span class="text-primary fw-bold me-1">/{{ $campaign->slug }}</span>
                                    <button type="button" class="btn btn-link p-0 text-muted copy-slug-btn me-1.5" data-url="{{ $campaign->public_url }}" title="Copy Public Link">
                                        <i class="fa-regular fa-copy"></i>
                                    </button>
                                    <a href="{{ $campaign->public_url }}" target="_blank" class="text-muted" title="Open Link in new tab">
                                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                    </a>
                                </div>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('admin.event-campaigns.show', $campaign->id) }}" class="badge bg-info-subtle text-info border border-info-subtle px-3 py-1.5 text-decoration-none fs-6 fw-bold aec-btn-hover-lift d-inline-flex align-items-center gap-1.5" title="অংশগ্রহণকারী তালিকা দেখুন">
                                    <i class="fa-solid fa-users small"></i>
                                    <span>{{ $campaign->registrations_count }}</span>
                                </a>
                            </td>
                            <td>
                                @if($campaign->has_fee_or_donation)
                                    @if($campaign->is_donation_flexible)
                                        <span class="text-success fw-semibold">Flexible</span>
                                        <div class="text-muted small">Min: ৳{{ number_format($campaign->min_donation) }}</div>
                                    @else
                                        <span class="fw-bold text-dark">৳{{ number_format($campaign->fee_amount) }}</span>
                                    @endif
                                @else
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">Free Entry</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-1.5">
                                    <button type="button" 
                                            class="btn btn-sm rounded-pill px-2.5 py-0.5 fw-semibold toggle-status-btn {{ $campaign->is_active ? 'btn-success text-white' : 'btn-outline-secondary' }}"
                                            data-id="{{ $campaign->id }}"
                                            data-url="{{ route('admin.event-campaigns.toggle-status', $campaign->id) }}"
                                            title="Click to toggle active/pause">
                                        {{ $campaign->is_active ? 'Active' : 'Inactive' }}
                                    </button>
                                    @if($campaign->isExpired())
                                        <span class="badge bg-danger-subtle text-danger border" title="Deadline reached">Expired</span>
                                    @elseif($campaign->isFull())
                                        <span class="badge bg-warning-subtle text-warning border" title="Capacity full">Full</span>
                                    @endif
                                </div>
                            </td>
                            <td class="pe-3 text-end">
                                <div class="d-inline-flex gap-1.5 align-items-center">
                                    {{-- Participant List Button --}}
                                    <a href="{{ route('admin.event-campaigns.show', $campaign->id) }}" class="aec-action-btn aec-action-btn-view" title="অংশগ্রহণকারী তালিকা">
                                        <i class="fa-solid fa-users"></i>
                                    </a>

                                    {{-- Dynamic 1-Click Broadcast SMS Action Button --}}
                                    <button type="button"
                                            class="aec-action-btn aec-action-btn-sms btn-campaign-quick-sms"
                                            data-id="{{ $campaign->id }}"
                                            data-title="{{ $campaign->title }}"
                                            data-type="{{ $campaign->type }}"
                                            data-total="{{ $campaign->registrations_count }}"
                                            data-confirmed="{{ $campaign->confirmed_registrations_count ?? 0 }}"
                                            data-pending="{{ $campaign->pending_registrations_count ?? 0 }}"
                                            data-venue="{{ $campaign->form_settings['form_venue'] ?? 'রংপুর' }}"
                                            data-date="{{ $campaign->form_settings['form_date'] ?? ($campaign->starts_at ? $campaign->starts_at->format('d M Y') : '২০ অক্টোবর ২০২৬') }}"
                                            data-url="{{ route('admin.event-campaigns.broadcast-sms', $campaign->id) }}"
                                            title="এই ইভেন্টের সকল অংশগ্রহণকারীকে এক ক্লিকে এসএমএস পাঠান">
                                        <i class="fa-solid fa-paper-plane"></i>
                                    </button>

                                    {{-- Quick Title Edit Button --}}
                                    <button type="button"
                                            class="aec-action-btn aec-action-btn-edit btn-quick-edit-trigger"
                                            data-id="{{ $campaign->id }}"
                                            data-title="{{ $campaign->title }}"
                                            data-badge="{{ $campaign->badge_text }}"
                                            data-slug="{{ $campaign->slug }}"
                                            data-url="{{ route('admin.event-campaigns.update-title', $campaign->id) }}"
                                            title="কুইক টাইটেল ও স্লাগ এডিট">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>

                                    {{-- Full Settings Edit Button --}}
                                    <a href="{{ route('admin.event-campaigns.edit', $campaign->id) }}" class="aec-action-btn aec-action-btn-edit" title="পূর্ণাঙ্গ সেটিংস সম্পাদনা">
                                        <i class="fa-solid fa-sliders"></i>
                                    </a>

                                    {{-- Duplicate / Clone Button --}}
                                    <form action="{{ route('admin.event-campaigns.clone', $campaign->id) }}" method="POST" class="d-inline m-0 form-clone-campaign">
                                        @csrf
                                        <button type="submit" class="aec-action-btn aec-action-btn-clone" title="ক্লোন / ডুপ্লিকেট ক্যাম্পেইন">
                                            <i class="fa-solid fa-copy"></i>
                                        </button>
                                    </form>

                                    {{-- Delete Button --}}
                                    <form action="{{ route('admin.event-campaigns.destroy', $campaign->id) }}" method="POST" class="d-inline m-0 form-delete-campaign" data-title="{{ $campaign->title }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="aec-action-btn aec-action-btn-delete" title="ক্যাম্পেইন মুছে ফেলুন">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <div class="mb-3"><i class="fa-solid fa-bullhorn fs-1 text-secondary opacity-50"></i></div>
                                <h5 class="fw-bold text-dark">কোনো ক্যাম্পেইন পাওয়া যায়নি</h5>
                                <p class="small text-muted mb-3">নতুন ক্যাম্পেইন তৈরি করুন যেমন: /rsutshab অথবা /joyeeshikkhabritti</p>
                                <a href="{{ route('admin.event-campaigns.create') }}" class="btn btn-primary rounded-pill px-4 shadow-sm">
                                    <i class="fa-solid fa-plus me-1"></i> New Campaign তৈরি করুন
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($campaigns->hasPages())
            <div class="card-footer bg-white py-3 border-top d-flex justify-content-end">
                {{ $campaigns->links() }}
            </div>
        @endif
    </div>

</div>

{{-- 5. Consolidated Universal Quick Edit Title Modal (Cleanly outside table!) --}}
<div class="modal fade" id="quickEditCampaignModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered text-start">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <form id="quickEditCampaignForm" method="POST" action="">
                @csrf
                @method('PATCH')
                <div class="modal-header border-bottom py-3 px-4 bg-light">
                    <h6 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                        <i class="fa-solid fa-pen-to-square text-primary"></i> <span id="quickEditModalTitleText">Change Campaign Title & Slug</span>
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Campaign Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="quickEditInputTitle" class="form-control rounded-3" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Badge / Sub-heading</label>
                        <input type="text" name="badge_text" id="quickEditInputBadge" class="form-control rounded-3" placeholder="যেমন: ডেলিগেট নিবন্ধন ২০২৬">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-bold text-dark">URL Slug <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted font-monospace small">{{ url('/') }}/</span>
                            <input type="text" name="slug" id="quickEditInputSlug" class="form-control font-monospace rounded-end-3" required>
                        </div>
                        <div class="form-text small text-muted">ছোট হাতের ইংরেজি অক্ষর ও ড্যাশ (-) ব্যবহার করুন।</div>
                    </div>
                </div>
                <div class="modal-footer border-top py-2 px-4 bg-light">
                    <button type="button" class="btn btn-light rounded-pill btn-sm px-3 border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill btn-sm px-4 fw-bold">
                        <i class="fa-solid fa-save me-1"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- 6. Dynamic Event Campaign 1-Click Broadcast SMS Modal --}}
<div class="modal fade" id="campaignIndexSmsModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg text-start">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <form id="campaignIndexSmsForm" action="" method="POST" onsubmit="handleIndexBroadcastSmsSubmit(event)">
                @csrf

                {{-- Modal Header --}}
                <div class="modal-header py-3 px-4 text-white" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0369a1 100%);">
                    <div class="d-flex align-items-center gap-2.5">
                        <div class="rounded-circle bg-white bg-opacity-20 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                            <i class="fa-solid fa-paper-plane text-warning fs-5"></i>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2">
                                <h5 class="modal-title fw-bold mb-0">এক ক্লিকে এসএমএস পাঠান</h5>
                                <span class="badge bg-white bg-opacity-20 text-white border border-white-50 small rounded-pill px-2.5 py-0.5" id="indexSmsModalTypeBadge">ইভেন্ট</span>
                            </div>
                            <small class="text-white-50" id="indexSmsModalCampaignTitle">ক্যাম্পেইনের নাম</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                {{-- Modal Body --}}
                <div class="modal-body p-4 bg-light">
                    {{-- Quick Target Stats Pill Banner --}}
                    <div class="d-flex align-items-center justify-content-between p-2.5 mb-3 bg-white rounded-3 border shadow-xs flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2 small">
                            <span class="text-muted"><i class="fa-solid fa-users me-1 text-primary"></i>মোট নিবন্ধিত:</span>
                            <strong class="text-dark font-monospace fs-6" id="indexSmsModalTotalCount">0</strong>
                        </div>
                        <div class="vr bg-secondary opacity-25 d-none d-sm-block"></div>
                        <div class="d-flex align-items-center gap-2 small">
                            <span class="text-muted"><i class="fa-solid fa-circle-check me-1 text-success"></i>কনফার্মড / অ্যাপ্রুভড:</span>
                            <strong class="text-success font-monospace" id="indexSmsModalConfirmedCount">0</strong>
                        </div>
                        <div class="vr bg-secondary opacity-25 d-none d-sm-block"></div>
                        <div class="d-flex align-items-center gap-2 small">
                            <span class="text-muted"><i class="fa-solid fa-clock-rotate-left me-1 text-warning"></i>পেন্ডিং:</span>
                            <strong class="text-warning font-monospace" id="indexSmsModalPendingCount">0</strong>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        {{-- 1. Target Audience --}}
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-dark mb-1 d-flex align-items-center justify-content-between">
                                <span>১. প্রাপক নির্বাচন করুন (Target Audience)</span>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill font-monospace" id="indexSmsLiveTargetBadge">
                                    0 জন প্রাপক
                                </span>
                            </label>
                            <select name="target_group" id="indexSmsTargetGroup" class="form-select form-select-sm fw-medium shadow-xs" onchange="updateIndexSmsTargetDisplay(this.value)">
                                <option value="all" selected id="optTargetAll">সকল অংশগ্রহণকারী (মোট 0 জন)</option>
                                <option value="confirmed" id="optTargetConfirmed">শুধু অনুমোদিত / কনফার্মড (মোট 0 জন)</option>
                                <option value="pending" id="optTargetPending">শুধু পেন্ডিং রিভিউ (মোট 0 জন)</option>
                            </select>
                        </div>

                        {{-- 2. Preset Template Selector --}}
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-dark mb-1">২. রেডি এসএমএস টেমপ্লেট</label>
                            <select class="form-select form-select-sm shadow-xs" id="indexSmsTemplateSelect" onchange="applyIndexSmsTemplate(this.value)">
                                <option value="">-- রেডি টেমপ্লেট বেছে নিন --</option>
                                <option value="schedule" selected>১. ইভেন্ট সময়সূচি ও ভেন্যু আপডেট</option>
                                <option value="card">২. আমন্ত্রণ কার্ড ডাউনলোড ও লিংক</option>
                                <option value="scholarship">৩. শিক্ষাবৃত্তি নোটিশ ও ফলাফল</option>
                                <option value="littlemag">৪. সাহিত্য উৎসব ও লিটিলম্যাগ স্টল</option>
                                <option value="notice">৫. জরুরি নোটিশ ও ঘোষণা</option>
                                <option value="custom">৬. কাস্টম বার্তা (খালি লিখুন)</option>
                            </select>
                        </div>
                    </div>

                    {{-- Dynamic Tags (Click to Insert) --}}
                    <div class="mb-3 p-2.5 bg-white rounded-3 border shadow-xs">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-1 mb-1.5">
                            <span class="small fw-bold text-dark" style="font-size: 12px;">
                                <i class="fa-solid fa-code text-primary me-1"></i> ডাইনামিক ট্যাগ (ক্লিক করলেই মেসেজে যুক্ত হবে):
                            </span>
                            <span class="text-muted small" style="font-size: 11px;">ইউজারের তথ্য স্বয়ংক্রিয়ভাবে রিপ্লেস হবে</span>
                        </div>
                        <div class="d-flex flex-wrap gap-1.5">
                            <button type="button" class="btn btn-xs btn-outline-primary rounded-pill px-2.5 py-1 fw-semibold font-monospace" onclick="insertIndexSmsTag('{name}')" title="অংশগ্রহণকারীর নাম">
                                + {name} (নাম)
                            </button>
                            <button type="button" class="btn btn-xs btn-outline-primary rounded-pill px-2.5 py-1 fw-semibold font-monospace" onclick="insertIndexSmsTag('{reg_no}')" title="রেজিস্ট্রেশন নম্বর">
                                + {reg_no} (আইডি নং)
                            </button>
                            <button type="button" class="btn btn-xs btn-outline-success rounded-pill px-2.5 py-1 fw-semibold font-monospace" onclick="insertIndexSmsTag('{card_url}')" title="কার্ডের ডাউনলোড লিংক">
                                + {card_url} (কার্ড লিংক)
                            </button>
                            <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2.5 py-1 fw-semibold font-monospace" onclick="insertIndexSmsTag('{event_title}')" title="ইভেন্টের নাম">
                                + {event_title} (ইভেন্ট)
                            </button>
                            <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2.5 py-1 fw-semibold font-monospace" onclick="insertIndexSmsTag('{venue}')" title="ভেন্যু">
                                + {venue} (ভেন্যু)
                            </button>
                            <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2.5 py-1 fw-semibold font-monospace" onclick="insertIndexSmsTag('{date}')" title="তারিখ">
                                + {date} (তারিখ)
                            </button>
                        </div>
                    </div>

                    {{-- Message Content Textarea --}}
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label small fw-bold text-dark mb-0">মেসেজ কন্টেন্ট <span class="text-danger">*</span></label>
                            <div class="text-muted font-monospace small" id="indexSmsCounter">
                                <span class="fw-bold text-primary" id="indexSmsCharCount">0</span> অক্ষর &bull;
                                <span class="badge bg-secondary-subtle text-dark border px-2 py-0.5 fw-bold" id="indexSmsMsgCountBadge">1 SMS</span>
                            </div>
                        </div>
                        <textarea name="message" id="indexSmsMessageInput" rows="4" class="form-control font-monospace shadow-xs" style="font-size: 13.5px; line-height: 1.6;" placeholder="এখানে মেসেজ লিখুন বা ট্যাগ যুক্ত করুন..." required oninput="handleIndexSmsMessageInput()"></textarea>
                    </div>

                    {{-- Live Sample Preview --}}
                    <div class="p-3 bg-white rounded-3 border mb-3 shadow-xs">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <span class="small fw-bold text-muted" style="font-size: 11.5px;">
                                <i class="fa-solid fa-mobile-screen me-1 text-primary"></i> প্রাপক যেমন এসএমএস পাবেন (Live Preview):
                            </span>
                            <span class="badge bg-light text-muted border font-monospace" style="font-size: 10px;">নমুনা প্রিভিউ</span>
                        </div>
                        <div class="p-2.5 bg-light rounded-2 font-monospace text-dark small border" id="indexSmsLivePreviewBox" style="font-size: 12.5px; line-height: 1.6; word-break: break-word;">
                            —
                        </div>
                    </div>

                    {{-- Duplicate Phone Safeguard --}}
                    <div class="d-flex align-items-center justify-content-between p-2.5 bg-white rounded-3 border">
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" name="avoid_duplicate_phone" id="indexAvoidDupPhoneCheck" value="1" checked>
                            <label class="form-check-label small fw-semibold text-dark" for="indexAvoidDupPhoneCheck">
                                একই মোবাইল নম্বরে একাধিকবার পাঠানো বন্ধ রাখুন (Avoid Duplicate Numbers)
                            </label>
                        </div>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle small px-2 py-1">প্রস্তাবিত</span>
                    </div>

                    {{-- Dispatch Progress Box --}}
                    <div id="indexSmsProgressBox" class="mt-3 p-3 bg-white rounded-3 border shadow-xs" style="display: none;">
                        <div class="d-flex align-items-center justify-content-between mb-1.5">
                            <span class="small fw-bold text-primary d-flex align-items-center gap-1.5">
                                <span class="spinner-border spinner-border-sm text-primary" role="status"></span>
                                <span id="indexSmsProgressText">বার্তা প্রেরিত হচ্ছে, অনুগ্রহ করে অপেক্ষা করুন...</span>
                            </span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: 100%;"></div>
                        </div>
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="modal-footer py-2.5 px-4 bg-white border-top d-flex justify-content-between">
                    <button type="button" class="btn btn-sm btn-light border rounded-pill px-3.5 fw-semibold" data-bs-dismiss="modal">বন্ধ করুন</button>
                    <button type="submit" id="btnIndexSubmitSms" class="btn btn-sm btn-success rounded-pill px-4 py-1.5 fw-bold shadow-sm d-inline-flex align-items-center gap-1.5">
                        <i class="fa-solid fa-paper-plane"></i>
                        <span id="btnIndexSubmitSmsText">একবারে আপডেট পাঠান</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Toast Container for dynamic copy / status feedback --}}
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
        toastMsg.innerHTML = msg;
        toast.show();
    }

    // 1. Quick Copy Public URL with Visual Animation
    document.querySelectorAll('.copy-slug-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const url = this.getAttribute('data-url');
            const icon = this.querySelector('i');
            const origIconClass = icon ? icon.className : '';

            const onCopySuccess = () => {
                if (icon) icon.className = 'fa-solid fa-check text-success';
                showToast('<i class="fa-solid fa-circle-check text-success me-1"></i> Copied: ' + url);
                setTimeout(() => {
                    if (icon) icon.className = origIconClass;
                }, 1500);
            };

            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(url).then(onCopySuccess);
            } else {
                const tempInput = document.createElement('input');
                tempInput.value = url;
                document.body.appendChild(tempInput);
                tempInput.select();
                document.execCommand('copy');
                document.body.removeChild(tempInput);
                onCopySuccess();
            }
        });
    });

    // 2. Real-Time Client-Side Table Filter while typing
    const searchInput = document.getElementById('campaignSearchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const term = this.value.toLowerCase().trim();
            const rows = document.querySelectorAll('#campaignsTableBody .campaign-row');
            rows.forEach(row => {
                const searchData = row.getAttribute('data-search') || '';
                if (!term || searchData.includes(term)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }

    // 3. Consolidated Dynamic Quick Edit Modal Handler
    const quickModalEl = document.getElementById('quickEditCampaignModal');
    const quickModal = quickModalEl ? bootstrap.Modal.getOrCreateInstance(quickModalEl) : null;
    const quickForm = document.getElementById('quickEditCampaignForm');
    const inputTitle = document.getElementById('quickEditInputTitle');
    const inputBadge = document.getElementById('quickEditInputBadge');
    const inputSlug = document.getElementById('quickEditInputSlug');
    const modalTitleText = document.getElementById('quickEditModalTitleText');

    document.querySelectorAll('.btn-quick-edit-trigger').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.getAttribute('data-id');
            const title = this.getAttribute('data-title');
            const badge = this.getAttribute('data-badge') || '';
            const slug = this.getAttribute('data-slug');
            const actionUrl = this.getAttribute('data-url');

            if (quickForm) quickForm.action = actionUrl;
            if (inputTitle) inputTitle.value = title;
            if (inputBadge) inputBadge.value = badge;
            if (inputSlug) inputSlug.value = slug;
            if (modalTitleText) modalTitleText.textContent = `Quick Edit: #${id} - ${title}`;

            if (quickModal) quickModal.show();
        });
    });

    // 4. AJAX Quick Toggle Status
    document.querySelectorAll('.toggle-status-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const url = this.getAttribute('data-url');
            const button = this;
            button.disabled = true;

            fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                button.disabled = false;
                if (data.success) {
                    if (data.is_active) {
                        button.className = 'btn btn-sm rounded-pill px-2.5 py-0.5 fw-semibold toggle-status-btn btn-success text-white';
                        button.textContent = 'Active';
                    } else {
                        button.className = 'btn btn-sm rounded-pill px-2.5 py-0.5 fw-semibold toggle-status-btn btn-outline-secondary';
                        button.textContent = 'Inactive';
                    }
                    showToast('<i class="fa-solid fa-circle-check text-success me-1"></i> ' + (data.message || 'Status updated'));
                } else {
                    showToast('<i class="fa-solid fa-circle-xmark text-danger me-1"></i> Failed to update status');
                }
            })
            .catch(err => {
                button.disabled = false;
                console.error(err);
                showToast('<i class="fa-solid fa-triangle-exclamation text-warning me-1"></i> Network error updating status');
            });
        });
    });

    // 5. SweetAlert2 Delete Confirmation
    document.querySelectorAll('.form-delete-campaign').forEach(form => {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const campTitle = this.getAttribute('data-title') || 'this campaign';

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'ক্যাম্পেইন মুছে ফেলতে চান?',
                    text: `"${campTitle}" মুছে ফেলা হবে। নিবন্ধিত সকল কাস্টমার ডাটাবেজে সংরক্ষিত থাকবে।`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'হ্যাঁ, ডিলিট করুন',
                    cancelButtonText: 'বাতিল'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            } else if (confirm(`Delete "${campTitle}"? Registered customer records will remain safe.`)) {
                form.submit();
            }
        });
    });

    // 6. Dynamic 1-Click Broadcast SMS for Each Campaign
    let activeCampaignData = null;

    document.querySelectorAll('.btn-campaign-quick-sms').forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();

            const cId = this.dataset.id;
            const title = this.dataset.title;
            const type = this.dataset.type || 'event';
            const total = parseInt(this.dataset.total || '0', 10);
            const confirmed = parseInt(this.dataset.confirmed || '0', 10);
            const pending = parseInt(this.dataset.pending || '0', 10);
            const venue = this.dataset.venue || 'রংপুর';
            const date = this.dataset.date || '২০ অক্টোবর ২০২৬';
            const url = this.dataset.url;

            if (total === 0) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'info',
                        title: 'কোনো অংশগ্রহণকারী নেই',
                        html: `<strong>${title}</strong>-এ এখনও কোনো অংশগ্রহণকারী নিবন্ধন করেননি।<br><small class="text-muted">অংশগ্রহণকারী যুক্ত হওয়ার পর এক ক্লিকে এসএমএস পাঠানো যাবে।</small>`,
                        confirmButtonColor: '#0284c7'
                    });
                } else {
                    alert(`"${title}"-এ এখনও কোনো অংশগ্রহণকারী নিবন্ধন করেননি।`);
                }
                return;
            }

            activeCampaignData = { id: cId, title, type, total, confirmed, pending, venue, date, url };

            // Set Form Action
            const form = document.getElementById('campaignIndexSmsForm');
            if (form) form.action = url;

            // Set Header and Labels
            const titleEl = document.getElementById('indexSmsModalCampaignTitle');
            const typeBadgeEl = document.getElementById('indexSmsModalTypeBadge');
            const totalCountEl = document.getElementById('indexSmsModalTotalCount');
            const confirmedCountEl = document.getElementById('indexSmsModalConfirmedCount');
            const pendingCountEl = document.getElementById('indexSmsModalPendingCount');
            const optAll = document.getElementById('optTargetAll');
            const optConfirmed = document.getElementById('optTargetConfirmed');
            const optPending = document.getElementById('optTargetPending');
            const targetSelect = document.getElementById('indexSmsTargetGroup');

            if (titleEl) titleEl.textContent = title;
            if (typeBadgeEl) typeBadgeEl.textContent = type.toUpperCase();
            if (totalCountEl) totalCountEl.textContent = total;
            if (confirmedCountEl) confirmedCountEl.textContent = confirmed;
            if (pendingCountEl) pendingCountEl.textContent = pending;

            if (optAll) optAll.textContent = `সকল অংশগ্রহণকারী (মোট ${total} জন)`;
            if (optConfirmed) optConfirmed.textContent = `শুধু অনুমোদিত / কনফার্মড (মোট ${confirmed} জন)`;
            if (optPending) optPending.textContent = `শুধু পেন্ডিং রিভিউ (মোট ${pending} জন)`;

            if (targetSelect) {
                targetSelect.value = 'all';
                updateIndexSmsTargetDisplay('all');
            }

            // Set default template based on campaign type
            const tplSelect = document.getElementById('indexSmsTemplateSelect');
            if (tplSelect) {
                if (type === 'scholarship') {
                    tplSelect.value = 'scholarship';
                } else if (type === 'library' || type === 'book_grant') {
                    tplSelect.value = 'notice';
                } else if (title.includes('লিটিলম্যাগ') || title.includes('সাহিত্য')) {
                    tplSelect.value = 'littlemag';
                } else {
                    tplSelect.value = 'schedule';
                }
                applyIndexSmsTemplate(tplSelect.value);
            }

            const modalEl = document.getElementById('campaignIndexSmsModal');
            if (modalEl) {
                bootstrap.Modal.getOrCreateInstance(modalEl).show();
            }
        });
    });

    window.updateIndexSmsTargetDisplay = function(val) {
        if (!activeCampaignData) return;
        const badge = document.getElementById('indexSmsLiveTargetBadge');
        if (!badge) return;

        let count = activeCampaignData.total;
        if (val === 'confirmed') count = activeCampaignData.confirmed;
        else if (val === 'pending') count = activeCampaignData.pending;

        badge.textContent = `${count} জন প্রাপক`;
    };

    window.applyIndexSmsTemplate = function(val) {
        if (!activeCampaignData) return;
        const textarea = document.getElementById('indexSmsMessageInput');
        if (!textarea) return;

        const templates = {
            'schedule': "প্রিয় {name}, {event_title}-এর সময়সূচি আপডেট: অনুষ্ঠান আগামী {date} তারিখে {venue}-এ অনুষ্ঠিত হবে। কার্ড ডাউনলোড লিংক: {card_url} — আইডিয়া প্রকাশন",
            'card': "প্রিয় {name}, {event_title}-এ আপনার নিবন্ধন অনুমোদিত হয়েছে। কার্ড ডাউনলোড লিংক: {card_url} (কার্ড নং: {reg_no}) — আইডিয়া প্রকাশন",
            'scholarship': "অভিনন্দন {name}! {event_title}-এ আপনার বৃত্তির আবেদন গৃহীত হয়েছে। বিস্তারিত ও কার্ড ডাউনলোড লিংক: {card_url} — আইডিয়া প্রকাশন",
            'littlemag': "সাহিত্য উৎসব ও লিটিলম্যাগমেলা ২০২৬: প্রিয় {name}, আপনার অংশগ্রহণ/স্টল নিশ্চিত হয়েছে। কার্ড ও নির্দেশিকা লিংক: {card_url} — আইডিয়া প্রকাশন",
            'notice': "জরুরি বিজ্ঞপ্তি: প্রিয় {name}, {event_title}-এ নির্ধারিত সময়ে উপস্থিত থাকার জন্য অনুরোধ করা হচ্ছে। বিস্তারিত: {card_url} — আইডিয়া প্রকাশন",
            'custom': ""
        };

        textarea.value = templates[val] !== undefined ? templates[val] : templates['schedule'];
        handleIndexSmsMessageInput();
    };

    window.insertIndexSmsTag = function(tag) {
        const textarea = document.getElementById('indexSmsMessageInput');
        if (!textarea) return;

        const start = textarea.selectionStart || 0;
        const end = textarea.selectionEnd || 0;
        const text = textarea.value;

        textarea.value = text.substring(0, start) + tag + text.substring(end);
        textarea.selectionStart = textarea.selectionEnd = start + tag.length;
        textarea.focus();
        handleIndexSmsMessageInput();
    };

    window.handleIndexSmsMessageInput = function() {
        const textarea = document.getElementById('indexSmsMessageInput');
        if (!textarea) return;

        const raw = textarea.value || '';
        const charCountEl = document.getElementById('indexSmsCharCount');
        const badgeEl = document.getElementById('indexSmsMsgCountBadge');
        const previewEl = document.getElementById('indexSmsLivePreviewBox');

        const charLen = raw.length;
        if (charCountEl) charCountEl.textContent = charLen;

        const isUnicode = /[^\u0000-\u007F]/.test(raw);
        let smsCount = 1;

        if (isUnicode) {
            smsCount = charLen <= 70 ? 1 : Math.ceil(charLen / 67);
            if (badgeEl) badgeEl.textContent = smsCount + ' SMS (বাংলা)';
        } else {
            smsCount = charLen <= 160 ? 1 : Math.ceil(charLen / 153);
            if (badgeEl) badgeEl.textContent = smsCount + ' SMS (English)';
        }

        if (previewEl) {
            const sampleName = "শাকিল মাসুদ";
            const sampleRegNo = "REG-2026-001";
            const sampleCardUrl = window.location.origin + "/card/REG-2026-001";
            const sampleTitle = activeCampaignData?.title || "ইভেন্টের নাম";
            const sampleVenue = activeCampaignData?.venue || "রংপুর";
            const sampleDate = activeCampaignData?.date || "২০ অক্টোবর ২০২৬";

            let previewText = raw
                .split('{name}').join(sampleName)
                .split('{reg_no}').join(sampleRegNo)
                .split('{card_url}').join(sampleCardUrl)
                .split('{event_title}').join(sampleTitle)
                .split('{venue}').join(sampleVenue)
                .split('{date}').join(sampleDate);

            previewEl.textContent = previewText || '(মেসেজ বক্স খালি)';
        }
    };

    window.handleIndexBroadcastSmsSubmit = async function(e) {
        e.preventDefault();
        const form = document.getElementById('campaignIndexSmsForm');
        const btn = document.getElementById('btnIndexSubmitSms');
        const btnText = document.getElementById('btnIndexSubmitSmsText');
        const progressBox = document.getElementById('indexSmsProgressBox');

        const msg = document.getElementById('indexSmsMessageInput')?.value?.trim();
        if (!msg) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'মেসেজ বক্স খালি!',
                    text: 'অনুগ্রহ করে বার্তা লিখুন বা একটি রেডি টেমপ্লেট বেছে নিন।',
                    confirmButtonColor: '#059669',
                });
            } else {
                alert('অনুগ্রহ করে মেসেজ কন্টেন্ট লিখুন।');
            }
            return;
        }

        const campTitle = activeCampaignData?.title || 'এই ক্যাম্পেইন';
        const targetGroup = document.getElementById('indexSmsTargetGroup')?.value;
        const targetNames = {
            'all': 'সকল নিবন্ধিত অংশগ্রহণকারী',
            'confirmed': 'শুধুমাত্র অনুমোদিত / কনফার্মড অংশগ্রহণকারী',
            'pending': 'শুধুমাত্র পেন্ডিং রিভিউ অংশগ্রহণকারী'
        };
        const targetLabel = targetNames[targetGroup] || 'নির্বাচিত অংশগ্রহণকারী';

        if (typeof Swal !== 'undefined') {
            const confirmRes = await Swal.fire({
                title: 'এক ক্লিকে এসএমএস পাঠাবেন?',
                html: `<strong>${campTitle}</strong>-এর <strong>${targetLabel}</strong>-দের কাছে এই বার্তা পাঠানো হবে।<br><small class="text-muted">মেসেজ গেটওয়ে দিয়ে প্রতিটি নম্বরে স্বয়ংক্রিয়ভাবে প্রেরিত হবে।</small>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#059669',
                cancelButtonColor: '#64748b',
                confirmButtonText: '<i class="fa-solid fa-paper-plane me-1"></i> হ্যাঁ, এসএমএস পাঠান',
                cancelButtonText: 'বাতিল'
            });
            if (!confirmRes.isConfirmed) return;
        } else if (!confirm(`আপনি কি নিশ্চিত যে "${campTitle}"-এর অংশগ্রহণকারীদের এসএমএস পাঠাতে চান?`)) {
            return;
        }

        if (btn) btn.disabled = true;
        if (btnText) btnText.textContent = 'প্রেরণ করা হচ্ছে...';
        if (progressBox) progressBox.style.display = 'block';

        const formData = new FormData(form);

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                const modalEl = document.getElementById('campaignIndexSmsModal');
                if (modalEl) {
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) modal.hide();
                }

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'বার্তা প্রেরিত হয়েছে!',
                        html: `<div class="text-start p-3 bg-light rounded-3 font-monospace small">` +
                              `<div class="mb-2 text-success fw-bold"><i class="fa-solid fa-circle-check me-1"></i> ${data.message}</div>` +
                              `&bull; ক্যাম্পেইন: <strong>${campTitle}</strong><br>` +
                              `&bull; সফল ডেলিভারি: <strong class="text-success">${data.sent_count ?? 0} জন</strong><br>` +
                              (data.failed_count > 0 ? `&bull; নম্বর বাদ/ব্যর্থ: <strong class="text-danger">${data.failed_count} টি</strong><br>` : '') +
                              `&bull; মোট টার্গেট: <strong>${data.total_target ?? 0} জন</strong>` +
                              `</div>`,
                        confirmButtonColor: '#059669',
                        confirmButtonText: 'ঠিক আছে'
                    });
                } else {
                    alert(data.message);
                }
            } else {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'বার্তা প্রেরণে ব্যর্থ',
                        text: data.message || 'মেসেজ প্রেরণে ব্যর্থ হয়েছে।',
                        confirmButtonColor: '#dc2626'
                    });
                } else {
                    alert(data.message || 'মেসেজ প্রেরণে ব্যর্থ হয়েছে।');
                }
            }
        } catch (err) {
            console.error('Broadcast SMS Error:', err);
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'সার্ভার যোগাযোগ সমস্যা',
                    text: 'সার্ভারের সাথে যোগাযোগে বিঘ্ন ঘটেছে। আবার চেষ্টা করুন।',
                    confirmButtonColor: '#dc2626'
                });
            } else {
                alert('সার্ভার যোগাযোগে সমস্যা হয়েছে। আবার চেষ্টা করুন।');
            }
        } finally {
            if (btn) btn.disabled = false;
            if (btnText) btnText.textContent = 'একবারে আপডেট পাঠান';
            if (progressBox) progressBox.style.display = 'none';
        }
    };
});
</script>
@endsection
