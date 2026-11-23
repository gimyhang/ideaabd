@extends('layouts.admin')

@section('title', 'ক্যাশ ও পারফরম্যান্স')
@section('heading', 'ক্যাশ ও পারফরম্যান্স')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin-cache.css') }}?v={{ @filemtime(public_path('css/admin-cache.css')) ?: 1 }}">
@endpush

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Cache</li>
@endsection

@section('actions')
    <button type="button" class="btn btn-light btn-sm" id="ckRefresh"><i class="fa-solid fa-rotate me-1"></i>রিফ্রেশ</button>
@endsection

@section('content')
@php
    $bnBool = fn ($b) => $b ? 'চালু' : 'বন্ধ';
    $clearCards = [
        ['views',  'clear-views',  'fa-file-code',        'কম্পাইল করা ভিউ', 'ডিজাইন বা টেমপ্লেট বদলের পর পুরনো চেহারা দেখালে।', $stats['view_files_count'] . ' ফাইল · ' . $stats['view_cache_size']],
        ['app',    'clear-app',    'fa-database',         'ডেটা ক্যাশ', 'সেটিংস, মেনু, হোমপেজ বা দাম বদলালেও পুরনোটা দেখালে।', $stats['data_files_count'] . ' ফাইল · ' . $stats['data_cache_size']],
        ['config', 'clear-config', 'fa-sliders',          'কনফিগ ক্যাশ', '.env ফাইল বদলের পর।', $stats['is_config_cached'] ? 'ক্যাশ করা আছে' : 'ক্যাশ নেই'],
        ['routes', 'clear-routes', 'fa-route',            'রুট ক্যাশ', 'নতুন পেজ/লিংক কাজ না করলে।', $stats['is_route_cached'] ? 'ক্যাশ করা আছে' : 'ক্যাশ নেই'],
        ['events', 'clear-events', 'fa-bolt',             'ইভেন্ট ক্যাশ', 'কোড আপডেটের পর ইভেন্ট ঠিকমতো না চললে।', $stats['is_events_cached'] ? 'ক্যাশ করা আছে' : 'ক্যাশ নেই'],
        ['opcache','clear-opcache','fa-microchip',        'PHP OPcache', 'সার্ভারে নতুন কোড আপলোডের পর পুরনো কোড চললে।', $stats['opcache_enabled'] ? ($stats['opcache_scripts'] . ' স্ক্রিপ্ট') : 'চালু নেই'],
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

    {{-- Status --}}
    <section class="ck-tiles" aria-label="অবস্থা">
        <div class="ck-tile">
            <span class="ck-tile-label"><i class="fa-solid fa-database"></i> ডেটা ক্যাশ</span>
            <strong data-stat="data_cache_size">{{ $stats['data_cache_size'] }}</strong>
            <small><span data-stat="data_files_count">{{ $stats['data_files_count'] }}</span> ফাইল · ড্রাইভার {{ $stats['cache_driver'] }}</small>
        </div>
        <div class="ck-tile">
            <span class="ck-tile-label"><i class="fa-solid fa-file-code"></i> কম্পাইল করা ভিউ</span>
            <strong data-stat="view_cache_size">{{ $stats['view_cache_size'] }}</strong>
            <small><span data-stat="view_files_count">{{ $stats['view_files_count'] }}</span> ফাইল</small>
        </div>
        <div class="ck-tile">
            <span class="ck-tile-label"><i class="fa-solid fa-gauge-high"></i> দ্রুত মোড</span>
            <div class="ck-pills">
                <span class="ck-pill {{ $stats['is_route_cached'] ? 'on' : '' }}" data-flag="is_route_cached">রুট</span>
                <span class="ck-pill {{ $stats['view_files_count'] > 0 ? 'on' : '' }}" data-flag="view_files_count">ভিউ</span>
                <span class="ck-pill {{ $stats['is_events_cached'] ? 'on' : '' }}" data-flag="is_events_cached">ইভেন্ট</span>
                <span class="ck-pill warn {{ $stats['is_config_cached'] ? 'on' : '' }}" data-flag="is_config_cached" title="কনফিগ ক্যাশ চালু থাকলে .env সরাসরি পড়া কোড (পেমেন্ট, SMS) খালি মান পায়">কনফিগ</span>
            </div>
            <small>সবুজ = আগে থেকে তৈরি</small>
        </div>
        <div class="ck-tile">
            <span class="ck-tile-label"><i class="fa-solid fa-microchip"></i> OPcache</span>
            @if ($stats['opcache_enabled'])
                <strong><span data-stat="opcache_hit_rate">{{ $stats['opcache_hit_rate'] ?? '—' }}</span>%</strong>
                <div class="ck-bar" title="মেমোরি ব্যবহার"><i style="width: {{ $stats['opcache_memory_percent'] }}%" data-bar="opcache_memory_percent"></i></div>
                <small>হিট রেট · মেমোরি {{ $stats['opcache_memory_used'] }} ব্যবহৃত</small>
            @else
                <strong class="text-muted">বন্ধ</strong>
                <small>এই সার্ভারে OPcache চালু নেই</small>
            @endif
        </div>
        <div class="ck-tile">
            <span class="ck-tile-label"><i class="fa-solid fa-box-archive"></i> ছবির কোয়ারেন্টাইন</span>
            <strong data-stat="quarantine_size">{{ $stats['quarantine_size'] }}</strong>
            <small>PHP {{ $stats['php_version'] }} · Laravel {{ $stats['laravel_version'] }}</small>
        </div>
    </section>
    <div class="ck-updated">সর্বশেষ আপডেট: <span id="ckUpdated">{{ now()->format('h:i:s A') }}</span></div>

    {{-- Main actions --}}
    <section class="ck-main">
        <div class="ck-main-item">
            <div class="ck-main-text">
                <h3>সাইট দ্রুত করুন</h3>
                <p>রুট, ভিউ ও ইভেন্ট আগে থেকে তৈরি রাখে। কোড আপডেটের পর একবার চাপুন। কনফিগ ক্যাশ করা হয় না, তাই পেমেন্ট ও SMS নিরাপদ।</p>
            </div>
            <button type="button" class="btn btn-primary" data-ck-action="{{ route('admin.cache.optimize') }}" data-busy="দ্রুত করা হচ্ছে…">দ্রুত করুন</button>
        </div>
        <div class="ck-main-item">
            <div class="ck-main-text">
                <h3>ওয়ার্ম-আপ</h3>
                <p>হোমপেজ, বইয়ের পাতা ও সাইটম্যাপ একবার খুলে ক্যাশ তৈরি করে, যাতে প্রথম ভিজিটরও দ্রুত পেজ পান।</p>
            </div>
            <button type="button" class="btn btn-success" data-ck-action="{{ route('admin.cache.warmup') }}" data-busy="পেজ লোড হচ্ছে…">ওয়ার্ম-আপ</button>
        </div>
        <div class="ck-main-item">
            <div class="ck-main-text">
                <h3>সব ক্যাশ পরিষ্কার</h3>
                <p>কোনো পরিবর্তন সাইটে না দেখালে এটি শেষ উপায়। পরে “দ্রুত করুন” ও “ওয়ার্ম-আপ” চাপলে আবার দ্রুত হবে।</p>
            </div>
            <button type="button" class="btn btn-outline-danger" data-ck-action="{{ route('admin.cache.clear-all') }}" data-busy="পরিষ্কার হচ্ছে…" data-confirm="সব ক্যাশ পরিষ্কার করবেন? কিছুক্ষণ সাইট একটু ধীরে চলবে।">সব পরিষ্কার</button>
        </div>
    </section>

    {{-- Individual clears --}}
    <section class="ck-card">
        <header class="ck-card-head"><h2>আলাদা আলাদা পরিষ্কার</h2><span>যেটি দরকার শুধু সেটি পরিষ্কার করুন</span></header>
        <div class="ck-grid">
            @foreach ($clearCards as [$id, $route, $icon, $title, $when, $state])
                <div class="ck-item">
                    <div class="ck-item-head"><i class="fa-solid {{ $icon }}"></i><h3>{{ $title }}</h3></div>
                    <p>{{ $when }}</p>
                    <div class="ck-item-foot">
                        <span class="ck-state" data-state="{{ $id }}">{{ $state }}</span>
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-ck-action="{{ route('admin.cache.' . $route) }}" data-busy="…">পরিষ্কার</button>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <div class="ck-cols">
        {{-- Memory keys --}}
        <section class="ck-card">
            <header class="ck-card-head">
                <h2>মেমোরির কি (Cache keys)</h2>
                <button type="button" class="btn btn-sm btn-outline-danger" id="ckBulkDelete" disabled>নির্বাচিত মুছুন</button>
            </header>
            <div class="table-responsive">
                <table class="table ck-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="width: 30px;"><input type="checkbox" class="form-check-input" id="ckAllKeys" aria-label="সব"></th>
                            <th>কি</th>
                            <th>অবস্থা</th>
                            <th class="text-end">কাজ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($cachedKeys as $k)
                            <tr data-key="{{ $k['key'] }}">
                                <td><input type="checkbox" class="form-check-input ck-key-pick" value="{{ $k['key'] }}" aria-label="নির্বাচন"></td>
                                <td>
                                    <div class="fw-semibold">{{ $k['label'] }} <span class="ck-type">{{ $k['type'] }}</span></div>
                                    <div class="ck-sub">{{ $k['description'] }}</div>
                                    <code class="ck-code">{{ $k['key'] }}</code>
                                </td>
                                <td><span class="ck-dot {{ $k['is_cached'] ? 'on' : '' }}" data-key-state>{{ $k['is_cached'] ? 'মেমোরিতে আছে' : 'খালি' }}</span></td>
                                <td class="text-end text-nowrap">
                                    <button type="button" class="btn btn-sm btn-light" data-ck-inspect title="দেখুন"><i class="fa-regular fa-eye"></i></button>
                                    @if ($k['can_warm'])<button type="button" class="btn btn-sm btn-light" data-ck-warm title="নতুন করে তৈরি করুন"><i class="fa-solid fa-rotate"></i></button>@endif
                                    <button type="button" class="btn btn-sm btn-light text-danger" data-ck-delete title="মুছুন"><i class="fa-regular fa-trash-can"></i></button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="ck-note">মুছে ফেললে ক্ষতি নেই — সাইট পরের বার দরকার হলে নিজেই নতুন করে তৈরি করে। গোপন তথ্য (পাসওয়ার্ড, API কি) দেখার সময় লুকানো থাকে।</p>
        </section>

        {{-- Unused images --}}
        <section class="ck-card">
            <header class="ck-card-head"><h2>অব্যবহৃত ছবি পরিষ্কার</h2></header>
            <div class="ck-pad">
                <p class="ck-sub mb-2">
                    যেসব আপলোড করা ছবি ডাটাবেসের কোথাও (বই, লেখক, ব্লগের লেখা, সেটিংস…) ব্যবহার হয়নি সেগুলো খুঁজে বের করে।
                    গত {{ $minAgeDays }} দিনের ছবি ধরা হয় না। ছবি মুছে না ফেলে <b>কোয়ারেন্টাইনে</b> সরানো হয় — {{ $retentionDays }} দিন পর্যন্ত এক ক্লিকে ফেরত আনা যায়।
                </p>
                <div class="d-flex flex-wrap gap-2">
                    <button type="button" class="btn btn-sm btn-primary" id="ckScan"><i class="fa-solid fa-magnifying-glass me-1"></i>স্ক্যান করুন</button>
                    <button type="button" class="btn btn-sm btn-warning" id="ckQuarantine" hidden><i class="fa-solid fa-box-archive me-1"></i>কোয়ারেন্টাইনে সরান</button>
                </div>
                <div id="ckScanResult" class="ck-scan" hidden></div>
            </div>
            <div class="ck-pad pt-0">
                <h3 class="ck-h3">কোয়ারেন্টাইন</h3>
                <div id="ckBatches">
                    @include('admin.partials.cache-batches', ['batches' => $batches])
                </div>
            </div>
        </section>
    </div>

    {{-- Console --}}
    @if (!empty($artisanCommands))
        <section class="ck-card">
            <header class="ck-card-head"><h2>নিরাপদ কনসোল</h2><span>শুধু অনুমোদিত কমান্ড</span></header>
            <div class="ck-pad">
                <div class="ck-console-btns">
                    @foreach ($artisanCommands as $cmd => $meta)
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-ck-run="{{ $cmd }}" title="{{ $meta['desc'] ?? '' }} (php artisan {{ $cmd }})">
                            <i class="fa-solid {{ $meta['icon'] ?? ($consoleGroups[$meta['group'] ?? '']['icon'] ?? 'fa-terminal') }} me-1"></i>{{ $meta['label'] ?? $cmd }}
                        </button>
                    @endforeach
                </div>
                <pre class="ck-console" id="ckConsole" aria-live="polite">$ কমান্ড বেছে নিন…</pre>
            </div>
        </section>
    @endif
</div>

{{-- Key inspector --}}
<div class="modal fade" id="ckKeyModal" tabindex="-1" aria-labelledby="ckKeyTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="ckKeyTitle">কি</h5>
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
@endpush
