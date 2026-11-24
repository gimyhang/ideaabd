@extends('layouts.admin')

@section('title', 'Cache & Storage')
@section('heading', 'Cache & Storage')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin-cache.css') }}?v={{ @filemtime(public_path('css/admin-cache.css')) ?: 1 }}">
@endpush

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Cache</li>
@endsection

@section('actions')
    <div class="ck-head-actions">
        <label class="ck-live" title="প্রতি ৩০ সেকেন্ডে নিজে থেকে হালনাগাদ">
            <input type="checkbox" id="ckLive" checked>
            <span class="ck-live-dot" aria-hidden="true"></span>
            <span>Live</span>
        </label>
        <button type="button" class="ck-btn ck-btn--ghost" id="ckRefresh"><i class="fa-solid fa-rotate"></i><span>Refresh</span></button>
    </div>
@endsection

@section('content')
@php
    $clearCards = [
        ['views',  'clear-views',  'fa-file-code',  'is-violet', 'Compiled views', 'ডিজাইন বদলের পর পুরনো চেহারা দেখালে', $stats['view_files_count'] . ' ফাইল · ' . $stats['view_cache_size']],
        ['app',    'clear-app',    'fa-database',   'is-blue',   'Data cache',     'সেটিংস, মেনু বা দাম বদলালেও পুরনোটা দেখালে', $stats['data_files_count'] . ' ফাইল · ' . $stats['data_cache_size']],
        ['config', 'clear-config', 'fa-sliders',    'is-amber',  'Config',         '.env ফাইল বদলের পর', $stats['is_config_cached'] ? 'ক্যাশ করা আছে' : 'ক্যাশ নেই'],
        ['routes', 'clear-routes', 'fa-route',      'is-teal',   'Routes',         'নতুন পেজ বা লিংক কাজ না করলে', $stats['is_route_cached'] ? 'ক্যাশ করা আছে' : 'ক্যাশ নেই'],
        ['events', 'clear-events', 'fa-bolt',       'is-pink',   'Events',         'কোড আপডেটের পর ইভেন্ট না চললে', $stats['is_events_cached'] ? 'ক্যাশ করা আছে' : 'ক্যাশ নেই'],
        ['opcache','clear-opcache','fa-microchip',  'is-slate',  'PHP OPcache',    'নতুন কোড আপলোডের পর পুরনো কোড চললে', $stats['opcache_enabled'] ? ($stats['opcache_scripts'] . ' স্ক্রিপ্ট') : 'চালু নেই'],
    ];
    $flags = [
        ['is_route_cached', 'Routes', $stats['is_route_cached'], false],
        ['view_files_count', 'Views', $stats['view_files_count'] > 0, false],
        ['is_events_cached', 'Events', $stats['is_events_cached'], false],
        ['is_config_cached', 'Config', $stats['is_config_cached'], true],
    ];
@endphp

<div class="ck" id="ckApp"
     data-stats="{{ route('admin.cache.stats-json') }}"
     data-inspect="{{ route('admin.cache.inspect-key') }}"
     data-warm="{{ route('admin.cache.warm-key') }}"
     data-delete="{{ route('admin.cache.delete-key') }}"
     data-bulk="{{ route('admin.cache.bulk-delete-keys') }}"
     data-scan="{{ route('admin.cache.scan-images') }}"
     data-quarantine="{{ route('admin.cache.clear-images') }}"
     data-restore="{{ route('admin.cache.restore-images') }}"
     data-delete-batch="{{ route('admin.cache.delete-image-batch') }}"
     data-run="{{ route('admin.cache.run-artisan') }}">

    {{-- ① Storage: what fills the hosting account, and one click to free it --}}
    <section class="ck-store" id="ckStore" data-measure="{{ route('admin.cache.storage') }}" data-clean="{{ route('admin.cache.storage-clean') }}" aria-labelledby="ckStoreTitle">
        <div class="ck-store-hero">
            <div class="ck-store-intro">
                <span class="ck-eyebrow"><i class="fa-solid fa-hard-drive"></i> Disk space</span>
                <h2 id="ckStoreTitle">Storage</h2>
                <p>হোস্টিংয়ের জায়গা ভরে গেলে পুরো সাইট বন্ধ হয়ে যায়। নিরাপদ অংশগুলো মুছলে কিছু হারায় না — সাইট দরকার হলে নিজেই আবার তৈরি করে।</p>
            </div>
            <div class="ck-store-free">
                <span>Can free now</span>
                <strong data-store-free><span class="ck-skel"></span></strong>
                <small data-store-total>মাপা হচ্ছে…</small>
            </div>
            <button type="button" class="ck-btn ck-btn--success ck-btn--xl ck-store-btn" id="ckStoreClean" disabled>
                <i class="fa-solid fa-broom"></i><span>জায়গা খালি করুন</span>
            </button>
        </div>
        <div class="ck-store-bar" data-store-bar aria-hidden="true"></div>
        <ul class="ck-store-list" data-store-list>
            @for ($i = 0; $i < 5; $i++)<li class="ck-store-row is-loading"><span class="ck-skel"></span></li>@endfor
        </ul>
        <div class="ck-store-foot">
            <span data-store-note><i class="fa-regular fa-circle-question me-1"></i>হোস্টিংয়ের মোট ব্যবহার (মেইল, ডাটাবেস সহ) cPanel → Disk Usage-এ দেখুন।</span>
            <button type="button" class="ck-btn ck-btn--ghost ck-btn--sm" id="ckStoreMeasure"><i class="fa-solid fa-ruler"></i><span>আবার মাপুন</span></button>
        </div>
    </section>

    {{-- ② Live status --}}
    <section class="ck-tiles" aria-label="Status">
        <div class="ck-tile">
            <span class="ck-tile-icon is-blue"><i class="fa-solid fa-database"></i></span>
            <span class="ck-tile-label">Data cache</span>
            <strong data-stat="data_cache_size">{{ $stats['data_cache_size'] }}</strong>
            <small><span data-stat="data_files_count">{{ $stats['data_files_count'] }}</span> ফাইল · {{ $stats['cache_driver'] }}</small>
        </div>
        <div class="ck-tile">
            <span class="ck-tile-icon is-violet"><i class="fa-solid fa-file-code"></i></span>
            <span class="ck-tile-label">Compiled views</span>
            <strong data-stat="view_cache_size">{{ $stats['view_cache_size'] }}</strong>
            <small><span data-stat="view_files_count">{{ $stats['view_files_count'] }}</span> ফাইল</small>
        </div>
        <div class="ck-tile">
            <span class="ck-tile-icon is-green"><i class="fa-solid fa-gauge-high"></i></span>
            <span class="ck-tile-label">Fast mode</span>
            <div class="ck-pills">
                @foreach ($flags as [$flag, $label, $on, $warn])
                    <span class="ck-pill {{ $warn ? 'warn' : '' }} {{ $on ? 'on' : '' }}" data-flag="{{ $flag }}" @if($warn) title="কনফিগ ক্যাশ চালু থাকলে .env সরাসরি পড়া কোড (পেমেন্ট, SMS) খালি মান পায়" @endif>{{ $label }}</span>
                @endforeach
            </div>
            <small>সবুজ = আগে থেকে তৈরি</small>
        </div>
        <div class="ck-tile">
            <span class="ck-tile-icon is-slate"><i class="fa-solid fa-microchip"></i></span>
            <span class="ck-tile-label">OPcache</span>
            @if ($stats['opcache_enabled'])
                <strong><span data-stat="opcache_hit_rate">{{ $stats['opcache_hit_rate'] ?? '—' }}</span>%</strong>
                <div class="ck-bar" title="মেমোরি ব্যবহার"><i style="width: {{ $stats['opcache_memory_percent'] }}%" data-bar="opcache_memory_percent"></i></div>
                <small>হিট রেট · মেমোরি {{ $stats['opcache_memory_used'] }}</small>
            @else
                <strong class="ck-muted">Off</strong>
                <small>এই সার্ভারে OPcache চালু নেই</small>
            @endif
        </div>
        <div class="ck-tile">
            <span class="ck-tile-icon is-amber"><i class="fa-solid fa-box-archive"></i></span>
            <span class="ck-tile-label">Image quarantine</span>
            <strong data-stat="quarantine_size">{{ $stats['quarantine_size'] }}</strong>
            <small>PHP {{ $stats['php_version'] }} · Laravel {{ $stats['laravel_version'] }}</small>
        </div>
    </section>
    <div class="ck-updated"><span class="ck-live-dot is-static" aria-hidden="true"></span> Updated <span id="ckUpdated">{{ now()->format('h:i:s A') }}</span></div>

    {{-- ③ Main actions --}}
    <section class="ck-main" aria-label="Main actions">
        <div class="ck-main-item">
            <span class="ck-main-icon is-blue"><i class="fa-solid fa-rocket"></i></span>
            <div class="ck-main-text">
                <h3>Speed up</h3>
                <p>রুট, ভিউ ও ইভেন্ট আগে থেকে তৈরি রাখে। কোড আপডেটের পর একবার চাপুন। কনফিগ ক্যাশ করা হয় না, তাই পেমেন্ট ও SMS নিরাপদ।</p>
            </div>
            <button type="button" class="ck-btn ck-btn--primary" data-ck-action="{{ route('admin.cache.optimize') }}" data-busy="দ্রুত করা হচ্ছে…"><i class="fa-solid fa-bolt"></i><span>দ্রুত করুন</span></button>
        </div>
        <div class="ck-main-item">
            <span class="ck-main-icon is-green"><i class="fa-solid fa-fire"></i></span>
            <div class="ck-main-text">
                <h3>Warm up</h3>
                <p>হোমপেজ, বইয়ের পাতা ও সাইটম্যাপ একবার খুলে ক্যাশ তৈরি করে, যাতে প্রথম ভিজিটরও দ্রুত পেজ পান।</p>
            </div>
            <button type="button" class="ck-btn ck-btn--success" data-ck-action="{{ route('admin.cache.warmup') }}" data-busy="পেজ লোড হচ্ছে…"><i class="fa-solid fa-temperature-arrow-up"></i><span>ওয়ার্ম-আপ</span></button>
        </div>
        <div class="ck-main-item">
            <span class="ck-main-icon is-red"><i class="fa-solid fa-trash-can-arrow-up"></i></span>
            <div class="ck-main-text">
                <h3>Clear everything</h3>
                <p>কোনো পরিবর্তন সাইটে না দেখালে এটি শেষ উপায়। পরে “দ্রুত করুন” ও “ওয়ার্ম-আপ” চাপলে আবার দ্রুত হবে।</p>
            </div>
            <button type="button" class="ck-btn ck-btn--danger-soft" data-ck-action="{{ route('admin.cache.clear-all') }}" data-busy="পরিষ্কার হচ্ছে…" data-ck-confirm="সব ক্যাশ পরিষ্কার করবেন? কিছুক্ষণ সাইট একটু ধীরে চলবে।"><i class="fa-solid fa-eraser"></i><span>সব পরিষ্কার</span></button>
        </div>
    </section>

    {{-- ④ One cache at a time --}}
    <section class="ck-card">
        <header class="ck-card-head"><h2><i class="fa-solid fa-layer-group"></i> Clear one</h2><span>যেটি দরকার শুধু সেটি পরিষ্কার করুন</span></header>
        <div class="ck-grid">
            @foreach ($clearCards as [$id, $route, $icon, $tone, $title, $when, $state])
                <div class="ck-item">
                    <span class="ck-item-icon {{ $tone }}"><i class="fa-solid {{ $icon }}"></i></span>
                    <div class="ck-item-body">
                        <h3>{{ $title }}</h3>
                        <p>{{ $when }}</p>
                        <span class="ck-state" data-state="{{ $id }}">{{ $state }}</span>
                    </div>
                    <button type="button" class="ck-btn ck-btn--icon" data-ck-action="{{ route('admin.cache.' . $route) }}" data-busy="" title="{{ $title }} পরিষ্কার করুন" aria-label="{{ $title }} পরিষ্কার করুন"><i class="fa-solid fa-broom"></i></button>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ⑤ Tools: keys · images · console --}}
    <section class="ck-card ck-tools">
        <div class="ck-tabs" role="tablist" aria-label="Tools">
            <button type="button" class="ck-tab is-active" role="tab" aria-selected="true" aria-controls="ckPaneKeys" id="ckTabKeys" data-ck-tab="ckPaneKeys"><i class="fa-solid fa-key"></i> Cache keys <span class="ck-count">{{ count($cachedKeys) }}</span></button>
            <button type="button" class="ck-tab" role="tab" aria-selected="false" aria-controls="ckPaneImages" id="ckTabImages" data-ck-tab="ckPaneImages"><i class="fa-regular fa-images"></i> Unused images @if(count($batches))<span class="ck-count">{{ count($batches) }}</span>@endif</button>
            @if (!empty($artisanCommands))
                <button type="button" class="ck-tab" role="tab" aria-selected="false" aria-controls="ckPaneConsole" id="ckTabConsole" data-ck-tab="ckPaneConsole"><i class="fa-solid fa-terminal"></i> Console</button>
            @endif
        </div>

        {{-- Keys --}}
        <div class="ck-pane is-active" id="ckPaneKeys" role="tabpanel" aria-labelledby="ckTabKeys">
            <div class="ck-pane-bar">
                <div class="ck-search"><i class="fa-solid fa-magnifying-glass"></i><input type="search" id="ckKeyFind" placeholder="কি খুঁজুন…" aria-label="Find a key" autocomplete="off"></div>
                <button type="button" class="ck-btn ck-btn--danger-soft ck-btn--sm" id="ckBulkDelete" disabled><i class="fa-regular fa-trash-can"></i><span>নির্বাচিত মুছুন</span></button>
            </div>
            <div class="table-responsive">
                <table class="table ck-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="width: 34px;"><input type="checkbox" class="form-check-input" id="ckAllKeys" aria-label="সব"></th>
                            <th>Key</th>
                            <th>State</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($cachedKeys as $k)
                            <tr data-key="{{ $k['key'] }}" data-find="{{ \Illuminate\Support\Str::lower($k['label'] . ' ' . $k['key'] . ' ' . $k['type'] . ' ' . $k['description']) }}">
                                <td><input type="checkbox" class="form-check-input ck-key-pick" value="{{ $k['key'] }}" aria-label="নির্বাচন"></td>
                                <td>
                                    <div class="ck-key-name">{{ $k['label'] }} <span class="ck-type">{{ $k['type'] }}</span></div>
                                    <div class="ck-sub">{{ $k['description'] }}</div>
                                    <code class="ck-code">{{ $k['key'] }}</code>
                                </td>
                                <td><span class="ck-dot {{ $k['is_cached'] ? 'on' : '' }}" data-key-state>{{ $k['is_cached'] ? 'মেমোরিতে আছে' : 'খালি' }}</span></td>
                                <td class="text-end text-nowrap">
                                    <button type="button" class="ck-btn ck-btn--icon ck-btn--sm" data-ck-inspect title="দেখুন" aria-label="দেখুন"><i class="fa-regular fa-eye"></i></button>
                                    @if ($k['can_warm'])<button type="button" class="ck-btn ck-btn--icon ck-btn--sm" data-ck-warm title="নতুন করে তৈরি করুন" aria-label="নতুন করে তৈরি করুন"><i class="fa-solid fa-rotate"></i></button>@endif
                                    <button type="button" class="ck-btn ck-btn--icon ck-btn--sm is-danger" data-ck-delete title="মুছুন" aria-label="মুছুন"><i class="fa-regular fa-trash-can"></i></button>
                                </td>
                            </tr>
                        @endforeach
                        <tr class="ck-none" hidden><td colspan="4">কিছু পাওয়া যায়নি</td></tr>
                    </tbody>
                </table>
            </div>
            <p class="ck-note"><i class="fa-solid fa-shield-halved me-1"></i>মুছে ফেললে ক্ষতি নেই — সাইট পরের বার দরকার হলে নিজেই নতুন করে তৈরি করে। গোপন তথ্য (পাসওয়ার্ড, API কি) দেখার সময় লুকানো থাকে।</p>
        </div>

        {{-- Unused images --}}
        <div class="ck-pane" id="ckPaneImages" role="tabpanel" aria-labelledby="ckTabImages" hidden>
            <div class="ck-pad">
                <p class="ck-sub mb-3">
                    যেসব আপলোড করা ছবি ডাটাবেসের কোথাও (বই, লেখক, ব্লগের লেখা, সেটিংস…) ব্যবহার হয়নি সেগুলো খুঁজে বের করে।
                    গত {{ $minAgeDays }} দিনের ছবি ধরা হয় না। ছবি মুছে না ফেলে <b>কোয়ারেন্টাইনে</b> সরানো হয় — {{ $retentionDays }} দিন পর্যন্ত এক ক্লিকে ফেরত আনা যায়।
                </p>
                <div class="d-flex flex-wrap gap-2">
                    <button type="button" class="ck-btn ck-btn--primary ck-btn--sm" id="ckScan"><i class="fa-solid fa-magnifying-glass"></i><span>স্ক্যান করুন</span></button>
                    <button type="button" class="ck-btn ck-btn--warning ck-btn--sm" id="ckQuarantine" hidden><i class="fa-solid fa-box-archive"></i><span>কোয়ারেন্টাইনে সরান</span></button>
                </div>
                <div id="ckScanResult" class="ck-scan" hidden></div>
                <h3 class="ck-h3 mt-4">Quarantine</h3>
                <div id="ckBatches">
                    @include('admin.partials.cache-batches', ['batches' => $batches])
                </div>
            </div>
        </div>

        {{-- Console --}}
        @if (!empty($artisanCommands))
            <div class="ck-pane" id="ckPaneConsole" role="tabpanel" aria-labelledby="ckTabConsole" hidden>
                <div class="ck-pad">
                    <div class="ck-console-btns">
                        @foreach ($artisanCommands as $cmd => $meta)
                            <button type="button" class="ck-btn ck-btn--soft ck-btn--sm" data-ck-run="{{ $cmd }}" title="{{ $meta['desc'] ?? '' }} (php artisan {{ $cmd }})">
                                <i class="fa-solid {{ $meta['icon'] ?? ($consoleGroups[$meta['group'] ?? '']['icon'] ?? 'fa-terminal') }}"></i><span>{{ $meta['label'] ?? $cmd }}</span>
                            </button>
                        @endforeach
                    </div>
                    <pre class="ck-console" id="ckConsole" aria-live="polite">$ কমান্ড বেছে নিন…</pre>
                </div>
            </div>
        @endif
    </section>
</div>

{{-- Key inspector --}}
<div class="modal fade ck-modal" id="ckKeyModal" tabindex="-1" aria-labelledby="ckKeyTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="ckKeyTitle">Key</h5>
                    <div class="ck-sub" id="ckKeyMeta"></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0"><pre class="ck-console m-0" id="ckKeyBody" style="border-radius: 0; max-height: 60vh;"></pre></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/admin-cache.js') }}?v={{ @filemtime(public_path('js/admin-cache.js')) ?: 1 }}" defer></script>
    <script src="{{ asset('js/admin-storage.js') }}?v={{ @filemtime(public_path('js/admin-storage.js')) ?: 1 }}" defer></script>
@endpush
