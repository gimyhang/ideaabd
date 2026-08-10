@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-slate-50">
    <div class="hero-panel py-16">
        <div class="container mx-auto px-4">
            <h1 class="text-4xl md:text-5xl font-bold text-white mb-4">গবেষণা ও প্রকাশনা</h1>
            <p class="text-lg text-slate-100">একাডেমিক জ্ঞান এবং গবেষণা পত্র আবিষ্কার করুন</p>
        </div>
    </div>

    <div class="container mx-auto px-4 py-12">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @forelse($papers as $paper)
                <article class="shop-card">
                    <div class="h-40 bg-gradient-to-br from-brand-400 to-brand-600 flex items-center justify-center">
                        <span class="text-4xl">📊</span>
                    </div>
                    <div class="p-4">
                        <h3 class="font-bold mb-2 line-clamp-2">{{ $paper->title }}</h3>
                        <p class="text-xs text-slate-600 mb-2">লেখক: {{ $paper->author->name ?? 'অজানা' }}</p>
                        <p class="text-xs text-slate-500 mb-3">{{ $paper->published_at->format('d M Y') }} | {{ $paper->view_count }} ভিউ</p>
                        <a href="{{ route('research.show', $paper->slug) }}" class="text-brand-600 hover:text-brand-700 font-semibold text-sm">পড়ুন →</a>
                    </div>
                </article>
            @empty
                <div class="col-span-full text-center py-12">
                    <p class="text-slate-600">কোন গবেষণা পত্র পাওয়া যায়নি</p>
                </div>
            @endforelse
        </div>

        @if($papers instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <div class="flex justify-center mt-12">
                {{ $papers->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
