@extends('layouts.admin')

@section('title', $employee->name . ' — Work Log & Cash Ledger — Idea Prakashan')

@section('content')
<div class="container-fluid px-3 px-md-4 py-3">

    <!-- Top Action & Profile Header -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
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

                <div class="d-flex align-items-center gap-2 flex-wrap ms-md-auto no-print">
                    <a href="{{ route('admin.accounting.employees.index') }}" class="btn btn-outline-secondary rounded-pill px-3 py-2 fw-semibold">
                        <i class="fa-solid fa-arrow-left me-1"></i> Staff Directory
                    </a>
                    <button type="button" class="btn btn-primary rounded-pill px-3.5 py-2 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#addWorkModal" style="background-color: #7e22ce; border-color: #7e22ce;">
                        <i class="fa-solid fa-book-bookmark me-1.5"></i> Add Book Binding Log
                    </button>
                    <button type="button" class="btn btn-success rounded-pill px-3.5 py-2 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#addWithdrawalModal">
                        <i class="fa-solid fa-hand-holding-dollar me-1.5"></i> Record Cash Payout
                    </button>
                    <button onclick="window.print()" class="btn btn-dark rounded-pill px-3 py-2 fw-semibold">
                        <i class="fa-solid fa-print me-1"></i> Print
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
                            <span class="text-muted" style="font-size: 11.5px;">Godown Delivered: <strong class="text-dark font-monospace">{{ (float)$totalWorkQuantity }}</strong> Books / Units</span>
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

    <!-- Multi-Day Book Production Progress Summary (Grouped by Book) -->
    @if(isset($bookSummaries) && $bookSummaries->isNotEmpty())
        <div class="card border-0 shadow-sm rounded-4 bg-white mb-4 overflow-hidden">
            <div class="card-header bg-light border-bottom p-3 d-flex align-items-center justify-content-between">
                <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                    <i class="fa-solid fa-book-open-reader text-purple" style="color: #7e22ce;"></i> Book-Wise Multi-Day Binding Progress & Totals
                </h6>
                <span class="badge bg-white text-dark border px-2.5 py-1 small">
                    {{ $bookSummaries->count() }} Books Tracked
                </span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-white table-light small text-muted">
                            <tr>
                                <th class="ps-3.5" style="min-width: 220px;">Book Title & Print Date</th>
                                <th class="text-center" style="min-width: 110px;">Total Printed</th>
                                <th class="text-center" style="min-width: 120px;">Received</th>
                                <th class="text-center" style="min-width: 140px;">Total Bound & Delivered</th>
                                <th class="text-center" style="min-width: 130px;">Incomplete Left</th>
                                <th class="text-center" style="min-width: 150px;">Progress</th>
                                <th class="text-end pe-3.5" style="min-width: 130px;">Total Earned (৳)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bookSummaries as $book)
                                <tr>
                                    <td class="ps-3.5">
                                        <div class="fw-bold text-dark fs-6">{{ $book['book_title'] }}</div>
                                        <div class="small text-muted d-flex align-items-center gap-2 mt-0.5" style="font-size: 11px;">
                                            @if($book['print_date'])
                                                <span><i class="fa-solid fa-print me-1 text-secondary"></i>Print: <strong>{{ $book['print_date'] }}</strong></span>
                                            @endif
                                            <span><i class="fa-solid fa-calendar-days me-1 text-primary"></i>{{ $book['days_count'] }} Days ({{ $book['entries_count'] }} logs)</span>
                                            @if($book['last_log_date'])
                                                <span>· Last Log: {{ $book['last_log_date'] }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-center font-monospace fw-bold text-dark fs-6">
                                        {{ (float)$book['printed_qty'] > 0 ? number_format((float)$book['printed_qty']) : '—' }}
                                    </td>
                                    <td class="text-center font-monospace fw-bold text-dark fs-6">
                                        {{ (float)$book['received_qty'] > 0 ? number_format((float)$book['received_qty']) : '—' }}
                                    </td>
                                    <td class="text-center font-monospace">
                                        <span class="badge px-3 py-1.5 fw-bold fs-6 rounded-pill" style="background-color: #f3e8ff; color: #7e22ce; border: 1px solid #d8b4fe;">
                                            📦 {{ number_format((float)$book['total_delivered']) }} pcs
                                        </span>
                                    </td>
                                    <td class="text-center font-monospace">
                                        @if($book['incomplete_qty'] > 0)
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2.5 py-1 fw-bold rounded-pill">
                                                ⏳ {{ number_format((float)$book['incomplete_qty']) }} Left
                                            </span>
                                        @else
                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 rounded-pill">
                                                <i class="fa-solid fa-check-double me-1"></i> Completed
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-grow-1" style="height: 8px;">
                                                <div class="progress-bar {{ $book['progress'] >= 100 ? 'bg-success' : 'bg-purple' }}" 
                                                     role="progressbar" 
                                                     style="width: {{ $book['progress'] }}%; {{ $book['progress'] < 100 ? 'background-color: #7e22ce;' : '' }}" 
                                                     aria-valuenow="{{ $book['progress'] }}" aria-valuemin="0" aria-valuemax="100">
                                                </div>
                                            </div>
                                            <span class="small font-monospace fw-bold text-dark" style="font-size: 11px;">{{ $book['progress'] }}%</span>
                                        </div>
                                    </td>
                                    <td class="text-end pe-3.5 fw-bold font-monospace fs-6" style="color: #7e22ce;">
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

    <!-- Daily Ledger Table -->
    <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-4">
        <div class="card-header bg-white border-bottom p-3 d-flex align-items-center justify-content-between">
            <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                <i class="fa-solid fa-receipt text-primary"></i> Date-Wise Daily Production Log & Ledger Statement
            </h6>
            <span class="badge bg-light text-dark border px-2.5 py-1 small">
                Total {{ $workLogs->total() }} Records
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light table-light small text-muted">
                        <tr>
                            <th class="ps-3.5" style="width: 125px;">Date & Voucher</th>
                            <th style="width: 105px;">Type</th>
                            <th style="min-width: 210px;">Book Title & Print Details</th>
                            <th class="text-center" style="min-width: 140px; background-color: #faf5ff;">Bound Quantity (কতগুলো বাঁধাই হলো)</th>
                            <th class="text-center" style="min-width: 180px;">Production Balance</th>
                            <th class="text-center" style="width: 110px;">Rate</th>
                            <th class="text-end" style="width: 125px;">Earned (+)</th>
                            <th class="text-end" style="width: 125px;">Withdrawn (-)</th>
                            <th class="no-print text-end pe-3.5" style="width: 60px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($workLogs as $log)
                            <tr>
                                <td class="ps-3.5 fw-semibold font-monospace small text-dark">
                                    <div>{{ $log->log_date ? $log->log_date->format('d M, Y') : '—' }}</div>
                                    <span class="text-muted" style="font-size: 9.5px;">{{ $log->voucher_no }}</span>
                                </td>
                                <td>
                                    @if($log->entry_type === 'work')
                                        <span class="badge border px-2 py-0.5 small rounded-pill fw-bold" style="background-color: #f3e8ff; color: #7e22ce; border-color: #d8b4fe; font-size: 11px;">
                                            <i class="fa-solid fa-book-bookmark me-1"></i> Binding
                                        </span>
                                    @else
                                        <span class="badge border px-2 py-0.5 small rounded-pill fw-bold" style="background-color: #e0f2fe; color: #0284c7; border-color: #bae6fd; font-size: 11px;">
                                            <i class="fa-solid fa-money-bill-transfer me-1"></i> Payout
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $log->book_title ?: ($log->entry_type === 'work' ? 'Book Binding Work' : 'Cash Withdrawal / Payout') }}</div>
                                    @if($log->print_date)
                                        <div class="small text-muted" style="font-size: 11px;">
                                            <i class="fa-solid fa-print me-1 text-secondary"></i>Print: <strong>{{ $log->print_date->format('d M, Y') }}</strong>
                                        </div>
                                    @endif
                                    @if($log->notes)
                                        <div class="small text-muted mt-0.5" style="font-size: 10.5px;"><i class="fa-solid fa-info-circle me-1 text-primary"></i>{{ $log->notes }}</div>
                                    @endif
                                </td>
                                <td class="text-center" style="background-color: #faf5ff;">
                                    @if($log->entry_type === 'work')
                                        <span class="badge px-3 py-1.5 fw-bold fs-6 rounded-pill shadow-2xs" style="background-color: #7e22ce; color: #ffffff;">
                                            📖 {{ number_format((float)($log->quantity ?: ($log->delivered_quantity ?: $log->received_quantity))) }} pcs
                                        </span>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                                <td class="text-center font-monospace small">
                                    @if($log->entry_type === 'work')
                                        <div class="d-flex flex-column gap-0.5 align-items-center">
                                            <div>
                                                @if($log->printed_quantity > 0)
                                                    <span class="text-muted" style="font-size: 10.5px;">Printed: <strong>{{ number_format((float)$log->printed_quantity) }}</strong> · </span>
                                                @endif
                                                @if($log->incomplete_quantity > 0)
                                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-1.5 py-0.5 fw-bold" style="font-size: 10.5px;">
                                                        Left: {{ number_format((float)$log->incomplete_quantity) }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-1.5 py-0.5" style="font-size: 10.5px;">
                                                        Done
                                                    </span>
                                                @endif
                                                @if($log->wastage_quantity > 0)
                                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-1.5 py-0.5 ms-0.5" style="font-size: 10.5px;">
                                                        Waste: {{ number_format((float)$log->wastage_quantity) }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    @elseif($log->entry_type === 'payment')
                                        <span class="badge bg-light text-muted border px-2 py-1 small">
                                            Method: {{ strtoupper($log->payment_method) }}
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-center font-monospace small">
                                    @if($log->entry_type === 'work' && $log->unit_rate > 0)
                                        <span class="fw-bold text-dark">৳{{ number_format($log->unit_rate, 2) }}</span>
                                        <div class="text-muted" style="font-size: 9.5px;">/ {{ $log->unit_name ?: 'Book' }}</div>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-end fw-bold font-monospace" style="color: #7e22ce;">
                                    @if($log->earned_amount > 0)
                                        +৳{{ number_format($log->earned_amount, 2) }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="text-end fw-bold text-primary font-monospace">
                                    @if($log->paid_amount > 0)
                                        -৳{{ number_format($log->paid_amount, 2) }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="no-print text-end pe-3.5">
                                    <form action="{{ route('admin.accounting.employees.work-logs.destroy', $log->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this ledger record?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger border-0 rounded-circle p-1.5" title="Delete Record">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-book-bookmark text-muted opacity-50 fs-2 mb-2"></i>
                                    <p class="small mb-0">No production logs or payout entries recorded yet.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="table-light fw-bold">
                            <td colspan="6" class="text-end py-2.5">Total Summary:</td>
                            <td class="text-end font-monospace py-2.5" style="color: #7e22ce; font-size: 15px;">
                                ৳{{ number_format($totalEarned, 2) }}
                            </td>
                            <td class="text-end text-primary font-monospace py-2.5" style="font-size: 15px;">
                                ৳{{ number_format($totalPaid, 2) }}
                            </td>
                            <td class="no-print"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            @if($workLogs->hasPages())
                <div class="p-3 border-top d-flex justify-content-center no-print">
                    {{ $workLogs->links() }}
                </div>
            @endif
        </div>
    </div>

</div>

<!-- Modal: Add Book Binding Production Log (Supports Multi-Day Book Binding) -->
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
                                        <i class="fa-solid fa-info-circle text-primary me-1"></i> Multi-day calculation: Incomplete is auto-calculated: <strong>Total Printed - (Previously Delivered + Today's Delivered + Wastage)</strong>. Today's bill amount is <strong>Total Binding × Rate</strong>.
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
