{{-- Shared partial used by all registration forms --}}
<div class="mb-3">
    <label class="form-label fw-semibold">পুরো নাম <span class="text-danger">*</span></label>
    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
           value="{{ old('name') }}" required placeholder="আপনার পুরো নাম">
    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<div class="mb-3">
    <label class="form-label fw-semibold">ইমেইল এড্রেস <span class="text-danger">*</span></label>
    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
           value="{{ old('email') }}" required placeholder="email@example.com">
    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<div class="mb-3">
    <label class="form-label fw-semibold">মোবাইল নম্বর <span class="text-danger">*</span></label>
    <input type="tel" name="phone" class="form-control @error('phone') is-invalid @enderror"
           value="{{ old('phone') }}" required placeholder="01XXXXXXXXX">
    @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<div class="row">
    <div class="col-sm-6 mb-3">
        <label class="form-label fw-semibold">পাসওয়ার্ড <span class="text-danger">*</span></label>
        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required minlength="8" maxlength="25" placeholder="৮-২৫ অক্ষর ও স্পেশাল ক্যারেক্টার">
        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-sm-6 mb-3">
        <label class="form-label fw-semibold">পাসওয়ার্ড নিশ্চিত করুন <span class="text-danger">*</span></label>
        <input type="password" name="password_confirmation" class="form-control" required minlength="8" maxlength="25" placeholder="পুনরায় লিখুন">
    </div>
    <div class="col-12">
        <div class="form-text small text-muted mt-0 mb-3"><i class="fa-solid fa-shield-halved text-success me-1"></i> পাসওয়ার্ড ৮ থেকে ২৫ অক্ষরের মধ্যে হতে হবে এবং অন্তত একটি স্পেশাল ক্যারেক্টার (যেমন: @, #, $, %, !, *, ?, &) ব্যবহার করুন।</div>
    </div>
</div>
