@extends('layouts.admin')

@section('title', 'Database Backup & Recovery')
@section('heading', 'ডাটাবেজ ব্যাকআপ ও রিকভারি ম্যানেজমেন্ট')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">ড্যাশবোর্ড</a></li>
    <li class="breadcrumb-item active">ডাটাবেজ ব্যাকআপ</li>
@endsection

@section('actions')
    <form action="{{ route('admin.backup.create') }}" method="POST" class="d-inline">
        @csrf
        <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4 shadow-sm fw-bold">
            <i class="fas fa-database me-1.5"></i> নতুন ব্যাকআপ তৈরি করুন (Create Backup)
        </button>
    </form>
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

    <!-- Database Info Card -->
    <div class="row g-3">
        <div class="col-12 col-md-4">
            <div class="card bg-white rounded-4 shadow-sm border-0 p-3.5 border-start border-4 border-primary h-100">
                <span class="small text-muted fw-semibold mb-1">কানেক্টেড ডাটাবেজ</span>
                <h4 class="text-dark fw-bold font-monospace mb-1">{{ $dbName }}</h4>
                <p class="text-muted small mb-0">ড্রাইভার: <span class="badge bg-light text-dark border font-monospace">{{ $dbDriver }}</span></p>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card bg-white rounded-4 shadow-sm border-0 p-3.5 border-start border-4 border-success h-100">
                <span class="small text-muted fw-semibold mb-1">মোট ব্যাকআপ ফাইল</span>
                <h4 class="text-dark fw-bold mb-1">{{ count($backups) }} টি</h4>
                <p class="text-muted small mb-0">লোকেশন: <code>storage/app/backups/</code></p>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card bg-white rounded-4 shadow-sm border-0 p-3.5 border-start border-4 border-info h-100">
                <span class="small text-muted fw-semibold mb-1">সর্বশেষ ব্যাকআপ</span>
                <h4 class="text-dark fw-bold mb-1">
                    {{ !empty($backups[0]) ? $backups[0]['created_at']->diffForHumans() : 'নেই' }}
                </h4>
                <p class="text-muted small mb-0">{{ !empty($backups[0]) ? $backups[0]['created_at']->format('d M, Y h:i A') : '—' }}</p>
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
                                    <div class="d-flex align-items-center justify-content-end gap-2">
                                        <a href="{{ route('admin.backup.download', $b['filename']) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold">
                                            <i class="fas fa-download me-1"></i> ডাউনলোড
                                        </a>
                                        <form action="{{ route('admin.backup.destroy', $b['filename']) }}" method="POST" onsubmit="return confirm('আপনি কি নিশ্চিত এই ব্যাকআপ ফাইলটি মুছে ফেলতে চান?');" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-2.5" title="মুছে ফেলুন">
                                                <i class="fas fa-trash-can"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted">
                                    <i class="fas fa-database fs-2 mb-2 text-secondary"></i>
                                    <div>এখনো কোনো ডাটাবেজ ব্যাকআপ তৈরি করা হয়নি।</div>
                                    <div class="small mt-1">উপরে <strong>নতুন ব্যাকআপ তৈরি করুন</strong> বাটনে ক্লিক করুন।</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection
