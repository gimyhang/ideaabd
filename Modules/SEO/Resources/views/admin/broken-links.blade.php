@extends('layouts.admin')

@section('title', '৪০৪ ব্রোকেন লিংক মনিটর — Idea প্রকাশন')
@section('heading', '৪০৪ নট ফাউন্ড ও ব্রোকেন লিংক মনিটর (404 Error Monitor)')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.seo.index') }}">এসইও ম্যানেজার</a></li>
    <li class="breadcrumb-item active">৪০৪ ব্রোকেন লিংকস</li>
@endsection

@section('actions')
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('admin.seo.redirects') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-xs">
            <i class="fa-solid fa-arrow-right-arrow-left"></i> ৩০১ রিডাইরেক্ট ম্যানেজার
        </a>
        <a href="{{ route('admin.seo.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-xs">
            <i class="fa-solid fa-arrow-left"></i> এসইও ড্যাশবোর্ড
        </a>
    </div>
@endsection

@section('content')
<div class="seo-broken-links-wrapper pb-4">

    <!-- Alert / Explanation Box -->
    <div class="card border-0 rounded-4 bg-white p-4 shadow-xs border mb-4">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h5 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                    <i class="fa-solid fa-triangle-exclamation text-danger"></i>
                    <span>৪০৪ ব্রোকেন লিংক ট্র্যাকিং ও অটো-ফিক্স</span>
                </h5>
                <p class="text-muted small mb-0">
                    ভিজিটর বা সার্চ ইঞ্জিন বট যেসব পেজে এসে ৪০৪ (Not Found) পেয়েছে তাদের তালিকা। এখান থেকে সরাসরি ১-ক্লিকে সঠিক পেজে ৩০১ রিডাইরেক্ট করে গুগল র‍্যাংকিং পেনাল্টি থেকে সাইটকে সুরক্ষিত রাখুন।
                </p>
            </div>
            <span class="badge {{ $totalBroken > 0 ? 'bg-danger' : 'bg-success' }} rounded-pill px-3 py-1.5 fw-bold fs-6">
                {{ number_format($totalBroken) }}টি ব্রোকেন লিংক
            </span>
        </div>
    </div>

    <!-- Broken Links Table -->
    <div class="card border-0 rounded-4 bg-white shadow-xs border overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr class="text-uppercase text-muted small" style="font-size: 11px; letter-spacing: 0.5px;">
                        <th class="ps-4">ব্যর্থ ইউআরএল পাথ (404 Missing URL)</th>
                        <th>হিটস (বার)</th>
                        <th>রেফারার (Referer)</th>
                        <th>সর্বশেষ রিকোয়েস্ট</th>
                        <th class="text-end pe-4" style="min-width: 250px;">সমাধান ও ৩০১ রিডাইরেক্ট</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($brokenLinks as $link)
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold font-monospace text-danger text-truncate" style="max-width: 300px;">
                                    <i class="fa-solid fa-link-slash me-1"></i>{{ $link->url }}
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-2.5 py-1 font-monospace fw-bold">
                                    {{ number_format($link->hits_count) }} বার
                                </span>
                            </td>
                            <td class="small text-muted text-truncate" style="max-width: 200px;">
                                {{ $link->referer ?: 'সরাসরি প্রবেশ / ক্রলার' }}
                            </td>
                            <td class="small text-muted">
                                {{ $link->last_hit_at ? $link->last_hit_at->diffForHumans() : '—' }}
                            </td>
                            <td class="text-end pe-4">
                                <form action="{{ route('admin.seo.broken-links.resolve', $link->id) }}" method="POST" class="d-flex align-items-center justify-content-end gap-1.5">
                                    @csrf
                                    <input type="text" name="target_url" class="form-control form-control-sm rounded-3 font-monospace" placeholder="সঠিক লিংক (যেমন: /books)" style="max-width: 180px;" required>
                                    <button type="submit" class="btn btn-success btn-sm rounded-pill px-3 fw-semibold text-nowrap" title="রিডাইরেক্ট করে সমাধান করুন">
                                        <i class="fa-solid fa-check me-1"></i> ফিক্স
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-circle-check fs-1 text-success mb-2 d-block opacity-50"></i>
                                চমৎকার! বর্তমানে কোনো সক্রিয় ৪০৪ ব্রোকেন লিংক নেই।
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($brokenLinks->hasPages())
            <div class="card-footer bg-white border-top p-3 d-flex justify-content-center">
                {{ $brokenLinks->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
