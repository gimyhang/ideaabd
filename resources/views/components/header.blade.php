<header class="bg-white shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <!-- Logo -->
            <div class="flex items-center">
                <a href="{{ url('/') }}" class="flex items-center gap-3">
                    <img src="{{ asset('images/logo.png') }}" alt="আইডিয়া প্রকাশন" class="h-10 w-auto rounded" />
                    <span class="font-semibold text-lg text-gray-800">আইডিয়া প্রকাশন</span>
                </a>
            </div>

            <!-- Desktop: Search + Nav -->
            <div class="hidden md:flex md:items-center md:gap-6 flex-1 mx-6">
                <form action="{{ route('search') ?? '#' }}" method="GET" class="flex w-full">
                    <label for="q" class="sr-only">বই খুঁজুন</label>
                    <div class="relative w-full">
                        <input id="q" name="q" type="search" placeholder="বইয়ের নাম, লেখক বা কিওয়ার্ড লিখুন..." 
                            class="block w-full rounded-full border border-gray-200 bg-gray-50 py-2 pl-4 pr-12 text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" />
                        <button type="submit" class="absolute right-1 top-1/2 -translate-y-1/2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-full px-3 py-1 text-sm">খুঁজুন</button>
                    </div>
                </form>

                <nav class="ml-6 flex items-center gap-4 text-sm">
                    <a href="#" class="text-gray-600 hover:text-indigo-600">নতুন আসলো</a>
                    <a href="#" class="text-gray-600 hover:text-indigo-600">বেস্টসেলার</a>
                    <a href="#" class="text-gray-600 hover:text-indigo-600">বিষয়ভিত্তিক</a>
                    <a href="#" class="text-gray-600 hover:text-indigo-600">অফার</a>
                </nav>
            </div>

            <!-- Actions: account/cart & mobile menu -->
            <div class="flex items-center gap-4">
                <div class="hidden md:flex md:items-center md:gap-4">
                    <a href="{{ route('login') ?? '#'}}" class="text-gray-700 hover:text-indigo-600 text-sm">সাইন ইন</a>
                    <a href="{{ route('register') ?? '#'}}" class="text-gray-700 hover:text-indigo-600 text-sm">রেজিস্টার</a>
                </div>

                <a href="{{ route('cart') ?? '#'}}" class="relative text-gray-700 hover:text-indigo-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4" />
                        <circle cx="10" cy="20" r="1" />
                        <circle cx="18" cy="20" r="1" />
                    </svg>
                    <!-- badge -->
                    <span class="absolute -top-2 -right-2 bg-red-600 text-white text-xs rounded-full px-1.5">0</span>
                </a>

                <!-- Mobile menu button -->
                <button id="mobile-menu-button" type="button" class="md:hidden inline-flex items-center justify-center p-2 rounded-md text-gray-700 hover:bg-gray-100 focus:outline-none" aria-expanded="false" aria-controls="mobile-menu">
                    <svg id="menu-open" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <svg id="menu-close" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile menu -->
    <div id="mobile-menu" class="md:hidden hidden border-t border-gray-100">
        <div class="px-4 py-3 space-y-2">
            <form action="{{ route('search') ?? '#' }}" method="GET" class="flex w-full">
                <input name="q" type="search" placeholder="বই খুঁজুন..." class="block w-full rounded-l-md border border-gray-200 bg-white py-2 px-3 text-sm focus:outline-none" />
                <button type="submit" class="bg-indigo-600 text-white px-4 rounded-r-md">খুঁজুন</button>
            </form>
            <a href="#" class="block text-gray-700 py-2">নতুন আসলো</a>
            <a href="#" class="block text-gray-700 py-2">বেস্টসেলার</a>
            <a href="#" class="block text-gray-700 py-2">বিষয়ভিত্তিক</a>
            <a href="#" class="block text-gray-700 py-2">অফার</a>
            <div class="pt-2 border-t border-gray-100">
                <a href="{{ route('login') ?? '#'}}" class="block py-2 text-gray-700">সাইন ইন</a>
                <a href="{{ route('register') ?? '#'}}" class="block py-2 text-gray-700">রেজিস্টার</a>
            </div>
        </div>
    </div>

    <script>
        (function(){
            const btn = document.getElementById('mobile-menu-button');
            const menu = document.getElementById('mobile-menu');
            const openIcon = document.getElementById('menu-open');
            const closeIcon = document.getElementById('menu-close');
            if (!btn) return;
            btn.addEventListener('click', function(){
                const isHidden = menu.classList.contains('hidden');
                menu.classList.toggle('hidden', !isHidden);
                openIcon.classList.toggle('hidden', !isHidden);
                closeIcon.classList.toggle('hidden', isHidden);
                btn.setAttribute('aria-expanded', String(isHidden));
            });
        })();
    </script>
</header>
