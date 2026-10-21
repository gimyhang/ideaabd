@extends('layouts.admin')

@section('title', 'SEO')
@section('heading', 'SEO')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">SEO</li>
@endsection

@section('actions')
    <div class="d-flex flex-wrap align-items-center gap-2">
        <form action="{{ route('admin.seo.batch-scan') }}" method="POST" onsubmit="return confirm('Scan every post, book, author and page and regenerate their SEO tags?')">
            @csrf
            <button type="submit" class="btn btn-warning btn-sm rounded-pill px-3 py-1.5 fw-bold text-dark"><i class="fa-solid fa-wand-magic-sparkles me-1"></i> Scan</button>
        </form>
        <form action="{{ route('admin.seo.ping') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-outline-info btn-sm rounded-pill px-3 py-1.5 fw-semibold"><i class="fa-solid fa-bolt me-1"></i> Ping</button>
        </form>
        <a href="{{ route('admin.seo.redirects') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 py-1.5 fw-semibold"><i class="fa-solid fa-arrow-right-arrow-left me-1"></i> Redirects</a>
        <a href="{{ route('admin.seo.broken-links') }}" class="btn btn-outline-danger btn-sm rounded-pill px-3 py-1.5 fw-semibold"><i class="fa-solid fa-link-slash me-1"></i> Monitor</a>
        <a href="{{ route('admin.seo.pages') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 py-1.5 fw-semibold"><i class="fa-solid fa-file-lines me-1"></i> Pages</a>
        <a href="{{ route('admin.seo.sitemap') }}" class="btn btn-outline-success btn-sm rounded-pill px-3 py-1.5 fw-semibold"><i class="fa-solid fa-sitemap me-1"></i> Sitemap</a>
    </div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('modules/seo/css/seo-modern.css') }}">
@endpush

@section('content')
@php
    $scoreTone = $avgScore >= 80 ? 'success' : ($avgScore >= 50 ? 'warning' : 'danger');
    $cards = [
        ['Indexed', $totalItems, 'fa-layer-group', 'primary', 'Pages with tags'],
        ['Strong', $highScoreCount, 'fa-circle-check', 'success', 'Score 80 or more'],
        ['Weak', $needsWorkCount, 'fa-triangle-exclamation', 'warning', 'Score under 60'],
        ['Missing', $missingDescCount, 'fa-circle-xmark', 'danger', 'No description'],
    ];
@endphp
<div class="seo-admin-wrapper pb-4">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 border-0" role="alert"><i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0" role="alert"><i class="fa-solid fa-triangle-exclamation me-2"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    {{-- Score banner --}}
    <div class="card border-0 seo-header-banner p-4 mb-4 shadow-sm">
        <div class="row align-items-center position-relative z-1 g-3">
            <div class="col-md-8">
                <div class="text-warning small fw-bold mb-2"><i class="fa-solid fa-bolt-lightning me-1"></i> Search health</div>
                <h3 class="fw-bold mb-1 text-white">Average SEO score</h3>
                <p class="text-white-50 mb-0">
                    {{ $totalItems > 0
                        ? 'Based on ' . number_format($totalItems) . ' scanned item' . ($totalItems === 1 ? '' : 's') . '. Run a scan to cover every book, post and page.'
                        : 'Nothing scanned yet. Run a scan to generate tags and scores.' }}
                </p>
            </div>
            <div class="col-md-4 text-md-end">
                <div class="d-inline-block bg-white bg-opacity-10 px-4 py-3 rounded-4 border border-white border-opacity-25 text-center">
                    <div class="display-5 fw-bold text-white mb-0">{{ $avgScore }}<span class="fs-5 text-warning">/100</span></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Metrics --}}
    <div class="row g-3 mb-4">
        @foreach($cards as [$label, $value, $icon, $tone, $hint])
            <div class="col-6 col-lg-3">
                <div class="card border-0 rounded-4 bg-white p-3 shadow-xs border h-100">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-semibold text-uppercase" style="font-size: 11px; letter-spacing: .4px;">{{ $label }}</span>
                        <span class="badge bg-{{ $tone }} bg-opacity-10 text-{{ $tone }} rounded-circle p-2"><i class="fa-solid {{ $icon }}"></i></span>
                    </div>
                    <div class="fs-3 fw-bold text-{{ $tone === 'primary' ? 'dark' : $tone }}">{{ number_format($value) }}</div>
                    <div class="small text-muted mt-1">{{ $hint }}</div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Filters --}}
    <div class="card border-0 rounded-4 bg-white p-3 mb-4 shadow-xs border">
        <form action="{{ route('admin.seo.index') }}" method="GET" id="seoFilters" class="row g-2 align-items-center">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-light border-0 ps-3 text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="search" name="search" value="{{ request('search') }}" class="form-control bg-light border-0" placeholder="Title, keyword or URL" autocomplete="off">
                </div>
            </div>
            <div class="col-md-3">
                <select name="filter_type" class="form-select bg-light border-0">
                    <option value="">All types</option>
                    @foreach(['Book' => 'Books', 'BlogPost' => 'Posts', 'Author' => 'Authors', 'Publisher' => 'Publishers', 'Ebook' => 'Ebooks', 'Webzine' => 'Webzines', 'pages' => 'Pages'] as $v => $l)
                        <option value="{{ $v }}" @selected(request('filter_type') === $v)>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="score_level" class="form-select bg-light border-0">
                    <option value="">All scores</option>
                    <option value="good" @selected(request('score_level') === 'good')>Strong (80+)</option>
                    <option value="fair" @selected(request('score_level') === 'fair')>Fair (50–79)</option>
                    <option value="poor" @selected(request('score_level') === 'poor')>Weak (&lt;50)</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary rounded-3 w-100 fw-bold">Filter</button>
                @if(request()->hasAny(['search', 'filter_type', 'score_level']))
                    <a href="{{ route('admin.seo.index') }}" class="btn btn-outline-secondary rounded-3" title="Reset"><i class="fa-solid fa-rotate-left"></i></a>
                @endif
            </div>
        </form>
    </div>

    {{-- Items --}}
    <div class="card border-0 rounded-4 bg-white shadow-xs border overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr class="text-uppercase text-muted" style="font-size: 11px; letter-spacing: .5px;">
                        <th class="ps-4" style="width: 80px;">Score</th>
                        <th style="min-width: 260px;">Page</th>
                        <th style="min-width: 120px;">Type</th>
                        <th style="min-width: 150px;">Keyword</th>
                        <th style="min-width: 200px;">Description</th>
                        <th style="width: 120px;">Scanned</th>
                        <th class="text-end pe-4" style="width: 100px;"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        @php
                            $badge = $item->score_badge;
                            $modelType = $item->seoable_type ? class_basename($item->seoable_type) : 'Page';
                        @endphp
                        <tr>
                            <td class="ps-4"><span class="badge {{ $badge['class'] }} rounded-pill px-2.5 py-1.5 fw-bold font-monospace">{{ $item->seo_score }}</span></td>
                            <td>
                                <div class="fw-bold text-dark text-truncate" style="max-width: 320px;" title="{{ $item->meta_title }}">{{ $item->meta_title ?: 'No title' }}</div>
                                <div class="small text-muted font-monospace text-truncate" style="max-width: 320px;">
                                    <a href="{{ $item->canonical_url ?: url($item->url_path ?: '/') }}" target="_blank" rel="noopener" class="text-muted text-decoration-none"><i class="fa-solid fa-link text-primary me-1"></i>{{ $item->canonical_url ?: $item->url_path }}</a>
                                </div>
                            </td>
                            <td><span class="badge bg-light text-dark border px-2.5 py-1 rounded-pill small">{{ $modelType }}</span></td>
                            <td>
                                @if($item->focus_keyword)
                                    <span class="badge bg-info bg-opacity-10 text-dark border border-info border-opacity-25 rounded-pill px-2.5 py-1 small"><i class="fa-solid fa-tag text-info me-1"></i>{{ $item->focus_keyword }}</span>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td><div class="small text-muted text-truncate" style="max-width: 280px;" title="{{ $item->meta_description }}">{{ $item->meta_description ?: 'No description' }}</div></td>
                            <td class="small text-muted">{{ $item->last_scanned_at ? $item->last_scanned_at->locale('en')->diffForHumans() : '—' }}</td>
                            <td class="text-end pe-4">
                                <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 py-1 fw-semibold" onclick="populateSeoModal(@js($item))"><i class="fa-solid fa-pen-to-square me-1"></i> Edit</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-chart-line fs-1 mb-2 d-block opacity-25"></i>
                                No SEO records yet. Press <strong>Scan</strong> above to generate them.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($items->hasPages())
            <div class="card-footer bg-white border-top p-3 d-flex justify-content-center">{{ $items->links() }}</div>
        @endif
    </div>
</div>

{{-- Editor --}}
<div class="modal fade" id="seoEditModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
            <div class="modal-header bg-light border-bottom p-3">
                <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2"><i class="fa-solid fa-magnifying-glass-chart text-primary"></i> Editor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                <form id="editSeoForm" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row g-4">
                        <div class="col-lg-7">
                            <h6 class="fw-bold text-dark mb-3">Tags</h6>

                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label small fw-bold text-dark mb-0">Title</label>
                                    <span id="modalTitleCount" class="char-counter-pill char-good">0 chars</span>
                                </div>
                                <input type="text" name="meta_title" id="modalMetaTitle" class="form-control rounded-3" required>
                                <div class="form-text small">Best length: 40–65 characters.</div>
                            </div>

                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label small fw-bold text-dark mb-0">Description</label>
                                    <span id="modalDescCount" class="char-counter-pill char-good">0 chars</span>
                                </div>
                                <textarea name="meta_description" id="modalMetaDesc" class="form-control rounded-3" rows="3"></textarea>
                                <div class="form-text small">Best length: 120–160 characters.</div>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-dark mb-1">Keyword</label>
                                    <input type="text" name="focus_keyword" id="modalFocusKeyword" class="form-control rounded-3">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-dark mb-1">Robots</label>
                                    <select name="robots" id="modalRobots" class="form-select rounded-3">
                                        <option value="index, follow">index, follow</option>
                                        <option value="noindex, follow">noindex, follow</option>
                                        <option value="index, nofollow">index, nofollow</option>
                                        <option value="noindex, nofollow">noindex, nofollow</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-dark mb-1">Keywords <span class="text-muted fw-normal">(comma separated)</span></label>
                                <input type="text" name="meta_keywords" id="modalKeywords" class="form-control rounded-3">
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-dark mb-1">Canonical</label>
                                <input type="url" name="canonical_url" id="modalCanonical" class="form-control rounded-3 font-monospace small">
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-dark mb-1">Image</label>
                                <input type="text" name="og_image" id="modalOgImage" class="form-control rounded-3 font-monospace small" placeholder="Social share image URL">
                            </div>
                        </div>

                        <div class="col-lg-5">
                            <h6 class="fw-bold text-dark mb-3"><i class="fa-brands fa-google text-danger me-1"></i>Preview</h6>

                            <div class="serp-card mb-4">
                                <div class="serp-url-box">
                                    <div class="serp-favicon d-flex align-items-center justify-content-center small text-primary"><i class="fa-solid fa-globe"></i></div>
                                    <div>
                                        <div class="serp-site-name">Idea Prokashon — ideaabd.com</div>
                                        <div class="serp-breadcrumbs" id="serpPreviewUrl">https://www.ideaabd.com</div>
                                    </div>
                                </div>
                                <div class="serp-title" id="serpPreviewTitle">Page title</div>
                                <div class="serp-snippet" id="serpPreviewDesc">Search result description.</div>
                            </div>

                            <div class="card border-0 bg-light rounded-4 p-3 border">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <h6 class="fw-bold text-dark mb-0">Score</h6>
                                    <div id="modalScoreCircle" class="seo-score-circle seo-score-good"><span id="modalScoreValue">0</span></div>
                                </div>
                                <hr class="my-2 opacity-10">
                                <h6 class="small fw-bold text-dark mb-2">Audit</h6>
                                <ul id="modalAuditList" class="list-unstyled mb-0"></ul>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-footer bg-light border-top p-3 d-flex justify-content-between align-items-center">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4 fw-semibold" data-bs-dismiss="modal">Close</button>
                <button type="submit" form="editSeoForm" class="btn btn-primary rounded-pill px-4 fw-bold"><i class="fa-solid fa-floppy-disk me-1"></i> Save</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('modules/seo/js/seo-scanner.js') }}"></script>
<script>
(function () {
    var f = document.getElementById('seoFilters');
    if (!f) return;
    var t;
    f.elements.search.addEventListener('input', function () { clearTimeout(t); t = setTimeout(function () { f.submit(); }, 500); });
    ['filter_type', 'score_level'].forEach(function (n) { f.elements[n].addEventListener('change', function () { f.submit(); }); });
    var s = f.elements.search;
    if (s.value && document.referrer.indexOf('/admin/seo') > -1) { s.focus(); s.setSelectionRange(s.value.length, s.value.length); }
})();
</script>
@endpush
