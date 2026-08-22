<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payout Voucher #{{ $payout->id }} — আইডিয়া প্রকাশন</title>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;600;700&family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        body { font-family: 'Hind Siliguri', 'Inter', sans-serif; background-color: #f8fafc; color: #1e293b; }
        .voucher-card { max-width: 700px; margin: 40px auto; background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; padding: 40px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); }
        @media print {
            body { background: #fff; }
            .voucher-card { box-shadow: none; border: none; padding: 0; margin: 0; max-width: 100%; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

    <div class="no-print text-center pt-4">
        <button onclick="window.print()" class="btn btn-primary rounded-pill px-4 fw-bold shadow-xs">
            রসিদ প্রিন্ট করুন (Print Receipt)
        </button>
    </div>

    <div class="voucher-card">
        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-start border-bottom pb-4 mb-4">
            <div>
                <h4 class="fw-bold mb-1 text-primary">আইডিয়া প্রকাশন (IDEA Publication)</h4>
                <p class="text-muted small mb-0">লেখক রয়্যালটি পরিশোধের অফিশিয়াল ভাউচার ও রসিদ</p>
            </div>
            <div class="text-end">
                <span class="badge bg-success fs-6 px-3 py-1.5 rounded-pill mb-1">পেমেন্ট সফল (Paid)</span>
                <div class="small text-muted font-monospace">ভাউচার নম্বর: #PAY-{{ str_pad($payout->id, 6, '0', STR_PAD_LEFT) }}</div>
                <div class="small text-muted font-monospace">তারিখ: {{ $payout->processed_at ? $payout->processed_at->format('d M, Y h:i A') : $payout->created_at->format('d M, Y') }}</div>
            </div>
        </div>

        {{-- Author & Payment Meta --}}
        <div class="row g-3 mb-4">
            <div class="col-6">
                <small class="text-muted text-uppercase fw-bold" style="font-size: 11px;">লেখক বিবরণ (Payee)</small>
                <h6 class="fw-bold mb-0 text-dark">{{ $payout->author?->name ?? $payout->user?->name }}</h6>
                <div class="small text-muted font-monospace">{{ $payout->user?->phone ?: $payout->user?->email }}</div>
            </div>
            <div class="col-6 text-end">
                <small class="text-muted text-uppercase fw-bold" style="font-size: 11px;">পেমেন্ট মেথড ও একাউন্ট</small>
                <div class="fw-bold text-uppercase text-dark">{{ $payout->payment_method }}</div>
                <div class="small text-muted font-monospace">{{ $payout->account_details }}</div>
            </div>
        </div>

        {{-- Amount Table --}}
        <table class="table table-bordered align-middle mb-4">
            <thead class="table-light small fw-bold">
                <tr>
                    <th>বিবরণ (Description)</th>
                    <th class="text-end" style="width: 160px;">পরিমাণ (৳)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <strong>লেখক সেলফ-পাবলিশিং ই-বুক রয়্যালটি উইথড্রয়াল (৫০% শেয়ার মডেল)</strong>
                        <small class="d-block text-muted">রিকোয়েস্ট আইডি: #{{ $payout->id }}</small>
                    </td>
                    <td class="text-end font-monospace fs-6">৳{{ number_format($payout->amount, 2) }}</td>
                </tr>
                @if($payout->tax_deduction_amount > 0)
                    <tr>
                        <td class="text-danger">ট্যাক্স / TDS কর্তন (Tax Withholding)</td>
                        <td class="text-end font-monospace text-danger">-৳{{ number_format($payout->tax_deduction_amount, 2) }}</td>
                    </tr>
                @endif
                <tr class="table-light">
                    <td class="fw-bold text-dark fs-6">মোট প্রদেয় অর্থ (Net Paid Amount)</td>
                    <td class="text-end font-monospace fw-bold text-success fs-5">৳{{ number_format($payout->net_payable_amount, 2) }}</td>
                </tr>
            </tbody>
        </table>

        {{-- Trx & Audit Details --}}
        <div class="p-3 bg-light rounded-3 border mb-4 small font-monospace">
            <div><strong>Transaction Reference / TrxID:</strong> {{ $payout->transaction_ref ?: 'N/A' }}</div>
            @if($payout->admin_notes)
                <div class="mt-1"><strong>নোট:</strong> {{ $payout->admin_notes }}</div>
            @endif
        </div>

        {{-- Signatures --}}
        <div class="row pt-5 mt-4 text-center">
            <div class="col-6">
                <div class="border-top pt-2 small text-muted">লেখকের স্বাক্ষর</div>
            </div>
            <div class="col-6">
                <div class="border-top pt-2 small text-muted">হিসাবরক্ষণ ও অনুমোদক কর্মকর্তা</div>
            </div>
        </div>
    </div>

</body>
</html>
