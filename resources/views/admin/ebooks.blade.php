@extends('layouts.admin')

@section('title', 'ই-বুক পরিচালনা')
@section('heading', 'ই-বুক ও ডিজিটাল প্রকাশনা ম্যানেজমেন্ট')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">ই-বুক তালিকা</li>
@endsection

@section('actions')
    <a href="{{ route('admin.content.create', 'ebooks') }}" class="btn btn-primary btn-sm rounded-pill px-3 fw-bold">
        <i class="fas fa-plus-circle me-1"></i> নতুন ই-বুক আপলোড করুন
    </a>
    <a href="{{ route('ebook.index') }}" target="_blank" rel="noopener" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
        <i class="fas fa-arrow-up-right-from-square me-1"></i> ডিজিটাল লাইব্রেরি দেখুন
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
    {{-- 1. KPI SUMMARY STRIP (ই-বুক মেট্রিক্স)                                     --}}
    {{-- ========================================================================= --}}
    <div class="row g-2">
        <div class="col-4">
            <div class="adm-card p-3 d-flex align-items-center justify-content-between">
                <div>
                    <small class="text-muted d-block">মোট ডিজিটাল ই-বুক</small>
                    <h4 class="fw-bold text-dark mb-0">@bn($stats['total'] ?? 0) <small class="fs-6 text-muted">টি</small></h4>
                </div>
                <span class="p-2 bg-primary-subtle text-primary rounded-circle fs-5"><i class="fas fa-tablet-screen-button"></i></span>
            </div>
        </div>
        <div class="col-4">
            <div class="adm-card p-3 d-flex align-items-center justify-content-between">
                <div>
                    <small class="text-muted d-block">সক্রিয় ও লাইভ ই-বুক</small>
                    <h4 class="fw-bold text-success mb-0">@bn($stats['active'] ?? 0) <small class="fs-6 text-muted">টি</small></h4>
                </div>
                <span class="p-2 bg-success-subtle text-success rounded-circle fs-5"><i class="fas fa-circle-check"></i></span>
            </div>
        </div>
        <div class="col-4">
            <div class="adm-card p-3 d-flex align-items-center justify-content-between">
                <div>
                    <small class="text-muted d-block">ফ্রি ও ওপেন রিডিং ই-বুক</small>
                    <h4 class="fw-bold text-info mb-0">@bn($stats['free'] ?? 0) <small class="fs-6 text-muted">টি</small></h4>
                </div>
                <span class="p-2 bg-info-subtle text-info rounded-circle fs-5"><i class="fas fa-gift"></i></span>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- 2. ADVANCED FILTER & SEARCH TOOLBAR                                       --}}
    {{-- ========================================================================= --}}
    <div class="adm-card p-3">
        <form action="{{ route('admin.ebooks') }}" method="GET" class="row g-2 align-items-center">
            
            <!-- Search Bar -->
            <div class="col-12 col-md-5">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="ই-বুকের নাম বা লেখক দিয়ে খুঁজুন...">
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

            <!-- Status Filter -->
            <div class="col-6 col-md-2">
                <select name="is_active" class="form-select form-select-sm">
                    <option value="">— সকল অবস্থা —</option>
                    <option value="1" @selected(request('is_active') === '1')>সক্রিয় / লাইভ</option>
                    <option value="0" @selected(request('is_active') === '0')>নিষ্ক্রিয় / খসড়া</option>
                </select>
            </div>

            <!-- Buttons -->
            <div class="col-12 col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-sm btn-primary flex-fill fw-semibold" title="ফিল্টার করুন">
                    <i class="fas fa-filter me-1"></i> ফিল্টার
                </button>
                <a href="{{ route('admin.ebooks') }}" class="btn btn-sm btn-outline-secondary" title="রিসেট">
                    <i class="fas fa-rotate-left"></i>
                </a>
            </div>

        </form>
    </div>

    {{-- ========================================================================= --}}
    {{-- 3. ULTRA-MODERN E-BOOK MANAGEMENT TABLE                                   --}}
    {{-- ========================================================================= --}}
    <div class="adm-card p-0 overflow-hidden">
        <div class="table-responsive">
            <table class="table adm-table align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-3" style="width: 50px;">#</th>
                        <th style="min-width: 260px;">ই-বুক ও কভার</th>
                        <th>লেখক</th>
                        <th>ক্যাটাগরি</th>
                        <th>ফাইল ফরম্যাট ও সাইজ</th>
                        <th class="text-end">মূল্য</th>
                        <th class="text-center">অবস্থা</th>
                        <th class="text-end pe-3">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($ebooks as $index => $ebook)
                        @php
                            $cover = $ebook->cover_image;
                            $coverUrl = $cover 
                                ? (str_starts_with($cover, 'http') ? $cover : (str_starts_with($cover, 'storage/') ? asset($cover) : asset('storage/' . ltrim($cover, '/'))))
                                : 'https://placehold.co/100x150/0284c7/ffffff?text=E-Book';
                            
                            $price = (float) $ebook->price;
                            $discount = (float) ($ebook->discount_price ?? 0);
                            $hasDiscount = $discount > 0 && $discount < $price;
                            $isFree = $price <= 0;
                        @endphp
                        <tr>
                            <td class="ps-3 text-muted small">
                                @bn(($ebooks->currentPage() - 1) * $ebooks->perPage() + $index + 1)
                            </td>
                            
                            {{-- Ebook Cover & Title --}}
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="position-relative flex-shrink-0" style="width: 44px; height: 60px;">
                                        <img src="{{ $coverUrl }}" alt="{{ $ebook->title }}" 
                                             class="rounded border shadow-xs" style="width: 100%; height: 100%; object-fit: cover;">
                                        <span class="badge bg-primary text-white position-absolute top-0 start-0 m-0.5 p-0.5 rounded-1" style="font-size: 8px;">
                                            <i class="fas fa-file-pdf"></i>
                                        </span>
                                    </div>
                                    <div class="text-truncate" style="max-width: 260px;">
                                        <a href="{{ route('ebook.show', $ebook->slug ?? $ebook->id) }}" target="_blank" 
                                           class="fw-bold text-dark text-decoration-none hover-primary d-block text-truncate mb-0.5">
                                            {{ $ebook->title }}
                                        </a>
                                        <div class="d-flex align-items-center gap-2 small text-muted" style="font-size: 11px;">
                                            <span><i class="fas fa-download me-0.5"></i> @bn($ebook->download_count ?? 0) বার পঠিত</span>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            {{-- Author --}}
                            <td>
                                <div class="fw-semibold text-dark small">
                                    @if($ebook->authorLink)
                                        <a href="{{ route('authors.show', $ebook->authorLink->slug ?? $ebook->authorLink->id) }}" target="_blank" class="text-decoration-none text-dark hover-primary">
                                            {{ $ebook->authorLink->name }}
                                        </a>
                                    @else
                                        {{ $ebook->author_name ?? '—' }}
                                    @endif
                                </div>
                            </td>

                            {{-- Category --}}
                            <td>
                                @if($ebook->category)
                                    <span class="badge bg-light text-primary border rounded-pill px-2.5 py-1">
                                        <i class="fas fa-folder me-1 text-primary-subtle"></i>{{ $ebook->category->name }}
                                    </span>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>

                            {{-- File Format & Size --}}
                            <td>
                                <div class="d-flex align-items-center gap-1.5">
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-0.5" style="font-size: 11px;">
                                        <i class="fas fa-file-pdf me-1"></i>{{ strtoupper($ebook->file_type ?? 'PDF') }}
                                    </span>
                                    <span class="small text-muted" style="font-size: 11px;">{{ $ebook->file_size ?? '—' }}</span>
                                </div>
                            </td>

                            {{-- Pricing --}}
                            <td class="text-end">
                                @if($isFree)
                                    <span class="badge bg-success text-white rounded-pill px-2.5">ফ্রি (বিনামূল্যে)</span>
                                @elseif($hasDiscount)
                                    <div class="fw-bold text-primary fs-6">৳@bn(number_format($discount, 0))</div>
                                    <div class="small text-muted text-decoration-line-through">৳@bn(number_format($price, 0))</div>
                                @else
                                    <div class="fw-bold text-dark fs-6">৳@bn(number_format($price, 0))</div>
                                @endif
                            </td>

                            {{-- Status --}}
                            <td class="text-center">
                                @if($ebook->is_active)
                                    <span class="pill pill--ok"><i class="fas fa-check me-1"></i>লাইভ</span>
                                @else
                                    <span class="pill pill--muted">খসড়া</span>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td class="text-end pe-3">
                                <div class="d-inline-flex align-items-center gap-1">
                                    <a href="{{ route('ebook.show', $ebook->slug ?? $ebook->id) }}" target="_blank" 
                                       class="btn btn-sm btn-outline-secondary rounded-pill px-2 py-0.5" title="ডিজিটাল রিডারে দেখুন">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.content.edit', ['type' => 'ebooks', 'id' => $ebook->id]) }}" 
                                       class="btn btn-sm btn-outline-primary rounded-pill px-2 py-0.5" title="সম্পাদনা করুন">
                                        <i class="fas fa-pen-to-square"></i>
                                    </a>
                                    <form action="{{ route('admin.content.destroy', ['type' => 'ebooks', 'id' => $ebook->id]) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('আপনি কি নিশ্চিত যে এই ই-বুকটি মুছে ফেলতে চান?');">
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
                                    <i class="fas fa-tablet-screen-button fs-1 text-muted mb-2 d-block"></i>
                                    <p class="fw-semibold text-dark mb-1">কোনো ই-বুক পাওয়া যায়নি</p>
                                    <small class="text-muted">নতুন ই-বুক ফাইল আপলোড করুন।</small>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($ebooks->hasPages())
            <div class="p-3 border-top d-flex align-items-center justify-content-between">
                <div class="small text-muted">
                    মোট @bn($ebooks->total()) টির মধ্যে @bn($ebooks->firstItem()) - @bn($ebooks->lastItem()) দেখানো হচ্ছে
                </div>
                <div>{{ $ebooks->links() }}</div>
            </div>
        @endif
    </div>

</div>
@endsection
