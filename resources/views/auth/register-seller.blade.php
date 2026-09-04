@extends('layouts.app')
@section('title', 'সেলার রেজিস্ট্রেশন — আইডিয়া প্রকাশন')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-7 col-md-9">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
                <div class="card-header border-0 py-4 px-4 text-white" style="background: linear-gradient(135deg, #0f172a 0%, #0369a1 50%, #0284c7 100%);">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-white text-primary d-flex align-items-center justify-content-center shadow-xs flex-shrink-0" style="width:54px;height:54px;">
                            <i class="fas fa-store fs-4"></i>
                        </div>
                        <div>
                            <h4 class="fw-bold mb-0 text-white fs-4">সেলার / ভেন্ডর রেজিস্ট্রেশন</h4>
                            <small class="text-white-50">আইডিয়া প্ল্যাটফর্মে বই বিক্রেতা ও ডিলার হিসেবে যুক্ত হোন</small>
                        </div>
                    </div>
                </div>
                
                <div class="card-body p-4 p-md-4.5">
                    @if($errors->any())
                        <div class="alert alert-danger rounded-3"><ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
                    @endif

                    <form method="POST" action="{{ route('register.submit', 'seller') }}" id="sellerRegisterForm" onsubmit="document.getElementById('sellerSubmitBtn').disabled = true; document.getElementById('sellerSubmitSpinner').classList.remove('d-none'); document.getElementById('sellerSubmitText').textContent = 'আবেদন জমা হচ্ছে...';">
                        @csrf
                        @include('auth.partials.base-fields')

                        <hr class="my-3.5">
                        <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                            <i class="fas fa-shop text-primary"></i>
                            <span>দোকান ও ব্যবসা সংক্রান্ত তথ্য</span>
                        </h6>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-dark">দোকান / বুকশপের নাম <span class="text-danger">*</span></label>
                            <input type="text" name="shop_name" class="form-control rounded-3 @error('shop_name') is-invalid @enderror"
                                   value="{{ old('shop_name') }}" placeholder="আপনার বইয়ের দোকান বা বুকশপের নাম" required>
                            @error('shop_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-dark">দোকান / ব্যবসার ঠিকানা <span class="text-danger">*</span></label>
                            <textarea name="address" rows="2" class="form-control rounded-3 @error('address') is-invalid @enderror" placeholder="দোকানের পূর্ণাঙ্গ ঠিকানা" required>{{ old('address') }}</textarea>
                            @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="row g-2">
                            <div class="col-sm-6 mb-3">
                                <label class="form-label small fw-bold text-dark">ট্রেড লাইসেন্স নম্বর <span class="text-muted small">(ঐচ্ছিক)</span></label>
                                <input type="text" name="trade_license" class="form-control rounded-3" value="{{ old('trade_license') }}" placeholder="ট্রেড লাইসেন্স নম্বর (যদি থাকে)">
                            </div>
                            <div class="col-sm-6 mb-3">
                                <label class="form-label small fw-bold text-dark">জাতীয় পরিচয়পত্র নম্বর <span class="text-muted small">(ঐচ্ছিক)</span></label>
                                <input type="text" name="nid" class="form-control rounded-3" value="{{ old('nid') }}" placeholder="NID নম্বর">
                            </div>
                        </div>

                        <div class="alert alert-info bg-info-subtle border-info-subtle text-info-emphasis small py-2.5 px-3 rounded-3 d-flex align-items-center gap-2 mb-3">
                            <i class="fas fa-circle-info fs-5 text-info"></i>
                            <span class="fw-semibold">
                                রেজিস্ট্রেশন সাবমিট করার পর আমাদের অ্যাডমিন টিম যাচাইপূর্বক ২৪ ঘণ্টার মধ্যে সেলার অ্যাকাউন্টটি সক্রিয় করে দেবে।
                            </span>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-3 fw-bold rounded-pill shadow-xs d-flex align-items-center justify-content-center gap-2" id="sellerSubmitBtn" style="font-size: 15.5px;">
                            <span class="spinner-border spinner-border-sm d-none" id="sellerSubmitSpinner" role="status"></span>
                            <i class="fas fa-paper-plane" id="sellerSubmitIcon"></i>
                            <span id="sellerSubmitText">রেজিস্ট্রেশন আবেদন জমা দিন</span>
                        </button>
                        
                        <p class="text-center mt-3.5 mb-0">
                            <a href="{{ route('register.choose') }}" class="text-muted small text-decoration-none">
                                <i class="fas fa-arrow-left me-1"></i> অন্য ধরনের অ্যাকাউন্ট (পাঠক / লেখক / প্রকাশক)
                            </a>
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
