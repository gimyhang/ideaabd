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
                            <label class="form-label fw-semibold">ক্রয় ইনভয়েস # <span class="text-danger">*</span></label>
                            <input type="text" name="purchase_no" class="form-control @error('purchase_no') is-invalid @enderror" 
                                   value="{{ old('purchase_no', $suggestedInvoiceNo) }}" required>
                            @error('purchase_no')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">ক্রয়ের তারিখ <span class="text-danger">*</span></label>
                            <input type="date" name="purchase_date" class="form-control" value="{{ old('purchase_date', date('Y-m-d')) }}" required>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Purchase Items Card --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-book-medical me-2 text-success"></i>ক্রয়কৃত বইয়ের তালিকা</h5>
                        <small class="text-success"><i class="fas fa-check-circle me-1"></i>ইনভয়েস সেভ হওয়ার সাথে সাথে বইটি বুকশপ এবং লেখক/প্রকাশক না থাকলে ডিরেক্টরিতে অটোমেটিক যোগ হবে।</small>
                    </div>
                    <button type="button" class="btn btn-sm btn-success rounded-pill px-3 fw-semibold" onclick="addItemRow()">
                        <i class="fas fa-plus me-1"></i> আরো বই যোগ করুন
                    </button>
                </div>
                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle" id="itemsTable">
                            <thead class="table-light">
                                <tr class="small text-muted text-uppercase">
                                    <th style="min-width: 220px;">বইয়ের নাম (নতুন/বিদ্যমান) <span class="text-danger">*</span></th>
                                    <th style="min-width: 140px;">লেখক (নতুন/বিদ্যমান)</th>
                                    <th style="min-width: 140px;">ক্যাটাগরি</th>
                                    <th style="width: 90px;">পরিমাণ <span class="text-danger">*</span></th>
                                    <th style="width: 110px;">ক্রয়মূল্য (৳) <span class="text-danger">*</span></th>
                                    <th style="width: 110px;">বিক্রয়মূল্য (৳) <span class="text-danger">*</span></th>
                                    <th style="width: 110px;">মোট (৳)</th>
                                    <th style="width: 45px;"></th>
                                </tr>
                            </thead>
                            <tbody id="itemsBody">
                                {{-- Initial Row --}}
                                <tr class="item-row" data-row="0">
                                    <td>
                                        <input type="text" name="items[0][title]" class="form-control form-control-sm item-title" 
                                               list="booksList" placeholder="বইয়ের নাম লিখুন..." required oninput="onTitleInput(this, 0)">
                                        <input type="hidden" name="items[0][book_id]" class="item-book-id" value="">
                                    </td>
                                    <td>
                                        <input type="text" name="items[0][author]" class="form-control form-control-sm item-author" 
                                               list="authorsList" placeholder="লেখকের নাম...">
                                    </td>
                                    <td>
                                        <select name="items[0][category_id]" class="form-select form-select-sm item-category">
                                            <option value="">ক্যাটাগরি</option>
                                            @foreach($categories as $cat)
                                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" name="items[0][quantity]" class="form-control form-control-sm item-qty text-center" 
                                               value="1" min="1" required oninput="calcRow(0)">
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" name="items[0][cost_price]" class="form-control form-control-sm item-cost text-end" 
                                               value="0" min="0" required oninput="calcRow(0)">
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" name="items[0][sale_price]" class="form-control form-control-sm item-sale text-end" 
                                               value="0" min="0" required>
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

                    {{-- Datalist for existing authors --}}
                    <datalist id="authorsList">
                        @foreach($authors as $a)
                            <option value="{{ $a->name }}"></option>
                        @endforeach
                    </datalist>

                    {{-- Datalist for fast existing books lookup --}}
                    <datalist id="booksList">
                        @foreach($books as $b)
                            <option value="{{ $b->title }}" 
                                    data-id="{{ $b->id }}"
                                    data-author="{{ $b->author_name }}"
                                    data-category="{{ $b->category_id }}"
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
                        <span class="text-muted">মোট বইয়ের ক্রয়মূল্য:</span>
                        <span class="fw-bold fs-5 text-dark" id="displayTotal">৳0.00</span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">ছাড় / কমিশন (Discount ৳):</label>
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
            category: opt.getAttribute('data-category'),
            price: opt.getAttribute('data-price'),
        };
    });

    function onTitleInput(input, index) {
        const val = input.value.trim().toLowerCase();
        const row = document.querySelector(`tr[data-row="${index}"]`);
        if (!row) return;

        const hiddenId = row.querySelector('.item-book-id');
        const authorInput = row.querySelector('.item-author');
        const catSelect = row.querySelector('.item-category');
        const saleInput = row.querySelector('.item-sale');

        if (existingBooksMap[val]) {
            const b = existingBooksMap[val];
            hiddenId.value = b.id;
            if (b.author && !authorInput.value) authorInput.value = b.author;
            if (b.category && !catSelect.value) catSelect.value = b.category;
            if (b.price && (!saleInput.value || saleInput.value == '0')) saleInput.value = b.price;
        } else {
            hiddenId.value = '';
        }
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
                       list="booksList" placeholder="বইয়ের নাম লিখুন..." required oninput="onTitleInput(this, ${i})">
                <input type="hidden" name="items[${i}][book_id]" class="item-book-id" value="">
            </td>
            <td>
                <input type="text" name="items[${i}][author]" class="form-control form-control-sm item-author" 
                       list="authorsList" placeholder="লেখকের নাম...">
            </td>
            <td>
                <select name="items[${i}][category_id]" class="form-select form-select-sm item-category">
                    <option value="">ক্যাটাগরি</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="number" name="items[${i}][quantity]" class="form-control form-control-sm item-qty text-center" 
                       value="1" min="1" required oninput="calcRow(${i})">
            </td>
            <td>
                <input type="number" step="0.01" name="items[${i}][cost_price]" class="form-control form-control-sm item-cost text-end" 
                       value="0" min="0" required oninput="calcRow(${i})">
            </td>
            <td>
                <input type="number" step="0.01" name="items[${i}][sale_price]" class="form-control form-control-sm item-sale text-end" 
                       value="0" min="0" required>
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
