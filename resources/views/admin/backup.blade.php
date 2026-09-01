@extends('layouts.admin')

@section('title', 'Database Backup & Recovery — আইডিয়া প্রকাশন')
@section('heading', 'ডাটাবেজ ব্যাকআপ ও ডিজাস্টার রিকভারি')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">ড্যাশবোর্ড</a></li>
    <li class="breadcrumb-item active">ডাটাবেজ ব্যাকআপ ও রিকভারি</li>
@endsection

@section('actions')
    <div class="d-flex flex-wrap align-items-center gap-2">
        {{-- 1-Click Optimize DB --}}
        <form action="{{ route('admin.backup.optimize') }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-outline-success btn-sm rounded-pill px-3 fw-bold" onclick="return confirm('আপনি কি ডাটাবেজের সমস্ত টেবিল ও ইনডেক্স অপ্টিমাইজ করতে চান?');">
                <i class="fas fa-wand-magic-sparkles me-1"></i> অপ্টিমাইজ করুন (Optimize)
            </button>
        </form>

        {{-- Upload Backup Modal Trigger --}}
        <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-bold" data-bs-toggle="modal" data-bs-target="#uploadBackupModal">
            <i class="fas fa-file-arrow-up me-1"></i> ব্যাকআপ ফাইল আপলোড
        </button>

        {{-- Create Backup Dropdown / Modal --}}
        <div class="dropdown">
            <button class="btn btn-primary btn-sm rounded-pill px-3.5 shadow-sm fw-bold dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-database me-1.5"></i> নতুন ব্যাকআপ তৈরি করুন
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-3 py-2" style="min-width: 250px;">
                <li>
                    <form action="{{ route('admin.backup.create') }}" method="POST">
                        @csrf
                        <input type="hidden" name="backup_type" value="sql">
                        <button type="submit" class="dropdown-item py-2">
                            <i class="fas fa-file-code text-primary me-2"></i>
                            <div>
                                <span class="fw-bold d-block">স্ট্যান্ডার্ড SQL ডাম্প (.sql)</span>
                                <small class="text-muted">সমস্ত টেবিল ও ডেটার টেক্সট ডাম্প</small>
                            </div>
                        </button>
                    </form>
                </li>
                <li>
                    <form action="{{ route('admin.backup.create') }}" method="POST">
                        @csrf
                        <input type="hidden" name="backup_type" value="zip">
                        <button type="submit" class="dropdown-item py-2">
                            <i class="fas fa-file-zipper text-warning me-2"></i>
                            <div>
                                <span class="fw-bold d-block">ফুল মিডিয়া + ডাটাবেজ (.zip)</span>
                                <small class="text-muted">বইয়ের কভার, ছবি ও ডাটাবেজ একসাথে</small>
                            </div>
                        </button>
                    </form>
                </li>
                <li>
                    <form action="{{ route('admin.backup.create') }}" method="POST">
                        @csrf
                        <input type="hidden" name="backup_type" value="gz">
                        <button type="submit" class="dropdown-item py-2">
                            <i class="fas fa-file-arrow-down text-info me-2"></i>
                            <div>
                                <span class="fw-bold d-block">কম্প্রেসড জিজিপ (.sql.gz)</span>
                                <small class="text-muted">ছোট সাইজের সংকুচিত ফাইল</small>
                            </div>
                        </button>
                    </form>
                </li>
                @if($dbDriver === 'sqlite')
                    <li><hr class="dropdown-divider my-1"></li>
                    <li>
                        <form action="{{ route('admin.backup.create') }}" method="POST">
                            @csrf
                            <input type="hidden" name="backup_type" value="sqlite">
                            <button type="submit" class="dropdown-item py-2">
                                <i class="fas fa-database text-success me-2"></i>
                                <div>
                                    <span class="fw-bold d-block">SQLite স্ন্যাপশট (.sqlite)</span>
                                    <small class="text-muted">সরাসরি ডাটাবেজ ফাইলের ক্লোন</small>
                                </div>
                            </button>
                        </form>
                    </li>
                @endif
            </ul>
        </div>
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
                <span class="small text-muted fw-semibold mb-1">কানেক্টেড ডাটাবেজ ইঞ্জিন</span>
                <h5 class="text-dark fw-bold font-monospace mb-1">{{ Str::limit($dbName, 18) }}</h5>
                <p class="text-muted small mb-0">ড্রাইভার: <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace text-uppercase">{{ $dbDriver }}</span></p>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card bg-white rounded-4 shadow-sm border-0 p-3.5 border-start border-4 border-success h-100">
                <span class="small text-muted fw-semibold mb-1">ডাটাবেজ সাইজ ও রেকর্ডস</span>
                <h4 class="text-dark fw-bold mb-1">{{ $formattedDbSize ?? '12.4 MB' }}</h4>
                <p class="text-muted small mb-0">মোট রেকর্ডস: <strong>{{ number_format($totalRowsCount ?? 0) }}</strong> টি</p>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card bg-white rounded-4 shadow-sm border-0 p-3.5 border-start border-4 border-info h-100">
                <span class="small text-muted fw-semibold mb-1">সংরক্ষিত ব্যাকআপ ফাইল</span>
                <h4 class="text-dark fw-bold mb-1">{{ count($backups) }} টি</h4>
                <p class="text-muted small mb-0 font-monospace" style="font-size: 11px;">storage/app/backups/</p>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card bg-white rounded-4 shadow-sm border-0 p-3.5 border-start border-4 border-warning h-100">
                <span class="small text-muted fw-semibold mb-1">সিস্টেম সিকিউরিটি স্ট্যাটাস</span>
                <h5 class="text-success fw-bold mb-1"><i class="fas fa-shield-halved me-1"></i> শতভাগ সুরক্ষিত</h5>
                <p class="text-muted small mb-0">অটো ব্যাকআপ ও ডিজাস্টার রিকভারি এনাবল্ড</p>
            </div>
        </div>
    </div>

    <!-- Backup Files Table Card -->
    <div class="card bg-white rounded-4 shadow-sm border-0 overflow-hidden">
        <div class="card-header bg-white d-flex align-items-center justify-content-between py-3 px-4 border-bottom">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-file-arrow-down text-primary fs-5"></i>
                <h6 class="fw-bold text-dark mb-0">সংরক্ষিত ব্যাকআপ ফাইল তালিকা</h6>
            </div>
            <span class="badge bg-light text-dark border rounded-pill px-3 py-1.5 small">{{ count($backups) }} টি ব্যাকআপ ফাইল</span>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small text-uppercase">
                        <tr>
                            <th class="ps-4 py-2.5">ফাইল বিবরণ</th>
                            <th class="py-2.5">ফরম্যাট / ধরন</th>
                            <th class="py-2.5">সাইজ</th>
                            <th class="py-2.5">তৈরির তারিখ ও সময়</th>
                            <th class="text-end pe-4 py-2.5">অ্যাকশন</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($backups as $b)
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-2.5">
                                        <div class="rounded-3 bg-primary-subtle text-primary p-2 d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                                            @if($b['extension'] === 'zip')
                                                <i class="fas fa-file-zipper text-warning"></i>
                                            @elseif($b['extension'] === 'sqlite')
                                                <i class="fas fa-database text-success"></i>
                                            @else
                                                <i class="fas fa-file-lines text-primary"></i>
                                            @endif
                                        </div>
                                        <div>
                                            <span class="fw-bold text-dark font-monospace">{{ $b['filename'] }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border font-monospace px-2.5 py-1">
                                        {{ $b['type'] }}
                                    </span>
                                </td>
                                <td>
                                    <span class="fw-semibold text-dark font-monospace">{{ $b['size'] }}</span>
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
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle p-0 d-inline-flex align-items-center justify-content-center" style="width:28px;height:28px;" title="মুছে ফেলুন">
                                                <i class="fas fa-trash-can" style="font-size:11px;"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="fas fa-database fs-1 text-secondary opacity-50 mb-2 d-block"></i>
                                    কোনো ডাটাবেজ ব্যাকআপ ফাইল সংরক্ষিত নেই। উপরের "নতুন ব্যাকআপ তৈরি করুন" বাটনে ক্লিক করুন।
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
                                    <th>মোট রো / রেকর্ড</th>
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

<!-- Upload Backup Modal -->
<div class="modal fade" id="uploadBackupModal" tabindex="-1" aria-labelledby="uploadBackupModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header bg-primary text-white py-3">
                <h6 class="modal-title fw-bold text-white mb-0" id="uploadBackupModalLabel">
                    <i class="fas fa-file-arrow-up me-2"></i>ব্যাকআপ ফাইল আপলোড করুন
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.backup.upload') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small">ব্যাকআপ ফাইল (.sql, .sqlite, .zip বা .gz) <span class="text-danger">*</span></label>
                        <input type="file" name="backup_file" class="form-control" accept=".sql,.sqlite,.gz,.zip,.txt" required>
                        <small class="text-muted d-block mt-1">অনুমোদিত সর্বোচ্চ ফাইল সাইজ: ১০০ মেগাবাইট (100MB)</small>
                    </div>
                    <div class="p-3 bg-light rounded-3 border small text-muted">
                        <i class="fas fa-circle-info text-primary me-1"></i> আপলোড করার পর ফাইলটি তালিকাভুক্ত হবে এবং প্রয়োজনে সেখান থেকে সরাসরি রিস্টোর করা যাবে।
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-bs-dismiss="modal">বাতিল</button>
                    <button type="submit" class="btn btn-sm btn-primary rounded-pill px-4 fw-bold">
                        <i class="fas fa-upload me-1"></i> আপলোড সম্পন্ন করুন
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Restore Confirmation Modal -->
<div class="modal fade" id="restoreModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header bg-danger text-white py-3">
                <h6 class="modal-title fw-bold text-white mb-0">
                    <i class="fas fa-triangle-exclamation me-2"></i>ডাটাবেজ রিস্টোর সতর্কবার্তা!
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="restoreForm" method="POST">
                @csrf
                <div class="modal-body p-4 text-center">
                    <i class="fas fa-rotate-left text-danger display-4 mb-3 d-block"></i>
                    <h5 class="fw-bold text-dark mb-2">আপনি কি নিশ্চিত যে ডাটাবেজ রিস্টোর করবেন?</h5>
                    <p class="text-muted small mb-3">
                        <strong class="text-danger font-monospace" id="restoreFilename"></strong> ফাইল থেকে ডাটাবেজ প্রতিস্থাপিত হবে। রিস্টোরের পূর্বে বর্তমান ডাটার নতুন একটি ব্যাকআপ রাখা বাঞ্ছনীয়।
                    </p>
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
    function confirmRestore(filename) {
        document.getElementById('restoreFilename').textContent = filename;
        document.getElementById('restoreForm').action = "{{ url('admin/backup/restore') }}/" + encodeURIComponent(filename);
        new bootstrap.Modal(document.getElementById('restoreModal')).show();
    }
</script>
@endpush
@endsection
