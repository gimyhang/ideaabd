@extends('layouts.admin')

@section('title', 'আইডিয়া প্রকাশন বিল, চালান ও দরপত্র তালিকা')
@section('heading', 'আইডিয়া প্রকাশন বিল, চালান, কোটেশন ও দরপত্র তালিকা')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.accounting.index') }}">হিসাব ও আয়-ব্যয়</a></li>
    <li class="breadcrumb-item active" aria-current="page">বিল, চালান ও দরপত্র</li>
@endsection

@section('actions')
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('admin.accounting.invoices.create') }}" class="btn btn-success fw-semibold shadow-sm">
            <i class="fas fa-plus me-1"></i> নতুন তৈরি করুন
        </a>
        <a href="{{ route('admin.accounting.invoices.create', ['type' => 'quotation']) }}" class="btn btn-warning fw-semibold text-dark shadow-sm">
            <i class="fas fa-file-lines me-1"></i> + কোটেশন
        </a>
        <a href="{{ route('admin.accounting.invoices.create', ['type' => 'tender']) }}" class="btn btn-purple text-white fw-semibold shadow-sm" style="background-color: #6f42c1;">
            <i class="fas fa-landmark me-1"></i> + দরপত্র
        </a>
        <a href="{{ route('admin.accounting.index') }}" class="btn btn-outline-primary fw-semibold">
            <i class="fas fa-scale-balanced me-1"></i> আয়-ব্যয় খাতা
        </a>
    </div>
@endsection

@section('content')

{{-- Summary Stat Cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-primary">
            <span class="text-muted small fw-semibold">মোট ডকুমেন্টস</span>
            <h3 class="fw-bold mb-0 text-primary">@bn($stats['total_invoices']) টি</h3>
            <div class="text-muted small mt-1" style="font-size: 11.5px;">
                বিল: @bn($stats['total_bills']) | চালান: @bn($stats['total_challans'])
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4" style="border-left-color: #6f42c1 !important;">
            <span class="text-muted small fw-semibold">কোটেশন ও দরপত্র</span>
            <h3 class="fw-bold mb-0" style="color: #6f42c1;">@bn($stats['total_quotations'] + $stats['total_tenders']) টি</h3>
            <div class="text-muted small mt-1" style="font-size: 11.5px;">
                কোটেশন: @bn($stats['total_quotations']) | দরপত্র: @bn($stats['total_tenders'])
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-success">
            <span class="text-muted small fw-semibold">মোট আদায় / পরিশোধ</span>
            <h3 class="fw-bold mb-0 text-success">@taka($stats['total_paid'])</h3>
            <div class="text-muted small mt-1" style="font-size: 11.5px;">বিক্রয় মূল্য: @taka($stats['total_amount'])</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-danger">
            <span class="text-muted small fw-semibold">মোট বকেয়া (Due)</span>
            <h3 class="fw-bold mb-0 text-danger">@taka($stats['total_due'])</h3>
            <div class="text-muted small mt-1" style="font-size: 11.5px;">চালান ও বিলের বাকি</div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-3">
        <form action="{{ route('admin.accounting.invoices.index') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-md-3">
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="fas fa-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="ডকুমেন্ট # / বিষয় / গ্রাহকের নাম..." value="{{ $search }}">
                </div>
            </div>
            <div class="col-md-2">
                <select name="type" class="form-select" onchange="this.form.submit()">
                    <option value="">সকল ধরন (All)</option>
                    <option value="invoice" @selected($type === 'invoice')>বিল / ক্যাশ মেমো</option>
                    <option value="challan" @selected($type === 'challan')>ডেলিভারি চালান</option>
                    <option value="quotation" @selected($type === 'quotation')>কোটেশন / প্রফর্মা</option>
                    <option value="tender" @selected($type === 'tender')>দরপত্র (Tender)</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="payment_status" class="form-select" onchange="this.form.submit()">
                    <option value="">পেমেন্ট স্ট্যাটাস</option>
                    <option value="paid" @selected($status === 'paid')>পরিশোধিত (Paid)</option>
                    <option value="partial" @selected($status === 'partial')>আংশিক বকেয়া</option>
                    <option value="unpaid" @selected($status === 'unpaid')>বকেয়া (Unpaid)</option>
                </select>
            </div>
            <div class="col-md-3">
                <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}" title="তারিখ">
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter me-1"></i> ফিল্টার</button>
                @if(request()->hasAny(['search', 'type', 'payment_status', 'date_from', 'date_to']))
                    <a href="{{ route('admin.accounting.invoices.index') }}" class="btn btn-light border" title="রিসেট"><i class="fas fa-rotate-left"></i></a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- Invoices Table --}}
<div class="adm-card shadow-sm rounded-4 overflow-hidden mb-4 bg-white">
    @if ($invoices->isEmpty())
        <div class="empty-state py-5 text-center">
            <i class="fas fa-file-invoice fs-1 text-muted opacity-50 mb-3"></i>
            <h5 class="fw-bold text-muted">কোনো ডকুমেন্ট পাওয়া যায়নি</h5>
            <p class="text-muted small">উপরের বাটনে ক্লিক করে নতুন বিল, ডেলিভারি চালান, কোটেশন বা দরপত্র তৈরি করুন।</p>
        </div>
    @else
        <div class="table-responsive">
            <table class="table adm-table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">ডকুমেন্ট নং #</th>
                        <th>ধরন</th>
                        <th>তারিখ</th>
                        <th>গ্রাহক / প্রতিষ্ঠান</th>
                        <th>আইটেম</th>
                        <th>মোট প্রস্তাবনা / মূল্য</th>
                        <th>পরিশোধ</th>
                        <th>বকেয়া</th>
                        <th>স্ট্যাটাস</th>
                        <th class="text-center pe-3">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($invoices as $inv)
                        <tr>
                            <td class="ps-3 fw-bold text-primary font-monospace">
                                <a href="{{ route('admin.accounting.invoices.show', $inv->id) }}" class="text-decoration-none text-primary">
                                    {{ $inv->invoice_no }}
                                </a>
                                @if($inv->reference_no)
                                    <div class="text-muted small fw-normal" style="font-size: 11px;">স্মারক: {{ $inv->reference_no }}</div>
                                @endif
                            </td>
                            <td>
                                @if($inv->type === 'tender')
                                    <span class="badge border px-2.5 py-1 rounded-pill" style="background-color: #f3e8ff; color: #7e22ce; border-color: #d8b4fe;">
                                        <i class="fas fa-landmark me-1"></i>দরপত্র
                                    </span>
                                @elseif($inv->type === 'quotation')
                                    <span class="badge border px-2.5 py-1 rounded-pill" style="background-color: #fef3c7; color: #b45309; border-color: #fcd34d;">
                                        <i class="fas fa-file-lines me-1"></i>কোটেশন
                                    </span>
                                @elseif($inv->type === 'challan')
                                    <span class="badge bg-info-subtle text-dark border border-info-subtle px-2.5 py-1 rounded-pill">
                                        <i class="fas fa-truck me-1"></i>চালান
                                    </span>
                                @else
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 rounded-pill">
                                        <i class="fas fa-receipt me-1"></i>বিল / মেমো
                                    </span>
                                @endif
                            </td>
                            <td class="text-muted small">
                                @bnDate($inv->invoice_date)
                                @if($inv->valid_until)
                                    <div class="text-danger" style="font-size: 10.5px;">মেয়াদ: @bnDate($inv->valid_until)</div>
                                @endif
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ $inv->customer_name }}</div>
                                @if($inv->subject)
                                    <div class="text-muted small" style="font-size: 11.5px; max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $inv->subject }}">
                                        {{ $inv->subject }}
                                    </div>
                                @elseif($inv->customer_phone)
                                    <div class="text-muted small"><i class="fas fa-phone me-1"></i>{{ $inv->customer_phone }}</div>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">@bn(count($inv->items ?? [])) টি</span>
                            </td>
                            <td class="fw-bold text-dark">@taka($inv->grand_total)</td>
                            <td class="fw-bold text-success">
                                @if(in_array($inv->type, ['invoice', 'challan']))
                                    @taka($inv->paid_amount)
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td class="fw-bold {{ $inv->due_amount > 0 ? 'text-danger' : 'text-muted' }}">
                                @if(in_array($inv->type, ['invoice', 'challan']))
                                    @taka($inv->due_amount)
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td>
                                @if(in_array($inv->type, ['quotation', 'tender']))
                                    <span class="badge bg-light text-dark border px-2 py-1 rounded-pill">
                                        প্রস্তাবিত
                                    </span>
                                @elseif($inv->payment_status === 'paid')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 rounded-pill">
                                        পরিশোধিত
                                    </span>
                                @elseif($inv->payment_status === 'partial')
                                    <span class="badge bg-warning-subtle text-dark border border-warning-subtle px-2.5 py-1 rounded-pill">
                                        আংশিক বাকি
                                    </span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1 rounded-pill">
                                        বকেয়া
                                    </span>
                                @endif
                            </td>
                            <td class="text-center pe-3">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.accounting.invoices.show', $inv->id) }}" class="btn btn-outline-primary" title="দেখুন ও প্রিন্ট করুন">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <form action="{{ route('admin.accounting.invoices.destroy', $inv->id) }}" method="POST" class="d-inline" onsubmit="return confirm('আপনি কি নিশ্চিতভাবে এই ডকুমেন্টটি মুছে ফেলতে চান?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="মুছে ফেলুন">
                                            <i class="fas fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($invoices->hasPages())
            <div class="p-3 border-top d-flex justify-content-end">
                {{ $invoices->links() }}
            </div>
        @endif
    @endif
</div>

@endsection
