/**
 * ══════════════════════════════════════════════════════════════════════════
 * IDEA PROKASHON — AMAZON "YOUR ACCOUNT" DYNAMIC CONTROLLER
 * ══════════════════════════════════════════════════════════════════════════
 */

const SECTION_TITLES = {
    'hub': { title: 'Your Account Hub', icon: 'fa-house' },
    'orders': { title: 'Your Orders', icon: 'fa-box-archive' },
    'loginSecurity': { title: 'Login & security', icon: 'fa-shield-halved' },
    'addresses': { title: 'Your Addresses', icon: 'fa-location-dot' },
    'payments': { title: 'Your Payments', icon: 'fa-credit-card' },
    'prime': { title: 'Prime Membership', icon: 'fa-crown' },
    'giftcards': { title: 'Gift cards & Balance', icon: 'fa-gift' },
    'digitalServices': { title: 'Digital Library & E-Books', icon: 'fa-book-open-reader' },
    'kyc': { title: 'KYC & Verification', icon: 'fa-id-card' },
    'royalties': { title: 'Royalties & Payouts', icon: 'fa-sack-dollar' },
    'authorHub': { title: 'Author Studio Hub', icon: 'fa-feather-pointed' },
    'blog': { title: 'Author Articles & Blog', icon: 'fa-pen-nib' },
    'memberships': { title: 'Memberships & Subscriptions', icon: 'fa-user-group' },
    'preferences': { title: 'Language & Preferences', icon: 'fa-globe' },
    'customerService': { title: 'Customer Service & Help', icon: 'fa-headset' },
};

function syncNavDropdown(panelKey) {
    const key = panelKey || 'hub';
    const info = SECTION_TITLES[key] || { title: 'Your Account Hub', icon: 'fa-house' };
    
    const labelEl = document.getElementById('currentAccountNavLabel');
    const iconEl = document.getElementById('currentAccountNavIcon');
    if (labelEl) labelEl.textContent = info.title;
    if (iconEl) iconEl.className = 'fa-solid ' + info.icon + ' text-primary';

    document.querySelectorAll('.amz-nav-menu .dropdown-item[data-panel]').forEach(function(item) {
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
    if (hub) hub.style.display = 'none';

    document.querySelectorAll('.amz-subpage-panel').forEach(function(panel) {
        panel.classList.remove('active');
        panel.style.setProperty('display', 'none', 'important');
    });

    const target = document.getElementById('panel_' + panelKey);
    if (target) {
        target.classList.add('active');
        target.style.setProperty('display', 'block', 'important');
        window.scrollTo({ top: 0, behavior: 'smooth' });

        if (history.pushState) {
            history.pushState({ panel: panelKey }, null, window.location.pathname + '?tab=' + panelKey);
        }
    }

    syncNavDropdown(panelKey);
}

function closeAllPanels() {
    document.querySelectorAll('.amz-subpage-panel').forEach(function(panel) {
        panel.classList.remove('active');
        panel.style.setProperty('display', 'none', 'important');
    });

    const hub = document.getElementById('mainAccountHubView');
    if (hub) {
        hub.style.display = 'block';
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
        reader.onload = function(e) {
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

// Browser Back/Forward navigation support
window.addEventListener('popstate', function(e) {
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

document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const tabParam = urlParams.get('tab');
    
    const hash = window.location.hash.replace(/^#/, '');
    const hashTab = hash.startsWith('tab=') ? hash.replace('tab=', '') : (hash || null);

    const initialTab = tabParam || hashTab;
    if (initialTab) {
        openSectionPanel(initialTab);
    }
});
