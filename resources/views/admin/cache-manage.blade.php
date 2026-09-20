@extends('layouts.admin')

@section('title', 'Cache Management')
@section('heading', 'Cache Hub')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Cache</li>
@endsection

@section('actions')
    <div class="d-flex flex-wrap align-items-center gap-2">
        {{-- Auto-refresh Switch --}}
        <div class="form-check form-switch d-inline-flex align-items-center gap-1.5 px-2.5 py-1 bg-white border rounded-pill shadow-xs me-1">
            <input class="form-check-input ms-0 cursor-pointer" type="checkbox" role="switch" id="autoRefreshToggle" onchange="toggleAutoRefresh(this)">
            <label class="form-check-label small fw-semibold text-muted cursor-pointer user-select-none" for="autoRefreshToggle" style="font-size: 12px;">
                <span id="autoRefreshLabel">Auto-Refresh</span>
            </label>
        </div>

        {{-- Live Refresh Stats Button --}}
        <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-xs" onclick="refreshCacheMetrics(this)">
            <i class="fa-solid fa-arrows-rotate" id="refreshIcon"></i>
            <span id="refreshText">Refresh</span>
        </button>

        {{-- 1-Click Cache Warmup Engine --}}
        <button type="button" class="btn btn-outline-success btn-sm rounded-pill px-3 fw-bold d-inline-flex align-items-center gap-1.5 shadow-xs" onclick="executeCacheAction('{{ route('admin.cache.warmup') }}', 'Warming up cache...', this)">
            <i class="fa-solid fa-rocket"></i>
            <span>Warm Up</span>
        </button>

        {{-- 1-Click Production Turbo Optimizer --}}
        <button type="button" class="btn btn-primary btn-sm rounded-pill px-3.5 fw-bold d-inline-flex align-items-center gap-1.5 shadow-xs" onclick="executeCacheAction('{{ route('admin.cache.optimize') }}', 'Optimizing system...', this)">
            <i class="fa-solid fa-bolt"></i>
            <span>Optimize</span>
        </button>

        {{-- 1-Click Master Purge All Cache --}}
        <button type="button" class="btn btn-danger btn-sm rounded-pill px-3.5 fw-bold d-inline-flex align-items-center gap-1.5 shadow-xs text-white hover-lift" onclick="confirmMasterPurge(this)">
            <i class="fa-solid fa-trash-can"></i>
            <span>Purge All</span>
        </button>
    </div>
@endsection

@section('content')
<div class="d-flex flex-column gap-3.5">

    {{-- Dynamic Live Toast Notification Container --}}
    <div id="dynamicCacheAlert"></div>

    {{-- ========================================================================= --}}
    {{-- 1. DIAGNOSTICS & SYSTEM METRICS (4 CARDS)                                 --}}
    {{-- ========================================================================= --}}
    <div class="row row-cols-1 row-cols-sm-2 row-cols-xl-4 g-3">
        
        {{-- Card 1: Blade Views Cache --}}
        <div class="col">
            <div class="card cache-metric-widget p-3 d-flex flex-column justify-content-between h-100 border-0 shadow-xs rounded-4 bg-white">
                <div class="d-flex align-items-start justify-content-between mb-2">
                    <div>
                        <span class="text-muted small fw-bold text-uppercase font-monospace d-block mb-1" style="font-size: 0.70rem; letter-spacing: 0.5px;">Views Cache</span>
                        <h4 class="fw-bold text-dark mb-0 font-monospace" style="font-size: 1.30rem;" id="statViewFiles">
                            {{ number_format($stats['view_files_count']) }} files
                        </h4>
                    </div>
                    <div class="cache-avatar-icon bg-primary bg-opacity-10 text-primary flex-shrink-0 rounded-3">
                        <i class="fa-solid fa-tv"></i>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between pt-2 border-top">
                    <span class="small text-muted font-monospace" style="font-size: 0.75rem;" id="statViewSize">
                        Size: {{ $stats['view_cache_size'] }}
                    </span>
                    <span class="small text-success fw-semibold d-inline-flex align-items-center gap-1.5" style="font-size: 0.75rem;">
                        <span class="pulse-live-indicator"></span> Optimized
                    </span>
                </div>
            </div>
        </div>

        {{-- Card 2: Application Data Cache --}}
        <div class="col">
            <div class="card cache-metric-widget p-3 d-flex flex-column justify-content-between h-100 border-0 shadow-xs rounded-4 bg-white">
                <div class="d-flex align-items-start justify-content-between mb-2">
                    <div>
                        <span class="text-muted small fw-bold text-uppercase font-monospace d-block mb-1" style="font-size: 0.70rem; letter-spacing: 0.5px;">Data Cache</span>
                        <h4 class="fw-bold text-dark mb-0 font-monospace" style="font-size: 1.30rem;" id="statDataSize">
                            {{ $stats['data_cache_size'] }}
                        </h4>
                    </div>
                    <div class="cache-avatar-icon bg-info bg-opacity-10 text-info flex-shrink-0 rounded-3">
                        <i class="fa-solid fa-database"></i>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between pt-2 border-top">
                    <span class="small text-muted font-monospace" style="font-size: 0.75rem;">
                        Driver: <span class="badge bg-light text-dark border font-monospace">{{ strtoupper($stats['cache_driver']) }}</span>
                    </span>
                    <span class="small text-info fw-semibold font-monospace" style="font-size: 0.75rem;">
                        Session: {{ strtoupper($stats['session_driver']) }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Card 3: PHP OPcache Engine --}}
        <div class="col">
            <div class="card cache-metric-widget p-3 d-flex flex-column justify-content-between h-100 border-0 shadow-xs rounded-4 bg-white">
                <div class="d-flex align-items-start justify-content-between mb-2">
                    <div>
                        <span class="text-muted small fw-bold text-uppercase font-monospace d-block mb-1" style="font-size: 0.70rem; letter-spacing: 0.5px;">OPcache Engine</span>
                        <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-1.5" style="font-size: 1.05rem;" id="statOpcacheStatus">
                            @if($stats['opcache_enabled'])
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2.5 py-0.5 rounded-pill">Active</span>
                            @else
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border px-2.5 py-0.5 rounded-pill">Disabled</span>
                            @endif
                        </h5>
                    </div>
                    <div class="cache-avatar-icon bg-warning bg-opacity-10 text-warning flex-shrink-0 rounded-3">
                        <i class="fa-solid fa-microchip"></i>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between pt-2 border-top">
                    <span class="small text-muted" style="font-size: 0.75rem;">Hit Rate: <strong class="text-dark font-monospace" id="statOpcacheHit">{{ $stats['opcache_hit_rate'] }}</strong></span>
                    <span class="small text-muted font-monospace" style="font-size: 0.75rem;" id="statOpcacheMem">
                        RAM: {{ $stats['opcache_memory_used'] }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Card 4: Environment & Precompiled Routes --}}
        <div class="col">
            <div class="card cache-metric-widget p-3 d-flex flex-column justify-content-between h-100 border-0 shadow-xs rounded-4 bg-white">
                <div class="d-flex align-items-start justify-content-between mb-2">
                    <div>
                        <span class="text-muted small fw-bold text-uppercase font-monospace d-block mb-1" style="font-size: 0.70rem; letter-spacing: 0.5px;">Environment</span>
                        <h5 class="fw-bold text-dark mb-0 font-monospace" style="font-size: 1.05rem;">
                            PHP {{ $stats['php_version'] }} ({{ $stats['server_os'] }})
                        </h5>
                    </div>
                    <div class="cache-avatar-icon bg-success bg-opacity-10 text-success flex-shrink-0 rounded-3">
                        <i class="fa-solid fa-gauge-high"></i>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between pt-2 border-top">
                    <div class="d-flex align-items-center gap-1.5" id="statCachePills">
                        <span class="badge {{ $stats['is_config_cached'] ? 'bg-success bg-opacity-10 text-success border border-success border-opacity-25' : 'bg-light text-muted border' }} rounded-pill" style="font-size: 0.65rem;">
                            Config {{ $stats['is_config_cached'] ? '✓' : '✗' }}
                        </span>
                        <span class="badge {{ $stats['is_route_cached'] ? 'bg-success bg-opacity-10 text-success border border-success border-opacity-25' : 'bg-light text-muted border' }} rounded-pill" style="font-size: 0.65rem;">
                            Route {{ $stats['is_route_cached'] ? '✓' : '✗' }}
                        </span>
                    </div>
                    <span class="small text-success fw-bold font-monospace" style="font-size: 0.75rem;">
                        Turbo 99%
                    </span>
                </div>
            </div>
        </div>

    </div>

    {{-- ========================================================================= --}}
    {{-- 2. OPCACHE & MEMORY VISUALIZATION STRIP                                   --}}
    {{-- ========================================================================= --}}
    @if($stats['opcache_enabled'])
        <div class="card border-0 shadow-xs rounded-4 bg-white p-3.5">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-warning bg-opacity-10 text-warning p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="fa-solid fa-memory fs-5"></i>
                    </div>
                    <div>
                        <div class="fw-bold text-dark small">OPcache Memory Allocation</div>
                        <div class="text-muted small" style="font-size: 11.5px;">
                            Used: <strong class="text-dark font-monospace">{{ $stats['opcache_memory_used'] }}</strong> / Free: <strong class="text-dark font-monospace">{{ $stats['opcache_memory_free'] }}</strong>
                            ({{ $stats['opcache_scripts'] }} cached scripts)
                        </div>
                    </div>
                </div>
                <div class="flex-grow-1 mx-md-4" style="max-width: 420px;">
                    <div class="d-flex justify-content-between small text-muted font-monospace mb-1" style="font-size: 11px;">
                        <span>Usage</span>
                        <span id="statOpcachePercentLabel">{{ $stats['opcache_memory_percent'] ?? 0 }}%</span>
                    </div>
                    <div class="progress rounded-pill bg-light" style="height: 8px;">
                        <div class="progress-bar bg-warning rounded-pill progress-bar-striped progress-bar-animated" role="progressbar" 
                             style="width: {{ $stats['opcache_memory_percent'] ?? 0 }}%;" 
                             aria-valuenow="{{ $stats['opcache_memory_percent'] ?? 0 }}" aria-valuemin="0" aria-valuemax="100" id="statOpcacheProgressBar"></div>
                    </div>
                </div>
                <div>
                    <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-semibold shadow-xs" onclick="executeCacheAction('{{ route('admin.cache.clear-opcache') }}', 'Resetting OPcache...', this)">
                        <i class="fa-solid fa-rotate-left me-1"></i> Reset Bytecode
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- 3. GRANULAR CACHE MODULES (6 ACTIONS)                                     --}}
    {{-- ========================================================================= --}}
    <div class="card bg-white rounded-4 shadow-xs border-0 p-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between pb-3 mb-3 border-bottom gap-2">
            <div>
                <h6 class="fw-bold text-dark mb-0" style="font-size: 0.98rem;">
                    <i class="fa-solid fa-sliders text-primary me-2"></i>Granular Cache Modules
                </h6>
                <small class="text-muted">Targeted cache purging for instantaneous code, configuration, or template sync</small>
            </div>
            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-3 py-1 font-monospace small">
                6 Active Modules
            </span>
        </div>

        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-3">
            
            {{-- Module 1: Blade View Cache --}}
            <div class="col">
                <div class="cache-action-box p-3.5 h-100 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-primary text-white rounded-3 p-2"><i class="fa-solid fa-tv"></i></span>
                            <h6 class="fw-bold text-dark mb-0" style="font-size: 0.92rem;">Blade Views</h6>
                        </div>
                        <p class="small text-muted mb-3" style="font-size: 0.82rem; line-height: 1.45;">
                            Purges compiled Blade HTML templates. Run after frontend layout or template changes.
                        </p>
                    </div>
                    <button type="button" class="btn btn-outline-primary btn-sm rounded-pill w-100 fw-semibold d-flex align-items-center justify-content-center gap-1.5" onclick="executeCacheAction('{{ route('admin.cache.clear-views') }}', 'Purging Views...', this)">
                        <i class="fa-solid fa-broom"></i> <span>Clear Views</span>
                    </button>
                </div>
            </div>

            {{-- Module 2: Application Data Cache --}}
            <div class="col">
                <div class="cache-action-box p-3.5 h-100 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-info text-white rounded-3 p-2"><i class="fa-solid fa-layer-group"></i></span>
                            <h6 class="fw-bold text-dark mb-0" style="font-size: 0.92rem;">App Data</h6>
                        </div>
                        <p class="small text-muted mb-3" style="font-size: 0.82rem; line-height: 1.45;">
                            Flushes database queries, models, and cached application runtime keys from memory.
                        </p>
                    </div>
                    <button type="button" class="btn btn-outline-info btn-sm rounded-pill w-100 fw-semibold d-flex align-items-center justify-content-center gap-1.5" onclick="executeCacheAction('{{ route('admin.cache.clear-app') }}', 'Purging Data...', this)">
                        <i class="fa-solid fa-broom"></i> <span>Clear Data</span>
                    </button>
                </div>
            </div>

            {{-- Module 3: Config & Environment Cache --}}
            <div class="col">
                <div class="cache-action-box p-3.5 h-100 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-warning text-dark rounded-3 p-2"><i class="fa-solid fa-gear"></i></span>
                            <h6 class="fw-bold text-dark mb-0" style="font-size: 0.92rem;">Config & Env</h6>
                        </div>
                        <p class="small text-muted mb-3" style="font-size: 0.82rem; line-height: 1.45;">
                            Clears cached configuration and <code>.env</code> file settings for immediate reflection.
                        </p>
                    </div>
                    <button type="button" class="btn btn-outline-warning text-dark btn-sm rounded-pill w-100 fw-semibold d-flex align-items-center justify-content-center gap-1.5" onclick="executeCacheAction('{{ route('admin.cache.clear-config') }}', 'Purging Config...', this)">
                        <i class="fa-solid fa-broom"></i> <span>Clear Config</span>
                    </button>
                </div>
            </div>

            {{-- Module 4: Route Cache --}}
            <div class="col">
                <div class="cache-action-box p-3.5 h-100 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-secondary text-white rounded-3 p-2"><i class="fa-solid fa-route"></i></span>
                            <h6 class="fw-bold text-dark mb-0" style="font-size: 0.92rem;">Routes</h6>
                        </div>
                        <p class="small text-muted mb-3" style="font-size: 0.82rem; line-height: 1.45;">
                            Rebuilds route mapping tables. Use when new routes or endpoints return 404.
                        </p>
                    </div>
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill w-100 fw-semibold d-flex align-items-center justify-content-center gap-1.5" onclick="executeCacheAction('{{ route('admin.cache.clear-routes') }}', 'Purging Routes...', this)">
                        <i class="fa-solid fa-broom"></i> <span>Clear Routes</span>
                    </button>
                </div>
            </div>

            {{-- Module 5: PHP OPcache Bytecode --}}
            <div class="col">
                <div class="cache-action-box p-3.5 h-100 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-danger text-white rounded-3 p-2"><i class="fa-solid fa-bolt"></i></span>
                            <h6 class="fw-bold text-dark mb-0" style="font-size: 0.92rem;">OPcache</h6>
                        </div>
                        <p class="small text-muted mb-3" style="font-size: 0.82rem; line-height: 1.45;">
                            Resets PHP bytecode cache in server memory to recompile updated PHP scripts immediately.
                        </p>
                    </div>
                    <button type="button" class="btn btn-outline-danger btn-sm rounded-pill w-100 fw-semibold d-flex align-items-center justify-content-center gap-1.5" onclick="executeCacheAction('{{ route('admin.cache.clear-opcache') }}', 'Resetting OPcache...', this)">
                        <i class="fa-solid fa-rotate-left"></i> <span>Reset OPcache</span>
                    </button>
                </div>
            </div>

            {{-- Module 6: Temp Images & Resized Cache --}}
            <div class="col">
                <div class="cache-action-box p-3.5 h-100 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-success text-white rounded-3 p-2"><i class="fa-solid fa-images"></i></span>
                            <h6 class="fw-bold text-dark mb-0" style="font-size: 0.92rem;">Temp Images</h6>
                        </div>
                        <p class="small text-muted mb-3" style="font-size: 0.82rem; line-height: 1.45;">
                            Deletes auto-generated temporary thumbnails and cached image artifacts to reclaim disk space.
                        </p>
                    </div>
                    <button type="button" class="btn btn-outline-success btn-sm rounded-pill w-100 fw-semibold d-flex align-items-center justify-content-center gap-1.5" onclick="executeCacheAction('{{ route('admin.cache.clear-images') }}', 'Cleaning Images...', this)">
                        <i class="fa-solid fa-trash-can"></i> <span>Clear Temp</span>
                    </button>
                </div>
            </div>

        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- 4. ACTIVE APPLICATION CACHE KEY REGISTRY & INSPECTOR                     --}}
    {{-- ========================================================================= --}}
    <div class="card bg-white rounded-4 shadow-xs border-0 overflow-hidden">
        <div class="card-header bg-white d-flex flex-wrap align-items-center justify-content-between py-3 px-4 border-bottom gap-3">
            <div class="d-flex align-items-center gap-2.5">
                <div class="rounded-3 bg-primary bg-opacity-10 text-primary p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                    <i class="fa-solid fa-key"></i>
                </div>
                <div>
                    <h6 class="fw-bold text-dark mb-0" style="font-size: 0.95rem;">Cache Key Inspector</h6>
                    <small class="text-muted" style="font-size: 11px;">Monitor, inspect payload, and flush critical memory keys</small>
                </div>
            </div>

            {{-- Search & Bulk Actions --}}
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <div class="input-group input-group-sm" style="width: 220px;">
                    <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                    <input type="text" id="cacheKeySearchInput" class="form-control bg-light border-start-0 ps-0" placeholder="Search keys..." onkeyup="filterCacheKeysTable()">
                </div>
                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-semibold d-none" id="bulkFlushBtn" onclick="bulkDeleteSelectedKeys()">
                    <i class="fa-solid fa-trash-can me-1"></i> Flush (<span id="bulkFlushCount">0</span>)
                </button>
                <span class="badge bg-light text-dark border rounded-pill px-3 py-1 font-monospace small">
                    {{ count($cachedKeys) }} Keys Registered
                </span>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small" id="cacheKeysTable">
                    <thead class="table-light font-monospace text-uppercase text-muted" style="font-size: 11px;">
                        <tr>
                            <th class="ps-4 py-2.5" style="width: 40px;">
                                <input class="form-check-input cursor-pointer" type="checkbox" id="selectAllKeysCheckbox" onchange="toggleSelectAllKeys(this)">
                            </th>
                            <th class="py-2.5" style="width: 240px;">Key</th>
                            <th class="py-2.5">Description & Purpose</th>
                            <th class="py-2.5" style="width: 130px;">Type</th>
                            <th class="py-2.5" style="width: 120px;">Status</th>
                            <th class="text-end pe-4 py-2.5" style="width: 160px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cachedKeys as $ck)
                            <tr class="cache-key-row" data-key="{{ strtolower($ck['key']) }}" data-label="{{ strtolower($ck['label']) }}">
                                <td class="ps-4">
                                    <input class="form-check-input key-select-checkbox cursor-pointer" type="checkbox" value="{{ $ck['key'] }}" onchange="updateBulkFlushButton()">
                                </td>
                                <td class="font-monospace text-dark fw-bold">
                                    <div class="d-flex align-items-center gap-1.5">
                                        <code>{{ $ck['key'] }}</code>
                                        <button type="button" class="btn btn-xs btn-link text-muted p-0 hover-opacity" title="Copy Key" onclick="copyToClipboard('{{ $ck['key'] }}')">
                                            <i class="fa-regular fa-copy"></i>
                                        </button>
                                    </div>
                                </td>
                                <td>
                                    <span class="d-block fw-semibold text-dark">{{ $ck['label'] }}</span>
                                    <span class="text-muted" style="font-size: 11.5px;">{{ $ck['description'] }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-secondary border font-monospace px-2 py-0.5">
                                        {{ $ck['type'] }}
                                    </span>
                                </td>
                                <td>
                                    @if($ck['is_cached'])
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2.5 py-1">
                                            <i class="fa-solid fa-circle-check me-1"></i> Cached
                                        </span>
                                    @else
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border rounded-pill px-2.5 py-1">
                                            <i class="fa-solid fa-circle-minus me-1"></i> Empty
                                        </span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-flex align-items-center justify-content-end gap-1.5">
                                        <button type="button" class="btn btn-sm btn-outline-info text-dark rounded-pill px-2.5 py-0.5 fw-semibold" onclick="inspectCacheKeyContent('{{ $ck['key'] }}')" title="Inspect Payload">
                                            <i class="fa-solid fa-eye text-info me-1"></i> Preview
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2 py-0.5" onclick="deleteSingleKey('{{ $ck['key'] }}', this)" title="Flush from Memory">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    No cache keys registered.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

{{-- ========================================================================= --}}
{{-- 5. CACHE KEY PAYLOAD INSPECTOR MODAL                                      --}}
{{-- ========================================================================= --}}
<div class="modal fade" id="cacheKeyInspectorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <div class="modal-header bg-light py-3 px-4 border-bottom">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle bg-info bg-opacity-10 text-info p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                        <i class="fa-solid fa-code"></i>
                    </div>
                    <div>
                        <h6 class="modal-title fw-bold text-dark mb-0">Cache Key Payload Inspector</h6>
                        <small class="text-muted" id="modalKeySubtitle"></small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="cacheKeyInspectorBody">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                    <div class="mt-2 text-muted small">Loading payload...</div>
                </div>
            </div>
            <div class="modal-footer bg-light py-2.5 px-4 d-flex align-items-center justify-content-between" id="cacheKeyInspectorFooter">
                <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Close</button>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" id="copyPayloadBtn" onclick="copyInspectorPayload()">
                        <i class="fa-regular fa-copy me-1"></i> Copy Payload
                    </button>
                    <button type="button" class="btn btn-sm btn-danger rounded-pill px-3 fw-semibold text-white" id="flushInspectorKeyBtn">
                        <i class="fa-solid fa-trash-can me-1"></i> Flush Key
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
/* ── Premium Modern Cache Management Styling ── */
.cache-metric-widget {
    transition: transform 0.22s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.22s ease;
    min-height: 124px;
}
.cache-metric-widget:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08) !important;
}
.cache-avatar-icon {
    width: 44px;
    height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.15rem;
}
.cache-action-box {
    background: #ffffff;
    border-radius: 14px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
    transition: all 0.2s ease;
}
.cache-action-box:hover {
    border-color: #cbd5e1;
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
}
.pulse-live-indicator {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    display: inline-block;
    background-color: #10b981;
    box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
    animation: pulseGlowRing 1.8s infinite cubic-bezier(0.66, 0, 0, 1);
}
@keyframes pulseGlowRing {
    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
    70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
}
.hover-lift {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.hover-lift:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(220, 53, 69, 0.3) !important;
}
pre.payload-code-block {
    max-height: 380px;
    overflow-y: auto;
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    font-size: 12px;
    background: #0f172a;
    color: #e2e8f0;
    border-radius: 10px;
    padding: 14px;
}
</style>
@endpush

@push('scripts')
<script>
    let autoRefreshTimer = null;

    // 1. Dynamic AJAX Cache Action Execution with live spinner & toast
    function executeCacheAction(url, loadingText, btnElement) {
        let originalContent = '';
        if (btnElement) {
            originalContent = btnElement.innerHTML;
            btnElement.disabled = true;
            btnElement.innerHTML = `<span class="spinner-border spinner-border-sm me-1.5" role="status"></span><span>${loadingText || 'Processing...'}</span>`;
        }

        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showCacheAlert('success', data.message || 'Action executed successfully.');
                refreshCacheMetrics();
            } else {
                showCacheAlert('danger', data.message || 'Operation failed.');
            }
        })
        .catch(() => {
            showCacheAlert('danger', 'Server connection error.');
        })
        .finally(() => {
            if (btnElement) {
                btnElement.disabled = false;
                btnElement.innerHTML = originalContent;
            }
        });
    }

    // 2. Confirm & Purge Master All Caches
    function confirmMasterPurge(btnElement) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Purge All Caches?',
                text: 'This will purge all application data, precompiled views, routes, config, and opcode caches.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fa-solid fa-trash-can me-1"></i> Yes, Purge All',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    executeCacheAction('{{ route("admin.cache.clear-all") }}', 'Purging All...', btnElement);
                }
            });
        } else if (confirm('Are you sure you want to purge all system, view, config, and route caches?')) {
            executeCacheAction('{{ route("admin.cache.clear-all") }}', 'Purging All...', btnElement);
        }
    }

    // 3. Delete a Single Cache Key
    function deleteSingleKey(key, btnElement) {
        let orig = btnElement ? btnElement.innerHTML : '';
        if (btnElement) {
            btnElement.disabled = true;
            btnElement.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
        }

        fetch('{{ route("admin.cache.delete-key") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ key: key })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showCacheAlert('success', data.message);
                setTimeout(() => window.location.reload(), 500);
            } else {
                showCacheAlert('danger', data.message);
            }
        })
        .finally(() => {
            if (btnElement) {
                btnElement.disabled = false;
                btnElement.innerHTML = orig;
            }
        });
    }

    // 4. Inspect Cache Key Payload
    let currentInspectedPayload = '';
    function inspectCacheKeyContent(key) {
        const modalEl = document.getElementById('cacheKeyInspectorModal');
        const subtitleEl = document.getElementById('modalKeySubtitle');
        const bodyEl = document.getElementById('cacheKeyInspectorBody');
        const flushBtn = document.getElementById('flushInspectorKeyBtn');
        if (!modalEl || !bodyEl) return;

        subtitleEl.textContent = `Key: ${key}`;
        bodyEl.innerHTML = `
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status"></div>
                <div class="mt-2 text-muted small">Loading key payload from memory...</div>
            </div>
        `;

        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();

        fetch('{{ route("admin.cache.inspect-key") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ key: key })
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                bodyEl.innerHTML = `<div class="alert alert-danger mb-0">${data.message || 'Key inspection failed.'}</div>`;
                return;
            }

            currentInspectedPayload = data.preview || '';

            bodyEl.innerHTML = `
                <div class="row g-3 mb-3">
                    <div class="col-sm-4">
                        <div class="p-2.5 bg-light rounded-3">
                            <div class="small text-muted mb-0.5">Status</div>
                            <span class="badge ${data.exists ? 'bg-success' : 'bg-secondary'} rounded-pill px-2.5 py-1">
                                ${data.exists ? 'In Memory (Cached)' : 'Empty / Expired'}
                            </span>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="p-2.5 bg-light rounded-3">
                            <div class="small text-muted mb-0.5">Type</div>
                            <span class="fw-bold font-monospace text-dark">${data.type || 'N/A'}</span>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="p-2.5 bg-light rounded-3">
                            <div class="small text-muted mb-0.5">Memory Size</div>
                            <span class="fw-bold font-monospace text-dark">${data.size || '0 B'}</span>
                        </div>
                    </div>
                </div>

                <div class="d-flex align-items-center justify-content-between mb-1.5">
                    <span class="small fw-bold text-dark font-monospace">Payload Preview:</span>
                </div>
                ${data.preview ? `
                    <pre class="payload-code-block mb-0"><code>${escapeHtml(data.preview)}</code></pre>
                ` : `
                    <div class="p-4 text-center text-muted bg-light rounded-3 border">
                        Key contains null or empty value.
                    </div>
                `}
            `;

            if (flushBtn) {
                flushBtn.onclick = function() {
                    modal.hide();
                    deleteSingleKey(key);
                };
            }
        })
        .catch(err => {
            bodyEl.innerHTML = `<div class="alert alert-danger mb-0">Server error inspecting key.</div>`;
        });
    }

    function copyInspectorPayload() {
        if (!currentInspectedPayload) return;
        navigator.clipboard.writeText(currentInspectedPayload).then(() => {
            showCacheAlert('success', 'Payload copied to clipboard!');
        });
    }

    function escapeHtml(text) {
        if (!text) return '';
        return text
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    // 5. Bulk Delete Selected Keys
    function toggleSelectAllKeys(master) {
        const checkboxes = document.querySelectorAll('.key-select-checkbox');
        checkboxes.forEach(cb => cb.checked = master.checked);
        updateBulkFlushButton();
    }

    function updateBulkFlushButton() {
        const checked = document.querySelectorAll('.key-select-checkbox:checked');
        const btn = document.getElementById('bulkFlushBtn');
        const countSpan = document.getElementById('bulkFlushCount');
        if (checked.length > 0) {
            btn.classList.remove('d-none');
            countSpan.textContent = checked.length;
        } else {
            btn.classList.add('d-none');
        }
    }

    function bulkDeleteSelectedKeys() {
        const checked = document.querySelectorAll('.key-select-checkbox:checked');
        const keys = Array.from(checked).map(cb => cb.value);
        if (keys.length === 0) return;

        if (!confirm(`Purge ${keys.length} selected cache key(s)?`)) return;

        fetch('{{ route("admin.cache.bulk-delete-keys") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ keys: keys })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showCacheAlert('success', data.message);
                setTimeout(() => window.location.reload(), 500);
            } else {
                showCacheAlert('danger', data.message);
            }
        });
    }

    // 6. Table Search Filter
    function filterCacheKeysTable() {
        const query = document.getElementById('cacheKeySearchInput').value.toLowerCase().trim();
        const rows = document.querySelectorAll('#cacheKeysTable tbody tr.cache-key-row');
        rows.forEach(row => {
            const key = row.getAttribute('data-key') || '';
            const label = row.getAttribute('data-label') || '';
            if (key.includes(query) || label.includes(query)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // 7. Live AJAX Cache Metrics Refresher
    function refreshCacheMetrics(btn) {
        const icon = document.getElementById('refreshIcon');
        const text = document.getElementById('refreshText');
        if (icon) icon.classList.add('fa-spin');
        if (text) text.textContent = 'Loading...';

        fetch('{{ route("admin.cache.stats-json") }}')
            .then(res => res.json())
            .then(data => {
                if (data.success && data.stats) {
                    const st = data.stats;
                    const vFiles = document.getElementById('statViewFiles');
                    const vSize = document.getElementById('statViewSize');
                    const dSize = document.getElementById('statDataSize');
                    const opHit = document.getElementById('statOpcacheHit');
                    const opMem = document.getElementById('statOpcacheMem');
                    const opBar = document.getElementById('statOpcacheProgressBar');
                    const opLabel = document.getElementById('statOpcachePercentLabel');

                    if (vFiles) vFiles.textContent = Number(st.view_files_count).toLocaleString() + ' files';
                    if (vSize) vSize.textContent = 'Size: ' + st.view_cache_size;
                    if (dSize) dSize.textContent = st.data_cache_size;
                    if (opHit) opHit.textContent = st.opcache_hit_rate;
                    if (opMem) opMem.textContent = 'RAM: ' + st.opcache_memory_used;
                    if (opBar && st.opcache_memory_percent) opBar.style.width = st.opcache_memory_percent + '%';
                    if (opLabel && st.opcache_memory_percent) opLabel.textContent = st.opcache_memory_percent + '%';

                    showCacheAlert('info', 'Metrics updated (' + data.timestamp + ')');
                }
            })
            .catch(() => {})
            .finally(() => {
                if (icon) icon.classList.remove('fa-spin');
                if (text) text.textContent = 'Refresh';
            });
    }

    // 8. Auto-Refresh Toggle
    function toggleAutoRefresh(cb) {
        const label = document.getElementById('autoRefreshLabel');
        if (cb.checked) {
            label.textContent = 'Live (30s)';
            label.classList.add('text-success');
            autoRefreshTimer = setInterval(() => refreshCacheMetrics(), 30000);
            showCacheAlert('info', 'Auto-refresh active (every 30 seconds).');
        } else {
            label.textContent = 'Auto-Refresh';
            label.classList.remove('text-success');
            if (autoRefreshTimer) clearInterval(autoRefreshTimer);
        }
    }

    // 9. Dynamic Toast Alert Display
    function showCacheAlert(type, message) {
        const container = document.getElementById('dynamicCacheAlert');
        if (!container) return;
        container.innerHTML = `
            <div class="alert alert-${type} alert-dismissible fade show d-flex align-items-center mb-0 rounded-3 shadow-xs border-0 border-start border-4 border-${type} bg-white py-2.5 px-3" role="alert">
                <i class="fa-solid fa-${type === 'success' ? 'circle-check text-success' : (type === 'info' ? 'circle-info text-info' : 'triangle-exclamation text-danger')} fs-5 me-2.5"></i>
                <div class="fw-semibold small text-dark">${message}</div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        setTimeout(() => {
            const el = container.querySelector('.alert');
            if (el) {
                el.classList.remove('show');
                setTimeout(() => el.remove(), 200);
            }
        }, 5000);
    }

    // 10. Copy Helper
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            showCacheAlert('success', `Copied "${text}" to clipboard!`);
        });
    }
</script>
@endpush
@endsection
