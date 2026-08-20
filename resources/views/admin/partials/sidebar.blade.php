@php
    /**
     * Nav tree for the admin panel. Every section of the site — including the
     * sub-admin / seller panels — hangs off this one sidebar.
     *
     * `route` is a route name; entries whose route is not registered are skipped
     * so a half-deployed module can never 500 the whole panel.
     */
    $pending = $adminPendingRegistrations ?? 0;

    $menu = [
        null => [
            ['route' => 'admin.dashboard', 'icon' => 'gauge-high', 'label' => 'ড্যাশবোর্ড'],
        ],
        'ক্যাটালগ' => [
            ['route' => 'admin.books',      'icon' => 'book',        'label' => 'বই'],
            ['route' => 'admin.categories', 'icon' => 'folder-tree', 'label' => 'ক্যাটাগরি'],
            ['route' => 'admin.ebooks',     'icon' => 'tablet-screen-button', 'label' => 'ই-বুক'],
            ['route' => 'admin.authors',    'icon' => 'pen-fancy',   'label' => 'লেখক'],
            ['route' => 'admin.publishers', 'icon' => 'building',    'label' => 'প্রকাশক'],
        ],
        'প্রকাশনী ক্রয় ও স্টক' => [
            ['route' => 'admin.purchases.index',    'icon' => 'receipt',             'label' => 'প্রকাশনী ক্রয় তালিকা'],
            ['route' => 'admin.purchases.create',   'icon' => 'cart-plus',           'label' => 'নতুন ক্রয় এন্ট্রি'],
            ['route' => 'admin.purchases.payments', 'icon' => 'hand-holding-dollar', 'label' => 'পরিশোধ ও কিস্তি হিসাব'],
        ],
        'আইডিয়া হিসাব' => [
            ['route' => 'admin.accounting.index',           'icon' => 'scale-balanced',     'label' => 'আয়-ব্যয় ও হিসাব খাতা'],
            ['route' => 'admin.accounting.invoices.index',  'icon' => 'file-invoice-dollar', 'label' => 'বিল, চালান ও দরপত্র তালিকা'],
            ['route' => 'admin.accounting.invoices.create', 'icon' => 'file-circle-plus',   'label' => 'বিল, চালান ও দরপত্র তৈরি'],
        ],
        'কনটেন্ট' => [
            ['route' => 'admin.blog',     'icon' => 'newspaper', 'label' => 'আইডিয়াপত্র'],
            ['route' => 'admin.webzines', 'icon' => 'book-open', 'label' => 'ওয়েবজিন'],
        ],
        'বিক্রয়' => [
            ['route' => 'admin.ecommerce-orders', 'icon' => 'cart-shopping', 'label' => 'বইয়ের অর্ডার'],
            ['route' => 'admin.payments.index',   'icon' => 'credit-card',   'label' => 'পেমেন্ট ও গেটওয়ে'],
            ['route' => 'admin.customers',        'icon' => 'user-tag',      'label' => 'গ্রাহক ও ব্রডকাস্ট'],
            ['route' => 'admin.orders',           'icon' => 'file-invoice',  'label' => 'সেলার বিল'],
            ['route' => 'admin.book-requests.index', 'icon' => 'code-pull-request', 'label' => 'বই রিকোয়েস্ট'],
        ],
        'ব্যবহারকারী' => [
            ['route' => 'admin.users',               'icon' => 'users',      'label' => 'সব ব্যবহারকারী'],
            ['route' => 'admin.registrations.index', 'icon' => 'user-check', 'label' => 'রেজিস্ট্রেশন অনুমোদন',
             'badge' => $pending, 'badgeClass' => 'bg-warning text-dark'],
            ['route' => 'admin.sub-admins.index',    'icon' => 'user-shield', 'label' => 'সাব-অ্যাডমিন'],
        ],
        'অ্যাডমিন এক্সেস' => [
            ['route' => 'admin.roles.index',       'icon' => 'key',             'label' => 'পারমিশন ম্যাট্রিক্স'],
            ['route' => 'admin.visitor-reports',   'icon' => 'chart-line',     'label' => 'ভিজিটর রিপোর্ট'],
            ['route' => 'admin.activity-logs',     'icon' => 'clock-rotate-left', 'label' => 'অ্যাক্টিভিটি লগ'],
            ['route' => 'admin.system-settings',   'icon' => 'sliders',         'label' => 'সিস্টেম সেটিংস'],
        ],
        'সাব-অ্যাডমিন প্যানেল' => [
            ['route' => 'subadmin.bills.index', 'icon' => 'file-invoice-dollar', 'label' => 'বিল তালিকা'],
            ['route' => 'subadmin.accounts',    'icon' => 'wallet',             'label' => 'সেলার অ্যাকাউন্ট'],
        ],
        'সাইট' => [
            ['route' => 'home', 'icon' => 'arrow-up-right-from-square', 'label' => 'ওয়েবসাইট দেখুন', 'target' => '_blank'],
        ],
    ];
@endphp

<aside class="adm-side">
    <div class="adm-side__header d-flex align-items-center justify-content-between">
        <a href="{{ route('admin.dashboard') }}" class="adm-brand text-decoration-none">
            <x-brand-logo :size="38" />
            <span class="adm-brand__text">
                <span class="adm-brand__name d-block">{{ config('brand.name') }}</span>
                <span class="adm-brand__sub">{{ config('brand.tagline') }}</span>
            </span>
        </a>
        <button type="button" class="adm-side__close d-lg-none btn btn-sm text-white" data-side-close aria-label="সাইডবার বন্ধ করুন">
            <i class="fas fa-times fs-5"></i>
        </button>
    </div>

    <nav class="adm-nav">
        @foreach ($menu as $group => $items)
            @php
                // Drop entries whose route isn't registered on this deployment.
                $items = array_filter($items, fn ($i) => Route::has($i['route']));
            @endphp

            @if (! empty($items))
                @if ($group)
                    <div class="adm-nav__label">{{ $group }}</div>
                @endif

                @foreach ($items as $item)
                    @php
                        // "admin.books" also highlights "admin.books.edit", etc.
                        $base   = preg_replace('/\.index$/', '', $item['route']);
                        $active = request()->routeIs($item['route']) || request()->routeIs($base . '.*');
                    @endphp
                    <a href="{{ route($item['route']) }}"
                       class="adm-nav__link {{ $active ? 'is-active' : '' }}"
                       @isset($item['target']) target="{{ $item['target'] }}" rel="noopener" @endisset
                       @if ($active) aria-current="page" @endif>
                        <i class="fas fa-{{ $item['icon'] }}"></i>
                        <span>{{ $item['label'] }}</span>
                        @if (! empty($item['badge']))
                            <span class="badge {{ $item['badgeClass'] ?? 'bg-primary' }}">@bn($item['badge'])</span>
                        @endif
                    </a>
                @endforeach
            @endif
        @endforeach
    </nav>

    <div class="adm-side__foot adm-brand__text">
        সংস্করণ @bn(1).@bn(0) · @bnDate(now())
    </div>
</aside>
