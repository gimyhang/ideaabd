@extends('layouts.admin')

@section('title', 'Accounting & Cashbook')
@section('heading', 'Accounting Ledger, Income & Expenses')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Accounting & Cashbook</li>
@endsection

@section('actions')
    <div class="d-flex flex-wrap align-items-center gap-2">
        <div class="dropdown">
            <button class="btn btn-primary btn-sm rounded-pill px-3.5 shadow-xs fw-semibold dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-plus-circle me-1"></i> New Transaction
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow rounded-3 border-0 p-2" style="min-width: 200px;">
                <li>
                    <button type="button" class="dropdown-item rounded-2 py-2 fw-semibold d-flex align-items-center gap-2 text-success" data-bs-toggle="modal" data-bs-target="#newIncomeModal">
                        <i class="fas fa-plus-circle text-success"></i> Record Income
                    </button>
                </li>
                <li>
                    <button type="button" class="dropdown-item rounded-2 py-2 fw-semibold d-flex align-items-center gap-2 text-danger" data-bs-toggle="modal" data-bs-target="#newExpenseModal">
                        <i class="fas fa-minus-circle text-danger"></i> Record Expense
                    </button>
                </li>
            </ul>
        </div>
        <a href="{{ route('admin.accounting.invoices.index') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-xs fw-semibold">
            <i class="fas fa-file-invoice-dollar me-1"></i> Invoices & Challans
        </a>
    </div>
@endsection

@section('content')

{{-- Idea Accounting Unified Navigation Bar --}}
<div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
    <div class="card-body p-2 px-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div class="btn-group shadow-2xs rounded-pill p-1 bg-light border" role="group">
                <a href="{{ route('admin.accounting.index') }}" 
                   class="btn btn-sm rounded-pill px-3.5 py-1.5 fw-semibold btn-white text-primary shadow-xs">
                    <i class="fas fa-scale-balanced me-1.5"></i> Income & Expenses
                </a>
                <a href="{{ route('admin.accounting.invoices.index') }}" 
                   class="btn btn-sm rounded-pill px-3.5 py-1.5 fw-semibold btn-light text-muted">
                    <i class="fas fa-file-invoice-dollar me-1.5"></i> Invoices & Challans
                </a>
                <a href="{{ route('admin.accounting.reports.index') }}" 
                   class="btn btn-sm rounded-pill px-3.5 py-1.5 fw-semibold btn-light text-muted">
                    <i class="fas fa-chart-pie me-1.5"></i> P&L Reports
                </a>
            </div>
            <div class="text-muted small">
                Net Cash Balance: <strong class="{{ $netBalance >= 0 ? 'text-success' : 'text-danger' }}">৳{{ number_format($netBalance, 2) }}</strong>
            </div>
        </div>
    </div>
</div>

{{-- Financial Stat Cards --}}
<div class="row g-3 mb-4">
    <div class="col-12 col-md-4">
        <div class="card border-0 shadow-sm rounded-4 p-3 p-md-4 bg-white border-start border-4 border-success">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold">Total Income</span>
                    <h3 class="fw-bold mb-0 text-success">৳{{ number_format($totalIncome, 2) }}</h3>
                </div>
                <div class="rounded-circle bg-success-subtle text-success p-3"><i class="fas fa-arrow-trend-up fs-4"></i></div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="card border-0 shadow-sm rounded-4 p-3 p-md-4 bg-white border-start border-4 border-danger">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold">Total Expenses & Purchases</span>
                    <h3 class="fw-bold mb-0 text-danger">৳{{ number_format($totalExpense, 2) }}</h3>
                </div>
                <div class="rounded-circle bg-danger-subtle text-danger p-3"><i class="fas fa-arrow-trend-down fs-4"></i></div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="card border-0 shadow-sm rounded-4 p-3 p-md-4 bg-white border-start border-4 {{ $netBalance >= 0 ? 'border-primary' : 'border-warning' }}">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold">Net Balance / Fund</span>
                    <h3 class="fw-bold mb-0 {{ $netBalance >= 0 ? 'text-primary' : 'text-danger' }}">৳{{ number_format($netBalance, 2) }}</h3>
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
    <span class="small fw-bold text-muted text-uppercase mb-2 d-block"><i class="fas fa-chart-pie me-1 text-danger"></i>Top Expense Sectors:</span>
    <div class="d-flex flex-wrap gap-2">
        @foreach($expenseBreakdown as $exp)
            <div class="badge bg-light text-dark border p-2 rounded-3 fw-normal">
                <span class="fw-semibold text-danger">{{ $exp->category }}:</span> ৳{{ number_format($exp->total, 2) }}
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
                    <input type="text" name="search" class="form-control" placeholder="Description / Voucher / Party..." value="{{ $search }}">
                </div>
            </div>
            <div class="col-md-2">
                <select name="type" class="form-select" onchange="this.form.submit()">
                    <option value="">All Types (Income & Expense)</option>
                    <option value="income" @selected($type === 'income')>Income Only</option>
                    <option value="expense" @selected($type === 'expense')>Expenses / Purchases Only</option>
                </select>
            </div>
            <div class="col-md-3">
                <input type="text" name="category" class="form-control" placeholder="Category (e.g. Paper, Printing...)" value="{{ $category }}" list="allCategoriesList">
                <datalist id="allCategoriesList">
                    @foreach(array_merge($categories['expense'], $categories['income']) as $cat)
                        <option value="{{ $cat }}"></option>
                    @endforeach
                </datalist>
            </div>
            <div class="col-md-2">
                <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}" title="Start Date">
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter me-1"></i> Filter</button>
                @if(request()->hasAny(['search', 'type', 'category', 'date_from', 'date_to']))
                    <a href="{{ route('admin.accounting.index') }}" class="btn btn-light border" title="Reset"><i class="fas fa-rotate-left"></i></a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- Transactions Table --}}
<div class="adm-card shadow-sm rounded-4 overflow-hidden mb-4 bg-white">
    @if ($entries->isEmpty())
        <div class="empty-state py-5 text-center">
            <i class="fas fa-receipt fs-1 text-muted opacity-50 mb-3"></i>
            <h5 class="fw-bold text-muted">No Accounting Entries Found</h5>
            <p class="text-muted small">Record an income or expense transaction using the buttons above.</p>
        </div>
    @else
        <div class="table-responsive">
            <table class="table adm-table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Date</th>
                        <th>Txn #</th>
                        <th>Type</th>
                        <th>Category</th>
                        <th>Description / Party</th>
                        <th>Amount</th>
                        <th>Payment Method</th>
                        <th>Recorded By</th>
                        <th class="text-center pe-3">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($entries as $entry)
                        <tr>
                            <td class="ps-3 text-muted small">{{ $entry->entry_date ? $entry->entry_date->format('d M, Y') : '—' }}</td>
                            <td>
                                <span class="fw-semibold small text-muted">{{ $entry->entry_no }}</span>
                                @if($entry->voucher_no)
                                    <div class="small text-muted">Voucher: {{ $entry->voucher_no }}</div>
                                @endif
                            </td>
                            <td>
                                @if($entry->type === 'income')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 rounded-pill">
                                        <i class="fas fa-arrow-up me-1"></i>Income
                                    </span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1 rounded-pill">
                                        <i class="fas fa-arrow-down me-1"></i>Expense
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span class="fw-semibold text-dark">{{ $entry->category }}</span>
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ $entry->title }}</div>
                                @if($entry->party_name)
                                    <div class="text-muted small"><i class="fas fa-store text-primary me-1"></i>{{ $entry->party_name }}</div>
                                @endif
                                @if($entry->notes)
                                    <div class="small text-secondary mt-0.5 bg-light p-1 rounded border-start border-2 border-primary" style="font-size: 11.5px; white-space: pre-line; line-height: 1.4;">
                                        {{ Str::limit($entry->notes, 160) }}
                                    </div>
                                @endif
                                @if($entry->invoice)
                                    <a href="{{ route('admin.accounting.invoices.show', $entry->invoice_id) }}" class="small text-primary text-decoration-none d-inline-block mt-0.5">
                                        <i class="fas fa-file-invoice me-1"></i>Invoice #{{ $entry->invoice->invoice_no }}
                                    </a>
                                @endif
                            </td>
                            <td class="fw-bold fs-6 {{ $entry->type === 'income' ? 'text-success' : 'text-danger' }}">
                                {{ $entry->type === 'income' ? '+' : '-' }}৳{{ number_format($entry->amount, 2) }}
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ $entry->payment_method }}</span>
                            </td>
                            <td class="text-muted small">{{ $entry->creator->name ?? 'Admin' }}</td>
                            <td class="text-center pe-3">
                                <form action="{{ route('admin.accounting.entries.destroy', $entry->id) }}" method="POST" class="d-inline" data-confirm="আপনি কি নিশ্চিত যে এই হিসাব ভাউচারটি ডিলিট করতে চান?" data-confirm-title="ভাউচার ডিলিট">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger p-1 border-0" title="Delete">
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
                    Showing {{ $entries->firstItem() }}–{{ $entries->lastItem() }} of {{ number_format($entries->total()) }} entries
                </span>
                {{ $entries->links() }}
            </div>
        @endif
    @endif
</div>

{{-- Dynamic New Expense & Purchasing Modal --}}
<div class="modal fade" id="newExpenseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <div class="modal-header border-bottom py-3 bg-danger text-white">
                <div class="d-flex align-items-center gap-2">
                    <span class="rounded-circle bg-white text-danger d-inline-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                        <i class="fa-solid fa-cart-shopping fs-6"></i>
                    </span>
                    <div>
                        <h5 class="modal-title fw-bold mb-0">Record New Expense / মালামাল ক্রয়ের এন্ট্রি</h5>
                        <p class="small text-white-50 mb-0">কাগজ, কালি, বোর্ড, অন্যান্য প্রকাশনীর বই, পিন, স্টেশনারি বা চা-নাস্তা ক্রয়ের হিসাব</p>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form action="{{ route('admin.accounting.entries.store') }}" method="POST" id="expenseEntryForm">
                @csrf
                <input type="hidden" name="type" value="expense">

                <div class="modal-body p-4">
                    
                    {{-- 1. Quick Category Chips / Presets --}}
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark mb-1.5 d-flex align-items-center gap-1.5">
                            <i class="fa-solid fa-tags text-danger"></i>
                            <span>ক্যাটাগরি বা ব্যয়ের খাত নির্বাচন করুন *</span>
                        </label>
                        <div class="d-flex flex-wrap gap-1.5 mb-2" id="quickCategoryPills">
                            <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2.5 py-1" onclick="selectExpCat('কাগজ ক্রয় (Paper Purchase)', this)">📄 কাগজ ক্রয়</button>
                            <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2.5 py-1" onclick="selectExpCat('বোর্ড ক্রয় (Binding Board Purchase)', this)">📦 বোর্ড ক্রয়</button>
                            <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2.5 py-1" onclick="selectExpCat('কালি ও প্লেট (Ink & Plates)', this)">🎨 কালি ও প্লেট</button>
                            <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2.5 py-1" onclick="selectExpCat('মুদ্রণ ও প্রেস খরচ (Printing & Press)', this)">🖨️ প্রেস ও মুদ্রণ</button>
                            <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2.5 py-1" onclick="selectExpCat('অন্যান্য প্রকাশনীর বই ক্রয় (Other Publisher Books)', this)">📖 অন্য প্রকাশনীর বই</button>
                            <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2.5 py-1" onclick="selectExpCat('স্টেশনারি, পিন ও সরঞ্জাম (Stationery, Pins & Tools)', this)">📎 পিন, স্ট্যাপলার ও স্টেশনারি</button>
                            <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2.5 py-1" onclick="selectExpCat('চা, নাস্তা ও পান আপ্যায়ন (Tea, Snacks & Refreshment)', this)">☕ চা, নাস্তা ও পান</button>
                            <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2.5 py-1" onclick="selectExpCat('দৈনিক মজুরি ও লেবার খরচ (Daily Wages & Labor)', this)">💼 দৈনিক মজুরি/লেবার</button>
                            <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2.5 py-1" onclick="selectExpCat('পরিবহন ও কুরিয়ার (Transport & Courier)', this)">🚚 কুরিয়ার/যাতায়াত</button>
                            <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2.5 py-1" onclick="selectExpCat('বিবিধ খরচ (Miscellaneous Expense)', this)">🏷️ বিবিধ খরচ</button>
                        </div>

                        <div class="row g-2">
                            <div class="col-md-6">
                                <select name="category" id="expCategorySelect" class="form-select rounded-3" required onchange="onCategorySelectChange(this)">
                                    <option value="">খাত নির্বাচন করুন (Select Category)...</option>
                                    @foreach($categories['expense'] as $cat)
                                        <option value="{{ $cat }}">{{ $cat }}</option>
                                    @endforeach
                                    <option value="__custom__">+ অন্যান্য / কাস্টম খাত লিখুন</option>
                                </select>
                            </div>
                            <div class="col-md-6" id="customCategoryBox" style="display: none;">
                                <input type="text" name="custom_category" id="customCategoryInput" class="form-control rounded-3" placeholder="কাস্টম খাতের নাম লিখুন (যেমন: ফটোস্ট্যাট বা সিল খরচ)...">
                            </div>
                        </div>
                    </div>

                    {{-- 2. Basic Info Row --}}
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">তারিখ (Date) *</label>
                            <input type="date" name="entry_date" class="form-control rounded-3" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">সরবরাহকারী / দোকান / বিক্রেতার নাম</label>
                            <input type="text" name="party_name" class="form-control rounded-3" placeholder="যেমন: কর্ণফুলী পেপার্স / অনন্যা প্রকাশনী / মতিন টি স্টল...">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-dark">মূল বিবরণ / লেনদেনের শিরোনাম *</label>
                            <input type="text" name="title" id="expMainTitle" class="form-control rounded-3" placeholder="যেমন: অফসেট কাগজ ২০ রিম ক্রয় বা মেহমান আপ্যায়ন ও চা-নাস্তা বিল..." required>
                        </div>
                    </div>

                    {{-- 3. Dynamic Itemized Purchasing Table (মালামাল ও আইটেমের তালিকা) --}}
                    <div class="p-3 bg-light rounded-4 border mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2 pb-1 border-bottom">
                            <span class="small fw-bold text-dark d-flex align-items-center gap-1.5">
                                <i class="fa-solid fa-list-ol text-danger"></i>
                                <span>মালামাল বা বই ক্রয়ের আইটেমভিত্তিক বিস্তারিত তালিকা (Itemized Lines - ঐচ্ছিক)</span>
                            </span>
                            <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-0.5 fw-semibold" onclick="addExpenseItemRow()">
                                <i class="fa-solid fa-plus me-1"></i> আইটেম যোগ করুন
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-sm table-borderless align-middle mb-1" id="expenseItemsTable">
                                <thead class="small text-muted">
                                    <tr>
                                        <th style="min-width: 260px;">পণ্যের নাম / বিবরণ (কাগজ/বই/পিন/নাস্তা)</th>
                                        <th style="width: 110px;">পরিমাণ (Qty)</th>
                                        <th style="width: 130px;">একক দর (৳)</th>
                                        <th class="text-end" style="width: 140px;">মোট টাকা (৳)</th>
                                        <th class="text-center" style="width: 45px;"></th>
                                    </tr>
                                </thead>
                                <tbody id="expenseItemsTbody">
                                    <!-- Dynamic Rows Injected Here -->
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-between align-items-center pt-2 mt-1 border-top small text-muted">
                            <span><i class="fa-solid fa-calculator me-1"></i> আইটেমের পরিমাণ ও দর লিখলে মোট টাকা স্বয়ংক্রিয়ভাবে হিসাব হবে।</span>
                            <span class="fw-bold text-dark">আইটেম সাবটোটাল: <span class="text-danger font-monospace fs-6" id="itemsSubtotalText">৳0.00</span></span>
                        </div>
                    </div>

                    {{-- 4. Payment Details & Voucher --}}
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-dark">সর্বমোট ব্যয়ের পরিমাণ (৳) *</label>
                            <div class="input-group">
                                <span class="input-group-text bg-danger text-white fw-bold">৳</span>
                                <input type="number" step="0.01" name="amount" id="expTotalAmount" class="form-control rounded-end-3 fw-bold text-danger fs-5" placeholder="0.00" min="0.01" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-dark">পেমেন্ট মেথড *</label>
                            <select name="payment_method" class="form-select rounded-3" required>
                                <option value="Cash">ক্যাশ / নগদ (Cash)</option>
                                <option value="bKash">বিকাশ (bKash)</option>
                                <option value="Nagad">নগদ (Nagad)</option>
                                <option value="Bank Transfer">ব্যাংক ট্রান্সফার (Bank Transfer)</option>
                                <option value="Cheque">চেক (Cheque)</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-muted">ভাউচার / ক্যাশ মেমো নং</label>
                            <input type="text" name="voucher_no" class="form-control rounded-3" placeholder="মেমো বা চালান নম্বর...">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-muted">অতিরিক্ত নোট বা মন্তব্য</label>
                            <textarea name="notes" rows="2" class="form-control rounded-3" placeholder="প্রয়োজনীয় অন্যান্য বিবরণ..."></textarea>
                        </div>
                    </div>

                </div>

                <div class="modal-footer bg-light py-2.5 px-4 d-flex justify-content-between align-items-center">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">বাতিল</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-5 fw-bold shadow-sm">
                        <i class="fa-solid fa-floppy-disk me-1.5"></i> খরচ ও ক্রয়ের হিসাব সংরক্ষণ করুন
                    </button>
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
                <h5 class="modal-title fw-bold"><i class="fas fa-plus-circle me-2"></i>Record New Income Transaction</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.accounting.entries.store') }}" method="POST">
                @csrf
                <input type="hidden" name="type" value="income">

                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Income Category <span class="text-danger">*</span></label>
                            <select name="category" class="form-select rounded-3" required>
                                <option value="">Select Category</option>
                                @foreach($categories['income'] as $cat)
                                    <option value="{{ $cat }}">{{ $cat }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                            <input type="date" name="entry_date" class="form-control rounded-3" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Description / Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control rounded-3" placeholder="e.g. Books sales revenue or service fees..." required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Amount (৳) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="amount" class="form-control rounded-3 fw-bold text-success fs-5" placeholder="0.00" min="1" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Payment Method <span class="text-danger">*</span></label>
                            <select name="payment_method" class="form-select rounded-3" required>
                                <option value="Cash">Cash</option>
                                <option value="Bank Transfer">Bank Transfer</option>
                                <option value="bKash">bKash</option>
                                <option value="Nagad">Nagad</option>
                                <option value="Cheque">Cheque</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">Customer / Client Name</label>
                            <input type="text" name="party_name" class="form-control rounded-3" placeholder="Optional">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">Receipt / Memo #</label>
                            <input type="text" name="voucher_no" class="form-control rounded-3" placeholder="Optional">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-muted">Notes / Remarks</label>
                            <textarea name="notes" rows="2" class="form-control rounded-3" placeholder="Additional details..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold">Save Income</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function selectExpCat(catName, btn) {
    const sel = document.getElementById('expCategorySelect');
    const customBox = document.getElementById('customCategoryBox');
    
    // Highlight active chip
    document.querySelectorAll('#quickCategoryPills button').forEach(b => {
        b.classList.remove('btn-danger', 'text-white');
        b.classList.add('btn-outline-secondary');
    });
    if (btn) {
        btn.classList.remove('btn-outline-secondary');
        btn.classList.add('btn-danger', 'text-white');
    }

    if (sel) {
        sel.value = catName;
        if (customBox) customBox.style.display = 'none';
    }
}

function onCategorySelectChange(sel) {
    const customBox = document.getElementById('customCategoryBox');
    if (sel.value === '__custom__') {
        if (customBox) {
            customBox.style.display = 'block';
            document.getElementById('customCategoryInput')?.focus();
        }
    } else {
        if (customBox) customBox.style.display = 'none';
    }
}

function addExpenseItemRow(name = '', qty = 1, price = 0) {
    const tbody = document.getElementById('expenseItemsTbody');
    if (!tbody) return;

    const rowId = 'item_row_' + Date.now() + '_' + Math.floor(Math.random() * 1000);
    const tr = document.createElement('tr');
    tr.id = rowId;
    tr.className = 'expense-item-row';
    tr.innerHTML = `
        <td class="ps-0">
            <input type="text" name="item_name[]" value="${name}" class="form-control form-control-sm rounded-3 item-name-input" 
                   placeholder="পণ্যের নাম (যেমন: অফসেট কাগজ / পিন / চা-বিস্কুট)..." oninput="onItemNameChange()">
        </td>
        <td>
            <input type="number" step="0.01" min="0" name="item_qty[]" value="${qty}" class="form-control form-control-sm rounded-3 item-qty-input text-center" 
                   placeholder="পরিমাণ" oninput="calcItemRow('${rowId}')">
        </td>
        <td>
            <input type="number" step="0.01" min="0" name="item_price[]" value="${price > 0 ? price : ''}" class="form-control form-control-sm rounded-3 item-price-input" 
                   placeholder="দর (৳)" oninput="calcItemRow('${rowId}')">
        </td>
        <td class="text-end">
            <input type="number" step="0.01" min="0" name="item_total[]" class="form-control form-control-sm rounded-3 text-end fw-bold text-danger item-total-input font-monospace" 
                   placeholder="0.00" readonly>
        </td>
        <td class="text-center pe-0">
            <button type="button" class="btn btn-sm btn-outline-danger border-0 p-1" onclick="removeExpenseItemRow('${rowId}')" title="আইটেম মুছুন">
                <i class="fa-solid fa-trash-can"></i>
            </button>
        </td>
    `;
    tbody.appendChild(tr);
    calcItemRow(rowId);
}

function calcItemRow(rowId) {
    const row = document.getElementById(rowId);
    if (!row) return;

    const qty = parseFloat(row.querySelector('.item-qty-input')?.value) || 0;
    const price = parseFloat(row.querySelector('.item-price-input')?.value) || 0;
    const total = qty * price;

    const totalInput = row.querySelector('.item-total-input');
    if (totalInput) {
        totalInput.value = total > 0 ? total.toFixed(2) : '0.00';
    }

    calcAllExpenseItems();
}

function removeExpenseItemRow(rowId) {
    const row = document.getElementById(rowId);
    if (row) {
        row.remove();
        calcAllExpenseItems();
    }
}

function calcAllExpenseItems() {
    let subtotal = 0;
    let hasItems = false;
    document.querySelectorAll('.expense-item-row .item-total-input').forEach(input => {
        const val = parseFloat(input.value) || 0;
        subtotal += val;
        hasItems = true;
    });

    const subText = document.getElementById('itemsSubtotalText');
    if (subText) {
        subText.textContent = '৳' + subtotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    const mainAmount = document.getElementById('expTotalAmount');
    if (mainAmount && hasItems && subtotal > 0) {
        mainAmount.value = subtotal.toFixed(2);
    }
}

function onItemNameChange() {
    const mainTitle = document.getElementById('expMainTitle');
    if (!mainTitle || mainTitle.value.trim() !== '') return;

    const names = [];
    document.querySelectorAll('.item-name-input').forEach(inp => {
        if (inp.value.trim()) names.push(inp.value.trim());
    });
    if (names.length > 0) {
        mainTitle.value = names.slice(0, 3).join(', ') + (names.length > 3 ? ' ইত্যাদি' : '') + ' ক্রয়';
    }
}

// Auto-add first blank row when modal opens if empty
document.addEventListener('DOMContentLoaded', function () {
    const modalEl = document.getElementById('newExpenseModal');
    if (modalEl) {
        modalEl.addEventListener('show.bs.modal', function () {
            const tbody = document.getElementById('expenseItemsTbody');
            if (tbody && tbody.children.length === 0) {
                addExpenseItemRow('', 1, 0);
            }
        });
    }
});
</script>
@endpush
@endsection
