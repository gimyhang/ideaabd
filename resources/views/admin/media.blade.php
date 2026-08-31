@extends('layouts.admin')

@section('title', 'Media & Asset Library')
@section('heading', 'মিডিয়া ও অ্যাসেট লাইব্রেরি')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">ড্যাশবোর্ড</a></li>
    <li class="breadcrumb-item active">মিডিয়া লাইব্রেরি</li>
@endsection

@section('actions')
    <button type="button" class="btn btn-primary btn-sm rounded-pill px-4 shadow-sm fw-bold" data-bs-toggle="modal" data-bs-target="#uploadMediaModal">
        <i class="fas fa-cloud-arrow-up me-1.5"></i> নতুন মিডিয়া আপলোড
    </button>
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

    <!-- Storage Statistics Card -->
    <div class="row g-3">
        <div class="col-12 col-md-4">
            <div class="card bg-white rounded-4 shadow-sm border-0 p-3.5 border-start border-4 border-primary h-100">
                <span class="small text-muted fw-semibold mb-1">মোট মিডিয়া ফাইল</span>
                <h4 class="text-dark fw-bold mb-1">{{ number_format($totalCount) }} টি</h4>
                <p class="text-muted small mb-0">ছবি, ব্যানার, কাভার ও লোগো</p>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card bg-white rounded-4 shadow-sm border-0 p-3.5 border-start border-4 border-info h-100">
                <span class="small text-muted fw-semibold mb-1">স্টোরেজ সাইজ</span>
                <h4 class="text-dark fw-bold font-monospace mb-1">{{ $totalFormatted }}</h4>
                <p class="text-muted small mb-0">পাবলিক ডিস্ক ও স্টোরেজ মেমোরি</p>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card bg-white rounded-4 shadow-sm border-0 p-3.5 border-start border-4 border-success h-100">
                <span class="small text-muted fw-semibold mb-1">ফিল্টার ফোল্ডার</span>
                <h4 class="text-dark fw-bold mb-1 text-capitalize">{{ $folderFilter }}</h4>
                <p class="text-muted small mb-0">নিচে নির্দিষ্ট ক্যাটাগরি ফিল্টার করুন</p>
            </div>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="card bg-white rounded-4 shadow-sm border-0 p-3">
        <form method="GET" action="{{ route('admin.media.index') }}" class="row g-2 align-items-center">
            <div class="col-12 col-md-5">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-search"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="ফাইলের নাম লিখে খুঁজুন..." value="{{ $search }}">
                </div>
            </div>
            <div class="col-6 col-md-4">
                <select name="folder" class="form-select form-select-sm rounded-3" onchange="this.form.submit()">
                    <option value="all" @selected($folderFilter === 'all')>📁 সকল ফোল্ডার (All Folders)</option>
                    <option value="covers" @selected($folderFilter === 'covers')>📚 বইয়ের কাভার (Book Covers)</option>
                    <option value="banners" @selected($folderFilter === 'banners')>🖼️ ব্যানার ও স্লাইডার (Banners)</option>
                    <option value="settings" @selected($folderFilter === 'settings')>⚙️ সেটিংস ও লোগো (Settings & Logos)</option>
                    <option value="qrcodes" @selected($folderFilter === 'qrcodes')>📱 QR কোড ইমেজ (Payment QRs)</option>
                    <option value="authors" @selected($folderFilter === 'authors')>✍️ লেখক ছবি (Author Photos)</option>
                </select>
            </div>
            <div class="col-6 col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary flex-fill fw-semibold rounded-pill">সার্চ</button>
                <a href="{{ route('admin.media.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill" title="রিসেট"><i class="fas fa-rotate-left"></i></a>
            </div>
        </form>
    </div>

    <!-- Media Grid -->
    <div class="row g-3">
        @forelse($mediaItems as $item)
            <div class="col-6 col-sm-4 col-md-3 col-xl-2">
                <div class="card bg-white rounded-4 shadow-2xs border overflow-hidden h-100 position-relative group-media">
                    <div class="d-flex align-items-center justify-content-center bg-light p-2" style="height: 140px;">
                        <img src="{{ $item['url'] }}" alt="{{ $item['filename'] }}" class="img-fluid rounded" style="max-height: 100%; object-fit: contain;">
                    </div>
                    <div class="p-2.5">
                        <div class="small fw-bold text-dark text-truncate" title="{{ $item['filename'] }}">{{ $item['filename'] }}</div>
                        <div class="d-flex align-items-center justify-content-between text-muted" style="font-size: 11px;">
                            <span class="badge bg-light text-dark border px-1.5 py-0.5 text-uppercase">{{ $item['ext'] }}</span>
                            <span class="font-monospace">{{ $item['size'] }}</span>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-top p-1.5 d-flex align-items-center justify-content-between">
                        <button type="button" class="btn btn-xs btn-outline-primary border-0 p-1" onclick="copyUrl('{{ $item['url'] }}')" title="URL কপি করুন">
                            <i class="fa-regular fa-copy"></i>
                        </button>
                        <a href="{{ $item['url'] }}" target="_blank" class="btn btn-xs btn-outline-secondary border-0 p-1" title="পূর্ণাঙ্গ দেখুন">
                            <i class="fas fa-arrow-up-right-from-square"></i>
                        </a>
                        <form action="{{ route('admin.media.destroy') }}" method="POST" onsubmit="return confirm('এই ছবিটি মুছে ফেলতে চান?');" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="path" value="{{ $item['path'] }}">
                            <button type="submit" class="btn btn-xs btn-outline-danger border-0 p-1" title="মুছে ফেলুন">
                                <i class="fas fa-trash-can"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card bg-white rounded-4 p-5 text-center text-muted border-0 shadow-sm">
                    <i class="fas fa-images fs-1 text-secondary mb-3"></i>
                    <h5>কোনো মিডিয়া ফাইল পাওয়া যায়নি</h5>
                    <p class="small mb-3">উপরে <strong>নতুন মিডিয়া আপলোড</strong> বাটনে চাপ দিয়ে ছবি যোগ করুন।</p>
                </div>
            </div>
        @endforelse
    </div>

</div>

<!-- Upload Media Modal -->
<div class="modal fade" id="uploadMediaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header border-bottom py-3 px-4">
                <h6 class="modal-title fw-bold text-dark"><i class="fas fa-cloud-arrow-up text-primary me-2"></i> নতুন মিডিয়া আপলোড</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.media.upload') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">টার্গেট ফোল্ডার</label>
                        <select name="folder" class="form-select form-select-sm rounded-3">
                            <option value="banners">🖼️ ব্যানার ও স্লাইডার (images/banners)</option>
                            <option value="settings">⚙️ সেটিংস ও ব্র্যান্ডিং (images/settings)</option>
                            <option value="qrcodes">📱 পেমেন্ট QR কোড (settings/qrcodes)</option>
                            <option value="general">📁 সাধারণ মিডিয়া (images/general)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">ছবি সিলেক্ট করুন (JPG, PNG, WEBP, SVG)</label>
                        <input type="file" name="file" class="form-control rounded-3" accept="image/*" required>
                        <small class="text-muted" style="font-size:11px;">সর্বোচ্চ সাইজ: 5MB</small>
                    </div>
                </div>
                <div class="modal-footer border-top py-2.5 px-4">
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">বাতিল</button>
                    <button type="submit" class="btn btn-sm btn-primary rounded-pill px-4 fw-bold">আপলোড সম্পন্ন করুন</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function copyUrl(url) {
        navigator.clipboard.writeText(url).then(() => {
            alert('ইমেজ লিঙ্ক কপি হয়েছে!\n' + url);
        });
    }
</script>
@endsection
