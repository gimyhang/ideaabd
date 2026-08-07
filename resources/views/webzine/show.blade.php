@include('components.header')

<main class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <article class="bg-white rounded-lg shadow-sm p-6">
        <header class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-extrabold">{{ $article->title ?? 'আর্টিকেলের শিরোনাম' }}</h1>
                    <p class="mt-2 text-sm text-gray-500">{{ $article->published_at ? $article->published_at->format('j M, Y') : $article->date ?? '' }} · লেখক: <a href="#" class="text-indigo-600">{{ $article->author ?? 'লেখক' }}</a></p>
                </div>
                <div class="flex items-center gap-3">
                    <button id="share-btn" class="text-sm px-3 py-2 border rounded">শেয়ার</button>
                    <a href="#subscribe" class="text-sm bg-indigo-600 text-white px-3 py-2 rounded">সাবস্ক্রাইব</a>
                </div>
            </div>
        </header>

        <div class="prose lg:prose-xl max-w-none text-gray-800">
            @if(!empty($article->image))
                <img src="{{ $article->image }}" alt="" class="w-full h-auto rounded mb-6" />
            @endif

            {!! $article->content ?? '<p>আর্টিকেল এর মূল কনটেন্ট এখানে দেখানো হবে।</p>' !!}
        </div>

        <footer class="mt-10 border-t pt-6">
            <div class="flex items-center gap-4">
                <img src="{{ $article->author_image ?? asset('images/placeholder/author.jpg') }}" alt="" class="w-12 h-12 object-cover rounded-full" />
                <div>
                    <div class="text-sm font-semibold">{{ $article->author ?? 'লেখক' }}</div>
                    <div class="text-xs text-gray-500">{{ Str::limit($article->author_bio ?? 'লেখকের সংক্ষিপ্ত জীবনী...', 140) }}</div>
                </div>
            </div>
        </footer>
    </article>

    <section class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
        <aside class="md:col-span-1">
            <div class="bg-white p-4 rounded-lg shadow-sm">
                <h3 class="text-sm font-semibold">সম্পাদকীয় পিক</h3>
                <ul class="mt-2 text-sm space-y-2">
                    @foreach($editorsPicks as $pick)
                        <li><a href="{{ $pick->url ?? '#' }}" class="text-gray-700 hover:text-indigo-600">{{ Str::limit($pick->title, 70) }}</a></li>
                    @endforeach
                </ul>
            </div>
        </aside>

        <div class="md:col-span-2">
            <h3 class="text-lg font-semibold">আরো পড়ুন</h3>
            <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach($related as $r)
                    @include('components.webzine-article-card', ['article' => $r])
                @endforeach
            </div>
        </div>
    </section>
</main>

@include('layouts.footer')

<script>
    (function(){
        const shareBtn = document.getElementById('share-btn');
        if (!shareBtn) return;
        shareBtn.addEventListener('click', function(){
            if (navigator.share) {
                navigator.share({
                    title: document.title,
                    url: window.location.href
                }).catch(()=>{});
            } else {
                // fallback: copy URL
                navigator.clipboard.writeText(window.location.href).then(()=>{
                    alert('লিংক কপি হয়েছে');
                });
            }
        });
    })();
</script>
