@extends('layouts.app')

@section('title', 'ওয়েবজিন ও ডিজিটাল পত্রিকা')

@section('content')
    <section class="px-6 py-10 mx-auto max-w-7xl">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-slate-900">📰 ওয়েবজিন ও ডিজিটাল পত্রিকা</h1>
            <p class="mt-2 text-slate-600">সাহিত্য এবং সংস্কৃতির ডিজিটাল সংকলন</p>
        </div>

        <!-- Search -->
        <div class="mb-8 rounded-3xl bg-white/90 p-6 shadow-lg ring-1 ring-slate-200">
            <form class="flex gap-4">
                <input type="text" name="search" placeholder="ওয়েবজিন খুঁজুন..." 
                    value="{{ request('search') }}"
                    class="flex-1 rounded-full border border-slate-200 bg-slate-50 py-3 px-4 text-sm placeholder-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200" />
                <button type="submit" class="rounded-full bg-gradient-to-r from-indigo-600 to-sky-600 px-8 py-3 text-sm font-semibold text-white shadow-sm transition hover:brightness-110">খুঁজুন</button>
            </form>
        </div>

        <!-- Webzines Grid -->
        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            @forelse($webzines as $webzine)
                <a href="{{ route('webzine.show', $webzine->slug) }}" class="group rounded-3xl bg-white/90 overflow-hidden shadow-lg hover:shadow-2xl ring-1 ring-slate-200 transition">
                    <div class="aspect-[2/3] bg-gradient-to-br from-orange-100 to-amber-100 flex items-center justify-center">
                        @if($webzine->cover_image)
                            <img src="{{ $webzine->cover_image }}" alt="{{ $webzine->title }}" class="w-full h-full object-cover" />
                        @else
                            <div class="text-6xl">📰</div>
                        @endif
                    </div>
                    <div class="p-4">
                        <h3 class="text-lg font-bold text-slate-900 group-hover:text-indigo-600">{{ $webzine->title }}</h3>
                        <p class="text-sm text-slate-600 mt-2">{{ $webzine->issue ?? 'সর্বশেষ ইস্যু' }}</p>
                        <div class="mt-4 pt-4 border-t border-slate-100">
                            <a href="{{ route('webzine.read', $webzine->slug) }}" class="inline-block px-4 py-2 rounded-full bg-indigo-600 text-white text-sm font-semibold hover:brightness-110 transition">
                                পড়ুন
                            </a>
                        </div>
                    </div>
                </a>
            @empty
                <div class="md:col-span-2 lg:col-span-3 rounded-3xl bg-slate-50 p-12 text-center">
                    <p class="text-slate-600 text-lg">এখনও কোনো ওয়েবজিন যোগ করা হয়নি</p>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($webzines instanceof \Illuminate\Pagination\Paginator)
            <div class="mt-8">
                {{ $webzines->links() }}
            </div>
        @endif
    </section>
@endsection
