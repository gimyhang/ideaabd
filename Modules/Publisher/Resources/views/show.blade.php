@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-slate-50">
    <div class="bg-gradient-to-r from-brand-600 to-accent py-12">
        <div class="container mx-auto px-4 text-white">
            <h1 class="text-4xl font-bold mb-2">{{ $publisher->name }}</h1>
            <p class="text-lg mb-4">{{ $publisher->description }}</p>
            <div class="flex gap-4 flex-wrap">
                @if($publisher->email)
                    <span class="text-sm">📧 {{ $publisher->email }}</span>
                @endif
                @if($publisher->website)
                    <a href="{{ $publisher->website }}" target="_blank" class="text-sm hover:underline">🌐 ওয়েবসাইট</a>
                @endif
                @if($publisher->is_verified)
                    <span class="text-sm bg-green-600 px-3 py-1 rounded">✓ যাচাইকৃত</span>
                @endif
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 py-12">
        <h2 class="text-3xl font-bold mb-8">প্রকাশিত বই</h2>
        
        @if($books->count())
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                @foreach($books as $book)
                    <article class="shop-card">
                        @if($book->cover_image)
                            <img src="{{ asset('storage/' . $book->cover_image) }}" alt="{{ $book->title }}" class="w-full h-48 object-cover">
                        @else
                            <div class="w-full h-48 bg-gradient-to-br from-brand-400 to-brand-600 flex items-center justify-center">
                                <span class="text-2xl">📚</span>
                            </div>
                        @endif
                        <div class="p-3">
                            <h3 class="font-bold text-sm mb-1">{{ Str::limit($book->title, 40) }}</h3>
                            <p class="text-xs text-slate-600 mb-2">{{ $book->price }} টাকা</p>
                            <a href="{{ route('book.show', $book->slug) }}" class="text-xs text-brand-600 hover:text-brand-700 font-semibold">বিস্তারিত →</a>
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <div class="bg-white rounded-lg p-12 text-center">
                <p class="text-slate-600">এই প্রকাশনীর কোন বই নেই</p>
            </div>
        @endif
    </div>
</div>
@endsection
