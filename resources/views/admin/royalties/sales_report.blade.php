@extends('layouts.admin')

@section('title', 'E-Book Sales Report & Revenue')
@section('heading', 'E-Book Sales Report & Royalty Share (৫০% Model)')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.ebooks') }}">E-Books</a></li>
    <li class="breadcrumb-item active">Sales Report</li>
@endsection

@section('actions')
    <div class="d-flex align-items-center gap-2">
        <button type="button" class="btn btn-outline-success btn-sm rounded-pill px-3 shadow-xs" onclick="exportSalesToCSV()">
            <i class="fas fa-file-csv me-1"></i> Export (CSV)
        </button>
        <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-xs" onclick="window.print()">
            <i class="fas fa-print me-1"></i> Print Report
        </button>
    </div>
@endsection

@section('content')
<div class="d-flex flex-column gap-3">

    {{-- Summary Cards --}}
    <div class="row g-2">
        {{-- Total Revenue --}}
        <div class="col-6 col-md-3">
            <div class="adm-card p-3 h-100 bg-white rounded-4 shadow-sm border-0 border-start border-4 border-primary">
                <small class="text-muted d-block font-sans">মোট বিক্রয় রেভিনিউ</small>
                <h4 class="fw-bold text-dark mb-0 font-monospace">৳{{ number_format($totalRevenue, 2) }}</h4>
                <small class="text-muted" style="font-size: 11px;">পাঠকদের মোট পেমেন্ট</small>
            </div>
        </div>

        {{-- Platform 50% Share --}}
        <div class="col-6 col-md-3">
            <div class="adm-card p-3 h-100 bg-white rounded-4 shadow-sm border-0 border-start border-4 border-info">
                <small class="text-muted d-block font-sans">প্ল্যাটফর্ম শেয়ার (৫০%)</small>
                <h4 class="fw-bold text-info mb-0 font-monospace">৳{{ number_format($totalPlatformFee, 2) }}</h4>
                <small class="text-muted" style="font-size: 11px;">আইডিয়া প্রকাশন আয়</small>
            </div>
        </div>

        {{-- Author 50% Royalty --}}
        <div class="col-6 col-md-3">
            <div class="adm-card p-3 h-100 bg-white rounded-4 shadow-sm border-0 border-start border-4 border-success">
                <small class="text-muted d-block font-sans">লেখক রয়্যালটি (৫০%)</small>
                <h4 class="fw-bold text-success mb-0 font-monospace">৳{{ number_format($totalAuthorRoyalty, 2) }}</h4>
                <small class="text-muted" style="font-size: 11px;">লেখকদের মোট প্রাপ্য</small>
            </div>
        </div>

        {{-- Total Copies Sold --}}
        <div class="col-6 col-md-3">
            <div class="adm-card p-3 h-100 bg-white rounded-4 shadow-sm border-0 border-start border-4 border-warning">
                <small class="text-muted d-block font-sans">মোট বিক্রিত কপি</small>
                <h4 class="fw-bold text-warning-emphasis mb-0 font-monospace">{{ number_format($totalCopies) }}</h4>
                <small class="text-muted" style="font-size: 11px;">ডিজিটাল বিক্রয় সংখ্যা</small>
            </div>
        </div>
    </div>

    {{-- Filter Toolbar --}}
    <div class="adm-card p-3 shadow-sm border-0 bg-white rounded-4">
        <form action="{{ route('admin.ebook-sales-report') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-12 col-md-3">
                <label class="form-label small text-muted mb-1">From Date</label>
                <input type="date" name="from_date" value="{{ request('from_date') }}" class="form-control form-control-sm rounded-3">
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label small text-muted mb-1">To Date</label>
                <input type="date" name="to_date" value="{{ request('to_date') }}" class="form-control form-control-sm rounded-3">
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label small text-muted mb-1">Author Filter</label>
                <select name="author_id" class="form-select form-select-sm rounded-3">
                    <option value="">All Authors</option>
                    @foreach($authors as $aut)
                        <option value="{{ $aut->id }}" @selected(request('author_id') == $aut->id)>{{ $aut->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label small text-muted mb-1">E-Book Filter</label>
                <select name="ebook_id" class="form-select form-select-sm rounded-3">
                    <option value="">All E-Books</option>
                    @foreach($ebooks as $eb)
                        <option value="{{ $eb->id }}" @selected(request('ebook_id') == $eb->id)>{{ $eb->title }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-12 d-flex justify-content-end gap-2 pt-2 border-top">
                @if(request()->hasAny(['from_date', 'to_date', 'author_id', 'ebook_id']))
                    <a href="{{ route('admin.ebook-sales-report') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                        <i class="fas fa-rotate-left me-1"></i> Reset
                    </a>
                @endif
                <button type="submit" class="btn btn-sm btn-primary rounded-pill px-4 fw-bold shadow-xs">
                    <i class="fas fa-filter me-1"></i> Generate Report
                </button>
            </div>
        </form>
    </div>

    {{-- Sales Data Table --}}
    <div class="adm-card p-0 overflow-hidden shadow-sm border-0 rounded-4 bg-white">
        <div class="table-responsive">
            <table class="table adm-table align-middle mb-0" id="salesReportTable">
                <thead class="table-light small fw-bold text-secondary">
                    <tr>
                        <th class="ps-3">তারিখ ও সময়</th>
                        <th>অর্ডার #</th>
                        <th>ই-বুক শিরোনাম</th>
                        <th>লেখক (Author)</th>
                        <th>বিক্রয় মূল্য (৳)</th>
                        <th>প্ল্যাটফর্ম শেয়ার (৫০%)</th>
                        <th>লেখক রয়্যালটি (৫০%)</th>
                        <th class="text-center">স্ট্যাটাস</th>
                    </tr>
                </thead>
                <tbody class="small">
                    @forelse($sales as $sale)
                        <tr>
                            <td class="ps-3 text-muted">{{ $sale->created_at->format('d M, Y h:i A') }}</td>
                            <td>
                                <span class="badge bg-light text-dark border font-monospace">#{{ $sale->order?->order_number ?? $sale->order_id }}</span>
                            </td>
                            <td>
                                <strong class="text-dark">{{ $sale->ebook?->title ?? 'E-Book' }}</strong>
                            </td>
                            <td>
                                <span class="fw-semibold text-primary">{{ $sale->author?->name ?? $sale->user?->name ?? '—' }}</span>
                            </td>
                            <td class="fw-bold font-monospace fs-6">৳{{ number_format($sale->sale_price, 2) }}</td>
                            <td class="font-monospace text-info">৳{{ number_format($sale->platform_fee, 2) }}</td>
                            <td class="font-monospace fw-bold text-success">+৳{{ number_format($sale->royalty_amount, 2) }}</td>
                            <td class="text-center">
                                @if($sale->status === 'earned')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-0.5">Earned</span>
                                @elseif($sale->status === 'withdrawn')
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2.5 py-0.5">Withdrawn</span>
                                @else
                                    <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-2.5 py-0.5">{{ ucfirst($sale->status) }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="fas fa-chart-pie fs-2 mb-2 d-block opacity-25"></i>
                                কোনো সেলস ডাটা পাওয়া যায়নি।
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($sales->hasPages())
            <div class="p-3 border-top">
                {{ $sales->links() }}
            </div>
        @endif
    </div>

</div>

@push('scripts')
<script>
function exportSalesToCSV() {
    const table = document.getElementById('salesReportTable');
    let csv = [];
    const rows = table.querySelectorAll('tr');
    
    for (let i = 0; i < rows.length; i++) {
        let row = [], cols = rows[i].querySelectorAll('td, th');
        for (let j = 0; j < cols.length; j++) {
            let data = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, '').replace(/(\s\s+)/gm, ' ');
            data = data.replace(/"/g, '""');
            row.push('"' + data + '"');
        }
        csv.push(row.join(','));
    }
    
    const csvFile = new Blob(["\uFEFF" + csv.join('\n')], { type: "text/csv;charset=utf-8;" });
    const downloadLink = document.createElement("a");
    downloadLink.download = "Idea_Ebook_Sales_Report_" + new Date().toISOString().slice(0, 10) + ".csv";
    downloadLink.href = window.URL.createObjectURL(csvFile);
    downloadLink.style.display = "none";
    document.body.appendChild(downloadLink);
    downloadLink.click();
    document.body.removeChild(downloadLink);
}
</script>
@endpush
@endsection
