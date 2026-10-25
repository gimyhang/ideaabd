@extends('layouts.admin')

@section('title', 'ব্যাকআপ')
@section('heading', 'ব্যাকআপ')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">ড্যাশবোর্ড</a></li>
    <li class="breadcrumb-item active">ব্যাকআপ</li>
@endsection

@php
    $freqLabel = ['daily' => 'প্রতিদিন', 'weekly' => 'প্রতি সপ্তাহে', 'monthly' => 'প্রতি মাসে'];
    $tabs      = ['all' => 'সব', 'archive' => 'ডাটাবেজ + মিডিয়া', 'database' => 'ডাটাবেজ', 'safety' => 'সেফটি', 'masked' => 'মাস্কড'];
    $healthTone = ['good' => ['success', 'ভালো'], 'fair' => ['warning', 'মাঝারি'], 'poor' => ['danger', 'ঝুঁকিপূর্ণ']][$health['level']];
    $barTone   = ['safe' => 'bg-primary', 'warning' => 'bg-warning', 'critical' => 'bg-danger'][$space['level']];
@endphp

@section('actions')
    <button type="button" class="btn btn-primary btn-sm rounded-pill px-3" data-backup="db_only">
        <i class="fa-solid fa-database me-1"></i> ডাটাবেজ
    </button>
    <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3" data-backup="data_media">
        <i class="fa-solid fa-box-archive me-1"></i> ডাটাবেজ + মিডিয়া
    </button>
    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" id="btnUpload">
        <i class="fa-solid fa-cloud-arrow-up me-1"></i> আপলোড
    </button>
    <div class="dropdown">
        <button class="btn btn-outline-secondary btn-sm rounded-circle" type="button" data-bs-toggle="dropdown" aria-label="আরও">
            <i class="fa-solid fa-ellipsis"></i>
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
            <li><button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#settingsModal"><i class="fa-solid fa-gear me-2 text-muted"></i>সেটিংস</button></li>
            <li><button type="button" class="dropdown-item" data-act="masked"><i class="fa-solid fa-user-shield me-2 text-muted"></i>মাস্কড ডাম্প</button></li>
            <li>
                <form action="{{ route('admin.backup.integrity') }}" method="POST">@csrf
                    <button class="dropdown-item"><i class="fa-solid fa-stethoscope me-2 text-muted"></i>ডাটাবেজ পরীক্ষা</button>
                </form>
            </li>
            <li>
                <form action="{{ route('admin.backup.optimize') }}" method="POST" data-confirm="সব টেবিল অপটিমাইজ করবেন?" data-confirm-title="অপটিমাইজ" data-confirm-icon="info">@csrf
                    <button class="dropdown-item"><i class="fa-solid fa-wand-magic-sparkles me-2 text-muted"></i>অপটিমাইজ</button>
                </form>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
                <form action="{{ route('admin.backup.cleanup-storage') }}" method="POST" data-confirm="সেটিংসের সীমার বাইরের পুরনো ব্যাকআপ মুছে যাবে ({{ $settings['retention_days'] }}টি রাখা হবে)।" data-confirm-title="পুরনো মুছুন" data-confirm-icon="warning">@csrf
                    <button class="dropdown-item text-danger"><i class="fa-solid fa-broom me-2"></i>পুরনো মুছুন</button>
                </form>
            </li>
        </ul>
    </div>
@endsection

@section('content')
<link rel="stylesheet" href="{{ asset('css/admin-backup.css') }}?v={{ @filemtime(public_path('css/admin-backup.css')) ?: 1 }}">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
    window.BACKUP = {
        timeline: @json($timeline),
        tables: @json(array_column($db['tables'], 'name')),
        uploadMaxMb: {{ $uploadMaxMb }},
        mailMaxMb: {{ $mailMaxMb }},
        email: @json($settings['backup_email'] ?: config('mail.from.address')),
        routes: {
            create: @json(route('admin.backup.create')),
            upload: @json(route('admin.backup.upload')),
            bulkDelete: @json(route('admin.backup.bulk-delete')),
            masked: @json(route('admin.backup.export-anonymized')),
            notify: @json(route('admin.backup.test-notification')),
            base: @json(url('admin/backup')),
        },
    };
</script>

<div class="d-flex flex-column gap-3">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-0" role="alert">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-0" role="alert">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger mb-0">{{ $errors->first() }}</div>
    @endif
    <div id="toastBox"></div>

    {{-- Status --}}
    <div class="bkp-grid">
        <div class="bkp-tile">
            <span class="bkp-label">শেষ ব্যাকআপ</span>
            <strong class="bkp-value">{{ $backups ? $backups[0]['created_at']->locale('bn')->diffForHumans() : 'নেই' }}</strong>
            <span class="badge rounded-pill text-bg-{{ $healthTone[0] }} align-self-start">{{ $healthTone[1] }} · {{ $health['score'] }}</span>
            @foreach($health['issues'] as $issue)
                <small class="text-muted">• {{ $issue }}</small>
            @endforeach
        </div>

        <div class="bkp-tile">
            <span class="bkp-label">স্টোরেজ</span>
            <strong class="bkp-value">{{ $space['used'] }} <small class="text-muted fw-normal">/ {{ $space['total'] }}</small></strong>
            <div class="progress bkp-progress"><div class="progress-bar {{ $barTone }}" style="width: {{ max(1, min(100, $space['pct'])) }}%"></div></div>
            <small class="text-muted">বাকি {{ $space['remaining'] }}@if($space['is_quota']) · প্ল্যান কোটা@endif</small>
        </div>

        <div class="bkp-tile">
            <span class="bkp-label">অটো-ব্যাকআপ</span>
            @if($settings['auto_backup_enabled'])
                <strong class="bkp-value text-success">{{ $freqLabel[$settings['backup_frequency']] }} · {{ $settings['backup_time'] }}</strong>
                <small class="text-muted">পরবর্তী: {{ $next?->format('d M, h:i A') }}</small>
            @else
                <strong class="bkp-value text-muted">বন্ধ</strong>
                <button type="button" class="btn btn-link btn-sm p-0 align-self-start" data-bs-toggle="modal" data-bs-target="#settingsModal">চালু করুন</button>
            @endif
            @if($settings['auto_backup_enabled'] && !$schedulerOk)
                <small class="text-danger"><i class="fa-solid fa-triangle-exclamation me-1"></i>সিডিউলার চলছে না — সার্ভারে ক্রন সেট করুন (<code>php artisan schedule:run</code>)</small>
            @endif
            <div class="d-flex flex-wrap gap-1 mt-1">
                @foreach(['telegram' => 'টেলিগ্রাম', 'email' => 'ইমেইল', 'offsite' => 'অফসাইট'] as $key => $label)
                    <button type="button" class="badge rounded-pill border-0 text-bg-{{ $channels[$key] ? 'success' : 'light' }}" data-bs-toggle="modal" data-bs-target="#settingsModal" title="{{ $channels[$key] ? 'চালু' : 'বন্ধ — সেট করুন' }}">
                        <i class="fa-solid {{ $channels[$key] ? 'fa-check' : 'fa-xmark' }} me-1"></i>{{ $label }}
                    </button>
                @endforeach
            </div>
            @if(!empty($last['at']))
                <small class="{{ ($last['status'] ?? '') === 'ok' ? 'text-muted' : 'text-danger' }}">
                    শেষ রান: {{ \Illuminate\Support\Carbon::parse($last['at'])->format('d M, h:i A') }} · {{ ($last['status'] ?? '') === 'ok' ? 'সফল' : 'ব্যর্থ' }}
                </small>
            @endif
        </div>

        <div class="bkp-tile">
            <span class="bkp-label">ডাটাবেজ</span>
            <strong class="bkp-value">{{ $db['size'] }}</strong>
            <small class="text-muted">{{ $db['name'] }} ({{ strtoupper($db['driver']) }}) · {{ count($db['tables']) }} টেবিল · {{ number_format($db['rows']) }} রো</small>
        </div>
    </div>

    {{-- Upload --}}
    <div class="bkp-drop" id="dropZone">
        <input type="file" id="backupFileInput" class="d-none" accept=".zip,.sql,.gz,.sqlite">
        <div id="dropIdle"><i class="fa-solid fa-cloud-arrow-up me-2"></i>ফাইল ছাড়ুন বা ক্লিক করুন <span class="text-muted">· .zip .sql .gz .sqlite · সর্বোচ্চ {{ $uploadMaxMb }} MB</span></div>
        <div id="dropBusy" class="d-none">
            <div class="d-flex justify-content-between small mb-1"><span id="uploadName"></span><span id="uploadPct">0%</span></div>
            <div class="progress bkp-progress"><div class="progress-bar" id="uploadBar" style="width:0%"></div></div>
        </div>
    </div>

    {{-- Bulk bar --}}
    <div class="bkp-bulk" id="bulkBar">
        <span id="bulkCount">০ নির্বাচিত</span>
        <button type="button" class="btn btn-sm btn-danger rounded-pill px-3" data-act="bulk-delete"><i class="fa-solid fa-trash-can me-1"></i>মুছুন</button>
    </div>

    {{-- Backups --}}
    <div class="card bkp-card">
        <div class="bkp-head">
            <ul class="nav bkp-tabs" id="bkTabs">
                @foreach($tabs as $key => $label)
                    <li class="nav-item">
                        <button type="button" class="nav-link {{ $loop->first ? 'active' : '' }}" data-cat="{{ $key }}">{{ $label }} <span class="bkp-count">{{ $counts[$key] }}</span></button>
                    </li>
                @endforeach
            </ul>
            <div class="d-flex align-items-center gap-2 ms-auto">
                <input type="search" id="backupSearch" class="form-control form-control-sm" placeholder="খুঁজুন" aria-label="খুঁজুন">
                <span class="text-muted small text-nowrap" id="shownCount">{{ count($backups) }}টি</span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table bkp-table align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width:36px"><input type="checkbox" class="form-check-input" id="selectAll" aria-label="সব বাছাই"></th>
                        <th>ফাইল</th>
                        <th>সাইজ</th>
                        <th>তৈরি</th>
                        <th class="text-end">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody id="backupRows">
                    @forelse($backups as $b)
                        @php $f = $b['filename']; @endphp
                        <tr data-name="{{ strtolower($f) }}" data-cat="{{ $b['category'] }}" data-file="{{ $f }}">
                            <td><input type="checkbox" class="form-check-input row-check" value="{{ $f }}" aria-label="বাছাই"></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fa-solid {{ $b['is_zip'] ? 'fa-file-zipper text-primary' : ($b['extension'] === 'sqlite' ? 'fa-database text-success' : 'fa-file-code text-secondary') }}"></i>
                                    <div class="min-w-0">
                                        <div class="font-monospace text-truncate bkp-name" title="{{ $f }}">{{ $f }}</div>
                                        <small class="text-muted">{{ ['archive' => 'ডাটাবেজ + মিডিয়া', 'database' => 'ডাটাবেজ', 'safety' => 'সেফটি কপি', 'masked' => 'মাস্কড ডাম্প'][$b['category']] }}</small>
                                    </div>
                                </div>
                            </td>
                            <td class="text-nowrap">{{ $b['size'] }}</td>
                            <td class="text-nowrap">
                                {{ $b['created_at']->format('d M Y, h:i A') }}
                                <small class="d-block text-muted">{{ $b['created_at']->locale('bn')->diffForHumans() }}</small>
                            </td>
                            <td class="text-end text-nowrap">
                                @if($b['category'] !== 'masked')
                                    <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3" data-act="restore" data-file="{{ $f }}">রিস্টোর</button>
                                @endif
                                <a href="{{ route('admin.backup.download', $f) }}" class="btn btn-sm btn-light rounded-circle" title="ডাউনলোড"><i class="fa-solid fa-download"></i></a>
                                <div class="dropdown d-inline-block">
                                    <button class="btn btn-sm btn-light rounded-circle" type="button" data-bs-toggle="dropdown" aria-label="আরও"><i class="fa-solid fa-ellipsis"></i></button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                        <li><button type="button" class="dropdown-item" data-act="verify" data-file="{{ $f }}">যাচাই</button></li>
                                        @if($b['category'] !== 'masked' && $b['extension'] !== 'sqlite')
                                            <li><button type="button" class="dropdown-item" data-act="diff" data-file="{{ $f }}">তুলনা</button></li>
                                            <li><button type="button" class="dropdown-item" data-act="selective" data-file="{{ $f }}">টেবিল বাছাই</button></li>
                                        @endif
                                        @if($b['is_zip'])
                                            <li><button type="button" class="dropdown-item" data-act="preview" data-file="{{ $f }}">প্রিভিউ</button></li>
                                        @endif
                                        <li><button type="button" class="dropdown-item" data-act="email" data-file="{{ $f }}">ইমেইল</button></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><button type="button" class="dropdown-item text-danger" data-act="delete" data-file="{{ $f }}">মুছুন</button></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr id="emptyRow"><td colspan="5" class="text-center text-muted py-5">কোনো ব্যাকআপ নেই। উপরের “ডাটাবেজ” বাটনে প্রথমটি তৈরি করুন।</td></tr>
                    @endforelse
                    <tr id="noMatchRow" class="d-none"><td colspan="5" class="text-center text-muted py-4">মিলছে না।</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Usage chart --}}
    <div class="card bkp-card">
        <div class="bkp-head"><strong>ব্যবহার · ৬ মাস</strong></div>
        <div class="p-3"><div class="bkp-chart"><canvas id="usageChart"></canvas></div></div>
    </div>

    {{-- Tables --}}
    @if(!empty($db['tables']))
        <div class="card bkp-card">
            <button type="button" class="bkp-head bkp-toggle" data-bs-toggle="collapse" data-bs-target="#tablesBox" aria-expanded="false">
                <strong>টেবিল <span class="bkp-count">{{ count($db['tables']) }}</span></strong>
                <i class="fa-solid fa-chevron-down ms-auto text-muted"></i>
            </button>
            <div class="collapse" id="tablesBox">
                <div class="p-3 border-bottom"><input type="search" id="schemaSearch" class="form-control form-control-sm" placeholder="টেবিল খুঁজুন"></div>
                <div class="table-responsive" style="max-height: 420px">
                    <table class="table bkp-table mb-0" id="schemaTable">
                        <thead><tr><th>নাম</th><th class="text-end">রো</th><th class="text-end">সাইজ</th></tr></thead>
                        <tbody>
                            @foreach($db['tables'] as $t)
                                <tr data-name="{{ strtolower($t['name']) }}">
                                    <td class="font-monospace">{{ $t['name'] }}</td>
                                    <td class="text-end">{{ number_format($t['rows']) }}</td>
                                    <td class="text-end text-muted">{{ $t['size'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>

@include('admin.backup._modals')

@push('scripts')
<script src="{{ asset('js/admin-backup.js') }}?v={{ @filemtime(public_path('js/admin-backup.js')) ?: 1 }}"></script>
@endpush
@endsection
