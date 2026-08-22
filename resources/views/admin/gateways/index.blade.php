@extends('layouts.admin')

@section('title', 'Payment Gateway Reports & Transactions')
@section('heading', 'Payment Gateway Reports & Transaction Logs')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.payments.index') }}">Payment Gateways</a></li>
    <li class="breadcrumb-item active">Gateway Reports</li>
@endsection

@section('actions')
    <div class="d-flex align-items-center gap-2">
        <button type="button" class="btn btn-outline-success btn-sm rounded-pill px-3 shadow-xs" onclick="exportGatewayCSV()">
            <i class="fas fa-file-csv me-1"></i> Export (CSV)
        </button>
        <a href="{{ route('admin.payments.index') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-xs">
            <i class="fas fa-sliders me-1"></i> Gateway Settings
        </a>
    </div>
@endsection

@section('content')
<div class="d-flex flex-column gap-3">

    {{-- Gateway Summary Breakdown Cards --}}
    <div class="row g-2">
        {{-- bKash Total --}}
        <div class="col-6 col-md-3">
            <div class="adm-card p-3 h-100 bg-white rounded-4 shadow-sm border-0 border-start border-4" style="border-color: #d12053 !important;">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <small class="text-muted font-sans fw-bold">বিকাশ (bKash)</small>
                    <span class="badge rounded-pill text-white px-2 py-0.5" style="background: #d12053; font-size: 10px;">bKash PGW</span>
                </div>
                <h4 class="fw-bold mb-0 font-monospace" style="color: #d12053;">৳{{ number_format($bkashTotal, 2) }}</h4>
                <small class="text-muted" style="font-size: 11px;">সফল বিকাশ পেমেন্ট</small>
            </div>
        </div>

        {{-- Nagad Total --}}
        <div class="col-6 col-md-3">
            <div class="adm-card p-3 h-100 bg-white rounded-4 shadow-sm border-0 border-start border-4" style="border-color: #f7931e !important;">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <small class="text-muted font-sans fw-bold">নগদ (Nagad)</small>
                    <span class="badge rounded-pill text-white px-2 py-0.5" style="background: #f7931e; font-size: 10px;">Nagad API</span>
                </div>
                <h4 class="fw-bold mb-0 font-monospace" style="color: #ea580c;">৳{{ number_format($nagadTotal, 2) }}</h4>
                <small class="text-muted" style="font-size: 11px;">সফল নগদ পেমেন্ট</small>
            </div>
        </div>

        {{-- SSLCommerz / Cards --}}
        <div class="col-6 col-md-3">
            <div class="adm-card p-3 h-100 bg-white rounded-4 shadow-sm border-0 border-start border-4 border-primary">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <small class="text-muted font-sans fw-bold">কার্ড / SSLCommerz</small>
                    <span class="badge bg-primary text-white rounded-pill px-2 py-0.5" style="font-size: 10px;">Cards / NetBanking</span>
                </div>
                <h4 class="fw-bold text-primary mb-0 font-monospace">৳{{ number_format($sslTotal, 2) }}</h4>
                <small class="text-muted" style="font-size: 11px;">ভিসা, মাস্টারকার্ড ও নেটব্যাংক</small>
            </div>
        </div>

        {{-- Cash on Delivery --}}
        <div class="col-6 col-md-3">
            <div class="adm-card p-3 h-100 bg-white rounded-4 shadow-sm border-0 border-start border-4 border-success">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <small class="text-muted font-sans fw-bold">ক্যাশ অন ডেলিভারি (COD)</small>
                    <span class="badge bg-success text-white rounded-pill px-2 py-0.5" style="font-size: 10px;">COD</span>
                </div>
                <h4 class="fw-bold text-success mb-0 font-monospace">৳{{ number_format($codTotal, 2) }}</h4>
                <small class="text-muted" style="font-size: 11px;">ক্যাশ অন ডেলিভারি অর্ডার</small>
            </div>
        </div>
    </div>

    {{-- Filter & Search Toolbar --}}
    <div class="adm-card p-3 shadow-sm border-0 bg-white rounded-4">
        <form action="{{ route('admin.gateway-reports') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-12 col-md-3">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-search"></i></span>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control border-start-0" 
                           placeholder="Search Order #, TrxID, Phone...">
                </div>
            </div>
            <div class="col-6 col-md-2">
                <select name="gateway" class="form-select form-select-sm rounded-3">
                    <option value="">সকল গেটওয়ে (All)</option>
                    <option value="bkash" @selected(request('gateway') === 'bkash')>bKash</option>
                    <option value="nagad" @selected(request('gateway') === 'nagad')>Nagad</option>
                    <option value="sslcommerz" @selected(request('gateway') === 'sslcommerz' || request('gateway') === 'card')>SSLCommerz / Cards</option>
                    <option value="cod" @selected(request('gateway') === 'cod')>Cash on Delivery</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <select name="status" class="form-select form-select-sm rounded-3">
                    <option value="">সকল স্ট্যাটাস</option>
                    <option value="paid" @selected(request('status') === 'paid' || request('status') === 'success')>সফল / Paid</option>
                    <option value="pending" @selected(request('status') === 'pending')>অপেক্ষমাণ / Pending</option>
                    <option value="failed" @selected(request('status') === 'failed')>ব্যর্থ / Failed</option>
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
                @if(request()->hasAny(['search', 'gateway', 'status', 'from_date', 'to_date']))
                    <a href="{{ route('admin.gateway-reports') }}" class="btn btn-sm btn-outline-secondary rounded-pill" title="Reset">
                        <i class="fas fa-rotate-left"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Transaction Table --}}
    <div class="adm-card p-0 overflow-hidden shadow-sm border-0 rounded-4 bg-white">
        <div class="table-responsive">
            <table class="table adm-table align-middle mb-0" id="gatewayReportsTable">
                <thead class="table-light small fw-bold text-secondary">
                    <tr>
                        <th class="ps-4">তারিখ ও সময়</th>
                        <th>অর্ডার নম্বর</th>
                        <th>গ্রাহকের তথ্য</th>
                        <th>পেমেন্ট গেটওয়ে (Gateway)</th>
                        <th>ট্রানজেকশন আইডি (TrxID)</th>
                        <th class="text-end">টাকার পরিমাণ (Amount)</th>
                        <th class="text-center">পেমেন্ট স্ট্যাটাস</th>
                        <th class="text-end pe-4">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody class="small">
                    @forelse($orders as $order)
                        @php
                            $pm = strtolower($order->payment_method ?? 'cod');
                            $isPaid = in_array(strtolower($order->payment_status ?? $order->status), ['paid', 'completed', 'processing'], true);
                        @endphp
                        <tr>
                            <td class="ps-4 text-muted">{{ $order->created_at->format('d M, Y h:i A') }}</td>
                            <td>
                                <span class="badge bg-light text-dark border font-monospace">#{{ $order->order_number ?: $order->id }}</span>
                            </td>
                            <td>
                                <strong class="text-dark">{{ $order->customer_name }}</strong>
                                <small class="d-block text-muted font-monospace">{{ $order->customer_phone }}</small>
                            </td>
                            <td>
                                @if(str_contains($pm, 'bkash') || str_contains($pm, 'বিকাশ'))
                                    <span class="badge rounded-pill text-white px-2.5 py-1" style="background: #d12053;">
                                        <i class="fas fa-mobile-screen-button me-1"></i> bKash
                                    </span>
                                @elseif(str_contains($pm, 'nagad') || str_contains($pm, 'নগদ'))
                                    <span class="badge rounded-pill text-white px-2.5 py-1" style="background: #f7931e;">
                                        <i class="fas fa-mobile-screen-button me-1"></i> Nagad
                                    </span>
                                @elseif(str_contains($pm, 'card') || str_contains($pm, 'sslcommerz') || str_contains($pm, 'visa'))
                                    <span class="badge bg-primary rounded-pill px-2.5 py-1">
                                        <i class="fas fa-credit-card me-1"></i> SSLCommerz (Card)
                                    </span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2.5 py-1">
                                        <i class="fas fa-truck me-1"></i> Cash on Delivery
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($order->transaction_id)
                                    <span class="badge bg-light text-dark border font-monospace">{{ $order->transaction_id }}</span>
                                @else
                                    <span class="text-muted font-monospace small">—</span>
                                @endif
                            </td>
                            <td class="text-end fw-bold font-monospace fs-6 text-dark">
                                ৳{{ number_format($order->total_amount, 2) }}
                            </td>
                            <td class="text-center">
                                @if($isPaid)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1">
                                        <i class="fas fa-circle-check me-1"></i> Paid / Success
                                    </span>
                                @elseif($order->status === 'cancelled' || $order->payment_status === 'failed')
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1">
                                        Failed / Cancelled
                                    </span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2.5 py-1">
                                        Pending / Unpaid
                                    </span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <a href="{{ route('admin.ecommerce-orders.show', $order->id) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5 py-0.5" title="View Order">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="fas fa-credit-card fs-2 mb-2 d-block opacity-25"></i>
                                কোনো গেটওয়ে ট্রানজেকশন রেকর্ড পাওয়া যায়নি।
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
            <div class="p-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                <small class="text-muted">Showing {{ $orders->firstItem() }} to {{ $orders->lastItem() }} of {{ $orders->total() }} transactions</small>
                <div>{{ $orders->links() }}</div>
            </div>
        @endif
    </div>

</div>

@push('scripts')
<script>
function exportGatewayCSV() {
    const table = document.getElementById('gatewayReportsTable');
    let csv = [];
    const rows = table.querySelectorAll('tr');
    
    for (let i = 0; i < rows.length; i++) {
        let row = [], cols = rows[i].querySelectorAll('td, th');
        for (let j = 0; j < cols.length - 1; j++) {
            let data = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, '').replace(/(\s\s+)/gm, ' ');
            data = data.replace(/"/g, '""');
            row.push('"' + data + '"');
        }
        csv.push(row.join(','));
    }
    
    const csvFile = new Blob(["\uFEFF" + csv.join('\n')], { type: "text/csv;charset=utf-8;" });
    const downloadLink = document.createElement("a");
    downloadLink.download = "Idea_Gateway_Transactions_Report_" + new Date().toISOString().slice(0, 10) + ".csv";
    downloadLink.href = window.URL.createObjectURL(csvFile);
    downloadLink.style.display = "none";
    document.body.appendChild(downloadLink);
    downloadLink.click();
    document.body.removeChild(downloadLink);
}
</script>
@endpush
@endsection
