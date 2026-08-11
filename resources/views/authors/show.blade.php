@extends('layouts.app')

@section('title', $author->name ?? 'লেখক')

@section('content')

<main class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center gap-6">
            <img src="{{ $author->image ?? asset('images/placeholder/author.jpg') }}" alt="{{ $author->name }}" class="w-28 h-28 object-cover rounded" />
            <div>
                <h1 class="text-2xl font-extrabold">{{ $author->name }}</h1>
                <p class="mt-2 text-sm text-gray-600">{{ $author->bio ?? 'লেখকের সংক্ষিপ্ত পরিচিতি নেই।' }}</p>
                <div class="mt-3 text-sm">
                    <a href="#" class="text-indigo-600">সব বই দেখুন</a>
                </div>
            </div>
        </div>

        <div class="mt-8">
            <h2 class="text-lg font-semibold">লেখকের বইসমূহ</h2>
            <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($books as $book)
                    @include('components.book-card', ['book' => $book])
                @endforeach
            </div>

            <div class="mt-6">
                {{ $books->links() }}
            </div>
        </div>
    </div>
</main>

@endsection
