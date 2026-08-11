@extends('layouts.app')

@section('title', 'লেখক ডিরেক্টরি')

@section('content')

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="flex flex-col lg:flex-row gap-8">
        <!-- Main list -->
        <section class="flex-1">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-extrabold text-gray-900">লেখক ডিরেক্টরি</h1>

                <form action="{{ route('authors.index') ?? '#' }}" method="GET" class="flex items-center gap-2">
                    <label for="q" class="sr-only">লেখক নাম খুঁজুন</label>
                    <input id="q" name="q" value="{{ request('q') }}" type="search" placeholder="লেখক নাম লিখুন..." class="border rounded px-3 py-2 text-sm" />
                    <button type="submit" class="bg-indigo-600 text-white px-3 py-2 rounded text-sm">খুঁজুন</button>
                </form>
            </div>

            <!-- Alphabet filter -->
            <div class="mt-4">
                <nav class="flex flex-wrap gap-2 text-sm">
                    <a href="{{ route('authors.index') }}" class="px-3 py-1 rounded {{ request('letter') ? 'text-gray-600' : 'bg-indigo-600 text-white' }}">সব</a>
                    @foreach(range('A','Z') as $char)
                        <a href="{{ route('authors.index', array_merge(request()->except('page'), ['letter' => $char])) }}" class="px-3 py-1 rounded hover:bg-gray-100 {{ request('letter') === $char ? 'bg-indigo-600 text-white' : 'text-gray-600' }}">{{ $char }}</a>
                    @endforeach
                </nav>
            </div>

            <!-- Sort / Filters -->
            <div class="mt-6 flex items-center justify-between">
                <div class="text-sm text-gray-500">{{ $authors->total() ?? 0 }} জন লেখক মিলে</div>
                <div class="flex items-center gap-3">
                    <form method="GET" action="{{ route('authors.index') ?? '#' }}" class="flex items-center gap-2">
                        <label for="sort" class="sr-only">Sort</label>
                        <select id="sort" name="sort" onchange="this.form.submit()" class="border rounded px-2 py-1 text-sm">
                            <option value="latest" {{ request('sort') === 'latest' ? 'selected' : '' }}>সর্বশেষ যোগকৃত</option>
                            <option value="popular" {{ request('sort') === 'popular' ? 'selected' : '' }}>জনপ্রিয়</option>
                            <option value="books_desc" {{ request('sort') === 'books_desc' ? 'selected' : '' }}>বইয়ের সংখ্যা (কম-বেশি)</option>
                        </select>
                        {{-- preserve q and letter in sort form --}}
                        <input type="hidden" name="q" value="{{ request('q') }}" />
                        <input type="hidden" name="letter" value="{{ request('letter') }}" />
                    </form>
                </div>
            </div>

            <!-- Authors grid -->
            <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @if($authors->count())
                    @foreach($authors as $author)
                        @include('components.author-card', ['author' => $author])
                    @endforeach
                @else
                    <div class="col-span-full bg-white p-6 rounded shadow text-gray-600">কোনো লেখক পাওয়া যায়নি।</div>
                @endif
            </div>

            <!-- Pagination -->
            <div class="mt-8">
                {{ $authors->withQueryString()->links() }}
            </div>
        </section>

        <!-- Sidebar -->
        <aside class="w-full lg:w-80 space-y-6">
            <div class="bg-white p-4 rounded-lg shadow-sm">
                <h3 class="text-sm font-semibold">ফিল্টার</h3>
                <div class="mt-3 text-sm text-gray-600 space-y-2">
                    <a href="{{ route('authors.index', array_merge(request()->except('page'), ['filter' => 'most_books'])) }}" class="block hover:text-indigo-600">সবচেয়ে বেশি বই</a>
                    <a href="{{ route('authors.index', array_merge(request()->except('page'), ['filter' => 'recent_active'])) }}" class="block hover:text-indigo-600">সাম্প্রতিকভাবে সক্রিয়</a>
                    <a href="{{ route('authors.index', array_merge(request()->except('page'), ['filter' => 'award_winners'])) }}" class="block hover:text-indigo-600">ইনঅয়ার্ড/অ্যাওয়ার্ড বিজয়ী</a>
                </div>
            </div>

            <div class="bg-white p-4 rounded-lg shadow-sm">
                <h3 class="text-sm font-semibold">টপ লেখক</h3>
                <ul class="mt-3 space-y-2 text-sm">
                    @foreach($topAuthors ?? [] as $a)
                        <li><a href="{{ route('authors.show', $a->id ?? '#') }}" class="text-gray-700 hover:text-indigo-600">{{ $a->name }}</a></li>
                    @endforeach
                </ul>
            </div>

            <div class="bg-white p-4 rounded-lg shadow-sm">
                <h3 class="text-sm font-semibold">সাইন আপ</h3>
                <p class="mt-2 text-sm text-gray-600">নতুন লেখক-আপডেট পেতে ইমেইল দিন।</p>
                <form action="{{ route('newsletter.subscribe') ?? '#' }}" method="POST" class="mt-3">
                    @csrf
                    <input name="email" type="email" placeholder="আপনার ইমেইল" class="w-full border rounded px-3 py-2 text-sm" required />
                    <button class="mt-2 w-full bg-indigo-600 text-white px-3 py-2 rounded">সাবস্ক্রাইব</button>
                </form>
            </div>
        </aside>
    </div>
</main>

@endsection

@push('scripts')
<script>
    // small helper: highlight active alphabet (optional if server-side handles it)
    (function(){
        const active = '{{ request('letter') }}';
        if (!active) return;
        const links = document.querySelectorAll('nav a');
        links.forEach(a => {
            if (a.textContent.trim() === active) {
                a.classList.add('bg-indigo-600','text-white');
            }
        });
    })();
</script>
@endpush
