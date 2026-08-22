@extends('author.layout')

@section('title', 'রয়্যালটি ও বিক্রয় হিসাব — লেখক পোর্টাল')
@section('heading', 'রয়্যালটি লেজার ও বিক্রয় বিবরণী (Royalty Ledger)')

@section('content')
<div class="d-flex flex-column gap-4">

    {{-- Stats Cards --}}
    <div class="row g-3">
        <div class="col-12 col-md-4">
            <div class="author-card p-3">
                <span class="text-muted small fw-semibold">সর্বমোট অর্জিত রয়্যালটি (৫০%)</span>
                <h3 class="fw-bold mb-0 text-success font-monospace mt-1">৳{{ number_format($totalEarned, 2) }}</h3>
                <small class="text-muted" style="font-size: 11px;">৫০% রয়্যালটি মডেল অনুযায়ী</small>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="author-card p-3">
                <span class="text-muted small fw-semibold">বর্তমান ওয়ালেট ব্যালেন্স</span>
                <h3 class="fw-bold mb-0 text-primary font-monospace mt-1">৳{{ number_format($author->wallet_balance ?? 0, 2) }}</h3>
                <small class="text-muted" style="font-size: 11px;">উত্তোলনযোগ্য জমা টাকা</small>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="author-card p-3">
                <span class="text-muted small fw-semibold">সর্বমোট উত্তোলিত অর্থ (Payouts)</span>
                <h3 class="fw-bold mb-0 text-secondary font-monospace mt-1">৳{{ number_format($author->total_payout_withdrawn ?? 0, 2) }}</h3>
                <small class="text-muted" style="font-size: 11px;">বিকাশ/নগদ/ব্যাংকে নেওয়া অর্থ</small>
            </div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="author-card p-3">
        <form method="GET" action="{{ route('author.royalties') }}" class="row g-2 align-items-center">
            <div class="col-12 col-md-6">
                <select name="ebook_id" class="form-select form-select-sm rounded-3">
                    <option value="">সকল ই-বুকের বিক্রয় হিসাব (All Books)</option>
                    @foreach($authorEbooks as $eb)
                        <option value="{{ $eb->id }}" @selected(request('ebook_id') == $eb->id)>{{ $eb->title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-4">
                <select name="status" class="form-select form-select-sm rounded-3">
                    <option value="">সকল স্ট্যাটাস</option>
                    <option value="earned" @selected(request('status') === 'earned')>অর্জিত (Earned)</option>
                    <option value="withdrawn" @selected(request('status') === 'withdrawn')>উত্তোলিত (Withdrawn)</option>
                    <option value="refunded" @selected(request('status') === 'refunded')>রিফান্ডেড (Refunded)</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <button type="submit" class="btn btn-sm btn-outline-secondary w-100 rounded-pill fw-semibold">
                    <i class="fas fa-filter me-1"></i> ফিল্টার
                </button>
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="author-card overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small fw-bold text-secondary">
                    <tr>
                        <th>তারিখ ও সময়</th>
                        <th>অর্ডার নম্বর</th>
                        <th>বইয়ের নাম</th>
                        <th>বিক্রয় মূল্য</th>
                        <th>রয়্যালটি পার্সেন্ট</th>
                        <th>লেখক রয়্যালটি (৫০%)</th>
                        <th>প্ল্যাটফর্ম শেয়ার</th>
                        <th>স্ট্যাটাস</th>
                    </tr>
                </thead>
                <tbody class="small">
                    @forelse($royalties as $r)
                        <tr>
                            <td class="text-muted">{{ $r->created_at->format('d M, Y h:i A') }}</td>
                            <td>
                                <span class="badge bg-light text-dark border font-monospace">#{{ $r->order?->order_number ?? $r->order_id }}</span>
                            </td>
                            <td>
                                <strong class="text-dark">{{ $r->ebook?->title ?? 'ই-বুক' }}</strong>
                            </td>
                            <td class="font-monospace">৳{{ number_format($r->sale_price, 2) }}</td>
                            <td>
                                <span class="badge bg-info-subtle text-info font-monospace">{{ $r->royalty_percentage }}%</span>
                            </td>
                            <td class="fw-bold text-success font-monospace fs-6">
                                +৳{{ number_format($r->royalty_amount, 2) }}
                            </td>
                            <td class="text-muted font-monospace">
                                ৳{{ number_format($r->platform_fee, 2) }}
                            </td>
                            <td>
                                @if($r->status === 'earned')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0.5">অর্জিত (Earned)</span>
                                @elseif($r->status === 'withdrawn')
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2 py-0.5">উত্তোলিত</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-0.5">বাতিল/রিফান্ড</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="fas fa-receipt fs-2 mb-2 d-block opacity-25"></i>
                                এখনও কোনো রয়্যালটি ট্রানজ্যাকশন জমা হয়নি।
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($royalties->hasPages())
            <div class="p-3 border-top">
                {{ $royalties->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
