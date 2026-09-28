@extends('layouts.admin')

@section('title', 'Master Backup & Disaster Recovery — Idea Publication')
@section('heading', 'Master Backup & Disaster Recovery Hub')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">Dashboard</a></li>
    <li class="breadcrumb-item active">Master Backup Hub</li>
@endsection

@section('actions')
    <div class="d-flex flex-wrap align-items-center gap-2 header-actions-wrapper">
        {{-- 1. Upload Backup Button (Rose Gradient) --}}
        <button type="button" class="btn-backup-gradient btn-gradient-rose" onclick="document.getElementById('backupFileInput').click()" title="Upload external backup archive">
            <i class="fa-solid fa-cloud-arrow-up"></i>
            <span>Upload Backup</span>
        </button>

        {{-- 2. Anonymized Developer Dump (Amber Gradient) --}}
        <button type="button" class="btn-backup-gradient btn-gradient-amber" onclick="generateAnonymizedDump()" title="Export sanitized database dump with masked customer PII for developers">
            <i class="fa-solid fa-user-shield"></i>
            <span>Anonymized Dump</span>
        </button>

        {{-- 3. 1-Click Integrity Health Check (Purple Gradient) --}}
        <form action="{{ route('admin.backup.integrity') }}" method="POST" class="m-0 d-inline-block">
            @csrf
            <button type="submit" class="btn-backup-gradient btn-gradient-purple" title="Run database integrity & consistency diagnostics">
                <i class="fa-solid fa-stethoscope"></i>
                <span>Integrity Scan</span>
            </button>
        </form>

        {{-- 4. 1-Click Database Table Optimizer (Sky Gradient) --}}
        <form action="{{ route('admin.backup.optimize') }}" method="POST" class="m-0 d-inline-block"
              data-confirm="Are you sure you want to optimize and vacuum all database tables and indexes?"
              data-confirm-title="Database Optimization"
              data-confirm-icon="info"
              data-confirm-btn="<i class='fa-solid fa-wand-magic-sparkles me-1'></i> Yes, Optimize Now">
            @csrf
            <button type="submit" class="btn-backup-gradient btn-gradient-sky" title="Optimize database table indexes & disk storage">
                <i class="fa-solid fa-wand-magic-sparkles"></i>
                <span>Optimize DB</span>
            </button>
        </form>

        {{-- 5. 1-Click Complete Data & Media Images Backup (.ZIP) (Emerald Gradient) --}}
        <button type="button" class="btn-backup-gradient btn-gradient-emerald" onclick="triggerLiveBackup('data_media', 'Data & Media Master Backup (.ZIP)')" title="Complete database + all uploaded book covers, author avatars, and digital media">
            <i class="fa-solid fa-box-archive"></i>
            <span>Data & Media Backup</span>
        </button>

        {{-- 6. 1-Click Full System Backup (.ZIP) (Indigo Gradient) --}}
        <button type="button" class="btn-backup-gradient btn-gradient-indigo" onclick="triggerLiveBackup('full_system', 'Full System & Source Code Backup')" title="Full system & database master archive">
            <i class="fa-solid fa-file-zipper"></i>
            <span>Full System Backup</span>
        </button>
    </div>
@endsection

@section('content')
<link rel="stylesheet" href="{{ asset('css/admin-backup.css') }}?v={{ @filemtime(public_path('css/admin-backup.css')) ?: 4 }}">

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    window.STORAGE_TIMELINE_DATA = @json($storageTimeline ?? []);
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
        restore: "{{ url('admin/backup/restore') }}",
        destroyBase: "{{ url('admin/backup') }}"
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

    <!-- 0. Disaster Recovery Health Audit & Storage Timeline Hero Section -->
    <div class="row g-3">
        {{-- Left: Smart Disaster Recovery & Health Score Card --}}
        <div class="col-12 col-xl-5">
            <div class="health-score-card h-100 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-white bg-opacity-20 text-white font-monospace px-2.5 py-1 rounded-pill small">
                                <i class="fa-solid fa-shield-halved text-success me-1"></i> System Health Audit
                            </span>
                        </div>
                        <div class="cron-heartbeat-pill">
                            <span class="pulse-online-badge"></span>
                            <span>Daily 00:00 Auto-Schedule</span>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-3.5 my-2">
                        <div class="health-score-dial" style="--health-deg: {{ ( ($healthAudit['score'] ?? 95) / 100) * 360 }}deg;">
                            <div class="health-score-dial-inner">
                                <div class="health-score-num">{{ $healthAudit['score'] ?? 95 }}</div>
                                <span class="health-score-pct">Score / 100</span>
                            </div>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <h5 class="fw-bold text-white mb-0">{{ $healthAudit['status'] ?? 'Optimal & Resilient' }}</h5>
                                <span class="badge font-monospace px-2 py-0.5 rounded-pill text-white" style="background:#10b981; font-size: 0.70rem;">
                                    Grade {{ $healthAudit['grade'] ?? 'A+' }}
                                </span>
                            </div>
                            <p class="text-white-50 small mb-0 font-monospace" style="font-size: 11px;">
                                @if(!empty($healthAudit['is_overdue']))
                                    <span class="text-warning"><i class="fa-solid fa-circle-exclamation me-1"></i> Last backup was {{ $healthAudit['last_backup_human'] }}. New backup recommended!</span>
                                @else
                                    <span class="text-emerald-300"><i class="fa-solid fa-circle-check me-1"></i> Continuous protection active. Last backup: {{ $healthAudit['last_backup_human'] ?? 'Recently' }}.</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Metric Checklist --}}
                <div class="pt-3 mt-3 border-top border-white border-opacity-10 d-flex flex-wrap align-items-center justify-content-between gap-2 font-monospace small">
                    <div class="text-white-50">
                        <i class="fa-solid fa-hard-drive me-1 text-info"></i> Free Disk: <strong class="text-white">{{ $healthAudit['free_disk'] ?? 'Adequate' }}</strong>
                    </div>
                    <div class="text-white-50">
                        <i class="fa-solid fa-database me-1 text-warning"></i> DB Footprint: <strong class="text-white">{{ $formattedDbSize }}</strong>
                    </div>
                    <div class="text-white-50">
                        <i class="fa-solid fa-clock-rotate-left me-1 text-success"></i> Auto-Retention: <strong class="text-white">Active</strong>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right: Storage Growth & Backup Volume Analytics Timeline --}}
        <div class="col-12 col-xl-7">
            <div class="storage-chart-card h-100 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div>
                        <h6 class="fw-bold text-dark mb-0" style="font-size: 0.96rem;">
                            <i class="fa-solid fa-chart-line text-primary me-1.5"></i> Storage Growth & Backup Volume Timeline
                        </h6>
                        <small class="text-muted font-monospace" style="font-size: 11px;">Monthly archive size & volume trend history</small>
                    </div>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace px-2.5 py-1 rounded-pill small">
                        {{ $formattedTotalBackupSize }} Total Allocated
                    </span>
                </div>

                <div class="chart-container-wrap my-1">
                    <canvas id="storageGrowthChartCanvas"></canvas>
                </div>

                <div class="d-flex align-items-center justify-content-between pt-2 border-top small text-muted font-monospace" style="font-size: 11px;">
                    <span><i class="fa-solid fa-box-archive text-primary me-1"></i> Master ZIPs: <strong>{{ $categoryCounts['master_zip'] ?? 0 }}</strong></span>
                    <span><i class="fa-solid fa-file-code text-info me-1"></i> SQL Dumps: <strong>{{ $categoryCounts['sql_dump'] ?? 0 }}</strong></span>
                    <span><i class="fa-solid fa-shield-heart text-success me-1"></i> Snapshots: <strong>{{ $categoryCounts['safety'] ?? 0 }}</strong></span>
                    <span><i class="fa-solid fa-user-shield text-warning me-1"></i> Masked: <strong>{{ $categoryCounts['anonymized'] ?? 0 }}</strong></span>
                </div>
            </div>
        </div>
    </div>

    <!-- 1. Symmetrical & Vibrant 4-Card Diagnostic Grid -->
    <div class="row row-cols-2 row-cols-md-2 row-cols-xl-4 g-2 g-md-3">
        
        {{-- Card 1: Connected Database Engine --}}
        <div class="col">
            <div class="backup-kpi-card kpi-indigo">
                <div class="d-flex align-items-start justify-content-between mb-2">
                    <div class="min-w-0 pe-2">
                        <span class="text-muted small fw-semibold text-uppercase font-monospace d-block mb-1" style="font-size: 0.70rem; letter-spacing: 0.5px;">Connected Database</span>
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
                        <span class="pulse-online-badge"></span> Live Connection
                    </span>
                </div>
            </div>
        </div>

        {{-- Card 2: Database Volume & Rows --}}
        <div class="col">
            <div class="backup-kpi-card kpi-emerald">
                <div class="d-flex align-items-start justify-content-between mb-2">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase font-monospace d-block mb-1" style="font-size: 0.70rem; letter-spacing: 0.5px;">Database Size</span>
                        <h4 class="fw-bold text-dark mb-0 font-monospace" style="font-size: 1.25rem;">{{ $formattedDbSize }}</h4>
                    </div>
                    <div class="kpi-avatar-icon bg-success-subtle text-success">
                        <i class="fa-solid fa-server"></i>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between pt-2.5 border-top">
                    <span class="small text-muted font-monospace" style="font-size: 0.75rem;">
                        <i class="fa-solid fa-table-cells text-muted me-1"></i>{{ count($tables) }} Tables
                    </span>
                    <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace px-2.5 py-0.5 rounded-pill" style="font-size: 0.70rem;">
                        {{ number_format($totalRowsCount) }} Records
                    </span>
                </div>
            </div>
        </div>

        {{-- Card 3: Master Backups Archive --}}
        <div class="col">
            <div class="backup-kpi-card kpi-amber">
                <div class="d-flex align-items-start justify-content-between mb-2">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase font-monospace d-block mb-1" style="font-size: 0.70rem; letter-spacing: 0.5px;">Backup Archives</span>
                        <h4 class="fw-bold text-dark mb-0 font-monospace" style="font-size: 1.25rem;">{{ count($backups) }} Files</h4>
                    </div>
                    <div class="kpi-avatar-icon bg-warning-subtle text-warning">
                        <i class="fa-solid fa-file-zipper"></i>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between pt-2.5 border-top">
                    <span class="small text-muted font-monospace" style="font-size: 0.75rem;">Disk Storage</span>
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
                        <span class="text-muted small fw-semibold text-uppercase font-monospace d-block mb-1" style="font-size: 0.70rem; letter-spacing: 0.5px;">Disaster Protection</span>
                        <h5 class="fw-bold text-success mb-0 d-flex align-items-center gap-1.5" style="font-size: 1.05rem;">
                            <i class="fa-solid fa-shield-halved"></i> 100% Protected
                        </h5>
                    </div>
                    <div class="kpi-avatar-icon bg-info-subtle text-info">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between pt-2.5 border-top">
                    <span class="small text-muted font-monospace" style="font-size: 0.75rem;">Auto Retention</span>
                    <button type="button" class="btn btn-xs btn-outline-info rounded-pill px-2 py-0.5 font-monospace fw-bold" data-bs-toggle="modal" data-bs-target="#backupSettingsModal">
                        <i class="fa-solid fa-gear me-1"></i> Configure
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
                <h6 class="fw-bold text-dark mb-0" style="font-size: 0.96rem;">Upload Backup Archives & Database Dumps</h6>
            </div>
            <span class="badge bg-light text-muted border rounded-pill px-3 py-1 font-monospace small">
                Supported: .ZIP, .SQL, .SQLITE, .GZ (Max 200 MB)
            </span>
        </div>

        <div class="enterprise-dropzone" id="dropZone" onclick="document.getElementById('backupFileInput').click()">
            <input type="file" id="backupFileInput" class="d-none" accept=".zip,.sql,.sqlite,.gz" onchange="handleDynamicUpload(this.files)">
            
            <div id="dropZonePrompt">
                <div class="dropzone-icon-circle">
                    <i class="fa-solid fa-cloud-arrow-up"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1.5" style="font-size: 1.08rem;">
                    Drag & Drop Backup Archive (.ZIP / .SQL) Here
                </h6>
                <p class="text-muted small mb-3" style="font-size: 0.86rem;">
                    Or click the button below to browse files from your computer — auto verified upon ingestion
                </p>
                <button type="button" class="btn-backup-gradient btn-gradient-indigo px-4" onclick="event.stopPropagation(); document.getElementById('backupFileInput').click()">
                    <i class="fa-solid fa-folder-open"></i>
                    <span>Browse Backup File</span>
                </button>
            </div>

            {{-- Live Dynamic Upload Progress Bar --}}
            <div class="d-none py-3" id="uploadProgressSection">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="d-flex align-items-center gap-2">
                        <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                        <span class="small fw-bold text-primary font-monospace" id="uploadFilenameText">Uploading archive...</span>
                    </div>
                    <span class="small fw-bold font-monospace text-dark" id="uploadPercentText">0%</span>
                </div>
                <div class="progress rounded-pill shadow-xs" style="height: 14px; background-color: #e2e8f0;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" id="uploadProgressBar" role="progressbar" style="width: 0%"></div>
                </div>
                <small class="text-muted d-block mt-2 font-monospace" style="font-size: 11px;">Please wait while the backup file is being uploaded and verified on server...</small>
            </div>
        </div>
    </div>

    <!-- 3. Sticky Bulk Actions Toolbar -->
    <div class="bulk-action-bar" id="bulkActionBar">
        <div class="d-flex align-items-center gap-2.5">
            <i class="fa-solid fa-check-double text-warning fs-5"></i>
            <span class="fw-bold font-monospace" id="bulkSelectedCount">0 Selected</span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-sm btn-outline-light rounded-pill px-3 fw-bold" onclick="deleteSelectedBackups()">
                <i class="fa-solid fa-trash-can me-1 text-danger"></i> Delete Selected Files
            </button>
        </div>
    </div>

    <!-- 4. Quick Category Filter Tabs & View Switcher Bar -->
    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-2.5">
        {{-- Category Pills --}}
        <div class="quick-filter-nav">
            <button type="button" class="btn-filter-tab active" data-category="all">
                <i class="fa-solid fa-layer-group"></i> All Archives
                <span class="tab-badge">{{ $categoryCounts['all'] ?? count($backups) }}</span>
            </button>
            <button type="button" class="btn-filter-tab" data-category="master_zip">
                <i class="fa-solid fa-file-zipper text-primary"></i> Master ZIP (DB + Media)
                <span class="tab-badge">{{ $categoryCounts['master_zip'] ?? 0 }}</span>
            </button>
            <button type="button" class="btn-filter-tab" data-category="sql_dump">
                <i class="fa-solid fa-database text-info"></i> SQL Dumps
                <span class="tab-badge">{{ $categoryCounts['sql_dump'] ?? 0 }}</span>
            </button>
            <button type="button" class="btn-filter-tab" data-category="safety">
                <i class="fa-solid fa-shield-heart text-success"></i> Safety Snapshots
                <span class="tab-badge">{{ $categoryCounts['safety'] ?? 0 }}</span>
            </button>
            <button type="button" class="btn-filter-tab" data-category="anonymized">
                <i class="fa-solid fa-user-shield text-warning"></i> Anonymized Dev Dumps
                <span class="tab-badge">{{ $categoryCounts['anonymized'] ?? 0 }}</span>
            </button>
        </div>

        {{-- View Switcher Buttons --}}
        <div class="view-switcher-group ms-auto">
            <button type="button" class="btn-view-toggle active" id="btnViewTable" title="Table View">
                <i class="fa-solid fa-table-list me-1"></i> Table View
            </button>
            <button type="button" class="btn-view-toggle" id="btnViewGrid" title="Card Grid View">
                <i class="fa-solid fa-grip me-1"></i> Grid View
            </button>
        </div>
    </div>

    <!-- 5. Master Backup Archive Records Container -->
    <div class="card bg-white rounded-4 shadow-sm border-0 overflow-hidden">
        
        {{-- Card Header with Live Search & Format Filter --}}
        <div class="card-header bg-white py-3 px-4 border-bottom">
            <div class="row g-2 align-items-center justify-content-between">
                <div class="col-12 col-md-5">
                    <div class="d-flex align-items-center gap-2.5">
                        <div class="rounded-circle bg-primary-subtle text-primary p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                            <i class="fa-solid fa-file-zipper"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold text-dark mb-0" style="font-size: 0.96rem;">Master Backup Archives & Snapshots</h6>
                            <small class="text-muted font-monospace" style="font-size: 11px;">Universal Database Dumps + Uploaded Book Covers & Digital Media</small>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-7">
                    <div class="d-flex flex-wrap align-items-center justify-content-md-end gap-2">
                        {{-- Live Search Filter --}}
                        <div class="input-group input-group-sm" style="max-width: 220px;">
                            <span class="input-group-text bg-light border-end-0 text-muted">
                                <i class="fa-solid fa-magnifying-glass"></i>
                            </span>
                            <input type="search" id="backupSearchInput" class="form-control border-start-0 ps-0 fw-semibold" placeholder="Search archives...">
                        </div>

                        {{-- Format Selector --}}
                        <select id="backupFormatFilter" class="form-select form-select-sm fw-semibold" style="max-width: 140px;">
                            <option value="all">All Formats</option>
                            <option value="zip">Master ZIP</option>
                            <option value="sql">SQL Dump</option>
                            <option value="sqlite">SQLite</option>
                        </select>

                        {{-- Total Badge --}}
                        <span class="badge bg-light text-dark border rounded-pill px-3 py-2 font-monospace small" id="backupCountBadge">
                            {{ count($backups) }} Files
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Table View Container --}}
        <div class="card-body p-0" id="backupTableViewContainer">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="backupsTable">
                    <thead class="table-light small text-uppercase font-monospace text-muted">
                        <tr>
                            <th class="ps-4 py-3" style="width: 40px;">
                                <input type="checkbox" class="form-check-input" id="selectAllBackups">
                            </th>
                            <th class="py-3" style="min-width: 260px;">Archive Filename</th>
                            <th class="py-3" style="width: 150px;">Format / Type</th>
                            <th class="py-3" style="width: 110px;">Size</th>
                            <th class="py-3" style="width: 170px;">Created Date</th>
                            <th class="text-end pe-4 py-3" style="min-width: 280px;">Action Controls</th>
                        </tr>
                    </thead>
                    <tbody id="backupsTableBody">
                        @forelse($backups as $b)
                            @php
                                $categoryKey = $b['category'] ?? ($b['is_master_zip'] ? 'master_zip' : (str_contains(strtolower($b['filename']), 'safety') ? 'safety' : (str_contains(strtolower($b['filename']), 'anonymized') ? 'anonymized' : 'sql_dump')));
                            @endphp
                            <tr class="table-custom-row" id="row-{{ md5($b['filename']) }}" 
                                data-filename="{{ strtolower($b['filename']) }}" 
                                data-ext="{{ strtolower($b['extension']) }}"
                                data-category="{{ $categoryKey }}"
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
                                                {{ $b['is_master_zip'] ? 'Full System Master ZIP (DB + Media)' : 'Standard SQL Database Dump' }}
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
                                    <span class="badge bg-light text-secondary border font-monospace mt-0.5 d-inline-flex align-items-center gap-1" style="font-size: 11px;">
                                        <i class="fa-regular fa-clock text-muted"></i> {{ $b['created_at']->locale('en')->diffForHumans() }}
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-flex align-items-center justify-content-end flex-wrap gap-1.5">
                                        {{-- 1. Full Disaster Restore --}}
                                        <button type="button" class="btn-action-pill btn-action-restore-main" 
                                                onclick="confirmRestore('{{ $b['filename'] }}', {{ $b['is_master_zip'] ? 'true' : 'false' }})" title="Full Disaster Recovery Restore">
                                            <i class="fa-solid fa-rotate-left"></i> Restore
                                        </button>

                                        {{-- 2. Diff & Analytics --}}
                                        <button type="button" class="btn-action-pill btn-action-cyan" onclick="openDiffModal('{{ $b['filename'] }}')" title="Compare Live Database vs Backup Snapshot">
                                            <i class="fa-solid fa-code-compare"></i> Diff
                                        </button>

                                        {{-- 3. Safe Dry-Run Simulation --}}
                                        <button type="button" class="btn-action-pill btn-action-purple" onclick="runDryRunSimulation('{{ $b['filename'] }}')" title="Safe Sandbox Dry-Run Simulation">
                                            <i class="fa-solid fa-flask-vial"></i> Dry-Run
                                        </button>

                                        {{-- 4. Selective Table Restore --}}
                                        <button type="button" class="btn-action-pill btn-action-amber" onclick="openSelectiveRestoreModal('{{ $b['filename'] }}')" title="Restore specific chosen tables only">
                                            <i class="fa-solid fa-list-check"></i> Selective
                                        </button>

                                        {{-- 5. Inspect ZIP Preview --}}
                                        @if($b['is_master_zip'])
                                            <button type="button" class="btn-action-pill btn-action-sky" onclick="inspectZipArchive('{{ $b['filename'] }}')" title="Inspect Archive Content & Manifest">
                                                <i class="fa-solid fa-eye"></i> Preview
                                            </button>
                                        @endif

                                        {{-- 6. Download --}}
                                        <a href="{{ route('admin.backup.download', $b['filename']) }}" class="btn-action-pill btn-action-indigo" title="Download backup archive">
                                            <i class="fa-solid fa-download"></i>
                                        </a>

                                        {{-- 7. Email Dispatch --}}
                                        <button type="button" class="btn-action-pill btn-action-emerald" onclick="openEmailModal('{{ $b['filename'] }}')" title="Send backup to admin email">
                                            <i class="fa-solid fa-paper-plane"></i>
                                        </button>

                                        {{-- 8. Delete --}}
                                        <button type="button" class="btn-action-pill btn-action-rose" onclick="deleteSingleBackup('{{ $b['filename'] }}')" title="Delete backup archive">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr id="emptyRow">
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <div class="p-3 text-center">
                                        <i class="fa-solid fa-file-zipper fs-1 text-secondary opacity-40 mb-3 d-block"></i>
                                        <h6 class="fw-bold text-dark">No Backup Archives Found</h6>
                                        <p class="small text-muted mb-3">Click the buttons above to generate your first Master Backup (.ZIP) archive.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                        <tr id="emptySearchRow" style="display: none;">
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="fa-solid fa-magnifying-glass me-1"></i> No matching backup archives found for your search query.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Grid Cards View Container --}}
        <div class="card-body p-4" id="backupGridViewContainer" style="display: none;">
            <div class="backup-grid-view">
                @forelse($backups as $b)
                    @php
                        $categoryKey = $b['category'] ?? ($b['is_master_zip'] ? 'master_zip' : (str_contains(strtolower($b['filename']), 'safety') ? 'safety' : (str_contains(strtolower($b['filename']), 'anonymized') ? 'anonymized' : 'sql_dump')));
                        $cardClass = $b['is_master_zip'] ? 'card-master-zip' : ($categoryKey === 'safety' ? 'card-safety' : ($categoryKey === 'anonymized' ? 'card-anonymized' : 'card-sql'));
                    @endphp
                    <div class="backup-file-card {{ $cardClass }}"
                         data-filename="{{ strtolower($b['filename']) }}"
                         data-ext="{{ strtolower($b['extension']) }}"
                         data-category="{{ $categoryKey }}"
                         data-date="{{ $b['created_at']->format('d M, Y') }}">
                        <div>
                            <div class="d-flex align-items-start justify-content-between mb-2">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-3 {{ $b['is_master_zip'] ? 'bg-primary text-white' : 'bg-light border text-muted' }} p-2 d-flex align-items-center justify-content-center" style="width:34px;height:34px;">
                                        @if($b['is_master_zip'])
                                            <i class="fa-solid fa-file-zipper"></i>
                                        @elseif($b['extension'] === 'sqlite')
                                            <i class="fa-solid fa-database text-success"></i>
                                        @else
                                            <i class="fa-solid fa-file-code"></i>
                                        @endif
                                    </div>
                                    <span class="badge {{ $b['is_master_zip'] ? 'bg-primary-subtle text-primary border border-primary-subtle' : 'bg-light text-dark border' }} rounded-pill font-monospace" style="font-size: 0.68rem;">
                                        {{ $b['is_master_zip'] ? 'MASTER .ZIP' : strtoupper($b['extension']) }}
                                    </span>
                                </div>
                                <span class="fw-bold text-dark font-monospace" style="font-size: 0.88rem;">{{ $b['size'] }}</span>
                            </div>

                            <h6 class="fw-bold text-dark font-monospace text-truncate mb-1" title="{{ $b['filename'] }}" style="font-size: 0.90rem;">
                                {{ $b['filename'] }}
                            </h6>
                            <div class="d-flex align-items-center justify-content-between text-muted font-monospace small mb-3" style="font-size: 11px;">
                                <span><i class="fa-solid fa-calendar-day me-1"></i> {{ $b['created_at']->format('d M, Y') }}</span>
                                <span class="badge bg-light text-secondary border"><i class="fa-solid fa-clock me-1"></i> {{ $b['created_at']->locale('en')->diffForHumans() }}</span>
                            </div>
                        </div>

                        <div class="pt-2 border-top d-flex align-items-center justify-content-between flex-wrap gap-1.5">
                            <div class="d-flex align-items-center gap-1">
                                <button type="button" class="btn-action-pill btn-action-cyan" onclick="openDiffModal('{{ $b['filename'] }}')" title="Diff Snapshot">
                                    <i class="fa-solid fa-code-compare"></i>
                                </button>
                                <button type="button" class="btn-action-pill btn-action-purple" onclick="runDryRunSimulation('{{ $b['filename'] }}')" title="Dry-Run Sandbox">
                                    <i class="fa-solid fa-flask-vial"></i>
                                </button>
                                <button type="button" class="btn-action-pill btn-action-amber" onclick="openSelectiveRestoreModal('{{ $b['filename'] }}')" title="Selective Restore">
                                    <i class="fa-solid fa-list-check"></i>
                                </button>
                                @if($b['is_master_zip'])
                                    <button type="button" class="btn-action-pill btn-action-sky" onclick="inspectZipArchive('{{ $b['filename'] }}')" title="Preview ZIP">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                @endif
                            </div>
                            <div class="d-flex align-items-center gap-1">
                                <button type="button" class="btn-action-pill btn-action-restore-main" onclick="confirmRestore('{{ $b['filename'] }}', {{ $b['is_master_zip'] ? 'true' : 'false' }})" title="Restore">
                                    <i class="fa-solid fa-rotate-left"></i>
                                </button>
                                <a href="{{ route('admin.backup.download', $b['filename']) }}" class="btn-action-pill btn-action-indigo" title="Download">
                                    <i class="fa-solid fa-download"></i>
                                </a>
                                <button type="button" class="btn-action-pill btn-action-emerald" onclick="openEmailModal('{{ $b['filename'] }}')" title="Email">
                                    <i class="fa-solid fa-paper-plane"></i>
                                </button>
                                <button type="button" class="btn-action-pill btn-action-rose" onclick="deleteSingleBackup('{{ $b['filename'] }}')" title="Delete">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center py-5 text-muted">
                        <i class="fa-solid fa-file-zipper fs-1 text-secondary opacity-40 mb-3 d-block"></i>
                        <h6 class="fw-bold text-dark">No Backup Archives Found</h6>
                    </div>
                @endforelse
            </div>
            <div id="emptyGridState" class="text-center py-5 text-muted" style="display: none;">
                <i class="fa-solid fa-magnifying-glass fs-2 text-secondary opacity-50 mb-2 d-block"></i>
                <h6 class="fw-bold text-dark mb-1">No Matching Backup Archives</h6>
                <p class="small text-muted mb-0">Try clearing the search query or switching category filter tab.</p>
            </div>
        </div>
    </div>

    <!-- 5. Database Tables & Live Schema Explorer -->
    @if(!empty($tables))
        <div class="card bg-white rounded-4 shadow-sm border-0 overflow-hidden">
            <div class="card-header bg-white d-flex flex-wrap align-items-center justify-content-between gap-2 py-3 px-4 border-bottom">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="rounded-circle bg-info-subtle text-info d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                        <i class="fa-solid fa-table-cells fs-6"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <h6 class="fw-bold text-dark mb-0" style="font-size: 0.96rem;">Database Tables & Schema Explorer</h6>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill font-monospace" id="schemaTableCountBadge" style="font-size: 0.70rem;">{{ count($tables) }} Tables</span>
                        </div>
                        <small class="text-muted font-monospace" style="font-size: 11px;">{{ number_format($totalRowsCount) }} Total Records Across Schema • {{ $formattedDbSize }} Volume</small>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2 ms-auto">
                    {{-- Live Schema Search Input --}}
                    <div class="input-group input-group-sm" style="max-width: 220px;">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                        <input type="search" id="schemaSearchInput" class="form-control border-start-0 ps-0 fw-semibold font-monospace" placeholder="Filter tables...">
                    </div>

                    <button class="btn btn-sm btn-light border rounded-pill px-3 fw-semibold font-monospace" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTables" aria-expanded="true">
                        <i class="fa-solid fa-chevron-down me-1"></i> Toggle Schema
                    </button>
                </div>
            </div>

            <div class="collapse show" id="collapseTables">
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 480px; overflow-y: auto;">
                        <table class="table table-hover align-middle mb-0 small" id="schemaTablesTable">
                            <thead class="table-light sticky-top font-monospace text-uppercase text-muted" style="font-size: 0.72rem; z-index: 10;">
                                <tr>
                                    <th class="ps-4 py-2.5" style="width: 45%;">TABLE NAME</th>
                                    <th class="py-2.5" style="width: 25%;">RECORD COUNT</th>
                                    <th class="py-2.5" style="width: 15%;">STORAGE ALLOCATION</th>
                                    <th class="text-end pe-4 py-2.5" style="width: 15%;">STATUS</th>
                                </tr>
                            </thead>
                            <tbody id="schemaTablesBody">
                                @foreach($tables as $tbl)
                                    <tr class="schema-table-row" data-tablename="{{ strtolower($tbl['name']) }}">
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="fa-solid fa-table text-muted opacity-50"></i>
                                                <span class="font-monospace text-dark fw-bold">{{ $tbl['name'] }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border font-monospace px-2.5 py-1 rounded-pill">
                                                <i class="fa-solid fa-database text-info me-1"></i>{{ number_format($tbl['rows']) }} rows
                                            </span>
                                        </td>
                                        <td>
                                            <span class="font-monospace text-muted fw-semibold">{{ $tbl['size'] }}</span>
                                        </td>
                                        <td class="text-end pe-4">
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill font-monospace px-2 py-0.5" style="font-size: 0.68rem;">
                                                <i class="fa-solid fa-check me-0.5"></i> Healthy
                                            </span>
                                        </td>
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
                    <span>Creating System Backup Archive</span>
                </h6>
            </div>
            <div class="modal-body p-4 text-center">
                <div class="rounded-circle bg-primary-subtle text-primary p-3 d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px;">
                    <i class="fa-solid fa-gears fs-2 animate-spin"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1 font-monospace" id="backupProgressStatusText">Synthesizing database & media files...</h6>
                <p class="text-muted small mb-3">Please wait a few moments while the archive is compressed and stored securely.</p>
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
                    <span>Master ZIP Archive Preview: <span id="inspectFilename" class="font-monospace text-info"></span></span>
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="inspectBody">
                <div class="text-center py-4 text-muted">
                    <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div> Scanning archive contents...
                </div>
            </div>
            <div class="modal-footer bg-light py-2.5 px-4 rounded-bottom-4">
                <button type="button" class="btn btn-sm btn-secondary rounded-pill px-4 fw-bold" data-bs-dismiss="modal">Close</button>
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
                    <span>Dispatch Backup Archive to Email</span>
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" id="emailModalFilename">
                <div class="mb-3">
                    <label class="form-label small fw-bold text-dark">Target Backup File:</label>
                    <div class="p-2.5 bg-light rounded-3 font-monospace text-primary fw-bold small border" id="emailModalFilenameDisplay"></div>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold text-dark">Recipient Email Address:</label>
                    <input type="email" id="emailRecipientInput" class="form-control font-monospace" placeholder="admin@example.com" value="{{ config('mail.from.address', 'adideabd@gmail.com') }}">
                    <small class="text-muted d-block mt-1 font-monospace" style="font-size: 11px;">The backup archive will be securely delivered as an attachment to the specified recipient.</small>
                </div>
            </div>
            <div class="modal-footer bg-light py-2.5 px-4 rounded-bottom-4 d-flex justify-content-between">
                <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn-backup-gradient btn-gradient-amber px-4" id="btnSubmitEmail" onclick="submitEmailDispatch()">
                    <i class="fa-solid fa-paper-plane"></i>
                    <span>Send via Email</span>
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
                    <span>System Disaster Recovery Warning</span>
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="restoreForm" method="POST">
                @csrf
                <div class="modal-body p-4 text-center">
                    <div class="rounded-circle bg-danger-subtle text-danger p-3 d-inline-flex align-items-center justify-content-center mb-3 shadow-xs" style="width: 60px; height: 60px;">
                        <i class="fa-solid fa-rotate-left fs-2"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-2">Are you sure you want to restore the system?</h5>
                    <p class="text-muted small mb-3">
                        The live database and media files will be restored from <strong class="text-danger font-monospace" id="restoreFilename"></strong>.
                    </p>
                    <div class="p-3 bg-light rounded-3 text-start small text-muted border">
                        <i class="fa-solid fa-shield-check text-success me-1.5"></i> <strong>Automatic Safety Snapshot:</strong> Before restoring, an automated pre-restore backup snapshot is created to ensure zero data loss.
                    </div>
                </div>
                <div class="modal-footer bg-light py-2.5 px-4 rounded-bottom-4 justify-content-center gap-2">
                    <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-danger rounded-pill px-4 fw-bold">
                        <i class="fa-solid fa-check me-1"></i> Yes, Confirm Restore
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
                    <span>Automated Backup, Telegram & Cloud Sync Settings</span>
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
                                <i class="fa-solid fa-clock-rotate-left me-1"></i> Auto Scheduling
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link py-2 rounded-2 fw-bold small" id="telegram-tab" data-bs-toggle="pill" data-bs-target="#tab-telegram" type="button" role="tab">
                                <i class="fa-brands fa-telegram me-1 text-primary"></i> Telegram Alerts
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link py-2 rounded-2 fw-bold small" id="cloud-tab" data-bs-toggle="pill" data-bs-target="#tab-cloud" type="button" role="tab">
                                <i class="fa-solid fa-cloud-arrow-up me-1 text-info"></i> Offsite Cloud
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content pt-2" id="settingsTabContent">
                        
                        {{-- Tab 1: Auto Scheduling --}}
                        <div class="tab-pane fade show active" id="tab-auto" role="tabpanel">
                            <div class="form-check form-switch mb-3 p-3 bg-light rounded-3 border">
                                <input class="form-check-input ms-0 me-2" type="checkbox" id="autoBackupSwitch" name="auto_backup_enabled" value="1" {{ !empty($settings['auto_backup_enabled']) ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold text-dark small" for="autoBackupSwitch">Enable Automated Scheduled Backups</label>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-dark">Backup Frequency:</label>
                                    <select name="backup_frequency" class="form-select form-select-sm fw-semibold">
                                        <option value="daily" {{ ($settings['backup_frequency'] ?? 'daily') === 'daily' ? 'selected' : '' }}>Daily (Every midnight at 00:00)</option>
                                        <option value="weekly" {{ ($settings['backup_frequency'] ?? '') === 'weekly' ? 'selected' : '' }}>Weekly (Every Friday)</option>
                                        <option value="monthly" {{ ($settings['backup_frequency'] ?? '') === 'monthly' ? 'selected' : '' }}>Monthly (1st day of month)</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-dark">Retention Limit (Max archives to keep):</label>
                                    <input type="number" name="retention_days" class="form-control form-control-sm font-monospace fw-bold" min="1" max="100" value="{{ $settings['retention_days'] ?? $retentionLimit }}">
                                </div>
                            </div>

                            <div class="mb-2">
                                <label class="form-label small fw-bold text-dark">Automated Backup Notification Email:</label>
                                <input type="email" name="backup_email" class="form-control form-control-sm font-monospace" placeholder="backup@ideaabd.com" value="{{ $settings['backup_email'] ?? config('mail.from.address', 'adideabd@gmail.com') }}">
                            </div>
                        </div>

                        {{-- Tab 2: Telegram Alerts --}}
                        <div class="tab-pane fade" id="tab-telegram" role="tabpanel">
                            <div class="form-check form-switch mb-3 p-3 bg-light rounded-3 border">
                                <input class="form-check-input ms-0 me-2" type="checkbox" id="telegramAlertsSwitch" name="telegram_alerts_enabled" value="1" {{ !empty($settings['telegram_alerts_enabled']) ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold text-dark small" for="telegramAlertsSwitch">Instant Telegram Notifications for Backup Events & Alerts</label>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-dark">Telegram Bot Token:</label>
                                <input type="text" name="telegram_bot_token" id="telegramBotTokenInput" class="form-control form-control-sm font-monospace" placeholder="123456789:ABCdefGhIJKlmNoPQRsTUVwxyZ" value="{{ $settings['telegram_bot_token'] ?? '' }}">
                                <small class="text-muted font-monospace" style="font-size: 11px;">Bot token obtained from @BotFather</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-dark">Telegram Chat / Channel ID:</label>
                                <input type="text" name="telegram_chat_id" id="telegramChatIdInput" class="form-control form-control-sm font-monospace" placeholder="-1001234567890 or 987654321" value="{{ $settings['telegram_chat_id'] ?? '' }}">
                            </div>

                            <div class="d-flex justify-content-end">
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold" onclick="testTelegramNotification()">
                                    <i class="fa-solid fa-paper-plane me-1"></i> Send Test Telegram Message
                                </button>
                            </div>
                        </div>

                        {{-- Tab 3: Offsite Cloud Sync --}}
                        <div class="tab-pane fade" id="tab-cloud" role="tabpanel">
                            <div class="p-3 bg-light rounded-3 border mb-3">
                                <h6 class="fw-bold text-dark small mb-1"><i class="fa-solid fa-cloud-arrow-up text-primary me-1"></i> Offsite Cloud Storage Synchronization</h6>
                                <p class="text-muted small mb-0 font-monospace" style="font-size: 11px;">Automatically stream backup archives to secure cloud storage upon creation.</p>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-dark">Cloud Storage Driver:</label>
                                <select name="offsite_cloud_driver" class="form-select form-select-sm fw-semibold">
                                    <option value="none" {{ ($settings['offsite_cloud_driver'] ?? 'none') === 'none' ? 'selected' : '' }}>Disabled (Local Disk Only)</option>
                                    <option value="s3" {{ ($settings['offsite_cloud_driver'] ?? '') === 's3' ? 'selected' : '' }}>Amazon AWS S3 / Cloudflare R2</option>
                                    <option value="gdrive" {{ ($settings['offsite_cloud_driver'] ?? '') === 'gdrive' ? 'selected' : '' }}>Google Drive</option>
                                    <option value="ftp" {{ ($settings['offsite_cloud_driver'] ?? '') === 'ftp' ? 'selected' : '' }}>Remote FTP / SFTP Server</option>
                                </select>
                            </div>

                            <div class="mb-2">
                                <label class="form-label small fw-bold text-dark">Remote Backup Directory Path:</label>
                                <input type="text" name="offsite_cloud_path" class="form-control form-control-sm font-monospace" placeholder="/backups/ideaabd" value="{{ $settings['offsite_cloud_path'] ?? '/backups/ideaabd' }}">
                            </div>
                        </div>

                    </div>

                </div>
                <div class="modal-footer bg-light py-2.5 px-4 rounded-bottom-4 d-flex justify-content-between">
                    <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-backup-gradient btn-gradient-sky px-4">
                        <i class="fa-solid fa-floppy-disk"></i>
                        <span>Save Settings</span>
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
                        <h6 class="modal-title fw-bold text-white mb-0">Database Diff & Live Comparison</h6>
                        <small class="text-white-50 font-monospace" style="font-size: 11px;">Archive: <span id="diffModalFilename" class="text-warning"></span></small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            
            <div class="modal-body p-4" id="diffModalBody">
                <div class="text-center py-5 text-muted">
                    <div class="spinner-border text-info mb-2" role="status"></div>
                    <div class="fw-semibold">Comparing backup schema and rows against live database...</div>
                </div>
            </div>

            <div class="modal-footer bg-light py-2.5 px-4 rounded-bottom-4 d-flex justify-content-between">
                <button type="button" class="btn btn-sm btn-secondary rounded-pill px-4 fw-bold" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn-backup-gradient btn-gradient-amber px-4" id="btnDiffToSelective" onclick="openSelectiveFromDiff()">
                    <i class="fa-solid fa-list-check"></i>
                    <span>Selective Table Restore</span>
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
                    <span>Safe Sandbox Dry-Run Simulation</span>
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4" id="dryRunModalBody">
                <div class="text-center py-5">
                    <div class="spinner-grow text-primary mb-3" style="width: 3rem; height: 3rem;" role="status"></div>
                    <h6 class="fw-bold text-dark font-monospace mb-1">Running sandbox restore simulation...</h6>
                    <p class="text-muted small mb-0">Validating SQL syntax, foreign-key constraints & schema integrity without altering live data.</p>
                </div>
            </div>
            <div class="modal-footer bg-light py-2.5 px-4 rounded-bottom-4">
                <button type="button" class="btn btn-sm btn-secondary rounded-pill px-4 fw-bold ms-auto" data-bs-dismiss="modal">Close</button>
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
                        <h6 class="modal-title fw-bold text-white mb-0">Selective Table Restore (Granular Recovery)</h6>
                        <small class="text-white-50 font-monospace" style="font-size: 11px;">Archive: <span id="selectiveModalFilename" class="text-warning"></span></small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-warning d-flex align-items-center gap-2 rounded-3 py-2 px-3 small mb-3 border-0 border-start border-4 border-warning">
                    <i class="fa-solid fa-shield-halved fs-5"></i>
                    <div>Only selected database tables will be restored and overwritten from the backup. All other live tables will remain intact.</div>
                </div>

                {{-- Table Filter & Quick Controls --}}
                <div class="d-flex align-items-center justify-content-between mb-3 gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-xs btn-outline-primary rounded-pill px-2.5 py-1 font-monospace fw-bold" onclick="toggleAllSelectiveTables(true)">Select All</button>
                        <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2.5 py-1 font-monospace fw-bold" onclick="toggleAllSelectiveTables(false)">Deselect All</button>
                        <span class="badge bg-light text-dark border rounded-pill px-2.5 py-1 font-monospace small" id="selectiveSelectedBadge">0 Selected</span>
                    </div>
                    <input type="search" id="selectiveSearchInput" class="form-control form-control-sm font-monospace" style="max-width: 200px;" placeholder="Search tables...">
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
                <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn-backup-gradient btn-gradient-amber px-4" id="btnExecuteSelectiveRestore" onclick="submitSelectiveRestore()">
                    <i class="fa-solid fa-rotate-left"></i>
                    <span>Restore Selected Tables</span>
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/admin-backup.js') }}?v={{ @filemtime(public_path('js/admin-backup.js')) ?: 3 }}"></script>
@endpush
@endsection
