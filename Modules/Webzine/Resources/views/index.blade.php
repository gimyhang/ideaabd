@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-slate-50">
    <div class="hero-panel py-16">
        <div class="container mx-auto px-4">
            <h1 class="text-4xl md:text-5xl font-bold text-white mb-4">ম্যাগাজিন ও ওয়েবজিন</h1>
            <p class="text-lg text-slate-100">ডিজিটাল ম্যাগাজিন এবং অনলাইন পাবলিকেশন পড়ুন</p>
        </div>
    </div>

    <div class="container mx-auto px-4 py-12">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @forelse($webzines as $webzine)
                <article class="shop-card">
                    @if($webzine->cover_image)
                        <img src="{{ asset('storage/' . $webzine->cover_image) }}" alt="{{ $webzine->title }}" class="w-full h-56 object-cover">
                    @else
                        <div class="w-full h-56 bg-gradient-to-br from-brand-500 to-accent flex items-center justify-center">
                            <span class="text-6xl">📰</span>
                        </div>
                    @endif
                    <div class="p-4">
                        <div class="mb-2">
                            <span class="text-xs bg-brand-100 text-brand-700 px-2 py-1 rounded">ইস্যু {{ $webzine->issue_number }}</span>
                        </div>
                        <h3 class="font-bold text-lg mb-2 line-clamp-2">{{ $webzine->title }}</h3>
                        <p class="text-sm text-slate-600 mb-2">{{ $webzine->publication_date?->format('d M Y') }}</p>
                        <p class="text-xs text-slate-500 mb-4">{{ $webzine->view_count }} বার পড়া হয়েছে</p>
                        <a href="{{ route('webzine.read', $webzine->slug) }}" class="text-brand-600 hover:text-brand-700 font-semibold">পড়ুন →</a>
                    </div>
                </article>
            @empty
                <div class="col-span-full text-center py-12">
                    <p class="text-slate-600">কোন ম্যাগাজিন পাওয়া যায়নি</p>
                </div>
            @endforelse
        </div>

        @if($webzines instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <div class="flex justify-center mt-12">
                {{ $webzines->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
