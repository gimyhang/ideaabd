@extends('layouts.admin')

@section('title', 'আইডিয়া প্রকাশন হিসাব ও আয়-ব্যয় খাতা')
@section('heading', 'আইডিয়া প্রকাশন আয়-ব্যয় ও ক্রয় হিসাব খাতা')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">ড্যাশবোর্ড</a></li>
    <li class="breadcrumb-item active" aria-current="page">হিসাব ও আয়-ব্যয়</li>
@endsection

@section('actions')
    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#newExpenseModal">
        <i class="fas fa-minus-circle me-1"></i> নতুন ব্যয় / ক্রয় এন্ট্রি
    </button>
    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#newIncomeModal">
        <i class="fas fa-plus-circle me-1"></i> নতুন আয় এন্ট্রি
    </button>
    <a href="{{ route('admin.accounting.invoices.create') }}" class="btn btn-primary">
        <i class="fas fa-file-invoice me-1"></i> বিল ও চালান তৈরি
    </a>
@endsection

@section('content')

{{-- Idea Accounting Unified Navigation Bar --}}
<div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
    <div class="card-body p-2">
        <div class="nav nav-pills gap-1.5 flex-wrap">
            <a href="{{ route('admin.accounting.index') }}" 
               class="nav-link rounded-pill px-3.5 py-2 fw-semibold active bg-primary text-white shadow-sm">
                <i class="fas fa-scale-balanced me-1.5"></i> আয়-ব্যয় ও হিসাব খাতা
            </a>
            <a href="{{ route('admin.accounting.invoices.index') }}" 
               class="nav-link rounded-pill px-3.5 py-2 fw-semibold text-dark hover-bg-light">
                <i class="fas fa-file-invoice-dollar me-1.5"></i> বিল, চালান ও দরপত্র তালিকা
            </a>
            <a href="{{ route('admin.accounting.invoices.create') }}" 
               class="nav-link rounded-pill px-3.5 py-2 fw-semibold text-dark hover-bg-light">
                <i class="fas fa-file-circle-plus me-1.5"></i> নতুন বিল, চালান ও দরপত্র তৈরি
            </a>
        </div>
    </div>
</div>

{{-- Financial Stat Cards --}}
<div class="row g-3 mb-4">
    <div class="col-12 col-md-4">
        <div class="card border-0 shadow-sm rounded-4 p-3 p-md-4 bg-white border-start border-4 border-success">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold">সর্বমোট আয় (Total Income)</span>
                    <h3 class="fw-bold mb-0 text-success">@taka($totalIncome)</h3>
                </div>
                <div class="rounded-circle bg-success-subtle text-success p-3"><i class="fas fa-arrow-trend-up fs-4"></i></div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="card border-0 shadow-sm rounded-4 p-3 p-md-4 bg-white border-start border-4 border-danger">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold">সর্বমোট ব্যয় ও ক্রয় (Total Expenses)</span>
                    <h3 class="fw-bold mb-0 text-danger">@taka($totalExpense)</h3>
                </div>
                <div class="rounded-circle bg-danger-subtle text-danger p-3"><i class="fas fa-arrow-trend-down fs-4"></i></div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="card border-0 shadow-sm rounded-4 p-3 p-md-4 bg-white border-start border-4 {{ $netBalance >= 0 ? 'border-primary' : 'border-warning' }}">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold">নিট ব্যালেন্স / তহবিল (Net Balance)</span>
                    <h3 class="fw-bold mb-0 {{ $netBalance >= 0 ? 'text-primary' : 'text-danger' }}">@taka($netBalance)</h3>
                </div>
                <div class="rounded-circle {{ $netBalance >= 0 ? 'bg-primary-subtle text-primary' : 'bg-warning-subtle text-warning' }} p-3">
                    <i class="fas fa-scale-balanced fs-4"></i>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Top Expense Sectors Summary Pill Carousel --}}
@if($expenseBreakdown->isNotEmpty())
<div class="card border-0 shadow-sm rounded-4 mb-4 p-3 bg-white">
    <span class="small fw-bold text-muted text-uppercase mb-2 d-block"><i class="fas fa-chart-pie me-1 text-danger"></i>খাতভিত্তিক প্রধান ব্যয়সমূহ:</span>
    <div class="d-flex flex-wrap gap-2">
        @foreach($expenseBreakdown as $exp)
            <div class="badge bg-light text-dark border p-2 rounded-3 fw-normal">
                <span class="fw-semibold text-danger">{{ $exp->category }}:</span> @taka($exp->total)
            </div>
        @endforeach
    </div>
</div>
@endif

{{-- Filters --}}
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-3">
        <form action="{{ route('admin.accounting.index') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-md-3">
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="fas fa-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="বিবরণ / ভাউচার / ব্যক্তি..." value="{{ $search }}">
                </div>
            </div>
            <div class="col-md-2">
                <select name="type" class="form-select" onchange="this.form.submit()">
                    <option value="">সকল ধরন (আয় ও ব্যয়)</option>
                    <option value="income" @selected($type === 'income')>শুধুমাত্র আয়</option>
                    <option value="expense" @selected($type === 'expense')>শুধুমাত্র ব্যয় / ক্রয়</option>
                </select>
            </div>
            <div class="col-md-3">
                <input type="text" name="category" class="form-control" placeholder="খাত (যেমন: কাগজ, কালি...)" value="{{ $category }}" list="allCategoriesList">
                <datalist id="allCategoriesList">
                    @foreach(array_merge($categories['expense'], $categories['income']) as $cat)
                        <option value="{{ $cat }}"></option>
                    @endforeach
                </datalist>
            </div>
            <div class="col-md-2">
                <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}" title="শুরুর তারিখ">
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter me-1"></i> ফিল্টার</button>
                @if(request()->hasAny(['search', 'type', 'category', 'date_from', 'date_to']))
                    <a href="{{ route('admin.accounting.index') }}" class="btn btn-light border" title="রিসেট"><i class="fas fa-rotate-left"></i></a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- Transactions Table --}}
<div class="adm-card shadow-sm rounded-4 overflow-hidden mb-4">
    @if ($entries->isEmpty())
        <div class="empty-state py-5 text-center">
            <i class="fas fa-receipt fs-1 text-muted opacity-50 mb-3"></i>
            <h5 class="fw-bold text-muted">কোনো আয়-ব্যয়ের হিসাব রেকর্ড পাওয়া যায়নি</h5>
            <p class="text-muted small">উপরের "নতুন ব্যয় / ক্রয় এন্ট্রি" বা "নতুন আয় এন্ট্রি" বাটনে ক্লিক করে হিসাব শুরু করুন।</p>
        </div>
    @else
        <div class="table-responsive">
            <table class="table adm-table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">তারিখ</th>
                        <th>লেনদেন #</th>
                        <th>ধরন</th>
                        <th>খাত (সেক্টর)</th>
                        <th>বিবরণ / পার্টী</th>
                        <th>টাকার পরিমাণ</th>
                        <th>পেমেন্ট মাধ্যম</th>
                        <th>রেকর্ডকারী</th>
                        <th class="text-center pe-3">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($entries as $entry)
                        <tr>
                            <td class="ps-3 text-muted small">@bnDate($entry->entry_date)</td>
                            <td>
                                <span class="fw-semibold small text-muted">{{ $entry->entry_no }}</span>
                                @if($entry->voucher_no)
                                    <div class="small text-muted">ভাউচার: {{ $entry->voucher_no }}</div>
                                @endif
                            </td>
                            <td>
                                @if($entry->type === 'income')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 rounded-pill">
                                        <i class="fas fa-arrow-up me-1"></i>আয় (Income)
                                    </span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1 rounded-pill">
                                        <i class="fas fa-arrow-down me-1"></i>ব্যয় / ক্রয়
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span class="fw-semibold text-dark">{{ $entry->category }}</span>
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ $entry->title }}</div>
                                @if($entry->party_name)
                                    <div class="text-muted small"><i class="fas fa-user me-1"></i>{{ $entry->party_name }}</div>
                                @endif
                                @if($entry->invoice)
                                    <a href="{{ route('admin.accounting.invoices.show', $entry->invoice_id) }}" class="small text-primary text-decoration-none">
                                        <i class="fas fa-file-invoice me-1"></i>বিল #{{ $entry->invoice->invoice_no }}
                                    </a>
                                @endif
                            </td>
                            <td class="fw-bold fs-6 {{ $entry->type === 'income' ? 'text-success' : 'text-danger' }}">
                                {{ $entry->type === 'income' ? '+' : '-' }}@taka($entry->amount)
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ $entry->payment_method }}</span>
                            </td>
                            <td class="text-muted small">{{ $entry->creator->name ?? 'অ্যাডমিন' }}</td>
                            <td class="text-center pe-3">
                                <form action="{{ route('admin.accounting.entries.destroy', $entry->id) }}" method="POST" class="d-inline" onsubmit="return confirm('আপনি কি নিশ্চিতভাবে এই লেনদেন রেকর্ডটি মুছে ফেলতে চান?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger p-1 border-0" title="মুছে ফেলুন">
                                        <i class="fas fa-trash-can"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($entries->hasPages())
            <div class="adm-card__foot d-flex flex-wrap justify-content-between align-items-center gap-2 p-3 bg-white border-top">
                <span class="text-muted small">
                    মোট @bn($entries->total())টির মধ্যে @bn($entries->firstItem())–@bn($entries->lastItem()) দেখানো হচ্ছে
                </span>
                {{ $entries->links() }}
            </div>
        @endif
    @endif
</div>

{{-- New Expense Modal --}}
<div class="modal fade" id="newExpenseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom py-3 bg-danger text-white rounded-top-4">
                <h5 class="modal-title fw-bold"><i class="fas fa-minus-circle me-2"></i>নতুন ব্যয় / ক্রয় হিসাব এন্ট্রি</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.accounting.entries.store') }}" method="POST">
                @csrf
                <input type="hidden" name="type" value="expense">

                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">ব্যয়ের খাত / ক্যাটাগরি <span class="text-danger">*</span></label>
                            <select name="category" class="form-select rounded-3" required>
                                <option value="">খাত নির্বাচন করুন</option>
                                @foreach($categories['expense'] as $cat)
                                    <option value="{{ $cat }}">{{ $cat }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">তারিখ <span class="text-danger">*</span></label>
                            <input type="date" name="entry_date" class="form-control rounded-3" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">বিবরণ / শিরোনাম <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control rounded-3" placeholder="যেমন: ১০০ রিম ডিমাই কাগজ ক্রয় বা প্রিন্টিং বিল..." required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">টাকার পরিমাণ (৳) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="amount" class="form-control rounded-3 fw-bold text-danger fs-5" placeholder="0.00" min="1" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">পেমেন্ট মাধ্যম <span class="text-danger">*</span></label>
                            <select name="payment_method" class="form-select rounded-3" required>
                                <option value="নগদ (Cash)">নগদ (Cash)</option>
                                <option value="ব্যাংক (Bank)">ব্যাংক (Bank Transfer)</option>
                                <option value="বিকাশ (bKash)">বিকাশ (bKash)</option>
                                <option value="নগদ (Nagad)">নগদ (Nagad)</option>
                                <option value="চেক (Cheque)">চেক (Cheque)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">সরবরাহকারী / ভেন্ডর / প্রাপকের নাম</label>
                            <input type="text" name="party_name" class="form-control rounded-3" placeholder="ঐচ্ছিক (প্রেস বা দোকান)">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">ভাউচার / মেমো নম্বর</label>
                            <input type="text" name="voucher_no" class="form-control rounded-3" placeholder="ঐচ্ছিক">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-muted">নোট / মন্তব্য</label>
                            <textarea name="notes" rows="2" class="form-control rounded-3" placeholder="অতিরিক্ত বিবরণ থাকলে লিখুন..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal">বাতিল</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold">ব্যয় সংরক্ষণ করুন</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- New Income Modal --}}
<div class="modal fade" id="newIncomeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom py-3 bg-success text-white rounded-top-4">
                <h5 class="modal-title fw-bold"><i class="fas fa-plus-circle me-2"></i>নতুন আয় হিসাব এন্ট্রি</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.accounting.entries.store') }}" method="POST">
                @csrf
                <input type="hidden" name="type" value="income">

                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">আয়ের খাত / ক্যাটাগরি <span class="text-danger">*</span></label>
                            <select name="category" class="form-select rounded-3" required>
                                <option value="">খাত নির্বাচন করুন</option>
                                @foreach($categories['income'] as $cat)
                                    <option value="{{ $cat }}">{{ $cat }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">তারিখ <span class="text-danger">*</span></label>
                            <input type="date" name="entry_date" class="form-control rounded-3" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">বিবরণ / শিরোনাম <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control rounded-3" placeholder="যেমন: বই বিক্রয় বা সার্ভিস ফি বাবদ আয়..." required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">টাকার পরিমাণ (৳) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="amount" class="form-control rounded-3 fw-bold text-success fs-5" placeholder="0.00" min="1" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">পেমেন্ট মাধ্যম <span class="text-danger">*</span></label>
                            <select name="payment_method" class="form-select rounded-3" required>
                                <option value="নগদ (Cash)">নগদ (Cash)</option>
                                <option value="ব্যাংক (Bank)">ব্যাংক (Bank Transfer)</option>
                                <option value="বিকাশ (bKash)">বিকাশ (bKash)</option>
                                <option value="নগদ (Nagad)">নগদ (Nagad)</option>
                                <option value="চেক (Cheque)">চেক (Cheque)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">গ্রাহক / প্রতিষ্ঠানের নাম</label>
                            <input type="text" name="party_name" class="form-control rounded-3" placeholder="ঐচ্ছিক">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">রসিদ / মেমো নম্বর</label>
                            <input type="text" name="voucher_no" class="form-control rounded-3" placeholder="ঐচ্ছিক">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-muted">নোট / মন্তব্য</label>
                            <textarea name="notes" rows="2" class="form-control rounded-3" placeholder="অতিরিক্ত বিবরণ থাকলে লিখুন..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal">বাতিল</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold">আয় সংরক্ষণ করুন</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
