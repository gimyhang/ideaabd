@extends('layouts.admin')

@section('title', 'বইয়ের অর্ডার')
@section('heading', 'ই-কমার্স অর্ডার ও গিফট')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">অ্যাডমিন</a></li>
    <li class="breadcrumb-item active">বইয়ের অর্ডার</li>
@endsection

@section('content')
<div class="adm-card">
    <div class="adm-card__head d-flex align-items-center justify-content-between">
        <h6 class="mb-0">সকল অর্ডার</h6>
    </div>
    <div class="adm-card__body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="5%">ID</th>
                        <th width="15%">বই</th>
                        <th width="20%">কাস্টমার তথ্য</th>
                        <th width="25%">গিফট তথ্য (যদি থাকে)</th>
                        <th width="15%">মোট বিল</th>
                        <th width="10%">স্ট্যাটাস</th>
                        <th width="10%">তারিখ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                    <tr>
                        <td>#{{ $order->id }}</td>
                        <td>
                            @if($order->book)
                                <a href="{{ route('book.show', $order->book->slug) }}" target="_blank" class="text-decoration-none fw-bold text-slate-800">{{ $order->book->title }}</a>
                            @else
                                <span class="text-muted">বই পাওয়া যায়নি</span>
                            @endif
                        </td>
                        <td>
                            <div class="fw-bold small">{{ $order->customer_name }}</div>
                            <div class="small text-muted"><i class="fas fa-phone-alt me-1"></i>{{ $order->customer_phone }}</div>
                            <div class="small text-muted"><i class="fas fa-map-marker-alt me-1"></i>{{ $order->district }} - {{ Str::limit($order->customer_address, 30) }}</div>
                        </td>
                        <td>
                            @if($order->is_gift)
                                <div class="p-2 bg-amber-50 rounded border border-amber-100">
                                    <div class="fw-bold small text-amber-800"><i class="fas fa-gift me-1"></i> {{ $order->gift_recipient_name }}</div>
                                    <div class="small text-muted"><i class="fas fa-phone-alt me-1"></i>{{ $order->gift_recipient_phone }}</div>
                                    <div class="small text-muted"><i class="fas fa-comment-dots me-1"></i>"{{ Str::limit($order->gift_message, 40) }}"</div>
                                </div>
                            @else
                                <span class="text-muted small">সাধারণ অর্ডার</span>
                            @endif
                        </td>
                        <td>
                            <div class="fw-bold text-indigo-600">৳ {{ number_format($order->total_amount) }}</div>
                        </td>
                        <td>
                            @if($order->status === 'pending')
                                <span class="badge bg-warning text-dark">Pending</span>
                            @elseif($order->status === 'processing')
                                <span class="badge bg-info">Processing</span>
                            @elseif($order->status === 'shipped')
                                <span class="badge bg-primary">Shipped</span>
                            @elseif($order->status === 'delivered')
                                <span class="badge bg-success">Delivered</span>
                            @else
                                <span class="badge bg-secondary">{{ ucfirst($order->status) }}</span>
                            @endif
                        </td>
                        <td>
                            <div class="small text-muted">{{ $order->created_at->format('d M, Y') }}</div>
                            <div class="small text-muted">{{ $order->created_at->format('h:i A') }}</div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">কোনো অর্ডার পাওয়া যায়নি।</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($orders->hasPages())
    <div class="adm-card__foot">
        {{ $orders->links() }}
    </div>
    @endif
</div>
@endsection
