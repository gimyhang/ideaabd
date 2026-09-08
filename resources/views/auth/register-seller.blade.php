@extends('layouts.app')
@section('title', 'Seller Registration — IDEA Publication')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-7 col-md-9">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
                <div class="card-header border-0 py-4 px-4 text-white" style="background: linear-gradient(135deg, #0f172a 0%, #0369a1 50%, #0284c7 100%);">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-white text-primary d-flex align-items-center justify-content-center shadow-xs flex-shrink-0" style="width:52px;height:52px;">
                            <i class="fas fa-store fs-4"></i>
                        </div>
                        <div>
                            <h4 class="fw-bold mb-0 text-white fs-4">Seller & Vendor Registration</h4>
                            <small class="text-white-50">Join as a bookstore partner or dealer on IDEA Platform</small>
                        </div>
                    </div>
                </div>
                
                <div class="card-body p-4 p-md-4.5">
                    @if($errors->any())
                        <div class="alert alert-danger rounded-3"><ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
                    @endif

                    <form method="POST" action="{{ route('register.submit', 'seller') }}" id="sellerRegisterForm" onsubmit="document.getElementById('sellerSubmitBtn').disabled = true; document.getElementById('sellerSubmitSpinner').classList.remove('d-none'); document.getElementById('sellerSubmitText').textContent = 'Submitting...';">
                        @csrf

                        {{-- Shop / Business Information --}}
                        <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                            <i class="fas fa-shop text-primary"></i>
                            <span>Business Information</span>
                        </h6>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-dark">Shop / Store Name <span class="text-danger">*</span></label>
                            <input type="text" name="shop_name" class="form-control rounded-3 @error('shop_name') is-invalid @enderror"
                                   value="{{ old('shop_name') }}" placeholder="Bookstore or business name" required>
                            @error('shop_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-dark">Store Address <span class="text-danger">*</span></label>
                            <textarea name="address" rows="2" class="form-control rounded-3 @error('address') is-invalid @enderror" placeholder="Full physical address of shop" required>{{ old('address') }}</textarea>
                            @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-sm-6">
                                <label class="form-label small fw-bold text-dark">Trade License <span class="text-muted small">(Optional)</span></label>
                                <input type="text" name="trade_license" class="form-control rounded-3" value="{{ old('trade_license') }}" placeholder="Trade license number">
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label small fw-bold text-dark">National ID (NID) <span class="text-muted small">(Optional)</span></label>
                                <input type="text" name="nid" class="form-control rounded-3" value="{{ old('nid') }}" placeholder="NID number">
                            </div>
                        </div>

                        <hr class="my-3.5">

                        {{-- Contact Person & Account Information --}}
                        <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                            <i class="fas fa-user-shield text-primary"></i>
                            <span>Account Details</span>
                        </h6>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-dark">Contact Person <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control rounded-3 @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}" required placeholder="Contact person full name">
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-sm-6">
                                <label class="form-label small fw-bold text-dark">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control rounded-3 @error('email') is-invalid @enderror"
                                       value="{{ old('email') }}" required placeholder="seller@example.com">
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label small fw-bold text-dark">Mobile No <span class="text-danger">*</span></label>
                                <input type="tel" name="phone" class="form-control rounded-3 @error('phone') is-invalid @enderror"
                                       value="{{ old('phone') }}" required placeholder="01XXXXXXXXX">
                                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-sm-6">
                                <label class="form-label small fw-bold text-dark">Password <span class="text-danger">*</span></label>
                                <input type="password" name="password" class="form-control rounded-3 @error('password') is-invalid @enderror" required minlength="8" maxlength="25" placeholder="8-25 characters & symbol">
                                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label small fw-bold text-dark">Confirm Password <span class="text-danger">*</span></label>
                                <input type="password" name="password_confirmation" class="form-control rounded-3" required minlength="8" maxlength="25" placeholder="Retype password">
                            </div>
                        </div>

                        <div class="alert alert-info bg-info-subtle border-info-subtle text-info-emphasis small py-2.5 px-3 rounded-3 d-flex align-items-center gap-2 mb-3">
                            <i class="fas fa-circle-info fs-5 text-info"></i>
                            <span class="fw-semibold">
                                Registration will be verified and activated within 24 hours.
                            </span>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-3 fw-bold rounded-pill shadow-xs d-flex align-items-center justify-content-center gap-2" id="sellerSubmitBtn" style="font-size: 15px;">
                            <span class="spinner-border spinner-border-sm d-none" id="sellerSubmitSpinner" role="status"></span>
                            <i class="fas fa-paper-plane" id="sellerSubmitIcon"></i>
                            <span id="sellerSubmitText">Submit Registration</span>
                        </button>
                        
                        <p class="text-center mt-3.5 mb-0">
                            <a href="{{ route('register.choose') }}" class="text-muted small text-decoration-none">
                                <i class="fas fa-arrow-left me-1"></i> Other Accounts (Buyer / Author / Publisher)
                            </a>
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

