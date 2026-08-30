@php
    $modalSettings = $settings ?? \App\Http\Controllers\Admin\IdeaAccountingController::getInvoiceSettings();
    $modalBizLogo = $modalSettings['logo'] ?? '/images/logo.png';
    $modalLogoSrc = \App\Support\SiteSetting::resolveImageUrl($modalBizLogo, 'images/logo.png') ?: asset('images/logo.png');
@endphp

{{-- Global Branding & Letterhead Settings Modal --}}
<div class="modal fade" id="ledgerBrandingSettingsModal" tabindex="-1" aria-labelledby="ledgerBrandingSettingsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow">
            <form action="{{ route('admin.accounting.settings.update') }}" method="POST" enctype="multipart/form-data" id="brandingSettingsForm">
                @csrf
                <input type="hidden" name="logo_base64" id="brandingLogoCroppedBase64">

                <div class="modal-header bg-dark text-white border-bottom py-3">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-primary rounded-circle p-2"><i class="fas fa-sliders fs-6 text-white"></i></span>
                        <h5 class="modal-title fw-bold mb-0 text-white" id="ledgerBrandingSettingsModalLabel">
                            লেজার, বিল ও স্টেটমেন্টের লোগো এবং অফিসিয়াল তথ্য কাস্টমাইজেশন
                        </h5>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4">
                    {{-- Live Preview Header Card --}}
                    <div class="card border border-primary-subtle rounded-3 p-3 mb-4 bg-light shadow-2xs">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small fw-bold text-primary text-uppercase">
                                <i class="fas fa-eye me-1"></i>মেমো ও লেজার হেডার লাইভ প্রিভিউ (Live Preview):
                            </span>
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">রিয়েল-টাইম আপডেট</span>
                        </div>
                        <div class="d-flex align-items-center gap-3 p-3 bg-white rounded border">
                            <img src="{{ $modalLogoSrc }}" id="brandingLivePreviewLogo" alt="Logo Preview" style="height: 52px; width: 120px; aspect-ratio: 2/1; object-fit: contain;" class="rounded border p-1 bg-light">
                            <div class="flex-fill">
                                <h5 class="fw-bold text-dark mb-0" id="brandingLivePreviewName">{{ $modalSettings['business_name'] ?? 'আইডিয়া প্রকাশন' }}</h5>
                                <div class="text-muted small fw-medium" id="brandingLivePreviewTagline">{{ $modalSettings['tagline'] ?? 'Book Publication, Printing, Binding & Distribution' }}</div>
                                <div class="text-secondary small mt-1" id="brandingLivePreviewMeta" style="font-size: 11.5px;">
                                    {{ $modalSettings['address'] ?? '38 Banglabazar, Dhaka-1100, Bangladesh' }} · ফোন: {{ $modalSettings['phone'] ?? '+8801700000000' }} · ইমেইল: {{ $modalSettings['email'] ?? 'ideaprokashon@gmail.com' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 2:1 Aspect Ratio Logo Upload & Cropper Tool --}}
                    <div class="card border border-info-subtle rounded-3 p-3 mb-4 bg-info-subtle bg-opacity-10">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label fw-bold text-dark mb-0">
                                <i class="fas fa-image text-info me-1.5"></i>প্রতিষ্ঠানের লোগো পরিবর্তন (Logo Upload & Wide Crop)
                            </label>
                            <span class="badge bg-info text-dark fw-bold font-monospace">2:1 Widescreen</span>
                        </div>

                        <input type="file" id="brandingLogoFileInput" class="form-control mb-2" accept="image/*">
                        <div class="form-text small text-muted mb-3" style="font-size: 11.5px;">
                            <i class="fas fa-lightbulb text-warning me-1"></i>কম্পিউটার বা মোবাইল থেকে লোগো সিলেক্ট করুন। স্বয়ংক্রিয়ভাবে জুম ও পজিশন করে কাঙ্ক্ষিত সাইজে বসিয়ে নিতে পারবেন।
                        </div>

                        <div id="brandingCropperContainer" class="d-none">
                            <div class="row g-3 align-items-center">
                                <div class="col-md-7">
                                    <div class="position-relative bg-dark rounded-3 overflow-hidden d-flex align-items-center justify-content-center" 
                                         style="height: 160px; width: 100%; border: 2px dashed #0ea5e9; cursor: grab;" id="brandingCropDragArea">
                                        <canvas id="brandingCropCanvas" width="360" height="180" class="w-100 h-100" style="object-fit: contain;"></canvas>
                                    </div>
                                    <div class="d-flex align-items-center gap-2 mt-2">
                                        <i class="fas fa-magnifying-glass-minus text-muted small"></i>
                                        <input type="range" class="form-range" id="brandingCropZoomSlider" min="0.3" max="3.5" step="0.02" value="1">
                                        <i class="fas fa-magnifying-glass-plus text-muted small"></i>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="brandingResetCrop()" title="রিসেট">
                                            <i class="fas fa-rotate-left"></i>
                                        </button>
                                    </div>
                                    <small class="text-muted d-block mt-1" style="font-size: 11px;">
                                        <i class="fas fa-hand me-1"></i>ড্র্যাগ করে লোগোর পজিশন সরান এবং স্লাইডার দিয়ে জুম ইন/আউট করুন।
                                    </small>
                                </div>
                                <div class="col-md-5 text-center">
                                    <span class="small fw-semibold text-muted d-block mb-1">ক্রপ প্রিভিউ (2:1 Widescreen):</span>
                                    <div class="p-2 bg-white rounded border d-inline-block shadow-xs">
                                        <img id="brandingCroppedResultThumb" src="{{ $modalLogoSrc }}" style="height: 60px; width: 120px; aspect-ratio: 2/1; object-fit: contain;" class="rounded">
                                    </div>
                                    <div class="text-success small fw-bold mt-1.5"><i class="fas fa-check-circle me-1"></i>লোগো ক্রপ প্রস্তুত</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Form Fields --}}
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">প্রতিষ্ঠানের নাম (Business / Org Name): <span class="text-danger">*</span></label>
                            <input type="text" name="business_name" id="brandingInputName" class="form-control" required value="{{ $modalSettings['business_name'] ?? 'আইডিয়া প্রকাশন' }}" oninput="brandingUpdateLivePreview()">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">ট্যাগলাইন / বিবরণ (Tagline / Slogan):</label>
                            <input type="text" name="tagline" id="brandingInputTagline" class="form-control" value="{{ $modalSettings['tagline'] ?? '' }}" placeholder="যেমন: বুক পাবলিকেশন, প্রিন্টিং ও ডিস্ট্রিবিউশন" oninput="brandingUpdateLivePreview()">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">অফিসিয়াল মোবাইল / ফোন নম্বর:</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                <input type="text" name="phone" id="brandingInputPhone" class="form-control font-monospace" value="{{ $modalSettings['phone'] ?? '' }}" placeholder="+88017XXXXXXXX" oninput="brandingUpdateLivePreview()">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">অফিসিয়াল ইমেইল ঠিকানা:</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input type="email" name="email" id="brandingInputEmail" class="form-control" value="{{ $modalSettings['email'] ?? '' }}" placeholder="info@ideaabd.com" oninput="brandingUpdateLivePreview()">
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold text-dark">অফিস ও শোরুমের পূর্ণাঙ্গ ঠিকানা (Office Address):</label>
                            <textarea name="address" id="brandingInputAddress" class="form-control" rows="2" placeholder="৩৮ বাংলাবাজার, ঢাকা-১১০০, বাংলাদেশ" oninput="brandingUpdateLivePreview()">{{ $modalSettings['address'] ?? '' }}</textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold text-dark">বিল ও লেজারের সাধারণ শর্তাবলী ও পেমেন্ট নির্দেশিকা (Terms & Note):</label>
                            <textarea name="terms_and_conditions" class="form-control" rows="2" placeholder="বকেয়া অর্থ সরাসরি ব্যাংক ট্রান্সফার, বিকাশ অথবা অফিসে পরিশোধ করে পাকা রসিদ গ্রহণ করুন...">{{ $modalSettings['terms_and_conditions'] ?? '' }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light p-3 d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">বাতিল</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-xs">
                        <i class="fas fa-check me-1.5"></i> পরিবর্তন সংরক্ষণ করুন
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Live update preview text
    function brandingUpdateLivePreview() {
        const nameVal = document.getElementById('brandingInputName')?.value || 'প্রতিষ্ঠানের নাম';
        const taglineVal = document.getElementById('brandingInputTagline')?.value || '';
        const phoneVal = document.getElementById('brandingInputPhone')?.value || '';
        const emailVal = document.getElementById('brandingInputEmail')?.value || '';
        const addressVal = document.getElementById('brandingInputAddress')?.value || '';

        const nameEl = document.getElementById('brandingLivePreviewName');
        const taglineEl = document.getElementById('brandingLivePreviewTagline');
        const metaEl = document.getElementById('brandingLivePreviewMeta');

        if (nameEl) nameEl.textContent = nameVal;
        if (taglineEl) taglineEl.textContent = taglineVal;
        if (metaEl) {
            let parts = [];
            if (addressVal) parts.push(addressVal);
            if (phoneVal) parts.push('ফোন: ' + phoneVal);
            if (emailVal) parts.push('ইমেইল: ' + emailVal);
            metaEl.textContent = parts.join(' · ');
        }
    }

    // Logo Cropper Engine (2:1 Widescreen)
    let brandingRawImage = new Image();
    let brandingImageLoaded = false;
    let brandingCropScale = 1;
    let brandingCropX = 0;
    let brandingCropY = 0;
    let brandingIsDragging = false;
    let brandingDragStartX = 0;
    let brandingDragStartY = 0;

    const brandCanvas = document.getElementById('brandingCropCanvas');
    const brandCtx = brandCanvas ? brandCanvas.getContext('2d') : null;
    const brandFileInput = document.getElementById('brandingLogoFileInput');
    const brandContainer = document.getElementById('brandingCropperContainer');
    const brandZoomSlider = document.getElementById('brandingCropZoomSlider');
    const brandDragArea = document.getElementById('brandingCropDragArea');
    const brandResultThumb = document.getElementById('brandingCroppedResultThumb');
    const brandBase64Input = document.getElementById('brandingLogoCroppedBase64');
    const brandLivePreviewImg = document.getElementById('brandingLivePreviewLogo');

    if (brandFileInput) {
        brandFileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = function(evt) {
                brandingRawImage = new Image();
                brandingRawImage.onload = function() {
                    brandingImageLoaded = true;
                    if (brandContainer) brandContainer.classList.remove('d-none');
                    brandingResetCrop();
                };
                brandingRawImage.src = evt.target.result;
            };
            reader.readAsDataURL(file);
        });
    }

    function renderBrandingCrop() {
        if (!brandingImageLoaded || !brandCtx || !brandCanvas) return;
        
        brandCtx.clearRect(0, 0, brandCanvas.width, brandCanvas.height);
        brandCtx.fillStyle = '#ffffff';
        brandCtx.fillRect(0, 0, brandCanvas.width, brandCanvas.height);
        
        const drawW = brandingRawImage.width * brandingCropScale;
        const drawH = brandingRawImage.height * brandingCropScale;
        
        brandCtx.drawImage(brandingRawImage, brandingCropX, brandingCropY, drawW, drawH);
        
        const dataUrl = brandCanvas.toDataURL('image/png', 0.95);
        if (brandBase64Input) brandBase64Input.value = dataUrl;
        if (brandResultThumb) brandResultThumb.src = dataUrl;
        if (brandLivePreviewImg) brandLivePreviewImg.src = dataUrl;
    }

    if (brandZoomSlider) {
        brandZoomSlider.addEventListener('input', function() {
            const prevScale = brandingCropScale;
            brandingCropScale = parseFloat(this.value);
            
            const centerX = brandCanvas.width / 2;
            const centerY = brandCanvas.height / 2;
            brandingCropX = centerX - ((centerX - brandingCropX) / prevScale) * brandingCropScale;
            brandingCropY = centerY - ((centerY - brandingCropY) / prevScale) * brandingCropScale;
            
            renderBrandingCrop();
        });
    }

    if (brandDragArea) {
        brandDragArea.addEventListener('mousedown', function(e) {
            brandingIsDragging = true;
            brandingDragStartX = e.clientX - brandingCropX;
            brandingDragStartY = e.clientY - brandingCropY;
            brandDragArea.style.cursor = 'grabbing';
        });

        window.addEventListener('mousemove', function(e) {
            if (!brandingIsDragging) return;
            brandingCropX = e.clientX - brandingDragStartX;
            brandingCropY = e.clientY - brandingDragStartY;
            renderBrandingCrop();
        });

        window.addEventListener('mouseup', function() {
            if (brandingIsDragging) {
                brandingIsDragging = false;
                brandDragArea.style.cursor = 'grab';
            }
        });

        brandDragArea.addEventListener('touchstart', function(e) {
            if (e.touches.length === 1) {
                brandingIsDragging = true;
                brandingDragStartX = e.touches[0].clientX - brandingCropX;
                brandingDragStartY = e.touches[0].clientY - brandingCropY;
            }
        }, {passive: true});

        window.addEventListener('touchmove', function(e) {
            if (!brandingIsDragging || e.touches.length !== 1) return;
            brandingCropX = e.touches[0].clientX - brandingDragStartX;
            brandingCropY = e.touches[0].clientY - brandingCropY;
            renderBrandingCrop();
        }, {passive: true});

        window.addEventListener('touchend', function() {
            brandingIsDragging = false;
        });
    }

    function brandingResetCrop() {
        if (!brandingImageLoaded || !brandCanvas) return;
        const scaleW = brandCanvas.width / brandingRawImage.width;
        const scaleH = brandCanvas.height / brandingRawImage.height;
        brandingCropScale = Math.max(scaleW, scaleH);
        if (brandZoomSlider) brandZoomSlider.value = brandingCropScale.toFixed(2);
        brandingCropX = (brandCanvas.width - brandingRawImage.width * brandingCropScale) / 2;
        brandingCropY = (brandCanvas.height - brandingRawImage.height * brandingCropScale) / 2;
        renderBrandingCrop();
    }
</script>
