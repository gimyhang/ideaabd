@extends('layouts.admin')

@section('title', 'মিডিয়া ও অ্যাসেট লাইব্রেরি — Media & Asset Library')
@section('heading', 'মিডিয়া ও অ্যাসেট লাইব্রেরি (Media & Asset Library)')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">মিডিয়া লাইব্রেরি</li>
@endsection

@section('actions')
    <div class="d-flex align-items-center gap-2 flex-wrap">
        {{-- 1-Click Auto Optimizer Engine --}}
        <button type="button" class="btn btn-outline-success btn-sm rounded-pill px-3 fw-bold shadow-xs d-inline-flex align-items-center gap-1.5" id="btnOptimizeAll" onclick="runMediaOptimization(this)">
            <i class="fas fa-bolt text-success"></i>
            <span>অটো-অপ্টিমাইজেশন চালান</span>
        </button>

        {{-- Upload Modal Trigger --}}
        <button type="button" class="btn btn-primary btn-sm rounded-pill px-3.5 shadow-sm fw-bold d-inline-flex align-items-center gap-1.5" data-bs-toggle="modal" data-bs-target="#uploadMediaModal">
            <i class="fas fa-cloud-arrow-up"></i>
            <span>নতুন মিডিয়া আপলোড</span>
        </button>
    </div>
@endsection

@push('styles')
<style>
    .media-card {
        transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        border-radius: 14px;
        overflow: hidden;
    }
    .media-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 22px rgba(15, 23, 42, 0.08);
        border-color: #3b82f6;
    }
    .media-thumb-box {
        height: 150px;
        background: #f8fafc;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        cursor: pointer;
        position: relative;
    }
    .media-thumb-img {
        max-height: 100%;
        max-width: 100%;
        object-fit: contain;
        transition: transform 0.25s ease;
    }
    .media-card:hover .media-thumb-img {
        transform: scale(1.05);
    }
    .media-pill {
        font-size: 0.8rem;
        font-weight: 600;
        padding: 5px 14px;
        border-radius: 50rem;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        color: #475569;
        text-decoration: none;
        transition: all 0.15s ease;
    }
    .media-pill:hover, .media-pill.active {
        background: #0f172a;
        color: #ffffff;
        border-color: #0f172a;
        box-shadow: 0 2px 6px rgba(15, 23, 42, 0.15);
    }
    .media-dragzone {
        border: 2px dashed #cbd5e1;
        border-radius: 12px;
        background: #f8fafc;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .media-dragzone:hover, .media-dragzone.dragover {
        border-color: #3b82f6;
        background: #eff6ff;
    }
</style>
@endpush

@section('content')
<div class="d-flex flex-column gap-3.5 pb-4">

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-0 rounded-4 shadow-xs border-0 border-start border-4 border-success bg-white py-2.5 px-3" role="alert">
            <i class="fas fa-circle-check text-success fs-5 me-2.5"></i>
            <div class="fw-semibold small text-dark">{{ session('success') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center mb-0 rounded-4 shadow-xs border-0 border-start border-4 border-danger bg-white py-2.5 px-3" role="alert">
            <i class="fas fa-triangle-exclamation text-danger fs-5 me-2.5"></i>
            <div class="fw-semibold small text-dark">{{ session('error') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Dynamic Live Alert Container -->
    <div id="mediaLiveAlert"></div>

    <!-- Storage Statistics 4-Card Grid -->
    <div class="row g-3">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card bg-white rounded-4 shadow-sm border-0 p-3.5 border-start border-4 border-primary h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="small text-muted fw-semibold">মোট মিডিয়া ফাইল</span>
                    <div class="rounded-circle bg-primary-subtle text-primary p-2 d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                        <i class="fas fa-images"></i>
                    </div>
                </div>
                <h3 class="text-dark fs-4 fw-bold mb-1 font-monospace" id="lblTotalCount">{{ number_format($totalCount) }} টি</h3>
                <p class="text-muted small mb-0">ছবি, ব্যানার, কাভার ও লোগো</p>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card bg-white rounded-4 shadow-sm border-0 p-3.5 border-start border-4 border-info h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="small text-muted fw-semibold">মোট স্টোরেজ সাইজ</span>
                    <div class="rounded-circle bg-info-subtle text-info p-2 d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                        <i class="fas fa-hard-drive"></i>
                    </div>
                </div>
                <h3 class="text-dark fs-4 fw-bold font-monospace mb-1" id="lblTotalSize">{{ $totalFormatted }}</h3>
                <p class="text-muted small mb-0">পাবলিক ডিস্ক ও স্টোরেজ মেমোরি</p>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card bg-white rounded-4 shadow-sm border-0 p-3.5 border-start border-4 border-success h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="small text-muted fw-semibold">অটো-অপ্টিমাইজার</span>
                    <div class="rounded-circle bg-success-subtle text-success p-2 d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                        <i class="fas fa-bolt"></i>
                    </div>
                </div>
                <h3 class="text-success fs-5 fw-bold mb-1 d-flex align-items-center gap-1.5">
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1">সক্রিয় (GD Engine)</span>
                </h3>
                <p class="text-muted small mb-0">আপলোডে স্বয়ংক্রিয় সাইজ অপ্টিমাইজ</p>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card bg-white rounded-4 shadow-sm border-0 p-3.5 border-start border-4 border-warning h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="small text-muted fw-semibold">বর্তমান ফোল্ডার</span>
                    <div class="rounded-circle bg-warning-subtle text-warning-emphasis p-2 d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                        <i class="fas fa-folder-open"></i>
                    </div>
                </div>
                <h3 class="text-dark fs-5 fw-bold mb-1 text-capitalize">
                    {{ $folderFilter === 'all' ? 'সকল ফোল্ডার' : $folderFilter }}
                </h3>
                <p class="text-muted small mb-0">নিচে নির্দিষ্ট ক্যাটাগরি ফিল্টার করুন</p>
            </div>
        </div>
    </div>

    <!-- Filter Pills & Live Search Bar -->
    <div class="card bg-white rounded-4 shadow-sm border-0 p-3">
        <div class="row g-2 align-items-center">
            <div class="col-12 col-lg-5">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-end-0 text-primary"><i class="fas fa-search"></i></span>
                    <input type="search" id="mediaLiveSearch" class="form-control border-start-0 ps-0 fw-semibold" 
                           placeholder="লাইভ খুঁজুন (ফাইলের নাম বা এক্সটেনশন টাইপ করুন)..." 
                           value="{{ $search }}" oninput="filterMediaCards(this.value)">
                </div>
            </div>
            <div class="col-12 col-lg-7">
                <div class="d-flex align-items-center gap-1.5 overflow-auto pb-1" style="scrollbar-width: thin;">
                    <a href="{{ route('admin.media.index', ['folder' => 'all']) }}" class="media-pill {{ $folderFilter === 'all' ? 'active' : '' }}">
                        📁 সকল ফোল্ডার
                    </a>
                    <a href="{{ route('admin.media.index', ['folder' => 'covers']) }}" class="media-pill {{ $folderFilter === 'covers' ? 'active' : '' }}">
                        📚 বইয়ের কাভার
                    </a>
                    <a href="{{ route('admin.media.index', ['folder' => 'banners']) }}" class="media-pill {{ $folderFilter === 'banners' ? 'active' : '' }}">
                        🖼️ ব্যানার
                    </a>
                    <a href="{{ route('admin.media.index', ['folder' => 'settings']) }}" class="media-pill {{ $folderFilter === 'settings' ? 'active' : '' }}">
                        ⚙️ সেটিংস ও লোগো
                    </a>
                    <a href="{{ route('admin.media.index', ['folder' => 'qrcodes']) }}" class="media-pill {{ $folderFilter === 'qrcodes' ? 'active' : '' }}">
                        📱 QR কোড
                    </a>
                    <a href="{{ route('admin.media.index', ['folder' => 'authors']) }}" class="media-pill {{ $folderFilter === 'authors' ? 'active' : '' }}">
                        ✍️ লেখক ছবি
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Media Grid -->
    <div class="row g-3" id="mediaGrid">
        @forelse($mediaItems as $item)
            <div class="col-6 col-sm-4 col-md-3 col-xl-2 media-item" data-filename="{{ strtolower($item['filename']) }}" data-ext="{{ strtolower($item['ext']) }}" data-folder="{{ $item['folder'] }}">
                <div class="card media-card h-100 position-relative">
                    <div class="media-thumb-box" onclick="openLightbox('{{ $item['url'] }}', '{{ addslashes($item['filename']) }}', '{{ $item['size'] }}', '{{ $item['updated_at']->format('d M, Y h:i A') }}')">
                        <img src="{{ $item['url'] }}" alt="{{ $item['filename'] }}" class="media-thumb-img" loading="lazy">
                    </div>
                    <div class="p-2.5">
                        <div class="small fw-bold text-dark text-truncate mb-1" title="{{ $item['filename'] }}">{{ $item['filename'] }}</div>
                        <div class="d-flex align-items-center justify-content-between text-muted" style="font-size: 11px;">
                            <span class="badge bg-light text-dark border px-1.5 py-0.5 text-uppercase font-monospace">{{ $item['ext'] }}</span>
                            <span class="font-monospace fw-semibold">{{ $item['size'] }}</span>
                        </div>
                    </div>
                    <div class="card-footer bg-light border-top p-1.5 d-flex align-items-center justify-content-between">
                        <button type="button" class="btn btn-xs btn-outline-primary rounded-pill px-2 py-0.5" onclick="copyUrl('{{ $item['url'] }}')" title="URL কপি করুন">
                            <i class="fa-regular fa-copy me-1"></i> কপি
                        </button>
                        <div class="d-flex align-items-center gap-1">
                            <a href="{{ $item['url'] }}" target="_blank" class="btn btn-xs btn-outline-secondary border-0 p-1" title="পূর্ণাঙ্গ ব্রাউজারে খুলুন">
                                <i class="fas fa-arrow-up-right-from-square"></i>
                            </a>
                            <form action="{{ route('admin.media.destroy') }}" method="POST"
                                  data-confirm="আপনি কি নিশ্চিত এই মিডিয়া ছবিটি মুছে ফেলতে চান?"
                                  data-confirm-title="মিডিয়া ফাইল অপসারণ"
                                  data-confirm-icon="warning"
                                  data-confirm-btn="<i class='fas fa-trash-can me-1'></i> মুছে ফেলুন"
                                  class="d-inline">
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
            </div>
        @empty
            <div class="col-12" id="emptyMediaNotice">
                <div class="card bg-white rounded-4 p-5 text-center text-muted border-0 shadow-sm">
                    <i class="fas fa-images fs-1 text-secondary mb-3 opacity-50"></i>
                    <h5 class="text-dark fw-bold">কোনো মিডিয়া ফাইল পাওয়া যায়নি</h5>
                    <p class="small mb-3">উপরে <strong>নতুন মিডিয়া আপলোড</strong> বাটনে চাপ দিয়ে ছবি যোগ করুন।</p>
                </div>
            </div>
        @endforelse
    </div>

    <!-- No Search Results Placeholder -->
    <div id="noSearchMatchNotice" class="d-none col-12">
        <div class="card bg-white rounded-4 p-5 text-center text-muted border-0 shadow-sm">
            <i class="fas fa-search fs-1 text-secondary mb-3 opacity-50"></i>
            <h5 class="text-dark fw-bold">কোনো ফলাফল মেলেনি</h5>
            <p class="small mb-0">ভিন্ন কোনো ফাইলের নাম বা এক্সটেনশন দিয়ে অনুসন্ধান করুন।</p>
        </div>
    </div>

</div>

<!-- Upload Media Modal with Drag & Drop -->
<div class="modal fade" id="uploadMediaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header border-bottom py-3 px-4 bg-light rounded-top-4">
                <h6 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                    <i class="fas fa-cloud-arrow-up text-primary"></i>
                    <span>নতুন মিডিয়া আপলোড ও অটো-অপ্টিমাইজ</span>
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.media.upload') }}" method="POST" enctype="multipart/form-data" id="uploadMediaForm">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">টার্গেট ফোল্ডার</label>
                        <select name="folder" class="form-select form-select-sm rounded-3 fw-semibold">
                            <option value="banners">🖼️ ব্যানার ও স্লাইডার (images/banners)</option>
                            <option value="settings">⚙️ সেটিংস ও ব্র্যান্ডিং (images/settings)</option>
                            <option value="qrcodes">📱 পেমেন্ট QR কোড (settings/qrcodes)</option>
                            <option value="general">📁 সাধারণ মিডিয়া (images/general)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">ছবি নির্বাচন (JPG, PNG, WEBP, SVG)</label>
                        <div class="media-dragzone p-4 text-center" onclick="document.getElementById('mediaFileInput').click()">
                            <i class="fas fa-cloud-arrow-up fs-2 text-primary mb-2 d-block"></i>
                            <div class="fw-bold text-dark small mb-0.5">ছবি ড্র্যাগ করুন অথবা ক্লিক করে নির্বাচন করুন</div>
                            <small class="text-muted" style="font-size:11px;">সর্বোচ্চ সাইজ: 5MB | স্বয়ংক্রিয়ভাবে ওয়েব অপ্টিমাইজড হবে</small>
                            <input type="file" name="file" id="mediaFileInput" class="d-none" accept="image/*" required onchange="handleFilePreview(this)">
                        </div>
                    </div>

                    {{-- Image Live Preview Box in Modal --}}
                    <div id="modalImagePreviewBox" class="d-none p-2 bg-light border rounded-3 text-center mb-2">
                        <img id="modalPreviewImg" src="" alt="Preview" class="img-fluid rounded" style="max-height: 180px; object-fit: contain;">
                        <div class="small text-muted mt-1 font-monospace" id="modalPreviewInfo"></div>
                    </div>
                </div>
                <div class="modal-footer border-top py-2.5 px-4 bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">বাতিল</button>
                    <button type="submit" class="btn btn-sm btn-primary rounded-pill px-4 fw-bold">
                        <i class="fas fa-upload me-1"></i> আপলোড ও অপ্টিমাইজ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Full Lightbox Modal -->
<div class="modal fade" id="lightboxModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <div class="modal-header border-bottom py-2.5 px-4 bg-dark text-white">
                <h6 class="modal-title fw-bold text-white small text-truncate" id="lightboxTitle">Image Preview</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3 bg-dark d-flex align-items-center justify-content-center" style="min-height: 350px;">
                <img id="lightboxImage" src="" alt="Full Preview" class="img-fluid rounded shadow" style="max-height: 70vh; object-fit: contain;">
            </div>
            <div class="modal-footer border-top py-2.5 px-4 bg-light d-flex justify-content-between align-items-center">
                <div class="small text-muted font-monospace" id="lightboxMeta"></div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" id="lightboxCopyBtn" onclick="copyUrl(document.getElementById('lightboxImage').src)">
                        <i class="fa-regular fa-copy me-1"></i> URL কপি
                    </button>
                    <a href="" target="_blank" class="btn btn-sm btn-primary rounded-pill px-3 fw-bold" id="lightboxOpenBtn">
                        <i class="fas fa-arrow-up-right-from-square me-1"></i> সরাসরি লিংক
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // 1. Dynamic Live Search Filter
    function filterMediaCards(query) {
        const q = query.toLowerCase().trim();
        const items = document.querySelectorAll('.media-item');
        let visibleCount = 0;

        items.forEach(el => {
            const filename = el.getAttribute('data-filename') || '';
            const ext = el.getAttribute('data-ext') || '';
            if (!q || filename.includes(q) || ext.includes(q)) {
                el.classList.remove('d-none');
                visibleCount++;
            } else {
                el.classList.add('d-none');
            }
        });

        const noMatch = document.getElementById('noSearchMatchNotice');
        if (noMatch) {
            noMatch.classList.toggle('d-none', visibleCount > 0 || items.length === 0);
        }
    }

    // 2. 1-Click Copy Public URL with Toast
    function copyUrl(url) {
        navigator.clipboard.writeText(url).then(() => {
            showMediaAlert('success', 'ইমেজ URL কপি হয়েছে: ' + url);
        }).catch(() => {
            prompt('নিচের URL টি কপি করুন:', url);
        });
    }

    // 3. Open Full Lightbox Preview
    function openLightbox(url, filename, size, date) {
        document.getElementById('lightboxImage').src = url;
        document.getElementById('lightboxTitle').textContent = filename;
        document.getElementById('lightboxMeta').textContent = `সাইজ: ${size} | আপলোড: ${date}`;
        document.getElementById('lightboxOpenBtn').href = url;

        const modalEl = document.getElementById('lightboxModal');
        if (modalEl) {
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        }
    }

    // 4. Modal File Preview on Select
    function handleFilePreview(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            const reader = new FileReader();
            reader.onload = function(e) {
                const box = document.getElementById('modalImagePreviewBox');
                const img = document.getElementById('modalPreviewImg');
                const info = document.getElementById('modalPreviewInfo');
                if (box && img) {
                    img.src = e.target.result;
                    if (info) info.textContent = `${file.name} (${(file.size / 1024).toFixed(1)} KB)`;
                    box.classList.remove('d-none');
                }
            };
            reader.readAsDataURL(file);
        }
    }

    // 5. Run 1-Click Batch Image Optimization via AJAX
    function runMediaOptimization(btn) {
        const origContent = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = `<span class="spinner-border spinner-border-sm me-1.5" role="status"></span><span>অপ্টিমাইজেশন চলছে...</span>`;

        fetch('{{ route('admin.media.optimize-all') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = origContent;
            if (data.success) {
                showMediaAlert('success', data.message);
                setTimeout(() => window.location.reload(), 2000);
            } else {
                showMediaAlert('danger', data.message || 'অপ্টিমাইজেশন ব্যর্থ হয়েছে।');
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = origContent;
            showMediaAlert('danger', 'সার্ভার রেসপন্স দিতে ব্যর্থ হয়েছে।');
        });
    }

    // 6. Dynamic Alert Banner
    function showMediaAlert(type, message) {
        const alertBox = document.getElementById('mediaLiveAlert');
        if (!alertBox) return;
        alertBox.innerHTML = `
            <div class="alert alert-${type} alert-dismissible fade show d-flex align-items-center mb-0 rounded-4 shadow-xs border-0 border-start border-4 border-${type} bg-white py-2.5 px-3" role="alert">
                <i class="fas ${type === 'success' ? 'fa-circle-check text-success' : 'fa-triangle-exclamation text-danger'} fs-5 me-2.5"></i>
                <div class="fw-semibold small text-dark">${message}</div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
    }
</script>
@endpush
@endsection
