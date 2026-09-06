@extends('layouts.admin')

@php
    $role = $employee->getRoleConfig();
    $roleCat = $employee->getRoleCategory();
@endphp

@section('title', $employee->name . ' — ' . $role['title_bn'] . ' — Idea Prakashan')

@push('styles')
<style>
/* =========================================================
   INTERNATIONAL A4 PRINT STYLES FOR STAFF LEDGER
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
        font-size: 9pt !important;
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
    .filter-card,
    .staff-switcher-box {
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
        font-size: 16pt !important;
        font-weight: 800 !important;
        color: #0f172a !important;
        letter-spacing: -0.5px;
    }

    .print-doc-title {
        font-size: 10.5pt !important;
        font-weight: 700 !important;
        text-transform: uppercase;
        background-color: #f1f5f9 !important;
        padding: 3px 8px;
        display: inline-block;
        border: 1px solid #cbd5e1;
        letter-spacing: 0.5px;
    }

    /* Compact Staff & Summary Box */
    .print-artisan-grid {
        display: grid !important;
        grid-template-columns: 1.6fr 1fr;
        gap: 6px;
        border: 1px solid #cbd5e1;
        padding: 6px 8px;
        background-color: #f8fafc !important;
        margin-bottom: 8px;
        font-size: 8.5pt !important;
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
        font-size: 8pt !important;
        line-height: 1.2 !important;
        vertical-align: middle !important;
    }

    table.print-table thead th {
        background-color: #f1f5f9 !important;
        color: #0f172a !important;
        font-weight: 700 !important;
        text-transform: uppercase;
        font-size: 7.5pt !important;
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
        font-size: 8pt !important;
        font-weight: 700 !important;
    }

    /* Print Signatures */
    .print-signatures {
        display: block !important;
        margin-top: 24px;
        page-break-inside: avoid !important;
    }

    .sig-line {
        border-top: 1px solid #000000;
        padding-top: 3px;
        font-size: 8pt;
        text-align: center;
        font-weight: 600;
    }
}

/* Screen Display Styles */
.print-only-block {
    display: none;
}
.shadow-2xs {
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
}
.cursor-pointer {
    cursor: pointer;
}
.running-bal-pos {
    color: #dc2626;
    font-weight: 700;
}
.running-bal-neg {
    color: #16a34a;
    font-weight: 700;
}
.running-bal-zero {
    color: #64748b;
    font-weight: 600;
}
</style>
@endpush

@section('content')
<div class="container-fluid px-3 px-md-4 py-3">

    <!-- Print-Only Letterhead Header -->
    <div class="print-only-block print-letterhead">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <div class="print-company-name">আইডিয়া প্রকাশন | IDEA PRAKASHAN</div>
                <div style="font-size: 8pt; color: #334155;">
                    {{ $invoiceSettings['company_address'] ?? 'বাংলাবাজার, ঢাকা — বই প্রকাশনা, টাইপসেটিং, প্রুফ রিডিং ও বাঁধাই ব্যবস্থাপনা' }}
                    @if(!empty($invoiceSettings['company_phone'])) · Phone: {{ $invoiceSettings['company_phone'] }} @endif
                    @if(!empty($invoiceSettings['company_email'])) · Email: {{ $invoiceSettings['company_email'] }} @endif
                </div>
            </div>
            <div class="text-end">
                <div class="print-doc-title">{{ $role['title_bn'] }} (Statement)</div>
                <div style="font-size: 7.5pt; color: #475569; margin-top: 2px;">
                    Date: <strong>{{ date('d M, Y — h:i A') }}</strong>
                </div>
            </div>
        </div>
    </div>

    <!-- Print-Only Staff Info & Financial Summary Grid -->
    <div class="print-only-block print-artisan-grid">
        <div>
            <div><strong>Staff Name:</strong> {{ $employee->name }} ({{ $employee->designation }})</div>
            <div><strong>Department:</strong> {{ $employee->department }} @if($employee->skill_category) · {{ $employee->skill_category }} @endif · Phone: {{ $employee->phone ?: 'N/A' }}</div>
            <div><strong>Work Rate / Wage:</strong> {{ $employee->formatted_rate }}</div>
        </div>
        <div class="text-end">
            <div><strong>Total Earned:</strong> ৳{{ number_format($totalEarned, 2) }} ({{ number_format($totalWorkQuantity) }} {{ $role['unit_default'] }})</div>
            <div><strong>Total Paid / Drawn:</strong> ৳{{ number_format($totalPaid, 2) }}</div>
            <div><strong>Current Balance:</strong> <span style="font-weight: 800;">৳{{ number_format(abs($balanceDue), 2) }}</span> ({{ $balanceDue >= 0 ? 'Due to Staff' : 'Advance' }})</div>
        </div>
    </div>

    <!-- Screen Profile & Quick Action Header (No Print) -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white no-print">
        <div class="card-body p-3 p-md-4">
            <div class="d-flex flex-column flex-xl-row align-items-start align-items-xl-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle fw-bold d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm" 
                         style="width: 58px; height: 58px; background-color: {{ $role['bg_color'] }}; color: {{ $role['text_color'] }}; border: 2px solid {{ $role['border_color'] }}; font-size: 22px;">
                        <i class="{{ $role['icon'] }}"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-0.5 flex-wrap">
                            <h4 class="fw-bold text-dark mb-0">{{ $employee->name }}</h4>
                            <span class="badge border px-2.5 py-1 rounded-pill small fw-bold" style="background-color: {{ $role['bg_color'] }}; color: {{ $role['text_color'] }}; border-color: {{ $role['border_color'] }};">
                                {{ $employee->designation }}
                            </span>
                            <span class="badge bg-light text-secondary border rounded-pill px-2.5 py-0.5 small">
                                ID: #EMP-{{ str_pad($employee->id, 3, '0', STR_PAD_LEFT) }}
                            </span>
                        </div>
                        <p class="text-muted small mb-0">
                            <strong>{{ $role['title_bn'] }}</strong>
                            · Dept: <span class="text-dark fw-semibold">{{ $employee->department }}</span>
                            @if($employee->skill_category) · <span class="text-secondary">{{ $employee->skill_category }}</span> @endif
                            · Rate: <strong class="text-primary font-monospace">{{ $employee->formatted_rate }}</strong>
                            · Phone: <strong class="text-dark font-monospace">{{ $employee->phone ?: 'N/A' }}</strong>
                        </p>
                    </div>
                </div>

                <!-- Right Action Buttons & Quick Staff Switcher -->
                <div class="d-flex align-items-center gap-2 flex-wrap ms-xl-auto">
                    <!-- Quick Staff Switcher Dropdown -->
                    @if(isset($allEmployees) && $allEmployees->count() > 1)
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle rounded-pill px-3 py-2 fw-semibold shadow-2xs" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fa-solid fa-users me-1 text-primary"></i> Switch Staff
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 py-2" style="max-height: 320px; overflow-y: auto; width: 280px;">
                                <li class="dropdown-header small text-muted text-uppercase fw-bold">Select Staff / Artisan</li>
                                @foreach($allEmployees as $emp)
                                    <li>
                                        <a class="dropdown-item py-2 d-flex align-items-center justify-content-between {{ $emp->id == $employee->id ? 'active fw-bold' : '' }}" href="{{ route('admin.accounting.employees.ledger', $emp->id) }}">
                                            <div>
                                                <div class="text-truncate" style="max-width: 170px;">{{ $emp->name }}</div>
                                                <small class="opacity-75 d-block" style="font-size: 11px;">{{ $emp->designation }}</small>
                                            </div>
                                            @if($emp->id == $employee->id)
                                                <i class="fa-solid fa-check"></i>
                                            @endif
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <a href="{{ route('admin.accounting.employees.index') }}" class="btn btn-outline-secondary rounded-pill px-3 py-2 fw-semibold">
                        <i class="fa-solid fa-arrow-left me-1"></i> Staff List
                    </a>

                    <!-- Add Work / Task Log Button -->
                    <button type="button" class="btn text-white rounded-pill px-3.5 py-2 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#addWorkModal" style="background-color: {{ $role['accent_color'] }}; border-color: {{ $role['accent_color'] }};">
                        <i class="fa-solid fa-plus-circle me-1.5"></i> Add Work Log (কাজের হিসাব)
                    </button>

                    <!-- Record Cash Payout Button -->
                    <button type="button" class="btn btn-success rounded-pill px-3.5 py-2 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#addWithdrawalModal">
                        <i class="fa-solid fa-hand-holding-dollar me-1.5"></i> Record Payout (টাকা পরিশোধ)
                    </button>

                    <!-- Print Button -->
                    <button onclick="window.print()" class="btn btn-dark rounded-pill px-3 py-2 fw-bold shadow-sm">
                        <i class="fa-solid fa-print me-1"></i> Print A4
                    </button>
                </div>
            </div>

            <hr class="my-3 opacity-25">

            <!-- Summary KPI Badges (4 Cards) -->
            <div class="row g-3">
                <div class="col-sm-6 col-lg-3">
                    <div class="p-3 bg-light rounded-3 border d-flex align-items-center justify-content-between h-100" style="border-left: 4px solid {{ $role['accent_color'] }} !important;">
                        <div>
                            <span class="small text-muted fw-semibold">Total Work Earned (মোট উপার্জন)</span>
                            <h4 class="fw-bold mb-0 font-monospace" style="color: {{ $role['accent_color'] }};">৳{{ number_format($totalEarned, 2) }}</h4>
                            <span class="text-muted" style="font-size: 11.5px;">Completed: <strong class="text-dark font-monospace">{{ number_format($totalWorkQuantity) }}</strong> {{ $role['unit_default'] }}</span>
                        </div>
                        <span class="badge bg-white border p-2.5 rounded-circle fs-4" style="color: {{ $role['accent_color'] }};">
                            <i class="{{ $role['icon'] }}"></i>
                        </span>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="p-3 bg-light rounded-3 border d-flex align-items-center justify-content-between h-100" style="border-left: 4px solid #0284c7 !important;">
                        <div>
                            <span class="small text-muted fw-semibold">Total Paid / Drawn (পরিশোধিত টাকা)</span>
                            <h4 class="fw-bold text-primary mb-0 font-monospace">৳{{ number_format($totalPaid, 2) }}</h4>
                            <span class="text-muted" style="font-size: 11.5px;">Cash / bKash / Bank Draws</span>
                        </div>
                        <span class="badge bg-white text-primary border p-2.5 rounded-circle fs-4"><i class="fa-solid fa-hand-holding-dollar"></i></span>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="p-3 rounded-3 border d-flex align-items-center justify-content-between h-100" 
                         style="background-color: {{ $balanceDue > 0 ? '#fef2f2' : '#f0fdf4' }}; border-color: {{ $balanceDue > 0 ? '#fca5a5' : '#86efac' }} !important; border-left: 4px solid {{ $balanceDue > 0 ? '#dc2626' : '#16a34a' }} !important;">
                        <div>
                            <span class="small fw-semibold {{ $balanceDue > 0 ? 'text-danger' : 'text-success' }}">
                                {{ $balanceDue >= 0 ? 'Current Net Due (বকেয়া পাওনা)' : 'Advance Balance (অগ্রিম জমা)' }}
                            </span>
                            <h4 class="fw-bold mb-0 font-monospace {{ $balanceDue > 0 ? 'text-danger' : 'text-success' }}">
                                ৳{{ number_format(abs($balanceDue), 2) }}
                            </h4>
                            <span class="text-muted" style="font-size: 11.5px;">{{ $balanceDue > 0 ? 'Payable to Staff' : 'Advance drawn' }}</span>
                        </div>
                        <span class="badge bg-white border p-2.5 rounded-circle fs-4 {{ $balanceDue > 0 ? 'text-danger' : 'text-success' }}">
                            <i class="fa-solid fa-scale-balanced"></i>
                        </span>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="p-3 bg-light rounded-3 border d-flex align-items-center justify-content-between h-100" style="border-left: 4px solid #64748b !important;">
                        <div>
                            <span class="small text-muted fw-semibold">Wage & Rate Structure</span>
                            <h6 class="fw-bold text-dark mb-0 font-monospace text-truncate" style="max-width: 160px;" title="{{ $employee->formatted_rate }}">{{ $employee->formatted_rate }}</h6>
                            <span class="text-muted" style="font-size: 11.5px;">Type: <strong class="text-dark">{{ ucfirst($employee->salary_rate_type ?? 'Monthly') }}</strong></span>
                        </div>
                        <span class="badge bg-white text-secondary border p-2.5 rounded-circle fs-4"><i class="fa-solid fa-tags"></i></span>
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
                        <span class="input-group-text bg-light"><i class="fa-solid fa-search"></i></span>
                        <input type="text" name="search" value="{{ request('search') ?: request('book_title') }}" class="form-control" placeholder="Search Book / Task / Note...">
                    </div>
                </div>
                <div class="col-md-2">
                    <select name="entry_type" class="form-select form-select-sm">
                        <option value="">All Transactions (সকল)</option>
                        <option value="work" {{ request('entry_type') === 'work' ? 'selected' : '' }}>Work Logs Only (কাজের এন্ট্রি)</option>
                        <option value="payment" {{ request('entry_type') === 'payment' ? 'selected' : '' }}>Payouts & Draws (টাকা উত্তোলন)</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light">From</span>
                        <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control">
                    </div>
                </div>
                <div class="col-md-2">
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
                        <option value="all" {{ request('per_page') == 'all' ? 'selected' : '' }}>All (Full Print)</option>
                    </select>
                </div>
                <div class="col-md-1 d-flex gap-1">
                    <button type="submit" class="btn btn-sm btn-primary w-100 fw-bold" style="background-color: {{ $role['accent_color'] }}; border-color: {{ $role['accent_color'] }};">
                        <i class="fa-solid fa-filter"></i>
                    </button>
                    @if(request()->hasAny(['search', 'book_title', 'entry_type', 'date_from', 'date_to', 'per_page']))
                        <a href="{{ route('admin.accounting.employees.ledger', $employee->id) }}" class="btn btn-sm btn-outline-secondary" title="Reset Filters">
                            <i class="fa-solid fa-rotate-left"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Dynamic Work Summary Breakdown (Grouped by Book or Task) -->
    @if(isset($bookSummaries) && $bookSummaries->isNotEmpty())
        <!-- Book Binding / Press Artisan Multi-Day Summary -->
        <div class="card border-0 shadow-sm rounded-4 bg-white mb-4 overflow-hidden">
            <div class="card-header bg-light border-bottom p-2.5 p-md-3 d-flex align-items-center justify-content-between">
                <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 13.5px;">
                    <i class="fa-solid fa-book-open-reader" style="color: {{ $role['accent_color'] }};"></i> Book-Wise Multi-Day Binding Progress & Totals
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
                                        <span class="badge px-2 py-1 fw-bold rounded-pill" style="background-color: {{ $role['bg_color'] }}; color: {{ $role['text_color'] }}; border: 1px solid {{ $role['border_color'] }};">
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
                                                <div class="progress-bar bg-success" 
                                                     role="progressbar" 
                                                     style="width: {{ $book['progress'] }}%;" 
                                                     aria-valuenow="{{ $book['progress'] }}" aria-valuemin="0" aria-valuemax="100">
                                                </div>
                                            </div>
                                            <span class="small font-monospace fw-bold text-dark" style="font-size: 10px;">{{ $book['progress'] }}%</span>
                                        </div>
                                    </td>
                                    <td class="text-end pe-3 fw-bold font-monospace" style="color: {{ $role['accent_color'] }};">
                                        ৳{{ number_format($book['total_earned'], 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @elseif(isset($taskSummaries) && $taskSummaries->isNotEmpty())
        <!-- Task-Wise Summary for Computer Operators, Proofreaders, Peons, Marketing & Designers -->
        <div class="card border-0 shadow-sm rounded-4 bg-white mb-4 overflow-hidden">
            <div class="card-header bg-light border-bottom p-2.5 p-md-3 d-flex align-items-center justify-content-between">
                <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 13.5px;">
                    <i class="{{ $role['icon'] }}" style="color: {{ $role['accent_color'] }};"></i> {{ $role['work_label'] }} (Task & Book Summary)
                </h6>
                <span class="badge bg-white text-dark border px-2.5 py-1 small">
                    {{ $taskSummaries->count() }} Projects / Tasks Tracked
                </span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 print-table">
                        <thead class="bg-white table-light small text-muted">
                            <tr>
                                <th class="ps-3" style="width: 45%;">Book Title / Project / Task Name</th>
                                <th class="text-center" style="width: 15%;">Total Units Completed</th>
                                <th class="text-center" style="width: 12%;">Unit Rate</th>
                                <th class="text-center" style="width: 14%;">Entries / Days</th>
                                <th class="text-end pe-3" style="width: 14%;">Total Earned (৳)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($taskSummaries as $task)
                                <tr>
                                    <td class="ps-3">
                                        <div class="fw-bold text-dark">{{ $task['task_title'] }}</div>
                                        @if($task['last_log_date'])
                                            <div class="small text-muted mt-0.5" style="font-size: 10.5px;">
                                                <i class="fa-solid fa-clock-rotate-left me-1 text-primary"></i>Last updated: {{ $task['last_log_date'] }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="text-center font-monospace fw-bold text-dark">
                                        <span class="badge px-2.5 py-1 rounded-pill" style="background-color: {{ $role['bg_color'] }}; color: {{ $role['text_color'] }}; border: 1px solid {{ $role['border_color'] }};">
                                            {{ number_format($task['total_qty']) }} {{ $task['unit_name'] }}
                                        </span>
                                    </td>
                                    <td class="text-center font-monospace text-dark">
                                        ৳{{ number_format($task['unit_rate'], 2) }}
                                    </td>
                                    <td class="text-center text-muted small">
                                        {{ $task['entries_count'] }} logs
                                    </td>
                                    <td class="text-end pe-3 fw-bold font-monospace" style="color: {{ $role['accent_color'] }};">
                                        ৳{{ number_format($task['total_earned'], 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <!-- Daily Date-Wise Ledger Table with Running Balance (A4 Formatted) -->
    <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-3">
        <div class="card-header bg-white border-bottom p-2.5 p-md-3 d-flex align-items-center justify-content-between">
            <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 13.5px;">
                <i class="fa-solid fa-receipt text-primary"></i> Date-Wise Daily Ledger Statement & Running Balance
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
                            <th class="ps-2.5" style="width: 13%;">Date & Voucher</th>
                            <th style="width: 10%;">Type</th>
                            <th style="width: 25%;">Task / Book Title / Purpose</th>
                            <th class="text-center" style="width: 12%;">Output / Qty</th>
                            <th class="text-center" style="width: 8%;">Rate (৳)</th>
                            <th class="text-end" style="width: 10%;">Earned (+)</th>
                            <th class="text-end" style="width: 10%;">Paid (-)</th>
                            <th class="text-end" style="width: 12%;">Running Balance</th>
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
                                        <span class="badge border px-2 py-0.5 rounded-pill fw-bold" style="background-color: {{ $role['bg_color'] }}; color: {{ $role['text_color'] }}; border-color: {{ $role['border_color'] }}; font-size: 8pt;">
                                            <i class="{{ $role['icon'] }} me-0.5"></i> Work
                                        </span>
                                    @else
                                        <span class="badge border px-2 py-0.5 rounded-pill fw-bold" style="background-color: #e0f2fe; color: #0284c7; border-color: #bae6fd; font-size: 8pt;">
                                            <i class="fa-solid fa-money-bill-transfer me-0.5"></i> Payout
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-bold text-dark" style="font-size: 9.5pt;">
                                        {{ $log->book_title ?: ($log->entry_type === 'work' ? 'General Staff Work / Task' : 'Cash Withdrawal / Payout') }}
                                    </div>
                                    @if($log->print_date)
                                        <div class="text-muted" style="font-size: 8pt;">
                                            <i class="fa-solid fa-print me-1 text-secondary"></i>Print Date: <strong>{{ $log->print_date->format('d M, Y') }}</strong>
                                        </div>
                                    @endif
                                    @if($log->notes)
                                        <div class="text-muted" style="font-size: 8pt;"><i class="fa-solid fa-info-circle me-1 text-primary"></i>{{ $log->notes }}</div>
                                    @endif
                                </td>
                                <td class="text-center font-monospace">
                                    @if($log->entry_type === 'work')
                                        <span class="badge px-2 py-0.5 fw-bold rounded-pill text-dark border" style="background-color: #f8fafc; font-size: 9pt;">
                                            {{ number_format((float)($log->quantity ?: 1)) }} {{ $log->unit_name ?: $role['unit_default'] }}
                                        </span>
                                    @else
                                        <span class="text-muted" style="font-size: 8pt;">
                                            <i class="fa-solid fa-wallet text-secondary me-0.5"></i> {{ ucfirst($log->payment_method ?? 'Cash') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center font-monospace text-dark" style="font-size: 9pt;">
                                    {{ (float)$log->unit_rate > 0 ? '৳' . number_format((float)$log->unit_rate, 2) : '—' }}
                                </td>
                                <td class="text-end fw-bold font-monospace" style="color: {{ $role['accent_color'] }}; font-size: 9.5pt;">
                                    {{ (float)$log->earned_amount > 0 ? '৳' . number_format((float)$log->earned_amount, 2) : '—' }}
                                </td>
                                <td class="text-end fw-bold text-primary font-monospace" style="font-size: 9.5pt;">
                                    {{ (float)$log->paid_amount > 0 ? '৳' . number_format((float)$log->paid_amount, 2) : '—' }}
                                </td>
                                <td class="text-end font-monospace pe-2" style="font-size: 9.5pt;">
                                    @php $rBal = (float)($log->running_balance ?? 0); @endphp
                                    <span class="{{ $rBal > 0 ? 'running-bal-pos' : ($rBal < 0 ? 'running-bal-neg' : 'running-bal-zero') }}">
                                        ৳{{ number_format(abs($rBal), 2) }}
                                        <small style="font-size: 7.5pt; font-weight: normal;">{{ $rBal > 0 ? '(Due)' : ($rBal < 0 ? '(Adv)' : '') }}</small>
                                    </span>
                                </td>
                                <td class="no-print text-end pe-2.5">
                                    <form action="{{ route('admin.accounting.employees.work-logs.destroy', $log->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this log entry?');" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-link text-danger p-0 text-decoration-none" title="Delete Log Entry">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <div class="py-4">
                                        <i class="{{ $role['icon'] }} fs-1 opacity-25 d-block mb-2"></i>
                                        <h6 class="fw-bold text-dark">No Ledger Records Found</h6>
                                        <p class="small text-muted mb-3">Add daily work logs or cash withdrawals to start building this staff ledger.</p>
                                        <button type="button" class="btn btn-sm text-white rounded-pill px-3 py-1.5 fw-semibold" data-bs-toggle="modal" data-bs-target="#addWorkModal" style="background-color: {{ $role['accent_color'] }};">
                                            <i class="fa-solid fa-plus-circle me-1"></i> Add First Work Log
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($workLogs->isNotEmpty())
                        <tfoot class="bg-light table-light fw-bold">
                            <tr>
                                <td colspan="5" class="ps-2.5 text-uppercase small text-dark">
                                    Grand Totals ({{ $workLogs->total() }} Records)
                                </td>
                                <td class="text-end font-monospace" style="color: {{ $role['accent_color'] }}; font-size: 10pt;">
                                    ৳{{ number_format($totalEarned, 2) }}
                                </td>
                                <td class="text-end text-primary font-monospace" style="font-size: 10pt;">
                                    ৳{{ number_format($totalPaid, 2) }}
                                </td>
                                <td class="text-end font-monospace pe-2" style="font-size: 10pt;">
                                    <span class="{{ $balanceDue > 0 ? 'text-danger' : 'text-success' }}">
                                        ৳{{ number_format(abs($balanceDue), 2) }}
                                    </span>
                                </td>
                                <td class="no-print"></td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>

        @if($workLogs->hasPages())
            <div class="card-footer bg-white border-top p-3 no-print d-flex justify-content-between align-items-center">
                <span class="text-muted small">Showing {{ $workLogs->firstItem() }} - {{ $workLogs->lastItem() }} of {{ $workLogs->total() }} records</span>
                {{ $workLogs->links() }}
            </div>
        @endif
    </div>

    <!-- Print Signatures Block -->
    <div class="print-only-block print-signatures">
        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 30px; margin-top: 35px;">
            <div class="sig-line">Staff / Artisan Signature</div>
            <div class="sig-line">Accountant Verification</div>
            <div class="sig-line">Idea Prakashan Management</div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 1: ADD WORK / TASK LOG ENTRY                                        -->
<!-- ========================================================================= -->
<div class="modal fade" id="addWorkModal" tabindex="-1" aria-labelledby="addWorkModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header text-white border-0 py-3 px-4" style="background-color: {{ $role['accent_color'] }};">
                <h5 class="modal-title fw-bold text-white mb-0" id="addWorkModalLabel">
                    <i class="{{ $role['icon'] }} me-2"></i> {{ $role['title_bn'] }} — কাজের হিসাব এন্ট্রি
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.accounting.employees.work-logs.store', $employee->id) }}" method="POST" id="workLogForm">
                @csrf
                <input type="hidden" name="entry_type" value="work">

                <div class="modal-body p-4">
                    <!-- Staff Info Card Inside Modal -->
                    <div class="p-2.5 rounded-3 mb-3 d-flex align-items-center justify-content-between" style="background-color: {{ $role['bg_color'] }}; border: 1px solid {{ $role['border_color'] }};">
                        <div>
                            <span class="fw-bold text-dark">{{ $employee->name }}</span> ({{ $employee->designation }})
                            <div class="small text-muted">Preset Rate: <strong class="text-primary font-monospace">{{ $employee->formatted_rate }}</strong></div>
                        </div>
                        <span class="badge rounded-pill px-3 py-1 font-monospace" style="background-color: #ffffff; color: {{ $role['text_color'] }}; border: 1px solid {{ $role['border_color'] }};">
                            Current Due: ৳{{ number_format($balanceDue, 2) }}
                        </span>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Date (কাজের তারিখ) <span class="text-danger">*</span></label>
                            <input type="date" name="log_date" value="{{ date('Y-m-d') }}" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Book / Project / Task Title (বই বা কাজের নাম) <span class="text-danger">*</span></label>
                            <input type="text" name="book_title" class="form-control" placeholder="e.g. বাংলা ব্যাকরণ সহায়িকা / কভার ডিজাইন / টাইপসেটিং" required>
                        </div>

                        <!-- Unit & Quantity Section -->
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-dark">Work Unit (একক) <span class="text-danger">*</span></label>
                            <select name="unit_name" id="workUnitSelect" class="form-select" onchange="calculateWorkTotal()">
                                @foreach($role['units'] as $u)
                                    <option value="{{ $u }}" {{ str_contains($u, $role['unit_default']) ? 'selected' : '' }}>{{ $u }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-dark">Completed Quantity (পরিমাণ / সংখ্যা) <span class="text-danger">*</span></label>
                            <input type="number" step="any" name="quantity" id="workQuantityInput" value="1" class="form-control font-monospace fw-bold" placeholder="e.g. 100" required oninput="calculateWorkTotal()">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-dark">Unit Rate (প্রতি এককের দর ৳) <span class="text-danger">*</span></label>
                            <input type="number" step="any" name="unit_rate" id="workUnitRateInput" value="{{ $employee->basic_salary ?: 0 }}" class="form-control font-monospace fw-bold text-primary" required oninput="calculateWorkTotal()">
                        </div>

                        <div class="col-12">
                            <div class="p-3 bg-light rounded-3 border d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="small text-muted d-block">Total Calculated Earnings (মোট উপার্জিত মজুরি):</span>
                                    <h4 class="fw-bold mb-0 font-monospace" id="displayEarnedAmount" style="color: {{ $role['accent_color'] }};">
                                        ৳{{ number_format($employee->basic_salary ?: 0, 2) }}
                                    </h4>
                                </div>
                                <div class="w-50">
                                    <label class="form-label small text-muted mb-1">Override Total Earned (৳)</label>
                                    <input type="number" step="any" name="earned_amount" id="workEarnedAmountInput" value="{{ $employee->basic_salary ?: 0 }}" class="form-control form-control-sm font-monospace fw-bold text-end">
                                </div>
                            </div>
                        </div>

                        <!-- Press / Binding Advanced Details (Collapsible) -->
                        @if($roleCat === 'artisan')
                            <div class="col-12">
                                <div class="accordion" id="pressDetailsAccordion">
                                    <div class="accordion-item border rounded-3">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed py-2 px-3 small fw-bold text-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#pressDetailsCollapse">
                                                <i class="fa-solid fa-sliders me-2"></i> Press & Multi-Day Production Quantities (ঐচ্ছিক / বিস্তারিত বাঁধাই হিসাব)
                                            </button>
                                        </h2>
                                        <div id="pressDetailsCollapse" class="accordion-collapse collapse" data-bs-parent="#pressDetailsAccordion">
                                            <div class="accordion-body p-3 bg-light">
                                                <div class="row g-2">
                                                    <div class="col-md-4">
                                                        <label class="form-label small text-muted">Print Date (ছাপার তারিখ)</label>
                                                        <input type="date" name="print_date" class="form-control form-control-sm">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label small text-muted">Total Printed (মোট ছাপা সংখ্যা)</label>
                                                        <input type="number" step="any" name="printed_quantity" class="form-control form-control-sm font-monospace">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label small text-muted">Received (বাঁধাইয়ের জন্য গ্রহণ)</label>
                                                        <input type="number" step="any" name="received_quantity" class="form-control form-control-sm font-monospace">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label small text-muted">Delivered to Godown (গোডাউনে জমা)</label>
                                                        <input type="number" step="any" name="delivered_quantity" class="form-control form-control-sm font-monospace">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label small text-muted">Wastage / Defect (নষ্ট / ওয়েস্টেজ)</label>
                                                        <input type="number" step="any" name="wastage_quantity" class="form-control form-control-sm font-monospace">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label small text-muted">Incomplete Left (অবশিষ্ট সংখ্যা)</label>
                                                        <input type="number" step="any" name="incomplete_quantity" class="form-control form-control-sm font-monospace">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="col-12">
                            <label class="form-label small fw-bold text-dark">Notes / Remarks (কাজের নোট বা মন্তব্য)</label>
                            <textarea name="notes" rows="2" class="form-control rounded-3" placeholder="e.g. ১ম খণ্ডের ১-১২০ পৃষ্ঠা টাইপ সম্পন্ন / কাভার ল্যামিনেশন ও বাঁধাই ডেলিভারি"></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light border-0 py-3 px-4">
                    <button type="button" class="btn btn-light border rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn text-white rounded-pill px-4 fw-bold shadow-sm" style="background-color: {{ $role['accent_color'] }};">
                        <i class="fa-solid fa-check-circle me-1"></i> Save Work Log (কাজের হিসাব সংরক্ষণ)
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 2: RECORD CASH PAYOUT / SALARY DRAW                                  -->
<!-- ========================================================================= -->
<div class="modal fade" id="addWithdrawalModal" tabindex="-1" aria-labelledby="addWithdrawalModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-success text-white border-0 py-3 px-4">
                <h5 class="modal-title fw-bold text-white mb-0" id="addWithdrawalModalLabel">
                    <i class="fa-solid fa-hand-holding-dollar me-2"></i> Record Cash Payout (টাকা পরিশোধ / উত্তোলন)
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.accounting.employees.work-logs.store', $employee->id) }}" method="POST">
                @csrf
                <input type="hidden" name="entry_type" value="payment">

                <div class="modal-body p-4">
                    <div class="p-2.5 rounded-3 mb-3 bg-success-subtle border border-success-subtle d-flex align-items-center justify-content-between">
                        <div>
                            <span class="fw-bold text-dark">{{ $employee->name }}</span> ({{ $employee->designation }})
                            <div class="small text-muted">Department: {{ $employee->department }}</div>
                        </div>
                        <div class="text-end">
                            <span class="small text-muted d-block">Current Due:</span>
                            <strong class="text-danger font-monospace fs-6">৳{{ number_format($balanceDue, 2) }}</strong>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Payment Date (টাকা পরিশোধের তারিখ) <span class="text-danger">*</span></label>
                            <input type="date" name="log_date" value="{{ date('Y-m-d') }}" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Paid Amount (টাকার পরিমাণ ৳) <span class="text-danger">*</span></label>
                            <input type="number" step="any" name="paid_amount" class="form-control font-monospace fw-bold fs-5 text-success" placeholder="e.g. 5000" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Payment Method (পরিশোধের মাধ্যম) <span class="text-danger">*</span></label>
                            <select name="payment_method" class="form-select" required>
                                <option value="cash" selected>💵 Cash (নগদ ক্যাশ)</option>
                                <option value="bkash">📱 bKash (বিকাশ)</option>
                                <option value="nagad">📱 Nagad (নগদ)</option>
                                <option value="rocket">📱 Rocket (রকেট)</option>
                                <option value="bank">🏦 Bank Transfer (ব্যাংক)</option>
                                <option value="cheque">📄 Cheque (চেক)</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Purpose / Expense Head (ব্যয়ের খাত)</label>
                            <select name="expense_category" class="form-select">
                                <option value="">Auto-Assign by Staff Role (স্বয়ংক্রিয় খাত)</option>
                                <option value="কম্পিউটার টাইপ, কম্পোজ ও মেকআপ মজুরি">কম্পিউটার টাইপ, কম্পোজ ও মেকআপ মজুরি</option>
                                <option value="প্রুফ রিডিং ও সম্পাদনা মজুরি">প্রুফ রিডিং ও সম্পাদনা মজুরি</option>
                                <option value="চুক্তিভিত্তিক ও বাইন্ডিং মজুরি (Piece-rate Wages)">চুক্তিভিত্তিক ও বাইন্ডিং মজুরি</option>
                                <option value="অফিস সহায়ক ও স্টাফ বেতন/ভাতা">অফিস সহায়ক ও স্টাফ বেতন/ভাতা</option>
                                <option value="মার্কেটিং ও সেলস বেতন/টিএ/কমিশন">মার্কেটিং ও সেলস বেতন/টিএ/কমিশন</option>
                                <option value="গ্রাফিক্স ও কভার ডিজাইন মজুরি">গ্রাফিক্স ও কভার ডিজাইন মজুরি</option>
                                <option value="স্টাফ অগ্রিম উত্তোলন (Advance Salary/Wage)">স্টাফ অগ্রিম উত্তোলন (Advance)</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold text-dark">Notes / Reference (ভাউচার নোট বা কারণ)</label>
                            <textarea name="notes" rows="2" class="form-control rounded-3" placeholder="e.g. সাপ্তাহিক মজুরি পরিশোধ / হাতখরচ / চলতি মাসের অগ্রিম বাবদ"></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light border-0 py-3 px-4">
                    <button type="button" class="btn btn-light border rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold shadow-sm">
                        <i class="fa-solid fa-hand-holding-dollar me-1"></i> Confirm Payout (টাকা পরিশোধ নিশ্চিত করুন)
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function calculateWorkTotal() {
    const qty = parseFloat(document.getElementById('workQuantityInput')?.value) || 0;
    const rate = parseFloat(document.getElementById('workUnitRateInput')?.value) || 0;
    const total = qty * rate;

    const displayEl = document.getElementById('displayEarnedAmount');
    const earnedInput = document.getElementById('workEarnedAmountInput');

    if (displayEl) {
        displayEl.textContent = '৳' + total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
    if (earnedInput) {
        earnedInput.value = total.toFixed(2);
    }
}
</script>
@endpush
@endsection
