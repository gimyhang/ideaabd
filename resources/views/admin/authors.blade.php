@extends('layouts.admin')

@section('title', 'লেখক ও গবেষক ডিরেক্টরি ব্যবস্থাপনা (Authors Directory)')
@section('heading', 'লেখক ও গবেষক ডিরেক্টরি')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">ড্যাশবোর্ড</a></li>
    <li class="breadcrumb-item active" aria-current="page">লেখক ব্যবস্থাপনা</li>
@endsection

@section('actions')
    <div class="d-flex flex-wrap align-items-center gap-2">
        <button type="button" class="btn btn-outline-success btn-sm rounded-pill px-3 shadow-sm d-inline-flex align-items-center gap-1" onclick="exportAuthorsToCSV()" title="CSV ফাইলে এক্সপোর্ট করুন">
            <i class="fa-solid fa-file-csv text-success"></i> <span>এক্সপোর্ট (CSV)</span>
        </button>
        <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm d-inline-flex align-items-center gap-1" onclick="window.print()" title="প্রিন্ট ভিউ">
            <i class="fa-solid fa-print"></i> <span>প্রিন্ট</span>
        </button>
        <button type="button" class="btn btn-primary btn-sm rounded-pill px-3.5 fw-bold shadow-sm d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#addAuthorModal" onclick="openAddAuthorModal()">
            <i class="fa-solid fa-user-plus"></i> <span>নতুন লেখক যুক্ত করুন</span>
        </button>
        <a href="{{ route('authors.index') }}" target="_blank" rel="noopener" class="btn btn-outline-dark btn-sm rounded-pill px-3 shadow-sm d-inline-flex align-items-center gap-1">
            <i class="fa-solid fa-arrow-up-right-from-square"></i> <span>পাবলিক বুকশপ</span>
        </a>
    </div>
@endsection

@section('content')
<style>
/* ── Premium Modern Aesthetic Styling & Spacing Fixes ── */
:root {
    --agy-primary: #0284c7;
    --agy-primary-light: #e0f2fe;
    --agy-success: #10b981;
    --agy-warning: #f59e0b;
    --agy-danger: #ef4444;
    --agy-card-bg: #ffffff;
    --agy-border-color: rgba(226, 232, 240, 0.9);
}

/* Explicit Spacing & Padding Guarantees */
.author-kpi-card {
    padding: 1.1rem 1.2rem;
    border-radius: 14px;
    background: #ffffff;
    border: 1px solid var(--agy-border-color);
    transition: all 0.22s ease-in-out;
}
.author-kpi-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06);
}

.author-filter-card {
    padding: 1.15rem 1.25rem;
    border-radius: 16px;
    background: #ffffff;
    border: 1px solid var(--agy-border-color);
}

.author-card-modern {
    background: var(--agy-card-bg);
    border: 1px solid var(--agy-border-color);
    border-radius: 16px;
    padding: 1.15rem;
    transition: all 0.26s cubic-bezier(0.16, 1, 0.3, 1);
    position: relative;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.author-card-modern:hover {
    transform: translateY(-4px);
    box-shadow: 0 14px 28px -6px rgba(2, 132, 199, 0.16), 0 4px 10px rgba(0, 0, 0, 0.04);
    border-color: rgba(2, 132, 199, 0.45);
}

.author-avatar-wrapper {
    position: relative;
    width: 62px;
    height: 62px;
    border-radius: 50%;
    flex-shrink: 0;
}

.author-avatar-img {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.author-avatar-fallback {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ffffff;
    font-weight: 700;
    font-size: 1.2rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    text-transform: uppercase;
}

.verified-halo {
    position: absolute;
    inset: -3px;
    border-radius: 50%;
    background: linear-gradient(135deg, #0ea5e9, #38bdf8, #0284c7);
    z-index: 0;
    opacity: 0.85;
}

.author-status-dot {
    position: absolute;
    bottom: 1px;
    right: 1px;
    width: 14px;
    height: 14px;
    border-radius: 50%;
    border: 2px solid #ffffff;
    z-index: 2;
}

.book-preview-thumb {
    width: 50px;
    height: 70px;
    border-radius: 6px;
    object-fit: cover;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.12);
    border: 1px solid rgba(0, 0, 0, 0.08);
    transition: transform 0.2s ease;
}

.book-preview-thumb:hover {
    transform: scale(1.06);
}

.filter-tab-btn {
    border-radius: 20px;
    font-size: 0.82rem;
    padding: 0.4rem 0.9rem;
    font-weight: 600;
    transition: all 0.2s ease;
    border: 1px solid transparent;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
}

.filter-tab-btn.active {
    background: #0f172a;
    color: #ffffff !important;
    box-shadow: 0 4px 12px rgba(15, 23, 42, 0.2);
}

.filter-tab-btn:not(.active) {
    background: #f8fafc;
    color: #475569;
    border-color: #e2e8f0;
}

.filter-tab-btn:not(.active):hover {
    background: #e2e8f0;
    color: #0f172a;
}

.action-btn-circle {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    transition: all 0.18s ease;
    border: 1px solid rgba(0, 0, 0, 0.08);
    background: #ffffff;
    cursor: pointer;
    padding: 0;
}

.action-btn-circle:hover {
    transform: scale(1.12);
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.12);
}

.custom-switch-clean .form-check-input {
    cursor: pointer;
    width: 2.2rem;
    height: 1.2rem;
}

.bulk-action-bar-modern {
    padding: 0.85rem 1.25rem;
    border-radius: 14px;
    background: #0f172a;
    color: #ffffff;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.author-table th {
    padding: 0.85rem 1rem;
    font-size: 0.76rem;
    font-weight: 700;
    letter-spacing: 0.5px;
}

.author-table td {
    padding: 0.9rem 1rem;
    font-size: 0.84rem;
}

.cursor-pointer {
    cursor: pointer;
}

/* Dark Mode Harmonization */
body.dark-mode .author-kpi-card,
body.dark-mode .author-filter-card,
body.dark-mode .author-card-modern,
body.dark-mode .card {
    background: #1e293b !important;
    border-color: #334155 !important;
}

body.dark-mode .filter-tab-btn:not(.active) {
    background: #0f172a;
    color: #cbd5e1;
    border-color: #334155;
}

body.dark-mode .filter-tab-btn:not(.active):hover {
    background: #334155;
    color: #ffffff;
}

body.dark-mode .action-btn-circle {
    background: #0f172a;
    border-color: #334155;
}

body.dark-mode .modal-content {
    background: #1e293b;
    color: #f8fafc;
}

body.dark-mode .modal-header,
body.dark-mode .modal-footer {
    background: #0f172a !important;
    border-color: #334155 !important;
}

body.dark-mode .modal-body .form-control,
body.dark-mode .modal-body .form-select,
body.dark-mode .modal-body textarea {
    background: #0f172a;
    color: #f8fafc;
    border-color: #334155;
}
</style>

<div class="d-flex flex-column gap-3 mb-4">

    {{-- Flash Notifications --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-0 shadow-sm rounded-4 border-0 bg-success-subtle text-success-emphasis p-3" role="alert">
            <i class="fa-solid fa-circle-check fs-5 me-2 text-success"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center mb-0 shadow-sm rounded-4 border-0 bg-danger-subtle text-danger-emphasis p-3" role="alert">
            <i class="fa-solid fa-triangle-exclamation fs-5 me-2 text-danger"></i>
            <div>{{ session('error') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- 1. KPI STAT METRICS CARDS                                                 --}}
    {{-- ========================================================================= --}}
    <div class="row g-3">
        {{-- 1. Total Authors --}}
        <div class="col-6 col-md-4 col-xl">
            <a href="{{ route('admin.authors') }}" class="text-decoration-none">
                <div class="author-kpi-card h-100 shadow-sm border-start border-4 border-primary {{ !request()->hasAny(['is_active', 'is_verified', 'has_books', 'author_type']) ? 'ring-2 ring-primary' : '' }}">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-semibold d-block" style="font-size: 0.75rem;">সর্বমোট লেখক</span>
                            <h5 class="fw-bold mb-0 text-dark">{{ number_format($stats['total'] ?? 0) }} <small class="text-muted fw-normal" style="font-size: 0.72rem;">জন</small></h5>
                        </div>
                        <div class="rounded-circle bg-primary-subtle text-primary p-2 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                            <i class="fa-solid fa-pen-nib small"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        {{-- 2. Active Authors --}}
        <div class="col-6 col-md-4 col-xl">
            <a href="{{ route('admin.authors', array_merge(request()->except(['is_active', 'page']), ['is_active' => '1'])) }}" class="text-decoration-none">
                <div class="author-kpi-card h-100 shadow-sm border-start border-4 border-success {{ request('is_active') === '1' ? 'ring-2 ring-success' : '' }}">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-semibold d-block" style="font-size: 0.75rem;">সক্রিয় লেখক</span>
                            <h5 class="fw-bold mb-0 text-success">{{ number_format($stats['active'] ?? 0) }} <small class="text-muted fw-normal" style="font-size: 0.72rem;">জন</small></h5>
                        </div>
                        <div class="rounded-circle bg-success-subtle text-success p-2 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                            <i class="fa-solid fa-user-check small"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        {{-- 3. Verified Authors --}}
        <div class="col-6 col-md-4 col-xl">
            <a href="{{ route('admin.authors', array_merge(request()->except(['is_verified', 'page']), ['is_verified' => '1'])) }}" class="text-decoration-none">
                <div class="author-kpi-card h-100 shadow-sm border-start border-4 border-info {{ request('is_verified') === '1' ? 'ring-2 ring-info' : '' }}">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-semibold d-block" style="font-size: 0.75rem;">ভেরিফাইড লেখক</span>
                            <h5 class="fw-bold mb-0 text-info">{{ number_format($stats['verified'] ?? 0) }} <small class="text-muted fw-normal" style="font-size: 0.72rem;">জন</small></h5>
                        </div>
                        <div class="rounded-circle bg-info-subtle text-info p-2 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                            <i class="fa-solid fa-certificate small"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        {{-- 4. Published Authors (With Books) --}}
        <div class="col-6 col-md-4 col-xl">
            <a href="{{ route('admin.authors', array_merge(request()->except(['has_books', 'page']), ['has_books' => '1'])) }}" class="text-decoration-none">
                <div class="author-kpi-card h-100 shadow-sm border-start border-4 border-warning {{ request('has_books') === '1' ? 'ring-2 ring-warning' : '' }}">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-semibold d-block" style="font-size: 0.75rem;">বই প্রকাশিত লেখক</span>
                            <h5 class="fw-bold mb-0 text-warning-emphasis">{{ number_format($stats['with_books'] ?? 0) }} <small class="text-muted fw-normal" style="font-size: 0.72rem;">জন</small></h5>
                        </div>
                        <div class="rounded-circle bg-warning-subtle text-warning-emphasis p-2 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                            <i class="fa-solid fa-book-open small"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        {{-- 5. Registered User Accounts --}}
        <div class="col-6 col-md-4 col-xl">
            <a href="{{ route('admin.authors', array_merge(request()->except(['author_type', 'page']), ['author_type' => 'registered'])) }}" class="text-decoration-none">
                <div class="author-kpi-card h-100 shadow-sm border-start border-4 {{ request('author_type') === 'registered' ? 'ring-2' : '' }}" style="border-left-color: #8b5cf6 !important;">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-semibold d-block" style="font-size: 0.75rem;">পোর্টাল রেজিস্টার্ড ইউজার</span>
                            <h5 class="fw-bold mb-0 text-dark">{{ number_format($stats['registered_users'] ?? 0) }} <small class="text-muted fw-normal" style="font-size: 0.72rem;">ইউজার</small></h5>
                        </div>
                        <div class="rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; background-color: #f3e8ff; color: #7c3aed;">
                            <i class="fa-solid fa-id-badge small"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        {{-- 6. Total Catalog Books --}}
        <div class="col-6 col-md-4 col-xl">
            <div class="author-kpi-card h-100 shadow-sm border-start border-4 border-secondary">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold d-block" style="font-size: 0.75rem;">মোট বই ক্যাটালগ</span>
                        <h5 class="fw-bold mb-0 text-dark">{{ number_format($stats['total_books'] ?? 0) }} <small class="text-muted fw-normal" style="font-size: 0.72rem;">টি বই</small></h5>
                    </div>
                    <div class="rounded-circle bg-secondary-subtle text-secondary p-2 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                        <i class="fa-solid fa-layer-group small"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- 2. ADVANCED FILTERS, PILL TABS & TOOLBAR                                   --}}
    {{-- ========================================================================= --}}
    <div class="author-filter-card shadow-sm">
        
        {{-- Quick Filter Pills --}}
        <div class="d-flex flex-wrap align-items-center gap-2 pb-3 mb-3 border-bottom">
            <span class="small fw-bold text-muted me-1 d-inline-flex align-items-center gap-1">
                <i class="fa-solid fa-sliders text-primary"></i> <span>ফিল্টার:</span>
            </span>
            <a href="{{ route('admin.authors') }}" 
               class="filter-tab-btn {{ !request()->hasAny(['is_active', 'is_verified', 'has_books', 'author_type']) ? 'active' : '' }}">
                <i class="fa-solid fa-shield-check text-success"></i> <span>অনুমোদিত লেখক ({{ number_format($stats['approved'] ?? 0) }})</span>
            </a>
            <a href="{{ route('admin.authors', array_merge(request()->except(['author_type', 'page']), ['author_type' => 'pending'])) }}" 
               class="filter-tab-btn {{ request('author_type') === 'pending' ? 'active' : '' }}">
                <i class="fa-solid fa-hourglass-half text-warning"></i> <span>অনুমোদন অপেক্ষমাণ ({{ number_format($stats['pending'] ?? 0) }})</span>
                @if(($stats['pending'] ?? 0) > 0)
                    <span class="badge bg-danger rounded-pill px-2 py-0.5 ms-1" style="font-size: 10px;">রিভিউ প্রয়োজন</span>
                @endif
            </a>
            <a href="{{ route('admin.authors', array_merge(request()->except(['author_type', 'page']), ['author_type' => 'registered'])) }}" 
               class="filter-tab-btn {{ request('author_type') === 'registered' ? 'active' : '' }}">
                <i class="fa-solid fa-user-shield text-primary"></i> <span>পোর্টাল অ্যাকাউন্ট ({{ number_format($stats['registered_users'] ?? 0) }})</span>
            </a>
            <a href="{{ route('admin.authors', array_merge(request()->except(['has_books', 'page']), ['has_books' => '1'])) }}" 
               class="filter-tab-btn {{ request('has_books') === '1' ? 'active' : '' }}">
                <i class="fa-solid fa-book-bookmark text-warning"></i> <span>বই প্রকাশিত ({{ number_format($stats['with_books'] ?? 0) }})</span>
            </a>
            <a href="{{ route('admin.authors', array_merge(request()->except(['is_verified', 'page']), ['is_verified' => '1'])) }}" 
               class="filter-tab-btn {{ request('is_verified') === '1' ? 'active' : '' }}">
                <i class="fa-solid fa-circle-check text-info"></i> <span>ভেরিফাইড ({{ number_format($stats['verified'] ?? 0) }})</span>
            </a>
            <a href="{{ route('admin.authors', ['is_active' => 'all']) }}" 
               class="filter-tab-btn {{ request('is_active') === 'all' ? 'active' : '' }}">
                <i class="fa-solid fa-layer-group text-secondary"></i> <span>সকল রেকর্ড ({{ number_format($stats['total'] ?? 0) }})</span>
            </a>
            <a href="{{ route('admin.users', ['role' => 'author']) }}" 
               class="filter-tab-btn ms-auto border-primary text-primary bg-primary-subtle" title="লেখক রেজিস্ট্রেশন অ্যাকাউন্টস পরিচালনা করুন">
                <i class="fa-solid fa-users"></i> <span>লেখক রেজিস্ট্রেশন ইউজারস</span> <i class="fa-solid fa-arrow-right ms-1 small"></i>
            </a>
        </div>

        <form action="{{ route('admin.authors') }}" method="GET" id="authorsFilterForm" class="row g-2 align-items-center">
            {{-- Live Search Box with instant client & server filter --}}
            <div class="col-12 col-md-5 col-lg-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-end-0 text-muted ps-3">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </span>
                    <input type="search" name="search" id="authorSearchInput" class="form-control border-start-0 bg-light" 
                           placeholder="লেখকের নাম, ফোন, ইমেইল, বা বায়ো খুঁজুন..." value="{{ request('search') }}"
                           autocomplete="off">
                    @if(request('search'))
                        <a href="{{ route('admin.authors', request()->except('search')) }}" class="btn btn-outline-secondary border-start-0 bg-light" title="ক্লিয়ার সার্চ">
                            <i class="fa-solid fa-xmark"></i>
                        </a>
                    @endif
                </div>
            </div>

            {{-- Status Filter --}}
            <div class="col-6 col-md-3 col-lg-2">
                <select name="is_active" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="" @selected(request('is_active') === null || request('is_active') === '')>সকল স্ট্যাটাস</option>
                    <option value="1" @selected(request('is_active') === '1')>🟢 সক্রিয় (Active)</option>
                    <option value="0" @selected(request('is_active') === '0')>🔴 নিষ্ক্রিয় (Inactive)</option>
                </select>
            </div>

            {{-- Verification Filter --}}
            <div class="col-6 col-md-3 col-lg-2">
                <select name="is_verified" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="" @selected(request('is_verified') === null || request('is_verified') === '')>সকল ভেরিফিকেশন</option>
                    <option value="1" @selected(request('is_verified') === '1')>✓ ভেরিফাইড (Verified)</option>
                    <option value="0" @selected(request('is_verified') === '0')>সাধারণ (Unverified)</option>
                </select>
            </div>

            {{-- Sort Filter --}}
            <div class="col-6 col-md-3 col-lg-2">
                <select name="sort" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="latest" @selected(request('sort') === 'latest' || !request('sort'))>সর্বশেষ যুক্ত (Newest)</option>
                    <option value="oldest" @selected(request('sort') === 'oldest')>প্রাচীনতম (Oldest)</option>
                    <option value="name_asc" @selected(request('sort') === 'name_asc')>নাম (A-Z / ক-হ)</option>
                    <option value="name_desc" @selected(request('sort') === 'name_desc')>নাম (Z-A / হ-ক)</option>
                    <option value="books_desc" @selected(request('sort') === 'books_desc')>সর্বোচ্চ বই (Most Books)</option>
                </select>
            </div>

            {{-- Per Page & View Buttons --}}
            <div class="col-6 col-md-3 col-lg-2 d-flex align-items-center justify-content-end gap-2">
                <select name="per_page" class="form-select form-select-sm w-auto" onchange="this.form.submit()" title="প্রতি পৃষ্ঠায় আইটেম">
                    <option value="12" @selected(request('per_page') == 12)>12</option>
                    <option value="24" @selected(request('per_page') == 24)>24</option>
                    <option value="28" @selected(request('per_page') == 28 || !request('per_page'))>28</option>
                    <option value="42" @selected(request('per_page') == 42)>42</option>
                    <option value="70" @selected(request('per_page') == 70)>70</option>
                    <option value="100" @selected(request('per_page') == 100)>100</option>
                </select>

                <div class="btn-group btn-group-sm shadow-sm" role="group" aria-label="View Mode">
                    <button type="button" class="btn btn-outline-primary active" id="btnViewGrid" onclick="switchViewMode('grid')" title="কার্ড গ্রিড ভিউ">
                        <i class="fa-solid fa-grip"></i>
                    </button>
                    <button type="button" class="btn btn-outline-primary" id="btnViewTable" onclick="switchViewMode('table')" title="টেবিল ভিউ">
                        <i class="fa-solid fa-table-list"></i>
                    </button>
                </div>

                @if(request()->hasAny(['search', 'is_active', 'is_verified', 'has_books', 'author_type', 'sort', 'per_page']))
                    <a href="{{ route('admin.authors') }}" class="btn btn-sm btn-light border text-danger" title="ফিল্টার রিসেট করুন">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- ========================================================================= --}}
    {{-- 3. BULK ACTIONS BAR (Visible when items selected)                         --}}
    {{-- ========================================================================= --}}
    <div id="bulkActionBar" class="bulk-action-bar-modern shadow-sm d-none">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary rounded-pill px-3 py-2 fw-semibold" id="selectedCountBadge">০ টি নির্বাচিত</span>
                <button type="button" class="btn btn-sm btn-outline-light rounded-pill px-3" onclick="selectAllAuthors(false)">বাছাই বাতিল</button>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <button type="button" class="btn btn-sm btn-success rounded-pill px-3 d-inline-flex align-items-center gap-1" onclick="executeBulkAction('activate')">
                    <i class="fa-solid fa-check"></i> <span>সক্রিয় করুন</span>
                </button>
                <button type="button" class="btn btn-sm btn-warning text-dark rounded-pill px-3 d-inline-flex align-items-center gap-1" onclick="executeBulkAction('deactivate')">
                    <i class="fa-solid fa-pause"></i> <span>নিষ্ক্রিয় করুন</span>
                </button>
                <button type="button" class="btn btn-sm btn-info text-white rounded-pill px-3 d-inline-flex align-items-center gap-1" onclick="executeBulkAction('verify')">
                    <i class="fa-solid fa-certificate"></i> <span>ভেরিফাই করুন</span>
                </button>
                <button type="button" class="btn btn-sm btn-danger rounded-pill px-3 d-inline-flex align-items-center gap-1" onclick="executeBulkAction('delete')">
                    <i class="fa-solid fa-trash"></i> <span>ডিলিট করুন</span>
                </button>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- 4. MAIN CONTENT: 4-COLUMN CARDS GRID & TABLE VIEW                         --}}
    {{-- ========================================================================= --}}
    @if ($authors->isEmpty())
        <div class="card border-0 shadow-sm rounded-4 bg-white p-5 text-center my-3">
            <div class="mb-3">
                <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center p-3" style="width: 72px; height: 72px;">
                    <i class="fa-solid fa-feather-pointed fs-2 text-muted opacity-50"></i>
                </div>
            </div>
            <h5 class="fw-bold text-dark mb-1">কোনো লেখক পাওয়া যায়নি</h5>
            <p class="text-muted small mb-3">অনুসন্ধানের কি-ওয়ার্ড বা ফিল্টার পরিবর্তন করে পুনরায় চেষ্টা করুন অথবা নতুন লেখক প্রোফাইল তৈরি করুন।</p>
            <div class="d-flex justify-content-center gap-2">
                <a href="{{ route('admin.authors') }}" class="btn btn-sm btn-light border rounded-pill px-4">ফিল্টার রিসেট</a>
                <button type="button" class="btn btn-sm btn-primary rounded-pill px-4 fw-bold d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#addAuthorModal" onclick="openAddAuthorModal()">
                    <i class="fa-solid fa-plus"></i> <span>নতুন লেখক যুক্ত করুন</span>
                </button>
            </div>
        </div>
    @else

        {{-- 4A. MODERN 4-COLUMN AUTHOR CARDS GRID --}}
        <div id="authorsGridView" class="view-container">
            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-3" id="authorsCardContainer">
                @foreach ($authors as $author)
                    @php
                        $avatarUrl = $author->avatar_url;
                        $initials = $author->initials;
                        $bgColor = $author->avatar_bg_color;
                        $booksCount = $author->books_count ?? 0;
                        $topBook = $author->books->first();
                        $hasUser = !empty($author->user_id) || !empty($author->user);
                    @endphp
                    <div class="col author-item-wrapper" id="authorCard-{{ $author->id }}" data-author-name="{{ strtolower($author->name . ' ' . $author->name_bn . ' ' . $author->name_en) }}" data-author-phone="{{ $author->phone }}">
                        <div class="author-card-modern h-100 shadow-sm">
                            
                            {{-- Card Top Row: Checkbox, Avatar, Author Info, & Top Book --}}
                            <div class="d-flex align-items-center justify-content-between gap-2 w-100 mb-3">
                                
                                {{-- 1. Selection & Avatar --}}
                                <div class="d-flex align-items-center gap-2 flex-shrink-0">
                                    <div class="form-check p-0 m-0">
                                        <input class="form-check-input author-select-checkbox cursor-pointer" type="checkbox" value="{{ $author->id }}" onchange="updateBulkSelectionState()">
                                    </div>

                                    <div class="author-avatar-wrapper cursor-pointer" 
                                         onclick="previewAuthorAvatar('{{ $avatarUrl }}', '{{ addslashes($author->name) }}')"
                                         title="ছবি পূর্ণ আকারে দেখতে ক্লিক করুন">
                                         
                                        @if($author->is_verified)
                                            <div class="verified-halo"></div>
                                        @endif
                                        
                                        <div class="position-relative w-100 h-100 rounded-circle overflow-hidden bg-white border border-2 border-white">
                                            @if($avatarUrl)
                                                <img src="{{ $avatarUrl }}" alt="{{ $author->name }}" class="author-avatar-img"
                                                     onerror="this.style.display='none'; this.parentElement.querySelector('.author-avatar-fallback').style.display='flex';">
                                                <div class="author-avatar-fallback" style="display: none; background: {{ $bgColor }};">
                                                    {{ $initials }}
                                                </div>
                                            @else
                                                <div class="author-avatar-fallback" style="background: {{ $bgColor }};">
                                                    {{ $initials }}
                                                </div>
                                            @endif
                                        </div>

                                        <span class="author-status-dot {{ $author->is_active ? 'bg-success' : 'bg-secondary' }}" 
                                              id="statusDot-{{ $author->id }}"
                                              title="{{ $author->is_active ? 'সক্রিয় (Active)' : 'নিষ্ক্রিয় (Inactive)' }}"></span>
                                    </div>
                                </div>

                                {{-- 2. Middle Column: Name, Verification, Books Count, User Account Badge --}}
                                <div class="flex-grow-1 min-w-0 text-center px-1">
                                    <div class="d-flex align-items-center justify-content-center gap-1 mb-1">
                                        <h6 class="fw-bold text-dark mb-0 text-truncate" style="font-size: 0.92rem;" title="{{ $author->name }}">
                                            <a href="javascript:void(0)" onclick="openAuthorDetailsModal({{ $author->id }})" class="text-decoration-none text-dark hover-primary">
                                                {{ $author->name }}
                                            </a>
                                        </h6>
                                        @if($author->is_verified)
                                            <i class="fa-solid fa-circle-check text-info flex-shrink-0" style="font-size: 13px;" title="ভেরিফাইড লেখক"></i>
                                        @endif
                                    </div>

                                    {{-- Books Count & User Badge --}}
                                    <div class="d-flex flex-wrap align-items-center justify-content-center gap-1 mb-1">
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2 py-0.5" style="font-size: 0.70rem;">
                                            <i class="fa-solid fa-book me-1"></i>{{ $booksCount }} টি বই
                                        </span>
                                        @if($hasUser)
                                            <span class="badge border rounded-pill px-2 py-0.5" style="font-size: 0.65rem; background-color: #f5f3ff; color: #7c3aed; border-color: #ddd6fe !important;" title="রেজিস্টার্ড পোর্টাল অ্যাকাউন্ট">
                                                <i class="fa-solid fa-user-check me-0.5"></i>Portal
                                            </span>
                                        @endif
                                    </div>

                                    {{-- Direct Slug / Phone snippet --}}
                                    <div class="text-muted small text-truncate" style="font-size: 0.72rem;" title="{{ $author->phone ?: $author->email ?: $author->slug }}">
                                        @if($author->phone)
                                            <i class="fa-solid fa-phone text-muted me-1" style="font-size: 9px;"></i>{{ $author->phone }}
                                        @elseif($author->email)
                                            <i class="fa-solid fa-envelope text-muted me-1" style="font-size: 9px;"></i>{{ Str::limit($author->email, 14) }}
                                        @else
                                            <span class="font-monospace opacity-75">/{{ Str::limit($author->slug, 12) }}</span>
                                        @endif
                                    </div>
                                </div>

                                {{-- 3. Right Column: Top Book Preview Thumbnail --}}
                                <div class="flex-shrink-0 text-center">
                                    @if($topBook)
                                        <div class="position-relative" title="নির্বাচিত বই: {{ $topBook->title }}">
                                            @if($topBook->cover_image)
                                                <img src="{{ asset('storage/' . $topBook->cover_image) }}" alt="{{ $topBook->title }}" 
                                                     class="book-preview-thumb" 
                                                     onerror="this.src='/images/book-placeholder.png'">
                                            @else
                                                <div class="book-preview-thumb d-flex flex-column align-items-center justify-content-center bg-light text-muted p-1 text-center" style="font-size: 0.55rem;">
                                                    <i class="fa-solid fa-book mb-1 text-primary opacity-50"></i>
                                                    <span class="line-clamp-2" style="font-size: 0.50rem; line-height: 1;">{{ Str::limit($topBook->title, 10) }}</span>
                                                </div>
                                            @endif
                                            <span class="badge bg-danger position-absolute top-0 end-0 translate-middle-y shadow-sm" style="font-size: 0.52rem; padding: 2px 4px;">Top</span>
                                        </div>
                                    @else
                                        <div class="book-preview-thumb border border-dashed d-flex flex-column align-items-center justify-content-center text-muted bg-light" 
                                             style="font-size: 0.60rem;" title="এখনো কোনো বই ক্যাটালগে যুক্ত হয়নি">
                                            <i class="fa-solid fa-book-open opacity-30 mb-1"></i>
                                            <span style="font-size: 0.52rem;">বই নেই</span>
                                        </div>
                                    @endif
                                </div>

                            </div>

                            {{-- Card Bottom Row: Action Toolbar & Status Toggle --}}
                            <div class="d-flex align-items-center justify-content-between pt-2.5 border-top gap-1 w-100">
                                {{-- Quick Action Buttons --}}
                                <div class="d-flex align-items-center gap-1.5">
                                    <button type="button" class="action-btn-circle text-info" 
                                            onclick="openAuthorDetailsModal({{ $author->id }})" title="🪪 সম্পূর্ণ ৩৬০° প্রোফাইল">
                                        <i class="fa-solid fa-id-card"></i>
                                    </button>
                                    <button type="button" class="action-btn-circle text-primary" 
                                            onclick="openEditAuthorModal({{ $author->id }})" title="✏️ কুইক এডিট">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    <button type="button" class="action-btn-circle text-warning" 
                                            onclick="openAuthorPasswordResetModal({{ $author->id }}, '{{ addslashes($author->name) }}', '{{ addslashes($author->email ?: ($author->phone ?: '')) }}')" title="🔑 পাসওয়ার্ড রিসেট ও WhatsApp">
                                        <i class="fa-solid fa-key"></i>
                                    </button>
                                    <a href="{{ route('authors.show', $author->slug ?: $author->id) }}" target="_blank" rel="noopener" 
                                       class="action-btn-circle text-muted" title="🌐 পাবলিক পেজ দেখুন">
                                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                    </a>
                                    <button type="button" class="action-btn-circle text-danger" 
                                            onclick="handleDeleteAuthor({{ $author->id }}, '{{ addslashes($author->name) }}', {{ $booksCount }})" title="🗑️ লেখক মুছে ফেলুন">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </div>

                                {{-- AJAX Status Switch --}}
                                <div class="custom-switch-clean d-flex align-items-center" title="স্ট্যাটাস পরিবর্তন করতে ক্লিক করুন">
                                    <div class="form-check form-switch m-0 p-0">
                                        <input class="form-check-input" type="checkbox" id="switchActive-{{ $author->id }}" 
                                               @checked($author->is_active) 
                                               onchange="toggleAuthorStatusAjax({{ $author->id }}, this)">
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- 4B. REFINED TABLE VIEW --}}
        <div id="authorsTableView" class="view-container d-none">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 author-table" id="authorsTable">
                        <thead class="table-light text-muted small text-uppercase">
                            <tr>
                                <th class="ps-3" style="width: 44px;">
                                    <input class="form-check-input cursor-pointer" type="checkbox" id="selectAllTableCheckbox" onchange="toggleSelectAllTable(this)">
                                </th>
                                <th style="min-width: 240px;">লেখক ও স্লাগ</th>
                                <th>যোগাযোগ</th>
                                <th>বই সংখ্যা</th>
                                <th>ভেরিফিকেশন</th>
                                <th>স্ট্যাটাস</th>
                                <th>যুক্ত হওয়ার তারিখ</th>
                                <th class="text-end pe-3" style="min-width: 140px;">অ্যাকশন</th>
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
                                    <td class="ps-3">
                                        <input class="form-check-input author-select-checkbox cursor-pointer" type="checkbox" value="{{ $author->id }}" onchange="updateBulkSelectionState()">
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="rounded-circle overflow-hidden shadow-sm flex-shrink-0 position-relative border"
                                                 style="width: 44px; height: 44px; cursor: pointer;"
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
                                                <div class="fw-bold text-dark text-truncate small d-flex align-items-center gap-1.5">
                                                    <a href="javascript:void(0)" onclick="openAuthorDetailsModal({{ $author->id }})" class="text-decoration-none text-dark hover-primary">
                                                        {{ $author->name }}
                                                    </a>
                                                    @if($author->is_verified)
                                                        <i class="fa-solid fa-circle-check text-info" style="font-size: 12px;" title="ভেরিফাইড লেখক"></i>
                                                    @endif
                                                    @if(!empty($author->user_id))
                                                        <span class="badge border rounded-pill px-2" style="font-size: 0.62rem; background: #f3e8ff; color: #7c3aed; border-color: #ddd6fe !important;">Portal</span>
                                                    @endif
                                                </div>
                                                <div class="text-muted font-monospace d-flex align-items-center gap-1.5 mt-0.5" style="font-size: 0.74rem;">
                                                    <span>{{ $author->slug }}</span>
                                                    <i class="fa-solid fa-copy cursor-pointer text-muted hover-primary" onclick="copyToClipboard('{{ $author->slug }}', 'স্লাগ কপি করা হয়েছে!')" title="স্লাগ কপি করুন"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="min-width: 140px;">
                                        @if($author->phone)
                                            <div class="text-nowrap small mb-1" style="font-size: 0.80rem;"><i class="fa-solid fa-phone text-muted me-1.5" style="font-size: 10px;"></i>{{ $author->phone }}</div>
                                        @endif
                                        @if($author->email)
                                            <div class="text-muted small text-truncate" style="font-size: 0.76rem; max-width: 160px;" title="{{ $author->email }}"><i class="fa-solid fa-envelope text-muted me-1.5" style="font-size: 10px;"></i>{{ $author->email }}</div>
                                        @endif
                                        @if(!$author->phone && !$author->email)
                                            <span class="text-muted small">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1 rounded-pill" style="font-size: 0.74rem;">
                                            <i class="fa-solid fa-book me-1"></i>{{ $booksCount }} টি বই
                                        </span>
                                    </td>
                                    <td>
                                        <button type="button" 
                                                class="badge rounded-pill border-0 shadow-sm cursor-pointer px-3 py-1.5 {{ $author->is_verified ? 'bg-info text-white' : 'bg-light text-muted border' }}"
                                                style="font-size: 0.72rem;"
                                                onclick="toggleAuthorVerifiedAjax({{ $author->id }}, this)"
                                                title="ভেরিফিকেশন টগল করুন">
                                            <i class="fas {{ $author->is_verified ? 'fa-certificate' : 'fa-circle-question' }} me-1"></i>
                                            <span>{{ $author->is_verified ? 'Verified' : 'Unverified' }}</span>
                                        </button>
                                    </td>
                                    <td>
                                        <button type="button" 
                                                class="badge rounded-pill border-0 shadow-sm cursor-pointer px-3 py-1.5 {{ $author->is_active ? 'bg-success text-white' : 'bg-secondary text-white' }}"
                                                style="font-size: 0.72rem;"
                                                id="tableStatusBadge-{{ $author->id }}"
                                                onclick="toggleAuthorStatusTableAjax({{ $author->id }}, this)"
                                                title="স্ট্যাটাস টগল করুন">
                                            <i class="fa-solid fa-circle-dot me-1" style="font-size: 7px;"></i>
                                            <span>{{ $author->is_active ? 'সক্রিয়' : 'নিষ্ক্রিয়' }}</span>
                                        </button>
                                    </td>
                                    <td class="text-muted small" style="font-size: 0.76rem;">{{ $author->created_at ? $author->created_at->format('d M, Y') : '—' }}</td>
                                    <td class="text-end pe-3">
                                        <div class="d-inline-flex gap-1.5 align-items-center">
                                            <button type="button" class="btn btn-xs btn-outline-info p-1.5 rounded-circle" onclick="openAuthorDetailsModal({{ $author->id }})" title="প্রোফাইল ৩৬০°">
                                                <i class="fa-solid fa-eye small"></i>
                                            </button>
                                            <button type="button" class="btn btn-xs btn-outline-primary p-1.5 rounded-circle" onclick="openEditAuthorModal({{ $author->id }})" title="এডিট">
                                                <i class="fa-solid fa-pen-to-square small"></i>
                                            </button>
                                            <button type="button" class="btn btn-xs btn-outline-warning p-1.5 rounded-circle" onclick="openAuthorPasswordResetModal({{ $author->id }}, '{{ addslashes($author->name) }}', '{{ addslashes($author->email ?: ($author->phone ?: '')) }}')" title="পাসওয়ার্ড রিসেট">
                                                <i class="fa-solid fa-key small"></i>
                                            </button>
                                            <a href="{{ route('authors.show', $author->slug ?: $author->id) }}" target="_blank" rel="noopener" class="btn btn-xs btn-light border p-1.5 rounded-circle" title="পাবলিক পেজ">
                                                <i class="fa-solid fa-arrow-up-right-from-square text-muted small"></i>
                                            </a>
                                            <button type="button" class="btn btn-xs btn-outline-danger p-1.5 rounded-circle" onclick="handleDeleteAuthor({{ $author->id }}, '{{ addslashes($author->name) }}', {{ $booksCount }})" title="ডিলিট">
                                                <i class="fa-solid fa-trash-can small"></i>
                                            </button>
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
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 p-3 bg-white border-0 shadow-sm rounded-4 mt-3">
                <span class="text-muted small" style="font-size: 0.82rem;">
                    মোট {{ number_format($stats['total'] ?? $authors->total()) }} জনের মধ্যে {{ $authors->firstItem() }}–{{ $authors->lastItem() }} প্রদর্শিত হচ্ছে
                </span>
                <div>
                    {{ $authors->links() }}
                </div>
            </div>
        @endif

    @endif
</div>

{{-- ========================================================================= --}}
{{-- 5. MODALS (ADD, EDIT, DETAILS 360°, PASSWORD RESET, AVATAR LIGHTBOX)      --}}
{{-- ========================================================================= --}}

{{-- Modal 1: Add Author --}}
<div class="modal fade" id="addAuthorModal" tabindex="-1" aria-labelledby="addAuthorModalLabel" aria-hidden="true" data-bs-backdrop="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-light border-0 py-3 px-4">
                <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2" id="addAuthorModalLabel">
                    <span class="rounded-circle bg-primary-subtle text-primary p-2 d-inline-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                        <i class="fa-solid fa-user-pen small"></i>
                    </span>
                    <span>নতুন লেখক প্রোফাইল তৈরি করুন</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addAuthorForm" onsubmit="submitAddAuthor(event)" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-7">
                            <label class="form-label small fw-bold text-dark">লেখকের নাম (Author Name) <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="যেমন: কাজী নজরুল ইসলাম" required oninput="generateSlugPreview(this.value, 'addAuthorSlug')">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small fw-bold text-dark">ইউআরএল স্লাগ (URL Slug)</label>
                            <input type="text" name="slug" id="addAuthorSlug" class="form-control font-monospace" placeholder="kazi-nazrul-islam">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">মোবাইল ফোন নম্বর</label>
                            <input type="text" name="phone" class="form-control" placeholder="01XXXXXXXXX">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">ইমেইল অ্যাড্রেস</label>
                            <input type="email" name="email" class="form-control" placeholder="author@example.com">
                        </div>

                        <div class="col-md-12">
                            <label class="form-label small fw-bold text-dark">ব্যক্তিগত ওয়েবসাইট / সোশ্যাল লিঙ্ক</label>
                            <input type="url" name="website" class="form-control" placeholder="https://...">
                        </div>

                        <div class="col-md-12">
                            <label class="form-label small fw-bold text-dark">লেখকের পরিচিতি ও বায়োগ্রাফি (Bio)</label>
                            <textarea name="bio" class="form-control" rows="3" placeholder="লেখকের জীবনবৃত্তান্ত, সাহিত্যকর্ম ও পরিচিতি..."></textarea>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label small fw-bold text-dark">লেখকের প্রোফাইল ছবি (Avatar Photo)</label>
                            <div class="d-flex align-items-center gap-3 p-3 rounded-3 bg-light border">
                                <div class="rounded-circle overflow-hidden bg-white border border-2 border-primary-subtle d-flex align-items-center justify-content-center shadow-sm flex-shrink-0" 
                                     style="width: 58px; height: 58px;" id="addAvatarPreviewBox">
                                    <i class="fa-solid fa-camera text-muted fs-4"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <input type="file" name="avatar_file" class="form-control form-control-sm" accept="image/*" onchange="previewImageInput(this, 'addAvatarPreviewBox')">
                                    <div class="form-text small text-muted">JPG, PNG বা WebP ফরম্যাট (সর্বোচ্চ ৪MB)।</div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="is_active" id="addAuthorActive" value="1" checked>
                                <label class="form-check-label small fw-semibold" for="addAuthorActive">বুকশপে সক্রিয় রাখুন (Active)</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="is_verified" id="addAuthorVerified" value="1">
                                <label class="form-check-label small fw-semibold" for="addAuthorVerified">ভেরিফাইড লেখক (Verified Badge)</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 py-3 px-4">
                    <button type="button" class="btn btn-light border rounded-pill px-4" data-bs-dismiss="modal">বাতিল</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm d-inline-flex align-items-center gap-1" id="btnAddAuthorSubmit">
                        <i class="fa-solid fa-check"></i> <span>সংরক্ষণ করুন</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal 2: Edit Author --}}
<div class="modal fade" id="editAuthorModal" tabindex="-1" aria-labelledby="editAuthorModalLabel" aria-hidden="true" data-bs-backdrop="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-light border-0 py-3 px-4">
                <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2" id="editAuthorModalLabel">
                    <span class="rounded-circle bg-primary-subtle text-primary p-2 d-inline-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                        <i class="fa-solid fa-pen-to-square small"></i>
                    </span>
                    <span>লেখকের তথ্য সম্পাদনা করুন</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editAuthorForm" onsubmit="submitEditAuthor(event)" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="author_id" id="editAuthorId">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-7">
                            <label class="form-label small fw-bold text-dark">লেখকের নাম <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="editAuthorName" class="form-control" required>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small fw-bold text-dark">ইউআরএল স্লাগ</label>
                            <input type="text" name="slug" id="editAuthorSlug" class="form-control font-monospace">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">মোবাইল ফোন নম্বর</label>
                            <input type="text" name="phone" id="editAuthorPhone" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">ইমেইল অ্যাড্রেস</label>
                            <input type="email" name="email" id="editAuthorEmail" class="form-control">
                        </div>

                        <div class="col-md-12">
                            <label class="form-label small fw-bold text-dark">ব্যক্তিগত ওয়েবসাইট / লিঙ্ক</label>
                            <input type="url" name="website" id="editAuthorWebsite" class="form-control">
                        </div>

                        <div class="col-md-12">
                            <label class="form-label small fw-bold text-dark">লেখকের পরিচিতি (Bio)</label>
                            <textarea name="bio" id="editAuthorBio" class="form-control" rows="3"></textarea>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label small fw-bold text-dark">প্রোফাইল ছবি পরিবর্তন করুন</label>
                            <div class="d-flex align-items-center gap-3 p-3 rounded-3 bg-light border">
                                <div class="rounded-circle overflow-hidden bg-white border border-2 border-primary-subtle d-flex align-items-center justify-content-center shadow-sm flex-shrink-0" 
                                     style="width: 58px; height: 58px;" id="editAvatarPreviewBox">
                                    <i class="fa-solid fa-user text-muted fs-4"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <input type="file" name="avatar_file" class="form-control form-control-sm" accept="image/*" onchange="previewImageInput(this, 'editAvatarPreviewBox')">
                                    <div class="form-text small text-muted">নতুন ছবি আপলোড করতে নির্বাচন করুন।</div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="is_active" id="editAuthorActive" value="1">
                                <label class="form-check-label small fw-semibold" for="editAuthorActive">সক্রিয় রাখুন (Active)</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="is_verified" id="editAuthorVerified" value="1">
                                <label class="form-check-label small fw-semibold" for="editAuthorVerified">ভেরিফাইড লেখক (Verified)</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 py-3 px-4">
                    <button type="button" class="btn btn-light border rounded-pill px-4" data-bs-dismiss="modal">বাতিল</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm d-inline-flex align-items-center gap-1" id="btnEditAuthorSubmit">
                        <i class="fa-solid fa-save"></i> <span>পরিবর্তন সংরক্ষণ</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal 3: Author 360° Profile Details --}}
<div class="modal fade" id="authorDetailsModal" tabindex="-1" aria-labelledby="authorDetailsModalLabel" aria-hidden="true" data-bs-backdrop="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-primary text-white border-0 py-3 px-4">
                <h5 class="modal-title fw-bold text-white d-flex align-items-center gap-2" id="authorDetailsModalLabel">
                    <i class="fa-solid fa-address-card"></i> <span>লেখক প্রোফাইল ৩৬০° ভিউ</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="authorDetailsContent">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="text-muted small mt-2">লেখকের সম্পূর্ণ তথ্য লোড হচ্ছে...</p>
                </div>
            </div>
            <div class="modal-footer bg-light border-0 py-3 px-4">
                <button type="button" class="btn btn-secondary btn-sm rounded-pill px-4" data-bs-dismiss="modal">বন্ধ করুন</button>
            </div>
        </div>
    </div>
</div>

{{-- Modal 4: Reset Password & WhatsApp Credential Share --}}
<div class="modal fade" id="authorPasswordResetModal" tabindex="-1" aria-labelledby="authorPasswordResetModalLabel" aria-hidden="true" data-bs-backdrop="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-light border-0 py-3 px-4">
                <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2" id="authorPasswordResetModalLabel">
                    <span class="rounded-circle bg-warning-subtle text-warning-emphasis p-2 d-inline-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                        <i class="fa-solid fa-key small"></i>
                    </span>
                    <span>লেখক পোর্টাল পাসওয়ার্ড রিসেট</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="authorPasswordResetForm" onsubmit="submitAuthorPasswordReset(event)">
                @csrf
                <input type="hidden" id="resetAuthorId">
                <div class="modal-body p-4">
                    <p class="text-muted small mb-3">
                        <strong class="text-dark" id="resetAuthorNameTitle">লেখক</strong> এর লেখক পোর্টালে লগইন করার জন্য নতুন পাসওয়ার্ড নির্ধারণ বা স্বয়ংক্রিয়ভাবে জেনারেট করুন।
                    </p>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">নতুন পাসওয়ার্ড</label>
                        <div class="input-group">
                            <input type="text" id="resetNewPassword" class="form-control font-monospace" placeholder="নতুন পাসওয়ার্ড লিখুন বা তৈরি করুন...">
                            <button class="btn btn-outline-secondary" type="button" onclick="generateRandomPassword()" title="অটো জেনারেট">
                                <i class="fa-solid fa-wand-magic-sparkles me-1"></i> জেনারেট
                            </button>
                        </div>
                    </div>

                    {{-- Result Area after successful reset --}}
                    <div id="resetSuccessBox" class="alert alert-success d-none rounded-3 border-0 p-3 mb-0">
                        <div class="fw-bold mb-1 text-success d-flex align-items-center gap-1.5">
                            <i class="fa-solid fa-circle-check"></i> <span>পাসওয়ার্ড সফলভাবে আপডেট হয়েছে!</span>
                        </div>
                        <div class="small text-dark mb-2">
                            <strong>ইউজারনেম:</strong> <span id="resLoginId" class="font-monospace"></span><br>
                            <strong>পাসওয়ার্ড:</strong> <span id="resPassword" class="font-monospace fw-bold text-primary"></span>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="btn btn-xs btn-outline-dark rounded-pill" onclick="copyPasswordDetails()">
                                <i class="fa-solid fa-copy me-1"></i> তথ্য কপি করুন
                            </button>
                            <a href="#" target="_blank" id="btnWhatsappShare" class="btn btn-xs btn-success rounded-pill d-none">
                                <i class="fa-brands fa-whatsapp me-1"></i> WhatsApp-এ পাঠান
                            </a>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 py-3 px-4">
                    <button type="button" class="btn btn-light border rounded-pill px-4" data-bs-dismiss="modal">বন্ধ করুন</button>
                    <button type="submit" class="btn btn-warning text-dark fw-bold rounded-pill px-4 shadow-sm d-inline-flex align-items-center gap-1" id="btnSubmitResetPass">
                        <i class="fa-solid fa-key"></i> <span>পাসওয়ার্ড সেট করুন</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal 5: Avatar Lightbox Preview --}}
<div class="modal fade" id="avatarLightboxModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 bg-transparent text-center">
            <div class="position-relative d-inline-block mx-auto">
                <img id="lightboxImg" src="" alt="Author Avatar" class="rounded-4 shadow-lg border border-3 border-white object-fit-cover" style="max-width: 260px; max-height: 260px;">
                <h6 id="lightboxTitle" class="text-white fw-bold mt-2 text-shadow"></h6>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// ── Core Dynamic Author Management JS ──
const csrfToken = '{{ csrf_token() }}';

// 1. Toast / Alert Utility
function showToast(message, type = 'success') {
    if (typeof window.SwalToast === 'function') {
        window.SwalToast(type, message);
    } else {
        alert(message);
    }
}

// 2. View Mode Switcher with LocalStorage persistence
window.switchViewMode = function(mode) {
    const gridView = document.getElementById('authorsGridView');
    const tableView = document.getElementById('authorsTableView');
    const btnGrid = document.getElementById('btnViewGrid');
    const btnTable = document.getElementById('btnViewTable');

    if (mode === 'table') {
        if (gridView) gridView.classList.add('d-none');
        if (tableView) tableView.classList.remove('d-none');
        if (btnGrid) btnGrid.classList.remove('active');
        if (btnTable) btnTable.classList.add('active');
        localStorage.setItem('author_view_mode', 'table');
    } else {
        if (tableView) tableView.classList.add('d-none');
        if (gridView) gridView.classList.remove('d-none');
        if (btnTable) btnTable.classList.remove('active');
        if (btnGrid) btnGrid.classList.add('active');
        localStorage.setItem('author_view_mode', 'grid');
    }
};

// Restore user view preference
document.addEventListener('DOMContentLoaded', () => {
    const savedMode = localStorage.getItem('author_view_mode');
    if (savedMode === 'table') {
        window.switchViewMode('table');
    }
});

// 3. Live Client-Side Quick Search Debounce
let searchTimeout;
const searchInput = document.getElementById('authorSearchInput');
if (searchInput) {
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim().toLowerCase();
        
        // Instant client-side DOM filter on cards
        const cards = document.querySelectorAll('.author-item-wrapper');
        cards.forEach(card => {
            const authorData = card.getAttribute('data-author-name') || '';
            const authorPhone = card.getAttribute('data-author-phone') || '';
            if (!query || authorData.includes(query) || authorPhone.includes(query)) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });

        // Server-side submit after 600ms of inactivity
        searchTimeout = setTimeout(() => {
            const filterForm = document.getElementById('authorsFilterForm');
            if (filterForm) filterForm.submit();
        }, 650);
    });
}

// 4. Slug Generator
window.generateSlugPreview = function(name, targetId) {
    if (!name) return;
    const target = document.getElementById(targetId);
    if (!target) return;
    
    let slug = name.toLowerCase()
        .replace(/[\s\.\,\_\-\/\\]+/g, '-')
        .replace(/[^\w\u0980-\u09FF\-]+/g, '')
        .replace(/\-\-+/g, '-')
        .replace(/^-+|-+$/g, '');
    
    target.value = slug;
};

// 5. Image Input Preview
window.previewImageInput = function(input, previewBoxId) {
    const box = document.getElementById(previewBoxId);
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            box.innerHTML = `<img src="${e.target.result}" class="w-100 h-100 object-fit-cover rounded-circle">`;
        };
        reader.readAsDataURL(input.files[0]);
    }
};

// 6. Lightbox Avatar Preview
window.previewAuthorAvatar = function(url, name) {
    if (!url) return;
    const imgEl = document.getElementById('lightboxImg');
    const titleEl = document.getElementById('lightboxTitle');
    if (imgEl) imgEl.src = url;
    if (titleEl) titleEl.textContent = name;
    const modalEl = document.getElementById('avatarLightboxModal');
    if (modalEl && typeof bootstrap !== 'undefined') {
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }
};

// 7. Clipboard Copy Helper
window.copyToClipboard = function(text, message = 'কপি করা হয়েছে!') {
    navigator.clipboard.writeText(text).then(() => {
        showToast(message, 'success');
    }).catch(() => {
        showToast('কপি করতে ব্যর্থ হয়েছে!', 'error');
    });
};

// 8. Open Add Author Modal
window.openAddAuthorModal = function() {
    const form = document.getElementById('addAuthorForm');
    if (form) form.reset();
    const box = document.getElementById('addAvatarPreviewBox');
    if (box) box.innerHTML = '<i class="fa-solid fa-camera text-muted fs-4"></i>';
    const modalEl = document.getElementById('addAuthorModal');
    if (modalEl && typeof bootstrap !== 'undefined') {
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }
};

// 9. Submit Add Author
window.submitAddAuthor = async function(e) {
    e.preventDefault();
    const form = e.target;
    const btn = document.getElementById('btnAddAuthorSubmit');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> সংরক্ষণ হচ্ছে...';
    }

    const formData = new FormData(form);

    try {
        const response = await fetch("{{ route('admin.authors.quick-store') }}", {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken
            }
        });

        const data = await response.json();
        if (data.success) {
            showToast(data.message || 'লেখক সফলভাবে সংরক্ষিত হয়েছে!');
            const modalEl = document.getElementById('addAuthorModal');
            if (modalEl && typeof bootstrap !== 'undefined') {
                bootstrap.Modal.getOrCreateInstance(modalEl).hide();
            }
            setTimeout(() => window.location.reload(), 700);
        } else {
            showToast(data.message || 'সংরক্ষণ ব্যর্থ হয়েছে!', 'error');
        }
    } catch (err) {
        showToast('সার্ভার ত্রুটি ঘটেছে!', 'error');
    } finally {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-check"></i> <span>সংরক্ষণ করুন</span>';
        }
    }
};

// 10. Open Edit Author Modal
window.openEditAuthorModal = async function(id) {
    try {
        const res = await fetch(`/admin/authors/${id}/details`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await res.json();
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
            
            const previewBox = document.getElementById('editAvatarPreviewBox');
            if (previewBox) {
                if (a.avatar_url) {
                    previewBox.innerHTML = `<img src="${a.avatar_url}" class="w-100 h-100 object-fit-cover rounded-circle">`;
                } else {
                    previewBox.innerHTML = '<i class="fa-solid fa-user text-muted fs-4"></i>';
                }
            }

            const modalEl = document.getElementById('editAuthorModal');
            if (modalEl && typeof bootstrap !== 'undefined') {
                bootstrap.Modal.getOrCreateInstance(modalEl).show();
            }
        }
    } catch (err) {
        showToast('লেখকের তথ্য আনতে ব্যর্থ হয়েছে!', 'error');
    }
};

// 11. Submit Edit Author
window.submitEditAuthor = async function(e) {
    e.preventDefault();
    const form = e.target;
    const authorId = document.getElementById('editAuthorId').value;
    const btn = document.getElementById('btnEditAuthorSubmit');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> আপডেট হচ্ছে...';
    }

    const formData = new FormData(form);

    try {
        const response = await fetch(`/admin/authors/${authorId}/quick-update`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken
            }
        });

        const data = await response.json();
        if (data.success) {
            showToast(data.message || 'লেখকের তথ্য সফলভাবে আপডেট হয়েছে!');
            const modalEl = document.getElementById('editAuthorModal');
            if (modalEl && typeof bootstrap !== 'undefined') {
                bootstrap.Modal.getOrCreateInstance(modalEl).hide();
            }
            setTimeout(() => window.location.reload(), 700);
        } else {
            showToast(data.message || 'আপডেট ব্যর্থ হয়েছে!', 'error');
        }
    } catch (err) {
        showToast('সার্ভার ত্রুটি ঘটেছে!', 'error');
    } finally {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-save"></i> <span>পরিবর্তন সংরক্ষণ</span>';
        }
    }
};

// 12. Instant AJAX Status Toggle on Card Switch
window.toggleAuthorStatusAjax = async function(id, checkbox) {
    const isChecked = checkbox.checked;
    const statusDot = document.getElementById(`statusDot-${id}`);
    
    try {
        const response = await fetch(`/admin/authors/${id}/toggle-status`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json'
            }
        });
        const data = await response.json();
        if (data.success) {
            showToast(data.message || 'স্ট্যাটাস সফলভাবে পরিবর্তিত হয়েছে!');
            if (statusDot) {
                statusDot.className = `author-status-dot ${data.is_active ? 'bg-success' : 'bg-secondary'}`;
                statusDot.title = data.is_active ? 'সক্রিয় (Active)' : 'নিষ্ক্রিয় (Inactive)';
            }
        } else {
            checkbox.checked = !isChecked;
            showToast('স্ট্যাটাস পরিবর্তনে সমস্যা হয়েছে!', 'error');
        }
    } catch (err) {
        checkbox.checked = !isChecked;
        showToast('সার্ভার ত্রুটি ঘটেছে!', 'error');
    }
};

// 13. Instant AJAX Status Toggle on Table Badge
window.toggleAuthorStatusTableAjax = async function(id, btn) {
    try {
        const response = await fetch(`/admin/authors/${id}/toggle-status`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json'
            }
        });
        const data = await response.json();
        if (data.success) {
            showToast(data.message || 'স্ট্যাটাস পরিবর্তিত হয়েছে!');
            btn.className = `badge rounded-pill border-0 shadow-sm cursor-pointer px-3 py-1.5 ${data.is_active ? 'bg-success text-white' : 'bg-secondary text-white'}`;
            btn.innerHTML = `<i class="fa-solid fa-circle-dot me-1" style="font-size: 7px;"></i> <span>${data.is_active ? 'সক্রিয়' : 'নিষ্ক্রিয়'}</span>`;
        }
    } catch (err) {
        showToast('সার্ভার ত্রুটি ঘটেছে!', 'error');
    }
};

// 14. Instant AJAX Verification Toggle
window.toggleAuthorVerifiedAjax = async function(id, btn) {
    try {
        const response = await fetch(`/admin/authors/${id}/toggle-verified`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json'
            }
        });
        const data = await response.json();
        if (data.success) {
            showToast(data.message || 'ভেরিফিকেশন পরিবর্তিত হয়েছে!');
            btn.className = `badge rounded-pill border-0 shadow-sm cursor-pointer px-3 py-1.5 ${data.is_verified ? 'bg-info text-white' : 'bg-light text-muted border'}`;
            btn.innerHTML = `<i class="fas ${data.is_verified ? 'fa-certificate' : 'fa-circle-question'} me-1"></i> <span>${data.is_verified ? 'Verified' : 'Unverified'}</span>`;
        }
    } catch (err) {
        showToast('সার্ভার ত্রুটি ঘটেছে!', 'error');
    }
};

// 15. Open 360° Author Details Modal
window.openAuthorDetailsModal = async function(id) {
    const modalEl = document.getElementById('authorDetailsModal');
    const content = document.getElementById('authorDetailsContent');
    if (!modalEl || !content) return;

    content.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="text-muted small mt-2">লেখকের সম্পূর্ণ তথ্য লোড হচ্ছে...</p>
        </div>`;
    
    if (typeof bootstrap !== 'undefined') {
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }

    try {
        const res = await fetch(`/admin/authors/${id}/details`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await res.json();
        if (data.success && data.author) {
            const a = data.author;
            const books = a.books || [];
            
            let booksHtml = '';
            if (books.length > 0) {
                booksHtml = `
                    <div class="row g-2 mt-2">
                        ${books.map(b => `
                            <div class="col-6 col-md-4">
                                <div class="card h-100 border rounded-3 p-2 bg-light shadow-sm">
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="${b.cover_image ? ('/storage/' + b.cover_image) : '/images/book-placeholder.png'}" 
                                             class="rounded flex-shrink-0 object-fit-cover shadow-sm" style="width: 40px; height: 55px;"
                                             onerror="this.src='/images/book-placeholder.png'">
                                        <div class="min-w-0">
                                            <h6 class="small fw-bold text-dark text-truncate mb-1" title="${b.title}">${b.title}</h6>
                                            <span class="text-success fw-bold small">৳${b.price || '0'}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `).join('')}
                    </div>`;
            } else {
                booksHtml = `<p class="text-muted small mb-0 fst-italic py-2">এখনো কোনো বই ক্যাটালগে যুক্ত হয়নি।</p>`;
            }

            content.innerHTML = `
                <div class="row g-3">
                    <div class="col-md-4 text-center border-end pe-md-3">
                        <div class="mx-auto rounded-circle overflow-hidden shadow-sm border border-3 border-primary-subtle position-relative mb-2" style="width: 90px; height: 90px;">
                            ${a.avatar_url ? `<img src="${a.avatar_url}" class="w-100 h-100 object-fit-cover">` : `<div class="w-100 h-100 d-flex align-items-center justify-content-center text-white fw-bold fs-3" style="background: ${a.avatar_bg_color || '#0284c7'};">${a.initials || 'L'}</div>`}
                        </div>
                        <h5 class="fw-bold text-dark mb-1">${a.name}</h5>
                        <p class="text-muted font-monospace small mb-2">/${a.slug}</p>
                        
                        <div class="d-flex flex-wrap justify-content-center gap-1.5 mb-3">
                            <span class="badge ${a.is_active ? 'bg-success' : 'bg-secondary'} rounded-pill px-2.5 py-1">${a.is_active ? '🟢 সক্রিয়' : '🔴 নিষ্ক্রিয়'}</span>
                            <span class="badge ${a.is_verified ? 'bg-info' : 'bg-light text-dark border'} rounded-pill px-2.5 py-1">${a.is_verified ? '✓ ভেরিফাইড' : 'সাধারণ'}</span>
                            <span class="badge bg-primary rounded-pill px-2.5 py-1">📚 ${a.books_count || 0} টি বই</span>
                        </div>

                        <div class="text-start bg-light p-3 rounded-3 small">
                            <div class="mb-1.5"><strong class="text-muted">ফোন:</strong> ${a.phone || '—'}</div>
                            <div class="mb-1.5 text-truncate"><strong class="text-muted">ইমেইল:</strong> ${a.email || '—'}</div>
                            <div><strong class="text-muted">ওয়েবসাইট:</strong> ${a.website ? `<a href="${a.website}" target="_blank" class="text-primary text-decoration-none">ভিজিট করুন &rarr;</a>` : '—'}</div>
                        </div>
                    </div>

                    <div class="col-md-8 ps-md-3">
                        <div class="mb-3">
                            <h6 class="fw-bold text-dark mb-2 d-flex align-items-center gap-1.5">
                                <i class="fa-solid fa-align-left text-primary"></i> <span>লেখকের পরিচিতি (Bio)</span>
                            </h6>
                            <div class="p-3 bg-light rounded-3 small text-secondary" style="max-height: 130px; overflow-y: auto;">
                                ${a.bio ? a.bio.replace(/\n/g, '<br>') : 'কোনো পরিচিতি বিবরণ যুক্ত করা হয়নি।'}
                            </div>
                        </div>

                        <div>
                            <h6 class="fw-bold text-dark mb-2 d-flex align-items-center justify-content-between">
                                <span class="d-inline-flex align-items-center gap-1.5">
                                    <i class="fa-solid fa-book-open text-warning"></i> <span>প্রকাশিত বইসমূহ (${books.length})</span>
                                </span>
                                <a href="/admin/books?author_id=${a.id}" class="small text-primary text-decoration-none">সকল বই পরিচালনা &rarr;</a>
                            </h6>
                            ${booksHtml}
                        </div>
                    </div>
                </div>`;
        }
    } catch (err) {
        content.innerHTML = `<div class="alert alert-danger mb-0">লেখকের বিস্তারিত লোড করতে ব্যর্থ হয়েছে!</div>`;
    }
};

// 16. Password Reset Modal Handler
window.openAuthorPasswordResetModal = function(id, name, contact) {
    document.getElementById('resetAuthorId').value = id;
    document.getElementById('resetAuthorNameTitle').textContent = name;
    document.getElementById('resetNewPassword').value = '';
    document.getElementById('resetSuccessBox').classList.add('d-none');
    document.getElementById('btnWhatsappShare').classList.add('d-none');
    window.generateRandomPassword();
    const modalEl = document.getElementById('authorPasswordResetModal');
    if (modalEl && typeof bootstrap !== 'undefined') {
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }
};

window.generateRandomPassword = function() {
    const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
    let pass = 'Idea@';
    for (let i = 0; i < 4; i++) {
        pass += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    const input = document.getElementById('resetNewPassword');
    if (input) input.value = pass;
};

window.submitAuthorPasswordReset = async function(e) {
    e.preventDefault();
    const id = document.getElementById('resetAuthorId').value;
    const pass = document.getElementById('resetNewPassword').value;
    const btn = document.getElementById('btnSubmitResetPass');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> রিসেট হচ্ছে...';
    }

    try {
        const response = await fetch(`/admin/authors/${id}/reset-password`, {
            method: 'POST',
            body: JSON.stringify({ password: pass }),
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json'
            }
        });
        const data = await response.json();
        if (data.success) {
            showToast(data.message || 'পাসওয়ার্ড সফলভাবে রিসেট হয়েছে!');
            document.getElementById('resLoginId').textContent = data.login_identity;
            document.getElementById('resPassword').textContent = data.new_password;
            document.getElementById('resetSuccessBox').classList.remove('d-none');
            
            if (data.whatsapp_url) {
                const waBtn = document.getElementById('btnWhatsappShare');
                waBtn.href = data.whatsapp_url;
                waBtn.classList.remove('d-none');
            }
        } else {
            showToast(data.message || 'পাসওয়ার্ড রিসেট ব্যর্থ হয়েছে!', 'error');
        }
    } catch (err) {
        showToast('সার্ভার ত্রুটি ঘটেছে!', 'error');
    } finally {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-key"></i> <span>পাসওয়ার্ড সেট করুন</span>';
        }
    }
};

window.copyPasswordDetails = function() {
    const id = document.getElementById('resLoginId').textContent;
    const pass = document.getElementById('resPassword').textContent;
    const text = `আইডিয়া প্রকাশন লেখক পোর্টাল লগইন:\nইউজারনেম: ${id}\nপাসওয়ার্ড: ${pass}\nলগইন লিংক: {{ route('login') }}`;
    window.copyToClipboard(text, 'লগইন তথ্য ক্লিপবোর্ডে কপি করা হয়েছে!');
};

// 17. Safe Delete Author (NO CONFLICT with layout's confirm interceptor)
window.handleDeleteAuthor = async function(id, name, booksCount) {
    let warning = `আপনি কি নিশ্চিত যে লেখক "${name}" প্রোফাইল মুছে ফেলতে চান?`;
    if (booksCount > 0) {
        warning += `\n⚠️ সতর্কতা: এই লেখকের সাথে ${booksCount} টি বই যুক্ত রয়েছে!`;
    }

    if (typeof window.SwalConfirm === 'function') {
        const result = await window.SwalConfirm({
            title: 'লেখক মুছে ফেলার নিশ্চিতকরণ',
            text: warning,
            icon: 'warning',
            confirmButtonText: '<i class="fas fa-trash-can me-1"></i> হ্যাঁ, মুছে ফেলুন',
            cancelButtonText: 'বাতিল'
        });
        if (!result.isConfirmed) return;
    } else {
        if (!confirm(warning)) return;
    }

    try {
        const response = await fetch(`/admin/authors/${id}`, {
            method: 'DELETE',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken
            }
        });
        const data = await response.json();
        if (data.success) {
            showToast(data.message || 'লেখক সফলভাবে মুছে ফেলা হয়েছে!');
            const card = document.getElementById(`authorCard-${id}`);
            const row = document.getElementById(`authorRow-${id}`);
            if (card) card.remove();
            if (row) row.remove();
        } else {
            showToast(data.message || 'মুছে ফেলা সম্ভব হয়নি!', 'error');
        }
    } catch (err) {
        showToast('সার্ভার ত্রুটি ঘটেছে!', 'error');
    }
};

// 18. Bulk Selection & Batch Actions
window.updateBulkSelectionState = function() {
    const checkboxes = document.querySelectorAll('.author-select-checkbox:checked');
    const bar = document.getElementById('bulkActionBar');
    const countBadge = document.getElementById('selectedCountBadge');

    if (bar && countBadge) {
        if (checkboxes.length > 0) {
            bar.classList.remove('d-none');
            countBadge.textContent = `${checkboxes.length} টি নির্বাচিত`;
        } else {
            bar.classList.add('d-none');
        }
    }
};

window.selectAllAuthors = function(select = true) {
    document.querySelectorAll('.author-select-checkbox').forEach(cb => cb.checked = select);
    const tblCb = document.getElementById('selectAllTableCheckbox');
    if (tblCb) tblCb.checked = select;
    window.updateBulkSelectionState();
};

window.toggleSelectAllTable = function(masterCb) {
    window.selectAllAuthors(masterCb.checked);
};

window.executeBulkAction = async function(action) {
    const selected = Array.from(document.querySelectorAll('.author-select-checkbox:checked')).map(cb => cb.value);
    if (selected.length === 0) {
        showToast('কোনো লেখক নির্বাচন করা হয়নি!', 'error');
        return;
    }

    if (action === 'delete') {
        const confirmMsg = `আপনি কি নিশ্চিত যে নির্বাচিত ${selected.length} জন লেখককে স্থায়ীভাবে মুছে ফেলতে চান?`;
        if (typeof window.SwalConfirm === 'function') {
            const result = await window.SwalConfirm({
                title: 'বাল্ক মুছে ফেলার নিশ্চিতকরণ',
                text: confirmMsg,
                icon: 'warning',
                confirmButtonText: 'হ্যাঁ, মুছুন',
                cancelButtonText: 'বাতিল'
            });
            if (!result.isConfirmed) return;
        } else {
            if (!confirm(confirmMsg)) return;
        }
    }

    try {
        const response = await fetch("{{ route('admin.authors.bulk-action') }}", {
            method: 'POST',
            body: JSON.stringify({ action: action, ids: selected }),
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json'
            }
        });
        const data = await response.json();
        if (data.success) {
            showToast(data.message || 'বাল্ক অ্যাকশন সফল হয়েছে!');
            setTimeout(() => window.location.reload(), 700);
        } else {
            showToast(data.message || 'বাল্ক অ্যাকশন ব্যর্থ হয়েছে!', 'error');
        }
    } catch (err) {
        showToast('সার্ভার ত্রুটি ঘটেছে!', 'error');
    }
};

// 19. Export to CSV helper
window.exportAuthorsToCSV = function() {
    window.location.href = "{{ route('admin.authors') }}?export=csv";
};
</script>
@endpush
