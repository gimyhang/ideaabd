@extends('layouts.app')
@section('title', 'ক্রেতা / বুক বায়ার রেজিস্ট্রেশন - ideaabd')
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
                            <h4 class="fw-bold mb-1 text-dark" style="color:#e8590c;">মোবাইল নম্বর দিয়ে রেজিস্ট্রেশন</h4>
                            <small class="text-muted">তাত্ক্ষণিক ভেরিফিকেশন মেসেজ ও ফ্রি অ্যাক্সেস</small>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    @if($errors->any())
                        <div class="alert alert-danger rounded-3"><ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
                    @endif

                    <form method="POST" action="{{ route('register.submit', 'buyer') }}">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">আপনার নাম <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control rounded-3 @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}" required placeholder="আপনার পুরো নাম">
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">মোবাইল নম্বর <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-phone text-success"></i></span>
                                <input type="tel" name="phone" class="form-control rounded-end-3 @error('phone') is-invalid @enderror"
                                       value="{{ old('phone') }}" required placeholder="01XXXXXXXXX">
                            </div>
                            <div class="form-text small text-muted"><i class="fa-solid fa-shield-check text-primary me-1"></i> এই নম্বরে ভেরিফিকেশন ও অর্ডার কনফার্মেশন বার্তা পাঠানো হবে।</div>
                            @error('phone')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">ইমেইল <span class="badge bg-light text-muted border">ঐচ্ছিক / Optional</span></label>
                            <input type="email" name="email" class="form-control rounded-3 @error('email') is-invalid @enderror"
                                   value="{{ old('email') }}" placeholder="ইমেইল থাকলে দিন (ঐচ্ছিক)">
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="row">
                            <div class="col-sm-6 mb-3">
                                <label class="form-label fw-semibold">পাসওয়ার্ড <span class="text-danger">*</span></label>
                                <input type="password" name="password" class="form-control rounded-3 @error('password') is-invalid @enderror" required minlength="6" placeholder="কমপক্ষে ৬ অক্ষর">
                                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-sm-6 mb-3">
                                <label class="form-label fw-semibold">পাসওয়ার্ড নিশ্চিত করুন <span class="text-danger">*</span></label>
                                <input type="password" name="password_confirmation" class="form-control rounded-3" required minlength="6" placeholder="পুনরায় লিখুন">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">ডেলিভারি ঠিকানা</label>
                            <textarea name="address" rows="2" class="form-control rounded-3" placeholder="বাসা/রোড, এলাকা, থানা ও জেলা (ঐচ্ছিক)">{{ old('address') }}</textarea>
                        </div>

                        <div class="alert alert-success small py-2.5 rounded-3 d-flex align-items-center gap-2">
                            <i class="fa-solid fa-circle-check text-success fs-5"></i>
                            <span>রেজিস্ট্রেশন সম্পন্ন হলেই সরাসরি ক্যাশ অন ডেলিভারিতে বই কিনতে পারবেন।</span>
                        </div>

                        <button type="submit" class="btn w-100 py-2.5 fw-bold text-white rounded-pill shadow-sm" style="background:#fd7e14">
                            <i class="fa-solid fa-user-plus me-1.5"></i> অ্যাকাউন্ট তৈরি করুন
                        </button>
                        
                        <p class="text-center mt-3 mb-0">
                            <a href="{{ route('register.choose') }}" class="text-muted small text-decoration-none">
                                <i class="fa-solid fa-arrow-left me-1"></i> অন্য ধরনের অ্যাকাউন্ট (লেখক / প্রকাশক / সেলার)
                            </a>
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
