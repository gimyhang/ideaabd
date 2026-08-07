@include('components.header')

@include('components.webzine-hero', [
    'issueTitle' => $issue->title ?? 'প্রজন্ম ওয়েবজিন — চলমান ইস্যু',
    'issueSubtitle' => $issue->subtitle ?? 'কভার স্টোরি, সাক্ষাৎকার ও সাহিত্য',
    'coverImage' => $issue->cover ?? asset('images/placeholder/issue-cover.jpg'),
    'issueUrl' => $issue->url ?? '#',
])

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main column -->
        <section class="lg:col-span-2">
            <h2 class="text-xl font-semibold">সম্পাদকীয় পিক</h2>
            <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-6">
                @foreach($featured as $article)
                    @include('components.webzine-article-card', ['article' => $article])
                @endforeach
            </div>

            <h2 class="mt-8 text-xl font-semibold">সর্বশেষ আর্টিকেল</h2>
            <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($articles as $article)
                    @include('components.webzine-article-card', ['article' => $article])
                @endforeach
            </div>

            <div class="mt-8 flex justify-center">
                <a href="{{ route('webzine.archive') ?? '#archive' }}" class="px-4 py-2 border rounded hover:bg-gray-50">আরও দেখুন</a>
            </div>
        </section>

        <!-- Sidebar -->
        <aside class="space-y-6">
            <div class="bg-white p-4 rounded-lg shadow-sm">
                <h3 class="text-sm font-semibold">সম্পাদকীয়</h3>
                <p class="mt-2 text-sm text-gray-600">সম্পাদকীয় ব্লক — প্ল্যাটফর্মের ভিশন, কারিকুলাম বা আজকের সম্পাদকীয়।</p>
            </div>

            <div class="bg-white p-4 rounded-lg shadow-sm">
                <h3 class="text-sm font-semibold">ইডিটরস পিক</h3>
                <ul class="mt-2 space-y-2 text-sm">
                    @foreach($editorsPicks as $pick)
                        <li><a href="{{ $pick->url ?? '#' }}" class="text-gray-700 hover:text-indigo-600">{{ Str::limit($pick->title, 60) }}</a></li>
                    @endforeach
                </ul>
            </div>

            <div class="bg-white p-4 rounded-lg shadow-sm">
                <h3 class="text-sm font-semibold">সাবস্ক্রাইব</h3>
                <form action="{{ route('newsletter.subscribe') ?? '#' }}" method="POST" class="mt-2">
                    @csrf
                    <input name="email" type="email" placeholder="আপনার ইমেইল" class="w-full border rounded px-3 py-2 text-sm" required />
                    <button class="mt-2 w-full bg-indigo-600 text-white px-3 py-2 rounded">সাবস্ক্রাইব</button>
                </form>
            </div>

            <div class="bg-white p-4 rounded-lg shadow-sm">
                <h3 class="text-sm font-semibold">ইস্যু আর্কাইভ</h3>
                <ul class="mt-2 text-sm space-y-2">
                    @foreach($issues as $iss)
                        <li><a href="{{ $iss->url ?? '#' }}" class="text-gray-700 hover:text-indigo-600">{{ $iss->title }}</a></li>
                    @endforeach
                </ul>
            </div>
        </aside>
    </div>
</main>

@include('layouts.footer')
