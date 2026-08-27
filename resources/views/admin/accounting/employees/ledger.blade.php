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
                        <i class="fa-solid fa-arrow-left me-1"></i> Back to Staff Directory
                    </a>
                    <button type="button" class="btn btn-primary rounded-pill px-3.5 py-2 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#addWorkModal">
                        <i class="fa-solid fa-plus me-1"></i> Add Work Entry
                    </button>
                    <button type="button" class="btn btn-success rounded-pill px-3.5 py-2 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#addWithdrawalModal">
                        <i class="fa-solid fa-money-bill-wave me-1"></i> Record Cash Payout
                    </button>
                    <button onclick="window.print()" class="btn btn-dark rounded-pill px-3 py-2 fw-semibold">
                        <i class="fa-solid fa-print me-1"></i> Print Statement
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
                            <span class="text-muted" style="font-size: 11.5px;">Completed: <strong class="text-dark font-monospace">{{ (float)$totalWorkQuantity }}</strong> Books / Units</span>
                        </div>
                        <span class="badge bg-white border p-2.5 rounded-circle fs-4" style="color: #7e22ce;"><i class="fa-solid fa-book-bookmark"></i></span>
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

    <!-- Ledger Table -->
    <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-4">
        <div class="card-header bg-white border-bottom p-3 d-flex align-items-center justify-content-between">
            <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                <i class="fa-solid fa-receipt text-primary"></i> Daily Work Log & Cash Ledger Statement
            </h6>
            <span class="badge bg-light text-dark border px-2.5 py-1 small">
                Total {{ $workLogs->total() }} Entries
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light table-light small text-muted">
                        <tr>
                            <th class="ps-3.5" style="width: 130px;">Date</th>
                            <th style="width: 140px;">Entry Type</th>
                            <th style="min-width: 250px;">Work Description / Book Title / Remarks</th>
                            <th class="text-center" style="width: 160px;">Quantity & Rate</th>
                            <th class="text-end" style="width: 140px;">Earned (+)</th>
                            <th class="text-end" style="width: 140px;">Withdrawn (-)</th>
                            <th class="no-print text-end pe-3.5" style="width: 80px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($workLogs as $log)
                            <tr>
                                <td class="ps-3.5 fw-semibold font-monospace small text-dark">
                                    {{ $log->log_date ? $log->log_date->format('d M, Y') : '—' }}
                                    <div class="text-muted" style="font-size: 10px;">{{ $log->voucher_no }}</div>
                                </td>
                                <td>
                                    @if($log->entry_type === 'work')
                                        <span class="badge border px-2.5 py-1 small rounded-pill fw-bold" style="background-color: #f3e8ff; color: #7e22ce; border-color: #d8b4fe;">
                                            <i class="fa-solid fa-book-bookmark me-1"></i> Work Entry
                                        </span>
                                    @else
                                        <span class="badge border px-2.5 py-1 small rounded-pill fw-bold" style="background-color: #e0f2fe; color: #0284c7; border-color: #bae6fd;">
                                            <i class="fa-solid fa-money-bill-transfer me-1"></i> Cash Payout
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $log->book_title ?: ($log->entry_type === 'work' ? 'Book Binding Work' : 'Cash Withdrawal / Payout') }}</div>
                                    @if($log->notes)
                                        <div class="small text-muted mt-0.5"><i class="fa-solid fa-info-circle me-1 text-primary"></i>{{ $log->notes }}</div>
                                    @endif
                                </td>
                                <td class="text-center font-monospace">
                                    @if($log->entry_type === 'work' && ($log->quantity > 0 || $log->unit_rate > 0))
                                        <span class="badge bg-light text-dark border px-2 py-1 small">
                                            {{ (float)$log->quantity }} {{ $log->unit_name ?: 'Units' }} × ৳{{ number_format($log->unit_rate, 2) }}
                                        </span>
                                    @elseif($log->entry_type === 'payment')
                                        <span class="badge bg-light text-muted border px-2 py-1 small">
                                            {{ strtoupper($log->payment_method) }}
                                        </span>
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
                                    <form action="{{ route('admin.accounting.employees.work-logs.destroy', $log->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this ledger entry?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger border-0 rounded-circle p-1.5" title="Delete Entry">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-book-bookmark text-muted opacity-50 fs-2 mb-2"></i>
                                    <p class="small mb-0">No work log or payout entries found yet. Use the buttons above to add.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="table-light fw-bold">
                            <td colspan="4" class="text-end py-2.5">Total Summary:</td>
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

<!-- Modal: Add Work Entry -->
<div class="modal fade" id="addWorkModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('admin.accounting.employees.work-logs.store', $employee->id) }}" method="POST" class="modal-content rounded-4 border-0 shadow-lg">
            @csrf
            <input type="hidden" name="entry_type" value="work">
            <div class="modal-header bg-dark text-white border-0 py-3">
                <h5 class="modal-title fw-bold fs-6 d-flex align-items-center gap-2">
                    <i class="fa-solid fa-book-bookmark text-purple" style="color: #c084fc;"></i> Add Daily Work / Book Binding Entry
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label small fw-bold text-dark">Work Date *</label>
                        <input type="date" name="log_date" value="{{ date('Y-m-d') }}" class="form-control rounded-3" required>
                    </div>

                    <div class="col-12">
                        <label class="form-label small fw-bold text-dark">Book Title & Work Description *</label>
                        <input type="text" name="book_title" class="form-control rounded-3" required placeholder="e.g. 1200 copies 'Poems Collection' Binding">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-dark">Quantity (Units / Books) *</label>
                        <input type="number" step="0.01" name="quantity" id="work_modal_qty" class="form-control rounded-3 font-monospace fw-bold" placeholder="e.g. 1200" required oninput="calcWorkModalTotal()">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-dark">Rate per Book / Unit (৳) *</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">৳</span>
                            <input type="number" step="0.01" name="unit_rate" id="work_modal_rate" value="{{ (float)$employee->basic_salary ?: '' }}" class="form-control font-monospace fw-bold" placeholder="e.g. 4.50" required oninput="calcWorkModalTotal()">
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label small fw-bold text-dark">Total Earned Amount (৳) *</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light fw-bold text-purple" style="color: #7e22ce;">৳</span>
                            <input type="number" step="0.01" name="earned_amount" id="work_modal_earned" class="form-control rounded-end-3 font-monospace fw-bold fs-5 text-purple" style="color: #7e22ce;" required placeholder="0.00">
                        </div>
                        <span class="small text-muted">Auto-calculated (Quantity × Rate) or freely enter any custom amount.</span>
                    </div>

                    <div class="col-12">
                        <label class="form-label small fw-semibold text-muted">Notes / Specifications</label>
                        <textarea name="notes" class="form-control rounded-3" rows="2" placeholder="Lot number, forma count, or special remarks..."></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-top p-3">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm" style="background-color: #7e22ce; border-color: #7e22ce;">
                    <i class="fa-solid fa-check me-1"></i> Save Work Entry
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
function calcWorkModalTotal() {
    const qty = parseFloat(document.getElementById('work_modal_qty').value) || 0;
    const rate = parseFloat(document.getElementById('work_modal_rate').value) || 0;
    if (qty > 0 && rate > 0) {
        document.getElementById('work_modal_earned').value = (qty * rate).toFixed(2);
    }
}
</script>
@endpush
@endsection
