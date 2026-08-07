<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <div class="md:col-span-1">
            <div class="bg-white rounded-lg shadow p-4">
                <img src="{{ $book->image ?? asset('images/placeholder/book-detail.jpg') }}" alt="{{ $book->title ?? 'বই' }}" class="w-full h-auto object-cover rounded" />
            </div>
        </div>

        <div class="md:col-span-2">
            <h1 class="text-2xl font-extrabold text-gray-900">{{ $book->title ?? 'বইয়ের শিরোনাম' }}</h1>
            <p class="mt-2 text-sm text-gray-600">by <a href="#" class="text-indigo-600">{{ $book->author ?? 'লেখক' }}</a></p>

            <div class="mt-4 flex items-center gap-6">
                <div class="text-2xl font-bold text-indigo-600">{{ $book->price ?? '৳ 0.00' }}</div>
                @if(!empty($book->old_price))<div class="text-sm line-through text-gray-400">{{ $book->old_price }}</div>@endif
            </div>

            <div class="mt-6 text-gray-700 leading-relaxed">
                {!! nl2br(e($book->description ?? 'বইয়ের সংক্ষিপ্ত বিবরণ এখানে দেখানো হবে।')) !!}
            </div>

            <div class="mt-6 flex items-center gap-3">
                <form action="{{ route('cart.add', $book->id ?? '#') ?? '#' }}" method="POST">
                    @csrf
                    <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded font-semibold">কার্টে যোগ করুন</button>
                </form>
                <a href="#" class="text-sm text-gray-600">উপহার হিসেবে দিন</a>
            </div>

            <div class="mt-8">
                <h3 class="text-sm font-semibold text-gray-800">প্রকাশক ও বিবরণ</h3>
                <ul class="mt-2 text-sm text-gray-600 space-y-1">
                    <li>প্রকাশক: {{ $book->publisher ?? 'পাবলিশার' }}</li>
                    <li>প্রকাশ বছর: {{ $book->year ?? '—' }}</li>
                    <li>ভাষা: {{ $book->language ?? 'বাংলা' }}</li>
                    <li>পৃষ্ঠা: {{ $book->pages ?? '—' }}</li>
                </ul>
            </div>
        </div>
    </div>
</div>
