@php
    $drawerEcom = [];
    try {
        if (\Illuminate\Support\Facades\Schema::hasTable('admin_dashboard_settings')) {
            $settingRow = \Illuminate\Support\Facades\DB::table('admin_dashboard_settings')->where('key', 'ecommerce_settings')->first();
            if ($settingRow && $settingRow->value) {
                $decoded = json_decode($settingRow->value, true);
                $drawerEcom = is_array($decoded) ? $decoded : [];
            }
        }
    } catch (\Throwable $e) {}

    $feeDhaka = floatval($drawerEcom['delivery_dhaka'] ?? 50);
    $feeSub = floatval($drawerEcom['delivery_sub'] ?? 100);
    $feeOutside = floatval($drawerEcom['delivery_outside'] ?? 120);
    $freeThreshold = floatval($drawerEcom['free_delivery_threshold'] ?? 1500);
    $bkashNum = $drawerEcom['bkash_number'] ?? '01558712810';
    $nagadNum = $drawerEcom['nagad_number'] ?? '01558712810';
    $rocketNum = $drawerEcom['rocket_number'] ?? '01558712810';
@endphp

<!-- Global Interactive Cart Drawer (Offcanvas) -->
<div class="offcanvas offcanvas-end shadow-lg border-0" tabindex="-1" id="siteCartDrawer" aria-labelledby="siteCartDrawerLabel" style="width: 100%; max-width: 440px; z-index: 2600 !important; height: 100dvh; max-height: 100dvh;">
    
    <!-- Drawer Header -->
    <div class="offcanvas-header bg-light border-bottom p-3.5 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
            <div class="rounded-3 p-2 bg-primary text-white d-flex align-items-center justify-content-center shadow-xs" style="width: 36px; height: 36px;">
                <i class="fa-solid fa-cart-shopping"></i>
            </div>
            <div>
                <h6 class="offcanvas-title fw-bold text-dark mb-0" id="siteCartDrawerLabel">আমার শপিং কার্ট</h6>
                <span class="text-muted small" id="drawerItemCountText">০ টি পণ্য</span>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>

    <!-- Drawer Body -->
    <div class="offcanvas-body p-0 d-flex flex-column justify-content-between" style="overflow-y: auto;">
        
        <!-- Cart Items List Container -->
        <div class="p-3" id="drawerCartContent">
            
            <!-- Empty State (Hidden when items exist) -->
            <div id="drawerEmptyState" class="text-center py-5">
                <div class="rounded-circle bg-light text-muted mx-auto d-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px; font-size: 2rem;">
                    <i class="fa-solid fa-bag-shopping opacity-50"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1">আপনার কার্ট খালি আছে</h6>
                <p class="text-muted small mb-4">পছন্দের বই বা গ্যাজেট খুঁজে নিয়ে কার্টে যুক্ত করুন।</p>
                <div class="d-flex align-items-center justify-content-center gap-2">
                    <a href="{{ route('book.index') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 py-1.5 fw-semibold shadow-2xs" data-bs-dismiss="offcanvas">
                        <i class="fa-solid fa-book-open me-1"></i> বইসমূহ
                    </a>
                    <a href="{{ route('products.electronics') }}" class="btn btn-primary btn-sm rounded-pill px-3 py-1.5 fw-semibold shadow-xs" data-bs-dismiss="offcanvas">
                        <i class="fa-solid fa-laptop me-1"></i> গ্যাজেট শপ
                    </a>
                </div>
            </div>

            <!-- Items List -->
            <div id="drawerItemList" class="d-flex flex-column gap-2.5">
                <!-- Dynamically populated by JS -->
            </div>

        </div>

        <!-- Drawer Footer & Checkout Panel (Shown only when cart has items) -->
        <div id="drawerFooterPanel" class="border-top bg-light p-3.5 d-none">
            
            <!-- Calculations Breakdown -->
            <div class="bg-white rounded-3 p-3 border shadow-2xs mb-3">
                <div class="d-flex justify-content-between align-items-center mb-2 small text-muted">
                    <span>পণ্যের মোট মূল্য (Subtotal):</span>
                    <span class="fw-bold text-dark fs-6" id="drawerSubtotalDisplay">৳০</span>
                </div>
                
                <div class="mb-2">
                    <label class="form-label text-muted small fw-semibold mb-1">ডেলিভারি এলাকা:</label>
                    <select class="form-select form-select-sm rounded-3" id="drawerDeliveryAreaSelect" onchange="updateDrawerTotals()">
                        <option value="dhaka" data-fee="{{ $feeDhaka }}">ঢাকার ভিতরে (৳{{ round($feeDhaka) }})</option>
                        <option value="dhaka_sub" data-fee="{{ $feeSub }}">ঢাকা উপশহর (৳{{ round($feeSub) }})</option>
                        <option value="outside" data-fee="{{ $feeOutside }}" selected>ঢাকার বাইরে - সারা দেশ (৳{{ round($feeOutside) }})</option>
                    </select>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-2 small text-muted">
                    <span>ডেলিভারি চার্জ:</span>
                    <span class="fw-bold text-dark" id="drawerDeliveryFeeDisplay">৳{{ round($feeOutside) }}</span>
                </div>

                <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                    <span class="fw-bold text-dark">সর্বমোট বিল:</span>
                    <span class="fw-bold text-primary fs-5" id="drawerTotalDisplay">৳০</span>
                </div>
            </div>

            <!-- Quick Checkout Trigger Accordion / Button -->
            <div class="d-grid gap-2">
                <button type="button" class="btn btn-primary rounded-pill py-2.5 fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2" onclick="toggleDrawerCheckoutForm()">
                    <i class="fa-solid fa-bolt"></i>
                    <span>দ্রুত অর্ডার সম্পন্ন করুন</span>
                    <i class="fa-solid fa-chevron-down small" id="drawerCheckoutChevron"></i>
                </button>
                
                <a href="{{ route('cart') }}" class="btn btn-outline-secondary btn-sm rounded-pill py-1.5 fw-semibold text-center">
                    বিস্তারিত কার্ট পেজ দেখুন →
                </a>
            </div>

            <!-- Inline Checkout Form (Toggled) -->
            <div id="drawerInlineCheckoutForm" class="d-none mt-3 pt-3 border-top">
                <form id="drawerOrderForm" onsubmit="submitDrawerOrder(event)">
                    @csrf
                    <input type="hidden" name="cart_items" id="drawerCartItemsInput">
                    <input type="hidden" name="district" id="drawerOrderDistrict">

                    <div class="mb-2">
                        <label class="form-label text-dark small fw-bold mb-1" for="drawerName">আপনার নাম <span class="text-danger">*</span></label>
                        <input type="text" id="drawerName" name="customer_name" class="form-control rounded-3" autocomplete="name"
                               value="{{ auth()->user()?->name ?? '' }}" placeholder="আপনার নাম" required>
                    </div>

                    <div class="mb-2">
                        <label class="form-label text-dark small fw-bold mb-1" for="drawerPhone">মোবাইল নম্বর <span class="text-danger">*</span></label>
                        <input type="tel" id="drawerPhone" name="customer_phone" class="form-control rounded-3" inputmode="numeric" autocomplete="tel" maxlength="20"
                               value="{{ auth()->user()?->phone ?? '' }}" placeholder="01XXXXXXXXX" required>
                    </div>

                    <div class="mb-2">
                        <label class="form-label text-dark small fw-bold mb-1" for="drawerAddress">ডেলিভারি ঠিকানা <span class="text-danger">*</span></label>
                        <textarea id="drawerAddress" name="customer_address" class="form-control rounded-3" autocomplete="street-address" rows="2"
                                  placeholder="গ্রাম/এলাকা, থানা, জেলা" required>{{ auth()->user()?->address ?? '' }}</textarea>
                    </div>

                    {{-- Cash on delivery is the default; mobile-banking payment is one optional tap away --}}
                    <input type="hidden" name="payment_method" value="cod" id="drawerPaymentMethod">
                    <details class="mb-3 small" id="drawerPayMore">
                        <summary class="text-primary fw-semibold" style="cursor: pointer;">আগেই বিকাশ / নগদে পেমেন্ট করতে চান?</summary>
                        <div class="mt-2 p-2 bg-light rounded-3 border">
                            <div class="d-flex gap-2 mb-2">
                                <label class="form-check p-2 border rounded-3 bg-white d-flex align-items-center gap-2 mb-0 flex-fill">
                                    <input class="form-check-input ms-0" type="radio" name="drawer_pay_choice" value="bkash" onchange="toggleDrawerPaymentInput('bkash')">
                                    <span class="fw-semibold text-danger">বিকাশ {{ $bkashNum }}</span>
                                </label>
                                <label class="form-check p-2 border rounded-3 bg-white d-flex align-items-center gap-2 mb-0 flex-fill">
                                    <input class="form-check-input ms-0" type="radio" name="drawer_pay_choice" value="nagad" onchange="toggleDrawerPaymentInput('nagad')">
                                    <span class="fw-semibold text-dark">নগদ {{ $nagadNum }}</span>
                                </label>
                            </div>
                            <div id="drawerTrxInputBox" class="d-none">
                                <input type="text" name="transaction_id" class="form-control rounded-3 mb-1" placeholder="TrxID (যেমন 9J3K8L2M)">
                                <input type="text" name="payment_phone" class="form-control rounded-3" inputmode="numeric" placeholder="যে নম্বর থেকে পাঠিয়েছেন">
                                <button type="button" class="btn btn-link btn-sm p-0 mt-1" onclick="toggleDrawerPaymentInput('cod')">না, ক্যাশ অন ডেলিভারি</button>
                            </div>
                        </div>
                    </details>

<div id="drawerOrderError" class="alert alert-danger small py-2 px-3 mb-2 d-none" role="alert" aria-live="assertive"></div>

                    <button type="submit" id="drawerSubmitOrderBtn" class="btn btn-success btn-lg w-100 rounded-pill fw-bold shadow-sm py-2.5 d-flex align-items-center justify-content-center gap-2">
                        <i class="fa-solid fa-circle-check"></i>
                        <span>অর্ডার নিশ্চিত করুন</span>
                    </button>
                </form>
            </div>

        </div>

    </div>
</div>

<script>
    // Cart Drawer JS Handlers
    (function() {
        const feeDhaka = {{ $feeDhaka }};
        const feeSub = {{ $feeSub }};
        const feeOutside = {{ $feeOutside }};
        const freeThreshold = {{ $freeThreshold }};

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

            fetch("{{ route('cart.checkout') }}", {
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
                    window.location.href = data.redirect || "{{ route('home') }}";
                    return;
                }
                if (res.status === 419) {
                    showError('পেজের সেশন শেষ হয়ে গেছে। পেজটি রিফ্রেশ করে আবার অর্ডার করুন।', { href: location.href, label: 'রিফ্রেশ' });
                } else if (res.status === 422 && data && data.errors) {
                    showError(Object.values(data.errors).flat().join(' '));       // e.g. the phone number is not valid
                } else if (res.status === 403 && data && data.message) {
                    // signed in but not yet allowed to order (unverified phone / e-mail, awaiting approval)
                    showError(data.message, data.requires_verification ? { href: "{{ url('/my-account') }}", label: 'যাচাই করুন →' } : null);
                } else if (data && data.message) {
                    showError(data.message);
                } else {
                    showError('অর্ডার সম্পন্ন করা যায়নি। ইন্টারনেট দেখে আবার চেষ্টা করুন, না হলে ' + '{{ $bkashNum }}' + ' নম্বরে কল করুন।');
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
                window.location.href = "{{ route('cart') }}";      // no drawer available: the full cart page still takes the order
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
</script>
