@extends('layouts.admin')

@section('title', 'প্রকাশনী ক্রয় ও হিসাব')
@section('heading', 'প্রকাশনী থেকে ক্রয় ও পেমেন্ট ব্যবস্থাপনা')
@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">প্রকাশনী ক্রয়</li>
@endsection

@section('actions')
    <a href="{{ route('admin.purchases.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i> নতুন ক্রয় এন্ট্রি করুন
    </a>
    <a href="{{ route('admin.purchases.payments') }}" class="btn btn-outline-success">
        <i class="fas fa-money-bill-transfer me-1"></i> পরিশোধ ও কিস্তি তালিকা
    </a>
@endsection

@section('content')

{{-- Summary Cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-primary">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold">মোট ক্রয় ইনভয়েস</span>
                    <h3 class="fw-bold mb-0 text-primary">@bn($stats['total_invoices'])</h3>
                </div>
                <div class="rounded-circle bg-primary-subtle text-primary p-3"><i class="fas fa-receipt fs-4"></i></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-dark">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold">সর্বমোট ক্রয় মূল্য</span>
                    <h3 class="fw-bold mb-0 text-dark">@taka($stats['total_purchase'])</h3>
                </div>
                <div class="rounded-circle bg-dark-subtle text-dark p-3"><i class="fas fa-cart-flatbed fs-4"></i></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-success">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold">মোট পরিশোধিত টাকা</span>
                    <h3 class="fw-bold mb-0 text-success">@taka($stats['total_paid'])</h3>
                </div>
                <div class="rounded-circle bg-success-subtle text-success p-3"><i class="fas fa-hand-holding-dollar fs-4"></i></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-danger">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold">সর্বমোট বকেয়া</span>
                    <h3 class="fw-bold mb-0 text-danger">@taka($stats['total_due'])</h3>
                </div>
                <div class="rounded-circle bg-danger-subtle text-danger p-3"><i class="fas fa-clock-rotate-left fs-4"></i></div>
            </div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-3">
        <form action="{{ route('admin.purchases.index') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-md-3">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="search" name="search" class="form-control border-start-0" 
                           placeholder="ইনভয়েস বা বই দিয়ে খুঁজুন..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3">
                <select name="publisher_id" class="form-select" onchange="this.form.submit()">
                    <option value="">সকল প্রকাশনী</option>
                    @foreach($publishers as $id => $name)
                        <option value="{{ $id }}" @selected(request('publisher_id') == $id)>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="payment_status" class="form-select" onchange="this.form.submit()">
                    <option value="all">সকল অবস্থা</option>
                    <option value="paid" @selected(request('payment_status') === 'paid')>পরিশোধিত (Paid)</option>
                    <option value="partial" @selected(request('payment_status') === 'partial')>আংশিক পরিশোধ (Partial)</option>
                    <option value="due" @selected(request('payment_status') === 'due')>সম্পূর্ণ বকেয়া (Due)</option>
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}" title="শুরুর তারিখ">
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter me-1"></i> ফিল্টার</button>
                @if(request()->hasAny(['search', 'publisher_id', 'payment_status', 'date_from']))
                    <a href="{{ route('admin.purchases.index') }}" class="btn btn-light border" title="রিসেট"><i class="fas fa-rotate-left"></i></a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- Purchase Invoices Table --}}
<div class="adm-card shadow-sm rounded-4 overflow-hidden">
    @if ($purchases->isEmpty())
        <div class="empty-state py-5 text-center">
            <i class="fas fa-receipt fs-1 text-muted opacity-50 mb-3"></i>
            <h5 class="fw-bold text-muted">কোনো প্রকাশনী ক্রয় ইনভয়েস পাওয়া যায়নি</h5>
            <p class="text-muted small">উপরের "নতুন ক্রয় এন্ট্রি করুন" বাটন দিয়ে প্রথম ক্রয় রেকর্ড তৈরি করুন।</p>
            <a href="{{ route('admin.purchases.create') }}" class="btn btn-primary rounded-pill px-4">
                <i class="fas fa-plus me-1"></i> ক্রয় রেকর্ড যোগ করুন
            </a>
        </div>
    @else
        <div class="table-responsive">
            <table class="table adm-table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">ইনভয়েস #</th>
                        <th>প্রকাশনী</th>
                        <th>তারিখ</th>
                        <th>বইয়ের সংখ্যা</th>
                        <th>মোট ক্রয়মূল্য</th>
                        <th>পরিশোধ</th>
                        <th>বকেয়া</th>
                        <th>অবস্থা</th>
                        <th class="text-end pe-3" style="min-width: 140px;">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($purchases as $p)
                        <tr>
                            <td class="ps-3">
                                <a href="{{ route('admin.purchases.show', $p->id) }}" class="fw-bold text-decoration-none text-primary">
                                    {{ $p->purchase_no }}
                                </a>
                                @if($p->publisher_memo_no)
                                    <div class="small text-muted"><i class="fas fa-receipt me-1"></i>মেমো: {{ $p->publisher_memo_no }}</div>
                                @endif
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ $p->publisher->name ?? '—' }}</div>
                                <div class="text-muted small"><i class="fas fa-phone me-1"></i>{{ $p->publisher->phone ?? '—' }}</div>
                            </td>
                            <td class="text-muted small">@bnDate($p->purchase_date)</td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    <i class="fas fa-book me-1 text-primary"></i>@bn($p->items->sum('quantity')) টি বই
                                </span>
                            </td>
                            <td class="fw-bold text-dark">@taka($p->grand_total)</td>
                            <td class="text-success fw-bold">@taka($p->paid_amount)</td>
                            <td class="text-danger fw-bold">
                                @if($p->due_amount > 0)
                                    @taka($p->due_amount)
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if($p->payment_status === 'paid')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 rounded-pill">
                                        <i class="fas fa-circle-check me-1"></i> পরিশোধিত
                                    </span>
                                @elseif($p->payment_status === 'partial')
                                    <span class="badge bg-warning-subtle text-dark border border-warning-subtle px-2.5 py-1 rounded-pill">
                                        <i class="fas fa-circle-half-stroke me-1"></i> আংশিক বকেয়া
                                    </span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1 rounded-pill">
                                        <i class="fas fa-circle-exclamation me-1"></i> সম্পূর্ণ বকেয়া
                                    </span>
                                @endif
                            </td>
                            <td class="text-end pe-3">
                                <div class="d-inline-flex gap-1 align-items-center">
                                    <a href="{{ route('admin.purchases.show', $p->id) }}" 
                                       class="btn btn-sm btn-primary px-2.5 py-1" title="ইনভয়েস ও কিস্তি পেমেন্ট দেখুন">
                                        <i class="fas fa-file-invoice me-1"></i> ইনভয়েস
                                    </a>

                                    <form action="{{ route('admin.purchases.destroy', $p->id) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('আপনি কি নিশ্চিত যে এই ক্রয় ইনভয়েসটি মুছে ফেলতে চান?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger px-2 py-1" title="মুছে ফেলুন">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($purchases->hasPages())
            <div class="adm-card__foot d-flex flex-wrap justify-content-between align-items-center gap-2 p-3 bg-white border-top">
                <span class="text-muted small">
                    মোট @bn($purchases->total())টির মধ্যে @bn($purchases->firstItem())–@bn($purchases->lastItem()) দেখানো হচ্ছে
                </span>
                {{ $purchases->links() }}
            </div>
        @endif
    @endif
</div>

@endsection
