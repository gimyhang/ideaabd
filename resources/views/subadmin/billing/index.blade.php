@extends('layouts.admin')

@section('title', 'বিল ও বিক্রয় তালিকা — আইডিয়া প্রকাশন')
@section('heading', $isAdmin ? 'সকল বিল ও বিক্রয় তালিকা' : 'আমার বিলসমূহ')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('subadmin.bills.index') }}" class="text-decoration-none">সেলার প্যানেল</a></li>
    <li class="breadcrumb-item active" aria-current="page">বিল তালিকা</li>
@endsection

@section('actions')
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('subadmin.bills.export', request()->query()) }}" class="btn btn-outline-success" title="বর্তমান ফিল্টার করা বিল CSV ডাউনলোড করুন">
            <i class="fas fa-file-csv me-1"></i> এক্সপোর্ট CSV
        </a>
        <a href="{{ route('subadmin.accounts') }}" class="btn btn-outline-info" title="সেলার ক্যাশ ও ব্যালেন্স লেজার">
            <i class="fas fa-wallet me-1"></i> হিসাব বিবরণী
        </a>
        <a href="{{ route('subadmin.bills.create') }}" class="btn btn-primary shadow-sm">
            <i class="fas fa-plus-circle me-1"></i> নতুন বিল তৈরি
        </a>
    </div>
@endsection

@section('content')
<div>
    {{-- 1. Metrics & Financial Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-4 col-xl-2">
            <div class="adm-card p-3 d-flex align-items-center gap-3">
                <div class="rounded-3 bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; font-size: 1.2rem;">
                    <i class="fas fa-file-invoice"></i>
                </div>
                <div>
                    <div class="small text-muted fw-semibold">সর্বমোট বিল</div>
                    <div class="fs-4 fw-bold text-dark">@bn($stats['total'])</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="adm-card p-3 d-flex align-items-center gap-3">
                <div class="rounded-3 bg-success-subtle text-success d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; font-size: 1.2rem;">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div>
                    <div class="small text-muted fw-semibold">পরিশোধিত</div>
                    <div class="fs-4 fw-bold text-success">@bn($stats['paid'])</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="adm-card p-3 d-flex align-items-center gap-3">
                <div class="rounded-3 bg-danger-subtle text-danger d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; font-size: 1.2rem;">
                    <i class="fas fa-clock"></i>
                </div>
                <div>
                    <div class="small text-muted fw-semibold">বকেয়া বিল</div>
                    <div class="fs-4 fw-bold text-danger">@bn($stats['unpaid'])</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-6 col-xl-3">
            <div class="adm-card p-3 d-flex align-items-center gap-3">
                <div class="rounded-3 bg-success text-white d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; font-size: 1.2rem;">
                    <i class="fas fa-taka-sign"></i>
                </div>
                <div>
                    <div class="small text-muted fw-semibold">মোট ক্যাশ আদায়</div>
                    <div class="fs-4 fw-bold text-success">৳@bn(number_format($stats['revenue'], 0))</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-6 col-xl-3">
            <div class="adm-card p-3 d-flex align-items-center gap-3">
                <div class="rounded-3 bg-warning-subtle text-warning-emphasis d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; font-size: 1.2rem;">
                    <i class="fas fa-hand-holding-dollar"></i>
                </div>
                <div>
                    <div class="small text-muted fw-semibold">মোট বকেয়া পাওনা</div>
                    <div class="fs-4 fw-bold text-danger">৳@bn(number_format($stats['due'] ?? 0, 0))</div>
                </div>
            </div>
        </div>
    </div>

    {{-- 2. Fast Date Filter Pills --}}
    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
        <span class="small text-muted fw-bold me-1"><i class="fas fa-calendar-day me-1"></i>দ্রুত ফিল্টার:</span>
        <a href="{{ route('subadmin.bills.index') }}" class="btn btn-sm rounded-pill {{ !request('date_preset') ? 'btn-primary' : 'btn-outline-secondary' }}">
            সকল বিল
        </a>
        <a href="{{ route('subadmin.bills.index', array_merge(request()->except('date_preset', 'page'), ['date_preset' => 'today'])) }}" 
           class="btn btn-sm rounded-pill {{ request('date_preset') === 'today' ? 'btn-primary' : 'btn-outline-secondary' }}">
            আজকের বিক্রয়
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

    {{-- 3. Detailed Filter Search Form --}}
    <div class="adm-card p-3 mb-4">
        <form method="GET" action="{{ route('subadmin.bills.index') }}" class="row g-2 align-items-center">
            @if(request('date_preset'))
                <input type="hidden" name="date_preset" value="{{ request('date_preset') }}">
            @endif

            <div class="col-12 col-md-3">
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="বিল নং, নাম বা মোবাইল..." value="{{ request('search') }}">
                </div>
            </div>
            
            @if($isAdmin && count($sellers) > 0)
                <div class="col-6 col-md-2">
                    <select name="seller_id" class="form-select">
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
                <select name="payment_status" class="form-select">
                    <option value="">— সব পেমেন্ট স্ট্যাটাস —</option>
                    <option value="paid" @selected(request('payment_status') === 'paid')>পরিশোধিত (Paid)</option>
                    <option value="unpaid" @selected(request('payment_status') === 'unpaid')>বকেয়া (Unpaid)</option>
                    <option value="partial" @selected(request('payment_status') === 'partial')>আংশিক (Partial)</option>
                </select>
            </div>

            <div class="col-6 col-md-2">
                <select name="payment_method" class="form-select">
                    <option value="">— পেমেন্ট মেথড —</option>
                    <option value="cash" @selected(request('payment_method') === 'cash')>ক্যাশ (Cash)</option>
                    <option value="bkash" @selected(request('payment_method') === 'bkash')>বিকাশ (bKash)</option>
                    <option value="nagad" @selected(request('payment_method') === 'nagad')>নগদ (Nagad)</option>
                    <option value="card" @selected(request('payment_method') === 'card')>কার্ড / ব্যাংক</option>
                </select>
            </div>

            <div class="col-6 col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1"><i class="fas fa-filter me-1"></i>ফিল্টার</button>
                @if(request()->hasAny(['search', 'seller_id', 'payment_status', 'payment_method', 'date_preset', 'from_date', 'to_date']))
                    <a href="{{ route('subadmin.bills.index') }}" class="btn btn-outline-secondary" title="ফিল্টার রিসেট"><i class="fas fa-rotate-left"></i></a>
                @endif
            </div>
        </form>
    </div>

    {{-- 4. Interactive Bills Table with Bulk Actions --}}
    <form method="POST" action="{{ route('subadmin.bills.bulk-action') }}" id="bulkActionForm">
        @csrf
        <div class="adm-card p-0 overflow-hidden shadow-sm">
            <div class="p-3 bg-light border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div class="d-flex align-items-center gap-2">
                    <select name="bulk_action" id="bulkActionSelect" class="form-select form-select-sm" style="width: 200px;">
                        <option value="">— সিলেক্ট করা বিল একশন —</option>
                        <option value="mark_paid">চিহ্নিত বিল পরিশোধিত করুন</option>
                        <option value="delete">চিহ্নিত বিল মুছে ফেলুন</option>
                    </select>
                    <button type="submit" class="btn btn-sm btn-outline-dark" onclick="return confirm('আপনি কি নিশ্চিত যে সিলেক্ট করা বিলগুলোর উপর এই একশন চালাতে চান?');">
                        প্রয়োগ করুন
                    </button>
                </div>
                <div class="small text-muted">
                    মোট প্রদর্শিত: <strong>@bn($bills->total())</strong> টি বিল
                </div>
            </div>

            <div class="table-responsive mb-0">
                <table class="table table-hover align-middle mb-0" style="font-size: 0.92rem;">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3 py-3" style="width: 35px;">
                                <input type="checkbox" class="form-check-input" id="selectAllCheckbox">
                            </th>
                            <th class="py-3">বিল নম্বর</th>
                            <th class="py-3">গ্রাহক</th>
                            @if($isAdmin)<th class="py-3">সেলার</th>@endif
                            <th class="py-3 text-center">বই সংখ্যা</th>
                            <th class="py-3 text-end">মোট মূল্য</th>
                            <th class="py-3 text-center">পেমেন্ট মেথড</th>
                            <th class="py-3 text-center">স্ট্যাটাস</th>
                            <th class="py-3 text-center">তারিখ</th>
                            <th class="pe-3 py-3 text-end" style="width: 170px;">অ্যাকশন</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bills as $bill)
                        @php
                            $cleanPhone = preg_replace('/[^0-9]/', '', $bill->customer_phone ?? '');
                            if (str_starts_with($cleanPhone, '01')) {
                                $waPhone = '88' . $cleanPhone;
                            } else {
                                $waPhone = $cleanPhone;
                            }
                            $waMessage = urlencode("প্রিয় {$bill->customer_name}, আইডিয়া প্রকাশন থেকে আপনার বিল নম্বর: #{$bill->bill_no}। মোট মূল্য: ৳{$bill->total}। ধন্যবাদ!");
                        @endphp
                        <tr>
                            <td class="ps-3">
                                <input type="checkbox" name="bill_ids[]" value="{{ $bill->id }}" class="form-check-input bill-checkbox">
                            </td>
                            <td class="fw-bold font-monospace">
                                <a href="{{ route('subadmin.bills.show', $bill) }}" class="text-decoration-none text-primary fw-bold">
                                    #{{ $bill->bill_no }}
                                </a>
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ $bill->customer_name }}</div>
                                @if($bill->customer_phone)
                                    <small class="text-muted d-block">
                                        <i class="fas fa-phone-alt me-1 text-success"></i>{{ $bill->customer_phone }}
                                    </small>
                                @elseif($bill->customer_email)
                                    <small class="text-muted d-block">{{ $bill->customer_email }}</small>
                                @endif
                            </td>
                            @if($isAdmin)
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        <i class="fas fa-user-tie me-1 text-primary"></i>{{ $bill->seller->name ?? 'সেলার' }}
                                    </span>
                                </td>
                            @endif
                            <td class="text-center">
                                <span class="badge bg-secondary-subtle text-secondary fw-semibold">
                                    @bn(count($bill->items ?? [])) টি বই
                                </span>
                            </td>
                            <td class="text-end fw-bold text-dark fs-6">
                                ৳@bn(number_format($bill->total, 2))
                            </td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark border font-monospace text-uppercase" style="font-size: 0.75rem;">
                                    {{ $bill->payment_method ?? 'CASH' }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if($bill->payment_status === 'paid')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1">
                                        <i class="fas fa-check-circle me-1"></i>পরিশোধিত
                                    </span>
                                @elseif($bill->payment_status === 'partial')
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2.5 py-1">
                                        <i class="fas fa-clock me-1"></i>আংশিক
                                    </span>
                                @else
                                    <form method="POST" action="{{ route('subadmin.bills.quick-pay', $bill) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 btn p-0 text-decoration-none" title="১-ক্লিকে পরিশোধিত চিহ্নিত করুন">
                                            <i class="fas fa-exclamation-circle me-1"></i>বকেয়া (Pay)
                                        </button>
                                    </form>
                                @endif
                            </td>
                            <td class="text-center small text-muted">
                                {{ $bill->created_at->format('d M Y') }}<br>
                                <span style="font-size: 11px;">{{ $bill->created_at->format('h:i A') }}</span>
                            </td>
                            <td class="pe-3 text-end">
                                <div class="btn-group btn-group-sm">
                                    {{-- 1. Thermal POS Receipt --}}
                                    <a href="{{ route('subadmin.bills.receipt', $bill) }}" target="_blank" class="btn btn-outline-dark" title="থার্মাল রিসিট প্রিন্ট (58/80mm)">
                                        <i class="fas fa-receipt"></i>
                                    </a>
                                    {{-- 2. View Full Invoice --}}
                                    <a href="{{ route('subadmin.bills.show', $bill) }}" class="btn btn-outline-primary" title="সম্পূর্ণ ইনভয়েস দেখুন">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    {{-- 3. WhatsApp Direct Share --}}
                                    @if($waPhone)
                                        <a href="https://wa.me/{{ $waPhone }}?text={{ $waMessage }}" target="_blank" class="btn btn-outline-success" title="হোয়াটসঅ্যাপে বিল পাঠান">
                                            <i class="fab fa-whatsapp"></i>
                                        </a>
                                    @endif
                                    {{-- 4. Edit Bill --}}
                                    <a href="{{ route('subadmin.bills.edit', $bill) }}" class="btn btn-outline-secondary" title="সম্পাদনা করুন">
                                        <i class="fas fa-pen-to-square"></i>
                                    </a>
                                    {{-- 5. Delete --}}
                                    <button type="button" class="btn btn-outline-danger" title="মুছে ফেলুন" onclick="confirmDeleteBill('{{ route('subadmin.bills.destroy', $bill) }}')">
                                        <i class="fas fa-trash-can"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ $isAdmin ? '10' : '9' }}" class="text-center py-5 text-muted">
                                <i class="fas fa-file-invoice fs-2 mb-2 d-block text-muted opacity-50"></i>
                                কোনো বিল পাওয়া যায়নি — <a href="{{ route('subadmin.bills.create') }}" class="text-primary fw-bold text-decoration-none">নতুন বিল তৈরি করুন</a>।
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($bills->hasPages())
                <div class="px-3 py-3 border-top">
                    {{ $bills->links() }}
                </div>
            @endif
        </div>
    </form>
</div>

{{-- Hidden Delete Form --}}
<form id="deleteBillForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

@push('scripts')
<script>
    // Select All Checkboxes
    document.getElementById('selectAllCheckbox')?.addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.bill-checkbox');
        checkboxes.forEach(cb => cb.checked = this.checked);
    });

    function confirmDeleteBill(actionUrl) {
        if (confirm('আপনি কি নিশ্চিত যে এই বিলটি মুছে ফেলতে চান?')) {
            const form = document.getElementById('deleteBillForm');
            form.action = actionUrl;
            form.submit();
        }
    }
</script>
@endpush
@endsection
