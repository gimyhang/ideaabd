@extends('layouts.admin')

@section('title', 'নতুন বিল')
@section('heading', 'নতুন বিল তৈরি করুন')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('subadmin.bills.index') }}" class="text-decoration-none">বিল তালিকা</a></li>
    <li class="breadcrumb-item active" aria-current="page">নতুন</li>
@endsection

@section('actions')
    <a href="{{ route('subadmin.bills.index') }}" class="btn btn-outline-secondary"><i class="fas fa-list me-1"></i>সব বিল</a>
@endsection

@section('content')
<div style="max-width:900px">

    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <form method="POST" action="{{ route('subadmin.bills.store') }}" id="billForm">
        @csrf
        <div class="row g-4">

            {{-- Customer info --}}
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header border-0 py-3" style="background:#E8F4F8">
                        <h6 class="fw-bold mb-0"><i class="fas fa-user me-1 text-primary"></i>কাস্টমারের তথ্য</h6>
                    </div>
                    <div class="card-body p-3">
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">নাম <span class="text-danger">*</span></label>
                            <input type="text" name="customer_name" class="form-control" value="{{ old('customer_name') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">মোবাইল</label>
                            <input type="tel" name="customer_phone" class="form-control" value="{{ old('customer_phone') }}" placeholder="01XXXXXXXXX">
                        </div>
                        <div class="mb-0">
                            <label class="form-label fw-semibold small">ইমেইল</label>
                            <input type="email" name="customer_email" class="form-control" value="{{ old('customer_email') }}">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Payment --}}
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header border-0 py-3" style="background:#E8F4F8">
                        <h6 class="fw-bold mb-0"><i class="fas fa-credit-card me-1 text-primary"></i>পেমেন্ট তথ্য</h6>
                    </div>
                    <div class="card-body p-3">
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">পেমেন্ট মেথড <span class="text-danger">*</span></label>
                            <select name="payment_method" class="form-select" required>
                                <option value="cash" @selected(old('payment_method','cash')==='cash')>নগদ (Cash)</option>
                                <option value="bkash" @selected(old('payment_method')==='bkash')>বিকাশ (bKash)</option>
                                <option value="nagad" @selected(old('payment_method')==='nagad')>নগদ (Nagad)</option>
                                <option value="card" @selected(old('payment_method')==='card')>কার্ড (Card)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">পেমেন্ট স্ট্যাটাস <span class="text-danger">*</span></label>
                            <select name="payment_status" class="form-select" required>
                                <option value="paid" @selected(old('payment_status','paid')==='paid')>পরিশোধিত</option>
                                <option value="unpaid" @selected(old('payment_status')==='unpaid')>বকেয়া</option>
                                <option value="partial" @selected(old('payment_status')==='partial')>আংশিক</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">ডিসকাউন্ট (৳)</label>
                            <input type="number" name="discount" class="form-control" value="{{ old('discount',0) }}" min="0" step="0.01" id="discountInput">
                        </div>
                        <div>
                            <label class="form-label fw-semibold small">নোট</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="ঐচ্ছিক...">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Items --}}
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header border-0 py-3 d-flex justify-content-between align-items-center" style="background:#E8F4F8">
                        <h6 class="fw-bold mb-0"><i class="fas fa-book me-1 text-primary"></i>বইয়ের তালিকা</h6>
                        <button type="button" class="btn btn-sm btn-primary" id="addItem"><i class="fas fa-plus me-1"></i>বই যোগ করুন</button>
                    </div>
                    <div class="card-body p-3">
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-2" id="itemsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th style="min-width:220px">বইয়ের নাম</th>
                                        <th style="width:90px">পরিমাণ</th>
                                        <th style="width:120px">একক মূল্য (৳)</th>
                                        <th style="width:100px">মোট (৳)</th>
                                        <th style="width:50px"></th>
                                    </tr>
                                </thead>
                                <tbody id="itemsBody">
                                    <tr class="item-row">
                                        <td><input type="text" name="items[0][title]" class="form-control form-control-sm" required placeholder="বইয়ের নাম"></td>
                                        <td><input type="number" name="items[0][qty]" class="form-control form-control-sm qty" value="1" min="1" required></td>
                                        <td><input type="number" name="items[0][price]" class="form-control form-control-sm price" value="0" min="0" step="0.01" required></td>
                                        <td><span class="row-total fw-bold text-primary">0</span></td>
                                        <td></td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr><td colspan="3" class="text-end fw-semibold">সাবটোটাল:</td><td colspan="2"><span id="subtotalDisplay" class="fw-bold">0</span> ৳</td></tr>
                                    <tr><td colspan="3" class="text-end fw-semibold">ডিসকাউন্ট:</td><td colspan="2"><span id="discountDisplay" class="text-danger">0</span> ৳</td></tr>
                                    <tr class="table-active"><td colspan="3" class="text-end fw-bold fs-6">সর্বমোট:</td><td colspan="2"><span id="grandTotal" class="fw-bold text-success fs-6">0</span> ৳</td></tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4 d-flex gap-2 justify-content-end">
            <button type="reset" class="btn btn-outline-secondary px-4">রিসেট</button>
            <button type="submit" class="btn btn-primary px-5 fw-bold"><i class="fas fa-save me-1"></i>বিল সেভ করুন</button>
        </div>
    </form>
</div>

<script>
let idx = 1;
const recalc = () => {
    let sub = 0;
    document.querySelectorAll('.item-row').forEach(row => {
        const q = +row.querySelector('.qty').value || 0;
        const p = +row.querySelector('.price').value || 0;
        const t = q * p;
        row.querySelector('.row-total').textContent = t.toFixed(2);
        sub += t;
    });
    const disc = +document.getElementById('discountInput').value || 0;
    document.getElementById('subtotalDisplay').textContent = sub.toFixed(2);
    document.getElementById('discountDisplay').textContent = disc.toFixed(2);
    document.getElementById('grandTotal').textContent = (sub - disc).toFixed(2);
};

document.getElementById('itemsBody').addEventListener('input', recalc);
document.getElementById('discountInput').addEventListener('input', recalc);

document.getElementById('addItem').addEventListener('click', () => {
    const tr = document.createElement('tr');
    tr.className = 'item-row';
    tr.innerHTML = `
        <td><input type="text" name="items[${idx}][title]" class="form-control form-control-sm" required></td>
        <td><input type="number" name="items[${idx}][qty]" class="form-control form-control-sm qty" value="1" min="1" required></td>
        <td><input type="number" name="items[${idx}][price]" class="form-control form-control-sm price" value="0" min="0" step="0.01" required></td>
        <td><span class="row-total fw-bold text-primary">0</span></td>
        <td><button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="fas fa-times"></i></button></td>`;
    document.getElementById('itemsBody').appendChild(tr);
    tr.querySelector('.remove-row').addEventListener('click', () => { tr.remove(); recalc(); });
    idx++;
});
</script>
@endsection
