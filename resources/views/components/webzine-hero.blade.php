<section class="bg-gradient-to-r from-yellow-400 via-red-400 to-pink-500 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-24">
        <div class="lg:flex lg:items-center lg:justify-between">
            <div>
                <h2 class="text-3xl font-extrabold tracking-tight">{{ $issueTitle ?? 'বর্তমান ইস্যু' }}</h2>
                <p class="mt-2 max-w-xl">{{ $issueSubtitle ?? 'সাহিত্য, সম্পাদকীয় ও বিশেষ কভার স্টোরি—সব এক জায়গায়।' }}</p>

                <div class="mt-6 flex gap-3">
                    <a href="{{ $issueUrl ?? '#' }}" class="bg-white text-black px-4 py-2 rounded-md font-semibold">সম্পূর্ণ ইস্যু দেখুন</a>
                    <a href="#archive" class="bg-white/20 text-white px-4 py-2 rounded-md">ইস্যু আর্কাইভ</a>
                </div>
            </div>

            <div class="mt-8 lg:mt-0 lg:ml-8">
                <div class="w-56 h-72 bg-white/20 rounded-lg overflow-hidden shadow-lg">
                    <img src="{{ $coverImage ?? asset('images/placeholder/issue-cover.jpg') }}" alt="Issue cover" class="w-full h-full object-cover" />
                </div>
            </div>
        </div>
    </div>
</section>
