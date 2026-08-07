<article class="bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-shadow">
    <a href="{{ $article->url ?? '#' }}" class="block">
        <div class="h-44 bg-gray-100">
            <img src="{{ $article->image ?? asset('images/placeholder/article.jpg') }}" alt="{{ $article->title ?? 'আর্টিকেল' }}" class="w-full h-full object-cover" />
        </div>
    </a>

    <div class="p-4">
        <div class="text-xs text-gray-500">{{ $article->published_at ? $article->published_at->format('j M, Y') : $article->date ?? '' }}</div>
        <h3 class="mt-1 text-sm font-semibold text-gray-800"><a href="{{ $article->url ?? '#' }}">{{ Str::limit($article->title ?? 'শিরোনাম', 80) }}</a></h3>
        <p class="mt-2 text-sm text-gray-600">{{ Str::limit($article->excerpt ?? 'সংক্ষিপ্ত সারাংশ...', 120) }}</p>
        <div class="mt-3 flex items-center justify-between">
            <div class="text-xs text-gray-500">লেখক: {{ $article->author ?? '—' }}</div>
            <a href="{{ $article->url ?? '#' }}" class="text-indigo-600 text-xs font-semibold">পড়ুন</a>
        </div>
    </div>
</article>
