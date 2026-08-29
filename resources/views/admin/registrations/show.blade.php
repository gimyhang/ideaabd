@extends('layouts.admin')

@section('title', $user->name . ' — Registration Details')
@section('heading', 'Registration Details')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.registrations.index') }}" class="text-decoration-none">Registrations</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $user->name }}</li>
@endsection

@section('actions')
    <div class="d-flex gap-2">
        <a href="{{ route('admin.registrations.edit', $user) }}" class="btn btn-outline-primary rounded-pill px-3">
            <i class="fas fa-edit me-1"></i> Edit Profile
        </a>
        <a href="{{ route('admin.registrations.index') }}" class="btn btn-outline-secondary rounded-pill px-3">
            <i class="fas fa-arrow-left me-1"></i> Back to Requests
        </a>
    </div>
@endsection

@section('content')
<div style="max-width:900px">
    <div class="row g-4">
        {{-- Profile card --}}
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-center p-4 rounded-4 bg-white">
                <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mx-auto mb-3" style="width:80px;height:80px">
                    <i class="fas fa-user fa-2x text-muted"></i>
                </div>
                <h5 class="fw-bold mb-1 text-dark">{{ $user->name }}</h5>
                <p class="text-muted small mb-2"><i class="fa-regular fa-envelope me-1"></i>{{ $user->email }}</p>
                <p class="text-muted small mb-3 font-monospace"><i class="fa-solid fa-phone me-1"></i>{{ $user->phone }}</p>

                @if($user->reg_status === 'pending')
                    <span class="badge bg-warning text-dark px-3 py-2 mb-3 rounded-pill">Pending</span>
                @elseif($user->reg_status === 'approved')
                    <span class="badge bg-success px-3 py-2 mb-3 rounded-pill">Approved</span>
                @else
                    <span class="badge bg-danger px-3 py-2 mb-3 rounded-pill">Rejected</span>
                @endif

                <small class="text-muted d-block">Registered On:<br>{{ $user->created_at->format('d M Y, h:i A') }}</small>
            </div>
        </div>

        {{-- Details card --}}
        <div class="col-md-8">
            <div class="card border-0 shadow-sm h-100 rounded-4 bg-white">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                        <h6 class="fw-bold text-muted text-uppercase mb-0" style="font-size:.75rem;letter-spacing:.05em">Submitted Application Data</h6>
                        <a href="{{ route('admin.registrations.edit', $user) }}" class="btn btn-sm btn-outline-secondary py-0.5 px-2.5 rounded-pill" style="font-size: 12px;">
                            <i class="fas fa-edit me-1"></i> Edit
                        </a>
                    </div>
                    
                    <table class="table table-sm table-borderless align-middle mb-0">
                        <tbody>
                            <tr>
                                <td class="fw-semibold text-muted" style="width:35%">Account Role</td>
                                <td class="fw-bold text-dark">{{ ucfirst($user->reg_type ?? $user->role) }}</td>
                            </tr>
                            <tr>
                                <td class="fw-semibold text-muted">Login Username</td>
                                <td class="font-monospace fw-bold text-primary">{{ $user->phone }}</td>
                            </tr>
                            <tr>
                                <td class="fw-semibold text-muted">Email Address</td>
                                <td>{{ $user->email }}</td>
                            </tr>
                            @if($user->reg_data)
                                @foreach($user->reg_data as $key => $value)
                                @if(!in_array($key, ['otp_code']))
                                <tr>
                                    <td class="fw-semibold text-muted">{{ str_replace('_', ' ', ucwords($key, '_')) }}</td>
                                    <td>
                                        @if($key === 'bio' && !empty($value))
                                            <div class="p-2.5 bg-light rounded-3 border border-light-subtle small mt-1" style="line-height: 1.6; white-space: pre-line;">
                                                {{ $value }}
                                            </div>
                                        @else
                                            {{ $value ?: '—' }}
                                        @endif
                                    </td>
                                </tr>
                                @endif
                                @endforeach
                            @endif
                        </tbody>
                    </table>

                    @if($user->rejection_reason)
                    <div class="alert alert-danger mt-3 mb-0 small rounded-3">
                        <strong>Rejection Reason:</strong> {{ $user->rejection_reason }}
                    </div>
                    @endif

                    <hr class="my-4">
                    <h6 class="fw-bold text-dark mb-3"><i class="fas fa-sliders text-primary me-1"></i> Administrative Actions & Approval Control</h6>
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        {{-- Approve Button --}}
                        <form method="POST" action="{{ route('admin.registrations.approve', $user) }}" class="flex-grow-1">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn {{ $user->reg_status === 'approved' ? 'btn-outline-success' : 'btn-success' }} w-100 rounded-pill fw-bold">
                                <i class="fas fa-check me-1"></i>{{ $user->reg_status === 'approved' ? 'Approved (Re-approve)' : 'Approve & Notify User' }}
                            </button>
                        </form>

                        {{-- Reject Button (Collapse trigger) --}}
                        <button type="button" class="btn {{ $user->reg_status === 'rejected' ? 'btn-outline-danger' : 'btn-danger' }} flex-grow-1 rounded-pill fw-semibold" data-bs-toggle="collapse" data-bs-target="#rejectForm">
                            <i class="fas fa-times me-1"></i>{{ $user->reg_status === 'rejected' ? 'Update Rejection Reason' : 'Decline / Reject Request' }}
                        </button>

                        {{-- Edit Button --}}
                        <a href="{{ route('admin.registrations.edit', $user) }}" class="btn btn-outline-secondary rounded-pill px-4 fw-semibold">
                            <i class="fas fa-edit me-1"></i> Edit
                        </a>

                        {{-- Delete Button --}}
                        <form method="POST" action="{{ route('admin.registrations.cancel', $user) }}" 
                              data-confirm="আপনি কি নিশ্চিত যে {{ addslashes($user->name) }} এর রেজিস্ট্রেশন ও অ্যাকাউন্ট স্থায়ীভাবে মুছে ফেলতে চান?" data-confirm-title="অ্যাকাউন্ট ডিলিট">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger rounded-pill px-4">
                                <i class="fas fa-trash me-1"></i> Delete Permanently
                            </button>
                        </form>
                    </div>

                    {{-- Collapsible Rejection Form --}}
                    <div id="rejectForm" class="collapse">
                        <form method="POST" action="{{ route('admin.registrations.reject', $user) }}" class="p-3 bg-light rounded-4 border border-danger-subtle">
                            @csrf @method('PATCH')
                            <label class="form-label fw-bold small text-danger">Rejection Reason <span class="text-danger">*</span></label>
                            <textarea name="reason" class="form-control mb-2 rounded-3" rows="3" required placeholder="State reason for declining registration (user will be notified)...">{{ $user->rejection_reason ?? '' }}</textarea>
                            <div class="d-flex justify-content-end gap-2">
                                <button type="button" class="btn btn-light btn-sm rounded-pill px-3" data-bs-toggle="collapse" data-bs-target="#rejectForm">Cancel</button>
                                <button type="submit" class="btn btn-danger btn-sm rounded-pill px-4 fw-bold">Confirm Decline</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
