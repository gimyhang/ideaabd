@extends('layouts.admin')

@section('title', 'কর্মচারী ও কারিগর বেতন-মজুরি বিতরণ (Salary & Wages) — আইডিয়া প্রকাশন')

@section('content')
<div class="container-fluid px-3 px-md-4 py-3">

    <!-- Top Action & Month Filter Header -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
        <div class="card-body p-3 p-md-4">
            <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1 small fw-bold">
                            <i class="fa-solid fa-money-check-dollar me-1"></i> পে-রোল ও মজুরি বিতরণ
                        </span>
                        <span class="text-muted small">
                            <i class="fa-solid fa-calendar me-1"></i> মাস: <strong>{{ \Carbon\Carbon::createFromFormat('Y-m', $month)->format('F Y') }}</strong>
                        </span>
                    </div>
                    <h4 class="fw-bold text-dark mb-0">কর্মচারী ও কারিগরদের বেতন-মজুরি প্রদান রেজিস্টার</h4>
                    <p class="text-muted small mb-0">মাসিক বেতন, চুক্তিভিত্তিক বই বাঁধাই বিল ও দৈনিক হাজিরা মজুরি স্বয়ংক্রিয়ভাবে মূল হিসাব খতিয়ানে যুক্ত হয়।</p>
                </div>

                <div class="d-flex align-items-center gap-2 flex-wrap ms-md-auto">
                    <a href="{{ route('admin.accounting.employees.index') }}" class="btn btn-outline-secondary rounded-pill px-3 py-2 fw-semibold shadow-2xs">
                        <i class="fa-solid fa-users me-1"></i> কর্মী ও কারিগর তালিকা
                    </a>
                    <button type="button" class="btn btn-success rounded-pill px-4 py-2 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#paySalaryModal">
                        <i class="fa-solid fa-hand-holding-dollar me-1.5"></i> নতুন বেতন / মজুরি প্রদান করুন
                    </button>
                </div>
            </div>

            <hr class="my-3 opacity-25">

            <!-- Summary KPI Badges -->
            <div class="row g-3">
                <div class="col-sm-6">
                    <div class="p-3 bg-light rounded-3 border d-flex align-items-center justify-content-between">
                        <div>
                            <span class="small text-muted fw-semibold">{{ \Carbon\Carbon::createFromFormat('Y-m', $month)->format('F Y') }} মাসে মোট পরিশোধিত মজুরি ও বেতন</span>
                            <h4 class="fw-bold text-success mb-0 font-monospace">৳{{ number_format($totalPaidInMonth, 2) }}</h4>
                        </div>
                        <span class="badge bg-white text-success border p-2.5 rounded-circle fs-5"><i class="fa-solid fa-calendar-check"></i></span>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="p-3 bg-light rounded-3 border d-flex align-items-center justify-content-between">
                        <div>
                            <span class="small text-muted fw-semibold">সর্বমোট আজ পর্যন্ত প্রদত্ত মোট পে-রোল</span>
                            <h4 class="fw-bold text-primary mb-0 font-monospace">৳{{ number_format($totalPaidAllTime, 2) }}</h4>
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
                        <option value="">সকল কর্মী ও কারিগর</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" @selected($employeeId == $emp->id)>
                                {{ $emp->name }} ({{ $emp->designation }} — {{ $emp->formatted_rate }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <div class="input-group input-group-sm">
                        <input type="text" name="search" value="{{ $search }}" class="form-control rounded-start-3" placeholder="ভাউচার নং, নাম বা কাজের বিবরণ...">
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
                            <th class="ps-3.5" style="min-width: 150px;">ভাউচার নং ও তারিখ</th>
                            <th style="min-width: 220px;">কর্মী / কারিগর ও পদবি</th>
                            <th style="min-width: 200px;">কাজের বিবরণ / কাজের ভিত্তি</th>
                            <th class="text-end" style="min-width: 120px;">মূল পরিমাণ</th>
                            <th class="text-end" style="min-width: 100px;">বোনাস/ভাতা</th>
                            <th class="text-end" style="min-width: 90px;">কর্তন</th>
                            <th class="text-end" style="min-width: 130px;">মোট প্রদেয় অর্থ</th>
                            <th class="text-center" style="width: 100px;">মাধ্যম</th>
                            <th class="text-end pe-3.5" style="width: 120px;">পে-স্লিপ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $pay)
                            @php
                                $empType = $pay->employment_type ?? ($pay->employee->employment_type ?? 'monthly');
                            @endphp
                            <tr>
                                <td class="ps-3.5">
                                    <div class="fw-bold text-dark font-monospace small">{{ $pay->slip_no }}</div>
                                    <span class="small text-muted">{{ $pay->payment_date ? $pay->payment_date->format('d M, Y') : '' }}</span>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $pay->employee->name ?? '—' }}</div>
                                    <div class="d-flex align-items-center gap-1.5 mt-0.5">
                                        <span class="small text-muted">{{ $pay->employee->designation ?? '' }}</span>
                                        @if($empType === 'contract_piece')
                                            <span class="badge border px-1.5 py-0" style="font-size: 9px; background-color: #f3e8ff; color: #7e22ce; border-color: #d8b4fe;">পিস-রেট বাইন্ডার</span>
                                        @elseif($empType === 'daily')
                                            <span class="badge border px-1.5 py-0" style="font-size: 9px; background-color: #fef3c7; color: #b45309; border-color: #fde68a;">দৈনিক হাজিরা</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @if($pay->work_details)
                                        <div class="small fw-semibold text-dark">{{ $pay->work_details }}</div>
                                    @endif
                                    @if($pay->job_quantity && $pay->rate_per_unit)
                                        <div class="text-muted" style="font-size: 11px;">
                                            {{ (float)$pay->job_quantity }} {{ $pay->rate_unit_name ?: 'একক' }} × ৳{{ number_format($pay->rate_per_unit, 2) }}
                                        </div>
                                    @else
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2 py-0.5" style="font-size: 10px;">
                                            {{ \Carbon\Carbon::createFromFormat('Y-m', $pay->salary_month)->format('M Y') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="text-end fw-semibold text-muted font-monospace">৳{{ number_format($pay->basic_amount, 2) }}</td>
                                <td class="text-end fw-semibold text-success font-monospace">
                                    @if(($pay->bonus_amount + $pay->overtime_amount) > 0)
                                        +৳{{ number_format($pay->bonus_amount + $pay->overtime_amount, 2) }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="text-end fw-semibold text-danger font-monospace">
                                    @if($pay->deduction_amount > 0)
                                        -৳{{ number_format($pay->deduction_amount, 2) }}
                                    @else
                                        —
                                    @endif
                                </td>
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
                                        <span>ভাউচার</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-hand-holding-dollar text-muted opacity-50 fs-2 mb-2"></i>
                                    <p class="small mb-0">এই মাসে কোনো বেতন বা মজুরি প্রদানের রেকর্ড নেই। নতুন বেতন বিতরণ করতে উপরের বাটনে চাপুন।</p>
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

<!-- Modal: Pay Salary / Wages -->
<div class="modal fade" id="paySalaryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form action="{{ route('admin.accounting.salary.store') }}" method="POST" class="modal-content rounded-4 border-0 shadow-lg">
            @csrf
            <div class="modal-header bg-dark text-white border-0 py-3">
                <h5 class="modal-title fw-bold fs-6 d-flex align-items-center gap-2">
                    <i class="fa-solid fa-hand-holding-dollar text-success"></i> কর্মচারী ও কারিগর বেতন / মজুরি ভাউচার
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-dark">কর্মী / কারিগর নির্বাচন করুন *</label>
                        <select name="employee_id" id="modal_employee_id" class="form-select rounded-3 fw-semibold border-primary" required onchange="onEmployeeSelect(this)">
                            <option value="">-- কর্মী / কারিগর নির্বাচন করুন --</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}" 
                                        data-type="{{ $emp->employment_type ?? 'monthly' }}"
                                        data-rate-type="{{ $emp->salary_rate_type ?? 'monthly' }}"
                                        data-salary="{{ $emp->basic_salary }}"
                                        data-unit="{{ $emp->rate_unit_name ?: 'একক' }}">
                                    {{ $emp->name }} ({{ $emp->designation }} — {{ $emp->formatted_rate }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-dark">কাজের মাস / বিল মাস *</label>
                        <input type="month" name="salary_month" value="{{ $month }}" class="form-control rounded-3" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-dark">প্রদানের তারিখ *</label>
                        <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" class="form-control rounded-3" required>
                    </div>

                    <!-- Dynamic Job / Piece-rate / Daily Work Calculation Section -->
                    <div class="col-12" id="pieceRateWorkBox" style="display: none;">
                        <div class="p-3 rounded-4 border" style="background-color: #faf5ff; border-color: #d8b4fe !important;">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="badge" style="background-color: #7e22ce; color: white;">
                                    <i class="fa-solid fa-book-bookmark me-1"></i> প্রেস কাজ ও পিস-রেট হিসাব
                                </span>
                                <span class="small text-muted" id="pieceRateBoxTitle">বইয়ের নাম ও কাজের পরিমাণ লিখুন</span>
                            </div>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-dark">কাজের বিবরণ / বইয়ের নাম</label>
                                    <input type="text" name="work_details" id="modal_work_details" class="form-control rounded-3" placeholder="যেমন: ৫০০০ কপি 'বাংলা গল্প' বই বাঁধাই">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold text-dark" id="modal_qty_label">কাজের পরিমাণ (Qty)</label>
                                    <input type="number" step="0.01" name="job_quantity" id="modal_job_qty" class="form-control rounded-3 font-monospace" placeholder="যেমন: 5000" oninput="calcPieceRateSubtotal()">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold text-dark" id="modal_rate_label">একক দর (৳ Rate)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white">৳</span>
                                        <input type="number" step="0.01" name="rate_per_unit" id="modal_rate_per_unit" class="form-control font-monospace" placeholder="যেমন: 4.50" oninput="calcPieceRateSubtotal()">
                                    </div>
                                </div>
                                <input type="hidden" name="rate_unit_name" id="modal_rate_unit_name" value="">
                            </div>
                        </div>
                    </div>

                    <!-- Salary Breakdown Calculation Box -->
                    <div class="col-12">
                        <div class="p-3 bg-light rounded-4 border">
                            <div class="row g-2 align-items-center">
                                <div class="col-sm-3">
                                    <label class="form-label small fw-semibold text-muted mb-1" id="lblBasicSalaryTitle">মূল বেতন / মজুরি (৳)</label>
                                    <input type="number" step="0.01" name="basic_amount" id="modal_basic_salary" class="form-control rounded-3 font-monospace fw-bold" required oninput="calcNetSalary()">
                                </div>
                                <div class="col-sm-3">
                                    <label class="form-label small fw-semibold text-success mb-1">+ বোনাস / ওভারটাইম (৳)</label>
                                    <input type="number" step="0.01" name="bonus_amount" id="modal_bonus" value="0" class="form-control rounded-3 font-monospace" oninput="calcNetSalary()">
                                </div>
                                <div class="col-sm-3">
                                    <label class="form-label small fw-semibold text-danger mb-1">- কর্তন / অগ্রিম সমন্বয় (৳)</label>
                                    <input type="number" step="0.01" name="deduction_amount" id="modal_deduction" value="0" class="form-control rounded-3 font-monospace" oninput="calcNetSalary()">
                                </div>
                                <div class="col-sm-3">
                                    <label class="form-label small fw-bold text-dark mb-1">= নিট প্রদেয় (Net ৳)</label>
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
                        <label class="form-label small fw-semibold text-muted">বিশেষ নোট / চালান বিবরণ</label>
                        <textarea name="notes" class="form-control rounded-3" rows="2" placeholder="যেমন: 'বাংলা একাডেমি বইমেলার ৫০০০ বই বাঁধাই বিল' অথবা বিশেষ অগ্রিম সমন্বয়..."></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-top p-3">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">বাতিল</button>
                <button type="submit" class="btn btn-success rounded-pill px-5 fw-bold shadow-sm">
                    <i class="fa-solid fa-check me-1.5"></i> মজুরি / বেতন প্রদান সম্পন্ন করুন
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function onEmployeeSelect(sel) {
    const opt = sel.options[sel.selectedIndex];
    if (!opt || !opt.value) {
        document.getElementById('pieceRateWorkBox').style.display = 'none';
        document.getElementById('modal_basic_salary').value = '';
        calcNetSalary();
        return;
    }

    const type = opt.getAttribute('data-type') || 'monthly';
    const rateType = opt.getAttribute('data-rate-type') || 'monthly';
    const salary = parseFloat(opt.getAttribute('data-salary')) || 0;
    const unit = opt.getAttribute('data-unit') || '';

    const workBox = document.getElementById('pieceRateWorkBox');
    const basicInput = document.getElementById('modal_basic_salary');
    const ratePerUnitInput = document.getElementById('modal_rate_per_unit');
    const jobQtyInput = document.getElementById('modal_job_qty');
    const unitHidden = document.getElementById('modal_rate_unit_name');
    const qtyLabel = document.getElementById('modal_qty_label');
    const rateLabel = document.getElementById('modal_rate_label');
    const boxTitle = document.getElementById('pieceRateBoxTitle');
    const basicTitle = document.getElementById('lblBasicSalaryTitle');

    unitHidden.value = unit;

    if (type === 'contract_piece' || rateType === 'per_book' || rateType === 'per_forma' || rateType === 'per_thousand' || rateType === 'per_page') {
        workBox.style.display = 'block';
        ratePerUnitInput.value = salary.toFixed(2);
        jobQtyInput.value = '';
        basicInput.value = '0.00';
        boxTitle.textContent = `কাজের পরিমাণ × দর (${salary} / ${unit})`;
        qtyLabel.textContent = `মোট সংখ্যা (${unit})`;
        rateLabel.textContent = `একক দর (৳ / ${unit})`;
        basicTitle.textContent = 'কাজের মোট মজুরি (৳)';
    } else if (type === 'daily' || rateType === 'daily') {
        workBox.style.display = 'block';
        ratePerUnitInput.value = salary.toFixed(2);
        jobQtyInput.value = '';
        basicInput.value = '0.00';
        boxTitle.textContent = `হাজিরা দিন × দৈনিক মজুরি (${salary} / দিন)`;
        qtyLabel.textContent = 'মোট কার্যদিবস (দিন)';
        rateLabel.textContent = 'দৈনিক দর (৳ / দিন)';
        basicTitle.textContent = 'হাজিরা বাবদ মজুরি (৳)';
    } else if (type === 'weekly' || rateType === 'weekly') {
        workBox.style.display = 'block';
        ratePerUnitInput.value = salary.toFixed(2);
        jobQtyInput.value = '1';
        basicInput.value = salary.toFixed(2);
        boxTitle.textContent = `সাপ্তাহিক মজুরি (${salary} / সপ্তাহ)`;
        qtyLabel.textContent = 'সপ্তাহ সংখ্যা';
        rateLabel.textContent = 'সাপ্তাহিক দর (৳)';
        basicTitle.textContent = 'মোট সাপ্তাহিক মজুরি (৳)';
    } else {
        workBox.style.display = 'none';
        ratePerUnitInput.value = '';
        jobQtyInput.value = '';
        basicInput.value = salary.toFixed(2);
        basicTitle.textContent = 'মূল মাসিক বেতন (৳)';
    }

    calcNetSalary();
}

function calcPieceRateSubtotal() {
    const qty = parseFloat(document.getElementById('modal_job_qty').value) || 0;
    const rate = parseFloat(document.getElementById('modal_rate_per_unit').value) || 0;
    if (qty > 0 && rate > 0) {
        document.getElementById('modal_basic_salary').value = (qty * rate).toFixed(2);
    }
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
