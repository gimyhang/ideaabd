@extends('layouts.admin')

@section('title', 'ডাইনামিক এসইও কন্ট্রোল ও অটো-স্ক্যানার — Idea প্রকাশন')
@section('heading', 'এসইও ও অটোমেটেড ট্যাগ ম্যানেজার (Dynamic SEO Engine)')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">এসইও ম্যানেজার</li>
@endsection

@section('actions')
    <div class="d-flex flex-wrap align-items-center gap-2">
        <form action="{{ route('admin.seo.batch-scan') }}" method="POST" onsubmit="return confirm('আপনি কি নিশ্চিত যে সকল পোস্ট, বই, লেখক ও পেজের সম্পূর্ণ এসইও অটো-স্ক্যান চালাতে চান?');">
            @csrf
            <button type="submit" class="btn btn-warning btn-sm rounded-pill px-3.5 py-1.5 fw-bold d-inline-flex align-items-center gap-1.5 shadow-xs text-dark">
                <i class="fa-solid fa-wand-magic-sparkles"></i> ১-ক্লিকে অল ডাটা অটো-স্ক্যান
            </button>
        </form>
        <form action="{{ route('admin.seo.ping') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-outline-info btn-sm rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-xs">
                <i class="fa-solid fa-bolt"></i> গুগল ও বিং ইনস্ট্যান্ট পিং
            </button>
        </form>
        <a href="{{ route('admin.seo.redirects') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-xs">
            <i class="fa-solid fa-arrow-right-arrow-left"></i> ৩০১ রিডাইরেক্টস
        </a>
        <a href="{{ route('admin.seo.broken-links') }}" class="btn btn-outline-danger btn-sm rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-xs">
            <i class="fa-solid fa-triangle-exclamation"></i> ৪০৪ মনিটর
        </a>
        <a href="{{ route('admin.seo.pages') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-xs">
            <i class="fa-solid fa-file-lines"></i> পেজ এসইও
        </a>
        <a href="{{ route('admin.seo.sitemap') }}" class="btn btn-outline-success btn-sm rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-xs">
            <i class="fa-solid fa-sitemap"></i> সাইটম্যাপ
        </a>
    </div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('modules/seo/css/seo-modern.css') }}">
@endpush

@section('content')
<div class="seo-admin-wrapper pb-4">

    <!-- Top Hero Banner -->
    <div class="card border-0 seo-header-banner p-4 p-md-5 mb-4 shadow-sm">
        <div class="row align-items-center position-relative z-1">
            <div class="col-lg-8">
                <div class="d-inline-flex align-items-center gap-2 bg-white bg-opacity-10 border border-white border-opacity-25 rounded-pill px-3 py-1 text-warning small fw-bold mb-3 backdrop-blur">
                    <i class="fa-solid fa-bolt-lightning"></i> অটোমেটেড সার্চ অপ্টিমাইজেশন ও মেটা স্ক্যানার
                </div>
                <h3 class="fw-bold mb-2 text-white" style="font-family: 'Noto Serif Bengali', serif;">
                    প্রতিটি পোস্ট, বই ও পেজের রিয়েল-টাইম গুগল এসইও হেলথ কন্ট্রোল
                </h3>
                <p class="text-white-50 mb-0 fs-6" style="line-height: 1.7;">
                    গুগল সার্চ কনসোল ও সোশ্যাল মিডিয়ায় আপনার বই এবং কনটেন্টকে সবার আগে ও আকর্ষণীয়ভাবে তুলে ধরার আধুনিক টুলস।
                </p>
            </div>
            <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                <div class="d-inline-block bg-white bg-opacity-10 p-3 rounded-4 border border-white border-opacity-20 text-center">
                    <div class="fs-6 text-white-50 mb-1">সামগ্রিক এসইও স্কোর</div>
                    <div class="display-6 fw-bold text-white mb-0">{{ $avgScore }}<span class="fs-4 text-warning">/১০০</span></div>
                </div>
            </div>
        </div>
    </div>

    <!-- 4 High Level Metric Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card border-0 rounded-4 bg-white p-3.5 shadow-xs border h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold">মোট ইনডেক্সড আইটেম</span>
                    <span class="badge bg-primary bg-opacity-10 text-primary rounded-circle p-2">
                        <i class="fa-solid fa-layer-group"></i>
                    </span>
                </div>
                <div class="fs-4 fw-bold text-dark">{{ number_format($totalItems) }}</div>
                <div class="small text-muted mt-1"><i class="fa-solid fa-check text-success me-1"></i>মেটা ট্যাগ যুক্ত</div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="card border-0 rounded-4 bg-white p-3.5 shadow-xs border h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold">উচ্চমানের এসইও (৮০+)</span>
                    <span class="badge bg-success bg-opacity-10 text-success rounded-circle p-2">
                        <i class="fa-solid fa-circle-check"></i>
                    </span>
                </div>
                <div class="fs-4 fw-bold text-success">{{ number_format($highScoreCount) }}</div>
                <div class="small text-muted mt-1">গুগল অপ্টিমাইজড</div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="card border-0 rounded-4 bg-white p-3.5 shadow-xs border h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold">উন্নতি প্রয়োজন (&lt;৬০)</span>
                    <span class="badge bg-warning bg-opacity-10 text-warning rounded-circle p-2">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </span>
                </div>
                <div class="fs-4 fw-bold text-warning">{{ number_format($needsWorkCount) }}</div>
                <div class="small text-muted mt-1">ট্যাগ রিভিশন দরকার</div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="card border-0 rounded-4 bg-white p-3.5 shadow-xs border h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold">বিবরণ অনুপস্থিত</span>
                    <span class="badge bg-danger bg-opacity-10 text-danger rounded-circle p-2">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </span>
                </div>
                <div class="fs-4 fw-bold text-danger">{{ number_format($missingDescCount) }}</div>
                <div class="small text-muted mt-1">মেটা ডেসক্রিপশন খালি</div>
            </div>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="card border-0 rounded-4 bg-white p-3 mb-4 shadow-xs border">
        <form action="{{ route('admin.seo.index') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-light border-0 ps-3 text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control bg-light border-0" placeholder="টাইটেল, ফোকাস কিওয়ার্ড বা ইউআরএল সার্চ করুন...">
                </div>
            </div>
            <div class="col-md-3">
                <select name="filter_type" class="form-select bg-light border-0" onchange="this.form.submit()">
                    <option value="">সকল ক্যাটাগরি ও মডেল</option>
                    <option value="Book" {{ request('filter_type') === 'Book' ? 'selected' : '' }}>বইসমূহ (Books)</option>
                    <option value="BlogPost" {{ request('filter_type') === 'BlogPost' ? 'selected' : '' }}>আইডিয়াপত্র ও ব্লগ (Blog Posts)</option>
                    <option value="Author" {{ request('filter_type') === 'Author' ? 'selected' : '' }}>লেখক পরিচিতি (Authors)</option>
                    <option value="Publisher" {{ request('filter_type') === 'Publisher' ? 'selected' : '' }}>প্রকাশকবৃন্দ (Publishers)</option>
                    <option value="Ebook" {{ request('filter_type') === 'Ebook' ? 'selected' : '' }}>ই-বুক (Ebooks)</option>
                    <option value="Webzine" {{ request('filter_type') === 'Webzine' ? 'selected' : '' }}>ওয়েবজিন (Webzines)</option>
                    <option value="pages" {{ request('filter_type') === 'pages' ? 'selected' : '' }}>কাস্টম ও স্ট্যাটিক পেজ</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="score_level" class="form-select bg-light border-0" onchange="this.form.submit()">
                    <option value="">সকল স্কোর</option>
                    <option value="good" {{ request('score_level') === 'good' ? 'selected' : '' }}>উচ্চমানের (৮০+)</option>
                    <option value="fair" {{ request('score_level') === 'fair' ? 'selected' : '' }}>মাঝারি (৫০-৭৯)</option>
                    <option value="poor" {{ request('score_level') === 'poor' ? 'selected' : '' }}>দুর্বল (&lt;৫০)</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary rounded-3 w-100 fw-bold">ফিল্টার</button>
                @if(request()->hasAny(['search', 'filter_type', 'score_level']))
                    <a href="{{ route('admin.seo.index') }}" class="btn btn-outline-secondary rounded-3" title="রিসেট"><i class="fa-solid fa-rotate-left"></i></a>
                @endif
            </div>
        </form>
    </div>

    <!-- Items Table -->
    <div class="card border-0 rounded-4 bg-white shadow-xs border overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr class="text-uppercase text-muted small" style="font-size: 11px; letter-spacing: 0.5px;">
                        <th class="ps-4" style="width: 80px;">স্কোর</th>
                        <th style="min-width: 260px;">টাইটেল ও লিংক (Page Title & URL)</th>
                        <th style="min-width: 140px;">মডেল / প্রকার</th>
                        <th style="min-width: 160px;">ফোকাস কিওয়ার্ড</th>
                        <th style="min-width: 200px;">মেটা বিবরণ সারসংক্ষেপ</th>
                        <th style="width: 130px;">সর্বশেষ স্ক্যান</th>
                        <th class="text-end pe-4" style="width: 120px;">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        @php
                            $badge = $item->score_badge;
                            $modelType = $item->seoable_type ? class_basename($item->seoable_type) : 'Static Page';
                        @endphp
                        <tr>
                            <td class="ps-4">
                                <span class="badge {{ $badge['class'] }} rounded-pill px-2.5 py-1.5 fw-bold font-monospace">
                                    {{ $item->seo_score }}%
                                </span>
                            </td>
                            <td>
                                <div class="fw-bold text-dark text-truncate" style="max-width: 320px;" title="{{ $item->meta_title }}">
                                    {{ $item->meta_title ?: 'টাইটেল অনুপস্থিত' }}
                                </div>
                                <div class="small text-muted font-monospace text-truncate" style="max-width: 320px;">
                                    <a href="{{ $item->canonical_url ?: url($item->url_path ?: '/') }}" target="_blank" class="text-muted text-decoration-none">
                                        <i class="fa-solid fa-link text-primary me-1"></i>{{ $item->canonical_url ?: $item->url_path }}
                                    </a>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border px-2.5 py-1 rounded-pill small">
                                    {{ $modelType }}
                                </span>
                            </td>
                            <td>
                                @if($item->focus_keyword)
                                    <span class="badge bg-info bg-opacity-10 text-dark border border-info border-opacity-25 rounded-pill px-2.5 py-1 small">
                                        <i class="fa-solid fa-tag text-info me-1"></i>{{ $item->focus_keyword }}
                                    </span>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td>
                                <div class="small text-muted text-truncate" style="max-width: 280px;" title="{{ $item->meta_description }}">
                                    {{ $item->meta_description ?: 'কোনো মেটা বিবরণ দেওয়া হয়নি' }}
                                </div>
                            </td>
                            <td class="small text-muted">
                                {{ $item->last_scanned_at ? $item->last_scanned_at->diffForHumans() : '—' }}
                            </td>
                            <td class="text-end pe-4">
                                <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 py-1 fw-semibold" 
                                        onclick="populateSeoModal(@js($item))">
                                    <i class="fa-solid fa-pen-to-square me-1"></i> অডিট ও এডিট
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-chart-line fs-1 text-muted mb-2 d-block opacity-25"></i>
                                কোনো এসইও রেকর্ড পাওয়া যায়নি। উপরের <strong>"১-ক্লিকে অল ডাটা অটো-স্ক্যান"</strong> বাটনে ক্লিক করে স্বয়ংক্রিয় মেটা ট্যাগ তৈরি করুন।
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($items->hasPages())
            <div class="card-footer bg-white border-top p-3 d-flex justify-content-center">
                {{ $items->links() }}
            </div>
        @endif
    </div>

</div>

<!-- Interactive Quick Audit & Edit Modal -->
<div class="modal fade" id="seoEditModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
            <div class="modal-header bg-light border-bottom p-3.5">
                <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                    <i class="fa-solid fa-magnifying-glass-chart text-primary"></i>
                    <span>লাইভ গুগল এসইও অডিট ও মেটা এডিটর</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body p-4">
                <form id="editSeoForm" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row g-4">
                        
                        <!-- Left Column: Inputs -->
                        <div class="col-lg-7">
                            <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-sliders text-primary me-1.5"></i>মেটা ট্যাগ কনফিগারেশন</h6>
                            
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label small fw-bold text-dark mb-0">মেটা টাইটেল (Page Meta Title)</label>
                                    <span id="modalTitleCount" class="char-counter-pill char-good">০ অক্ষর</span>
                                </div>
                                <input type="text" name="meta_title" id="modalMetaTitle" class="form-control rounded-3" required>
                                <div class="form-text small text-muted">গুগলে আদর্শ দৈর্ঘ্য: ৪০-৬৫ অক্ষর। ব্র্যান্ড নাম সহ স্বয়ংক্রিয়ভাবে সংযুক্ত হবে।</div>
                            </div>

                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label small fw-bold text-dark mb-0">মেটা বিবরণ (Meta Description)</label>
                                    <span id="modalDescCount" class="char-counter-pill char-good">০ অক্ষর</span>
                                </div>
                                <textarea name="meta_description" id="modalMetaDesc" class="form-control rounded-3" rows="3"></textarea>
                                <div class="form-text small text-muted">গুগল সার্চ ফলাফলের নিচে প্রদর্শিত সারসংক্ষেপ (আদর্শ: ১২০-১৬০ অক্ষর)।</div>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-dark mb-1">ফোকাস কিওয়ার্ড (Focus Keyword)</label>
                                    <input type="text" name="focus_keyword" id="modalFocusKeyword" class="form-control rounded-3" placeholder="যেমন: বইমেলা ২০২৬">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-dark mb-1">রোবটস ইনডেক্সিং (Robots)</label>
                                    <select name="robots" id="modalRobots" class="form-select rounded-3">
                                        <option value="index, follow">index, follow (স্ট্যান্ডার্ড)</option>
                                        <option value="noindex, follow">noindex, follow</option>
                                        <option value="index, nofollow">index, nofollow</option>
                                        <option value="noindex, nofollow">noindex, nofollow</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-dark mb-1">মেটা কিওয়ার্ডস (কমা দিয়ে আলাদা করুন)</label>
                                <input type="text" name="meta_keywords" id="modalKeywords" class="form-control rounded-3">
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-dark mb-1">ক্যানোনিকাল ইউআরএল (Canonical URL)</label>
                                <input type="url" name="canonical_url" id="modalCanonical" class="form-control rounded-3 font-monospace small">
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-dark mb-1">সোশ্যাল শেয়ার ইমেজ (OpenGraph Image URL)</label>
                                <input type="text" name="og_image" id="modalOgImage" class="form-control rounded-3 font-monospace small">
                            </div>
                        </div>

                        <!-- Right Column: Live SERP Simulator & Score Breakdown -->
                        <div class="col-lg-5">
                            <h6 class="fw-bold text-dark mb-3"><i class="fa-brands fa-google text-danger me-1.5"></i>গুগল সার্চ প্রিভিউ (Live SERP Preview)</h6>
                            
                            <!-- SERP Card -->
                            <div class="serp-card mb-4">
                                <div class="serp-url-box">
                                    <div class="serp-favicon d-flex align-items-center justify-content-center small text-primary"><i class="fa-solid fa-globe"></i></div>
                                    <div>
                                        <div class="serp-site-name">আইডিয়া প্রকাশন — ideaabd.com</div>
                                        <div class="serp-breadcrumbs" id="serpPreviewUrl">https://www.ideaabd.com/books/...</div>
                                    </div>
                                </div>
                                <div class="serp-title" id="serpPreviewTitle">আইডিয়া প্রকাশন — বই ও মুক্তচিন্তা</div>
                                <div class="serp-snippet" id="serpPreviewDesc">গুগল সার্চে প্রদর্শিত আকর্ষণীয় মেটা বিবরণ...</div>
                            </div>

                            <!-- Score Card & Checklist -->
                            <div class="card border-0 bg-light rounded-4 p-3.5 border">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <h6 class="fw-bold text-dark mb-0">এসইও কোয়ালিটি স্কোর</h6>
                                    <div id="modalScoreCircle" class="seo-score-circle seo-score-good">
                                        <span id="modalScoreValue">৮৫</span>
                                    </div>
                                </div>
                                <hr class="my-2 opacity-10">
                                <h6 class="small fw-bold text-dark mb-2">অডিট ও সুপারিশসমূহ:</h6>
                                <ul id="modalAuditList" class="list-unstyled mb-0">
                                    <!-- Dynamic audit checklist injected via JS -->
                                </ul>
                            </div>
                        </div>

                    </div>
                </form>
            </div>

            <div class="modal-footer bg-light border-top p-3 d-flex justify-content-between align-items-center">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4 fw-semibold" data-bs-dismiss="modal">বন্ধ করুন</button>
                <button type="submit" form="editSeoForm" class="btn btn-primary rounded-pill px-4 fw-bold shadow-xs">
                    <i class="fa-solid fa-floppy-disk me-1"></i> এসইও ট্যাগ সেভ করুন
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('modules/seo/js/seo-scanner.js') }}"></script>
@endpush
