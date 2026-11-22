@extends('layouts.admin')

@section('title', 'Receipts')
@section('heading', 'Receipts')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.accounting.index') }}">Accounting</a></li>
    <li class="breadcrumb-item active">Receipts</li>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin-receipts.css') }}?v={{ @filemtime(public_path('css/admin-receipts.css')) }}">
@endpush

@section('content')
<div class="mr-page">
    @if(!$ready)
        <div class="alert alert-warning mb-0">
            <i class="fa-solid fa-database me-1"></i> রসিদের ডাটাবেজ টেবিল এখনো তৈরি হয়নি। <a href="{{ route('admin.dashboard') }}">Dashboard</a>-এর <b>Update</b> বাটনে ডাটাবেজ আপডেট করুন।
        </div>
    @else
        @if(session('success'))
            <div class="alert alert-success mb-0">{{ session('success') }} @if(session('print_url'))<a href="{{ session('print_url') }}" target="_blank" class="ms-2 fw-bold">Print</a>@endif</div>
        @endif

        <div class="mr-actions">
            <button type="button" class="mr-action is-pay" data-mr-open="honorarium">
                <i class="fa-solid fa-hand-holding-dollar"></i>
                <span><b>Honorarium</b><small>লেখককে সম্মানী প্রদান — রসিদসহ</small></span>
                <i class="fa-solid fa-arrow-right"></i>
            </button>
            <button type="button" class="mr-action is-get" data-mr-open="bill">
                <i class="fa-solid fa-receipt"></i>
                <span><b>Receipt</b><small>বিলের অর্থ গ্রহণ — মানি রিসিট</small></span>
                <i class="fa-solid fa-arrow-right"></i>
            </button>
        </div>

        <div class="mr-kpis">
            <div class="mr-kpi is-pay"><i class="fa-solid fa-feather-pointed"></i><div><strong>{{ \App\Support\Bn::money($stats['honorarium_month']) }}</strong><span>সম্মানী · এ মাসে</span></div></div>
            <div class="mr-kpi is-get"><i class="fa-solid fa-sack-dollar"></i><div><strong>{{ \App\Support\Bn::money($stats['bill_month']) }}</strong><span>গৃহীত · এ মাসে</span></div></div>
            <div class="mr-kpi is-pay"><i class="fa-solid fa-chart-line"></i><div><strong>{{ \App\Support\Bn::money($stats['honorarium_all']) }}</strong><span>মোট সম্মানী</span></div></div>
            <div class="mr-kpi is-get"><i class="fa-solid fa-file-invoice-dollar"></i><div><strong>{{ \App\Support\Bn::num($stats['count_month']) }}</strong><span>রসিদ · এ মাসে</span></div></div>
        </div>

        <section class="mr-card">
            <form class="mr-filter" method="GET">
                <div class="mr-seg">
                    @foreach(['' => 'All', 'honorarium' => 'Honorarium', 'bill' => 'Receipt'] as $k => $label)
                        <a href="{{ route('admin.receipts.index', array_filter(['kind' => $k, 'search' => $search, 'month' => $month])) }}" class="{{ $kind === $k ? 'is-on' : '' }}">{{ $label }}</a>
                    @endforeach
                </div>
                <input type="hidden" name="kind" value="{{ $kind }}">
                <input type="search" name="search" value="{{ $search }}" placeholder="নম্বর, নাম, ফোন, TrxID…">
                <input type="month" name="month" value="{{ $month }}" onchange="this.form.submit()" aria-label="Month">
                <button class="mr-btn" type="submit" style="height:36px"><i class="fa-solid fa-magnifying-glass"></i></button>
            </form>

            <div class="table-responsive">
                <table class="table mr-table">
                    <thead><tr><th>No</th><th>Type</th><th>Name</th><th>Purpose</th><th class="text-end">Amount</th><th>Method</th><th>Date</th><th class="text-end"></th></tr></thead>
                    <tbody>
                        @forelse($receipts as $r)
                            @php $pay = $r->isHonorarium(); @endphp
                            <tr>
                                <td class="mr-no">{{ $r->receipt_no }}</td>
                                <td><span class="mr-badge {{ $pay ? 'is-pay' : 'is-get' }}"><i class="fa-solid {{ $pay ? 'fa-arrow-up' : 'fa-arrow-down' }}"></i>{{ $pay ? 'Honorarium' : 'Receipt' }}</span></td>
                                <td>{{ $r->party_name }}<span class="mr-sub">{{ $r->party_phone }}</span></td>
                                <td>{{ \Illuminate\Support\Str::limit($r->purpose, 60) }}@if($r->invoice_id)<span class="mr-sub"><i class="fa-solid fa-link"></i> বিল #{{ $r->invoice?->invoice_no }}</span>@endif</td>
                                <td class="text-end mr-amt {{ $pay ? 'is-pay' : 'is-get' }}">{{ $pay ? '−' : '+' }}{{ \App\Support\Bn::money($r->amount) }}</td>
                                <td>{{ $r->methodLabel() }}@if($r->reference)<span class="mr-sub">{{ $r->reference }}</span>@endif</td>
                                <td class="text-nowrap">{{ $r->receipt_date->format('d M Y') }}</td>
                                <td class="text-end text-nowrap">
                                    <a class="mr-icon" href="{{ route('admin.receipts.print', $r) }}" target="_blank" title="Print"><i class="fa-solid fa-print"></i></a>
                                    <a class="mr-icon" href="{{ route('admin.receipts.print', $r) }}?copies=2" target="_blank" title="Print ×2"><i class="fa-solid fa-copy"></i></a>
                                    <form action="{{ route('admin.receipts.destroy', $r) }}" method="POST" class="d-inline" data-confirm="রসিদ {{ $r->receipt_no }} বাতিল হবে এবং হিসাব থেকেও বাদ যাবে{{ $r->invoice_id ? ' (বিলের বকেয়া আবার বাড়বে)' : '' }}।" data-confirm-title="Cancel receipt">
                                        @csrf @method('DELETE')
                                        <button class="mr-icon is-danger" title="Cancel"><i class="fa-regular fa-trash-can"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8"><div class="mr-empty"><i class="fa-solid fa-receipt"></i>এখনো কোনো রসিদ নেই। ওপরের বাটন থেকে প্রথমটি তৈরি করুন।</div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($receipts->hasPages())<div class="p-3">{{ $receipts->links() }}</div>@endif
        </section>
    @endif
</div>

@if($ready)
    @include('admin.receipts._modal')
@endif
@endsection

@push('scripts')
    <script src="{{ asset('js/admin-receipts.js') }}?v={{ @filemtime(public_path('js/admin-receipts.js')) }}"></script>
    <script>document.addEventListener('mr:saved', function () { document.getElementById('mrModal').addEventListener('hidden.bs.modal', function () { location.reload(); }, { once: true }); });</script>
@endpush
