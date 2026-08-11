@extends('layouts.app')

@section('title', 'ই-বুক সংগ্রহ')

@section('content')
    <section class="px-6 py-10 mx-auto max-w-7xl">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-slate-900">📖 ই-বুক সংগ্রহ</h1>
            <p class="mt-2 text-slate-600">ডিজিটাল বই এবং ই-পাব ফরম্যাটে সাহিত্য পড়ুন</p>
        </div>

        <!-- Search & Filter -->
        <div class="mb-8 rounded-3xl bg-white/90 p-6 shadow-lg ring-1 ring-slate-200">
            <form class="flex flex-col md:flex-row gap-4">
                <input type="text" name="search" placeholder="ই-বুক খুঁজুন..." 
                    value="{{ request('search') }}"
                    class="flex-1 rounded-full border border-slate-200 bg-slate-50 py-3 px-4 text-sm placeholder-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200" />
                <select name="sort" class="rounded-full border border-slate-200 bg-slate-50 py-3 px-4 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                    <option value="">সর্বশেষ প্রথম</option>
                    <option value="price_low">কম মূল্য প্রথম</option>
                    <option value="price_high">বেশি মূল্য প্রথম</option>
                </select>
                <button type="submit" class="rounded-full bg-gradient-to-r from-indigo-600 to-sky-600 px-8 py-3 text-sm font-semibold text-white shadow-sm transition hover:brightness-110">খুঁজুন</button>
            </form>
        </div>

        <!-- Ebooks Grid -->
        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
            @forelse($ebooks as $ebook)
                <a href="{{ route('ebook.show', $ebook->slug) }}" class="group rounded-3xl bg-white/90 overflow-hidden shadow-lg hover:shadow-2xl ring-1 ring-slate-200 transition">
                    <div class="aspect-square bg-gradient-to-br from-purple-100 to-indigo-100 flex items-center justify-center">
                        @if($ebook->cover_image)
                            <img src="{{ $ebook->cover_image }}" alt="{{ $ebook->title }}" class="w-full h-full object-cover" />
                        @else
                            <div class="text-6xl">📕</div>
                        @endif
                    </div>
                    <div class="p-4">
                        <h3 class="text-sm font-bold text-slate-900 group-hover:text-indigo-600 line-clamp-2">{{ $ebook->title }}</h3>
                        @if($ebook->author)
                            <p class="text-xs text-slate-600 mt-1">{{ $ebook->author->name }}</p>
                        @endif
                        <div class="mt-3 flex items-center justify-between">
                            <span class="text-lg font-bold text-indigo-600">৳{{ $ebook->price }}</span>
                            <button class="px-3 py-1.5 rounded-full bg-indigo-600 text-white text-xs font-semibold hover:brightness-110 transition">পড়ুন</button>
                        </div>
                    </div>
                </a>
            @empty
                <div class="md:col-span-2 lg:col-span-4 rounded-3xl bg-slate-50 p-12 text-center">
                    <p class="text-slate-600 text-lg">এখনও কোনো ই-বুক যোগ করা হয়নি</p>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($ebooks instanceof \Illuminate\Pagination\Paginator)
            <div class="mt-8">
                {{ $ebooks->links() }}
            </div>
        @endif
    </section>
@endsection
