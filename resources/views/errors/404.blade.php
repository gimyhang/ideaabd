@extends('layouts.app')

@section('title', 'পাতাটি পাওয়া যায়নি (৪০৪) — আইডিয়া প্রকাশন')

@section('content')
<div class="container py-5 my-md-5">
    <div class="row justify-content-center">
        <div class="col-lg-7 col-md-9 text-center">
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
                <div class="mb-4">
                    <span class="display-1 fw-bold text-primary opacity-25 d-block font-monospace">৪০৪</span>
                    <div class="rounded-circle bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center shadow-xs" style="width: 80px; height: 80px; margin-top: -40px;">
                        <i class="fa-solid fa-book-skull fs-2"></i>
                    </div>
                </div>

                <h2 class="fw-bold text-dark mb-2">অনুরোধকৃত পাতা বা বইটি পাওয়া যায়নি</h2>
                <p class="text-muted mb-4 lead fs-6">
                    আপনি যে লিঙ্কটি খুঁজছেন তা হয়তো সরানো হয়েছে, নাম পরিবর্তন হয়েছে অথবা বর্তমানে অনুপলব্ধ রয়েছে।
                </p>

                <!-- Search Form -->
                <form action="{{ route('search') }}" method="GET" class="mb-4">
                    <div class="input-group input-group-lg rounded-pill border overflow-hidden shadow-xs">
                        <span class="input-group-text bg-white border-0 ps-3">
                            <i class="fa-solid fa-magnifying-glass text-muted"></i>
                        </span>
                        <input type="text" name="q" class="form-control border-0 ps-2" placeholder="বইয়ের নাম, লেখক বা বিষয় দিয়ে খুঁজুন..." aria-label="Search" required>
                        <button class="btn btn-primary px-4 fw-bold" type="submit">খুঁজুন</button>
                    </div>
                </form>

                <!-- Action Buttons -->
                <div class="d-flex flex-wrap align-items-center justify-content-center gap-2">
                    <a href="{{ url('/') }}" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold">
                        <i class="fa-solid fa-house me-1.5"></i> হোম পেজে যান
                    </a>
                    <a href="{{ route('book.index') }}" class="btn btn-outline-secondary rounded-pill px-4 py-2 fw-semibold">
                        <i class="fa-solid fa-book me-1.5"></i> বই ক্যাটালগ দেখুন
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
