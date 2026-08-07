<section class="bg-gradient-to-r from-indigo-600 to-indigo-500 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-24">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
            <div>
                <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight">পড়ার আনন্দ, এক ক্লিকে — আইডিয়া প্রকাশন</h1>
                <p class="mt-4 text-indigo-100 max-w-xl">নতুন রিলিজ, স্থানীয় লেখক, এক্সক্লুসিভ ই-বুকস ও বিশেষ ছাড় — আপনার পরবর্তী প্রিয় বইটি এখানে আছে।</p>

                <form action="{{ route('search') ?? '#' }}" method="GET" class="mt-6 flex max-w-md">
                    <label for="hero-q" class="sr-only">বই খুঁজুন</label>
                    <input id="hero-q" name="q" type="search" placeholder="বই/লেখক/শিরোনাম লিখুন" 
                        class="flex-1 rounded-l-md px-4 py-3 text-sm text-gray-800" />
                    <button type="submit" class="bg-white text-indigo-600 rounded-r-md px-4 py-3 font-semibold">খুঁজুন</button>
                </form>

                <div class="mt-6 flex gap-3">
                    <a href="#" class="bg-white/20 hover:bg-white/30 px-4 py-2 rounded text-sm">বেস্টসেলার দেখুন</a>
                    <a href="#" class="bg-white/10 hover:bg-white/20 px-4 py-2 rounded text-sm">শিশু ও কিশোর</a>
                </div>
            </div>

            <div class="hidden lg:block">
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-white/10 rounded-lg p-4">
                        <img src="{{ asset('images/placeholder/book-1.jpg') }}" alt="Sample" class="w-full h-48 object-cover rounded" />
                    </div>
                    <div class="bg-white/10 rounded-lg p-4">
                        <img src="{{ asset('images/placeholder/book-2.jpg') }}" alt="Sample" class="w-full h-48 object-cover rounded" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
