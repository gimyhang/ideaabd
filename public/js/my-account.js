/**
 * ══════════════════════════════════════════════════════════════════════════
 * IDEA PROKASHON — USER ACCOUNT DASHBOARD DYNAMIC CONTROLLER
 * ══════════════════════════════════════════════════════════════════════════
 */

const SECTION_TITLES = {
    'hub': { title: 'অ্যাকাউন্ট ড্যাশবোর্ড' },
    'orders': { title: 'অর্ডার ও ট্র্যাকিং' },
    'digitalServices': { title: 'ই-বুক ও লাইব্রেরি' },
    'wishlist': { title: 'পছন্দের তালিকা' },
    'addresses': { title: 'ডেলিভারি ঠিকানা' },
    'loginSecurity': { title: 'লগইন ও নিরাপত্তা' },
    'kyc': { title: 'কেওয়াইসি ও ভেরিফিকেশন' },
    'payments': { title: 'ওয়ালেট ও রয়্যালটি' },
    'libraryGrant': { title: 'পাঠাগার ও বই অনুদান' },
    'customerService': { title: 'গ্রাহক সেবা ও সহায়তা' }
};

function syncNavDropdown(panelKey) {
    const key = panelKey || 'hub';
    const info = SECTION_TITLES[key] || { title: 'অ্যাকাউন্ট ড্যাশবোর্ড' };

    const labelEl = document.getElementById('currentAccountNavLabel');
    if (labelEl) labelEl.textContent = info.title;

    document.querySelectorAll('.amz-nav-menu .dropdown-item[data-panel]').forEach(function (item) {
        if (item.getAttribute('data-panel') === key) {
            item.classList.add('active');
        } else {
            item.classList.remove('active');
        }
    });
}

function openSectionPanel(panelKey) {
    if (!panelKey || panelKey === 'hub') {
        closeAllPanels();
        return;
    }

    const hub = document.getElementById('mainAccountHubView');
    if (hub) {
        hub.style.display = 'none';
        hub.classList.add('d-none');
    }

    document.querySelectorAll('.amz-subpage-panel, .amz-section-panel').forEach(function (panel) {
        panel.classList.remove('active');
        panel.classList.add('d-none');
        panel.style.setProperty('display', 'none', 'important');
    });

    const target = document.getElementById('panel_' + panelKey) || document.getElementById('panel-' + panelKey);
    if (target) {
        target.classList.add('active');
        target.classList.remove('d-none');
        target.style.setProperty('display', 'block', 'important');
        window.scrollTo({ top: 0, behavior: 'smooth' });

        if (history.pushState) {
            history.pushState({ panel: panelKey }, null, window.location.pathname + '?tab=' + panelKey);
        }
    }

    syncNavDropdown(panelKey);
}

function closeAllPanels() {
    document.querySelectorAll('.amz-subpage-panel, .amz-section-panel').forEach(function (panel) {
        panel.classList.remove('active');
        panel.classList.add('d-none');
        panel.style.setProperty('display', 'none', 'important');
    });

    const hub = document.getElementById('mainAccountHubView');
    if (hub) {
        hub.style.display = 'block';
        hub.classList.remove('d-none');
    }

    window.scrollTo({ top: 0, behavior: 'smooth' });

    if (history.pushState) {
        history.pushState({ panel: 'hub' }, null, window.location.pathname);
    }

    syncNavDropdown('hub');
}

function toggleAddressForm() {
    const formBox = document.getElementById('amzAddressFormBox');
    if (formBox) {
        if (formBox.style.display === 'none' || formBox.classList.contains('d-none')) {
            formBox.style.display = 'block';
            formBox.classList.remove('d-none');
            formBox.scrollIntoView({ behavior: 'smooth' });
        } else {
            formBox.style.display = 'none';
            formBox.classList.add('d-none');
        }
    }
}

function previewKycPhoto(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        if (file.size > 5 * 1024 * 1024) {
            alert('File size exceeds 5MB limit.');
            input.value = '';
            return;
        }
        const reader = new FileReader();
        reader.onload = function (e) {
            const preview = document.getElementById('kycAvatarPreview');
            const placeholder = document.getElementById('kycAvatarPlaceholder');
            if (preview) {
                preview.src = e.target.result;
                preview.classList.remove('d-none');
            }
            if (placeholder) {
                placeholder.style.display = 'none';
            }
        };
        reader.readAsDataURL(file);
    }
}

function toggleKycRoleFields(role) {
    const r = (role || '').toLowerCase();
    const authorEl = document.getElementById('kycAuthorFields');
    const publisherEl = document.getElementById('kycPublisherFields');
    const sellerEl = document.getElementById('kycSellerFields');

    if (authorEl) authorEl.classList.toggle('d-none', r !== 'author');
    if (publisherEl) publisherEl.classList.toggle('d-none', r !== 'publisher');
    if (sellerEl) sellerEl.classList.toggle('d-none', r !== 'seller');
}

// Browser Back/Forward navigation support
window.addEventListener('popstate', function (e) {
    if (e.state && e.state.panel && e.state.panel !== 'hub') {
        openSectionPanel(e.state.panel);
    } else {
        const urlParams = new URLSearchParams(window.location.search);
        const tab = urlParams.get('tab');
        if (tab) {
            openSectionPanel(tab);
        } else {
            closeAllPanels();
        }
    }
});

document.addEventListener('DOMContentLoaded', function () {
    const urlParams = new URLSearchParams(window.location.search);
    const tabParam = urlParams.get('tab');

    const hash = window.location.hash.replace(/^#/, '');
    const hashTab = hash.startsWith('tab=') ? hash.replace('tab=', '') : (hash || null);

    const initialTab = tabParam || hashTab;
    if (initialTab) {
        openSectionPanel(initialTab);
    }

    const kycRoleSelect = document.getElementById('kycRequestedRoleSelect');
    if (kycRoleSelect) {
        toggleKycRoleFields(kycRoleSelect.value);
    }
});
