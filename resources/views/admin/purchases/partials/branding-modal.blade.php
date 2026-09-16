@php
    $modalSettings = $settings ?? \App\Http\Controllers\Admin\IdeaAccountingController::getInvoiceSettings();
    $modalBizLogo = $modalSettings['logo'] ?? '/images/logo.png';
    $modalLogoSrc = \App\Support\SiteSetting::resolveImageUrl($modalBizLogo, 'images/logo.png') ?: asset('images/logo.png');
    $mRecipientNameSize = $modalSettings['challan_recipient_name_size'] ?? '13px';
    $mRecipientPhoneSize = $modalSettings['challan_recipient_phone_size'] ?? '12px';
    $mRecipientAddressSize = $modalSettings['challan_recipient_address_size'] ?? '11.5px';
    $mRecipientDesigSize = $modalSettings['challan_recipient_desig_size'] ?? '11.5px';
    $mRecipientOrgSize = $modalSettings['challan_recipient_org_size'] ?? '12px';
@endphp

{{-- Invoice & Memo Header Settings / Design Modal with 2:1 Cropper --}}
<div class="modal fade d-print-none" id="invoiceSettingsModal" tabindex="-1" aria-labelledby="invoiceSettingsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow">
            <form action="{{ route('admin.accounting.settings.update') }}" method="POST" enctype="multipart/form-data" id="settingsForm">
                @csrf
                <input type="hidden" name="logo_base64" id="logoCroppedBase64">

                <div class="modal-header border-bottom py-3 bg-white">
                    <h5 class="modal-title fw-bold text-primary mb-0" id="invoiceSettingsModalLabel">
                        <i class="fa-solid fa-palette me-2"></i>Purchases & Inventory Memo Branding Settings
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    
                    {{-- Live Preview Header Card --}}
                    <div class="card border rounded-3 p-3 mb-4 bg-light">
                        <span class="small fw-bold text-muted text-uppercase mb-2 d-block"><i class="fa-solid fa-eye me-1 text-primary"></i>Purchase Invoice & Memo Header Live Preview:</span>
                        <div class="d-flex align-items-center gap-3 p-2 bg-white rounded border">
                            <img src="{{ $modalLogoSrc }}" id="previewHeaderLogo" alt="Logo Preview" style="height: 48px; width: 96px; aspect-ratio: 2/1; object-fit: contain; flex-shrink: 0; margin-right: 6px;">
                            <div class="d-flex flex-column justify-content-center" style="line-height: 1.35; padding-left: 2px;">
                                <div class="fw-bold text-primary mb-0" id="previewHeaderTitle" style="font-size: 15.5px;">{{ $modalSettings['business_name'] ?? 'Idea Publication' }}</div>
                                <div class="text-muted small mb-0" id="previewHeaderTagline" style="font-size: 10.5px;">{{ $modalSettings['tagline'] ?? 'Book Publication, Printing & Distribution' }}</div>
                                <div class="text-muted small mt-0.5" id="previewHeaderMeta" style="font-size: 10px;">
                                    <span><i class="fa-solid fa-location-dot me-0.5 text-danger"></i><span id="previewMetaAddr">{{ $modalSettings['address'] ?? 'Dhaka, Bangladesh' }}</span></span>
                                    <span class="mx-1 text-muted">·</span>
                                    <span><i class="fa-solid fa-phone me-0.5 text-primary"></i><span id="previewMetaPhone">{{ $modalSettings['phone'] ?? '018XXXXXXXX' }}</span></span>
                                    <span class="mx-1 text-muted">·</span>
                                    <span><i class="fa-solid fa-envelope me-0.5 text-primary"></i><span id="previewMetaEmail">{{ $modalSettings['email'] ?? 'info@ideaabd.com' }}</span></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 2:1 Aspect Ratio Logo Cropper Tool --}}
                    <div class="card border border-primary-subtle rounded-3 p-3 mb-4 bg-primary-subtle bg-opacity-10">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label fw-bold text-primary mb-0">
                                <i class="fa-solid fa-crop-simple me-1"></i> Logo Upload & 2:1 Wide Crop Tool
                            </label>
                            <span class="badge bg-primary text-white">Ratio 2:1 (Double Width)</span>
                        </div>
                        
                        <input type="file" id="logoFileInput" class="form-control mb-3" accept="image/*">
                        
                        <div id="cropperContainer" class="d-none">
                            <div class="row g-3 align-items-center">
                                <div class="col-md-7">
                                    <div class="position-relative bg-dark rounded-3 overflow-hidden d-flex align-items-center justify-content-center" 
                                         style="height: 180px; width: 100%; border: 2px dashed #0d6efd; cursor: grab;" id="cropDragArea">
                                        <canvas id="cropCanvas" width="360" height="180" class="w-100 h-100" style="object-fit: contain;"></canvas>
                                    </div>
                                    <div class="d-flex align-items-center gap-2 mt-2">
                                        <i class="fa-solid fa-magnifying-glass-minus text-muted small"></i>
                                        <input type="range" class="form-range" id="cropZoomSlider" min="0.3" max="3.5" step="0.02" value="1">
                                        <i class="fa-solid fa-magnifying-glass-plus text-muted small"></i>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mt-1">
                                        <small class="text-muted"><i class="fa-solid fa-hand-pointer me-1"></i>Drag to reposition, slider to zoom</small>
                                        <button type="button" class="btn btn-sm btn-link text-decoration-none p-0" onclick="resetCrop()">Reset</button>
                                    </div>
                                </div>
                                <div class="col-md-5 text-center">
                                    <span class="small fw-semibold text-muted d-block mb-2">Crop Preview (2:1 Ratio):</span>
                                    <div class="p-2 border rounded-3 bg-white d-inline-block shadow-xs mb-2">
                                        <img id="cropperPreviewThumb" src="{{ $modalLogoSrc }}" alt="Live Crop Thumb" style="height: 50px; width: 100px; object-fit: contain;">
                                    </div>
                                    <div class="small text-success fw-semibold"><i class="fa-solid fa-circle-check me-1"></i>Logo ready to save</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Challan Destination & Recipient Typography Controls --}}
                    <div class="card border border-primary-subtle rounded-3 p-3 mb-3 bg-primary bg-opacity-10">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label fw-bold text-primary mb-0">
                                <i class="fa-solid fa-truck-ramp-box me-1"></i> Delivery & Supplier Typography
                            </label>
                            <span class="badge bg-primary text-white">Challan Typography</span>
                        </div>
                        <p class="small text-muted mb-3" style="font-size: 11px;">
                            Customize font sizes for Supplier / Recipient details on Purchase Memos & Challans.
                        </p>

                        {{-- Recipient Live Preview Box --}}
                        <div class="p-2.5 bg-white rounded-2 border mb-3 shadow-xs">
                            <div class="small fw-bold text-muted text-uppercase mb-1" style="font-size: 10px;">
                                <i class="fa-solid fa-eye me-1 text-primary"></i>Typography Live Preview:
                            </div>
                            <div class="p-2 bg-light rounded border" id="previewRecipientBox">
                                <div class="fw-bold text-dark mb-1" style="font-size: 11px;"><i class="fa-solid fa-truck me-1 text-primary"></i>Supplier / Destination Details:</div>
                                <div id="previewRecipientName" style="font-size: {{ $mRecipientNameSize }}; font-weight: bold; color: #0f172a;">Rahim Book House / Supplier Name</div>
                                <div id="previewRecipientDesig" class="text-muted" style="font-size: {{ $mRecipientDesigSize }};">Proprietor / Manager</div>
                                <div id="previewRecipientOrg" class="text-primary fw-semibold" style="font-size: {{ $mRecipientOrgSize }};">Anupam Publishing & Distributors</div>
                                <div id="previewRecipientAddr" class="text-dark" style="font-size: {{ $mRecipientAddressSize }};">38 Banglabazar, Dhaka-1100, Bangladesh</div>
                                <div id="previewRecipientPhone" class="text-dark fw-bold font-monospace" style="font-size: {{ $mRecipientPhoneSize }};">01812-345678, 01712-345678</div>
                            </div>
                        </div>

                        <div class="row g-2.5">
                            <div class="col-md-4 col-sm-6">
                                <label class="form-label small fw-semibold text-dark mb-1">
                                    Supplier / Name Size
                                </label>
                                <select name="challan_recipient_name_size" id="inputNameSize" class="form-select form-select-sm" onchange="updateRecipientPreview()">
                                    @foreach(['11px'=>'Small (11px)', '12px'=>'Regular (12px)', '13px'=>'Medium (13px)', '14px'=>'Large (14px)', '15px'=>'Extra Large (15px)', '16px'=>'Huge (16px)', '18px'=>'Display (18px)'] as $val => $lbl)
                                        <option value="{{ $val }}" {{ ($mRecipientNameSize === $val) ? 'selected' : '' }}>{{ $lbl }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4 col-sm-6">
                                <label class="form-label small fw-semibold text-dark mb-1">
                                    Mobile Number Size
                                </label>
                                <select name="challan_recipient_phone_size" id="inputPhoneSize" class="form-select form-select-sm" onchange="updateRecipientPreview()">
                                    @foreach(['10.5px'=>'Small (10.5px)', '11.5px'=>'Regular (11.5px)', '12px'=>'Medium (12px)', '13px'=>'Large (13px)', '14px'=>'Extra Large (14px)', '15px'=>'Huge (15px)'] as $val => $lbl)
                                        <option value="{{ $val }}" {{ ($mRecipientPhoneSize === $val) ? 'selected' : '' }}>{{ $lbl }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4 col-sm-6">
                                <label class="form-label small fw-semibold text-dark mb-1">
                                    Address Size
                                </label>
                                <select name="challan_recipient_address_size" id="inputAddressSize" class="form-select form-select-sm" onchange="updateRecipientPreview()">
                                    @foreach(['10px'=>'Small (10px)', '11px'=>'Regular (11px)', '11.5px'=>'Medium (11.5px)', '12px'=>'Large (12px)', '13px'=>'Extra Large (13px)', '14px'=>'Huge (14px)'] as $val => $lbl)
                                        <option value="{{ $val }}" {{ ($mRecipientAddressSize === $val) ? 'selected' : '' }}>{{ $lbl }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 col-sm-6">
                                <label class="form-label small fw-semibold text-dark mb-1">
                                    Designation & Org Size
                                </label>
                                <select name="challan_recipient_desig_size" id="inputDesigSize" class="form-select form-select-sm" onchange="updateRecipientPreview()">
                                    @foreach(['10px'=>'Small (10px)', '11px'=>'Regular (11px)', '11.5px'=>'Medium (11.5px)', '12px'=>'Large (12px)', '13px'=>'Extra Large (13px)'] as $val => $lbl)
                                        <option value="{{ $val }}" {{ ($mRecipientDesigSize === $val) ? 'selected' : '' }}>{{ $lbl }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 col-sm-12">
                                <label class="form-label small fw-semibold text-dark mb-1">
                                    Default Signatory Designation
                                </label>
                                <input type="text" name="default_creator_designation" id="inputDefaultCreatorDesig" class="form-control form-control-sm" 
                                       value="{{ $modalSettings['default_creator_designation'] ?? '' }}" placeholder="e.g. Authorized Signatory / Purchase In-Charge">
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Company / Imprint Name</label>
                            <input type="text" name="business_name" id="inputBusinessName" class="form-control" value="{{ $modalSettings['business_name'] ?? 'Idea Publication' }}" required oninput="updateLivePreview()">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tagline / Slogan</label>
                            <input type="text" name="tagline" id="inputTagline" class="form-control" value="{{ $modalSettings['tagline'] ?? 'Book Publication, Printing & Distribution' }}" placeholder="Book Publication, Printing..." oninput="updateLivePreview()">
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Full Official Address</label>
                            <input type="text" name="address" id="inputAddress" class="form-control" value="{{ $modalSettings['address'] ?? 'Dhaka, Bangladesh' }}" placeholder="e.g. 38 Banglabazar, Dhaka..." oninput="updateLivePreview()">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Official Phone Number</label>
                            <input type="text" name="phone" id="inputPhone" class="form-control" value="{{ $modalSettings['phone'] ?? '018XXXXXXXX' }}" placeholder="017XXXXXXXX, 018XXXXXXXX" oninput="updateLivePreview()">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Official Email Address</label>
                            <input type="email" name="email" id="inputEmail" class="form-control" value="{{ $modalSettings['email'] ?? 'info@ideaabd.com' }}" placeholder="info@ideaabd.com" oninput="updateLivePreview()">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Terms & Conditions / Note</label>
                            <textarea name="terms_and_conditions" id="inputTerms" class="form-control" rows="2" placeholder="Standard terms, receiving conditions, etc.">{{ $modalSettings['terms_and_conditions'] ?? '' }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top py-2.5 bg-light">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-semibold px-4 shadow-sm">
                        <i class="fa-solid fa-save me-1"></i> Save Design & Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
if (typeof updateLivePreview !== 'function') {
    window.updateLivePreview = function() {
        const name = document.getElementById('inputBusinessName')?.value || 'Idea Publication';
        const tag = document.getElementById('inputTagline')?.value || '';
        const addr = document.getElementById('inputAddress')?.value || '';
        const ph = document.getElementById('inputPhone')?.value || '';
        const em = document.getElementById('inputEmail')?.value || '';

        const titleEl = document.getElementById('previewHeaderTitle');
        const tagEl = document.getElementById('previewHeaderTagline');
        const addrEl = document.getElementById('previewMetaAddr');
        const phoneEl = document.getElementById('previewMetaPhone');
        const emailEl = document.getElementById('previewMetaEmail');

        if (titleEl) titleEl.textContent = name;
        if (tagEl) tagEl.textContent = tag;
        if (addrEl) addrEl.textContent = addr;
        if (phoneEl) phoneEl.textContent = ph;
        if (emailEl) emailEl.textContent = em;
    };
}

if (typeof updateRecipientPreview !== 'function') {
    window.updateRecipientPreview = function() {
        const nameSize = document.getElementById('inputNameSize')?.value || '13px';
        const phoneSize = document.getElementById('inputPhoneSize')?.value || '12px';
        const addrSize = document.getElementById('inputAddressSize')?.value || '11.5px';
        const desigSize = document.getElementById('inputDesigSize')?.value || '11.5px';

        const pName = document.getElementById('previewRecipientName');
        const pPhone = document.getElementById('previewRecipientPhone');
        const pAddr = document.getElementById('previewRecipientAddr');
        const pDesig = document.getElementById('previewRecipientDesig');
        const pOrg = document.getElementById('previewRecipientOrg');

        if (pName) pName.style.fontSize = nameSize;
        if (pPhone) pPhone.style.fontSize = phoneSize;
        if (pAddr) pAddr.style.fontSize = addrSize;
        if (pDesig) pDesig.style.fontSize = desigSize;
        if (pOrg) pOrg.style.fontSize = desigSize;

        // Also update on-page challan target elements if present
        const cName = document.getElementById('challanRecipientName');
        const cPhone = document.getElementById('challanRecipientPhone');
        const cAddr = document.getElementById('challanRecipientAddr');
        const cDesig = document.getElementById('challanRecipientDesig');
        const cOrg = document.getElementById('challanRecipientOrg');

        if (cName) cName.style.fontSize = nameSize;
        if (cPhone) cPhone.style.fontSize = phoneSize;
        if (cAddr) cAddr.style.fontSize = addrSize;
        if (cDesig) cDesig.style.fontSize = desigSize;
        if (cOrg) cOrg.style.fontSize = desigSize;
    };
}

(function() {
    let pRawImage = new Image();
    let pImageLoaded = false;
    let pCropX = 0, pCropY = 0;
    let pCropScale = 1;
    let pIsDragging = false;
    let pDragStartX = 0, pDragStartY = 0;

    const pFileInput = document.getElementById('logoFileInput');
    const pCropperBox = document.getElementById('cropperContainer');
    const pCanvas = document.getElementById('cropCanvas');
    const pCtx = pCanvas?.getContext('2d');
    const pZoomSlider = document.getElementById('cropZoomSlider');
    const pBase64Input = document.getElementById('logoCroppedBase64');
    const pResultThumb = document.getElementById('cropperPreviewThumb');
    const pHeaderPreviewImg = document.getElementById('previewHeaderLogo');
    const pDragArea = document.getElementById('cropDragArea');

    if (pFileInput && !pFileInput.dataset.bound) {
        pFileInput.dataset.bound = 'true';
        pFileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = function(evt) {
                pRawImage = new Image();
                pRawImage.onload = function() {
                    pImageLoaded = true;
                    if (pCropperBox) pCropperBox.classList.remove('d-none');
                    
                    if (pCanvas) {
                        const scaleW = pCanvas.width / pRawImage.width;
                        const scaleH = pCanvas.height / pRawImage.height;
                        pCropScale = Math.max(scaleW, scaleH);
                        
                        if (pZoomSlider) {
                            pZoomSlider.min = (pCropScale * 0.4).toFixed(2);
                            pZoomSlider.max = (pCropScale * 3.5).toFixed(2);
                            pZoomSlider.value = pCropScale.toFixed(2);
                        }
                        
                        pCropX = (pCanvas.width - pRawImage.width * pCropScale) / 2;
                        pCropY = (pCanvas.height - pRawImage.height * pCropScale) / 2;

                        renderCropPurchases();
                    }
                };
                pRawImage.src = evt.target.result;
            };
            reader.readAsDataURL(file);
        });
    }

    function renderCropPurchases() {
        if (!pImageLoaded || !pCtx || !pCanvas) return;
        
        pCtx.clearRect(0, 0, pCanvas.width, pCanvas.height);
        pCtx.fillStyle = '#ffffff';
        pCtx.fillRect(0, 0, pCanvas.width, pCanvas.height);
        
        const drawW = pRawImage.width * pCropScale;
        const drawH = pRawImage.height * pCropScale;
        
        pCtx.drawImage(pRawImage, pCropX, pCropY, drawW, drawH);
        
        const dataUrl = pCanvas.toDataURL('image/png', 0.95);
        if (pBase64Input) pBase64Input.value = dataUrl;
        if (pResultThumb) pResultThumb.src = dataUrl;
        if (pHeaderPreviewImg) pHeaderPreviewImg.src = dataUrl;
    }

    if (pZoomSlider && !pZoomSlider.dataset.bound) {
        pZoomSlider.dataset.bound = 'true';
        pZoomSlider.addEventListener('input', function() {
            const prevScale = pCropScale;
            pCropScale = parseFloat(this.value);
            
            if (pCanvas) {
                const centerX = pCanvas.width / 2;
                const centerY = pCanvas.height / 2;
                pCropX = centerX - ((centerX - pCropX) / prevScale) * pCropScale;
                pCropY = centerY - ((centerY - pCropY) / prevScale) * pCropScale;
                renderCropPurchases();
            }
        });
    }

    if (pDragArea && !pDragArea.dataset.bound) {
        pDragArea.dataset.bound = 'true';
        pDragArea.addEventListener('mousedown', function(e) {
            pIsDragging = true;
            pDragStartX = e.clientX - pCropX;
            pDragStartY = e.clientY - pCropY;
            pDragArea.style.cursor = 'grabbing';
        });

        window.addEventListener('mousemove', function(e) {
            if (!pIsDragging) return;
            pCropX = e.clientX - pDragStartX;
            pCropY = e.clientY - pDragStartY;
            renderCropPurchases();
        });

        window.addEventListener('mouseup', function() {
            if (pIsDragging) {
                pIsDragging = false;
                if (pDragArea) pDragArea.style.cursor = 'grab';
            }
        });

        pDragArea.addEventListener('touchstart', function(e) {
            if (e.touches.length === 1) {
                pIsDragging = true;
                pDragStartX = e.touches[0].clientX - pCropX;
                pDragStartY = e.touches[0].clientY - pCropY;
            }
        }, {passive: true});

        window.addEventListener('touchmove', function(e) {
            if (!pIsDragging || e.touches.length !== 1) return;
            pCropX = e.touches[0].clientX - pDragStartX;
            pCropY = e.touches[0].clientY - pCropY;
            renderCropPurchases();
        }, {passive: true});

        window.addEventListener('touchend', function() {
            pIsDragging = false;
        });
    }

    window.resetCrop = function() {
        if (!pImageLoaded || !pCanvas) return;
        const scaleW = pCanvas.width / pRawImage.width;
        const scaleH = pCanvas.height / pRawImage.height;
        pCropScale = Math.max(scaleW, scaleH);
        if (pZoomSlider) pZoomSlider.value = pCropScale.toFixed(2);
        pCropX = (pCanvas.width - pRawImage.width * pCropScale) / 2;
        pCropY = (pCanvas.height - pRawImage.height * pCropScale) / 2;
        renderCropPurchases();
    };
})();
</script>
