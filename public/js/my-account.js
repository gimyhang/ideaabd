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

function viewOrderModal(orderId) {
    const modalEl = document.getElementById('amzOrderDetailModal');
    if (!modalEl) return;

    const bsModal = window.bootstrap && window.bootstrap.Modal ? window.bootstrap.Modal.getOrCreateInstance(modalEl) : null;
    const bodyEl = document.getElementById('amzOrderDetailModalBody');
    if (bodyEl) {
        bodyEl.innerHTML = '<div class="text-center py-5 text-muted"><span class="spinner-border spinner-border-sm me-2"></span>অর্ডারের বিবরণ লোড হচ্ছে...</div>';
    }
    if (bsModal) bsModal.show();

    fetch('/my-account/orders/' + orderId, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(function (r) { return r.json(); })
    .then(function (data) {
        if (!data.success || !data.order) {
            if (bodyEl) bodyEl.innerHTML = '<div class="alert alert-danger mb-0">অর্ডার তথ্য পাওয়া যায়নি।</div>';
            return;
        }
        const o = data.order;
        if (bodyEl) {
            bodyEl.innerHTML = `
                <div class="d-flex align-items-center justify-content-between pb-3 border-bottom mb-3 flex-wrap gap-2">
                    <div>
                        <h5 class="fw-bold mb-1 text-dark">অর্ডার #${o.order_number}</h5>
                        <small class="text-muted">${o.created_at}</small>
                    </div>
                    <div>
                        <span class="badge ${o.status === 'delivered' ? 'bg-success' : (o.status === 'cancelled' ? 'bg-danger' : 'bg-warning text-dark')} px-3 py-1.5 rounded-pill">
                            ${o.status_label || o.status}
                        </span>
                    </div>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded-3 h-100">
                            <span class="text-muted small d-block">প্রাপকের তথ্য</span>
                            <strong class="text-dark d-block mt-1">${o.customer_name}</strong>
                            <div class="text-secondary small mt-1">ফোন: ${o.customer_phone}</div>
                            <div class="text-secondary small mt-1">ঠিকানা: ${o.full_address}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded-3 h-100">
                            <span class="text-muted small d-block">পেমেন্ট ও ডেলিভারি</span>
                            <div class="d-flex justify-content-between small text-dark mt-1">
                                <span>পেমেন্ট মাধ্যম:</span>
                                <strong>${o.payment_method || 'Cash on Delivery'}</strong>
                            </div>
                            <div class="d-flex justify-content-between small text-dark mt-1">
                                <span>পেমেন্ট অবস্থা:</span>
                                <strong>${o.payment_status || 'Pending'}</strong>
                            </div>
                            ${o.courier_name ? `
                            <div class="d-flex justify-content-between small text-dark mt-1">
                                <span>কুরিয়ার:</span>
                                <strong>${o.courier_name}</strong>
                            </div>` : ''}
                            ${o.tracking_code ? `
                            <div class="d-flex justify-content-between small text-dark mt-1">
                                <span>ট্র্যাকিং কোড:</span>
                                <strong class="font-monospace text-primary">${o.tracking_code}</strong>
                            </div>` : ''}
                        </div>
                    </div>
                </div>
                <div class="card border rounded-3 p-3 bg-white mb-3">
                    <div class="d-flex align-items-center gap-3">
                        ${o.book_cover ? `<img src="${o.book_cover}" alt="${o.book_title}" style="width: 52px; height: 70px; object-fit: cover; border-radius: 6px;" class="border flex-shrink-0">` : '<div style="width: 52px; height: 70px; background: #f1f5f9; border-radius: 6px;" class="d-flex align-items-center justify-content-center text-muted flex-shrink-0"><i class="fa-solid fa-book"></i></div>'}
                        <div class="flex-grow-1 min-w-0">
                            <h6 class="fw-semibold mb-1 text-dark text-truncate">${o.book_title}</h6>
                            <small class="text-muted d-block">${o.book_author}</small>
                            <span class="text-secondary small">পরিমাণ: ${o.quantity} কপি</span>
                        </div>
                        <div class="text-end flex-shrink-0">
                            <span class="text-muted small d-block">সর্বমোট মূল্য</span>
                            <strong class="text-dark fs-6">৳${Number(o.total_amount).toLocaleString('en-US')}</strong>
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center pt-2">
                    <a href="${o.track_url}" class="amz-btn-gold">
                        লাইভ ট্র্যাক করুন
                    </a>
                    <button type="button" class="amz-btn-silver" data-bs-dismiss="modal">
                        বন্ধ করুন
                    </button>
                </div>
            `;
        }
    })
    .catch(function (err) {
        if (bodyEl) bodyEl.innerHTML = '<div class="alert alert-danger mb-0">অর্ডার তথ্য লোড করতে সমস্যা হয়েছে। আবার চেষ্টা করুন।</div>';
    });
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
