<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>বেতন স্লিপ (Salary Slip) — {{ $salary->slip_no }}</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Hind Siliguri', sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
        }
        .salary-slip-card {
            max-width: 800px;
            margin: 30px auto;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
            padding: 40px;
        }
        @media print {
            body { background: #ffffff; }
            .salary-slip-card {
                border: none;
                box-shadow: none;
                padding: 0;
                margin: 0;
                max-width: 100%;
            }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

<div class="container py-3">
    <!-- Action Bar -->
    <div class="d-flex justify-content-between align-items-center mb-3 no-print max-w-800 mx-auto" style="max-width: 800px;">
        <a href="{{ route('admin.accounting.salary.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            <i class="fa-solid fa-arrow-left me-1"></i> ফিরে যান
        </a>
        <button onclick="window.print()" class="btn btn-primary btn-sm rounded-pill px-4 fw-bold">
            <i class="fa-solid fa-print me-1"></i> বেতন স্লিপ প্রিন্ট করুন
        </button>
    </div>

    <div class="salary-slip-card">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-start border-bottom pb-4 mb-4">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    @php $logoUrl = \App\Support\SiteSetting::logoUrl(); @endphp
                    @if($logoUrl)
                        <img src="{{ $logoUrl }}" alt="Logo" style="max-height: 48px; max-width: 180px; object-fit: contain;">
                    @else
                        <h4 class="fw-bold text-primary mb-0">{{ $invoiceSettings['company_name'] ?? 'আইডিয়া প্রকাশন' }}</h4>
                    @endif
                </div>
                <p class="text-muted small mb-0">{{ $invoiceSettings['company_address'] ?? 'সেন্ট্রাল রোড, রংপুর ৫৪০০, বাংলাদেশ' }}</p>
                <p class="text-muted small mb-0">ফোন: {{ $invoiceSettings['company_phone'] ?? '01558712870' }} | ইমেইল: {{ $invoiceSettings['company_email'] ?? 'ideapbd@gmail.com' }}</p>
            </div>
            <div class="text-end">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1 fw-bold fs-6 mb-2">
                    অফিসিয়াল বেতন স্লিপ (Pay Slip)
                </span>
                <div class="fw-bold text-dark font-monospace">ভাউচার নং: {{ $salary->slip_no }}</div>
                <div class="small text-muted">তারিখ: {{ $salary->payment_date ? $salary->payment_date->format('d M, Y') : '' }}</div>
            </div>
        </div>

        <!-- Employee Info Grid -->
        <div class="row g-3 mb-4 p-3 bg-light rounded-4 border">
            <div class="col-sm-6">
                <div class="small text-muted fw-semibold">কর্মচারীর নাম:</div>
                <div class="fw-bold text-dark fs-6">{{ $salary->employee->name ?? '—' }}</div>
                <div class="small text-muted">{{ $salary->employee->designation ?? '' }}</div>
            </div>
            <div class="col-sm-3">
                <div class="small text-muted fw-semibold">বিভাগ:</div>
                <div class="fw-bold text-dark">{{ $salary->employee->department ?? 'সাধারণ' }}</div>
            </div>
            <div class="col-sm-3">
                <div class="small text-muted fw-semibold">বেতনের মাস:</div>
                <div class="fw-bold text-primary fs-6">{{ \Carbon\Carbon::createFromFormat('Y-m', $salary->salary_month)->format('F Y') }}</div>
            </div>
        </div>

        <!-- Salary Earnings & Deductions Breakdown -->
        <div class="row g-3 mb-4">
            <!-- Earnings -->
            <div class="col-sm-6">
                <div class="card h-100 border rounded-3 p-3">
                    <h6 class="fw-bold text-success border-bottom pb-2 mb-2 d-flex justify-content-between">
                        <span>আয় / পাওনা (Earnings)</span>
                        <span>টাকা (৳)</span>
                    </h6>
                    <div class="d-flex justify-content-between small py-1 text-muted">
                        <span>মূল বেতন (Basic Salary)</span>
                        <span class="fw-bold text-dark font-monospace">৳{{ number_format($salary->basic_amount, 2) }}</span>
                    </div>
                    @if($salary->bonus_amount > 0)
                        <div class="d-flex justify-content-between small py-1 text-muted">
                            <span>উৎসব ভাতা / বোনাস (Bonus)</span>
                            <span class="fw-bold text-success font-monospace">+৳{{ number_format($salary->bonus_amount, 2) }}</span>
                        </div>
                    @endif
                    @if($salary->overtime_amount > 0)
                        <div class="d-flex justify-content-between small py-1 text-muted">
                            <span>ওভারটাইম / অতিরিক্ত কাজ (Overtime)</span>
                            <span class="fw-bold text-success font-monospace">+৳{{ number_format($salary->overtime_amount, 2) }}</span>
                        </div>
                    @endif
                    <div class="d-flex justify-content-between fw-bold border-top pt-2 mt-auto text-dark">
                        <span>মোট পাওনা (Total Gross)</span>
                        <span class="font-monospace">৳{{ number_format($salary->basic_amount + $salary->bonus_amount + $salary->overtime_amount, 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Deductions -->
            <div class="col-sm-6">
                <div class="card h-100 border rounded-3 p-3">
                    <h6 class="fw-bold text-danger border-bottom pb-2 mb-2 d-flex justify-content-between">
                        <span>কর্তন / অগ্রিম (Deductions)</span>
                        <span>টাকা (৳)</span>
                    </h6>
                    <div class="d-flex justify-content-between small py-1 text-muted">
                        <span>অগ্রিম কর্তন / অনুপস্থিতি</span>
                        <span class="fw-bold text-danger font-monospace">-৳{{ number_format($salary->deduction_amount, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between fw-bold border-top pt-2 mt-auto text-dark">
                        <span>মোট কর্তন (Total Deductions)</span>
                        <span class="font-monospace text-danger">৳{{ number_format($salary->deduction_amount, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Net Paid Box -->
        <div class="p-3 bg-light rounded-4 border d-flex justify-content-between align-items-center mb-4">
            <div>
                <span class="text-muted small fw-semibold">পেমেন্ট মেথড:</span>
                <span class="badge bg-white text-dark border ms-1 px-2.5 py-1">{{ strtoupper($salary->payment_method) }}</span>
                @if($salary->trx_reference)
                    <span class="text-muted small ms-2">রেফারেন্স: <strong>{{ $salary->trx_reference }}</strong></span>
                @endif
                @if($salary->notes)
                    <div class="small text-muted mt-1"><i class="fa-solid fa-circle-info text-primary me-1"></i>{{ $salary->notes }}</div>
                @endif
            </div>
            <div class="text-end">
                <span class="small text-muted fw-bold text-uppercase d-block">সর্বমোট নিট প্রদেয় (Net Paid)</span>
                <h3 class="fw-bold text-success mb-0 font-monospace">৳{{ number_format($salary->net_paid, 2) }}</h3>
            </div>
        </div>

        <!-- Signature Zone -->
        <div class="row pt-5 mt-5 text-center small text-muted">
            <div class="col-4">
                <div class="border-top pt-2 mx-3">কর্মচারীর স্বাক্ষর</div>
            </div>
            <div class="col-4">
                <div class="border-top pt-2 mx-3">হিসাবরক্ষক</div>
            </div>
            <div class="col-4">
                <div class="border-top pt-2 mx-3">অনুমোদনকারী কর্তৃপক্ষ</div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
