/* Cart drawer: list, totals, delivery fee and the quick order form (was inline in partials/cart-drawer.blade.php).
 * Loaded with defer; fees and addresses come from window.IDEA_CART, printed by the partial. */
// Cart Drawer JS Handlers
(function() {
    const C = window.IDEA_CART || {};
    const feeDhaka = (+C.feeDhaka || 0);
    const feeSub = (+C.feeSub || 0);
    const feeOutside = (+C.feeOutside || 0);
    const freeThreshold = (+C.freeThreshold || 0);

    function parsePrice(val) {
        if (typeof val === 'number') return isNaN(val) ? 0 : val;
        if (!val) return 0;
        const cleaned = String(val).replace(/[^0-9.]/g, '');
        const parsed = parseFloat(cleaned);
        return isNaN(parsed) ? 0 : parsed;
    }

    function getSafeCart() {
        try {
            const raw = JSON.parse(localStorage.getItem('idea_cart') || '[]');
            if (!Array.isArray(raw)) return [];
            return raw.map(item => ({
                id: item.id,
                title: item.title || 'বই',
                price: parsePrice(item.price),
                image: item.image || '',
                quantity: Math.max(1, parseInt(item.quantity || item.qty || 1, 10) || 1),
                type: item.type || '',
                format: item.format || (item.type ? '' : 'paperback')
            }));
        } catch(e) {
            return [];
        }
    }

    function saveSafeCart(cart) {
        try {
            localStorage.setItem('idea_cart', JSON.stringify(cart));
        } catch(e) {}
        if (typeof updateHeaderCartBadge === 'function') {
            updateHeaderCartBadge();
        }
        renderCartDrawer();
    }

    window.renderCartDrawer = function() {
        const cart = getSafeCart();
        const listEl = document.getElementById('drawerItemList');
        const emptyEl = document.getElementById('drawerEmptyState');
        const footerEl = document.getElementById('drawerFooterPanel');
        const countTextEl = document.getElementById('drawerItemCountText');

        if (!listEl || !emptyEl || !footerEl) return;

        const totalQty = cart.reduce((sum, item) => sum + item.quantity, 0);
        if (countTextEl) {
            countTextEl.textContent = (totalQty).toLocaleString('bn-BD') + ' টি পণ্য';
        }

        if (cart.length === 0) {
            listEl.innerHTML = '';
            emptyEl.classList.remove('d-none');
            footerEl.classList.add('d-none');
            return;
        }

        emptyEl.classList.add('d-none');
        footerEl.classList.remove('d-none');

        let html = '';
        cart.forEach((item, index) => {
            const qty = item.quantity;
            const price = item.price;
            const itemTotal = price * qty;
            const isGadget = item.type === 'electronics' || item.type === 'stationery';
            const imgSrc = item.image || (isGadget ? '/assets/images/placeholder-product.svg' : '/images/default-book.png');
            let formatLabel = '';
            if (item.type === 'electronics') {
                formatLabel = 'গ্যাজেট';
            } else if (item.type === 'stationery') {
                formatLabel = 'স্টেশনারি';
            } else if (item.format) {
                formatLabel = item.format === 'hardcover' ? 'হার্ডকভার' : 'পেপারব্যাক';
            } else {
                formatLabel = 'জেনুইন';
            }

            html += `
                <div class="p-2.5 bg-white border rounded-3 shadow-2xs d-flex align-items-center gap-2.5">
                    <img src="${imgSrc}" alt="${item.title}" class="rounded-2 object-fit-contain bg-light flex-shrink-0" style="width: 52px; height: 52px; border: 1px solid #eee;" onerror="this.onerror=null; this.src='/assets/images/placeholder-product.svg';">
                    <div class="flex-grow-1 min-w-0">
                        <h6 class="fw-bold text-dark mb-1 small text-truncate" title="${item.title}">${item.title}</h6>
                        <div class="d-flex align-items-center gap-1.5 mb-1.5 flex-wrap">
                            <span class="badge bg-light text-secondary border" style="font-size: 10px;">${formatLabel}</span>
                            <span class="text-muted small" style="font-size: 11px;">একক দাম: ৳${price.toLocaleString('bn-BD')}</span>
                        </div>
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="input-group input-group-sm border rounded-pill bg-light overflow-hidden" style="width: 95px;">
                                <button class="btn btn-sm btn-light px-2 py-0" type="button" onclick="updateCartItemQty(${index}, -1)">
                                    <i class="fa-solid fa-minus" style="font-size: 9px;"></i>
                                </button>
                                <span class="form-control form-control-sm text-center border-0 bg-light p-0 fw-bold" style="font-size: 12px; line-height: 24px;">${qty}</span>
                                <button class="btn btn-sm btn-light px-2 py-0" type="button" onclick="updateCartItemQty(${index}, 1)">
                                    <i class="fa-solid fa-plus" style="font-size: 9px;"></i>
                                </button>
                            </div>
                            <div class="text-end">
                                <span class="text-primary fw-bold small d-block">৳${itemTotal.toLocaleString('bn-BD')}</span>
                                <span class="text-muted" style="font-size: 10px;">(৳${price.toLocaleString('bn-BD')} × ${qty})</span>
                            </div>
                            <button type="button" class="btn btn-link text-danger p-0 text-decoration-none small" onclick="removeCartItem(${index})" title="মুছে ফেলুন">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
        });

        listEl.innerHTML = html;
        updateDrawerTotals();
    };

    window.updateCartItemQty = function(index, delta) {
        let cart = getSafeCart();
        if (cart[index]) {
            let current = cart[index].quantity;
            let newQty = current + delta;
            if (newQty <= 0) {
                cart.splice(index, 1);
            } else {
                cart[index].quantity = Math.min(99, newQty);
            }
            saveSafeCart(cart);
        }
    };

    window.removeCartItem = function(index) {
        let cart = getSafeCart();
        cart.splice(index, 1);
        saveSafeCart(cart);
    };

    window.updateDrawerTotals = function() {
        const cart = getSafeCart();
        let subtotal = 0;
        cart.forEach(item => {
            subtotal += (item.price * item.quantity);
        });

        const areaSelect = document.getElementById('drawerDeliveryAreaSelect');
        const selectedOpt = areaSelect ? areaSelect.options[areaSelect.selectedIndex] : null;
        let fee = selectedOpt ? parseFloat(selectedOpt.dataset.fee || feeOutside) : feeOutside;

        if (freeThreshold > 0 && subtotal >= freeThreshold) {
            fee = 0;
        }

        const total = subtotal + fee;

        const subDisplay = document.getElementById('drawerSubtotalDisplay');
        const feeDisplay = document.getElementById('drawerDeliveryFeeDisplay');
        const totalDisplay = document.getElementById('drawerTotalDisplay');

        if (subDisplay) subDisplay.textContent = '৳' + subtotal.toLocaleString('bn-BD');
        if (feeDisplay) feeDisplay.textContent = fee === 0 ? 'ফ্রি' : '৳' + fee.toLocaleString('bn-BD');
        if (totalDisplay) totalDisplay.textContent = '৳' + total.toLocaleString('bn-BD');
    };

    window.toggleDrawerCheckoutForm = function() {
        const form = document.getElementById('drawerInlineCheckoutForm');
        const chevron = document.getElementById('drawerCheckoutChevron');
        if (form) {
            form.classList.toggle('d-none');
            if (chevron) {
                chevron.classList.toggle('fa-chevron-up');
                chevron.classList.toggle('fa-chevron-down');
            }
        }
    };

    window.toggleDrawerPaymentInput = function(method) {
        const trxBox = document.getElementById('drawerTrxInputBox');
        const field = document.getElementById('drawerPaymentMethod');
        const online = method === 'bkash' || method === 'nagad' || method === 'rocket';
        if (field) field.value = online ? method : 'cod';
        if (trxBox) trxBox.classList.toggle('d-none', !online);
        if (!online) {
            document.querySelectorAll('input[name="drawer_pay_choice"]').forEach(r => { r.checked = false; });
            trxBox?.querySelectorAll('input').forEach(i => { i.value = ''; });
        }
    };

    // Remember who ordered on this phone: the next order is one tap
    const QUICK_KEY = 'idea_quick_customer';
    function restoreQuickCustomer() {
        try {
            const saved = JSON.parse(localStorage.getItem(QUICK_KEY) || 'null');
            if (!saved) return;
            const f = document.getElementById('drawerOrderForm');
            ['customer_phone', 'customer_address', 'customer_name'].forEach(n => {
                const el = f?.elements[n];
                if (el && !el.value && saved[n]) el.value = saved[n];
            });
        } catch (e) { /* storage blocked */ }
    }
    function rememberQuickCustomer(form) {
        try {
            localStorage.setItem(QUICK_KEY, JSON.stringify({
                customer_phone: form.elements['customer_phone']?.value || '',
                customer_address: form.elements['customer_address']?.value || '',
                customer_name: form.elements['customer_name']?.value || '',
            }));
        } catch (e) { /* storage blocked */ }
    }
    window.submitDrawerOrder = function(e) {
        e.preventDefault();
        const cart = getSafeCart();
        if (cart.length === 0) {
            alert('আপনার কার্টে কোনো পণ্য নেই!');
            return;
        }

        const form = document.getElementById('drawerOrderForm');
        const areaSelect = document.getElementById('drawerDeliveryAreaSelect');
        const districtInput = document.getElementById('drawerOrderDistrict');
        const cartItemsInput = document.getElementById('drawerCartItemsInput');
        const submitBtn = document.getElementById('drawerSubmitOrderBtn');

        if (districtInput && areaSelect) {
            districtInput.value = areaSelect.value;
        }
        if (cartItemsInput) {
            cartItemsInput.value = JSON.stringify(cart);
        }

        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> অর্ডার প্রক্রিয়াধীন...';
        }

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || form.querySelector('input[name="_token"]')?.value;
        const formData = new FormData(form);

        const errBox = document.getElementById('drawerOrderError');
        const resetBtn = () => {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fa-solid fa-circle-check"></i> <span>অর্ডার নিশ্চিত করুন</span>';
            }
        };
        const showError = (message, link) => {
            if (errBox) {
                errBox.textContent = message;
                if (link) {
                    const a = document.createElement('a');
                    a.href = link.href; a.textContent = ' ' + link.label; a.className = 'fw-bold';
                    errBox.appendChild(a);
                }
                errBox.classList.remove('d-none');
                errBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
            } else { alert(message); }
            resetBtn();
        };
        if (errBox) errBox.classList.add('d-none');

        fetch(C.checkout, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: formData,
            credentials: 'same-origin'
        })
        .then(async res => {
            let data = null;
            try { data = await res.json(); } catch (e) { /* HTML error page */ }

            if (res.ok && data && data.success) {
                rememberQuickCustomer(form);
                localStorage.removeItem('idea_cart');
                if (typeof updateHeaderCartBadge === 'function') updateHeaderCartBadge();
                const drawerEl = document.getElementById('siteCartDrawer');
                if (drawerEl) { bootstrap.Offcanvas.getInstance(drawerEl)?.hide(); }
                window.location.href = data.redirect || C.home;
                return;
            }
            if (res.status === 419) {
                showError('পেজের সেশন শেষ হয়ে গেছে। পেজটি রিফ্রেশ করে আবার অর্ডার করুন।', { href: location.href, label: 'রিফ্রেশ' });
            } else if (res.status === 422 && data && data.errors) {
                showError(Object.values(data.errors).flat().join(' '));       // e.g. the phone number is not valid
            } else if (res.status === 403 && data && data.message) {
                // signed in but not yet allowed to order (unverified phone / e-mail, awaiting approval)
                showError(data.message, data.requires_verification ? { href: C.account, label: 'যাচাই করুন →' } : null);
            } else if (data && data.message) {
                showError(data.message);
            } else {
                showError('অর্ডার সম্পন্ন করা যায়নি। ইন্টারনেট দেখে আবার চেষ্টা করুন, না হলে ' + C.phone + ' নম্বরে কল করুন।');
            }
        })
        .catch(err => {
            console.error('Order submission error:', err);
            showError('নেটওয়ার্ক সমস্যা হয়েছে। সংযোগ দেখে আবার চেষ্টা করুন।');
        });
    };

    // Open cart drawer helper
    window.openCartDrawer = function(opts) {
        renderCartDrawer();
        restoreQuickCustomer();
        const drawerEl = document.getElementById('siteCartDrawer');
        if (!drawerEl || typeof bootstrap === 'undefined' || !bootstrap.Offcanvas) {
            window.location.href = C.cart;      // no drawer available: the full cart page still takes the order
            return;
        }
        {
            const offcanvas = bootstrap.Offcanvas.getOrCreateInstance(drawerEl);
            offcanvas.show();
            // "Buy now": show the order form straight away instead of one more tap
            if (opts && opts.checkout) {
                const form = document.getElementById('drawerInlineCheckoutForm');
                if (form && form.classList.contains('d-none') && getSafeCart().length) toggleDrawerCheckoutForm();
            }
        }
    };

    document.addEventListener('DOMContentLoaded', function() {
        renderCartDrawer();
        
        const drawerEl = document.getElementById('siteCartDrawer');
        restoreQuickCustomer();
        if (drawerEl) {
            drawerEl.addEventListener('show.bs.offcanvas', renderCartDrawer);
        }
    });
})();
