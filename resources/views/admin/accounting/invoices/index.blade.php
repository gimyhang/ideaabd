@extends('layouts.admin')

@php
    $settings = $invoiceSettings ?? \App\Http\Controllers\Admin\IdeaAccountingController::getInvoiceSettings();
    $bizLogo = $settings['logo'] ?? '/images/logo.png';
    $logoSrc = \App\Support\SiteSetting::resolveImageUrl($bizLogo, 'images/logo.png') ?: asset('images/logo.png');
@endphp

@section('title', 'আইডিয়া প্রকাশন বিল, চালান ও দরপত্র তালিকা')
@section('heading', 'আইডিয়া প্রকাশন বিল, চালান, কোটেশন ও দরপত্র তালিকা')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.accounting.index') }}">হিসাব ও আয়-ব্যয়</a></li>
    <li class="breadcrumb-item active" aria-current="page">বিল, চালান ও দরপত্র</li>
@endsection

@section('actions')
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('admin.accounting.invoices.create') }}" class="btn btn-success fw-semibold shadow-sm">
            <i class="fas fa-plus me-1"></i> নতুন তৈরি করুন
        </a>
        <a href="{{ route('admin.accounting.invoices.create', ['type' => 'quotation']) }}" class="btn btn-warning fw-semibold text-dark shadow-sm">
            <i class="fas fa-file-lines me-1"></i> + কোটেশন
        </a>
        <a href="{{ route('admin.accounting.invoices.create', ['type' => 'tender']) }}" class="btn btn-purple text-white fw-semibold shadow-sm" style="background-color: #6f42c1;">
            <i class="fas fa-landmark me-1"></i> + দরপত্র
        </a>
        <button type="button" class="btn btn-outline-dark fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#invoiceSettingsModal" title="ইনভয়েস ডিজাইন ও অফিশিয়াল তথ্য পরিবর্তন করুন">
            <i class="fas fa-palette me-1.5 text-primary"></i> ইনভয়েস ডিজাইন ও সেটিংস
        </button>
        <a href="{{ route('admin.accounting.index') }}" class="btn btn-outline-primary fw-semibold">
            <i class="fas fa-scale-balanced me-1"></i> আয়-ব্যয় খাতা
        </a>
    </div>
@endsection

@section('content')

{{-- Idea Accounting Unified Navigation Bar --}}
<div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
    <div class="card-body p-2 d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div class="nav nav-pills gap-1.5 flex-wrap">
            <a href="{{ route('admin.accounting.index') }}" 
               class="nav-link rounded-pill px-3.5 py-2 fw-semibold text-dark hover-bg-light">
                <i class="fas fa-scale-balanced me-1.5"></i> আয়-ব্যয় ও হিসাব খাতা
            </a>
            <a href="{{ route('admin.accounting.invoices.index') }}" 
               class="nav-link rounded-pill px-3.5 py-2 fw-semibold active bg-primary text-white shadow-sm">
                <i class="fas fa-file-invoice-dollar me-1.5"></i> বিল, চালান ও দরপত্র তালিকা
            </a>
            <a href="{{ route('admin.accounting.invoices.create') }}" 
               class="nav-link rounded-pill px-3.5 py-2 fw-semibold text-dark hover-bg-light">
                <i class="fas fa-file-circle-plus me-1.5"></i> নতুন বিল, চালান ও দরপত্র তৈরি
            </a>
        </div>

        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-semibold" data-bs-toggle="modal" data-bs-target="#invoiceSettingsModal">
            <i class="fas fa-sliders me-1 text-primary"></i> ইনভয়েস মেমো সেটিংস
        </button>
    </div>
</div>

{{-- Summary Stat Cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-primary">
            <span class="text-muted small fw-semibold">মোট ডকুমেন্টস</span>
            <h3 class="fw-bold mb-0 text-primary">@bn($stats['total_invoices']) টি</h3>
            <div class="text-muted small mt-1" style="font-size: 11.5px;">
                বিল: @bn($stats['total_bills']) | চালান: @bn($stats['total_challans'])
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4" style="border-left-color: #6f42c1 !important;">
            <span class="text-muted small fw-semibold">কোটেশন ও দরপত্র</span>
            <h3 class="fw-bold mb-0" style="color: #6f42c1;">@bn($stats['total_quotations'] + $stats['total_tenders']) টি</h3>
            <div class="text-muted small mt-1" style="font-size: 11.5px;">
                কোটেশন: @bn($stats['total_quotations']) | দরপত্র: @bn($stats['total_tenders'])
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-success">
            <span class="text-muted small fw-semibold">মোট আদায় / পরিশোধ</span>
            <h3 class="fw-bold mb-0 text-success">@taka($stats['total_paid'])</h3>
            <div class="text-muted small mt-1" style="font-size: 11.5px;">বিক্রয় মূল্য: @taka($stats['total_amount'])</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-danger">
            <span class="text-muted small fw-semibold">মোট বকেয়া (Due)</span>
            <h3 class="fw-bold mb-0 text-danger">@taka($stats['total_due'])</h3>
            <div class="text-muted small mt-1" style="font-size: 11.5px;">চালান ও বিলের বাকি</div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-3">
        <form action="{{ route('admin.accounting.invoices.index') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-md-3">
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="fas fa-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="ডকুমেন্ট # / প্রতিষ্ঠান / গ্রাহক..." value="{{ $search }}">
                </div>
            </div>
            <div class="col-md-2">
                <select name="type" class="form-select" onchange="this.form.submit()">
                    <option value="">সকল ধরন (All)</option>
                    <option value="invoice" @selected($type === 'invoice')>বিল / ক্যাশ মেমো</option>
                    <option value="challan" @selected($type === 'challan')>ডেলিভারি চালান</option>
                    <option value="quotation" @selected($type === 'quotation')>কোটেশন / প্রফর্মা</option>
                    <option value="tender" @selected($type === 'tender')>দরপত্র (Tender)</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="payment_status" class="form-select" onchange="this.form.submit()">
                    <option value="">পেমেন্ট স্ট্যাটাস</option>
                    <option value="paid" @selected($status === 'paid')>পরিশোধিত (Paid)</option>
                    <option value="partial" @selected($status === 'partial')>আংশিক বকেয়া</option>
                    <option value="unpaid" @selected($status === 'unpaid')>বকেয়া (Unpaid)</option>
                </select>
            </div>
            <div class="col-md-3">
                <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}" title="তারিখ">
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter me-1"></i> ফিল্টার</button>
                @if(request()->hasAny(['search', 'type', 'payment_status', 'date_from', 'date_to']))
                    <a href="{{ route('admin.accounting.invoices.index') }}" class="btn btn-light border" title="রিসেট"><i class="fas fa-rotate-left"></i></a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- Invoices Table --}}
<div class="adm-card shadow-sm rounded-4 overflow-hidden mb-4 bg-white">
    @if ($invoices->isEmpty())
        <div class="empty-state py-5 text-center">
            <i class="fas fa-file-invoice fs-1 text-muted opacity-50 mb-3"></i>
            <h5 class="fw-bold text-muted">কোনো ডকুমেন্ট পাওয়া যায়নি</h5>
            <p class="text-muted small">উপরের বাটনে ক্লিক করে নতুন বিল, ডেলিভারি চালান, কোটেশন বা দরপত্র তৈরি করুন।</p>
        </div>
    @else
        <div class="table-responsive">
            <table class="table adm-table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">ডকুমেন্ট নং #</th>
                        <th>ধরন</th>
                        <th>তারিখ</th>
                        <th>প্রতিষ্ঠান ও গ্রাহক তথ্য</th>
                        <th>আইটেম</th>
                        <th>মোট মূল্য</th>
                        <th>পরিশোধ</th>
                        <th>বকেয়া</th>
                        <th>স্ট্যাটাস</th>
                        <th class="text-center pe-3">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($invoices as $inv)
                        <tr>
                            <td class="ps-3 fw-bold text-primary font-monospace">
                                <a href="{{ route('admin.accounting.invoices.show', $inv->id) }}" class="text-decoration-none text-primary">
                                    {{ $inv->invoice_no }}
                                </a>
                                @if($inv->reference_no)
                                    <div class="text-muted small fw-normal" style="font-size: 11px;">স্মারক: {{ $inv->reference_no }}</div>
                                @endif
                            </td>
                            <td>
                                @if($inv->type === 'tender')
                                    <span class="badge border px-2.5 py-1 rounded-pill" style="background-color: #f3e8ff; color: #7e22ce; border-color: #d8b4fe;">
                                        <i class="fas fa-landmark me-1"></i>দরপত্র
                                    </span>
                                @elseif($inv->type === 'quotation')
                                    <span class="badge border px-2.5 py-1 rounded-pill" style="background-color: #fef3c7; color: #b45309; border-color: #fcd34d;">
                                        <i class="fas fa-file-lines me-1"></i>কোটেশন
                                    </span>
                                @elseif($inv->type === 'challan')
                                    <span class="badge bg-info-subtle text-dark border border-info-subtle px-2.5 py-1 rounded-pill">
                                        <i class="fas fa-truck me-1"></i>চালান
                                    </span>
                                @else
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 rounded-pill">
                                        <i class="fas fa-receipt me-1"></i>বিল / মেমো
                                    </span>
                                @endif
                            </td>
                            <td class="text-muted small">
                                @bnDate($inv->invoice_date)
                                @if($inv->valid_until)
                                    <div class="text-danger" style="font-size: 10.5px;">মেয়াদ: @bnDate($inv->valid_until)</div>
                                @endif
                            </td>
                            <td>
                                @if($inv->customer_org)
                                    <div class="fw-bold text-primary">
                                        <i class="fas fa-building me-1 text-primary opacity-75" style="font-size: 11px;"></i>{{ $inv->customer_org }}
                                    </div>
                                    <div class="text-dark small">
                                        <i class="fas fa-user me-1 text-muted" style="font-size: 10px;"></i>{{ $inv->customer_name }}
                                    </div>
                                @else
                                    <div class="fw-bold text-dark">
                                        <i class="fas fa-user me-1 text-primary opacity-75" style="font-size: 11px;"></i>{{ $inv->customer_name }}
                                    </div>
                                @endif

                                @if($inv->customer_phone)
                                    <div class="text-muted small" style="font-size: 11px;"><i class="fas fa-phone me-1"></i>{{ $inv->customer_phone }}</div>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">@bn(count($inv->items ?? [])) টি</span>
                            </td>
                            <td class="fw-bold text-dark">@taka($inv->grand_total)</td>
                            <td class="fw-bold text-success">
                                @if(in_array($inv->type, ['invoice', 'challan']))
                                    @taka($inv->paid_amount)
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td class="fw-bold {{ $inv->due_amount > 0 ? 'text-danger' : 'text-muted' }}">
                                @if(in_array($inv->type, ['invoice', 'challan']))
                                    @taka($inv->due_amount)
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td>
                                @if(in_array($inv->type, ['quotation', 'tender']))
                                    <span class="badge bg-light text-dark border px-2 py-1 rounded-pill">
                                        প্রস্তাবিত
                                    </span>
                                @elseif($inv->payment_status === 'paid')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 rounded-pill">
                                        পরিশোধিত
                                    </span>
                                @elseif($inv->payment_status === 'partial')
                                    <span class="badge bg-warning-subtle text-dark border border-warning-subtle px-2.5 py-1 rounded-pill">
                                        আংশিক বাকি
                                    </span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1 rounded-pill">
                                        বকেয়া
                                    </span>
                                @endif
                            </td>
                            <td class="text-center pe-3">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.accounting.invoices.show', $inv->id) }}" class="btn btn-outline-primary" title="দেখুন ও প্রিন্ট করুন">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.accounting.invoices.edit', $inv->id) }}" class="btn btn-outline-warning text-dark" title="সম্পাদন (Edit) করুন">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.accounting.invoices.destroy', $inv->id) }}" method="POST" class="d-inline" onsubmit="return confirm('আপনি কি নিশ্চিতভাবে এই ডকুমেন্টটি মুছে ফেলতে চান?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="মুছে ফেলুন">
                                            <i class="fas fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($invoices->hasPages())
            <div class="p-3 border-top d-flex justify-content-end">
                {{ $invoices->links() }}
            </div>
        @endif
    @endif
</div>

{{-- Invoice & Memo Header Settings / Design Modal --}}
<div class="modal fade" id="invoiceSettingsModal" tabindex="-1" aria-labelledby="invoiceSettingsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow">
            <form action="{{ route('admin.accounting.settings.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header border-bottom py-3">
                    <h5 class="modal-title fw-bold text-primary" id="invoiceSettingsModalLabel">
                        <i class="fas fa-palette me-2"></i>ইনভয়েস ডিজাইন ও মেমো ব্র্যান্ডিং সেটিংস
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    
                    {{-- Live Preview Header Card --}}
                    <div class="card border rounded-3 p-3 mb-4 bg-light">
                        <span class="small fw-bold text-muted text-uppercase mb-2 d-block"><i class="fas fa-eye me-1 text-primary"></i>ইনভয়েস হেডার লাইভ প্রিভিউ (Preview):</span>
                        <div class="d-flex align-items-center gap-3 p-2 bg-white rounded border">
                            <img src="{{ $logoSrc }}" alt="Logo Preview" style="height: 50px; max-width: 120px; object-fit: contain;">
                            <div>
                                <h4 class="fw-bold text-primary mb-0">{{ $settings['business_name'] ?? 'আইডিয়া প্রকাশন' }}</h4>
                                <p class="text-muted small mb-0">{{ $settings['tagline'] ?? 'বই প্রকাশনা, মুদ্রণ ও পরিবেশনা' }}</p>
                                <div class="text-muted small mt-0.5" style="font-size: 11.5px;">
                                    {{ $settings['address'] ?? 'ঢাকা, বাংলাদেশ' }} · মোবাইল: {{ $settings['phone'] ?? '018XXXXXXXX' }} · ইমেইল: {{ $settings['email'] ?? 'info@ideaabd.com' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">কোম্পানি / প্রকাশনীর নাম <span class="text-danger">*</span></label>
                            <input type="text" name="business_name" class="form-control" value="{{ $settings['business_name'] ?? 'আইডিয়া প্রকাশন' }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">ট্যাগলাইন / স্লোগান</label>
                            <input type="text" name="tagline" class="form-control" value="{{ $settings['tagline'] ?? 'বই প্রকাশনা, মুদ্রণ ও পরিবেশনা' }}" placeholder="বই প্রকাশনা, মুদ্রণ ও পরিবেশনা...">
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-semibold">অফিসের পূর্ণাঙ্গ ঠিকানা</label>
                            <input type="text" name="address" class="form-control" value="{{ $settings['address'] ?? 'ঢাকা, বাংলাদেশ' }}" placeholder="যেমন: সেন্ট্রাল রোড, রংপুর / ৩৮ বাংলাবাজার, ঢাকা...">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">অফিশিয়াল মোবাইল নম্বর</label>
                            <input type="text" name="phone" class="form-control" value="{{ $settings['phone'] ?? '018XXXXXXXX' }}" placeholder="017XXXXXXXX, 018XXXXXXXX">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">অফিশিয়াল ইমেইল ঠিকানা</label>
                            <input type="email" name="email" class="form-control" value="{{ $settings['email'] ?? 'info@ideaabd.com' }}" placeholder="info@ideaabd.com">
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-semibold">নতুন লোগো আপলোড করুন (Image Upload)</label>
                            <input type="file" name="logo_file" class="form-control" accept="image/*">
                            <div class="form-text small">পিএনজি (PNG) বা জেপিজি (JPG) লোগো আপলোড করতে পারেন। খালি রাখলে বর্তমান লোগো অপরিবর্তিত থাকবে।</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top py-2.5">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">বাতিল</button>
                    <button type="submit" class="btn btn-primary fw-semibold px-4">
                        <i class="fas fa-save me-1"></i> ডিজাইন ও সেটিংস সংরক্ষণ করুন
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
