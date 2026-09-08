@extends('layouts.app')
@section('title', 'Buyer Registration — IDEA Publication')
@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header border-0 py-4" style="background:linear-gradient(135deg,#FFF5E5,#FFE5D4)">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center shadow-xs" style="width:52px;height:52px;background:#fd7e14">
                            <i class="fa-solid fa-mobile-screen-button text-white fs-4"></i>
                        </div>
                        <div>
                            <h4 class="fw-bold mb-1 text-dark" style="color:#e8590c;">Create Buyer Account</h4>
                            <small class="text-muted">Instant access and order tracking</small>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    @if(isset($errors) && $errors->any())
                        <div class="alert alert-danger rounded-3"><ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
                    @endif

                    <form method="POST" action="{{ route('register.submit', 'buyer') }}">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control rounded-3 @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}" required placeholder="Your full name">
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Mobile No <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-phone text-success"></i></span>
                                <input type="tel" name="phone" class="form-control rounded-end-3 @error('phone') is-invalid @enderror"
                                       value="{{ old('phone') }}" required placeholder="01XXXXXXXXX">
                            </div>
                            @error('phone')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Email <span class="badge bg-light text-muted border">Optional</span></label>
                            <input type="email" name="email" class="form-control rounded-3 @error('email') is-invalid @enderror"
                                   value="{{ old('email') }}" placeholder="email@example.com">
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="row g-2">
                            <div class="col-sm-6 mb-3">
                                <label class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" name="password" id="buyerRegPassword" class="form-control rounded-start-3 @error('password') is-invalid @enderror" required minlength="8" maxlength="25" placeholder="Min 8 characters & symbol" oninput="checkPasswordStrength(this.value, 'buyerPwdStrengthBar', 'buyerPwdStrengthText')">
                                    <button type="button" class="btn btn-outline-secondary rounded-end-3" onclick="togglePasswordVisibility('buyerRegPassword', this)" title="Show/Hide Password">
                                        <i class="fa-regular fa-eye"></i>
                                    </button>
                                </div>
                                @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-sm-6 mb-3">
                                <label class="form-label fw-semibold">Confirm Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" name="password_confirmation" id="buyerRegPasswordConfirm" class="form-control rounded-start-3" required minlength="8" maxlength="25" placeholder="Retype password">
                                    <button type="button" class="btn btn-outline-secondary rounded-end-3" onclick="togglePasswordVisibility('buyerRegPasswordConfirm', this)" title="Show/Hide Password">
                                        <i class="fa-regular fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-12 mb-3">
                                <div class="d-flex align-items-center justify-content-between mb-1" style="font-size: 11.5px;">
                                    <span class="text-muted">Password Strength: <strong id="buyerPwdStrengthText" class="text-secondary">Type password...</strong></span>
                                </div>
                                <div class="progress" style="height: 4px;">
                                    <div id="buyerPwdStrengthBar" class="progress-bar bg-danger" role="progressbar" style="width: 0%; transition: width 0.3s ease;"></div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Delivery Address <span class="badge bg-light text-muted border">Optional</span></label>
                            <textarea name="address" rows="2" class="form-control rounded-3" placeholder="House/Road, Area, District">{{ old('address') }}</textarea>
                        </div>

                        <div class="alert alert-success small py-2.5 rounded-3 d-flex align-items-center gap-2">
                            <i class="fa-solid fa-circle-check text-success fs-5"></i>
                            <span>Instant ordering with Cash on Delivery and online payments.</span>
                        </div>

                        <button type="submit" class="btn w-100 py-3 fw-bold text-white rounded-pill shadow-xs d-flex align-items-center justify-content-center gap-2" id="buyerSubmitBtn" style="background:#fd7e14; font-size: 15px;">
                            <span class="spinner-border spinner-border-sm d-none" id="buyerSubmitSpinner" role="status"></span>
                            <i class="fa-solid fa-user-plus" id="buyerSubmitIcon"></i>
                            <span id="buyerSubmitText">Create Account</span>
                        </button>
                        
                        <p class="text-center mt-3.5 mb-0">
                            <a href="{{ route('register.choose') }}" class="text-muted small text-decoration-none">
                                <i class="fa-solid fa-arrow-left me-1"></i> Other Accounts (Author / Publisher / Seller)
                            </a>
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function togglePasswordVisibility(inputId, btn) {
    const input = document.getElementById(inputId);
    const icon = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        if (icon) {
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        }
    } else {
        input.type = 'password';
        if (icon) {
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }
}

function checkPasswordStrength(password, barId, textId) {
    const bar = document.getElementById(barId);
    const text = document.getElementById(textId);
    if (!bar || !text) return;

    if (!password) {
        bar.style.width = '0%';
        bar.className = 'progress-bar bg-danger';
        text.textContent = 'Type password...';
        text.className = 'text-secondary';
        return;
    }

    let score = 0;
    if (password.length >= 8) score += 30;
    if (/[A-Z]/.test(password) && /[a-z]/.test(password)) score += 25;
    if (/[0-9]/.test(password)) score += 25;
    if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) score += 20;

    if (score < 40) {
        bar.style.width = '30%';
        bar.className = 'progress-bar bg-danger';
        text.textContent = 'Weak';
        text.className = 'text-danger fw-bold';
    } else if (score < 75) {
        bar.style.width = '65%';
        bar.className = 'progress-bar bg-warning';
        text.textContent = 'Medium';
        text.className = 'text-warning fw-bold';
    } else {
        bar.style.width = '100%';
        bar.className = 'progress-bar bg-success';
        text.textContent = 'Strong';
        text.className = 'text-success fw-bold';
    }
}
</script>
@endsection

