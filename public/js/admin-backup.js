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
    initQuickCategoryTabs();
    initViewSwitcher();
    initStorageGrowthChart();
});

/* ── 0. Smart Quick Category Tabs & View Switcher ── */
let currentActiveCategory = 'all';

function initQuickCategoryTabs() {
    const tabs = document.querySelectorAll('.btn-filter-tab');
    if (!tabs || tabs.length === 0) return;

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            tabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            currentActiveCategory = tab.dataset.category || 'all';

            // Sync with format dropdown if exists
            const formatSelect = document.getElementById('backupFormatFilter');
            if (formatSelect) {
                if (currentActiveCategory === 'master_zip') formatSelect.value = 'zip';
                else if (currentActiveCategory === 'sql_dump') formatSelect.value = 'sql';
                else formatSelect.value = 'all';
            }

            applyUnifiedFilter();
        });
    });
}

function initViewSwitcher() {
    const btnTable = document.getElementById('btnViewTable');
    const btnGrid = document.getElementById('btnViewGrid');
    const tableView = document.getElementById('backupTableViewContainer');
    const gridView = document.getElementById('backupGridViewContainer');

    if (!btnTable || !btnGrid) return;

    const savedView = localStorage.getItem('idea_backup_view_pref') || 'table';
    setViewMode(savedView);

    btnTable.addEventListener('click', () => setViewMode('table'));
    btnGrid.addEventListener('click', () => setViewMode('grid'));

    function setViewMode(mode) {
        if (mode === 'grid') {
            btnTable.classList.remove('active');
            btnGrid.classList.add('active');
            if (tableView) tableView.style.display = 'none';
            if (gridView) gridView.style.display = 'grid';
            localStorage.setItem('idea_backup_view_pref', 'grid');
        } else {
            btnGrid.classList.remove('active');
            btnTable.classList.add('active');
            if (gridView) gridView.style.display = 'none';
            if (tableView) tableView.style.display = 'block';
            localStorage.setItem('idea_backup_view_pref', 'table');
        }
    }
}

/* ── Storage Growth Trend Chart (Chart.js Integration) ── */
function initStorageGrowthChart() {
    const ctx = document.getElementById('storageGrowthChartCanvas');
    if (!ctx || typeof Chart === 'undefined') return;

    const chartData = window.STORAGE_TIMELINE_DATA || {
        labels: ['May', 'Jun', 'Jul', 'Aug', 'Sep'],
        backup_sizes_mb: [10, 15, 20, 25, 30],
        archive_counts: [2, 3, 4, 5, 6]
    };

    const gradient = ctx.getContext('2d').createLinearGradient(0, 0, 0, 160);
    gradient.addColorStop(0, 'rgba(79, 70, 229, 0.35)');
    gradient.addColorStop(1, 'rgba(79, 70, 229, 0.00)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData.labels,
            datasets: [{
                label: 'Total Backup Size (MB)',
                data: chartData.backup_sizes_mb,
                borderColor: '#4f46e5',
                borderWidth: 2.5,
                backgroundColor: gradient,
                fill: true,
                tension: 0.38,
                pointBackgroundColor: '#4f46e5',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#0f172a',
                    titleFont: { size: 12, family: 'monospace', weight: 'bold' },
                    bodyFont: { size: 11, family: 'monospace' },
                    padding: 10,
                    cornerRadius: 8,
                    callbacks: {
                        label: function(context) {
                            return ' Backup Volume: ' + context.parsed.y + ' MB';
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 11, family: 'monospace' }, color: '#64748b' }
                },
                y: {
                    grid: { color: 'rgba(226, 232, 240, 0.6)' },
                    ticks: {
                        font: { size: 10, family: 'monospace' },
                        color: '#64748b',
                        callback: function(value) { return value + ' MB'; }
                    }
                }
            }
        }
    });
}

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
    if (filenameText) filenameText.textContent = `Uploading '${file.name}'...`;

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
                if (filenameText) filenameText.textContent = 'Upload complete! Refreshing archive list...';
                if (progressBar) {
                    progressBar.classList.remove('bg-primary');
                    progressBar.classList.add('bg-success');
                }
                showToast('success', res.message || 'Backup archive uploaded successfully!');
                setTimeout(() => window.location.reload(), 600);
            } catch(e) {
                window.location.reload();
            }
        } else {
            let err = 'Upload failed! Please check the archive file format and size.';
            try {
                const errRes = JSON.parse(xhr.responseText);
                if (errRes.message) err = errRes.message;
            } catch(e){}
            showToast('danger', err);
            resetUploadZone();
        }
    };

    xhr.onerror = function() {
        showToast('danger', 'Network error! Upload could not be completed.');
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

/* ── 2. Unified Live Table & Grid Search & Category Filter ── */
function applyUnifiedFilter() {
    const searchInput = document.getElementById('backupSearchInput');
    const formatSelect = document.getElementById('backupFormatFilter');
    const query = (searchInput ? searchInput.value : '').toLowerCase().trim();
    const format = (formatSelect ? formatSelect.value : 'all').toLowerCase();

    const tableRows = document.querySelectorAll('#backupsTableBody tr.table-custom-row');
    const gridCards = document.querySelectorAll('#backupGridViewContainer .backup-file-card');

    let visibleCount = 0;

    function matchesFilter(element) {
        const filename = (element.dataset.filename || '').toLowerCase();
        const ext = (element.dataset.ext || '').toLowerCase();
        const date = (element.dataset.date || '').toLowerCase();
        const category = (element.dataset.category || '').toLowerCase();

        // 1. Text Query Match
        const matchQuery = !query || filename.includes(query) || date.includes(query);

        // 2. Format Dropdown Match
        const matchFormat = (format === 'all') || (format === ext) || (format === 'zip' && ext === 'zip');

        // 3. Category Tab Match
        let matchCategory = true;
        if (currentActiveCategory === 'master_zip') {
            matchCategory = (category === 'master_zip' || ext === 'zip');
        } else if (currentActiveCategory === 'sql_dump') {
            matchCategory = (category === 'sql_dump' || ext === 'sql' || ext === 'sqlite' || ext === 'gz');
        } else if (currentActiveCategory === 'safety') {
            matchCategory = (category === 'safety' || filename.includes('safety') || filename.includes('snapshot'));
        } else if (currentActiveCategory === 'anonymized') {
            matchCategory = (category === 'anonymized' || filename.includes('anonymized') || filename.includes('masked') || filename.includes('dev'));
        }

        return matchQuery && matchFormat && matchCategory;
    }

    // Filter Table Rows
    tableRows.forEach(row => {
        if (matchesFilter(row)) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    // Filter Grid Cards
    let gridVisibleCount = 0;
    gridCards.forEach(card => {
        if (matchesFilter(card)) {
            card.style.display = '';
            gridVisibleCount++;
        } else {
            card.style.display = 'none';
        }
    });

    // If grid is active, use its count or table count
    const totalVisible = tableRows.length > 0 ? visibleCount : gridVisibleCount;

    const countBadge = document.getElementById('backupCountBadge');
    if (countBadge) {
        countBadge.textContent = `${totalVisible} File${totalVisible === 1 ? '' : 's'}`;
    }

    const emptySearch = document.getElementById('emptySearchRow');
    if (emptySearch) {
        emptySearch.style.display = (visibleCount === 0 && tableRows.length > 0) ? '' : 'none';
    }

    const emptyGrid = document.getElementById('emptyGridState');
    if (emptyGrid) {
        emptyGrid.style.display = (gridVisibleCount === 0 && gridCards.length > 0) ? 'block' : 'none';
    }
}

function initLiveSearch() {
    const searchInput = document.getElementById('backupSearchInput');
    const formatSelect = document.getElementById('backupFormatFilter');

    if (searchInput) searchInput.addEventListener('input', applyUnifiedFilter);
    if (formatSelect) formatSelect.addEventListener('change', () => {
        applyUnifiedFilter();
    });
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
                if (selectedCountSpan) selectedCountSpan.textContent = `${count} Selected`;
            } else {
                bar.style.display = 'none';
            }
        }
    }
}

function deleteSelectedBackups() {
    const checked = document.querySelectorAll('.backup-select-cb:checked');
    if (checked.length === 0) return;

    if (!confirm(`Are you sure you want to permanently delete ${checked.length} selected backup archive(s)? This action cannot be undone.`)) {
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

    if (titleEl) titleEl.textContent = modeLabel || 'Creating Backup Archive...';
    if (statusText) statusText.textContent = 'Analyzing database tables & generating SQL dump...';
    if (progressBar) {
        progressBar.style.width = '30%';
        progressBar.classList.add('progress-bar-animated');
    }

    // Step 2 timer animation
    setTimeout(() => {
        if (statusText) statusText.textContent = 'Packaging media files, book covers & documents...';
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
            if (statusText) statusText.textContent = 'Completed! Master archive saved successfully.';
            if (progressBar) {
                progressBar.style.width = '100%';
                progressBar.classList.remove('bg-primary');
                progressBar.classList.add('bg-success');
            }
            showToast('success', data.message || 'Backup completed successfully!');
            setTimeout(() => {
                window.location.reload();
            }, 800);
        } else {
            if (modal) modal.hide();
            showToast('danger', data.message || 'Error occurred while creating backup!');
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
        body.innerHTML = '<div class="text-center py-5 text-muted"><div class="spinner-border spinner-border-sm text-primary me-2"></div> Scanning archive contents...</div>';
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
                            <small class="text-muted d-block font-monospace" style="font-size: 11px;">Total Files</small>
                            <strong class="font-monospace text-primary fs-5">${data.files_count}</strong>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="p-3 bg-light rounded-3 text-center border">
                            <small class="text-muted d-block font-monospace" style="font-size: 11px;">Archive Size</small>
                            <strong class="font-monospace text-success fs-5">${data.size}</strong>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="p-3 bg-light rounded-3 text-center border">
                            <small class="text-muted d-block font-monospace" style="font-size: 11px;">Database Engine</small>
                            <strong class="font-monospace text-dark fs-5">${data.manifest ? data.manifest.driver.toUpperCase() : 'MySQL'}</strong>
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h6 class="fw-bold small text-muted text-uppercase mb-0 font-monospace" style="font-size: 11px;">Archive File Manifest:</h6>
                    <span class="badge bg-light text-dark border font-monospace" style="font-size: 10px;">Preview Mode</span>
                </div>
                <div class="table-responsive rounded-3 border" style="max-height: 260px; overflow-y: auto;">
                    <table class="table table-sm table-hover small mb-0 font-monospace">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th class="ps-3 py-2">File Path</th>
                                <th class="text-end pe-3 py-2">Size</th>
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
        if (body) body.innerHTML = `<div class="alert alert-danger mb-0">Failed to load archive preview.</div>`;
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
        alert('Please provide a valid recipient email address.');
        return;
    }

    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Sending email...';
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
            btn.innerHTML = '<i class="fa-solid fa-paper-plane me-1"></i> Send via Email';
        }
        const modalEl = document.getElementById('emailDispatchModal');
        if (modalEl) bootstrap.Modal.getInstance(modalEl)?.hide();

        if (data.success) {
            showToast('success', data.message || 'Backup archive successfully sent to email!');
        } else {
            showToast('danger', data.message || 'Failed to dispatch email.');
        }
    })
    .catch(() => {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-paper-plane me-1"></i> Send via Email';
        }
        showToast('danger', 'Error occurred during email dispatch process.');
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

/* ── 9. Database Diff & Analytics Modal ── */
let currentDiffData = null;
let currentDiffFilename = '';

function openDiffModal(filename) {
    currentDiffFilename = filename;
    const fnEl = document.getElementById('diffModalFilename');
    if (fnEl) fnEl.textContent = filename;

    const modalEl = document.getElementById('diffModal');
    const modal = modalEl ? new bootstrap.Modal(modalEl) : null;
    if (modal) modal.show();

    const body = document.getElementById('diffModalBody');
    if (body) {
        body.innerHTML = `
            <div class="text-center py-5 text-muted">
                <div class="spinner-border text-info mb-2" role="status"></div>
                <div class="fw-semibold font-monospace">Comparing backup schema and rows against live database...</div>
            </div>
        `;
    }

    const diffUrl = (window.BACKUP_ROUTES ? window.BACKUP_ROUTES.diff : '/admin/backup/diff') + '/' + encodeURIComponent(filename);

    fetch(diffUrl, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            currentDiffData = data;
            renderDiffResults(data);
        } else {
            if (body) body.innerHTML = `<div class="alert alert-danger mb-0">${data.message}</div>`;
        }
    })
    .catch(err => {
        if (body) body.innerHTML = `<div class="alert alert-danger mb-0">Failed to load database diff analysis.</div>`;
    });
}

function renderDiffResults(data) {
    const body = document.getElementById('diffModalBody');
    if (!body) return;

    let html = `
        <div class="row g-2.5 mb-3">
            <div class="col-6 col-md-3">
                <div class="p-3 bg-light rounded-3 text-center border">
                    <small class="text-muted d-block font-monospace" style="font-size: 11px;">Total Tables</small>
                    <strong class="font-monospace text-primary fs-5">${data.total_tables}</strong>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="p-3 bg-light rounded-3 text-center border">
                    <small class="text-muted d-block font-monospace" style="font-size: 11px;">Changes Detected</small>
                    <strong class="font-monospace ${data.changed_tables_count > 0 ? 'text-warning' : 'text-success'} fs-5">${data.changed_tables_count} Table${data.changed_tables_count === 1 ? '' : 's'}</strong>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="p-3 bg-light rounded-3 text-center border">
                    <small class="text-muted d-block font-monospace" style="font-size: 11px;">Total Live Rows</small>
                    <strong class="font-monospace text-dark fs-5">${data.total_live_rows.toLocaleString()}</strong>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="p-3 bg-light rounded-3 text-center border">
                    <small class="text-muted d-block font-monospace" style="font-size: 11px;">Total Backup Rows</small>
                    <strong class="font-monospace text-dark fs-5">${data.total_backup_rows.toLocaleString()}</strong>
                </div>
            </div>
        </div>

        <div class="d-flex align-items-center justify-content-between mb-2 gap-2">
            <div class="d-flex align-items-center gap-2">
                <h6 class="fw-bold small text-muted text-uppercase mb-0 font-monospace" style="font-size: 11px;">Table-by-Table Comparison:</h6>
                <span class="badge ${data.changed_tables_count === 0 ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning'} border rounded-pill px-2.5 py-0.5 font-monospace" style="font-size: 10px;">
                    ${data.changed_tables_count === 0 ? '100% Exact Match' : `${data.changed_tables_count} Differences Found`}
                </span>
            </div>
            <input type="search" id="diffSearchInput" class="form-control form-control-sm font-monospace" style="max-width: 220px;" placeholder="Filter tables..." oninput="filterDiffTable(this.value)">
        </div>

        <div class="table-responsive rounded-3 border" style="max-height: 350px; overflow-y: auto;">
            <table class="table table-sm table-hover align-middle mb-0 font-monospace small" id="diffTable">
                <thead class="table-light sticky-top">
                    <tr>
                        <th class="ps-3 py-2">Table Name</th>
                        <th class="py-2 text-center">Live Records</th>
                        <th class="py-2 text-center">Backup Records</th>
                        <th class="py-2 text-center">Difference (Diff)</th>
                        <th class="text-end pe-3 py-2">Status</th>
                    </tr>
                </thead>
                <tbody id="diffTableBody">
    `;

    (data.tables || []).forEach(row => {
        let statusBadge = '';
        let diffBadge = '';

        if (row.status === 'equal') {
            statusBadge = '<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0.5"><i class="fa-solid fa-check me-1"></i> Exact Match</span>';
            diffBadge = '<span class="text-muted font-monospace">0</span>';
        } else if (row.status === 'live_higher') {
            statusBadge = '<span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2 py-0.5"><i class="fa-solid fa-arrow-up me-1"></i> Higher in Live</span>';
            diffBadge = `<span class="badge bg-primary text-white font-monospace">+${row.diff}</span>`;
        } else if (row.status === 'backup_higher') {
            statusBadge = '<span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-2 py-0.5"><i class="fa-solid fa-arrow-down me-1"></i> Higher in Backup</span>';
            diffBadge = `<span class="badge bg-warning text-dark font-monospace">${row.diff}</span>`;
        } else if (row.status === 'only_in_live') {
            statusBadge = '<span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-2 py-0.5">Live Only</span>';
            diffBadge = '<span class="text-info font-monospace">New</span>';
        } else if (row.status === 'only_in_backup') {
            statusBadge = '<span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-0.5">Backup Only</span>';
            diffBadge = '<span class="text-danger font-monospace">Removed</span>';
        }

        html += `
            <tr class="diff-table-row" data-tbl="${(row.table || '').toLowerCase()}">
                <td class="ps-3 font-monospace fw-bold text-dark">${row.table}</td>
                <td class="text-center font-monospace">${row.live_rows !== null ? row.live_rows.toLocaleString() : '—'}</td>
                <td class="text-center font-monospace">${row.backup_rows !== null ? row.backup_rows.toLocaleString() : '—'}</td>
                <td class="text-center font-monospace">${diffBadge}</td>
                <td class="text-end pe-3">${statusBadge}</td>
            </tr>
        `;
    });

    html += `</tbody></table></div>`;
    body.innerHTML = html;
}

function filterDiffTable(query) {
    const q = (query || '').toLowerCase().trim();
    const rows = document.querySelectorAll('#diffTableBody tr.diff-table-row');
    rows.forEach(r => {
        const tbl = r.dataset.tbl || '';
        r.style.display = (!q || tbl.includes(q)) ? '' : 'none';
    });
}

function openSelectiveFromDiff() {
    const diffModalEl = document.getElementById('diffModal');
    if (diffModalEl) bootstrap.Modal.getInstance(diffModalEl)?.hide();

    if (currentDiffFilename) {
        openSelectiveRestoreModal(currentDiffFilename);
    }
}

/* ── 10. Safe Dry-Run Simulation Modal ── */
function runDryRunSimulation(filename) {
    const modalEl = document.getElementById('dryRunModal');
    const modal = modalEl ? new bootstrap.Modal(modalEl) : null;
    if (modal) modal.show();

    const body = document.getElementById('dryRunModalBody');
    if (body) {
        body.innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-grow text-primary mb-3" style="width: 3rem; height: 3rem;" role="status"></div>
                <h6 class="fw-bold text-dark font-monospace mb-1">Running sandbox restore simulation...</h6>
                <p class="text-muted small mb-0">Validating SQL syntax, foreign-key constraints & schema integrity without altering live data.</p>
            </div>
        `;
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const dryRunUrl = (window.BACKUP_ROUTES ? window.BACKUP_ROUTES.dryRun : '/admin/backup/dry-run') + '/' + encodeURIComponent(filename);

    fetch(dryRunUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success && data.status === 'passed') {
            if (body) {
                body.innerHTML = `
                    <div class="text-center py-4">
                        <div class="rounded-circle bg-success-subtle text-success p-3 d-inline-flex align-items-center justify-content-center mb-3 shadow-xs" style="width: 68px; height: 68px;">
                            <i class="fa-solid fa-circle-check fs-1"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-1 font-monospace">Dry-Run Passed with Zero Errors!</h5>
                        <p class="text-muted small mb-3">${data.message}</p>
                        
                        <div class="row g-2 text-start p-3 bg-light rounded-3 border mb-3">
                            <div class="col-6">
                                <small class="text-muted font-monospace d-block" style="font-size: 11px;">Simulation Latency:</small>
                                <strong class="font-monospace text-primary">${data.execution_time_ms} ms</strong>
                            </div>
                            <div class="col-6">
                                <small class="text-muted font-monospace d-block" style="font-size: 11px;">Database Safety:</small>
                                <strong class="font-monospace text-success"><i class="fa-solid fa-shield-halved me-1"></i> 100% Uncommitted Sandbox</strong>
                            </div>
                        </div>

                        <div class="d-flex justify-content-center gap-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn-backup-gradient btn-gradient-emerald px-4" onclick="bootstrap.Modal.getInstance(document.getElementById('dryRunModal'))?.hide(); confirmRestore('${filename}', true)">
                                <i class="fa-solid fa-rotate-left"></i>
                                <span>Confirm Live Restore</span>
                            </button>
                        </div>
                    </div>
                `;
            }
        } else {
            if (body) {
                body.innerHTML = `
                    <div class="text-center py-4">
                        <div class="rounded-circle bg-danger-subtle text-danger p-3 d-inline-flex align-items-center justify-content-center mb-3 shadow-xs" style="width: 68px; height: 68px;">
                            <i class="fa-solid fa-triangle-exclamation fs-1"></i>
                        </div>
                        <h5 class="fw-bold text-danger mb-1 font-monospace">Dry-Run Failed! Issues Detected</h5>
                        <p class="text-muted small mb-3">${data.message || 'Errors encountered in SQL statements or constraint checks.'}</p>
                        ${data.error_details ? `<div class="p-3 bg-danger-subtle text-danger rounded-3 font-monospace small text-start border border-danger-subtle mb-3">${data.error_details}</div>` : ''}
                    </div>
                `;
            }
        }
    })
    .catch(err => {
        if (body) {
            body.innerHTML = `<div class="alert alert-danger mb-0">Network or server error occurred during dry-run simulation.</div>`;
        }
    });
}

/* ── 11. Selective Table Restore Modal & Handler ── */
let currentSelectiveFilename = '';

function openSelectiveRestoreModal(filename) {
    currentSelectiveFilename = filename;
    const fnEl = document.getElementById('selectiveModalFilename');
    if (fnEl) fnEl.textContent = filename;

    // Reset checkboxes
    toggleAllSelectiveTables(false);

    const modalEl = document.getElementById('selectiveRestoreModal');
    if (modalEl) new bootstrap.Modal(modalEl).show();

    initSelectiveSearch();
}

function toggleAllSelectiveTables(check) {
    const cbs = document.querySelectorAll('.selective-tbl-cb');
    cbs.forEach(cb => {
        const col = cb.closest('.selective-tbl-col');
        if (col && col.style.display !== 'none') {
            cb.checked = check;
        }
    });
    updateSelectiveCount();
}

function updateSelectiveCount() {
    const checked = document.querySelectorAll('.selective-tbl-cb:checked');
    const badge = document.getElementById('selectiveSelectedBadge');
    if (badge) {
        badge.textContent = `${checked.length} Selected`;
    }
}

function initSelectiveSearch() {
    const input = document.getElementById('selectiveSearchInput');
    if (!input) return;

    input.value = '';
    input.oninput = function() {
        const q = (input.value || '').toLowerCase().trim();
        const cols = document.querySelectorAll('.selective-tbl-col');
        cols.forEach(col => {
            const tbl = col.dataset.tbl || '';
            col.style.display = (!q || tbl.includes(q)) ? '' : 'none';
        });
    };
}

function submitSelectiveRestore() {
    const checked = document.querySelectorAll('.selective-tbl-cb:checked');
    if (checked.length === 0) {
        alert('Please select at least one database table to restore.');
        return;
    }

    const tables = Array.from(checked).map(cb => cb.value);
    if (!confirm(`Are you sure you want to restore ${tables.length} selected table(s) (${tables.slice(0, 3).join(', ')}${tables.length > 3 ? '...' : ''})?`)) {
        return;
    }

    const btn = document.getElementById('btnExecuteSelectiveRestore');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Restoring tables...';
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const selectiveUrl = (window.BACKUP_ROUTES ? window.BACKUP_ROUTES.selectiveRestore : '/admin/backup/selective-restore') + '/' + encodeURIComponent(currentSelectiveFilename);

    fetch(selectiveUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ tables: tables })
    })
    .then(res => res.json())
    .then(data => {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-rotate-left"></i> <span>Restore Selected Tables</span>';
        }
        const modalEl = document.getElementById('selectiveRestoreModal');
        if (modalEl) bootstrap.Modal.getInstance(modalEl)?.hide();

        if (data.success) {
            showToast('success', data.message || 'Selective restore completed successfully!');
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showToast('danger', data.message || 'Selective restore encountered errors.');
        }
    })
    .catch(err => {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-rotate-left"></i> <span>Restore Selected Tables</span>';
        }
        showToast('danger', 'Error occurred during selective restore execution.');
    });
}

/* ── 12. Anonymized Developer Dump ── */
function generateAnonymizedDump() {
    if (!confirm('Do you want to export an anonymized database dump with sensitive customer data (passwords, emails, phone numbers) masked?')) {
        return;
    }

    showToast('info', 'Generating anonymized developer dump, please wait...');

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const exportUrl = window.BACKUP_ROUTES ? window.BACKUP_ROUTES.exportAnonymized : '/admin/backup/export-anonymized';

    fetch(exportUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast('success', data.message || 'Anonymized dump generated successfully!');
            if (data.download_url) {
                const a = document.createElement('a');
                a.href = data.download_url;
                a.download = data.filename || 'anonymized_backup.sql';
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
            }
            setTimeout(() => window.location.reload(), 1200);
        } else {
            showToast('danger', data.message || 'Error occurred while generating anonymized dump!');
        }
    })
    .catch(() => {
        // Fallback to standard form download
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = exportUrl;
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = csrfToken;
        form.appendChild(csrfInput);
        document.body.appendChild(form);
        form.submit();
    });
}

/* ── 13. Test Telegram Instant Alert Notification ── */
function testTelegramNotification() {
    const token = document.getElementById('telegramBotTokenInput')?.value;
    const chatId = document.getElementById('telegramChatIdInput')?.value;

    if (!token || !chatId) {
        alert('Please provide both Telegram Bot Token and Chat ID.');
        return;
    }

    showToast('info', 'Sending test Telegram message...');

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const testUrl = window.BACKUP_ROUTES ? window.BACKUP_ROUTES.testNotification : '/admin/backup/test-notification';

    fetch(testUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            type: 'telegram',
            telegram_bot_token: token,
            telegram_chat_id: chatId
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast('success', data.message || 'Telegram test notification delivered successfully!');
        } else {
            showToast('danger', data.message || 'Failed to send Telegram test message.');
        }
    })
    .catch(() => {
        showToast('danger', 'Could not reach the Telegram notification server.');
    });
}
