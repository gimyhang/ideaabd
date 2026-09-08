{{-- Shared partial used by registration forms --}}
<div class="mb-3">
    <label class="form-label fw-semibold">Contact Person <span class="text-danger">*</span></label>
    <input type="text" name="name" class="form-control rounded-3 @error('name') is-invalid @enderror"
           value="{{ old('name') }}" required placeholder="Contact person full name">
    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<div class="row g-2 mb-3">
    <div class="col-sm-6">
        <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
        <input type="email" name="email" class="form-control rounded-3 @error('email') is-invalid @enderror"
               value="{{ old('email') }}" required placeholder="email@example.com">
        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-sm-6">
        <label class="form-label fw-semibold">Mobile No <span class="text-danger">*</span></label>
        <input type="tel" name="phone" class="form-control rounded-3 @error('phone') is-invalid @enderror"
               value="{{ old('phone') }}" required placeholder="01XXXXXXXXX">
        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>
<div class="row g-2">
    <div class="col-sm-6 mb-3">
        <label class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
        <input type="password" name="password" class="form-control rounded-3 @error('password') is-invalid @enderror" required minlength="8" maxlength="25" placeholder="8-25 characters & symbol">
        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-sm-6 mb-3">
        <label class="form-label fw-semibold">Confirm Password <span class="text-danger">*</span></label>
        <input type="password" name="password_confirmation" class="form-control rounded-3" required minlength="8" maxlength="25" placeholder="Retype password">
    </div>
</div>

