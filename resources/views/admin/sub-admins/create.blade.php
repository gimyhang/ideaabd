@extends('layouts.admin')

@section('title', 'New Sub-Admin')
@section('heading', 'Add New Sub-Admin / Staff')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.sub-admins.index') }}" class="text-decoration-none">Sub-Admins</a></li>
    <li class="breadcrumb-item active" aria-current="page">Create</li>
@endsection

@section('content')

<div class="row justify-content-center">
    <div class="col-xl-7 col-lg-9">
        <form method="POST" action="{{ route('admin.sub-admins.store') }}" class="adm-card bg-white rounded-4 shadow-sm border-0">
            @csrf

            <div class="adm-card__head p-3 border-bottom">
                <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-user-plus me-2 text-primary"></i> Account Information</h6>
            </div>

            <div class="adm-card__body p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="name" class="form-label small fw-semibold">Full Name <span class="text-danger">*</span></label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required
                               class="form-control rounded-3 @error('name') is-invalid @enderror" placeholder="Full name">
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="role" class="form-label small fw-semibold">Role <span class="text-danger">*</span></label>
                        <select id="role" name="role" required class="form-select rounded-3 @error('role') is-invalid @enderror">
                            <option value="sub_admin" @selected(old('role') === 'sub_admin')>Sub-Admin</option>
                            <option value="seller" @selected(old('role') === 'seller')>Seller</option>
                        </select>
                        @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="email" class="form-label small fw-semibold">Email Address <span class="text-danger">*</span></label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required
                               class="form-control rounded-3 @error('email') is-invalid @enderror" placeholder="name@example.com">
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="phone" class="form-label small fw-semibold">Phone Number</label>
                        <input type="text" id="phone" name="phone" value="{{ old('phone') }}"
                               class="form-control rounded-3 @error('phone') is-invalid @enderror" placeholder="01XXXXXXXXX">
                        @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="password" class="form-label small fw-semibold">Password <span class="text-danger">*</span></label>
                        <input type="password" id="password" name="password" required minlength="8"
                               class="form-control rounded-3 @error('password') is-invalid @enderror" autocomplete="new-password">
                        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <div class="form-text small">Minimum 8 characters</div>
                    </div>

                    <div class="col-md-6">
                        <label for="password_confirmation" class="form-label small fw-semibold">Confirm Password <span class="text-danger">*</span></label>
                        <input type="password" id="password_confirmation" name="password_confirmation" required minlength="8"
                               class="form-control rounded-3" autocomplete="new-password">
                    </div>
                </div>
            </div>

            <div class="adm-card__foot p-3 border-top bg-light rounded-bottom-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-xs"><i class="fas fa-check me-1"></i> Create Account</button>
                <a href="{{ route('admin.sub-admins.index') }}" class="btn btn-outline-secondary rounded-pill px-3">Cancel</a>
            </div>
        </form>
    </div>
</div>

@endsection
