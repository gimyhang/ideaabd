@extends('layouts.admin')

@section('title', 'বই রিকোয়েস্ট হাব')
@section('heading', 'ইউজারদের স্পেশাল বই রিকোয়েস্ট হাব')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">ড্যাশবোর্ড</a></li>
    <li class="breadcrumb-item active" aria-current="page">বই রিকোয়েস্ট</li>
@endsection

@section('actions')
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-primary btn-sm rounded-pill px-3.5 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#addRequestModal">
            <i class="fas fa-plus-circle me-1.5"></i> নতুন রিকোয়েস্ট যোগ করুন
        </button>
        <a href="{{ route('admin.purchases.create') }}" class="btn btn-outline-success btn-sm rounded-pill px-3 fw-semibold">
            <i class="fas fa-cart-plus me-1.5"></i> প্রকাশনী ক্রয় এন্ট্রি
        </a>
    </div>
@endsection

@section('content')

{{-- ========================================================================= --}}
{{-- 1. REAL-TIME METRICS & STATS CARDS                                        --}}
{{-- ========================================================================= --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-xl">
        <a href="{{ route('admin.book-requests.index') }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 transition-hover border-start border-4 border-primary">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold">মোট রিকোয়েস্ট</span>
                        <h3 class="fw-bold text-dark mb-0 mt-1">@bn($stats['total'])</h3>
                    </div>
                    <div class="rounded-circle bg-primary-subtle text-primary p-3 d-flex align-items-center justify-content-center" style="width: 46px; height: 46px;">
                        <i class="fas fa-book-open fs-5"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-6 col-md-4 col-xl">
        <a href="{{ route('admin.book-requests.index', ['status' => 'pending']) }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 transition-hover border-start border-4 border-warning">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold">অপেক্ষমান</span>
                        <h3 class="fw-bold text-warning mb-0 mt-1">@bn($stats['pending'])</h3>
                    </div>
                    <div class="rounded-circle bg-warning-subtle text-warning p-3 d-flex align-items-center justify-content-center" style="width: 46px; height: 46px;">
                        <i class="fas fa-clock fs-5"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-6 col-md-4 col-xl">
        <a href="{{ route('admin.book-requests.index', ['status' => 'processing']) }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 transition-hover border-start border-4 border-info">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold">প্রক্রিয়াধীন</span>
                        <h3 class="fw-bold text-info mb-0 mt-1">@bn($stats['processing'])</h3>
                    </div>
                    <div class="rounded-circle bg-info-subtle text-info p-3 d-flex align-items-center justify-content-center" style="width: 46px; height: 46px;">
                        <i class="fas fa-spinner fs-5"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-6 col-md-6 col-xl">
        <a href="{{ route('admin.book-requests.index', ['status' => 'available']) }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 transition-hover border-start border-4 border-success">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold">সংগৃহীত / প্রস্তুত</span>
                        <h3 class="fw-bold text-success mb-0 mt-1">@bn($stats['available'])</h3>
                    </div>
                    <div class="rounded-circle bg-success-subtle text-success p-3 d-flex align-items-center justify-content-center" style="width: 46px; height: 46px;">
                        <i class="fas fa-check-circle fs-5"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-12 col-md-6 col-xl">
        <a href="{{ route('admin.book-requests.index', ['status' => 'closed']) }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 transition-hover border-start border-4 border-secondary">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold">সম্পন্ন / বন্ধ</span>
                        <h3 class="fw-bold text-secondary mb-0 mt-1">@bn($stats['closed'])</h3>
                    </div>
                    <div class="rounded-circle bg-secondary-subtle text-secondary p-3 d-flex align-items-center justify-content-center" style="width: 46px; height: 46px;">
                        <i class="fas fa-circle-xmark fs-5"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- 2. SMART SEARCH & MULTI-FILTER BAR                                        --}}
{{-- ========================================================================= --}}
<div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
    <div class="card-body p-3.5">
        <form action="{{ route('admin.book-requests.index') }}" method="GET" class="row g-2.5 align-items-center">
            
            {{-- Search Input --}}
            <div class="col-12 col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="fas fa-magnifying-glass"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0 bg-light" 
                           placeholder="বইয়ের নাম, লেখক, কাস্টমার বা ফোন..." value="{{ $search }}">
                    @if($search)
                        <a href="{{ route('admin.book-requests.index', array_filter(['status' => $status, 'date_from' => $dateFrom, 'date_to' => $dateTo])) }}" class="input-group-text bg-light text-muted text-decoration-none">
                            <i class="fas fa-times"></i>
                        </a>
                    @endif
                </div>
            </div>

            {{-- Status Filter --}}
            <div class="col-6 col-md-2">
                <select name="status" class="form-select bg-light" onchange="this.form.submit()">
                    <option value="">সকল স্ট্যাটাস</option>
                    <option value="pending" @selected($status === 'pending')>⏳ অপেক্ষমান (Pending)</option>
                    <option value="processing" @selected($status === 'processing')>⚙️ প্রক্রিয়াধীন (Processing)</option>
                    <option value="available" @selected($status === 'available')>✅ প্রস্তুত (Available)</option>
                    <option value="closed" @selected($status === 'closed')>❌ সম্পন্ন/বন্ধ (Closed)</option>
                </select>
            </div>

            {{-- Date Range --}}
            <div class="col-6 col-md-2">
                <input type="date" name="date_from" class="form-control bg-light" value="{{ $dateFrom }}" placeholder="শুরুর তারিখ" onchange="this.form.submit()">
            </div>
            <div class="col-6 col-md-2">
                <input type="date" name="date_to" class="form-control bg-light" value="{{ $dateTo }}" placeholder="শেষ তারিখ" onchange="this.form.submit()">
            </div>

            {{-- Actions --}}
            <div class="col-6 col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100 rounded-3 fw-semibold">
                    <i class="fas fa-filter me-1"></i> ফিল্টার
                </button>
                @if($search || $status || $dateFrom || $dateTo)
                    <a href="{{ route('admin.book-requests.index') }}" class="btn btn-light border rounded-3 text-danger" title="ফিল্টার মুছুন">
                        <i class="fas fa-rotate-left"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- 3. MAIN TABLE & BULK ACTIONS                                              --}}
{{-- ========================================================================= --}}
<div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4">
    
    {{-- Bulk Action Bar (Hidden by default, shown when items selected) --}}
    <form id="bulkForm" action="{{ route('admin.book-requests.bulk-action') }}" method="POST">
        @csrf
        
        <div id="bulkActionBar" class="p-2.5 bg-primary-subtle border-bottom d-flex align-items-center justify-content-between px-3" style="display: none;">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary rounded-pill px-2.5" id="selectedCountBadge">০</span>
                <span class="small fw-bold text-primary">টি রিকোয়েস্ট নির্বাচিত</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <select name="bulk_action" class="form-select form-select-sm rounded-pill" style="width: 170px;" required>
                    <option value="">অ্যাকশন নির্বাচন করুন</option>
                    <option value="pending">স্ট্যাটাস: Pending</option>
                    <option value="processing">স্ট্যাটাস: Processing</option>
                    <option value="available">স্ট্যাটাস: Available</option>
                    <option value="closed">স্ট্যাটাস: Closed</option>
                    <option value="delete">🗑️ নির্বাচিতগুলো মুছুন</option>
                </select>
                <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3 fw-bold" onclick="return confirmBulkAction()">
                    প্রয়োগ করুন
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="requestsTable">
                <thead class="table-light text-muted small text-uppercase">
                    <tr>
                        <th class="ps-3" style="width: 40px;">
                            <input type="checkbox" class="form-check-input" id="selectAllCheckbox" onchange="toggleSelectAll(this)">
                        </th>
                        <th style="min-width: 220px;">বই ও লেখকের তথ্য</th>
                        <th style="min-width: 180px;">কাস্টমার বিবরণ</th>
                        <th style="min-width: 180px;">কাস্টমার নোট</th>
                        <th style="min-width: 160px;">অ্যাডমিন নোট</th>
                        <th style="min-width: 140px;">বর্তমান স্ট্যাটাস</th>
                        <th style="min-width: 110px;">তারিখ</th>
                        <th class="text-end pe-3" style="min-width: 130px;">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $req)
                        @php
                            $cleanPhone = $req->clean_phone;
                            $waText = urlencode("প্রিয় গ্রাহক, আইডিয়া প্রকাশনে আপনার রিকোয়েস্টকৃত বই '{$req->book_title}'-এর বিষয়ে যোগাযোগ করা হলো।");
                        @endphp
                        <tr id="reqRow-{{ $req->id }}">
                            {{-- Checkbox --}}
                            <td class="ps-3">
                                <input type="checkbox" name="selected_ids[]" value="{{ $req->id }}" class="form-check-input row-checkbox" onchange="onRowCheckboxChange()">
                            </td>

                            {{-- Book Title & Author --}}
                            <td>
                                <div class="fw-bold text-dark fs-6 mb-0.5">
                                    <span class="text-primary fw-bold font-monospace small me-1">#{{ $req->id }}</span>
                                    {{ $req->book_title }}
                                </div>
                                <div class="d-flex flex-wrap align-items-center gap-2 small text-muted">
                                    @if($req->author_name)
                                        <span><i class="fas fa-user-pen me-1 text-secondary"></i>{{ $req->author_name }}</span>
                                    @endif
                                    @if($req->edition)
                                        <span class="badge bg-light text-dark border"><i class="fas fa-bookmark me-1 text-warning"></i>{{ $req->edition }}</span>
                                    @endif
                                </div>
                                {{-- Quick Link to check in catalog / purchase --}}
                                <div class="mt-1">
                                    <a href="{{ route('admin.books', ['search' => $req->book_title]) }}" target="_blank" class="text-decoration-none small text-muted hover-primary" style="font-size: 0.73rem;">
                                        <i class="fas fa-search me-0.5"></i>ক্যাটালগে খুঁজুন
                                    </a>
                                    <span class="text-muted mx-1">•</span>
                                    <a href="{{ route('admin.purchases.create') }}" class="text-decoration-none small text-success hover-underline" style="font-size: 0.73rem;">
                                        <i class="fas fa-cart-plus me-0.5"></i>ক্রয় এন্ট্রি
                                    </a>
                                </div>
                            </td>

                            {{-- Customer Info --}}
                            <td>
                                <div class="fw-semibold text-dark">{{ $req->customer_name ?: 'নামহীন কাস্টমার' }}</div>
                                @if($req->customer_phone)
                                    <div class="d-flex align-items-center gap-1.5 mt-0.5">
                                        <span class="small font-monospace text-muted">{{ $req->customer_phone }}</span>
                                        <a href="tel:{{ $req->customer_phone }}" class="badge bg-primary-subtle text-primary p-1 rounded-circle text-decoration-none" title="সরাসরি কল করুন">
                                            <i class="fas fa-phone" style="font-size: 9px;"></i>
                                        </a>
                                        <a href="https://wa.me/{{ $cleanPhone }}?text={{ $waText }}" target="_blank" class="badge bg-success-subtle text-success p-1 rounded-circle text-decoration-none" title="হোয়াটসঅ্যাপে মেসেজ পাঠান">
                                            <i class="fab fa-whatsapp" style="font-size: 10px;"></i>
                                        </a>
                                    </div>
                                @endif
                                @if($req->customer_email)
                                    <div class="small text-muted text-truncate" style="max-width: 160px;" title="{{ $req->customer_email }}">
                                        <i class="fas fa-envelope me-1" style="font-size: 10px;"></i>{{ $req->customer_email }}
                                    </div>
                                @endif
                            </td>

                            {{-- Additional Info --}}
                            <td>
                                @if($req->additional_info)
                                    <div class="small text-muted line-clamp-2" style="font-size: 0.8rem; line-height: 1.4;" title="{{ $req->additional_info }}">
                                        {{ $req->additional_info }}
                                    </div>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>

                            {{-- Admin Notes --}}
                            <td>
                                <div class="d-flex align-items-center gap-1">
                                    <div class="small text-dark font-monospace line-clamp-2" style="font-size: 0.78rem;" id="notesText-{{ $req->id }}" title="{{ $req->admin_notes }}">
                                        {{ $req->admin_notes ?: 'নোট নেই' }}
                                    </div>
                                    <button type="button" class="btn btn-xs btn-light border-0 p-1 text-muted" onclick="openNotesModal({{ $req->id }}, '{{ addslashes($req->admin_notes ?? '') }}')" title="নোট লিখুন/সম্পাদনা করুন">
                                        <i class="fas fa-pen" style="font-size: 10px;"></i>
                                    </button>
                                </div>
                            </td>

                            {{-- Status Dropdown --}}
                            <td>
                                <select class="form-select form-select-sm rounded-pill fw-semibold {{ $req->status_badge_class }}" 
                                        style="font-size: 0.76rem;" 
                                        onchange="updateRequestStatus({{ $req->id }}, this.value, this)">
                                    <option value="pending" @selected($req->status === 'pending')>⏳ Pending</option>
                                    <option value="processing" @selected($req->status === 'processing')>⚙️ Processing</option>
                                    <option value="available" @selected($req->status === 'available')>✅ Available</option>
                                    <option value="closed" @selected($req->status === 'closed')>❌ Closed</option>
                                </select>
                            </td>

                            {{-- Created Date --}}
                            <td>
                                <div class="small text-dark">@bnDate($req->created_at)</div>
                                <small class="text-muted" style="font-size: 0.7rem;">{{ $req->created_at->diffForHumans() }}</small>
                            </td>

                            {{-- Actions --}}
                            <td class="text-end pe-3">
                                <div class="d-flex align-items-center justify-content-end gap-1">
                                    <button type="button" class="btn btn-sm btn-light border p-1 rounded-circle shadow-xs" 
                                            onclick="openViewModal({{ json_encode($req) }})" title="বিস্তারিত দেখুন">
                                        <i class="fas fa-eye text-primary" style="font-size: 11px;"></i>
                                    </button>

                                    <form action="{{ route('admin.book-requests.destroy', $req->id) }}" method="POST" 
                                          onsubmit="return confirm('আপনি কি নিশ্চিত যে এই রিকোয়েস্টটি মুছে ফেলতে চান?');" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-light border p-1 rounded-circle shadow-xs text-danger" title="মুছুন">
                                            <i class="fas fa-trash-can" style="font-size: 11px;"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <div class="p-4">
                                    <i class="fas fa-book-open fs-1 opacity-25 mb-3 d-block"></i>
                                    <h6 class="fw-bold text-dark">কোনো বইয়ের রিকোয়েস্ট খুঁজে পাওয়া যায়নি</h6>
                                    <p class="small text-muted mb-3">নতুন রিকোয়েস্ট আসলে স্বয়ংক্রিয়ভাবে এখানে তালিকাভুক্ত হবে।</p>
                                    <button type="button" class="btn btn-sm btn-primary rounded-pill px-3.5" data-bs-toggle="modal" data-bs-target="#addRequestModal">
                                        <i class="fas fa-plus me-1"></i> নতুন রিকোয়েস্ট যোগ করুন
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </form>

    {{-- Pagination Footer --}}
    @if($requests->hasPages())
        <div class="card-footer bg-white border-top py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div class="text-muted small">
                মোট <strong>@bn($requests->total())</strong>টির মধ্যে <strong>@bn($requests->firstItem()) - @bn($requests->lastItem())</strong>টি দেখানো হচ্ছে
            </div>
            <div>
                {{ $requests->links() }}
            </div>
        </div>
    @endif
</div>

{{-- ========================================================================= --}}
{{-- 4. MODAL 1: ADD NEW BOOK REQUEST MANUALLY                                 --}}
{{-- ========================================================================= --}}
<div class="modal fade" id="addRequestModal" tabindex="-1" aria-labelledby="addRequestModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-primary text-white py-3 px-4">
                <h5 class="modal-title fw-bold" id="addRequestModalLabel">
                    <i class="fas fa-plus-circle me-1.5"></i> নতুন বই রিকোয়েস্ট এন্ট্রি
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.book-requests.admin-store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">বইয়ের নাম <span class="text-danger">*</span></label>
                        <input type="text" name="book_title" class="form-control rounded-3" placeholder="বইয়ের পূর্ণাঙ্গ শিরোনাম..." required>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-sm-6">
                            <label class="form-label small fw-bold">লেখকের নাম</label>
                            <input type="text" name="author_name" class="form-control rounded-3" placeholder="লেখকের নাম...">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label small fw-bold">সংস্করণ / প্রকাশ সাল</label>
                            <input type="text" name="edition" class="form-control rounded-3" placeholder="যেমন: ১ম প্রকাশ / ২০২৬">
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-sm-6">
                            <label class="form-label small fw-bold">কাস্টমারের নাম</label>
                            <input type="text" name="customer_name" class="form-control rounded-3" placeholder="গ্রাহকের নাম...">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label small fw-bold">মোবাইল নম্বর <span class="text-danger">*</span></label>
                            <input type="text" name="customer_phone" class="form-control rounded-3 font-monospace" placeholder="০১৭১০..." required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">ইমেইল এড্রেস</label>
                        <input type="email" name="customer_email" class="form-control rounded-3" placeholder="customer@gmail.com">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">কাস্টমার অতিরিক্ত বিবরণ / নোট</label>
                        <textarea name="additional_info" rows="2" class="form-control rounded-3" placeholder="কোনো বিশেষ সংস্করণ বা তথ্য থাকলে..."></textarea>
                    </div>

                    <div class="row g-2">
                        <div class="col-sm-6">
                            <label class="form-label small fw-bold">স্ট্যাটাস</label>
                            <select name="status" class="form-select rounded-3">
                                <option value="pending">⏳ Pending (অপেক্ষমান)</option>
                                <option value="processing">⚙️ Processing (খোঁজা হচ্ছে)</option>
                                <option value="available">✅ Available (প্রস্তুত)</option>
                                <option value="closed">❌ Closed (বন্ধ)</option>
                            </select>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label small fw-bold">অ্যাডমিন নোট (অভ্যন্তরীণ)</label>
                            <input type="text" name="admin_notes" class="form-control rounded-3" placeholder="বাংলাবাজারে যোগাযোগ...">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2.5 px-4 border-top">
                    <button type="button" class="btn btn-light border rounded-pill px-3" data-bs-dismiss="modal">বাতিল</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">সংরক্ষণ করুন</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- 5. MODAL 2: EDIT ADMIN NOTES                                              --}}
{{-- ========================================================================= --}}
<div class="modal fade" id="notesModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-dark text-white py-2.5 px-3">
                <h6 class="modal-title fw-bold mb-0"><i class="fas fa-note-sticky text-warning me-1.5"></i>অ্যাডমিন নোট</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="notesForm" onsubmit="saveAdminNotes(event)">
                <div class="modal-body p-3">
                    <input type="hidden" id="notesReqId" value="">
                    <textarea id="notesTextarea" class="form-control rounded-3" rows="4" placeholder="অভ্যন্তরীণ ট্র্যাকিং নোট লিখুন..."></textarea>
                </div>
                <div class="modal-footer bg-light p-2 border-top">
                    <button type="button" class="btn btn-light btn-sm border rounded-pill px-3" data-bs-dismiss="modal">বন্ধ</button>
                    <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3 fw-bold">সেভ করুন</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- 6. MODAL 3: VIEW FULL REQUEST DETAILS                                     --}}
{{-- ========================================================================= --}}
<div class="modal fade" id="viewRequestModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-light py-3 px-4 border-bottom">
                <h5 class="modal-title fw-bold text-dark mb-0">
                    <i class="fas fa-circle-info text-primary me-1.5"></i>রিকোয়েস্ট বিস্তারিত (#<span id="vReqId"></span>)
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="p-3 bg-light rounded-3 mb-3 border">
                    <span class="text-muted small fw-bold d-block">বইয়ের শিরোনাম:</span>
                    <h5 class="fw-bold text-dark mb-1" id="vBookTitle"></h5>
                    <div class="text-muted small" id="vAuthorName"></div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-sm-6">
                        <span class="text-muted small d-block">কাস্টমার নাম:</span>
                        <div class="fw-bold text-dark" id="vCustomerName"></div>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted small d-block">মোবাইল নম্বর:</span>
                        <div class="fw-bold text-dark font-monospace" id="vCustomerPhone"></div>
                    </div>
                    <div class="col-12" id="vEmailWrapper">
                        <span class="text-muted small d-block">ইমেইল:</span>
                        <div class="text-dark" id="vCustomerEmail"></div>
                    </div>
                </div>

                <div class="mb-3">
                    <span class="text-muted small d-block">কাস্টমার অতিরিক্ত তথ্য:</span>
                    <div class="p-2.5 bg-light rounded-3 text-dark small" id="vAdditionalInfo"></div>
                </div>

                <div class="mb-2">
                    <span class="text-muted small d-block">অ্যাডমিন নোট:</span>
                    <div class="p-2.5 bg-warning-subtle rounded-3 text-dark small font-monospace" id="vAdminNotes"></div>
                </div>
            </div>
            <div class="modal-footer bg-light py-2.5 px-4 border-top">
                <button type="button" class="btn btn-secondary btn-sm rounded-pill px-4" data-bs-dismiss="modal">বন্ধ করুন</button>
            </div>
        </div>
    </div>
</div>

<script>
function updateRequestStatus(id, newStatus, selectEl) {
    fetch(`/admin/book-requests/${id}`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ status: newStatus })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            selectEl.className = `form-select form-select-sm rounded-pill fw-semibold ${data.badge_class}`;
            showToast(data.message);
        } else {
            alert('স্ট্যাটাস আপডেট করা সম্ভব হয়নি।');
        }
    })
    .catch(() => alert('সার্ভার যোগাযোগে ত্রুটি ঘটেছে।'));
}

function openNotesModal(id, currentNotes) {
    document.getElementById('notesReqId').value = id;
    document.getElementById('notesTextarea').value = currentNotes || '';
    new bootstrap.Modal(document.getElementById('notesModal')).show();
}

function saveAdminNotes(e) {
    e.preventDefault();
    const id = document.getElementById('notesReqId').value;
    const notes = document.getElementById('notesTextarea').value;

    fetch(`/admin/book-requests/${id}/notes`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ admin_notes: notes })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const noteEl = document.getElementById(`notesText-${id}`);
            if (noteEl) noteEl.textContent = notes || 'নোট নেই';
            bootstrap.Modal.getInstance(document.getElementById('notesModal')).hide();
            showToast('অ্যাডমিন নোট সংরক্ষিত হয়েছে!');
        }
    })
    .catch(() => alert('নোট সেভ করতে সমস্যা হয়েছে।'));
}

function openViewModal(req) {
    document.getElementById('vReqId').textContent = req.id;
    document.getElementById('vBookTitle').textContent = req.book_title;
    document.getElementById('vAuthorName').textContent = req.author_name ? 'লেখক: ' + req.author_name : 'লেখকের নাম উল্লেখ নেই';
    document.getElementById('vCustomerName').textContent = req.customer_name || 'নামহীন';
    document.getElementById('vCustomerPhone').textContent = req.customer_phone || '-';
    document.getElementById('vCustomerEmail').textContent = req.customer_email || '-';
    document.getElementById('vAdditionalInfo').textContent = req.additional_info || 'কোনো অতিরিক্ত তথ্য নেই';
    document.getElementById('vAdminNotes').textContent = req.admin_notes || 'কোনো অভ্যন্তরীণ নোট নেই';
    new bootstrap.Modal(document.getElementById('viewRequestModal')).show();
}

function toggleSelectAll(master) {
    document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = master.checked);
    onRowCheckboxChange();
}

function onRowCheckboxChange() {
    const checked = document.querySelectorAll('.row-checkbox:checked');
    const bulkBar = document.getElementById('bulkActionBar');
    const badge = document.getElementById('selectedCountBadge');

    if (checked.length > 0) {
        bulkBar.style.display = 'flex';
        badge.textContent = checked.length;
    } else {
        bulkBar.style.display = 'none';
        document.getElementById('selectAllCheckbox').checked = false;
    }
}

function confirmBulkAction() {
    return confirm('আপনি কি নিশ্চিত যে নির্বাচিত রিকোয়েস্টগুলোতে এই অ্যাকশন প্রয়োগ করতে চান?');
}

function showToast(msg) {
    const alertDiv = document.createElement('div');
    alertDiv.className = 'position-fixed bottom-0 end-0 p-3 z-3';
    alertDiv.innerHTML = `
        <div class="toast show align-items-center text-white bg-dark border-0 shadow-lg rounded-3" role="alert">
            <div class="d-flex">
                <div class="toast-body small fw-semibold">
                    <i class="fas fa-check-circle text-success me-1.5"></i> ${msg}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;
    document.body.appendChild(alertDiv);
    setTimeout(() => alertDiv.remove(), 3500);
}
</script>

<style>
.transition-hover {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.transition-hover:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px -4px rgba(0, 0, 0, 0.08) !important;
}
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.shadow-xs {
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
}
</style>

@endsection
