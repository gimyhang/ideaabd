@extends('layouts.app')
@section('title', 'Register')

@section('content')
<script>
    // Automatically redirect to the unified registration form
    window.location.replace("{{ route('login', ['mode' => 'register']) }}");
</script>

<div class="container py-4 py-md-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8 text-center">
            
            <div class="card p-4 p-md-5 border-0 shadow-sm rounded-4 bg-white">
                <div class="rounded-circle bg-primary bg-opacity-10 text-primary mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
                    <i class="fa-solid fa-user-plus fs-3"></i>
                </div>

                <h1 class="fw-bold text-dark mb-2" style="font-size: 24px;">
                    নতুন অ্যাকাউন্ট তৈরি করুন
                </h1>
                <p class="text-muted small mb-4" style="font-size: 14px;">
                    একটি সহজ প্রক্রিয়ায় নিবন্ধন সম্পন্ন করুন। পরবর্তীতে ড্যাশবোর্ড থেকে ভূমিকা ও KYC তথ্য উন্নত করতে পারবেন।
                </p>

                <a href="{{ route('login', ['mode' => 'register']) }}" class="btn btn-warning text-dark btn-lg rounded-pill fw-bold w-100 py-2.5 shadow-sm d-inline-flex align-items-center justify-content-center gap-2">
                    <span>রেজিস্ট্রেশন ফর্মে এগিয়ে যান</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </a>

                <div class="mt-4 pt-3 border-top text-muted small">
                    ইতিমধ্যে অ্যাকাউন্ট আছে? 
                    <a href="{{ route('login') }}" class="text-primary fw-bold text-decoration-none ms-1">লগইন করুন</a>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
