@extends('layouts.app')

@section('title', 'প্রকাশক ডিরেক্টরি')

@section('content')
    <section class="px-6 py-10 mx-auto max-w-7xl">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-slate-900">🏢 প্রকাশক ডিরেক্টরি</h1>
            <p class="mt-2 text-slate-600">আইডিয়া প্রকাশনের সকল প্রকাশক ও প্রকাশনী</p>
        </div>

        <!-- Grid -->
        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            @forelse($publishers as $publisher)
                <a href="{{ route('publishers.show', $publisher) }}" class="group rounded-3xl bg-white/90 overflow-hidden shadow-lg hover:shadow-2xl ring-1 ring-slate-200 transition p-6">
                    <h3 class="text-lg font-bold text-slate-900 group-hover:text-indigo-600">{{ $publisher->name }}</h3>
                    <p class="text-sm text-slate-600 mt-2">{{ \Illuminate\Support\Str::limit($publisher->bio, 100) }}</p>
                    <div class="mt-4 pt-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                        <span>📚 {{ $publisher->books_count ?? 0 }} বই</span>
                    </div>
                </a>
            @empty
                <div class="md:col-span-2 lg:col-span-3 rounded-3xl bg-slate-50 p-12 text-center">
                    <p class="text-slate-600 text-lg">এখনও কোনো প্রকাশক যোগ করা হয়নি</p>
                </div>
            @endforelse
        </div>
    </section>
@endsection
