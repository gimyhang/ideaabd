<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>বিক্রয় ও ভিজিটর অ্যানালিটিক্স রিপোর্ট — আইডিয়া প্রকাশন</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Hind Siliguri', 'Inter', sans-serif;
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
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> ড্যাশবোর্ডে ফিরুন
        </a>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-primary btn-sm px-3 fw-bold">
                <i class="fas fa-print me-1.5"></i> প্রিন্ট / PDF সংরক্ষণ করুন
            </button>
        </div>
    </div>

    <!-- Letterhead -->
    <div class="row align-items-center mb-4 pb-3 border-bottom">
        <div class="col-7">
            <div class="header-logo mb-1">আইডিয়া প্রকাশন</div>
            <div class="text-muted small">স্মার্ট ই-কমার্স ও ডিজিটাল প্রকাশনা প্ল্যাটফর্ম</div>
            <div class="text-muted small">ওয়েবসাইট: ideaabd.com | ফোন: 01558712810</div>
        </div>
        <div class="col-5 text-end">
            <h5 class="fw-bold text-dark mb-1">এক্সিকিউটিভ পারফরম্যান্স রিপোর্ট</h5>
            <div class="badge bg-primary text-white p-1.5 mb-1">{{ $stats['filter_label'] }}</div>
            <div class="text-muted small">রিপোর্ট তৈরির সময়: {{ now()->format('d M Y, h:i A') }}</div>
        </div>
    </div>

    <!-- KPI Summary Grid -->
    <div class="row g-3 mb-4">
        <div class="col-3">
            <div class="stat-card">
                <small class="text-muted d-block">মোট বিক্রয় রাজস্ব</small>
                <h4 class="fw-bold text-primary mb-0">৳@bn(number_format($stats['filtered_revenue'], 0))</h4>
                <small class="text-success fw-semibold">পরিশোধিত: ৳@bn(number_format($stats['paid_revenue'], 0))</small>
            </div>
        </div>
        <div class="col-3">
            <div class="stat-card">
                <small class="text-muted d-block">মোট অর্ডার সংখ্যা</small>
                <h4 class="fw-bold text-dark mb-0">@bn($stats['filtered_orders']) টি</h4>
                <small class="text-muted">ডেলিভারড: @bn($stats['delivered_orders']) টি</small>
            </div>
        </div>
        <div class="col-3">
            <div class="stat-card">
                <small class="text-muted d-block">মোট পেজভিউ</small>
                <h4 class="fw-bold text-info mb-0">@bn($stats['visitor']['filtered_views'])</h4>
                <small class="text-muted">আজকে: @bn($stats['visitor']['today_views']) ভিউ</small>
            </div>
        </div>
        <div class="col-3">
            <div class="stat-card">
                <small class="text-muted d-block">ইউনিক ভিজিটর</small>
                <h4 class="fw-bold text-success mb-0">@bn($stats['visitor']['filtered_uniques']) জন</h4>
                <small class="text-muted">আজকে: @bn($stats['visitor']['today_uniques']) জন</small>
            </div>
        </div>
    </div>

    <!-- Payment Gateways Collection Breakdown -->
    <div class="mb-4">
        <h6 class="fw-bold text-dark border-bottom pb-2 mb-3"><i class="fas fa-credit-card me-1.5 text-primary"></i> পেমেন্ট গেটওয়ে কালেকশন সামারি</h6>
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead>
                    <tr>
                        <th>পেমেন্ট মেথড</th>
                        <th class="text-end">সংগৃহীত বিল (৳)</th>
                        <th class="text-end">শতকরা হার (%)</th>
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
                        <td><strong>বিকাশ (bKash)</strong></td>
                        <td class="text-end fw-bold">৳@bn(number_format($stats['payment_split']['bkash'], 0))</td>
                        <td class="text-end">@bn($bkashPct)%</td>
                    </tr>
                    <tr>
                        <td><strong>নগদ (Nagad)</strong></td>
                        <td class="text-end fw-bold">৳@bn(number_format($stats['payment_split']['nagad'], 0))</td>
                        <td class="text-end">@bn($nagadPct)%</td>
                    </tr>
                    <tr>
                        <td><strong>রকেট (Rocket)</strong></td>
                        <td class="text-end fw-bold">৳@bn(number_format($stats['payment_split']['rocket'], 0))</td>
                        <td class="text-end">@bn($rocketPct)%</td>
                    </tr>
                    <tr>
                        <td><strong>ক্যাশ অন ডেলিভারি (COD)</strong></td>
                        <td class="text-end fw-bold">৳@bn(number_format($stats['payment_split']['cod'], 0))</td>
                        <td class="text-end">@bn($codPct)%</td>
                    </tr>
                    <tr class="table-light fw-bold">
                        <td>সর্বমোট কালেকশন</td>
                        <td class="text-end text-primary">৳@bn(number_format($stats['filtered_revenue'], 0))</td>
                        <td class="text-end">১০০%</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Orders in this timeframe -->
    <div class="mb-4">
        <h6 class="fw-bold text-dark border-bottom pb-2 mb-3"><i class="fas fa-receipt me-1.5 text-primary"></i> সাম্প্রতিক অর্ডার বিবরণী</h6>
        <div class="table-responsive">
            <table class="table table-bordered table-sm align-middle">
                <thead>
                    <tr>
                        <th>অর্ডার নং</th>
                        <th>গ্রাহকের নাম ও ফোন</th>
                        <th>বইয়ের বিবরণ</th>
                        <th>পেমেন্ট</th>
                        <th class="text-end">মোট বিল</th>
                        <th>তারিখ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentOrders as $o)
                        <tr>
                            <td><strong>#{{ $o->order_number }}</strong></td>
                            <td>
                                {{ $o->customer_name }}
                                <div class="small text-muted">{{ $o->customer_phone }}</div>
                            </td>
                            <td>{{ $o->book?->title ?? 'বই' }}</td>
                            <td>
                                <span class="badge bg-light text-dark border text-uppercase">{{ $o->payment_method ?? 'COD' }}</span>
                            </td>
                            <td class="text-end fw-bold">৳@bn(number_format($o->total_amount, 0))</td>
                            <td class="small text-muted">{{ $o->created_at?->format('d/m/Y h:i A') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-3">এই সময়সীমায় কোনো অর্ডার পাওয়া যায়নি।</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Footer Signature Area -->
    <div class="row mt-5 pt-4 border-top">
        <div class="col-6">
            <div class="text-muted small">স্বয়ংক্রিয়ভাবে জেনারেটকৃত সিস্টেম রিপোর্ট</div>
            <div class="text-muted small">আইডিয়া প্রকাশন © {{ date('Y') }}</div>
        </div>
        <div class="col-6 text-end">
            <div class="d-inline-block border-top border-dark px-4 pt-1 small fw-bold">
                অনুমোদনকারী কর্মকর্তার স্বাক্ষর
            </div>
        </div>
    </div>

</div>

</body>
</html>
