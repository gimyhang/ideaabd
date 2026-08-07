@extends('layouts.app')

@section('title', 'বই ক্যাটালগ - আইডিয়া প্রকাশন')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <h1 class="text-2xl font-semibold">অনলাইন বুকশপ</h1>
        <p class="text-sm text-gray-600">সকল সক্রিয় বই ও ক্যাটাগরিগুলো নিচে দেখুন।</p>
    </div>

    <div class="flex gap-6">
        <aside class="w-1/4 hidden lg:block">
            <div class="bg-white p-4 rounded shadow">
                <h3 class="font-semibold mb-3">ক্যাটাগরিসমূহ</h3>
                <ul class="space-y-2">
                    @foreach($categories as $cat)
                        <li>
                            <a href="?category={{ $cat->slug }}" class="text-gray-700 hover:text-blue-600">{{ $cat->name }} <span class="text-xs text-gray-400">({{ $cat->books_count }})</span></a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </aside>

        <section class="flex-1">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($books as $book)
                    <article class="bg-white p-4 rounded shadow">
                        <h4 class="font-semibold text-lg">{{ $book->title }}</h4>
                        <p class="text-sm text-gray-600">by {{ $book->authors->pluck('name')->join(', ') }}</p>
                        <p class="mt-2 text-gray-800 font-medium">৳ {{ number_format($book->price, 2) }}</p>
                        <a href="/books/{{ $book->slug }}" class="inline-block mt-3 text-sm text-blue-600">বিস্তারিত দেখুন</a>
                    </article>
                @empty
                    <div class="col-span-full bg-white p-6 rounded shadow text-center text-gray-600">
                        কোনো বই পাওয়া যায়নি।
                    </div>
                @endforelse
            </div>

            <div class="mt-6">
                {{ $books->links() }}
            </div>
        </section>
    </div>
</div>
@endsection
