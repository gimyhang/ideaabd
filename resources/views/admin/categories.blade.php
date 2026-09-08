@extends('layouts.admin')

@section('title', 'বইয়ের ক্যাটাগরি ব্যবস্থাপনা')
@section('heading', 'ক্যাটাগরি সমূহ')
@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">ক্যাটাগরি গ্যালারি</li>
@endsection

@section('actions')
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('admin.books') }}" class="btn btn-outline-secondary rounded-pill px-3 py-2 shadow-xs fw-semibold d-inline-flex align-items-center gap-1.5">
            <i class="fas fa-book text-muted"></i>
            <span>সকল বই দেখুন</span>
        </a>
        <a href="{{ route('admin.content.create', 'categories') }}" class="btn btn-primary rounded-pill px-3.5 py-2 shadow-sm fw-bold d-inline-flex align-items-center gap-2" style="background: linear-gradient(135deg, #2563eb, #1d4ed8); border: none;">
            <i class="fas fa-plus-circle fs-6"></i>
            <span>নতুন ক্যাটাগরি তৈরি করুন</span>
        </a>
    </div>
@endsection

@push('styles')
<style>
    /* ==========================================================================
       ULTRA-MODERN CATEGORY GALLERY & FOLDER SYSTEM
       ========================================================================== */

    /* Bento Stats Widgets */
    .bento-stat-card {
        background: #ffffff;
        border: 1px solid rgba(226, 232, 240, 0.8);
        border-radius: 20px;
        padding: 16px 18px;
        position: relative;
        overflow: hidden;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 4px 12px -2px rgba(15, 23, 42, 0.03), 0 2px 6px -1px rgba(15, 23, 42, 0.02);
    }
    .bento-stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 24px -4px rgba(15, 23, 42, 0.08);
        border-color: rgba(37, 99, 235, 0.3);
    }
    .bento-stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.35rem;
    }

    /* Filter & Search Bar */
    .filter-glass-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(226, 232, 240, 0.9);
        border-radius: 20px;
        padding: 14px 18px;
        box-shadow: 0 4px 16px -2px rgba(15, 23, 42, 0.04);
    }

    /* Responsive Grid: 8 to 10 columns on desktop */
    .cat-modern-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 16px;
    }
    @media (min-width: 576px) { .cat-modern-grid { grid-template-columns: repeat(3, 1fr); gap: 16px; } }
    @media (min-width: 768px) { .cat-modern-grid { grid-template-columns: repeat(4, 1fr); gap: 18px; } }
    @media (min-width: 992px) { .cat-modern-grid { grid-template-columns: repeat(6, 1fr); gap: 18px; } }
    @media (min-width: 1200px) { .cat-modern-grid { grid-template-columns: repeat(8, 1fr); gap: 18px; } }
    @media (min-width: 1440px) { .cat-modern-grid { grid-template-columns: repeat(9, 1fr); gap: 20px; } }
    @media (min-width: 1680px) { .cat-modern-grid { grid-template-columns: repeat(10, 1fr); gap: 20px; } }

    /* Modern Category Card */
    .cat-modern-card {
        background: linear-gradient(180deg, #ffffff 0%, #fcfdfe 100%);
        border: 1px solid rgba(226, 232, 240, 0.85);
        border-radius: 20px;
        padding: 10px 8px 8px;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        position: relative;
        transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        box-shadow: 0 2px 8px -2px rgba(15, 23, 42, 0.04), 0 1px 3px -1px rgba(15, 23, 42, 0.02);
    }
    .cat-modern-card:hover {
        transform: translateY(-5px);
        background: #ffffff;
        border-color: rgba(37, 99, 235, 0.4);
        box-shadow: 0 16px 32px -8px rgba(37, 99, 235, 0.18), 0 6px 12px -3px rgba(15, 23, 42, 0.04);
    }

    /* Top Badges (Book count chip + Active Dot) */
    .cat-top-bar {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 4px;
        padding: 0 2px;
    }
    .cat-book-chip {
        font-size: 10px;
        font-weight: 700;
        color: #475569;
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        padding: 1.5px 6px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        gap: 3px;
        line-height: 1;
        transition: all 0.2s ease;
    }
    .cat-modern-card:hover .cat-book-chip {
        background: #eff6ff;
        color: #2563eb;
        border-color: #bfdbfe;
    }
    .cat-live-dot {
        width: 9px;
        height: 9px;
        border-radius: 50%;
        position: relative;
    }
    .cat-live-dot.active {
        background-color: #10b981;
        box-shadow: 0 0 0 2.5px rgba(16, 185, 129, 0.2);
    }
    .cat-live-dot.inactive {
        background-color: #cbd5e1;
    }

    /* 100px Full-Bleed Folder Cover Stage */
    .cat-stage-box {
        width: 100px;
        height: 100px;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 6px;
        border-radius: 14px;
        overflow: hidden;
        background: #f1f5f9;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.08);
        border: 2px solid #ffffff;
        cursor: pointer;
    }

    /* Multi-Slide Item inside 100px Box */
    .cat-slide-wrap {
        width: 100%;
        height: 100%;
        position: relative;
    }
    .cat-book-img {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        opacity: 0;
        visibility: hidden;
        transform: scale(0.96);
        transition: opacity 0.5s ease, transform 0.5s ease, visibility 0.5s ease;
    }
    .cat-book-img.active {
        opacity: 1;
        visibility: visible;
        transform: scale(1);
    }
    .cat-modern-card:hover .cat-book-img.active {
        transform: scale(1.06);
    }

    /* Multi-cover Badge Pill */
    .cat-slide-badge {
        position: absolute;
        bottom: 4px;
        left: 50%;
        transform: translateX(-50%);
        background: rgba(15, 23, 42, 0.75);
        backdrop-filter: blur(4px);
        color: #ffffff;
        font-size: 8.5px;
        font-weight: 700;
        padding: 1px 5px;
        border-radius: 8px;
        z-index: 3;
        letter-spacing: 0.3px;
        pointer-events: none;
    }

    /* Slide Navigation Arrow Pills */
    .cat-nav-pill {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: rgba(15, 23, 42, 0.85);
        backdrop-filter: blur(4px);
        color: #ffffff;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 8.5px;
        cursor: pointer;
        opacity: 0;
        visibility: hidden;
        transition: all 0.2s ease;
        z-index: 5;
        padding: 0;
    }
    .cat-nav-pill.prev { left: 3px; }
    .cat-nav-pill.next { right: 3px; }
    .cat-modern-card:hover .cat-nav-pill {
        opacity: 1;
        visibility: visible;
    }
    .cat-nav-pill:hover {
        background: #2563eb;
        transform: translateY(-50%) scale(1.15);
    }

    /* Category Name Typography - Padding 0 underneath */
    .cat-title-text {
        font-family: 'Hind Siliguri', sans-serif;
        font-weight: 700;
        font-size: 0.88rem;
        line-height: 1.22;
        color: #0f172a;
        text-decoration: none;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        min-height: 34px;
        max-height: 34px;
        width: 100%;
        word-break: break-word;
        transition: color 0.15s ease;
        margin-bottom: 0px !important;
        padding-bottom: 0px !important;
        margin-top: 2px;
    }
    .cat-modern-card:hover .cat-title-text {
        color: #2563eb;
    }

    /* Parent / Hierarchy Badge */
    .cat-parent-tag {
        font-size: 10px;
        padding: 2px 7px;
        border-radius: 6px;
        font-weight: 600;
        max-width: 100%;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        display: inline-block;
        margin-top: 3px;
        margin-bottom: 0px !important;
        padding-bottom: 2px !important;
        line-height: 1.15;
    }

    /* Action Buttons Row */
    .cat-actions-bar {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding-top: 6px;
        border-top: 1px solid rgba(241, 245, 249, 0.9);
        width: 100%;
        margin-top: 6px;
    }
    .cat-action-circle {
        width: 28px;
        height: 28px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        border: 1px solid rgba(226, 232, 240, 0.8);
        background: #ffffff;
        color: #64748b;
        text-decoration: none;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        padding: 0;
    }
    .cat-action-circle:hover {
        transform: translateY(-2px) scale(1.08);
    }
    .cat-action-circle.act-view:hover {
        color: #0d9488;
        border-color: #99f6e4;
        background: #f0fdfa;
        box-shadow: 0 4px 10px rgba(13, 148, 136, 0.15);
    }
    .cat-action-circle.act-edit:hover {
        color: #2563eb;
        border-color: #bfdbfe;
        background: #eff6ff;
        box-shadow: 0 4px 10px rgba(37, 99, 235, 0.15);
    }
    .cat-action-circle.act-del:hover {
        color: #e11d48;
        border-color: #fecdd3;
        background: #fff1f2;
        box-shadow: 0 4px 10px rgba(225, 29, 72, 0.15);
    }

    /* View Mode Switcher */
    .view-pill-btn {
        padding: 6px 14px;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 600;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        color: #64748b;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .view-pill-btn.active {
        background: #2563eb;
        color: #ffffff;
        border-color: #2563eb;
        box-shadow: 0 4px 10px rgba(37, 99, 235, 0.25);
    }

    /* Dark Mode Theme */
    .dark-mode .bento-stat-card,
    .dark-mode .filter-glass-card {
        background: #1e293b;
        border-color: #334155;
    }
    .dark-mode .cat-modern-card {
        background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
        border-color: #334155;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
    }
    .dark-mode .cat-modern-card:hover {
        border-color: #3b82f6;
        box-shadow: 0 16px 32px -8px rgba(0, 0, 0, 0.6);
    }
    .dark-mode .cat-title-text { color: #f8fafc; }
    .dark-mode .cat-title-text:hover { color: #60a5fa; }
    .dark-mode .cat-book-chip {
        background: #334155;
        color: #cbd5e1;
        border-color: #475569;
    }
    .dark-mode .cat-stage-box {
        background: #0f172a;
        border-color: #334155;
    }
    .dark-mode .cat-actions-bar {
        border-top-color: #334155;
    }
    .dark-mode .cat-action-circle {
        background: #0f172a;
        border-color: #334155;
        color: #94a3b8;
    }
    .dark-mode .view-pill-btn {
        background: #1e293b;
        border-color: #334155;
        color: #94a3b8;
    }
    .dark-mode .view-pill-btn.active {
        background: #3b82f6;
        color: #ffffff;
        border-color: #3b82f6;
    }
</style>
@endpush

@section('content')

{{-- Bento Metric Cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="bento-stat-card d-flex align-items-center gap-3">
            <div class="bento-stat-icon bg-primary-subtle text-primary">
                <i class="fas fa-layer-group"></i>
            </div>
            <div>
                <div class="small text-muted fw-semibold">সর্বমোট ক্যাটাগরি</div>
                <div class="fs-4 fw-bold text-dark">@bn($stats['total']) <span class="fs-6 fw-normal text-muted">টি</span></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="bento-stat-card d-flex align-items-center gap-3">
            <div class="bento-stat-icon bg-success-subtle text-success">
                <i class="fas fa-circle-check"></i>
            </div>
            <div>
                <div class="small text-muted fw-semibold">সক্রিয় ক্যাটাগরি</div>
                <div class="fs-4 fw-bold text-success">@bn($stats['active']) <span class="fs-6 fw-normal text-muted">টি</span></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="bento-stat-card d-flex align-items-center gap-3">
            <div class="bento-stat-icon bg-warning-subtle text-warning">
                <i class="fas fa-folder-open"></i>
            </div>
            <div>
                <div class="small text-muted fw-semibold">মূল বিষয় (Parent)</div>
                <div class="fs-4 fw-bold text-warning">@bn($stats['parents']) <span class="fs-6 fw-normal text-muted">টি</span></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="bento-stat-card d-flex align-items-center gap-3">
            <div class="bento-stat-icon bg-info-subtle text-info">
                <i class="fas fa-diagram-nested"></i>
            </div>
            <div>
                <div class="small text-muted fw-semibold">উপ-ক্যাটাগরি (Sub)</div>
                <div class="fs-4 fw-bold text-info">@bn($stats['children']) <span class="fs-6 fw-normal text-muted">টি</span></div>
            </div>
        </div>
    </div>
</div>

{{-- Search & Filters Bar --}}
<div class="filter-glass-card mb-4">
    <form method="GET" action="{{ route('admin.categories') }}" id="catFilterForm" class="row g-2 align-items-center">
        <div class="col-12 col-md-4">
            <div class="input-group">
                <span class="input-group-text bg-transparent border-end-0 text-muted"><i class="fas fa-search"></i></span>
                <input type="text" name="search" class="form-control border-start-0 rounded-end-3" placeholder="ক্যাটাগরি নাম বা স্লাগ খুঁজুন..." value="{{ request('search') }}">
            </div>
        </div>
        <div class="col-6 col-md-2">
            <select name="parent_id" class="form-select rounded-3" onchange="document.getElementById('catFilterForm').submit();">
                <option value="">— সকল বিষয় —</option>
                <option value="root" @selected(request('parent_id') === 'root')>মূল বিষয় সমূহ (Root)</option>
                @foreach ($parentCategories as $pId => $pName)
                    <option value="{{ $pId }}" @selected((string)request('parent_id') === (string)$pId)>
                        উপ: {{ $pName }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-md-2">
            <select name="is_active" class="form-select rounded-3" onchange="document.getElementById('catFilterForm').submit();">
                <option value="">— সকল স্ট্যাটাস —</option>
                <option value="1" @selected(request('is_active') === '1')>সক্রিয় (Active)</option>
                <option value="0" @selected(request('is_active') === '0')>নিষ্ক্রিয় (Inactive)</option>
            </select>
        </div>
        <div class="col-6 col-md-2">
            <select name="per_page" class="form-select rounded-3" onchange="document.getElementById('catFilterForm').submit();">
                <option value="20" @selected($perPage == 20)>২০টি করে</option>
                <option value="40" @selected($perPage == 40)>৪০টি করে (ডিফল্ট)</option>
                <option value="80" @selected($perPage == 80)>৮০টি করে</option>
                <option value="100" @selected($perPage == 100)>১০০টি করে</option>
            </select>
        </div>
        <div class="col-6 col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-primary flex-grow-1 rounded-3 fw-bold">
                <i class="fas fa-filter me-1"></i> ফিল্টার
            </button>
            @if(request()->hasAny(['search', 'parent_id', 'is_active', 'per_page']))
                <a href="{{ route('admin.categories') }}" class="btn btn-outline-secondary rounded-3" title="রিসেট">
                    <i class="fas fa-rotate-left"></i>
                </a>
            @endif
        </div>
    </form>
</div>

{{-- Gallery Toolbar & View Mode --}}
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 px-1">
    <div class="text-muted small fw-semibold">
        <i class="fas fa-folder-open text-primary me-1.5"></i>
        মোট <strong>@bn($categories->total())</strong>টি ক্যাটাগরির মধ্যে @bn($categories->firstItem() ?? 0) - @bn($categories->lastItem() ?? 0) প্রদর্শিত হচ্ছে
    </div>
    <div class="d-flex align-items-center gap-2">
        <span class="small text-muted me-1 d-none d-sm-inline">ভিউ মোড:</span>
        <div class="btn-group" role="group">
            <button type="button" class="view-pill-btn active" id="btnViewGrid" onclick="switchView('grid')">
                <i class="fas fa-grid-2"></i> <span>ফোল্ডার গ্যালারি</span>
            </button>
            <button type="button" class="view-pill-btn" id="btnViewTable" onclick="switchView('table')">
                <i class="fas fa-list"></i> <span>তালিকা</span>
            </button>
        </div>
    </div>
</div>

{{-- 1. MODERN FULL-BLEED FOLDER COVER GRID VIEW (8-10 Columns on Desktop) --}}
<div id="categoryGridView" class="mb-4">
    @if($categories->count() > 0)
        <div class="cat-modern-grid">
            @foreach($categories as $cat)
                @php
                    $isRoot = is_null($cat->parent_id);

                    // Collect all cover images for this category
                    $covers = collect();

                    // 1. Custom category image if set
                    if (!empty($cat->icon_or_image) && (str_starts_with($cat->icon_or_image, 'http') || str_starts_with($cat->icon_or_image, '/') || str_starts_with($cat->icon_or_image, 'storage/') || str_starts_with($cat->icon_or_image, 'assets/'))) {
                        $url = str_starts_with($cat->icon_or_image, 'http') ? $cat->icon_or_image : asset($cat->icon_or_image);
                        $covers->push([
                            'url' => $url,
                            'title' => $cat->name
                        ]);
                    }

                    // 2. Recent book cover images from recentBooks relation
                    if ($cat->recentBooks && $cat->recentBooks->isNotEmpty()) {
                        foreach ($cat->recentBooks->take(6) as $rBook) {
                            $c = $rBook->cover_image;
                            if (!empty($c)) {
                                $cUrl = str_starts_with($c, 'http') ? $c : (str_starts_with($c, 'storage/') ? asset($c) : (str_starts_with($c, '/storage/') ? asset(ltrim($c, '/')) : asset('storage/' . $c)));
                                if (!$covers->contains('url', $cUrl)) {
                                    $covers->push([
                                        'url' => $cUrl,
                                        'title' => $rBook->title ?? $cat->name
                                    ]);
                                }
                            }
                        }
                    }

                    $totalCovers = $covers->count();
                @endphp

                <div class="cat-modern-card">
                    {{-- Card Top Bar: Book Count Chip & Status Dot --}}
                    <div class="cat-top-bar">
                        <a href="{{ route('admin.books', ['category_id' => $cat->id]) }}" class="text-decoration-none" title="এই ক্যাটাগরির @bn($cat->books_count ?? 0)টি বই দেখুন">
                            <span class="cat-book-chip">
                                <i class="fas fa-book-bookmark text-primary"></i>
                                <span>@bn($cat->books_count ?? 0)</span>
                            </span>
                        </a>
                        <span class="cat-live-dot {{ $cat->is_active ? 'active' : 'inactive' }}" 
                              title="{{ $cat->is_active ? 'সক্রিয় ক্যাটাগরি' : 'নিষ্ক্রিয় ক্যাটাগরি' }}"></span>
                    </div>

                    {{-- 100px Stage: Full-bleed Cover Area --}}
                    <div class="cat-stage-box" id="stage_{{ $cat->id }}" data-total="{{ $totalCovers }}" 
                         onclick="window.location.href='{{ route('admin.content.edit', ['type' => 'categories', 'id' => $cat->id]) }}'"
                         title="{{ $cat->name }}">
                        @if($totalCovers > 0)
                            <div class="cat-slide-wrap">
                                @foreach($covers as $index => $coverItem)
                                    <img src="{{ $coverItem['url'] }}" 
                                         alt="{{ $coverItem['title'] }}" 
                                         class="cat-book-img {{ $index === 0 ? 'active' : '' }}" 
                                         data-slide-index="{{ $index }}"
                                         loading="lazy"
                                         onerror="this.style.display='none';">
                                @endforeach
                            </div>

                            @if($totalCovers > 1)
                                <span class="cat-slide-badge">
                                    <i class="fas fa-layer-group me-1 opacity-75"></i>১/@bn($totalCovers)
                                </span>
                            @endif
                        @else
                            {{-- Modern Gradient Folder Graphic (100px full area) --}}
                            <div class="w-100 h-100 d-flex flex-column align-items-center justify-content-center text-white" 
                                 style="background: {{ $isRoot ? 'linear-gradient(135deg, #f59e0b, #d97706)' : 'linear-gradient(135deg, #3b82f6, #1d4ed8)' }}; padding: 6px;">
                                <i class="fas {{ $isRoot ? 'fa-folder-open' : 'fa-folder' }} fs-4 mb-1 opacity-90"></i>
                                <span style="font-size: 8.5px; font-weight: 800; line-height: 1.1; opacity: 0.95; text-transform: uppercase;">
                                    {{ Str::limit($cat->slug, 10, '') }}
                                </span>
                            </div>
                        @endif

                        {{-- Hover Interactive Slide Arrows --}}
                        @if($totalCovers > 1)
                            <button type="button" class="cat-nav-pill prev" onclick="cycleCategorySlide({{ $cat->id }}, -1, event)" title="পূর্ববর্তী কভার">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <button type="button" class="cat-nav-pill next" onclick="cycleCategorySlide({{ $cat->id }}, 1, event)" title="পরবর্তী কভার">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        @endif
                    </div>

                    {{-- Category Title (Bengali Typography) - Padding 0 underneath --}}
                    <a href="{{ route('admin.content.edit', ['type' => 'categories', 'id' => $cat->id]) }}" 
                       class="cat-title-text" 
                       title="{{ $cat->name }} ({{ $cat->slug }})">
                        {{ $cat->name }}
                    </a>

                    {{-- Hierarchy Pill (Root or Parent Name) --}}
                    @if($cat->parent)
                        <span class="cat-parent-tag bg-info-subtle text-info border border-info-subtle" title="মূল ক্যাটাগরি: {{ $cat->parent->name }}">
                            ↳ {{ Str::limit($cat->parent->name, 12) }}
                        </span>
                    @else
                        <span class="cat-parent-tag bg-warning-subtle text-warning border border-warning-subtle">
                            মূল বিষয় (Root)
                        </span>
                    @endif

                    {{-- Action Icons Row --}}
                    <div class="cat-actions-bar">
                        {{-- View Books in Admin --}}
                        <a href="{{ route('admin.books', ['category_id' => $cat->id]) }}" 
                           class="cat-action-circle act-view" 
                           title="এই ক্যাটাগরির বইগুলো দেখুন">
                            <i class="fas fa-arrow-up-right-from-square"></i>
                        </a>

                        {{-- Edit Category --}}
                        <a href="{{ route('admin.content.edit', ['type' => 'categories', 'id' => $cat->id]) }}" 
                           class="cat-action-circle act-edit" 
                           title="সম্পাদনা করুন">
                            <i class="fas fa-pen-to-square"></i>
                        </a>

                        {{-- Delete Category --}}
                        <button type="button" 
                                onclick="confirmCategoryDelete({{ $cat->id }}, '{{ addslashes($cat->name) }}')" 
                                class="cat-action-circle act-del" 
                                title="মুছে ফেলুন">
                            <i class="fas fa-trash-can"></i>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="bento-stat-card p-5 text-center text-muted">
            <div class="mb-3">
                <i class="fas fa-folder-open fs-1 text-muted opacity-50"></i>
            </div>
            <h5 class="fw-bold text-dark">কোন ক্যাটাগরি পাওয়া যায়নি!</h5>
            <p class="small text-muted mb-3">আপনার সার্চ ফিল্টারে কোন তথ্য নেই অথবা নতুন ক্যাটাগরি তৈরি করুন।</p>
            <a href="{{ route('admin.content.create', 'categories') }}" class="btn btn-primary rounded-pill px-4">
                <i class="fas fa-plus me-1"></i> নতুন ক্যাটাগরি তৈরি করুন
            </a>
        </div>
    @endif
</div>

{{-- 2. TABLE VIEW (Alternate Mode) --}}
<div id="categoryTableView" class="bento-stat-card p-0 overflow-hidden mb-4" style="display: none;">
    <div class="table-responsive mb-0">
        <table class="table table-hover align-middle mb-0" style="font-size: 0.92rem;">
            <thead class="table-light">
                <tr>
                    <th class="py-3 px-3" style="width: 50px;">#</th>
                    <th class="py-3 px-3" style="width: 70px;">কভার</th>
                    <th class="py-3 px-3">ক্যাটাগরি নাম ও স্লাগ</th>
                    <th class="py-3 px-3">মূল বিষয় (Parent)</th>
                    <th class="py-3 px-3 text-center">সংযুক্ত বই</th>
                    <th class="py-3 px-3 text-center">ক্রম (Order)</th>
                    <th class="py-3 px-3 text-center">স্ট্যাটাস</th>
                    <th class="py-3 px-3 text-end" style="width: 140px;">অ্যাকশন</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $cat)
                    <tr>
                        <td class="px-3 text-muted">{{ $loop->iteration + ($categories->currentPage() - 1) * $categories->perPage() }}</td>
                        <td class="px-3">
                            <div class="rounded-3 overflow-hidden d-flex align-items-center justify-content-center bg-light border shadow-2xs" style="width: 44px; height: 56px;">
                                @php
                                    $firstCover = null;
                                    if (!empty($cat->icon_or_image) && (str_starts_with($cat->icon_or_image, 'http') || str_starts_with($cat->icon_or_image, '/') || str_starts_with($cat->icon_or_image, 'storage/'))) {
                                        $firstCover = str_starts_with($cat->icon_or_image, 'http') ? $cat->icon_or_image : asset($cat->icon_or_image);
                                    } elseif ($cat->recentBooks && $cat->recentBooks->isNotEmpty() && !empty($cat->recentBooks->first()->cover_image)) {
                                        $c = $cat->recentBooks->first()->cover_image;
                                        $firstCover = str_starts_with($c, 'http') ? $c : (str_starts_with($c, 'storage/') ? asset($c) : asset('storage/' . $c));
                                    }
                                @endphp
                                @if($firstCover)
                                    <img src="{{ $firstCover }}" alt="{{ $cat->name }}" class="w-100 h-100 object-fit-cover">
                                @else
                                    <i class="fas {{ $cat->parent_id ? 'fa-folder text-info' : 'fa-folder text-warning' }} fs-5"></i>
                                @endif
                            </div>
                        </td>
                        <td class="px-3">
                            <div class="fw-bold text-dark">
                                <a href="{{ route('admin.content.edit', ['type' => 'categories', 'id' => $cat->id]) }}" class="text-dark text-decoration-none hover-primary">
                                    {{ $cat->name }}
                                </a>
                            </div>
                            <small class="text-muted font-monospace">{{ $cat->slug }}</small>
                            @if($cat->description)
                                <div class="text-muted small text-truncate" style="max-width: 300px;">{{ $cat->description }}</div>
                            @endif
                        </td>
                        <td class="px-3">
                            @if($cat->parent)
                                <span class="badge bg-light text-dark border">
                                    <i class="fas fa-folder-open text-primary me-1"></i> {{ $cat->parent->name }}
                                </span>
                            @else
                                <span class="badge bg-primary-subtle text-primary border">মূল বিষয় (Root)</span>
                            @endif
                        </td>
                        <td class="px-3 text-center">
                            <a href="{{ route('admin.books', ['category_id' => $cat->id]) }}" class="badge bg-secondary-subtle text-secondary fw-semibold text-decoration-none px-2.5 py-1 rounded-pill">
                                @bn($cat->books_count ?? 0)টি বই
                            </a>
                        </td>
                        <td class="px-3 text-center text-muted">@bn($cat->sort_order ?? 0)</td>
                        <td class="px-3 text-center">
                            @if($cat->is_active)
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">সক্রিয়</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill">নিষ্ক্রিয়</span>
                            @endif
                        </td>
                        <td class="px-3 text-end">
                            <div class="d-inline-flex gap-1">
                                <a href="{{ route('admin.books', ['category_id' => $cat->id]) }}"
                                   class="btn btn-sm btn-outline-secondary py-1 px-2" title="বইগুলো দেখুন">
                                    <i class="fas fa-arrow-up-right-from-square"></i>
                                </a>
                                <a href="{{ route('admin.content.edit', ['type' => 'categories', 'id' => $cat->id]) }}"
                                   class="btn btn-sm btn-outline-primary py-1 px-2" title="সম্পাদনা">
                                    <i class="fas fa-pen-to-square"></i>
                                </a>
                                <button type="button" 
                                        onclick="confirmCategoryDelete({{ $cat->id }}, '{{ addslashes($cat->name) }}')"
                                        class="btn btn-sm btn-outline-danger py-1 px-2" title="মুছে ফেলুন">
                                    <i class="fas fa-trash-can"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="fas fa-folder-open fs-2 mb-2 d-block text-muted opacity-50"></i>
                            কোন ক্যাটাগরি পাওয়া যায়নি।
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Pagination Bar --}}
@if($categories->hasPages())
    <div class="bento-stat-card p-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="small text-muted fw-semibold">
            পৃষ্ঠা <strong>@bn($categories->currentPage())</strong> / @bn($categories->lastPage())
        </div>
        <div>
            {{ $categories->links() }}
        </div>
    </div>
@endif

{{-- Hidden Delete Form for JS Submissions --}}
<form id="globalCategoryDeleteForm" method="POST" action="" class="d-none">
    @csrf
    @method('DELETE')
</form>

@endsection

@push('scripts')
<script>
    // View Switcher (Grid vs Table)
    function switchView(mode) {
        var gridView = document.getElementById('categoryGridView');
        var tableView = document.getElementById('categoryTableView');
        var btnGrid = document.getElementById('btnViewGrid');
        var btnTable = document.getElementById('btnViewTable');

        if (mode === 'table') {
            gridView.style.display = 'none';
            tableView.style.display = 'block';
            btnTable.classList.add('active');
            btnGrid.classList.remove('active');
            localStorage.setItem('admin_cat_view_mode', 'table');
        } else {
            gridView.style.display = 'block';
            tableView.style.display = 'none';
            btnGrid.classList.add('active');
            btnTable.classList.remove('active');
            localStorage.setItem('admin_cat_view_mode', 'grid');
        }
    }

    // Restore saved view preference
    document.addEventListener('DOMContentLoaded', function () {
        var savedMode = localStorage.getItem('admin_cat_view_mode');
        if (savedMode === 'table') {
            switchView('table');
        }
    });

    // Delete confirmation handler
    function confirmCategoryDelete(catId, catName) {
        SwalConfirm({
            title: 'ক্যাটাগরি মুছে ফেলা',
            text: 'আপনি কি নিশ্চিত যে "' + catName + '" ক্যাটাগরিটি মুছে ফেলতে চান?',
            icon: 'warning',
            confirmButtonText: '<i class="fas fa-trash-can me-1"></i> হ্যাঁ, মুছে ফেলুন',
            cancelButtonText: '<i class="fas fa-times me-1"></i> বাতিল'
        }).then(function(result) {
            if (result.isConfirmed) {
                var form = document.getElementById('globalCategoryDeleteForm');
                form.action = "{{ url('admin/content/categories') }}/" + catId;
                form.submit();
            }
        });
    }

    // Interactive Slide Cycler
    function cycleCategorySlide(catId, direction, event) {
        if (event) {
            event.stopPropagation();
            event.preventDefault();
        }
        var stage = document.getElementById('stage_' + catId);
        if (!stage) return;

        var images = stage.querySelectorAll('.cat-book-img');
        if (images.length <= 1) return;

        var currentIndex = 0;
        images.forEach(function(img, idx) {
            if (img.classList.contains('active')) {
                currentIndex = idx;
            }
            img.classList.remove('active');
        });

        var nextIndex = (currentIndex + direction + images.length) % images.length;
        images[nextIndex].classList.add('active');
    }

    // Dynamic Staggered Auto-Slideshow for Category Covers
    document.addEventListener('DOMContentLoaded', function () {
        var stages = document.querySelectorAll('.cat-stage-box[data-total]');

        stages.forEach(function (stage, index) {
            var total = parseInt(stage.getAttribute('data-total') || '1', 10);
            if (total <= 1) return;

            var catId = stage.id.replace('stage_', '');
            var isHovered = false;

            var card = stage.closest('.cat-modern-card');
            if (card) {
                card.addEventListener('mouseenter', function () { isHovered = true; });
                card.addEventListener('mouseleave', function () { isHovered = false; });
            }

            // Staggered interval
            var staggerOffset = (index % 6) * 550;
            var intervalTime = 3600 + staggerOffset;

            setInterval(function () {
                if (!isHovered) {
                    cycleCategorySlide(catId, 1, null);
                }
            }, intervalTime);
        });
    });
</script>
@endpush
