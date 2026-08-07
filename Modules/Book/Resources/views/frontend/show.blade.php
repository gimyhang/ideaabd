@extends('layouts.app')

@section('title', $book->title)

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white p-6 rounded shadow">
        <h1 class="text-2xl font-bold">{{ $book->title }}</h1>
        <p class="text-sm text-gray-600">by {{ $book->authors->pluck('name')->join(', ') }}</p>
        <p class="mt-4">{!! nl2br(e($book->description)) !!}</p>
        <p class="mt-4 font-semibold">Price: ৳ {{ number_format($book->price, 2) }}</p>
        <a href="/" class="inline-block mt-4 text-blue-600">ফিরে যান</a>
    </div>

    @if(isset($relatedBooks) && $relatedBooks->isNotEmpty())
        <div class="mt-8">
            <h3 class="font-semibold mb-3">সম্পর্কিত বই</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach($relatedBooks as $r)
                    <div class="bg-white p-4 rounded shadow">
                        <h4 class="font-semibold">{{ $r->title }}</h4>
                        <p class="text-sm">by {{ $r->authors->pluck('name')->join(', ') }}</p>
                        <a href="/books/{{ $r->slug }}" class="text-blue-600 text-sm">বিস্তারিত</a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
