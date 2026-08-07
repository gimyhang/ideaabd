<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h2 class="text-lg font-semibold text-gray-800">বিষয়ভিত্তিক ক্যাটাগরি</h2>
    <div class="mt-4 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-4">
        @foreach($categories as $cat)
            <a href="{{ $cat->url ?? '#' }}" class="flex items-center gap-3 bg-white rounded-lg p-3 shadow-sm hover:shadow-md">
                <div class="w-10 h-10 bg-gray-100 rounded flex items-center justify-center text-indigo-600">
                    {{-- icon placeholder --}}
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 1.343-3 3v6h6v-6c0-1.657-1.343-3-3-3z" />
                    </svg>
                </div>
                <div class="text-sm text-gray-700">{{ $cat->name }}</div>
            </a>
        @endforeach
    </div>
</div>
