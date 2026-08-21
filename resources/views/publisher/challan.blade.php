<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Challan #{{ $purchase->purchase_no ?? $purchase->id }} — {{ $publisher->name ?? 'Publisher' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', 'Hind Siliguri', sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
        }
        .challan-container {
            max-width: 920px;
            margin: 30px auto;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
            padding: 40px;
        }
        .challan-header {
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 20px;
            margin-bottom: 25px;
        }
        .challan-badge {
            background: #0f172a;
            color: #ffffff;
            padding: 6px 16px;
            font-weight: 700;
            letter-spacing: 1px;
            border-radius: 6px;
            font-size: 14px;
            display: inline-block;
        }
        .info-box {
            background-color: #f8fafc;
            border-radius: 8px;
            padding: 14px 18px;
            border: 1px solid #e2e8f0;
        }
        .table-challan th {
            background-color: #f1f5f9;
            color: #334155;
            font-weight: 700;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 12px 14px;
            border: 1px solid #e2e8f0;
        }
        .table-challan td {
            padding: 12px 14px;
            vertical-align: middle;
            border: 1px solid #e2e8f0;
            font-size: 14px;
        }
        .signature-line {
            border-top: 1px dashed #94a3b8;
            padding-top: 8px;
            text-align: center;
            font-weight: 600;
            font-size: 13px;
            color: #475569;
        }
        @media print {
            body {
                background: #ffffff !important;
                color: #000000 !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .challan-container {
                box-shadow: none !important;
                border: none !important;
                padding: 0 !important;
                max-width: 100% !important;
                margin: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .table-challan th {
                background-color: #f1f5f9 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>

<div class="container my-3 no-print">
    <div class="d-flex align-items-center justify-content-between">
        <a href="{{ route('publisher.dashboard', ['tab' => 'today-purchases']) }}" class="btn btn-outline-secondary rounded-pill px-3">
            <i class="fas fa-arrow-left me-1.5"></i> Back to Company Panel
        </a>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
                <i class="fas fa-print me-1.5"></i> Print Challan
            </button>
            <button onclick="window.close()" class="btn btn-light border rounded-pill px-3">
                <i class="fas fa-times me-1"></i> Close
            </button>
        </div>
    </div>
</div>

<div class="challan-container">
    {{-- Header --}}
    <div class="challan-header d-flex flex-wrap align-items-start justify-content-between gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <h3 class="fw-bold mb-0 text-dark">{{ config('app.name', 'Idea Publication') }}</h3>
            </div>
            <p class="text-muted small mb-0">
                Central Inventory & Distribution Department<br>
                Email: support@ideaabd.com | Web: www.ideaabd.com
            </p>
        </div>
        <div class="text-md-end">
            <div class="challan-badge mb-2">DELIVERY CHALLAN & PURCHASE INVOICE</div>
            <div class="fw-bold text-dark fs-6">Challan #: <span class="text-primary">{{ $purchase->purchase_no ?? ('CHL-' . str_pad($purchase->id, 6, '0', STR_PAD_LEFT)) }}</span></div>
            <div class="small text-muted">Date: <strong>{{ $purchase->purchase_date ? $purchase->purchase_date->format('d M, Y') : now()->format('d M, Y') }}</strong></div>
        </div>
    </div>

    {{-- Meta Details --}}
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="info-box h-100">
                <div class="small text-uppercase text-muted fw-bold mb-1">Publisher / Supplier:</div>
                <h5 class="fw-bold text-dark mb-1">{{ $publisher->name }}</h5>
                @if($publisher->phone)
                    <div class="small text-secondary"><i class="fas fa-phone me-1.5 text-muted"></i> {{ $publisher->phone }}</div>
                @endif
                @if($publisher->email)
                    <div class="small text-secondary"><i class="fas fa-envelope me-1.5 text-muted"></i> {{ $publisher->email }}</div>
                @endif
                @if($publisher->address)
                    <div class="small text-secondary"><i class="fas fa-location-dot me-1.5 text-muted"></i> {{ $publisher->address }}</div>
                @endif
            </div>
        </div>
        <div class="col-md-6">
            <div class="info-box h-100">
                <div class="small text-uppercase text-muted fw-bold mb-1">Order Information:</div>
                <div class="d-flex justify-content-between mb-1">
                    <span class="small text-muted">Publisher Memo / Ref:</span>
                    <span class="small fw-semibold text-dark">{{ $purchase->publisher_memo_no ?: 'N/A' }}</span>
                </div>
                <div class="d-flex justify-content-between mb-1">
                    <span class="small text-muted">Payment Type:</span>
                    <span class="small fw-semibold text-dark text-capitalize">{{ str_replace('_', ' ', $purchase->payment_type ?: 'Credit') }}</span>
                </div>
                <div class="d-flex justify-content-between mb-1">
                    <span class="small text-muted">Payment Status:</span>
                    <span class="badge {{ $purchase->payment_status === 'paid' ? 'bg-success' : ($purchase->payment_status === 'partial' ? 'bg-warning text-dark' : 'bg-danger') }} small">
                        {{ strtoupper($purchase->payment_status ?: 'DUE') }}
                    </span>
                </div>
                @if($purchase->creator)
                    <div class="d-flex justify-content-between">
                        <span class="small text-muted">Created By:</span>
                        <span class="small fw-semibold text-dark">{{ $purchase->creator->name }}</span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Items Table --}}
    <div class="table-responsive mb-4">
        <table class="table table-challan w-100">
            <thead>
                <tr>
                    <th class="text-center" style="width: 45px;">#</th>
                    <th>Book Title & Details</th>
                    <th class="text-center" style="width: 100px;">Qty</th>
                    <th class="text-end" style="width: 120px;">MRP Rate</th>
                    <th class="text-end" style="width: 120px;">Cost Rate</th>
                    <th class="text-end" style="width: 130px;">Total (৳)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($purchase->items as $idx => $item)
                    <tr>
                        <td class="text-center fw-bold text-muted">{{ $idx + 1 }}</td>
                        <td>
                            <div class="fw-bold text-dark">{{ $item->book_title ?? ($item->book->title ?? 'Book Item') }}</div>
                            <div class="small text-muted">
                                @if($item->author_name || ($item->book && $item->book->author_name))
                                    <span>Author: {{ $item->author_name ?: $item->book->author_name }}</span>
                                @endif
                                @if($item->book && $item->book->sku)
                                    <span class="ms-2">| SKU: {{ $item->book->sku }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="text-center fw-bold fs-6">{{ $item->quantity }}</td>
                        <td class="text-end text-muted">৳{{ number_format($item->mrp_price, 2) }}</td>
                        <td class="text-end text-dark font-monospace">৳{{ number_format($item->unit_cost_price, 2) }}</td>
                        <td class="text-end fw-bold text-dark font-monospace">৳{{ number_format($item->subtotal ?: ($item->quantity * $item->unit_cost_price), 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">No items recorded in this purchase order.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Financial Summary --}}
    <div class="row justify-content-end mb-5">
        <div class="col-md-5">
            <div class="info-box bg-light border-0">
                <div class="d-flex justify-content-between mb-1.5">
                    <span class="text-muted small">Subtotal:</span>
                    <span class="fw-semibold text-dark">৳{{ number_format($purchase->total_amount, 2) }}</span>
                </div>
                @if($purchase->discount_amount > 0)
                    <div class="d-flex justify-content-between mb-1.5 text-danger">
                        <span class="small">Discount:</span>
                        <span class="fw-semibold">-৳{{ number_format($purchase->discount_amount, 2) }}</span>
                    </div>
                @endif
                <div class="d-flex justify-content-between border-top pt-2 mb-2">
                    <span class="fw-bold text-dark">Grand Total:</span>
                    <span class="fw-bold text-dark fs-5">৳{{ number_format($purchase->grand_total ?: $purchase->total_amount, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between mb-1 text-success">
                    <span class="small">Paid Amount:</span>
                    <span class="fw-semibold">৳{{ number_format($purchase->paid_amount, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between text-danger">
                    <span class="small fw-bold">Due Balance:</span>
                    <span class="fw-bold">৳{{ number_format($purchase->due_amount ?: max(0, $purchase->grand_total - $purchase->paid_amount), 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    @if($purchase->notes)
        <div class="alert alert-light border small text-muted mb-5">
            <strong>Notes / Instructions:</strong> {{ $purchase->notes }}
        </div>
    @endif

    {{-- Signatures --}}
    <div class="row pt-5 mt-5">
        <div class="col-4">
            <div class="signature-line">
                Prepared By
            </div>
        </div>
        <div class="col-4">
            <div class="signature-line">
                Store In-Charge / Checked
            </div>
        </div>
        <div class="col-4">
            <div class="signature-line">
                Publisher / Authorized Signatory
            </div>
        </div>
    </div>
</div>

</body>
</html>
