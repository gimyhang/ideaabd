/**
 * Admin SMS, Email & Unified Communication Hub JavaScript
 * Enterprise Grade Frontend Logic - Idea Prokashon
 */

// Global Recipient Counts passed from Blade
window.messagingCounts = window.messagingCounts || {
    total_users: 0,
    customers: 0,
    authors: 0,
    publishers: 0,
    sellers: 0,
    libraries: 0
};

// Global Templates Cache
window.savedTemplates = window.savedTemplates || [];

document.addEventListener('DOMContentLoaded', function() {
    // 1. Tab Persistence across page reloads
    initTabPersistence();

    // 2. Initialize real-time character counters & simulator on all textareas
    initCharacterCounters();

    // 3. Initialize test dispatch handlers
    initAjaxDispatchers();

    // 4. Initialize Live Cost Estimators
    initCostEstimators();

    // 5. Initialize Campaign Logs Search Filter
    initLogsFilter();
});

/**
 * Tab Persistence with localStorage
 */
function initTabPersistence() {
    const mainTabBtns = document.querySelectorAll('#messagingHubTabs button[data-bs-toggle="pill"]');
    // a #tab-xyz link (pagination, filters) wins over the remembered tab
    const hashTab = window.location.hash && document.querySelector(`#messagingHubTabs button[data-bs-target="${window.location.hash}"]`)
        ? window.location.hash : null;
    let storedTab = hashTab;
    if (!storedTab) {
        try { storedTab = localStorage.getItem('active_messaging_hub_tab'); } catch (e) { storedTab = null; }
    }

    if (storedTab) {
        const targetBtn = document.querySelector(`#messagingHubTabs button[data-bs-target="${storedTab}"]`);
        if (targetBtn && typeof bootstrap !== 'undefined' && bootstrap.Tab) {
            new bootstrap.Tab(targetBtn).show();
        }
    }

    mainTabBtns.forEach(btn => {
        btn.addEventListener('shown.bs.tab', function(e) {
            const target = e.target.getAttribute('data-bs-target');
            if (target) {
                try { localStorage.setItem('active_messaging_hub_tab', target); } catch (err) { /* private mode */ }
            }
        });
    });
}

/**
 * Switch Tab Programmatically
 */
function switchMessagingTab(tabBtnId) {
    const btn = document.getElementById(tabBtnId);
    if (btn && typeof bootstrap !== 'undefined' && bootstrap.Tab) {
        new bootstrap.Tab(btn).show();
        btn.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

/**
 * Real-time SMS Character, Part & Unicode Calculator
 */
function calculateSmsParts(textarea, counterId, progressId, previewPhoneId, costBadgeId, targetGroupSelectId) {
    if (!textarea) return;
    const text = textarea.value || '';
    const len = text.length;
    let isUnicode = false;

    // Check for Unicode / Bengali / Non-ASCII characters
    for (let i = 0; i < len; i++) {
        if (text.charCodeAt(i) > 127) {
            isUnicode = true;
            break;
        }
    }

    let parts = 1;
    let maxPartLen = isUnicode ? 70 : 160;
    let multiPartLen = isUnicode ? 67 : 153;

    if (len === 0) {
        parts = 1;
    } else if (len <= maxPartLen) {
        parts = 1;
    } else {
        parts = Math.ceil(len / multiPartLen);
    }

    // Update Counter Text
    if (counterId) {
        const counterEl = document.getElementById(counterId);
        if (counterEl) {
            const langBadge = isUnicode 
                ? '<span class="badge bg-primary-subtle text-primary fw-bold px-2">Unicode (বাংলা)</span>' 
                : '<span class="badge bg-secondary-subtle text-secondary px-2">GSM English</span>';
            counterEl.innerHTML = `${len} ক্যারেক্টার &bull; <strong class="text-dark">${parts} SMS পার্ট</strong> &bull; ${langBadge}`;
        }
    }

    // Update Progress Bar
    if (progressId) {
        const progressEl = document.getElementById(progressId);
        if (progressEl) {
            let currentPartLimit = parts === 1 ? maxPartLen : multiPartLen;
            let remainder = len % currentPartLimit;
            if (remainder === 0 && len > 0) remainder = currentPartLimit;
            let pct = Math.min(100, Math.round((remainder / currentPartLimit) * 100));
            
            progressEl.style.width = pct + '%';
            progressEl.className = 'sms-counter-progress';
            if (parts > 2) {
                progressEl.classList.add('danger');
            } else if (parts > 1) {
                progressEl.classList.add('warning');
            }
        }
    }

    // Update Live Smartphone Preview Bubble
    if (previewPhoneId) {
        const phoneBubble = document.getElementById(previewPhoneId);
        if (phoneBubble) {
            phoneBubble.innerText = text.trim() || 'এখানে মেসেজ টাইপ করলে লাইভ প্রিভিউ দেখতে পাবেন...';
        }
    }

    // Update Dynamic Cost Estimator if requested
    if (costBadgeId && targetGroupSelectId) {
        updateCostEstimate(costBadgeId, targetGroupSelectId, parts);
    }
}

/**
 * Initialize Character Counters & Live Preview on all textareas
 */
function initCharacterCounters() {
    // 1. Quick Test SMS
    const testMsg = document.getElementById('testMessage');
    if (testMsg) {
        testMsg.addEventListener('input', () => calculateSmsParts(testMsg, 'testMsgCounter', 'testMsgProgress', 'phoneSimPreviewQuick'));
        calculateSmsParts(testMsg, 'testMsgCounter', 'testMsgProgress', 'phoneSimPreviewQuick');
    }

    // 2. Simple Bulk SMS
    const bulkMsg = document.getElementById('bulkMessage');
    if (bulkMsg) {
        bulkMsg.addEventListener('input', () => calculateSmsParts(bulkMsg, 'bulkMsgCounter', 'bulkMsgProgress', 'phoneSimPreviewBulk', 'bulkCostSummary', 'bulkTargetGroup'));
        calculateSmsParts(bulkMsg, 'bulkMsgCounter', 'bulkMsgProgress', 'phoneSimPreviewBulk', 'bulkCostSummary', 'bulkTargetGroup');
    }

    // 3. Many-to-Many Template
    const manyTpl = document.getElementById('manyTemplate');
    if (manyTpl) {
        manyTpl.addEventListener('input', () => calculateSmsParts(manyTpl, 'manyMsgCounter', 'manyMsgProgress', 'phoneSimPreviewMany', 'manyCostSummary', 'manyTargetGroup'));
        calculateSmsParts(manyTpl, 'manyMsgCounter', 'manyMsgProgress', 'phoneSimPreviewMany', 'manyCostSummary', 'manyTargetGroup');
    }

    // 4. Dual Marketing Message
    const dualMsg = document.getElementById('dualMessage');
    if (dualMsg) {
        dualMsg.addEventListener('input', () => calculateSmsParts(dualMsg, 'dualMsgCounter', 'dualMsgProgress', 'phoneSimPreviewDual'));
        calculateSmsParts(dualMsg, 'dualMsgCounter', 'dualMsgProgress', 'phoneSimPreviewDual');
    }
}

/**
 * Initialize Cost Estimator triggers on group changes
 */
function initCostEstimators() {
    const bulkGroup = document.getElementById('bulkTargetGroup');
    if (bulkGroup) {
        bulkGroup.addEventListener('change', () => {
            const bulkMsg = document.getElementById('bulkMessage');
            if (bulkMsg) calculateSmsParts(bulkMsg, 'bulkMsgCounter', 'bulkMsgProgress', 'phoneSimPreviewBulk', 'bulkCostSummary', 'bulkTargetGroup');
        });
    }

    const manyGroup = document.getElementById('manyTargetGroup');
    if (manyGroup) {
        manyGroup.addEventListener('change', () => {
            const manyTpl = document.getElementById('manyTemplate');
            if (manyTpl) calculateSmsParts(manyTpl, 'manyMsgCounter', 'manyMsgProgress', 'phoneSimPreviewMany', 'manyCostSummary', 'manyTargetGroup');
        });
    }
}

/**
 * Update Cost Estimate Badge & Balance Warning
 */
function updateCostEstimate(costBadgeId, targetGroupSelectId, partsCount) {
    const badgeEl = document.getElementById(costBadgeId);
    const selectEl = document.getElementById(targetGroupSelectId);
    if (!badgeEl || !selectEl) return;

    const group = selectEl.value;
    let recipientCount = 0;

    if (group === 'all') recipientCount = window.messagingCounts.total_users || 0;
    else if (group === 'customers') recipientCount = window.messagingCounts.customers || 0;
    else if (group === 'authors') recipientCount = window.messagingCounts.authors || 0;
    else if (group === 'publishers') recipientCount = window.messagingCounts.publishers || 0;
    else if (group === 'sellers') recipientCount = window.messagingCounts.sellers || 0;
    else if (group === 'libraries') recipientCount = window.messagingCounts.libraries || 0;
    else recipientCount = 1; // Custom

    const totalSmsCredits = recipientCount * partsCount;
    const estCostBdt = (totalSmsCredits * (window.smsCostPerSms || 0.35)).toFixed(2);

    badgeEl.innerHTML = `
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <span><i class="fa-solid fa-users me-1 text-primary"></i> প্রাপক: <strong>${recipientCount.toLocaleString()}</strong> জন</span>
            <span><i class="fa-solid fa-comment-sms me-1 text-success"></i> মোট ক্রেডিট: <strong>${totalSmsCredits.toLocaleString()} SMS</strong></span>
            <span><i class="fa-solid fa-bangladeshi-taka-sign me-1 text-warning"></i> আনুমানিক খরচ: <strong>৳${estCostBdt}</strong></span>
        </div>
    `;
}

/**
 * Insert Shortcode Chip into active Textarea
 */
function insertShortcode(shortcode, textareaId) {
    const textarea = document.getElementById(textareaId);
    if (!textarea) return;

    const start = textarea.selectionStart || 0;
    const end = textarea.selectionEnd || 0;
    const text = textarea.value;

    textarea.value = text.substring(0, start) + shortcode + text.substring(end);
    textarea.focus();
    textarea.selectionStart = textarea.selectionEnd = start + shortcode.length;

    // Trigger input event to re-calculate parts & simulator
    textarea.dispatchEvent(new Event('input'));
}

/**
 * Apply a saved template into a form
 */
function applyTemplate(templateId, targetType) {
    const tpl = window.savedTemplates.find(t => t.id === templateId);
    if (!tpl) return;

    if (targetType === 'bulk_sms') {
        const textarea = document.getElementById('bulkMessage');
        if (textarea) {
            textarea.value = tpl.body;
            textarea.dispatchEvent(new Event('input'));
            switchMessagingTab('tab-sms-btn');
        }
    } else if (targetType === 'many_sms') {
        const textarea = document.getElementById('manyTemplate');
        if (textarea) {
            textarea.value = tpl.body;
            textarea.dispatchEvent(new Event('input'));
            switchMessagingTab('tab-sms-btn');
        }
    } else if (targetType === 'email') {
        const subj = document.getElementById('bulkEmailSubject');
        const body = document.getElementById('bulkEmailBody');
        const actText = document.getElementById('bulkEmailActionText');
        const actUrl = document.getElementById('bulkEmailActionUrl');

        if (subj && tpl.subject) subj.value = tpl.subject;
        if (body) body.value = tpl.body;
        if (actText && tpl.action_text) actText.value = tpl.action_text;
        if (actUrl && tpl.action_url) actUrl.value = tpl.action_url;

        switchMessagingTab('tab-email-btn');
    } else if (targetType === 'dual') {
        const subj = document.getElementById('dualSubject');
        const body = document.getElementById('dualMessage');
        const actText = document.getElementById('dualActionText');
        const actUrl = document.getElementById('dualActionUrl');

        if (subj && tpl.subject) subj.value = tpl.subject;
        if (body) {
            body.value = tpl.body;
            body.dispatchEvent(new Event('input'));
        }
        if (actText && tpl.action_text) actText.value = tpl.action_text;
        if (actUrl && tpl.action_url) actUrl.value = tpl.action_url;

        switchMessagingTab('tab-unified-btn');
    }
}

/**
 * Edit Template Modal Filler
 */
function editTemplateModal(templateJson) {
    try {
        const tpl = typeof templateJson === 'string' ? JSON.parse(templateJson) : templateJson;
        document.getElementById('templateId').value = tpl.id || '';
        document.getElementById('templateTitle').value = tpl.title || '';
        document.getElementById('templateChannel').value = tpl.channel || 'sms';
        document.getElementById('templateTag').value = tpl.tag || 'General';
        document.getElementById('templateSubject').value = tpl.subject || '';
        document.getElementById('templateBody').value = tpl.body || '';
        document.getElementById('templateActionText').value = tpl.action_text || '';
        document.getElementById('templateActionUrl').value = tpl.action_url || '';

        const modalEl = document.getElementById('templateFormModal');
        if (modalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    } catch (e) {
        console.error('Error opening template modal:', e);
    }
}

/**
 * Reset Template Modal for New Entry
 */
function openNewTemplateModal() {
    document.getElementById('templateId').value = '';
    document.getElementById('templateTitle').value = '';
    document.getElementById('templateChannel').value = 'both';
    document.getElementById('templateTag').value = 'General';
    document.getElementById('templateSubject').value = '';
    document.getElementById('templateBody').value = '';
    document.getElementById('templateActionText').value = '';
    document.getElementById('templateActionUrl').value = '';

    const modalEl = document.getElementById('templateFormModal');
    if (modalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }
}

/**
 * Filter & Search Campaign History Table
 */
function initLogsFilter() {
    const searchInput = document.getElementById('campaignLogSearch');
    const channelSelect = document.getElementById('campaignLogChannel');
    const tableBody = document.getElementById('campaignLogsTableBody');

    if (!searchInput || !tableBody) return;

    function applyFilter() {
        const term = (searchInput.value || '').toLowerCase().trim();
        const selectedChannel = channelSelect ? channelSelect.value.toLowerCase() : 'all';
        const rows = tableBody.querySelectorAll('tr.log-row');

        rows.forEach(row => {
            const text = row.innerText.toLowerCase();
            const rowChannel = (row.getAttribute('data-channel') || '').toLowerCase();

            const matchesTerm = !term || text.includes(term);
            const matchesChannel = selectedChannel === 'all' || rowChannel === selectedChannel;

            if (matchesTerm && matchesChannel) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    searchInput.addEventListener('input', applyFilter);
    if (channelSelect) channelSelect.addEventListener('change', applyFilter);
}

/**
 * View Log Details Modal
 */
function viewLogDetails(logJson) {
    try {
        const log = typeof logJson === 'string' ? JSON.parse(logJson) : logJson;
        document.getElementById('logDetailId').innerText = log.id || '';
        document.getElementById('logDetailDate').innerText = log.created_at || '';
        document.getElementById('logDetailChannel').innerText = (log.channel || 'SMS').toUpperCase();
        document.getElementById('logDetailTarget').innerText = (log.target_group || '').toUpperCase();
        document.getElementById('logDetailStats').innerText = `মোট: ${log.total || 0} | সফল: ${log.sent || 0} | ব্যর্থ: ${log.failed || 0}`;
        document.getElementById('logDetailAdmin').innerText = log.admin_name || 'Admin';
        document.getElementById('logDetailTitle').innerText = log.title || '';
        document.getElementById('logDetailPreview').innerText = log.preview || '';

        const modalEl = document.getElementById('logDetailsModal');
        if (modalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    } catch (e) {
        console.error('Error opening log details modal:', e);
    }
}

/** Server and gateway texts go into the page as text, never as HTML. */
function escapeHtml(value) {
    const div = document.createElement('div');
    div.textContent = value == null ? '' : String(value);
    return div.innerHTML;
}

/**
 * Refresh Live Gateway SMS Balance via AJAX
 */
function refreshSmsBalance(reload) {
    const icons = document.querySelectorAll('#refreshIcon, #refreshIconCard, #refreshIconHealth');
    icons.forEach(i => i.classList.add('fa-spin'));

    fetch('/admin/sms/balance?fresh=1', {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        icons.forEach(i => i.classList.remove('fa-spin'));
        if (reload === true) { location.reload(); return; } // the health banner is built on the server
        const ok = data.balance !== null && data.balance !== undefined;

        document.querySelectorAll('[data-sms-balance]').forEach(el => {
            el.textContent = ok ? Number(data.balance).toLocaleString() : '—';
        });
        document.querySelectorAll('[data-sms-balance-status]').forEach(el => {
            el.innerHTML = ok
                ? (Number(data.balance) < 100
                    ? '<i class="fa-solid fa-triangle-exclamation text-warning me-1"></i> ব্যালান্স কম, শীঘ্র রিচার্জ করুন'
                    : '<i class="fa-solid fa-circle-check text-success me-1"></i> সক্রিয়')
                : `<i class="fa-solid fa-triangle-exclamation text-danger me-1"></i> ${escapeHtml(data.error || 'অপ্রাপ্য')}`;
        });
    })
    .catch(err => {
        icons.forEach(i => i.classList.remove('fa-spin'));
        console.error('Balance refresh error:', err);
    });
}

/**
 * Initialize AJAX Dispatchers for Test Single Actions
 */
function initAjaxDispatchers() {
    // Test SMS AJAX Form
    const testSmsForm = document.getElementById('quickTestSmsForm');
    if (testSmsForm) {
        testSmsForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = testSmsForm.querySelector('button[type="submit"]');
            const resultBox = document.getElementById('testSmsResult');
            const originalHtml = btn ? btn.innerHTML : '';

            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin me-1.5"></i> পাঠানো হচ্ছে...';
            }
            if (resultBox) resultBox.classList.add('d-none');

            const formData = new FormData(testSmsForm);

            fetch(testSmsForm.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(r => r.json())
            .then(res => {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                }
                if (resultBox) {
                    resultBox.classList.remove('d-none', 'sms-result-success', 'sms-result-error');
                    if (res.success) {
                        resultBox.classList.add('sms-result-success');
                        resultBox.innerHTML = `<strong><i class="fa-solid fa-circle-check me-1.5"></i> টেস্ট এসএমএস সফল!</strong><div class="small mt-1">${escapeHtml(res.message || 'গেটওয়ে রেসপন্স সম্পন্ন')}</div>`;
                        refreshSmsBalance();
                    } else {
                        resultBox.classList.add('sms-result-error');
                        resultBox.innerHTML = `<strong><i class="fa-solid fa-triangle-exclamation me-1.5"></i> সমস্যা:</strong><div class="small mt-1">${escapeHtml(res.message || res.error || 'অজানা সমস্যা')}</div>`;
                    }
                }
            })
            .catch(err => {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                }
                if (resultBox) {
                    resultBox.classList.remove('d-none', 'sms-result-error');
                    resultBox.innerHTML = '<strong><i class="fa-solid fa-circle-xmark me-1.5"></i> নেটওয়ার্ক রিকোয়েস্ট ব্যর্থ হয়েছে!</strong>';
                }
            });
        });
    }

    // Test Email AJAX Form
    const testEmailForm = document.getElementById('testEmailForm');
    if (testEmailForm) {
        testEmailForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = testEmailForm.querySelector('button[type="submit"]');
            const resultBox = document.getElementById('testEmailResult');
            const originalHtml = btn ? btn.innerHTML : '';

            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin me-1.5"></i> সেন্ড হচ্ছে...';
            }
            if (resultBox) resultBox.classList.add('d-none');

            const formData = new FormData(testEmailForm);

            fetch(testEmailForm.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(r => r.json())
            .then(res => {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                }
                if (resultBox) {
                    resultBox.classList.remove('d-none', 'sms-result-success', 'sms-result-error');
                    if (res.success) {
                        resultBox.classList.add('sms-result-success');
                        resultBox.innerHTML = `<strong><i class="fa-solid fa-circle-check me-1.5"></i> ইমেইল পাঠানো সফল!</strong><div class="small mt-1">${escapeHtml(res.message || 'সার্ভার রেসপন্স সম্পন্ন')}</div>`;
                    } else {
                        resultBox.classList.add('sms-result-error');
                        resultBox.innerHTML = `<strong><i class="fa-solid fa-triangle-exclamation me-1.5"></i> সমস্যা:</strong><div class="small mt-1">${escapeHtml(res.message || 'ইমেইল সেন্ড ব্যর্থ')}</div>`;
                    }
                }
            })
            .catch(err => {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                }
                if (resultBox) {
                    resultBox.classList.remove('d-none', 'sms-result-error');
                    resultBox.innerHTML = '<strong><i class="fa-solid fa-circle-xmark me-1.5"></i> রিকোয়েস্ট ব্যর্থ!</strong>';
                }
            });
        });
    }

    // Test OTP AJAX Form
    const testOtpForm = document.getElementById('testOtpForm');
    if (testOtpForm) {
        testOtpForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = testOtpForm.querySelector('button[type="submit"]');
            const resultBox = document.getElementById('testOtpResult');
            const originalHtml = btn ? btn.innerHTML : '';

            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<i class="fa-solid fa-bolt-lightning fa-bounce me-1.5"></i> ওটিপি তৈরি ও পাঠানো হচ্ছে...';
            }
            if (resultBox) resultBox.classList.add('d-none');

            const formData = new FormData(testOtpForm);

            fetch(testOtpForm.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(r => r.json())
            .then(res => {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                }
                if (resultBox) {
                    resultBox.classList.remove('d-none', 'sms-result-success', 'sms-result-error');
                    if (res.success) {
                        resultBox.classList.add('sms-result-success');
                        resultBox.innerHTML = `<strong><i class="fa-solid fa-circle-check me-1.5"></i> ওটিপি টেস্ট সফল! [OTP: ${escapeHtml(res.otp)}]</strong><div class="small mt-1">চ্যানেল রেসপন্স সম্পন্ন হয়েছে।</div>`;
                    } else {
                        resultBox.classList.add('sms-result-error');
                        resultBox.innerHTML = `<strong><i class="fa-solid fa-triangle-exclamation me-1.5"></i> ওটিপি পাঠানো ব্যর্থ:</strong><div class="small mt-1">${escapeHtml(res.message || 'গেটওয়ে সেটিংস চেক করুন।')}</div>`;
                    }
                }
            })
            .catch(err => {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                }
                if (resultBox) {
                    resultBox.classList.remove('d-none', 'sms-result-error');
                    resultBox.innerHTML = '<strong><i class="fa-solid fa-circle-xmark me-1.5"></i> ওটিপি রিকোয়েস্ট ফেইল করেছে!</strong>';
                }
            });
        });
    }
}

/* ---------- Bulk SMS campaigns: live progress, pause / resume / cancel ---------- */
(function () {
    const box = document.getElementById('smsCampaigns');
    if (!box) return;
    const list = box.querySelector('[data-campaign-list]');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const n = (v) => Number(v || 0).toLocaleString('bn-BD');
    let timer = null;
    let busy = false;

    function render(items) {
        box.hidden = !items.length;
        list.innerHTML = items.map((c) => {
            const btns = [];
            if (c.status === 'running') btns.push(`<button type="button" class="btn btn-outline-secondary btn-sm rounded-pill py-0" data-act="pause" data-id="${c.id}">Pause</button>`);
            if (c.status === 'paused') btns.push(`<button type="button" class="btn btn-primary btn-sm rounded-pill py-0" data-act="resume" data-id="${c.id}">Resume</button>`);
            if (c.status === 'running' || c.status === 'paused') btns.push(`<button type="button" class="btn btn-outline-danger btn-sm rounded-pill py-0" data-act="cancel" data-id="${c.id}">Cancel</button>`);
            const left = Math.max(0, c.total - c.position);
            const note = c.last_error
                ? `<div class="sms-camp-note"><i class="fa-solid fa-triangle-exclamation me-1"></i>${escapeHtml(c.last_error)}</div>`
                : (c.stalled ? '<div class="sms-camp-note">কিছুক্ষণ থেমে আছে — এই পেজ খোলা থাকলে নিজে থেকেই আবার চলবে।</div>' : '');
            return `<div class="sms-camp">
                <div class="sms-camp-top">
                    <span class="sms-camp-title">#${c.id} · ${escapeHtml(c.title)}</span>
                    <span class="sms-camp-badge ${c.status}">${escapeHtml(c.status_bn)}</span>
                    <small class="text-muted">${escapeHtml(c.started_at || '')}${c.segments > 1 ? ' · প্রতি মেসেজ ' + n(c.segments) + ' SMS' : ''}</small>
                    <span class="sms-camp-actions">${btns.join('')}</span>
                </div>
                <div class="sms-camp-bar" role="progressbar" aria-valuenow="${c.percent}" aria-valuemin="0" aria-valuemax="100"><span style="width:${c.percent}%"></span></div>
                <div class="sms-camp-nums">
                    <span>${n(c.percent)}% · ${n(c.position)}/${n(c.total)}</span>
                    <span>সফল <b>${n(c.sent)}</b></span>
                    <span>ব্যর্থ <b>${n(c.failed)}</b></span>
                    <span>ভুয়া/অবৈধ বাদ <b>${n(c.refused)}</b></span>
                    ${c.unknown ? `<span>অনিশ্চিত <b>${n(c.unknown)}</b></span>` : ''}
                    ${left && c.status !== 'done' ? `<span>বাকি <b>${n(left)}</b></span>` : ''}
                </div>${note}
            </div>`;
        }).join('');
        schedule(items);
    }

    function schedule(items) {
        clearTimeout(timer);
        // While a campaign runs, every poll also sends the next slice: an open page keeps it moving.
        if (items.some((c) => c.status === 'running')) timer = setTimeout(() => tick(true), 3000);
        else if (items.some((c) => c.status === 'paused')) timer = setTimeout(() => tick(false), 30000);
    }

    async function post(url, body) {
        const res = await fetch(url, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' },
            body: JSON.stringify(body || {}),
            credentials: 'same-origin',
        });
        if (!res.ok) throw new Error('HTTP ' + res.status);
        return res.json();
    }

    async function tick(work) {
        if (busy) return;
        busy = true;
        try {
            const data = await post(box.dataset.tickUrl, { work: work ? 1 : 0 });
            render(data.campaigns || []);
        } catch (e) {
            clearTimeout(timer);
            timer = setTimeout(() => tick(work), 10000); // network hiccup or a slow slice: try again later
        } finally {
            busy = false;
        }
    }

    list.addEventListener('click', async (e) => {
        const b = e.target.closest('[data-act]');
        if (!b) return;
        if (b.dataset.act === 'cancel' && !confirm('ক্যাম্পেইন বাতিল করবেন? যাদের কাছে এখনও যায়নি তারা আর পাবেন না।')) return;
        b.disabled = true;
        try {
            await post(box.dataset.actionUrl.replace('__ID__', b.dataset.id), { action: b.dataset.act });
        } catch (err) {
            alert('কাজটি করা যায়নি, আবার চেষ্টা করুন।');
        }
        busy = false;
        tick(b.dataset.act === 'resume');
    });

    const initial = window.smsCampaigns || [];
    render(initial);
    if (initial.some((c) => c.status === 'running')) tick(true);

    // A double click on "শুরু করুন" used to start the same campaign twice (and spend the balance twice).
    document.querySelectorAll('form[action$="/sms/broadcast"], form[action$="/sms/broadcast-many"]').forEach((form) => {
        form.addEventListener('submit', (e) => {
            if (e.defaultPrevented) return;
            if (form.dataset.sending) { e.preventDefault(); return; }
            form.dataset.sending = '1';
            form.querySelectorAll('button[type="submit"]').forEach((btn) => {
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> শুরু হচ্ছে…';
            });
        });
    });
})();

/* Gateway settings: try the key before saving, show / hide it, jump to settings from the health banner */
(function () {
    const verify = document.getElementById('smsVerifyKey');
    const out = document.getElementById('smsVerifyOut');
    const input = document.getElementById('smsApiKeyInput');
    if (verify && out) {
        verify.addEventListener('click', function () {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
            verify.disabled = true;
            out.className = 'sms-verify-out small mt-2 text-muted';
            out.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> গেটওয়েতে যাচাই হচ্ছে…';
            const body = new FormData();
            body.append('api_key', input ? input.value.trim() : '');
            fetch(verify.dataset.url, { method: 'POST', body, headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
                .then(r => r.json())
                .then(d => {
                    out.className = 'sms-verify-out small mt-2 ' + (d.ok ? 'is-ok' : (d.ok === false ? 'is-bad' : 'is-warn'));
                    out.innerHTML = '<i class="fa-solid ' + (d.ok ? 'fa-circle-check' : 'fa-circle-xmark') + ' me-1"></i>' + escapeHtml(d.message || '') + (input && !input.value.trim() ? ' <span class="text-muted">(সংরক্ষিত key)</span>' : '');
                })
                .catch(() => { out.className = 'sms-verify-out small mt-2 is-warn'; out.textContent = 'যাচাই করা যায়নি — আবার চেষ্টা করুন।'; })
                .finally(() => { verify.disabled = false; });
        });
    }
    document.querySelectorAll('[data-sms-toggle-key]').forEach(btn => btn.addEventListener('click', function () {
        if (!input) return;
        input.type = input.type === 'password' ? 'text' : 'password';
        btn.innerHTML = input.type === 'password' ? '<i class="fa-regular fa-eye"></i>' : '<i class="fa-regular fa-eye-slash"></i>';
    }));
    document.querySelectorAll('[data-sms-goto-settings]').forEach(btn => btn.addEventListener('click', function () {
        const main = document.getElementById('tab-sms-btn');
        const sub = document.getElementById('pills-sms-settings-tab');
        if (main && window.bootstrap) bootstrap.Tab.getOrCreateInstance(main).show();
        if (sub && window.bootstrap) bootstrap.Tab.getOrCreateInstance(sub).show();
        setTimeout(() => { if (input) { input.scrollIntoView({ behavior: 'smooth', block: 'center' }); input.focus(); } }, 250);
    }));
})();