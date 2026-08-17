@extends('layouts.admin')

@section('title', 'ক্রয় ইনভয়েস সংশোধন #' . $purchase->purchase_no)
@section('heading', 'ক্রয় ইনভয়েস ও স্টক সংশোধন')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.purchases.index') }}">প্রকাশনী ক্রয়</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.purchases.show', $purchase->id) }}">#{{ $purchase->purchase_no }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">ইনভয়েস সংশোধন</li>
@endsection

@section('actions')
    <a href="{{ route('admin.purchases.show', $purchase->id) }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> ইনভয়েসে ফিরে যান
    </a>
@endsection

@section('content')

<form action="{{ route('admin.purchases.update', $purchase->id) }}" method="POST" id="purchaseEditForm">
    @csrf
    @method('PUT')

    <div class="row g-4">
        {{-- Top Card: Publisher & Invoice Information --}}
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white py-3 px-4 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-warning-subtle text-dark p-2 rounded-3">
                            <i class="fas fa-file-pen fs-5"></i>
                        </span>
                        <div>
                            <h5 class="fw-bold mb-0 text-dark">ইনভয়েস ও প্রকাশনী সংশোধন</h5>
                            <small class="text-muted">চালান #{{ $purchase->purchase_no }} — তথ্য ও বইয়ের সংখ্যা/মূল্য পরিবর্তন করুন</small>
                        </div>
                    </div>

                    {{-- Publisher Mode Toggle --}}
                    <div class="btn-group p-1 bg-light rounded-pill border" role="group">
                        <button type="button" class="btn btn-sm rounded-pill fw-semibold px-3 active" id="btnExistingPub" onclick="setPublisherMode(false)">
                            <i class="fas fa-list-check me-1"></i> তালিকা থেকে নির্বাচন
                        </button>
                        <button type="button" class="btn btn-sm rounded-pill fw-semibold px-3 text-muted" id="btnNewPub" onclick="setPublisherMode(true)">
                            <i class="fas fa-plus-circle me-1"></i> + নতুন প্রকাশনী
                        </button>
                    </div>
                </div>

                <div class="card-body p-4 bg-white">
                    <div class="row g-4 align-items-start">
                        {{-- Left Side: Publisher Select / Input --}}
                        <div class="col-12 col-lg-6 border-end-lg pe-lg-4">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <label class="form-label fw-bold text-dark mb-0">
                                    <i class="fas fa-store text-primary me-1"></i> প্রকাশনী / সরবরাহকারী <span class="text-danger">*</span>
                                </label>
                            </div>

                            {{-- Existing Publisher Select --}}
                            <div id="existingPublisherWrapper">
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted"><i class="fas fa-magnifying-glass"></i></span>
                                    <select name="publisher_id" id="publisherSelect" class="form-select form-select-lg fs-6 @error('publisher_id') is-invalid @enderror">
                                        <option value="">-- প্রকাশনী নির্বাচন করুন --</option>
                                        @foreach($publishers as $pub)
                                            <option value="{{ $pub->id }}" @selected(old('publisher_id', $purchase->publisher_id) == $pub->id)>
                                                {{ $pub->name }} @if($pub->phone) (📞 {{ $pub->phone }}) @endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-text text-muted mt-1">
                                    <i class="fas fa-info-circle me-1 text-primary"></i> বর্তমান বা পরিবর্তিত প্রকাশনী বেছে নিন।
                                </div>
                            </div>

                            {{-- New Publisher Input Box --}}
                            <div id="newPublisherWrapper" style="display: none;">
                                <div class="p-3 bg-light rounded-3 border">
                                    <div class="mb-2">
                                        <label class="form-label small fw-semibold text-dark">নতুন প্রকাশনীর নাম <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white"><i class="fas fa-pen-nib text-primary"></i></span>
                                            <input type="text" name="publisher_name" id="newPublisherName" class="form-control" placeholder="নতুন প্রকাশনীর নাম লিখুন...">
                                        </div>
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-md-6">
                                            <label class="form-label small fw-semibold text-muted">মোবাইল / ফোন নম্বর</label>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text bg-white"><i class="fas fa-phone"></i></span>
                                                <input type="text" name="publisher_phone" class="form-control" placeholder="০১৭১০...">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-semibold text-muted">ঠিকানা (বাংলাবাজার / এলাকা)</label>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text bg-white"><i class="fas fa-location-dot"></i></span>
                                                <input type="text" name="publisher_address" class="form-control" placeholder="ঠিকানা...">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @error('publisher_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>

                        {{-- Right Side: Invoice No, Memo No, Purchase Date --}}
                        <div class="col-12 col-lg-6 ps-lg-4">
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <label class="form-label fw-bold text-dark mb-1">
                                        <i class="fas fa-hashtag text-primary me-1"></i> সফটওয়্যার চালান নং <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="text" name="purchase_no" class="form-control fw-bold @error('purchase_no') is-invalid @enderror" 
                                               value="{{ old('purchase_no', $purchase->purchase_no) }}" required>
                                    </div>
                                    @error('purchase_no')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-sm-6">
                                    <label class="form-label fw-bold text-dark mb-1">
                                        <i class="fas fa-calendar-day text-primary me-1"></i> ক্রয়ের তারিখ <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="fas fa-calendar-alt text-muted"></i></span>
                                        <input type="date" name="purchase_date" class="form-control" 
                                               value="{{ old('purchase_date', $purchase->purchase_date ? $purchase->purchase_date->format('Y-m-d') : date('Y-m-d')) }}" required>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-bold text-dark mb-1">
                                        <i class="fas fa-receipt text-success me-1"></i> প্রকাশকের নিজস্ব মেমো / ইনভয়েস নং
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light text-success"><i class="fas fa-file-invoice"></i></span>
                                        <input type="text" name="publisher_memo_no" class="form-control" 
                                               placeholder="যেমন: মেমো নং # ১২৮৯ বা চালান ৫২..." 
                                               value="{{ old('publisher_memo_no', $purchase->publisher_memo_no) }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Main Full-Width Table Card: Books & Purchase Entry --}}
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white py-3 px-4 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-success-subtle text-success p-2 rounded-3">
                            <i class="fas fa-book-bookmark fs-5"></i>
                        </span>
                        <div>
                            <h5 class="fw-bold mb-0 text-dark">ক্রয়কৃত বই ও কমিশন হিসাব সংশোধন</h5>
                            <small class="text-muted">বইয়ের নাম, পরিমাণ, কমিশন বা মূল্য পরিবর্তন করলে স্টক ও মোট বিল স্বয়ংক্রিয়ভাবে আপডেট হবে</small>
                        </div>
                    </div>

                    <button type="button" class="btn btn-success rounded-pill px-4 fw-bold shadow-sm" onclick="addItemRow()">
                        <i class="fas fa-plus me-1.5"></i> আরো বই যোগ করুন
                    </button>
                </div>

                <div class="card-body p-3 p-md-4">
                    <div class="table-responsive rounded-3 border">
                        <table class="table table-hover align-middle mb-0" id="itemsTable">
                            <thead>
                                <tr class="table-light text-center small text-muted text-uppercase align-middle">
                                    <th style="min-width: 220px;" class="text-start ps-3 py-3">বইয়ের নাম <span class="text-danger">*</span></th>
                                    <th style="min-width: 140px;" class="text-start py-3">লেখক</th>
                                    <th style="min-width: 130px;" class="text-start py-3">ক্যাটাগরি</th>
                                    <th style="width: 85px;" class="py-3">পরিমাণ</th>
                                    <th style="width: 115px;" class="py-3 bg-light-subtle">মূল্য (MRP ৳)</th>
                                    <th style="width: 95px;" class="py-3 bg-primary-subtle text-primary">ক্রয় কমিশন %</th>
                                    <th style="width: 115px;" class="py-3 bg-primary-subtle text-primary">ক্রয়মূল্য (৳)</th>
                                    <th style="width: 95px;" class="py-3 bg-success-subtle text-success">শপ ছাড় %</th>
                                    <th style="width: 115px;" class="py-3 bg-success-subtle text-success">বিক্রয়মূল্য (৳)</th>
                                    <th style="width: 125px;" class="text-end pe-3 py-3">মোট ক্রয় (৳)</th>
                                    <th style="width: 45px;" class="py-3"></th>
                                </tr>
                            </thead>
                            <tbody id="itemsBody">
                                @forelse($purchase->items as $i => $item)
                                    <tr class="item-row" data-row="{{ $i }}">
                                        <td class="ps-3">
                                            <input type="text" name="items[{{ $i }}][title]" class="form-control form-control-sm item-title fw-semibold" 
                                                   list="booksList" placeholder="বইয়ের নাম..." value="{{ $item->book_title }}" required oninput="onTitleInput(this, {{ $i }})">
                                            <input type="hidden" name="items[{{ $i }}][book_id]" class="item-book-id" value="{{ $item->book_id }}">
                                        </td>
                                        <td>
                                            <input type="text" name="items[{{ $i }}][author]" class="form-control form-control-sm item-author" 
                                                   list="authorsList" placeholder="লেখকের নাম..." value="{{ $item->author_name ?? ($item->book?->author_name ?? '') }}">
                                        </td>
                                        <td>
                                            <input type="text" name="items[{{ $i }}][category_name]" class="form-control form-control-sm item-category" 
                                                   list="categoriesList" placeholder="ক্যাটাগরি..." value="{{ $item->category?->name ?? ($item->book?->category?->name ?? '') }}">
                                            <input type="hidden" name="items[{{ $i }}][category_id]" class="item-category-id" value="{{ $item->category_id ?? ($item->book?->category_id ?? '') }}">
                                        </td>
                                        <td>
                                            <input type="number" name="items[{{ $i }}][quantity]" class="form-control form-control-sm item-qty text-center fw-bold" 
                                                   value="{{ $item->quantity }}" min="1" required oninput="onQtyChange({{ $i }})">
                                        </td>
                                        <td class="bg-light-subtle">
                                            <input type="number" step="0.01" name="items[{{ $i }}][mrp_price]" class="form-control form-control-sm item-mrp text-end fw-semibold" 
                                                   value="{{ $item->mrp_price > 0 ? $item->mrp_price : ($item->book?->price ?? 0) }}" min="0" placeholder="MRP" oninput="onMrpChange({{ $i }})">
                                        </td>
                                        <td class="bg-primary-subtle bg-opacity-25">
                                            <input type="number" step="0.01" name="items[{{ $i }}][purchase_commission_percent]" class="form-control form-control-sm item-comm text-center text-primary fw-bold" 
                                                   value="{{ $item->purchase_commission_percent ?? 0 }}" min="0" max="100" placeholder="%" oninput="onCommChange({{ $i }})">
                                        </td>
                                        <td class="bg-primary-subtle bg-opacity-25">
                                            <input type="number" step="0.01" name="items[{{ $i }}][cost_price]" class="form-control form-control-sm item-cost text-end fw-bold text-danger" 
                                                   value="{{ $item->unit_cost_price }}" min="0" required oninput="onCostChange({{ $i }})">
                                        </td>
                                        <td class="bg-success-subtle bg-opacity-25">
                                            <input type="number" step="0.01" name="items[{{ $i }}][shop_discount_percent]" class="form-control form-control-sm item-shop-disc text-center text-success fw-bold" 
                                                   value="{{ $item->shop_discount_percent ?? 0 }}" min="0" max="100" placeholder="%" oninput="onShopDiscChange({{ $i }})">
                                        </td>
                                        <td class="bg-success-subtle bg-opacity-25">
                                            <input type="number" step="0.01" name="items[{{ $i }}][sale_price]" class="form-control form-control-sm item-sale text-end fw-bold text-success" 
                                                   value="{{ $item->unit_sale_price }}" min="0" required oninput="onSaleChange({{ $i }})">
                                        </td>
                                        <td class="text-end pe-3 fw-bold text-dark item-subtotal fs-6">৳{{ number_format($item->subtotal, 2) }}</td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-outline-danger p-1 rounded-circle border-0" onclick="removeRow(this)" title="সারি মুছুন">
                                                <i class="fas fa-trash-can"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr class="item-row" data-row="0">
                                        <td class="ps-3">
                                            <input type="text" name="items[0][title]" class="form-control form-control-sm item-title fw-semibold" 
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
                                            <input type="number" name="items[0][quantity]" class="form-control form-control-sm item-qty text-center fw-bold" 
                                                   value="1" min="1" required oninput="onQtyChange(0)">
                                        </td>
                                        <td class="bg-light-subtle">
                                            <input type="number" step="0.01" name="items[0][mrp_price]" class="form-control form-control-sm item-mrp text-end fw-semibold" 
                                                   value="0" min="0" placeholder="MRP" oninput="onMrpChange(0)">
                                        </td>
                                        <td class="bg-primary-subtle bg-opacity-25">
                                            <input type="number" step="0.01" name="items[0][purchase_commission_percent]" class="form-control form-control-sm item-comm text-center text-primary fw-bold" 
                                                   value="0" min="0" max="100" placeholder="%" oninput="onCommChange(0)">
                                        </td>
                                        <td class="bg-primary-subtle bg-opacity-25">
                                            <input type="number" step="0.01" name="items[0][cost_price]" class="form-control form-control-sm item-cost text-end fw-bold text-danger" 
                                                   value="0" min="0" required oninput="onCostChange(0)">
                                        </td>
                                        <td class="bg-success-subtle bg-opacity-25">
                                            <input type="number" step="0.01" name="items[0][shop_discount_percent]" class="form-control form-control-sm item-shop-disc text-center text-success fw-bold" 
                                                   value="0" min="0" max="100" placeholder="%" oninput="onShopDiscChange(0)">
                                        </td>
                                        <td class="bg-success-subtle bg-opacity-25">
                                            <input type="number" step="0.01" name="items[0][sale_price]" class="form-control form-control-sm item-sale text-end fw-bold text-success" 
                                                   value="0" min="0" required oninput="onSaleChange(0)">
                                        </td>
                                        <td class="text-end pe-3 fw-bold text-dark item-subtotal fs-6">৳0.00</td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-outline-danger p-1 rounded-circle border-0" onclick="removeRow(this)" title="সারি মুছুন">
                                                <i class="fas fa-trash-can"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Add Row Button at Bottom of Table --}}
                    <div class="mt-3 d-flex justify-content-between align-items-center">
                        <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3 fw-semibold" onclick="addItemRow()">
                            <i class="fas fa-plus-circle me-1"></i> আরো বইয়ের সারি যোগ করুন
                        </button>
                    </div>

                    {{-- Datalists for Auto-suggestions --}}
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
                                (স্টক: {{ $b->stock_quantity }} | MRP: ৳{{ $b->price }})
                            </option>
                        @endforeach
                    </datalist>
                </div>
            </div>
        </div>

        {{-- Bottom Section: Notes (Left) and Calculation & Summary (Right) --}}
        <div class="col-12 col-lg-7">
            {{-- Notes Card --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white py-3 px-4 border-bottom">
                    <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-note-sticky text-warning me-2"></i>মন্তব্য বা চালান সংক্রান্ত নোট (ঐচ্ছিক)</h6>
                </div>
                <div class="card-body p-4">
                    <textarea name="notes" rows="4" class="form-control rounded-3" 
                              placeholder="ক্রয় সংক্রান্ত কোনো বিশেষ শর্ত, নোট বা মেমোর তথ্য...">{{ old('notes', $purchase->notes) }}</textarea>
                </div>
            </div>

            {{-- Automation Notice Card --}}
            <div class="card border-0 bg-warning-subtle bg-opacity-25 rounded-4 p-4 border-start border-4 border-warning">
                <div class="d-flex align-items-start gap-3">
                    <div class="fs-3 text-warning"><i class="fas fa-rotate"></i></div>
                    <div>
                        <h6 class="fw-bold text-dark mb-1">স্টক সমন্বয় ও হিসাব সতর্কতা:</h6>
                        <p class="mb-0 small text-muted">
                            ইনভয়েস পরিবর্তন বা আপডেট করার সাথে সাথে পূর্বের এন্ট্রিকৃত পরিমাণের বিপরীতে স্টক পুনর্গণনা করে বর্তমান নতুন পরিমাণ বুকশপ ইনভেন্টরিতে আপডেট করা হবে।
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Side: Payment & Calculation Card --}}
        <div class="col-12 col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 sticky-top" style="top: 80px;">
                <div class="card-header bg-dark text-white py-3 px-4 rounded-top-4 d-flex align-items-center justify-content-between">
                    <h5 class="fw-bold mb-0"><i class="fas fa-calculator text-warning me-2"></i>হিসাব সামারি</h5>
                    <span class="badge bg-light text-dark px-2.5 py-1 rounded-pill small">সংশোধন</span>
                </div>

                <div class="card-body p-4 bg-white">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted fw-semibold">মোট ক্রয়মূল্য:</span>
                        <span class="fw-bold fs-5 text-dark" id="displayTotal">৳{{ number_format($purchase->total_amount, 2) }}</span>
                    </div>

                    <div class="mb-3 p-3 bg-light rounded-3 border">
                        <label class="form-label small fw-bold text-muted mb-1">
                            <i class="fas fa-tag text-danger me-1"></i> অতিরিক্ত চালান ছাড় / ডিসকাউন্ট (৳):
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-white fw-bold">৳</span>
                            <input type="number" step="0.01" name="discount_amount" id="discountInput" class="form-control form-control-lg text-end fw-bold text-danger" 
                                   value="{{ old('discount_amount', $purchase->discount_amount) }}" min="0" oninput="calcTotals()">
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center p-3 bg-primary-subtle rounded-3 mb-3 border border-primary-subtle">
                        <div>
                            <span class="fw-bold text-dark d-block">সর্বমোট প্রদেয় (Grand Total):</span>
                            <small class="text-muted">ছাড় বাদে প্রদেয় মোট বিল</small>
                        </div>
                        <span class="fw-bolder fs-3 text-primary" id="displayGrandTotal">৳{{ number_format($purchase->grand_total, 2) }}</span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">
                            <i class="fas fa-hand-holding-dollar text-primary me-1"></i> ক্রয়ের ধরন <span class="text-danger">*</span>
                        </label>
                        <select name="payment_type" id="paymentType" class="form-select form-select-lg fs-6 fw-semibold" required onchange="calcTotals()">
                            <option value="cash" @selected(old('payment_type', $purchase->payment_type) == 'cash')>💵 নগদে ক্রয় (সম্পূর্ণ পরিশোধ / Cash)</option>
                            <option value="credit" @selected(old('payment_type', $purchase->payment_type) == 'credit')>⏳ বাকিতে ক্রয় (সম্পূর্ণ বকেয়া / Due)</option>
                            <option value="partial" @selected(old('payment_type', $purchase->payment_type) == 'partial')>⚖️ আংশিক নগদ ও বাকি (Partial)</option>
                        </select>
                    </div>

                    <div class="d-flex justify-content-between align-items-center p-2 mb-2 bg-light rounded-3">
                        <span class="text-muted small fw-semibold">ইতিপূর্বে পরিশোধিত:</span>
                        <span class="fw-bold text-success fs-6" id="displayPaid">৳{{ number_format($purchase->paid_amount, 2) }}</span>
                    </div>

                    <div class="alert alert-danger p-3 rounded-3 mb-4 d-flex justify-content-between align-items-center border-0 bg-danger-subtle text-danger" id="dueAlert">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-circle-exclamation fs-5"></i>
                            <span class="fw-bold">অবশিষ্ট বকেয়া (Due):</span>
                        </div>
                        <span class="fw-bolder fs-4 text-danger" id="displayDue">৳{{ number_format($purchase->due_amount, 2) }}</span>
                    </div>

                    <button type="submit" class="btn btn-warning btn-lg w-100 py-3 rounded-pill fw-bold text-dark shadow-lg d-flex align-items-center justify-content-center gap-2">
                        <i class="fas fa-check-circle fs-5"></i>
                        <span>ইনভয়েস সংশোধন সংরক্ষণ করুন</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
    let rowCounter = {{ $purchase->items->count() > 0 ? $purchase->items->count() : 1 }};
    const totalRecordedPaid = {{ (float) $purchase->paid_amount }};

    // Cache preloaded books map
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

    function setPublisherMode(isNew) {
        const existWrap = document.getElementById('existingPublisherWrapper');
        const newWrap = document.getElementById('newPublisherWrapper');
        const btnExisting = document.getElementById('btnExistingPub');
        const btnNew = document.getElementById('btnNewPub');
        const select = document.getElementById('publisherSelect');
        const newNameInput = document.getElementById('newPublisherName');

        if (isNew) {
            existWrap.style.display = 'none';
            newWrap.style.display = 'block';
            select.value = '';
            btnExisting.classList.remove('active');
            btnExisting.classList.add('text-muted');
            btnNew.classList.add('active');
            btnNew.classList.remove('text-muted');
            setTimeout(() => newNameInput.focus(), 100);
        } else {
            existWrap.style.display = 'block';
            newWrap.style.display = 'none';
            newNameInput.value = '';
            btnNew.classList.remove('active');
            btnNew.classList.add('text-muted');
            btnExisting.classList.add('active');
            btnExisting.classList.remove('text-muted');
        }
    }

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

        const due = Math.max(0, grandTotal - totalRecordedPaid);

        document.getElementById('displayDue').textContent = '৳' + due.toFixed(2);

        const dueAlert = document.getElementById('dueAlert');
        if (due <= 0) {
            dueAlert.classList.remove('alert-danger', 'bg-danger-subtle', 'text-danger');
            dueAlert.classList.add('alert-success', 'bg-success-subtle', 'text-success');
        } else {
            dueAlert.classList.remove('alert-success', 'bg-success-subtle', 'text-success');
            dueAlert.classList.add('alert-danger', 'bg-danger-subtle', 'text-danger');
        }
    }

    function addItemRow() {
        const tbody = document.getElementById('itemsBody');
        const i = rowCounter++;

        const tr = document.createElement('tr');
        tr.className = 'item-row';
        tr.setAttribute('data-row', i);
        tr.innerHTML = `
            <td class="ps-3">
                <input type="text" name="items[${i}][title]" class="form-control form-control-sm item-title fw-semibold" 
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
                <input type="number" name="items[${i}][quantity]" class="form-control form-control-sm item-qty text-center fw-bold" 
                       value="1" min="1" required oninput="onQtyChange(${i})">
            </td>
            <td class="bg-light-subtle">
                <input type="number" step="0.01" name="items[${i}][mrp_price]" class="form-control form-control-sm item-mrp text-end fw-semibold" 
                       value="0" min="0" placeholder="MRP" oninput="onMrpChange(${i})">
            </td>
            <td class="bg-primary-subtle bg-opacity-25">
                <input type="number" step="0.01" name="items[${i}][purchase_commission_percent]" class="form-control form-control-sm item-comm text-center text-primary fw-bold" 
                       value="0" min="0" max="100" placeholder="%" oninput="onCommChange(${i})">
            </td>
            <td class="bg-primary-subtle bg-opacity-25">
                <input type="number" step="0.01" name="items[${i}][cost_price]" class="form-control form-control-sm item-cost text-end fw-bold text-danger" 
                       value="0" min="0" required oninput="onCostChange(${i})">
            </td>
            <td class="bg-success-subtle bg-opacity-25">
                <input type="number" step="0.01" name="items[${i}][shop_discount_percent]" class="form-control form-control-sm item-shop-disc text-center text-success fw-bold" 
                       value="0" min="0" max="100" placeholder="%" oninput="onShopDiscChange(${i})">
            </td>
            <td class="bg-success-subtle bg-opacity-25">
                <input type="number" step="0.01" name="items[${i}][sale_price]" class="form-control form-control-sm item-sale text-end fw-bold text-success" 
                       value="0" min="0" required oninput="onSaleChange(${i})">
            </td>
            <td class="text-end pe-3 fw-bold text-dark item-subtotal fs-6">৳0.00</td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger p-1 rounded-circle border-0" onclick="removeRow(this)" title="সারি মুছুন">
                    <i class="fas fa-trash-can"></i>
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

    // Initialize calculation on load
    document.addEventListener('DOMContentLoaded', () => {
        calcTotals();
    });
</script>

@endsection
