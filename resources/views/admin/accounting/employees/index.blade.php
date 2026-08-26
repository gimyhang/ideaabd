@extends('layouts.admin')

@section('title', 'কর্মচারী ব্যবস্থাপনা ও বেতন তালিকা (Employees & Staff) — আইডিয়া প্রকাশন')

@section('content')
<div class="container-fluid px-3 px-md-4 py-3">

    <!-- Top Action & Metric Header -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
        <div class="card-body p-3 p-md-4">
            <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1 small fw-bold">
                            <i class="fa-solid fa-users me-1"></i> হিউম্যান রিসোর্স ও বেতন
                        </span>
                    </div>
                    <h4 class="fw-bold text-dark mb-0">কর্মচারী ব্যবস্থাপনা ও প্রোফাইল</h4>
                    <p class="text-muted small mb-0">সকল কর্মকর্তা-কর্মচারীর তথ্য, বিভাগ, মাসিক মূল বেতন ও কর্মসংস্থান রেকর্ড।</p>
                </div>

                <div class="d-flex align-items-center gap-2 flex-wrap ms-md-auto">
                    <a href="{{ route('admin.accounting.salary.index') }}" class="btn btn-outline-primary rounded-pill px-3 py-2 fw-semibold shadow-2xs">
                        <i class="fa-solid fa-money-check-dollar me-1"></i> মাসিক বেতন বিতরণ
                    </a>
                    <button type="button" class="btn btn-primary rounded-pill px-3.5 py-2 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">
                        <i class="fa-solid fa-user-plus me-1.5"></i> নতুন কর্মচারী যুক্ত করুন
                    </button>
                </div>
            </div>

            <hr class="my-3 opacity-25">

            <!-- Summary KPI Badges -->
            <div class="row g-3">
                <div class="col-sm-4">
                    <div class="p-3 bg-light rounded-3 border d-flex align-items-center justify-content-between">
                        <div>
                            <span class="small text-muted fw-semibold">মোট কর্মচারী</span>
                            <h5 class="fw-bold text-dark mb-0">{{ $totalEmployees }} জন</h5>
                        </div>
                        <span class="badge bg-white text-dark border p-2 rounded-circle"><i class="fa-solid fa-users"></i></span>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="p-3 bg-light rounded-3 border d-flex align-items-center justify-content-between">
                        <div>
                            <span class="small text-muted fw-semibold">সক্রিয় কর্মচারী</span>
                            <h5 class="fw-bold text-success mb-0">{{ $activeEmployees }} জন</h5>
                        </div>
                        <span class="badge bg-white text-success border p-2 rounded-circle"><i class="fa-solid fa-user-check"></i></span>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="p-3 bg-light rounded-3 border d-flex align-items-center justify-content-between">
                        <div>
                            <span class="small text-muted fw-semibold">মোট মাসিক পে-রোল (বেতন বাজেট)</span>
                            <h5 class="fw-bold text-primary mb-0">৳{{ number_format($monthlyPayroll, 2) }}</h5>
                        </div>
                        <span class="badge bg-white text-primary border p-2 rounded-circle"><i class="fa-solid fa-hand-holding-dollar"></i></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
        <div class="card-body p-3">
            <form action="{{ route('admin.accounting.employees.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-5">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-search text-muted"></i></span>
                        <input type="text" name="search" value="{{ $search }}" class="form-control rounded-end-3" placeholder="নাম, পদবি বা ফোন নম্বর দিয়ে খুঁজুন...">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="department" class="form-select form-select-sm rounded-3">
                        <option value="">সকল বিভাগ</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept }}" @selected($department === $dept)>{{ $dept }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select form-select-sm rounded-3">
                        <option value="">সকল স্ট্যাটাস</option>
                        <option value="active" @selected($status === 'active')>সক্রিয় (Active)</option>
                        <option value="inactive" @selected($status === 'inactive')>নিষ্ক্রিয় (Inactive)</option>
                        <option value="on_leave" @selected($status === 'on_leave')>ছুটিতে (On Leave)</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3 w-100 fw-semibold">ফিল্টার</button>
                    @if($search || $department || $status)
                        <a href="{{ route('admin.accounting.employees.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5" title="রিসেট"><i class="fa-solid fa-xmark"></i></a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Employees List Table -->
    <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light table-light small text-muted">
                        <tr>
                            <th class="ps-3.5">কর্মচারীর নাম ও পদবি</th>
                            <th>বিভাগ</th>
                            <th>যোগাযোগ (ফোন/ইমেইল)</th>
                            <th class="text-end">মূল মাসিক বেতন</th>
                            <th class="text-center">স্ট্যাটাস</th>
                            <th class="text-end pe-3.5" style="width: 140px;">অ্যাকশন</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($employees as $emp)
                            <tr>
                                <td class="ps-3.5">
                                    <div class="d-flex align-items-center gap-2.5">
                                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary fw-bold d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px;">
                                            {{ mb_substr($emp->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <h6 class="fw-bold text-dark mb-0 font-monospace-title">{{ $emp->name }}</h6>
                                            <span class="small text-muted">{{ $emp->designation }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border rounded-pill px-2.5 py-1 small">
                                        {{ $emp->department }}
                                    </span>
                                </td>
                                <td class="small text-muted">
                                    <div><i class="fa-solid fa-phone me-1 text-primary"></i>{{ $emp->phone ?: 'প্রযোজ্য নয়' }}</div>
                                    @if($emp->email)
                                        <div><i class="fa-solid fa-envelope me-1 text-info"></i>{{ $emp->email }}</div>
                                    @endif
                                </td>
                                <td class="text-end fw-bold text-dark font-monospace">
                                    ৳{{ number_format($emp->basic_salary, 2) }}
                                </td>
                                <td class="text-center">
                                    @if($emp->status === 'active')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 small">সক্রিয়</span>
                                    @elseif($emp->status === 'inactive')
                                        <span class="badge bg-secondary-subtle text-secondary border rounded-pill px-2.5 py-1 small">নিষ্ক্রিয়</span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning border rounded-pill px-2.5 py-1 small">ছুটিতে</span>
                                    @endif
                                </td>
                                <td class="text-end pe-3.5">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary rounded-pill px-2.5 py-1" type="button" data-bs-toggle="dropdown">
                                            <i class="fa-solid fa-ellipsis-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3">
                                            <li>
                                                <button type="button" class="dropdown-item small" onclick="openEditEmployeeModal({{ $emp->toJson() }})">
                                                    <i class="fa-solid fa-pen-to-square text-primary me-2"></i> তথ্য এডিট করুন
                                                </button>
                                            </li>
                                            <li>
                                                <a href="{{ route('admin.accounting.salary.index', ['employee_id' => $emp->id]) }}" class="dropdown-item small">
                                                    <i class="fa-solid fa-receipt text-success me-2"></i> বেতন রেকর্ড
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider my-1"></li>
                                            <li>
                                                <form action="{{ route('admin.accounting.employees.destroy', $emp->id) }}" method="POST" onsubmit="return confirm('আপনি কি নিশ্চিত যে এই কর্মচারীর তথ্য মুছে ফেলতে চান?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item small text-danger">
                                                        <i class="fa-solid fa-trash me-2"></i> কর্মচারী মুছুন
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-users text-muted opacity-50 fs-2 mb-2"></i>
                                    <p class="small mb-0">কোনো কর্মচারী পাওয়া যায়নি। নতুন কর্মচারী যোগ করতে উপরের বাটনে চাপুন।</p>
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

<!-- Modal: Add Employee -->
<div class="modal fade" id="addEmployeeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form action="{{ route('admin.accounting.employees.store') }}" method="POST" class="modal-content rounded-4 border-0 shadow-lg">
            @csrf
            <div class="modal-header bg-dark text-white border-0 py-3">
                <h5 class="modal-title fw-bold fs-6 d-flex align-items-center gap-2">
                    <i class="fa-solid fa-user-plus text-primary"></i> নতুন কর্মচারী যুক্ত করুন
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-dark">কর্মচারীর নাম *</label>
                        <input type="text" name="name" class="form-control rounded-3" required placeholder="যেমন: মো. কামরুল হাসান">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-dark">পদবি (Designation) *</label>
                        <input type="text" name="designation" class="form-control rounded-3" required placeholder="যেমন: প্রিন্টিং ইনচার্জ / প্রুফরিডার">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-dark">বিভাগ (Department) *</label>
                        <select name="department" class="form-select rounded-3" required>
                            @foreach($departments as $dept)
                                <option value="{{ $dept }}">{{ $dept }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-dark">মাসিক মূল বেতন (৳) *</label>
                        <input type="number" step="0.01" name="basic_salary" class="form-control rounded-3" required placeholder="যেমন: 20000">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-muted">ফোন নম্বর</label>
                        <input type="text" name="phone" class="form-control rounded-3" placeholder="01XXXXXXXXX">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-muted">ইমেইল ঠিকানা</label>
                        <input type="email" name="email" class="form-control rounded-3" placeholder="email@example.com">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-muted">যোগদানের তারিখ</label>
                        <input type="date" name="joining_date" value="{{ date('Y-m-d') }}" class="form-control rounded-3">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-muted">স্ট্যাটাস</label>
                        <select name="status" class="form-select rounded-3">
                            <option value="active">সক্রিয় (Active)</option>
                            <option value="inactive">নিষ্ক্রিয় (Inactive)</option>
                            <option value="on_leave">ছুটিতে (On Leave)</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold text-muted">ঠিকানা</label>
                        <textarea name="address" class="form-control rounded-3" rows="2" placeholder="বর্তমান ও স্থায়ী ঠিকানা..."></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-top p-3">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">বাতিল</button>
                <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">সংরক্ষণ করুন</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Edit Employee -->
<div class="modal fade" id="editEmployeeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form id="editEmployeeForm" method="POST" class="modal-content rounded-4 border-0 shadow-lg">
            @csrf
            @method('PUT')
            <div class="modal-header bg-dark text-white border-0 py-3">
                <h5 class="modal-title fw-bold fs-6 d-flex align-items-center gap-2">
                    <i class="fa-solid fa-pen-to-square text-primary"></i> কর্মচারীর তথ্য আপডেট করুন
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-dark">কর্মচারীর নাম *</label>
                        <input type="text" name="name" id="edit_name" class="form-control rounded-3" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-dark">পদবি *</label>
                        <input type="text" name="designation" id="edit_designation" class="form-control rounded-3" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-dark">বিভাগ *</label>
                        <select name="department" id="edit_department" class="form-select rounded-3" required>
                            @foreach($departments as $dept)
                                <option value="{{ $dept }}">{{ $dept }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-dark">মাসিক মূল বেতন (৳) *</label>
                        <input type="number" step="0.01" name="basic_salary" id="edit_basic_salary" class="form-control rounded-3" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-muted">ফোন নম্বর</label>
                        <input type="text" name="phone" id="edit_phone" class="form-control rounded-3">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-muted">ইমেইল</label>
                        <input type="email" name="email" id="edit_email" class="form-control rounded-3">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-muted">যোগদানের তারিখ</label>
                        <input type="date" name="joining_date" id="edit_joining_date" class="form-control rounded-3">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-muted">স্ট্যাটাস</label>
                        <select name="status" id="edit_status" class="form-select rounded-3">
                            <option value="active">সক্রিয় (Active)</option>
                            <option value="inactive">নিষ্ক্রিয় (Inactive)</option>
                            <option value="on_leave">ছুটিতে (On Leave)</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold text-muted">ঠিকানা</label>
                        <textarea name="address" id="edit_address" class="form-control rounded-3" rows="2"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-top p-3">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">বাতিল</button>
                <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">আপডেট করুন</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openEditEmployeeModal(emp) {
    document.getElementById('edit_name').value = emp.name || '';
    document.getElementById('edit_designation').value = emp.designation || '';
    document.getElementById('edit_department').value = emp.department || '';
    document.getElementById('edit_basic_salary').value = emp.basic_salary || '';
    document.getElementById('edit_phone').value = emp.phone || '';
    document.getElementById('edit_email').value = emp.email || '';
    document.getElementById('edit_joining_date').value = emp.joining_date ? emp.joining_date.substring(0, 10) : '';
    document.getElementById('edit_status').value = emp.status || 'active';
    document.getElementById('edit_address').value = emp.address || '';

    document.getElementById('editEmployeeForm').action = `{{ url('/admin/accounting/employees') }}/${emp.id}`;

    const modal = new bootstrap.Modal(document.getElementById('editEmployeeModal'));
    modal.show();
}
</script>
@endpush
@endsection
