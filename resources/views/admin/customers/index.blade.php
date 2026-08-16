@extends('layouts.admin')

@section('title', 'গ্রাহক তালিকা ও ব্রডকাস্ট মেসেজিং')
@section('heading', 'গ্রাহক তালিকা ও ব্রডকাস্ট মেসেজিং')

@section('breadcrumb')
    <li class="breadcrumb-item active">গ্রাহক ব্যবস্থাপনা</li>
@endsection

@section('actions')
    <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 fw-semibold" data-bs-toggle="modal" data-bs-target="#bulkMessageModal">
        <i class="fas fa-paper-plane me-1.5"></i> একসাথে সব গ্রাহককে মেসেজ পাঠান
    </button>
@endsection

@section('content')
<div class="d-flex flex-column gap-4">

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-0" role="alert">
            <i class="fas fa-circle-check me-2"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Summary KPIs -->
    <div class="row g-3">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi" style="--bar: var(--brand);">
                <div class="kpi__icon bg-primary-subtle text-primary">
                    <i class="fas fa-users"></i>
                </div>
                <p class="kpi__label">মোট নিবন্ধিত গ্রাহক</p>
                <h3 class="kpi__value text-dark">@bn($summary['total_customers']) জন</h3>
                <p class="kpi__foot">বুকশপের নিবন্ধিত পাঠক</p>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi" style="--bar: var(--ok);">
                <div class="kpi__icon bg-success-subtle text-success">
                    <i class="fas fa-bag-shopping"></i>
                </div>
                <p class="kpi__label">সক্রিয় ক্রেতা</p>
                <h3 class="kpi__value text-dark">@bn($summary['active_buyers']) জন</h3>
                <p class="kpi__foot">কমপক্ষে ১টি অর্ডার সম্পন্নকারী</p>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi" style="--bar: var(--brand-2);">
                <div class="kpi__icon bg-info-subtle text-info">
                    <i class="fas fa-wallet"></i>
                </div>
                <p class="kpi__label">সর্বমোট বিক্রয় রেভিনিউ</p>
                <h3 class="kpi__value text-dark">৳@bn($summary['total_spent_sum'] ?? 0)</h3>
                <p class="kpi__foot">ই-কমার্স অর্ডারের মোট বিক্রয়</p>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi" style="--bar: var(--warn);">
                <div class="kpi__icon bg-warning-subtle text-warning">
                    <i class="fas fa-gift"></i>
                </div>
                <p class="kpi__label">মোট লয়্যালটি পয়েন্ট</p>
                <h3 class="kpi__value text-dark">@bn($summary['loyalty_points'] ?? 0)</h3>
                <p class="kpi__foot">গ্রাহকদের অর্জিত রিওয়ার্ড পয়েন্ট</p>
            </div>
        </div>
    </div>

    <!-- Search & Filter Card -->
    <div class="adm-card p-3">
        <form action="{{ route('admin.customers') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-12 col-md-8">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white"><i class="fas fa-magnifying-glass text-muted"></i></span>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="গ্রাহকের নাম, মোবাইল নম্বর বা ইমেইল দিয়ে খুঁজুন...">
                </div>
            </div>
            <div class="col-12 col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary flex-fill fw-semibold">
                    <i class="fas fa-filter me-1"></i> অনুসন্ধান
                </button>
                @if(request('search'))
                    <a href="{{ route('admin.customers') }}" class="btn btn-sm btn-outline-secondary" title="রিসেট">
                        <i class="fas fa-rotate-left"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Customers Table Card -->
    <div class="adm-card overflow-hidden">
        <div class="adm-card__head flex-wrap gap-2">
            <h6 class="mb-0 d-flex align-items-center gap-2">
                <i class="fas fa-users text-primary"></i> 
                গ্রাহক তালিকা 
                <span class="badge bg-primary-subtle text-primary rounded-pill px-2.5 py-1">@bn($customers->total()) জন</span>
            </h6>
            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold" data-bs-toggle="modal" data-bs-target="#bulkMessageModal">
                <i class="fas fa-message me-1"></i> মেসেজ পাঠান
            </button>
        </div>

        <div class="adm-card__body p-0">
            <div class="table-responsive">
                <table class="table adm-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-3">#</th>
                            <th>গ্রাহকের নাম ও প্রোফাইল</th>
                            <th>মোবাইল নম্বর</th>
                            <th class="text-center">মোট অর্ডার</th>
                            <th class="text-end">মোট ক্রয়কৃত মূল্য</th>
                            <th class="text-center">লয়্যালটি পয়েন্ট</th>
                            <th class="text-center pe-3">যোগদানের তারিখ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customers as $index => $customer)
                        <tr>
                            <td class="ps-3 text-muted small">{{ $customers->firstItem() + $index }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="adm-avatar adm-avatar--sm bg-primary text-white">
                                        {{ mb_substr($customer->name ?? 'C', 0, 1) }}
                                    </span>
                                    <div>
                                        <div class="fw-semibold text-dark">{{ $customer->name }}</div>
                                        <small class="text-muted" style="font-size: 0.72rem;">{{ $customer->email ?? 'ইমেইল নেই' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($customer->phone)
                                    <a href="tel:{{ $customer->phone }}" class="text-decoration-none fw-semibold text-primary">
                                        <i class="fas fa-phone me-1 small"></i>{{ $customer->phone }}
                                    </a>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($customer->orders_count > 0)
                                    <span class="pill pill--ok">
                                        @bn($customer->orders_count) টি অর্ডার
                                    </span>
                                @else
                                    <span class="pill pill--muted">০</span>
                                @endif
                            </td>
                            <td class="text-end fw-bold text-dark">
                                ৳@bn(number_format($customer->total_spent ?? 0, 0))
                            </td>
                            <td class="text-center">
                                <span class="pill pill--pending">
                                    <i class="fas fa-star me-0.5"></i> @bn($customer->loyalty_points ?? 0)
                                </span>
                            </td>
                            <td class="text-center text-muted small pe-3">
                                {{ $customer->created_at ? $customer->created_at->format('d M, Y') : '—' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <i class="fas fa-users-slash"></i>
                                    <p class="mb-0 fw-semibold">কোনো গ্রাহক পাওয়া যায়নি</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($customers->hasPages())
            <div class="adm-card__foot d-flex justify-content-between align-items-center">
                <span class="small text-muted">মোট {{ $customers->total() }} জনের মধ্যে {{ $customers->firstItem() }} থেকে {{ $customers->lastItem() }} জন প্রদর্শিত</span>
                <div>{{ $customers->links() }}</div>
            </div>
            @endif
        </div>
    </div>

</div>

<!-- Modal: Bulk Broadcast Message -->
<div class="modal fade" id="bulkMessageModal" tabindex="-1" aria-labelledby="bulkMessageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            
            <div class="modal-header bg-primary text-white py-3">
                <h6 class="modal-title fw-bold text-white mb-0" id="bulkMessageModalLabel">
                    <i class="fas fa-paper-plane me-1"></i> গ্রাহকদের একসাথে ব্রডকাস্ট মেসেজ / অফার পাঠান
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{ route('admin.customers.broadcast') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    
                    <!-- Alert Notice -->
                    <div class="alert alert-info d-flex align-items-center gap-2 mb-4 p-3" role="alert">
                        <i class="fas fa-info-circle fs-5 flex-shrink-0"></i>
                        <div class="small">
                            এই টুলের মাধ্যমে আপনি আপনার গ্রাহকদের প্রমোশনাল ডিসকাউন্ট অফার, বই প্রকাশনা সংক্রান্ত ঘোষণা বা নোটিশ সরাসরি পাঠাতে পারবেন।
                        </div>
                    </div>

                    <!-- Target Selection -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">মেসেজ প্রাপক গ্রুপ (Target Audience)</label>
                        <select name="target_group" class="form-select form-select-sm" required>
                            <option value="all">সকল নিবন্ধিত গ্রাহক ({{ $summary['total_customers'] }} জন)</option>
                            <option value="with_orders">যাদের কমপক্ষে ১টি অর্ডার আছে ({{ $summary['active_buyers'] }} জন)</option>
                            <option value="high_value">টপ ভ্যালু ক্রেতা (যাদের মোট ক্রয় ৫,০০০+ টাকা)</option>
                        </select>
                    </div>

                    <!-- Channel Selection -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">মেসেজ প্রেরণের মাধ্যম (Channel)</label>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="channel" id="channelNotice" value="notice" checked>
                                <label class="form-check-label small" for="channelNotice">
                                    <i class="fas fa-bell me-1 text-primary"></i> ইন-অ্যাপ নোটিফিকেশন
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="channel" id="channelSMS" value="sms">
                                <label class="form-check-label small" for="channelSMS">
                                    <i class="fas fa-comment-sms me-1 text-success"></i> মোবাইল এসএমএস (SMS Gateway)
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="channel" id="channelEmail" value="email">
                                <label class="form-check-label small" for="channelEmail">
                                    <i class="fas fa-envelope me-1 text-danger"></i> ইমেইল নিউজলেটার
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Message Title -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">শিরোনাম / অফার টাইটেল (Title)</label>
                        <input type="text" name="title" class="form-control form-control-sm" placeholder="উদা: অমর একুশে বইমেলা উপলক্ষে ২৫% ছাড়!">
                    </div>

                    <!-- Message Body -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">মেসেজের মূল বিবরণ <span class="text-danger">*</span></label>
                        <textarea name="message_body" rows="4" class="form-control form-control-sm" placeholder="আপনার বার্তাটি লিখুন..." required></textarea>
                    </div>

                </div>

                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">বাতিল</button>
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="fas fa-paper-plane me-1"></i> মেসেজ ব্রডকাস্ট করুন
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>
@endsection
