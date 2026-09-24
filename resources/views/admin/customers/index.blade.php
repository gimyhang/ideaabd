@extends('layouts.admin')

@section('title', 'Customer Registrations — ideaabd')
@section('heading', 'Customer Registrations')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.users') }}">User Management</a></li>
    <li class="breadcrumb-item active" aria-current="page">Customer Registrations</li>
@endsection

@section('actions')
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('admin.customers', array_merge(request()->query(), ['export' => 'csv'])) }}" class="btn btn-outline-success btn-sm rounded-pill px-3 fw-semibold shadow-xs">
            <i class="fa-solid fa-file-csv me-1.5"></i> Export CSV
        </a>
        <button type="button" class="btn btn-primary btn-sm rounded-pill px-3.5 fw-semibold shadow-xs" data-bs-toggle="modal" data-bs-target="#bulkMessageModal">
            <i class="fa-solid fa-paper-plane me-1.5"></i> Broadcast Message
        </button>
    </div>
@endsection

@section('content')
<div class="d-flex flex-column gap-4">

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-0 rounded-4 shadow-xs" role="alert">
            <i class="fa-solid fa-circle-check me-2 text-success fs-5"></i>
            <div class="fw-semibold">{{ session('success') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Summary KPIs -->
    <div class="row g-3">
        <div class="col-6 col-xl-3">
            <div class="card border-0 rounded-4 shadow-xs p-3 bg-white border-start border-4 border-primary h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="small fw-semibold text-muted d-block mb-1">Total Customers</span>
                        <h3 class="fw-bold mb-0 text-dark">{{ number_format($summary['total_customers']) }}</h3>
                    </div>
                    <div class="rounded-circle p-2.5 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: rgba(2, 132, 199, 0.1);">
                        <i class="fa-solid fa-users fs-5 text-primary"></i>
                    </div>
                </div>
                <div class="small text-muted mt-2 pt-2 border-top d-flex justify-content-between" style="font-size: 11.5px;">
                    <span>Registered readers:</span>
                    <strong class="text-primary">{{ number_format($summary['total_customers']) }}</strong>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="card border-0 rounded-4 shadow-xs p-3 bg-white border-start border-4 border-success h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="small fw-semibold text-muted d-block mb-1">Verified (Can Order)</span>
                        <h3 class="fw-bold mb-0 text-success">{{ number_format($summary['verified_both'] ?? 0) }}</h3>
                    </div>
                    <div class="rounded-circle p-2.5 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: rgba(16, 185, 129, 0.1);">
                        <i class="fa-solid fa-circle-check fs-5 text-success"></i>
                    </div>
                </div>
                <div class="small text-muted mt-2 pt-2 border-top d-flex justify-content-between" style="font-size: 11.5px;">
                    <span>Phone & Email verified:</span>
                    <strong class="text-success">{{ number_format($summary['verified_both'] ?? 0) }}</strong>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="card border-0 rounded-4 shadow-xs p-3 bg-white border-start border-4 border-warning h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="small fw-semibold text-muted d-block mb-1">Unverified</span>
                        <h3 class="fw-bold mb-0 text-warning">{{ number_format($summary['unverified'] ?? 0) }}</h3>
                    </div>
                    <div class="rounded-circle p-2.5 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: rgba(245, 158, 11, 0.12);">
                        <i class="fa-solid fa-user-clock fs-5 text-warning"></i>
                    </div>
                </div>
                <div class="small text-muted mt-2 pt-2 border-top d-flex justify-content-between" style="font-size: 11.5px;">
                    <span>Need OTP verification:</span>
                    <strong class="text-warning">{{ number_format($summary['unverified'] ?? 0) }}</strong>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="card border-0 rounded-4 shadow-xs p-3 bg-white border-start border-4 border-info h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="small fw-semibold text-muted d-block mb-1">Active Buyers</span>
                        <h3 class="fw-bold mb-0 text-dark">{{ number_format($summary['active_buyers']) }}</h3>
                    </div>
                    <div class="rounded-circle p-2.5 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: rgba(6, 182, 212, 0.1);">
                        <i class="fa-solid fa-bag-shopping fs-5 text-info"></i>
                    </div>
                </div>
                <div class="small text-muted mt-2 pt-2 border-top d-flex justify-content-between" style="font-size: 11.5px;">
                    <span>With store orders:</span>
                    <strong class="text-info">{{ number_format($summary['active_buyers']) }}</strong>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Tabs & Search Card -->
    <div class="card bg-white rounded-4 shadow-xs border-0 p-3">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            
            <!-- Quick Filter Tabs -->
            <div class="d-flex flex-wrap gap-1.5 p-1 bg-light rounded-pill border">
                <a href="{{ route('admin.customers', ['filter' => 'all', 'search' => request('search')]) }}" 
                   class="btn btn-sm rounded-pill fw-semibold px-3 {{ ($filter ?? 'all') === 'all' ? 'btn-primary shadow-xs' : 'text-muted' }}">
                    All ({{ number_format($summary['total_customers']) }})
                </a>
                <a href="{{ route('admin.customers', ['filter' => 'verified_both', 'search' => request('search')]) }}" 
                   class="btn btn-sm rounded-pill fw-semibold px-3 {{ ($filter ?? '') === 'verified_both' ? 'btn-success shadow-xs text-white' : 'text-muted' }}">
                    <i class="fa-solid fa-circle-check me-1"></i> Verified ({{ number_format($summary['verified_both'] ?? 0) }})
                </a>
                <a href="{{ route('admin.customers', ['filter' => 'unverified', 'search' => request('search')]) }}" 
                   class="btn btn-sm rounded-pill fw-semibold px-3 {{ ($filter ?? '') === 'unverified' ? 'btn-warning shadow-xs text-dark' : 'text-muted' }}">
                    <i class="fa-solid fa-clock me-1"></i> Unverified ({{ number_format($summary['unverified'] ?? 0) }})
                </a>
                <a href="{{ route('admin.customers', ['filter' => 'with_orders', 'search' => request('search')]) }}" 
                   class="btn btn-sm rounded-pill fw-semibold px-3 {{ ($filter ?? '') === 'with_orders' ? 'btn-info shadow-xs text-white' : 'text-muted' }}">
                    <i class="fa-solid fa-bag-shopping me-1"></i> Buyers ({{ number_format($summary['active_buyers']) }})
                </a>
                <a href="{{ route('admin.customers', ['filter' => 'zero_orders', 'search' => request('search')]) }}" 
                   class="btn btn-sm rounded-pill fw-semibold px-3 {{ ($filter ?? '') === 'zero_orders' ? 'btn-secondary shadow-xs text-white' : 'text-muted' }}">
                    New ({{ number_format($summary['zero_orders']) }})
                </a>
            </div>

            <!-- Search Form -->
            <form action="{{ route('admin.customers') }}" method="GET" class="d-flex align-items-center gap-2">
                <input type="hidden" name="filter" value="{{ $filter ?? 'all' }}">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control border-start-0 ps-0 rounded-end" placeholder="Search name, phone, email...">
                </div>
                <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3 fw-semibold">
                    <i class="fa-solid fa-filter me-1"></i> Search
                </button>
                @if(request('search'))
                    <a href="{{ route('admin.customers', ['filter' => $filter ?? 'all']) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5" title="Clear Search">
                        <i class="fa-solid fa-times"></i>
                    </a>
                @endif
            </form>

        </div>
    </div>

    <!-- Customers Table Card -->
    <div class="card bg-white rounded-4 shadow-xs border-0 overflow-hidden">
        <div class="card-header bg-white d-flex flex-wrap align-items-center justify-content-between gap-2 p-3 border-bottom">
            <h6 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                <i class="fa-solid fa-user-tag text-primary"></i> 
                Customer Registrations Directory
                <span class="badge bg-primary-subtle text-primary rounded-pill px-2.5 py-1">{{ number_format($customers->total()) }} customers</span>
            </h6>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1" style="font-size: 11px;">
                    <i class="fa-solid fa-check-double me-1"></i> Ordering: Phone & Email Verified Required
                </span>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                    <thead class="table-light text-secondary text-uppercase" style="font-size: 11.5px; letter-spacing: 0.5px;">
                        <tr>
                            <th class="ps-3" style="width: 4%;">#</th>
                            <th style="width: 22%;">Customer Profile</th>
                            <th style="width: 20%;">Contact Info</th>
                            <th style="width: 18%;">Verification Status</th>
                            <th class="text-center" style="width: 10%;">Orders</th>
                            <th class="text-end" style="width: 12%;">Lifetime Spent</th>
                            <th class="text-center" style="width: 8%;">Loyalty</th>
                            <th class="text-end pe-3" style="width: 14%;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customers as $index => $customer)
                        @php
                            // Format clean WhatsApp phone number (BD prefix +88)
                            $cleanPhone = preg_replace('/[^0-9]/', '', (string)$customer->phone);
                            if (str_starts_with($cleanPhone, '01')) {
                                $waNumber = '88' . $cleanPhone;
                            } elseif (str_starts_with($cleanPhone, '8801')) {
                                $waNumber = $cleanPhone;
                            } else {
                                $waNumber = $cleanPhone;
                            }
                            $waMessage = urlencode("আসসালামু আলাইকুম " . ($customer->name ?: 'গ্রাহক') . ", আইডিয়া প্রকাশন থেকে আপনাকে শুভেচ্ছা। আপনার বইয়ের অর্ডার সম্পর্কে জানতে বা যেকোনো প্রয়োজনে আমাদের সাথে যোগাযোগ করতে পারেন।");
                            $isPhoneVer = $customer->isPhoneVerified();
                            $isEmailVer = $customer->isEmailVerified();
                            $canOrder = $customer->canCustomerOrder();
                        @endphp
                        <tr>
                            <td class="ps-3 text-muted small font-monospace">{{ $customers->firstItem() + $index }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2.5">
                                    <span class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center fw-bold flex-shrink-0" style="width: 38px; height: 38px; font-size: 14px;">
                                        {{ mb_substr($customer->name ?? 'C', 0, 1) }}
                                    </span>
                                    <div class="overflow-hidden">
                                        <div class="fw-bold text-dark text-truncate d-flex align-items-center gap-1.5" style="max-width: 220px;" title="{{ $customer->name }}">
                                            <span>{{ $customer->name ?: 'Unnamed Customer' }}</span>
                                            @if($customer->orders_count >= 3)
                                                <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill px-1.5 py-0.5" style="font-size: 9px;" title="VIP Reader">
                                                    <i class="fa-solid fa-crown"></i> VIP
                                                </span>
                                            @endif
                                        </div>
                                        <div class="text-muted small" style="font-size: 11px;">
                                            Joined: {{ $customer->created_at ? $customer->created_at->format('d M, Y') : '-' }}
                                            <span class="badge bg-light text-muted border font-monospace ms-1" style="font-size: 9.5px;">#{{ $customer->id }}</span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($customer->phone)
                                    <div>
                                        <div class="d-flex align-items-center gap-2 mb-0.5">
                                            <a href="tel:{{ $customer->phone }}" class="text-decoration-none fw-bold text-dark font-monospace" style="font-size: 12.5px;">
                                                <i class="fa-solid fa-phone text-muted me-1 small"></i>{{ $customer->phone }}
                                            </a>
                                        </div>
                                        @if($customer->email)
                                            <div class="text-muted small text-truncate" style="max-width: 200px; font-size: 11.5px;">
                                                <i class="fa-solid fa-envelope me-1"></i>{{ $customer->email }}
                                            </div>
                                        @endif
                                        <div class="d-flex align-items-center gap-1.5 mt-1">
                                            <a href="https://wa.me/{{ $waNumber }}?text={{ $waMessage }}" 
                                               target="_blank" 
                                               rel="noopener" 
                                               class="badge bg-success text-white text-decoration-none px-2 py-0.5" 
                                               title="Chat on WhatsApp">
                                                <i class="fab fa-whatsapp"></i> WhatsApp
                                            </a>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted small">— No Contact —</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex flex-column gap-1">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge {{ $isPhoneVer ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-danger-subtle text-danger border border-danger-subtle' }} px-2 py-0.5" style="font-size: 10.5px;">
                                            <i class="fa-solid {{ $isPhoneVer ? 'fa-circle-check' : 'fa-circle-xmark' }} me-1"></i> Mobile {{ $isPhoneVer ? 'Verified' : 'Unverified' }}
                                        </span>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge {{ $isEmailVer ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-secondary-subtle text-secondary border' }} px-2 py-0.5" style="font-size: 10.5px;">
                                            <i class="fa-solid {{ $isEmailVer ? 'fa-circle-check' : 'fa-circle-xmark' }} me-1"></i> Email {{ $isEmailVer ? 'Verified' : 'Unverified' }}
                                        </span>
                                    </div>
                                    <div class="mt-0.5">
                                        @if($canOrder)
                                            <span class="text-success small fw-semibold" style="font-size: 11px;"><i class="fa-solid fa-cart-shopping me-1"></i> Can Order Books</span>
                                        @else
                                            <span class="text-warning-emphasis small fw-medium" style="font-size: 11px;"><i class="fa-solid fa-lock me-1 text-warning"></i> Orders Blocked</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                @if($customer->orders_count > 0)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 fw-bold">
                                        {{ number_format($customer->orders_count) }} orders
                                    </span>
                                @else
                                    <span class="badge bg-light text-muted border rounded-pill px-2 py-0.5" style="font-size: 11px;">0 orders</span>
                                @endif
                            </td>
                            <td class="text-end fw-bold text-dark font-monospace">
                                ৳{{ number_format($customer->total_spent ?? 0, 2) }}
                            </td>
                            <td class="text-center">
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2 py-0.5 fw-semibold" style="font-size: 11px;">
                                    <i class="fa-solid fa-coins me-0.5 text-warning"></i> {{ number_format($customer->loyalty_points ?? 0) }}
                                </span>
                            </td>
                            <td class="text-end pe-3">
                                <div class="d-inline-flex align-items-center gap-1">
                                    <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-2.5 py-1 fw-semibold d-flex align-items-center gap-1 shadow-2xs" 
                                            onclick='openCustomerProfileModal(@json($customer))' 
                                            title="View Customer Profile & Order History">
                                        <i class="fa-solid fa-eye"></i> Details
                                    </button>

                                    <!-- Quick Verification Toggle Dropdown -->
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary rounded-pill px-2 py-1" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Verification Controls">
                                            <i class="fa-solid fa-shield-halved"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3 p-1" style="font-size: 12.5px;">
                                            <li><h6 class="dropdown-header py-1 text-muted" style="font-size: 11px;">Verification Controls</h6></li>
                                            @if(!$isPhoneVer || !$isEmailVer)
                                                <li>
                                                    <form action="{{ route('admin.customers.toggle-verification', $customer->id) }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="action" value="verify_both">
                                                        <button type="submit" class="dropdown-item py-1.5 text-success">
                                                            <i class="fa-solid fa-check-double me-1.5 text-success"></i> Verify Both (Enable Orders)
                                                        </button>
                                                    </form>
                                                </li>
                                            @endif
                                            @if(!$isPhoneVer)
                                                <li>
                                                    <form action="{{ route('admin.customers.toggle-verification', $customer->id) }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="action" value="verify_phone">
                                                        <button type="submit" class="dropdown-item py-1.5">
                                                            <i class="fa-solid fa-mobile-screen me-1.5 text-primary"></i> Mark Phone Verified
                                                        </button>
                                                    </form>
                                                </li>
                                            @endif
                                            @if(!$isEmailVer)
                                                <li>
                                                    <form action="{{ route('admin.customers.toggle-verification', $customer->id) }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="action" value="verify_email">
                                                        <button type="submit" class="dropdown-item py-1.5">
                                                            <i class="fa-solid fa-envelope me-1.5 text-info"></i> Mark Email Verified
                                                        </button>
                                                    </form>
                                                </li>
                                            @endif
                                            @if($isPhoneVer || $isEmailVer)
                                                <li><hr class="dropdown-divider my-1"></li>
                                                <li>
                                                    <form action="{{ route('admin.customers.toggle-verification', $customer->id) }}" method="POST" onsubmit="return confirm('গ্রাহকের ভেরিফিকেশন বাতিল করতে চান? এতে তিনি পুনরায় ভেরিফাই ছাড়া অর্ডার করতে পারবেন না।');">
                                                        @csrf
                                                        <input type="hidden" name="action" value="reset_both">
                                                        <button type="submit" class="dropdown-item py-1.5 text-danger">
                                                            <i class="fa-solid fa-rotate-left me-1.5"></i> Reset Verification
                                                        </button>
                                                    </form>
                                                </li>
                                            @endif
                                        </ul>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8">
                                <div class="py-5 text-center text-muted">
                                    <i class="fa-solid fa-users-slash fs-1 mb-2 d-block text-secondary opacity-50"></i>
                                    <h6 class="fw-bold text-dark mb-1">No customers found</h6>
                                    <p class="small text-muted mb-0">Try changing your search keywords or filter category.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($customers->hasPages())
            <div class="p-3 border-top bg-light d-flex flex-wrap justify-content-between align-items-center gap-2">
                <span class="small text-muted">Showing {{ $customers->firstItem() }} to {{ $customers->lastItem() }} of {{ $customers->total() }} customers</span>
                <div>{{ $customers->links() }}</div>
            </div>
            @endif
        </div>
    </div>

</div>

<!-- Modal: Customer Profile & Order History Inspector -->
<div class="modal fade" id="customerProfileModal" tabindex="-1" aria-labelledby="customerProfileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-primary text-white py-3 px-4">
                <h6 class="modal-title fw-bold text-white mb-0" id="customerProfileModalLabel">
                    <i class="fa-solid fa-user-circle me-1.5"></i> Customer Profile & Registration Details
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="d-flex flex-column gap-3">
                    
                    <!-- Customer Header Snapshot -->
                    <div class="p-3 bg-light rounded-4 border d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold fs-4" style="width: 52px; height: 52px;" id="cModalAvatar">
                                C
                            </div>
                            <div>
                                <h5 class="fw-bold text-dark mb-0" id="cModalName">Customer Name</h5>
                                <div class="d-flex align-items-center gap-3 text-muted small mt-1">
                                    <span id="cModalPhone"><i class="fa-solid fa-phone me-1"></i>-</span>
                                    <span id="cModalEmail"><i class="fa-solid fa-envelope me-1"></i>-</span>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <a id="cModalWaBtn" href="#" target="_blank" class="btn btn-success btn-sm rounded-pill px-3 fw-bold text-white">
                                <i class="fab fa-whatsapp me-1"></i> WhatsApp
                            </a>
                            <a id="cModalCallBtn" href="#" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-bold">
                                <i class="fa-solid fa-phone-alt me-1"></i> Call
                            </a>
                        </div>
                    </div>

                    <!-- Verification Details Box -->
                    <div class="p-3 rounded-3 bg-white border">
                        <h6 class="fw-bold text-dark mb-2 small"><i class="fa-solid fa-shield-halved text-primary me-1"></i> Order Eligibility & Verification</h6>
                        <div class="row g-2">
                            <div class="col-sm-4">
                                <div class="p-2 bg-light rounded-2 text-center" id="cModalPhoneVerBox">
                                    <small class="text-muted d-block" style="font-size: 11px;">Mobile Phone</small>
                                    <span class="fw-semibold small" id="cModalPhoneVerText">-</span>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="p-2 bg-light rounded-2 text-center" id="cModalEmailVerBox">
                                    <small class="text-muted d-block" style="font-size: 11px;">Email Address</small>
                                    <span class="fw-semibold small" id="cModalEmailVerText">-</span>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="p-2 bg-light rounded-2 text-center" id="cModalOrderEligibleBox">
                                    <small class="text-muted d-block" style="font-size: 11px;">Order Books</small>
                                    <span class="fw-semibold small" id="cModalOrderEligibleText">-</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Financial Summary Cards -->
                    <div class="row g-2">
                        <div class="col-4">
                            <div class="p-2.5 bg-light rounded-3 border text-center">
                                <span class="small text-muted d-block">Total Orders</span>
                                <h5 class="fw-bold text-success mb-0" id="cModalOrdersCount">0</h5>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2.5 bg-light rounded-3 border text-center">
                                <span class="small text-muted d-block">Lifetime Spent</span>
                                <h5 class="fw-bold text-dark mb-0 font-monospace" id="cModalSpent">৳0.00</h5>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2.5 bg-light rounded-3 border text-center">
                                <span class="small text-muted d-block">Loyalty Points</span>
                                <h5 class="fw-bold text-warning mb-0" id="cModalLoyalty">0</h5>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Orders Section -->
                    <div>
                        <h6 class="fw-bold text-dark mb-2 d-flex align-items-center gap-1.5">
                            <i class="fa-solid fa-bag-shopping text-primary"></i> Recent Order History
                        </h6>
                        <div class="table-responsive border rounded-3">
                            <table class="table table-sm align-middle mb-0" style="font-size: 12px;">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3">Order #</th>
                                        <th>Date</th>
                                        <th class="text-end">Amount</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-end pe-3">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="cModalOrdersList">
                                    <!-- Dynamic rows inserted via JS -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
            <div class="modal-footer bg-light py-2.5 px-4 border-top">
                <button type="button" class="btn btn-sm btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Bulk Broadcast Message -->
<div class="modal fade" id="bulkMessageModal" tabindex="-1" aria-labelledby="bulkMessageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            
            <div class="modal-header bg-primary text-white py-3 px-4">
                <h6 class="modal-title fw-bold text-white mb-0" id="bulkMessageModalLabel">
                    <i class="fa-solid fa-paper-plane me-1.5"></i> Broadcast Customer Campaign / Announcement
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{ route('admin.customers.broadcast') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    
                    <!-- Alert Notice -->
                    <div class="alert alert-info d-flex align-items-center gap-2 mb-4 p-3 rounded-3" role="alert">
                        <i class="fa-solid fa-circle-info fs-5 flex-shrink-0"></i>
                        <div class="small">
                            Broadcast promotional offers, new book releases, or announcements directly to selected customer groups via WhatsApp, SMS, Email, or In-App Notification.
                        </div>
                    </div>

                    <!-- Target Selection -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Target Audience Group</label>
                        <select name="target_group" class="form-select rounded-3" required>
                            <option value="all">All registered readers ({{ number_format($summary['total_customers']) }} readers)</option>
                            <option value="with_orders">Active Buyers with at least 1 order ({{ number_format($summary['active_buyers']) }} readers)</option>
                            <option value="high_value">High-value customers (Spent 2,000+ BDT)</option>
                        </select>
                    </div>

                    <!-- Channel Selection -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Broadcast Channel</label>
                        <div class="d-flex flex-wrap gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="channel" id="channelNotice" value="notice" checked>
                                <label class="form-check-label small fw-semibold" for="channelNotice">
                                    <i class="fa-solid fa-bell me-1 text-primary"></i> In-App Notification
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="channel" id="channelWA" value="whatsapp">
                                <label class="form-check-label small fw-semibold" for="channelWA">
                                    <i class="fab fa-whatsapp me-1 text-success"></i> WhatsApp Campaign
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="channel" id="channelSMS" value="sms">
                                <label class="form-check-label small fw-semibold" for="channelSMS">
                                    <i class="fa-solid fa-comment-sms me-1 text-success"></i> Mobile SMS Gateway
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="channel" id="channelEmail" value="email">
                                <label class="form-check-label small fw-semibold" for="channelEmail">
                                    <i class="fa-solid fa-envelope me-1 text-danger"></i> Email Newsletter
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Message Title -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Campaign / Offer Title</label>
                        <input type="text" name="title" class="form-control rounded-3" placeholder="e.g. নতুন বই প্রকাশনা উপলক্ষে ২৫% বিশেষ ছাড়!">
                    </div>

                    <!-- Message Body -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Message Content <span class="text-danger">*</span></label>
                        <textarea name="message_body" rows="4" class="form-control rounded-3" placeholder="Type your broadcast message..." required></textarea>
                    </div>

                </div>

                <div class="modal-footer bg-light py-2.5 px-4 border-top">
                    <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary rounded-pill px-4 fw-bold shadow-xs">
                        <i class="fa-solid fa-paper-plane me-1"></i> Send Broadcast
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

@push('scripts')
<script>
function openCustomerProfileModal(customer) {
    if (!customer) return;

    document.getElementById('cModalAvatar').innerText = (customer.name || 'C').substring(0, 1).toUpperCase();
    document.getElementById('cModalName').innerText = customer.name || 'Unnamed Customer';
    document.getElementById('cModalPhone').innerHTML = '<i class="fa-solid fa-phone me-1 text-primary"></i> ' + (customer.phone || 'No Phone');
    document.getElementById('cModalEmail').innerHTML = '<i class="fa-solid fa-envelope me-1 text-info"></i> ' + (customer.email || 'No Email');
    
    document.getElementById('cModalOrdersCount').innerText = customer.orders_count || 0;
    document.getElementById('cModalSpent').innerText = '৳' + Number(customer.total_spent || 0).toLocaleString('en-US', {minimumFractionDigits: 2});
    document.getElementById('cModalLoyalty').innerText = (customer.loyalty_points || 0) + ' pts';

    // Verification info in modal
    const isPhoneVer = !!customer.phone_verified_at;
    const isEmailVer = !!customer.email_verified_at;
    const canOrder = isPhoneVer && isEmailVer;

    const phoneText = document.getElementById('cModalPhoneVerText');
    const emailText = document.getElementById('cModalEmailVerText');
    const orderText = document.getElementById('cModalOrderEligibleText');

    if (phoneText) {
        phoneText.innerHTML = isPhoneVer 
            ? '<span class="text-success"><i class="fa-solid fa-circle-check"></i> Verified</span>' 
            : '<span class="text-danger"><i class="fa-solid fa-circle-xmark"></i> Unverified</span>';
    }
    if (emailText) {
        emailText.innerHTML = isEmailVer 
            ? '<span class="text-success"><i class="fa-solid fa-circle-check"></i> Verified</span>' 
            : '<span class="text-secondary"><i class="fa-solid fa-circle-xmark"></i> Unverified</span>';
    }
    if (orderText) {
        orderText.innerHTML = canOrder 
            ? '<span class="text-success fw-bold"><i class="fa-solid fa-cart-shopping"></i> Allowed</span>' 
            : '<span class="text-warning-emphasis fw-bold"><i class="fa-solid fa-lock"></i> Verification Required</span>';
    }

    // WhatsApp Link setup
    const clean = (customer.phone || '').replace(/[^0-9]/g, '');
    let wa = clean;
    if (clean.startsWith('01')) wa = '88' + clean;
    const msg = encodeURIComponent("আসসালামু আলাইকুম " + (customer.name || 'গ্রাহক') + ", আইডিয়া প্রকাশন থেকে যোগাযোগ করা হচ্ছে।");
    
    const waBtn = document.getElementById('cModalWaBtn');
    const callBtn = document.getElementById('cModalCallBtn');
    if (customer.phone) {
        waBtn.href = `https://wa.me/${wa}?text=${msg}`;
        waBtn.style.display = 'inline-flex';
        callBtn.href = `tel:${customer.phone}`;
        callBtn.style.display = 'inline-flex';
    } else {
        waBtn.style.display = 'none';
        callBtn.style.display = 'none';
    }

    // Orders Table
    const listBody = document.getElementById('cModalOrdersList');
    listBody.innerHTML = '';

    if (customer.orders && customer.orders.length > 0) {
        customer.orders.forEach(order => {
            const tr = document.createElement('tr');
            const dateStr = order.created_at ? new Date(order.created_at).toLocaleDateString('en-GB') : '-';
            const statusBadge = order.status === 'completed' 
                ? '<span class="badge bg-success-subtle text-success rounded-pill px-2 py-0.5">Completed</span>' 
                : `<span class="badge bg-primary-subtle text-primary rounded-pill px-2 py-0.5">${order.status || 'Processing'}</span>`;
            
            tr.innerHTML = `
                <td class="ps-3 fw-bold font-monospace text-primary">#${order.order_number || order.id}</td>
                <td class="text-muted">${dateStr}</td>
                <td class="text-end fw-bold text-dark font-monospace">৳${Number(order.total_amount || 0).toFixed(2)}</td>
                <td class="text-center">${statusBadge}</td>
                <td class="text-end pe-3">
                    <a href="/admin/ecommerce-orders/${order.id}" class="btn btn-xs btn-outline-secondary rounded-pill px-2" target="_blank">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i> View
                    </a>
                </td>
            `;
            listBody.appendChild(tr);
        });
    } else {
        listBody.innerHTML = `
            <tr>
                <td colspan="5" class="text-center py-4 text-muted small">
                    <i class="fa-solid fa-bag-shopping fs-3 mb-1 text-secondary opacity-50 d-block"></i>
                    No orders placed yet by this customer.
                </td>
            </tr>
        `;
    }

    const modal = new bootstrap.Modal(document.getElementById('customerProfileModal'));
    modal.show();
}
</script>
@endpush
@endsection
