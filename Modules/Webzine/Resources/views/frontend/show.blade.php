@extends('layouts.app')

@section('title', $webzine->title ?? 'ওয়েবজিন')

@section('content')
    <section class="px-6 py-10 mx-auto max-w-7xl">
        <div class="grid gap-8 lg:grid-cols-3">
            <!-- Cover & Actions -->
            <div class="lg:col-span-1">
                <div class="rounded-3xl bg-gradient-to-br from-orange-100 to-amber-100 aspect-[2/3] flex items-center justify-center mb-6">
                    @if($webzine->cover_image)
                        <img src="{{ $webzine->cover_image }}" alt="{{ $webzine->title }}" class="w-full h-full object-cover rounded-3xl" />
                    @else
                        <div class="text-8xl">📰</div>
                    @endif
                </div>

                <a href="{{ route('webzine.read', $webzine->slug) }}" class="w-full rounded-full bg-gradient-to-r from-indigo-600 to-sky-600 px-6 py-4 text-center font-semibold text-white shadow-lg hover:brightness-110 transition block">
                    📖 এখনই পড়ুন
                </a>
            </div>

            <!-- Details -->
            <div class="lg:col-span-2">
                <h1 class="text-4xl font-bold text-slate-900 mb-2">{{ $webzine->title }}</h1>
                
                <p class="text-lg text-indigo-600 mb-6">{{ $webzine->issue ?? 'ডিজিটাল সংকলন' }}</p>

                <div class="rounded-3xl bg-white/90 p-6 shadow-lg ring-1 ring-slate-200 mb-6">
                    <h3 class="text-lg font-bold text-slate-900 mb-3">বিবরণ</h3>
                    <p class="text-slate-700 leading-relaxed">{{ $webzine->description }}</p>
                </div>

                @if($webzine->publisher)
                    <div class="rounded-3xl bg-white/90 p-6 shadow-lg ring-1 ring-slate-200">
                        <h3 class="text-lg font-bold text-slate-900 mb-3">প্রকাশক</h3>
                        <p class="text-slate-700">{{ $webzine->publisher->name }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Related Webzines -->
        @if($relatedWebzines->count())
            <div class="mt-12">
                <h2 class="text-2xl font-bold text-slate-900 mb-6">অন্যান্য ওয়েবজিন</h2>
                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    @foreach($relatedWebzines as $related)
                        <a href="{{ route('webzine.show', $related->slug) }}" class="group rounded-3xl bg-white/90 overflow-hidden shadow-lg hover:shadow-2xl ring-1 ring-slate-200 transition">
                            <div class="aspect-[2/3] bg-gradient-to-br from-orange-100 to-amber-100 flex items-center justify-center">
                                @if($related->cover_image)
                                    <img src="{{ $related->cover_image }}" alt="{{ $related->title }}" class="w-full h-full object-cover" />
                                @else
                                    <div class="text-6xl">📰</div>
                                @endif
                            </div>
                            <div class="p-4">
                                <h3 class="text-sm font-bold text-slate-900">{{ $related->title }}</h3>
                                <p class="text-xs text-slate-600 mt-2">{{ $related->issue ?? 'ডিজিটাল সংকলন' }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </section>
@endsection
