@extends('layouts.admin')

@section('title', 'Master Backup & Disaster Recovery — আইডিয়া প্রকাশন')
@section('heading', 'মাস্টার ব্যাকআপ ও ডিজাস্টার রিকভারি হাব')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">ড্যাশবোর্ড</a></li>
    <li class="breadcrumb-item active">মাস্টার ব্যাকআপ হাব</li>
@endsection

@section('actions')
    <div class="d-flex flex-wrap align-items-center gap-2">
        {{-- 1. Upload Backup Button (Rose Gradient) --}}
        <button type="button" class="btn-backup-gradient btn-gradient-rose" onclick="document.getElementById('backupFileInput').click()" title="বাহ্যিক ব্যাকআপ আপলোড">
            <i class="fa-solid fa-cloud-arrow-up"></i>
            <span>আপলোড ব্যাকআপ</span>
        </button>

        {{-- 2. Anonymized Developer Dump (Amber Gradient) --}}
        <button type="button" class="btn-backup-gradient btn-gradient-amber" onclick="generateAnonymizedDump()" title="ডেভেলপার ও স্টেজিং এর জন্য কাস্টমার ডাটা মাস্ক করে ডাম্প">
            <i class="fa-solid fa-user-shield"></i>
            <span>অ্যানোনিমাস ডাম্প</span>
        </button>

        {{-- 3. 1-Click Integrity Health Check (Purple Gradient) --}}
        <form action="{{ route('admin.backup.integrity') }}" method="POST" class="m-0">
            @csrf
            <button type="submit" class="btn-backup-gradient btn-gradient-purple" title="ডাটাবেজ টেবিল ইন্টিগ্রিটি স্ক্যান">
                <i class="fa-solid fa-stethoscope"></i>
                <span>ইন্টিগ্রিটি স্ক্যান</span>
            </button>
        </form>

        {{-- 4. 1-Click Database Table Optimizer (Sky Gradient) --}}
        <form action="{{ route('admin.backup.optimize') }}" method="POST" class="m-0"
              data-confirm="আপনি কি ডাটাবেজের সমস্ত টেবিল ও ইনডেক্স অপ্টিমাইজ করতে চান?"
              data-confirm-title="ডাটাবেজ অপ্টিমাইজেশন"
              data-confirm-icon="info"
              data-confirm-btn="<i class='fa-solid fa-wand-magic-sparkles me-1'></i> হ্যাঁ, অপ্টিমাইজ করুন">
            @csrf
            <button type="submit" class="btn-backup-gradient btn-gradient-sky" title="টেবিল ইনডেক্স ও সাইজ অপ্টিমাইজ">
                <i class="fa-solid fa-wand-magic-sparkles"></i>
                <span>ডাটাবেজ অপ্টিমাইজ</span>
            </button>
        </form>

        {{-- 5. 1-Click Complete Data & Media Images Backup (.ZIP) (Emerald Gradient) --}}
        <button type="button" class="btn-backup-gradient btn-gradient-emerald" onclick="triggerLiveBackup('data_media', 'সমস্ত ডাটা ও ছবি ব্যাকআপ (.ZIP)')" title="ডাটাবেজ + সমস্ত বইয়ের প্রচ্ছদ ও মিডিয়া ব্যাকআপ">
            <i class="fa-solid fa-box-archive"></i>
            <span>ডাটা ও ছবি ব্যাকআপ (.ZIP)</span>
        </button>

        {{-- 6. 1-Click Full System Backup (.ZIP) (Indigo Gradient) --}}
        <button type="button" class="btn-backup-gradient btn-gradient-indigo" onclick="triggerLiveBackup('full_system', 'সম্পূর্ণ সিস্টেম ও সোর্স কোড ব্যাকআপ')" title="সম্পূর্ণ সিস্টেম ও ডাটাবেজ মাস্টার ব্যাকআপ">
            <i class="fa-solid fa-file-zipper"></i>
            <span>সম্পূর্ণ সিস্টেম ব্যাকআপ</span>
        </button>
    </div>
@endsection

@section('content')
<link rel="stylesheet" href="{{ asset('css/admin-backup.css') }}?v={{ @filemtime(public_path('css/admin-backup.css')) ?: 3 }}">

<script>
    window.BACKUP_ROUTES = {
        create: "{{ route('admin.backup.create') }}",
        upload: "{{ route('admin.backup.upload') }}",
        bulkDelete: "{{ route('admin.backup.bulk-delete') }}",
        inspect: "{{ url('admin/backup/inspect') }}",
        diff: "{{ url('admin/backup/diff') }}",
        dryRun: "{{ url('admin/backup/dry-run') }}",
        selectiveRestore: "{{ url('admin/backup/selective-restore') }}",
        exportAnonymized: "{{ route('admin.backup.export-anonymized') }}",
        testNotification: "{{ route('admin.backup.test-notification') }}",
        email: "{{ url('admin/backup/email') }}",
        restore: "{{ url('admin/backup/restore') }}"
    };
</script>

<div class="d-flex flex-column gap-3.5">

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-0 rounded-4 shadow-sm border-0 border-start border-4 border-success bg-white py-3 px-3.5" role="alert">
            <i class="fa-solid fa-circle-check text-success fs-5 me-2.5"></i>
            <div class="fw-semibold small text-dark">{{ session('success') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center mb-0 rounded-4 shadow-sm border-0 border-start border-4 border-danger bg-white py-3 px-3.5" role="alert">
            <i class="fa-solid fa-triangle-exclamation text-danger fs-5 me-2.5"></i>
            <div class="fw-semibold small text-dark">{{ session('error') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Dynamic Toast Notification Container -->
    <div id="dynamicAlertContainer"></div>

    <!-- 1. Symmetrical & Vibrant 4-Card Diagnostic Grid -->
    <div class="row row-cols-1 row-cols-sm-2 row-cols-xl-4 g-3">
        
        {{-- Card 1: Connected Database Engine --}}
        <div class="col">
            <div class="backup-kpi-card kpi-indigo">
                <div class="d-flex align-items-start justify-content-between mb-2">
                    <div class="min-w-0 pe-2">
                        <span class="text-muted small fw-semibold text-uppercase font-monospace d-block mb-1" style="font-size: 0.70rem; letter-spacing: 0.5px;">কানেক্টেড ডাটাবেজ</span>
                        <h5 class="fw-bold text-dark mb-0 font-monospace text-truncate" style="font-size: 1.05rem;" title="{{ $dbName }}">
                            {{ Str::limit($dbName, 16) }}
                        </h5>
                    </div>
                    <div class="kpi-avatar-icon bg-primary-subtle text-primary">
                        <i class="fa-solid fa-database"></i>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between pt-2.5 border-top">
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace text-uppercase px-2.5 py-0.5 rounded-pill" style="font-size: 0.70rem;">
                        {{ $dbDriver }}
                    </span>
                    <span class="small text-success fw-bold d-inline-flex align-items-center gap-1.5" style="font-size: 0.75rem;">
                        <span class="pulse-online-badge"></span> লাইভ কানেকশন
                    </span>
                </div>
            </div>
        </div>

        {{-- Card 2: Database Volume & Rows --}}
        <div class="col">
            <div class="backup-kpi-card kpi-emerald">
                <div class="d-flex align-items-start justify-content-between mb-2">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase font-monospace d-block mb-1" style="font-size: 0.70rem; letter-spacing: 0.5px;">ডাটাবেজ মোট সাইজ</span>
                        <h4 class="fw-bold text-dark mb-0 font-monospace" style="font-size: 1.25rem;">{{ $formattedDbSize }}</h4>
                    </div>
                    <div class="kpi-avatar-icon bg-success-subtle text-success">
                        <i class="fa-solid fa-server"></i>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between pt-2.5 border-top">
                    <span class="small text-muted font-monospace" style="font-size: 0.75rem;">
                        <i class="fa-solid fa-table-cells text-muted me-1"></i>{{ count($tables) }} টি টেবিল
                    </span>
                    <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace px-2.5 py-0.5 rounded-pill" style="font-size: 0.70rem;">
                        {{ number_format($totalRowsCount) }} টি রেকর্ড
                    </span>
                </div>
            </div>
        </div>

        {{-- Card 3: Master Backups Archive --}}
        <div class="col">
            <div class="backup-kpi-card kpi-amber">
                <div class="d-flex align-items-start justify-content-between mb-2">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase font-monospace d-block mb-1" style="font-size: 0.70rem; letter-spacing: 0.5px;">সংরক্ষিত আর্কাইভ</span>
                        <h4 class="fw-bold text-dark mb-0 font-monospace" style="font-size: 1.25rem;">{{ count($backups) }} টি ফাইল</h4>
                    </div>
                    <div class="kpi-avatar-icon bg-warning-subtle text-warning">
                        <i class="fa-solid fa-file-zipper"></i>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between pt-2.5 border-top">
                    <span class="small text-muted font-monospace" style="font-size: 0.75rem;">ডিস্ক স্টোরেজ</span>
                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle font-monospace px-2.5 py-0.5 rounded-pill" style="font-size: 0.70rem;">
                        {{ $formattedTotalBackupSize }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Card 4: Disaster Recovery & Security --}}
        <div class="col">
            <div class="backup-kpi-card kpi-sky">
                <div class="d-flex align-items-start justify-content-between mb-2">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase font-monospace d-block mb-1" style="font-size: 0.70rem; letter-spacing: 0.5px;">ডিজাস্টার সিকিউরিটি</span>
                        <h5 class="fw-bold text-success mb-0 d-flex align-items-center gap-1.5" style="font-size: 1.05rem;">
                            <i class="fa-solid fa-shield-halved"></i> শতভাগ সুরক্ষিত
                        </h5>
                    </div>
                    <div class="kpi-avatar-icon bg-info-subtle text-info">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between pt-2.5 border-top">
                    <span class="small text-muted font-monospace" style="font-size: 0.75rem;">অটো-রিটেনশন</span>
                    <button type="button" class="btn btn-xs btn-outline-info rounded-pill px-2 py-0.5 font-monospace fw-bold" data-bs-toggle="modal" data-bs-target="#backupSettingsModal">
                        <i class="fa-solid fa-gear me-1"></i> সেটিংস
                    </button>
                </div>
            </div>
        </div>

    </div>

    <!-- 2. Dynamic Drag & Drop Instant Upload Zone -->
    <div class="card border-0 shadow-sm rounded-4 bg-white p-4">
        <div class="d-flex align-items-center justify-content-between mb-3 pb-2.5 border-bottom">
            <div class="d-flex align-items-center gap-2">
                <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                    <i class="fa-solid fa-file-arrow-up"></i>
                </div>
                <h6 class="fw-bold text-dark mb-0" style="font-size: 0.96rem;">ডাটাবেজ ও মাস্টার ব্যাকআপ ফাইল আপলোড</h6>
            </div>
            <span class="badge bg-light text-muted border rounded-pill px-3 py-1 font-monospace small">
                অনুমোদিত: .ZIP, .SQL, .SQLITE, .GZ (সর্বোচ্চ ২০০ MB)
            </span>
        </div>

        <div class="enterprise-dropzone" id="dropZone" onclick="document.getElementById('backupFileInput').click()">
            <input type="file" id="backupFileInput" class="d-none" accept=".zip,.sql,.sqlite,.gz" onchange="handleDynamicUpload(this.files)">
            
            <div id="dropZonePrompt">
                <div class="dropzone-icon-circle">
                    <i class="fa-solid fa-cloud-arrow-up"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1.5" style="font-size: 1.08rem;">
                    কম্পিউটার থেকে ব্যাকআপ ফাইল (.ZIP / .SQL) এখানে টেনে আনুন
                </h6>
                <p class="text-muted small mb-3" style="font-size: 0.86rem;">
                    অথবা নিচের বাটনে ক্লিক করে ফাইল নির্বাচন করুন — সিস্টেম স্বয়ংক্রিয়ভাবে আপলোড ও ভেরিফাই করবে
                </p>
                <button type="button" class="btn-backup-gradient btn-gradient-indigo px-4" onclick="event.stopPropagation(); document.getElementById('backupFileInput').click()">
                    <i class="fa-solid fa-folder-open"></i>
                    <span>ফাইল নির্বাচন করুন (Browse File)</span>
                </button>
            </div>

            {{-- Live Dynamic Upload Progress Bar --}}
            <div class="d-none py-3" id="uploadProgressSection">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="d-flex align-items-center gap-2">
                        <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                        <span class="small fw-bold text-primary font-monospace" id="uploadFilenameText">আপলোড হচ্ছে...</span>
                    </div>
                    <span class="small fw-bold font-monospace text-dark" id="uploadPercentText">0%</span>
                </div>
                <div class="progress rounded-pill shadow-xs" style="height: 14px; background-color: #e2e8f0;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" id="uploadProgressBar" role="progressbar" style="width: 0%"></div>
                </div>
                <small class="text-muted d-block mt-2 font-monospace" style="font-size: 11px;">অনুগ্রহ করে অপেক্ষা করুন, ফাইলটি সার্ভারে আপলোড ও ভ্যালিডেশন হচ্ছে...</small>
            </div>
        </div>
    </div>

    <!-- 3. Sticky Bulk Actions Toolbar -->
    <div class="bulk-action-bar" id="bulkActionBar">
        <div class="d-flex align-items-center gap-2.5">
            <i class="fa-solid fa-check-double text-warning fs-5"></i>
            <span class="fw-bold font-monospace" id="bulkSelectedCount">0 টি নির্বাচিত</span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-sm btn-outline-light rounded-pill px-3 fw-bold" onclick="deleteSelectedBackups()">
                <i class="fa-solid fa-trash-can me-1 text-danger"></i> নির্বাচিত ফাইল মুছুন
            </button>
        </div>
    </div>

    <!-- 4. Master Backup Archive Records Table -->
    <div class="card bg-white rounded-4 shadow-sm border-0 overflow-hidden">
        
        {{-- Card Header with Live Search & Format Filter --}}
        <div class="card-header bg-white py-3 px-4 border-bottom">
            <div class="row g-2 align-items-center justify-content-between">
                <div class="col-12 col-md-4">
                    <div class="d-flex align-items-center gap-2.5">
                        <div class="rounded-circle bg-primary-subtle text-primary p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                            <i class="fa-solid fa-file-zipper"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold text-dark mb-0" style="font-size: 0.96rem;">মাস্টার ব্যাকআপ আর্কাইভ তালিকা</h6>
                            <small class="text-muted font-monospace" style="font-size: 11px;">ডাটাবেজ ডাম্প + সমস্ত আপলোড করা ইমেজ ও বুক কভার</small>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-8">
                    <div class="d-flex flex-wrap align-items-center justify-content-md-end gap-2">
                        {{-- Live Search Filter --}}
                        <div class="input-group input-group-sm" style="max-width: 240px;">
                            <span class="input-group-text bg-light border-end-0 text-muted">
                                <i class="fa-solid fa-magnifying-glass"></i>
                            </span>
                            <input type="search" id="backupSearchInput" class="form-control border-start-0 ps-0 fw-semibold" placeholder="আর্কাইভ খুঁজুন...">
                        </div>

                        {{-- Format Selector --}}
                        <select id="backupFormatFilter" class="form-select form-select-sm fw-semibold" style="max-width: 140px;">
                            <option value="all">সব ফরম্যাট</option>
                            <option value="zip">Master ZIP</option>
                            <option value="sql">SQL Dump</option>
                            <option value="sqlite">SQLite</option>
                        </select>

                        {{-- Total Badge --}}
                        <span class="badge bg-light text-dark border rounded-pill px-3 py-2 font-monospace small" id="backupCountBadge">
                            {{ count($backups) }} টি ফাইল
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Table Container --}}
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="backupsTable">
                    <thead class="table-light small text-uppercase font-monospace text-muted">
                        <tr>
                            <th class="ps-4 py-3" style="width: 40px;">
                                <input type="checkbox" class="form-check-input" id="selectAllBackups">
                            </th>
                            <th class="py-3" style="min-width: 260px;">আর্কাইভ ফাইল নাম</th>
                            <th class="py-3" style="width: 160px;">ফরম্যাট / ধরন</th>
                            <th class="py-3" style="width: 120px;">সাইজ</th>
                            <th class="py-3" style="width: 170px;">তৈরির সময়</th>
                            <th class="text-end pe-4 py-3" style="min-width: 250px;">অ্যাকশন বাটন</th>
                        </tr>
                    </thead>
                    <tbody id="backupsTableBody">
                        @forelse($backups as $b)
                            <tr class="table-custom-row" id="row-{{ md5($b['filename']) }}" 
                                data-filename="{{ strtolower($b['filename']) }}" 
                                data-ext="{{ strtolower($b['extension']) }}"
                                data-date="{{ $b['created_at']->format('d M, Y') }}">
                                <td class="ps-4">
                                    <input type="checkbox" class="form-check-input backup-select-cb" value="{{ $b['filename'] }}">
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2.5">
                                        <div class="rounded-3 {{ $b['is_master_zip'] ? 'bg-primary text-white' : 'bg-light border text-muted' }} p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width:38px;height:38px;">
                                            @if($b['is_master_zip'])
                                                <i class="fa-solid fa-file-zipper fs-6"></i>
                                            @elseif($b['extension'] === 'sqlite')
                                                <i class="fa-solid fa-database text-success fs-6"></i>
                                            @else
                                                <i class="fa-solid fa-file-code fs-6"></i>
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <span class="fw-bold text-dark font-monospace text-truncate d-block" title="{{ $b['filename'] }}" style="font-size: 0.88rem;">
                                                {{ $b['filename'] }}
                                            </span>
                                            <small class="text-muted font-monospace" style="font-size: 11px;">
                                                {{ $b['is_master_zip'] ? 'Full System Master ZIP (DB + Media)' : 'Database Dump' }}
                                            </small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($b['is_master_zip'])
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1 font-monospace" style="font-size: 0.70rem;">
                                            <i class="fa-solid fa-box-archive me-1"></i> MASTER .ZIP
                                        </span>
                                    @else
                                        <span class="badge bg-light text-dark border rounded-pill px-2.5 py-1 font-monospace" style="font-size: 0.70rem;">
                                            {{ strtoupper($b['extension']) }}
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <span class="fw-bold text-dark font-monospace" style="font-size: 0.86rem;">{{ $b['size'] }}</span>
                                </td>
                                <td>
                                    <div class="text-dark small fw-semibold font-monospace">{{ $b['created_at']->format('d M, Y h:i A') }}</div>
                                    <small class="text-muted font-monospace" style="font-size: 11px;">{{ $b['created_at']->diffForHumans() }}</small>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-flex align-items-center justify-content-end flex-wrap gap-1.5">
                                        {{-- 1. Diff & Analytics --}}
                                        <button type="button" class="btn-action-pill btn-action-cyan" onclick="openDiffModal('{{ $b['filename'] }}')" title="ডাটাবেজ ও ব্যাকআপ তুলনা (Diff)">
                                            <i class="fa-solid fa-code-compare"></i> ডিফ
                                        </button>

                                        {{-- 2. Safe Dry-Run Simulation --}}
                                        <button type="button" class="btn-action-pill btn-action-purple" onclick="runDryRunSimulation('{{ $b['filename'] }}')" title="স্যান্ডবক্স ড্রাই-রান সিমুলেশন">
                                            <i class="fa-solid fa-flask-vial"></i> টেস্ট
                                        </button>

                                        {{-- 3. Selective Table Restore --}}
                                        <button type="button" class="btn-action-pill btn-action-amber" onclick="openSelectiveRestoreModal('{{ $b['filename'] }}')" title="নির্দিষ্ট টেবিল রিস্টোর">
                                            <i class="fa-solid fa-list-check"></i> সিলেক্টিভ
                                        </button>

                                        {{-- 4. Inspect ZIP Preview --}}
                                        @if($b['is_master_zip'])
                                            <button type="button" class="btn-action-pill btn-action-sky" onclick="inspectZipArchive('{{ $b['filename'] }}')" title="আর্কাইভ প্রিভিউ দেখুন">
                                                <i class="fa-solid fa-eye"></i> প্রিভিউ
                                            </button>
                                        @endif

                                        {{-- 5. Download --}}
                                        <a href="{{ route('admin.backup.download', $b['filename']) }}" class="btn-action-pill btn-action-indigo" title="কম্পিউটারে ডাউনলোড করুন">
                                            <i class="fa-solid fa-download"></i>
                                        </a>

                                        {{-- 6. Email Dispatch --}}
                                        <button type="button" class="btn-action-pill btn-action-emerald" onclick="openEmailModal('{{ $b['filename'] }}')" title="ইমেইলে ব্যাকআপ পাঠান">
                                            <i class="fa-solid fa-paper-plane"></i>
                                        </button>

                                        {{-- 7. Full Restore with Safety Guarantee --}}
                                        <button type="button" class="btn-action-pill btn-action-emerald" 
                                                onclick="confirmRestore('{{ $b['filename'] }}', {{ $b['is_master_zip'] ? 'true' : 'false' }})" title="সম্পূর্ণ রিস্টোর">
                                            <i class="fa-solid fa-rotate-left"></i> রিস্টোর
                                        </button>

                                        {{-- 8. Delete --}}
                                        <form action="{{ route('admin.backup.destroy', $b['filename']) }}" method="POST"
                                              data-confirm="আপনি কি নিশ্চিত এই ব্যাকআপ ফাইলটি ({{ $b['filename'] }}) মুছে ফেলতে চান?"
                                              data-confirm-title="ব্যাকআপ ফাইল অপসারণ"
                                              data-confirm-icon="warning"
                                              data-confirm-btn="<i class='fa-solid fa-trash-can me-1'></i> মুছে ফেলুন"
                                              class="d-inline m-0">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-action-pill btn-action-rose" title="মুছে ফেলুন">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr id="emptyRow">
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <div class="p-3 text-center">
                                        <i class="fa-solid fa-file-zipper fs-1 text-secondary opacity-40 mb-3 d-block"></i>
                                        <h6 class="fw-bold text-dark">কোনো ব্যাকআপ ফাইল সংরক্ষিত নেই</h6>
                                        <p class="small text-muted mb-3">উপরের বাটনে ক্লিক করে প্রথম মাস্টার ব্যাকআপ (.ZIP) তৈরি করুন</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                        <tr id="emptySearchRow" style="display: none;">
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="fa-solid fa-magnifying-glass me-1"></i> সার্চের সাথে মিল রেখে কোনো ব্যাকআপ ফাইল পাওয়া যায়নি।
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 5. Database Tables & Records Breakdown Accordion -->
    @if(!empty($tables))
        <div class="card bg-white rounded-4 shadow-sm border-0 overflow-hidden">
            <div class="card-header bg-white d-flex align-items-center justify-content-between py-3 px-4 border-bottom">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle bg-info-subtle text-info d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                        <i class="fa-solid fa-table-list"></i>
                    </div>
                    <h6 class="fw-bold text-dark mb-0" style="font-size: 0.96rem;">ডাটাবেজ টেবিল ও রেকর্ড বিবরণী ({{ count($tables) }} টি টেবিল)</h6>
                </div>
                <button class="btn btn-sm btn-light border rounded-pill px-3 fw-semibold font-monospace" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTables" aria-expanded="false">
                    <i class="fa-solid fa-chevron-down me-1"></i> বিস্তারিত দেখুন
                </button>
            </div>
            <div class="collapse" id="collapseTables">
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 420px; overflow-y: auto;">
                        <table class="table table-hover table-sm align-middle mb-0 small">
                            <thead class="table-light sticky-top font-monospace">
                                <tr>
                                    <th class="ps-4 py-2.5">টেবিল নাম</th>
                                    <th class="py-2.5">মোট রেকর্ড সংখ্যা</th>
                                    <th class="text-end pe-4 py-2.5">সাইজ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($tables as $tbl)
                                    <tr>
                                        <td class="ps-4 font-monospace text-dark fw-semibold">{{ $tbl['name'] }}</td>
                                        <td>
                                            <span class="badge bg-light text-dark border font-monospace">{{ number_format($tbl['rows']) }} rows</span>
                                        </td>
                                        <td class="text-end pe-4 font-monospace text-muted">{{ $tbl['size'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>

{{-- ========================================================================= --}}
{{-- MODAL 1: LIVE BACKUP CREATION PROGRESS MODAL                               --}}
{{-- ========================================================================= --}}
<div class="modal fade" id="backupProgressModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header bg-dark text-white py-3 px-4 rounded-top-4">
                <h6 class="modal-title fw-bold text-white d-flex align-items-center gap-2 mb-0" id="backupProgressTitle">
                    <i class="fa-solid fa-box-archive text-warning"></i>
                    <span>মাস্টার ব্যাকআপ তৈরি হচ্ছে</span>
                </h6>
            </div>
            <div class="modal-body p-4 text-center">
                <div class="rounded-circle bg-primary-subtle text-primary p-3 d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px;">
                    <i class="fa-solid fa-gears fs-2 animate-spin"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1 font-monospace" id="backupProgressStatusText">ডাটাবেজ ও মিডিয়া সংকলন হচ্ছে...</h6>
                <p class="text-muted small mb-3">অনুগ্রহ করে কয়েক সেকেন্ড অপেক্ষা করুন, ফাইলটি কম্প্রেস করে সংরক্ষণ করা হচ্ছে।</p>
                <div class="progress rounded-pill shadow-xs mb-2" style="height: 14px; background-color: #e2e8f0;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" id="backupCreationProgressBar" style="width: 35%;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- MODAL 2: INSPECT ZIP PREVIEW MODAL                                        --}}
{{-- ========================================================================= --}}
<div class="modal fade" id="inspectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header bg-dark text-white py-3 px-4 rounded-top-4">
                <h6 class="modal-title fw-bold text-white mb-0 d-flex align-items-center gap-2">
                    <i class="fa-solid fa-file-zipper text-warning"></i>
                    <span>মাস্টার জিপ আর্কাইভ প্রিভিউ: <span id="inspectFilename" class="font-monospace text-info"></span></span>
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="inspectBody">
                <div class="text-center py-4 text-muted">
                    <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div> আর্কাইভ ফাইল স্ক্যান করা হচ্ছে...
                </div>
            </div>
            <div class="modal-footer bg-light py-2.5 px-4 rounded-bottom-4">
                <button type="button" class="btn btn-sm btn-secondary rounded-pill px-4 fw-bold" data-bs-dismiss="modal">বন্ধ করুন</button>
            </div>
        </div>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- MODAL 3: EMAIL DISPATCH MODAL                                             --}}
{{-- ========================================================================= --}}
<div class="modal fade" id="emailDispatchModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header bg-dark text-white py-3 px-4 rounded-top-4">
                <h6 class="modal-title fw-bold text-white mb-0 d-flex align-items-center gap-2">
                    <i class="fa-solid fa-paper-plane text-warning"></i>
                    <span>ইমেইলে ব্যাকআপ ফাইল পাঠান</span>
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" id="emailModalFilename">
                <div class="mb-3">
                    <label class="form-label small fw-bold text-dark">নির্বাচিত ব্যাকআপ ফাইল:</label>
                    <div class="p-2.5 bg-light rounded-3 font-monospace text-primary fw-bold small border" id="emailModalFilenameDisplay"></div>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold text-dark">প্রাপকের ইমেইল ঠিকানা:</label>
                    <input type="email" id="emailRecipientInput" class="form-control font-monospace" placeholder="admin@example.com" value="{{ config('mail.from.address', 'adideabd@gmail.com') }}">
                    <small class="text-muted d-block mt-1 font-monospace" style="font-size: 11px;">ব্যাকআপ আর্কাইভটি অ্যাটাচমেন্ট হিসেবে উল্লেখিত ইমেইলে প্রেরণ করা হবে।</small>
                </div>
            </div>
            <div class="modal-footer bg-light py-2.5 px-4 rounded-bottom-4 d-flex justify-content-between">
                <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-bs-dismiss="modal">বাতিল</button>
                <button type="button" class="btn-backup-gradient btn-gradient-amber px-4" id="btnSubmitEmail" onclick="submitEmailDispatch()">
                    <i class="fa-solid fa-paper-plane"></i>
                    <span>ইমেইলে পাঠান</span>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- MODAL 4: RESTORE CONFIRMATION MODAL                                       --}}
{{-- ========================================================================= --}}
<div class="modal fade" id="restoreModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header bg-danger text-white py-3 px-4 rounded-top-4">
                <h6 class="modal-title fw-bold text-white mb-0 d-flex align-items-center gap-2">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <span>সিস্টেম রিস্টোর সতর্কতা!</span>
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="restoreForm" method="POST">
                @csrf
                <div class="modal-body p-4 text-center">
                    <div class="rounded-circle bg-danger-subtle text-danger p-3 d-inline-flex align-items-center justify-content-center mb-3 shadow-xs" style="width: 60px; height: 60px;">
                        <i class="fa-solid fa-rotate-left fs-2"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-2">আপনি কি নিশ্চিত যে সিস্টেম রিস্টোর করবেন?</h5>
                    <p class="text-muted small mb-3">
                        <strong class="text-danger font-monospace" id="restoreFilename"></strong> ফাইল থেকে ডাটাবেজ ও মিডিয়া ফাইল প্রতিস্থাপিত হবে।
                    </p>
                    <div class="p-3 bg-light rounded-3 text-start small text-muted border">
                        <i class="fa-solid fa-shield-check text-success me-1.5"></i> <strong>অটোমেটিক সেফটি স্ন্যাপশট:</strong> রিস্টোর শুরু হওয়ার পূর্বে বর্তমান ডাটার একটি স্বয়ংক্রিয় ব্যাকআপ তৈরি হবে, যাতে যেকোনো প্রয়োজনে পূর্বাবস্থায় ফিরে যাওয়া যায়।
                    </div>
                </div>
                <div class="modal-footer bg-light py-2.5 px-4 rounded-bottom-4 justify-content-center gap-2">
                    <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-bs-dismiss="modal">না, বাতিল করুন</button>
                    <button type="submit" class="btn btn-sm btn-danger rounded-pill px-4 fw-bold">
                        <i class="fa-solid fa-check me-1"></i> হ্যাঁ, রিস্টোর নিশ্চিত করুন
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- MODAL 5: AUTOMATED BACKUP, TELEGRAM & CLOUD SETTINGS MODAL                 --}}
{{-- ========================================================================= --}}
<div class="modal fade" id="backupSettingsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header bg-dark text-white py-3 px-4 rounded-top-4">
                <h6 class="modal-title fw-bold text-white mb-0 d-flex align-items-center gap-2">
                    <i class="fa-solid fa-gear text-warning"></i>
                    <span>স্বয়ংক্রিয় ব্যাকআপ, টেলিগ্রাম ও ক্লাউড সিঙ্ক সেটিংস</span>
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.backup.settings') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    
                    {{-- Nav Tabs --}}
                    <ul class="nav nav-pills nav-fill mb-3 p-1 bg-light rounded-3 gap-1" id="settingsTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active py-2 rounded-2 fw-bold small" id="auto-tab" data-bs-toggle="pill" data-bs-target="#tab-auto" type="button" role="tab">
                                <i class="fa-solid fa-clock-rotate-left me-1"></i> অটো শিডিউলিং
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link py-2 rounded-2 fw-bold small" id="telegram-tab" data-bs-toggle="pill" data-bs-target="#tab-telegram" type="button" role="tab">
                                <i class="fa-brands fa-telegram me-1 text-primary"></i> টেলিগ্রাম অ্যালার্ট
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link py-2 rounded-2 fw-bold small" id="cloud-tab" data-bs-toggle="pill" data-bs-target="#tab-cloud" type="button" role="tab">
                                <i class="fa-solid fa-cloud-arrow-up me-1 text-info"></i> অফসাইট ক্লাউড
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content pt-2" id="settingsTabContent">
                        
                        {{-- Tab 1: Auto Scheduling --}}
                        <div class="tab-pane fade show active" id="tab-auto" role="tabpanel">
                            <div class="form-check form-switch mb-3 p-3 bg-light rounded-3 border">
                                <input class="form-check-input ms-0 me-2" type="checkbox" id="autoBackupSwitch" name="auto_backup_enabled" value="1" {{ !empty($settings['auto_backup_enabled']) ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold text-dark small" for="autoBackupSwitch">স্বয়ংক্রিয় শিডিউল ব্যাকআপ সক্রিয় রাখুন</label>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-dark">ব্যাকআপের পুনরাবৃত্তি (Frequency):</label>
                                    <select name="backup_frequency" class="form-select form-select-sm fw-semibold">
                                        <option value="daily" {{ ($settings['backup_frequency'] ?? 'daily') === 'daily' ? 'selected' : '' }}>দৈনিক (Daily — রাত ১২টায়)</option>
                                        <option value="weekly" {{ ($settings['backup_frequency'] ?? '') === 'weekly' ? 'selected' : '' }}>সাপ্তাহিক (Weekly — শুক্রবার)</option>
                                        <option value="monthly" {{ ($settings['backup_frequency'] ?? '') === 'monthly' ? 'selected' : '' }}>মাসিক (Monthly)</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-dark">রিটেনশন লিমিট (সর্বোচ্চ কতটি ফাইল থাকবে):</label>
                                    <input type="number" name="retention_days" class="form-control form-control-sm font-monospace fw-bold" min="1" max="100" value="{{ $settings['retention_days'] ?? $retentionLimit }}">
                                </div>
                            </div>

                            <div class="mb-2">
                                <label class="form-label small fw-bold text-dark">স্বয়ংক্রিয় ব্যাকআপ রিসিভ ইমেইল:</label>
                                <input type="email" name="backup_email" class="form-control form-control-sm font-monospace" placeholder="backup@ideaabd.com" value="{{ $settings['backup_email'] ?? config('mail.from.address', 'adideabd@gmail.com') }}">
                            </div>
                        </div>

                        {{-- Tab 2: Telegram Alerts --}}
                        <div class="tab-pane fade" id="tab-telegram" role="tabpanel">
                            <div class="form-check form-switch mb-3 p-3 bg-light rounded-3 border">
                                <input class="form-check-input ms-0 me-2" type="checkbox" id="telegramAlertsSwitch" name="telegram_alerts_enabled" value="1" {{ !empty($settings['telegram_alerts_enabled']) ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold text-dark small" for="telegramAlertsSwitch">ব্যাকআপ তৈরি ও অ্যালার্টের তাৎক্ষণিক টেলিগ্রাম মেসেজ নোটিফিকেশন</label>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-dark">টেলিগ্রাম বট টোকেন (Telegram Bot Token):</label>
                                <input type="text" name="telegram_bot_token" id="telegramBotTokenInput" class="form-control form-control-sm font-monospace" placeholder="123456789:ABCdefGhIJKlmNoPQRsTUVwxyZ" value="{{ $settings['telegram_bot_token'] ?? '' }}">
                                <small class="text-muted font-monospace" style="font-size: 11px;">BotFather থেকে পাওয়া বট টোকেন</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-dark">টেলিগ্রাম চ্যাট / চ্যানেল আইডি (Chat ID):</label>
                                <input type="text" name="telegram_chat_id" id="telegramChatIdInput" class="form-control form-control-sm font-monospace" placeholder="-1001234567890 অথবা 987654321" value="{{ $settings['telegram_chat_id'] ?? '' }}">
                            </div>

                            <div class="d-flex justify-content-end">
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold" onclick="testTelegramNotification()">
                                    <i class="fa-solid fa-paper-plane me-1"></i> টেস্ট টেলিগ্রাম মেসেজ পাঠান
                                </button>
                            </div>
                        </div>

                        {{-- Tab 3: Offsite Cloud Sync --}}
                        <div class="tab-pane fade" id="tab-cloud" role="tabpanel">
                            <div class="p-3 bg-light rounded-3 border mb-3">
                                <h6 class="fw-bold text-dark small mb-1"><i class="fa-solid fa-cloud-arrow-up text-primary me-1"></i> অফসাইট ক্লাউড স্টোরেজ সিঙ্ক</h6>
                                <p class="text-muted small mb-0 font-monospace" style="font-size: 11px;">প্রতিবার ব্যাকআপ তৈরি হওয়ার পর স্বয়ংক্রিয়ভাবে ক্লাউড ড্রাইভ বা অফসাইট স্টোরেজে কপি সংরক্ষিত হবে।</p>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-dark">ক্লাউড ড্রাইভ টাইপ:</label>
                                <select name="offsite_cloud_driver" class="form-select form-select-sm fw-semibold">
                                    <option value="none" {{ ($settings['offsite_cloud_driver'] ?? 'none') === 'none' ? 'selected' : '' }}>নিষ্ক্রিয় (Local Disk Only)</option>
                                    <option value="s3" {{ ($settings['offsite_cloud_driver'] ?? '') === 's3' ? 'selected' : '' }}>Amazon AWS S3 / Cloudflare R2</option>
                                    <option value="gdrive" {{ ($settings['offsite_cloud_driver'] ?? '') === 'gdrive' ? 'selected' : '' }}>Google Drive</option>
                                    <option value="ftp" {{ ($settings['offsite_cloud_driver'] ?? '') === 'ftp' ? 'selected' : '' }}>Remote FTP / SFTP Server</option>
                                </select>
                            </div>

                            <div class="mb-2">
                                <label class="form-label small fw-bold text-dark">রিমোট ব্যাকআপ ফোল্ডার পাথ:</label>
                                <input type="text" name="offsite_cloud_path" class="form-control form-control-sm font-monospace" placeholder="/backups/ideaabd" value="{{ $settings['offsite_cloud_path'] ?? '/backups/ideaabd' }}">
                            </div>
                        </div>

                    </div>

                </div>
                <div class="modal-footer bg-light py-2.5 px-4 rounded-bottom-4 d-flex justify-content-between">
                    <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-bs-dismiss="modal">বাতিল</button>
                    <button type="submit" class="btn-backup-gradient btn-gradient-sky px-4">
                        <i class="fa-solid fa-floppy-disk"></i>
                        <span>সেটিংস সংরক্ষণ করুন</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- MODAL 6: DATABASE DIFF & ANALYTICS MODAL                                   --}}
{{-- ========================================================================= --}}
<div class="modal fade" id="diffModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header bg-dark text-white py-3 px-4 rounded-top-4">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle bg-info-subtle text-info p-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                        <i class="fa-solid fa-code-compare"></i>
                    </div>
                    <div>
                        <h6 class="modal-title fw-bold text-white mb-0">ডাটাবেজ ও ব্যাকআপ তুলনা (Diff & Analytics)</h6>
                        <small class="text-white-50 font-monospace" style="font-size: 11px;">ফাইল: <span id="diffModalFilename" class="text-warning"></span></small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            
            <div class="modal-body p-4" id="diffModalBody">
                <div class="text-center py-5 text-muted">
                    <div class="spinner-border text-info mb-2" role="status"></div>
                    <div class="fw-semibold">লাইভ ডাটাবেজের সাথে ব্যাকআপ ফাইল তুলনা করা হচ্ছে...</div>
                </div>
            </div>

            <div class="modal-footer bg-light py-2.5 px-4 rounded-bottom-4 d-flex justify-content-between">
                <button type="button" class="btn btn-sm btn-secondary rounded-pill px-4 fw-bold" data-bs-dismiss="modal">বন্ধ করুন</button>
                <button type="button" class="btn-backup-gradient btn-gradient-amber px-4" id="btnDiffToSelective" onclick="openSelectiveFromDiff()">
                    <i class="fa-solid fa-list-check"></i>
                    <span>নির্বাচিত টেবিল রিস্টোর করুন</span>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- MODAL 7: SAFE DRY-RUN & SANDBOX SIMULATION MODAL                           --}}
{{-- ========================================================================= --}}
<div class="modal fade" id="dryRunModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header bg-dark text-white py-3 px-4 rounded-top-4">
                <h6 class="modal-title fw-bold text-white mb-0 d-flex align-items-center gap-2">
                    <i class="fa-solid fa-flask-vial text-warning"></i>
                    <span>নিরাপদ স্যান্ডবক্স ড্রাই-রান সিমুলেশন</span>
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4" id="dryRunModalBody">
                <div class="text-center py-5">
                    <div class="spinner-grow text-primary mb-3" style="width: 3rem; height: 3rem;" role="status"></div>
                    <h6 class="fw-bold text-dark font-monospace mb-1">স্যান্ডবক্সে রিস্টোর পরীক্ষা চলছে...</h6>
                    <p class="text-muted small mb-0">SQL সিনট্যাক্স, ফরেন-কী ও টেবিল স্ট্রাকচার পরীক্ষা করা হচ্ছে (লাইভ ডাটা সম্পূর্ণ অপরিবর্তিত থাকবে)।</p>
                </div>
            </div>
            <div class="modal-footer bg-light py-2.5 px-4 rounded-bottom-4">
                <button type="button" class="btn btn-sm btn-secondary rounded-pill px-4 fw-bold ms-auto" data-bs-dismiss="modal">বন্ধ করুন</button>
            </div>
        </div>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- MODAL 8: SELECTIVE TABLE RESTORE MODAL                                     --}}
{{-- ========================================================================= --}}
<div class="modal fade" id="selectiveRestoreModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header bg-dark text-white py-3 px-4 rounded-top-4">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle bg-warning-subtle text-warning p-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                        <i class="fa-solid fa-list-check"></i>
                    </div>
                    <div>
                        <h6 class="modal-title fw-bold text-white mb-0">সিলেক্টিভ টেবিল রিস্টোর (Selective Restore)</h6>
                        <small class="text-white-50 font-monospace" style="font-size: 11px;">ফাইল: <span id="selectiveModalFilename" class="text-warning"></span></small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-warning d-flex align-items-center gap-2 rounded-3 py-2 px-3 small mb-3 border-0 border-start border-4 border-warning">
                    <i class="fa-solid fa-shield-halved fs-5"></i>
                    <div>শুধুমাত্র নির্বাচিত টেবিলগুলো ব্যাকআপ থেকে ওভাররাইট হবে। অন্য কোনো টেবিলের ডাটা পরিবর্তন হবে না।</div>
                </div>

                {{-- Table Filter & Quick Controls --}}
                <div class="d-flex align-items-center justify-content-between mb-3 gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-xs btn-outline-primary rounded-pill px-2.5 py-1 font-monospace fw-bold" onclick="toggleAllSelectiveTables(true)">সব নির্বাচন</button>
                        <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2.5 py-1 font-monospace fw-bold" onclick="toggleAllSelectiveTables(false)">সব বাতিল</button>
                        <span class="badge bg-light text-dark border rounded-pill px-2.5 py-1 font-monospace small" id="selectiveSelectedBadge">০ টি নির্বাচিত</span>
                    </div>
                    <input type="search" id="selectiveSearchInput" class="form-control form-control-sm font-monospace" style="max-width: 200px;" placeholder="টেবিল খুঁজুন...">
                </div>

                <div class="border rounded-3 p-3 bg-light" style="max-height: 280px; overflow-y: auto;">
                    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-2" id="selectiveTablesList">
                        @foreach($tables as $t)
                            <div class="col selective-tbl-col" data-tbl="{{ strtolower($t['name']) }}">
                                <div class="form-check p-2 bg-white rounded-2 border shadow-xs h-100 d-flex align-items-center">
                                    <input class="form-check-input ms-0 me-2 selective-tbl-cb" type="checkbox" value="{{ $t['name'] }}" id="tbl_cb_{{ $loop->index }}" onchange="updateSelectiveCount()">
                                    <label class="form-check-label small font-monospace text-dark fw-semibold text-truncate" for="tbl_cb_{{ $loop->index }}" title="{{ $t['name'] }}">
                                        {{ $t['name'] }}
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light py-2.5 px-4 rounded-bottom-4 d-flex justify-content-between">
                <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-bs-dismiss="modal">বাতিল</button>
                <button type="button" class="btn-backup-gradient btn-gradient-amber px-4" id="btnExecuteSelectiveRestore" onclick="submitSelectiveRestore()">
                    <i class="fa-solid fa-rotate-left"></i>
                    <span>নির্বাচিত টেবিল রিস্টোর করুন</span>
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/admin-backup.js') }}?v={{ @filemtime(public_path('js/admin-backup.js')) ?: 3 }}"></script>
@endpush
@endsection
