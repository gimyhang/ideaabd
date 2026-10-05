@extends('layouts.admin')

@section('title', 'ইলেক্ট্রনিক্স ও স্টেশনারি পণ্য ব্যবস্থাপনা')
@section('heading', 'ইলেক্ট্রনিক্স ও স্টেশনারি ক্যাটালগ')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">ড্যাশবোর্ড</a></li>
    <li class="breadcrumb-item active" aria-current="page">ইলেক্ট্রনিক্স ও স্টেশনারি</li>
@endsection

@section('actions')
    <div class="d-flex align-items-center gap-2">
        <button type="button" class="btn btn-outline-secondary rounded-pill px-3 py-2 shadow-xs fw-semibold d-inline-flex align-items-center gap-1.5" data-bs-toggle="modal" data-bs-target="#categoryManageModal">
            <i class="fa-solid fa-folder-tree text-primary"></i>
            <span>ক্যাটাগরি ম্যানেজমেন্ট</span>
        </button>
        <a href="{{ route('admin.products.create', ['type' => $type !== 'all' ? $type : 'electronics']) }}" class="btn btn-primary rounded-pill px-3.5 py-2 shadow-sm fw-bold d-inline-flex align-items-center gap-2">
            <i class="fa-solid fa-circle-plus"></i>
            <span>নতুন পণ্য যোগ করুন</span>
        </a>
    </div>
@endsection

@section('content')
<div class="container-fluid px-0">

    <!-- ═══ METRIC CARDS ═══ -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 rounded-4 shadow-xs p-3.5 bg-white">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold">সর্বমোট পণ্য</div>
                        <h3 class="fw-bold text-dark mb-0 mt-1">{{ $counts['total'] }}</h3>
                    </div>
                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-3">
                        <i class="fa-solid fa-boxes-stacked fa-xl"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 rounded-4 shadow-xs p-3.5 bg-white">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold">ইলেক্ট্রনিক্স আইটেম</div>
                        <h3 class="fw-bold text-primary mb-0 mt-1">{{ $counts['electronics'] }}</h3>
                    </div>
                    <div class="rounded-circle bg-info bg-opacity-10 text-info p-3">
                        <i class="fa-solid fa-laptop-code fa-xl"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 rounded-4 shadow-xs p-3.5 bg-white">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold">স্টেশনারি আইটেম</div>
                        <h3 class="fw-bold text-success mb-0 mt-1">{{ $counts['stationery'] }}</h3>
                    </div>
                    <div class="rounded-circle bg-success bg-opacity-10 text-success p-3">
                        <i class="fa-solid fa-pen-nib fa-xl"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 rounded-4 shadow-xs p-3.5 bg-white">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold">মোট ক্যাটাগরি</div>
                        <h3 class="fw-bold text-warning mb-0 mt-1">{{ $counts['categories'] }}</h3>
                    </div>
                    <div class="rounded-circle bg-warning bg-opacity-10 text-warning p-3">
                        <i class="fa-solid fa-tags fa-xl"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══ FILTER & TABS CARD ═══ -->
    <div class="card border-0 rounded-4 shadow-xs bg-white mb-4">
        <div class="card-body p-3 p-md-4">
            
            <!-- Type Tabs -->
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 border-bottom pb-3 mb-3">
                <ul class="nav nav-pills gap-1">
                    <li class="nav-item">
                        <a class="nav-link rounded-pill fw-bold px-3 py-1.5 {{ $type === 'all' ? 'active' : '' }}" 
                           href="{{ route('admin.products.index', ['type' => 'all']) }}">
                            সব পণ্য ({{ $counts['total'] }})
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link rounded-pill fw-bold px-3 py-1.5 {{ $type === 'electronics' ? 'active' : '' }}" 
                           href="{{ route('admin.products.index', ['type' => 'electronics']) }}">
                            <i class="fa-solid fa-laptop-code me-1"></i>ইলেক্ট্রনিক্স ({{ $counts['electronics'] }})
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link rounded-pill fw-bold px-3 py-1.5 {{ $type === 'stationery' ? 'active' : '' }}" 
                           href="{{ route('admin.products.index', ['type' => 'stationery']) }}">
                            <i class="fa-solid fa-pen-nib me-1"></i>স্টেশনারি ({{ $counts['stationery'] }})
                        </a>
                    </li>
                </ul>

                <!-- Public Store Quick Links -->
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('products.electronics') }}" target="_blank" class="btn btn-sm btn-outline-info rounded-pill fw-semibold">
                        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i>ইলেক্ট্রনিক্স পেজ
                    </a>
                    <a href="{{ route('products.stationery') }}" target="_blank" class="btn btn-sm btn-outline-success rounded-pill fw-semibold">
                        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i>স্টেশনারি পেজ
                    </a>
                </div>
            </div>

            <!-- Search & Filter Form -->
            <form action="{{ route('admin.products.index') }}" method="GET" class="row g-2 align-items-center">
                <input type="hidden" name="type" value="{{ $type }}">
                <div class="col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                        <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm border-start-0" placeholder="নাম, SKU বা ব্র্যান্ড খুঁজুন...">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="category_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">সকল ক্যাটাগরি</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                                [{{ $cat->type === 'electronics' ? 'ইলেক' : 'স্টেশ' }}] {{ $cat->name }} ({{ $cat->products_count }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">সকল স্ট্যাটাস</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>সক্রিয়</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>নিষ্ক্রিয়</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3 fw-bold flex-grow-1">ফিল্টার</button>
                    @if(request()->anyFilled(['q', 'category_id', 'status']))
                        <a href="{{ route('admin.products.index', ['type' => $type]) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">রিসেট</a>
                    @endif
                </div>
            </form>

        </div>
    </div>

    <!-- ═══ PRODUCTS TABLE ═══ -->
    <div class="card border-0 rounded-4 shadow-xs bg-white overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 13.5px;">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" style="width: 70px;">ছবি</th>
                        <th>পণ্যের নাম ও বিবরণ</th>
                        <th>টাইপ ও ক্যাটাগরি</th>
                        <th>মূল্য ও ছাড়</th>
                        <th>স্টক</th>
                        <th>স্ট্যাটাস</th>
                        <th class="text-end pe-3" style="width: 140px;">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $prod)
                        <tr>
                            <!-- Image Thumbnail -->
                            <td class="ps-3">
                                <div class="rounded-3 border overflow-hidden bg-light" style="width: 50px; height: 50px;">
                                    <img src="{{ $prod->image_url }}" alt="" class="w-100 h-100 object-fit-cover"
                                         onerror="this.onerror=null; this.src='https://placehold.co/100x100/f8fafc/0284c7?text=Idea';">
                                </div>
                            </td>

                            <!-- Title & Info -->
                            <td>
                                <div class="fw-bold text-dark mb-0.5">
                                    <a href="{{ route('products.show', ['type' => $prod->type, 'slug' => $prod->slug]) }}" target="_blank" class="text-dark text-decoration-none hover-primary">
                                        {{ $prod->title }}
                                    </a>
                                </div>
                                <div class="d-flex align-items-center gap-2 text-muted small" style="font-size: 11.5px;">
                                    @if($prod->sku)
                                        <span>SKU: <strong class="font-monospace text-secondary">{{ $prod->sku }}</strong></span>
                                    @endif
                                    @if($prod->brand)
                                        <span>• {{ $prod->brand }}</span>
                                    @endif
                                    @if($prod->is_featured)
                                        <span class="badge bg-warning bg-opacity-25 text-warning-emphasis rounded-pill px-1.5">ফিচার্ড</span>
                                    @endif
                                </div>
                            </td>

                            <!-- Type & Category -->
                            <td>
                                <div class="mb-1">
                                    @if($prod->type === 'electronics')
                                        <span class="badge bg-info bg-opacity-15 text-info-emphasis rounded-pill px-2 py-0.5 fw-semibold" style="font-size: 11px;">
                                            <i class="fa-solid fa-laptop-code me-1"></i>ইলেক্ট্রনিক্স
                                        </span>
                                    @else
                                        <span class="badge bg-success bg-opacity-15 text-success-emphasis rounded-pill px-2 py-0.5 fw-semibold" style="font-size: 11px;">
                                            <i class="fa-solid fa-pen-nib me-1"></i>স্টেশনারি
                                        </span>
                                    @endif
                                </div>
                                <span class="text-muted small">{{ $prod->category?->name ?? '—' }}</span>
                            </td>

                            <!-- Price -->
                            <td>
                                <div class="fw-bold text-primary">৳{{ number_format($prod->final_price, 0) }}</div>
                                @if($prod->discount_price && $prod->discount_price < $prod->price)
                                    <div class="text-muted small text-decoration-line-through" style="font-size: 11px;">
                                        ৳{{ number_format($prod->price, 0) }} (-{{ $prod->discount_percent }}%)
                                    </div>
                                @endif
                            </td>

                            <!-- Stock -->
                            <td>
                                @if($prod->stock > 0)
                                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2 py-1 fw-semibold">
                                        {{ $prod->stock }} টি স্টকে
                                    </span>
                                @else
                                    <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-2 py-1 fw-semibold">
                                        স্টক আউট
                                    </span>
                                @endif
                            </td>

                            <!-- Active Status Toggle -->
                            <td>
                                <form action="{{ route('admin.products.toggle-status', $prod->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm p-0 border-0 bg-transparent" title="ক্লিক করে স্ট্যাটাস পরিবর্তন করুন">
                                        @if($prod->is_active)
                                            <span class="badge bg-success rounded-pill px-2.5 py-1">সক্রিয়</span>
                                        @else
                                            <span class="badge bg-secondary rounded-pill px-2.5 py-1">নিষ্ক্রিয়</span>
                                        @endif
                                    </button>
                                </form>
                            </td>

                            <!-- Actions -->
                            <td class="text-end pe-3">
                                <div class="d-inline-flex gap-1">
                                    <a href="{{ route('admin.products.edit', $prod->id) }}" class="btn btn-sm btn-light rounded-circle shadow-2xs" style="width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center;" title="সম্পাদনা">
                                        <i class="fa-solid fa-pen text-secondary" style="font-size: 12px;"></i>
                                    </a>
                                    <form action="{{ route('admin.products.destroy', $prod->id) }}" method="POST" class="d-inline" onsubmit="return confirm('আপনি কি নিশ্চিতভাবে এই পণ্যটি মুছে ফেলতে চান?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-light rounded-circle shadow-2xs text-danger" style="width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center;" title="মুছুন">
                                            <i class="fa-solid fa-trash-can" style="font-size: 12px;"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-boxes-stacked fa-3x opacity-25 mb-2 d-block"></i>
                                কোনো পণ্য পাওয়া যায়নি।
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($products->hasPages())
            <div class="card-footer bg-white border-top py-3 d-flex justify-content-center">
                {{ $products->links() }}
            </div>
        @endif
    </div>

</div>

<!-- ═══ CATEGORIES MANAGEMENT MODAL ═══ -->
<div class="modal fade" id="categoryManageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow-2xl">
            <div class="modal-header border-bottom py-2.5 px-3.5 bg-light">
                <h6 class="modal-title fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                    <i class="fa-solid fa-folder-tree text-primary"></i>
                    <span>ইলেক্ট্রনিক্স ও স্টেশনারি ক্যাটাগরি ব্যবস্থাপনা</span>
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3.5">
                
                <!-- Add New Category Form -->
                <div class="p-3 rounded-4 bg-light border mb-4">
                    <h6 class="fw-bold text-dark mb-2.5" style="font-size: 13.5px;">+ নতুন ক্যাটাগরি তৈরি করুন</h6>
                    <form action="{{ route('admin.products.categories.store') }}" method="POST" class="row g-2">
                        @csrf
                        <div class="col-md-3">
                            <label class="form-label small fw-bold mb-1">ক্যাটাগরি টাইপ</label>
                            <select name="type" class="form-select form-select-sm" required>
                                <option value="electronics">ইলেক্ট্রনিক্স</option>
                                <option value="stationery">স্টেশনারি</option>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small fw-bold mb-1">ক্যাটাগরির নাম</label>
                            <input type="text" name="name" class="form-control form-control-sm" placeholder="যেমন: রিডিং লাইট" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold mb-1">আইকন ক্লাস</label>
                            <input type="text" name="icon" class="form-control form-control-sm" placeholder="fas fa-lightbulb">
                        </div>
                        <div class="col-12 text-end mt-2">
                            <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3 fw-bold">
                                যুক্ত করুন
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Existing Categories Table -->
                <h6 class="fw-bold text-dark mb-2" style="font-size: 13px;">বর্তমান ক্যাটাগরিসমূহ</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle mb-0" style="font-size: 12.5px;">
                        <thead class="table-light">
                            <tr>
                                <th>টাইপ</th>
                                <th>নাম</th>
                                <th>স্লাগ</th>
                                <th>পণ্য সংখ্যা</th>
                                <th class="text-end">অ্যাকশন</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($categories as $cat)
                                <tr>
                                    <td>
                                        <span class="badge {{ $cat->type === 'electronics' ? 'bg-info text-dark' : 'bg-success text-white' }} rounded-pill px-2">
                                            {{ $cat->type === 'electronics' ? 'ইলেক' : 'স্টেশ' }}
                                        </span>
                                    </td>
                                    <td class="fw-semibold">{{ $cat->name }}</td>
                                    <td><code>{{ $cat->slug }}</code></td>
                                    <td>{{ $cat->products_count }} টি</td>
                                    <td class="text-end">
                                        <form action="{{ route('admin.products.categories.destroy', $cat->id) }}" method="POST" class="d-inline" onsubmit="return confirm('এই ক্যাটাগরি মুছে ফেলতে চান?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-1.5 rounded-2">
                                                <i class="fa-solid fa-trash-can" style="font-size: 10px;"></i>
                                            </button>
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
