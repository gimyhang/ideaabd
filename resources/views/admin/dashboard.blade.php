@extends('layouts.admin')

@section('title', 'ড্যাশবোর্ড')
@section('heading', 'স্মার্ট ড্যাশবোর্ড ও কন্ট্রোল প্যানেল')
@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">ড্যাশবোর্ড</li>
@endsection

@section('actions')
    <button type="button" class="btn btn-outline-secondary btn-sm" data-theme-toggle title="থিম সুইচার">
        <i class="fas fa-moon me-1"></i> ডার্ক/লাইট
    </button>
    @if (Route::has('admin.registrations.index'))
        <a href="{{ route('admin.registrations.index', ['status' => 'pending']) }}" class="btn btn-outline-warning btn-sm">
            <i class="fas fa-user-check me-1"></i> রেজিস্ট্রেশন
            @if (($stats['pending_regs'] ?? 0) > 0)
                <span class="badge bg-danger ms-1">@bn($stats['pending_regs'])</span>
            @endif
        </a>
    @endif
    <a href="{{ route('subadmin.bills.create') }}" class="btn btn-success btn-sm">
        <i class="fas fa-plus-circle me-1"></i> নতুন বিল
    </a>
    <a href="{{ route('admin.sub-admins.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-user-plus me-1"></i> নতুন সাব-অ্যাডমিন
    </a>
@endsection

@section('content')

@php
    $tiles = [
        ['label' => 'মোট বই',        'value' => $stats['total_books'],   'icon' => 'book',      'color' => 'var(--brand)',
         'foot' => 'ক্যাটালগে প্রকাশিত বই'],
        ['label' => 'মোট অর্ডার',     'value' => $stats['total_orders'],  'icon' => 'receipt',   'color' => '#ff6b35',
         'foot' => 'এই মাসে ' . \App\Support\Bn::num($stats['orders_month'] ?? 0) . 'টি'],
        ['label' => 'মোট রাজস্ব',     'value' => $stats['revenue_total'], 'icon' => 'sack-dollar', 'color' => '#2a9d8f',
         'foot' => 'এই মাসে ' . \App\Support\Bn::moneyShort($stats['revenue_month'] ?? 0), 'money' => true],
        ['label' => 'ব্যবহারকারী',    'value' => $stats['total_users'],   'icon' => 'users',     'color' => '#7048e8',
         'foot' => 'এই মাসে নতুন ' . \App\Support\Bn::num($stats['new_users_month'] ?? 0) . ' জন'],
    ];

    $secondary = [
        ['label' => 'ই-বুক',          'value' => $stats['total_ebooks'],     'icon' => 'tablet-screen-button', 'color' => '#0099ff', 'route' => 'admin.ebooks'],
        ['label' => 'লেখক',           'value' => $stats['total_authors'],    'icon' => 'pen-fancy',   'color' => '#e8590c', 'route' => 'admin.authors'],
        ['label' => 'ব্লগ পোস্ট',      'value' => $stats['total_blog'],       'icon' => 'blog',        'color' => '#1098ad', 'route' => 'admin.blog'],
        ['label' => 'ওয়েবজিন',        'value' => $stats['total_webzines'],   'icon' => 'newspaper',   'color' => '#f4a261', 'route' => 'admin.webzines'],
        ['label' => 'গবেষণা পত্র',     'value' => $stats['total_research'],   'icon' => 'flask',       'color' => '#5f3dc4', 'route' => null],
        ['label' => 'সাব-অ্যাডমিন',    'value' => $stats['total_sub_admins'], 'icon' => 'user-shield', 'color' => '#c2255c', 'route' => 'admin.sub-admins.index'],
    ];
@endphp

{{-- System Notice Banner (if set) --}}
@if (!empty($systemNotice) && !empty($systemNotice['text']))
    <div class="alert alert-{{ $systemNotice['type'] ?? 'info' }} alert-dismissible d-flex align-items-center gap-2 mb-4 shadow-sm" role="alert">
        <i class="fas fa-bullhorn fs-5 me-1"></i>
        <div class="fw-medium">{{ $systemNotice['text'] }}</div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

{{-- System Health Live Status Bar --}}
<div class="sys-health-bar d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div class="d-flex align-items-center gap-3 flex-wrap">
        <span class="d-flex align-items-center gap-1.5 small fw-semibold">
            <span class="health-dot {{ !empty($systemHealth['database']) ? 'health-dot--ok' : 'health-dot--warn' }}"></span>
            ডাটাবেজ: {{ !empty($systemHealth['database']) ? 'সংযুক্ত (Online)' : 'ডিফরেড' }}
        </span>
        <span class="d-flex align-items-center gap-1.5 small fw-semibold">
            <span class="health-dot {{ !empty($systemHealth['storage']) ? 'health-dot--ok' : 'health-dot--warn' }}"></span>
            স্টোরেজ: {{ !empty($systemHealth['storage']) ? 'প্রস্তুত (Writable)' : 'সীমাবদ্ধ' }}
        </span>
        <span class="d-flex align-items-center gap-1.5 small fw-semibold">
            <i class="fas fa-shield-cat text-primary"></i>
            সক্রিয় অ্যাডমিন: <strong>@bn($systemHealth['active_admins'] ?? 1) জন</strong>
        </span>
    </div>
    <div class="d-flex align-items-center gap-2">
        <span class="badge bg-light text-dark border fw-normal" style="font-size: 0.75rem;">
            PHP v{{ $systemHealth['php_version'] ?? PHP_VERSION }}
        </span>
        <a href="{{ route('admin.system-settings') }}" class="btn btn-sm btn-light border py-0 px-2 small">
            <i class="fas fa-gear me-1"></i> কনফিগার
        </a>
    </div>
</div>

{{-- Pending registrations alert --}}
@if (($stats['pending_regs'] ?? 0) > 0)
    <div class="alert alert-warning d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <div>
            <i class="fas fa-user-clock me-2"></i>
            <strong>@bn($stats['pending_regs'])টি রেজিস্ট্রেশন অনুমোদনের অপেক্ষায়</strong>
            — সেলার, প্রকাশক ও লেখকের আবেদন পর্যালোচনা করুন।
        </div>
        <a href="{{ route('admin.registrations.index', ['status' => 'pending']) }}" class="btn btn-sm btn-warning fw-semibold">
            এখনই দেখুন <i class="fas fa-arrow-right ms-1"></i>
        </a>
    </div>
@endif

{{-- KPI Tiles Grid --}}
<div class="row g-3 mb-4">
    @foreach ($tiles as $tile)
        <div class="col-xl-3 col-md-6">
            <div class="kpi kpi--glass" style="--bar: {{ $tile['color'] }}">
                <p class="kpi__label">{{ $tile['label'] }}</p>
                <p class="kpi__value" style="color: {{ $tile['color'] }}">
                    @if (is_null($tile['value']))
                        <span class="text-muted fs-5" title="ডেটা টেবিল এখনো তৈরি হয়নি">—</span>
                    @elseif (! empty($tile['money']))
                        @takaS($tile['value'])
                    @else
                        @bn($tile['value'])
                    @endif
                </p>
                <p class="kpi__foot">{{ $tile['foot'] }}</p>
                <span class="kpi__icon" style="background: {{ $tile['color'] }}1a; color: {{ $tile['color'] }}">
                    <i class="fas fa-{{ $tile['icon'] }}"></i>
                </span>
            </div>
        </div>
    @endforeach
</div>

{{-- Quick Action Center (মাল্টিফাংশনাল অ্যাকশন শর্টকাট) --}}
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="adm-card p-3">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h6 class="mb-0 fw-bold text-uppercase small text-muted"><i class="fas fa-bolt text-warning me-2"></i> মাল্টিফাংশনাল অ্যাকশন সেন্টার</h6>
                <span class="small text-muted">দ্রুত নেভিগেশন ও শর্টকাট</span>
            </div>
            <div class="row g-2">
                <div class="col-md-2 col-4">
                    <a href="{{ route('subadmin.bills.create') }}" class="quick text-center flex-column justify-content-center py-2">
                        <span class="quick__icon bg-primary text-white mx-auto mb-1"><i class="fas fa-receipt"></i></span>
                        <span class="quick__title small">নতুন বিল</span>
                    </a>
                </div>
                <div class="col-md-2 col-4">
                    <a href="{{ route('admin.moderation') }}" class="quick text-center flex-column justify-content-center py-2">
                        <span class="quick__icon bg-warning text-dark mx-auto mb-1"><i class="fas fa-filter"></i></span>
                        <span class="quick__title small">মডারেশন</span>
                    </a>
                </div>
                <div class="col-md-2 col-4">
                    <a href="{{ route('admin.roles.index') }}" class="quick text-center flex-column justify-content-center py-2">
                        <span class="quick__icon bg-info text-white mx-auto mb-1"><i class="fas fa-key"></i></span>
                        <span class="quick__title small">পারমিশন</span>
                    </a>
                </div>
                <div class="col-md-2 col-4">
                    <a href="{{ route('admin.activity-logs') }}" class="quick text-center flex-column justify-content-center py-2">
                        <span class="quick__icon bg-secondary text-white mx-auto mb-1"><i class="fas fa-history"></i></span>
                        <span class="quick__title small">অ্যাক্টিভিটি</span>
                    </a>
                </div>
                <div class="col-md-2 col-4">
                    <a href="{{ route('admin.sub-admins.index') }}" class="quick text-center flex-column justify-content-center py-2">
                        <span class="quick__icon bg-danger text-white mx-auto mb-1"><i class="fas fa-user-shield"></i></span>
                        <span class="quick__title small">সাব-অ্যাডমিন</span>
                    </a>
                </div>
                <div class="col-md-2 col-4">
                    <a href="{{ route('admin.system-settings') }}" class="quick text-center flex-column justify-content-center py-2">
                        <span class="quick__icon bg-dark text-white mx-auto mb-1"><i class="fas fa-sliders"></i></span>
                        <span class="quick__title small">সেটিংস</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Interactive Tabs Navigation --}}
<ul class="nav nav-pills nav-pills-modern mb-4 gap-2" id="dashboardTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="analytics-tab" data-bs-toggle="pill" data-bs-target="#analytics-pane" type="button" role="tab">
            <i class="fas fa-chart-line me-1.5"></i> অ্যানালিটিক্স ও মেট্রিক্স
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="activity-tab" data-bs-toggle="pill" data-bs-target="#activity-pane" type="button" role="tab">
            <i class="fas fa-clock-rotate-left me-1.5"></i> অডিট ও অ্যাক্টিভিটি
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="orders-tab" data-bs-toggle="pill" data-bs-target="#orders-pane" type="button" role="tab">
            <i class="fas fa-receipt me-1.5"></i> অর্ডার ও শীর্ষ সেলার
        </button>
    </li>
</ul>

<div class="tab-content" id="dashboardTabsContent">
    {{-- TAB 1: Analytics --}}
    <div class="tab-pane fade show active" id="analytics-pane" role="tabpanel" aria-labelledby="analytics-tab">
        <div class="row g-3 mb-4">
            <div class="col-lg-8">
                <div class="adm-card h-100">
                    <div class="adm-card__head">
                        <h6><i class="fas fa-chart-line me-2 text-primary"></i> বিক্রয় প্রবণতা <span class="text-muted fw-normal small">(গত ১২ মাস)</span></h6>
                        <span class="pill pill--info">মোট @takaS($stats['revenue_total'] ?? 0)</span>
                    </div>
                    <div class="adm-card__body">
                        <div class="chart-box"><canvas id="salesChart"></canvas></div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="adm-card h-100">
                    <div class="adm-card__head">
                        <h6><i class="fas fa-chart-pie me-2 text-purple"></i> ব্যবহারকারীর ধরন</h6>
                    </div>
                    <div class="adm-card__body">
                        @if (empty($roles))
                            <div class="empty-state"><i class="fas fa-users-slash"></i>কোনো ব্যবহারকারী নেই</div>
                        @else
                            <div class="chart-box chart-box--sm"><canvas id="roleChart"></canvas></div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Secondary counters --}}
        <div class="row g-3 mb-4">
            @foreach ($secondary as $item)
                <div class="col-xl-2 col-md-4 col-6">
                    @php $href = $item['route'] && Route::has($item['route']) ? route($item['route']) : null; @endphp
                    <a @if ($href) href="{{ $href }}" @endif class="quick {{ $href ? '' : 'pe-none' }}">
                        <span class="quick__icon" style="background: {{ $item['color'] }}1a; color: {{ $item['color'] }}">
                            <i class="fas fa-{{ $item['icon'] }}"></i>
                        </span>
                        <span>
                            <span class="quick__title">
                                @if (is_null($item['value']))
                                    <span class="text-muted">—</span>
                                @else
                                    @bn($item['value'])
                                @endif
                            </span>
                            <span class="quick__sub d-block">{{ $item['label'] }}</span>
                        </span>
                    </a>
                </div>
            @endforeach
        </div>
    </div>

    {{-- TAB 2: Activity Stream & Pending Approvals --}}
    <div class="tab-pane fade" id="activity-pane" role="tabpanel" aria-labelledby="activity-tab">
        <div class="row g-3">
            <div class="col-lg-7">
                <div class="adm-card h-100">
                    <div class="adm-card__head">
                        <h6><i class="fas fa-stream me-2 text-primary"></i> সাম্প্রতিক অ্যাডমিন অ্যাক্টিভিটি স্ট্রিম</h6>
                        <a href="{{ route('admin.activity-logs') }}" class="btn btn-sm btn-outline-primary">সব দেখুন</a>
                    </div>
                    <div class="adm-card__body">
                        @if($activityLogs->isEmpty())
                            <div class="empty-state py-4"><i class="fas fa-history"></i>কোনো অডিট লগ নেই</div>
                        @else
                            <div class="timeline-stream">
                                @foreach($activityLogs as $log)
                                    <div class="timeline-item">
                                        <div class="d-flex align-items-center justify-content-between mb-1">
                                            <span class="fw-semibold small">{{ $log->user->name ?? 'সিস্টেম' }}</span>
                                            <small class="text-muted" style="font-size:.74rem">@bnDate($log->created_at) {{ $log->created_at->format('h:i A') }}</small>
                                        </div>
                                        <p class="small mb-0 text-secondary">{{ $log->description }}</p>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="adm-card h-100">
                    <div class="adm-card__head">
                        <h6><i class="fas fa-user-clock me-2 text-warning"></i> অনুমোদনের অপেক্ষায়</h6>
                        <a href="{{ route('admin.registrations.index', ['status' => 'pending']) }}" class="btn btn-sm btn-outline-warning">সব দেখুন</a>
                    </div>
                    @if ($pendingRegs->isEmpty())
                        <div class="empty-state"><i class="fas fa-circle-check"></i>সব আবেদন নিষ্পন্ন হয়েছে</div>
                    @else
                        <div class="list-group list-group-flush">
                            @foreach ($pendingRegs as $reg)
                                <a href="{{ route('admin.registrations.show', $reg) }}" class="list-group-item list-group-item-action d-flex align-items-center gap-3">
                                    <span class="adm-avatar adm-avatar--sm">{{ mb_substr($reg->name, 0, 1) }}</span>
                                    <span class="flex-grow-1">
                                        <span class="d-block fw-semibold small">{{ $reg->name }}</span>
                                        <span class="d-block text-muted" style="font-size:.75rem">{{ $reg->email }}</span>
                                    </span>
                                    <span class="pill pill--pending">
                                        {{ ['seller' => 'সেলার', 'publisher' => 'প্রকাশক', 'author' => 'লেখক'][$reg->reg_type] ?? $reg->reg_type }}
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- TAB 3: Orders & Top Sellers --}}
    <div class="tab-pane fade" id="orders-pane" role="tabpanel" aria-labelledby="orders-tab">
        <div class="row g-3">
            <div class="col-lg-7">
                <div class="adm-card h-100">
                    <div class="adm-card__head">
                        <h6><i class="fas fa-receipt me-2 text-warning"></i> সাম্প্রতিক অর্ডারসমূহ</h6>
                        <a href="{{ route('admin.orders') }}" class="btn btn-sm btn-outline-primary">সব দেখুন</a>
                    </div>
                    @if ($recentBills->isEmpty())
                        <div class="empty-state"><i class="fas fa-inbox"></i>এখনো কোনো অর্ডার নেই</div>
                    @else
                        <div class="table-responsive">
                            <table class="table adm-table">
                                <thead>
                                    <tr>
                                        <th class="ps-3">বিল নং</th>
                                        <th>ক্রেতা</th>
                                        <th>সেলার</th>
                                        <th class="text-end">মোট</th>
                                        <th class="pe-3">অবস্থা</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($recentBills as $bill)
                                        <tr>
                                            <td class="ps-3"><span class="fw-semibold small">{{ $bill->bill_no }}</span></td>
                                            <td>
                                                <div class="small">{{ $bill->customer_name ?: 'অজানা' }}</div>
                                                <div class="text-muted" style="font-size:.75rem">{{ $bill->customer_phone }}</div>
                                            </td>
                                            <td class="small text-muted">{{ $bill->seller->name ?? '—' }}</td>
                                            <td class="text-end fw-semibold">@taka($bill->total)</td>
                                            <td class="pe-3">
                                                <span class="pill {{ $bill->payment_status === 'paid' ? 'pill--ok' : 'pill--pending' }}">
                                                    {{ $bill->payment_status === 'paid' ? 'পরিশোধিত' : 'বাকি' }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <div class="col-lg-5">
                <div class="adm-card h-100">
                    <div class="adm-card__head">
                        <h6><i class="fas fa-trophy me-2 text-warning"></i> শীর্ষ সেলারসমূহ</h6>
                        <a href="{{ route('admin.sub-admins.index') }}" class="btn btn-sm btn-outline-primary">সব দেখুন</a>
                    </div>
                    <div class="adm-card__body">
                        @if ($topSellers->isEmpty())
                            <div class="empty-state py-4"><i class="fas fa-chart-simple"></i>বিক্রয়ের তথ্য নেই</div>
                        @else
                            @php $max = max(1, (float) $topSellers->max('revenue')); @endphp
                            @foreach ($topSellers as $row)
                                <div class="barlist__row">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="small fw-semibold">{{ $row->seller->name ?? 'অজানা সেলার' }}</span>
                                        <span class="small text-muted">@takaS($row->revenue) · @bn($row->bills)টি বিল</span>
                                    </div>
                                    <div class="barlist__track">
                                        <div class="barlist__fill" style="width: {{ round(((float) $row->revenue / $max) * 100, 1) }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
<script>
(function () {
    if (typeof Chart === 'undefined') return;

    Chart.defaults.font.family = "'Hind Siliguri', sans-serif";
    Chart.defaults.color = '#6b7c93';

    var sales  = @json($sales);
    var growth = @json($growth);
    var roles  = @json($roles);

    var taka = function (v) { return '৳' + Number(v).toLocaleString('bn-BD'); };

    var salesCanvas = document.getElementById('salesChart');
    if (salesCanvas) {
        new Chart(salesCanvas, {
            data: {
                labels: sales.labels,
                datasets: [
                    {
                        type: 'line',
                        label: 'রাজস্ব',
                        data: sales.revenue,
                        borderColor: '#0066cc',
                        backgroundColor: 'rgba(0,102,204,.12)',
                        fill: true,
                        tension: .35,
                        borderWidth: 2,
                        pointRadius: 3,
                        yAxisID: 'y'
                    },
                    {
                        type: 'bar',
                        label: 'অর্ডার সংখ্যা',
                        data: sales.orders,
                        backgroundColor: 'rgba(255,107,53,.55)',
                        borderRadius: 4,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8 } },
                    tooltip: {
                        callbacks: {
                            label: function (c) {
                                return c.dataset.yAxisID === 'y'
                                    ? c.dataset.label + ': ' + taka(c.parsed.y)
                                    : c.dataset.label + ': ' + Number(c.parsed.y).toLocaleString('bn-BD');
                            }
                        }
                    }
                },
                scales: {
                    y:  { position: 'left',  beginAtZero: true, grid: { color: '#eef3f8' },
                          ticks: { callback: function (v) { return taka(v); } } },
                    y1: { position: 'right', beginAtZero: true, grid: { drawOnChartArea: false },
                          ticks: { precision: 0 } },
                    x:  { grid: { display: false } }
                }
            }
        });
    }

    var roleLabels = {
        admin: 'অ্যাডমিন', sub_admin: 'সাব-অ্যাডমিন', seller: 'সেলার',
        publisher: 'প্রকাশক', author: 'লেখক', buyer: 'ক্রেতা', customer: 'গ্রাহক'
    };

    var roleCanvas = document.getElementById('roleChart');
    if (roleCanvas && Object.keys(roles).length) {
        new Chart(roleCanvas, {
            type: 'doughnut',
            data: {
                labels: Object.keys(roles).map(function (k) { return roleLabels[k] || k; }),
                datasets: [{
                    data: Object.values(roles),
                    backgroundColor: ['#0066cc', '#0099ff', '#2a9d8f', '#f4a261', '#e8590c', '#7048e8', '#c2255c'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '62%',
                plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8, padding: 12 } } }
            }
        });
    }
})();
</script>
@endpush
