@extends('layouts.admin')

@section('title', 'বিল তালিকা')
@section('heading', 'আমার বিলসমূহ')
@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">বিল তালিকা</li>
@endsection

@section('actions')
    <a href="{{ route('subadmin.bills.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i>নতুন বিল তৈরি</a>
@endsection

@section('content')
<div>

    {{-- Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0" style="background:linear-gradient(135deg,#E8F4F8,#D4E9F0)">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div><p class="text-muted small mb-1">মোট বিল</p><h3 class="fw-bold mb-0" style="color:#0066cc">{{ $stats['total'] }}</h3></div>
                    <i class="fas fa-file-invoice fa-2x" style="color:#0099ff;opacity:.3"></i>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0" style="background:linear-gradient(135deg,#E5FFE5,#D4FFD4)">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div><p class="text-muted small mb-1">পরিশোধিত</p><h3 class="fw-bold mb-0" style="color:#198754">{{ $stats['paid'] }}</h3></div>
                    <i class="fas fa-check-circle fa-2x" style="color:#198754;opacity:.3"></i>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0" style="background:linear-gradient(135deg,#FFE5E5,#FFD4D4)">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div><p class="text-muted small mb-1">বকেয়া</p><h3 class="fw-bold mb-0" style="color:#dc3545">{{ $stats['unpaid'] }}</h3></div>
                    <i class="fas fa-clock fa-2x" style="color:#dc3545;opacity:.3"></i>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0" style="background:linear-gradient(135deg,#E5F5FF,#D4EAFF)">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div><p class="text-muted small mb-1">মোট আয়</p><h3 class="fw-bold mb-0" style="color:#0099ff">৳{{ number_format($stats['revenue'],0) }}</h3></div>
                    <i class="fas fa-taka-sign fa-2x" style="color:#0099ff;opacity:.3"></i>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible"><button class="btn-close" data-bs-dismiss="alert"></button>{{ session('success') }}</div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead style="background:#E8F4F8">
                    <tr>
                        <th class="ps-4">বিল নম্বর</th>
                        <th>কাস্টমার</th>
                        <th>আইটেম</th>
                        <th>মোট</th>
                        <th>পেমেন্ট</th>
                        <th>তারিখ</th>
                        <th class="pe-4">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bills as $bill)
                    <tr>
                        <td class="ps-4 fw-bold text-primary small">{{ $bill->bill_no }}</td>
                        <td>
                            <p class="fw-semibold mb-0 small">{{ $bill->customer_name }}</p>
                            <small class="text-muted">{{ $bill->customer_phone }}</small>
                        </td>
                        <td class="small text-muted">{{ count($bill->items) }} টি বই</td>
                        <td class="fw-bold">৳{{ number_format($bill->total,2) }}</td>
                        <td>
                            @if($bill->payment_status==='paid') <span class="badge bg-success">পরিশোধিত</span>
                            @elseif($bill->payment_status==='partial') <span class="badge bg-warning text-dark">আংশিক</span>
                            @else <span class="badge bg-danger">বকেয়া</span>
                            @endif
                        </td>
                        <td class="small text-muted">{{ $bill->created_at->format('d M Y') }}</td>
                        <td class="pe-4">
                            <a href="{{ route('subadmin.bills.show', $bill) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center py-5 text-muted"><i class="fas fa-file-invoice fa-2x mb-2 d-block"></i>কোনো বিল নেই — নতুন বিল তৈরি করুন</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($bills->hasPages())<div class="px-4 py-3 border-top">{{ $bills->links() }}</div>@endif
    </div>
</div>
@endsection
