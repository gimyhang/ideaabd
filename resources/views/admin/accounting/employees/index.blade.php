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
                    <a href="{{ route('admin.accounting.employees.index', array_merge(request()->except('employment_type', 'page'))) }}" 
                       class="btn btn-sm rounded-pill px-3 py-1.5 fw-semibold {{ empty($employmentType) ? 'btn-dark text-white' : 'btn-light border text-dark' }}">
                        🌐 All Staff ({{ $totalEmployees }})
                    </a>
                    <a href="{{ route('admin.accounting.employees.index', array_merge(request()->except('page'), ['employment_type' => 'monthly'])) }}" 
                       class="btn btn-sm rounded-pill px-3 py-1.5 fw-semibold {{ $employmentType === 'monthly' ? 'btn-primary text-white' : 'btn-light border text-dark' }}">
                        💼 Monthly Fixed
                    </a>
                    <a href="{{ route('admin.accounting.employees.index', array_merge(request()->except('page'), ['employment_type' => 'contract_piece'])) }}" 
                       class="btn btn-sm rounded-pill px-3 py-1.5 fw-semibold {{ $employmentType === 'contract_piece' ? 'btn-purple text-white' : 'btn-light border text-dark' }}" style="{{ $employmentType === 'contract_piece' ? 'background-color: #7e22ce;' : '' }}">
                        📚 Piece-Rate / Binders ({{ $pieceRateCount }})
                    </a>
                    <a href="{{ route('admin.accounting.employees.index', array_merge(request()->except('page'), ['employment_type' => 'daily'])) }}" 
                       class="btn btn-sm rounded-pill px-3 py-1.5 fw-semibold {{ $employmentType === 'daily' ? 'btn-warning text-dark' : 'btn-light border text-dark' }}">
                        ⏱️ Daily Wage ({{ $dailyWageCount }})
                    </a>
                    <a href="{{ route('admin.accounting.employees.index', array_merge(request()->except('page'), ['employment_type' => 'weekly'])) }}" 
                       class="btn btn-sm rounded-pill px-3 py-1.5 fw-semibold {{ $employmentType === 'weekly' ? 'btn-info text-white' : 'btn-light border text-dark' }}">
                        📅 Weekly Wage
                    </a>
                    <a href="{{ route('admin.accounting.employees.index', array_merge(request()->except('page'), ['employment_type' => 'contract_project'])) }}" 
                       class="btn btn-sm rounded-pill px-3 py-1.5 fw-semibold {{ $employmentType === 'contract_project' ? 'btn-secondary text-white' : 'btn-light border text-dark' }}">
                        🎯 Project Contract
                    </a>
                </div>
            </div>

            <form action="{{ route('admin.accounting.employees.index') }}" method="GET" class="row g-2 align-items-center">
                @if($employmentType)
                    <input type="hidden" name="employment_type" value="{{ $employmentType }}">
                @endif
                <div class="col-md-5">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-search text-muted"></i></span>
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
                            @endphp
                            <tr>
                                <td class="ps-3.5">
                                    <div class="d-flex align-items-center gap-2.5">
                                        <div class="rounded-circle fw-bold d-flex align-items-center justify-content-center flex-shrink-0" 
                                             style="width: 42px; height: 42px; background-color: {{ $empType === 'contract_piece' ? '#f3e8ff' : ($empType === 'daily' ? '#fef3c7' : '#e0f2fe') }}; color: {{ $empType === 'contract_piece' ? '#7e22ce' : ($empType === 'daily' ? '#b45309' : '#0369a1') }}; font-size: 16px;">
                                            {{ mb_substr($emp->name, 0, 1) }}
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
                                    <div class="small fw-semibold text-dark">{{ $emp->department }}</div>
                                    <span class="text-muted" style="font-size: 11px;">
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
                                    <div><i class="fa-solid fa-phone me-1 text-primary"></i>{{ $emp->phone ?: 'N/A' }}</div>
                                    @if($emp->email)
                                        <div style="font-size: 11px;"><i class="fa-solid fa-envelope me-1 text-info"></i>{{ $emp->email }}</div>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($emp->status === 'active')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 small">Active</span>
                                    @elseif($emp->status === 'inactive')
                                        <span class="badge bg-secondary-subtle text-secondary border rounded-pill px-2.5 py-1 small">Inactive</span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning border rounded-pill px-2.5 py-1 small">On Leave</span>
                                    @endif
                                </td>
                                <td class="text-end pe-3.5">
                                    <div class="d-inline-flex align-items-center gap-1.5">
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
                
                {{-- Fast 1-Click Role Presets for Printing Press & Publishing --}}
                <div class="mb-3 p-3 bg-light rounded-3 border">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="small fw-bold text-dark">
                            <i class="fa-solid fa-bolt text-warning me-1"></i> Quick Press Role Presets (1-Click Fill):
                        </span>
                    </div>
                    <select class="form-select form-select-sm rounded-pill border-primary fw-semibold" id="addRolePresetSelect" onchange="applyEmployeePreset('add', this.value)">
                        <option value="">-- Select Press / Publishing Role Preset --</option>
                        <option value='{"name":"","desig":"Master Book Binder","dept":"Press & Book Binding","type":"contract_piece","skill":"Master Book Binder","rate_type":"per_book","unit":"Book","rate":4.50,"schedule":"per_job"}'>📚 Master Book Binder (Piece-rate: 4.50 / Book Binding)</option>
                        <option value='{"name":"","desig":"Assistant Binder & Pasting Artisan","dept":"Press & Book Binding","type":"contract_piece","skill":"Assistant Binder & Pasting Artisan","rate_type":"per_forma","unit":"Forma","rate":0.60,"schedule":"weekly"}'>📖 Pasting Artisan (Piece-rate: 0.60 / Forma)</option>
                        <option value='{"name":"","desig":"Paper Cutting Master","dept":"Press & Book Binding","type":"daily","skill":"Paper Cutting Master","rate_type":"daily","unit":"Day","rate":800,"schedule":"daily"}'>✂️ Paper Cutting Master (Daily Wage: 800 / Day)</option>
                        <option value='{"name":"","desig":"Offset Press Machine Operator","dept":"Press & Book Binding","type":"monthly","skill":"Offset Press Machine Operator","rate_type":"monthly","unit":"Month","rate":24000,"schedule":"monthly"}'>🖨️ Offset Press Operator (Monthly Salary: 24000 / Month)</option>
                        <option value='{"name":"","desig":"Book Cover & Graphics Designer","dept":"Design & Pre-Press","type":"contract_piece","skill":"Book Cover & Graphics Designer","rate_type":"per_book","unit":"Cover","rate":1500,"schedule":"per_job"}'>🎨 Book Cover Designer (Piece-rate: 1500 / Cover)</option>
                        <option value='{"name":"","desig":"Typesetter / Page Compositor","dept":"Design & Pre-Press","type":"contract_piece","skill":"Typesetter / Page Compositor","rate_type":"per_page","unit":"Page","rate":15,"schedule":"weekly"}'>⌨️ Typesetter (Piece-rate: 15 / Page)</option>
                        <option value='{"name":"","desig":"Lamination & Spot UV Specialist","dept":"Press & Book Binding","type":"contract_piece","skill":"Lamination & Spot UV Specialist","rate_type":"per_thousand","unit":"1000 Sheets","rate":350,"schedule":"per_job"}'>✨ Lamination Specialist (Piece-rate: 350 / 1000 Sheets)</option>
                        <option value='{"name":"","desig":"Marketing & Sales Executive","dept":"Marketing & Sales","type":"monthly","skill":"Marketing & Sales Representative","rate_type":"monthly","unit":"Month","rate":18000,"schedule":"monthly"}'>📢 Marketing Executive (Monthly: 18000 / Month)</option>
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
</script>
@endpush
@endsection
