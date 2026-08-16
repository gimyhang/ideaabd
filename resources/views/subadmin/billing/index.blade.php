@extends('layouts.admin')

@section('title', 'বিল তালিকা')
@section('heading', $isAdmin ? 'সকল বিল ও বিক্রয় তালিকা' : 'আমার বিলসমূহ')
@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">বিল তালিকা</li>
@endsection

@section('actions')
    <a href="{{ route('subadmin.bills.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i> নতুন বিল তৈরি
    </a>
@endsection

@section('content')
<div>

    {{-- Stats Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="adm-card p-3 d-flex align-items-center gap-3">
                <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 46px; height: 46px; font-size: 1.25rem;">
                    <i class="fas fa-file-invoice"></i>
                </div>
                <div>
                    <div class="small text-muted fw-semibold">সর্বমোট বিল</div>
                    <div class="fs-4 fw-bold text-dark">@bn($stats['total'])</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="adm-card p-3 d-flex align-items-center gap-3">
                <div class="rounded-circle bg-success-subtle text-success d-flex align-items-center justify-content-center" style="width: 46px; height: 46px; font-size: 1.25rem;">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div>
                    <div class="small text-muted fw-semibold">পরিশোধিত</div>
                    <div class="fs-4 fw-bold text-success">@bn($stats['paid'])</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="adm-card p-3 d-flex align-items-center gap-3">
                <div class="rounded-circle bg-danger-subtle text-danger d-flex align-items-center justify-content-center" style="width: 46px; height: 46px; font-size: 1.25rem;">
                    <i class="fas fa-clock"></i>
                </div>
                <div>
                    <div class="small text-muted fw-semibold">বকেয়া</div>
                    <div class="fs-4 fw-bold text-danger">@bn($stats['unpaid'])</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="adm-card p-3 d-flex align-items-center gap-3">
                <div class="rounded-circle bg-info-subtle text-info d-flex align-items-center justify-content-center" style="width: 46px; height: 46px; font-size: 1.25rem;">
                    <i class="fas fa-taka-sign"></i>
                </div>
                <div>
                    <div class="small text-muted fw-semibold">মোট আদায়</div>
                    <div class="fs-4 fw-bold text-info">৳@bn(number_format($stats['revenue'], 0))</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="adm-card p-3 mb-4">
        <form method="GET" action="{{ route('subadmin.bills.index') }}" class="row g-2 align-items-center">
            <div class="col-12 col-md-{{ $isAdmin ? '4' : '6' }}">
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="বিল নম্বর, গ্রাহকের নাম বা মোবাইল..." value="{{ request('search') }}">
                </div>
            </div>
            
            @if($isAdmin && count($sellers) > 0)
                <div class="col-6 col-md-3">
                    <select name="seller_id" class="form-select">
                        <option value="">— সব সেলার / স্টাফ —</option>
                        @foreach ($sellers as $sId => $sName)
                            <option value="{{ $sId }}" @selected((string)request('seller_id') === (string)$sId)>
                                {{ $sName }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="col-6 col-md-{{ $isAdmin ? '3' : '4' }}">
                <select name="payment_status" class="form-select">
                    <option value="">— সব পেমেন্ট স্ট্যাটাস —</option>
                    <option value="paid" @selected(request('payment_status') === 'paid')>পরিশোধিত (Paid)</option>
                    <option value="unpaid" @selected(request('payment_status') === 'unpaid')>বকেয়া (Unpaid)</option>
                    <option value="partial" @selected(request('payment_status') === 'partial')>আংশিক (Partial)</option>
                </select>
            </div>

            <div class="col-12 col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter me-1"></i>ফিল্টার</button>
                @if(request()->hasAny(['search', 'seller_id', 'payment_status']))
                    <a href="{{ route('subadmin.bills.index') }}" class="btn btn-outline-secondary" title="রিসেট"><i class="fas fa-rotate-left"></i></a>
                @endif
            </div>
        </form>
    </div>

    {{-- Bills Table --}}
    <div class="adm-card p-0 overflow-hidden">
        <div class="table-responsive mb-0">
            <table class="table table-hover align-middle mb-0" style="font-size: 0.92rem;">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3 py-3">বিল নম্বর</th>
                        <th class="py-3">গ্রাহক</th>
                        @if($isAdmin)<th class="py-3">সেলার</th>@endif
                        <th class="py-3 text-center">বই সংখ্যা</th>
                        <th class="py-3 text-end">মোট মূল্য</th>
                        <th class="py-3 text-center">পেমেন্ট</th>
                        <th class="py-3 text-center">তারিখ</th>
                        <th class="pe-3 py-3 text-end" style="width: 140px;">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bills as $bill)
                    <tr>
                        <td class="ps-3 fw-bold text-primary font-monospace">
                            <a href="{{ route('subadmin.bills.show', $bill) }}" class="text-decoration-none fw-bold">
                                #{{ $bill->bill_no }}
                            </a>
                        </td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $bill->customer_name }}</div>
                            <small class="text-muted">{{ $bill->customer_phone ?: $bill->customer_email }}</small>
                        </td>
                        @if($isAdmin)
                            <td>
                                <span class="badge bg-light text-dark border">
                                    <i class="fas fa-user-tie me-1 text-primary"></i>{{ $bill->seller->name ?? 'সেলার' }}
                                </span>
                            </td>
                        @endif
                        <td class="text-center">
                            <span class="badge bg-secondary-subtle text-secondary fw-semibold">
                                @bn(count($bill->items ?? [])) টি বই
                            </span>
                        </td>
                        <td class="text-end fw-bold text-dark fs-6">৳@bn(number_format($bill->total, 2))</td>
                        <td class="text-center">
                            @if($bill->payment_status === 'paid')
                                <span class="badge bg-success-subtle text-success border border-success-subtle">পরিশোধিত</span>
                            @elseif($bill->payment_status === 'partial')
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle">আংশিক</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">বকেয়া</span>
                            @endif
                        </td>
                        <td class="text-center small text-muted">
                            {{ $bill->created_at->format('d M Y') }}
                        </td>
                        <td class="pe-3 text-end">
                            <div class="d-inline-flex gap-1">
                                <a href="{{ route('subadmin.bills.show', $bill) }}" class="btn btn-sm btn-outline-primary py-1 px-2" title="ভিউ ইনভয়েস">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('subadmin.bills.edit', $bill) }}" class="btn btn-sm btn-outline-secondary py-1 px-2" title="সম্পাদনা করুন">
                                    <i class="fas fa-pen-to-square"></i>
                                </a>
                                <form method="POST" action="{{ route('subadmin.bills.destroy', $bill) }}" 
                                      onsubmit="return confirm('আপনি কি নিশ্চিত যে এই বিলটি মুছে ফেলতে চান?');" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger py-1 px-2" title="মুছে ফেলুন">
                                        <i class="fas fa-trash-can"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ $isAdmin ? '8' : '7' }}" class="text-center py-5 text-muted">
                            <i class="fas fa-file-invoice fs-2 mb-2 d-block text-muted opacity-50"></i>
                            কোনো বিল পাওয়া যায়নি — নতুন বিল তৈরি করুন।
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($bills->hasPages())
            <div class="px-3 py-3 border-top">
                {{ $bills->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
