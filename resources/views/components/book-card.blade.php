<article class="bg-white shadow-sm rounded-lg overflow-hidden">
    <a href="{{ $book->url ?? '#' }}" class="block">
        <div class="h-56 bg-gray-100">
            <img src="{{ $book->image ?? asset('images/placeholder/book-card.jpg') }}" alt="{{ $book->title ?? 'বই' }}" class="w-full h-full object-cover" />
        </div>
    </a>
    <div class="p-4">
        <h3 class="text-sm font-semibold text-gray-800"><a href="{{ $book->url ?? '#' }}">{{ Str::limit($book->title ?? 'বইয়ের শিরোনাম', 60) }}</a></h3>
        <p class="text-xs text-gray-500 mt-1">{{ $book->author ?? 'লেখক' }}</p>

        <div class="mt-3 flex items-center justify-between">
            <div>
                <div class="text-sm font-bold text-indigo-600">{{ $book->price ?? '৳ 0.00' }}</div>
                @if(!empty($book->old_price))<div class="text-xs line-through text-gray-400">{{ $book->old_price }}</div>@endif
            </div>
            <div class="flex items-center gap-2">
                <button class="bg-indigo-600 text-white px-3 py-1 rounded text-sm">কার্টে যোগ</button>
                <button class="border border-gray-200 px-2 py-1 rounded text-sm text-gray-600">বিস্তারিত</button>
            </div>
        </div>
    </div>
</article>
