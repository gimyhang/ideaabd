@extends('layouts.admin')

@php
    $types       = \App\Models\Product::TYPES;
    $currentType = old('type', $selectedType ?? $product->type ?? 'electronics');
    if (!isset($types[$currentType])) { $currentType = 'electronics'; }
    $rawSpecs    = is_array($product->specifications) ? $product->specifications : [];
    $schema      = \App\Models\Product::SPEC_FIELDS;

    // Labels already covered by the type-specific inputs; everything else is a custom spec row
    $knownLabels = collect($schema[$currentType])->map(fn ($f) => $f[0])
        ->merge(collect(\App\Models\Product::extraFields($currentType))->map(fn ($f) => $f[0]))->unique()->all();
    $customSpecs = [];
    if (is_array(old('spec_keys'))) {
        foreach (old('spec_keys') as $i => $k) {
            if ($k !== null && $k !== '') { $customSpecs[$k] = old('spec_vals')[$i] ?? ''; }
        }
    } else {
        foreach ($rawSpecs as $k => $v) {
            if (!in_array($k, $knownLabels, true)) { $customSpecs[$k] = $v; }
        }
    }
    $allPresets = [
        'electronics' => ['Processor', 'RAM', 'Storage', 'Display', 'Connectivity', 'Weight'],
        'stationery'  => ['Material', 'Weight', 'Quantity', 'Origin'],
        'appliance'   => ['Frequency', 'Cord length', 'Programs', 'Speed settings', 'Material', 'Safety', 'Origin', 'Package'],
    ];
    $presets = $allPresets[$currentType] ?? [];
    $coverUrl = $product->cover_image ? $product->image_url : null;
@endphp

@section('title', $isEdit ? 'Edit product' : 'New product')
@section('heading', ($isEdit ? 'Edit ' : 'New ') . $types[$currentType])
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.products.index') }}">Products</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.products.' . $currentType) }}">{{ $types[$currentType] }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $isEdit ? 'Edit' : 'New' }}</li>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin-products.css') }}?v={{ @filemtime(public_path('css/admin-products.css')) ?: 1 }}">
@endpush

@section('actions')
    <a href="{{ route('admin.products.' . $currentType) }}" class="btn btn-outline-secondary pr-btn">Back</a>
@endsection

@section('content')
<form action="{{ $isEdit ? route('admin.products.update', $product->id) : route('admin.products.store') }}"
      method="POST" enctype="multipart/form-data" id="productEntryForm" novalidate>
    @csrf
    @if($isEdit) @method('PUT') @endif
    <input type="hidden" name="type" id="hiddenProductType" value="{{ $currentType }}">

    <div class="pf-wrap">
        {{-- ───────── Main column ───────── --}}
        <div>
            {{-- Family + category (drives which spec inputs appear) --}}
            <section class="pf-card">
                <h2>{{ $types[$currentType] }} · Category</h2>
                <div class="pf-cats" role="radiogroup" aria-label="Category">
                    @foreach($allCategories as $cat)
                        <label class="pf-cat">
                            <input type="radio" name="category_id" value="{{ $cat->id }}" data-name-text="{{ $cat->name }}" @checked((string) old('category_id', $product->category_id) === (string) $cat->id)>
                            <span>{{ $cat->name }}</span>
                        </label>
                    @endforeach
                    <label class="pf-cat">
                        <input type="radio" name="category_id" value="" data-name-text="" @checked(!old('category_id', $product->category_id))>
                        <span>Other</span>
                    </label>
                </div>
                @error('category_id')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
                <div class="pf-hint mt-2">Pick a category to get its matching spec fields.</div>
            </section>

            {{-- Basics --}}
            <section class="pf-card">
                <h2>Basics</h2>
                <div class="pf-field mb-3">
                    <label for="fTitle">Name *</label>
                    <input type="text" name="title" id="fTitle" value="{{ old('title', $product->title) }}" class="form-control @error('title') is-invalid @enderror" placeholder="e.g. Philips Air Fryer 4.1L" required>
                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="pf-grid pf-grid--2 mb-3">
                    <div class="pf-field">
                        <label for="fBrand">Brand</label>
                        <input type="text" name="brand" id="fBrand" value="{{ old('brand', $product->brand) }}" class="form-control" list="brandList" placeholder="e.g. Walton">
                        <datalist id="brandList">
                            @foreach(['Walton','Samsung','LG','Philips','Panasonic','Sharp','Singer','Miyako','Vision','Jamuna','Xiaomi','Havells','Prestige','Faber-Castell','Pentel','Dollar'] as $b)<option value="{{ $b }}">@endforeach
                        </datalist>
                    </div>
                    <div class="pf-field">
                        <label for="fModel">Model</label>
                        <input type="text" name="model" id="fModel" value="{{ old('model', $product->model) }}" class="form-control" placeholder="e.g. HD9200">
                    </div>
                </div>
                <div class="pf-grid pf-grid--2 mb-3">
                    <div class="pf-field">
                        <label for="fSku">SKU <small>(auto if empty)</small></label>
                        <input type="text" name="sku" id="fSku" value="{{ old('sku', $product->sku) }}" class="form-control font-monospace @error('sku') is-invalid @enderror">
                        @error('sku')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="pf-field">
                        <label for="fSlug">Slug <small>(auto if empty)</small></label>
                        <input type="text" name="slug" id="fSlug" value="{{ old('slug', $product->slug) }}" class="form-control font-monospace @error('slug') is-invalid @enderror">
                        @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="pf-field mb-3">
                    <label for="fSummary">Summary</label>
                    <textarea name="summary" id="fSummary" rows="2" maxlength="1000" class="form-control" placeholder="One or two lines of highlights">{{ old('summary', $product->summary) }}</textarea>
                </div>
                <div class="pf-field">
                    <label for="fDesc">Description</label>
                    <textarea name="description" id="fDesc" rows="5" class="form-control" placeholder="Full details, usage, what's in the box">{{ old('description', $product->description) }}</textarea>
                </div>
            </section>

            {{-- Family specs (always shown for this family) --}}
            <section class="pf-card">
                <h2>Specs</h2>
                <div class="pf-grid">
                    @foreach($schema[$currentType] as $field => [$label, $placeholder])
                        <div class="pf-field">
                            <label for="spec_{{ $field }}">{{ $label }}</label>
                            <input type="text" id="spec_{{ $field }}" name="spec_{{ $field }}"
                                   value="{{ old('spec_' . $field, $rawSpecs[$label] ?? '') }}" class="form-control" placeholder="{{ $placeholder }}">
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- Category-specific specs (shown when the chosen category matches) --}}
            @foreach(\App\Models\Product::CATEGORY_SPECS[$currentType] ?? [] as $keywords => $fields)
                <section class="pf-card pf-extra" data-keywords="{{ $keywords }}" hidden>
                    <h2><span class="pf-extra-name">Category</span> specs</h2>
                    <div class="pf-grid">
                        @foreach($fields as $field => [$label, $placeholder])
                            <div class="pf-field">
                                <label>{{ $label }}</label>
                                <input type="text" data-name="spec_x_{{ $field }}" value="{{ old('spec_x_' . $field, $rawSpecs[$label] ?? '') }}" class="form-control" placeholder="{{ $placeholder }}">
                            </div>
                        @endforeach
                    </div>
                </section>
            @endforeach
            {{-- Custom specs --}}
            <section class="pf-card">
                <h2>More specs</h2>
                <div class="pf-quick" id="presetPills"></div>
                <div class="pf-rows" id="specRows">
                    @foreach($customSpecs as $k => $v)
                        <div class="pf-row">
                            <input type="text" name="spec_keys[]" value="{{ $k }}" class="form-control" placeholder="Name">
                            <input type="text" name="spec_vals[]" value="{{ $v }}" class="form-control" placeholder="Value">
                            <button type="button" class="pf-del" aria-label="Remove">&times;</button>
                        </div>
                    @endforeach
                </div>
                <button type="button" class="btn btn-outline-primary pr-act mt-2" id="addSpecBtn">Add row</button>
            </section>
        </div>

        {{-- ───────── Side column ───────── --}}
        <aside class="pf-side">
            <section class="pf-card">
                <h2>Price</h2>
                <div class="pf-field mb-3">
                    <label for="fPrice">Price (৳) *</label>
                    <input type="number" step="0.01" min="0" name="price" id="fPrice" value="{{ old('price', $product->price) }}" class="form-control fw-bold @error('price') is-invalid @enderror" inputmode="decimal" required>
                    @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="pf-field mb-2">
                    <label for="fDisc">Offer price (৳)</label>
                    <input type="number" step="0.01" min="0" name="discount_price" id="fDisc" value="{{ old('discount_price', $product->discount_price) }}" class="form-control @error('discount_price') is-invalid @enderror" inputmode="decimal">
                    @error('discount_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="small fw-bold text-success" id="discHint"></div>
            </section>

            <section class="pf-card">
                <h2>Stock</h2>
                <div class="pf-grid pf-grid--2">
                    <div class="pf-field">
                        <label for="fStock">Qty *</label>
                        <input type="number" min="0" name="stock" id="fStock" value="{{ old('stock', $product->stock ?? 50) }}" class="form-control" inputmode="numeric" required>
                    </div>
                    <div class="pf-field">
                        <label for="fStockStatus">Status</label>
                        <select name="stock_status" id="fStockStatus" class="form-select">
                            @foreach(['in_stock' => 'In stock', 'out_of_stock' => 'Out', 'pre_order' => 'Pre-order'] as $v => $l)
                                <option value="{{ $v }}" @selected(old('stock_status', $product->stock_status ?? 'in_stock') === $v)>{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </section>

            <section class="pf-card">
                <h2>Details</h2>
                <div class="pf-field mb-3">
                    <label for="fWarranty">Warranty</label>
                    <input type="text" name="warranty" id="fWarranty" value="{{ old('warranty', $product->warranty) }}" class="form-control" placeholder="e.g. 2 years service">
                </div>
                <div class="pf-grid pf-grid--2 mb-3">
                    <div class="pf-field">
                        <label for="fBadge">Badge</label>
                        <input type="text" name="badge" id="fBadge" value="{{ old('badge', $product->badge) }}" class="form-control" maxlength="50" placeholder="New, Hot">
                    </div>
                    <div class="pf-field">
                        <label for="fSort">Order</label>
                        <input type="number" name="sort_order" id="fSort" value="{{ old('sort_order', $product->sort_order ?? 0) }}" class="form-control" inputmode="numeric">
                    </div>
                </div>
                <div class="form-check form-switch mb-2">
                    <input type="hidden" name="is_active" value="0">
                    <input class="form-check-input" type="checkbox" role="switch" name="is_active" value="1" id="fActive" @checked(old('is_active', $product->is_active ?? true))>
                    <label class="form-check-label fw-semibold" for="fActive">Active</label>
                </div>
                <div class="form-check form-switch">
                    <input type="hidden" name="is_featured" value="0">
                    <input class="form-check-input" type="checkbox" role="switch" name="is_featured" value="1" id="fFeatured" @checked(old('is_featured', $product->is_featured))>
                    <label class="form-check-label fw-semibold" for="fFeatured">Featured</label>
                </div>
            </section>

            <section class="pf-card">
                <h2>Image</h2>
                <div class="pf-preview mb-2" id="imgPreview">
                    @if($coverUrl)<img src="{{ $coverUrl }}" alt="">@else<span>No image</span>@endif
                </div>
                <input type="file" name="cover_image_file" id="fFile" class="form-control mb-2" accept="image/*">
                <input type="url" name="cover_image_url" id="fUrl" value="{{ old('cover_image_url', str_starts_with($product->cover_image ?? '', 'http') ? $product->cover_image : '') }}" class="form-control" placeholder="…or paste image URL">
            </section>

            <div class="pf-savebar">
                <a href="{{ route('admin.products.' . $currentType) }}" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Update' : 'Save' }}</button>
            </div>
        </aside>
    </div>
</form>
@endsection

@push('scripts')
<script>
(function () {
    var presets = @json($presets);
    var cats = document.querySelectorAll('input[name="category_id"]');
    var extras = document.querySelectorAll('.pf-extra');
    var rows = document.getElementById('specRows');

    // Show only the spec group whose keywords match the chosen category; only visible inputs are submitted
    function matches(group, name) {
        name = (name || '').toLowerCase();
        return name && group.dataset.keywords.split(',').some(function (k) { return name.indexOf(k.trim()) !== -1; });
    }
    function syncExtras() {
        var sel = document.querySelector('input[name="category_id"]:checked');
        var name = sel ? sel.dataset.nameText : '';
        extras.forEach(function (g) {
            var on = matches(g, name);
            g.hidden = !on;
            var title = g.querySelector('.pf-extra-name'); if (title && on) title.textContent = name;
            g.querySelectorAll('input[data-name]').forEach(function (i) {
                if (on) i.name = i.dataset.name; else i.removeAttribute('name');
            });
        });
    }
    cats.forEach(function (c) { c.addEventListener('change', syncExtras); });
    syncExtras();

    // Custom spec rows
    function addRow(k, v) {
        var d = document.createElement('div'); d.className = 'pf-row';
        d.innerHTML = '<input type="text" name="spec_keys[]" class="form-control" placeholder="Name">' +
            '<input type="text" name="spec_vals[]" class="form-control" placeholder="Value">' +
            '<button type="button" class="pf-del" aria-label="Remove">&times;</button>';
        d.children[0].value = k || ''; d.children[1].value = v || '';
        rows.appendChild(d); (k ? d.children[1] : d.children[0]).focus();
    }
    document.getElementById('addSpecBtn').addEventListener('click', function () { addRow(); });
    rows.addEventListener('click', function (e) { var b = e.target.closest('.pf-del'); if (b) b.parentNode.remove(); });
    var box = document.getElementById('presetPills');
    presets.forEach(function (name) {
        var b = document.createElement('button'); b.type = 'button'; b.textContent = '+ ' + name;
        b.addEventListener('click', function () { addRow(name, ''); });
        box.appendChild(b);
    });

    // Offer-price hint + live image preview
    var price = document.getElementById('fPrice'), disc = document.getElementById('fDisc'), hint = document.getElementById('discHint');
    function calc() {
        var p = parseFloat(price.value), d = parseFloat(disc.value);
        if (p > 0 && d > 0 && d < p) { hint.textContent = Math.round((p - d) / p * 100) + '% off · saves ৳' + (p - d).toFixed(0); hint.className = 'small fw-bold text-success'; }
        else if (d >= p && d > 0) { hint.textContent = 'Offer must be lower than price'; hint.className = 'small fw-bold text-danger'; }
        else hint.textContent = '';
    }
    price.addEventListener('input', calc); disc.addEventListener('input', calc); calc();

    var prev = document.getElementById('imgPreview');
    function showImg(src) { prev.innerHTML = src ? '<img alt="" src="' + src.replace(/"/g, '&quot;') + '">' : '<span>No image</span>'; }
    document.getElementById('fFile').addEventListener('change', function () {
        var f = this.files && this.files[0]; if (!f) return;
        var r = new FileReader(); r.onload = function (e) { showImg(e.target.result); }; r.readAsDataURL(f);
    });
    document.getElementById('fUrl').addEventListener('change', function () { if (this.value) showImg(this.value); });
})();</script>
@endpush
