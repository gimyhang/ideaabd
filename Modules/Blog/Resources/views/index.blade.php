@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-slate-50">
    <!-- Hero Section -->
    <div class="hero-panel py-16">
        <div class="container mx-auto px-4">
            <h1 class="text-4xl md:text-5xl font-bold text-white mb-4">আমাদের ব্লগ</h1>
            <p class="text-lg text-slate-100">লেখক এবং গবেষকদের থেকে অনুপ্রেরণামূলক গল্প এবং অন্তর্দৃষ্টি পড়ুন</p>
        </div>
    </div>

    <div class="container mx-auto px-4 py-12">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <!-- Search -->
                <div class="mb-8">
                    <form action="{{ route('blog.index') }}" method="GET">
                        <input type="text" name="search" placeholder="ব্লগ খুঁজুন..." class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </form>
                </div>

                <!-- Categories -->
                <div class="mb-8">
                    <h3 class="text-lg font-bold mb-4">বিভাগসমূহ</h3>
                    <div class="space-y-2">
                        @forelse($categories as $category)
                            <a href="{{ route('blog.category', $category->slug) }}" class="block px-4 py-2 hover:bg-brand-50 rounded-lg text-slate-700 hover:text-brand-600">
                                {{ $category->name }}
                            </a>
                        @empty
                            <p class="text-slate-500">কোন বিভাগ নেই</p>
                        @endforelse
                    </div>
                </div>

                <!-- Featured Posts -->
                @if($featured->count())
                <div>
                    <h3 class="text-lg font-bold mb-4">সুপারিশকৃত পোস্ট</h3>
                    <div class="space-y-4">
                        @foreach($featured as $post)
                            <a href="{{ route('blog.show', $post->slug) }}" class="block p-4 bg-white rounded-lg hover:shadow-lg transition-shadow">
                                <h4 class="font-semibold text-slate-800 mb-2">{{ $post->title }}</h4>
                                <p class="text-sm text-slate-600">{{ $post->category->name ?? 'অন্যান্য' }}</p>
                            </a>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            <!-- Main Content -->
            <div class="lg:col-span-3">
                <!-- Posts Grid -->
                @if($posts->count())
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-12">
                        @foreach($posts as $post)
                            <article class="shop-card overflow-hidden">
                                @if($post->featured_image)
                                    <img src="{{ asset('storage/' . $post->featured_image) }}" alt="{{ $post->title }}" class="w-full h-48 object-cover">
                                @else
                                    <div class="w-full h-48 bg-gradient-to-br from-brand-400 to-brand-600 flex items-center justify-center">
                                        <span class="text-white text-2xl">📚</span>
                                    </div>
                                @endif
                                <div class="p-6">
                                    <h2 class="text-xl font-bold mb-2">{{ $post->title }}</h2>
                                    <p class="text-slate-600 mb-4">{{ $post->excerpt ?? Str::limit($post->content, 100) }}</p>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-slate-500">{{ $post->published_at->format('d M Y') }}</span>
                                        <a href="{{ route('blog.show', $post->slug) }}" class="text-brand-600 hover:text-brand-700 font-semibold">পড়ুন →</a>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    <div class="flex justify-center">
                        {{ $posts->links() }}
                    </div>
                @else
                    <div class="bg-white rounded-lg p-12 text-center">
                        <p class="text-slate-600 mb-4">কোন পোস্ট পাওয়া যায়নি</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
