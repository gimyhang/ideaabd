@extends('layouts.admin')

@section('title', 'আইডিয়া প্রকাশন বিল ও চালান তালিকা')
@section('heading', 'আইডিয়া প্রকাশন বিল ও ডেলিভারি চালান তালিকা')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.accounting.index') }}">হিসাব ও আয়-ব্যয়</a></li>
    <li class="breadcrumb-item active" aria-current="page">বিল ও চালান</li>
@endsection

@section('actions')
    <a href="{{ route('admin.accounting.invoices.create') }}" class="btn btn-success">
        <i class="fas fa-plus me-1"></i> নতুন বিল / চালান তৈরি
    </a>
    <a href="{{ route('admin.accounting.index') }}" class="btn btn-outline-primary">
        <i class="fas fa-scale-balanced me-1"></i> আয়-ব্যয় খাতা দেখুন
    </a>
@endsection

@section('content')

{{-- Summary Stat Cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-primary">
            <span class="text-muted small fw-semibold">মোট বিল ও চালান</span>
            <h3 class="fw-bold mb-0 text-primary">@bn($stats['total_invoices']) টি</h3>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-dark">
            <span class="text-muted small fw-semibold">সর্বমোট বিল মূল্য</span>
            <h3 class="fw-bold mb-0 text-dark">@taka($stats['total_amount'])</h3>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-success">
            <span class="text-muted small fw-semibold">মোট আদায় / পরিশোধ</span>
            <h3 class="fw-bold mb-0 text-success">@taka($stats['total_paid'])</h3>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-danger">
            <span class="text-muted small fw-semibold">মোট বকেয়া (Due)</span>
            <h3 class="fw-bold mb-0 text-danger">@taka($stats['total_due'])</h3>
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
                    <input type="text" name="search" class="form-control" placeholder="ইনভয়েস # / গ্রাহকের নাম / ফোন..." value="{{ $search }}">
                </div>
            </div>
            <div class="col-md-2">
                <select name="type" class="form-select" onchange="this.form.submit()">
                    <option value="">সকল ধরন</option>
                    <option value="invoice" @selected($type === 'invoice')>বিল / ক্যাশ মেমো</option>
                    <option value="challan" @selected($type === 'challan')>ডেলিভারি চালান</option>
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
<div class="adm-card shadow-sm rounded-4 overflow-hidden mb-4">
    @if ($invoices->isEmpty())
        <div class="empty-state py-5 text-center">
            <i class="fas fa-file-invoice fs-1 text-muted opacity-50 mb-3"></i>
            <h5 class="fw-bold text-muted">কোনো বিল বা চালান পাওয়া যায়নি</h5>
            <p class="text-muted small">উপরের "নতুন বিল / চালান তৈরি" বাটনে ক্লিক করে প্রথম বিল/চালান তৈরি করুন।</p>
        </div>
    @else
        <div class="table-responsive">
            <table class="table adm-table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">চালান / ইনভয়েস #</th>
                        <th>ধরন</th>
                        <th>তারিখ</th>
                        <th>গ্রাহক / প্রতিষ্ঠান</th>
                        <th>আইটেম সংখ্যা</th>
                        <th>মোট টাকা</th>
                        <th>পরিশোধ</th>
                        <th>বকেয়া</th>
                        <th>স্ট্যাটাস</th>
                        <th class="text-center pe-3">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($invoices as $inv)
                        <tr>
                            <td class="ps-3 fw-bold text-primary">
                                <a href="{{ route('admin.accounting.invoices.show', $inv->id) }}" class="text-decoration-none text-primary">
                                    {{ $inv->invoice_no }}
                                </a>
                            </td>
                            <td>
                                @if($inv->type === 'challan')
                                    <span class="badge bg-info-subtle text-dark border px-2 py-1 rounded-pill">
                                        <i class="fas fa-truck me-1"></i>ডেলিভারি চালান
                                    </span>
                                @else
                                    <span class="badge bg-primary-subtle text-primary border px-2 py-1 rounded-pill">
                                        <i class="fas fa-file-invoice me-1"></i>বিল / মেমো
                                    </span>
                                @endif
                            </td>
                            <td class="text-muted small">@bnDate($inv->invoice_date)</td>
                            <td>
                                <div class="fw-bold text-dark">{{ $inv->customer_name }}</div>
                                @if($inv->customer_phone)
                                    <div class="text-muted small"><i class="fas fa-phone me-1"></i>{{ $inv->customer_phone }}</div>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">@bn(count($inv->items ?? [])) টি আইটেম</span>
                            </td>
                            <td class="fw-bold text-dark">@taka($inv->grand_total)</td>
                            <td class="fw-bold text-success">@taka($inv->paid_amount)</td>
                            <td class="fw-bold {{ $inv->due_amount > 0 ? 'text-danger' : 'text-muted' }}">
                                @taka($inv->due_amount)
                            </td>
                            <td>
                                @if($inv->payment_status === 'paid')
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
                                        <i class="fas fa-print"></i>
                                    </a>
                                    <form action="{{ route('admin.accounting.invoices.destroy', $inv->id) }}" method="POST" class="d-inline" onsubmit="return confirm('আপনি কি নিশ্চিতভাবে এই ইনভয়েসটি মুছে ফেলতে চান?')">
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

        @if ($invoices->hasPages())
            <div class="adm-card__foot d-flex flex-wrap justify-content-between align-items-center gap-2 p-3 bg-white border-top">
                <span class="text-muted small">
                    মোট @bn($invoices->total())টির মধ্যে @bn($invoices->firstItem())–@bn($invoices->lastItem()) দেখানো হচ্ছে
                </span>
                {{ $invoices->links() }}
            </div>
        @endif
    @endif
</div>

@endsection
