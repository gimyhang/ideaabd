@extends('layouts.app')
@section('title', 'সেলার রেজিস্ট্রেশন - ideaabd')
@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header border-0 py-4" style="background:linear-gradient(135deg,#E8F4F8,#D4E9F0)">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:50px;height:50px;background:#0066cc">
                            <i class="fas fa-store text-white"></i>
                        </div>
                        <div>
                            <h4 class="fw-bold mb-0" style="color:#0066cc">সেলার রেজিস্ট্রেশন</h4>
                            <small class="text-muted">অনুমোদনের পরে বই বিক্রি শুরু করুন</small>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    @if($errors->any())
                        <div class="alert alert-danger"><ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
                    @endif

                    <form method="POST" action="{{ route('register.submit', 'seller') }}">
                        @csrf
                        @include('auth.partials.base-fields')

                        <hr class="my-3">
                        <h6 class="fw-bold text-muted mb-3">দোকানের তথ্য</h6>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">দোকানের নাম <span class="text-danger">*</span></label>
                            <input type="text" name="shop_name" class="form-control @error('shop_name') is-invalid @enderror"
                                   value="{{ old('shop_name') }}" required>
                            @error('shop_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">ঠিকানা <span class="text-danger">*</span></label>
                            <textarea name="address" rows="2" class="form-control @error('address') is-invalid @enderror" required>{{ old('address') }}</textarea>
                            @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="row">
                            <div class="col-sm-6 mb-3">
                                <label class="form-label fw-semibold">ট্রেড লাইসেন্স নম্বর</label>
                                <input type="text" name="trade_license" class="form-control" value="{{ old('trade_license') }}" placeholder="ঐচ্ছিক">
                            </div>
                            <div class="col-sm-6 mb-3">
                                <label class="form-label fw-semibold">জাতীয় পরিচয়পত্র নম্বর</label>
                                <input type="text" name="nid" class="form-control" value="{{ old('nid') }}" placeholder="ঐচ্ছিক">
                            </div>
                        </div>

                        <div class="alert alert-info small py-2">
                            <i class="fas fa-info-circle me-1"></i>
                            রেজিস্ট্রেশনের পরে অ্যাডমিন যাচাই করবেন। অনুমোদন পেলে আপনাকে ইমেইলে জানানো হবে।
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">রেজিস্ট্রেশন করুন</button>
                        <p class="text-center mt-3 mb-0">
                            <a href="{{ route('register.choose') }}" class="text-muted small">← অন্য ধরনের অ্যাকাউন্ট</a>
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
