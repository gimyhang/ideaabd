@extends('layouts.app')

@section('title', $ebook->title ?? 'ই-বুক')

@section('content')
    <section class="px-6 py-10 mx-auto max-w-7xl">
        <div class="grid gap-8 lg:grid-cols-3">
            <!-- Cover & Actions -->
            <div class="lg:col-span-1">
                <div class="rounded-3xl bg-gradient-to-br from-purple-100 to-indigo-100 aspect-square flex items-center justify-center mb-6">
                    @if($ebook->cover_image)
                        <img src="{{ $ebook->cover_image }}" alt="{{ $ebook->title }}" class="w-full h-full object-cover rounded-3xl" />
                    @else
                        <div class="text-8xl">📕</div>
                    @endif
                </div>

                <div class="space-y-3">
                    <div class="rounded-3xl bg-white/90 p-6 shadow-lg ring-1 ring-slate-200">
                        <p class="text-3xl font-bold text-indigo-600">৳{{ $ebook->price }}</p>
                    </div>
                    <a href="{{ route('ebook.read', $ebook->slug) }}" class="w-full rounded-full bg-gradient-to-r from-indigo-600 to-sky-600 px-6 py-4 text-center font-semibold text-white shadow-lg hover:brightness-110 transition block">
                        📖 এখনই পড়ুন
                    </a>
                    <button class="w-full rounded-full border-2 border-slate-200 px-6 py-4 font-semibold text-slate-900 hover:bg-slate-50 transition">
                        🛒 কার্টে যোগ করুন
                    </button>
                </div>
            </div>

            <!-- Details -->
            <div class="lg:col-span-2">
                <h1 class="text-4xl font-bold text-slate-900 mb-2">{{ $ebook->title }}</h1>
                
                @if($ebook->author)
                    <p class="text-lg text-indigo-600 mb-6">লেখক: {{ $ebook->author->name }}</p>
                @endif

                <div class="rounded-3xl bg-white/90 p-6 shadow-lg ring-1 ring-slate-200 mb-6">
                    <h3 class="text-lg font-bold text-slate-900 mb-3">বিবরণ</h3>
                    <p class="text-slate-700 leading-relaxed">{{ $ebook->description }}</p>
                </div>

                @if($ebook->publisher)
                    <div class="rounded-3xl bg-white/90 p-6 shadow-lg ring-1 ring-slate-200">
                        <h3 class="text-lg font-bold text-slate-900 mb-3">প্রকাশক</h3>
                        <p class="text-slate-700">{{ $ebook->publisher->name }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Related Ebooks -->
        @if($relatedEbooks->count())
            <div class="mt-12">
                <h2 class="text-2xl font-bold text-slate-900 mb-6">অন্যান্য ই-বুক</h2>
                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                    @foreach($relatedEbooks as $related)
                        <a href="{{ route('ebook.show', $related->slug) }}" class="group rounded-3xl bg-white/90 overflow-hidden shadow-lg hover:shadow-2xl ring-1 ring-slate-200 transition">
                            <div class="aspect-square bg-gradient-to-br from-purple-100 to-indigo-100 flex items-center justify-center">
                                @if($related->cover_image)
                                    <img src="{{ $related->cover_image }}" alt="{{ $related->title }}" class="w-full h-full object-cover" />
                                @else
                                    <div class="text-6xl">📕</div>
                                @endif
                            </div>
                            <div class="p-4">
                                <h3 class="text-sm font-bold text-slate-900 line-clamp-2">{{ $related->title }}</h3>
                                <p class="text-xs text-slate-600 mt-2">৳{{ $related->price }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </section>
@endsection
