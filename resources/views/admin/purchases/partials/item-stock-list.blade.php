{{-- Stock block shared by /items-stock and the purchase forms. Swapped in by purchases-items.js --}}
@if ($dupes->isNotEmpty())
    <div class="pi-dupes" role="alert">
        <strong>{{ $dupes->count() }} duplicate {{ \Illuminate\Support\Str::plural('group', $dupes->count()) }} found</strong>
        <span class="pi-sub">Same name in the same type. Merging adds the stock together and keeps the history.</span>
        @foreach ($dupes as $group)
            @php $keep = $group->first(); @endphp
            <div class="pi-dupe">
                <div class="pi-dupe-items">
                    @foreach ($group as $d)
                        <span class="pi-dupe-chip {{ $d->id === $keep->id ? 'is-keep' : '' }}">{{ $d->title }} · {{ $d->sku ?: '#' . $d->id }} · stock {{ $d->stock }} · {{ $d->used }} purchases{{ $d->id === $keep->id ? ' · keep' : '' }}</span>
                    @endforeach
                </div>
                <button type="button" class="btn btn-warning btn-sm pi-merge" data-keep="{{ $keep->id }}" data-ids="{{ $group->skip(1)->pluck('id')->implode(',') }}">Merge</button>
            </div>
        @endforeach
    </div>
@endif

<div class="pi-kpis pi-kpis-5">
    @foreach ([
        ['Items', number_format($kpis['items']), ''],
        ['Units', number_format($kpis['units']), ''],
        ['Low', number_format($kpis['low']), $kpis['low'] > 0 ? 'is-bad' : ''],
        ['Out', number_format($kpis['out']), $kpis['out'] > 0 ? 'is-bad' : ''],
        ['Value', '৳' . number_format($kpis['value'], 0), ''],
    ] as $k)
        <article class="card pi-kpi {{ $k[2] }}"><span>{{ $k[0] }}</span><strong>{{ $k[1] }}</strong></article>
    @endforeach
</div>

{{-- Category menu --}}
<nav class="pi-tabs" aria-label="Categories">
    @php $active = (string) request('category_id', ''); @endphp
    <button type="button" class="pi-tab {{ $active === '' ? 'is-active' : '' }}" data-cat="">All <b>{{ $kpis['items'] }}</b></button>
    @foreach ($tabs as $t)
        <button type="button" class="pi-tab {{ $active === $t['key'] ? 'is-active' : '' }}" data-cat="{{ $t['key'] }}" title="{{ number_format($t['units']) }} units">
            @if (!$type && $t['type'])<i class="pi-tab-type is-{{ $t['type'] }}"></i>@endif{{ $t['name'] }} <b>{{ $t['items'] }}</b>
        </button>
    @endforeach
</nav>

<div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead><tr><th>Item</th><th>SKU</th><th class="text-end">Stock</th><th class="text-end">Purchased</th><th class="text-end">Price</th><th>Last</th>@if ($withBuy)<th></th>@endif</tr></thead>
        <tbody>
            @php $cols = $withBuy ? 7 : 6; $lastType = null; $lastCat = null; @endphp
            @forelse ($products as $p)
                @php $catName = $p->category?->name ?: 'Uncategorized'; @endphp
                @if (!$type && $p->type !== $lastType)
                    @php $lastType = $p->type; $lastCat = null; $typeRows = $products->where('type', $p->type); @endphp
                    <tr class="pi-type-row"><td colspan="{{ $cols }}"><span class="pi-tag is-{{ $p->type }}">{{ $types[$p->type] ?? ucfirst($p->type) }}</span> <span class="pi-sub">{{ $typeRows->count() }} items · {{ number_format($typeRows->sum('stock')) }} units</span></td></tr>
                @endif
                @if ($catName !== $lastCat)
                    @php
                        $lastCat = $catName;
                        $catRows = $products->filter(fn ($x) => $x->type === $p->type && ($x->category?->name ?: 'Uncategorized') === $catName);
                    @endphp
                    <tr class="pi-cat-row"><td colspan="{{ $cols }}"><strong>{{ $catName }}</strong> <span class="pi-sub">{{ $catRows->count() }} items · {{ number_format($catRows->sum('stock')) }} units · ৳{{ number_format($catRows->sum(fn ($x) => $x->stock * $x->price), 0) }}</span></td></tr>
                @endif
                <tr>
                    <td class="fw-semibold">{{ $p->title }}@if ($p->brand)<div class="pi-sub ms-0">{{ $p->brand }}</div>@endif</td>
                    <td class="text-muted">{{ $p->sku ?: '—' }}</td>
                    <td class="text-end"><span class="pi-qty-chip {{ $p->stock <= 0 ? 'is-out' : ($p->stock <= 5 ? 'is-low' : '') }}">{{ number_format($p->stock) }}</span></td>
                    <td class="text-end">{{ number_format($p->purchased_qty) }}</td>
                    <td class="text-end">৳{{ number_format($p->price, 2) }}</td>
                    <td class="text-muted text-nowrap">{{ $p->last_at ? \Illuminate\Support\Carbon::parse($p->last_at)->format('d M Y') : '—' }}</td>
                    @if ($withBuy)
                        <td class="text-end"><button type="button" class="btn btn-outline-primary btn-sm pi-buy" data-id="{{ $p->id }}" data-title="{{ $p->title }}" data-price="{{ $p->price }}" data-stock="{{ $p->stock }}">+ Buy</button></td>
                    @endif
                </tr>
            @empty
                <tr><td colspan="{{ $cols }}" class="pi-empty">None</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@if ($products->hasPages())<div class="card-footer">{{ $products->links() }}</div>@endif
