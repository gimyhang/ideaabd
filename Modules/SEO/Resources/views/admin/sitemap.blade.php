@extends('layouts.admin')

@section('title', 'ডাইনামিক সাইটম্যাপ ও রোবটস ম্যানেজার — Idea প্রকাশন')
@section('heading', 'গুগল সাইটম্যাপ (Sitemap.xml) ও Robots.txt ম্যানেজার')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.seo.index') }}">এসইও ম্যানেজার</a></li>
    <li class="breadcrumb-item active">সাইটম্যাপ ও রোবটস</li>
@endsection

@section('actions')
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('admin.seo.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5">
            <i class="fa-solid fa-arrow-left"></i> এসইও ড্যাশবোর্ড
        </a>
    </div>
@endsection

@section('content')
<div class="seo-sitemap-wrapper pb-4">

    <div class="row g-4">
        
        <!-- Left: Sitemap Status & Links -->
        <div class="col-lg-6">
            <div class="card border-0 rounded-4 bg-white shadow-xs border p-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                        <i class="fa-solid fa-sitemap text-success"></i>
                        <span>XML সাইটম্যাপ স্ট্যাটাস</span>
                    </h5>
                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-3 py-1 fw-bold">
                        <i class="fa-solid fa-circle-check me-1"></i> লাইভ ও সক্রিয়
                    </span>
                </div>
                <p class="text-muted small mb-4">
                    আপনার ওয়েবসাইটের সকল বই, আইডিয়াপত্র ব্লগ, লেখক প্রোফাইল ও পেজের জন্য স্বয়ংক্রিয়ভাবে স্ট্যান্ডার্ড এক্সএমএল সাইটম্যাপ জেনারেট হচ্ছে।
                </p>

                <div class="p-3 bg-light rounded-3 border mb-4">
                    <label class="form-label small fw-bold text-dark mb-1">অফিসিয়াল সাইটম্যাপ ইউআরএল:</label>
                    <div class="input-group">
                        <input type="text" class="form-control font-monospace small bg-white" value="{{ $sitemapUrl }}" readonly id="sitemapUrlInput">
                        <button class="btn btn-outline-primary" type="button" onclick="navigator.clipboard.writeText('{{ $sitemapUrl }}'); alert('সাইটম্যাপ ইউআরএল কপি করা হয়েছে!');">
                            <i class="fa-solid fa-copy"></i> কপি
                        </button>
                        <a href="{{ $sitemapUrl }}" target="_blank" class="btn btn-primary">
                            <i class="fa-solid fa-arrow-up-right-from-square"></i> ভিউ
                        </a>
                    </div>
                </div>

                <h6 class="fw-bold text-dark mb-2">গুগল সার্চ কনসোল সাবমিশন গাইড:</h6>
                <ol class="small text-muted ps-3 mb-4" style="line-height: 1.8;">
                    <li><a href="https://search.google.com/search-console" target="_blank" class="text-primary text-decoration-none fw-semibold">Google Search Console</a> এ প্রবেশ করুন।</li>
                    <li>বামে <strong>Sitemaps</strong> মেনুতে ক্লিক করুন।</li>
                    <li><strong>Add a new sitemap</strong> বক্সে <code>sitemap.xml</code> লিখে <strong>Submit</strong> বাটনে চাপুন।</li>
                    <li>গুগল রোবট স্বয়ংক্রিয়ভাবে প্রতিদিন নতুন বই ও পোস্ট ইনডেক্স করবে।</li>
                </ol>
            </div>
        </div>

        <!-- Right: Robots.txt Preview -->
        <div class="col-lg-6">
            <div class="card border-0 rounded-4 bg-white shadow-xs border p-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                        <i class="fa-solid fa-robot text-primary"></i>
                        <span>Robots.txt ফাইল কনফিগারেশন</span>
                    </h5>
                    <a href="{{ $robotsUrl }}" target="_blank" class="btn btn-outline-primary btn-sm rounded-pill px-3 py-1">
                        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Robots.txt লাইভ
                    </a>
                </div>
                <p class="text-muted small mb-3">
                    সার্চ ইঞ্জিন ক্রলার ও বটসমূহের জন্য অ্যাক্সেস কন্ট্রোল ও এডমিন সিকিউরিটি ডিরেক্টিভস।
                </p>

                <div class="bg-dark text-light p-3.5 rounded-3 font-monospace small mb-4 overflow-x-auto" style="line-height: 1.6;">
                    <pre class="mb-0 text-success">{{ $robotsContent }}</pre>
                </div>

                <div class="alert alert-info border-0 rounded-3 small mb-0 d-flex align-items-center gap-2">
                    <i class="fa-solid fa-circle-info fs-5"></i>
                    <div>এডমিন প্যানেল, এপিআই ও সংবেদনশীল চেকআউট পাথ সার্চ ইঞ্জিনের ক্রল থেকে সুরক্ষিত রাখা হয়েছে।</div>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
