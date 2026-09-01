@extends('layouts.admin')

@section('title', 'Boi Mela Stall POS Terminal')
@section('heading', 'Amar Ekushey Boi Mela & Stall POS')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Boi Mela POS</li>
@endsection

@section('actions')
    <div class="d-flex align-items-center gap-2">
        <span class="badge bg-success-subtle text-success border border-success px-3 py-2 rounded-pill font-monospace fw-bold">
            <i class="fas fa-circle me-1 animate-pulse"></i> {{ $activeRegister->name ?? 'Stall Register' }}
        </span>
    </div>
@endsection

@section('content')
<div class="row g-3">

    <!-- Left Column: Fast Product Search & Grid -->
    <div class="col-12 col-xl-7">
        <div class="adm-card bg-white h-100 d-flex flex-column">
            <div class="adm-card__head">
                <div class="input-group input-group-lg">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-barcode fs-4 text-primary"></i></span>
                    <input type="text" id="posSearchInput" class="form-control border-start-0 ps-0" placeholder="Scan Barcode / SKU or type book title..." autofocus oninput="handleSearch(this.value)">
                </div>
            </div>
            <div class="adm-card__body p-3 flex-grow-1 overflow-auto" style="max-height: 580px;">
                <div class="row g-2.5" id="posBookGrid">
                    @foreach($books as $b)
                        <div class="col-6 col-sm-4 col-md-3">
                            <div class="p-2.5 border rounded-3 bg-light h-100 cursor-pointer text-center book-pos-card"
                                 onclick="addToCart({{ $b->id }}, '{{ addslashes($b->title) }}', {{ $b->discount_price ?? $b->price }}, {{ $b->stock_quantity }})">
                                <div class="fw-bold small text-dark text-truncate mb-1" title="{{ $b->title }}">{{ $b->title }}</div>
                                <div class="fw-bold text-primary mb-1">৳{{ number_format($b->discount_price ?? $b->price, 0) }}</div>
                                <span class="badge {{ $b->stock_quantity > 5 ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }} rounded-pill small" style="font-size: 10px;">
                                    Stock: {{ $b->stock_quantity }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Live Register Cart & Instant Billing -->
    <div class="col-12 col-xl-5">
        <div class="adm-card bg-white h-100 d-flex flex-column border-top border-4 border-primary">
            <div class="adm-card__head d-flex justify-content-between align-items-center py-2.5">
                <h6 class="mb-0 fw-bold"><i class="fas fa-receipt me-1.5 text-primary"></i> Current Stall Bill</h6>
                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2.5 py-0.5" onclick="clearCart()">
                    <i class="fas fa-trash me-1"></i> Clear
                </button>
            </div>
            
            <!-- Cart Items List -->
            <div class="adm-card__body p-0 flex-grow-1 overflow-auto" style="max-height: 320px;">
                <table class="table table-sm adm-table align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-3">Book</th>
                            <th style="width: 80px;">Qty</th>
                            <th>Total</th>
                            <th class="text-end pe-3"></th>
                        </tr>
                    </thead>
                    <tbody id="cartTableBody">
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted small">Cart is empty. Scan barcode to add books.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pricing Summary & Checkout -->
            <div class="p-3 bg-light border-top">
                <div class="d-flex justify-content-between small text-muted mb-1">
                    <span>Subtotal:</span>
                    <span class="fw-bold text-dark font-monospace" id="lblSubtotal">৳0.00</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="small text-muted">Stall Discount (৳):</span>
                    <input type="number" id="txtDiscount" value="0" min="0" class="form-control form-control-sm text-end font-monospace" style="width: 100px;" oninput="calculateTotal()">
                </div>
                <div class="d-flex justify-content-between fs-5 fw-bold text-dark border-top pt-2 mb-3">
                    <span>Payable Total:</span>
                    <span class="text-primary font-monospace" id="lblTotal">৳0.00</span>
                </div>

                <!-- Payment Methods -->
                <div class="mb-3">
                    <label class="form-label small fw-semibold text-muted mb-1">Payment Method</label>
                    <div class="btn-group w-100" role="group">
                        <input type="radio" class="btn-check" name="posPayMethod" id="payCash" value="cash" checked>
                        <label class="btn btn-outline-secondary btn-sm" for="payCash"><i class="fas fa-money-bill-wave me-1"></i> Cash</label>

                        <input type="radio" class="btn-check" name="posPayMethod" id="payBkash" value="bkash">
                        <label class="btn btn-outline-danger btn-sm" for="payBkash"><i class="fas fa-mobile-screen me-1"></i> bKash</label>

                        <input type="radio" class="btn-check" name="posPayMethod" id="payCard" value="card">
                        <label class="btn btn-outline-primary btn-sm" for="payCard"><i class="fas fa-credit-card me-1"></i> Card</label>
                    </div>
                </div>

                <!-- Complete Sale Button -->
                <button type="button" id="btnCompleteSale" class="btn btn-primary btn-lg w-100 rounded-pill fw-bold shadow-sm" onclick="handleCheckout()">
                    <i class="fas fa-check-circle me-1.5"></i> Complete Sale & Print Bill
                </button>
            </div>
        </div>
    </div>

</div>

<!-- Recent POS Sales Table -->
<div class="adm-card bg-white mt-4">
    <div class="adm-card__head d-flex justify-content-between align-items-center">
        <div>
            <h6 class="mb-0 fw-bold"><i class="fas fa-clock-rotate-left me-2 text-primary"></i> Today's Stall Transactions</h6>
            <small class="text-muted">Total: <strong>৳{{ number_format($todayTotalSales, 2) }}</strong> (Cash: ৳{{ number_format($todayCash, 2) }} | Online: ৳{{ number_format($todayOnline, 2) }})</small>
        </div>
    </div>
    <div class="adm-card__body p-0">
        <div class="table-responsive">
            <table class="table adm-table align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Receipt #</th>
                        <th>Time</th>
                        <th>Cashier</th>
                        <th>Method</th>
                        <th>Total Amount</th>
                        <th class="text-end pe-3">Print</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentSales as $sale)
                        <tr>
                            <td class="ps-3 fw-bold text-primary font-monospace">#{{ $sale->receipt_no }}</td>
                            <td class="small">{{ $sale->created_at->format('h:i A') }}</td>
                            <td class="small">{{ $sale->cashier->name ?? 'Stall Staff' }}</td>
                            <td><span class="badge bg-light text-dark border text-uppercase">{{ $sale->payment_method }}</span></td>
                            <td class="fw-bold text-dark">৳{{ number_format($sale->total, 2) }}</td>
                            <td class="text-end pe-3">
                                <a href="{{ route('admin.pos.receipt', $sale->id) }}" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5 py-0.5">
                                    <i class="fas fa-print"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-3 text-muted small">No transactions today yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
let cart = [];

function addToCart(id, title, price, maxStock) {
    const existing = cart.find(item => item.id === id);
    if (existing) {
        if (existing.qty < maxStock) {
            existing.qty++;
        } else {
            SwalToast('warning', 'স্টকে পর্যাপ্ত বই নেই!');
        }
    } else {
        cart.push({ id, title, price, qty: 1, maxStock });
    }
    renderCart();
}

function updateQty(id, delta) {
    const item = cart.find(i => i.id === id);
    if (item) {
        item.qty += delta;
        if (item.qty <= 0) {
            cart = cart.filter(i => i.id !== id);
        }
    }
    renderCart();
}

function removeItem(id) {
    cart = cart.filter(i => i.id !== id);
    renderCart();
}

function clearCart() {
    cart = [];
    renderCart();
}

function renderCart() {
    const tbody = document.getElementById('cartTableBody');
    if (cart.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" class="text-center py-4 text-muted small">Cart is empty. Scan barcode to add books.</td></tr>';
        document.getElementById('lblSubtotal').textContent = '৳0.00';
        document.getElementById('lblTotal').textContent = '৳0.00';
        return;
    }

    let html = '';
    let subtotal = 0;

    cart.forEach(item => {
        const itemTotal = item.price * item.qty;
        subtotal += itemTotal;
        html += `
            <tr>
                <td class="ps-3">
                    <div class="fw-semibold text-dark text-truncate small" style="max-width: 150px;">${item.title}</div>
                    <small class="text-muted">৳${item.price.toFixed(0)} each</small>
                </td>
                <td>
                    <div class="d-flex align-items-center gap-1">
                        <button type="button" class="btn btn-sm btn-light p-0 text-center" style="width: 20px; height: 20px;" onclick="updateQty(${item.id}, -1)">-</button>
                        <span class="fw-bold small font-monospace">${item.qty}</span>
                        <button type="button" class="btn btn-sm btn-light p-0 text-center" style="width: 20px; height: 20px;" onclick="updateQty(${item.id}, 1)">+</button>
                    </div>
                </td>
                <td class="fw-bold text-dark font-monospace">৳${itemTotal.toFixed(0)}</td>
                <td class="text-end pe-3">
                    <button type="button" class="btn btn-sm text-danger p-0" onclick="removeItem(${item.id})"><i class="fas fa-times"></i></button>
                </td>
            </tr>
        `;
    });

    tbody.innerHTML = html;
    calculateTotal();
}

function calculateTotal() {
    let subtotal = cart.reduce((acc, item) => acc + (item.price * item.qty), 0);
    let discount = parseFloat(document.getElementById('txtDiscount').value) || 0;
    let total = Math.max(0, subtotal - discount);

    document.getElementById('lblSubtotal').textContent = '৳' + subtotal.toFixed(2);
    document.getElementById('lblTotal').textContent = '৳' + total.toFixed(2);
}

function handleCheckout() {
    if (cart.length === 0) {
        SwalToast('error', 'অনুগ্রহ করে প্রথমে বই সিলেক্ট করুন!');
        return;
    }

    const subtotal = cart.reduce((acc, item) => acc + (item.price * item.qty), 0);
    const discount = parseFloat(document.getElementById('txtDiscount').value) || 0;
    const total = Math.max(0, subtotal - discount);
    const method = document.querySelector('input[name="posPayMethod"]:checked').value;

    const btn = document.getElementById('btnCompleteSale');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Processing...';

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    fetch("{{ route('admin.pos.checkout') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({
            items: cart,
            subtotal: subtotal,
            discount: discount,
            total: total,
            payment_method: method,
            paid_cash: method === 'cash' ? total : 0,
            paid_online: method !== 'cash' ? total : 0,
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            SwalToast('success', data.message);
            window.open('/admin/pos/receipt/' + data.sale_id, '_blank', 'width=400,height=600');
            clearCart();
            setTimeout(() => location.reload(), 1500);
        } else {
            SwalToast('error', data.message || 'Error processing bill');
        }
    })
    .catch(() => SwalToast('error', 'Server error occurred'))
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check-circle me-1.5"></i> Complete Sale & Print Bill';
    });
}
</script>
@endpush
@endsection
