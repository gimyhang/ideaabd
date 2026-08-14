@extends('layouts.admin')

@section('title', 'বই রিকোয়েস্ট সমূহ')
@section('heading', 'ইউজারদের স্পেশাল বই রিকোয়েস্ট')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">অ্যাডমিন</a></li>
    <li class="breadcrumb-item active">বই রিকোয়েস্ট</li>
@endsection

@section('content')
<div class="adm-card">
    <div class="adm-card__head d-flex align-items-center justify-content-between">
        <h6 class="mb-0">সকল রিকোয়েস্ট</h6>
    </div>
    <div class="adm-card__body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="5%">ID</th>
                        <th width="20%">বইয়ের নাম</th>
                        <th width="15%">লেখক</th>
                        <th width="20%">কাস্টমার তথ্য</th>
                        <th width="20%">অতিরিক্ত তথ্য</th>
                        <th width="10%">স্ট্যাটাস</th>
                        <th width="10%">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $req)
                    <tr>
                        <td>#{{ $req->id }}</td>
                        <td class="fw-bold">{{ $req->book_title }}</td>
                        <td>{{ $req->author_name ?? '-' }}</td>
                        <td>
                            <div class="small fw-semibold">{{ $req->customer_name ?? 'অজ্ঞাত' }}</div>
                            <div class="small text-muted">{{ $req->customer_phone ?? '-' }}</div>
                        </td>
                        <td><small class="text-muted text-wrap" style="max-width: 200px; display: block;">{{ $req->additional_info ?? '-' }}</small></td>
                        <td>
                            @if($req->status === 'pending')
                                <span class="badge bg-warning text-dark">Pending</span>
                            @elseif($req->status === 'processing')
                                <span class="badge bg-info">Processing</span>
                            @elseif($req->status === 'available')
                                <span class="badge bg-success">Available</span>
                            @else
                                <span class="badge bg-secondary">Closed</span>
                            @endif
                        </td>
                        <td>
                            <form action="{{ route('admin.book-requests.update', $req->id) }}" method="POST" class="d-flex gap-1">
                                @csrf
                                @method('PATCH')
                                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="pending" {{ $req->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="processing" {{ $req->status === 'processing' ? 'selected' : '' }}>Processing</option>
                                    <option value="available" {{ $req->status === 'available' ? 'selected' : '' }}>Available</option>
                                    <option value="closed" {{ $req->status === 'closed' ? 'selected' : '' }}>Closed</option>
                                </select>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">কোনো রিকোয়েস্ট পাওয়া যায়নি।</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($requests->hasPages())
    <div class="adm-card__foot">
        {{ $requests->links() }}
    </div>
    @endif
</div>
@endsection
