@extends('layouts.admin')

@section('title', 'Authors')
@section('heading', 'Authors')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin-authors.css') }}?v={{ @filemtime(public_path('css/admin-authors.css')) ?: 1 }}">
@endpush

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Authors</li>
@endsection

@section('actions')
    <div class="ad-actions">
        <button type="button" class="btn btn-primary" onclick="openAddAuthorModal()">Add author</button>
        <button type="button" class="btn btn-outline-secondary" onclick="exportAuthorsToCSV()" title="Download the authors that match the current filters">Export</button>
        <a href="{{ route('authors.index') }}" class="btn btn-outline-secondary" target="_blank" rel="noopener">Public directory ↗</a>
    </div>
@endsection

@section('content')
@php
    // One source for the filter cards: label, count, the query they apply.
    $adFilters = [
        'approved'  => ['Approved',   $stats['approved'] ?? 0,   []],
        'pending'   => ['Pending',    $stats['pending'] ?? 0,    ['author_type' => 'pending']],
        'all'       => ['All',        $stats['total'] ?? 0,      ['is_active' => 'all']],
        'verified'  => ['Verified',   $stats['verified'] ?? 0,   ['is_active' => 'all', 'is_verified' => '1']],
        'books'     => ['With books', $stats['with_books'] ?? 0, ['is_active' => 'all', 'has_books' => '1']],
        'nobooks'   => ['No books',   $stats['no_books'] ?? 0,   ['is_active' => 'all', 'has_books' => '0']],
        'portal'    => ['Portal',     $stats['registered_users'] ?? 0, ['is_active' => 'all', 'author_type' => 'registered']],
    ];
    $adActive = match (true) {
        request('author_type') === 'pending' || request('is_active') === '0' => 'pending',
        request('author_type') === 'registered' => 'portal',
        request('has_books') === '1' => 'books',
        request('has_books') === '0' => 'nobooks',
        request('is_verified') === '1' => 'verified',
        request('is_active') === 'all' => 'all',
        default => 'approved',
    };
@endphp

<div class="ad-page" id="adPage" data-url="{{ route('admin.authors') }}">

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-0" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- filter cards: number + one tap filter --}}
    <nav class="ad-filters" aria-label="Author filters">
        @foreach ($adFilters as $key => [$label, $count, $params])
            <a href="{{ route('admin.authors', array_merge(request()->only(['search', 'sort', 'per_page']), $params)) }}"
               class="ad-filter {{ $adActive === $key ? 'is-active' : '' }} {{ $key === 'pending' && $count > 0 ? 'has-alert' : '' }}"
               data-ad-filter="{{ $key }}" data-params='@json($params)'>
                <span class="ad-filter__label">{{ $label }}</span>
                <span class="ad-filter__count" data-ad-stat="{{ $key }}">{{ number_format($count) }}</span>
            </a>
        @endforeach
    </nav>

    {{-- search, sort, page size, view --}}
    <form class="ad-toolbar" id="authorsFilterForm" action="{{ route('admin.authors') }}" method="GET" role="search">
        @foreach (request()->only(['is_active', 'author_type', 'has_books', 'is_verified']) as $k => $v)
            <input type="hidden" name="{{ $k }}" value="{{ $v }}" data-ad-keep>
        @endforeach
        <div class="ad-search">
            <input type="search" name="search" id="authorSearchInput" value="{{ $search }}" class="form-control" placeholder="Search name, phone, email…" autocomplete="off" aria-label="Search authors">
            <span class="ad-search__spin" aria-hidden="true"></span>
        </div>
        <select name="sort" class="form-select ad-sort" aria-label="Sort">
            @foreach (['latest' => 'Newest', 'oldest' => 'Oldest', 'name_asc' => 'Name A–Z', 'name_desc' => 'Name Z–A', 'books_desc' => 'Most books', 'books_asc' => 'Fewest books'] as $v => $l)
                <option value="{{ $v }}" @selected($sort === $v)>{{ $l }}</option>
            @endforeach
        </select>
        <select name="per_page" class="form-select ad-per" aria-label="Per page">
            @foreach ([24, 48, 96] as $pp)
                <option value="{{ $pp }}" @selected($perPage === $pp)>{{ $pp }} / page</option>
            @endforeach
        </select>
        <div class="ad-view" role="group" aria-label="View">
            <button type="button" class="ad-view__btn" data-ad-view="grid" aria-pressed="true">Cards</button>
            <button type="button" class="ad-view__btn" data-ad-view="list" aria-pressed="false">List</button>
        </div>
        <noscript><button type="submit" class="btn btn-primary">Search</button></noscript>
    </form>

    {{-- bulk bar: appears when authors are selected --}}
    <div class="ad-bulk d-none" id="bulkActionBar" role="region" aria-label="Selected authors">
        <span class="ad-bulk__count" id="selectedCountBadge">0 selected</span>
        <button type="button" class="btn btn-sm btn-light" onclick="selectAllAuthors(true)">Select page</button>
        <button type="button" class="btn btn-sm btn-light" onclick="selectAllAuthors(false)">Clear</button>
        <span class="ad-bulk__sep"></span>
        <button type="button" class="btn btn-sm btn-success" onclick="executeBulkAction('activate')">Approve</button>
        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="executeBulkAction('deactivate')">Hide</button>
        <button type="button" class="btn btn-sm btn-outline-primary" onclick="executeBulkAction('verify')">Verify</button>
        <button type="button" class="btn btn-sm btn-outline-danger" onclick="executeBulkAction('delete')">Delete</button>
    </div>

    {{-- results: cards or list, swapped in place by live filtering --}}
    <section class="ad-results" id="adResults" aria-live="polite" aria-busy="false">
        @include('admin.authors_grid_partial', ['authors' => $authors])
    </section>
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
                    <span>লেখক প্রোফাইল ৩৬০° ভিউ</span>
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

// 2. Live directory: filters, search, sort, paging and view — no page reload
(function () {
    const page = document.getElementById('adPage');
    const results = document.getElementById('adResults');
    const form = document.getElementById('authorsFilterForm');
    if (!page || !results || !form) return;
    const base = page.dataset.url;
    let ctrl = null;

    // view: cards or list, remembered in this browser
    function setView(mode) {
        mode = mode === 'list' ? 'list' : 'grid';
        results.classList.toggle('is-list', mode === 'list');
        document.querySelectorAll('[data-ad-view]').forEach(b => b.setAttribute('aria-pressed', String(b.dataset.adView === mode)));
        try { localStorage.setItem('author_view_mode', mode); } catch (e) { /* private mode */ }
    }
    window.switchViewMode = m => setView(m === 'table' ? 'list' : m);
    let saved = 'grid';
    try { saved = localStorage.getItem('author_view_mode') || 'grid'; } catch (e) { /* ignore */ }
    setView(saved === 'table' ? 'list' : saved);
    document.querySelectorAll('[data-ad-view]').forEach(b => b.addEventListener('click', () => setView(b.dataset.adView)));

    function currentParams() {
        const p = new URLSearchParams(new FormData(form));
        for (const [k, v] of [...p.entries()]) if (v === '' || (k === 'sort' && v === 'latest') || (k === 'per_page' && v === '24')) p.delete(k);
        return p;
    }

    async function load(params, push) {
        if (ctrl) ctrl.abort();
        ctrl = new AbortController();
        const q = params.toString();
        results.setAttribute('aria-busy', 'true');
        page.classList.add('is-loading');
        try {
            const res = await fetch(base + '?' + (q ? q + '&' : '') + 'ajax_filter=1', {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, signal: ctrl.signal, credentials: 'same-origin',
            });
            const data = await res.json();
            if (!res.ok || !data.success) throw new Error('load');
            results.innerHTML = data.html;
            Object.entries(data.stats || {}).forEach(([k, v]) => {
                const key = { approved: 'approved', pending: 'pending', total: 'all', verified: 'verified', with_books: 'books', no_books: 'nobooks', registered_users: 'portal' }[k];
                const el = key && document.querySelector('[data-ad-stat="' + key + '"]');
                if (el) el.textContent = Number(v).toLocaleString('en-US');
            });
            const url = base + (q ? '?' + q : '');
            if (push) history.pushState({ ad: q }, '', url); else history.replaceState({ ad: q }, '', url);
            updateBulkSelectionState();
        } catch (e) {
            if (e.name !== 'AbortError') showToast('Could not load authors. Please try again.', 'error');
        } finally {
            results.setAttribute('aria-busy', 'false');
            page.classList.remove('is-loading');
        }
    }

    // filter cards: replace the filter fields, keep search / sort / page size
    document.querySelectorAll('[data-ad-filter]').forEach(card => card.addEventListener('click', e => {
        e.preventDefault();
        form.querySelectorAll('[data-ad-keep]').forEach(i => i.remove());
        const params = JSON.parse(card.dataset.params || '{}');
        Object.entries(params).forEach(([k, v]) => {
            const i = Object.assign(document.createElement('input'), { type: 'hidden', name: k, value: v });
            i.setAttribute('data-ad-keep', '');
            form.appendChild(i);
        });
        document.querySelectorAll('[data-ad-filter]').forEach(c => c.classList.toggle('is-active', c === card));
        load(currentParams(), true);
    }));

    let t = null;
    const search = document.getElementById('authorSearchInput');
    if (search) search.addEventListener('input', () => { clearTimeout(t); t = setTimeout(() => load(currentParams(), false), 300); });
    form.addEventListener('change', e => { if (e.target.matches('select')) load(currentParams(), true); });
    form.addEventListener('submit', e => { e.preventDefault(); load(currentParams(), true); });

    // pagination links and "clear filters" inside the results
    results.addEventListener('click', e => {
        const a = e.target.closest('.ad-pages a[href]');
        if (a) {
            e.preventDefault();
            const u = new URL(a.href, location.href);
            u.searchParams.delete('ajax_filter');
            load(u.searchParams, true);
            page.scrollIntoView({ behavior: 'smooth', block: 'start' });
            return;
        }
        if (e.target.closest('[data-ad-clear]')) {
            e.preventDefault();
            location.href = base;
        }
    });

    window.addEventListener('popstate', () => location.reload());
    window.reloadAuthors = () => load(new URLSearchParams(location.search), false);
})();

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
            (typeof window.reloadAuthors === 'function' ? window.reloadAuthors() : setTimeout(() => window.location.reload(), 700));   // refresh the results only
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
            (typeof window.reloadAuthors === 'function' ? window.reloadAuthors() : setTimeout(() => window.location.reload(), 700));   // refresh the results only
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
            // the card and the list row both have a switch for this author
            document.querySelectorAll(`.ad-live-input[data-id="${id}"]`).forEach(cb => { cb.checked = !!data.is_active; });
            [document.getElementById(`authorCard-${id}`), document.getElementById(`authorRow-${id}`)].forEach(el => el && el.classList.toggle('is-off', !data.is_active));
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
            if (typeof window.reloadAuthors === 'function') window.reloadAuthors();   // tick, menu text and counts
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
                                <span>লেখকের পরিচিতি (Bio)</span>
                            </h6>
                            <div class="p-3 bg-light rounded-3 small text-secondary" style="max-height: 130px; overflow-y: auto;">
                                ${a.bio ? a.bio.replace(/\n/g, '<br>') : 'কোনো পরিচিতি বিবরণ যুক্ত করা হয়নি।'}
                            </div>
                        </div>

                        <div>
                            <h6 class="fw-bold text-dark mb-2 d-flex align-items-center justify-content-between">
                                <span class="d-inline-flex align-items-center gap-1.5">
                                    <span>প্রকাশিত বইসমূহ (${books.length})</span>
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
    const checkboxes = new Set(Array.from(document.querySelectorAll('.author-select-checkbox:checked')).map(cb => cb.value));
    const bar = document.getElementById('bulkActionBar');
    const countBadge = document.getElementById('selectedCountBadge');

    if (bar && countBadge) {
        if (checkboxes.size > 0) {
            bar.classList.remove('d-none');
            countBadge.textContent = `${checkboxes.size} selected`;
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
    // the card and the list row each have a checkbox: count every author once
    const selected = [...new Set(Array.from(document.querySelectorAll('.author-select-checkbox:checked')).map(cb => cb.value))];
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
            (typeof window.reloadAuthors === 'function' ? window.reloadAuthors() : setTimeout(() => window.location.reload(), 700));   // refresh the results only
        } else {
            showToast(data.message || 'বাল্ক অ্যাকশন ব্যর্থ হয়েছে!', 'error');
        }
    } catch (err) {
        showToast('সার্ভার ত্রুটি ঘটেছে!', 'error');
    }
};

// 19. Export to CSV helper
window.exportAuthorsToCSV = function() {
    // the authors that match the filters on screen (all pages)
    const p = new URLSearchParams(location.search);
    p.set('export', 'csv');
    p.delete('page');
    window.location.href = "{{ route('admin.authors') }}?" + p.toString();
};
</script>
@endpush
