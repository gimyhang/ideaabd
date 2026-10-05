@extends('layouts.admin')

@section('title', $isEdit ? 'পণ্য সম্পাদনা: ' . $product->title : 'নতুন পণ্য যোগ করুন')
@section('heading', $isEdit ? 'পণ্য সম্পাদনা' : 'নতুন পণ্য সংযোজন')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">ড্যাশবোর্ড</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.products.index') }}">ইলেক্ট্রনিক্স ও স্টেশনারি</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $isEdit ? 'সম্পাদনা' : 'নতুন' }}</li>
@endsection

@section('content')
<div class="container-fluid px-0">
    <form action="{{ $isEdit ? route('admin.products.update', $product->id) : route('admin.products.store') }}" 
          method="POST" 
          enctype="multipart/form-data">
        @csrf
        @if($isEdit)
            @method('PUT')
        @endif

        <div class="row g-4">
            
            <!-- Left Main Column (Product Details) -->
            <div class="col-lg-8">
                
                <!-- 1. Basic Info Card -->
                <div class="card border-0 rounded-4 shadow-xs bg-white mb-4">
                    <div class="card-body p-4">
                        <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">সাধারণ তথ্য</h6>

                        <!-- Type Selector (Electronics vs Stationery) -->
                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark small text-uppercase tracking-wider">পণ্যের ধরন (Type) <span class="text-danger">*</span></label>
                            <div class="d-flex gap-3">
                                <div class="form-check form-check-inline p-3 border rounded-3 flex-grow-1 cursor-pointer bg-light">
                                    <input class="form-check-input" type="radio" name="type" id="type_electronics" value="electronics" 
                                           {{ old('type', $product->type ?? 'electronics') === 'electronics' ? 'checked' : '' }}
                                           onchange="filterCategoriesByType('electronics')">
                                    <label class="form-check-label fw-bold text-dark d-flex align-items-center gap-2" for="type_electronics">
                                        <i class="fa-solid fa-laptop-code text-info fa-lg"></i>
                                        <span>ইলেক্ট্রনিক্স পণ্য (Electronics)</span>
                                    </label>
                                </div>
                                <div class="form-check form-check-inline p-3 border rounded-3 flex-grow-1 cursor-pointer bg-light">
                                    <input class="form-check-input" type="radio" name="type" id="type_stationery" value="stationery" 
                                           {{ old('type', $product->type ?? 'electronics') === 'stationery' ? 'checked' : '' }}
                                           onchange="filterCategoriesByType('stationery')">
                                    <label class="form-check-label fw-bold text-dark d-flex align-items-center gap-2" for="type_stationery">
                                        <i class="fa-solid fa-pen-nib text-success fa-lg"></i>
                                        <span>স্টেশনারি পণ্য (Stationery)</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Product Title -->
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark small">পণ্যের শিরোনাম (Title) <span class="text-danger">*</span></label>
                            <input type="text" name="title" value="{{ old('title', $product->title) }}" 
                                   class="form-control @error('title') is-invalid @enderror" 
                                   placeholder="যেমন: আইডিয়া প্রো ফ্লেক্সিবল রিচার্জেবল রিডিং ল্যাম্প" required>
                            @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <!-- Slug & SKU -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark small">কাস্টম স্লাগ (Slug - খালি রাখলে অটো জেনারেট হবে)</label>
                                <input type="text" name="slug" value="{{ old('slug', $product->slug) }}" class="form-control form-control-sm font-monospace" placeholder="idea-reading-lamp">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark small">পণ্য কোড / SKU</label>
                                <input type="text" name="sku" value="{{ old('sku', $product->sku) }}" class="form-control form-control-sm font-monospace" placeholder="ELC-001">
                            </div>
                        </div>

                        <!-- Brand & Model -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark small">ব্র্যান্ড (Brand)</label>
                                <input type="text" name="brand" value="{{ old('brand', $product->brand) }}" class="form-control form-control-sm" placeholder="যেমন: Baseus, Parker, Idea">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark small">মডেল (Model)</label>
                                <input type="text" name="model" value="{{ old('model', $product->model) }}" class="form-control form-control-sm" placeholder="যেমন: Pro Edition 2026">
                            </div>
                        </div>

                        <!-- Short Summary -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark small">সংক্ষিপ্ত বিবরণ (Summary)</label>
                            <textarea name="summary" rows="2" class="form-control" placeholder="১-২ লাইনে পণ্যের মূল বৈশিষ্ট্য লিখুন...">{{ old('summary', $product->summary) }}</textarea>
                        </div>

                        <!-- Detailed Description -->
                        <div class="mb-0">
                            <label class="form-label fw-semibold text-dark small">বিস্তারিত বিবরণ (Description)</label>
                            <textarea name="description" rows="5" class="form-control" placeholder="পণ্যের পূর্ণাঙ্গ বর্ণনা ও ব্যবহারের নির্দেশিকা...">{{ old('description', $product->description) }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- 2. Specifications Repeater Card -->
                <div class="card border-0 rounded-4 shadow-xs bg-white mb-4">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between border-bottom pb-2 mb-3">
                            <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                                <i class="fa-solid fa-list-check text-primary"></i>
                                <span>টেকনিক্যাল ও ফিচার স্পেসিফিকেশন</span>
                            </h6>
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold" onclick="addSpecRow()">
                                + নতুন স্পেসিফিকেশন যোগ করুন
                            </button>
                        </div>

                        <div id="specsContainer" class="d-flex flex-column gap-2">
                            @php
                                $specs = old('specifications', $product->specifications ?? []);
                            @endphp
                            @if(is_array($specs) && count($specs) > 0)
                                @foreach($specs as $k => $v)
                                    <div class="row g-2 align-items-center spec-row">
                                        <div class="col-md-5">
                                            <input type="text" name="spec_keys[]" value="{{ $k }}" class="form-control form-control-sm" placeholder="ফিচারের নাম (যেমন: ব্যাটারি)">
                                        </div>
                                        <div class="col-md-6">
                                            <input type="text" name="spec_vals[]" value="{{ $v }}" class="form-control form-control-sm" placeholder="মান (যেমন: 2000mAh)">
                                        </div>
                                        <div class="col-md-1 text-center">
                                            <button type="button" class="btn btn-sm btn-light text-danger rounded-circle p-1" onclick="this.closest('.spec-row').remove()">
                                                <i class="fa-solid fa-xmark"></i>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="row g-2 align-items-center spec-row">
                                    <div class="col-md-5">
                                        <input type="text" name="spec_keys[]" class="form-control form-control-sm" placeholder="ফিচারের নাম (যেমন: ব্যাটারি)">
                                    </div>
                                    <div class="col-md-6">
                                        <input type="text" name="spec_vals[]" class="form-control form-control-sm" placeholder="মান (যেমন: 1800mAh)">
                                    </div>
                                    <div class="col-md-1 text-center">
                                        <button type="button" class="btn btn-sm btn-light text-danger rounded-circle p-1" onclick="this.closest('.spec-row').remove()">
                                            <i class="fa-solid fa-xmark"></i>
                                        </button>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

            </div>

            <!-- Right Column (Pricing, Stock, Media & Publish) -->
            <div class="col-lg-4">
                
                <!-- 1. Category & Publish Settings -->
                <div class="card border-0 rounded-4 shadow-xs bg-white mb-4">
                    <div class="card-body p-4">
                        <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">ক্যাটাগরি ও স্ট্যাটাস</h6>

                        <!-- Category Selection -->
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark small">ক্যাটাগরি <span class="text-danger">*</span></label>
                            <select name="category_id" id="categorySelect" class="form-select" required>
                                <option value="">ক্যাটাগরি নির্বাচন করুন</option>
                                @foreach($allCategories as $cat)
                                    <option value="{{ $cat->id }}" data-type="{{ $cat->type }}" 
                                            {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>
                                        [{{ $cat->type === 'electronics' ? 'ইলেক' : 'স্টেশ' }}] {{ $cat->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Active Toggle -->
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActiveSwitch" 
                                   {{ old('is_active', $product->is_active ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold text-dark small" for="isActiveSwitch">
                                স্টোরে সক্রিয় (Active) রাখুন
                            </label>
                        </div>

                        <!-- Featured Toggle -->
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="is_featured" value="1" id="isFeaturedSwitch" 
                                   {{ old('is_featured', $product->is_featured) ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold text-dark small" for="isFeaturedSwitch">
                                হোমপেজ ফিচার্ড প্রোডাক্ট হিসেবে দেখান
                            </label>
                        </div>

                        <!-- Badge & Warranty -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark small">হাইলাইট ব্যাজ (Badge)</label>
                            <input type="text" name="badge" value="{{ old('badge', $product->badge) }}" class="form-control form-control-sm" placeholder="যেমন: হট ডিল, বেস্টসেলার">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark small">ওয়ারেন্টি তথ্য</label>
                            <input type="text" name="warranty" value="{{ old('warranty', $product->warranty) }}" class="form-control form-control-sm" placeholder="যেমন: ৬ মাসের অফিশিয়াল ওয়ারেন্টি">
                        </div>

                        <!-- Sort Order -->
                        <div class="mb-0">
                            <label class="form-label fw-semibold text-dark small">সর্ট অর্ডার (Sort Order)</label>
                            <input type="number" name="sort_order" value="{{ old('sort_order', $product->sort_order ?? 0) }}" class="form-control form-control-sm">
                        </div>

                    </div>
                </div>

                <!-- 2. Pricing & Stock Card -->
                <div class="card border-0 rounded-4 shadow-xs bg-white mb-4">
                    <div class="card-body p-4">
                        <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">মূল্য ও স্টক</h6>

                        <!-- Regular Price -->
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark small">নিয়মিত মূল্য (টাকা) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="price" value="{{ old('price', $product->price) }}" class="form-control fw-bold" placeholder="0.00" required>
                        </div>

                        <!-- Discount Price -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark small">অফার / ছাড়কৃত মূল্য (টাকা)</label>
                            <input type="number" step="0.01" name="discount_price" value="{{ old('discount_price', $product->discount_price) }}" class="form-control" placeholder="0.00">
                        </div>

                        <!-- Stock Quantity -->
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark small">স্টক পরিমাণ <span class="text-danger">*</span></label>
                            <input type="number" name="stock" value="{{ old('stock', $product->stock ?? 50) }}" class="form-control" required>
                        </div>

                        <!-- Stock Status -->
                        <div class="mb-0">
                            <label class="form-label fw-bold text-dark small">স্টক অবস্থা</label>
                            <select name="stock_status" class="form-select form-select-sm">
                                <option value="in_stock" {{ old('stock_status', $product->stock_status) === 'in_stock' ? 'selected' : '' }}>স্টকে আছে (In Stock)</option>
                                <option value="out_of_stock" {{ old('stock_status', $product->stock_status) === 'out_of_stock' ? 'selected' : '' }}>স্টক আউট (Out of Stock)</option>
                                <option value="pre_order" {{ old('stock_status', $product->stock_status) === 'pre_order' ? 'selected' : '' }}>প্রি-অর্ডার (Pre-order)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- 3. Cover Image Card -->
                <div class="card border-0 rounded-4 shadow-xs bg-white mb-4">
                    <div class="card-body p-4">
                        <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">পণ্যের ছবি</h6>

                        @if(!empty($product->cover_image))
                            <div class="text-center mb-3 p-2 bg-light rounded-3 border">
                                <img src="{{ $product->image_url }}" alt="" class="img-fluid rounded-2" style="max-height: 180px;">
                            </div>
                        @endif

                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark small">নতুন ছবি আপলোড করুন</label>
                            <input type="file" name="cover_image_file" class="form-control form-control-sm" accept="image/*">
                        </div>

                        <div class="mb-0">
                            <label class="form-label fw-semibold text-dark small">অথবা ছবির সরাসরি URL লিংক</label>
                            <input type="url" name="cover_image_url" value="{{ old('cover_image_url', str_starts_with($product->cover_image ?? '', 'http') ? $product->cover_image : '') }}" class="form-control form-control-sm" placeholder="https://...">
                        </div>
                    </div>
                </div>

                <!-- Save Actions -->
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-lg rounded-pill fw-bold shadow-xs py-2.5">
                        <i class="fa-solid fa-cloud-arrow-up me-1"></i>
                        <span>{{ $isEdit ? 'আপডেট সংরক্ষণ করুন' : 'পণ্য প্রকাশ করুন' }}</span>
                    </button>
                    <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary rounded-pill py-2 fw-semibold">
                        বাতিল করুন
                    </a>
                </div>

            </div>

        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function filterCategoriesByType(type) {
    const select = document.getElementById('categorySelect');
    if (!select) return;
    const options = select.querySelectorAll('option');
    options.forEach(opt => {
        if (!opt.value) return; // keep default placeholder
        const optType = opt.getAttribute('data-type');
        if (optType === type) {
            opt.style.display = '';
        } else {
            opt.style.display = 'none';
            if (opt.selected) {
                opt.selected = false;
            }
        }
    });
}

function addSpecRow() {
    const container = document.getElementById('specsContainer');
    const row = document.createElement('div');
    row.className = 'row g-2 align-items-center spec-row';
    row.innerHTML = `
        <div class="col-md-5">
            <input type="text" name="spec_keys[]" class="form-control form-control-sm" placeholder="ফিচারের নাম">
        </div>
        <div class="col-md-6">
            <input type="text" name="spec_vals[]" class="form-control form-control-sm" placeholder="মান">
        </div>
        <div class="col-md-1 text-center">
            <button type="button" class="btn btn-sm btn-light text-danger rounded-circle p-1" onclick="this.closest('.spec-row').remove()">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    `;
    container.appendChild(row);
}

document.addEventListener('DOMContentLoaded', function() {
    const checkedRadio = document.querySelector('input[name="type"]:checked');
    if (checkedRadio) {
        filterCategoriesByType(checkedRadio.value);
    }
});
</script>
@endpush
