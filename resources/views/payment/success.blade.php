@extends('layouts.frontend')

@section('title', 'পেমেন্ট সফল — আইডিয়া প্রকাশন')

@section('content')
<div class="container py-5 my-md-4">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden text-center p-4 p-md-5 bg-white">
                
                {{-- Success Icon Animation --}}
                <div class="mb-4">
                    <div class="rounded-circle bg-success bg-opacity-10 text-success d-inline-flex align-items-center justify-content-center shadow-xs" 
                         style="width: 85px; height: 85px;">
                        <i class="fas fa-check-circle fs-1 text-success"></i>
                    </div>
                </div>

                <h3 class="fw-bold text-dark mb-2">পেমেন্ট সফল হয়েছে!</h3>
                <p class="text-muted small mb-4">
                    {{ session('message', 'আপনার অনলাইন পেমেন্টটি সফলভাবে সম্পন্ন হয়েছে। আমাদের টিম আপনার অর্ডারটি প্রস্তুত করছে।') }}
                </p>

                @if(session('order_number') || request('order_number'))
                    <div class="p-3 bg-light rounded-3 border mb-4 text-start font-monospace small">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">অর্ডার নম্বর:</span>
                            <strong class="text-primary">#{{ session('order_number') ?? request('order_number') }}</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">পেমেন্ট স্ট্যাটাস:</span>
                            <span class="badge bg-success">পরিশোধিত (Paid)</span>
                        </div>
                    </div>
                @endif

                <div class="d-flex flex-column flex-sm-row gap-2 justify-content-center">
                    <a href="{{ route('home') }}" class="btn btn-primary rounded-pill px-4 py-2.5 fw-bold shadow-xs">
                        <i class="fas fa-house me-1.5"></i> হোমে ফিরে যান
                    </a>
                    <a href="{{ route('book.index') }}" class="btn btn-outline-secondary rounded-pill px-4 py-2.5 fw-semibold">
                        <i class="fas fa-book-open me-1.5"></i> আরও বই দেখুন
                    </a>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
