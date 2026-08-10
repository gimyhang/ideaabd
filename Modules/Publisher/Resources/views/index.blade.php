@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-slate-50">
    <div class="hero-panel py-16">
        <div class="container mx-auto px-4">
            <h1 class="text-4xl md:text-5xl font-bold text-white mb-4">প্রকাশনী</h1>
            <p class="text-lg text-slate-100">বাংলাদেশের শীর্ষস্থানীয় প্রকাশনী সমূহের সাথে আবিষ্কার করুন</p>
        </div>
    </div>

    <div class="container mx-auto px-4 py-12">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @forelse($publishers as $publisher)
                <div class="shop-card">
                    <div class="h-40 bg-gradient-to-br from-brand-500 to-accent flex items-center justify-center p-4">
                        <span class="text-3xl text-white font-bold text-center">{{ substr($publisher->name, 0, 3) }}</span>
                    </div>
                    <div class="p-4">
                        <h3 class="font-bold text-lg mb-2">{{ $publisher->name }}</h3>
                        <p class="text-sm text-slate-600 mb-4">{{ Str::limit($publisher->description, 80) }}</p>
                        @if($publisher->website)
                            <a href="{{ $publisher->website }}" target="_blank" class="text-sm text-blue-600 hover:underline">ওয়েবসাইট</a>
                        @endif
                        <a href="{{ route('publisher.show', $publisher->slug) }}" class="block text-brand-600 hover:text-brand-700 font-semibold mt-2">আরও দেখুন →</a>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-12">
                    <p class="text-slate-600">কোন প্রকাশনী পাওয়া যায়নি</p>
                </div>
            @endforelse
        </div>

        @if($publishers instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <div class="flex justify-center mt-12">
                {{ $publishers->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
