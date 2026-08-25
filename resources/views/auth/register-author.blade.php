@extends('layouts.app')
@section('title', 'লেখক রেজিস্ট্রেশন - ideaabd')
@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-7 col-md-9">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header border-0 py-4" style="background:linear-gradient(135deg,#E5FFE5,#D4FFD4)">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center shadow-xs" style="width:52px;height:52px;background:#198754">
                            <i class="fas fa-pen-fancy text-white fs-4"></i>
                        </div>
                        <div>
                            <h4 class="fw-bold mb-1" style="color:#198754">লেখক রেজিস্ট্রেশন</h4>
                            <small class="text-muted">আপনার মোবাইল নম্বরটি ইউজারনেম হিসেবে ব্যবহার হবে</small>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    @if(isset($errors) && $errors->any())
                        <div class="alert alert-danger rounded-3"><ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
                    @endif

                    <form method="POST" action="{{ route('register.submit', 'author') }}" enctype="multipart/form-data">
                        @csrf
                        
                        {{-- ══ AUTHOR PHOTO / AVATAR UPLOAD ══ --}}
                        <div class="mb-4 p-3 bg-light rounded-4 border text-center">
                            <label class="form-label fw-bold text-dark d-block mb-2">
                                <i class="fas fa-camera text-success me-1"></i> লেখকের ছবি / প্রোফাইল ফটো <span class="text-muted small fw-normal">(ঐচ্ছিক কিন্তু সুপারিশকৃত)</span>
                            </label>
                            
                            <div class="d-flex flex-column flex-sm-row align-items-center justify-content-center gap-3">
                                <div class="text-center">
                                    <div class="position-relative overflow-hidden rounded-circle shadow-sm border border-2 border-success mx-auto d-flex align-items-center justify-content-center" style="width: 96px; height: 96px; background: #f8fafc;">
                                        <img id="authorAvatarPreview" 
                                             src="https://placehold.co/150x150/e2e8f0/198754?text=Author+Photo" 
                                             alt="Author Preview" 
                                             class="object-fit-cover transition-transform" 
                                             style="width: 100%; height: 100%; transform: scale(1); transition: transform 0.15s ease;">
                                    </div>
                                    <!-- Dynamic Avatar Zoom Bar Slider -->
                                    <div class="mt-2 d-flex align-items-center justify-content-center gap-1.5" id="authorZoomControlWrapper">
                                        <i class="fa-solid fa-magnifying-glass-minus text-muted" style="font-size: 11px;"></i>
                                        <input type="range" class="form-range" style="width: 90px; height: 4px;" min="0.6" max="2.2" step="0.05" value="1" id="authorAvatarZoomRange" oninput="zoomAuthorAvatar(this.value)" title="ছবির জুম নিয়ন্ত্রণ করুন">
                                        <i class="fa-solid fa-magnifying-glass-plus text-muted" style="font-size: 11px;"></i>
                                        <span class="badge bg-light text-dark border" style="font-size: 10px;" id="authorAvatarZoomBadge">100%</span>
                                    </div>
                                </div>
                                <div class="text-start">
                                    <input type="file" name="avatar" id="authorAvatarInput" 
                                           accept="image/jpeg,image/png,image/jpg,image/webp" 
                                           class="form-control form-control-sm rounded-3 @error('avatar') is-invalid @enderror"
                                           onchange="previewAuthorAvatar(this)">
                                    <div class="form-text small text-muted mt-1" style="font-size: 11.5px;">
                                        <i class="fas fa-circle-info text-success me-1"></i> পাসপোর্ট সাইজ বা স্কয়ার ছবি (JPG/PNG/WebP)। জুম বার দিয়ে ছবিটি অ্যাডজাস্ট করতে পারবেন।
                                    </div>
                                    @error('avatar')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">লেখকের পুরো নাম <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control rounded-3 @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}" required placeholder="আপনার পুরো নাম">
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">মোবাইল নম্বর (ইউজারনেম) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-phone text-success"></i></span>
                                <input type="tel" name="phone" class="form-control rounded-end-3 @error('phone') is-invalid @enderror"
                                       value="{{ old('phone') }}" required placeholder="01XXXXXXXXX">
                            </div>
                            <div class="form-text small text-muted"><i class="fa-solid fa-shield-check text-success me-1"></i> এই মোবাইল নম্বরটি দিয়ে আপনি ওয়েবসাইটে লগইন করবেন।</div>
                            @error('phone')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">লেখকের নিজস্ব সক্রিয় ইমেইল <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-envelope text-primary"></i></span>
                                <input type="email" name="email" class="form-control rounded-end-3 @error('email') is-invalid @enderror"
                                       value="{{ old('email') }}" required placeholder="yourname@gmail.com">
                            </div>
                            <div class="form-text small text-muted"><i class="fa-solid fa-circle-info text-primary me-1"></i> অ্যাকাউন্ট অনুমোদন, পাসওয়ার্ড রিসেট ও লেখা অনুমোদনের নোটিফিকেশন এই ইমেইলে যাবে।</div>
                            @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="row g-2">
                            <div class="col-sm-6 mb-3">
                                <label class="form-label fw-semibold">পাসওয়ার্ড <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" name="password" id="authorRegPassword" class="form-control rounded-start-3 @error('password') is-invalid @enderror" required minlength="6" maxlength="50" placeholder="ন্যূনতম ৬ অক্ষর" oninput="checkPasswordStrength(this.value, 'authorPwdStrengthBar', 'authorPwdStrengthText')">
                                    <button type="button" class="btn btn-outline-secondary rounded-end-3" onclick="togglePasswordVisibility('authorRegPassword', this)" title="পাসওয়ার্ড দেখুন বা লুকান">
                                        <i class="fa-regular fa-eye"></i>
                                    </button>
                                </div>
                                @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-sm-6 mb-3">
                                <label class="form-label fw-semibold">পাসওয়ার্ড নিশ্চিত করুন <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" name="password_confirmation" id="authorRegPasswordConfirm" class="form-control rounded-start-3" required minlength="6" maxlength="50" placeholder="পুনরায় লিখুন">
                                    <button type="button" class="btn btn-outline-secondary rounded-end-3" onclick="togglePasswordVisibility('authorRegPasswordConfirm', this)" title="পাসওয়ার্ড দেখুন বা লুকান">
                                        <i class="fa-regular fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-12 mb-3">
                                <div class="d-flex align-items-center justify-content-between mb-1" style="font-size: 11.5px;">
                                    <span class="text-muted">পাসওয়ার্ডের শক্তি: <strong id="authorPwdStrengthText" class="text-secondary">টাইপ করুন...</strong></span>
                                </div>
                                <div class="progress" style="height: 4px;">
                                    <div id="authorPwdStrengthBar" class="progress-bar bg-danger" role="progressbar" style="width: 0%; transition: width 0.3s ease;"></div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-3">
                        <h6 class="fw-bold text-muted mb-3"><i class="fas fa-feather-pointed text-success me-1"></i> লেখকের অতিরিক্ত তথ্য</h6>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">ছদ্মনাম / কলমনাম <span class="text-muted small">(যদি থাকে)</span></label>
                            <input type="text" name="pen_name" class="form-control rounded-3" value="{{ old('pen_name') }}" placeholder="ঐচ্ছিক — ব্যবহার না করলে আসল নাম ব্যবহার হবে">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">লেখার ধরন / ঘরানা</label>
                            <select name="genre" class="form-select rounded-3 @error('genre') is-invalid @enderror">
                                <option value="">বেছে নিন (ঐচ্ছিক)</option>
                                @foreach(['উপন্যাস','গল্প','কবিতা','প্রবন্ধ','শিশু সাহিত্য','বিজ্ঞান কল্পকাহিনী','রহস্য','ঐতিহাসিক','ধর্মীয়','অন্যান্য'] as $g)
                                <option value="{{ $g }}" @selected(old('genre') === $g)>{{ $g }}</option>
                                @endforeach
                            </select>
                            @error('genre')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">পরিচিতি / বায়ো</label>
                            <textarea name="bio" rows="3" class="form-control rounded-3 @error('bio') is-invalid @enderror"
                                      placeholder="আপনার সম্পর্কে সংক্ষেপে লিখুন (ঐচ্ছিক)...">{{ old('bio') }}</textarea>
                            @error('bio')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">জাতীয় পরিচয়পত্র নম্বর</label>
                            <input type="text" name="nid" class="form-control rounded-3" value="{{ old('nid') }}" placeholder="ঐচ্ছিক">
                        </div>

                        <div class="alert alert-info small py-2.5 rounded-3 d-flex align-items-center gap-2 mb-3">
                            <i class="fas fa-clock text-info fs-5"></i>
                            <span>রেজিস্ট্রেশন জমা দেওয়ার পর অ্যাডমিন অ্যাকাউন্টটি অনুমোদন করবেন। অনুমোদন নোটিফিকেশন ও লগইন নির্দেশিকা আপনার ইমেইলে পাঠিয়ে দেওয়া হবে।</span>
                        </div>

                        <button type="submit" class="btn w-100 py-2.5 fw-bold text-white rounded-pill shadow-sm" style="background:#198754">
                            <i class="fas fa-pen-nib me-1.5"></i> লেখক হিসেবে নিবন্ধন করুন
                        </button>
                        <p class="text-center mt-3 mb-0">
                            <a href="{{ route('register.choose') }}" class="text-muted small text-decoration-none">
                                <i class="fa-solid fa-arrow-left me-1"></i> অন্য ধরনের অ্যাকাউন্ট (পাঠক / ক্রেতা / প্রকাশক)
                            </a>
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function previewAuthorAvatar(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('authorAvatarPreview');
            if (preview) {
                preview.src = e.target.result;
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function zoomAuthorAvatar(val) {
    const preview = document.getElementById('authorAvatarPreview');
    const badge = document.getElementById('authorAvatarZoomBadge');
    if (preview) {
        preview.style.transform = `scale(${val})`;
    }
    if (badge) {
        badge.textContent = `${Math.round(val * 100)}%`;
    }
}

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
</script>
@endsection
