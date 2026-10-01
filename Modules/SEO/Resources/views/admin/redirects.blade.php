@extends('layouts.admin')

@section('title', '৩০১ ইউআরএল রিডাইরেক্ট ম্যানেজার — Idea প্রকাশন')
@section('heading', '৩০১/৩০২ ইউআরএল রিডাইরেক্ট ম্যানেজার (SEO Redirects)')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.seo.index') }}">এসইও ম্যানেজার</a></li>
    <li class="breadcrumb-item active">রিডাইরেক্টস</li>
@endsection

@section('actions')
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('admin.seo.broken-links') }}" class="btn btn-outline-danger btn-sm rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-xs">
            <i class="fa-solid fa-triangle-exclamation"></i> ৪০৪ ব্রোকেন লিংক মনিটর
        </a>
        <a href="{{ route('admin.seo.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-xs">
            <i class="fa-solid fa-arrow-left"></i> এসইও ড্যাশবোর্ড
        </a>
    </div>
@endsection

@section('content')
<div class="seo-redirects-wrapper pb-4">

    <div class="row g-4 mb-4">
        <!-- Metric Cards -->
        <div class="col-md-6">
            <div class="card border-0 rounded-4 bg-white p-3.5 shadow-xs border h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold">মোট সক্রিয় রিডাইরেক্ট রুল</span>
                    <span class="badge bg-primary bg-opacity-10 text-primary rounded-circle p-2">
                        <i class="fa-solid fa-arrow-right-arrow-left"></i>
                    </span>
                </div>
                <div class="fs-4 fw-bold text-dark">{{ number_format($totalRedirects) }}টি</div>
                <div class="small text-muted mt-1">গুগল এসইও জুস ও ব্যাকলিংক সংরক্ষিত রাখে</div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 rounded-4 bg-white p-3.5 shadow-xs border h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold">সর্বমোট রিডাইরেক্ট ট্রাফিক হিট</span>
                    <span class="badge bg-success bg-opacity-10 text-success rounded-circle p-2">
                        <i class="fa-solid fa-chart-simple"></i>
                    </span>
                </div>
                <div class="fs-4 fw-bold text-success">{{ number_format($totalHits) }} বার</div>
                <div class="small text-muted mt-1">৪০৪ এরর এড়িয়ে সঠিক পেজে ভিজিটর পাঠানো হয়েছে</div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        
        <!-- Left: Add New Redirect Form -->
        <div class="col-lg-4">
            <div class="card border-0 rounded-4 bg-white shadow-xs border p-4">
                <h5 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                    <i class="fa-solid fa-plus-circle text-primary"></i>
                    <span>নতুন রিডাইরেক্ট তৈরি করুন</span>
                </h5>
                <form action="{{ route('admin.seo.redirects.store') }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark mb-1">সোর্স ইউআরএল বা পাথ (Old URL / Path)</label>
                        <input type="text" name="source_url" class="form-control rounded-3 font-monospace small" 
                               placeholder="যেমন: /old-book-name বা /old-category" required>
                        <div class="form-text small text-muted">যে পুরানো লিংকে ভিজিটর প্রবেশ করলে রিডাইরেক্ট হবে।</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark mb-1">টার্গেট ইউআরএল (Target URL)</label>
                        <input type="text" name="target_url" class="form-control rounded-3 font-monospace small" 
                               placeholder="যেমন: /books/new-slug বা https://..." required>
                        <div class="form-text small text-muted">যে নতুন পেজে ভিজিটরকে পাঠানো হবে।</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark mb-1">রিডাইরেক্ট স্ট্যাটাস কোড</label>
                        <select name="status_code" class="form-select rounded-3">
                            <option value="301" selected>301 Moved Permanently (গুগল এসইও রেকমেন্ডেড)</option>
                            <option value="302">302 Found / Temporary (সাময়িক)</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold text-dark mb-1">নোট / কারণ (ঐচ্ছিক)</label>
                        <input type="text" name="notes" class="form-control rounded-3" placeholder="যেমন: বইয়ের স্লাগ পরিবর্তন">
                    </div>

                    <button type="submit" class="btn btn-primary rounded-pill w-100 fw-bold shadow-xs">
                        <i class="fa-solid fa-floppy-disk me-1"></i> রিডাইরেক্ট রুল সেভ করুন
                    </button>
                </form>
            </div>
        </div>

        <!-- Right: Redirects List Table -->
        <div class="col-lg-8">
            <div class="card border-0 rounded-4 bg-white shadow-xs border overflow-hidden">
                <div class="card-header bg-white border-bottom p-3.5 d-flex align-items-center justify-content-between">
                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                        <i class="fa-solid fa-list-check text-success"></i> সক্রিয় রিডাইরেক্ট রুলস
                    </h6>
                    <span class="badge bg-light text-dark border">{{ $redirects->total() }}টি নিয়ম</span>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr class="text-uppercase text-muted small" style="font-size: 11px; letter-spacing: 0.5px;">
                                <th class="ps-4">স্ট্যাটাস</th>
                                <th>সোর্স পাথ (Source)</th>
                                <th>টার্গেট লিংক (Target)</th>
                                <th>হিটস (Hits)</th>
                                <th class="text-end pe-4">অ্যাকশন</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($redirects as $r)
                                <tr>
                                    <td class="ps-4">
                                        <span class="badge {{ $r->status_code == 301 ? 'bg-success' : 'bg-warning' }} rounded-pill px-2.5 py-1 small">
                                            {{ $r->status_code }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-bold font-monospace text-dark text-truncate" style="max-width: 220px;">
                                            {{ $r->source_url }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="font-monospace text-primary text-truncate" style="max-width: 220px;">
                                            <a href="{{ url($r->target_url) }}" target="_blank" class="text-decoration-none">
                                                {{ $r->target_url }} <i class="fa-solid fa-arrow-up-right-from-square small ms-1"></i>
                                            </a>
                                        </div>
                                        @if($r->notes)
                                            <div class="small text-muted">{{ $r->notes }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border rounded-pill px-2.5 py-1 font-monospace">
                                            {{ number_format($r->hits_count) }}
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <form action="{{ route('admin.seo.redirects.delete', $r->id) }}" method="POST" onsubmit="return confirm('আপনি কি নিশ্চিত যে এই রিডাইরেক্ট নিয়মটি মুছে ফেলতে চান?');" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-xs rounded-pill px-2.5 py-1" title="ডিলিট">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="fa-solid fa-arrow-right-arrow-left fs-1 text-muted mb-2 d-block opacity-25"></i>
                                        এখনও কোনো কাস্টম রিডাইরেক্ট তৈরি করা হয়নি।
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($redirects->hasPages())
                    <div class="card-footer bg-white border-top p-3 d-flex justify-content-center">
                        {{ $redirects->links() }}
                    </div>
                @endif
            </div>
        </div>

    </div>

</div>
@endsection
