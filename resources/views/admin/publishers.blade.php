@extends('layouts.admin')

@section('title', 'প্রকাশক ও প্রকাশনী ব্যবস্থাপনা')
@section('heading', 'প্রকাশক ডিরেক্টরি, বই ক্যাটালগ ও হিসাব লেজার')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">প্রকাশক তালিকা</li>
@endsection

@section('actions')
    <div class="d-flex align-items-center gap-2">
        <button type="button" class="btn btn-outline-success btn-sm rounded-pill px-3 shadow-xs" onclick="exportPublishersToCSV()" title="CSV ফাইলে এক্সপোর্ট করুন">
            <i class="fas fa-file-csv me-1"></i> এক্সপোর্ট (CSV)
        </button>
        <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-xs" onclick="window.print()" title="তালিকা প্রিন্ট করুন">
            <i class="fas fa-print me-1"></i> প্রিন্ট
        </button>
        <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 fw-bold shadow-xs" onclick="openAddPublisherModal()">
            <i class="fas fa-plus-circle me-1"></i> নতুন প্রকাশক যোগ করুন
        </button>
        <a href="{{ route('publishers.index') }}" target="_blank" rel="noopener" class="btn btn-outline-dark btn-sm rounded-pill px-3 shadow-xs">
            <i class="fas fa-arrow-up-right-from-square me-1"></i> শপে দেখুন
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
    {{-- 1. FINANCIAL & INVENTORY KPI METRICS                                      --}}
    {{-- ========================================================================= --}}
    <div class="row g-2">
        <div class="col-6 col-md-2-4 col-lg">
            <a href="{{ route('admin.publishers') }}" class="text-decoration-none">
                <div class="adm-card p-3 d-flex align-items-center justify-content-between h-100 {{ !request()->hasAny(['is_active', 'has_due']) ? 'border-primary border-2' : '' }}">
                    <div>
                        <small class="text-muted d-block font-sans">সর্বমোট প্রকাশক</small>
                        <h4 class="fw-bold text-dark mb-0">@bn($stats['total'] ?? 0) <small class="fs-6 text-muted">টি</small></h4>
                    </div>
                    <span class="p-2 bg-primary-subtle text-primary rounded-circle fs-5"><i class="fas fa-building"></i></span>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-2-4 col-lg">
            <a href="{{ route('admin.publishers', array_merge(request()->except(['is_active', 'page']), ['is_active' => '1'])) }}" class="text-decoration-none">
                <div class="adm-card p-3 d-flex align-items-center justify-content-between h-100 {{ request('is_active') === '1' ? 'border-success border-2' : '' }}">
                    <div>
                        <small class="text-muted d-block font-sans">সক্রিয় প্রকাশনী</small>
                        <h4 class="fw-bold text-success mb-0">@bn($stats['active'] ?? 0) <small class="fs-6 text-muted">টি</small></h4>
                    </div>
                    <span class="p-2 bg-success-subtle text-success rounded-circle fs-5"><i class="fas fa-circle-check"></i></span>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-2-4 col-lg">
            <div class="adm-card p-3 d-flex align-items-center justify-content-between h-100">
                <div>
                    <small class="text-muted d-block font-sans">মোট ক্যাটালগ বই</small>
                    <h4 class="fw-bold text-dark mb-0">@bn($stats['total_books'] ?? 0) <small class="fs-6 text-muted">টি</small></h4>
                </div>
                <span class="p-2 bg-info-subtle text-info rounded-circle fs-5"><i class="fas fa-book-open"></i></span>
            </div>
        </div>
        <div class="col-6 col-md-2-4 col-lg">
            <div class="adm-card p-3 d-flex align-items-center justify-content-between h-100">
                <div>
                    <small class="text-muted d-block font-sans">মোট কেনাকাটা (চালান)</small>
                    <h4 class="fw-bold text-primary mb-0 font-monospace">৳@bn(number_format($stats['total_purchase_sum'] ?? 0, 0))</h4>
                </div>
                <span class="p-2 bg-primary-subtle text-primary rounded-circle fs-5"><i class="fas fa-cart-flatbed"></i></span>
            </div>
        </div>
        <div class="col-12 col-md-2-4 col-lg">
            <a href="{{ route('admin.publishers', array_merge(request()->except(['has_due', 'page']), ['has_due' => '1'])) }}" class="text-decoration-none">
                <div class="adm-card p-3 d-flex align-items-center justify-content-between h-100 {{ request('has_due') === '1' ? 'border-danger border-2 bg-danger-subtle bg-opacity-25' : '' }}">
                    <div>
                        <small class="text-muted d-block font-sans">মোট প্রদেয় বকেয়া (Due)</small>
                        <h4 class="fw-bold text-danger mb-0 font-monospace">৳@bn(number_format($stats['total_due_sum'] ?? 0, 0))</h4>
                    </div>
                    <span class="p-2 bg-danger-subtle text-danger rounded-circle fs-5"><i class="fas fa-hand-holding-dollar"></i></span>
                </div>
            </a>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- 2. DYNAMIC SEARCH ENGINE & MULTI-FILTER TOOLBAR                           --}}
    {{-- ========================================================================= --}}
    <div class="adm-card p-3 shadow-sm border-0">
        <form action="{{ route('admin.publishers') }}" method="GET" id="publishersFilterForm" class="row g-2 align-items-center">
            
            {{-- Search Bar --}}
            <div class="col-12 col-md-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-search"></i></span>
                    <input type="text" name="search" id="publisherSearchInput" value="{{ request('search') }}" 
                           class="form-control border-start-0 border-end-0 ps-0" 
                           placeholder="প্রকাশনীর নাম, ফোন, ইমেইল, ঠিকানা, স্লাগ..." autocomplete="off">
                    @if(request('search'))
                        <a href="{{ route('admin.publishers', request()->except('search')) }}" class="input-group-text bg-white border-start-0 text-muted hover-danger" title="সার্চ মুছুন">
                            <i class="fas fa-times"></i>
                        </a>
                    @endif
                    <button type="submit" class="btn btn-primary px-3 fw-semibold">খুঁজুন</button>
                </div>
            </div>

            {{-- Status Filter --}}
            <div class="col-6 col-md-2">
                <select name="is_active" class="form-select form-select-sm" onchange="submitPubFilter()">
                    <option value="">— সকল স্ট্যাটাস —</option>
                    <option value="1" @selected(request('is_active') === '1')>🟢 সক্রিয় প্রকাশক</option>
                    <option value="0" @selected(request('is_active') === '0')>🔴 নিষ্ক্রিয় প্রকাশক</option>
                </select>
            </div>

            {{-- Due Filter --}}
            <div class="col-6 col-md-2">
                <select name="has_due" class="form-select form-select-sm" onchange="submitPubFilter()">
                    <option value="">— সকল হিসাব —</option>
                    <option value="1" @selected(request('has_due') === '1')>🔴 বকেয়া পাওনাদার রয়েছে</option>
                </select>
            </div>

            {{-- Sort By --}}
            <div class="col-6 col-md-2">
                <select name="sort" class="form-select form-select-sm" onchange="submitPubFilter()">
                    <option value="latest" @selected(request('sort') === 'latest' || !request('sort'))>নতুন প্রকাশক প্রথমে</option>
                    <option value="oldest" @selected(request('sort') === 'oldest')>পুরাতন প্রথমে</option>
                    <option value="name_asc" @selected(request('sort') === 'name_asc')>নাম: ক থেকে ক্ষ (A-Z)</option>
                    <option value="name_desc" @selected(request('sort') === 'name_desc')>নাম: Z থেকে A</option>
                    <option value="books_desc" @selected(request('sort') === 'books_desc')>সর্বোচ্চ বই সংখ্যা</option>
                    <option value="purchase_desc" @selected(request('sort') === 'purchase_desc')>সর্বোচ্চ কেনাকাটা</option>
                    <option value="due_desc" @selected(request('sort') === 'due_desc')>সর্বোচ্চ বকেয়া পাওনা</option>
                </select>
            </div>

            {{-- Per Page & Reset --}}
            <div class="col-6 col-md-2 d-flex gap-1">
                <select name="per_page" class="form-select form-select-sm flex-fill" onchange="submitPubFilter()">
                    <option value="20" @selected(request('per_page') == 20 || !request('per_page'))>২০ টি</option>
                    <option value="50" @selected(request('per_page') == 50)>৫০ টি</option>
                    <option value="100" @selected(request('per_page') == 100)>১০০ টি</option>
                </select>
                <a href="{{ route('admin.publishers') }}" class="btn btn-sm btn-outline-secondary px-2.5" title="সকল ফিল্টার রিসেট">
                    <i class="fas fa-rotate-left"></i>
                </a>
            </div>

        </form>

        {{-- Active Filter Chips --}}
        @php
            $hasActiveFilters = request()->hasAny(['search', 'is_active', 'has_due']) || (request('sort') && request('sort') !== 'latest');
        @endphp

        @if($hasActiveFilters)
            <div class="d-flex flex-wrap align-items-center gap-1.5 pt-2.5 mt-2 border-top">
                <span class="small fw-semibold text-muted me-1"><i class="fas fa-sliders me-1"></i>সক্রিয় ফিল্টারসমূহ:</span>
                
                @if(request('search'))
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill py-1 px-2.5 d-inline-flex align-items-center gap-1">
                        সার্চ: "{{ request('search') }}"
                        <a href="{{ route('admin.publishers', request()->except('search')) }}" class="text-primary text-decoration-none"><i class="fas fa-times-circle"></i></a>
                    </span>
                @endif

                @if(request('is_active') !== null && request('is_active') !== '')
                    <span class="badge bg-light text-dark border rounded-pill py-1 px-2.5 d-inline-flex align-items-center gap-1">
                        অবস্থা: {{ request('is_active') === '1' ? 'সক্রিয়' : 'নিষ্ক্রিয়' }}
                        <a href="{{ route('admin.publishers', request()->except('is_active')) }}" class="text-dark text-decoration-none"><i class="fas fa-times-circle"></i></a>
                    </span>
                @endif

                @if(request('has_due') === '1')
                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill py-1 px-2.5 d-inline-flex align-items-center gap-1">
                        শুধুমাত্র বকেয়া পাওনাদার
                        <a href="{{ route('admin.publishers', request()->except('has_due')) }}" class="text-danger text-decoration-none"><i class="fas fa-times-circle"></i></a>
                    </span>
                @endif

                <a href="{{ route('admin.publishers') }}" class="btn btn-link btn-xs text-danger text-decoration-none fw-bold ms-auto">
                    <i class="fas fa-trash-can me-1"></i> সকল ফিল্টার মুছুন
                </a>
            </div>
        @endif

    </div>

    {{-- ========================================================================= --}}
    {{-- 3. INTERACTIVE PUBLISHER DIRECTORY TABLE                                  --}}
    {{-- ========================================================================= --}}
    <div class="adm-card p-0 overflow-hidden shadow-sm border-0 rounded-4 bg-white">
        <div class="table-responsive">
            <table class="table adm-table align-middle mb-0" id="adminPublishersTable">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" style="width: 45px;">#</th>
                        <th style="min-width: 240px;">প্রকাশক ও লোগো</th>
                        <th style="min-width: 200px;">যোগাযোগের ঠিকানা</th>
                        <th class="text-center" style="min-width: 110px;">ক্যাটালগ বই</th>
                        <th class="text-end" style="min-width: 130px;">মোট কেনাকাটা</th>
                        <th class="text-end" style="min-width: 140px;">বকেয়া পাওনা (Due)</th>
                        <th class="text-center" style="min-width: 90px;">লাইভ স্ট্যাটাস</th>
                        <th class="text-end pe-3" style="min-width: 160px;">অ্যাকশনস</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($publishers as $index => $publisher)
                        @php
                            $logo = $publisher->logo;
                            $logoUrl = $logo 
                                ? (str_starts_with($logo, 'http') ? $logo : (str_starts_with($logo, 'storage/') ? asset($logo) : asset('storage/' . ltrim($logo, '/'))))
                                : 'https://placehold.co/100x100/4f46e5/ffffff?text=' . urlencode(mb_substr($publisher->name, 0, 1));
                            
                            $purchaseSum = (float) ($publisher->total_purchase_sum ?? 0);
                            $dueSum = (float) ($publisher->total_due_sum ?? 0);
                        @endphp
                        <tr id="publisherRow_{{ $publisher->id }}" class="pub-table-row">
                            <td class="ps-3 text-muted small">
                                @bn(($publishers->currentPage() - 1) * $publishers->perPage() + $index + 1)
                            </td>

                            {{-- Name & Logo --}}
                            <td>
                                <div class="d-flex align-items-center gap-2.5">
                                    <div class="position-relative flex-shrink-0">
                                        <a href="{{ route('admin.publishers.show', $publisher->id) }}">
                                            <img src="{{ $logoUrl }}" alt="{{ $publisher->name }}" id="pubLogoImg_{{ $publisher->id }}"
                                                 class="rounded-circle border shadow-xs" style="width: 44px; height: 44px; object-fit: cover;">
                                        </a>
                                    </div>
                                    <div class="text-truncate" style="max-width: 220px;">
                                        <a href="{{ route('admin.publishers.show', $publisher->id) }}" 
                                           class="fw-bold text-dark text-decoration-none hover-primary d-block text-truncate mb-0.5" 
                                           id="pubNameDisplay_{{ $publisher->id }}" title="{{ $publisher->name }}">
                                            {{ $publisher->name }}
                                        </a>
                                        <div class="small text-muted font-monospace" style="font-size: 11px;">
                                            {{ $publisher->slug ?? '—' }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            {{-- Contact Details --}}
                            <td>
                                <div class="small">
                                    @if($publisher->phone)
                                        <div class="text-dark"><i class="fas fa-phone me-1 text-primary" style="font-size: 10px;"></i>@bn($publisher->phone)</div>
                                    @endif
                                    @if($publisher->email)
                                        <div class="text-muted text-truncate" style="max-width: 200px;"><i class="fas fa-envelope me-1 text-secondary" style="font-size: 10px;"></i>{{ $publisher->email }}</div>
                                    @endif
                                    @if($publisher->address)
                                        <div class="text-muted text-truncate" style="max-width: 200px; font-size: 11px;"><i class="fas fa-location-dot me-1 text-danger-subtle" style="font-size: 10px;"></i>{{ $publisher->address }}</div>
                                    @endif
                                    @if(!$publisher->phone && !$publisher->email && !$publisher->address)
                                        <span class="text-muted">—</span>
                                    @endif
                                </div>
                            </td>

                            {{-- Books Count & Catalog Value --}}
                            <td class="text-center">
                                <a href="{{ route('admin.publishers.show', $publisher->id) }}" 
                                   class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1 text-decoration-none fw-bold hover-primary"
                                   title="এই প্রকাশনীর বই ও ক্রয় আদেশ পরিচালনা করুন">
                                    <i class="fas fa-book me-1"></i>@bn($publisher->books_count ?? 0) টি বই
                                </a>
                                @if(($publisher->total_catalog_price ?? 0) > 0)
                                    <div class="small text-muted font-monospace mt-0.5" style="font-size: 10.5px;" title="ক্যাটালগ বইগুলোর মোট মূল্য">
                                        মূল্য: ৳@bn(number_format($publisher->total_catalog_price, 0))
                                    </div>
                                @endif
                            </td>

                            {{-- Total Purchases --}}
                            <td class="text-end">
                                <span class="fw-bold text-dark font-monospace">৳@bn(number_format($purchaseSum, 0))</span>
                            </td>

                            {{-- Due Payable Balance --}}
                            <td class="text-end">
                                @if($dueSum > 0)
                                    <div class="d-inline-flex align-items-center gap-1.5 justify-content-end">
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-1 font-monospace fw-bold">
                                            ৳@bn(number_format($dueSum, 0))
                                        </span>
                                        <button type="button" class="btn btn-xs btn-outline-danger rounded-circle" 
                                                style="width: 24px; height: 24px; padding: 0;" 
                                                onclick="openQuickPaymentModal({{ $publisher->id }}, '{{ addslashes($publisher->name) }}', {{ $dueSum }})"
                                                title="পেমেন্ট পরিশোধ রেকর্ড করুন">
                                            <i class="fas fa-money-bill-wave" style="font-size: 9px;"></i>
                                        </button>
                                    </div>
                                @else
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0.5" style="font-size: 11px;">
                                        <i class="fas fa-check me-0.5"></i>পরিশোধিত
                                    </span>
                                @endif
                            </td>

                            {{-- Status Switch (Instant Ajax) --}}
                            <td class="text-center">
                                <div class="form-check form-switch d-inline-block mb-0">
                                    <input class="form-check-input cursor-pointer" type="checkbox" role="switch" 
                                           id="statusSwitch_{{ $publisher->id }}" 
                                           @checked($publisher->is_active) 
                                           onchange="togglePublisherActive({{ $publisher->id }}, this)">
                                </div>
                            </td>

                            {{-- Actions --}}
                            <td class="text-end pe-3">
                                <div class="d-inline-flex align-items-center gap-1">
                                    <a href="{{ route('admin.publishers.show', $publisher->id) }}" 
                                       class="btn btn-sm btn-primary rounded-pill px-2.5 py-0.5 fw-semibold" title="বই তালিকা ও ক্রয় আদেশ (PO)">
                                        <i class="fas fa-file-invoice-dollar me-1"></i> বই ও PO
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-2 py-0.5" 
                                            onclick="openEditPublisherModal({{ $publisher->id }})" title="প্রকাশক তথ্য সম্পাদনা">
                                        <i class="fas fa-pen-to-square"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-2 py-0.5" 
                                            onclick="openQuickPaymentModal({{ $publisher->id }}, '{{ addslashes($publisher->name) }}', {{ $dueSum }})" title="পেমেন্ট ভাউচার এন্ট্রি">
                                        <i class="fas fa-hand-holding-dollar"></i>
                                    </button>
                                    <a href="{{ route('publishers.show', $publisher->slug ?? $publisher->id) }}" target="_blank" 
                                       class="btn btn-sm btn-outline-secondary rounded-pill px-2 py-0.5" title="সাইটে দেখুন">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <form action="{{ route('admin.content.destroy', ['type' => 'publishers', 'id' => $publisher->id]) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('আপনি কি নিশ্চিত যে এই প্রকাশকটি মুছে ফেলতে চান?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-2 py-0.5" title="মুছে ফেলুন">
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
                                        <i class="fas fa-building fs-1 text-muted"></i>
                                    </div>
                                    <h5 class="fw-bold text-dark mb-1">কোনো প্রকাশক পাওয়া যায়নি</h5>
                                    <p class="text-muted small mb-3">নতুন প্রকাশক যোগ করুন অথবা আপনার সার্চ ফিল্টার পরিবর্তন করুন।</p>
                                    <button type="button" class="btn btn-sm btn-primary rounded-pill px-3" onclick="openAddPublisherModal()">
                                        <i class="fas fa-plus me-1"></i> নতুন প্রকাশক যোগ করুন
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($publishers->hasPages())
            <div class="p-3 border-top d-flex flex-column flex-md-row align-items-center justify-content-between gap-2 bg-light bg-opacity-50">
                <div class="small text-muted">
                    মোট @bn($publishers->total()) টির মধ্যে @bn($publishers->firstItem()) - @bn($publishers->lastItem()) দেখানো হচ্ছে
                </div>
                <div>{{ $publishers->links() }}</div>
            </div>
        @endif
    </div>

</div>

{{-- ========================================================================= --}}
{{-- MODAL 1: QUICK ADD NEW PUBLISHER                                          --}}
{{-- ========================================================================= --}}
<div class="modal fade" id="quickAddPublisherModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title fw-bold text-white mb-0">
                    <i class="fas fa-building-circle-arrow-right me-1.5"></i> নতুন প্রকাশক / প্রকাশনী যুক্ত করুন
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="quickAddPublisherForm" onsubmit="handleAddPublisherSubmit(event)" enctype="multipart/form-data">
                <div class="modal-body p-4">
                    <div id="addPubAlertBox"></div>

                    {{-- Logo Upload with Preview --}}
                    <div class="text-center mb-3">
                        <div class="position-relative d-inline-block mb-2">
                            <img src="https://placehold.co/90x90/4f46e5/ffffff?text=Logo" id="addPubLogoPreview" class="rounded-circle border shadow-sm" style="width: 80px; height: 80px; object-fit: cover;">
                        </div>
                        <div>
                            <label for="addPubLogoInput" class="btn btn-xs btn-outline-primary rounded-pill px-3 cursor-pointer">
                                <i class="fas fa-camera me-1"></i> লোগো আপলোড
                            </label>
                            <input type="file" id="addPubLogoInput" name="logo_file" accept="image/*" class="d-none" onchange="previewAddPubLogo(this)">
                        </div>
                    </div>

                    <div class="row g-2.5">
                        <div class="col-12">
                            <label class="form-label small fw-bold text-dark">প্রকাশনীর নাম <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control form-control-sm fw-bold" required placeholder="যেমন: ইত্যাদি গ্রন্থ প্রকাশ">
                        </div>
                        <div class="col-6">
                            <label class="form-label small text-muted">ফোন নম্বর</label>
                            <input type="text" name="phone" class="form-control form-control-sm" placeholder="017XXXXXXXX">
                        </div>
                        <div class="col-6">
                            <label class="form-label small text-muted">ইমেইল ঠিকানা</label>
                            <input type="email" name="email" class="form-control form-control-sm" placeholder="contact@publisher.com">
                        </div>
                        <div class="col-12">
                            <label class="form-label small text-muted">ঠিকানা</label>
                            <input type="text" name="address" class="form-control form-control-sm" placeholder="যেমন: ৩৮/৪ বাংলাবাজার, ঢাকা">
                        </div>
                        <div class="col-12">
                            <label class="form-label small text-muted">ওয়েবসাইট (Website)</label>
                            <input type="url" name="website" class="form-control form-control-sm" placeholder="https://example.com">
                        </div>
                        <div class="col-12">
                            <label class="form-label small text-muted">সংক্ষিপ্ত বিবরণ</label>
                            <textarea name="description" class="form-control form-control-sm" rows="2" placeholder="প্রকাশনীর সংক্ষিপ্ত পরিচিতি..."></textarea>
                        </div>
                        <div class="col-12 pt-1">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="addPubIsActive" name="is_active" value="1" checked>
                                <label class="form-check-label small fw-bold text-dark" for="addPubIsActive">সক্রিয় ও শপে দৃশ্যমান</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light py-2.5">
                    <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-bs-dismiss="modal">বাতিল</button>
                    <button type="submit" id="addPubSubmitBtn" class="btn btn-sm btn-primary rounded-pill px-4 fw-bold shadow-xs">
                        <i class="fas fa-plus-circle me-1"></i> সংরক্ষণ করুন
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- MODAL 2: QUICK EDIT PUBLISHER                                             --}}
{{-- ========================================================================= --}}
<div class="modal fade" id="quickEditPublisherModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-dark text-white py-3">
                <h5 class="modal-title fw-bold text-white mb-0">
                    <i class="fas fa-pen-to-square me-1.5 text-primary-subtle"></i> প্রকাশকের তথ্য সম্পাদনা
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="quickEditPublisherForm" onsubmit="handleEditPublisherSubmit(event)" enctype="multipart/form-data">
                <input type="hidden" id="editPubId" name="id">
                
                <div class="modal-body p-4">
                    <div id="editPubAlertBox"></div>

                    {{-- Logo Upload with Preview --}}
                    <div class="text-center mb-3">
                        <div class="position-relative d-inline-block mb-2">
                            <img src="https://placehold.co/90x90/4f46e5/ffffff?text=Logo" id="editPubLogoPreview" class="rounded-circle border shadow-sm" style="width: 80px; height: 80px; object-fit: cover;">
                        </div>
                        <div>
                            <label for="editPubLogoInput" class="btn btn-xs btn-outline-primary rounded-pill px-3 cursor-pointer">
                                <i class="fas fa-camera me-1"></i> নতুন লোগো পরিবর্তন
                            </label>
                            <input type="file" id="editPubLogoInput" name="logo_file" accept="image/*" class="d-none" onchange="previewEditPubLogo(this)">
                        </div>
                    </div>

                    <div class="row g-2.5">
                        <div class="col-8">
                            <label class="form-label small fw-bold text-dark">প্রকাশনীর নাম <span class="text-danger">*</span></label>
                            <input type="text" id="editPubName" name="name" class="form-control form-control-sm fw-bold" required>
                        </div>
                        <div class="col-4">
                            <label class="form-label small text-muted">স্লাগ (Slug)</label>
                            <input type="text" id="editPubSlug" name="slug" class="form-control form-control-sm font-monospace">
                        </div>
                        <div class="col-6">
                            <label class="form-label small text-muted">ফোন নম্বর</label>
                            <input type="text" id="editPubPhone" name="phone" class="form-control form-control-sm">
                        </div>
                        <div class="col-6">
                            <label class="form-label small text-muted">ইমেইল ঠিকানা</label>
                            <input type="email" id="editPubEmail" name="email" class="form-control form-control-sm">
                        </div>
                        <div class="col-12">
                            <label class="form-label small text-muted">ঠিকানা</label>
                            <input type="text" id="editPubAddress" name="address" class="form-control form-control-sm">
                        </div>
                        <div class="col-12">
                            <label class="form-label small text-muted">ওয়েবসাইট (Website)</label>
                            <input type="url" id="editPubWebsite" name="website" class="form-control form-control-sm">
                        </div>
                        <div class="col-12">
                            <label class="form-label small text-muted">সংক্ষিপ্ত বিবরণ</label>
                            <textarea id="editPubDescription" name="description" class="form-control form-control-sm" rows="2"></textarea>
                        </div>
                        <div class="col-12 pt-1">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="editPubIsActive" name="is_active" value="1">
                                <label class="form-check-label small fw-bold text-dark" for="editPubIsActive">সক্রিয় ও শপে দৃশ্যমান</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light py-2.5">
                    <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-bs-dismiss="modal">বাতিল</button>
                    <button type="submit" id="editPubSubmitBtn" class="btn btn-sm btn-primary rounded-pill px-4 fw-bold shadow-xs">
                        <i class="fas fa-check-circle me-1"></i> আপডেট করুন
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- MODAL 3: QUICK RECORD PAYMENT VOUCHER                                     --}}
{{-- ========================================================================= --}}
<div class="modal fade" id="quickPaymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-success text-white py-3">
                <h5 class="modal-title fw-bold text-white mb-0">
                    <i class="fas fa-hand-holding-dollar me-1.5"></i> প্রকাশককে পেমেন্ট পরিশোধ ভাউচার
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="quickPaymentForm" onsubmit="handleQuickPaymentSubmit(event)">
                <input type="hidden" id="payPubId" name="publisher_id">
                
                <div class="modal-body p-4">
                    <div id="payPubAlertBox"></div>

                    <div class="p-3 bg-light rounded-3 mb-3 border">
                        <span class="text-muted small d-block">প্রকাশকের নাম:</span>
                        <h5 class="fw-bold text-dark mb-1" id="payPubName">—</h5>
                        <div class="small text-muted">
                            বর্তমান বকেয়া পাওনা: <strong class="text-danger font-monospace fs-6" id="payPubDueAmount">৳০</strong>
                        </div>
                    </div>

                    <div class="row g-2.5">
                        <div class="col-6">
                            <label class="form-label small fw-bold text-dark">পরিশোধের পরিমাণ (৳) <span class="text-danger">*</span></label>
                            <input type="number" id="payAmountInput" name="amount" min="1" step="1" class="form-control form-control-sm fw-bold text-success font-monospace fs-6" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-dark">পরিশোধের তারিখ <span class="text-danger">*</span></label>
                            <input type="date" name="payment_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-dark">পেমেন্ট মাধ্যম <span class="text-danger">*</span></label>
                            <select name="payment_method" class="form-select form-select-sm" required>
                                <option value="cash">নগদ (Cash)</option>
                                <option value="bank">ব্যাংক ট্রান্সফার (Bank)</option>
                                <option value="bkash">বিকাশ (bKash)</option>
                                <option value="nagad">নগদ (Nagad)</option>
                                <option value="cheque">চেক (Cheque)</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small text-muted">ট্রানজেকশন / চেক নং</label>
                            <input type="text" name="transaction_ref" class="form-control form-control-sm" placeholder="Trx ID / Cheque #">
                        </div>
                        <div class="col-12">
                            <label class="form-label small text-muted">মন্তব্য / নোট</label>
                            <input type="text" name="note" class="form-control form-control-sm" placeholder="যেমন: আগস্ট মাসের বই ক্রয়ের কিস্তি...">
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light py-2.5">
                    <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-bs-dismiss="modal">বাতিল</button>
                    <button type="submit" id="payPubSubmitBtn" class="btn btn-sm btn-success rounded-pill px-4 fw-bold shadow-xs">
                        <i class="fas fa-check-circle me-1"></i> পেমেন্ট সংরক্ষণ করুন
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Dynamic Debounce Auto Search
let pubSearchTimeout = null;
const pubSearchInput = document.getElementById('publisherSearchInput');
if (pubSearchInput) {
    pubSearchInput.addEventListener('input', function() {
        clearTimeout(pubSearchTimeout);
        pubSearchTimeout = setTimeout(() => {
            submitPubFilter();
        }, 500);
    });
}

function submitPubFilter() {
    const form = document.getElementById('publishersFilterForm');
    if (form) {
        form.submit();
    }
}

// In-Memory Publisher Data Store for Instant Modal Editing
const publishersDataMap = {
    @foreach ($publishers as $p)
        {{ $p->id }}: {
            id: {{ $p->id }},
            name: "{{ addslashes($p->name) }}",
            slug: "{{ addslashes($p->slug ?? '') }}",
            phone: "{{ addslashes($p->phone ?? '') }}",
            email: "{{ addslashes($p->email ?? '') }}",
            address: "{{ addslashes($p->address ?? '') }}",
            website: "{{ addslashes($p->website ?? '') }}",
            description: "{{ addslashes($p->description ?? '') }}",
            is_active: {{ $p->is_active ? 1 : 0 }},
            logo_url: "{{ $p->logo ? (str_starts_with($p->logo, 'http') ? $p->logo : (str_starts_with($p->logo, 'storage/') ? asset($p->logo) : asset('storage/' . ltrim($p->logo, '/')))) : 'https://placehold.co/90x90/4f46e5/ffffff?text=' . urlencode(mb_substr($p->name, 0, 1)) }}"
        },
    @endforeach
};

// 1. Add Publisher Modal
function openAddPublisherModal() {
    document.getElementById('quickAddPublisherForm').reset();
    document.getElementById('addPubLogoPreview').src = 'https://placehold.co/90x90/4f46e5/ffffff?text=Logo';
    document.getElementById('addPubAlertBox').innerHTML = '';
    new bootstrap.Modal(document.getElementById('quickAddPublisherModal')).show();
}

function previewAddPubLogo(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('addPubLogoPreview').src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function handleAddPublisherSubmit(e) {
    e.preventDefault();
    const btn = document.getElementById('addPubSubmitBtn');
    const alertBox = document.getElementById('addPubAlertBox');
    const form = document.getElementById('quickAddPublisherForm');
    const formData = new FormData(form);

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> সেভ হচ্ছে...';

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    fetch("{{ route('admin.publishers.quick-store') }}", {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alertBox.innerHTML = `<div class="alert alert-success p-2 small mb-3"><i class="fas fa-check-circle me-1"></i> ${data.message}</div>`;
            setTimeout(() => {
                location.reload();
            }, 800);
        } else {
            alertBox.innerHTML = `<div class="alert alert-danger p-2 small mb-3">${data.message || 'ত্রুটি হয়েছে'}</div>`;
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-plus-circle me-1"></i> সংরক্ষণ করুন';
        }
    })
    .catch(err => {
        alertBox.innerHTML = `<div class="alert alert-danger p-2 small mb-3">সার্ভার এরর হয়েছে।</div>`;
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-plus-circle me-1"></i> সংরক্ষণ করুন';
    });
}

// 2. Edit Publisher Modal
function openEditPublisherModal(pubId) {
    const pub = publishersDataMap[pubId];
    if (!pub) return;

    document.getElementById('editPubId').value = pub.id;
    document.getElementById('editPubName').value = pub.name;
    document.getElementById('editPubSlug').value = pub.slug || '';
    document.getElementById('editPubPhone').value = pub.phone || '';
    document.getElementById('editPubEmail').value = pub.email || '';
    document.getElementById('editPubAddress').value = pub.address || '';
    document.getElementById('editPubWebsite').value = pub.website || '';
    document.getElementById('editPubDescription').value = pub.description || '';
    document.getElementById('editPubIsActive').checked = (pub.is_active === 1);
    document.getElementById('editPubLogoPreview').src = pub.logo_url;
    document.getElementById('editPubAlertBox').innerHTML = '';

    new bootstrap.Modal(document.getElementById('quickEditPublisherModal')).show();
}

function previewEditPubLogo(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('editPubLogoPreview').src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function handleEditPublisherSubmit(e) {
    e.preventDefault();
    const btn = document.getElementById('editPubSubmitBtn');
    const alertBox = document.getElementById('editPubAlertBox');
    const pubId = document.getElementById('editPubId').value;
    const form = document.getElementById('quickEditPublisherForm');
    const formData = new FormData(form);

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> আপডেট হচ্ছে...';

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    fetch(`/admin/publishers/${pubId}/quick-update`, {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alertBox.innerHTML = `<div class="alert alert-success p-2 small mb-3"><i class="fas fa-check-circle me-1"></i> ${data.message}</div>`;
            setTimeout(() => {
                location.reload();
            }, 800);
        } else {
            alertBox.innerHTML = `<div class="alert alert-danger p-2 small mb-3">${data.message || 'ত্রুটি হয়েছে'}</div>`;
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check-circle me-1"></i> আপডেট করুন';
        }
    })
    .catch(err => {
        alertBox.innerHTML = `<div class="alert alert-danger p-2 small mb-3">সার্ভার এরর হয়েছে।</div>`;
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check-circle me-1"></i> আপডেট করুন';
    });
}

// 3. Quick Payment Modal
function openQuickPaymentModal(pubId, pubName, dueAmount) {
    document.getElementById('quickPaymentForm').reset();
    document.getElementById('payPubId').value = pubId;
    document.getElementById('payPubName').textContent = pubName;
    document.getElementById('payPubDueAmount').textContent = '৳' + Number(dueAmount).toLocaleString('en-US', { minimumFractionDigits: 0 });
    document.getElementById('payAmountInput').value = dueAmount > 0 ? dueAmount : '';
    document.getElementById('payPubAlertBox').innerHTML = '';

    new bootstrap.Modal(document.getElementById('quickPaymentModal')).show();
}

function handleQuickPaymentSubmit(e) {
    e.preventDefault();
    const btn = document.getElementById('payPubSubmitBtn');
    const alertBox = document.getElementById('payPubAlertBox');
    const pubId = document.getElementById('payPubId').value;
    const form = document.getElementById('quickPaymentForm');
    const formData = new FormData(form);

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> পেমেন্ট হচ্ছে...';

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    fetch(`/admin/publishers/${pubId}/quick-payment`, {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alertBox.innerHTML = `<div class="alert alert-success p-2 small mb-3"><i class="fas fa-check-circle me-1"></i> ${data.message}</div>`;
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            alertBox.innerHTML = `<div class="alert alert-danger p-2 small mb-3">${data.message || 'ত্রুটি হয়েছে'}</div>`;
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check-circle me-1"></i> পেমেন্ট সংরক্ষণ করুন';
        }
    })
    .catch(err => {
        alertBox.innerHTML = `<div class="alert alert-danger p-2 small mb-3">সার্ভার এরর হয়েছে।</div>`;
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check-circle me-1"></i> পেমেন্ট সংরক্ষণ করুন';
    });
}

// 4. Live Active/Inactive Switch Toggle
function togglePublisherActive(pubId, checkbox) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    fetch(`/admin/publishers/${pubId}/toggle-status`, {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        }
    })
    .then(res => res.json())
    .then(data => {
        if (!data.success) {
            checkbox.checked = !checkbox.checked;
            alert(data.message || 'স্ট্যাটাস পরিবর্তন করা সম্ভব হয়নি');
        }
    })
    .catch(() => {
        checkbox.checked = !checkbox.checked;
        alert('সার্ভার এরর হয়েছে');
    });
}

// 5. CSV Export
function exportPublishersToCSV() {
    const table = document.getElementById('adminPublishersTable');
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
    link.setAttribute("download", "Idea_Publishers_Directory_" + new Date().toISOString().slice(0, 10) + ".csv");
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
</script>
@endpush

@endsection
