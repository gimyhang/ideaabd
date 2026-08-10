<header class="sticky top-0 z-40 border-b border-slate-200/80 bg-white/90 backdrop-blur-xl shadow-[0_8px_30px_rgba(15,23,42,0.05)]">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center justify-between">
            <!-- Logo -->
            <div class="flex items-center">
                <a href="{{ url('/') }}" class="flex items-center gap-3">
                    <img src="{{ asset('images/logo.png') }}" alt="আইডিয়া প্রকাশন" class="h-10 w-auto rounded" />
                    <span class="text-lg font-black tracking-tight text-slate-900">আইডিয়া প্রকাশন</span>
                </a>
            </div>

            <!-- Desktop: Search + Nav -->
            <div class="hidden md:flex md:items-center md:gap-6 flex-1 mx-6">
                @php $searchAction = \Illuminate\Support\Facades\Route::has('search') ? route('search') : '#'; @endphp
                <form action="{{ $searchAction }}" method="GET" class="flex w-full">
                    <label for="q" class="sr-only">বই খুঁজুন</label>
                    <div class="relative w-full">
                        <input id="q" name="q" type="search" placeholder="বইয়ের নাম, লেখক বা কিওয়ার্ড লিখুন..." 
                            class="block w-full rounded-full border border-slate-200 bg-slate-50 py-2.5 pl-4 pr-14 text-sm text-slate-700 placeholder-slate-400 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200" />
                        <button type="submit" class="absolute right-1 top-1/2 -translate-y-1/2 rounded-full bg-gradient-to-r from-indigo-600 to-sky-600 px-3 py-1.5 text-sm font-semibold text-white shadow-sm transition hover:brightness-110">খুঁজুন</button>
                    </div>
                </form>

                <nav class="ml-6 flex items-center gap-4 text-sm">
                    <a href="#" class="text-gray-600 hover:text-indigo-600">নতুন আসলো</a>
                    <a href="#" class="text-gray-600 hover:text-indigo-600">বেস্টসেলার</a>

                    <!-- Categories dropdown -->
                    <div class="relative" data-dropdown>
                        <button id="categories-button" aria-expanded="false" aria-haspopup="true" class="flex items-center gap-2 text-gray-600 hover:text-indigo-600 focus:outline-none">
                            বিষয়ভিত্তিক
                            <svg class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>

                        <div id="categories-panel" class="dropdown-panel hidden absolute left-0 mt-2 w-56 z-50" role="menu" aria-labelledby="categories-button">
                            <a href="#" role="menuitem" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">বিকাশ</a>
                            <a href="#" role="menuitem" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">শিক্ষা</a>
                            <a href="#" role="menuitem" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">সাহিত্য</a>
                            <a href="#" role="menuitem" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">শিশু/কিশোর</a>
                            <a href="#" role="menuitem" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">সাইন্স</a>
                        </div>
                    </div>

                    <a href="#" class="text-gray-600 hover:text-indigo-600">অফার</a>
                </nav>
            </div>

            <!-- Actions: account/cart & mobile menu -->
            <div class="flex items-center gap-4">
                <div class="hidden md:flex md:items-center md:gap-4">
                    @php $loginAction = \Illuminate\Support\Facades\Route::has('login') ? route('login') : '#'; $registerAction = \Illuminate\Support\Facades\Route::has('register') ? route('register') : '#'; @endphp
                    <a href="{{ $loginAction }}" class="text-gray-700 hover:text-indigo-600 text-sm">সাইন ইন</a>
                    <a href="{{ $registerAction }}" class="text-gray-700 hover:text-indigo-600 text-sm">রেজিস্টার</a>
                </div>

                <button type="button" id="open-cart-drawer" class="relative text-gray-700 hover:text-indigo-600" aria-label="Open Cart">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4" />
                        <circle cx="10" cy="20" r="1" />
                        <circle cx="18" cy="20" r="1" />
                    </svg>
                    <!-- badge -->
                    <span id="cart-count" class="absolute -top-2 -right-2 bg-red-600 text-white text-xs rounded-full px-1.5">0</span>
                </button>

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
            @php $searchAction = \Illuminate\Support\Facades\Route::has('search') ? route('search') : '#'; @endphp
            <form action="{{ $searchAction }}" method="GET" class="flex w-full">
                <input name="q" type="search" placeholder="বই খুঁজুন..." class="block w-full rounded-l-md border border-gray-200 bg-white py-2 px-3 text-sm focus:outline-none" />
                <button type="submit" class="bg-indigo-600 text-white px-4 rounded-r-md">খুঁজুন</button>
            </form>
            <a href="#" class="block text-gray-700 py-2">নতুন আসলো</a>
            <a href="#" class="block text-gray-700 py-2">বেস্টসেলার</a>

            <div>
                <button type="button" class="w-full flex items-center justify-between text-gray-700 py-2" data-mobile-toggle aria-expanded="false">বিষয়ভিত্তিক
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div class="mt-1 hidden pl-4" data-mobile-panel>
                    <a href="#" class="block text-gray-700 py-2">বিকাশ</a>
                    <a href="#" class="block text-gray-700 py-2">শিক্ষা</a>
                    <a href="#" class="block text-gray-700 py-2">সাহিত্য</a>
                </div>
            </div>

            <a href="#" class="block text-gray-700 py-2">অফার</a>
            <div class="pt-2 border-t border-gray-100">
                <a href="{{ $loginAction ?? (\Illuminate\Support\Facades\Route::has('login') ? route('login') : '#') }}" class="block py-2 text-gray-700">সাইন ইন</a>
                <a href="{{ $registerAction ?? (\Illuminate\Support\Facades\Route::has('register') ? route('register') : '#') }}" class="block py-2 text-gray-700">রেজিস্টার</a>
            </div>
        </div>
    </div>

    <script>
        (function(){
            // Mobile menu toggle
            const btn = document.getElementById('mobile-menu-button');
            const menu = document.getElementById('mobile-menu');
            const openIcon = document.getElementById('menu-open');
            const closeIcon = document.getElementById('menu-close');
            if (btn) {
                btn.addEventListener('click', function(){
                    const isHidden = menu.classList.contains('hidden');
                    menu.classList.toggle('hidden', !isHidden);
                    openIcon.classList.toggle('hidden', !isHidden);
                    closeIcon.classList.toggle('hidden', isHidden);
                    btn.setAttribute('aria-expanded', String(isHidden));
                });
            }

            // Desktop dropdown (hover on capable devices, click/tap for touch)
            const dropdown = document.querySelector('[data-dropdown]');
            if (dropdown) {
                const toggle = dropdown.querySelector('button');
                const panel = dropdown.querySelector('.dropdown-panel');
                let open = false;

                const openMenu = () => {
                    panel.classList.remove('hidden');
                    panel.classList.add('show');
                    toggle.setAttribute('aria-expanded', 'true');
                    open = true;
                };
                const closeMenu = () => {
                    panel.classList.add('hidden');
                    panel.classList.remove('show');
                    toggle.setAttribute('aria-expanded', 'false');
                    open = false;
                };

                // Hover only on devices that support hover
                dropdown.addEventListener('mouseenter', function(){
                    if (window.matchMedia && window.matchMedia('(hover: hover)').matches) {
                        openMenu();
                    }
                });
                dropdown.addEventListener('mouseleave', function(){
                    if (window.matchMedia && window.matchMedia('(hover: hover)').matches) {
                        closeMenu();
                    }
                });

                // Click/tap toggles (for touch)
                toggle.addEventListener('click', function(e){
                    e.preventDefault();
                    open ? closeMenu() : openMenu();
                });

                // Keyboard support
                toggle.addEventListener('keydown', function(e){
                    if (e.key === 'ArrowDown' || e.key === 'Down') {
                        e.preventDefault();
                        openMenu();
                        const first = panel.querySelector('[role="menuitem"]');
                        if (first) first.focus();
                    }
                    if (e.key === 'Escape' || e.key === 'Esc') {
                        closeMenu();
                        toggle.focus();
                    }
                });

                const items = panel.querySelectorAll('[role="menuitem"]');
                items.forEach((item, idx) => {
                    // make programmatically focusable
                    item.setAttribute('tabindex', '-1');
                    item.addEventListener('keydown', function(e){
                        if (e.key === 'ArrowDown' || e.key === 'Down') {
                            e.preventDefault();
                            const next = items[(idx + 1) % items.length];
                            next.focus();
                        } else if (e.key === 'ArrowUp' || e.key === 'Up') {
                            e.preventDefault();
                            const prev = items[(idx - 1 + items.length) % items.length];
                            prev.focus();
                        } else if (e.key === 'Escape' || e.key === 'Esc') {
                            closeMenu();
                            toggle.focus();
                        }
                    });
                });

                // Close when clicking outside
                document.addEventListener('click', function(e){
                    if (open && !dropdown.contains(e.target)) {
                        closeMenu();
                    }
                });
            }

            // Mobile submenu accordion toggles
            document.querySelectorAll('[data-mobile-toggle]').forEach(function(t){
                t.addEventListener('click', function(){
                    const panel = t.nextElementSibling;
                    if (!panel) return;
                    const isHidden = panel.classList.contains('hidden');
                    panel.classList.toggle('hidden', !isHidden);
                    t.setAttribute('aria-expanded', String(!panel.classList.contains('hidden')));
                });
            });

            // Open cart drawer button event (dispatches custom event listened by cart-drawer component)
            const openCartBtn = document.getElementById('open-cart-drawer');
            if (openCartBtn){
                openCartBtn.addEventListener('click', function(e){
                    e.preventDefault();
                    document.dispatchEvent(new CustomEvent('openCartDrawer'));
                });
            }
        })();
    </script>
</header>
