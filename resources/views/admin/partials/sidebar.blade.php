@php
    /**
     * Nav tree for the admin panel. Every section of the site — including the
     * sub-admin / seller panels — hangs off this one sidebar.
     *
     * `route` is a route name; entries whose route is not registered are skipped
     * so a half-deployed module can never 500 the whole panel.
     */
    $pending = $adminPendingRegistrations ?? 0;
    $pendingPayouts = \Illuminate\Support\Facades\Schema::hasTable('author_payout_requests')
        ? \App\Models\AuthorPayoutRequest::where('status', 'pending')->count()
        : 0;
    $pendingHonorariums = \Illuminate\Support\Facades\Schema::hasTable('author_honorariums')
        ? \App\Models\AuthorHonorarium::where('payment_status', 'pending')->count()
        : 0;

    $menu = [
        null => [
            ['route' => 'admin.dashboard', 'icon' => 'gauge-high', 'label' => 'Dashboard'],
        ],
        'Catalog' => [
            ['route' => 'admin.books',      'icon' => 'book',        'label' => 'Books'],
            ['route' => 'admin.categories', 'icon' => 'folder-tree', 'label' => 'Categories'],
            ['route' => 'admin.authors',    'icon' => 'pen-fancy',   'label' => 'Authors'],
            ['route' => 'admin.publishers', 'icon' => 'building',    'label' => 'Publishers'],
        ],
        'E-Books & Royalties' => [
            ['route' => 'admin.ebooks',                 'icon' => 'tablet-screen-button', 'label' => 'E-Books Inventory'],
            ['route' => 'admin.ebook-sales-report',     'icon' => 'chart-pie',             'label' => 'E-Book Sales Report'],
            ['route' => 'admin.author-royalties.index', 'icon' => 'scale-balanced',       'label' => 'Royalty Management'],
            ['route' => 'admin.author-payouts.index',   'icon' => 'hand-holding-dollar',  'label' => 'Royalty Payouts',
             'badge' => $pendingPayouts > 0 ? $pendingPayouts : null, 'badgeClass' => 'bg-warning text-dark'],
            ['route' => 'admin.royalty-payout-logs',    'icon' => 'file-invoice-dollar',  'label' => 'Payout Gateway Logs'],
        ],
        'Purchases & Inventory' => [
            ['route' => 'admin.purchases.index',    'url' => route('admin.purchases.index', ['category' => 'books']), 'category' => 'books', 'icon' => 'book-open', 'label' => '১. বই ক্রয় (Books)'],
            ['route' => 'admin.purchases.index',    'url' => route('admin.purchases.index', ['category' => 'raw_materials']), 'category' => 'raw_materials', 'icon' => 'boxes-stacked', 'label' => '২. কাঁচামাল ক্রয় (Raw Materials)'],
            ['route' => 'admin.purchases.index',    'url' => route('admin.purchases.index', ['category' => 'other']), 'category' => 'other', 'icon' => 'cart-shopping', 'label' => '৩. অন্যান্য ক্রয় (Other Purchases)'],
            ['route' => 'admin.purchases.create',   'icon' => 'cart-plus',           'label' => 'নতুন ক্রয় চালান এন্ট্রি'],
            ['route' => 'admin.purchases.payments', 'icon' => 'hand-holding-dollar', 'label' => 'কিস্তি ও পেমেন্ট হিস্ট্রি'],
        ],
        'Idea Accounting' => [
            ['route' => 'admin.accounting.reports.index',   'icon' => 'chart-pie',           'label' => 'P&L Reports & Analytics'],
            ['route' => 'admin.accounting.salary.index',    'icon' => 'money-check-dollar',  'label' => 'Staff Payroll & Salary'],
            ['route' => 'admin.accounting.employees.index', 'icon' => 'users-gear',          'label' => 'Employee Profiles'],
            ['route' => 'admin.accounting.index',           'icon' => 'scale-balanced',      'label' => 'Income & Expenses Ledger'],
            ['route' => 'admin.accounting.invoices.index',  'icon' => 'file-invoice-dollar', 'label' => 'Invoices & Challans'],
            ['route' => 'admin.accounting.invoices.create', 'icon' => 'file-circle-plus',   'label' => 'Create Invoice'],
        ],
        'Content' => [
            ['route' => 'admin.blog',                     'icon' => 'newspaper',          'label' => 'Ideapatra / Blog'],
            ['route' => 'admin.author-honorariums.index', 'icon' => 'hand-holding-heart', 'label' => 'Author Honorariums',
             'badge' => $pendingHonorariums > 0 ? $pendingHonorariums : null, 'badgeClass' => 'bg-danger text-white'],
            ['route' => 'admin.webzines',                 'icon' => 'book-open',          'label' => 'Webzines'],
        ],
        'Sales & Orders' => [
            ['route' => 'admin.ecommerce-orders', 'icon' => 'cart-shopping', 'label' => 'Book Orders'],
            ['route' => 'admin.gateway-reports',  'icon' => 'receipt',       'label' => 'Gateway Reports'],
            ['route' => 'admin.payments.index',   'icon' => 'credit-card',   'label' => 'Payment Gateways'],
            ['route' => 'admin.customers',        'icon' => 'user-tag',      'label' => 'Customers & Broadcast'],
            ['route' => 'admin.orders',           'icon' => 'file-invoice',  'label' => 'Seller Bills'],
            ['route' => 'admin.book-requests.index', 'icon' => 'code-pull-request', 'label' => 'Book Requests'],
        ],
        'User Management' => [
            ['route' => 'admin.users',               'icon' => 'users',      'label' => 'All Users'],
            ['route' => 'admin.registrations.index', 'icon' => 'user-check', 'label' => 'Registration Approvals',
             'badge' => $pending, 'badgeClass' => 'bg-warning text-dark'],
            ['route' => 'admin.sub-admins.index',    'icon' => 'user-shield', 'label' => 'Sub-Admins'],
        ],
        'Administration' => [
            ['route' => 'admin.roles.index',       'icon' => 'key',             'label' => 'Roles & Permissions'],
            ['route' => 'admin.visitor-reports',   'icon' => 'chart-line',     'label' => 'Visitor Reports'],
            ['route' => 'admin.system-settings',   'icon' => 'sliders',         'label' => 'System Settings'],
            ['route' => 'admin.cache.manage',      'icon' => 'bolt',            'label' => 'Cache Management'],
            ['route' => 'admin.media.index',       'icon' => 'images',          'label' => 'Media & Library'],
            ['route' => 'admin.backup.index',      'icon' => 'database',        'label' => 'Backup Database'],
            ['route' => 'admin.audit-logs.index',  'icon' => 'shield-halved',   'label' => 'Audit & Logs'],
        ],
        'Seller Panel' => [
            ['route' => 'subadmin.bills.index', 'icon' => 'file-invoice-dollar', 'label' => 'Bills List'],
            ['route' => 'subadmin.accounts',    'icon' => 'wallet',             'label' => 'Seller Accounts'],
        ],
        'Public Site' => [
            ['route' => 'home', 'icon' => 'arrow-up-right-from-square', 'label' => 'View Website', 'target' => '_blank'],
        ],
    ];
@endphp

<aside class="adm-side" data-sidebar>
    <div class="adm-side__header d-flex align-items-center justify-content-between">
        <a href="{{ route('admin.dashboard') }}" class="adm-brand text-decoration-none">
            <x-brand-logo :size="38" />
            <span class="adm-brand__text">
                <span class="adm-brand__name d-block">{{ config('brand.name') }}</span>
                <span class="adm-brand__sub">{{ config('brand.tagline') }}</span>
            </span>
        </a>
        <button type="button" class="adm-side__close d-lg-none btn btn-sm text-white" data-side-close aria-label="Close sidebar">
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
                        // Check if active based on route and query param
                        $base   = preg_replace('/\.index$/', '', $item['route']);
                        $isRouteActive = request()->routeIs($item['route']) || request()->routeIs($base . '.*');
                        if (isset($item['category'])) {
                            $active = $isRouteActive && request('category') === $item['category'];
                        } else {
                            $active = $isRouteActive && empty(request('category'));
                        }
                        $href = $item['url'] ?? route($item['route']);
                    @endphp
                    <a href="{{ $href }}"
                       class="adm-nav__link {{ $active ? 'is-active' : '' }}"
                       @isset($item['target']) target="{{ $item['target'] }}" rel="noopener" @endisset
                       @if ($active) aria-current="page" @endif>
                        <i class="fas fa-{{ $item['icon'] }}"></i>
                        <span>{{ $item['label'] }}</span>
                        @if (! empty($item['badge']))
                            <span class="badge {{ $item['badgeClass'] ?? 'bg-primary' }}">{{ $item['badge'] }}</span>
                        @endif
                    </a>
                @endforeach
            @endif
        @endforeach
    </nav>

    <div class="adm-side__foot adm-brand__text">
        Version 1.0 · {{ now()->format('M d, Y') }}
    </div>
</aside>
