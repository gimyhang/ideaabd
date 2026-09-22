@extends('layouts.admin')

@section('title', 'Employees & Staff Directory — Idea Prakashan')

@section('content')
<div class="container-fluid px-3 px-md-4 py-3">

    <!-- Top Action & Metric Header -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
        <div class="card-body p-3 p-md-4">
            <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1 small fw-bold">
                            <i class="fa-solid fa-users me-1"></i> HR & Payroll Management
                        </span>
                        <span class="badge rounded-pill px-3 py-1 small fw-bold" style="background-color: #f3e8ff; color: #7e22ce; border: 1px solid #d8b4fe;">
                            <i class="fa-solid fa-book-open-reader me-1"></i> Piece-Rate & Book Binder Support
                        </span>
                    </div>
                    <h4 class="fw-bold text-dark mb-0">Employees & Press Artisans Directory</h4>
                    <p class="text-muted small mb-0">Manage monthly salaried staff, daily wage workers, and piece-rate book binders with work logs & ledgers.</p>
                </div>

                <div class="d-flex align-items-center gap-2 flex-wrap ms-md-auto">
                    <a href="{{ route('admin.accounting.salary.index') }}" class="btn btn-outline-primary rounded-pill px-3.5 py-2 fw-semibold shadow-2xs">
                        <i class="fa-solid fa-money-check-dollar me-1"></i> Payroll & Disbursements
                    </a>
                    <button type="button" class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">
                        <i class="fa-solid fa-user-plus me-1.5"></i> Add Staff / Artisan
                    </button>
                </div>
            </div>

            <hr class="my-3 opacity-25">

            <!-- Summary KPI Badges (4 Cards) -->
            <div class="row g-3">
                <div class="col-6 col-lg-3">
                    <div class="p-3 bg-light rounded-3 border d-flex align-items-center justify-content-between h-100">
                        <div>
                            <span class="small text-muted fw-semibold">Total Staff</span>
                            <h5 class="fw-bold text-dark mb-0 font-monospace">{{ $totalEmployees }} Persons</h5>
                            <span class="text-muted" style="font-size: 11.5px;">Active: <strong class="text-success">{{ $activeEmployees }}</strong></span>
                        </div>
                        <span class="badge bg-white text-dark border p-2.5 rounded-circle fs-5"><i class="fa-solid fa-users"></i></span>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="p-3 bg-light rounded-3 border d-flex align-items-center justify-content-between h-100">
                        <div>
                            <span class="small text-muted fw-semibold">Monthly Fixed Payroll</span>
                            <h5 class="fw-bold text-primary mb-0 font-monospace">৳{{ number_format($monthlyPayroll, 2) }}</h5>
                            <span class="text-muted" style="font-size: 11.5px;">Permanent Budget</span>
                        </div>
                        <span class="badge bg-white text-primary border p-2.5 rounded-circle fs-5"><i class="fa-solid fa-hand-holding-dollar"></i></span>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="p-3 bg-light rounded-3 border d-flex align-items-center justify-content-between h-100" style="border-left: 3px solid #9333ea !important;">
                        <div>
                            <span class="small text-muted fw-semibold">Piece-Rate / Book Binders</span>
                            <h5 class="fw-bold mb-0 font-monospace" style="color: #7e22ce;">{{ $pieceRateCount }} Persons</h5>
                            <span class="text-muted" style="font-size: 11.5px;">Book & Forma Binding</span>
                        </div>
                        <span class="badge bg-white border p-2.5 rounded-circle fs-5" style="color: #7e22ce;"><i class="fa-solid fa-book-bookmark"></i></span>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="p-3 bg-light rounded-3 border d-flex align-items-center justify-content-between h-100" style="border-left: 3px solid #f59e0b !important;">
                        <div>
                            <span class="small text-muted fw-semibold">Daily Wage Staff</span>
                            <h5 class="fw-bold text-warning mb-0 font-monospace">{{ $dailyWageCount }} Persons</h5>
                            <span class="text-muted" style="font-size: 11.5px;">Attendance Based</span>
                        </div>
                        <span class="badge bg-white text-warning border p-2.5 rounded-circle fs-5"><i class="fa-solid fa-business-time"></i></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Category / Employment Type Filter Bar -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
        <div class="card-body p-3">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                <div class="d-flex flex-wrap gap-1.5">
                    <a href="{{ route('admin.accounting.employees.index', array_merge(request()->except('department', 'employment_type', 'page'))) }}" 
                       class="btn btn-sm rounded-pill px-3 py-1.5 fw-semibold {{ empty($department) && empty($employmentType) ? 'btn-dark text-white' : 'btn-light border text-dark' }}">
                        🌐 All Staff ({{ $totalEmployees }})
                    </a>
                    <a href="{{ route('admin.accounting.employees.index', array_merge(request()->except('page', 'employment_type'), ['department' => 'Digital Marketing'])) }}" 
                       class="btn btn-sm rounded-pill px-3 py-1.5 fw-semibold {{ str_contains((string)$department, 'Digital') ? 'btn-primary text-white' : 'btn-light border text-dark' }}">
                        📱 Digital Marketing
                    </a>
                    <a href="{{ route('admin.accounting.employees.index', array_merge(request()->except('page', 'employment_type'), ['department' => 'Content & Editorial'])) }}" 
                       class="btn btn-sm rounded-pill px-3 py-1.5 fw-semibold {{ str_contains((string)$department, 'Editorial') || str_contains((string)$department, 'Content') ? 'btn-warning text-dark' : 'btn-light border text-dark' }}">
                        ✍️ Content & Editorial
                    </a>
                    <a href="{{ route('admin.accounting.employees.index', array_merge(request()->except('page', 'employment_type'), ['department' => 'Technical & IT'])) }}" 
                       class="btn btn-sm rounded-pill px-3 py-1.5 fw-semibold {{ str_contains((string)$department, 'Technical') || str_contains((string)$department, 'IT') ? 'btn-success text-white' : 'btn-light border text-dark' }}">
                        💻 Technical & IT
                    </a>
                    <a href="{{ route('admin.accounting.employees.index', array_merge(request()->except('page', 'employment_type'), ['department' => 'Operations & Support'])) }}" 
                       class="btn btn-sm rounded-pill px-3 py-1.5 fw-semibold {{ str_contains((string)$department, 'Operations') || str_contains((string)$department, 'Support') ? 'btn-danger text-white' : 'btn-light border text-dark' }}">
                        ⚙️ Operations & Support
                    </a>
                    <a href="{{ route('admin.accounting.employees.index', array_merge(request()->except('page', 'department'), ['employment_type' => 'contract_piece'])) }}" 
                       class="btn btn-sm rounded-pill px-3 py-1.5 fw-semibold {{ $employmentType === 'contract_piece' ? 'btn-purple text-white' : 'btn-light border text-dark' }}" style="{{ $employmentType === 'contract_piece' ? 'background-color: #7e22ce;' : '' }}">
                        📚 Piece-Rate / Binders ({{ $pieceRateCount }})
                    </a>
                    <a href="{{ route('admin.accounting.employees.index', array_merge(request()->except('page', 'department'), ['employment_type' => 'daily'])) }}" 
                       class="btn btn-sm rounded-pill px-3 py-1.5 fw-semibold {{ $employmentType === 'daily' ? 'btn-warning text-dark' : 'btn-light border text-dark' }}">
                        ⏱️ Daily Wage ({{ $dailyWageCount }})
                    </a>
                    <a href="{{ route('admin.accounting.employees.index', array_merge(request()->except('page', 'department'), ['employment_type' => 'monthly'])) }}" 
                       class="btn btn-sm rounded-pill px-3 py-1.5 fw-semibold {{ $employmentType === 'monthly' ? 'btn-primary text-white' : 'btn-light border text-dark' }}">
                        💼 Monthly Fixed
                    </a>
                </div>
            </div>

            <form action="{{ route('admin.accounting.employees.index') }}" method="GET" class="row g-2 align-items-center">
                @if($employmentType)
                    <input type="hidden" name="employment_type" value="{{ $employmentType }}">
                @endif
                <div class="col-md-5">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                        <input type="text" name="search" value="{{ $search }}" class="form-control rounded-end-3" placeholder="Search by name, role, trade or phone...">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="department" class="form-select form-select-sm rounded-3">
                        <option value="">All Departments</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept }}" @selected($department === $dept)>{{ $dept }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select form-select-sm rounded-3">
                        <option value="">All Statuses</option>
                        <option value="active" @selected($status === 'active')>Active</option>
                        <option value="inactive" @selected($status === 'inactive')>Inactive</option>
                        <option value="on_leave" @selected($status === 'on_leave')>On Leave</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3 w-100 fw-semibold">Filter</button>
                    @if($search || $department || $status || $employmentType)
                        <a href="{{ route('admin.accounting.employees.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5" title="Reset Filters"><i class="fa-solid fa-xmark"></i></a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Employees List Table -->
    <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="employeeTable">
                    <thead class="bg-light table-light small text-muted">
                        <tr>
                            <th class="ps-3.5" style="min-width: 240px;">Staff / Artisan Name & Role</th>
                            <th style="min-width: 200px;">Department & Trade</th>
                            <th style="min-width: 160px;">Employment Type</th>
                            <th class="text-end" style="min-width: 170px;">Salary / Rate (৳)</th>
                            <th style="min-width: 150px;">Contact Details</th>
                            <th class="text-center" style="width: 100px;">Status</th>
                            <th class="text-end pe-3.5" style="width: 170px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($employees as $emp)
                            @php
                                $empType = $emp->employment_type ?? 'monthly';
                                $roleCfg = $emp->getRoleConfig();
                            @endphp
                            <tr>
                                <td class="ps-3.5">
                                    <div class="d-flex align-items-center gap-2.5">
                                        <div class="rounded-circle fw-bold d-flex align-items-center justify-content-center flex-shrink-0" 
                                             style="width: 42px; height: 42px; background-color: {{ $roleCfg['bg_color'] }}; color: {{ $roleCfg['text_color'] }}; font-size: 16px; border: 1px solid {{ $roleCfg['border_color'] }};">
                                            <i class="{{ $roleCfg['icon'] }}" style="font-size: 15px;"></i>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold text-dark mb-0 font-monospace-title">{{ $emp->name }}</h6>
                                            <div class="d-flex align-items-center gap-1.5 mt-0.5">
                                                <span class="small text-muted">{{ $emp->designation }}</span>
                                                @if($emp->skill_category)
                                                    <span class="badge bg-light text-secondary border px-1.5 py-0" style="font-size: 9.5px;">{{ $emp->skill_category }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <span class="badge rounded-pill px-2.5 py-1 small fw-semibold" 
                                              style="background-color: {{ $roleCfg['bg_color'] }}; color: {{ $roleCfg['text_color'] }}; border: 1px solid {{ $roleCfg['border_color'] }}; font-size: 11px;">
                                            <i class="{{ $roleCfg['icon'] }} me-1"></i>{{ $emp->department }}
                                        </span>
                                    </div>
                                    <span class="text-muted d-block mt-1" style="font-size: 11px;">
                                        Schedule: <strong>{{ ucfirst($emp->payment_schedule ?: 'monthly') }}</strong>
                                    </span>
                                </td>
                                <td>
                                    @if($empType === 'contract_piece')
                                        <span class="badge border px-2.5 py-1 small rounded-pill fw-bold" style="background-color: #f3e8ff; color: #7e22ce; border-color: #d8b4fe;">
                                            <i class="fa-solid fa-book-bookmark me-1"></i> Piece-Rate Binder
                                        </span>
                                    @elseif($empType === 'daily')
                                        <span class="badge border px-2.5 py-1 small rounded-pill fw-bold" style="background-color: #fef3c7; color: #b45309; border-color: #fde68a;">
                                            <i class="fa-solid fa-business-time me-1"></i> Daily Wage
                                        </span>
                                    @elseif($empType === 'weekly')
                                        <span class="badge border px-2.5 py-1 small rounded-pill fw-bold" style="background-color: #e0f2fe; color: #0284c7; border-color: #bae6fd;">
                                            <i class="fa-solid fa-calendar-week me-1"></i> Weekly Wage
                                        </span>
                                    @elseif($empType === 'contract_project')
                                        <span class="badge border px-2.5 py-1 small rounded-pill fw-bold bg-secondary-subtle text-secondary">
                                            <i class="fa-solid fa-briefcase me-1"></i> Project Basis
                                        </span>
                                    @else
                                        <span class="badge border px-2.5 py-1 small rounded-pill fw-bold bg-primary-subtle text-primary border-primary-subtle">
                                            <i class="fa-solid fa-user-check me-1"></i> Monthly Fixed
                                        </span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="fw-bold font-monospace fs-6" style="{{ $empType === 'contract_piece' ? 'color: #7e22ce;' : '' }}">
                                        {{ $emp->formatted_rate }}
                                    </div>
                                    @if($emp->salary_payments_sum_net_paid > 0)
                                        <span class="text-muted" style="font-size: 10.5px;">Paid: ৳{{ number_format($emp->salary_payments_sum_net_paid, 2) }}</span>
                                    @endif
                                </td>
                                <td class="small text-muted">
                                    <div class="d-flex align-items-center gap-1.5">
                                        @if($emp->phone)
                                            <a href="tel:{{ $emp->phone }}" class="btn btn-xs btn-outline-success rounded-circle p-1" title="Call {{ $emp->phone }}" style="width: 26px; height: 26px; display: grid; place-items: center;">
                                                <i class="fa-solid fa-phone" style="font-size: 11px;"></i>
                                            </a>
                                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $emp->phone) }}" target="_blank" class="btn btn-xs btn-outline-success rounded-circle p-1" title="WhatsApp" style="width: 26px; height: 26px; display: grid; place-items: center; border-color: #25d366; color: #25d366;">
                                                <i class="fab fa-whatsapp" style="font-size: 12px;"></i>
                                            </a>
                                            <span class="font-monospace text-dark fw-semibold" style="font-size: 11px;">{{ $emp->phone }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </div>
                                    @if($emp->email)
                                        <div class="mt-0.5 text-muted" style="font-size: 10.5px;"><i class="fa-solid fa-envelope me-1 text-primary"></i>{{ $emp->email }}</div>
                                    @endif
                                </td>
                                <td class="text-center" id="staff-status-cell-{{ $emp->id }}">
                                    <div class="dropdown d-inline-block">
                                        <button class="btn btn-xs rounded-pill px-2.5 py-0.5 border dropdown-toggle fw-semibold status-toggle-btn-{{ $emp->id }}" 
                                                type="button" data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 11px;">
                                            @if($emp->status === 'active')
                                                <span class="text-success"><i class="fa-solid fa-circle-check me-1"></i>Active</span>
                                            @elseif($emp->status === 'on_leave')
                                                <span class="text-warning"><i class="fa-solid fa-clock me-1"></i>On Leave</span>
                                            @else
                                                <span class="text-secondary"><i class="fa-solid fa-circle-xmark me-1"></i>Inactive</span>
                                            @endif
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 py-1" style="font-size: 12px; min-width: 130px;">
                                            <li><a class="dropdown-item py-1 text-success fw-semibold" href="javascript:void(0)" onclick="quickToggleStaffStatus({{ $emp->id }}, 'active')"><i class="fa-solid fa-circle-check me-1.5"></i> Active</a></li>
                                            <li><a class="dropdown-item py-1 text-warning fw-semibold" href="javascript:void(0)" onclick="quickToggleStaffStatus({{ $emp->id }}, 'on_leave')"><i class="fa-solid fa-clock me-1.5"></i> On Leave</a></li>
                                            <li><a class="dropdown-item py-1 text-danger fw-semibold" href="javascript:void(0)" onclick="quickToggleStaffStatus({{ $emp->id }}, 'inactive')"><i class="fa-solid fa-circle-xmark me-1.5"></i> Inactive</a></li>
                                        </ul>
                                    </div>
                                </td>
                                <td class="text-end pe-3.5">
                                    <div class="d-inline-flex align-items-center gap-1.5">
                                        <button type="button" class="btn btn-sm btn-light border rounded-pill px-2 py-1 text-primary shadow-2xs" style="font-size: 11px;" title="Quick View Profile" onclick="openQuickStaffDetails({{ $emp->id }})">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>
                                        <a href="{{ route('admin.accounting.employees.ledger', $emp->id) }}" 
                                           class="btn btn-sm rounded-pill px-2.5 py-1 small fw-semibold shadow-2xs {{ $empType === 'contract_piece' ? 'btn-purple text-white' : 'btn-outline-primary' }}"
                                           style="{{ $empType === 'contract_piece' ? 'background-color: #7e22ce; border-color: #7e22ce;' : '' }}"
                                           title="Daily Work Log & Cash Withdrawals">
                                            <i class="fa-solid fa-book-bookmark me-1"></i>Work & Ledger
                                        </a>

                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary rounded-pill px-2.5 py-1" type="button" data-bs-toggle="dropdown">
                                                <i class="fa-solid fa-ellipsis-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3">
                                                <li>
                                                    <a href="{{ route('admin.accounting.employees.ledger', $emp->id) }}" class="dropdown-item small">
                                                        <i class="fa-solid fa-book-bookmark text-purple me-2" style="color: #7e22ce;"></i> Work Log & Cash Ledger
                                                    </a>
                                                </li>
                                                <li>
                                                    <button type="button" class="dropdown-item small" onclick="openEditEmployeeModal({{ $emp->toJson() }})">
                                                        <i class="fa-solid fa-pen-to-square text-primary me-2"></i> Edit Details
                                                    </button>
                                                </li>
                                                <li>
                                                    <a href="{{ route('admin.accounting.salary.index', ['employee_id' => $emp->id]) }}" class="dropdown-item small">
                                                        <i class="fa-solid fa-receipt text-success me-2"></i> Payment & Slips
                                                    </a>
                                                </li>
                                                <li><hr class="dropdown-divider my-1"></li>
                                                <li>
                                                    <form action="{{ route('admin.accounting.employees.destroy', $emp->id) }}" method="POST" data-confirm="আপনি কি নিশ্চিত যে এই কর্মী রেকর্ডটি ({{ $emp->name }}) মুছে ফেলতে চান?" data-confirm-title="কর্মী ডিলিট">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="dropdown-item small text-danger">
                                                            <i class="fa-solid fa-trash me-2"></i> Delete Staff
                                                        </button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-users text-muted opacity-50 fs-2 mb-2"></i>
                                    <p class="small mb-0">No employee or artisan records found. Click above to add new staff.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($employees->hasPages())
                <div class="p-3 border-top d-flex justify-content-center">
                    {{ $employees->links() }}
                </div>
            @endif
        </div>
    </div>

</div>

<!-- Modal: Add Employee / Worker -->
<div class="modal fade" id="addEmployeeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form action="{{ route('admin.accounting.employees.store') }}" method="POST" class="modal-content rounded-4 border-0 shadow-lg">
            @csrf
            <div class="modal-header bg-dark text-white border-0 py-3">
                <h5 class="modal-title fw-bold fs-6 d-flex align-items-center gap-2">
                    <i class="fa-solid fa-user-plus text-primary"></i> Add New Employee / Artisan
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                
                {{-- Fast 1-Click Role Presets for Publishing, Design, Computer, Marketing, Press & Office Staff --}}
                <div class="mb-3 p-3 bg-light rounded-3 border">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="small fw-bold text-dark">
                            <i class="fa-solid fa-bolt text-warning me-1"></i> Quick 1-Click Role Presets (দ্রুত রোল নির্বাচন):
                        </span>
                    </div>
                    <select class="form-select form-select-sm rounded-pill border-primary fw-semibold" id="addRolePresetSelect" onchange="applyEmployeePreset('add', this.value)">
                        <option value="">-- Select Staff / Worker Preset (যেকোনো স্টাফ সিলেক্ট করুন) --</option>
                        <optgroup label="📱 1. Digital Marketing Class (ডিজিটাল মার্কেটিং)">
                            <option value='{"name":"","desig":"Digital Marketing Specialist & Media Buyer","dept":"Digital Marketing (ডিজিটাল মার্কেটিং)","type":"monthly","skill":"Digital Marketing Specialist & Media Buyer (ডিজিটাল মার্কেটিং ও মিডিয়া বায়ার)","rate_type":"monthly","unit":"Month (মাস)","rate":25000.00,"schedule":"monthly"}'>📱 Digital Marketing Specialist & Media Buyer (Monthly: ৳25,000)</option>
                            <option value='{"name":"","desig":"SEO & Social Media Campaign Manager","dept":"Digital Marketing (ডিজিটাল মার্কেটিং)","type":"monthly","skill":"SEO & Social Media Campaign Manager (এসইও ও সোশ্যাল মিডিয়া ম্যানেজার)","rate_type":"monthly","unit":"Month (মাস)","rate":20000.00,"schedule":"monthly"}'>📱 SEO & Social Media Manager (Monthly: ৳20,000)</option>
                        </optgroup>
                        <optgroup label="✍️ 2. Content & Editorial Class (কনটেন্ট ও সম্পাদকীয়)">
                            <option value='{"name":"","desig":"Executive Editor & Content Lead","dept":"Content & Editorial (কনটেন্ট ও সম্পাদকীয়)","type":"monthly","skill":"Executive Editor & Content Lead (প্রধান সম্পাদক ও কনটেন্ট লিড)","rate_type":"monthly","unit":"Month (মাস)","rate":25000.00,"schedule":"monthly"}'>✍️ Executive Editor & Content Lead (Monthly: ৳25,000)</option>
                            <option value='{"name":"","desig":"Proofreader & Sub-Editor","dept":"Content & Editorial (কনটেন্ট ও সম্পাদকীয়)","type":"contract_piece","skill":"Proofreader & Sub-Editor (প্রুফ রিডার ও সাব-এডিটর)","rate_type":"per_forma","unit":"Forma (ফর্মা)","rate":25.00,"schedule":"weekly"}'>✍️ Proofreader & Sub-Editor (Piece-rate: ৳25.00 / Forma)</option>
                            <option value='{"name":"","desig":"Book Layout & Typesetter","dept":"Content & Editorial (কনটেন্ট ও সম্পাদকীয়)","type":"contract_piece","skill":"Book Layout & Typesetter (বই লেআউট ও কম্পোজিটর)","rate_type":"per_page","unit":"Page (পৃষ্ঠা)","rate":15.00,"schedule":"weekly"}'>💻 Typesetter & Book Layout (Piece-rate: ৳15.00 / Page)</option>
                        </optgroup>
                        <optgroup label="💻 3. Technical & IT Class (টেকনিক্যাল ও আইটি)">
                            <option value='{"name":"","desig":"Full-Stack Web & Software Developer","dept":"Technical & IT (টেকনিক্যাল ও আইটি)","type":"monthly","skill":"Full-Stack Web & Software Developer (সফটওয়্যার ও ওয়েব ডেভেলপার)","rate_type":"monthly","unit":"Month (মাস)","rate":35000.00,"schedule":"monthly"}'>💻 Full-Stack Web & Software Developer (Monthly: ৳35,000)</option>
                            <option value='{"name":"","desig":"IT Support & System Administrator","dept":"Technical & IT (টেকনিক্যাল ও আইটি)","type":"monthly","skill":"IT Support & System Administrator (আইটি সাপোর্ট ও সিস্টেম অ্যাডমিন)","rate_type":"monthly","unit":"Month (মাস)","rate":22000.00,"schedule":"monthly"}'>💻 IT Support & Systems Admin (Monthly: ৳22,000)</option>
                            <option value='{"name":"","desig":"UI/UX & Graphics Designer","dept":"Technical & IT (টেকনিক্যাল ও আইটি)","type":"monthly","skill":"UI/UX & Graphics Designer (ইউআই/ইউএক্স ও গ্রাফিক্স ডিজাইনার)","rate_type":"monthly","unit":"Month (মাস)","rate":25000.00,"schedule":"monthly"}'>🎨 UI/UX & Graphics Designer (Monthly: ৳25,000)</option>
                        </optgroup>
                        <optgroup label="⚙️ 4. Operations & Support Class (অপারেশন্স ও কাস্টমার সাপোর্ট)">
                            <option value='{"name":"","desig":"Customer Support & CRM Executive","dept":"Operations & Support (অপারেশনস ও সাপোর্ট)","type":"monthly","skill":"Customer Support & CRM Executive (কাস্টমার সাপোর্ট ও সিআরএম এক্সিকিউটিভ)","rate_type":"monthly","unit":"Month (মাস)","rate":18000.00,"schedule":"monthly"}'>⚙️ Customer Support & CRM Executive (Monthly: ৳18,000)</option>
                            <option value='{"name":"","desig":"Order Fulfillment & Dispatch Officer","dept":"Operations & Support (অপারেশনস ও সাপোর্ট)","type":"monthly","skill":"Order Fulfillment & Dispatch Officer (অর্ডার প্রসেসিং ও ডিসপ্যাচ অফিসার)","rate_type":"monthly","unit":"Month (মাস)","rate":16000.00,"schedule":"monthly"}'>⚙️ Order Fulfillment & Dispatch Officer (Monthly: ৳16,000)</option>
                            <option value='{"name":"","desig":"Office Assistant / Peon","dept":"Operations & Support (অপারেশনস ও সাপোর্ট)","type":"daily","skill":"Office Assistant / Peon / MLSS (অফিস সহায়ক / পিওন)","rate_type":"daily","unit":"Day (দিন)","rate":650.00,"schedule":"daily"}'>🏃 Office Assistant / Peon (Daily Wage: ৳650 / Day)</option>
                        </optgroup>
                        <optgroup label="📚 5. Press & Book Production Artisans (ছাপাখানা ও বাঁধাই কারিগর)">
                            <option value='{"name":"","desig":"Master Book Binder","dept":"ছাপাখানা ও বাঁধাই (Press & Book Binding)","type":"contract_piece","skill":"Master Book Binder (মাস্টার বুক বাইন্ডার ও বাঁধাই কারিগর)","rate_type":"per_book","unit":"Book (বই)","rate":4.50,"schedule":"per_job"}'>📚 Master Book Binder (Piece-rate: ৳4.50 / Book Binding)</option>
                            <option value='{"name":"","desig":"Assistant Binder & Pasting Artisan","dept":"ছাপাখানা ও বাঁধাই (Press & Book Binding)","type":"contract_piece","skill":"Assistant Binder & Pasting Artisan (সহকারী বাইন্ডার ও পেস্টিং কারিগর)","rate_type":"per_forma","unit":"Forma (ফর্মা)","rate":0.60,"schedule":"weekly"}'>📖 Pasting Artisan (Piece-rate: ৳0.60 / Forma)</option>
                            <option value='{"name":"","desig":"Paper Cutting Master","dept":"ছাপাখানা ও বাঁধাই (Press & Book Binding)","type":"daily","skill":"Paper Cutting Master (পেপার কাটিং মাস্টার)","rate_type":"daily","unit":"Day (দিন)","rate":800.00,"schedule":"daily"}'>✂️ Paper Cutting Master (Daily Wage: ৳800 / Day)</option>
                            <option value='{"name":"","desig":"Offset Press Machine Operator","dept":"ছাপাখানা ও বাঁধাই (Press & Book Binding)","type":"monthly","skill":"Offset Press Machine Operator (অফসেট প্রেস মেশিন অপারেটর)","rate_type":"monthly","unit":"Month (মাস)","rate":24000.00,"schedule":"monthly"}'>🖨️ Offset Press Operator (Monthly: ৳24,000 / Month)</option>
                        </optgroup>
                    </select>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-dark">Staff / Artisan Full Name *</label>
                        <input type="text" name="name" id="add_emp_name" class="form-control rounded-3" required placeholder="e.g. Md. Kamrul Hasan">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-dark">Designation / Role *</label>
                        <input type="text" name="designation" id="add_emp_designation" class="form-control rounded-3" required placeholder="e.g. Master Book Binder / Press Operator">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-dark">Department *</label>
                        <select name="department" id="add_emp_department" class="form-select rounded-3" required>
                            @foreach($departments as $dept)
                                <option value="{{ $dept }}">{{ $dept }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-dark">Trade / Skill Category</label>
                        <select name="skill_category" id="add_emp_skill" class="form-select rounded-3">
                            <option value="">-- Select Skill / Trade --</option>
                            @foreach($skillCategories as $skill)
                                <option value="{{ $skill }}">{{ $skill }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-dark">
                            <i class="fa-solid fa-handshake text-primary me-1"></i> Employment Nature *
                        </label>
                        <select name="employment_type" id="add_emp_type" class="form-select rounded-3 fw-semibold border-primary shadow-2xs" required onchange="onEmploymentTypeChanged('add', this.value)">
                            @foreach($employmentTypes as $val => $label)
                                <option value="{{ $val }}" @selected($val === 'monthly')>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-dark">
                            <i class="fa-solid fa-calculator text-success me-1"></i> Salary / Wage Basis (Rate Type) *
                        </label>
                        <select name="salary_rate_type" id="add_emp_rate_type" class="form-select rounded-3 fw-semibold" required onchange="onRateTypeChanged('add', this.value)">
                            @foreach($rateTypes as $val => $label)
                                <option value="{{ $val }}" @selected($val === 'monthly')>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-dark" id="add_rate_label">
                            Salary / Wage / Piece Rate Amount (৳) *
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light fw-bold">৳</span>
                            <input type="number" step="0.01" name="basic_salary" id="add_emp_basic_salary" class="form-control font-monospace fw-bold" required placeholder="e.g. 20000 or 4.50">
                        </div>
                        <span class="small text-muted" id="add_rate_hint">For binders, enter rate per book (e.g. 4.50) or fixed monthly salary</span>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label small fw-semibold text-muted">Unit Name</label>
                        <input type="text" name="rate_unit_name" id="add_emp_unit" class="form-control rounded-3" value="Month" placeholder="e.g. Book, Forma, Day">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label small fw-semibold text-muted">Payment Schedule</label>
                        <select name="payment_schedule" id="add_emp_schedule" class="form-select rounded-3">
                            <option value="monthly">Monthly Payout</option>
                            <option value="weekly">Weekly Payout</option>
                            <option value="per_job">Per Job / Lot Completed</option>
                            <option value="daily">Daily Payout</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-muted">Phone Number</label>
                        <input type="text" name="phone" class="form-control rounded-3" placeholder="01XXXXXXXXX">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-muted">Email Address</label>
                        <input type="email" name="email" class="form-control rounded-3" placeholder="email@example.com">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-muted">Joining Date</label>
                        <input type="date" name="joining_date" value="{{ date('Y-m-d') }}" class="form-control rounded-3">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-muted">Status</label>
                        <select name="status" class="form-select rounded-3">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="on_leave">On Leave</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-muted">National ID / Passport</label>
                        <input type="text" name="nid_passport" class="form-control rounded-3" placeholder="NID Number">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-muted">Emergency Contact</label>
                        <input type="text" name="emergency_contact" class="form-control rounded-3" placeholder="Relative / Guardian Phone">
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold text-muted">Present & Permanent Address</label>
                        <textarea name="address" class="form-control rounded-3" rows="2" placeholder="Street address, City, District..."></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold text-muted">Contract Notes & Specifications</label>
                        <textarea name="notes" class="form-control rounded-3" rows="2" placeholder="Contract terms, piece specifications, or remarks..."></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-top p-3">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">Save Employee</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Edit Employee / Worker -->
<div class="modal fade" id="editEmployeeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form id="editEmployeeForm" method="POST" class="modal-content rounded-4 border-0 shadow-lg">
            @csrf
            @method('PUT')
            <div class="modal-header bg-dark text-white border-0 py-3">
                <h5 class="modal-title fw-bold fs-6 d-flex align-items-center gap-2">
                    <i class="fa-solid fa-pen-to-square text-primary"></i> Edit Staff / Artisan Profile
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-dark">Staff / Artisan Full Name *</label>
                        <input type="text" name="name" id="edit_name" class="form-control rounded-3" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-dark">Designation / Role *</label>
                        <input type="text" name="designation" id="edit_designation" class="form-control rounded-3" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-dark">Department *</label>
                        <select name="department" id="edit_department" class="form-select rounded-3" required>
                            @foreach($departments as $dept)
                                <option value="{{ $dept }}">{{ $dept }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-dark">Trade / Skill Category</label>
                        <select name="skill_category" id="edit_skill" class="form-select rounded-3">
                            <option value="">-- Select Skill / Trade --</option>
                            @foreach($skillCategories as $skill)
                                <option value="{{ $skill }}">{{ $skill }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-dark">
                            <i class="fa-solid fa-handshake text-primary me-1"></i> Employment Nature *
                        </label>
                        <select name="employment_type" id="edit_type" class="form-select rounded-3 fw-semibold border-primary shadow-2xs" required onchange="onEmploymentTypeChanged('edit', this.value)">
                            @foreach($employmentTypes as $val => $label)
                                <option value="{{ $val }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-dark">
                            <i class="fa-solid fa-calculator text-success me-1"></i> Salary / Wage Basis (Rate Type) *
                        </label>
                        <select name="salary_rate_type" id="edit_rate_type" class="form-select rounded-3 fw-semibold" required onchange="onRateTypeChanged('edit', this.value)">
                            @foreach($rateTypes as $val => $label)
                                <option value="{{ $val }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-dark" id="edit_rate_label">
                            Salary / Wage / Piece Rate Amount (৳) *
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light fw-bold">৳</span>
                            <input type="number" step="0.01" name="basic_salary" id="edit_basic_salary" class="form-control font-monospace fw-bold" required placeholder="e.g. 20000 or 4.50">
                        </div>
                        <span class="small text-muted" id="edit_rate_hint">Enter unit rate or fixed monthly salary</span>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label small fw-semibold text-muted">Unit Name</label>
                        <input type="text" name="rate_unit_name" id="edit_unit" class="form-control rounded-3" placeholder="e.g. Book, Forma, Day">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label small fw-semibold text-muted">Payment Schedule</label>
                        <select name="payment_schedule" id="edit_schedule" class="form-select rounded-3">
                            <option value="monthly">Monthly Payout</option>
                            <option value="weekly">Weekly Payout</option>
                            <option value="per_job">Per Job / Lot Completed</option>
                            <option value="daily">Daily Payout</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-muted">Phone Number</label>
                        <input type="text" name="phone" id="edit_phone" class="form-control rounded-3">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-muted">Email Address</label>
                        <input type="email" name="email" id="edit_email" class="form-control rounded-3">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-muted">Joining Date</label>
                        <input type="date" name="joining_date" id="edit_joining_date" class="form-control rounded-3">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-muted">Status</label>
                        <select name="status" id="edit_status" class="form-select rounded-3">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="on_leave">On Leave</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-muted">National ID / Passport</label>
                        <input type="text" name="nid_passport" id="edit_nid" class="form-control rounded-3">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-muted">Emergency Contact</label>
                        <input type="text" name="emergency_contact" id="edit_emergency" class="form-control rounded-3">
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold text-muted">Present & Permanent Address</label>
                        <textarea name="address" id="edit_address" class="form-control rounded-3" rows="2"></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold text-muted">Contract Notes & Remarks</label>
                        <textarea name="notes" id="edit_notes" class="form-control rounded-3" rows="2"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-top p-3">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">Update Employee</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL: QUICK VIEW STAFF PROFILE & LEDGER BREAKDOWN --}}
<div class="modal fade" id="quickStaffDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header bg-dark text-white border-0 py-3">
                <h5 class="modal-title fw-bold fs-6 d-flex align-items-center gap-2">
                    <i class="fa-solid fa-id-card-clip text-primary"></i> Staff Profile & Work Status
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4" id="quickStaffDetailsModalBody">
                <div class="text-center py-4">
                    <i class="fa-solid fa-spinner fa-spin fs-3 text-primary"></i>
                    <p class="small text-muted mt-2">Loading profile details...</p>
                </div>
            </div>
            <div class="modal-footer bg-light border-top p-3 d-flex justify-content-between">
                <a href="#" id="quickStaffLedgerLink" class="btn btn-sm btn-primary rounded-pill px-3.5 fw-semibold">
                    <i class="fa-solid fa-book-bookmark me-1"></i> Full Work Ledger
                </a>
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function onEmploymentTypeChanged(prefix, empType) {
    const rateTypeSelect = document.getElementById(`${prefix === 'add' ? 'add_emp_rate_type' : 'edit_rate_type'}`);
    const unitInput = document.getElementById(`${prefix === 'add' ? 'add_emp_unit' : 'edit_unit'}`);
    const scheduleSelect = document.getElementById(`${prefix === 'add' ? 'add_emp_schedule' : 'edit_schedule'}`);

    if (empType === 'contract_piece') {
        if (rateTypeSelect) rateTypeSelect.value = 'per_book';
        if (unitInput) unitInput.value = 'Book Binding';
        if (scheduleSelect) scheduleSelect.value = 'per_job';
    } else if (empType === 'daily') {
        if (rateTypeSelect) rateTypeSelect.value = 'daily';
        if (unitInput) unitInput.value = 'Day';
        if (scheduleSelect) scheduleSelect.value = 'daily';
    } else if (empType === 'weekly') {
        if (rateTypeSelect) rateTypeSelect.value = 'weekly';
        if (unitInput) unitInput.value = 'Week';
        if (scheduleSelect) scheduleSelect.value = 'weekly';
    } else if (empType === 'contract_project') {
        if (rateTypeSelect) rateTypeSelect.value = 'project_fixed';
        if (unitInput) unitInput.value = 'Project';
        if (scheduleSelect) scheduleSelect.value = 'per_job';
    } else { // monthly
        if (rateTypeSelect) rateTypeSelect.value = 'monthly';
        if (unitInput) unitInput.value = 'Month';
        if (scheduleSelect) scheduleSelect.value = 'monthly';
    }
    onRateTypeChanged(prefix, rateTypeSelect ? rateTypeSelect.value : 'monthly');
}

function onRateTypeChanged(prefix, rateType) {
    const labelEl = document.getElementById(`${prefix}_rate_label`);
    const hintEl = document.getElementById(`${prefix}_rate_hint`);
    const unitInput = document.getElementById(`${prefix === 'add' ? 'add_emp_unit' : 'edit_unit'}`);

    if (rateType === 'per_book') {
        if (labelEl) labelEl.textContent = 'Per Book Binding Rate (৳ / Book) *';
        if (hintEl) hintEl.textContent = 'e.g. 4.50 per book binding';
        if (unitInput && !unitInput.value) unitInput.value = 'Book';
    } else if (rateType === 'per_forma') {
        if (labelEl) labelEl.textContent = 'Per Forma Binding Rate (৳ / Forma) *';
        if (hintEl) hintEl.textContent = 'e.g. 0.60 per forma';
        if (unitInput) unitInput.value = 'Forma';
    } else if (rateType === 'per_thousand') {
        if (labelEl) labelEl.textContent = 'Per 1,000 Sheets Rate (৳ / 1000) *';
        if (hintEl) hintEl.textContent = 'e.g. 350.00 per 1000 sheets';
        if (unitInput) unitInput.value = '1000 Sheets';
    } else if (rateType === 'per_page') {
        if (labelEl) labelEl.textContent = 'Per Page Rate (৳ / Page) *';
        if (hintEl) hintEl.textContent = 'e.g. 15.00 per page';
        if (unitInput) unitInput.value = 'Page';
    } else if (rateType === 'daily') {
        if (labelEl) labelEl.textContent = 'Daily Wage Rate (৳ / Day) *';
        if (hintEl) hintEl.textContent = 'e.g. 800.00 daily wage';
        if (unitInput) unitInput.value = 'Day';
    } else if (rateType === 'weekly') {
        if (labelEl) labelEl.textContent = 'Weekly Wage Rate (৳ / Week) *';
        if (hintEl) hintEl.textContent = 'e.g. 5000.00 weekly wage';
        if (unitInput) unitInput.value = 'Week';
    } else if (rateType === 'project_fixed') {
        if (labelEl) labelEl.textContent = 'Project Fixed Fee (৳ / Project) *';
        if (hintEl) hintEl.textContent = 'Total fixed contract amount';
        if (unitInput) unitInput.value = 'Project';
    } else {
        if (labelEl) labelEl.textContent = 'Monthly Fixed Salary (৳ / Month) *';
        if (hintEl) hintEl.textContent = 'Fixed basic monthly salary';
        if (unitInput) unitInput.value = 'Month';
    }
}

function applyEmployeePreset(prefix, jsonStr) {
    if (!jsonStr) return;
    try {
        const item = JSON.parse(jsonStr);
        if (item.desig) document.getElementById(`${prefix === 'add' ? 'add_emp_designation' : 'edit_designation'}`).value = item.desig;
        if (item.dept) document.getElementById(`${prefix === 'add' ? 'add_emp_department' : 'edit_department'}`).value = item.dept;
        if (item.skill) document.getElementById(`${prefix === 'add' ? 'add_emp_skill' : 'edit_skill'}`).value = item.skill;
        if (item.type) {
            document.getElementById(`${prefix === 'add' ? 'add_emp_type' : 'edit_type'}`).value = item.type;
        }
        if (item.rate_type) {
            document.getElementById(`${prefix === 'add' ? 'add_emp_rate_type' : 'edit_rate_type'}`).value = item.rate_type;
        }
        if (item.unit) {
            document.getElementById(`${prefix === 'add' ? 'add_emp_unit' : 'edit_unit'}`).value = item.unit;
        }
        if (item.rate !== undefined) {
            document.getElementById(`${prefix === 'add' ? 'add_emp_basic_salary' : 'edit_basic_salary'}`).value = item.rate;
        }
        if (item.schedule) {
            document.getElementById(`${prefix === 'add' ? 'add_emp_schedule' : 'edit_schedule'}`).value = item.schedule;
        }
        onRateTypeChanged(prefix, item.rate_type);
    } catch(e) {
        console.error('Error loading preset', e);
    }
}

function openEditEmployeeModal(emp) {
    document.getElementById('edit_name').value = emp.name || '';
    document.getElementById('edit_designation').value = emp.designation || '';
    document.getElementById('edit_department').value = emp.department || '';
    document.getElementById('edit_skill').value = emp.skill_category || '';
    document.getElementById('edit_type').value = emp.employment_type || 'monthly';
    document.getElementById('edit_rate_type').value = emp.salary_rate_type || 'monthly';
    document.getElementById('edit_basic_salary').value = emp.basic_salary || '';
    document.getElementById('edit_unit').value = emp.rate_unit_name || (emp.salary_rate_type === 'per_book' ? 'Book' : 'Month');
    document.getElementById('edit_schedule').value = emp.payment_schedule || 'monthly';
    document.getElementById('edit_phone').value = emp.phone || '';
    document.getElementById('edit_email').value = emp.email || '';
    document.getElementById('edit_joining_date').value = emp.joining_date ? emp.joining_date.substring(0, 10) : '';
    document.getElementById('edit_status').value = emp.status || 'active';
    document.getElementById('edit_nid').value = emp.nid_passport || '';
    document.getElementById('edit_emergency').value = emp.emergency_contact || '';
    document.getElementById('edit_address').value = emp.address || '';
    document.getElementById('edit_notes').value = emp.notes || '';

    onRateTypeChanged('edit', emp.salary_rate_type || 'monthly');

    document.getElementById('editEmployeeForm').action = `{{ url('/admin/accounting/employees') }}/${emp.id}`;

    const modal = new bootstrap.Modal(document.getElementById('editEmployeeModal'));
    modal.show();
}

// Quick Toggle Staff Status via AJAX
function quickToggleStaffStatus(employeeId, newStatus) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const cell = document.getElementById(`staff-status-cell-${employeeId}`);
    
    fetch("{{ route('admin.accounting.employees.quick-status') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({ id: employeeId, status: newStatus })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            if (cell) {
                let statusLabel = '';
                if (newStatus === 'active') {
                    statusLabel = '<span class="text-success"><i class="fa-solid fa-circle-check me-1"></i>Active</span>';
                } else if (newStatus === 'on_leave') {
                    statusLabel = '<span class="text-warning"><i class="fa-solid fa-clock me-1"></i>On Leave</span>';
                } else {
                    statusLabel = '<span class="text-secondary"><i class="fa-solid fa-circle-xmark me-1"></i>Inactive</span>';
                }
                const btn = cell.querySelector(`.status-toggle-btn-${employeeId}`);
                if (btn) btn.innerHTML = statusLabel;
            }
            if (typeof Swal !== 'undefined') {
                Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: data.message, showConfirmButton: false, timer: 2000 });
            }
        }
    })
    .catch(err => {
        console.error('Error toggling staff status:', err);
    });
}

// Quick View Staff Details Modal
function openQuickStaffDetails(employeeId) {
    const modalEl = document.getElementById('quickStaffDetailsModal');
    const bodyEl = document.getElementById('quickStaffDetailsModalBody');
    const ledgerLink = document.getElementById('quickStaffLedgerLink');
    
    bodyEl.innerHTML = `
        <div class="text-center py-4">
            <i class="fa-solid fa-spinner fa-spin fs-3 text-primary"></i>
            <p class="small text-muted mt-2">Loading profile details...</p>
        </div>
    `;
    
    ledgerLink.href = `/admin/accounting/employees/${employeeId}/ledger`;
    const bsModal = new bootstrap.Modal(modalEl);
    bsModal.show();

    fetch(`/admin/accounting/employees/${employeeId}/quick-details`, {
        headers: { 'Accept': 'application/json' }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const emp = data.employee;
            const cfg = data.category_cfg || { icon: 'fa-solid fa-user', bg_color: '#eff6ff', text_color: '#1d4ed8', border_color: '#bfdbfe' };
            const earned = Number(data.total_earned || 0).toLocaleString('en-US', {minimumFractionDigits: 2});
            const paid = Number(data.total_paid || 0).toLocaleString('en-US', {minimumFractionDigits: 2});
            const due = Number(data.balance_due || 0).toLocaleString('en-US', {minimumFractionDigits: 2});
            const statusClass = emp.status === 'active' ? 'text-success bg-success-subtle' : (emp.status === 'on_leave' ? 'text-warning bg-warning-subtle' : 'text-secondary bg-light');

            bodyEl.innerHTML = `
                <div class="d-flex align-items-center gap-3 mb-3 p-3 rounded-3" style="background-color: ${cfg.bg_color}; border: 1px solid ${cfg.border_color};">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" 
                         style="width: 50px; height: 50px; background: #ffffff; color: ${cfg.text_color}; font-size: 20px; border: 1px solid ${cfg.border_color};">
                        <i class="${cfg.icon}"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="fw-bold mb-0 text-dark">${emp.name}</h6>
                        <span class="small fw-semibold text-muted">${emp.designation}</span>
                        <div><span class="badge rounded-pill px-2 py-0.5 mt-1 small font-monospace ${statusClass}">${emp.status.toUpperCase()}</span></div>
                    </div>
                </div>

                <div class="row g-2 mb-3 small">
                    <div class="col-6">
                        <div class="p-2.5 bg-light rounded-3 border">
                            <span class="text-muted d-block" style="font-size: 11px;">Department</span>
                            <strong class="text-dark">${emp.department}</strong>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-2.5 bg-light rounded-3 border">
                            <span class="text-muted d-block" style="font-size: 11px;">Employment Nature</span>
                            <strong class="text-dark">${(emp.employment_type || 'monthly').toUpperCase()}</strong>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-2.5 bg-light rounded-3 border">
                            <span class="text-muted d-block" style="font-size: 11px;">Salary / Rate Scale</span>
                            <strong class="text-primary font-monospace">${data.formatted_rate}</strong>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-2.5 bg-light rounded-3 border">
                            <span class="text-muted d-block" style="font-size: 11px;">Payment Schedule</span>
                            <strong class="text-dark">${(emp.payment_schedule || 'monthly').toUpperCase()}</strong>
                        </div>
                    </div>
                </div>

                <div class="p-3 rounded-3 border mb-3" style="background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);">
                    <div class="row g-2 text-center small font-monospace">
                        <div class="col-4">
                            <span class="text-muted d-block" style="font-size: 10.5px;">Total Earned</span>
                            <strong class="text-dark">৳${earned}</strong>
                        </div>
                        <div class="col-4 border-start">
                            <span class="text-muted d-block" style="font-size: 10.5px;">Total Paid</span>
                            <strong class="text-success">৳${paid}</strong>
                        </div>
                        <div class="col-4 border-start">
                            <span class="text-muted d-block" style="font-size: 10.5px;">Balance Due</span>
                            <strong class="text-danger">৳${due}</strong>
                        </div>
                    </div>
                </div>

                <div class="small">
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted"><i class="fa-solid fa-phone me-1.5 text-success"></i>Phone:</span>
                        <span class="fw-semibold font-monospace text-dark">${emp.phone || '—'}</span>
                    </div>
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted"><i class="fa-solid fa-envelope me-1.5 text-primary"></i>Email:</span>
                        <span class="fw-semibold text-dark">${emp.email || '—'}</span>
                    </div>
                    <div class="d-flex justify-content-between py-1">
                        <span class="text-muted"><i class="fa-solid fa-calendar-check me-1.5 text-info"></i>Joining Date:</span>
                        <span class="fw-semibold text-dark">${emp.joining_date ? new Date(emp.joining_date).toLocaleDateString('en-GB') : '—'}</span>
                    </div>
                </div>
            `;
        }
    })
    .catch(err => {
        bodyEl.innerHTML = `<div class="alert alert-danger mb-0">Failed to load staff details.</div>`;
    });
}
</script>
@endpush
@endsection
