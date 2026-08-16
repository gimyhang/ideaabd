@extends('layouts.admin')

@section('title', 'বই পরিচালনা')
@section('heading', 'বই ক্যাটালগ ও ইনভেন্টরি ম্যানেজমেন্ট')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">বই তালিকা</li>
@endsection

@section('actions')
    <a href="{{ route('admin.content.create', 'books') }}" class="btn btn-primary btn-sm rounded-pill px-3 fw-bold">
        <i class="fas fa-plus-circle me-1"></i> নতুন বই যুক্ত করুন
    </a>
    <a href="{{ route('book.index') }}" target="_blank" rel="noopener" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
        <i class="fas fa-arrow-up-right-from-square me-1"></i> ওয়েবসাইটে দেখুন
    </a>
@endsection

@section('content')
<div class="d-flex flex-column gap-3">

    {{-- Flash Notifications --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-0" role="alert">
            <i class="fas fa-circle-check me-2"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- 1. KPI SUMMARY STRIP (বই ও ইনভেন্টরি মেট্রিক্স)                            --}}
    {{-- ========================================================================= --}}
    <div class="row g-2">
        <div class="col-6 col-md-3">
            <div class="adm-card p-3 d-flex align-items-center justify-content-between">
                <div>
                    <small class="text-muted d-block">মোট বই ক্যাটালগ</small>
                    <h4 class="fw-bold text-dark mb-0">@bn($stats['total'] ?? 0) <small class="fs-6 text-muted">টি</small></h4>
                </div>
                <span class="p-2 bg-primary-subtle text-primary rounded-circle fs-5"><i class="fas fa-book"></i></span>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="adm-card p-3 d-flex align-items-center justify-content-between">
                <div>
                    <small class="text-muted d-block">সক্রিয় ও লাইভ বই</small>
                    <h4 class="fw-bold text-success mb-0">@bn($stats['active'] ?? 0) <small class="fs-6 text-muted">টি</small></h4>
                </div>
                <span class="p-2 bg-success-subtle text-success rounded-circle fs-5"><i class="fas fa-circle-check"></i></span>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="adm-card p-3 d-flex align-items-center justify-content-between">
                <div>
                    <small class="text-muted d-block">লো-স্টক অ্যালার্ট (&le;৫)</small>
                    <h4 class="fw-bold text-warning mb-0">@bn($stats['low_stock'] ?? 0) <small class="fs-6 text-muted">টি</small></h4>
                </div>
                <span class="p-2 bg-warning-subtle text-warning rounded-circle fs-5"><i class="fas fa-triangle-exclamation"></i></span>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="adm-card p-3 d-flex align-items-center justify-content-between">
                <div>
                    <small class="text-muted d-block">স্টক শেষ (Out of Stock)</small>
                    <h4 class="fw-bold text-danger mb-0">@bn($stats['out_stock'] ?? 0) <small class="fs-6 text-muted">টি</small></h4>
                </div>
                <span class="p-2 bg-danger-subtle text-danger rounded-circle fs-5"><i class="fas fa-box-open"></i></span>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- 2. ADVANCED FILTER & SEARCH TOOLBAR                                       --}}
    {{-- ========================================================================= --}}
    <div class="adm-card p-3">
        <form action="{{ route('admin.books') }}" method="GET" class="row g-2 align-items-center">
            
            <!-- Search Bar -->
            <div class="col-12 col-md-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="বইয়ের নাম, লেখক, ISBN দিয়ে খুঁজুন...">
                </div>
            </div>

            <!-- Category Filter -->
            <div class="col-6 col-md-3">
                <select name="category_id" class="form-select form-select-sm">
                    <option value="">— সকল ক্যাটাগরি —</option>
                    @foreach ($categories as $cId => $cName)
                        <option value="{{ $cId }}" @selected(request('category_id') == $cId)>{{ $cName }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Stock Filter -->
            <div class="col-6 col-md-2">
                <select name="stock" class="form-select form-select-sm">
                    <option value="">— সকল স্টক অবস্থা —</option>
                    <option value="in_stock" @selected(request('stock') === 'in_stock')>ইন স্টক (&gt;৫)</option>
                    <option value="low" @selected(request('stock') === 'low')>লো স্টক (&le;৫)</option>
                    <option value="out" @selected(request('stock') === 'out')>স্টক আউট (০)</option>
                </select>
            </div>

            <!-- Status Filter -->
            <div class="col-6 col-md-2">
                <select name="is_active" class="form-select form-select-sm">
                    <option value="">— সকল স্ট্যাটাস —</option>
                    <option value="1" @selected(request('is_active') === '1')>সক্রিয় / লাইভ</option>
                    <option value="0" @selected(request('is_active') === '0')>নিষ্ক্রিয় / খসড়া</option>
                </select>
            </div>

            <!-- Buttons -->
            <div class="col-6 col-md-1 d-flex gap-1">
                <button type="submit" class="btn btn-sm btn-primary flex-fill fw-semibold" title="ফিল্টার করুন">
                    <i class="fas fa-filter"></i>
                </button>
                <a href="{{ route('admin.books') }}" class="btn btn-sm btn-outline-secondary" title="রিসেট">
                    <i class="fas fa-rotate-left"></i>
                </a>
            </div>

        </form>
    </div>

    {{-- ========================================================================= --}}
    {{-- 3. ULTRA-MODERN BOOK MANAGEMENT TABLE                                     --}}
    {{-- ========================================================================= --}}
    <div class="adm-card p-0 overflow-hidden">
        <div class="table-responsive">
            <table class="table adm-table align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-3" style="width: 50px;">#</th>
                        <th style="min-width: 260px;">বই ও শিরোনাম</th>
                        <th>লেখক ও প্রকাশনী</th>
                        <th>ক্যাটাগরি</th>
                        <th class="text-end">মূল্য ও ছাড়</th>
                        <th class="text-center">স্টক ইনভেন্টরি</th>
                        <th class="text-center">অবস্থা</th>
                        <th class="text-end pe-3">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($books as $index => $book)
                        @php
                            $cover = $book->cover_image;
                            $coverUrl = $cover 
                                ? (str_starts_with($cover, 'http') ? $cover : (str_starts_with($cover, 'storage/') ? asset($cover) : asset('storage/' . ltrim($cover, '/'))))
                                : 'https://placehold.co/100x150/e2e8f0/475569?text=Cover';
                            
                            $price = (float) $book->price;
                            $discount = (float) ($book->discount_price ?? 0);
                            $hasDiscount = $discount > 0 && $discount < $price;
                            $discountPercent = $hasDiscount ? round((($price - $discount) / $price) * 100) : 0;
                            $stock = (int) ($book->stock_quantity ?? 0);
                        @endphp
                        <tr>
                            <td class="ps-3 text-muted small">
                                @bn(($books->currentPage() - 1) * $books->perPage() + $index + 1)
                            </td>
                            
                            {{-- Book Title & Cover --}}
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="position-relative flex-shrink-0" style="width: 44px; height: 60px;">
                                        <img src="{{ $coverUrl }}" alt="{{ $book->title }}" 
                                             class="rounded border shadow-xs" style="width: 100%; height: 100%; object-fit: cover;">
                                        @if($book->format === 'ebook')
                                            <span class="badge bg-info text-white position-absolute top-0 start-0 m-0.5 p-0.5 rounded-1" style="font-size: 8px;">ই-বুক</span>
                                        @endif
                                    </div>
                                    <div class="text-truncate" style="max-width: 260px;">
                                        <a href="{{ route('book.show', $book->slug ?? $book->id) }}" target="_blank" 
                                           class="fw-bold text-dark text-decoration-none hover-primary d-block text-truncate mb-0.5">
                                            {{ $book->title }}
                                        </a>
                                        @if($book->subtitle)
                                            <div class="small text-muted text-truncate mb-0.5" style="font-size: 11px;">{{ $book->subtitle }}</div>
                                        @endif
                                        <div class="d-flex align-items-center gap-2 small text-muted" style="font-size: 11px;">
                                            @if($book->isbn)
                                                <span><i class="fas fa-barcode me-0.5"></i> {{ $book->isbn }}</span>
                                            @endif
                                            <span><i class="fas fa-cart-shopping me-0.5"></i> @bn($book->sales_count ?? 0) কপি বিক্রি</span>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            {{-- Author & Publisher --}}
                            <td>
                                <div class="fw-semibold text-dark small">
                                    @if($book->authorLink)
                                        <a href="{{ route('authors.show', $book->authorLink->slug ?? $book->authorLink->id) }}" target="_blank" class="text-decoration-none text-dark hover-primary">
                                            {{ $book->authorLink->name }}
                                        </a>
                                    @else
                                        {{ $book->author_name ?? '—' }}
                                    @endif
                                </div>
                                <div class="small text-muted" style="font-size: 11px;">
                                    {{ $book->publisher->name ?? 'আইডিয়া প্রকাশন' }}
                                </div>
                            </td>

                            {{-- Category --}}
                            <td>
                                @if($book->category)
                                    <span class="badge bg-light text-primary border rounded-pill px-2.5 py-1">
                                        <i class="fas fa-folder me-1 text-primary-subtle"></i>{{ $book->category->name }}
                                    </span>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>

                            {{-- Price & Discount --}}
                            <td class="text-end">
                                @if($hasDiscount)
                                    <div class="fw-bold text-primary fs-6">৳@bn(number_format($discount, 0))</div>
                                    <div class="d-flex align-items-center justify-content-end gap-1 small">
                                        <span class="text-muted text-decoration-line-through">৳@bn(number_format($price, 0))</span>
                                        <span class="badge bg-danger-subtle text-danger rounded-pill" style="font-size: 10px;">-{{ $discountPercent }}%</span>
                                    </div>
                                @else
                                    <div class="fw-bold text-dark fs-6">৳@bn(number_format($price, 0))</div>
                                @endif
                            </td>

                            {{-- Stock Inventory & Quick Refill --}}
                            <td class="text-center">
                                <div class="d-inline-flex align-items-center gap-1.5">
                                    @if($stock <= 0)
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2">
                                            <i class="fas fa-times-circle me-1"></i>স্টকআউট
                                        </span>
                                    @elseif($stock <= 5)
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-2">
                                            <i class="fas fa-triangle-exclamation me-1"></i>@bn($stock) টি বাকি
                                        </span>
                                    @else
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2">
                                            @bn($stock) টি ইন-স্টক
                                        </span>
                                    @endif

                                    <button type="button" class="btn btn-xs btn-outline-secondary rounded-circle" 
                                            style="width: 24px; height: 24px; padding: 0;"
                                            onclick="openQuickStockModal({{ $book->id }}, '{{ addslashes($book->title) }}', {{ $stock }})"
                                            title="স্টক আপডেট করুন">
                                        <i class="fas fa-pen" style="font-size: 10px;"></i>
                                    </button>
                                </div>
                            </td>

                            {{-- Status --}}
                            <td class="text-center">
                                @if($book->is_active)
                                    <span class="pill pill--ok"><i class="fas fa-check me-1"></i>লাইভ</span>
                                @else
                                    <span class="pill pill--muted">খসড়া</span>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td class="text-end pe-3">
                                <div class="d-inline-flex align-items-center gap-1">
                                    <a href="{{ route('book.show', $book->slug ?? $book->id) }}" target="_blank" 
                                       class="btn btn-sm btn-outline-secondary rounded-pill px-2 py-0.5" title="সাইটে প্রিভিউ দেখুন">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.content.edit', ['type' => 'books', 'id' => $book->id]) }}" 
                                       class="btn btn-sm btn-outline-primary rounded-pill px-2 py-0.5" title="সম্পাদনা করুন">
                                        <i class="fas fa-pen-to-square"></i>
                                    </a>
                                    <form action="{{ route('admin.content.destroy', ['type' => 'books', 'id' => $book->id]) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('আপনি কি নিশ্চিত যে এই বইটি মুছে ফেলতে চান?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-2 py-0.5" title="মুছে ফেলুন">
                                            <i class="fas fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="empty-state py-5 text-center">
                                    <i class="fas fa-book-open fs-1 text-muted mb-2 d-block"></i>
                                    <p class="fw-semibold text-dark mb-1">কোনো বই পাওয়া যায়নি</p>
                                    <small class="text-muted">আপনার ফিল্টার পরিবর্তন করুন অথবা নতুন বই যোগ করুন।</small>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($books->hasPages())
            <div class="p-3 border-top d-flex align-items-center justify-content-between">
                <div class="small text-muted">
                    মোট @bn($books->total()) টির মধ্যে @bn($books->firstItem()) - @bn($books->lastItem()) দেখানো হচ্ছে
                </div>
                <div>{{ $books->links() }}</div>
            </div>
        @endif
    </div>

</div>

{{-- ========================================================================= --}}
{{-- MODAL: QUICK STOCK REFILL                                                 --}}
{{-- ========================================================================= --}}
<div class="modal fade" id="quickStockModal" tabindex="-1" aria-labelledby="quickStockModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-primary text-white py-2.5">
                <h6 class="modal-title fw-bold text-white mb-0" id="quickStockModalLabel">
                    <i class="fas fa-boxes-stacked me-1.5"></i> ইনভেন্টরি স্টক আপডেট
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="quickStockForm" onsubmit="handleQuickStockSubmit(event)">
                <input type="hidden" id="quickStockBookId" name="book_id">
                <div class="modal-body p-4">
                    <div id="quickStockAlert"></div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">বইয়ের নাম</label>
                        <h6 class="fw-bold text-dark" id="quickStockBookTitle">—</h6>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">নতুন স্টক সংখ্যা <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" id="quickStockQty" name="quantity" min="0" max="100000" class="form-control form-control-lg fw-bold" required>
                            <span class="input-group-text bg-light">টি</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">বাতিল</button>
                    <button type="submit" id="quickStockBtn" class="btn btn-sm btn-primary">
                        <i class="fas fa-check-circle me-1"></i> স্টক সংরক্ষণ করুন
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function openQuickStockModal(bookId, title, currentStock) {
    document.getElementById('quickStockBookId').value = bookId;
    document.getElementById('quickStockBookTitle').textContent = title;
    document.getElementById('quickStockQty').value = currentStock;
    document.getElementById('quickStockAlert').innerHTML = '';

    const modalEl = document.getElementById('quickStockModal');
    new bootstrap.Modal(modalEl).show();
}

function handleQuickStockSubmit(e) {
    e.preventDefault();
    const btn = document.getElementById('quickStockBtn');
    const alertBox = document.getElementById('quickStockAlert');
    const bookId = document.getElementById('quickStockBookId').value;
    const qty = document.getElementById('quickStockQty').value;

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> সংরক্ষণ হচ্ছে...';

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    fetch("{{ route('admin.books.quick-stock') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({ book_id: bookId, quantity: qty })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alertBox.innerHTML = `<div class="alert alert-danger p-2 small mb-2">${data.message || 'ত্রুটি হয়েছে'}</div>`;
        }
    })
    .catch(err => {
        alertBox.innerHTML = `<div class="alert alert-danger p-2 small mb-2">সার্ভার এরর হয়েছে।</div>`;
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check-circle me-1"></i> স্টক সংরক্ষণ করুন';
    });
}
</script>
@endpush

@endsection
