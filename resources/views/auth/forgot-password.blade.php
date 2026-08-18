@extends('layouts.app')

@section('title', 'পাসওয়ার্ড রিসেট — ideaabd')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-5 col-md-7 col-sm-10">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header border-0 py-4 text-center" style="background: linear-gradient(135deg, #003366 0%, #0066cc 100%);">
                    <div class="rounded-circle bg-white bg-opacity-20 d-inline-flex align-items-center justify-content-center mb-2 shadow-xs" style="width: 55px; height: 55px;">
                        <i class="fa-solid fa-key text-white fs-4"></i>
                    </div>
                    <h4 class="fw-bold text-white mb-1">পাসওয়ার্ড রিসেট</h4>
                    <p class="text-white text-opacity-75 small mb-0">ইমেইলে ওয়ান-টাইম রিসেট লিংকের মাধ্যমে পাসওয়ার্ড পুনরুদ্ধার করুন</p>
                </div>
                
                <div class="card-body p-4 p-md-4.5">
                    @if(session('status'))
                        <div class="alert alert-success rounded-3 small mb-3 p-3 border-0 bg-success bg-opacity-10 text-success fw-medium">
                            <i class="fa-solid fa-circle-check me-1 fs-6"></i> {{ session('status') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger rounded-3 small mb-3 p-3 border-0 bg-danger bg-opacity-10 text-danger">
                            <div class="fw-bold mb-1"><i class="fa-solid fa-triangle-exclamation me-1"></i> সমস্যা দেখা দিয়েছে:</div>
                            <ul class="mb-0 ps-3">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('password.email') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="identity" class="form-label fw-semibold text-dark">
                                নিবন্ধিত ইমেইল অথবা মোবাইল নম্বর <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-envelope text-primary"></i></span>
                                <input type="text" 
                                       id="identity" 
                                       name="identity" 
                                       class="form-control rounded-end-3 @error('identity') is-invalid @enderror" 
                                       value="{{ old('identity') }}" 
                                       required 
                                       autofocus 
                                       placeholder="example@mail.com অথবা 01XXXXXXXXX">
                            </div>
                            <div class="form-text small text-muted mt-2">
                                <i class="fa-solid fa-clock text-warning me-1"></i> আপনার অ্যাকাউন্টে নিবন্ধিত ইমেইলে একটি <strong>ওয়ান-টাইম পাসওয়ার্ড রিসেট লিংক</strong> পাঠানো হবে (যার মেয়াদ থাকবে <strong>৩ মিনিট</strong>)।
                            </div>
                            @error('identity')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2.5 rounded-pill fw-bold shadow-sm mb-3">
                            <i class="fa-solid fa-paper-plane me-1.5"></i> রিসেট লিংক পাঠান
                        </button>

                        <div class="text-center">
                            <a href="{{ route('login') }}" class="text-decoration-none small text-muted hover-primary">
                                <i class="fa-solid fa-arrow-left me-1"></i> লগইন পেজে ফিরে যান
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
