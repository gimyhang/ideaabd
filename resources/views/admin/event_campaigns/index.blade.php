@extends('layouts.admin')

@section('title', 'Campaigns & Events — ideaabd')

@section('content')
<div class="container-fluid px-3 px-md-4 py-4">

    {{-- Header --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                    <li class="breadcrumb-item active fw-semibold text-primary" aria-current="page">Campaigns</li>
                </ol>
            </nav>
            <h1 class="h3 fw-bold mb-0 text-gray-900 d-flex align-items-center gap-2">
                <i class="fa-solid fa-bullhorn text-primary fs-4"></i> Campaigns & Events
            </h1>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.event-campaigns.create') }}" class="btn btn-primary rounded-pill px-3.5 py-2 fw-semibold shadow-sm d-inline-flex align-items-center gap-2">
                <i class="fa-solid fa-plus"></i> New Campaign
            </a>
        </div>
    </div>

    {{-- Flash Notifications --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-xs border-0 p-3 mb-4" role="alert">
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
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Total</span>
                        <h3 class="fw-bold mb-0 mt-1 text-dark">{{ number_format($totalCampaigns) }}</h3>
                    </div>
                    <div class="rounded-3 bg-primary-subtle text-primary p-2.5">
                        <i class="fa-solid fa-bullhorn fs-5"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-xs rounded-4 p-3 bg-white h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Active</span>
                        <h3 class="fw-bold mb-0 mt-1 text-success">{{ number_format($activeCampaigns) }}</h3>
                    </div>
                    <div class="rounded-3 bg-success-subtle text-success p-2.5">
                        <i class="fa-solid fa-circle-check fs-5"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-xs rounded-4 p-3 bg-white h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Registrations</span>
                        <h3 class="fw-bold mb-0 mt-1 text-info">{{ number_format($totalRegistrations) }}</h3>
                    </div>
                    <div class="rounded-3 bg-info-subtle text-info p-2.5">
                        <i class="fa-solid fa-users fs-5"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-xs rounded-4 p-3 bg-white h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Collected</span>
                        <h3 class="fw-bold mb-0 mt-1 text-warning">৳{{ number_format($totalCollected, 2) }}</h3>
                    </div>
                    <div class="rounded-3 bg-warning-subtle text-warning p-2.5">
                        <i class="fa-solid fa-hand-holding-dollar fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter & Search Form --}}
    <div class="card border-0 shadow-xs rounded-4 mb-4 bg-white">
        <div class="card-body p-3">
            <form action="{{ route('admin.event-campaigns.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-12 col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                        <input type="text" name="search" class="form-control bg-light border-start-0 ps-0" placeholder="Search title, slug, badge..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <select name="type" class="form-select bg-light">
                        <option value="">All Types</option>
                        <option value="event" {{ request('type') == 'event' ? 'selected' : '' }}>Event</option>
                        <option value="scholarship" {{ request('type') == 'scholarship' ? 'selected' : '' }}>Scholarship</option>
                        <option value="donation" {{ request('type') == 'donation' ? 'selected' : '' }}>Donation</option>
                        <option value="competition" {{ request('type') == 'competition' ? 'selected' : '' }}>Competition</option>
                        <option value="workshop" {{ request('type') == 'workshop' ? 'selected' : '' }}>Workshop</option>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <select name="status" class="form-select bg-light">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                    </select>
                </div>
                <div class="col-12 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary rounded-pill px-3 fw-semibold flex-grow-1">Filter</button>
                    @if(request()->hasAny(['search', 'type', 'status']))
                        <a href="{{ route('admin.event-campaigns.index') }}" class="btn btn-light rounded-pill px-3 border" title="Reset"><i class="fa-solid fa-rotate-left"></i></a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Campaigns Table --}}
    <div class="card border-0 shadow-xs rounded-4 bg-white overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 14px;">
                <thead class="table-light text-secondary text-uppercase" style="font-size: 12px; letter-spacing: 0.5px;">
                    <tr>
                        <th class="ps-3 py-3" style="width: 50px;">#</th>
                        <th class="py-3">Campaign</th>
                        <th class="py-3">Type</th>
                        <th class="py-3">Slug / URL</th>
                        <th class="py-3 text-center">Registrants</th>
                        <th class="py-3">Fee / Goal</th>
                        <th class="py-3">Status</th>
                        <th class="pe-3 py-3 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($campaigns as $campaign)
                        <tr id="row-campaign-{{ $campaign->id }}">
                            <td class="ps-3 fw-bold text-muted">{{ $campaign->id }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2.5">
                                    <div class="rounded-3 bg-light border p-1 flex-shrink-0 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; border-left: 4px solid {{ $campaign->theme_color ?: '#0284c7' }} !important;">
                                        @if($campaign->banner_image)
                                            <img src="{{ asset('storage/' . $campaign->banner_image) }}" alt="" class="rounded-2 img-fluid" style="max-height: 38px; object-fit: cover;">
                                        @else
                                            <i class="fa-solid fa-bullhorn text-secondary fs-5"></i>
                                        @endif
                                    </div>
                                    <div>
                                        <a href="{{ route('admin.event-campaigns.show', $campaign->id) }}" class="fw-bold text-dark text-decoration-none hover-primary">
                                            {{ $campaign->title }}
                                        </a>
                                        @if($campaign->badge_text)
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-0.5 ms-1" style="font-size: 10px;">
                                                {{ $campaign->badge_text }}
                                            </span>
                                        @endif
                                        <div class="text-muted small" style="font-size: 11.5px;">
                                            {{ $campaign->created_at->format('d M, Y') }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border px-2 py-1 text-capitalize">
                                    {{ $campaign->type }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-1.5">
                                    <code class="px-2 py-1 bg-light text-primary rounded-2 fw-bold font-monospace" style="font-size: 12px;">/{{ $campaign->slug }}</code>
                                    <button type="button" class="btn btn-sm btn-light border rounded-pill px-2 py-0.5 text-muted copy-slug-btn" data-url="{{ $campaign->public_url }}" title="Copy URL">
                                        <i class="fa-regular fa-copy"></i>
                                    </button>
                                    <a href="{{ $campaign->public_url }}" target="_blank" class="btn btn-sm btn-link text-decoration-none p-0 text-muted" title="Open Link">
                                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                    </a>
                                </div>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('admin.event-campaigns.show', $campaign->id) }}" class="badge bg-info-subtle text-info border border-info-subtle px-2.5 py-1 text-decoration-none fs-6 fw-bold">
                                    {{ $campaign->registrations_count }}
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
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">Free</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-1.5">
                                    <button type="button" 
                                            class="btn btn-sm rounded-pill px-2.5 py-0.5 fw-semibold toggle-status-btn {{ $campaign->is_active ? 'btn-success' : 'btn-outline-secondary' }}"
                                            data-id="{{ $campaign->id }}"
                                            data-url="{{ route('admin.event-campaigns.toggle-status', $campaign->id) }}"
                                            title="Click to toggle status">
                                        {{ $campaign->is_active ? 'Active' : 'Inactive' }}
                                    </button>
                                    @if($campaign->isExpired())
                                        <span class="badge bg-danger-subtle text-danger border" title="Registration deadline reached">Expired</span>
                                    @elseif($campaign->isFull())
                                        <span class="badge bg-warning-subtle text-warning border" title="Capacity full">Full</span>
                                    @endif
                                </div>
                            </td>
                            <td class="pe-3 text-end">
                                <div class="d-inline-flex gap-1">
                                    <button type="button" class="btn btn-sm btn-outline-dark rounded-pill px-2 py-1" data-bs-toggle="modal" data-bs-target="#editTitleModal{{ $campaign->id }}" title="Change Title">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                    <a href="{{ route('admin.event-campaigns.show', $campaign->id) }}" class="btn btn-sm btn-outline-primary rounded-pill px-2.5 py-1" title="Registrants">
                                        <i class="fa-solid fa-users me-1"></i> List
                                    </a>
                                    <a href="{{ route('admin.event-campaigns.edit', $campaign->id) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-2 py-1" title="Edit">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <form action="{{ route('admin.event-campaigns.clone', $campaign->id) }}" method="POST" class="d-inline m-0">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-info rounded-pill px-2 py-1" title="Duplicate / Clone">
                                            <i class="fa-solid fa-copy"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.event-campaigns.destroy', $campaign->id) }}" method="POST" class="d-inline m-0" onsubmit="return confirm('Delete this campaign? Note: All registered customers remain safe in users table.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-2 py-1" title="Delete">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>

                                {{-- Quick Edit Title Modal --}}
                                <div class="modal fade" id="editTitleModal{{ $campaign->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered text-start">
                                        <div class="modal-content rounded-4 border-0 shadow">
                                            <form action="{{ route('admin.event-campaigns.update-title', $campaign->id) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <div class="modal-header border-bottom py-3">
                                                    <h6 class="modal-title fw-bold">Change Title: #{{ $campaign->id }}</h6>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body p-3">
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-semibold text-dark">Campaign Title <span class="text-danger">*</span></label>
                                                        <input type="text" name="title" class="form-control" value="{{ $campaign->title }}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-semibold text-dark">Badge / Sub-heading</label>
                                                        <input type="text" name="badge_text" class="form-control" value="{{ $campaign->badge_text }}">
                                                    </div>
                                                    <div class="mb-2">
                                                        <label class="form-label small fw-semibold text-dark">URL Slug</label>
                                                        <div class="input-group">
                                                            <span class="input-group-text bg-light text-muted fw-monospace small">{{ url('/') }}/</span>
                                                            <input type="text" name="slug" class="form-control font-monospace" value="{{ $campaign->slug }}" required>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-top py-2">
                                                    <button type="button" class="btn btn-light rounded-pill btn-sm px-3" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-primary rounded-pill btn-sm px-4 fw-semibold">Save Title</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <div class="mb-2"><i class="fa-solid fa-bullhorn fs-2 text-secondary"></i></div>
                                <h6 class="fw-bold">No Campaigns</h6>
                                <p class="small mb-3">Create custom URL registration forms like /rsutshab or /joyeeshikkhabritti.</p>
                                <a href="{{ route('admin.event-campaigns.create') }}" class="btn btn-primary btn-sm rounded-pill px-3">
                                    <i class="fa-solid fa-plus me-1"></i> New Campaign
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
        toastMsg.textContent = msg;
        toast.show();
    }

    // Quick Copy Public URL
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

    // AJAX Quick Toggle Status
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
                        button.className = 'btn btn-sm rounded-pill px-2.5 py-0.5 fw-semibold toggle-status-btn btn-success';
                        button.textContent = 'Active';
                    } else {
                        button.className = 'btn btn-sm rounded-pill px-2.5 py-0.5 fw-semibold toggle-status-btn btn-outline-secondary';
                        button.textContent = 'Inactive';
                    }
                    showToast(data.message || 'Status updated');
                } else {
                    showToast('Failed to update status');
                }
            })
            .catch(err => {
                button.disabled = false;
                console.error(err);
                showToast('Network error updating status');
            });
        });
    });
});
</script>
@endsection
