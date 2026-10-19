@extends('layouts.admin')

@php
    $types = \App\Models\Product::TYPES;
    $typeLabel = $types[$type] ?? ucfirst($type);
    $typeTone = ['electronics' => 'info', 'stationery' => 'success', 'appliance' => 'warning'];
    $typeAddLabel = ['electronics' => 'Add', 'stationery' => 'Add', 'appliance' => 'Add'][$type] ?? 'Add';
@endphp

@section('title', $typeLabel)
@section('heading', $typeLabel)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.products.index') }}">Products</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $typeLabel }}</li>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin-products.css') }}?v={{ @filemtime(public_path('css/admin-products.css')) ?: 1 }}">
@endpush

@section('actions')
    <div class="pr-actions">
        <a href="{{ route('admin.products.create', ['type' => $type]) }}" class="btn btn-primary pr-btn">{{ $typeAddLabel }}</a>
        <button type="button" class="btn btn-outline-secondary pr-btn" data-bs-toggle="modal" data-bs-target="#categoryManageModal">Categories</button>
        <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary pr-btn">Overview</a>
    </div>
@endsection

@section('content')
@php
    $base = route('admin.products.' . $type);
    $kpis = [
        ['Total',      $counts['total'],      $base,                              !request()->hasAny(['status', 'stock', 'category_id']), 'primary'],
        ['Active',     $counts['active'],     $base . '?status=active',           request('status') === 'active', 'success'],
        ['Low',        $counts['low_stock'],  $base . '?stock=low',               request('stock') === 'low', 'danger'],
        ['Categories', $counts['categories'], '#',                                false, 'warning'],
    ];
    $advActive = request()->filled('status') || request()->filled('stock');
@endphp

<div class="d-flex flex-column gap-3">

    {{-- Type switch: each family has its own page --}}
    <nav class="pr-chips" aria-label="Product families">
        <a class="pr-chip" href="{{ route('admin.products.index') }}">Overview</a>
        @foreach($types as $tKey => $tLabel)
            <a class="pr-chip {{ $type === $tKey ? 'is-active' : '' }}" href="{{ route('admin.products.' . $tKey) }}">{{ $tLabel }}</a>
        @endforeach
    </nav>

    {{-- KPI strip --}}
    <div class="pr-kpis pr-kpis--4">
        @foreach($kpis as [$kLabel, $kValue, $kUrl, $kActive, $kTone])
            <a href="{{ $kUrl }}" @if($kUrl === '#') data-bs-toggle="modal" data-bs-target="#categoryManageModal" @endif class="pr-kpi pr-kpi--{{ $kTone }} {{ $kActive ? 'is-active' : '' }}">
                <span class="pr-kpi__label">{{ $kLabel }}</span>
                <span class="pr-kpi__value">{{ number_format($kValue) }}</span>
            </a>
        @endforeach
    </div>

    {{-- Toolbar --}}
    <div class="adm-card p-3">
        <form action="{{ $base }}" method="GET" id="prFilterForm" role="search" class="d-flex flex-column gap-2">
            @if(request('category_id'))<input type="hidden" name="category_id" value="{{ request('category_id') }}">@endif
            <div class="pr-searchrow">
                <div class="pr-search">
                    <input type="search" name="q" id="prSearch" value="{{ request('q') }}" class="form-control pr-search__input"
                           placeholder="Search name, brand, model, SKU…  ( / )" autocomplete="off" enterkeyhint="search" aria-label="Search {{ $typeLabel }}">
                    <button type="submit" class="btn btn-primary pr-search__go">Search</button>
                </div>
                <select name="status" class="form-select pr-tool pr-tool--select" onchange="this.form.submit()" aria-label="Status">
                    <option value="">Any status</option>
                    <option value="active" @selected(request('status') === 'active')>Active</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Hidden</option>
                </select>
                <div class="pr-view" role="group" aria-label="View">
                    <button type="button" class="pr-view__btn" data-view="grid">Grid</button>
                    <button type="button" class="pr-view__btn" data-view="table">Table</button>
                </div>
            </div>

            {{-- Category: filter within this family --}}
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <select class="form-select pr-tool pr-tool--select w-auto" aria-label="Category" onchange="window.location = this.value">
                    <option value="{{ $base . (request('q') ? '?q=' . urlencode(request('q')) : '') }}">All</option>
                    @foreach($categories as $cat)
                        <option value="{{ $base . '?category_id=' . $cat->id . (request('q') ? '&q=' . urlencode(request('q')) : '') }}" @selected((string) request('category_id') === (string) $cat->id)>{{ $cat->name }} ({{ $cat->products_count }})</option>
                    @endforeach
                </select>
                <span class="pr-count">{{ number_format($products->total()) }} items</span>
            </div>
        </form>
    </div>

    {{-- Products --}}
    @if($products->isEmpty())
        <div class="adm-card pr-empty">
            <div class="pr-empty__title">No products</div>
            <p class="text-muted mb-3">Nothing matches this view yet.</p>
            <a href="{{ route('admin.products.create', ['type' => $type]) }}" class="btn btn-primary pr-btn">Add</a>
        </div>
    @else
        @php
            $statusQ = fn ($p) => $p->stock <= 0 ? ['Out', 'danger'] : ($p->stock <= 5 ? ['Low', 'warning'] : ['In stock', 'success']);
        @endphp

        {{-- Grid view --}}
        <div class="pr-grid" id="prGrid">
            @foreach($products as $prod)
                @php [$sLabel, $sTone] = $statusQ($prod); $specs = collect($prod->specifications ?? [])->take(3); @endphp
                <article class="pr-card {{ $prod->is_active ? '' : 'is-hidden' }}">
                    <a href="{{ route('admin.products.edit', $prod->id) }}" class="pr-card__media" aria-label="Edit {{ $prod->title }}">
                        <img src="{{ $prod->image_url }}" alt="" loading="lazy" decoding="async"
                             onerror="this.onerror=null;this.src='https://placehold.co/400x400/f1f5f9/64748b?text={{ rawurlencode(mb_substr($prod->title, 0, 1)) }}';">
                        @if($prod->category)<span class="pr-tag pr-tag--{{ $typeTone[$prod->type] ?? 'info' }}">{{ $prod->category->name }}</span>@endif
                        @if($prod->discount_percent)<span class="pr-off">-{{ $prod->discount_percent }}%</span>@endif
                        @unless($prod->is_active)<span class="pr-hidden">Hidden</span>@endunless
                    </a>
                    <div class="pr-card__body">
                        <div class="pr-card__meta">{{ $prod->brand ?: ($prod->category?->name ?? '—') }}@if($prod->model) · {{ $prod->model }}@endif</div>
                        <a href="{{ route('admin.products.edit', $prod->id) }}" class="pr-card__title" title="{{ $prod->title }}">{{ $prod->title }}</a>
                        @if($specs->isNotEmpty())
                            <ul class="pr-specs">
                                @foreach($specs as $sk => $sv)
                                    <li><span>{{ $sk }}</span><b>{{ $sv }}</b></li>
                                @endforeach
                            </ul>
                        @endif
                        <div class="pr-card__price">
                            <strong>৳{{ number_format($prod->final_price, 0) }}</strong>
                            @if($prod->discount_percent)<s>৳{{ number_format($prod->price, 0) }}</s>@endif
                        </div>
                        <div class="pr-card__row">
                            <span class="pr-pill pr-pill--{{ $sTone }}">{{ $sLabel }}@if($prod->stock > 0) · {{ $prod->stock }}@endif</span>
                            @if($prod->sku)<span class="pr-sku">{{ $prod->sku }}</span>@endif
                        </div>
                    </div>
                    <div class="pr-card__foot">
                        <a href="{{ route('admin.products.edit', $prod->id) }}" class="btn btn-primary pr-act">Edit</a>
                        <form action="{{ route('admin.products.toggle-status', $prod->id) }}" method="POST" class="pr-flex">
                            @csrf
                            <button type="submit" class="btn btn-outline-secondary pr-act w-100">{{ $prod->is_active ? 'Hide' : 'Show' }}</button>
                        </form>
                        <form action="{{ route('admin.products.destroy', $prod->id) }}" method="POST" data-confirm="এই পণ্যটি স্থায়ীভাবে মুছে ফেলবেন?">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger pr-act">Delete</button>
                        </form>
                    </div>
                </article>
            @endforeach
        </div>

        {{-- Table view --}}
        <div class="adm-card p-0 overflow-hidden d-none" id="prTableWrap">
            <div class="table-responsive">
                <table class="table align-middle mb-0 pr-table">
                    <thead>
                        <tr>
                            <th class="ps-3">Product</th>
                            <th>Category</th>
                            <th class="text-end">Price</th>
                            <th class="text-center">Stock</th>
                            <th class="text-center">Status</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $prod)
                            @php [$sLabel, $sTone] = $statusQ($prod); @endphp
                            <tr>
                                <td class="ps-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="{{ $prod->image_url }}" alt="" width="46" height="46" loading="lazy" class="pr-thumb"
                                             onerror="this.onerror=null;this.src='https://placehold.co/100x100/f1f5f9/64748b?text=%20';">
                                        <div class="min-w-0">
                                            <a href="{{ route('admin.products.edit', $prod->id) }}" class="pr-t-title">{{ $prod->title }}</a>
                                            <div class="pr-t-sub">{{ $prod->brand }}@if($prod->brand && $prod->sku) · @endif{{ $prod->sku }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-muted">{{ $prod->category?->name ?? '—' }}</td>
                                <td class="text-end">
                                    <strong>৳{{ number_format($prod->final_price, 0) }}</strong>
                                    @if($prod->discount_percent)<div class="pr-t-sub"><s>৳{{ number_format($prod->price, 0) }}</s> -{{ $prod->discount_percent }}%</div>@endif
                                </td>
                                <td class="text-center"><span class="pr-pill pr-pill--{{ $sTone }}">{{ $sLabel }}@if($prod->stock > 0) · {{ $prod->stock }}@endif</span></td>
                                <td class="text-center">
                                    <form action="{{ route('admin.products.toggle-status', $prod->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="pr-pill pr-pill--{{ $prod->is_active ? 'success' : 'muted' }} pr-pill--btn" title="Toggle">{{ $prod->is_active ? 'Active' : 'Hidden' }}</button>
                                    </form>
                                </td>
                                <td class="text-end pe-3">
                                    <div class="d-inline-flex gap-1">
                                        <a href="{{ route('admin.products.edit', $prod->id) }}" class="btn btn-primary pr-act">Edit</a>
                                        <form action="{{ route('admin.products.destroy', $prod->id) }}" method="POST" data-confirm="এই পণ্যটি স্থায়ীভাবে মুছে ফেলবেন?">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger pr-act">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @if($products->hasPages())
            <div class="d-flex justify-content-center">{{ $products->links() }}</div>
        @endif
    @endif
</div>

{{-- Categories modal --}}
<div class="modal fade" id="categoryManageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content rounded-4 border-0">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Categories</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('admin.products.categories.store') }}" method="POST" class="row g-2 p-3 rounded-3 bg-light border mb-3">
                    @csrf
                    <input type="hidden" name="type" value="{{ $type }}">
                    <div class="col-12 col-md-3">
                        <label class="pr-label">Family</label>
                        <div class="form-control bg-white fw-bold">{{ $typeLabel }}</div>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="pr-label">Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Air Fryer" required>
                    </div>
                    <div class="col-12 col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary pr-btn w-100">Add</button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead><tr><th>Name</th><th class="text-center">Items</th><th class="text-end">Actions</th></tr></thead>
                        <tbody>
                            @foreach($categories as $cat)
                                <tr>
                                    <td class="fw-semibold">{{ $cat->name }}</td>
                                    <td class="text-center">{{ $cat->products_count }}</td>
                                    <td class="text-end">
                                        <form action="{{ route('admin.products.categories.destroy', $cat->id) }}" method="POST" class="d-inline" data-confirm="এই ক্যাটাগরি মুছে ফেলবেন?">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger pr-act">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    // Grid / Table toggle (remembered per browser; defaults to grid on phones, table on desktop)
    var grid = document.getElementById('prGrid'), table = document.getElementById('prTableWrap');
    var btns = document.querySelectorAll('.pr-view__btn');
    function apply(v) {
        if (grid) grid.classList.toggle('d-none', v !== 'grid');
        if (table) table.classList.toggle('d-none', v !== 'table');
        btns.forEach(function (b) { b.classList.toggle('is-active', b.dataset.view === v); });
        try { localStorage.setItem('pr-view', v); } catch (e) {}
    }
    var saved = null; try { saved = localStorage.getItem('pr-view'); } catch (e) {}
    apply(saved || 'grid');
    btns.forEach(function (b) { b.addEventListener('click', function () { apply(b.dataset.view); }); });

    // Search: "/" focuses, auto-search after typing stops
    var input = document.getElementById('prSearch'), form = document.getElementById('prFilterForm');
    if (input && form) {
        var t, initial = input.value;
        input.addEventListener('input', function () {
            clearTimeout(t);
            if (input.value.trim() === initial.trim()) return;
            t = setTimeout(function () { form.submit(); }, 700);
        });
        document.addEventListener('keydown', function (e) {
            var el = e.target, typing = el && (/^(input|textarea|select)$/i.test(el.tagName) || el.isContentEditable);
            if (e.key === '/' && !typing && !e.ctrlKey && !e.metaKey) { e.preventDefault(); input.focus(); input.select(); }
        });
    }
})();
</script>
@endpush
