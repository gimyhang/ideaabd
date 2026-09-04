@extends('layouts.app')

@section('title', 'বিল ও বিক্রয় তালিকা — আইডিয়া প্রকাশন')

@section('content')
<div class="container-fluid py-4 px-md-4" style="max-width: 1440px;">

    @include('seller.partials.header')

    {{-- Flash Notifications --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-4 rounded-4 shadow-xs" role="alert">
            <i class="fas fa-circle-check fs-5 me-2.5 text-success"></i>
            <div class="fw-medium">{{ session('success') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center mb-4 rounded-4 shadow-xs" role="alert">
            <i class="fas fa-triangle-exclamation fs-5 me-2.5 text-danger"></i>
            <div class="fw-medium">{{ session('error') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- 1. Metrics & Financial Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white d-flex align-items-center gap-3 h-100">
                <div class="rounded-3 bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; font-size: 1.2rem;">
                    <i class="fas fa-file-invoice"></i>
                </div>
                <div>
                    <div class="small text-muted fw-semibold">সর্বমোট মেমো</div>
                    <div class="fs-4 fw-bold text-dark">@bn($stats['total'])</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white d-flex align-items-center gap-3 h-100">
                <div class="rounded-3 bg-info-subtle text-info d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; font-size: 1.2rem;">
                    <i class="fas fa-truck"></i>
                </div>
                <div>
                    <div class="small text-muted fw-semibold">ডেলিভারি চালান</div>
                    <div class="fs-4 fw-bold text-info">@bn($stats['challans'] ?? 0)</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white d-flex align-items-center gap-3 h-100">
                <div class="rounded-3 bg-success-subtle text-success d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; font-size: 1.2rem;">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div>
                    <div class="small text-muted fw-semibold">পরিশোধিত বিল</div>
                    <div class="fs-4 fw-bold text-success">@bn($stats['paid'])</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white d-flex align-items-center gap-3 h-100">
                <div class="rounded-3 bg-danger-subtle text-danger d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; font-size: 1.2rem;">
                    <i class="fas fa-clock"></i>
                </div>
                <div>
                    <div class="small text-muted fw-semibold">বকেয়া বিল</div>
                    <div class="fs-4 fw-bold text-danger">@bn($stats['unpaid'])</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white d-flex align-items-center gap-3 h-100">
                <div class="rounded-3 bg-success text-white d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; font-size: 1.2rem;">
                    <i class="fas fa-taka-sign"></i>
                </div>
                <div>
                    <div class="small text-muted fw-semibold">মোট আদায়</div>
                    <div class="fs-5 fw-bold text-success">৳@bn(number_format($stats['revenue'], 0))</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white d-flex align-items-center gap-3 h-100">
                <div class="rounded-3 bg-warning-subtle text-warning-emphasis d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; font-size: 1.2rem;">
                    <i class="fas fa-hand-holding-dollar"></i>
                </div>
                <div>
                    <div class="small text-muted fw-semibold">বকেয়া পাওনা</div>
                    <div class="fs-5 fw-bold text-danger">৳@bn(number_format($stats['due'] ?? 0, 0))</div>
                </div>
            </div>
        </div>
    </div>

    {{-- 2. Fast Date & Type Filter Pills --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <span class="small text-muted fw-bold me-1"><i class="fas fa-calendar-day me-1"></i>দ্রুত তারিখ:</span>
            <a href="{{ route('subadmin.bills.index') }}" class="btn btn-sm rounded-pill {{ !request('date_preset') ? 'btn-primary' : 'btn-outline-secondary' }}">
                সকল
            </a>
            <a href="{{ route('subadmin.bills.index', array_merge(request()->except('date_preset', 'page'), ['date_preset' => 'today'])) }}" 
               class="btn btn-sm rounded-pill {{ request('date_preset') === 'today' ? 'btn-primary' : 'btn-outline-secondary' }}">
                আজকে
            </a>
            <a href="{{ route('subadmin.bills.index', array_merge(request()->except('date_preset', 'page'), ['date_preset' => 'yesterday'])) }}" 
               class="btn btn-sm rounded-pill {{ request('date_preset') === 'yesterday' ? 'btn-primary' : 'btn-outline-secondary' }}">
                গতকাল
            </a>
            <a href="{{ route('subadmin.bills.index', array_merge(request()->except('date_preset', 'page'), ['date_preset' => 'this_week'])) }}" 
               class="btn btn-sm rounded-pill {{ request('date_preset') === 'this_week' ? 'btn-primary' : 'btn-outline-secondary' }}">
                এই সপ্তাহ
            </a>
            <a href="{{ route('subadmin.bills.index', array_merge(request()->except('date_preset', 'page'), ['date_preset' => 'this_month'])) }}" 
               class="btn btn-sm rounded-pill {{ request('date_preset') === 'this_month' ? 'btn-primary' : 'btn-outline-secondary' }}">
                এই মাস
            </a>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('subadmin.bills.create', ['type' => 'invoice']) }}" class="btn btn-sm btn-primary rounded-pill px-3 shadow-xs">
                <i class="fas fa-plus-circle me-1"></i> নতুন বিল তৈরি
            </a>
            <a href="{{ route('subadmin.bills.create', ['type' => 'challan']) }}" class="btn btn-sm btn-info text-white rounded-pill px-3 shadow-xs">
                <i class="fas fa-truck me-1"></i> নতুন চালান তৈরি
            </a>
            <a href="{{ route('subadmin.bills.export', request()->query()) }}" class="btn btn-sm btn-outline-success rounded-pill px-3">
                <i class="fas fa-file-excel me-1"></i> CSV এক্সপোর্ট
            </a>
        </div>
    </div>

    {{-- 3. Detailed Filter Search Form --}}
    <div class="card border-0 shadow-sm rounded-4 bg-white p-3 mb-4">
        <form method="GET" action="{{ route('subadmin.bills.index') }}" class="row g-2 align-items-center">
            @if(request('date_preset'))
                <input type="hidden" name="date_preset" value="{{ request('date_preset') }}">
            @endif

            <div class="col-12 col-md-3">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="মেমো নং, গ্রাহক, প্রতিষ্ঠান বা মোবাইল..." value="{{ request('search') }}">
                </div>
            </div>

            <div class="col-6 col-md-2">
                <select name="type" class="form-select form-select-sm">
                    <option value="">— সব ডকুমেন্টের ধরন —</option>
                    <option value="invoice" @selected(request('type') === 'invoice')>ক্যাশ মেমো / ইনভয়েস</option>
                    <option value="challan" @selected(request('type') === 'challan')>ডেলিভারি চালান</option>
                    <option value="quotation" @selected(request('type') === 'quotation')>কোটেশন / প্রফর্মা</option>
                </select>
            </div>
            
            @if($isAdmin && count($sellers) > 0)
                <div class="col-6 col-md-2">
                    <select name="seller_id" class="form-select form-select-sm">
                        <option value="">— সব সেলার / স্টাফ —</option>
                        @foreach ($sellers as $sId => $sName)
                            <option value="{{ $sId }}" @selected((string)request('seller_id') === (string)$sId)>
                                {{ $sName }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="col-6 col-md-2">
                <select name="payment_status" class="form-select form-select-sm">
                    <option value="">— সব পেমেন্ট স্ট্যাটাস —</option>
                    <option value="paid" @selected(request('payment_status') === 'paid')>পরিশোধিত (Paid)</option>
                    <option value="unpaid" @selected(request('payment_status') === 'unpaid')>বকেয়া (Unpaid)</option>
                    <option value="partial" @selected(request('payment_status') === 'partial')>আংশিক (Partial)</option>
                </select>
            </div>

            <div class="col-6 col-md-2">
                <select name="payment_method" class="form-select form-select-sm">
                    <option value="">— সব মেথড —</option>
                    <option value="cash" @selected(request('payment_method') === 'cash')>নগদ (Cash)</option>
                    <option value="bkash" @selected(request('payment_method') === 'bkash')>বিকাশ (bKash)</option>
                    <option value="nagad" @selected(request('payment_method') === 'nagad')>নগদ (Nagad)</option>
                    <option value="card" @selected(request('payment_method') === 'card')>কার্ড / ব্যাংক</option>
                </select>
            </div>

            <div class="col-12 col-md-1 text-end">
                <button type="submit" class="btn btn-primary btn-sm rounded-pill w-100">
                    <i class="fas fa-filter me-1"></i>ফিল্টার
                </button>
            </div>
        </form>
    </div>

    {{-- 4. Bills Table & Bulk Actions --}}
    <form method="POST" action="{{ route('subadmin.bills.bulk-action') }}" id="bulkActionForm">
        @csrf

        <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
            <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div class="d-flex align-items-center gap-2">
                    <input type="checkbox" id="selectAllCheckbox" class="form-check-input mt-0" title="সকল সিলেক্ট করুন">
                    <label for="selectAllCheckbox" class="small fw-bold text-dark mb-0 cursor-pointer">সব নির্বাচন</label>
                    <span class="text-muted small">| মোট বিল ও চালান: <strong>@bn($bills->total())</strong> টি</span>
                </div>

                {{-- Bulk Action Dropdown --}}
                <div class="d-flex align-items-center gap-2">
                    <select name="bulk_action" id="bulkActionSelect" class="form-select form-select-sm" style="width: 170px;">
                        <option value="">— বাল্ক অ্যাকশন —</option>
                        <option value="mark_paid">পরিশোধিত চিহ্নিত করুন</option>
                        <option value="delete">মুছে ফেলুন (Delete)</option>
                    </select>
                    <button type="submit" class="btn btn-sm btn-outline-secondary rounded-pill px-3" onclick="return confirmBulkAction()">
                        প্রয়োগ করুন
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-muted small text-uppercase">
                        <tr>
                            <th style="width: 40px;" class="text-center">#</th>
                            <th style="width: 160px;">মেমো নং ও ধরন</th>
                            <th>গ্রাহক ও প্রতিষ্ঠান</th>
                            @if($isAdmin)
                                <th>সেলার</th>
                            @endif
                            <th>তারিখ</th>
                            <th class="text-end">সর্বমোট (৳)</th>
                            <th class="text-end">পরিশোধিত</th>
                            <th class="text-end">বকেয়া</th>
                            <th class="text-center">স্ট্যাটাস</th>
                            <th class="text-center" style="width: 140px;">অ্যাকশন</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bills as $bill)
                            @php
                                $isCh = ($bill->type === 'challan');
                            @endphp
                            <tr>
                                <td class="text-center">
                                    <input type="checkbox" name="bill_ids[]" value="{{ $bill->id }}" class="form-check-input bill-checkbox mt-0">
                                </td>
                                <td>
                                    <div class="d-flex flex-column gap-1">
                                        <a href="{{ route('subadmin.bills.show', $bill) }}" class="fw-bold text-primary text-decoration-none font-monospace">
                                            #{{ $bill->bill_no }}
                                        </a>
                                        <span class="badge {{ $bill->type_badge_class }} px-2 py-0.5" style="font-size: 10px; width: fit-content;">
                                            {{ $bill->type_label }}
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $bill->customer_name }}</div>
                                    @if($bill->customer_org)
                                        <div class="small text-dark fw-semibold"><i class="fas fa-building text-muted me-1"></i>{{ $bill->customer_org }}</div>
                                    @endif
                                    @if($bill->customer_phone)
                                        <div class="small text-muted"><i class="fas fa-phone text-muted me-1"></i>{{ $bill->customer_phone }}</div>
                                    @endif
                                </td>
                                @if($isAdmin)
                                    <td>
                                        <span class="badge bg-light text-dark border">{{ $bill->seller->name ?? 'সেলার' }}</span>
                                    </td>
                                @endif
                                <td class="small text-muted">
                                    {{ ($bill->bill_date ?? $bill->created_at)->format('d M, Y') }}
                                </td>
                                <td class="text-end fw-bold text-dark font-monospace">
                                    ৳{{ number_format($bill->total, 2) }}
                                </td>
                                <td class="text-end fw-bold text-success font-monospace">
                                    ৳{{ number_format($bill->paid_amount ?? $bill->total, 2) }}
                                </td>
                                <td class="text-end fw-bold {{ $bill->due_amount > 0 ? 'text-danger' : 'text-muted' }} font-monospace">
                                    ৳{{ number_format($bill->due_amount, 2) }}
                                </td>
                                <td class="text-center">
                                    @if($bill->payment_status === 'paid')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 rounded-pill">
                                            <i class="fas fa-check-circle me-1"></i>পরিশোধিত
                                        </span>
                                    @elseif($bill->payment_status === 'partial')
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2.5 py-1 rounded-pill">
                                            <i class="fas fa-clock me-1"></i>আংশিক
                                        </span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1 rounded-pill">
                                            <i class="fas fa-triangle-exclamation me-1"></i>বকেয়া
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('subadmin.bills.show', $bill) }}" class="btn btn-outline-primary" title="মেমো ও চালান প্রিন্ট">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('subadmin.bills.receipt', $bill) }}" target="_blank" class="btn btn-outline-secondary" title="থার্মাল রিসিট">
                                            <i class="fas fa-receipt"></i>
                                        </a>
                                        <a href="{{ route('subadmin.bills.edit', $bill) }}" class="btn btn-outline-warning text-dark" title="সম্পাদনা">
                                            <i class="fas fa-pen-to-square"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $isAdmin ? 10 : 9 }}" class="text-center py-5 text-muted">
                                    <i class="fas fa-file-invoice-dollar fs-1 text-muted mb-2 d-block opacity-50"></i>
                                    কোনো বিল বা চালান পাওয়া যায়নি।
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($bills->hasPages())
                <div class="card-footer bg-white py-3 border-top d-flex justify-content-center">
                    {{ $bills->links() }}
                </div>
            @endif
        </div>
    </form>
</div>

@push('scripts')
<script>
document.getElementById('selectAllCheckbox').addEventListener('change', function() {
    document.querySelectorAll('.bill-checkbox').forEach(cb => cb.checked = this.checked);
});

function confirmBulkAction() {
    const action = document.getElementById('bulkActionSelect').value;
    if (!action) {
        alert('অনুগ্রহ করে একটি অ্যাকশন নির্বাচন করুন।');
        return false;
    }
    const checkedCount = document.querySelectorAll('.bill-checkbox:checked').length;
    if (checkedCount === 0) {
        alert('অনুগ্রহ করে কমপক্ষে একটি বিল নির্বাচন করুন।');
        return false;
    }
    if (action === 'delete') {
        return confirm(`আপনি কি নিশ্চিত যে নির্বাচিত ${checkedCount} টি বিল মুছে ফেলতে চান?`);
    }
    return confirm(`নির্বাচিত ${checkedCount} টি বিলে এই পরিবর্তনটি করতে চান?`);
}
</script>
@endpush
@endsection
