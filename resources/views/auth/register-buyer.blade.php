@extends('layouts.app')
@section('title', 'বুক বায়ার রেজিস্ট্রেশন - ideaabd')
@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header border-0 py-4" style="background:linear-gradient(135deg,#FFF5E5,#FFE5D4)">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:50px;height:50px;background:#fd7e14">
                            <i class="fas fa-shopping-bag text-white"></i>
                        </div>
                        <div>
                            <h4 class="fw-bold mb-0" style="color:#fd7e14">বুক বায়ার রেজিস্ট্রেশন</h4>
                            <small class="text-muted">বিনামূল্যে এবং তাৎক্ষণিক অ্যাক্সেস</small>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    @if($errors->any())
                        <div class="alert alert-danger"><ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
                    @endif

                    <form method="POST" action="{{ route('register.submit', 'buyer') }}">
                        @csrf
                        @include('auth.partials.base-fields')

                        <div class="mb-3">
                            <label class="form-label fw-semibold">ডেলিভারি ঠিকানা</label>
                            <textarea name="address" rows="2" class="form-control" placeholder="ঐচ্ছিক">{{ old('address') }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">জন্ম তারিখ</label>
                            <input type="date" name="date_of_birth" class="form-control" value="{{ old('date_of_birth') }}">
                        </div>

                        <div class="alert alert-success small py-2">
                            <i class="fas fa-check-circle me-1"></i>
                            রেজিস্ট্রেশনের সাথে সাথেই বই অর্ডার করতে পারবেন।
                        </div>

                        <button type="submit" class="btn w-100 py-2 fw-bold text-white" style="background:#fd7e14">রেজিস্ট্রেশন করুন</button>
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
