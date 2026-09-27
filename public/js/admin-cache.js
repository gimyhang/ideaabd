/**
 * Cache Management & Performance Hub JavaScript
 * Enterprise Grade Frontend Logic - Idea Prokashon
 */

let autoRefreshTimer = null;

document.addEventListener('DOMContentLoaded', function() {
    // 1. Initialize Table Search Filter
    const searchInput = document.getElementById('cacheKeySearchInput');
    if (searchInput) {
        searchInput.addEventListener('input', filterCacheKeysTable);
    }

    // 2. Initialize Terminal Input Enter Key
    const cmdInput = document.getElementById('customArtisanCmd');
    if (cmdInput) {
        cmdInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                runCustomTerminalCommand();
            }
        });
    }

    // 3. Initialize Select All Checkboxes
    const selectAllModules = document.getElementById('selectAllModulesCb');
    if (selectAllModules) {
        selectAllModules.addEventListener('change', function() {
            document.querySelectorAll('.module-select-cb').forEach(cb => cb.checked = selectAllModules.checked);
        });
    }

    const selectAllKeys = document.getElementById('selectAllKeysCb');
    if (selectAllKeys) {
        selectAllKeys.addEventListener('change', function() {
            document.querySelectorAll('.key-select-cb').forEach(cb => cb.checked = selectAllKeys.checked);
        });
    }
});

/**
 * 1. Execute Cache Action via AJAX
 */
function executeCacheAction(url, loadingMsg, btn) {
    if (!url) return;

    let originalHtml = '';
    if (btn) {
        btn.disabled = true;
        originalHtml = btn.innerHTML;
        btn.innerHTML = `<i class="fa-solid fa-circle-notch fa-spin me-1.5"></i> ${loadingMsg || 'Processing...'}`;
    }

    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                  document.querySelector('input[name="_token"]')?.value || '';

    fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': token,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showCacheAlert('success', data.message || 'Cache action completed successfully!');
            refreshCacheMetrics();
        } else {
            showCacheAlert('danger', data.message || 'Action failed.');
        }
    })
    .catch(err => {
        console.error('Cache Action Error:', err);
        showCacheAlert('danger', 'Network request failed. Please check connection.');
    })
    .finally(() => {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        }
    });
}

/**
 * 2. Confirm & Run Master Cache Purge
 */
function confirmMasterPurge(btn) {
    if (!confirm('আপনি কি নিশ্চিত যে সমস্ত সিস্টেম ক্যাশ, ভিউ, কনফিগারেশন এবং রুট ক্যাশ একসাথে সম্পূর্ণ ক্লিয়ার করতে চান?')) {
        return;
    }
    executeCacheAction('/admin/cache/clear-all', 'Purging All Cache...', btn);
}

/**
 * 3. Purge Multiple Selected Modules
 */
function purgeMultipleSelectedModules() {
    const checked = document.querySelectorAll('.module-select-cb:checked');
    if (checked.length === 0) {
        showCacheAlert('info', 'অনুগ্রহ করে অন্তত একটি ক্যাশ মডিউল সিলেক্ট করুন।');
        return;
    }

    if (!confirm(`আপনি নির্বাচিত ${checked.length} টি ক্যাশ মডিউল একসাথে ক্লিয়ার করতে চান?`)) {
        return;
    }

    let completed = 0;
    const total = checked.length;
    showCacheAlert('info', `নির্বাচিত ${total} টি মডিউল প্রসেস হচ্ছে...`);

    checked.forEach(cb => {
        const route = cb.getAttribute('data-route');
        if (route) {
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            fetch(route, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(r => r.json())
            .then(() => {
                completed++;
                if (completed === total) {
                    showCacheAlert('success', `সফলভাবে ${total} টি ক্যাশ মডিউল ক্লিয়ার সম্পন্ন হয়েছে!`);
                    refreshCacheMetrics();
                }
            })
            .catch(() => {
                completed++;
            });
        }
    });
}

/**
 * 4. Run Artisan Console Command
 */
function runTerminalCommand(cmd) {
    if (!cmd) return;

    appendTerminalLine(`\n$ php artisan ${cmd}\n[EXECUTING...] Please wait...`);
    const clock = document.getElementById('terminalClock');

    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    fetch('/admin/cache/run-artisan', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': token,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ command: cmd })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            appendTerminalLine(data.output ? data.output.trim() : '[SUCCESS] Command completed successfully.');
            appendTerminalLine(`--- [DONE in ${data.time || '0.12s'}] ---`);
            showCacheAlert('success', `Artisan command "${cmd}" executed successfully!`);
            refreshCacheMetrics();
        } else {
            appendTerminalLine(`[FAILED] ${data.message || 'Execution error.'}`);
            showCacheAlert('danger', data.message || 'Artisan command execution failed.');
        }
    })
    .catch(err => {
        console.error('Artisan error:', err);
        appendTerminalLine(`[NETWORK ERROR] Could not execute $ php artisan ${cmd}`);
    })
    .finally(() => {
        if (clock) clock.textContent = new Date().toLocaleTimeString();
    });
}

function runCustomTerminalCommand() {
    const input = document.getElementById('customArtisanCmd');
    if (!input) return;
    const cmd = input.value.trim();
    if (!cmd) return;
    runTerminalCommand(cmd);
    input.value = '';
}

function appendTerminalLine(text) {
    const term = document.getElementById('terminalOutput');
    if (!term) return;
    term.textContent += '\n' + text;
    term.scrollTop = term.scrollHeight;
}

function clearTerminalLog() {
    const term = document.getElementById('terminalOutput');
    if (term) term.textContent = '// Terminal cleared.\nideaabd-cache-runner@production:~$ Ready for commands.';
}

function copyTerminalOutput() {
    const term = document.getElementById('terminalOutput');
    if (!term) return;
    navigator.clipboard.writeText(term.textContent).then(() => {
        showCacheAlert('success', 'Terminal log copied to clipboard!');
    });
}

/**
 * 5. Live AJAX Cache Metrics Refresher
 */
function refreshCacheMetrics(btn) {
    const icon = document.getElementById('refreshIcon');
    const text = document.getElementById('refreshText');
    if (icon) icon.classList.add('fa-spin');
    if (text) text.textContent = 'Refreshing...';

    fetch('/admin/cache/stats-json', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success && data.stats) {
            const st = data.stats;
            const vFiles = document.getElementById('statViewFiles');
            const vSize = document.getElementById('statViewSize');
            const dSize = document.getElementById('statDataSize');
            const opHit = document.getElementById('statOpcacheHit');
            const opMem = document.getElementById('statOpcacheMem');
            const opBar = document.getElementById('statOpcacheProgressBar');
            const opLabel = document.getElementById('statOpcachePercentLabel');

            if (vFiles) vFiles.textContent = Number(st.view_files_count).toLocaleString() + ' files';
            if (vSize) vSize.innerHTML = 'Size: <strong class="text-dark">' + st.view_cache_size + '</strong>';
            if (dSize) dSize.textContent = st.data_cache_size;
            if (opHit) opHit.textContent = st.opcache_hit_rate;
            if (opMem) opMem.innerHTML = 'RAM: <strong class="text-dark">' + st.opcache_memory_used + '</strong>';
            if (opBar && st.opcache_memory_percent) opBar.style.width = st.opcache_memory_percent + '%';
            if (opLabel && st.opcache_memory_percent) opLabel.textContent = st.opcache_memory_percent + '%';

            showCacheAlert('info', 'Live metrics updated (' + (data.timestamp || new Date().toLocaleTimeString()) + ')');
        }
    })
    .catch(err => console.error('Refresh stats error:', err))
    .finally(() => {
        if (icon) icon.classList.remove('fa-spin');
        if (text) text.textContent = 'Refresh';
    });
}

/**
 * 6. Auto-Refresh Toggle
 */
function toggleAutoRefresh(cb) {
    const label = document.getElementById('autoRefreshLabel');
    if (cb.checked) {
        label.textContent = 'Live Active (30s)';
        label.classList.add('text-success');
        autoRefreshTimer = setInterval(() => refreshCacheMetrics(), 30000);
        showCacheAlert('info', 'Auto-refresh active (polls every 30 seconds).');
    } else {
        label.textContent = 'Auto-Refresh';
        label.classList.remove('text-success');
        if (autoRefreshTimer) clearInterval(autoRefreshTimer);
    }
}

/**
 * 7. Key Inspector Table Search Filter
 */
function filterCacheKeysTable() {
    const query = (document.getElementById('cacheKeySearchInput')?.value || '').toLowerCase().trim();
    const rows = document.querySelectorAll('#cacheKeysTable tbody tr.cache-key-row');
    let visibleCount = 0;

    rows.forEach(row => {
        const key = (row.getAttribute('data-key') || '').toLowerCase();
        const label = (row.getAttribute('data-label') || '').toLowerCase();
        if (key.includes(query) || label.includes(query)) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    const countEl = document.getElementById('cacheKeyVisibleCount');
    if (countEl) countEl.textContent = visibleCount;
}

/**
 * 8. Inspect Cache Key Payload Modal
 */
function inspectKeyPayload(keyName) {
    if (!keyName) return;

    const modalTitle = document.getElementById('keyModalTitle');
    const modalKey = document.getElementById('keyModalKeyName');
    const modalPayload = document.getElementById('keyModalPayload');
    const modalSize = document.getElementById('keyModalSize');
    const modalTtl = document.getElementById('keyModalTtl');

    if (modalTitle) modalTitle.textContent = `Payload Inspector — ${keyName}`;
    if (modalKey) modalKey.textContent = keyName;
    if (modalPayload) modalPayload.textContent = 'Fetching cached payload from Redis/File storage...';

    const modalEl = document.getElementById('keyInspectorModal');
    let bsModal = null;
    if (modalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        bsModal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
        bsModal.show();
    }

    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    fetch('/admin/cache/inspect-key', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': token,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ key: keyName })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            if (modalPayload) {
                modalPayload.textContent = data.payload ? JSON.stringify(data.payload, null, 2) : '(Empty / Null Value)';
            }
            if (modalSize) modalSize.textContent = data.size || 'N/A';
            if (modalTtl) modalTtl.textContent = data.ttl ? `${data.ttl} sec` : 'Forever / Not Expiring';
        } else {
            if (modalPayload) modalPayload.textContent = `Key is currently empty or not in memory: ${data.message || ''}`;
        }
    })
    .catch(err => {
        if (modalPayload) modalPayload.textContent = `Error inspecting key payload: ${err.message}`;
    });
}

/**
 * 9. Delete Single Key
 */
function deleteSingleKey(keyName, btn) {
    if (!confirm(`Are you sure you want to flush key "${keyName}" from memory?`)) {
        return;
    }

    let originalHtml = '';
    if (btn) {
        btn.disabled = true;
        originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i>';
    }

    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    fetch('/admin/cache/delete-key', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': token,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ key: keyName })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showCacheAlert('success', `Key "${keyName}" flushed successfully!`);
            // Update row badge
            const row = document.querySelector(`tr[data-key="${keyName}"]`);
            if (row) {
                const statusBadge = row.querySelector('.key-status-badge');
                if (statusBadge) {
                    statusBadge.className = 'badge bg-warning-subtle text-warning-emphasis key-status-badge rounded-pill px-2.5 py-1 font-monospace';
                    statusBadge.innerHTML = '<i class="fa-solid fa-hourglass-half me-1"></i> Empty';
                }
            }
        } else {
            showCacheAlert('danger', data.message || 'Key flush failed.');
        }
    })
    .catch(err => showCacheAlert('danger', 'Network request failed.'))
    .finally(() => {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        }
    });
}

/**
 * 10. Dynamic Floating Toast Alert Display
 */
function showCacheAlert(type, message) {
    const container = document.getElementById('dynamicCacheAlert');
    if (!container) return;

    const iconMap = {
        'success': 'fa-circle-check text-success',
        'info': 'fa-circle-info text-primary',
        'warning': 'fa-triangle-exclamation text-warning',
        'danger': 'fa-circle-exclamation text-danger'
    };

    const iconClass = iconMap[type] || 'fa-bell text-primary';

    container.innerHTML = `
        <div class="alert alert-${type} alert-dismissible fade show d-flex align-items-center mb-3 rounded-4 shadow-sm border-0 border-start border-4 border-${type} bg-white py-3 px-4" role="alert">
            <i class="fa-solid ${iconClass} fs-4 me-3"></i>
            <div class="fw-semibold text-dark">${message}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;

    setTimeout(() => {
        const el = container.querySelector('.alert');
        if (el) {
            el.classList.remove('show');
            setTimeout(() => el.remove(), 250);
        }
    }, 5000);
}
