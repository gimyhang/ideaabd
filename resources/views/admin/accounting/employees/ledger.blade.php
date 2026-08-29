@extends('layouts.admin')

@section('title', $employee->name . ' — Work Log & Cash Ledger — Idea Prakashan')

@push('styles')
<style>
/* =========================================================
   INTERNATIONAL A4 PRINT STYLES FOR ARTISAN LEDGER
   ========================================================= */
@media print {
    @page {
        size: A4 portrait;
        margin: 8mm 8mm 10mm 8mm;
    }
    
    html, body {
        background: #ffffff !important;
        color: #000000 !important;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif !important;
        font-size: 9.5pt !important;
        line-height: 1.25 !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    .no-print, 
    nav, 
    header, 
    .sidebar, 
    .navbar, 
    footer, 
    .btn, 
    .modal, 
    .pagination, 
    .alert,
    .filter-card {
        display: none !important;
    }

    .container-fluid {
        width: 100% !important;
        max-width: 100% !important;
        padding: 0 !important;
        margin: 0 !important;
    }

    .card {
        border: none !important;
        box-shadow: none !important;
        background: transparent !important;
        margin-bottom: 8px !important;
    }

    .card-body {
        padding: 0 !important;
    }

    .print-only-block {
        display: block !important;
    }

    /* Official Letterhead Header */
    .print-letterhead {
        display: block !important;
        border-bottom: 2px solid #0f172a;
        padding-bottom: 6px;
        margin-bottom: 8px;
    }

    .print-company-name {
        font-size: 17pt !important;
        font-weight: 800 !important;
        color: #0f172a !important;
        letter-spacing: -0.5px;
    }

    .print-doc-title {
        font-size: 11pt !important;
        font-weight: 700 !important;
        text-transform: uppercase;
        background-color: #f1f5f9 !important;
        padding: 3px 8px;
        display: inline-block;
        border: 1px solid #cbd5e1;
        letter-spacing: 0.5px;
    }

    /* Compact Artisan & Summary Box */
    .print-artisan-grid {
        display: grid !important;
        grid-template-columns: 1.6fr 1fr;
        gap: 6px;
        border: 1px solid #cbd5e1;
        padding: 6px 8px;
        background-color: #f8fafc !important;
        margin-bottom: 8px;
        font-size: 9pt !important;
    }

    .print-summary-badges {
        display: grid !important;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 6px;
        margin-bottom: 8px;
    }

    .print-kpi-box {
        border: 1px solid #cbd5e1;
        padding: 4px 6px;
        text-align: center;
        background-color: #ffffff !important;
    }

    .print-kpi-box .val {
        font-size: 11pt !important;
        font-weight: 800 !important;
        font-family: monospace !important;
    }

    /* High Density Table for 15-25 Rows on Single A4 */
    table.print-table {
        width: 100% !important;
        border-collapse: collapse !important;
        margin-bottom: 8px !important;
    }

    table.print-table th, 
    table.print-table td {
        border: 1px solid #cbd5e1 !important;
        padding: 3px 4px !important;
        font-size: 8.5pt !important;
        line-height: 1.2 !important;
        vertical-align: middle !important;
    }

    table.print-table thead th {
        background-color: #f1f5f9 !important;
        color: #0f172a !important;
        font-weight: 700 !important;
        text-transform: uppercase;
        font-size: 8pt !important;
    }

    table.print-table tr {
        page-break-inside: avoid !important;
    }

    table.print-table tbody tr:nth-child(even) {
        background-color: #f8fafc !important;
    }

    .badge {
        border: none !important;
        background: transparent !important;
        color: #000000 !important;
        padding: 0 !important;
        font-size: 8.5pt !important;
        font-weight: 700 !important;
    }

    /* Print Signatures */
    .print-signatures {
        display: block !important;
        margin-top: 18px;
        page-break-inside: avoid !important;
    }

    .sig-line {
        border-top: 1px solid #000000;
        padding-top: 3px;
        font-size: 8.5pt;
        text-align: center;
        font-weight: 600;
    }
}

/* Screen Display Styles */
.print-only-block {
    display: none;
}
.bg-purple-light { background-color: #f3e8ff; }
.text-purple { color: #7e22ce; }
.border-purple { border-color: #d8b4fe; }
</style>
@endpush

@section('content')
<div class="container-fluid px-3 px-md-4 py-3">

    <!-- Print-Only Letterhead Header -->
    <div class="print-only-block print-letterhead">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <div class="print-company-name">আইডিয়া প্রকাশন | IDEA PRAKASHAN</div>
                <div style="font-size: 8.5pt; color: #334155;">
                    {{ $invoiceSettings['company_address'] ?? 'বাংলাবাজার, ঢাকা — বই প্রকাশনা, মুদ্রণ ও বাঁধাই ব্যবস্থাপনা' }}
                    @if(!empty($invoiceSettings['company_phone'])) · Phone: {{ $invoiceSettings['company_phone'] }} @endif
                    @if(!empty($invoiceSettings['company_email'])) · Email: {{ $invoiceSettings['company_email'] }} @endif
                </div>
            </div>
            <div class="text-end">
                <div class="print-doc-title">Artisan Ledger Statement</div>
                <div style="font-size: 8pt; color: #475569; margin-top: 2px;">
                    Date: <strong>{{ date('d M, Y — h:i A') }}</strong>
                </div>
            </div>
        </div>
    </div>

    <!-- Print-Only Artisan Info & Financial Summary Grid -->
    <div class="print-only-block print-artisan-grid">
        <div>
            <div><strong>Artisan / Staff:</strong> {{ $employee->name }} ({{ $employee->designation }})</div>
            <div><strong>Department & Skill:</strong> {{ $employee->department }} @if($employee->skill_category) · {{ $employee->skill_category }} @endif · Phone: {{ $employee->phone ?: 'N/A' }}</div>
            <div><strong>Piece-Rate / Unit Wage:</strong> {{ $employee->formatted_rate }}</div>
        </div>
        <div class="text-end">
            <div><strong>Total Earned:</strong> ৳{{ number_format($totalEarned, 2) }} ({{ number_format($totalWorkQuantity) }} pcs)</div>
            <div><strong>Total Paid / Drawn:</strong> ৳{{ number_format($totalPaid, 2) }}</div>
            <div><strong>Net Balance Payable:</strong> <span style="font-weight: 800;">৳{{ number_format(abs($balanceDue), 2) }}</span> ({{ $balanceDue >= 0 ? 'Due' : 'Advance' }})</div>
        </div>
    </div>

    <!-- Screen Profile & Quick Action Header (No Print) -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white no-print">
        <div class="card-body p-3 p-md-4">
            <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle fw-bold d-flex align-items-center justify-content-center flex-shrink-0" 
                         style="width: 54px; height: 54px; background-color: #f3e8ff; color: #7e22ce; font-size: 22px;">
                        {{ mb_substr($employee->name, 0, 1) }}
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-0.5">
                            <h4 class="fw-bold text-dark mb-0">{{ $employee->name }}</h4>
                            <span class="badge border px-2.5 py-0.5 rounded-pill small fw-bold" style="background-color: #f3e8ff; color: #7e22ce; border-color: #d8b4fe;">
                                {{ $employee->designation }}
                            </span>
                        </div>
                        <p class="text-muted small mb-0">
                            {{ $employee->department }} 
                            @if($employee->skill_category) · <strong class="text-dark">{{ $employee->skill_category }}</strong> @endif
                            · Rate: <strong class="text-primary font-monospace">{{ $employee->formatted_rate }}</strong>
                            · Phone: <strong class="text-dark font-monospace">{{ $employee->phone ?: 'N/A' }}</strong>
                        </p>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2 flex-wrap ms-md-auto">
                    <a href="{{ route('admin.accounting.employees.index') }}" class="btn btn-outline-secondary rounded-pill px-3 py-2 fw-semibold">
                        <i class="fa-solid fa-arrow-left me-1"></i> Staff Directory
                    </a>
                    <button type="button" class="btn btn-primary rounded-pill px-3.5 py-2 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#addWorkModal" style="background-color: #7e22ce; border-color: #7e22ce;">
                        <i class="fa-solid fa-book-bookmark me-1.5"></i> Add Book Binding Log
                    </button>
                    <button type="button" class="btn btn-success rounded-pill px-3.5 py-2 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#addWithdrawalModal">
                        <i class="fa-solid fa-hand-holding-dollar me-1.5"></i> Record Cash Payout
                    </button>
                    <button onclick="window.print()" class="btn btn-dark rounded-pill px-3.5 py-2 fw-bold shadow-sm">
                        <i class="fa-solid fa-print me-1.5"></i> Print A4 Statement
                    </button>
                </div>
            </div>

            <hr class="my-3 opacity-25">

            <!-- Summary KPI Badges (3 Cards) -->
            <div class="row g-3">
                <div class="col-sm-4">
                    <div class="p-3 bg-light rounded-3 border d-flex align-items-center justify-content-between h-100" style="border-left: 4px solid #7e22ce !important;">
                        <div>
                            <span class="small text-muted fw-semibold">Total Work Value / Earned</span>
                            <h4 class="fw-bold mb-0 font-monospace" style="color: #7e22ce;">৳{{ number_format($totalEarned, 2) }}</h4>
                            <span class="text-muted" style="font-size: 11.5px;">Godown Delivered: <strong class="text-dark font-monospace">{{ number_format($totalWorkQuantity) }}</strong> Books / Units</span>
                        </div>
                        <span class="badge bg-white border p-2.5 rounded-circle fs-4" style="color: #7e22ce;"><i class="fa-solid fa-boxes-packing"></i></span>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="p-3 bg-light rounded-3 border d-flex align-items-center justify-content-between h-100" style="border-left: 4px solid #0284c7 !important;">
                        <div>
                            <span class="small text-muted fw-semibold">Total Cash Withdrawn / Paid</span>
                            <h4 class="fw-bold text-primary mb-0 font-monospace">৳{{ number_format($totalPaid, 2) }}</h4>
                            <span class="text-muted" style="font-size: 11.5px;">Cash / bKash / Bank Draws</span>
                        </div>
                        <span class="badge bg-white text-primary border p-2.5 rounded-circle fs-4"><i class="fa-solid fa-hand-holding-dollar"></i></span>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="p-3 rounded-3 border d-flex align-items-center justify-content-between h-100" 
                         style="background-color: {{ $balanceDue > 0 ? '#fef2f2' : '#f0fdf4' }}; border-color: {{ $balanceDue > 0 ? '#fca5a5' : '#86efac' }} !important; border-left: 4px solid {{ $balanceDue > 0 ? '#dc2626' : '#16a34a' }} !important;">
                        <div>
                            <span class="small fw-semibold {{ $balanceDue > 0 ? 'text-danger' : 'text-success' }}">
                                {{ $balanceDue >= 0 ? 'Current Net Balance Due' : 'Advance Balance' }}
                            </span>
                            <h4 class="fw-bold mb-0 font-monospace {{ $balanceDue > 0 ? 'text-danger' : 'text-success' }}">
                                ৳{{ number_format(abs($balanceDue), 2) }}
                            </h4>
                            <span class="text-muted" style="font-size: 11.5px;">{{ $balanceDue > 0 ? 'Payable to Artisan' : 'No outstanding balance' }}</span>
                        </div>
                        <span class="badge bg-white border p-2.5 rounded-circle fs-4 {{ $balanceDue > 0 ? 'text-danger' : 'text-success' }}">
                            <i class="fa-solid fa-scale-balanced"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Screen Filter Toolbar (No Print) -->
    <div class="card border-0 shadow-sm rounded-4 bg-white mb-4 no-print filter-card">
        <div class="card-body p-3">
            <form action="{{ route('admin.accounting.employees.ledger', $employee->id) }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light"><i class="fa-solid fa-book"></i></span>
                        <input type="text" name="book_title" value="{{ request('book_title') }}" class="form-control" placeholder="Search Book Title...">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light">From</span>
                        <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light">To</span>
                        <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control">
                    </div>
                </div>
                <div class="col-md-2">
                    <select name="per_page" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="25" {{ request('per_page', 25) == 25 ? 'selected' : '' }}>25 / Page</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 / Page</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100 / Page</option>
                        <option value="all" {{ request('per_page') == 'all' ? 'selected' : '' }}>All Records (Print)</option>
                    </select>
                </div>
                <div class="col-md-1 d-flex gap-1">
                    <button type="submit" class="btn btn-sm btn-primary w-100 fw-bold" style="background-color: #7e22ce; border-color: #7e22ce;">
                        <i class="fa-solid fa-filter"></i>
                    </button>
                    @if(request()->hasAny(['book_title', 'date_from', 'date_to', 'per_page']))
                        <a href="{{ route('admin.accounting.employees.ledger', $employee->id) }}" class="btn btn-sm btn-outline-secondary" title="Reset Filters">
                            <i class="fa-solid fa-rotate-left"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Multi-Day Book Production Progress Summary (Grouped by Book) -->
    @if(isset($bookSummaries) && $bookSummaries->isNotEmpty())
        <div class="card border-0 shadow-sm rounded-4 bg-white mb-4 overflow-hidden">
            <div class="card-header bg-light border-bottom p-2.5 p-md-3 d-flex align-items-center justify-content-between">
                <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 13.5px;">
                    <i class="fa-solid fa-book-open-reader text-purple" style="color: #7e22ce;"></i> Book-Wise Multi-Day Binding Progress & Totals
                </h6>
                <span class="badge bg-white text-dark border px-2.5 py-1 small">
                    {{ $bookSummaries->count() }} Books Tracked
                </span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 print-table">
                        <thead class="bg-white table-light small text-muted">
                            <tr>
                                <th class="ps-3" style="width: 30%;">Book Title & Print Date</th>
                                <th class="text-center" style="width: 12%;">Total Printed</th>
                                <th class="text-center" style="width: 12%;">Received</th>
                                <th class="text-center" style="width: 16%;">Total Bound & Delivered</th>
                                <th class="text-center" style="width: 12%;">Incomplete Left</th>
                                <th class="text-center" style="width: 18%;">Progress</th>
                                <th class="text-end pe-3" style="width: 14%;">Total Earned (৳)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bookSummaries as $book)
                                <tr>
                                    <td class="ps-3">
                                        <div class="fw-bold text-dark">{{ $book['book_title'] }}</div>
                                        <div class="small text-muted d-flex align-items-center gap-2 mt-0.5" style="font-size: 10px;">
                                            @if($book['print_date'])
                                                <span><i class="fa-solid fa-print me-1 text-secondary"></i>Print: <strong>{{ $book['print_date'] }}</strong></span>
                                            @endif
                                            <span><i class="fa-solid fa-calendar-days me-1 text-primary"></i>{{ $book['days_count'] }} Days ({{ $book['entries_count'] }} logs)</span>
                                            @if($book['last_log_date'])
                                                <span>· Last Log: {{ $book['last_log_date'] }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-center font-monospace fw-bold text-dark">
                                        {{ (float)$book['printed_qty'] > 0 ? number_format((float)$book['printed_qty']) : '—' }}
                                    </td>
                                    <td class="text-center font-monospace fw-bold text-dark">
                                        {{ (float)$book['received_qty'] > 0 ? number_format((float)$book['received_qty']) : '—' }}
                                    </td>
                                    <td class="text-center font-monospace">
                                        <span class="badge px-2 py-1 fw-bold rounded-pill" style="background-color: #f3e8ff; color: #7e22ce; border: 1px solid #d8b4fe;">
                                            📦 {{ number_format((float)$book['total_delivered']) }} pcs
                                        </span>
                                    </td>
                                    <td class="text-center font-monospace">
                                        @if($book['incomplete_qty'] > 0)
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-0.5 fw-bold rounded-pill">
                                                ⏳ {{ number_format((float)$book['incomplete_qty']) }} Left
                                            </span>
                                        @else
                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-0.5 rounded-pill">
                                                <i class="fa-solid fa-check-double me-1"></i> Completed
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex align-items-center gap-1.5 justify-content-center">
                                            <div class="progress flex-grow-1" style="height: 6px; max-width: 80px;">
                                                <div class="progress-bar {{ $book['progress'] >= 100 ? 'bg-success' : 'bg-purple' }}" 
                                                     role="progressbar" 
                                                     style="width: {{ $book['progress'] }}%; {{ $book['progress'] < 100 ? 'background-color: #7e22ce;' : '' }}" 
                                                     aria-valuenow="{{ $book['progress'] }}" aria-valuemin="0" aria-valuemax="100">
                                                </div>
                                            </div>
                                            <span class="small font-monospace fw-bold text-dark" style="font-size: 10px;">{{ $book['progress'] }}%</span>
                                        </div>
                                    </td>
                                    <td class="text-end pe-3 fw-bold font-monospace" style="color: #7e22ce;">
                                        ৳{{ number_format($book['total_earned'], 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <!-- Daily Date-Wise Ledger Table (A4 Precision Formatted) -->
    <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-3">
        <div class="card-header bg-white border-bottom p-2.5 p-md-3 d-flex align-items-center justify-content-between">
            <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 13.5px;">
                <i class="fa-solid fa-receipt text-primary"></i> Date-Wise Daily Production Log & Ledger Statement
            </h6>
            <span class="badge bg-light text-dark border px-2.5 py-1 small">
                Showing {{ $workLogs->count() }} of {{ $workLogs->total() }} Records
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 print-table">
                    <thead class="bg-light table-light small text-muted">
                        <tr>
                            <th class="ps-2.5" style="width: 14%;">Date & Voucher</th>
                            <th style="width: 10%;">Type</th>
                            <th style="width: 24%;">Book Title & Print Details</th>
                            <th class="text-center" style="width: 14%;">Bound Qty (আজকের বাঁধাই)</th>
                            <th class="text-center" style="width: 16%;">Production Balance</th>
                            <th class="text-center" style="width: 8%;">Rate</th>
                            <th class="text-end" style="width: 10%;">Earned (+)</th>
                            <th class="text-end" style="width: 10%;">Withdrawn (-)</th>
                            <th class="no-print text-end pe-2.5" style="width: 4%;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($workLogs as $log)
                            <tr>
                                <td class="ps-2.5 fw-semibold font-monospace text-dark" style="font-size: 9.5pt;">
                                    <div>{{ $log->log_date ? $log->log_date->format('d M, Y') : '—' }}</div>
                                    <span class="text-muted" style="font-size: 8pt;">{{ $log->voucher_no }}</span>
                                </td>
                                <td>
                                    @if($log->entry_type === 'work')
                                        <span class="badge border px-1.5 py-0.5 rounded-pill fw-bold" style="background-color: #f3e8ff; color: #7e22ce; border-color: #d8b4fe; font-size: 8pt;">
                                            <i class="fa-solid fa-book-bookmark me-0.5"></i> Binding
                                        </span>
                                    @else
                                        <span class="badge border px-1.5 py-0.5 rounded-pill fw-bold" style="background-color: #e0f2fe; color: #0284c7; border-color: #bae6fd; font-size: 8pt;">
                                            <i class="fa-solid fa-money-bill-transfer me-0.5"></i> Payout
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-bold text-dark" style="font-size: 9.5pt;">{{ $log->book_title ?: ($log->entry_type === 'work' ? 'Book Binding Work' : 'Cash Withdrawal / Payout') }}</div>
                                    @if($log->print_date)
                                        <div class="text-muted" style="font-size: 8pt;">
                                            <i class="fa-solid fa-print me-1 text-secondary"></i>Print: <strong>{{ $log->print_date->format('d M, Y') }}</strong>
                                        </div>
                                    @endif
                                    @if($log->notes)
                                        <div class="text-muted" style="font-size: 8pt;"><i class="fa-solid fa-info-circle me-1 text-primary"></i>{{ $log->notes }}</div>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($log->entry_type === 'work')
                                        <span class="badge px-2 py-0.5 fw-bold rounded-pill" style="background-color: #7e22ce; color: #ffffff; font-size: 9pt;">
                                            📖 {{ number_format((float)($log->quantity ?: ($log->delivered_quantity ?: $log->received_quantity))) }} pcs
                                        </span>
                                    @else
                                        <span class="text-muted" style="font-size: 8.5pt;">—</span>
                                    @endif
                                </td>
                                <td class="text-center font-monospace" style="font-size: 8.5pt;">
                                    @if($log->entry_type === 'work')
                                        <div>
                                            @if($log->printed_quantity > 0)
                                                <span class="text-muted">Printed: {{ number_format((float)$log->printed_quantity) }} · </span>
                                            @endif
                                            @if($log->incomplete_quantity > 0)
                                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-1 py-0.5 fw-bold" style="font-size: 8pt;">
                                                    Left: {{ number_format((float)$log->incomplete_quantity) }}
                                                </span>
                                            @else
                                                <span class="badge bg-success-subtle text-success border border-success-subtle px-1 py-0.5" style="font-size: 8pt;">
                                                    Done
                                                </span>
                                            @endif
                                            @if($log->wastage_quantity > 0)
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-1 py-0.5 ms-0.5" style="font-size: 8pt;">
                                                    Waste: {{ number_format((float)$log->wastage_quantity) }}
                                                </span>
                                            @endif
                                        </div>
                                    @elseif($log->entry_type === 'payment')
                                        <span class="badge bg-light text-muted border px-1.5 py-0.5" style="font-size: 8pt;">
                                            {{ strtoupper($log->payment_method) }}
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-center font-monospace" style="font-size: 9pt;">
                                    @if($log->entry_type === 'work' && $log->unit_rate > 0)
                                        <span class="fw-bold text-dark">৳{{ number_format($log->unit_rate, 2) }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-end fw-bold font-monospace" style="color: #7e22ce; font-size: 9.5pt;">
                                    @if($log->earned_amount > 0)
                                        +৳{{ number_format($log->earned_amount, 2) }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="text-end fw-bold text-primary font-monospace" style="font-size: 9.5pt;">
                                    @if($log->paid_amount > 0)
                                        -৳{{ number_format($log->paid_amount, 2) }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="no-print text-end pe-2.5">
                                    <form action="{{ route('admin.accounting.employees.work-logs.destroy', $log->id) }}" method="POST" data-confirm="আপনি কি নিশ্চিত যে এই লেজার রেকর্ডটি মুছে ফেলতে চান?" data-confirm-title="রেকর্ড ডিলিট">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger border-0 rounded-circle p-1" title="Delete Record">
                                            <i class="fa-solid fa-trash" style="font-size: 11px;"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">
                                    <i class="fa-solid fa-book-bookmark text-muted opacity-50 fs-3 mb-1"></i>
                                    <p class="small mb-0">No production logs or payout entries recorded yet.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="table-light fw-bold" style="border-top: 2px solid #0f172a !important;">
                            <td colspan="6" class="text-end py-1.5" style="font-size: 9.5pt;">Grand Total Summary:</td>
                            <td class="text-end font-monospace py-1.5" style="color: #7e22ce; font-size: 10.5pt;">
                                ৳{{ number_format($totalEarned, 2) }}
                            </td>
                            <td class="text-end text-primary font-monospace py-1.5" style="font-size: 10.5pt;">
                                ৳{{ number_format($totalPaid, 2) }}
                            </td>
                            <td class="no-print"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            @if($workLogs->hasPages())
                <div class="p-2.5 border-top d-flex justify-content-center no-print">
                    {{ $workLogs->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Official Printable Signatures Block -->
    <div class="print-only-block print-signatures">
        <div class="row g-4 pt-4">
            <div class="col-3">
                <div class="sig-line">Prepared By</div>
            </div>
            <div class="col-3">
                <div class="sig-line">Checked / Accounts</div>
            </div>
            <div class="col-3">
                <div class="sig-line">Worker / Artisan Signature</div>
            </div>
            <div class="col-3">
                <div class="sig-line">Authorized Signatory</div>
            </div>
        </div>
        <div class="text-center mt-3 text-muted" style="font-size: 7.5pt;">
            This is an official computer-generated production and accounting statement issued by Idea Prakashan Management System.
        </div>
    </div>

</div>

<!-- Modal: Add Book Binding Production Log -->
<div class="modal fade" id="addWorkModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form action="{{ route('admin.accounting.employees.work-logs.store', $employee->id) }}" method="POST" class="modal-content rounded-4 border-0 shadow-lg">
            @csrf
            <input type="hidden" name="entry_type" value="work">
            <div class="modal-header bg-dark text-white border-0 py-3">
                <h5 class="modal-title fw-bold fs-6 d-flex align-items-center gap-2">
                    <i class="fa-solid fa-book-bookmark text-purple" style="color: #c084fc;"></i> Book Binding & Production Log Entry
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-dark">
                            <i class="fa-solid fa-calendar-day me-1 text-primary"></i> Binding / Entry Date *
                        </label>
                        <input type="date" name="log_date" value="{{ date('Y-m-d') }}" class="form-control rounded-3" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-dark">
                            <i class="fa-solid fa-print me-1 text-secondary"></i> Book Print Date
                        </label>
                        <input type="date" name="print_date" id="work_print_date" value="{{ date('Y-m-d') }}" class="form-control rounded-3">
                    </div>

                    <div class="col-12">
                        <label class="form-label small fw-bold text-dark">
                            Book Title & Job Description *
                        </label>
                        <input type="text" list="existingBooksDatalist" name="book_title" id="work_book_title" class="form-control rounded-3" required placeholder="Type or select book title..." oninput="onBookTitleSelected(this.value)" autocomplete="off">
                        
                        <datalist id="existingBooksDatalist">
                            @if(isset($bookSummaries))
                                @foreach($bookSummaries as $book)
                                    <option value="{{ $book['book_title'] }}">
                                        Printed: {{ (float)$book['printed_qty'] }} | Delivered So Far: {{ (float)$book['total_delivered'] }} | Incomplete: {{ (float)$book['incomplete_qty'] }}
                                    </option>
                                @endforeach
                            @endif
                        </datalist>

                        <!-- Multi-day Notice Card (Dynamically shown when existing book selected) -->
                        <div id="multiDayNoticeCard" class="mt-2 p-2.5 bg-light rounded-3 border d-none">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <span class="small text-muted">
                                    <i class="fa-solid fa-clock-rotate-left text-primary me-1"></i> Multi-Day Job: 
                                    Previously Delivered: <strong id="notice_prev_delivered" class="text-success">0</strong> copies.
                                    Remaining before today: <strong id="notice_prev_incomplete" class="text-warning">0</strong> copies.
                                </span>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-0.5 small">
                                    Continuing Multi-Day Log
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Production Quantities Breakdown -->
                    <div class="col-12">
                        <div class="p-3 bg-light rounded-3 border">
                            <h6 class="fw-bold text-dark mb-2.5 small">
                                <i class="fa-solid fa-calculator text-primary me-1"></i> Production & Stock Reconciliation
                            </h6>
                            <div class="row g-2">
                                <div class="col-6 col-md-4">
                                    <label class="form-label small fw-semibold text-muted">1. Total Printed</label>
                                    <input type="number" step="0.01" name="printed_quantity" id="work_printed_qty" class="form-control form-control-sm font-monospace fw-bold" placeholder="e.g. 500" oninput="onPrintedQtyInput()">
                                </div>
                                <div class="col-6 col-md-4">
                                    <label class="form-label small fw-semibold text-muted">2. Received for Binding</label>
                                    <input type="number" step="0.01" name="received_quantity" id="work_received_qty" class="form-control form-control-sm font-monospace fw-bold" placeholder="e.g. 500" oninput="onProductionQtyChanged()">
                                </div>
                                <div class="col-6 col-md-4">
                                    <label class="form-label small fw-bold text-success">3. Delivered to Godown *</label>
                                    <input type="number" step="0.01" name="delivered_quantity" id="work_delivered_qty" class="form-control form-control-sm font-monospace fw-bold border-success" placeholder="Today's delivery e.g. 50" required oninput="onDeliveredQtyInput()">
                                </div>
                                <div class="col-6 col-md-4 mt-2">
                                    <label class="form-label small fw-bold text-warning">4. Incomplete (Auto)</label>
                                    <input type="number" step="0.01" name="incomplete_quantity" id="work_incomplete_qty" class="form-control form-control-sm font-monospace fw-bold bg-warning-subtle text-dark border-warning" placeholder="0" oninput="calcWorkModalEarned()">
                                </div>
                                <div class="col-6 col-md-4 mt-2">
                                    <label class="form-label small fw-bold text-success">5. Total Binding (Auto) *</label>
                                    <input type="number" step="0.01" name="quantity" id="work_total_binding" class="form-control form-control-sm font-monospace fw-bold bg-success-subtle text-success border-success" placeholder="0" required oninput="onTotalBindingInput()">
                                </div>
                                <div class="col-6 col-md-4 mt-2">
                                    <label class="form-label small fw-semibold text-danger">Wastage / Damage</label>
                                    <input type="number" step="0.01" name="wastage_quantity" id="work_wastage_qty" class="form-control form-control-sm font-monospace text-danger" placeholder="0" oninput="onProductionQtyChanged()">
                                </div>
                                <div class="col-12 mt-2">
                                    <span class="small text-muted" style="font-size: 11.5px;">
                                        <i class="fa-solid fa-info-circle text-primary me-1"></i> Multi-day calculation: Incomplete is auto-calculated: <strong>Total Printed - (2. Received for Binding + 3. Delivered to Godown + Wastage)</strong>. Today's bill amount is <strong>5. Total Binding × Rate</strong>.
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Rate & Bill Calculation -->
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-dark">Rate per Book / Unit (৳) *</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">৳</span>
                            <input type="number" step="0.01" name="unit_rate" id="work_modal_rate" value="{{ (float)$employee->basic_salary ?: '' }}" class="form-control font-monospace fw-bold" placeholder="e.g. 4.50" required oninput="calcWorkModalEarned()">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-dark">Total Billable Earned Amount (৳) *</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light fw-bold text-purple" style="color: #7e22ce;">৳</span>
                            <input type="number" step="0.01" name="earned_amount" id="work_modal_earned" class="form-control rounded-end-3 font-monospace fw-bold fs-5 text-purple" style="color: #7e22ce;" required placeholder="0.00">
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label small fw-semibold text-muted">Notes / Specifications</label>
                        <textarea name="notes" class="form-control rounded-3" rows="2" placeholder="Lot no, binding specifications, delivery notes..."></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-top p-3">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm" style="background-color: #7e22ce; border-color: #7e22ce;">
                    <i class="fa-solid fa-check me-1"></i> Save Binding Log
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Add Cash Withdrawal / Payment -->
<div class="modal fade" id="addWithdrawalModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('admin.accounting.employees.work-logs.store', $employee->id) }}" method="POST" class="modal-content rounded-4 border-0 shadow-lg">
            @csrf
            <input type="hidden" name="entry_type" value="payment">
            <div class="modal-header bg-dark text-white border-0 py-3">
                <h5 class="modal-title fw-bold fs-6 d-flex align-items-center gap-2">
                    <i class="fa-solid fa-hand-holding-dollar text-success"></i> Record Artisan Cash Withdrawal / Payout
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3">
                    <div class="col-12">
                        <div class="p-2.5 bg-light rounded-3 border d-flex justify-content-between align-items-center">
                            <span class="small text-muted fw-semibold">Current Outstanding Balance:</span>
                            <span class="fw-bold font-monospace fs-6 {{ $balanceDue > 0 ? 'text-danger' : 'text-success' }}">
                                ৳{{ number_format($balanceDue, 2) }}
                            </span>
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label small fw-bold text-dark">Payout Date *</label>
                        <input type="date" name="log_date" value="{{ date('Y-m-d') }}" class="form-control rounded-3" required>
                    </div>

                    <div class="col-12">
                        <label class="form-label small fw-bold text-dark">Amount Withdrawn (৳) *</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light fw-bold text-success">৳</span>
                            <input type="number" step="0.01" name="paid_amount" class="form-control rounded-end-3 font-monospace fw-bold fs-5 text-success" required placeholder="e.g. 3000">
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label small fw-bold text-dark">Payment Method *</label>
                        <select name="payment_method" class="form-select rounded-3 fw-semibold" required>
                            <option value="cash">Cash</option>
                            <option value="bkash">bKash</option>
                            <option value="nagad">Nagad</option>
                            <option value="rocket">Rocket</option>
                            <option value="bank">Bank Transfer</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label small fw-semibold text-muted">Notes / Remarks</label>
                        <textarea name="notes" class="form-control rounded-3" rows="2" placeholder="e.g. Weekly advance draw, food allowance, or partial work settlement..."></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-top p-3">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-success rounded-pill px-5 fw-bold shadow-sm">
                    <i class="fa-solid fa-check me-1"></i> Save Payout
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
const bookSummariesMap = @json($bookSummaries ?? []);
let currentPrevDelivered = 0;
let currentPrevWastage = 0;

function onBookTitleSelected(title) {
    title = (title || '').trim().toLowerCase();
    const found = bookSummariesMap.find(b => (b.book_title || '').trim().toLowerCase() === title);
    const noticeEl = document.getElementById('multiDayNoticeCard');

    if (found) {
        currentPrevDelivered = parseFloat(found.total_delivered) || 0;
        currentPrevWastage = parseFloat(found.total_wastage) || 0;

        if (found.printed_qty > 0) {
            document.getElementById('work_printed_qty').value = found.printed_qty;
        }
        if (found.received_qty > 0) {
            document.getElementById('work_received_qty').value = found.received_qty;
        }
        if (found.print_date) {
            document.getElementById('work_print_date').value = found.print_date;
        }
        if (found.unit_rate > 0) {
            document.getElementById('work_modal_rate').value = found.unit_rate;
        }

        if (noticeEl) {
            document.getElementById('notice_prev_delivered').textContent = currentPrevDelivered;
            document.getElementById('notice_prev_incomplete').textContent = found.incomplete_qty;
            noticeEl.classList.remove('d-none');
        }
    } else {
        currentPrevDelivered = 0;
        currentPrevWastage = 0;
        if (noticeEl) {
            noticeEl.classList.add('d-none');
        }
    }

    onProductionQtyChanged();
}

function onProductionQtyChanged() {
    const printed = parseFloat(document.getElementById('work_printed_qty').value) || 0;
    const received = parseFloat(document.getElementById('work_received_qty').value) || 0;
    const delivered = parseFloat(document.getElementById('work_delivered_qty').value) || 0;
    const wastage = parseFloat(document.getElementById('work_wastage_qty').value) || 0;

    // 5. Total Binding (Auto) = 2. Received for Binding + 3. Delivered to Godown
    const totalBinding = received + delivered;
    document.getElementById('work_total_binding').value = totalBinding;

    // 4. Incomplete (Auto) = 1. Total Printed - (2. Received for Binding + 3. Delivered to Godown + Wastage)
    const incomplete = printed > 0 ? Math.max(0, printed - (totalBinding + wastage)) : 0;
    document.getElementById('work_incomplete_qty').value = incomplete;

    // Total Earned Amount = 5. Total Binding * Rate
    const rate = parseFloat(document.getElementById('work_modal_rate').value) || 0;
    const earnedInput = document.getElementById('work_modal_earned');
    if (totalBinding > 0 && rate > 0) {
        earnedInput.value = (totalBinding * rate).toFixed(2);
    }
}

function onPrintedQtyInput() {
    onProductionQtyChanged();
}

function onDeliveredQtyInput() {
    onProductionQtyChanged();
}

function onTotalBindingInput() {
    calcWorkModalEarned();
}

function calcWorkModalEarned() {
    const totalBinding = parseFloat(document.getElementById('work_total_binding').value) || 0;
    const rate = parseFloat(document.getElementById('work_modal_rate').value) || 0;
    const earnedInput = document.getElementById('work_modal_earned');

    if (totalBinding > 0 && rate > 0) {
        earnedInput.value = (totalBinding * rate).toFixed(2);
    }
}

// Initial calculation bind
document.addEventListener('DOMContentLoaded', function() {
    ['work_printed_qty', 'work_received_qty', 'work_delivered_qty', 'work_total_binding', 'work_wastage_qty', 'work_modal_rate'].forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('input', onProductionQtyChanged);
            el.addEventListener('change', onProductionQtyChanged);
            el.addEventListener('keyup', onProductionQtyChanged);
        }
    });
});
</script>
@endpush
@endsection
