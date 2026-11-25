@extends('layouts.admin')

@section('title', 'Raw Stock')
@section('heading', 'Raw Stock')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.purchases.index') }}">Purchases</a></li>
    <li class="breadcrumb-item active">Raw Stock</li>
@endsection

@php
    $q = fn ($v) => rtrim(rtrim(number_format((float) $v, 3), '0'), '.');
    $tk = fn ($v) => '৳' . number_format((float) $v, 2);
    $groups = \App\Models\RawMaterial::GROUPS;
    $groupIcons = ['paper' => 'fa-scroll', 'board' => 'fa-square', 'plate' => 'fa-clone', 'ink' => 'fa-droplet', 'binding' => 'fa-book', 'lamination' => 'fa-layer-group', 'packaging' => 'fa-box', 'other' => 'fa-cubes', 'service' => 'fa-file-invoice'];
    $stateBadge = [
        'in'      => ['bg-success-subtle text-success', 'মজুদ আছে'],
        'low'     => ['bg-warning-subtle text-dark', 'কম'],
        'out'     => ['bg-danger-subtle text-danger', 'শেষ'],
        'service' => ['bg-secondary-subtle text-secondary', 'সেবা'],
    ];
    $maxMonth = max(1, collect($monthly)->flatMap(fn ($m) => [$m['in'], $m['out']])->max());
@endphp

@section('actions')
    <div class="d-flex flex-wrap gap-2">
        <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 fw-semibold" data-rm-new>
            <i class="fa-solid fa-plus me-1"></i> নতুন উপকরণ
        </button>
        <a href="{{ route('admin.purchases.create', ['type' => 'raw_materials']) }}" class="btn btn-warning btn-sm rounded-pill px-3 fw-bold text-dark">
            <i class="fa-solid fa-cart-plus me-1"></i> কাঁচামাল ক্রয়
        </a>
        <a href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-semibold">
            <i class="fa-solid fa-file-csv text-success me-1"></i> Export
        </a>
        <form action="{{ route('admin.purchases.raw.resync') }}" method="POST" class="d-inline" data-confirm="সব কাঁচামাল ক্রয় ইনভয়েস আবার পড়ে মজুদের ক্রয়-এন্ট্রি মিলিয়ে নেওয়া হবে। আপনার ব্যবহার/সমন্বয় এন্ট্রি অপরিবর্তিত থাকবে।" data-confirm-title="ক্রয় থেকে মিলিয়ে নিন">
            @csrf
            <button type="submit" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-semibold" title="ক্রয় ইনভয়েস থেকে আবার মিলিয়ে নিন">
                <i class="fa-solid fa-arrows-rotate me-1"></i> Sync
            </button>
        </form>
        <a href="{{ route('admin.purchases.index', ['category' => 'raw_materials']) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-semibold">
            <i class="fa-solid fa-receipt me-1"></i> ক্রয় তালিকা
        </a>
    </div>
@endsection

@section('content')
<div class="rm-page">

{{-- KPIs --}}
<div class="row g-3 mb-3">
    <div class="col-6 col-lg">
        <div class="rm-kpi"><span class="rm-kpi__ic bg-primary-subtle text-primary"><i class="fa-solid fa-cubes"></i></span>
            <div><div class="rm-kpi__lb">উপকরণ</div><div class="rm-kpi__v">{{ number_format($kpi['materials']) }}</div></div></div>
    </div>
    <div class="col-6 col-lg">
        <div class="rm-kpi"><span class="rm-kpi__ic bg-success-subtle text-success"><i class="fa-solid fa-warehouse"></i></span>
            <div><div class="rm-kpi__lb">মজুদের মূল্য</div><div class="rm-kpi__v font-monospace">{{ $tk($kpi['value']) }}</div></div></div>
    </div>
    <div class="col-6 col-lg">
        <div class="rm-kpi"><span class="rm-kpi__ic bg-warning-subtle text-warning"><i class="fa-solid fa-cart-arrow-down"></i></span>
            <div><div class="rm-kpi__lb">এই মাসে ক্রয়</div><div class="rm-kpi__v font-monospace">{{ $tk($kpi['bought']) }}</div></div></div>
    </div>
    <div class="col-6 col-lg">
        <div class="rm-kpi"><span class="rm-kpi__ic bg-info-subtle text-info"><i class="fa-solid fa-dolly"></i></span>
            <div><div class="rm-kpi__lb">এই মাসে ব্যবহার</div><div class="rm-kpi__v font-monospace">{{ $tk($kpi['used']) }}</div></div></div>
    </div>
    <div class="col-12 col-lg">
        <a href="{{ route('admin.purchases.raw.index', ['status' => 'low']) }}" class="rm-kpi text-decoration-none {{ $kpi['low'] ? 'rm-kpi--alert' : '' }}"><span class="rm-kpi__ic bg-danger-subtle text-danger"><i class="fa-solid fa-triangle-exclamation"></i></span>
            <div><div class="rm-kpi__lb">কম / শেষ</div><div class="rm-kpi__v {{ $kpi['low'] ? 'text-danger' : '' }}">{{ number_format($kpi['low']) }}</div></div></a>
    </div>
</div>

<div class="row g-3 mb-3">
    {{-- 6-month bought vs used --}}
    <div class="col-lg-7">
        <div class="rm-card h-100">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div class="fw-bold small"><i class="fa-solid fa-chart-column text-primary me-1"></i> ক্রয় বনাম ব্যবহার (৬ মাস)</div>
                <div class="small text-muted"><span class="rm-dot bg-warning"></span> ক্রয় <span class="rm-dot bg-info ms-2"></span> ব্যবহার</div>
            </div>
            <div class="rm-bars" role="img" aria-label="Last six months bought and used">
                @foreach($monthly as $ym => $m)
                    <div class="rm-bars__col">
                        <div class="rm-bars__pair">
                            <span class="rm-bars__b bg-warning" style="height: {{ round($m['in'] / $maxMonth * 100, 1) }}%" title="ক্রয় {{ $tk($m['in']) }}"></span>
                            <span class="rm-bars__b bg-info" style="height: {{ round($m['out'] / $maxMonth * 100, 1) }}%" title="ব্যবহার {{ $tk($m['out']) }}"></span>
                        </div>
                        <div class="rm-bars__lb">{{ \Carbon\Carbon::parse($ym . '-01')->format('M') }}</div>
                    </div>
                @endforeach
            </div>
            @if($kpi['services'] > 0)
                <div class="small text-muted mt-2"><i class="fa-solid fa-file-invoice me-1"></i> সেবা / বিল (ছাপা, মজুরি ইত্যাদি, মজুদ নয়) মোট: <strong class="font-monospace">{{ $tk($kpi['services']) }}</strong></div>
            @endif
        </div>
    </div>
    {{-- recent moves --}}
    <div class="col-lg-5">
        <div class="rm-card h-100">
            <div class="fw-bold small mb-2"><i class="fa-solid fa-clock-rotate-left text-primary me-1"></i> সাম্প্রতিক লেনদেন</div>
            <div class="rm-recent-list">
            @forelse($recent as $mv)
                <div class="rm-recent">
                    <span class="rm-recent__t rm-t-{{ $mv->type }}">{{ \App\Models\RawMaterialMove::TYPES[$mv->type] ?? $mv->type }}</span>
                    <div class="flex-grow-1 min-w-0">
                        <div class="text-truncate fw-semibold small">{{ $mv->material?->name }} <span class="text-muted fw-normal">{{ $mv->material?->spec }}</span></div>
                        <div class="text-muted" style="font-size: 11px;">
                            {{ $mv->moved_at?->format('d M Y') }}
                            @if($mv->purchase) · <a href="{{ route('admin.purchases.show', $mv->purchase_id) }}">#{{ $mv->purchase->purchase_no }}</a>@endif
                            @if($mv->book || $mv->job) · {{ $mv->book?->title ?? $mv->job }}@endif
                        </div>
                    </div>
                    <div class="text-end font-monospace small {{ $mv->signedQty() < 0 ? 'text-danger' : 'text-success' }}">{{ $mv->signedQty() < 0 ? '−' : '+' }}{{ $q(abs($mv->signedQty())) }} <span class="text-muted">{{ $mv->material?->unit }}</span></div>
                </div>
            @empty
                <div class="text-muted small py-3 text-center">এখনো কোনো লেনদেন নেই। কাঁচামাল ক্রয় করলে এখানে স্বয়ংক্রিয়ভাবে আসবে।</div>
            @endforelse
            </div>
        </div>
    </div>
</div>

{{-- filters --}}
<div class="rm-card mb-3">
    <div class="d-flex flex-wrap gap-1.5 mb-2 rm-chips">
        <a href="{{ request()->fullUrlWithQuery(['group' => null]) }}" class="btn btn-sm rounded-pill {{ !$group ? 'btn-dark' : 'btn-light border' }}">সব <span class="opacity-75">({{ $groupCounts->sum() }})</span></a>
        @foreach($groups as $gk => [$gl])
            @if(($groupCounts[$gk] ?? 0) > 0 || $group === $gk)
                <a href="{{ request()->fullUrlWithQuery(['group' => $gk]) }}" class="btn btn-sm rounded-pill {{ $group === $gk ? 'btn-dark' : 'btn-light border' }}"><i class="fa-solid {{ $groupIcons[$gk] ?? 'fa-cube' }} me-1 opacity-75"></i>{{ $gl }} <span class="opacity-75">({{ $groupCounts[$gk] ?? 0 }})</span></a>
            @endif
        @endforeach
    </div>
    <form method="GET" class="row g-2 align-items-center">
        @if($group)<input type="hidden" name="group" value="{{ $group }}">@endif
        <div class="col-md-6">
            <div class="input-group">
                <span class="input-group-text bg-light"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                <input type="search" name="search" value="{{ $search }}" class="form-control" placeholder="উপকরণের নাম, মাপ, GSM..." id="rmSearch" autocomplete="off">
            </div>
        </div>
        <div class="col-7 col-md-3">
            <select name="status" class="form-select" onchange="this.form.submit()" aria-label="অবস্থা">
                <option value="">সব অবস্থা</option>
                <option value="in" @selected($status === 'in')>মজুদ আছে</option>
                <option value="low" @selected($status === 'low')>কম / শেষ</option>
                <option value="out" @selected($status === 'out')>শেষ</option>
                <option value="inactive" @selected($status === 'inactive')>নিষ্ক্রিয়</option>
            </select>
        </div>
        <div class="col-5 col-md-3 d-flex gap-2">
            <button class="btn btn-primary rounded-pill w-100 fw-semibold"><i class="fa-solid fa-filter me-1"></i> খুঁজুন</button>
            @if($search || $status || $group)
                <a href="{{ route('admin.purchases.raw.index') }}" class="btn btn-light border rounded-pill" title="Reset"><i class="fa-solid fa-rotate-left"></i></a>
            @endif
        </div>
    </form>
</div>

{{-- stock table --}}
<div class="rm-card p-0 overflow-hidden">
    @if($materials->isEmpty())
        <div class="text-center py-5">
            <div class="rm-empty mb-3"><i class="fa-solid fa-warehouse"></i></div>
            <h6 class="fw-bold">কোনো উপকরণ পাওয়া যায়নি</h6>
            <p class="text-muted small mb-3">কাঁচামাল ক্রয় ইনভয়েস করলে উপকরণ স্বয়ংক্রিয়ভাবে যুক্ত হয়। চাইলে শুরুর মজুদসহ হাতে যোগ করুন।</p>
            <button type="button" class="btn btn-primary btn-sm rounded-pill px-4" data-rm-new><i class="fa-solid fa-plus me-1"></i> নতুন উপকরণ</button>
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 rm-table" id="rmTable">
                <thead class="table-light small text-muted">
                    <tr>
                        <th class="ps-3">উপকরণ</th>
                        <th class="text-end">ক্রয়</th>
                        <th class="text-end">ব্যবহার</th>
                        <th class="text-end">মজুদ</th>
                        <th class="text-end">গড় দর</th>
                        <th class="text-end">মূল্য</th>
                        <th class="text-center">অবস্থা</th>
                        <th class="text-end pe-3">কাজ</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($materials as $m)
                    @php
                        $t = $m->t;
                        $re = (float) $m->reorder_level;
                        $fill = $re > 0 ? min(100, max(0, $t['balance'] / ($re * 2) * 100)) : ($t['in_qty'] > 0 ? min(100, max(0, $t['balance'] / $t['in_qty'] * 100)) : 0);
                        $payload = ['id' => $m->id, 'name' => $m->name, 'spec' => $m->spec, 'unit' => $m->unit, 'group' => $m->group, 'reorder_level' => $re, 'notes' => $m->notes, 'is_active' => $m->is_active, 'balance' => $t['balance'], 'avg' => $t['avg_cost'], 'stocked' => $m->isStocked()];
                    @endphp
                    <tr data-search="{{ mb_strtolower($m->name . ' ' . $m->spec . ' ' . $m->groupLabel()) }}">
                        <td class="ps-3">
                            <div class="d-flex align-items-center gap-2">
                                <span class="rm-gic rm-g-{{ $m->group }}"><i class="fa-solid {{ $groupIcons[$m->group] ?? 'fa-cube' }}"></i></span>
                                <div class="min-w-0">
                                    <div class="fw-bold text-dark">{{ $m->name }}</div>
                                    <div class="text-muted" style="font-size: 11.5px;">{{ $m->spec ? $m->spec . ' · ' : '' }}{{ $m->groupLabel() }} · {{ $m->unit }}@if($t['last_at']) · শেষ {{ \Carbon\Carbon::parse($t['last_at'])->format('d M') }}@endif</div>
                                </div>
                            </div>
                        </td>
                        <td class="text-end font-monospace small">{{ $q($t['in_qty']) }}<div class="text-muted" style="font-size: 10.5px;">{{ $tk($t['in_amount']) }}</div></td>
                        <td class="text-end font-monospace small">{{ $m->isStocked() ? $q($t['out_qty']) : '—' }}@if($t['adj_qty'] != 0)<div class="text-muted" style="font-size: 10.5px;" title="সমন্বয়">± {{ $q($t['adj_qty']) }}</div>@endif</td>
                        <td class="text-end">
                            @if($m->isStocked())
                                <div class="fw-bold font-monospace {{ $t['balance'] <= 0 ? 'text-danger' : '' }}">{{ $q($t['balance']) }} <span class="text-muted fw-normal small">{{ $m->unit }}</span></div>
                                <div class="rm-level ms-auto" title="{{ $re > 0 ? 'রি-অর্ডার লেভেল ' . $q($re) : '' }}"><span class="rm-level--{{ $m->state }}" style="width: {{ round($fill, 1) }}%"></span></div>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-end font-monospace small">{{ $tk($t['avg_cost']) }}</td>
                        <td class="text-end font-monospace fw-semibold">{{ $m->isStocked() ? $tk($t['value']) : '—' }}</td>
                        <td class="text-center"><span class="badge rounded-pill {{ $stateBadge[$m->state][0] }}">{{ $stateBadge[$m->state][1] }}</span></td>
                        <td class="text-end pe-3 text-nowrap">
                            @if($m->isStocked())
                                <button type="button" class="btn btn-sm btn-info text-white rounded-pill px-2.5 fw-semibold" data-rm-move="out" data-m='@json($payload)' title="বই / কাজে ব্যবহার"><i class="fa-solid fa-dolly"></i><span class="d-none d-xl-inline ms-1">ব্যবহার</span></button>
                                <button type="button" class="btn btn-sm btn-light border rounded-pill px-2" data-rm-move="adjust" data-m='@json($payload)' title="সমন্বয় (গণনা / নষ্ট)"><i class="fa-solid fa-scale-balanced"></i></button>
                            @endif
                            <button type="button" class="btn btn-sm btn-light border rounded-pill px-2" data-rm-ledger="{{ route('admin.purchases.raw.ledger', $m->id) }}" data-name="{{ $m->label() }}" title="লেজার"><i class="fa-solid fa-book-open"></i></button>
                            <button type="button" class="btn btn-sm btn-light border rounded-pill px-2" data-rm-edit data-m='@json($payload)' title="সম্পাদনা"><i class="fa-solid fa-pen"></i></button>
                        </td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot class="rm-foot small">
                    <tr class="fw-bold">
                        <td class="ps-3">মোট ({{ $materials->count() }})</td>
                        <td class="text-end font-monospace">{{ $tk($materials->sum(fn ($m) => $m->t['in_amount'])) }}</td>
                        <td class="text-end font-monospace">{{ $tk($materials->sum(fn ($m) => $m->t['out_amount'])) }}</td>
                        <td></td><td></td>
                        <td class="text-end font-monospace">{{ $tk($materials->sum(fn ($m) => $m->isStocked() ? $m->t['value'] : 0)) }}</td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @endif
</div>
</div>

{{-- use / adjust / opening --}}
<div class="modal fade" id="rmMoveModal" tabindex="-1" aria-labelledby="rmMoveTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content rounded-4 border-0" method="POST" action="{{ route('admin.purchases.raw.moves.store') }}" id="rmMoveForm">
            @csrf
            <input type="hidden" name="raw_material_id">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-bold" id="rmMoveTitle">ব্যবহার</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="rm-mhead mb-3">
                    <div class="fw-bold" data-f="name"></div>
                    <div class="small text-muted">মজুদ: <strong class="font-monospace" data-f="balance"></strong> · গড় দর <span class="font-monospace" data-f="avg"></span></div>
                </div>
                <div class="btn-group w-100 mb-3" role="group" aria-label="ধরন">
                    <input type="radio" class="btn-check" name="type" value="out" id="rmTypeOut" checked><label class="btn btn-outline-info" for="rmTypeOut"><i class="fa-solid fa-dolly me-1"></i>ব্যবহার</label>
                    <input type="radio" class="btn-check" name="type" value="adjust" id="rmTypeAdj"><label class="btn btn-outline-secondary" for="rmTypeAdj"><i class="fa-solid fa-scale-balanced me-1"></i>সমন্বয়</label>
                    <input type="radio" class="btn-check" name="type" value="opening" id="rmTypeOpen"><label class="btn btn-outline-success" for="rmTypeOpen"><i class="fa-solid fa-flag me-1"></i>শুরুর মজুদ</label>
                </div>
                <div class="row g-2">
                    <div class="col-12 rm-only-adjust">
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="direction" value="minus" id="rmDirMinus" checked><label class="btn btn-sm btn-outline-danger" for="rmDirMinus">− কমেছে (নষ্ট / গণনায় কম)</label>
                            <input type="radio" class="btn-check" name="direction" value="plus" id="rmDirPlus"><label class="btn btn-sm btn-outline-success" for="rmDirPlus">+ বেশি পাওয়া গেছে</label>
                        </div>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-semibold">পরিমাণ (<span data-f="unit"></span>)</label>
                        <input type="number" name="qty" step="any" min="0.001" class="form-control font-monospace" required>
                        <div class="form-text" data-f="value"></div>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-semibold">তারিখ</label>
                        <input type="date" name="moved_at" class="form-control" value="{{ now()->toDateString() }}" max="{{ now()->addDay()->toDateString() }}" required>
                    </div>
                    <div class="col-12 rm-only-rate">
                        <label class="form-label small fw-semibold">প্রতি এককের দর (৳)</label>
                        <input type="number" name="unit_cost" step="0.01" min="0" class="form-control font-monospace" placeholder="ফাঁকা রাখলে গড় দর">
                    </div>
                    <div class="col-12 rm-only-out">
                        <label class="form-label small fw-semibold">কোন বই / কাজে</label>
                        <input type="text" name="job" class="form-control" list="rmBooks" maxlength="250" placeholder="বইয়ের নাম বা কাজের বিবরণ">
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold">নোট</label>
                        <input type="text" name="note" class="form-control" maxlength="500" placeholder="ঐচ্ছিক">
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">বাতিল</button>
                <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold"><i class="fa-solid fa-check me-1"></i> সংরক্ষণ</button>
            </div>
        </form>
    </div>
</div>
<datalist id="rmBooks">@foreach($books as $b)<option value="{{ $b }}">@endforeach</datalist>

{{-- add / edit material --}}
<div class="modal fade" id="rmMatModal" tabindex="-1" aria-labelledby="rmMatTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content rounded-4 border-0" method="POST" action="{{ route('admin.purchases.raw.store') }}" id="rmMatForm" data-store="{{ route('admin.purchases.raw.store') }}" data-update="{{ route('admin.purchases.raw.update', '__ID__') }}">
            @csrf
            <input type="hidden" name="_method" value="POST">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-bold" id="rmMatTitle">নতুন উপকরণ</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-2">
                    <div class="col-12">
                        <label class="form-label small fw-semibold">নাম</label>
                        <input type="text" name="name" class="form-control" maxlength="200" required placeholder="যেমন: অফসেট কাগজ">
                    </div>
                    <div class="col-7">
                        <label class="form-label small fw-semibold">মাপ / মান</label>
                        <input type="text" name="spec" class="form-control" maxlength="200" placeholder="যেমন: 23x36, 80 GSM">
                    </div>
                    <div class="col-5">
                        <label class="form-label small fw-semibold">একক</label>
                        <input type="text" name="unit" class="form-control" list="rmUnits" maxlength="30" required value="রিম">
                        <datalist id="rmUnits">@foreach(\App\Models\RawMaterial::UNITS as $u)<option value="{{ $u }}">@endforeach</datalist>
                    </div>
                    <div class="col-7">
                        <label class="form-label small fw-semibold">ধরন</label>
                        <select name="group" class="form-select">
                            @foreach($groups as $gk => [$gl])<option value="{{ $gk }}">{{ $gl }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-5">
                        <label class="form-label small fw-semibold">রি-অর্ডার লেভেল</label>
                        <input type="number" name="reorder_level" step="any" min="0" class="form-control font-monospace" placeholder="0">
                    </div>
                    <div class="col-12 rm-only-new">
                        <div class="rm-opening">
                            <div class="small fw-semibold mb-1"><i class="fa-solid fa-flag text-success me-1"></i> শুরুর মজুদ (ঐচ্ছিক)</div>
                            <div class="row g-2">
                                <div class="col-4"><input type="number" name="opening_qty" step="any" min="0" class="form-control form-control-sm font-monospace" placeholder="পরিমাণ" aria-label="শুরুর পরিমাণ"></div>
                                <div class="col-4"><input type="number" name="opening_rate" step="0.01" min="0" class="form-control form-control-sm font-monospace" placeholder="দর ৳" aria-label="দর"></div>
                                <div class="col-4"><input type="date" name="opening_date" class="form-control form-control-sm" value="{{ now()->toDateString() }}" aria-label="তারিখ"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold">নোট</label>
                        <textarea name="notes" rows="2" class="form-control" maxlength="1000"></textarea>
                    </div>
                    <div class="col-12 rm-only-edit">
                        <input type="hidden" name="is_active" value="0">
                        <label class="form-check form-switch"><input class="form-check-input" type="checkbox" name="is_active" value="1" checked> <span class="form-check-label small">সক্রিয় (তালিকায় দেখাবে)</span></label>
                        <div class="form-text">নাম বদলালেও আগের নামে আসা নতুন ক্রয় এই উপকরণেই যোগ হবে।</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">বাতিল</button>
                <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold"><i class="fa-solid fa-check me-1"></i> সংরক্ষণ</button>
            </div>
        </form>
    </div>
</div>

{{-- ledger --}}
<div class="modal fade" id="rmLedgerModal" tabindex="-1" aria-labelledby="rmLedgerTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable modal-fullscreen-md-down">
        <div class="modal-content rounded-4 border-0">
            <div class="modal-header">
                <h6 class="modal-title fw-bold" id="rmLedgerTitle">লেজার</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0" id="rmLedgerBody"></div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.rm-card { background: #fff; border-radius: 16px; box-shadow: 0 1px 3px rgba(15,23,42,.08); padding: 16px; }
.rm-kpi { display: flex; align-items: center; gap: 12px; background: #fff; border-radius: 16px; padding: 14px 16px; box-shadow: 0 1px 3px rgba(15,23,42,.08); height: 100%; color: inherit; transition: transform .15s, box-shadow .15s; }
.rm-kpi:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(15,23,42,.08); }
.rm-kpi--alert { outline: 2px solid #fecaca; }
.rm-kpi__ic { width: 42px; height: 42px; border-radius: 12px; display: grid; place-items: center; font-size: 18px; flex-shrink: 0; }
.rm-kpi__lb { font-size: 12px; color: #64748b; font-weight: 600; }
.rm-kpi__v { font-size: 1.2rem; font-weight: 800; color: #0f172a; line-height: 1.2; }
.rm-dot { display: inline-block; width: 9px; height: 9px; border-radius: 3px; vertical-align: middle; }
.rm-bars { display: flex; align-items: flex-end; gap: 10px; height: 150px; padding-top: 6px; }
.rm-bars__col { flex: 1; display: flex; flex-direction: column; height: 100%; }
.rm-bars__pair { flex: 1; display: flex; align-items: flex-end; justify-content: center; gap: 4px; border-bottom: 1px solid #e2e8f0; }
.rm-bars__b { display: block; width: 40%; max-width: 22px; min-height: 2px; border-radius: 5px 5px 0 0; transition: height .4s; }
.rm-bars__lb { text-align: center; font-size: 11px; color: #64748b; margin-top: 4px; }
.rm-recent-list { max-height: 260px; overflow-y: auto; padding-right: 4px; }
.rm-recent { display: flex; align-items: center; gap: 10px; padding: 7px 0; border-bottom: 1px dashed #e2e8f0; }
.rm-recent:last-child { border-bottom: 0; }
.rm-recent__t { font-size: 10.5px; font-weight: 700; padding: 2px 8px; border-radius: 20px; flex-shrink: 0; }
.rm-t-in { background: #fef3c7; color: #92400e; } .rm-t-out { background: #cffafe; color: #155e75; }
.rm-t-adjust { background: #e2e8f0; color: #334155; } .rm-t-opening { background: #dcfce7; color: #166534; }
.rm-chips .btn { font-size: 12.5px; }
.rm-gic { width: 34px; height: 34px; border-radius: 10px; display: grid; place-items: center; flex-shrink: 0; background: #f1f5f9; color: #475569; }
.rm-g-paper { background: #eff6ff; color: #1d4ed8; } .rm-g-board { background: #f5f3ff; color: #6d28d9; } .rm-g-plate { background: #f0fdfa; color: #0f766e; }
.rm-g-ink { background: #fdf2f8; color: #be185d; } .rm-g-binding { background: #fff7ed; color: #c2410c; } .rm-g-lamination { background: #ecfeff; color: #0e7490; }
.rm-g-packaging { background: #fefce8; color: #a16207; } .rm-g-service { background: #f8fafc; color: #64748b; }
.rm-level { width: 90px; height: 5px; background: #f1f5f9; border-radius: 4px; overflow: hidden; margin-top: 4px; }
.rm-level span { display: block; height: 100%; border-radius: 4px; }
.rm-level--in { background: #22c55e; } .rm-level--low { background: #f59e0b; } .rm-level--out { background: #ef4444; }
.rm-foot td { background: #f8fafc; border-top: 2px solid #e2e8f0; }
.rm-empty { width: 72px; height: 72px; border-radius: 50%; background: #f1f5f9; display: inline-grid; place-items: center; font-size: 28px; color: #94a3b8; }
.rm-mhead { background: #f8fafc; border-radius: 12px; padding: 10px 12px; }
.rm-opening { background: #f0fdf4; border: 1px dashed #bbf7d0; border-radius: 12px; padding: 10px; }
.min-w-0 { min-width: 0; }
@media (max-width: 767.98px) {
    .rm-table thead { display: none; }
    .rm-table, .rm-table tbody, .rm-table tr, .rm-table td { display: block; width: 100%; }
    .rm-table tr { border-bottom: 1px solid #e2e8f0; padding: 10px 12px; display: grid; grid-template-columns: 1fr 1fr; gap: 4px 10px; }
    .rm-table td { border: 0; padding: 0 !important; text-align: left !important; }
    .rm-table td:first-child, .rm-table td:last-child { grid-column: 1 / -1; }
    .rm-table td:last-child { margin-top: 6px; }
    .rm-table td[data-l]::before { content: attr(data-l); display: block; font-size: 10.5px; color: #94a3b8; font-weight: 600; }
    .rm-level { margin-left: 0 !important; }
    .rm-table tfoot tr { display: none; }
    .rm-bars { height: 120px; gap: 6px; }
}
</style>
@endpush

@push('scripts')
<script>
(function () {
    var fmt = function (v) { return (Math.round(v * 1000) / 1000).toLocaleString('en-US', { maximumFractionDigits: 3 }); };
    var tk = function (v) { return '৳' + (+v || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); };
    var modal = function (id) { return bootstrap.Modal.getOrCreateInstance(document.getElementById(id)); };

    // mobile: label every cell from the header
    var heads = Array.prototype.map.call(document.querySelectorAll('#rmTable thead th'), function (th) { return th.textContent.trim(); });
    document.querySelectorAll('#rmTable tbody tr').forEach(function (tr) {
        Array.prototype.forEach.call(tr.children, function (td, i) { if (i > 0 && i < heads.length - 1) td.setAttribute('data-l', heads[i]); });
    });

    // live search in the list
    var search = document.getElementById('rmSearch');
    if (search) search.addEventListener('input', function () {
        var term = search.value.trim().toLowerCase();
        document.querySelectorAll('#rmTable tbody tr').forEach(function (tr) {
            tr.style.display = !term || (tr.getAttribute('data-search') || '').indexOf(term) !== -1 ? '' : 'none';
        });
    });

    // use / adjust / opening
    var mf = document.getElementById('rmMoveForm'), cur = null;
    function paintMove() {
        if (!cur) return;
        var type = mf.querySelector('[name=type]:checked').value;
        var dir = mf.querySelector('[name=direction]:checked').value;
        mf.querySelectorAll('.rm-only-out').forEach(function (el) { el.style.display = type === 'out' ? '' : 'none'; });
        mf.querySelectorAll('.rm-only-adjust').forEach(function (el) { el.style.display = type === 'adjust' ? '' : 'none'; });
        mf.querySelectorAll('.rm-only-rate').forEach(function (el) { el.style.display = type === 'opening' || (type === 'adjust' && dir === 'plus') ? '' : 'none'; });
        var qty = parseFloat(mf.qty.value) || 0;
        var minus = type === 'out' || (type === 'adjust' && dir === 'minus');
        mf.qty.max = minus ? cur.balance : '';
        var rate = (type === 'opening' || (type === 'adjust' && dir === 'plus')) && mf.unit_cost.value !== '' ? parseFloat(mf.unit_cost.value) : cur.avg;
        var after = minus ? cur.balance - qty : cur.balance + qty;
        mf.querySelector('[data-f=value]').innerHTML = qty > 0 ? 'মূল্য ' + tk(qty * rate) + ' · পরে মজুদ <b class="' + (after < 0 ? 'text-danger' : '') + '">' + fmt(after) + '</b>' : '';
        document.getElementById('rmMoveTitle').textContent = { out: 'বই / কাজে ব্যবহার', adjust: 'মজুদ সমন্বয়', opening: 'শুরুর মজুদ' }[type];
    }
    document.querySelectorAll('[data-rm-move]').forEach(function (b) {
        b.addEventListener('click', function () {
            cur = JSON.parse(b.getAttribute('data-m'));
            mf.reset();
            mf.raw_material_id.value = cur.id;
            mf.querySelector('[name=type][value=' + b.getAttribute('data-rm-move') + ']').checked = true;
            mf.querySelector('[data-f=name]').textContent = cur.name + (cur.spec ? ' — ' + cur.spec : '');
            mf.querySelector('[data-f=balance]').textContent = fmt(cur.balance) + ' ' + cur.unit;
            mf.querySelector('[data-f=avg]').textContent = tk(cur.avg);
            mf.querySelector('[data-f=unit]').textContent = cur.unit;
            paintMove();
            modal('rmMoveModal').show();
            setTimeout(function () { mf.qty.focus(); }, 300);
        });
    });
    if (mf) mf.addEventListener('input', paintMove);
    if (mf) mf.addEventListener('change', paintMove);

    // add / edit material
    var af = document.getElementById('rmMatForm');
    function openMat(m) {
        af.reset();
        var edit = !!m;
        af.action = edit ? af.getAttribute('data-update').replace('__ID__', m.id) : af.getAttribute('data-store');
        af.querySelector('[name=_method]').value = edit ? 'PUT' : 'POST';
        document.getElementById('rmMatTitle').textContent = edit ? 'উপকরণ সম্পাদনা' : 'নতুন উপকরণ';
        af.querySelectorAll('.rm-only-new').forEach(function (el) { el.style.display = edit ? 'none' : ''; });
        af.querySelectorAll('.rm-only-edit').forEach(function (el) { el.style.display = edit ? '' : 'none'; });
        if (edit) {
            var el = function (n) { return af.elements.namedItem(n); };
            el('name').value = m.name || ''; el('spec').value = m.spec || ''; el('unit').value = m.unit || '';
            el('group').value = m.group; el('reorder_level').value = m.reorder_level || ''; el('notes').value = m.notes || '';
            af.querySelector('[name=is_active][type=checkbox]').checked = !!m.is_active;
        }
        modal('rmMatModal').show();
    }
    document.querySelectorAll('[data-rm-new]').forEach(function (b) { b.addEventListener('click', function () { openMat(null); }); });
    document.querySelectorAll('[data-rm-edit]').forEach(function (b) { b.addEventListener('click', function () { openMat(JSON.parse(b.getAttribute('data-m'))); }); });

    // ledger (loaded on demand, period filter stays inside the modal)
    var body = document.getElementById('rmLedgerBody');
    function loadLedger(url) {
        body.innerHTML = '<div class="text-center text-muted py-5"><span class="spinner-border spinner-border-sm me-2"></span> লোড হচ্ছে…</div>';
        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
            .then(function (r) { if (!r.ok) throw new Error(r.status); return r.text(); })
            .then(function (h) { body.innerHTML = h; })
            .catch(function () { body.innerHTML = '<div class="alert alert-danger m-3">লেজার লোড করা যায়নি।</div>'; });
    }
    document.querySelectorAll('[data-rm-ledger]').forEach(function (b) {
        b.addEventListener('click', function () {
            document.getElementById('rmLedgerTitle').textContent = 'লেজার — ' + b.getAttribute('data-name');
            loadLedger(b.getAttribute('data-rm-ledger'));
            modal('rmLedgerModal').show();
        });
    });
    body.addEventListener('submit', function (e) {
        var f = e.target.closest('form[data-ledger-filter]');
        if (!f) return;
        e.preventDefault();
        loadLedger(f.action + '?' + new URLSearchParams(new FormData(f)).toString());
    });
})();
</script>
@endpush
