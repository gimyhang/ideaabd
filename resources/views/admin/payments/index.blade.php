@extends('layouts.admin')

@section('title', 'Payments & Gateway Settings')
@section('heading', 'Payments & Payment Gateway Management')

@section('breadcrumb')
    <li class="breadcrumb-item active">Payments & Gateways</li>
@endsection

@section('content')
<div class="d-flex flex-column gap-4">

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-0 rounded-4 shadow-xs" role="alert">
            <i class="fas fa-circle-check me-2 text-success"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Payment Stats KPI Grid -->
    <div class="row g-3">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi bg-white rounded-4 shadow-sm border-0 p-3" style="--bar: var(--ok);">
                <div class="kpi__icon bg-success-subtle text-success">
                    <i class="fas fa-sack-dollar"></i>
                </div>
                <p class="kpi__label small text-muted fw-semibold mb-1">Total Paid Revenue</p>
                <h3 class="kpi__value text-dark fs-4 fw-bold mb-1">৳{{ number_format($stats['total_online_revenue'], 2) }}</h3>
                <p class="kpi__foot text-muted small mb-0">Online & COD collections</p>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi bg-white rounded-4 shadow-sm border-0 p-3" style="--bar: var(--brand);">
                <div class="kpi__icon bg-primary-subtle text-primary">
                    <i class="fas fa-circle-check"></i>
                </div>
                <p class="kpi__label small text-muted fw-semibold mb-1">Paid Orders</p>
                <h3 class="kpi__value text-dark fs-4 fw-bold mb-1">{{ number_format($stats['paid_orders_count']) }}</h3>
                <p class="kpi__foot text-muted small mb-0">Verified payment orders</p>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi bg-white rounded-4 shadow-sm border-0 p-3" style="--bar: var(--warn);">
                <div class="kpi__icon bg-warning-subtle text-warning">
                    <i class="fas fa-hourglass-half"></i>
                </div>
                <p class="kpi__label small text-muted fw-semibold mb-1">Pending Payments</p>
                <h3 class="kpi__value text-dark fs-4 fw-bold mb-1">{{ number_format($stats['pending_orders_count']) }}</h3>
                <p class="kpi__foot text-muted small mb-0">Orders awaiting verification</p>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi bg-white rounded-4 shadow-sm border-0 p-3" style="--bar: var(--danger);">
                <div class="kpi__icon bg-danger-subtle text-danger">
                    <i class="fas fa-mobile-screen"></i>
                </div>
                <p class="kpi__label small text-muted fw-semibold mb-1">Mobile Banking</p>
                <h3 class="kpi__value text-dark fs-4 fw-bold mb-1">৳{{ number_format($stats['bkash_revenue'] + $stats['nagad_revenue'], 2) }}</h3>
                <p class="kpi__foot text-muted small mb-0">Total bKash & Nagad MFS</p>
            </div>
        </div>
    </div>

    <!-- Main Navigation Card -->
    <div class="adm-card bg-white rounded-4 shadow-sm border-0 overflow-hidden">
        <div class="adm-card__head d-flex flex-wrap gap-2 py-3 px-4 border-bottom">
            <ul class="nav nav-pills gap-2" id="paymentTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active rounded-pill fw-semibold py-1.5 px-3" 
                            id="tab-gateways-btn" data-bs-toggle="pill" data-bs-target="#tab-gateways" type="button" role="tab">
                        <i class="fas fa-sliders me-1.5 text-primary"></i> Payment Gateways Configuration
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-pill fw-semibold py-1.5 px-3" 
                            id="tab-trx-btn" data-bs-toggle="pill" data-bs-target="#tab-trx" type="button" role="tab">
                        <i class="fas fa-receipt me-1.5 text-success"></i> Transactions & Payment Logs
                    </button>
                </li>
            </ul>
        </div>

        <div class="adm-card__body p-3 p-md-4">
            <div class="tab-content" id="paymentTabContent">
                
                <!-- TAB 1: Payment Gateways Settings Form -->
                <div class="tab-pane fade show active" id="tab-gateways" role="tabpanel">
                    <form action="{{ route('admin.payments.update') }}" method="POST">
                        @csrf
                        
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 pb-3 border-bottom">
                            <div>
                                <h5 class="fw-bold mb-1 text-dark">Payment Gateways & Accounts</h5>
                                <p class="text-muted small mb-0">Enable and configure payment methods available to customers at checkout.</p>
                            </div>
                            <button type="submit" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold shadow-xs">
                                <i class="fas fa-floppy-disk me-1.5"></i> Save Settings
                            </button>
                        </div>

                        <div class="row g-4">
                            
                            <!-- 1. bKash Settings Card -->
                            <div class="col-12 col-md-6">
                                <div class="adm-card bg-light rounded-4 border h-100 p-3">
                                    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge text-white px-2.5 py-1 fw-bold" style="background:#d82a6f;">bKash</span>
                                            <div>
                                                <h6 class="mb-0 fw-bold text-dark">bKash</h6>
                                                <small class="text-muted" style="font-size: 11px;">Mobile Financial Service</small>
                                            </div>
                                        </div>
                                        <div class="form-check form-switch mb-0">
                                            <input type="hidden" name="payment_gateways[bkash][enabled]" value="0">
                                            <input class="form-check-input" type="checkbox" role="switch" id="gw_bkash_enabled" 
                                                   name="payment_gateways[bkash][enabled]" value="1" 
                                                   @checked(!empty($paymentGateways['bkash']['enabled']))>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold text-muted">bKash Number (Personal / Merchant)</label>
                                            <input type="text" class="form-control form-control-sm rounded-3 font-monospace" name="payment_gateways[bkash][number]" 
                                                   value="{{ $paymentGateways['bkash']['number'] ?? '01558712810' }}" placeholder="01XXXXXXXXX">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold text-muted">Account Type</label>
                                            <select class="form-select form-select-sm rounded-3" name="payment_gateways[bkash][type]">
                                                <option value="personal" @selected(($paymentGateways['bkash']['type'] ?? '') === 'personal')>Personal (Send Money)</option>
                                                <option value="merchant" @selected(($paymentGateways['bkash']['type'] ?? '') === 'merchant')>Merchant (Payment)</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="form-label small fw-semibold text-muted">Customer Instructions</label>
                                            <textarea class="form-control form-control-sm rounded-3" name="payment_gateways[bkash][instructions]" rows="2">{{ $paymentGateways['bkash']['instructions'] ?? 'Use Send Money in bKash app to transfer bill total to the provided number.' }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 2. Nagad Settings Card -->
                            <div class="col-12 col-md-6">
                                <div class="adm-card bg-light rounded-4 border h-100 p-3">
                                    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge text-white px-2.5 py-1 fw-bold" style="background:#e8590c;">Nagad</span>
                                            <div>
                                                <h6 class="mb-0 fw-bold text-dark">Nagad</h6>
                                                <small class="text-muted" style="font-size: 11px;">Mobile Financial Service</small>
                                            </div>
                                        </div>
                                        <div class="form-check form-switch mb-0">
                                            <input type="hidden" name="payment_gateways[nagad][enabled]" value="0">
                                            <input class="form-check-input" type="checkbox" role="switch" id="gw_nagad_enabled" 
                                                   name="payment_gateways[nagad][enabled]" value="1" 
                                                   @checked(!empty($paymentGateways['nagad']['enabled']))>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold text-muted">Nagad Number (Personal / Merchant)</label>
                                            <input type="text" class="form-control form-control-sm rounded-3 font-monospace" name="payment_gateways[nagad][number]" 
                                                   value="{{ $paymentGateways['nagad']['number'] ?? '01558712810' }}" placeholder="01XXXXXXXXX">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold text-muted">Account Type</label>
                                            <select class="form-select form-select-sm rounded-3" name="payment_gateways[nagad][type]">
                                                <option value="personal" @selected(($paymentGateways['nagad']['type'] ?? '') === 'personal')>Personal (Send Money)</option>
                                                <option value="merchant" @selected(($paymentGateways['nagad']['type'] ?? '') === 'merchant')>Merchant (Payment)</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="form-label small fw-semibold text-muted">Customer Instructions</label>
                                            <textarea class="form-control form-control-sm rounded-3" name="payment_gateways[nagad][instructions]" rows="2">{{ $paymentGateways['nagad']['instructions'] ?? 'Use Send Money in Nagad app to transfer bill total to the provided number.' }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 3. Rocket Settings Card -->
                            <div class="col-12 col-md-6">
                                <div class="adm-card bg-light rounded-4 border h-100 p-3">
                                    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge text-white px-2.5 py-1 fw-bold" style="background:#8b5cf6;">Rocket</span>
                                            <div>
                                                <h6 class="mb-0 fw-bold text-dark">Rocket</h6>
                                                <small class="text-muted" style="font-size: 11px;">Dutch-Bangla Bank</small>
                                            </div>
                                        </div>
                                        <div class="form-check form-switch mb-0">
                                            <input type="hidden" name="payment_gateways[rocket][enabled]" value="0">
                                            <input class="form-check-input" type="checkbox" role="switch" id="gw_rocket_enabled" 
                                                   name="payment_gateways[rocket][enabled]" value="1" 
                                                   @checked(!empty($paymentGateways['rocket']['enabled']))>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold text-muted">Rocket Account Number (12 Digits)</label>
                                            <input type="text" class="form-control form-control-sm rounded-3 font-monospace" name="payment_gateways[rocket][number]" 
                                                   value="{{ $paymentGateways['rocket']['number'] ?? '01558712810' }}" placeholder="01XXXXXXXXXX">
                                        </div>
                                        <div>
                                            <label class="form-label small fw-semibold text-muted">Customer Instructions</label>
                                            <textarea class="form-control form-control-sm rounded-3" name="payment_gateways[rocket][instructions]" rows="2">{{ $paymentGateways['rocket']['instructions'] ?? 'Transfer bill amount via Rocket Send Money and provide transaction ID.' }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 4. Cash on Delivery (COD) -->
                            <div class="col-12 col-md-6">
                                <div class="adm-card bg-light rounded-4 border h-100 p-3">
                                    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-success text-white"><i class="fas fa-hand-holding-dollar"></i></span>
                                            <div>
                                                <h6 class="mb-0 fw-bold text-dark">Cash on Delivery (COD)</h6>
                                                <small class="text-muted" style="font-size: 11px;">Pay on parcel delivery</small>
                                            </div>
                                        </div>
                                        <div class="form-check form-switch mb-0">
                                            <input type="hidden" name="payment_gateways[cod][enabled]" value="0">
                                            <input class="form-check-input" type="checkbox" role="switch" id="gw_cod_enabled" 
                                                   name="payment_gateways[cod][enabled]" value="1" 
                                                   @checked(!empty($paymentGateways['cod']['enabled']))>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold text-muted">Method Display Title</label>
                                            <input type="text" class="form-control form-control-sm rounded-3" name="payment_gateways[cod][name]" 
                                                   value="{{ $paymentGateways['cod']['name'] ?? 'Cash on Delivery (COD)' }}">
                                        </div>
                                        <div>
                                            <label class="form-label small fw-semibold text-muted">Customer Instructions</label>
                                            <textarea class="form-control form-control-sm rounded-3" name="payment_gateways[cod][instructions]" rows="2">{{ $paymentGateways['cod']['instructions'] ?? 'Pay cash directly upon book delivery.' }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 5. Bank Account Settings -->
                            <div class="col-12">
                                <div class="adm-card bg-light rounded-4 border p-3">
                                    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-primary text-white"><i class="fas fa-building-columns"></i></span>
                                            <div>
                                                <h6 class="mb-0 fw-bold text-dark">Bank Wire Transfer</h6>
                                                <small class="text-muted" style="font-size: 11px;">Direct bank account deposit or online transfer</small>
                                            </div>
                                        </div>
                                        <div class="form-check form-switch mb-0">
                                            <input type="hidden" name="payment_gateways[bank][enabled]" value="0">
                                            <input class="form-check-input" type="checkbox" role="switch" id="gw_bank_enabled" 
                                                   name="payment_gateways[bank][enabled]" value="1" 
                                                   @checked(!empty($paymentGateways['bank']['enabled']))>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="row g-3">
                                            <div class="col-12 col-md-4">
                                                <label class="form-label small fw-semibold text-muted">Bank Name</label>
                                                <input type="text" class="form-control form-control-sm rounded-3" name="payment_gateways[bank][bank_name]" 
                                                       value="{{ $paymentGateways['bank']['bank_name'] ?? 'Islami Bank Bangladesh Ltd' }}">
                                            </div>
                                            <div class="col-12 col-md-4">
                                                <label class="form-label small fw-semibold text-muted">Account Name</label>
                                                <input type="text" class="form-control form-control-sm rounded-3" name="payment_gateways[bank][account_name]" 
                                                       value="{{ $paymentGateways['bank']['account_name'] ?? 'Idea Prokashon' }}">
                                            </div>
                                            <div class="col-12 col-md-4">
                                                <label class="form-label small fw-semibold text-muted">Account Number</label>
                                                <input type="text" class="form-control form-control-sm rounded-3 font-monospace" name="payment_gateways[bank][account_no]" 
                                                       value="{{ $paymentGateways['bank']['account_no'] ?? '2050XXXXXXXXX' }}">
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <label class="form-label small fw-semibold text-muted">Branch Name</label>
                                                <input type="text" class="form-control form-control-sm rounded-3" name="payment_gateways[bank][branch]" 
                                                       value="{{ $paymentGateways['bank']['branch'] ?? 'Rangpur Branch' }}">
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <label class="form-label small fw-semibold text-muted">Routing Number</label>
                                                <input type="text" class="form-control form-control-sm rounded-3 font-monospace" name="payment_gateways[bank][routing]" 
                                                       value="{{ $paymentGateways['bank']['routing'] ?? '125XXXXXXXX' }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="text-end mt-4">
                            <button type="submit" class="btn btn-primary rounded-pill px-5 py-2.5 fw-bold shadow-sm">
                                <i class="fas fa-floppy-disk me-1.5"></i> Save Settings
                            </button>
                        </div>
                    </form>
                </div>

                <!-- TAB 2: Payment Transactions / Order Log -->
                <div class="tab-pane fade" id="tab-trx" role="tabpanel">
                    
                    <!-- Filters -->
                    <form method="GET" action="{{ route('admin.payments.index') }}" class="adm-card bg-light rounded-4 border p-3 mb-4">
                        <input type="hidden" name="tab" value="trx">
                        <div class="row g-2 align-items-center">
                            <div class="col-12 col-md-4">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-search"></i></span>
                                    <input type="text" name="search" class="form-control border-start-0 ps-0" 
                                           placeholder="Order no, TrxID, phone or customer..." value="{{ request('search') }}">
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <select name="method" class="form-select form-select-sm rounded-3">
                                    <option value="">All Payment Methods</option>
                                    <option value="bkash" @selected(request('method') === 'bkash')>bKash</option>
                                    <option value="nagad" @selected(request('method') === 'nagad')>Nagad</option>
                                    <option value="rocket" @selected(request('method') === 'rocket')>Rocket</option>
                                    <option value="cod" @selected(request('method') === 'cod')>Cash on Delivery (COD)</option>
                                </select>
                            </div>
                            <div class="col-6 col-md-3">
                                <select name="status" class="form-select form-select-sm rounded-3">
                                    <option value="">All Statuses</option>
                                    <option value="paid" @selected(request('status') === 'paid')>Paid</option>
                                    <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                                    <option value="failed" @selected(request('status') === 'failed')>Failed</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-2 d-flex gap-2">
                                <button type="submit" class="btn btn-sm btn-primary flex-fill fw-semibold rounded-pill">Filter</button>
                                <a href="{{ route('admin.payments.index') }}?tab=trx" class="btn btn-sm btn-outline-secondary rounded-pill" title="Reset"><i class="fas fa-rotate-left"></i></a>
                            </div>
                        </div>
                    </form>

                    <!-- Transactions Table -->
                    <div class="table-responsive">
                        <table class="table adm-table align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Order Number</th>
                                    <th>Customer Info</th>
                                    <th>Method</th>
                                    <th>TrxID / Phone</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Order Date</th>
                                    <th class="text-end pe-3">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($transactions as $order)
                                    <tr>
                                        <td class="ps-3">
                                            <a href="{{ route('admin.ecommerce-orders.show', $order->id) }}" class="fw-bold text-primary text-decoration-none">
                                                #{{ $order->order_number }}
                                            </a>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-dark">{{ $order->customer_name }}</div>
                                            <small class="text-muted"><i class="fas fa-phone me-1 small"></i>{{ $order->customer_phone }}</small>
                                        </td>
                                        <td>
                                            @php
                                                $m = strtolower($order->payment_method ?? 'cod');
                                                $badgeColor = match($m) {
                                                    'bkash' => 'danger',
                                                    'nagad' => 'warning text-dark',
                                                    'rocket' => 'info text-dark',
                                                    default => 'secondary',
                                                };
                                            @endphp
                                            <span class="badge bg-{{ $badgeColor }} text-uppercase rounded-pill px-2.5 py-1">
                                                {{ $order->payment_method ?? 'COD' }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($order->transaction_id)
                                                <code class="px-2 py-0.5 bg-light rounded border fw-bold text-dark">{{ $order->transaction_id }}</code>
                                                @if($order->payment_phone)
                                                    <div class="text-muted small mt-0.5 font-monospace">{{ $order->payment_phone }}</div>
                                                @endif
                                            @else
                                                <span class="text-muted small">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="fw-bold text-dark font-monospace">৳{{ number_format($order->total_amount, 2) }}</span>
                                        </td>
                                        <td>
                                            @if($order->payment_status === 'paid')
                                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1">
                                                    <i class="fas fa-check-double me-1"></i> Paid
                                                </span>
                                            @elseif($order->payment_status === 'pending')
                                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2.5 py-1">
                                                    <i class="fas fa-clock me-1"></i> Pending
                                                </span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1">
                                                    {{ ucfirst($order->payment_status) }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-muted small">
                                            {{ $order->created_at ? $order->created_at->format('d M Y, h:i A') : '—' }}
                                        </td>
                                        <td class="text-end pe-3">
                                            <!-- Quick Status Modal Trigger -->
                                            <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-2.5 py-0.5" 
                                                    data-bs-toggle="modal" data-bs-target="#editPaymentModal{{ $order->id }}">
                                                <i class="fas fa-pen-to-square me-1"></i> Update
                                            </button>

                                            <!-- Modal -->
                                            <div class="modal fade text-start" id="editPaymentModal{{ $order->id }}" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content rounded-4 border-0 shadow">
                                                        <div class="modal-header bg-light">
                                                            <h6 class="modal-title fw-bold text-dark">Order #{{ $order->order_number }} Payment Update</h6>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <form action="{{ route('admin.payments.status', $order->id) }}" method="POST">
                                                            @csrf
                                                            @method('PATCH')
                                                            <div class="modal-body p-4">
                                                                <div class="mb-3">
                                                                    <label class="form-label small fw-semibold text-muted">Payment Status</label>
                                                                    <select name="payment_status" class="form-select rounded-3">
                                                                        <option value="paid" @selected($order->payment_status === 'paid')>Paid</option>
                                                                        <option value="pending" @selected($order->payment_status === 'pending')>Pending</option>
                                                                        <option value="failed" @selected($order->payment_status === 'failed')>Failed</option>
                                                                        <option value="refunded" @selected($order->payment_status === 'refunded')>Refunded</option>
                                                                    </select>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label small fw-semibold text-muted">Transaction ID (TrxID)</label>
                                                                    <input type="text" name="transaction_id" class="form-control rounded-3 font-monospace" value="{{ $order->transaction_id }}" placeholder="Enter TrxID">
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer bg-light">
                                                                <button type="button" class="btn btn-sm btn-light rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                                                                <button type="submit" class="btn btn-sm btn-primary rounded-pill px-4 fw-bold shadow-xs">Save</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8">
                                            <div class="empty-state py-5 text-center text-muted">
                                                <i class="fas fa-receipt fs-2 mb-2 d-block opacity-50"></i>
                                                <p class="mb-0 fw-semibold text-dark">No transactions found</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $transactions->links() }}
                    </div>

                </div>

            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('tab') === 'trx' || urlParams.get('page') || urlParams.get('search') || urlParams.get('method') || urlParams.get('status')) {
            const trxTabBtn = document.getElementById('tab-trx-btn');
            if (trxTabBtn) {
                bootstrap.Tab.getOrCreateInstance(trxTabBtn).show();
            }
        }
    });
</script>
@endpush
@endsection
