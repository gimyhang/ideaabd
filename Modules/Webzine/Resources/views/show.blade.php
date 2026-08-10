@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-slate-50">
    <div class="bg-gradient-to-r from-brand-600 to-accent py-12">
        <div class="container mx-auto px-4 text-white">
            <h1 class="text-4xl font-bold mb-2">{{ $webzine->title }}</h1>
            <p class="text-lg mb-4">{{ $webzine->description }}</p>
            <div class="flex gap-4 flex-wrap text-sm">
                <span>ইস্যু {{ $webzine->issue_number }}</span>
                <span>{{ $webzine->publication_date?->format('d M Y') }}</span>
                <span>{{ $webzine->view_count }} বার পড়া হয়েছে</span>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 py-12">
        <div class="max-w-2xl mx-auto">
            @if($webzine->cover_image)
                <img src="{{ asset('storage/' . $webzine->cover_image) }}" alt="{{ $webzine->title }}" class="w-full rounded-lg mb-8">
            @endif

            <a href="{{ route('webzine.read', $webzine->slug) }}" class="inline-block px-8 py-3 bg-brand-600 text-white rounded-lg font-bold hover:bg-brand-700 mb-8">
                📖 সম্পূর্ণ ম্যাগাজিন পড়ুন
            </a>

            @if($articles->count())
                <div class="bg-white rounded-lg p-8">
                    <h2 class="text-2xl font-bold mb-6">প্রধান নিবন্ধ</h2>
                    <div class="space-y-6">
                        @foreach($articles->take(3) as $article)
                            <article class="border-b pb-6 last:border-b-0">
                                <h3 class="text-xl font-bold mb-2">{{ $article->title }}</h3>
                                @if($article->author)
                                    <p class="text-sm text-slate-600 mb-2">লেখক: {{ $article->author->name }}</p>
                                @endif
                                <p class="text-slate-700">{{ Str::limit(strip_tags($article->content), 200) }}</p>
                            </article>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
