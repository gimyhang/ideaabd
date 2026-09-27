@extends('layouts.admin')

@section('title', 'Cache Management & Performance Hub — ideaabd')
@section('heading', 'Cache Management & Performance Hub')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin-cache.css') }}">
@endpush

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.system-settings') }}">Settings</a></li>
    <li class="breadcrumb-item active">Cache & Performance</li>
@endsection

@section('actions')
    <div class="d-flex flex-wrap align-items-center gap-2.5">
        {{-- Auto-refresh Switch --}}
        <div class="form-check form-switch d-inline-flex align-items-center gap-2 px-3.5 py-2 bg-white border rounded-pill shadow-xs me-1">
            <input class="form-check-input ms-0 cursor-pointer" type="checkbox" role="switch" id="autoRefreshToggle" onchange="toggleAutoRefresh(this)">
            <label class="form-check-label small fw-bold text-muted cursor-pointer user-select-none mb-0" for="autoRefreshToggle" style="font-size: 13px;">
                <span id="autoRefreshLabel">Auto-Refresh</span>
            </label>
        </div>

        {{-- Live Refresh Stats Button --}}
        <button type="button" class="btn btn-white btn-sm rounded-pill px-3.5 py-2 fw-semibold d-inline-flex align-items-center gap-2 shadow-xs border hover-lift" onclick="refreshCacheMetrics(this)">
            <i class="fa-solid fa-arrows-rotate text-primary" id="refreshIcon"></i>
            <span id="refreshText">Refresh</span>
        </button>

        {{-- 1-Click Cache Warmup Engine --}}
        <button type="button" class="btn btn-cache-action btn-cache-success btn-sm px-4 py-2 fw-bold hover-lift" onclick="executeCacheAction('{{ route('admin.cache.warmup') }}', 'Warming up cache...', this)">
            <i class="fa-solid fa-rocket"></i>
            <span>Warm Up</span>
        </button>

        {{-- 1-Click Production Turbo Optimizer --}}
        <button type="button" class="btn btn-cache-action btn-cache-primary btn-sm px-4 py-2 fw-bold hover-lift" onclick="executeCacheAction('{{ route('admin.cache.optimize') }}', 'Optimizing system...', this)">
            <i class="fa-solid fa-bolt"></i>
            <span>Turbo Optimize</span>
        </button>

        {{-- 1-Click Master Purge All Cache --}}
        <button type="button" class="btn btn-cache-action btn-cache-danger btn-sm px-4 py-2 fw-bold text-white hover-lift" onclick="confirmMasterPurge(this)">
            <i class="fa-solid fa-trash-can"></i>
            <span>Purge All</span>
        </button>
    </div>
@endsection

@section('content')
<div class="cache-hub-container d-flex flex-column gap-4">

    {{-- Dynamic Live Floating Alert Container --}}
    <div id="dynamicCacheAlert" class="floating-alert-anchor"></div>

    {{-- ========================================================================= --}}
    {{-- 0. SUB-NAVIGATION & SYSTEM INFO STRIP                                     --}}
    {{-- ========================================================================= --}}
    <div class="cache-section-card p-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center flex-wrap gap-2">
                <a href="#sectionMetrics" class="btn btn-sm btn-light rounded-pill px-3.5 py-2 fw-semibold text-dark border">
                    <i class="fa-solid fa-chart-pie text-primary me-1.5"></i> 1. Diagnostics & Metrics
                </a>
                <a href="#sectionModules" class="btn btn-sm btn-light rounded-pill px-3.5 py-2 fw-semibold text-dark border">
                    <i class="fa-solid fa-sliders text-success me-1.5"></i> 2. Granular Modules
                </a>
                <a href="#sectionConsole" class="btn btn-sm btn-light rounded-pill px-3.5 py-2 fw-semibold text-dark border">
                    <i class="fa-solid fa-terminal text-warning me-1.5"></i> 3. Artisan Console
                </a>
                <a href="#sectionRegistry" class="btn btn-sm btn-light rounded-pill px-3.5 py-2 fw-semibold text-dark border">
                    <i class="fa-solid fa-key text-info me-1.5"></i> 4. Key Registry
                </a>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-light text-dark border font-monospace px-3 py-2 rounded-pill shadow-xs">
                    <i class="fa-brands fa-php text-primary me-1.5"></i> PHP {{ $stats['php_version'] }} ({{ $stats['server_os'] }})
                </span>
                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 font-monospace px-3 py-2 rounded-pill shadow-xs">
                    <span class="pulse-live-indicator me-1.5"></span> Live Engine
                </span>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- 1. DIAGNOSTICS & SYSTEM METRICS (4 CARDS)                                 --}}
    {{-- ========================================================================= --}}
    <div id="sectionMetrics" class="row g-4">
        
        {{-- Card 1: Blade Views Cache --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="cache-kpi-card cache-kpi-views">
                <div class="d-flex align-items-start justify-content-between mb-2">
                    <div>
                        <span class="text-muted small fw-bold text-uppercase font-monospace d-block mb-1">Views Cache</span>
                        <div class="cache-kpi-value" id="statViewFiles">
                            {{ number_format($stats['view_files_count']) }} files
                        </div>
                    </div>
                    <div class="cache-kpi-icon">
                        <i class="fa-solid fa-tv"></i>
                    </div>
                </div>
                <div class="cache-kpi-footer">
                    <span class="text-muted font-monospace" id="statViewSize">
                        Size: <strong class="text-dark">{{ $stats['view_cache_size'] }}</strong>
                    </span>
                    <button type="button" class="btn btn-sm btn-outline-primary rounded-circle p-1.5" title="Clear Blade Views" onclick="executeCacheAction('{{ route('admin.cache.clear-views') }}', 'Purging Views...', this)">
                        <i class="fa-solid fa-rotate"></i>
                    </button>
                </div>
            </div>
        </div>

        {{-- Card 2: Application Data Cache --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="cache-kpi-card cache-kpi-data">
                <div class="d-flex align-items-start justify-content-between mb-2">
                    <div>
                        <span class="text-muted small fw-bold text-uppercase font-monospace d-block mb-1">Data & Memory</span>
                        <div class="cache-kpi-value" id="statDataSize">
                            {{ $stats['data_cache_size'] }}
                        </div>
                    </div>
                    <div class="cache-kpi-icon">
                        <i class="fa-solid fa-database"></i>
                    </div>
                </div>
                <div class="cache-kpi-footer">
                    <span class="text-muted font-monospace">
                        Driver: <span class="badge bg-light text-dark border">{{ strtoupper($stats['cache_driver']) }}</span>
                    </span>
                    <span class="text-info fw-semibold font-monospace">
                        Session: {{ strtoupper($stats['session_driver']) }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Card 3: PHP OPcache Engine --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="cache-kpi-card cache-kpi-opcache">
                <div class="d-flex align-items-start justify-content-between mb-2">
                    <div>
                        <span class="text-muted small fw-bold text-uppercase font-monospace d-block mb-1">OPcache Engine</span>
                        <div class="cache-kpi-value d-flex align-items-center gap-2">
                            @if($stats['opcache_enabled'])
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-1 rounded-pill">Active</span>
                            @else
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border px-3 py-1 rounded-pill">Disabled</span>
                            @endif
                        </div>
                    </div>
                    <div class="cache-kpi-icon">
                        <i class="fa-solid fa-microchip"></i>
                    </div>
                </div>
                <div class="cache-kpi-footer">
                    <span class="text-muted">Hit Rate: <strong class="text-dark font-monospace" id="statOpcacheHit">{{ $stats['opcache_hit_rate'] }}</strong></span>
                    <span class="text-muted font-monospace" id="statOpcacheMem">
                        RAM: <strong class="text-dark">{{ $stats['opcache_memory_used'] }}</strong>
                    </span>
                </div>
            </div>
        </div>

        {{-- Card 4: Precompiled & Turbo Status --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="cache-kpi-card cache-kpi-turbo">
                <div class="d-flex align-items-start justify-content-between mb-2">
                    <div>
                        <span class="text-muted small fw-bold text-uppercase font-monospace d-block mb-1">Turbo Optimizer</span>
                        <div class="cache-kpi-value text-success">
                            99% Optimized
                        </div>
                    </div>
                    <div class="cache-kpi-icon">
                        <i class="fa-solid fa-gauge-high"></i>
                    </div>
                </div>
                <div class="cache-kpi-footer">
                    <div class="d-flex align-items-center gap-1.5 flex-wrap">
                        <span class="badge {{ $stats['is_config_cached'] ? 'bg-success text-white' : 'bg-light text-muted border' }} rounded-pill px-2 py-0.5">
                            Config {{ $stats['is_config_cached'] ? '✓' : '✗' }}
                        </span>
                        <span class="badge {{ $stats['is_route_cached'] ? 'bg-success text-white' : 'bg-light text-muted border' }} rounded-pill px-2 py-0.5">
                            Route {{ $stats['is_route_cached'] ? '✓' : '✗' }}
                        </span>
                        <span class="badge {{ $stats['is_events_cached'] ? 'bg-success text-white' : 'bg-light text-muted border' }} rounded-pill px-2 py-0.5">
                            Event {{ $stats['is_events_cached'] ? '✓' : '✗' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- ========================================================================= --}}
    {{-- 2. OPCACHE & MEMORY PROGRESS STRIP                                        --}}
    {{-- ========================================================================= --}}
    @if($stats['opcache_enabled'])
        <div class="cache-section-card p-4">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-warning bg-opacity-10 text-warning p-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px;">
                        <i class="fa-solid fa-memory fs-4"></i>
                    </div>
                    <div>
                        <div class="fw-bold text-dark mb-0.5">OPcache Bytecode Memory Allocation</div>
                        <div class="text-muted small">
                            Used: <strong class="text-dark font-monospace">{{ $stats['opcache_memory_used'] }}</strong> / Free: <strong class="text-dark font-monospace">{{ $stats['opcache_memory_free'] }}</strong>
                            ({{ $stats['opcache_scripts'] }} cached scripts compiled)
                        </div>
                    </div>
                </div>
                <div class="flex-grow-1 mx-md-4" style="max-width: 420px;">
                    <div class="d-flex justify-content-between small text-muted font-monospace mb-1.5">
                        <span>Bytecode RAM Usage</span>
                        <span id="statOpcachePercentLabel" class="fw-bold text-dark">{{ $stats['opcache_memory_percent'] ?? 0 }}%</span>
                    </div>
                    <div class="progress rounded-pill bg-light border" style="height: 10px;">
                        <div class="progress-bar bg-warning rounded-pill progress-bar-striped progress-bar-animated" role="progressbar" 
                             style="width: {{ $stats['opcache_memory_percent'] ?? 0 }}%;" 
                             aria-valuenow="{{ $stats['opcache_memory_percent'] ?? 0 }}" aria-valuemin="0" aria-valuemax="100" id="statOpcacheProgressBar"></div>
                    </div>
                </div>
                <div>
                    <button type="button" class="btn btn-cache-action btn-cache-danger btn-sm px-4 py-2 fw-semibold" onclick="executeCacheAction('{{ route('admin.cache.clear-opcache') }}', 'Resetting OPcache...', this)">
                        <i class="fa-solid fa-rotate-left"></i> Reset Bytecode
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- 3. GRANULAR CACHE MODULES (7 TARGETED MODULES)                            --}}
    {{-- ========================================================================= --}}
    <div id="sectionModules" class="cache-section-card p-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between pb-3.5 mb-4 border-bottom gap-3">
            <div>
                <h5 class="cache-header-title">
                    <i class="fa-solid fa-sliders text-primary"></i> Granular Cache Modules
                </h5>
                <p class="cache-header-subtitle">Targeted cache purging for instantaneous code, configuration, or template sync without logging users out.</p>
            </div>
            <div class="d-flex align-items-center gap-2.5">
                <div class="form-check me-2">
                    <input class="form-check-input cursor-pointer" type="checkbox" id="selectAllModulesCb">
                    <label class="form-check-label small fw-semibold text-muted cursor-pointer user-select-none" for="selectAllModulesCb">Select All</label>
                </div>
                <button type="button" class="btn btn-cache-action btn-cache-dark btn-sm px-3.5 py-1.5 fw-semibold" onclick="purgeMultipleSelectedModules()">
                    <i class="fa-solid fa-broom me-1 text-warning"></i> Purge Selected
                </button>
                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-3 py-2 font-monospace">
                    7 Active Modules
                </span>
            </div>
        </div>

        <div class="row g-4">
            
            {{-- Module 1: Blade View Cache --}}
            <div class="col-12 col-md-6 col-xl-4">
                <div class="cache-module-card">
                    <div>
                        <div class="cache-module-header">
                            <div class="d-flex align-items-center gap-2.5">
                                <div class="cache-module-badge bg-primary">
                                    <i class="fa-solid fa-tv"></i>
                                </div>
                                <h6 class="cache-module-title">Blade Views</h6>
                            </div>
                            <input type="checkbox" class="form-check-input module-select-cb cursor-pointer" data-route="{{ route('admin.cache.clear-views') }}" data-name="Blade Views">
                        </div>
                        <p class="cache-module-desc">
                            Purges compiled Blade HTML templates. Run after frontend layout, CSS, or blade changes.
                        </p>
                    </div>
                    <button type="button" class="btn btn-cache-action btn-cache-primary w-100" onclick="executeCacheAction('{{ route('admin.cache.clear-views') }}', 'Purging Views...', this)">
                        <i class="fa-solid fa-broom"></i> Clear Blade Views
                    </button>
                </div>
            </div>

            {{-- Module 2: Application Data Cache --}}
            <div class="col-12 col-md-6 col-xl-4">
                <div class="cache-module-card">
                    <div>
                        <div class="cache-module-header">
                            <div class="d-flex align-items-center gap-2.5">
                                <div class="cache-module-badge bg-info">
                                    <i class="fa-solid fa-layer-group"></i>
                                </div>
                                <h6 class="cache-module-title">App Data & Models</h6>
                            </div>
                            <input type="checkbox" class="form-check-input module-select-cb cursor-pointer" data-route="{{ route('admin.cache.clear-app') }}" data-name="App Data">
                        </div>
                        <p class="cache-module-desc">
                            Flushes database query results, cached models, and application runtime keys from memory.
                        </p>
                    </div>
                    <button type="button" class="btn btn-cache-action btn-cache-info w-100" onclick="executeCacheAction('{{ route('admin.cache.clear-app') }}', 'Purging Data...', this)">
                        <i class="fa-solid fa-broom"></i> Clear App Data
                    </button>
                </div>
            </div>

            {{-- Module 3: Config & Env Cache --}}
            <div class="col-12 col-md-6 col-xl-4">
                <div class="cache-module-card">
                    <div>
                        <div class="cache-module-header">
                            <div class="d-flex align-items-center gap-2.5">
                                <div class="cache-module-badge bg-success">
                                    <i class="fa-solid fa-gears"></i>
                                </div>
                                <h6 class="cache-module-title">Config & .env</h6>
                            </div>
                            <input type="checkbox" class="form-check-input module-select-cb cursor-pointer" data-route="{{ route('admin.cache.clear-config') }}" data-name="Config">
                        </div>
                        <p class="cache-module-desc">
                            Clears cached configuration and .env file settings for immediate reflection across the application.
                        </p>
                    </div>
                    <button type="button" class="btn btn-cache-action btn-cache-success w-100" onclick="executeCacheAction('{{ route('admin.cache.clear-config') }}', 'Purging Config...', this)">
                        <i class="fa-solid fa-broom"></i> Clear Config Cache
                    </button>
                </div>
            </div>

            {{-- Module 4: Route Cache --}}
            <div class="col-12 col-md-6 col-xl-4">
                <div class="cache-module-card">
                    <div>
                        <div class="cache-module-header">
                            <div class="d-flex align-items-center gap-2.5">
                                <div class="cache-module-badge bg-warning text-dark">
                                    <i class="fa-solid fa-route"></i>
                                </div>
                                <h6 class="cache-module-title">Routes Mapping</h6>
                            </div>
                            <input type="checkbox" class="form-check-input module-select-cb cursor-pointer" data-route="{{ route('admin.cache.clear-routes') }}" data-name="Routes">
                        </div>
                        <p class="cache-module-desc">
                            Rebuilds route mapping tables. Use when new routes or endpoints return 404 Not Found.
                        </p>
                    </div>
                    <button type="button" class="btn btn-cache-action btn-cache-warning w-100" onclick="executeCacheAction('{{ route('admin.cache.clear-routes') }}', 'Purging Routes...', this)">
                        <i class="fa-solid fa-broom"></i> Clear Route Cache
                    </button>
                </div>
            </div>

            {{-- Module 5: OPcache Reset --}}
            <div class="col-12 col-md-6 col-xl-4">
                <div class="cache-module-card">
                    <div>
                        <div class="cache-module-header">
                            <div class="d-flex align-items-center gap-2.5">
                                <div class="cache-module-badge bg-danger">
                                    <i class="fa-solid fa-microchip"></i>
                                </div>
                                <h6 class="cache-module-title">PHP OPcache</h6>
                            </div>
                            <input type="checkbox" class="form-check-input module-select-cb cursor-pointer" data-route="{{ route('admin.cache.clear-opcache') }}" data-name="OPcache">
                        </div>
                        <p class="cache-module-desc">
                            Resets PHP bytecode cache in server memory to recompile updated PHP scripts immediately.
                        </p>
                    </div>
                    <button type="button" class="btn btn-cache-action btn-cache-danger w-100" onclick="executeCacheAction('{{ route('admin.cache.clear-opcache') }}', 'Resetting OPcache...', this)">
                        <i class="fa-solid fa-rotate-left"></i> Reset OPcache
                    </button>
                </div>
            </div>

            {{-- Module 6: Temp Images & Thumbnails --}}
            <div class="col-12 col-md-6 col-xl-4">
                <div class="cache-module-card">
                    <div>
                        <div class="cache-module-header">
                            <div class="d-flex align-items-center gap-2.5">
                                <div class="cache-module-badge bg-purple">
                                    <i class="fa-solid fa-images"></i>
                                </div>
                                <h6 class="cache-module-title">Temp Image Artifacts</h6>
                            </div>
                            <input type="checkbox" class="form-check-input module-select-cb cursor-pointer" data-route="{{ route('admin.cache.clear-images') }}" data-name="Temp Images">
                        </div>
                        <p class="cache-module-desc">
                            Deletes auto-generated temporary thumbnails and cached image artifacts to reclaim disk space.
                        </p>
                    </div>
                    <button type="button" class="btn btn-cache-action btn-cache-purple w-100" onclick="executeCacheAction('{{ route('admin.cache.clear-images') }}', 'Clearing Images...', this)">
                        <i class="fa-solid fa-broom"></i> Clear Temp Images
                    </button>
                </div>
            </div>

            {{-- Module 7: Event & Listener Cache --}}
            <div class="col-12 col-md-6 col-xl-4">
                <div class="cache-module-card">
                    <div>
                        <div class="cache-module-header">
                            <div class="d-flex align-items-center gap-2.5">
                                <div class="cache-module-badge bg-dark">
                                    <i class="fa-solid fa-bell"></i>
                                </div>
                                <h6 class="cache-module-title">Events & Listeners</h6>
                            </div>
                            <input type="checkbox" class="form-check-input module-select-cb cursor-pointer" data-route="{{ route('admin.cache.clear-events') }}" data-name="Events">
                        </div>
                        <p class="cache-module-desc">
                            Purges cached event discovery and listener manifests for background tasks and mail triggers.
                        </p>
                    </div>
                    <button type="button" class="btn btn-cache-action btn-cache-dark w-100" onclick="executeCacheAction('{{ route('admin.cache.clear-events') }}', 'Purging Events...', this)">
                        <i class="fa-solid fa-broom"></i> Clear Event Cache
                    </button>
                </div>
            </div>

        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- 4. INTERACTIVE ARTISAN CONSOLE TERMINAL                                    --}}
    {{-- ========================================================================= --}}
    <div id="sectionConsole" class="cache-section-card p-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between pb-3.5 mb-4 border-bottom gap-3">
            <div>
                <h5 class="cache-header-title">
                    <i class="fa-solid fa-terminal text-warning"></i> Interactive Artisan Console
                </h5>
                <p class="cache-header-subtitle">Execute optimization and cache maintenance commands in real-time with streaming output.</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1.5 fw-semibold" onclick="copyTerminalOutput()">
                    <i class="fa-solid fa-copy me-1"></i> Copy Log
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1.5 fw-semibold" onclick="clearTerminalLog()">
                    <i class="fa-solid fa-eraser me-1"></i> Clear
                </button>
            </div>
        </div>

        {{-- Preset Command Chips --}}
        <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
            <span class="small text-muted fw-bold text-uppercase font-monospace me-1">Quick Presets:</span>
            <span class="terminal-preset-chip" onclick="runTerminalCommand('optimize')"><i class="fa-solid fa-bolt text-primary"></i> php artisan optimize</span>
            <span class="terminal-preset-chip" onclick="runTerminalCommand('view:clear')"><i class="fa-solid fa-tv text-info"></i> php artisan view:clear</span>
            <span class="terminal-preset-chip" onclick="runTerminalCommand('route:clear')"><i class="fa-solid fa-route text-warning"></i> php artisan route:clear</span>
            <span class="terminal-preset-chip" onclick="runTerminalCommand('config:clear')"><i class="fa-solid fa-gears text-success"></i> php artisan config:clear</span>
            <span class="terminal-preset-chip" onclick="runTerminalCommand('cache:clear')"><i class="fa-solid fa-database text-danger"></i> php artisan cache:clear</span>
            <span class="terminal-preset-chip" onclick="runTerminalCommand('event:clear')"><i class="fa-solid fa-bell text-secondary"></i> php artisan event:clear</span>
            <span class="terminal-preset-chip" onclick="runTerminalCommand('about')"><i class="fa-solid fa-circle-info text-info"></i> php artisan about</span>
        </div>

        {{-- Terminal Window --}}
        <div class="terminal-window mb-3">
            <div class="terminal-header">
                <div class="terminal-dots">
                    <span class="terminal-dot dot-red"></span>
                    <span class="terminal-dot dot-yellow"></span>
                    <span class="terminal-dot dot-green"></span>
                    <span class="ms-2 small text-muted font-monospace">ideaabd-cache-runner@production:~</span>
                </div>
                <div class="small text-muted font-monospace" id="terminalClock">{{ now()->format('h:i:s A') }}</div>
            </div>
            <pre class="terminal-screen mb-0" id="terminalOutput">// Ready for Artisan commands.
ideaabd-cache-runner@production:~$ Click any preset chip above or type a command below.</pre>
        </div>

        {{-- Custom Command Prompt Bar --}}
        <div class="input-group">
            <span class="input-group-text bg-dark text-white border-0 font-monospace px-3">$ php artisan</span>
            <input type="text" id="customArtisanCmd" class="form-control font-monospace border-0 bg-light px-3" placeholder="e.g. optimize, view:clear, config:cache, route:list">
            <button type="button" class="btn btn-cache-action btn-cache-primary px-4 fw-bold" onclick="runCustomTerminalCommand()">
                <i class="fa-solid fa-play me-1"></i> Execute
            </button>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- 5. CACHE KEY INSPECTOR & REGISTRY                                         --}}
    {{-- ========================================================================= --}}
    <div id="sectionRegistry" class="cache-section-card p-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between pb-3.5 mb-4 border-bottom gap-3">
            <div>
                <h5 class="cache-header-title">
                    <i class="fa-solid fa-key text-info"></i> Cache Key Inspector & Memory Registry
                </h5>
                <p class="cache-header-subtitle">Monitor, inspect JSON payloads, and flush critical application memory keys.</p>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2.5">
                <div class="input-group input-group-sm" style="width: 250px;">
                    <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                    <input type="text" id="cacheKeySearchInput" class="form-control border-start-0" placeholder="Search cache keys...">
                </div>
                <span class="badge bg-light text-dark border rounded-pill px-3 py-2 font-monospace">
                    <span id="cacheKeyVisibleCount">{{ count($cachedKeys) }}</span> Keys Registered
                </span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="cache-table" id="cacheKeysTable">
                <thead>
                    <tr>
                        <th style="width: 30%;">Key Identifier</th>
                        <th style="width: 35%;">Description & Purpose</th>
                        <th style="width: 15%;">Type / Category</th>
                        <th style="width: 10%;">Status</th>
                        <th style="width: 10%;" class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cachedKeys as $k)
                        <tr class="cache-key-row" data-key="{{ strtolower($k['key']) }}" data-label="{{ strtolower($k['label']) }}">
                            <td>
                                <div class="fw-bold text-dark font-monospace" style="font-size: 0.92rem;">
                                    {{ $k['key'] }}
                                </div>
                                <div class="text-muted small fs-xs font-monospace">TTL: {{ $k['ttl'] ?? 'Persistent' }}</div>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark small">{{ $k['label'] }}</div>
                                <div class="text-muted small" style="font-size: 0.8rem;">{{ $k['description'] }}</div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border font-monospace px-2.5 py-1 rounded-pill">
                                    {{ $k['type'] ?? 'General' }}
                                </span>
                            </td>
                            <td>
                                @if(!empty($k['is_cached']))
                                    <span class="badge bg-success-subtle text-success key-status-badge rounded-pill px-2.5 py-1 font-monospace">
                                        <i class="fa-solid fa-circle-check me-1"></i> Cached
                                    </span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning-emphasis key-status-badge rounded-pill px-2.5 py-1 font-monospace">
                                        <i class="fa-solid fa-hourglass-half me-1"></i> Empty
                                    </span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="d-flex align-items-center justify-content-end gap-1.5">
                                    <button type="button" class="btn btn-sm btn-outline-info rounded-circle p-2" title="Inspect JSON Payload" onclick="inspectKeyPayload('{{ $k['key'] }}')">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger rounded-circle p-2" title="Flush Memory Key" onclick="deleteSingleKey('{{ $k['key'] }}', this)">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-key fs-1 opacity-25 mb-2"></i>
                                <div>No cache keys registered in inspector.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

{{-- MODAL: JSON Payload Inspector Modal --}}
<div class="modal fade" id="keyInspectorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom px-4 py-3 bg-light rounded-top-4">
                <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2" id="keyModalTitle">
                    <i class="fa-solid fa-code text-info"></i> Payload Inspector
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="d-flex align-items-center justify-content-between p-3 bg-light border rounded-3 mb-3 font-monospace small">
                    <div>Key: <strong class="text-primary" id="keyModalKeyName">-</strong></div>
                    <div>Size: <strong class="text-dark" id="keyModalSize">-</strong></div>
                    <div>TTL: <strong class="text-success" id="keyModalTtl">-</strong></div>
                </div>

                <div class="terminal-window">
                    <div class="terminal-header">
                        <span class="small text-muted font-monospace">Payload Content (JSON)</span>
                        <button type="button" class="btn btn-xs btn-outline-light rounded-pill px-2.5" onclick="navigator.clipboard.writeText(document.getElementById('keyModalPayload').textContent); showCacheAlert('success', 'Payload copied!');">
                            <i class="fa-solid fa-copy me-1"></i> Copy
                        </button>
                    </div>
                    <pre class="terminal-screen mb-0" id="keyModalPayload" style="min-height: 250px; max-height: 400px; color: #38bdf8;">Loading payload from cache storage...</pre>
                </div>
            </div>
            <div class="modal-footer border-top px-4 py-3">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('js/admin-cache.js') }}"></script>
@endpush
