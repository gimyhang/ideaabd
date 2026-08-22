@extends('layouts.frontend')

@section('title', 'পেমেন্ট সম্পন্ন হয়নি — আইডিয়া প্রকাশন')

@section('content')
<div class="container py-5 my-md-4">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden text-center p-4 p-md-5 bg-white">
                
                {{-- Failed / Cancel Icon --}}
                <div class="mb-4">
                    <div class="rounded-circle bg-danger bg-opacity-10 text-danger d-inline-flex align-items-center justify-content-center shadow-xs" 
                         style="width: 85px; height: 85px;">
                        <i class="fas fa-circle-xmark fs-1 text-danger"></i>
                    </div>
                </div>

                <h3 class="fw-bold text-dark mb-2">পেমেন্ট সম্পন্ন করা যায়নি</h3>
                <p class="text-muted small mb-4">
                    {{ session('error', 'আপনার পেমেন্টটি প্রক্রিয়া করা যায়নি অথবা বাতিল করা হয়েছে। আপনি চাইলে পুনরায় চেষ্টা করতে পারেন অথবা ক্যাশ অন ডেলিভারি (COD) নির্বাচন করতে পারেন।') }}
                </p>

                <div class="d-flex flex-column flex-sm-row gap-2 justify-content-center">
                    <a href="{{ route('checkout') }}" class="btn btn-primary rounded-pill px-4 py-2.5 fw-bold shadow-xs">
                        <i class="fas fa-rotate-left me-1.5"></i> পুনরায় চেষ্টা করুন
                    </a>
                    <a href="{{ route('contact') }}" class="btn btn-outline-secondary rounded-pill px-4 py-2.5 fw-semibold">
                        <i class="fas fa-headset me-1.5"></i> সহায়তা নিন
                    </a>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
