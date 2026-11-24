@php
    $drawerEcom = [];
    try {
        if (\App\Support\SchemaCache::has('admin_dashboard_settings')) {
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

<script>window.IDEA_CART = {!! json_encode(['feeDhaka' => (float) $feeDhaka, 'feeSub' => (float) $feeSub, 'feeOutside' => (float) $feeOutside, 'freeThreshold' => (float) $freeThreshold, 'checkout' => route('cart.checkout'), 'home' => route('home'), 'account' => url('/my-account'), 'cart' => route('cart'), 'phone' => (string) $bkashNum], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!};</script>
<script src="{{ \App\Support\AssetBundle::versioned('js/cart-drawer.js') }}" defer></script>
