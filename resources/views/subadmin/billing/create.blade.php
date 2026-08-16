@extends('layouts.admin')

@section('title', 'নতুন বিল তৈরি')
@section('heading', 'নতুন বিল তৈরি করুন')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('subadmin.bills.index') }}" class="text-decoration-none">বিল তালিকা</a></li>
    <li class="breadcrumb-item active" aria-current="page">নতুন বিল</li>
@endsection

@section('actions')
    <a href="{{ route('subadmin.bills.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-list me-1"></i> সব বিল
    </a>
@endsection

@section('content')
<div style="max-width: 1080px;" class="mx-auto">

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible">
            <strong><i class="fas fa-circle-exclamation me-1"></i> অনুগ্রহ করে নিচের ত্রুটিগুলো সংশোধন করুন:</strong>
            <ul class="mb-0 ps-3 mt-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form method="POST" action="{{ route('subadmin.bills.store') }}" id="billForm">
        @csrf
        <div class="row g-4">

            {{-- ══ Customer Info Card ══ --}}
            <div class="col-12 col-md-6">
                <div class="adm-card h-100 p-3 p-md-4">
                    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                        <h6 class="fw-bold text-dark mb-0">
                            <i class="fas fa-user-circle text-primary me-2"></i>গ্রাহকের তথ্য
                        </h6>
                        <span class="badge bg-light text-muted border small">ক্রেতার বিবরণ</span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">গ্রাহকের নাম <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-user text-muted"></i></span>
                            <input type="text" name="customer_name" class="form-control" value="{{ old('customer_name') }}" required placeholder="উদা: মো: আনিসুর রহমান">
                        </div>
                    </div>

                    <div class="row g-2">
                        <div class="col-12 col-sm-6 mb-3">
                            <label class="form-label small fw-semibold">মোবাইল নম্বর</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-phone text-muted"></i></span>
                                <input type="tel" name="customer_phone" class="form-control" value="{{ old('customer_phone') }}" placeholder="01XXXXXXXXX">
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 mb-3">
                            <label class="form-label small fw-semibold">ইমেইল (ঐচ্ছিক)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-envelope text-muted"></i></span>
                                <input type="email" name="customer_email" class="form-control" value="{{ old('customer_email') }}" placeholder="customer@mail.com">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ══ Payment & Special Discount Card ══ --}}
            <div class="col-12 col-md-6">
                <div class="adm-card h-100 p-3 p-md-4">
                    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                        <h6 class="fw-bold text-dark mb-0">
                            <i class="fas fa-credit-card text-success me-2"></i>পেমেন্ট ও বিশেষ ছাড়
                        </h6>
                        <span class="badge bg-success-subtle text-success border small">মূল্য ও পরিশোধ</span>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">পেমেন্ট মেথড <span class="text-danger">*</span></label>
                            <select name="payment_method" class="form-select" required>
                                <option value="cash" @selected(old('payment_method','cash')==='cash')>💵 নগদ (Cash)</option>
                                <option value="bkash" @selected(old('payment_method')==='bkash')>📱 বিকাশ (bKash)</option>
                                <option value="nagad" @selected(old('payment_method')==='nagad')>📱 নগদ (Nagad)</option>
                                <option value="card" @selected(old('payment_method')==='card')>💳 কার্ড (Card)</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">পেমেন্ট স্ট্যাটাস <span class="text-danger">*</span></label>
                            <select name="payment_status" class="form-select" required>
                                <option value="paid" @selected(old('payment_status','paid')==='paid')>✅ পরিশোধিত (Paid)</option>
                                <option value="unpaid" @selected(old('payment_status')==='unpaid')>⏳ বকেয়া (Unpaid)</option>
                                <option value="partial" @selected(old('payment_status')==='partial')>⚠️ আংশিক (Partial)</option>
                            </select>
                        </div>
                    </div>

                    {{-- Special Overall Discount --}}
                    <div class="p-2.5 bg-light rounded-3 border mb-3">
                        <div class="d-flex align-items-center justify-content-between mb-1.5">
                            <label class="form-label small fw-bold text-dark mb-0">
                                <i class="fas fa-tags text-primary me-1"></i>মোটের ওপর বিশেষ ছাড়:
                            </label>
                            <div class="btn-group btn-group-sm" role="group">
                                <input type="radio" class="btn-check" name="special_discount_type" id="spec_type_percent" value="percent" checked onchange="recalc()">
                                <label class="btn btn-outline-primary py-0 px-2" for="spec_type_percent" style="font-size: 11.5px;">শতকরা (%)</label>

                                <input type="radio" class="btn-check" name="special_discount_type" id="spec_type_fixed" value="fixed" onchange="recalc()">
                                <label class="btn btn-outline-primary py-0 px-2" for="spec_type_fixed" style="font-size: 11.5px;">নির্দিষ্ট (৳)</label>
                            </div>
                        </div>

                        <div class="input-group input-group-sm mb-1.5">
                            <input type="number" step="0.5" min="0" id="specialDiscountInput" name="special_discount_value" 
                                   class="form-control fw-bold" value="{{ old('special_discount_value', 0) }}" placeholder="0" oninput="recalc()">
                            <span class="input-group-text bg-white fw-bold" id="specialDiscountUnit">%</span>
                        </div>

                        {{-- Quick Presets --}}
                        <div class="d-flex flex-wrap gap-1 align-items-center">
                            <span class="small text-muted me-1" style="font-size: 11px;">কুইক %:</span>
                            @foreach([5, 10, 15, 20, 25, 30, 40, 50] as $preset)
                                <button type="button" class="btn btn-outline-secondary btn-xs py-0 px-1.5" 
                                        style="font-size: 10.5px;" onclick="applySpecialDiscount({{ $preset }})">
                                    {{ $preset }}%
                                </button>
                            @endforeach
                            <button type="button" class="btn btn-outline-danger btn-xs py-0 px-1.5 ms-auto" 
                                    style="font-size: 10.5px;" onclick="applySpecialDiscount(0)">
                                ০%
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="form-label small fw-semibold">অতিরিক্ত নোট বা মন্তব্য (ঐচ্ছিক)</label>
                        <input type="text" name="notes" class="form-control form-control-sm" placeholder="যেমন: বিশেষ কাস্টমার ডিসকাউন্ট বা রেফারেন্স..." value="{{ old('notes') }}">
                    </div>
                </div>
            </div>

            {{-- ══ Book Items Table & Search ══ --}}
            <div class="col-12">
                <div class="adm-card p-3 p-md-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 pb-2 border-bottom">
                        <div>
                            <h6 class="fw-bold text-dark mb-0">
                                <i class="fas fa-book-open-reader text-primary me-2"></i>বইয়ের তালিকা ও বিক্রয় বিবরণ
                            </h6>
                            <span class="small text-muted">বইয়ের নাম লিখলেই শপ থেকে স্বয়ংক্রিয় সাজেশন ও মূল্য চলে আসবে</span>
                        </div>
                        <button type="button" class="btn btn-sm btn-primary px-3 shadow-xs" id="addItemBtn">
                            <i class="fas fa-plus-circle me-1"></i>আরেকটি বই যোগ করুন
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0" id="itemsTable">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 40px;" class="text-center">#</th>
                                    <th style="min-width: 260px;">বইয়ের নাম (বুকশপ থেকে খুঁজুন) <span class="text-danger">*</span></th>
                                    <th style="width: 100px;" class="text-center">পরিমাণ <span class="text-danger">*</span></th>
                                    <th style="width: 130px;" class="text-end">একক মূল্য (৳) <span class="text-danger">*</span></th>
                                    <th style="width: 120px;" class="text-center">বইয়ের ছাড় (%)</th>
                                    <th style="width: 130px;" class="text-end">মোট মূল্য (৳)</th>
                                    <th style="width: 50px;" class="text-center"></th>
                                </tr>
                            </thead>
                            <tbody id="itemsBody">
                                <tr class="item-row" data-index="0">
                                    <td class="text-center text-muted row-num fw-bold">১</td>
                                    <td class="position-relative">
                                        <input type="hidden" name="items[0][book_id]" class="book-id-input" value="">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-light"><i class="fas fa-search text-muted"></i></span>
                                            <input type="text" name="items[0][title]" class="form-control book-title-input" 
                                                   required placeholder="বইয়ের নাম বা ISBN লিখুন..." autocomplete="off"
                                                   oninput="handleBookSearch(this)" onfocus="handleBookSearch(this)">
                                        </div>
                                        <div class="book-autocomplete-dropdown d-none"></div>
                                        <div class="book-stock-indicator small mt-1"></div>
                                    </td>
                                    <td>
                                        <input type="number" name="items[0][qty]" class="form-control form-control-sm text-center qty-input fw-bold" 
                                               value="1" min="1" required oninput="recalc()">
                                    </td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-light">৳</span>
                                            <input type="number" name="items[0][price]" class="form-control text-end price-input fw-bold" 
                                                   value="0" min="0" step="0.01" required oninput="recalc()">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <input type="number" name="items[0][discount_pct]" class="form-control text-center discount-pct-input" 
                                                   value="0" min="0" max="100" step="0.5" placeholder="০" oninput="recalc()">
                                            <span class="input-group-text bg-light">%</span>
                                        </div>
                                        <div class="item-discount-label small text-muted text-center" style="font-size: 11px;"></div>
                                    </td>
                                    <td class="text-end">
                                        <div class="row-total fw-bold text-dark fs-6">৳0.00</div>
                                        <div class="row-raw-total small text-muted text-decoration-line-through d-none"></div>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-danger py-1 px-2 remove-row" title="মুছুন">
                                            <i class="fas fa-trash-can"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="5" class="text-end fw-semibold">বইগুলোর নিয়মিত সর্বমোট (Subtotal):</td>
                                    <td class="text-end fw-bold fs-6"><span id="subtotalDisplay">৳0.00</span></td>
                                    <td></td>
                                </tr>
                                <tr id="itemDiscountRow" class="d-none">
                                    <td colspan="5" class="text-end text-success fw-semibold">বইভিত্তিক মোট ছাড় (Items Discount):</td>
                                    <td class="text-end text-success fw-bold">-<span id="itemsDiscountDisplay">৳0.00</span></td>
                                    <td></td>
                                </tr>
                                <tr id="specialDiscountRow" class="d-none">
                                    <td colspan="5" class="text-end text-primary fw-semibold">মোটের ওপর বিশেষ ছাড় (Special Discount):</td>
                                    <td class="text-end text-primary fw-bold">-<span id="specialDiscountDisplay">৳0.00</span></td>
                                    <td></td>
                                </tr>
                                <tr class="table-active">
                                    <td colspan="5" class="text-end fw-bold text-dark fs-5">
                                        সর্বমোট প্রদেয় বিল (Grand Total):
                                    </td>
                                    <td class="text-end fw-bold text-success fs-5">
                                        <span id="grandTotalDisplay">৳0.00</span>
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- ══ Actions Footer ══ --}}
        <div class="mt-4 p-3 adm-card d-flex flex-wrap gap-2 justify-content-between align-items-center">
            <a href="{{ route('subadmin.bills.index') }}" class="btn btn-outline-secondary px-4">
                <i class="fas fa-arrow-left me-1"></i> ফিরে যান
            </a>
            <div class="d-flex gap-2">
                <button type="reset" class="btn btn-light border px-4" onclick="setTimeout(recalc, 50)">রিসেট</button>
                <button type="submit" class="btn btn-primary px-5 fw-bold shadow-xs">
                    <i class="fas fa-file-invoice-dollar me-1"></i> বিল সম্পন্ন ও তৈরি করুন
                </button>
            </div>
        </div>
    </form>
</div>

<style>
.book-autocomplete-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    z-index: 1050;
    max-height: 240px;
    overflow-y: auto;
    background: #ffffff;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    box-shadow: 0 10px 25px -5px rgba(0,0,0,0.15);
}
.book-autocomplete-item {
    padding: 8px 12px;
    cursor: pointer;
    border-bottom: 1px solid #f1f5f9;
    display: flex;
    align-items: center;
    gap: 10px;
    transition: background 0.15s ease;
}
.book-autocomplete-item:last-child {
    border-bottom: none;
}
.book-autocomplete-item:hover, .book-autocomplete-item.active {
    background-color: #f8fafc;
}
</style>

<script>
let rowIndex = 1;
const searchUrl = "{{ route('subadmin.books.search') }}";

function handleBookSearch(input) {
    const row = input.closest('.item-row');
    const dropdown = row.querySelector('.book-autocomplete-dropdown');
    const query = input.value.trim();

    if (query.length < 1) {
        dropdown.classList.add('d-none');
        dropdown.innerHTML = '';
        return;
    }

    fetch(`${searchUrl}?q=${encodeURIComponent(query)}`)
        .then(res => res.json())
        .then(books => {
            if (!books || books.length === 0) {
                dropdown.innerHTML = '<div class="p-2.5 text-muted small text-center"><i class="fas fa-info-circle me-1"></i>শপে কোনো বই পাওয়া যায়নি (কাস্টম নাম হিসেবে ব্যবহার হবে)</div>';
                dropdown.classList.remove('d-none');
                return;
            }

            let html = '';
            books.forEach(b => {
                const stockBadge = b.stock_quantity > 0 
                    ? `<span class="badge bg-success-subtle text-success border small">স্টক: ${b.stock_quantity.toLocaleString('bn-BD')} টি</span>`
                    : `<span class="badge bg-danger-subtle text-danger border small">স্টক আউট</span>`;
                
                const priceBadge = b.discount_price && b.discount_price < b.regular_price
                    ? `<span class="fw-bold text-success">৳${b.discount_price.toLocaleString('bn-BD')}</span> <span class="text-muted text-decoration-line-through small">৳${b.regular_price.toLocaleString('bn-BD')}</span>`
                    : `<span class="fw-bold text-dark">৳${b.regular_price.toLocaleString('bn-BD')}</span>`;

                html += `
                    <div class="book-autocomplete-item" onclick="selectBook(this, ${JSON.stringify(b).replace(/"/g, '&quot;')})">
                        ${b.cover_image ? `<img src="${b.cover_image}" style="width:28px;height:38px;object-fit:cover;" class="rounded border">` : `<div class="bg-light rounded d-flex align-items-center justify-content-center text-muted" style="width:28px;height:38px;"><i class="fas fa-book small"></i></div>`}
                        <div class="overflow-hidden me-auto">
                            <div class="small fw-bold text-dark text-truncate" style="max-width:240px;">${b.title}</div>
                            <div class="small text-muted">${stockBadge}</div>
                        </div>
                        <div class="text-end small">${priceBadge}</div>
                    </div>`;
            });

            dropdown.innerHTML = html;
            dropdown.classList.remove('d-none');
        })
        .catch(() => {
            dropdown.classList.add('d-none');
        });
}

function selectBook(itemEl, book) {
    const row = itemEl.closest('.item-row');
    const titleInput = row.querySelector('.book-title-input');
    const idInput = row.querySelector('.book-id-input');
    const priceInput = row.querySelector('.price-input');
    const discInput = row.querySelector('.discount-pct-input');
    const stockIndicator = row.querySelector('.book-stock-indicator');
    const dropdown = row.querySelector('.book-autocomplete-dropdown');

    titleInput.value = book.title;
    idInput.value = book.id;
    priceInput.value = book.regular_price || book.selling_price;
    discInput.value = book.discount_pct || 0;

    if (book.stock_quantity > 0) {
        stockIndicator.innerHTML = `<span class="text-success small fw-semibold"><i class="fas fa-check-circle me-1"></i>শপে মজুত আছে: ${book.stock_quantity.toLocaleString('bn-BD')} টি</span>`;
    } else {
        stockIndicator.innerHTML = `<span class="text-danger small fw-semibold"><i class="fas fa-triangle-exclamation me-1"></i>শপে স্টক শেষ</span>`;
    }

    dropdown.classList.add('d-none');
    recalc();
}

// Close dropdowns on outside click
document.addEventListener('click', function (e) {
    if (!e.target.closest('.position-relative')) {
        document.querySelectorAll('.book-autocomplete-dropdown').forEach(d => d.classList.add('d-none'));
    }
});

function applySpecialDiscount(pct) {
    const typePercent = document.getElementById('spec_type_percent');
    const input = document.getElementById('specialDiscountInput');
    if (typePercent) typePercent.checked = true;
    if (input) {
        input.value = pct;
        recalc();
    }
}

function recalc() {
    let rawSubtotal = 0;
    let itemsDiscountTotal = 0;
    let itemsNetTotal = 0;

    // Check special discount type
    const isPercent = document.getElementById('spec_type_percent')?.checked;
    const unitEl = document.getElementById('specialDiscountUnit');
    if (unitEl) unitEl.textContent = isPercent ? '%' : '৳';

    document.querySelectorAll('.item-row').forEach((row, i) => {
        row.querySelector('.row-num').textContent = (i + 1).toLocaleString('bn-BD');
        const q = Math.max(1, parseFloat(row.querySelector('.qty-input').value) || 0);
        const p = Math.max(0, parseFloat(row.querySelector('.price-input').value) || 0);
        const dPct = Math.max(0, Math.min(100, parseFloat(row.querySelector('.discount-pct-input').value) || 0));

        const lineRaw = q * p;
        const lineDisc = lineRaw * (dPct / 100);
        const lineNet = lineRaw - lineDisc;

        const rowTotalEl = row.querySelector('.row-total');
        const rowRawEl = row.querySelector('.row-raw-total');
        const itemDiscLabel = row.querySelector('.item-discount-label');

        rowTotalEl.textContent = '৳' + lineNet.toLocaleString('bn-BD', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        if (dPct > 0) {
            rowRawEl.textContent = '৳' + lineRaw.toLocaleString('bn-BD', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            rowRawEl.classList.remove('d-none');
            itemDiscLabel.textContent = `ছাড়: -৳${lineDisc.toLocaleString('bn-BD', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
        } else {
            rowRawEl.classList.add('d-none');
            itemDiscLabel.textContent = '';
        }

        rawSubtotal += lineRaw;
        itemsDiscountTotal += lineDisc;
        itemsNetTotal += lineNet;
    });

    // Special discount
    const specVal = Math.max(0, parseFloat(document.getElementById('specialDiscountInput')?.value) || 0);
    let specialDiscountAmount = 0;

    if (isPercent) {
        specialDiscountAmount = itemsNetTotal * (Math.min(100, specVal) / 100);
    } else {
        specialDiscountAmount = Math.min(itemsNetTotal, specVal);
    }

    const totalDiscount = itemsDiscountTotal + specialDiscountAmount;
    const grandTotal = Math.max(0, rawSubtotal - totalDiscount);

    document.getElementById('subtotalDisplay').textContent = '৳' + rawSubtotal.toLocaleString('bn-BD', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    const itemDiscRow = document.getElementById('itemDiscountRow');
    if (itemsDiscountTotal > 0) {
        itemDiscRow.classList.remove('d-none');
        document.getElementById('itemsDiscountDisplay').textContent = '৳' + itemsDiscountTotal.toLocaleString('bn-BD', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    } else {
        itemDiscRow.classList.add('d-none');
    }

    const specDiscRow = document.getElementById('specialDiscountRow');
    if (specialDiscountAmount > 0) {
        specDiscRow.classList.remove('d-none');
        document.getElementById('specialDiscountDisplay').textContent = '৳' + specialDiscountAmount.toLocaleString('bn-BD', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    } else {
        specDiscRow.classList.add('d-none');
    }

    document.getElementById('grandTotalDisplay').textContent = '৳' + grandTotal.toLocaleString('bn-BD', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

document.getElementById('addItemBtn').addEventListener('click', () => {
    const tbody = document.getElementById('itemsBody');
    const tr = document.createElement('tr');
    tr.className = 'item-row';
    tr.dataset.index = rowIndex;
    tr.innerHTML = `
        <td class="text-center text-muted row-num fw-bold"></td>
        <td class="position-relative">
            <input type="hidden" name="items[${rowIndex}][book_id]" class="book-id-input" value="">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light"><i class="fas fa-search text-muted"></i></span>
                <input type="text" name="items[${rowIndex}][title]" class="form-control book-title-input" 
                       required placeholder="বইয়ের নাম বা ISBN লিখুন..." autocomplete="off"
                       oninput="handleBookSearch(this)" onfocus="handleBookSearch(this)">
            </div>
            <div class="book-autocomplete-dropdown d-none"></div>
            <div class="book-stock-indicator small mt-1"></div>
        </td>
        <td>
            <input type="number" name="items[${rowIndex}][qty]" class="form-control form-control-sm text-center qty-input fw-bold" 
                   value="1" min="1" required oninput="recalc()">
        </td>
        <td>
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light">৳</span>
                <input type="number" name="items[${rowIndex}][price]" class="form-control text-end price-input fw-bold" 
                       value="0" min="0" step="0.01" required oninput="recalc()">
            </div>
        </td>
        <td>
            <div class="input-group input-group-sm">
                <input type="number" name="items[${rowIndex}][discount_pct]" class="form-control text-center discount-pct-input" 
                       value="0" min="0" max="100" step="0.5" placeholder="০" oninput="recalc()">
                <span class="input-group-text bg-light">%</span>
            </div>
            <div class="item-discount-label small text-muted text-center" style="font-size: 11px;"></div>
        </td>
        <td class="text-end">
            <div class="row-total fw-bold text-dark fs-6">৳0.00</div>
            <div class="row-raw-total small text-muted text-decoration-line-through d-none"></div>
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-outline-danger py-1 px-2 remove-row" title="মুছুন">
                <i class="fas fa-trash-can"></i>
            </button>
        </td>`;
    tbody.appendChild(tr);

    tr.querySelector('.remove-row').addEventListener('click', () => {
        if (document.querySelectorAll('.item-row').length > 1) {
            tr.remove();
            recalc();
        } else {
            alert('কমপক্ষে একটি বইয়ের সারি থাকতে হবে।');
        }
    });

    rowIndex++;
    recalc();
});

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.remove-row').forEach(btn => {
        btn.addEventListener('click', function () {
            const row = this.closest('.item-row');
            if (document.querySelectorAll('.item-row').length > 1) {
                row.remove();
                recalc();
            } else {
                alert('কমপক্ষে একটি বইয়ের সারি থাকতে হবে।');
            }
        });
    });
    recalc();
});
</script>
@endsection
