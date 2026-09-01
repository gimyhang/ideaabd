@extends('layouts.admin')

@section('title', 'Database Backup & Recovery — আইডিয়া প্রকাশন')
@section('heading', 'ডাটাবেজ ব্যাকআপ ও ডিজাস্টার রিকভারি')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">ড্যাশবোর্ড</a></li>
    <li class="breadcrumb-item active">ডাটাবেজ ব্যাকআপ</li>
@endsection

@section('actions')
    <div class="d-flex align-items-center gap-2">
        <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-bold" data-bs-toggle="modal" data-bs-target="#uploadBackupModal">
            <i class="fas fa-file-arrow-up me-1"></i> ব্যাকআপ ফাইল আপলোড (Upload)
        </button>
        <form action="{{ route('admin.backup.create') }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4 shadow-sm fw-bold">
                <i class="fas fa-database me-1.5"></i> নতুন ব্যাকআপ তৈরি করুন (Create Backup)
            </button>
        </form>
    </div>
@endsection

@section('content')
<div class="d-flex flex-column gap-4">

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-0 rounded-4 shadow-xs" role="alert">
            <i class="fas fa-circle-check me-2 text-success fs-5"></i>
            <div class="fw-semibold">{{ session('success') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center mb-0 rounded-4 shadow-xs" role="alert">
            <i class="fas fa-triangle-exclamation me-2 text-danger fs-5"></i>
            <div class="fw-semibold">{{ session('error') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Database Info & Health Cards -->
    <div class="row g-3">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card bg-white rounded-4 shadow-sm border-0 p-3.5 border-start border-4 border-primary h-100">
                <span class="small text-muted fw-semibold mb-1">কানেক্টেড ডাটাবেজ</span>
                <h5 class="text-dark fw-bold font-monospace mb-1">{{ $dbName }}</h5>
                <p class="text-muted small mb-0">ড্রাইভার: <span class="badge bg-light text-dark border font-monospace">{{ $dbDriver }}</span></p>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card bg-white rounded-4 shadow-sm border-0 p-3.5 border-start border-4 border-success h-100">
                <span class="small text-muted fw-semibold mb-1">ডাটাবেজ মোট সাইজ</span>
                <h4 class="text-dark fw-bold mb-1">{{ $formattedDbSize ?? '12.4 MB' }}</h4>
                <p class="text-muted small mb-0">মোট রেকর্ডস: <strong>{{ number_format($totalRowsCount ?? 0) }}</strong> টি</p>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card bg-white rounded-4 shadow-sm border-0 p-3.5 border-start border-4 border-info h-100">
                <span class="small text-muted fw-semibold mb-1">সংরক্ষিত ব্যাকআপ ফাইল</span>
                <h4 class="text-dark fw-bold mb-1">{{ count($backups) }} টি</h4>
                <p class="text-muted small mb-0">লোকেশন: <code>storage/app/backups/</code></p>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card bg-white rounded-4 shadow-sm border-0 p-3.5 border-start border-4 border-warning h-100">
                <span class="small text-muted fw-semibold mb-1">অটোমেটেড ব্যাকআপ শিডিউল</span>
                <h5 class="text-success fw-bold mb-1"><i class="fas fa-circle-check me-1"></i> সক্রিয় (Daily 02:00 AM)</h5>
                <p class="text-muted small mb-0">ক্লাউড রিটেনশন: ৭ দিনের ব্যাকআপ</p>
            </div>
        </div>
    </div>

    <!-- Backup Files Table Card -->
    <div class="card bg-white rounded-4 shadow-sm border-0 overflow-hidden">
        <div class="card-header bg-white d-flex align-items-center justify-content-between py-3 px-4 border-bottom">
            <h6 class="fw-bold text-dark mb-0"><i class="fas fa-file-arrow-down text-primary me-2"></i> সংরক্ষিত ব্যাকআপ ফাইল তালিকা</h6>
            <span class="badge bg-light text-dark border rounded-pill px-3 py-1.5 small">{{ count($backups) }} টি ফাইল</span>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4 py-2.5">ফাইল নাম</th>
                            <th class="py-2.5">সাইজ</th>
                            <th class="py-2.5">তৈরির তারিখ ও সময়</th>
                            <th class="text-end pe-4 py-2.5">অ্যাকশন</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($backups as $b)
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-3 bg-primary-subtle text-primary p-2 d-flex align-items-center justify-content-center" style="width:34px;height:34px;">
                                            <i class="fas fa-file-lines"></i>
                                        </div>
                                        <div>
                                            <span class="fw-bold text-dark font-monospace">{{ $b['filename'] }}</span>
                                            <div class="text-muted small" style="font-size: 11px;">MySQL SQL Dump</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border font-monospace px-2.5 py-1">{{ $b['size'] }}</span>
                                </td>
                                <td>
                                    <div class="text-dark small fw-semibold">{{ $b['created_at']->format('d M, Y h:i:s A') }}</div>
                                    <small class="text-muted" style="font-size: 11px;">{{ $b['created_at']->diffForHumans() }}</small>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-flex align-items-center justify-content-end gap-1.5">
                                        <a href="{{ route('admin.backup.download', $b['filename']) }}" class="btn btn-sm btn-outline-primary rounded-pill px-2.5 fw-semibold" title="ডাউনলোড">
                                            <i class="fas fa-download me-1"></i> ডাউনলোড
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-2.5 fw-semibold" 
                                                onclick="confirmRestore('{{ $b['filename'] }}')" title="ডাটাবেজ রিস্টোর">
                                            <i class="fas fa-rotate-left me-1"></i> রিস্টোর
                                        </button>
                                        <form action="{{ route('admin.backup.destroy', $b['filename']) }}" method="POST" onsubmit="return confirm('আপনি কি নিশ্চিত এই ব্যাকআপ ফাইলটি মুছে ফেলতে চান?');" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-2" title="মুছে ফেলুন">
                                                <i class="fas fa-trash-can"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted">
                                    <i class="fas fa-database fs-1 text-secondary opacity-50 mb-2 d-block"></i>
                                    কোনো ডাটাবেজ ব্যাকআপ ফাইল সংরক্ষিত নেই। উপরের বাটনে ক্লিক করে নতুন ব্যাকআপ তৈরি বা আপলোড করুন।
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Database Tables Statistics Accordion -->
    @if(!empty($tables))
    <div class="card bg-white rounded-4 shadow-sm border-0 overflow-hidden">
        <div class="card-header bg-white py-3 px-4 d-flex align-items-center justify-content-between border-bottom">
            <h6 class="fw-bold text-dark mb-0"><i class="fas fa-table-cells text-info me-2"></i> ডাটাবেজ টেবিল বিবরণী ({{ count($tables) }} টি টেবিল)</h6>
            <button class="btn btn-sm btn-outline-secondary rounded-pill" type="button" data-bs-toggle="collapse" data-bs-target="#tablesCollapse">
                <i class="fas fa-chevron-down me-1"></i> তালিকা দেখুন
            </button>
        </div>
        <div class="collapse" id="tablesCollapse">
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 320px;">
                    <table class="table table-sm table-striped mb-0 small">
                        <thead>
                            <tr>
                                <th class="ps-4">টেবিলের নাম</th>
                                <th>রেকর্ড সংখ্যা (Rows)</th>
                                <th class="text-end pe-4">সাইজ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tables as $tbl)
                                <tr>
                                    <td class="ps-4 font-monospace fw-semibold">{{ $tbl['name'] }}</td>
                                    <td>{{ number_format($tbl['rows']) }}</td>
                                    <td class="text-end pe-4 font-monospace">{{ $tbl['size'] }}</td>
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

<!-- Modal: Upload Backup -->
<div class="modal fade" id="uploadBackupModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-primary text-white py-2.5">
                <h6 class="modal-title fw-bold text-white mb-0"><i class="fas fa-file-arrow-up me-1.5"></i> ডাটাবেজ ব্যাকআপ ফাইল আপলোড</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.backup.upload') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">SQL ব্যাকআপ ফাইল নির্বাচন করুন (.sql, .txt, .gz)</label>
                        <input type="file" name="backup_file" class="form-control" accept=".sql,.txt,.gz" required>
                        <div class="form-text small">সর্বোচ্চ ফাইল সাইজ: ১০০ মেগাবাইট (100MB)</div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">বাতিল</button>
                    <button type="submit" class="btn btn-sm btn-primary">আপলোড সম্পন্ন করুন</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Form for Restore Post -->
<form id="restoreBackupForm" method="POST" style="display:none;">
    @csrf
</form>

@push('scripts')
<script>
function confirmRestore(filename) {
    if (confirm("সতর্কতা! আপনি কি নিশ্চিতভাবে '" + filename + "' ব্যাকআপ ফাইল থেকে ডাটাবেজ রিস্টোর করতে চান? এটি বর্তমান ডাটাবেজের সকল ডেটা প্রতিস্থাপন করবে।")) {
        const form = document.getElementById('restoreBackupForm');
        form.action = "/admin/backup/restore/" + encodeURIComponent(filename);
        form.submit();
    }
}
</script>
@endpush
@endsection
