@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-slate-50">
    <!-- Hero Section -->
    <div class="hero-panel py-20">
        <div class="container mx-auto px-4">
            <h1 class="text-5xl md:text-6xl font-bold text-white mb-4">বিশাল জ্ঞানের ভাণ্ডার</h1>
            <p class="text-xl text-slate-100 mb-8">আপনার প্রিয় লেখক, ব্লগার এবং গবেষকদের কাছ থেকে শিখুন</p>
        </div>
    </div>

    <!-- Quick Navigation -->
    <div class="bg-white shadow-lg -mt-12 relative z-10">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-2 md:grid-cols-6 gap-4 py-6">
                <a href="{{ route('book.index') }}" class="text-center p-4 hover:bg-slate-50 rounded-lg transition">
                    <span class="text-4xl mb-2 block">📚</span>
                    <span class="font-bold text-slate-700">বই</span>
                </a>
                <a href="{{ route('blog.index') }}" class="text-center p-4 hover:bg-slate-50 rounded-lg transition">
                    <span class="text-4xl mb-2 block">✍️</span>
                    <span class="font-bold text-slate-700">ব্লগ</span>
                </a>
                <a href="{{ route('authors.index') }}" class="text-center p-4 hover:bg-slate-50 rounded-lg transition">
                    <span class="text-4xl mb-2 block">👨‍✏️</span>
                    <span class="font-bold text-slate-700">লেখক</span>
                </a>
                <a href="{{ route('webzine.index') }}" class="text-center p-4 hover:bg-slate-50 rounded-lg transition">
                    <span class="text-4xl mb-2 block">📰</span>
                    <span class="font-bold text-slate-700">ম্যাগাজিন</span>
                </a>
                <a href="{{ route('research.index') }}" class="text-center p-4 hover:bg-slate-50 rounded-lg transition">
                    <span class="text-4xl mb-2 block">📊</span>
                    <span class="font-bold text-slate-700">গবেষণা</span>
                </a>
                <a href="{{ route('publisher.index') }}" class="text-center p-4 hover:bg-slate-50 rounded-lg transition">
                    <span class="text-4xl mb-2 block">🏢</span>
                    <span class="font-bold text-slate-700">প্রকাশনী</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Content Sections -->
    <div class="container mx-auto px-4 py-16">
        <!-- Featured Blog Posts -->
        <section class="mb-16">
            <h2 class="text-3xl font-bold mb-8">সর্বশেষ ব্লগ পোস্ট</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @php
                    $posts = \Modules\Blog\Models\BlogPost::published()->latest('published_at')->take(3)->get();
                @endphp
                @forelse($posts as $post)
                    <article class="shop-card">
                        <div class="h-40 bg-gradient-to-br from-brand-400 to-brand-600 flex items-center justify-center">
                            <span class="text-3xl">📝</span>
                        </div>
                        <div class="p-4">
                            <h3 class="font-bold mb-2 line-clamp-2">{{ $post->title }}</h3>
                            <p class="text-xs text-slate-600 mb-2">{{ $post->published_at->format('d M Y') }}</p>
                            <a href="{{ route('blog.show', $post->slug) }}" class="text-brand-600 hover:text-brand-700 font-semibold text-sm">পড়ুন →</a>
                        </div>
                    </article>
                @empty
                    <p class="text-slate-600">কোন পোস্ট পাওয়া যায়নি</p>
                @endforelse
            </div>
        </section>

        <!-- Featured Authors -->
        <section class="mb-16">
            <h2 class="text-3xl font-bold mb-8">বৈশিষ্ট্যযুক্ত লেখক</h2>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                @php
                    $authors = \Modules\Author\Models\Author::where('is_active', true)->where('is_verified', true)->take(4)->get();
                @endphp
                @forelse($authors as $author)
                    <div class="shop-card text-center">
                        <div class="w-32 h-32 mx-auto mb-4 bg-gradient-to-br from-brand-400 to-accent rounded-full flex items-center justify-center">
                            <span class="text-5xl">📝</span>
                        </div>
                        <h3 class="font-bold text-lg mb-2">{{ $author->name }}</h3>
                        <a href="{{ route('author.show', $author->slug) }}" class="text-brand-600 hover:text-brand-700 font-semibold">প্রোফাইল →</a>
                    </div>
                @empty
                    <p class="text-slate-600">কোন লেখক পাওয়া যায়নি</p>
                @endforelse
            </div>
        </section>

        <!-- CTA Section -->
        <section class="bg-gradient-to-r from-brand-600 to-accent rounded-lg p-12 text-white text-center mb-16">
            <h2 class="text-3xl font-bold mb-4">লেখক হিসেবে যোগ দিন</h2>
            <p class="text-lg mb-6">আপনার জ্ঞান এবং অভিজ্ঞতা লক্ষ লক্ষ পাঠকের সাথে শেয়ার করুন</p>
            <a href="{{ route('author.register') }}" class="inline-block px-8 py-3 bg-white text-brand-600 rounded-lg font-bold hover:bg-slate-100">এখনই যোগ দিন</a>
        </section>
    </div>
</div>
@endsection
