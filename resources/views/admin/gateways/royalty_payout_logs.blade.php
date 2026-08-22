@extends('layouts.admin')

@section('title', 'Royalty Payout Gateway Logs')
@section('heading', 'Royalty Payout Gateway Logs & Disbursal Audit')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.author-payouts.index') }}">Royalty Payouts</a></li>
    <li class="breadcrumb-item active">Payout Gateway Logs</li>
@endsection

@section('actions')
    <div class="d-flex align-items-center gap-2">
        <button type="button" class="btn btn-outline-success btn-sm rounded-pill px-3 shadow-xs" onclick="exportPayoutLogsCSV()">
            <i class="fas fa-file-csv me-1"></i> Export (CSV)
        </button>
        <a href="{{ route('admin.author-payouts.index') }}" class="btn btn-primary btn-sm rounded-pill px-3 shadow-xs fw-bold">
            <i class="fas fa-hand-holding-dollar me-1"></i> Pending Payout Requests
        </a>
    </div>
@endsection

@section('content')
<div class="d-flex flex-column gap-3">

    {{-- Disbursal Gateway KPI Cards --}}
    <div class="row g-2">
        {{-- Total Disbursed --}}
        <div class="col-6 col-md-3">
            <div class="adm-card p-3 h-100 bg-white rounded-4 shadow-sm border-0 border-start border-4 border-success">
                <small class="text-muted d-block font-sans">সর্বমোট প্রেরিত রয়্যালটি (Net Paid)</small>
                <h4 class="fw-bold text-success mb-0 font-monospace">৳{{ number_format($stats['total_disbursed'], 2) }}</h4>
                <small class="text-muted" style="font-size: 11px;">সফলভাবে ডিসবার্সড</small>
            </div>
        </div>

        {{-- bKash Payouts --}}
        <div class="col-6 col-md-3">
            <div class="adm-card p-3 h-100 bg-white rounded-4 shadow-sm border-0 border-start border-4" style="border-color: #d12053 !important;">
                <small class="text-muted d-block font-sans">বিকাশ ডিসবার্সাল (bKash Payout)</small>
                <h4 class="fw-bold mb-0 font-monospace" style="color: #d12053;">৳{{ number_format($stats['bkash_payout'], 2) }}</h4>
                <small class="text-muted" style="font-size: 11px;">বিকাশ একাউন্টে পরিশোধ</small>
            </div>
        </div>

        {{-- Nagad Payouts --}}
        <div class="col-6 col-md-3">
            <div class="adm-card p-3 h-100 bg-white rounded-4 shadow-sm border-0 border-start border-4" style="border-color: #f7931e !important;">
                <small class="text-muted d-block font-sans">নগদ ডিসবার্সাল (Nagad Payout)</small>
                <h4 class="fw-bold mb-0 font-monospace" style="color: #ea580c;">৳{{ number_format($stats['nagad_payout'], 2) }}</h4>
                <small class="text-muted" style="font-size: 11px;">নগদ ওয়ালেটে পরিশোধ</small>
            </div>
        </div>

        {{-- Bank Transfers --}}
        <div class="col-6 col-md-3">
            <div class="adm-card p-3 h-100 bg-white rounded-4 shadow-sm border-0 border-start border-4 border-primary">
                <small class="text-muted d-block font-sans">ব্যাংক ট্রান্সফার (Bank Wire)</small>
                <h4 class="fw-bold text-primary mb-0 font-monospace">৳{{ number_format($stats['bank_payout'], 2) }}</h4>
                <small class="text-muted" style="font-size: 11px;">ব্যাংক একাউন্টে EFT/NPSB</small>
            </div>
        </div>
    </div>

    {{-- Filter Toolbar --}}
    <div class="adm-card p-3 shadow-sm border-0 bg-white rounded-4">
        <form action="{{ route('admin.royalty-payout-logs') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-12 col-md-3">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-search"></i></span>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control border-start-0" 
                           placeholder="Search TrxID, Author, Account...">
                </div>
            </div>
            <div class="col-6 col-md-2">
                <select name="channel" class="form-select form-select-sm rounded-3">
                    <option value="">সকল চ্যানেল (All Channels)</option>
                    <option value="bkash_api" @selected(request('channel') === 'bkash_api')>bKash Payout API</option>
                    <option value="nagad_api" @selected(request('channel') === 'nagad_api')>Nagad Payout API</option>
                    <option value="bank_api" @selected(request('channel') === 'bank_api')>Bank API / EFT</option>
                    <option value="manual" @selected(request('channel') === 'manual')>Manual Confirmation</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <select name="status" class="form-select form-select-sm rounded-3">
                    <option value="">সকল স্ট্যাটাস</option>
                    <option value="paid" @selected(request('status') === 'paid')>পরিশোধিত (Paid)</option>
                    <option value="pending" @selected(request('status') === 'pending')>অপেক্ষমাণ (Pending)</option>
                    <option value="rejected" @selected(request('status') === 'rejected')>বাতিল (Rejected)</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <input type="date" name="from_date" value="{{ request('from_date') }}" class="form-control form-control-sm rounded-3" title="From Date">
            </div>
            <div class="col-6 col-md-2">
                <input type="date" name="to_date" value="{{ request('to_date') }}" class="form-control form-control-sm rounded-3" title="To Date">
            </div>
            <div class="col-12 col-md-1 d-flex gap-1">
                <button type="submit" class="btn btn-sm btn-primary rounded-pill w-100 fw-bold shadow-xs">
                    <i class="fas fa-filter"></i>
                </button>
                @if(request()->hasAny(['search', 'channel', 'status', 'from_date', 'to_date']))
                    <a href="{{ route('admin.royalty-payout-logs') }}" class="btn btn-sm btn-outline-secondary rounded-pill" title="Reset">
                        <i class="fas fa-rotate-left"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Log Table --}}
    <div class="adm-card p-0 overflow-hidden shadow-sm border-0 rounded-4 bg-white">
        <div class="table-responsive">
            <table class="table adm-table align-middle mb-0" id="royaltyPayoutLogsTable">
                <thead class="table-light small fw-bold text-secondary">
                    <tr>
                        <th class="ps-4">তারিখ ও সময়</th>
                        <th>লেখক ও একাউন্ট তথ্য</th>
                        <th>পেমেন্ট মেথড / গেটওয়ে</th>
                        <th>গেটওয়ে TrxID / Ref</th>
                        <th>ট্যাক্স/TDS কর্তন</th>
                        <th class="text-end">প্রদত্ত অর্থ (Net Paid)</th>
                        <th class="text-center">স্ট্যাটাস</th>
                        <th class="text-end pe-4">ভাউচার</th>
                    </tr>
                </thead>
                <tbody class="small">
                    @forelse($payoutLogs as $log)
                        <tr>
                            <td class="ps-4 text-muted">{{ $log->processed_at ? $log->processed_at->format('d M, Y h:i A') : $log->created_at->format('d M, Y') }}</td>
                            <td>
                                <strong class="text-dark">{{ $log->author?->name ?? $log->user?->name }}</strong>
                                <small class="d-block text-muted font-monospace text-truncate" style="max-width: 180px;" title="{{ $log->account_details }}">
                                    {{ $log->account_details }}
                                </small>
                            </td>
                            <td>
                                @if(str_contains(strtolower($log->gateway_channel ?? $log->payment_method), 'bkash'))
                                    <span class="badge rounded-pill text-white px-2.5 py-1" style="background: #d12053;">
                                        bKash Payout
                                    </span>
                                @elseif(str_contains(strtolower($log->gateway_channel ?? $log->payment_method), 'nagad'))
                                    <span class="badge rounded-pill text-white px-2.5 py-1" style="background: #f7931e;">
                                        Nagad Payout
                                    </span>
                                @elseif(str_contains(strtolower($log->gateway_channel ?? $log->payment_method), 'bank'))
                                    <span class="badge bg-primary text-white rounded-pill px-2.5 py-1">
                                        Bank Transfer
                                    </span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2.5 py-1">
                                        {{ ucfirst($log->payment_method) }}
                                    </span>
                                @endif
                                @if(!empty($log->gateway_channel) && $log->gateway_channel !== 'manual')
                                    <small class="d-block text-success font-monospace" style="font-size: 10px;">
                                        <i class="fas fa-bolt me-0.5"></i> Automated API
                                    </small>
                                @endif
                            </td>
                            <td>
                                @if($log->transaction_ref)
                                    <span class="badge bg-light text-dark border font-monospace">{{ $log->transaction_ref }}</span>
                                @else
                                    <span class="text-muted font-monospace">—</span>
                                @endif
                            </td>
                            <td class="font-monospace text-danger">
                                @if($log->tax_deduction_amount > 0)
                                    -৳{{ number_format($log->tax_deduction_amount, 2) }}
                                @else
                                    ৳0.00
                                @endif
                            </td>
                            <td class="text-end fw-bold font-monospace fs-6 text-success">
                                ৳{{ number_format($log->net_payable_amount, 2) }}
                            </td>
                            <td class="text-center">
                                @if($log->status === 'paid')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1">
                                        <i class="fas fa-circle-check me-1"></i> Paid
                                    </span>
                                @elseif($log->status === 'rejected')
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1">
                                        Rejected
                                    </span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2.5 py-1">
                                        Pending
                                    </span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                @if($log->status === 'paid')
                                    <a href="{{ route('admin.author-payouts.receipt', $log->id) }}" target="_blank" 
                                       class="btn btn-sm btn-outline-success rounded-pill px-2.5 py-0.5" title="Print Official Receipt">
                                        <i class="fas fa-receipt me-1"></i> Receipt
                                    </a>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="fas fa-receipt fs-2 mb-2 d-block opacity-25"></i>
                                কোনো রয়্যালটি গেটওয়ে ট্রানজেকশন লগ পাওয়া যায়নি।
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($payoutLogs->hasPages())
            <div class="p-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                <small class="text-muted">Showing {{ $payoutLogs->firstItem() }} to {{ $payoutLogs->lastItem() }} of {{ $payoutLogs->total() }} payout logs</small>
                <div>{{ $payoutLogs->links() }}</div>
            </div>
        @endif
    </div>

</div>

@push('scripts')
<script>
function exportPayoutLogsCSV() {
    const table = document.getElementById('royaltyPayoutLogsTable');
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
    downloadLink.download = "Idea_Royalty_Payout_Gateway_Logs_" + new Date().toISOString().slice(0, 10) + ".csv";
    downloadLink.href = window.URL.createObjectURL(csvFile);
    downloadLink.style.display = "none";
    document.body.appendChild(downloadLink);
    downloadLink.click();
    document.body.removeChild(downloadLink);
}
</script>
@endpush
@endsection
