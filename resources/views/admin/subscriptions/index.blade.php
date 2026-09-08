@extends('layouts.admin')

@section('title', 'Idea Unlimited & Subscriptions — আইডিয়া আনলিমিটেড মেম্বারশিপ')
@section('heading', 'Idea Unlimited & Reader Subscriptions')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">ড্যাশবোর্ড</a></li>
    <li class="breadcrumb-item active">আনলিমিটেড সাবস্ক্রিপশন ও প্ল্যানস</li>
@endsection

@section('actions')
    <div class="d-flex flex-wrap align-items-center gap-2">
        <a href="{{ route('admin.author-royalties.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3 shadow-2xs fw-semibold">
            <i class="fas fa-scale-balanced me-1.5 text-warning"></i> রয়্যালটি পুল হিসাব
        </a>
        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3.5 shadow-2xs fw-semibold" data-bs-toggle="modal" data-bs-target="#grantSubModal">
            <i class="fas fa-user-plus me-1.5"></i> সাবস্ক্রিপশন প্রদান
        </button>
        <button type="button" class="btn btn-sm btn-primary rounded-pill px-3.5 shadow-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#addPlanModal">
            <i class="fas fa-plus-circle me-1.5"></i> নতুন প্ল্যান তৈরি
        </button>
    </div>
@endsection

@section('content')
<div class="d-flex flex-column gap-4">

    <!-- 1. KPI Summary Hero Cards -->
    <div class="row g-3">
        {{-- Card 1: Active Subscribers --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi bg-white shadow-2xs border-start border-4 border-primary rounded-4" style="--bar: #0066cc;">
                <div class="kpi__icon bg-primary-subtle text-primary rounded-3"><i class="fas fa-crown"></i></div>
                <p class="kpi__label fw-semibold text-muted mb-1">সক্রিয় মেম্বার (Active Members)</p>
                <h3 class="kpi__value text-dark fw-bold mb-1">{{ number_format($activeSubscribersCount) }}</h3>
                <p class="kpi__foot text-muted small mb-0"><i class="fas fa-users me-1 text-primary"></i>Kindle Unlimited মডেল সদস্য</p>
            </div>
        </div>

        {{-- Card 2: Total Revenue --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi bg-white shadow-2xs border-start border-4 border-success rounded-4" style="--bar: #16a34a;">
                <div class="kpi__icon bg-success-subtle text-success rounded-3"><i class="fas fa-sack-dollar"></i></div>
                <p class="kpi__label fw-semibold text-muted mb-1">সাবস্ক্রিপশন আয় (Revenue)</p>
                <h3 class="kpi__value text-dark fw-bold mb-1">৳{{ number_format($totalSubscriptionRevenue, 2) }}</h3>
                <p class="kpi__foot text-muted small mb-0"><i class="fas fa-chart-line me-1 text-success"></i>রিক্যারিং পাঠক মেম্বারশিপ ফি</p>
            </div>
        </div>

        {{-- Card 3: Pages Read --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi bg-white shadow-2xs border-start border-4 border-warning rounded-4" style="--bar: #ff6b35;">
                <div class="kpi__icon bg-warning-subtle text-warning rounded-3"><i class="fas fa-book-open-reader"></i></div>
                <p class="kpi__label fw-semibold text-muted mb-1">পঠিত পৃষ্ঠা (Pages Read)</p>
                <h3 class="kpi__value text-dark fw-bold mb-1">{{ number_format($totalPagesReadThisMonth) }}</h3>
                <p class="kpi__foot text-muted small mb-0"><i class="fas fa-calendar-check me-1 text-warning"></i>চলতি মাসে রয়্যালটি ফান্ড গণনা</p>
            </div>
        </div>

        {{-- Card 4: Active Plans --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi bg-white shadow-2xs border-start border-4 border-info rounded-4" style="--bar: #0099ff;">
                <div class="kpi__icon bg-info-subtle text-info rounded-3"><i class="fas fa-layer-group"></i></div>
                <p class="kpi__label fw-semibold text-muted mb-1">সক্রিয় প্ল্যান (Active Plans)</p>
                <h3 class="kpi__value text-dark fw-bold mb-1">{{ $activePlansCount }} <span class="fs-6 fw-normal text-muted">টি প্যাকেজ</span></h3>
                <p class="kpi__foot text-muted small mb-0"><i class="fas fa-cubes me-1 text-info"></i>মোট {{ $plans->count() }}টি প্যাকেজ কনফিগার করা</p>
            </div>
        </div>
    </div>

    <!-- 2. Membership & Reading Plans Grid -->
    <div class="adm-card bg-white shadow-2xs rounded-4 border overflow-hidden">
        <div class="adm-card__head d-flex flex-wrap justify-content-between align-items-center gap-2 p-3.5 border-bottom bg-light bg-opacity-50">
            <div>
                <h6 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                    <i class="fas fa-layer-group text-primary"></i> 
                    আইডিয়া মেম্বারশিপ ও রিডিং প্যাকেজসমূহ (Membership Plans)
                </h6>
                <small class="text-muted">পাঠকদের জন্য আনলিমিটেড ই-বুক ও ডিজিটাল পড়ার প্ল্যান তালিকা</small>
            </div>
            <button type="button" class="btn btn-sm btn-primary rounded-pill px-3.5 shadow-2xs fw-semibold" data-bs-toggle="modal" data-bs-target="#addPlanModal">
                <i class="fas fa-plus me-1.5"></i> নতুন প্ল্যান যোগ করুন
            </button>
        </div>
        <div class="adm-card__body p-4">
            <div class="row g-3.5">
                @forelse($plans as $plan)
                    <div class="col-12 col-md-6 col-xl-4">
                        <div class="card h-100 rounded-4 border {{ $plan->is_featured ? 'border-2 border-primary shadow-sm' : 'border-slate-200' }} position-relative overflow-hidden transition-all">
                            @if($plan->is_featured)
                                <div class="position-absolute top-0 end-0 bg-primary text-white text-uppercase fw-bold px-3 py-1 rounded-bottom-start-3" style="font-size: 0.68rem; letter-spacing: 0.5px;">
                                    <i class="fas fa-star me-1 text-warning"></i> জনপ্রিয় (Popular)
                                </div>
                            @endif

                            <div class="card-body p-4 d-flex flex-column justify-content-between">
                                <div>
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span class="badge {{ $plan->is_active ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }} rounded-pill px-2.5 py-1 fw-bold" style="font-size: 0.72rem;">
                                            <i class="fas fa-circle {{ $plan->is_active ? 'text-success' : 'text-secondary' }} me-1" style="font-size: 6px;"></i>
                                            {{ $plan->is_active ? 'সক্রিয় প্ল্যান (Active)' : 'নিষ্ক্রিয় (Inactive)' }}
                                        </span>
                                        <span class="text-muted small fw-semibold">
                                            <i class="fas fa-users text-primary me-1"></i>{{ $plan->subscriptions_count }} জন মেম্বার
                                        </span>
                                    </div>

                                    <h5 class="fw-bold text-dark mb-1">{{ $plan->name }}</h5>
                                    <p class="text-muted small mb-3 text-break">{{ $plan->description ?: 'কোনো বিবরণ দেওয়া হয়নি।' }}</p>

                                    <div class="p-3 bg-light rounded-3 mb-3 border">
                                        <div class="d-flex align-items-baseline gap-2">
                                            <h2 class="fw-bold text-primary mb-0 font-monospace">৳{{ number_format($plan->price_bdt, 0) }}</h2>
                                            <span class="text-muted small fw-semibold">/ {{ $plan->duration_days }} দিন</span>
                                            <span class="badge bg-white border text-secondary ms-auto font-monospace">${{ number_format($plan->price_usd, 2) }}</span>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <small class="text-uppercase fw-bold text-muted d-block mb-2" style="font-size: 0.7rem; letter-spacing: 0.5px;">প্ল্যানের সুবিধাসমূহ:</small>
                                        <ul class="list-unstyled small text-secondary mb-0 d-flex flex-column gap-2">
                                            <li class="d-flex align-items-center gap-2">
                                                <i class="fas fa-mobile-screen-button text-primary"></i> 
                                                সর্বোচ্চ <strong>{{ $plan->max_devices }}টি ডিভাইসে</strong> একসাথে ব্যবহার
                                            </li>
                                            <li class="d-flex align-items-center gap-2">
                                                <i class="fas fa-shield-halved text-success"></i> 
                                                DRM সুরক্ষিত ক্লাউড ই-রিডার সুবিধা
                                            </li>
                                            <li class="d-flex align-items-center gap-2">
                                                <i class="fas fa-{{ $plan->unlimited_ebooks ? 'check-circle text-success' : 'times-circle text-muted' }}"></i> 
                                                সীমাহীন ই-বুক পাঠের সুবিধা
                                            </li>
                                            <li class="d-flex align-items-center gap-2">
                                                <i class="fas fa-{{ $plan->unlimited_webzines ? 'check-circle text-success' : 'times-circle text-muted' }}"></i> 
                                                সকল ওয়েবজিন ও ম্যাগাজিন এক্সেস
                                            </li>
                                            @if($plan->unlimited_audiobooks)
                                                <li class="d-flex align-items-center gap-2">
                                                    <i class="fas fa-headphones text-info"></i> 
                                                    অডিওবুক এক্সেস অন্তর্ভুক্ত
                                                </li>
                                            @endif
                                        </ul>
                                    </div>
                                </div>

                                <div class="pt-3 border-top mt-2 d-flex align-items-center justify-content-between gap-2">
                                    <form action="{{ route('admin.subscriptions.plans.toggle', $plan) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-xs {{ $plan->is_active ? 'btn-outline-warning' : 'btn-outline-success' }} rounded-pill px-2.5 py-1 fw-semibold">
                                            <i class="fas fa-power-off me-1"></i>{{ $plan->is_active ? 'নিষ্ক্রিয় করুন' : 'সক্রিয় করুন' }}
                                        </button>
                                    </form>

                                    <div class="d-flex align-items-center gap-1">
                                        <button type="button" class="btn btn-xs btn-outline-primary rounded-pill px-2.5 py-1 fw-semibold" data-bs-toggle="modal" data-bs-target="#editPlanModal{{ $plan->id }}">
                                            <i class="fas fa-pen me-1"></i> এডিট
                                        </button>
                                        <form action="{{ route('admin.subscriptions.plans.destroy', $plan) }}" method="POST" class="d-inline" onsubmit="return confirm('আপনি কি নিশ্চিত যে এই প্ল্যানটি মুছে ফেলতে চান?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-xs btn-outline-danger rounded-pill px-2.5 py-1 fw-semibold" title="Delete Plan">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Edit Plan Modal for each plan -->
                    <div class="modal fade" id="editPlanModal{{ $plan->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
                                <div class="modal-header bg-primary text-white py-3 px-4">
                                    <h6 class="modal-title fw-bold text-white mb-0">
                                        <i class="fas fa-pen-to-square me-1.5"></i> প্ল্যান সম্পাদনা: {{ $plan->name }}
                                    </h6>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form action="{{ route('admin.subscriptions.plans.update', $plan) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-body p-4">
                                        <div class="mb-3">
                                            <label class="form-label small fw-bold">প্ল্যানের নাম (Plan Name) <span class="text-danger">*</span></label>
                                            <input type="text" name="name" class="form-control" value="{{ $plan->name }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label small fw-bold">সংক্ষিপ্ত বিবরণ (Description)</label>
                                            <textarea name="description" rows="2" class="form-control">{{ $plan->description }}</textarea>
                                        </div>
                                        <div class="row g-2 mb-3">
                                            <div class="col-6">
                                                <label class="form-label small fw-bold">মূল্য BDT (৳) <span class="text-danger">*</span></label>
                                                <input type="number" step="0.01" name="price_bdt" class="form-control fw-bold" value="{{ $plan->price_bdt }}" required>
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label small fw-bold">মূল্য USD ($) <span class="text-danger">*</span></label>
                                                <input type="number" step="0.01" name="price_usd" class="form-control fw-bold" value="{{ $plan->price_usd }}" required>
                                            </div>
                                        </div>
                                        <div class="row g-2 mb-3">
                                            <div class="col-6">
                                                <label class="form-label small fw-bold">মেয়াদ (দিন) <span class="text-danger">*</span></label>
                                                <input type="number" name="duration_days" value="{{ $plan->duration_days }}" class="form-control" required>
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label small fw-bold">ডিভাইস সংখ্যা <span class="text-danger">*</span></label>
                                                <input type="number" name="max_devices" value="{{ $plan->max_devices }}" class="form-control" required>
                                            </div>
                                        </div>
                                        <div class="p-3 bg-light rounded-3 border mb-2">
                                            <div class="form-check form-switch mb-2">
                                                <input class="form-check-input" type="checkbox" name="unlimited_ebooks" value="1" id="editEbooks{{ $plan->id }}" {{ $plan->unlimited_ebooks ? 'checked' : '' }}>
                                                <label class="form-check-label small fw-semibold" for="editEbooks{{ $plan->id }}">আনলিমিটেড ই-বুক এক্সেস</label>
                                            </div>
                                            <div class="form-check form-switch mb-2">
                                                <input class="form-check-input" type="checkbox" name="unlimited_webzines" value="1" id="editWebzines{{ $plan->id }}" {{ $plan->unlimited_webzines ? 'checked' : '' }}>
                                                <label class="form-check-label small fw-semibold" for="editWebzines{{ $plan->id }}">ওয়েবজিন ও ম্যাগাজিন এক্সেস</label>
                                            </div>
                                            <div class="form-check form-switch mb-2">
                                                <input class="form-check-input" type="checkbox" name="unlimited_audiobooks" value="1" id="editAudiobooks{{ $plan->id }}" {{ $plan->unlimited_audiobooks ? 'checked' : '' }}>
                                                <label class="form-check-label small fw-semibold" for="editAudiobooks{{ $plan->id }}">অডিওবুক এক্সেস</label>
                                            </div>
                                            <div class="form-check form-switch mb-2">
                                                <input class="form-check-input" type="checkbox" name="is_featured" value="1" id="editFeatured{{ $plan->id }}" {{ $plan->is_featured ? 'checked' : '' }}>
                                                <label class="form-check-label small fw-semibold text-warning" for="editFeatured{{ $plan->id }}">জনপ্রিয় হিসেবে প্রদর্শন (Featured / Popular)</label>
                                            </div>
                                            <div class="form-check form-switch mb-0">
                                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="editActive{{ $plan->id }}" {{ $plan->is_active ? 'checked' : '' }}>
                                                <label class="form-check-label small fw-semibold text-success" for="editActive{{ $plan->id }}">প্ল্যান সক্রিয় রাখুন (Active)</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer bg-light py-2.5 px-4">
                                        <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-bs-dismiss="modal">বাতিল</button>
                                        <button type="submit" class="btn btn-sm btn-primary rounded-pill px-4 fw-bold">আপডেট করুন</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="p-5 text-center text-muted border rounded-4 bg-light">
                            <i class="fas fa-layer-group fs-2 mb-2 d-block opacity-50"></i>
                            <h6 class="fw-bold">কোনো সাবস্ক্রিপশন প্ল্যান তৈরি করা হয়নি</h6>
                            <p class="small mb-3">পাঠকদের জন্য নতুন মেম্বারশিপ প্যাকেজ তৈরি করতে ওপরের বাটনে ক্লিক করুন।</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- 3. Active Subscribers Table Section -->
    <div class="adm-card bg-white shadow-2xs rounded-4 border overflow-hidden">
        <div class="adm-card__head d-flex flex-wrap justify-content-between align-items-center gap-3 p-3.5 border-bottom bg-light bg-opacity-50">
            <div>
                <h6 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                    <i class="fas fa-users-gear text-primary"></i> 
                    পাঠক মেম্বারশিপ তালিকা (Active Subscriber Enrollments)
                </h6>
                <small class="text-muted">বর্তমানে রেজিস্টার্ড সাবস্ক্রাইবার ও রিডিং মেম্বারশিপ রেকর্ড</small>
            </div>

            {{-- Filter & Search Form --}}
            <form action="{{ route('admin.subscriptions.index') }}" method="GET" class="d-flex flex-wrap align-items-center gap-2">
                <div class="input-group input-group-sm" style="width: 220px;">
                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="নাম, ইমেইল বা ফোন..." value="{{ request('search') }}">
                </div>

                <select name="plan_id" class="form-select form-select-sm" style="width: 160px;" onchange="this.form.submit()">
                    <option value="">সকল প্ল্যান (All)</option>
                    @foreach($plans as $p)
                        <option value="{{ $p->id }}" {{ request('plan_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                    @endforeach
                </select>

                <select name="status" class="form-select form-select-sm" style="width: 140px;" onchange="this.form.submit()">
                    <option value="">সকল স্ট্যাটাস</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>সক্রিয় (Active)</option>
                    <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>মেয়াদোত্তীর্ণ (Expired)</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>বাতিলকৃত (Cancelled)</option>
                </select>

                @if(request()->hasAny(['search', 'plan_id', 'status']))
                    <a href="{{ route('admin.subscriptions.index') }}" class="btn btn-sm btn-outline-danger rounded-pill px-2.5" title="ফিল্টার মুছুন">
                        <i class="fas fa-times"></i>
                    </a>
                @endif
            </form>
        </div>

        <div class="adm-card__body p-0">
            <div class="table-responsive">
                <table class="table adm-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">পাঠক (Subscriber)</th>
                            <th>সাবস্ক্রিপশন প্যাকেজ</th>
                            <th>শুরুর তারিখ</th>
                            <th>মেয়াদ শেষ</th>
                            <th>পরিশোধিত ফি</th>
                            <th>স্ট্যাটাস</th>
                            <th class="text-end pe-4">অ্যাকশন</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($subscribers as $sub)
                            @php
                                $isActive = $sub->isActive();
                                $remainingDays = $sub->expires_at ? now()->diffInDays($sub->expires_at, false) : 0;
                            @endphp
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-2.5">
                                        <div class="adm-avatar adm-avatar--sm bg-primary text-white fw-bold shadow-2xs">
                                            {{ mb_substr($sub->user->name ?? 'U', 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark">{{ $sub->user->name ?? 'Reader' }}</div>
                                            <small class="text-muted d-block font-monospace" style="font-size: 0.75rem;">
                                                <i class="fas fa-envelope me-1"></i>{{ $sub->user->email ?? $sub->user->phone ?? 'N/A' }}
                                            </small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1 fw-semibold">
                                        <i class="fas fa-layer-group me-1"></i>{{ $sub->plan->name ?? 'Custom Plan' }}
                                    </span>
                                </td>
                                <td class="small text-muted font-monospace">
                                    {{ $sub->starts_at ? $sub->starts_at->format('d M, Y') : '—' }}
                                </td>
                                <td>
                                    <div class="d-flex flex-column gap-0.5">
                                        <span class="small font-monospace fw-semibold {{ $isActive ? 'text-dark' : 'text-danger' }}">
                                            {{ $sub->expires_at ? $sub->expires_at->format('d M, Y') : '—' }}
                                        </span>
                                        @if($isActive)
                                            <span class="badge bg-success-subtle text-success rounded-pill px-2" style="font-size: 0.65rem; width: fit-content;">
                                                {{ $remainingDays }} দিন বাকি
                                            </span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger rounded-pill px-2" style="font-size: 0.65rem; width: fit-content;">
                                                মেয়াদোত্তীর্ণ
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark font-monospace">৳{{ number_format($sub->amount_paid, 2) }}</div>
                                    <small class="text-muted" style="font-size: 0.72rem;">{{ ucfirst(str_replace('_', ' ', $sub->payment_method ?: 'Gateway')) }}</small>
                                </td>
                                <td>
                                    @if($isActive)
                                        <span class="pill pill--ok shadow-2xs">
                                            <i class="fas fa-circle-check text-success"></i> সক্রিয় (Active)
                                        </span>
                                    @elseif($sub->status === 'cancelled')
                                        <span class="pill pill--danger">
                                            <i class="fas fa-ban"></i> বাতিলকৃত
                                        </span>
                                    @else
                                        <span class="pill pill--pending">
                                            <i class="fas fa-clock"></i> এক্সপায়ার্ড
                                        </span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    @if($isActive)
                                        <form action="{{ route('admin.subscriptions.cancel', $sub) }}" method="POST" class="d-inline" onsubmit="return confirm('আপনি কি নিশ্চিত যে এই ইউজারের সাবস্ক্রিপশন বাতিল করতে চান?')">
                                            @csrf
                                            <button type="submit" class="btn btn-xs btn-outline-danger rounded-pill px-2.5 py-1 fw-semibold" title="Cancel Subscription">
                                                <i class="fas fa-times-circle me-1"></i> বাতিল
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fas fa-users-slash fs-3 mb-2 d-block opacity-50"></i>
                                    <div class="fw-bold">কোনো সাবস্ক্রাইবার রেকর্ড পাওয়া যায়নি</div>
                                    <small class="text-muted">নতুন ইউজারকে সাবস্ক্রিপশন প্রদান করতে ওপরের 'সাবস্ক্রিপশন প্রদান' বাটনে ক্লিক করুন।</small>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($subscribers->hasPages())
                <div class="p-3 border-top d-flex justify-content-center">
                    {{ $subscribers->links() }}
                </div>
            @endif
        </div>
    </div>

</div>

<!-- Modal: Create Plan -->
<div class="modal fade" id="addPlanModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <div class="modal-header bg-primary text-white py-3 px-4">
                <h6 class="modal-title fw-bold text-white mb-0">
                    <i class="fas fa-plus-circle me-1.5"></i> নতুন রিডিং সাবস্ক্রিপশন প্ল্যান তৈরি করুন
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.subscriptions.plans.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">প্ল্যানের নাম (Plan Name) <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="যেমন: আইডিয়া আনলিমিটেড মাসিক (Idea Unlimited Monthly)" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">সংক্ষিপ্ত বিবরণ (Description)</label>
                        <textarea name="description" rows="2" class="form-control" placeholder="প্যাকেজের বিস্তারিত বিবরণ লিখুন..."></textarea>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold">মূল্য BDT (৳) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="price_bdt" class="form-control fw-bold font-monospace" placeholder="299.00" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">মূল্য USD ($) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="price_usd" class="form-control fw-bold font-monospace" placeholder="3.99" required>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold">মেয়াদ (দিন) <span class="text-danger">*</span></label>
                            <input type="number" name="duration_days" value="30" class="form-control font-monospace" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">সর্বোচ্চ ডিভাইস <span class="text-danger">*</span></label>
                            <input type="number" name="max_devices" value="3" class="form-control font-monospace" required>
                        </div>
                    </div>
                    <div class="p-3 bg-light rounded-3 border mb-2">
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" name="unlimited_ebooks" value="1" id="addEbooks" checked>
                            <label class="form-check-label small fw-semibold" for="addEbooks">আনলিমিটেড ই-বুক এক্সেস</label>
                        </div>
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" name="unlimited_webzines" value="1" id="addWebzines" checked>
                            <label class="form-check-label small fw-semibold" for="addWebzines">ওয়েবজিন ও ম্যাগাজিন এক্সেস</label>
                        </div>
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" name="unlimited_audiobooks" value="1" id="addAudiobooks">
                            <label class="form-check-label small fw-semibold" for="addAudiobooks">অডিওবুক এক্সেস</label>
                        </div>
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" name="is_featured" value="1" id="addFeatured">
                            <label class="form-check-label small fw-semibold text-warning" for="addFeatured">জনপ্রিয় হিসেবে প্রদর্শন (Featured / Popular)</label>
                        </div>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="addActive" checked>
                            <label class="form-check-label small fw-semibold text-success" for="addActive">প্ল্যান সক্রিয় রাখুন (Active)</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2.5 px-4">
                    <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-bs-dismiss="modal">বাতিল</button>
                    <button type="submit" class="btn btn-sm btn-primary rounded-pill px-4 fw-bold">প্ল্যান সংরক্ষণ করুন</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Grant Subscription -->
<div class="modal fade" id="grantSubModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <div class="modal-header bg-primary text-white py-3 px-4">
                <h6 class="modal-title fw-bold text-white mb-0">
                    <i class="fas fa-user-plus me-1.5"></i> ব্যবহারকারীকে সাবস্ক্রিপশন প্রদান করুন
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.subscriptions.grant') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">ব্যবহারকারী নির্বাচন করুন (Select User) <span class="text-danger">*</span></label>
                        <select name="user_id" class="form-select" required>
                            <option value="">-- ইউজার সিলেক্ট করুন --</option>
                            @foreach($usersList as $u)
                                <option value="{{ $u->id }}">{{ $u->name }} (ID: {{ $u->id }} - {{ $u->email ?: $u->phone }})</option>
                            @endforeach
                        </select>
                        <div class="form-text small">তালিকায় না পেলে নিচে সরাসরি ইউজার আইডি দিন:</div>
                        <input type="number" id="manualUserId" class="form-control form-control-sm mt-1" placeholder="অথবা সরাসরি User ID লিখুন" oninput="if(this.value){ document.querySelector('select[name=user_id]').value = ''; this.name = 'user_id'; document.querySelector('select[name=user_id]').removeAttribute('name'); } else { this.removeAttribute('name'); document.querySelector('select').setAttribute('name', 'user_id'); }">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">সাবস্ক্রিপশন প্যাকেজ <span class="text-danger">*</span></label>
                        <select name="plan_id" class="form-select" required>
                            @foreach($plans as $p)
                                <option value="{{ $p->id }}">{{ $p->name }} (৳{{ number_format($p->price_bdt, 0) }} / {{ $p->duration_days }} দিন)</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold">পরিশোধের মাধ্যম</label>
                            <input type="text" name="payment_method" value="Manual/Admin Grant" class="form-control">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">পরিশোধিত অর্থ (৳)</label>
                            <input type="number" step="0.01" name="amount_paid" class="form-control font-monospace" placeholder="ফাঁকা রাখলে প্ল্যানের মূল্য ধার্য হবে">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2.5 px-4">
                    <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-bs-dismiss="modal">বাতিল</button>
                    <button type="submit" class="btn btn-sm btn-primary rounded-pill px-4 fw-bold">এক্সেস প্রদান করুন</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
