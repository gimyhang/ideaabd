@extends('layouts.app')
@section('title', 'প্রকাশক রেজিস্ট্রেশন - ideaabd')
@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header border-0 py-4" style="background:linear-gradient(135deg,#FFE5F5,#FFD4E5)">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:50px;height:50px;background:#d63384">
                            <i class="fas fa-print text-white"></i>
                        </div>
                        <div>
                            <h4 class="fw-bold mb-0" style="color:#d63384">প্রকাশক রেজিস্ট্রেশন</h4>
                            <small class="text-muted">ideaabd-তে প্রকাশনী হিসেবে যোগ দিন</small>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    @if($errors->any())
                        <div class="alert alert-danger"><ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
                    @endif

                    <form method="POST" action="{{ route('register.submit', 'publisher') }}">
                        @csrf
                        @include('auth.partials.base-fields')

                        <hr class="my-3">
                        <h6 class="fw-bold text-muted mb-3">প্রকাশনীর তথ্য</h6>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">প্রকাশনীর নাম <span class="text-danger">*</span></label>
                            <input type="text" name="publisher_name" class="form-control @error('publisher_name') is-invalid @enderror"
                                   value="{{ old('publisher_name') }}" required>
                            @error('publisher_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">ঠিকানা <span class="text-danger">*</span></label>
                            <textarea name="address" rows="2" class="form-control @error('address') is-invalid @enderror" required>{{ old('address') }}</textarea>
                            @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="row">
                            <div class="col-sm-6 mb-3">
                                <label class="form-label fw-semibold">প্রতিষ্ঠার বছর</label>
                                <input type="number" name="established" class="form-control" value="{{ old('established') }}" min="1800" max="{{ date('Y') }}" placeholder="e.g. 2005">
                            </div>
                            <div class="col-sm-6 mb-3">
                                <label class="form-label fw-semibold">ট্রেড লাইসেন্স নম্বর</label>
                                <input type="text" name="trade_license" class="form-control" value="{{ old('trade_license') }}" placeholder="ঐচ্ছিক">
                            </div>
                        </div>

                        <div class="alert alert-info small py-2">
                            <i class="fas fa-info-circle me-1"></i>
                            রেজিস্ট্রেশনের পরে অ্যাডমিন যাচাই করবেন।
                        </div>

                        <button type="submit" class="btn w-100 py-2 fw-bold text-white" style="background:#d63384">রেজিস্ট্রেশন করুন</button>
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
