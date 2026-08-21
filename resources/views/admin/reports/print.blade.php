<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales & Visitor Analytics Report — Idea Prokashon</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
            padding: 20px;
        }
        .report-container {
            max-width: 900px;
            margin: 0 auto;
            background: #fff;
            padding: 35px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        .header-logo {
            font-weight: 800;
            font-size: 24px;
            color: #0066cc;
        }
        .stat-card {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px 16px;
            background: #f8fafc;
        }
        .table th {
            background-color: #f1f5f9;
            font-size: 13px;
            text-transform: uppercase;
        }
        @media print {
            body {
                background: #fff;
                padding: 0;
            }
            .report-container {
                box-shadow: none;
                padding: 0;
                max-width: 100%;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>

<div class="report-container">
    
    <!-- Action Bar (hidden in print) -->
    <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-bottom no-print">
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
        </a>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-primary btn-sm rounded-pill px-3 fw-bold shadow-xs">
                <i class="fas fa-print me-1.5"></i> Print / Save PDF
            </button>
        </div>
    </div>

    <!-- Letterhead -->
    <div class="row align-items-center mb-4 pb-3 border-bottom">
        <div class="col-7">
            <div class="header-logo mb-1">IDEA PROKASHON</div>
            <div class="text-muted small">Smart E-Commerce & Publishing Platform</div>
            <div class="text-muted small">Website: ideaabd.com | Phone: 01558712810</div>
        </div>
        <div class="col-5 text-end">
            <h5 class="fw-bold text-dark mb-1">Executive Performance Report</h5>
            <div class="badge bg-primary text-white p-1.5 mb-1 rounded-pill">{{ $stats['filter_label'] }}</div>
            <div class="text-muted small">Generated: {{ now()->format('d M Y, h:i A') }}</div>
        </div>
    </div>

    <!-- KPI Summary Grid -->
    <div class="row g-3 mb-4">
        <div class="col-3">
            <div class="stat-card">
                <small class="text-muted d-block fw-semibold">Total Sales Revenue</small>
                <h4 class="fw-bold text-primary mb-0 font-monospace">৳{{ number_format($stats['filtered_revenue'], 2) }}</h4>
                <small class="text-success fw-semibold">Paid: ৳{{ number_format($stats['paid_revenue'], 2) }}</small>
            </div>
        </div>
        <div class="col-3">
            <div class="stat-card">
                <small class="text-muted d-block fw-semibold">Total Orders</small>
                <h4 class="fw-bold text-dark mb-0 font-monospace">{{ number_format($stats['filtered_orders']) }}</h4>
                <small class="text-muted">Delivered: {{ number_format($stats['delivered_orders']) }}</small>
            </div>
        </div>
        <div class="col-3">
            <div class="stat-card">
                <small class="text-muted d-block fw-semibold">Total Pageviews</small>
                <h4 class="fw-bold text-info mb-0 font-monospace">{{ number_format($stats['visitor']['filtered_views']) }}</h4>
                <small class="text-muted">Today: {{ number_format($stats['visitor']['today_views']) }} views</small>
            </div>
        </div>
        <div class="col-3">
            <div class="stat-card">
                <small class="text-muted d-block fw-semibold">Unique Visitors</small>
                <h4 class="fw-bold text-success mb-0 font-monospace">{{ number_format($stats['visitor']['filtered_uniques']) }}</h4>
                <small class="text-muted">Today: {{ number_format($stats['visitor']['today_uniques']) }} visitors</small>
            </div>
        </div>
    </div>

    <!-- Payment Gateways Collection Breakdown -->
    <div class="mb-4">
        <h6 class="fw-bold text-dark border-bottom pb-2 mb-3"><i class="fas fa-credit-card me-1.5 text-primary"></i> Payment Gateways Collection Summary</h6>
        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Payment Method</th>
                        <th class="text-end">Collected Amount (৳)</th>
                        <th class="text-end">Share (%)</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $totRev = $stats['filtered_revenue'] > 0 ? $stats['filtered_revenue'] : 1;
                        $bkashPct = round(($stats['payment_split']['bkash'] / $totRev) * 100, 1);
                        $nagadPct = round(($stats['payment_split']['nagad'] / $totRev) * 100, 1);
                        $rocketPct = round(($stats['payment_split']['rocket'] / $totRev) * 100, 1);
                        $codPct = round(($stats['payment_split']['cod'] / $totRev) * 100, 1);
                    @endphp
                    <tr>
                        <td><strong>bKash (Mobile Banking)</strong></td>
                        <td class="text-end fw-bold font-monospace">৳{{ number_format($stats['payment_split']['bkash'], 2) }}</td>
                        <td class="text-end font-monospace">{{ $bkashPct }}%</td>
                    </tr>
                    <tr>
                        <td><strong>Nagad (Mobile Banking)</strong></td>
                        <td class="text-end fw-bold font-monospace">৳{{ number_format($stats['payment_split']['nagad'], 2) }}</td>
                        <td class="text-end font-monospace">{{ $nagadPct }}%</td>
                    </tr>
                    <tr>
                        <td><strong>Rocket (Dutch-Bangla)</strong></td>
                        <td class="text-end fw-bold font-monospace">৳{{ number_format($stats['payment_split']['rocket'], 2) }}</td>
                        <td class="text-end font-monospace">{{ $rocketPct }}%</td>
                    </tr>
                    <tr>
                        <td><strong>Cash on Delivery (COD)</strong></td>
                        <td class="text-end fw-bold font-monospace">৳{{ number_format($stats['payment_split']['cod'], 2) }}</td>
                        <td class="text-end font-monospace">{{ $codPct }}%</td>
                    </tr>
                    <tr class="table-light fw-bold">
                        <td>Total Collections</td>
                        <td class="text-end text-primary font-monospace">৳{{ number_format($stats['filtered_revenue'], 2) }}</td>
                        <td class="text-end font-monospace">100%</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Orders in this timeframe -->
    <div class="mb-4">
        <h6 class="fw-bold text-dark border-bottom pb-2 mb-3"><i class="fas fa-receipt me-1.5 text-primary"></i> Recent Orders Breakdown</h6>
        <div class="table-responsive">
            <table class="table table-bordered table-sm align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Order No.</th>
                        <th>Customer Name & Phone</th>
                        <th>Book Details</th>
                        <th>Payment</th>
                        <th class="text-end">Total Amount</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentOrders as $o)
                        <tr>
                            <td><strong>#{{ $o->order_number }}</strong></td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $o->customer_name }}</div>
                                <div class="small text-muted font-monospace">{{ $o->customer_phone }}</div>
                            </td>
                            <td>{{ $o->book?->title ?? 'Book Order' }}</td>
                            <td>
                                <span class="badge bg-light text-dark border text-uppercase rounded-pill px-2">{{ $o->payment_method ?? 'COD' }}</span>
                            </td>
                            <td class="text-end fw-bold font-monospace">৳{{ number_format($o->total_amount, 2) }}</td>
                            <td class="small text-muted">{{ $o->created_at?->format('d/m/Y h:i A') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-3">No orders found in this timeframe.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Footer Signature Area -->
    <div class="row mt-5 pt-4 border-top">
        <div class="col-6">
            <div class="text-muted small">System generated executive report</div>
            <div class="text-muted small">Idea Prokashon © {{ date('Y') }}</div>
        </div>
        <div class="col-6 text-end">
            <div class="d-inline-block border-top border-dark px-4 pt-1 small fw-bold">
                Authorized Executive Signature
            </div>
        </div>
    </div>

</div>

</body>
</html>
