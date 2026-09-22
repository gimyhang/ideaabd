@extends('layouts.admin')

@section('title', 'Admin Profile & Settings — Idea Prokashon')
@section('heading', 'Admin Profile & Settings')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Profile Settings</li>
@endsection

@section('content')
<div class="container-fluid px-0">
    <div class="row g-4">
        {{-- Left Column: Profile Summary & Avatar Card --}}
        <div class="col-12 col-lg-4">
            <div class="adm-card text-center p-4 shadow-sm border-0 position-relative mb-4">
                <div class="position-absolute top-0 end-0 p-3">
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1">
                        <i class="fa-solid fa-circle-check me-1"></i>Active
                    </span>
                </div>

                {{-- Avatar Image / Initial Badge --}}
                <div class="mb-3 position-relative d-inline-block">
                    @if($user->avatar && \Illuminate\Support\Facades\Storage::disk('public')->exists($user->avatar))
                        <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}" 
                             class="rounded-circle shadow-md object-fit-cover border border-3 border-primary" 
                             style="width: 110px; height: 110px;">
                    @else
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center shadow-md mx-auto" 
                             style="width: 110px; height: 110px; font-size: 2.8rem; font-weight: 700;">
                            {{ mb_substr(trim($user->name ?? 'A'), 0, 1) }}
                        </div>
                    @endif
                    <span class="position-absolute bottom-0 end-0 bg-success border border-white border-2 rounded-circle" style="width: 18px; height: 18px;" title="Online"></span>
                </div>

                <h4 class="fw-bold mb-1 text-dark">{{ $user->name }}</h4>
                <div class="badge bg-primary-subtle text-primary rounded-pill px-3 py-1 mb-2 font-monospace">
                    <i class="fa-solid fa-shield-halved me-1"></i>{{ ['admin' => 'Super Admin', 'sub_admin' => 'Sub-Admin', 'seller' => 'Seller'][$user->role] ?? ucfirst($user->role) }}
                </div>
                
                @if(!empty($user->reg_data['designation']))
                    <p class="text-muted small fw-semibold mb-2">{{ $user->reg_data['designation'] }}</p>
                @endif

                <div class="border-top pt-3 mt-3 text-start small">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted"><i class="fa-solid fa-envelope me-1.5 text-primary"></i>Email:</span>
                        <span class="fw-semibold text-dark">{{ $user->email }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted"><i class="fa-solid fa-phone me-1.5 text-success"></i>Phone:</span>
                        <span class="fw-semibold text-dark">{{ $user->phone ?: '—' }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted"><i class="fa-solid fa-calendar-check me-1.5 text-info"></i>Joined:</span>
                        <span class="fw-semibold text-dark">{{ $user->created_at->format('d M Y') }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted"><i class="fa-solid fa-fingerprint me-1.5 text-secondary"></i>User ID:</span>
                        <span class="fw-semibold font-monospace text-dark">#ADM-{{ str_pad($user->id, 4, '0', STR_PAD_LEFT) }}</span>
                    </div>
                </div>

                @if($user->avatar)
                    <form method="POST" action="{{ route('admin.profile.avatar.remove') }}" class="mt-3"
                          data-confirm="Are you sure you want to remove your profile photo?"
                          data-confirm-title="Remove Profile Photo"
                          data-confirm-icon="warning"
                          data-confirm-btn="<i class='fa-solid fa-trash-can me-1'></i> Yes, Remove">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm w-100 rounded-pill">
                            <i class="fa-solid fa-trash-can me-1"></i> Remove Avatar
                        </button>
                    </form>
                @endif
            </div>

            {{-- Quick System Shortcuts --}}
            <div class="adm-card p-3 shadow-sm border-0">
                <h6 class="fw-bold mb-3 text-dark"><i class="fa-solid fa-bolt text-warning me-2"></i>Quick Links</h6>
                <div class="d-flex flex-column gap-2">
                    <a href="{{ route('admin.roles.index') }}" class="btn btn-light text-start btn-sm py-2 d-flex justify-content-between align-items-center">
                        <span><i class="fa-solid fa-key text-primary me-2"></i>Roles & Permissions Matrix</span>
                        <i class="fa-solid fa-chevron-right text-muted small"></i>
                    </a>
                    <a href="{{ route('admin.users.security.index') }}" class="btn btn-light text-start btn-sm py-2 d-flex justify-content-between align-items-center">
                        <span><i class="fa-solid fa-user-lock text-danger me-2"></i>Login Security & IP Blocklist</span>
                        <i class="fa-solid fa-chevron-right text-muted small"></i>
                    </a>
                    <a href="{{ route('admin.backup.index') }}" class="btn btn-light text-start btn-sm py-2 d-flex justify-content-between align-items-center">
                        <span><i class="fa-solid fa-database text-info me-2"></i>Database Backup & Restore</span>
                        <i class="fa-solid fa-chevron-right text-muted small"></i>
                    </a>
                    <a href="{{ route('admin.cache.manage') }}" class="btn btn-light text-start btn-sm py-2 d-flex justify-content-between align-items-center">
                        <span><i class="fa-solid fa-broom text-success me-2"></i>System Cache Manager</span>
                        <i class="fa-solid fa-chevron-right text-muted small"></i>
                    </a>
                </div>
            </div>
        </div>

        {{-- Right Column: Multi-Dimensional Tabs & Forms --}}
        <div class="col-12 col-lg-8">
            <div class="adm-card p-0 shadow-sm border-0 overflow-hidden">
                {{-- Navigation Tabs --}}
                <ul class="nav nav-tabs nav-fill bg-light px-3 pt-3 border-bottom" id="profileTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-bold py-2.5" id="tab-general" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab">
                            <i class="fa-solid fa-user-gear me-1.5 text-primary"></i>General Info
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold py-2.5" id="tab-preferences" data-bs-toggle="tab" data-bs-target="#preferences" type="button" role="tab">
                            <i class="fa-solid fa-sliders me-1.5 text-success"></i>Preferences
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold py-2.5" id="tab-signature" data-bs-toggle="tab" data-bs-target="#signature" type="button" role="tab">
                            <i class="fa-solid fa-signature me-1.5 text-info"></i>Signature & Seal
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold py-2.5" id="tab-security" data-bs-toggle="tab" data-bs-target="#security" type="button" role="tab">
                            <i class="fa-solid fa-lock me-1.5 text-danger"></i>Password & Security
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold py-2.5" id="tab-logs" data-bs-toggle="tab" data-bs-target="#logs" type="button" role="tab">
                            <i class="fa-solid fa-list-check me-1.5 text-warning"></i>Login Logs
                        </button>
                    </li>
                </ul>

                <div class="tab-content p-4" id="profileTabsContent">
                    {{-- Tab 1: General Info & Avatar Upload --}}
                    <div class="tab-pane fade show active" id="general" role="tabpanel">
                        <form method="POST" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data">
                            @csrf
                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-bold small text-dark">Full Name (Display Name) <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required placeholder="e.g. System Administrator">
                                    <small class="text-muted d-block mt-1"><i class="fa-solid fa-circle-info text-primary me-1"></i>Displayed across the dashboard and printed documents.</small>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-bold small text-dark">Official Email <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control font-monospace" value="{{ old('email', $user->email) }}" required placeholder="admin@ideaprokashon.com">
                                    <small class="text-muted d-block mt-1"><i class="fa-solid fa-shield-check text-success me-1"></i>Primary login identifier and system alert recipient.</small>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-bold small text-dark">Phone Number</label>
                                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}" placeholder="017XXXXXXXX">
                                    <small class="text-muted d-block mt-1"><i class="fa-solid fa-mobile-screen text-info me-1"></i>Can also be used for mobile OTP login.</small>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-bold small text-dark">Designation / Title</label>
                                    <input type="text" name="designation" class="form-control" value="{{ old('designation', $user->reg_data['designation'] ?? '') }}" placeholder="e.g. Publisher & CEO / System Admin">
                                    <small class="text-muted d-block mt-1">Official title shown on public profile and records.</small>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold small text-dark">Upload Avatar Photo</label>
                                    <input type="file" name="avatar" class="form-control" accept="image/jpeg,image/png,image/webp,image/svg+xml">
                                    <small class="text-muted">Supported formats: JPG, PNG, WebP, SVG (Max: 3MB)</small>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold small text-dark">Bio / Summary</label>
                                    <textarea name="bio" class="form-control" rows="3" placeholder="Brief background summary...">{{ old('bio', $user->reg_data['bio'] ?? '') }}</textarea>
                                </div>
                                <div class="col-12 text-end">
                                    <button type="submit" class="btn btn-primary px-4 shadow-sm">
                                        <i class="fa-solid fa-floppy-disk me-1.5"></i>Save Profile
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    {{-- Tab 2: Customization & Preferences --}}
                    <div class="tab-pane fade" id="preferences" role="tabpanel">
                        <form method="POST" action="{{ route('admin.profile.preferences') }}">
                            @csrf
                            
                            {{-- 1. UI Layout & View Customization --}}
                            <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-desktop me-2 text-primary"></i>Display & Navigation</h6>
                            <div class="row g-3 mb-4">
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-bold small text-dark">Default Landing Page</label>
                                    <select name="landing_page" class="form-select">
                                        <option value="admin.dashboard" @selected(($preferences['landing_page'] ?? '') === 'admin.dashboard')>Admin Dashboard</option>
                                        <option value="admin.pos.index" @selected(($preferences['landing_page'] ?? '') === 'admin.pos.index')>Boi Mela POS</option>
                                        <option value="admin.ecommerce-orders" @selected(($preferences['landing_page'] ?? '') === 'admin.ecommerce-orders')>Book Orders</option>
                                        <option value="admin.books" @selected(($preferences['landing_page'] ?? '') === 'admin.books')>Books Catalog</option>
                                        <option value="admin.accounting.index" @selected(($preferences['landing_page'] ?? '') === 'admin.accounting.index')>Idea Accounting</option>
                                        <option value="subadmin.bills.index" @selected(($preferences['landing_page'] ?? '') === 'subadmin.bills.index')>Seller Billing Panel</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-bold small text-dark">Color Theme</label>
                                    <select name="theme" class="form-select">
                                        <option value="auto" @selected(($preferences['theme'] ?? '') === 'auto')>Auto (System Detect)</option>
                                        <option value="light" @selected(($preferences['theme'] ?? '') === 'light')>Light Mode</option>
                                        <option value="dark" @selected(($preferences['theme'] ?? '') === 'dark')>Dark Mode</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label fw-bold small text-dark">Rows Per Table Page</label>
                                    <select name="table_per_page" class="form-select">
                                        <option value="10" @selected(($preferences['table_per_page'] ?? 20) == 10)>10 Rows</option>
                                        <option value="20" @selected(($preferences['table_per_page'] ?? 20) == 20)>20 Rows (Standard)</option>
                                        <option value="50" @selected(($preferences['table_per_page'] ?? 20) == 50)>50 Rows</option>
                                        <option value="100" @selected(($preferences['table_per_page'] ?? 20) == 100)>100 Rows</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label fw-bold small text-dark">Numeral Format</label>
                                    <select name="number_format" class="form-select">
                                        <option value="bengali" @selected(($preferences['number_format'] ?? 'bengali') === 'bengali')>Bengali (১২,৫০০)</option>
                                        <option value="english" @selected(($preferences['number_format'] ?? '') === 'english')>English (12,500)</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label fw-bold small text-dark">Audio Feedback</label>
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" name="sound_effects" id="soundEffects" value="1" @checked(!empty($preferences['sound_effects']))>
                                        <label class="form-check-label fw-semibold text-dark small" for="soundEffects">
                                            Sound on POS & Orders
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            {{-- 2. Instant Alerts & External Messaging --}}
                            <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-bell me-2 text-warning"></i>Instant Alerts & Messaging</h6>
                            <div class="row g-3 mb-4">
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-bold small text-dark">Telegram Bot Chat ID</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light text-primary"><i class="fab fa-telegram"></i></span>
                                        <input type="text" name="telegram_chat_id" class="form-control font-monospace" placeholder="e.g. 123456789" value="{{ $preferences['telegram_chat_id'] ?? '' }}">
                                    </div>
                                    <small class="text-muted">Real-time Telegram alerts for backups and large orders</small>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-bold small text-dark">WhatsApp Alert Number</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light text-success"><i class="fab fa-whatsapp"></i></span>
                                        <input type="text" name="whatsapp_alerts" class="form-control font-monospace" placeholder="017XXXXXXXX" value="{{ $preferences['whatsapp_alerts'] ?? '' }}">
                                    </div>
                                    <small class="text-muted">Critical payment or security incident notifications</small>
                                </div>
                            </div>

                            <div class="p-3 bg-light rounded-3 border mb-4">
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" name="notify_orders" id="notifyOrders" value="1" @checked(!empty($preferences['notify_orders']))>
                                    <label class="form-check-label fw-semibold text-dark small" for="notifyOrders">
                                        Send instant email alerts for new book orders
                                    </label>
                                </div>
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" name="notify_registrations" id="notifyReg" value="1" @checked(!empty($preferences['notify_registrations']))>
                                    <label class="form-check-label fw-semibold text-dark small" for="notifyReg">
                                        Send email alerts for new author, publisher, or seller applications
                                    </label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="notify_tickets" id="notifyTickets" value="1" @checked(!empty($preferences['notify_tickets']))>
                                    <label class="form-check-label fw-semibold text-dark small" for="notifyTickets">
                                        Send email alerts for urgent support tickets
                                    </label>
                                </div>
                            </div>

                            <hr class="my-4">

                            {{-- 3. IP Whitelist Security Binding --}}
                            <h6 class="fw-bold text-dark mb-2"><i class="fa-solid fa-network-wired me-2 text-danger"></i>IP Whitelist & Restrictions</h6>
                            <p class="text-muted small mb-2">Restrict access to specific IPs (comma separated, leave empty to allow all):</p>
                            <input type="text" name="ip_whitelist" class="form-control font-monospace mb-4" placeholder="e.g. 103.145.12.5, 127.0.0.1" value="{{ $preferences['ip_whitelist'] ?? '' }}">

                            <div class="text-end">
                                <button type="submit" class="btn btn-success px-4 shadow-sm">
                                    <i class="fa-solid fa-check me-1.5"></i>Save Preferences
                                </button>
                            </div>
                        </form>
                    </div>

                    {{-- Tab 3: Digital Signature & Official Seal --}}
                    <div class="tab-pane fade" id="signature" role="tabpanel">
                        <div class="row g-4 align-items-center">
                            <div class="col-12 col-md-5 text-center">
                                <div class="p-3 bg-light rounded-3 border">
                                    <p class="fw-bold small text-dark mb-2">Current Signature Preview</p>
                                    @if(!empty($user->reg_data['signature']) && \Illuminate\Support\Facades\Storage::disk('public')->exists($user->reg_data['signature']))
                                        <div class="p-3 bg-white border rounded shadow-xs mb-2 d-inline-block">
                                            <img src="{{ asset('storage/' . $user->reg_data['signature']) }}" alt="Signature" style="max-height: 90px; max-width: 220px;">
                                        </div>
                                        <form method="POST" action="{{ route('admin.profile.signature.remove') }}"
                                              data-confirm="Are you sure you want to remove your digital signature?"
                                              data-confirm-title="Remove Signature"
                                              data-confirm-icon="warning"
                                              data-confirm-btn="<i class='fa-solid fa-trash-can me-1'></i> Remove">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                                <i class="fa-solid fa-trash-can me-1"></i>Remove Signature
                                            </button>
                                        </form>
                                    @else
                                        <div class="p-4 border border-dashed rounded bg-white text-muted">
                                            <i class="fa-solid fa-file-signature fs-2 text-muted mb-2 d-block opacity-50"></i>
                                            No signature uploaded yet.
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-12 col-md-7">
                                <h6 class="fw-bold text-dark mb-2"><i class="fa-solid fa-signature text-info me-2"></i>Upload Digital Signature & Seal</h6>
                                <p class="text-muted small mb-3">
                                    Your uploaded signature or seal will automatically be printed on official customer invoices, receipts, delivery challans, and payroll vouchers.
                                </p>

                                <form method="POST" action="{{ route('admin.profile.signature') }}" enctype="multipart/form-data">
                                    @csrf
                                    <div class="mb-3">
                                        <input type="file" name="signature" class="form-control" accept="image/png,image/webp,image/svg+xml" required>
                                        <small class="text-muted">Transparent PNG/SVG recommended (Max: 2MB)</small>
                                    </div>
                                    <button type="submit" class="btn btn-info text-white px-4">
                                        <i class="fa-solid fa-cloud-arrow-up me-1.5"></i>Upload Signature
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    {{-- Tab 4: Security & Password --}}
                    <div class="tab-pane fade" id="security" role="tabpanel">
                        <form method="POST" action="{{ route('admin.profile.password') }}">
                            @csrf
                            <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-key me-2 text-danger"></i>Change Password</h6>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-bold small text-dark">Current Password <span class="text-danger">*</span></label>
                                    <input type="password" name="current_password" class="form-control" required placeholder="Enter current password">
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-bold small text-dark">New Password <span class="text-danger">*</span></label>
                                    <input type="password" name="password" class="form-control" required placeholder="Minimum 6 characters">
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-bold small text-dark">Confirm New Password <span class="text-danger">*</span></label>
                                    <input type="password" name="password_confirmation" class="form-control" required placeholder="Re-enter new password">
                                </div>
                                <div class="col-12 text-end">
                                    <button type="submit" class="btn btn-danger px-4">
                                        <i class="fa-solid fa-lock me-1.5"></i>Update Password
                                    </button>
                                </div>
                            </div>
                        </form>

                        <hr class="my-4">

                        {{-- Logout from Other Devices --}}
                        <div class="p-3 bg-light rounded-3 border">
                            <h6 class="fw-bold text-dark mb-1"><i class="fa-solid fa-laptop-code me-2 text-primary"></i>Logout Other Sessions & Devices</h6>
                            <p class="text-muted small mb-3">Terminate all active login sessions on other computers and mobile devices.</p>
                            
                            <form method="POST" action="{{ route('admin.profile.logout-others') }}" class="d-flex flex-wrap gap-2"
                                  data-confirm="Are you sure you want to log out from all other devices?"
                                  data-confirm-title="Logout Other Devices"
                                  data-confirm-icon="warning"
                                  data-confirm-btn="<i class='fa-solid fa-right-from-bracket me-1'></i> Yes, Logout All">
                                @csrf
                                <input type="password" name="password" class="form-control form-control-sm" placeholder="Enter password to confirm" required style="max-width: 250px;">
                                <button type="submit" class="btn btn-sm btn-outline-dark">
                                    <i class="fa-solid fa-right-from-bracket me-1"></i>Logout All Other Devices
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- Tab 5: Login Audit Trail --}}
                    <div class="tab-pane fade" id="logs" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold text-dark mb-0"><i class="fa-solid fa-clock-rotate-left me-2 text-primary"></i>Recent Login & Security Logs</h6>
                            <span class="badge bg-light text-muted border">Last 8 Activities</span>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 small">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date & Time</th>
                                        <th>IP Address</th>
                                        <th>Device / Browser</th>
                                        <th class="text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($securityLogs as $log)
                                        <tr>
                                            <td class="fw-semibold text-dark">{{ $log->created_at->format('d M Y, h:i A') }}</td>
                                            <td><span class="font-monospace text-primary">{{ $log->ip_address }}</span></td>
                                            <td class="text-truncate" style="max-width: 250px;" title="{{ $log->user_agent }}">
                                                {{ $log->user_agent ? Str::limit($log->user_agent, 40) : 'Chrome / Windows' }}
                                            </td>
                                            <td class="text-center">
                                                @if($log->status === 'success' || empty($log->is_blocked))
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle">Success</span>
                                                @else
                                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Failed / Blocked</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-4 text-muted">
                                                <i class="fa-solid fa-shield-halved fs-3 mb-2 d-block opacity-50"></i>
                                                No security or login records found.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Tab deep linking based on URL Hash (#preferences, #security, #signature, #logs, #general)
    document.addEventListener('DOMContentLoaded', function () {
        let hash = window.location.hash;
        if (hash) {
            let triggerEl = document.querySelector(`#profileTabs button[data-bs-target="${hash}"]`);
            if (triggerEl) {
                let tab = new bootstrap.Tab(triggerEl);
                tab.show();
            }
        }

        // Update URL hash on tab switch
        document.querySelectorAll('#profileTabs button[data-bs-toggle="tab"]').forEach(tabBtn => {
            tabBtn.addEventListener('shown.bs.tab', function (e) {
                let targetId = e.target.getAttribute('data-bs-target');
                if (history.pushState) {
                    history.pushState(null, null, targetId);
                } else {
                    location.hash = targetId;
                }
            });
        });
    });
</script>
@endpush
@endsection
