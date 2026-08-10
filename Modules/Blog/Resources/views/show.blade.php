@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-slate-50">
    <div class="container mx-auto px-4 py-12">
        <article class="max-w-3xl mx-auto">
            <!-- Post Header -->
            <header class="mb-8">
                <div class="mb-4">
                    <span class="inline-block px-3 py-1 bg-brand-100 text-brand-700 rounded-full text-sm font-semibold">
                        {{ $post->category->name ?? 'অন্যান্য' }}
                    </span>
                </div>
                <h1 class="text-4xl md:text-5xl font-bold text-slate-900 mb-4">{{ $post->title }}</h1>
                <div class="flex items-center justify-between text-slate-600">
                    <div>
                        <span class="font-semibold">{{ $post->author->name ?? 'অজানা লেখক' }}</span>
                        <span class="mx-2">•</span>
                        <span>{{ $post->published_at->format('d M Y') }}</span>
                    </div>
                    <span>{{ $post->view_count }} ভিউ</span>
                </div>
            </header>

            <!-- Featured Image -->
            @if($post->featured_image)
                <img src="{{ asset('storage/' . $post->featured_image) }}" alt="{{ $post->title }}" class="w-full rounded-lg mb-8">
            @endif

            <!-- Post Content -->
            <div class="bg-white rounded-lg p-8 mb-12 prose max-w-none">
                {!! nl2br(e($post->content)) !!}
            </div>

            <!-- Tags -->
            @if($post->tags->count())
                <div class="mb-12">
                    <h3 class="text-lg font-bold mb-4">ট্যাগসমূহ</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach($post->tags as $tag)
                            <a href="{{ route('blog.tag', $tag->slug) }}" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 rounded-full text-slate-700">
                                #{{ $tag->name }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Related Posts -->
            @if($related->count())
                <div class="border-t pt-12">
                    <h2 class="text-2xl font-bold mb-8">সম্পর্কিত পোস্ট</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        @foreach($related as $relatedPost)
                            <article class="shop-card">
                                @if($relatedPost->featured_image)
                                    <img src="{{ asset('storage/' . $relatedPost->featured_image) }}" alt="{{ $relatedPost->title }}" class="w-full h-40 object-cover">
                                @endif
                                <div class="p-4">
                                    <h3 class="font-bold mb-2">{{ $relatedPost->title }}</h3>
                                    <a href="{{ route('blog.show', $relatedPost->slug) }}" class="text-brand-600 hover:text-brand-700 font-semibold">পড়ুন →</a>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            @endif
        </article>
    </div>
</div>
@endsection
