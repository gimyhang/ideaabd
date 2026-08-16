@php
    /**
     * Public site header.
     * Uses Bootstrap's own dropdown JS + CSS hover effects in public/css/site.css.
     */
    $me = auth()->user();

    // Direct single navigation items (flat structure for fast & direct visitor access)
    $nav = [
        ['route' => 'home', 'label' => 'হোম', 'icon' => 'house', 'active' => 'home'],
        ['route' => 'book.index', 'label' => 'বইসমূহ', 'icon' => 'book', 'active' => ['book.*', 'shop.*']],
        ['route' => 'ebook.index', 'label' => 'ই-বুক', 'icon' => 'tablet-screen-button', 'active' => 'ebook.*'],
        ['route' => 'authors.index', 'label' => 'লেখক', 'icon' => 'pen-fancy', 'active' => 'authors.*'],
        ['route' => 'publishers.index', 'label' => 'প্রকাশক', 'icon' => 'building', 'active' => 'publishers.*'],
        ['route' => 'blog.index', 'label' => 'ব্লগ', 'icon' => 'blog', 'active' => 'blog.*'],
        ['route' => 'webzine.index', 'label' => 'ওয়েবজিন', 'icon' => 'newspaper', 'active' => 'webzine.*'],
        ['route' => 'research.index', 'label' => 'গবেষণা', 'icon' => 'flask', 'active' => 'research.*'],
        ['route' => 'hub', 'label' => 'আইডিয়া হাব', 'icon' => 'compass', 'active' => 'hub'],
        ['route' => 'about', 'label' => 'আমাদের সম্পর্কে', 'icon' => 'circle-info', 'active' => 'about'],
        ['route' => 'contact', 'label' => 'যোগাযোগ', 'icon' => 'envelope', 'active' => 'contact'],
    ];

    // Category mega-menu
    $megaCategories = collect();
    try {
        if (\Illuminate\Support\Facades\Schema::hasTable('categories')) {
            $megaCategories = \Illuminate\Support\Facades\DB::table('categories')
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->limit(10)
                ->get(['name', 'slug']);
        }
    } catch (\Throwable $e) {}

    /** Keeps a nav entry only when its route actually exists. */
    $usable = function (array $item) use (&$usable) {
        if (!empty($item['children'])) {
            $item['children'] = array_values(array_filter($item['children'], fn ($c) => Route::has($c['route'])));
            return $item['children'] ? $item : null;
        }
        return Route::has($item['route']) ? $item : null;
    };

    $nav = array_values(array_filter(array_map($usable, $nav)));
@endphp

<header class="site-head" id="siteHead">
    <div class="container">
        <div class="site-head__main">

            {{-- Brand Logo & Text --}}
            <a class="site-brand" href="{{ route('home') }}">
                @php 
                    $logoUrl = \App\Support\SiteSetting::logoUrl();
                    $siteName = \App\Support\SiteSetting::name();
                    $siteTagline = \App\Support\SiteSetting::tagline();
                @endphp
                <div class="site-brand__logo-box">
                    @if ($logoUrl)
                        <img src="{{ $logoUrl }}"
                             alt="{{ $siteName }}" 
                             class="site-brand__img"
                             onerror="this.onerror=null; this.src='{{ asset('images/logo.svg') }}';">
                    @else
                        <span class="site-brand__fallback">{{ config('brand.lettermark', 'আই') }}</span>
                    @endif
                </div>
                <div class="site-brand__text d-flex flex-column justify-content-center">
                    <span class="site-brand__name">{{ $siteName }}</span>
                    <span class="site-brand__sub">{{ $siteTagline }}</span>
                </div>
            </a>

            {{-- Enhanced Search Bar --}}
            <form class="site-search position-relative" method="GET" action="{{ route('search') }}" role="search">
                <div class="site-search__box shadow-sm">
                    <label for="siteSearchScope" class="visually-hidden">বিভাগ</label>
                    <select id="siteSearchScope" name="type" class="site-search__scope">
                        <option value="">সব বিভাগ</option>
                        <option value="book" @selected(request('type') === 'book')>বই</option>
                        <option value="ebook" @selected(request('type') === 'ebook')>ই-বুক</option>
                        <option value="author" @selected(request('type') === 'author')>লেখক</option>
                        <option value="publisher" @selected(request('type') === 'publisher')>প্রকাশক</option>
                    </select>

                    <label for="siteSearchInput" class="visually-hidden">খুঁজুন</label>
                    <input id="siteSearchInput" class="site-search__input" type="search" name="q"
                           value="{{ request('q') }}" placeholder="বই, লেখক, প্রকাশক বা বিষয় দিয়ে খুঁজুন..."
                           autocomplete="off">

                    <button class="site-search__btn" type="submit">
                        <i class="fas fa-magnifying-glass"></i><span class="d-none d-md-inline ms-1">খুঁজুন</span>
                    </button>
                </div>
                <!-- Live Search Results Dropdown -->
                <div id="liveSearchResults" class="dropdown-menu w-100 shadow-lg border-0 mt-2 d-none" style="position: absolute; top: 100%; left: 0; z-index: 1050; border-radius: 14px; max-height: 400px; overflow-y: auto;">
                    <div class="p-3 text-center text-muted" id="searchSpinner" style="display: none;">
                        <i class="fas fa-circle-notch fa-spin text-primary"></i> খুঁজছি...
                    </div>
                    <div id="searchResultsContent" class="p-2"></div>
                </div>
            </form>

            {{-- User Actions & Header Icons --}}
            <div class="site-actions">
                @if (Route::has('wishlist'))
                    <a href="{{ route('wishlist') }}" class="site-icon d-none d-sm-grid" title="পছন্দের তালিকা" aria-label="পছন্দের তালিকা">
                        <i class="fas fa-heart"></i>
                    </a>
                @endif

                <a href="{{ route('cart') }}" class="site-icon" title="কার্ট" aria-label="কার্ট" data-bs-toggle="offcanvas" data-bs-target="#siteCartDrawer" onclick="if(window.openCartDrawer){ window.openCartDrawer(); }">
                    <i class="fas fa-cart-shopping"></i>
                    <span class="site-icon__count" id="siteCartCount" hidden>০</span>
                </a>

                @auth
                    <div class="dropdown">
                        <button class="site-user dropdown-toggle border-0 shadow-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="padding: 0.35rem 0.9rem 0.35rem 0.35rem;">
                            <span class="site-user__avatar shadow-sm">{{ mb_substr($me->name, 0, 1) }}</span>
                            <span class="text-start d-none d-md-block ms-1">
                                <span class="site-user__name d-block text-dark">{{ Str::limit($me->name, 12) }}</span>
                            </span>
                        </button>

                        <ul class="dropdown-menu dropdown-menu-end site-drop border-0 shadow-lg p-2" style="width: 260px; border-radius: 16px;">
                            <li class="px-3 py-3 border-bottom mb-2 text-center bg-light" style="margin: -0.5rem -0.5rem 0.5rem -0.5rem; border-top-left-radius: 16px; border-top-right-radius: 16px;">
                                <div class="site-user__avatar mx-auto mb-2 shadow-sm" style="width: 52px; height: 52px; font-size: 1.4rem;">{{ mb_substr($me->name, 0, 1) }}</div>
                                <div class="fw-bold text-dark fs-6">{{ $me->name }}</div>
                                <div class="text-muted small">{{ $me->email }}</div>
                                <div class="badge bg-primary mt-2 px-3 py-1 rounded-pill small">
                                    {{ ['admin' => 'অ্যাডমিন', 'sub_admin' => 'সাব-অ্যাডমিন', 'seller' => 'সেলার',
                                        'publisher' => 'প্রকাশক', 'author' => 'লেখক'][$me->role] ?? 'গ্রাহক' }}
                                </div>
                            </li>

                            @if ($me->isAdmin() && Route::has('admin.dashboard'))
                                <li><a class="dropdown-item py-2" href="{{ route('admin.dashboard') }}"><i class="fas fa-gauge-high text-primary me-2"></i>অ্যাডমিন প্যানেল</a></li>
                            @elseif ($me->isSeller() && Route::has('subadmin.bills.index'))
                                <li><a class="dropdown-item py-2" href="{{ route('subadmin.bills.index') }}"><i class="fas fa-file-invoice-dollar text-success me-2"></i>সেলার প্যানেল</a></li>
                            @endif

                            @if (Route::has('wishlist'))
                                <li><a class="dropdown-item py-2" href="{{ route('wishlist') }}"><i class="fas fa-heart text-danger me-2"></i>পছন্দের তালিকা</a></li>
                            @endif
                            <li><a class="dropdown-item py-2" href="{{ route('cart') }}"><i class="fas fa-bag-shopping text-info me-2"></i>আমার কার্ট</a></li>
                            <li><a class="dropdown-item py-2" href="{{ route('my-account') }}"><i class="fas fa-user text-primary me-2"></i>আমার একাউন্ট</a></li>

                            <li><hr class="dropdown-divider my-2"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger py-2 fw-semibold bg-danger-subtle rounded text-center">
                                        <i class="fas fa-arrow-right-from-bracket me-2"></i>লগ আউট
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="site-btn-login d-none d-sm-inline-block">লগইন</a>
                    @if (Route::has('register.choose'))
                        <a href="{{ route('register.choose') }}" class="site-btn-join">রেজিস্ট্রেশন</a>
                    @endif
                @endauth

                <button class="site-burger" type="button" data-bs-toggle="offcanvas"
                        data-bs-target="#siteMobileNav" aria-controls="siteMobileNav" aria-label="মেনু">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>
    </div>

    {{-- Main Navbar with Submenu Dropdowns (Desktop) --}}
    <nav class="site-nav" aria-label="প্রধান মেনু">
        <div class="container">
            <ul class="site-nav__list">
                {{-- Categories mega menu --}}
                <li class="dropdown site-nav__item">
                    <button class="site-nav__link site-nav__all dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-grip"></i> সব বিভাগ
                    </button>
                    <div class="dropdown-menu site-drop site-mega border-0 shadow-lg p-4" style="border-radius: 16px;">
                        <div class="row g-4">
                            <div class="col-md-8">
                                <h6 class="text-uppercase fw-bold text-muted mb-3" style="font-size: 0.75rem; letter-spacing: 1px;">
                                    <i class="fas fa-list me-2 text-primary"></i>জনপ্রিয় ক্যাটাগরি
                                </h6>
                                @if ($megaCategories->isNotEmpty())
                                    <div class="site-mega__grid" style="grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0.5rem 1rem;">
                                        @foreach ($megaCategories as $cat)
                                            <a class="site-mega__item border border-light shadow-sm" href="{{ route('book.index', ['category' => $cat->slug]) }}">
                                                <span class="site-mega__icon bg-primary bg-opacity-10 text-primary"><i class="fas fa-book-open"></i></span>
                                                <span class="fw-semibold">{{ $cat->name }}</span>
                                            </a>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="site-mega__grid" style="grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0.5rem 1rem;">
                                        <a class="site-mega__item border border-light shadow-sm" href="{{ route('book.index') }}">
                                            <span class="site-mega__icon bg-primary bg-opacity-10 text-primary"><i class="fas fa-book"></i></span>
                                            <span class="fw-semibold">সকল বই</span>
                                        </a>
                                        <a class="site-mega__item border border-light shadow-sm" href="{{ route('ebook.index') }}">
                                            <span class="site-mega__icon bg-primary bg-opacity-10 text-primary"><i class="fas fa-tablet-screen-button"></i></span>
                                            <span class="fw-semibold">ই-বুক</span>
                                        </a>
                                    </div>
                                @endif
                            </div>
                            <div class="col-md-4 d-none d-md-block">
                                <div class="rounded-4 p-4 h-100 text-white d-flex flex-column justify-content-center shadow-sm position-relative overflow-hidden" 
                                     style="background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);">
                                    <div class="position-absolute top-0 end-0 opacity-20 p-3"><i class="fas fa-gift" style="font-size: 4.5rem;"></i></div>
                                    <span class="badge bg-warning text-dark fw-bold px-2 py-1 rounded-pill align-self-start mb-2 small shadow-sm">স্পেশাল ছাড়</span>
                                    <h5 class="fw-bold mb-2 position-relative" style="z-index: 1;">বইমেলা অফার</h5>
                                    <p class="small mb-3 opacity-90 position-relative" style="z-index: 1;">নির্বাচিত বইগুলোতে পাচ্ছেন সর্বোচ্চ ৪০% পর্যন্ত নিশ্চিত ছাড়।</p>
                                    <a href="{{ route('book.index', ['sort' => 'bestselling']) }}" class="btn btn-light btn-sm rounded-pill fw-bold text-primary align-self-start position-relative shadow-sm px-3" style="z-index: 1;">অফার দেখুন</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>

                {{-- Direct Single Navigation Items --}}
                @foreach ($nav as $item)
                    @php
                        $isActive = isset($item['active']) 
                            ? (is_array($item['active']) 
                                ? collect($item['active'])->contains(fn($pattern) => request()->routeIs($pattern))
                                : request()->routeIs($item['active']))
                            : request()->routeIs($item['route']);
                    @endphp
                    <li class="site-nav__item">
                        <a class="site-nav__link {{ $isActive ? 'is-active' : '' }}"
                           href="{{ route($item['route'], $item['params'] ?? []) }}">
                            <i class="fas fa-{{ $item['icon'] }} me-1 text-primary opacity-75"></i>
                            {{ $item['label'] }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </nav>
</header>

{{-- Mobile Navigation Offcanvas --}}
<div class="offcanvas offcanvas-start site-offcanvas" tabindex="-1" id="siteMobileNav" aria-labelledby="siteMobileNavLabel">
    <div class="offcanvas-header bg-light border-bottom">
        <h5 class="offcanvas-title d-flex align-items-center gap-2 fw-bold text-primary" id="siteMobileNavLabel">
            <x-brand-logo :size="30" />
            {{ config('brand.name', 'আইডিয়া প্রকাশন') }}
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="বন্ধ করুন"></button>
    </div>

    <div class="offcanvas-body p-3">
        @foreach ($nav as $item)
            @php
                $isActive = isset($item['active']) 
                    ? (is_array($item['active']) 
                        ? collect($item['active'])->contains(fn($pattern) => request()->routeIs($pattern))
                        : request()->routeIs($item['active']))
                    : request()->routeIs($item['route']);
            @endphp
            <a class="site-m-link {{ $isActive ? 'active fw-bold text-primary' : '' }}" href="{{ route($item['route'], $item['params'] ?? []) }}">
                <i class="fas fa-{{ $item['icon'] }} text-primary"></i>
                <span>{{ $item['label'] }}</span>
            </a>
        @endforeach

        <div class="d-grid gap-2 mt-4 pt-3 border-top">
            @auth
                @if ($me->isAdmin() && Route::has('admin.dashboard'))
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-primary rounded-pill fw-semibold">অ্যাডমিন প্যানেল</a>
                @elseif ($me->isSeller() && Route::has('subadmin.bills.index'))
                    <a href="{{ route('subadmin.bills.index') }}" class="btn btn-primary rounded-pill fw-semibold">সেলার প্যানেল</a>
                @endif
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn btn-outline-danger rounded-pill w-100 fw-semibold">লগ আউট</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="btn btn-outline-primary rounded-pill fw-semibold">লগইন</a>
                @if (Route::has('register.choose'))
                    <a href="{{ route('register.choose') }}" class="btn btn-primary rounded-pill fw-semibold">রেজিস্ট্রেশন</a>
                @endif
            @endauth
        </div>
    </div>
</div>

@once
    <script>
        // Header scroll effect & AJAX live search
        (function () {
            var head = document.getElementById('siteHead');
            if (!head) return;

            var onScroll = function () {
                head.classList.toggle('is-stuck', window.scrollY > 4);
            };

            onScroll();
            window.addEventListener('scroll', onScroll, { passive: true });

            // AJAX Live Search
            const searchInput = document.getElementById('siteSearchInput');
            const searchScope = document.getElementById('siteSearchScope');
            const searchResults = document.getElementById('liveSearchResults');
            const searchContent = document.getElementById('searchResultsContent');
            const searchSpinner = document.getElementById('searchSpinner');
            let debounceTimer;

            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    clearTimeout(debounceTimer);
                    const q = this.value.trim();
                    const type = searchScope ? searchScope.value : '';
                    
                    if (q.length < 2) {
                        searchResults.classList.add('d-none');
                        return;
                    }

                    searchResults.classList.remove('d-none');
                    searchContent.innerHTML = '';
                    searchSpinner.style.display = 'block';

                    debounceTimer = setTimeout(() => {
                        fetch(`{{ route('search') }}?q=${encodeURIComponent(q)}&type=${encodeURIComponent(type)}`, {
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        })
                        .then(res => res.text())
                        .then(html => {
                            searchSpinner.style.display = 'none';
                            searchContent.innerHTML = `
                                <div class="text-center p-3 border-bottom border-light">
                                    <span class="text-primary fw-bold">"${q}"</span> সম্পর্কিত ফলাফল
                                </div>
                                <div class="p-2">
                                    <a href="{{ route('search') }}?q=${encodeURIComponent(q)}&type=${encodeURIComponent(type)}" class="btn btn-primary btn-sm w-100 rounded-pill fw-semibold">সব রেজাল্ট দেখুন →</a>
                                </div>
                            `;
                        })
                        .catch(() => {
                            searchSpinner.style.display = 'none';
                            searchContent.innerHTML = '<div class="text-danger text-center p-2 small">অনুসন্ধানে কোনো ত্রুটি হয়েছে।</div>';
                        });
                    }, 400);
                });

                document.addEventListener('click', function(e) {
                    if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                        searchResults.classList.add('d-none');
                    }
                });
            }
        })();

        // Global Dynamic Cart Functions
        function updateHeaderCartBadge() {
            try {
                const cart = JSON.parse(localStorage.getItem('idea_cart') || '[]');
                const badge = document.getElementById('siteCartCount');
                if (badge) {
                    const totalQty = cart.reduce((sum, item) => sum + (item.quantity || item.qty || 1), 0);
                    if (totalQty > 0) {
                        badge.textContent = totalQty.toLocaleString('bn-BD');
                        badge.removeAttribute('hidden');
                        badge.classList.add('animate__animated', 'animate__bounceIn');
                    } else {
                        badge.setAttribute('hidden', 'true');
                    }
                }
            } catch(e) {}
        }

        window.updateHeaderCartBadge = updateHeaderCartBadge;

        window.addToCartLive = function(arg1, arg2, arg3, arg4, arg5) {
            try {
                let btn = null, id, title, price, image, qty = 1;

                // Support both (btn, id, title, price, image) and (id, title, price, image, [qty])
                if (arg1 && (arg1 instanceof HTMLElement || (typeof arg1 === 'object' && arg1.nodeType === 1))) {
                    btn = arg1;
                    id = arg2;
                    title = arg3;
                    price = arg4;
                    image = arg5;
                } else if (typeof arg1 === 'object' && arg1 !== null && !arg1.nodeType) {
                    id = arg1.id;
                    title = arg1.title;
                    price = arg1.price;
                    image = arg1.image;
                    qty = arg1.quantity || arg1.qty || 1;
                } else {
                    id = arg1;
                    title = arg2;
                    price = arg3;
                    image = arg4;
                    qty = (typeof arg5 === 'number') ? arg5 : (parseInt(document.getElementById('bookQuantity')?.value || 1) || 1);
                }

                if (!id) return;

                let cart = JSON.parse(localStorage.getItem('idea_cart') || '[]');
                const numPrice = Number(price) || 0;
                const existing = cart.find(item => item.id == id);
                
                if (existing) {
                    existing.quantity = (existing.quantity || existing.qty || 1) + qty;
                    existing.qty = existing.quantity;
                } else {
                    cart.push({ id, title, price: numPrice, image, quantity: qty, qty: qty });
                }

                localStorage.setItem('idea_cart', JSON.stringify(cart));
                updateHeaderCartBadge();

                if (typeof window.renderCartDrawer === 'function') {
                    window.renderCartDrawer();
                }

                // Button Visual Feedback
                if (btn) {
                    const originalHtml = btn.innerHTML;
                    btn.classList.remove('btn-outline-primary');
                    btn.classList.add('btn-success', 'text-white');
                    btn.innerHTML = '<i class="fa-solid fa-check"></i> যোগ হয়েছে';
                    setTimeout(() => {
                        btn.classList.remove('btn-success', 'text-white');
                        btn.classList.add('btn-outline-primary');
                        btn.innerHTML = originalHtml;
                    }, 1600);
                }

                // Interactive Toast Notification with View Cart Link
                const oldToast = document.getElementById('globalLiveCartToast');
                if (oldToast) oldToast.remove();

                const toast = document.createElement('div');
                toast.id = 'globalLiveCartToast';
                toast.className = 'position-fixed bottom-0 end-0 m-3 p-3 bg-dark text-white rounded-4 shadow-lg d-flex align-items-center justify-content-between gap-3 border border-secondary border-opacity-50';
                toast.style.zIndex = '99999';
                toast.style.fontSize = '0.85rem';
                toast.style.maxWidth = '380px';
                toast.innerHTML = `
                    <div class="d-flex align-items-center gap-2 text-truncate">
                        <i class="fa-solid fa-circle-check text-success fs-5 flex-shrink-0"></i>
                        <div class="text-truncate">
                            <strong class="d-block text-truncate text-white" style="max-width: 180px;">${title}</strong>
                            <span class="text-light opacity-75 small">${qty > 1 ? qty + ' কপি ' : ''}কার্টে যুক্ত হয়েছে</span>
                        </div>
                    </div>
                    <button type="button" class="btn btn-warning btn-sm rounded-pill fw-bold text-dark text-nowrap px-3 py-1" style="font-size: 0.76rem;" onclick="if(window.openCartDrawer){ window.openCartDrawer(); } this.parentElement.remove();">
                        কার্ট দেখুন →
                    </button>
                `;
                document.body.appendChild(toast);
                setTimeout(() => { if (toast) toast.remove(); }, 4000);
            } catch(e) {
                console.error(e);
            }
        };

        window.buyNowLive = function(id, title, price, image, slug) {
            try {
                let cart = JSON.parse(localStorage.getItem('idea_cart') || '[]');
                const numPrice = Number(price) || 0;
                const existing = cart.find(item => item.id == id);
                if (existing) {
                    existing.quantity = (existing.quantity || existing.qty || 1) + 1;
                    existing.qty = existing.quantity;
                } else {
                    cart.push({ id, title, price: numPrice, image, quantity: 1, qty: 1 });
                }
                localStorage.setItem('idea_cart', JSON.stringify(cart));
                updateHeaderCartBadge();

                // Open Cart Drawer or redirect to detail
                if (window.openCartDrawer) {
                    window.openCartDrawer();
                } else if (slug) {
                    window.location.href = "{{ url('/books') }}/" + encodeURIComponent(slug) + "?buy_now=1";
                } else {
                    window.location.href = "{{ route('cart') }}";
                }
            } catch(e) {
                console.error(e);
            }
        };

        document.addEventListener('DOMContentLoaded', updateHeaderCartBadge);
    </script>
@endonce
