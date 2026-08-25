@extends('layouts.app')

@section('title', 'নতুন পাসওয়ার্ড সেট করুন — ideaabd')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-5 col-md-7 col-sm-10">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header border-0 py-4 text-center" style="background: linear-gradient(135deg, #003366 0%, #0066cc 100%);">
                    <div class="rounded-circle bg-white bg-opacity-20 d-inline-flex align-items-center justify-content-center mb-2 shadow-xs" style="width: 55px; height: 55px;">
                        <i class="fa-solid fa-lock-open text-white fs-4"></i>
                    </div>
                    <h4 class="fw-bold text-white mb-1">নতুন পাসওয়ার্ড সেট করুন</h4>
                    <p class="text-white text-opacity-75 small mb-0">নিচের ফর্মে আপনার পছন্দমতো নতুন পাসওয়ার্ড দিন</p>
                </div>
                
                <div class="card-body p-4 p-md-4.5">
                    <!-- Live Countdown Timer Alert -->
                    <div class="alert alert-warning border-0 bg-warning bg-opacity-10 rounded-3 p-2.5 mb-3 text-center d-flex align-items-center justify-content-center gap-2">
                        <i class="fa-solid fa-stopwatch text-warning fs-5"></i>
                        <span class="small fw-semibold text-dark">
                            লিংকের মেয়াদ অবশিষ্ট আছে: <span id="timerBadge" class="badge bg-warning text-dark px-2.5 py-1.5 fs-6 fw-bold">03:00</span>
                        </span>
                    </div>

                    @if(isset($errors) && $errors->any())
                        <div class="alert alert-danger rounded-3 small mb-3 p-3 border-0 bg-danger bg-opacity-10 text-danger">
                            <div class="fw-bold mb-1"><i class="fa-solid fa-triangle-exclamation me-1"></i> অনুগ্রহ করে নিচের ত্রুটিগুলো সংশোধন করুন:</div>
                            <ul class="mb-0 ps-3">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('password.update') }}" id="resetPasswordForm">
                        @csrf
                        <input type="hidden" name="token" value="{{ $token }}">

                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold text-dark">ইমেইল অ্যাড্রেস</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fa-solid fa-envelope text-muted"></i></span>
                                <input type="email" 
                                       id="email" 
                                       name="email" 
                                       class="form-control bg-light @error('email') is-invalid @enderror" 
                                       value="{{ old('email', $email) }}" 
                                       readonly 
                                       required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label fw-semibold text-dark">
                                নতুন পাসওয়ার্ড <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fa-solid fa-lock text-primary"></i></span>
                                <input type="password" 
                                       id="password" 
                                       name="password" 
                                       class="form-control @error('password') is-invalid @enderror" 
                                       placeholder="নতুন পাসওয়ার্ড (ন্যূনতম ৬ অক্ষর)" 
                                       required 
                                       autofocus 
                                       autocomplete="new-password"
                                       oninput="checkPasswordStrength(this.value, 'resetPwdStrengthBar', 'resetPwdStrengthText')">
                                <button type="button" class="btn btn-outline-secondary" onclick="togglePasswordVisibility('password', this)" title="পাসওয়ার্ড দেখুন">
                                    <i class="fa-regular fa-eye"></i>
                                </button>
                            </div>
                            <div class="d-flex align-items-center justify-content-between my-1" style="font-size: 11.5px;">
                                <span class="text-muted">পাসওয়ার্ডের শক্তি: <strong id="resetPwdStrengthText" class="text-secondary">টাইপ করুন...</strong></span>
                            </div>
                            <div class="progress mb-2" style="height: 4px;">
                                <div id="resetPwdStrengthBar" class="progress-bar bg-danger" role="progressbar" style="width: 0%; transition: width 0.3s ease;"></div>
                            </div>
                            @error('password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label fw-semibold text-dark">
                                নতুন পাসওয়ার্ড নিশ্চিত করুন <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fa-solid fa-shield-check text-success"></i></span>
                                <input type="password" 
                                       id="password_confirmation" 
                                       name="password_confirmation" 
                                       class="form-control" 
                                       placeholder="পুনরায় নতুন পাসওয়ার্ড লিখুন" 
                                       required 
                                       autocomplete="new-password">
                                <button type="button" class="btn btn-outline-secondary" onclick="togglePasswordVisibility('password_confirmation', this)" title="পাসওয়ার্ড দেখুন">
                                    <i class="fa-regular fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" id="submitBtn" class="btn btn-success w-100 py-2.5 rounded-pill fw-bold shadow-sm mb-3">
                            <i class="fa-solid fa-check-double me-1.5"></i> পাসওয়ার্ড সংরক্ষণ করুন
                        </button>

                        <div class="text-center">
                            <a href="{{ route('password.request') }}" class="text-decoration-none small text-muted hover-primary">
                                <i class="fa-solid fa-arrow-rotate-left me-1"></i> নতুন লিংক রিকোয়েস্ট করুন
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Password visibility toggle
    function togglePasswordVisibility(inputId, btn) {
        const input = document.getElementById(inputId);
        const icon = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }

    function checkPasswordStrength(password, barId, textId) {
        const bar = document.getElementById(barId);
        const text = document.getElementById(textId);
        if (!bar || !text) return;

        if (!password) {
            bar.style.width = '0%';
            bar.className = 'progress-bar bg-danger';
            text.textContent = 'টাইপ করুন...';
            text.className = 'text-secondary';
            return;
        }

        let score = 0;
        if (password.length >= 6) score += 25;
        if (password.length >= 8) score += 25;
        if (/[A-Z]/.test(password) && /[a-z]/.test(password)) score += 25;
        if (/[0-9]/.test(password)) score += 15;
        if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) score += 10;

        if (score < 40) {
            bar.style.width = '30%';
            bar.className = 'progress-bar bg-danger';
            text.textContent = 'দুর্বল (Weak)';
            text.className = 'text-danger fw-bold';
        } else if (score < 75) {
            bar.style.width = '65%';
            bar.className = 'progress-bar bg-warning';
            text.textContent = 'মাঝারি (Medium)';
            text.className = 'text-warning fw-bold';
        } else {
            bar.style.width = '100%';
            bar.className = 'progress-bar bg-success';
            text.textContent = 'খুব শক্তিশালী (Strong)';
            text.className = 'text-success fw-bold';
        }
    }

    // 3-Minute Live Countdown Timer
    let secondsLeft = {{ (int) ($remainingSeconds ?? 180) }};
    const timerBadge = document.getElementById('timerBadge');
    const submitBtn = document.getElementById('submitBtn');

    function updateTimer() {
        if (secondsLeft <= 0) {
            timerBadge.textContent = '00:00 (মেয়াদ শেষ)';
            timerBadge.classList.remove('bg-warning', 'text-dark');
            timerBadge.classList.add('bg-danger', 'text-white');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fa-solid fa-clock-rotate-left me-1"></i> লিংকের মেয়াদ শেষ হয়েছে';
            
            setTimeout(() => {
                alert('পাসওয়ার্ড রিসেট লিংকের ৩ মিনিট মেয়াদ শেষ হয়ে গেছে। অনুগ্রহ করে নতুন লিংকের জন্য রিকোয়েস্ট করুন।');
                window.location.href = "{{ route('password.request') }}";
            }, 500);
            return;
        }

        const mins = Math.floor(secondsLeft / 60);
        const secs = secondsLeft % 60;
        timerBadge.textContent = `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
        
        if (secondsLeft <= 30) {
            timerBadge.classList.remove('bg-warning', 'text-dark');
            timerBadge.classList.add('bg-danger', 'text-white');
        }

        secondsLeft--;
    }

    updateTimer();
    const interval = setInterval(updateTimer, 1000);
</script>
@endsection
