/**
 * Admin SMS, Email & Unified Communication Hub JavaScript
 * Enterprise Grade Frontend Logic - Idea Prokashon
 */

document.addEventListener('DOMContentLoaded', function() {
    // 1. Tab Persistence across page reloads
    initTabPersistence();

    // 2. Initialize real-time character counters on all textareas
    initCharacterCounters();

    // 3. Initialize test dispatch handlers
    initAjaxDispatchers();
});

/**
 * Tab Persistence with localStorage
 */
function initTabPersistence() {
    const mainTabBtns = document.querySelectorAll('#messagingHubTabs button[data-bs-toggle="pill"]');
    const storedTab = localStorage.getItem('active_messaging_tab');

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
                localStorage.setItem('active_messaging_tab', target);
            }
        });
    });
}

/**
 * Real-time SMS Character, Part & Unicode Calculator
 */
function calculateSmsParts(textarea, counterId, progressId) {
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
    const counterEl = document.getElementById(counterId);
    if (counterEl) {
        const langBadge = isUnicode ? '<span class="text-primary fw-bold">Unicode (বাংলা)</span>' : '<span class="text-secondary">GSM English</span>';
        counterEl.innerHTML = `${len} ক্যারেক্টার &bull; <strong class="text-dark">${parts} SMS</strong> &bull; ${langBadge}`;
    }

    // Update Progress Bar
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

/**
 * Initialize Character Counters on all textareas
 */
function initCharacterCounters() {
    const testMsg = document.getElementById('testMessage');
    if (testMsg) {
        testMsg.addEventListener('input', () => calculateSmsParts(testMsg, 'testMsgCounter', 'testMsgProgress'));
        calculateSmsParts(testMsg, 'testMsgCounter', 'testMsgProgress');
    }

    const bulkMsg = document.getElementById('bulkMessage');
    if (bulkMsg) {
        bulkMsg.addEventListener('input', () => calculateSmsParts(bulkMsg, 'bulkMsgCounter', 'bulkMsgProgress'));
        calculateSmsParts(bulkMsg, 'bulkMsgCounter', 'bulkMsgProgress');
    }

    const manyMsg = document.getElementById('manyMessageTemplate');
    if (manyMsg) {
        manyMsg.addEventListener('input', () => calculateSmsParts(manyMsg, 'manyMsgCounter', 'manyMsgProgress'));
        calculateSmsParts(manyMsg, 'manyMsgCounter', 'manyMsgProgress');
    }
}

/**
 * Fast Template Inserter
 */
function applySmsTemplate(type, targetInputId, counterId, progressId) {
    const input = document.getElementById(targetInputId);
    if (!input) return;

    const templates = {
        'order_confirm': 'আইডিয়া প্রকাশন: আপনার অর্ডার #{order_id} নিশ্চিত হয়েছে। শীঘ্রই ডেলিভারি প্রক্রিয়া শুরু হবে। ধন্যবাদ!',
        'order_shipped': 'আইডিয়া প্রকাশন: আপনার বই কুরিয়ারে হস্তান্তর করা হয়েছে। ট্র্যাকিং কোড: {tracking_code}। সাথে থাকার জন্য ধন্যবাদ!',
        'book_grant': 'আইডিয়া প্রকাশন: অভিনন্দন! আপনার পাঠাগার বাৎসরিক বই বিতরণ কর্মসূচির আওতায় বই অনুদানের জন্য নির্বাচিত হয়েছে। বিস্তারিত জানতে ইনবক্স চেক করুন।',
        'otp_code': 'আইডিয়া প্রকাশন সিকিউরিটি ওটিপি: {otp}। কোডটি কারো সাথে শেয়ার করবেন না। মেয়াদ ৫ মিনিট।',
        'welcome': 'প্রিয় {name}, আইডিয়া প্রকাশনে আপনাকে স্বাগতম! সেরা সব বই ও অফার দেখতে ভিজিট করুন www.ideaabd.com',
        'discount': 'আইডিয়া প্রকাশন মেগা অফার! নির্বাচিত সকল বইয়ে পাচ্ছেন ২৫% পর্যন্ত বিশেষ ছাড়। অফার সীমিত সময়ের জন্য: www.ideaabd.com'
    };

    if (templates[type]) {
        input.value = templates[type];
        input.focus();
        calculateSmsParts(input, counterId, progressId);
    }
}

/**
 * Live SMS Balance Refresh with Ajax
 */
function refreshSmsBalance() {
    const icon = document.getElementById('refreshIcon');
    const display = document.getElementById('smsBalanceDisplay');
    const statusBadge = document.getElementById('smsBalanceStatus');
    const pillBadge = document.getElementById('pillSmsBadge');

    if (icon) icon.classList.add('fa-spin');

    fetch('/admin/sms/balance', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (icon) icon.classList.remove('fa-spin');
        if (data && data.balance !== null && data.balance !== undefined) {
            const formatted = new Intl.NumberFormat('en-US').format(data.balance);
            if (display) display.textContent = formatted;
            if (pillBadge) pillBadge.textContent = `${formatted} SMS`;
            if (statusBadge) {
                statusBadge.className = 'badge bg-success-subtle text-success border border-success-subtle rounded-pill ms-1';
                statusBadge.textContent = 'Active (সক্রিয়)';
            }
        } else {
            if (statusBadge) {
                statusBadge.className = 'badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill ms-1';
                statusBadge.textContent = 'Check Failed';
            }
        }
    })
    .catch(err => {
        if (icon) icon.classList.remove('fa-spin');
        console.error('Balance fetch error:', err);
    });
}

/**
 * Insert placeholder tag into Many-to-Many message template
 */
function insertPlaceholder(tag) {
    const textarea = document.getElementById('manyMessageTemplate');
    if (!textarea) return;

    const startPos = textarea.selectionStart;
    const endPos = textarea.selectionEnd;
    const val = textarea.value;

    textarea.value = val.substring(0, startPos) + tag + val.substring(endPos, val.length);
    textarea.focus();
    textarea.selectionStart = startPos + tag.length;
    textarea.selectionEnd = startPos + tag.length;

    calculateSmsParts(textarea, 'manyMsgCounter', 'manyMsgProgress');
}

/**
 * Copy Code Snippet Helper with Feedback
 */
function copySnippet(elementId, btn) {
    const el = document.getElementById(elementId);
    if (!el) return;

    const text = el.innerText || el.textContent;
    navigator.clipboard.writeText(text).then(() => {
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fa-solid fa-check text-success me-1"></i> Copied!';
        btn.classList.add('btn-success');
        btn.classList.remove('btn-dark');

        setTimeout(() => {
            btn.innerHTML = originalHtml;
            btn.classList.remove('btn-success');
            btn.classList.add('btn-dark');
        }, 2000);
    });
}

/**
 * Preset Gateway Configuration Loaders
 */
function loadGatewayPreset(preset) {
    const providerSelect = document.getElementById('smsProviderSelect');
    const urlInput = document.getElementById('smsGatewayUrl');
    const apiKeyInput = document.getElementById('smsApiKeyInput');
    const senderIdInput = document.getElementById('smsSenderIdInput');
    const badge = document.getElementById('activeProviderBadge');

    if (preset === 'bulksmsbd') {
        if (providerSelect) providerSelect.value = 'bulksmsbd';
        if (urlInput) urlInput.value = 'http://bulksmsbd.net/api/smsapi';
        if (apiKeyInput && (!apiKeyInput.value || apiKeyInput.value.includes('your_'))) apiKeyInput.value = 'NDZQOR8CI0fWSxqk1go8';
        if (senderIdInput && (!senderIdInput.value || senderIdInput.value.includes('your_'))) senderIdInput.value = '8809617634835';
        if (badge) badge.textContent = 'BULKSMSBD';
    } else if (preset === 'greenweb') {
        if (providerSelect) providerSelect.value = 'greenweb';
        if (urlInput) urlInput.value = 'http://api.greenweb.com.bd/api.php';
        if (badge) badge.textContent = 'GREENWEB';
    } else if (preset === 'alaapcloud') {
        if (providerSelect) providerSelect.value = 'alaapcloud';
        if (urlInput) urlInput.value = 'https://www.alaapcloud.gov.bd/api/sms/send';
        if (badge) badge.textContent = 'ALAAPCLOUD';
    } else if (preset === 'alphasms') {
        if (providerSelect) providerSelect.value = 'alphasms';
        if (urlInput) urlInput.value = 'https://api.sms.net.bd/sendsms';
        if (badge) badge.textContent = 'ALPHASMS';
    }
}

/**
 * Toggle Visibility of Custom Input Boxes
 */
function toggleCustomNumbersBox(val) {
    const container = document.getElementById('customNumbersContainer');
    if (container) container.classList.toggle('d-none', val !== 'custom');
}

function toggleManyCustomBox(val) {
    const container = document.getElementById('manyCustomBox');
    if (container) container.classList.toggle('d-none', val !== 'custom');
}

function toggleCustomEmailsBox(val) {
    const container = document.getElementById('customEmailsContainer');
    if (container) container.classList.toggle('d-none', val !== 'custom');
}

function toggleDualCustomBox(val) {
    const container = document.getElementById('dualCustomBox');
    if (container) container.classList.toggle('d-none', val !== 'custom');
}

function autoFillGatewayUrl(provider) {
    loadGatewayPreset(provider);
}

/**
 * Toggle Password / API Key visibility
 */
function togglePasswordVisibility(inputId, btn) {
    const input = document.getElementById(inputId);
    if (!input) return;
    const icon = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        if (icon) {
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        }
    } else {
        input.type = 'password';
        if (icon) {
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
}

/**
 * AJAX Dispatch Handlers for Diagnostics
 */
function initAjaxDispatchers() {
    // 1. Test SMS Dispatcher
    document.getElementById('formSendTestSms')?.addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = document.getElementById('btnSubmitTestSms');
        const spinner = document.getElementById('testSmsLoading');
        const resultBox = document.getElementById('testResultBox');
        const resultAlert = document.getElementById('testResultAlert');
        const resultIcon = document.getElementById('testResultIcon');
        const resultTitle = document.getElementById('testResultTitle');
        const resultDetails = document.getElementById('testResultDetails');

        if (btn) btn.disabled = true;
        if (spinner) spinner.classList.remove('d-none');
        if (resultBox) resultBox.classList.add('d-none');

        const formData = new FormData(this);

        fetch(this.action, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (btn) btn.disabled = false;
            if (spinner) spinner.classList.add('d-none');
            if (resultBox) resultBox.classList.remove('d-none');

            if (data.success) {
                resultAlert.className = 'sms-result-box sms-result-success';
                resultIcon.className = 'fa-solid fa-circle-check text-success fs-5';
                resultTitle.textContent = 'টেস্ট এসএমএস সফলভাবে পাঠানো হয়েছে!';
                resultDetails.textContent = `নম্বর: ${data.numbers} | রেসপন্স কোড: ${data.response_code || '202'} | গেটওয়ে বার্তা: ${data.message || 'Success'}`;
                refreshSmsBalance();
            } else {
                resultAlert.className = 'sms-result-box sms-result-error';
                resultIcon.className = 'fa-solid fa-circle-xmark text-danger fs-5';
                resultTitle.textContent = 'এসএমএস পাঠাতে ব্যর্থ হয়েছে!';
                resultDetails.textContent = `ত্রুটি: ${data.error || data.message || 'Unknown Error'} ${data.raw_response ? ' | Raw Response: ' + data.raw_response : ''}`;
            }
        })
        .catch(err => {
            if (btn) btn.disabled = false;
            if (spinner) spinner.classList.add('d-none');
            if (resultBox) resultBox.classList.remove('d-none');
            resultAlert.className = 'sms-result-box sms-result-error';
            resultIcon.className = 'fa-solid fa-triangle-exclamation text-danger fs-5';
            resultTitle.textContent = 'সার্ভার সংযোগ ত্রুটি!';
            resultDetails.textContent = err.message;
        });
    });

    // 2. Test Email Dispatcher
    document.getElementById('formSendTestEmail')?.addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = document.getElementById('btnSubmitTestEmail');
        const spinner = document.getElementById('testEmailLoading');
        const resultBox = document.getElementById('testEmailResultBox');
        const resultAlert = document.getElementById('testEmailResultAlert');
        const resultIcon = document.getElementById('testEmailResultIcon');
        const resultTitle = document.getElementById('testEmailResultTitle');
        const resultDetails = document.getElementById('testEmailResultDetails');

        if (btn) btn.disabled = true;
        if (spinner) spinner.classList.remove('d-none');
        if (resultBox) resultBox.classList.add('d-none');

        const formData = new FormData(this);

        fetch(this.action, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (btn) btn.disabled = false;
            if (spinner) spinner.classList.add('d-none');
            if (resultBox) resultBox.classList.remove('d-none');

            if (data.success) {
                resultAlert.className = 'sms-result-box sms-result-success';
                resultIcon.className = 'fa-solid fa-circle-check text-success fs-5';
                resultTitle.textContent = 'টেস্ট ইমেইল সফলভাবে পাঠানো হয়েছে!';
                resultDetails.textContent = `প্রাপক: ${data.recipient || ''} | স্ট্যাটাস: ${data.message || 'Delivered'}`;
            } else {
                resultAlert.className = 'sms-result-box sms-result-error';
                resultIcon.className = 'fa-solid fa-circle-xmark text-danger fs-5';
                resultTitle.textContent = 'ইমেইল পাঠাতে ব্যর্থ হয়েছে!';
                resultDetails.textContent = `ত্রুটি: ${data.message || 'Unknown Error'}`;
            }
        })
        .catch(err => {
            if (btn) btn.disabled = false;
            if (spinner) spinner.classList.add('d-none');
            if (resultBox) resultBox.classList.remove('d-none');
            resultAlert.className = 'sms-result-box sms-result-error';
            resultIcon.className = 'fa-solid fa-triangle-exclamation text-danger fs-5';
            resultTitle.textContent = 'সার্ভার সংযোগ ত্রুটি!';
            resultDetails.textContent = err.message;
        });
    });

    // 3. Unified OTP Dispatcher
    document.getElementById('formUnifiedOtp')?.addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = document.getElementById('btnSubmitUnifiedOtp');
        const spinner = document.getElementById('otpLoadingSpinner');
        const resultBox = document.getElementById('unifiedOtpResultBox');
        const resultAlert = document.getElementById('unifiedOtpAlert');
        const resultIcon = document.getElementById('unifiedOtpIcon');
        const resultTitle = document.getElementById('unifiedOtpTitle');
        const resultDetails = document.getElementById('unifiedOtpDetails');

        if (btn) btn.disabled = true;
        if (spinner) spinner.classList.remove('d-none');
        if (resultBox) resultBox.classList.add('d-none');

        const formData = new FormData(this);

        fetch(this.action, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (btn) btn.disabled = false;
            if (spinner) spinner.classList.add('d-none');
            if (resultBox) resultBox.classList.remove('d-none');

            if (data.success) {
                resultAlert.className = 'sms-result-box sms-result-success';
                resultIcon.className = 'fa-solid fa-circle-check text-success fs-5';
                resultTitle.textContent = `ওটিপি কোড (${data.otp}) সফলভাবে পাঠানো হয়েছে!`;
                
                const smsStatus = data.results?.sms ? (data.results.sms.success ? 'SMS: সফল ✅' : 'SMS: ব্যর্থ ❌') : '';
                const emailStatus = data.results?.email ? (data.results.email.success ? 'Email: সফল ✅' : 'Email: ব্যর্থ ❌') : '';
                resultDetails.textContent = [smsStatus, emailStatus].filter(Boolean).join(' | ');
                refreshSmsBalance();
            } else {
                resultAlert.className = 'sms-result-box sms-result-error';
                resultIcon.className = 'fa-solid fa-circle-xmark text-danger fs-5';
                resultTitle.textContent = 'ওটিপি পাঠাতে সমস্যা হয়েছে!';
                resultDetails.textContent = 'গেটওয়ে রেসপন্স বা ইমেইল ঠিকানা চেক করুন।';
            }
        })
        .catch(err => {
            if (btn) btn.disabled = false;
            if (spinner) spinner.classList.add('d-none');
            if (resultBox) resultBox.classList.remove('d-none');
            resultAlert.className = 'sms-result-box sms-result-error';
            resultIcon.className = 'fa-solid fa-triangle-exclamation text-danger fs-5';
            resultTitle.textContent = 'সার্ভার সংযোগ ত্রুটি!';
            resultDetails.textContent = err.message;
        });
    });
}
