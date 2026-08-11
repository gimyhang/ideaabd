@extends('layouts.admin')

@section('title', 'নতুন সাব-অ্যাডমিন')
@section('heading', 'নতুন সাব-অ্যাডমিন / সেলার')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.sub-admins.index') }}" class="text-decoration-none">সাব-অ্যাডমিন</a></li>
    <li class="breadcrumb-item active" aria-current="page">নতুন</li>
@endsection

@section('content')

<div class="row justify-content-center">
    <div class="col-xl-7 col-lg-9">
        <form method="POST" action="{{ route('admin.sub-admins.store') }}" class="adm-card">
            @csrf

            <div class="adm-card__head">
                <h6><i class="fas fa-user-plus me-2" style="color:#0066cc"></i> অ্যাকাউন্টের তথ্য</h6>
            </div>

            <div class="adm-card__body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="name" class="form-label small fw-semibold">নাম <span class="text-danger">*</span></label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required
                               class="form-control @error('name') is-invalid @enderror" placeholder="পূর্ণ নাম">
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="role" class="form-label small fw-semibold">ভূমিকা <span class="text-danger">*</span></label>
                        <select id="role" name="role" required class="form-select @error('role') is-invalid @enderror">
                            <option value="sub_admin" @selected(old('role') === 'sub_admin')>সাব-অ্যাডমিন</option>
                            <option value="seller" @selected(old('role') === 'seller')>সেলার</option>
                        </select>
                        @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="email" class="form-label small fw-semibold">ইমেইল <span class="text-danger">*</span></label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required
                               class="form-control @error('email') is-invalid @enderror" placeholder="name@example.com">
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="phone" class="form-label small fw-semibold">ফোন</label>
                        <input type="text" id="phone" name="phone" value="{{ old('phone') }}"
                               class="form-control @error('phone') is-invalid @enderror" placeholder="01XXXXXXXXX">
                        @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="password" class="form-label small fw-semibold">পাসওয়ার্ড <span class="text-danger">*</span></label>
                        <input type="password" id="password" name="password" required minlength="8"
                               class="form-control @error('password') is-invalid @enderror" autocomplete="new-password">
                        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <div class="form-text">অন্তত ৮ অক্ষর</div>
                    </div>

                    <div class="col-md-6">
                        <label for="password_confirmation" class="form-label small fw-semibold">পাসওয়ার্ড নিশ্চিত করুন <span class="text-danger">*</span></label>
                        <input type="password" id="password_confirmation" name="password_confirmation" required minlength="8"
                               class="form-control" autocomplete="new-password">
                    </div>
                </div>
            </div>

            <div class="adm-card__foot d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="fas fa-check me-1"></i> অ্যাকাউন্ট তৈরি করুন</button>
                <a href="{{ route('admin.sub-admins.index') }}" class="btn btn-outline-secondary">বাতিল</a>
            </div>
        </form>
    </div>
</div>

@endsection
