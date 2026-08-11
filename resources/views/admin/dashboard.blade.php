@extends('layouts.admin')

@section('title', 'ড্যাশবোর্ড')
@section('heading', 'ড্যাশবোর্ড')
@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">ড্যাশবোর্ড</li>
@endsection

@section('actions')
    @if (Route::has('admin.registrations.index'))
        <a href="{{ route('admin.registrations.index', ['status' => 'pending']) }}" class="btn btn-outline-warning">
            <i class="fas fa-user-check me-1"></i> রেজিস্ট্রেশন
            @if (($stats['pending_regs'] ?? 0) > 0)
                <span class="badge bg-danger ms-1">@bn($stats['pending_regs'])</span>
            @endif
        </a>
    @endif
    <a href="{{ route('admin.sub-admins.create') }}" class="btn btn-primary">
        <i class="fas fa-user-plus me-1"></i> নতুন সাব-অ্যাডমিন
    </a>
@endsection

@section('content')

@php
    /** Renders "—" when a metric's table is missing, so a zero is never faked. */
    $tiles = [
        ['label' => 'মোট বই',        'value' => $stats['total_books'],   'icon' => 'book',      'color' => '#0066cc',
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

{{-- Pending registrations callout --}}
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

{{-- Headline KPIs --}}
<div class="row g-3 mb-4">
    @foreach ($tiles as $tile)
        <div class="col-xl-3 col-md-6">
            <div class="kpi" style="--bar: {{ $tile['color'] }}">
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

{{-- Charts --}}
<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="adm-card h-100">
            <div class="adm-card__head">
                <h6><i class="fas fa-chart-line me-2" style="color:#0066cc"></i> বিক্রয় প্রবণতা <span class="text-muted fw-normal small">(গত ১২ মাস)</span></h6>
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
                <h6><i class="fas fa-chart-pie me-2" style="color:#7048e8"></i> ব্যবহারকারীর ধরন</h6>
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

<div class="row g-3 mb-4">
    {{-- Recent bills --}}
    <div class="col-lg-7">
        <div class="adm-card h-100">
            <div class="adm-card__head">
                <h6><i class="fas fa-receipt me-2" style="color:#ff6b35"></i> সাম্প্রতিক অর্ডার</h6>
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

    {{-- Pending registrations --}}
    <div class="col-lg-5">
        <div class="adm-card h-100">
            <div class="adm-card__head">
                <h6><i class="fas fa-user-clock me-2" style="color:#f4a261"></i> অনুমোদনের অপেক্ষায়</h6>
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

<div class="row g-3">
    {{-- Top sellers --}}
    <div class="col-lg-5">
        <div class="adm-card h-100">
            <div class="adm-card__head">
                <h6><i class="fas fa-trophy me-2" style="color:#f4a261"></i> শীর্ষ সেলার</h6>
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

    {{-- User growth --}}
    <div class="col-lg-7">
        <div class="adm-card h-100">
            <div class="adm-card__head">
                <h6><i class="fas fa-user-plus me-2" style="color:#2a9d8f"></i> নতুন ব্যবহারকারী <span class="text-muted fw-normal small">(গত ৬ মাস)</span></h6>
            </div>
            <div class="adm-card__body">
                <div class="chart-box chart-box--sm"><canvas id="growthChart"></canvas></div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
<script>
(function () {
    if (typeof Chart === 'undefined') return;   // CDN blocked — tables still work

    Chart.defaults.font.family = "'Hind Siliguri', sans-serif";
    Chart.defaults.color = '#6b7c93';

    var sales  = @json($sales);
    var growth = @json($growth);
    var roles  = @json($roles);

    var taka = function (v) { return '৳' + Number(v).toLocaleString('bn-BD'); };

    new Chart(document.getElementById('salesChart'), {
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

    new Chart(document.getElementById('growthChart'), {
        type: 'bar',
        data: {
            labels: growth.labels,
            datasets: [{
                label: 'নতুন ব্যবহারকারী',
                data: growth.counts,
                backgroundColor: 'rgba(42,157,143,.65)',
                borderRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#eef3f8' } },
                x: { grid: { display: false } }
            }
        }
    });

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
