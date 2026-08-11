@php
    /**
     * Public site header.
     *
     * Uses Bootstrap's own dropdown JS (bootstrap.bundle is loaded by
     * layouts.app) rather than hand-rolled show/hide, and its styles live in
     * public/css/site.css so no vite build is needed on deploy.
     *
     * Entries whose route is not registered are skipped, so a module that is
     * absent from a deployment cannot break the nav.
     */
    $me = auth()->user();

    // Primary nav. 'children' turns an entry into a dropdown.
    $nav = [
        ['route' => 'home',           'label' => 'হোম',        'icon' => 'house'],
        ['route' => 'book.index',     'label' => 'বই',         'icon' => 'book'],
        ['route' => 'ebook.index',    'label' => 'ই-বুক',      'icon' => 'tablet-screen-button'],
        ['label' => 'প্রকাশনা', 'icon' => 'building-columns', 'children' => [
            ['route' => 'authors.index',    'label' => 'লেখকগণ',   'icon' => 'pen-fancy'],
            ['route' => 'publishers.index', 'label' => 'প্রকাশকগণ', 'icon' => 'building'],
        ]],
        ['label' => 'পড়ার ঘর', 'icon' => 'newspaper', 'children' => [
            ['route' => 'blog.index',     'label' => 'ব্লগ',     'icon' => 'blog'],
            ['route' => 'webzine.index',  'label' => 'ওয়েবজিন',  'icon' => 'newspaper'],
            ['route' => 'research.index', 'label' => 'গবেষণা',   'icon' => 'flask'],
        ]],
        ['label' => 'আরও', 'icon' => 'ellipsis', 'children' => [
            ['route' => 'hub',     'label' => 'হাব',            'icon' => 'compass'],
            ['route' => 'about',   'label' => 'আমাদের সম্পর্কে', 'icon' => 'circle-info'],
            ['route' => 'contact', 'label' => 'যোগাযোগ',        'icon' => 'envelope'],
        ]],
    ];

    // Category mega-menu. Pulled from the catalog when the table exists.
    $megaCategories = collect();
    try {
        if (\Illuminate\Support\Facades\Schema::hasTable('categories')) {
            $megaCategories = \Illuminate\Support\Facades\DB::table('categories')
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->limit(12)
                ->get(['name', 'slug']);
        }
    } catch (\Throwable) {
        // Catalog not migrated yet — the mega menu simply falls back below.
    }

    /** Keeps a nav entry only when its route actually exists. */
    $usable = function (array $item) use (&$usable) {
        if (! empty($item['children'])) {
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

            {{-- Brand --}}
            <a class="site-brand" href="{{ route('home') }}">
                @php $logo = config('brand.logo'); @endphp
                @if ($logo && is_file(public_path($logo)))
                    <img src="{{ asset($logo) }}?v={{ @filemtime(public_path($logo)) ?: 1 }}"
                         alt="{{ config('brand.name') }}" width="44" height="44">
                @else
                    <span class="site-brand__fallback">{{ config('brand.lettermark') }}</span>
                @endif
                <span>
                    <span class="site-brand__name">{{ config('brand.name') }}</span>
                    <span class="site-brand__sub">বই ও প্রকাশনার ঠিকানা</span>
                </span>
            </a>

            {{-- Search --}}
            <form class="site-search" method="GET" action="{{ route('search') }}" role="search">
                <div class="site-search__box">
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
                           value="{{ request('q') }}" placeholder="বই, লেখক বা প্রকাশকের নাম লিখুন..."
                           autocomplete="off">

                    <button class="site-search__btn" type="submit">
                        <i class="fas fa-magnifying-glass"></i><span>খুঁজুন</span>
                    </button>
                </div>
            </form>

            {{-- Actions --}}
            <div class="site-actions">
                @if (Route::has('wishlist'))
                    <a href="{{ route('wishlist') }}" class="site-icon d-none d-sm-grid" title="পছন্দের তালিকা" aria-label="পছন্দের তালিকা">
                        <i class="fas fa-heart"></i>
                    </a>
                @endif

                <a href="{{ route('cart') }}" class="site-icon" title="কার্ট" aria-label="কার্ট">
                    <i class="fas fa-cart-shopping"></i>
                    <span class="site-icon__count" id="siteCartCount" hidden>০</span>
                </a>

                @auth
                    <div class="dropdown">
                        <button class="site-user dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="site-user__avatar">{{ mb_substr($me->name, 0, 1) }}</span>
                            <span class="text-start d-none d-md-block">
                                <span class="site-user__name d-block">{{ Str::limit($me->name, 14) }}</span>
                                <span class="site-user__role">
                                    {{ ['admin' => 'অ্যাডমিন', 'sub_admin' => 'সাব-অ্যাডমিন', 'seller' => 'সেলার',
                                        'publisher' => 'প্রকাশক', 'author' => 'লেখক'][$me->role] ?? 'গ্রাহক' }}
                                </span>
                            </span>
                        </button>

                        <ul class="dropdown-menu dropdown-menu-end site-drop">
                            <li class="px-2 py-1 d-md-none">
                                <div class="fw-semibold small">{{ $me->name }}</div>
                                <div class="text-muted" style="font-size:.75rem">{{ $me->email }}</div>
                            </li>

                            @if ($me->isAdmin() && Route::has('admin.dashboard'))
                                <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}"><i class="fas fa-gauge-high"></i>অ্যাডমিন প্যানেল</a></li>
                            @elseif ($me->isSeller() && Route::has('subadmin.bills.index'))
                                <li><a class="dropdown-item" href="{{ route('subadmin.bills.index') }}"><i class="fas fa-file-invoice-dollar"></i>সেলার প্যানেল</a></li>
                            @endif

                            @if (Route::has('wishlist'))
                                <li><a class="dropdown-item" href="{{ route('wishlist') }}"><i class="fas fa-heart"></i>পছন্দের তালিকা</a></li>
                            @endif
                            <li><a class="dropdown-item" href="{{ route('cart') }}"><i class="fas fa-bag-shopping"></i>আমার কার্ট</a></li>

                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="fas fa-arrow-right-from-bracket"></i>লগ আউট
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

    {{-- Nav row (desktop) --}}
    <nav class="site-nav" aria-label="প্রধান মেনু">
        <div class="container">
            <ul class="site-nav__list">
                {{-- Categories mega menu --}}
                <li class="dropdown">
                    <button class="site-nav__link site-nav__all dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-grip"></i> সব বিভাগ
                    </button>
                    <div class="dropdown-menu site-drop site-mega">
                        @if ($megaCategories->isNotEmpty())
                            <div class="site-mega__grid">
                                @foreach ($megaCategories as $cat)
                                    <a class="site-mega__item" href="{{ route('book.index', ['category' => $cat->slug]) }}">
                                        <span class="site-mega__icon"><i class="fas fa-book-open"></i></span>
                                        <span>{{ $cat->name }}</span>
                                    </a>
                                @endforeach
                            </div>
                        @else
                            <div class="site-mega__grid">
                                @foreach ($nav as $item)
                                    @if (empty($item['children']))
                                        <a class="site-mega__item" href="{{ route($item['route']) }}">
                                            <span class="site-mega__icon"><i class="fas fa-{{ $item['icon'] }}"></i></span>
                                            <span>{{ $item['label'] }}</span>
                                        </a>
                                    @else
                                        @foreach ($item['children'] as $child)
                                            <a class="site-mega__item" href="{{ route($child['route']) }}">
                                                <span class="site-mega__icon"><i class="fas fa-{{ $child['icon'] }}"></i></span>
                                                <span>{{ $child['label'] }}</span>
                                            </a>
                                        @endforeach
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    </div>
                </li>

                @foreach ($nav as $item)
                    @if (empty($item['children']))
                        <li>
                            <a class="site-nav__link {{ request()->routeIs($item['route']) ? 'is-active' : '' }}"
                               href="{{ route($item['route']) }}">{{ $item['label'] }}</a>
                        </li>
                    @else
                        @php
                            $active = collect($item['children'])->contains(fn ($c) => request()->routeIs($c['route']));
                        @endphp
                        <li class="dropdown">
                            <button class="site-nav__link dropdown-toggle {{ $active ? 'is-active' : '' }}"
                                    type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                {{ $item['label'] }}
                            </button>
                            <ul class="dropdown-menu site-drop">
                                @foreach ($item['children'] as $child)
                                    <li>
                                        <a class="dropdown-item {{ request()->routeIs($child['route']) ? 'active' : '' }}"
                                           href="{{ route($child['route']) }}">
                                            <i class="fas fa-{{ $child['icon'] }}"></i>{{ $child['label'] }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </li>
                    @endif
                @endforeach
            </ul>
        </div>
    </nav>
</header>

{{-- Mobile nav --}}
<div class="offcanvas offcanvas-start site-offcanvas" tabindex="-1" id="siteMobileNav" aria-labelledby="siteMobileNavLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title d-flex align-items-center gap-2" id="siteMobileNavLabel">
            <x-brand-logo :size="32" />
            {{ config('brand.name') }}
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="বন্ধ করুন"></button>
    </div>

    <div class="offcanvas-body">
        @foreach ($nav as $item)
            @if (empty($item['children']))
                <a class="site-m-link" href="{{ route($item['route']) }}">
                    <i class="fas fa-{{ $item['icon'] }}"></i>{{ $item['label'] }}
                </a>
            @else
                <div class="text-uppercase fw-semibold text-muted mt-3 mb-1" style="font-size:.7rem; letter-spacing:.07em">
                    {{ $item['label'] }}
                </div>
                @foreach ($item['children'] as $child)
                    <a class="site-m-link" href="{{ route($child['route']) }}">
                        <i class="fas fa-{{ $child['icon'] }}"></i>{{ $child['label'] }}
                    </a>
                @endforeach
            @endif
        @endforeach

        <div class="d-grid gap-2 mt-4">
            @auth
                @if ($me->isAdmin() && Route::has('admin.dashboard'))
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-primary rounded-pill">অ্যাডমিন প্যানেল</a>
                @elseif ($me->isSeller() && Route::has('subadmin.bills.index'))
                    <a href="{{ route('subadmin.bills.index') }}" class="btn btn-primary rounded-pill">সেলার প্যানেল</a>
                @endif
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn btn-outline-danger rounded-pill w-100">লগ আউট</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="btn btn-outline-primary rounded-pill">লগইন</a>
                @if (Route::has('register.choose'))
                    <a href="{{ route('register.choose') }}" class="btn btn-primary rounded-pill">রেজিস্ট্রেশন</a>
                @endif
            @endauth
        </div>
    </div>
</div>

@once
    <script>
        // Adds a shadow to the header once the page is scrolled.
        (function () {
            var head = document.getElementById('siteHead');
            if (!head) return;

            var onScroll = function () {
                head.classList.toggle('is-stuck', window.scrollY > 4);
            };

            onScroll();
            window.addEventListener('scroll', onScroll, { passive: true });
        })();
    </script>
@endonce
