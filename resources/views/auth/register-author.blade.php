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
                            <h4 class="fw-bold mb-1" style="color:#198754">মোবাইল দিয়ে লেখক রেজিস্ট্রেশন</h4>
                            <small class="text-muted">আপনার মোবাইল নম্বরটি ইউজারনেম হিসেবে ব্যবহার হবে</small>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    @if($errors->any())
                        <div class="alert alert-danger rounded-3"><ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
                    @endif

                    <form method="POST" action="{{ route('register.submit', 'author') }}">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">লেখকের নাম <span class="text-danger">*</span></label>
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
                            <div class="form-text small text-muted"><i class="fa-solid fa-shield-check text-success me-1"></i> এই মোবাইল নম্বরটি দিয়ে আপনি লগইন করতে পারবেন।</div>
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

                        <div class="alert alert-success small py-2.5 rounded-3 d-flex align-items-center gap-2">
                            <i class="fas fa-check-circle text-success fs-5"></i>
                            <span>রেজিস্ট্রেশন সম্পন্ন হলেই আপনি ড্যাশবোর্ডে লগইন করে ব্লগ লেখা পোস্ট বা ড্রাফট করতে পারবেন।</span>
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
@endsection
