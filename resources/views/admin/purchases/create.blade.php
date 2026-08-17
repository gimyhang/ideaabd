@extends('layouts.admin')

@section('title', 'নতুন প্রকাশনী ক্রয় এন্ট্রি')
@section('heading', 'প্রকাশনী থেকে নতুন ক্রয় ও স্টক এন্ট্রি')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.purchases.index') }}">প্রকাশনী ক্রয়</a></li>
    <li class="breadcrumb-item active" aria-current="page">নতুন এন্ট্রি</li>
@endsection

@section('actions')
    <a href="{{ route('admin.purchases.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> তালিকায় ফিরুন
    </a>
@endsection

@section('content')

<form action="{{ route('admin.purchases.store') }}" method="POST" id="purchaseForm">
    @csrf

    <div class="row g-4">
        {{-- Left: Invoice & Items Form --}}
        <div class="col-12 col-xl-8">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="fw-bold mb-0 text-primary"><i class="fas fa-building me-2"></i>প্রকাশনী ও ইনভয়েস তথ্য</h5>
                </div>
                <div class="card-body p-3 p-md-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fw-semibold mb-0">প্রকাশনী / সরবরাহকারী <span class="text-danger">*</span></label>
                                <button type="button" class="btn btn-link btn-sm text-primary text-decoration-none p-0 fw-semibold" id="togglePubBtn" onclick="togglePublisherMode()">
                                    <i class="fas fa-plus-circle me-1"></i>+ নতুন প্রকাশনী
                                </button>
                            </div>

                            {{-- Existing Publisher Select --}}
                            <div id="existingPublisherWrapper">
                                <select name="publisher_id" id="publisherSelect" class="form-select @error('publisher_id') is-invalid @enderror">
                                    <option value="">তালিকা থেকে প্রকাশনী বেছে নিন</option>
                                    @foreach($publishers as $pub)
                                        <option value="{{ $pub->id }}" @selected(old('publisher_id') == $pub->id)>
                                            {{ $pub->name }} ({{ $pub->phone ?? 'ফোন নেই' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- New Publisher Input Fields --}}
                            <div id="newPublisherWrapper" style="display: none;">
                                <div class="input-group mb-2">
                                    <span class="input-group-text bg-light"><i class="fas fa-building text-primary"></i></span>
                                    <input type="text" name="publisher_name" id="newPublisherName" class="form-control" placeholder="নতুন প্রকাশনীর নাম লিখুন...">
                                </div>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <input type="text" name="publisher_phone" class="form-control form-control-sm" placeholder="ফোন নম্বর (ঐচ্ছিক)">
                                    </div>
                                    <div class="col-6">
                                        <input type="text" name="publisher_address" class="form-control form-control-sm" placeholder="ঠিকানা (ঐচ্ছিক)">
                                    </div>
                                </div>
                                <small class="text-success d-block mt-1"><i class="fas fa-check-circle me-1"></i>নতুন নাম লিখলে স্বয়ংক্রিয়ভাবে প্রকাশনী ডিরেক্টরিতে যুক্ত হবে।</small>
                            </div>
                            @error('publisher_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold">ক্রয় চালান # <span class="text-danger">*</span></label>
                            <input type="text" name="purchase_no" class="form-control @error('purchase_no') is-invalid @enderror" 
                                   value="{{ old('purchase_no', $suggestedInvoiceNo) }}" required>
                            @error('purchase_no')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold">ক্রয়ের তারিখ <span class="text-danger">*</span></label>
                            <input type="date" name="purchase_date" class="form-control" value="{{ old('purchase_date', date('Y-m-d')) }}" required>
                        </div>

                        {{-- Publisher Memo No --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-dark">
                                <i class="fas fa-receipt me-1 text-primary"></i>প্রকাশকের নিজস্ব মেমো / চালান নং
                            </label>
                            <input type="text" name="publisher_memo_no" class="form-control" 
                                   placeholder="যেমন: মেমো নং ১২৫৭ বা চালান নং..." value="{{ old('publisher_memo_no') }}">
                            <small class="text-muted">প্রকাশনীর পক্ষ থেকে প্রাপ্ত মেমো/চালান নম্বরটি এখানে সংরক্ষণ করুন।</small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Purchase Items Card --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div>
                        <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-book-medical me-2 text-success"></i>ক্রয়কৃত বই ও কমিশন হিসাব</h5>
                        <small class="text-success"><i class="fas fa-check-circle me-1"></i>বইয়ের মূল মূল্য ও কমিশন দিলে ক্রয়মূল্য এবং বুকশপে ছাড় দিলে বিক্রয়মূল্য স্বয়ংক্রিয়ভাবে হিসাব হবে।</small>
                    </div>
                    <button type="button" class="btn btn-sm btn-success rounded-pill px-3 fw-semibold" onclick="addItemRow()">
                        <i class="fas fa-plus me-1"></i> আরো বই যোগ করুন
                    </button>
                </div>
                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle" id="itemsTable">
                            <thead class="table-light">
                                <tr class="small text-muted text-uppercase text-center">
                                    <th style="min-width: 180px;" class="text-start">বইয়ের নাম <span class="text-danger">*</span></th>
                                    <th style="min-width: 130px;" class="text-start">লেখক</th>
                                    <th style="min-width: 130px;" class="text-start">ক্যাটাগরি</th>
                                    <th style="width: 75px;">পরিমাণ</th>
                                    <th style="width: 100px;">বইয়ের মূল্য (MRP ৳)</th>
                                    <th style="width: 80px;">ক্রয় কমিশন %</th>
                                    <th style="width: 100px;">ক্রয়মূল্য (৳)</th>
                                    <th style="width: 80px;">শপ ছাড় %</th>
                                    <th style="width: 100px;">বিক্রয়মূল্য (৳)</th>
                                    <th style="width: 105px;">মোট ক্রয় (৳)</th>
                                    <th style="width: 40px;"></th>
                                </tr>
                            </thead>
                            <tbody id="itemsBody">
                                {{-- Initial Row --}}
                                <tr class="item-row" data-row="0">
                                    <td>
                                        <input type="text" name="items[0][title]" class="form-control form-control-sm item-title" 
                                               list="booksList" placeholder="বইয়ের নাম..." required oninput="onTitleInput(this, 0)">
                                        <input type="hidden" name="items[0][book_id]" class="item-book-id" value="">
                                    </td>
                                    <td>
                                        <input type="text" name="items[0][author]" class="form-control form-control-sm item-author" 
                                               list="authorsList" placeholder="লেখকের নাম...">
                                    </td>
                                    <td>
                                        <input type="text" name="items[0][category_name]" class="form-control form-control-sm item-category" 
                                               list="categoriesList" placeholder="ক্যাটাগরি...">
                                        <input type="hidden" name="items[0][category_id]" class="item-category-id" value="">
                                    </td>
                                    <td>
                                        <input type="number" name="items[0][quantity]" class="form-control form-control-sm item-qty text-center" 
                                               value="1" min="1" required oninput="onQtyChange(0)">
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" name="items[0][mrp_price]" class="form-control form-control-sm item-mrp text-end" 
                                               value="0" min="0" placeholder="MRP" oninput="onMrpChange(0)">
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" name="items[0][purchase_commission_percent]" class="form-control form-control-sm item-comm text-center" 
                                               value="0" min="0" max="100" placeholder="%" oninput="onCommChange(0)">
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" name="items[0][cost_price]" class="form-control form-control-sm item-cost text-end fw-bold text-danger" 
                                               value="0" min="0" required oninput="onCostChange(0)">
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" name="items[0][shop_discount_percent]" class="form-control form-control-sm item-shop-disc text-center" 
                                               value="0" min="0" max="100" placeholder="%" oninput="onShopDiscChange(0)">
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" name="items[0][sale_price]" class="form-control form-control-sm item-sale text-end fw-bold text-success" 
                                               value="0" min="0" required oninput="onSaleChange(0)">
                                    </td>
                                    <td class="text-end fw-bold text-dark item-subtotal">৳0.00</td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-danger p-1 border-0" onclick="removeRow(this)">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    {{-- Datalists --}}
                    <datalist id="authorsList">
                        @foreach($authors as $a)
                            <option value="{{ $a->name }}"></option>
                        @endforeach
                    </datalist>

                    <datalist id="categoriesList">
                        @foreach($categories as $cat)
                            <option value="{{ $cat->name }}" data-id="{{ $cat->id }}"></option>
                        @endforeach
                    </datalist>

                    <datalist id="booksList">
                        @foreach($books as $b)
                            <option value="{{ $b->title }}" 
                                    data-id="{{ $b->id }}"
                                    data-author="{{ $b->author_name }}"
                                    data-category-id="{{ $b->category_id }}"
                                    data-category-name="{{ $b->category?->name ?? '' }}"
                                    data-price="{{ $b->price }}">
                                (স্টক: {{ $b->stock_quantity }} | মূল্য: ৳{{ $b->price }})
                            </option>
                        @endforeach
                    </datalist>

                    <div class="mt-2">
                        <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3" onclick="addItemRow()">
                            <i class="fas fa-plus me-1"></i> আরো বইয়ের সারি যোগ করুন
                        </button>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-3">
                    <label class="form-label fw-semibold">মন্তব্য বা নোট (ঐচ্ছিক)</label>
                    <textarea name="notes" rows="2" class="form-control rounded-3" placeholder="ক্রয় সংক্রান্ত কোনো বিশেষ শর্ত বা মেমো থাকলে লিখুন..."></textarea>
                </div>
            </div>
        </div>

        {{-- Right: Payment & Calculation Summary --}}
        <div class="col-12 col-xl-4">
            <div class="card border-0 shadow-sm rounded-4 sticky-top" style="top: 80px;">
                <div class="card-header bg-primary text-white py-3 rounded-top-4">
                    <h5 class="fw-bold mb-0"><i class="fas fa-calculator me-2"></i>হিসাব ও পেমেন্ট</h5>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted">মোট ক্রয়মূল্য:</span>
                        <span class="fw-bold fs-5 text-dark" id="displayTotal">৳0.00</span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">ছাড় / অতিরিক্ত কমিশন (Discount ৳):</label>
                        <input type="number" step="0.01" name="discount_amount" id="discountInput" class="form-control text-end" value="0" min="0" oninput="calcTotals()">
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="fw-bold">সর্বমোট প্রদেয় (Grand Total):</span>
                        <span class="fw-bold fs-4 text-primary" id="displayGrandTotal">৳0.00</span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">ক্রয়ের ধরন <span class="text-danger">*</span></label>
                        <select name="payment_type" id="paymentType" class="form-select" required onchange="onPaymentTypeChange()">
                            <option value="cash">নগদে ক্রয় (সম্পূর্ণ পরিশোধ / Cash)</option>
                            <option value="credit">বাকিতে ক্রয় (সম্পূর্ণ বকেয়া / Due)</option>
                            <option value="partial">আংশিক নগদ ও আংশিক বাকি (Partial)</option>
                        </select>
                    </div>

                    <div class="mb-3" id="paidAmountGroup">
                        <label class="form-label fw-semibold">বর্তমান পরিশোধের পরিমাণ (৳):</label>
                        <input type="number" step="0.01" name="paid_amount" id="paidAmountInput" class="form-control text-end" value="0" min="0" oninput="calcTotals()">
                    </div>

                    <div class="mb-3" id="paymentMethodGroup">
                        <label class="form-label small fw-semibold text-muted">পেমেন্ট মাধ্যম:</label>
                        <select name="payment_method" class="form-select">
                            <option value="cash">ক্যাশ / নগদ (Cash)</option>
                            <option value="bank">ব্যাংক একাউন্ট (Bank Transfer)</option>
                            <option value="bkash">বিকাশ (bKash)</option>
                            <option value="nagad">নগদ (Nagad)</option>
                            <option value="cheque">চেক (Cheque)</option>
                        </select>
                    </div>

                    <div class="mb-3" id="trxRefGroup">
                        <label class="form-label small fw-semibold text-muted">ট্রানজেকশন আইডি / রেফারেন্স:</label>
                        <input type="text" name="transaction_ref" class="form-control" placeholder="ঐচ্ছিক (চেক নং / Trx ID)">
                    </div>

                    <div class="alert alert-danger p-3 rounded-3 mb-4 d-flex justify-content-between align-items-center" id="dueAlert">
                        <span class="fw-semibold">অবশিষ্ট বকেয়া (Due):</span>
                        <span class="fw-bold fs-5 text-danger" id="displayDue">৳0.00</span>
                    </div>

                    <button type="submit" class="btn btn-success w-100 py-3 rounded-pill fw-bold shadow-sm">
                        <i class="fas fa-check-circle me-1.5"></i> ক্রয় ও ইনভেনটরি সংরক্ষণ করুন
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
    let rowCounter = 1;

    const existingBooksMap = {};
    document.querySelectorAll('#booksList option').forEach(opt => {
        existingBooksMap[opt.value.trim().toLowerCase()] = {
            id: opt.getAttribute('data-id'),
            author: opt.getAttribute('data-author'),
            categoryId: opt.getAttribute('data-category-id'),
            categoryName: opt.getAttribute('data-category-name'),
            price: opt.getAttribute('data-price'),
        };
    });

    const categoryMap = {};
    document.querySelectorAll('#categoriesList option').forEach(opt => {
        categoryMap[opt.value.trim().toLowerCase()] = opt.getAttribute('data-id');
    });

    function onTitleInput(input, index) {
        const val = input.value.trim().toLowerCase();
        const row = document.querySelector(`tr[data-row="${index}"]`);
        if (!row) return;

        const hiddenId = row.querySelector('.item-book-id');
        const authorInput = row.querySelector('.item-author');
        const catInput = row.querySelector('.item-category');
        const catIdInput = row.querySelector('.item-category-id');
        const mrpInput = row.querySelector('.item-mrp');
        const saleInput = row.querySelector('.item-sale');

        if (existingBooksMap[val]) {
            const b = existingBooksMap[val];
            hiddenId.value = b.id;
            if (b.author && !authorInput.value) authorInput.value = b.author;
            if (b.categoryName && !catInput.value) {
                catInput.value = b.categoryName;
                catIdInput.value = b.categoryId;
            }
            if (b.price) {
                if (!mrpInput.value || mrpInput.value == '0') mrpInput.value = b.price;
                if (!saleInput.value || saleInput.value == '0') saleInput.value = b.price;
                onMrpChange(index);
            }
        } else {
            hiddenId.value = '';
        }
    }

    function onMrpChange(index) {
        const row = document.querySelector(`tr[data-row="${index}"]`);
        if (!row) return;

        const mrp = parseFloat(row.querySelector('.item-mrp').value) || 0;
        const comm = parseFloat(row.querySelector('.item-comm').value) || 0;
        const costInput = row.querySelector('.item-cost');
        const shopDisc = parseFloat(row.querySelector('.item-shop-disc').value) || 0;
        const saleInput = row.querySelector('.item-sale');

        if (comm > 0) {
            const cost = mrp - (mrp * comm / 100);
            costInput.value = cost.toFixed(2);
        } else if (parseFloat(costInput.value) === 0) {
            costInput.value = mrp.toFixed(2);
        }

        if (shopDisc > 0) {
            const sale = mrp - (mrp * shopDisc / 100);
            saleInput.value = sale.toFixed(2);
        } else if (!saleInput.value || parseFloat(saleInput.value) === 0) {
            saleInput.value = mrp.toFixed(2);
        }

        calcRow(index);
    }

    function onCommChange(index) {
        const row = document.querySelector(`tr[data-row="${index}"]`);
        if (!row) return;

        const mrp = parseFloat(row.querySelector('.item-mrp').value) || 0;
        const comm = parseFloat(row.querySelector('.item-comm').value) || 0;
        const costInput = row.querySelector('.item-cost');

        if (mrp > 0) {
            const cost = mrp - (mrp * comm / 100);
            costInput.value = Math.max(0, cost).toFixed(2);
        }
        calcRow(index);
    }

    function onCostChange(index) {
        const row = document.querySelector(`tr[data-row="${index}"]`);
        if (!row) return;

        const mrp = parseFloat(row.querySelector('.item-mrp').value) || 0;
        const cost = parseFloat(row.querySelector('.item-cost').value) || 0;
        const commInput = row.querySelector('.item-comm');

        if (mrp > 0 && cost <= mrp) {
            const comm = ((mrp - cost) / mrp) * 100;
            commInput.value = comm.toFixed(2);
        }
        calcRow(index);
    }

    function onShopDiscChange(index) {
        const row = document.querySelector(`tr[data-row="${index}"]`);
        if (!row) return;

        const mrp = parseFloat(row.querySelector('.item-mrp').value) || 0;
        const shopDisc = parseFloat(row.querySelector('.item-shop-disc').value) || 0;
        const saleInput = row.querySelector('.item-sale');

        if (mrp > 0) {
            const sale = mrp - (mrp * shopDisc / 100);
            saleInput.value = Math.max(0, sale).toFixed(2);
        }
    }

    function onSaleChange(index) {
        const row = document.querySelector(`tr[data-row="${index}"]`);
        if (!row) return;

        const mrp = parseFloat(row.querySelector('.item-mrp').value) || 0;
        const sale = parseFloat(row.querySelector('.item-sale').value) || 0;
        const shopDiscInput = row.querySelector('.item-shop-disc');

        if (mrp > 0 && sale <= mrp) {
            const disc = ((mrp - sale) / mrp) * 100;
            shopDiscInput.value = disc.toFixed(2);
        }
    }

    function onQtyChange(index) {
        calcRow(index);
    }

    function calcRow(index) {
        const row = document.querySelector(`tr[data-row="${index}"]`);
        if (!row) return;

        const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
        const cost = parseFloat(row.querySelector('.item-cost').value) || 0;
        const subtotal = qty * cost;

        row.querySelector('.item-subtotal').textContent = '৳' + subtotal.toFixed(2);
        calcTotals();
    }

    function calcTotals() {
        let total = 0;
        document.querySelectorAll('.item-row').forEach(row => {
            const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
            const cost = parseFloat(row.querySelector('.item-cost').value) || 0;
            total += (qty * cost);
        });

        const discount = parseFloat(document.getElementById('discountInput').value) || 0;
        const grandTotal = Math.max(0, total - discount);

        document.getElementById('displayTotal').textContent = '৳' + total.toFixed(2);
        document.getElementById('displayGrandTotal').textContent = '৳' + grandTotal.toFixed(2);

        const type = document.getElementById('paymentType').value;
        const paidInput = document.getElementById('paidAmountInput');

        if (type === 'cash') {
            paidInput.value = grandTotal.toFixed(2);
        } else if (type === 'credit') {
            paidInput.value = 0;
        }

        const paid = parseFloat(paidInput.value) || 0;
        const due = Math.max(0, grandTotal - paid);

        document.getElementById('displayDue').textContent = '৳' + due.toFixed(2);
    }

    let isNewPublisherMode = false;
    function togglePublisherMode() {
        isNewPublisherMode = !isNewPublisherMode;
        const existWrap = document.getElementById('existingPublisherWrapper');
        const newWrap = document.getElementById('newPublisherWrapper');
        const toggleBtn = document.getElementById('togglePubBtn');
        const select = document.getElementById('publisherSelect');
        const newNameInput = document.getElementById('newPublisherName');

        if (isNewPublisherMode) {
            existWrap.style.display = 'none';
            newWrap.style.display = 'block';
            select.value = '';
            toggleBtn.innerHTML = '<i class="fas fa-list me-1"></i>বিদ্যমান তালিকা থেকে বেছে নিন';
            setTimeout(() => newNameInput.focus(), 100);
        } else {
            existWrap.style.display = 'block';
            newWrap.style.display = 'none';
            newNameInput.value = '';
            toggleBtn.innerHTML = '<i class="fas fa-plus-circle me-1"></i>+ নতুন প্রকাশনী';
        }
    }

    function onPaymentTypeChange() {
        const type = document.getElementById('paymentType').value;
        const paidInput = document.getElementById('paidAmountInput');
        const paidGroup = document.getElementById('paidAmountGroup');
        const payMethodGroup = document.getElementById('paymentMethodGroup');
        const trxRefGroup = document.getElementById('trxRefGroup');

        if (type === 'cash') {
            paidGroup.style.display = 'block';
            payMethodGroup.style.display = 'block';
            trxRefGroup.style.display = 'block';
        } else if (type === 'credit') {
            paidGroup.style.display = 'none';
            payMethodGroup.style.display = 'none';
            trxRefGroup.style.display = 'none';
        } else {
            paidGroup.style.display = 'block';
            payMethodGroup.style.display = 'block';
            trxRefGroup.style.display = 'block';
        }
        calcTotals();
    }

    function addItemRow() {
        const tbody = document.getElementById('itemsBody');
        const i = rowCounter++;

        const tr = document.createElement('tr');
        tr.className = 'item-row';
        tr.setAttribute('data-row', i);
        tr.innerHTML = `
            <td>
                <input type="text" name="items[${i}][title]" class="form-control form-control-sm item-title" 
                       list="booksList" placeholder="বইয়ের নাম..." required oninput="onTitleInput(this, ${i})">
                <input type="hidden" name="items[${i}][book_id]" class="item-book-id" value="">
            </td>
            <td>
                <input type="text" name="items[${i}][author]" class="form-control form-control-sm item-author" 
                       list="authorsList" placeholder="লেখকের নাম...">
            </td>
            <td>
                <input type="text" name="items[${i}][category_name]" class="form-control form-control-sm item-category" 
                       list="categoriesList" placeholder="ক্যাটাগরি...">
                <input type="hidden" name="items[${i}][category_id]" class="item-category-id" value="">
            </td>
            <td>
                <input type="number" name="items[${i}][quantity]" class="form-control form-control-sm item-qty text-center" 
                       value="1" min="1" required oninput="onQtyChange(${i})">
            </td>
            <td>
                <input type="number" step="0.01" name="items[${i}][mrp_price]" class="form-control form-control-sm item-mrp text-end" 
                       value="0" min="0" placeholder="MRP" oninput="onMrpChange(${i})">
            </td>
            <td>
                <input type="number" step="0.01" name="items[${i}][purchase_commission_percent]" class="form-control form-control-sm item-comm text-center" 
                       value="0" min="0" max="100" placeholder="%" oninput="onCommChange(${i})">
            </td>
            <td>
                <input type="number" step="0.01" name="items[${i}][cost_price]" class="form-control form-control-sm item-cost text-end fw-bold text-danger" 
                       value="0" min="0" required oninput="onCostChange(${i})">
            </td>
            <td>
                <input type="number" step="0.01" name="items[${i}][shop_discount_percent]" class="form-control form-control-sm item-shop-disc text-center" 
                       value="0" min="0" max="100" placeholder="%" oninput="onShopDiscChange(${i})">
            </td>
            <td>
                <input type="number" step="0.01" name="items[${i}][sale_price]" class="form-control form-control-sm item-sale text-end fw-bold text-success" 
                       value="0" min="0" required oninput="onSaleChange(${i})">
            </td>
            <td class="text-end fw-bold text-dark item-subtotal">৳0.00</td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger p-1 border-0" onclick="removeRow(this)">
                    <i class="fas fa-times"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    }

    function removeRow(btn) {
        const rows = document.querySelectorAll('.item-row');
        if (rows.length <= 1) {
            alert('কমপক্ষে একটি বই তালিকায় থাকতে হবে।');
            return;
        }
        btn.closest('tr').remove();
        calcTotals();
    }

    // Initialize calculation
    document.addEventListener('DOMContentLoaded', () => {
        calcTotals();
        onPaymentTypeChange();
    });
</script>

@endsection
