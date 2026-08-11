@extends('layouts.app')

@section('title', 'গবেষণা ও নিবন্ধ')

@section('content')
    <section class="px-6 py-10 mx-auto max-w-7xl">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-slate-900">🔬 গবেষণা ও নিবন্ধ</h1>
            <p class="mt-2 text-slate-600">একাডেমিক গবেষণা, নিবন্ধ এবং বৈজ্ঞানিক পর্যালোচনা</p>
        </div>

        <!-- Search -->
        <div class="mb-8 rounded-3xl bg-white/90 p-6 shadow-lg ring-1 ring-slate-200">
            <form class="flex gap-4">
                <input type="text" placeholder="গবেষণা খুঁজুন..." 
                    class="flex-1 rounded-full border border-slate-200 bg-slate-50 py-3 px-4 text-sm placeholder-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200" />
                <button type="submit" class="rounded-full bg-gradient-to-r from-indigo-600 to-sky-600 px-8 py-3 text-sm font-semibold text-white shadow-sm transition hover:brightness-110">খুঁজুন</button>
            </form>
        </div>

        <!-- Papers List -->
        <div class="space-y-6">
            @forelse($papers as $paper)
                <a href="{{ route('research.show', $paper) }}" class="group rounded-3xl bg-white/90 p-6 shadow-lg hover:shadow-2xl ring-1 ring-slate-200 transition">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0 w-12 h-12 rounded-full bg-indigo-100 flex items-center justify-center">
                            <span class="text-xl">📄</span>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-bold text-slate-900 group-hover:text-indigo-600">{{ $paper->title }}</h3>
                            <p class="text-sm text-slate-600 mt-2">{{ \Illuminate\Support\Str::limit($paper->abstract, 200) }}</p>
                            <div class="mt-4 flex flex-wrap gap-2 text-xs text-slate-500">
                                @if($paper->author)
                                    <span>✍️ {{ $paper->author->name }}</span>
                                @endif
                                <span>📅 {{ $paper->published_at?->format('d M Y') ?? 'অজানা' }}</span>
                            </div>
                        </div>
                    </div>
                </a>
            @empty
                <div class="rounded-3xl bg-slate-50 p-12 text-center">
                    <p class="text-slate-600 text-lg">এখনও কোনো গবেষণা যোগ করা হয়নি</p>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($papers instanceof \Illuminate\Pagination\Paginator)
            <div class="mt-8">
                {{ $papers->links() }}
            </div>
        @endif
    </section>
@endsection
