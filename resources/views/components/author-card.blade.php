<div class="bg-white rounded-lg shadow-sm p-4 flex gap-4">
    <div class="w-24 h-24 flex-shrink-0">
        <img src="{{ $author->image ?? asset('images/placeholder/author.jpg') }}" alt="{{ $author->name ?? 'লেখক' }}" class="w-full h-full object-cover rounded" />
    </div>
    <div>
        <h4 class="text-sm font-semibold text-gray-800">{{ $author->name ?? 'লেখক' }}</h4>
        <p class="text-xs text-gray-500 mt-1">{{ Str::limit($author->bio ?? 'লেখকের সংক্ষিপ্ত পরিচিতি...', 140) }}</p>
        <div class="mt-3 text-sm">
            <a href="{{ route('author.show', $author->id ?? '#') ?? '#' }}" class="text-indigo-600">সব বই দেখুন</a>
        </div>
    </div>
</div>
