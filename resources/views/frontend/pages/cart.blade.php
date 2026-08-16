@extends('layouts.app')

@section('title', 'শপিং কার্ট ও চেকআউট — ' . config('brand.name'))

@section('content')
<div class="container py-4 py-md-5">
    
    <!-- Breadcrumb & Header -->
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1 small">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none text-muted">হোম</a></li>
                <li class="breadcrumb-item"><a href="{{ route('book.index') }}" class="text-decoration-none text-muted">বইসমূহ</a></li>
                <li class="breadcrumb-item active text-primary" aria-current="page">শপিং কার্ট</li>
            </ol>
        </nav>
        <h2 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
            <i class="fa-solid fa-cart-shopping text-primary"></i>
            <span>আমার শপিং কার্ট ও চেকআউট</span>
        </h2>
    </div>

    <!-- Empty State -->
    <div id="fullCartEmptyState" class="card border-0 shadow-sm rounded-4 p-5 text-center my-4 d-none">
        <div class="rounded-circle bg-light text-muted mx-auto d-flex align-items-center justify-content-center mb-3" style="width: 100px; height: 100px; font-size: 2.8rem;">
            <i class="fa-solid fa-bag-shopping opacity-50 text-primary"></i>
        </div>
        <h4 class="fw-bold text-dark mb-2">আপনার শপিং কার্ট বর্তমানে খালি</h4>
        <p class="text-muted mb-4" style="max-width: 420px; margin: 0 auto;">আপনার পছন্দের কোনো বই বা পণ্য কার্টে যোগ করা হয়নি। আমাদের সমৃদ্ধ ক্যাটালগ থেকে বই বেছে নিন।</p>
        <div>
            <a href="{{ route('book.index') }}" class="btn btn-primary rounded-pill px-5 py-2.5 fw-bold shadow-sm">
                <i class="fa-solid fa-book-open me-2"></i> বই ক্যাটালগ দেখুন
            </a>
        </div>
    </div>

    <!-- Active Cart Content Grid -->
    <div id="fullCartContentGrid" class="row g-4 d-none">
        
        <!-- Left: Items List Table -->
        <div class="col-12 col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-4">
                <div class="card-header bg-white border-bottom p-3.5 d-flex align-items-center justify-content-between">
                    <h5 class="fw-bold text-dark mb-0">কার্টের পণ্যসমূহ</h5>
                    <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3" onclick="clearFullCart()">
                        <i class="fa-solid fa-trash-can me-1"></i> কার্ট খালি করুন
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light small text-secondary">
                                <tr>
                                    <th class="ps-3.5">পণ্য</th>
                                    <th>মূল্য</th>
                                    <th class="text-center">পরিমাণ</th>
                                    <th class="text-end">মোট</th>
                                    <th class="text-end pe-3.5">অ্যাকশন</th>
                                </tr>
                            </thead>
                            <tbody id="fullCartItemsTable">
                                <!-- Populated dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white border-top p-3 d-flex justify-content-between align-items-center">
                    <a href="{{ route('book.index') }}" class="btn btn-link text-primary text-decoration-none fw-semibold p-0 small">
                        <i class="fa-solid fa-arrow-left me-1"></i> আরও বই কিনুন
                    </a>
                    <span class="text-muted small" id="fullCartCountSummary">০ টি পণ্য নির্বাচিত</span>
                </div>
            </div>
        </div>

        <!-- Right: Checkout & Billing Form -->
        <div class="col-12 col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-4 sticky-top" style="top: 100px;">
                <h5 class="fw-bold text-dark mb-3 pb-2 border-bottom">অর্ডার ও ডেলিভারি তথ্য</h5>

                <form id="fullCartCheckoutForm" method="POST" action="{{ route('cart.checkout') }}">
                    @csrf
                    <input type="hidden" name="cart_items" id="fullCartItemsHidden">
                    <input type="hidden" name="district" id="fullCartDistrictHidden">

                    <!-- Customer Inputs -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">আপনার পূর্ণ নাম <span class="text-danger">*</span></label>
                        <input type="text" name="customer_name" class="form-control rounded-3" 
                               value="{{ auth()->user()?->name ?? '' }}" placeholder="আপনার নাম লিখুন" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">মোবাইল নম্বর <span class="text-danger">*</span></label>
                        <input type="tel" name="customer_phone" class="form-control rounded-3" 
                               value="{{ auth()->user()?->phone ?? '' }}" placeholder="01XXXXXXXXX" required>
                    </div>

                    <!-- Delivery Area Selector -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">ডেলিভারি অঞ্চল <span class="text-danger">*</span></label>
                        <div class="d-flex flex-column gap-2">
                            <label class="form-check p-2.5 border rounded-3 bg-light d-flex align-items-center gap-2 cursor-pointer mb-0">
                                <input class="form-check-input ms-0 me-2" type="radio" name="area_radio" value="dhaka" data-fee="{{ $ecomSetting['delivery_dhaka'] ?? 50 }}" onchange="updateFullCartCalculations()">
                                <span class="small fw-semibold text-dark flex-grow-1">ঢাকার ভিতরে</span>
                                <span class="badge bg-primary rounded-pill">৳{{ round($ecomSetting['delivery_dhaka'] ?? 50) }}</span>
                            </label>
                            <label class="form-check p-2.5 border rounded-3 bg-light d-flex align-items-center gap-2 cursor-pointer mb-0">
                                <input class="form-check-input ms-0 me-2" type="radio" name="area_radio" value="dhaka_sub" data-fee="{{ $ecomSetting['delivery_sub'] ?? 100 }}" onchange="updateFullCartCalculations()">
                                <span class="small fw-semibold text-dark flex-grow-1">ঢাকা উপশহর (সাভার, গাজীপুর, কেরানীগঞ্জ)</span>
                                <span class="badge bg-primary rounded-pill">৳{{ round($ecomSetting['delivery_sub'] ?? 100) }}</span>
                            </label>
                            <label class="form-check p-2.5 border rounded-3 bg-light d-flex align-items-center gap-2 cursor-pointer mb-0">
                                <input class="form-check-input ms-0 me-2" type="radio" name="area_radio" value="outside" data-fee="{{ $ecomSetting['delivery_outside'] ?? 120 }}" checked onchange="updateFullCartCalculations()">
                                <span class="small fw-semibold text-dark flex-grow-1">ঢাকার বাইরে (সারাদেশ)</span>
                                <span class="badge bg-primary rounded-pill">৳{{ round($ecomSetting['delivery_outside'] ?? 120) }}</span>
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">সম্পূর্ণ ঠিকানা <span class="text-danger">*</span></label>
                        <textarea name="customer_address" class="form-control rounded-3" rows="2" 
                                  placeholder="বাসা নং, রোড, গ্রাম/মহল্লা, থানা ও জেলার নাম লিখুন..." required>{{ auth()->user()?->address ?? '' }}</textarea>
                    </div>

                    <!-- Payment Methods -->
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-dark">পেমেন্ট মাধ্যম বেছে নিন</label>
                        <div class="d-flex flex-column gap-2">
                            <label class="form-check p-2.5 border rounded-3 bg-light d-flex align-items-center gap-2 cursor-pointer mb-0">
                                <input class="form-check-input ms-0 me-2" type="radio" name="payment_method" value="cod" checked onchange="toggleFullCartTrxInput('cod')">
                                <span class="small fw-semibold text-dark flex-grow-1">ক্যাশ অন ডেলিভারি (হোম ডেলিভারি)</span>
                                <i class="fa-solid fa-hand-holding-dollar text-success"></i>
                            </label>

                            <label class="form-check p-2.5 border rounded-3 bg-light d-flex align-items-center gap-2 cursor-pointer mb-0">
                                <input class="form-check-input ms-0 me-2" type="radio" name="payment_method" value="bkash" onchange="toggleFullCartTrxInput('bkash')">
                                <span class="small fw-semibold text-danger flex-grow-1">বিকাশ (Send Money: {{ $ecomSetting['bkash_number'] ?? '01558712810' }})</span>
                                <span class="badge bg-danger rounded-pill">বিকাশ</span>
                            </label>

                            <label class="form-check p-2.5 border rounded-3 bg-light d-flex align-items-center gap-2 cursor-pointer mb-0">
                                <input class="form-check-input ms-0 me-2" type="radio" name="payment_method" value="nagad" onchange="toggleFullCartTrxInput('nagad')">
                                <span class="small fw-semibold text-warning text-dark flex-grow-1">নগদ (Send Money: {{ $ecomSetting['nagad_number'] ?? '01558712810' }})</span>
                                <span class="badge bg-warning text-dark rounded-pill">নগদ</span>
                            </label>
                        </div>

                        <!-- TrxID Input Box -->
                        <div id="fullCartTrxBox" class="d-none mt-2.5 p-3 bg-light-subtle rounded-3 border">
                            <label class="form-label text-dark small fw-semibold mb-1">ট্রানজাকশন আইডি (TrxID) ও প্রেরক নম্বর</label>
                            <input type="text" name="transaction_id" class="form-control rounded-3 mb-2" placeholder="যেমন: 9J3K8L2M">
                            <input type="text" name="payment_phone" class="form-control rounded-3" placeholder="যে নম্বর থেকে টাকা পাঠিয়েছেন">
                        </div>
                    </div>

                    <!-- Bill Summary Box -->
                    <div class="bg-light rounded-4 p-3.5 mb-4 border">
                        <div class="d-flex justify-content-between text-muted small mb-2">
                            <span>পণ্যের উপমোট (Subtotal):</span>
                            <span class="fw-bold text-dark" id="fullCartSubtotal">৳০</span>
                        </div>
                        <div class="d-flex justify-content-between text-muted small mb-2">
                            <span>ডেলিভারি ফি:</span>
                            <span class="fw-bold text-dark" id="fullCartDeliveryFee">৳{{ round($ecomSetting['delivery_outside'] ?? 120) }}</span>
                        </div>
                        <div class="d-flex justify-content-between pt-2 border-top">
                            <span class="fw-bold text-dark fs-6">সর্বমোট প্রদেয়:</span>
                            <span class="fw-bold text-primary fs-5" id="fullCartTotal">৳০</span>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" id="fullCartSubmitBtn" class="btn btn-primary btn-lg w-100 rounded-pill fw-bold shadow py-3 d-flex align-items-center justify-content-center gap-2">
                        <i class="fa-solid fa-circle-check fs-5"></i>
                        <span>অর্ডার নিশ্চিত করুন</span>
                    </button>
                </form>
            </div>
        </div>

    </div>

</div>

@push('scripts')
<script>
    (function() {
        const freeThreshold = {{ floatval($ecomSetting['free_delivery_threshold'] ?? 1500) }};

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
            renderFullCartPage();
        }

        window.renderFullCartPage = function() {
            const cart = getSafeCart();
            const emptyEl = document.getElementById('fullCartEmptyState');
            const gridEl = document.getElementById('fullCartContentGrid');
            const tableBody = document.getElementById('fullCartItemsTable');
            const countSummary = document.getElementById('fullCartCountSummary');

            if (!emptyEl || !gridEl) return;

            if (cart.length === 0) {
                emptyEl.classList.remove('d-none');
                gridEl.classList.add('d-none');
                return;
            }

            emptyEl.classList.add('d-none');
            gridEl.classList.remove('d-none');

            let totalQty = 0;
            let html = '';

            cart.forEach((item, index) => {
                const qty = item.quantity || item.qty || 1;
                totalQty += qty;
                const price = Number(item.price) || 0;
                const itemTotal = price * qty;
                const imgSrc = item.image || '/images/default-book.png';

                html += `
                    <tr>
                        <td class="ps-3.5 py-3">
                            <div class="d-flex align-items-center gap-3">
                                <img src="${imgSrc}" alt="${item.title}" class="rounded-2 object-fit-cover shadow-2xs" style="width: 52px; height: 72px; border: 1px solid #eee;" onerror="this.onerror=null; this.src='/images/default-book.png';">
                                <div>
                                    <h6 class="fw-bold text-dark mb-1" style="font-size: 0.92rem;">${item.title}</h6>
                                    <span class="badge bg-light text-secondary border">আইটেম #${index + 1}</span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="fw-bold text-dark">৳${price.toLocaleString('bn-BD')}</span>
                        </td>
                        <td class="text-center">
                            <div class="input-group input-group-sm border rounded-pill bg-light mx-auto overflow-hidden" style="width: 105px;">
                                <button class="btn btn-sm btn-light px-2.5 py-1" type="button" onclick="updateFullCartQty(${index}, -1)">
                                    <i class="fa-solid fa-minus small"></i>
                                </button>
                                <span class="form-control form-control-sm text-center border-0 bg-light p-0 fw-bold" style="line-height: 28px;">${qty}</span>
                                <button class="btn btn-sm btn-light px-2.5 py-1" type="button" onclick="updateFullCartQty(${index}, 1)">
                                    <i class="fa-solid fa-plus small"></i>
                                </button>
                            </div>
                        </td>
                        <td class="text-end">
                            <span class="fw-bold text-primary fs-6">৳${itemTotal.toLocaleString('bn-BD')}</span>
                        </td>
                        <td class="text-end pe-3.5">
                            <button type="button" class="btn btn-outline-danger btn-sm rounded-circle p-2" onclick="removeFullCartItem(${index})" title="মুছে ফেলুন">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });

            if (tableBody) tableBody.innerHTML = html;
            if (countSummary) countSummary.textContent = totalQty.toLocaleString('bn-BD') + ' টি পণ্য নির্বাচিত';

            updateFullCartCalculations();
        };

        window.updateFullCartQty = function(index, delta) {
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

        window.removeFullCartItem = function(index) {
            let cart = getSafeCart();
            cart.splice(index, 1);
            saveSafeCart(cart);
        };

        window.clearFullCart = function() {
            if (confirm('আপনি কি নিশ্চিত যে কার্টের সকল পণ্য মুছে ফেলতে চান?')) {
                saveSafeCart([]);
            }
        };

        window.updateFullCartCalculations = function() {
            const cart = getSafeCart();
            let subtotal = 0;
            cart.forEach(item => {
                const qty = item.quantity || item.qty || 1;
                const price = Number(item.price) || 0;
                subtotal += (price * qty);
            });

            const checkedArea = document.querySelector('input[name="area_radio"]:checked');
            let fee = checkedArea ? parseFloat(checkedArea.dataset.fee || 120) : 120;
            const districtHidden = document.getElementById('fullCartDistrictHidden');
            if (districtHidden && checkedArea) {
                districtHidden.value = checkedArea.value;
            }

            if (freeThreshold > 0 && subtotal >= freeThreshold) {
                fee = 0;
            }

            const total = subtotal + fee;

            const subDisplay = document.getElementById('fullCartSubtotal');
            const feeDisplay = document.getElementById('fullCartDeliveryFee');
            const totalDisplay = document.getElementById('fullCartTotal');
            const hiddenItems = document.getElementById('fullCartItemsHidden');

            if (subDisplay) subDisplay.textContent = '৳' + subtotal.toLocaleString('bn-BD');
            if (feeDisplay) feeDisplay.textContent = fee === 0 ? 'ফ্রি' : '৳' + fee.toLocaleString('bn-BD');
            if (totalDisplay) totalDisplay.textContent = '৳' + total.toLocaleString('bn-BD');
            if (hiddenItems) hiddenItems.value = JSON.stringify(cart);
        };

        window.toggleFullCartTrxInput = function(method) {
            const trxBox = document.getElementById('fullCartTrxBox');
            if (trxBox) {
                if (method === 'bkash' || method === 'nagad' || method === 'rocket') {
                    trxBox.classList.remove('d-none');
                } else {
                    trxBox.classList.add('d-none');
                }
            }
        };

        document.addEventListener('DOMContentLoaded', function() {
            renderFullCartPage();

            const form = document.getElementById('fullCartCheckoutForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const cart = getSafeCart();
                    if (cart.length === 0) {
                        e.preventDefault();
                        alert('আপনার কার্টে কোনো বই বা পণ্য নেই!');
                        return;
                    }
                    const hiddenItems = document.getElementById('fullCartItemsHidden');
                    if (hiddenItems) {
                        hiddenItems.value = JSON.stringify(cart);
                    }
                    const submitBtn = document.getElementById('fullCartSubmitBtn');
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin fs-5"></i> <span>অর্ডার প্রক্রিয়া সম্পন্ন হচ্ছে...</span>';
                    }
                });
            }
        });
    })();
</script>
@endpush
@endsection
