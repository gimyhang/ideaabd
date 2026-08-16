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
<div class="offcanvas offcanvas-end shadow-lg border-0" tabindex="-1" id="siteCartDrawer" aria-labelledby="siteCartDrawerLabel" style="width: 100%; max-width: 440px; z-index: 1060;">
    
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
                <p class="text-muted small mb-4">পছন্দের বই খুঁজে নিয়ে কার্টে যুক্ত করুন।</p>
                <a href="{{ route('book.index') }}" class="btn btn-primary btn-sm rounded-pill px-4 py-2 fw-semibold shadow-xs" data-bs-dismiss="offcanvas">
                    <i class="fa-solid fa-book-open me-1"></i> বইসমূহ দেখুন
                </a>
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

                    <div class="mb-2.5">
                        <label class="form-label text-dark small fw-bold mb-1">আপনার নাম <span class="text-danger">*</span></label>
                        <input type="text" name="customer_name" class="form-control form-control-sm rounded-3" 
                               value="{{ auth()->user()?->name ?? '' }}" placeholder="আপনার পূর্ণ নাম দিন" required>
                    </div>

                    <div class="mb-2.5">
                        <label class="form-label text-dark small fw-bold mb-1">মোবাইল নম্বর <span class="text-danger">*</span></label>
                        <input type="tel" name="customer_phone" class="form-control form-control-sm rounded-3" 
                               value="{{ auth()->user()?->phone ?? '' }}" placeholder="01XXXXXXXXX" required>
                    </div>

                    <div class="mb-2.5">
                        <label class="form-label text-dark small fw-bold mb-1">সম্পূর্ণ ডেলিভারি ঠিকানা <span class="text-danger">*</span></label>
                        <textarea name="customer_address" class="form-control form-control-sm rounded-3" rows="2" 
                                  placeholder="বাসা নং, রোড, এলাকা, থানা ও জেলা লিখুন..." required>{{ auth()->user()?->address ?? '' }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-dark small fw-bold mb-1">পেমেন্ট মাধ্যম</label>
                        <div class="d-flex flex-column gap-1.5">
                            <label class="form-check p-2 border rounded-3 bg-white d-flex align-items-center gap-2 cursor-pointer mb-0">
                                <input class="form-check-input ms-0 me-2" type="radio" name="payment_method" value="cod" checked onchange="toggleDrawerPaymentInput('cod')">
                                <span class="small fw-semibold text-dark">ক্যাশ অন ডেলিভারি (হোম ডেলিভারি)</span>
                            </label>
                            
                            <label class="form-check p-2 border rounded-3 bg-white d-flex align-items-center gap-2 cursor-pointer mb-0">
                                <input class="form-check-input ms-0 me-2" type="radio" name="payment_method" value="bkash" onchange="toggleDrawerPaymentInput('bkash')">
                                <span class="small fw-semibold text-danger">বিকাশ (Send Money: {{ $bkashNum }})</span>
                            </label>

                            <label class="form-check p-2 border rounded-3 bg-white d-flex align-items-center gap-2 cursor-pointer mb-0">
                                <input class="form-check-input ms-0 me-2" type="radio" name="payment_method" value="nagad" onchange="toggleDrawerPaymentInput('nagad')">
                                <span class="small fw-semibold text-warning text-dark">নগদ (Send Money: {{ $nagadNum }})</span>
                            </label>
                        </div>

                        <!-- TrxID Input Box (Hidden by default) -->
                        <div id="drawerTrxInputBox" class="d-none mt-2 p-2.5 bg-light rounded-3 border">
                            <label class="form-label text-dark small fw-semibold mb-1">ট্রানজাকশন আইডি (TrxID) ও প্রেরক নম্বর</label>
                            <input type="text" name="transaction_id" class="form-control form-control-sm rounded-3 mb-1.5" placeholder="যেমন: 9J3K8L2M">
                            <input type="text" name="payment_phone" class="form-control form-control-sm rounded-3" placeholder="যে নম্বর থেকে টাকা পাঠিয়েছেন">
                        </div>
                    </div>

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

        function getSafeCart() {
            try {
                return JSON.parse(localStorage.getItem('idea_cart') || '[]');
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

            const totalQty = cart.reduce((sum, item) => sum + (item.quantity || item.qty || 1), 0);
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
                const qty = item.quantity || item.qty || 1;
                const price = Number(item.price) || 0;
                const itemTotal = price * qty;
                const imgSrc = item.image || '/images/default-book.png';

                html += `
                    <div class="p-2.5 bg-white border rounded-3 shadow-2xs d-flex align-items-center gap-2.5">
                        <img src="${imgSrc}" alt="${item.title}" class="rounded-2 object-fit-cover flex-shrink-0" style="width: 50px; height: 68px; border: 1px solid #eee;" onerror="this.onerror=null; this.src='/images/default-book.png';">
                        <div class="flex-grow-1 min-w-0">
                            <h6 class="fw-bold text-dark mb-1 small text-truncate" title="${item.title}">${item.title}</h6>
                            <div class="text-primary fw-bold small mb-2">৳${price.toLocaleString('bn-BD')} <span class="text-muted fw-normal" style="font-size: 11px;">× ${qty} = ৳${itemTotal.toLocaleString('bn-BD')}</span></div>
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
                let current = cart[index].quantity || cart[index].qty || 1;
                let newQty = current + delta;
                if (newQty <= 0) {
                    cart.splice(index, 1);
                } else {
                    cart[index].quantity = newQty;
                    cart[index].qty = newQty;
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
                const qty = item.quantity || item.qty || 1;
                const price = Number(item.price) || 0;
                subtotal += (price * qty);
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
            if (trxBox) {
                if (method === 'bkash' || method === 'nagad' || method === 'rocket') {
                    trxBox.classList.remove('d-none');
                } else {
                    trxBox.classList.add('d-none');
                }
            }
        };

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

            fetch("{{ route('cart.checkout') }}", {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: formData
            })
            .then(res => {
                if (!res.ok) {
                    return res.json().then(err => { throw err; });
                }
                return res.json();
            })
            .then(data => {
                if (data.success) {
                    // Clear cart
                    localStorage.removeItem('idea_cart');
                    if (typeof updateHeaderCartBadge === 'function') updateHeaderCartBadge();
                    
                    // Close offcanvas
                    const drawerEl = document.getElementById('siteCartDrawer');
                    if (drawerEl) {
                        const modal = bootstrap.Offcanvas.getInstance(drawerEl);
                        if (modal) modal.hide();
                    }

                    window.location.href = data.redirect || "{{ route('home') }}";
                } else {
                    alert(data.message || 'অর্ডারে ত্রুটি হয়েছে। অনুগ্রহ করে আবার চেষ্টা করুন।');
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = '<i class="fa-solid fa-circle-check"></i> <span>অর্ডার নিশ্চিত করুন</span>';
                    }
                }
            })
            .catch(err => {
                console.error('Order submission error:', err);
                const errMsg = err && err.message ? err.message : 'অর্ডার প্রক্রিয়াকরণে সমস্যা হয়েছে। সরাসরি ফর্ম সাবমিট করা হচ্ছে...';
                // Fallback to normal form submit
                form.action = "{{ route('cart.checkout') }}";
                form.method = "POST";
                form.submit();
            });
        };

        // Open cart drawer helper
        window.openCartDrawer = function() {
            renderCartDrawer();
            const drawerEl = document.getElementById('siteCartDrawer');
            if (drawerEl) {
                const offcanvas = bootstrap.Offcanvas.getOrCreateInstance(drawerEl);
                offcanvas.show();
            }
        };

        document.addEventListener('DOMContentLoaded', function() {
            renderCartDrawer();
            
            const drawerEl = document.getElementById('siteCartDrawer');
            if (drawerEl) {
                drawerEl.addEventListener('show.bs.offcanvas', renderCartDrawer);
            }
        });
    })();
</script>
