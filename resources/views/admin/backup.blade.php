@extends('layouts.admin')

@section('title', 'Backup')
@section('heading', 'Backup')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">Dashboard</a></li>
    <li class="breadcrumb-item active">Backup</li>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin-backup.css') }}?v={{ @filemtime(public_path('css/admin-backup.css')) ?: 1 }}">
@endpush

@php
    $freqLabel  = ['daily' => 'প্রতিদিন', 'weekly' => 'প্রতি সপ্তাহে', 'monthly' => 'প্রতি মাসে'];
    $tabs       = ['all' => 'All', 'archive' => 'Full', 'database' => 'Database', 'safety' => 'Safety', 'masked' => 'Masked'];
    $catInfo    = [
        'archive'  => ['Full', 'ডাটাবেজ + মিডিয়া', 'fa-box-archive', 'is-blue'],
        'database' => ['Database', 'শুধু ডাটাবেজ', 'fa-database', 'is-green'],
        'safety'   => ['Safety', 'রিস্টোরের আগের কপি', 'fa-shield-halved', 'is-amber'],
        'masked'   => ['Masked', 'গোপন তথ্য বদলানো', 'fa-user-shield', 'is-violet'],
    ];
    $tone       = ['good' => 'is-good', 'fair' => 'is-fair', 'poor' => 'is-poor'][$health['level']];
    $toneLabel  = ['good' => 'Healthy', 'fair' => 'Fair', 'poor' => 'At risk'][$health['level']];
    $spaceTone  = ['safe' => 'is-good', 'warning' => 'is-fair', 'critical' => 'is-poor'][$space['level']];
    $latest     = $backups[0] ?? null;
@endphp

@section('content')
<script>
    window.BACKUP = {
        timeline: @json($timeline),
        tables: @json(array_column($db['tables'], 'name')),
        uploadMaxMb: {{ $uploadMaxMb }},
        mailMaxMb: {{ $mailMaxMb }},
        email: @json($settings['backup_email'] ?: config('mail.from.address')),
        restoreWord: @json(\App\Http\Controllers\Admin\AdminBackupController::RESTORE_WORD),
        routes: {
            create: @json(route('admin.backup.create')),
            upload: @json(route('admin.backup.upload')),
            bulkDelete: @json(route('admin.backup.bulk-delete')),
            masked: @json(route('admin.backup.export-anonymized')),
            notify: @json(route('admin.backup.test-notification')),
            integrity: @json(route('admin.backup.integrity')),
            optimize: @json(route('admin.backup.optimize')),
            cleanup: @json(route('admin.backup.cleanup-storage')),
            base: @json(url('admin/backup')),
        },
    };
</script>

<div class="bk">
    @if(session('success'))
        <div class="bk-alert is-good" role="alert"><i class="fa-solid fa-circle-check"></i><span>{{ session('success') }}</span><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="বন্ধ"></button></div>
    @endif
    @if(session('error'))
        <div class="bk-alert is-poor" role="alert"><i class="fa-solid fa-circle-exclamation"></i><span>{{ session('error') }}</span><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="বন্ধ"></button></div>
    @endif
    @if($errors->any())
        <div class="bk-alert is-poor" role="alert"><i class="fa-solid fa-circle-exclamation"></i><span>{{ $errors->first() }}</span></div>
    @endif

    {{-- Overview --}}
    <section class="bk-hero">
        <div class="bk-ring {{ $tone }}" style="--p: {{ $health['score'] }}" title="Backup health">
            <svg viewBox="0 0 120 120" aria-hidden="true">
                <circle class="bk-ring-track" cx="60" cy="60" r="52"/>
                <circle class="bk-ring-value" cx="60" cy="60" r="52" pathLength="100" stroke-dasharray="{{ $health['score'] }} 100"/>
            </svg>
            <div class="bk-ring-text"><strong>{{ $health['score'] }}</strong><small>{{ $toneLabel }}</small></div>
        </div>
        <div class="bk-hero-main">
            <h2>Backups <span class="bk-pill {{ $tone }}"><i class="fa-solid fa-heart-pulse"></i> {{ $toneLabel }}</span></h2>
            <p>
                @if($latest)
                    শেষ ব্যাকআপ <b>{{ $latest['created_at']->locale('bn')->diffForHumans() }}</b> · {{ $latest['size'] }} · মোট <b>{{ count($backups) }}</b>টি ফাইল
                @else
                    এখনো কোনো ব্যাকআপ নেই — পাশের <b>Database</b> বাটনে প্রথমটি তৈরি করুন।
                @endif
            </p>
            @if($health['issues'])
                <ul class="bk-issues">
                    @foreach($health['issues'] as $issue)<li><i class="fa-solid fa-triangle-exclamation"></i>{{ $issue }}</li>@endforeach
                </ul>
            @else
                <ul class="bk-issues is-ok"><li><i class="fa-solid fa-circle-check"></i>সব ঠিক আছে</li></ul>
            @endif
        </div>
        <div class="bk-hero-acts">
            <button type="button" class="bk-cta is-primary" data-backup="db_only">
                <i class="fa-solid fa-database"></i><span><b>Database</b><small>.sql.gz · দ্রুত</small></span>
            </button>
            <button type="button" class="bk-cta" data-backup="data_media">
                <i class="fa-solid fa-box-archive"></i><span><b>Full</b><small>ডাটাবেজ + মিডিয়া</small></span>
            </button>
            <button type="button" class="bk-cta" id="btnUpload">
                <i class="fa-solid fa-cloud-arrow-up"></i><span><b>Upload</b><small>সর্বোচ্চ {{ $uploadMaxMb }} MB</small></span>
            </button>
        </div>
    </section>

    {{-- Tools --}}
    <nav class="bk-tools" aria-label="Tools">
        <button type="button" class="bk-tool" data-bs-toggle="modal" data-bs-target="#settingsModal"><i class="fa-solid fa-gear is-blue"></i><span><b>Settings</b><small>সময়সূচি, টেলিগ্রাম</small></span></button>
        <button type="button" class="bk-tool" data-act="integrity"><i class="fa-solid fa-stethoscope is-green"></i><span><b>Check</b><small>টেবিল পরীক্ষা</small></span></button>
        <button type="button" class="bk-tool" data-act="optimize"><i class="fa-solid fa-wand-magic-sparkles is-violet"></i><span><b>Optimize</b><small>জায়গা খালি করুন</small></span></button>
        <button type="button" class="bk-tool" data-act="masked"><i class="fa-solid fa-user-shield is-amber"></i><span><b>Masked</b><small>ডেভেলপারদের কপি</small></span></button>
        <button type="button" class="bk-tool is-danger" data-act="cleanup" data-keep="{{ $settings['retention_days'] }}"><i class="fa-solid fa-broom"></i><span><b>Cleanup</b><small>{{ $settings['retention_days'] }}টির বেশি পুরনো মুছুন</small></span></button>
    </nav>

    @if($settings['auto_backup_enabled'] && $cronDb)
        <div class="bk-alert is-fair"><i class="fa-solid fa-plug-circle-exclamation"></i>
            <span>সার্ভারের ক্রন অন্য ডাটাবেজ (<code>{{ $cronDb }}</code>) ব্যবহার করছে, তাই সেখান থেকে অটো-ব্যাকআপ হয় না। সময় হলে Dashboard বা এই পেজ খোলার পর ব্যাকআপ নিজে থেকেই চলে। স্থায়ী সমাধানে সার্ভারের <code>.env</code>-এ CLI-এর ডাটাবেজ ঠিক করুন।</span></div>
    @elseif($settings['auto_backup_enabled'] && !$schedulerOk)
        <div class="bk-alert is-fair"><i class="fa-solid fa-clock"></i>
            <span>সার্ভারের ক্রন চলছে না। সময় হলে Dashboard বা এই পেজ খোলার পর অটো-ব্যাকআপ চলে; নিয়মিত চালাতে cPanel-এ ক্রন দিন: <code>* * * * * php artisan schedule:run</code></span></div>
    @endif

    {{-- Status --}}
    <div class="bk-tiles">
        <div class="bk-tile">
            <div class="bk-tile-top"><i class="fa-solid fa-clock-rotate-left is-blue"></i><span>Last</span></div>
            <strong>{{ $latest ? $latest['created_at']->format('d M, h:i A') : '—' }}</strong>
            <small>{{ $latest ? $catInfo[$latest['category']][0] . ' · ' . $latest['size'] : 'কোনো ফাইল নেই' }}</small>
            @if(!empty($last['at']))
                <small class="{{ ($last['status'] ?? '') === 'ok' ? '' : 'text-danger' }}">Auto: {{ \Illuminate\Support\Carbon::parse($last['at'])->format('d M, h:i A') }} · {{ ($last['status'] ?? '') === 'ok' ? 'সফল' : 'ব্যর্থ' }}</small>
            @endif
            @if(($last['status'] ?? '') === 'failed' && !empty($last['message']))
                <div class="bk-fail" title="{{ $last['message'] }}"><i class="fa-solid fa-circle-info"></i><span>কারণ: {{ \Illuminate\Support\Str::limit($last['message'], 180) }}</span></div>
            @endif
        </div>

        <div class="bk-tile">
            <div class="bk-tile-top"><i class="fa-solid fa-hard-drive is-violet"></i><span>Storage</span><em class="bk-tile-pct {{ $spaceTone }}">{{ round($space['pct']) }}%</em></div>
            <strong>{{ $space['used'] }} <em>/ {{ $space['total'] }}</em></strong>
            <div class="bk-meter {{ $spaceTone }}"><i style="width: {{ max(1, min(100, $space['pct'])) }}%"></i></div>
            <small>বাকি {{ $space['remaining'] }}@if($space['is_quota']) · প্ল্যান কোটা@endif</small>
        </div>

        <div class="bk-tile">
            <div class="bk-tile-top"><i class="fa-solid fa-calendar-check is-green"></i><span>Auto</span>
                <button type="button" class="bk-link" data-bs-toggle="modal" data-bs-target="#settingsModal"><i class="fa-solid fa-pen"></i> Edit</button></div>
            @if($settings['auto_backup_enabled'])
                <strong class="is-on"><span class="bk-dot"></span>{{ $freqLabel[$settings['backup_frequency']] }} · {{ $settings['backup_time'] }}</strong>
                <small>পরবর্তী: {{ $next?->format('d M, h:i A') }} · {{ $settings['backup_mode'] === 'db_only' ? 'Database' : 'Full' }}</small>
            @else
                <strong class="is-off">বন্ধ</strong>
                <small>Settings থেকে চালু করুন</small>
            @endif
            <div class="bk-chips">
                @foreach(['telegram' => ['Telegram', 'fa-paper-plane'], 'email' => ['Email', 'fa-envelope'], 'offsite' => ['Offsite', 'fa-cloud']] as $key => [$label, $icon])
                    <button type="button" class="bk-chip {{ $channels[$key] ? 'is-on' : '' }}" data-bs-toggle="modal" data-bs-target="#settingsModal" title="{{ $channels[$key] ? 'চালু' : 'বন্ধ — সেট করুন' }}"><i class="fa-solid {{ $icon }}"></i>{{ $label }}</button>
                @endforeach
            </div>
        </div>

        <div class="bk-tile">
            <div class="bk-tile-top"><i class="fa-solid fa-server is-amber"></i><span>Database</span></div>
            <strong>{{ $db['size'] }}</strong>
            <small>{{ strtoupper($db['driver']) }} · {{ count($db['tables']) }} tables · {{ number_format($db['rows']) }} rows</small>
            <small class="bk-mono" title="{{ $db['name'] }}">{{ $db['name'] }}</small>
        </div>
    </div>

    {{-- Upload --}}
    <div class="bk-drop" id="dropZone" role="button" tabindex="0" aria-label="ব্যাকআপ ফাইল আপলোড">
        <input type="file" id="backupFileInput" class="d-none" accept=".zip,.sql,.gz,.sqlite">
        <div id="dropIdle" class="bk-drop-idle">
            <span class="bk-drop-ic"><i class="fa-solid fa-cloud-arrow-up"></i></span>
            <span><b>Upload</b> — ফাইল এখানে টেনে ছাড়ুন বা ক্লিক করুন <em>.zip .sql .gz .sqlite · সর্বোচ্চ {{ $uploadMaxMb }} MB · আপলোডের পর নিজে থেকে যাচাই হয়</em></span>
        </div>
        <div id="dropBusy" class="d-none bk-drop-busy">
            <div class="bk-drop-row"><span id="uploadName"></span><span><span id="uploadPct">0%</span> <button type="button" class="bk-link ms-2" id="uploadCancel">বাতিল</button></span></div>
            <div class="bk-meter"><i id="uploadBar" style="width:0%"></i></div>
        </div>
    </div>

    {{-- Files --}}
    <section class="bk-card">
        <div class="bk-card-head">
            <div class="bk-seg" id="bkTabs" role="tablist">
                @foreach($tabs as $key => $label)
                    <button type="button" class="nav-link {{ $loop->first ? 'active' : '' }}" data-cat="{{ $key }}" role="tab" aria-selected="{{ $loop->first ? 'true' : 'false' }}">{{ $label }} <span class="bk-count">{{ $counts[$key] }}</span></button>
                @endforeach
            </div>
            <label class="bk-search">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="search" id="backupSearch" placeholder="Search file…" aria-label="ফাইল খুঁজুন">
                <kbd class="bk-kbd">/</kbd>
                <span id="shownCount">{{ count($backups) }}টি</span>
            </label>
        </div>

        <div class="table-responsive">
            <table class="table bk-table align-middle mb-0">
                <thead>
                    <tr>
                        <th class="bk-col-check"><input type="checkbox" class="form-check-input" id="selectAll" aria-label="সব বাছাই"></th>
                        <th>File</th>
                        <th><button type="button" class="bk-sort" data-sort="size">Size <i class="fa-solid fa-sort"></i></button></th>
                        <th><button type="button" class="bk-sort is-desc" data-sort="time">Created <i class="fa-solid fa-sort-down"></i></button></th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody id="backupRows">
                    @forelse($backups as $b)
                        @php
                            $f = $b['filename']; [$cLabel, $cSub, $cIcon, $cTone] = $catInfo[$b['category']];
                            $ver = $verified[$f] ?? null;
                        @endphp
                        <tr data-name="{{ strtolower($f) }}" data-cat="{{ $b['category'] }}" data-file="{{ $f }}" data-size="{{ $b['size_bytes'] }}" data-time="{{ $b['created_at']->timestamp }}">
                            <td class="bk-col-check"><input type="checkbox" class="form-check-input row-check" value="{{ $f }}" aria-label="বাছাই: {{ $f }}"></td>
                            <td>
                                <div class="bk-file">
                                    <span class="bk-file-icon {{ $cTone }}"><i class="fa-solid {{ $b['extension'] === 'sqlite' ? 'fa-database' : $cIcon }}"></i></span>
                                    <div class="min-w-0">
                                        <div class="bk-name" title="{{ $f }}">{{ $f }}</div>
                                        <small>
                                            <span class="bk-tag {{ $cTone }}">{{ $cLabel }}</span>
                                            @if($loop->first)<span class="bk-tag is-new">Latest</span>@endif
                                            <span class="bk-ver {{ $ver ? ($ver['ok'] ? 'is-ok' : 'is-bad') : '' }}" data-ver @if(!$ver) hidden @endif title="{{ $ver ? 'যাচাই: ' . \Illuminate\Support\Carbon::createFromTimestamp($ver['at'])->format('d M, h:i A') : '' }}"><i class="fa-solid {{ $ver && !$ver['ok'] ? 'fa-circle-xmark' : 'fa-circle-check' }}"></i> <span>{{ $ver ? ($ver['ok'] ? 'Verified' : 'Failed') : '' }}</span></span>
                                            {{ $cSub }} · .{{ $b['extension'] }}
                                        </small>
                                    </div>
                                </div>
                            </td>
                            <td class="text-nowrap bk-size">{{ $b['size'] }}</td>
                            <td class="text-nowrap">
                                <span class="bk-date">{{ $b['created_at']->format('d M Y, h:i A') }}</span>
                                <small class="d-block text-muted">{{ $b['created_at']->locale('bn')->diffForHumans() }}</small>
                            </td>
                            <td class="text-end text-nowrap">
                                <div class="bk-acts">
                                    <button type="button" class="bk-icon" data-act="verify" data-file="{{ $f }}" title="Verify — লাইভ ডাটাবেজ না ছুঁয়ে যাচাই" aria-label="Verify"><i class="fa-solid fa-circle-check"></i></button>
                                    <a href="{{ route('admin.backup.download', $f) }}" class="bk-icon" title="Download" aria-label="Download"><i class="fa-solid fa-download"></i></a>
                                    @if($b['category'] !== 'masked')
                                        <button type="button" class="bk-btn is-sm is-danger-soft" data-act="restore" data-file="{{ $f }}"><i class="fa-solid fa-rotate-left"></i><span>Restore</span></button>
                                    @endif
                                    <div class="dropdown d-inline-block">
                                        <button class="bk-icon" type="button" data-bs-toggle="dropdown" aria-label="আরও" title="আরও"><i class="fa-solid fa-ellipsis-vertical"></i></button>
                                        <ul class="dropdown-menu dropdown-menu-end bk-menu">
                                            @if($b['category'] !== 'masked' && $b['extension'] !== 'sqlite')
                                                <li><button type="button" class="dropdown-item" data-act="diff" data-file="{{ $f }}"><i class="fa-solid fa-code-compare"></i><span><b>Compare</b><small>লাইভের সঙ্গে রো মিলিয়ে দেখুন</small></span></button></li>
                                                <li><button type="button" class="dropdown-item" data-act="selective" data-file="{{ $f }}"><i class="fa-solid fa-table-list"></i><span><b>Tables</b><small>নির্দিষ্ট টেবিল রিস্টোর</small></span></button></li>
                                            @endif
                                            @if($b['is_zip'])
                                                <li><button type="button" class="dropdown-item" data-act="preview" data-file="{{ $f }}"><i class="fa-solid fa-eye"></i><span><b>Preview</b><small>ZIP-এর ভেতরের ফাইল</small></span></button></li>
                                            @endif
                                            <li><button type="button" class="dropdown-item" data-act="email" data-file="{{ $f }}"><i class="fa-solid fa-envelope"></i><span><b>Email</b><small>{{ $mailMaxMb }} MB পর্যন্ত</small></span></button></li>
                                            <li><button type="button" class="dropdown-item" data-act="copy" data-file="{{ $f }}"><i class="fa-regular fa-copy"></i><span><b>Copy name</b><small>ফাইলের নাম কপি</small></span></button></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><button type="button" class="dropdown-item text-danger" data-act="delete" data-file="{{ $f }}"><i class="fa-regular fa-trash-can"></i><span><b>Delete</b><small>স্থায়ীভাবে মুছে যাবে</small></span></button></li>
                                        </ul>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr id="emptyRow"><td colspan="5"><div class="bk-empty"><span class="bk-empty-ic"><i class="fa-solid fa-box-open"></i></span><strong>No backups yet</strong><span>ওপরের <b>Database</b> বাটনে প্রথম ব্যাকআপ তৈরি করুন।</span></div></td></tr>
                    @endforelse
                    <tr id="noMatchRow" class="d-none"><td colspan="5"><div class="bk-empty is-small"><i class="fa-solid fa-magnifying-glass"></i><span>কিছু মেলেনি।</span></div></td></tr>
                </tbody>
            </table>
        </div>
    </section>

    <div class="bk-cols">
        {{-- Usage --}}
        <section class="bk-card">
            <div class="bk-card-head"><h3><i class="fa-solid fa-chart-column"></i> Usage</h3><span class="bk-muted">শেষ ৬ মাস</span></div>
            <div class="bk-chart"><canvas id="usageChart" aria-label="মাসভিত্তিক ব্যাকআপের সাইজ"></canvas></div>
        </section>

        {{-- Tables --}}
        <section class="bk-card">
            <div class="bk-card-head">
                <h3><i class="fa-solid fa-table"></i> Tables <span class="bk-count">{{ count($db['tables']) }}</span></h3>
                <label class="bk-search is-small"><i class="fa-solid fa-magnifying-glass"></i><input type="search" id="schemaSearch" placeholder="Search table…" aria-label="টেবিল খুঁজুন"></label>
            </div>
            <div class="bk-schema">
                <table class="table bk-table mb-0" id="schemaTable">
                    <thead><tr><th>Name</th><th class="text-end">Rows</th><th class="text-end">Size</th></tr></thead>
                    <tbody>
                        @php $maxRows = max([1, ...array_map('intval', array_column($db['tables'], 'rows'))]); @endphp
                        @forelse($db['tables'] as $t)
                            <tr data-name="{{ strtolower($t['name']) }}">
                                <td class="bk-mono">{{ $t['name'] }}<span class="bk-bar"><i style="width: {{ max(1, round($t['rows'] / $maxRows * 100)) }}%"></i></span></td>
                                <td class="text-end">{{ number_format($t['rows']) }}</td>
                                <td class="text-end text-muted">{{ $t['size'] }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-muted py-4">—</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>

{{-- Bulk bar --}}
<div class="bk-bulk" id="bulkBar">
    <button type="button" class="bk-bulk-x" data-act="bulk-clear" aria-label="বাছাই বাতিল"><i class="fa-solid fa-xmark"></i></button>
    <span id="bulkCount">০ নির্বাচিত</span>
    <button type="button" class="bk-btn is-sm is-danger" data-act="bulk-delete"><i class="fa-regular fa-trash-can"></i><span>Delete</span></button>
</div>
<div id="toastBox" class="bk-toasts" aria-live="polite"></div>

@include('admin.backup._modals')

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script src="{{ asset('js/admin-backup.js') }}?v={{ @filemtime(public_path('js/admin-backup.js')) ?: 1 }}"></script>
@endpush
@endsection
