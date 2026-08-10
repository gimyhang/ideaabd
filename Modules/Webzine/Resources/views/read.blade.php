@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-white">
    <!-- Magazine Header -->
    <div class="bg-gradient-to-r from-brand-600 to-accent py-8 sticky top-0 z-50">
        <div class="container mx-auto px-4 flex justify-between items-center text-white">
            <h1 class="text-2xl font-bold">{{ $webzine->title }}</h1>
            <a href="{{ route('webzine.show', $webzine->slug) }}" class="text-sm hover:underline">← ফিরে যান</a>
        </div>
    </div>

    <div class="container mx-auto px-4 py-12">
        @if($webzine->cover_image)
            <div class="mb-12">
                <img src="{{ asset('storage/' . $webzine->cover_image) }}" alt="{{ $webzine->title }}" class="w-full max-w-3xl mx-auto rounded-lg">
            </div>
        @endif

        <div class="max-w-3xl mx-auto">
            <!-- TOC -->
            @if($articles->count())
                <div class="bg-slate-100 rounded-lg p-6 mb-12">
                    <h2 class="font-bold text-lg mb-4">বিষয়বস্তু সূচী</h2>
                    <ol class="list-decimal list-inside space-y-2">
                        @foreach($articles as $article)
                            <li><a href="#article-{{ $article->id }}" class="text-brand-600 hover:text-brand-700">{{ $article->title }}</a></li>
                        @endforeach
                    </ol>
                </div>
            @endif

            <!-- Articles -->
            @forelse($articles as $article)
                <article id="article-{{ $article->id }}" class="mb-16 pb-12 border-b last:border-b-0">
                    @if($article->featured_image)
                        <img src="{{ asset('storage/' . $article->featured_image) }}" alt="{{ $article->title }}" class="w-full rounded-lg mb-6">
                    @endif

                    <h2 class="text-3xl font-bold mb-4">{{ $article->title }}</h2>

                    @if($article->author)
                        <p class="text-slate-600 mb-6">লেখক: <strong>{{ $article->author->name }}</strong></p>
                    @endif

                    <div class="prose max-w-none text-slate-700">
                        {!! nl2br(e($article->content)) !!}
                    </div>
                </article>
            @empty
                <div class="text-center py-12">
                    <p class="text-slate-600">এই ম্যাগাজিনে কোন নিবন্ধ নেই</p>
                </div>
            @endforelse

            <!-- Navigation -->
            @if($articles->count())
                <div class="bg-slate-100 rounded-lg p-6 mt-12">
                    <a href="{{ route('webzine.index') }}" class="text-brand-600 hover:text-brand-700 font-semibold">← সব ম্যাগাজিন দেখুন</a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
