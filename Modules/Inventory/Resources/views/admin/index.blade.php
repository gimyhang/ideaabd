@extends('layouts.admin')

@push('styles')
@include('inventory::admin._nav')
@endpush

@use('App\Services\BookProductionService', 'BPS')

@section('title', 'বইয়ের জীবনচক্র ও স্টক')
@section('heading', 'বইয়ের জীবনচক্র ও স্টক')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Stock</li>
@endsection

@section('actions')
    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#lifeModal" data-life-mode="printed"><i class="fa-solid fa-plus me-1"></i>নতুন এন্ট্রি</button>
    <div class="dropdown">
        <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">আরও</button>
        <ul class="dropdown-menu dropdown-menu-end">
            <li><button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#stockInModal"><i class="fa-solid fa-box-open me-2"></i>সরাসরি স্টক ইন</button></li>
            <li><a class="dropdown-item" href="{{ route('admin.production.index') }}"><i class="fa-solid fa-print me-2"></i>প্রোডাকশন খাতা</a></li>
            <li><a class="dropdown-item" href="{{ route('admin.inventory.wastages') }}"><i class="fa-solid fa-triangle-exclamation me-2"></i>নষ্টের খাতা</a></li>
            @if ($stats['unallocated'] > 0)
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="POST" action="{{ route('admin.inventory.opening-balance') }}" onsubmit="return confirm('লোকেশন ছাড়া স্টক কেন্দ্রীয় গুদামে বসানো হবে। চালিয়ে যাবেন?')">
                        @csrf
                        <button class="dropdown-item"><i class="fa-solid fa-warehouse me-2"></i>লোকেশন ছাড়া স্টক গুদামে বসান ({{ BPS::bn($stats['unallocated']) }})</button>
                    </form>
                </li>
            @endif
        </ul>
    </div>
@endsection

@section('content')
@include('inventory::admin._tabs')

@php
    $stageUrl = fn ($s) => route('admin.inventory.index', array_filter(['stage' => $s, 'q' => request('q'), 'category_id' => request('category_id')]));
    $sortUrl = fn ($col) => request()->fullUrlWithQuery(['sort' => $col, 'dir' => request('sort') === $col && request('dir') !== 'desc' ? 'desc' : 'asc', 'page' => null]);
    $flow = [
        ['key' => 'printed', 'icon' => 'print', 'label' => 'ছাপা হয়েছে', 'value' => $stats['printed'], 'tone' => 'ink',
         'note' => 'প্রোডাকশন খাতা অনুযায়ী মোট', 'href' => route('admin.production.index')],
        ['key' => 'press', 'icon' => 'layer-group', 'label' => 'প্রেসে · বাঁধাই বাকি', 'value' => $stats['at_press'], 'tone' => 'amber',
         'note' => $stats['press_titles'] ? BPS::bn($stats['press_titles']) . ' টাইটেল · সবচেয়ে পুরনো ' . BPS::age($stats['press_oldest']) : 'কিছু বাকি নেই', 'href' => $stageUrl('press')],
        ['key' => 'binder', 'icon' => 'book', 'label' => 'বাঁধাইখানায়', 'value' => $stats['at_binder'], 'tone' => 'violet',
         'note' => $stats['binder_titles'] ? BPS::bn($stats['binder_titles']) . ' টাইটেল · সবচেয়ে পুরনো ' . BPS::age($stats['binder_oldest']) : 'কিছু পড়ে নেই', 'href' => $stageUrl('binder')],
        ['key' => 'store', 'icon' => 'warehouse', 'label' => 'স্টোরে আছে', 'value' => $stats['catalogue'], 'tone' => 'green',
         'note' => BPS::bn($stats['locations']) . ' লোকেশন · ' . BPS::bn($stats['low']) . ' জায়গায় স্টক কম', 'href' => $stageUrl('low')],
        ['key' => 'sold', 'icon' => 'cart-shopping', 'label' => 'বিক্রি / বিতরণ', 'value' => $stats['sold'], 'tone' => 'blue',
         'note' => BPS::bn($stats['soldout']) . ' টাইটেল সম্পূর্ণ শেষ', 'href' => $stageUrl('soldout')],
    ];
@endphp

{{-- Pipeline: from the press to the reader --}}
<section class="lc-flow" aria-label="বইয়ের জীবনচক্র">
    @foreach ($flow as $i => $f)
        <a href="{{ $f['href'] }}" class="lc-node is-{{ $f['tone'] }} {{ ($stage === $f['key'] || ($f['key'] === 'store' && $stage === 'low') || ($f['key'] === 'sold' && $stage === 'soldout')) ? 'is-active' : '' }}">
            <span class="lc-node-icon"><i class="fa-solid fa-{{ $f['icon'] }}"></i></span>
            <span class="lc-node-label">{{ $f['label'] }}</span>
            <strong class="lc-node-value">{{ BPS::bn($f['value']) }}</strong>
            <span class="lc-node-note">{{ $f['note'] }}</span>
        </a>
        @if (!$loop->last)<span class="lc-arrow" aria-hidden="true"><i class="fa-solid fa-chevron-right"></i></span>@endif
    @endforeach
</section>

<a href="{{ route('admin.inventory.wastages') }}" class="lc-waste">
    <span class="lc-waste-title"><i class="fa-solid fa-triangle-exclamation"></i> মোট নষ্ট <b>{{ BPS::bn($stats['waste']) }}</b> কপি</span>
    <span>প্রেসে <b>{{ BPS::bn($stats['press_waste']) }}</b></span>
    <span>বাঁধাইয়ে <b>{{ BPS::bn($stats['binder_waste']) }}</b></span>
    <span>স্টোরে <b>{{ BPS::bn($stats['store_waste']) }}</b></span>
    @if ($stats['printed'] > 0)<span class="lc-waste-rate">ছাপার {{ BPS::bn(round(($stats['press_waste'] + $stats['binder_waste']) / $stats['printed'] * 100, 1), 1) }}%</span>@endif
</a>

<section class="card lc-card">
    <form method="GET" class="lc-toolbar" id="ivFilter" data-export="{{ route('admin.inventory.export') }}">
        <div class="lc-search">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="search" name="q" value="{{ $search }}" class="form-control" placeholder="বই, লেখক, ISBN বা SKU খুঁজুন" aria-label="খুঁজুন">
        </div>
        <select name="category_id" class="form-select lc-cat" aria-label="ক্যাটাগরি">
            <option value="">সব ক্যাটাগরি</option>
            @foreach ($categories as $c)<option value="{{ $c->id }}" @selected(request('category_id') == $c->id)>{{ $c->name }}</option>@endforeach
        </select>
        <div class="lc-pills" role="radiogroup" aria-label="অবস্থা">
            <label class="lc-pill"><input type="radio" name="stage" value="" @checked($stage === '')><span>সব</span></label>
            @foreach ($stageFilters as $key => $label)
                <label class="lc-pill"><input type="radio" name="stage" value="{{ $key }}" @checked($stage === $key)><span>{{ $label }}</span></label>
            @endforeach
        </div>
        <div class="lc-tools">
            <span class="iv-livebar" id="ivLiveBar"><span class="iv-dot"></span><span id="ivUpdated">লাইভ</span></span>
            <a href="{{ route('admin.inventory.export', request()->query()) }}" class="btn btn-outline-secondary btn-sm" id="ivExport"><i class="fa-solid fa-file-csv me-1"></i>CSV</a>
        </div>
    </form>

    <div id="ivLive">
        <div class="table-responsive">
            <table class="table align-middle lc-table">
                <thead>
                    <tr>
                        <th><a href="{{ $sortUrl('title') }}" class="iv-sort">বই</a></th>
                        <th class="lc-col-bar">জীবনচক্র</th>
                        <th class="text-end">ছাপা</th>
                        <th class="text-end">প্রেসে</th>
                        <th class="text-end">বাঁধাইখানায়</th>
                        <th class="text-end"><a href="{{ $sortUrl('stock_quantity') }}" class="iv-sort">স্টোরে</a></th>
                        <th class="text-end">বিক্রি</th>
                        <th class="text-end">নষ্ট</th>
                        <th class="text-end"><span class="visually-hidden">কাজ</span></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($books as $book)
                        @php
                            $l = $life[$book->id] ?? null;
                            $inStore = (int) $book->stock_quantity;
                            $printed = $l->printed ?? 0;
                            $press = $l->at_press ?? 0;
                            $binder = $l->at_binder ?? 0;
                            $sold = $l->sold ?? 0;
                            $waste = $l->waste ?? 0;
                            $total = max(1, $sold + max(0, $inStore) + $binder + $press + $waste);
                            $pct = fn ($v) => round(max(0, $v) / $total * 100, 2);
                            $byLoc = $book->inventories->where('quantity', '!=', 0)->keyBy('location_id');
                            $lowHere = $book->inventories->first(fn ($i) => $i->quantity <= $i->min_alert_quantity);
                            $status = match (true) {
                                $inStore <= 0 && ($press + $binder) > 0 => ['বাঁধাই করে আনুন', 'is-amber'],
                                $inStore <= 0 && $sold > 0               => ['বিক্রি শেষ', 'is-blue'],
                                $inStore <= 0                             => ['স্টক নেই', 'is-red'],
                                (bool) $lowHere                           => ['স্টক কম', 'is-red'],
                                default                                   => null,
                            };
                        @endphp
                        <tr>
                            <td class="lc-book">
                                <a href="{{ route('admin.inventory.book', $book->id) }}" class="lc-title">{{ $book->title }}</a>
                                <div class="lc-meta">
                                    @if ($book->author_name)<span>{{ $book->author_name }}</span>@endif
                                    @if ($book->category)<span>{{ $book->category->name }}</span>@endif
                                    @if ($status)<span class="lc-tag {{ $status[1] }}">{{ $status[0] }}</span>@endif
                                </div>
                            </td>
                            <td class="lc-col-bar">
                                @if ($l)
                                    <div class="lc-bar" role="img" aria-label="বিক্রি {{ $sold }}, স্টোরে {{ $inStore }}, বাঁধাইখানায় {{ $binder }}, প্রেসে {{ $press }}, নষ্ট {{ $waste }}">
                                        <i class="is-sold" style="width:{{ $pct($sold) }}%"></i><i class="is-store" style="width:{{ $pct($inStore) }}%"></i><i class="is-binder" style="width:{{ $pct($binder) }}%"></i><i class="is-press" style="width:{{ $pct($press) }}%"></i><i class="is-waste" style="width:{{ $pct($waste) }}%"></i>
                                    </div>
                                    <div class="lc-bar-cap">বিক্রি {{ BPS::bn($l->sell_through, $l->sell_through == floor($l->sell_through) ? 0 : 1) }}%</div>
                                @else
                                    <span class="lc-dim">প্রোডাকশন এন্ট্রি নেই</span>
                                @endif
                            </td>
                            <td class="text-end lc-num">{{ $printed ? BPS::bn($printed) : '—' }}</td>
                            <td class="text-end lc-num">
                                @if ($press)
                                    <span class="lc-chip is-amber">{{ BPS::bn($press) }}</span>
                                    <div class="lc-age {{ BPS::ageTone($l->press_since) }}" title="{{ $l->press_since?->format('d M Y') }} থেকে">{{ BPS::age($l->press_since) }}</div>
                                @else<span class="lc-dim">০</span>@endif
                            </td>
                            <td class="text-end lc-num">
                                @if ($binder)
                                    <span class="lc-chip is-violet">{{ BPS::bn($binder) }}</span>
                                    <div class="lc-age {{ BPS::ageTone($l->binder_since) }}" title="{{ $l->binder_since?->format('d M Y') }} থেকে">{{ BPS::age($l->binder_since) }}</div>
                                @else<span class="lc-dim">০</span>@endif
                            </td>
                            <td class="text-end lc-num">
                                <strong class="{{ $inStore <= 0 ? 'text-danger' : '' }}">{{ BPS::bn($inStore) }}</strong>
                                @if ($byLoc->isNotEmpty())
                                    <div class="lc-locs">
                                        @foreach ($locations as $loc)
                                            @if ($inv = $byLoc[$loc->id] ?? null)
                                                <span class="{{ $inv->quantity <= $inv->min_alert_quantity ? 'is-low' : '' }}" title="{{ $loc->name }}">{{ $loc->code }} {{ BPS::bn($inv->quantity) }}</span>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td class="text-end lc-num">{{ $sold ? BPS::bn($sold) : '—' }}</td>
                            <td class="text-end lc-num">@if ($waste)<span class="text-danger">{{ BPS::bn($waste) }}</span>@else<span class="lc-dim">০</span>@endif</td>
                            <td class="text-end">
                                <div class="lc-actions">
                                    <button type="button" class="btn btn-sm btn-light" title="এন্ট্রি" aria-label="এন্ট্রি" data-bs-toggle="modal" data-bs-target="#lifeModal"
                                            data-life-mode="{{ $binder ? 'received' : ($press ? 'bound' : 'printed') }}" data-life-book="{{ $book->id }}" data-life-title="{{ $book->title }}"
                                            data-life-press="{{ $press }}" data-life-binder="{{ $binder }}" data-life-stock="{{ $inStore }}"><i class="fa-solid fa-plus"></i></button>
                                    <a href="{{ route('admin.inventory.book', $book->id) }}" class="btn btn-sm btn-light" title="খাতা" aria-label="খাতা"><i class="fa-solid fa-arrow-right"></i></a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="lc-empty"><i class="fa-regular fa-folder-open"></i><div>এই ফিল্টারে কোনো বই নেই</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="lc-legend">
            <span><i class="is-sold"></i>বিক্রি</span><span><i class="is-store"></i>স্টোরে</span><span><i class="is-binder"></i>বাঁধাইখানায়</span><span><i class="is-press"></i>প্রেসে</span><span><i class="is-waste"></i>নষ্ট</span>
            <span class="ms-auto lc-dim">{{ BPS::bn($books->total()) }} টাইটেল</span>
        </div>
        @if ($books->hasPages())<div class="card-footer">{{ $books->links() }}</div>@endif
    </div>
</section>

@include('inventory::admin._entry')

<div class="modal fade" id="stockInModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('admin.inventory.stock-in') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <div>
                    <h5 class="modal-title">সরাসরি স্টক ইন</h5>
                    <div class="lc-sub">প্রোডাকশন খাতার বাইরে আসা কপি (পুরনো স্টক, ক্রয় ইত্যাদি)</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-12">@include('inventory::admin._picker', ['name' => 'book_id', 'label' => 'বই', 'locationSelector' => '#stockInLocation'])</div>
                    <div class="col-6">
                        <label class="form-label">লোকেশন</label>
                        <select name="location_id" id="stockInLocation" class="form-select">
                            @foreach ($locations->where('type', 'warehouse') as $loc)<option value="{{ $loc->id }}" @selected($loc->is_central)>{{ $loc->name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-6"><label class="form-label">সংখ্যা</label><input type="number" name="quantity" min="1" class="form-control" required></div>
                    <div class="col-6"><label class="form-label">ব্যাচ</label><input type="text" name="batch_no" maxlength="100" class="form-control"></div>
                    <div class="col-6"><label class="form-label">নোট</label><input type="text" name="notes" maxlength="1000" class="form-control"></div>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">বাতিল</button><button class="btn btn-primary">সংরক্ষণ</button></div>
        </form>
    </div>
</div>
@endsection
