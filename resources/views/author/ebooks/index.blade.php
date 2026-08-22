@extends('author.layout')

@section('title', 'আমার ই-বুকসমূহ — লেখক পোর্টাল')
@section('heading', 'আমার ই-বুক ও প্রকাশনা তালিকা')

@section('content')
<div class="d-flex flex-column gap-4">

    {{-- Top Action & Filter Header --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <h5 class="fw-bold mb-1 text-dark">আমার প্রকাশিত ও পেন্ডিং ই-বুকসমূহ</h5>
            <p class="text-muted small mb-0">আপনার তৈরি সকল ই-বুক, সেলস পরিসংখ্যান ও এপ্রুভাল স্ট্যাটাস পর্যবেক্ষণ করুন।</p>
        </div>
        <a href="{{ route('author.ebooks.create') }}" class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-xs">
            <i class="fas fa-plus-circle me-1.5"></i> নতুন ই-বুক আপলোড করুন
        </a>
    </div>

    {{-- Filter Bar --}}
    <div class="author-card p-3">
        <form method="GET" action="{{ route('author.ebooks.index') }}" class="row g-2 align-items-center">
            <div class="col-12 col-md-6">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-search"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" 
                           placeholder="বইয়ের শিরোনাম দিয়ে খুঁজুন..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-6 col-md-4">
                <select name="status" class="form-select form-select-sm rounded-3">
                    <option value="">সকল স্ট্যাটাস (All)</option>
                    <option value="approved" @selected(request('status') === 'approved')>অনুমোদিত ও লাইভ (Published)</option>
                    <option value="pending" @selected(request('status') === 'pending')>রিভিউতে অপেক্ষমাণ (Pending)</option>
                    <option value="rejected" @selected(request('status') === 'rejected')>সংশোধন প্রয়োজন (Rejected)</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <button type="submit" class="btn btn-sm btn-outline-secondary w-100 rounded-pill fw-semibold">
                    <i class="fas fa-filter me-1"></i> ফিল্টার
                </button>
            </div>
        </form>
    </div>

    {{-- Ebooks Grid / Table --}}
    <div class="author-card overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small fw-bold text-secondary">
                    <tr>
                        <th style="width: 70px;">প্রচ্ছদ</th>
                        <th>ই-বুকের বিবরণ</th>
                        <th>বিষয়শ্রেণী</th>
                        <th>মূল্য ও ৫০% শেয়ার</th>
                        <th>বিক্রয় সংখ্যা</th>
                        <th>স্ট্যাটাস</th>
                        <th class="text-end">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody class="small">
                    @forelse($ebooks as $ebook)
                        <tr>
                            <td>
                                <img src="{{ $ebook->cover_url ?? 'https://placehold.co/100x140?text=Cover' }}" 
                                     alt="Cover" class="rounded object-fit-cover shadow-xs" style="width: 48px; height: 68px;">
                            </td>
                            <td>
                                <h6 class="fw-bold mb-0 text-dark">{{ $ebook->title }}</h6>
                                @if($ebook->subtitle)
                                    <small class="text-muted d-block text-truncate" style="max-width: 250px;">{{ $ebook->subtitle }}</small>
                                @endif
                                <div class="d-flex align-items-center gap-2 mt-1">
                                    <span class="badge bg-secondary-subtle text-secondary" style="font-size: 10px;">{{ $ebook->format_badge }}</span>
                                    @if($ebook->pages)
                                        <span class="text-muted" style="font-size: 11px;"><i class="fas fa-file-lines me-0.5"></i> {{ $ebook->pages }} পৃ.</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ $ebook->category?->name ?? 'জেনারেল' }}</span>
                            </td>
                            <td>
                                <div class="font-monospace fw-bold text-dark">৳{{ number_format($ebook->price, 2) }}</div>
                                <div class="small text-success fw-semibold font-monospace" style="font-size: 11px;">
                                    রয়্যালটি: ৳{{ number_format(($ebook->price * ($ebook->royalty_percentage ?: 50)) / 100, 2) }} (৫০%)
                                </div>
                            </td>
                            <td>
                                <strong class="text-primary fs-6">{{ number_format($ebook->sales_count ?? 0) }}</strong>
                                <span class="text-muted small d-block">কপি</span>
                            </td>
                            <td>
                                @if($ebook->mod_status === 'approved' && $ebook->is_active)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1">
                                        <i class="fas fa-circle-check me-1"></i> লাইভ স্টোরে প্রকাশিত
                                    </span>
                                @elseif($ebook->mod_status === 'rejected')
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1" 
                                          title="{{ $ebook->rejection_reason ?? 'এডমিন রিভিউ নোটস' }}" data-bs-toggle="tooltip">
                                        <i class="fas fa-circle-xmark me-1"></i> সংশোধন প্রয়োজন
                                    </span>
                                    @if($ebook->rejection_reason)
                                        <small class="d-block text-danger mt-1" style="font-size: 11px;">{{ $ebook->rejection_reason }}</small>
                                    @endif
                                @else
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2.5 py-1">
                                        <i class="fas fa-hourglass-half me-1"></i> অ্যাডমিন রিভিউতে আছে
                                    </span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('ebook.read', $ebook->slug ?: $ebook->id) }}" target="_blank" 
                                       class="btn btn-outline-primary" title="রিডার ভিউ">
                                        <i class="fas fa-book-open"></i>
                                    </a>
                                    <a href="{{ route('author.ebooks.edit', $ebook->id) }}" class="btn btn-outline-secondary" title="এডিট করুন">
                                        <i class="fas fa-pen-to-square"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fas fa-book-open fs-2 mb-2 d-block opacity-25"></i>
                                <h6 class="fw-bold mb-1">কোনো ই-বুক পাওয়া যায়নি</h6>
                                <p class="small text-muted mb-3">আপনার নতুন বইয়ের পাণ্ডুলিপি আপলোড করে সেলফ-পাবলিশিং শুরু করুন।</p>
                                <a href="{{ route('author.ebooks.create') }}" class="btn btn-sm btn-primary rounded-pill px-4 py-2 fw-semibold">
                                    <i class="fas fa-plus me-1"></i> নতুন ই-বুক যোগ করুন
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($ebooks->hasPages())
            <div class="p-3 border-top">
                {{ $ebooks->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
