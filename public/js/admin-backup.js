/**
 * ============================================================================
 * Enterprise Disaster Recovery & Master Backup Hub — Client Engine
 * Idea Publication (ideaabd.com)
 * ============================================================================
 */

document.addEventListener('DOMContentLoaded', () => {
    initDragAndDrop();
    initLiveSearch();
    initBulkSelection();
});

/* ── 1. Drag and Drop Upload Handler ── */
function initDragAndDrop() {
    const dropZone = document.getElementById('dropZone');
    if (!dropZone) return;

    ['dragenter', 'dragover'].forEach(name => {
        dropZone.addEventListener(name, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropZone.classList.add('dragover');
        }, false);
    });

    ['dragleave', 'drop'].forEach(name => {
        dropZone.addEventListener(name, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropZone.classList.remove('dragover');
        }, false);
    });

    dropZone.addEventListener('drop', (e) => {
        const dt = e.dataTransfer;
        const files = dt ? dt.files : null;
        if (files && files.length > 0) {
            handleDynamicUpload(files);
        }
    });
}

function handleDynamicUpload(files) {
    if (!files || files.length === 0) return;
    const file = files[0];

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const formData = new FormData();
    formData.append('backup_file', file);
    formData.append('_token', csrfToken);

    // Show Progress UI
    const prompt = document.getElementById('dropZonePrompt');
    const progressSection = document.getElementById('uploadProgressSection');
    const progressBar = document.getElementById('uploadProgressBar');
    const percentText = document.getElementById('uploadPercentText');
    const filenameText = document.getElementById('uploadFilenameText');

    if (prompt) prompt.classList.add('d-none');
    if (progressSection) progressSection.classList.remove('d-none');
    if (filenameText) filenameText.textContent = `'${file.name}' আপলোড হচ্ছে...`;

    const xhr = new XMLHttpRequest();
    const uploadUrl = window.BACKUP_ROUTES ? window.BACKUP_ROUTES.upload : '/admin/backup/upload';
    xhr.open('POST', uploadUrl, true);
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

    xhr.upload.onprogress = function(e) {
        if (e.lengthComputable) {
            const percent = Math.round((e.loaded / e.total) * 100);
            if (progressBar) progressBar.style.width = percent + '%';
            if (percentText) percentText.textContent = percent + '%';
        }
    };

    xhr.onload = function() {
        if (xhr.status === 200) {
            try {
                const res = JSON.parse(xhr.responseText);
                if (filenameText) filenameText.textContent = 'আপলোড সম্পন্ন! তালিকা আপডেট হচ্ছে...';
                if (progressBar) {
                    progressBar.classList.remove('bg-primary');
                    progressBar.classList.add('bg-success');
                }
                showToast('success', res.message || 'ফাইল সফলভাবে আপলোড হয়েছে!');
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
            showToast('danger', err);
            resetUploadZone();
        }
    };

    xhr.onerror = function() {
        showToast('danger', 'নেটওয়ার্ক ত্রুটি! আপলোড সম্পন্ন করা যায়নি।');
        resetUploadZone();
    };

    xhr.send(formData);
}

function resetUploadZone() {
    const prompt = document.getElementById('dropZonePrompt');
    const progressSection = document.getElementById('uploadProgressSection');
    const progressBar = document.getElementById('uploadProgressBar');
    const input = document.getElementById('backupFileInput');

    if (prompt) prompt.classList.remove('d-none');
    if (progressSection) progressSection.classList.add('d-none');
    if (progressBar) {
        progressBar.style.width = '0%';
        progressBar.classList.remove('bg-success');
        progressBar.classList.add('bg-primary');
    }
    if (input) input.value = '';
}

/* ── 2. Live Table Search & Filter ── */
function initLiveSearch() {
    const searchInput = document.getElementById('backupSearchInput');
    const formatSelect = document.getElementById('backupFormatFilter');
    if (!searchInput && !formatSelect) return;

    function applyFilter() {
        const query = (searchInput ? searchInput.value : '').toLowerCase().trim();
        const format = (formatSelect ? formatSelect.value : 'all').toLowerCase();

        const rows = document.querySelectorAll('#backupsTableBody tr.table-custom-row');
        let visibleCount = 0;

        rows.forEach(row => {
            const filename = (row.dataset.filename || '').toLowerCase();
            const ext = (row.dataset.ext || '').toLowerCase();
            const date = (row.dataset.date || '').toLowerCase();

            const matchQuery = !query || filename.includes(query) || date.includes(query);
            const matchFormat = (format === 'all') || (format === ext) || (format === 'zip' && ext === 'zip');

            if (matchQuery && matchFormat) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        const countBadge = document.getElementById('backupCountBadge');
        if (countBadge) {
            countBadge.textContent = `${visibleCount} টি ফাইল`;
        }

        const emptySearch = document.getElementById('emptySearchRow');
        if (emptySearch) {
            emptySearch.style.display = (visibleCount === 0 && rows.length > 0) ? '' : 'none';
        }
    }

    if (searchInput) searchInput.addEventListener('input', applyFilter);
    if (formatSelect) formatSelect.addEventListener('change', applyFilter);
}

/* ── 3. Bulk Selection & Batch Actions ── */
function initBulkSelection() {
    const selectAll = document.getElementById('selectAllBackups');
    const bar = document.getElementById('bulkActionBar');
    const selectedCountSpan = document.getElementById('bulkSelectedCount');

    if (!selectAll) return;

    selectAll.addEventListener('change', () => {
        const checkboxes = document.querySelectorAll('.backup-select-cb:not(:disabled)');
        checkboxes.forEach(cb => {
            const row = cb.closest('tr');
            if (row && row.style.display !== 'none') {
                cb.checked = selectAll.checked;
            }
        });
        updateBulkBar();
    });

    document.addEventListener('change', (e) => {
        if (e.target && e.target.classList.contains('backup-select-cb')) {
            updateBulkBar();
        }
    });

    function updateBulkBar() {
        const checked = document.querySelectorAll('.backup-select-cb:checked');
        const count = checked.length;

        if (bar) {
            if (count > 0) {
                bar.style.display = 'flex';
                if (selectedCountSpan) selectedCountSpan.textContent = `${count} টি নির্বাচিত`;
            } else {
                bar.style.display = 'none';
            }
        }
    }
}

function deleteSelectedBackups() {
    const checked = document.querySelectorAll('.backup-select-cb:checked');
    if (checked.length === 0) return;

    if (!confirm(`আপনি কি নিশ্চিত যে নির্বাচিত ${checked.length}টি ব্যাকআপ ফাইল মুছে ফেলতে চান?`)) {
        return;
    }

    const filenames = Array.from(checked).map(cb => cb.value);
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = window.BACKUP_ROUTES ? window.BACKUP_ROUTES.bulkDelete : '/admin/backup/bulk-delete';

    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = csrfToken;
    form.appendChild(csrfInput);

    filenames.forEach(fn => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'filenames[]';
        input.value = fn;
        form.appendChild(input);
    });

    document.body.appendChild(form);
    form.submit();
}

/* ── 4. Live AJAX 1-Click Backup Creation with Progress Modal ── */
function triggerLiveBackup(mode, modeLabel) {
    const modalEl = document.getElementById('backupProgressModal');
    const modal = modalEl ? new bootstrap.Modal(modalEl) : null;
    if (modal) modal.show();

    const titleEl = document.getElementById('backupProgressTitle');
    const statusText = document.getElementById('backupProgressStatusText');
    const progressBar = document.getElementById('backupCreationProgressBar');

    if (titleEl) titleEl.textContent = modeLabel || 'ব্যাকআপ তৈরি হচ্ছে...';
    if (statusText) statusText.textContent = 'ডাটাবেজ টেবিল বিশ্লেষণ ও SQL ডাম্প তৈরি হচ্ছে...';
    if (progressBar) {
        progressBar.style.width = '30%';
        progressBar.classList.add('progress-bar-animated');
    }

    // Step 2 timer animation
    setTimeout(() => {
        if (statusText) statusText.textContent = 'মিডিয়া ফাইল, কভার ও ডকুমেন্টস প্যাকেজিং চলছে...';
        if (progressBar) progressBar.style.width = '65%';
    }, 1200);

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const createUrl = window.BACKUP_ROUTES ? window.BACKUP_ROUTES.create : '/admin/backup/create';

    fetch(createUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ mode: mode })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            if (statusText) statusText.textContent = 'সম্পূর্ণ হয়েছে! আর্কাইভ ফাইল সংরক্ষণ করা হয়েছে।';
            if (progressBar) {
                progressBar.style.width = '100%';
                progressBar.classList.remove('bg-primary');
                progressBar.classList.add('bg-success');
            }
            showToast('success', data.message || 'ব্যাকআপ সফলভাবে সম্পন্ন হয়েছে!');
            setTimeout(() => {
                window.location.reload();
            }, 800);
        } else {
            if (modal) modal.hide();
            showToast('danger', data.message || 'ব্যাকআপ তৈরিতে ত্রুটি ঘটেছে!');
        }
    })
    .catch(err => {
        if (modal) modal.hide();
        // If JSON failed, fall back to native submit
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = createUrl;
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = csrfToken;
        form.appendChild(csrfInput);
        const modeInput = document.createElement('input');
        modeInput.type = 'hidden';
        modeInput.name = 'mode';
        modeInput.value = mode;
        form.appendChild(modeInput);
        document.body.appendChild(form);
        form.submit();
    });
}

/* ── 5. Inspect ZIP Archive Modal ── */
function inspectZipArchive(filename) {
    const fnEl = document.getElementById('inspectFilename');
    if (fnEl) fnEl.textContent = filename;

    const modalEl = document.getElementById('inspectModal');
    const modal = modalEl ? new bootstrap.Modal(modalEl) : null;
    if (modal) modal.show();

    const body = document.getElementById('inspectBody');
    if (body) {
        body.innerHTML = '<div class="text-center py-5 text-muted"><div class="spinner-border spinner-border-sm text-primary me-2"></div> আর্কাইভ ফাইল স্ক্যান করা হচ্ছে...</div>';
    }

    const inspectUrl = (window.BACKUP_ROUTES ? window.BACKUP_ROUTES.inspect : '/admin/backup/inspect') + '/' + encodeURIComponent(filename);

    fetch(inspectUrl, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            let html = `
                <div class="row g-2.5 mb-3">
                    <div class="col-6 col-md-4">
                        <div class="p-3 bg-light rounded-3 text-center border">
                            <small class="text-muted d-block font-monospace" style="font-size: 11px;">মোট ফাইল সংখ্যা</small>
                            <strong class="font-monospace text-primary fs-5">${data.files_count} টি</strong>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="p-3 bg-light rounded-3 text-center border">
                            <small class="text-muted d-block font-monospace" style="font-size: 11px;">আর্কাইভ সাইজ</small>
                            <strong class="font-monospace text-success fs-5">${data.size}</strong>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="p-3 bg-light rounded-3 text-center border">
                            <small class="text-muted d-block font-monospace" style="font-size: 11px;">ডাটাবেজ ইঞ্জিন</small>
                            <strong class="font-monospace text-dark fs-5">${data.manifest ? data.manifest.driver.toUpperCase() : 'MySQL'}</strong>
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h6 class="fw-bold small text-muted text-uppercase mb-0 font-monospace" style="font-size: 11px;">আর্কাইভের ফাইল তালিকা:</h6>
                    <span class="badge bg-light text-dark border font-monospace" style="font-size: 10px;">প্রিভিউ মোড</span>
                </div>
                <div class="table-responsive rounded-3 border" style="max-height: 260px; overflow-y: auto;">
                    <table class="table table-sm table-hover small mb-0 font-monospace">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th class="ps-3 py-2">ফাইলের পথ</th>
                                <th class="text-end pe-3 py-2">সাইজ</th>
                            </tr>
                        </thead>
                        <tbody>
            `;
            (data.files || []).forEach(f => {
                html += `
                    <tr>
                        <td class="ps-3 text-truncate" style="max-width: 380px;">${f.name}</td>
                        <td class="text-end pe-3 text-muted">${f.size}</td>
                    </tr>
                `;
            });
            html += `</tbody></table></div>`;
            if (body) body.innerHTML = html;
        } else {
            if (body) body.innerHTML = `<div class="alert alert-danger mb-0">${data.message}</div>`;
        }
    })
    .catch(() => {
        if (body) body.innerHTML = `<div class="alert alert-danger mb-0">আর্কাইভ প্রিভিউ লোড করতে ব্যর্থ হয়েছে।</div>`;
    });
}

/* ── 6. Send to Email Modal ── */
function openEmailModal(filename) {
    const fnInput = document.getElementById('emailModalFilename');
    const fnDisplay = document.getElementById('emailModalFilenameDisplay');
    if (fnInput) fnInput.value = filename;
    if (fnDisplay) fnDisplay.textContent = filename;

    const modalEl = document.getElementById('emailDispatchModal');
    if (modalEl) new bootstrap.Modal(modalEl).show();
}

function submitEmailDispatch() {
    const filename = document.getElementById('emailModalFilename')?.value;
    const email = document.getElementById('emailRecipientInput')?.value;
    const btn = document.getElementById('btnSubmitEmail');

    if (!filename || !email) {
        alert('অনুগ্রহ করে সঠিক ইমেইল ঠিকানা প্রদান করুন।');
        return;
    }

    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> পাঠানো হচ্ছে...';
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const emailUrl = (window.BACKUP_ROUTES ? window.BACKUP_ROUTES.email : '/admin/backup/email') + '/' + encodeURIComponent(filename);

    fetch(emailUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ email: email })
    })
    .then(res => res.json())
    .then(data => {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-paper-plane me-1"></i> ইমেইলে পাঠান';
        }
        const modalEl = document.getElementById('emailDispatchModal');
        if (modalEl) bootstrap.Modal.getInstance(modalEl)?.hide();

        if (data.success) {
            showToast('success', data.message || 'ইমেইল সফলভাবে পাঠানো হয়েছে!');
        } else {
            showToast('danger', data.message || 'ইমেইল পাঠাতে ব্যর্থ হয়েছে।');
        }
    })
    .catch(() => {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-paper-plane me-1"></i> ইমেইলে পাঠান';
        }
        showToast('danger', 'ইমেইল প্রেরণ প্রক্রিয়ায় সমস্যা হয়েছে।');
    });
}

/* ── 7. Restore Confirmation ── */
function confirmRestore(filename, isMasterZip) {
    const fnDisplay = document.getElementById('restoreFilename');
    if (fnDisplay) fnDisplay.textContent = filename;

    const form = document.getElementById('restoreForm');
    const restoreBase = window.BACKUP_ROUTES ? window.BACKUP_ROUTES.restore : '/admin/backup/restore';
    if (form) form.action = restoreBase + '/' + encodeURIComponent(filename);

    const modalEl = document.getElementById('restoreModal');
    if (modalEl) new bootstrap.Modal(modalEl).show();
}

/* ── 8. Global Dynamic Toast ── */
function showToast(type, message) {
    const container = document.getElementById('dynamicAlertContainer');
    if (!container) return;

    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show d-flex align-items-center mb-0 rounded-4 shadow-sm border-0 border-start border-4 border-${type} bg-white py-3 px-3.5" role="alert">
            <i class="fa-solid fa-${type === 'success' ? 'circle-check text-success' : 'triangle-exclamation text-danger'} fs-5 me-2.5"></i>
            <div class="fw-semibold small text-dark">${message}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    container.innerHTML = alertHtml;
    container.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}
