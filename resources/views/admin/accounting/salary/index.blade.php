@extends('layouts.admin')

@section('title', 'কর্মচারী বেতন বিতরণ ও পে-রোল (Staff Salary Disbursements) — আইডিয়া প্রকাশন')

@section('content')
<div class="container-fluid px-3 px-md-4 py-3">

    <!-- Top Action & Month Filter Header -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
        <div class="card-body p-3 p-md-4">
            <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1 small fw-bold">
                            <i class="fa-solid fa-money-check-dollar me-1"></i> পে-রোল ও বেতন বিতরণ
                        </span>
                        <span class="text-muted small">
                            <i class="fa-solid fa-calendar me-1"></i> মাস: <strong>{{ \Carbon\Carbon::createFromFormat('Y-m', $month)->format('F Y') }}</strong>
                        </span>
                    </div>
                    <h4 class="fw-bold text-dark mb-0">কর্মচারীদের বেতন বিতরণ ও রেজিস্টার</h4>
                    <p class="text-muted small mb-0">মাসিক বেতন প্রদান, বোনাস, ওভারটাইম ও কর্তন হিসাব স্বয়ংক্রিয়ভাবে মূল খতিয়ানে যুক্ত হয়।</p>
                </div>

                <div class="d-flex align-items-center gap-2 flex-wrap ms-md-auto">
                    <a href="{{ route('admin.accounting.employees.index') }}" class="btn btn-outline-secondary rounded-pill px-3 py-2 fw-semibold shadow-2xs">
                        <i class="fa-solid fa-users me-1"></i> কর্মচারী তালিকা
                    </a>
                    <button type="button" class="btn btn-success rounded-pill px-3.5 py-2 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#paySalaryModal">
                        <i class="fa-solid fa-hand-holding-dollar me-1.5"></i> নতুন বেতন প্রদান করুন
                    </button>
                </div>
            </div>

            <hr class="my-3 opacity-25">

            <!-- Summary KPI Badges -->
            <div class="row g-3">
                <div class="col-sm-6">
                    <div class="p-3 bg-light rounded-3 border d-flex align-items-center justify-content-between">
                        <div>
                            <span class="small text-muted fw-semibold">{{ \Carbon\Carbon::createFromFormat('Y-m', $month)->format('F Y') }} মাসে মোট পরিশোধিত বেতন</span>
                            <h4 class="fw-bold text-success mb-0">৳{{ number_format($totalPaidInMonth, 2) }}</h4>
                        </div>
                        <span class="badge bg-white text-success border p-2.5 rounded-circle fs-5"><i class="fa-solid fa-calendar-check"></i></span>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="p-3 bg-light rounded-3 border d-flex align-items-center justify-content-between">
                        <div>
                            <span class="small text-muted fw-semibold">সর্বমোট আজ পর্যন্ত প্রদত্ত বেতন</span>
                            <h4 class="fw-bold text-primary mb-0">৳{{ number_format($totalPaidAllTime, 2) }}</h4>
                        </div>
                        <span class="badge bg-white text-primary border p-2.5 rounded-circle fs-5"><i class="fa-solid fa-vault"></i></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Month & Search Filter -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
        <div class="card-body p-3">
            <form action="{{ route('admin.accounting.salary.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-calendar-days text-muted"></i></span>
                        <input type="month" name="month" value="{{ $month }}" class="form-control rounded-end-3" onchange="this.form.submit()">
                    </div>
                </div>
                <div class="col-md-4">
                    <select name="employee_id" class="form-select form-select-sm rounded-3" onchange="this.form.submit()">
                        <option value="">সকল কর্মচারী</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" @selected($employeeId == $emp->id)>{{ $emp->name }} ({{ $emp->designation }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <div class="input-group input-group-sm">
                        <input type="text" name="search" value="{{ $search }}" class="form-control rounded-start-3" placeholder="রিসিট নং বা নাম...">
                        <button type="submit" class="btn btn-primary fw-semibold"><i class="fa-solid fa-search"></i></button>
                    </div>
                </div>
                <div class="col-md-1">
                    @if($search || $employeeId)
                        <a href="{{ route('admin.accounting.salary.index', ['month' => $month]) }}" class="btn btn-sm btn-outline-secondary rounded-pill w-100" title="রিসেট"><i class="fa-solid fa-xmark"></i></a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Salary Payments Table -->
    <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light table-light small text-muted">
                        <tr>
                            <th class="ps-3.5">রিসিট নং ও তারিখ</th>
                            <th>কর্মচারীর নাম ও পদবি</th>
                            <th>বেতনের মাস</th>
                            <th class="text-end">মূল বেতন</th>
                            <th class="text-end">বোনাস / ভাতা</th>
                            <th class="text-end">কর্তন</th>
                            <th class="text-end">মোট প্রদেয় অর্থ</th>
                            <th class="text-center">মাধ্যম</th>
                            <th class="text-end pe-3.5" style="width: 130px;">পে-স্লিপ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $pay)
                            <tr>
                                <td class="ps-3.5">
                                    <div class="fw-bold text-dark font-monospace small">{{ $pay->slip_no }}</div>
                                    <span class="small text-muted">{{ $pay->payment_date ? $pay->payment_date->format('d M, Y') : '' }}</span>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $pay->employee->name ?? '—' }}</div>
                                    <span class="small text-muted">{{ $pay->employee->designation ?? '' }} ({{ $pay->employee->department ?? '' }})</span>
                                </td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1 small fw-semibold">
                                        {{ \Carbon\Carbon::createFromFormat('Y-m', $pay->salary_month)->format('M Y') }}
                                    </span>
                                </td>
                                <td class="text-end fw-semibold text-muted font-monospace">৳{{ number_format($pay->basic_amount, 2) }}</td>
                                <td class="text-end fw-semibold text-success font-monospace">+৳{{ number_format($pay->bonus_amount + $pay->overtime_amount, 2) }}</td>
                                <td class="text-end fw-semibold text-danger font-monospace">-৳{{ number_format($pay->deduction_amount, 2) }}</td>
                                <td class="text-end fw-bold text-dark font-monospace fs-6">
                                    ৳{{ number_format($pay->net_paid, 2) }}
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-muted border px-2 py-0.5 small">
                                        {{ strtoupper($pay->payment_method) }}
                                    </span>
                                </td>
                                <td class="text-end pe-3.5">
                                    <a href="{{ route('admin.accounting.salary.slip', $pay->id) }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 fw-semibold d-inline-flex align-items-center gap-1">
                                        <i class="fa-solid fa-receipt"></i>
                                        <span>পে-স্লিপ</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-hand-holding-dollar text-muted opacity-50 fs-2 mb-2"></i>
                                    <p class="small mb-0">এই মাসে কোনো বেতন প্রদানের রেকর্ড নেই। নতুন বেতন বিতরণ করতে উপরের বাটনে চাপুন।</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($payments->hasPages())
                <div class="p-3 border-top d-flex justify-content-center">
                    {{ $payments->links() }}
                </div>
            @endif
        </div>
    </div>

</div>

<!-- Modal: Pay Salary -->
<div class="modal fade" id="paySalaryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form action="{{ route('admin.accounting.salary.store') }}" method="POST" class="modal-content rounded-4 border-0 shadow-lg">
            @csrf
            <div class="modal-header bg-dark text-white border-0 py-3">
                <h5 class="modal-title fw-bold fs-6 d-flex align-items-center gap-2">
                    <i class="fa-solid fa-hand-holding-dollar text-success"></i> কর্মচারী বেতন বিতরণ ও ভাউচার
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-dark">কর্মচারী নির্বাচন করুন *</label>
                        <select name="employee_id" id="modal_employee_id" class="form-select rounded-3" required onchange="onEmployeeSelect(this)">
                            <option value="">কর্মচারী নির্বাচন করুন...</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}" data-salary="{{ $emp->basic_salary }}">
                                    {{ $emp->name }} ({{ $emp->designation }} — মূল বেতন: ৳{{ number_format($emp->basic_salary, 0) }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-dark">বেতনের মাস *</label>
                        <input type="month" name="salary_month" value="{{ $month }}" class="form-control rounded-3" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-dark">প্রদানের তারিখ *</label>
                        <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" class="form-control rounded-3" required>
                    </div>

                    <!-- Salary Breakdown Calculation Box -->
                    <div class="col-12">
                        <div class="p-3 bg-light rounded-4 border">
                            <div class="row g-2 align-items-center">
                                <div class="col-sm-3">
                                    <label class="form-label small fw-semibold text-muted mb-1">মূল বেতন (৳)</label>
                                    <input type="number" step="0.01" name="basic_amount" id="modal_basic_salary" class="form-control rounded-3" required oninput="calcNetSalary()">
                                </div>
                                <div class="col-sm-3">
                                    <label class="form-label small fw-semibold text-success mb-1">+ বোনাস / ভাতা (৳)</label>
                                    <input type="number" step="0.01" name="bonus_amount" id="modal_bonus" value="0" class="form-control rounded-3" oninput="calcNetSalary()">
                                </div>
                                <div class="col-sm-3">
                                    <label class="form-label small fw-semibold text-danger mb-1">- কর্তন / অগ্রিম (৳)</label>
                                    <input type="number" step="0.01" name="deduction_amount" id="modal_deduction" value="0" class="form-control rounded-3" oninput="calcNetSalary()">
                                </div>
                                <div class="col-sm-3">
                                    <label class="form-label small fw-bold text-dark mb-1">= মোট প্রদেয় বেতন (৳)</label>
                                    <div class="p-2 bg-white rounded-3 border fw-bold text-success fs-5 text-end font-monospace" id="lblNetSalary">
                                        ৳0.00
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-dark">পেমেন্ট মেথড *</label>
                        <select name="payment_method" class="form-select rounded-3" required>
                            <option value="cash">ক্যাশ / নগদ (Cash)</option>
                            <option value="bkash">বিকাশ (bKash)</option>
                            <option value="nagad">নগদ (Nagad)</option>
                            <option value="rocket">রকেট (Rocket)</option>
                            <option value="bank">ব্যাংক ট্রান্সফার (Bank Transfer)</option>
                            <option value="cheque">চেক (Cheque)</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-muted">ট্রানজেকশন / চেক রেফারেন্স</label>
                        <input type="text" name="trx_reference" class="form-control rounded-3" placeholder="TrxID বা চেক নম্বর...">
                    </div>

                    <div class="col-12">
                        <label class="form-label small fw-semibold text-muted">বিশেষ নোট / বিবরণ</label>
                        <textarea name="notes" class="form-control rounded-3" rows="2" placeholder="যেমন: ঈদ বোনাস সহ অগ্রিম সমন্বয়..."></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-top p-3">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">বাতিল</button>
                <button type="submit" class="btn btn-success rounded-pill px-5 fw-bold shadow-sm">
                    <i class="fa-solid fa-check me-1.5"></i> বেতন প্রদান ও খতিয়ানে সংরক্ষণ
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function onEmployeeSelect(sel) {
    const opt = sel.options[sel.selectedIndex];
    const salary = opt.getAttribute('data-salary') || 0;
    document.getElementById('modal_basic_salary').value = parseFloat(salary).toFixed(2);
    calcNetSalary();
}

function calcNetSalary() {
    const basic = parseFloat(document.getElementById('modal_basic_salary').value) || 0;
    const bonus = parseFloat(document.getElementById('modal_bonus').value) || 0;
    const ded = parseFloat(document.getElementById('modal_deduction').value) || 0;

    const net = Math.max(0, basic + bonus - ded);
    document.getElementById('lblNetSalary').textContent = '৳' + net.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
</script>
@endpush
@endsection
