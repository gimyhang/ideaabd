@extends('layouts.admin')

@section('title', 'Enterprise Cache & Performance Tuning Hub — আইডিয়া প্রকাশন')
@section('heading', 'ক্যাশ ব্যবস্থাপনা ও পারফরম্যান্স টিউনিং হাব')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">ড্যাশবোর্ড</a></li>
    <li class="breadcrumb-item active">ক্যাশ ও পারফরম্যান্স হাব</li>
@endsection

@section('actions')
    <div class="d-flex flex-wrap align-items-center gap-2">
        {{-- Live Refresh Stats Button --}}
        <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-bold d-inline-flex align-items-center gap-1.5 shadow-xs" onclick="refreshCacheMetrics(this)">
            <i class="fas fa-arrows-rotate" id="refreshIcon"></i>
            <span id="refreshText">রিফ্রেশ</span>
        </button>

        {{-- 1-Click Cache Warmup Engine --}}
        <button type="button" class="btn btn-outline-success btn-sm rounded-pill px-3 fw-bold d-inline-flex align-items-center gap-1.5 shadow-xs" onclick="executeCacheAction('{{ route('admin.cache.warmup') }}', 'ক্যাশ প্রি-লোড ও ওয়ার্ম-আপ হচ্ছে...', this)">
            <i class="fas fa-rocket"></i>
            <span>ক্যাশ ওয়ার্ম-আপ (Warm Up)</span>
        </button>

        {{-- 1-Click Production Turbo Optimizer --}}
        <button type="button" class="btn btn-primary btn-sm rounded-pill px-3.5 fw-bold d-inline-flex align-items-center gap-1.5 shadow-sm" onclick="executeCacheAction('{{ route('admin.cache.optimize') }}', 'টার্বো অপ্টিমাইজেশন চলছে...', this)">
            <i class="fas fa-bolt"></i>
            <span>টার্বো অপ্টিমাইজ (Run Optimize)</span>
        </button>

        {{-- 1-Click Master Purge All Cache --}}
        <button type="button" class="btn btn-danger btn-sm rounded-pill px-3.5 fw-bold d-inline-flex align-items-center gap-1.5 shadow-sm text-white" onclick="confirmMasterPurge(this)">
            <i class="fas fa-trash-can"></i>
            <span>সমস্ত ক্যাশ ক্লিয়ার</span>
        </button>
    </div>
@endsection

@section('content')
<style>
/* ── World-Class Cache & Performance Tuning Hub Styling ── */
.cache-metric-widget {
    background: #ffffff;
    border-radius: 14px;
    border: 1px solid rgba(226, 232, 240, 0.8);
    box-shadow: 0 4px 16px rgba(15, 23, 42, 0.03);
    transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1);
    min-height: 128px;
}

.cache-metric-widget:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(15, 23, 42, 0.06);
    border-color: rgba(14, 165, 233, 0.3);
}

.cache-avatar-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.15rem;
}

.cache-action-box {
    background: #ffffff;
    border-radius: 14px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.02);
    transition: all 0.2s ease;
}

.cache-action-box:hover {
    border-color: #cbd5e1;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.04);
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
</style>

<div class="d-flex flex-column gap-3.5">

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-0 rounded-3 shadow-xs border-0 border-start border-4 border-success bg-white py-2.5 px-3" role="alert">
            <i class="fas fa-circle-check text-success fs-5 me-2.5"></i>
            <div class="fw-semibold small text-dark">{{ session('success') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center mb-0 rounded-3 shadow-xs border-0 border-start border-4 border-danger bg-white py-2.5 px-3" role="alert">
            <i class="fas fa-triangle-exclamation text-danger fs-5 me-2.5"></i>
            <div class="fw-semibold small text-dark">{{ session('error') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Dynamic Live Toast Notification Container -->
    <div id="dynamicCacheAlert"></div>

    <!-- 1. Symmetrical 4-Card Diagnostics Grid -->
    <div class="row row-cols-1 row-cols-sm-2 row-cols-xl-4 g-3">
        
        {{-- Card 1: Blade View Cache --}}
        <div class="col">
            <div class="card cache-metric-widget p-3 d-flex flex-column justify-content-between h-100">
                <div class="d-flex align-items-start justify-content-between mb-2">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase font-monospace d-block mb-1" style="font-size: 0.70rem; letter-spacing: 0.5px;">ভিউ ক্যাশ (Blade)</span>
                        <h4 class="fw-bold text-dark mb-0 font-monospace" style="font-size: 1.25rem;" id="statViewFiles">
                            {{ $stats['view_files_count'] }} টি ফাইল
                        </h4>
                    </div>
                    <div class="cache-avatar-icon bg-primary-subtle text-primary flex-shrink-0">
                        <i class="fas fa-tv"></i>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between pt-2 border-top">
                    <span class="small text-muted font-monospace" style="font-size: 0.75rem;" id="statViewSize">
                        সাইজ: {{ $stats['view_cache_size'] }}
                    </span>
                    <span class="small text-success fw-semibold d-inline-flex align-items-center gap-1.5" style="font-size: 0.75rem;">
                        <span class="pulse-live-indicator"></span> অপ্টিমাইজড
                    </span>
                </div>
            </div>
        </div>

        {{-- Card 2: Application Data Cache --}}
        <div class="col">
            <div class="card cache-metric-widget p-3 d-flex flex-column justify-content-between h-100">
                <div class="d-flex align-items-start justify-content-between mb-2">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase font-monospace d-block mb-1" style="font-size: 0.70rem; letter-spacing: 0.5px;">অ্যাপ ডেটা ক্যাশ</span>
                        <h4 class="fw-bold text-dark mb-0 font-monospace" style="font-size: 1.25rem;" id="statDataSize">
                            {{ $stats['data_cache_size'] }}
                        </h4>
                    </div>
                    <div class="cache-avatar-icon bg-info-subtle text-info flex-shrink-0">
                        <i class="fas fa-database"></i>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between pt-2 border-top">
                    <span class="small text-muted font-monospace" style="font-size: 0.75rem;">
                        ড্রাইভার: <span class="badge bg-light text-dark border font-monospace">{{ strtoupper($stats['cache_driver']) }}</span>
                    </span>
                    <span class="small text-info fw-semibold font-monospace" style="font-size: 0.75rem;">
                        সেশন: {{ strtoupper($stats['session_driver']) }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Card 3: PHP OPcache Engine --}}
        <div class="col">
            <div class="card cache-metric-widget p-3 d-flex flex-column justify-content-between h-100">
                <div class="d-flex align-items-start justify-content-between mb-2">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase font-monospace d-block mb-1" style="font-size: 0.70rem; letter-spacing: 0.5px;">PHP OPcache ইঞ্জিন</span>
                        <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-1.5" style="font-size: 1.05rem;" id="statOpcacheStatus">
                            @if($stats['opcache_enabled'])
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-0.5">সক্রিয় (Active)</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border px-2.5 py-0.5">নিষ্ক্রিয় (Off)</span>
                            @endif
                        </h5>
                    </div>
                    <div class="cache-avatar-icon bg-warning-subtle text-warning flex-shrink-0">
                        <i class="fas fa-microchip"></i>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between pt-2 border-top">
                    <span class="small text-muted" style="font-size: 0.75rem;">হিট রেট: <strong class="text-dark font-monospace" id="statOpcacheHit">{{ $stats['opcache_hit_rate'] }}</strong></span>
                    <span class="small text-muted font-monospace" style="font-size: 0.75rem;" id="statOpcacheMem">
                        র‍্যাম: {{ $stats['opcache_memory_used'] }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Card 4: Production Routing & Config State --}}
        <div class="col">
            <div class="card cache-metric-widget p-3 d-flex flex-column justify-content-between h-100">
                <div class="d-flex align-items-start justify-content-between mb-2">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase font-monospace d-block mb-1" style="font-size: 0.70rem; letter-spacing: 0.5px;">এনভায়রনমেন্ট ও রুট</span>
                        <h5 class="fw-bold text-dark mb-0 font-monospace" style="font-size: 1.05rem;">
                            PHP {{ $stats['php_version'] }} ({{ $stats['server_os'] }})
                        </h5>
                    </div>
                    <div class="cache-avatar-icon bg-success-subtle text-success flex-shrink-0">
                        <i class="fas fa-gauge-high"></i>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between pt-2 border-top">
                    <div class="d-flex align-items-center gap-1.5" id="statCachePills">
                        <span class="badge {{ $stats['is_config_cached'] ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-light text-muted border' }}" style="font-size: 0.65rem;">
                            Config {{ $stats['is_config_cached'] ? '✓' : '✗' }}
                        </span>
                        <span class="badge {{ $stats['is_route_cached'] ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-light text-muted border' }}" style="font-size: 0.65rem;">
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

    <!-- 2. Six Granular Performance & Cache Clearing Actions (৬টি সুনির্দিষ্ট অ্যাকশন কার্ড) -->
    <div class="card bg-white rounded-4 shadow-sm border-0 p-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between pb-3 mb-3 border-bottom gap-2">
            <div>
                <h6 class="fw-bold text-dark mb-0" style="font-size: 0.98rem;">
                    <i class="fas fa-sliders text-primary me-2"></i>সুনির্দিষ্ট ক্যাশ মডিউল ক্লিয়ার ও টিউনিং
                </h6>
                <small class="text-muted">সাইটে কোনো নতুন ডিজাইন, রুট বা সেটিংস আপডেট তাৎক্ষণিক দেখতে সংশ্লিষ্ট ক্যাশ ক্লিয়ার করুন</small>
            </div>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1 font-monospace small">
                ৬টি সক্রিয় মডিউল
            </span>
        </div>

        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-3">
            
            {{-- Module 1: Blade View Cache --}}
            <div class="col">
                <div class="cache-action-box p-3.5 h-100 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-primary text-white rounded-3 p-2"><i class="fas fa-tv"></i></span>
                            <h6 class="fw-bold text-dark mb-0" style="font-size: 0.92rem;">Blade View Cache</h6>
                        </div>
                        <p class="small text-muted mb-3" style="font-size: 0.82rem; line-height: 1.45;">
                            ওয়েবসাইটের ডিজাইন, HTML বা Blade টেমপ্লেট ফাইলের পরিবর্তন সাথে সাথে দেখতে এই ক্যাশ ক্লিয়ার করুন।
                        </p>
                    </div>
                    <button type="button" class="btn btn-outline-primary btn-sm rounded-pill w-100 fw-semibold d-flex align-items-center justify-content-center gap-1.5" onclick="executeCacheAction('{{ route('admin.cache.clear-views') }}', 'ভিউ ক্যাশ ক্লিয়ার হচ্ছে...', this)">
                        <i class="fas fa-broom"></i> <span>ক্লিয়ার ভিউ ক্যাশ</span>
                    </button>
                </div>
            </div>

            {{-- Module 2: Application Data Cache --}}
            <div class="col">
                <div class="cache-action-box p-3.5 h-100 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-info text-white rounded-3 p-2"><i class="fas fa-layer-group"></i></span>
                            <h6 class="fw-bold text-dark mb-0" style="font-size: 0.92rem;">App Data & Model Cache</h6>
                        </div>
                        <p class="small text-muted mb-3" style="font-size: 0.82rem; line-height: 1.45;">
                            ডাটাবেজ কুয়েরি রেজাল্ট, সাইট সেটিংস ও সাময়িক সংরক্ষিত মেমোরি ডেটা ক্যাশ পরিষ্কার করে।
                        </p>
                    </div>
                    <button type="button" class="btn btn-outline-info btn-sm rounded-pill w-100 fw-semibold d-flex align-items-center justify-content-center gap-1.5" onclick="executeCacheAction('{{ route('admin.cache.clear-app') }}', 'ডেটা ক্যাশ ক্লিয়ার হচ্ছে...', this)">
                        <i class="fas fa-broom"></i> <span>ক্লিয়ার ডেটা ক্যাশ</span>
                    </button>
                </div>
            </div>

            {{-- Module 3: Config & Environment Cache --}}
            <div class="col">
                <div class="cache-action-box p-3.5 h-100 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-warning text-dark rounded-3 p-2"><i class="fas fa-gear"></i></span>
                            <h6 class="fw-bold text-dark mb-0" style="font-size: 0.92rem;">Config & Environment</h6>
                        </div>
                        <p class="small text-muted mb-3" style="font-size: 0.82rem; line-height: 1.45;">
                            <code>.env</code> বা <code>config/*.php</code> ফাইলে করা যেকোনো কনফিগারেশন পরিবর্তন কার্যকর করার জন্য।
                        </p>
                    </div>
                    <button type="button" class="btn btn-outline-warning text-dark btn-sm rounded-pill w-100 fw-semibold d-flex align-items-center justify-content-center gap-1.5" onclick="executeCacheAction('{{ route('admin.cache.clear-config') }}', 'কনফিগ ক্যাশ ক্লিয়ার হচ্ছে...', this)">
                        <i class="fas fa-broom"></i> <span>ক্লিয়ার কনফিগ ক্যাশ</span>
                    </button>
                </div>
            </div>

            {{-- Module 4: Route Cache --}}
            <div class="col">
                <div class="cache-action-box p-3.5 h-100 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-secondary text-white rounded-3 p-2"><i class="fas fa-route"></i></span>
                            <h6 class="fw-bold text-dark mb-0" style="font-size: 0.92rem;">Routing Table Cache</h6>
                        </div>
                        <p class="small text-muted mb-3" style="font-size: 0.82rem; line-height: 1.45;">
                            নতুন পেজ লিংক বা রাউট 404 দেখালে কিংবা নতুন রুট যুক্ত হলে এই ক্যাশ ক্লিয়ার করুন।
                        </p>
                    </div>
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill w-100 fw-semibold d-flex align-items-center justify-content-center gap-1.5" onclick="executeCacheAction('{{ route('admin.cache.clear-routes') }}', 'রুট ক্যাশ ক্লিয়ার হচ্ছে...', this)">
                        <i class="fas fa-broom"></i> <span>ক্লিয়ার রুট ক্যাশ</span>
                    </button>
                </div>
            </div>

            {{-- Module 5: PHP OPcache Bytecode --}}
            <div class="col">
                <div class="cache-action-box p-3.5 h-100 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-danger text-white rounded-3 p-2"><i class="fas fa-bolt"></i></span>
                            <h6 class="fw-bold text-dark mb-0" style="font-size: 0.92rem;">PHP OPcache Reset</h6>
                        </div>
                        <p class="small text-muted mb-3" style="font-size: 0.82rem; line-height: 1.45;">
                            পিএইচপি স্ক্রিপ্টের মেমোরি বাইটকোড অ্যাক্সিলারেটর ক্যাশ রিসেট করে কোড পরিবর্তন তাৎক্ষণিক কার্যকর করে।
                        </p>
                    </div>
                    <button type="button" class="btn btn-outline-danger btn-sm rounded-pill w-100 fw-semibold d-flex align-items-center justify-content-center gap-1.5" onclick="executeCacheAction('{{ route('admin.cache.clear-opcache') }}', 'OPcache রিসেট হচ্ছে...', this)">
                        <i class="fas fa-rotate-left"></i> <span>রিসেট OPcache</span>
                    </button>
                </div>
            </div>

            {{-- Module 6: Temp Images & Resized Cache --}}
            <div class="col">
                <div class="cache-action-box p-3.5 h-100 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-success text-white rounded-3 p-2"><i class="fas fa-images"></i></span>
                            <h6 class="fw-bold text-dark mb-0" style="font-size: 0.92rem;">Temp Thumbnails & Images</h6>
                        </div>
                        <p class="small text-muted mb-3" style="font-size: 0.82rem; line-height: 1.45;">
                            সাময়িক অটো-জেনারেটেড ইমেজ থাম্বনেল ও ক্যাশ ফাইল ডিলিট করে ডিস্ক স্পেস খালি করে।
                        </p>
                    </div>
                    <button type="button" class="btn btn-outline-success btn-sm rounded-pill w-100 fw-semibold d-flex align-items-center justify-content-center gap-1.5" onclick="executeCacheAction('{{ route('admin.cache.clear-images') }}', 'ইমেজ ক্যাশ পরিষ্কার হচ্ছে...', this)">
                        <i class="fas fa-trash-can"></i> <span>ক্লিন ইমেজ ক্যাশ</span>
                    </button>
                </div>
            </div>

        </div>
    </div>

    <!-- 3. Active Application Cache Key Registry Inspector -->
    <div class="card bg-white rounded-4 shadow-sm border-0 overflow-hidden">
        <div class="card-header bg-white d-flex flex-wrap align-items-center justify-content-between py-3 px-4 border-bottom gap-2">
            <div class="d-flex align-items-center gap-2.5">
                <div class="rounded-3 bg-primary-subtle text-primary p-2 d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                    <i class="fas fa-key"></i>
                </div>
                <div>
                    <h6 class="fw-bold text-dark mb-0" style="font-size: 0.95rem;">অ্যাপ্লিকেশন ক্যাশ কী ইন্সপেক্টর (Active Cache Keys)</h6>
                    <small class="text-muted" style="font-size: 11px;">ওয়েবসাইটের গুরুত্বপূর্ণ মেমোরি ডেটা ক্যাশ তালিকা ও স্থিতি</small>
                </div>
            </div>
            <span class="badge bg-light text-dark border rounded-pill px-3 py-1 font-monospace small">
                {{ count($cachedKeys) }} টি নিয়মিত ক্যাশ কী
            </span>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="table-light font-monospace text-uppercase text-muted">
                        <tr>
                            <th class="ps-4 py-2.5" style="width: 250px;">ক্যাশ কী (Key)</th>
                            <th class="py-2.5">বিবরণ ও উদ্দেশ্য</th>
                            <th class="py-2.5" style="width: 140px;">ক্যাটাগরি</th>
                            <th class="py-2.5" style="width: 120px;">বর্তমান স্ট্যাটাস</th>
                            <th class="text-end pe-4 py-2.5" style="width: 120px;">অ্যাকশন</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cachedKeys as $ck)
                            <tr>
                                <td class="ps-4 font-monospace text-dark fw-bold">
                                    <code>{{ $ck['key'] }}</code>
                                </td>
                                <td>
                                    <span class="d-block fw-semibold text-dark">{{ $ck['label'] }}</span>
                                    <span class="text-muted" style="font-size: 11px;">{{ $ck['description'] }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-secondary border font-monospace px-2 py-0.5">
                                        {{ $ck['type'] }}
                                    </span>
                                </td>
                                <td>
                                    @if($ck['is_cached'])
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1">
                                            <i class="fas fa-circle-check me-1"></i> Cached
                                        </span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border rounded-pill px-2.5 py-1">
                                            <i class="fas fa-circle-minus me-1"></i> Empty
                                        </span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <button type="button" class="btn btn-xs btn-outline-danger rounded-pill px-2 py-1" onclick="deleteSingleKey('{{ $ck['key'] }}', this)" title="ক্যাশ থেকে মুছুন">
                                        <i class="fas fa-trash-can me-1"></i> মুছুন
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
    // 1. Dynamic AJAX Cache Action Execution with live spinner & toast
    function executeCacheAction(url, loadingText, btnElement) {
        let originalContent = '';
        if (btnElement) {
            originalContent = btnElement.innerHTML;
            btnElement.disabled = true;
            btnElement.innerHTML = `<span class="spinner-border spinner-border-sm me-1.5" role="status"></span><span>${loadingText || 'প্রসেসিং...'}</span>`;
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
                showCacheAlert('success', data.message);
                refreshCacheMetrics();
            } else {
                showCacheAlert('danger', data.message || 'ত্রুটি ঘটেছে!');
            }
        })
        .catch(() => {
            showCacheAlert('danger', 'সার্ভারে রিকোয়েস্ট পাঠাতে ব্যর্থ হয়েছে।');
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
        if (!confirm('আপনি কি নিশ্চিত যে সমস্ত সিস্টেম ক্যাশ, ভিউ ক্যাশ, কনফিগ ও রুট ক্যাশ একযোগে ক্লিয়ার করতে চান?')) {
            return;
        }
        executeCacheAction('{{ route("admin.cache.clear-all") }}', 'সমস্ত ক্যাশ ক্লিয়ার হচ্ছে...', btnElement);
    }

    // 3. Delete a Single Cache Key
    function deleteSingleKey(key, btnElement) {
        let orig = btnElement.innerHTML;
        btnElement.disabled = true;
        btnElement.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

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
                setTimeout(() => window.location.reload(), 600);
            } else {
                showCacheAlert('danger', data.message);
            }
        })
        .finally(() => {
            btnElement.disabled = false;
            btnElement.innerHTML = orig;
        });
    }

    // 4. Live AJAX Cache Metrics Refresher
    function refreshCacheMetrics(btn) {
        const icon = document.getElementById('refreshIcon');
        const text = document.getElementById('refreshText');
        if (icon) icon.classList.add('fa-spin');
        if (text) text.textContent = 'লোড হচ্ছে...';

        fetch('{{ route("admin.cache.stats-json") }}')
            .then(res => res.json())
            .then(data => {
                if (data.success && data.stats) {
                    document.getElementById('statViewFiles').textContent = data.stats.view_files_count + ' টি ফাইল';
                    document.getElementById('statViewSize').textContent = 'সাইজ: ' + data.stats.view_cache_size;
                    document.getElementById('statDataSize').textContent = data.stats.data_cache_size;
                    if (document.getElementById('statOpcacheHit')) {
                        document.getElementById('statOpcacheHit').textContent = data.stats.opcache_hit_rate;
                    }
                    if (document.getElementById('statOpcacheMem')) {
                        document.getElementById('statOpcacheMem').textContent = 'র‍্যাম: ' + data.stats.opcache_memory_used;
                    }
                    showCacheAlert('info', 'ক্যাশ মেট্রিক্স সফলভাবে রিফ্রেশ হয়েছে (' + data.timestamp + ')');
                }
            })
            .catch(() => {})
            .finally(() => {
                if (icon) icon.classList.remove('fa-spin');
                if (text) text.textContent = 'রিফ্রেশ';
            });
    }

    // 5. Dynamic Toast Alert Display
    function showCacheAlert(type, message) {
        const container = document.getElementById('dynamicCacheAlert');
        container.innerHTML = `
            <div class="alert alert-${type} alert-dismissible fade show d-flex align-items-center mb-0 rounded-3 shadow-xs border-0 border-start border-4 border-${type} bg-white py-2.5 px-3" role="alert">
                <i class="fas fa-${type === 'success' ? 'circle-check text-success' : (type === 'info' ? 'circle-info text-info' : 'triangle-exclamation text-danger')} fs-5 me-2.5"></i>
                <div class="fw-semibold small text-dark">${message}</div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
    }
</script>
@endpush
@endsection
