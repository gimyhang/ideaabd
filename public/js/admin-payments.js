/**
 * Admin Payment Gateways & Transaction Management JavaScript
 * Enterprise Grade Frontend Logic - Idea Prokashon
 */

document.addEventListener('DOMContentLoaded', function() {
    // 1. Tab Persistence across page saves and reloads
    initTabPersistence();

    // 2. Initialize real-time search on transaction table
    initTrxLiveSearch();
});

/**
 * Tab Persistence with localStorage
 */
function initTabPersistence() {
    const tabBtns = document.querySelectorAll('#paymentTab button[data-bs-toggle="pill"]');
    const storedTab = localStorage.getItem('active_payment_tab');

    if (storedTab) {
        const targetBtn = document.querySelector(`#paymentTab button[data-bs-target="${storedTab}"]`);
        if (targetBtn && typeof bootstrap !== 'undefined' && bootstrap.Tab) {
            new bootstrap.Tab(targetBtn).show();
        }
    }

    tabBtns.forEach(btn => {
        btn.addEventListener('shown.bs.tab', function(e) {
            const target = e.target.getAttribute('data-bs-target');
            if (target) {
                localStorage.setItem('active_payment_tab', target);
            }
        });
    });
}

/**
 * Programmatic Tab Switcher
 */
function switchTab(tabBtnId) {
    const btn = document.getElementById(tabBtnId);
    if (btn && typeof bootstrap !== 'undefined' && bootstrap.Tab) {
        new bootstrap.Tab(btn).show();
    }
}

/**
 * Toggle Gateway Operation Mode (Manual / Automated PGW / Custom Code)
 */
function toggleGwMode(gw, mode) {
    const sections = ['manual', 'automated', 'custom_code'];
    
    sections.forEach(sec => {
        const el = document.getElementById(`${gw}_mode_${sec}`);
        if (el) {
            if (sec === mode) {
                el.classList.remove('d-none');
            } else {
                el.classList.add('d-none');
            }
        }
    });
}

/**
 * Live QR Code Image Preview
 */
function previewQr(input, previewId) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById(previewId);
            if (preview) {
                preview.src = e.target.result;
                preview.classList.remove('d-none');
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

/**
 * Copy to Clipboard Helper
 */
function copyTrx(text, btn) {
    if (!text) return;
    navigator.clipboard.writeText(text).then(() => {
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fa-solid fa-check text-success"></i>';
        setTimeout(() => {
            btn.innerHTML = originalHtml;
        }, 1800);
    });
}

/**
 * Live Table Filter
 */
function initTrxLiveSearch() {
    const searchInput = document.getElementById('trxLiveSearch');
    if (!searchInput) return;

    searchInput.addEventListener('input', function() {
        const query = this.value.toLowerCase().trim();
        const rows = document.querySelectorAll('.trx-row');

        rows.forEach(row => {
            const text = (row.getAttribute('data-search') || row.innerText).toLowerCase();
            if (!query || text.includes(query)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
}
