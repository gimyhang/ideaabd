@extends('layouts.admin')

@section('title', 'SEO')
@section('heading', 'SEO')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">SEO</li>
@endsection

@section('actions')
    <div class="d-flex flex-wrap align-items-center gap-2">
        <button type="button" class="btn btn-warning btn-sm rounded-pill px-3 py-1.5 fw-bold text-dark" data-seo-scan>
            <i class="fa-solid fa-wand-magic-sparkles me-1"></i> Scan
        </button>
        <form action="{{ route('admin.seo.ping') }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-outline-info btn-sm rounded-pill px-3 py-1.5 fw-semibold"><i class="fa-solid fa-bolt me-1"></i> Ping</button>
        </form>
        <a href="{{ route('admin.seo.pages') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 py-1.5 fw-semibold"><i class="fa-solid fa-file-lines me-1"></i> Pages</a>
        <a href="{{ route('admin.seo.redirects') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 py-1.5 fw-semibold"><i class="fa-solid fa-arrow-right-arrow-left me-1"></i> Redirects</a>
        <a href="{{ route('admin.seo.broken-links') }}" class="btn btn-outline-danger btn-sm rounded-pill px-3 py-1.5 fw-semibold"><i class="fa-solid fa-link-slash me-1"></i> Monitor</a>
        <a href="{{ route('admin.seo.sitemap') }}" class="btn btn-outline-success btn-sm rounded-pill px-3 py-1.5 fw-semibold"><i class="fa-solid fa-sitemap me-1"></i> Sitemap</a>
    </div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('modules/seo/css/seo-modern.css') }}?v={{ @filemtime(public_path('modules/seo/css/seo-modern.css')) }}">
@endpush

@section('content')
@php
    $o = $overview;
    $avg = $o['avg'];
    $tone = $avg >= 100 ? 'perfect' : ($avg >= 80 ? 'good' : ($avg >= 50 ? 'fair' : 'poor'));
    $grade = $o['total'] === 0 ? 'Not scanned' : ($avg >= 100 ? 'Perfect' : ($avg >= 90 ? 'Excellent' : ($avg >= 80 ? 'Good' : ($avg >= 50 ? 'Needs work' : 'Poor'))));
    $below = $o['total'] - $o['perfect'];
    $bucketTone = ['100' => 'perfect', '80–99' => 'good', '50–79' => 'fair', '0–49' => 'poor'];
    $bucketFilter = ['100' => 'perfect', '80–99' => 'good', '50–79' => 'fair', '0–49' => 'poor'];
    $issueIcons = ['title' => 'fa-heading', 'description' => 'fa-align-left', 'keyword_title' => 'fa-key', 'keyword_desc' => 'fa-quote-left', 'image' => 'fa-image', 'keywords' => 'fa-tags'];
    $ring = fn (int $s, int $r) => round(2 * M_PI * $r * (1 - max(0, min(100, $s)) / 100), 2);
    $filtered = request()->hasAny(['search', 'filter_type', 'score_level', 'issue']);
@endphp

<div class="seo-page pb-4"
     data-plan-url="{{ route('admin.seo.scan-plan') }}"
     data-step-url="{{ route('admin.seo.scan-step') }}"
     data-analyze-url="{{ route('admin.seo.analyze') }}">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 border-0" role="alert"><i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0" role="alert"><i class="fa-solid fa-triangle-exclamation me-2"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    {{-- ── Hero: score gauge + scan ─────────────────────────── --}}
    <section class="seo-hero">
        <div class="seo-hero-text">
            <div class="seo-eyebrow"><i class="fa-solid fa-bolt"></i> Search health</div>
            <h2>Average SEO score</h2>
            <p>
                @if($o['total'] > 0)
                    {{ number_format($o['perfect']) }} of {{ number_format($o['total']) }} pages pass all six checks.
                    @if($o['last_scan']) Last scan {{ $o['last_scan']->locale('en')->diffForHumans() }}. @endif
                @else
                    Nothing scanned yet — press Scan to write tags for every book, post, author and page.
                @endif
            </p>

            <div class="seo-scan" data-scan-box hidden>
                <div class="seo-scan-top"><span data-scan-label>Preparing…</span><b data-scan-pct>0%</b></div>
                <div class="seo-scan-bar"><span data-scan-fill style="width:0%"></span></div>
            </div>
            <div class="seo-hero-actions" data-scan-actions>
                <button type="button" class="seo-btn-light" data-seo-scan><i class="fa-solid fa-wand-magic-sparkles"></i> Scan &amp; fix all</button>
                @if($below > 0)
                    <a href="{{ route('admin.seo.index', ['score_level' => 'below']) }}" class="seo-btn-ghost"><i class="fa-solid fa-filter"></i> {{ number_format($below) }} below 100</a>
                @endif
            </div>
        </div>

        <div class="seo-gauge seo-tone-{{ $tone }}" data-gauge data-score="{{ $avg }}">
            <svg viewBox="0 0 120 120" aria-hidden="true">
                <circle cx="60" cy="60" r="50" class="seo-gauge-track"/>
                <circle cx="60" cy="60" r="50" class="seo-gauge-value"
                        stroke-dasharray="{{ round(2 * M_PI * 50, 2) }}" stroke-dashoffset="{{ round(2 * M_PI * 50, 2) }}"
                        data-target-offset="{{ $ring($avg, 50) }}"/>
            </svg>
            <div class="seo-gauge-label">
                <b data-gauge-num>{{ $avg }}</b><small>/100</small>
                <span>{{ $grade }}</span>
            </div>
        </div>
    </section>

    {{-- ── Distribution ─────────────────────────────────────── --}}
    @if($o['total'] > 0)
        <section class="seo-card seo-dist">
            <div class="seo-card-head"><h3>Score spread</h3><small>{{ number_format($o['total']) }} pages</small></div>
            <div class="seo-dist-bar" role="img" aria-label="Score spread">
                @foreach($o['buckets'] as $label => $n)
                    @if($n > 0)
                        <a href="{{ route('admin.seo.index', ['score_level' => $bucketFilter[$label]]) }}" class="seo-dist-seg seo-bg-{{ $bucketTone[$label] }}" style="flex-grow: {{ $n }}" title="{{ $label }}: {{ $n }}"></a>
                    @endif
                @endforeach
            </div>
            <div class="seo-dist-legend">
                @foreach($o['buckets'] as $label => $n)
                    <a href="{{ route('admin.seo.index', ['score_level' => $bucketFilter[$label]]) }}" class="{{ request('score_level') === $bucketFilter[$label] ? 'active' : '' }}">
                        <i class="seo-dot seo-bg-{{ $bucketTone[$label] }}"></i> {{ $label }} <b>{{ number_format($n) }}</b>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- ── KPIs ─────────────────────────────────────────────── --}}
    <div class="seo-kpis">
        @foreach([
            ['Indexed', $o['total'], 'fa-layer-group', 'blue', 'Pages with tags', null],
            ['Perfect', $o['perfect'], 'fa-circle-check', 'green', 'Score 100', ['score_level' => 'perfect']],
            ['Below 100', $below, 'fa-triangle-exclamation', $below ? 'amber' : 'green', $below ? 'Fix to reach 100' : 'Nothing to fix', ['score_level' => 'below']],
            ['Edited', $o['manual'], 'fa-pen-nib', 'violet', 'Written by hand', null],
        ] as [$label, $value, $icon, $color, $hint, $q])
            @php $tag = $q ? 'a' : 'div'; @endphp
            <{{ $tag }} @if($q) href="{{ route('admin.seo.index', $q) }}" @endif class="seo-kpi seo-kpi-{{ $color }}">
                <span class="seo-kpi-icon"><i class="fa-solid {{ $icon }}"></i></span>
                <div><small>{{ $label }}</small><b>{{ number_format($value) }}</b><span>{{ $hint }}</span></div>
            </{{ $tag }}>
        @endforeach
    </div>

    {{-- ── Issues + types ───────────────────────────────────── --}}
    @if($o['total'] > 0)
        <div class="seo-split">
            <section class="seo-card">
                <div class="seo-card-head"><h3>Issues</h3><small>six checks · 100 points</small></div>
                @php $anyIssue = array_sum($o['issues']) > 0; @endphp
                @if($anyIssue)
                    <ul class="seo-issues">
                        @foreach($o['issues'] as $key => $n)
                            @continue($n === 0)
                            <li>
                                <a href="{{ route('admin.seo.index', ['issue' => $key]) }}" class="{{ request('issue') === $key ? 'active' : '' }}">
                                    <span class="seo-issue-icon"><i class="fa-solid {{ $issueIcons[$key] ?? 'fa-circle' }}"></i></span>
                                    <span class="seo-issue-name">{{ $issues[$key] }}</span>
                                    <span class="seo-issue-bar"><span style="width: {{ max(4, round($n * 100 / max(1, $o['total']))) }}%"></span></span>
                                    <b>{{ number_format($n) }}</b>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="seo-allgood"><i class="fa-solid fa-trophy"></i><div><b>No issues</b><span>Every page passes all six checks.</span></div></div>
                @endif
            </section>

            <section class="seo-card">
                <div class="seo-card-head"><h3>By type</h3><small>average score</small></div>
                <ul class="seo-types">
                    @foreach($o['types'] as $t)
                        @php $tt = $t['avg'] >= 100 ? 'perfect' : ($t['avg'] >= 80 ? 'good' : ($t['avg'] >= 50 ? 'fair' : 'poor')); @endphp
                        <li>
                            <a href="{{ route('admin.seo.index', ['filter_type' => $t['key'] === 'Page' ? 'pages' : $t['key']]) }}">
                                <span class="seo-type-name">{{ $t['label'] }} <small>{{ number_format($t['count']) }}</small></span>
                                <span class="seo-type-bar"><span class="seo-bg-{{ $tt }}" style="width: {{ $t['avg'] }}%"></span></span>
                                <b class="seo-fg-{{ $tt }}">{{ $t['avg'] }}</b>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </section>
        </div>
    @endif

    {{-- ── Filters ──────────────────────────────────────────── --}}
    <form action="{{ route('admin.seo.index') }}" method="GET" id="seoFilters" class="seo-filters">
        <div class="seo-search">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Title, keyword or URL" autocomplete="off" aria-label="Search">
        </div>
        <select name="filter_type" class="form-select" aria-label="Type">
            <option value="">All types</option>
            @foreach(['Book' => 'Books', 'BlogPost' => 'Posts', 'Author' => 'Authors', 'Publisher' => 'Publishers', 'Ebook' => 'Ebooks', 'Webzine' => 'Webzines', 'ResearchPaper' => 'Research', 'pages' => 'Pages'] as $v => $l)
                <option value="{{ $v }}" @selected(request('filter_type') === $v)>{{ $l }}</option>
            @endforeach
        </select>
        <select name="score_level" class="form-select" aria-label="Score">
            <option value="">All scores</option>
            <option value="perfect" @selected(request('score_level') === 'perfect')>Perfect (100)</option>
            <option value="below" @selected(request('score_level') === 'below')>Below 100</option>
            <option value="good" @selected(request('score_level') === 'good')>Good (80–99)</option>
            <option value="fair" @selected(request('score_level') === 'fair')>Fair (50–79)</option>
            <option value="poor" @selected(request('score_level') === 'poor')>Weak (&lt;50)</option>
        </select>
        <select name="issue" class="form-select" aria-label="Issue">
            <option value="">All checks</option>
            @foreach($issues as $k => $l)
                <option value="{{ $k }}" @selected(request('issue') === $k)>{{ $l }}</option>
            @endforeach
        </select>
        @if($filtered)
            <a href="{{ route('admin.seo.index') }}" class="btn btn-outline-secondary" title="Reset"><i class="fa-solid fa-rotate-left"></i></a>
        @endif
    </form>

    {{-- ── Records ──────────────────────────────────────────── --}}
    <section class="seo-card seo-table-card">
        <div class="table-responsive">
            <table class="table align-middle mb-0 seo-table">
                <thead>
                    <tr>
                        <th style="width: 72px;">Score</th>
                        <th style="min-width: 280px;">Page</th>
                        <th>Type</th>
                        <th>Keyword</th>
                        <th style="min-width: 240px;">Description</th>
                        <th>Scanned</th>
                        <th class="text-end"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        @php
                            $s = (int) $item->seo_score;
                            $st = $s >= 100 ? 'perfect' : ($s >= 80 ? 'good' : ($s >= 50 ? 'fair' : 'poor'));
                            $type = $item->seoable_type ? class_basename($item->seoable_type) : 'Page';
                            $dl = mb_strlen((string) $item->meta_description);
                            $link = $item->canonical_url ?: url($item->url_path ?: '/');
                        @endphp
                        <tr data-row="{{ $item->id }}">
                            <td data-label="Score">
                                <span class="seo-mini seo-tone-{{ $st }}" data-cell="score" title="{{ $s }}/100">
                                    <svg viewBox="0 0 36 36" aria-hidden="true"><circle cx="18" cy="18" r="15" class="t"/><circle cx="18" cy="18" r="15" class="v" stroke-dasharray="{{ round(2 * M_PI * 15, 2) }}" stroke-dashoffset="{{ $ring($s, 15) }}"/></svg>
                                    <b>{{ $s }}</b>
                                </span>
                            </td>
                            <td data-label="Page">
                                <div class="seo-title" data-cell="title" title="{{ $item->meta_title }}">{{ $item->meta_title ?: 'No title' }}</div>
                                <a href="{{ $link }}" target="_blank" rel="noopener" class="seo-url"><i class="fa-solid fa-arrow-up-right-from-square"></i>{{ $item->url_path ?: $link }}</a>
                            </td>
                            <td data-label="Type"><span class="seo-chip">{{ $type }}</span>@if(!$item->is_auto_generated)<span class="seo-chip seo-chip-violet" title="Written by hand">Edited</span>@endif</td>
                            <td data-label="Keyword">
                                @if($item->focus_keyword)
                                    <span class="seo-kw" data-cell="keyword"><i class="fa-solid fa-key"></i>{{ \Illuminate\Support\Str::limit($item->focus_keyword, 34) }}</span>
                                @else
                                    <span class="text-muted small" data-cell="keyword">—</span>
                                @endif
                            </td>
                            <td data-label="Description">
                                <div class="seo-desc" data-cell="desc" title="{{ $item->meta_description }}">{{ $item->meta_description ?: 'No description' }}</div>
                                <span class="seo-len {{ $dl >= 120 && $dl <= 160 ? 'ok' : 'warn' }}" data-cell="desclen">{{ $dl }} chars</span>
                            </td>
                            <td data-label="Scanned" class="small text-muted">{{ $item->last_scanned_at ? $item->last_scanned_at->locale('en')->diffForHumans() : '—' }}</td>
                            <td class="text-end seo-actions">
                                <button type="button" class="btn btn-sm seo-btn-fix" data-fix="{{ $item->id }}" @if($s >= 100) hidden @endif title="Rewrite the tags from the page's content"><i class="fa-solid fa-wand-magic-sparkles"></i> Fix</button>
                                <button type="button" class="btn btn-sm seo-btn-edit" data-edit="{{ $item->id }}"><i class="fa-solid fa-pen-to-square"></i> Edit</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="seo-empty">
                                <i class="fa-solid fa-magnifying-glass-chart"></i>
                                @if($filtered)
                                    Nothing matches these filters. <a href="{{ route('admin.seo.index') }}">Show all</a>
                                @else
                                    No SEO records yet. Press <b>Scan</b> to create them.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($items->hasPages())
            <div class="seo-pager">{{ $items->links() }}</div>
        @endif
    </section>
</div>

{{-- ── Editor ───────────────────────────────────────────────── --}}
<div class="modal fade" id="seoEditModal" tabindex="-1" aria-hidden="true" aria-labelledby="seoEditTitle">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable modal-fullscreen-md-down">
        <div class="modal-content seo-modal">
            <div class="modal-header">
                <div class="d-flex align-items-center gap-3 min-w-0">
                    <span class="seo-mini seo-mini-lg" data-m="ring">
                        <svg viewBox="0 0 36 36" aria-hidden="true"><circle cx="18" cy="18" r="15" class="t"/><circle cx="18" cy="18" r="15" class="v" stroke-dasharray="{{ round(2 * M_PI * 15, 2) }}" stroke-dashoffset="{{ round(2 * M_PI * 15, 2) }}"/></svg>
                        <b data-m="score">0</b>
                    </span>
                    <div class="min-w-0">
                        <h5 class="modal-title" id="seoEditTitle">Editor <span class="seo-chip" data-m="type"></span></h5>
                        <small class="text-muted text-truncate d-block" data-m="path"></small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="editSeoForm" method="POST" novalidate>
                    @csrf
                    @method('PUT')
                    <div class="row g-4">
                        <div class="col-lg-7">
                            <div class="seo-field">
                                <div class="seo-field-top"><label for="mTitle">Title</label><span class="seo-count" data-count="title">0 / 65</span></div>
                                <input type="text" name="meta_title" id="mTitle" class="form-control" maxlength="255" required>
                                <div class="seo-meter"><span data-meter="title"></span></div>
                            </div>
                            <div class="seo-field">
                                <div class="seo-field-top"><label for="mDesc">Description</label><span class="seo-count" data-count="desc">0 / 160</span></div>
                                <textarea name="meta_description" id="mDesc" class="form-control" rows="3" maxlength="1500"></textarea>
                                <div class="seo-meter"><span data-meter="desc"></span></div>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6 seo-field">
                                    <label for="mFocus">Keyword</label>
                                    <input type="text" name="focus_keyword" id="mFocus" class="form-control" maxlength="150">
                                </div>
                                <div class="col-md-6 seo-field">
                                    <label for="mRobots">Robots</label>
                                    <select name="robots" id="mRobots" class="form-select">
                                        <option value="index, follow">index, follow</option>
                                        <option value="noindex, follow">noindex, follow</option>
                                        <option value="index, nofollow">index, nofollow</option>
                                        <option value="noindex, nofollow">noindex, nofollow</option>
                                    </select>
                                </div>
                            </div>
                            <div class="seo-field">
                                <label for="mKeywords">Keywords <small class="text-muted">(comma separated)</small></label>
                                <input type="text" name="meta_keywords" id="mKeywords" class="form-control" maxlength="1000">
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6 seo-field">
                                    <label for="mCanonical">Canonical</label>
                                    <input type="text" name="canonical_url" id="mCanonical" class="form-control font-monospace small" maxlength="500">
                                </div>
                                <div class="col-md-6 seo-field">
                                    <label for="mImage">Image</label>
                                    <input type="text" name="og_image" id="mImage" class="form-control font-monospace small" maxlength="500" placeholder="https://… or /path">
                                </div>
                            </div>
                            <div class="seo-form-error" data-m="error" hidden></div>
                        </div>

                        <div class="col-lg-5">
                            <div class="seo-preview-label"><i class="fa-brands fa-google"></i> Google</div>
                            <div class="serp-card mb-3">
                                <div class="serp-url-box">
                                    <div class="serp-favicon"><i class="fa-solid fa-globe"></i></div>
                                    <div class="min-w-0">
                                        <div class="serp-site-name">{{ \App\Support\SiteSetting::name() }}</div>
                                        <div class="serp-breadcrumbs text-truncate" data-p="url">https://www.ideaabd.com</div>
                                    </div>
                                </div>
                                <div class="serp-title" data-p="title">Page title</div>
                                <div class="serp-snippet" data-p="desc">Description</div>
                            </div>

                            <div class="seo-preview-label"><i class="fa-brands fa-facebook"></i> Share</div>
                            <div class="seo-social mb-3">
                                <div class="seo-social-img" data-p="img"><i class="fa-regular fa-image"></i></div>
                                <div class="seo-social-body">
                                    <small data-p="host">ideaabd.com</small>
                                    <b data-p="stitle">Page title</b>
                                    <span data-p="sdesc">Description</span>
                                </div>
                            </div>

                            <div class="seo-checks-head"><b>Checks</b><small data-m="live">live</small></div>
                            <ul class="seo-checks" data-m="checks"></ul>
                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-outline-warning rounded-pill px-3 me-auto" data-m="regen" title="Rewrite title, description and keywords from the page's content"><i class="fa-solid fa-wand-magic-sparkles me-1"></i> Auto</button>
                <button type="submit" form="editSeoForm" class="btn btn-primary rounded-pill px-4 fw-bold" data-m="save"><i class="fa-solid fa-floppy-disk me-1"></i> Save</button>
            </div>
        </div>
    </div>
</div>

<div class="seo-toast" data-toast role="status" aria-live="polite" hidden></div>
@endsection

@push('scripts')
<script>
    window.seoItems = @json($items->getCollection()->mapWithKeys(fn ($m) => [$m->id => \Modules\SEO\Http\Controllers\Admin\AdminSeoController::modalData($m)]));
</script>
<script src="{{ asset('modules/seo/js/seo-dashboard.js') }}?v={{ @filemtime(public_path('modules/seo/js/seo-dashboard.js')) }}" defer></script>
@endpush
