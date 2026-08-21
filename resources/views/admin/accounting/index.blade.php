@extends('layouts.admin')

@section('title', 'Accounting & Cashbook')
@section('heading', 'Accounting Ledger, Income & Expenses')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Accounting & Cashbook</li>
@endsection

@section('actions')
    <button type="button" class="btn btn-danger btn-sm rounded-pill px-3 shadow-xs" data-bs-toggle="modal" data-bs-target="#newExpenseModal">
        <i class="fas fa-minus-circle me-1"></i> Record Expense
    </button>
    <button type="button" class="btn btn-success btn-sm rounded-pill px-3 shadow-xs" data-bs-toggle="modal" data-bs-target="#newIncomeModal">
        <i class="fas fa-plus-circle me-1"></i> Record Income
    </button>
    <a href="{{ route('admin.accounting.invoices.create') }}" class="btn btn-primary btn-sm rounded-pill px-3 fw-bold shadow-xs">
        <i class="fas fa-file-invoice me-1"></i> Create Invoice / Challan
    </a>
@endsection

@section('content')

{{-- Idea Accounting Unified Navigation Bar --}}
<div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
    <div class="card-body p-2">
        <div class="nav nav-pills gap-1.5 flex-wrap">
            <a href="{{ route('admin.accounting.index') }}" 
               class="nav-link rounded-pill px-3.5 py-2 fw-semibold active bg-primary text-white shadow-sm">
                <i class="fas fa-scale-balanced me-1.5"></i> Income & Expense Ledger
            </a>
            <a href="{{ route('admin.accounting.invoices.index') }}" 
               class="nav-link rounded-pill px-3.5 py-2 fw-semibold text-dark hover-bg-light">
                <i class="fas fa-file-invoice-dollar me-1.5"></i> Invoices, Challans & Quotations
            </a>
            <a href="{{ route('admin.accounting.invoices.create') }}" 
               class="nav-link rounded-pill px-3.5 py-2 fw-semibold text-dark hover-bg-light">
                <i class="fas fa-file-circle-plus me-1.5"></i> Create New Invoice
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
                                    <div class="text-muted small"><i class="fas fa-user me-1"></i>{{ $entry->party_name }}</div>
                                @endif
                                @if($entry->invoice)
                                    <a href="{{ route('admin.accounting.invoices.show', $entry->invoice_id) }}" class="small text-primary text-decoration-none">
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
                                <form action="{{ route('admin.accounting.entries.destroy', $entry->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this accounting transaction?')">
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

{{-- New Expense Modal --}}
<div class="modal fade" id="newExpenseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom py-3 bg-danger text-white rounded-top-4">
                <h5 class="modal-title fw-bold"><i class="fas fa-minus-circle me-2"></i>Record New Expense Transaction</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.accounting.entries.store') }}" method="POST">
                @csrf
                <input type="hidden" name="type" value="expense">

                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Expense Category <span class="text-danger">*</span></label>
                            <select name="category" class="form-select rounded-3" required>
                                <option value="">Select Category</option>
                                @foreach($categories['expense'] as $cat)
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
                            <input type="text" name="title" class="form-control rounded-3" placeholder="e.g. 100 Reams Paper purchase or Printing bill..." required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Amount (৳) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="amount" class="form-control rounded-3 fw-bold text-danger fs-5" placeholder="0.00" min="1" required>
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
                            <label class="form-label small fw-semibold text-muted">Supplier / Vendor / Payee Name</label>
                            <input type="text" name="party_name" class="form-control rounded-3" placeholder="Optional (Press or Vendor)">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">Voucher / Memo #</label>
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
                    <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold">Save Expense</button>
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

@endsection
