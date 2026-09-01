@extends('layouts.admin')

@section('title', 'Master Backup & Disaster Recovery — আইডিয়া প্রকাশন')
@section('heading', 'মাস্টার ব্যাকআপ ও ডিজাস্টার রিকভারি হাব')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">ড্যাশবোর্ড</a></li>
    <li class="breadcrumb-item active">মাস্টার ব্যাকআপ হাব</li>
@endsection

@section('actions')
    <div class="d-flex flex-wrap align-items-center gap-2">
        {{-- Explicit Upload Backup Button --}}
        <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-bold d-inline-flex align-items-center gap-1.5 shadow-xs" onclick="document.getElementById('backupFileInput').click()">
            <i class="fas fa-file-arrow-up"></i>
            <span>ব্যাকআপ আপলোড</span>
        </button>

        {{-- 1-Click Integrity Health Check --}}
        <form action="{{ route('admin.backup.integrity') }}" method="POST" class="m-0">
            @csrf
            <button type="submit" class="btn btn-outline-info btn-sm rounded-pill px-3 fw-bold d-inline-flex align-items-center gap-1.5 shadow-xs">
                <i class="fas fa-stethoscope"></i>
                <span>ইন্টিগ্রিটি স্ক্যান</span>
            </button>
        </form>

        {{-- 1-Click Database Table Optimizer --}}
        <form action="{{ route('admin.backup.optimize') }}" method="POST" class="m-0">
            @csrf
            <button type="submit" class="btn btn-outline-success btn-sm rounded-pill px-3 fw-bold d-inline-flex align-items-center gap-1.5 shadow-xs" onclick="return confirm('আপনি কি ডাটাবেজের সমস্ত টেবিল ও ইনডেক্স অপ্টিমাইজ করতে চান?');">
                <i class="fas fa-wand-magic-sparkles"></i>
                <span>ডাটাবেজ অপ্টিমাইজ</span>
            </button>
        </form>

        {{-- NEW: 1-Click Complete Data & Media Images Backup (.ZIP) --}}
        <form action="{{ route('admin.backup.create') }}" method="POST" class="m-0" onsubmit="handleBackupCreation(this, 'dataMedia')">
            @csrf
            <input type="hidden" name="mode" value="data_media">
            <button type="submit" class="btn btn-success btn-sm rounded-pill px-3.5 fw-bold d-inline-flex align-items-center gap-1.5 shadow-sm text-white" id="btnDataMediaBackup">
                <i class="fas fa-box-archive"></i>
                <span id="btnDataMediaBackupText">সমস্ত ডাটা ও ছবি ব্যাকআপ (.ZIP)</span>
            </button>
        </form>

        {{-- 1-Click Full System Backup (.ZIP) --}}
        <form action="{{ route('admin.backup.create') }}" method="POST" class="m-0" id="createMasterBackupForm" onsubmit="handleBackupCreation(this, 'fullSystem')">
            @csrf
            <input type="hidden" name="mode" value="full_system">
            <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3.5 fw-bold d-inline-flex align-items-center gap-1.5 shadow-sm" id="btnMasterBackup">
                <i class="fas fa-file-zipper"></i>
                <span id="btnMasterBackupText">সম্পূর্ণ সিস্টেম ব্যাকআপ</span>
            </button>
        </form>
    </div>
@endsection

@section('content')
<style>
/* ── World-Class Enterprise Backup Hub Styling ── */
.backup-card-widget {
    background: #ffffff;
    border-radius: 14px;
    border: 1px solid rgba(226, 232, 240, 0.8);
    box-shadow: 0 4px 16px rgba(15, 23, 42, 0.03);
    transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1);
    min-height: 128px;
}

.backup-card-widget:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(15, 23, 42, 0.06);
    border-color: rgba(14, 165, 233, 0.3);
}

.metric-avatar-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.15rem;
}

/* ── Modern Dynamic Upload Zone ── */
.enterprise-dropzone {
    border: 2px dashed #cbd5e1;
    border-radius: 16px;
    background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
    transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    cursor: pointer;
}

.enterprise-dropzone:hover, .enterprise-dropzone.dragover {
    border-color: #2563eb;
    background: #eff6ff;
    transform: scale(1.002);
}

.pulse-online-badge {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    display: inline-block;
    background-color: #10b981;
    box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
    animation: pulseRing 1.8s infinite cubic-bezier(0.66, 0, 0, 1);
}

@keyframes pulseRing {
    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
    70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
}

.table-custom-row {
    transition: background-color 0.15s ease;
}

.table-custom-row:hover {
    background-color: #f8fafc;
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

    <!-- Dynamic Toast Notification Container -->
    <div id="dynamicAlertContainer"></div>

    <!-- 1. Symmetrical & Polished 4-Card Diagnostic Grid -->
    <div class="row row-cols-1 row-cols-sm-2 row-cols-xl-4 g-3">
        
        {{-- Card 1: Connected Database Engine --}}
        <div class="col">
            <div class="card backup-card-widget p-3 d-flex flex-column justify-content-between h-100">
                <div class="d-flex align-items-start justify-content-between mb-2">
                    <div class="min-w-0 pe-2">
                        <span class="text-muted small fw-semibold text-uppercase font-monospace d-block mb-1" style="font-size: 0.70rem; letter-spacing: 0.5px;">কানেক্টেড ডাটাবেজ</span>
                        <h5 class="fw-bold text-dark mb-0 font-monospace text-truncate" style="font-size: 1.05rem;" title="{{ $dbName }}">
                            {{ Str::limit($dbName, 15) }}
                        </h5>
                    </div>
                    <div class="metric-avatar-icon bg-primary-subtle text-primary flex-shrink-0">
                        <i class="fas fa-database"></i>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between pt-2 border-top">
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace text-uppercase px-2 py-0.5" style="font-size: 0.68rem;">
                        {{ $dbDriver }}
                    </span>
                    <span class="small text-success fw-semibold d-inline-flex align-items-center gap-1.5" style="font-size: 0.75rem;">
                        <span class="pulse-online-badge"></span> লাইভ কানেকশন
                    </span>
                </div>
            </div>
        </div>

        {{-- Card 2: Database Volume & Rows --}}
        <div class="col">
            <div class="card backup-card-widget p-3 d-flex flex-column justify-content-between h-100">
                <div class="d-flex align-items-start justify-content-between mb-2">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase font-monospace d-block mb-1" style="font-size: 0.70rem; letter-spacing: 0.5px;">ডাটাবেজ মোট সাইজ</span>
                        <h4 class="fw-bold text-dark mb-0 font-monospace" style="font-size: 1.25rem;">{{ $formattedDbSize }}</h4>
                    </div>
                    <div class="metric-avatar-icon bg-success-subtle text-success flex-shrink-0">
                        <i class="fas fa-server"></i>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between pt-2 border-top">
                    <span class="small text-muted font-monospace" style="font-size: 0.75rem;">
                        <i class="fas fa-table-cells text-muted me-1"></i>{{ count($tables) }} টি টেবিল
                    </span>
                    <span class="small text-dark fw-bold font-monospace" style="font-size: 0.75rem;">
                        {{ number_format($totalRowsCount) }} টি রেকর্ড
                    </span>
                </div>
            </div>
        </div>

        {{-- Card 3: Master Backups Archive --}}
        <div class="col">
            <div class="card backup-card-widget p-3 d-flex flex-column justify-content-between h-100">
                <div class="d-flex align-items-start justify-content-between mb-2">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase font-monospace d-block mb-1" style="font-size: 0.70rem; letter-spacing: 0.5px;">সংরক্ষিত মাস্টার জিপ</span>
                        <h4 class="fw-bold text-dark mb-0 font-monospace" style="font-size: 1.25rem;">{{ count($backups) }} টি আর্কাইভ</h4>
                    </div>
                    <div class="metric-avatar-icon bg-warning-subtle text-warning flex-shrink-0">
                        <i class="fas fa-file-zipper"></i>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between pt-2 border-top">
                    <span class="small text-muted" style="font-size: 0.75rem;">ডিস্ক ব্যবহার</span>
                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle font-monospace px-2 py-0.5" style="font-size: 0.70rem;">
                        {{ $formattedTotalBackupSize }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Card 4: Disaster Recovery & Security --}}
        <div class="col">
            <div class="card backup-card-widget p-3 d-flex flex-column justify-content-between h-100">
                <div class="d-flex align-items-start justify-content-between mb-2">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase font-monospace d-block mb-1" style="font-size: 0.70rem; letter-spacing: 0.5px;">ডিজাস্টার সিকিউরিটি</span>
                        <h5 class="fw-bold text-success mb-0 d-flex align-items-center gap-1.5" style="font-size: 1.05rem;">
                            <i class="fas fa-shield-halved"></i> শতভাগ সুরক্ষিত
                        </h5>
                    </div>
                    <div class="metric-avatar-icon bg-info-subtle text-info flex-shrink-0">
                        <i class="fas fa-clock-rotate-left"></i>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between pt-2 border-top">
                    <span class="small text-muted" style="font-size: 0.75rem;">অটো-রিটেনশন</span>
                    <span class="small text-info fw-semibold font-monospace" style="font-size: 0.75rem;">
                        সর্বশেষ {{ $retentionLimit }} টি সংরক্ষিত
                    </span>
                </div>
            </div>
        </div>

    </div>

    <!-- 2. Dynamic Drag & Drop Instant Upload Zone (ডাইনামিক স্বয়ংক্রিয় আপলোড) -->
    <div class="card border-0 shadow-xs rounded-4 bg-white p-4">
        <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-file-arrow-up text-primary fs-5"></i>
                <h6 class="fw-bold text-dark mb-0" style="font-size: 0.95rem;">ডাটাবেজ ও মাস্টার ব্যাকআপ ফাইল আপলোড</h6>
            </div>
            <span class="badge bg-light text-muted border rounded-pill px-3 py-1 small">
                অনুমোদিত: .ZIP, .SQL, .SQLITE, .GZ
            </span>
        </div>

        <div class="enterprise-dropzone p-4 text-center position-relative" id="dropZone" onclick="document.getElementById('backupFileInput').click()">
            <input type="file" id="backupFileInput" class="d-none" accept=".zip,.sql,.sqlite,.gz" onchange="handleDynamicUpload(this.files)">
            
            <div id="dropZonePrompt" class="py-2">
                <div class="rounded-circle bg-primary-subtle text-primary p-3 d-inline-flex align-items-center justify-content-center mb-2 shadow-xs" style="width: 52px; height: 52px;">
                    <i class="fas fa-cloud-arrow-up fs-4"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1" style="font-size: 1.05rem;">
                    কম্পিউটার থেকে ব্যাকআপ ফাইল (.ZIP / .SQL) এখানে টেনে আনুন
                </h6>
                <p class="text-muted small mb-3" style="font-size: 0.85rem;">
                    অথবা নিচে বাটনে ক্লিক করে ফাইল নির্বাচন করুন (সর্বোচ্চ ২০০ মেগাবাইট)
                </p>
                <button type="button" class="btn btn-sm btn-primary rounded-pill px-4 fw-bold shadow-sm" onclick="event.stopPropagation(); document.getElementById('backupFileInput').click()">
                    <i class="fas fa-folder-open me-1.5"></i> ফাইল নির্বাচন করুন (Browse File)
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
                <div class="progress rounded-pill shadow-xs" style="height: 12px; background-color: #e2e8f0;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" id="uploadProgressBar" role="progressbar" style="width: 0%"></div>
                </div>
                <small class="text-muted d-block mt-2" style="font-size: 11px;">অনুগ্রহ করে অপেক্ষা করুন, ফাইলটি নিরাপদে সার্ভারে আপলোড ও ভেরিফাই হচ্ছে...</small>
            </div>
        </div>
    </div>

    <!-- 3. Master Backup Archive Records Table -->
    <div class="card bg-white rounded-4 shadow-sm border-0 overflow-hidden">
        
        {{-- Card Header --}}
        <div class="card-header bg-white d-flex flex-wrap align-items-center justify-content-between py-3 px-4 border-bottom gap-2">
            <div class="d-flex align-items-center gap-2.5">
                <div class="rounded-3 bg-primary-subtle text-primary p-2 d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                    <i class="fas fa-file-zipper"></i>
                </div>
                <div>
                    <h6 class="fw-bold text-dark mb-0" style="font-size: 0.95rem;">মাস্টার ব্যাকআপ আর্কাইভ তালিকা</h6>
                    <small class="text-muted" style="font-size: 11px;">ডাটাবেজ ডাম্প + মিডিয়া আপলোডস একসাথে সংরক্ষিত</small>
                </div>
            </div>

            <div class="d-flex align-items-center gap-2">
                {{-- Quick DB-only snapshot button --}}
                <form action="{{ route('admin.backup.create') }}" method="POST" class="m-0">
                    @csrf
                    <input type="hidden" name="include_media" value="0">
                    <button type="submit" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-semibold" title="মিডিয়া ছাড়া শুধুমাত্র ডাটাবেজ ব্যাকআপ">
                        <i class="fas fa-database me-1"></i> শুধুমাত্র ডাটাবেজ ব্যাকআপ
                    </button>
                </form>

                <span class="badge bg-light text-dark border rounded-pill px-3 py-1.5 font-monospace small" id="backupCountBadge">
                    {{ count($backups) }} টি ফাইল
                </span>
            </div>
        </div>

        {{-- Table Container --}}
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="backupsTable">
                    <thead class="table-light small text-uppercase font-monospace text-muted">
                        <tr>
                            <th class="ps-4 py-3" style="min-width: 280px;">আর্কাইভ ফাইল নাম</th>
                            <th class="py-3" style="width: 160px;">ফরম্যাট / ধরন</th>
                            <th class="py-3" style="width: 120px;">সাইজ</th>
                            <th class="py-3" style="width: 170px;">তৈরির সময়</th>
                            <th class="text-end pe-4 py-3" style="min-width: 200px;">অ্যাকশন</th>
                        </tr>
                    </thead>
                    <tbody id="backupsTableBody">
                        @forelse($backups as $b)
                            <tr class="table-custom-row" id="row-{{ md5($b['filename']) }}">
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-2.5">
                                        <div class="rounded-3 {{ $b['is_master_zip'] ? 'bg-primary text-white' : 'bg-light border text-muted' }} p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width:36px;height:36px;">
                                            @if($b['is_master_zip'])
                                                <i class="fas fa-file-zipper"></i>
                                            @elseif($b['extension'] === 'sqlite')
                                                <i class="fas fa-database text-success"></i>
                                            @else
                                                <i class="fas fa-file-code"></i>
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <span class="fw-bold text-dark font-monospace text-truncate d-block" title="{{ $b['filename'] }}" style="font-size: 0.88rem;">
                                                {{ $b['filename'] }}
                                            </span>
                                            <small class="text-muted" style="font-size: 11px;">
                                                {{ $b['is_master_zip'] ? 'Full System Master ZIP (DB + Media)' : 'Database Dump' }}
                                            </small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($b['is_master_zip'])
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1 font-monospace" style="font-size: 0.70rem;">
                                            <i class="fas fa-box-archive me-1"></i> MASTER .ZIP
                                        </span>
                                    @else
                                        <span class="badge bg-light text-dark border rounded-pill px-2.5 py-1 font-monospace" style="font-size: 0.70rem;">
                                            {{ strtoupper($b['extension']) }}
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <span class="fw-bold text-dark font-monospace" style="font-size: 0.85rem;">{{ $b['size'] }}</span>
                                </td>
                                <td>
                                    <div class="text-dark small fw-semibold">{{ $b['created_at']->format('d M, Y h:i A') }}</div>
                                    <small class="text-muted" style="font-size: 11px;">{{ $b['created_at']->diffForHumans() }}</small>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-flex align-items-center justify-content-end gap-1.5">
                                        {{-- Inspect ZIP Preview --}}
                                        @if($b['is_master_zip'])
                                            <button type="button" class="btn btn-sm btn-outline-info rounded-pill px-2.5 py-1 fw-semibold" onclick="inspectZipArchive('{{ $b['filename'] }}')" title="প্রিভিউ দেখুন">
                                                <i class="fas fa-eye me-1"></i> প্রিভিউ
                                            </button>
                                        @endif

                                        {{-- Download --}}
                                        <a href="{{ route('admin.backup.download', $b['filename']) }}" class="btn btn-sm btn-outline-primary rounded-pill px-2.5 py-1 fw-semibold" title="ডাউনলোড">
                                            <i class="fas fa-download me-1"></i> ডাউনলোড
                                        </a>

                                        {{-- Restore with Safety Guarantee --}}
                                        <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-2.5 py-1 fw-semibold" 
                                                onclick="confirmRestore('{{ $b['filename'] }}', {{ $b['is_master_zip'] ? 'true' : 'false' }})" title="সিস্টেম রিস্টোর">
                                            <i class="fas fa-rotate-left me-1"></i> রিস্টোর
                                        </button>

                                        {{-- Delete --}}
                                        <form action="{{ route('admin.backup.destroy', $b['filename']) }}" method="POST" onsubmit="return confirm('আপনি কি নিশ্চিত এই ব্যাকআপ ফাইলটি মুছে ফেলতে চান?');" class="d-inline m-0">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle p-0 d-inline-flex align-items-center justify-content-center" style="width:28px;height:28px;" title="মুছে ফেলুন">
                                                <i class="fas fa-trash-can" style="font-size:11px;"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr id="emptyRow">
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <div class="p-3 text-center">
                                        <i class="fas fa-file-zipper fs-1 text-secondary opacity-40 mb-3 d-block"></i>
                                        <h6 class="fw-bold text-dark">কোনো ব্যাকআপ ফাইল সংরক্ষিত নেই</h6>
                                        <p class="small text-muted mb-3">উপরের বাটনে ক্লিক করে প্রথম মাস্টার ব্যাকআপ (.ZIP) তৈরি করুন</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 4. Database Tables & Records Breakdown Accordion -->
    @if(!empty($tables))
        <div class="card bg-white rounded-4 shadow-sm border-0 overflow-hidden">
            <div class="card-header bg-white d-flex align-items-center justify-content-between py-3 px-4 border-bottom">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-table-list text-info fs-5"></i>
                    <h6 class="fw-bold text-dark mb-0">ডাটাবেজ টেবিল ও রেকর্ড বিবরণী ({{ count($tables) }} টি টেবিল)</h6>
                </div>
                <button class="btn btn-sm btn-light border rounded-pill px-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTables" aria-expanded="false">
                    <i class="fas fa-chevron-down me-1"></i> বিস্তারিত দেখুন
                </button>
            </div>
            <div class="collapse" id="collapseTables">
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 420px; overflow-y: auto;">
                        <table class="table table-hover table-sm align-middle mb-0 small">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th class="ps-4">টেবিল নাম</th>
                                    <th>মোট রেকর্ড সংখ্যা</th>
                                    <th class="text-end pe-4">সাইজ</th>
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

<!-- Inspect ZIP Preview Modal -->
<div class="modal fade" id="inspectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header bg-dark text-white py-3">
                <h6 class="modal-title fw-bold text-white mb-0">
                    <i class="fas fa-file-zipper text-warning me-2"></i>মাস্টার জিপ আর্কাইভ প্রিভিউ: <span id="inspectFilename" class="font-monospace text-info"></span>
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="inspectBody">
                <div class="text-center py-4 text-muted">
                    <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div> আর্কাইভ ফাইল স্ক্যান করা হচ্ছে...
                </div>
            </div>
            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-bs-dismiss="modal">বন্ধ করুন</button>
            </div>
        </div>
    </div>
</div>

<!-- Restore Confirmation Modal with Pre-Snapshot Guarantee -->
<div class="modal fade" id="restoreModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header bg-danger text-white py-3">
                <h6 class="modal-title fw-bold text-white mb-0">
                    <i class="fas fa-triangle-exclamation me-2"></i>সিস্টেম রিস্টোর সতর্কতা!
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="restoreForm" method="POST">
                @csrf
                <div class="modal-body p-4 text-center">
                    <div class="rounded-circle bg-danger-subtle text-danger p-3 d-inline-flex align-items-center justify-content-center mb-3 shadow-xs" style="width: 60px; height: 60px;">
                        <i class="fas fa-rotate-left fs-2"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-2">আপনি কি নিশ্চিত যে সিস্টেম রিস্টোর করবেন?</h5>
                    <p class="text-muted small mb-3">
                        <strong class="text-danger font-monospace" id="restoreFilename"></strong> ফাইল থেকে ডাটাবেজ ও মিডিয়া প্রতিস্থাপিত হবে।
                    </p>
                    <div class="p-3 bg-light rounded-3 text-start small text-muted border">
                        <i class="fas fa-shield-check text-success me-1.5"></i> <strong>অটোমেটিক সেফটি স্ন্যাপশট:</strong> রিস্টোর শুরু হওয়ার পূর্বে বর্তমান ডাটার একটি স্বয়ংক্রিয় ব্যাকআপ তৈরি হবে, যাতে যেকোনো প্রয়োজনে পূর্বাবস্থায় ফিরে যাওয়া যায়।
                    </div>
                </div>
                <div class="modal-footer bg-light py-2 justify-content-center">
                    <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-bs-dismiss="modal">না, বাতিল করুন</button>
                    <button type="submit" class="btn btn-sm btn-danger rounded-pill px-4 fw-bold">
                        <i class="fas fa-check me-1"></i> হ্যাঁ, রিস্টোর নিশ্চিত করুন
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // 1. Interactive Dynamic Drag & Drop Uploading
    const dropZone = document.getElementById('dropZone');
    
    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropZone.classList.add('dragover');
        }, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropZone.classList.remove('dragover');
        }, false);
    });

    dropZone.addEventListener('drop', (e) => {
        const dt = e.dataTransfer;
        const files = dt.files;
        if (files.length > 0) {
            handleDynamicUpload(files);
        }
    });

    function handleDynamicUpload(files) {
        if (!files || files.length === 0) return;
        const file = files[0];

        const formData = new FormData();
        formData.append('backup_file', file);
        formData.append('_token', '{{ csrf_token() }}');

        // Show Live Progress UI
        document.getElementById('dropZonePrompt').classList.add('d-none');
        const progressSection = document.getElementById('uploadProgressSection');
        progressSection.classList.remove('d-none');
        const progressBar = document.getElementById('uploadProgressBar');
        const percentText = document.getElementById('uploadPercentText');
        const filenameText = document.getElementById('uploadFilenameText');

        filenameText.textContent = `'${file.name}' আপলোড হচ্ছে...`;

        const xhr = new XMLHttpRequest();
        xhr.open('POST', '{{ route("admin.backup.upload") }}', true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

        xhr.upload.onprogress = function(e) {
            if (e.lengthComputable) {
                const percent = Math.round((e.loaded / e.total) * 100);
                progressBar.style.width = percent + '%';
                percentText.textContent = percent + '%';
            }
        };

        xhr.onload = function() {
            if (xhr.status === 200) {
                try {
                    const res = JSON.parse(xhr.responseText);
                    filenameText.textContent = 'আপলোড সম্পন্ন! তালিকা আপডেট হচ্ছে...';
                    progressBar.classList.remove('bg-primary');
                    progressBar.classList.add('bg-success');
                    showDynamicAlert('success', res.message || 'ফাইল সফলভাবে আপলোড হয়েছে!');
                    setTimeout(() => window.location.reload(), 600);
                } catch(e) {
                    window.location.reload();
                }
            } else {
                let err = 'আপলোডে ত্রুটি ঘটেছে! দয়া করে ফাইলটি পরীক্ষা করুন।';
                try {
                    const errRes = JSON.parse(xhr.responseText);
                    if (errRes.message) err = errRes.message;
                } catch(e){}
                showDynamicAlert('danger', err);
                resetUploadZone();
            }
        };

        xhr.onerror = function() {
            showDynamicAlert('danger', 'নেটওয়ার্ক ত্রুটি! আপলোড সম্পন্ন করা যায়নি।');
            resetUploadZone();
        };

        xhr.send(formData);
    }

    function resetUploadZone() {
        document.getElementById('dropZonePrompt').classList.remove('d-none');
        document.getElementById('uploadProgressSection').classList.add('d-none');
        const progressBar = document.getElementById('uploadProgressBar');
        progressBar.style.width = '0%';
        progressBar.classList.remove('bg-success');
        progressBar.classList.add('bg-primary');
        document.getElementById('backupFileInput').value = '';
    }

    function showDynamicAlert(type, message) {
        const container = document.getElementById('dynamicAlertContainer');
        container.innerHTML = `
            <div class="alert alert-${type} alert-dismissible fade show d-flex align-items-center mb-0 rounded-3 shadow-xs border-0 border-start border-4 border-${type} bg-white py-2.5 px-3" role="alert">
                <i class="fas fa-${type === 'success' ? 'circle-check text-success' : 'triangle-exclamation text-danger'} fs-5 me-2.5"></i>
                <div class="fw-semibold small text-dark">${message}</div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
    }

    // 2. Handle Backup creation button spinners
    function handleBackupCreation(form, mode) {
        if (mode === 'dataMedia') {
            const btn = document.getElementById('btnDataMediaBackup');
            const btnText = document.getElementById('btnDataMediaBackupText');
            if (btn) btn.disabled = true;
            if (btnText) btnText.textContent = 'ডাটা ও ছবি কম্প্রেস হচ্ছে...';
        } else {
            const btn = document.getElementById('btnMasterBackup');
            const btnText = document.getElementById('btnMasterBackupText');
            if (btn) btn.disabled = true;
            if (btnText) btnText.textContent = 'সিস্টেম ব্যাকআপ তৈরি হচ্ছে...';
        }
    }

    // 3. Restore Confirmation Modal Trigger
    function confirmRestore(filename, isMasterZip) {
        document.getElementById('restoreFilename').textContent = filename;
        document.getElementById('restoreForm').action = "{{ url('admin/backup/restore') }}/" + encodeURIComponent(filename);
        new bootstrap.Modal(document.getElementById('restoreModal')).show();
    }

    // 4. Inspect ZIP Preview
    function inspectZipArchive(filename) {
        document.getElementById('inspectFilename').textContent = filename;
        const modal = new bootstrap.Modal(document.getElementById('inspectModal'));
        modal.show();

        const body = document.getElementById('inspectBody');
        body.innerHTML = '<div class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm text-primary me-2"></div> আর্কাইভ ফাইল স্ক্যান করা হচ্ছে...</div>';

        fetch("{{ url('admin/backup/inspect') }}/" + encodeURIComponent(filename))
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    let html = `
                        <div class="row g-2 mb-3">
                            <div class="col-6 col-md-4">
                                <div class="p-2.5 bg-light rounded-3 text-center border">
                                    <small class="text-muted d-block" style="font-size: 11px;">মোট ফাইল সংখ্যা</small>
                                    <strong class="font-monospace text-primary fs-6">${data.files_count} টি</strong>
                                </div>
                            </div>
                            <div class="col-6 col-md-4">
                                <div class="p-2.5 bg-light rounded-3 text-center border">
                                    <small class="text-muted d-block" style="font-size: 11px;">আর্কাইভ সাইজ</small>
                                    <strong class="font-monospace text-success fs-6">${data.size}</strong>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="p-2.5 bg-light rounded-3 text-center border">
                                    <small class="text-muted d-block" style="font-size: 11px;">ডাটাবেজ ইঞ্জিন</small>
                                    <strong class="font-monospace text-dark fs-6">${data.manifest ? data.manifest.driver.toUpperCase() : 'MySQL/SQLite'}</strong>
                                </div>
                            </div>
                        </div>
                        <h6 class="fw-bold small text-muted text-uppercase mb-2 font-monospace" style="font-size: 11px;">আর্কাইভের ফাইল তালিকা:</h6>
                        <div class="table-responsive" style="max-height: 240px; overflow-y: auto;">
                            <table class="table table-sm table-hover small mb-0 font-monospace">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th>ফাইলের পথ</th>
                                        <th class="text-end">সাইজ</th>
                                    </tr>
                                </thead>
                                <tbody>
                    `;
                    data.files.forEach(f => {
                        html += `
                            <tr>
                                <td>${f.name}</td>
                                <td class="text-end text-muted">${f.size}</td>
                            </tr>
                        `;
                    });
                    html += `</tbody></table></div>`;
                    body.innerHTML = html;
                } else {
                    body.innerHTML = `<div class="alert alert-danger mb-0">${data.message}</div>`;
                }
            })
            .catch(() => {
                body.innerHTML = `<div class="alert alert-danger mb-0">আর্কাইভ প্রিভিউ লোড করতে ব্যর্থ হয়েছে।</div>`;
            });
    }
</script>
@endpush
@endsection
