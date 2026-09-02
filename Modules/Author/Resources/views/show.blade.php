@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-slate-50">
    <div class="bg-gradient-to-r from-brand-600 to-accent py-12">
        <div class="container mx-auto px-4 text-white">
            <div class="flex flex-col md:flex-row gap-8">
                <div class="w-32 h-32 bg-white rounded-full flex items-center justify-center flex-shrink-0">
                    <span class="text-6xl">📝</span>
                </div>
                <div>
                    <h1 class="text-4xl font-bold mb-2">{{ $author->name }}</h1>
                    <p class="text-lg mb-4">{{ $author->bio }}</p>
                    <div class="flex gap-4 flex-wrap">
                        @if($author->is_verified)
                            <span class="text-sm bg-green-600 px-3 py-1 rounded">✓ যাচাইকৃত লেখক</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 py-12">
        <h2 class="text-3xl font-bold mb-8">লেখকের ব্লগ পোস্ট</h2>
        
        @if($posts->count())
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach($posts as $post)
                    <article class="shop-card">
                        <div class="h-40 bg-gradient-to-br from-brand-400 to-brand-600 flex items-center justify-center">
                            <span class="text-4xl">📄</span>
                        </div>
                        <div class="p-4">
                            <h3 class="font-bold mb-2">{{ $post->title }}</h3>
                            <p class="text-sm text-slate-600 mb-4">{{ Str::limit($post->excerpt, 80) }}</p>
                            <a href="{{ route('blog.show', $post->slug) }}" class="text-brand-600 hover:text-brand-700 font-semibold">পড়ুন →</a>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="flex justify-center mt-12">
                {{ $posts->links() }}
            </div>
        @else
            <div class="bg-white rounded-lg p-12 text-center">
                <p class="text-slate-600">এই লেখকের কোন পোস্ট নেই</p>
            </div>
        @endif
    </div>
</div>
@endsection
