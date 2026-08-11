@extends('layouts.app')

@section('title', 'লেখক ডিরেক্টরি')

@section('content')
    <section class="px-6 py-10 mx-auto max-w-7xl">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-slate-900">📖 লেখক ডিরেক্টরি</h1>
            <p class="mt-2 text-slate-600">আইডিয়া প্রকাশনের সকল লেখক ও সাহিত্যিক</p>
        </div>

        <!-- Search & Filter -->
        <div class="mb-8 rounded-3xl bg-white/90 p-6 shadow-lg ring-1 ring-slate-200">
            <form class="flex flex-col md:flex-row gap-4">
                <input type="text" placeholder="লেখকের নাম খুঁজুন..." 
                    class="flex-1 rounded-full border border-slate-200 bg-slate-50 py-3 px-4 text-sm placeholder-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200" />
                <select class="rounded-full border border-slate-200 bg-slate-50 py-3 px-4 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                    <option>সমস্ত ক্যাটাগরি</option>
                    <option>উপন্যাস</option>
                    <option>কবিতা</option>
                    <option>গল্প</option>
                    <option>প্রবন্ধ</option>
                </select>
                <button type="submit" class="rounded-full bg-gradient-to-r from-indigo-600 to-sky-600 px-8 py-3 text-sm font-semibold text-white shadow-sm transition hover:brightness-110">খুঁজুন</button>
            </form>
        </div>

        <!-- Authors Grid -->
        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            @forelse($authors as $author)
                <a href="{{ route('authors.show', $author) }}" class="group rounded-3xl bg-white/90 overflow-hidden shadow-lg hover:shadow-2xl ring-1 ring-slate-200 transition">
                    <div class="aspect-square bg-gradient-to-br from-indigo-100 to-sky-100 flex items-center justify-center">
                        @if($author->image)
                            <img src="{{ $author->image }}" alt="{{ $author->name }}" class="w-full h-full object-cover" />
                        @else
                            <div class="text-6xl">👤</div>
                        @endif
                    </div>
                    <div class="p-6">
                        <h3 class="text-lg font-bold text-slate-900 group-hover:text-indigo-600">{{ $author->name }}</h3>
                        <p class="text-sm text-slate-600 mt-2">{{ \Illuminate\Support\Str::limit($author->bio, 100) }}</p>
                        <div class="mt-4 flex items-center gap-2 text-xs text-slate-500">
                            <span>📚 {{ $author->books_count ?? 0 }} বই</span>
                        </div>
                    </div>
                </a>
            @empty
                <div class="md:col-span-2 lg:col-span-3 rounded-3xl bg-slate-50 p-12 text-center">
                    <p class="text-slate-600 text-lg">এখনও কোনো লেখক যোগ করা হয়নি</p>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($authors instanceof \Illuminate\Pagination\Paginator)
            <div class="mt-8">
                {{ $authors->links() }}
            </div>
        @endif
    </section>
@endsection
