/**
 * =========================================================================
 * ENTERPRISE MEDIA & ASSET STUDIO JAVASCRIPT ENGINE — IDEA PUBLICATION
 * =========================================================================
 */

document.addEventListener('DOMContentLoaded', () => {
    initDragDropZone();
    initMultiSelectListeners();
    initStudioEvents();
});

/* ========================================================================= */
/* 1. SELECTION & BATCH ACTIONS                                              */
/* ========================================================================= */
let selectedMediaPaths = [];
let selectedMediaUrls = [];

function initMultiSelectListeners() {
    document.querySelectorAll('.media-select-cb').forEach(cb => {
        cb.addEventListener('change', () => {
            updateSelectionState();
        });
    });
}

function toggleSelectAll(masterCb) {
    const isChecked = masterCb ? masterCb.checked : false;
    document.querySelectorAll('.media-select-cb').forEach(cb => {
        cb.checked = isChecked;
    });
    updateSelectionState();
}

function clearAllSelections() {
    document.querySelectorAll('.media-select-cb').forEach(cb => {
        cb.checked = false;
    });
    const master = document.getElementById('selectAllMaster');
    if (master) master.checked = false;
    updateSelectionState();
}

function updateSelectionState() {
    selectedMediaPaths = [];
    selectedMediaUrls = [];

    const checkboxes = document.querySelectorAll('.media-select-cb:checked');
    checkboxes.forEach(cb => {
        const path = cb.getAttribute('data-path');
        const url = cb.getAttribute('data-url');
        if (path) selectedMediaPaths.push(path);
        if (url) selectedMediaUrls.push(url);

        const card = cb.closest('.media-card');
        if (card) card.classList.add('is-selected');
        const row = cb.closest('tr');
        if (row) row.classList.add('table-primary');
    });

    document.querySelectorAll('.media-select-cb:not(:checked)').forEach(cb => {
        const card = cb.closest('.media-card');
        if (card) card.classList.remove('is-selected');
        const row = cb.closest('tr');
        if (row) row.classList.remove('table-primary');
    });

    const count = selectedMediaPaths.length;
    const bar = document.getElementById('mediaFloatingBar');
    const badge = document.getElementById('selectedMediaCountBadge');

    if (badge) badge.textContent = `${count} টি নির্বাচিত`;

    if (bar) {
        if (count > 0) {
            bar.classList.add('show');
        } else {
            bar.classList.remove('show');
        }
    }
}

function copySelectedUrls() {
    if (selectedMediaUrls.length === 0) return;
    const text = selectedMediaUrls.join('\n');
    navigator.clipboard.writeText(text).then(() => {
        showMediaAlert('success', `${selectedMediaUrls.length}টি ছবির URL ক্লিপবোর্ডে কপি করা হয়েছে!`);
    });
}

function openBulkMoveModal() {
    if (selectedMediaPaths.length === 0) return;
    const modalEl = document.getElementById('bulkMoveModal');
    if (modalEl && typeof bootstrap !== 'undefined') {
        const m = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
        m.show();
    }
}

function executeBulkMove() {
    const targetFolder = document.getElementById('bulkMoveTargetFolder')?.value;
    if (!targetFolder || selectedMediaPaths.length === 0) return;

    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const btn = document.getElementById('btnConfirmBulkMove');
    if (btn) btn.disabled = true;

    fetch('/admin/media/bulk-action', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': token,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            action: 'move',
            paths: selectedMediaPaths,
            target_folder: targetFolder
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showMediaAlert('success', data.message);
            setTimeout(() => window.location.reload(), 1200);
        } else {
            showMediaAlert('danger', data.message || 'ফাইল সরানো ব্যর্থ হয়েছে।');
        }
    })
    .catch(() => showMediaAlert('danger', 'সার্ভার অনুরোধ ব্যর্থ হয়েছে।'))
    .finally(() => {
        if (btn) btn.disabled = false;
    });
}

function executeBulkDelete() {
    if (selectedMediaPaths.length === 0) return;
    if (!confirm(`আপনি কি নিশ্চিত নির্বাচিত ${selectedMediaPaths.length}টি ছবি স্থায়ীভাবে মুছে ফেলতে চান?`)) {
        return;
    }

    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    fetch('/admin/media/bulk-action', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': token,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            action: 'delete',
            paths: selectedMediaPaths
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showMediaAlert('success', data.message);
            setTimeout(() => window.location.reload(), 1200);
        } else {
            showMediaAlert('danger', data.message || 'ফাইল মোছা ব্যর্থ হয়েছে।');
        }
    })
    .catch(() => showMediaAlert('danger', 'সার্ভার অনুরোধ ব্যর্থ হয়েছে।'));
}

function executeBulkDownloadZip() {
    if (selectedMediaPaths.length === 0) return;

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/admin/media/download-zip';
    form.target = '_blank';

    const tokenInput = document.createElement('input');
    tokenInput.type = 'hidden';
    tokenInput.name = '_token';
    tokenInput.value = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    form.appendChild(tokenInput);

    selectedMediaPaths.forEach(p => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'paths[]';
        input.value = p;
        form.appendChild(input);
    });

    document.body.appendChild(form);
    form.submit();
    form.remove();
}

/* ========================================================================= */
/* 2. STUDIO IMAGE CUSTOMIZER ENGINE (HTML5 CANVAS)                          */
/* ========================================================================= */
let studioState = {
    imgObj: null,
    origUrl: '',
    origPath: '',
    origFilename: '',
    origWidth: 0,
    origHeight: 0,
    currWidth: 0,
    currHeight: 0,
    rotation: 0,
    flipH: false,
    flipV: false,
    brightness: 100,
    contrast: 100,
    saturation: 100,
    grayscale: 0,
    sepia: 0,
    blur: 0,
    aspectRatioLock: true,
    watermarkText: '',
    watermarkOpacity: 60,
    watermarkPosition: 'bottom-right',
    watermarkColor: '#ffffff',
    format: 'webp',
    quality: 85
};

function openStudioModal(url, path, filename, width, height) {
    studioState.origUrl = url;
    studioState.origPath = path;
    studioState.origFilename = filename;
    studioState.rotation = 0;
    studioState.flipH = false;
    studioState.flipV = false;
    studioState.brightness = 100;
    studioState.contrast = 100;
    studioState.saturation = 100;
    studioState.grayscale = 0;
    studioState.sepia = 0;
    studioState.blur = 0;
    studioState.watermarkText = '';

    const titleEl = document.getElementById('studioModalTitle');
    if (titleEl) titleEl.textContent = `কাস্টমাইজ ও অপ্টিমাইজ স্টুডিও — ${filename}`;

    const filenameInput = document.getElementById('studioFilenameInput');
    if (filenameInput) filenameInput.value = filename.substring(0, filename.lastIndexOf('.')) || filename;

    // Load image into Image object
    const img = new Image();
    img.crossOrigin = 'anonymous';
    img.onload = () => {
        studioState.imgObj = img;
        studioState.origWidth = img.naturalWidth;
        studioState.origHeight = img.naturalHeight;
        studioState.currWidth = img.naturalWidth;
        studioState.currHeight = img.naturalHeight;

        updateStudioInputs();
        renderStudioCanvas();

        const modalEl = document.getElementById('mediaStudioModal');
        if (modalEl && typeof bootstrap !== 'undefined') {
            const m = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
            m.show();
        }
    };
    img.src = url;
}

function initStudioEvents() {
    // Width / Height input listeners
    const widthInput = document.getElementById('studioWidthInput');
    const heightInput = document.getElementById('studioHeightInput');
    const lockCb = document.getElementById('studioLockRatio');

    if (widthInput) {
        widthInput.addEventListener('input', (e) => {
            const w = parseInt(e.target.value) || 0;
            if (w <= 0) return;
            studioState.currWidth = w;
            if (lockCb && lockCb.checked && studioState.origWidth > 0) {
                const ratio = studioState.origHeight / studioState.origWidth;
                studioState.currHeight = Math.round(w * ratio);
                if (heightInput) heightInput.value = studioState.currHeight;
            }
            renderStudioCanvas();
        });
    }

    if (heightInput) {
        heightInput.addEventListener('input', (e) => {
            const h = parseInt(e.target.value) || 0;
            if (h <= 0) return;
            studioState.currHeight = h;
            if (lockCb && lockCb.checked && studioState.origHeight > 0) {
                const ratio = studioState.origWidth / studioState.origHeight;
                studioState.currWidth = Math.round(h * ratio);
                if (widthInput) widthInput.value = studioState.currWidth;
            }
            renderStudioCanvas();
        });
    }

    // Sliders
    bindSlider('studioBrightness', (v) => { studioState.brightness = v; renderStudioCanvas(); }, 'studioBrightnessVal', '%');
    bindSlider('studioContrast', (v) => { studioState.contrast = v; renderStudioCanvas(); }, 'studioContrastVal', '%');
    bindSlider('studioSaturation', (v) => { studioState.saturation = v; renderStudioCanvas(); }, 'studioSaturationVal', '%');
    bindSlider('studioGrayscale', (v) => { studioState.grayscale = v; renderStudioCanvas(); }, 'studioGrayscaleVal', '%');
    bindSlider('studioQuality', (v) => { studioState.quality = v; }, 'studioQualityVal', '%');
    bindSlider('studioWatermarkOpacity', (v) => { studioState.watermarkOpacity = v; renderStudioCanvas(); }, 'studioWatermarkOpacityVal', '%');

    const wmInput = document.getElementById('studioWatermarkTextInput');
    if (wmInput) {
        wmInput.addEventListener('input', (e) => {
            studioState.watermarkText = e.target.value;
            renderStudioCanvas();
        });
    }
}

function bindSlider(id, callback, valDisplayId, unit = '') {
    const el = document.getElementById(id);
    if (!el) return;
    el.addEventListener('input', (e) => {
        const val = e.target.value;
        const disp = document.getElementById(valDisplayId);
        if (disp) disp.textContent = val + unit;
        callback(val);
    });
}

function updateStudioInputs() {
    const widthInput = document.getElementById('studioWidthInput');
    const heightInput = document.getElementById('studioHeightInput');
    const metaDisplay = document.getElementById('studioOrigMetaDisplay');

    if (widthInput) widthInput.value = studioState.currWidth;
    if (heightInput) heightInput.value = studioState.currHeight;
    if (metaDisplay) metaDisplay.textContent = `আসল রেজোলিউশন: ${studioState.origWidth} × ${studioState.origHeight} px`;
}

function setStudioPreset(preset) {
    if (!studioState.imgObj) return;

    document.querySelectorAll('.studio-preset-chip').forEach(c => c.classList.remove('active'));

    const activeChip = document.querySelector(`.studio-preset-chip[data-preset="${preset}"]`);
    if (activeChip) activeChip.classList.add('active');

    const lockCb = document.getElementById('studioLockRatio');

    if (preset === 'original') {
        studioState.currWidth = studioState.origWidth;
        studioState.currHeight = studioState.origHeight;
    } else if (preset === '1:1') {
        const minDim = Math.min(studioState.origWidth, studioState.origHeight);
        studioState.currWidth = minDim;
        studioState.currHeight = minDim;
        if (lockCb) lockCb.checked = false;
    } else if (preset === '16:9') {
        studioState.currWidth = 1200;
        studioState.currHeight = 675;
        if (lockCb) lockCb.checked = false;
    } else if (preset === '4:3') {
        studioState.currWidth = 1024;
        studioState.currHeight = 768;
        if (lockCb) lockCb.checked = false;
    } else if (preset === '800x800') {
        studioState.currWidth = 800;
        studioState.currHeight = 800;
        if (lockCb) lockCb.checked = false;
    } else if (preset === '1920x1080') {
        studioState.currWidth = 1920;
        studioState.currHeight = 1080;
        if (lockCb) lockCb.checked = false;
    }

    updateStudioInputs();
    renderStudioCanvas();
}

function studioRotate(deg) {
    studioState.rotation = (studioState.rotation + deg) % 360;
    if (studioState.rotation < 0) studioState.rotation += 360;
    renderStudioCanvas();
}

function studioFlip(axis) {
    if (axis === 'h') studioState.flipH = !studioState.flipH;
    if (axis === 'v') studioState.flipV = !studioState.flipV;
    renderStudioCanvas();
}

function setStudioQuickFilter(filter) {
    if (filter === 'none') {
        studioState.brightness = 100;
        studioState.contrast = 100;
        studioState.saturation = 100;
        studioState.grayscale = 0;
        studioState.sepia = 0;
    } else if (filter === 'bw') {
        studioState.brightness = 105;
        studioState.contrast = 120;
        studioState.saturation = 0;
        studioState.grayscale = 100;
        studioState.sepia = 0;
    } else if (filter === 'vintage') {
        studioState.brightness = 110;
        studioState.contrast = 90;
        studioState.saturation = 80;
        studioState.grayscale = 0;
        studioState.sepia = 45;
    } else if (filter === 'crisp') {
        studioState.brightness = 105;
        studioState.contrast = 130;
        studioState.saturation = 115;
        studioState.grayscale = 0;
        studioState.sepia = 0;
    }

    // Sync sliders
    setSliderVal('studioBrightness', studioState.brightness, 'studioBrightnessVal', '%');
    setSliderVal('studioContrast', studioState.contrast, 'studioContrastVal', '%');
    setSliderVal('studioSaturation', studioState.saturation, 'studioSaturationVal', '%');
    setSliderVal('studioGrayscale', studioState.grayscale, 'studioGrayscaleVal', '%');

    renderStudioCanvas();
}

function setSliderVal(id, val, dispId, unit = '') {
    const el = document.getElementById(id);
    if (el) el.value = val;
    const disp = document.getElementById(dispId);
    if (disp) disp.textContent = val + unit;
}

function renderStudioCanvas() {
    const canvas = document.getElementById('studioCanvas');
    if (!canvas || !studioState.imgObj) return;

    const ctx = canvas.getContext('2d');
    const isSideways = (studioState.rotation === 90 || studioState.rotation === 270);

    const w = isSideways ? studioState.currHeight : studioState.currWidth;
    const h = isSideways ? studioState.currWidth : studioState.currHeight;

    canvas.width = w;
    canvas.height = h;

    ctx.clearRect(0, 0, w, h);

    // Apply CSS Filters to Canvas Context
    ctx.filter = `brightness(${studioState.brightness}%) contrast(${studioState.contrast}%) saturate(${studioState.saturation}%) grayscale(${studioState.grayscale}%) sepia(${studioState.sepia}%)`;

    ctx.save();
    ctx.translate(w / 2, h / 2);
    ctx.rotate((studioState.rotation * Math.PI) / 180);
    ctx.scale(studioState.flipH ? -1 : 1, studioState.flipV ? -1 : 1);

    ctx.drawImage(
        studioState.imgObj,
        -studioState.currWidth / 2,
        -studioState.currHeight / 2,
        studioState.currWidth,
        studioState.currHeight
    );

    ctx.restore();

    // Reset filter for Watermark
    ctx.filter = 'none';

    // Apply Watermark if exists
    if (studioState.watermarkText && studioState.watermarkText.trim()) {
        ctx.save();
        const fontSize = Math.max(16, Math.round(w / 35));
        ctx.font = `bold ${fontSize}px sans-serif`;
        ctx.fillStyle = studioState.watermarkColor || '#ffffff';
        ctx.globalAlpha = studioState.watermarkOpacity / 100;
        ctx.shadowColor = 'rgba(0,0,0,0.8)';
        ctx.shadowBlur = 4;
        ctx.shadowOffsetX = 1;
        ctx.shadowOffsetY = 1;

        const pad = fontSize * 1.2;
        const textWidth = ctx.measureText(studioState.watermarkText).width;

        let posX = w - textWidth - pad;
        let posY = h - pad;

        if (studioState.watermarkPosition === 'center') {
            posX = (w - textWidth) / 2;
            posY = h / 2;
        } else if (studioState.watermarkPosition === 'bottom-left') {
            posX = pad;
            posY = h - pad;
        } else if (studioState.watermarkPosition === 'top-right') {
            posX = w - textWidth - pad;
            posY = pad + fontSize;
        }

        ctx.fillText(studioState.watermarkText, posX, posY);
        ctx.restore();
    }
}

function saveCustomizedImage(mode) {
    const canvas = document.getElementById('studioCanvas');
    if (!canvas) return;

    const formatSelect = document.getElementById('studioFormatSelect')?.value || 'webp';
    const quality = (parseInt(document.getElementById('studioQuality')?.value) || 85) / 100;
    const filenameInput = document.getElementById('studioFilenameInput')?.value || '';
    const folderSelect = document.getElementById('studioFolderSelect')?.value || 'uploads';

    let mime = 'image/webp';
    if (formatSelect === 'png') mime = 'image/png';
    if (formatSelect === 'jpg' || formatSelect === 'jpeg') mime = 'image/jpeg';

    const dataUrl = canvas.toDataURL(mime, quality);

    const btn = (mode === 'overwrite') ? document.getElementById('btnStudioOverwrite') : document.getElementById('btnStudioSaveCopy');
    if (btn) btn.disabled = true;

    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    fetch('/admin/media/save-customized', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': token,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            data_url: dataUrl,
            original_path: studioState.origPath,
            mode: mode,
            new_filename: filenameInput,
            folder: folderSelect,
            target_format: formatSelect
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showMediaAlert('success', data.message);
            const modalEl = document.getElementById('mediaStudioModal');
            if (modalEl && typeof bootstrap !== 'undefined') {
                const m = bootstrap.Modal.getInstance(modalEl);
                if (m) m.hide();
            }
            setTimeout(() => window.location.reload(), 1200);
        } else {
            showMediaAlert('danger', data.message || 'কাস্টমাইজড ছবি সংরক্ষণ ব্যর্থ হয়েছে।');
        }
    })
    .catch(() => showMediaAlert('danger', 'সার্ভার রিকোয়েস্ট ব্যর্থ হয়েছে।'))
    .finally(() => {
        if (btn) btn.disabled = false;
    });
}

function downloadStudioCanvasLocally() {
    const canvas = document.getElementById('studioCanvas');
    if (!canvas) return;

    const format = document.getElementById('studioFormatSelect')?.value || 'webp';
    const quality = (parseInt(document.getElementById('studioQuality')?.value) || 85) / 100;
    const filenameInput = document.getElementById('studioFilenameInput')?.value || 'customized-image';

    const link = document.createElement('a');
    link.download = `${filenameInput}.${format}`;
    link.href = canvas.toDataURL(`image/${format}`, quality);
    link.click();
    link.remove();
}

/* ========================================================================= */
/* 3. MULTI-FILE DRAG & DROP UPLOAD ZONE                                     */
/* ========================================================================= */
function initDragDropZone() {
    const zone = document.getElementById('mediaUploadZone');
    const input = document.getElementById('multiFileInput');

    if (!zone || !input) return;

    ['dragenter', 'dragover'].forEach(eventName => {
        zone.addEventListener(eventName, (e) => {
            e.preventDefault();
            zone.classList.add('dragover');
        }, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        zone.addEventListener(eventName, (e) => {
            e.preventDefault();
            zone.classList.remove('dragover');
        }, false);
    });

    zone.addEventListener('drop', (e) => {
        const dt = e.dataTransfer;
        const files = dt.files;
        handleFilesSelected(files);
    });

    input.addEventListener('change', (e) => {
        handleFilesSelected(e.target.files);
    });
}

let pendingUploadFiles = [];

function handleFilesSelected(files) {
    if (!files || files.length === 0) return;

    pendingUploadFiles = Array.from(files);
    const previewContainer = document.getElementById('uploadThumbPreviewList');
    if (previewContainer) previewContainer.innerHTML = '';

    pendingUploadFiles.forEach((file, idx) => {
        if (!file.type.startsWith('image/')) return;

        const reader = new FileReader();
        reader.onload = (e) => {
            const div = document.createElement('div');
            div.className = 'd-flex align-items-center justify-content-between p-2 bg-white border rounded-3 mb-1.5 small';
            div.innerHTML = `
                <div class="d-flex align-items-center gap-2 text-truncate">
                    <img src="${e.target.result}" style="width: 38px; height: 38px; object-fit: cover; border-radius: 6px;">
                    <div class="text-truncate">
                        <div class="fw-bold text-dark text-truncate">${file.name}</div>
                        <div class="text-muted fs-xs font-monospace">${(file.size / 1024).toFixed(1)} KB</div>
                    </div>
                </div>
                <span class="badge bg-primary-subtle text-primary rounded-pill">প্রস্তুত</span>
            `;
            if (previewContainer) previewContainer.appendChild(div);
        };
        reader.readAsDataURL(file);
    });

    const box = document.getElementById('uploadPreviewWrap');
    if (box) box.classList.remove('d-none');
}

/**
 * Convert any File (PNG/JPG/BMP) to a WebP Blob directly in the browser via HTML5 Canvas
 */
async function convertFileToWebpInBrowser(file, quality = 0.85, maxDim = 1920) {
    if (!file.type.startsWith('image/') || file.type === 'image/svg+xml' || file.type === 'image/webp') {
        return file; // SVGs and WebPs pass through natively
    }

    return new Promise((resolve) => {
        const reader = new FileReader();
        reader.onload = (e) => {
            const img = new Image();
            img.onload = () => {
                let w = img.naturalWidth;
                let h = img.naturalHeight;

                if (maxDim > 0 && (w > maxDim || h > maxDim)) {
                    const ratio = Math.min(maxDim / w, maxDim / h);
                    w = Math.round(w * ratio);
                    h = Math.round(h * ratio);
                }

                const canvas = document.createElement('canvas');
                canvas.width = w;
                canvas.height = h;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, w, h);

                canvas.toBlob((blob) => {
                    if (blob) {
                        const originalName = file.name.substring(0, file.name.lastIndexOf('.')) || file.name;
                        const webpFile = new File([blob], `${originalName}.webp`, { type: 'image/webp' });
                        resolve(webpFile);
                    } else {
                        resolve(file);
                    }
                }, 'image/webp', quality);
            };
            img.onerror = () => resolve(file);
            img.src = e.target.result;
        };
        reader.onerror = () => resolve(file);
        reader.readAsDataURL(file);
    });
}

async function submitMultiUploadAjax() {
    if (pendingUploadFiles.length === 0) {
        showMediaAlert('warning', 'অনুগ্রহ করে অন্তত একটি ছবি নির্বাচন করুন!');
        return;
    }

    const btn = document.getElementById('btnSubmitUpload');
    const progressBar = document.getElementById('uploadProgressBar');
    const progressWrap = document.getElementById('uploadProgressWrap');
    const percentLabel = document.getElementById('uploadPercentLabel');

    if (btn) btn.disabled = true;
    if (progressWrap) progressWrap.classList.remove('d-none');
    if (progressBar) progressBar.style.width = '10%';
    if (percentLabel) percentLabel.textContent = 'ব্রাউজারে WebP রূপান্তর ও অপ্টিমাইজেশন চলছে...';

    const formData = new FormData();
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const folder = document.getElementById('uploadTargetFolder')?.value || 'uploads';
    const autoWebp = document.getElementById('uploadAutoWebp')?.checked;
    const maxDim = parseInt(document.getElementById('uploadMaxDim')?.value) || 1920;

    formData.append('_token', token);
    formData.append('folder', folder);
    formData.append('auto_webp', autoWebp ? 1 : 0);
    formData.append('max_dim', maxDim);

    // Client-side WebP pre-conversion
    for (let i = 0; i < pendingUploadFiles.length; i++) {
        let f = pendingUploadFiles[i];
        if (autoWebp) {
            f = await convertFileToWebpInBrowser(f, 0.85, maxDim);
        }
        formData.append('files[]', f);
    }

    if (progressBar) progressBar.style.width = '35%';
    if (percentLabel) percentLabel.textContent = 'সার্ভারে দ্রুত আপলোড হচ্ছে...';

    const xhr = new XMLHttpRequest();
    xhr.open('POST', '/admin/media/upload', true);
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    xhr.setRequestHeader('Accept', 'application/json');

    xhr.upload.onprogress = (e) => {
        if (e.lengthComputable && progressBar) {
            const percent = 35 + Math.round((e.loaded / e.total) * 60);
            progressBar.style.width = percent + '%';
            if (percentLabel) percentLabel.textContent = `আপলোড সম্পন্ন: ${percent}%`;
        }
    };

    xhr.onload = () => {
        if (btn) btn.disabled = false;
        if (progressBar) progressBar.style.width = '100%';
        if (xhr.status >= 200 && xhr.status < 300) {
            try {
                const res = JSON.parse(xhr.responseText);
                showMediaAlert('success', res.message || 'আপলোড সফল হয়েছে!');
                setTimeout(() => window.location.reload(), 1000);
            } catch (e) {
                window.location.reload();
            }
        } else {
            showMediaAlert('danger', 'ফাইল আপলোড ব্যর্থ হয়েছে।');
        }
    };

    xhr.onerror = () => {
        if (btn) btn.disabled = false;
        showMediaAlert('danger', 'নেটওয়ার্ক সমস্যা। আপলোড ব্যর্থ।');
    };

    xhr.send(formData);
}

/**
 * 1-Click Convert All Existing PNG/JPG Images to WebP Across Whole System
 */
function runConvertAllToWebp(btn) {
    if (!confirm('আপনি কি বিদ্যমান সকল PNG ও JPG ইমেজকে আধুনিক WebP ফরম্যাটে রূপান্তর করতে চান?')) {
        return;
    }

    const origContent = btn ? btn.innerHTML : '';
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = `<span class="spinner-border spinner-border-sm me-1.5"></span><span>WebP কনভার্সন চলছে...</span>`;
    }

    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    fetch('/admin/media/convert-all-webp', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': token,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showMediaAlert('success', data.message);
            setTimeout(() => window.location.reload(), 1500);
        } else {
            showMediaAlert('danger', data.message || 'WebP কনভার্সন ব্যর্থ হয়েছে।');
        }
    })
    .catch(() => {
        showMediaAlert('danger', 'সার্ভার রেসপন্স দিতে ব্যর্থ হয়েছে।');
    })
    .finally(() => {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = origContent;
        }
    });
}

/* ========================================================================= */
/* 4. RENAME FILE & SINGLE OPTIMIZE                                          */
/* ========================================================================= */
function openRenameModal(path, currentName) {
    const pathInput = document.getElementById('renamePathInput');
    const nameInput = document.getElementById('renameNewNameInput');
    if (pathInput) pathInput.value = path;
    if (nameInput) nameInput.value = currentName.substring(0, currentName.lastIndexOf('.')) || currentName;

    const modalEl = document.getElementById('renameFileModal');
    if (modalEl && typeof bootstrap !== 'undefined') {
        const m = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
        m.show();
    }
}

function submitRenameFile() {
    const path = document.getElementById('renamePathInput')?.value;
    const newName = document.getElementById('renameNewNameInput')?.value;
    if (!path || !newName) return;

    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const btn = document.getElementById('btnConfirmRename');
    if (btn) btn.disabled = true;

    fetch('/admin/media/rename', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': token,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ path: path, new_name: newName })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showMediaAlert('success', data.message);
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showMediaAlert('danger', data.message || 'নাম পরিবর্তন ব্যর্থ হয়েছে।');
        }
    })
    .catch(() => showMediaAlert('danger', 'সার্ভার অনুরোধ ব্যর্থ হয়েছে।'))
    .finally(() => {
        if (btn) btn.disabled = false;
    });
}

/* ========================================================================= */
/* 5. CREATE NEW FOLDER                                                      */
/* ========================================================================= */
function submitCreateFolder() {
    const name = document.getElementById('newFolderNameInput')?.value;
    const loc = document.getElementById('newFolderLocSelect')?.value || 'storage';
    if (!name) return;

    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const btn = document.getElementById('btnConfirmCreateFolder');
    if (btn) btn.disabled = true;

    fetch('/admin/media/create-folder', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': token,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ folder_name: name, location: loc })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showMediaAlert('success', data.message);
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showMediaAlert('danger', data.message || 'ফোল্ডার তৈরি ব্যর্থ হয়েছে।');
        }
    })
    .catch(() => showMediaAlert('danger', 'সার্ভার অনুরোধ ব্যর্থ হয়েছে।'))
    .finally(() => {
        if (btn) btn.disabled = false;
    });
}

/* ========================================================================= */
/* 6. LIGHTBOX & URL COPY                                                    */
/* ========================================================================= */
function copyUrl(url) {
    navigator.clipboard.writeText(url).then(() => {
        showMediaAlert('success', 'ইমেজ লিংক কপি হয়েছে!');
    });
}

function openLightbox(url, filename, size, date, dimensions, folder) {
    document.getElementById('lightboxImage').src = url;
    document.getElementById('lightboxTitle').textContent = filename;
    document.getElementById('lightboxMeta').textContent = `রেজোলিউশন: ${dimensions || 'N/A'} | সাইজ: ${size} | ফোল্ডার: ${folder} | আপলোড: ${date}`;
    document.getElementById('lightboxOpenBtn').href = url;

    const modalEl = document.getElementById('lightboxModal');
    if (modalEl && typeof bootstrap !== 'undefined') {
        const m = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
        m.show();
    }
}

/* ========================================================================= */
/* 7. RUN BATCH AUTO-OPTIMIZE ALL IMAGES                                     */
/* ========================================================================= */
function runMediaOptimization(btn) {
    const origContent = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = `<span class="spinner-border spinner-border-sm me-1.5"></span><span>অপ্টিমাইজেশন চলছে...</span>`;

    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    fetch('/admin/media/optimize-all', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': token,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = origContent;
        if (data.success) {
            showMediaAlert('success', data.message);
            setTimeout(() => window.location.reload(), 1500);
        } else {
            showMediaAlert('danger', data.message || 'অপ্টিমাইজেশন ব্যর্থ হয়েছে।');
        }
    })
    .catch(() => {
        btn.disabled = false;
        btn.innerHTML = origContent;
        showMediaAlert('danger', 'সার্ভার রেসপন্স দিতে ব্যর্থ হয়েছে।');
    });
}

/* ========================================================================= */
/* 8. CLIENT LIVE SEARCH & FILTER                                            */
/* ========================================================================= */
function filterMediaLive(query) {
    const q = (query || '').toLowerCase().trim();
    const cards = document.querySelectorAll('.media-item-card, .media-item-row');
    let count = 0;

    cards.forEach(el => {
        const name = (el.getAttribute('data-filename') || '').toLowerCase();
        const folder = (el.getAttribute('data-folder') || '').toLowerCase();
        const ext = (el.getAttribute('data-ext') || '').toLowerCase();

        if (!q || name.includes(q) || folder.includes(q) || ext.includes(q)) {
            el.style.display = '';
            count++;
        } else {
            el.style.display = 'none';
        }
    });

    const emptyNotice = document.getElementById('noSearchMatchNotice');
    if (emptyNotice) {
        emptyNotice.classList.toggle('d-none', count > 0 || cards.length === 0);
    }
}

/* ========================================================================= */
/* 9. TOAST NOTIFICATION                                                     */
/* ========================================================================= */
function showMediaAlert(type, message) {
    const container = document.getElementById('mediaLiveAlert');
    if (!container) return;

    const icon = (type === 'success') ? 'fa-circle-check text-success' : (type === 'warning' ? 'fa-triangle-exclamation text-warning' : 'fa-circle-exclamation text-danger');

    container.innerHTML = `
        <div class="alert alert-${type} alert-dismissible fade show d-flex align-items-center mb-0 rounded-4 shadow-sm border-0 border-start border-4 border-${type} bg-white py-3 px-4" role="alert">
            <i class="fa-solid ${icon} fs-4 me-3"></i>
            <div class="fw-semibold text-dark">${message}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    `;

    setTimeout(() => {
        const alert = container.querySelector('.alert');
        if (alert) {
            alert.classList.remove('show');
            setTimeout(() => alert.remove(), 250);
        }
    }, 4500);
}
