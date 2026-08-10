@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-slate-50">
    <div class="hero-panel py-16">
        <div class="container mx-auto px-4">
            <h1 class="text-4xl md:text-5xl font-bold text-white mb-4">আমাদের লেখকরা</h1>
            <p class="text-lg text-slate-100">বাংলাদেশের সেরা লেখক এবং চিন্তাবিদদের আবিষ্কার করুন</p>
            <a href="{{ route('author.register') }}" class="inline-block mt-6 px-6 py-3 bg-white text-brand-600 rounded-lg font-bold hover:bg-slate-100">লেখক হিসেবে যোগ দিন</a>
        </div>
    </div>

    <div class="container mx-auto px-4 py-12">
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @forelse($authors as $author)
                <div class="shop-card text-center">
                    <div class="w-24 h-24 mx-auto mb-4 bg-gradient-to-br from-brand-400 to-accent rounded-full flex items-center justify-center">
                        <span class="text-4xl">📝</span>
                    </div>
                    <h3 class="font-bold text-lg mb-2">{{ $author->name }}</h3>
                    <p class="text-sm text-slate-600 mb-4">{{ Str::limit($author->bio, 100) }}</p>
                    <a href="{{ route('author.show', $author->slug) }}" class="text-brand-600 hover:text-brand-700 font-semibold">লেখকের প্রোফাইল →</a>
                </div>
            @empty
                <div class="col-span-full text-center py-12">
                    <p class="text-slate-600">কোন লেখক পাওয়া যায়নি</p>
                </div>
            @endforelse
        </div>

        @if($authors instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <div class="flex justify-center mt-12">
                {{ $authors->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
