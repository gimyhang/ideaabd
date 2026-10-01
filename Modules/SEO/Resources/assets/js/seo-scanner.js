/**
 * Idea Prakashan - Live Interactive SEO Scanner & SERP Simulator Engine
 */

document.addEventListener('DOMContentLoaded', function () {
    // 1. Live Character Counter Bindings
    function bindCharCounter(inputId, counterId, minLen, maxLen) {
        const input = document.getElementById(inputId);
        const counter = document.getElementById(counterId);
        if (!input || !counter) return;

        function update() {
            const len = (input.value || '').length;
            counter.textContent = `${len} অক্ষর`;
            counter.className = 'char-counter-pill ';
            if (len >= minLen && len <= maxLen) {
                counter.className += 'char-good';
            } else if (len > maxLen || (len > 0 && len < minLen)) {
                counter.className += 'char-warn';
            } else {
                counter.className += 'char-bad';
            }
        }

        input.addEventListener('input', update);
        update();
    }

    bindCharCounter('modalMetaTitle', 'modalTitleCount', 30, 65);
    bindCharCounter('modalMetaDesc', 'modalDescCount', 120, 160);
    bindCharCounter('pageMetaTitle', 'pageTitleCount', 30, 65);
    bindCharCounter('pageMetaDesc', 'pageDescCount', 120, 160);

    // 2. Real-time Live SERP Simulator Sync
    function bindSerpSync(titleInputId, descInputId, serpTitleId, serpDescId) {
        const titleInput = document.getElementById(titleInputId);
        const descInput = document.getElementById(descInputId);
        const serpTitle = document.getElementById(serpTitleId);
        const serpDesc = document.getElementById(serpDescId);

        if (titleInput && serpTitle) {
            titleInput.addEventListener('input', () => {
                serpTitle.textContent = titleInput.value || 'পৃষ্ঠার মেটা টাইটেল...';
            });
        }
        if (descInput && serpDesc) {
            descInput.addEventListener('input', () => {
                serpDesc.textContent = descInput.value || 'গুগল সার্চে প্রদর্শিত সংক্ষিপ্ত মেটা বিবরণ...';
            });
        }
    }

    bindSerpSync('modalMetaTitle', 'modalMetaDesc', 'serpPreviewTitle', 'serpPreviewDesc');
    bindSerpSync('pageMetaTitle', 'pageMetaDesc', 'pageSerpTitle', 'pageSerpDesc');
});

/**
 * Execute AJAX Live Scan on specific item
 */
function runLiveSeoScan(type, id, urlPath, focusKeyword) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const scanBtn = document.getElementById('scanSubmitBtn');
    if (scanBtn) {
        scanBtn.disabled = true;
        scanBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> স্ক্যান ও অডিট চলছে...';
    }

    fetch('/admin/seo/scan-ajax', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            type: type,
            id: id,
            url_path: urlPath,
            focus_keyword: focusKeyword
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success && data.seo) {
            populateSeoModal(data.seo);
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'স্ক্যান সফল!',
                    text: data.message,
                    timer: 2500,
                    showConfirmButton: false
                });
            }
        } else {
            alert(data.message || 'স্ক্যান সম্পন্ন করা যায়নি।');
        }
    })
    .catch(err => {
        console.error('Scan Error:', err);
        alert('সার্ভার যোগাযোগে ত্রুটি হয়েছে।');
    })
    .finally(() => {
        if (scanBtn) {
            scanBtn.disabled = false;
            scanBtn.innerHTML = '<i class="fa-solid fa-bolt me-1"></i> স্ক্যান ও ট্যাগ তৈরি করুন';
        }
    });
}

/**
 * Populate Quick Edit / Audit Modal
 */
function populateSeoModal(seo) {
    const form = document.getElementById('editSeoForm');
    if (form) {
        form.action = `/admin/seo/update/${seo.id}`;
    }

    const setVal = (id, val) => {
        const el = document.getElementById(id);
        if (el) el.value = val || '';
    };

    setVal('modalMetaTitle', seo.meta_title);
    setVal('modalMetaDesc', seo.meta_description);
    setVal('modalKeywords', seo.meta_keywords);
    setVal('modalFocusKeyword', seo.focus_keyword);
    setVal('modalCanonical', seo.canonical_url);
    setVal('modalRobots', seo.robots || 'index, follow');
    setVal('modalOgImage', seo.og_image);

    // Update SERP Preview
    const serpTitle = document.getElementById('serpPreviewTitle');
    if (serpTitle) serpTitle.textContent = seo.meta_title || '';
    const serpDesc = document.getElementById('serpPreviewDesc');
    if (serpDesc) serpDesc.textContent = seo.meta_description || '';
    const serpUrl = document.getElementById('serpPreviewUrl');
    if (serpUrl) serpUrl.textContent = seo.canonical_url || 'https://www.ideaabd.com';

    // Update Score Circle & Diagnostics
    const scoreVal = document.getElementById('modalScoreValue');
    const scoreCircle = document.getElementById('modalScoreCircle');
    if (scoreVal && scoreCircle) {
        const score = parseInt(seo.seo_score || 0);
        scoreVal.textContent = score;
        scoreCircle.className = 'seo-score-circle ' + (score >= 80 ? 'seo-score-good' : (score >= 50 ? 'seo-score-fair' : 'seo-score-poor'));
    }

    // Diagnostics list
    const diagList = document.getElementById('modalAuditList');
    if (diagList && seo.seo_analysis) {
        let html = '';
        if (seo.seo_analysis.passed && seo.seo_analysis.passed.length) {
            seo.seo_analysis.passed.forEach(p => {
                html += `<li class="text-success small mb-1"><i class="fa-solid fa-circle-check me-1.5"></i>${p}</li>`;
            });
        }
        if (seo.seo_analysis.warnings && seo.seo_analysis.warnings.length) {
            seo.seo_analysis.warnings.forEach(w => {
                html += `<li class="text-warning small mb-1"><i class="fa-solid fa-triangle-exclamation me-1.5"></i>${w}</li>`;
            });
        }
        if (seo.seo_analysis.critical && seo.seo_analysis.critical.length) {
            seo.seo_analysis.critical.forEach(c => {
                html += `<li class="text-danger small mb-1"><i class="fa-solid fa-circle-xmark me-1.5"></i>${c}</li>`;
            });
        }
        diagList.innerHTML = html;
    }

    // Open Bootstrap Modal
    const modalEl = document.getElementById('seoEditModal');
    if (modalEl && typeof bootstrap !== 'undefined') {
        const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
        modal.show();
    }
}
