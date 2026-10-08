{{-- Dynamic Category Books Grid / Shelf Partial --}}
@php
    $books = $books ?? collect();
    $tab = $tab ?? 'all';
    $title = $title ?? 'সকল বই';
    $viewAllUrl = $viewAllUrl ?? route('book.index');
@endphp

@if($books->isNotEmpty())
    <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-6 g-2.5 g-md-3 align-items-stretch animate-fade-in">
        @foreach($books as $book)
            <div class="col d-flex">
                @include('book::frontend.partials.book-card', ['book' => $book])
            </div>
        @endforeach
    </div>
@else
    <div class="text-center py-5 px-3 bg-light rounded-4 border border-dashed border-2 my-2 animate-fade-in">
        <div class="rounded-circle bg-white shadow-2xs d-inline-flex align-items-center justify-content-center p-3 mb-3 text-muted" style="width: 64px; height: 64px;">
            <i class="fa-solid fa-book-open fs-3 text-secondary opacity-50"></i>
        </div>
        <h6 class="fw-bold text-dark mb-1">এই ক্যাটাগরিতে বর্তমানে কোনো বই যুক্ত নেই</h6>
        <p class="text-muted small mb-3">আমাদের বিশাল ক্যাটালগ থেকে অন্যান্য জনপ্রিয় বইগুলো খুঁজে দেখুন।</p>
        <a href="{{ route('book.index') }}" class="btn btn-primary btn-sm rounded-pill px-4 fw-bold">
            <i class="fa-solid fa-arrow-right me-1"></i>সকল বই ক্যাটালগ দেখুন
        </a>
    </div>
@endif
