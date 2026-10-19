@extends('layouts.admin')

@push('styles')
@include('admin.accounting._modern')
<link rel="stylesheet" href="{{ asset('css/purchases-items.css') }}?v={{ @filemtime(public_path('css/purchases-items.css')) ?: 1 }}">
<script src="{{ asset('js/purchases-items.js') }}?v={{ @filemtime(public_path('js/purchases-items.js')) ?: 1 }}" defer></script>
@endpush

@section('title', $label)
@section('heading', $label)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.purchases.index') }}">Purchases</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $label }}</li>
@endsection

@section('actions')
    <button type="button" class="btn btn-primary btn-sm" id="piFormToggle" data-bs-toggle="collapse" data-bs-target="#piFormWrap" aria-expanded="{{ $errors->any() || old('vendor_name') ? 'true' : 'false' }}" aria-controls="piFormWrap">+ New purchase</button>
    <a href="{{ route('admin.purchases.items.stock', ['type' => $type]) }}" class="btn btn-outline-secondary btn-sm">Stock</a>
    <a href="{{ route('admin.purchases.index') }}" class="btn btn-light btn-sm">Back</a>
@endsection

@section('content')
<section class="card pi-card p-0 pi-stock" id="piStock" data-url="{{ route('admin.purchases.items.stock-list') }}" data-merge="{{ route('admin.purchases.items.merge') }}" data-type="{{ $type }}" data-buy="1" data-csrf="{{ csrf_token() }}">
    <div class="pi-toolbar">
        <h3 class="pi-h">{{ $label }} stock <span class="pi-live"><span class="pi-dot"></span> <span id="piStockAt">live</span></span></h3>
        <form class="pi-filter" id="piStockFilter" onsubmit="return false">
            <input type="search" name="q" class="form-control" placeholder="Name / SKU / Brand" aria-label="Search stock">
            <input type="hidden" name="category_id" value="">
            <label class="iv-check"><input type="checkbox" name="low" value="1"> Low</label>
        </form>
    </div>
    <div id="piStockBody">@include('admin.purchases.partials.item-stock-list', $stockBlock)</div>
</section>

<div class="collapse {{ $errors->any() || old('vendor_name') ? 'show' : '' }}" id="piFormWrap">
<form method="POST" action="{{ route('admin.purchases.items.store') }}" class="pi-form" id="itemPurchaseForm" data-products="{{ route('admin.purchases.items.products') }}" data-type="{{ $type }}">
    @csrf
    <input type="hidden" name="type" value="{{ $type }}">

    <section class="card pi-card">
        <h3 class="pi-h">Invoice</h3>
        <div class="row">
            <div class="col-md-3 col-6"><label class="form-label" for="purchase_no">Number</label><input id="purchase_no" name="purchase_no" class="form-control" value="{{ old('purchase_no', $purchaseNo) }}" required maxlength="50"></div>
            <div class="col-md-3 col-6"><label class="form-label" for="purchase_date">Date</label><input id="purchase_date" type="date" name="purchase_date" class="form-control" value="{{ old('purchase_date', date('Y-m-d')) }}" required></div>
            <div class="col-md-3 col-6"><label class="form-label" for="memo_no">Memo</label><input id="memo_no" name="memo_no" class="form-control" value="{{ old('memo_no') }}" maxlength="100"></div>
            <div class="col-md-3 col-6"><label class="form-label" for="due_date">Due date</label><input id="due_date" type="date" name="due_date" class="form-control" value="{{ old('due_date') }}"></div>
            <div class="col-md-6 col-12">
                <label class="form-label" for="vendor_name">Vendor</label>
                <input id="vendor_name" name="vendor_name" class="form-control" value="{{ old('vendor_name') }}" list="vendorList" required maxlength="255" autocomplete="off">
                <datalist id="vendorList">@foreach ($vendors as $v)<option value="{{ $v->vendor_name }}" data-phone="{{ $v->vendor_phone }}"></option>@endforeach</datalist>
            </div>
            <div class="col-md-6 col-12"><label class="form-label" for="vendor_phone">Phone</label><input id="vendor_phone" name="vendor_phone" class="form-control" value="{{ old('vendor_phone') }}" maxlength="50"></div>
        </div>
    </section>

    <section class="card pi-card">
        <div class="pi-head">
            <h3 class="pi-h">Items</h3>
            <span class="pi-draft" id="draftNote" hidden>Draft restored · <a href="#" id="draftClear">Discard</a></span>
            <button type="button" class="btn btn-outline-primary btn-sm" id="addRow">Add</button>
        </div>
        <div class="pi-rows" id="rows"></div>
    </section>

    <section class="card pi-card">
        <div class="row">
            <div class="col-lg-7">
                <div class="row">
                    <div class="col-sm-6"><label class="form-label" for="paid_amount">Paid</label><input id="paid_amount" type="number" step="0.01" min="0" name="paid_amount" class="form-control" value="{{ old('paid_amount', 0) }}"></div>
                    <div class="col-sm-6">
                        <label class="form-label" for="payment_method">Method</label>
                        <select id="payment_method" name="payment_method" class="form-select">
                            @foreach (\App\Models\PublisherPayment::paymentMethods() as $k => $v)<option value="{{ $k }}" @selected(old('payment_method') === $k)>{{ $v }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-sm-6"><label class="form-label" for="transaction_ref">Reference</label><input id="transaction_ref" name="transaction_ref" class="form-control" value="{{ old('transaction_ref') }}" maxlength="100"></div>
                    <div class="col-sm-6"><label class="form-label" for="notes">Note</label><input id="notes" name="notes" class="form-control" value="{{ old('notes') }}" maxlength="1000"></div>
                </div>
            </div>
            <div class="col-lg-5">
                <dl class="pi-totals">
                    <div><dt>Subtotal</dt><dd id="tSub">৳0.00</dd></div>
                    <div><dt>Discount</dt><dd><input type="number" step="0.01" min="0" name="discount" id="discount" class="form-control form-control-sm text-end" value="{{ old('discount', 0) }}"></dd></div>
                    <div class="is-grand"><dt>Total</dt><dd id="tGrand">৳0.00</dd></div>
                    <div><dt>Due</dt><dd id="tDue">৳0.00</dd></div>
                    <div><dt>Items · Qty</dt><dd id="tCount">0 · 0</dd></div>
                    <div><dt>Status</dt><dd><span class="pi-status" id="tStatus">Credit</span> <button type="button" class="btn btn-outline-secondary btn-sm" id="payFull">Full paid</button></dd></div>
                </dl>
            </div>
        </div>
    </section>

    <div class="pi-submit"><button class="btn btn-primary">Save</button></div>
</form>
</div>


<template id="rowTpl">
    <div class="pi-row">
        <div class="pi-item iv-pick">
            <label class="form-label">Item</label>
            <input type="search" class="form-control pi-search" autocomplete="off" aria-label="Item">
            <input type="hidden" name="items[__i__][product_id]" class="pi-pid">
            <input type="hidden" name="items[__i__][title]" class="pi-title">
            <div class="iv-pick-list" hidden></div>
            <div class="pi-hint" hidden></div>
        </div>
        <div class="pi-cat" hidden>
            <label class="form-label">Category</label>
            <select name="items[__i__][category_id]" class="form-select pi-catsel" disabled>
                <option value="">None</option>
                @foreach ($categories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
            </select>
        </div>
        <div><label class="form-label">Qty</label><input type="number" min="1" step="1" name="items[__i__][quantity]" class="form-control pi-qty" required></div>
        <div><label class="form-label">Cost</label><input type="number" min="0" step="0.01" name="items[__i__][cost_price]" class="form-control pi-cost" required></div>
        <div><label class="form-label">Sale</label><input type="number" min="0" step="0.01" name="items[__i__][sale_price]" class="form-control pi-sale"></div>
        <div class="pi-line"><label class="form-label">Line</label><output class="pi-sum">৳0.00</output></div>
        <button type="button" class="btn btn-outline-danger btn-sm pi-del" aria-label="Remove">Remove</button>
    </div>
</template>
@endsection
