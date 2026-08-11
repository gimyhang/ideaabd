@extends('layouts.app')
@section('title', 'লগইন - ideaabd')
@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header border-0 py-4 text-center" style="background:linear-gradient(135deg,#E8F4F8,#D4E9F0)">
                    <img src="{{ asset('images/logo.svg') }}" width="50" alt="ideaabd">
                    <h4 class="fw-bold mt-2 mb-0" style="color:#0066cc">আপনার অ্যাকাউন্টে লগইন করুন</h4>
                </div>
                <div class="card-body p-4">
                    @if($errors->any())
                        <div class="alert alert-danger py-2">
                            @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
                        </div>
                    @endif
                    @if(session('status'))
                        <div class="alert alert-success py-2">{{ session('status') }}</div>
                    @endif

                    <form method="POST" action="/login">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold">ইমেইল</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">পাসওয়ার্ড</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="form-check">
                                <input type="checkbox" name="remember" id="remember" class="form-check-input">
                                <label for="remember" class="form-check-label small">মনে রাখুন</label>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">লগইন করুন</button>
                    </form>

                    <hr>
                    <div class="text-center">
                        <p class="small text-muted mb-1">নতুন অ্যাকাউন্ট তৈরি করুন:</p>
                        <div class="d-flex gap-2 justify-content-center flex-wrap">
                            <a href="{{ route('register.form', 'buyer') }}" class="btn btn-sm btn-outline-warning">বুক বায়ার</a>
                            <a href="{{ route('register.form', 'seller') }}" class="btn btn-sm btn-outline-primary">সেলার</a>
                            <a href="{{ route('register.form', 'author') }}" class="btn btn-sm btn-outline-success">লেখক</a>
                            <a href="{{ route('register.form', 'publisher') }}" class="btn btn-sm btn-outline-danger">প্রকাশক</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
