@extends('layouts.admin')

@section('title', 'Author Royalty Ledger & Management')
@section('heading', 'Author Royalty Management & Balances')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.ebooks') }}">E-Books</a></li>
    <li class="breadcrumb-item active">Royalty Management</li>
@endsection

@section('actions')
    <div class="d-flex align-items-center gap-2">
        <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 fw-bold shadow-xs" data-bs-toggle="modal" data-bs-target="#manualAdjustmentModal">
            <i class="fas fa-plus-minus me-1"></i> Manual Royalty Adjustment
        </button>
        <a href="{{ route('admin.ebook-sales-report') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-xs">
            <i class="fas fa-chart-pie me-1"></i> View Sales Report
        </a>
    </div>
@endsection

@section('content')
<div class="d-flex flex-column gap-3">

    {{-- Flash Notifications --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-0 shadow-xs rounded-4" role="alert">
            <i class="fas fa-circle-check fs-5 me-2 text-success"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Summary Cards --}}
    <div class="row g-2">
        <div class="col-6 col-md-3">
            <div class="adm-card p-3 h-100 bg-white rounded-4 shadow-sm border-0 border-start border-4 border-success">
                <small class="text-muted d-block font-sans">সর্বমোট অর্জিত রয়্যালটি (৫০%)</small>
                <h4 class="fw-bold text-success mb-0 font-monospace">৳{{ number_format($stats['total_earned'], 2) }}</h4>
                <small class="text-muted" style="font-size: 11px;">লেখকদের মোট প্রাপ্য আয়</small>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="adm-card p-3 h-100 bg-white rounded-4 shadow-sm border-0 border-start border-4 border-primary">
                <small class="text-muted d-block font-sans">ইতিমধ্যে পরিশোধিত (Paid Out)</small>
                <h4 class="fw-bold text-primary mb-0 font-monospace">৳{{ number_format($stats['total_paid'], 2) }}</h4>
                <small class="text-muted" style="font-size: 11px;">বিকাশ/নগদ/ব্যাংকে পরিশোধ</small>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="adm-card p-3 h-100 bg-white rounded-4 shadow-sm border-0 border-start border-4 border-warning">
                <small class="text-muted d-block font-sans">বর্তমান বকেয়া / ওয়ালেট ব্যালেন্স</small>
                <h4 class="fw-bold text-warning-emphasis mb-0 font-monospace">৳{{ number_format($stats['current_balance'], 2) }}</h4>
                <small class="text-muted" style="font-size: 11px;">লেখকদের ওয়ালেটে জমা</small>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="adm-card p-3 h-100 bg-white rounded-4 shadow-sm border-0 border-start border-4 border-danger">
                <small class="text-muted d-block font-sans">পেন্ডিং উত্তোলনের আবেদন</small>
                <h4 class="fw-bold text-danger mb-0 font-monospace">৳{{ number_format($stats['pending_payouts'], 2) }}</h4>
                <small class="text-muted" style="font-size: 11px;">
                    <a href="{{ route('admin.author-payouts.index') }}" class="text-danger text-decoration-none fw-semibold">ক্লিয়ার করুন &rarr;</a>
                </small>
            </div>
        </div>
    </div>

    {{-- Author Summary Table Card --}}
    <div class="adm-card p-0 overflow-hidden shadow-sm border-0 rounded-4 bg-white">
        <div class="adm-card__head d-flex flex-wrap align-items-center justify-content-between gap-3 p-3 p-md-4 border-bottom">
            <div>
                <h5 class="fw-bold mb-1 text-dark">লেখকভিত্তিক রয়্যালটি ব্যালেন্স সামারি (Author Balances)</h5>
                <p class="text-muted small mb-0">প্রতিটি লেখকের মোট অর্জিত রয়্যালটি, পরিশোধিত অর্থ ও বর্তমান উত্তোলনযোগ্য ব্যালেন্স।</p>
            </div>

            <form method="GET" action="{{ route('admin.author-royalties.index') }}" class="d-flex align-items-center gap-2">
                <div class="input-group input-group-sm">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control rounded-start-pill" placeholder="Search author name/phone...">
                    <button type="submit" class="btn btn-outline-secondary rounded-end-pill px-3"><i class="fas fa-search"></i></button>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table adm-table align-middle mb-0">
                <thead class="table-light small fw-bold text-secondary">
                    <tr>
                        <th class="ps-4">লেখক নাম ও যোগাযোগ</th>
                        <th>মোট বিক্রি (কপি)</th>
                        <th>অর্জিত রয়্যালটি (Total Earned)</th>
                        <th>পরিশোধিত (Paid Royalty)</th>
                        <th>বর্তমান ব্যালেন্স (Available)</th>
                        <th>পেমেন্ট তথ্য</th>
                        <th class="text-end pe-4">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody class="small">
                    @forelse($authors as $author)
                        <tr>
                            <td class="ps-4">
                                <strong class="text-dark fs-6">{{ $author->name }}</strong>
                                <small class="d-block text-muted font-monospace">{{ $author->phone ?: $author->email }}</small>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border font-monospace">{{ number_format($author->total_sales_count ?? 0) }} Copies</span>
                            </td>
                            <td class="fw-bold text-success font-monospace">
                                ৳{{ number_format($author->total_earned_sum ?? 0, 2) }}
                            </td>
                            <td class="text-secondary font-monospace">
                                ৳{{ number_format($author->total_paid_sum ?? ($author->total_payout_withdrawn ?? 0), 2) }}
                            </td>
                            <td>
                                <span class="badge bg-warning-subtle text-dark border border-warning-subtle font-monospace fs-6 px-2.5 py-1">
                                    ৳{{ number_format($author->wallet_balance ?? 0, 2) }}
                                </span>
                            </td>
                            <td>
                                @if($author->payout_account_type)
                                    <span class="badge bg-dark text-white text-uppercase">{{ $author->payout_account_type }}</span>
                                    <small class="d-block text-muted text-truncate font-monospace" style="max-width: 140px;">{{ $author->payout_account_details }}</small>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-primary" 
                                            onclick="openAdjustmentModal({{ $author->id }}, '{{ addslashes($author->name) }}')" title="Adjust Balance">
                                        <i class="fas fa-plus-minus"></i> Adjust
                                    </button>
                                    <a href="{{ route('admin.ebook-sales-report', ['author_id' => $author->id]) }}" class="btn btn-outline-secondary" title="View Sales">
                                        <i class="fas fa-list"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                কোনো লেখক রেকর্ড পাওয়া যায়নি।
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($authors->hasPages())
            <div class="p-3 border-top">
                {{ $authors->links() }}
            </div>
        @endif
    </div>

</div>

{{-- Manual Royalty Adjustment Modal --}}
<div class="modal fade text-start" id="manualAdjustmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form action="{{ route('admin.author-royalties.adjustment') }}" method="POST">
                @csrf
                <div class="modal-header border-bottom">
                    <h6 class="modal-title fw-bold text-dark">
                        <i class="fas fa-plus-minus text-primary me-1.5"></i> ম্যানুয়াল রয়্যালটি এডজাস্টমেন্ট (Credit / Debit)
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    {{-- Author Select --}}
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">লেখক নির্বাচন করুন <span class="text-danger">*</span></label>
                        <select name="author_id" id="modal_author_id" required class="form-select form-select-sm rounded-3">
                            <option value="">-- লেখক সিলেক্ট করুন --</option>
                            @foreach($authorList as $a)
                                <option value="{{ $a->id }}">{{ $a->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Adjustment Type --}}
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">এডজাস্টমেন্টের ধরন <span class="text-danger">*</span></label>
                        <select name="adjustment_type" required class="form-select form-select-sm rounded-3">
                            <option value="credit">ক্রেডিট (Credit +) — লেখকের ব্যালেন্সে টাকা যোগ করুন</option>
                            <option value="debit">ডেবিট (Debit -) — লেখকের ব্যালেন্স থেকে টাকা কর্তন করুন</option>
                        </select>
                    </div>

                    {{-- Amount --}}
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">টাকার পরিমাণ (৳) <span class="text-danger">*</span></label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light fw-bold">৳</span>
                            <input type="number" step="0.01" min="1" name="amount" required class="form-control font-monospace fw-semibold" placeholder="e.g. 500.00">
                        </div>
                    </div>

                    {{-- Reason / Note --}}
                    <div class="mb-2">
                        <label class="form-label small fw-bold text-dark">এডজাস্টমেন্টের কারণ ও বিবরণ <span class="text-danger">*</span></label>
                        <textarea name="reason" rows="3" required class="form-control form-control-sm rounded-3" placeholder="কেন এই ক্রেডিট বা ডেবিট দেওয়া হচ্ছে তা বিস্তারিত লিখুন..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-sm btn-light rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary rounded-pill px-4 fw-bold shadow-xs">Apply Adjustment</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function openAdjustmentModal(authorId, authorName) {
    const modal = new bootstrap.Modal(document.getElementById('manualAdjustmentModal'));
    const select = document.getElementById('modal_author_id');
    if (select) {
        select.value = authorId;
    }
    modal.show();
}
</script>
@endpush
@endsection
